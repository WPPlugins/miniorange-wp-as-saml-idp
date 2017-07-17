<?php
function mo_idp_plugin() {
	if( isset( $_GET[ 'tab' ]) && $_GET[ 'tab' ] !== 'register' ) {
		$active_tab = $_GET[ 'tab' ];
	} else if(MO_IdP_Utility::mo_is_customer_registered()) {
		$active_tab = 'settings';
	} else {
		$active_tab = 'register';
	}

	if(MO_IdP_Utility::mo_is_curl_installed()==0){ ?>
		<p style="color:red;">(Warning: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP CURL extension</a> is not installed or disabled) Please go to Troubleshooting for steps to enable curl.</p>
	<?php
	}?>
<div id="tab">
	<h2 class="nav-tab-wrapper">
		<?php if(!MO_IdP_Utility::mo_is_customer_registered()) { ?>
			<a class="nav-tab <?php echo $active_tab == 'register' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'register'), $_SERVER['REQUEST_URI'] ); ?>">Account Setup</a>
		<?php }else{ ?>
			<a class="nav-tab <?php echo $active_tab == 'profile' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'profile'), $_SERVER['REQUEST_URI'] ); ?>">User Profile</a>
		<?php }?>
		<a class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'settings'), $_SERVER['REQUEST_URI'] ); ?>">Identity Provider</a>
		<a class="nav-tab <?php echo $active_tab == 'sp_settings' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'sp_settings'), $_SERVER['REQUEST_URI'] ); ?>">Service Provider</a>
		<a class="nav-tab <?php echo $active_tab == 'attr_settings' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'attr_settings'), $_SERVER['REQUEST_URI'] ); ?>">Attribute/Role Mapping</a>
		<a class="nav-tab <?php echo $active_tab == 'pricing' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'pricing'), $_SERVER['REQUEST_URI'] ); ?>">Licensing Plans</a>
		<a class="nav-tab <?php echo $active_tab == 'help' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'help'), $_SERVER['REQUEST_URI'] ); ?>">Help & Troubleshooting</a>
	</h2>
</div>

<div id="mo_idp_settings">

	<div class="mo_container">
		<div id="mo_idp_msgs"></div>
			<table class="mo_idp_settings_table">
				<tr>
					<td style="vertical-align:top;width:65%;">

						<?php
							if ( $active_tab == 'register') {
								if (get_option ( 'mo_idp_verify_customer' ) == 'true') {
									mo_idp_show_verify_password_page();
								} else if (trim ( get_option ( 'mo_idp_admin_email' ) ) != '' && trim ( get_option ( 'mo_idp_admin_api_key' ) ) == '' && get_option ( 'mo_idp_new_registration' ) != 'true') {
									mo_idp_show_verify_password_page();
								} else if(get_option('mo_idp_registration_status') == 'MO_OTP_DELIVERED_SUCCESS' || get_option('mo_idp_registration_status') == 'MO_OTP_VALIDATION_FAILURE' 
										|| get_option('mo_idp_registration_status') == 'MO_OTP_DELIVERED_FAILURE' ){
									mo_idp_show_otp_verification();
								}else if (!MO_IdP_Utility::mo_is_customer_registered()) {
									delete_option ( 'password_mismatch' );
									mo_idp_show_new_registration_page();
								}
							}else if($active_tab == 'sp_settings') {
								mo_idp_show_sp_settings_page();
							}else if($active_tab == 'settings') {
								mo_idp_show_settings_page();
							}else if($active_tab == 'attr_settings') {
								mo_idp_show_attr_settings_page();
							}else if($active_tab == 'help') {
								mo_idp_troubleshoot_info();
							}else if($active_tab == 'profile'){
								mo_idp_profile_info();
							}else if($active_tab == 'pricing'){
								mo_idp_pricing_info();
							}
							
						?>
					</td>
					<?php if($active_tab != 'pricing'){?>
					<td style="vertical-align:top;padding-left:1%;">
						<?php echo mo_plugin_support(); ?>
					</td>
					<?php }?>
				</tr>
			</table>
		<?php

}


function mo_idp_profile_info(){
	
	$current_user = wp_get_current_user();
?>
	<div class="mo_idp_table_layout">

		<h4>Thank you for registering with us.</h4>
		<h3>Your Profile</h3>
		<table border="1" style="background-color:#FFFFFF; border:1px solid #CCCCCC; border-collapse: collapse; padding:0px 0px 0px 10px; margin:2px; width:100%">
			<tr>
				<td style="width:45%; padding: 10px;"><b>Registered Email</b></td>
				<td style="width:55%; padding: 10px;"><?php echo get_option('mo_idp_admin_email');?> 
				</td>
			</tr>
			<tr>
				<td style="width:45%; padding: 10px;"><b>Customer ID</b></td>
				<td style="width:55%; padding: 10px;"><?php echo get_option('mo_idp_admin_customer_key');?></td>
			</tr>
			<tr>
				<td style="width:45%; padding: 10px;"><b>API Key</b></td>
				<td style="width:55%; padding: 10px;"><?php echo get_option('mo_idp_admin_api_key');?></td>
			</tr>
			<tr>
				<td style="width:45%; padding: 10px;"><b>Token Key</b></td>
				<td style="width:55%; padding: 10px;"><?php echo get_option('mo_idp_customer_token');?></td>
			</tr>
		</table>
	</div>

<?php 
}

