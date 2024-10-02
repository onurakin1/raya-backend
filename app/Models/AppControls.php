<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppControls extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "app_controls";
    protected $primaryKey = 'id';
    protected $fillable = ['type', 'content_type', 'version', 'contents', 'update_link', 'button_title', 'button_action_type', 'is_can_close', 'is_active', 'created_at',  'updated_at', 'deleted_at'];
}
