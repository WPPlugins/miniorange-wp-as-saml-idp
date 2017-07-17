<?php

	class GenerateResponse{
		
		private $xml;
		private $acsUrl;
		private $issuer;
		private $audience;
		private $username;
		private $email;
		private $my_sp;
		private $requestID;
		
		function __construct($email,$username, $acs_url, $issuer, $audience, $requestID){
			global $wpdb;
			$this->xml = new DOMDocument("1.0", "utf-8");
			$this->acsUrl = $acs_url;		
			$this->issuer = $issuer;		
			$this->audience = $audience;
			$this->email = $email;
			$this->username = $username;
			$this->requestID=$requestID;
			$this->my_sp = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}mo_sp_data WHERE mo_idp_acs_url = '$acs_url'" );
		}
		
		function createSamlResponse(){
			
			$response_params = $this->getResponseParams();

			//Create Response Element
			$resp = $this->createResponseElement($response_params);
			$this->xml->appendChild($resp);
			
			//Build Issuer
			$issuer = $this->buildIssuer();
			$resp->appendChild($issuer);
			
			//Build Status
			$status = $this->buildStatus();
			$resp->appendChild($status);
			$statusCode = $this->buildStatusCode();
			$status->appendChild($statusCode);
			
			//Build Assertion
			$assertion = $this->buildAssertion($response_params);
			$resp->appendChild($assertion);

			$samlResponse = $this->xml->saveXML();

			return $samlResponse;								
			
		}
		
		function getResponseParams(){
			$response_params = array();
			$time = time();
			$response_params['IssueInstant'] = str_replace('+00:00','Z',gmdate("c",$time));
			$response_params['NotOnOrAfter'] = str_replace('+00:00','Z',gmdate("c",$time+300));
			$response_params['NotBefore'] = str_replace('+00:00','Z',gmdate("c",$time-30));
			$response_params['AuthnInstant'] = str_replace('+00:00','Z',gmdate("c",$time-120));
			$response_params['SessionNotOnOrAfter'] = str_replace('+00:00','Z',gmdate("c",$time+3600*8));
			$response_params['ID'] = $this->generateUniqueID(40);
			$response_params['AssertID'] = $this->generateUniqueID(40);
			$response_params['Issuer'] = $this->issuer;
			$public_key = plugin_dir_path(__FILE__) . 'resources' . DIRECTORY_SEPARATOR . 'idp-signing.crt';
			$objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256,array( 'type' => 'public'));
			$objKey->loadKey($public_key, TRUE,TRUE);
			$response_params['x509'] = $objKey->getX509Certificate();
			return $response_params;
		}
		
		function createResponseElement($response_params){
			$resp = $this->xml->createElementNS('urn:oasis:names:tc:SAML:2.0:protocol','samlp:Response');
			$resp->setAttribute('ID',$response_params['ID']);
			$resp->setAttribute('Version','2.0');
			$resp->setAttribute('IssueInstant',$response_params['IssueInstant']);
			$resp->setAttribute('Destination',$this->acsUrl);
			if(!is_null($this->requestID))
				$resp->setAttribute('InResponseTo',$this->requestID);
			return $resp;
		}
		
		function buildIssuer(){
			$issuer = $this->xml->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion','saml:Issuer',$this->issuer);
			return $issuer;
		}
		
		function buildStatus(){
			$status = $this->xml->createElementNS('urn:oasis:names:tc:SAML:2.0:protocol','samlp:Status');
			return $status;
		}
		
		function buildStatusCode(){
			$statusCode = $this->xml->createElementNS('urn:oasis:names:tc:SAML:2.0:protocol','samlp:StatusCode');
			$statusCode->setAttribute('Value', 'urn:oasis:names:tc:SAML:2.0:status:Success');
			return $statusCode;
		}
		
		function buildAssertion($response_params){
			$assertion = $this->xml->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion','saml:Assertion');
			$assertion->setAttribute('ID',$response_params['AssertID']);
			$assertion->setAttribute('IssueInstant',$response_params['IssueInstant']);
			$assertion->setAttribute('Version','2.0');
			
			//Build Issuer
			$issuer = $this->buildIssuer($response_params);
			$assertion->appendChild($issuer);

			//Build Subject
			$subject = $this->buildSubject($response_params);
			$assertion->appendChild($subject);
			
			//Build Condition
			$condition = $this->buildCondition($response_params);
			$assertion->appendChild($condition);
			
			//Build AuthnStatement
			$authnstat = $this->buildAuthnStatement($response_params);
			$assertion->appendChild($authnstat);

			return $assertion;
		}
		
		function buildSubject($response_params){
			$subject = $this->xml->createElement('saml:Subject');
			$nameid = $this->buildNameIdentifier();
			$subject->appendChild($nameid);
			$confirmation = $this->buildSubjectConfirmation($response_params);
			$subject->appendChild($confirmation);
			return $subject;
		}
		
		function buildNameIdentifier(){
			if($this->my_sp->mo_idp_nameid_attr==="emailAddress")
				$nameid = $this->xml->createElement('saml:NameID',$this->email);
			else
				$nameid = $this->xml->createElement('saml:NameID',$this->username);
			$nameid->setAttribute('Format','urn:oasis:names:tc:SAML:'.$this->my_sp->mo_idp_nameid_format);
			//$nameid->setAttribute('SPNameQualifier',$this->audience);
			return $nameid;
		}
		
		function buildSubjectConfirmation($response_params){
			$confirmation = $this->xml->createElement('saml:SubjectConfirmation');
			$confirmation->setAttribute('Method','urn:oasis:names:tc:SAML:2.0:cm:bearer');
			$confirmationdata = $this->getSubjectConfirmationData($response_params);
			$confirmation->appendChild($confirmationdata);
			return $confirmation;
		}
		
		function getSubjectConfirmationData($response_params){
			$confirmationdata = $this->xml->createElement('saml:SubjectConfirmationData');
			$confirmationdata->setAttribute('NotOnOrAfter',$response_params['NotOnOrAfter']);
			$confirmationdata->setAttribute('Recipient',$this->acsUrl);
			if(!is_null($this->requestID))
				$confirmationdata->setAttribute('InResponseTo',$this->requestID);
			return $confirmationdata;
		}
		
		function buildCondition($response_params){
			$condition = $this->xml->createElement('saml:Conditions');
			$condition->setAttribute('NotBefore',$response_params['NotBefore']);
			$condition->setAttribute('NotOnOrAfter',$response_params['NotOnOrAfter']);
			
			//Build AudienceRestriction
			$audiencer = $this->buildAudienceRestriction();
			$condition->appendChild($audiencer);
			
			return $condition;
		}
		
		function buildAudienceRestriction(){
			$audiencer = $this->xml->createElement('saml:AudienceRestriction');
			$audience = $this->xml->createElement('saml:Audience',$this->audience);
			$audiencer->appendChild($audience);
			return $audiencer;
		}
		
		function buildAuthnStatement($response_params){
			$authnstat = $this->xml->createElement('saml:AuthnStatement');
			$authnstat->setAttribute('AuthnInstant',$response_params['AuthnInstant']);
			$authnstat->setAttribute('SessionIndex','_'.$this->generateUniqueID(30));
			$authnstat->setAttribute('SessionNotOnOrAfter',$response_params['SessionNotOnOrAfter']);
			
			$authncontext = $this->xml->createElement('saml:AuthnContext');
			$authncontext_ref = $this->xml->createElement('saml:AuthnContextClassRef','urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport');
			$authncontext->appendChild($authncontext_ref);
			$authnstat->appendChild($authncontext);
			
			return $authnstat;
		}
		
		function generateUniqueID($length) {
			$chars = "abcdef0123456789";
			$chars_len = strlen($chars);
			$uniqueID = "";
			for ($i = 0; $i < $length; $i++)
				$uniqueID .= substr($chars,rand(0,15),1);
			return 'a'.$uniqueID;
		}
		
	}