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
                            'text' => 'CRITICAL SECURITY PROTOCOL: You are a high-security biometric face verification system. Compare the face in the first image (live webcam capture) with the face in the second image (reference ID photo). Focus strictly on unique facial structures: bone framework, eye distance, nose bridge shape, jawline, and lip alignment. Even if there is noise, lighting differences, or slight angle variations, do NOT authorize unless they are unequivocally the EXACT SAME PERSON. If they are different people, or if the face is not fully visible, or if you have any doubt, you MUST reject. Reply ONLY with the word "YES" to authorize, or ONLY with the word "NO" to reject. Do not output any other text or punctuation.'
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
                'maxOutputTokens' => 5
            ]
        ];
        
        $response = Http::timeout(30)->withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey, $payload);
        
        if ($response->successful()) {
            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'NO';
            $cleanText = trim(str_replace(['"', "'", '`', '*', '.', "\n", "\r", ' '], '', strtoupper($text)));
            
            if ($cleanText === 'YES') {
                return response()->json(['success' => true, 'message' => "Tizimga xush kelibsiz " . strtoupper($user->name) . ", kelganingiz uchun rahmat!"]);
            }
            return response()->json(['success' => false, 'message' => 'Identity mismatch or invalid face detected. Access denied.']);
        }
        
        return response()->json(['success' => false, 'message' => 'Neural Network Error. Try again.', 'details' => $response->body()], 500);
    }

    public function verifyLoginFace(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'image' => 'required|string',
        ]);

        if (!\Illuminate\Support\Facades\Auth::validate($request->only('email', 'password'))) {
            return response()->json(['success' => false, 'message' => 'Noto\'g\'ri login yoki parol!']);
        }

        $user = \App\Models\User::where('email', $request->email)->first();

        // Check if user is blocked or pending approval
        if ($user->approval_status === 'pending') {
            return response()->json(['success' => false, 'message' => 'Sizning hisobingiz hali tasdiqlanmagan. Iltimos kuting.']);
        }
        if ($user->approval_status === 'rejected') {
            return response()->json(['success' => false, 'message' => 'Sizning arizangiz rad etilgan.']);
        }
        if ($user->status === 'blocked') {
            return response()->json(['success' => false, 'message' => 'Sizning hisobingiz administrator tomonidan BLOKLANGAN.']);
        }

        $targetPhoto = null;
        if ($user->face_id_token && str_starts_with($user->face_id_token, '/storage/')) {
            $targetPhoto = $user->face_id_token;
        } elseif ($user->avatar && str_starts_with($user->avatar, '/storage/')) {
            $targetPhoto = $user->avatar;
        }

        if (!$targetPhoto) {
            return response()->json(['success' => false, 'message' => 'Tizimda yuz rasmingiz topilmadi. Iltimos, admin bilan ro\'yxatdan o\'ting.']);
        }

        $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $request->image);
        
        try {
            $photoPath = str_replace('/storage/', '', $targetPhoto);
            $photoContent = \Illuminate\Support\Facades\Storage::disk('public')->get($photoPath);
            $base64Avatar = base64_encode($photoContent);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Tizim rasmini o\'qib bo\'lmadi.']);
        }

        $apiKey = 'AIzaSyDErlgJn3UcfYYAT0TSbRiRk672PX42UL0'; 
        
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => 'CRITICAL SECURITY PROTOCOL: You are a high-security biometric face verification system. Compare the face in the first image (live webcam capture) with the face in the second image (reference ID photo). Focus strictly on unique facial structures: bone framework, eye distance, nose bridge shape, jawline, and lip alignment. Even if there is noise, lighting differences, or slight angle variations, do NOT authorize unless they are unequivocally the EXACT SAME PERSON. If they are different people, or if the face is not fully visible, or if you have any doubt, you MUST reject. Reply ONLY with the word "YES" to authorize, or ONLY with the word "NO" to reject. Do not output any other text or punctuation.'
                        ],
                        [
                            'inlineData' => [
                                'mimeType' => 'image/jpeg',
                                'data' => $base64Image
                            ]
                        ],
                        [
                            'inlineData' => [
                                'mimeType' => 'image/jpeg',
                                'data' => $base64Avatar
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.0,
                'maxOutputTokens' => 5
            ]
        ];

        $response = \Illuminate\Support\Facades\Http::timeout(30)->withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey, $payload);

        if ($response->successful()) {
            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'NO';
            $cleanText = trim(str_replace(['"', "'", '`', '*', '.', "\n", "\r", ' '], '', strtoupper($text)));
            
            if ($cleanText === 'YES') {
                return response()->json(['success' => true]);
            }
            return response()->json(['success' => false, 'message' => 'Yuz mos kelmadi! Ruxsat etilmadi.']);
        }

        return response()->json(['success' => false, 'message' => 'Sun\'iy intellekt xatosi yuz berdi. Qayta urining.'], 500);
    }
}
