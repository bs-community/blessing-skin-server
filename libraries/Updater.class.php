<?php
/**
 * @Author: printempw
 * @Date:   2016-03-27 15:16:22
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-27 16:26:07
 */

class Updater
{
    public $current_version = "";
    public $latest_version = "";

    public $update_time = "";

    public $update_url = "https://work.prinzeugen.net/update.json";

    function __construct($current_version) {
        $this->current_version = $current_version;
    }

    public function getUpdateInfo() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->update_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // quick fix for accessing https resources
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    public function checkUpdate() {
        $info = $this->getUpdateInfo();
        $this->latest_version = $info['latest_version'];
        $this->update_time = date('Y-m-d H:i:s', $info['update_time']);
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