function mo_plugin_support(){
	
	$current_user = wp_get_current_user();
?>
	<div class="mo_idp_support_layout">

			<h3>Support</h3>
			<p>Need any help? Just send us a query so we can help you.</p>
			<form method="post" action="">
				<input type="hidden" name="option" value="mo_idp_contact_us_query_option" />
				<table class="mo_idp_settings_table">
					<tr>
						<td><input type="email" class="mo_idp_table_contact" required placeholder="Enter your Email" name="mo_idp_contact_us_email" value="<?php echo get_option("mo_idp_admin_email"); ?>"></td>
					</tr>
					<tr>
						<td><input type="tel" id="contact_us_phone" pattern="[\+]\d{11,14}|[\+]\d{1,4}[\s]\d{9,10}" placeholder="Enter your phone number with country code (+1)" class="mo_idp_table_contact" name="mo_idp_contact_us_phone" value="<?php echo get_option('mo_idp_admin_phone');?>"></td>
					</tr>
					<tr>
						<td><textarea class="mo_idp_table_contact" onkeypress="mo_idp_valid_query(this)" onkeyup="mo_idp_valid_query(this)" placeholder="Write your query here" onblur="mo_idp_valid_query(this)" required name="mo_idp_contact_us_query" rows="4" style="resize: vertical;"></textarea></td>
					</tr>
				</table>
				<br>
			<input type="submit" name="submit" value="Submit Query" style="width:110px;" class="button button-primary button-large" />

			</form>
			<p>If you want custom features in the plugin, just drop an email to <a href="mailto:info@miniorange.com">info@miniorange.com</a>.</p>
		</div>
	</div>
	</div>
	</div>
	<script>
		jQuery("#contact_us_phone").intlTelInput();
		function mo_valid_query(f) {
			!(/^[a-zA-Z?,.\(\)\/@ 0-9]*$/).test(f.value) ? f.value = f.value.replace(
					/[^a-zA-Z?,.\(\)\/@ 0-9]/, '') : null;
		}
		
	function moSharingSizeValidate(e){
		var t=parseInt(e.value.trim());t>60?e.value=60:10>t&&(e.value=10)
	}
	function moSharingSpaceValidate(e){
		var t=parseInt(e.value.trim());t>50?e.value=50:0>t&&(e.value=0)
	}
	function moLoginSizeValidate(e){
		var t=parseInt(e.value.trim());t>60?e.value=60:20>t&&(e.value=20)
	}
	function moLoginSpaceValidate(e){
		var t=parseInt(e.value.trim());t>60?e.value=60:0>t&&(e.value=0)
	}
	function moLoginWidthValidate(e){
		var t=parseInt(e.value.trim());t>1000?e.value=1000:140>t&&(e.value=140)
	}
	function moLoginHeightValidate(e){
		var t=parseInt(e.value.trim());t>50?e.value=50:35>t&&(e.value=35)
	}

	</script>
<?php
}

function mo_idp_show_verify_password_page() {
	?>
		<!--Verify password with miniOrange-->
		<form name="f" method="post" action="">
			<input type="hidden" name="option" value="mo_idp_connect_verify_customer" />
			<div class="mo_idp_table_layout">
				<?php if(!MO_IdP_Utility::mo_is_customer_registered()) { ?>
					<div style="display:block;margin-top:10px;color:red;background-color:rgba(251, 232, 0, 0.15);padding:5px;border:solid 1px rgba(255, 0, 9, 0.36);">
					Please <a href="<?php echo add_query_arg( array('tab' => 'register'), $_SERVER['REQUEST_URI'] ); ?>">Register or Login with miniOrange</a> to enable WordPress IDP.
					</div>
				<?php } ?>
			
				<h3>Login with miniOrange</h3>
				<p><b>It seems you already have an account with miniOrange. Please enter your miniOrange email and password. <a href="#forgot_password">Click here if you forgot your password?</a></b></p>
				<table class="mo_idp_settings_table">
					<tr>
						<td><b><font color="#FF0000">*</font>Email:</b></td>
						<td><input class="mo_idp_table_textbox" type="email" name="email"
							required placeholder="person@example.com"
							value="<?php echo get_option('mo_idp_admin_email');?>" /></td>
					</tr>
					<td><b><font color="#FF0000">*</font>Password:</b></td>
					<td><input class="mo_idp_table_textbox" required type="password"
						name="password" placeholder="Choose your password" /></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><input type="button" id="goBackButton" value="Go Back"
							class="button button-primary button-large" />
							<input type="submit" name="submit"
							class="button button-primary button-large" value="Submit" />
						</td>
					</tr>
				</table>
			</div>
		</form>
		<form name="goBack" method="post" action="" id="goBacktoRegistrationPage">
			<input type="hidden" name="option" value="mo_idp_go_back"/>
		</form>
		<form name="forgotpassword" method="post" action="" id="forgotpasswordform">
			<input type="hidden" name="option" value="mo_idp_forgot_password"/>
		</form>
		<script>
			jQuery('a[href="#forgot_password"]').click(function(){
				jQuery('#forgotpasswordform').submit();
			});
			jQuery('#goBackButton').click(function(){
				jQuery('#goBacktoRegistrationPage').submit();
			});
		</script>
		<?php
}

function mo_idp_show_otp_verification(){
	?>
		<!-- Enter otp -->
		<form name="f" method="post" id="otp_form" action="">
			<input type="hidden" name="option" value="mo_idp_validate_otp" />
				<div class="mo_idp_table_layout">
					<table class="mo_idp_settings_table">
						<h3>Verify Your Email</h3>
						<tr>
							<td><b><font color="#FF0000">*</font>Enter OTP:</b></td>
							<td colspan="3"><input class="mo_idp_table_textbox" autofocus="true" type="text" name="otp_token" required placeholder="Enter OTP" style="width:40%;"  title="Only 6 digit numbers are allowed"/>
							 &nbsp;&nbsp;<a style="cursor:pointer;" onclick="document.getElementById('resend_otp_form').submit();">Resend OTP ?</a></td>
						</tr>
						<tr><td colspan="3"></td></tr>
						<tr>
							<td>&nbsp;</td>
							<td style="width:17%">
								<input type="submit" name="submit" value="Validate OTP" class="button button-primary button-large" />
							</td>
		</form>
							<form name="f" method="post">
							<td style="width:18%">
											<input type="hidden" name="option" value="mo_idp_go_back"/>
											<input type="submit" name="submit"  value="Back" class="button button-primary button-large" /></td>
							</form>
								<form name="f" id="resend_otp_form" method="post" action="">
							<td>

								<input type="hidden" name="option" value="mo_idp_resend_otp"/>
							</td>
							</tr>
												
											
								</form>
					</table>
		<br>
		<hr>

		<h3>I did not recieve any email with OTP . What should I do ?</h3>
		<form id="phone_verification" method="post" action="">
			<input type="hidden" name="option" value="mo_idp_phone_verification" />
			 If you cannot see an email from miniOrange in your mails, please check your <b>SPAM Folder</b>. If you don't see an email even in SPAM folder, verify your identity with our alternate method.
			 <br><br>
				<b>Enter your valid phone number here and verify your identity using one time passcode sent to your phone.</b>
				<br><br>
				<table class="mo_idp_settings_table">
				<tr>
				<td colspan="3">
				<input class="mo_idp_table_textbox" required  pattern="[0-9\+]{12,18}" autofocus="true" style="width:100%;" type="tel" name="phone_number" id="phone" placeholder="Enter Phone Number" value="<?php echo get_option('mo_idp_admin_phone'); ?>" title="Enter phone number(at least 10 digits) without any space or dashes."/>
				</td>
				<td>&nbsp;&nbsp;
			<a style="cursor:pointer;" onclick="document.getElementById('phone_verification').submit();">Resend OTP ?</a>
				</td>
				</tr>
				</table>
				<br><input type="submit" value="Send OTP" class="button button-primary button-large" />
		
		</form>
		<br>
		<h3>What is an OTP ?</h3>
		<p>OTP is a one time passcode ( a series of numbers) that is sent to your email or phone number to verify that you have access to your email account or phone. </p>
		</div>
		<script>
		jQuery("#phone").intlTelInput();
					
						
		</script>


<?php
}

