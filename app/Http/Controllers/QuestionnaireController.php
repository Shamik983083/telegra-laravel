<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\QuestionnaireResponse;

class QuestionnaireController extends Controller
{
    public function show()
    {
        $questionnaireId = session('questionnaire_instance_id');
        $product = session('selected_product');

        if (!$questionnaireId) {
            return redirect('/checkout')->with('error', 'Questionnaire not found.');
        }

        $baseUrl = rtrim(config('telegra.base_url'), '/');
        $token = config('telegra.token');
        $url = "{$baseUrl}/questionnaireInstances/{$questionnaireId}";

        $response = Http::withToken($token)->get($url);

        if ($response->failed()) {
            Log::error('❌ Failed to fetch questionnaire', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return view('questionnaire', [
                'product' => $product,
                'questions' => [],
                'rawResponse' => ['error' => true, 'body' => $response->body()],
            ]);
        }

        $data = $response->json();
        $questions = $data['questionnaire']['locations'] ?? [];
        $currentLocation = $data['currentLocation'] ?? null;

        return view('questionnaire', compact('product', 'questions', 'currentLocation'));
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

        // ✅ Save locally
        QuestionnaireResponse::create([
            'session_id' => $sessionId,
            'product_id' => $product['id'] ?? null,
            'questionnaire_instance_id' => $questionnaireId,
            'responses' => $answers,
        ]);

        $baseUrl = rtrim(config('telegra.base_url'), '/');
        $token = config('telegra.token');

        $currentLocation = null;
        $isValid = false;

        foreach ($answers as $location => $value) {
            $endpoint = "{$baseUrl}/questionnaireInstances/{$questionnaireId}/actions/answerLocation?shouldNavigateNext=true";

            $payload = [
                'location' => $location,
                'value' => $this->normalizeValue($value),
            ];

            $response = Http::withToken($token)->asJson()->post($endpoint, $payload);

            Log::info('📤 Sent to Telegra', [
                'url' => $endpoint,
                'payload' => $payload,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            if ($response->failed()) {
                Log::error('❌ Failed to push answer to Telegra', [
                    'url' => $endpoint,
                    'payload' => $payload,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                continue;
            }

            $result = $response->json();

            // ✅ Capture next question
            $currentLocation = $result['currentLocation'] ?? null;
            $isValid = $result['valid'] ?? false;

            if ($isValid) {
                Log::info('✅ Questionnaire marked as complete by Telegra', [
                    'final_location' => $location,
                ]);
                break;
            }

            if (!$currentLocation) {
                Log::warning('⚠️ No currentLocation returned, stopping iteration.');
                break;
            }
        }

        session(['questionnaire_answers' => $answers]);

        return redirect('/thank-you')
            ->with('success', 'Your responses have been submitted successfully!');
    }

    private function normalizeValue($value)
    {
        if (is_file($value)) {
            return [base64_encode(file_get_contents($value))];
        }

        if (is_string($value) && str_contains($value, ',')) {
            return explode(',', $value);
        }

        if (is_array($value)) {
            return $value;
        }

        return (string) $value;
    }
}
