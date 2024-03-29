<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdTrp extends Model
{
    use HasFactory;
    protected $table="adtrps";
    public $timestamps = false;
    protected $fillable = ['commercial_name','channel_id','channel_name','date','start','finish','timewatched','duration','tvrp','tvr0','reach0','reachp','c1','c2','c3','c4','c5','c6','c7','c8','c9','c10','playlog_id'];

    public function channel(){
        return $this->belongsTo(Channel::class, 'channel_id');
    }

}
