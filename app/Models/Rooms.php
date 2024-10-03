<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TourDetails;
use App\Models\User;

class Rooms extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "rooms";
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'room_code', 'updated_at', 'created_by', 'tour_id', 'expiration_time'];
}
