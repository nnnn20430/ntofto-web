<?php
//http_request('POST', 'localhost', 80, '/test/compute.php', array(), array('GenNewKeyRequest' => 'true'));
function http_request(
    $verb = 'GET',             /* HTTP Request Method (GET and POST supported) */ 
    $ip,                       /* Target IP/Hostname */ 
    $port = 80,                /* Target TCP port */ 
    $uri = '/',                /* Target URI */ 
    $getdata = array(),        /* HTTP GET Data ie. array('var1' => 'val1', 'var2' => 'val2') */ 
    $postdata = array(),       /* HTTP POST Data ie. array('var1' => 'val1', 'var2' => 'val2') */ 
    $cookie = array(),         /* HTTP Cookie Data ie. array('var1' => 'val1', 'var2' => 'val2') */ 
    $custom_headers = array(), /* Custom HTTP headers ie. array('Referer: http://localhost/ */ 
    $timeout = 10,           /* Socket timeout in seconds */ 
    $req_hdr = false,          /* Include HTTP request headers */ 
    $res_hdr = false           /* Include HTTP response headers */ 
    ) 
{ 
    $ret = ''; 
    $verb = strtoupper($verb); 
    $cookie_str = ''; 
    $getdata_str = count($getdata) ? '?' : ''; 
    $postdata_str = ''; 

    foreach ($getdata as $k => $v) 
                $getdata_str .= urlencode($k) .'='. urlencode($v) . '&'; 

    foreach ($postdata as $k => $v) 
        $postdata_str .= urlencode($k) .'='. urlencode($v) .'&'; 

    foreach ($cookie as $k => $v) 
        $cookie_str .= urlencode($k) .'='. urlencode($v) .'; '; 

    $crlf = "\r\n"; 
    $req = $verb .' '. $uri . $getdata_str .' HTTP/1.1' . $crlf; 
    $req .= 'Host: '. $ip . $crlf; 
    $req .= 'User-Agent: Mozilla/5.0 Firefox/3.6.12' . $crlf; 
    $req .= 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' . $crlf; 
    $req .= 'Accept-Language: en-us,en;q=0.5' . $crlf; 
    $req .= 'Accept-Encoding: deflate' . $crlf; 
    $req .= 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7' . $crlf; 
    
    foreach ($custom_headers as $k => $v) 
        $req .= $k .': '. $v . $crlf; 
        
    if (!empty($cookie_str)) 
        $req .= 'Cookie: '. substr($cookie_str, 0, -2) . $crlf; 
        
    if ($verb == 'POST' && !empty($postdata_str)) 
    { 
        $postdata_str = substr($postdata_str, 0, -1); 
        $req .= 'Content-Type: application/x-www-form-urlencoded' . $crlf; 
        $req .= 'Content-Length: '. strlen($postdata_str) . $crlf . $crlf; 
        $req .= $postdata_str; 
    } 
    else $req .= $crlf; 
    
    if ($req_hdr) 
        $ret .= $req; 
    
    if (($fp = @fsockopen($ip, $port, $errno, $errstr)) == false) 
        return "Error $errno: $errstr\n"; 
    
    stream_set_timeout($fp, 0, $timeout * 1000000); 
    
    fputs($fp, $req); 
    while ($line = fgets($fp)) $ret .= $line; 
    fclose($fp); 
    
    if (!$res_hdr) 
        $ret = substr($ret, strpos($ret, "\r\n\r\n") + 4); 
    
    return $ret; 
}

function readfile_chunked($filename,$retbytes=true) { 
   $chunksize = 10*(1024*1024); // how many bytes per chunk 
   $buffer = ''; 
   $cnt =0; 
   // $handle = fopen($filename, 'rb'); 
   $handle = fopen($filename, 'rb'); 
   if ($handle === false) { 
       return false; 
   } 
   while (!feof($handle)) { 
       $buffer = fread($handle, $chunksize); 
       echo $buffer; 
       ob_flush(); 
       flush(); 
       if ($retbytes) { 
           $cnt += strlen($buffer); 
       } 
   } 
       $status = fclose($handle); 
   if ($retbytes && $status) { 
       return $cnt; // return num. bytes delivered like readfile() does. 
   } 
   return $status; 

}


function DownloadFile($file) { // $file = include path
    if(file_exists($file)) {
        set_time_limit(0);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile_chunked($file);
        exit;
    }
}




