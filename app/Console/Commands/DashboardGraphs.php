<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ViewLog;
use App\Models\Device;
use App\Models\Channel;
use App\Models\DashboardTempData;
use App\Models\DataCleanse;
use App\Models\Universe;
use App\Models\User;
use stdClass;
use Illuminate\Support\Facades\DB;

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

        $lastCleanedUpdatedDate = DataCleanse::where('status',1)->latest('id')->first();
        
        if ($lastCleanedUpdatedDate >= $yesterday) {


        // $y_data = DashboardTempData::where('date', $yesterday)->first();
        // if (!$y_data) {

            // $yesterday_ForData = date("Y-m-d");
            // $finishDateTime = $yesterday_ForData . " 00:00:00";
            // //$finishDateTime = date("Y-m-d H:i:s");
            // $min = 1440;
            // $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
            // $startDateTime = date('Y-m-d H:i:s', $newtimestamp); 

            $ram_logs = ViewLog::where('finished_watching_at', '>', $startDateTime)
            ->where('started_watching_at', '<', $finishDateTime)
            ->get();

            $total_user = User::count();
            $universe_size = Universe::sum(DB::raw('universe / 1000'));

            $reach = $this->reachpercentdashboard($startDateTime, $finishDateTime, $ram_logs, $universe_size);
            $reachZero = $this->reachuserdashboard($startDateTime, $finishDateTime, $ram_logs);
            $tvr = $this->tvrgraphdashboard($startDateTime, $finishDateTime, $ram_logs, $universe_size);
            $tvrZero = $this->tvrgraphzerodashboard($startDateTime, $finishDateTime, $ram_logs, $universe_size);
            $share =  $this->sharegraphdashboard($startDateTime, $finishDateTime, $ram_logs, $universe_size);
            $timeSpent = $this->timeSpentUniverse($startDateTime, $finishDateTime, $ram_logs);


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
                "universe" => $universe_size,
                "sample" => $total_user,
            ];

            $td = new DashboardTempData();
            $td->data = json_encode($all_graph);
            $td->date = $yesterday;
            $td->save();

        //$all_graph = json_encode($y_data->data);

        }

    }

    public function reachpercentdashboard($startDateTime, $finishDateTime, $ram_logs, $universe_size)
    {
        $channels = Channel::whereNotIn('id', [888, 40])
          ->select('id', 'channel_name')
          ->get();
        // $total_user = User::count();
        // $universe_size = Universe::sum(DB::raw('universe / 1000'));
        $channel_info = [];
    
        // $ram_logs = ViewLog::where('finished_watching_at', '>', $startDateTime)
        //         ->where('started_watching_at', '<', $finishDateTime)
        //         ->get();
    
        foreach ($channels as $c) {
          $user_count = $ram_logs
                    ->where('channel_id', $c->id)
                    ->groupBy('user_id')
                    ->map(function ($groupedLogs) {
                        return $groupedLogs->max(function ($log) {
                            return $log->universe / (1000 * $log->system);
                        });
                    })
                    ->sum();
    
          $user_count = ($user_count / $universe_size) * 100;
          $user_count = round($user_count, 1);
                  
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
    
        $t_data = new stdClass;
        $t_data->reach_channel = $label;
        $t_data->reach_value = $value;
        // $t_data->universe = $universe_size;
        // $t_data->sample = $total_user;
        return $t_data;
    }

    public function reachuserdashboard($startDateTime, $finishDateTime, $ram_logs)
    {
        $channels = Channel::whereNotIn('id', [888, 40])
          ->select('id', 'channel_name')
          ->get();
    
        $channel_info = [];
    
        // $ram_logs = ViewLog::where('finished_watching_at', '>', $startDateTime)
        //         ->where('started_watching_at', '<', $finishDateTime)
        //         ->get();
    
        foreach ($channels as $c) {
          $user_count = $ram_logs
                    ->where('channel_id', $c->id)
                    ->groupBy('user_id')
                    ->map(function ($groupedLogs) {
                        return $groupedLogs->max(function ($log) {
                            return $log->universe / (1000 * $log->system);
                        });
                    })
                    ->sum();
    
          $channel = [
            "channel_name" => $c->channel_name,
            "users" => round($user_count)
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
    
        $t_data = new stdClass;
        $t_data->reachzero_channel = $label;
        $t_data->reachzero_value = $value;
        return $t_data;
    }

    public function tvrgraphdashboard($startDateTime, $finishDateTime, $ram_logs, $universe_size)
    {
        $temp = array();
        $viewer = array();
    
        $start_range = strtotime($startDateTime);
        $finish_range = strtotime($finishDateTime);
        $diff = abs($start_range - $finish_range) / 60;;
    
        // $universe_size = Universe::sum(DB::raw('universe / 1000'));
        $channels = Channel::whereNotIn('id', [888, 40])
          ->select('id', 'channel_name')
          ->get();
    
        // $ram_logs = ViewLog::where('finished_watching_at', '>', $startDateTime)
        //   ->where('started_watching_at', '<', $finishDateTime)
        //   ->get();
    
        foreach ($channels as $c) {
          $viewers = $ram_logs->where('channel_id', $c->id)
            ->toArray();
            
          foreach ($viewers as $v) {
            $v = (object)$v;
    
            if ($v->finished_watching_at == null) {
              if ((strtotime($v->started_watching_at)) < ($start_range)) {
                $timeviewd = abs($start_range - strtotime($finishDateTime)) * ($v->universe / (1000 * $v->system));
              } else if ((strtotime($v->started_watching_at)) >= ($start_range)) {
                $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($finishDateTime)) * ($v->universe / (1000 * $v->system));
              }
            } else if (((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))) {
              $timeviewd = abs($start_range - $finish_range) * ($v->universe / (1000 * $v->system));
            } else if (((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) <= ($finish_range))) {
              $timeviewd = abs($start_range - strtotime($v->finished_watching_at)) * ($v->universe / (1000 * $v->system));
            } else if (((strtotime($v->started_watching_at)) >= ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))) {
              $timeviewd = abs(strtotime($v->started_watching_at) - $finish_range) * ($v->universe / (1000 * $v->system));
            } else {
              $timeviewd = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at)) * ($v->universe / (1000 * $v->system));
            }
            //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
            $timeviewd = $timeviewd / 60;
            array_push($viewer, $timeviewd);
          }
    
          $timeSpent_universe = array_sum($viewer) / $universe_size;
          $tvrp = ($timeSpent_universe * 100) / $diff;
    
          unset($viewer);
          $viewer = array();
    
          $tempc = array(
    
            "label" => $c->channel_name,
    
            "value" => $tvrp
    
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
    
        $t_data = new stdClass;
        $t_data->tvr_channel = $label;
        $t_data->tvr_value = $value;
        return $t_data;
    }

    public function tvrgraphzerodashboard($startDateTime, $finishDateTime, $ram_logs, $universe_size)
    {
        $temp = array();
        $viewer = array();
    
        $start_range = strtotime($startDateTime);
        $finish_range = strtotime($finishDateTime);
        $diff = abs($start_range - $finish_range) / 60;
    
        // $universe_size = Universe::sum(DB::raw('universe / 1000'));
        $channels = Channel::whereNotIn('id', [888, 40])
          ->select('id', 'channel_name')
          ->get();
    
        // $ram_logs = ViewLog::where('finished_watching_at', '>', $startDateTime)
        //   ->where('started_watching_at', '<', $finishDateTime)
        //   ->get();
    
        foreach ($channels as $c) {
          $viewers = $ram_logs->where('channel_id', $c->id)
            ->toArray();
    
          foreach ($viewers as $v) {
            $v = (object)$v;
    
            if ($v->finished_watching_at == null) {
              if ((strtotime($v->started_watching_at)) < ($start_range)) {
                $timeviewd = abs($start_range - strtotime($finishDateTime)) * ($v->universe / (1000 * $v->system));
              } else if ((strtotime($v->started_watching_at)) >= ($start_range)) {
                $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($finishDateTime)) * ($v->universe / (1000 * $v->system));
              }
            } else if (((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))) {
              $timeviewd = abs($start_range - $finish_range) * ($v->universe / (1000 * $v->system));
            } else if (((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) <= ($finish_range))) {
              $timeviewd = abs($start_range - strtotime($v->finished_watching_at)) * ($v->universe / (1000 * $v->system));
            } else if (((strtotime($v->started_watching_at)) >= ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))) {
              $timeviewd = abs(strtotime($v->started_watching_at) - $finish_range) * ($v->universe / (1000 * $v->system));
            } else {
              $timeviewd = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at)) * ($v->universe / (1000 * $v->system));
            }
           
            $timeviewd = $timeviewd / 60;
            array_push($viewer, $timeviewd);
          }
    
          $timeSpent_universe = array_sum($viewer) / $universe_size;
          $tvrp = ($timeSpent_universe * 100) / $diff;
          $tvr0 = ($tvrp * $universe_size) / 100;
          
          unset($viewer);
          $viewer = array();
    
          $tempc = array(
    
            "label" => $c->channel_name,
    
            "value" => $tvr0
    
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
      
        $t_data = new stdClass;
        $t_data->tvrzero_channel = $label;
        $t_data->tvrzero_value = $value;
        return $t_data;
    }

    public function sharegraphdashboard($startDateTime, $finishDateTime, $ram_logs, $universe_size)
    {
        $temp = array();
        $viewer = array();
    
        $start_range = strtotime($startDateTime);
        $finish_range = strtotime($finishDateTime);
        $diff = abs($start_range - $finish_range) / 60;
    
        $channelArray = array();
        $shares = array();
        $all_tvr = array();
    
        // $universe_size = Universe::sum(DB::raw('universe / 1000'));
        $channels = Channel::whereNotIn('id', [888, 40])
          ->select('id', 'channel_name')
          ->get();
    
        // $ram_logs = ViewLog::where('finished_watching_at', '>', $startDateTime)
        //   ->where('started_watching_at', '<', $finishDateTime)
        //   ->get();
        
        foreach ($channels as $c) {
          $viewelogs = $ram_logs->where('channel_id', $c->id)
            ->toArray();
            
          foreach ($viewelogs as $v) {
            $v = (object)$v;
    
            if ($v->finished_watching_at == null) {
              if ((strtotime($v->started_watching_at)) < ($start_range)) {
                $timeviewd = abs($start_range - strtotime($finishDateTime)) * ($v->universe / (1000 * $v->system));
              } else if ((strtotime($v->started_watching_at)) >= ($start_range)) {
                $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($finishDateTime)) * ($v->universe / (1000 * $v->system));
              }
            } else if (((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))) {
              $timeviewd = abs($start_range - $finish_range) * ($v->universe / (1000 * $v->system));
            } else if (((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) <= ($finish_range))) {
              $timeviewd = abs($start_range - strtotime($v->finished_watching_at)) * ($v->universe / (1000 * $v->system));
            } else if (((strtotime($v->started_watching_at)) >= ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))) {
              $timeviewd = abs(strtotime($v->started_watching_at) - $finish_range) * ($v->universe / (1000 * $v->system));
            } else {
              $timeviewd = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at)) * ($v->universe / (1000 * $v->system));
            }
           
            $timeviewd = $timeviewd / 60;
            array_push($viewer, $timeviewd);
          }
    
          $timeSpent_universe = array_sum($viewer) / $universe_size;
          $tvrp = ($timeSpent_universe * 100) / $diff;
          //$tvr0 = ($tvrp * $universe_size) / 100;
    
          array_push($all_tvr, $tvrp);
          array_push($channelArray, $c->channel_name);
        }
        $total_tvr = array_sum($all_tvr);
        $total_tvr = round($total_tvr, 5);
    
        $total_share = 0;
        for ($i = 0; $i < count($all_tvr); $i++) {
          if($total_tvr == 0){
            $s = 0;
          }else{
              $s = ($all_tvr[$i] / $total_tvr) * 100;
          }
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

        $t_data = new stdClass;
        $t_data->share_channel = $label;
        $t_data->share_value = $value;
        return $t_data;
    }

    public function timeSpentUniverse($startDateTime, $finishDateTime, $ram_logs)
    {
        $temp = array();
        $viewer = array();
    
        $start_range = strtotime($startDateTime);
        $finish_range = strtotime($finishDateTime);
    
        $channels = Channel::whereNotIn('id', [888, 40])
          ->select('id', 'channel_name')
          ->get();
    
        // $ram_logs = ViewLog::where('finished_watching_at', '>', $startDateTime)
        //   ->where('started_watching_at', '<', $finishDateTime)
        //   ->get();
    
        foreach ($channels as $c) {
          $viewers = $ram_logs->where('channel_id', $c->id)
            ->toArray();
    
          foreach ($viewers as $v) {
            $v = (object)$v;
    
            if ($v->finished_watching_at == null) {
              if ((strtotime($v->started_watching_at)) < ($start_range)) {
                $timeviewd = abs($start_range - strtotime($finishDateTime)) * ($v->universe / (1000 * $v->system));
              } else if ((strtotime($v->started_watching_at)) >= ($start_range)) {
                $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($finishDateTime)) * ($v->universe / (1000 * $v->system));
              }
            } else if (((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))) {
              $timeviewd = abs($start_range - $finish_range) * ($v->universe / (1000 * $v->system));
            } else if (((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) <= ($finish_range))) {
              $timeviewd = abs($start_range - strtotime($v->finished_watching_at)) * ($v->universe / (1000 * $v->system));
            } else if (((strtotime($v->started_watching_at)) >= ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))) {
              $timeviewd = abs(strtotime($v->started_watching_at) - $finish_range) * ($v->universe / (1000 * $v->system));
            } else {
              $timeviewd = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at)) * ($v->universe / (1000 * $v->system));
            }
            
            $timeviewd = $timeviewd / 60;
            array_push($viewer, $timeviewd);
          }
    
          $timeSpent_universe = array_sum($viewer);
    
          unset($viewer);
          $viewer = array();
    
          $tempc = array(
    
            "label" => $c->channel_name,
    
            "value" => $timeSpent_universe
    
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
    
        $t_data = new stdClass;
        $t_data->timeSpent_channel = $label;
        $t_data->timeSpent_value = $value;
        return $t_data;
    }





}
