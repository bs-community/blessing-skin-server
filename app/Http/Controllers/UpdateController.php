<?php

namespace App\Http\Controllers;

use Log;
use File;
use Cache;
use Storage;
use Exception;
use ZipArchive;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Composer\Semver\Comparator;
use App\Services\PackageManager;

class UpdateController extends Controller
{
    /**
     * Current application version.
     *
     * @var string
     */
    protected $currentVersion;

    /**
     * Latest application version in update source.
     *
     * @var string
     */
    protected $latestVersion;

    /**
     * Where to get information of new application versions.
     *
     * @var string
     */
    protected $updateSource;

    /**
     * Updates information fetched from update source.
     *
     * @var array|null
     */
    protected $updateInfo;

    /**
     * Guzzle HTTP client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $guzzle;

    public function __construct(\GuzzleHttp\Client $guzzle)
    {
        $this->updateSource = config('app.update_source');
        $this->currentVersion = config('app.version');

        $this->guzzle = $guzzle;
    }

    public function showUpdatePage()
    {
        $info = [
            'latest_version'  => '',
            'current_version' => $this->currentVersion,
            'release_note'    => '',
            'release_url'     => '',
            'pre_release'     => false,
            // Fallback to current time
            'release_time'    => '',
            'new_version_available' => false,
        ];

        // If current update source is available
        if ($this->getUpdateInfo()) {
            $info['latest_version'] = $this->getUpdateInfo('latest_version');

            $info['new_version_available'] = Comparator::greaterThan(
                $info['latest_version'],
                $info['current_version']
            );

            if ($detail = $this->getReleaseInfo($info['latest_version'])) {
                $info = array_merge($info, Arr::only($detail, [
                    'release_note',
                    'release_url',
                    'release_time',
                    'pre_release',
                ]));
            } else {
                // if detailed release info is not given
                $info['new_version_available'] = false;
            }

            if (! $info['new_version_available']) {
                $info['release_time'] = Arr::get($this->getReleaseInfo($this->currentVersion), 'release_time');
            }
        }

        $connectivity = true;

        try {
            $this->guzzle->request('GET', $this->updateSource);
        } catch (Exception $e) {
            $connectivity = $e->getMessage();
        }

        $extra = ['canUpdate' => $info['new_version_available']];
        return view('admin.update', compact('info', 'connectivity', 'extra'));
    }

    public function checkUpdates()
    {
        return json([
            'latest' => $this->getUpdateInfo('latest_version'),
            'available' => $this->newVersionAvailable(),
        ]);
    }

    protected function newVersionAvailable()
    {
        $latest = $this->getUpdateInfo('latest_version');

        return Comparator::greaterThan($latest, $this->currentVersion) && $this->getReleaseInfo($latest);
    }

    public function download(Request $request, PackageManager $package)
    {
        if (! $this->newVersionAvailable()) {
            return json([]);
        }

        $url = $this->getReleaseInfo($this->latestVersion)['release_url'];
        $path = storage_path('packages/bs_'.$this->latestVersion.'.zip');
        switch ($request->get('action')) {
            case 'download':
                try {
                    $package->download($url, $path)->extract(base_path());
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

    protected function getUpdateInfo($key = null)
    {
        if (! $this->updateInfo) {
            // Add timestamp to control cdn cache
            $url = starts_with($this->updateSource, 'http')
                ? $this->updateSource.'?v='.substr(time(), 0, -3)
                : $this->updateSource;

            try {
                $response = $this->guzzle->request('GET', $url)->getBody();
            } catch (Exception $e) {
                Log::error('[CheckingUpdate] Failed to get update information: '.$e->getMessage());
            }

            if (isset($response)) {
                $this->updateInfo = json_decode($response, true);
            }
        }

        $this->latestVersion = Arr::get($this->updateInfo, 'latest_version', $this->currentVersion);

        if (! is_null($key)) {
            return Arr::get($this->updateInfo, $key);
        }

        return $this->updateInfo;
    }

    protected function getReleaseInfo($version)
    {
        return Arr::get($this->getUpdateInfo('releases'), $version);
    }
}
