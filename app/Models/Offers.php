<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TourDetails;
use App\Models\User;

class Offers extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "offers";
    protected $primaryKey = 'id';
    protected $fillable = ['full_name', 'email', 'phone_number', 'tax_plate', 'signature_circular', 'created_at', 'deleted_at'];
}
