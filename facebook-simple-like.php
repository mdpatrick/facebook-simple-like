<?php
/*
Plugin Name: Facebook Simple Like
Plugin URI: http://www.mdpatrick.com/2011/fsl/
Version: 1.0.3
Author: Dan Patrick
Description: Want to boost your Facebook fan page subscription rate? This plugin makes what should be an easy task, but isn't, an easy one. It enables you to use a shortcode to place a small like button where ever you like without the clutter: stream, faces, count, and all of the other junk that comes with the "fan page like box" ordinarily. Basically, it generates a fan page subscription button that looks *identical* to the one ordinarily only for *sharing* a page (as opposed to actually subscribing).
*/

define('WIDGET_STYLESHEET_URL', plugins_url( 'facebook-simple-like.css', __FILE__) );
define('WIDGET_STYLESHEET_PATH', dirname(__FILE__) . '/facebook-simple-like.css');

$fsl_options = get_option( 'fsl_options' ); // This fails and displays error first time.
if (!isset($fsl_options['fsl_color']))
    $fsl_options['fsl_color'] = "#FFFFFF"; // Set default "border" color

function fsl_settings_page() { 
global $fsl_options;



// Below is actually what's displayed in the options page.
?>
    <div class="wrap" style="margin-bottom:0;">
    <h2>Facebook Simple Like Settings</h2>
    <form method="post" action="options.php">
    <?php settings_fields( 'fsl_options' ); // adds nonce ?>
    <strong>Where You Can Find Your Fan Page Profile ID</strong><br />
    <img src="<?php echo plugins_url( 'screenshot-3.png', __FILE__); ?>" /><br />
    <label for="profile_id">Profile ID:</label>
    <input id="profile_id" name="fsl_options[profile_id]" type="input" value="<?php echo $fsl_options['profile_id']; ?>" /> <br />
    <label for="actual_url">Actual URL of Fan Page:</label>
    <input id="actual_url" name="fsl_options[actual_url]" type="input" value="<?php echo $fsl_options['actual_url']; ?>" /> <br />
    <label id="fsl_color">Background Color:</label>
    <input id="fsl_color" name="fsl_options[fsl_color]" type="input" value="<?php echo $fsl_options['fsl_color']; ?>" /> <br />
    <label for="widget_shortcodes_enable">Enable Shortcodes In Widgets?</label>
    <input id="widget_shortcodes_enable" name="fsl_options[widget_shortcodes_enable]" type="checkbox" value="true" <?php if (isset($fsl_options['widget_shortcodes_enable'])) echo "checked"; ?> />
    <div class="instructions"><p>After entering your profile ID and saving it you can then place your new fan page like button where ever you like with the shortcode <strong>[facebooksimplelike]</strong>, or if you have more than one fan page you'll be using like buttons for: <strong>[facebooksimplelike profile="12345678910" pageurl="http://www.facebook.com/mypage"]</strong>.</p></div>
    <p class="submit"><input type="Submit" name="submit" value="<?php esc_attr_e('Save Changes'); ?>" /></p>
    </form>
<?php
    // Sends email, and prints response if contact form is used.
    if (preg_match('/[A-Za-z]{4,15}/', $_POST['comments'])) {
        $admin_email = get_option('admin_email');
        wp_mail(
            'dan@mdpatrick.com',                             //to
            "{$_POST['subject']} - F.S.L.",                  //subject
            "{$_POST['comments']}",                          //body
            "From: $admin_email <$admin_email>"." \r\n"      //header
            );
        echo "<h2>Email sent!</h2><br />";
        readfile( plugins_url( 'includes/helpmeout.php' , __FILE__ ) );
    }
    else {
        // Prints contact form, newsletter, donate button, etc.
        readfile( plugins_url( 'includes/helpmeout.php' , __FILE__ ) );
    }
} 

// Enable shortcodes in widgets if requested.
if (isset($fsl_options['widget_shortcodes_enable']))
    add_filter('widget_text', 'do_shortcode');

function fsl_plugin_menu() {
    $page = add_options_page( 'Facebook Simple Like Options', 'Facebook Simple Like', 'manage_options', 'fsl_options', 'fsl_settings_page' );
    // page title, menu link anchor, capablity required, menu_slug unique identifier, function used for actual display of plugin page.
    add_action( 'admin_print_styles-' . $page, 'fsl_admin_style_enqueue');
}

function fsl_admin_style_enqueue() {
    wp_enqueue_style('fsl_admin_stylesheet');
}

