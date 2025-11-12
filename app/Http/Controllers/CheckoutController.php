<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
            'phone'      => 'required|string',
            'address'    => 'required|string',
            'city'       => 'required|string',
            'state'      => 'required|string',
            'zip'        => 'required|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string'
        ]);

        $product = session('selected_product');
        if (!$product) {
            return redirect('/products')->with('error', 'No product selected.');
        }

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
                'shipping'         => 10.00,
                'total'            => $product['price'] + 10.00,
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

            // return redirect('/checkout')
            //     ->with('success', 'Order successfully created in Telegra!')
            //     ->with('order_id', $order->telegra_order_id);

            return redirect('/questionnaire');
        }

        return back()->with('error', 'Failed to create order: ' . $response->body());
    }
}
