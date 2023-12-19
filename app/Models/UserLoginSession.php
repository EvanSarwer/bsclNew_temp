<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Login;

class UserLoginSession extends Model
{
    use HasFactory;
    protected $table = 'user_login_sessions';
    public $timestamps = false;
    protected $guarded = [];


    public function login()
    {
        return $this->belongsTo(Login::class, 'user_id');
    }

}