function DownloadFilePart($file, $part) { // $file = include path
 function readfile_chunked_specific($filename,$part,$retbytes=true) { 
   $chunksize = 10*(1024*1024); // how many bytes per chunk
   $offset = $chunksize * ($part - 1);
   $buffer = ''; 
   $cnt =0; 
   $partsread = 0;
   // $handle = fopen($filename, 'rb'); 
   $handle = fopen($filename, 'rb'); 
   if ($handle === false) { 
       return false; 
   } 
   fseek($handle, $offset, SEEK_SET);
   if (!feof($handle)) { 
       $buffer = fread($handle, $chunksize); 
       header('Content-Description: File Transfer');
       header('Content-Type: application/octet-stream');
       header('Content-Disposition: attachment; filename='.basename($filename));
       header('Content-Transfer-Encoding: binary');
       header('Expires: 0');
       header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
       header('Pragma: public');
       header('Content-Length: ' . strlen($buffer));
       ob_clean();
       flush();
       echo $buffer;
       ob_flush(); 
       flush();
       if ($retbytes) { 
           $cnt += strlen($buffer); 
       } 
   } 
       $status = fclose($handle); 
   if ($retbytes && $status) { 
       return $cnt; // return num. bytes delivered like readfile() does. 
   } 
   return $status; 

 }
    if(file_exists($file)) {
        set_time_limit(0);
        readfile_chunked_specific($file, $part);
        exit;
    }
}




if(isset($_GET['list'])){
 function sizeFilter( $bytes )
 {
  $label = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );
  for( $i = 0; $bytes >= 1024 && $i < ( count( $label ) -1 ); $bytes /= 1024, $i++ );
  return( round( $bytes, 2 ) . "" . $label[$i] );
 }

 $dir    = './data';
 $files = array_diff(scandir($dir), array('..', '.'));;
 $list = "<html>\n<body>\n<table cellpadding=\"5\">\n";
 $list .= '<tr><th valign="top"><img src="/icons/blank.gif" alt="[ICO]"></th><th><a href="">Name</a></th><th><a href="">Last modified</a></th><th><a href="">Size</a></th><th><a href="">Description</a></th></tr>';
 $list .= '<tr><th colspan="5"><hr></th></tr>';

 foreach ($files as $k => $v){
  $name = $v;
  if (strlen($name) > 30)
   $name = substr($name, 0, 27) . '...';
  $path = realpath('./data/' . $v);
  $datemodified = date('Y\-m\-d G:i', filemtime($path));
  $size = sizeFilter(filesize($path));
  $type = '  ';
  if(filetype($path) == 'dir') $type = 'DIR';
  $list .= '<tr><td valign="top"><img src="" alt="[' . $type . ']"></td>' . '<td><a href="' . 'compute.php?getfile=' . $path . '">' . $name .'</a></td>' . '<td align="right">' . $datemodified . '</td>' . '<td align="right">' . $size . '</td><td>&nbsp;</td></tr>' . "\n";
 }
 $list .= '<tr><th colspan="5"><hr></th></tr>';
 $list .= "</table>\n</body>\n</html>";
 echo $list;
}

/*
// Example 1
$pizza  = "piece1 piece2 piece3 piece4 piece5 piece6";
$pieces = explode(" ", $pizza);
echo $pieces[0]; // piece1
echo $pieces[1]; // piece2
//If limit is set and positive, the returned array will contain a maximum of limit elements with the last element containing the rest of string. 
//array explode ( string $delimiter , string $string [, int $limit ] )
*/


//$currentdir = getcwd();

/*
// parent sript, called by user request from browser

// create socket for calling child script
$socketToChild = fsockopen("localhost", 80);

// HTTP-packet building; header first
$msgToChild = "POST /sript.php?&param=value&<more params> HTTP/1.0\n";
$msgToChild .= "Host: localhost\n";
$postData = "Any data for child as POST-query";
$msgToChild .= "Content-Length: ".strlen($postData)."\n\n";

// header done, glue with data
$msgToChild .= $postData;

// send packet no oneself www-server - new process will be created to handle our query
fwrite($socketToChild, $msgToChild);

// wait and read answer from child
$data = fread($socketToChild, $dataSize);

// close connection to child
fclose($socketToChild);
...

Now the child script:

// parse HTTP-query somewhere and somehow before this point

// "disable partial output" or 
// "enable buffering" to give out all at once later
ob_start();

// "say hello" to client (parent script in this case) disconnection
// before child ends - we need not care about it
ignore_user_abort(1);

// we will work forever
set_time_limit(0);

// we need to say something to parent to stop its waiting
// it could be something useful like client ID or just "OK"
...
echo $reply;

// push buffer to parent
ob_flush();

// parent gets our answer and disconnects
// but we can work "in background" :)
...
*/

//smallest recommended key is 1024 anything below that is just not secure

function renewpkey()
{
 //session_start();
 ini_set('zlib.output_compression', 0); 
 ob_end_clean();
 header("Connection: close");
 ignore_user_abort(true);
 ob_start();
 echo('Generating key');
 $size = ob_get_length();
 header("Content-Length: $size");
 ob_end_flush(); // Strange behaviour, will not work
 flush();            // Unless both are called !
 
 //chdir($currentdir);
 //ini_set('max_execution_time', 60);
 set_time_limit(60);
 set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
 require_once("Crypt/RSA.php");
 $rsa = new Crypt_RSA();
 extract($rsa->createKey(4096));
 $file = fopen("./rsa.pem","w");
 fwrite($file,$privatekey);
 fclose($file);
 set_time_limit(30);
}

