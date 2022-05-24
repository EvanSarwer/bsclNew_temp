<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use DateTime;

class SarwerAPIController extends Controller
{
    public function testApi(Request $req){
        return response()->json(["user"=>$req->user,"time"=>$req->time],200);
    }

    public function ttestApi(Request $req){
        if($req->timeframe == "Daily"){
            $start = date('2022-05-19 00:00:00');
            $addmin = 120;
            $count = 12;
        }
        else if($req->timeframe == "Weekly"){
            $start = date('2022-05-19 00:00:00');
            $addmin = 1440;
            $count = 7;
        }
        


        $AllDateTime = [];
        for($i=0 ; $i<$count; $i++){
            //$addmin = $addmin + 120;
            
            $newtimestamp = strtotime("{$start} + {$addmin} minute");
            $finish = date('Y-m-d H:i:s', $newtimestamp);
            $dt = [
                "Start"=> $start,
                "finish"=> $finish
            ];
            $start = $finish;

            array_push($AllDateTime,$dt);
        }
        return response()->json(["All Date Time"=> $AllDateTime],200);
        

    }



    public function activechannellistget(){
        $channels = Channel::all();
        $activeChannels =[];
        foreach ($channels as $c){
            $viewlogs = ViewLog::where('channel_id',$c->id)
                        ->whereNull('finished_watching_at')->get();

            if(count($viewlogs) > 0){
                $activeChannel =[
                    "channel_id" => $c->id,
                    "channel_name" => $c->channel_name,
                    "channel_logo" => $c->logo,
                    "user_count" => count($viewlogs)
                ];
                array_push($activeChannels,$activeChannel);
            }
            
        }
        return response()->json(["activeChannels"=> $activeChannels],200);
    }

    public function activechannellist(Request $req){
        if($req->search != ""){
            $channels = Channel::where('channel_name','like','%'.$req->search.'%')
                        ->get();
        }
        else{
            $channels = Channel::all();
        }
        $activeChannels =[];
        if(count($channels) > 0){
            foreach ($channels as $c){
                $viewlogs = ViewLog::where('channel_id',$c->id)
                            ->whereNull('finished_watching_at')->get();
    
                if(count($viewlogs) > 0){
                    $activeChannel =[
                        "channel_id" => $c->id,
                        "channel_name" => $c->channel_name,
                        "channel_logo" => $c->logo,
                        "user_count" => count($viewlogs)
                    ];
                    array_push($activeChannels,$activeChannel);
                }
                
            }
        }
        
        return response()->json(["activeChannels"=> $activeChannels],200);
    }




    public function activeuserlistget(){
        $viewlogs = ViewLog::whereNull('finished_watching_at')
        ->distinct('user_id')->get();
        $activeUsers = [];
        if(count($viewlogs) > 0){
            foreach($viewlogs as $v){
                $user = User::where('id',$v->user_id)->first();
                $channel = Channel::where('id',$v->channel_id)->first();
                $activeUser = [
                    "user_id" => $user->id,
                    "user_name" => $user->user_name,
                    "channel_id" => $channel->id,
                    "channel_name" => $channel->channel_name,
                    "channel_logo" => $channel->logo
                ];
                array_push($activeUsers,$activeUser);
            }
        }
        return response()->json(["activeUsers"=>$activeUsers],200);

    }

    public function activeuserlist(Request $req){
        $viewlogs = ViewLog::whereNull('finished_watching_at')
        ->distinct('user_id')->get();
        $activeUsers = [];
        if(count($viewlogs) > 0){
            foreach($viewlogs as $v){

                if($req->search != ""){
                    $user = User::where('id',$v->user_id)->where('user_name','like','%'.$req->search.'%')->first();
                    if($user != null){
                        $channel = Channel::where('id',$v->channel_id)->first();
                        $activeUser = [
                            "user_id" => $user->id,
                            "user_name" => $user->user_name,
                            "channel_id" => $channel->id,
                            "channel_name" => $channel->channel_name,
                            "channel_logo" => $channel->logo
                        ];
                        array_push($activeUsers,$activeUser);
                    }
                    
                }
                else{
                    $user = User::where('id',$v->user_id)->first();
                    $channel = Channel::where('id',$v->channel_id)->first();
                    $activeUser = [
                        "user_id" => $user->id,
                        "user_name" => $user->user_name,
                        "channel_id" => $channel->id,
                        "channel_name" => $channel->channel_name,
                        "channel_logo" => $channel->logo
                    ];
                    array_push($activeUsers,$activeUser);

                }
                
            }
        }

        return response()->json(["activeUsers"=>$activeUsers],200);
    }

