<?php
/*
Plugin Name: Facebook Simple Like
Plugin URI: http://www.mdpatrick.com/2011/fsl/
Version: 1.1.0
Author: Dan Patrick
Description: Want to boost your Facebook fan page subscription rate? This plugin makes what should be an easy task, but isn't, an easy one. It enables you to use a shortcode to place a small like button where ever you like without the clutter: stream, faces, count, and all of the other junk that comes with the "fan page like box" ordinarily. Basically, it generates a fan page subscription button that looks *identical* to the one ordinarily only for *sharing* a page (as opposed to actually subscribing). Enables shortcodes & widget.
*/
define('WIDGET_STYLESHEET_URL', plugins_url( 'facebook-simple-like.css', __FILE__) );
define('WIDGET_STYLESHEET_PATH', dirname(__FILE__) . '/facebook-simple-like.css');

$fsl_options = get_option( 'fsl_options' ); // This fails and displays error first time.
if (!isset($fsl_options['fsl_color']))
    $fsl_options['fsl_color'] = "#FFFFFF"; // Set default "border" color

// Enable shortcodes in widgets if requested.
if (isset($fsl_options['widget_shortcodes_enable']))
    add_filter('widget_text', 'do_shortcode');


// Automatically add like button to post if specified in options and like box exists.
if ($fsl_options['add_like_to_post_bottom'] && $fsl_options['like_string']) {
    add_filter('the_content', 'add_like_to_post_bottom');
}
if ($fsl_options['add_like_to_post_top'] && $fsl_options['like_string']) {
    add_filter('the_content', 'add_like_to_post_top');
}
function add_like_to_post_bottom($postcontent) {
    global $fsl_options;
    $postcontent .= "<br />" . $fsl_options['like_string'];
    return $postcontent;
}
function add_like_to_post_top($postcontent) {
    global $fsl_options;
    $content = $fsl_options['like_string'] . "<br />";
    $content .= $postcontent;
    return $content;
}

function add_fb_script_to_footer() {
    echo '<div id="fb-root"></div><script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>';
}
add_action('wp_footer', 'add_fb_script_to_footer');
add_action('admin_footer', 'add_fb_script_to_footer');

function fsl_shortcode( $atts ) {
        global $fsl_options;
        extract( shortcode_atts( array('profile' => $fsl_options['profile_id'], 'pageurl' => $fsl_options['actual_url']), $atts ) );
        $fsl_stored_like_strings = get_option('fsl_stored_like_strings');
        if (is_array($fsl_stored_like_strings) && array_key_exists($pageurl, $fsl_stored_like_strings)) {
            return $fsl_stored_like_strings[$pageurl];
        } else {
            if ($like_string = like_code_from_url($pageurl)) {
                $fsl_stored_like_strings[$pageurl] = $like_string;
                update_option('fsl_stored_like_strings', $fsl_stored_like_strings);
                
                return $like_string;
            }
        }
}

//[facebooksimplelike] is substituted with return string delivered by fsl_shortcode function 
add_shortcode( 'facebooksimplelike', 'fsl_shortcode' );

// Changes the color in the stylesheet.
function fsl_newcolor($newcolor) {
    //Open stylesheet, replace out whatever is there with the color that exists in $fsl_option['fsl_color']
    $fsl_stylesheet = wp_remote_get( constant( "WIDGET_STYLESHEET_PATH" ) );
    $fsl_stylesheet = is_wp_error( $fsl_stylesheet ) ? '' : $fsl_stylesheet['body'];
    // $fsl_stylesheet = preg_replace('/(\.full_widget.*background-color: )(#\d{3,6})(.*?)$/', "/$1#".$fsl_option['fsl_color']."$3/", $fsl_stylesheet);
    $fsl_stylesheet = preg_replace('/(\.full_widget.*background-color: )(#[0-9A-Fa-f]{3,6})(.*?)$/', "$1".$newcolor."$3", $fsl_stylesheet);
    file_put_contents(constant("WIDGET_STYLESHEET_PATH"), $fsl_stylesheet);
}


