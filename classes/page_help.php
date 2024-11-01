<?php
defined('_SITEGUARDING_WAP') or die;

class FUNC_WAP2_help
{
    public static function PageHTML()  
    {
        wp_enqueue_style( 'plgwap2_LoadStyle_UI' );
        wp_enqueue_script( 'plgwap2_LoadJS_UI', '', array(), false, true );
        
        $list = array(
            array(
                'txt' => 'Installation and configuration of antivirus',
                'free' => true,
                'prem' => true
            ),
            array(
                'txt' => 'Help to check and analyze your website',
                'free' => true,
                'prem' => true
            ),
            array(
                'txt' => 'Support by email or live chat',
                'free' => true,
                'prem' => true
            ),
            array(
                'txt' => 'Daily update of antivirus database',
                'free' => false,
                'prem' => true
            ),
            array(
                'txt' => 'Full and detailed antivirus report',
                'free' => false,
                'prem' => true
            ),
            array(
                'txt' => 'Malware Removal (Hack repair and restoration, Trojan detection, Vulnerability repair, SEO poisoining recovery)',
                'free' => false,
                'prem' => true
            ),
            array(
                'txt' => 'Help to remove your website from blacklists (Google, McAfee, Norton and etc.)',
                'free' => false,
                'prem' => true
            ),
            array(
                'txt' => 'Website 24/7 monitoring service',
                'free' => false,
                'prem' => true
            ),
            array(
                'txt' => 'Website full backup service and restoring',
                'free' => false,
                'prem' => true
            ),
            array(
                'txt' => 'Website scripts/codes analyze and bugs fix to avoid possibility for future hacks',
                'free' => false,
                'prem' => true
            ),
            array(
                'txt' => 'Premium firewall rules (help to configure firewall and apply more suitable firewall rules for your website)',
                'free' => false,
                'prem' => true
            ),
            array(
                'txt' => 'Server log analyze & Issue investigation',
                'free' => false,
                'prem' => true
            ),
            array(
                'txt' => 'SSL certificate (https:// green lock in address bar)',
                'free' => false,
                'prem' => true
            ),
            array(
                'txt' => 'Unlock all security extensions',
                'free' => false,
                'prem' => true
            ),
            array(
                'txt' => 'High priority support',
                'free' => false,
                'prem' => true
            ),
        );
        
        ?>

        <?php
            FUNC_WAP2_general::Wait_CSS_Loader();
        ?>
        
        <div id="main" class="ui main container" style="float: left;margin-top:20px;display:none">
            <h2 class="ui dividing header">Help & Support</h2>
            
            
            <div class="ui two column grid">
            

              <div class="column">
                <div class="ui segment">
                    <h3 class="ui centered header">Free Support</h3>
                    <div class="ui list">
                        <?php
                        foreach ($list as $row) 
                        {
                            if ($row['free']) $tmp_class = 'check icon green';
                            else $tmp_class = 'times circle outline icon red';
                        ?>
                            <a class="item"><i class="<?php echo $tmp_class; ?>"></i><div class="content"><div class="description"><?php echo $row['txt']; ?></div></div></a>
                        <?php
                        }
                        ?>
                    </div>
                    <p style="text-align: center;"><a class="ui medium button" href="<?php echo FUNC_WAP2_general::$LINKS['contact_support']; ?>" target="_blank">Contact Support</a></p>
                </div>
              </div>
              
              <div class="column">
                <div class="ui segment">
                    <h3 class="ui centered header">Premium Support</h3>
                    <div class="ui list">
                        <?php
                        foreach ($list as $row) 
                        {
                            if ($row['prem']) $tmp_class = 'check icon green';
                            else $tmp_class = 'times circle outline icon red';
                        ?>
                            <a class="item"><i class="<?php echo $tmp_class; ?>"></i><div class="content"><div class="description"><?php echo $row['txt']; ?></div></div></a>
                        <?php
                        }
                        ?>
                    </div>
                    <p style="text-align: center;"><a class="ui medium positive button" href="<?php echo FUNC_WAP2_general::$LINKS['upgrade_to_premium']; ?>" target="_blank">Upgrade to Premium</a>&nbsp;<a class="ui medium positive button" href="<?php echo FUNC_WAP2_general::$LINKS['contact_support']; ?>" target="_blank">Contact Support</a></p>
                </div>
              </div>

              
            </div>
            
            
        </div>
        <?php
    } 
}

?>