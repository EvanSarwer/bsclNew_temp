<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Device;

class RawRequest extends Model
{
    use HasFactory;
    protected $table = 'raw_requests';
    public $timestamps = false;



    public function device(){
        return $this->belongsTo(Device::class, 'device_id');
    }
}
