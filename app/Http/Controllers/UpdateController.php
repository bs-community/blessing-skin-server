<?php

namespace App\Http\Controllers;

use Log;
use File;
use Cache;
use Option;
use Storage;
use Exception;
use ZipArchive;
use App\Services\OptionForm;
use Illuminate\Http\Request;
use Composer\Semver\Comparator;

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

    /**
     * Default request options for Guzzle HTTP client.
     *
     * @var array
     */
    protected $guzzleConfig;

    public function __construct(\GuzzleHttp\Client $guzzle)
    {
        $this->updateSource = config('app.update_source');
        $this->currentVersion = config('app.version');

        $this->guzzle = $guzzle;
        $this->guzzleConfig = [
            'headers' => ['User-Agent' => config('secure.user_agent')],
            'verify' => config('secure.certificates')
        ];
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
            'new_version_available' => false
        ];

        // If current update source is available
        if ($this->getUpdateInfo()) {
            $info['latest_version'] = $this->getUpdateInfo('latest_version');

            $info['new_version_available'] = Comparator::greaterThan(
                $info['latest_version'],
                $info['current_version']
            );

            if ($detail = $this->getReleaseInfo($info['latest_version'])) {
                $info = array_merge($info, array_only($detail, [
                    'release_note',
                    'release_url',
                    'release_time',
                    'pre_release'
                ]));
            } else {
                // if detailed release info is not given
                $info['new_version_available'] = false;
            }

            if (! $info['new_version_available']) {
                $info['release_time'] = array_get($this->getReleaseInfo($this->currentVersion), 'release_time');
            }
        }

        $connectivity = true;

        try {
            $this->guzzle->request('GET', $this->updateSource, $this->guzzleConfig);
        } catch (Exception $e) {
            $connectivity = $e->getMessage();
        }

        return view('admin.update', compact('info', 'connectivity'));
    }

    public function checkUpdates()
    {
        return json([
            'latest' => $this->getUpdateInfo('latest_version'),
            'available' => $this->newVersionAvailable()
        ]);
    }

    protected function newVersionAvailable()
    {
        $latest = $this->getUpdateInfo('latest_version');

        return Comparator::greaterThan($latest, $this->currentVersion) && $this->getReleaseInfo($latest);
    }

    public function download(Request $request)
    {
        if (! $this->newVersionAvailable())
            return json([]);

        $action = $request->get('action');
        $release_url = $this->getReleaseInfo($this->latestVersion)['release_url'];
        $tmp_path = Cache::get('tmp_path');

        switch ($action) {
            case 'prepare-download':

                Cache::forget('download-progress');
                $update_cache = storage_path('update_cache');

                if (! is_dir($update_cache)) {
                    if (false === Storage::disk('root')->makeDirectory('storage/update_cache')) {
                        return json(trans('admin.update.errors.write-permission'), 1);
                    }
                }

                // Set temporary path for the update package
                $tmp_path = $update_cache.'/update_'.time().'.zip';
                Cache::put('tmp_path', $tmp_path, 60);
                Log::info('[Update Wizard] Prepare to download update package', compact('release_url', 'tmp_path'));

                // We won't get remote file size here since HTTP HEAD method is not always reliable
                return json(compact('release_url', 'tmp_path'));

            case 'start-download':

                if (! $tmp_path) {
                    return json('No temp path available, please try again.', 1);
                }

                @set_time_limit(0);
                $GLOBALS['last_downloaded'] = 0;

                Log::info('[Update Wizard] Start downloading update package');

                try {
                    $this->guzzle->request('GET', $release_url, array_merge($this->guzzleConfig, [
                        'sink' => $tmp_path,
                        'progress' => function ($total, $downloaded) {
                            // @codeCoverageIgnoreStart
                            if ($total == 0) return;
                            // Log current progress per 100 KiB
                            if ($total == $downloaded || floor($downloaded / 102400) > floor($GLOBALS['last_downloaded'] / 102400)) {
                                $GLOBALS['last_downloaded'] = $downloaded;
                                Log::info('[Update Wizard] Download progress (in bytes):', [$total, $downloaded]);
                                Cache::put('download-progress', compact('total', 'downloaded'), 60);
                            }
                            // @codeCoverageIgnoreEnd
                        }
                    ]));
                } catch (Exception $e) {
                    @unlink($tmp_path);
                    return json(trans('admin.update.errors.prefix').$e->getMessage(), 1);
                }

                Log::info('[Update Wizard] Finished downloading update package');

                return json(compact('tmp_path'));

            case 'get-progress':

                return json((array) Cache::get('download-progress'));

            case 'extract':

                if (! file_exists($tmp_path)) {
                    return json('No file available', 1);
                }

                $extract_dir = storage_path("update_cache/{$this->latestVersion}");

                $zip = new ZipArchive();
                $res = $zip->open($tmp_path);

                if ($res === true) {
                    Log::info("[Update Wizard] Extracting file $tmp_path");

                    if ($zip->extractTo($extract_dir) === false) {
                        return json(trans('admin.update.errors.prefix').'Cannot unzip file.', 1);
                    }

                } else {
                    return json(trans('admin.update.errors.unzip').$res, 1);
                }
                $zip->close();

                try {
                    File::copyDirectory("$extract_dir/vendor", base_path('vendor'));
                } catch (Exception $e) {
                    report($e);
                    Log::error('[Update Wizard] Unable to extract vendors');
                    // Skip copying vendor
                    File::deleteDirectory("$extract_dir/vendor");
                }

                try {
                    File::copyDirectory($extract_dir, base_path());

                    Log::info('[Update Wizard] Overwrite with extracted files');

                } catch (Exception $e) {
                    report($e);
                    Log::error('[Update Wizard] Error occured when overwriting files');

                    // Response can be returned, while cache will be cleared
                    // @see https://gist.github.com/g-plane/2f88ad582826a78e0a26c33f4319c1e0
                    return json(trans('admin.update.errors.overwrite').$e->getMessage(), 1);
                } finally {
                    File::deleteDirectory(storage_path('update_cache'));
                    Log::info('[Update Wizard] Cleaning cache');
                }

                Log::info('[Update Wizard] Done');
                return json(trans('admin.update.complete'), 0);

            default:
                return json(trans('general.illegal-parameters'), 1);
        }
    }

    protected function getUpdateInfo($key = null)
    {
        if (! $this->updateInfo) {
            // Add timestamp to control cdn cache
            $url = starts_with($this->updateSource, 'http')
                ? $this->updateSource."?v=".substr(time(), 0, -3)
                : $this->updateSource;

            try {
                $response = $this->guzzle->request('GET', $url, $this->guzzleConfig)->getBody();
            } catch (Exception $e) {
                Log::error("[CheckingUpdate] Failed to get update information: ".$e->getMessage());
            }

            if (isset($response)) {
                $this->updateInfo = json_decode($response, true);
            }
        }

        $this->latestVersion = array_get($this->updateInfo, 'latest_version', $this->currentVersion);

        if (! is_null($key)) {
            return array_get($this->updateInfo, $key);
        }

        return $this->updateInfo;
    }

    protected function getReleaseInfo($version)
    {
        return array_get($this->getUpdateInfo('releases'), $version);
    }

}
