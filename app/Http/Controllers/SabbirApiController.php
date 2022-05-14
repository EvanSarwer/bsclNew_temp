<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use DateTime;

class SabbirApiController extends Controller
{
  
    public function homecount(){
        $livecount=0;
        $viewer=array();
        $usercountlist=array();
        $userlist=array();
        $userchannellist=array();
        $channellist=array();
        $channeluser=array();
        $users=User::all();
        $channels=Channel::all('id','channel_name');
        $numOfUser=$users->count();
      /*foreach ($users as $u) {
        if(abs(strtotime($ldate)-strtotime($u->last_request))>180){
          //array_push($activity,false);
          
        }
        else{
          //array_push($activity,true);
          $livecount++;
        }
      }*/
      
      foreach ($channels as $c) {
        $viewers = ViewLog::where('channel_id', $c->id)
            ->whereNull('finished_watching_at')
            ->get();
          
          foreach ($viewers as $v) {
              $livecount++;
            array_push($viewer,$v->user->id);
            array_push($userlist,$v->user->id);
            array_push($userchannellist,$c->channel_name);
            }
          //$viewer=array_values(array_unique($viewer));
          $numofViewer=count($viewer);
          unset($viewer);
          $viewer=array();
          if($numofViewer>0){
            array_push($channellist,$c->channel_name);
            array_push($channeluser,$numofViewer);
          }
          }

    return response()->json(["totalUser"=>$numOfUser,"activeCount"=>$livecount,"channellist"=>$channellist,"channeluser"=>$channeluser,"userlist"=>$userlist,"userchannel"=>$userchannellist],200);
    }


    public function userstat(){
    $users=User::all();
    $activity=array();
    $ldate = date('Y-m-d H:i:s');
    //$num=$users->count();
    //$ldate = date("2022-04-05 16:27:59");
    //$ldate = date("2022-03-31 21:42:21");
    foreach ($users as $u) {
      if(abs(strtotime($ldate)-strtotime($u->last_request))>180){
        array_push($activity,false);
      }
      else{
        array_push($activity,true);
      }
    }
    //return response()->json([abs(strtotime($ldate)-strtotime($users[0]->last_request))],200);
    return response()->json(["users"=>$users,"activity"=>$activity],200);
  }
    public function channels(){
    $definedchannel=array("BTV","BTV World","BTV Sangsad","BTV Chattrogram","Independent TV","ATN Bangla","Channel I HD","Ekushey TV","NTV","RTV HD","Boishakhi ","Bangla Vision","Desh TV","My TV","ATN News","Mohona TV","Bijoy TV","Shomoy TV","Masranga TV","Channel 9 HD","Channel 24","Gazi TV","Ekattor TV HD","Asian TV HD","SA TV","Gaan Bangla TV","Jamuna TV","Deepto TV HD","DBC News HD","News 24 HD","Bangla TV","Duranto TV HD","Nagorik TV HD","Ananda TV","T Sports HD","nexus","spice","global");
    $channellist=array();$channellistnew=array();$dc=count($definedchannel);
    $channels=Channel::all('id','channel_name');
    for ($i = 0; $i < 38; $i++){
      for ($j = 0; $j < 40; $j++){
        if($definedchannel[$i]==$channels[$j]->channel_name){
          array_push($channellistnew,$channels[$j]->channel_name);
          break;
        }
        //else{}
      }
      //array_push($channellist,$channels->channel_name);
    }

    return response()->json(['channels'=>$channellistnew],200);
  }
  public function reachpercent(Request $req){
    
    
    $channelArray=array();
    $reachs=array();
    $totalReachs=array();
    $viewer=array();
    /*if($req->start=="" && $req->finish==""){
    return response()->json(["reach"=>$reachs,"channels"=>$channelArray],200);
    }*/
    $startDate=substr($req->start,0,10);
    $startTime=substr($req->start,11,19);
    $finishDate=substr($req->finish,0,10);
    $finishTime=substr($req->finish,11,19);
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
        
        foreach ($viewers as $v) {
          array_push($viewer,$v->user->id);

          }
        $viewer=array_values(array_unique($viewer));
        $numofViewer=count($viewer);
        $reach=($numofViewer/$numOfUser)*100;
        unset($viewer);
        $viewer=array();
        /*$arr=array(
            'channel_name' => $c->channel_name,
            'reach' => $reach
          );
          array_push($all,$arr);*/
          //array_push($channelArray,$c->channel_name);
        array_push($channelArray,$c->channel_name);
        array_push($reachs,$reach);
          //array_push($reachs,$reach);

        }
        $channellistnew=array();$reachllistnew=array();
        $definedchannel=array("BTV","BTV World","BTV Sangsad","BTV Chattrogram","Independent TV","ATN Bangla","Channel I HD","Ekushey TV","NTV","RTV HD","Boishakhi ","Bangla Vision","Desh TV","My TV","ATN News","Mohona TV","Bijoy TV","Shomoy TV","Masranga TV","Channel 9 HD","Channel 24","Gazi TV","Ekattor TV HD","Asian TV HD","SA TV","Gaan Bangla TV","Jamuna TV","Deepto TV HD","DBC News HD","News 24 HD","Bangla TV","Duranto TV HD","Nagorik TV HD","Ananda TV","T Sports HD","nexus","spice","global");
  $channellist=array();$channellistnew=array();$dc=count($definedchannel);
  $channels=Channel::all('id','channel_name');
  for ($i = 0; $i < 38; $i++){
    for ($j = 0; $j < 40; $j++){
      if($definedchannel[$i]==$channelArray[$j]){
        array_push($channellistnew,$channelArray[$j]);
        array_push($reachllistnew,$reachs[$j]);
        break;
      }
    }
  }

