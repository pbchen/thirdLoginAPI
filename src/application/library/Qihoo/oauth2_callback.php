<?php
session_start();
require_once(dirname(__FILE__).'/QClient.php');
require_once(dirname(__FILE__).'/config.php');
if (array_key_exists('code', $_GET)) {
    $connect = new QOAuth2(APPKEY, // api key
                        SECRET, // app secret
                        ''); // access token

    $response = $connect->getAccessTokenByCode($_GET['code'], REDIRECT);
    if(isset($response['access_token'])) {
        $_SESSION['access_token']  = $response['access_token'];
        $_SESSION['refresh_token'] = $response['refresh_token'];
        header("Location:testapi.php");
        exit;
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<script>
window.onload = function() {
    if(window.location.hash != undefined) {
        var token = window.location.hash.split("&")[0].split("=")[1];
        if(token != undefined) {
            window.document.getElementById('implicit').innerHTML = '您使用了implicit模式，accesst token为'+token+'此流程仅使用于纯前端应用，例如纯js应用！';
        }
    }
}
</script>
<body>
<div id="implicit"></div>
<a href="testapi.php">回到testapi页</a>
</body>