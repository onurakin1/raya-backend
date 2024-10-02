<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agreements extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "agreements";
    protected $primaryKey = 'id';
    protected $fillable = ['show_login', 'title', 'description', 'order_number', 'is_active', 'is_required', 'created_at', 'updated_at', 'deleted_at'];
}
