<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Company extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "company";
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'created_at'];

    public function guides()
    {
        return $this->belongsToMany(User::class, 'company_to_guide', 'company_id', 'user_id');
    }
}