function mo_idp_show_new_registration_page() {
	update_option ( 'mo_idp_new_registration', 'true' );
	
	$current_user = wp_get_current_user();
	?>

		<form name="f" method="post" action="" id="register-form">
			<input type="hidden" name="option" value="mo_idp_register_customer" />
			<div class="mo_idp_table_layout">
				
				<h3>Register with miniOrange</h3>

				<p>
					<div class="mo_idp_help_title">[ Why should I register? ]</a></div>
					<div hidden class="mo_idp_help_desc">
						All configurations made by you are stored on your WordPress instance and all transactions made are between your site and the Service Provider(s) that you have configured. We do not track any of your transactions or store any of your data. We have made registration mandatory so that we can get back to you as in when you need support.
					</div>
				</p>
				<table class="mo_idp_settings_table">
					<tr>
						<td><b><font color="#FF0000">*</font>Email:</b></td>
						<td><input class="mo_idp_table_textbox" type="email" name="email"
							required placeholder="person@example.com"
							value="<?php echo $current_user->user_email;?>" /></td>
					</tr>

					<tr>
						<td><b><font color="#FF0000">*</font>Website/Company Name:</b></td>
						<td><input class="mo_idp_table_textbox" type="text" name="companyName"
							required placeholder="Enter your companyName"
							value="<?php echo $_SERVER["SERVER_NAME"]; ?>" /></td>
						<td></td>
					</tr>

					<tr>
						<td><b>&nbsp;&nbsp;FirstName:</b></td>
						<td><input class="mo_idp_table_textbox" type="text" name="firstName"
							placeholder="Enter your First Name"
							value="<?php echo $current_user->user_firstname; ?>" /></td>
						<td></td>
					</tr>

					<tr>
						<td><b>&nbsp;&nbsp;LastName:</b></td>
						<td><input class="mo_idp_table_textbox" type="text" name="lastName"
							placeholder="Enter your Last Name"
							value="<?php echo $current_user->user_lastname; ?>" /></td>
						<td></td>
					</tr>

					<tr>
						<td><b>&nbsp;&nbsp;Phone number:</b></td>
						<td><input class="mo_idp_table_textbox" type="tel" id="phone"
							pattern="[\+]\d{11,14}|[\+]\d{1,4}[\s]\d{9,10}" name="phone"
							title="Phone with country code eg. +1xxxxxxxxxx"
							placeholder="Phone with country code eg. +1xxxxxxxxxx"
							value="<?php echo get_option('mo_idp_admin_phone');?>" /><br/>We will call only if you need support.</td>
						<td></td>
					</tr>
					<tr>
						<td><b><font color="#FF0000">*</font>Password:</b></td>
						<td><input class="mo_idp_table_textbox" required type="password"
							name="password" placeholder="Choose your password (Min. length 6)" /></td>
					</tr>
					<tr>
						<td><b><font color="#FF0000">*</font>Confirm Password:</b></td>
						<td><input class="mo_idp_table_textbox" required type="password"
							name="confirmPassword" placeholder="Confirm your password" /></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><br /><input type="submit" name="submit" value="Next" style="width:100px;"
							class="button button-primary button-large" /></td>
					</tr>
					<tr>
						<td colspan="2"><br/><font color="#FF0000">NOTE:</font> By clicking Next, you agree to our <a href="http://miniorange.com/usecases/miniOrange_Privacy_Policy.pdf" target="_blank">Privacy Policy</a> and <a href="http://miniorange.com/usecases/miniOrange_User_Agreement.pdf" target="_blank">User Agreement</a>.</td>
					</tr>
				</table>
				<br/>			
			</div>
		</form>
		<script>
				var text = "&nbsp;&nbsp;We will call only if you need support."
				jQuery('.intl-number-input').append(text);

		</script>
<?php 
}

function mo_idp_show_sp_settings_page() {
	?>	
	<div class="mo_idp_table_layout">
		<?php if(!MO_IdP_Utility::mo_is_customer_registered()) { ?>
			<div style="display:block;margin-top:10px;color:red;background-color:rgba(251, 232, 0, 0.15);padding:5px;border:solid 1px rgba(255, 0, 9, 0.36);">
			Please <a href="<?php echo add_query_arg( array('tab' => 'register'), $_SERVER['REQUEST_URI'] ); ?>">Register or Login with miniOrange</a> to enable WordPress IDP.
			</div>
		<?php } ?>
		<form name="f" method="post" action="" id="mo_idp_settings">
			<input type="hidden" name="option" value="mo_idp_settings" />
				<h4>You will need the following information to configure your Service Provider. Copy it and keep it handy:</h4>
				<table border="1" style="background-color:#FFFFFF; border:1px solid #CCCCCC; padding:0px 0px 0px 10px; margin:2px; border-collapse: collapse; width:98%">
					<tr>
						<td style="width:40%; padding: 15px;"><b>IDP-EntityID / Issuer</b></td>
						<td style="width:60%; padding: 15px;"><?php echo plugins_url('', __FILE__).'/'?></td>
					</tr>
					<tr>
						<td style="width:40%; padding: 15px;"><b>SAML Login URL</b></td>
						<td style="width:60%;  padding: 15px;"><?php echo site_url().'/'?></td>
					</tr>
					<tr>
						<td style="width:40%; padding: 15px;"><b>Certificate (Optional)</b></td>
						<?php if(!MO_IdP_Utility::mo_is_customer_registered()) { ?>
							<td style="width:60%;  padding: 15px;">Download <i>(Register to download the certificate)</i></td>
						<?php } else { ?>
							<td style="width:60%;  padding: 15px;"><a href="<?php echo plugins_url('resources/idp-signing.crt', __FILE__); ?>" download>Download</a></td>
						<?php } ?>
					</tr>
					<tr>
						<td style="width:40%; padding: 15px;"><b>Response Signed</b></td>
						<td style="width:60%;  padding: 15px;">You can choose to sign your response in <a href="<?php echo add_query_arg( array('tab' => 'settings'), $_SERVER['REQUEST_URI'] ); ?>">Identity Provider</a></td>
					</tr>
					<tr>
						<td style="width:40%; padding: 15px;"><b>Assertion Signed</b></td>
						<td style="width:60%;  padding: 15px;">You can choose to sign your assertion in <a href="<?php echo add_query_arg( array('tab' => 'settings'), $_SERVER['REQUEST_URI'] ); ?>">Identity Provider</a></td>
					</tr>
				</table>
				<p style="text-align: center;font-size: 13pt;font-weight: bold;">OR</p>
				<p>You can provide this metadata URL to your Service Provider</p>
					<code><b>This is available in the premium version of the plugin. Check <a href="<?php echo add_query_arg( array('tab' => 'pricing'), $_SERVER['REQUEST_URI'] ); ?>">Licensing Tab</a> to learn more.</b></code>									
				</br><br/>
		</form>
	</div>

<?php
}

