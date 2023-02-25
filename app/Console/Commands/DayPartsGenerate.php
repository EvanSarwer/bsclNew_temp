<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\DayPart;
use App\Models\DayPartProcess;
use App\Models\User;

class DayPartsGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dayparts:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type=['all','stb','ott'];
        $ranges=[30,15];
        $day=date("Y-m-d", strtotime('-1 days'));
        foreach($type as $t){
            foreach($ranges as $r){
                $this->dayrangedtrendsave((object)['type'=>$t,'range'=>$r,'day'=>$day]);
            }
        }
        return 0;
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
            $userids = User::where('type', 'like', '%' . $type . '%')
                ->pluck('id')->toArray();
            //return response()->json(["time" => $channel->channel_name], 200);
            $all = [["Time-Frame", "Reach(000)", "Reach(%)", "TVR(000)", "TVR(%)"]];
            $users = User::all();
            $numOfUser = $users->count();
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
            DayPart::create(["channel_id" => $c->id, "day" => $req->day, "time_range" => $req->range, "type" => (($type != "") ? $type : "all"), "data" => json_encode(((object)(["label" => $label, "reach0" => $reach0, "reachp" => $reachp, "tvr0" => $tvr0, "tvrp" => $tvrp])))]);
            $count++;
        }
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
    public function timeviewed($id, $start, $finish)
    {
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
    
}
