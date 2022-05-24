<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use DateTime;

class UserController extends Controller
{
    //
    public function usertimespent(Request $req){

        if($req->user != "" && $req->time != ""){
            if($req->time == "Daily"){
                $startDate=date('Y-m-d',strtotime("2022-05-18"));
                $startTime="00:00:00";
                $finishDate=date('Y-m-d',strtotime("2022-05-18"));
                $finishTime="23:59:59";

                $startDateTime = date($startDate)." ".$startTime;
                $finishDateTime = date($finishDate)." ".$finishTime;
            }
            else if($req->time == "Weekly"){
                $startDate=date('Y-m-d',strtotime("2022-05-11"));
                $startTime="00:00:00";
                $finishDate=date('Y-m-d',strtotime("2022-05-18"));
                $finishTime="23:59:59";

                $startDateTime = date($startDate)." ".$startTime;
                $finishDateTime = date($finishDate)." ".$finishTime;
            }
            else if($req->time == "Monthly"){
                $startDate=date('Y-m-d',strtotime("2022-05-01"));
                $startTime="00:00:00";
                $finishDate=date('Y-m-d',strtotime("2022-05-18"));
                $finishTime="23:59:59";

                $startDateTime = date($startDate)." ".$startTime;
                $finishDateTime = date($finishDate)." ".$finishTime;
            }
            else if($req->time == "Yearly"){
                $startDate=date('Y-m-d',strtotime("2022-01-01"));
                $startTime="00:00:00";
                $finishDate=date('Y-m-d',strtotime("2022-05-18"));
                $finishTime="23:59:59";

                $startDateTime = date($startDate)." ".$startTime;
                $finishDateTime = date($finishDate)." ".$finishTime;
            }

            $to_time = strtotime($startDate." ".$startTime);
            $from_time = strtotime($finishDate." ".$finishTime);
            $diff=abs($to_time - $from_time) / 60;

            $channelArray=array();
            $total_time =array();
            $total =0.00;

            $channels=Channel::all('id','channel_name');
            foreach ($channels as $c) {
                $viewlogs = ViewLog::where('channel_id', $c->id)
                ->where('user_id', $req->user)
                ->where(function($query) use ($startDateTime,$finishDateTime){
                    $query->where('finished_watching_at','>',$startDateTime)
                    ->orWhereNull('finished_watching_at');
                    })
                ->where('started_watching_at','<',$finishDateTime)
                ->get();
                $total_time_viewed = 0;
                
                foreach ($viewlogs as $v) {
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
                //$tota_time_viewed = $tota_time_viewed / $diff;
                $total_time_viewed=round($total_time_viewed);
                
                array_push($total_time,$total_time_viewed);
                array_push($channelArray,$c->channel_name);

            }
            return response()->json(["totaltime"=>$total_time,"channels"=>$channelArray],200);

        }
        return response()->json(["error"=> "Error"],200);
    }

    public function getallList(){
        $chnls = Channel::all('id','channel_name');
        return response()->json(["Channels"=>$chnls],200);
    }
}