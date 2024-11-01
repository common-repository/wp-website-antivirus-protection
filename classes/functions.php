<?php




function plgwpuan_NotifyAdmin($message, $is_advert = false, $data = array())
{
        $domain = get_site_url();
                
        $body_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>SiteGuarding - Professional Web Security Services!</title>
</head>
<body bgcolor="#ECECEC">
<table cellpadding="0" cellspacing="0" width="100%" align="center" border="0">
  <tr>
    <td width="100%" align="center" bgcolor="#ECECEC" style="padding: 5px 30px 20px 30px;">
      <table width="750" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#fff" style="background-color: #fff;">
        <tr>
          <td width="750" bgcolor="#fff"><table width="750" border="0" cellspacing="0" cellpadding="0" bgcolor="#fff" style="background-color: #fff;">
            <tr>
              <td width="267" height="60" bgcolor="#fff" style="padding: 5px; background-color: #fff;"><a href="http://www.siteguarding.com/" target="_blank"><img src="data:image/png;base64,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" alt="SiteGuarding - Protect your website from unathorized access, malware and other threat" height="60" border="0" style="display:block" /></a></td>
              <td width="400" height="60" align="right" bgcolor="#fff" style="background-color: #fff;">
              <table border="0" cellspacing="0" cellpadding="0" bgcolor="#fff" style="background-color: #fff;">
                <tr>
                  <td style="font-family:Arial, Helvetica, sans-serif; font-size:11px;"><a href="http://www.siteguarding.com/en/login" target="_blank" style="color:#656565; text-decoration: none;">Login</a></td>
                  <td width="15"></td>
                  <td width="1" bgcolor="#656565"></td>
                  <td width="15"></td>
                  <td style="font-family:Arial, Helvetica, sans-serif; font-size:11px;"><a href="http://www.siteguarding.com/en/prices" target="_blank" style="color:#656565; text-decoration: none;">Services</a></td>
                  <td width="15"></td>
                  <td width="1" bgcolor="#656565"></td>
                  <td width="15"></td>
                  <td style="font-family:Arial, Helvetica, sans-serif; font-size:11px;"><a href="http://www.siteguarding.com/en/what-to-do-if-your-website-has-been-hacked" target="_blank" style="color:#656565; text-decoration: none;">Security Tips</a></td>            
                  <td width="15"></td>
                  <td width="1" bgcolor="#656565"></td>
                  <td width="15"></td>
                  <td style="font-family:Arial, Helvetica, sans-serif;  font-size:11px;"><a href="http://www.siteguarding.com/en/contacts" target="_blank" style="color:#656565; text-decoration: none;">Contacts</a></td>
                  <td width="30"></td>
                </tr>
              </table>
              </td>
            </tr>
          </table></td>
        </tr>

        <tr>
          <td width="750" height="2" bgcolor="#D9D9D9"></td>
        </tr>
        <tr>
          <td width="750" bgcolor="#fff" ><table width="750" border="0" cellspacing="0" cellpadding="0" bgcolor="#fff" style="background-color:#fff;">
            <tr>
              <td width="750" height="30"></td>
            </tr>
            <tr>
              <td width="750">
                <table width="750" border="0" cellspacing="0" cellpadding="0" bgcolor="#fff" style="background-color:#fff;">
                <tr>
                  <td width="30"></td>
                  <td width="690" bgcolor="#fff" align="left" style="background-color:#fff; font-family:Arial, Helvetica, sans-serif; color:#000000; font-size:12px;">
                    <br />
                    {MESSAGE_CONTENT}
                  </td>
                  <td width="30"></td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td width="750" height="15"></td>
            </tr>
            <tr>
              <td width="750" height="15"></td>
            </tr>
            <tr>
              <td width="750"><table width="750" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="30"></td>
                  <td width="690" align="left" style="font-family:Arial, Helvetica, sans-serif; color:#000000; font-size:12px;"><strong>How can we help?</strong><br />
                    If you have any questions please dont hesitate to contact us. Our support team will be happy to answer your questions 24 hours a day, 7 days a week. You can contact us at <a href="mailto:support@siteguarding.com" style="color:#2C8D2C;"><strong>support@siteguarding.com</strong></a>.<br />
                    <br />
                    Thanks again for choosing SiteGuarding as your security partner!<br />
                    <br />
                    <span style="color:#2C8D2C;"><strong>SiteGuarding Team</strong></span><br />
                    <span style="font-family:Arial, Helvetica, sans-serif; color:#000; font-size:11px;"><strong>We will help you to protect your website from unauthorized access, malware and other threats.</strong></span></td>
                  <td width="30"></td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td width="750" height="30"></td>
            </tr>
          </table></td>
        </tr>
        <tr>
          <td width="750" height="2" bgcolor="#D9D9D9"></td>
        </tr>
      </table>
      <table width="750" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="750" height="10"></td>
        </tr>
        <tr>
          <td width="750" align="center"><table border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td style="font-family:Arial, Helvetica, sans-serif; color:#ffffff; font-size:10px;"><a href="http://www.siteguarding.com/en/website-daily-scanning-and-analysis" target="_blank" style="color:#656565; text-decoration: none;">Website Daily Scanning</a></td>
              <td width="15"></td>
              <td width="1" bgcolor="#656565"></td>
              <td width="15"></td>
              <td style="font-family:Arial, Helvetica, sans-serif; color:#ffffff; font-size:10px;"><a href="http://www.siteguarding.com/en/malware-backdoor-removal" target="_blank" style="color:#656565; text-decoration: none;">Malware & Backdoor Removal</a></td>
              <td width="15"></td>
              <td width="1" bgcolor="#656565"></td>
              <td width="15"></td>
              <td style="font-family:Arial, Helvetica, sans-serif; color:#ffffff; font-size:10px;"><a href="http://www.siteguarding.com/en/update-scripts-on-your-website" target="_blank" style="color:#656565; text-decoration: none;">Security Analyze & Update</a></td>
              <td width="15"></td>
              <td width="1" bgcolor="#656565"></td>
              <td width="15"></td>
              <td style="font-family:Arial, Helvetica, sans-serif; color:#ffffff; font-size:10px;"><a href="http://www.siteguarding.com/en/website-development-and-promotion" target="_blank" style="color:#656565; text-decoration: none;">Website Development</a></td>
            </tr>
          </table></td>
        </tr>

        <tr>
          <td width="750" height="10"></td>
        </tr>
        <tr>
          <td width="750" align="center" style="font-family: Arial,Helvetica,sans-serif; font-size: 10px; color: #656565;">Add <a href="mailto:support@siteguarding.com" style="color:#656565">support@siteguarding.com</a> to the trusted senders list.</td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>';
        
        
        $message .= "<br><br><b>User Information</b></br>";
		$message .= 'Date: <span style="color:#D54E21">'.$data['datetime'].'</span>'."<br>";
		$message .= "Username: ".$data['username']."<br>";
		$message .= "Browser: ".$data['browser']."<br>";
		$message .= "IP Address: ".$data['ip_address']."<br>";
		$message .= 'Location: <span style="color:#D54E21">'.$data['geolocation']['cityName'].", ".$data['geolocation']['countryName'].'</span>'."<br>";
		

    	$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		$admin_email = $data['admin_email'] ? $data['admin_email'] : get_option( 'admin_email' );

        
            
                                                            
            $body_message = str_replace("{MESSAGE_CONTENT}", $message, $body_message);

        $subject = sprintf( __( '['.$data['login_status'].'] Access Notification to (%s)' ), $blogname );
        $headers = 'content-type: text/html';  

        
    	wp_mail( $admin_email, $subject, $body_message, $headers );
}	



