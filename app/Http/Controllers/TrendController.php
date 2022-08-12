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


    public function dayrangedtrendreach0(Request $req)
    {

        //$numOfUser = $users->count();
        $time = $this->dayrange($req->start, $req->finish, ((int)$req->range));

        //   return response()->json(["time" => $time], 200);
        if (((int)$req->range) == 30) {
            $m = 900;
            $reachs = array([], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], []);
        } else {
            $m = 450;
            $reachs = array([], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], []);
        }
        //return response()->json(["time" => count($reachs)], 200);
        $label = array();
        foreach ($time as $tt) {
            for ($i = 0; $i < count($tt); $i++) {
                $viewers = $this->views($req->id, $tt[$i]["start"], $tt[$i]["finish"]);
                //return response()->json(["time" => $viewers], 200);
                //return response()->json(["time" => $viewers], 200);
                if (!empty($viewers)) {
                    //return response()->json(["time" => $viewers,"timer" => $i], 200);
                    $reachs[$i] = array_merge($reachs[$i], $viewers);
                }
            }
        }
        for ($i = 0; $i < count($tt); $i++) {
            $reachs[$i] = count(array_unique($reachs[$i]));
            $mid = strtotime("+" . $m . " seconds", strtotime($time[0][$i]["start"]));
            $mid = date("H:i:s", $mid);
            array_push($label, $mid);
        }
        return response()->json(["values" => $reachs, "label" => $label], 200);
    }


    public function dayrangedtrendreachp(Request $req)
    {
        $users = User::all();
        $numOfUser = $users->count();
        $time = $this->dayrange($req->start, $req->finish, ((int)$req->range));

        //   return response()->json(["time" => $time], 200);
        if (((int)$req->range) == 30) {
            $m = 900;
            $reachs = array([], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], []);
        } else {
            $m = 450;
            $reachs = array([], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], []);
        }
        //return response()->json(["time" => count($reachs)], 200);
        $label = array();
        foreach ($time as $tt) {
            for ($i = 0; $i < count($tt); $i++) {
                $viewers = $this->views($req->id, $tt[$i]["start"], $tt[$i]["finish"]);
                //return response()->json(["time" => $viewers], 200);
                //return response()->json(["time" => $viewers], 200);
                if (!empty($viewers)) {
                    //return response()->json(["time" => $viewers,"timer" => $i], 200);
                    $reachs[$i] = array_merge($reachs[$i], $viewers);
                }
            }
        }
        for ($i = 0; $i < count($tt); $i++) {
            $reachs[$i] = (count(array_unique($reachs[$i]))*100)/$numOfUser;
            $mid = strtotime("+" . $m . " seconds", strtotime($time[0][$i]["start"]));
            $mid = date("H:i:s", $mid);
            array_push($label, $mid);
        }
        return response()->json(["values" => $reachs, "label" => $label], 200);
    }


    public function views($id, $start, $finish)
    {
        $vv = array();
        $viewers = ViewLog::where('channel_id', $id)
            ->where(function ($query) use ($start) {
                $query->where('finished_watching_at', '>', $start)
                    ->orWhereNull('finished_watching_at');
            })
            ->where('started_watching_at', '<', $finish)
            ->select('user_id')
            ->distinct('user_id')
            ->get();
        foreach ($viewers as $v) {
            array_push($vv, $v->user_id);
        }
        return $vv;
    }
    public function dayrange($s, $f, $d)
    {

        $ms = array();
        if ($d == 30) {
            $l = 48;
        } else {
            $l = 96;
        }

        $sa = array();
        while (strtotime($s) <= strtotime($f)) {

            array_push($sa, $s);
            $s = date('Y-m-d', strtotime("+1 day", strtotime($s)));
        }
        foreach ($sa as $s) {
            $ts = array();
            $m = 0;

            for ($i = 0; $i < $l; $i++) { //19
                $st = (date('Y-m-d H:i:s', strtotime("+" . $m . " minutes", strtotime($s))));
                $m = $m + $d;
                $ft = (date('Y-m-d H:i:s', strtotime("+" . $m . " minutes", strtotime($s))));
                $tts = array("start" => $st, "finish" => $ft);

                array_push($ts, $tts);
            }
            array_push($ms, $ts);
            //echo $s."<br/>";
        }
        return $ms;
    }

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
