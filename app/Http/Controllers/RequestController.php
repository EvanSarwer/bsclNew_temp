<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\ViewLog;
use Illuminate\Http\Request;
use DateTime;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Universe;
use App\Models\Category;
use App\Models\Device;
use App\Models\Channel;
use App\Models\DataReliability;
use App\Models\DeselectPeriod;
use App\Models\DeselectLog;
use App\Models\DeviceBox;
use App\Models\Notification;
use App\Models\RawRequest;
use App\Models\TempData;

class RequestController extends Controller
{

    public function receiveReliabilityLog(Request $request)
    {
        $dr = new DataReliability();
        //$data = $request->data;
        $dr->data = $request->data; //json_encode($data);
        $dr->time = Carbon::now()->toDateTimeString();;
        $dr->save();

        return response()->json(["response" => "done", "data" => $request->data], 200);
    }


    public function receiveoutside(Request $request)
    {
        $td = new TempData();
        $data = $request->data;
        $td->data = json_encode($data);
        $td->time = Carbon::now()->toDateTimeString();;
        $td->save();


        //return response()->json(["msg" => "ok received", "your_data"=>$request->data], 200);


        //return response()->json(["value" => $request[1]['user']], 200);
        foreach ($request->data as $req) {
            $user = User::where('user_name', 'like', '%' . $req['user'] . '%')->first();
            //return response()->json(["value" => $user->id], 200);
            // $viewlogp = Viewlog::where('started_watching_at', $req['start'])
            //     ->where('finished_watching_at', $req['finish'])
            //     ->where('channel_id', $req['channel_id'])
            //     ->where('user_id', $user->id)
            //     ->first();
            // if (!$viewlogp) {
            $var = new ViewLog;
            //$var->id=5010;
            $var->user_id = $user->id;
            $var->channel_id = $req['channel_id'];
            $var->started_watching_at = $req['start'];
            $var->finished_watching_at = $req['finish'];
            $var->duration_minute = abs(strtotime($req['start']) - strtotime($req['finish'])) / 60;
            $var->save();

            //$user->tvoff = 1;
            //$user->save();
            //}
        }
        return response()->json(["response" => "done"], 200);
    }

    //
    // public function receive(Request $req)
    // {
    //     if ($req->people != null) {
    //         $devices = Device::where('id', $req->device_id)->first();
    //         //return response()->json(["dd"=>$devices],200);
    //         $arr=array();
    //         if ($devices) {
    //             for ($i = 0; $i < 8; $i++) {
    //                 $index = User::where('device_id', $req->device_id)->where('user_index', $i)->first();
    //                 //array_push($arr,$index);
    //                 //return response()->json(["ranges" => $index], 200);
    //                 if ($index) {
    //                     if ($req->people[$i] === '1') {
    //                         $ob = array("device_id" => $index->id, "channel_name" => $channel_id, "time_stamp" => $req->time_stamp);
    //                         //array_push($user, (object)$ob);
    //                         $this->receiver((object)$ob);
    //                         //array_push($arr,$index);
    //                     } else {
    //                         $ob = array("device_id" => $index->id, "channel_name" => 999, "time_stamp" => $req->time_stamp);
    //                         //array_push($user, (object)$ob);
    //                         $this->receiver((object)$ob);
    //                     }
    //                 }
    //             }
    //         }
    //     } else {
    //         $this->receiver($req);
    //     }
    // }




