<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewLog;
use App\Models\Universe;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use DateTime;

use App\Models\DataCleanse;
class TrendController extends Controller
{
    public function trendGeneralAll(Request $req)
    {
        $str1 =strtotime(DataCleanse::where('status',1)->latest('id')->first()->date);
        //$str1 = strtotime("2023-10-9");
        $str2 = strtotime(date("Y-m-d"));
        $n = $str2 - $str1;
        $userids = User::where('type', 'like', '%' . $req->type . '%')
            ->pluck('id')->toArray();
        $fweek = false;
        $weekdays = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
        $time = array();
        $length = 12;
        if ($req->time == "Weekly") {
            $fweek = true;
            $tstring = "w";
            $inc = -174;  //weekly
            $length = 7;
            for ($i = 0; $i < 15; $i++) {
                $inc = $inc + 24;
                //echo "".$inc;
                array_push($time, ((string)$inc) . " hours"." -" . $n . " seconds");
            }
        } elseif ($req->time == "Monthly") {
            $tstring = "d";
            $inc = -750;  //monthly
            $length = 31;
            for ($i = 0; $i < 32; $i++) {
                $inc = $inc + 24;
                //echo "".$inc;
                array_push($time, ((string)$inc) . " hours"." -" . $n . " seconds");
            }
        } elseif ($req->time == "Yearly") {
            $tstring = "y-M";
            $inc = -13;  //yearly
            $length = 12;
            for ($i = 0; $i < 13; $i++) {
                $inc = $inc + 1;
                //echo "".$inc;
                array_push($time, ((string)$inc) . " months"." -" . $n . " seconds");
            }
        } else {
            $inc = -20; //daily
            $tstring = "h A";
            for ($i = 0; $i < 13; $i++) {
                $inc = $inc + 2;
                //echo "".$inc;
                array_push($time, ((string)$inc) . " hours"." -" . $n . " seconds");
            }
        }
        $time1 = array();
        for ($i = 0; $i < count($time); $i++) {
            $time1[$i] = date("Y-m-d H:i:s", strtotime($time[$i]));
        }
        //return response()->json(["time" => $time1], 200);
        $ldate = date('Y-m-d H:i:s');
        $tvrps = array();
        $tvr0s = array();
        $label = array();
        $reachps = array();
        $reach0s = array();
        $totalReachs = array();
        $watchtime = array();
        $reacht = array();
        /*if($req->start=="" && $req->finish==""){
      return response()->json(["reach"=>$reachs,"channels"=>$label],200);
      }
      $startDate=date('Y-m-d',strtotime("-1 days"));
      $startTime="00:00:00";
      $finishDate=date('Y-m-d',strtotime("-1 days"));
      $finishTime="23:59:59";*/
        //$channels = Channel::all('id', 'channel_name');
        $users = User::all();
        // Create an array of DateOnly objects
        $dates = [];
        $startDate_ = Carbon::parse($time[0]);
        $endDate_ = Carbon::parse($time[$length]);

        for ($date = $startDate_; $date->lte($endDate_); $date->addDay()) {
            $dates[] = $date->toDateString();
        }

        // Query the database using Eloquent
        $allUniverses = Universe::get();

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
        $numOfUser = $universe_size; //Universe::sum('universe') / 1000;
        //$numOfUser = $users->count();
        //return response()->json(["reachsum" => array_sum($reachllistnew), "reach" => $reachllistnew, "channels" => $channellistnew], 200);

        //$all=array();
        for ($i = 0; $i < $length; $i++) {

            $viewers = ViewLog::where('channel_id', $req->id)
                ->where(function ($query) use ($time, $i) {
                    $query->where('finished_watching_at', '>', date("Y-m-d H:i:s", strtotime($time[$i])))
                        ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at', '<', date("Y-m-d H:i:s", strtotime($time[$i + 1])))
                ->whereIn('user_id', $userids)
                ->get();

            foreach ($viewers as $v) {
                if ($v->finished_watching_at == null) {
                    if ((strtotime($v->started_watching_at)) < (strtotime($time[$i]))) {
                        $timeviewd = abs(strtotime($time[$i]) - strtotime($ldate));
                    } else if ((strtotime($v->started_watching_at)) >= (strtotime($time[$i]))) {
                        $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($ldate));
                    }
                } else if (((strtotime($v->started_watching_at)) < (strtotime($time[$i]))) && ((strtotime($v->finished_watching_at)) > (strtotime($time[$i + 1])))) {
                    $timeviewd = abs(strtotime($time[$i]) - strtotime($time[$i + 1]));
                } else if (((strtotime($v->started_watching_at)) < (strtotime($time[$i]))) && ((strtotime($v->finished_watching_at)) <= (strtotime($time[$i + 1])))) {
                    $timeviewd = abs(strtotime($time[$i]) - strtotime($v->finished_watching_at));
                } else if (((strtotime($v->started_watching_at)) >= (strtotime($time[$i]))) && ((strtotime($v->finished_watching_at)) > (strtotime($time[$i + 1])))) {
                    $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($time[$i + 1]));
                } else {
                    $timeviewd = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                }
                //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                $timeviewd = $timeviewd / 60;
                $mult = ($v->universe / 1000) / $v->system;
                array_push($watchtime, $timeviewd * $mult);
                array_push($reacht, ["user_id" => $v->user_id, "mult" => $mult]);
            }
            $tvr = array_sum($watchtime) / $numOfUser;
            //$tvr=$tvr/60;
            $diff = (strtotime(date("Y-m-d H:i:s", strtotime($time[$i + 1]))) - strtotime(date("Y-m-d H:i:s", strtotime($time[$i])))) / 60;
            $tvr = $tvr / $diff;
            array_push($tvr0s, $tvr);
            $tvr = ($tvr * 100) / $numOfUser;
            array_push($tvrps, $tvr);
            unset($watchtime);
            $watchtime = array();

            $reach = (int)$this->mult_sum($reacht);
            array_push($reach0s, $reach);
            $reach = $reach * 100 / $numOfUser;
            array_push($reachps, $reach);
            unset($reacht);
            $reacht = array();
            if ($fweek) {
                array_push($label, $weekdays[(int)date($tstring, strtotime($time[$i]))]);
            } else {
                array_push($label, date($tstring, strtotime($time[$i])));
            }
            //array_push($label, date("Y-m-d H:i:s", strtotime($time[$i]))."-".date("Y-m-d H:i:s", strtotime($time[$i+1])));
            //      array_push($reachs, $reach);
            //array_push($reachs,$reach);

        }


        return response()->json(["reachp" => $reachps, "reach0" => $reach0s, "tvrp" => $tvrps, "tvr0" => $tvr0s, "label" => $label], 200);
    }
    public function mult_sum($inputArray)
    {
        $sums = [];

        foreach ($inputArray as $item) {
            $userId = $item["user_id"];
            $mult = $item["mult"];

            // Check if the user ID already exists in the sums array
            if (isset($sums[$userId])) {
                // Update the 'mult' value if it's greater than the current value
                if ($mult > $sums[$userId]["mult"]) {
                    $sums[$userId]["mult"] = $mult;
                }
            } else {
                // If the user ID doesn't exist, add it to the sums array
                $sums[$userId] = ["user_id" => $userId, "mult" => $mult];
            }
        }

        // Convert the associative array to a sequential array
        $modifiedArray = array_values($sums);

        // Calculate the sum of 'mult' values from the modified array
        $sum = 0;
        foreach ($modifiedArray as $item) {
            $sum += $item["mult"];
        }

        return ($sum);
    }


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
    public function dayrangedtrendtvr0(Request $req)
    {
        $users = User::all();
        $numOfUser = $users->count();
        //$numOfUser = $users->count();
        $time = $this->dayrange($req->start, $req->finish, ((int)$req->range));

        //   return response()->json(["time" => $time], 200);
        if (((int)$req->range) == 30) {
            $m = 900;
            $dd = 30 * count($time);
            $reachs = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        } else {
            $m = 450;
            $dd = 15 * count($time);
            $reachs = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        }
        //return response()->json(["time" => count($reachs)], 200);
        $label = array();
        foreach ($time as $tt) {
            for ($i = 0; $i < count($tt); $i++) {
                $viewers = $this->timeviewed($req->id, $tt[$i]["start"], $tt[$i]["finish"]);


                $reachs[$i] = $reachs[$i] + $viewers;
            }
        }
        for ($i = 0; $i < count($tt); $i++) {
            $reachs[$i] = $reachs[$i] / ($numOfUser * $dd);
            $mid = strtotime("+" . $m . " seconds", strtotime($time[0][$i]["start"]));
            $mid = date("H:i:s", $mid);
            array_push($label, $mid);
        }
        return response()->json(["values" => $reachs, "label" => $label], 200);
    }

