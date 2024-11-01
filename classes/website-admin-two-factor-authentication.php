<?php

class WebsiteAdminTwoFactorAuthentication
{
	
	protected $_codeLength = 6;
	static $instance; 
	protected $ip;
	protected $whiteListed;


	public function __construct() {
	    $params = FUNC_WAP2_general::Get_SQL_Params(array('enable_2fa'));
        if (!isset($params['enable_2fa']) || intval($params['enable_2fa']) == 0) return;
		//$enabled = FUNC_WAP2_general::Get_SQL_Params(array('enable_2fa'))['enable_2fa'];
		//var_dump($enabled); exit;
		//if (!$enabled) return;
		self::$instance = $this;
		$this->ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : '';
		add_action( 'init', array( $this, 'init' ) );
		$this->whiteListed = $this->ipInList();
	}

	
	public function init() {
    
		add_action( 'login_form', array( $this, 'addToLogin' ) );
		add_action( 'login_footer', array( $this, 'addToFooter' ) );
		add_filter( 'authenticate', array( $this, 'ifGTFAenabled' ), 50, 3 );
		add_action( 'personal_options_update', array( $this, 'personal_options_update' ) );
		add_action( 'profile_personal_options', array( $this, 'profile_personal_options' ) );
		add_action( 'edit_user_profile', array( $this, 'edit_user_profile' ) );
		add_action( 'edit_user_profile_update', array( $this, 'edit_user_profile_update' ) );
		add_action('admin_enqueue_scripts', array($this, 'add_qrcode_script'));
        add_filter( 'admin_init' , array( &$this , 'register_GTFA_whitelist' ) );
		
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			add_action( 'wp_ajax_two_factor_authentication_action', array( $this, 'ajaxHandler' ) );
		}

	}
	
	public function ipInList()
	{
		$list = str_replace("\r", "", trim(get_option('GTFA_whitelist', '')));
		$list = explode("\n", $list);    
		if (count($list) == 0) return false;
		
		foreach ($list as $v)
		{
			$v = trim($v);
			if ($v == '') continue;
			if ($v == $this->ip) return true;
		}
		
		return false;
	}
	
	
	public function register_GTFA_whitelist() {
        register_setting( 'general', 'GTFA_whitelist', 'esc_attr' );
        add_settings_field('GTFA_whitelist', '<label for="GTFA_whitelist">'.__('IPs Whitelist, each in new line <br>(Two-Factor Authentication):' , 'GTFA_whitelist' ).'</label>' , array(&$this, 'GTFA_whitelist_html') , 'general' );
    }
	

	public function GTFA_whitelist_html() {
		$value = get_option( 'GTFA_whitelist', '' );
        echo '<textarea cols="50" rows="10" style="resize:none;	" id="GTFA_whitelist" name="GTFA_whitelist">' . $value . '</textarea>';
    }
	
	public function add_qrcode_script() {
		wp_enqueue_script('jquery');
		wp_register_script('qrcode_script', plugins_url('../assets/qrcode.js', __FILE__),array("jquery"));
		wp_enqueue_script('qrcode_script');
	}	
	
	
	public function addToLogin() {
		if ($this->whiteListed) return;		
		?>
		<p>	
		<label title="<?php _e('If you don\'t have Website Admin Two-Factor Authentication enabled for your WordPress account, leave this field empty.','two-factor-authentication')?>"><?php _e('Two-Factor Authentication code','two-factor-authentication'); ?><span id="google-auth-info"></span><br />
		<input type="text" name="vCode" id="user_email" class="input" value="" size="20" style="ime-mode: inactive;" /></label>
		</p>
		<?php
	}


	public function addToFooter() {
		?>
		<script type="text/javascript">
			try{
				document.getElementById('user_email').setAttribute('autocomplete','off');
			} catch(e){}
		</script>
		<?php
	}
	
	
	public function ifGTFAenabled( $user, $username = '', $password = '' ) {
		$userstate = $user;
		


		if ($this->whiteListed) return $userstate;	
			
		if ( get_user_by( 'email', $username ) === false ) {
			$user = get_user_by( 'login', $username );
		} else {
			$user = get_user_by( 'email', $username );
		}

		if ( isset( $user->ID ) && trim(get_user_option( 'two_factor_authentication_enabled', $user->ID ) ) == 'enabled' ) {
			$GTFA_secret = trim( get_user_option( 'two_factor_authentication_secret', $user->ID ) );
			$GTFA_delay = trim( get_user_option( 'two_factor_authentication_delay', $user->ID ) );
			if ( !empty( $_POST['vCode'] )) { 
				$vCode = trim( $_POST[ 'vCode' ] );
			} else {
				$vCode = '';
			}
			$lasttimeslot = trim( get_user_option( 'two_factor_authentication_lasttimeslot', $user->ID ) );
			if ( $timeslot = $this->verifyCode( $GTFA_secret, $vCode, $GTFA_delay, $lasttimeslot ) ) {
				update_user_option( $user->ID, 'two_factor_authentication_lasttimeslot', $timeslot, true );
				return $userstate;
			} else {
				return new WP_Error( 'invalid_two_factor_authentication_token', __( '<strong>ERROR</strong>: The Website Admin Two-Factor Authentication code is incorrect or has expired.', 'two-factor-authentication' ) );		
			}
		}
		return $userstate;
	}
	
		
	public function profile_personal_options() {
		global $user_id, $is_profile_page;
		
		$GTFA_hidefromuser = trim( get_user_option( 'two_factor_authentication_hidefromuser', $user_id ) );
		if ( $GTFA_hidefromuser == 'enabled') return;
		
		$GTFA_secret			= trim( get_user_option( 'two_factor_authentication_secret', $user_id ) );
		$GTFA_enabled			= trim( get_user_option( 'two_factor_authentication_enabled', $user_id ) );
		$GTFA_delay		= trim( get_user_option( 'two_factor_authentication_delay', $user_id ) );

		if ( '' == $GTFA_secret ) {
			$GTFA_secret = $this->createSecret();
		}
		

		?>
		<hr>
		<h3><?php _e( 'Website Admin Two-Factor Authentication', 'two-factor-authentication' );?></h3>
		<table class="form-table">
		<tbody>
		<tr>
		<th scope="row"><?php _e( 'Enable for this user', 'two-factor-authentication' ); ?></th>
		<td>
		<input name="GTFA_enabled" id="GTFA_enabled" class="tog" type="checkbox" <?php echo checked( $GTFA_enabled, 'enabled', false ); ?>/><?php if ($this->whiteListed) : ?><span class="description" style="color:green;font-weight:bold;"> <?php _e( 'Your IP address is in the whitelist ', 'two-factor-authentication' ); ?></span>
		<?php endif; ?>
		</td>
		</tr>

		<?php if ( $is_profile_page || IS_PROFILE_PAGE ) : ?>
			<tr>
			<th scope="row"><?php _e( 'Simplified mode', 'two-factor-authentication' ); ?></th>
			<td>
			<input name="GTFA_delay" id="GTFA_delay" class="tog" type="checkbox" <?php echo checked( $GTFA_delay, 'enabled', false ); ?>/><span class="description"><?php _e(' Simplified mode allows for more time drifting on your phone clock (&#177;2 min).','two-factor-authentication'); ?></span>
			</td>
			</tr>	
			<tr>
			<th><label for="GTFA_secret"><?php _e('Secret','two-factor-authentication'); ?></label></th>
			<td>
			<input name="GTFA_secret" style="text-align:center;" id="GTFA_secret" class="regular-text" value="<?php echo $GTFA_secret; ?>" readonly="readonly"  type="text" size="25" />
			<input name="GTFA_newsecret" id="GTFA_newsecret" value="<?php _e("Create new secret",'two-factor-authentication'); ?>"   type="button" class="button" />
			<input name="show_qr" id="show_qr" value="<?php _e("Show/Hide QR code",'two-factor-authentication'); ?>"   type="button" class="button" onclick="ShowOrHideQRCode();" />
			</td>
			</tr>
			<tr>
			<th></th>
			<td><div id="GTFA_QR_INFO" style="display: none;margin-left:3%;" >
			<div id="GTFA_QRCODE" style=""></div>
			<span class="description"><br/> <?php _e( 'Scan this with the Google Authenticator app.', 'two-factor-authentication' ); ?></span>
			</div></td>
			</tr>

		<?php endif; ?>

		</tbody></table>
		<hr>
		<script type="text/javascript">
		var GTFAnonce='<?php echo wp_create_nonce('two_factor_authenticationaction'); ?>';


		//Create new secret and display it
		jQuery('#GTFA_newsecret').bind('click', function() {
			// Remove existing QRCode
			jQuery('#GTFA_QRCODE').html("");
			var data=new Object();
			data['action']	= 'two_factor_authentication_action';
			data['nonce']	= GTFAnonce;
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('#GTFA_secret').val(response['new-secret']);
				var qrcode="otpauth://totp/<?php echo urlencode(get_site_url());?>:<?php echo urlencode(wp_get_current_user()->user_login);?>?secret="+jQuery('#GTFA_secret').val()+"&issuer=<?php echo urlencode(get_site_url());?>";
				jQuery('#GTFA_QRCODE').qrcode(qrcode);
				jQuery('#GTFA_QR_INFO').show('slow');
			});  	
		});

		// If the user starts modifying the description, hide the qrcode
		jQuery('#GTFA_description').bind('focus blur change keyup', function() {
			// Only remove QR Code if it's visible
			if (jQuery('#GTFA_QR_INFO').is(':visible')) {
				jQuery('#GTFA_QR_INFO').hide('slow');
				jQuery('#GTFA_QRCODE').html("");
			}
		});


		function ShowOrHideQRCode() {
			if (jQuery('#GTFA_QR_INFO').is(':hidden')) {
				var qrcode="otpauth://totp/<?php echo urlencode(get_site_url());?>:<?php echo urlencode(wp_get_current_user()->user_login);?>?secret="+jQuery('#GTFA_secret').val()+"&issuer=<?php echo urlencode(get_site_url());?>";
				jQuery('#GTFA_QRCODE').qrcode(qrcode);
				jQuery('#GTFA_QR_INFO').show('slow');
			} else {
				jQuery('#GTFA_QR_INFO').hide('slow');
				jQuery('#GTFA_QRCODE').html("");
			}
		}
	</script>
	<?php
	}	
	
	
	public function personal_options_update() {

		global $user_id;
		$GTFA_hidefromuser = trim( get_user_option( 'two_factor_authentication_hidefromuser', $user_id ) );
		if ( $GTFA_hidefromuser == 'enabled') return;
		$GTFA_enabled	= ! empty( $_POST['GTFA_enabled'] );
		$GTFA_delay	= ! empty( $_POST['GTFA_delay'] );
		$GTFA_secret	= trim( $_POST['GTFA_secret'] );	
		if ( ! $GTFA_enabled ) {
			$GTFA_enabled = 'disabled';
		} else {
			$GTFA_enabled = 'enabled';
		}
		if ( ! $GTFA_delay ) {
			$GTFA_delay = 'disabled';
		} else {
			$GTFA_delay = 'enabled';
		}
		update_user_option( $user_id, 'two_factor_authentication_enabled', $GTFA_enabled, true );
		update_user_option( $user_id, 'two_factor_authentication_delay', $GTFA_delay, true );
		update_user_option( $user_id, 'two_factor_authentication_secret', $GTFA_secret, true );
	}
	

	public function edit_user_profile() {
		global $user_id;
		$GTFA_enabled      = trim( get_user_option( 'two_factor_authentication_enabled', $user_id ) );
		$GTFA_hidefromuser = trim( get_user_option( 'two_factor_authentication_hidefromuser', $user_id ) );
		?>
		<h3><?php _e('Website Admin Two-Factor Authentication Settings','two-factor-authentication'); ?></h3>
		<table class="form-table">
		<tbody>
		<tr>
		<th scope="row"><?php _e('Hide settings from user','two-factor-authentication'); ?></th>
		<td>
		<div><input name="GTFA_hidefromuser" id="GTFA_hidefromuser"  class="tog" type="checkbox" <?php  checked( $GTFA_hidefromuser, 'enabled', false ); ?>/>
		</td>
		</tr>
		<tr>
		<th scope="row"><?php _e('Active','two-factor-authentication'); ?></th>
		<td>
		<div><input name="GTFA_enabled" id="GTFA_enabled"  class="tog" type="checkbox" <?php checked( $GTFA_enabled, 'enabled', false ); ?>/>
		</td>
		</tr>
		</tbody>
		</table>
		<?php
	}
	

	public function edit_user_profile_update() {
		global $user_id;
		$GTFA_enabled	     = ! empty( $_POST['GTFA_enabled'] );
		$GTFA_hidefromuser = ! empty( $_POST['GTFA_hidefromuser'] );
		if ( ! $GTFA_enabled ) {
			$GTFA_enabled = 'disabled';
		} else {
			$GTFA_enabled = 'enabled';
		}
		if ( ! $GTFA_hidefromuser ) {
			$GTFA_hidefromuser = 'disabled';
		} else {
			$GTFA_hidefromuser = 'enabled';
		}
		update_user_option( $user_id, 'two_factor_authentication_enabled', $GTFA_enabled, true );
		update_user_option( $user_id, 'two_factor_authentication_hidefromuser', $GTFA_hidefromuser, true );

	}


    public function createSecret($secretLength = 16)
    {
        $validChars = $this->_getBase32LookupTable();
        if ($secretLength < 16 || $secretLength > 128) {
            throw new Exception('Bad secret length');
        }
        $secret = '';
        $rnd = false;
        if (function_exists('random_bytes')) {
            $rnd = random_bytes($secretLength);
        } elseif (function_exists('mcrypt_create_iv')) {
            $rnd = mcrypt_create_iv($secretLength, MCRYPT_DEV_URANDOM);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $rnd = openssl_random_pseudo_bytes($secretLength, $cryptoStrong);
            if (!$cryptoStrong) {
                $rnd = false;
            }
        }
        if ($rnd !== false) {
            for ($i = 0; $i < $secretLength; ++$i) {
                $secret .= $validChars[ord($rnd[$i]) & 31];
            }
        } else {
            throw new Exception('No source of secure random');
        }

        return $secret;
    }


    public function getCode($secret, $timeSlice = null)
    {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }
        $secretkey = $this->_base32Decode($secret);

        // Pack time into binary string
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        // Hash it with users secret key
        $hm = hash_hmac('SHA1', $time, $secretkey, true);
        // Use last nipple of result as index/offset
        $offset = ord(substr($hm, -1)) & 0x0F;
        // grab 4 bytes of the result
        $hashpart = substr($hm, $offset, 4);

        // Unpak binary value
        $value = unpack('N', $hashpart);
        $value = $value[1];
        // Only 32 bits
        $value = $value & 0x7FFFFFFF;

        $modulo = pow(10, $this->_codeLength);

        return str_pad($value % $modulo, $this->_codeLength, '0', STR_PAD_LEFT);
    }

	
	public function verifyCode( $secretkey, $thistry, $relaxedmode, $lasttimeslot ) {
		if ( strlen( $thistry ) != 6) {
			return false;
		} else {
			$thistry = intval ( $thistry );
		}
		if ( $relaxedmode == 'enabled' ) {
			$firstcount = -4;
			$lastcount  =  4; 
		} else {
			$firstcount = -1;
			$lastcount  =  1; 	
		}	
		$tm = floor( time() / 30 );	
		$secretkey=$this->_base32Decode($secretkey);
		for ($i=$firstcount; $i<=$lastcount; $i++) {

			$time=chr(0).chr(0).chr(0).chr(0).pack('N*',$tm+$i);
			$hm = hash_hmac( 'SHA1', $time, $secretkey, true );
			$offset = ord(substr($hm,-1)) & 0x0F;
			$hashpart=substr($hm,$offset,4);
			$value=unpack("N",$hashpart);
			$value=$value[1];

			$value = $value & 0x7FFFFFFF;
			$value = $value % 1000000;
			if ( $value === $thistry ) {
				if ( $lasttimeslot >= ($tm+$i) ) {
					error_log("Website Admin Two-Factor Authentication plugin: Man-in-the-middle attack detected (Could also be 2 legit login attempts within the same 30 second period)");
					return false;
				}
				return $tm+$i;
			}
		}
		return false;
	}


    protected function _base32Decode($secret)
    {
        if (empty($secret)) {
            return '';
        }
        $base32chars = $this->_getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);

        $paddingCharCount = substr_count($secret, $base32chars[32]);
        $allowedValues = array(6, 4, 3, 1, 0);
        if (!in_array($paddingCharCount, $allowedValues)) {
            return false;
        }
        for ($i = 0; $i < 4; ++$i) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat($base32chars[32], $allowedValues[$i])) {
                return false;
            }
        }
        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = '';
        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = '';
            if (!in_array($secret[$i], $base32chars)) {
                return false;
            }
            for ($j = 0; $j < 8; ++$j) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); ++$z) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
            }
        }

        return $binaryString;
    }


    protected function _getBase32LookupTable()
    {
        return array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '=',  
        );
    }

	
	public function ajaxHandler() {
		global $user_id;
		check_ajax_referer( 'two_factor_authenticationaction', 'nonce' );
		$secret = $this->createSecret();
		$result = array( 'new-secret' => $secret );
		header( 'Content-Type: application/json' );
		echo json_encode( $result );
		die(); 
	}
	
}

$WebsiteAdminTwoFactorAuthentication = new WebsiteAdminTwoFactorAuthentication;
