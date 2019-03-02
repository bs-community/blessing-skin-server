<?php

namespace App\Console\Commands;

use App\Models\Player;
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
        $players = Player::where('tid_skin', -1)->get();
        $count = $players->count();

        if ($count == 0) {
            $this->info('No need to update.');
            return;
        }

        $this->info('We are going to update your `players` table. Please wait...');
        $bar = $this->output->createProgressBar($count);

        $players->each(function ($player) use ($bar) {
            $player->tid_skin = $player->preference == 'default'
                ? $player->tid_steve
                : $player->tid_alex;
            $player->save();

            $bar->advance();
        });
        $bar->finish();

        $this->info("\nCongratulations! We've updated $count rows.");
    }
}