function renewpkeywait()
{
 //chdir($currentdir);
 //ini_set('max_execution_time', 60);
 set_time_limit(60);
 set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
 require_once("Crypt/RSA.php");
 $rsa = new Crypt_RSA();
 extract($rsa->createKey(4096));
 $file = fopen("./rsa.pem","w");
 fwrite($file,$privatekey);
 fclose($file);
 set_time_limit(30);
}

function setrenewtime()
{
 $file = fopen("./renewtime","w");
 $renewtime = time() + 120;
 fwrite($file,$renewtime);
 fclose($file);
}

if(isset($_GET['renewKeyCheck'])){
 if(file_exists("./renewtime"))
 {
  if(file_get_contents("./renewtime") <= time() + 20)
  {
   setrenewtime();
   renewpkey();
  }
  elseif(file_get_contents("./renewtime") > time() + 3600)
  {
   setrenewtime();
   renewpkey();
  } 
  else
  {
   exit();
  }
 }
 else
 {
  setrenewtime();
  renewpkeywait();
 }
}


if(file_exists("./renewtime")&&!isset($_GET['renewKeyCheck']))
{
 if(file_get_contents("./renewtime") <= time())
 {
  setrenewtime();
  session_start();
  ini_set('zlib.output_compression', 0); 
  //ini_set('implicit_flush', 1);
  /*
  ignore_user_abort(true);
  $response = "Generating"; 
  header("Connection: close");
  header("Content-Length: " . mb_strlen($response));
  echo $response;
  flush();
  */
  ob_end_clean();
  header("Connection: close");
  ignore_user_abort(true);
  ob_start();
  echo('Generating key');
  $size = ob_get_length();
  header("Content-Length: $size");
  ob_end_flush(); // Strange behaviour, will not work
  flush();            // Unless both are called !
  /*
  register_shutdown_function('renewpkey', $currentdir);
  exit();
  */
  //http_request('POST', 'localhost', 80, '/test/compute.php', array(), array('GenNewKeyRequest' => 'true'));
  renewpkey();
 }
 elseif(file_get_contents("./renewtime") > time() + 3600)
 {
  setrenewtime();
  renewpkey();
 } 
}
else
{
 setrenewtime();
 renewpkeywait();
}


/*
// traverse all results
foreach ($xpath->query('//row[@name="title"]') as $rowNode) {
    echo $rowNode->nodeValue; // will be 'this item'
}
*/

/*
$doc = new DOMDocument('1.0', 'UTF-8');
// we want a nice output
$doc->formatOutput = true;

$root = $doc->createElement('book');
$root = $doc->appendChild($root);

$title = $doc->createElement('title');
$title = $root->appendChild($title);

$text = $doc->createTextNode('This is the title');
$text = $title->appendChild($text);

$doc->save('./users/blah.xml');
*/

function setloginSessionKey($username, $key, $exprtime){
 $dom = new DOMDocument;
 $dom->load('./users/' . $username. '.xml');
 $dom->preserveWhiteSpace = false;
 $dom->formatOutput = true;
 $xpath = new DOMXPath($dom);
 $node = $xpath->query('/user/' . $username . '/loginSessionKey')->item(0);
 $node->nodeValue = $key;
 $node = $xpath->query('/user/' . $username . '/loginSessionKeyExpireDate')->item(0);
 $node->nodeValue = $exprtime;
 $dom->save('./users/' . $username. '.xml');
}

function checkUserLoginSessionKey($username, $key){
 if(file_exists('./users/' . $username. '.xml')){
  $returnvalue = "";
  $dom = new DOMDocument;
  $dom->load('./users/' . $username. '.xml');
  $dom->preserveWhiteSpace = false;
  $dom->formatOutput = true;
  $xpath = new DOMXPath($dom);
  $node = $xpath->query('/user/' . $username . '/loginSessionKey')->item(0);
  $savedKey = $node->nodeValue;
  if($key == $savedKey){
   $node = $xpath->query('/user/' . $username . '/authenticationlevel')->item(0);
   $returnvalue = $node->nodeValue;
  }else{
   $returnvalue = 'false';
  }
  $node = $xpath->query('/user/' . $username . '/loginSessionKeyExpireDate')->item(0);
  $loginSessionKeyExpireDate = $node->nodeValue;
  if($loginSessionKeyExpireDate == 'false' || $loginSessionKeyExpireDate <= time()){
   $node->nodeValue = 'false';
   echo ' loginSessionKey has expired ';
   $returnvalue = 'false';
   $dom->save('./users/' . $username. '.xml');
  }
  return $returnvalue;
 }else{return 'false';}
}