    public function dayrangedtrendtvrp(Request $req)
    {
        $users = User::all();
        $numOfUser = $users->count();
        //$numOfUser = $users->count();
        $time = $this->dayrange($req->start, $req->finish, ((int)$req->range));

        //   return response()->json(["time" => $time], 200);
        if (((int)$req->range) == 30) {
            $m = 900;
            $dd = 30 * count($time);
            $reachs = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        } else {
            $m = 450;
            $dd = 15 * count($time);
            $reachs = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        }
        //return response()->json(["time" => count($reachs)], 200);
        $label = array();
        foreach ($time as $tt) {
            for ($i = 0; $i < count($tt); $i++) {
                $viewers = $this->timeviewed($req->id, $tt[$i]["start"], $tt[$i]["finish"]);


                $reachs[$i] = $reachs[$i] + $viewers;
            }
        }
        for ($i = 0; $i < count($tt); $i++) {
            $reachs[$i] = ($reachs[$i] / ($numOfUser * $dd)) * 100;
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
            $reachs[$i] = (count(array_unique($reachs[$i])) * 100) / $numOfUser;
            $mid = strtotime("+" . $m . " seconds", strtotime($time[0][$i]["start"]));
            $mid = date("H:i:s", $mid);
            array_push($label, $mid);
        }
        return response()->json(["values" => $reachs, "label" => $label], 200);
    }