  return response()->json(["reachsum"=>array_sum($reachllistnew),"reach"=>$reachllistnew,"channels"=>$channellistnew],200);
}



public function reachuser(Request $req){
      
      
  $channelArray=array();
  $reachs=array();
  $viewer=array();
  /*if($req->start=="" && $req->finish==""){
  return response()->json(["reach"=>$reachs,"channels"=>$channelArray],200);
  }*/
  $startDate=substr($req->start,0,10);
  $startTime=substr($req->start,11,19);
  $finishDate=substr($req->finish,0,10);
  $finishTime=substr($req->finish,11,19);
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
      foreach ($viewers as $v) {
        array_push($viewer,$v->user->id);

        }
        
      //return response()->json([$viewer],200);
      $viewer=array_values(array_unique($viewer));
      $numofViewer=count($viewer);
      $reach=$numofViewer;
      unset($viewer);
      $viewer=array();
      /*$arr=array(
          'channel_name' => $c->channel_name,
          'reach' => $reach
        );
        array_push($all,$arr);*/
        array_push($channelArray,$c->channel_name);
        array_push($reachs,$reach);
      }
      
      $channellistnew=array();$reachllistnew=array();
      $definedchannel=array("BTV","BTV World","BTV Sangsad","BTV Chattrogram","Independent TV","ATN Bangla","Channel I HD","Ekushey TV","NTV","RTV HD","Boishakhi ","Bangla Vision","Desh TV","My TV","ATN News","Mohona TV","Bijoy TV","Shomoy TV","Masranga TV","Channel 9 HD","Channel 24","Gazi TV","Ekattor TV HD","Asian TV HD","SA TV","Gaan Bangla TV","Jamuna TV","Deepto TV HD","DBC News HD","News 24 HD","Bangla TV","Duranto TV HD","Nagorik TV HD","Ananda TV","T Sports HD","nexus","spice","global");
