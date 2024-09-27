<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetMe extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $connection = 'mysql2';
    protected $table = "meetme";
    protected $fillable = ['exten', 'options', "userpin", "adminpin", "description", "joinmsg_id", "music", "users"];
}
