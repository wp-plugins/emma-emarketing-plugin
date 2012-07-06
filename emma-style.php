<?php
/**
* Dynamic Styles for the Emma Emarketing Plugin
*
* Outputs a custom stylesheet for the plugin that only gets instantiated on the pages the widget or shortcode is used on,
* @package Emma_Emarketing
* @author ah so designs
* @version 1.0
* @abstract
* @copyright not yet
*/

// output dynamic form stylesheet
//http://css-tricks.com/css-variables-with-php/
//header("Content-type: text/css; charset: UTF-8");

$form_setup_settings = array();
$_form_setup_settings_key = 'emma_form_setup';
$_form_custom_settings_key = 'emma_form_custom';

$form_setup_settings = get_option( $_form_setup_settings_key );
$form_custom_settings = get_option( $_form_custom_settings_key );

?>
<style id="emma-emarketing" type="text/css" media="screen">
    /**
    * Emma Emarketing Plugin Stylesheet
    */

    /** Basics **/
    #emma-subscription-form { width: 100%; }
    ul#emma-form-elements { list-style-type: none; margin: 0; padding: 0; }
    ul#emma-form-elements li.emma-form-row { list-style-type: none; width: 90%; margin: 3px auto; height: 20px; display: block; }
    ul#emma-form-elements .emma-form-label { float: left; width: 27%; }
    ul#emma-form-elements .emma-form-input { float: right; width: 69%;}
    ul#emma-form-elements .emma-form-row-last { clear: both; }
    ul#emma-form-elements .emma-required { color: #C00; }
    ul#emma-form-elements #emma-form-submit { float: right; }
    ul#emma-form-elements .emma-form-label-required { width: 40%; }
    .emma-status-msg { width: 90%; margin: 0 auto; }
    .emma-error { width: 90%; margin: 0 auto; color: #C00; }


    #emma-form.x-small { width: 200px; }
    #emma-form.small { width: 280px; }
    #emma-form.medium { width: 300px; }
    #emma-form.large { width: 340px; }

    /** Customizable Elements **/
    ul#emma-form-elements .emma-form-input {
        border: <?php echo $form_custom_settings['border_width'] . 'px ' . $form_custom_settings['border_type'] . ' #' . $form_custom_settings['border_color']; ?>;
        color: #<?php echo $form_custom_settings['txt_color']; ?>;
        background-color: #<?php echo $form_custom_settings['bg_color']; ?>;
    }
    #emma-form input[type="submit"] {
        border: <?php echo $form_custom_settings['submit_border_width'] . 'px ' . $form_custom_settings['submit_border_type'] . ' #' . $form_custom_settings['submit_border_color']; ?>;
        color: #<?php echo $form_custom_settings['submit_txt_color']; ?>;
        background-color: #<?php echo $form_custom_settings['submit_bg_color']; ?>;
    }
    #emma-form input[type="submit"]:hover {
        border: <?php echo $form_custom_settings['submit_hover_border_width'] . 'px ' . $form_custom_settings['submit_hover_border_type'] . ' #' . $form_custom_settings['submit_hover_border_color']; ?>;
        color: #<?php echo $form_custom_settings['submit_hover_txt_color']; ?>;
        background-color: #<?php echo $form_custom_settings['submit_hover_bg_color']; ?>;
    }

    #emma-form.x-small ul#emma-form-elements .emma-form-input,
    #emma-form.x-small ul#emma-form-elements .emma-form-label { float: left; width: 97%; }

    /* status text */
    #emma-form .emma-status { width: 90%; margin: 0 auto; }

    /* powered by logo */
    #powered-by { padding: 0 20px; }
    #powered-by a { width: 130px; height: 40px; display: block; text-indent: -9999px; background: transparent url(<?php echo plugins_url( 'images/emma_powered_by_logo.png' , __FILE__ ) ?> ) no-repeat top left; }

</style>

<?php

?>
