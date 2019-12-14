<?php

namespace App\Http\Controllers;

use App\Services\PackageManager;
use Composer\Semver\Comparator;
use Exception;
use Illuminate\Contracts\Console\Kernel as Artisan;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\Finder\SplFileInfo;

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
            'latest' => Arr::get($this->getUpdateInfo(), 'latest'),
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

    public function download(Request $request, PackageManager $package, Filesystem $filesystem)
    {
        if (!$this->canUpdate()) {
            return json([]);
        }

        $path = storage_path('packages/bs_'.$this->info['latest'].'.zip');
        switch ($request->get('action')) {
            case 'download':
                try {
                    $package->download($this->info['url'], $path)->extract(base_path());

                    // Delete options cache. This allows us to update the version info which is recorded as an option.
                    $filesystem->delete(storage_path('options/cache.php'));

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

    public function update(Filesystem $filesystem, Artisan $artisan)
    {
        collect($filesystem->files(database_path('update_scripts')))
            ->filter(function (SplFileInfo $file) {
                $name = $file->getFilenameWithoutExtension();

                return preg_match('/^\d+\.\d+\.\d+$/', $name) > 0
                    && Comparator::greaterThanOrEqualTo($name, option('version'));
            })
            ->each(function (SplFileInfo $file) use ($filesystem) {
                $filesystem->getRequire($file->getPathname());
            });

        option(['version' => config('app.version')]);
        $artisan->call('migrate', ['--force' => true]);
        $artisan->call('view:clear');
        $filesystem->put(storage_path('install.lock'), '');

        return view('setup.updates.success');
    }

    protected function getUpdateInfo()
    {
        $acceptableSpec = 2;
        if (app()->runningUnitTests() || !$this->info) {
            try {
                $json = $this->guzzle->request(
                    'GET',
                    $this->updateSource,
                    ['verify' => \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath()]
                )->getBody();
                $info = json_decode($json, true);
                if (Arr::get($info, 'spec') == $acceptableSpec) {
                    $this->info = $info;
                } else {
                    $this->error = trans('admin.update.errors.spec');
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

        $php = Arr::get($this->info, 'php');
        preg_match('/(\d+\.\d+\.\d+)/', PHP_VERSION, $matches);
        $version = $matches[1];
        if (Comparator::lessThan($version, $php)) {
            $this->error = trans('admin.update.errors.php', ['version' => $php]);

            return false;
        }

        return Comparator::greaterThan(Arr::get($this->info, 'latest'), $this->currentVersion);
    }
}
