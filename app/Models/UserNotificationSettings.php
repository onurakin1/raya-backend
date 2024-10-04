<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\NotificationSettings;
use App\Models\User;

class UserNotificationSettings extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "user_notification_settings";
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'notification_setting_id', 'status', 'created_at', 'updated_at', 'deleted_at'];

    public function notification_setting()
    {
        return $this->belongsTo(NotificationSettings::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
