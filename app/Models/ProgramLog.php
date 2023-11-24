<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramLog extends Model
{
    use HasFactory;
    protected $table="program_logs";
    public $timestamps = false;
    
}
