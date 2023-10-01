<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Device;

class DeviceBox extends Model
{
    use HasFactory;
    protected $table = 'device_boxes';
    public $timestamps = false;
    protected $guarded = [];


    public function device(){
        return $this->belongsTo(Device::class, 'device_id');
    }
}
