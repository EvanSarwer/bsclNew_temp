<?php

namespace App\Console\Commands;

use App\Models\AppUser;
use App\Models\Device;
use App\Models\Notification;
use App\Models\RawRequest;
use Illuminate\Console\Command;
use Carbon\Carbon;
use DateTime;

class NotificationGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->generate_notification();
    }


    public function generate_notification()
    {
      $day5Before = date('Y-m-d H:i:s', strtotime("-5 days"));
      $day10Before = date('Y-m-d H:i:s', strtotime("-10 days"));
      $today = date('Y-m-d H:i:s');
      $roles=['admin','operator'];
      $appUser = AppUser::select('app_users.id')->whereIn('login.role',$roles )
        ->join('login', 'login.user_name', '=', 'app_users.user_name')
        ->get();
  
  
  
      $devices = Device::where('type', "STB")
        ->where('last_request', '<', $day5Before)->orWhereNull('last_request')->select("id", "device_name", "last_request", "created_at")->get();
      if ($devices) {
        foreach ($devices as $d) {
          if ($d->last_request == null) {
            $check_noti = Notification::where('flag', 1)->where('du_id', $d->id)->where('created_at', '>', $day10Before)->orWhere('created_at', $day10Before)->first();    //
            //return response()->json(["data" => $check_noti], 200);
            if (!$check_noti) {
              Notification::where('flag', 1)->where('du_id', $d->id)->where('created_at', '<', $day10Before)->delete();
              foreach ($appUser as $au) {
                $noti = new Notification();
                $noti->user_id = $au->id;
                $noti->flag = 1;                 // Device has not made any requests yet
                $noti->status = 'unseen';
                $noti->du_id = $d->id;
                $noti->du_name = $d->device_name;
                $noti->details = " has not made any requests yet.";
                $noti->created_at = new Datetime();
                $noti->save();
              }
            }
          } else {
  
            $check_noti = Notification::where('flag', 2)->where('du_id', $d->id)->where('created_at', '>', $day10Before)->orWhere('created_at', $day10Before)->first();    //
            //return response()->json(["data" => $check_noti], 200);
            if (!$check_noti) {
              Notification::where('flag', 2)->where('du_id', $d->id)->where('created_at', '<', $day10Before)->delete();
              foreach ($appUser as $au) {
                $noti = new Notification();
                $noti->user_id = $au->id;
                $noti->flag = 2;                  // Device offline for more than 5 days
                $noti->status = 'unseen';
                $noti->du_id = $d->id;
                $noti->du_name = $d->device_name;
                $noti->details = " has been offline for more than 5 days.";
                $noti->created_at = new Datetime();
                $noti->save();
              }
            }
          }
        }
      }
  
  
      $allDevices = Device::where('type', "STB")->select("id", "device_name", "last_request")->get();
  
      foreach ($allDevices as $ad) {
        $devicePeople = RawRequest::where('device_id', $ad->id)->whereBetween('server_time', [$ad->last_request, date($ad->last_request, strtotime("-6 days"))])->select("people")->latest('id')->get();
        //return response()->json(["data" => $devicePeople], 200);
        if (count($devicePeople) <= 0) {
          continue;
        }
        $people_value = $devicePeople[0]->people;
        $peopleChangeCount = 0;
        foreach ($devicePeople as $dp) {
  
          if ($dp->people == $people_value) {
            continue;
          } else {
            $peopleChangeCount = $peopleChangeCount + 1;
            break;
          }
        }
  
        if ($peopleChangeCount == 0) {
          $check_noti = Notification::where('flag', 4)->where('du_id', $ad->id)->where('created_at', '>', $day10Before)->orWhere('created_at', $day10Before)->first();
          //return response()->json(["data" => $check_noti], 200);
          if (!$check_noti) {
            Notification::where('flag', 4)->where('du_id', $ad->id)->where('created_at', '<', $day10Before)->delete();
            foreach ($appUser as $au) {
              $noti = new Notification();
              $noti->user_id = $au->id;
              $noti->flag = 4;                  // Device button number in People meter not changed for last 5 days.
              $noti->status = 'unseen';
              $noti->du_id = $ad->id;
              $noti->du_name = $ad->device_name;
              $noti->details = " button number in People meter not changed for last 5 days.";
              $noti->created_at = new Datetime();
              $noti->save();
            }
          }
        }
      }
    }



}
