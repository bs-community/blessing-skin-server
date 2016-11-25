<?php

namespace App\Http\Controllers;

use Arr;
use Log;
use Utils;
use App\Services\Storage;
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
            // fallback to current time
            'release_time'    => time(),
            'new_version_available' => false
        ];

        // if current update source is available
        if ($this->getUpdateInfo()) {
            $info['latest_version'] = $this->getUpdateInfo('latest_version');

            if ($current_release = $this->getReleaseInfo($this->currentVersion)) {
                $info['release_time'] = Arr::get($current_release, 'release_time') ?: time();
            }

            $info['new_version_available'] = version_compare(
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
        }


        return view('admin.update')->with('info', $info);
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

        return version_compare($latest, $this->currentVersion, '>') && $this->getReleaseInfo($latest);
    }

    public function download(Request $request)
    {
        $action = $request->input('action');

        if (!$this->newVersionAvailable()) return;

        $release_url = $this->getReleaseInfo($this->latestVersion)['release_url'];
        $file_size   = Utils::getRemoteFileSize($release_url);
        $tmp_path    = session('tmp_path');

        switch ($action) {
            case 'prepare-download':

                $update_cache = storage_path('update_cache');

                if (!is_dir($update_cache)) {
                    if (false === mkdir($update_cache)) {
                        exit('创建下载缓存文件夹失败，请检查目录权限。');
                    }
                }

                $tmp_path = $update_cache."/update_".time().".zip";

                session(['tmp_path' => $tmp_path]);

                return json(compact('release_url', 'tmp_path', 'file_size'));

                break;

            case 'start-download':

                if (!session()->has('tmp_path')) return "No temp path is set.";

                try {
                    Utils::download($release_url, $tmp_path);

                } catch (\Exception $e) {
                    Storage::remove($tmp_path);

                    exit('发生错误：'.$e->getMessage());
                }

                return json(compact('tmp_path'));

                break;

            case 'get-file-size':

                if (!session()->has('tmp_path')) return "No temp path is set.";

                if (file_exists($tmp_path)) {
                    return json(['size' => filesize($tmp_path)]);
                }

                break;

            case 'extract':
                //
                break;

            default:
                # code...
                break;
        }
    }

    protected function getUpdateInfo($key = null)
    {
        if (!$this->updateInfo) {
            // add timestamp to control cdn cache
            $url = $this->updateSource."?v=".substr(time(), 0, -3);

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

        if (!is_null($key)) {
            return Arr::get($this->updateInfo, $key);
        }

        return $this->updateInfo;
    }

    protected function getReleaseInfo($version)
    {
        return Arr::get($this->getUpdateInfo('releases'), $version);
    }

}
