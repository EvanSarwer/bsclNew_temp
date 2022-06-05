<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;
use App\Models\ViewLog;
class ShutDownDevice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shutdown:device';

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
        $this->shutOff();
        //sleep(30);
        //$this->shutOff();
        //$u->last_request=$time;
        //$u->save();
        //
        /*$time = Carbon::now()->toDateTimeString();;
        $time = strtotime($time)-30;
        $u->last_request=$time;
        $u->save();*/
        //return Command::SUCCESS;
    }
    public function shutOff(){
        $time = Carbon::now()->toDateTimeString();;
        $time = date("Y-m-d H:i:s", strtotime($time) - 540);
        $users =User::where('last_request','<',$time)->get();
        foreach($users as $user){
            $user->wrong_time = null;
            $user->wrong_channel = null;
            $v_log = ViewLog::where('user_id',$user->id)
            ->where('finished_watching_at')->first();
            if($v_log){
                $v_log->finished_watching_at = $user->last_request;
                $v_log->duration_minute = 0.00;
                $v_log->save();
            }
        }
    }
}
