<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TourDetails;
use App\Models\User;

class MenuItems extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "menu_items";
    protected $primaryKey = 'id';
    protected $fillable = ['parent_id', 'menu_id', 'title', 'value', 'seque', 'icon', 'type', 'link_type', 'is_active', 'order_number', 'created_at', 'updated_at', 'deleted_at'];
}