    public function receive(Request $req)
    {
        //return response()->json(["values" => "kk1"], 200);

        $rr = new RawRequest();
        $rr->channel_id = $req->channel_name;
        $rr->device_id = $req->device_id;
        $rr->start = $req->start;
        $rr->finish = $req->finish;
        $rr->people = $req->people;
        $rr->offline = $req->offline;
        $rr->temp = $req->temp;
        $rr->error = $req->error;
        $rr->server_time = Carbon::now()->toDateTimeString();;
        $rr->save();
        if ((strtotime($req->start) >= strtotime($req->finish)) ||
            (strtotime($req->start) < strtotime("2020-01-01 00:00:00")) || (strtotime($req->finish) < strtotime("2020-01-01 00:00:00"))
            || (abs(strtotime($req->start) - strtotime($req->finish)) > 3600)
        ) {
            return;
        }

        $channel_id = $req->channel_name;
        if ($req->channel_name >= 40 && $req->channel_name <= 888) {
            $channel_id = 888;
        }

        $hasDeviceBox = DeviceBox::where('id', $req->device_id)->first();
        if ($hasDeviceBox && $hasDeviceBox->device != null){

            $last_req_time = Carbon::now()->toDateTimeString();
            $this->updateDeviceLastReq($hasDeviceBox->device->id, $last_req_time);

            $hasChannel = Channel::where('id', $channel_id)->first();

            if (($channel_id == 999)) {
                $hasDeviceBox->device->tvoff = 0;
                $hasDeviceBox->device->save();
                //$users = 
                User::where('device_id', $hasDeviceBox->device->id)->update(['tvoff' => 0]);
                // foreach ($users as $i) {
                //     $i->tvoff = 0;
                //     $i->save();
                // }



            } else if (($hasChannel && $channel_id != 999)) {
                $hasDeviceBox->device->tvoff = 1;
                $hasDeviceBox->device->save();
                User::where('device_id', $hasDeviceBox->device->id)->update(['tvoff' => 1]);
                for ($i = 0; $i < strlen($req->people); $i++) {
                    $index = User::where('device_id', $hasDeviceBox->device->id)->where('user_index', $i)->first();
                    //array_push($arr,$index);
                    //return response()->json(["ranges" => $index], 200);
                    if ($index) {
                        if ($req->people[$i] === '1') {

                            $ob = array("device_id" => $hasDeviceBox->device->id, "user_id" => $index->id, "channel_name" => $channel_id, "start" => $req->start, "finish" => $req->finish, "error_msg" => $req->error);
                            $this->receiver((object)$ob);
                        }
                    }
                }
            }


            if ($req->temp && (substr($req->temp, 0, -2)) > 80) {
                $this->check_temperature($hasDeviceBox->device->id, $hasDeviceBox->device->device_name, $req->temp);
            }

        }

        // $hasDevice = Device::where('id', $req->device_id)->first();
        // if ($hasDevice) {
        //     $last_req_time = Carbon::now()->toDateTimeString();
        //     $this->updateDeviceLastReq($req->device_id, $last_req_time);

        //     $hasChannel = Channel::where('id', $channel_id)->first();

        //     if (($channel_id == 999)) {
        //         $hasDevice->tvoff = 0;
        //         $hasDevice->save();
        //         //$users = 
        //         User::where('device_id', $req->device_id)->update(['tvoff' => 0]);
        //         // foreach ($users as $i) {
        //         //     $i->tvoff = 0;
        //         //     $i->save();
        //         // }



        //     } else if (($hasChannel && $channel_id != 999)) {
        //         $hasDevice->tvoff = 1;
        //         $hasDevice->save();
        //         User::where('device_id', $req->device_id)->update(['tvoff' => 1]);
        //         for ($i = 0; $i < strlen($req->people); $i++) {
        //             $index = User::where('device_id', $req->device_id)->where('user_index', $i)->first();
        //             //array_push($arr,$index);
        //             //return response()->json(["ranges" => $index], 200);
        //             if ($index) {
        //                 if ($req->people[$i] === '1') {

        //                     $ob = array("device_id" => $req->device_id, "user_id" => $index->id, "channel_name" => $channel_id, "start" => $req->start, "finish" => $req->finish, "error_msg" => $req->error);
        //                     $this->receiver((object)$ob);
        //                 }
        //             }
        //         }
        //     }


        //     if ($req->temp && (substr($req->temp, 0, -2)) > 80) {
        //         $this->check_temperature($req->device_id, $hasDevice->device_name, $req->temp);
        //     }
        // }
    }

