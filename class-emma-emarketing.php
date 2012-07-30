<?php


include_once('admin/class-settings.php');
include_once('widget/class-widget.php');
include_once('shortcode/class-shortcode.php');
include_once('class-emma-api.php');
include_once('class-form.php');

/**
 * Main Class for the Emma Emarketing Plugin
 *
 * long desc
 * @package Emma_Emarketing
 * @author ah so designs
 * @version 1.0
 * @abstract
 * @copyright 2012
 */
class Emma_Emarketing {


    /*
     * the constructor
	 * Fired during plugins_loaded (very very early),
	 * only actions and filters,
	 *
	 */
    function __construct() {

        new Settings();

        // Add shortcode support for widgets
        add_filter('widget_text', 'do_shortcode');


    }



} // end Class Emma_Emarketing


