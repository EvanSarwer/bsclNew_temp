<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayPart extends Model
{
    use HasFactory;
    protected $table="dayparts";
    public $timestamps = false;
    protected $fillable = ['channel_id','day','data','type','time_range'];

    public function channel(){
        return $this->belongsTo(Channel::class, 'channel_id');
    }
}
