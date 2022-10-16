<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayoutLog extends Model
{
    use HasFactory;
    protected $table="playout_logs";
    public $timestamps = false;
    protected $fillable = ['channel_id','commercial_name', 'program','date','start','finish','duration','playout_id'];

}
