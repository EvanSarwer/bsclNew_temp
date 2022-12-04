<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewLog;
use App\Models\Channel;
use App\Models\User;

class DayRangedController extends Controller
{
    //
    public function dayrangedall(Request $req)
    {
        $userids=User::where('type', 'like', '%' . $req->type . '%')
            ->pluck('id')->toArray();
        $tv = 0;
        $dd = 0;
        $tr = array();
        $df = array();
        $ds = array();
        $tvrps = array();
        $tvr0s = array();
        $reacht = array();
        $reachps = array();
        $reach0s = array();
        $channelArray = array();
        $viewer = array();
        $ldate = date('H:i:s');
        $users = User::all();
        $numOfUser = $users->count();
        $start = $req->start;
        if ($start == "") {
            $start = "00:00:00";
        }
        $finish = $req->finish;
        if ($finish == "") {
            $finish = "23:59:59";
        }
        $diff = abs(strtotime($start) - strtotime($finish)) / 60;
        $month = $req->month;
        if (empty($month)) {
            $month = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12');
        }
        //$month = implode(",", $month);
        $year = $req->year;
        if (empty($year)) {
            $year = array('2022', '2021', '2020', '2019', '2018');
        }
        //$year = implode(",", $year);
        $day = $req->day;
        if (empty($day)) {
            $day = array('0', '1', '2', '3', '4', '5', '6');
        }
        //$day = implode(",", $day);

        $tr = $this->getrange($year, $month, $day, $start, $finish);

        //    return response()->json(["range" => $tr], 200);


        $channels = Channel::all('id', 'channel_name');
        if ($req->id == "") {
            foreach ($channels as $c) {
                foreach ($tr as $t) {
                    $viewers = ViewLog::where('channel_id', $c->id)
                        ->where(function ($query) use ($t) {
                            $query->where('finished_watching_at', '>', date("Y-m-d H:i:s", strtotime($t["start"])))
                                ->orWhereNull('finished_watching_at');
                        })
                        ->where('started_watching_at', '<', date("Y-m-d H:i:s", strtotime($t["finish"])))
                        ->whereIn('user_id', $userids)
                        ->get();

                    foreach ($viewers as $v) {
                        if ($v->finished_watching_at == null) {
                            if ((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) {
                                $timeviewd = abs(strtotime($t["start"]) - strtotime($ldate));
                            } else if ((strtotime($v->started_watching_at)) >= (strtotime($t["start"]))) {
                                $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($ldate));
                            }
                        } else if (((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) > (strtotime($t["finish"])))) {
                            $timeviewd = abs(strtotime($t["start"]) - strtotime($t["finish"]));
                        } else if (((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) <= (strtotime($t["finish"])))) {
                            $timeviewd = abs(strtotime($t["start"]) - strtotime($v->finished_watching_at));
                        } else if (((strtotime($v->started_watching_at)) >= (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) > (strtotime($t["finish"])))) {
                            $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($t["finish"]));
                        } else {
                            $timeviewd = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                        }
                        //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                        $timeviewd = $timeviewd / 60;
                        $tv = $tv + $timeviewd;

                        array_push($reacht, $v->user_id);
                    }

                    $dd = $dd + abs(strtotime($t["start"]) - strtotime($t["finish"]));
                }
                //$reacht = array_unique($reacht);
                $numofViewer = count(array_unique($reacht));
                array_push($reach0s, $numofViewer);
                $reachp = ($numofViewer / $numOfUser) * 100;
                unset($reacht);
                $reacht = array();
                array_push($reachps, $reachp);

                $dd = $dd / 60;
                $tv = $tv / $numOfUser;

                $tv = $tv / $dd;
                array_push($tvr0s, $tv);
                $tv = $tv * 100;
                array_push($channelArray, $c->channel_name);
                array_push($tvrps, $tv);
                array_push($ds, $dd);
                $tv = 0;
                $dd = 0;
            }
        } else {
            foreach ($channels as $c) {
                if ($c->id == ((int)$req->id)) {
                    foreach ($tr as $t) {
                        $viewers = ViewLog::where('channel_id', $c->id)
                            ->where(function ($query) use ($t) {
                                $query->where('finished_watching_at', '>', date("Y-m-d H:i:s", strtotime($t["start"])))
                                    ->orWhereNull('finished_watching_at');
                            })
                            ->where('started_watching_at', '<', date("Y-m-d H:i:s", strtotime($t["finish"])))
                            ->whereIn('user_id', $userids)
                            ->get();
                        foreach ($viewers as $v) {
                            if ($v->finished_watching_at == null) {
                                if ((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) {
                                    $timeviewd = abs(strtotime($t["start"]) - strtotime($ldate));
                                } else if ((strtotime($v->started_watching_at)) >= (strtotime($t["start"]))) {
                                    $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($ldate));
                                }
                            } else if (((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) > (strtotime($t["finish"])))) {
                                $timeviewd = abs(strtotime($t["start"]) - strtotime($t["finish"]));
                            } else if (((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) <= (strtotime($t["finish"])))) {
                                $timeviewd = abs(strtotime($t["start"]) - strtotime($v->finished_watching_at));
                            } else if (((strtotime($v->started_watching_at)) >= (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) > (strtotime($t["finish"])))) {
                                $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($t["finish"]));
                            } else {
                                $timeviewd = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                            }
                            //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                            $timeviewd = $timeviewd / 60;
                            $tv = $tv + $timeviewd;

                            array_push($reacht, $v->user_id);
                        }
                        $dd = $dd + abs(strtotime($t["start"]) - strtotime($t["finish"]));
                    }
                    $numofViewer = count(array_unique($reacht));
                    array_push($reach0s, $numofViewer);
                    $reachp = ($numofViewer / $numOfUser) * 100;
                    unset($reacht);
                    $reacht = array();
                    array_push($reachps, $reachp);

                    $dd = $dd / 60;
                    $tv = $tv / $numOfUser;
                    $tv = $tv / $dd;
                    array_push($tvr0s, $tv);
                    $tv = $tv * 100;
                    array_push($channelArray, $c->channel_name);
                    array_push($tvrps, $tv);
                    array_push($ds, $dd);
                    $tv = 0;
                    $dd = 0;
                } else {
                    array_push($tvrps, 0);
                    array_push($tvr0s, 0);
                    array_push($reachps, 0);
                    array_push($reach0s, 0);
                    array_push($channelArray, $c->channel_name);
                    array_push($ds, $dd);
                }
            }
        }

        return response()->json(["dd" => $ds, "label" => $channelArray, "tvrp" => $tvrps, "tvr0" => $tvr0s, "reachp" => $reachps, "reach0" => $reach0s], 200);
    }
    public function reachp(Request $req)
    {
        $tv = 0;
        $dd = 0;
        $tr = array();
        $df = array();
        $ds = array();
        $reachs = array();
        $channelArray = array();
        $viewer = array();
        $ldate = date('H:i:s');
        $users = User::all();
        $numOfUser = $users->count();
        $start = $req->start;
        if ($start == "") {
            $start = "00:00:00";
        }
        $finish = $req->finish;
        if ($finish == "") {
            $finish = "23:59:59";
        }
        $diff = abs(strtotime($start) - strtotime($finish)) / 60;
        $month = $req->month;
        if (empty($month)) {
            $month = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12');
        }
        //$month = implode(",", $month);
        $year = $req->year;
        if (empty($year)) {
            $year = array('2022', '2021', '2020', '2019', '2018');
        }
        //$year = implode(",", $year);
        $day = $req->day;
        if (empty($day)) {
            $day = array('0', '1', '2', '3', '4', '5', '6');
        }
        //$day = implode(",", $day);

        $tr = $this->getrange($year, $month, $day, $start, $finish);

        //    return response()->json(["range" => $tr], 200);


        $channels = Channel::all('id', 'channel_name');
        if ($req->id == "") {
            foreach ($channels as $c) {
                foreach ($tr as $t) {
                    $viewers = ViewLog::where('channel_id', $c->id)
                        ->where(function ($query) use ($t) {
                            $query->where('finished_watching_at', '>', date("Y-m-d H:i:s", strtotime($t["start"])))
                                ->orWhereNull('finished_watching_at');
                        })
                        ->where('started_watching_at', '<', date("Y-m-d H:i:s", strtotime($t["finish"])))
                        ->get();

                    foreach ($viewers as $v) {
                        array_push($viewer, $v->user->id);
                    }

                    //$dd = $dd + abs(strtotime($t["start"]) - strtotime($t["finish"]));
                }
                $viewer = array_values(array_unique($viewer));
                $numofViewer = count($viewer);
                $reachp = ($numofViewer / $numOfUser) * 100;
                //$reach0=$numofViewer;
                unset($viewer);
                $viewer = array();
                array_push($reachs, $reachp);
                array_push($channelArray, $c->channel_name);
            }
        } else {
            foreach ($channels as $c) {
                if ($c->id == ((int)$req->id)) {
                    foreach ($tr as $t) {
                        $viewers = ViewLog::where('channel_id', $c->id)
                            ->where(function ($query) use ($t) {
                                $query->where('finished_watching_at', '>', date("Y-m-d H:i:s", strtotime($t["start"])))
                                    ->orWhereNull('finished_watching_at');
                            })
                            ->where('started_watching_at', '<', date("Y-m-d H:i:s", strtotime($t["finish"])))
                            ->get();
                        foreach ($viewers as $v) {
                            array_push($viewer, $v->user->id);
                        }
                        //$dd = $dd + abs(strtotime($t["start"]) - strtotime($t["finish"]));
                    }
                    $viewer = array_values(array_unique($viewer));
                    $numofViewer = count($viewer);
                    $reachp = ($numofViewer / $numOfUser) * 100;
                    //$reach0=$numofViewer;
                    unset($viewer);
                    $viewer = array();
                    array_push($reachs, $reachp);
                    array_push($channelArray, $c->channel_name);
                } else {
                    array_push($reachs, 0);
                    array_push($channelArray, $c->channel_name);
                    //array_push($ds, $dd);
                }
            }
        }

        return response()->json(["dd" => $ds, "label" => $channelArray, "value" => $reachs], 200);
    }



    public function reach0(Request $req)
    {
        $tv = 0;
        $dd = 0;
        $tr = array();
        $df = array();
        $ds = array();
        $reachs = array();
        $channelArray = array();
        $viewer = array();
        $ldate = date('H:i:s');
        $users = User::all();
        $numOfUser = $users->count();
        $start = $req->start;
        if ($start == "") {
            $start = "00:00:00";
        }
        $finish = $req->finish;
        if ($finish == "") {
            $finish = "23:59:59";
        }
        $diff = abs(strtotime($start) - strtotime($finish)) / 60;
        $month = $req->month;
        if (empty($month)) {
            $month = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12');
        }
        //$month = implode(",", $month);
        $year = $req->year;
        if (empty($year)) {
            $year = array('2022', '2021', '2020', '2019', '2018');
        }
        //$year = implode(",", $year);
        $day = $req->day;
        if (empty($day)) {
            $day = array('0', '1', '2', '3', '4', '5', '6');
        }
        //$day = implode(",", $day);

        $tr = $this->getrange($year, $month, $day, $start, $finish);

        //    return response()->json(["range" => $tr], 200);


        $channels = Channel::all('id', 'channel_name');
        if ($req->id == "") {
            foreach ($channels as $c) {
                foreach ($tr as $t) {
                    $viewers = ViewLog::where('channel_id', $c->id)
                        ->where(function ($query) use ($t) {
                            $query->where('finished_watching_at', '>', date("Y-m-d H:i:s", strtotime($t["start"])))
                                ->orWhereNull('finished_watching_at');
                        })
                        ->where('started_watching_at', '<', date("Y-m-d H:i:s", strtotime($t["finish"])))
                        ->get();

                    foreach ($viewers as $v) {
                        array_push($viewer, $v->user->id);
                    }

                    //$dd = $dd + abs(strtotime($t["start"]) - strtotime($t["finish"]));
                }
                $viewer = array_values(array_unique($viewer));
                $numofViewer = count($viewer);
                //$reachp = ($numofViewer / $numOfUser) * 100;
                $reachp = $numofViewer;
                //$reach0=$numofViewer;
                unset($viewer);
                $viewer = array();
                array_push($reachs, $reachp);
                array_push($channelArray, $c->channel_name);
            }
        } else {
            foreach ($channels as $c) {
                if ($c->id == ((int)$req->id)) {
                    foreach ($tr as $t) {
                        $viewers = ViewLog::where('channel_id', $c->id)
                            ->where(function ($query) use ($t) {
                                $query->where('finished_watching_at', '>', date("Y-m-d H:i:s", strtotime($t["start"])))
                                    ->orWhereNull('finished_watching_at');
                            })
                            ->where('started_watching_at', '<', date("Y-m-d H:i:s", strtotime($t["finish"])))
                            ->get();
                        foreach ($viewers as $v) {
                            array_push($viewer, $v->user->id);
                        }
                        //$dd = $dd + abs(strtotime($t["start"]) - strtotime($t["finish"]));
                    }
                    $viewer = array_values(array_unique($viewer));
                    $numofViewer = count($viewer);
                    $reachp = $numofViewer;
                    //$reachp = ($numofViewer / $numOfUser) * 100;
                    //$reach0=$numofViewer;
                    unset($viewer);
                    $viewer = array();
                    array_push($reachs, $reachp);
                    array_push($channelArray, $c->channel_name);
                } else {
                    array_push($reachs, 0);
                    array_push($channelArray, $c->channel_name);
                    //array_push($ds, $dd);
                }
            }
        }

        return response()->json(["dd" => $ds, "label" => $channelArray, "value" => $reachs], 200);
    }



    public function tvrp(Request $req)
    {
        $tv = 0;
        $dd = 0;
        $tr = array();
        $df = array();
        $ds = array();
        $tvrs = array();
        $channelArray = array();
        $viewer = array();
        $ldate = date('H:i:s');
        $users = User::all();
        $numOfUser = $users->count();
        $start = $req->start;
        if ($start == "") {
            $start = "00:00:00";
        }
        $finish = $req->finish;
        if ($finish == "") {
            $finish = "23:59:59";
        }
        $diff = abs(strtotime($start) - strtotime($finish)) / 60;
        $month = $req->month;
        if (empty($month)) {
            $month = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12');
        }
        //$month = implode(",", $month);
        $year = $req->year;
        if (empty($year)) {
            $year = array('2022', '2021', '2020', '2019', '2018');
        }
        //$year = implode(",", $year);
        $day = $req->day;
        if (empty($day)) {
            $day = array('0', '1', '2', '3', '4', '5', '6');
        }
        //$day = implode(",", $day);

        $tr = $this->getrange($year, $month, $day, $start, $finish);

        //    return response()->json(["range" => $tr], 200);


        $channels = Channel::all('id', 'channel_name');
        if ($req->id == "") {
            foreach ($channels as $c) {
                foreach ($tr as $t) {
                    $viewers = ViewLog::where('channel_id', $c->id)
                        ->where(function ($query) use ($t) {
                            $query->where('finished_watching_at', '>', date("Y-m-d H:i:s", strtotime($t["start"])))
                                ->orWhereNull('finished_watching_at');
                        })
                        ->where('started_watching_at', '<', date("Y-m-d H:i:s", strtotime($t["finish"])))
                        ->get();

                    foreach ($viewers as $v) {
                        if ($v->finished_watching_at == null) {
                            if ((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) {
                                $timeviewd = abs(strtotime($t["start"]) - strtotime($ldate));
                            } else if ((strtotime($v->started_watching_at)) >= (strtotime($t["start"]))) {
                                $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($ldate));
                            }
                        } else if (((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) > (strtotime($t["finish"])))) {
                            $timeviewd = abs(strtotime($t["start"]) - strtotime($t["finish"]));
                        } else if (((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) <= (strtotime($t["finish"])))) {
                            $timeviewd = abs(strtotime($t["start"]) - strtotime($v->finished_watching_at));
                        } else if (((strtotime($v->started_watching_at)) >= (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) > (strtotime($t["finish"])))) {
                            $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($t["finish"]));
                        } else {
                            $timeviewd = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                        }
                        //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                        $timeviewd = $timeviewd / 60;
                        $tv = $tv + $timeviewd;
                    }

                    $dd = $dd + abs(strtotime($t["start"]) - strtotime($t["finish"]));
                }
                $dd = $dd / 60;
                $tv = $tv / $numOfUser;

                $tv = $tv / $dd;
                $tv = $tv * 100;
                array_push($channelArray, $c->channel_name);
                array_push($tvrs, $tv);
                array_push($ds, $dd);
                $tv = 0;
                $dd = 0;
            }
        } else {
            foreach ($channels as $c) {
                if ($c->id == ((int)$req->id)) {
                    foreach ($tr as $t) {
                        $viewers = ViewLog::where('channel_id', $c->id)
                            ->where(function ($query) use ($t) {
                                $query->where('finished_watching_at', '>', date("Y-m-d H:i:s", strtotime($t["start"])))
                                    ->orWhereNull('finished_watching_at');
                            })
                            ->where('started_watching_at', '<', date("Y-m-d H:i:s", strtotime($t["finish"])))
                            ->get();
                        foreach ($viewers as $v) {
                            if ($v->finished_watching_at == null) {
                                if ((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) {
                                    $timeviewd = abs(strtotime($t["start"]) - strtotime($ldate));
                                } else if ((strtotime($v->started_watching_at)) >= (strtotime($t["start"]))) {
                                    $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($ldate));
                                }
                            } else if (((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) > (strtotime($t["finish"])))) {
                                $timeviewd = abs(strtotime($t["start"]) - strtotime($t["finish"]));
                            } else if (((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) <= (strtotime($t["finish"])))) {
                                $timeviewd = abs(strtotime($t["start"]) - strtotime($v->finished_watching_at));
                            } else if (((strtotime($v->started_watching_at)) >= (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) > (strtotime($t["finish"])))) {
                                $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($t["finish"]));
                            } else {
                                $timeviewd = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                            }
                            //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                            $timeviewd = $timeviewd / 60;
                            $tv = $tv + $timeviewd;
                        }
                        $dd = $dd + abs(strtotime($t["start"]) - strtotime($t["finish"]));
                    }
                    $dd = $dd / 60;
                    $tv = $tv / $numOfUser;
                    $tv = $tv / $dd;
                    $tv = $tv * 100;
                    array_push($channelArray, $c->channel_name);
                    array_push($tvrs, $tv);
                    array_push($ds, $dd);
                    $tv = 0;
                    $dd = 0;
                } else {
                    array_push($tvrs, 0);
                    array_push($channelArray, $c->channel_name);
                    array_push($ds, $dd);
                }
            }
        }

        return response()->json(["dd" => $ds, "label" => $channelArray, "value" => $tvrs], 200);
    }
    public function tvr0(Request $req)
    {
        $tv = 0;
        $dd = 0;
        $tr = array();
        $df = array();
        $ds = array();
        $tvrs = array();
        $channelArray = array();
        $viewer = array();
        $ldate = date('H:i:s');
        $users = User::all();
        $numOfUser = $users->count();
        $start = $req->start;
        if ($start == "") {
            $start = "00:00:00";
        }
        $finish = $req->finish;
        if ($finish == "") {
            $finish = "23:59:59";
        }
        $diff = abs(strtotime($start) - strtotime($finish)) / 60;
        $month = $req->month;
        if (empty($month)) {
            $month = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12');
        }
        //$month = implode(",", $month);
        $year = $req->year;
        if (empty($year)) {
            $year = array('2022', '2021', '2020', '2019', '2018');
        }
        //$year = implode(",", $year);
        $day = $req->day;
        if (empty($day)) {
            $day = array('0', '1', '2', '3', '4', '5', '6');
        }
        //$day = implode(",", $day);

        $tr = $this->getrange($year, $month, $day, $start, $finish);

        //return response()->json(["range" => $tr], 200);


        $channels = Channel::all('id', 'channel_name');
        if ($req->id == "") {
            foreach ($channels as $c) {
                foreach ($tr as $t) {
                    $viewers = ViewLog::where('channel_id', $c->id)
                        ->where(function ($query) use ($t) {
                            $query->where('finished_watching_at', '>', date("Y-m-d H:i:s", strtotime($t["start"])))
                                ->orWhereNull('finished_watching_at');
                        })
                        ->where('started_watching_at', '<', date("Y-m-d H:i:s", strtotime($t["finish"])))
                        ->get();

                    foreach ($viewers as $v) {
                        if ($v->finished_watching_at == null) {
                            if ((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) {
                                $timeviewd = abs(strtotime($t["start"]) - strtotime($ldate));
                            } else if ((strtotime($v->started_watching_at)) >= (strtotime($t["start"]))) {
                                $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($ldate));
                            }
                        } else if (((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) > (strtotime($t["finish"])))) {
                            $timeviewd = abs(strtotime($t["start"]) - strtotime($t["finish"]));
                        } else if (((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) <= (strtotime($t["finish"])))) {
                            $timeviewd = abs(strtotime($t["start"]) - strtotime($v->finished_watching_at));
                        } else if (((strtotime($v->started_watching_at)) >= (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) > (strtotime($t["finish"])))) {
                            $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($t["finish"]));
                        } else {
                            $timeviewd = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                        }
                        //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                        $timeviewd = $timeviewd / 60;
                        $tv = $tv + $timeviewd;
                    }

                    $dd = $dd + abs(strtotime($t["start"]) - strtotime($t["finish"]));
                }
                $dd = $dd / 60;
                $tv = $tv / $numOfUser;

                $tv = $tv / $dd;
                //$tv = $tv * 100;
                array_push($channelArray, $c->channel_name);
                array_push($tvrs, $tv);
                array_push($ds, $dd);
                $tv = 0;
                $dd = 0;
            }
        } else {
            foreach ($channels as $c) {
                if ($c->id == ((int)$req->id)) {
                    foreach ($tr as $t) {
                        $viewers = ViewLog::where('channel_id', $c->id)
                            ->where(function ($query) use ($t) {
                                $query->where('finished_watching_at', '>', date("Y-m-d H:i:s", strtotime($t["start"])))
                                    ->orWhereNull('finished_watching_at');
                            })
                            ->where('started_watching_at', '<', date("Y-m-d H:i:s", strtotime($t["finish"])))
                            ->get();
                        foreach ($viewers as $v) {
                            if ($v->finished_watching_at == null) {
                                if ((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) {
                                    $timeviewd = abs(strtotime($t["start"]) - strtotime($ldate));
                                } else if ((strtotime($v->started_watching_at)) >= (strtotime($t["start"]))) {
                                    $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($ldate));
                                }
                            } else if (((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) > (strtotime($t["finish"])))) {
                                $timeviewd = abs(strtotime($t["start"]) - strtotime($t["finish"]));
                            } else if (((strtotime($v->started_watching_at)) < (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) <= (strtotime($t["finish"])))) {
                                $timeviewd = abs(strtotime($t["start"]) - strtotime($v->finished_watching_at));
                            } else if (((strtotime($v->started_watching_at)) >= (strtotime($t["start"]))) && ((strtotime($v->finished_watching_at)) > (strtotime($t["finish"])))) {
                                $timeviewd = abs(strtotime($v->started_watching_at) - strtotime($t["finish"]));
                            } else {
                                $timeviewd = abs(strtotime($v->finished_watching_at) - strtotime($v->started_watching_at));
                            }
                            //$timeviewd=abs(strtotime($v->finished_watching_at)-strtotime($v->started_watching_at));
                            $timeviewd = $timeviewd / 60;
                            $tv = $tv + $timeviewd;
                        }
                        $dd = $dd + abs(strtotime($t["start"]) - strtotime($t["finish"]));
                    }
                    $dd = $dd / 60;
                    $tv = $tv / $numOfUser;
                    $tv = $tv / $dd;
                    //$tv = $tv * 100;
                    array_push($channelArray, $c->channel_name);
                    array_push($tvrs, $tv);
                    array_push($ds, $dd);
                    $tv = 0;
                    $dd = 0;
                } else {
                    array_push($tvrs, 0);
                    array_push($channelArray, $c->channel_name);
                    array_push($ds, $dd);
                }
            }
        }

        return response()->json(["dd" => $ds, "label" => $channelArray, "value" => $tvrs], 200);
    }

    function getrange($year, $month, $week,  $s, $f)
    {
        $tr = array();
        foreach ($year as $y) {
            foreach ($month as $m) {
                $d = cal_days_in_month(CAL_GREGORIAN, ((int)$month), ((int)$year));
                for ($ii = 1; $ii <= $d; $ii++) {
                    if (in_array(date("w", strtotime("$y-$m-$ii")), ($week))) {
                        //echo "$y-$m-$ii $s    $y-$m-$ii $f"."<br/>";
                        $tr[] = array("start" => "$y-$m-$ii $s", "finish" => "$y-$m-$ii $f");
                    }
                }
            }
        }
        return $tr;
    }
}
