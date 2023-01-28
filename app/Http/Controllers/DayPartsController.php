<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\DayPart;
use App\Models\DayPartProcess;
use App\Models\User;
use Carbon\Carbon;
use DateTime;

class DayPartsController extends Controller
{
    //
    public function dayrangedtrendsave(Request $req)
    {

        $channel = Channel::all();
        $c_count = $channel->count();
        $count=0;
        $pcount=0;
        foreach ($channel as $c) {
            $daypartcheck = DayPartProcess::where('channel_id', $c->id)
            ->where('type', 'like', '%' . $req->type . '%')
            ->where('time_range', $req->range)
            ->where('day', $req->start)
            ->first();
            //return response()->json(["done" => $daypartcheck], 200);
            if($daypartcheck){
                
                $pcount++;

                continue;
            }
            else{
                
                DayPartProcess::create(["channel_id" => $c->id, "day" => $req->start, "time_range" => $req->range, "type" => (($req->type != "") ? $req->type : "all")]);
        
            }
            $userids = User::where('type', 'like', '%' . $req->type . '%')
                ->pluck('id')->toArray();
            //return response()->json(["time" => $channel->channel_name], 200);
            $all = [["Time-Frame", "Reach(000)", "Reach(%)", "TVR(000)", "TVR(%)"]];
            $users = User::all();
            $numOfUser = $users->count();
            //$numOfUser = $users->count();
            $time = $this->dayrange($req->start, $req->start, ((int)$req->range));

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
                        //return response()->json(["time" => $viewers,"timer" => $i], 200);
                        $reachs[$i] = array_merge($reachs[$i], $viewers);
                    }

                    //$timeandviewers = $this->timeandviewed($req->id, $tt[$i]["start"], $tt[$i]["finish"]);
                    //(object)(array("time"=>$watchtime,"viewers"=>$viewers));
                    //return response()->json(["time" => $timeandviewers], 200);
                    $tvrs[$i] = $tvrs[$i] + $watchtime;
                }
            }
            for ($i = 0; $i < count($tt); $i++) {

                $reachp[$i] = (count(array_unique($reachs[$i])) * 100) / $numOfUser;
                $reach0[$i] = count(array_unique($reachs[$i]));
                $tvr0[$i] = $tvrs[$i] / ($numOfUser * $dd);

                $tvrp[$i] = ($tvrs[$i] / ($numOfUser * $dd)) * 100;
                $mid = strtotime("+" . $m . " seconds", strtotime($time[0][$i]["start"]));
                $mid = date("H:i:s", $mid);
                array_push($label, $mid);
                array_push($all, [$mid, $reach0[$i], $reachp[$i], $tvr0[$i], $tvrp[$i]]);
            }
            DayPart::create(["channel_id" => $c->id, "day" => $req->start, "time_range" => $req->range, "type" => (($req->type != "") ? $req->type : "all"), "data" => json_encode(((object)(["label" => $label, "reach0" => $reach0, "reachp" => $reachp, "tvr0" => $tvr0, "tvrp" => $tvrp])))]);
            $count++;
        }
        return response()->json(["done" => "properly done","previous"=>$pcount,"new"=>$count,"total"=>$c_count], 200);
        //return response()->json(["channel" => $channel->channel_name, "value" => ((object)(["label" => $label, "reach0" => $reach0, "reachp" => $reachp, "tvr0" => $tvr0, "tvrp" => $tvrp])), "all" => $all], 200);
    }





    public function dayrangedtrendall(Request $req)
    {

        $channel = Channel::where('id', $req->id)
            ->first();
        /*
            $userids=User::where('type', 'like', '%' . $req->type . '%')
            ->pluck('id')->toArray();
        //return response()->json(["time" => $channel->channel_name], 200);
        $all = [["Time-Frame", "Reach(000)", "Reach(%)", "TVR(000)", "TVR(%)"]];
        $users = User::all();
        $numOfUser = $users->count();
        //$numOfUser = $users->count();
        $time = $this->dayrange($req->start, $req->finish, ((int)$req->range));

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
                
                $timeandviewers = $this->timeandviewed($req->id,$userids, $tt[$i]["start"], $tt[$i]["finish"]);
                $viewers = $timeandviewers->view;
                $watchtime = $timeandviewers->time;
                //return response()->json(["ok"=>"ss","time" => $viewers ], 200);
                //return response()->json(["time" => $viewers], 200);
                if (!empty($viewers)) {
                    //return response()->json(["time" => $viewers,"timer" => $i], 200);
                    $reachs[$i] = array_merge($reachs[$i], $viewers);
                }

                //$timeandviewers = $this->timeandviewed($req->id, $tt[$i]["start"], $tt[$i]["finish"]);
                //(object)(array("time"=>$watchtime,"viewers"=>$viewers));
                //return response()->json(["time" => $timeandviewers], 200);
                $tvrs[$i] = $tvrs[$i] + $watchtime;
            }
        }
        for ($i = 0; $i < count($tt); $i++) {

            $reachp[$i] = (count(array_unique($reachs[$i])) * 100) / $numOfUser;
            $reach0[$i] = count(array_unique($reachs[$i]));
            $tvr0[$i] = $tvrs[$i] / ($numOfUser * $dd);

            $tvrp[$i] = ($tvrs[$i] / ($numOfUser * $dd)) * 100;
            $mid = strtotime("+" . $m . " seconds", strtotime($time[0][$i]["start"]));
            $mid = date("H:i:s", $mid);
            array_push($label, $mid);
            array_push($all, [$mid, $reach0[$i], $reachp[$i], $tvr0[$i], $tvrp[$i]]);
        }*/
        $daypart = DayPart::where('channel_id', $req->id)
            ->where('type', 'like', '%' . $req->type . '%')
            ->where('time_range', $req->range)
            ->where('day', '>=', $req->start)
            ->where('day', '<=', $req->finish)
            ->get();
        //return response()->json(["channel" => count($daypart)],200);
        $label = (json_decode($daypart[0]->data))->label;
        if ((int)$req->range == 30) {

            $f = 48;
            $reachp = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $reach0 = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $tvrp = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $tvr0 = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        } else {
            $f = 96;
            $reachp = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $reach0 = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $tvrp = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $tvr0 = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        }
        //return response()->json(["channel" => count($daypart)],200);
        foreach ($daypart as $d) {
            for ($i = 0; $i < $f; $i++) {

                $reachp[$i] += (json_decode($d->data))->reachp[$i];
                $reach0[$i] += (json_decode($d->data))->reach0[$i];
                $tvrp[$i] += (json_decode($d->data))->tvrp[$i];
                $tvr0[$i] += (json_decode($d->data))->tvr0[$i];
            }
        }
        return response()->json(["channel" => $channel->channel_name, "value" => ((object)(["label" => $label, "reach0" => $reach0, "reachp" => $reachp, "tvr0" => $tvr0, "tvrp" => $tvrp]))], 200);
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


    public function timeandviewed($id, $userids, $start, $finish)
    {
        //$id=36;$start="2022-08-12 00:00:00" ;$finish="2022-08-12 00:30:00";
        $time = array();

        $view = array();
        $viewers = ViewLog::where('channel_id', $id)
            ->where(function ($query) use ($start) {
                $query->where('finished_watching_at', '>', $start)
                    ->orWhereNull('finished_watching_at');
            })
            ->where('started_watching_at', '<', $finish)
            ->whereIn('user_id', $userids)
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
            array_push($time, $watched_sec);
            array_push($view, $v->user_id);
        }

        $vv = (object)(array("time" => array_sum($time), "view" => array_unique($view)));

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
}
