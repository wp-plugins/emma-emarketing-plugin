<?php
/**
 * uninstall.php
 *
 *
 */

// If uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

// Delete options from options table

$_account_information_settings_key = 'emma_account_information';
$_form_setup_settings_key = 'emma_form_setup';
$_form_custom_settings_key = 'emma_form_custom';
$_help_settings_key = 'emma_help';

delete_option( $_account_information_settings_key );
delete_option( $_form_custom_settings_key );
delete_option( $_form_custom_settings_key );
delete_option( $_form_setup_settings_key );

// remove any additional options and custom tables

?>