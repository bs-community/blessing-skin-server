<?php

namespace App\Http\Controllers;

use Arr;
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
    public $currentVersion;

    public $latestVersion;

    public $updateSource;

    protected $updateInfo;

    public function __construct()
    {
        $this->updateSource = option('update_source');

        $this->currentVersion = config('app.version');
    }

    public function showUpdatePage()
    {
        $this->refreshInfo();

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
                $info = array_merge($info, Arr::only($detail, [
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
                $info['release_time'] = Arr::get($this->getReleaseInfo($this->currentVersion), 'release_time');
            }
        }

        $update = Option::form('update', OptionForm::AUTO_DETECT, function($form)
        {
            $form->checkbox('check_update', OptionForm::AUTO_DETECT)->label(OptionForm::AUTO_DETECT);
            $form->text('update_source', OptionForm::AUTO_DETECT)
                ->description(OptionForm::AUTO_DETECT);
        })->handle()->always(function($form) {
            try {
                $response = file_get_contents(option('update_source'));
            } catch (Exception $e) {
                $form->addMessage(trans('admin.update.errors.connection').$e->getMessage(), 'danger');
            }
        });

        return view('admin.update')->with('info', $info)->with('update', $update);
    }

    public function checkUpdates()
    {
        $this->refreshInfo();

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
        $this->refreshInfo();

        if (! $this->newVersionAvailable())
            return;

        $action = $request->get('action');
        $release_url = $this->getReleaseInfo($this->latestVersion)['release_url'];
        $tmp_path = Cache::get('tmp_path');

        $client = new \GuzzleHttp\Client();
        $guzzle_config = [
            'headers' => ['User-Agent' => config('secure.user_agent')],
            'verify' => config('secure.certificates')
        ];

        switch ($action) {
            case 'prepare-download':

                Cache::forget('download-progress');
                $update_cache = storage_path('update_cache');

                if (! is_dir($update_cache)) {
                    if (false === Storage::disk('root')->makeDirectory('storage/update_cache')) {
                        return response(trans('admin.update.errors.write-permission'));
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
                    return 'No temp path available, please try again.';
                }

                @set_time_limit(0);
                $GLOBALS['last_downloaded'] = 0;

                Log::info('[Update Wizard] Start downloading update package');

                try {
                    $client->request('GET', $release_url, array_merge($guzzle_config, [
                        'sink' => $tmp_path,
                        'progress' => function ($total, $downloaded) {
                            if ($total == 0) return;
                            // Log current progress per 100 KiB
                            if ($total == $downloaded || floor($downloaded / 102400) > floor($GLOBALS['last_downloaded'] / 102400)) {
                                $GLOBALS['last_downloaded'] = $downloaded;
                                Log::info('[Update Wizard] Download progress (in bytes):', [$total, $downloaded]);
                                Cache::put('download-progress', compact('total', 'downloaded'), 60);
                            }
                        }
                    ]));
                } catch (Exception $e) {
                    @unlink($tmp_path);
                    return response(trans('admin.update.errors.prefix').$e->getMessage());
                }

                Log::info('[Update Wizard] Finished downloading update package');

                return json(compact('tmp_path'));

            case 'get-progress':

                return json((array) Cache::get('download-progress'));

            case 'extract':

                if (! file_exists($tmp_path)) {
                    return response('No file available');
                }

                $extract_dir = storage_path("update_cache/{$this->latestVersion}");

                $zip = new ZipArchive();
                $res = $zip->open($tmp_path);

                if ($res === true) {
                    Log::info("[Update Wizard] Extracting file $tmp_path");

                    if ($zip->extractTo($extract_dir) === false) {
                        return response(trans('admin.update.errors.prefix').'Cannot unzip file.');
                    }

                } else {
                    return response(trans('admin.update.errors.unzip').$res);
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
                    return response(trans('admin.update.errors.overwrite').$e->getMessage());
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
                $response = file_get_contents($url);
            } catch (Exception $e) {
                Log::error("[CheckingUpdate] Failed to get update information: ".$e->getMessage());
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

    /**
     * Only used in testing.
     */
    protected function refreshInfo()
    {
        if (config('app.env') == 'testing') {
            $this->updateSource = option('update_source');
            $this->currentVersion = config('app.version');
        }
    }

}
