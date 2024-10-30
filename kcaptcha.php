<?php
/*
  Plugin Name: KCaptcha
  Plugin URI: http://ksolves.com/plugins.php
  Description: This is a plugin to add captcha to anywhere in website's form.
  Author: Ksolves
  Version: 1.0.1
  Author URI: http://products.ksolves.com
 */
session_start();

/**
 * 
 * Adding captcha 
 *     
 * @param none 
 * @return false
 * @author Ksolves
 * */
function kcaptcha() {
/**
 * 
 * To validate captcha
 *     
 * @param none 
 * @return boolean
 * @author Ksolves
 * */    
    function validateKCaptcha() {
        //check the entered value and captcha text are equal or not
        if (empty($_SESSION['captcha_code']) || strcasecmp($_SESSION['captcha_code'], $_POST['captcha_code']) != 0) {
            return false;
        }
        return true;
    }
?>
<p>
        <script type='text/javascript'>            
            function refreshCaptcha() {
                var img = document.images['captchaimg'];
                img.src = img.src.substring(0, img.src.lastIndexOf("?")) + "?rand=" + Math.random() * 1000;
            }
        </script>
    <?php if (isset($msg)) { ?>            
            <label colspan="2" align="center" valign="top"><?php echo $msg; ?></label>            
    <?php } ?>
        <img src="<?php echo plugin_dir_url( __FILE__ ); ?>lib/createcaptcha.php?rand=<?php echo rand(); ?>" id='captchaimg'><br>
        <label for='message'>Enter the code above here :</label>
        <br>
        <input id="captcha_code" name="captcha_code" type="text">
        <br>
        Can't read the image? click <a href='javascript: refreshCaptcha();'>here</a> to refresh.
    </p>
<?php

}
//Adding admin_menu hook to the set_param function
add_action('admin_menu', 'set_setting_page');

/**
 * 
 * To add setting option page
 *    
 * @param none  
 * @return false
 * @author Ksolves
 * */
function set_setting_page() {
    //Add options page for setting of this plugin, this page will visible on admin section 
    add_options_page('Kcaptcha', 'Kcaptcha', 'manage_options',__FILE__, 'setting_page');
}


/**
 * 
 * Adding a form to get credentials from admin
 *    
 * @param none  
 * @return false
 * @author Ksolves
 * */
function setting_page() {
    //include setting form
    include_once dirname( __FILE__ ).'/admin/setting.php';
}

//Create welcome page on activation
/* Runs when plugin is activated */
register_activation_hook(__FILE__, 'kcaptcha_plugin_install');

//Trash welcome page on deactivation
/* Runs on plugin deactivation */
register_uninstall_hook(__FILE__, 'kcaptcha_plugin_uninstall');

/**
 * 
 * To create a setting table when a plugin activated
 *
 * @param null     
 * @return false
 * @author Ksolves
 * */
