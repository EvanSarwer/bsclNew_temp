<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Login;

class AppUser extends Model
{
    use HasFactory;
    protected $table = "app_users";
    protected $fillable = ['user_name', 'email', 'password', 'address', 'phone', 'created_by'];

    public function login()
    {
        return $this->hasOne(Login::class, 'user_name');
    }
}
