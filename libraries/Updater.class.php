<?php
/**
 * @Author: printempw
 * @Date:   2016-03-27 15:16:22
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 21:15:23
 */

class Updater
{
    public $current_version = "";
    public $latest_version = "";

    public $update_time = "";

    public $update_url = "";

    public $update_info = null;

    function __construct($current_version) {
        $this->current_version = $current_version;
        $this->update_url = Option::get('update_url');
    }

    public function getUpdateInfo() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->update_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // quick fix for accessing https resources
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        $this->update_info = json_decode($result, true);
        return $this->update_info;
    }

    public function checkUpdate() {
        $info = $this->getUpdateInfo();
        $this->latest_version = $info['latest_version'];
        $this->update_time = date('Y-m-d H:i:s', $info['update_time']);
    }

    public function downloadUpdate($silent = true) {
        $release_url = $this->update_info['releases'][$this->latest_version]['release_url'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $release_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

        if (!$silent) echo "<p>正在下载更新包：$release_url </p>";
        $file = curl_exec($ch);
        if (!$silent) echo "<p>下载完成。</p>";
        curl_close($ch);

        $update_cache = BASE_DIR."/setup/update_cache/";
        if (!is_dir($update_cache)) mkdir($update_cache);

        $zip_path = $update_cache."update_".time().".zip";

        if (file_put_contents($zip_path, $file) === false) {
            Utils::removeDir(BASE_DIR.'/setup/update_cache/');
            return false;

        }
        return $zip_path;
    }

    /**
     * Check if a new version is available
     *
     * @return bool
     */
    public function newVersionAvailable() {
        $this->checkUpdate();
        return $this->compareVersion($this->latest_version, $this->current_version);
    }

    /**
     * Compare version string
     *
     * @param  string $v1
     * @param  string $v2
     * @return boolean
     */
    private function compareVersion($v1, $v2) {
        if (strnatcasecmp($v1, $v2) > 0) {
            // v1 > v2
            return true;
        } else {
            // v1 < v2 || v1 = v2
            return false;
        }
    }
}
