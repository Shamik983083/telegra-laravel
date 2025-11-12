<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\SelectedProduct;

class ProductController extends Controller
{
    public function index(Request $request)
    {

        // ✅ Use values from config (which reads .env)
        $baseUrl = config('telegra.base_url');
        $token = config('telegra.token');

         $url = "{$baseUrl}/productVariations";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get($url);

        if ($response->failed()) {
            // ✅ Fallback mock data
            $products = [
                [
                    'id' => 'mock1',
                    'description' => 'Semaglutide',
                    'form' => 'Injection',
                    'pricePerUnit' => 370,
                    'product' => [
                        'title' => 'Semaglutide',
                        'image' => 'https://via.placeholder.com/250x250?text=Semaglutide',
                    ],
                ],
                [
                    'id' => 'mock2',
                    'description' => 'Tirzepatide',
                    'form' => 'Injection',
                    'pricePerUnit' => 199,
                    'product' => [
                        'title' => 'Tirzepatide',
                        'image' => 'https://via.placeholder.com/250x250?text=Tirzepatide',
                    ],
                ],
            ];
        } else {
            $products = $response->json()['productVariations'] ?? [];
        }

        $search = $request->get('search');
        if ($search) {
            $products = array_filter($products, function ($p) use ($search) {
                return stripos($p['product']['title'], $search) !== false
                    || stripos($p['description'], $search) !== false;
            });
        }

        return view('products.index', compact('products', 'search'));
    }

    public function selectProduct(Request $request)
    {
        $productId = $request->input('product_id');
        $name = $request->input('name');
        $price = $request->input('price');
        $image = $request->input('image') ?: 'https://via.placeholder.com/250x250?text=No+Image';
 // ✅ capture image from hidden input

        session([
            'selected_product' => [
                'id' => $productId,
                'name' => $name,
                'price' => $price,
                'image' => $image // ✅ save it here
            ],
        ]);

        SelectedProduct::create([
            'user_id' => auth()->id() ?? null,
            'product_id' => $productId,
            'name' => $name,
            'price' => $price,
        ]);

        //return redirect('/questionnaire');
        return redirect()->route('checkout.show');
    }
}
