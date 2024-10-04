<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSettings extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "notification_settings";
    protected $primaryKey = 'id';
    protected $fillable = ['title', 'is_active', 'row_number', 'created_at', 'updated_at', 'deleted_at'];

    public function status()
    {
        return $this->belongsToMany(User::class, 'user_notification_settings', 'notification_setting_id', 'user_id');
    }
}
