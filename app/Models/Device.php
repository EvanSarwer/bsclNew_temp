<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\DeselectPeriod;

class Device extends Model
{
    use HasFactory;
    protected $table = 'devices';
    public $timestamps = false;
    protected $fillable = [
        'device_name','address','lat','lng', 'type', 'economic_status', 'socio_status'
    ];

    public function users(){
        return $this->hasMany(User::class);
    }

    public function deselectPeriods(){
        return $this->hasMany(DeselectPeriod::class, 'device_id');
    }


}
