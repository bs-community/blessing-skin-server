<?php
require "./config.php";
$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWD);

if (!$con) {
    die ("Can not connect to mysql, check if database info correct.".mysql_error());
}
mysql_select_db(DB_NAME, $con);

function getToken($uname) {
    global $con;
    // Simple SQL injection protection
    $uname = strtolower(stripslashes(trim($_POST['uname']))); 
    $uname = mysql_real_escape_string($uname);
    $query = mysql_query("SELECT * FROM users where username='$uname'", $con); 
    $row = mysql_fetch_array($query);
    return md5($row['uname'].$row['passwd'].SALT);
    mysql_close($con);
}

function checkToken($uname, $token) {
    $uname = strtolower(stripslashes(trim($_POST['uname']))); 
    $uname = mysql_real_escape_string($uname);
    if ($token != getToken($uname)){
        $arr['success'] = 0;
        $arr['msg'] = "Invalid Token: ".$token;
    } else {
        $arr['success'] = 1; 
        $arr['msg'] = 'Valid Token.';
    }
    return $arr;
}

function checkPasswd($uname, $rawPasswd) {
    global $con;
    $uname = strtolower(stripslashes(trim($_POST['uname']))); 
    $uname = mysql_escape_string($uname);
    $query = mysql_query("SELECT * FROM users where username='$uname'", $con); 
    $row = mysql_fetch_array($query);
    
    if (!$row['password']) {
        $arr['success'] = 0; 
        $arr['msg'] = "Non-existent user.";
    } else {
        if ($row['password'] == $rawPasswd) {
            $arr['success'] = 1; 
            $arr['msg'] = 'Logging in succeed!'; 
            $arr['token'] = getToken();
        } else {
            $arr['success'] = 0; 
            $arr['msg'] = "Incorrect usename or password.";
        }
    }
    return $arr;
    mysql_close($con);
}

function register($uname, $passwd, $ip) {
    global $con;
    $uname = strtolower(stripslashes(trim($_POST['uname']))); 
    $uname = mysql_real_escape_string($uname);
    $query = mysql_query("SELECT * FROM users where username='$uname'", $con); 
    $row = mysql_fetch_array($query);
    
    if (!$row['password']) {
        
        $ipQuery = mysql_query("SELECT * FROM users where ip='$ip'", $con);
        $ipRow = mysql_fetch_array($ipQuery);
        
        if(!$ipRow['username']) {
            mysql_query("INSERT INTO users (username, password, ip) VALUES ('$uname', '$passwd', '$ip')", $con);
            $arr['success'] = 1; 
            $arr['msg'] = "Registered successfully.";
        } else {
            $arr['success'] = 0; 
            $arr['msg'] = "It seems that you have already register a account with this IP address.";
        }
    } else {
        $arr['success'] = 0; 
        $arr['msg'] = "User already existed.";
    }
    return $arr;
    mysql_close($con);
}
?>