    public function check_temperature($d_id, $d_name, $d_temp)
    {
        $appUser = AppUser::select('app_users.id')->where('login.role', 'admin')
            ->join('login', 'login.user_name', '=', 'app_users.user_name')
            ->get();


        Notification::where('flag', 3)->where('du_id', $d_id)->delete();
        foreach ($appUser as $au) {
            $noti = new Notification();
            $noti->user_id = $au->id;
            $noti->flag = 3;                                   //    Device Temperature is above 80'C
            $noti->status = 'unseen';
            $noti->du_id = $d_id;
            $noti->du_name = $d_name;
            $noti->details = " temperature is Now " . $d_temp;                       //" temperature is Now ".$d_temp;
            $noti->created_at = new Datetime();
            $noti->save();
        }
    }


    public function get_universe($id)
    {
        $user_deselected = DeselectPeriod::whereNotNull('start_date')->whereNull('end_date')->pluck('device_id')->toArray();
        $user_deselected=User::whereIn('device_id',$user_deselected)->pluck('id')->toArray();
        $user = User::where('id', $id)->first();

        $startDate = new DateTime($user->dob);
$endDate = new DateTime();

$diffInYears = $endDate->format('Y') - $startDate->format('Y');

        $years = $diffInYears;
        $ageGroupListNumRange = [
            [0, 14],
            [15, 24],
            [25, 34],
            [35, 44],
            [45, 150],
        ];
        $age_group_string = array("0-14", "15-24", "25-34", "35-44", "45 & Above");
        if ($years <= 14) {
            $age_range=$ageGroupListNumRange[0];
            $age_group = $age_group_string[0];
        } elseif ($years >= 15 && $years <= 24) {
            $age_range=$ageGroupListNumRange[1];
            $age_group = $age_group_string[1];
        } elseif ($years >= 25 && $years <= 34) {
            $age_range=$ageGroupListNumRange[2];
            $age_group = $age_group_string[2];
        } elseif ($years >= 35 && $years <= 44) {
            $age_range=$ageGroupListNumRange[3];
            $age_group = $age_group_string[3];
        } elseif ($years >= 45) {
            $age_range=$ageGroupListNumRange[4];
            $age_group = $age_group_string[4];
        }

        $systemUniverse = User:: //where('type', $req->userType)
            //->
            where('address', 'like', '%' . $user->address . '%')
            ->where('gender', 'like', '%' . $user->gender . '%')
            ->where('economic_status', 'like', '%' . $user->economic_status . '%')
            ->whereNotIn('id', $user_deselected)
            //->whereBetween('age', [$req->age1, $req->age2])
                            ->whereRaw('YEAR(CURDATE()) - YEAR(dob) >= ?', [$age_range[0]])
                            ->whereRaw('YEAR(CURDATE()) - YEAR(dob) <= ?', [$age_range[1]])
                            ->whereNotNull('dob')
                            ->whereNotNull('economic_status')
                            ->whereNotNull('gender')
                            ->whereNotNull('address')
            ->count();
            $startDateTime=date("Y-m-d");
        $universe = Universe::where('start', '<=', $startDateTime)
                ->where('end', '>=', $startDateTime)
            ->where('region', 'like', '%' . strtolower($user->address) . '%')
            ->where('gender', 'like', '%' . $user->gender . '%')
            ->where('sec', 'like', '%' . $user->economic_status . '%')
            ->where('age_group', $age_group)->first();
        $cat = Category:: //where('type', $req->userType)
            //->
            where('region', 'like', '%' . strtolower($user->address) . '%')
            ->where('gender', 'like', '%' . $user->gender . '%')
            ->where('sec', 'like', '%' . $user->economic_status . '%')
            ->where('age_group', $age_group)->first();
        $arr = array($systemUniverse, $universe, $cat);
        return $arr;
    }
    
