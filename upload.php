<?php 
require "./connect.php";
$uname = $_COOKIE['uname'];
$token = $_COOKIE['token'];

if ($uname && $token && ($token == getToken($uname))) {
    
    if ($_FILES["skinFile"]) {
        if (($_FILES["skinFile"]["type"] == "image/png")||($_FILES["skinFile"]["type"] == "image/x-png")) {
            if ($_FILES["skinFile"]["error"] > 0) {
                $arr1['success'] = 0; 
                $arr1['msg'] = $_FILES["skinFile"]["error"];
            } else {
                move_uploaded_file($_FILES["skinFile"]["tmp_name"],"uploads/skin/".$_COOKIE['uname'].'.png');
                $arr1['success'] = 1; 
                $arr1['msg'] = 'Uploading succeed!';
            }
        } else {
            $arr1['success'] = 0; 
            $arr1['msg'] = 'File type error.';
        }
    } else {
        $arr1['success'] = 1; 
        $arr1['msg'] = 'No input file selected';
    }
    
    if ($_FILES["capeFile"]) {
        if (($_FILES["capeFile"]["type"] == "image/png")||($_FILES["capeFile"]["type"] == "image/x-png")) {
            if ($_FILES["capeFile"]["error"] > 0) {
                $arr2['success'] = 0; 
                $arr['msg'] = $_FILES["capeFile"]["error"];
            } else {
                move_uploaded_file($_FILES["capeFile"]["tmp_name"],"uploads/cape/".$_COOKIE['uname'].'.png');
                $arr2['success'] = 1; 
                $arr2['msg'] = 'Uploading succeed!';
            }
        } else {
            $arr2['success'] = 0; 
            $arr2['msg'] = 'File type error.';
        }
    } else {
        $arr2['success'] = 1; 
        $arr2['msg'] = 'No input file selected';
    }
// if token is invaild
} else {
    $arr1['success'] = 0;
    $arr2['success'] = 0; 
    $arr2['msg'] = 'Illegal access, invaild token.\n'.$token;
}

echo "[".json_encode($arr1).",".json_encode($arr2)."]";

?>