function mo_idp_show_settings_page(){
	global $wpdb;
	$my_sp = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}mo_sp_data WHERE mo_idp_sp_name='WordPress'" );
?>	
	<div class="mo_idp_table_layout">
		<?php if(!MO_IdP_Utility::mo_is_customer_registered()) { ?>
			<div style="display:block;margin-top:10px;color:red;background-color:rgba(251, 232, 0, 0.15);padding:5px;border:solid 1px rgba(255, 0, 9, 0.36);">
			Please <a href="<?php echo add_query_arg( array('tab' => 'register'), $_SERVER['REQUEST_URI'] ); ?>">Register or Login with miniOrange</a> to enable WordPress IDP.
			</div>
		<?php } ?>
		
			<div>
				<div style="width:40%;float:left;"><h3>Configure Identity Provider </h3></div>
			</div>
		<form name="f" method="post" action="" id="mo_idp_settings">
			<input type="hidden" name="option" value="mo_idp_settings" />
			<input type="hidden" name="service_provider" value="<?php echo isset($my_sp) && !empty($my_sp) ? $my_sp->id : "" ?>" />
			<table class="mo_idp_settings_table">
				<tr>
					<td colspan="2">
					<b>Please note down the following information from your Service Provider admin screen and keep it handy to configure your Identity provider.</b>
						<ol>
							<li><b>SP Entity ID / Issuer</b></li>
							<li><b>ACS URL</b></li>
							<li><b>X.509 Certificate for Signing if you are using HTTP-POST Binding. [This is a premium feature.]</b></li>
							<li><b>X.509 Certificate for Encryption. [This is a premium feature.]</b></li>
							<li><b>NameID Format</b></li>
						</ol>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div style="background-color:#CBCBCB;padding:1%;border-radius:2px;">
							New to SAML? &nbsp; Looking for a documentation? &nbsp; <a href="<?php echo plugins_url('resources/Generic_IdP_Plugin_Guide.pdf', __FILE__); ?>" download>Click Here to download our guide.</a>
						</div>
						<br><br>
					</td>
				</tr>
				<tr>
					<td><strong>SP Entity ID or Issuer <span style="color:red;">*</span>:</strong></td>
					<td><input type="text" name="idp_sp_issuer" placeholder="Service Provider Entity ID or Issuer" style="width: 95%;" value="<?php  echo !empty($my_sp) ? $my_sp->mo_idp_sp_issuer : ''; ?>" required <?php if(!MO_IdP_Utility::mo_is_customer_registered()) echo 'disabled'?>/></td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td><strong>ACS URL <span style="color:red;">*</span>:</strong></td>
					<td><input type="text" name="idp_acs_url" placeholder="AssertionConsumerService URL" style="width: 95%;" value="<?php  echo !empty($my_sp) ? $my_sp->mo_idp_acs_url : ''; ?>" required <?php if(!MO_IdP_Utility::mo_is_customer_registered()) echo 'disabled'?>/></td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td><strong>Single Logout URL (optional):<br/></strong></td>
					<td><span style="color:red;">*</span> This is a premium feature. Check <a href="<?php echo add_query_arg( array('tab' => 'pricing'), $_SERVER['REQUEST_URI'] ); ?>">Licensing Tab</a> to learn more.</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td><strong>X.509 Certificate (optional):<br/><i><span style="font-size:11px;">(For Signed Request)</span></i></strong></td>
					<td><span style="color:red;">*</span> This is a premium feature. Check <a href="<?php echo add_query_arg( array('tab' => 'pricing'), $_SERVER['REQUEST_URI'] ); ?>">Licensing Tab</a> to learn more.</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td><strong>X.509 Certificate (optional):<br/><i><span style="font-size:11px;">(For Encrypted Assertion)</span></i></strong></td>
					<td><span style="color:red;">*</span> This is a premium feature. Check <a href="<?php echo add_query_arg( array('tab' => 'pricing'), $_SERVER['REQUEST_URI'] ); ?>">Licensing Tab</a> to learn more.</td>
				</tr>
				<tr>
					<td><strong>NameID format <span style="color:red;">*</span>:</strong></td>
					<td>
						<select style="margin-top:4%;width:95%;" name='idp_nameid_format' required <?php if(!MO_IdP_Utility::mo_is_customer_registered()) echo 'disabled'?>>
						  <option value="">Select a NameID Format</option>
						  <option value="1.1:nameid-format:emailAddress" <?php  echo !empty($my_sp) && strpos($my_sp->mo_idp_nameid_format,'emailAddress') ? 'selected' : ''; ?>>urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress</option>
						  <option value="1.1:nameid-format:unspecified" <?php  echo !empty($my_sp) && strpos($my_sp->mo_idp_nameid_format,'unspecified') ? 'selected' : ''; ?>>urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified</option>
						  <option value="2.0:nameid-format:transient" <?php  echo !empty($my_sp) && strpos($my_sp->mo_idp_nameid_format,'transient') ? 'selected' : ''; ?>>urn:oasis:names:tc:SAML:1.1:nameid-format:transient</option>
						  <option value="2.0:nameid-format:persistent" <?php  echo  !empty($my_sp) && strpos($my_sp->mo_idp_nameid_format,'persistent') ? 'selected' : ''; ?>>urn:oasis:names:tc:SAML:1.1:nameid-format:persistent</option>
						</select><br>
						<i>(<span style="color:red">NOTE: </span> Select urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress by default)</i>	
					</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td><strong>Response Signed:</strong></td>
					<td><span style="color:red;">*</span> This is a premium feature. Check <a href="<?php echo add_query_arg( array('tab' => 'pricing'), $_SERVER['REQUEST_URI'] ); ?>">Licensing Tab</a> to learn more.</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td><strong>Assertion Signed:</strong></td>
					<td><span style="color:red;">*</span> This is a premium feature. Check <a href="<?php echo add_query_arg( array('tab' => 'pricing'), $_SERVER['REQUEST_URI'] ); ?>">Licensing Tab</a> to learn more.</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td><strong>Encrypted Assertion:</strong></td>
					<td><span style="color:red;">*</span> This is a premium feature. Check <a href="<?php echo add_query_arg( array('tab' => 'pricing'), $_SERVER['REQUEST_URI'] ); ?>">Licensing Tab</a> to learn more.</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><br/>
						<input type="submit" name="submit" style="width:100px;margin-bottom:2%;" value="Save" class="button button-primary button-large" <?php if(!MO_IdP_Utility::mo_is_customer_registered()) echo 'disabled'?>/> &nbsp;
						<input type="button" name="test" title="You can only test your Configuration after saving your Service Provider Settings." onclick="showTestWindow();" <?php if(!MO_IdP_Utility::mo_is_customer_registered() || empty($my_sp)) echo 'disabled'?> value="Test configuration" class="button button-primary button-large" style="margin-right: 3%;"/>
					</td>
				</tr>
			</table>
			<input type="hidden" name="idp_sp_name" placeholder="Service Provider Name" style="width: 95%;" value="WordPress" required <?php if(!MO_IdP_Utility::mo_is_customer_registered()) echo 'disabled'?> pattern="^\w*$" title="Only alphabets, numbers and underscore is allowed"/>
		</form>
		<input type="checkbox"  <?php if(!MO_IdP_Utility::mo_is_customer_registered() || empty($my_sp)) echo 'disabled title="Disabled. Configure your Identity Provider"'?> onchange="window.location='<?php echo admin_url(); ?>admin.php?page=mo_idp_settings&tab=sp_settings'" />Check this option if you have Configured your Identity Provider settings.
		<br/><br/>
		<script>
			function showTestWindow() {
				var myWindow = window.open("<?php echo site_url(). '/?option=testConfig&acs='.$my_sp->mo_idp_acs_url.'&issuer='.$my_sp->mo_idp_sp_issuer.'' ?>", "TEST SAML IDP", "scrollbars=1 width=800, height=600");	
			}
		</script>
	</div>

