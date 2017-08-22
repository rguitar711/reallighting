<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property
//of Internet Business Solutions SL. Any use, reproduction, disclosure or copying
//of any kind without the express and written permission of Internet Business 
//Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
$isvsadmindir=TRUE;
include 'db_conn_open.php';
include 'inc/languagefile.php';
include 'includes.php';
include 'inc/incemail.php';
include 'inc/incfunctions.php';
if(@$htmlemails==TRUE) $emlNl = '<br />'; else $emlNl="\n";
$emailtxt='';

class Amazon_FPS_SignatureException extends Exception {}

class Amazon_FPS_SignatureUtilsForOutbound{
	const SIGNATURE_KEYNAME = "signature";
	const SIGNATURE_METHOD_KEYNAME = "signatureMethod";
	const SIGNATURE_VERSION_KEYNAME = "signatureVersion";
	const SIGNATURE_VERSION_1 = "1";
	const SIGNATURE_VERSION_2 = "2";
	const CERTIFICATE_URL_KEYNAME = "certificateUrl";
	const FPS_PROD_ENDPOINT = 'https://fps.amazonaws.com/';
	const FPS_SANDBOX_ENDPOINT = 'https://fps.sandbox.amazonaws.com/';
	const USER_AGENT_IDENTIFIER = 'SigV2_MigrationSampleCode_PHP-2010-09-13';
	//Your AWS access key
	private $aws_access_key;

	//Your AWS secret key. Required only for ipn or return url verification signed using signature version1.	
	private $aws_secret_key;

