<?php
include_once dirname(__FILE__) . '/Utilities.php';
include_once dirname(__FILE__) . '/ReadResponse.php';
include_once dirname(__FILE__) . '/LogoutRequest.php';
include_once dirname(__FILE__) . '/AuthnRequest.php';
require_once dirname(__FILE__) . '/includes/lib/encryption.php';
require_once dirname(__FILE__) . '/GenerateResponse.php';

class Mo_idp{
	
	function __construct() {
		add_action( 'init', array( $this, 'mo_idp_validate' ));
		add_action( 'wp_login', array( $this,'mo_idp_handle_post_login') , 99);
	}

	function mo_idp_validate(){
		if(array_key_exists('SAMLRequest', $_REQUEST) && !empty($_REQUEST['SAMLRequest'])){
			$this->_read_saml_request($_REQUEST,$_GET);
		}elseif (array_key_exists('option', $_REQUEST) && $_REQUEST['option']==='testConfig'){
			$this->mo_idp_send_reponse($_REQUEST['acs'],$_REQUEST['issuer'],null);
		}elseif (array_key_exists('option', $_REQUEST) && $_REQUEST['option']==='saml_user_login'){
			global $wpdb;
			$spName = $_REQUEST['sp'];
			$my_sp = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}mo_sp_data WHERE mo_idp_sp_name = '$spName'" );
			if (isset($my_sp) && !empty($my_sp)){
				$this->mo_idp_send_reponse($my_sp->mo_idp_acs_url,$my_sp->mo_idp_sp_issuer,null);
			}
		}
	}

	function mo_idp_handle_post_login($login) {
		if(isset($_COOKIE['response_params'])){
			$response_params =  json_decode(stripslashes($_COOKIE['response_params']),true);
			if(strcmp( $response_params['moIdpsendResponse'], 'true') == 0) {
				$this->mo_idp_send_reponse($response_params['acs_url'],$response_params['audience'],$response_params['defaultRelayState'],$response_params['relayState'],$login,$response_params['requestID']);
			}
		}
	}

	private function _read_saml_request($REQUEST,$GET){
		$samlRequest = $REQUEST['SAMLRequest'];
		$relayState = '/';
		if(array_key_exists('RelayState', $REQUEST)) {
			$relayState = $REQUEST['RelayState'];
		}
		
		$samlRequest = base64_decode($samlRequest);
		if(array_key_exists('SAMLRequest', $GET) && !empty($GET['SAMLRequest'])) {
			$samlRequest = gzinflate($samlRequest);
		}
		
		$document = new DOMDocument();
		$document->loadXML($samlRequest);
		$samlRequestXML = $document->firstChild;
		$authnRequest = new AuthnRequest($samlRequestXML);

		$errors = '';
		if(strtotime($authnRequest->getIssueInstant()) >= time()+60)
			$errors.=__('<strong>INVALID_REQUEST: </strong>Request time is greater than the current time.<br/>');
		if($authnRequest->getVersion()!=='2.0')
			$errors.='We only support SAML 2.0! Please send a SAML 2.0 request.<br/>';
		
		global $wpdb;
		$acs = $authnRequest->getIssuer();
		$my_sp = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}mo_sp_data WHERE mo_idp_sp_issuer = '$acs'" );
		
		if(!isset($my_sp))
			$errors.=__('<strong>INVALID_SP: </strong>Service Provider is not configured. Please configure your Service Provider.<br/>');

		$defaultRelayState = '/';

		if(empty($errors)){
			$this->mo_idp_authorize_user($my_sp->mo_idp_acs_url,$authnRequest->getIssuer(),
					$defaultRelayState,$relayState,$authnRequest->getRequestID());
		} else{
			echo sprintf($errors);
			exit;
		}
	}

	private function mo_idp_authorize_user($acs_url,$audience,$defaultRelayState,$relayState,$requestID=null){
		if(is_user_logged_in()) {
			$this->mo_idp_send_reponse($acs_url,$audience,$defaultRelayState,$relayState,null,$requestID);
		} else {
			$saml_response_params = array('moIdpsendResponse' => "true" , "acs_url" => $acs_url , "audience" => $audience , "relayState" => $relayState, "requestID" => $requestID, "defaultRelayState" => $defaultRelayState );
			setcookie("response_params",json_encode($saml_response_params));
			$redirect_url = wp_login_url();
			wp_redirect($redirect_url);
			exit;
		}
	}

	private function mo_idp_send_reponse($acs_url,$audience,$defaultRelayState,$relayState=null,$user_login=null,$requestID=null){
		
		$current_user = wp_get_current_user();
		if(MO_IdP_Utility::mo_check_empty_or_null($current_user->ID) && !MO_IdP_Utility::mo_check_empty_or_null($user_login)){
			$current_user = get_user_by('login',$user_login);
		}
		$email = $current_user->user_email;
		$username = $current_user->user_login;

		$issuer = plugins_url('/',__FILE__);
		$relayState = !is_null($relayState) ? $relayState : '/';

		$saml_response_obj = new GenerateResponse($email,$username, $acs_url, $issuer, $audience,$requestID);
		$saml_response = $saml_response_obj->createSamlResponse();
		setcookie("response_params","");
		$this->_send_response($saml_response, $relayState,$acs_url);
	}

	private function _send_response($saml_response, $ssoUrl,$acs_url){
		$saml_response = base64_encode($saml_response);
		?>
		<form id="responseform" action="<?php echo $acs_url; ?>" method="post">
			<input type="hidden" name="SAMLResponse" value="<?php echo htmlspecialchars($saml_response); ?>" />
			<input type="hidden" name="RelayState" value="<?php echo $ssoUrl; ?>" />
		</form>
		<script>
			setTimeout(function(){
				document.getElementById('responseform').submit();
			}, 100);	
		</script>
	<?php
		exit;
	}

}
new Mo_idp();
?>