<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\AppUser;
use App\Models\Device;
use App\Models\Notification;
use App\Models\RawRequest;
use App\Models\User;
use App\Models\ViewLog;
use Carbon\Carbon;
use DateTime;
class GenerateNotificationFU extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notificationfu:generate';

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
      ->get();
    if ($devices) {
      foreach ($devices as $d) {

        $users = User::where('device_id', $d->id)->pluck('id')->toArray();
        $viewererror = (int)(ViewLog::selectRaw("sum(TIMESTAMPDIFF(SECOND,started_watching_at,finished_watching_at)) as 'sec'")
          ->whereIn('user_id', $users)->whereIn('channel_id', [888])->where('started_watching_at', '>', $day5Before)->first()->sec);
        $viewertotal = (int)(ViewLog::selectRaw("sum(TIMESTAMPDIFF(SECOND,started_watching_at,finished_watching_at)) as 'sec'")
          ->whereIn('user_id', $users)->where('started_watching_at', '>', $day5Before)->first()->sec);
        //return response()->json(["total" => $viewertotal,"error" => $viewererror/$viewertotal,"ok"=>"not"], 200);
        if ($viewertotal > 0) {
          if ($viewererror / $viewertotal > 0.98) {
            //return response()->json(["total" => $viewertotal,"error" => $viewererror,"ok"=>"not"], 200);
            $check_noti = Notification::where('flag', 5)->where('du_id', $d->id)->where('created_at', '>', $day10Before)->orWhere('created_at', $day10Before)->first();    //
            //return response()->json(["data" => $check_noti], 200);
            if (!$check_noti) {
              Notification::where('flag', 5)->where('du_id', $d->id)->where('created_at', '<', $day10Before)->delete();
              foreach ($appUser as $au) {
                $noti = new Notification();
                $noti->user_id = $au->id;
                $noti->flag = 5;                 // Device has not made any requests yet
                $noti->status = 'unseen';
                $noti->du_id = $d->id;
                $noti->du_name = $d->device_name;
                $noti->details = " to many unknown or foreign in the last 5 days";
                $noti->created_at = new Datetime();
                $noti->save();
              }
            }
          }
        }
        //return response()->json(["total" => $viewertotal,"error" => $viewererror], 200);

      }
    }
  }
}
