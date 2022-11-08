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
            
        }
        //return response()->json(["num" => $num, "value" => "done"], 200);
    }
}
