<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use DateTime;

class OverviewController extends Controller
{
    //
    public function reachusergraph(Request $req){
        
        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $startDateTime = date($startDate)." ".$startTime;
        $finishDateTime = date($finishDate)." ".$finishTime;

        $channels = Channel::all('id', 'channel_name');
        
        $number_of_user = [];
        $channel_label = [];
    
        foreach ($channels as $c) {
            
            $viewlogs = ViewLog::where('channel_id', $c->id)
                ->where(function($query) use ($startDateTime,$finishDateTime){
                    $query->where('finished_watching_at','>',$startDateTime)
                    ->orWhereNull('finished_watching_at');
                    })
                ->where('started_watching_at','<',$finishDateTime)
                ->distinct()->get('user_id');

            $user_count = 0;

            foreach ($viewlogs as $v) {
                $user= User::where('id',$v->user_id)
                        ->where('type','like','%'.$req->userType.'%')
                        ->where('address','like','%'.$req->region.'%')
                        ->where('gender','like','%'.$req->gender.'%')
                        ->where('economic_status','like','%'.$req->economic.'%')
                        ->where('socio_status','like','%'.$req->socio.'%')
                        ->whereBetween('age',[$req->age1,$req->age2])
                        ->first();
                if($user){
                    $user_count = $user_count + 1;
                }
                else{
                    continue;
                }
            
            }

            array_push($channel_label,$c->channel_name);
            array_push($number_of_user,$user_count);
            
        }
        
        return response()->json(["reachsum"=>array_sum($number_of_user),"reach"=>$number_of_user,"channels"=>$channel_label],200);
  

    }

    public function reachpercentgraph(Request $req){
        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $startDateTime = date($startDate)." ".$startTime;
        $finishDateTime = date($finishDate)." ".$finishTime;

        $channels = Channel::all('id', 'channel_name');
        $total_user = User::count();

        $number_of_user = [];
        $channel_label = [];
    
        foreach ($channels as $c) {
            
            $viewlogs = ViewLog::where('channel_id', $c->id)
                ->where(function($query) use ($startDateTime,$finishDateTime){
                    $query->where('finished_watching_at','>',$startDateTime)
                    ->orWhereNull('finished_watching_at');
                    })
                ->where('started_watching_at','<',$finishDateTime)
                ->distinct()->get('user_id');

            $user_count = 0;

            foreach ($viewlogs as $v) {
                $user= User::where('id',$v->user_id)
                        ->where('type','like','%'.$req->userType.'%')
                        ->where('address','like','%'.$req->region.'%')
                        ->where('gender','like','%'.$req->gender.'%')
                        ->where('economic_status','like','%'.$req->economic.'%')
                        ->where('socio_status','like','%'.$req->socio.'%')
                        ->whereBetween('age',[$req->age1,$req->age2])
                        ->first();
                if($user){
                    $user_count = $user_count + 1;
                }
                else{
                    continue;
                }
            
            }
            $user_count = ($user_count / $total_user) * 100 ;
            $user_count = round($user_count,1);
            array_push($channel_label,$c->channel_name);
            array_push($number_of_user,$user_count);
            
        }
        
        return response()->json(["reachsum"=>array_sum($number_of_user),"reach"=>$number_of_user,"channels"=>$channel_label],200);
  
    }
    // public function reachusergraph(Request $req){
        
    //     $startDate = substr($req->start, 0, 10);
    //     $startTime = substr($req->start, 11, 19);
    //     $finishDate = substr($req->finish, 0, 10);
    //     $finishTime = substr($req->finish, 11, 19);
    //     $channels = Channel::all()->filter(function ($c) use ($finishDate, $finishTime,$startDate,$startTime)
    //     {  return $c->reach( $startDate, $startTime, $finishDate,$finishTime) || !$c->channel_reach ;});
        
    
    //     $value = [];
    //     $label = [];
    //     foreach($channels as $c){
    //         $value[] = $c->channel_reach;
    //         $label[] = $c->channel_name;
    //     }
    //     return response()->json(["reachsum"=>array_sum($value),"reach"=>$value,"channels"=>$label],200);
    // }

    // public function reachpercentgraph(Request $req)
    // {

