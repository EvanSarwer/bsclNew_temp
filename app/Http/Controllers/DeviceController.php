<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Datetime;

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
    public function deviceUserList(){
        $data = User::all();
        foreach($data as $d){
            if($d->gender == "m"){
                $d->gender = "Male";
            }elseif($d->gender == "f"){
                $d->gender= "Female";
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

    public function addDeviceUser(Request $req){    
        $validator = Validator::make($req->all(),$this->rules());
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        
        $user = (object)$req->all();
        //return response()->json(["message"=>$user->user_name]);
        User::create((array)$user);

        return response()->json(["message"=>"Device User Created Successfully"]);
    }

    public function editDeviceUser(Request $req){
        
        $rules = array_diff_key($this->rules(), array_flip((array) ['user_name']));
        $validator = Validator::make($req->all(),$rules);
        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }
        $user= User::where('user_name',$req->user_name)->first();
        if($user->lat == ""){
            if($req->lat != ""){
                $user->update(["address"=>$req->address,"lat"=>$req->lat,"lng"=>$req->lng,"type"=>$req->type,"age"=>$req->age,"gender"=>$req->gender,"socio_status"=>$req->socio_status,"economic_status"=>$req->economic_status,"updated_at"=>new Datetime()]);
            }
        }else{
            $user->update(["address"=>$req->address,"type"=>$req->type,"age"=>$req->age,"gender"=>$req->gender,"socio_status"=>$req->socio_status,"economic_status"=>$req->economic_status,"updated_at"=>new Datetime()]);
        }
        
        
        return response()->json(["message"=>"Device User Updated Successfully"]);
    }

    function getDeviceUser($user_name){
        $user = User::where('user_name',$user_name)->first();
        return response()->json($user);
    }

    public function deleteDeviceUser(Request $req){
        $user= User::where('user_name',$req->user_name)->first();
        $user->delete();
        
        return response()->json(["message"=>"User Deleted Successfully"]);
    }

    function rules(){
        return[
            "user_name"=>"required|unique:users,user_name",
            "address"=>"required",
            "type"=>"required",
            "gender"=>"required",
            "economic_status"=>"required",
            "socio_status"=>"required",
            "age"=>"required"
        ];
    }
    
}
