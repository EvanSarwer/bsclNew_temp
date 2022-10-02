<?php

namespace App\Http\Controllers;

use App\Models\ViewLog;
use Illuminate\Http\Request;
use DateTime;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Device;
use App\Models\Channel;
use App\Models\DeselectPeriod;
use App\Models\DeselectLog;
use App\Models\RawRequest;

class RequestController extends Controller
{
    public function receiveoutside(Request $request)
    {

        //return response()->json(["value" => $request[1]['user']], 200);
        foreach ($request->data as $req) {
            $user = User::where('user_name', 'like', '%' . $req['user'] . '%')->first();
            //return response()->json(["value" => $user->id], 200);
            $viewlogp = Viewlog::where('started_watching_at', $req['start'])
                ->where('finished_watching_at', $req['finish'])
                ->where('channel_id', $req['channel_id'])
                ->where('user_id', $user->id)
                ->first();
            if (!$viewlogp) {
                $var = new ViewLog;
                //$var->id=5010;
                $var->user_id = $user->id;
                $var->channel_id = $req['channel_id'];
                $var->started_watching_at = $req['start'];
                $var->finished_watching_at = $req['finish'];
                $var->duration_minute = abs(strtotime($req['start']) - strtotime($req['finish'])) / 60;
                $var->save();

                $user->tvoff = 1;
                $user->save();
            }
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
    //                         $ob = array("device_id" => $index->id, "channel_name" => $req->channel_name, "time_stamp" => $req->time_stamp);
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
        if ($req->time_stamp != null) {
            $rr = new RawRequest();
            $rr->channel_id = $req->channel_name;
            $rr->device_id = $req->device_id;
            $rr->finish = $req->time_stamp;
            $rr->server_time = Carbon::now()->toDateTimeString();;
            $rr->save();
            $req->finish = $req->time_stamp;
        } else {
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
        }


        if ($req->channel_name >= 40 && $req->channel_name <= 100) {
            $req->channel_name = 888;
        }


        $hasDevice = Device::where('id', $req->device_id)->first();
        if ($hasDevice) {
            $last_req_time = Carbon::now()->toDateTimeString();
            $this->updateDeviceLastReq($req->device_id, $last_req_time);

            $hasChannel = Channel::where('id', $req->channel_name)->first();
            if (($hasChannel || $req->channel_name == 999) && ((strtotime($req->start)) <= (strtotime($req->finish)))) {

                if ($req->channel_name != 999) {
                    $hasDevice->tvoff = 1;
                    $hasDevice->save();
                } else {
                    $hasDevice->tvoff = 0;
                    $hasDevice->save();
                }

                //$users = array();
                if ($req->people != null) {
                    for ($i = 0; $i < strlen($req->people); $i++) {
                        $index = User::where('device_id', $req->device_id)->where('user_index', $i)->first();
                        //array_push($arr,$index);
                        //return response()->json(["ranges" => $index], 200);
                        if ($index) {
                            if ($req->people[$i] === '1') {
                                //$ob = array("device_id" => $req->device_id, "user_id" => $index->id, "channel_id" => $req->channel_name, "start" => $req->start, "finish" => $req->finish, "error_msg" => $req->error);
                                //$this->receiver1((object)$ob);
                                //array_push($users, (object)$ob);
                                $ob = array("device_id" => $req->device_id, "user_id" => $index->id, "channel_name" => $req->channel_name, "start" => $req->start, "finish" => $req->finish, "error_msg" => $req->error);
                                $this->receiver((object)$ob);
                            } else {
                                //$ob = array("device_id" => $req->device_id, "user_id" => $index->id, "channel_id" => 999,  "start" => $req->start, "finish" => $req->finish, "error_msg" => $req->error);
                                //$this->receiver1((object)$ob);
                                //array_push($users, (object)$ob);
                                $ob = array("device_id" => $req->device_id, "user_id" => $index->id, "channel_name" => 999, "start" => $req->start, "finish" => $req->finish, "error_msg" => $req->error);
                                $this->receiver((object)$ob);
                            }
                        }
                    }
                } else {
                    $user = User::where('device_id', $req->device_id)->first();
                    $req->user_id = $user->id;
                    $this->receiver($req);
                }
                //return response()->json([$users],200);
            }
        }
    }





    public function receiver($request)
    {

        $channel_id = $request->channel_name;
        $user_id = $request->user_id;
        //$last_req_time = Carbon::now()->toDateTimeString();
        $last_watching_time = $request->finish;
        //$started_watching_at = $request->finish;
        // if ($channel_id == 24) {
        //     $started_watching_at =  Carbon::now()->toDateTimeString();;
        //     $this->updateUserWatchingLastReq($user_id, $started_watching_at);
        //     return;
        // }


        $deselect_user = DeselectPeriod::where('device_id', $request->device_id)->whereNotNull('start_date')->whereNull('end_date')->first();
        if ($deselect_user) {
            $this->updateUserWatchingLastReq($user_id, $last_watching_time);

            $Deselect_log = DeselectLog::where('user_id', $user_id)
                ->where('finished_watching_at', NULL)->first();
            if (($Deselect_log && $this->wrongDetect($channel_id, $last_watching_time, $user_id)) || ($Deselect_log && $channel_id == $Deselect_log->channel_id)) return;
            if ($Deselect_log) {
                $Deselect_log->finished_watching_at = $last_watching_time;
                $Deselect_log->duration_minute = abs(strtotime($Deselect_log->started_watching_at) - strtotime($Deselect_log->finished_watching_at)) / 60;
                $Deselect_log->save();
                //return $minutes;
                //$tmp_log->delete();
            }
        } else {
            $this->updateUserWatchingLastReq($user_id, $last_watching_time);

            $log = ViewLog::where('user_id', $user_id)
                ->where('finished_watching_at', NULL)->first();
            if (($log && $this->wrongDetect($channel_id, $last_watching_time, $user_id)) || ($log && $channel_id == $log->channel_id)) return;
            if ($log) {
                $log->finished_watching_at = $last_watching_time;
                $log->duration_minute = abs(strtotime($log->started_watching_at) - strtotime($log->finished_watching_at)) / 60;
                $log->save();
                //return $minutes;
                //$tmp_log->delete();
            }
        }


        if ($channel_id != 999) {
            $deselect_user = DeselectPeriod::where('device_id', $request->device_id)->whereNotNull('start_date')->whereNull('end_date')->first();
            if ($deselect_user) {
                $var = new DeselectLog;
                //$var->id=5010;
                $var->user_id = $user_id;
                $var->channel_id = $channel_id;
                $var->started_watching_at = $last_watching_time;
                $var->save();

                $user = User::where('id', $user_id)->first();
                $user->tvoff = 1;
                $user->save();
            } else {
                $var = new ViewLog;
                //$var->id=5010;
                $var->user_id = $user_id;
                $var->channel_id = $channel_id;
                $var->started_watching_at = $last_watching_time;
                $var->save();

                $user = User::where('id', $user_id)->first();
                $user->tvoff = 1;
                $user->save();
            }
        } else if ($channel_id == 999) {

            $user = User::where('id', $user_id)->first();
            $user->tvoff = 0;
            $user->save();
        }
    }






    public function receiver1($request)
    {

        // if ($request->channel_id >= 40 && $request->channel_id <= 100) {
        //     $request->channel_id = 888;
        // }
        $channel_id = $request->channel_id;
        $user_id = $request->user_id;


        if ($channel_id != 999) {
            $last_req_time = Carbon::now()->toDateTimeString(); //update
            $this->updateUserWatchingLastReq($user_id, $last_req_time);
            $deselect_device = DeselectPeriod::where('device_id', $request->device_id)->whereNotNull('start_date')->whereNull('end_date')->first();
            if ($deselect_device) {

                $Deselect_log = DeselectLog::where('user_id', $user_id)->where('channel_id', $channel_id)->where('started_watching_at', $request->start)->first();
                if ($Deselect_log) {
                    $Deselect_log->finished_watching_at = $request->finish;
                    $Deselect_log->duration_minute = abs(strtotime($Deselect_log->started_watching_at) - strtotime($Deselect_log->finished_watching_at)) / 60;
                    $Deselect_log->save();
                } else {

                    $var = new DeselectLog;
                    $var->user_id = $user_id;
                    $var->channel_id = $channel_id;
                    $var->started_watching_at = $request->start;
                    $var->finished_watching_at = $request->finish;
                    $var->duration_minute = abs(strtotime($var->started_watching_at) - strtotime($var->finished_watching_at)) / 60;
                    $var->save();

                    $user = User::where('id', $user_id)->first();
                    $user->tvoff = 1;
                    $user->save();
                }
            } else {
                $log = ViewLog::where('user_id', $user_id)->where('channel_id', $channel_id)->where('started_watching_at', $request->start)->first();
                if ($log) {
                    $log->finished_watching_at = $request->finish;
                    $log->duration_minute = abs(strtotime($log->started_watching_at) - strtotime($log->finished_watching_at)) / 60;
                    $log->save();
                } else {

                    $var = new ViewLog;
                    $var->user_id = $user_id;
                    $var->channel_id = $channel_id;
                    $var->started_watching_at = $request->start;
                    $var->finished_watching_at = $request->finish;
                    $var->duration_minute = abs(strtotime($var->started_watching_at) - strtotime($var->finished_watching_at)) / 60;

                    $var->save();

                    $user = User::where('id', $user_id)->first();
                    $user->tvoff = 1;
                    $user->save();
                }
            }
        } else if ($channel_id == 999) {

            $user = User::where('id', $user_id)->first();
            $user->tvoff = 0;
            $user->save();
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

        $data = RawRequest::where('device_id', $id)->orderBy('id', 'DESC')->get();
        $ot = "<table border=1><tr><td>channel_id</td><td>device_id</td><td>start</td><td>finish</td><td>people</td><td>offline</td><td>temp</td><td>error</td><td>server_time</td></tr>";
        foreach ($data as $d) {
            $ot .= "<tr><td>" . $d->channel_id . "</td><td>" . $d->device_id . "</td><td>" . $d->start . "</td><td>" . $d->finish . "</td><td>" . $d->people . "</td><td>" . $d->offline . "</td><td>" . $d->temp . "</td><td>" . $d->error . "</td><td>" . $d->server_time . "</td></tr>";
        }

        $ot .= "</table>";

        return $ot;
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
