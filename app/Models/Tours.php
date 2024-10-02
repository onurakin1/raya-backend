<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TourDetails;
use App\Models\User;

class Tours extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "tours";
    protected $primaryKey = 'id';
    protected $fillable = ['tour_code', 'name', 'created_at',  'updated_at'];

    public function details()
    {
        return $this->hasMany(TourDetails::class, 'tour_id');
    }
    public function guides()
    {
        return $this->belongsToMany(User::class, 'tour_to_guide', 'tour_id', 'user_id');
    }
}
