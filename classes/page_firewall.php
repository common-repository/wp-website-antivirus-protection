<?php
defined('_SITEGUARDING_WAP') or die;

class FUNC_WAP2_firewall
{
    public static function PageHTML()  
    {
        wp_enqueue_style( 'plgwap2_LoadStyle_UI' );
        wp_enqueue_script( 'plgwap2_LoadJS_UI', '', array(), false, true );
        
        $params = FUNC_WAP2_general::Get_SQL_Params(array('firewall_status'));
        $params['firewall_status'] = intval($params['firewall_status']);
        
        
        ?>

        <?php
            FUNC_WAP2_general::Wait_CSS_Loader();
        ?>
        
        <div id="main" class="ui main container" style="float: left;margin-top:20px;display:none">
            <h2 class="ui dividing header">Website Firewall (WAF)</h2>
            
            <?php
            $viewlog_arr = self::CheckActions();
            ?>
            
            
            <div class="ui grid">
                <div class="six wide column center aligned">
                
                    <?php
                    if ($params['firewall_status'] == 1) {
                    ?>
                        <i class="huge check icon green"></i>
                        <p class="green">Firewall is active</p>
                        <p><div class="ui yellow horizontal label">basic rules</div></p>
                    <?php
                    }
                    else {
                    ?>
                        <i class="massive exclamation triangle icon red"></i>
                        <p class="green">Firewall is disabled</p>
                    <?php
                    }
                    ?>
        

                
                </div>
                
                <div class="ten wide column">
                    <?php
                    FUNC_WAP2_general::PremiumAdvertBlock();
                    ?>
                </div>
            
            </div>
            

            
            
            
            <?php if (!FUNC_WAP2_general::IsPRO()) FUNC_WAP2_general::BannerArea(); ?>


            <div class="ui grid">
                <div class="ten wide column">
                
                    <div class="ui raised segment full_h">
                    
                        <h3 class="ui dividing header">Settings, Manipulation with access and IP addresses</h3>
                        
                        <?php
                            if (!FUNC_WAP2_general::CheckGEOProtectionInstallation()) 
                            {
                                $action = 'install-plugin';
                                $slug = 'wp-geo-website-protection';
                                $install_url = wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'action' => $action,
                                            'plugin' => $slug
                                        ),
                                        admin_url( 'update.php' )
                                    ),
                                    $action.'_'.$slug
                                );
                                
                                $msg_data = array(
                                    'type' => 'info',
                                    'header' => 'GEO Website Protection',
                                    'content' => 'Security plugin GEO Website Protection is not installed on your website.<p class="mini">WP GEO Website Protection is the security plugin to limit access from unwanted counties or IP addresses.<br>You can easy filter front-end visitors and visitors who wants to login to Wordpress backend. Detailed Logs and Statistics. <a href="https://wordpress.org/plugins/wp-geo-website-protection/" target="_blank">Click to see the details</a></p>',
                                );
                                $button_url = $install_url;
                                $button_txt = 'Install GEO Protection';
                            }
                            else {
                                if (isset($license_info['extensions']['wp-geo-website-protection'])) $geo_ext_txt = '<br><p class="mini">Please use license key <b>'.$license_info['extensions']['wp-geo-website-protection'].'</b> to active PRO version.</p>';
                                else $geo_ext_txt = '';
                                
                                $msg_data = array(
                                    'type' => 'ok',
                                    'header' => 'GEO Website Protection',
                                    'content' => 'Security plugin GEO Website Protection is installed on your website.'.$geo_ext_txt,
                                );
                                $button_url = 'admin.php?page=plgsggeo_protection';
                                $button_txt = 'Configure GEO Protection';
                            }
                            FUNC_WAP2_general::Print_MessageBox($msg_data);
                        ?>
                        <p class="sg_center"><a href="<?php echo $button_url; ?>" class="medium positive ui button"><?php echo $button_txt; ?></a></p>
                    </div>
                    
                </div>
                
                <div class="six wide column">
                
                    <div class="ui raised segment full_h">
                    
                        <h3 class="ui dividing header">Firewall Status</h3>
                        
                        <div class="content">
                            <b>Status:</b>&nbsp;
                            <?php
                            if ($params['firewall_status'] == 1) echo '<i class="check green icon"></i> Firewall is enabled';
                            else echo '<i class="exclamation triangle red icon"></i> Firewall is disabled';
                            ?>
                        </div>
                        
                      <p>Website firewall is a barrier to keep destructive forces away from your website. Firewall works behind the scenes to control the flow of data and will alert you if suspicious activities occur.</p>
                      <p>
                    <script>
                            function ShowLoader_Firewall()
                            {
                                jQuery("#ajax_button_Firewall").hide();
                                jQuery("#scanner_ajax_loader_Firewall").show(); 
                                jQuery("#scanner_ajax_reportbox").show(); 
                                
                                jQuery.post(
                                    ajaxurl, 
                                    {
                                        'action': 'plgwap2_ajax_enable_Firewall'
                                    }, 
                                    function(response){
                                        document.location.href = 'admin.php?page=plgwap2_firewall_page';
                                    }
                                );  
                            }
                            </script>
                            <?php
                            if ($params['firewall_status'] == 0)
                            {
                                $btn_class = 'green';
                                $btn_txt = 'Enable Firewall';
                            }
                            else {
                                $btn_class = 'red';
                                $btn_txt = 'Disable Firewall';
                            }
                            ?>
                            <a id="ajax_button_Firewall" class="medium <?php echo $btn_class; ?> ui button" href="javascript:;" onclick="ShowLoader_Firewall()"><?php echo $btn_txt; ?></a>
                            <img id="scanner_ajax_loader_Firewall" width="48" height="48" style="display: none;" src="<?php echo plugins_url('images/ajax_loader.svg', dirname(__FILE__)); ?>" />

                            <a class="medium positive ui button" href="<?php echo FUNC_WAP2_general::$LINKS['upgrade_to_premium']; ?>" target="_blank">Upgrade</a></p>
                    </div>
                
           
                </div>
            
            </div>




            <div class="ui raised segment">
                <h3 class="ui header">Available log files</h3>
                <?php
                $i = count($viewlog_arr);
                if (count($viewlog_arr))
                {
                    if ($i <= 100) $txt_limit = '(Latest 100 records) <a href="'.wp_nonce_url(admin_url('admin.php?page=plgwap2_firewall_page&action=viewlog&viewall=1&file='.$_GET['file']), 'a', '4AA006F01C86').'">View All</a>';
                    ?>
                    <h4>View log: <?php echo $_GET['file']; ?></h4>
                    <p>
                    <?php
                    echo '<a style="text-decoration: none;" href="'.wp_nonce_url(admin_url('admin.php?page=plgwap2_firewall_page&action=deletelog&file='.$_GET['file']), 'a', '4AA006F01C86').'"><i class="ui trash alternate icon"></i> Delete this log file</a>';
                    ?>
                    </p>
                    
                    <table class="ui celled table"><thead><tr><th width="50">#</th><th>Log data <?php echo $txt_limit; ?></th></tr></thead><tbody>
                    <?php
                    foreach ($viewlog_arr as $row)
                    {
                        $row = explode("|", $row);
                        if (count($row) < 3) continue;
                        ?>
                        <tr><td><?php echo $i; ?></td>
                        <td>
                        <?php
                        
                        $geo = new FUNC_WAP2_Geo_IP2Country;
                        $country_code = $geo->getCoutryByIP($row[1]);
                        
                        $row[3] = str_replace(ABSPATH, "/", $row[3]);
                        echo 'Date: '.$row[0]."<br>";
                        echo 'IP: '.$row[1].' ('.$geo->getNameByCountryCode($country_code).')'."<br>";
                        echo 'URL: '.$row[2]."<br>";
                        echo 'File: '.$row[3];
                        if (isset($row[4])) echo "<br>".'Reason: '.$row[4];
                        ?>
                        </td></tr>
                        <?php
                        
                        $i--;
                    }
                    ?>
                    </tbody></table>
                    <?php
                }
                
                
                $folder = WP_CONTENT_DIR.'/siteguarding_firewall/logs/';
                $files = array();
                foreach (glob($folder."*.log.php") as $filename) 
                {
                    $f = explode(".php_", $filename);
                    $f_short = trim($f[0]).'.php';
                    $f_short = str_replace($folder, "", $f_short);
                    $files[$f_short] = array(
                        'size' => filesize($filename),
                        'fname' => basename($filename)
                    );
                }
                
                $list_html = '<table class="ui celled table"><thead><tr><th>Log file <a style="text-decoration: none;" href="'.wp_nonce_url(admin_url('admin.php?page=plgwap2_firewall_page&action=clear_all_logs'), 'a', '4AA006F01C86').'"><i class="ui trash alternate icon"></i> Clean All Logs</a></th><th style="width:20%">Size</th></tr></thead><tbody>';
                
                $file = WP_CONTENT_DIR.'/siteguarding_firewall/logs/_blocked.log';
                if (file_exists($file))
                {
                    $filesize = round(filesize($file) / 1024, 2);
                    $list_html .= '<tr><td><a style="text-decoration: none;" href="'.wp_nonce_url(admin_url('admin.php?page=plgwap2_firewall_page&action=deletelog&file=_blocked.log'), 'a', '4AA006F01C86').'"><i class="ui trash alternate icon"></i></a> <a href="'.wp_nonce_url(admin_url('admin.php?page=plgwap2_firewall_page&action=viewlog&file=_blocked.log'), 'a', '4AA006F01C86').'"><b><span style="color:#DD3D36">Blocked actions</span></b> <i class="ui file outline icon"></i></a></td><td>'.$filesize.' Kb</td></tr>';
                }
                foreach ($files as $file => $file_info)
                {
                    $filesize = round($file_info['size'] / 1024, 2);
                    $list_html .= '<tr><td><a style="text-decoration: none;" href="'.wp_nonce_url(admin_url('admin.php?page=plgwap2_firewall_page&action=deletelog&file='.$file_info['fname']), 'a', '4AA006F01C86').'"><i class="ui trash alternate icon"></i></a> <a href="'.wp_nonce_url(admin_url('admin.php?page=plgwap2_firewall_page&action=viewlog&file='.$file_info['fname']), 'a', '4AA006F01C86').'">'.$file.' <i class="ui file outline icon"></i></a></td><td>'.$filesize.' Kb</td></tr>';
                }
                $list_html .= '</tbody></table>';
                
                echo $list_html;
                ?>
            </div>
            
            
            
            
            <?php FUNC_WAP2_general::QuickLinks(); ?>

            
                   
        </div>
        <?php
    } 
    
    




    public static function CheckActions()
    {
        $viewlog_arr = array();
        
        if (!isset($_REQUEST['action']) || !wp_verify_nonce( $_GET['4AA006F01C86'], 'a' )) return $viewlog_arr;
        
        $action = trim($_REQUEST['action']);
        
        if ($action == 'viewlog')
        {
            $folder = WP_CONTENT_DIR.'/siteguarding_firewall/logs/';
            if (isset($_GET['file']))
            {
                $file = $folder.$_GET['file'];
                if (file_exists($file))
                {
                    $handle = fopen($file, "r");
                    $content = fread($handle, filesize($file));
                    fclose($handle);

                    $rows = explode("\n", $content);
                    for ($i = 0; $i <= 2; $i++)
                    {
                        if ($rows[$i][0] == "<" || $rows[$i][0] == "/") unset($rows[$i]);
                    }
                    
                    foreach($rows as $k => $row)
                    {
                        if (trim($row) == '') unset($rows[$k]);
                    }
                    
                    if (isset($_GET['viewall']) && intval($_GET['viewall']) == 1)
                    {
                        
                    }
                    else $rows = array_slice($rows, -100);
                    $rows = array_reverse($rows);
                    
                    $viewlog_arr = $rows;
                }
            }

        }
        
        if ($action == 'deletelog')
        {
            $folder = WP_CONTENT_DIR.'/siteguarding_firewall/logs/';
            if (isset($_GET['file']))
            {
                unlink($folder.$_GET['file']);
                
                $msg_data = array(
                    'type' => 'ok',
                    'size' => 'small',
                    'content' => 'Log file deleted.',
                );
                FUNC_WAP2_general::Print_MessageBox($msg_data);
            }
        }
        
        if ($action == 'clear_all_logs')
        {
            $folder = WP_CONTENT_DIR.'/siteguarding_firewall/logs/';
            foreach (glob($folder."*.log.php") as $filename) 
            {
                unlink($filename);
            }
            $file = $folder.'_blocked.log';
            if (file_exists($file))
            {
                unlink($file);
            }
            
            $msg_data = array(
                'type' => 'ok',
                'size' => 'small',
                'content' => 'All logs deleted.',
            );
            FUNC_WAP2_general::Print_MessageBox($msg_data);
        }
        
        return $viewlog_arr;
    }

    
    
    
    public static function ChangeFirewallStatus($status)
    {
        FUNC_WAP2_settings::InstallFirewallFolder();
        FUNC_WAP2_settings::CombineFirewallRules();
        $firewall_module = 'wp-content/siteguarding_firewall/firewall.php';
        if (!file_exists(SG_SITE_ROOT.$firewall_module)) copy(dirname(__FILE__).DIRSEP.'firewall.php', SG_SITE_ROOT.$firewall_module);
        
        $installer = new SG_Firewall_Auto_Prepend_Class();
        $installer->WORK_PATH = substr(SG_SITE_ROOT, 0, -1);
        $installer->FIREWALL_PATH = $firewall_module;
        $installer->setAutoPrepends($status);
    }
}