function plgwpuan_Notify_Telegram($telegram_bot_api_token, $chat_id, $data, $message)
{
	$str = 'Date: ' . $data['datetime']. "%0A%0A" . 
	$message."%0A%0A" . 
	'IP: ' . $data['ip_address'] . "%0A" . 
	'Country: ' . $data['geolocation']['countryName'] . ', ' . $data['geolocation']['cityName']. "%0A%0A" . 
    'Plugin developed by SiteGuarding.com';
                
    if ($telegram_bot_api_token != '' && $chat_id != '')
    {
        $content = wp_remote_retrieve_body( wp_remote_get("https://api.telegram.org/bot".$telegram_bot_api_token."/sendMessage?chat_id=".$chat_id."&parse_mode=html&text=".$str) );
    }
}




function plgwpuan_action_user_login_success( $user_info )
{
    plgwpuan_process_login_action( $user_info, 'success' );
}


function plgwpuan_action_user_login_failed( $user_info )
{
    plgwpuan_process_login_action( $user_info, 'failed' );
}


function plgwpuan_process_login_action($user_login, $type)
{
	$settings = array (
		'send_notification_success',
		'send_notification_failed',
		'notification_email',
		'send_by_telegram',
		'telegram_bot_api_token',
		'chat_id',
		'reg_code',
	);
	

    $userdata = get_user_by('login', $user_login);

    $uid = ($userdata && $userdata->ID) ? $userdata->ID : 0;

	if ($uid > 0)
	{
        $data = array();
		$domain = get_site_url();
		$data['domain'] = $domain;
		$data['datetime'] = date("d F Y, H:i:s");
		$data['ip_address'] = trim($_SERVER['REMOTE_ADDR']);
		$data['browser'] = $_SERVER['HTTP_USER_AGENT'];
		$data['username'] = $user_login;
		$link = 'http://api.ipinfodb.com/v3/ip-city/?key=524ec42c675fe66c37cc26f5e289f98555be21e05720bda46e51da63aa58a2ca&ip='.$data['ip_address'].'&format=json';
		$result = file_get_contents($link);
		$data['geolocation'] = (array)json_decode($result,true);
        
        $params = FUNC_WAP2_general::Get_SQL_Params($settings);
        
		$data['admin_email'] = $params['notification_email'] ? $params['notification_email'] : '';
		$send_notification_success = $params['send_notification_success'];
		$send_notification_failed = $params['send_notification_failed'];
		$data['free'] = false;

		switch ($type)
		{
			case  'success':
                if ($send_notification_success || $send_notification_success == 1)
                {
    				$data['login_status'] = 'Successful login';
    				$message = 'User <b>'.$data['username'].'</b> successfully has logged to '.$domain.'<br>If you didn\'t login, please change your password and contact website support team.';
                    plgwpuan_NotifyAdmin($message, false, $data);
                    
                    if (intval($params['send_by_telegram']) == 1)
                    {
                        $message = 'User <b>'.$data['username'].'</b> successfully has logged to <b>'.$domain.'</b>'."%0A%0A".'If you didn\'t login, please change your password and contact website support team.';
                        plgwpuan_Notify_Telegram($params['telegram_bot_api_token'], $params['chat_id'], $data, $message);
                    }
                }
				break;
				
			case  'failed':
                if ($send_notification_failed || $send_notification_failed == 1)
                {
    				$data['login_status'] = 'Failed login';
    				$message = '<span style="color:#D54E21">Someone has tried to login as <b>'.$data['username'].'</b> to '.$domain.' with wrong password.</span><br>If it\'s not you, it means the hacker knows your username, please change your username and password to strong and uniq.';
                    plgwpuan_NotifyAdmin($message, false, $data);
                    
                    if (intval($params['send_by_telegram']) == 1)
                    {
                        $message = 'Someone has tried to login as <b>'.$data['username'].'</b> to <b>'.$domain.'</b> with wrong password.'."%0A%0A".'If it\'s not you, it means the hacker knows your username, please <b>change your username</b> and password to strong and uniq.';
                        plgwpuan_Notify_Telegram($params['telegram_bot_api_token'], $params['chat_id'], $data, $message);
                    }
                }
				break;
		}
		
	}
}


