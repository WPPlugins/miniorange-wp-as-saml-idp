<?php
/** miniOrange enables user to log in through mobile authentication as an additional layer of security over password.
    Copyright (C) 2015  miniOrange

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
* @package 		miniOrange OAuth
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

class MO_IdP_Utility{

	public $email;
	public $phone;

	private $defaultCustomerKey = "16555";
	private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
	
	public static function get_hidden_phone($phone){
		$hidden_phone = 'xxxxxxx' . substr($phone,strlen($phone) - 3);
		return $hidden_phone;
	}
	
	public static function mo_check_empty_or_null( $value ) {
		if( ! isset( $value ) || empty( $value ) ) {
			return true;
		}
		return false;
	}
	
	public static function mo_is_curl_installed() {
		if  (in_array  ('curl', get_loaded_extensions())) {
			return 1;
		} else 
			return 0;
	}

	public static function mo_curpageurl() {
		$pageURL = 'http';
		
		if ((isset($_SERVER["HTTPS"])) && ($_SERVER["HTTPS"] == "on"))
			$pageURL .= "s";
			
		$pageURL .= "://";

		if ($_SERVER["SERVER_PORT"] != "80")
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			
		else
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		
		if ( function_exists('apply_filters') ) apply_filters('wppb_curpageurl', $pageURL);

        return $pageURL;
	}
	
	public static function mo_check_number_length($token){
		if(is_numeric($token)){
			if(strlen($token) >= 4 && strlen($token) <= 8){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	public static function mo_get_hiden_email($email){
        if(!isset($email) || trim($email)===''){
			return "";
		}
		$emailsize = strlen($email);
		$partialemail = substr($email,0,1);
		$temp = strrpos($email,"@");
		$endemail = substr($email,$temp-1,$emailsize);
		for($i=1;$i<$temp;$i++){
			$partialemail = $partialemail . 'x';
		}
		$hiddenemail = $partialemail . $endemail;
               
        return $hiddenemail;
    }
	
	public static function check_if_request_is_from_mobile_device($useragent){
		if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
			return true;
		}else{
			return false;
		}
	}

	public static function mo_is_customer_registered(){
		$email 			= get_option('mo_idp_admin_email');
		$customerKey 	= get_option('mo_idp_admin_customer_key');
		if( ! $email || ! $customerKey || ! is_numeric( trim( $customerKey ) ) ) {
			return 0;
		} else {
			return 1;
		}
	}

	private function get_ip_address(){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		   $ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		   $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		   $ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	function create_customer(){
		$url = get_option('mo_idp_host_name') . '/moas/rest/customer/add';
		$ch = curl_init( $url );
		
		$current_user = wp_get_current_user();
		$this->email 		= get_option('mo_idp_admin_email');
		$this->phone 		= get_option('mo_idp_admin_phone');
		$password 			= get_option('mo_idp_admin_password');
		$companyName        = get_option( 'mo_idp_company_name' );
        $firstName          = get_option( 'mo_idp_first_name' );
        $lastName           = get_option( 'mo_idp_last_name' );	

		$fields = array(
			'companyName' => $companyName,
			'areaOfInterest' => 'WP IDP Plugin',
			'firstname'	=> $firstName,
			'lastname'	=> $lastName,
			'email'		=> $this->email,
			'phone'		=> $this->phone,
			'password'	=> $password
		);
		$field_string = json_encode($fields);

		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'charset: UTF - 8', 'Authorization: Basic' ) );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string );
		$content = curl_exec( $ch );

		if( curl_errno( $ch ) ){
			echo 'Request Error:' . curl_error( $ch );
			exit();
		}

		curl_close( $ch );
		return $content;
	}

	function get_customer_key() {
		$url 	= get_option('mo_idp_host_name') . "/moas/rest/customer/key";
		$ch 	= curl_init( $url );
		$email 	= get_option("mo_idp_admin_email");

		$password = get_option("mo_idp_admin_password");

		$fields = array(
			'email' 	=> $email,
			'password' 	=> $password
		);
		$field_string = json_encode( $fields );

		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'charset: UTF - 8', 'Authorization: Basic' ) );
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		$content = curl_exec( $ch );
		if( curl_errno( $ch ) ){
			echo 'Request Error:' . curl_error( $ch );
			exit();
		}
		curl_close( $ch );

		return $content;
	}

	function check_customer() {
			$url 	= get_option('mo_idp_host_name') . "/moas/rest/customer/check-if-exists";
			$ch 	= curl_init( $url );
			$email 	= get_option("mo_idp_admin_email");

			$fields = array(
				'email' 	=> $email
			);
			$field_string = json_encode( $fields );

			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $ch, CURLOPT_ENCODING, "" );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

			curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'charset: UTF - 8', 'Authorization: Basic' ) );
			curl_setopt( $ch, CURLOPT_POST, true);
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
			$content = curl_exec( $ch );
			if( curl_errno( $ch ) ){
				echo 'Request Error:' . curl_error( $ch );
				exit();
			}
			curl_close( $ch );

			return $content;
	}

	function send_otp_token($authType,$email='',$phone=''){
			$url = get_option('mo_idp_host_name') . '/moas/api/auth/challenge';
			$ch = curl_init($url);
			if($this->mo_check_empty_or_null(get_option('mo_idp_admin_customer_key')))
				$customerKey =  $this->defaultCustomerKey;
			else
				$customerKey = get_option('mo_idp_admin_customer_key');
			if($this->mo_check_empty_or_null(get_option('mo_idp_admin_api_key')))
				$apiKey =   $this->defaultApiKey;
			else
				$apiKey =  get_option('mo_idp_admin_api_key');
			
			/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
			$currentTimeInMillis = round(microtime(true) * 1000);

			/* Creating the Hash using SHA-512 algorithm */
			$stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
			$hashValue = hash("sha512", $stringToHash);

			$customerKeyHeader = "Customer-Key: " . $customerKey;
			$timestampHeader = "Timestamp: " . $currentTimeInMillis;
			$authorizationHeader = "Authorization: " . $hashValue;
			if($authType == 'EMAIL') {
				$fields = array(
				'customerKey' => $customerKey,
				'email' => $email,
				'authType' => 'EMAIL',
				'transactionName' => 'WP IDP Plugin'
				);
			}else if($authType == 'SMS'){
				$fields = array(
				'customerKey' => $customerKey,
				'phone' => $phone,
				'authType' => 'SMS',
				'transactionName' => 'WP IDP Plugin'
			);
			}
			$field_string = json_encode($fields);
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $ch, CURLOPT_ENCODING, "" );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

			curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
												$timestampHeader, $authorizationHeader));
			curl_setopt( $ch, CURLOPT_POST, true);
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
			$content = curl_exec($ch);

			if(curl_errno($ch)){
				echo 'Request Error:' . curl_error($ch);
			    exit();
			}
			curl_close($ch);
			return $content;
		}

		function validate_otp_token($transactionId,$otpToken){
			$url = get_option('mo_idp_host_name') . '/moas/api/auth/validate';
			$ch = curl_init($url);

			if($this->mo_check_empty_or_null(get_option('mo_idp_admin_customer_key')))
				$customerKey =  $this->defaultCustomerKey;
			else
				$customerKey = get_option('mo_idp_admin_customer_key');
			if($this->mo_check_empty_or_null(get_option('mo_idp_admin_api_key')))
				$apiKey =  $this->defaultApiKey;
			else
				$apiKey =  get_option('mo_idp_admin_api_key');

			$username = get_option('mo_idp_admin_email');

			/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
			$currentTimeInMillis = round(microtime(true) * 1000);

			/* Creating the Hash using SHA-512 algorithm */
			$stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
			$hashValue = hash("sha512", $stringToHash);

			$customerKeyHeader = "Customer-Key: " . $customerKey;
			$timestampHeader = "Timestamp: " . $currentTimeInMillis;
			$authorizationHeader = "Authorization: " . $hashValue;

			$fields = '';

				//*check for otp over sms/email
				$fields = array(
					'txId' => $transactionId,
					'token' => $otpToken,
				);

			$field_string = json_encode($fields);

			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $ch, CURLOPT_ENCODING, "" );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

			curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
												$timestampHeader, $authorizationHeader));
			curl_setopt( $ch, CURLOPT_POST, true);
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
			$content = curl_exec($ch);

			if(curl_errno($ch)){
				echo 'Request Error:' . curl_error($ch);
			   exit();
			}
			curl_close($ch);
			return $content;
	}

	function submit_contact_us( $email, $phone, $query ) {
			
			$current_user = wp_get_current_user();
			$query = '[WP IDP Plugin] ' . $query;
			$fields = array(
				'firstName'			=> $current_user->user_firstname,
				'lastName'	 		=> $current_user->user_lastname,
				'company' 			=> $_SERVER['SERVER_NAME'],
				'email' 			=> $email,
				'phone'				=> $phone,
				'query'				=> $query
			);
			$field_string = json_encode( $fields );

			$url = get_option('mo_idp_host_name') . '/moas/rest/customer/contact-us';

			$ch = curl_init( $url );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $ch, CURLOPT_ENCODING, "" );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

			curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'charset: UTF-8', 'Authorization: Basic' ) );
			curl_setopt( $ch, CURLOPT_POST, true);
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
			$content = curl_exec( $ch );

			if( curl_errno( $ch ) ){
				echo 'Request Error:' . curl_error( $ch );
				return false;
			}
			

			curl_close( $ch );

			return true;
	}
	
	function forgot_password($email){
		$url = get_option('mo_idp_host_name') . '/moas/rest/customer/password-reset';
		$ch = curl_init($url);
		
		/* The customer Key provided to you */
		$customerKey = get_option('mo_idp_admin_customer_key');
	
		/* The customer API Key provided to you */
		$apiKey = get_option('mo_idp_admin_api_key');
	
		/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
		$currentTimeInMillis = round(microtime(true) * 1000);
	
		/* Creating the Hash using SHA-512 algorithm */
		$stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
		$hashValue = hash("sha512", $stringToHash);
	
		$customerKeyHeader = "Customer-Key: " . $customerKey;
		$timestampHeader = "Timestamp: " . $currentTimeInMillis;
		$authorizationHeader = "Authorization: " . $hashValue;
		
		$fields = '';
	
			//*check for otp over sms/email
			$fields = array(
				'email' => $email
			);
		
		$field_string = json_encode($fields);
		
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
		
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader, 
											$timestampHeader, $authorizationHeader));
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt( $ch, CURLOPT_TIMEOUT, 20);
		$content = curl_exec($ch);
		
		if(curl_errno($ch)){
			return null;
		}
		curl_close($ch);
		return $content;
	}
}
?>