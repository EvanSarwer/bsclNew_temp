<?php

namespace App\Models;
use App\Models\Login;
use App\Models\UserLoginSession;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    use HasFactory;
    protected $table = 'user_activity_log';
    public $timestamps = false;
    protected $guarded = [];

    public function login()
    {
        return $this->belongsTo(Login::class, 'user_id');
    }

    public function userLoginSession()
    {
        return $this->belongsTo(UserLoginSession::class, 'session_id');
    }
}