$channellist=array();$channellistnew=array();$dc=count($definedchannel);
$channels=Channel::all('id','channel_name');
for ($i = 0; $i < 38; $i++){
  for ($j = 0; $j < 40; $j++){
    if($definedchannel[$i]==$channelArray[$j]){
      array_push($channellistnew,$channelArray[$j]);
      array_push($reachllistnew,$reachs[$j]);
      break;
    }
  }
}
      return response()->json(["reachsum"=>array_sum($reachllistnew),"reach"=>$reachllistnew,"channels"=>$channellistnew],200);
}

  public function tvrgraph(Request $req){
      
      
      $channelArray=array();
      $reachs=array();
      $viewer=array();
      /*if($req->start=="" && $req->finish==""){
      return response()->json(["reach"=>$reachs,"channels"=>$channelArray],200);
      }*/
      $startDate=substr($req->start,0,10);
      $startTime=substr($req->start,11,19);
      $finishDate=substr($req->finish,0,10);
      $finishTime=substr($req->finish,11,19);
      $start_range = strtotime($startDate." ".$startTime);
      $finish_range = strtotime($finishDate." ".$finishTime);
      $diff=abs($start_range - $finish_range) / 60;
      //return response()->json(["tvr"=>$diff],200);
      //$channels=Channel::all('id','channel_name');
      $users=User::all();
      $numOfUser=$users->count();
      //$all=array();
      
      
          $viewers = ViewLog::where('channel_id', $req->id)
          ->where('started_watching_at','>',date($startDate)." ".$startTime)
          ->where('finished_watching_at','<',date($finishDate)." ".$finishTime)
          ->get();
          foreach ($viewers as $v) {
            $timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at)) / 60;
            array_push($viewer,$timeviewd);

            }
            //return response()->json([$viewer],200);
            $tvr=array_sum($viewer)/$numOfUser;
            $tvr=$tvr/$diff;
            $tvr=$tvr*100;
            return response()->json(["tvr"=>$tvr],200);
          $viewer=array_values(array_unique($viewer));
          $numofViewer=count($viewer);
          $reach=($numofViewer/$numOfUser)*100;
          /*$arr=array(
              'channel_name' => $c->channel_name,
              'reach' => $reach
            );
            array_push($all,$arr);*/
            array_push($channelArray,$c->channel_name);
            array_push($reachs,$reach);
          
          return response()->json(["reach"=>$reachllistnew,"channels"=>$channellistnew],200);
  }


  public function tvrgraphallchannel(Request $req){
      
      
    $channelArray=array();
    $tvrs=array();
    $viewer=array();
    $ldate = date('Y-m-d H:i:s');
    /*if($req->start=="" && $req->finish==""){
    return response()->json(["reach"=>$reachs,"channels"=>$channelArray],200);
    }*/
    $startDate=substr($req->start,0,10);
    $startTime=substr($req->start,11,19);
    $finishDate=substr($req->finish,0,10);
    $finishTime=substr($req->finish,11,19);
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
        $channellistnew=array();$tvrlistnew=array();
        $definedchannel=array("BTV","BTV World","BTV Sangsad","BTV Chattrogram","Independent TV","ATN Bangla","Channel I HD","Ekushey TV","NTV","RTV HD","Boishakhi ","Bangla Vision","Desh TV","My TV","ATN News","Mohona TV","Bijoy TV","Shomoy TV","Masranga TV","Channel 9 HD","Channel 24","Gazi TV","Ekattor TV HD","Asian TV HD","SA TV","Gaan Bangla TV","Jamuna TV","Deepto TV HD","DBC News HD","News 24 HD","Bangla TV","Duranto TV HD","Nagorik TV HD","Ananda TV","T Sports HD","nexus","spice","global");
  $channellist=array();$channellistnew=array();$dc=count($definedchannel);
  $channels=Channel::all('id','channel_name');
  for ($i = 0; $i < 38; $i++){
    for ($j = 0; $j < 40; $j++){
      if($definedchannel[$i]==$channelArray[$j]){
        array_push($channellistnew,$channelArray[$j]);
        array_push($tvrlistnew,$tvrs[$j]);
        break;
      }
    }
  }
        return response()->json(["tvrs"=>$tvrlistnew,"channels"=>$channellistnew],200);
          //return response()->json(["tvr"=>$tvr],200);
        
}






