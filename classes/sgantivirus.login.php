<?php
/**
 * Load $captcha_key_site and $captcha_key_secret
 * for wp-website-antivirus-protection
 * 
 * Ver.: 3.3
 * Date: 14 Apr 2020
 */

$session = session_start();

// --- LOAD KEYS ---
$key_file = dirname(dirname(dirname(dirname(__FILE__)))).'/siteguarding_logs/sgantivirus.login.keys.php';
if (file_exists($key_file)) include_once($key_file);
// --- LOAD KEYS ---

if (!isset($captcha_key_site) || $captcha_key_site == '' || !isset($captcha_key_secret) || $captcha_key_secret == '' ) return;

$flag_do_verification = true;
if (isset($license_code) && base64_decode($license_code) < date("Y-m-d")) $flag_do_verification = false;

REMOVE_old_codes();

if (isset($_REQUEST['captcha_task'])) $captcha_task = trim($_REQUEST['captcha_task']);
else $captcha_task = '';

$current_ip = $_SERVER["REMOTE_ADDR"];

if (isset($_SERVER["HTTP_X_REAL_IP"]) && filter_var($_SERVER["HTTP_X_REAL_IP"], FILTER_VALIDATE_IP)) $current_ip = $_SERVER["HTTP_X_REAL_IP"];
if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && filter_var($_SERVER["HTTP_X_FORWARDED_FOR"], FILTER_VALIDATE_IP)) $current_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
if (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) && filter_var($_SERVER["HTTP_CF_CONNECTING_IP"], FILTER_VALIDATE_IP)) $current_ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
if (isset($_SERVER["HTTP_X_SUCURI_CLIENTIP"]) && filter_var($_SERVER["HTTP_X_SUCURI_CLIENTIP"], FILTER_VALIDATE_IP)) $current_ip = $_SERVER["HTTP_X_SUCURI_CLIENTIP"];



if ($session && $captcha_task != 'check' && isset($_SESSION["siteguarding_verification_page"]))
{
    $time_stamp = intval($_SESSION["siteguarding_verification_page"]);
    if ( (time() - $time_stamp) < 3 * 60 ) return;
} else if ($captcha_task != 'check' && CHECK_session_code()){
	 return;
}



if ($captcha_task == 'check' && !$flag_do_verification) // Show login page 
{
    // create login session
    if ($session) {
		$_SESSION["siteguarding_verification_page"] = time();
	} else {
		CREATE_session_code();
	}
    
    return;
}


if ($captcha_task == '') 
{
?>
    <html>
      <head>
        <title>SiteGuarding.com verification page</title>
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto+Condensed">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes"/>
        <?php 
        if ($flag_do_verification) {
        ?>
        <script type="text/javascript">
          var onloadCallback = function() {
            grecaptcha.render('html_element', {
              'sitekey' : '<?php echo $captcha_key_site; ?>'
            });
          };
        </script>
        <?php 
        }
        ?>
      </head>
      <body>
      <style>
        #login {
            width: 310px;
            padding: 8% 0 0;
            margin: auto;
        }
