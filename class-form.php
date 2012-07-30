<?php 

include_once( 'admin/class-account-information.php' );
include_once( 'admin/class-form-setup.php' );

class Form {

    private $form_setup_settings = array();
    private $account_information_settings = array();

    function __construct () {

        // get authorization data from plugin options
        $this->account_information_settings = (array) get_option( Account_Information::$key );
        $this->form_setup_settings = (array) get_option( Form_Setup::$key );

    }

    public function output() {

        // check if the form has been submitted
        if ( isset($_POST['emma_form_submit']) ) {

            // validate form fields
            if ( !is_email($_POST['emma_email']) ) {
                $status_txt = 'Please enter a valid email address';
            } else {

                $data = array();
                $data['email'] = $_POST['emma_email'];
                if ( isset($_POST['emma_firstname']) ) $data['fields']['firstname'] = $_POST['emma_firstname'];
                if ( isset($_POST['emma_lastname']) ) $data['fields']['lastname'] = $_POST['emma_lastname'];

                // assign members to group(s)
                if ( $this->account_information_settings['group_active'] !== '0' ) {

                    // the api accepts an array of integers.
                    // pass in the active group id as an integer.
                    $group_ids = (int)$this->account_information_settings['group_active'];

                    // for now, we're just passing in one group,
                    $data['group_ids'] = array($group_ids);

                }

                // instantiate new emma api class, pass in auth data
                $emma_api = new Emma_API( $this->account_information_settings['account_id'], $this->account_information_settings['publicAPIkey'], $this->account_information_settings['privateAPIkey'] );

                // call emma, import_single_member
                $imported_member = $emma_api->import_single_member( $data );

                // check for response, assign confirmation msg || error text
                if ( $imported_member == 'success' ) {
                    $status_txt = $this->form_setup_settings['confirmation_msg'];
                } else {
                    $status_txt = $imported_member;
                }

            }

        }

        // output form markup
        $emma_form = '<div id="emma-form" class="' . $this->form_setup_settings['form_size'] . '">';
        $emma_form .= '<div class="emma-wrap">';
        $emma_form .= '<form id="emma-subscription-form" action="' . htmlspecialchars( $_SERVER['REQUEST_URI'] ) . '" method="post" accept-charset="utf-8">';
        $emma_form .= '<ul id="emma-form-elements">';
        $emma_form .= '<li class="emma-form-row">';
        $emma_form .= '<label class="emma-form-label" for="emma-email"> Email <span class="emma-required">*</span> </label>';
        $emma_form .= '<input id="emma-email" class="emma-form-input" type="text" name="emma_email" size="30" placeholder="' . $this->form_setup_settings['email_placeholder'] . '">';
        $emma_form .= '</li>';
        // check if they want to include the first and last name fields
        // and include custom placeholder text
        if ( $this->form_setup_settings['include_firstname_lastname'] == '1' ) {
            $emma_form .= '<li class="emma-form-row">';
            $emma_form .= '<label class="emma-form-label" for="emma-firstname">First Name</label>';
            $emma_form .= '<input id="emma-firstname" class="emma-form-input" type="text" name="emma_firstname" size="30" placeholder="' . $this->form_setup_settings['firstname_placeholder'] . '">';
            $emma_form .= '</li>';
            $emma_form .= '<li class="emma-form-row">';
            $emma_form .= '<label class="emma-form-label" for="emma-lastname">Last Name</label>';
            $emma_form .= '<input id="emma-lastname"  class="emma-form-input" type="text" name="emma_lastname"  size="30" placeholder="' . $this->form_setup_settings['lastname_placeholder'] . '">';
            $emma_form .= '</li>';
        }
        $emma_form .= '<li class="emma-form-row emma-form-row-last">';
        $emma_form .= '<span class="emma-form-label-required">';
        $emma_form .= '<span class="emma-required">*</span> required </span>';
        $emma_form .= '<input id="emma-form-submit" type="submit" name="emma_form_submit" value="' . $this->form_setup_settings['submit_txt'] . '">';
        $emma_form .= '</li>';
        $emma_form .= '</ul>';
        $emma_form .= '</form>';

        // output status message
        if ( isset($_POST['emma_form_submit']) ) {
            $emma_form .= '<div class="emma-status">' . $status_txt . '</div>';
        }

        if ( $this->form_setup_settings['powered_by'] == 'yes' ) {

            $emma_form .= '<div id="powered-by">Powered By:';
            $emma_form .= '<a href="http://myemma.com/" target="_blank">Emma</a>';
            $emma_form .= '</div>';

        }

        $emma_form .= '</div><!-- end .emma-wrap -->';
        
        $emma_form .= '</div><!-- end #emma-form -->';

        return $emma_form;

    } // end output

} // end Class Form

