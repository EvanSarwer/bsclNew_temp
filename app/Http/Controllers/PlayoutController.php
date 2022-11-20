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
        $ids=array();
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
            $log->date=date("Y-m-d");
            $log->start=date("Y-m-d H:i:s",strtotime($logs->start));
            $finish= date("Y-m-d H:i:s",strtotime("+" . $logs->duration . " seconds", strtotime($logs->start)));
            $log->finish=$finish;
            $log->duration=abs(strtotime($log->start)-strtotime($log->finish));
            $log->file_id=$file->id;
            $log->save();
            array_push($ids,$log->id);
            
            //return response()->json(["values" => $logs->duration], 200);
        }
        if(count($req->data)!=count($ids)){
            PlayoutLog::whereIn('id', $ids)->delete();
            PlayoutFile::where('id', $file->id)->delete();
            return response()->json(["error" =>"Faulty Data" ], 422);
        }
        return response()->json(["status" => "done"], 200);
    }
}
