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

  public function reachpercent()
  {

    //$timeRanges=array("00:00-00:30","00:30-01:00","01:00-01:30","01:30-02:00","02:00-02:30","","04-06","06-08","08-10","10-12","12-14","14-16","16-18","18-20","20-22","22-24");
    $values = array(78.72727272727273, 78.72727272727273, 79.63636363636363, 81.81818181818183, 85.81818181818183, 81.81818181818183, 79.54545454545454, 78.72727272727273, 78.72727272727273, 81.81818181818183, 78.72727272727273, 82.9090909090909, 78.72727272727273, 78.72727272727273, 79.63636363636363, 81.81818181818183, 85.81818181818183, 81.81818181818183, 79.54545454545454, 78.72727272727273, 78.72727272727273, 81.81818181818183, 78.72727272727273, 82.9090909090909, 78.72727272727273, 78.72727272727273, 79.63636363636363, 81.81818181818183, 85.81818181818183, 81.81818181818183, 79.54545454545454, 78.72727272727273, 78.72727272727273, 81.81818181818183, 78.72727272727273, 82.9090909090909, 78.72727272727273, 78.72727272727273, 79.63636363636363, 81.81818181818183, 85.81818181818183, 81.81818181818183, 79.54545454545454, 78.72727272727273, 78.72727272727273, 81.81818181818183, 78.72727272727273, 82.9090909090909);
    //$values=array(10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,85,80,75,70,65,60,55,50,45,40,35,30,30,35,40,45,50,55,60,65,70,75,80);
    $timeRanges = array("00:00-00:30", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "23:30-24:00",);
    return response()->json(["range" => $timeRanges, "values" => $values], 200);
  }
  public function reachtrend()
  {

    $time=array();
    $inc=-26;
    for($i=0;$i<13;$i++){
    $inc=$inc+2;
    //echo "".$inc;
    array_push($time,((string)$inc)." hours");
    }
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
        array_push($viewer, $v->user->id);
      }
      $viewer = array_values(array_unique($viewer));
      $numofViewer = count($viewer);
      $reach = $numofViewer;//($numofViewer / $numOfUser) * 100;
      unset($viewer);
      $viewer = array();
      /*$arr=array(
            'channel_name' => $c->channel_name,
            'reach' => $reach
          );
          array_push($all,$arr);*/
      //array_push($channelArray,$c->channel_name);
      
      array_push($channelArray,"");
      //array_push($channelArray, date("Y-m-d H:i:s", strtotime($time[$i]))."-".date("Y-m-d H:i:s", strtotime($time[$i+1])));
      array_push($reachs, $reach);
      //array_push($reachs,$reach);

    }
    

  return response()->json(["reachsum" => array_sum($reachs), "values" => $reachs, "range" => $channelArray], 200);
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
    

  return response()->json(["reachsum" => array_sum($tvrs), "values" => $tvrs, "range" => $channelArray], 200);
  }
}
