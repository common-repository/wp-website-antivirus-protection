<?php
defined('_SITEGUARDING_WAP') or die;

class FUNC_WAP2_antivirus
{
    public static function PageHTML()  
    {
        ini_set('max_execution_time',7200);
        set_time_limit ( 7200 );

        wp_enqueue_style( 'plgwap2_LoadStyle_UI' );
        wp_enqueue_script( 'plgwap2_LoadJS_UI', '', array(), false, true );
        
        $flag_show_message_REVIEW_REPORT = false;
        $flag_show_message_SCAN_RETURNED_ERROR = false;
        $flag_show_message_REPORT_TAKES_LONGTIME = false;
        if (isset($_GET['license_update']))
        {
            if ($_GET['license_update'] == 1)
            {
                FUNC_WAP2_general::UpdateLicenseInfo();
                $flag_show_message_REVIEW_REPORT = true;
            }
            
            if ($_GET['license_update'] == 2)
            {
                FUNC_WAP2_general::UpdateLicenseInfo();
                $flag_show_message_SCAN_RETURNED_ERROR = true;
            }
            
            if ($_GET['license_update'] == 3)
            {
                FUNC_WAP2_general::UpdateLicenseInfo();
                $flag_show_message_REPORT_TAKES_LONGTIME = true;
            }
        }
        
        $license_info = $_SESSION['session_plgwap2_license_info'];
        
        $params = FUNC_WAP2_general::Get_SQL_Params(array('sql_results', 'sql_latest_scan_date'));
        
        if ($license_info['membership'] == 'free') $membership_txt = 'free';
        if ($license_info['membership'] == 'trial') $membership_txt = 'trial';
        if ($license_info['membership'] == 'pro') $membership_txt= 'PRO';
        if (FUNC_WAP2_general::IsPRO_full()) $membership_txt= 'PREMIUM';

        ?>
        
        <?php
            FUNC_WAP2_general::Wait_CSS_Loader();
        ?>

        <div id="main" class="ui main container" style="float: left;margin-top:20px;display:none">
            <h2 class="ui dividing header">Antivirus scanner</h2>
            
            <?php 
            if (ini_get('max_execution_time') < 240)
            {
                $msg_data = array(
                    'type' => 'warning',
                    'content' => 'Your PHP has limits for <b>max_execution_time</b> To give you the best results and stable work of the scanner, please contact with your hosting company support and ask them to set <b>max_execution_time = 600</b>'
                );
                FUNC_WAP2_general::Print_MessageBox($msg_data);
            }
            
            
            if ($flag_show_message_REVIEW_REPORT && isset($_SESSION['session_plgwap2_license_info']['reports']) && count($_SESSION['session_plgwap2_license_info']['reports']) > 0)
            {
                $msg_data = array(
                    'type' => 'warning',
                    'content' => 'Please review your latest scan report <a href="'.$_SESSION['session_plgwap2_license_info']['reports'][0]['report_link'].'" target="_blank">'.$_SESSION['session_plgwap2_license_info']['reports'][0]['date'].'</a>'
                );
                FUNC_WAP2_general::Print_MessageBox($msg_data);
            }
            
            if ($flag_show_message_REPORT_TAKES_LONGTIME || $flag_show_message_SCAN_RETURNED_ERROR)
            {
                $msg_data = array(
                    'type' => 'warning',
                    'content' => 'Looks like it takes more time than expected. Please see your report in <b>Latest Antivirus Reports</b> section. Use <a href="javascript:;" onclick="ShowLoader_Refresh();"><b>Refresh</b></a> button to get the latest scan results'
                );
                FUNC_WAP2_general::Print_MessageBox($msg_data);
            }
            ?>
            
            <div class="ui grid">
                <div class="six wide column center aligned">
                    <?php
                    if (count($license_info['reports']) == 0)
                    {
                        echo '<i class="massive meh outline icon yellow"></i>';
                        echo '<p>Never scanned before</p>';
                    }
                    else if ($license_info['last_scan_files_counters']['heuristic'] == 0)
                        {
                            echo '<i class="huge check icon green"></i>';
                            echo '<p class="green">Website is clean</p>';
                            echo '<p class="green">Last scan: '.$license_info['reports'][0]['date'].'</p>';
                        }
                        else {
                            echo '<i class="huge exclamation triangle icon red"></i>';
                            echo '<p class="green">Website is infected</p>';
                            echo '<p class="green">Last scan: '.$license_info['reports'][0]['date'].'</p>';
                        }
                    ?>
                </div>
                
                <div class="ten wide column">
                    <?php
                    FUNC_WAP2_general::PremiumAdvertBlock();
                    ?>
                </div>
            
            </div>
            
            
            
            <?php
                $data = array(
                    array(
                        'active' => 'active',
                        'icon' => 'check green',
                        'title' => 'Antivirus scanner',
                        'description' => 'detect malware codes on your website<br />',
                    ),
                    array(
                        'active' => 'active',
                        'icon' => 'check green',
                        'title' => 'File Analysis',
                        'description' => 'manual analyze of detected file',
                    ),
                    array(
                        'active' => 'active',
                        'icon' => 'check green',
                        'title' => 'Website cleaning',
                        'description' => 'if any issue detected, we will clean your website',
                    ),
                );
                
                if ($license_info['membership'] == 'free') $membership_html = '<div class="ui mini red horizontal label">'.$membership_txt.'</div>';
                if ($license_info['membership'] == 'trial') $membership_html = '<div class="ui mini yellow horizontal label">'.$membership_txt.'</div>';
                if ($license_info['membership'] == 'pro') $membership_html = '<div class="ui mini green horizontal label">'.$membership_txt.'</div>';
                
                $data[0]['description'] .= $membership_html;
                
                if (!FUNC_WAP2_general::IsPRO())
                {
                    $data[1]['active'] = 'disabled';
                    $data[1]['icon'] = 'lock';
                }
                
                if (!FUNC_WAP2_general::IsPRO_full())
                {
                    $data[2]['active'] = 'disabled';
                    $data[2]['icon'] = 'lock';
                }

                FUNC_WAP2_general::PrintSteps($data);
            ?>
            
            
            <?php if (!FUNC_WAP2_general::IsPRO()) FUNC_WAP2_general::BannerArea(); ?>

            
            <div class="ui grid">
                <div class="ten wide column">
                
                    <div class="ui blue message">To start the scan process click "Start Scanner" button.</div>
                    
                    <p>Scanner will automatically collect and analyze the files of your website. The scanning process can take up to 10 minutes (it depends of speed of your server and amount of the files to analyze). The copy of the report we will send by email for your records.</p>
                    
                    <?php
                    $show_scan_bttn = true;
                    $lock_file = SG_SITE_ROOT.'webanalyze'.DIRSEP.'antivirus_scan.lock';
                    if (file_exists($lock_file) && (time() - filectime($lock_file)) < 5 * 60 ) $show_scan_bttn = false;
                    
                    if ($show_scan_bttn) 
                    {
                        $style_bttn = '';
                        $style_loader = 'style="display: none;"';
                        FUNC_WAP2_antivirus::CreateReportSessionKey();
                    }
                    else {
                        $style_bttn = 'style="display: none;"';
                        $style_loader = '';
                        FUNC_WAP2_general::UpdateLicenseInfo();
                    }
                    
                    ?>
                    <script>
                    function ShowLoader_AVP()
                    {
                        jQuery("#ajax_button_AVP").hide();
                        jQuery("#scanner_ajax_loader_AVP").show(); 
                        jQuery("#scanner_ajax_reportbox").show(); 
                        
                        setTimeout(function(){ document.location.href = 'admin.php?page=plgwap2_antivirus_page&license_update=3'; }, 420000);   // 7 mins
                        
                        jQuery.post(
                            ajaxurl, 
                            {
                                'action': 'plgwap2_ajax_scan_avp'
                            }, 
                            function(response){
                                document.location.href = 'admin.php?page=plgwap2_antivirus_page&license_update=1';
                            }
                        ).fail(function() {
                            document.location.href = 'admin.php?page=plgwap2_antivirus_page&license_update=2';
                          });  
                    }
                    
                    <?php
                    if (!$show_scan_bttn) {
                    ?>
                        jQuery(document).ready(function()
                        {
                            var countDownDate = new Date(new Date().getTime() + 5*60000).getTime();
                            
                            var x = setInterval(function() 
                            {
                            
                              var now = new Date().getTime();
                            
                              var distance = countDownDate - now;
                            
                              var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                              var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                              var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                              var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                            
                              document.getElementById("countdown").innerHTML = "<br />Scanning is in progress: "+minutes + "m " + seconds + "s ";
                            
                              if (distance < 0) 
                              {
                                clearInterval(x);
                                document.location.href = 'admin.php?page=plgwap2_antivirus_page&license_update=3';
                              }
                            }, 1000);
                        });
                    <?php
                    }
                    ?>
                    </script>
                    <p class="sg_center">
                        <a id="ajax_button_AVP" class="massive positive ui button" href="javascript:;" <?php echo $style_bttn; ?> onclick="ShowLoader_AVP()">Start Scanner</a>
                        <img id="scanner_ajax_loader_AVP" width="48" height="48" <?php echo $style_loader; ?> src="<?php echo plugins_url('images/ajax_loader.svg', dirname(__FILE__)); ?>" />
                        <span id="countdown" class="c_red" style="font-weight: bold; font-size: 120%;"></span>
                    </p>
                    <?php
                    $report_link = 'https://www.siteguarding.com/antivirus/viewreport?report_id='.FUNC_WAP2_antivirus::GetReportSessionKey().'&refresh=1';
                    ?>
                    <div id="scanner_ajax_reportbox" class="ui small blue message" <?php echo $style_loader; ?>>Please note full scan process can take upto 5-10 minutes. If it takes too long please contact SiteGuarding.com support or use this link to see the current status.<br /><br /><b><a href="<?php echo $report_link; ?>" target="_blank"><?php echo $report_link; ?></a></b></div>
                    
                    <p class="sg_center">Scanner will check all the files for this website and all the folders.<br><b><span class="c_red">Please note:</span></b> <span class="c_red">Folders with other sites will be excluded</span></p>
                    
                    <p>&nbsp;</p>
                    
                    <script>
                    function ShowLoader_SQL()
                    {
                        jQuery("#check_SQL_block").hide();
                        jQuery("#check_SQL_block_loader").show(); 
                        
                        jQuery.post(
                            ajaxurl, 
                            {
                                'action': 'plgwap2_ajax_scan_sql'
                            }, 
                            function(response){
                                document.location.href = 'admin.php?page=plgwap2_antivirus_page';
                            }
                        );  
                    }
                    </script>
                    <div id="check_SQL_block">
                        <p class="sg_center"><a class="massive positive ui button" onclick="ShowLoader_SQL()">Check Database</a></p>
                        <p class="sg_center">Scanner will check all posts and pages in database to detect unwanted links, iframes, javascript codes.</p>
                    </div>
                    <div id="check_SQL_block_loader" style="display: none;">
                        <p class="sg_center"><img width="48" height="48" src="<?php echo plugins_url('images/ajax_loader.svg', dirname(__FILE__)); ?>" /></p>
                        <p class="sg_center">Please wait, it will take approximately 30 seconds.</p>
                    </div>
                
                    <div class="ui info icon message">
                        <i class="info letter icon"></i>
                        <div class="content">
                          <div class="header">Free Professional Consultation</div>
                          We will be more than happy to provide free professional consultation with detailed explanations of all the issues on your website.
                        </div>
                    </div>

                    

                    <div class="ui raised segment">
                    
                        <h3 class="ui dividing header">Detected Files</h3>
                        
                        <?php
                            if (count($license_info['reports']) > 0) echo '<p>Latest scan was '.$license_info['reports'][0]['date'].'</p>';
                        ?>
                        
                        <?php
                        if ( isset($license_info['last_scan_files']['heuristic']) && $license_info['last_scan_files']['heuristic'] > 0)
                        {
                        ?>
                        
                            <table class="ui celled table">
                              <thead>
                                <tr>
                                <th>File</th>
                              </tr>
                              </thead>
                              <tbody>
                                <?php
                                foreach ($license_info['last_scan_files']['heuristic'] as $row) 
                                {
                                    $view_file_link = wp_nonce_url(admin_url('admin.php?page=plgwap2_antivirus_page&action=view_file&file='.$row), 'viewfile', '4AA006F01C86');
                                ?>
                                    <tr>
                                        <td><?php echo $row; ?> <a target="_blank" href="<?php echo $view_file_link; ?>" target="_blank">
                                        <?php
                                        if ($row[0] != '*') {
                                            ?>
                                            
                                            <?php
                                        }
                                        ?>
                                        
                                      <i class="ui file outline icon"></i></a></td>
                                    </tr>
                                <?php
                                }
                                ?>
    
                              </tbody>
                            </table>
                        
                        <p class="c_red">Heuristic algorithm has the capability of detecting malware that was previously unknown. It doesn't give 100% guarantee that the file is the virus and requires manual review. If these files are not a part of plugins, extentions or website, delete or block them. <b>If you are not sure, you always can contact our support and we will analyze the files.</b> 95% these files have malicious code/scripts or contain code elements and commands those have been used in different malicious scripts. Review is required.</p>
                        
                        <?php
                        }
                        else {
                            if (count($license_info['reports']) == 0) echo '<p>Please scan your website.</p>';
                            else echo '<p>Problems with the files are not detected.</p>';
                        }
                        ?>
                        
                    </div>

                    
                    
                    <?php
                        if (isset($params['sql_latest_scan_date']))
                        {
                            ?>
                            <div class="ui raised segment">
                            <h3 class="ui dividing header">Dababase (SQL) scan report</h3>
                            <?php
                            
                                    // Show report
                                    echo '<p>Latest scan was '.$params['sql_latest_scan_date'].'</p>';
                                    
                                    $params['results'] = (array)json_decode($params['sql_results'], true);
                                    unset($params['sql_results']);
                    
                                    
                                    if (isset($_GET['showdetailed']) && intval($_GET['showdetailed']) == 0)
                                    {
                                        /**
                                         * Show simple
                                         */
                                        $results = WAP2_AVP_SEO_SG_Protection::PrepareResults($params['results']);
                    
                                        echo '<h3>Bad words (<a href="admin.php?page=plgwap2_antivirus_page&showdetailed=1">show details</a>)</h3>';
                                        if (count($results['WORDS']))
                                        {
                                            echo '<table class="ui selectable celled table small">';
                                            echo '<thead><tr><th><i class="exclamation triangle icon red"></i>Detected Words</th></thead>';
                                            foreach ($results['WORDS'] as $word)
                                            {
                                                echo '<tr>';
                                                echo '<td class="red">'.$word.'</td>';
                                                echo '</tr>';
                                            }
                                            echo '</table>';
                                        }
                                        else echo '<p>No bad words detected.</p>';
                                        
                                        echo "<hr>";
                                        
                                        echo '<h3>Detected links (<a href="admin.php?page=plgwap2_antivirus_page&showdetailed=1">show details</a>)</h3>';
                                        if (count($results['A']))
                                        {
                                            echo '<table class="ui selectable celled table small">';
                                            echo '<thead><tr><th>Links</th><th>Text in links</th></tr></thead>';
                                            foreach ($results['A'] as $link => $txt)
                                            {
                                                echo '<tr>';
                                                echo '<td>'.$link.'</td><td>'.$txt.'</td>';
                                                echo '</tr>';
                                            }
                                            echo '</table>';
                                        }
                                        else echo '<p>No strange links detected.</p>';
                                        
                                        echo "<hr>";
                                        
                                        echo '<h3>Detected iframes (<a href="admin.php?page=plgwap2_antivirus_page&showdetailed=1">show details</a>)</h3>';
                                        if (count($results['IFRAME']))
                                        {
                                            echo '<table class="ui selectable celled table small">';
                                            echo '<thead><tr><th>Links</th></thead>';
                                            foreach ($results['IFRAME'] as $link)
                                            {
                                                echo '<tr>';
                                                echo '<td>'.$link.'</td>';
                                                echo '</tr>';
                                            }
                                            echo '</table>';
                                        }
                                        else echo '<p>No iframes detected.</p>';
                                        
                                        echo "<hr>";
                                        
                                        echo '<h3>Detected JavaScripts (<a href="admin.php?page=plgwap2_antivirus_page&showdetailed=1">show details</a>)</h3>';
                                        if (count($results['SCRIPT']))
                                        {
                                            echo '<table class="ui selectable celled table small">';
                                            echo '<thead><tr><th>JavaScripts Link or codes</th></thead>';
                                            foreach ($results['SCRIPT'] as $link)
                                            {
                                                echo '<tr>';
                                                echo '<td>'.$link.'</td>';
                                                echo '</tr>';
                                            }
                                            echo '</table>';
                                        }
                                        else echo '<p>No iframes detected.</p>';
                                    }
                                    else {
                                        /**
                                         * Show detailed
                                         */
                                        $post_ids = array();
                                        $post_titles = array();
                                        if (count($params['results']['posts']['WORDS']))
                                        {
                                            foreach ($params['results']['posts']['WORDS'] as $post_id => $post_arr)
                                            {
                                                $post_ids[ $post_id ] = $post_id;
                                            }
                                        }
                                        if (count($params['results']['posts']['A']))
                                        {
                                            foreach ($params['results']['posts']['A'] as $post_id => $post_arr)
                                            {
                                                $post_ids[ $post_id ] = $post_id;
                                            }
                                        }
                                        if (count($params['results']['posts']['IFRAME']))
                                        {
                                            foreach ($params['results']['posts']['IFRAME'] as $post_id => $post_arr)
                                            {
                                                $post_ids[ $post_id ] = $post_id;
                                            }
                                        }
                                        if (count($params['results']['posts']['SCRIPT']))
                                        {
                                            foreach ($params['results']['posts']['SCRIPT'] as $post_id => $post_arr)
                                            {
                                                $post_ids[ $post_id ] = $post_id;
                                            }
                                        }
                                        $post_titles = WAP2_AVP_SEO_SG_Protection::GetPostTitles_by_IDs($post_ids);
                                        
                                        echo '<h3>Detailed by post (<a href="admin.php?page=plgwap2_antivirus_page&showdetailed=0">show simple</a>)</h3>'; 
                                        if (count($params['results']['posts']['WORDS']))
                                        {
                                            foreach ($params['results']['posts']['WORDS'] as $post_id => $post_arr)
                                            {
                                                if (count($post_arr))
                                                {
                                                    $edit_link = 'post.php?post='.$post_id.'&action=edit';
                                                    echo '<table class="ui selectable celled table small">';
                                                    echo '<thead><tr><th><b>Bad words in post ID: '.$post_id.'</b> ('.$post_titles[$post_id]/*SEO_SG_Protection::GetPostTitle_by_ID($post_id)*/.') <a href="'.$edit_link.'" target="_blank" class="edit_post"><i class="write icon"></i> edit</a></th></tr></thead>';
                                                    foreach ($post_arr as $word)
                                                    {
                                                        echo '<tr>';
                                                        echo '<td>'.$word.'</td>';
                                                        echo '</tr>';
                                                    }
                                                    echo '</table>';
                                                }
                                            }
                                        }
                                        if (count($params['results']['posts']['A']))
                                        {
                                            foreach ($params['results']['posts']['A'] as $post_id => $post_arr)
                                            {
                                                if (count($post_arr))
                                                {
                                                    $edit_link = 'post.php?post='.$post_id.'&action=edit';
                                                    echo '<table class="ui selectable celled table small">';
                                                    echo '<thead><tr><th class="ten wide"><b>Links in post ID: '.$post_id.'</b> ('.$post_titles[$post_id]/*SEO_SG_Protection::GetPostTitle_by_ID($post_id)*/.') <a href="'.$edit_link.'" target="_blank" class="edit_post"><i class="write icon"></i> edit</a></th><th class="six wide">Text in links</th></tr></thead>';
                                                    foreach ($post_arr as $link_data)
                                                    {
                                                        foreach ($link_data as $link => $txt)
                                                        {
                                                            echo '<tr>';
                                                            echo '<td>'.$link.'</td><td>'.$txt.'</td>';
                                                            echo '</tr>';
                                                        }
                                                    }
                                                    echo '</table>';
                                                }
                                            }
                                        }
                                        //else echo '<p>No strange links detected.</p>';
                    //print_r($params['results']['posts']['IFRAME']);
                                        if (count($params['results']['posts']['IFRAME']))
                                        {
                                            foreach ($params['results']['posts']['IFRAME'] as $post_id => $post_arr)
                                            {
                                                if (count($post_arr))
                                                {
                                                    $edit_link = 'post.php?post='.$post_id.'&action=edit';
                                                    echo '<table class="ui selectable celled table small">';
                                                    echo '<thead><tr><th><b>Iframes in post ID: '.$post_id.'</b> ('.$post_titles[$post_id]/*SEO_SG_Protection::GetPostTitle_by_ID($post_id)*/.') <a href="'.$edit_link.'" target="_blank" class="edit_post"><i class="write icon"></i> edit</a></th></tr></thead>';
                                                    foreach ($post_arr as $link)
                                                    {
                                                        echo '<tr>';
                                                        echo '<td>'.$link.'</td>';
                                                        echo '</tr>';
                                                    }
                                                    echo '</table>';
                                                }
                                            }
                                        }
                                        //else echo '<p>No strange links detected.</p>';
                    //print_r($params['results']['posts']['SCRIPT']);exit;
                                        if (count($params['results']['posts']['SCRIPT']))
                                        {
                                            foreach ($params['results']['posts']['SCRIPT'] as $post_id => $post_arr)
                                            {
                                                if (count($post_arr))
                                                {
                                                    $edit_link = 'post.php?post='.$post_id.'&action=edit';
                                                    echo '<table class="ui selectable celled table small">';
                                                    echo '<thead><tr><th><b>JavaScript in post ID: '.$post_id.'</b> ('.$post_titles[$post_id].') <a href="'.$edit_link.'" target="_blank" class="edit_post"><i class="write icon"></i> edit</a></th></tr></thead>';
                                                    foreach ($post_arr as $js_link => $js_code)
                                                    {
                                                        if ($js_code == '') $js_code = $js_link;
                                                        echo '<tr>';
                                                        echo '<td>'.$js_code.'</td>';
                                                        echo '</tr>';
                                                    }
                                                    echo '</table>';
                                                }
                                            }
                                        }
                                        //else echo '<p>No strange links detected.</p>';
                                    }
                        
                        ?>
                        </div>
                        
                        <?php            
                        }
                    
                    
                    
                    ?>

      
                </div>
                
                <div class="six wide column">
                
                    <div class="ui raised segment">
                    
                        <h3 class="ui dividing header">Antivirus Status</h3>
                        
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
                                        document.location.href = 'admin.php?page=plgwap2_antivirus_page';
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
                    


                </div>
            
            </div>
            
            
            
            <?php FUNC_WAP2_general::QuickLinks(); ?>
            
            <?php FUNC_WAP2_general::Print_HelpBlock(); ?>

            
                   
        </div>
        <?php
    } 
    
    
    
    
    