function userlogout($username){
 $dom = new DOMDocument;
 $dom->load('./users/' . $username. '.xml');
 $dom->preserveWhiteSpace = false;
 $dom->formatOutput = true;
 $xpath = new DOMXPath($dom);
 $node = $xpath->query('/user/' . $username . '/loginSessionKey')->item(0);
 $node->nodeValue = 'false';
 $node = $xpath->query('/user/' . $username . '/loginSessionKeyExpireDate')->item(0);
 $node->nodeValue = 'false';
 $dom->save('./users/' . $username. '.xml');
}

function decrypt($strBase64CipherText)
{
    set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
    require_once("Crypt/RSA.php");
    //CRYPT_RSA_MODE_INTERNAL is slow
    //CRYPT_RSA_MODE_OPENSSL is fast, but requires openssl to be installed, configured and accessible.
    if(!defined("CRYPT_RSA_MODE")){
     define("CRYPT_RSA_MODE", CRYPT_RSA_MODE_INTERNAL);
    }

    $rsa=new Crypt_RSA();


    $strPrivateKey=file_get_contents("./rsa.pem");
    //This private key is for example purposes
    //DO NOT REUSE
    //$strPrivateKey="-----BEGIN RSA PRIVATE KEY-----
    //MIICXQIBAAKBgQDVd/gb2ORdLI7nTRHJR8C5EHs4RkRBcQuQdHkZ6eq0xnV2f0hk
    //WC8h0mYH/bmelb5ribwulMwzFkuktXoufqzoft6Q6jLQRnkNJGRP6yA4bXqXfKYj
    //1yeMusIPyIb3CTJT/gfZ40oli6szwu4DoFs66IZpJLv4qxU9hqu6NtJ+8QIDAQAB
    //AoGADbnXFENP+8W/spO8Dws0EzJCGg46mVKhgbpbhxUJaHJSXzoz92/MKAqVUPI5
    //mz7ZraR/mycqMia+2mpo3tB6YaKiOpjf9J6j+VGGO5sfRY/5VNGVEQ+JLiV0pUmM
    //doq8n2ZhKdSd5hZ4ulb4MFygzV4bmH29aIMvogMqx2Gkp3kCQQDx0UvBoNByr5hO
    //Rl0WmDiDMdWa9IkKD+EkUItR1XjpsfEQcwXet/3QlAqYf+FE/LBcnA79NdBGxoyJ
    //XS+O/p4rAkEA4f0JMSnIgjl7Tm3TpNmbHb7tsAHggWIrPstCuHCbNclmROfMvcDE
    //r560i1rbOtuvq5F/3BQs+QOnOIz1jLslUwJAbyEGNZfX87yqu94uTYHrBq/SQIH8
    //sHkXuH6jaBo4lP1HkY2qtu3LYR2HuQmb1v5hdk3pvYgLjVsVntMKVibBPQJBAKd2
    //Dj20LLTzS4BOuirKZbuhJBjtCyRVTp51mLd8Gke9Ol+NNZbXJejNvhQV+6ad7ItC
    //gnDfMoRERMIPElZ6x6kCQQCP45DVojZduLRuhJtzBkQXJ4pCsGC8mrHXF3M+hJV+
    //+LAYJbXrQa4mre59wR0skgb6CwGg1siMrDzJgu3lmBB0
    //-----END RSA PRIVATE KEY-----";

    //$strPrivateKey=preg_replace("/[ \t]/", "", $strPrivateKey);//this won't be necessary when loading from PEM


    $rsa->loadKey($strPrivateKey);

    $binaryCiphertext=base64_decode($strBase64CipherText);

    $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
    $strBase64DecryptedData=$rsa->decrypt($binaryCiphertext);

    return base64_decode($strBase64DecryptedData);
}


function encrypt($normaltext, $key)
{
    set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
    require_once("Crypt/RSA.php");
    if(!defined("CRYPT_RSA_MODE")){
     define("CRYPT_RSA_MODE", CRYPT_RSA_MODE_INTERNAL);
    }

    $rsa=new Crypt_RSA();


    $strPublicKey=$key;


    $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);

    $rsa->loadKey($strPublicKey);

    $encryptedData=$rsa->encrypt(base64_encode($normaltext));

    return base64_encode($encryptedData);
}

function createnewfile($filename, $user){
 $file = fopen('./data/' . $user . '/' . $filename,"w");
 fwrite($file,'');
 fclose($file);
}