public function tvrgraphallchannelzero(Request $req){
      
      
  $channelArray=array();
  $tvrs=array();
  $viewer=array();
  $ldate = date('Y-m-d H:i:s');
  /*if($req->start=="" && $req->finish==""){
  return response()->json(["reach"=>$reachs,"channels"=>$channelArray],200);
  }*/
  $startDate=substr($req->start,0,10);
  $startTime=substr($req->start,11,19);
  $finishDate=substr($req->finish,0,10);
  $finishTime=substr($req->finish,11,19);
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
      $channellistnew=array();$tvrlistnew=array();
      $definedchannel=array("BTV","BTV World","BTV Sangsad","BTV Chattrogram","Independent TV","ATN Bangla","Channel I HD","Ekushey TV","NTV","RTV HD","Boishakhi ","Bangla Vision","Desh TV","My TV","ATN News","Mohona TV","Bijoy TV","Shomoy TV","Masranga TV","Channel 9 HD","Channel 24","Gazi TV","Ekattor TV HD","Asian TV HD","SA TV","Gaan Bangla TV","Jamuna TV","Deepto TV HD","DBC News HD","News 24 HD","Bangla TV","Duranto TV HD","Nagorik TV HD","Ananda TV","T Sports HD","nexus","spice","global");
$channellist=array();$channellistnew=array();$dc=count($definedchannel);
$channels=Channel::all('id','channel_name');
for ($i = 0; $i < 38; $i++){
  for ($j = 0; $j < 40; $j++){
    if($definedchannel[$i]==$channelArray[$j]){
      array_push($channellistnew,$channelArray[$j]);
      array_push($tvrlistnew,$tvrs[$j]);
      break;
    }
  }
}
      return response()->json(["tvrs"=>$tvrlistnew,"channels"=>$channellistnew],200);
        //return response()->json(["tvr"=>$tvr],200);
      
}








public function share(Request $req){
      
      
  $channelArray=array();
  $tvrs=array();
  $viewer=array();
  /*if($req->start=="" && $req->finish==""){
  return response()->json(["reach"=>$reachs,"channels"=>$channelArray],200);
  }*/
  $startDate=substr($req->start,0,10);
  $startTime=substr($req->start,11,19);
  $finishDate=substr($req->finish,0,10);
  $finishTime=substr($req->finish,11,19);
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
      $query->whereBetween('started_watching_at',array(date($startDate)." ".$startTime,date($finishDate)." ".$finishTime))
      ->orWhereBetween('finished_watching_at',array(date($startDate)." ".$startTime,date($finishDate)." ".$finishTime));
  })
    ->get();
      foreach ($viewers as $v) {
        if(((strtotime($v->started_watching_at)) < ($start_range)) && ((strtotime($v->finished_watching_at)) > ($finish_range))){
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
        $timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
        array_push($viewer,$timeviewd);

        }
        //return response()->json([$viewer],200);
        $tvr=array_sum($viewer)/$numOfUser;
        $tvr=$tvr/60;
        $tvr=$tvr/$diff;
        $tvr=$tvr*100;
        unset($viewer);
        $viewer=array();
        array_push($channelArray,$c->channel_name);
        array_push($tvrs,$tvr);
      }
      $channellistnew=array();$tvrlistnew=array();
      $definedchannel=array("BTV","BTV World","BTV Sangsad","BTV Chattrogram","Independent TV","ATN Bangla","Channel I HD","Ekushey TV","NTV","RTV HD","Boishakhi ","Bangla Vision","Desh TV","My TV","ATN News","Mohona TV","Bijoy TV","Shomoy TV","Masranga TV","Channel 9 HD","Channel 24","Gazi TV","Ekattor TV HD","Asian TV HD","SA TV","Gaan Bangla TV","Jamuna TV","Deepto TV HD","DBC News HD","News 24 HD","Bangla TV","Duranto TV HD","Nagorik TV HD","Ananda TV","T Sports HD","nexus","spice","global");
