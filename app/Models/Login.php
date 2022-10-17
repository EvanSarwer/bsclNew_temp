<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AppUser;

class Login extends Model
{
    use HasFactory;
    protected $table="login";
    protected $fillable =['user_name', 'email','password','role','created_by'];

    public function appUser(){
        return $this->belongsTo(AppUser::class,'user_name');
    }
}
