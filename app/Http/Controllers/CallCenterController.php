<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CallCenter;
use Carbon\Carbon;

class CallCenterController extends Controller
{

    public function CallCenter(Request $request)
    {
        try {
            $languageType = $request->header('Accept-Language');
            $today = Carbon::today();
            $user = $request->user();
            $userId = $user->id;
    
            // Fetch the user's message from CallCenter
            $UsersMessage = CallCenter::where('user_id', $userId)->get();
    
            // Prepare the base data
            $responseData = [
                'message' =>  $UsersMessage
            ];
    
            // Check if 'with_phone' is present in the query string and equals 1
            if ($request->query('with_phone') == 1) {
                $phoneInfo = [
                    'message' => 'Dilerseniz aşağıdaki buton ile bize çağrı merkezimizden de ulaşabilirsiniz.',
                    'phone' => '0 (850) 532 5678',
                    'number' => '+908505325678'
                ];
    
                // Append the phone information to the response data
                $responseData['phone'] = $phoneInfo;
            }
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'data' => $responseData
            ], 201);
        } catch (\Exception $e) {
            // Dil bilgisine göre hata mesajını ayarla
            $errorMessage = ($languageType === 'tr') ? 'Sunucu hatası oluştu' : 'Server error occurred';
    
            // Hata durumunda JSON yanıtı döndür
            return response()->json([
                'status' => false,
                'message' => $errorMessage, // Hata mesajı
            ], 500); // 500 sunucu hatası kodu
        }
    }
    


    public function SendMessage(Request $request) 
    {
        try {
            $languageType = $request->header('Accept-Language');
            $today = Carbon::today();
            $user = $request->user();
            $userId = $user->id;
            $content = $request->content;

            $createdMessage = CallCenter::create([
                'user_id' => $userId,
                'content' => $content,
                'created_at' => $today,
                'is_active' => true,
                'sender_type' => 2

            ]);
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return response()->json([
                'success' => true,
                'message' =>  $successMessage,

                'data' => [
                    'message' =>  [
                        'id' => $createdMessage->id,
                        'content' => $createdMessage->content,
                        'type' => $createdMessage->sender_type,
                        'date' => $createdMessage->created_at
                    ]
                ]


            ], 201);
        } catch (\Exception $e) {
            // Dil bilgisine göre hata mesajını ayarla
            $errorMessage = ($languageType === 'tr') ? 'Sunucu hatası oluştu' : 'Server error occurred';
    
            // Hata durumunda JSON yanıtı döndür
            return response()->json([
                'status' => false,
                'message' => $errorMessage, // Hata mesajı
            ], 500); // 500 sunucu hatası kodu
        }
    }
}
