<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use Illuminate\Http\Request;
use App\Models\ViewLog;
use App\Models\Device;
use App\Models\Channel;
use App\Models\DashboardTempData;
use App\Models\Notification;
use App\Models\RawRequest;
use App\Models\Token;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use DB;
use Illuminate\Support\Facades\Http;
use PhpParser\Node\Stmt\Continue_;
use stdClass;

class DashboardController extends Controller
{
  // public function __construct()
  // {
  //       $this->middleware('auth.admin','auth.user');
  // }

  public function CurrentStatusUser()
  {
    //$total_user = User::all()->count();
    $stb_all = Device::all()->count();
    $stb_total = Device::whereNotNull('contact_email')->whereNotNull('household_condition')->whereNotNull('monthly_income')
      ->get()->count();
    $total_user = User::whereNotNull('devices.contact_email')->whereNotNull('devices.household_condition')->whereNotNull('devices.monthly_income')
      ->join('devices', 'devices.id', '=', 'users.device_id')
      ->get()->count();




    //$stb_total = User::where('type', 'STB')->get()->count();
    $ott_total = User::where('type', 'OTT')->get()->count();



    //$active = ViewLog::select('user_id')->whereNull('finished_watching_at')->distinct('user_id')->get();
    $compare_date = date('Y-m-d H:i:s', (time() - 45));
    $active_users = User::where('last_request', '>', $compare_date)->get();
    $stb_active_user = $active_users->where('type', 'STB')->where('tvoff', 1)->count();
    $ott_active_user = $active_users->where('type', 'OTT')->count();
    // $stb_active = Device::whereIn('users.id', $active)
    //   ->join('users', 'devices.id', '=', 'users.device_id')
    //   ->distinct('devices.id')->count();
    $stb_active = Device::where('last_request', '>', $compare_date)->where('tvoff', 1)->count();

    $active_percent = ($stb_active * 100) / $stb_total;
    $active_percent = round($active_percent, 2);

    // $stb_active = User::whereHas('viewLogs', function ($query) {
    //   $query->where('finished_watching_at', null);
    // })->get()->count();


    // $stb_user = User::where('type','STB')->select("id","user_name","last_request")->get();
    // $stb_active = 0;
    // $cw=array();
    // $ldate = date('Y-m-d H:i:s');
    // if ($stb_user) {
    //     foreach ($stb_user as $u) {
    //         if(abs(strtotime($u->last_request) - strtotime($ldate))<180  && $u->last_request!=null){

    //             array_push($cw,$u);
    //         }
    //     }
    //     $stb_active = count($cw);
    // }

    //$ott_active = $stb_active_user - $stb_active;

    //return response()->json(["total_user" => $total_user, "stb_total" => $stb_total, "ott_total" => $ott_total, "stb_active" => $stb_active, "ott_active" => $ott_active, "stb_active_user" => $stb_active_user, "active_percent" => $active_percent], 200);
    $t_data = new stdClass;
    $t_data->stb_all = $stb_all;
    $t_data->total_user = $total_user;
    $t_data->stb_total = $stb_total;
    $t_data->ott_total = $ott_total;
    $t_data->stb_active = $stb_active;
    $t_data->active_percent = $active_percent;
    $t_data->ott_active_user = $ott_active_user;
    $t_data->stb_active_user = $stb_active_user;

    return $t_data;
  }

  public function CurrentStatusTopReach()
  {
    $finishDateTime = date("Y-m-d H:i:s");
    $min = 1440;
    $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
    $startDateTime = date('Y-m-d H:i:s', $newtimestamp);

    $channels = Channel::whereNotIn('id', [888, 39])
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

    //return response()->json(["top_reach" => $channel_info[0]['channel_name']], 200);
    return $channel_info[0]['channel_name'];
  }