if(isset($_GET['pageRequest']))
{
 if($_GET['pageRequest'] == 'loginpage')
 {
  echo '<!DOCTYPE html>

<style>
body {
  padding-top: 40px;
  padding-bottom: 40px;
  background-color: #eee !important;
}

.form-signin {
  max-width: 330px;
  padding: 15px;
  margin: 0 auto;
}
.form-signin .form-signin-heading,
.form-signin .checkbox {
  margin-bottom: 10px;
}
.form-signin .checkbox {
  font-weight: normal;
}
.form-signin .form-control {
  position: relative;
  height: auto;
  -webkit-box-sizing: border-box;
     -moz-box-sizing: border-box;
          box-sizing: border-box;
  padding: 10px;
  font-size: 16px;
}
.form-signin .form-control:focus {
  z-index: 2;
}
.form-signin input[type="email"] {
  margin-bottom: -1px;
  border-bottom-right-radius: 0;
  border-bottom-left-radius: 0;
}
.form-signin input[type="password"] {
  margin-bottom: 10px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}
</style>

<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="description" content="">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="author" content="">

<title>Login page</title>

<link href="bootstrap.css" rel="stylesheet">
</head>

<body>
<div class="container">
<div id="floatthing">

<div class="form1233">

<form class="form-signin" action="" id="registerform" method="POST" name="reginput">
<h2 class="form-signin-heading">Register:</h2>
<input class="form-control" type="text" name="usernamereg" placeholder="Username" id="usernamereg" maxlength="214" required/>
<input class="form-control" type="password" name="passreg" placeholder="Password" id="passreg" maxlength="214" required/>
<button class="btn-block btn btn-lg btn-primary" id="regbutton" name="regbuttonname" type="button">Register</button>
<div id="registerresponse"></div>
</form>

</div>

<div class="form1234">
<form class="form-signin" action="" id="loginform" method="POST" name="logininput">
<h2 class="form-signin-heading">Login:</h2>
<input class="form-control" type="text" name="usernamelogin" placeholder="Username" id="usernamelogin" maxlength="214" required/>
<input class="form-control" type="password" name="passlogin" placeholder="Password" id="passlogin" maxlength="214" required/>
<button class="btn-block btn btn-lg btn-primary" id="loginbutton" name="loginbuttonname" type="button">Login</button>
<div id="loginresponse"></div>
</form>

</div>
</div>
</div>

</body>
</html>';
 }
 if($_GET['pageRequest'] == 'Proccesingpage')
 {
  echo '<!DOCTYPE html>
<html>
<body>

<div style="color:#1e90ff">
  <h4>Procesing...</h4>
</div>

</body>
</html>';
 }
}
 

