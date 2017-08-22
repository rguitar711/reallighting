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
$req='';
$success = true;
$emailtxt='';
if(@$htmlemails==TRUE) $emlNl = "<br>"; else $emlNl="\n";
$alreadygotadmin = getadminsettings();
if(@$_POST['nbx_merchant_reference']!='' && @$_POST['nbx_netbanx_reference']!='' && @$_POST['nbx_checksum']!='' && @$_POST['nbx_status']=='passed'){ // Netbanx
	$ordIDarr = explode('.', @$_POST['nbx_merchant_reference']);
	$ordID = $ordIDarr[0];
	if(is_numeric($ordID) && getpayprovdetails(15,$data1,$data2,$data3,$demomode,$ppmethod)){
		$Txn_id = @$_POST['nbx_netbanx_reference'];
		$thechecksum = @$_POST['nbx_checksum'];
		$checksumstring = @$_POST['nbx_payment_amount'] . @$_POST['nbx_currency_code'] . @$_POST['nbx_merchant_reference'] . @$_POST['nbx_netbanx_reference'] . $data2;
		$calculatedsum = sha1($checksumstring);
		if($thechecksum==$calculatedsum || trim($data2)==''){
			ect_query('UPDATE cart SET cartCompleted=1 WHERE cartOrderID=' . $ordID);
			ect_query("UPDATE orders SET ordAuthNumber='" . escape_string($Txn_id) . "',ordStatus=3,ordAVS='" . escape_string(@$_POST['nbx_houseno_auth'] . '-' . @$_POST['nbx_postcode_auth']) . "',ordCVV='" . escape_string(@$_POST['nbx_CVV_auth']) . "',ordAuthStatus='' WHERE ordPayProvider=15 AND ordID=" . $ordID);
			do_order_success(ordID,emailAddr,sendEmail,FALSE,TRUE,TRUE,TRUE);
			print 'NETBANXOK:1';
		}else
			$emailtxt .= 'Checksum mismatch: ' . $thechecksum . ' : ' . $calculatedsum . $emlNl . $checksumstring . $emlNl;
	}
}else{ // Nochex
	foreach ($_POST as $key => $value) {
	  $value = urlencode(stripslashes($value));
	  if($req != '') $req .= '&';
	  $req .= "$key=$value";
	}
	// assign posted variables to local variables
	$Txn_id = str_replace("'","",@$_POST['transaction_id']);
	$ordID = str_replace("'","",@$_POST['order_id']);
	$card_address_check = str_replace("'","",@$_POST['card_address_check']);
	$card_postcode_check = str_replace("'","",@$_POST['card_postcode_check']);
	$card_security_code = str_replace("'","",@$_POST['card_security_code']);
	if(@$usenewnochexcallback){
		$Receiver_email = str_replace("'","",@$_POST['merchant_ID']);
		$Payment_gross = str_replace("'","",@$_POST['gross_amount']);
		$Payer_email = str_replace("'","",@$_POST['email_address']);
		$apcurl = 'https://secure.nochex.com/callback';
	}else{
		$Receiver_email = str_replace("'","",@$_POST['to_email']);
		$Payment_gross = str_replace("'","",@$_POST['amount']);
		$Payer_email = str_replace("'","",@$_POST['from_email']);
		$apcurl = 'https://www.nochex.com/nochex.dll/apc/apc';
	}
	if(@$pathtocurl != ""){
		exec($pathtocurl . ' --data-binary ' . escapeshellarg($req) . ' ' . $apcurl, $res, $retvar);
		$res = implode("\n",$res);
	}else{
		if (!$ch = curl_init()) {
			$success = false;
			$errormsg = "cURL package not installed in PHP";
		}else{
			curl_setopt($ch, CURLOPT_URL, $apcurl); 
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
			if(curl_error($ch) != "") print "Error with cURL installation: " . curl_error($ch) . "<br />";
			curl_close($ch);
		}
	}
	if($success){
		// print str_replace("<","<br />&lt;",$res) . "<br />\n";
		if(strcmp ($res, "AUTHORISED")==0){
			// check the payment_status is Completed
			// check that txn_id has not been previously processed
			// check that receiver_email is an email address in your PayPal account process payment
			$sSQL="UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'";
			ect_query($sSQL) or ect_error();
			if(strtolower(@$_POST['status'])!='live') $authstatus='Pending: DEMO MODE'; else $authstatus='';
			$sSQL="UPDATE orders SET ordStatus=3,ordAuthNumber='" . $Txn_id . "',ordAVS='" . $card_address_check.':'.$card_postcode_check . "',ordCVV='" . $card_security_code . "',ordAuthStatus='".$authstatus."' WHERE ordID='" . escape_string($ordID) . "'";
			ect_query($sSQL) or ect_error();
			do_order_success($ordID,$emailAddr,$sendEmail,FALSE,TRUE,TRUE,TRUE);
		}elseif(strcmp ($res, "DECLINED")==0){
			; // log for manual investigation
		}else{
			if(@$debugmode==TRUE) print $res; // error
		}
	}
}
if(@$debugmode==TRUE){
	foreach(@$_POST as $key => $val){
		$emailtxt .= $key . ' : ' . $val . $emlNl;
	}
	dosendemail($emailAddr, $emailAddr, '', 'ncconfirm.php debug', $emailtxt);
}
?>
