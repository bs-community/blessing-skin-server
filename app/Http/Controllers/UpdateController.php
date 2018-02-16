<?php

namespace App\Http\Controllers;

use Arr;
use Log;
use Utils;
use File;
use Option;
use Storage;
use ZipArchive;
use App\Services\OptionForm;
use Illuminate\Http\Request;

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

            $info['new_version_available'] = Utils::versionCompare(
                $info['latest_version'],
                $info['current_version'], '>'
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
            } catch (\Exception $e) {
                $form->addMessage(trans('admin.update.errors.connection').$e->getMessage(), 'danger');
            }
        });

        return view('admin.update')->with('info', $info)->with('update', $update);
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

        return Utils::versionCompare($latest, $this->currentVersion, '>') && $this->getReleaseInfo($latest);
    }

    public function download(Request $request)
    {
        $action = $request->input('action');

        if (! $this->newVersionAvailable()) return;

        $release_url = $this->getReleaseInfo($this->latestVersion)['release_url'];
        $file_size   = Utils::getRemoteFileSize($release_url);
        $tmp_path    = session('tmp_path');

        switch ($action) {
            case 'prepare-download':

                $update_cache = storage_path('update_cache');

                if (! is_dir($update_cache)) {
                    if (false === Storage::disk('storage')->makeDirectory('update_cache')) {
                        return response(trans('admin.update.errors.write-permission'));
                    }
                }

                $tmp_path = $update_cache."/update_".time().".zip";

                session(['tmp_path' => $tmp_path]);

                return json(compact('release_url', 'tmp_path', 'file_size'));

            case 'start-download':

                if (! session()->has('tmp_path')) {
                    return "No temp path is set.";
                }

                try {
                    Utils::download($release_url, $tmp_path);

                } catch (\Exception $e) {
                    File::delete($tmp_path);

                    return response(trans('admin.update.errors.prefix').$e->getMessage());
                }

                return json(compact('tmp_path'));

            case 'get-file-size':

                if (! session()->has('tmp_path')) {
                    return "No temp path is set.";
                }

                if (file_exists($tmp_path)) {
                    return json(['size' => filesize($tmp_path)]);
                }

            case 'extract':

                if (! file_exists($tmp_path)) {
                    return response('No file available');
                }

                $extract_dir = storage_path("update_cache/{$this->latestVersion}");

                $zip = new ZipArchive();
                $res = $zip->open($tmp_path);

                if ($res === true) {
                    Log::info("[ZipArchive] Extracting file $tmp_path");

                    if ($zip->extractTo($extract_dir) === false) {
                        return response(trans('admin.update.errors.prefix').'Cannot unzip file.');
                    }

                } else {
                    return response(trans('admin.update.errors.unzip').$res);
                }
                $zip->close();

                try {
                    File::copyDirectory("$extract_dir/vendor", base_path('vendor'));
                } catch (\Exception $e) {
                    Log::error('[Extracter] Unable to extract vendors', [$e]);
                    // Skip copying vendor
                    File::deleteDirectory("$extract_dir/vendor");
                }


                try {
                    File::copyDirectory($extract_dir, base_path());

                    Log::info("[Extracter] Covering files");

                } catch (\Exception $e) {
                    Log::error("[Extracter] Error occured when covering files", [$e]);

                    // Response can be returned, while cache will be cleared
                    // @see https://gist.github.com/g-plane/2f88ad582826a78e0a26c33f4319c1e0
                    return response(trans('admin.update.errors.overwrite').$e->getMessage());
                } finally {
                    File::deleteDirectory(storage_path('update_cache'));
                    Log::info("[Extracter] Cleaning cache");
                }

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
            } catch (\Exception $e) {
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

}
