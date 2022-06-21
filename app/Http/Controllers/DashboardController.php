<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Http;
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
    ->take(10)
    ->sortBy('id');

    $value = [];
    $label = [];
    foreach($channels as $c){
        $value[] = ($c->channel_reach)*100/Channel::count();
        $label[] = $c->channel_name;
    }
    return response()->json(["value"=>$value,"label"=>$label,"start"=>($startDate." ".$startTime),"finish"=>($finishDate." ".$finishTime)],200);
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
    return response()->json(["value"=>$value,"label"=>$label,"start"=>($startDate." ".$startTime),"finish"=>($finishDate." ".$finishTime)],200);
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
        
        //return response()->json([$channels],200);
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
              if($tvrs[$i]>$temptvr[10]){
                array_push($nchannelArray,$channelArray[$i]);
            array_push($ntvrs,$tvrs[$i]);
            $cc++;
              }
            }
      return response()->json(["value"=>$ntvrs,"label"=>$nchannelArray,"start"=>($startDate." ".$startTime),"finish"=>($finishDate." ".$finishTime)],200);
            
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
              if($tvrs[$i]>$temptvr[10]){
                array_push($nchannelArray,$channelArray[$i]);
            array_push($ntvrs,$tvrs[$i]);
            $cc++;
              }
            }
      return response()->json(["value"=>$ntvrs,"label"=>$nchannelArray,"start"=>($startDate." ".$startTime),"finish"=>($finishDate." ".$finishTime)],200);
      
      }      

      public function activechannellistget(){
        // $request = array("channel_name"=>31,"device_id"=>1,"time_stamp"=>"2022-04-07 14:51:05");
        // $request = (object)$request;
        
        // $rsp = Http::get("http://123.200.5.219:8000/api/receive?channel_name=$request->channel_name&device_id=$request->device_id&time_stamp=$request->time_stamp");
  
        
        $channels = Channel::withCount(['viewLogs' => function($query){
          $query->where('finished_watching_at', null);
      } ])->orderBy('view_logs_count', 'DESC')->get(['id','channel_name']);  
      $activeChannels =[];
      foreach($channels as $c){
          if($c->view_logs_count > 0){
              $activeChannel =[
                "channel_id" => $c->id,
                "channel_name" => $c->channel_name,
                "channel_logo" => $c->logo,
                "user_count" => $c->view_logs_count
            ];
            array_push($activeChannels,$activeChannel);
          }
      }
      return response()->json(["activeChannels"=> $activeChannels],200);
    }

    public function activeuserlistget(){     
      $actives = ViewLog::where('finished_watching_at', null)
            ->with(['channel', 'user'])
            ->orderBy('started_watching_at', 'ASC')->get();
      foreach($actives as $a){
        $a->duration = Carbon::parse($a->started_watching_at)->diffForHumans();
        $a->totaltime = Carbon::parse($a->started_watching_at)->diffForHumans();
      }
      return response()->json($actives,200);
  
  }
}
