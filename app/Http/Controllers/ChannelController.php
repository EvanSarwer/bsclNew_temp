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
  // public function __construct()
  // {
  //       $this->middleware('auth.admin');
  // }

  public function definedtrendreachp(Request $req)
  {
    $reachs=array();
    $channelArray=array();
    $viewer = array();
    $ldate = date('H:i:s');
    $users = User::all();
    $numOfUser = $users->count();
        $start=$req->start;
        if($start==""){
          $start="00:00:00";
        }
        $finish=$req->finish;
        if($finish==""){
          $finish="23:59:59";
        }
        $diff = abs(strtotime($start) - strtotime($finish)) / 60;
        $month=$req->month;
        if(empty($month)){
          $month=array('1','2','3','4','5','6','7','8','9','10','11','12');
        }
        $month=implode(",", $month);
        $year=$req->year;
        if(empty($year)){
          $year=array('2022','2021','2020','2019','2018');
        }
        $year=implode(",", $year);
        $day=$req->day;
        if(empty($day)){
          $day=array('0','1','2','3','4','5','6');
        }
        $day=implode(",", $day);

        $channels = Channel::all('id', 'channel_name');
        if($req->id==""){
    foreach ($channels as $c) {
        $viewers = ViewLog::where('channel_id', $c->id)
        ->where(function ($query) use ($finish,$start) {
          $query->whereTime('finished_watching_at', '>', $start)
            ->orWhereNull('finished_watching_at');
        })
         ->whereTime('started_watching_at', '<', $finish)
        ->where(function ($query) use ($finish,$start,$month,$year,$day) {
          $query
          ->whereRaw("month(started_watching_at) in ($month)")
          ->orWhereRaw("month(finished_watching_at) in ($month)");
        })
        ->where(function ($query) use ($finish,$start,$month,$year,$day) {
          $query->whereRaw("year(started_watching_at) in ($year)")
          ->orWhereRaw("year(finished_watching_at) in ($year)");
        })
        ->where(function ($query) use ($finish,$start,$month,$year,$day) {
          $query->whereRaw("weekday(started_watching_at) in ($day)")
          ->orWhereRaw("weekday(finished_watching_at) in ($day)");
        })
        
        
        
        ->get();
        

      foreach ($viewers as $v) {
        array_push($viewer, $v->user->id);
      }
      $viewer = array_values(array_unique($viewer));
      $numofViewer = count($viewer);
      $reachp = ($numofViewer / $numOfUser) * 100;
      //$reach0=$numofViewer;
      unset($viewer);
      $viewer = array();
      array_push($reachs, $reachp);
      array_push($channelArray,$c->channel_name);
    }}
    else{
      foreach ($channels as $c) {
if($c->id==((int)$req->id)){
          $viewers = ViewLog::where('channel_id', $c->id)
          ->where(function ($query) use ($finish,$start) {
            $query->whereTime('finished_watching_at', '>', $start)
              ->orWhereNull('finished_watching_at');
          })
           ->whereTime('started_watching_at', '<', $finish)
          ->where(function ($query) use ($finish,$start,$month,$year,$day) {
            $query
            ->whereRaw("month(started_watching_at) in ($month)")
            ->orWhereRaw("month(finished_watching_at) in ($month)");
          })
          ->where(function ($query) use ($finish,$start,$month,$year,$day) {
            $query->whereRaw("year(started_watching_at) in ($year)")
            ->orWhereRaw("year(finished_watching_at) in ($year)");
          })
          ->where(function ($query) use ($finish,$start,$month,$year,$day) {
            $query->whereRaw("weekday(started_watching_at) in ($day)")
            ->orWhereRaw("weekday(finished_watching_at) in ($day)");
          })
          
          
          
          ->get();
          
  
        foreach ($viewers as $v) {
          array_push($viewer, $v->user->id);
        }
        $viewer = array_values(array_unique($viewer));
        $numofViewer = count($viewer);
        $reachp = ($numofViewer / $numOfUser) * 100;
        //$reach0=$numofViewer;
        unset($viewer);
        $viewer = array();
        array_push($reachs, $reachp);
        array_push($channelArray,$c->channel_name);
      }
      else{
        array_push($reachs, 0);
        array_push($channelArray,$c->channel_name);
      }
    }
    
    }

      
return response()->json(["label" => $channelArray,"value"=>$reachs], 200);
  }
  public function definedtrendreach0(Request $req)
  {
    $reachs=array();
    $channelArray=array();
    $viewer = array();
    $ldate = date('H:i:s');
    $users = User::all();
    $numOfUser = $users->count();
        $start=$req->start;
        if($start==""){
          $start="00:00:00";
        }
        $finish=$req->finish;
        if($finish==""){
          $finish="23:59:59";
        }
        $diff = abs(strtotime($start) - strtotime($finish)) / 60;
        $month=$req->month;
        if(empty($month)){
          $month=array('1','2','3','4','5','6','7','8','9','10','11','12');
        }
        $month=implode(",", $month);
        $year=$req->year;
        if(empty($year)){
          $year=array('2022','2021','2020','2019','2018');
        }
        $year=implode(",", $year);
        $day=$req->day;
        if(empty($day)){
          $day=array('0','1','2','3','4','5','6');
        }
        $day=implode(",", $day);

        $channels = Channel::all('id', 'channel_name');
        if($req->id==""){
          foreach ($channels as $c) {
        $viewers = ViewLog::where('channel_id', $c->id)
        ->where(function ($query) use ($finish,$start) {
          $query->whereTime('finished_watching_at', '>', $start)
            ->orWhereNull('finished_watching_at');
        })
         ->whereTime('started_watching_at', '<', $finish)
        ->where(function ($query) use ($finish,$start,$month,$year,$day) {
          $query
          ->whereRaw("month(started_watching_at) in ($month)")
          ->orWhereRaw("month(finished_watching_at) in ($month)");
        })
        ->where(function ($query) use ($finish,$start,$month,$year,$day) {
          $query->whereRaw("year(started_watching_at) in ($year)")
          ->orWhereRaw("year(finished_watching_at) in ($year)");
        })
        ->where(function ($query) use ($finish,$start,$month,$year,$day) {
          $query->whereRaw("weekday(started_watching_at) in ($day)")
          ->orWhereRaw("weekday(finished_watching_at) in ($day)");
        })
        
        
        
        ->get();
        

      foreach ($viewers as $v) {
        array_push($viewer, $v->user->id);
      }
      $viewer = array_values(array_unique($viewer));
      $numofViewer = count($viewer);
      //$reachp = ($numofViewer / $numOfUser) * 100;
      $reach0=$numofViewer;
      unset($viewer);
      $viewer = array();
      array_push($reachs, $reach0);
      array_push($channelArray,$c->channel_name);
    }}
    else{
      foreach ($channels as $c) {
        if($c->id==((int)$req->id)){
    $viewers = ViewLog::where('channel_id', $c->id)
    ->where(function ($query) use ($finish,$start) {
      $query->whereTime('finished_watching_at', '>', $start)
        ->orWhereNull('finished_watching_at');
    })
     ->whereTime('started_watching_at', '<', $finish)
    ->where(function ($query) use ($finish,$start,$month,$year,$day) {
      $query
      ->whereRaw("month(started_watching_at) in ($month)")
      ->orWhereRaw("month(finished_watching_at) in ($month)");
    })
    ->where(function ($query) use ($finish,$start,$month,$year,$day) {
      $query->whereRaw("year(started_watching_at) in ($year)")
      ->orWhereRaw("year(finished_watching_at) in ($year)");
    })
    ->where(function ($query) use ($finish,$start,$month,$year,$day) {
      $query->whereRaw("weekday(started_watching_at) in ($day)")
      ->orWhereRaw("weekday(finished_watching_at) in ($day)");
    })
    
    
    
    ->get();
    

  foreach ($viewers as $v) {
    array_push($viewer, $v->user->id);
  }
  $viewer = array_values(array_unique($viewer));
  $numofViewer = count($viewer);
  //$reachp = ($numofViewer / $numOfUser) * 100;
  $reach0=$numofViewer;
  unset($viewer);
  $viewer = array();
  array_push($reachs, $reach0);
  array_push($channelArray,$c->channel_name);
}
else{
  array_push($reachs, 0);
  array_push($channelArray,$c->channel_name);
}
}
}
return response()->json(["label" => $channelArray,"value"=>$reachs], 200);
  }

  public function definedtrendtvrp(Request $req)
  {
    
    $tvrs=array();
    $channelArray=array();
    $viewer = array(); 
    $ldate = date('H:i:s');
    $users = User::all();
    $numOfUser = $users->count();
        $start=$req->start;
        if($start==""){
          $start="00:00:00";
        }
        $finish=$req->finish;
        if($finish==""){
          $finish="23:59:59";
        }
        $diff = abs(strtotime($start) - strtotime($finish)) / 60;
        $month=$req->month;
        if(empty($month)){
          $month=array('1','2','3','4','5','6','7','8','9','10','11','12');
        }
        $month=implode(",", $month);
        $year=$req->year;
        if(empty($year)){
          $year=array('2022','2021','2020','2019','2018');
        }
        $year=implode(",", $year);
        $day=$req->day;
        if(empty($day)){
          $day=array('0','1','2','3','4','5','6');
        }
        $day=implode(",", $day);

        $channels = Channel::all('id', 'channel_name');
        if($req->id==""){
        foreach ($channels as $c) {   
        $viewers = ViewLog::where('channel_id', $c->id)
        ->where(function ($query) use ($finish,$start) {
          $query->whereTime('finished_watching_at', '>', $start)
            ->orWhereNull('finished_watching_at');
        })
         ->whereTime('started_watching_at', '<', $finish)
        ->where(function ($query) use ($finish,$start,$month,$year,$day) {
          $query
          ->whereRaw("month(started_watching_at) in ($month)")
          ->orWhereRaw("month(finished_watching_at) in ($month)");
        })
        ->where(function ($query) use ($finish,$start,$month,$year,$day) {
          $query->whereRaw("year(started_watching_at) in ($year)")
          ->orWhereRaw("year(finished_watching_at) in ($year)");
        })
        ->where(function ($query) use ($finish,$start,$month,$year,$day) {
          $query->whereRaw("weekday(started_watching_at) in ($day)")
          ->orWhereRaw("weekday(finished_watching_at) in ($day)");
        })
        
        
        
        ->get();
        

      foreach ($viewers as $v) {
        if ($v->finished_watching_at == null) {
          if ((strtotime(substr($v->started_watching_at,11))) < (strtotime($start))) {
            $timeviewd = abs(strtotime($start) - strtotime($ldate));
          } else if ((strtotime(substr($v->started_watching_at,11))) >= (strtotime($start))) {
            $timeviewd = abs(strtotime(substr($v->started_watching_at,11)) - strtotime($ldate));
          }
        } else if (((strtotime(substr($v->started_watching_at,11))) < (strtotime($start))) && ((strtotime(substr($v->finished_watching_at,11))) > (strtotime($finish)))) {
          $timeviewd = abs(strtotime($start) - strtotime($finish));
        } else if (((strtotime(substr($v->started_watching_at,11))) < (strtotime($start))) && ((strtotime(substr($v->finished_watching_at,11))) <= (strtotime($finish)))) {
          $timeviewd = abs(strtotime($start) - strtotime(substr($v->finished_watching_at,11)));
        } else if (((strtotime(substr($v->started_watching_at,11))) >= (strtotime($start))) && ((strtotime(substr($v->finished_watching_at,11))) > (strtotime($finish)))) {
          $timeviewd = abs(strtotime(substr($v->started_watching_at,11)) - strtotime($finish));
        } else {
          $timeviewd = abs(strtotime(substr($v->finished_watching_at,11)) - strtotime(substr($v->started_watching_at,11)));
        }
        //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime(substr($v->started_watching_at,11)));
        $timeviewd = $timeviewd / 60;
        array_push($viewer, $timeviewd);
      }
      //$tvr0=array_sum($viewer);
      $tvr = array_sum($viewer) / $numOfUser;
      //$tvr=$tvr/60;
      $tvr = $tvr / $diff;
      $tvrp = $tvr * 100;
      unset($viewer);
      $viewer = array();
      array_push($tvrs, $tvrp);
      array_push($channelArray,$c->channel_name);
    }}
    else{
      foreach ($channels as $c) { 
        if($c->id==((int)$req->id)){  
      $viewers = ViewLog::where('channel_id', $c->id)
      ->where(function ($query) use ($finish,$start) {
        $query->whereTime('finished_watching_at', '>', $start)
          ->orWhereNull('finished_watching_at');
      })
       ->whereTime('started_watching_at', '<', $finish)
      ->where(function ($query) use ($finish,$start,$month,$year,$day) {
        $query
        ->whereRaw("month(started_watching_at) in ($month)")
        ->orWhereRaw("month(finished_watching_at) in ($month)");
      })
      ->where(function ($query) use ($finish,$start,$month,$year,$day) {
        $query->whereRaw("year(started_watching_at) in ($year)")
        ->orWhereRaw("year(finished_watching_at) in ($year)");
      })
      ->where(function ($query) use ($finish,$start,$month,$year,$day) {
        $query->whereRaw("weekday(started_watching_at) in ($day)")
        ->orWhereRaw("weekday(finished_watching_at) in ($day)");
      })
      
      
      
      ->get();
      

    foreach ($viewers as $v) {
      if ($v->finished_watching_at == null) {
        if ((strtotime(substr($v->started_watching_at,11))) < (strtotime($start))) {
          $timeviewd = abs(strtotime($start) - strtotime($ldate));
        } else if ((strtotime(substr($v->started_watching_at,11))) >= (strtotime($start))) {
          $timeviewd = abs(strtotime(substr($v->started_watching_at,11)) - strtotime($ldate));
        }
      } else if (((strtotime(substr($v->started_watching_at,11))) < (strtotime($start))) && ((strtotime(substr($v->finished_watching_at,11))) > (strtotime($finish)))) {
        $timeviewd = abs(strtotime($start) - strtotime($finish));
      } else if (((strtotime(substr($v->started_watching_at,11))) < (strtotime($start))) && ((strtotime(substr($v->finished_watching_at,11))) <= (strtotime($finish)))) {
        $timeviewd = abs(strtotime($start) - strtotime(substr($v->finished_watching_at,11)));
      } else if (((strtotime(substr($v->started_watching_at,11))) >= (strtotime($start))) && ((strtotime(substr($v->finished_watching_at,11))) > (strtotime($finish)))) {
        $timeviewd = abs(strtotime(substr($v->started_watching_at,11)) - strtotime($finish));
      } else {
        $timeviewd = abs(strtotime(substr($v->finished_watching_at,11)) - strtotime(substr($v->started_watching_at,11)));
      }
      //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime(substr($v->started_watching_at,11)));
      $timeviewd = $timeviewd / 60;
      array_push($viewer, $timeviewd);
    }
    //$tvr0=array_sum($viewer);
    $tvr = array_sum($viewer) / $numOfUser;
    //$tvr=$tvr/60;
    $tvr = $tvr / $diff;
    $tvrp = $tvr * 100;
    unset($viewer);
    $viewer = array();
    array_push($tvrs, $tvrp);
    array_push($channelArray,$c->channel_name);
  }
  else{
    array_push($tvrs, 0);
    array_push($channelArray,$c->channel_name);
  }
}
}
return response()->json(["label" => $channelArray,"value"=>$tvrs], 200);
  }

  

  public function definedtrendtvr0(Request $req)
  {
    
    $tvrs=array();
    $channelArray=array();
    $viewer = array(); 
    $ldate = date('H:i:s');
    $users = User::all();
    $numOfUser = $users->count();
        $start=$req->start;
        if($start==""){
          $start="00:00:00";
        }
        $finish=$req->finish;
        if($finish==""){
          $finish="23:59:59";
        }
        $diff = abs(strtotime($start) - strtotime($finish)) / 60;
        $month=$req->month;
        if(empty($month)){
          $month=array('1','2','3','4','5','6','7','8','9','10','11','12');
        }
        $month=implode(",", $month);
        $year=$req->year;
        if(empty($year)){
          $year=array('2022','2021','2020','2019','2018');
        }
        $year=implode(",", $year);
        $day=$req->day;
        if(empty($day)){
          $day=array('0','1','2','3','4','5','6');
        }
        $day=implode(",", $day);

        $channels = Channel::all('id', 'channel_name');
        if($req->id==""){
        foreach ($channels as $c) {   
        $viewers = ViewLog::where('channel_id', $c->id)
        ->where(function ($query) use ($finish,$start) {
          $query->whereTime('finished_watching_at', '>', $start)
            ->orWhereNull('finished_watching_at');
        })
         ->whereTime('started_watching_at', '<', $finish)
        ->where(function ($query) use ($finish,$start,$month,$year,$day) {
          $query
          ->whereRaw("month(started_watching_at) in ($month)")
          ->orWhereRaw("month(finished_watching_at) in ($month)");
        })
        ->where(function ($query) use ($finish,$start,$month,$year,$day) {
          $query->whereRaw("year(started_watching_at) in ($year)")
          ->orWhereRaw("year(finished_watching_at) in ($year)");
        })
        ->where(function ($query) use ($finish,$start,$month,$year,$day) {
          $query->whereRaw("weekday(started_watching_at) in ($day)")
          ->orWhereRaw("weekday(finished_watching_at) in ($day)");
        })
        
        
        
        ->get();
        

      foreach ($viewers as $v) {
        if ($v->finished_watching_at == null) {
          if ((strtotime(substr($v->started_watching_at,11))) < (strtotime($start))) {
            $timeviewd = abs(strtotime($start) - strtotime($ldate));
          } else if ((strtotime(substr($v->started_watching_at,11))) >= (strtotime($start))) {
            $timeviewd = abs(strtotime(substr($v->started_watching_at,11)) - strtotime($ldate));
          }
        } else if (((strtotime(substr($v->started_watching_at,11))) < (strtotime($start))) && ((strtotime(substr($v->finished_watching_at,11))) > (strtotime($finish)))) {
          $timeviewd = abs(strtotime($start) - strtotime($finish));
        } else if (((strtotime(substr($v->started_watching_at,11))) < (strtotime($start))) && ((strtotime(substr($v->finished_watching_at,11))) <= (strtotime($finish)))) {
          $timeviewd = abs(strtotime($start) - strtotime(substr($v->finished_watching_at,11)));
        } else if (((strtotime(substr($v->started_watching_at,11))) >= (strtotime($start))) && ((strtotime(substr($v->finished_watching_at,11))) > (strtotime($finish)))) {
          $timeviewd = abs(strtotime(substr($v->started_watching_at,11)) - strtotime($finish));
        } else {
          $timeviewd = abs(strtotime(substr($v->finished_watching_at,11)) - strtotime(substr($v->started_watching_at,11)));
        }
        //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime(substr($v->started_watching_at,11)));
        $timeviewd = $timeviewd / 60;
        array_push($viewer, $timeviewd);
      }
      $tvr0=array_sum($viewer);
      //$tvr = array_sum($viewer) / $numOfUser;
      //$tvr=$tvr/60;
      //$tvr = $tvr / $diff;
      //$tvrp = $tvr * 100;
      unset($viewer);
      $viewer = array();
      array_push($tvrs, $tvr0);
      array_push($channelArray,$c->channel_name);
    }
  }
  else{
    foreach ($channels as $c) {  
      if($c->id==((int)$req->id)){ 
    $viewers = ViewLog::where('channel_id', $c->id)
    ->where(function ($query) use ($finish,$start) {
      $query->whereTime('finished_watching_at', '>', $start)
        ->orWhereNull('finished_watching_at');
    })
     ->whereTime('started_watching_at', '<', $finish)
    ->where(function ($query) use ($finish,$start,$month,$year,$day) {
      $query
      ->whereRaw("month(started_watching_at) in ($month)")
      ->orWhereRaw("month(finished_watching_at) in ($month)");
    })
    ->where(function ($query) use ($finish,$start,$month,$year,$day) {
      $query->whereRaw("year(started_watching_at) in ($year)")
      ->orWhereRaw("year(finished_watching_at) in ($year)");
    })
    ->where(function ($query) use ($finish,$start,$month,$year,$day) {
      $query->whereRaw("weekday(started_watching_at) in ($day)")
      ->orWhereRaw("weekday(finished_watching_at) in ($day)");
    })
    
    
    
    ->get();
    

  foreach ($viewers as $v) {
    if ($v->finished_watching_at == null) {
      if ((strtotime(substr($v->started_watching_at,11))) < (strtotime($start))) {
        $timeviewd = abs(strtotime($start) - strtotime($ldate));
      } else if ((strtotime(substr($v->started_watching_at,11))) >= (strtotime($start))) {
        $timeviewd = abs(strtotime(substr($v->started_watching_at,11)) - strtotime($ldate));
      }
    } else if (((strtotime(substr($v->started_watching_at,11))) < (strtotime($start))) && ((strtotime(substr($v->finished_watching_at,11))) > (strtotime($finish)))) {
      $timeviewd = abs(strtotime($start) - strtotime($finish));
    } else if (((strtotime(substr($v->started_watching_at,11))) < (strtotime($start))) && ((strtotime(substr($v->finished_watching_at,11))) <= (strtotime($finish)))) {
      $timeviewd = abs(strtotime($start) - strtotime(substr($v->finished_watching_at,11)));
    } else if (((strtotime(substr($v->started_watching_at,11))) >= (strtotime($start))) && ((strtotime(substr($v->finished_watching_at,11))) > (strtotime($finish)))) {
      $timeviewd = abs(strtotime(substr($v->started_watching_at,11)) - strtotime($finish));
    } else {
      $timeviewd = abs(strtotime(substr($v->finished_watching_at,11)) - strtotime(substr($v->started_watching_at,11)));
    }
    //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime(substr($v->started_watching_at,11)));
    $timeviewd = $timeviewd / 60;
    array_push($viewer, $timeviewd);
  }
  $tvr0=array_sum($viewer);
  //$tvr = array_sum($viewer) / $numOfUser;
  //$tvr=$tvr/60;
  //$tvr = $tvr / $diff;
  //$tvrp = $tvr * 100;
  unset($viewer);
  $viewer = array();
  array_push($tvrs, $tvr0);
  array_push($channelArray,$c->channel_name);
}
else{
  array_push($tvrs, 0);
  array_push($channelArray,$c->channel_name);
}
}
}
return response()->json(["label" => $channelArray,"value"=>$tvrs], 200);
  }

  
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
    $fweek=false;
    $weekdays=array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
    $time=array();
    $length=12;
    if($req->time=="Weekly"){
      $fweek=true;
      $tstring="w";
      $inc=-174;  //weekly
      $length=7;
        for($i=0;$i<15;$i++){
        $inc=$inc+24;
        //echo "".$inc;
        array_push($time,((string)$inc)." hours");
        }
    }
    elseif($req->time=="Monthly"){
      $tstring="d";
      $inc=-750;  //monthly
      $length=31;
        for($i=0;$i<32;$i++){
        $inc=$inc+24;
        //echo "".$inc;
        array_push($time,((string)$inc)." hours");
        }
    }
    elseif($req->time=="Yearly"){
      $tstring="y-M";
      $inc=-13;  //yearly
      $length=12;
        for($i=0;$i<13;$i++){
        $inc=$inc+1;
        //echo "".$inc;
        array_push($time,((string)$inc)." months");
        }
    }
    else{
      $inc=-20; //daily
      $tstring="h A";
      for($i=0;$i<13;$i++){
      $inc=$inc+2;
      //echo "".$inc;
      array_push($time,((string)$inc)." hours");
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
      if($fweek){
        array_push($channelArray,$weekdays[ (int)date($tstring, strtotime($time[$i]))]);
      }
      else{
        array_push($channelArray,date($tstring, strtotime($time[$i])));
      }
      array_push($reachs, $reach);

    }
    

  return response()->json(["reachsum" => array_sum($reachs), "values" => $reachs, "label" => $channelArray], 200);
  }
  public function reachtrend(Request $req)
  {
    $fweek=false;
    $weekdays=array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
    $time=array();
    $length=12;
    if($req->time=="Weekly"){
      $fweek=true;
      $tstring="w";
      $inc=-174;  //weekly
      $length=7;
        for($i=0;$i<15;$i++){
        $inc=$inc+24;
        //echo "".$inc;
        array_push($time,((string)$inc)." hours");
        }
    }
    elseif($req->time=="Monthly"){
      $tstring="d";
      $inc=-750;  //monthly
      $length=31;
        for($i=0;$i<32;$i++){
        $inc=$inc+24;
        //echo "".$inc;
        array_push($time,((string)$inc)." hours");
        }
    }
    elseif($req->time=="Yearly"){
      $tstring="y-M";
      $inc=-13;  //yearly
      $length=12;
        for($i=0;$i<13;$i++){
        $inc=$inc+1;
        //echo "".$inc;
        array_push($time,((string)$inc)." months");
        }
    }
    else{
      $inc=-20; //daily
      $tstring="h A";
      for($i=0;$i<13;$i++){
      $inc=$inc+2;
      //echo "".$inc;
      array_push($time,((string)$inc)." hours");
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
      if($fweek){
        array_push($channelArray,$weekdays[ (int)date($tstring, strtotime($time[$i]))]);
      }
      else{
        array_push($channelArray,date($tstring, strtotime($time[$i])));
      }
      array_push($reachs, $reach);

    }
    

  return response()->json(["reachsum" => array_sum($reachs), "values" => $reachs, "label" => $channelArray], 200);
  }
  public function tvrtrend(Request $req)
  {
    $fweek=false;
    $weekdays=array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
    $time=array();
    $length=12;
    if($req->time=="Weekly"){
      $fweek=true;
      $tstring="w";
      $inc=-174;  //weekly
      $length=7;
        for($i=0;$i<15;$i++){
        $inc=$inc+24;
        //echo "".$inc;
        array_push($time,((string)$inc)." hours");
        }
    }
    elseif($req->time=="Monthly"){
      $tstring="d";
      $inc=-750;  //monthly
      $length=31;
        for($i=0;$i<32;$i++){
        $inc=$inc+24;
        //echo "".$inc;
        array_push($time,((string)$inc)." hours");
        }
    }
    elseif($req->time=="Yearly"){
      $tstring="y-M";
      $inc=-13;  //yearly
      $length=12;
        for($i=0;$i<13;$i++){
        $inc=$inc+1;
        //echo "".$inc;
        array_push($time,((string)$inc)." months");
        }
    }
    else{
      $inc=-20; //daily
      $tstring="h A";
      for($i=0;$i<13;$i++){
      $inc=$inc+2;
      //echo "".$inc;
      array_push($time,((string)$inc)." hours");
      }
    }
$ldate = date('Y-m-d H:i:s');
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
      $tvr = $tvr / $diff;
      $tvr = $tvr * 100;
      unset($viewer);
      $viewer = array();
  //    array_push($channelArray, $c->channel_name);
      array_push($tvrs, $tvr);
      if($fweek){
        array_push($channelArray,$weekdays[ (int)date($tstring, strtotime($time[$i]))]);
      }
      else{
        array_push($channelArray,date($tstring, strtotime($time[$i])));
      }
      //array_push($channelArray, date("Y-m-d H:i:s", strtotime($time[$i]))."-".date("Y-m-d H:i:s", strtotime($time[$i+1])));
//      array_push($reachs, $reach);
      //array_push($reachs,$reach);

    }
    

  return response()->json(["reachsum" => array_sum($tvrs), "values" => $tvrs, "label" => $channelArray], 200);
  }



  public function tvrtrendzero(Request $req)
  {

    $fweek=false;
    $weekdays=array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
    $time=array();
    $length=12;
    if($req->time=="Weekly"){
      $fweek=true;
      $tstring="w";
      $inc=-174;  //weekly
      $length=7;
        for($i=0;$i<15;$i++){
        $inc=$inc+24;
        //echo "".$inc;
        array_push($time,((string)$inc)." hours");
        }
    }
    elseif($req->time=="Monthly"){
      $tstring="d";
      $inc=-750;  //monthly
      $length=31;
        for($i=0;$i<32;$i++){
        $inc=$inc+24;
        //echo "".$inc;
        array_push($time,((string)$inc)." hours");
        }
    }
    elseif($req->time=="Yearly"){
      $tstring="y-M";
      $inc=-13;  //yearly
      $length=12;
        for($i=0;$i<13;$i++){
        $inc=$inc+1;
        //echo "".$inc;
        array_push($time,((string)$inc)." months");
        }
    }
    else{
      $inc=-20; //daily
      $tstring="h A";
      for($i=0;$i<13;$i++){
      $inc=$inc+2;
      //echo "".$inc;
      array_push($time,((string)$inc)." hours");
      }
    }
$ldate = date('Y-m-d H:i:s');
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
        $tvr = array_sum($viewer);// / $numOfUser;
      //$tvr=$tvr/60;
      //$diff = (strtotime(date("Y-m-d H:i:s", strtotime($time[$i+1])))-strtotime(date("Y-m-d H:i:s", strtotime($time[$i])))) / 60;
      //$tvr = $tvr / $diff;
      //$tvr = $tvr * 100;
      $tvr=round($tvr);
      unset($viewer);
      $viewer = array();
  //    array_push($channelArray, $c->channel_name);
      array_push($tvrs, $tvr);
      if($fweek){
        array_push($channelArray,$weekdays[ (int)date($tstring, strtotime($time[$i]))]);
      }
      else{
        array_push($channelArray,date($tstring, strtotime($time[$i])));
      }
      //array_push($channelArray, date("Y-m-d H:i:s", strtotime($time[$i]))."-".date("Y-m-d H:i:s", strtotime($time[$i+1])));
//      array_push($reachs, $reach);
      //array_push($reachs,$reach);

    }
    

  return response()->json(["reachsum" => array_sum($tvrs), "values" => $tvrs, "label" => $channelArray], 200);
  }
}
