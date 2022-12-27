<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDataFilter extends Model
{
    use HasFactory;
    protected $table = 'user_data_filter';
    public $timestamps = false;

}