    public function views($id, $start, $finish)
    {
        //$id=36;$start="2022-08-12 00:00:00" ;$finish="2022-08-12 00:30:00";
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
        //return response()->json(["values" => $vv], 200);
        return $vv;
    }
    public function timeviewed($id, $start, $finish)
    {
        //$id=36;$start="2022-08-12 00:00:00" ;$finish="2022-08-12 00:30:00";
        $viewer = array();
        $viewers = ViewLog::where('channel_id', $id)
            ->where(function ($query) use ($start) {
                $query->where('finished_watching_at', '>', $start)
                    ->orWhereNull('finished_watching_at');
            })
            ->where('started_watching_at', '<', $finish)
            ->get();

        foreach ($viewers as $v) {

            if (((strtotime($v->started_watching_at)) < (strtotime($start))) && (((strtotime($v->finished_watching_at)) > (strtotime($finish))) || (($v->finished_watching_at) == Null))) {
                $watched_sec = abs(strtotime($start) - strtotime($finish));
            } else if (((strtotime($v->started_watching_at)) < (strtotime($start))) && ((strtotime($v->finished_watching_at)) <= (strtotime($finish)))) {
                $watched_sec = abs(strtotime($start) - strtotime($v->finished_watching_at));
            } else if (((strtotime($v->started_watching_at)) >= (strtotime($start))) && (((strtotime($v->finished_watching_at)) > (strtotime($finish))) || (($v->finished_watching_at) == Null))) {
                $watched_sec = abs(strtotime($v->started_watching_at) - strtotime($finish));
            } else {
                $watched_sec = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
            }
            $watched_sec = $watched_sec / 60;
            array_push($viewer, $watched_sec);
        }

        $vv = array_sum($viewer);

        //return response()->json(["values" => $vv], 200);

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
