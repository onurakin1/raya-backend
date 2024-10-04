<?php

namespace App\Http\Controllers;

use App\Models\Intro;
use App\Http\Requests\StoreIntroRequest;
use App\Http\Requests\UpdateIntroRequest;
use App\Models\GuideToNotifications;
use App\Models\Notifications;
use Illuminate\Http\Request;
use App\Models\NotificationSettings;
use App\Models\UserNotificationSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function NotificationSettings(Request $request)
    {
        try{
            $user = $request->user();
            $userId = $user->id;
            $languageType = $request->header('Accept-Language');

            $notification_settings = DB::table('notification_settings')
            ->join('user_notification_settings', 'notification_settings.id', '=', 'user_notification_settings.notification_setting_id')
            ->where('user_notification_settings.user_id', $userId)
            ->select('notification_settings.*', 'user_notification_settings.status') // İhtiyacınıza göre hangi alanları seçeceğinizi ayarlayın
            ->get();
       
    
            // Sadece id, title ve is_active (status) alanlarını içeren yeni bir yapı oluştur
            $filtered_data = $notification_settings->map(function($item) {
                return [
                    'id' => $item->id,
                    'title' => json_decode($item->title)->tr, // Title'ı JSON olarak çözüp 'tr' alanını alıyoruz
                    'status' => (bool) $item->is_active // is_active'i status olarak döndürüyoruz
                ];
            });
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            // Dönüştürülmüş veriyi JSON olarak döndür
            return response()->json([
                'status' => true,
                'message' => $successMessage,
                'data' => [
                    'items' => $filtered_data
                ]
            ]);
        }
        catch (\Exception $e) {
            // Dil bilgisine göre hata mesajını ayarla
            $errorMessage = ($languageType === 'tr') ? 'Sunucu hatası oluştu' : 'Server error occurred';
    
            // Hata durumunda JSON yanıtı döndür
            return response()->json([
                'status' => false,
                'message' => $errorMessage, // Hata mesajı
            ], 500); // 500 sunucu hatası kodu
        }

    }

    public function StatusNotificationSetting(Request $request)
    {
        try{
            $languageType = $request->header('Accept-Language');
            $user = $request->user();
            $status = $request->query('status');
            $id = $request->query('id');
            $today = Carbon::today();
            $notification_settings_status = UserNotificationSettings::create([
                'user_id' => $user->id,
                'notification_setting_id' => $id,
                'status' => $status == true ? 1 : 0,
                'created_at' => $today,
                'updated_at' => $today,
            ]);
    
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return response()->json([
                'status' => true,
                'message' => $successMessage,
                'data' => [
                    'notification_id' => $notification_settings_status->notification_setting_id,
                    'status' =>(bool) $notification_settings_status->status,
                    
    
                ]
            ]);
        }
        catch (\Exception $e) {
            // Dil bilgisine göre hata mesajını ayarla
            $errorMessage = ($languageType === 'tr') ? 'Sunucu hatası oluştu' : 'Server error occurred';
    
            // Hata durumunda JSON yanıtı döndür
            return response()->json([
                'status' => false,
                'message' => $errorMessage, // Hata mesajı
            ], 500); // 500 sunucu hatası kodu
        }

    }


    public function Notification(Request $request)
    {
        
        try{
            $languageType = $request->header('Accept-Language');
            $user = $request->user();
            $userId = $user->id;
        
      
            $notifications = Notifications::whereDoesntHave('guides', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->get();
            // Bildirimleri bir dizi içine dönüştür
            $notificationData = [];
            
            foreach ($notifications as $notification) {
                $notificationData[] = [
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'date' => $notification->created_at // Tarihi istediğiniz formatta döndür
                ];
            }
        
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return response()->json([
                'status' => true,
                'message' =>  $successMessage,
                'data' => [
                    'items' => $notifications
                ]
                
                
            ]);
        }
        catch (\Exception $e) {
            // Dil bilgisine göre hata mesajını ayarla
            $errorMessage = ($languageType === 'tr') ? 'Sunucu hatası oluştu' : 'Server error occurred';
    
            // Hata durumunda JSON yanıtı döndür
            return response()->json([
                'status' => false,
                'message' => $errorMessage, // Hata mesajı
            ], 500); // 500 sunucu hatası kodu
        }

    }

    public function DisableNotification(Request $request)
    {
        $languageType = $request->header('Accept-Language');
        try{
            $user = $request->user();
            $userId = $user->id;
        
            $id = $request->query('id');
            $existNoti = Notifications::find($id);
    
            $notification_status = GuideToNotifications::create([
                'user_id' => $userId,
                'notification_id' => $existNoti->id
            ]);
    
        
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return response()->json([
                'status' => true,
                'message' => $successMessage,
                'data' => $user,
                'id' =>  $notification_status
            ]);
        }
        catch (\Exception $e) {
            // Dil bilgisine göre hata mesajını ayarla
            $errorMessage = ($languageType === 'tr') ? 'Sunucu hatası oluştu' : 'Server error occurred';
    
            // Hata durumunda JSON yanıtı döndür
            return response()->json([
                'status' => false,
                'message' => $errorMessage, // Hata mesajı
            ], 500); // 500 sunucu hatası kodu
        }
 
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIntroRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Intro $intro)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIntroRequest $request, Intro $intro)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Intro $intro)
    {
        //
    }
}
