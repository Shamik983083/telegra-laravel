<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class CheckoutController extends Controller
{
    public function show()
    {
        $product = session('selected_product');

        if (!$product) {
            return redirect('/products')->with('error', 'Please select a product first.');
        }

        $subtotal = $product['price'];
        $shipping = 10.00;
        $total = $subtotal + $shipping;

        return view('checkout', compact('product', 'subtotal', 'shipping', 'total'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email',
            'phone'      => 'required|numeric',
            'address'    => 'required|string',
            'city'       => 'required|string',
            'state'      => 'required|string',
            'zip'        => 'required|numeric',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string',
            'cardnumber' => 'required|numeric',
            'expmonth' => 'required|numeric',
            'expyear' => 'required|numeric',
            'cvv' => 'required|numeric',
            'shipping' => 'required|numeric', // ✅ Add shipping validation

        ]);

        $product = session('selected_product');
        if (!$product) {
            return redirect('/products')->with('error', 'No product selected.');
        }

        // ✅ Dynamic shipping from form
        $shippingCost = (float) $request->input('shipping');
        $total = $product['price'] + $shippingCost;

        // ✅ Prepare API payload
        $payload = [
    'address' => [
        'billing' => [
            'address1' => $request->address,
            'city'     => $request->city,
            'state'    => $request->state,
            'zipcode'  => $request->zip, // ✅ changed from zip to zipcode
            'country'  => 'US',
        ],
        'shipping' => [
            'address1' => $request->address,
            'city'     => $request->city,
            'state'    => $request->state,
            'zipcode'  => $request->zip, // ✅ changed from zip to zipcode
            'country'  => 'US',
        ],
    ],
    'patient' => [
        'firstName'        => $request->first_name,
        'lastName'         => $request->last_name,
        'middleName'       => '',
        'email'            => $request->email,
        'phone'            => $request->phone,
        'dateOfBirth'      => $request->date_of_birth ?? '1990-01-01',
        'genderBiological' => $request->gender ?? 'male',
        'ssoKey'           => 'custom-sso-key-' . uniqid(),
    ],
    'productVariations' => [
        [
            'productVariation' => $product['id'],
            'quantity' => 1
        ]
        ],
       // ✅ ADD THIS FIELD
    'expiresAt' => now()->addDays(7)->toISOString() // e.g., 7 days from today
];


        // ✅ Send to Telegra API
 // ✅ Use values from config (which reads .env)
        $baseUrl = config('telegra.base_url');
        $token = config('telegra.token');

        $url = "{$baseUrl}/orders";

        $response = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/json'])
            ->post($url, $payload);

        if ($response->successful()) {
            $data = $response->json();

             // ✅ Extract order + questionnaire instance
        $orderId = $data['id'] ?? null;
        $questionnaireId = $data['questionnaireInstances'][0]['id'] ?? null;

            // ✅ Store order in local DB
            $order = Order::create([
                'telegra_order_id' => $data['id'] ?? null,
                'product_id'       => $product['id'],
                'product_name'     => $product['name'],
                'price'            => $product['price'],
                'shipping'         => $shippingCost, // ✅ Store dynamic shipping
                'total'            => $total,
                'first_name'       => $request->first_name,
                'last_name'        => $request->last_name,
                'email'            => $request->email,
                'phone'            => $request->phone,
                'date_of_birth'    => $request->date_of_birth ?? null,
                'gender'           => $request->gender ?? null,
                'address'          => $request->address,
                'city'             => $request->city,
                'state'            => $request->state,
                'zip'              => $request->zip,
                'country'          => 'US'
            ]);

             // ✅ Store questionnaire instance in session
        session([
            'telegra_order_id' => $orderId,
            'questionnaire_instance_id' => $questionnaireId
        ]);

 // ✅ Step 2: Create order in VRIO CRM
        $vrioUrl = config('vrio.base_url') . '/orders';
        $vrioToken = config('vrio.token');

         $vrioPayload = [
            'action' => '',
            'connection_id' => 1, // ⚠️ Replace with your actual connection ID
            'campaign_id' => 67,   // ⚠️ Replace with your actual campaign ID
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'birthday' => $request->date_of_birth ?? '1990-01-01',
            'gender' => $request->gender ?? 'male',
            'offers' => [
                [
                    'offer_id' => 2, // depends on Vrio campaign offer ID
                    'order_offer_quantity' => 1,
                    'item_id' => 2082, //✅ Replace 5 with the actual item_id from VRIO
                    'quantity' => 1,
                ],
            ],
            'bill_fname' => $request->first_name,
            'bill_lname' => $request->last_name,
            'bill_address1' => $request->address,
            'bill_city' => $request->city,
            'bill_state' => $request->state,
            'bill_zipcode' => $request->zip,
            'bill_country' => 'US',
            'same_address' => true,
            'payment_method_id' => 1, // Credit Card
            'card_type_id' => 2, // Mastercard (for example)
            'card_number' => $request->cardnumber, // test card for sandbox
            'card_cvv' => $request->cvv,
            'card_exp_month' => $request->expmonth,
            'card_exp_year' => $request->expyear,
            'ip_address' => $request->ip(),
        ];


   $vrioResponse = Http::withHeaders([
        'X-Api-Key' => $vrioToken,
        'Accept' => 'application/json',
    ])
    ->asJson()
    ->post($vrioUrl, $vrioPayload);

      if ($vrioResponse->failed()) {
    $errorBody = $vrioResponse->json() ?? $vrioResponse->body();

    Log::error('❌ VRIO Order Failed', [
        'payload' => $vrioPayload,
        'response' => $errorBody,
    ]);

    // ✅ Return back to checkout with the VRIO error visible
    return back()->with('error', 'VRIO order failed: ' . json_encode($errorBody));
} else {
    Log::info('✅ VRIO Order Created', [
        'response' => $vrioResponse->json(),
    ]);
}


            // return redirect('/checkout')
            //     ->with('success', 'Order successfully created in Telegra!')
            //     ->with('order_id', $order->telegra_order_id);

            return redirect('/questionnaire');
        }

        return back()->with('error', 'Failed to create order: ' . $response->body());
    }
}
