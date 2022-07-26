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
  public function __construct()
  {
        $this->middleware('auth.admin');
  }
  
  public function CurrentStatusUser(){
    $total_user = User::all()->count();
    $stb_total = User::where('type','STB')->get()->count();
    $ott_total = $total_user - $stb_total;
    $active_user =ViewLog::whereNull('finished_watching_at')->distinct('user_id')->count();
    $active_percent = ($active_user * 100)/$total_user ;
    $active_percent = round($active_percent,2);

    $cw=array();
    $ldate = date('Y-m-d H:i:s');
    $stb_user = User::where('type','STB')->select("id","user_name","last_request")->get();
    $stb_active = 0;
    if ($stb_user) {
        foreach ($stb_user as $u) {
            if(abs(strtotime($u->last_request) - strtotime($ldate))<180  && $u->last_request!=null){
                
                array_push($cw,$u);
            }
        }
        $stb_active = count($cw);
    }
    
    $ott_active = $active_user - $stb_active;

    return response()->json(["total_user"=>$total_user,"stb_total"=>$stb_total,"ott_total"=>$ott_total,"stb_active"=>$stb_active,"ott_active"=>$ott_active, "active_user"=>$active_user,"active_percent"=>$active_percent],200);

  }

  public function CurrentStatusTopReach(){
    $finishDateTime = date("Y-m-d H:i:s");
    $min = 1440;
    $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
    $startDateTime = date('Y-m-d H:i:s', $newtimestamp);

    $channels = Channel::all('id', 'channel_name');
    $total_user = User::count();
    $channel_info = [];

    foreach ($channels as $c) {
      $user_count = 0;
      $logs = ViewLog::where('channel_id',$c->id)
          ->where(function($query) use ($startDateTime,$finishDateTime){
              $query->where('finished_watching_at','>',$startDateTime)
              ->orWhereNull('finished_watching_at');
              })
          ->where('started_watching_at','<',$finishDateTime)
          ->distinct()->get('user_id');
      
      $viewlogs = count($logs);

      $user_count = ($viewlogs / $total_user) * 100 ;
      $channel = [
        "channel_name"=> $c->channel_name,
        "users"=> $user_count
      ];
      array_push($channel_info,$channel);      
    }
    array_multisort(array_column($channel_info, 'users'), SORT_DESC, $channel_info);
    if($channel_info[0]['channel_name'] == "unknown"){
      return response()->json(["top_reach"=>$channel_info[1]['channel_name']],200);
    }
    return response()->json(["top_reach"=>$channel_info[0]['channel_name']],200);
  }

  public function CurrentStatusTopTvr(){
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
    $channels = Channel::all('id', 'channel_name');
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
        "channel_name"=> $c->channel_name,
        "tvr"=> $tvr
      ];

      array_push($channelArray, $chnl);
    }
    array_multisort(array_column($channelArray, 'tvr'), SORT_DESC, $channelArray);
    if($channelArray[0]['channel_name'] == "unknown"){
      return response()->json(["top_tvr" => $channelArray[1]['channel_name']], 200);
    }
    return response()->json(["top_tvr" => $channelArray[0]['channel_name']], 200);
    //return response()->json(["tvr"=>$tvr],200);
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

public function reachpercentdashboard(){

  $finishDateTime = date("Y-m-d H:i:s");
  $min = 1440;
  $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
  $startDateTime = date('Y-m-d H:i:s', $newtimestamp);

  $channels = Channel::all('id', 'channel_name');
  $total_user = User::count();
  $channel_info = [];

  foreach ($channels as $c) {
    $user_count = 0;
    $logs = ViewLog::where('channel_id',$c->id)
        ->where(function($query) use ($startDateTime,$finishDateTime){
            $query->where('finished_watching_at','>',$startDateTime)
            ->orWhereNull('finished_watching_at');
            })
        ->where('started_watching_at','<',$finishDateTime)
        ->distinct()->get('user_id');
    
    $viewlogs = count($logs);

    $user_count = ($viewlogs / $total_user) * 100 ;
    $channel = [
      "channel_name"=> $c->channel_name,
      "users"=> $user_count
    ];
    array_push($channel_info,$channel);      
  }
  array_multisort(array_column($channel_info, 'users'), SORT_DESC, $channel_info);
  $label =array();
  $value =array();
  $k=0;
  for ($i = 0; $i<10;$i++){
    if($channel_info[$i]['channel_name'] == "unknown"){
      $k=1;
    }
    $n = $i + $k;
    array_push($label,$channel_info[$n]['channel_name']);
    array_push($value,$channel_info[$n]['users']);
  }
  return response()->json(["value"=>$value,"label"=>$label,"start"=>$startDateTime,"finish"=>$finishDateTime],200);

}

