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
    
            // Kullanıcının mesajlarını al
            $UsersMessage = CallCenter::where('user_id', $userId)->get();
    
            // Mesajları istenilen formatta düzenle
            $formattedMessages = $UsersMessage->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'type' => $message->sender_type, // sender_type direkt type olarak alındı
                    'date' => $message->created_at->format('d.m.Y H:i:s') // Tarih formatı
                ];
            });
    
            // Temel yanıt verilerini hazırla
            $responseData = [
                'message' => $formattedMessages
            ];
    
            // 'with_phone' sorgu parametresi kontrolü
            if ($request->query('with_phone') == 1) {
                $phoneInfo = [
                    'message' => 'Dilerseniz aşağıdaki buton ile bize çağrı merkezimizden de ulaşabilirsiniz.',
                    'phone' => '0 (850) 532 5678',
                    'number' => '+908505325678'
                ];
    
                // Telefon bilgilerini yanıt verisine ekle
                $responseData['phone'] = $phoneInfo;
            }
    
            // Başarı mesajı
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
            $today = Carbon::now(); // Tarihi ve saati şu anki zaman olarak ayarladık
            $user = $request->user();
            $userId = $user->id;
            $content = $request->content;
    
            // Kullanıcıya ait kayıt kontrolü
            $existingMessages = CallCenter::where('user_id', $userId)->first();
    
            // Eğer ilgili user_id için bir kayıt yoksa, belirtilen objeleri ekle
            if (!$existingMessages) {
                // Önceden tanımlanan iki mesajı ekle
                CallCenter::insert([
                    [
                        'user_id' => $userId,
                        'content' => 'Merhaba, size nasıl yardımcı olabiliriz ?',
                        'created_at' => $today,
                        'is_active' => true,
                        'sender_type' => 2 // Gönderen tipi 2
                    ],
                   
                ]);
            }
            
         
                $createdMessage = CallCenter::create([
                    'user_id' => $userId,
                    'content' => $content,
                    'created_at' => $today,
                    'is_active' => true,
                    'sender_type' => 1 // Kullanıcının kendi mesajı
                ]);
            
            // Kullanıcının gönderdiği mesajı kaydet
     
    
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'data' => [
                  
                        'id' => $createdMessage->id,
                        'content' => $createdMessage->content,
                        'type' => $createdMessage->sender_type,
                        'date' => $createdMessage->created_at->format('d.m.Y H:i:s') // Formatla
                    
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
