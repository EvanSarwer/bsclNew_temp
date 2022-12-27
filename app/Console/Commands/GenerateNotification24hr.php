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
class GenerateNotification24hr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification2hr:generate';

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
    $day1Before = date('Y-m-d H:i:s', strtotime("-1 days"));
    $day2Before = date('Y-m-d H:i:s', strtotime("-2 days"));
    $today = date('Y-m-d H:i:s');

    $appUser = AppUser::select('app_users.id')->where('login.role', 'admin')
      ->join('login', 'login.user_name', '=', 'app_users.user_name')
      ->get();



    $devices = Device::where('type', "STB")
      ->get();
    if ($devices) {
      foreach ($devices as $d) {
        //if()
        $channelmax = //(int)(
          RawRequest::select('channel_id')
          ->where('device_id', $d->id)
          ->where('start', '>', $day1Before)
          ->groupBy('channel_id')
          ->orderByRaw('COUNT(*) DESC')
          ->limit(1)
          ->first();
        if ($channelmax == null) {
          continue;
        }

        $channelmax = (int)($channelmax->channel_id);
        //return  response()->json(["total" => $channelmax], 200);
        $rawCount = RawRequest::where('start', '>', $day1Before)
          ->where('device_id', $d->id)
          ->count();
        $rawchCount = RawRequest::where('start', '>', $day1Before)
          ->where('channel_id', $channelmax)
          ->where('device_id', $d->id)
          ->count();

        //return response()->json(["total" => $channelmax,"c"=>$rawCount,"ch"=>$rawchCount,"ok"=>"not","1d"=>$day1Before], 200);
        if ($rawCount > 0) {
          if ($rawchCount / $rawCount >= 0.98) {
            //return response()->json(["total" => $rawCount,"error" => $rawchCount,"ok"=>"not"], 200);
            $check_noti = Notification::where('flag', 6)->where('du_id', $d->id)->where('created_at', '>', $day2Before)->orWhere('created_at', $day2Before)->first();    //
            //return response()->json(["data" => $check_noti], 200);
            if (!$check_noti) {
              Notification::where('flag', 6)->where('du_id', $d->id)->where('created_at', '<', $day2Before)->delete();
              foreach ($appUser as $au) {
                $noti = new Notification();
                $noti->user_id = $au->id;
                $noti->flag = 6;                 // Device has not made any requests yet
                $noti->status = 'unseen';
                $noti->du_id = $d->id;
                $noti->du_name = $d->device_name;
                $noti->details = " same channel 24 hours";
                $noti->created_at = new Datetime();
                $noti->save();
              }
            }
          }
        }
        //return response()->json(["total" => $rawCount,"error" => $rawchCount], 200);

      }
    }
  }
}
