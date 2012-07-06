<?php
/**
 * Widget Class for the Emma Emarketing Plugin
 *
 * long desc
 * @package Emma_Emarketing
 * @author ah so designs
 * @version 1.0
 * @abstract
 * @copyright not yet
 */

class Emma_Widget extends WP_Widget {

    private $_form_setup_settings_key = 'emma_form_setup';
    private $_form_custom_settings_key = 'emma_form_custom';

	// process the widget (the constructor)
	function __construct() {

        // get plugin options for form settings
        $this->form_setup_settings = (array) get_option( $this->_form_setup_settings_key );

        // setup widget ops
		$widget_ops = array(
			'classname' => 'emma-widget',
			'description' => 'Displays an email subscription form for Emma Emarketing'
		);
		
		$this->WP_Widget( 'emma-widget', 'Emma Emarketing Subscription Form', $widget_ops );
		
		// check to see if widget is being used
		if ( is_active_widget(false, false, $this->id_base) ) {
			
            // register / enqueue styles / scripts
            // wp_enqueue_style( $handle, $src, $deps, $ver, $media );
            // wp_enqueue_style( 'emma-form-style', plugins_url( 'emma-style.php', __FILE__ ), array(), '1.0', 'screen' );

            // for now, output the dynamic style sheet in the <head> directly
            // dangit. i RLY wanna use wp_enque, but get_option isn't avail.
            // should've trusted my gut and made the value object to house the settigns. fewer db queries too.
            add_action( 'wp_head', array( &$this, 'do_dynamic_styles' ) );

            // wp_register_script( $handle, $src, $deps, $ver, $in_footer );
			// wp_register_script( 'jquery-emma', plugins_url( '/js/jquery.emma.js', dirname(__FILE__) ), array( 'jquery' ), 1.0, TRUE );
			// wp_enqueue_script( 'jquery-emma' );

        } // end if
        
	} // end __construct

    function do_dynamic_styles() {
        include_once( 'emma-style.php' );
    }
	
	// displays the widget form in the admin dashboard, Appearance -> Widgets
	function form($instance) {
	
		$defaults = array(
			'title' => '',
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		
		$title = $instance['title'];
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wp-emem'); ?>:</label>
		<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" /></p>
		
	<?php } // end form
		
	// processes widget options to save
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;
		
		$instance['title'] = strip_tags( $new_instance['title'] );
		
		return $instance;
		
	} // end update
	
	// display the widget
	function widget($args, $instance) {
		
		extract($args);
		
		// generate widget markup
		echo $before_widget;
		
		// load up the widget settings
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		// check if there's a title, and display it.
		if (!empty($instance['title']))
			echo $before_title . apply_filters('widget_title', $title) . $after_title;

        // instantiate form class, pass in plugin settings
		$emma_form = new Emma_Form( $this->form_setup_settings );

        // hey crazy, if a function returns something, you'll need to echo it out.
        echo $emma_form->do_form();

		// end of widget output
		echo $after_widget;
		
	} // end widget

} // end class Emma_Widget

// use widgets_init action hook to execute custom function
add_action( 'widgets_init', 'emma_register_widgets' );

function emma_register_widgets() {
	register_widget( 'emma_widget' );
}
?>