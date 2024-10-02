<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tours;
use App\Models\User;

class TourToGuide extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "tour_to_guide";
    protected $primaryKey = 'id';
    protected $fillable = ['tour_id', 'user_id'];

    public function tour()
    {
        return $this->belongsTo(Tours::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
