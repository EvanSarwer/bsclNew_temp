<?php

namespace App\Http\Controllers;
use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;

class ExcelController extends Controller
{
    public function reachp(Request $request){
        $values = [];
        foreach ($request->ranges as $req) {
        $startDate = substr($req["start"], 0, 10);
        $startTime = substr($req["start"], 11, 19);
        $finishDate = substr($req["finish"], 0, 10);
        $finishTime = substr($req["finish"], 11, 19);
        $startDateTime = date($startDate) . " " . $startTime;
        $finishDateTime = date($finishDate) . " " . $finishTime;

        $channels = Channel::all('id', 'channel_name');
        $total_user = User::count();

            $viewlogs = ViewLog::where('channel_id', $request->id)
                ->where(function($query) use ($startDateTime,$finishDateTime){
                    $query->where('finished_watching_at','>',$startDateTime)
                    ->orWhereNull('finished_watching_at');
                    })
                ->where('started_watching_at','<',$finishDateTime)
                ->distinct()->get('user_id');

            $user_count = $viewlogs->count();

            $user_count = ($user_count / $total_user) * 100 ;
            $user_count = round($user_count,1);
            array_push($values,$user_count);
            
        
    }
    return response()->json(["value"=>$values],200);
    }

    public function reach0(Request $request){
        $values = [];
        foreach ($request->ranges as $req) {
        $startDate = substr($req["start"], 0, 10);
        $startTime = substr($req["start"], 11, 19);
        $finishDate = substr($req["finish"], 0, 10);
        $finishTime = substr($req["finish"], 11, 19);
        $startDateTime = date($startDate) . " " . $startTime;
        $finishDateTime = date($finishDate) . " " . $finishTime;

        $channels = Channel::all('id', 'channel_name');
        $total_user = User::count();

            $viewlogs = ViewLog::where('channel_id', $request->id)
                ->where(function($query) use ($startDateTime,$finishDateTime){
                    $query->where('finished_watching_at','>',$startDateTime)
                    ->orWhereNull('finished_watching_at');
                    })
                ->where('started_watching_at','<',$finishDateTime)
                ->distinct()->get('user_id');

            $user_count = $viewlogs->count();

            //$user_count = ($user_count / $total_user) * 100 ;
            //$user_count = round($user_count,1);
            array_push($values,$user_count);
            
        
    }
    return response()->json(["value"=>$values],200);
    }
}