<?php
}

function mo_idp_show_attr_settings_page(){
	global $wpdb;
	$my_sp = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}mo_sp_data WHERE id=1" );
?>
	<div class="mo_idp_table_layout">
		<?php if(!MO_IdP_Utility::mo_is_customer_registered()) { ?>
			<div style="display:block;margin-top:10px;color:red;background-color:rgba(251, 232, 0, 0.15);padding:5px;border:solid 1px rgba(255, 0, 9, 0.36);">
			Please <a href="<?php echo add_query_arg( array('tab' => 'register'), $_SERVER['REQUEST_URI'] ); ?>">Register or Login with miniOrange</a> to enable WordPress IDP.
			</div>
		<?php } ?>
		
			<div>
				<div style="width:40%;float:left;"><h3>Attribute Mapping (Optional)</h3></div>
					
			</div>
		<form name="f" method="post" action="" id="mo_idp_attr_settings">
			<input type="hidden" name="option" value="mo_idp_attr_settings" />
			<input type="hidden" name="error_message" id="error_message">
			<input type="hidden" name="service_provider" value="<?php  echo isset($my_sp) && !empty($my_sp) ? $my_sp->id : ""; ?>" />
			<table class="mo_idp_settings_table">
				<tr>
					<td style="width:150px;"><strong>NameID Attribute:</strong></td>
					<td>
						<select style="width:60%;" name='idp_nameid_attr' required <?php if(!MO_IdP_Utility::mo_is_customer_registered()) echo 'disabled'?>>
						  <option disabled 	value="">Select a NameID attribute value to be sent in the SAML Response</option>
						  <option value="emailAddress" <?php  echo !empty($my_sp) && !strcmp($my_sp->mo_idp_nameid_attr,'emailAddress') ? 'selected' : ''; ?>>WordPress Email Address</option>
						  <option value="username" <?php  echo !empty($my_sp) && !strcmp($my_sp->mo_idp_nameid_attr,'username') ? 'selected' : ''; ?>>WordPress Username</option>
						</select>
					</td>
				</tr>
				<tr>
					<td></td>
					<td><i><span style="color:red">NOTE: </span>This attribute value is sent in SAML Response. Users in your Service Provider will be searched (existing users) or created (new users) based on this attribute. Use EmailAddress by default.</i></td>
				</tr>
			</table><br/>
			<div class="mo_premium_option_text"><span style="color:red;">*</span> This is a premium feature. Check <a href="<?php echo add_query_arg( array('tab' => 'pricing'), $_SERVER['REQUEST_URI'] ); ?>">Licensing Tab</a> to learn more.</div>
			<div class="mo_premium_option">
				<table class="mo_idp_settings_table">
					<tr>
						<td><strong>Attribute Statements (OPTIONAL):</strong></td>
						<td><input type="button" name="add_attribute" value="+" disabled onclick="add_custom_attribute();" class="button button-primary" />&nbsp;
						<input type="button" name="remove_attribute" value="-" disabled onclick="remove_custom_attribute();" class="button button-primary" /></td>
					</tr>
					<tr>
						<td style="width:20%"><strong>Name</strong></td>
						<td style="width:40%"><strong>User Meta Data</strong></td>
					</tr>
					<?php $sp_attr_result = get_sp_attr_name_value($wpdb,$my_sp); ?>
				</table>
				<i><span style="color:red;">NOTE:</span>These are extra attributes that will be send in the SAML Response. Choose the User data you want to send in the Response from the dropdown. In the textbox to the left of the dropdown give an appropriate name you want the User data mapped to.</i>
				<br/><hr/>
				<div><h3>Group/Role Mapping (Optional)</h3></div>
				<div>
					<input type="checkbox" class="mo_idp_checkbox" name="idp_role_attribute" value='1' disabled />Check this option if you want to send User Roles as Group Attribute
					<div id="idp_role_attr_name" class="mo_idp_help_desc" <?php echo isset($sp_attr_result['groupMapName']) ? '' : 'hidden' ?>>
						<input type="text" style="margin-bottom:1%;" disabled name="mo_idp_role_mapping_name" placeholder="Name" value=""/>	
						<i><span style="margin-left:2%;color:red;">NOTE:</span> User Role will be mapped to this name in the SAML Response</i>
					</div>
				</div>
			</div>
			<br/>
			<table>
				<tr id="save_attributes">
					<input type="submit" name="submit" style="width:100px;margin-bottom:2%;" value="Save" class="button button-primary button-large" <?php if(!MO_IdP_Utility::mo_is_customer_registered()) echo 'disabled'?>/>
				</tr>
			</table>
		</form>
		<br/><br/>
	</div>
	
