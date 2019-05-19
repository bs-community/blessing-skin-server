<?php

namespace App\Console\Commands;

use App\Models\Texture;
use Illuminate\Console\Command;

class RegressLikesField extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bs:migrate-v4:likes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply fixes for `likes` field of `textures` table.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('We are going to update your `textures` table. Please wait...');
        $textures = Texture::all();
        $bar = $this->output->createProgressBar($textures->count());

        $textures->each(function ($texture) use ($bar) {
            $texture->likes = $texture->likers->count();
            $texture->save();
            $bar->advance();
        });

        $bar->finish();
        $this->info("\nCongratulations! Table was updated successfully.");
    }
}
