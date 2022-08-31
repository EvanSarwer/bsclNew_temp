<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Device;

class DeselectPeriod extends Model
{
    use HasFactory;
    protected $table = 'deselect_periods';
    public $timestamps = false;
    protected $fillable =['device_id', 'start_date','end_date'];

    public function device(){
        return $this->belongsTo(Device::class, 'device_id');
    }
}