    public function datafix(Request $req)
    {
        $startDate = substr($req->start, 0, 10);
        $startTime = substr($req->start, 11, 19);
        $finishDate = substr($req->finish, 0, 10);
        $finishTime = substr($req->finish, 11, 19);
        $startDateTime = date($startDate) . " " . $startTime;
        $finishDateTime = date($finishDate) . " " . $finishTime;
        $i=0;
        //return response()->json(["values" => $finishDateTime], 200);
        $ram_logs = ViewLog::where('finished_watching_at', '>', $startDateTime)
        ->where('started_watching_at', '<', $finishDateTime)
        //->where('universe', '=', 1)

            ->get();
            foreach ($ram_logs as $ram_log) {
                //return response()->json(["values" => $ram_log], 200);
                $uni = $this->get_universe($ram_log->user_id);
                //return response()->json(["values" => $uni], 200);
                $ram_log->system = ($uni[0]!=0)?$uni[0]:1;
                $ram_log->universe = ($uni[1]!=null)?$uni[1]->universe:1;
                $ram_log->category_id = ($uni[2]!=null)?$uni[2]->id:-1;
                $ram_log->save();
                $i++;
            }
        return response()->json(["values" => $i,"total"=>count($ram_logs)], 200);
    }



    public function receiver($request)
    {

        //return response()->json(["values" => $request->user_id], 200);

        $channel_id = $request->channel_name;
        //$user_id = $request->user_id;
        //$last_req_time = Carbon::now()->toDateTimeString();
        $last_watching_time = $request->finish;
        //$channel_id = $request->channel_id;
        $user_id = $request->user_id;





        $last_req_time = Carbon::now()->toDateTimeString(); //update
        $this->updateUserWatchingLastReq($user_id, $last_req_time);
        $deselect_device = DeselectPeriod::where('device_id', $request->device_id)->whereNotNull('start_date')->whereNull('end_date')->first();
        if ($deselect_device) {
            $Deselect_log = DeselectLog::where('user_id', $user_id)->where('channel_id', $channel_id)
                ->where('finished_watching_at', $request->start)->first();
            if ($Deselect_log) {
                $Deselect_log->finished_watching_at = $request->finish;
                $Deselect_log->duration_minute = abs(strtotime($Deselect_log->started_watching_at) - strtotime($Deselect_log->finished_watching_at)) / 60;
                if (strtotime($Deselect_log->started_watching_at) < strtotime($Deselect_log->finished_watching_at)) {

                    $Deselect_log->save();
                } else {
                    return;
                }
            } else {


                $uni = $this->get_universe($user_id);
                $var = new DeselectLog;
                $var->user_id = $user_id;
                $var->channel_id = $channel_id;
                $var->started_watching_at = $request->start;
                $var->finished_watching_at = $request->finish;
                $var->system = ($uni[0]!=0)?$uni[0]:1;
                $var->universe = ($uni[1]!=null)?$uni[1]->universe:1;
                $var->category_id = ($uni[2]!=null)?$uni[2]->id:-1;
                $var->duration_minute = abs(strtotime($var->started_watching_at) - strtotime($var->finished_watching_at)) / 60;
                if (strtotime($var->started_watching_at) < strtotime($var->finished_watching_at)) {

                    $var->save();
                } else {
                    return;
                }
            }
        } else {
            $log = ViewLog::where('user_id', $user_id)->where('channel_id', $channel_id)
                ->where('finished_watching_at', $request->start)->first();
            if ($log) {
                $log->finished_watching_at = $request->finish;
                $log->duration_minute = abs(strtotime($log->started_watching_at) - strtotime($log->finished_watching_at)) / 60;
                //$log->save();
                if (strtotime($log->started_watching_at) < strtotime($log->finished_watching_at)) {

                    $log->save();
                } else {
                    return;
                }
            } else {


                $uni = $this->get_universe($user_id);
                $var = new ViewLog;
                $var->user_id = $user_id;
                $var->channel_id = $channel_id;
                $var->started_watching_at = $request->start;
                $var->finished_watching_at = $request->finish;

                $var->system = ($uni[0]!=0)?$uni[0]:1;
                $var->universe = ($uni[1]!=null)?$uni[1]->universe:1;
                $var->category_id = ($uni[2]!=null)?$uni[2]->id:-1;
                $var->duration_minute = abs(strtotime($var->started_watching_at) - strtotime($var->finished_watching_at)) / 60;
                if (strtotime($var->started_watching_at) < strtotime($var->finished_watching_at)) {

                    $var->save();
                } else {
                    return;
                }
                //$var->save();

            }
        }
    }




