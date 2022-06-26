<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Login;

class Token extends Model
{
    use HasFactory;

    protected $table = "tokens";
    public $timestamps = false;

    public function login()
    {
        return $this->belongsTo(Login::class, 'user_id');
    }
}
