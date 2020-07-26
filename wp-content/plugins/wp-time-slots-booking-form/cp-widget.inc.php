<?php
class CP_TimeSlotsBookingPlugin_Widget extends WP_Widget
{
  function __construct()
  {
    $widget_ops = array('classname' => 'CP_TimeSlotsBookingPlugin_Widget', 'description' => 'Displays a Calendar Booking Form' );
    parent::__construct('CP_TimeSlotsBookingPlugin_Widget', 'WP Time Slots Booking Form', $widget_ops);
  }

  function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'itemid' => ''  ) );
    $title = $instance['title'];
    $itemid = $instance['itemid'];
    ?><p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label>
    <label for="<?php echo $this->get_field_id('itemid'); ?>">Form ID: <input class="widefat" id="<?php echo $this->get_field_id('itemid'); ?>" name="<?php echo $this->get_field_name('itemid'); ?>" type="text" value="<?php echo esc_attr($itemid); ?>" /></label>
    </p><?php
  }

  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    $instance['itemid'] = $new_instance['itemid'];
    return $instance;
  }

  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);

    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
    $itemid = $instance['itemid'];

    if (!empty($title))
      echo $before_title . $title . $after_title;;

    // WIDGET CODE GOES HERE
    //$multiview = new CP_TimeSlotsBookingPlugin;
    global $cp_tslotsb_plugin;

    if ($itemid != '')
        $cp_tslotsb_plugin->setId($itemid);

    $cp_tslotsb_plugin->insert_public_item();

    echo $after_widget;
  }

} // end widget class
  
?>