<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\DeselectPeriod;
use App\Models\Device;
use App\Models\User;
use Carbon\Carbon;
use DateTime;

class LiveChannelController extends Controller
{
    //
    // public function __construct()
    // {
    //     $this->middleware('auth.admin');
    // }

    public function activechannellistgraph(Request $req)
    {
        $channels = Channel::all();
        $activeChannels = [];
        $number_of_user = [];
        $points = [];
        if (count($channels) > 0) {
            foreach ($channels as $c) {
                $viewlogs = ViewLog::where('channel_id', $c->id)
                    ->whereNull('finished_watching_at')->get();
                $user_count = 0;
                // if(count($viewlogs) > 0){
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
                        $uu = array("id" => $user->device->id, "title" => $user->device->device_name, "lat" => $user->device->lat, "lng" => $user->device->lng);
                        array_push($points, $uu);
                    } else {
                        continue;
                    }
                }
                // if($user_count > 0){
                // $activeChannel =[
                //     "channel_id" => $c->id,
                //     "channel_name" => $c->channel_name,
                //     "channel_logo" => $c->logo,
                //     "user_count" => $user_count
                // ];
                array_push($activeChannels, $c->channel_name);
                array_push($number_of_user, $user_count);
                // }

                // }

            }
        }

        //return response()->json(["points" => $points], 200);
        return response()->json(["channels" => $activeChannels, "user_count" => $number_of_user, "points" => $points], 200);
    }

    public function activechannellistgraphfast(Request $req)
    {
        $channels = Channel::all();
        $activeChannels = [];
        $number_of_user = [];
        $allusers = [];
        $time = 45;

        if ($req->userType == "STB") {
            $minDate = Carbon::today()->subYears($req->age2 + 1); // make sure to use Carbon\Carbon in the class
            $maxDate = Carbon::today()->subYears($req->age1)->endOfDay();

            $users = User::join('devices', 'devices.id', '=', 'users.device_id')->where('users.last_request', '>', date('Y-m-d H:i:s', (time() - $time)))->where('users.tvoff', 1)
                ->select('users.id', 'users.user_name', 'devices.device_name as title', 'devices.id as device_id', 'devices.lat', 'devices.lng')
                ->where('users.type', $req->userType)
                ->where('users.address', 'like', '%' . $req->region . '%')
                ->where('users.gender', 'like', '%' . $req->gender . '%')
                ->where('users.economic_status', 'like', '%' . $req->economic . '%')
                ->where('users.socio_status', 'like', '%' . $req->socio . '%')
                ->whereBetween('users.dob', [$minDate, $maxDate])
                ->get();
        } else if ($req->userType == "OTT") {
            $users = User::join('devices', 'devices.id', '=', 'users.device_id')->where('users.last_request', '>', date('Y-m-d H:i:s', (time() - $time)))->where('users.tvoff', 1)
                ->select('users.id', 'users.user_name',  'devices.device_name as title', 'devices.id as device_id', 'devices.lat', 'devices.lng')
                ->where('users.type', $req->userType)
                ->get();
        } else {
            $users = User::join('devices', 'devices.id', '=', 'users.device_id')->where('users.last_request', '>', date('Y-m-d H:i:s', (time() - $time)))->where('users.tvoff', 1)
                ->select('users.id', 'users.user_name',  'devices.device_name as title', 'devices.id as device_id', 'devices.lat', 'devices.lng')
                ->get();
        }

        foreach ($channels as $c) {
            $u_count = array();
            foreach ($users as $u) {
                $view_log = ViewLog::where('user_id', $u->id)->latest('id')->first();
                if ($view_log) {
                    if ($c->id == $view_log->channel_id) {
                        array_push($u_count, $u->user_id);
                    }
                }
            }
            array_push($number_of_user, count($u_count));
            $allusers = array_merge($allusers, $u_count);
            array_push($activeChannels, $c->channel_name);
            unset($u_count);
        }

        return response()->json(["channels" => $activeChannels, "user_count" => $number_of_user, "points" => $users], 200);
    }
    public function activedevicelistmap()
    {
        $User_deSelected = DeselectPeriod::whereNotNull('start_date')->whereNull('end_date')->pluck('device_id')->toArray();
        $User_selected = Device::whereNotNull('contact_person')
            ->whereNotNull('contact_email')
            ->whereNotNull('contact_number')
            ->whereNotIn('id', $User_deSelected)
            ->pluck('Id')
            ->toArray();

        $green = Device::where('last_request', '>', date('Y-m-d H:i:s', strtotime('-120 seconds')))
            ->whereIN('id', $User_selected)
            ->select('id', 'device_name as title',  'lat', 'lng')
            ->get();
        $red = Device::where('last_request', '<', date('Y-m-d H:i:s', strtotime('-15 days')))
            ->whereIN('id', $User_selected)
            ->select('id', 'device_name as title',  'lat', 'lng')
            ->get();

        $gray = Device::where('last_request', '<=', date('Y-m-d H:i:s', strtotime('-120 seconds')))
            ->where('last_request', '>=', date('Y-m-d H:i:s', strtotime('-15 days')))
            ->whereIN('id', $User_selected)
            ->select('id', 'device_name as title',  'lat', 'lng')
            ->get();
        return response()->json(["red" => $red, "green" => $green, "gray" => $gray], 200);
    }


    // public function activechannellistgraphfast(Request $req)
    // {

    //     //}

    //     $channels = Channel::all();
    //     $activeChannels = [];
    //     $number_of_user = [];
    //     $points = [];
    //     $allusers = [];

    //     if ($req->userType == "STB") {
    //         $minDate = Carbon::today()->subYears($req->age2 + 1); // make sure to use Carbon\Carbon in the class
    //         $maxDate = Carbon::today()->subYears($req->age1)->endOfDay();
    //         //return response()->json(["minDate" => $minDate, "maxDate" => $maxDate], 200);
    //         $userids = User::where('type', $req->userType)
    //             ->where('address', 'like', '%' . $req->region . '%')
    //             ->where('gender', 'like', '%' . $req->gender . '%')
    //             ->where('economic_status', 'like', '%' . $req->economic . '%')
    //             ->where('socio_status', 'like', '%' . $req->socio . '%')
    //             //->whereBetween('age', [$req->age1, $req->age2])
    //             ->whereBetween('dob', [$minDate, $maxDate])
    //             ->pluck('id')->toArray();
    //     } else if ($req->userType == "OTT") {
    //         $userids = User::where('type', $req->userType)
    //             ->pluck('id')->toArray();
    //     } else {
    //         $userids = User::pluck('id')->toArray();
    //     }
    //     //return response()->json(["user" => $userids], 200);
    //     //if (count($channels) > 0) {
    //     //foreach ($channels as $c) {
    //     $users = ViewLog:: //where('channel_id', $c->id)
    //         //->
    //         whereNull('finished_watching_at')
    //         ->whereIn('user_id', $userids)
    //         //->pluck('user_id')->toArray();
    //         //select('user_id','channel_id')
    //         ->get();
    //     foreach ($channels as $c) {
    //         $u_count = array();
    //         foreach ($users as $value) {
    //             if ($c->id == $value->channel_id) {
    //                 array_push($u_count, $value->user_id);
    //             }
    //         }
    //         array_push($number_of_user, count($u_count));
    //         $allusers = array_merge($allusers, $u_count);
    //         array_push($activeChannels, $c->channel_name);
    //         unset($u_count);
    //     }
    //     //return response()->json(["user" => $users], 200);
    //     //$user_count = count($users);

    //     //$allusers = array_merge($allusers, $users);
    //     //array_push($activeChannels, 'channel_name');
    //     //array_push($number_of_user, $user_count);


    //     //}
    //     $points = User::select('devices.id', 'devices.device_name as title', 'devices.lat', 'devices.lng')->whereIn('users.id', $allusers)
    //         ->join('devices', 'devices.id', '=', 'users.device_id')
    //         ->get();
    //     //}

    //     return response()->json(["channels" => $activeChannels, "user_count" => $number_of_user, "points" => $points], 200);
    // }
}
