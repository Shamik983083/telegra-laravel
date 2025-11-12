<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\QuestionnaireResponse; // ✅ <--- ADD THIS IMPORT
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

public function store(Request $request)
    {
        $answers = $request->input('answers', []);
        $product = session('selected_product');
        $questionnaireId = session('questionnaire_instance_id');
        $sessionId = session()->getId();

        if (empty($answers)) {
            return back()->with('error', 'Please answer all questions.');
        }

        // Save to DB
        QuestionnaireResponse::create([
            'session_id' => $sessionId,
            'product_id' => $product['id'] ?? null,
            'questionnaire_instance_id' => $questionnaireId,
            'responses' => $answers,
        ]);

        // Save in session too (for review or next step)
        session(['questionnaire_answers' => $answers]);

        return redirect('/thank-you')->with('success', 'Your responses have been saved successfully!');
    }

}
