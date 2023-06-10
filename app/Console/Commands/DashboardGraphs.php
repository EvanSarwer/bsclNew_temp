<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ViewLog;
use App\Models\Device;
use App\Models\Channel;
use App\Models\DashboardTempData;
use App\Models\User;
use stdClass;

class DashboardGraphs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboardGraph:generate';

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
            //$all_graph = [];
        $yesterday = date("Y-m-d", strtotime('-1 days'));
        $startDateTime = $yesterday . " 00:00:00";
        $finishDateTime = $yesterday . " 23:59:59";

        // $y_data = DashboardTempData::where('date', $yesterday)->first();
        // if (!$y_data) {

            // $yesterday_ForData = date("Y-m-d");
            // $finishDateTime = $yesterday_ForData . " 00:00:00";
            // //$finishDateTime = date("Y-m-d H:i:s");
            // $min = 1440;
            // $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
            // $startDateTime = date('Y-m-d H:i:s', $newtimestamp); 


            $reach = $this->reachpercentdashboard($startDateTime, $finishDateTime);
            $reachZero = $this->reachuserdashboard($startDateTime, $finishDateTime);
            $tvr = $this->tvrgraphdashboard($startDateTime, $finishDateTime);
            $tvrZero = $this->tvrgraphzerodashboard($startDateTime, $finishDateTime);
            $share =  $this->sharegraphdashboard($startDateTime, $finishDateTime);
            $timeSpent = $this->timeSpentUniverse($startDateTime, $finishDateTime);


            $all_graph = [
                "reach_channel" => $reach->reach_channel,
                "reach_value" => $reach->reach_value,
                "reachZero_channel" => $reachZero->reachzero_channel,
                "reachZero_value" => $reachZero->reachzero_value,
                "tvr_channel" => $tvr->tvr_channel,
                "tvr_value" => $tvr->tvr_value,
                "tvrZero_channel" => $tvrZero->tvrzero_channel,
                "tvrZero_value" => $tvrZero->tvrzero_value,
                "share_channel" => $share->share_channel,
                "share_value" => $share->share_value,
                "timeSpent_channel" => $timeSpent->timeSpent_channel,
                "timeSpent_value" => $timeSpent->timeSpent_value,
                "start" => $startDateTime,
                "finish" => $finishDateTime,
                "top_reach" => $reach->reach_channel[0],
                "top_tvr" => $tvr->tvr_channel[0],
            ];

            $td = new DashboardTempData();
            $td->data = json_encode($all_graph);
            $td->date = $yesterday;
            $td->save();

        //$all_graph = json_encode($y_data->data);

        //}

    }

    public function reachpercentdashboard($startDateTime, $finishDateTime)
    {
        // $yesterday = date("Y-m-d");
        // $finishDateTime = $yesterday . " 00:00:00";
        // //$finishDateTime = date("Y-m-d H:i:s");
        // $min = 1440;
        // $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
        // $startDateTime = date('Y-m-d H:i:s', $newtimestamp);

        $channels = Channel::whereNotIn('id', [888, 40])
        ->select('id', 'channel_name')
        ->get();
        $total_user = User::count();
        $channel_info = [];

        foreach ($channels as $c) {
        $user_count = 0;
        $logs = ViewLog::where('channel_id', $c->id)
            ->where(function ($query) use ($startDateTime, $finishDateTime) {
            $query->where('finished_watching_at', '>', $startDateTime)
                ->orWhereNull('finished_watching_at');
            })
            ->where('started_watching_at', '<', $finishDateTime)
            ->distinct()->get('user_id');

        $viewlogs = count($logs);

        $user_count = ($viewlogs / $total_user) * 100;
        $channel = [
            "channel_name" => $c->channel_name,
            "users" => $user_count
        ];
        array_push($channel_info, $channel);
        }
        array_multisort(array_column($channel_info, 'users'), SORT_DESC, $channel_info);
        $label = array();
        $value = array();
        for ($i = 0; $i < 10; $i++) {
        array_push($label, $channel_info[$i]['channel_name']);
        array_push($value, $channel_info[$i]['users']);
        }

        //return response()->json(["value" => $value, "label" => $label, "start" => $startDateTime, "finish" => $finishDateTime], 200);
        $t_data = new stdClass;
        $t_data->reach_channel = $label;
        $t_data->reach_value = $value;
        return $t_data;
    }

    public function reachuserdashboard($startDateTime, $finishDateTime)
    {
        // $yesterday = date("Y-m-d");
        // $finishDateTime = $yesterday . " 00:00:00";
        // //$finishDateTime = date("Y-m-d H:i:s");
        // $min = 1440;
        // $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
        // $startDateTime = date('Y-m-d H:i:s', $newtimestamp);

        $channels = Channel::whereNotIn('id', [888, 40])
        ->select('id', 'channel_name')
        ->get();
        $total_user = User::count();
        $channel_info = [];

        foreach ($channels as $c) {
        $user_count = 0;
        $logs = ViewLog::where('channel_id', $c->id)
            ->where(function ($query) use ($startDateTime, $finishDateTime) {
            $query->where('finished_watching_at', '>', $startDateTime)
                ->orWhereNull('finished_watching_at');
            })
            ->where('started_watching_at', '<', $finishDateTime)
            ->distinct()->get('user_id');

        $viewlogs = count($logs);

        //$user_count = ($viewlogs / $total_user) * 100 ;
        $channel = [
            "channel_name" => $c->channel_name,
            "users" => $viewlogs
        ];
        array_push($channel_info, $channel);
        }
        array_multisort(array_column($channel_info, 'users'), SORT_DESC, $channel_info);
        $label = array();
        $value = array();
        for ($i = 0; $i < 10; $i++) {
        array_push($label, $channel_info[$i]['channel_name']);
        array_push($value, $channel_info[$i]['users']);
        }

        //return response()->json(["value" => $value, "label" => $label, "start" => $startDateTime, "finish" => $finishDateTime], 200);
        $t_data = new stdClass;
        $t_data->reachzero_channel = $label;
        $t_data->reachzero_value = $value;
        return $t_data;
    }

    public function tvrgraphdashboard($startDateTime, $finishDateTime)
    {


        $channelArray = array();
        $tvrs = array();
        $temp = array();
        $viewer = array();
        //$ldate = date('Y-m-d H:i:s');
        /*if($req->start=="" && $req->finish==""){
            return response()->json(["value"=>$reachs,"label"=>$channelArray],200);
            }*/
        // $yesterday = date("Y-m-d");
        // $finishDateTime = $yesterday . " 00:00:00";
        // //$finishDateTime = date("Y-m-d H:i:s");
        // $min = 1440;
        // $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
        // $startDateTime = date('Y-m-d H:i:s', $newtimestamp);
        $start_range = strtotime($startDateTime);
        $finish_range = strtotime($finishDateTime);
        $diff = abs($start_range - $finish_range) / 60;

        //return response()->json([$di],200);
        //return response()->json(["tvr"=>$diff],200);
        $channels = Channel::whereNotIn('id', [888, 40])
        ->select('id', 'channel_name')
        ->get();

        //return response()->json([$channels],200);
        $users = User::all();
        $numOfUser = $users->count();
        //$all=array();

        foreach ($channels as $c) {
        $viewers = ViewLog::where('channel_id', $c->id)
            ->where(function ($query) use ($finishDateTime, $startDateTime) {
            $query->where('finished_watching_at', '>', $startDateTime)
                ->orWhereNull('finished_watching_at');
            })
            ->where('started_watching_at', '<', $finishDateTime)
            ->get();
        /*$viewers = ViewLog::where('channel_id', $c->id)
            ->where('started_watching_at','<',date($finishDate)." ".$finishTime)
            ->where('finished_watching_at','>',date($startDate)." ".$startTime)
            ->get();*/
        foreach ($viewers as $v) {
            if ($v->finished_watching_at == null) {
            if ((strtotime($v->started_watching_at)) < ($start_range)) {
                $timeviewd = abs($start_range - strtotime($finishDateTime));
            } else if ((strtotime($v->started_watching_at)) >= ($start_range)) {
                $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($finishDateTime));
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
            //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
            $timeviewd = $timeviewd / 60;
            array_push($viewer, $timeviewd);
        }
        //return response()->json([$viewer],200);
        $tvr = array_sum($viewer) / $numOfUser;
        //$tvr=$tvr/60;
        $tvr = $tvr / $diff;
        $tvr = $tvr * 100;
        unset($viewer);
        $viewer = array();
        array_push($channelArray, $c->channel_name);
        array_push($tvrs, $tvr);
        $tempc = array(

            "label" => $c->channel_name,

            "value" => $tvr

        );
        array_push($temp, $tempc);
        }
        //return response()->json([$temp],200);

        $label = array();
        $value = array();
        array_multisort(array_column($temp, 'value'), SORT_DESC, $temp);
        for ($i = 0; $i < 10; $i++) {

        array_push($label, $temp[$i]['label']);
        array_push($value, $temp[$i]['value']);
        }
        /*rsort($temptvr);
                $rlength=count($tvrs);
                $cc=0;
                for($i=0;$i<$rlength && $cc<10;$i++){
                if($tvrs[$i]>$temptvr[10]){
                    array_push($nchannelArray,$channelArray[$i]);
                array_push($ntvrs,$tvrs[$i]);
                $cc++;
                }
                }*/

        //return response()->json(["value" => $value, "label" => $label, "start" => $startDateTime, "finish" => $finishDateTime], 200);
        $t_data = new stdClass;
        $t_data->tvr_channel = $label;
        $t_data->tvr_value = $value;
        return $t_data;
    }

    public function tvrgraphzerodashboard($startDateTime, $finishDateTime)
    {


        $channelArray = array();
        $tvrs = array();

        $temp = array();
        $viewer = array();

        // $yesterday = date("Y-m-d");
        // $finishDateTime = $yesterday . " 00:00:00";
        // //$finishDateTime = date("Y-m-d H:i:s");
        // $min = 1440;
        // $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
        // $startDateTime = date('Y-m-d H:i:s', $newtimestamp);
        $start_range = strtotime($startDateTime);
        $finish_range = strtotime($finishDateTime);
        $diff = abs($start_range - $finish_range) / 60;
        $channels = Channel::whereNotIn('id', [888, 40])
        ->select('id', 'channel_name')
        ->get();
        $users = User::all();
        $numOfUser = $users->count();

        foreach ($channels as $c) {
        $viewers = ViewLog::where('channel_id', $c->id)
            ->where(function ($query) use ($finishDateTime, $startDateTime) {
            $query->where('finished_watching_at', '>', $startDateTime)
                ->orWhereNull('finished_watching_at');
            })
            ->where('started_watching_at', '<', $finishDateTime)
            ->get();
        /*$viewers = ViewLog::where('channel_id', $c->id)
            ->where('started_watching_at','<',date($finishDate)." ".$finishTime)
            ->where('finished_watching_at','>',date($startDate)." ".$startTime)
            ->get();*/
        foreach ($viewers as $v) {
            if ($v->finished_watching_at == null) {
            if ((strtotime($v->started_watching_at)) < ($start_range)) {
                $timeviewd = abs($start_range - strtotime($finishDateTime));
            } else if ((strtotime($v->started_watching_at)) >= ($start_range)) {
                $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($finishDateTime));
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
            //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
            $timeviewd = $timeviewd / 60;
            array_push($viewer, $timeviewd);
        }
        //return response()->json([$viewer],200);
        $tvr = array_sum($viewer) / $numOfUser;
        //$tvr=$tvr/60;
        $tvr = $tvr / $diff;
        //$tvr=$tvr*100;
        unset($viewer);
        $viewer = array();
        array_push($channelArray, $c->channel_name);
        array_push($tvrs, $tvr);
        $tempc = array(

            "label" => $c->channel_name,

            "value" => $tvr

        );
        array_push($temp, $tempc);
        }
        $label = array();
        $value = array();
        array_multisort(array_column($temp, 'value'), SORT_DESC, $temp);

        for ($i = 0; $i < 10; $i++) {

        array_push($label, $temp[$i]['label']);
        array_push($value, $temp[$i]['value']);
        }

        //return response()->json(["value" => $value, "label" => $label, "start" => $startDateTime, "finish" => $finishDateTime], 200);
        $t_data = new stdClass;
        $t_data->tvrzero_channel = $label;
        $t_data->tvrzero_value = $value;
        return $t_data;
    }

    public function sharegraphdashboard($startDateTime, $finishDateTime)
    {
        // $yesterday = date("Y-m-d");
        // $finishDateTime = $yesterday . " 00:00:00";
        // //$finishDateTime = date("Y-m-d H:i:s");
        // $min = 1440;
        // $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
        // $startDateTime = date('Y-m-d H:i:s', $newtimestamp);

        $temp = array();
        $to_time = strtotime($startDateTime);
        $from_time = strtotime($finishDateTime);
        $diff = abs($to_time - $from_time) / 60;
        $users = User::all();
        $numOfUser = $users->count();

        $channelArray = array();
        $shares = array();
        $all_tvr = array();

        $channels = Channel::whereNotIn('id', [888, 40])
        ->select('id', 'channel_name')
        ->get();
        foreach ($channels as $c) {
        $tvr = 0;
        $viewelogs = ViewLog::where('channel_id', $c->id)
            ->where(function ($query) use ($finishDateTime, $startDateTime) {
            $query->where('finished_watching_at', '>', $startDateTime)
                ->orWhereNull('finished_watching_at');
            })
            ->where('started_watching_at', '<', $finishDateTime)
            ->get();
        $total_time_viewed = 0;
        foreach ($viewelogs as $v) {

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
        $tvr = $total_time_viewed / $diff;
        $tvr = $tvr / $numOfUser;
        $tvr = $tvr * 100;
        $tvr = round($tvr, 4);

        array_push($all_tvr, $tvr);
        array_push($channelArray, $c->channel_name);
        }
        $total_tvr = array_sum($all_tvr);
        $total_tvr = round($total_tvr, 5);

        $total_share = 0;
        for ($i = 0; $i < count($all_tvr); $i++) {
        $s = ($all_tvr[$i] / $total_tvr) * 100;
        $total_share = $total_share + $s;
        array_push($shares, $s);
        }
        for ($i = 0; $i < count($all_tvr); $i++) {
        $tempc = array(

            "label" => $channelArray[$i],

            "value" => $shares[$i]

        );
        array_push($temp, $tempc);
        }
        $label = array();
        $value = array();
        array_multisort(array_column($temp, 'value'), SORT_DESC, $temp);

        for ($i = 0; $i < 10; $i++) {

        array_push($label, $temp[$i]['label']);
        array_push($value, $temp[$i]['value']);
        }
        //return response()->json(["value" => $value, "label" => $label, "start" => $startDateTime, "finish" => $finishDateTime], 200);
        //return response()->json(["share"=>$shares,"channels"=>$channelArray],200);
        $t_data = new stdClass;
        $t_data->share_channel = $label;
        $t_data->share_value = $value;
        return $t_data;
    }

    public function timeSpentUniverse($startDateTime, $finishDateTime)
    {


        $channelArray = array();
        $tvrs = array();

        $temp = array();
        $viewer = array();
        /*if($req->start=="" && $req->finish==""){
        return response()->json(["value"=>$reachs,"label"=>$channelArray],200);
        }*/
        // $yesterday = date("Y-m-d");
        // $finishDateTime = $yesterday . " 00:00:00";
        // //$finishDateTime = date("Y-m-d H:i:s");
        // $min = 1440;
        // $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
        // $startDateTime = date('Y-m-d H:i:s', $newtimestamp);
        $start_range = strtotime($startDateTime);
        $finish_range = strtotime($finishDateTime);
        $diff = abs($start_range - $finish_range) / 60;

        //return response()->json([$di],200);
        //return response()->json(["tvr"=>$diff],200);
        $channels = Channel::whereNotIn('id', [888, 40])
        ->select('id', 'channel_name')
        ->get();
        $users = User::all();
        $numOfUser = $users->count();
        //$all=array();

        foreach ($channels as $c) {
        $viewers = ViewLog::where('channel_id', $c->id)
            ->where(function ($query) use ($finishDateTime, $startDateTime) {
            $query->where('finished_watching_at', '>', $startDateTime)
                ->orWhereNull('finished_watching_at');
            })
            ->where('started_watching_at', '<', $finishDateTime)
            ->get();
        /*$viewers = ViewLog::where('channel_id', $c->id)
        ->where('started_watching_at','<',date($finishDate)." ".$finishTime)
        ->where('finished_watching_at','>',date($startDate)." ".$startTime)
        ->get();*/
        foreach ($viewers as $v) {
            if ($v->finished_watching_at == null) {
            if ((strtotime($v->started_watching_at)) < ($start_range)) {
                $timeviewd = abs($start_range - strtotime($finishDateTime));
            } else if ((strtotime($v->started_watching_at)) >= ($start_range)) {
                $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($finishDateTime));
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
            //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
            $timeviewd = $timeviewd / 60;
            array_push($viewer, $timeviewd);
        }
        //return response()->json([$viewer],200);
        $tvr = array_sum($viewer); ///$numOfUser;
        //$tvr=$tvr/60;
        //$tvr=$tvr/$diff;
        //$tvr=$tvr*100;
        unset($viewer);
        $viewer = array();
        array_push($channelArray, $c->channel_name);
        array_push($tvrs, $tvr);
        $tempc = array(

            "label" => $c->channel_name,

            "value" => $tvr

        );
        array_push($temp, $tempc);
        }
        $label = array();
        $value = array();
        array_multisort(array_column($temp, 'value'), SORT_DESC, $temp);

        for ($i = 0; $i < 10; $i++) {

        array_push($label, $temp[$i]['label']);
        array_push($value, $temp[$i]['value']);
        }
        // $temptvr=$tvrs;
        // $ntvrs=array();
        // $nchannelArray=array();
        // rsort($temptvr);
        // $rlength=count($tvrs);
        // $cc=0;
        // for($i=0;$i<$rlength && $cc<10;$i++){
        //   if($tvrs[$i]>$temptvr[10]){
        //     array_push($nchannelArray,$channelArray[$i]);
        // array_push($ntvrs,$tvrs[$i]);
        // $cc++;
        //   }
        // }


        //return response()->json(["value" => $value, "label" => $label, "start" => $startDateTime, "finish" => $finishDateTime], 200);
        $t_data = new stdClass;
        $t_data->timeSpent_channel = $label;
        $t_data->timeSpent_value = $value;
        return $t_data;
    }







}
