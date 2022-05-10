<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    public function packages(){
        return $this->belongsToMany(Package::class)->withPivot('channel_number');
    }

    public function operators(){
        return $this->belongsToMany(Operator::class)->withPivot('channel_number');
    }

    public function watchedBy(){
        return $this->belongsToMany(User::class, 'view_logs');
    }

    public function viewLogs(){
        return $this->hasMany(ViewLog::class);
    }

    public function activeViewersCount(){
        return ViewLog::where('channel_id', $this->id)->where('finished_watching_at', null)->count();
    }
}
