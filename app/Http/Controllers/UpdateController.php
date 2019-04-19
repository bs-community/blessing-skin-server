<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Composer\Semver\Comparator;
use App\Services\PackageManager;

class UpdateController extends Controller
{
    protected $currentVersion;
    protected $updateSource;
    protected $guzzle;
    protected $error;
    protected $info = [];

    public function __construct(\GuzzleHttp\Client $guzzle)
    {
        $this->updateSource = config('app.update_source');
        $this->currentVersion = config('app.version');
        $this->guzzle = $guzzle;
    }

    public function showUpdatePage()
    {
        $info = [
            'latest'  => Arr::get($this->getUpdateInfo(), 'latest'),
            'current' => $this->currentVersion,
        ];
        $error = $this->error;
        $extra = ['canUpdate' => $this->canUpdate()];

        return view('admin.update', compact('info', 'error', 'extra'));
    }

    public function checkUpdates()
    {
        return json(['available' => $this->canUpdate()]);
    }

    public function download(Request $request, PackageManager $package)
    {
        if (! $this->canUpdate()) {
            return json([]);
        }

        $path = storage_path('packages/bs_'.$this->info['latest'].'.zip');
        switch ($request->get('action')) {
            case 'download':
                try {
                    $package->download($this->info['url'], $path)->extract(base_path());

                    return json(trans('admin.update.complete'), 0);
                } catch (Exception $e) {
                    report($e);

                    return json($e->getMessage(), 1);
                }
            case 'progress':
                return $package->progress();
            default:
                return json(trans('general.illegal-parameters'), 1);
        }
    }

    protected function getUpdateInfo()
    {
        $acceptableSpec = 1;
        if (! $this->info) {
            try {
                $json = $this->guzzle->request(
                    'GET',
                    $this->updateSource,
                    ['verify' => resource_path('misc/ca-bundle.crt')]
                )->getBody();
                $info = json_decode($json, true);
                if (Arr::get($info, 'spec') == $acceptableSpec) {
                    $this->info = $info;
                } else {
                    $this->error = trans('admin.update.spec');
                }
            } catch (Exception $e) {
                $this->error = $e->getMessage();
            }
        }

        return $this->info;
    }

    protected function canUpdate()
    {
        $this->getUpdateInfo();

        return Comparator::greaterThan(Arr::get($this->info, 'latest'), $this->currentVersion);
    }
}
