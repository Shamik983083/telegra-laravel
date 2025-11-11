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

    $url = "https://dev-core-ias-rest.telegramd.com/questionnaireInstances/{$questionnaireId}";
    $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6eyJfaWQiOiJ1c3I6OjNjZTQyMjFlLWNkOWUtNGJlMi1iNjU0LTI1NjI3MjI3NmJmMyIsImVtYWlsIjoiZGV2QGNvZGVjbG91ZHMuYml6IiwicGFzc3dvcmQiOiIkMmIkMDkkVUk3enRpaWcvMk56ME1oUDU3Z1Q2dXJFZGFwME1aMjE4UmFBdFVBYnpyREczMGk0TUhLczIiLCJzdGF0dXMiOiJhY3RpdmUiLCJwaG9uZSI6IjEyMTIxMjEyMTIiLCJuYW1lIjoiQ29kZSBDbG91ZHMiLCJmaXJzdE5hbWUiOiJDb2RlIiwibWlkZGxlTmFtZSI6IiIsImxhc3ROYW1lIjoiQ2xvdWRzIiwicm9sZSI6ImFmZmlsaWF0ZS1zdXBlci1hZG1pbiIsInBpY3R1cmUiOiJodHRwczovL2dyYXZhdGFyLmNvbS9hdmF0YXIvMzE2ZWI3ZWY4N2E1NGYyOTZhMjQyOGVhZDAxNWE2NWY_ZD1pZGVudGljb24iLCJhZmZpbGlhdGUiOiJhZmY6OjVjMWMyNjMzLWRiNWEtNDI5NC1hNjIwLTIzZDNkOWNjNDg2NCIsInBhc3N3b3JkUmVzZXRSZXF1aXJlZCI6ZmFsc2UsInNldHRpbmdzIjp7ImZpbHRlcnMiOltdfSwidHdvRmFjdG9yVHlwZSI6ImVtYWlsIiwiZGVsZXRlZCI6ZmFsc2UsImtleXdvcmRzIjpbImRldiBjb2RlY2xvdWRzIGJpeiIsIjEyMTIxMjEyMTIiLCJjb2RlIGNsb3VkcyJdLCJjcmVhdGVkQXQiOiIyMDI1LTEwLTI3VDE1OjUwOjU0Ljc2NFoiLCJ1cGRhdGVkQXQiOiIyMDI1LTEwLTI4VDEwOjAwOjUzLjE2MFoiLCJwYXNzd29yZFVwZGF0ZWRBdCI6IjIwMjUtMTAtMjhUMDk6NTg6NTcuMjYxWiIsIm90cFNlY3JldCI6IlpDRjI0Rk8yUUpLV1ZQRk9BSURGTUtYSDUzTEgySDIyIiwib3RwVXJpIjoib3RwYXV0aDovL3RvdHAvVGVsZWdyYSUyME1EJTNBQ29kZSUyMENsb3Vkcz9zZWNyZXQ9WkNGMjRGTzJRSktXVlBGT0FJREZNS1hINTNMSDJIMjImaXNzdWVyPVRlbGVncmElMjBNRCIsImZ1bGxOYW1lIjoiQ29kZSBDbG91ZHMiLCJpZCI6InVzcjo6M2NlNDIyMWUtY2Q5ZS00YmUyLWI2NTQtMjU2MjcyMjc2YmYzIn0sImlhdCI6MTc2Mjc3NDk0MH0.WAhsaEXSngjkrR_m1kUznF8yanXe_tN3buduBWJ-QJw'; // Replace with your valid token

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

    // ✅ Extract questions from `locations`
    $questions = $data['questionnaire']['locations'] ?? [];

    return view('questionnaire', [
        'product' => $product,
        'questions' => $questions,
        'rawResponse' => $data,
    ]);
}

}
