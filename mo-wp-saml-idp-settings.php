<?php
/*
Plugin Name: Login using WordPress Users / SAML IDP
Plugin URI: http://miniorange.com/
Description: Convert your WordPress into an SAML 2.0 Compliant IDP.
Version: 1.10.1
Author: miniOrange
Author URI: http://miniorange.com/
*/

require('class-wp-saml-idp-utility.php');
require('mo-wp-saml-idp-pages.php');
require('mo-wp-saml-idp.php');
class wordpress_idp_saml{

	function __construct() {
		add_action( 'init', array( $this,'mo_idp_start_session') );
		add_action( 'wp_logout', array( $this,'mo_idp_end_session') );
		add_action( 'admin_menu', array( $this, 'mo_idp_menu' ) );
		add_action( 'admin_init',  array( $this, 'mo_idp_save_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'mo_idp_plugin_settings_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'mo_idp_plugin_settings_script' ) );
		add_action( 'enqueue_scripts', array( $this, 'mo_idp_plugin_settings_style' ) );
		add_action( 'enqueue_scripts', array( $this, 'mo_idp_plugin_settings_script' ) );
		register_deactivation_hook(__FILE__, array( $this, 'mo_idp_deactivate'));
		register_activation_hook( __FILE__, array( $this,'mo_plugin_activate') );
		add_shortcode('mo_sp_link', array($this, 'mo_idp_shortcode') );
	}

	function mo_idp_menu() {
		$page = add_menu_page( 'MO WordPress IDP Settings ' . __( 'Configure WordPress IDP Settings', 'mo_idp_settings' ), 'WordPress IDP', 'administrator',
		'mo_idp_settings', array( $this, 'mo_idp_options' ),plugin_dir_url(__FILE__) . 'includes/images/miniorange_icon.png');
	}

	function  mo_idp_options() {
		global $wpdb;
		update_option( 'mo_idp_host_name', 'https://auth.miniorange.com' );
		mo_idp_plugin();
	}

	function mo_idp_start_session() {
		if( !session_id() )
			session_start();
	}

	function mo_idp_end_session() {
		if( session_id() )
			session_destroy();
	}

	function mo_idp_plugin_settings_style() {
		wp_enqueue_style( 'mo_idp_admin_settings_style', plugins_url('includes/css/mo_idp_style.css', __FILE__));
		wp_enqueue_style( 'mo_idp_admin_settings_phone_style', plugins_url('includes/css/phone.css', __FILE__));				
	}

	function mo_idp_plugin_settings_script() {
		wp_enqueue_script( 'mo_idp_admin_settings_phone_script', plugins_url('includes/js/phone.js', __FILE__ ));
		wp_enqueue_script( 'mo_idp_admin_settings_script', plugins_url('includes/js/settings.js', __FILE__ ), array('jquery'));
	}


	function mo_idp_save_settings(){
		if ( current_user_can( 'manage_options' )){ 
			if( isset( $_POST['option'] ) and $_POST['option'] == "mo_idp_register_customer" )
				$this->_idp_register_customer($_POST);
			else if(isset($_POST['option']) and $_POST['option'] == "mo_idp_validate_otp")
				$this->_idp_validate_otp($_POST);
			else if( isset($_POST['option']) and $_POST['option'] == 'mo_idp_phone_verification')
				$this->_mo_idp_phone_verification($_POST);
			else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_idp_connect_verify_customer" )
				$this->_mo_idp_verify_customer($_POST);
			else if(isset($_POST['option']) and $_POST['option'] == 'mo_idp_forgot_password')
				$this->_mo_idp_forgot_password();
			else if(isset($_POST['option']) and $_POST['option'] == 'mo_idp_go_back')
				$this->_mo_idp_go_back();
			else if(isset($_POST['option']) and trim($_POST['option']) == "mo_idp_resend_otp")
				$this->_mo_idp_resend_otp();
			else if(isset($_POST['option']) and trim($_POST['option']) == "mo_idp_settings")
				$this->_mo_idp_save_settings($_POST);
			else if(isset($_POST['option']) and trim($_POST['option']) == "mo_show_sp_settings")
				$this->_mo_sp_change_settings($_POST);
			else if(isset($_POST['option']) and trim($_POST['option']) == "mo_idp_contact_us_query_option")
				$this->_mo_idp_support_query();
			else if(isset($_POST['option']) and trim($_POST['option']) == "mo_idp_attr_settings")
				$this->mo_idp_save_attr_settings($_POST);
		}
	}