if(isset($_GET['AuthenticatedPageRequest'])){
 $AuthenticationLevel = checkUserLoginSessionKey($_GET['username'], decrypt($_GET['loginSessionKey']));
 if($AuthenticationLevel != 'false'){
  if($AuthenticationLevel == 0){
   if($_GET['AuthenticatedPageRequest'] == 'logedinpage'){
    echo '<!DOCTYPE html>
<html>
<body>

<button id="logout" type="button">LogOut</button>
<div style="color:#0000FF">
  <p>Hi</p><br>
  <p>You dont have any Permissions...</p>
</div>

</body>
</html>';
   } 
  }
  if($AuthenticationLevel == 1){
   if($_GET['AuthenticatedPageRequest'] == 'logedinpage'){
    echo '<!DOCTYPE html>

<style>
body {
  background-color: #eee !important;
  padding-top: 70px;
  padding-bottom: 30px;
}

.container {
 width: auto !important;
}

.form-control {
 float: left;
 width: 298px !important;
 pading-bottom: 5px !important;
}

.upload-form {
 padding-bottom: 5px !important;
}

.btn-stuffff {
 position: relative;
 left: 5px;
}

.floattorightmenu {
 position: relative;
 left: 20px !important;
}

.logoutb {
 float: right;
 width: 80px;
 height: 80px;
}

.logoutbutton {
    /* For Opera and <= IE9, we need to add unselectable="on" attribute onto each element */
    /* Check this site for more details: http://help.dottoro.com/lhwdpnva.php */
    -moz-user-select: none; /* These user-select properties are inheritable, used to prevent text selection */
    -webkit-user-select: none;
    -ms-user-select: none; /* From IE10 only */
    user-select: none; /* Not valid CSS yet, as of July 2012 */

    -webkit-user-drag: none; /* Prevents dragging of images/divs etc */
    user-drag: none;
    cursor: pointer;
}
</style>

<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="description" content="">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="author" content="">

<title>Loged in page</title>
<link href="bootstrap.css" rel="stylesheet">
</head>
<body>

    <!-- Fixed navbar -->
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Bootstrap theme</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="#">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="#">Action</a></li>
                <li><a href="#">Another action</a></li>
                <li><a href="#">Something else here</a></li>
                <li class="divider"></li>
                <li class="dropdown-header">Nav header</li>
                <li><a href="#">Separated link</a></li>
                <li><a href="#">One more separated link</a></li>
              </ul>
            </li>
          </ul>
          <div class="navbar-header pull-right">
           <p class="navbar-text">
            <a id="logout" class="navbar-link logoutbutton">Logout</a>
           </p>  
          </div>
        </div><!--/.nav-collapse -->
      </div>
    </div>

<div style="color:#0000FF">
  <p>Hi</p>
</div>

<br>
<form action="" id="createnewfileform">
<p>Create new file:<p>
<input class="form-control" type="text" id="filenametocreate" maxlength="214"/>
<button class="btn btn-primary btn-stuffff" id="createfile" type="button">Create file</button>
<div id="createfileresponse"></div>
</form>

<br>
<form action="" id="deletefileform">
<p>Delete file:<p>
<input class="form-control" type="text" id="filenametodelete" maxlength="214"/>
<button class="btn btn-primary btn-stuffff" id="deletefile" type="button">Delete file</button>
<div id="deletefileresponse"></div>
</form>

<br>
<form action="" id="uploadfileform">
<p>Upload file:<p>
<input class="upload-form" type="file" name="upload_file" id="upload_file" multiple />
<button class="btn btn-primary" id="uploadfilebutton" type="button">Upload file</button>
<div id=\'progress\'></div>
<div id=\'b_transfered\'></div>
<div id=\'speed\'></div>
<div id=\'remaining\'></div>
</form>

<div id="filelist"></div>

<script>
  AuthRequest(localStorage.getItem(\'username\'), localStorage.getItem(\'loginSessionKey\'), "&Request=ListUserFiles", function(response){document.getElementById("filelist").innerHTML = response});
  var filecheckinterval = setInterval(function(){AuthRequest(localStorage.getItem(\'username\'), localStorage.getItem(\'loginSessionKey\'), "&Request=ListUserFiles", function(response){document.getElementById("filelist").innerHTML = response});},3000);
  document.getElementById("createfile").onclick = requestFileCreation;
  document.getElementById("deletefile").onclick = requestFileDeletion;
  function requestFileCreation(){
   function setfilecretionresponse(response){
    $("#createfileresponse").html(response);
   }
   var requestdata = \'&Request=createfile\' + \'&filename=\' + encodeURIComponent(document.getElementById("filenametocreate").value);
   AuthRequest(localStorage.getItem(\'username\'), localStorage.getItem(\'loginSessionKey\'), requestdata, setfilecretionresponse);
  }
  function requestFileDeletion(){
   function setfiledeletionresponse(response){
    $("#deletefileresponse").html(response);
   }
   var requestdata = \'&Request=deletefile\' + \'&filename=\' + encodeURIComponent(document.getElementById("filenametodelete").value);
   AuthRequest(localStorage.getItem(\'username\'), localStorage.getItem(\'loginSessionKey\'), requestdata, setfiledeletionresponse);
  }
</script>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="bootstrap.js"></script>
    <script src="docs.min.js"></script>

<script src="upload.js"></script>
<script src="other.js"></script>

</body>
</html>';
   } 
  }
 }else{echo 'Wrong key';}
}

if(isset($_POST['AuthenticatedRequest'])){
 $AuthenticationLevel = checkUserLoginSessionKey($_POST['username'], decrypt($_POST['loginSessionKey']));
 if($AuthenticationLevel != 'false'){
  if($_POST['Request'] == 'CheckKey'){
   echo 'true';
  }
  if($AuthenticationLevel >= 1){
   if($_POST['Request'] == 'createfile'){
    $file = str_replace('/', '', $_POST['filename']);
    if(!file_exists('./data/' . $_POST['username'] . '/' . $file)){
     echo('<b>File created</b>');
     createnewfile($file, $_POST['username']);
    }else{
     echo('<b>File already exists</b>');
    }
   }
   if($_POST['Request'] == 'deletefile'){
    if(file_exists('./data/' . $_POST['username'] . '/' . $_POST['filename']) && $_POST['filename'] != ''){
     echo('<b>File Deleted</b>');
     unlink('./data/' . $_POST['username'] . '/' . $_POST['filename']);
    }else{
     echo('<b>File Dosent exist</b>');
    }
   }
   if($_POST['Request'] == 'uploadfile'){
    $file_name = $_FILES['file']['name'];
    $file_tmp_name =$_FILES['file']['tmp_name'];
    move_uploaded_file($file_tmp_name,"./data/".$_POST['username'].'/'.$file_name);
   }
   if($_POST['Request'] == 'ListUserFiles'){
    function sizeFilter( $bytes, $decimals = 2 )
    {
     $label = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );
     $factor = floor((strlen($bytes) - 1) / 3);
     return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $label[$factor];
    }

    $dir = './data/'.$_POST['username'].'/';
    $files = array_diff(scandir($dir), array('..', '.'));;
    $list = "<html>\n<body>\n<table class='table1'>\n";
    $list .= '<tr><th valign="top"><img src="" alt="[ICO]"></th><th><a href="">Name</a></th><th><a href="">Last modified</a></th><th><a href="">Size</a></th><th><a href="">Actions</a></th><th><a href="">Status</a></th></tr>'."\n";
    $list .= '<tr><th colspan="6"><hr class="hr123"></th></tr>'."\n";

    foreach ($files as $k => $v){
     $name = $v;
     if (strlen($name) > 30)
      $name = substr($name, 0, 27) . '...';
     $path = realpath('./data/'.$_POST['username'].'/'.$v);
     $datemodified = date('Y\-m\-d G:i', filemtime($path));
     $size = sizeFilter(filesize($path));
     $type = '  ';
     if(filetype($path) == 'dir') $type = 'DIR';
     $list .= '<tr><td valign="top"><img src="" alt="[' . $type . ']"></td>' . '<td><a class="unselectable" onclick="AuthGetFile('."'".str_replace("'", "!ApostropheChar!", $path)."'".')">' . $name .'</a></td>' . '<td align="right">' . $datemodified . '</td>' . '<td align="right">' . $size . '</td><td><a class="unselectable" onclick="deleteuserfile('."'".$v."'".')">DEL</a>&nbsp;</td><td><div id="userfilesstatusdisplay:'.$v.'"></div></td></tr>' . "\n";
    }
    $list .= '<tr><th colspan="6"><hr class="hr123"></th></tr>'."\n";
    $list .= "</table>\n</body>\n</html>";
    $list .= "<style>table.table1{border-spacing: 10px 0px;border-collapse:separate !important;position:relative;top:30px;}</style>";
    $list .= "<style>.hr123{border-top: 1px solid #000000 !important;}</style>";
    $list .= "<style>.unselectable {-moz-user-select: none;-webkit-user-select: none;-ms-user-select: none;user-select: none;-webkit-user-drag: none;user-drag: none;cursor: pointer;}</style>";
    echo $list;
   }
   if($_POST['Request'] == 'RequestDownloadFile'){
    if(isset($_POST['file'])){
     $file = $_POST['file'];
     $userRealDataPath = realpath('./data/'.$_POST['username'].'/');
     if($userRealDataPath == "" || $userRealDataPath == null) exit();
     $RealFilePath = realpath($file);
     $lengthOfUserRealDataPath = strlen($userRealDataPath);
     if(substr($userRealDataPath, 0, $lengthOfUserRealDataPath) == substr($RealFilePath, 0, $lengthOfUserRealDataPath)){
      DownloadFile($file);
     }
    }
   }
   if($_POST['Request'] == 'RequestDownloadFileSize'){
     $file=$_POST['file'];
     $userRealDataPath = realpath('./data/'.$_POST['username'].'/');
     if($userRealDataPath == "" || $userRealDataPath == null) exit();
     $RealFilePath = realpath($file);
     $lengthOfUserRealDataPath = strlen($userRealDataPath);
     if(substr($userRealDataPath, 0, $lengthOfUserRealDataPath) == substr($RealFilePath, 0, $lengthOfUserRealDataPath)){
      $filesize=filesize ($file);
      echo $filesize;
     }
   }
   if($_POST['Request'] == 'RequestDownloadFilePart'){
     $file=$_POST['file'];
     $userRealDataPath = realpath('./data/'.$_POST['username'].'/');
     if($userRealDataPath == "" || $userRealDataPath == null) exit();
     $RealFilePath = realpath($file);
     $lengthOfUserRealDataPath = strlen($userRealDataPath);
     if(substr($userRealDataPath, 0, $lengthOfUserRealDataPath) == substr($RealFilePath, 0, $lengthOfUserRealDataPath)){
       DownloadFilePart($file, $_POST['part']);
     }
   }
   if($_POST['Request'] == 'RequestDownloadFileKey'){
    if(isset($_POST['file'])){
     $file = $_POST['file'];
     $userRealDataPath = realpath('./data/'.$_POST['username'].'/');
     if($userRealDataPath == "" || $userRealDataPath == null) exit();
     $RealFilePath = realpath($file);
     $lengthOfUserRealDataPath = strlen($userRealDataPath);
     if(substr($userRealDataPath, 0, $lengthOfUserRealDataPath) == substr($RealFilePath, 0, $lengthOfUserRealDataPath)){
      require_once("download.php");
      $download = new download();
      echo $download->GenGetKey($file);
      $download->CleanOldKeys();
     }
    }
   }
  }
 }else{echo 'Wrong key';}
}

if(isset($_POST['keyRequest'])){
  set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
  require_once("Crypt/RSA.php");

  $rsa = new Crypt_RSA();
  $strPrivateKey=file_get_contents("./rsa.pem");
 
  $rsa->loadKey($strPrivateKey);
  $rsa->setPublicKey();

  $publickey = $rsa->getPublicKey(); // could do CRYPT_RSA_PUBLIC_FORMAT_PKCS1 too
  echo $publickey;
}

//@ prevents output of errors

if(isset($_POST['registerRequest'])){

 function createuser($username, $pass)
 {
  @mkdir("./data/".$username.'/', 0700, true);
  @mkdir("./users/", 0700, true);
  $hashedpass = hash('sha512', $pass);
  
  $doc = new DOMDocument('1.0', 'UTF-8');
  // we want a nice output
  $doc->formatOutput = true;

  $user = $doc->createElement('user');
  $user = $doc->appendChild($user);

  $name = $doc->createElement($username);
  $name = $user->appendChild($name);
  
  $passnode = $doc->createElement('password');
  $passnode = $name->appendChild($passnode);
  
  $logintimesnode = $doc->createElement('logintimes');
  $logintimesnode = $name->appendChild($logintimesnode)->appendChild($doc->createTextNode('0'));
  
  $name->appendChild($doc->createElement('authenticationlevel'))->appendChild($doc->createTextNode('0'));
  $name->appendChild($doc->createElement('loginSessionKey'))->appendChild($doc->createTextNode('false'));
  $name->appendChild($doc->createElement('loginSessionKeyExpireDate'))->appendChild($doc->createTextNode('false'));

  $passtext = $doc->createTextNode($hashedpass);
  $passtext = $passnode->appendChild($passtext);

  $doc->save('./users/' . $username . '.xml');
  echo('<h4>Registered</h4>');
 }

 //The pidCrypt example implementation will output a base64 string of an encrypted base64 string which contains the original data, like this one:
 $strBase64CipherText=$_POST['passreg'];

 $binaryDecrypted=decrypt($strBase64CipherText);
 
 if($_POST['usernamereg'] == '' || $binaryDecrypted == ''){echo '<h4>Error: Field(s) empty</h4>';exit();}

 if(!file_exists("./users/" . $_POST['usernamereg'] . '.xml'))
 {
  createuser($_POST['usernamereg'], $binaryDecrypted);
 }
 else
 {
  echo('<h4>User already exists</h4>');
 }
}


if(isset($_POST['loginRequest'])){

 function checkpass($username, $pass)
 {
  $hashedpass = hash('sha512', $pass); 
  $dom = new DOMDocument;
  $dom->load('./users/' . $username. '.xml');
  $dom->preserveWhiteSpace = false;
  $dom->formatOutput = true;
  $usernode = $dom->getElementsByTagName('user')->item(0);
  $namenode = $usernode->getElementsByTagName($username)->item(0);
  $passnode = $namenode->getElementsByTagName('password')->item(0);
  $logintimesnode = $namenode->getElementsByTagName('logintimes')->item(0);
  $registeredpass = $passnode->nodeValue;
  
  /*
  $dom = new DOMDocument;
  $dom->load('./users/' . $username. '.xml');
  $xpath = new DOMXPath($dom);
  $node = $xpath->query('/user/' . $username . '/password')->item(0);
  $registeredpass = $node->nodeValue;
  */
  
  if($hashedpass == $registeredpass)
  {
   $logintimesnode->nodeValue = $logintimesnode->nodeValue + 1;
   $dom->save('./users/' . $username . '.xml');
   return true;
  }
  else
  {
   return false;
  }
 }

 //The pidCrypt example implementation will output a base64 string of an encrypted base64 string which contains the original data, like this one:
 $strBase64CipherText=$_POST['passlogin'];

 $binaryDecrypted=decrypt($strBase64CipherText);

 if(file_exists("./users/" . $_POST['usernamelogin'] . '.xml'))
 {
  if(checkpass($_POST['usernamelogin'], $binaryDecrypted))
  {
   $cstrong = false;
   $numoftries = 0;
   while ($cstrong == false){
    $randomebyte = openssl_random_pseudo_bytes(91, $cstrong);
    $numoftries = $numoftries + 1;
    if($numoftries == 50){break;};
   }
   $randomehex = bin2hex($randomebyte);
   setloginSessionKey($_POST['usernamelogin'], $randomehex, time()+86400);
   $encryptedrandomehex = encrypt($randomehex, $_POST['clientkey']);
   echo("<h4>Success: correct pass</h4>,");
   echo($encryptedrandomehex . ',');
   echo(checkUserLoginSessionKey($_POST['usernamelogin'], $randomehex));
  }
  else
  {
   echo("<h4>Failure: wrong pass</h4>");
  }
 }
 else
 {
  echo('<h4>User dosen\'t exist</h4>');
 }
}


//var_export($binaryDecrypted);

if(isset($_POST['logoutRequest'])){
 if(file_exists("./users/" . $_POST['username'] . '.xml')){
  $AuthenticationLevel = checkUserLoginSessionKey($_POST['username'], decrypt($_POST['loginSessionKey']));
  if($AuthenticationLevel != 'false'){
   userlogout($_POST['username']);
   echo 'LogOut success';
  }else{echo 'LogOut faliure';}
 }
}
?>
