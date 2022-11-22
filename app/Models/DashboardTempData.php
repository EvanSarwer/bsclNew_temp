<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardTempData extends Model
{
    use HasFactory;
    protected $table = 'dashboard_temp_data';
    public $timestamps = false;
}