	function mo_idp_deactivate(){
		delete_option('mo_idp_host_name');
		delete_option('mo_idp_transactionId');
		delete_option('mo_idp_admin_password');
		delete_option('mo_idp_registration_status');
		delete_option('mo_idp_admin_phone');
		delete_option('mo_idp_new_registration');
		delete_option('mo_idp_admin_customer_key');
		delete_option('mo_idp_admin_api_key');
		delete_option('mo_idp_customer_token');
		delete_option('mo_idp_verify_customer');
		delete_option('mo_idp_message');
	}

	function mo_plugin_activate() {
		global $wpdb;
		if(!get_option('mo_saml_idp_plugin_version')){
			update_option('mo_saml_idp_plugin_version', '1.3' );
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$collate = '';

			if ( $wpdb->has_cap( 'collation' ) ){
				if ( ! empty( $wpdb->charset ) ) 
					$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
				if ( ! empty( $wpdb->collate ) )
					$collate .= " COLLATE $wpdb->collate";
			}

			$table_name = $wpdb->prefix . 'mo_sp_data';
			$sql = "CREATE TABLE $table_name (
						id bigint(20) NOT NULL auto_increment,
						mo_idp_sp_name text NOT NULL,
						mo_idp_sp_issuer longtext NOT NULL,
						mo_idp_acs_url longtext NOT NULL,
						mo_idp_cert longtext NULL,
						mo_idp_cert_encrypt longtext NULL,
						mo_idp_nameid_format longtext NOT NULL,
						mo_idp_nameid_attr varchar(55) DEFAULT 'emailAddress' NOT NULL,
						mo_idp_response_signed smallint NULL,
						mo_idp_assertion_signed smallint NULL,
						mo_idp_encrypted_assertion smallint NULL,
						mo_idp_enable_group_mapping smallint NULL,
						mo_idp_default_relayState longtext NULL,
						mo_idp_logout_url longtext NULL,
						mo_idp_logout_binding_type varchar(15) DEFAULT 'HttpRedirect' NOT NULL,
						PRIMARY KEY (id)
					)$collate;";
			dbDelta($sql);

			$table_name_atr = $wpdb->prefix . 'mo_sp_attributes';
			$sql = "CREATE TABLE $table_name_atr (
						id bigint(20) NOT NULL auto_increment,
						mo_sp_id bigint(20),
						mo_sp_attr_name longtext NOT NULL,
						mo_sp_attr_value longtext NOT NULL,
						mo_attr_type smallint DEFAULT 0 NOT NULL,
						PRIMARY KEY (id),
						FOREIGN KEY (mo_sp_id) REFERENCES $table_name (id)
					)$collate;";
			dbDelta($sql);
		} else if(strcasecmp(get_option('mo_saml_idp_plugin_version'), '1.0') == 0) {
			update_option('mo_saml_idp_plugin_version', '1.3' );
			$table_name = $wpdb->prefix . 'mo_sp_data';
			$wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN mo_idp_cert_encrypt longtext NULL");
			$wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN mo_idp_encrypted_assertion smallint NULL");
			$wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN mo_idp_default_relayState longtext NULL");
			$wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN mo_idp_logout_url longtext NULL");
			$wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN mo_idp_logout_binding_type varchar(15) DEFAULT 'HttpRedirect' NOT NULL");
			
			$table_name = $wpdb->prefix . 'mo_sp_attributes';
 			$wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN mo_attr_type smallint DEFAULT 0 NOT NULL");

		}else if(strcasecmp(get_option('mo_saml_idp_plugin_version'), '1.0.2') == 0){
			update_option('mo_saml_idp_plugin_version', '1.3' );
			$table_name = $wpdb->prefix . 'mo_sp_data';
			$wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN mo_idp_default_relayState longtext NULL");
			$wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN mo_idp_logout_url longtext NULL");
			$wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN mo_idp_logout_binding_type varchar(15) DEFAULT 'HttpRedirect' NOT NULL");
			
			$table_name = $wpdb->prefix . 'mo_sp_attributes';
 			$wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN mo_attr_type smallint DEFAULT 0 NOT NULL");

		}else if(strcasecmp(get_option('mo_saml_idp_plugin_version'), '1.0.4') == 0){
			update_option('mo_saml_idp_plugin_version', '1.3' );
			$table_name = $wpdb->prefix . 'mo_sp_data';
			$wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN mo_idp_logout_url longtext NULL");
			$wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN mo_idp_logout_binding_type varchar(15) DEFAULT 'HttpRedirect' NOT NULL");
 			
 			$table_name = $wpdb->prefix . 'mo_sp_attributes';
 			$wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN mo_attr_type smallint DEFAULT 0 NOT NULL");

 		}else if(strcasecmp(get_option('mo_saml_idp_plugin_version'), '1.2') == 0){
 			update_option('mo_saml_idp_plugin_version', '1.3' );
 			$table_name = $wpdb->prefix . 'mo_sp_attributes';
 			$wpdb->query("ALTER TABLE ".$table_name." ADD COLUMN mo_attr_type smallint DEFAULT 0 NOT NULL");
 			$wpdb->update(  $table_name, array('mo_attr_type'=>'1'), array('mo_sp_attr_name'=>'groupMapName') );
 		}
	}

	function mo_idp_success_message() {
		$message = get_option('mo_idp_message'); ?>
		<script> 
		jQuery(document).ready(function() {	
			var message = "<?php echo $message; ?>";
			jQuery('#mo_idp_msgs').append("<div class='error notice is-dismissible mo_registration_error_container'> <p class='mo_registration_msgs'>" + message + "</p></div>");
		});
		</script>
	<?php }

	function mo_idp_error_message() {
			$message = get_option('mo_idp_message'); ?>
		<script> 
		jQuery(document).ready(function() {
			var message = "<?php echo $message; ?>";
			jQuery('#mo_idp_msgs').append("<div class='updated notice is-dismissible mo_registration_success_container'> <p class='mo_registration_msgs'>" + message + "</p></div>");
		});
		</script>
	<?php }

	function get_current_customer(){
		$customer = new MO_IdP_Utility();
		$content = $customer->get_customer_key();
		$customerKey = json_decode( $content, true );
		if( json_last_error() == JSON_ERROR_NONE ) {
			update_option('mo_idp_admin_customer_key', $customerKey['id'] );
			update_option('mo_idp_admin_api_key', $customerKey['apiKey'] );
			update_option('mo_idp_customer_token', $customerKey['token'] );
			update_option('mo_idp_admin_password', '' );
			update_option('mo_idp_message', 'Your account has been retrieved successfully.' );
			delete_option('mo_idp_verify_customer');
			delete_option('mo_idp_new_registration');
			$this->mo_idp_show_success_message();
		} else {
			update_option('mo_idp_message', 'You already have an account with miniOrange. Please enter a valid password.');
			update_option('mo_idp_verify_customer', 'true');
			delete_option('mo_idp_new_registration');
			$this->mo_idp_show_error_message();
		}
	}

	function create_customer(){
		delete_option('mo_idp_sms_otp_count');
		delete_option('mo_idp_email_otp_count');
		$customer = new MO_IdP_Utility();
		$customerKey = json_decode( $customer->create_customer(), true );
		if( strcasecmp( $customerKey['status'], 'CUSTOMER_USERNAME_ALREADY_EXISTS') == 0 ) {
			$this->get_current_customer();
		} else if( strcasecmp( $customerKey['status'], 'SUCCESS' ) == 0 ) {
			update_option('mo_idp_admin_customer_key', $customerKey['id'] );
			update_option('mo_idp_admin_api_key', $customerKey['apiKey'] );
			update_option('mo_idp_customer_token', $customerKey['token'] );
			update_option('mo_idp_admin_password', '');
			update_option('mo_idp_message', 'Registration complete!');
			update_option('mo_idp_registration_status','MO_IDP_REGISTRATION_COMPLETE');
			delete_option('mo_idp_verify_customer');
			delete_option('mo_idp_new_registration');
			$this->mo_idp_show_success_message();
			header('Location: admin.php?page=mo_idp_settings&tab=pricing');
		}
		update_option('mo_idp_admin_password', '');
	}

	function mo_idp_shortcode($atts=null){
		if(is_user_logged_in()){
			if(!MO_IdP_Utility::mo_check_empty_or_null($atts))
				$html = '<a href="'.site_url().'/?option=saml_user_login&sp='.$atts['sp'].'">Login to '.$atts['sp'].'</a>';
		}else{
			$html = '<a href='.wp_login_url(get_permalink()).'>Log in</a>';
		}
		return $html;
	}

	private function mo_idp_show_success_message() {
		remove_action( 'admin_notices', array( $this, 'mo_idp_success_message') );
		add_action( 'admin_notices', array( $this, 'mo_idp_error_message') );
	}

	private function mo_idp_show_error_message() {
		remove_action( 'admin_notices', array( $this, 'mo_idp_error_message') );
		add_action( 'admin_notices', array( $this, 'mo_idp_success_message') );
	}

	private function _mo_idp_support_query() {
		if(!MO_IdP_Utility::mo_is_curl_installed()) {
			update_option( 'mo_idp_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Query submit failed.');
			$this->mo_idp_show_error_message();
			return;
		}
		
		// Contact Us query
		$email = sanitize_text_field($_POST['mo_idp_contact_us_email']);
		$phone = sanitize_text_field($_POST['mo_idp_contact_us_phone']);
		$query = sanitize_text_field($_POST['mo_idp_contact_us_query']);
		$customer = new MO_IdP_Utility();
		if ( MO_IdP_Utility::mo_check_empty_or_null( $email ) || MO_IdP_Utility::mo_check_empty_or_null( $query ) ) {
			update_option('mo_idp_message', 'Please fill up Email and Query fields to submit your query.');
			$this->mo_idp_show_error_message();
		} else {
			$submited = $customer->submit_contact_us( $email, $phone, $query );
			if ( $submited == false ) {
				update_option('mo_idp_message', 'Your query could not be submitted. Please try again.');
				$this->mo_idp_show_error_message();
			} else {
				update_option('mo_idp_message', 'Thanks for getting in touch! We shall get back to you shortly.');
				$this->mo_idp_show_success_message();
			}
		}
	}

	private function _idp_register_customer($POSTED){
		$email = '';
		$phone = '';
		$password = '';
		$confirmPassword = '';
		$companyName = '';
        $firstName = '';
        $lastName = '';
		$illegal = "#$%^*()+=[]';,/{}|:<>?~";
		$illegal = $illegal . '"';
		if(MO_IdP_Utility::mo_check_empty_or_null( $POSTED['email'] ) ||MO_IdP_Utility::mo_check_empty_or_null( $POSTED['password'] ) ||MO_IdP_Utility::mo_check_empty_or_null( $POSTED['confirmPassword'] ) || MO_IdP_Utility::mo_check_empty_or_null( $POSTED['companyName'] )) {
			update_option( 'mo_idp_message', 'Email, Company Name Password and Confirm Password are required fields. Please enter valid entries.');
			$this->mo_idp_show_error_message();
			return;
		} else if( strlen( $POSTED['password'] ) < 6 || strlen( $POSTED['confirmPassword'] ) < 6){	//check password is of minimum length 6
			update_option( 'mo_idp_message', 'Choose a password with minimum length 6.');
			$this->mo_idp_show_error_message();
			return;
		} else if(strpbrk($POSTED['email'],$illegal)) {
			update_option( 'mo_idp_message', 'Please match the format of Email. No special characters are allowed.');
			$this->mo_idp_show_error_message();
			return;
		} else {
			$email = sanitize_email( $POSTED['email'] );
			$phone = sanitize_text_field( $POSTED['phone'] );
			$password = sanitize_text_field( $POSTED['password'] );
			$confirmPassword = sanitize_text_field( $POSTED['confirmPassword'] );
		 	$firstName = sanitize_text_field( $POSTED['firstName'] );
            $lastName = sanitize_text_field( $POSTED['lastName'] );
            $companyName = sanitize_text_field( $POSTED['companyName'] );
		}

		update_option( 'mo_idp_admin_email', $email );
		update_option( 'mo_idp_admin_phone', $phone );
	 	update_option( 'mo_idp_company_name', $companyName );
        update_option( 'mo_idp_first_name', $firstName );
        update_option( 'mo_idp_last_name', $lastName );
		if( strcmp( $password, $confirmPassword) == 0 ) {
			update_option( 'mo_idp_admin_password', $password );
			$customer = new MO_IdP_Utility();
			$content = json_decode($customer->check_customer(), true);
			if( strcasecmp( $content['status'], 'CUSTOMER_NOT_FOUND') == 0 ){
				$content = json_decode($customer->send_otp_token('EMAIL',get_option('mo_idp_admin_email')), true);
				if(strcasecmp($content['status'], 'SUCCESS') == 0) {
					if(get_option('mo_idp_email_otp_count')){
						update_option('mo_idp_email_otp_count',get_option('mo_idp_email_otp_count') + 1);
						update_option('mo_idp_message', 'Another One Time Passcode has been sent <b>( ' . get_option('mo_idp_email_otp_count') . ' )</b> for verification to ' . get_option('mo_idp_admin_email'));
					}else{
						update_option('mo_idp_message', ' A passcode is sent to ' . get_option('mo_idp_admin_email') . '. Please enter the otp here to verify your email.');
						update_option('mo_idp_email_otp_count',1 );
					}
					update_option('mo_idp_transactionId',$content['txId']);
					update_option('mo_idp_registration_status','MO_OTP_DELIVERED_SUCCESS');
					$this->mo_idp_show_success_message();
				}else{
					update_option('mo_idp_message','There was an error in sending email. Please click on Resend OTP to try again.');
					update_option('mo_idp_registration_status','MO_OTP_DELIVERED_FAILURE');
					$this->mo_idp_show_error_message();
				}
			}else{
					$this->get_current_customer();
			}
		} else {
			update_option( 'mo_idp_message', 'Passwords do not match.');
			delete_option('mo_idp_verify_customer');
			$this->mo_idp_show_error_message();
		}
	}

	private function _idp_validate_otp($POSTED){
		$otp_token = '';
		if(MO_IdP_Utility::mo_check_empty_or_null( $POSTED['otp_token'] ) ) {
			update_option( 'mo_idp_message', 'Please enter a value in OTP field.');
			update_option('mo_idp_registration_status','MO_OTP_VALIDATION_FAILURE');
			$this->mo_idp_show_error_message();
			return;
		} else if(!preg_match('/^[0-9]*$/', $POSTED['otp_token'])) {
			update_option( 'mo_idp_message', 'Please enter a valid value in OTP field.');
			update_option('mo_idp_registration_status','MO_OTP_VALIDATION_FAILURE');
			$this->mo_idp_show_error_message();
			return;
		} else{
			$otp_token = sanitize_text_field( $POSTED['otp_token'] );
		}

		$customer = new MO_IdP_Utility();
		$content = json_decode($customer->validate_otp_token(get_option('mo_idp_transactionId'), $otp_token ),true);
		
		if(strcasecmp($content['status'], 'SUCCESS') == 0) {
				$this->create_customer();
		}else{
			update_option( 'mo_idp_message','Invalid one time passcode. Please enter a valid passcode.');
			update_option('mo_idp_registration_status','MO_OTP_VALIDATION_FAILURE');
			$this->mo_idp_show_error_message();
		}
	}

	private function _mo_idp_phone_verification($POSTED){
		$phone = sanitize_text_field($POSTED['phone_number']);
		$phone = str_replace(' ', '', $phone);
		update_option('mo_idp_admin_phone',$phone);
		$auth_type = 'SMS';
		$customer = new MO_IdP_Utility();
		$send_otp_response = json_decode($customer->send_otp_token($auth_type,'',$phone),true);
		if(strcasecmp($send_otp_response['status'], 'SUCCESS') == 0){
			update_option('mo_idp_transactionId',$send_otp_response['txId']);
			update_option( 'mo_idp_registration_status','MO_OTP_DELIVERED_SUCCESS');
			if(get_option('mo_idp_sms_otp_count')){
				update_option('mo_idp_sms_otp_count',get_option('mo_idp_sms_otp_count') + 1);
				update_option('mo_idp_message', 'Another One Time Passcode has been sent <b>( ' . get_option('mo_idp_sms_otp_count') . ' )</b> for verification to ' . $phone);
			}else{
					update_option('mo_idp_message', 'One Time Passcode has been sent for verification to ' . $phone);
					update_option('mo_idp_sms_otp_count',1);
			}
			$this->mo_idp_show_success_message();
		}else{
			update_option('mo_idp_message','There was an error in sending sms. Please click on Resend OTP link next to phone number textbox.');
			update_option('mo_idp_registration_status','MO_OTP_DELIVERED_FAILURE');
			$this->mo_idp_show_error_message();
		}
	}

	private function _mo_idp_verify_customer($POSTED){
		$email = '';
		$password = '';
		$illegal = "#$%^*()+=[]';,/{}|:<>?~";
		$illegal = $illegal . '"';
		if(MO_IdP_Utility::mo_check_empty_or_null( $POSTED['email'] ) ||MO_IdP_Utility::mo_check_empty_or_null( $POSTED['password'] ) ) {
			update_option( 'mo_idp_message', 'All the fields are required. Please enter valid entries.');
			$this->mo_idp_show_error_message();
			return;
		} else if(strpbrk($POSTED['email'],$illegal)) {
			update_option( 'mo_idp_message', 'Please match the format of Email. No special characters are allowed.');
			$this->mo_idp_show_error_message();
			return;
		} else{
			$email = sanitize_email( $POSTED['email'] );
			$password = sanitize_text_field( $POSTED['password'] );
		}

		update_option( 'mo_idp_admin_email', $email );
		update_option( 'mo_idp_admin_password', $password );
		$customer = new MO_IdP_Utility();
		$content = $customer->get_customer_key();
		$customerKey = json_decode( $content, true );
		if( json_last_error() == JSON_ERROR_NONE ) {
			update_option( 'mo_idp_admin_customer_key', $customerKey['id'] );
			update_option( 'mo_idp_admin_api_key', $customerKey['apiKey'] );
			update_option( 'mo_idp_customer_token', $customerKey['token'] );
			update_option( 'mo_idp_admin_phone', $customerKey['phone'] );
			update_option( 'mo_idp_admin_password', '');
			update_option( 'mo_idp_message', 'Your account has been retrieved successfully.');
			delete_option( 'mo_idp_verify_customer');
			$this->mo_idp_show_success_message();
		} else {
			update_option( 'mo_idp_message', 'Invalid username or password. Please try again.');
			$this->mo_idp_show_error_message();
		}
		update_option('mo_idp_admin_password', '');
	}

	private function _mo_idp_forgot_password(){
		$email = get_option('mo_idp_admin_email');
		$customer = new MO_IdP_Utility();
		$content = json_decode($customer->forgot_password($email),true);
		if(strcasecmp($content['status'], 'SUCCESS') == 0){
			update_option( 'mo_idp_message','You password has been reset successfully. Please enter the new password sent to your registered mail here.');
			$this->mo_idp_show_success_message();
		}else{
			update_option( 'mo_idp_message','An error occured while processing your request. Please try again.');
			$this->mo_idp_show_error_message();
		}
	}

	private function _mo_idp_go_back(){
		update_option('mo_idp_registration_status','');
		delete_option('mo_idp_new_registration');
		delete_option('mo_idp_verify_customer' ) ;
		delete_option('mo_idp_admin_email');
		delete_option('mo_idp_sms_otp_count');
		delete_option('mo_idp_email_otp_count');
	}

	private function _mo_idp_resend_otp(){
		$customer = new MO_IdP_Utility();
		$content = json_decode($customer->send_otp_token('EMAIL',get_option('mo_idp_admin_email')), true);
		if(strcasecmp($content['status'], 'SUCCESS') == 0) {
			if(get_option('mo_idp_email_otp_count')){
				update_option('mo_idp_email_otp_count',get_option('mo_idp_email_otp_count') + 1);
				update_option('mo_idp_message', 'Another One Time Passcode has been sent <b>( ' . get_option('mo_idp_email_otp_count') . ' )</b> for verification to ' . get_option('mo_idp_admin_email'));
			}else{
				
				update_option( 'mo_idp_message', ' A passcode is sent to ' . get_option('mo_idp_admin_email') . '. Please enter the otp here to verify your email.');
				update_option('mo_idp_email_otp_count',1);
			}
			update_option('mo_idp_transactionId',$content['txId']);
			update_option('mo_idp_registration_status','MO_OTP_DELIVERED_SUCCESS');

			$this->mo_idp_show_success_message();				
		}else{
			update_option('mo_idp_message','There was an error in sending email. Please click on Resend OTP to try again.');
			update_option('mo_idp_registration_status','MO_OTP_DELIVERED_FAILURE');
			$this->mo_idp_show_error_message();
		}
	}

	private function _mo_idp_save_settings($POSTED){
		if(MO_IdP_Utility::mo_is_customer_registered()){
			if(MO_IdP_Utility::mo_check_empty_or_null( $POSTED['idp_sp_name']) || MO_IdP_Utility::mo_check_empty_or_null( $POSTED['idp_sp_issuer']) 
				|| MO_IdP_Utility::mo_check_empty_or_null( $POSTED['idp_acs_url']) || MO_IdP_Utility::mo_check_empty_or_null($POSTED['idp_nameid_format'])){
				update_option('mo_idp_message','Missing required fields. Please enter valid entries.');
				$this->mo_idp_show_error_message();
			}else{
				global $wpdb;
				if(session_id() == '' || !isset($_SESSION)){ session_start(); }
				$where = $data = array();
				$check = $where['mo_idp_sp_name'] = $data['mo_idp_sp_name'] = sanitize_text_field($POSTED['idp_sp_name']);
				$data['mo_idp_sp_issuer'] = sanitize_text_field($POSTED['idp_sp_issuer']);
				$data['mo_idp_acs_url'] = sanitize_text_field($POSTED['idp_acs_url']);
				$data['mo_idp_nameid_format'] = sanitize_text_field($POSTED['idp_nameid_format']);	
				$my_sp = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}mo_sp_data WHERE mo_idp_sp_name = '$check'" );
				if(isset($my_sp))
					$wpdb->update(  $wpdb->prefix.'mo_sp_data', $data, $where );
				else
					$wpdb->insert(  $wpdb->prefix.'mo_sp_data', $data);
				$_SESSION['SP'] = $wpdb->get_row( "SELECT id FROM {$wpdb->prefix}mo_sp_data WHERE mo_idp_sp_name = '$check'" )->id;

				if(!isset($_POST['error_message']) || !$_POST['error_message']){
					update_option('mo_idp_message','Settings saved successfully.');
					$this->mo_idp_show_success_message();
				}else{
					update_option( 'mo_idp_message', $_POST['error_message']);
					$this->mo_registration_show_error_message();
				}
				
			}
		}else{
			update_option('mo_idp_message','Please register an account before trying to configure your service provider.');
			$this->mo_idp_show_error_message();
		}
	}

	private function mo_idp_save_attr_settings($POSTED) {
		global $wpdb;
		if(MO_IdP_Utility::mo_is_customer_registered()){
			$data = $where = array();
			
			if(array_key_exists('service_provider', $POSTED) && !empty($POSTED['service_provider'])){
				$sp_id = $where['id'] = $POSTED['service_provider'];
				$nameid_attr = $data['mo_idp_nameid_attr'] = $POSTED['idp_nameid_attr'];
				if(isset($POSTED['idp_role_attribute']))
					$roleMapping = $data['mo_idp_enable_group_mapping'] = $POSTED['idp_role_attribute'];

				$wpdb->update( $wpdb->prefix.'mo_sp_data', $data, $where);

				$my_sp_attr = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}mo_sp_attributes WHERE mo_sp_id = $sp_id");
				if(isset($my_sp_attr)) {
					$attrWhere['mo_sp_id'] = $sp_id;
					$wpdb->delete( $wpdb->prefix.'mo_sp_attributes', $attrWhere, $where_format = null );
				}
				
				$_SESSION['SP'] = $sp_id;
				
				if(!isset($_POST['error_message']) || !$_POST['error_message']){
					update_option('mo_idp_message','Settings saved successfully.');
					$this->mo_idp_show_success_message();
				}else{
					update_option( 'mo_idp_message', $_POST['error_message']);
					$this->mo_idp_show_error_message();
				}
			}else{
				update_option('mo_idp_message','Please Configure a Service Provider.');
				$this->mo_idp_show_error_message();
			}
		}else{
			update_option('mo_idp_message','Please register an account before trying to configure your service provider.');
			$this->mo_idp_show_error_message();
		}
	}

	private function _mo_sp_change_settings($POSTED){
		if(session_id() == '' || !isset($_SESSION)){ session_start(); }
		$_SESSION['SP'] = $POSTED['sp_id'];
	}
}
new wordpress_idp_saml;