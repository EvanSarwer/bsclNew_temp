<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\DeselectPeriod;
use App\Models\RawRequest;
use App\Models\DeviceBox;

class Device extends Model
{
    use HasFactory;
    protected $table = 'devices';
    protected $fillable = [
        'device_name','lat','lng', 'type', 'economic_status','monthly_income', 'socio_status', 'contact_person', 'contact_number', 'contact_email', 'alt_number','payment_type','payment_number', 'other_payment_type', 'other_payment_number', 'house_name', 'house_number', 'road_number', 'state_name', 'ward_no', 'zone_thana', 'city_corporation', 'city_name', 'zip_code', 'district', 'household_condition', 'installer_name', 'survey_date', 'installation_date', 'description', 'tv_type', 'tv_brand', 'tv_placement', 'gsm_signal_strength', 'wifi', 'wifi_signal_strength', 'stb_provider_name', 'stb_subscription_type', 'stb_subscription_charge'
    ];

    public function users(){
        return $this->hasMany(User::class, 'device_id');
    }

    public function deselectPeriods(){
        return $this->hasMany(DeselectPeriod::class, 'device_id');
    }

    public function rawRequests(){
        return $this->hasMany(RawRequest::class, 'device_id');
    }

    public function deviceBox()
    {
        return $this->hasOne(DeviceBox::class, 'device_id');
    }


}
