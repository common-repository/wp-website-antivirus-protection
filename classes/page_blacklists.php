<?php
defined('_SITEGUARDING_WAP') or die;

class FUNC_WAP2_blacklists
{
    public static function PageHTML()  
    {
        wp_enqueue_style( 'plgwap2_LoadStyle_UI' );
        wp_enqueue_script( 'plgwap2_LoadJS_UI', '', array(), false, true );
        
        $params = FUNC_WAP2_general::Get_SQL_Params(array('latest_scan_date', 'latest_results'));
        
        $domain = FUNC_WAP2_general::PrepareDomain(get_site_url());


        $list = WAP2_PLGSGWBM::$blacklists;
        foreach ($list as $k => $row)
        {
            $row['status'] = 'OK';
            $list[$k] = $row;
        }
        
        $latest_results = (array)json_decode($params['latest_results'], true);
        if (count($latest_results))
        {
            foreach ($latest_results as $row)
            {
                $list[$row]['status'] = 'BL';
            }
        }


        if (trim($params['latest_scan_date']) == '') $flag_status_unknown = true;
        else $flag_status_unknown = false;
        
        if (!$flag_status_unknown)
        {
            // Prepare BL and OK lists
            $tmp_arr = array('BL' => array(), 'OK' => array());
            foreach ($list as $k => $row)
            {
                if ($row['status'] == "OK")
                {
                    $tmp_arr['OK'][$k] = $row;
                }
                else {
                    $tmp_arr['BL'][$k] = $row;
                }
            }
        }
        
        


        ?>
        <script>
        function ShowLoader()
        {
            jQuery(".ajax_button").hide();
            jQuery(".scanner_ajax_loader").show(); 
            
            jQuery.post(
                ajaxurl, 
                {
                    'action': 'plgwap2_ajax_scan_blacklist'
                }, 
                function(response){
                    document.location.href = 'admin.php?page=plgwap2_blacklists_page';
                }
            );  
        }
        </script>
        
        <?php
            FUNC_WAP2_general::Wait_CSS_Loader();
        ?>
        
        
        <div id="main" class="ui main container" style="float: left;margin-top:20px;display:none">
            <h2 class="ui dividing header">Blacklist status</h2>
            
            <div class="ui grid">
                <div class="six wide column">
                
                <?php 
                    if ($flag_status_unknown)
                    {
                        ?>
                        <p class="sg_center">
                            <i class="question circle outline icon massive red"></i>
                        </p>
                        <?php
                    }
                    else {
                        $data = array(
                            array(
                                'txt' => 'Clean ('.count($tmp_arr['OK']).')',
                                'val' => count($tmp_arr['OK']),
                            ),
                            array(
                                'txt' => 'Blacklisted ('.count($tmp_arr['BL']).')',
                                'val' => count($tmp_arr['BL']),
                            ),
                        );
                        FUNC_WAP2_general::Print_PIE_chart($data);
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
                        'title' => 'Blacklist Scanner',
                        'description' => 'you can check your website in 30+ blacklists',
                    ),
                    array(
                        'active' => 'active',
                        'icon' => 'check green',
                        'title' => 'Blacklist Removal & Monitoring',
                        'description' => 'if any issue detected, we will fix your website',
                    ),
                );
                
                if (!FUNC_WAP2_general::IsPRO_full())
                {
                    $data[1]['active'] = 'disabled';
                    $data[1]['icon'] = 'lock';
                }

                FUNC_WAP2_general::PrintSteps($data);
            ?>
            
            
            
            <?php if (!FUNC_WAP2_general::IsPRO()) FUNC_WAP2_general::BannerArea(); ?>
            
            <?php
            if ($flag_status_unknown)
            {
                $msg_data = array(
                    'type' => 'warning',
                    'content' => 'You don\'t have any results yet. Please use the button <b>Recheck</b> to get the results.'
                );
                FUNC_WAP2_general::Print_MessageBox($msg_data);
            }

            ?>
            

            

            <div class="ui grid">
                <div class="ten wide column">
                    <?php
                    if (!$flag_status_unknown)
                    {
                        // Show blocked
                        foreach ($tmp_arr['BL'] as $k => $row)
                        {
                            $msg_data = array(
                                'type' => 'error',
                                'content' => 'Your domain ('.$domain.') is blacklisted in <img src="'.$row['logo'].'"> <b>'.$k.'</b>'
                            );
                            FUNC_WAP2_general::Print_MessageBox($msg_data);
                        }
                        
                        // Show OK
                        foreach ($tmp_arr['OK'] as $k => $row)
                        {
                            $msg_data = array(
                                'type' => 'ok',
                                'content' => 'Not blacklisted in <img src="'.$row['logo'].'"> <b>'.$k.'</b>'
                            );
                            FUNC_WAP2_general::Print_MessageBox($msg_data);
                        }
                        
                        /*if (count($tmp_arr['BL']) > 0 && $send_alert_email === true && $params['send_notifications'] == 1)
                        {
                            $data = array('params' => $params, 'BL' => $tmp_arr['BL']);
                            plgsgwbm_SendEmail('', $data);
                        }*/
                    }
                    
