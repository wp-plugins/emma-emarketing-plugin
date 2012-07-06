<?php 

class Emma_Form {

    private $form_setup_settings = array();
    private $account_information_settings = array();
    private $_account_information_settings_key = 'emma_account_information';
    private $_form_setup_settings_key = 'emma_form_setup';
    private $_form_custom_settings_key = 'emma_form_custom';

    private $_data_object;

    function __construct( $settings ) {

        $this->form_setup_settings = $settings;
        // checkity check yrself b4 u wreck yrself
        // print_r($this->form_setup_settings);

    }

    function do_form() {

        // check if the form has been submitted
        if ( isset($_POST['emma_form_submit']) ) {

            // get authorization data from plugin options
            $this->account_information_settings = (array) get_option( $this->_account_information_settings_key );

            // validate form fields
            if ( !is_email($_POST['emma_email']) ) {
                $status_txt = 'Please enter a valid email address';
            } else {
                // build data object for transport

//                $this->_data_object = new stdClass();
//                $this->_data_object->email = $_POST['emma_email'];
//                if ( isset($_POST['emma_firstname']) ) $this->_data_object->fields->firstname = $_POST['emma_firstname'];
//                if ( isset($_POST['emma_lastname']) ) $this->_data_object->fields->lastname = $_POST['emma_lastname'];
//                // assign members to group(s)
//                // get the group id from the array of groups based on the active group name
//                // cast group id's as integer
////                $data_object->group_ids = (int)$this->account_information_settings['groups'][ $this->account_information_settings['group'] ];
//                $this->_data_object->group_ids = key( $this->account_information_settings['groups'][ $this->account_information_settings['group'] ] );

                $data = array();
                $data['email'] = $_POST['emma_email'];
                if ( isset($_POST['emma_firstname']) ) $data['fields']['firstname'] = $_POST['emma_firstname'];
                if ( isset($_POST['emma_lastname']) ) $data['fields']['lastname'] = $_POST['emma_lastname'];

                // assign members to group(s)
                if ( $this->account_information_settings['group_ids'] !== '' ) {

                    // the api accepts an array of integers.
                    $group_ids = (int)$this->account_information_settings['group_ids'];

                    // for now, we're just passing in one group,
                    $data['group_ids'] = array($group_ids);

                }

                // instantiate new emma api class, pass in auth data
                $emma_api = new Emma_API( $this->account_information_settings['account_id'], $this->account_information_settings['publicAPIkey'], $this->account_information_settings['privateAPIkey'] );

                // call emma, import_single_member
                $imported_member = $emma_api->import_single_member( $data );
//                $imported_member = $emma_api->import_single_member( $this->_data_object );

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

    } // end do_form



} // end Class Emma_Form
?>