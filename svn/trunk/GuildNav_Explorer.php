<?php

class Guild_Explorer_Widget extends WP_Widget {
  /**
   * based on https://www.wpexplorer.com/create-widget-plugin-wordpress/

   * The widget simply creates a container that the Guild script will populate
   * after the page loads.
  */
  
  public function __construct() {
    parent::__construct(
      'guild_explorer_widget', 
      __('Guild Explorer Widget', 'text_domain'),
      array('customize_selective_refresh' => true)
    );
  }

  public function form( $instance ) {

  }

  public function update( $new_instance, $old_instance) {

  }

  public function widget( $args, $instance) {
    extract($args);
    echo $before_widget;
    echo '<div class="widget-text wp_widget_plugin_box widget-guild-explorer">';
    echo '</div>';
    echo $after_widget;
  }
}

function register_guild_widgets() {
  register_widget('Guild_Explorer_Widget');
}

add_action( 'widgets_init', 'register_guild_widgets');
