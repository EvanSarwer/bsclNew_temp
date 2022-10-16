<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayoutFile extends Model
{
    use HasFactory;
    protected $table="playout_files";
    protected $fillable = ['channel_id','date'];

}