public function reachuserdashboard(){

  $finishDateTime = date("Y-m-d H:i:s");
  $min = 1440;
  $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
  $startDateTime = date('Y-m-d H:i:s', $newtimestamp);

  $channels = Channel::all('id', 'channel_name');
  $total_user = User::count();
  $channel_info = [];

  foreach ($channels as $c) {
    $user_count = 0;
    $logs = ViewLog::where('channel_id',$c->id)
        ->where(function($query) use ($startDateTime,$finishDateTime){
            $query->where('finished_watching_at','>',$startDateTime)
            ->orWhereNull('finished_watching_at');
            })
        ->where('started_watching_at','<',$finishDateTime)
        ->distinct()->get('user_id');
    
    $viewlogs = count($logs);

    //$user_count = ($viewlogs / $total_user) * 100 ;
    $channel = [
      "channel_name"=> $c->channel_name,
      "users"=> $viewlogs
    ];
    array_push($channel_info,$channel);      
  }
  array_multisort(array_column($channel_info, 'users'), SORT_DESC, $channel_info);
  $label =array();
  $value =array();
  $k=0;
  for ($i = 0; $i<10;$i++){

    if($channel_info[$i]['channel_name'] == "unknown"){
      $k=1;
    }
    $n = $i + $k;
    array_push($label,$channel_info[$n]['channel_name']);
    array_push($value,$channel_info[$n]['users']);

  }
  return response()->json(["value"=>$value,"label"=>$label,"start"=>$startDateTime,"finish"=>$finishDateTime],200);

}