class SG_Firewall_Auto_Prepend_Class {
	
    public static $WORK_PATH = '';
    public static $FIREWALL_PATH = '';
    
	//const WORK_PATH = "d:/Work/www/wordpress";
	//const FIREWALL_PATH = "wp-content/plugins/wp-website-antivirus-protection/classes/firewall.php";
	const APACHE = 1;
	const NGINX = 2;
	const LITESPEED = 4;
	const IIS = 8;


	private $handler;
	private $software;
	private $softwareName;
	private $dirsep;
	

	public function __construct() {
		$sapi = php_sapi_name();
		if (stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false) {
			$this->setSoftware(self::APACHE);
			$this->setSoftwareName('apache');
		}
		if (stripos($_SERVER['SERVER_SOFTWARE'], 'litespeed') !== false || $sapi == 'litespeed') {
			$this->setSoftware(self::LITESPEED);
			$this->setSoftwareName('litespeed');
		}
		if (strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false) {
			$this->setSoftware(self::NGINX);
			$this->setSoftwareName('nginx');
		}
		if (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false || strpos($_SERVER['SERVER_SOFTWARE'], 'ExpressionDevServer') !== false) {
			$this->setSoftware(self::IIS);
			$this->setSoftwareName('iis');
		}
		
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$this->dirsep = '\\';
		} else {
			$this->dirsep = '/';
		}

