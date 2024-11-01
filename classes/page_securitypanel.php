<?php
defined('_SITEGUARDING_WAP') or die;

class FUNC_WAP2_securitypanel
{
    public static function PageHTML()  
    {
        wp_enqueue_style( 'plgwap2_LoadStyle_UI' );
        wp_enqueue_script( 'plgwap2_LoadJS_UI', '', array(), false, true );
        
        
	    $autologin_config = ABSPATH.DIRSEP.'webanalyze'.DIRSEP.'website-security-conf.php';
        if (file_exists($autologin_config)) include_once($autologin_config);
        
       
		$website_url = get_site_url();
        $admin_email = get_option( 'admin_email' );
        
        
        $success = FUNC_WAP2_general::CopySiteGuardingTools();
        
        ?>

        <?php
            FUNC_WAP2_general::Wait_CSS_Loader();
        ?>
        
        <style>
        .makecenter{text-align:center;margin-left:auto!important;margin-right:auto!important;}
        </style>
        <div id="main" class="ui main container" style="float: left;margin-top:20px;display:none">
            <h2 class="ui dividing header">Security Panel</h2>
            
            
            <?php
    		if ($success) 
            {
                if (defined('WEBSITE_SECURITY_AUTOLOGIN'))
                {
                    // file exists
                    ?>
                    <script>
                    jQuery(document).ready(function(){
                        jQuery("#autologin_form").submit();
                    });
                    </script>
                    <form action="https://www.siteguarding.com/index.php" method="post" id="autologin_form">

                    <div class="ui placeholder segment makecenter">
                      <div class="ui icon header">
                        <img  style="width:350px" src="<?php echo plugins_url('images/', dirname(__FILE__)).'logo_siteguarding.svg'; ?>" />
                        <i class="asterisk loading small icon"></i>Logging to the account. If it take more than 30 seconds, please login manually
                      </div>
                      <br />
                      <input class="ui green button" type="submit" value="Security Dashboard" />
                    </div>
    
                    
    
                    <input type="hidden" name="option" value="com_securapp" />
                    <input type="hidden" name="autologin_key" value="<?php echo WEBSITE_SECURITY_AUTOLOGIN; ?>" />
                    
                    <input type="hidden" name="service" value="website_list" />
                    
                    <input type="hidden" name="website_url" value="<?php echo $website_url; ?>" />
                    <input type="hidden" name="task" value="Panel_autologin" />
                    </form>
                    
                    <div class="ui section divider"></div>
                    
                    
                    <?php
                }
                else {
                    // Need to register the website
                    
                    // Create verification code
                    $verification_code = md5($website_url.'-'.time().'-'.rand(1, 1000).'-'.$admin_email);
                    $folder_webanalyze = ABSPATH.DIRSEP.'webanalyze';
                    $verification_file = $folder_webanalyze.DIRSEP.'domain_verification.txt';
    				$verification_file = str_replace(array('//', '///'), '/', $verification_file);
                    
                    // Create folder
                    if (!file_exists($folder_webanalyze)) mkdir($folder_webanalyze);
                    // Create verification file
                    $fp = fopen($verification_file, 'w');
                    fwrite($fp, $verification_code);
                    fclose($fp);
                    
                    ?>
                    
                    
                    <div class="ui placeholder segment makecenter">
                      <div class="ui icon header">
                        <img  style="width:350px" src="<?php echo plugins_url('images/', dirname(__FILE__)).'logo_siteguarding.svg'; ?>" />
                        <br /><br />
                        One more step to protect <?php echo $website_url; ?>
                      </div>
                      
                      <div class="ui divider"></div>
                      
                      
                      <form action="https://www.siteguarding.com/index.php" method="post" class="ui form">
    
                        <div class="ui grid">
                          <div class="column row">
                            <div class="column">
                                  <div class="fields">
                                    <div class="field makecenter" style="min-width: 400px;">
                                      <label>Your email for account</label>
                                      <input type="text" placeholder="Your email for account" name="email" value="<?php echo $admin_email; ?>">
                                    </div>
                                  </div>
                            </div>
                          </div>
                        </div>
                        
                        <div class="inline">
                            <input class="ui green button" type="submit" value="Register & Activate" />
                        </div>
                      
                        <input type="hidden" name="option" value="com_securapp" />
                        <input type="hidden" name="verification_code" value="<?php echo $verification_code; ?>" />
                        
                        <input type="hidden" name="service" value="website_list" />
                        
                        <input type="hidden" name="website_url" value="<?php echo $website_url; ?>" />
                        <input type="hidden" name="task" value="Panel_plugin_register_website" />
                        </form>
                    
                    
                    </div>
    
    
                    <div class="ui section divider"></div>
                    
                    <?php
                }
    
    		} else {
    		      ?>
                    <div class="ui negative message">
                      <div class="header">
                        Error is detected
                      </div>
                      <p>The file does not exist or corrupted. Could not to overwrite it. Please reinstall plugin from <a target="_blank" href="https://www.siteguarding.com">https://www.siteguarding.com</a>
                    </div>
                  <?php
    		}
            ?>
            
            
        </div>
        <?php
    } 
}

?>