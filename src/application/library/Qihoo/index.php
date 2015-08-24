<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
    p.topmargin {margin-top: 20px; }
    form.style     {margin-left: 10px; }
    h1.header    {text-align: left; }
    body        {font-family:"Times New Roman"}
</style>

</head>

<body>
<h1 class="header">360 OpenApi PHPSDK v1.0 Example</h1>
<hr />
<h3>oauth2 mode:</h3>
<form name="form" action="oauth2_request.php" method="post" class="style">
      <input type="radio" name="oauth2" value="authorization_code" /> authorization<br />
      <input type="radio" name="oauth2" value="implicit_grant" /> implicit<br />
      <input type="radio" checked="checked" name="oauth2" value="refresh_token" /> refresh <br />
      <input type="radio" name="oauth2" value="pay_order" /> pay order<br /><br />
      <input type="submit" name="submit" value="submit" /><br />
</form>
</body>
</html>
