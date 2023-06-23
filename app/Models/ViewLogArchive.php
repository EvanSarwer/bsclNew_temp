<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewLogArchive extends Model
{
    use HasFactory;
    protected $table = 'view_logs_archive';
    public $timestamps = false;
}