                    ?>
                </div>
                
                <div class="six wide column">
                    <div class="ui raised segment">
                        <h3 class="ui dividing header">Blacklist Status</h3>
                        <div class="content"><b>Latest check:</b> <?php echo $params['latest_scan_date']; ?></div>
                        <div class="content"><b>Blacklisted:</b> <?php echo count($tmp_arr['BL']); ?></div>
                        <div class="content"><b>Clean:</b> <?php echo count($tmp_arr['OK']); ?></div>
                        
                        <form method="post" action="admin.php?page=plgwap2_blacklists_page" novalidate="novalidate">
                        
                        <p class="sg_center">
                            <img class="scanner_ajax_loader" width="48" height="48" style="display: none;" src="<?php echo plugins_url('images/ajax_loader.svg', dirname(__FILE__)); ?>" />
                            <a class="ajax_button positive medium ui button" href="javascript:;" onclick="ShowLoader();">Recheck</a>
                        </p>
                        <?php /*
                        <input type="submit" name="submit" id="submit" class="positive medium ui button" value="Recheck">
                        <?php 
                        if (count($tmp_arr['BL']) > 0) echo '<a href="'.FUNC_WAP2_general::$LINKS['blacklist_removal'].'" class="medium negative ui button" target="_blank">Fix My Website</a>';
                        ?>
                        </p>
                        
                        <?php
                        wp_nonce_field( 'name_49FD96F7C7F5' );
                        ?>
                        <input type="hidden" name="action" value="rescan"/>
                        </form>
                        <?php
                        */
                        if (FUNC_WAP2_general::IsPRO()) {
                        ?>
                            <p><i class="user md green icon"></i>Premium customers can request free cleaning. Please contact <a href="<?php echo FUNC_WAP2_general::$LINKS['contact_support']; ?>" target="_blank">SiteGuarding.com support</a></p>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                
            </div>
            

                
      

            <div class="ui grid">
                <div class="sixteen wide column">
                    <p class="sg_center">
                        <img class="scanner_ajax_loader" width="96" height="96" style="display: none;" src="<?php echo plugins_url('images/ajax_loader.svg', dirname(__FILE__)); ?>" />
                        <a class="ajax_button positive massive ui button" href="javascript:;" onclick="ShowLoader();">Recheck</a>
                        
                         <?php 
                            if (count($tmp_arr['BL']) > 0 && !$flag_status_unknown) echo '<a href="'.FUNC_WAP2_general::$LINKS['blacklist_removal'].'" class="massive negative ui button" target="_blank">Fix My Website</a>';
                         ?>
                    </p>
                    <p class="sg_center c_red"><b>Scan process will take approximately 30 seconds</b></p>
                    <?php /*
                    <form method="post" action="admin.php?page=plgwap2_blacklists_page" novalidate="novalidate">
                    
                    <p class="sg_center"><input type="submit" name="submit" id="submit" class="massive positive ui button" value="Recheck">
                     <?php 
                        if (count($tmp_arr['BL']) > 0 && !$flag_status_unknown) echo '<a href="'.FUNC_WAP2_general::$LINKS['blacklist_removal'].'" class="massive negative ui button" target="_blank">Fix My Website</a>';
                     ?>
                    </p> 
                    <p class="sg_center c_red"><b>Scan process will take approximately 30 seconds</b></p>
                    
                    <?php
                    wp_nonce_field( 'name_49FD96F7C7F5' );
                    ?>
                    <input type="hidden" name="action" value="rescan"/>
                    </form>
                    */ ?>
                </div>
            </div>
            
            
            <?php FUNC_WAP2_general::QuickLinks(); ?>
                    
                    
        </div>
        
        <?php
    } 
    
    
    public static function UpdateBlacklistStatus()
    {
        $domain = FUNC_WAP2_general::PrepareDomain(get_site_url());
        
        $data = array(
            'latest_scan_date' => date("Y-m-d H:i:s"),
            'latest_results' => array()
        );
        
        if (WAP2_PLGSGWBM::Scan_in_Google($domain) == "BL") $data['latest_results'][] = 'Google';
        if (WAP2_PLGSGWBM::Scan_in_McAfee($domain) == "BL") $data['latest_results'][] = 'McAfee';
        if (WAP2_PLGSGWBM::Scan_in_Norton($domain) == "BL") $data['latest_results'][] = 'Norton';
        
        $URLVoid_arr = WAP2_PLGSGWBM::Scan_in_URLVoid($domain);
        if (count($URLVoid_arr))
        {
            foreach ($URLVoid_arr as $row)
            {
                $data['latest_results'][] = $row;
            }
        }
        
        //print_r($data);
        
        
        $data['latest_results'] = json_encode($data['latest_results']);
        
        FUNC_WAP2_general::Set_SQL_Params($data);
    }
    
}



