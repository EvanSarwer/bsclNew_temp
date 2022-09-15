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

            if ($d->economic_status == "a1") {
                $d->economic_status = "Lower Class";
            } elseif ($d->economic_status == "b1") {
                $d->economic_status = "Upper Class";
            } elseif ($d->economic_status == "c1") {
                $d->economic_status = "Upper Middle Class";
            } elseif ($d->economic_status == "d1") {
                $d->economic_status = "Lower Middle Class";
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
        //return response()->json(["message"=>$user->user_name]);
        Device::create((array)$device);

        return response()->json(["message" => "Device Created Successfully"]);
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
        $device->update(["address" => $req->address, "lat" => $req->lat, "lng" => $req->lng, "type" => $req->type, "socio_status" => $req->socio_status, "economic_status" => $req->economic_status, "updated_at" => new Datetime()]);

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

            if ($du->economic_status == "a1") {
                $du->economic_status = "Lower Class";
            } elseif ($du->economic_status == "b1") {
                $du->economic_status = "Upper Class";
            } elseif ($du->economic_status == "e1") {
                $du->economic_status = "Middle Class";
            } elseif ($du->economic_status == "c1") {
                $du->economic_status = "Upper Middle Class";
            } elseif ($du->economic_status == "d1") {
                $du->economic_status = "Lower Middle Class";
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
            "address" => "required",
            "type" => "required",
            "lat"=>"required",
            "lng"=>"required",
            "economic_status" => "required",
            "socio_status" => "required",
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
        //return response()->json(["message"=>$user->user_name]);
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

        $user->update(["dob" => $req->dob, "gender" => $req->gender, "updated_at" => new Datetime()]);


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
            "user_name" => "required|unique:users,user_name",
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