<?php
}

function mo_idp_troubleshoot_info(){
?>
	<div class="mo_idp_table_layout">
		<?php if(!MO_IdP_Utility::mo_is_customer_registered()) { ?>
			<div style="display:block;margin-top:10px;color:red;background-color:rgba(251, 232, 0, 0.15);padding:5px;border:solid 1px rgba(255, 0, 9, 0.36);">
			Please <a href="<?php echo add_query_arg( array('tab' => 'register'), $_SERVER['REQUEST_URI'] ); ?>">Register or Login with miniOrange</a> to enable WordPress IDP.
			</div>
		<?php } ?>
		<h3>Frequently Asked Questions</h3>
		If any section is not opening, press CTRL + F5 to clear cache.<br/><br/>
		<table class="mo_idp_settings_table">
			<tr>
				<td>
					<div style="font-size:17px;" class="mo_idp_help_title">Instructions to use miniOrange IDP plugin</div>
					<div hidden class="mo_idp_help_desc">
						<ul>
							<li>Step 1:&nbsp;&nbsp;&nbsp;&nbsp;Configure your Identity Provider by following the steps in <a href="<?php echo add_query_arg( array('tab' => 'settings'), $_SERVER['REQUEST_URI'] ); ?>">Identity Provider Tab</a>.</li>
							<li>Step 3:&nbsp;&nbsp;&nbsp;&nbsp;Enter appropriate values in the fields.</li>
							<li>Step 4:&nbsp;&nbsp;&nbsp;&nbsp;After saving your configuration, you will need to configure your Service Provider using information given in the <a href="<?php echo add_query_arg( array('tab' => 'sp_settings'), $_SERVER['REQUEST_URI'] ); ?>">Service Provider Tab</a>.</li>
							<li>Step 5:&nbsp;&nbsp;&nbsp;&nbsp;Add "Login to &lt;SP&gt;" widget to your WordPress page for IdP initiated Login.</li>
						</ul>
						For any further queries, please contact us.								
					</div>
				</td>
			</tr>
			<tr><td><br/><hr><br/></td></tr>
			<tr>
				<td>
					<div style="font-size:17px;" class="mo_idp_help_title">How to enable PHP cURL extension?</div>
					<div hidden class="mo_idp_help_desc">
						<ol>
							<li>Open php.ini file located under php installation folder.</li>
							<li>Search for extension=php_curl.dll.</li>
							<li>Uncomment it by removing the semi-colon(;) in front of it.</li>
							<li>Restart the Apache Server.</li>
						</ol>
						For any further queries, please contact us.								
					</div>
				</td>
			</tr>
			<tr><td><br/><hr><br/></td></tr>
			<tr>
				<td>
					<div style="font-size:17px;" class="mo_idp_help_title">What is SAML?</div>
					<div hidden class="mo_idp_help_desc">
						Security Assertion Markup Language(SAML) is an XML-based, open-standard data format for exchanging authentication and authorization data between parties, in particular, between an Identity Provider and a Service Provider. In our case, miniOrange is the Service Provider and the application which manages credentials is the Identity provider.
						<br/><br/>
						The SAML specification defines three roles: the Principal (in this case, your Wordpress user), the Identity provider (IdP), and the Service Provider (SP). The Service Provider requests and obtains an identity assertion from the Identity Provider. On the basis of this assertion, the service provider can make an access control decision - in other words it can decide whether to allow user to login to WordPress.
						<br/><br/>
						For more details please refer to this <a href="https://en.wikipedia.org/wiki/Security_Assertion_Markup_Language" target="_blank">SAML document</a>.
					</div>
				</td>
			</tr>
			<tr><td><br/><hr><br/></td></tr>
			<tr>
				<td>
					<div style="font-size:17px;" class="mo_idp_help_title">I get an error when trying to log in from my Service Provider</div>
					<div hidden class="mo_idp_help_desc">
						Here are the some frequent errors:
							<ol>
								<li><b>INVALID_SP</b>: This means you have not configured your Service Provider. Please use the information in the <a href="<?php echo add_query_arg( array('tab' => 'sp_settings'), $_SERVER['REQUEST_URI'] ); ?>">Service Provider Tab</a> to configure your SP.</li>
								<li><b>INVALID_ISSUER</b>: This means that you have NOT entered the correct Issuer or Entity ID value provided by your Service Provider.</li>
								<li><b>INVALID_SIGNATURE</b>: [For HTTP-POST Binding ] This means the signature in the SAML Request was not corrent. Make sure you provide the same certificate that you downloaded from your SP. If you have your SP's Metadata XML file then make sure you provide certificate enclosed in X509Certificate tag which has an attribute <b>use="signing"</b>.</li>
								<li><b>INVALID_REQUEST</b>: This means the time in the SAML request was greater than your current WordPress server time.</li>
							</ol>
						If you need help resolving the issue, please contact us using the support form and we will get back to you shortly.
					</div>
				</td>
			</tr>
			<tr>
				<tr><td><br/><hr><br/></td></tr>
				<td>
					<div style="font-size:17px;" class="mo_idp_help_title">SP-Initiated Login vs. IdP-Initiated Login</div>
					<div hidden class="mo_idp_help_desc">
						The user's identity(user profile and credentials) is managed by an Identity Provider(IdP) and the user wants to login to your WordPress site.
						<br/><br/>
						<b>IdP-Initiated Login</b>
						<br/>
						<ol>
							<li>The user initiates login through WordPress.</li>
							<li>The user is authenticated by WordPress.</li>
							<li>When the user clicks on the SP link a login response is sent by miniOrange IDP Plugin.</li>
							<li>With the help of the response, SP logs in the user.</li>
						</ol>
						<b>SP-Initiated Login</b>
						<br/>
						<ol>
							<li>The request to login is initiated through the SP.</li>
							<li>The user is redirected to WordPress login page.</li>
							<li>The user is authenticated by WordPress and a response is sent by miniOrange IDP Plugin to the SP.</li>
							<li>With the help of the response, SP logs in the user.</li>
						</ol>
					</div>
				</td>
			</tr>
		</table>
		<br/><br/>
	</div>
<?php	
}