class WAP2_URLVoidAPI
{
	private $_api;
	private $_plan;
	
    public $_output;
	public $_error;
	
	public function __construct( $api, $plan )
	{
		$this->_api = $api;
		$this->_plan = $plan;
	}
	
	/*
	 * Set key for the API call
	 */
	public function set_api( $api )
	{
		$this->_api = $api;
	}
	
	/*
	 * Set plan identifier for the API call
	 */
	public function set_plan( $plan )
	{
		$this->_plan = $plan;
	}

	/*
	 * Call the API
	 */
	public function query_urlvoid_api( $website, $first_time_scan = false )
	{
	    $curl = curl_init();
        
		if ($first_time_scan === true) curl_setopt ($curl, CURLOPT_URL, "http://api.urlvoid.com/".$this->_plan."/".$this->_api."/host/".$website."/scan/" );
		else curl_setopt ($curl, CURLOPT_URL, "http://api.urlvoid.com/".$this->_plan."/".$this->_api."/host/".$website."/rescan/" );
        
		curl_setopt ($curl, CURLOPT_USERAGENT, "API");
    	curl_setopt ($curl, CURLOPT_TIMEOUT, 30);
    	curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, 30);
    	curl_setopt ($curl, CURLOPT_HEADER, 0);
    	curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, false);
    	curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec( $curl );
/*
echo "<pre>";
echo $result;
echo "</pre>";
*/
		curl_close( $curl );
		return $result;
	}
	
	/*
	 * Convert array of engines to string
	 */
	public function show_engines_array_as_string( $engines, $last_char = ", " )
	{
   		if ( is_array($engines) )
		{
   		    foreach( $engines as $item ) $str .= trim($item).$last_char;
   		    return rtrim( $str, $last_char );
		}
		else
		{
		    return $engines;
		}
	}
	
	public function scan_host( $host )
	{
	    $output = $this->query_urlvoid_api( $host );
        
        if (stripos($output, '<action_result>ERROR</action_result>') !== false) $output = $this->query_urlvoid_api( $host, true );

		$this->_output = $output;
		
		$this->_error = ( preg_match( "/<error>(.*)<\/error>/is", $output, $parts ) ) ? $parts[1] : '';
		
		return json_decode( json_encode( simplexml_load_string( $output, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOWARNING ) ), true );
	}
	
}


class WAP2_PLGSGWBM
{
    public static $blacklists = array(
        'Google' => array('logo' => 'http://www.google.com/s2/favicons?domain=google.com'),
        'McAfee' => array('logo' => 'http://www.google.com/s2/favicons?domain=mcafee.com'),
        'Norton' => array('logo' => 'http://www.google.com/s2/favicons?domain=norton.com'),
        'Quttera' => array('logo' => 'http://www.google.com/s2/favicons?domain=quttera.com'),
        'ZeroCERT' => array('logo' => 'http://www.google.com/s2/favicons?domain=zerocert.org'),
        'AVGThreatLabs' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.avgthreatlabs.com'),
        'Avira' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.avira.com'),
        'Bambenek Consulting' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.bambenekconsulting.com'),
        'BitDefender' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.bitdefender.com'),
        'CERT-GIB' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.cert-gib.com'),
        'CyberCrime' => array('logo' => 'http://www.google.com/s2/favicons?domain=cybercrime-tracker.net'),
        'c_APT_ure' => array('logo' => 'http://www.google.com/s2/favicons?domain=security-research.dyndns.org'),
        'Disconnect.me (Malw)' => array('logo' => 'http://www.google.com/s2/favicons?domain=disconnect.me'),
        'DNS-BH' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.malwaredomains.com'),
        'DrWeb' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.drweb.com'),
        'DShield' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.dshield.org'),
        'Fortinet' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.fortinet.com'),
        'GoogleSafeBrowsing' => array('logo' => 'http://www.google.com/s2/favicons?domain=developers.google.com'),
        'hpHosts' => array('logo' => 'http://www.google.com/s2/favicons?domain=hosts-file.net'),
        'Malc0de' => array('logo' => 'http://www.google.com/s2/favicons?domain=malc0de.com'),
        'MalwareDomainList' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.malwaredomainlist.com'),
        'MalwarePatrol' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.malware.com.br'),
        'MyWOT' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.mywot.com'),
        'OpenPhish' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.openphish.com'),
        'PhishTank' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.phishtank.com'),
        'Ransomware Tracker' => array('logo' => 'http://www.google.com/s2/favicons?domain=ransomwaretracker.abuse.ch'),
        'SCUMWARE' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.scumware.org'),
        'Spam404' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.spam404.com'),
        'SURBL' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.surbl.org'),
        'ThreatCrowd' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.threatcrowd.org'),
        'ThreatLog' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.threatlog.com'),
        'urlQuery' => array('logo' => 'http://www.google.com/s2/favicons?domain=urlquery.net'),
        'URLVir' => array('logo' => 'http://www.google.com/s2/favicons?domain=urlvir.com'),
        'VXVault' => array('logo' => 'http://www.google.com/s2/favicons?domain=vxvault.net'),
        'WebSecurityGuard' => array('logo' => 'http://www.google.com/s2/favicons?domain=www.websecurityguard.com'),
        'YandexSafeBrowsing' => array('logo' => 'http://www.google.com/s2/favicons?domain=yandex.com'),
        'ZeuS Tracker' => array('logo' => 'http://www.google.com/s2/favicons?domain=zeustracker.abuse.ch'),
    );
    
