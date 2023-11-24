<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlayoutFile;
use App\Models\PlayoutLog;
use App\Models\AdLog;
use App\Models\ProgramLog;
use App\Models\AdTrp;

class PlayoutController extends Controller
{
    public function adlog(Request $req)
    {
        $ids=array();
        foreach ($req->data as $logs) {
            $logs=(object)$logs;
            $log=new AdLog();
            //`ad_date`, `channel_id`, `company`, `ad_type`, `peak`, `telecast_time`, `duration`, `start`, `finish`, `ad_name`, `brand`, `sub_brand`, `product_type`, `product`, `program_type`, `program_name`, `break_type`, `ad_qty`, `ad_pos`, `campaign`, `ad_price`, `incomplete_ad`
            $log->ad_date=date("Y-m-d",strtotime($logs->ad_date));
            $log->channel_id=$logs->channel_id;
            $log->company=$logs->company;
            $log->ad_type=$logs->ad_type;
            $log->peak=$logs->peak;
            $log->telecast_time=$logs->telecast_time;
            $log->duration=$logs->duration;
            $log->start=date("Y-m-d H:i:s",strtotime($logs->start));
            $log->finish=date("Y-m-d H:i:s",strtotime($logs->finish));
            $log->ad_name=$logs->ad_name;
            $log->brand=$logs->brand;
            $log->sub_brand=$logs->sub_brand;
            $log->product_type=$logs->product_type;
            $log->product=$logs->product;
            $log->program_type=$logs->program_type;
            $log->program_name=$logs->program_name;
            $log->break_type=$logs->break_type;
            $log->ad_qty=$logs->ad_qty;
            $log->ad_pos=$logs->ad_pos;
            $log->campaign=(isset($logs->campaign))?$logs->campaign:null;
            $log->incomplete_ad=(isset($logs->incomplete_ad))?$logs->incomplete_ad:null;//$logs->campaign;
            $log->ad_price=$logs->ad_price;
            

            $log->save();
            array_push($ids,$log->id);
            
            //return response()->json(["values" => $logs->duration], 200);
        }
        if(count($req->data)!=count($ids)){
            AdLog::whereIn('id', $ids)->delete();
            return response()->json(["error" =>"Faulty Data" ], 422);
        }
        return response()->json(["status" => "done"], 200);
    }
    public function programlog(Request $req)
    {
        /*
        program_date:json[i].program_date,
            week_no:json[i].Week_No,
            day:json[i].Day,
            channel_id:getIdFromChannelName(json[i]['Channel Name']),
            peak_offpeak:json[i].peak_offpeak,
            start:json[i].start,
            finish:json[i].finish,
            program_duration_min:json[i].program_duration_min,
            program_type_genre:json[i].program_type_genre,
            program_name:json[i].Program_Name,
            language:json[i].Language
            */
        $ids=array();
        foreach ($req->data as $logs) {
            $logs=(object)$logs;
            $log=new ProgramLog();
            $log->program_date=date("Y-m-d",strtotime($logs->program_date));
            $log->week_no=$logs->week_no;
            $log->day=$logs->day;
            $log->channel_id=$logs->channel_id;
            $log->peak_offpeak=$logs->peak_offpeak;
            $log->start=date("Y-m-d H:i:s",strtotime($logs->start));
            $log->finish=date("Y-m-d H:i:s",strtotime($logs->finish));
            $log->program_duration_min=$logs->program_duration_min;
            $log->program_type_genre=$logs->program_type_genre;
            $log->program_name=$logs->program_name;
            $log->language=$logs->language;
            $log->save();
            array_push($ids,$log->id);
            
            //return response()->json(["values" => $logs->duration], 200);
        }
        if(count($req->data)!=count($ids)){
            ProgramLog::whereIn('id', $ids)->delete();
            return response()->json(["error" =>"Faulty Data" ], 422);
        }
        return response()->json(["status" => "done"], 200);
    }
    public function receive(Request $req)
    {
        $ids=array();
        
        
        

        foreach ($req->data as $logs) {
            $logs=(object)$logs;
            $log=new PlayoutLog();
            $log->commercial_name=$logs->commercial_name;
            $log->date=date("Y-m-d");
            $log->start=date("Y-m-d H:i:s",strtotime($logs->start));
            $finish= date("Y-m-d H:i:s",strtotime("+" . $logs->duration . " seconds", strtotime($logs->start)));
            $log->finish=$finish;
            $log->duration=abs(strtotime($log->start)-strtotime($log->finish));
            
            $log->save();
            array_push($ids,$log->id);
            
            //return response()->json(["values" => $logs->duration], 200);
        }
        if(count($req->data)!=count($ids)){
            PlayoutLog::whereIn('id', $ids)->delete();
            return response()->json(["error" =>"Faulty Data" ], 422);
        }
        return response()->json(["status" => "done"], 200);
    }
}
