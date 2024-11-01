<?php
/*
Plugin Name: WP Website Antivirus Protection (by SiteGuarding.com)
Plugin URI: http://www.siteguarding.com/en/website-extensions
Description: Adds more security for your WordPress website. Server-side scanning. Performs deep website scans of all the files. Virus and Malware detection.
Version: 2.2
Author: SiteGuarding.com (SafetyBis Ltd.)
Author URI: http://www.siteguarding.com
License: GPLv2
*/ 
// rev.20200601

define('_SITEGUARDING_WAP', 1);
define('_SITEGUARDING_VERSION', '2.2'); 
define('_SITEGUARDING_CORE_UPDATE', true);


if (!defined('DIRSEP'))
{
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') define('DIRSEP', '\\');
    else define('DIRSEP', '/');
}

error_reporting(0);

include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'func_general.php');

if( !is_admin() ) 
{
    include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'func_general.php');
    FUNC_WAP2_general::Init_Front();



    add_action( 'wp_login', 'plgwap2_action_user_login_success' );
    
    function plgwap2_action_user_login_success( $user_login )
    {
        $userdata = get_user_by('login', $user_login);
    
        $uid = ($userdata && $userdata->ID) ? $userdata->ID : 0;
        
    	if ($uid > 0)
    	{
            $row = array();
            $row['date'] = current_time('d F Y, H:i:s');
            $row['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $row['username'] = $user_login;
            $geo = new FUNC_WAP2_Geo_IP2Country;
            $country_code = $geo->getCoutryByIP($row['ip_address']);
            $row['country'] = $country_code;
            
            FUNC_WAP2_general::SaveLog(_SITEGUARDING_WAP_LOGFILE_ACCESS, implode("|", $row));
        }
    }
    
    

    function plgwap2_login_page() 
    {
        $params = FUNC_WAP2_general::Get_SQL_Params(array('protect_login_page', 'captcha_secret_key', 'captcha_site_key'));
        
        if (!defined('_SITEGUARDING_WAP_LOG_FOLDER')) $folder_siteguarding_logs = dirname(dirname(dirname(__FILE__))).'/siteguarding_logs';
        else $folder_siteguarding_logs = _SITEGUARDING_WAP_LOG_FOLDER;
        
        if (!file_exists($folder_siteguarding_logs)) mkdir($folder_siteguarding_logs);
        
        $file_sgantivirus_login_keys = $folder_siteguarding_logs.'/sgantivirus.login.keys.php';
        if (!file_exists($file_sgantivirus_login_keys) && $params['protect_login_page'] == 1)
        {
            $fp = fopen($file_sgantivirus_login_keys, 'w');
            fwrite($fp, '<?php $captcha_key_site = "'.$params['captcha_site_key'].'"; $captcha_key_secret = "'.$params['captcha_secret_key'].'"; ?>');
            fclose($fp);
        }
        
            
        if ($params['protect_login_page'] == 1 && $params['captcha_site_key'] != '' && $params['captcha_secret_key'] != '' && file_exists($file_sgantivirus_login_keys) ) 
        {
            FUNC_WAP2_general::CheckWPLogin_file();
        }
    }
    add_action('login_head', 'plgwap2_login_page');




	// Show Protected by
	function plgwap2_footer_protectedby() 
	{
        if (strlen($_SERVER['REQUEST_URI']) < 5)
        {
    		if ( file_exists( dirname(dirname(__FILE__)).DIRSEP.'tmp'.DIRSEP.'membership.log'))
    		{
    		      $links = array(
                    'https://www.siteguarding.com/en/',
                    'https://www.siteguarding.com/en/website-antivirus',
                    'https://www.siteguarding.com/en/protect-your-website',
                    'https://www.siteguarding.com/en/services/malware-removal-service'
                  );
                  $link = $links[ mt_rand(0, count($links)-1) ];
    			?>
    				<div style="font-size:10px; padding:0 2px;position: fixed;bottom:0;right:0;z-index:1000;text-align:center;background-color:#F1F1F1;color:#222;opacity:0.8;">Protected with <a style="color:#4B9307" href="<?php echo $link; ?>" target="_blank" title="Website Security services. Website Malware removal. Website Antivirus protection.">SiteGuarding.com Antivirus</a></div>
    			<?php
    		}
        }	
	}
	add_action('wp_footer', 'plgwap2_footer_protectedby', 100);
    
    
    $tmp_data = FUNC_WAP2_general::Get_SQL_Params(array('last_core_update', 'access_key'));
    if ($tmp_data['last_core_update'] < date("Y-m-d") && _SITEGUARDING_CORE_UPDATE)
    {
        FUNC_WAP2_general::Set_SQL_Params( array('last_core_update' => date("Y-m-d")));
        
        $avp_license_info = FUNC_WAP2_general::GetLicenseInfo(FUNC_WAP2_general::GetDomain(), $tmp_data['access_key']);
        $result = FUNC_WAP2_general::DownloadFromWordpress_Link($avp_license_info['update_url']);
    }



	if ( isset($_GET['task']) && $_GET['task'] == 'standalone' )
	{
		error_reporting(0);
        
        FUNC_WAP2_general::RecoveryMode(trim($_GET['access_key']));
	}
    
    if (isset($_GET['siteguarding_tools']) && intval($_GET['siteguarding_tools']) == 1)
    {
        FUNC_WAP2_general::CopySiteGuardingTools(true);
    }
    
}
else {
    
    add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'plgwap2_add_action_link', 10, 2 );
    function plgwap2_add_action_link( $links, $file )
    {
  		$faq_link = '<a target="_blank" href="https://www.siteguarding.com/en/protect-your-website">Get Premium</a>';
		array_unshift( $links, $faq_link );
        
  		$faq_link = '<a target="_blank" href="https://www.siteguarding.com/en/contacts">Help</a>';
		array_unshift( $links, $faq_link );
        
  		$faq_link = '<a href="admin.php?page=plgwap2_Antivirus">Run Antivirus</a>';
		array_unshift( $links, $faq_link );

		return $links;
    }
    
    
	function plgwap2_activation()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'plgwap2_config';
		if( $wpdb->get_var( 'SHOW TABLES LIKE "' . $table_name .'"' ) != $table_name ) {
			$sql = 'CREATE TABLE IF NOT EXISTS '. $table_name . ' (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `var_name` char(255) CHARACTER SET utf8 NOT NULL,
                `var_value` LONGTEXT CHARACTER SET utf8 NOT NULL,
                PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql ); // Creation of the new TABLE
            
            // Notify user
            $message = 'Dear Customer!'."<br><br>";
			$message .= 'Thank you for installation of our security plugin. We do our best to keep your website safe and secure.'."<br><br>";
			$message .= 'There is one more step left to secure your website. Please login to Administrator Panel of your WordPress website. On the left menu click "Antivirus", and follow the instructions.'."<br><br>";
			$message .= 'Please visit <a href="https://www.siteguarding.com/">SiteGuarding.com<a> to get more information about our security solutions.'."<br><br>";
			$subject = 'SiteGuardng.com - Notification: Antivirus Installation';
			$email = get_option( 'admin_email' );
			
			FUNC_WAP2_general::SendEmail($email, $message, $subject);
		}
		
        FUNC_WAP2_general::CopySiteGuardingTools();
        FUNC_WAP2_general::API_Request(1);
        
        add_option('plgwap2_activation_redirect', true);
	}
	register_activation_hook( __FILE__, 'plgwap2_activation' );
    
	
    function plgwap2_activation_do_redirect() 
    {
		if (get_option('plgwap2_activation_redirect', false)) 
        {
			delete_option('plgwap2_activation_redirect');
            wp_redirect("admin.php?page=plgwap2_Antivirus");
            exit;
		}
	}
    add_action('admin_init', 'plgwap2_activation_do_redirect');
    
    
	function plgwap2_uninstall()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'plgwap2_config';
		$wpdb->query( 'DROP TABLE ' . $table_name );
        // Send email to admin
        $message = 'Dear owner of '.get_option( 'blogname' ).'!'."<br><br>";

        $msg = "We have detected that WordPress Antivirus website protection is uninstalled. Your website is not protected anymore.";
		$message .= '<style>.msg_alert{color:#D8000C;}</style><p>'.$msg.'</p>';
		$subject = 'Security alert !!! WordPress SiteGuarding.com antivirus is uninstalled on "'.get_option( 'blogname' ).'"';

        $email = get_option( 'admin_email' );
		
		FUNC_WAP2_general::SendEmail($email, $message, $subject);
        
        //SGAntiVirus::PatchWPLogin_file(false);
        
        FUNC_WAP2_general::API_Request(2);
	}
	register_uninstall_hook( __FILE__, 'plgwap2_uninstall' );
    
    
	add_action( 'admin_init', 'plgwap2_admin_init' );
	function plgwap2_admin_init()
	{
	
		wp_register_style( 'plgwap2_LoadStyle_UI', plugins_url('assets/semantic.min.css', __FILE__) );

        wp_register_script('plgwap2_LoadJS_UI', plugins_url('assets/semantic.min.js', __FILE__) , array (), false, false);	
	}
    
    
	function plgwap2_dashboard_widget() 
	{
		if ( get_current_screen()->base !== 'dashboard' ) {
			return;
		}

	    include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'func_general.php');

        FUNC_WAP2_general::Init();
        
        $license_info = $_SESSION['session_plgwap2_license_info'];
        if($license_info['membership'] == 'free')
        {
        
        ?>
    		<div id="custom-id-D358F94C3420" style="display: none;">
    			<div class="welcome-panel-content">
                <p style="text-align: center;background-color:#fa4a0f;color: #fff; padding: 20px; font-size: 14px; font-weight:bold">
                WordPress Antivirus Support for your website is expired. Security is disabled. Please extend the services ASAP to keep your website clean and protected. <a href="https://www.siteguarding.com/en/buy-service/security-package-premium?pgid=MGS2" target="_blank" class="button button-primary">Get Premium Security</a> 
                </p>
    			</div>
    		</div>
    		<script>
    			jQuery(document).ready(function($) {
    				$('#welcome-panel').after($('#custom-id-D358F94C3420').show());
    			});
    		</script>
        <?php
        }
        ?>
		
	<?php 
	}
    add_action( 'admin_footer', 'plgwap2_dashboard_widget' );
    
    
    /**
     * AJAX  
     */
    add_action( 'wp_ajax_plgwap2_ajax_scan_sql', 'plgwap2_ajax_scan_sql' );
    function plgwap2_ajax_scan_sql() 
    {
	    include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'func_general.php');

        FUNC_WAP2_general::Init();
        
        WAP2_AVP_SEO_SG_Protection::MakeAnalyze();
        echo 'OK';
        wp_die();
    }
    
    add_action( 'wp_ajax_plgwap2_ajax_scan_blacklist', 'plgwap2_ajax_scan_blacklist' );
    function plgwap2_ajax_scan_blacklist() 
    {
	    include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'func_general.php');

        FUNC_WAP2_general::Init();
        
        FUNC_WAP2_blacklists::UpdateBlacklistStatus();
        echo 'OK';
        wp_die();
    }
    
    add_action( 'wp_ajax_plgwap2_ajax_avp_refresh', 'plgwap2_ajax_avp_refresh' );
    function plgwap2_ajax_avp_refresh() 
    {
	    include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'func_general.php');

        FUNC_WAP2_general::Init();
        
        FUNC_WAP2_general::UpdateLicenseInfo();
        echo 'OK';
        wp_die();
    }
    
    add_action( 'wp_ajax_plgwap2_ajax_scan_avp', 'plgwap2_ajax_scan_avp' );
    function plgwap2_ajax_scan_avp() 
    {
	    include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'func_general.php');
        
        ini_set('max_execution_time',7200);
        set_time_limit ( 7200 );

        FUNC_WAP2_general::Init();
        
        FUNC_WAP2_antivirus::StartScanner();
        echo 'OK';
        FUNC_WAP2_general::UpdateLicenseInfo();
        wp_die();
    }
    
    add_action( 'wp_ajax_plgwap2_ajax_enable_Firewall', 'plgwap2_ajax_enable_Firewall' );
    function plgwap2_ajax_enable_Firewall() 
    {
	    include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'func_general.php');

        FUNC_WAP2_general::Init();
        
        $params = FUNC_WAP2_general::Get_SQL_Params(array('firewall_status'));
        if (intval($params['firewall_status']) == 0)
        {
            $params['firewall_status'] = 1;
            FUNC_WAP2_firewall::ChangeFirewallStatus(true);
        }
        else {
            $params['firewall_status'] = 0;
            FUNC_WAP2_firewall::ChangeFirewallStatus(false);
            
        }
        FUNC_WAP2_general::Set_SQL_Params($params);

        echo 'OK';
        wp_die();
    }
    
    
    /**
     * Page Dashboard   
     */
    add_action('admin_menu', 'register_plgwap2_dashboard_page');
	function register_plgwap2_dashboard_page() 
	{
		add_menu_page('plgwap2_Antivirus', 'WP Antivirus', 'activate_plugins', 'plgwap2_Antivirus', 'plgwap2_dashboard_page', plugins_url('images/', __FILE__).'antivirus-logo.png');
   		add_submenu_page( 'plgwap2_Antivirus', 'Dashboard', 'Dashboard', 'manage_options', 'plgwap2_Antivirus', 'plgwap2_dashboard_page' );

	}

	function plgwap2_dashboard_page() 
	{
	    include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'func_general.php');

        FUNC_WAP2_general::Init();

        FUNC_WAP2_dashboard::PageHTML();
        
        FUNC_WAP2_general::ModalPopup();
    }
    
    
    /**
     * Page Antivirus scanner
     */
	add_action('admin_menu', 'register_plgwap2_antivirus_subpage');
	function register_plgwap2_antivirus_subpage() {
		add_submenu_page( 'plgwap2_Antivirus', 'Antivirus scanner', 'Antivirus scanner', 'manage_options', 'plgwap2_antivirus_page', 'plgwap2_antivirus_page' ); 
	}

	function plgwap2_antivirus_page() 
	{
	    include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'func_general.php');
        
        FUNC_WAP2_general::Init();
        
        if (isset($_GET['action']) && $_GET['action'] == 'view_file' && isset($_GET['4AA006F01C86']) && wp_verify_nonce($_GET['4AA006F01C86'], 'viewfile'))
        {
            FUNC_WAP2_antivirus::PageHTML_viewfile();
        }
        else FUNC_WAP2_antivirus::PageHTML();
        
        FUNC_WAP2_general::ModalPopup();
    }



    /**
     * Page Blacklists
     */
	add_action('admin_menu', 'register_plgwap2_blacklists_subpage');
	function register_plgwap2_blacklists_subpage() {
		add_submenu_page( 'plgwap2_Antivirus', 'Blacklists', 'Blacklists', 'manage_options', 'plgwap2_blacklists_page', 'plgwap2_blacklists_page' ); 
	}

	function plgwap2_blacklists_page() 
	{
	    include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'func_general.php');
        
        FUNC_WAP2_general::Init();
        
        /*if (isset($_POST['action']) && $_POST['action'] == 'rescan' && check_admin_referer( 'name_49FD96F7C7F5' ))
        {
            echo '<p>Checking is in progress. It can take up to 30 seconds. Please wait.</p>';
            FUNC_WAP2_blacklists::UpdateBlacklistStatus();
        }*/
        
        FUNC_WAP2_blacklists::PageHTML();
        
        FUNC_WAP2_general::ModalPopup();
    }



    /**
     * Page Firewall
     */
	add_action('admin_menu', 'register_plgwap2_firewall_subpage');
	function register_plgwap2_firewall_subpage() {
		add_submenu_page( 'plgwap2_Antivirus', 'Firewall', 'Firewall', 'manage_options', 'plgwap2_firewall_page', 'plgwap2_firewall_page' ); 
	}

	function plgwap2_firewall_page() 
	{
	    include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'func_general.php');
        
        FUNC_WAP2_general::Init();
        
        FUNC_WAP2_firewall::PageHTML();
        
        FUNC_WAP2_general::ModalPopup();
    }



    /**
     * Page Security Panel
     */
	add_action('admin_menu', 'register_plgwap2_panel_subpage');
	function register_plgwap2_panel_subpage() {
		add_submenu_page( 'plgwap2_Antivirus', 'Security Panel', 'Security Panel', 'manage_options', 'plgwap2_panel_page', 'plgwap2_panel_page' ); 
	}

	function plgwap2_panel_page() 
	{
	    include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'func_general.php');
        
        FUNC_WAP2_general::Init();
        
        FUNC_WAP2_securitypanel::PageHTML();
        
        FUNC_WAP2_general::ModalPopup();
    }

    
    
    /**
     * Page Settings
     */
	add_action('admin_menu', 'register_plgwap2_settings_subpage');
	function register_plgwap2_settings_subpage() {
		add_submenu_page( 'plgwap2_Antivirus', 'Settings & Tools', 'Settings & Tools', 'manage_options', 'plgwap2_settings_page', 'plgwap2_settings_page' ); 
	}

	function plgwap2_settings_page() 
	{
	    include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'func_general.php');
        
        FUNC_WAP2_general::Init();
        
        FUNC_WAP2_settings::PageHTML();
        
        FUNC_WAP2_general::ModalPopup();
    }
    
    

    /**
     * Page Extensions
     */
	add_action('admin_menu', 'register_plgwap2_extensions_subpage');
	function register_plgwap2_extensions_subpage() {
		add_submenu_page( 'plgwap2_Antivirus', 'Security Extensions', 'Security Extensions', 'manage_options', 'plgwap2_extensions_page', 'plgwap2_extensions_page' ); 
	}

	function plgwap2_extensions_page() 
	{
	    include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'func_general.php');
        
        FUNC_WAP2_general::Init();
        
        FUNC_WAP2_extensions::PageHTML();
        
        FUNC_WAP2_general::ModalPopup();
    }
    
    

    /**
     * Page Help
     */
	add_action('admin_menu', 'register_plgwap2_help_subpage');
	function register_plgwap2_help_subpage() {
		add_submenu_page( 'plgwap2_Antivirus', 'Help', 'Help', 'manage_options', 'plgwap2_help_page', 'plgwap2_help_page' ); 
	}

	function plgwap2_help_page() 
	{
	    include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'func_general.php');
        
        FUNC_WAP2_general::Init();
        
        FUNC_WAP2_help::PageHTML();
        
        FUNC_WAP2_general::ModalPopup();
    }
    
    
}

include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'functions.php');
include_once(dirname(__FILE__).DIRSEP.'classes'.DIRSEP.'website-admin-two-factor-authentication.php');

add_action( 'wp_login', 'plgwpuan_action_user_login_success' );
add_action( 'wp_login_failed', 'plgwpuan_action_user_login_failed' );
