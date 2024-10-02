<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    use HasFactory;

    // Modelin zaman damgalarını kullanmadığını belirtir.
    public $timestamps = false;

    // Veritabanı tablosunun adı
    protected $table = "notifications";

    // Modelin birincil anahtarının adı
    protected $primaryKey = 'id';

    // Modelin doldurulmasına izin verilen alanlar
    protected $fillable = [
        'title',
        'message',
        'created_at',
        'type',
        'related_id',
        'exist_id'
    ];

    // Kullanıcılarla çoktan çoğa ilişkisini tanımlar
    public function guides()
    {
        return $this->belongsToMany(User::class, 'guide_to_notification', 'notification_id', 'user_id');
    }
}
