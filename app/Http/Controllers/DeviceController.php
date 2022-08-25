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
    public function tvoff()
    {
        $user = User::where('type', "STB")
        ->where('tvoff', 0)->select("id","user_name")->get();
        if ($user) {
            return response()->json(["data" => $user], 200);
        }
    }
    public function deviceOff()
    {
        $offdevice=array();
        $ldate = date('Y-m-d H:i:s');
        $user = User::where('type', "STB")
        ->select("id","user_name","last_request")->get();
        //return response()->json(["data" => $user], 200);
        if ($user) {
            foreach ($user as $u) {
                if(abs(strtotime($u->last_request) - strtotime($ldate))>600||$u->last_request==null){
                    
                    array_push($offdevice,$u);
                }
            }
            return response()->json(["data" => $offdevice], 200);
        }
    }
    public function currentlyWatching()
    {
        $cw=array();
        $ldate = date('Y-m-d H:i:s');
        $user = User::where('type', "STB")
        ->select("id","user_name","last_request")->get();
        //return response()->json(["data" => $user], 200);
        if ($user) {
            foreach ($user as $u) {
                if(abs(strtotime($u->last_request) - strtotime($ldate))<180  && $u->last_request!=null){
                    
                    array_push($cw,$u);
                }
            }
            return response()->json(["data" => $cw], 200);
        }
    }
    public function deviceList(){
        $data = Device::all();
        foreach($data as $d){
            $user_deselect = DeselectPeriod::where('user_id',$d->id)->whereNotNull('start_date')
                                            ->whereNull('end_date')->first();
            if($user_deselect){
                $d->deselect = "deselect";
            }else{
                $d->deselect = "";
            }

            if($d->economic_status == "a1"){
                $d->economic_status = "Lower Class";
            }elseif($d->economic_status == "b1"){
                $d->economic_status = "Upper Class";
            }elseif($d->economic_status == "c1"){
                $d->economic_status = "Upper Middle Class";
            }elseif($d->economic_status == "d1"){
                $d->economic_status= "Lower Middle Class";
            }

            if($d->socio_status == "u"){
                $d->socio_status = "Urban";
            }elseif($d->socio_status == "r"){
                $d->socio_status = "Rural";
            }

        }
        return response()->json($data);
    }

    public function addDevice(Request $req){    
        $validator = Validator::make($req->all(),$this->rules());
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        
        $device = (object)$req->all();
        //return response()->json(["message"=>$user->user_name]);
        Device::create((array)$device);

        return response()->json(["message"=>"Device Created Successfully"]);
    }

    public function editDevice(Request $req){
        
        $rules = array_diff_key($this->rules(), array_flip((array) ['device_name']));
        $validator = Validator::make($req->all(),$rules);
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        $device= Device::where('id',$req->id)->first();

        // if($user->lat == ""){
        //     if($req->lat != ""){
        //         $user->update(["address"=>$req->address,"lat"=>$req->lat,"lng"=>$req->lng,"type"=>$req->type,"age"=>$req->age,"gender"=>$req->gender,"socio_status"=>$req->socio_status,"economic_status"=>$req->economic_status,"updated_at"=>new Datetime()]);
        //     }
        // }else{
        //     $user->update(["address"=>$req->address,"type"=>$req->type,"age"=>$req->age,"gender"=>$req->gender,"socio_status"=>$req->socio_status,"economic_status"=>$req->economic_status,"updated_at"=>new Datetime()]);
        // }
        $device->update(["address"=>$req->address,"lat"=>$req->lat,"lng"=>$req->lng,"type"=>$req->type,"socio_status"=>$req->socio_status,"economic_status"=>$req->economic_status,"updated_at"=>new Datetime()]);
        
        return response()->json(["message"=>"Device Updated Successfully"]);
    }

    function getDevice($device_id){
        $device = Device::where('id',$device_id)->first();
        $dUser = $device->users;
        $deviceUser= $dUser->sortBy('user_index');

        return response()->json([ "device"=>$device,"deviceUser"=>$deviceUser],200);
    }

    public function deleteDevice(Request $req){
        $device= Device::where('id',$req->id)->first();
        $device->delete();
        
        return response()->json(["message"=>"Device Deleted Successfully"]);
    }

    function rules(){
        return[
            "device_name"=>"required|unique:devices,device_name",
            "address"=>"required",
            "type"=>"required",
            //"gender"=>"required",
            "economic_status"=>"required",
            "socio_status"=>"required",
            //"age"=>"required"
        ];
    }

    public function deselectuser(Request $req){

        if($req->deselect == "deselect"){
            $user_deselect_period = DeselectPeriod::where('user_id',$req->user_id)->whereNotNull('start_date')->whereNull('end_date')->first();
            if($user_deselect_period){  
                return response()->json(["message"=>"User Already Deselected"]);
            }else{
                $user_deselect = new DeselectPeriod();
                $user_deselect->user_id = $req->user_id;
                $user_deselect->save();

                $log = ViewLog::where('user_id',$req->user_id)
                        ->where('finished_watching_at',NULL)->first();
                if($log){
                    $log->finished_watching_at = Carbon::now()->toDateTimeString();;
                    $time = new DateTime($log->started_watching_at);
                    $diff = $time->diff(new DateTime($log->finished_watching_at));
                    $minutes = ($diff->days * 24 * 60) +
                            ($diff->h * 60) + $diff->i;
                    $minutes = $minutes>999 ? 999:$minutes;          
                    $log->duration_minute = $minutes;
                    $log->save();

                    $var=new DeselectLog;
                    $var->user_id = $req->user_id;
                    $var->channel_id = $log->channel_id;
                    $var->started_watching_at = new Datetime();
                    $var->save();

                    return response()->json(["message"=>"User Deselected & Log Changed"]);
                }
                return response()->json(["message"=>"User Deselected & Log Not Affected"]);

            }

        }elseif($req->deselect == ""){
            $user_deselect_period = DeselectPeriod::where('user_id',$req->user_id)->whereNotNull('start_date')->whereNull('end_date')->first();
            if($user_deselect_period){  
                $user_deselect_period->update(["end_date"=>new Datetime()]);

                $Deselect_log = DeselectLog::where('user_id',$req->user_id)
                        ->where('finished_watching_at',NULL)->first();
                if($Deselect_log){
                    $Deselect_log->finished_watching_at = Carbon::now()->toDateTimeString();;
                    $time = new DateTime($Deselect_log->started_watching_at);
                    $diff = $time->diff(new DateTime($Deselect_log->finished_watching_at));
                    $minutes = ($diff->days * 24 * 60) +
                            ($diff->h * 60) + $diff->i;
                    $minutes = $minutes>999 ? 999:$minutes;          
                    $Deselect_log->duration_minute = $minutes;
                    $Deselect_log->save();

                    $var=new ViewLog;
                    $var->user_id = $req->user_id;
                    $var->channel_id = $Deselect_log->channel_id;
                    $var->started_watching_at = new Datetime();
                    $var->save();

                    return response()->json(["message"=>"User Deselection Released & Log Changed"]);
                }
                return response()->json(["message"=>"User Deselection Released & Log Not Affected"]);

            }
            return response()->json(["message"=>"Already User Deselection Released"]);

        }



    }
    
}
