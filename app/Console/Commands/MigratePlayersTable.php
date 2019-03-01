<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class MigratePlayersTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bs:migrate-v4:players-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the players table for v4';

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
     * @return mixed
     */
    public function handle()
    {
        $table = env('DB_PREFIX') . 'players';

        $this->info('We are going to update your `players` table.');
        $this->comment('This will take a moment. Please wait...');

        $count = DB::update("UPDATE $table SET tid_skin=IF(preference='slim', tid_alex, tid_steve) WHERE tid_skin=-1");

        $this->info("Congratulations! We've updated $count rows.");
    }
}
