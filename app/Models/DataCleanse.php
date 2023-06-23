<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataCleanse extends Model
{
    use HasFactory;
    protected $table = "data_cleanse";
    public $timestamps = false;
}