    public function updateDeviceLastReq($d_id, $time)
    {
        $d = Device::where('id', $d_id)->first();
        $d->last_request = $time;
        $d->save();
    }

    public function updateUserWatchingLastReq($u_id, $time)
    {
        $u = User::where('id', $u_id)->first();
        $u->last_request = $time;
        $u->save();
    }

    public function isValid($time)
    {
        $date = new DateTime($time);
        $date2 = new DateTime();
        $diff = $date2->getTimestamp() - $date->getTimestamp();
        if ($diff > 26) {
            return true;
        }
        return false;
    }
    public function wrongDetect($channel_id, $time, $device_id)
    {
        return false; //
        $device = User::where('id', $device_id)->first();
        if ($channel_id == 32 || $channel_id == 37 || $channel_id == 39) { //only 39
            if ($device->wrong_channel && $device->wrong_channel == $channel_id) {
                $date = new DateTime($device->wrong_time);
                $date2 = new DateTime();
                $diff = $date2->getTimestamp() - $date->getTimestamp();
                if ($diff > 40) { //seconds ex 30
                    $device->wrong_time = null;
                    $device->wrong_channel = null;
                    $device->save();
                    return false;
                }
                return true;
            } else {
                $device->wrong_channel = $channel_id;
                $device->wrong_time = $time;
                $device->save();
                return true;
            }
        }

        $device->wrong_time = null;
        $device->wrong_channel = null;
        $device->save();
        return false;
    }

    public function raw($id)

    {

        $data = RawRequest::where('device_id', $id)->orderBy('id', 'DESC')->take(500)->get();
        $ot = "<table border=1><tr><td>channel_id</td><td>device_id</td><td>start</td><td>finish</td><td>people</td><td>offline</td><td>temp</td><td>error</td><td>server_time</td></tr>";
        foreach ($data as $d) {
            $ot .= "<tr><td>" . $d->channel_id . "</td><td>" . $d->device_id . "</td><td>" . $d->start . "</td><td>" . $d->finish . "</td><td>" . $d->people . "</td><td>" . $d->offline . "</td><td>" . $d->temp . "</td><td>" . $d->error . "</td><td>" . $d->server_time . "</td></tr>";
        }

        $ot .= "</table>";

        return $ot;
    }

    public function deselect()
    {
       
        $user_deselected = DeselectPeriod::whereNotNull('start_date')->whereNull('end_date')->pluck('device_id')->toArray();
        $user_deselected=User::whereIn('device_id',$user_deselected)->pluck('id')->toArray();
        
        return response()->json(["response" => $user_deselected], 200);
    }
    public function logs($id)
    {
        $data = ViewLog::where('user_id', $id)->orderBy('id', 'DESC')->get();
        $ot = '<html><head><script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="//cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script>';
        $ot .= '<script>
            function export_data(){
                $("#data").table2excel({
                   
                    name: "sheet1",
                    filename: "Logs.xls", // do include extension
                    preserveColors: false // set to true if you want background colors and font colors preserved
                });
            }
        </script></head><body>';
        $ot .= "<button onclick='export_data()'>Download Excel</button><table id='data' border=1><thead><tr><th>channel_name</th><th>device_id</th><th>started_watching</th><th>finished_watching</th></tr></thead><tbody>";
        foreach ($data as $d) {
            $ot .= "<tr><td>" . $d->channel->channel_name . "</td><td>" . $d->user_id . "</td><td>" . $d->started_watching_at . "</td><td>" . $d->finished_watching_at . "</td></tr>";
        }
        $ot .= "</tbody></table></body></html>";
        return $ot;
    }
}
