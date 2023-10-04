<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataCleanse;
use App\Models\ViewLog;
use App\Models\ViewLogArchive;

class DataCleanseController extends Controller
{
    //
    function index(){
        $yesterday = date("Y-m-d", strtotime('-1 days'));
        $lastData = DataCleanse::latest('id')->first();
        if(!$lastData || $yesterday > $lastData->date){
            $newData=new DataCleanse();
            $newData->date = $yesterday;
            $newData->status = 0;
            $newData->save();
        }
        $lastUpdatedDate = DataCleanse::where('status',1)->latest('id')->first();
        $updatedData = DataCleanse::all();
        $lastUpdatedData = $updatedData->last(); 
        //return response()->json($lastUpdatedData->date);
        if($yesterday == $lastUpdatedData->date){
            return response()->json(["data"=>[$lastUpdatedData], "lastUpdatedDate"=>$lastUpdatedDate?->date],200);
        }
        
    }

    function cleaningData_Date(Request $req){
        $data = DataCleanse::where('id',$req->id)->first();
        $data->status = 1;
        $data->save();

        return redirect()->route('data.cleanse.alldates');
    }



    function getViewlog(Request $req){
        $data = ViewLog::where('id',$req->id)->first();
        return response()->json($data);
    }
    function cleanData(Request $req){
        $data = ViewLog::where('id',$req->id)->first();
        $arch=new ViewLogArchive();
        $arch->view_log_id = $data->id;
        $arch->channel_id = $data->channel_id;
        $arch->user_id = $data->user_id;
        $arch->started_watching_at = $data->started_watching_at;
        $arch->finished_watching_at = $data->finished_watching_at;
        $arch->duration_minute = $data->duration_minute;
        $arch->save();
        $data->delete();
        return response()->json("Cleaned");

    }

    public function lastCleanedDate(){
        $lastCleanedDate = DataCleanse::where('status',1)->latest('id')->first();
        return response()->json(["lastCleanedDate"=>$lastCleanedDate?->date],200);
    }
}
