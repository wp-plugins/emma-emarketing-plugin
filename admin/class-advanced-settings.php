<?php

class Advanced_Settings {

    public static $key = 'emma_advanced_settings';

    public static $settings = array();

    function __construct() {

        add_action( 'admin_init', array( &$this, 'register_settings' ) );

        self::$settings = $this->get_settings_options();
    }

    public static function get_settings_options() {

        // load the settings from the database
        $settings_options = (array) get_option( self::$key );

        // merge with defaults
        $settings_options = array_merge( self::get_settings_defaults(), $settings_options );

        return $settings_options;

    }

    public static function get_settings_defaults() {
        $defaults = array(
            'success_tracking_pixel' => '',
        );
        return $defaults;
    }

    function register_settings() {
        // register_setting( $option_group, $option_name, $sanitize_callback );
        register_setting( self::$key, self::$key, array( &$this, 'sanitize_advanced_settings' ) );

        add_settings_section( 'section_adv_settings', 'Emma Advanced Settings', array( &$this, 'section_adv_desc' ), self::$key );

        add_settings_field( 'successTrackingPixel', 'Tracking Pixel', array( &$this, 'field_success_tracking_pixel' ), self::$key, 'section_adv_settings' );
    }
    
    function section_adv_desc() { 
		echo '<p><strong>For advanced users only!</strong><br />Any code inserted in this area will be applied upon a successful form submission. This area is commonly can used for tracking pixels for Facebook or tracking for Google Analytics for pay per click campaigns.</p>';
	}
    function field_success_tracking_pixel() { ?>
        <textarea id="emma_success_tracking_pixel" style="width:400px;max-width: 100%;" rows="10" name="<?php echo self::$key; ?>[successTrackingPixel]"><?php echo esc_attr( self::$settings['successTrackingPixel'] ); ?></textarea>
    <?php }

    function sanitize_advanced_settings( $input ) {
		
        // get the current options
        // $valid_input = self::$settings;
        $valid_input = array();

        // check which button was clicked, submit or reset,
        $submit = ( ! empty( $input['submit'] ) ? true : false );
        $reset = ( ! empty( $input['reset'])  ? true : false );

        // if the submit or refresh button was clicked
        if ( $submit || $refresh ) {

            /**
             * validate advanced settings, and add error messages
             * add_settings_error( $setting, $code, $message, $type )
             * $setting here refers to the $id of add_settings_field
             * add_settings_field( $id, $title, $callback, $page, $section, $args );
             */
			
			// success tracking pixel
			$valid_input['successTrackingPixel'] = $input['successTrackingPixel'];

        } elseif ( $reset ) {

            // get defaults
            $default_input = $this->get_settings_defaults();
            // assign to valid input
            $valid_input = $default_input;

        }

        return $valid_input;

    } // end sanitize_advanced_settings


}
