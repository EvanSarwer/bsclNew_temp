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
        $data = DataCleanse::all();
        return response()->json($data);
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
}
