<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;
    public $channel_reach;

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
    public function playoutLogs(){
        return $this->hasMany(PlayoutLog::class, 'channel_id');
    }
    public function playoutFile(){
        return $this->hasMany(PlayoutFile::class, 'channel_id');
    }
    public function adTrp(){
        return $this->hasMany(AdTrp::class, 'channel_id');
    }
    public function daypart(){
        return $this->hasMany(DayPart::class, 'channel_id');
    }

    public function deselectLogs(){
        return $this->hasMany(DeselctLog::class, 'channel_id');
    }







    public function activeViewersCount(){
        return ViewLog::where('channel_id', $this->id)->where('finished_watching_at', null)->count();
    }
    public function reach($startDate,$startTime,$finishDate,$finishTime){
        $reach = ViewLog::where('channel_id', $this->id)
                ->where(function($query) use ($finishDate, $finishTime,$startDate,$startTime){
                $query->where('finished_watching_at','>',date($startDate)." ".$startTime)
                ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at','<',date($finishDate)." ".$finishTime)
                ->select('user_id')
                ->distinct('user_id')
                ->get();
        $this->channel_reach = count($reach);
        return count($reach);
        
    }
}
