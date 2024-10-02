<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tours;

class TourDetails extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "tour_detail";
    protected $primaryKey = 'id';
    protected $fillable = ['description', 'materials', 'voice_rooms',  'tour_dates', 'tour_id'];

    public function tour()
    {
        return $this->belongsTo(Tours::class, 'tour_id');
    }
}