  public function CurrentStatusTopTvr()
  {
    $channelArray = array();
    $tvrs = array();
    $viewer = array();
    $ldate = date('Y-m-d H:i:s');
    /*if($req->start=="" && $req->finish==""){
    return response()->json(["reach"=>$reachs,"channels"=>$channelArray],200);
    }*/
    $finishDateTime = date("Y-m-d H:i:s");
    $min = 1439;
    $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
    $startDateTime = date('Y-m-d H:i:s', $newtimestamp);
    $start_range = strtotime($startDateTime);
    $finish_range = strtotime($finishDateTime);
    $diff = abs($start_range - $finish_range) / 60;

    //return response()->json([$di],200);
    //return response()->json(["tvr"=>$diff],200);
    $channels = Channel::whereNotIn('id', [888, 39])
      ->select('id', 'channel_name')
      ->get();
    //return response()->json(["top_tvr" => $channels], 200);
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
      $chnl = [
        "channel_name" => $c->channel_name,
        "tvr" => $tvr
      ];

      array_push($channelArray, $chnl);
    }
    array_multisort(array_column($channelArray, 'tvr'), SORT_DESC, $channelArray);

    //return response()->json(["tvr"=>$tvr],200);
    //return response()->json(["top_tvr" => $channelArray[0]['channel_name']], 200);

