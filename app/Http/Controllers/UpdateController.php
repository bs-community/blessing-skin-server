<?php

namespace App\Http\Controllers;

use Arr;
use Log;
use Utils;
use Option;
use ZipArchive;
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
            'release_time'    => '',
            'new_version_available' => false
        ];

        // if current update source is available
        if ($this->getUpdateInfo()) {
            $info['latest_version'] = $this->getUpdateInfo('latest_version');

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

            if (!$info['new_version_available']) {
                $info['release_time'] = Arr::get($this->getReleaseInfo($this->currentVersion), 'release_time');
            }
        }

        $update = Option::form('update', '更新选项', function($form)
        {
            $form->checkbox('check_update', '检查更新')->label('自动检查更新并提示');
            $form->text('update_source', '更新源')
                ->description('可用的更新源列表可以在这里查看：<a href="https://github.com/printempw/blessing-skin-server/wiki/%E6%9B%B4%E6%96%B0%E6%BA%90%E5%88%97%E8%A1%A8">@GitHub Wiki</a>');
        })->handle()->always(function($form) {
            try {
                $response = file_get_contents(option('update_source'));
            } catch (\Exception $e) {
                $form->addMessage('无法访问当前更新源。详细信息：'.$e->getMessage(), 'danger');
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

                if (!file_exists($tmp_path)) exit('No file available');

                $extract_dir = storage_path("update_cache/{$this->latestVersion}");

                $zip = new ZipArchive();
                $res = $zip->open($tmp_path);

                if ($res === true) {
                    Log::info("[ZipArchive] Extracting file $tmp_path");

                    try {
                        $zip->extractTo($extract_dir);
                    } catch (\Exception $e) {
                        exit('发生错误：'.$e->getMessage());
                    }

                } else {
                    exit('更新包解压缩失败。错误代码：'.$res);
                }
                $zip->close();

                if (Storage::copyDir($extract_dir, base_path()) !== true) {

                    Storage::removeDir(storage_path('update_cache'));
                    exit('无法覆盖文件。');

                } else {

                   Log::info("[Extracter] Covering files");
                   Storage::removeDir(storage_path('update_cache'));

                   Log::info("[Extracter] Cleaning cache");
                }

                return json('更新完成', 0);

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
