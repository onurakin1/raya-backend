<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use App\Models\User;

class CompanyToGuide extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "company_to_guide";
    protected $primaryKey = 'id';
    protected $fillable = ['company_id', 'user_id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
