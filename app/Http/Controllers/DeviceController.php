<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
    
}
