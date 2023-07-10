<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use DateTime;

class OverviewController extends Controller
{
    //
    // public function __construct()
    // {
    //     $this->middleware('auth.admin');
    // }
    public function reachusergraph(Request $req)
    {
        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $startDateTime = date($startDate) . " " . $startTime;
        $finishDateTime = date($finishDate) . " " . $finishTime;

        $channels = Channel::all('id', 'channel_name');

        $number_of_user = [];
        $channel_label = [];
        $channel_id = [];
        if ($req->userType == "STB") {
            $minDate = Carbon::today()->subYears($req->age2 + 1); // make sure to use Carbon\Carbon in the class
            $maxDate = Carbon::today()->subYears($req->age1)->endOfDay();
            //return response()->json(["minDate" => $minDate, "maxDate" => $maxDate], 200);
            $userids = User::where('type', $req->userType)
                ->where('address', 'like', '%' . $req->region . '%')
                ->where('gender', 'like', '%' . $req->gender . '%')
                ->where('economic_status', 'like', '%' . $req->economic . '%')
                ->where('socio_status', 'like', '%' . $req->socio . '%')
                //->whereBetween('age', [$req->age1, $req->age2])
                ->whereBetween('dob', [$minDate, $maxDate])
                ->pluck('id')->toArray();
        } else if ($req->userType == "OTT") {
            $userids = User::where('type', $req->userType)
                ->pluck('id')->toArray();
        } else {
            $userids = User::pluck('id')->toArray();
        }
        $ram_logs = ViewLog::where('finished_watching_at', '>', $startDateTime)
            ->where('started_watching_at', '<', $finishDateTime)
            ->whereIn('user_id', $userids)
            ->get();
        //return response()->json(["reachsum" => $ram_logs], 200);
        foreach ($channels as $c) {

            $user_count = $ram_logs->where('channel_id', $c->id)

                ->pluck('user_id')->unique()->count();
            //return response()->json(["reachsum" => $viewlogs], 200);

            //$user_count = 0;



            array_push($channel_label, $c->channel_name);
            array_push($channel_id, $c->id);
            array_push($number_of_user, $user_count);
            $user_count = 0;
        }

        return response()->json(["reachsum" => array_sum($number_of_user), "reach" => $number_of_user, "channels" => $channel_label, "channel_ids" => $channel_id], 200);
    }
    public function reachpercentgraph(Request $req)
    {
        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $startDateTime = date($startDate) . " " . $startTime;
        $finishDateTime = date($finishDate) . " " . $finishTime;
        $total_user = User::count();
        $channels = Channel::all('id', 'channel_name');

        $number_of_user = [];
        $channel_label = [];
        $channel_id = [];
        if ($req->userType == "STB") {
            $minDate = Carbon::today()->subYears($req->age2 + 1); // make sure to use Carbon\Carbon in the class
            $maxDate = Carbon::today()->subYears($req->age1)->endOfDay();
            //return response()->json(["minDate" => $minDate, "maxDate" => $maxDate], 200);
            $userids = User::where('type', $req->userType)
                ->where('address', 'like', '%' . $req->region . '%')
                ->where('gender', 'like', '%' . $req->gender . '%')
                ->where('economic_status', 'like', '%' . $req->economic . '%')
                ->where('socio_status', 'like', '%' . $req->socio . '%')
                //->whereBetween('age', [$req->age1, $req->age2])
                ->whereBetween('dob', [$minDate, $maxDate])
                ->pluck('id')->toArray();
        } else if ($req->userType == "OTT") {
            $userids = User::where('type', $req->userType)
                ->pluck('id')->toArray();
        } else {
            $userids = User::pluck('id')->toArray();
        }
        $ram_logs = ViewLog::where('finished_watching_at', '>', $startDateTime)
            ->where('started_watching_at', '<', $finishDateTime)
            ->whereIn('user_id', $userids)
            ->get();
        //return response()->json(["reachsum" => $ram_logs], 200);
        foreach ($channels as $c) {

            $user_count = $ram_logs->where('channel_id', $c->id)

                ->pluck('user_id')->unique()->count();
            //return response()->json(["reachsum" => $viewlogs], 200);

            //$user_count = 0;



            $user_count = ($user_count / $total_user) * 100;
            $user_count = round($user_count, 1);
            array_push($channel_label, $c->channel_name);
            array_push($channel_id, $c->id);
            array_push($number_of_user, $user_count);
            $user_count = 0;
        }

        return response()->json(["reachsum" => array_sum($number_of_user), "reach" => $number_of_user, "channels" => $channel_label, "channel_ids" => $channel_id], 200);
    }
    public function tvrgraphallchannelzero(Request $req)
    {
        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $startDateTime = date($startDate) . " " . $startTime;
        $finishDateTime = date($finishDate) . " " . $finishTime;
        $numOfUser = User::count();
        //$numOfUser = $users->count();
        $channelArray = array();
        $channel_id = [];
        $tvrs = array();
        $viewer = array();
        $channels = Channel::all('id', 'channel_name');
        $to_time = strtotime($startDateTime);
        $from_time = strtotime($finishDateTime);

        $diff = abs($to_time - $from_time) / 60;
        $tvrs = array();
        $viewer = array();
        $number_of_user = [];
        $channel_label = [];
        $channel_id = [];
        if ($req->userType == "STB") {
            $minDate = Carbon::today()->subYears($req->age2 + 1); // make sure to use Carbon\Carbon in the class
            $maxDate = Carbon::today()->subYears($req->age1)->endOfDay();
            //return response()->json(["minDate" => $minDate, "maxDate" => $maxDate], 200);
            $userids = User::where('type', $req->userType)
                ->where('address', 'like', '%' . $req->region . '%')
                ->where('gender', 'like', '%' . $req->gender . '%')
                ->where('economic_status', 'like', '%' . $req->economic . '%')
                ->where('socio_status', 'like', '%' . $req->socio . '%')
                //->whereBetween('age', [$req->age1, $req->age2])
                ->whereBetween('dob', [$minDate, $maxDate])
                ->pluck('id')->toArray();
        } else if ($req->userType == "OTT") {
            $userids = User::where('type', $req->userType)
                ->pluck('id')->toArray();
        } else {
            $userids = User::pluck('id')->toArray();
        }
        $ram_logs = ViewLog::where('finished_watching_at', '>', $startDateTime)
            ->where('started_watching_at', '<', $finishDateTime)
            ->whereIn('user_id', $userids)
            ->get();
        //return response()->json(["reachsum" => $ram_logs], 200);
        foreach ($channels as $c) {

            $users = $ram_logs->where('channel_id', $c->id)

                ->toArray();
            //return response()->json(["reachsum" => $users], 200);

            //$user_count = 0;
            foreach ($users as $user) {

                $user = (object)$user;
                //return response()->json(["reachsum" => $user->started_watching_at], 200);
                if (((strtotime($user->started_watching_at)) < ($to_time)) && (((strtotime($user->finished_watching_at)) > ($from_time)) || (($user->finished_watching_at) == Null))) {
                    $watched_sec = abs($to_time - $from_time);
                } else if (((strtotime($user->started_watching_at)) < ($to_time)) && ((strtotime($user->finished_watching_at)) <= ($from_time))) {
                    $watched_sec = abs($to_time - strtotime($user->finished_watching_at));
                } else if (((strtotime($user->started_watching_at)) >= ($to_time)) && (((strtotime($user->finished_watching_at)) > ($from_time)) || (($user->finished_watching_at) == Null))) {
                    $watched_sec = abs(strtotime($user->started_watching_at) - $from_time);
                } else {
                    $watched_sec = abs(strtotime($user->finished_watching_at) - strtotime($user->started_watching_at));
                }
                //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                $watched_sec = $watched_sec / 60;
                array_push($viewer, $watched_sec);
            }


            $tvr = array_sum($viewer) / $numOfUser; ///$numOfUser;
            //return response()->json(["reachsum" => $tvr], 200);
            //$tvr=$tvr/60;
            $tvr = $tvr / $diff;
            //$tvr=$tvr*100;
            unset($viewer);
            $viewer = array();
            array_push($channelArray, $c->channel_name);
            array_push($tvrs, $tvr);
            array_push($channel_id, $c->id);
        }

        return response()->json(["tvrs" => $tvrs, "channels" => $channelArray, "channel_ids" => $channel_id], 200);
    }
    public function tvrgraphallchannelpercent(Request $req)
    {
        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $startDateTime = date($startDate) . " " . $startTime;
        $finishDateTime = date($finishDate) . " " . $finishTime;
        $numOfUser = User::count();
        //$numOfUser = $users->count();
        $channelArray = array();
        $channel_id = [];
        $tvrs = array();
        $viewer = array();
        $channels = Channel::all('id', 'channel_name');
        $to_time = strtotime($startDateTime);
        $from_time = strtotime($finishDateTime);

        $diff = abs($to_time - $from_time) / 60;
        $tvrs = array();
        $viewer = array();
        $number_of_user = [];
        $channel_label = [];
        $channel_id = [];
        if ($req->userType == "STB") {
            $minDate = Carbon::today()->subYears($req->age2 + 1); // make sure to use Carbon\Carbon in the class
            $maxDate = Carbon::today()->subYears($req->age1)->endOfDay();
            //return response()->json(["minDate" => $minDate, "maxDate" => $maxDate], 200);
            $userids = User::where('type', $req->userType)
                ->where('address', 'like', '%' . $req->region . '%')
                ->where('gender', 'like', '%' . $req->gender . '%')
                ->where('economic_status', 'like', '%' . $req->economic . '%')
                ->where('socio_status', 'like', '%' . $req->socio . '%')
                //->whereBetween('age', [$req->age1, $req->age2])
                ->whereBetween('dob', [$minDate, $maxDate])
                ->pluck('id')->toArray();
        } else if ($req->userType == "OTT") {
            $userids = User::where('type', $req->userType)
                ->pluck('id')->toArray();
        } else {
            $userids = User::pluck('id')->toArray();
        }
        $ram_logs = ViewLog::where('finished_watching_at', '>', $startDateTime)
            ->where('started_watching_at', '<', $finishDateTime)
            ->whereIn('user_id', $userids)
            ->get();
        //return response()->json(["reachsum" => $ram_logs], 200);
        foreach ($channels as $c) {

            $users = $ram_logs->where('channel_id', $c->id)

                ->toArray();
            //return response()->json(["reachsum" => $users], 200);

            //$user_count = 0;
            foreach ($users as $user) {

                $user = (object)$user;
                //return response()->json(["reachsum" => $user->started_watching_at], 200);
                if (((strtotime($user->started_watching_at)) < ($to_time)) && (((strtotime($user->finished_watching_at)) > ($from_time)) || (($user->finished_watching_at) == Null))) {
                    $watched_sec = abs($to_time - $from_time);
                } else if (((strtotime($user->started_watching_at)) < ($to_time)) && ((strtotime($user->finished_watching_at)) <= ($from_time))) {
                    $watched_sec = abs($to_time - strtotime($user->finished_watching_at));
                } else if (((strtotime($user->started_watching_at)) >= ($to_time)) && (((strtotime($user->finished_watching_at)) > ($from_time)) || (($user->finished_watching_at) == Null))) {
                    $watched_sec = abs(strtotime($user->started_watching_at) - $from_time);
                } else {
                    $watched_sec = abs(strtotime($user->finished_watching_at) - strtotime($user->started_watching_at));
                }
                //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                $watched_sec = $watched_sec / 60;
                array_push($viewer, $watched_sec);
            }


            $tvr = array_sum($viewer) / $numOfUser; ///$numOfUser;
            //return response()->json(["reachsum" => $tvr], 200);
            //$tvr=$tvr/60;
            $tvr = $tvr / $diff;
            $tvr=$tvr*100;
            unset($viewer);
            $viewer = array();
            array_push($channelArray, $c->channel_name);
            array_push($tvrs, $tvr);
            array_push($channel_id, $c->id);
        }

        return response()->json(["tvrs" => $tvrs, "channels" => $channelArray, "channel_ids" => $channel_id], 200);
    }

