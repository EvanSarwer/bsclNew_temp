<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayPartProcess extends Model
{
    use HasFactory;
    protected $table="dayparts_process";
    public $timestamps = false;
    protected $fillable = ['channel_id','day','type','time_range'];
}