    public static function PageHTML_viewfile()
    {
        wp_enqueue_style( 'plgwap2_LoadStyle_UI' );
        wp_enqueue_script( 'plgwap2_LoadJS_UI', '', array(), false, true );
        
        
        $file = trim($_GET['file']);

        FUNC_WAP2_general::Wait_CSS_Loader();
        
        ?>
        <div id="main" class="ui main container" style="float: left;margin-top:20px;display:none">
            <h2 class="ui dividing header">View file</h2>
            
            <?php
                $msg_data = array(
                    'type' => 'info',
                    'content' => 'You can view the content of the file. Please use FTP or any editor to modify the file. <b>If you are not PHP developer and don\'t know the coding, better contact with SiteGuarding.com support.</b>'
                );
                FUNC_WAP2_general::Print_MessageBox($msg_data);
            ?>
            
            <div class="ui raised segment">
                <h3 class="ui header">File: <?php echo $file; ?></h3>
            
                <?php
                
                $license_info = $_SESSION['session_plgwap2_license_info'];
                
                foreach ($license_info['last_scan_files']['heuristic'] as $row) 
                {
                    if ($file == $row)
                    {
                        // Show file
                        echo highlight_file(SG_SITE_ROOT.$file, true);
                        break;
                    }
                }
                ?>
            </div>
        </div>
        <?php
    }



