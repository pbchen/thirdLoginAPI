<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
    p.lable        {margin-left: 10px;margin-top: 10px; margin-bottom: 5px; }
    form.style     {margin-left: 10px; }
    a.top          {color: #FF0000; margin-left: 10px;}
    h1.header      {text-align: left; }
    body           {font-family:"Times New Roman"}
</style>

</head>

<body>

<h1 class="header">360 OpenApi PHPSDK v1.0 Example</h1>
<hr /><br />
<a href="index.php" class="top">回到起始页</a>
<h3>API Radio:</h3>

<form name="form" action="testapi.php" method="post" class="style">
      <input type="radio" checked="checked" name="api" value="userMe" /> userMe<br />
      <input type="radio" name="api" value="bind" /> bind<br />
      <input type="radio" name="api" value="unbind" /> unbind<br /><br />
      <p>implicit_grant Access token: <input type="text" name="access_token" /></p>
      <input type="submit" name="submit" value="submit" />
</form><br />

<h3>API Results:</h3>

<?php
// Load required lib files. 
require_once(dirname(__FILE__).'/QClient.php');
require_once(dirname(__FILE__).'/config.php');

// Show Access token and refresh token.
echo "<pre>";
echo '<h4>Token:</h4>';

// Get user access tokens out of the session.
$access_token = '';
if(array_key_exists('access_token', $_SESSION)) {
    $access_token = $_SESSION['access_token'];
    print_r($_SESSION);
}
elseif (!empty($_POST['access_token'])) {
    $access_token = $_POST['access_token'];
    print_r($_POST);
}
else {
    exit;
}

// Create a Oauth2 object 
$connection = new QClient(APPKEY,  // api key
                        SECRET, // app secret
                        $access_token); // access token


// API choose.
switch (!empty($_POST['api']))
{
    case 'userMe':
        $apiResult = $connection->userMe();
        break;

}

// Show Api Result.
if(!empty($apiResult)) {
    echo '<h4>'.$_POST['api'].':</h4>';
    var_dump($apiResult);
}
?>


</body>
</html>
