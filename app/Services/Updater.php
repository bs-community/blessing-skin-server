<?php

namespace App\Services;

use Blessing\Storage;
use Option;

class Updater
{
    /**
     * Current version
     *
     * @var string
     */
    public $current_version = "";

    /**
     * Latest version
     *
     * @var string
     */
    public $latest_version  = "";

    /**
     * Latest update time in Y-m-d H:i:s format
     *
     * @var string
     */
    public $update_time     = "";

    /**
     * See /config/update.php
     *
     * @var array
     */
    private $update_sources = null;

    /**
     * Current selected update source
     *
     * @var array
     */
    private $current_source = null;

    /**
     * Details of updates
     *
     * @var array
     */
    private $update_info    = null;

    public function __construct($current_version)
    {
        $this->current_version = $current_version;
        $this->update_sources  = require BASE_DIR."/config/update.php";

        $source = Option::get('update_source');

        if (!isset($this->update_sources[$source])) {
            Option::set('update_source', config('options.update_source'));
        }

        $this->current_source  = $this->update_sources[Option::get('update_source')];
    }

    /**
     * Get update info from selected json source
     *
     * @return array Decoded json
     */
    public function getUpdateInfo()
    {
        $ch = curl_init();
        // add timestamp to control cdn cache
        $url = $this->current_source['update_url']."?v=".substr(time(), 0, -3);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // quick fix for accessing https resources
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);

        $this->update_info = json_decode($result, true);
        return $this->update_info;
    }

    /**
     * Check for updates
     *
     * @return void
     */
    public function checkUpdate()
    {
        $info = $this->getUpdateInfo();

        $this->latest_version = $info['latest_version'];
        $this->update_time    = date('Y-m-d H:i:s', $info['update_time']);
    }

    /**
     * Download update files
     *
     * @param  bool $silent Don't print messages
     * @return void
     */
    public function downloadUpdate($silent = true)
    {
        $release_url = $this->update_info['releases'][$this->latest_version]['release_url'];

        if (!$silent)
            echo "<p>正在下载更新包：$release_url </p>";

        // I don't know why curl can't get full file content here..
        $file = file_get_contents($release_url);

        if (!$silent)
            echo "<p>下载完成。</p>";

        $update_cache = BASE_DIR."/setup/update_cache/";

        if (!is_dir($update_cache)) {
            if (false === mkdir($update_cache)) {
                exit('<p>创建下载缓存文件夹失败，请检查目录权限。</p>');
            }
        }

        $zip_path = $update_cache."update_".time().".zip";

        if (Storage::put($zip_path, $file) === false) {
            Storage::removeDir(BASE_DIR.'/setup/update_cache/');
            return false;
        }

        return $zip_path;
    }

    /**
     * Check if a new version is available
     *
     * @return bool
     */
    public function newVersionAvailable()
    {
        $this->checkUpdate();
        return $this->compareVersion($this->latest_version, $this->current_version);
    }

    public function getUpdateSources()
    {
        return $this->update_sources;
    }

    /**
     * Compare version string
     *
     * @param  string $v1
     * @param  string $v2
     * @return boolean
     */
    private function compareVersion($v1, $v2)
    {
        if (version_compare($v1, $v2) > 0) {
            // v1 > v2
            return true;
        } else {
            // v1 < v2 || v1 = v2
            return false;
        }
    }
}
