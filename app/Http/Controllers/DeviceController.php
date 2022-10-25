<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Device;
use App\Models\DeselectPeriod;
use App\Models\DeselectLog;
use App\Models\ViewLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Datetime;
use Carbon\Carbon;

class DeviceController extends Controller
{
    public function servertime()
    {
        $ldate = date('Y-m-d H:i:s');
        return response()->json(["value" => $ldate], 200);
    }
    public function tvoff()
    {
        $tvOff = array();
        $ldate = date('Y-m-d H:i:s');
        $devices = Device::where('type', "STB")
            ->where('tvoff', 0)->select("id", "device_name", "last_request")->get();
        if ($devices) {
            foreach ($devices as $d) {
                if (abs(strtotime($d->last_request) - strtotime($ldate)) <= 30) {
                    array_push($tvOff, $d);
                }
            }
            return response()->json(["data" => $tvOff], 200);
        }
    }

    public function deviceOff()
    {
        $offdevice = array();
        $ldate = date('Y-m-d H:i:s');
        $devices = Device::where('type', "STB")
            ->select("id", "device_name", "last_request")->get();
        //return response()->json(["data" => $user], 200);
        if ($devices) {
            foreach ($devices as $d) {
                if (abs(strtotime($d->last_request) - strtotime($ldate)) > 30 || $d->last_request == null) {

                    array_push($offdevice, $d);
                }
            }
            return response()->json(["data" => $offdevice], 200);
        }
    }

    public function currentlyWatching()
    {
        $cw = array();
        $ldate = date('Y-m-d H:i:s');
        $devices = Device::where('type', "STB")
            ->where('tvoff', 1)->select("id", "device_name", "last_request")->get();
        if ($devices) {
            foreach ($devices as $d) {
                if (abs(strtotime($d->last_request) - strtotime($ldate)) <= 27) {
                    array_push($cw, $d);
                }
            }
            return response()->json(["data" => $cw], 200);
        }
    }

    public function deviceList()
    {
        $data = Device::all();
        foreach ($data as $d) {
            $user_deselect = DeselectPeriod::where('device_id', $d->id)->whereNotNull('start_date')
                ->whereNull('end_date')->first();
            if ($user_deselect) {
                $d->deselect = "deselect";
            } else {
                $d->deselect = "";
            }

            if ($d->economic_status == "a") {
                $d->economic_status = "Poorest";
            } elseif ($d->economic_status == "b") {
                $d->economic_status = "Poorer";
            } elseif ($d->economic_status == "c") {
                $d->economic_status = "Middle";
            } elseif ($d->economic_status == "d") {
                $d->economic_status = "Richer";
            } elseif ($d->economic_status == "e") {
                $d->economic_status = "Richest";
            }

            if ($d->socio_status == "u") {
                $d->socio_status = "Urban";
            } elseif ($d->socio_status == "r") {
                $d->socio_status = "Rural";
            }
        }
        return response()->json($data);
    }