function mo_idp_pricing_info(){
	$test='<table>
	    <tr>
	      <td class="mo_idp_pricing_text" colspan="3">$449 - One Time</td>
	    </tr>
		<tr><td class="mo_idp_pricing_text" colspan="3">+</td></tr>
	    <tr>
	    	<td class="mo_idp_pricing_text" style="width:35%;text-align:left;">Service Providers :</td>
	        <td style="width:30%">
	            <select class="mo-form-control" id="noOfSp" required>
	                <option>1 - $0
	                <option>2 - $25
	                <option>3 - $36
	                <option>4 - $48
	                <option>5 - $55
	                <option>10 - $85
	                <option>15 - $109
	                <option>20 - $120
	        	</select>
	        </td>
	        <td class="mo_idp_pricing_text">- One Time
	    </tr>
	   <tr>
	      <td class="mo_idp_pricing_text" colspan="3">+<td>
	   <tr>
	      <td class="mo_idp_pricing_text" style="width:45%;text-align:left;">Users :
	      <td style="width:30%">
	         <select class="mo-form-control user_pricing">
	            <option value="custom">Enter Users
	            <option selected>5 - $15
	            <option>10 - $30
	            <option>20 - $45
	            <option>30 - $60
	            <option>40 - $75
	            <option>50 - $90
	            <option>60 - $100
	            <option>70 - $110
	            <option>80 - $120
	            <option>90 - $130
	            <option>100 - $140
	            <option>150 - $177.5
	            <option>200 - $215
	            <option>250 - $245
	            <option>300 - $275
	            <option>350 - $300
	            <option>400 - $325
	            <option>450 - $347.5
	            <option>500 - $370
	            <option>600 - $395
	            <option>700 - $420
	            <option>800 - $445
	            <option>900 - $470
	            <option>1000 - $495
	            <option>2000 - $549
	            <option>3000 - $599
	            <option>4000 - $649
	            <option>5000 - $699
	            <option>10000 - $799
	            <option>20000 - $999
	            <option>Unlimited - Contact Us
	         </select>
	      	<td class="mo_idp_pricing_text">- per year
      </tr>
      <tr>
      	<td colspan="3">
      		<input type="text" hidden class="custom_users_premium" value="5" style="width: 45%; border: 1px solid rgb(204, 204, 204); border-radius: 4px; color: rgb(85, 85, 85); padding: 6px 12px;" placeholder="Enter number of users">
      		<input type="text" hidden id="custom_users_premium_price" value="$15" style="width: 45%; border: 1px solid rgb(204, 204, 204); border-radius: 4px; color: rgb(85, 85, 85); padding: 6px 12px;" disabled="" placeholder="Yearly Price" "="">
      	</td>
      </tr>
	</table>';
?>
	<div class="mo_idp_table_layout">
		<?php if(!MO_IdP_Utility::mo_is_customer_registered()) { ?>
			<div style="display:block;margin-top:10px;color:red;background-color:rgba(251, 232, 0, 0.15);padding:5px;border:solid 1px rgba(255, 0, 9, 0.36);">
			Please <a href="<?php echo add_query_arg( array('tab' => 'register'), $_SERVER['REQUEST_URI'] ); ?>">Register or Login with miniOrange</a> to enable WordPress IDP Plugin.
			</div>
		<?php } ?>
		
		<table class="mo_idp_pricing_table">
		<h2>LICENSING PLANS
			<span style="float:right">
				<input type="button" name="ok_btn" id="ok_btn" class="button button-primary button-large" value="OK, Got It" onclick="window.location.href='admin.php?page=mo_idp_settings&tab=settings'" />
			</span>
		<h2>
		<hr>
		<tr style="vertical-align:top;">
			<td>
				<div class="mo_idp_thumbnail mo_idp_pricing_free_tab" >
					<h3 class="mo_idp_pricing_header">Free</h3>
					<h4 class="mo_idp_pricing_sub_header">( You are automatically on this plan )<br/><br/></h4>
					<hr>
					<div class="mo_idp_pricing_text" style="height:270px;">$0 - One Time Payment</div>
					<hr>
					<p class="mo_idp_pricing_text">Features:</p>
					<p class="mo_idp_pricing_text" style="margin-bottom:60.8%;">
						Unlimited Authentications with 1 SP<br>
						SP initiated login<br><br>
					</p>
					<hr>
					<p class="mo_idp_pricing_text">Basic Support by Email</p>
				</div>
			</td>
			<td>
				<div class="mo_idp_thumbnail mo_idp_pricing_paid_tab">
					<h3 class="mo_idp_pricing_header">Do it yourself</h3>
						<h4 class="mo_idp_pricing_sub_header">
							<input type="button" style="margin-bottom:3.8%;" <?php if(!MO_IdP_Utility::mo_is_customer_registered()) echo 'disabled'; ?> class="button button-primary button-large" onclick="mo2f_upgradeform('wp_saml_idp_basic_plan')" value="Click here to upgrade"></input>*
						</h4>
					<hr>
					<!--<p class="mo_idp_pricing_text">For 1+ site</p><hr>-->
					<div style="height:270px;"><?php echo $test; ?></div>
					<hr>
					<p class="mo_idp_pricing_text">Features:</p>
					<p class="mo_idp_pricing_text" style="margin-bottom:11%;">
						Unlimited Authentications with Multiple SPs<br>
						SP and IDP initiated login<br>
						Customized Role Mapping<br>
						Customized Attribute Mapping<br>
						Signed Assertion<br/>
						Signed Response<br/>
						Encrypted Assertion<br/>
						HTTP-POST Binding<br/>
						Metadata XML File<br>
						Single Logout<br>
						Multisite Support<br/>
					</p>
					<hr>
					<p class="mo_idp_pricing_text">Basic Support By Email</p>
				</div>
			</td>
			<td>
				<div class="mo_idp_thumbnail mo_idp_pricing_free_tab">
					<h3 class="mo_idp_pricing_header">Premium</h3>
					<h4 class="mo_idp_pricing_sub_header">
						<input type="button" style="margin-bottom:3.8%;" <?php if(!MO_IdP_Utility::mo_is_customer_registered()) echo 'disabled'; ?> class="button button-primary button-large" onclick="mo2f_upgradeform('wp_saml_idp_premium_plan')" value="Click here to upgrade"></input>*
					</h4>
					<hr>
					<!--<p class="mo_idp_pricing_text">For 1+ site, Setup and Custom Work</p><hr>-->
					<div style="height:270px;">
						<?php echo $test; ?>
						<p  class="mo_idp_pricing_text">+</p>
						<p  class="mo_idp_pricing_text">$60 per hour - Setup Fee / Custom Work</p>
					</div>
					<hr>
					<p class="mo_idp_pricing_text">Features:</p>
					<p class="mo_idp_pricing_text">
						Unlimited Authentications with Multiple SPs<br>
						SP and IDP initiated login<br>
						Customized Role Mapping<br>
						Customized Attribute Mapping<br>
						Signed Assertion<br/>
						Signed Response<br/>
						Encrypted Assertion<br/>
						HTTP-POST Binding<br/>
						Metadata XML File<br>
						Single Logout<br>
						Multisite Support<br/>
						End to End Configuration **<br>
					</p><hr>
					<p class="mo_idp_pricing_text">Premium Support Plans</p>
				</div>
			</td>
		</tr>
		</table>
		<div id="disclaimer" style="margin-bottom:15px;">
			<h3>* Steps to Upgrade to Premium Plugin -</h3>
			<p>1. You will be redirected to miniOrange Login Console. Enter your password with which you created an account with us. After that you will be redirected to payment page.</p>
			<p>2. Enter you card details and complete the payment. On successful payment completion, you will see the link to download the premium plugin.</p>
			<p>3. Once you download the premium plugin, uninstall the free plugin and install the premium plugin. <br>
			<p>4. From this point on, do not update the plugin from the Wordpress store.</p>
			
			<h3>** End to End Integration - </h3>
			<p>We will setup a Conference Call / Gotomeeting and do end to end configuration for you. We provide services to do the configuration on your behalf. </p>
			<h3>Refund Policy -</h3>
			<p><b>At miniOrange, we want to ensure you are 100% happy with your purchase. If the premium plugin you purchased is not working as advertised and you've attempted to resolve any issues with our support team, which couldn't get resolved then we will refund the whole amount within 10 days of the purchase. Please email us at <a href="mailto:info@miniorange.com"><i>info@miniorange.com</i></a> for any queries regarding the return policy.</b></p>
			If you have any doubts regarding the licensing plans, you can mail us at <a href="mailto:info@miniorange.com"><i>info@miniorange.com</i></a> or submit a query using the <b>Support Form</b> in the plugin.
			<br>
		</div>
		</div>
		
		  <form style="display:none;" id="mocf_loginform" action="<?php echo get_option( 'mo_idp_host_name').'/moas/login'; ?>" target="_blank" method="post">
			<input type="email" name="username" value="<?php echo get_option('mo_idp_admin_email'); ?>" />
			<input type="text" name="redirectUrl" value="<?php echo get_option( 'mo_idp_host_name').'/moas/initializepayment'; ?>" />
			<input type="text" name="requestOrigin" id="requestOrigin"  />
		</form>
		<script>
			function mo2f_upgradeform(planType){
				jQuery('#requestOrigin').val(planType);
				jQuery('#mocf_loginform').submit();
			}
		</script>
	</div>
<?php 
}

