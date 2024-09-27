<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SipUsers extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $connection = 'mysql2';
    protected $table = 'users';
    protected $fillable = ['extension', 'password', 'name', 'voicemail', 'ringtimer', 'noanswer', 'recording', 'outboundcid', 'sipname', 'mohclass', 'noanswer_cid', 'busy_cid'];
}
