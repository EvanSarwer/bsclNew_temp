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
    public function activechannellistgraph(Request $req){
        $channels = Channel::all();
        $activeChannels =[];
        $number_of_user=[];
        if(count($channels) > 0){
            foreach ($channels as $c){
                $viewlogs = ViewLog::where('channel_id',$c->id)
                            ->whereNull('finished_watching_at')->get();
                $user_count = 0;
                // if(count($viewlogs) > 0){
                    foreach($viewlogs as $v){
                        $user= User::where('id',$v->user_id)
                                ->where('address','like','%'.$req->region.'%')->first();
                        if($user){
                            $user_count = $user_count + 1;
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
        
        return response()->json(["channels"=>$activeChannels,"user_count"=>$number_of_user],200);
    }
}