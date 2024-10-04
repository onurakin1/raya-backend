<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TourDetails;
use App\Models\User;

class UserDevices extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "user_devices";
    protected $primaryKey = 'id';
    protected $fillable = ['app_version', 'device_id', 'device_model', 'device_version', 'device_type', 'user_id', 'created_at', 'updated_at', 'deleted_at'];
}
