<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class QuestionnaireController extends Controller
{
    public function show()
    {
        $questionnaireId = session('questionnaire_instance_id');
        $product = session('selected_product');

        if (!$questionnaireId) {
            return redirect('/checkout')->with('error', 'Questionnaire not found.');
        }

        // ✅ Use values from config (which reads .env)
        $baseUrl = config('telegra.base_url');
        $token = config('telegra.token');

        $url = "{$baseUrl}/questionnaireInstances/{$questionnaireId}";

        // ✅ API request
        $response = Http::withToken($token)->get($url);

        if ($response->failed()) {
            $status = $response->status();
            $body = $response->body();

            return response()->view('questionnaire', [
                'product' => $product,
                'questions' => [],
                'rawResponse' => [
                    'error' => true,
                    'status' => $status,
                    'body' => $body,
                    'url' => $url,
                ]
            ]);
        }

        $data = $response->json();

        // ✅ Extract questions from `locations` (Telegra format)
        $questions = $data['questionnaire']['locations'] ?? [];

        return view('questionnaire', [
            'product' => $product,
            'questions' => $questions,
            'rawResponse' => $data,
        ]);
    }
}