    public static $api_urlvoid = array(
        '075d2746f96bc493d977e5c45c0e66457a147995',
        'd8a6c7bfc0bcdcafee9015f279fb87f0d2f98461',
        'e913bc7f9dd4c3d029774a8937ec0c6e48190ea2',
        'd99fdac6cbaed9d4549f1ba1b15f23950c7bcb54',
        'fcd3e995e2fd998bdaf63fa5c39423ec96fad48b',
        'b86d0094996fa5dedfa0a942d27081414ce4a9cb',
        '753b5c36de6bb9f7cfd726c7bf91020c1ecb547a',
        '095216e11be24a074ca4fe50a6d9bb8abd01e0c6',
        'dca6d53bf80cbd950cc6e2d4dce2d04772151342',
        'ed602b474bb3e1d670b5ed1ae43c8f323b736856',
        '2adc4c7b87647252fec79fde5a5ed2d01f7c57a7',
        'dbfee84de858035aafe6e26d81edd7c7b01660df',
        '91caa4eb6d2293099be5f3351c128cbdf957da9d'
    );
    
    
    function Scan_in_Google($domain)
    {
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://safebrowsing.googleapis.com/v4/threatMatches:find?key=AIzaSyBtFip7uxKIDAMCV9tQAfQZzFyW0_JQjuo",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => '  {
		"client": {
		  "clientId":      "siteguarding",
		  "clientVersion": "1.5.2"
		},
		"threatInfo": {
		  "threatTypes":      ["MALWARE", "SOCIAL_ENGINEERING"],
		  "platformTypes":    ["WINDOWS"],
		  "threatEntryTypes": ["URL"],
		  "threatEntries": [
			{"url": "https://'.$domain.'/"}
		  ]
		}
		  }',
		  CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"content-type: application/json",
			"postman-token: b05b8d34-85f2-49cf-0f8e-03686a71e4e9"
		  ),
		));

		$response = curl_exec($curl);

		curl_close($curl);

		$response = json_decode($response, true);
		if (!isset($response['matches'])) return "OK";
		else return 'BL';

    }
    
    function Scan_in_McAfee($domain)
    {
        $url = "http://www.siteadvisor.com/sites/".$domain;
        $response = wp_remote_get( esc_url_raw( $url ) );
        $content = wp_remote_retrieve_body( $response );
        
    	if (strpos($content, 'siteYellow') || strpos($content, 'siteRed'))
        {
    		return 'BL';
    	} 
        else return 'OK';
    }
    
    function Scan_in_Norton($domain)
    {
        $url = "https://safeweb.norton.com/report/show?url=".$domain;
        $response = wp_remote_get( esc_url_raw( $url ) );
        $content = wp_remote_retrieve_body( $response );
        
    	if (strpos($content, $domain) !== false)
        {
    		if (!strpos($content, 'SAFE') && !strpos($content, 'UNTESTED'))
            {
    			return 'BL';
    		}
            else return 'OK';
    	}
    }
    
    function Scan_in_URLVoid($domain)
    {
        // check if domain is subdomain
        if(substr_count($domain, '.') > 1)
        {
            $pieces = explode(".", $domain);
            $last_piece = end($pieces);
            $domain = prev($pieces) . '.' . $last_piece;
        }
        
        $tmp_api_keys = self::$api_urlvoid;
        shuffle($tmp_api_keys);
        shuffle($tmp_api_keys);
        $URLVoidAPI = new WAP2_URLVoidAPI( $tmp_api_keys[0], 'api1000' );
        $array = array();
        $array = $URLVoidAPI->scan_host( $domain );
        if (intval($array['detections']['count']) > 0) return $array['detections']['engines']['engine'];
        else return array();
    }


    
	function PrepareDomain($domain)
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