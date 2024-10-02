<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TourDetails;
use App\Models\User;

class RoomUsers extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "room_users";
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'password', 'role', 'created_at', 'is_active', 'user_id'];
}
