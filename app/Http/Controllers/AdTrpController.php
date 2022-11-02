<?php

namespace App\Http\Controllers;

use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use App\Models\PlayoutFile;
use App\Models\PlayoutLog;
use App\Models\AdTrp;
use Illuminate\Http\Request;
use App\Models\Keyword;
use App\Models\Token;
use Illuminate\Support\Facades\DB;
class AdTrpController extends Controller
{
    //
    public function allkeyword()
    {
        $cname = AdTrp::distinct()->pluck('commercial_name')->toArray();
        return response()->json(["value" => $cname], 200);
    }
    public function adtrpall()
    {

        $viewer = array();
        $viewert = array();
        $num = 0;
        $users = User::all();
        $numOfUser = $users->count();
        $date = date('Y-m-d', strtotime("-1 days"));
        $logs = PlayoutLog::where('date', $date)
            ->get();
        //return response()->json(["value" => $logs], 200);
        foreach ($logs as $log) {
            //if (((int)$log->done) == 0) {
            //return response()->json(["value" => $log->channel->channel_name], 200);
            $start = $log->start;
            $finish = $log->finish;
            $fromTime = strtotime($log->start);
            $toTime = strtotime($log->finish);
            $diff = abs($fromTime - $toTime) / 60;


            $viewlogs = ViewLog::where('channel_id', $log->channel_id)
                ->where(function ($query) use ($start) {
                    $query->where('finished_watching_at', '>', $start)
                        ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at', '<', $finish)
                ->distinct()->get('user_id');

            $user_count = $viewlogs->count();
            $reach0 = $user_count;
            $user_count = ($user_count / $numOfUser) * 100;
            $reachp = $user_count;

            $viewers = ViewLog::where('channel_id', $log->channel_id)
                ->where(function ($query) use ($start) {
                    $query->where('finished_watching_at', '>', $start)
                        ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at', '<', $finish)
                ->get();

            foreach ($viewers as $v) {
                if (((strtotime($v->started_watching_at)) < ($fromTime)) && (((strtotime($v->finished_watching_at)) > ($toTime)) || (($v->finished_watching_at) == Null))) {
                    $watched_sec = abs($fromTime - $toTime);
                } else if (((strtotime($v->started_watching_at)) < ($fromTime)) && ((strtotime($v->finished_watching_at)) <= ($toTime))) {
                    $watched_sec = abs($fromTime - strtotime($v->finished_watching_at));
                } else if (((strtotime($v->started_watching_at)) >= ($fromTime)) && (((strtotime($v->finished_watching_at)) > ($toTime)) || (($v->finished_watching_at) == Null))) {
                    $watched_sec = abs(strtotime($v->started_watching_at) - $toTime);
                } else {
                    $watched_sec = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                }
                //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                //$watched_sec = $watched_sec / 60;
                array_push($viewer, $watched_sec);
            }
            //return response()->json([$viewer],200);
            $timewatched = array_sum($viewer);
            $tvr = $timewatched / $numOfUser; ///$numOfUser;
            $tvr = $tvr / 60;
            $tvr = $tvr / $diff;
            $tvr0 = $tvr;
            $tvr = $tvr * 100;
            $tvrp = $tvr;
            unset($viewer);
            $viewer = array();
            $adtrparr = [
                "commercial_name" => $log->commercial_name, "program" => $log->program, "channel_id" => $log->channel_id, "channel_name" => $log->channel->channel_name, "date" => $log->date,
                "start" => $log->start, "finish" => $log->finish, "timewatched" => $timewatched, "duration" => $log->duration, "tvrp" => $tvrp, "tvr0" => $tvr0, "reach0" => $reach0, "reachp" => $reachp, "playlog_id" => $log->id
            ];
            //return response()->json(["value" => $adtrparr,"log"=>$log], 200);
            if (AdTrp::create($adtrparr)) {
                $log->done = 1;
                $log->save();
            }
            $num++;

            //array_push($values, $tvr);  
            //return response()->json(["value" => $log->channel_id], 200);
            //}
        }
        return response()->json(["num" => $num, "value" => "done"], 200);
    }
    public function dailyadtrp(Request $req)
    {
        if ($req->date == "") {
            $date = date('Y-m-d', strtotime("-1 days"));
        } else {
            $date = date('Y-m-d', strtotime($req->date));
        }
        $adtrps = AdTrp::where('date', $date)
            ->get();

        return response()->json(["value" => $adtrps], 200);
    }
    public function keywordadtrp(Request $req)
    {
        if ($req->date == "") {
            $date = date('Y-m-d', strtotime("-1 days"));
        } else {
            $date = date('Y-m-d', strtotime($req->date));
        }
        $adtrps = AdTrp::where('date', $date)
        ->wherein('commercial_name',$req->keywords)
            ->get();

        return response()->json(["value" => $adtrps], 200);
    }


    public function channelwiseadtrp(Request $req)
    {
        if ($req->date == "") {
            $date = date('Y-m-d', strtotime("-1 days"));
        } else {
            $date = date('Y-m-d', strtotime($req->date));
        }
        $adtrps = AdTrp::where('date', $date)
            ->where('channel_id', $req->id)
            ->get();

        return response()->json(["value" => $adtrps], 200);
    }

    public function frequency(Request $req)
    {
        $start = date('Y-m-d H:i:s', strtotime($req->start));
        //$finish=date('Y-m-d H:i:s', strtotime($req->finish));
        $c1 = 0;
        $c2 = 0;
        $c3 = 0;
        $c4 = 0;
        $c5 = 0;
        $arr = array();
        $viewlogs = ViewLog::where('channel_id', (int)$req->channel_id)
            ->where(function ($query) use ($start) {
                $query->where('finished_watching_at', '>', $start)
                    ->orWhereNull('finished_watching_at');
            })
            ->where('started_watching_at', '<', $req->finish)
            ->pluck('user_id');
        foreach ($viewlogs as $v) {
            array_push($arr, $v);
        }
        //return response()->json(["value" => $arr], 200);
        //$array = array(1,2,'v', 'v1', 'v2', 'v2', 'v3', 'v3', 'v3', 'val4', 'val4', 'val4', 'val4', 'val5', 'val5', 'val5', 'val5', 'val5');

        //return response()->json(["value" => $viewlogs,"value1" => $array], 200);
        $array = $arr;
        //$array=$viewlogs;
        $cnt = array_count_values($array);
        foreach ($cnt as $c) {
            switch ($c) {
                case 5:
                    $c5++;
                    $c4++;
                    $c3++;
                    $c2++;
                    $c1++;
                    break;
                case 4:
                    $c4++;
                    $c3++;
                    $c2++;
                    $c1++;
                    break;
                case 3:
                    $c3++;
                    $c2++;
                    $c1++;
                    break;
                case 2:
                    $c2++;
                    $c1++;
                    break;
                case 1:
                    $c1++;
                    break;
            }
        }
        $count = array("1s" => $c1, "2s" => $c2, "3s" => $c3, "4s" => $c4, "5s" => $c5);

        return $count;
    }
    public function adtrptvrp(Request $request)
    {
        $values = [];
        $viewer = array();
        $users = User::all();
        $numOfUser = $users->count();
        foreach ($request->ranges as $req) {
            $startDate = substr($req["start"], 0, 10);
            $startTime = substr($req["start"], 11, 19);
            $finishDate = substr($req["finish"], 0, 10);
            $finishTime = substr($req["finish"], 11, 19);
            $startDateTime = date($startDate) . " " . $startTime;
            $finishDateTime = date($finishDate) . " " . $finishTime;
            $fromTime = strtotime($startDateTime);
            $toTime = strtotime($finishDateTime);
            $diff = abs(strtotime($startDateTime) - strtotime($finishDateTime)) / 60;
            $channel = Channel::where('channel_name', $req["channel"])->first('id');
            $id = $channel->id;
            $viewers = ViewLog::where('channel_id', $id)
                ->where(function ($query) use ($startDateTime, $finishDateTime) {
                    $query->where('finished_watching_at', '>', $startDateTime)
                        ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at', '<', $finishDateTime)
                ->get();

            foreach ($viewers as $v) {
                if (((strtotime($v->started_watching_at)) < ($fromTime)) && (((strtotime($v->finished_watching_at)) > ($toTime)) || (($v->finished_watching_at) == Null))) {
                    $watched_sec = abs($fromTime - $toTime);
                } else if (((strtotime($v->started_watching_at)) < ($fromTime)) && ((strtotime($v->finished_watching_at)) <= ($toTime))) {
                    $watched_sec = abs($fromTime - strtotime($v->finished_watching_at));
                } else if (((strtotime($v->started_watching_at)) >= ($fromTime)) && (((strtotime($v->finished_watching_at)) > ($toTime)) || (($v->finished_watching_at) == Null))) {
                    $watched_sec = abs(strtotime($v->started_watching_at) - $toTime);
                } else {
                    $watched_sec = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                }
                //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                $watched_sec = $watched_sec / 60;
                array_push($viewer, $watched_sec);
            }
            //return response()->json([$viewer],200);
            $tvr = array_sum($viewer) / $numOfUser; ///$numOfUser;
            //$tvr=$tvr/60;
            $tvr = $tvr / $diff;
            $tvr = $tvr * 100;
            unset($viewer);
            $viewer = array();
            array_push($values, $tvr);
        }
        return response()->json(["value" => $values], 200);
    }
    public function getKeywords(Request $req){
        $user = $this->getAuth($req);
        $keywords = Keyword::where('agency_id',$user->user_name)->get();
        return response($keywords,200);

    }

    public function addKeyword(Request $req){
        $var = new Keyword();
        $var->keyword = $req->keyword;
        $var->agency_id= $this->getAuth($req)->user_name;
        $var->save();
        return response()->json(["msg"=>"stored"],200);

    }
    public function removeKeyword(Request $req){
        $var = Keyword::where('id',$req->id)->delete();
        return response()->json(["msg"=>"deleted"],200);
    }
    public function getAuth(Request $req){
        $token = $req->header('Authorization');
        $userToken = Token::where('token', $token)->first();
        $user = $userToken->login;
        return $user;
    }
    public function getAdTrp(Request $req){
        $date = $req->date;
        //$date = '2022-10-27';
        $user = $this->getAuth($req);
        $keywords = Keyword::where('agency_id',$user->user_name)->pluck('keyword')->toArray();
        $adtrps =DB::Table('adtrps')
        ->select('*')                
        ->Where(function ($query) use($keywords) {
             for ($i = 0; $i < count($keywords); $i++){
                $query->orwhere('commercial_name', 'like',  '%' . $keywords[$i] .'%');
             }      
        })->get();
        return response()->json(["trps"=>$adtrps,"date"=>$date],200);
    }
}
