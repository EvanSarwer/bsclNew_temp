<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlayoutFile;
use App\Models\PlayoutLog;
use App\Models\AdTrp;

class PlayoutController extends Controller
{
    public function receive(Request $req)
    {
        $id=$req->id;
        
        $file=new PlayoutFile();
        $file->date=date("Y-m-d");
        $file->channel_id=$req->id;
        $file->save();
        

        foreach ($req->data as $logs) {
            $logs=(object)$logs;
            $log=new PlayoutLog();
            $log->channel_id=$id;
            $log->commercial_name=$logs->commercial_name;
            $log->program=$logs->program;
            $log->date=date("Y-m-d");
            $log->start=date("Y-m-d H:i:s",strtotime($logs->start));
            $finish= date("Y-m-d H:i:s",strtotime("+" . $logs->duration . " seconds", strtotime($logs->start)));
            $log->finish=$finish;
            $log->duration=abs(strtotime($log->start)-strtotime($log->finish));
            $log->playout_id=$file->id;
            $log->save();
            
            //return response()->json(["values" => $logs->duration], 200);
        }
        return response()->json(["values" => "done"], 200);
    }
}
