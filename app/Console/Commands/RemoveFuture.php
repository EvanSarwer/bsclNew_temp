<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ViewLog;

class RemoveFuture extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:future';

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
        ViewLog::where('started_watching_at', '>', 'finished_watching_at')->delete();
        return 0;
    }
}
