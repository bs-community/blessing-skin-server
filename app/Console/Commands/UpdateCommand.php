<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateCommand extends Command
{
    protected $signature = 'update';

    protected $description = 'Execute update.';

    public function handle()
    {
        app()->call('App\Http\Controllers\UpdateController@update');
        $this->info(trans('setup.updates.success.title'));
    }
}