$channellist=array();$channellistnew=array();$dc=count($definedchannel);
$channels=Channel::all('id','channel_name');
for ($i = 0; $i < 38; $i++){
  for ($j = 0; $j < 40; $j++){
    if($definedchannel[$i]==$channelArray[$j]){
      array_push($channellistnew,$channelArray[$j]);
      array_push($tvrlistnew,$tvrs[$j]);
      break;
    }
  }
}

      $total_tvr=array_sum($tvrlistnew);
      $dd=100/$total_tvr;
      $cnum=count($tvrlistnew);
      /*foreach($tvrlistnew as $tt) {
        $tt=$tt*100;
     }*/
      for($x = 0; $x < $cnum; $x++){
        $tvrlistnew[$x]=$tvrlistnew[$x]*$dd;
      }
      return response()->json(["total_tvr"=>$total_tvr,"tvrs"=>$tvrlistnew,"channels"=>$channellistnew],200);
        //return response()->json(["tvr"=>$tvr],200);
      
}

public function demo(){
  
  $viewer=array();
  $channels=Channel::all('id','channel_name');
  foreach ($channels as $c) {
    $viewers = ViewLog::where('channel_id', $c->id)
    ->where('started_watching_at',date("2021-10-13")." "."22:11:42")
    ->get();
    foreach ($viewers as $v) {
      $timeviewd=$v->finished_watching_at;
      if($v->finished_watching_at!=null){
        return response()->json(["null value"],200);

      }
      array_push($viewer,$timeviewd);

      }
    }
  return response()->json(["vv"=>$viewer],200);
  //return response()->json(["tvr"=>$tvr],200);

}
  public function tvrgraphallchannelbackup(Request $req){
    
    
  $channelArray=array();
  $tvrs=array();
  $viewer=array();
  /*if($req->start=="" && $req->finish==""){
  return response()->json(["reach"=>$reachs,"channels"=>$channelArray],200);
  }*/
  $startDate=substr($req->start,0,10);
  $startTime=substr($req->start,11,19);
  $finishDate=substr($req->finish,0,10);
  $finishTime=substr($req->finish,11,19);
  $start_range = strtotime($startDate." ".$startTime);
  $finish_range = strtotime($finishDate." ".$finishTime);
  $diff=abs($start_range - $finish_range) / 60;
  //return response()->json(["tvr"=>$diff],200);
  $channels=Channel::all('id','channel_name');
  $users=User::all();
  $numOfUser=$users->count();
  //$all=array();
  
  foreach ($channels as $c) {
      $viewers = ViewLog::where('channel_id', $c->id)
      ->where('started_watching_at','>',date($startDate)." ".$startTime)
      ->where('finished_watching_at','<',date($finishDate)." ".$finishTime)
      ->get();
      foreach ($viewers as $v) {
        $timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at)) / 60;
        array_push($viewer,$timeviewd);

        }
        //return response()->json([$viewer],200);
        $tvr=array_sum($viewer)/$numOfUser;
        $tvr=$tvr/$diff;
        $tvr=$tvr*100;
        unset($viewer);
        $viewer=array();
        array_push($channelArray,$c->channel_name);
        array_push($tvrs,$tvr);
      }
      $channellistnew=array();$tvrlistnew=array();
      $definedchannel=array("BTV","BTV World","BTV Sangsad","BTV Chattrogram","Independent TV","ATN Bangla","Channel I HD","Ekushey TV","NTV","RTV HD","Boishakhi ","Bangla Vision","Desh TV","My TV","ATN News","Mohona TV","Bijoy TV","Shomoy TV","Masranga TV","Channel 9 HD","Channel 24","Gazi TV","Ekattor TV HD","Asian TV HD","SA TV","Gaan Bangla TV","Jamuna TV","Deepto TV HD","DBC News HD","News 24 HD","Bangla TV","Duranto TV HD","Nagorik TV HD","Ananda TV","T Sports HD","nexus","spice","global");
