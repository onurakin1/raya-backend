<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SipDevices extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $connection = 'mysql2';
    protected $table = 'devices';
    protected $fillable = ['id', 'tech', 'dial', 'devicetype', 'user', 'description', 'emergency_cid'];
}
