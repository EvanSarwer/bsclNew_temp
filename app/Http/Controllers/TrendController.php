<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use DateTime;

class TrendController extends Controller
{
    //
    public function rangedtrendreachp(Request $req)
    {
        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $startDateTime = date($startDate) . " " . $startTime;
        $finishDateTime = date($finishDate) . " " . $finishTime;
        $time = array();
        array_push($time, date("Y-m-d H:i:s", strtotime($startDateTime)));
        while (true) {

            $demotime = strtotime("+15 minutes", strtotime($time[count($time) - 1]));
            if ($demotime < strtotime($finishDateTime)) {
                array_push($time, date("Y-m-d H:i:s", $demotime));
            } else {
                break;
            }
        }
        //return response()->json(["time"=>$time],200);
        $reachs = array();
        $viewer = array();
        $users = User::all();
        $numOfUser = $users->count();
        $len = count($time) - 1;
        for ($i = 0; $i < $len; $i++) {

            $viewers = ViewLog::where('channel_id', $req->id)
                ->where(function ($query) use ($time, $i) {
                    $query->where('finished_watching_at', '>', date("Y-m-d H:i:s", strtotime($time[$i])))
                        ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at', '<', date("Y-m-d H:i:s", strtotime($time[$i + 1])))
                ->get();

            foreach ($viewers as $v) {
                array_push($viewer, $v->user->id);
            }
            $viewer = array_values(array_unique($viewer));
            $numofViewer = count($viewer);
            $reach = ($numofViewer / $numOfUser) * 100;
            unset($viewer);
            $viewer = array();

            array_push($reachs, $reach);
        }

        return response()->json(["values" => $reachs], 200);
    }
    //
    public function rangedtrendreach0(Request $req)
    {
        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $startDateTime = date($startDate) . " " . $startTime;
        $finishDateTime = date($finishDate) . " " . $finishTime;
        $time = array();
        array_push($time, date("Y-m-d H:i:s", strtotime($startDateTime)));
        while (true) {

            $demotime = strtotime("+15 minutes", strtotime($time[count($time) - 1]));
            if ($demotime < strtotime($finishDateTime)) {
                array_push($time, date("Y-m-d H:i:s", $demotime));
            } else {
                break;
            }
        }
        //return response()->json(["time"=>$time],200);
        $reachs = array();
        $viewer = array();
        $users = User::all();
        $numOfUser = $users->count();
        $len = count($time) - 1;
        for ($i = 0; $i < $len; $i++) {

            $viewers = ViewLog::where('channel_id', $req->id)
                ->where(function ($query) use ($time, $i) {
                    $query->where('finished_watching_at', '>', date("Y-m-d H:i:s", strtotime($time[$i])))
                        ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at', '<', date("Y-m-d H:i:s", strtotime($time[$i + 1])))
                ->get();

            foreach ($viewers as $v) {
                array_push($viewer, $v->user->id);
            }
            $viewer = array_values(array_unique($viewer));
            $numofViewer = count($viewer);
            $reach = $numofViewer; //($numofViewer / $numOfUser) * 100;
            unset($viewer);
            $viewer = array();

            array_push($reachs, $reach);
        }

        return response()->json(["values" => $reachs], 200);
    }
}
