<?php
// If currently an admin, show the Facebook Simple Like option under the Settings section.
// Show nag screen asking to rate plugin (first time only).
// Add facebook script to admin footer to enable preview of like box.
if (is_admin()) {
    add_action('admin_menu', 'fsl_admin_menu', 9);
    add_action('admin_init', 'register_fsl_settings', 9);
    add_action('admin_notices', 'rate_plugin_notice');
    add_action('admin_footer', 'add_fb_script_to_footer'); // Necessary for preview
}

function fsl_admin_menu()
{
    $icon = 'http://0.gravatar.com/avatar/89ba550ea497b0b1a329b4e9b10034b2?s=16&amp;d=http%3A%2F%2F0.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D16&amp';
    // plugins_url('facebook-simple-like/icon.png');
    add_object_page('Facebook Simple Like', 'Facebook Like', 'edit_theme_options', 'facebook-simple-like/fsl-settings.php', '', $icon, 79);
    wp_register_style('fsl_admin_stylesheet', WP_PLUGIN_URL . '/facebook-simple-like/admin-stylesheet.css');
    add_action('admin_enqueue_scripts', 'fsl_enqueue_admin_scripts');
    //add_action( 'admin_print_styles-' . $page, 'fsl_admin_style_enqueue');
}

function fsl_enqueue_admin_scripts()
{
    wp_enqueue_style('fsl_admin_stylesheet');
}

function register_fsl_settings()
{
    register_setting('fsl_options', 'fsl_options', 'fsl_options_validation');
}

function rate_plugin_notice()
{
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
    if (!$rateNoticeDismissed && (!$reminderDate || ($reminderDate < strtotime('now')))) {
        ?>
    <div id="message" class="updated"><p>
        Hey, you've been using <a href="admin.php?page=facebook-simple-like/fsl-settings.php">Facebook Simple Like</a>
        for a while. Will you please take a moment to rate it? <br/><br/><a
        href="http://wordpress.org/extend/plugins/facebook-simple-like" target="_blank" style="
    border: 1px solid #777;
    padding: 5px;
    font-size:1.1em;
    text-shadow: 3px 1px 10px black;
    color: green;
" onclick="jQuery.ajax({'type': 'get', 'url':'options-general.php?page=fsl_options&dismiss_rate_notice=1'});">sure, i'll
        rate it rate now</a>
        <a href="options-general.php?page=fsl_options&remind_rate_later=1" style="
    border: 1px solid #777;
    padding: 5px;
    text-shadow: 3px 1px 10px black;
    color: black;
font-size: 1.1em;
">remind me later</a>
    </p></div>
    <?php
    }
}

// Establish options for settings page
// Validation options are passed through
// ALL of the options array passes through this. This must be amended for new options.  
function fsl_options_validation($input)
{
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

    $safe['widget_shortcodes_enable'] = $input['widget_shortcodes_enable']; // no validation for this  
    $safe['add_like_to_post_top'] = $input['add_like_to_post_top']; // no validation for this  
    $safe['add_like_to_post_bottom'] = $input['add_like_to_post_bottom']; // no validation for this  

    // Stores generated like_string from these inputs.
    $safe['like_string'] = like_code_from_url($safe['actual_url']);

    return $safe;
}

function extract_profileid_from_pageurl($pageurl)
{
    if (preg_match('/\/([^\/]+)(\?.*?)?$/', $pageurl, $matches)) {
        $pageidentifier = $matches[1];
        $fbpagehtml = wp_remote_get('https://graph.facebook.com/' . $pageidentifier);
        $fbpagehtml = is_wp_error($fbpagehtml) ? '{}' : $fbpagehtml['body'];
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

?>
