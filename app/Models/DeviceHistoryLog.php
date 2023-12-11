<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DeviceBox;
use App\Models\Device;

class DeviceHistoryLog extends Model
{
    use HasFactory;
    protected $table = 'device_history_log';
    public $timestamps = false;
    protected $guarded = [];


    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function deviceBox()
    {
        return $this->belongsTo(DeviceBox::class, 'box_id');
    }
}
