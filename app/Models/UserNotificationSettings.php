<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotificationSettings extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "customer_notification_settings";
    protected $primaryKey = 'id';
    protected $fillable = ['customer_id', 'notification_setting_id', 'status', 'created_at', 'updated_at', 'deleted_at'];
}
