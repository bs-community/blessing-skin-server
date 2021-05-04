<?php

namespace App\Http\Controllers;

use App\Services\Unzip;
use Cache;
use Composer\CaBundle\CaBundle;
use Composer\Semver\Comparator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class UpdateController extends Controller
{
    public const SPEC = 2;

    public function showUpdatePage()
    {
        $info = $this->getUpdateInfo();
        $canUpdate = $this->canUpdate(Arr::get($info, 'info'));

        return view('admin.update', [
            'info' => [
                'latest' => Arr::get($info, 'info.latest'),
                'current' => config('app.version'),
            ],
            'error' => Arr::get($info, 'error', $canUpdate['reason']),
            'can_update' => $canUpdate['can'],
        ]);
    }

    public function download(Unzip $unzip, Filesystem $filesystem)
    {
        $info = $this->getUpdateInfo();
        if (!$info['ok'] || !$this->canUpdate($info['info'])['can']) {
            return json(trans('admin.update.info.up-to-date'), 1);
        }

        $info = $info['info'];
        $path = tempnam(sys_get_temp_dir(), 'bs');

        $response = Http::withOptions([
            'sink' => $path,
            'verify' => CaBundle::getSystemCaRootBundlePath(),
        ])->get($info['url']);

        if ($response->ok()) {
            $unzip->extract($path, base_path());

            // Delete options cache. This allows us to update the version.
            $filesystem->delete(storage_path('options.php'));

            return json(trans('admin.update.complete'), 0);
        } else {
            return json(trans('admin.download.errors.download', ['error' => $response->status()]), 1);
        }
    }

    protected function getUpdateInfo()
    {
        $response = Http::withOptions([
            'verify' => CaBundle::getSystemCaRootBundlePath(),
        ])->get(config('app.update_source'));

        if ($response->ok()) {
            $info = $response->json();
            if (Arr::get($info, 'spec') === self::SPEC) {
                return ['ok' => true, 'info' => $info];
            } else {
                return ['ok' => false, 'error' => trans('admin.update.errors.spec')];
            }
        } else {
            return ['ok' => false, 'error' => 'HTTP status code: '.$response->status()];
        }
    }

    protected function canUpdate($info = [])
    {
        $php = Arr::get($info, 'php');
        preg_match('/(\d+\.\d+\.\d+)/', PHP_VERSION, $matches);
        $version = $matches[1];
        if (Comparator::lessThan($version, $php)) {
            return [
                'can' => false,
                'reason' => trans('admin.update.errors.php', ['version' => $php]),
            ];
        }

        $can = Comparator::greaterThan(Arr::get($info, 'latest'), config('app.version'));

        return ['can' => $can, 'reason' => ''];
    }
}
