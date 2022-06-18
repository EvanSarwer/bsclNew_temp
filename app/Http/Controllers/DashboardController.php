<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
class DashboardController extends Controller
{

    public function reachpercentdashboard(){
      
      
      $startDate=date('Y-m-d',strtotime("-7 days"));
    $startTime="00:00:00";
    $finishDate=date('Y-m-d',strtotime("-1 days"));
    $finishTime="23:59:59";
    $channels = Channel::all()->filter(function ($c) use ($finishDate, $finishTime,$startDate,$startTime)
    { return $c->reach( $startDate, $startTime, $finishDate,$finishTime) >0 && $c->id != 39;})
    ->sortByDesc('channel_reach')
    ->take(10);

    $value = [];
    $label = [];
    foreach($channels as $c){
        $value[] = ($c->channel_reach)*100/Channel::count();
        $label[] = $c->channel_name;
    }
    return response()->json(["value"=>$value,"label"=>$label],200);
  }
      
public function reachuserdashboard(){
    
    
    $startDate=date('Y-m-d',strtotime("-7 days"));
    $startTime="00:00:00";
    $finishDate=date('Y-m-d',strtotime("-1 days"));
    $finishTime="23:59:59";
    $channels = Channel::all()->filter(function ($c) use ($finishDate, $finishTime,$startDate,$startTime)
    { return $c->reach( $startDate, $startTime, $finishDate,$finishTime) >0 && $c->id != 39;})
    ->sortByDesc('channel_reach')
    ->take(10);

    $value = [];
    $label = [];
    foreach($channels as $c){
        $value[] = $c->channel_reach;
        $label[] = $c->channel_name;
    }
    return response()->json(["value"=>$value,"label"=>$label],200);
  }


public function tvrgraphdashboard(){
      
      
        $channelArray=array();
        $tvrs=array();
        $viewer=array();
        $ldate = date('Y-m-d H:i:s');
        /*if($req->start=="" && $req->finish==""){
        return response()->json(["value"=>$reachs,"label"=>$channelArray],200);
        }*/
        $startDate=date('Y-m-d',strtotime("-30 days"));
        $startTime="00:00:00";
        $finishDate=date('Y-m-d',strtotime("-1 days"));
        $finishTime="23:59:59";
        $start_range = strtotime($startDate." ".$startTime);
        $finish_range = strtotime($finishDate." ".$finishTime);
        $diff=abs($start_range - $finish_range) / 60;
        
        //return response()->json([$di],200);
        //return response()->json(["tvr"=>$diff],200);
        $channels=Channel::all('id','channel_name');
        $users=User::all();
        $numOfUser=$users->count();
        //$all=array();
        
        foreach ($channels as $c) {
          $viewers = ViewLog::where('channel_id', $c->id)
                ->where(function($query) use ($finishDate, $finishTime,$startDate,$startTime){
                  $query->where('finished_watching_at','>',date($startDate)." ".$startTime)
                  ->orWhereNull('finished_watching_at');
              })
              ->where('started_watching_at','<',date($finishDate)." ".$finishTime)
                ->get();
          /*$viewers = ViewLog::where('channel_id', $c->id)
          ->where('started_watching_at','<',date($finishDate)." ".$finishTime)
          ->where('finished_watching_at','>',date($startDate)." ".$startTime)
          ->get();*/
            foreach ($viewers as $v) {
              if($v->finished_watching_at==null){
                if((strtotime($v->started_watching_at)) < ($start_range)){
                  $timeviewd = abs($start_range - strtotime($ldate));
                }
                else if((strtotime($v->started_watching_at)) >= ($start_range)){
                  $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($ldate));
                }
              }
              else if(((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))){
                $timeviewd = abs($start_range - $finish_range);
            }
            else if(((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) <= ($finish_range))){
                $timeviewd = abs($start_range - strtotime($v->finished_watching_at));
            }
            else if(((strtotime($v->started_watching_at)) >= ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))){
                $timeviewd = abs(strtotime($v->started_watching_at) - $finish_range);
            }
            else{
                $timeviewd = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
            }
              //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
              $timeviewd=$timeviewd/60;
              array_push($viewer,$timeviewd);
      
              }
              //return response()->json([$viewer],200);
              $tvr=array_sum($viewer)/$numOfUser;
              //$tvr=$tvr/60;
              $tvr=$tvr/$diff;
              $tvr=$tvr*100;
              unset($viewer);
              $viewer=array();
              array_push($channelArray,$c->channel_name);
              array_push($tvrs,$tvr);
            }
            $temptvr=$tvrs;
            $ntvrs=array();
            $nchannelArray=array();
            rsort($temptvr);
            $rlength=count($tvrs);
            $cc=0;
            for($i=0;$i<$rlength && $cc<10;$i++){
              if($tvrs[$i]<$temptvr[10]){
                array_push($nchannelArray,$channelArray[$i]);
            array_push($ntvrs,$tvrs[$i]);
            $cc++;
              }
            }
      return response()->json(["value"=>$ntvrs,"label"=>$nchannelArray],200);
            
      }



      public function tvrgraphzerodashboard(Request $req){
      
      
        $channelArray=array();
        $tvrs=array();
        $viewer=array();
        $ldate = date('Y-m-d H:i:s');
        /*if($req->start=="" && $req->finish==""){
        return response()->json(["value"=>$reachs,"label"=>$channelArray],200);
        }*/
        $startDate=date('Y-m-d',strtotime("-30 days"));
        $startTime="00:00:00";
        $finishDate=date('Y-m-d',strtotime("-1 days"));
        $finishTime="23:59:59";
        $start_range = strtotime($startDate." ".$startTime);
        $finish_range = strtotime($finishDate." ".$finishTime);
        $diff=abs($start_range - $finish_range) / 60;
        
        //return response()->json([$di],200);
        //return response()->json(["tvr"=>$diff],200);
        $channels=Channel::all('id','channel_name');
        $users=User::all();
        $numOfUser=$users->count();
        //$all=array();
        
        foreach ($channels as $c) {
          $viewers = ViewLog::where('channel_id', $c->id)
                ->where(function($query) use ($finishDate, $finishTime,$startDate,$startTime){
                  $query->where('finished_watching_at','>',date($startDate)." ".$startTime)
                  ->orWhereNull('finished_watching_at');
              })
              ->where('started_watching_at','<',date($finishDate)." ".$finishTime)
                ->get();
          /*$viewers = ViewLog::where('channel_id', $c->id)
          ->where('started_watching_at','<',date($finishDate)." ".$finishTime)
          ->where('finished_watching_at','>',date($startDate)." ".$startTime)
          ->get();*/
            foreach ($viewers as $v) {
              if($v->finished_watching_at==null){
                if((strtotime($v->started_watching_at)) < ($start_range)){
                  $timeviewd = abs($start_range - strtotime($ldate));
                }
                else if((strtotime($v->started_watching_at)) >= ($start_range)){
                  $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($ldate));
                }
              }
              else if(((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))){
                $timeviewd = abs($start_range - $finish_range);
            }
            else if(((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) <= ($finish_range))){
                $timeviewd = abs($start_range - strtotime($v->finished_watching_at));
            }
            else if(((strtotime($v->started_watching_at)) >= ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))){
                $timeviewd = abs(strtotime($v->started_watching_at) - $finish_range);
            }
            else{
                $timeviewd = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
            }
              //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
              $timeviewd=$timeviewd/60;
              array_push($viewer,$timeviewd);
      
              }
              //return response()->json([$viewer],200);
              $tvr=array_sum($viewer);///$numOfUser;
              //$tvr=$tvr/60;
              //$tvr=$tvr/$diff;
              //$tvr=$tvr*100;
              unset($viewer);
              $viewer=array();
              array_push($channelArray,$c->channel_name);
              array_push($tvrs,$tvr);
            }
            $temptvr=$tvrs;
            $ntvrs=array();
            $nchannelArray=array();
            rsort($temptvr);
            $rlength=count($tvrs);
            $cc=0;
            for($i=0;$i<$rlength && $cc<10;$i++){
              if($tvrs[$i]<$temptvr[10]){
                array_push($nchannelArray,$channelArray[$i]);
            array_push($ntvrs,$tvrs[$i]);
            $cc++;
              }
            }
      return response()->json(["value"=>$ntvrs,"label"=>$nchannelArray],200);
      
      }      

      public function activechannellistget(){
        $channels = Channel::all();
        $activeChannels =[];
        foreach ($channels as $c){
            $viewlogs = ViewLog::where('channel_id',$c->id)
                        ->whereNull('finished_watching_at')->get();

            if(count($viewlogs) > 0){
                $activeChannel =[
                    "channel_id" => $c->id,
                    "channel_name" => $c->channel_name,
                    "channel_logo" => $c->logo,
                    "user_count" => count($viewlogs)
                ];
                array_push($activeChannels,$activeChannel);
            }
            
        }
        //array_multisort(array_column($inventory, 'key_name'), SORT_DESC, SORT_NATURAL|SORT_FLAG_CASE, $inventory);     // Case Insensitive Sort
        array_multisort(array_column($activeChannels, 'user_count'), SORT_DESC, $activeChannels);
        return response()->json(["activeChannels"=> $activeChannels],200);
    }

    public function activeuserlistget(){
      $viewlogs = ViewLog::whereNull('finished_watching_at')
      ->distinct('user_id')->get();
      $activeUsers = [];
      if(count($viewlogs) > 0){
        $total_time_viewed = 0;
        $total_time = 0;
        $duration = "";
              foreach($viewlogs as $v){
              $user = User::where('id',$v->user_id)->first();
              $channel = Channel::where('id',$v->channel_id)->first();

              //$finishDateTime = date('2022-05-18 23:59:59');
              $finishDateTime = date('Y-m-d h:m:i');
              $from_time = strtotime($finishDateTime);
              $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
              $total_time_viewed = ($watched_sec)/60;
              //$tota_time_viewed = $tota_time_viewed / $diff;
              $total_time_viewed=round($total_time_viewed);
              if ($total_time_viewed >= 1440){
                $total_time = (($total_time_viewed/60)/24);
                $total_time = floor($total_time);
                $duration = $total_time." day";

              }
              elseif($total_time_viewed >= 60){
                $total_time = ($total_time_viewed/60);
                $total_time = floor($total_time);
                $duration = $total_time." hour";
              }
              else{
                $total_time = floor($total_time_viewed);
                $duration = $total_time." minute";
              }

              $activeUser = [
                  "user_id" => $user->id,
                  "user_name" => $user->user_name,
                  "channel_id" => $channel->id,
                  "channel_name" => $channel->channel_name,
                  "channel_logo" => $channel->logo,
                  "start_watching" => $v->started_watching_at,
                  "totaltime" => $total_time_viewed,
                  "duration" => $duration
              ];
              array_push($activeUsers,$activeUser);
          }
      }
      array_multisort(array_column($activeUsers, 'totaltime'), SORT_DESC, $activeUsers);
      return response()->json(["activeUsers"=>$activeUsers],200);

  }
}