add_action( 'admin_notices', 'rate_plugin_notice' );
function rate_plugin_notice() {
    if ($_GET['dismiss_rate_notice'] == '1') {
        update_option('fsl_rate_notice_dismissed', '1');
    } elseif ($_GET['remind_rate_later'] == '1') {
        update_option('fsl_reminder_date', strtotime('+10 days'));
    } else {
	// If no dismiss & no reminder, this is fresh install. Lets give it a few days before nagging.
	update_option('fsl_reminder_date', strtotime('+3 days'));
    }

    $rateNoticeDismissed = get_option('fsl_rate_notice_dismissed');
    $reminderDate = get_option('fsl_reminder_date');
    if (!$rateNoticeDismissed && (!$reminderDate || ($reminderDate < strtotime('now')))) { ?>
       <div id="message" class="updated" ><p>
        Hey, you've been using <a href="admin.php?page=facebook-simple-like/fsl-settings.php">Facebook Simple Like</a> for a while. Will you please take a moment to rate it? <br /><br /><a href="http://wordpress.org/extend/plugins/facebook-simple-like" target="_blank" style="
    border: 1px solid #777;
    padding: 5px;
    font-size:1.1em;
    text-shadow: 3px 1px 10px black;
    color: green;
" onclick="jQuery.ajax({'type': 'get', 'url':'options-general.php?page=fsl_options&dismiss_rate_notice=1'});">sure, i'll rate it rate now</a>
<a href="options-general.php?page=fsl_options&remind_rate_later=1" style="
    border: 1px solid #777;
    padding: 5px;
    text-shadow: 3px 1px 10px black;
    color: black;
font-size: 1.1em;
">remind me later</a>
       </p></div>
    <?php }
}


// Establish options for settings page
function register_fsl_settings() {
    register_setting('fsl_options', 'fsl_options', 'fsl_options_validation');
}
// Validation options are passed through
// ALL of the options array passes through this. This must be ammended for new options.  
function fsl_options_validation($input) {  
    $safe = array();  
    $input['profile_id'] = trim($input['profile_id']);  
    $input['actual_url'] = trim($input['actual_url']);  
    if (preg_match('/^.*?([\d]{3,20})[^\d]*$/', $input['profile_id'], $matches)) {  
        $safe['profile_id'] = $matches[1];  
    }  
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
 
    $safe['widget_shortcodes_enable'] = $input['widget_shortcodes_enable']; // no validation for this  
    $safe['add_like_to_post_top'] = $input['add_like_to_post_top']; // no validation for this  
    $safe['add_like_to_post_bottom'] = $input['add_like_to_post_bottom']; // no validation for this  
    
    // Stores generated like_string from these inputs.
    $safe['like_string'] = like_code_from_url($safe['actual_url'], $safe['fsl_color']);

    return $safe;  
} 
 

// If currently an admin, show the Facebook Subscription Surge option under the Settings section.
if ( is_admin() ) {
    add_action( 'admin_menu', 'fsl_admin_menu', 9 );
    add_action('admin_init', 'register_fsl_settings', 9);
}

function fsl_admin_menu() {
        $icon = 'http://0.gravatar.com/avatar/89ba550ea497b0b1a329b4e9b10034b2?s=16&amp;d=http%3A%2F%2F0.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D16&amp';
	// plugins_url('facebook-simple-like/icon.png');
	add_object_page('Facebook Simple Like', 'Facebook Like', 'edit_theme_options', 'facebook-simple-like/fsl-settings.php', '', $icon, 79);
   	wp_register_style('fsl_admin_stylesheet', WP_PLUGIN_URL . '/facebook-simple-like/includes/admin-stylesheet.css');
	add_action( 'admin_enqueue_scripts', 'fsl_enqueue_admin_scripts' );
    //add_action( 'admin_print_styles-' . $page, 'fsl_admin_style_enqueue');
}
function fsl_enqueue_admin_scripts() {
	wp_enqueue_style('fsl_admin_stylesheet');
}

require('fsl-widget.php');

function extract_profileid_from_pageurl($pageurl) {
	if (preg_match('/\/([^\/]+)(\?.*?)?$/', $pageurl, $matches)) {
	    $pageidentifier = $matches[1];
	    $fbpagehtml = wp_remote_get('https://graph.facebook.com/'.$pageidentifier);
	    $fbpagehtml = is_wp_error( $fbpagehtml ) ? '{}' : $fbpagehtml['body'];
        $json = json_decode($fbpagehtml, true);
	    if ($json && isset($json['id'])) {
            return $json['id'];
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function like_code_from_url($pageurl, $css_suffix = null) {
    if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $pageurl)) {
         return false;
    }
    if (!$profileid = extract_profileid_from_pageurl($pageurl)) {
        return false;
    }
    if (!$css_suffix) {
        $stylesheet = constant("WIDGET_STYLESHEET_URL") . '?' . rand(0,999999);
    } else {
            $stylesheet = constant("WIDGET_STYLESHEET_URL") . '?' . $css_suffix;
    }
        $like_string = '<fb:fan href="'.$pageurl.'" width="60" height="34" show_faces="false" stream="false" header="false" profile_id="'.$profileid.'" css="'.$stylesheet.'"></fb:fan>';
        return $like_string;
    }
?>