    public static function StartScanner()
    {
        // Prepare $_REQUEST vars
        $_REQUEST['task'] = 'scan';
        $_REQUEST['access_key'] = $_SESSION['session_plgwap2_params']['access_key'];
        $_REQUEST['email'] = $_SESSION['session_plgwap2_license_info']['email'];
        $_REQUEST['session_report_key'] = self::GetReportSessionKey();

        include(SG_SITE_ROOT.'webanalyze'.DIRSEP.'antivirus.php');
    }
    
    
    public static function CreateReportSessionKey()
    {
        $_SESSION['session_plgwap2_session_report_key'] = md5(get_site_url().'-'.rand(1, 100000).'-'.time());
    }
    
    public static function GetReportSessionKey()
    {
        return $_SESSION['session_plgwap2_session_report_key'];
    }
    
}




class WAP2_AVP_SEO_SG_Protection
{
    public static $search_words = array(
        0 => 'document.write(',
    	6 => 'document.createElement(',
    	20 => 'display:none',
    	21 => 'poker',
    	22 => 'casino',
    	48=> 'hacked',
    	49=> 'cialis ',
    	52=> 'viagra '
    );
    
    
    public static function PrepareResults($results)
    {
        $a = array(
            'WORDS' => array(),
            'A' => array(),
            'IFRAME' => array(),
            'SCRIPT' => array()
        );
        
        //return $results;
        
        if (count($results['posts']['WORDS']))
        {
            foreach ($results['posts']['WORDS'] as $post_id => $post_arr)
            {
                foreach ($post_arr as $word)
                {
                    $a['WORDS'][$word] = $word;
                }
            }
        }
        
        if (count($results['posts']['A']))
        {
            foreach ($results['posts']['A'] as $posts)
            {
                if (count($posts))
                {
                    foreach ($posts as $post_id => $post_arr)
                    {
                        if (count($post_arr))
                        {
                            foreach ($post_arr as $post_link => $post_txt)
                            {
                                $a['A'][$post_link] = $post_txt;
                            }
                        }
                    }
                }
            }
        }
        
        
        if (count($results['posts']['IFRAME']))
        {
            foreach ($results['posts']['IFRAME'] as $posts)
            {
                if (count($posts))
                {
                    foreach ($posts as $post_id => $post_link)
                    {
                        $a['IFRAME'][$post_link] = $post_link;
                    }
                }
            }
        }
        
        //print_r($results['posts']['IFRAME']);exit;
        if (count($results['posts']['SCRIPT']))
        {
            foreach ($results['posts']['SCRIPT'] as $post_id => $post_arr)
            {
                foreach ($post_arr as $js_link => $js_code)
                {
                    if (strpos($js_link, "javascript code") !== false) $a['SCRIPT'][md5($js_code)] = $js_code;
                    else $a['SCRIPT'][md5($js_link)] = $js_link;
                }
            }
        }
        
        //echo '0000'.$post_link;exit;
        //print_r($a); exit;
        
        ksort($a['A']);
        ksort($a['SCRIPT']);
        sort($a['IFRAME']);
        return $a;
        
    }


