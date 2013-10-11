<?php
/*
Plugin Name: Facebook Simple Like
Plugin URI: http://www.mdpatrick.com/2011/fsl/
Version: 1.1.1
Author: Dan Patrick
Description: Want to boost your Facebook fan page subscription rate? This plugin makes what should be an easy task, but isn't, an easy one. It enables you to use a shortcode to place a small like button where ever you like without the clutter: stream, faces, count, and all of the other junk that comes with the "fan page like box" ordinarily. Basically, it generates a fan page subscription button that looks *identical* to the one ordinarily only for *sharing* a page (as opposed to actually subscribing). Enables shortcodes & widget.
*/
define('WIDGET_STYLESHEET_URL', plugins_url('facebook-simple-like.css', __FILE__));
wp_enqueue_style('facebooksimplelike_style', dirname(__FILE__) . '/facebook-simple-like.css');

// Enable widget and admin settings area w/ form validation
require('fsl-widget.php');
require('fsl-admin.php');

add_action('wp_footer', 'add_fb_script_to_footer');
add_shortcode('facebooksimplelike', 'fsl_shortcode');

$fsl_options = get_option('fsl_options'); // This fails and displays error first time.

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

function add_like_to_post_bottom($postcontent)
{
    global $fsl_options;
    $postcontent .= "<br />" . $fsl_options['like_string'];
    return $postcontent;
}

function add_like_to_post_top($postcontent)
{
    global $fsl_options;
    $content = $fsl_options['like_string'] . "<br />";
    $content .= $postcontent;
    return $content;
}

function add_fb_script_to_footer()
{
    echo '<div id="fb-root"></div><script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>';
}

function fsl_shortcode($atts)
{
    global $fsl_options; // TODO remove global!
    extract(shortcode_atts(array('profile' => $fsl_options['profile_id'], 'pageurl' => $fsl_options['actual_url']), $atts));
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

function like_code_from_url($pageurl)
{
    $is_url = preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $pageurl);
    if (!$is_url || !$profileid = extract_profileid_from_pageurl($pageurl)) {
        return false;
    }
    $like_string = '<fb:fan href="' . $pageurl . '" width="60" height="34" show_faces="false" stream="false" header="false" profile_id="' . $profileid . '"></fb:fan>';
    return $like_string;
}

?>
