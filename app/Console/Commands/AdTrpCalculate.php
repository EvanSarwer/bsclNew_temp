<?php

namespace App\Console\Commands;

use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use App\Models\PlayoutFile;
use App\Models\PlayoutLog;
use App\Models\AdTrp;
use App\Models\UserDataFilter;
use Illuminate\Console\Command;

class AdTrpCalculate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adtrp:calculate';

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
        //echo "adtrp calculating";
        $this->adtrpall();
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
        ->where('done', 0)
            ->get();
        //return response()->json(["value" => $logs], 200);
        foreach ($logs as $log) {
                //return response()->json(["value" => $log->channel->channel_name], 200);
                $start = $log->start;
                $finish = $log->finish;
                $fromTime = strtotime($log->start);
                $toTime = strtotime($log->finish);
                $diff = abs($fromTime - $toTime) / 60;
                if($diff==0){
                    continue;
                }


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
                $freq=$this->frequency((object)array("channel_id"=>$log->channel_id,"start"=>$start,"finish"=>$finish));
                $adtrparr = [
                    "commercial_name" => $log->commercial_name,  "channel_id" => $log->channel_id, "channel_name" => $log->channel->channel_name, "date" => $log->date,
                    "start" => $log->start, "finish" => $log->finish, "timewatched" => $timewatched, "duration" => $log->duration, "tvrp" => $tvrp, "tvr0" => $tvr0, "reach0" => $reach0, "reachp" => $reachp,
                    "c1"=>$freq->c1,"c2"=>$freq->c2,"c3"=>$freq->c3,"c4"=>$freq->c4,
                    "c5"=>$freq->c5,"c6"=>$freq->c6,"c7"=>$freq->c7,"c8"=>$freq->c8,"c9"=>$freq->c9,"c10"=>$freq->c10, "playlog_id" => $log->id
                ];
                //return response()->json(["value" => $adtrparr,"log"=>$log], 200);
                if (AdTrp::create($adtrparr)) {
                    $log->done = 1;
                    $log->save();
                }
                $num++;

                //array_push($values, $tvr);  
                //return response()->json(["value" => $log->channel_id], 200);
            
        }
        //return response()->json(["num" => $num, "value" => "done"], 200);
    }
    public function frequency($req)
    {
        //$start = date('Y-m-d H:i:s', strtotime($req->start));
        $start = $req->start;
        //$finish=date('Y-m-d H:i:s', strtotime($req->finish));
        $c1 = 0;
        $c2 = 0;
        $c3 = 0;
        $c4 = 0;
        $c5 = 0;
        $c6 = 0;
        $c7 = 0;
        $c8 = 0;
        $c9 = 0;
        $c10 = 0;
        $arr = array();
        $viewlogs = ViewLog::where('channel_id', (int)$req->channel_id)
            ->where(function ($query) use ($start) {
                $query->where('finished_watching_at', '>', $start)
                    ->orWhereNull('finished_watching_at');
            })
            ->where('started_watching_at', '<', $req->finish)
            ->pluck('user_id')->toArray();
            $arr=$viewlogs;
        //return response()->json(["value" => $arr], 200);
        //$array = array(1,2,'v', 'v1', 'v2', 'v2', 'v3', 'v3', 'v3', 'val4', 'val4', 'val4', 'val4', 'val5', 'val5', 'val5', 'val5', 'val5');

        //return response()->json(["value" => $viewlogs,"value1" => $array], 200);
        $array = $arr;
        //$array=$viewlogs;
        $cnt = array_count_values($array);
        //return response()->json(["value" => $cnt], 200);
        foreach ($cnt as $c) {
            switch ($c) {
                case 10:
                    $c10++;
                    $c9++;
                    $c8++;
                    $c7++;
                    $c6++;
                    $c5++;
                    $c4++;
                    $c3++;
                    $c2++;
                    $c1++;
                    break;
                case 9:
                    $c9++;
                    $c8++;
                    $c7++;
                    $c6++;
                    $c5++;
                    $c4++;
                    $c3++;
                    $c2++;
                    $c1++;
                    break;
                case 8:
                    $c8++;
                    $c7++;
                    $c6++;
                    $c5++;
                    $c4++;
                    $c3++;
                    $c2++;
                    $c1++;
                    break;
                case 7:
                    $c7++;
                    $c6++;
                    $c5++;
                    $c4++;
                    $c3++;
                    $c2++;
                    $c1++;
                    break;
                case 6:
                    $c6++;
                    $c5++;
                    $c4++;
                    $c3++;
                    $c2++;
                    $c1++;
                    break;
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
        $count = (object)array("c1" => $c1, "c2" => $c2, "c3" => $c3, "c4" => $c4, "c5" => $c5,"c6" => $c6, "c7" => $c7, "c8" => $c8, "c9" => $c9, "c10" => $c10);

        return $count;
    }


    function generate_userFilterData()
    {
        $data = UserDataFilter::where('generate_flag', 0)->get();
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
                ->select('users.id','users.user_name','users.device_id')
                ->distinct('view_logs.user_id')->get();
            
            //$logs->gid = $d->id;
            if(count($logs)>0){
                $d->generate_flag = 1;
                $d->generated_data = json_encode($logs);
                $d->save();
            }else{
                $d->generate_flag = 1;
                //$d->generated_data = json_encode($logs);
                $d->save();
            }
            
            //array_push($all_data, $logs);

        }
        // return response()->json(["Data" => "data"], 200);
    }


}
