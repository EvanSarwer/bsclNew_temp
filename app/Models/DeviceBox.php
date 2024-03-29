<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Device;
use App\Models\DeviceHistoryLog;

class DeviceBox extends Model
{
    use HasFactory;
    protected $table = 'device_boxes';
    public $timestamps = false;
    protected $guarded = [];


    public function device(){
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function deviceHistoryLogs()
    {
        return $this->hasMany(DeviceHistoryLog::class, 'box_id');
    }
}