    public static function GetPostTitle_by_ID($post_id)
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'posts';
        
        $rows = $wpdb->get_results( 
        	"
        	SELECT post_title
        	FROM ".$table_name."
            WHERE ID = ".$post_id."
            LIMIT 1;
        	"
        );
        
        if (count($rows)) return $rows[0]->post_title;
        else return false;
    }
    
    public static function GetPostTitles_by_IDs($post_ids = array())
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'posts';
        
        $rows = $wpdb->get_results( 
        	"
        	SELECT ID, post_title
        	FROM ".$table_name."
            WHERE ID IN (".implode(",", $post_ids).")
        	"
        );
        
        if (count($rows)) 
        {
            $a = array();
            foreach ($rows as $row)
            {
                $a[$row->ID] = $row->post_title;
            }
            return $a;
        }
        else return false;
    }
    
    public static function MakeAnalyze()
    {
        error_reporting(0);
        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'posts';
        
        $rows = $wpdb->get_results( 
        	"
        	SELECT ID, post_content AS val_data
        	FROM ".$table_name."
        	"
        );
        
        $a = array();
        if (count($rows))
        {
            include_once(dirname(__FILE__).DIRSEP.'simple_html_dom.php');
            
            $domain = self::PrepareDomain(get_site_url());
            
            $a['total_scanned'] = count($rows);
            
            foreach ($rows as $row)
            {
                //$post_content = $row->val_data;
				$post_content = "<html><body>".$row->val_data."</body></html>";
                
                foreach (self::$search_words as $find_block)
                {
                    if (stripos($post_content, $find_block) !== false)
                    {
                        $a['posts']['WORDS'][$row->ID][] = $find_block;
                    }
                }
                
                $html = str_get_html($post_content);
                
                if ($html !== false)
                {
                    $tmp_a = array();
                    
                    // Tag A
                    foreach($html->find('a') as $e) 
                    {
                        $link = strtolower(trim($e->href));
                        if (strpos($link, $domain) !== false) continue;     // Skip own links
                        if (strpos($link, "mailto:") !== false) continue;
                        if (strpos($link, "callto:") !== false) continue;
                        if ( $link[0] == '?' || $link[0] == '/' ) continue;
                        if ( $link[0] != 'h' && $link[1] != 't' && $link[2] != 't' && $link[3] != 'p' ) continue;
                        
                        //$tmp_s = $link.' <span class="color_light_grey">[Txt: '.strip_tags($e->outertext).']</span>';

                        /*$tmp_data = array(
                            'l' => $link,
                            't' => strip_tags($e->outertext)
                        );
                        $tmp_a[$link] = $tmp_data;*/
                        $tmp_a[$link] = strip_tags($e->outertext);
                        
                        $a['posts']['A'][$row->ID][] = $tmp_a;
                    }
                    
                    
                    
                    // Tag IFRAME
                    foreach($html->find('iframe') as $e) 
                    {
                        $link = strtolower(trim($e->src));
                        if (strpos($link, $domain) !== false) continue;     // Skip own links
                        if ( $link[0] == '?' || $link[0] == '/' ) continue;
                        if ( $link[0] != 'h' && $link[1] != 't' && $link[2] != 't' && $link[3] != 'p' ) continue;
                        
                        /*$tmp_data = array(
                            'l' => $link,
                            't' => 'iframe'
                        );
                        $tmp_a[$link] = $tmp_data;*/
                        
                        $a['posts']['IFRAME'][$row->ID][] = $link;
                    }
                    
                    
                    
                	// Tag SCRIPT
                	foreach($html->find('script') as $e)
                	{
                	    if (isset($e->src)) 
                        {
                            $link = strtolower(trim($e->src));
                        
                            if (strpos($link, $domain) !== false) continue;     // Skip own links
                            if ( $link[0] == '?' || $link[0] == '/' ) continue;
                            if ( $link[0] != 'h' && $link[1] != 't' && $link[2] != 't' && $link[3] != 'p' ) continue;
                            
                            $t = '';
                        }
                        else  {
                            $link = 'javascript code '.rand(1, 1000);
                            $t = $e->innertext;
                        }
                        
                        /*$tmp_data = array(
                            'l' => $link,
                            't' => $t
                        );*/
                        $tmp_a[$link] = $t;
                        
                        $a['posts']['SCRIPT'][$row->ID] = $tmp_a;
                    }
                    
                }
                
                unset($html);
            }
            
        }
        
        // save results
        $data = array(
            'sql_results' => json_encode($a),
            'sql_latest_scan_date' => date("Y-m-d H:i:s")
        );
        FUNC_WAP2_general::Set_SQL_Params($data);
    }
    
    
    




	public static function PrepareDomain($domain)
	{
	    $host_info = parse_url($domain);
	    if ($host_info == NULL) return false;
	    $domain = $host_info['host'];
	    if ($domain[0] == "w" && $domain[1] == "w" && $domain[2] == "w" && $domain[3] == ".") $domain = str_replace("www.", "", $domain);
	    //$domain = str_replace("www.", "", $domain);
	    
	    return $domain;
	}

}

?>