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
        $users = User::all('id','user_name');
        return response()->json(["users"=>$users],200);
    }

    public function userAllTimeView(Request $req){
        if($req->user != ""){

            $channelArray=array();
            $total =0.00;

            $channels=Channel::all('id','channel_name','logo');
            foreach ($channels as $c) {
                $viewlogs = ViewLog::where('channel_id', $c->id)
                ->where('user_id', $req->user)
                ->get();
                $total_time_viewed = 0;
                
                foreach ($viewlogs as $v) {
                    if(($v->finished_watching_at) == Null){
                        $finishDateTime = date('2022-05-18 23:59:59');
                        $from_time = strtotime($finishDateTime);
                        $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                    }
                    else{
                        $watched_sec = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                    }
                    $total_time_viewed = $total_time_viewed + $watched_sec;
                }
                $total_time_viewed = date("H:i:s", $total_time_viewed);
                
                $chnls =[
                    "id" => $c->id,
                    "channel_name" => $c->channel_name,
                    "logo" => $c->logo,
                    "totaltime" => $total_time_viewed
                ];

                //array_push($total_time,$total_time_viewed);
                array_push($channelArray,$chnls);

            }
            return response()->json(["channels"=>$channelArray],200);

        }
        return response()->json(["error"=> "Error"],200);
    }

    public function userDayTimeViewList(Request $req){
        if($req->user != ""){

            $finishDateTime = date('2022-05-18 23:59:59');
            $addmin = 1439;
            $newtimestamp = strtotime("{$finishDateTime} - {$addmin} minute");
            $startDateTime = date('Y-m-d H:i:s', $newtimestamp);

            $to_time = strtotime($startDateTime);
            $from_time = strtotime($finishDateTime);
            $diff=abs($to_time - $from_time) / 60;

            $channelArray=array();
            $total =0.00;

            $channels=Channel::all('id','channel_name','logo');
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
                $total_time_viewed = date("H:i:s", $total_time_viewed);
                    
                $chnls =[
                    "id" => $c->id,
                    "channel_name" => $c->channel_name,
                    "logo" => $c->logo,
                    "totaltime" => $total_time_viewed
                ];

                //array_push($total_time,$total_time_viewed);
                array_push($channelArray,$chnls);

            }
            return response()->json(["channels"=>$channelArray],200);
        }
        return response()->json(["error"=> "Error"],200);
    }

    public function usertimespent2(Request $req){
        if($req->user != "" && $req->start != "" && $req->finish != ""){
            
            $startDate=substr($req->start,0,10);
            $startTime=substr($req->start,11,19);
            $finishDate=substr($req->finish,0,10);
            $finishTime=substr($req->finish,11,19);

            $startDateTime = date($startDate)." ".$startTime;
            $finishDateTime = date($finishDate)." ".$finishTime;
            

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

    public function LastTweentyFourViewsGraph(Request $req){
        $rows = 0;
        $chart_labels = [];
        $chart_data = [];

        if($req->user != ""){

            $data = ViewLog::where('user_id', $req->user)->where(function($query){
                $query->where('started_watching_at', '>=', Carbon::now()->subHours(730));
            })->with('channel:id,channel_name')->orderBy('started_watching_at')->get();
            $channel_ids = array_unique($data->pluck('channel_id')->toArray());
            array_splice($channel_ids, 0, 0);
            $chart_labels = array_unique($data->whereIn('channel_id', $channel_ids)->pluck('channel.channel_name')->toArray());
            array_splice($chart_labels, 0, 0);
            for($i = 0; $i < count($channel_ids); $i++){
                $rows = $rows + 1;
                $datum = $data->where('channel_id', $channel_ids[$i]);
                $graph_data = [];
                $datum->map(function($temp) use(&$graph_data){
                    array_push($graph_data, [Carbon::parse($temp->started_watching_at), Carbon::Parse($temp->finished_watching_at) ?? Carbon::now(), Carbon::Parse($temp->started_watching_at)->diffInMinutes(Carbon::parse($temp->finished_watching_at) ?? Carbon::now()) . ' min']);
                });
                $chart_data[$i]['data'] = $graph_data;
            }//dd($this->chart_data);
            return response()->json(["rows"=>$rows,"chart_data"=>$chart_data,"chart_labels"=>$chart_labels]);
    
        }
        return response()->json(["error"=> "Error"],200);
        
    }





    public function LastSeventyTwoViewsGraph(Request $req){
        $rows = 0;
        $chart_labels = [];
        $chart_data = [];

        if($req->user != ""){

            $data = ViewLog::where('user_id', $req->user)->where(function($query){
                $query->where('started_watching_at', '>=', Carbon::now()->subHours(730));
            })->with('channel:id,channel_name')->orderBy('started_watching_at')->get();
            $channel_ids = array_unique($data->pluck('channel_id')->toArray());
            array_splice($channel_ids, 0, 0);
            $chart_labels = array_unique($data->whereIn('channel_id', $channel_ids)->pluck('channel.channel_name')->toArray());
            array_splice($chart_labels, 0, 0);
            for($i = 0; $i < count($channel_ids); $i++){
                $rows = $rows + 1;
                $datum = $data->where('channel_id', $channel_ids[$i]);
                $graph_data = [];
                $datum->map(function($temp) use(&$graph_data){
                    array_push($graph_data, [Carbon::parse($temp->started_watching_at), Carbon::Parse($temp->finished_watching_at) ?? Carbon::now(), Carbon::Parse($temp->started_watching_at)->diffInMinutes(Carbon::parse($temp->finished_watching_at) ?? Carbon::now()) . ' min']);
                });
                $chart_data[$i]['data'] = $graph_data;
            }//dd($this->chart_data);
            return response()->json(["rows"=>$rows,"chart_data"=>$chart_data,"chart_labels"=>$chart_labels]);
    
        }
        return response()->json(["error"=> "Error"],200);

    }

    public function last72from18(Request $req){

        if($req->user != "" ){
            
            //$startDate=date('Y-m-d',strtotime("2022-05-18"));
            //$startTime="00:00:00";
            $finishDate=date('Y-m-d',strtotime("2022-05-18"));
            $finishTime="23:59:59";

            //$startDateTime = date($startDate)." ".$startTime;
            $finishDateTime = date($finishDate)." ".$finishTime;

            $min = 4319;
            $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
            $startDateTime = date('Y-m-d H:i:s', $newtimestamp);
            

            $to_time = strtotime($startDateTime);
            $from_time = strtotime($finishDateTime);
            

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
                        $start_time = $startDateTime;
                        $finish_time = $finishDateTime;
                    }
                    else if(((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) <= ($from_time))){
                        $watched_sec = abs($to_time - strtotime($v->finished_watching_at));
                        $start_time = $startDateTime;
                        $finish_time = $v->finished_watching_at;
                    }
                    else if(((strtotime($v->started_watching_at)) >= ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null ) )){
                        $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                        $start_time = $v->started_watching_at;
                        $finish_time = $finishDateTime;

                    }
                    else{
                        $watched_sec = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                        $start_time = $v->started_watching_at;
                        $finish_time = $v->finished_watching_at;
                    }
                    $total_time_viewed = floor($watched_sec/60);
                    $chnls =[
                        "channel_name" => $c->channel_name,
                        "start" => $start_time,
                        "finish" => $finish_time,
                        "min" => $total_time_viewed." min"
                    ];
                    array_push($channelArray,$chnls);

                    
                }
                

            }
            array_multisort(array_column($channelArray, 'start'), SORT_ASC, $channelArray);
            return response()->json(["channels"=>$channelArray],200);

        }
        return response()->json(["error"=> "Error"],200);
    }



}