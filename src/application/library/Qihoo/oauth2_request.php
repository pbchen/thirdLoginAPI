<?php
session_start();
// load library. 
require_once(dirname(__FILE__).'/QClient.php');
require_once(dirname(__FILE__).'/config.php');

// Build QClient object. 
$connection = new QOAuth2(APPKEY, // api key
                        SECRET,  // app secret
                        ''); // access token
    
$scope = 'basic';
        
$opt = $_POST['oauth2'];
if(!empty($opt)) {
    if ("authorization_code" === $opt) {
        $url = $connection->getAuthorizeURL('code', REDIRECT, $scope);
        header("Location:$url");
        exit;
    }
    elseif ("implicit_grant" === $opt) {
        $url = $connection->getAuthorizeURL('token', REDIRECT, $scope);
        header("Location:$url");
        exit;
    }
    elseif ("refresh_token" === $opt) {
        if(empty($_SESSION['refresh_token'])) {
            $error = "Refresh Token未找到，请在授权后回调时存储获得的Refresh Token";
        }
        $response =  $connection->getAccessTokenByRefreshToken($_SESSION['refresh_token'], $scope);
        if(isset($response['access_token'])) {
            $_SESSION['access_token']  = $response['access_token'];
            $_SESSION['refresh_token'] = $response['refresh_token'];
            header("Location:testapi.php");
            exit;
        } 
    }
    
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<?php if(!empty($error)) {echo $error;} ?><br />
<a href="testapi.php">回到testapi页</a>
</body>