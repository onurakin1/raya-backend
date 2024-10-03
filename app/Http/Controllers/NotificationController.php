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

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Veritabanından tüm verileri al
        $notification_settings = NotificationSettings::all();
    
        // Sadece id, title ve is_active (status) alanlarını içeren yeni bir yapı oluştur
        $filtered_data = $notification_settings->map(function($item) {
            return [
                'id' => $item->id,
                'title' => json_decode($item->title)->tr, // Title'ı JSON olarak çözüp 'tr' alanını alıyoruz
                'status' => (bool) $item->is_active // is_active'i status olarak döndürüyoruz
            ];
        });
    
        // Dönüştürülmüş veriyi JSON olarak döndür
        return response()->json([
            'status' => true,
            'message' => 'The transaction was completed successfully.',
            'data' => [
                'items' => $filtered_data
            ]
        ]);
    }

    public function StatusNotificationSetting(Request $request)
    {
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

        // Dönüştürülmüş veriyi JSON olarak döndür
        return response()->json([
            'status' => true,
            'message' => 'notification settings status changed',
            'data' => [
                'notification_id' => $notification_settings_status->notification_setting_id,
                'status' =>(bool) $notification_settings_status->status,
                

            ]
        ]);
    }


    public function Notification(Request $request)
    {
        // Giriş yapan kullanıcının ID'sini al
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
    
        // Dönüştürülmüş veriyi JSON olarak döndür
        return response()->json([
            'status' => true,
            'message' => 'Successfully',
            'data' => $notifications
        ]);
    }

    public function DisableNotification(Request $request)
    {
        // Giriş yapan kullanıcının ID'sini al
        $user = $request->user();
        $userId = $user->id;
    
        $id = $request->query('id');
        $existNoti = Notifications::find($id);

        $notification_status = GuideToNotifications::create([
            'user_id' => $userId,
            'notification_id' => $existNoti->id
        ]);

    
        // Dönüştürülmüş veriyi JSON olarak döndür
        return response()->json([
            'status' => true,
            'message' => 'Successfully',
            'data' => $user,
            'id' =>  $notification_status
        ]);
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