	public function __construct($aws_access_key = null, $aws_secret_key = null) {
		$this->aws_access_key = $aws_access_key;
		$this->aws_secret_key = $aws_secret_key;
	}
	/**
	 * Validates the request by checking the integrity of its parameters.
	 * @param parameters - all the http parameters sent in IPNs or return urls.
	 * @param urlEndPoint should be the url which recieved this request.
	 * @param httpMethod should be either POST (IPNs) or GET (returnUrl redirections)
	 */
	public function validateRequest(array $parameters, $urlEndPoint, $httpMethod){
		$signatureVersion = $parameters[self::SIGNATURE_VERSION_KEYNAME];
		if(self::SIGNATURE_VERSION_2==$signatureVersion){
			return $this->validateSignatureV2($parameters, $urlEndPoint, $httpMethod);
		}else{
			return $this->validateSignatureV1($parameters);
		}
	}
	/**
	 * Verifies the signature using HMAC and your secret key. 
	 */
	private function validateSignatureV1(array $parameters){
	if(isset($parameters[self::SIGNATURE_KEYNAME])){
		$signatureKey = self::SIGNATURE_KEYNAME;
	}else{
		throw new Amazon_FPS_SignatureException("Signature not present in parameter list"); 
	}
	$signature = $parameters[$signatureKey];
	unset($parameters[$signatureKey]);
		//We should not include signature while calculating string to sign.
	$stringToSign = self::_calculateStringToSignV1($parameters);
		//We should include signature back to array after calculating string to sign.
	$parameters[$signatureKey] = $signature;
			
		return $signature==base64_encode(hash_hmac('sha1', $stringToSign, $this->aws_secret_key, true));
	}
	/**
	 * Verifies the signature. 
	 * Only default algorithm OPENSSL_ALGO_SHA1 is supported.
	 */
	private function validateSignatureV2(array $parameters, $urlEndPoint, $httpMethod){
		$signature = $parameters[self::SIGNATURE_KEYNAME];
		if (!isset($signature)) {
			throw new Amazon_FPS_SignatureException("'signature' is missing from the parameters.");
		}
		$signatureMethod = $parameters[self::SIGNATURE_METHOD_KEYNAME];
		if (!isset($signatureMethod)) {
			throw new Amazon_FPS_SignatureException("'signatureMethod' is missing from the parameters.");
		}
		$signatureAlgorithm = self::getSignatureAlgorithm($signatureMethod);
		if (!isset($signatureAlgorithm)) {
			throw new Amazon_FPS_SignatureException("'signatureMethod' present in parameters is invalid. Valid values are: RSA-SHA1");
		}
		$certificateUrl = $parameters[self::CERTIFICATE_URL_KEYNAME];
		if (!isset($certificateUrl)) {
			throw new Amazon_FPS_SignatureException("'certificateUrl' is missing from the parameters.");
		}
		elseif((stripos($parameters[self::CERTIFICATE_URL_KEYNAME], self::FPS_PROD_ENDPOINT) !== 0) 
			&& (stripos($parameters[self::CERTIFICATE_URL_KEYNAME], self::FPS_SANDBOX_ENDPOINT) !== 0)){
			throw new Amazon_FPS_SignatureException('The `certificateUrl` value must begin with ' . self::FPS_PROD_ENDPOINT . ' or ' . self::FPS_SANDBOX_ENDPOINT . '.');
		}
		$verified = $this->verifySignature($parameters, $urlEndPoint);
		if (!$verified){
		//throw new Amazon_FPS_SignatureException('Certificate could not be verified by the FPS service');
		}
		return $verified;
	}
	private function httpsRequest($url){
		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL, $url);
		curl_setopt($curlHandle, CURLOPT_FILETIME, false);
		curl_setopt($curlHandle, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curlHandle, CURLOPT_CAINFO, getcwd().'/ca-bundle.crt');
		curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($curlHandle, CURLOPT_MAXREDIRS, 0);
		curl_setopt($curlHandle, CURLOPT_HEADER, true);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlHandle, CURLOPT_NOSIGNAL, true);
		curl_setopt($curlHandle, CURLOPT_USERAGENT, self::USER_AGENT_IDENTIFIER);
		// Handle the encoding if we can.
		if (extension_loaded('zlib')){
			curl_setopt($curlHandle, CURLOPT_ENCODING, '');
		}
		// Execute the request
		$response = curl_exec($curlHandle);
		// Grab only the body
		$headerSize = curl_getinfo($curlHandle, CURLINFO_HEADER_SIZE);
		$responseBody = substr($response, $headerSize);
		// Close the cURL connection
		curl_close($curlHandle);
		// Return the public key
		return $responseBody;
	}
	/**
	 * Method: verify_signature
	 */
	private function verifySignature($parameters, $urlEndPoint){
		// Switch hostnames
		if (stripos($parameters[self::CERTIFICATE_URL_KEYNAME], self::FPS_SANDBOX_ENDPOINT) === 0){
			$fpsServiceEndPoint = self::FPS_SANDBOX_ENDPOINT;
		}
		elseif (stripos($parameters[self::CERTIFICATE_URL_KEYNAME], self::FPS_PROD_ENDPOINT) === 0){
			$fpsServiceEndPoint = self::FPS_PROD_ENDPOINT;
		}

		$url = $fpsServiceEndPoint . '?Action=VerifySignature&UrlEndPoint=' . rawurlencode($urlEndPoint);

		$queryString = rawurlencode(str_replace('+','%20',http_build_query($parameters, '', '&')));
		//$queryString = str_replace(array('%2F', '%2B'), array('%252F', '%252B'), $queryString);
		//print str_replace('&','&amp;<br />',urldecode($queryString))."<br />";
		$url .= '&HttpParameters=' . $queryString . '&Version=2008-09-17';
		//print "URL is: " . str_replace('&','&amp;',$url)."<br>";
		$response = $this->httpsRequest($url);
		$xml = new SimpleXMLElement($response);
		$result = (string) $xml->VerifySignatureResult->VerificationStatus;

		return ($result === 'Success');
	}
	/**
	 * Calculate String to Sign for SignatureVersion 1
	 * @param array $parameters request parameters
	 * @return String to Sign
	 */
	private static function _calculateStringToSignV1(array $parameters) {
		$data = '';
		uksort($parameters, 'strcasecmp');
		foreach ($parameters as $parameterName => $parameterValue) {
			$data .= $parameterName . $parameterValue;
		}
		return $data;
	}
	private static function getSignatureAlgorithm($signatureMethod) {
		if ("RSA-SHA1"==$signatureMethod) {
			return OPENSSL_ALGO_SHA1;
		}
		return null;
	}

}
function already_authorized($tid){
	$isalready=FALSE;
	$sSQL = "SELECT ordAuthNumber,ordAuthStatus FROM orders WHERE ordID='" . escape_string($tid) . "'";
	$result = ect_query($sSQL) or ect_error();
	if($rs = ect_fetch_assoc($result)){
		if(trim($rs['ordAuthNumber'])!=''&&trim($rs['ordAuthNumber'])!='no ipn'&&trim($rs['ordAuthNumber'])!='CHECK MANUALLY')$isalready=TRUE;
	}
	if($isalready&&@$digidownloademail!=''){
		if(substr($rs['ordAuthStatus'],0,8)=='Pending:'){
			$sSQL="SELECT cartProdID FROM products INNER JOIN cart ON products.pID=cart.cartProdID INNER JOIN orders ON cart.cartOrderID=orders.ordID WHERE cartOrderID='" . escape_string($tid) . " AND pDownload<>''";
			$result = ect_query($sSQL) or ect_error();
			if(ect_num_rows($result)>0) $isalready=FALSE;
		}
	}
	ect_free_result($result);
	return($isalready);
}
$alreadygotadmin = getadminsettings();
$isamazonpayment = FALSE;
if(trim(@$_POST['transactionId'])!='' && trim(@$_POST['status'])!='' && trim(@$_POST['referenceId'])!='' && trim(@$_POST['signature'])!='') $isamazonpayment=TRUE;
if($isamazonpayment){
	getpayprovdetails(21,$data1,$data2,$data3,$demomode,$ppmethod);
	$ordID = str_replace("'", '', @$_POST['referenceId']);
	$Txn_id = str_replace("'", '', @$_POST['transactionId']);
	$avs = '';
	$cvv = '';
	$receipt_id = '';
	$sigversion=1;
	if(@$_POST['signatureVersion']=='2') $sigversion=2;
	$emailtxt.='Signature version: ' . $sigversion . $emlNl;
	if($sigversion==2){
		$utils = new Amazon_FPS_SignatureUtilsForOutbound($data1, $data2);
		if(TRUE){
			foreach($_POST as $key=>$val){
				$params[$key]=unstripslashes($_POST[$key]);
			}
		}else{
			$params['transactionId'] = unstripslashes(@$_POST['transactionId']);
			$params['transactionDate'] = unstripslashes(@$_POST['transactionDate']);
			$params['status'] = unstripslashes(@$_POST['status']);
			$params['notificationType'] = unstripslashes(@$_POST['notificationType']);
			$params['callerReference'] = unstripslashes(@$_POST['callerReference']);
			$params['operation'] = unstripslashes(@$_POST['operation']);
			$params['transactionAmount'] = unstripslashes(@$_POST['transactionAmount']);
			$params['buyerName'] = unstripslashes(@$_POST['buyerName']);
			$params['paymentMethod'] = unstripslashes(@$_POST['paymentMethod']);
			$params['paymentReason'] = unstripslashes(@$_POST['paymentReason']);
			$params['recipientEmail'] = unstripslashes(@$_POST['recipientEmail']);
			$params['signatureMethod'] = unstripslashes(@$_POST['signatureMethod']);
			$params['signatureVersion'] = unstripslashes(@$_POST['signatureVersion']);
			$params['certificateUrl'] = unstripslashes(@$_POST['certificateUrl']);
			$params['signature'] = unstripslashes(@$_POST['signature']);
		}
		$validsig=$utils->validateRequest($params, $storeurl.'vsadmin/ppconfirm.php', 'POST');
		$emailtxt.='validsig: ' . $validsig . $emlNl;
	}else{
		foreach(@$_POST as $key=>$val){
			if(! ($key=='signature')) $sigarr[] .= $key.unstripslashes($val);
		}
		asort($sigarr, SORT_STRING);
		$sigchk='';
		foreach($sigarr as $val){
			$sigchk .= $val;
		}
		$thesig = base64_encode(CalcHmacSha1($sigchk,$data2));
		$validsig=($thesig==trim(@$_POST['signature']));
	}
	if((@$_POST['status']=='PS' || @$_POST['status']=='PR' || @$_POST['status']=='PI') && $validsig && is_numeric($ordID)){
		$do_send_emails = ! already_authorized($ordID);
		if(@$_POST['status']=='PR') $authstatus='Pending: Settle'; else $authstatus='';
		if(@$_POST['status']=='PI') $authstatus='Pending: Review';
		ect_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'") or ect_error();
		ect_query("UPDATE orders SET ordAVS='".$avs."',ordCVV='".$cvv."',ordStatus=3,ordAuthNumber='".$Txn_id."',ordAuthStatus='".$authstatus."',ordTransID='".$receipt_id."' WHERE ordPayProvider=21 AND ordID='" . escape_string($ordID) . "'") or ect_error();
		if($do_send_emails) do_order_success($ordID,$emailAddr,$sendEmail,FALSE,TRUE,TRUE,TRUE);
	}
}else{
	$req = 'cmd=_notify-validate';
	foreach ($_POST as $key => $value) {
	  $value = urlencode(stripslashes($value));
	  $req .= "&$key=$value";
	}
	// post back to PayPal system to validate
	$header = "POST /cgi-bin/webscr HTTP/1.1\r\n" .
		"Content-Type: application/x-www-form-urlencoded\r\n" .
		"Host: www.paypal.com\r\n" .
		"Connection: close\r\n\r\n";
	// assign posted variables to local variables
	$Receiver_email = @$_POST['receiver_email'];
	$Item_number = @$_POST['item_number'];
	$invoice = @$_POST['invoice'];
	$Payment_status = @$_POST['payment_status'];
	$mc_gross = @$_POST['mc_gross'];
	$Txn_id = @$_POST['txn_id'];
	$Payer_email = @$_POST['payer_email'];
	$ordID = ($invoice!=''?$invoice:trim(@$_POST['custom']));
	$receipt_id = trim(@$_POST['receipt_id']);
	$address_status = strtolower(trim(@$_POST['address_status']));
	$pending_reason = trim(@$_POST['pending_reason']);
	if($address_status=='confirmed')
		$avs = 'Y';
	elseif($address_status=='unconfirmed')
		$avs = 'N';
	else
		$avs = 'U';
	$payer_status = strtolower(trim(@$_POST['payer_status']));
	if($payer_status=='verified')
		$cvv = 'Y';
	elseif($payer_status=='unverified')
		$cvv = 'N';
	else
		$cvv = 'U';
	$success = FALSE;
	$res = '';
	if(trim(@$_POST['txn_type'])=="express_checkout" && trim(@$_POST['parent_txn_id'])!=''){
		$sSQL = "SELECT ordID FROM orders WHERE ordDate>'" . date('Y-m-d', time()-(31*60*60*24)) . "' AND ordAuthNumber='" . escape_string(@$_POST['parent_txn_id']) . "'";
		$result = ect_query($sSQL) or ect_error();
		if($rs = ect_fetch_assoc($result)){
			$ordID = $rs['ordID'];
		}
		ect_free_result($result);
	}
	if((strpos($ordID,':')===FALSE && is_numeric($ordID)) || getget('ppdebug')=='true'){ // Otherwise PayPal Express Payment
		if(! is_numeric($ordID) || getget('ppdebug')=='true') $ordID=0;
		$payprov=1;
		$sSQL = "SELECT ordPayProvider FROM orders WHERE ordID='" . escape_string($ordID) . "'";
		$result = ect_query($sSQL) or ect_error();
		if($rs = ect_fetch_assoc($result))
			$payprov=$rs['ordPayProvider'];
		ect_free_result($result);
		getpayprovdetails($payprov,$data1,$data2,$data3,$demomode,$ppmethod);
		if($demomode) $sandbox = '.sandbox'; else $sandbox = '';
		$emailtxt .= 'SANDBOX: ' . $sandbox . $emlNl;
		if(@$usefsockforpaypalipn){
			$header = "POST /cgi-bin/webscr HTTP/1.1\r\n" .
			"Content-Type: application/x-www-form-urlencoded\r\n" .
			"Content-Length: " . strlen($req) . "\r\n" .
			"Host: www.paypal.com\r\n" .
			"Connection: close\r\n\r\n";
			$fp = fsockopen ('www' . $sandbox . '.paypal.com', 80, $errno, $errstr, 30);
			if (!$fp){
				$curlerrormsg = "$errstr ($errno)";
				$emailtxt .= 'Ecommerce Plus PayPal IPN Error: ' . $curlerrormsg . $emlNl;
				if(@$_GET['ppdebug']=='true') print $curlerrormsg . '<br />';
				$debugmode=TRUE;
			}else{
				fputs($fp, $header . $req);
				while(!feof($fp)){
					$res = fgets($fp, 1024);
					if(strcmp(trim($res), 'VERIFIED')==0 && ($ordID!='')){
						$success = TRUE;
					}elseif(strcmp(trim($res), 'INVALID')==0){
						; // log for manual investigation
					}else{
						if(@$debugmode==TRUE) print $res; // error
					}
				}
				fclose ($fp);
			}
		}else{
			if(@$pathtocurl!=''){
				exec($pathtocurl . ' --data-binary ' . escapeshellarg($req) . ' https://www' . $sandbox . '.paypal.com/cgi-bin/webscr', $res, $retvar);
				$res = trim(implode('',$res));
			}else{
				if(!$ch = curl_init()) {
					$success = false;
					$curlerrormsg = 'cURL package not installed in PHP';
					$emailtxt .= 'Ecommerce Plus PayPal IPN Error: ' . $curlerrormsg . $emlNl;
					if(@$_GET['ppdebug']=='true') print $curlerrormsg . '<br />';
					$debugmode=TRUE;
				}else{
					curl_setopt($ch, CURLOPT_URL,'https://www' . $sandbox . '.paypal.com/cgi-bin/webscr'); 
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					if(@$curlproxy!=''){ 
						curl_setopt($ch, CURLOPT_PROXY, $curlproxy);
					}
					$res = curl_exec($ch);
					if(curl_error($ch)!=''){
						$curlerrormsg = 'Error with cURL installation: ' . curl_error($ch);
						$emailtxt .= 'Ecommerce Plus PayPal IPN Error: ' . $curlerrormsg . $emlNl;
						if(@$_GET['ppdebug']=='true') print $curlerrormsg . '<br />';
						$debugmode=TRUE;
					}
					curl_close($ch);
				}
			}
		}
		if(strcmp(trim($res), 'VERIFIED')==0 && ($ordID!='')){
			$success = TRUE;
		}elseif(strcmp(trim($res), 'INVALID')==0){
			; // log for manual investigation
		}
		if(@$debugmode==TRUE) $emailtxt .= 'RESULT: ' . $res . $emlNl;
		if(@$_GET['ppdebug']=='true') print 'RESULT: ' . $res . '<br />';
	}
	if($success){
		// check the payment_status is Completed
		// check that txn_id has not been previously processed
		// check that receiver_email is an email address in your PayPal account process payment
		$amount = 0;
		$orderexists=TRUE;
		$sSQL="SELECT ordShipping,ordStateTax,ordCountryTax,ordHandling,ordTotal,ordDiscount FROM orders WHERE ordID='" . escape_string($ordID) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result))
			$amount=($rs['ordShipping']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordTotal']+$rs['ordHandling'])-$rs['ordDiscount'];
		else
			$orderexists=FALSE;
		ect_free_result($result);
		if($orderexists){
			if($Payment_status=='Completed' || $Payment_status=='Pending'){
				ect_query("UPDATE orders SET ordAVS='". $avs . "',ordCVV='" . $cvv . "' WHERE ordPayProvider=1 AND ordID='" . escape_string($ordID) . "'") or ect_error();
			}
			if(($Payment_status=='Completed' || $Payment_status=='Pending') && $amount > ((double)$mc_gross+0.01)){
				ect_query("UPDATE cart SET cartCompleted=2 WHERE cartCompleted=0 AND cartOrderID='" . escape_string($ordID) . "'") or ect_error();
				ect_query("UPDATE orders SET ordAuthNumber='" . $Txn_id . "',ordAuthStatus='Pending: Total paid " . @$_POST['mc_currency'] . " " . $mc_gross . "' WHERE ordPayProvider IN (1,18,19,22) AND ordID='" . escape_string($ordID) . "'") or ect_error();
			}elseif($Payment_status=='Completed'){
				$do_send_emails = ! already_authorized($ordID);
				ect_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'") or ect_error();
				ect_query("UPDATE orders SET ordAuthNumber='" . $Txn_id . "',ordAuthStatus='',ordTransID='" . $receipt_id . "' WHERE ordPayProvider IN (1,18,19,22) AND ordID='" . escape_string($ordID) . "'") or ect_error();
				ect_query("UPDATE orders SET ordStatus=3 WHERE ordStatus<3 AND ordPayProvider IN (1,18,19,22) AND ordID='" . escape_string($ordID) . "'") or ect_error();
				if($do_send_emails) do_order_success($ordID,$emailAddr,$sendEmail,FALSE,TRUE,TRUE,TRUE);
			}elseif($Payment_status=='Pending'){
				if($pending_reason=='authorization') $pending_reason='Capture';
				$do_send_emails = ! already_authorized($ordID);
				ect_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'") or ect_error();
				ect_query("UPDATE orders SET ordStatus=3,ordAuthNumber='" . $Txn_id . "',ordAuthStatus='Pending: " . escape_string($pending_reason) . "' WHERE ordPayProvider IN (1,18,19,22) AND ordID='" . escape_string($ordID) . "'") or ect_error();
				if($do_send_emails) do_order_success($ordID,$emailAddr,$sendEmail,FALSE,TRUE,TRUE,TRUE);
			}
		}
	}
}
if(@$debugmode==TRUE){
	foreach(@$_POST as $key => $val){
		$emailtxt .= $key . ' : ' . $val . $emlNl;
	}
	dosendemail($emailAddr, $emailAddr, '', 'ppconfirm.php debug', $emailtxt);
}
?>