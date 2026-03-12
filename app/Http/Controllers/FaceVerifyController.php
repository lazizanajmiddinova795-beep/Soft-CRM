<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FaceVerifyController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'image' => 'required|string'
        ]);

        $user = auth()->user();
        
        $targetPhoto = null;
        if ($user->face_id_token && str_starts_with($user->face_id_token, '/storage/')) {
            $targetPhoto = $user->face_id_token;
        } elseif ($user->avatar && str_starts_with($user->avatar, '/storage/')) {
            $targetPhoto = $user->avatar;
        }
        
        if (!$targetPhoto) {
            return response()->json(['success' => false, 'message' => 'Profile or Face ID photo missing in database. Access Denied.']);
        }

        // Remove the data URI scheme if present (data:image/jpeg;base64,...)
        $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $request->image);
        
        try {
            // Read target photo from storage. The url is typically '/storage/folder/filename.ext'
            $photoPath = str_replace('/storage/', '', $targetPhoto);
            $photoContent = \Illuminate\Support\Facades\Storage::disk('public')->get($photoPath);
            $base64Avatar = base64_encode($photoContent);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Could not process database ID photo.']);
        }
        
        $apiKey = 'AIzaSyDErlgJn3UcfYYAT0TSbRiRk672PX42UL0'; 
        
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => 'You are a strict security biometric system. Look at the two images provided. The first is a live camera feed (might be low quality). The second is an ID photo. Do they clearly show the EXACT same person? Focus on facial bone structure, eye proportions, and unique facial landmarks even if the live image has noise, bad lighting, or blur. If it is the same person, reply ONLY with "YES". If it is definitively a different person or the face is completely covered/missing, reply ONLY with "NO".'
                        ],
                        [
                            'inlineData' => [
                                'mimeType' => 'image/jpeg',
                                'data' => $base64Image // live feed
                            ]
                        ],
                        [
                            'inlineData' => [
                                'mimeType' => 'image/jpeg',
                                'data' => $base64Avatar // ID photo
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.0,
                'maxOutputTokens' => 10
            ]
        ];
        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey, $payload);
        
        if ($response->successful()) {
            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'NO';
            if (str_contains(strtoupper($text), 'YES')) {
                return response()->json(['success' => true, 'message' => "Tizimga xush kelibsiz " . strtoupper($user->name) . ", kelganingiz uchun rahmat!"]);
            }
            return response()->json(['success' => false, 'message' => 'Identity mismatch or invalid face detected. Access denied.']);
        }
        
        return response()->json(['success' => false, 'message' => 'Neural Network Error. Try again.', 'details' => $response->body()], 500);
    }
}
