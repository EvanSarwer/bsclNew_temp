<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdLog extends Model
{
    use HasFactory;
    protected $table="ad_logs";
    public $timestamps = false;
    
}
