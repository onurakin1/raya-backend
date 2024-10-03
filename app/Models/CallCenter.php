<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallCenter extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "call_center_messages";
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'content', 'sender_type', 'is_active', 'created_at', 'updated_at', 'deleted_at'];
}
