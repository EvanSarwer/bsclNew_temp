<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Login;

class PasswordReset extends Model
{
    use HasFactory;

    protected $table = "password_resets";
    public $timestamps = false;

    public function login()
    {
        return $this->belongsTo(Login::class, 'email');
    }
}