body {
    font-family: 'Roboto Condensed', sans-serif;
}
.tbig{font-size:15px}
.tsmall{font-size:9px}
.btn {
	-moz-box-shadow: 0px 10px 14px -7px #3e7327;
	-webkit-box-shadow: 0px 10px 14px -7px #3e7327;
	box-shadow: 0px 10px 14px -7px #3e7327;
	background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #77b55a), color-stop(1, #72b352));
	background:-moz-linear-gradient(top, #77b55a 5%, #72b352 100%);
	background:-webkit-linear-gradient(top, #77b55a 5%, #72b352 100%);
	background:-o-linear-gradient(top, #77b55a 5%, #72b352 100%);
	background:-ms-linear-gradient(top, #77b55a 5%, #72b352 100%);
	background:linear-gradient(to bottom, #77b55a 5%, #72b352 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#77b55a', endColorstr='#72b352',GradientType=0);
	background-color:#77b55a;
	-moz-border-radius:4px;
	-webkit-border-radius:4px;
	border-radius:4px;
	border:1px solid #4b8f29;
	display:inline-block;
	cursor:pointer;
	color:#ffffff;
	font-family:Arial;
	font-size:16px;
	padding:7px 33px;
	text-decoration:none;
	text-shadow:0px 1px 0px #5b8a3c;
}
.btn:hover {
	background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #72b352), color-stop(1, #77b55a));
	background:-moz-linear-gradient(top, #72b352 5%, #77b55a 100%);
	background:-webkit-linear-gradient(top, #72b352 5%, #77b55a 100%);
	background:-o-linear-gradient(top, #72b352 5%, #77b55a 100%);
	background:-ms-linear-gradient(top, #72b352 5%, #77b55a 100%);
	background:linear-gradient(to bottom, #72b352 5%, #77b55a 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#72b352', endColorstr='#77b55a',GradientType=0);
	background-color:#72b352;
}
.btn:active {
	position:relative;
	top:1px;
}
.center{text-align: center;}
a {
    color: #4B9307;
    text-decoration: none;
}
a:hover {
    color: #1d591d;
    text-decoration: underline;
}
.color_red{color:#DB2828;}
      </style>

        <div id="login">
        <form action="?" method="POST">
          <p class="center">
            <img width="300" src="wp-content/plugins/wp-website-antivirus-protection/images/logo_siteguarding.svg" />
          </p>
          <p class="tbig center">
            Login page protected with <a href="https://www.siteguarding.com/en/" target="_blank">SiteGuarding.com</a> security extension
          </p>
        <?php 
        if ($flag_do_verification) {
        ?>
          <div id="html_element"></div>
        <?php 
        } else {
        ?>
            <p class="center color_red">
                Your current subscritpion is expired.<br><br>Security is disabled.<br><br>Please extend your security package. <a target="_blank" href="https://www.siteguarding.com/en/protect-your-website">Click here</a>
            </p>
        <?php 
        }
        ?>
          <br>
          <p class="center">
            <input type="submit" class="btn" value="Login page">
          </p>
          <p class="tsmall center">
            <a href="https://www.siteguarding.com/en/bruteforce-attack" target="_blank">How it works. Learn more</a>
          </p>
          <input type="hidden" name="captcha_task" value="check">
          <input type="hidden" name="captcha_session" value="<?php echo md5(time().mt_rand()); ?>">
        </form>
        </div>
        <?php 
        if ($flag_do_verification) {
        ?>
        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
            async defer>
        </script>
        <?php 
        }
        ?>
      </body>
    </html>


<?php
    
    exit;
}
else {
    
    if(isset($_POST['g-recaptcha-response'])) $captcha = $_POST['g-recaptcha-response'];
    
	$url = "https://www.google.com/recaptcha/api/siteverify?secret=".$captcha_key_secret."&response=".$captcha."&remoteip=".$current_ip;
    
    // Try file_get_contents
    $response_google = file_get_contents($url);
    if ($response_google !== false && $response_google != '') 
    {
        $result = CheckRecaptcha($captcha_key_session, $response_google, true);
    }
    
    
    // Try GetRemote_file_contents
    if ($result !== true)
    {
    	$response_google = GetRemote_file_contents($url);
        if ($response_google !== false && $response_google != '') 
        {
            $result = CheckRecaptcha($captcha_key_session, $response_google, true);
        }
    }
    
    // Try EasyRequest
    if ($result !== true)
    {
        if (!class_exists('EasyRequest'))
        {
            include_once(dirname(__FILE__).'/EasyRequest.min.php');
        }
        $client = EasyRequest::create($url);
        $client->send();
        $response_google = $client->getResponseBody();
        if ($response_google !== false && $response_google != '') 
        {
            $result = CheckRecaptcha($captcha_key_session, $response_google, true);
        }
    }
    
    // Try SiteGuariding server
    if ($result !== true)
    {
        $url = "https://www.siteguarding.com/google_proxy.php?secret=".$captcha_key_secret."&response=".$captcha."&remoteip=".$current_ip;
        $client = EasyRequest::create($url);
        $client->send();
        $response_google = $client->getResponseBody();
    }
    
    CheckRecaptcha($captcha_key_session, $response_google);
}


function CheckRecaptcha($captcha_key_session, $response_google, $try = false)
{
	$response=json_decode($response_google, true);
    
    //print_r($response);
    if($response['success'] != 1)
    {
        // Error
        if ($try === true) return false;
        ?>
        <html>
        <head>
        <META HTTP-EQUIV="Refresh" CONTENT="3;url=/wp-login.php">
        </head>
        <body>
        <p style="text-align: center;padding:30px 0">We can't verify your request. Please try again.</p>
        <p style="text-align: center;padding:30px 0">PHP functions file_get_contents and cURL are disabled or return wrong the answer from Google.<br>Outgoing connection can be blocked by hoster.</p>
        <p>&nbsp;</p>
        <p><b>Debug:</b></p>
        <p><?php echo 'Size: '.strlen($response_google)."<br>".$response_google; ?></p>
        </body>
        </html>
        <?php
        exit;
    }
    else {
        // create login session
        if ($session) {
			$_SESSION["siteguarding_verification_page"] = time();
		} else {
			CREATE_session_code();
		}
        return true;
    }
}


function GetRemote_file_contents($url, $parse = false)
{
    if (extension_loaded('curl')) 
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 3600000);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20); // 10 sec
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 20000); // 10 sec
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $output = trim(curl_exec($ch));
        curl_close($ch);
        
        if ($output === false)  return false;
        
        if ($parse === true) $output = (array)json_decode($output, true);
        
        return $output;
    }
    else return false;
}

function CHECK_session_code()
{
	global $current_ip;
	
    $path = dirname(dirname(__FILE__));
    $path = str_replace('//', '/', $path);
    $path = $path.DIRECTORY_SEPARATOR.'tmp';
    $filename = 'bf_session_'.md5($current_ip).'.login';

	if (!file_exists($path.DIRECTORY_SEPARATOR.$filename))
	{
		$filename = 'bf_session_'.md5('').'.login';
	}
    if (file_exists($path.DIRECTORY_SEPARATOR.$filename))
    {
        $ctime = filectime($path.DIRECTORY_SEPARATOR.$filename);
        if (date("Y-m-d") == date("Y-m-d", $ctime)) return true;
        else {
            unlink($path.DIRECTORY_SEPARATOR.$filename);
            return false;
        }
    }
}

function REMOVE_old_codes()
{
	global $current_ip;
	
    $path = dirname(dirname(__FILE__));

    $path = str_replace('//', '/', $path);
    $path = $path.DIRECTORY_SEPARATOR.'tmp';
    $filename = 'bf_session_'.md5($current_ip).'.login';

    foreach (glob($path.DIRECTORY_SEPARATOR."*.login") as $filename) 
    {
        $ctime = filectime($filename);
        if (date("Y-m-d") != date("Y-m-d", $ctime)) unlink($filename);
    }
}

function CREATE_session_code()
{
	global $current_ip;
	
    $path = dirname(dirname(__FILE__));
    $path = str_replace('//', '/', $path);
	
	$filename = 'bf_session_'.md5($current_ip).'.login';
	$session_file = $path.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$filename;
	if (file_exists($session_file)) 
	{
		$ctime = filectime($session_file);
		if (date("Y-m-d") == date("Y-m-d", $ctime)) return;
		
		unlink($session_file);
	}
	$fp = fopen($session_file, 'w');
	fwrite($fp, date("Y-m-d H:i:s"));
	fclose($fp);
}
    
/* Dont remove this code: SiteGuarding_Block_6C33B41CEC02 */
