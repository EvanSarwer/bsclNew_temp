<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use DateTime;

class TrendController extends Controller
{
    //
    public function rangedtrendreachp(Request $req)
    {
        $time = $this->ranged_time($req->range, $req->start, $req->finish);
        $reachs = array();
        $label = array();
        $len = count($time) - 1;
        //return response()->json(["time"=>$time],200);
        $reachs = array();
        $users = User::all();
        $numOfUser = $users->count();
        for ($i = 0; $i < $len; $i++) {

            $viewers = $this->reach($req->id, $time[$i], $time[$i + 1]);

            array_push($reachs, ($viewers / $numOfUser) * 100);
            if ($req->range == "15") {
                $mid = strtotime("+450 seconds", strtotime($time[$i]));
                $mid = date("Y-m-d H:i:s", $mid);
                array_push($label, $mid);
            } else {
                $mid = strtotime("+900 seconds", strtotime($time[$i]));
                $mid = date("Y-m-d H:i:s", $mid);
                array_push($label, $mid);
            }
        }

        return response()->json(["values" => $reachs], 200);
    }
    //
    public function rangedtrendreach0(Request $req)
    {

        $time = $this->ranged_time($req->range, $req->start, $req->finish);
        $reachs = array();
        $label = array();
        $len = count($time) - 1;
        for ($i = 0; $i < $len; $i++) {
            $viewers = $this->reach($req->id, $time[$i], $time[$i + 1]);
            array_push($reachs, $viewers);
            if ($req->range == "15") {
                $mid = strtotime("+450 seconds", strtotime($time[$i]));
                $mid = date("Y-m-d H:i:s", $mid);
                array_push($label, $mid);
            } else {
                $mid = strtotime("+900 seconds", strtotime($time[$i]));
                $mid = date("Y-m-d H:i:s", $mid);
                array_push($label, $mid);
            }
        }
        return response()->json(["values" => $reachs, "label" => $label, "time" => $time], 200);
    }

    public function reach($id, $start, $finish)
    {
        $viewers = ViewLog::where('channel_id', $id)
            ->where(function ($query) use ($start) {
                $query->where('finished_watching_at', '>', $start)
                    ->orWhereNull('finished_watching_at');
            })
            ->where('started_watching_at', '<', $finish)
            ->select('user_id')
            ->distinct('user_id')
            ->count();
        return $viewers;
    }
    public function tvr($id, $start, $finish, $type)
    {

        $viewer = array();
        $start_range = strtotime($start);
        $finish_range = strtotime($finish);
        $diff = abs($start_range - $finish_range) / 60;
        $users = User::all();
        $numOfUser = $users->count();
        $ldate = date('Y-m-d H:i:s');
        $viewers = ViewLog::where('channel_id', $id)
            ->where(function ($query) use ($start) {
                $query->where('finished_watching_at', '>', $start)
                    ->orWhereNull('finished_watching_at');
            })
            ->where('started_watching_at', '<', $finish)
            ->get();


        foreach ($viewers as $v) {
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
            $timeviewd = $timeviewd / 60;
            array_push($viewer, $timeviewd);
        }
        if ($type == "p") {
            $tvr = array_sum($viewer) / $numOfUser;
            $tvr = $tvr / $diff;
            $tvr = $tvr * 100;
        } else {
            $tvr = array_sum($viewer) / $numOfUser;
            $tvr = $tvr / $diff;
            $tvr = $tvr; //*100; 
        }
        unset($viewer);
        $viewer = array();

        return $tvr;
    }

    public function rangedtrendtvrp(Request $req)
    {
        $time = $this->ranged_time($req->range, $req->start, $req->finish);
        $tvrs = array();
        $label = array();
        $len = count($time) - 1;
        //return response()->json(["time"=>$time],200);
        $tvrs = array();
        $users = User::all();
        $numOfUser = $users->count();
        for ($i = 0; $i < $len; $i++) {

            $viewers = $this->tvr($req->id, $time[$i], $time[$i + 1], "p");

            array_push($tvrs, $viewers * 100);
            if ($req->range == "15") {
                $mid = strtotime("+450 seconds", strtotime($time[$i]));
                $mid = date("Y-m-d H:i:s", $mid);
                array_push($label, $mid);
            } else {
                $mid = strtotime("+900 seconds", strtotime($time[$i]));
                $mid = date("Y-m-d H:i:s", $mid);
                array_push($label, $mid);
            }
        }

        return response()->json(["values" => $tvrs], 200);
    }
    //
    public function rangedtrendtvr0(Request $req)
    {

        $time = $this->ranged_time($req->range, $req->start, $req->finish);
        $tvrs = array();
        $label = array();
        $len = count($time) - 1;
        for ($i = 0; $i < $len; $i++) {
            $viewers = $this->tvr($req->id, $time[$i], $time[$i + 1], "0");
            array_push($tvrs, $viewers);
            if ($req->range == "15") {
                $mid = strtotime("+450 seconds", strtotime($time[$i]));
                $mid = date("Y-m-d H:i:s", $mid);
                array_push($label, $mid);
            } else {
                $mid = strtotime("+900 seconds", strtotime($time[$i]));
                $mid = date("Y-m-d H:i:s", $mid);
                array_push($label, $mid);
            }
        }
        return response()->json(["values" => $tvrs, "label" => $label, "time" => $time], 200);
    }


    public function ranged_time($range, $start, $finish)
    {
        $startDate = substr($start, 0, 10);
        $startTime = substr($start, 11, 19);
        $finishDate = substr($finish, 0, 10);
        $finishTime = substr($finish, 11, 19);
        $startDateTime = date($startDate) . " " . $startTime;
        $finishDateTime = date($finishDate) . " " . $finishTime;
        $time = array();
        array_push($time, date("Y-m-d H:i:s", strtotime($startDateTime)));
        while (true) {

            $demotime = strtotime("+" . $range . " minutes", strtotime($time[count($time) - 1]));
            if ($demotime < strtotime($finishDateTime)) {
                array_push($time, date("Y-m-d H:i:s", $demotime));
            } else {
                break;
            }
        }
        return $time;
    }
}