    public function addDevice(Request $req)
    {
        $validator = Validator::make($req->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $device = (object)$req->all();
        $device->survey_date= date('Y-m-d H:i:s');
        $device->installation_date= date('Y-m-d H:i:s');
        //return response()->json(["message"=>$user->user_name]);
        $d= Device::create((array)$device);

        return response()->json(["device_id"=>$d->id, "message" => "Device Created Successfully"],200);
    }

    public function editDevice(Request $req)
    {
        $rules = array_diff_key($this->rules(), array_flip((array) ['device_name']));
        $validator = Validator::make($req->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $device = Device::where('id', $req->id)->first();

        // if($user->lat == ""){
        //     if($req->lat != ""){
        //         $user->update(["address"=>$req->address,"lat"=>$req->lat,"lng"=>$req->lng,"type"=>$req->type,"age"=>$req->age,"gender"=>$req->gender,"socio_status"=>$req->socio_status,"economic_status"=>$req->economic_status,"updated_at"=>new Datetime()]);
        //     }
        // }else{
        //     $user->update(["address"=>$req->address,"type"=>$req->type,"age"=>$req->age,"gender"=>$req->gender,"socio_status"=>$req->socio_status,"economic_status"=>$req->economic_status,"updated_at"=>new Datetime()]);
        // }

        if($req->socio_status == "r"){
            $req->city_corporation = "";
            $req->city_name = "";
        }

        if($req->wifi == "n"){
            $req->wifi_signal_strength = "";
        }


        if($req->socio_status == "u"){
            if($req->household_condition == "Flat owner / Flat in apartment" ){
                if($req->monthly_income == "a"){
                    $req->economic_status = "b";
                }else if($req->monthly_income == "b"){
                    $req->economic_status = "c";
                }else if($req->monthly_income == "c"){
                    $req->economic_status = "d";
                }else if($req->monthly_income == "d"){
                    $req->economic_status = "d";
                }else if($req->monthly_income == "e"){
                    $req->economic_status = "e";
                }

            }else if($req->household_condition == "Rented flat / Non-Flat apartment where there is no security guard and parking" ){
                if($req->monthly_income == "a"){
                    $req->economic_status = "a";
                }else if($req->monthly_income == "b"){
                    $req->economic_status = "b";
                }else if($req->monthly_income == "c"){
                    $req->economic_status = "c";
                }else if($req->monthly_income == "d"){
                    $req->economic_status = "d";
                }else if($req->monthly_income == "e"){
                    $req->economic_status = "d";
                }
            }else if($req->household_condition == "Lower tier house" ){
                if($req->monthly_income == "a"){
                    $req->economic_status = "a";
                }else if($req->monthly_income == "b"){
                    $req->economic_status = "a";
                }else if($req->monthly_income == "c"){
                    $req->economic_status = "b";
                }else if($req->monthly_income == "d"){
                    $req->economic_status = "c";
                }else if($req->monthly_income == "e"){
                    $req->economic_status = "d";
                }
            }

        }else if($req->socio_status == "r"){
            if($req->household_condition == "Full Concrete house (wall, floor and roof)" ){
                if($req->monthly_income == "a"){
                    $req->economic_status = "b";
                }else if($req->monthly_income == "b"){
                    $req->economic_status = "c";
                }else if($req->monthly_income == "c"){
                    $req->economic_status = "d";
                }else if($req->monthly_income == "d"){
                    $req->economic_status = "d";
                }else if($req->monthly_income == "e"){
                    $req->economic_status = "e";
                }

            }else if($req->household_condition == "Semi Concrete house (wall and floor concrete but the roof is made by Tin)" ){
                if($req->monthly_income == "a"){
                    $req->economic_status = "a";
                }else if($req->monthly_income == "b"){
                    $req->economic_status = "b";
                }else if($req->monthly_income == "c"){
                    $req->economic_status = "c";
                }else if($req->monthly_income == "d"){
                    $req->economic_status = "d";
                }else if($req->monthly_income == "e"){
                    $req->economic_status = "d";
                }
            }else if($req->household_condition == "Non-Concrete (Made by Tin/Wood/Bamboo etc.)" ){
                if($req->monthly_income == "a"){
                    $req->economic_status = "a";
                }else if($req->monthly_income == "b"){
                    $req->economic_status = "a";
                }else if($req->monthly_income == "c"){
                    $req->economic_status = "b";
                }else if($req->monthly_income == "d"){
                    $req->economic_status = "c";
                }else if($req->monthly_income == "e"){
                    $req->economic_status = "d";
                }
            }
        }






        $device->update(["type" => $req->type, "contact_person" => $req->contact_person, "contact_email" => $req->contact_email, "contact_number" => $req->contact_number, "alt_number" => $req->alt_number, "payment_type" => $req->payment_type, "payment_number" => $req->payment_number, "other_payment_type" => $req->other_payment_type, "other_payment_number" => $req->other_payment_number,
                         "house_name" => $req->house_name, "house_number" => $req->house_number, "road_number" => $req->road_number, "state_name" => $req->state_name, "ward_no" => $req->ward_no, "zone_thana" => $req->zone_thana, "city_corporation" => $req->city_corporation, "city_name" => $req->city_name, "zip_code" => $req->zip_code, "district" => $req->district, "lat" => $req->lat, "lng" => $req->lng,
                         "household_condition" => $req->household_condition, "description" => $req->description, "tv_type" => $req->tv_type, "tv_brand" => $req->tv_brand, "tv_placement" => $req->tv_placement, "gsm_signal_strength" => $req->gsm_signal_strength, "wifi" => $req->wifi, "wifi_signal_strength" => $req->wifi_signal_strength, "stb_provider_name" => $req->stb_provider_name, "stb_subscription_type" => $req->stb_subscription_type, "stb_subscription_charge" => $req->stb_subscription_charge, 
                         "socio_status" => $req->socio_status,"economic_status" => $req->economic_status, "monthly_income" => $req->monthly_income, "installer_name" => $req->installer_name, "survey_date" => new Datetime(), "installation_date"=> new Datetime(),  "updated_at" => new Datetime()]);

        return response()->json(["message" => "Device Updated Successfully"]);
    }

    function getDevice($device_id)
    {
        $device = Device::where('id', $device_id)->first();
        //$dUser = $device->users;
        //$deviceUser = $dUser->sortBy('user_index');
        $deviceUser = array();

        foreach ($device->users as $du) {

            if ($du->gender == "m") {
                $du->gender = "Male";
            } elseif ($du->gender == "f") {
                $du->gender = "Female";
            }

            if ($du->economic_status == "a") {
                $du->economic_status = "Poorest";
            } elseif ($du->economic_status == "b") {
                $du->economic_status = "Poorer";
            } elseif ($du->economic_status == "c") {
                $du->economic_status = "Middle";
            } elseif ($du->economic_status == "d") {
                $du->economic_status = "Richer";
            } elseif ($du->economic_status == "e") {
                $du->economic_status = "Richest";
            }

            if ($du->monthly_income == "a") {
                $du->monthly_income = "Income below 10,000 Taka";
            } elseif ($du->monthly_income == "b") {
                $du->monthly_income = "Income 10,000 to 39,999 Taka";
            } elseif ($du->monthly_income == "c") {
                $du->monthly_income = "Income 40,000 to 69,999 Taka";
            } elseif ($du->monthly_income == "d") {
                $du->monthly_income = "Income 70,000 to 99,999 Taka";
            } elseif ($du->monthly_income == "e") {
                $du->monthly_income = "Income above 1,00,000 Taka";
            }

            $du->age = Carbon::parse($du->dob)->diff(Carbon::now())->y;

            //dd($age. " Years"); // To check result

            if ($du->socio_status == "u") {
                $du->socio_status = "Urban";
            } elseif ($du->socio_status == "r") {
                $du->socio_status = "Rural";
            }

            array_push($deviceUser,$du);
        }
        array_multisort(array_column($deviceUser, 'user_index'), SORT_ASC, $deviceUser);

        return response()->json(["device" => $device, "deviceUser" => $deviceUser], 200);
    }

    public function deleteDevice(Request $req)
    {
        $device = Device::where('id', $req->id)->first();
        $device->delete();

        return response()->json(["message" => "Device Deleted Successfully"]);
    }

    function rules()
    {
        return [
            "device_name" => "required|unique:devices,device_name",
            "lat"=>"required",
            "lng"=>"required",
            "monthly_income" => "required",
            "socio_status" => "required",
            "contact_person" => "required",
            "contact_email" => "required",
            "contact_number" => "required",
            "payment_type" => "required",
            "payment_number" => "required",
            "house_name" => "required",
            "house_number" => "required",
            "road_number" => "required",
            "state_name" => "required",
            "ward_no" => "required",
            "zone_thana" => "required",
            "zip_code" => "required",
            "installer_name" => "required",
            "district" => "required",
            "household_condition" => "required",
            "tv_type" => "required",
            "tv_brand" => "required",
            "tv_placement" => "required",
            "gsm_signal_strength" => "required",
            "wifi" => "required",
            //"wifi_signal_strength" => "required",
            "stb_provider_name" => "required",
            "stb_subscription_type" => "required",
            "stb_subscription_charge" => "required",
            "type" => "required",
            //"age"=>"required"
        ];
    }

    public function deselectDevice(Request $req)
    {

        if ($req->deselect == "deselect") {
            $device_deselect_period = DeselectPeriod::where('device_id', $req->device_id)->whereNotNull('start_date')->whereNull('end_date')->first();
            if ($device_deselect_period) {
                return response()->json(["message" => "Device Already Deselected"]);
            } else {
                $user_deselect = new DeselectPeriod();
                $user_deselect->device_id = $req->device_id;
                $user_deselect->save();

                $user = User::where('device_id', $req->device_id)->first();   //For Old Device Request Only
                $log = ViewLog::where('user_id', $user->id)
                    ->where('finished_watching_at', NULL)->first();
                if ($log) {
                    $log->finished_watching_at = Carbon::now()->toDateTimeString();;
                    $time = new DateTime($log->started_watching_at);
                    $diff = $time->diff(new DateTime($log->finished_watching_at));
                    $minutes = ($diff->days * 24 * 60) +
                        ($diff->h * 60) + $diff->i;
                    $minutes = $minutes > 999 ? 999 : $minutes;
                    $log->duration_minute = $minutes;
                    $log->save();

                    $var = new DeselectLog;
                    $var->user_id = $user->id;
                    $var->channel_id = $log->channel_id;
                    $var->started_watching_at = new Datetime();
                    $var->save();

                    return response()->json(["message" => "User Deselected & Log Changed"]);
                }
                return response()->json(["message" => "Device Deselected & Log Not Affected"]);
            }
        } elseif ($req->deselect == "") {
            $device_deselect_period = DeselectPeriod::where('device_id', $req->device_id)->whereNotNull('start_date')->whereNull('end_date')->first();
            if ($device_deselect_period) {
                $device_deselect_period->update(["end_date" => new Datetime()]);

                $user = User::where('device_id', $req->device_id)->first();   //For Old Device Request Only
                $Deselect_log = DeselectLog::where('user_id', $user->id)
                    ->where('finished_watching_at', NULL)->first();
                if ($Deselect_log) {
                    $Deselect_log->finished_watching_at = Carbon::now()->toDateTimeString();;
                    $time = new DateTime($Deselect_log->started_watching_at);
                    $diff = $time->diff(new DateTime($Deselect_log->finished_watching_at));
                    $minutes = ($diff->days * 24 * 60) +
                        ($diff->h * 60) + $diff->i;
                    $minutes = $minutes > 999 ? 999 : $minutes;
                    $Deselect_log->duration_minute = $minutes;
                    $Deselect_log->save();

                    $var = new ViewLog;
                    $var->user_id = $user->id;
                    $var->channel_id = $Deselect_log->channel_id;
                    $var->started_watching_at = new Datetime();
                    $var->save();

                    return response()->json(["message" => "User Deselection Released & Log Changed"]);
                }
                return response()->json(["message" => "Device Deselection Released & Log Not Affected"]);
            }
            return response()->json(["message" => "Already User Deselection Released"]);
        }
    }

    public function addDeviceUser(Request $req)
    {
        $validator = Validator::make($req->all(), $this->userRules());
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = (object)$req->all();
        $user->type = "STB";
        //return response()->json(["message"=>$user]);
        User::create((array)$user);
       
        return response()->json(["message" => "Device User Created Successfully"]);
    }

    public function editDeviceUser(Request $req)
    {

        $rules = array_diff_key($this->userRules(), array_flip((array) ['user_name', 'device_id', 'user_index']));
        $validator = Validator::make($req->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user = User::where('id', $req->id)->first();

        // if($user->lat == ""){
        //     if($req->lat != ""){
        //         $user->update(["address"=>$req->address,"lat"=>$req->lat,"lng"=>$req->lng,"type"=>$req->type,"age"=>$req->age,"gender"=>$req->gender,"socio_status"=>$req->socio_status,"economic_status"=>$req->economic_status,"updated_at"=>new Datetime()]);
        //     }
        // }else{
        //     $user->update(["address"=>$req->address,"type"=>$req->type,"age"=>$req->age,"gender"=>$req->gender,"socio_status"=>$req->socio_status,"economic_status"=>$req->economic_status,"updated_at"=>new Datetime()]);
        // }

        $user->update(["type" => "STB", "dob" => $req->dob, "gender" => $req->gender, "updated_at" => new Datetime()]);


        return response()->json(["message" => "Device User Updated Successfully"]);
    }

    function getDeviceUser($user_id)
    {
        $user = User::where('id', $user_id)->first();
        return response()->json($user);
    }

    public function deleteDeviceUser(Request $req)
    {
        $user = User::where('id', $req->id)->first();
        $user->delete();

        return response()->json(["message" => "User Deleted Successfully"]);
    }

    function userRules()
    {
        return [
            "user_name" => "required",
            // "address" => "required",
            // "type" => "required",
            "gender" => "required",
            // "economic_status" => "required",
            // "socio_status" => "required",
            // "age" => "required",
            "dob" => "required",
            "device_id" => "required",
            "user_index" => "required"
        ];
    }
}