		$this->setHandler($sapi);


	}

	public function isApache() {
		return $this->getSoftware() === self::APACHE;
	}


	public function isNGINX() {
		return $this->getSoftware() === self::NGINX;
	}


	public function isLiteSpeed() {
		return $this->getSoftware() === self::LITESPEED;
	}


	public function isIIS() {
		return $this->getSoftware() === self::IIS;
	}


	public function isApacheModPHP() {
		return $this->isApache() && function_exists('apache_get_modules');
	}


	public function isApacheSuPHP() {
		return $this->isApache() && $this->isCGI() &&
			function_exists('posix_getuid') &&
			getmyuid() === posix_getuid();
	}


	public function isCGI() {
		return !$this->isFastCGI() && stripos($this->getHandler(), 'cgi') !== false;
	}


	public function isFastCGI() {
		return stripos($this->getHandler(), 'fastcgi') !== false || stripos($this->getHandler(), 'fpm-fcgi') !== false;
	}


	public function getHandler() {
		return $this->handler;
	}


	public function setHandler($handler) {
		$this->handler = $handler;
	}


	public function getSoftware() {
		return $this->software;
	}


	public function setSoftware($software) {
		$this->software = $software;
	}


	public function getSoftwareName() {
		return $this->softwareName;
	}


	public function setSoftwareName($softwareName) {
		$this->softwareName = $softwareName;
	}

	
	public function getServerConfig() {
		if ($this->isApacheModPHP()) return 'apache-mod_php';
		if ($this->isApacheSuPHP()) return 'apache-suphp';
		if ($this->isLiteSpeed()) return 'cgi';
		if ($this->isApache() && !$this->isApacheSuPHP() && ($this->isCGI() || $this->isFastCGI())) return 'litespeed';
		if ($this->isNGINX()) return 'nginx';
		if ($this->isIIS()) return 'iis';
		
	}
	
	public function getHtaccessPath() {
		return str_replace(array("/", "\\"), $this->dirsep, $this->WORK_PATH . $this->dirsep . '.htaccess');
	}

	public function getUserIniPath() {
		$userIni = ini_get('user_ini.filename');
		if ($userIni) {
			return str_replace(array("/", "\\"), $this->dirsep, $this->WORK_PATH . $this->dirsep . $userIni);
		}
		return false;
	}

	public function getFirewallFilePath() {
		return str_replace(array("/", "\\"), $this->dirsep, $this->WORK_PATH . $this->FIREWALL_PATH);
	}



	function setAutoPrepends($state = true) {
		
		$bootstrapPath = $this->getFirewallFilePath();

		$serverConfig = $this->getServerConfig(); 

		$htaccessPath = $this->getHtaccessPath();

		$homePath = dirname($htaccessPath);

		$userIniPath = $this->getUserIniPath();
		$userIni = ini_get('user_ini.filename');
		
		if (!$state) {
			if (is_file($htaccessPath)) {
				$htaccessContent = @file_get_contents($htaccessPath);
				$regex = '/# SiteGuarding Firewall Block.*?# END SiteGuarding Firewall Block/is';
				if (preg_match($regex, $htaccessContent, $matches)) {
					$htaccessContent = preg_replace($regex, '', $htaccessContent);
					if (!file_put_contents($htaccessPath, $htaccessContent)) {
						return false;
					}
				}
			}

			if (is_file($userIniPath)) {
				$userIniContent = @file_get_contents($userIniPath);
				$regex = '/; SiteGuarding Firewall Block.*?; END SiteGuarding Firewall Block/is';
				if (preg_match($regex, $userIniContent, $matches)) {
					$userIniContent = preg_replace($regex, '', $userIniContent);
					if (!file_put_contents($userIniPath, $userIniContent)) {
						return false;
					}
				}
			}
			return true;
		} else {

		$userIniHtaccessDirectives = '';
		if ($userIni) {
			$userIniHtaccessDirectives = sprintf('<Files "%s">
<IfModule mod_authz_core.c>
	Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
	Order deny,allow
	Deny from all
</IfModule>
</Files>
', addcslashes($userIni, '"'));
		}


		// .htaccess configuration
		switch ($serverConfig) {
			case 'apache-mod_php':
				$autoPrependDirective = sprintf("# SiteGuarding Firewall Block
<IfModule mod_php%d.c>
	php_value auto_prepend_file '%s'
</IfModule>
$userIniHtaccessDirectives
# END SiteGuarding Firewall Block
", PHP_MAJOR_VERSION, addcslashes($bootstrapPath, "'"));
				break;

			case 'litespeed':
				$escapedBootstrapPath = addcslashes($bootstrapPath, "'");
				$autoPrependDirective = sprintf("# SiteGuarding Firewall Block
<IfModule LiteSpeed>
php_value auto_prepend_file '%s'
</IfModule>
<IfModule lsapi_module>
php_value auto_prepend_file '%s'
</IfModule>
$userIniHtaccessDirectives
# END SiteGuarding Firewall Block
", $escapedBootstrapPath, $escapedBootstrapPath);
				break;

			case 'apache-suphp':
				$autoPrependDirective = sprintf("# SiteGuarding Firewall Block
$userIniHtaccessDirectives
# END SiteGuarding Firewall Block
", addcslashes($homePath, "'"));
				break;

			case 'cgi':
				if ($userIniHtaccessDirectives) {
					$autoPrependDirective = sprintf("# SiteGuarding Firewall Block
$userIniHtaccessDirectives
# END SiteGuarding Firewall Block
", addcslashes($homePath, "'"));
				}
				break;

		}

		if (!empty($autoPrependDirective)) {
			// Modify .htaccess
			$htaccessContent = @file_get_contents($htaccessPath);

			if ($htaccessContent) {
				$regex = '/# SiteGuarding Firewall Block.*?# END SiteGuarding Firewall Block/is';
				if (preg_match($regex, $htaccessContent, $matches)) {
					$htaccessContent = preg_replace($regex, $autoPrependDirective, $htaccessContent);
				} else {
					$htaccessContent .= "\n\n" . $autoPrependDirective;
				}
			} else {
				$htaccessContent = $autoPrependDirective;
			}

			if (!file_put_contents($htaccessPath, $htaccessContent)) {
				echo 'We were unable to make changes to the .htaccess file. It\'s
				possible server cannot write to the .htaccess file because of file permissions, which may have been
				set by another security plugin, or you may have set them manually. Please verify the permissions allow
				the web server to write to the file, and retry the installation.';
				die;
			}
			if ($serverConfig == 'litespeed') {
				// sleep(2);
				touch($htaccessPath);
			}

		}
		if ($userIni) {
			// .user.ini configuration
			switch ($serverConfig) {
				case 'cgi':
				case 'nginx':
				case 'apache-suphp':
				case 'litespeed':
				case 'iis':
					$autoPrependIni = sprintf("; SiteGuarding Firewall Block
auto_prepend_file = '%s'
; END SiteGuarding Firewall Block
", addcslashes($bootstrapPath, "'"));

					break;
			}

			if (!empty($autoPrependIni)) {

				// Modify .user.ini
				$userIniContent = @file_get_contents($userIniPath);
				if (is_string($userIniContent)) {
					$userIniContent = str_replace('auto_prepend_file', ';auto_prepend_file', $userIniContent);
					$regex = '/; SiteGuarding Firewall Block.*?; END SiteGuarding Firewall Block/is';
					if (preg_match($regex, $userIniContent, $matches)) {
						$userIniContent = preg_replace($regex, $autoPrependIni, $userIniContent);
					} else {
						$userIniContent .= "\n\n" . $autoPrependIni;
					}
				} else {
					$userIniContent = $autoPrependIni;
				}

				if (!file_put_contents($userIniPath, $userIniContent)) {
					echo sprintf('We were unable to make changes to the %1$s file.
					It\'s possible server cannot write to the %1$s file because of file permissions.
					Please verify the permissions are correct and retry the installation.', basename($userIniPath));
					die;
				}
			}
		}
		return true;
		}
	}
		
	
}

?>