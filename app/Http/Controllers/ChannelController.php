<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
class ChannelController extends Controller
{
  public function trendchannel()
  {
    $channelslist=array();
    //$demo=array("id"=>"name"=>);
    $channels = Channel::all('id', 'channel_name');
    foreach ($channels as $c) {
$demo=array("id"=>$c->id,"name"=>$c->channel_name);
array_push($channelslist,$demo);
    }
    
  return response()->json(["channels"=>$channelslist], 200);
  }
  public function reachpercent()
  {

    //$timeRanges=array("00:00-00:30","00:30-01:00","01:00-01:30","01:30-02:00","02:00-02:30","","04-06","06-08","08-10","10-12","12-14","14-16","16-18","18-20","20-22","22-24");
    $values = array(78.72727272727273, 78.72727272727273, 79.63636363636363, 81.81818181818183, 85.81818181818183, 81.81818181818183, 79.54545454545454, 78.72727272727273, 78.72727272727273, 81.81818181818183, 78.72727272727273, 82.9090909090909, 78.72727272727273, 78.72727272727273, 79.63636363636363, 81.81818181818183, 85.81818181818183, 81.81818181818183, 79.54545454545454, 78.72727272727273, 78.72727272727273, 81.81818181818183, 78.72727272727273, 82.9090909090909, 78.72727272727273, 78.72727272727273, 79.63636363636363, 81.81818181818183, 85.81818181818183, 81.81818181818183, 79.54545454545454, 78.72727272727273, 78.72727272727273, 81.81818181818183, 78.72727272727273, 82.9090909090909, 78.72727272727273, 78.72727272727273, 79.63636363636363, 81.81818181818183, 85.81818181818183, 81.81818181818183, 79.54545454545454, 78.72727272727273, 78.72727272727273, 81.81818181818183, 78.72727272727273, 82.9090909090909);
    //$values=array(10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,85,80,75,70,65,60,55,50,45,40,35,30,30,35,40,45,50,55,60,65,70,75,80);
    $timeRanges = array("00:00-00:30", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "23:30-24:00",);
    return response()->json(["label" => $timeRanges, "values" => $values], 200);
  }
  public function reachpercenttrend(Request $req)
  {
    $time=array();
    $length=12;
if($req->time=="Daily"){
  $inc=-20; //daily
  for($i=0;$i<13;$i++){
  $inc=$inc+2;
  //echo "".$inc;
  array_push($time,((string)$inc)." hours");
  }
}
elseif($req->time=="Weekly"){
  $inc=-174;  //weekly
  $length=14;
    for($i=0;$i<15;$i++){
    $inc=$inc+12;
    //echo "".$inc;
    array_push($time,((string)$inc)." hours");
    }
}
elseif($req->time=="Monthly"){
  $inc=-750;  //monthly
  $length=31;
    for($i=0;$i<32;$i++){
    $inc=$inc+24;
    //echo "".$inc;
    array_push($time,((string)$inc)." hours");
    }
}
elseif($req->time=="Yearly"){
  $inc=-13;  //yearly
  $length=12;
    for($i=0;$i<13;$i++){
    $inc=$inc+1;
    //echo "".$inc;
    array_push($time,((string)$inc)." months");
    }
}

    $channelArray = array();
    $reachs = array();
    $totalReachs = array();
    $viewer = array();
    $channels = Channel::all('id', 'channel_name');
    $users = User::all();
    $numOfUser = $users->count();

    for($i=0;$i<$length;$i++)
    {
      
      $viewers = ViewLog::where('channel_id', $req->id)
        ->where(function ($query) use ($time,$i) {
          $query->where('finished_watching_at', '>', date("Y-m-d H:i:s", strtotime($time[$i])))
            ->orWhereNull('finished_watching_at');
        })
        ->where('started_watching_at', '<', date("Y-m-d H:i:s", strtotime($time[$i+1])))
        ->get();

      foreach ($viewers as $v) {
        array_push($viewer, $v->user->id);
      }
      $viewer = array_values(array_unique($viewer));
      $numofViewer = count($viewer);
      $reach = ($numofViewer / $numOfUser) * 100;
      unset($viewer);
      $viewer = array();
      
      array_push($channelArray,"");
      array_push($reachs, $reach);

    }
    

  return response()->json(["reachsum" => array_sum($reachs), "values" => $reachs, "label" => $channelArray], 200);
  }
  public function reachtrend(Request $req)
  {
    $time=array();
    $length=12;
if($req->time=="Daily"){
  $inc=-20; //daily
  for($i=0;$i<13;$i++){
  $inc=$inc+2;
  //echo "".$inc;
  array_push($time,((string)$inc)." hours");
  }
}
elseif($req->time=="Weekly"){
  $inc=-174;  //weekly
  $length=14;
    for($i=0;$i<15;$i++){
    $inc=$inc+12;
    //echo "".$inc;
    array_push($time,((string)$inc)." hours");
    }
}
elseif($req->time=="Monthly"){
  $inc=-750;  //monthly
  $length=31;
    for($i=0;$i<32;$i++){
    $inc=$inc+24;
    //echo "".$inc;
    array_push($time,((string)$inc)." hours");
    }
}
elseif($req->time=="Yearly"){
  $inc=-13;  //yearly
  $length=12;
    for($i=0;$i<13;$i++){
    $inc=$inc+1;
    //echo "".$inc;
    array_push($time,((string)$inc)." months");
    }
}

    $channelArray = array();
    $reachs = array();
    $totalReachs = array();
    $viewer = array();
    $channels = Channel::all('id', 'channel_name');
    $users = User::all();
    $numOfUser = $users->count();

    for($i=0;$i<$length;$i++)
    {
      
      $viewers = ViewLog::where('channel_id', $req->id)
        ->where(function ($query) use ($time,$i) {
          $query->where('finished_watching_at', '>', date("Y-m-d H:i:s", strtotime($time[$i])))
            ->orWhereNull('finished_watching_at');
        })
        ->where('started_watching_at', '<', date("Y-m-d H:i:s", strtotime($time[$i+1])))
        ->get();

      foreach ($viewers as $v) {
        array_push($viewer, $v->user->id);
      }
      $viewer = array_values(array_unique($viewer));
      $numofViewer = count($viewer);
      $reach = $numofViewer;//($numofViewer / $numOfUser) * 100;
      unset($viewer);
      $viewer = array();
      
      array_push($channelArray,"");
      array_push($reachs, $reach);

    }
    

  return response()->json(["reachsum" => array_sum($reachs), "values" => $reachs, "label" => $channelArray], 200);
  }
  public function tvrtrend()
  {

    $time=array();
    $inc=-26;
    for($i=0;$i<13;$i++){
    $inc=$inc+2;
    //echo "".$inc;
    array_push($time,((string)$inc)." hours");
    }
    $tvrs = array();
    $channelArray = array();
    $reachs = array();
    $totalReachs = array();
    $viewer = array();
    /*if($req->start=="" && $req->finish==""){
    return response()->json(["reach"=>$reachs,"channels"=>$channelArray],200);
    }
    $startDate=date('Y-m-d',strtotime("-1 days"));
    $startTime="00:00:00";
    $finishDate=date('Y-m-d',strtotime("-1 days"));
    $finishTime="23:59:59";*/
    $channels = Channel::all('id', 'channel_name');
    $users = User::all();
    $numOfUser = $users->count();
    //return response()->json(["reachsum" => array_sum($reachllistnew), "reach" => $reachllistnew, "channels" => $channellistnew], 200);
 
    //$all=array();
for($i=0;$i<12;$i++)
    {
      
      $viewers = ViewLog::where('channel_id', 1)
        ->where(function ($query) use ($time,$i) {
          $query->where('finished_watching_at', '>', date("Y-m-d H:i:s", strtotime($time[$i])))
            ->orWhereNull('finished_watching_at');
        })
        ->where('started_watching_at', '<', date("Y-m-d H:i:s", strtotime($time[$i+1])))
        ->get();

        foreach ($viewers as $v) {
          if ($v->finished_watching_at == null) {
            if ((strtotime($v->started_watching_at)) < (strtotime($time[$i]))) {
              $timeviewd = abs(strtotime($time[$i]) - strtotime($ldate));
            } else if ((strtotime($v->started_watching_at)) >= (strtotime($time[$i]))) {
              $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($ldate));
            }
          } else if (((strtotime($v->started_watching_at)) < (strtotime($time[$i]))) && ((strtotime($v->finished_watching_at)) > (strtotime($time[$i+1])))) {
            $timeviewd = abs(strtotime($time[$i]) - strtotime($time[$i+1]));
          } else if (((strtotime($v->started_watching_at)) < (strtotime($time[$i]))) && ((strtotime($v->finished_watching_at)) <= (strtotime($time[$i+1])))) {
            $timeviewd = abs(strtotime($time[$i]) - strtotime($v->finished_watching_at));
          } else if (((strtotime($v->started_watching_at)) >= (strtotime($time[$i]))) && ((strtotime($v->finished_watching_at)) > (strtotime($time[$i+1])))) {
            $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($time[$i+1]));
          } else {
            $timeviewd = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
          }
          //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
          $timeviewd = $timeviewd / 60;
          array_push($viewer, $timeviewd);
        }
        $tvr = array_sum($viewer) / $numOfUser;
      //$tvr=$tvr/60;
      $diff = (strtotime(date("Y-m-d H:i:s", strtotime($time[$i+1])))-strtotime(date("Y-m-d H:i:s", strtotime($time[$i])))) / 60;
      //$tvr = $tvr / $diff;
      $tvr = $tvr * 100;
      unset($viewer);
      $viewer = array();
  //    array_push($channelArray, $c->channel_name);
      array_push($tvrs, $tvr);

      array_push($channelArray,"");
      //array_push($channelArray, date("Y-m-d H:i:s", strtotime($time[$i]))."-".date("Y-m-d H:i:s", strtotime($time[$i+1])));
//      array_push($reachs, $reach);
      //array_push($reachs,$reach);

    }
    

  return response()->json(["reachsum" => array_sum($tvrs), "values" => $tvrs, "label" => $channelArray], 200);
  }
}
