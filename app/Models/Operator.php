<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    use HasFactory;
    public function channels(){
        return $this->belongsToMany(Channel::class)->withPivot('channel_number');
    }

    public function users(){
        return $this->hasMany(User::class);
    }

    public function packages(){
        return $this->hasMany(Package::class);
    }
}
