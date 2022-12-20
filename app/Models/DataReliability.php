<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataReliability extends Model
{
    use HasFactory;
    protected $table = 'data_reliability';
    public $timestamps = false;
}