    //     $startDate = substr($req->start, 0, 10);
    //     $startTime = substr($req->start, 11, 19);
    //     $finishDate = substr($req->finish, 0, 10);
    //     $finishTime = substr($req->finish, 11, 19);
    //     $channels = Channel::all()->filter(function ($c) use ($finishDate, $finishTime,$startDate,$startTime)
    //     { return $c->reach( $startDate, $startTime, $finishDate,$finishTime) || !$c->channel_reach ;});
        
    
    //     $value = [];
    //     $label = [];
    //     foreach($channels as $c){
    //         $value[] = $c->channel_reach*100/Channel::count();
    //         $label[] = $c->channel_name;
    //     }
    //     return response()->json(["reachsum"=>array_sum($value),"reach"=>$value,"channels"=>$label],200);
    // }

    public function tvrgraphallchannelzero(Request $req)
    {

        $channelArray = array();
        $tvrs = array();
        $viewer = array();
        $ldate = date('Y-m-d H:i:s');
        /*if($req->start=="" && $req->finish==""){
        return response()->json(["reach"=>$reachs,"channels"=>$channelArray],200);
        }*/
        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $start_range = strtotime($startDate . " " . $startTime);
        $finish_range = strtotime($finishDate . " " . $finishTime);
        $diff = abs($start_range - $finish_range) / 60;
    
        //return response()->json([$di],200);
        //return response()->json(["tvr"=>$diff],200);
        $channels = Channel::all('id', 'channel_name');
        $users = User::all();
        $numOfUser = $users->count();
        //$all=array();
    
        foreach ($channels as $c) {
            $viewers = ViewLog::where('channel_id', $c->id)
            ->where(function ($query) use ($finishDate, $finishTime, $startDate, $startTime) {
                $query->where('finished_watching_at', '>', date($startDate) . " " . $startTime)
                ->orWhereNull('finished_watching_at');
            })
            ->where('started_watching_at', '<', date($finishDate) . " " . $finishTime)
            ->get();
            /*$viewers = ViewLog::where('channel_id', $c->id)
        ->where('started_watching_at','<',date($finishDate)." ".$finishTime)
        ->where('finished_watching_at','>',date($startDate)." ".$startTime)
        ->get();*/
            foreach ($viewers as $v) {
                $user= User::where('id',$v->user_id)
                        ->where('type','like','%'.$req->userType.'%')
                        ->where('address','like','%'.$req->region.'%')
                        ->where('gender','like','%'.$req->gender.'%')
                        ->where('economic_status','like','%'.$req->economic.'%')
                        ->where('socio_status','like','%'.$req->socio.'%')
                        ->whereBetween('age',[$req->age1,$req->age2])
                        ->first();
                if($user){
                    if ($v->finished_watching_at == null) {
                        if ((strtotime($v->started_watching_at)) < ($start_range)) {
                        $timeviewd = abs($start_range - strtotime($ldate));
                        } else if ((strtotime($v->started_watching_at)) >= ($start_range)) {
                        $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($ldate));
                        }
                    } else if (((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))) {
                        $timeviewd = abs($start_range - $finish_range);
                    } else if (((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) <= ($finish_range))) {
                        $timeviewd = abs($start_range - strtotime($v->finished_watching_at));
                    } else if (((strtotime($v->started_watching_at)) >= ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))) {
                        $timeviewd = abs(strtotime($v->started_watching_at) - $finish_range);
                    } else {
                        $timeviewd = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                    }
                    //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                    $timeviewd = $timeviewd / 60;
                    array_push($viewer, $timeviewd);
                }
                else{
                    continue;
                }
            
            }
            //return response()->json([$viewer],200);
            $tvr = array_sum($viewer); ///$numOfUser;
            //$tvr=$tvr/60;
            //$tvr=$tvr/$diff;
            //$tvr=$tvr*100;
            unset($viewer);
            $viewer = array();
            array_push($channelArray, $c->channel_name);
            array_push($tvrs, $tvr);
        }
        
        return response()->json(["tvrs" => $tvrs, "channels" => $channelArray], 200);
        //return response()->json(["tvr"=>$tvr],200);
  
    }
    public function tvrgraphallchannelpercent(Request $req)
    {
        $channelArray = array();
        $tvrs = array();
        $viewer = array();
        $ldate = date('Y-m-d H:i:s');
        /*if($req->start=="" && $req->finish==""){
        return response()->json(["reach"=>$reachs,"channels"=>$channelArray],200);
        }*/
        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $start_range = strtotime($startDate . " " . $startTime);
        $finish_range = strtotime($finishDate . " " . $finishTime);
        $diff = abs($start_range - $finish_range) / 60;

        //return response()->json([$di],200);
        //return response()->json(["tvr"=>$diff],200);
        $channels = Channel::all('id', 'channel_name');
        $users = User::all();
        $numOfUser = $users->count();
        //$all=array();

        foreach ($channels as $c) {
            $viewers = ViewLog::where('channel_id', $c->id)
                ->where(function ($query) use ($finishDate, $finishTime, $startDate, $startTime) {
                $query->where('finished_watching_at', '>', date($startDate) . " " . $startTime)
                    ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at', '<', date($finishDate) . " " . $finishTime)
                ->get();
            
            foreach ($viewers as $v) {
                $user= User::where('id',$v->user_id)
                        ->where('type','like','%'.$req->userType.'%')
                        ->where('address','like','%'.$req->region.'%')
                        ->where('gender','like','%'.$req->gender.'%')
                        ->where('economic_status','like','%'.$req->economic.'%')
                        ->where('socio_status','like','%'.$req->socio.'%')
                        ->whereBetween('age',[$req->age1,$req->age2])
                        ->first();
                if($user){
                    if ($v->finished_watching_at == null) {
                        if ((strtotime($v->started_watching_at)) < ($start_range)) {
                            $timeviewd = abs($start_range - strtotime($ldate));
                        } else if ((strtotime($v->started_watching_at)) >= ($start_range)) {
                            $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($ldate));
                        }
                    } else if (((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))) {
                    $timeviewd = abs($start_range - $finish_range);
                    } else if (((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) <= ($finish_range))) {
                    $timeviewd = abs($start_range - strtotime($v->finished_watching_at));
                    } else if (((strtotime($v->started_watching_at)) >= ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))) {
                    $timeviewd = abs(strtotime($v->started_watching_at) - $finish_range);
                    } else {
                    $timeviewd = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                    }
                    //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                    $timeviewd = $timeviewd / 60;
                    array_push($viewer, $timeviewd);
                }
                else{
                    continue;
                }


                
            }
            //return response()->json([$viewer],200);
            $tvr = array_sum($viewer) / $numOfUser;
            //$tvr=$tvr/60;
            $tvr = $tvr / $diff;
            $tvr = $tvr * 100;
            unset($viewer);
            $viewer = array();
            array_push($channelArray, $c->channel_name);
            array_push($tvrs, $tvr);
        }
        return response()->json(["tvrs" => $tvrs, "channels" => $channelArray], 200);
    }

    public function tvrsharegraph(Request $req){
        
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

                $user= User::where('id',$v->user_id)
                        ->where('type','like','%'.$req->userType.'%')
                        ->where('address','like','%'.$req->region.'%')
                        ->where('gender','like','%'.$req->gender.'%')
                        ->where('economic_status','like','%'.$req->economic.'%')
                        ->where('socio_status','like','%'.$req->socio.'%')
                        ->whereBetween('age',[$req->age1,$req->age2])
                        ->first();
                if($user){
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
                else{
                    continue;
                }


                
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

    public function timespentgraph(Request $req){
        
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
            ->where(function($query) use ($finishDate, $finishTime,$startDate,$startTime){
            $query->where('finished_watching_at','>',date($startDate)." ".$startTime)
            ->orWhereNull('finished_watching_at');
            })
            ->where('started_watching_at','<',date($finishDate)." ".$finishTime)
            ->get();
            $total_time_viewed = 0;
            foreach ($viewlogs as $v) {

                $user= User::where('id',$v->user_id)
                        ->where('type','like','%'.$req->userType.'%')   
                        ->where('address','like','%'.$req->region.'%')
                        ->where('gender','like','%'.$req->gender.'%')
                        ->where('economic_status','like','%'.$req->economic.'%')
                        ->where('socio_status','like','%'.$req->socio.'%')
                        ->whereBetween('age',[$req->age1,$req->age2])
                        ->first();
                if($user){
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
                else{
                    continue;
                }
 
            }
            $total_time_viewed = ($total_time_viewed)/60;
            //$tota_time_viewed = $tota_time_viewed / $diff;
            $total_time_viewed=round($total_time_viewed);
            
            array_push($total_time,$total_time_viewed);
            array_push($channelArray,$c->channel_name);

        }
        return response()->json(["totaltime"=>$total_time,"channels"=>$channelArray],200);
    }


}
