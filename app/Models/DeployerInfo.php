<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeployerInfo extends Model
{
    use HasFactory;
    protected $table = 'deployer_info';
    public $timestamps = false;
    protected $fillable = ['user_name', 'organization_name','designation','email','number','alt_number','doj', 'dob', 'nid', 'employee_id', 'description', 'house_name', 'house_number', 'road_number', 'state_name', 'district_name', 'division_name', 'created_at'];
}