    public function reachusergraphs(Request $req)
    {
        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $startDateTime = date($startDate) . " " . $startTime;
        $finishDateTime = date($finishDate) . " " . $finishTime;

        $channels = Channel::all('id', 'channel_name');

        $number_of_user = [];
        $channel_label = [];
        $channel_id = [];

        foreach ($channels as $c) {

            $viewlogs = ViewLog::where('channel_id', $c->id)
                ->where(function ($query) use ($startDateTime, $finishDateTime) {
                    $query->where('finished_watching_at', '>', $startDateTime)
                        ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at', '<', $finishDateTime)
                ->distinct()->get('user_id');

            $user_count = 0;

            foreach ($viewlogs as $v) {

                if ($req->userType == "STB") {
                    $minDate = Carbon::today()->subYears($req->age2 + 1); // make sure to use Carbon\Carbon in the class
                    $maxDate = Carbon::today()->subYears($req->age1)->endOfDay();
                    //return response()->json(["minDate" => $minDate, "maxDate" => $maxDate], 200);
                    $user = User::where('id', $v->user_id)
                        ->where('type', $req->userType)
                        ->where('address', 'like', '%' . $req->region . '%')
                        ->where('gender', 'like', '%' . $req->gender . '%')
                        ->where('economic_status', 'like', '%' . $req->economic . '%')
                        ->where('socio_status', 'like', '%' . $req->socio . '%')
                        //->whereBetween('age', [$req->age1, $req->age2])
                        ->whereBetween('dob', [$minDate, $maxDate])
                        ->first();
                } else if ($req->userType == "OTT") {
                    $user = User::where('id', $v->user_id)
                        ->where('type', $req->userType)
                        ->first();
                } else {
                    $user = User::where('id', $v->user_id)
                        ->first();
                }




                // $user= User::where('id',$v->user_id)
                //         ->where('type','like','%'.$req->userType.'%')
                //         ->where('address','like','%'.$req->region.'%')
                //         ->where('gender','like','%'.$req->gender.'%')
                //         ->where('economic_status','like','%'.$req->economic.'%')
                //         ->where('socio_status','like','%'.$req->socio.'%')
                //         ->whereBetween('age',[$req->age1,$req->age2])
                //         ->first();
                if ($user) {
                    $user_count = $user_count + 1;
                } else {
                    continue;
                }
            }

            array_push($channel_label, $c->channel_name);
            array_push($channel_id, $c->id);
            array_push($number_of_user, $user_count);
        }

        return response()->json(["reachsum" => array_sum($number_of_user), "reach" => $number_of_user, "channels" => $channel_label, "channel_ids" => $channel_id], 200);
    }

    public function reachpercentgraphs(Request $req)
    {
        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $startDateTime = date($startDate) . " " . $startTime;
        $finishDateTime = date($finishDate) . " " . $finishTime;

        $channels = Channel::all('id', 'channel_name');
        $total_user = User::count();

        $number_of_user = [];
        $channel_label = [];
        $channel_id = [];

        foreach ($channels as $c) {
            $viewlogs = ViewLog::where('channel_id', $c->id)
                ->where(function ($query) use ($startDateTime, $finishDateTime) {
                    $query->where('finished_watching_at', '>', $startDateTime)
                        ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at', '<', $finishDateTime)
                ->distinct()->get('user_id');

            $user_count = 0;

            foreach ($viewlogs as $v) {
                if ($req->userType == "STB") {
                    $minDate = Carbon::today()->subYears($req->age2 + 1); // make sure to use Carbon\Carbon in the class
                    $maxDate = Carbon::today()->subYears($req->age1)->endOfDay();
                    //return response()->json(["minDate" => $minDate, "maxDate" => $maxDate], 200);
                    $user = User::where('id', $v->user_id)
                        ->where('type', $req->userType)
                        ->where('address', 'like', '%' . $req->region . '%')
                        ->where('gender', 'like', '%' . $req->gender . '%')
                        ->where('economic_status', 'like', '%' . $req->economic . '%')
                        ->where('socio_status', 'like', '%' . $req->socio . '%')
                        //->whereBetween('age', [$req->age1, $req->age2])
                        ->whereBetween('dob', [$minDate, $maxDate])
                        ->first();
                } else if ($req->userType == "OTT") {
                    $user = User::where('id', $v->user_id)
                        ->where('type', $req->userType)
                        ->first();
                } else {
                    $user = User::where('id', $v->user_id)
                        ->first();
                }


                // $user = User::where('id', $v->user_id)
                //     ->where('type', 'like', '%' . $req->userType . '%')
                //     ->where('address', 'like', '%' . $req->region . '%')
                //     ->where('gender', 'like', '%' . $req->gender . '%')
                //     ->where('economic_status', 'like', '%' . $req->economic . '%')
                //     ->where('socio_status', 'like', '%' . $req->socio . '%')
                //     ->whereBetween('age', [$req->age1, $req->age2])
                //     ->first();
                if ($user) {
                    $user_count = $user_count + 1;
                } else {
                    continue;
                }
            }
            $user_count = ($user_count / $total_user) * 100;
            $user_count = round($user_count, 1);
            array_push($channel_label, $c->channel_name);
            array_push($channel_id, $c->id);
            array_push($number_of_user, $user_count);
        }

        return response()->json(["reachsum" => array_sum($number_of_user), "reach" => $number_of_user, "channels" => $channel_label, "channel_ids" => $channel_id], 200);
    }
    // public function reachusergraph(Request $req){

    //     $startDate = substr($req->start, 0, 10);
    //     $startTime = substr($req->start, 11, 19);
    //     $finishDate = substr($req->finish, 0, 10);
    //     $finishTime = substr($req->finish, 11, 19);
    //     $channels = Channel::all()->filter(function ($c) use ($finishDate, $finishTime,$startDate,$startTime)
    //     {  return $c->reach( $startDate, $startTime, $finishDate,$finishTime) || !$c->channel_reach ;});


    //     $value = [];
    //     $label = [];
    //     foreach($channels as $c){
    //         $value[] = $c->channel_reach;
    //         $label[] = $c->channel_name;
    //     }
    //     return response()->json(["reachsum"=>array_sum($value),"reach"=>$value,"channels"=>$label],200);
    // }

    // public function reachpercentgraph(Request $req)
    // {

    //     $startDate = substr($req->start, 0, 10);
    //     $startTime = substr($req->start, 11, 19);
    //     $finishDate = substr($req->finish, 0, 10);
    //     $finishTime = substr($req->finish, 11, 19);
    //     $channels = Channel::all()->filter(function ($c) use ($finishDate, $finishTime,$startDate,$startTime)
    //     { return $c->reach( $startDate, $startTime, $finishDate,$finishTime) || !$c->channel_reach ;});


    //     $value = [];
    //     $label = [];
    //     foreach($channels as $c){
    //         $value[] = $c->channel_reach*100/Channel::count();
    //         $label[] = $c->channel_name;
    //     }
    //     return response()->json(["reachsum"=>array_sum($value),"reach"=>$value,"channels"=>$label],200);
    // }

    public function tvrgraphallchannelzeros(Request $req)
    {
        $channelArray = array();
        $channel_id = [];
        $tvrs = array();
        $viewer = array();

        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $startDateTime = date($startDate) . " " . $startTime;
        $finishDateTime = date($finishDate) . " " . $finishTime;

        $to_time = strtotime($startDateTime);
        $from_time = strtotime($finishDateTime);
        $diff = abs($to_time - $from_time) / 60;

        $channels = Channel::all('id', 'channel_name');
        $users = User::all();
        $numOfUser = $users->count();

        foreach ($channels as $c) {
            $viewers = ViewLog::where('channel_id', $c->id)
                ->where(function ($query) use ($startDateTime, $finishDateTime) {
                    $query->where('finished_watching_at', '>', $startDateTime)
                        ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at', '<', $finishDateTime)
                ->get();

            foreach ($viewers as $v) {

                if ($req->userType == "STB") {
                    $minDate = Carbon::today()->subYears($req->age2 + 1); // make sure to use Carbon\Carbon in the class
                    $maxDate = Carbon::today()->subYears($req->age1)->endOfDay();
                    //return response()->json(["minDate" => $minDate, "maxDate" => $maxDate], 200);
                    $user = User::where('id', $v->user_id)
                        ->where('type', $req->userType)
                        ->where('address', 'like', '%' . $req->region . '%')
                        ->where('gender', 'like', '%' . $req->gender . '%')
                        ->where('economic_status', 'like', '%' . $req->economic . '%')
                        ->where('socio_status', 'like', '%' . $req->socio . '%')
                        //->whereBetween('age', [$req->age1, $req->age2])
                        ->whereBetween('dob', [$minDate, $maxDate])
                        ->first();
                } else if ($req->userType == "OTT") {
                    $user = User::where('id', $v->user_id)
                        ->where('type', $req->userType)
                        ->first();
                } else {
                    $user = User::where('id', $v->user_id)
                        ->first();
                }


                // $user = User::where('id', $v->user_id)
                //     ->where('type', 'like', '%' . $req->userType . '%')
                //     ->where('address', 'like', '%' . $req->region . '%')
                //     ->where('gender', 'like', '%' . $req->gender . '%')
                //     ->where('economic_status', 'like', '%' . $req->economic . '%')
                //     ->where('socio_status', 'like', '%' . $req->socio . '%')
                //     ->whereBetween('age', [$req->age1, $req->age2])
                //     ->first();
                if ($user) {
                    if (((strtotime($v->started_watching_at)) < ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                        $watched_sec = abs($to_time - $from_time);
                    } else if (((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) <= ($from_time))) {
                        $watched_sec = abs($to_time - strtotime($v->finished_watching_at));
                    } else if (((strtotime($v->started_watching_at)) >= ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                        $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                    } else {
                        $watched_sec = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                    }
                    //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                    $watched_sec = $watched_sec / 60;
                    array_push($viewer, $watched_sec);
                } else {
                    continue;
                }
            }
            //return response()->json([$viewer],200);
            $tvr = array_sum($viewer) / $numOfUser; ///$numOfUser;
            //$tvr=$tvr/60;
            $tvr = $tvr / $diff;
            //$tvr=$tvr*100;
            unset($viewer);
            $viewer = array();
            array_push($channelArray, $c->channel_name);
            array_push($tvrs, $tvr);
            array_push($channel_id, $c->id);
        }

        return response()->json(["tvrs" => $tvrs, "channels" => $channelArray, "channel_ids" => $channel_id], 200);
        //return response()->json(["tvr"=>$tvr],200);

    }
    public function tvrgraphallchannelpercents(Request $req)
    {
        $channelArray = array();
        $channel_id = [];
        $tvrs = array();
        $viewer = array();

        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $startDateTime = date($startDate) . " " . $startTime;
        $finishDateTime = date($finishDate) . " " . $finishTime;

        $to_time = strtotime($startDateTime);
        $from_time = strtotime($finishDateTime);
        $diff = abs($to_time - $from_time) / 60;

        $channels = Channel::all('id', 'channel_name');
        $users = User::all();
        $numOfUser = $users->count();

        foreach ($channels as $c) {
            $viewers = ViewLog::where('channel_id', $c->id)
                ->where(function ($query) use ($startDateTime, $startDate, $startTime) {
                    $query->where('finished_watching_at', '>', $startDateTime)
                        ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at', '<', $finishDateTime)
                ->get();

            foreach ($viewers as $v) {

                if ($req->userType == "STB") {
                    $minDate = Carbon::today()->subYears($req->age2 + 1); // make sure to use Carbon\Carbon in the class
                    $maxDate = Carbon::today()->subYears($req->age1)->endOfDay();
                    //return response()->json(["minDate" => $minDate, "maxDate" => $maxDate], 200);
                    $user = User::where('id', $v->user_id)
                        ->where('type', $req->userType)
                        ->where('address', 'like', '%' . $req->region . '%')
                        ->where('gender', 'like', '%' . $req->gender . '%')
                        ->where('economic_status', 'like', '%' . $req->economic . '%')
                        ->where('socio_status', 'like', '%' . $req->socio . '%')
                        //->whereBetween('age', [$req->age1, $req->age2])
                        ->whereBetween('dob', [$minDate, $maxDate])
                        ->first();
                } else if ($req->userType == "OTT") {
                    $user = User::where('id', $v->user_id)
                        ->where('type', $req->userType)
                        ->first();
                } else {
                    $user = User::where('id', $v->user_id)
                        ->first();
                }



                // $user = User::where('id', $v->user_id)
                //     ->where('type', 'like', '%' . $req->userType . '%')
                //     ->where('address', 'like', '%' . $req->region . '%')
                //     ->where('gender', 'like', '%' . $req->gender . '%')
                //     ->where('economic_status', 'like', '%' . $req->economic . '%')
                //     ->where('socio_status', 'like', '%' . $req->socio . '%')
                //     ->whereBetween('age', [$req->age1, $req->age2])
                //     ->first();
                if ($user) {
                    if (((strtotime($v->started_watching_at)) < ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                        $watched_sec = abs($to_time - $from_time);
                    } else if (((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) <= ($from_time))) {
                        $watched_sec = abs($to_time - strtotime($v->finished_watching_at));
                    } else if (((strtotime($v->started_watching_at)) >= ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                        $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                    } else {
                        $watched_sec = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                    }
                    $watched_sec = $watched_sec / 60;
                    array_push($viewer, $watched_sec);
                } else {
                    continue;
                }
            }
            $tvr = array_sum($viewer) / $numOfUser;
            //$tvr=$tvr/60;
            $tvr = $tvr / $diff;
            $tvr = $tvr * 100;
            unset($viewer);
            $viewer = array();
            array_push($channelArray, $c->channel_name);
            array_push($tvrs, $tvr);
            array_push($channel_id, $c->id);
        }
        return response()->json(["tvrs" => $tvrs, "channels" => $channelArray, "channel_ids" => $channel_id], 200);
    }

    public function tvrsharegraph(Request $req)
    {

        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $to_time = strtotime($startDate . " " . $startTime);
        $from_time = strtotime($finishDate . " " . $finishTime);
        $diff = abs($to_time - $from_time) / 60;
        $users = User::all();
        $numOfUser = $users->count();

        $channelArray = array();
        $channel_id = [];
        $shares = array();
        $all_tvr = array();

        $channels = Channel::all('id', 'channel_name');
        foreach ($channels as $c) {
            $tvr = 0;
            $viewelogs = ViewLog::where('channel_id', $c->id)
                ->where(function ($query) use ($finishDate, $finishTime, $startDate, $startTime) {
                    $query->where('finished_watching_at', '>', date($startDate) . " " . $startTime)
                        ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at', '<', date($finishDate) . " " . $finishTime)
                ->get();
            $total_time_viewed = 0;
            foreach ($viewelogs as $v) {

                if ($req->userType == "STB") {
                    $minDate = Carbon::today()->subYears($req->age2 + 1); // make sure to use Carbon\Carbon in the class
                    $maxDate = Carbon::today()->subYears($req->age1)->endOfDay();
                    //return response()->json(["minDate" => $minDate, "maxDate" => $maxDate], 200);
                    $user = User::where('id', $v->user_id)
                        ->where('type', $req->userType)
                        ->where('address', 'like', '%' . $req->region . '%')
                        ->where('gender', 'like', '%' . $req->gender . '%')
                        ->where('economic_status', 'like', '%' . $req->economic . '%')
                        ->where('socio_status', 'like', '%' . $req->socio . '%')
                        //->whereBetween('age', [$req->age1, $req->age2])
                        ->whereBetween('dob', [$minDate, $maxDate])
                        ->first();
                } else if ($req->userType == "OTT") {
                    $user = User::where('id', $v->user_id)
                        ->where('type', $req->userType)
                        ->first();
                } else {
                    $user = User::where('id', $v->user_id)
                        ->first();
                }

                // $user = User::where('id', $v->user_id)
                //     ->where('type', 'like', '%' . $req->userType . '%')
                //     ->where('address', 'like', '%' . $req->region . '%')
                //     ->where('gender', 'like', '%' . $req->gender . '%')
                //     ->where('economic_status', 'like', '%' . $req->economic . '%')
                //     ->where('socio_status', 'like', '%' . $req->socio . '%')
                //     ->whereBetween('age', [$req->age1, $req->age2])
                //     ->first();
                if ($user) {
                    if (((strtotime($v->started_watching_at)) < ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                        $watched_sec = abs($to_time - $from_time);
                    } else if (((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) <= ($from_time))) {
                        $watched_sec = abs($to_time - strtotime($v->finished_watching_at));
                    } else if (((strtotime($v->started_watching_at)) >= ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                        $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                    } else {
                        $watched_sec = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                    }
                    $total_time_viewed = $total_time_viewed + $watched_sec;
                    //$timeviewed = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at))/60;
                } else {
                    continue;
                }
            }
            $total_time_viewed = ($total_time_viewed) / 60;
            $tvr = $total_time_viewed / $diff;
            $tvr = $tvr / $numOfUser;
            $tvr = $tvr * 100;
            $tvr = round($tvr, 4);

            array_push($all_tvr, $tvr);
            array_push($channelArray, $c->channel_name);
            array_push($channel_id, $c->id);
        }
        $total_tvr = array_sum($all_tvr);
        $total_tvr = round($total_tvr, 5);

        $total_share = 0;
        for ($i = 0; $i < count($all_tvr); $i++) {
            if ($total_tvr != 0) {
                $s = ($all_tvr[$i] / $total_tvr) * 100;
            } else {
                $s = 0;
            }
            //$s = ($all_tvr[$i] / $total_tvr) * 100;
            $total_share = $total_share + $s;
            array_push($shares, $s);
        }
        //return response()->json(["Total-tvr"=>$total_tvr,"all_tvr"=>$all_tvr,"total_share"=>$total_share,"share"=>$shares,"channels"=>$channelArray],200);
        return response()->json(["share" => $shares, "channels" => $channelArray, "channel_ids" => $channel_id], 200);
    }

    public function timespentgraph(Request $req)
    {
        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $startDateTime = date($startDate) . " " . $startTime;
        $finishDateTime = date($finishDate) . " " . $finishTime;

        $to_time = strtotime($startDateTime);
        $from_time = strtotime($finishDateTime);

        $channelArray = array();
        $channel_id = [];
        $total_time = array();
        $total = 0.00;

        $channels = Channel::all('id', 'channel_name');
        foreach ($channels as $c) {
            $viewlogs = ViewLog::where('channel_id', $c->id)
                ->where(function ($query) use ($finishDateTime, $startDateTime) {
                    $query->where('finished_watching_at', '>', $startDateTime)
                        ->orWhereNull('finished_watching_at');
                })
                ->where('started_watching_at', '<', $finishDateTime)
                ->get();
            $total_time_viewed = 0;
            foreach ($viewlogs as $v) {

                if ($req->userType == "STB") {
                    $minDate = Carbon::today()->subYears($req->age2 + 1); // make sure to use Carbon\Carbon in the class
                    $maxDate = Carbon::today()->subYears($req->age1)->endOfDay();
                    //return response()->json(["minDate" => $minDate, "maxDate" => $maxDate], 200);
                    $user = User::where('id', $v->user_id)
                        ->where('type', $req->userType)
                        ->where('address', 'like', '%' . $req->region . '%')
                        ->where('gender', 'like', '%' . $req->gender . '%')
                        ->where('economic_status', 'like', '%' . $req->economic . '%')
                        ->where('socio_status', 'like', '%' . $req->socio . '%')
                        //->whereBetween('age', [$req->age1, $req->age2])
                        ->whereBetween('dob', [$minDate, $maxDate])
                        ->first();
                } else if ($req->userType == "OTT") {
                    $user = User::where('id', $v->user_id)
                        ->where('type', $req->userType)
                        ->first();
                } else {
                    $user = User::where('id', $v->user_id)
                        ->first();
                }

                // $user = User::where('id', $v->user_id)
                //     ->where('type', 'like', '%' . $req->userType . '%')
                //     ->where('address', 'like', '%' . $req->region . '%')
                //     ->where('gender', 'like', '%' . $req->gender . '%')
                //     ->where('economic_status', 'like', '%' . $req->economic . '%')
                //     ->where('socio_status', 'like', '%' . $req->socio . '%')
                //     ->whereBetween('age', [$req->age1, $req->age2])
                //     ->first();
                if ($user) {
                    if (((strtotime($v->started_watching_at)) < ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                        $watched_sec = abs($to_time - $from_time);
                    } else if (((strtotime($v->started_watching_at)) < ($to_time)) && ((strtotime($v->finished_watching_at)) <= ($from_time))) {
                        $watched_sec = abs($to_time - strtotime($v->finished_watching_at));
                    } else if (((strtotime($v->started_watching_at)) >= ($to_time)) && (((strtotime($v->finished_watching_at)) > ($from_time)) || (($v->finished_watching_at) == Null))) {
                        $watched_sec = abs(strtotime($v->started_watching_at) - $from_time);
                    } else {
                        $watched_sec = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                    }
                    $total_time_viewed = $total_time_viewed + $watched_sec;
                    //$timeviewed = abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at))/60;
                } else {
                    continue;
                }
            }
            $total_time_viewed = ($total_time_viewed) / 60;
            $total_time_viewed = round($total_time_viewed);

            array_push($total_time, $total_time_viewed);
            array_push($channelArray, $c->channel_name);
            array_push($channel_id, $c->id);
        }
        return response()->json(["totaltime" => $total_time, "channels" => $channelArray, "channel_ids" => $channel_id], 200);
    }
}
