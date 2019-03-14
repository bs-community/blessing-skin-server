<?php

namespace App\Console\Commands;

use DB;
use Schema;
use App\Models\User;
use Illuminate\Console\Command;

class MigrateCloset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bs:migrate-v4:closet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the closet for v4';

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
        if (!Schema::hasTable('closets')) {
            $this->info('Nothing to do.');
            return;
        }

        $this->info('We will migrate all closets data. Please wait...');

        $rows = DB::table('closets')->select('*')->get();
        $bar = $this->output->createProgressBar($rows->count());

        $rows->map(function ($row) use ($bar) {
            $closet = User::find($row->uid)->closet();
            collect(json_decode($row->textures, true))->each(function ($item) use ($closet) {
                $closet->attach($item['tid'], ['item_name' => $item['name']]);
            });
            $bar->advance();
        });

        Schema::drop('closets');
        $bar->finish();
        $this->info("\nCongrats! Everything are done.");
    }
}