    return $channelArray[0]['channel_name'];
  }

  public function CurrentStatusTopTvrReach()
  {

    $topReach = $this->CurrentStatusTopReach();
    $topTVR = $this->CurrentStatusTopTvr();
    //return response()->json(["top_reach" => $channel_info[0]['channel_name']], 200);
    //return response()->json(["top_tvr" => $channelArray[0]['channel_name']], 200);
    return response()->json(["top_reach" => $topReach, "top_tvr" => $topTVR], 200);
  }


  //   public function reachpercentdashboard(){

  //     $startDate=date('Y-m-d',strtotime("-7 days"));
  //     $startTime="00:00:00";
  //     $finishDate=date('Y-m-d',strtotime("-1 days"));
  //     $finishTime="23:59:59";
  //     $channels = Channel::all()->filter(function ($c) use ($finishDate, $finishTime,$startDate,$startTime)
  //     { return $c->reach( $startDate, $startTime, $finishDate,$finishTime) >0 && $c->id != 39;})
  //     ->sortByDesc('channel_reach')
  //     ->take(10)
  //     ->sortBy('id');

  //     $value = [];
  //     $label = [];
  //     foreach($channels as $c){
  //         $value[] = ($c->channel_reach)*100/Channel::count();
  //         $label[] = $c->channel_name;
  //     }
  //     return response()->json(["value"=>$value,"label"=>$label,"start"=>($startDate." ".$startTime),"finish"=>($finishDate." ".$finishTime)],200);
  //   }



  // public function reachuserdashboard(){


  //     $startDate=date('Y-m-d',strtotime("-7 days"));
  //     $startTime="00:00:00";
  //     $finishDate=date('Y-m-d',strtotime("-1 days"));
  //     $finishTime="23:59:59";
  //     $channels = Channel::all()->filter(function ($c) use ($finishDate, $finishTime,$startDate,$startTime)
  //     { return $c->reach( $startDate, $startTime, $finishDate,$finishTime) >0 && $c->id != 39;})
  //     ->sortByDesc('channel_reach')
  //     ->take(10);

  //     $value = [];
  //     $label = [];
  //     foreach($channels as $c){
  //         $value[] = $c->channel_reach;
  //         $label[] = $c->channel_name;
  //     }
  //     return response()->json(["value"=>$value,"label"=>$label,"start"=>($startDate." ".$startTime),"finish"=>($finishDate." ".$finishTime)],200);
  //   }

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




  public function allgraphdashboard()
  {
    //$all_graph = [];
    $yesterday = date("Y-m-d", strtotime('-1 days'));
    $startDateTime = $yesterday . " 00:00:00";
    $finishDateTime = $yesterday . " 23:59:59";

    $y_data = DashboardTempData::where('date', $yesterday)->first();
    if ($y_data) {

      $all_graph = json_decode($y_data->data);
    } else {

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
        "finish" => $finishDateTime
      ];

      $td = new DashboardTempData();
      $td->data = json_encode($all_graph);
      $td->date = $yesterday;
      $td->save();

      //$all_graph = json_encode($y_data->data);

    }

    return $all_graph;
  }







  public function activechannellistget()
  {
    // $request = array("channel_name"=>31,"device_id"=>1,"time_stamp"=>"2022-04-07 14:51:05");
    // $request = (object)$request;

    // $rsp = Http::get("http://123.200.5.219:8000/api/receive?channel_name=$request->channel_name&device_id=$request->device_id&time_stamp=$request->time_stamp");


    $channels = Channel::withCount(['viewLogs' => function ($query) {
      $query->where('finished_watching_at', null);
    }])->orderBy('view_logs_count', 'DESC')->get(['id', 'channel_name']);
    $activeChannels = [];
    foreach ($channels as $c) {
      if ($c->view_logs_count > 0) {
        $activeChannel = [
          "channel_id" => $c->id,
          "channel_name" => $c->channel_name,
          "channel_logo" => $c->logo,
          "user_count" => $c->view_logs_count
        ];
        array_push($activeChannels, $activeChannel);
      }
    }
    return response()->json(["activeChannels" => $activeChannels], 200);
  }

  public function activeuserlistget()
  {
    $activeChannels = [];
    $actives = ViewLog::where('finished_watching_at', null)
      ->with(['channel', 'user'])
      ->orderBy('started_watching_at', 'ASC')->get();
    foreach ($actives as $a) {
      $a->duration = Carbon::parse($a->started_watching_at)->diffForHumans();
      $a->totaltime = Carbon::parse($a->started_watching_at)->diffForHumans();
      $a->device_name = $a->user->device->device_name;
      $a->device_id = $a->user->device->id;
      array_push($activeChannels, $a->channel_id);
    }
    $countChannels = array_count_values($activeChannels);
    $allChnlList = [];
    foreach ($countChannels as $key => $val) {
      $chnl = Channel::where('id', $key)->first();
      $chnl->user_count = $val;


      array_push($allChnlList, $chnl);
    }

    $currentStatusUser = $this->CurrentStatusUser();


    //return response()->json(["activeUsers" => $actives, "activeChannels" => $allChnlList, "total_user" => $currentStatusUser->total_user, "stb_total" => $currentStatusUser->stb_total, "ott_total" => $currentStatusUser->ott_total, "stb_active" => $currentStatusUser->stb_active, "ott_active" => $currentStatusUser->ott_active, "active_user" => $currentStatusUser->active_user, "active_percent" => $currentStatusUser->active_percent ], 200);
    return response()->json(["activeUsers" => $actives, "activeChannels" => $allChnlList, "stb_all" => $currentStatusUser->stb_all, "total_user" => $currentStatusUser->total_user, "stb_total" => $currentStatusUser->stb_total, "ott_total" => $currentStatusUser->ott_total, "stb_active" => $currentStatusUser->stb_active, "active_user" => $currentStatusUser->active_user, "active_percent" => $currentStatusUser->active_percent], 200);

    //return response()->json($actives, 200);
  }

  public function dashboardstatus()
  {
    $activeChannels = [];
    $actives = [];
    $compare_date = date('Y-m-d H:i:s', (time() - 45)); //where('users.last_request', '>', $compare_date)

    $active_users = User::join('devices', 'devices.id', '=', 'users.device_id')->where('users.last_request', '>', $compare_date)->where('users.tvoff', 1)
      ->select('users.id', 'users.user_name', 'devices.device_name', 'devices.id as device_id')
      ->get();

    foreach ($active_users as $key => $a) {
      $view_log = ViewLog::where('user_id', $a->id)->latest('id')->first();
      if ($view_log) {
        $a->duration = Carbon::parse($view_log->started_watching_at)->diffForHumans(Carbon::parse($view_log->finished_watching_at));
        $a->totaltime = Carbon::parse($view_log->started_watching_at)->diffForHumans(Carbon::parse($view_log->finished_watching_at));
        $a->channel = $view_log->channel;
        $a->started_watching_at = $view_log->started_watching_at;
        array_push($activeChannels, $view_log->channel_id);
        array_push($actives, $a);
      }
    }

    $countChannels = array_count_values($activeChannels);
    $allChnlList = [];
    foreach ($countChannels as $key => $val) {
      $chnl = Channel::where('id', $key)->first();
      $chnl->user_count = $val;


      array_push($allChnlList, $chnl);
    }

    $currentStatusUser = $this->CurrentStatusUser();

    //return response()->json(["activeUsers" => $actives, "activeChannels" => $allChnlList, "total_user" => $currentStatusUser->total_user, "stb_total" => $currentStatusUser->stb_total, "ott_total" => $currentStatusUser->ott_total, "stb_active" => $currentStatusUser->stb_active, "ott_active" => $currentStatusUser->ott_active, "active_user" => $currentStatusUser->active_user, "active_percent" => $currentStatusUser->active_percent ], 200);
    return response()->json(["activeUsers" => $actives, "activeChannels" => $allChnlList, "stb_all" => $currentStatusUser->stb_all, "total_user" => $currentStatusUser->total_user, "stb_total" => $currentStatusUser->stb_total, "ott_total" => $currentStatusUser->ott_total, "stb_active" => $currentStatusUser->stb_active, "stb_active_user" => $currentStatusUser->stb_active_user, "ott_active_user" => $currentStatusUser->ott_active_user, "active_percent" => $currentStatusUser->active_percent], 200);

    //return response()->json($actives, 200);
  }


  public function notification()
  {
    $notifications = array();
    $datebefore = date('Y-m-d H:i:s', strtotime("-5 days"));
    //return response()->json(["data" => $datebefore], 200);

    $devices = Device::where('type', "STB")
      ->where('last_request', '<', $datebefore)->orWhereNull('last_request')->select("id", "device_name", "last_request", "created_at")->get();
    if ($devices) {
      foreach ($devices as $d) {

        if ($d->last_request == null) {
          $d->duration = Carbon::parse($d->created_at)->diffForHumans();
          $d->flag = 1;                                                     // Device has not made any requests yet
          array_push($notifications, $d);
        } else {
          $d->duration = Carbon::parse($d->last_request)->diffForHumans();
          $d->flag = 2;                                                     // Device offline for more than 3 days
          array_push($notifications, $d);
        }
      }
    }


    $activeDevices = Device::where('type', "STB")
      ->whereBetween('last_request', [date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime("+27 minutes"))])->select("id", "device_name", "last_request")->get();

    foreach ($activeDevices as $ad) {
      $temp = RawRequest::where('device_id', $ad->id)->select("temp")->latest('id')->first();
      if ($temp) {
        if ($temp->temp && (substr($temp->temp, 0, -2)) > 80) {
          $ad->duration = Carbon::parse($ad->last_request)->diffForHumans();
          $ad->temp = $temp->temp;
          $ad->flag = 3;                                                         //Active Device Temperature is above 70'C
          array_push($notifications, $ad);
        }
      }
    }

    return response()->json(["notifyNumber" => count($notifications), "data" => $notifications], 200);
  }

  public function generate_notification3()
  {
    $day1Before = date('Y-m-d H:i:s', strtotime("-1 days"));
    $day2Before = date('Y-m-d H:i:s', strtotime("-2 days"));
    $today = date('Y-m-d H:i:s');

    $appUser = AppUser::select('app_users.id')->where('login.role', 'admin')
      ->join('login', 'login.user_name', '=', 'app_users.user_name')
      ->get();



    $devices = Device::where('type', "STB")
      ->get();
    if ($devices) {
      foreach ($devices as $d) {
        //if()
        $channelmax = //(int)(
          RawRequest::select('channel_id')
          ->where('device_id', $d->id)
          ->where('start', '>', $day1Before)
          ->groupBy('channel_id')
          ->orderByRaw('COUNT(*) DESC')
          ->limit(1)
          ->first();
        if ($channelmax == null) {
          continue;
        }

        $channelmax = (int)($channelmax->channel_id);
        //return  response()->json(["total" => $channelmax], 200);
        $rawCount = RawRequest::where('start', '>', $day1Before)
          ->where('device_id', $d->id)
          ->count();
        $rawchCount = RawRequest::where('start', '>', $day1Before)
          ->where('channel_id', $channelmax)
          ->where('device_id', $d->id)
          ->count();

        //return response()->json(["total" => $channelmax,"c"=>$rawCount,"ch"=>$rawchCount,"ok"=>"not","1d"=>$day1Before], 200);
        if ($rawCount > 0) {
          if ($rawchCount / $rawCount >= 0.98) {
            //return response()->json(["total" => $rawCount,"error" => $rawchCount,"ok"=>"not"], 200);
            $check_noti = Notification::where('flag', 6)->where('du_id', $d->id)->where('created_at', '>', $day2Before)->orWhere('created_at', $day2Before)->first();    //
            //return response()->json(["data" => $check_noti], 200);
            if (!$check_noti) {
              Notification::where('flag', 6)->where('du_id', $d->id)->where('created_at', '<', $day2Before)->delete();
              foreach ($appUser as $au) {
                $noti = new Notification();
                $noti->user_id = $au->id;
                $noti->flag = 6;                 // Device has not made any requests yet
                $noti->status = 'unseen';
                $noti->du_id = $d->id;
                $noti->du_name = $d->device_name;
                $noti->details = " same channel 24 hours";
                $noti->created_at = new Datetime();
                $noti->save();
              }
            }
          }
        }
        //return response()->json(["total" => $rawCount,"error" => $rawchCount], 200);

      }
    }
  }
  public function generate_notification2()
  {
    $day5Before = date('Y-m-d H:i:s', strtotime("-5 days"));
    $day10Before = date('Y-m-d H:i:s', strtotime("-10 days"));
    $today = date('Y-m-d H:i:s');

    $appUser = AppUser::select('app_users.id')->where('login.role', 'admin')
      ->join('login', 'login.user_name', '=', 'app_users.user_name')
      ->get();



    $devices = Device::where('type', "STB")
      ->get();
    if ($devices) {
      foreach ($devices as $d) {

        $users = User::where('device_id', $d->id)->pluck('id')->toArray();
        $viewererror = (int)(ViewLog::selectRaw("sum(TIMESTAMPDIFF(SECOND,started_watching_at,finished_watching_at)) as 'sec'")
          ->whereIn('user_id', $users)->whereIn('channel_id', [39, 888])->where('started_watching_at', '>', $day5Before)->first()->sec);
        $viewertotal = (int)(ViewLog::selectRaw("sum(TIMESTAMPDIFF(SECOND,started_watching_at,finished_watching_at)) as 'sec'")
          ->whereIn('user_id', $users)->where('started_watching_at', '>', $day5Before)->first()->sec);
        //return response()->json(["total" => $viewertotal,"error" => $viewererror/$viewertotal,"ok"=>"not"], 200);
        if ($viewertotal > 0) {
          if ($viewererror / $viewertotal > 0.98) {
            //return response()->json(["total" => $viewertotal,"error" => $viewererror,"ok"=>"not"], 200);
            $check_noti = Notification::where('flag', 5)->where('du_id', $d->id)->where('created_at', '>', $day10Before)->orWhere('created_at', $day10Before)->first();    //
            //return response()->json(["data" => $check_noti], 200);
            if (!$check_noti) {
              Notification::where('flag', 5)->where('du_id', $d->id)->where('created_at', '<', $day10Before)->delete();
              foreach ($appUser as $au) {
                $noti = new Notification();
                $noti->user_id = $au->id;
                $noti->flag = 5;                 // Device has not made any requests yet
                $noti->status = 'unseen';
                $noti->du_id = $d->id;
                $noti->du_name = $d->device_name;
                $noti->details = " to many unknown or foreign in the last 5 days";
                $noti->created_at = new Datetime();
                $noti->save();
              }
            }
          }
        }
        //return response()->json(["total" => $viewertotal,"error" => $viewererror], 200);

      }
    }
  }
  public function generate_notification()
  {
    $day5Before = date('Y-m-d H:i:s', strtotime("-5 days"));
    $day10Before = date('Y-m-d H:i:s', strtotime("-10 days"));
    $today = date('Y-m-d H:i:s');

    $appUser = AppUser::select('app_users.id')->where('login.role', 'admin')
      ->join('login', 'login.user_name', '=', 'app_users.user_name')
      ->get();



    $devices = Device::where('type', "STB")
      ->where('last_request', '<', $day5Before)->orWhereNull('last_request')->select("id", "device_name", "last_request", "created_at")->get();
    if ($devices) {
      foreach ($devices as $d) {
        if ($d->last_request == null) {
          $check_noti = Notification::where('flag', 1)->where('du_id', $d->id)->where('created_at', '>', $day10Before)->orWhere('created_at', $day10Before)->first();    //
          //return response()->json(["data" => $check_noti], 200);
          if (!$check_noti) {
            Notification::where('flag', 1)->where('du_id', $d->id)->where('created_at', '<', $day10Before)->delete();
            foreach ($appUser as $au) {
              $noti = new Notification();
              $noti->user_id = $au->id;
              $noti->flag = 1;                 // Device has not made any requests yet
              $noti->status = 'unseen';
              $noti->du_id = $d->id;
              $noti->du_name = $d->device_name;
              $noti->details = " has not made any requests yet.";
              $noti->created_at = new Datetime();
              $noti->save();
            }
          }
        } else {

          $check_noti = Notification::where('flag', 2)->where('du_id', $d->id)->where('created_at', '>', $day10Before)->orWhere('created_at', $day10Before)->first();    //
          //return response()->json(["data" => $check_noti], 200);
          if (!$check_noti) {
            Notification::where('flag', 2)->where('du_id', $d->id)->where('created_at', '<', $day10Before)->delete();
            foreach ($appUser as $au) {
              $noti = new Notification();
              $noti->user_id = $au->id;
              $noti->flag = 2;                  // Device offline for more than 5 days
              $noti->status = 'unseen';
              $noti->du_id = $d->id;
              $noti->du_name = $d->device_name;
              $noti->details = " has been offline for more than 5 days.";
              $noti->created_at = new Datetime();
              $noti->save();
            }
          }
        }
      }
    }


    $allDevices = Device::where('type', "STB")->select("id", "device_name", "last_request")->get();

    foreach ($allDevices as $ad) {
      $devicePeople = RawRequest::where('device_id', $ad->id)->whereBetween('server_time', [$ad->last_request, date($ad->last_request, strtotime("-6 days"))])->select("people")->latest('id')->get();
      //return response()->json(["data" => $devicePeople], 200);
      if (count($devicePeople) <= 0) {
        continue;
      }
      $people_value = $devicePeople[0]->people;
      $peopleChangeCount = 0;
      foreach ($devicePeople as $dp) {

        if ($dp->people == $people_value) {
          continue;
        } else {
          $peopleChangeCount = $peopleChangeCount + 1;
          break;
        }
      }

      if ($peopleChangeCount == 0) {
        $check_noti = Notification::where('flag', 4)->where('du_id', $ad->id)->where('created_at', '>', $day10Before)->orWhere('created_at', $day10Before)->first();
        //return response()->json(["data" => $check_noti], 200);
        if (!$check_noti) {
          Notification::where('flag', 4)->where('du_id', $ad->id)->where('created_at', '<', $day10Before)->delete();
          foreach ($appUser as $au) {
            $noti = new Notification();
            $noti->user_id = $au->id;
            $noti->flag = 4;                  // Device button number in People meter not changed for last 5 days.
            $noti->status = 'unseen';
            $noti->du_id = $ad->id;
            $noti->du_name = $ad->device_name;
            $noti->details = " button number in People meter not changed for last 5 days.";
            $noti->created_at = new Datetime();
            $noti->save();
          }
        }
      }
    }
  }
  public function get_notification(Request $req)
  {

    $token = $req->header('Authorization');
    $userToken = Token::where('token', $token)->first();
    $user_id = $userToken->login->appUser->id;


    $unseen_noti = Notification::where('user_id', $user_id)->where('status', "unseen")->orderBy('created_at', 'desc')->get();
    $seen_noti = Notification::where('user_id', $user_id)->where('status', "seen")->orderBy('created_at', 'desc')->get();
    $merged_noti = $unseen_noti->merge($seen_noti);

    if (count($merged_noti) > 0) {
      foreach ($merged_noti as $mn) {
        $mn->created_at = Carbon::parse($mn->created_at)->diffForHumans();
        // if($mn->message != null){
        //     $mm->message = json_decode($mm->message);
        // }
      }
    }

    return response()->json(["notifyNumber" => count($unseen_noti), "data" => $merged_noti], 200);
  }

  public function seen_notification(Request $req)
  {
    $token = $req->header('Authorization');
    $userToken = Token::where('token', $token)->first();
    $user_id = $userToken->login->appUser->id;

    $unseen_noti = Notification::where('user_id', $user_id)->where('status', "unseen")->update(['status' => 'seen']);
    // foreach ($unseen_noti as $un) {
    //   $un->status = "seen";
    //   $un->update();
    // }
  }
}
