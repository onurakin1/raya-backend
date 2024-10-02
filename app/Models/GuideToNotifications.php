<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notifications;
use App\Models\User;

class GuideToNotifications extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "guide_to_notification";
    protected $primaryKey = 'id';
    protected $fillable = ['notification_id', 'user_id'];

    public function notification()
    {
        return $this->belongsTo(Notifications::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
