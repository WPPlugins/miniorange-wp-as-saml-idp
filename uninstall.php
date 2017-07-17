<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

if (! is_multisite ()) {
	// delete all stored key-value pairs
	
	
	$users = get_users( array() );
	foreach ( $users as $user ) {
		global $wpdb;	
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
		delete_option('mo_idp_admin_email');
		delete_option('mo_saml_idp_plugin_version');
		//plugin settings
		$sql = "DROP TABLE ". $wpdb->prefix.'mo_sp_data';
		$wpdb->query($sql);

		$sql = "DROP TABLE ". $wpdb->prefix.'mo_sp_attributes';
		$wpdb->query($sql);
	}
} else {
	global $wpdb;
	$blog_ids = $wpdb->get_col ( "SELECT blog_id FROM $wpdb->blogs" );
	$original_blog_id = get_current_blog_id ();
	
	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog ( $blog_id );
		global $wpdb;	
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
		delete_option('mo_idp_admin_email');
		delete_option('mo_saml_idp_plugin_version');
		//plugin settings
		$sql = "DROP TABLE ". $wpdb->prefix.'mo_sp_data';
		$wpdb->query($sql);

		$sql = "DROP TABLE ". $wpdb->prefix.'mo_sp_attributes';
		$wpdb->query($sql);
	}
	switch_to_blog ( $original_blog_id );
}
?>