$channellist=array();$channellistnew=array();$dc=count($definedchannel);
$channels=Channel::all('id','channel_name');
for ($i = 0; $i < 38; $i++){
  for ($j = 0; $j < 40; $j++){
    if($definedchannel[$i]==$channelArray[$j]){
      array_push($channellistnew,$channelArray[$j]);
      array_push($tvrlistnew,$tvrs[$j]);
      break;
    }
  }
}
      return response()->json(["tvrs"=>$tvrlistnew,"channels"=>$channellistnew],200);
        //return response()->json(["tvr"=>$tvr],200);
      
}




public function timespent(Request $req){
    
    
  $channelArray=array();
  $timespents=array();
  $viewer=array();
  /*if($req->start=="" && $req->finish==""){
  return response()->json(["reach"=>$reachs,"channels"=>$channelArray],200);
  }*/
  $startDate=substr($req->start,0,10);
  $startTime=substr($req->start,11,19);
  $finishDate=substr($req->finish,0,10);
  $finishTime=substr($req->finish,11,19);
  $start_range = strtotime($startDate." ".$startTime);
  $finish_range = strtotime($finishDate." ".$finishTime);
  $diff=abs($start_range - $finish_range) / 60;
  //return response()->json(["timespent"=>$diff],200);
  $channels=Channel::all('id','channel_name');
  $users=User::all();
  $numOfUser=$users->count();
  //$all=array();
  
  foreach ($channels as $c) {
      $viewers = ViewLog::where('channel_id', $c->id)
      ->where('started_watching_at','>',date($startDate)." ".$startTime)
      ->where('finished_watching_at','<',date($finishDate)." ".$finishTime)
      ->get();
      foreach ($viewers as $v) {
        $timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at)) / 60;
        array_push($viewer,$timeviewd);

        }
        //return response()->json([$viewer],200);
        $timespent=array_sum($viewer);//$numOfUser;
        //$timespent=$timespent/$diff;
        //$timespent=$timespent*100;
        unset($viewer);
        $viewer=array();
        array_push($channelArray,$c->channel_name);
        array_push($timespents,$timespent);
      }
      $channellistnew=array();$timespentlistnew=array();
        $definedchannel=array("BTV","BTV World","BTV Sangsad","BTV Chattrogram","Independent TV","ATN Bangla","Channel I HD","Ekushey TV","NTV","RTV HD","Boishakhi ","Bangla Vision","Desh TV","My TV","ATN News","Mohona TV","Bijoy TV","Shomoy TV","Masranga TV","Channel 9 HD","Channel 24","Gazi TV","Ekattor TV HD","Asian TV HD","SA TV","Gaan Bangla TV","Jamuna TV","Deepto TV HD","DBC News HD","News 24 HD","Bangla TV","Duranto TV HD","Nagorik TV HD","Ananda TV","T Sports HD","nexus","spice","global");
  $channellist=array();$channellistnew=array();$dc=count($definedchannel);
  $channels=Channel::all('id','channel_name');
  for ($i = 0; $i < 38; $i++){
    for ($j = 0; $j < 40; $j++){
      if($definedchannel[$i]==$channelArray[$j]){
        array_push($channellistnew,$channelArray[$j]);
        array_push($timespentlistnew,$timespents[$j]);
        break;
      }
    }
  }
        return response()->json(["timespent"=>$timespentlistnew,"channels"=>$channellistnew],200);
        
      
}



public function index(){
  return view("graph.index");
}
public function allgraphmenu(){
  return view("graph.allgraphmenu");
}
public function tvrgraphallchannelzeroview(){
  return view("graph.tvrgraphallchannelzero");
}
public function tvrgraphallchannelview(){
  return view("graph.tvrgraphallchannel");
}
public function timespentview(){
  return view("graph.timespent");
}
public function reachuserview(){
  return view("graph.reachuser");
}
public function shareview(){
  return view("graph.share");
}
public function userstatview(){
  return view("stats.userstat");
}
public function tvr(Request $req){
  //return $req->id;
        return view("graph.tvr")->with('id',$req->id);
}
public function channeltvr(Request $req){
  $channels=Channel::all('id','channel_name');

        return view("graph.channelstvr")->with('channels',$channels);
}
}
