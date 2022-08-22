<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Device extends Model
{
    use HasFactory;
    protected $table = 'devices';
    public $timestamps = false;
    public function user(){
        return $this->hasMany(User::class);
    }
}
