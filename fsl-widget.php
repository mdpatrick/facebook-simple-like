<?php
class FSLWidget extends WP_Widget
{
    function __construct()
    {
        $widget_ops = array('classname' => 'FSLWidget', 'description' => 'Displays a super stripped down facebook fan page like button which is attached to the fan page of your choice.');
        $this->WP_Widget('FSLWidget', 'Facebook Like Button', $widget_ops);
    }

    function form($instance)
    {
        global $fsl_options;
        $instance = wp_parse_args((array)$instance, array('title' => ''));
        $title = $instance['title'];
        if ($title === false) {
            $title = "Facebook Us";
        }
        $pageurl = $instance['pageurl'];
        if (!$pageurl) {
            $pageurl = $fsl_options['actual_url'];
        }
        ?>
    <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat"
                                                                              id="<?php echo $this->get_field_id('title'); ?>"
                                                                              name="<?php echo $this->get_field_name('title'); ?>"
                                                                              type="text"
                                                                              value="<?php echo attribute_escape($title); ?>"/></label>
    </p>
    <p><label for="<?php echo $this->get_field_id('pageurl'); ?>">Page URL: <input class="widefat"
                                                                                   id="<?php echo $this->get_field_id('pageurl'); ?>"
                                                                                   name="<?php echo $this->get_field_name('pageurl'); ?>"
                                                                                   type="text"
                                                                                   value="<?php echo attribute_escape($pageurl); ?>"/></label>
    </p>

    <?php
    }

    function update($new_instance, $old_instance)
    {
        global $fsl_options;
        $instance = $old_instance;

        $like_string = like_code_from_url($new_instance['pageurl'], $fsl_options['fsl_color']);
        if ($like_string) {
            $instance['title'] = $new_instance['title'];
            $instance['pageurl'] = $new_instance['pageurl'];
            $instance['fsl_color'] = $new_instance['fsl_color'];
            $instance['like_string'] = $like_string;
        }
        return $instance;
    }

    function widget($args, $instance)
    {
        global $fsl_options;
        extract($args, EXTR_SKIP);

        echo $before_widget;
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

        if (!empty($title))
            echo $before_title . $title . $after_title;

        // WIDGET CODE GOES HERE
        echo $instance['like_string'];

        echo $after_widget;
    }

}

add_action('widgets_init', create_function('', 'return register_widget("FSLWidget");'));

