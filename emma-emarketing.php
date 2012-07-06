<?php
/**
 * Plugin Name: Emma Emarketing Plugin
 * Plugin URI: http://ahsodeisgns.com/wordpress-plugins/emma-emarketing
 * Description: This Plugin has a Widget and a Shortcode that creates a subscription form for Emma, it also enables shortcode support for widget areas, just for fun.
 * Version: 1.0
 * Author: Ah SO Designs
 * Author URI: http://ahsodesigns.com
 * Contributors: ahsodesigns, emma
 * License: GPLv2
 *
 */

/*  Copyright 2012 Ah SO Designs  (email : info@ahsodesigns.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// setup proxy for sir charles the vase
// define('WP_PROXY_HOST','localhost');
// define('WP_PROXY_PORT','8888');

// include the widget and shortcode
include_once( 'emma-widget.php' );
include_once( 'emma-shortcode.php' );
// include the EMMA API class
include_once('emma-api.php');
// include the subscription form class
include_once('emma-form.php');

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
	 * For easier overriding we declared the keys
	 * here as well as our tabs array which is populated
	 * when registering settings
	 */

    private $_account_information_settings_key = 'emma_account_information';
    private $_form_setup_settings_key = 'emma_form_setup';
    private $_form_custom_settings_key = 'emma_form_custom';
    private $_help_settings_key = 'emma_help';

    private $_plugin_options_key = 'emma_plugin_options';
    private $_plugin_settings_tabs = array();

    /*
     * the constructor
	 * Fired during plugins_loaded (very very early),
	 * so don't miss-use this, only actions and filters,
	 *
	 */
    function __construct() {

        add_action( 'init', array( &$this, 'load_settings') );

        // Add admin stylesheet
        // add_action( 'admin_init', array( &$this, 'admin_stylesheet_init' ) );

        // Register settings
        add_action( 'admin_init', array( &$this, 'register_account_information_settings' ) );
        add_action( 'admin_init', array( &$this, 'register_form_setup_settings' ) );
        add_action( 'admin_init', array( &$this, 'register_form_custom_settings' ) );
        add_action( 'admin_init', array( &$this, 'register_help_settings') );

        // make sure the admin menu gets hooked up in the admin menu
        add_action( 'admin_menu', array( &$this, 'add_admin_menus' ) );

        // add action link
        add_filter( 'plugin_action_links', array( &$this, 'add_action_links' ), 10, 2 );

        // Add shortcode support for widgets
        add_filter('widget_text', 'do_shortcode');

        // Add activation hook
        register_activation_hook( __FILE__, array( &$this, 'register_emma_activation' ) );
    }

    function admin_stylesheet_init() {
        // Register our stylesheet.
        // wp_register_style( 'emma-form-styles', plugins_url('emma-style.php', __FILE__) );
        // wp_enqueue_style( 'emma-form-styles' );
    }

    /**
     * Add action links to installed plugins page
     * @param $links
     * @param $file
     * @return array
     */
    function add_action_links($links, $file) {
        static $this_plugin;
        if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
        if ($file == $this_plugin) {
            /**
             * The "page" query string value must be equal to the slug
             * of the Settings admin page we defined earlier,
             * the $_plugin_options_key property of this class which in
             * this case equals "emma_plugin_options".
             */
            $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . $this->_plugin_options_key . '">Settings</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }
    /**
	 * Loads both the account information, form setup,
     * and form customization settings from
	 * the database into their respective arrays. Uses
	 * array_merge to merge with default values if they're
	 * missing.
	 */
    function load_settings() {

        $this->account_information_settings = (array) get_option( $this->_account_information_settings_key );
        $this->form_setup_settings = (array) get_option( $this->_form_setup_settings_key );
        $this->form_custom_settings = (array) get_option( $this->_form_custom_settings_key );

        // merge with defaults
        $this->account_information_settings = array_merge( array(
            'plugin_version' => '1.0',
            'account_id' => '',
            'publicAPIkey' => '',
            'privateAPIkey' => '',
            'logged_in' => 'false',
            'groups' => array(),
            'group_name' => '',
            'group_ids' => '',
        ), $this->account_information_settings );

        $this->form_setup_settings = array_merge( array(
            'include_firstname_lastname' => '1',
            'form_size' => 'medium',
            'email_placeholder' => '',
            'firstname_placeholder' => '',
            'lastname_placeholder' => '',
            'submit_txt' => 'Subscribe',
            'confirmation_msg' => 'Thanks for subscribing! you should receive a confirmation email shortly. Please check your spam folder, as occasionally this email may be perceived as spam',
            'powered_by' => 'no'
        ), $this->form_setup_settings );

        $this->form_custom_settings = array_merge( array(
            'border_width' => '1',
            'border_color' => '000',
            'border_type' => 'solid',
            'txt_color' => '000',
            'bg_color' => 'FFF',
            'submit_txt_color' => 'FFF',
            'submit_bg_color' => '000',
            'submit_border_width' => '1',
            'submit_border_color' => '555',
            'submit_border_type' => 'solid',
            'submit_hover_txt_color' => '000',
            'submit_hover_border_width' => '1',
            'submit_hover_bg_color' => 'FFF',
            'submit_hover_border_color' => '555',
            'submit_hover_border_type' => 'solid'
        ), $this->form_custom_settings );
    }

    /**
     * ACCOUNT INFORMATION SETTINGS
	 * Registers the account information settings via the Settings API,
	 * appends the setting to the tabs array of the object.
	 */
    function register_account_information_settings() {

        $this->_plugin_settings_tabs[$this->_account_information_settings_key] = 'Account Information';

        // register_setting( $option_group, $option_name, $sanitize_callback );
        register_setting( $this->_account_information_settings_key, $this->_account_information_settings_key, array( &$this, 'sanitize_account_information_settings' ) );

        add_settings_section( 'section_login', 'Emma Account Login Information', array( &$this, 'section_login_desc' ), $this->_account_information_settings_key );

        add_settings_field( 'public_api_key', 'Public API Key', array( &$this, 'field_public_api_key' ), $this->_account_information_settings_key, 'section_login' );
        add_settings_field( 'private_api_key', 'Private API Key', array( &$this, 'field_private_api_key' ), $this->_account_information_settings_key, 'section_login' );
        add_settings_field( 'account_id', 'Account Number', array( &$this, 'field_account_id' ), $this->_account_information_settings_key, 'section_login' );

        add_settings_section( 'section_groups', 'Add New Members to Group', array( &$this, 'section_groups_desc' ), $this->_account_information_settings_key );

        add_settings_field( 'logged_in', '', array( &$this, 'field_logged_in' ), $this->_account_information_settings_key, 'section_groups' );

        // check to see if logged in to emma
        if ( $this->account_information_settings['logged_in'] == 'true' ) {

            add_settings_field( 'groups', 'Select Group', array( &$this, 'field_groups' ), $this->_account_information_settings_key, 'section_groups' );

        }

    }

    function section_login_desc() { echo 'You must have an Emma Account with a API key. If you&apos;re unsure if your account is on the new API, contact Emma'; }
    function field_account_id() { ?>
        <input id="emma_account_id"
            type="text"
            size="7"
            name="<?php echo $this->_account_information_settings_key; ?>[account_id]"
            value="<?php echo esc_attr( $this->account_information_settings['account_id'] ); ?>"
        />
    <?php }
    function field_public_api_key() { ?>
        <input id="emma_publicapikey"
            type="text"
            size="20"
            name="<?php echo $this->_account_information_settings_key; ?>[publicAPIkey]"
            value="<?php echo esc_attr( $this->account_information_settings['publicAPIkey'] ); ?>"
        />
    <?php }
    function field_private_api_key() { ?>
        <input id="emma_privateapikey"
            type="text"
            size="20"
            name="<?php echo $this->_account_information_settings_key ?>[privateAPIkey]"
            value="<?php echo esc_attr( $this->account_information_settings['privateAPIkey'] ); ?>"
        />
    <?php }

    function section_groups_desc() {

        if ( $this->account_information_settings['logged_in'] == 'true' ) {
            echo 'Assign members to groups ( optional )';
        } else {
            echo 'Once you&apos;ve entered your account information and saved the changes, then you can choose from the available groups to assign new members to';
        }
    }

    function field_logged_in() { ?>
        <input id="emma_logged_in"
            type="hidden"
            name="<?php echo $this->_account_information_settings_key ?>[logged_in]"
            value="<?php echo esc_attr( $this->account_information_settings['logged_in'] ); ?>"
        />
    <?php }

    function field_groups() {

        $groups = $this->account_information_settings['groups'];

        // groups dropdown
        echo '<select id="emma_groups" name="' . $this->_account_information_settings_key . '[group_name]">';
        echo '<option value=""> - select a group - </option>';
        foreach ( $groups as $group ) {
            echo '<option value="' . $group . '"';
            if ( $this->account_information_settings['group_name'] == $group ) { echo "selected"; }
            echo '>' . $group . '</option>';
        }
        echo '</select>';
        // refresh button
        echo '<input style="margin-left: 20px;" type="submit" name="emma_account_information[refresh]" id="refresh" class="button-secondary" value="Refresh" />';
    }

    function sanitize_account_information_settings( $input ) {

        // get the current options
        // $valid_input = $this->account_information_settings;
        $valid_input = array();

        // check which button was clicked, submit or reset,
        $submit = ( ! empty( $input['submit'] ) ? true : false );
        $reset = ( ! empty( $input['reset'])  ? true : false );
        $refresh = ( ! empty( $input['refresh']) ? true : false );

        // if the submit or refresh button was clicked
        if ( $submit || $refresh ) {

            /**
             * validate the account information settings, and add error messages
             * add_settings_error( $setting, $code, $message, $type )
             * $setting here refers to the $id of add_settings_field
             * add_settings_field( $id, $title, $callback, $page, $section, $args );
             */

            // account number

            // check if it's a number
            $valid_input['account_id'] = ( is_numeric($input['account_id']) ? $input['account_id'] : $valid_input['account_id'] );
            if ( $valid_input['account_id'] != $input['account_id'] ) {
                add_settings_error(
                    'account_id',
                    'emma_error',
                    'The Account Number can only contain numbers, no letters or alpha-numeric characters',
                    'error'
                );
            };

            // public API key
            if ( ( strlen($input['publicAPIkey']) == 20 ) && ( ctype_alnum($input['publicAPIkey']) ) ) {
                $valid_input['publicAPIkey'] = $input['publicAPIkey'];
            } else {
                add_settings_error(
                    'public_api_key',
                    'emma_error',
                    'The Public API Key can only contain letters and numbers, and should be 20 characters long',
                    'error'
                );
            }

            // private API key
            // make sure it's only 20 characters and contains only upper / lowercase letters and numbers
//            if ( preg_match('/[0-9a-zA-Z]{20}/', $input['privateAPIkey']) ) {
            if ( ( strlen($input['privateAPIkey']) == 20 ) && ( ctype_alnum($input['privateAPIkey']) ) ) {
                $valid_input['privateAPIkey'] = $input['privateAPIkey'];
            } else {
                add_settings_error(
                    'private_api_key',
                    'emma_error',
                    'The Private API Key can only contain letters and numbers, and should be 20 characters long',
                    'error'
                );
            }

            // get group data
            // check if not logged in, or just refreshing groups
            if ( ( $input['logged_in'] == 'false') || $refresh ) {

                // instantiate a new Emma API class, pass login / auth data to it
                $emma_api = new Emma_API( $valid_input['account_id'], $valid_input['publicAPIkey'], $valid_input['privateAPIkey']);

                // get the groups for this account
                $groups = $emma_api->list_groups();

                // check if groups returned an error, or an answer
                if ( is_array($groups) ) {

                    // if it returns an array, it's got groups from hooking up w/ emma
                    $valid_input['logged_in'] = 'true';

                    // pass the array of group names into the settings
                    $valid_input['groups'] = $groups;

                    // pass the group name in as the active group
                    $valid_input['group_name'] = $input['group_name'];

                    // assign the group id of the active group name
                    // if there is a group name, assign the group id based on the group name
                    $valid_input['group_ids'] = array_search( $input['group_name'], $this->account_information_settings['groups'] );

                } else {

                    // not logged in...
                    $valid_input['logged_in'] = 'false';

                    // pass thru previous info
                    $valid_input['group_name'] = $input['group_name'];

                    // the method returns a string / error message otherwise
                    add_settings_error(
                        'account_id',
                        'emma_error',
                        $groups,
                        'error'
                    );

                }

            } // endif logged_in == false

            if ( $input['logged_in'] == 'true' ) {

                // pass thru groups and group information
                $valid_input['groups'] = $this->account_information_settings['groups'];
                $valid_input['group_ids'] = array_search( $input['group_name'], $this->account_information_settings['groups'] );
                $valid_input['group_name'] = $input['group_name'];
                $valid_input['logged_in'] = $input['logged_in'];

            }

        } elseif ( $reset ) {

            // establish defaults
            $default_options = array(
                'account_id' => '',
                'privateAPIkey' => '',
                'publicAPIkey' => '',
                'logged_in' => 'false',
                'groups' => array(),
                'group_name' => '',
                'group_ids' => '',
            );

            // assign defaults
            $valid_input['account_id'] = $default_options['account_id'];
            $valid_input['privateAPIkey'] = $default_options['privateAPIkey'];
            $valid_input['publicAPIkey'] = $default_options['publicAPIkey'];
            $valid_input['logged_in'] = $default_options['logged_in'];
            $valid_input['groups'] = $default_options['groups'];
            $valid_input['group_name'] = $default_options['group_name'];
            $valid_input['group_ids'] = $default_options['group_ids'];

        }

        return $valid_input;

    } // end sanitize_account_information_settings

    /**
     * FORM SETUP SETTINGS
     * Registers the form setup settings via the Settings API,
     * appends the setting to the tabs array of the object.
     */
    function register_form_setup_settings() {

        $this->_plugin_settings_tabs[$this->_form_setup_settings_key] = 'Form Setup';

        register_setting( $this->_form_setup_settings_key, $this->_form_setup_settings_key, array( &$this, 'sanitize_form_setup_settings' ) );

        add_settings_section( 'section_form_field_includes', 'Forms Fields', array( &$this, 'section_form_field_includes_desc' ), $this->_form_setup_settings_key );

        add_settings_field( 'include_firstname_lastname', 'Include First and Last Name Fields', array( &$this, 'field_include_firstname_lastname' ), $this->_form_setup_settings_key, 'section_form_field_includes' );

        add_settings_section( 'section_form_size', 'Select Form Size', array( &$this, 'section_form_size_desc' ), $this->_form_setup_settings_key );

        add_settings_field( 'form_size', 'Form Size', array( &$this, 'field_form_size' ), $this->_form_setup_settings_key, 'section_form_size' );

        add_settings_section( 'section_form_placeholders', 'Form Placeholders', array( &$this, 'section_form_placeholders_desc' ), $this->_form_setup_settings_key );

        add_settings_field( 'email_placeholder', 'Email Placeholder', array( &$this, 'field_email_placeholder' ), $this->_form_setup_settings_key, 'section_form_placeholders' );
        add_settings_field( 'firstname_placeholder', 'First Name Placeholder', array( &$this, 'field_firstname_placeholder' ), $this->_form_setup_settings_key, 'section_form_placeholders' );
        add_settings_field( 'lastname_placeholder', 'Last Name Placeholder', array( &$this, 'field_lastname_placeholder' ), $this->_form_setup_settings_key, 'section_form_placeholders' );
        add_settings_field( 'submit_button_text', 'Submit Button Text', array( &$this, 'field_submit_txt' ), $this->_form_setup_settings_key, 'section_form_placeholders' );
        add_settings_field( 'confirmation_msg', 'Confirmation Message', array( &$this, 'field_confirmation_msg' ), $this->_form_setup_settings_key, 'section_form_placeholders' );

        add_settings_section( 'section_powered_by', 'Give Props', array( &$this, 'section_powered_by_desc'), $this->_form_setup_settings_key );
        add_settings_field( 'powered_by', 'Add "Powered By Emma" Link', array( &$this, 'field_powered_by'), $this->_form_setup_settings_key, 'section_powered_by' );

        // form preview
        // add_settings_field( 'form_preview', 'Form Preview', array( &$this, 'field_form_preview' ), $this->_form_setup_settings_key, 'section_form_placeholders' );

    }

    function section_form_field_includes_desc() {  }
    function field_include_firstname_lastname() { ?>
        <label for="include_firstname_lastname_yes">Yes</label>
        <input id="include_firstname_lastname_yes"
               type="radio"
               name="<?php echo $this->_form_setup_settings_key; ?>[include_firstname_lastname]"
               value="1" <?php checked( '1', ( $this->form_setup_settings['include_firstname_lastname'] ) ); ?>
        />
        <label for="include_firstname_lastname_no">No</label>
        <input id="include_firstname_lastname_no"
               type="radio"
               name="<?php echo $this->_form_setup_settings_key; ?>[include_firstname_lastname]"
               value="0" <?php checked( '0', ( $this->form_setup_settings['include_firstname_lastname'] ) ); ?>
        />
    <?php }

    function section_form_size_desc() {  }
    function field_form_size() { ?>
        <input id="form_size_x_small"
               type="radio"
               name="<?php echo $this->_form_setup_settings_key; ?>[form_size]"
               value="x-small" <?php checked( 'x-small', ( $this->form_setup_settings['form_size'] ) ); ?>
        />
        <label for="form_size_x_small">Extra Small ( 200px )</label>
        <br />
        <input id="form_size_small"
               type="radio"
               name="<?php echo $this->_form_setup_settings_key; ?>[form_size]"
               value="small" <?php checked( 'small', ( $this->form_setup_settings['form_size'] ) ); ?>
        />
        <label for="form_size_small">Small ( 280px )</label>
        <br />
        <input id="form_size_medium"
               type="radio"
               name="<?php echo $this->_form_setup_settings_key; ?>[form_size]"
               value="medium" <?php checked( 'medium', ( $this->form_setup_settings['form_size'] ) ); ?>
        />
        <label for="form_size_medium">Medium ( 300px )</label>
        <br />
        <input id="form_size_large"
               type="radio"
               name="<?php echo $this->_form_setup_settings_key; ?>[form_size]"
               value="large" <?php checked( 'large', ( $this->form_setup_settings['form_size'] ) ); ?>
        />
        <label for="form_size_large">Large ( 340px )</label>
    <?php }

    function section_form_placeholders_desc() {  }
    function field_email_placeholder() { ?>
        <input id="emma_email_placeholder"
            type="text"
            size="40"
            name="<?php echo $this->_form_setup_settings_key; ?>[email_placeholder]"
            value="<?php echo esc_attr( $this->form_setup_settings['email_placeholder'] ); ?>"
        />
    <?php }

    function field_firstname_placeholder() {?>
        <input id="emma_firstname_placeholder"
            type="text"
            size="40"
            name="<?php echo $this->_form_setup_settings_key; ?>[firstname_placeholder]"
            value="<?php echo esc_attr( $this->form_setup_settings['firstname_placeholder'] ); ?>"
        />
    <?php }

    function field_lastname_placeholder() { ?>
        <input id="emma_lastname_placeholder"
            type="text"
            size="40"
            name="<?php echo $this->_form_setup_settings_key; ?>[lastname_placeholder]"
            value="<?php echo esc_attr( $this->form_setup_settings['lastname_placeholder'] ); ?>"
        />
    <? }

    function field_submit_txt() { ?>
        <input id="emma_submit_txt"
            type="text"
            size="40"
            name="<?php echo $this->_form_setup_settings_key; ?>[submit_txt]"
            value="<?php echo esc_attr( $this->form_setup_settings['submit_txt'] ); ?>"
        />
    <?php }

    function field_confirmation_msg() { ?>
        <textarea id="emma_confirmation_msg"
            name="<?php echo $this->_form_setup_settings_key; ?>[confirmation_msg]"
            rows="6"
            cols="40" >
            <?php
                // avoid undefined index by checking for the value 1st, then assigning it nothing if it has not been set.
                $confirmation_msg = isset( $this->form_setup_settings['confirmation_msg'] ) ? esc_attr( $this->form_setup_settings['confirmation_msg'] ) : '';
                echo $confirmation_msg;
            ?>
        </textarea>
        <?php
            // someday we're gonna use the native wp_editor, and let them dump html in thar...
            //$args = array("textarea_name" => "emma_options[confirmation_msg]");
            //wp_editor( $options['confirmation_msg'], "emma_options[confirmation_msg]", $args );
    }

    // Powered By
    function section_powered_by_desc() {
        echo 'Would you like to add a stylish "Powered by Emma" logo and link to your form? It would really help us out!';
    }

    function field_powered_by() { ?>
        <label for="powered_by_yes">Yes</label>
        <input id="powered_by_yes"
               type="radio"
               name="<?php echo $this->_form_setup_settings_key; ?>[powered_by]"
               value="yes" <?php checked( 'yes', ( $this->form_setup_settings['powered_by'] ) ); ?>
        />
        <label for="powered_by_no">No</label>
        <input id="powered_by_no"
               type="radio"
               name="<?php echo $this->_form_setup_settings_key; ?>[powered_by]"
               value="no" <?php checked( 'no', ( $this->form_setup_settings['powered_by'] ) ); ?>
        />
    <?php }

    // Form preview section
    // for version 2.0,
    function field_form_preview() {
        echo '<div style="position: fixed; top: 130px; right: 50px;">';
        $preview_form = new Emma_Form( $this->form_setup_settings );
        echo $preview_form->do_form();
        echo '</div>';
    }

    function sanitize_form_setup_settings( $input ) {

        $valid_input = array();

        // check which button was clicked, submit or reset,
        $submit = ( ! empty( $input['submit'] ) ? true : false );
        $reset = ( ! empty( $input['reset'])  ? true : false );

       if ( $submit ) {

           // text inputs
           $valid_input['include_firstname_lastname'] = $input['include_firstname_lastname'];
           $valid_input['form_size'] = $input['form_size'];
           $valid_input['email_placeholder'] = wp_kses( $input['email_placeholder'], '' );
           $valid_input['firstname_placeholder'] = wp_kses( $input['firstname_placeholder'], '' );
           $valid_input['lastname_placeholder'] = wp_kses( $input['lastname_placeholder'], '' );
           $valid_input['submit_txt'] = wp_kses( $input['submit_txt'], '' );
           $valid_input['confirmation_msg'] = wp_kses( $input['confirmation_msg'], '' );
           $valid_input['powered_by'] = $input['powered_by'];

       } elseif ( $reset ) {

           $default_options = array(
               'include_firstname_lastname' => '1',
               'form_size' => 'medium',
               'email_placeholder' => '',
               'firstname_placeholder' => '',
               'lastname_placeholder' => '',
               'submit_txt' => 'Subscribe',
               'confirmation_msg' => 'Thanks for subscribing! you should receive a confirmation email shortly. Please check your spam folder, as occasionally this email may be perceived as spam',
               'powered_by' => 'no'
           );

           $valid_input['include_firstname_lastname'] = $default_options['include_firstname_lastname'];
           $valid_input['form_size'] = $default_options['form_size'];
           $valid_input['email_placeholder'] = $default_options['email_placeholder'];
           $valid_input['firstname_placeholder'] = $default_options['firstname_placeholder'];
           $valid_input['lastname_placeholder'] = $default_options['lastname_placeholder'];
           $valid_input['submit_txt'] = $default_options['submit_txt'];
           $valid_input['confirmation_msg'] = $default_options['confirmation_msg'];
           $valid_input['powered_by'] = $default_options['powered_by'];

       }

        return $valid_input;

    }

    /*
      * registers the form customization settings via the settings api,
      * appends the setting to the tabs array of the object.
      */
    function register_form_custom_settings() {

        $this->_plugin_settings_tabs[$this->_form_custom_settings_key] = 'Form Customization';

        // register_setting( $option_group, $option_name, $sanitize_callback );
        register_setting( $this->_form_custom_settings_key, $this->_form_custom_settings_key, array( &$this, 'sanitize_form_custom_settings') );

        // add_settings_section( $id, $title, $callback, $page );

        add_settings_section( 'section_form_fields_custom', 'Form Fields customization', array( &$this, 'section_form_fields_custom_desc' ), $this->_form_custom_settings_key );

        add_settings_field( 'border_width', 'Border Width', array( &$this, 'field_border_width' ), $this->_form_custom_settings_key, 'section_form_fields_custom' );
        add_settings_field( 'border_color', 'Border Color', array( &$this, 'field_border_color' ), $this->_form_custom_settings_key, 'section_form_fields_custom' );
        add_settings_field( 'border_type', 'Border Type', array( &$this, 'field_border_type' ), $this->_form_custom_settings_key, 'section_form_fields_custom' );
        add_settings_field( 'txt_color', 'Text Color', array( &$this, 'field_txt_color' ), $this->_form_custom_settings_key, 'section_form_fields_custom' );
        add_settings_field( 'bg_color', 'Background Color', array( &$this, 'field_bg_color' ), $this->_form_custom_settings_key, 'section_form_fields_custom' );

        add_settings_section( 'section_submit_custom', 'Submit button customization', array( &$this, 'section_submit_desc' ), $this->_form_custom_settings_key );

        add_settings_field( 'submit_txt_color', 'Submit Button Text Color', array( &$this, 'field_submit_txt_color' ), $this->_form_custom_settings_key, 'section_submit_custom' );
        add_settings_field( 'submit_bg_color', 'Submit Button Background Color', array( &$this, 'field_submit_bg_color' ), $this->_form_custom_settings_key, 'section_submit_custom' );

        add_settings_field( 'submit_border_width', 'Submit Button Border Width', array( &$this, 'field_submit_border_width' ), $this->_form_custom_settings_key, 'section_submit_custom' );
        add_settings_field( 'submit_border_color', 'Submit Button Border Color', array( &$this, 'field_submit_border_color' ), $this->_form_custom_settings_key, 'section_submit_custom' );
        add_settings_field( 'submit_border_type', 'Submit Button Border Type', array( &$this, 'field_submit_border_type' ), $this->_form_custom_settings_key, 'section_submit_custom' );

        add_settings_section( 'section_submit_hover_custom', 'Submit button hover state customization', array( &$this, 'section_submit_hover_desc' ), $this->_form_custom_settings_key );

        add_settings_field( 'submit_hover_txt_color', 'Submit Button Hover Text Color', array( &$this, 'field_submit_hover_txt_color' ), $this->_form_custom_settings_key, 'section_submit_hover_custom' );
        add_settings_field( 'submit_hover_bg_color', 'Submit Button Hover Background Color', array( &$this, 'field_submit_hover_bg_color' ), $this->_form_custom_settings_key, 'section_submit_hover_custom' );

        add_settings_field( 'submit_hover_border_width', 'Submit Button Hover Border Width', array( &$this, 'field_submit_hover_border_width' ), $this->_form_custom_settings_key, 'section_submit_hover_custom' );
        add_settings_field( 'submit_hover_border_color', 'Submit Button Hover Border Color', array( &$this, 'field_submit_hover_border_color' ), $this->_form_custom_settings_key, 'section_submit_hover_custom' );
        add_settings_field( 'submit_hover_border_type', 'Submit Button Hover Border Type', array( &$this, 'field_submit_hover_border_type' ), $this->_form_custom_settings_key, 'section_submit_hover_custom' );

    }

    function section_form_fields_custom_desc() {  }

    function field_border_width() { ?>
        <input id="emma_border_width"
            type="text"
            size="2"
            name="<?php echo $this->_form_custom_settings_key; ?>[border_width]"
            value="<?php echo esc_attr( $this->form_custom_settings['border_width'] ); ?>"
        /> px (enter 0 for no border.)
    <?php }

    function field_border_color() { ?>
        # <input id="emma_border_color"
            type="text"
            size="6"
            name="<?php echo $this->_form_custom_settings_key; ?>[border_color]"
            value="<?php echo esc_attr( $this->form_custom_settings['border_color'] ); ?>"
        />
    <?php }

    function field_border_type() {
        $border_types = array( 'none', 'dashed', 'dotted', 'double', 'groove', 'inset', 'outset', 'ridge', 'solid' );
        echo '<select id="emma_border_type" name="' . $this->_form_custom_settings_key . '[border_type]">';
        foreach ( $border_types as $border_type ) {
            echo '<option value="' . $border_type . '"';
            if ( $this->form_custom_settings['border_type'] == $border_type ) { echo "selected"; }
            echo '>'; echo $border_type . '</option>';
        }
        echo '</select>';
    }

    function field_txt_color() { ?>
        # <input id="emma_txt_color"
            type="text"
            size="6"
            name="<?php echo $this->_form_custom_settings_key; ?>[txt_color]"
            value="<?php echo esc_attr( $this->form_custom_settings['txt_color'] ); ?>"
        />
    <?php }
    function field_bg_color() { ?>
        # <input id="emma_bg_color"
            type="text"
            size="6"
            name="<?php echo $this->_form_custom_settings_key; ?>[bg_color]"
            value="<?php echo esc_attr( $this->form_custom_settings['bg_color'] ); ?>"
        />
    <?php }

    function section_submit_desc() {  }

    function field_submit_txt_color() { ?>
        # <input id="emma_submit_txt_color"
            type="text"
            size="6"
            name="<?php echo $this->_form_custom_settings_key; ?>[submit_txt_color]"
            value="<?php echo esc_attr( $this->form_custom_settings['submit_txt_color'] ); ?>"
        />
    <?php }

    function field_submit_bg_color() { ?>
        # <input id="emma_submit_bg_color"
            type="text"
            size="6"
            name="<?php echo $this->_form_custom_settings_key; ?>[submit_bg_color]"
            value="<?php echo esc_attr( $this->form_custom_settings['submit_bg_color'] ); ?>"
        />
    <?php }

    function field_submit_border_width() { ?>
        <input id="emma_submit_border_width"
            type="text"
            size="2"
            name="<?php echo $this->_form_custom_settings_key; ?>[submit_border_width]"
            value="<?php echo esc_attr( $this->form_custom_settings['submit_border_width'] ); ?>"
        /> px (enter 0 for no border.)
    <?php }

    function field_submit_border_color() { ?>
        # <input id="emma_submit_border_color"
            type="text"
            size="6"
            name="<?php echo $this->_form_custom_settings_key; ?>[submit_border_color]"
            value="<?php echo esc_attr( $this->form_custom_settings['submit_border_color'] ); ?>"
        />
    <?php }

    function field_submit_border_type() {
        $border_types = array( 'none', 'dashed', 'dotted', 'double', 'groove', 'inset', 'outset', 'ridge', 'solid' );
        echo '<select name="' . (string)$this->_form_custom_settings_key . '[submit_border_type]">';
        foreach ( $border_types as $border_type ) {
            echo '<option value="' . $border_type . '"';
            if ( $this->form_custom_settings['submit_border_type'] == $border_type ) { echo "selected"; }
            echo '>';
            echo $border_type . '</option>';
        }
        echo '</select>';
    }

    function section_submit_hover_desc() {  }

    function field_submit_hover_txt_color() { ?>
        # <input id="emma_submit_hover_text"
            type="text"
            size="6"
            name="<?php echo $this->_form_custom_settings_key; ?>[submit_hover_txt_color]"
            value="<?php echo esc_attr( $this->form_custom_settings['submit_hover_txt_color'] ); ?>"
        />
    <?php }

    function field_submit_hover_bg_color() { ?>
        # <input id="emma_submit_hover_bg_color"
            type="text"
            size="6"
            name="<?php echo $this->_form_custom_settings_key; ?>[submit_hover_bg_color]"
            value="<?php echo esc_attr( $this->form_custom_settings['submit_hover_bg_color'] ); ?>"
        />
    <?php }

    function field_submit_hover_border_width() { ?>
        <input id="emma_submit_hover_border_width"
            type="text"
            size="2"
            name="<?php echo $this->_form_custom_settings_key; ?>[submit_hover_border_width]"
            value="<?php echo esc_attr( $this->form_custom_settings['submit_hover_border_width'] ); ?>"
        /> px (enter 0 for no border.)
    <?php }

    function field_submit_hover_border_color() { ?>
        # <input id="emma_submit_hover_boder_color"
            type="text"
            size="6"
            name="<?php echo $this->_form_custom_settings_key; ?>[submit_hover_border_color]"
            value="<?php echo esc_attr( $this->form_custom_settings['submit_hover_border_color'] ); ?>"
        />
    <?php }

    function field_submit_hover_border_type() {
        $border_types = array( 'none', 'dashed', 'dotted', 'double', 'groove', 'inset', 'outset', 'ridge', 'solid' );
        echo '<select name="' . (string)$this->_form_custom_settings_key . '[submit_hover_border_type]">';
        foreach ( $border_types as $border_type ) {
            echo '<option value="' . $border_type . '"';
            if ( $this->form_custom_settings['submit_hover_border_type'] == $border_type ) { echo "selected"; }
            echo '>';
            echo $border_type . '</option>';
        }
        echo '</select>';
    }

    function sanitize_form_custom_settings( $input ) {

        $valid_input = array();

        // check which button was clicked, submit or reset,
        $submit = ( ! empty( $input['submit'] ) ? true : false );
        $reset = ( ! empty( $input['reset'])  ? true : false );

        if ( $submit ) {

            // check all hexadecimal values
            // not checking for a true hex value, not capturing '#'
            // border_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['border_color']) ) {
                $valid_input['border_color'] = $input['border_color'];
            } else {
                add_settings_error(
                    'border_color',
                    'emma_error',
                    'The form fields border color is an invalid hexadecimal value',
                    'error'
                );
            }
            // txt_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['txt_color']) ) {
                $valid_input['txt_color'] = $input['txt_color'];
            } else {
                add_settings_error(
                    'txt_color',
                    'emma_error',
                    'The form fields text color is an invalid hexadecimal value',
                    'error'
                );
            }
            // bg_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['bg_color']) ) {
                $valid_input['bg_color'] = $input['bg_color'];
            } else {
                add_settings_error(
                    'bg_color',
                    'emma_error',
                    'The form fields background color is an invalid hexadecimal value',
                    'error'
                );
            }
            // submit_txt_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['submit_txt_color']) ) {
                $valid_input['submit_txt_color'] = $input['submit_txt_color'];
            } else {
                add_settings_error(
                    'submit_txt_color',
                    'emma_error',
                    'The submit button text color is an invalid hexadecimal value',
                    'error'
                );
            }
            // submit_bg_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['submit_bg_color']) ) {
                $valid_input['submit_bg_color'] = $input['submit_bg_color'];
            } else {
                add_settings_error(
                    'submit_bg_color',
                    'emma_error',
                    'The submit button background color is an invalid hexadecimal value',
                    'error'
                );
            }
            // submit_border_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['submit_border_color']) ) {
                $valid_input['submit_border_color'] = $input['submit_border_color'];
            } else {
                add_settings_error(
                    'submit_border_color',
                    'emma_error',
                    'The submit border color is an invalid hexadecimal value',
                    'error'
                );
            }
            // submit_hover_txt_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['submit_hover_txt_color']) ) {
                $valid_input['submit_hover_txt_color'] = $input['submit_hover_txt_color'];
            } else {
                add_settings_error(
                    'submit_hover_txt_color',
                    'emma_error',
                    'The submit hover text color is an invalid hexadecimal value',
                    'error'
                );
            }
            // submit_hover_bg_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['submit_hover_bg_color']) ) {
                $valid_input['submit_hover_bg_color'] = $input['submit_hover_bg_color'];
            } else {
                add_settings_error(
                    'submit_hover_bg_color',
                    'emma_error',
                    'The submit hover background color is an invalid hexadecimal value',
                    'error'
                );
            }
            // submit_hover_border_color
            if ( preg_match('/[a-fA-F0-9]{3,6}/', $input['submit_hover_border_color']) ) {
                $valid_input['submit_hover_border_color'] = $input['submit_hover_border_color'];
            } else {
                add_settings_error(
                    'submit_hover_border_color',
                    'emma_error',
                    'The submit hover border color is an invalid hexadecimal value',
                    'error'
                );
            }

            // validate pixel values,
            $valid_input['border_width'] = (is_numeric($input['border_width']) ? $input['border_width'] : $valid_input['border_width']);
            $valid_input['submit_border_width'] = (is_numeric($input['submit_border_width']) ? $input['submit_border_width'] : $valid_input['submit_border_width']);
            $valid_input['submit_hover_border_width'] = (is_numeric($input['submit_hover_border_width']) ? $input['submit_hover_border_width'] : $valid_input['submit_hover_border_width']);

            // validate select elements, border types
            $valid_input['border_type'] = $input['border_type'];
            $valid_input['submit_border_type'] = $input['submit_border_type'];
            $valid_input['submit_hover_border_type'] = $input['submit_hover_border_type'];

        } elseif ( $reset ) {

            $default_options = array(
                'border_width' => '1',
                'border_color' => '000',
                'border_type' => 'solid',
                'txt_color' => '000',
                'bg_color' => 'FFF',
                'submit_txt_color' => '000',
                'submit_bg_color' => 'FFF',
                'submit_border_width' => '1',
                'submit_border_color' => '000',
                'submit_border_type' => 'solid',
                'submit_hover_txt_color' => 'FFF',
                'submit_hover_bg_color' => '000',
                'submit_hover_border_width' => '1',
                'submit_hover_border_color' => '888',
                'submit_hover_border_type' => 'solid'

            );

            $valid_input['border_width'] = $default_options['border_width'];
            $valid_input['border_color'] = $default_options['border_color'];
            $valid_input['border_type'] = $default_options['border_type'];
            $valid_input['txt_color'] = $default_options['txt_color'];
            $valid_input['bg_color'] = $default_options['bg_color'];
            $valid_input['submit_txt_color'] = $default_options['submit_txt_color'];
            $valid_input['submit_bg_color'] = $default_options['submit_bg_color'];
            $valid_input['submit_border_width'] = $default_options['submit_border_width'];
            $valid_input['submit_border_color'] = $default_options['submit_border_color'];
            $valid_input['submit_border_type'] = $default_options['submit_border_type'];
            $valid_input['submit_hover_txt_color'] = $default_options['submit_hover_txt_color'];
            $valid_input['submit_hover_bg_color'] = $default_options['submit_hover_bg_color'];
            $valid_input['submit_hover_border_width'] = $default_options['submit_hover_border_width'];
            $valid_input['submit_hover_border_color'] = $default_options['submit_hover_border_color'];
            $valid_input['submit_hover_border_type'] = $default_options['submit_hover_border_type'];

        }

        return $valid_input;

    }
    /**
     * HELP SETTINGS
     * Registers the help settings via the Settings API,
     * appends the setting to the tabs array of the object.
     */
    function register_help_settings() {

        $this->_plugin_settings_tabs[$this->_help_settings_key] = 'Help';

        // register_setting( $option_group, $option_name, $sanitize_callback );
        register_setting( $this->_help_settings_key, $this->_help_settings_key, array( &$this, 'sanitize_help_settings' ) );

        add_settings_section( 'section_help', 'Help and Setup Information', array( &$this, 'section_help_desc' ), $this->_help_settings_key );

    }

    function section_help_desc() { ?>

        <a href="http://myemma.com/login/" target="_blank">Login to the Emma Dashboard</a>

        <h3>ACCOUNT INFORMATION TAB</h3>

        <strong>Account Login Information:</strong>
        <p>
            Click on 'Account & billing' in the upper right hand of your Emma dashboard. This will take you to
            your “Manage your account settings” page. In the Account settings section, the forth tab is <strong>API
            key</strong>. Click on Generate new key to create your API key.
        </p>
        <p>
            Once you create the key, you will need to copy your account number, public api key, and private
            api key into the corresponding fields in the plugin.
        </p>
        <p>
            The plugin will now be able to connect your WordPress site to your Emma account. You may
            now assign a group to hold the email addresses that you capture from your form.
        </p>

        <h3>FORM SETUP</h3>
        <p>
            <strong>Include fields</strong> are the information that you can capture from users who submit the form.
            This information is captured and then put into the Emma group you specified in the account
            information tab.
        </p>
        <p>
            <strong>Form size</strong> includes four default sizes are included to be used on your sidebar widget area.
        </p>
        <p>
            <strong>Form placeholders</strong> is where your default text goes for the fields on the form.
        <p>
            <strong>Give props</strong> is where you can choose whether or not to display the Emma logo on your site. The
            default setting is no.
        </p>

        <h3>FORM CUSTOMIZATION</h3>
        <p>
            <strong>Form fields</strong> are the border width, color, border type, text color and background color of the
            individual fields the form.
        </p>
        <p>
            <strong>Submit button</strong> are the settings for the submit button on the form.
        </p>
        <p>
            <strong>Submit button hover state</strong> are the settings for the hover property of the submit button
        </p>

        <h3>DISPLAYING THE FORM ON YOUR SITE</h3>
        <p>
            To insert the form as a <strong>widget</strong> on your sidebar, go to Appearance -> Widgets and then move
            the “Emma Emarketing Subscription Form” to the widget area where you want the form to appear.
        </p>
        <p>
            To insert the form as a <strong>shortcode</strong> within your site, insert [emma_form] within your text editor
            where you want the form to appear.
        </p>

        <h3>DONATE</h3>
        <p>Help us out with a donation, so we can keep improving this plugin, and keep Emma in Style.</p>
        <?php $this->donations_form(); ?>

    <?php }

    function sanitize_help_settings() {
        // nothing to sanitize here folks, move along...
    }

    /*
	 * Called during admin_menu, adds an options
	 * page under Settings called Emma Emarketing Settings, rendered
	 * using the plugin_options_page method.
	 */
    function add_admin_menus() {
        // add_options_page( $page_title, $menu_title, $capability, $menu_slug, $callback );
        add_options_page( 'Emma Emarketing', 'Emma Emarketing', 'manage_options', $this->_plugin_options_key, array( &$this, 'plugin_options_page' ) );

        // enqueue stylesheet for form preview only on our plugin settings page, not entire admin area.
        // Using registered $menu_slug from add_options_page handle to hook stylesheet loading
        // no love this time. get_option() not available. should've gone w/ the value object
        // add_action( 'admin_print_styles-' . $this->_plugin_options_key, 'emma-form-styles' );

    }

    /*
      * Plugin Options page rendering goes here, checks
      * for active tab and replaces key with the related
      * settings key. Uses the plugin_options_tabs method
      * to render the tabs.
      */
    function plugin_options_page() {
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->_account_information_settings_key;
		?>
		<div class="wrap">
			<?php $this->plugin_options_tabs(); ?>
			<form method="post" action="options.php">
                <?php
				    wp_nonce_field( 'update-options' );
				    settings_fields( $tab );
				    do_settings_sections( $tab );
                    // don't do the submit button on the help tab...
                    if ( $tab !== 'emma_help' ) {
                        echo '<p class="submit">';
                        submit_button( 'Save', 'primary', $tab . '[submit]', false, array( 'id' => 'submit' ) );
                        submit_button( 'Reset', 'primary', $tab . '[reset]', false, array( 'id' => 'reset' ) );
                        echo '</p>';
                    }
                ?>
			</form>
            <?php if ( $tab !== 'emma_help' ) { ?>
                <h3>DISPLAYING THE FORM ON YOUR SITE</h3>
                <p>
                    To insert the form as a <strong>widget</strong> on your sidebar, go to Appearance -> Widgets and then move
                    the “Emma Emarketing Subscription Form” to the widget area where you want the form to appear.
                </p>
                <p>
                    To insert the form as a <strong>shortcode</strong> within your site, insert [emma_form] within your text editor
                    where you want the form to appear.
                </p>
            <?php } ?>
		</div>
    <?php }

    /*
      * Renders our tabs in the plugin options page,
      * walks through the object's tabs array and prints
      * them one by one. Provides the heading for the
      * plugin_options_page method.
      */

    function plugin_options_tabs() {
        $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->_account_information_settings_key;
        screen_icon();
        echo '<img style="margin: 7px 8px 0 0; float: left" src="' . plugins_url( 'images/e2ma_logo_35x33.png', __FILE__ ) . '"/>';
        echo '<h2 class="nav-tab-wrapper">';
        echo '<span style="padding-right:10px">Emma Emarketing</span>';
        foreach ( $this->_plugin_settings_tabs as $tab_key => $tab_caption ) {
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->_plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
        }
        echo '</h2>';
        // a little output buffering to keep the settings errors from jumping to the top of the page.
        ob_start();
        settings_errors();
        $errors = ob_get_contents();
        ob_end_clean();
        echo $errors;
    }

    function register_emma_activation() {

        if( version_compare( PHP_VERSION, '5.2.6', '<' ) ) {
            $emma_activation_error_txt = 'The Emma Emarketing plugin requires at least PHP 5.';
        }
        if( version_compare( get_bloginfo( 'version' ), '3.1', '<' ) ) {
            $emma_activation_error_txt = 'The Emma Emarketing plugin requires at least WordPress version 3.1.';
        }

        add_action( 'admin_notices', 'do_version_require' );

        function do_version_require() {
            global $emma_activation_error_txt;
            if( current_user_can( 'manage_options' ) )
                echo '<div class="error"><p>' . $emma_activation_error_txt . '</p></div>';
        }

        return;

    }

    function donations_form() { ?>
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="hosted_button_id" value="Q7FRK4XEF8EAS">
        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
        <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
        </form>
    <?php }

} // end Class Emma_Emarketing

// when the plugin is loaded, create a new instance of the class
add_action( 'plugins_loaded', create_function( '', '$emma_emarketing = new Emma_Emarketing;' ) );
?>