<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use DateTime;

class LiveChannelController extends Controller
{
    //
    // public function __construct()
    // {
    //     $this->middleware('auth.admin');
    // }

    public function activechannellistgraph(Request $req){
        $channels = Channel::all();
        $activeChannels =[];
        $number_of_user=[];
        $points=[];
        if(count($channels) > 0){
            foreach ($channels as $c){
                $viewlogs = ViewLog::where('channel_id',$c->id)
                            ->whereNull('finished_watching_at')->get();
                $user_count = 0;
                // if(count($viewlogs) > 0){
                    foreach($viewlogs as $v){
                        if ($req->userType == "STB") {
                            $user = User::where('id', $v->user_id)
                                ->where('type', $req->userType)
                                ->where('address', 'like', '%' . $req->region . '%')
                                ->where('gender', 'like', '%' . $req->gender . '%')
                                ->where('economic_status', 'like', '%' . $req->economic . '%')
                                ->where('socio_status', 'like', '%' . $req->socio . '%')
                                ->whereBetween('age', [$req->age1, $req->age2])
                                ->first();
                        } else if($req->userType == "OTT"){
                            $user = User::where('id', $v->user_id)
                                ->where('type', $req->userType)
                                ->first();
                        }else {
                            $user = User::where('id', $v->user_id)
                                ->first();
                        }
                        
                        // $user= User::where('id',$v->user_id)
                        //         ->where('type','like','%'.$req->userType.'%')
                        //         ->where('address','like','%'.$req->region.'%')
                        //         ->where('gender','like','%'.$req->gender.'%')
                        //         ->where('economic_status','like','%'.$req->economic.'%')
                        //         ->where('socio_status','like','%'.$req->socio.'%')
                        //         ->whereBetween('age',[$req->age1,$req->age2])
                        //         ->first();
                        if($user){
                            $user_count = $user_count + 1;
                            $uu = array("id"=>$user->device->id, "title"=>$user->device->device_name, "lat"=>$user->device->lat, "lng"=>$user->device->lng);
                            array_push($points,$uu);
                            
                        }
                        else{
                            continue;
                        }
                    }
                    // if($user_count > 0){
                        // $activeChannel =[
                        //     "channel_id" => $c->id,
                        //     "channel_name" => $c->channel_name,
                        //     "channel_logo" => $c->logo,
                        //     "user_count" => $user_count
                        // ];
                        array_push($activeChannels,$c->channel_name);
                        array_push($number_of_user,$user_count);
                    // }
                    
                // }
                
            }
        }
        
        return response()->json(["channels"=>$activeChannels,"user_count"=>$number_of_user,"points"=>$points],200);
    }
}
