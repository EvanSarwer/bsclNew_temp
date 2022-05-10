<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    public function users(){
        return $this->hasMany(User::class);
    }

    public function channels(){
        return $this->belongsToMany(Channel::class)->withPivot('channel_number');
    }

    public function operator(){
        return $this->belongsTo(Operator::class);
    }
}
