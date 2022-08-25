<?php

namespace App\Http\Controllers;

use App\Models\ViewLog;
use Illuminate\Http\Request;
use DateTime;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Device;
use App\Models\DeselectPeriod;
use App\Models\DeselectLog;
use App\Models\RawRequest;

class RequestController extends Controller
{

    //
    public function receive(Request $req)
    {
        if ($req->people != null) {
            $devices = Device::where('id', $req->device_id)->first();
            //return response()->json(["dd"=>$devices],200);
            $arr=array();
            if ($devices) {
                for ($i = 0; $i < 8; $i++) {
                    $index = User::where('device_id', $req->device_id)->where('user_index', $i)->first();
                    //array_push($arr,$index);
                    //return response()->json(["ranges" => $index], 200);
                    if ($index) {
                        if ($req->people[$i] === '1') {
                            $ob = array("device_id" => $index->id, "channel_name" => $req->channel_name, "time_stamp" => $req->time_stamp);
                            //array_push($user, (object)$ob);
                            $this->receiver((object)$ob);
                            //array_push($arr,$index);
                        } else {
                            $ob = array("device_id" => $index->id, "channel_name" => 999, "time_stamp" => $req->time_stamp);
                            //array_push($user, (object)$ob);
                            $this->receiver((object)$ob);
                        }
                    }
                }
            }
        } else {
            $this->receiver($req);
        }
    }
    public function receiver($request)
    {
        $rr = new RawRequest();
        $rr->channel_id = $request->channel_name;
        $rr->device_id = $request->device_id;
        $rr->time_stamp = $request->time_stamp;
        $rr->server_time = Carbon::now()->toDateTimeString();;
        $rr->save();

        if ($request->channel_name >= 40 && $request->channel_name <= 100) {
            $request->channel_name = 888;
        }
        $channel_id = $request->channel_name;
        $user_id = $request->device_id;
        if ($channel_id == 24) {
            $started_watching_at =  Carbon::now()->toDateTimeString();;
            $this->updateLastReq($user_id, $started_watching_at);
            return;
        }
        $started_watching_at = $request->time_stamp;
        //$started_watching_at =  Carbon::now()->toDateTimeString();

        $deselect_user = DeselectPeriod::where('user_id', $user_id)->whereNotNull('start_date')->whereNull('end_date')->first();
        if ($deselect_user) {
            $Deselect_log = DeselectLog::where('user_id', $user_id)
                ->where('finished_watching_at', NULL)->first();
            $this->updateLastReq($user_id, $started_watching_at);
            if (($Deselect_log && $this->wrongDetect($channel_id, $started_watching_at, $user_id)) || ($Deselect_log && $channel_id == $Deselect_log->channel_id)) return;
            if ($Deselect_log) {
                $Deselect_log->finished_watching_at = $started_watching_at;
                $time = new DateTime($Deselect_log->started_watching_at);
                $diff = $time->diff(new DateTime($Deselect_log->finished_watching_at));
                $minutes = ($diff->days * 24 * 60) +
                    ($diff->h * 60) + $diff->i;

                $minutes = $minutes > 999 ? 999 : $minutes;
                $Deselect_log->duration_minute = $minutes;
                $Deselect_log->save();
                //return $minutes;
                //$tmp_log->delete();
            }
        } else {
            $log = ViewLog::where('user_id', $user_id)
                ->where('finished_watching_at', NULL)->first();
            $this->updateLastReq($user_id, $started_watching_at);
            if (($log && $this->wrongDetect($channel_id, $started_watching_at, $user_id)) || ($log && $channel_id == $log->channel_id)) return;
            if ($log) {
                $log->finished_watching_at = $started_watching_at;
                $time = new DateTime($log->started_watching_at);
                $diff = $time->diff(new DateTime($log->finished_watching_at));
                $minutes = ($diff->days * 24 * 60) +
                    ($diff->h * 60) + $diff->i;

                $minutes = $minutes > 999 ? 999 : $minutes;
                $log->duration_minute = $minutes;
                $log->save();
                //return $minutes;
                //$tmp_log->delete();
            }
        }


        if ($channel_id != 999) {
            $deselect_user = DeselectPeriod::where('user_id', $user_id)->whereNotNull('start_date')->whereNull('end_date')->first();
            if ($deselect_user) {
                $var = new DeselectLog;
                //$var->id=5010;
                $var->user_id = $user_id;
                $var->channel_id = $channel_id;
                $var->started_watching_at = $started_watching_at;
                $var->save();

                $user = User::where('id', $user_id)->first();
                $user->tvoff = 1;
                $user->save();
            } else {
                $var = new ViewLog;
                //$var->id=5010;
                $var->user_id = $user_id;
                $var->channel_id = $channel_id;
                $var->started_watching_at = $started_watching_at;
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
    public function updateLastReq($u_id, $time)
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
        $ot = "<table border=1><tr><td>channel_id</td><td>device_id</td><td>time_stamp</td><td>server_time</td></tr>";
        foreach ($data as $d) {
            $ot .= "<tr><td>" . $d->channel_id . "</td><td>" . $d->device_id . "</td><td>" . $d->time_stamp . "</td><td>" . $d->server_time . "</td></tr>";
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