function kcaptcha_plugin_install() {
    global $wpdb;
    //Create a setting table 
    $wpdb->query("CREATE TABLE IF NOT EXISTS `wp_kcaptcha_setting` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `login_form` tinyint(4) NOT NULL DEFAULT '1',
                    `register_form` tinyint(4) NOT NULL DEFAULT '1',
                    `lost_password_form` tinyint(4) DEFAULT '1',
                    `comments_form` tinyint(4) NOT NULL DEFAULT '1',
                    `hide_register` tinyint(4) NOT NULL DEFAULT '1',
                    PRIMARY KEY (`id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

}

/**
 * 
 * Drop table when uninstall
 *   
 * @param null     
 * @return false
 * @author Ksolves
 * */
function kcaptcha_plugin_uninstall() {
    //Drop table for setting
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}_kcaptcha_setting");
}

// Add settings link to plugins list
function add_kcaptcha_settings_link( $links ) {
  	array_unshift( $links, '<a href="options-general.php?page='.__FILE__.'">Settings</a>' );
  	return $links;
}

$plugin = plugin_basename( __FILE__ );
add_filter( 'plugin_action_links_'.$plugin, 'add_kcaptcha_settings_link' );



//get the seting values from database
global $wpdb;
$result = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kcaptcha_setting`");
  foreach ($result as $page) {
      $login_form          = $page->login_form;
      $register_form       = $page->register_form;
      $lost_password_form  = $page->lost_password_form;
      $comments_form       = $page->comments_form;
      $hide_register       = $page->hide_register;      
  }
  
/* Add captcha into login form */
if ( 1 == $login_form ) {
	add_action( 'login_form', 'kcaptcha_login_form' );
	add_action( 'authenticate', 'kcaptcha_login_check', 21, 1 );	
}

/**
 * 
 * Add captcha into login form
 * 
 * @param none
 * @return false
 * @author Ksolves
 * */
function kcaptcha_login_form() {
    kcaptcha();
}

/**
 * 
 * Validating captcha on login form
 * 
 * @param none
 * @return false
 * @author Ksolves
 * */
function kcaptcha_login_check($user) {

    if (isset($_POST['captcha_code'])) {
        //Check for captcha validation
        if (empty($_SESSION['captcha_code']) || strcasecmp($_SESSION['captcha_code'], $_POST['captcha_code']) != 0) {
            $error = new WP_Error();
            $error->add('cptch_error', __('<strong>ERROR</strong>: Please enter a valid CAPTCHA value.', 'captcha'));
            return $error;
        }
    }
    return $user;
}

/* Add captcha in the register form */
if ( 1 == $register_form ) {
	add_action( 'register_form', 'kcaptcha_register_form' );
        //Adding registration_errors filter to the myplugin_check_fields function
    add_filter('registration_errors', 'kcaptcha_register_validate', 10, 3);
}

/**
 * 
 * Add captcha in the register form
 * 
 * @param none
 * @return false
 * @author Ksolves
 * */
function kcaptcha_register_form(){
    kcaptcha();
}

/**
 * 
 * Adding validation to added captcha
 *
 * @param @object $errors object of the class WP_Error
 * @param $sanitized_user_login Sanitized user login 
 * @param $user_email user email
 * @return $errors
 * @author Ksolves
 **/
function kcaptcha_register_validate($errors, $sanitized_user_login, $user_email) {
    if (empty($_SESSION['captcha_code']) || strcasecmp($_SESSION['captcha_code'], $_POST['captcha_code']) != 0) {
        $errors->add('Captcha Mismatch', __('<strong>ERROR</strong>: The Validation code does not match!.', 'mydomain'));
    }
    return $errors;
}
/* Add captcha into comments form */
if ( 1 == $comments_form ) {
	global $wp_version;
	if ( version_compare( $wp_version,'3','>=' ) ) { /* wp 3.0 + */
		add_action( 'comment_form_after_fields', 'kcaptcha_comment_form_wp3', 1 );
		add_action( 'comment_form_logged_in_after', 'kcaptcha_comment_form_wp3', 1 );
	}
	/* For WP before WP 3.0 */
	add_action( 'comment_form', 'kcaptcha_comment_form' );
	add_filter( 'preprocess_comment', 'kcaptcha_comment_post' );	 
}
/**
 * 
 * This function adds captcha to the comment form
 * 
 * @param none
 * @return false
 * @author Ksolves
 * */
function kcaptcha_comment_form_wp3() {
    /* skip captcha if user is logged in and the settings allow */
    if ( is_user_logged_in() && 1 == $hide_register ) {
            return;
    }
    kcaptcha();
    remove_action('comment_form', 'kcaptcha_comment_form');
    return true;
}
/* End function kcaptcha_comment_form_wp3 */

/**
 * 
 * This function adds captcha to the comment form
 * 
 * @param none
 * @return false
 * @author Ksolves
 * */
function kcaptcha_comment_form() {
    /* skip captcha if user is logged in and the settings allow */
    if ( is_user_logged_in() && 1 == $hide_register ) {
            return;
    }
    kcaptcha();    
    return true;
}
/* End function kcaptcha_comment_form */

/**
 * 
 * This function checks captcha posted with the comment 
 * 
 * @param $comment comment entered by user 
 * @return false
 * @author Ksolves
 * */
function kcaptcha_comment_post($comment) {    
    /* skip captcha if user is logged in and the settings allow */
    if ( is_user_logged_in() && 1 == $hide_register ) {
            return $comment;
    }
    /* If captcha is empty */
    if (isset($_REQUEST['captcha_code']) && "" == $_REQUEST['captcha_code'])
        wp_die(__('Please fill the CAPTCHA.', 'captcha'));

    if (empty($_SESSION['captcha_code']) || strcasecmp($_SESSION['captcha_code'], $_REQUEST['captcha_code']) != 0) {
        // Captcha was not matched
        wp_die(__('Error: You have entered an incorrect CAPTCHA value. Click the BACK button on your browser, and try again.', 'captcha'));        
    } else {
        return( $comment );
    }
}

/* Add captcha into lost password form */
if ( 1 == $lost_password_form ) {
	add_action( 'lostpassword_form', 'kcaptcha_register_form' );
	add_action( 'lostpassword_post', 'kcaptcha_lostpassword_post', 10, 3 );
}
/**
 * 
 * This function checks the captcha posted with lostpassword form
 * 
 * @param none 
 * @return false
 * @author Ksolves
 * */
function kcaptcha_lostpassword_post() { 
    /* If captcha doesn't entered */
    if (isset($_REQUEST['captcha_code']) && "" == $_REQUEST['captcha_code']) {
        wp_die(__('Please fill the form.', 'captcha'));
    }

    /* Check entered captcha */
    if (empty($_SESSION['captcha_code']) || strcasecmp($_SESSION['captcha_code'], $_REQUEST['captcha_code']) != 0) {
        wp_die(__('Error: You have entered an incorrect CAPTCHA value. Click the BACK button on your browser, and try again.', 'captcha'));
    } else {
        return;
    }
}
/* function kcaptcha_lostpassword_post */



/* End function kcaptcha_comment_post */

/**
 * 
 * Handle to add shortcode
 * 
 * @param none 
 * @return false
 * @author Ksolves
 * */
function add_Kcaptcha() {
    ob_start();    
    kcaptcha();
    return ob_get_clean();
}
//create a shortcode 'addKcaptcha'
add_shortcode( 'addKcaptcha', 'add_Kcaptcha' );
?>