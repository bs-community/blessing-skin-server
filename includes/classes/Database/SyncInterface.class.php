<?php
/**
 * @Author: printempw
 * @Date:   2016-03-13 13:31:28
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-18 17:23:33
 */

namespace Database;

interface SyncInterface
{
    /**
     * Synchronize records between tables of bs and other programs
     *
     * @param  string $username, unique identifier of each record
     * @return bool
     */
    public function sync($username);

}
