<?php
// Register shortcode [emma_form]
add_shortcode( 'emma_form', 'emma_form_shortcode' );

function emma_form_shortcode() {

    $_form_setup_settings_key = 'emma_form_setup';
    $_form_custom_settings_key = 'emma_form_custom';

    $settings = (array) get_option( $_form_setup_settings_key );

    $emma_form = new Emma_Form( $settings );

	$returned = $emma_form->do_form();
	
	// this just cracks me up.
	return $returned;
}
?>