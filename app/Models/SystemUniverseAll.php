<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemUniverseAll extends Model
{
    use HasFactory;
    protected $table="system_universe_all";
    public $timestamps = false;
    protected $fillable = [
        'date_of_gen', // Add any other fields that are fillable here
        'Gender',
        'Region',
        'Sec',
        'Age_Group',
        'Universe',
    ];

}
