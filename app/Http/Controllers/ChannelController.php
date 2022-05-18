<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChannelController extends Controller
{
    
    public function reachpercent(){
      
      //$timeRanges=array("00:00-00:30","00:30-01:00","01:00-01:30","01:30-02:00","02:00-02:30","","04-06","06-08","08-10","10-12","12-14","14-16","16-18","18-20","20-22","22-24");
      $values=array(78.72727272727273,78.72727272727273,79.63636363636363,81.81818181818183,85.81818181818183,81.81818181818183,79.54545454545454,78.72727272727273,78.72727272727273,81.81818181818183,78.72727272727273,82.9090909090909,78.72727272727273,78.72727272727273,79.63636363636363,81.81818181818183,85.81818181818183,81.81818181818183,79.54545454545454,78.72727272727273,78.72727272727273,81.81818181818183,78.72727272727273,82.9090909090909,78.72727272727273,78.72727272727273,79.63636363636363,81.81818181818183,85.81818181818183,81.81818181818183,79.54545454545454,78.72727272727273,78.72727272727273,81.81818181818183,78.72727272727273,82.9090909090909,78.72727272727273,78.72727272727273,79.63636363636363,81.81818181818183,85.81818181818183,81.81818181818183,79.54545454545454,78.72727272727273,78.72727272727273,81.81818181818183,78.72727272727273,82.9090909090909);
      //$values=array(10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,85,80,75,70,65,60,55,50,45,40,35,30,30,35,40,45,50,55,60,65,70,75,80);
      $timeRanges=array("00:00-00:30","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","23:30-24:00",);
      return response()->json(["range"=>$timeRanges,"values"=>$values],200);
      }

      

}
