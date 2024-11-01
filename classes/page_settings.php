<?php
defined('_SITEGUARDING_WAP') or die;

class FUNC_WAP2_settings
{
	public static $settings_list = array(
		'access_key',
		'email_for_notifications',
		'show_protectedby',
		'send_notifications',
        
		'protect_login_page',
		'captcha_secret_key',
		'captcha_site_key',
        
		'rules_blocked_ip',
		'rules_allowed_ip',
		'rules_blocked_files',
		'rules_blocked_urls',
        
		'enable_access_notification',
        
		'enable_2fa',
		
		'send_notification_success',
		'send_notification_failed',
		'notification_email',
		'send_by_telegram',
		'telegram_bot_api_token',
		'chat_id',
		'reg_code',

	);
    
    public static function PageHTML()  
    {
        wp_enqueue_style( 'plgwap2_LoadStyle_UI' );
      //  wp_enqueue_style( 'plgwap2_test' );
        wp_enqueue_script( 'plgwap2_LoadJS_UI', '', array(), false, true );
        
        $params = FUNC_WAP2_general::Get_SQL_Params(self::$settings_list);
        if (trim($params['email_for_notifications']) == '') 
        {
            $params['email_for_notifications'] = get_option( 'admin_email' );
            FUNC_WAP2_general::Set_SQL_Params( array('email_for_notifications' => $params['email_for_notifications']) );
        }
        if ($_SESSION['session_plgwap2_license_info']['membership'] != 'pro')
        {
            $params['show_protectedby'] = 1;
            FUNC_WAP2_general::Set_SQL_Params( array('show_protectedby' => 1) );
        }
        ?>

        <?php
            FUNC_WAP2_general::Wait_CSS_Loader();
        ?>
        
        <div id="main" class="ui main container" style="float: left;margin-top:20px;display:none;">
        
            <?php
            if (self::CheckActions()) $params = FUNC_WAP2_general::Get_SQL_Params(self::$settings_list);
            ?>
        
            <h2 class="ui dividing header">Settings & Tools</h2>
            
            
            <?php if (!FUNC_WAP2_general::IsPRO()) FUNC_WAP2_general::BannerArea(); ?>




            <form method="post" id="plgwpagp_decision_page" action="admin.php?page=plgwap2_settings_page">

            <div class="ui styled accordion full_w">
              <div class="active title">
                <i class="dropdown icon"></i>
                Antivirus settings
              </div>
              <div class="active content">
                <div class="ui form full_h">
                  <div class="field">
                    <label>Access Key</label>
                    <input type="text" name="access_key" id="access_key" value="<?php echo $params['access_key']; ?>">
                    <p class="ui tiny c_red">This key is necessary to access to <a target="_blank" href="http://www.siteguarding.com">SiteGuarding API</a> features. Every website has uniq access key. Don't change it if you don't know what is it.</p>
                  </div>
                </div>
              </div>




              <div class="title">
                <i class="dropdown icon"></i>
                Notifications
              </div>
              <div class="content">
                <div class="ui form full_h">
                    <div class="inline field">
                        <div class="ui toggle checkbox">
                            <input type="checkbox" name="send_notifications" type="checkbox" id="send_notifications" class="hidden" value="1" <?php if ($params['send_notifications'] == 1) echo 'checked="checked"'; ?>>
                            <label>Send Notifications <span class="c_red">We will send important notifications by email.</span></label>
                        </div>
                    </div>
                    
                  <div class="field">
                    <label>Email for Notifications</label>
                    <input type="text" name="email_for_notifications" id="email_for_notifications" value="<?php echo $params['email_for_notifications']; ?>">
                  </div>
                </div>
              </div>




              <div class="title">
                <i class="dropdown icon"></i>
                Tool: Bruteforce protection
              </div>
              <div class="content">
                <?php
                   $data = array(
                        'header' => 'Bruteforce protection for login page',
                        'content' => '<p><img style="float:left; padding:5px 25px 10px 0" src="'.plugins_url('images/bruteforce-attack.png', dirname(__FILE__)).'" /><b>Activates special captcha page against bruteforce attack.</b><br><br>Brute-force attack is the most common attack, used against Web applications. The purpose of this attack is to gain access to user’s accounts by repeated attempts to guess the password of the user or group of users. If the Web application does not have any protective measures against this type of attack, it is quite simple to hack the system. This method of password guessing is good because in the end the password is cracked, but it may take a very, very long time.<br><br><a class="mini ui green button" href="'.FUNC_WAP2_general::$LINKS['learn_bruteforce'].'" target="_blank">Learn more</a></p>',
                   );
                   FUNC_WAP2_general::Print_MessageBox($data);
                ?>
                <h3 class="ui dividing header">Settings</h3>
                <div class="ui form full_h">
                    <div class="inline field">
                        <div class="ui toggle checkbox">
                            <input type="checkbox" name="protect_login_page" type="checkbox" id="protect_login_page" class="hidden" value="1" <?php if ($params['protect_login_page'] == 1) echo 'checked="checked"'; ?>>
                            <label>Enable Bruteforce protection (protection for administrator login page)</label>
                        </div>
                    </div>
                    <div class="field">
                      <label>reCAPTCHA Site Key</label>
                      <input type="text" name="captcha_site_key" id="captcha_site_key" value="<?php echo $params['captcha_site_key']; ?>">
                    </div>
                    <div class="field">
                      <label>reCAPTCHA Secret key</label>
                      <input type="text" name="captcha_secret_key" id="captcha_secret_key" value="<?php echo $params['captcha_secret_key']; ?>">
                    </div>
                </div>
                    
                <p>Get reCAPTCHA keys for your site here <a target="_blank" href="https://www.google.com/recaptcha/intro/index.html">https://www.google.com/recaptcha/intro/index.html</a></p>
                
                <h3 class="ui dividing header">Help with reCAPTCHA keys</h3>
                <p></p><b>Step 1. Go to <a target="_blank" href="https://www.google.com/recaptcha/intro/index.html">https://www.google.com/recaptcha/</a> and fill the form</b><br><br>
                <img src="<?php echo plugins_url('images/help1.jpg', dirname(__FILE__)); ?>"/><br><br>
                
                <br><b>Step 2. Copy and Insert the keys</b><br><br>
                <img src="<?php echo plugins_url('images/help2.jpg', dirname(__FILE__)); ?>"/>
                </p>
              </div>




              <div class="title">
                <i class="dropdown icon"></i>
                Tool: Administrator Access Notification (by email or Telegram)
              </div>
              <div class="content">
                <div class="ui form full_h">
                    <div class="inline field">
                        <div class="ui toggle checkbox">
                            <input type="checkbox" name="enable_access_notification" type="checkbox" id="enable_access_notification" class="hidden" value="1" <?php if ($params['enable_access_notification'] == 1) echo 'checked="checked"'; ?>>
                            <label>Enable Administrator Access Notification (we will send you an notification about all login actions)</label>
                        </div>
                    </div>
						<div class="field">
						  <label>Email</label>
						  <input type="text" name="notification_email" id="notification_email" value="<?php echo $params['notification_email']; ?>">
						</div>
					<div class="ui segment">
						<div class="inline field">
							<div class="ui checkbox">
								<input name="send_notification_success" type="checkbox" id="send_notification_success" value="1" <?php if (intval($params['send_notification_success']) == 1) echo 'checked="checked"'; ?>> 
								<label> Send for successful login action </label>
							</div>
						</div>
						<div class="inline field">
							<div class="ui checkbox">
							   <input name="send_notification_failed" type="checkbox" id="send_notification_failed" value="1" <?php if (intval($params['send_notification_failed']) == 1) echo 'checked="checked"'; ?>>
								<label> Send for failed login action </label>
							</div>
						</div>
					</div>
					<div class="inline field">
                        <div class="ui toggle checkbox">
                            <input name="send_by_telegram" type="checkbox" id="send_by_telegram" value="1" <?php if (intval($params['send_by_telegram']) == 1) echo 'checked="checked"'; ?>>
                            <label>Enable Telegram (Send notification to your <a href="https://telegram.org/" target="_blank">Telegram Messenger</a>)</label>
                        </div>
					</div>
						<div class="field">
						  <label>Telegram Bot API Token</label>
						  <input type="text" name="telegram_bot_api_token" id="telegram_bot_api_token" value="<?php echo $params['telegram_bot_api_token']; ?>">
						  <p class="ui tiny">&nbsp;&nbsp;Please learn more how to <a target="_blank" href="https://www.siteguarding.com/en/how-to-get-telegram-bot-api-token">Get your API Token</a></p>
						</div>
						<div class="field">
						  <label>Chat ID</label>
						  <input type="text" name="chat_id" id="chat_id" value="<?php echo $params['chat_id']; ?>">
						  <p class="ui tiny c_red">&nbsp;&nbsp;keep it empty this field will be automatically filled after saving the settings</p>
						</div>
                    <?php /*
                    <div class="field">
                      <label>reCAPTCHA Site Key</label>
                      <input type="text" name="captcha_site_key" id="captcha_site_key" value="<?php echo $params['captcha_site_key']; ?>">
                    </div>
                    <div class="field">
                      <label>reCAPTCHA Secret key</label>
                      <input type="text" name="captcha_secret_key" id="captcha_secret_key" value="<?php echo $params['captcha_secret_key']; ?>">
                    </div>
                    */?>
                </div>
              </div>




              <div class="title">
                <i class="dropdown icon"></i>
                Tool: Administrator Two-Factor Authentication
              </div>
              <div class="content">
                <div class="ui form full_h">
                    <div class="inline field">
                        <div class="ui toggle checkbox">
                            <input type="checkbox" name="enable_2fa" type="checkbox" id="enable_2fa" class="hidden" value="1" <?php if ($params['enable_2fa'] == 1) echo 'checked="checked"'; ?>>
                            <label>Enable Two-Factor Authentication for administrator login page</label>
						    
						</div>
                        </div>
						<p class="ui tiny">Please go to <a href="<?php echo get_site_url(); ?>/wp-admin/profile.php">your profile page</a> to cofigure </p>
                    </div>
                    <?php /*
                    <div class="field">
                      <label>reCAPTCHA Site Key</label>
                      <input type="text" name="captcha_site_key" id="captcha_site_key" value="<?php echo $params['captcha_site_key']; ?>">
                    </div>
                    <div class="field">
                      <label>reCAPTCHA Secret key</label>
                      <input type="text" name="captcha_secret_key" id="captcha_secret_key" value="<?php echo $params['captcha_secret_key']; ?>">
                    </div>
                    */?>
  
              </div>




              <div class="title">
                <i class="dropdown icon"></i>
                Additional Firewal Rules
              </div>
              <div class="content">
                <div class="ui form full_h">

                    <h4 class="ui header">Block by IP address</h4>
                    
                    <div class="ui ignored message">
                          <i class="help circle icon"></i>e.g. 200.150.160.1 or 200.150.160.* or or 200.150.*.*
                    </div>
                    
                    <div class="ui input" style="width: 100%;margin-bottom:10px">
                        <textarea name="rules_blocked_ip" style="width: 100%;height:200px" placeholder="Insert IP addresses or range you want to block, one by line"><?php echo $params['rules_blocked_ip']; ?></textarea>
                    </div>
                    
                    
                    <h4 class="ui header">Allowed IP addresses</h4>
                    
                    <div class="ui ignored message">
                          <i class="help circle icon"></i>e.g. 200.150.160.1 or 200.150.160.* or or 200.150.*.*
                    </div>
                    
                    <div class="ui input" style="width: 100%;margin-bottom:10px">
                        <textarea name="rules_allowed_ip" style="width: 100%;height:200px" placeholder="Insert IP addresses or range you want to allow for any action, one by line"><?php echo $params['rules_allowed_ip']; ?></textarea>
                    </div>
                    
                    
                    <h4 class="ui header">Block access to the files</h4>
                    
                    <div class="ui ignored message">
                          <i class="help circle icon"></i>e.g. /wp-config.php
                    </div>
                    
                    <div class="ui input" style="width: 100%;margin-bottom:10px">
                        <textarea name="rules_blocked_files" style="width: 100%;height:200px" placeholder="Insert the files you want to block for direct access, one by line"><?php echo $params['rules_blocked_files']; ?></textarea>
                    </div>
                    
                    
                    <h4 class="ui header">Block access to the URLs</h4>
                    
                    <div class="ui ignored message">
                          <i class="help circle icon"></i>e.g. /wp-admin/ (nobody will be able to login to /wp-admin/, don't forget to allow your IP)
                    </div>
                    
                    <div class="ui input" style="width: 100%;margin-bottom:10px">
                        <textarea name="rules_blocked_urls" style="width: 100%;height:200px" placeholder="Insert the URLs you want to block for direct access, one by line"><?php echo $params['rules_blocked_urls']; ?></textarea>
                    </div>


                </div>
              </div>
              
              
              
              
              <div class="title">
                <i class="dropdown icon"></i>
                General
              </div>
              <div class="content">
                <div class="ui form full_h">
                    <div class="inline field">
                        <div class="ui toggle checkbox">
                            <input <?php if ($_SESSION['session_plgwap2_license_info']['membership'] != 'pro') echo 'disabled="disabled"'; ?> type="checkbox" name="show_protectedby" type="checkbox" id="show_protectedby" class="hidden" value="1" <?php if ($params['show_protectedby'] == 1) echo 'checked="checked"'; ?>>
                            <label>Show 'Protected by'</label>
                        </div>
                    </div>
                    <div class="inline field">
                        <b>Server Time: </b><?php echo date("Y-m-d H:i:s"); ?>. This time stamp we will use in our logs. You can change timezone in <a href="options-general.php" target="_blank">WordPress General Settings</a>
                    </div>
                </div>
              </div>
              
            </div>
            
            <br />
            <button type="submit" class="medium positive ui button">Save Settings   </button>

    		<?php
    		wp_nonce_field( 'C30B990F5FB2' );
    		?>
            <input type="hidden" name="action" value="save_settings"/>

            </form>
            
            <script>
            jQuery(document).ready(function(){
                jQuery('.ui.accordion').accordion();
                jQuery('.ui.checkbox').checkbox();
                jQuery('#main').css('opacity','0');
                jQuery('#main').css('display','block');
                jQuery('#loader').css('display','none');
				fromBlur();
            });
			
			var i = 0;
			
			function fromBlur() {
				running = true;
					if (running){
					
						jQuery('#main').css("opacity", i);
						
						i = i + 0.02;

					if(i > 1) {
						running = false;
						i = 0;
					}
					if(running) setTimeout("fromBlur()",5);

				}
			}

            </script>
        </div>
        <?php
    } 
    
    
    public static function CheckActions()
    {
        if (!isset($_REQUEST['action']) || !check_admin_referer( 'C30B990F5FB2' )) return;
        
        $action = trim($_REQUEST['action']);
        
        if ($action == 'save_settings')
        {
            $data = array();
            
            foreach (self::$settings_list as $row)
            {
                $data[$row] = trim($_POST[$row]);
            }
            
			if ($data['notification_email'] == '') $data['notification_email'] = get_option( 'admin_email' );
            

            
            if ($data['chat_id'] == '' && $data['telegram_bot_api_token'] != '' && intval($data['send_by_telegram']) == 1) 
            {
                // get chat id
                $content = wp_remote_retrieve_body( wp_remote_get("https://api.telegram.org/bot".$data['telegram_bot_api_token']."/getUpdates") );
                if ($content != '')
                {
                    $content = json_decode($content);
                    if ($content != NULL)
                    {
                        $data['chat_id'] = $content->result[0]->message->chat->id;
                    }
                }
            }
			
            if ($data['protect_login_page'] == 1 && ($data['captcha_secret_key'] == '' || $data['captcha_site_key'] == '')) $data['protect_login_page'] = 0;
            
            $license_info = $_SESSION['session_plgwap2_license_info'];
            FUNC_WAP2_general::UpdateBruteForceLoginKeys($data['captcha_site_key'], $data['captcha_secret_key'], $license_info['exp_date']);
            
            // Patch wp-login.php
            if ($data['protect_login_page'] == 1) 
            {
                if (FUNC_WAP2_general::PatchWPLogin_file(true) === false)
                {
                    $data['protect_login_page'] = 0;
                    $_SESSION['session_plgwap2_alert_message'] = ' Protect Login page is disabled. Can\'t modify wp-config.php';
                }
            }
            if ($data['protect_login_page'] == 0) 
            {
                FUNC_WAP2_general::PatchWPLogin_file(false);
            }
            
            if ($_SESSION['session_plgwap2_license_info']['membership'] != 'pro') $data['show_protectedby'] = 1;
            
            
                // Firewall rules
                $folder = WP_CONTENT_DIR.'/siteguarding_firewall/';
                
                $files = array(
                    'rules_allowed_ip',
                    'rules_blocked_ip',
                    'rules_blocked_files',
                    'rules_blocked_urls'
                );
                
                $full_rules_txt = '';
                foreach ($files as $file)
                {
                    $filename = $folder.$file.".txt";
                    $txt = '';
                    if (isset($_POST[$file]))
                    {
                        $txt = trim($_POST[$file]);
                    }
                    else $txt = '';
                    
                    $fp = fopen($filename, 'w');
                    fwrite($fp, $txt);
                    fclose($fp);
                }
                
                self::CombineFirewallRules();
                        
            
            FUNC_WAP2_general::Set_SQL_Params($data);
                
            $msg_data = array(
                'type' => 'ok',
                'size' => 'small',
                'content' => 'Settings saved.',
           );
           FUNC_WAP2_general::Print_MessageBox($msg_data);
           
           unset($_SESSION['session_plgwap2_params']);
           unset($_SESSION['session_plgwap2_license_info']);
           
           return true;
        }
    }
    
    
	public static function CombineFirewallRules()
	{
	    $a = array();
        $folder = WP_CONTENT_DIR.'/siteguarding_firewall/';
        if (!file_exists($folder)) self::InstallFirewallFolder();
        
        $files = array(
            'rules_allowed_ip.txt' => '::ALLOW_ALL_IP::',
            'rules_blocked_ip.txt' => '::BLOCK_ALL_IP::',
            'rules_blocked_files.txt' => '::RULES::',
            'rules_blocked_urls.txt' => '::BLOCK_URLS::',
            'rules_requests.txt' => '::BLOCK_REQUESTS::'
        );
        
        $full_rules_txt = '';
        foreach ($files as $file => $firewall_section)
        {
            if (file_exists($folder.$file))
            {
                $filename = $folder.$file;
                $file_size = filesize($filename);
                if ($file_size > 0)
                {
                    $handle = fopen($filename, "r");
                    $txt = fread($handle, filesize($filename));
                    fclose($handle);
                }
                else $txt = '';
                
                $full_rules_txt .= $firewall_section."\n";
                
                if ($file == 'rules_blocked_files.txt' && $txt != '')
                {
                    $txt = explode("\n", $txt);
                    if (count($txt))
                    {
                        foreach ($txt as $k => $v)
                        {
                            $txt[$k] = 'allow|file|'.$v;
                        }
                        
                        $txt = implode("\n", $txt);
                    }
                }
                $full_rules_txt .= $txt."\n\n";
            }
        }
        
        $filename = $folder."rules.txt";
        $fp = fopen($filename, 'w');
        fwrite($fp, $full_rules_txt);
        fclose($fp);
	}
    
    
    
    
	public static function InstallFirewallFolder()
	{
        $folder = WP_CONTENT_DIR.'/siteguarding_firewall/';
        if (!file_exists($folder)) mkdir($folder);
        
        $file = $folder.'.htaccess';
        if (!file_exists($file))
        {
            $fp = fopen($file, 'w');
            $t = '<Limit GET POST>
order deny,allow
deny from all
</Limit>';
            fwrite($fp, $t);
            fclose($fp);
        }
        
        $file = $folder.'rules_requests.txt';
        if (!file_exists($file))
        {
            $fp = fopen($file, 'w');
            $t = 'cDF8Kg0KKnxiYXNlNjRfZGVjb2RlDQoqfHN0cl9yb3QxMw0KKnw8P3BocA0KKnxldmFsKA0KKnxGaWxlc01hbg0KKnxlZG9jZWRfNDZlc2FiDQoqfG1vdmVfdXBsb2FkZWRfZmlsZQ0KKnxleHRyYWN0KCRfQ09PS0lFKQ0KbG9nfHdwdXBkYXRlc3RyZWFtDQpleGVjdXRlfHdwX2luc2VydF91c2VyDQpsb2d8d3AuDQp1c2VybmFtZXxqb29tbGEu';
            $t = base64_decode($t);
            fwrite($fp, $t);
            fclose($fp);
        }
        
        $files = array(
            'rules_allowed_ip.txt',
            'rules_blocked_ip.txt',
            'rules_blocked_files.txt',
            'rules_blocked_urls.txt'
        );
        foreach ($files as $file)
        {
            $file = $folder.$file;
            if (!file_exists($file))
            {
                $fp = fopen($file, 'w');
                fwrite($fp, '');
                fclose($fp);
            }
        }
        
        $folder = $folder.'/logs/';
        if (!file_exists($folder)) mkdir($folder);
        
        $file = $folder.'.htaccess';
        if (!file_exists($file))
        {
            $fp = fopen($file, 'w');
            $t = '<Limit GET POST>
order deny,allow
deny from all
</Limit>';
            fwrite($fp, $t);
            fclose($fp);
        }
	}


    
    

}

?>