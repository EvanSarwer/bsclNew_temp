<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AppUser;
use App\Models\DeployerInfo;
use App\Models\Token;
use App\Models\UserLoginSession;


class Login extends Model
{
    use HasFactory;
    protected $table="login";
    protected $fillable =['user_name', 'email','password','role','created_by'];

    public function appUser(){
        return $this->belongsTo(AppUser::class,'user_name','user_name');
    }

    public function deployerUser(){
        return $this->belongsTo(DeployerInfo::class,'user_name','user_name');
    }

    public function tokens(){
        return $this->hasMany(Token::class,'user_id');
    }

    public function userLoginSessions(){
        return $this->hasMany(UserLoginSession::class,'user_id');
    }
}
