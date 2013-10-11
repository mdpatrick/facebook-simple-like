<?php
global $fsl_options;

// Below is actually what's displayed in the options page.
?>
<div class="wrap" style="margin-bottom:0;">
    <h2>Facebook Simple Like Settings</h2>

    <form method="post" action="options.php">
        <?php settings_fields('fsl_options'); // adds nonce ?>
        <table>
            <tr>
                <td>
                    <label for="actual_url">Fan Page URL:</label><br/>
                </td>
                <td>
                    <input id="actual_url" name="fsl_options[actual_url]" type="text" style="width:280px;"
                           value="<?php echo $fsl_options['actual_url']; ?>"/>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="add_like_to_post_top">Automatically add like button to top of posts/pages</label>
                </td>
                <td>
                    <input id="add_like_to_post_top" name="fsl_options[add_like_to_post_top]" type="checkbox"
                           value="true" <?php if (isset($fsl_options['add_like_to_post_top'])) echo "checked"; ?> />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="add_like_to_post_bottom">Automatically add like button to bottom of posts/pages</label>
                </td>
                <td>
                    <input id="add_like_to_post_bottom" name="fsl_options[add_like_to_post_bottom]" type="checkbox"
                           value="true" <?php if (isset($fsl_options['add_like_to_post_bottom'])) echo "checked"; ?> />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="widget_shortcodes_enable">Enable Shortcodes In Widgets</label>
                </td>
                <td>
                    <input id="widget_shortcodes_enable" name="fsl_options[widget_shortcodes_enable]" type="checkbox"
                           value="true" <?php if (isset($fsl_options['widget_shortcodes_enable'])) echo "checked"; ?> />
                </td>
            </tr>
        </table>
        <p class="submit"><input type="Submit" name="submit" value="<?php esc_attr_e('Save Changes'); ?>"/></p>
    </form>
    <?php if ($fsl_options['like_string']) { ?>
    <div class="fsl-like-preview">
        <p>Currently saved default like button (preview):</p>
        <?php echo $fsl_options['like_string']; ?>
    </div>
    <?php } ?>
    <div class="instructions">
        <h1>Instructions</h1>
        You have three options on how to use this plugin after entering your fan page's URL:
        <ol>
            <li>Just put the shortcode <strong>[facebooksimplelike]</strong> where ever
                you want a simple like button for your fan page
            </li>
            <li>Use your theme's <a href="widgets.php">widgets interface</a> to add a like button</li>
            <li>... or ... you can even place as many like buttons to as many fan pages as you like via the this shortcode syntax:<br/><strong>[facebooksimplelike
                pageurl="http://www.facebook.com/mypage"]</strong></li>
        </ol>
        <br/>
        <hr/>
        <br/>
    </div>
    <?php
    // Sends email, and prints response if contact form is used.
    if (preg_match('/[A-Za-z]{4,15}/', $_POST['comments'])) {
        $admin_email = get_option('admin_email');
        wp_mail(
            'dan@mdpatrick.com', //to
            "{$_POST['subject']} - F.S.L.", //subject
            "{$_POST['comments']}", //body
            "From: $admin_email <$admin_email>" . " \r\n" //header
        );
        echo "<h2>Email sent!</h2><br />";
        readfile(plugins_url('helpmeout.php', __FILE__));
    } else {
        // Prints contact form, newsletter, donate button, etc.
        readfile(plugins_url('helpmeout.php', __FILE__));
    }
    ?></div></div>