    public function tvrshare1p(Request $req){
        $channelArray=array();
        $shares=array();
        $viewer=array();
        
        $startDate=substr($req->start,0,10);
        $startTime=substr($req->start,11,19);
        $finishDate=substr($req->finish,0,10);
        $finishTime=substr($req->finish,11,19);
        $to_time = strtotime($startDate." ".$startTime);
        $from_time = strtotime($finishDate." ".$finishTime);
        $diff=abs($to_time - $from_time) / 60;
        
        $users=User::all();
        $numOfUser=$users->count();
        
        $all_tvr =array();
        $total =0.00;

        $channels=Channel::all('id','channel_name');
        foreach ($channels as $c) {
            $viewlogs = ViewLog::where('channel_id', $c->id)
            ->where('started_watching_at','>',date($startDate)." ".$startTime)
            ->where('finished_watching_at','<',date($finishDate)." ".$finishTime)
            ->get();
            foreach ($viewlogs as $v) {
                $timeviewed = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at))/60;
                array_push($viewer,$timeviewed);
            }

            $tvr=array_sum($viewer)/$numOfUser;
            $tvr=$tvr/$diff;
            $tvr=round($tvr,4);
            
            $total = ($total + $tvr);
            array_push($all_tvr,$tvr);
            array_push($channelArray,$c->channel_name);

        }
        $total_tvr = round($total,5);
        
        //$total_share= 0;
        for($i=0; $i< count($all_tvr); $i++){
            $s = ($all_tvr[$i]/$total_tvr)*100;
            //$total_share= $total_share+$s;
            array_push($shares,$s);
        }
        //return response()->json(["Total-tvr"=>$total_tvr,"all_tvr"=>$all_tvr,"total_share"=>$total_share,"share"=>$shares,"channels"=>$channelArray],200);
        return response()->json(["share"=>$shares,"channels"=>$channelArray],200);
    }


    public function tvr(Request $req){

        $startDate=substr($req->start,0,10);
        $startTime=substr($req->start,11,19);
        $finishDate=substr($req->finish,0,10);
        $finishTime=substr($req->finish,11,19);
        $to_time = strtotime($startDate." ".$startTime);
        $from_time = strtotime($finishDate." ".$finishTime);
        $diff=abs($to_time - $from_time) / 60;
        $users=User::all();
        $numOfUser=$users->count();
   
        $channelArray=array();
        $all_tvr =array();
        $total =0.00;

        $channels=Channel::all('id','channel_name');
        foreach ($channels as $c) {
            $tvr =0;
            $viewelogs = ViewLog::where('channel_id', $c->id)
            ->where('started_watching_at','<',date($finishDate)." ".$finishTime)
            ->where('finished_watching_at','>',date($startDate)." ".$startTime)
            ->get();
            $total_time_viewed = 0;
            foreach ($viewelogs as $v) {
                if(((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) > ($from_time))){
                    $watched_sec = abs($to_time - $from_time);
                }
                else if(((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) <= ($from_time))){
                    $watched_sec = abs($to_time - strtotime($v->finished_watching_at));
                }
                else if(((strtotime($v->started_watching_at)) >= ($to_time)) && ((strtotime($v->finished_watching_at)) > ($from_time))){
                    $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                }
                else{
                    $watched_sec = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                }
                $total_time_viewed = $total_time_viewed + $watched_sec;
                //$timeviewed = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at))/60;
            }
            $total_time_viewed = ($total_time_viewed)/60;
            $tvr = $total_time_viewed / $diff;
            $tvr=$tvr/$numOfUser;
            $tvr=$tvr*100;
            $tvr=round($tvr,4);
            
            $total = ($total + $tvr);
            array_push($all_tvr,$tvr);
            array_push($channelArray,$c->channel_name);

        }
        $total_tvr = round($total,5);

        return response()->json(["tvr"=>$all_tvr,"channels"=>$channelArray],200);
        //return response()->json(["share"=>$shares,"channels"=>$channelArray],200);

    }

    public function tvrshare(Request $req){
        
        $startDate=substr($req->start,0,10);
        $startTime=substr($req->start,11,19);
        $finishDate=substr($req->finish,0,10);
        $finishTime=substr($req->finish,11,19);
        $to_time = strtotime($startDate." ".$startTime);
        $from_time = strtotime($finishDate." ".$finishTime);
        $diff=abs($to_time - $from_time) / 60;
        $users=User::all();
        $numOfUser=$users->count();
   
        $channelArray=array();
        $shares=array();
        $all_tvr =array();
        $total =0.00;

        $channels=Channel::all('id','channel_name');
        foreach ($channels as $c) {
            $tvr =0;
            $viewelogs = ViewLog::where('channel_id', $c->id)
                        ->where(function($query) use ($finishDate, $finishTime,$startDate,$startTime){
                        $query->where('finished_watching_at','>',date($startDate)." ".$startTime)
                        ->orWhereNull('finished_watching_at');
                        })
                        ->where('started_watching_at','<',date($finishDate)." ".$finishTime)
                        ->get();
            $total_time_viewed = 0;
            foreach ($viewelogs as $v) {
                if(((strtotime($v->started_watching_at)) < ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null ) )){
                    $watched_sec = abs($to_time - $from_time);
                }
                else if(((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) <= ($from_time))){
                    $watched_sec = abs($to_time - strtotime($v->finished_watching_at));
                }
                else if(((strtotime($v->started_watching_at)) >= ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null ) )){
                    $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                }
                else{
                    $watched_sec = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                }
                $total_time_viewed = $total_time_viewed + $watched_sec;
                //$timeviewed = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at))/60;
                
            }
            $total_time_viewed = ($total_time_viewed)/60;
            $tvr = $total_time_viewed / $diff;
            $tvr=$tvr/$numOfUser;
            $tvr=$tvr*100;
            $tvr=round($tvr,4);
            
            $total = ($total + $tvr);
            array_push($all_tvr,$tvr);
            array_push($channelArray,$c->channel_name);

        }
        $total_tvr = round($total,5);
        
        $total_share= 0;
        for($i=0; $i< count($all_tvr); $i++){
            $s = ($all_tvr[$i]/$total_tvr)*100;
            $total_share= $total_share+$s;
            array_push($shares,$s);
        }
        //return response()->json(["Total-tvr"=>$total_tvr,"all_tvr"=>$all_tvr,"total_share"=>$total_share,"share"=>$shares,"channels"=>$channelArray],200);
        return response()->json(["share"=>$shares,"channels"=>$channelArray],200);
    }

    public function timespent(Request $req){
        
        $startDate=substr($req->start,0,10);
        $startTime=substr($req->start,11,19);
        $finishDate=substr($req->finish,0,10);
        $finishTime=substr($req->finish,11,19);
        $to_time = strtotime($startDate." ".$startTime);
        $from_time = strtotime($finishDate." ".$finishTime);
        $diff=abs($to_time - $from_time) / 60;

        $channelArray=array();
        $total_time =array();
        $total =0.00;

        $channels=Channel::all('id','channel_name');
        foreach ($channels as $c) {
            $viewlogs = ViewLog::where('channel_id', $c->id)
            ->where('started_watching_at','<',date($finishDate)." ".$finishTime)
            ->where('finished_watching_at','>',date($startDate)." ".$startTime)
            ->get();
            $total_time_viewed = 0;
            foreach ($viewlogs as $v) {
                if(((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) > ($from_time))){
                    $watched_sec = abs($to_time - $from_time);
                }
                else if(((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) <= ($from_time))){
                    $watched_sec = abs($to_time - strtotime($v->finished_watching_at));
                }
                else if(((strtotime($v->started_watching_at)) >= ($to_time)) && ((strtotime($v->finished_watching_at)) > ($from_time))){
                    $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                }
                else{
                    $watched_sec = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                }
                $total_time_viewed = $total_time_viewed + $watched_sec;
                //$timeviewed = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at))/60;
            }
            $total_time_viewed = ($total_time_viewed)/60;
            //$tota_time_viewed = $tota_time_viewed / $diff;
            $total_time_viewed=round($total_time_viewed);
            
            array_push($total_time,$total_time_viewed);
            array_push($channelArray,$c->channel_name);

        }
        return response()->json(["totaltime"=>$total_time,"channels"=>$channelArray],200);
    }

    public function usertimespent(Request $req){
        
        $startDate=substr($req->start,0,10);
        $startTime=substr($req->start,11,19);
        $finishDate=substr($req->finish,0,10);
        $finishTime=substr($req->finish,11,19);
        $to_time = strtotime($startDate." ".$startTime);
        $from_time = strtotime($finishDate." ".$finishTime);
        $diff=abs($to_time - $from_time) / 60;

        $channelArray=array();
        $total_time =array();
        $total =0.00;

        $channels=Channel::all('id','channel_name');
        foreach ($channels as $c) {
            $viewlogs = ViewLog::where('channel_id', $c->id)
            ->where('user_id', $req->userid)
            ->where('started_watching_at','<',date($finishDate)." ".$finishTime)
            ->where('finished_watching_at','>',date($startDate)." ".$startTime)
            ->get();
            $total_time_viewed = 0;
            foreach ($viewlogs as $v) {
                if(((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) > ($from_time))){
                    $watched_sec = abs($to_time - $from_time);
                }
                else if(((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) <= ($from_time))){
                    $watched_sec = abs($to_time - strtotime($v->finished_watching_at));
                }
                else if(((strtotime($v->started_watching_at)) >= ($to_time)) && ((strtotime($v->finished_watching_at)) > ($from_time))){
                    $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                }
                else{
                    $watched_sec = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                }
                $total_time_viewed = $total_time_viewed + $watched_sec;
                //$timeviewed = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at))/60;
            }
            $total_time_viewed = ($total_time_viewed)/60;
            //$tota_time_viewed = $tota_time_viewed / $diff;
            $total_time_viewed=round($total_time_viewed);
            
            array_push($total_time,$total_time_viewed);
            array_push($channelArray,$c->channel_name);

        }
        return response()->json(["totaltime"=>$total_time,"channels"=>$channelArray],200);
    }




    public function shareview1p(){
        return view("graph1.tvrshare1p");
    }
    public function tvrview(){
        return view("graph1.tvr");
    }

    public function shareview(){
        return view("graph1.tvrshare");
    }
    public function timespentview(){
        return view("graph1.timespent");
    }
    public function usertimespentview(){
        $users = User::all('id','user_name');
        return view("graph1.usertimespent")->with('users', $users);
    }




    public function tvrPrac(Request $req){

        $startDate=substr($req->start,0,10);
        $startTime=substr($req->start,11,19);
        $finishDate=substr($req->finish,0,10);
        $finishTime=substr($req->finish,11,19);
        $to_time = strtotime($startDate." ".$startTime);
        $from_time = strtotime($finishDate." ".$finishTime);
        $diff=abs($to_time - $from_time) / 60;
        //return response()->json(["tvr"=>$diff],200);

        $chnl_names = [];
        $ch_tvr = [];

        $channels=Channel::all('id','channel_name');
        //     return response()->json(["one-user-log"=>$viewlogs],200);  
        $chnl_info = [];  
        foreach ($channels as $c) {
            $tvr = 0;
            $viewlogs = ViewLog::where('channel_id', $c->id)
            ->where('started_watching_at','<',date($finishDate)." ".$finishTime)
            ->where('finished_watching_at','>',date($startDate)." ".$startTime)
            ->get();
            if((count($viewlogs)) > 0){
                foreach ($viewlogs as $v) {
                    $users=User::all();
                    $numOfUser=$users->count();
                    $user_info = [];

                    foreach($users as $u){
                        $viewloggs = ViewLog::where('id',$v->id)
                        ->where('user_id',$u->id)
                        ->get();
                        $user_watched_sec = 0;

                        if((count($viewloggs)) > 0 ){
                            foreach($viewloggs as $vls){
                                if(((strtotime($vls->started_watching_at)) < ($to_time)) && ((strtotime($vls->finished_watching_at)) > ($from_time))){
                                    $watched_sec = abs($to_time - $from_time);
                                    //return response()->json(["watched_sec"=> $watched_sec],200);
                                }
                                else if(((strtotime($vls->started_watching_at)) < ($to_time)) && ((strtotime($vls->finished_watching_at)) <= ($from_time))){
                                    $watched_sec = abs($to_time - strtotime($vls->finished_watching_at));
                                }
                                else if(((strtotime($vls->started_watching_at)) >= ($to_time)) && ((strtotime($vls->finished_watching_at)) > ($from_time))){
                                    $watched_sec = abs(strtotime($vls->started_watching_at) - $from_time);
                                }
                                else{
                                    $watched_sec = abs(strtotime($vls->finished_watching_at)-strtotime($vls->started_watching_at));
                                }
                                $user_watched_sec = $user_watched_sec + $watched_sec;
                                //return response()->json(["finished"=> $vls->finished_watching_at,"started"=> $vls->started_watching_at],200);
                            }
                        }
                        $user_watched_min = ($user_watched_sec)/60;
                        //return response()->json(["user_watched_min"=> $user_watched_min,"diff"=> $diff],200);
                        $time_spent = $user_watched_min/$diff;
                        array_push($user_info,$time_spent);
                    }
                    
                    $tvr = (array_sum($user_info))/$numOfUser;
                    $tvr = $tvr*100;
                    
                    //return response()->json(["total Tvr"=> $tvr,"no_of_user"=>$numOfUser,"User_info"=>$user_info]); 
                }
                
            }
            $chnl_tvr = [
                "channel_name"=>$c->channel_name,
                "tvr"=> $tvr
            ];
            array_push($chnl_names,$c->channel_name);
            array_push($ch_tvr,$tvr);
            array_push($chnl_info,$chnl_tvr);
        }
        //return response()->json(["chnl_info"=> $chnl_names],200); 
        return response()->json(["tvr"=>$ch_tvr,"channels"=> $chnl_names],200);

    }

}