// create the select box to choose which user data to be mapped
function get_user_data_select_box($user_info,$my_sp,$attr=null,$counter=0){
	echo '<td><select name="mo_idp_attribute_mapping_val['.$counter.']" ';
	echo 'disabled';
	echo '><option value="">Select User Data to be sent</option>';
	foreach ($user_info as $key => $value) {
		echo '<option value="'.$key.'"';
		if(!is_null($attr))
			echo $attr->mo_sp_attr_value===$key ? 'selected' : '';
		echo '>'.$key.'</option>';
	}
	echo '</tr></td>';
}

// create Service Provider Attribute Mapping Pairs
function get_sp_attr_name_value($wpdb,$my_sp){
	
	$current_user = wp_get_current_user();
	$result= Array();
	$user_info = get_user_meta($current_user->ID);
	foreach ($user_info as $key => $value)
		$user_info[$key] = $key;
	foreach ($current_user->data as $key => $value)
		$user_info[$key] = $key;
	$counter = 0;
	if(isset($my_sp) && !empty($my_sp)){
		$my_sp_attr = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mo_sp_attributes WHERE mo_sp_id = $my_sp->id" );
		if(isset($my_sp_attr) && !empty($my_sp_attr)){
			foreach ($my_sp_attr as $attr) {
				if($attr->mo_sp_attr_name!='groupMapName'){
					echo '<tr id="row_'.$counter.'">';
					echo '<td><input type="text" disabled required name="mo_idp_attribute_mapping_name['.$counter.']" placeholder="Name" value="'.$attr->mo_sp_attr_name.'"/></td>';
					get_user_data_select_box($user_info,$my_sp,$attr,$counter);
					$counter+=1;
				}else{
					$result['groupMapName'] = $attr->mo_sp_attr_value;
				}
			}
		}else{
			echo '<tr id="row_0"><td><input type="text" disabled required name="mo_idp_attribute_mapping_name[0]" placeholder="Name"/></td>';
			get_user_data_select_box($user_info,$my_sp);
		}
	}
	$result['user_info'] = $user_info;
	$result['counter']	 = $counter;
	return $result;
}