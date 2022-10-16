<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdTrp extends Model
{
    use HasFactory;
    protected $table="adtrps";
    public $timestamps = false;
    protected $fillable = ['commercial_name', 'program','channel_id','date','start','finish','timewatched','duration','tvrp','tvr0','reach0','reachp'];
}
