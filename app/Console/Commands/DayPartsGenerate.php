<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\DayPart;
use App\Models\DayPartProcess;
use App\Models\DataCleanse;
use App\Models\Universe;
use App\Models\User;
use Carbon\Carbon;
use DateTime;

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
        $type=['','stb','ott'];
        $ranges=[30,15];
        $endDate = DataCleanse::where('status',1)->latest('id')->first()->date;
        $startDate = DayPartProcess::max('day');
        $dates = $this->getDatesBetween($startDate, $endDate);
        //$day=date("Y-m-d", strtotime('-1 days'));
        
        foreach($dates as $day){
        foreach($type as $t){
            foreach($ranges as $r){
                $this->dayrangedtrendsave((object)['type'=>$t,'range'=>$r,'day'=>$day]);
            }
        }}
        return 0;
    }
    function getDatesBetween($startDate, $endDate) {
        $dateArray = array();
    
        $currentDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);
    
        while ($currentDate < $endDate) {
            
            $currentDate->modify('+1 day');
            $dateArray[] = $currentDate->format('Y-m-d');
        }
    
        return $dateArray;
    }

    public function dayrangedtrendsave($req)
    {
        $type = $req->type;
        if ($req->type == "") {
            $type = "all";
        }
        $channel = Channel::all();
        //$channel=Channel::where('id',1)->get();
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
                $rr=(int)$this->mult_sum($reachs[$i]);

                $reachp[$i] = ($rr * 100) / $numOfUser;
                $reach0[$i] = $rr;
                
                $tvrp[$i] = ($tvrs[$i] / ($numOfUser * $dd)) * 100;
                $tvr0[$i] = $tvrp[$i]*$numOfUser / 100;

                $mid = date("H:i:s", strtotime($time[0][$i]["start"]));
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
            $mult=($v->universe/1000)/$v->system;
            array_push($time, $watched_sec*$mult);
            array_push($view, ["user_id"=>$v->user_id,"mult"=>$mult]);
        }

        $vv = (object)(array("time" => array_sum($time), "view" => $this->array_unique2($view)));

        //return response()->json(["values" => $vv], 200);

        return $vv;
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
    public function array_unique2($inputArray)
    {
        $uniqueArray = [];

foreach ($inputArray as $item) {
    $user_id = $item["user_id"];
    $mult = $item["mult"];
    $uniqueKey = $user_id . '-' . $mult;

    if (!isset($uniqueArray[$uniqueKey])) {
        $uniqueArray[$uniqueKey] = ["user_id" => $user_id, "mult" => $mult];
    }
}

// Convert the unique array back to indexed array
$uniqueArray = array_values($uniqueArray);

return ($uniqueArray);
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
