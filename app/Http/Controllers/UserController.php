<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;
use App\Models\Universe;
use Carbon\Carbon;
use DateTime;

use App\Mail\SendMail;
use App\Models\UserDataFilter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

use App\Models\DayPartProcess;
use App\Models\DayPart;
use App\Models\DataCleanse;
use App\Models\DeselectPeriod;
use App\Models\Device;
use App\Models\SystemUniverse;
use App\Models\SystemUniverseAll;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth.admin');
    // }

   
    public function get_last_SystemUniverse()
    {

        // Perform the query using Laravel Eloquent
        $result = SystemUniverse::select(
            'system_universe.region',
            'system_universe.sec',
            'system_universe.gender',
            'system_universe.age_group',
            'system_universe.universe as system',
            DB::raw('universe.universe/1000 as universe'),
            DB::raw('(universe.universe/1000)/system_universe.universe as multiplication_factor')
        )
            ->join('universe', function ($join) {
                $join->on('system_universe.region', '=', 'universe.region')
                    ->on('system_universe.sec', '=', 'universe.sec')
                    ->on('system_universe.gender', '=', 'universe.gender')
                    ->on('system_universe.age_group', '=', 'universe.age_group')
                    ->whereRaw('system_universe.date_of_gen between universe.start and universe.`end`');
            })
            ->where('system_universe.date_of_gen', '=', function ($query) {
                $query->select(DB::raw('MAX(`date_of_gen`)'))
                    ->from('system_universe');
            })
            ->get();

        // Prepare the array
        $dataArray = [];

        // Add headers to the array
        $headers = ['region', 'sec', 'gender', 'age_group', 'system', 'universe', 'multiplication_factor'];
        $dataArray[] = $headers;
        
        // Add data to the array
        foreach ($result as $row) {
            $dataArray[] = [
                $row->region,
                $row->sec,
                $row->gender,
                $row->age_group,
                $row->system,
                $row->universe,
                $row->multiplication_factor,
            ];
        }
        return response()->json(["data" => $dataArray], 200);
    }
    public function get_last_SystemUniverseAll()
    {

        // Perform the query using Laravel Eloquent
        $result = SystemUniverseAll::select(
            'system_universe_all.region',
            'system_universe_all.sec',
            'system_universe_all.gender',
            'system_universe_all.age_group',
            'system_universe_all.universe as system',
            DB::raw('universe.universe/1000 as universe'),
            DB::raw('(universe.universe/1000)/system_universe_all.universe as multiplication_factor')
        )
            ->join('universe', function ($join) {
                $join->on('system_universe_all.region', '=', 'universe.region')
                    ->on('system_universe_all.sec', '=', 'universe.sec')
                    ->on('system_universe_all.gender', '=', 'universe.gender')
                    ->on('system_universe_all.age_group', '=', 'universe.age_group')
                    ->whereRaw('system_universe_all.date_of_gen between universe.start and universe.`end`');
            })
            ->where('system_universe_all.date_of_gen', '=', function ($query) {
                $query->select(DB::raw('MAX(`date_of_gen`)'))
                    ->from('system_universe_all');
            })
            ->get();

        // Prepare the array
        $dataArray = [];

        // Add headers to the array
        $headers = ['region', 'sec', 'gender', 'age_group', 'system', 'universe', 'multiplication_factor'];
        $dataArray[] = $headers;
        
        // Add data to the array
        foreach ($result as $row) {
            $dataArray[] = [
                $row->region,
                $row->sec,
                $row->gender,
                $row->age_group,
                $row->system,
                $row->universe,
                $row->multiplication_factor,
            ];
        }
        return response()->json(["data" => $dataArray], 200);
    }
    public function logs(Request $req)
    {
        if ($req->user != "" && $req->start != "" && $req->finish != "") {

            $startDate = substr($req->start, 0, 10);
            $startTime = substr($req->start, 11, 19);
            $finishDate = substr($req->finish, 0, 10);
            $finishTime = substr($req->finish, 11, 19);

            $startDateTime = date($startDate) . " " . $startTime;
            $finishDateTime = date($finishDate) . " " . $finishTime;

            //return response()->json(["user"=>$req->user,"start"=>$startDateTime,"finish"=>$finishDateTime],200);

            $ndata = array();
            $data = ViewLog::where('user_id', $req->user)
                ->where(function ($query) use ($startDateTime, $finishDateTime) {
                    $query->where('finished_watching_at', '>', $startDateTime)
                        ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at', '<', $finishDateTime)
                ->orderBy('id', 'DESC')->get();

            //$data = ViewLog::where('user_id',$req->user)->orderBy('id','DESC')->get();
            //return response()->json(["user"=>$req->user,"start"=>$startDateTime,"finish"=>$finishDateTime,"data"=>$data],200);
            foreach ($data as $d) {
                // if(($d->finished_watching_at) == Null ){
                //     $from_time = date('Y-m-d H:i:s');
                // }
                // else{
                //     $from_time = $d->finished_watching_at;
                // }

                if (((strtotime($d->started_watching_at)) < (strtotime($startDateTime))) && (((strtotime($d->finished_watching_at)) > (strtotime($finishDateTime))) || (($d->finished_watching_at) == Null))) {
                    $to_time = $startDateTime;
                    $from_time = $finishDateTime;
                } else if (((strtotime($d->started_watching_at)) < (strtotime($startDateTime))) && ((strtotime($d->finished_watching_at)) <= (strtotime($finishDateTime)))) {
                    $to_time = $startDateTime;
                    $from_time = $d->finished_watching_at;
                } else if (((strtotime($d->started_watching_at)) >= (strtotime($startDateTime))) && (((strtotime($d->finished_watching_at)) > (strtotime($finishDateTime))) || (($d->finished_watching_at) == Null))) {
                    $to_time = $d->started_watching_at;
                    $from_time = $finishDateTime;
                } else {
                    $to_time = $d->started_watching_at;
                    $from_time = $d->finished_watching_at;
                }





                $arr = array(
                    "log_id" => $d->id,
                    "channel_name" => $d->channel->channel_name,
                    "started_watching_at" => $to_time,
                    "finished_watching_at" => $from_time,
                    "duration_sec" => abs(strtotime($to_time) - strtotime($from_time))
                );
                array_push($ndata, $arr);
            }

            return response()->json(["data" => $ndata], 200);
        }
        return response()->json(["error" => "Error"], 200);
    }

    public function alllogs(Request $req)
    {
        if ($req->start != "" && $req->finish != "") {

            $startDate = substr($req->start, 0, 10);
            $startTime = substr($req->start, 11, 19);
            $finishDate = substr($req->finish, 0, 10);
            $finishTime = substr($req->finish, 11, 19);

            $startDateTime = date($startDate) . " " . $startTime;
            $finishDateTime = date($finishDate) . " " . $finishTime;

            //return response()->json(["user"=>$req->user,"start"=>$startDateTime,"finish"=>$finishDateTime],200);

            $ndata = array();
            $data = ViewLog::where(function ($query) use ($startDateTime, $finishDateTime) {
                $query->where('finished_watching_at', '>', $startDateTime)
                    ->orWhereNull('finished_watching_at');
            })
                ->where('started_watching_at', '<', $finishDateTime)
                ->orderBy('id', 'DESC')->get();

            //$data = ViewLog::where('user_id',$req->user)->orderBy('id','DESC')->get();
            //return response()->json(["user"=>$req->user,"start"=>$startDateTime,"finish"=>$finishDateTime,"data"=>$data],200);
            foreach ($data as $d) {
                // if(($d->finished_watching_at) == Null ){
                //     $from_time = date('Y-m-d H:i:s');
                // }
                // else{
                //     $from_time = $d->finished_watching_at;
                // }

                // if(((strtotime($d->started_watching_at)) < (strtotime($startDateTime))) && (((strtotime($d->finished_watching_at)) > (strtotime($finishDateTime))) || (($d->finished_watching_at) == Null ) )){
                //     $to_time = $startDateTime;
                //     $from_time = $finishDateTime;
                // }
                // else if(((strtotime($d->started_watching_at)) < (strtotime($startDateTime))) && ((strtotime($d->finished_watching_at)) <= (strtotime($finishDateTime)))){
                //     $to_time = $startDateTime;
                //     $from_time = $d->finished_watching_at;

                // }
                // else if(((strtotime($d->started_watching_at)) >= (strtotime($startDateTime))) && (((strtotime($d->finished_watching_at)) > (strtotime($finishDateTime))) || (($d->finished_watching_at) == Null ) )){
                //     $to_time = $d->started_watching_at;
                //     $from_time = $finishDateTime;
                // }
                // else{
                //     $to_time = $d->started_watching_at;
                //     $from_time = $d->finished_watching_at;
                // }





                // $arr=array(
                //     "log_id"=>$d->id,
                //     "user_id"=>$d->user_id,
                //     "channel_name"=>$d->channel->channel_name,
                //     "started_watching_at"=>$to_time,
                //     "finished_watching_at"=>$from_time,
                //     "duration_sec"=>abs(strtotime($to_time)-strtotime($from_time))
                // );

                $arr = array(
                    "log_id" => $d->id,
                    "user_id" => $d->user_id,
                    "channel_name" => $d->channel->channel_name,
                    "started_watching_at" => $d->started_watching_at,
                    "finished_watching_at" => $d->finished_watching_at,
                    "duration_sec" => abs(strtotime($d->started_watching_at) - strtotime($d->finished_watching_at))
                );
                array_push($ndata, $arr);
            }

            return response()->json(["data" => $ndata], 200);
        }
        return response()->json(["error" => "Error"], 200);
    }
    //
    public function usertimespent(Request $req)
    {

        if ($req->user != "" && $req->time != "") {
            if ($req->time == "Daily") {

                $finishDateTime = date("Y-m-d H:i:s");
                $min = 1440;
                $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
                $startDateTime = date('Y-m-d H:i:s', $newtimestamp);
            } else if ($req->time == "Weekly") {
                $finishDateTime = date("Y-m-d H:i:s");
                $min = 10080;
                $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
                $startDateTime = date('Y-m-d H:i:s', $newtimestamp);
            } else if ($req->time == "Monthly") {
                $finishDateTime = date("Y-m-d H:i:s");
                $min = 43200;
                $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
                $startDateTime = date('Y-m-d H:i:s', $newtimestamp);
            } else if ($req->time == "Yearly") {
                $finishDateTime = date("Y-m-d H:i:s");
                $min = 525600;
                $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
                $startDateTime = date('Y-m-d H:i:s', $newtimestamp);
            }

            $to_time = strtotime($startDateTime);
            $from_time = strtotime($finishDateTime);
            $diff = abs($to_time - $from_time) / 60;

            $channelArray = array();
            $total_time = array();
            $total = 0.00;

            $channels = Channel::all('id', 'channel_name');
            foreach ($channels as $c) {
                $viewlogs = ViewLog::where('channel_id', $c->id)
                    ->where('user_id', $req->user)
                    ->where(function ($query) use ($startDateTime, $finishDateTime) {
                        $query->where('finished_watching_at', '>', $startDateTime)
                            ->orWhereNull('finished_watching_at');
                    })
                    ->where('started_watching_at', '<', $finishDateTime)
                    ->get();
                $total_time_viewed = 0;

                foreach ($viewlogs as $v) {
                    if (((strtotime($v->started_watching_at)) < ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                        $watched_sec = abs($to_time - $from_time);
                    } else if (((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) <= ($from_time))) {
                        $watched_sec = abs($to_time - strtotime($v->finished_watching_at));
                    } else if (((strtotime($v->started_watching_at)) >= ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                        $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                    } else {
                        $watched_sec = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                    }
                    $total_time_viewed = $total_time_viewed + $watched_sec;
                    //$timeviewed = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at))/60;
                }
                $total_time_viewed = ($total_time_viewed) / 60;
                //$tota_time_viewed = $tota_time_viewed / $diff;
                $total_time_viewed = round($total_time_viewed);

                array_push($total_time, $total_time_viewed);
                array_push($channelArray, $c->channel_name);
            }
            return response()->json(["start" => $startDateTime, "finish" => $finishDateTime, "totaltime" => $total_time, "channels" => $channelArray], 200);
        }
        return response()->json(["error" => "Error"], 200);
    }

    public function getallList()
    {
        $users = User::all('id', 'user_name');
        return response()->json(["users" => $users], 200);
    }

    public function userAllTimeView(Request $req)
    {
        if ($req->user != "") {

            $channelArray = array();
            $total = 0.00;

            $channels = Channel::all('id', 'channel_name', 'logo');
            foreach ($channels as $c) {
                $viewlogs = ViewLog::where('channel_id', $c->id)
                    ->where('user_id', $req->user)
                    ->get();
                $total_time_viewed = 0;

                foreach ($viewlogs as $v) {
                    if (($v->finished_watching_at) == Null) {
                        $finishDateTime = date("Y-m-d H:i:s");
                        $from_time = strtotime($finishDateTime);
                        $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                    } else {
                        $watched_sec = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                    }
                    $total_time_viewed = $total_time_viewed + $watched_sec;
                }
                //$total_time_viewed = date("H:i:s", $total_time_viewed);
                $total_time_viewed = ($total_time_viewed / 60);
                $total_time_viewed = round($total_time_viewed);
                if ($total_time_viewed >= 60) {
                    $total_time = ($total_time_viewed / 60);
                    $total_time = round($total_time);
                    $duration = $total_time . " hour";
                } else {
                    $total_time = floor($total_time_viewed);
                    $duration = $total_time . " minute";
                }

                $chnls = [
                    "id" => $c->id,
                    "channel_name" => $c->channel_name,
                    "logo" => $c->logo,
                    "totaltime" => $total_time_viewed,
                    "duration" => $duration
                ];

                //array_push($total_time,$total_time_viewed);
                array_push($channelArray, $chnls);
            }
            return response()->json(["channels" => $channelArray], 200);
        }
        return response()->json(["error" => "Error"], 200);
    }

    // public function userDayTimeViewList(Request $req){
    //     $channels = ViewLog::where('user_id', $req->user)->where(function($query){
    //         $query->where('finished_watching_at', '>=', Carbon::now()->subHours(24))->orWhere('started_watching_at', '>=', Carbon::now()->subHours(24));
    //     })->with('channel')->orderBy('started_watching_at','DESC')->get();
    //     $data=[];
    //     foreach($channels as $c){
    //         $ch = array();
    //         $ch["channel_name"] = $c->channel->channel_name;
    //         $ch["logo"] = $c->channel->logo;
    //         $ch["totaltime"] = abs(strtotime($c->channel->finished_watching_at)-strtotime($c->channel->started_watching_at))/60;
    //         $data[] = (object)$ch;
    //     }

    //     if (count($data))return response()->json(["channels"=>$data],200);
    //     return response()->json(["error"=> "Error"],200);
    // }

    public function userDayTimeViewList(Request $req)
    {
        if ($req->user != "") {
            $finishDateTime = date('Y-m-d H:i:s');
            $addmin = 1439;
            $newtimestamp = strtotime("{$finishDateTime} - {$addmin} minute");
            $startDateTime = date('Y-m-d H:i:s', $newtimestamp);

            $to_time = strtotime($startDateTime);
            $from_time = strtotime($finishDateTime);
            $diff = abs($to_time - $from_time) / 60;

            $channelArray = array();
            $total = 0.00;

            $channels = Channel::all('id', 'channel_name', 'logo');
            foreach ($channels as $c) {
                $viewlogs = ViewLog::where('channel_id', $c->id)
                    ->where('user_id', $req->user)
                    ->where(function ($query) use ($startDateTime, $finishDateTime) {
                        $query->where('finished_watching_at', '>', $startDateTime)
                            ->orWhereNull('finished_watching_at');
                    })
                    ->where('started_watching_at', '<', $finishDateTime)
                    ->get();
                $total_time_viewed = 0;
                $total_time = 0;
                $duration = "";
                foreach ($viewlogs as $v) {
                    if (((strtotime($v->started_watching_at)) < ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                        $watched_sec = abs($to_time - $from_time);
                    } else if (((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) <= ($from_time))) {
                        $watched_sec = abs($to_time - strtotime($v->finished_watching_at));
                    } else if (((strtotime($v->started_watching_at)) >= ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                        $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                    } else {
                        $watched_sec = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                    }
                    $total_time_viewed = $total_time_viewed + $watched_sec;
                    //$timeviewed = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at))/60;
                }
                //$total_time_viewed = date("H:i:s", $total_time_viewed);
                $total_time_viewed = ($total_time_viewed / 60);
                $total_time_viewed = round($total_time_viewed);
                if ($total_time_viewed >= 60) {
                    $total_time = ($total_time_viewed / 60);
                    $total_time = round($total_time);
                    $duration = $total_time . " hour";
                } else {
                    $total_time = floor($total_time_viewed);
                    $duration = $total_time . " minute";
                }

                $chnls = [
                    "id" => $c->id,
                    "channel_name" => $c->channel_name,
                    "logo" => $c->logo,
                    "totaltime" => $total_time_viewed,
                    "duration" => $duration
                ];
                //array_push($total_time,$total_time_viewed);
                array_push($channelArray, $chnls);
            }
            return response()->json(["channels" => $channelArray], 200);
        }
        return response()->json(["error" => "Error"], 200);
    }

    public function usertimespent2(Request $req)
    {
        if ($req->user != "" && $req->start != "" && $req->finish != "") {

            $startDate = substr($req->start, 0, 10);
            $startTime = substr($req->start, 11, 19);
            $finishDate = substr($req->finish, 0, 10);
            $finishTime = substr($req->finish, 11, 19);

            $startDateTime = date($startDate) . " " . $startTime;
            $finishDateTime = date($finishDate) . " " . $finishTime;


            $to_time = strtotime($startDate . " " . $startTime);
            $from_time = strtotime($finishDate . " " . $finishTime);

            $channelArray = array();
            $total_time = array();
            $total = 0.00;

            $channels = Channel::all('id', 'channel_name');
            foreach ($channels as $c) {
                $viewlogs = ViewLog::where('channel_id', $c->id)
                    ->where('user_id', $req->user)
                    ->where(function ($query) use ($startDateTime, $finishDateTime) {
                        $query->where('finished_watching_at', '>', $startDateTime)
                            ->orWhereNull('finished_watching_at');
                    })
                    ->where('started_watching_at', '<', $finishDateTime)
                    ->get();
                $total_time_viewed = 0;

                foreach ($viewlogs as $v) {
                    if (((strtotime($v->started_watching_at)) < ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                        $watched_sec = abs($to_time - $from_time);
                    } else if (((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) <= ($from_time))) {
                        $watched_sec = abs($to_time - strtotime($v->finished_watching_at));
                    } else if (((strtotime($v->started_watching_at)) >= ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                        $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                    } else {
                        $watched_sec = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                    }
                    $total_time_viewed = $total_time_viewed + $watched_sec;
                    //$timeviewed = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at))/60;
                }
                $total_time_viewed = ($total_time_viewed) / 60;

                $total_time_viewed = round($total_time_viewed);

                array_push($total_time, $total_time_viewed);
                array_push($channelArray, $c->channel_name);
            }
            return response()->json(["totaltime" => $total_time, "channels" => $channelArray], 200);
        }
        return response()->json(["error" => "Error"], 200);
    }

    public function LastTweentyFourViewsGraph(Request $req)
    {
        $rows = 0;
        $chart_labels = [];
        $chart_data = [];

        if ($req->user != "") {

            $data = ViewLog::where('user_id', $req->user)->where(function ($query) {
                $query->where('started_watching_at', '>=', Carbon::now()->subHours(730));
            })->with('channel:id,channel_name')->orderBy('started_watching_at')->get();
            $channel_ids = array_unique($data->pluck('channel_id')->toArray());
            array_splice($channel_ids, 0, 0);
            $chart_labels = array_unique($data->whereIn('channel_id', $channel_ids)->pluck('channel.channel_name')->toArray());
            array_splice($chart_labels, 0, 0);
            for ($i = 0; $i < count($channel_ids); $i++) {
                $rows = $rows + 1;
                $datum = $data->where('channel_id', $channel_ids[$i]);
                $graph_data = [];
                $datum->map(function ($temp) use (&$graph_data) {
                    array_push($graph_data, [Carbon::parse($temp->started_watching_at), Carbon::Parse($temp->finished_watching_at) ?? Carbon::now(), Carbon::Parse($temp->started_watching_at)->diffInMinutes(Carbon::parse($temp->finished_watching_at) ?? Carbon::now()) . ' min']);
                });
                $chart_data[$i]['data'] = $graph_data;
            } //dd($this->chart_data);
            return response()->json(["rows" => $rows, "chart_data" => $chart_data, "chart_labels" => $chart_labels]);
        }
        return response()->json(["error" => "Error"], 200);
    }



    public function LastSeventyTwoViewsGraph(Request $req)
    {
        $rows = 0;
        $chart_labels = [];
        $chart_data = [];

        if ($req->user != "") {

            $data = ViewLog::where('user_id', $req->user)->where(function ($query) {
                $query->where('started_watching_at', '>=', Carbon::now()->subHours(72));
            })->with('channel:id,channel_name')->orderBy('started_watching_at')->get();
            $channel_ids = array_unique($data->pluck('channel_id')->toArray());
            array_splice($channel_ids, 0, 0);
            $chart_labels = array_unique($data->whereIn('channel_id', $channel_ids)->pluck('channel.channel_name')->toArray());
            array_splice($chart_labels, 0, 0);
            for ($i = 0; $i < count($channel_ids); $i++) {
                $rows = $rows + 1;
                $datum = $data->where('channel_id', $channel_ids[$i]);
                $graph_data = [];
                $datum->map(function ($temp) use (&$graph_data) {
                    array_push($graph_data, [Carbon::parse($temp->started_watching_at), Carbon::Parse($temp->finished_watching_at) ?? Carbon::now(), Carbon::Parse($temp->started_watching_at)->diffInMinutes(Carbon::parse($temp->finished_watching_at) ?? Carbon::now()) . ' min']);
                });
                $chart_data[$i]['data'] = $graph_data;
            } //dd($this->chart_data);
            return response()->json(["rows" => $rows, "chart_data" => $chart_data, "chart_labels" => $chart_labels]);
        }
        return response()->json(["error" => "Error"], 200);
    }

    public function last24WatchingData(Request $req)
    {

        if ($req->user != "") {
            $finishDateTime = date("Y-m-d H:i:s");
            $min = 1439;
            $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
            $startDateTime = date('Y-m-d H:i:s', $newtimestamp);

            $to_time = strtotime($startDateTime);
            $from_time = strtotime($finishDateTime);


            $channelArray = array();
            $total_time = array();
            $total = 0.00;

            $viewlogs = ViewLog::where('user_id', $req->user)
                ->where(function ($query) use ($startDateTime, $finishDateTime) {
                    $query->where('finished_watching_at', '>', $startDateTime)
                        ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at', '<', $finishDateTime)
                ->get();
            $total_time_viewed = 0;

            foreach ($viewlogs as $v) {
                if (((strtotime($v->started_watching_at)) < ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                    $watched_sec = abs($to_time - $from_time);
                    $start_time = $startDateTime;
                    $finish_time = $finishDateTime;
                } else if (((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) <= ($from_time))) {
                    $watched_sec = abs($to_time - strtotime($v->finished_watching_at));
                    $start_time = $startDateTime;
                    $finish_time = $v->finished_watching_at;
                } else if (((strtotime($v->started_watching_at)) >= ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                    $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                    $start_time = $v->started_watching_at;
                    $finish_time = $finishDateTime;
                } else {
                    $watched_sec = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                    $start_time = $v->started_watching_at;
                    $finish_time = $v->finished_watching_at;
                }
                $total_time_viewed = floor($watched_sec / 60);
                $chnls = [
                    "channel_name" => $v->channel->channel_name,
                    "start" => date('Y-m-d H:i:s', (strtotime($start_time) + 21600)),
                    "finish" => date('Y-m-d H:i:s', (strtotime($finish_time) + 21600)),
                    "min" => $total_time_viewed . " min"
                ];
                array_push($channelArray, $chnls);
            }
            array_multisort(array_column($channelArray, 'start'), SORT_ASC, $channelArray);
            $rows = count($channelArray);
            return response()->json(["rows" => $rows, "channels" => $channelArray], 200);
        }
        return response()->json(["error" => "Error"], 200);
    }

    public function last72WatchingData(Request $req)
    {

        if ($req->user != "") {
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

            $channelArray = array();
            $total_time = array();
            $total = 0.00;

            $viewlogs = ViewLog::where('user_id', $req->user)
                ->where(function ($query) use ($startDateTime, $finishDateTime) {
                    $query->where('finished_watching_at', '>', $startDateTime)
                        ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at', '<', $finishDateTime)
                ->get();
            $total_time_viewed = 0;

            foreach ($viewlogs as $v) {
                if (((strtotime($v->started_watching_at)) < ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                    $watched_sec = abs($to_time - $from_time);
                    $start_time = $startDateTime;
                    $finish_time = $finishDateTime;
                } else if (((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) <= ($from_time))) {
                    $watched_sec = abs($to_time - strtotime($v->finished_watching_at));
                    $start_time = $startDateTime;
                    $finish_time = $v->finished_watching_at;
                } else if (((strtotime($v->started_watching_at)) >= ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                    $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                    $start_time = $v->started_watching_at;
                    $finish_time = $finishDateTime;
                } else {
                    $watched_sec = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                    $start_time = $v->started_watching_at;
                    $finish_time = $v->finished_watching_at;
                }
                $total_time_viewed = floor($watched_sec / 60);
                $chnls = [
                    "channel_name" => $v->channel->channel_name,
                    "start" => date('Y-m-d H:i:s', (strtotime($start_time) + 21600)),
                    "finish" => date('Y-m-d H:i:s', (strtotime($finish_time) + 21600)),
                    "min" => $total_time_viewed . " min"
                ];
                array_push($channelArray, $chnls);
            }
            array_multisort(array_column($channelArray, 'start'), SORT_ASC, $channelArray);
            $rows = count($channelArray);
            return response()->json(["rows" => $rows, "channels" => $channelArray], 200);
        }
        return response()->json(["error" => "Error"], 200);
    }

    function user_info(Request $req)
    {
        $user = User::where('id', $req->user)->first();
        $user->device_name = $user->device->device_name;
        $user->device_box_id = $user->device->deviceBox->id;

        if ($user->gender == "m") {
            $user->gender = "Male";
        } elseif ($user->gender == "f") {
            $user->gender = "Female";
        }

        if ($user->device->economic_status == "a") {
            $user->economic_status = "SEC A";
        } elseif ($user->device->economic_status == "b") {
            $user->economic_status = "SEC B";
        } elseif ($user->device->economic_status == "c") {
            $user->economic_status = "SEC C";
        } elseif ($user->device->economic_status == "d") {
            $user->economic_status = "SEC D";
        } elseif ($user->device->economic_status == "e") {
            $user->economic_status = "SEC E";
        } else {
            $user->economic_status = $user->device->economic_status;
        }


        $user->age = Carbon::parse($user->dob)->diff(Carbon::now())->y;
        $user->box_id = $user->device->deviceBox->id;

        return response()->json(["user" => $user], 200);
    }

    function getUserFilterDataList()
    {
        $data = UserDataFilter::orderBy('id', 'DESC')->get();
        foreach ($data as $d) {
            if ($d->channel_id) {
                $ch = Channel::where('id', $d->channel_id)->select('channel_name')->first();
                $d->channel_name = $ch->channel_name;
            }


            if ($d->gender == "m") {
                $d->gender = "Male";
            } elseif ($d->gender == "f") {
                $d->gender = "Female";
            } else {
                $d->gender = "All";
            }
        }
        return response()->json($data);
    }

    function userFilterValueAdd(Request $req)
    {
        $validator = Validator::make($req->all(), $this->UserFilterData_rules());
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $udf = new UserDataFilter();
        $udf->filter_name = $req->filter_name;
        $udf->channel_id = $req->channel_id;
        $udf->start = $req->start;
        $udf->finish = $req->finish;
        $udf->gender = $req->gender;
        $udf->from_age = $req->from_age;
        $udf->to_age = $req->to_age;
        $udf->created_at = date('Y-m-d H:i:s');
        $udf->save();


        // $userFilterData = (object)$req->all();
        // //$userFilterData->survey_date = date('Y-m-d H:i:s');
        // //$userFilterData->installation_date = date('Y-m-d H:i:s');
        // //return response()->json(["message"=>$user->user_name]);
        // $d = UserDataFilter::create((array)$userFilterData);

        return response()->json(["message" => "User Filter Added Successfully"], 200);
    }

    function UserFilterData_rules()
    {
        return [
            "filter_name" => "required",
            "channel_id" => "required",
            "start" => "required",
            "finish" => "required",
            "from_age" => "numeric|lt:to_age",
            "to_age" => "numeric|",
        ];
    }


    function generate_userFilterData()
    {
        $data = UserDataFilter::where('generate_flag', 0)->get();
        $all_data = [];
        foreach ($data as $d) {

            $startDateTime = $d->start;
            $finishDateTime = $d->finish;
            $minDate = Carbon::today()->subYears($d->to_age + 1); // make sure to use Carbon\Carbon in the class
            $maxDate = Carbon::today()->subYears($d->from_age)->endOfDay();

            $logs = ViewLog::where('view_logs.channel_id', $d->channel_id)
                ->where(function ($query) use ($startDateTime, $finishDateTime) {
                    $query->where('view_logs.finished_watching_at', '>', $startDateTime)
                        ->orWhereNull('view_logs.finished_watching_at');
                })
                ->where('view_logs.started_watching_at', '<', $finishDateTime)
                ->where('users.gender', 'like', '%' . $d->gender . '%')
                ->whereBetween('users.dob', [$minDate, $maxDate])
                ->join('users', 'users.id', '=', 'view_logs.user_id')
                ->select('users.id', 'users.user_name', 'users.device_id')
                ->distinct('view_logs.user_id')->get();

            //$logs->gid = $d->id;
            if (count($logs) > 0) {
                $d->generate_flag = 1;
                $d->generated_data = json_encode($logs);
                $d->save();
            } else {
                $d->generate_flag = 1;
                //$d->generated_data = json_encode($logs);
                $d->save();
            }

            //array_push($all_data, $logs);

        }
        return response()->json(["Data" => "data"], 200);
    }

    function getUserFilter_generatedData($view_id)
    {

        $data = UserDataFilter::where('id', $view_id)->where('generate_flag', 1)->first();
        if ($data) {
            $data->generated_data = json_decode($data->generated_data);
            return response()->json(["data" => $data->generated_data], 200);
        }
    }



    function getDatesBetween($startDate, $endDate)
    {
        $dateArray = array();

        $currentDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);

        while ($currentDate < $endDate) {

            $currentDate->modify('+1 day');
            $dateArray[] = $currentDate->format('Y-m-d');
        }

        return $dateArray;
    }
    function demo_test()
    {
        //$this->systemUniverse();
        $this->systemUniverseAll();
        return response()->json(["data" => "done"], 200);
        $endDate = DataCleanse::where('status', 1)->latest('id')->first()->date;
        $startDate = systemUniverse::max('date_of_gen');
        $dates = $this->getDatesBetween($startDate, $endDate);
        return response()->json(["data" => $dates], 200);
    }
    function systemUniverse()
    {
        $deselectedIds = DeselectPeriod::where('end_date', null)->pluck('device_id')->toArray();
        $dids = Device::pluck('id')->toArray();
        $list = [];
        $divisions = ["dhaka", "barishal", "chattogram", "khulna", "mymensingh", "rajshahi", "rangpur", "sylhet"];
        $genders = ["m", "f"];
        $ageGroups = ["0-14", "15-24", "25-34", "35-44", "45 & Above"];
        $secs = ["a", "b", "c", "d", "e"];
        $ageGroupListNumRange = [
            [0, 14],
            [15, 24],
            [25, 34],
            [35, 44],
            [45, 150],
        ];

        $cc = 0;
        foreach ($divisions as $division) {
            $deviceIds = Device::where('district', strtolower($division))
                ->whereNotIn('Id', $deselectedIds)
                ->pluck('Id')
                ->toArray();


            foreach ($genders as $gender) {
                foreach ($ageGroups as $i => $ageGroup) {
                    foreach ($secs as $s) {
                        $noOfUsers = User::whereIn('Device_Id', $deviceIds)
                            ->where('economic_status', 'like', '%' .  $s . '%')
                            ->where('gender', 'like', '%' .  $gender . '%')
                            ->where('address', 'like', '%' . $division . '%')
                            ->whereRaw('YEAR(CURDATE()) - YEAR(dob) >= ?', [$ageGroupListNumRange[$i][0]])
                            ->whereRaw('YEAR(CURDATE()) - YEAR(dob) <= ?', [$ageGroupListNumRange[$i][1]])
                            ->whereNotNull('dob')
                            ->whereNotNull('economic_status')
                            ->whereNotNull('gender')
                            ->whereNotNull('address')
                            ->count();
                        $cc = $cc + $noOfUsers;
                        //$list[]
                        $obj = new SystemUniverse([
                            'date_of_gen' => now()->toDateString(),
                            'Gender' => $gender,
                            'Region' => $division,
                            'Sec' => $s,
                            'Age_Group' => $ageGroup,
                            'Universe' => $noOfUsers,
                        ]);
                        $obj->save();
                    }
                }
            }
        }

        try {
            //SystemUniverse::insert($list);
            return response()->json(['message' => 'done', 'count' => $cc]);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()]);
        }
        //return $hash;
        //return response()->json(["systemUniverse" => $systemUniverse,"mindate"=>$minDate,"maxdate"=>$maxDate,"a"=>$age_group,"u"=>$universe], 200);
    }
    function systemUniverseAll()
    {
        //$deselectedIds = DeselectPeriod::where('end_date', null)->pluck('device_id')->toArray();
        $dids = Device::pluck('id')->toArray();
        $list = [];
        $divisions = ["dhaka", "barishal", "chattogram", "khulna", "mymensingh", "rajshahi", "rangpur", "sylhet"];
        $genders = ["m", "f"];
        $ageGroups = ["0-14", "15-24", "25-34", "35-44", "45 & Above"];
        $secs = ["a", "b", "c", "d", "e"];
        $ageGroupListNumRange = [
            [0, 14],
            [15, 24],
            [25, 34],
            [35, 44],
            [45, 150],
        ];

        $cc = 0;
        foreach ($divisions as $division) {
            $deviceIds = Device::where('district', strtolower($division))
                //->whereNotIn('Id', $deselectedIds)
                ->pluck('Id')
                ->toArray();


            foreach ($genders as $gender) {
                foreach ($ageGroups as $i => $ageGroup) {
                    foreach ($secs as $s) {
                        $noOfUsers = User::whereIn('Device_Id', $deviceIds)
                            ->where('economic_status', 'like', '%' .  $s . '%')
                            ->where('gender', 'like', '%' .  $gender . '%')
                            ->where('address', 'like', '%' . $division . '%')
                            ->whereRaw('YEAR(CURDATE()) - YEAR(dob) >= ?', [$ageGroupListNumRange[$i][0]])
                            ->whereRaw('YEAR(CURDATE()) - YEAR(dob) <= ?', [$ageGroupListNumRange[$i][1]])
                            ->whereNotNull('dob')
                            ->whereNotNull('economic_status')
                            ->whereNotNull('gender')
                            ->whereNotNull('address')
                            ->count();
                        $cc = $cc + $noOfUsers;
                        //$list[]
                        $obj = new SystemUniverseAll([
                            'date_of_gen' => now()->toDateString(),
                            'Gender' => $gender,
                            'Region' => $division,
                            'Sec' => $s,
                            'Age_Group' => $ageGroup,
                            'Universe' => $noOfUsers,
                        ]);
                        $obj->save();
                    }
                }
            }
        }

        try {
            //SystemUniverse::insert($list);
            return response()->json(['message' => 'done', 'count' => $cc]);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()]);
        }
        //return $hash;
        //return response()->json(["systemUniverse" => $systemUniverse,"mindate"=>$minDate,"maxdate"=>$maxDate,"a"=>$age_group,"u"=>$universe], 200);
    }
    public function dayrangedtrendsave($req)
    {
        $type = $req->type;
        if ($req->type == "") {
            $type = "all";
        }
        $channel = Channel::all();
        $c_count = $channel->count();
        $count = 0;
        $pcount = 0;
        foreach ($channel as $c) {
            $daypartcheck = DayPartProcess::where('channel_id', $c->id)
                ->where('type', 'like', '%' . $type . '%')
                ->where('time_range', $req->range)
                ->where('day', $req->day)
                ->first();
            //return response()->json(["done" => $daypartcheck], 200);
            if ($daypartcheck) {

                $pcount++;

                continue;
            } else {

                DayPartProcess::create(["channel_id" => $c->id, "day" => $req->day, "time_range" => $req->range, "type" => (($type != "") ? $type : "all")]);
            }
            /*
            $userids = User::where('type', 'like', '%' . $req->type . '%')
                ->pluck('id')->toArray();
            //return response()->json(["time" => $channel->channel_name], 200);
            $all = [["Time-Frame", "Reach(000)", "Reach(%)", "TVR(000)", "TVR(%)"]];
            $users = User::all();
            // Create an array of DateOnly objects
            $dates = [];
            $startDate_ = Carbon::parse($req->day);
            $endDate_ = Carbon::parse($req->day);

            for ($date = $startDate_; $date->lte($endDate_); $date->addDay()) {
                $dates[] = $date->toDateString();
            }

            // Query the database using Eloquent
            $allUniverses = Universe::
                get();

            $suniverses = [];
            foreach ($dates as $date) {
                $uCount = $allUniverses->where('start', '<=', $date)
                    ->where('end', '>=', $date)
                    ->sum('universe');

                $suniverses[] = [
                    'date' => $date,
                    'unum' => $uCount / 1000,
                ];
            }

            $universe_size = max(array_column($suniverses, 'unum'));
            $numOfUser = Universe::sum('universe')/1000;//$users->count();
            //$numOfUser = $users->count();
            $time = $this->dayrange($req->day, $req->day, ((int)$req->range));

            //   return response()->json(["time" => $time], 200);
            if (((int)$req->range) == 30) {
                $dd = 30 * count($time);
                $m = 900;
                $reachs = array([], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], []);
                $reachp = array([], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], []);
                $reach0 = array([], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], []);

                $tvrs = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
                $tvrp = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
                $tvr0 = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
            } else {
                $dd = 15 * count($time);
                $m = 450;
                $reachs = array([], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], []);
                $reachp = array([], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], []);
                $reach0 = array([], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], []);

                $tvrs = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
                $tvrp = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
                $tvr0 = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
            }
            //return response()->json(["time" => count($reachs)], 200);
            $label = array();
            foreach ($time as $tt) {
                for ($i = 0; $i < count($tt); $i++) {

                    // $viewers = $this->views($req->id, $tt[$i]["start"], $tt[$i]["finish"]);
                    // $watchtime = $this->timeviewed($req->id, $tt[$i]["start"], $tt[$i]["finish"]);

                    $timeandviewers = $this->timeandviewed($c->id, $userids, $tt[$i]["start"], $tt[$i]["finish"]);
                    $viewers = $timeandviewers->view;
                    $watchtime = $timeandviewers->time;
                    //return response()->json(["ok"=>"ss","time" => $viewers ], 200);
                    //return response()->json(["time" => $viewers], 200);
                    if (!empty($viewers)) {

                        $reachs[$i] = array_merge($reachs[$i], $viewers);
                    }

                    $tvrs[$i] = $tvrs[$i] + $watchtime;
                }
            }
            /*int total_sample = Samplemax;
            double total_timespent = time.Sum() / 60;
            double total_reach = this.get_reach(userss);
            int total_reachint = (int)total_reach;
            double total_reachp = total_reach / total_sample * 100;
            double total_share = (!alltimef) ? ((total_timespent > 0) ? 100 : 0) : ((alltime[allc] == 0) ? 0 : total_timespent / (total_sample * alltime[allc]) * 100);
            total_timespent = total_timespent / total_sample;
            double total_tvrp = total_timespent * 100 / timerange;
            double total_tvr = total_tvrp * total_sample / 100;*/
            /*
            for ($i = 0; $i < count($tt); $i++) {
                $rr=(int)$this->mult_sum($reachs[$i]);

                $reachp[$i] = ($rr * 100) / $numOfUser;
                $reach0[$i] = $rr;
                
                $tvrp[$i] = ($tvrs[$i] / ($numOfUser * $dd)) * 100;
                $tvr0[$i] = $tvrp[$i]*$numOfUser / 100;

                //$tvrp[$i] = ($tvrs[$i] / ($numOfUser * $dd)) * 100;
                //$mid = strtotime("+" . $m . " seconds", strtotime($time[0][$i]["start"]));
                //$mid = date("H:i:s", $mid);
                $mid = date("H:i:s", strtotime($time[0][$i]["start"]));
                array_push($label, $mid);
                array_push($all, [$mid, $reach0[$i], $reachp[$i], $tvr0[$i], $tvrp[$i]]);
            }*/
            DayPart::create(["channel_id" => $c->id, "day" => $req->day, "time_range" => $req->range, "type" => (($type != "") ? $type : "all"), "data" => "dd"/*json_encode(((object)(["label" => $label, "reach0" => $reach0, "reachp" => $reachp, "tvr0" => $tvr0, "tvrp" => $tvrp])))*/]);
            $count++;
        }
    }
}
