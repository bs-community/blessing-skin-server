<?php
/**
 * @Author: printempw
 * @Date:   2016-03-27 11:04:14
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 15:53:59
 */

class E extends Exception
{
    /**
     * Custom error handler
     *
     * @param string  $message
     * @param integer $code
     * @param boolean $render, to show a error page
     */
    function __construct($message = "Error occured.", $code = -1, $render = false) {
        parent::__construct($message, $code);
        if ($render) {
            $this->showErrorPage();
        } else {
            $this->showErrorJson();
        }
    }

    private function showErrorJson() {
        $exception['errno'] = $this->code;
        $exception['msg'] = $this->message;
        @header('Content-type: application/json; charset=utf-8');
        exit(json_encode($exception));
    }

    private function showErrorPage() {
        $message = $this->message;
        $code = $this->code;
        require dirname(dirname(__FILE__))."/templates/error.tpl.php";
        exit;
    }
}
