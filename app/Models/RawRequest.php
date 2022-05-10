<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawRequest extends Model
{
    use HasFactory;
    protected $table = 'raw_requests';
    public $timestamps = false;
}
