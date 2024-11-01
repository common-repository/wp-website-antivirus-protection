<?php
defined('_SITEGUARDING_WAP') or die;

class FUNC_WAP2_dashboard
{
    public static function PageHTML()  
    {
        wp_enqueue_style( 'plgwap2_LoadStyle_UI' );
        wp_enqueue_script( 'plgwap2_LoadJS_UI', '', array(), false, true );
        
        $license_info = $_SESSION['session_plgwap2_license_info'];
        
        $params = FUNC_WAP2_general::Get_SQL_Params(array('firewall_status'));
        $params['firewall_status'] = intval($params['firewall_status']);
        
        if ($license_info['membership'] == 'free') $membership_txt = 'free';
        if ($license_info['membership'] == 'trial') $membership_txt = 'trial';
        if ($license_info['membership'] == 'pro') $membership_txt= 'PRO';
        if (FUNC_WAP2_general::IsPRO_full()) $membership_txt= 'PREMIUM';
        
        if ($license_info['membership'] == 'free') $membership_html = '<div class="ui mini red horizontal label">'.$membership_txt.'</div>';
        if ($license_info['membership'] == 'trial') $membership_html = '<div class="ui mini yellow horizontal label">'.$membership_txt.'</div>';
        if ($license_info['membership'] == 'pro') $membership_html = '<div class="ui mini green horizontal label">'.$membership_txt.'</div>';
        
        
                $pie_data = array('secure' => 6, 'issue' => 0);
                $data = array(
                    array(
                        'active' => 'active',
                        'icon' => 'check green',
                        'title' => 'Antivirus',
                        'description' => 'detect malware codes<br />',
                    ),
                    array(
                        'active' => 'active',
                        'icon' => 'check green',
                        'title' => 'Firewall',
                        'description' => 'realtime protection, block injections',
                    ),
                    array(
                        'active' => 'active',
                        'icon' => 'check green',
                        'title' => 'Login Page',
                        'description' => 'block bruteforce attack',
                    ),
                    array(
                        'active' => 'active',
                        'icon' => 'check green',
                        'title' => 'Monitoring 24/7',
                        'description' => 'Control all changes on the website',
                    ),
                    array(
                        'active' => 'active',
                        'icon' => 'check green',
                        'title' => 'Backup',
                        'description' => 'Full backup of the website',
                    ),
                );
                
                // Antivirus
                $data[0]['description'] .= $membership_html;
                
                // Firewall
                if (FUNC_WAP2_general::CheckFirewall())
                {
                    $data[1]['description'] .= '<div class="ui mini green horizontal label">Premium rules</div>';
                }
                else if ($params['firewall_status'] == 1)
                    {
                        $data[1]['description'] .= '<div class="ui mini yellow horizontal label">basic rules</div>';
                        
                        $pie_data['issue']++;
                        $pie_data['secure']--;
                    }
                    else {
                        $data[1]['icon'] = 'frown outline red';
                        $data[1]['description'] .= '<br /><a href="admin.php?page=plgwap2_firewall_page" class="mini ui button">activate</a>';
                        
                        $pie_data['issue']++;
                        $pie_data['secure']--;
                    }
                
                // Monitoring 24/7
                if (!FUNC_WAP2_general::IsPRO_full())
                {
                    $data[3]['active'] = 'disabled';
                    $data[3]['icon'] = 'lock';
                    
                    $pie_data['issue']++;
                    $pie_data['secure']--;
                }
                
                // Login Page
                if (!FUNC_WAP2_general::CheckBruteforceProtection())
                {
                    $data[2]['icon'] = 'frown outline red';
                    $data[2]['description'] .= '<br /><a href="admin.php?page=plgwap2_settings_page" class="mini ui button">activate</a>';
                    
                    $pie_data['issue']++;
                    $pie_data['secure']--;
                }
                
                // Backup
                if ( !isset($license_info['filemonitoring']['remote_backup_status']) || $license_info['filemonitoring']['remote_backup_status'] == 0)
                {
                    $data[4]['active'] = 'disabled';
                    $data[4]['icon'] = 'lock';
                    
                    $pie_data['issue']++;
                    $pie_data['secure']--;
                }
                
                if ($license_info['last_scan_files_counters']['heuristic'] > 0)
                {
                    $pie_data['issue']++;
                    $pie_data['secure']--;
                }
                
                
        ?>

        <?php
            FUNC_WAP2_general::Wait_CSS_Loader();
        ?>
        
        <div id="main" class="ui main container" style="float: left;margin-top:20px;display:none">
            <h2 class="ui dividing header">SiteGuarding dashboard</h2>
            
            <div class="ui grid">
                <div class="six wide column">
                
                    <?php
                    
                        $print_pie_data = array(
                            array(
                                'txt' => 'Secured',
                                'val' => $pie_data['secure'],
                            ),
                            array(
                                'txt' => 'Issues',
                                'val' => $pie_data['issue'],
                            ),
                        );
                        FUNC_WAP2_general::Print_PIE_chart($print_pie_data);
                    
                    ?>

                
                </div>
                
                <div class="ten wide column">
                    <?php
                    FUNC_WAP2_general::PremiumAdvertBlock();
                    ?>
                </div>
            
            </div>
            
            
            <?php
                FUNC_WAP2_general::PrintSteps($data);
            ?>
            
            
            
            
            <?php if (!FUNC_WAP2_general::IsPRO()) FUNC_WAP2_general::BannerArea(); ?>

            
            <div class="ui grid">
                <div class="ten wide column">
                
                <?php
                    switch ($license_info['membership'])
                    {
                        case 'trial':
                            $msg_data = array(
                                'type' => 'info',
                                'header' => 'You have: Trial version (ver. '._SITEGUARDING_VERSION.')',
                                'content' => 'Available Scans: '.$license_info['scans'].'<br>Valid till: '.$license_info['exp_date'],
                                'button' => array(
                                        'url' => FUNC_WAP2_general::$LINKS['upgrade_to_premium'],
                                        'txt' => 'Upgrade'
                                    )
                            );
                            break;
                            
                        case 'pro':
                            $msg_data = array(
                                'type' => 'ok',
                                'header' => 'You have: Pro version (ver. '._SITEGUARDING_VERSION.')',
                                'content' => 'Available Scans: '.$license_info['scans'].'<br>Valid till: '.$license_info['exp_date'],
                            );
                            break;
                            
                        case 'free':
                            $msg_data = array(
                                'type' => 'alert',
                                'header' => 'You have: Free version (ver. '._SITEGUARDING_VERSION.')',
                                'content' => 'Available Scans: '.$license_info['scans'],
                                'button' => array(
                                        'url' => FUNC_WAP2_general::$LINKS['upgrade_to_premium'],
                                        'txt' => 'Upgrade'
                                    )
                            );
                            break;
                    }
                    FUNC_WAP2_general::Print_MessageBox($msg_data);
                    
                    
                    if (count($license_info['reports']) > 0) 
                    {
                        if ($license_info['last_scan_files_counters']['main'] == 0 && $license_info['last_scan_files_counters']['heuristic'] == 0) 
                        {
                            $msg_data = array(
                                'type' => 'ok',
                                'header' => 'Website is clean',
                                'content' => 'We didn\'t detect any problems with the files on your website.',
                            );
                        }
                        
                        if ($license_info['filemonitoring']['status'] == 0) 
                        {
                            $extra_clean_text = '';
                            $link_clean = FUNC_WAP2_general::$LINKS['clean_website'];
                            $button_text = 'Clean Website';
                        }
                        else {
                            $extra_clean_text = "You have subscription with SiteGuarding.com and can request free cleaning. Please send us a ticket to request the cleaning service.";
                            $link_clean = FUNC_WAP2_general::$LINKS['contact_support'];
                            $button_text = 'Send Request';
                        }
                        
                        if ($license_info['last_scan_files_counters']['main'] > 0)
                        {
                            $msg_data = array(
                                'type' => 'error',
                                'header' => 'Website is infected',
                                'content' => 'We have detected virus / unsafe files on your website.<br>'.$extra_clean_text,
                                'button' => array(
                                        'url' => $link_clean,
                                        'txt' => $button_text
                                    )
                            );
                        }
                        else if ($license_info['last_scan_files_counters']['heuristic'] > 0) 
                        {
                            $msg_data = array(
                                'type' => 'error',
                                'header' => 'Website has unsafe/infected files',
                                'content' => 'We have detected virus / unsafe files on your website.<br>'.$extra_clean_text,
                                'button' => array(
                                        'url' => $link_clean,
                                        'txt' => $button_text
                                    )
                            );
                        }
                    }
                    else {
                        $msg_data = array(
                            'type' => 'info',
                            'header' => 'Website never analyzed before',
                            'content' => 'Please go to Antivirus section and scan your website.',
                            'button' => array(
                                    'url' => 'admin.php?page=plgwap2_antivirus_page',
                                    'txt' => 'Scan Website',
                                    'target' => 0
                                )
                        );
                    }
                    FUNC_WAP2_general::Print_MessageBox($msg_data);
                    
                    
                    
                    
                    if ($license_info['filemonitoring']['status'] == 0)
                    {
                        $msg_data = array(
                            'type' => 'info',
                            'header' => 'Files Change Monitoring',
                            'content' => 'You don\'t have subscription for this service.<br><p class="ui mini">One of the services provided by us is the day-to-day scanning and checking Your website for malware installation and changes in the files.</p><p>If hacker upload any file, remove or inject malware codes into the website\'s files. We will easy detect it and fix the issue.</p>',
                            'button' => array(
                                    'url' => FUNC_WAP2_general::$LINKS['protect_your_website'],
                                    'txt' => 'Subsribe'
                                )
                        );
                    }
                    else {
                        $msg_data = array(
                            'type' => 'ok',
                            'header' => 'Files Change Monitoring',
                            'content' => 'Your subscription is '.$license_info['filemonitoring']['plan'].' ['.$license_info['filemonitoring']['exp_date'].']',
                        );
                    }
                    FUNC_WAP2_general::Print_MessageBox($msg_data); 
    


                    if (FUNC_WAP2_general::CheckFirewall())
                    {
                        $msg_data = array(
                            'type' => 'ok',
                            'header' => 'Professional Website Firewall',
                            'content' => '<p class="mini">Firewall is installed. A website firewall is an appliance, standalone plugin that applies a set of rules to an HTTP conversation. Generally, these rules cover common attacks such as cross-site scripting (XSS), backdoor requests and SQL injection. By customizing the rules to your application, many attacks can be identified and blocked.</p>',
                        );
                
                    }
                    else {
                        if ($params['firewall_status'] == 1)
                        {
                            $msg_data = array(
                                'type' => 'info',
                                'header' => 'Basic Website Firewall',
                                'content' => 'Firewall is activated. <b>Please note:</b> Basic rules are enabled.',
                                'button' => array(
                                        'url' => FUNC_WAP2_general::$LINKS['upgrade_to_premium'],
                                        'txt' => 'Upgrade'
                                    )
                            );
                        }
                        else {
                            $msg_data = array(
                                'type' => 'info',
                                'header' => 'Professional Website Firewall',
                                'content' => 'Firewall is not installed or not activated. We don\'t filter the traffic of your website.<p class="mini">A website firewall is an appliance, standalone plugin that applies a set of rules to an HTTP conversation. Generally, these rules cover common attacks such as cross-site scripting (XSS), backdoor requests and SQL injection. By customizing the rules to your application, many attacks can be identified and blocked.</p>',
                                'button' => array(
                                        'url' => 'admin.php?page=plgwap2_firewall_page',
                                        'txt' => 'Activate'
                                    )
                            );
                        }
                
                    }
                    FUNC_WAP2_general::Print_MessageBox($msg_data);
    



                    if (!FUNC_WAP2_general::CheckBruteforceProtection())
                    {
                        $msg_data = array(
                            'type' => 'info',
                            'header' => 'Bruteforce Protection',
                            'content' => '<p>You don\'t have bruteforce protection on login page.<br>Brute-force attack is the most common attack, used against Web applications. The purpose of this attack is to gain access to user\'s accounts by repeated attempts to guess the password of the user or group of users.</p>',
                            'button' => array(
                                    'url' => 'admin.php?page=plgwap2_settings_page',
                                    'txt' => 'Activate'
                                )
                        );
                    }
                    else {
                        $msg_data = array(
                            'type' => 'ok',
                            'header' => 'Bruteforce Protection',
                            'content' => 'Protection is active. Login page is protected.',
                            'button' => array(
                                    'url' => FUNC_WAP2_general::$LINKS['learn_bruteforce'],
                                    'txt' => 'Learn More'
                                )
                        );
                    }
                    FUNC_WAP2_general::Print_MessageBox($msg_data);
    
   



                    if ( !isset($license_info['filemonitoring']['remote_backup_status']) || $license_info['filemonitoring']['remote_backup_status'] == 0)
                    {
                        $msg_data = array(
                            'type' => 'info',
                            'header' => 'Website Backup service',
                            'content' => 'You don\'t have subscription for this service.<br><p class="mini">It is extremely important to have your website backed up regularly. The website backup means that you can have a similar copy of your content and data with you. You can keep it safe. Whatever happens to your website, the data will be available to you, and you can use it later.</p>',
                            'button' => array(
                                    'url' => FUNC_WAP2_general::$LINKS['backup_service'],
                                    'txt' => 'Learn More'
                                )
                        );
                    }
                    else {
                        $msg_data = array(
                            'type' => 'ok',
                            'header' => 'Website Backup service',
                            'content' => 'Backup service is activated.',
                        );
                    }
                    FUNC_WAP2_general::Print_MessageBox($msg_data);
    
    


    
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
                            'button' => array(
                                    'url' => $install_url,
                                    'txt' => 'Install'
                                )
                        );
                    }
                    else {
                        if (isset($license_info['extensions']['wp-geo-website-protection'])) $geo_ext_txt = '<br><p class="mini">Please use license key <b>'.$license_info['extensions']['wp-geo-website-protection'].'</b> to active PRO version.</p>';
                        else $geo_ext_txt = '';
                        
                        $msg_data = array(
                            'type' => 'ok',
                            'header' => 'GEO Website Protection',
                            'content' => 'Security plugin GEO Website Protection is installed on your website.'.$geo_ext_txt,
                            'button' => array(
                                    'url' => 'admin.php?page=plgsggeo_protection',
                                    'txt' => 'Configure'
                                )
                        );
                    }
                    FUNC_WAP2_general::Print_MessageBox($msg_data);

    
                    echo '<p class="mini">Status Timestamp: '.date("Y-m-d H:i:s", $license_info['cache_license_info_time']).'</p>';
                
                
                ?>
                
                </div>



                <div class="six wide column">
                
                    <div class="ui raised segment">
                    
                        <h3 class="ui dividing header">Status</h3>
                        
                        <?php
                        if (isset($_SESSION['session_plgwap2_license_info']['membership']))
                        {
                        ?>
                        
                        <div class="content"><b>Version:</b> <?php echo _SITEGUARDING_VERSION; ?> <?php echo $membership_html; ?></div>
                        <div class="content"><b>Valid till:</b> <?php echo $license_info['exp_date']; ?></div>
                        <div class="content"><b>Available scans:</b> <?php echo $license_info['scans']; ?></div>
                        
                      <p>To get the latest status of your license, click <b>Refresh</b> button</p>

                            <script>
                            function ShowLoader_Refresh()
                            {
                                jQuery(".ajax_button_refresh").hide();
                                jQuery(".scanner_ajax_loader_refresh").show(); 
                                
                                jQuery.post(
                                    ajaxurl, 
                                    {
                                        'action': 'plgwap2_ajax_avp_refresh'
                                    }, 
                                    function(response){
                                        document.location.href = 'admin.php?page=plgwap2_Antivirus';
                                    }
                                );  
                            }
                            </script>
                            <p class="sg_center">
                            <a href="javascript:;" class="ajax_button_refresh positive medium ui button" onclick="ShowLoader_Refresh();">Refresh</a>
                            
                            <a href="javascript:;" class="scanner_ajax_loader_refresh medium ui button" style="display: none;">
                                <img width="32" height="32" src="<?php echo plugins_url('images/ajax_loader.svg', dirname(__FILE__)); ?>" />
                            </a>
                            
                            <a class="medium positive ui button" href="<?php echo FUNC_WAP2_general::$LINKS['upgrade_to_premium']; ?>" target="_blank">Upgrade</a>
                            <?php
                            if ( isset($license_info['last_scan_files_counters']['heuristic']) && $license_info['last_scan_files_counters']['heuristic'] > 0 )
                            {
                                ?>
                                <a class="medium negative ui button" href="<?php echo FUNC_WAP2_general::$LINKS['clean_website']; ?>" target="_blank">Clean Website</a>
                                <?php
                            }
                            ?>
                            </p>
                            <?php
                            if (FUNC_WAP2_general::IsPRO()) {
                            ?>
                                <p><i class="user md green icon"></i>Premium customers can request free cleaning. Please contact <a href="<?php echo FUNC_WAP2_general::$LINKS['contact_support']; ?>" target="_blank">SiteGuarding.com support</a></p>
                            <?php
                            }
                            ?>

                      
                      </p>
                        <?php
                        }
                        ?>
                    </div>
                    
                    
                
                    <div class="ui raised segment">
                    
                        <h3 class="ui dividing header">Latest Antivirus Reports</h3>
                        
                        <?php
                        if (isset($_SESSION['session_plgwap2_license_info']['reports']) && count($_SESSION['session_plgwap2_license_info']['reports']) > 0)
                        {
                        ?>
                            <div class="ui list">
                        <?php
                                foreach ($_SESSION['session_plgwap2_license_info']['reports'] as $row)
                                {
                                    ?>
                                      <div class="item">
                                        <i class="file alternate outline icon"></i>
                                        <div class="content">
                                          <a href="<?php echo $row['report_link']; ?>" target="_blank" class="header">Antivirus report <?php echo $row['date']; ?></a>
                                        </div>
                                      </div>
                                    
                                    <?php
                                }
                                ?>
                                    <p class="sg_center">
                                        <br><b>Need help from professional web security expert?</b><br><br>
                                        <a href="<?php echo FUNC_WAP2_general::$LINKS['clean_website']; ?>" class="medium negative ui button" target="_blank" href="<?php echo FUNC_WAP2_general::$LINKS['clean_website']; ?>">Clean My Website</a>
                                    </p>
                                    <?php
                                    if (FUNC_WAP2_general::IsPRO()) {
                                    ?>
                                        <p><i class="user md green icon"></i>Premium customers can request free cleaning. Please contact <a href="<?php echo FUNC_WAP2_general::$LINKS['contact_support']; ?>" target="_blank">SiteGuarding.com support</a></p>
                                    <?php
                                    }
                            ?>

                            </div>
                        <?php
                        }
                        else echo '<i class="frown outline icon red"></i>You don\'t have any reports yet. Go to <a href="admin.php?page=plgwap2_antivirus_page">Antivirus scanner</a> section to check your website.';
                        ?>
                      
                    </div>
                    



                    <div class="ui raised segment">
                    
                        <h3 class="ui dividing header">Login Attempts</h3>
                        
                        <?php
                        $logs = FUNC_WAP2_general::GetLastLogs_from_file(_SITEGUARDING_WAP_LOGFILE_ACCESS, 700);
                        $logs = FUNC_WAP2_general::PrepareLogContent($logs, "|");
                        
                        $geo = new FUNC_WAP2_Geo_IP2Country;
                        $country_code = $geo->getCoutryByIP($_SERVER['REMOTE_ADDR']);
                        ?>
                        <p>Your current IP: <?php echo $_SERVER['REMOTE_ADDR']; ?> (<?php echo $geo->getNameByCountryCode($country_code); ?>)</p>
                        
                        <?php
                        if (count($logs))
                        {
                            ?>
                                <table class="ui celled table">
                                  <thead>
                                    <tr>
                                    <th>Username</th>
                                    <th>IP / Country</th>
                                    <th>Date</th>
                                  </tr>
                                  </thead>
                                  <tbody>
                            
                            <?php
                            foreach ($logs as $row)
                            {
                                ?>
                                    <tr>
                                      <td><?php echo $row[2]; ?></td>
                                      <td><?php echo $row[1]; ?><br><?php echo $geo->getNameByCountryCode($row[3]); ?></td>
                                      <td><?php echo $row[0]; ?></td>
                                    </tr>
                                
                                <?php
                            }
                            ?>
                                  </tbody>
                                </table>
                            <?php
                        }
                        
                        ?>
                    </div>



                </div>
            
            </div>
            
            
            
            
            <?php FUNC_WAP2_general::QuickLinks(); ?>
            


            <h3 class="ui dividing header">WordPress Status</h3>
            
            <?php
            $update_check = new FUNC_SG_CheckForUpdates();
            $needs_update = $update_check->checkAllUpdates()->needsAnyUpdates();
            
			$result = array(
				'core'    => $update_check->getCoreUpdateVersion(),
				'plugins' => $update_check->getPluginUpdates(),
				'themes'  => $update_check->getThemeUpdates(),
			);
            
            $allusers_info = count_users();
            $total_admin_users = $allusers_info['avail_roles']['administrator'];
            
            // Get all admin users
            $args = array(
            	'role'         => 'administrator',
            	'fields'       => 'all'
             ); 
            $wp_admin_users = get_users( $args );
            
            
            ?>
            
            <div class="ui top attached tabular menu" id="tabs_1" style="overflow-x: unset;">
              <a class="item active" data-tab="tab1">Administrator accounts</a>
              <a class="item" data-tab="tab2"><?php if (!$result['core']) echo '<i class="check icon green"></i>'; ?>WordPress Core <?php if ($result['core']) FUNC_WAP2_general::Print_LabelBox(array('type' => 'error', 'content' => '!')); ?></a>
              <a class="item" data-tab="tab3"><?php if (!$result['plugins']) echo '<i class="check icon green"></i>'; ?>Plugins <?php if ($result['plugins']) FUNC_WAP2_general::Print_LabelBox(array('type' => 'error', 'content' => count($result['plugins']))); ?></a>
              <a class="item" data-tab="tab4"><?php if (!$result['themes']) echo '<i class="check icon green"></i>'; ?>Themes <?php if ($result['themes']) FUNC_WAP2_general::Print_LabelBox(array('type' => 'error', 'content' => count($result['themes']))); ?></a>
            </div>
            <div class="ui bottom attached tab segment active" data-tab="tab1">

                    <h3 class="ui dividing header">Exist Administrator acounts</h3>
                    <?php
                        $msg_data = array(
                            'type' => 'warning',
                            'content' => 'We have detected '.$total_admin_users.' accounts with administrator level (<a href="users.php?role=administrator" target="_blank">View site admins</a>). If you see any fake accounts or old accounts (freelancers, developers, etc) remove them or change the password. It\'s the most simple and common way to hack your website. <a href="https://www.siteguarding.com/en/old-or-fake-administrator-accounts" target="_blank">Learn more</a>'
                        );
                        FUNC_WAP2_general::Print_MessageBox($msg_data);
                    ?>
                    
                    <table class="ui celled table">
                      <thead>
                        <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                      </tr>
                      </thead>
                      <tbody>
                
                <?php
                foreach ($wp_admin_users as $row)
                {
                    ?>
                        <tr>
                          <td><?php echo $row->ID; ?></td>
                          <td><?php echo $row->data->user_login; ?></td>
                          <td><?php echo $row->data->user_email; ?></td>
                          <td>Administrator</td>
                        </tr>
                    
                    <?php
                }
                ?>
                      </tbody>
                    </table>

            </div>
            <div class="ui bottom attached tab segment" data-tab="tab2">
                <?php
                if ($result['core'])
                {
                    $msg_data = array(
                        'type' => 'error',
                        'content' => 'You have old version of Wordpress. WordPress update found. New version: ' . $result['core']
                    );
                    FUNC_WAP2_general::Print_MessageBox($msg_data);
                }
                else {
                    $msg_data = array(
                        'type' => 'ok',
                        'content' => 'You have the latest version of WordPress.'
                    );
                    FUNC_WAP2_general::Print_MessageBox($msg_data);
                }
                
                ?>
            </div>
            <div class="ui bottom attached tab segment" data-tab="tab3">
                <?php
                if ($result['plugins'])
                {
                    foreach ($result['plugins'] as $props)
                    {
                        $msg_data = array(
                            'type' => 'error',
                            'content' => 'You have old version of plugin "<b>' . $props['slug'] . '</b>" You have version: <b>' . $props['Version'] . '</b>. New version: <b>' . $props['newVersion'].'</b>'
                        );
                        FUNC_WAP2_general::Print_MessageBox($msg_data);
                    }
                }
                else {
                    $msg_data = array(
                        'type' => 'ok',
                        'content' => 'All plugins have the latest version.'
                    );
                    FUNC_WAP2_general::Print_MessageBox($msg_data);
                }
                
                ?>
            </div>
            <div class="ui bottom attached tab segment" data-tab="tab4">
                <?php
                if ($result['themes'])
                {
                    foreach ($result['themes'] as $props)
                    {
                        $msg_data = array(
                            'type' => 'error',
                            'content' => 'You have old version of theme "<b>' . $props['name'] . '</b>" You have version: <b>' . $props['version'] . '</b>. New version: <b>' . $props['newVersion'].'</b>'
                        );
                        FUNC_WAP2_general::Print_MessageBox($msg_data);
                    }
                }
                else {
                    $msg_data = array(
                        'type' => 'ok',
                        'content' => 'All themes have the latest version.'
                    );
                    FUNC_WAP2_general::Print_MessageBox($msg_data);
                }
                
                ?>
            </div>
            <script>
            jQuery(document).ready(function(){
                jQuery('#tabs_1 .item').tab();
            });
            </script>
            
            
            <?php FUNC_WAP2_general::Print_HelpBlock(); ?>

                    
                    
        </div>
        <?php

    } 
}

?>