function fsl_shortcode( $atts ) {
global $fsl_options;
        // $atts grabs all attributes (even non-existent ones), shortcode_atts filters
        // for only what you need
        extract( shortcode_atts( array('profile' => $fsl_options['profile_id'], 'pageurl' => $fsl_options['actual_url']), $atts ) );
        $widget_stylesheet = constant("WIDGET_STYLESHEET_URL");
        $nohash = substr($fsl_options['fsl_color'], 1); // Updates cache on stylesheet if color changed.
        $intext = <<<FBFAN
<div id="fb-root"></div><script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script><fb:fan href="{$pageurl}" width="60" height="34" show_faces="false" stream="false" header="false" profile_id="{$profile}" css="{$widget_stylesheet}?{$nohash}"></fb:fan>
FBFAN;
        return $intext;
}


//[facebooksimplelike] is substituted with return string delivered by fsl_shortcode function 
add_shortcode( 'facebooksimplelike', 'fsl_shortcode' );

// Changes the color in the stylesheet.
function fsl_newcolor($newcolor) {
    //Open stylesheet, replace out whatever is there with the color that exists in $fsl_option['fsl_color']
    $fsl_stylesheet = file_get_contents(constant("WIDGET_STYLESHEET_PATH"));
    // $fsl_stylesheet = preg_replace('/(\.full_widget.*background-color: )(#\d{3,6})(.*?)$/', "/$1#".$fsl_option['fsl_color']."$3/", $fsl_stylesheet);
    $fsl_stylesheet = preg_replace('/(\.full_widget.*background-color: )(#[0-9A-Fa-f]{3,6})(.*?)$/', "$1".$newcolor."$3", $fsl_stylesheet);
    file_put_contents(constant("WIDGET_STYLESHEET_PATH"), $fsl_stylesheet);
}

// ALL of the options array passes through this. This must be ammended for new options.
function fsl_options_validation($input) {
    $safe = array();
    $input['profile_id'] = trim($input['profile_id']);
    $input['actual_url'] = trim($input['actual_url']);
    if (preg_match('/^.*?([\d]{3,20})[^\d]*$/', $input['profile_id'], $matches)) {
        $safe['profile_id'] = $matches[1];
    }
//    if (preg_match('/^http.*?(\d{8,20})\/?$/', $input['profile_id'], $matches)) {
//        $safe['profile_id'] = $matches[1];
//    }
    // This strips the trailing query string off the fan page URL.
    if (preg_match('/(http[^? ]*)/', $input['actual_url'], $matches)) {
        $safe['actual_url'] = $matches[1];
    }

    // Ensure color entered is an actual color.
    if (preg_match('/(\#[0-9A-Fa-f]{3,6})/', $input['fsl_color'], $matches)) {
        $safe['fsl_color'] = $matches[1];
        // Make file changes in css file via custom function.
        fsl_newcolor($matches[1]);
    }

//  $safe['actual_url'] = $input['actual_url'];
    $safe['widget_shortcodes_enable'] = $input['widget_shortcodes_enable']; // no validation for this
    return $safe;
}

// This registers the "settings" which are used to store the plugins various values.
function fsl_admin_enqueue() {
    // Sets up an "option group" to keep track of data submitted via options.php.
    register_setting ( 'fsl_options', 'fsl_options', 'fsl_options_validation');//, 'fsl_options_validation' ); 
    // Syntax: group name, option to save and sanitize, sanitizing function (optional)
    
    wp_register_style ('fsl_admin_stylesheet', WP_PLUGIN_URL . '/facebook-simple-like/includes/admin-stylesheet.css');
}

// If currently an admin, show the Facebook Subscription Surge option under the Settings section.
if ( is_admin() ) {
    add_action( 'admin_menu', 'fsl_plugin_menu' );
    add_action( 'admin_init', 'fsl_admin_enqueue');
}


function rate_plugin_notice() {
    if ($_GET['dismiss_rate_notice'] == '1') {
        update_option('fsl_rate_notice_dismissed', '1');
    } 

    $rateNoticeDismissed = get_option('fsl_rate_notice_dismissed');
    if ($rateNoticeDismissed != 1) { ?>
       <div id="message" class="updated" ><p>
        Please don't forget to rate Facebook Simple Like. <a href="http://wordpress.org/extend/plugins/facebook-simple-like" target="_blank" style="
    border: 1px solid #777;
    padding: 5px;
    font-size:1.3em;
    margin-left: 20px;
    text-shadow: 3px 1px 10px black;
    color: green;
">rate now</a> <a href="options-general.php?page=fsl_options&dismiss_rate_notice=1" style="
    border: 1px solid #777;
    padding: 5px;
    margin-left: 20px;
    text-shadow: 3px 1px 10px black;
    color: red;
font-size: 1.3em;
">dismiss notice</a>
       </p></div>
    <?php }
}
add_action( 'admin_notices', 'rate_plugin_notice' );

?>
