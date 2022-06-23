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
    public function logs(Request $req){
        $ndata=array();
        $data = ViewLog::where('user_id',$req->user)->orderBy('id','DESC')->get();
        foreach ($data as $d) {
            $arr=array(
                "channel_name"=>$d->channel->channel_name,
                "started_watching_at"=>$d->started_watching_at,
                "finished_watching_at"=>$d->finished_watching_at,
                "duration_minute"=>$d->duration_minute
            );
            array_push($ndata,$arr);
        }

        return response()->json(["data"=> $ndata],200);
    }
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
            return response()->json(["start"=>$startDateTime, "finish"=>$finishDateTime,"totaltime"=>$total_time,"channels"=>$channelArray],200);

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


        $channels = ViewLog::where('user_id', $req->user)->where(function($query){
            $query->where('finished_watching_at', '>=', Carbon::now()->subHours(24))->orWhere('started_watching_at', '>=', Carbon::now()->subHours(24));
        })->with('channel')->orderBy('started_watching_at','DESC')->get();
        $data=[];
        foreach($channels as $c){
            $ch = array();
            $ch["channel_name"] = $c->channel->channel_name;
            $ch["logo"] = $c->channel->logo;
            $ch["totaltime"] = abs(strtotime($c->channel->finished_watching_at)-strtotime($c->channel->started_watching_at))/60;
            $data[] = (object)$ch;
        }
        
        if (count($data))return response()->json(["channels"=>$data],200);
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
                $query->where('started_watching_at', '>=', Carbon::now()->subHours(72));
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

    public function last24WatchingData(Request $req){

        if($req->user != "" ){
            
            //$startDate=date('Y-m-d',strtotime("2022-05-18"));
            //$startTime="00:00:00";
            // $finishDate=date('Y-m-d',strtotime("2022-05-18"));
            // $finishTime="23:59:59";
            $finishDateTime = date("Y-m-d H:i:s");

            $min = 1439;
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
            $rows = count($channelArray);
            return response()->json(["rows"=>$rows,"channels"=>$channelArray],200);

        }
        return response()->json(["error"=> "Error"],200);
    }

    public function last72WatchingData(Request $req){

        if($req->user != "" ){
            
            //$startDate=date('Y-m-d',strtotime("2022-05-18"));
            //$startTime="00:00:00";
            // $finishDate=date('Y-m-d',strtotime("2022-05-18"));
            // $finishTime="23:59:59";
            $finishDateTime = date("Y-m-d H:i:s");

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
            $rows = count($channelArray);
            return response()->json(["rows"=>$rows,"channels"=>$channelArray],200);

        }
        return response()->json(["error"=> "Error"],200);
    }






    function demo_test(){
        
        $arr = [];
        $obj1 = array("x"=>"Design","y"=>[strtotime("2022-05-16 11:57:29"),strtotime("2022-05-16 12:55:04")]);
        //$obj1 = array("x"=>"Design","y"=>[12,14]);
        $obj2 = array("x"=>"Code","y"=>[strtotime("2022-05-16 12:55:43"),strtotime("2022-05-16 13:54:33")]);
        $obj3 = array("x"=>"Code","y"=>[strtotime("2022-05-16 14:12:14"),strtotime("2022-05-16 14:29:56")]);
        $obj4 = array("x"=>"Test","y"=>[strtotime("2022-05-16 14:38:28"),strtotime("2022-05-16 14:51:33")]);
        $arr[] = (object)$obj1;
        $arr[] = (object)$obj2;
        $arr[] = (object)$obj3;
        $arr[] = (object)$obj4;
        return $arr;

        return response("[
  
            {
              x: 'Design',
              y: [
                12,
                14
              ]
            },
            {
              x: 'Code',
              y: [
                15,
                16
              ]
            },
            {
              x: 'Code',
              y: [
                10,
                11
              ]
            }
            
          ]");
    }



}