public function tvrgraphdashboard(){
      
      
        $channelArray=array();
        $tvrs=array();
        $temp = array();
        $viewer=array();
        $ldate = date('Y-m-d H:i:s');
        /*if($req->start=="" && $req->finish==""){
        return response()->json(["value"=>$reachs,"label"=>$channelArray],200);
        }*/
        $finishDateTime = date("Y-m-d H:i:s");
        $min = 1440;
        $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
        $startDateTime = date('Y-m-d H:i:s', $newtimestamp);
        $start_range = strtotime($startDateTime);
        $finish_range = strtotime($finishDateTime);
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
                ->where(function($query) use ($finishDateTime,$startDateTime){
                  $query->where('finished_watching_at','>',$startDateTime)
                  ->orWhereNull('finished_watching_at');
              })
              ->where('started_watching_at','<',$finishDateTime)
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
              $tempc = array(

                "label"=> $c->channel_name,
        
                "value"=> $tvr
        
              );
              array_push($temp,$tempc);      
            }
            //return response()->json([$temp],200);
      
            $label =array();
            $value =array();
            array_multisort(array_column($temp, 'value'), SORT_DESC, $temp);
            $k=0;
            for ($i = 0; $i<10;$i++){

              if($temp[$i]['label'] == "unknown"){
                $k=1;
              }
              $n = $i + $k;
              array_push($label,$temp[$n]['label']);
              array_push($value,$temp[$n]['value']);
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
      return response()->json(["value"=>$value,"label"=>$label,"start"=>$startDateTime,"finish"=>$finishDateTime],200);
            
      }



      public function tvrgraphzerodashboard(Request $req){
      
      
        $channelArray=array();
        $tvrs=array();
        
        $temp = array();
        $viewer=array();
        $ldate = date('Y-m-d H:i:s');
        $finishDateTime = date("Y-m-d H:i:s");
        $min = 1440;
        $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
        $startDateTime = date('Y-m-d H:i:s', $newtimestamp);
        $start_range = strtotime($startDateTime);
        $finish_range = strtotime($finishDateTime);
        $diff=abs($start_range - $finish_range) / 60;
        
        $channels=Channel::all('id','channel_name');
        $users=User::all();
        $numOfUser=$users->count();
        
        foreach ($channels as $c) {
          $viewers = ViewLog::where('channel_id', $c->id)
                ->where(function($query) use ($finishDateTime,$startDateTime){
                  $query->where('finished_watching_at','>',$startDateTime)
                  ->orWhereNull('finished_watching_at');
              })
              ->where('started_watching_at','<',$finishDateTime)
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
              $tvr=array_sum($viewer) /$numOfUser;
              //$tvr=$tvr/60;
              $tvr=$tvr/$diff;
              //$tvr=$tvr*100;
              unset($viewer);
              $viewer=array();
              array_push($channelArray,$c->channel_name);
              array_push($tvrs,$tvr);
              $tempc = array(

                "label"=> $c->channel_name,
        
                "value"=> $tvr
        
              );
              array_push($temp,$tempc);  
            }
            $label =array();
            $value =array();
            array_multisort(array_column($temp, 'value'), SORT_DESC, $temp);
            $k=0;
            for ($i = 0; $i<10;$i++){

              if($temp[$i]['label'] == "unknown"){
                $k=1;
              }
              $n = $i + $k;
              array_push($label,$temp[$n]['label']);
              array_push($value,$temp[$n]['value']);
            }
      return response()->json(["value"=>$value,"label"=>$label,"start"=>$startDateTime,"finish"=>$finishDateTime],200);
      
      }     
      
      
    public function sharegraphdashboard(Request $req){
        
      $finishDateTime = date("Y-m-d H:i:s");
      $min = 1440;
      $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
      $startDateTime = date('Y-m-d H:i:s', $newtimestamp);
        
      $temp = array();
      $to_time = strtotime($startDateTime);
      $from_time = strtotime($finishDateTime);
      $diff=abs($to_time - $from_time) / 60;
      $users=User::all();
      $numOfUser=$users->count();
 
      $channelArray=array();
      $shares=array();
      $all_tvr =array();

      $channels=Channel::all('id','channel_name');
      foreach ($channels as $c) {
          $tvr =0;
          $viewelogs = ViewLog::where('channel_id', $c->id)
                      ->where(function($query) use ($finishDateTime,$startDateTime){
                      $query->where('finished_watching_at','>',$startDateTime)
                      ->orWhereNull('finished_watching_at');
                      })
                      ->where('started_watching_at','<',$finishDateTime)
                      ->get();
          $total_time_viewed = 0;
          foreach ($viewelogs as $v) {
              
                  if(((strtotime($v->started_watching_at)) < ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null ) )){
                      $watched_sec = abs($to_time - $from_time);
                  }
                  else if(((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) <= ($from_time))){
                      $watched_sec = abs($to_time - strtotime($v->finished_watching_at));
                  }
                  else if(((strtotime($v->started_watching_at)) >= ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null ) )){
                      $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                  }
                  else{
                      $watched_sec = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                  }
                  $total_time_viewed = $total_time_viewed + $watched_sec;
                  //$timeviewed = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at))/60;

          }
          $total_time_viewed = ($total_time_viewed)/60;
          $tvr = $total_time_viewed / $diff;
          $tvr=$tvr/$numOfUser;
          $tvr=$tvr*100;
          $tvr=round($tvr,4);
          
          array_push($all_tvr,$tvr);
          array_push($channelArray,$c->channel_name);

      }
      $total_tvr = array_sum($all_tvr);
      $total_tvr = round($total_tvr,5);
      
      $total_share= 0;
      for($i=0; $i< count($all_tvr); $i++){
          $s = ($all_tvr[$i]/$total_tvr)*100;
          $total_share= $total_share+$s;
          array_push($shares,$s);

      }
      for($i=0; $i< count($all_tvr); $i++){
        $tempc = array(

          "label"=> $channelArray[$i],
  
          "value"=> $shares[$i]
  
        );
        array_push($temp,$tempc);  

      }
      $label =array();
      $value =array();
      array_multisort(array_column($temp, 'value'), SORT_DESC, $temp);
      $k=0;
      for ($i = 0; $i<10;$i++){
        if($temp[$i]['label'] == "unknown"){
          $k=1;
        }
        $n = $i + $k;
        array_push($label,$temp[$n]['label']);
        array_push($value,$temp[$n]['value']);
      }
      return response()->json(["value"=>$value,"label"=>$label,"start"=>$startDateTime,"finish"=>$finishDateTime],200);
      
      //return response()->json(["share"=>$shares,"channels"=>$channelArray],200);
  }

  public function timeSpentUniverse(){
    
      
    $channelArray=array();
    $tvrs=array();
    
    $temp = array();
    $viewer=array();
    $ldate = date('Y-m-d H:i:s');
    /*if($req->start=="" && $req->finish==""){
    return response()->json(["value"=>$reachs,"label"=>$channelArray],200);
    }*/
    $finishDateTime = date("Y-m-d H:i:s");
    $min = 1440;
    $newtimestamp = strtotime("{$finishDateTime} - {$min} minute");
    $startDateTime = date('Y-m-d H:i:s', $newtimestamp);
    $start_range = strtotime($startDateTime);
    $finish_range = strtotime($finishDateTime);
    $diff=abs($start_range - $finish_range) / 60;
    
    //return response()->json([$di],200);
    //return response()->json(["tvr"=>$diff],200);
    $channels=Channel::all('id','channel_name');
    $users=User::all();
    $numOfUser=$users->count();
    //$all=array();
    
    foreach ($channels as $c) {
      $viewers = ViewLog::where('channel_id', $c->id)
            ->where(function($query) use ($finishDateTime,$startDateTime){
              $query->where('finished_watching_at','>',$startDateTime)
              ->orWhereNull('finished_watching_at');
          })
          ->where('started_watching_at','<',$finishDateTime)
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
          $tempc = array(

            "label"=> $c->channel_name,
    
            "value"=> $tvr
    
          );
          array_push($temp,$tempc);  
        }
        $label =array();
        $value =array();
        array_multisort(array_column($temp, 'value'), SORT_DESC, $temp);
        $k=0;
        for ($i = 0; $i<10;$i++){
          if($temp[$i]['label'] == "unknown"){
            $k=1;
          }
          $n = $i + $k;
          array_push($label,$temp[$n]['label']);
          array_push($value,$temp[$n]['value']);
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
  return response()->json(["value"=>$value,"label"=>$label,"start"=>$startDateTime,"finish"=>$finishDateTime],200);
  
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
