<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\SelectedProduct;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $url = 'https://dev-core-ias-rest.telegramd.com/productVariations';
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6eyJfaWQiOiJ1c3I6OjNjZTQyMjFlLWNkOWUtNGJlMi1iNjU0LTI1NjI3MjI3NmJmMyIsImVtYWlsIjoiZGV2QGNvZGVjbG91ZHMuYml6IiwicGFzc3dvcmQiOiIkMmIkMDkkVUk3enRpaWcvMk56ME1oUDU3Z1Q2dXJFZGFwME1aMjE4UmFBdFVBYnpyREczMGk0TUhLczIiLCJzdGF0dXMiOiJhY3RpdmUiLCJwaG9uZSI6IjEyMTIxMjEyMTIiLCJuYW1lIjoiQ29kZSBDbG91ZHMiLCJmaXJzdE5hbWUiOiJDb2RlIiwibWlkZGxlTmFtZSI6IiIsImxhc3ROYW1lIjoiQ2xvdWRzIiwicm9sZSI6ImFmZmlsaWF0ZS1zdXBlci1hZG1pbiIsInBpY3R1cmUiOiJodHRwczovL2dyYXZhdGFyLmNvbS9hdmF0YXIvMzE2ZWI3ZWY4N2E1NGYyOTZhMjQyOGVhZDAxNWE2NWY_ZD1pZGVudGljb24iLCJhZmZpbGlhdGUiOiJhZmY6OjVjMWMyNjMzLWRiNWEtNDI5NC1hNjIwLTIzZDNkOWNjNDg2NCIsInBhc3N3b3JkUmVzZXRSZXF1aXJlZCI6ZmFsc2UsInNldHRpbmdzIjp7ImZpbHRlcnMiOltdfSwidHdvRmFjdG9yVHlwZSI6ImVtYWlsIiwiZGVsZXRlZCI6ZmFsc2UsImtleXdvcmRzIjpbImRldiBjb2RlY2xvdWRzIGJpeiIsIjEyMTIxMjEyMTIiLCJjb2RlIGNsb3VkcyJdLCJjcmVhdGVkQXQiOiIyMDI1LTEwLTI3VDE1OjUwOjU0Ljc2NFoiLCJ1cGRhdGVkQXQiOiIyMDI1LTEwLTI4VDEwOjAwOjUzLjE2MFoiLCJwYXNzd29yZFVwZGF0ZWRBdCI6IjIwMjUtMTAtMjhUMDk6NTg6NTcuMjYxWiIsIm90cFNlY3JldCI6IlpDRjI0Rk8yUUpLV1ZQRk9BSURGTUtYSDUzTEgySDIyIiwib3RwVXJpIjoib3RwYXV0aDovL3RvdHAvVGVsZWdyYSUyME1EJTNBQ29kZSUyMENsb3Vkcz9zZWNyZXQ9WkNGMjRGTzJRSktXVlBGT0FJREZNS1hINTNMSDJIMjImaXNzdWVyPVRlbGVncmElMjBNRCIsImZ1bGxOYW1lIjoiQ29kZSBDbG91ZHMiLCJpZCI6InVzcjo6M2NlNDIyMWUtY2Q5ZS00YmUyLWI2NTQtMjU2MjcyMjc2YmYzIn0sImlhdCI6MTc2Mjc3NDk0MH0.WAhsaEXSngjkrR_m1kUznF8yanXe_tN3buduBWJ-QJw';

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
        $image = $request->input('image'); // ✅ capture image from hidden input

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
