<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(!@$GLOBALS['incfunctionsdefined']){print 'No incfunctions.php file';exit;}
global $alreadygotadmin,$thesessionid;
include "./vsadmin/inc/incemail.php";
if(@$_SERVER['CONTENT_LENGTH']!='' && $_SERVER['CONTENT_LENGTH'] > 10000) exit;
$success=FALSE;
$errtext=$errormsg=$thereference=$orderText='';
$ordGrandTotal=$ordTotal=$ordStateTax=$ordHSTTax=$ordCountryTax=$ordShipping=$ordHandling=$ordDiscount=0;
$ordID=$affilID=$ordCity=$ordState=$ordCountry=$ordDiscountText=$ordEmail='';
if(@$dateadjust=='') $dateadjust=0;
$_SESSION['couponapply']=NULL; unset($_SESSION['couponapply']);
$_SESSION['giftcerts']=NULL; unset($_SESSION['giftcerts']);
$_SESSION['cpncode']=NULL; unset($_SESSION['cpncode']);
$ordAuthNumber='';
$paypalwaitipn=$showclickreload=FALSE;
if(@$GLOBALS['xxPPWIPN']=='')$GLOBALS['xxPPWIPN']='We are just waiting for confirmation of the status of your payment. Please be patient for just a few moments.';
if(@$GLOBALS['xxPPTOUT']=='')$GLOBALS['xxPPTOUT']='There was a timeout waiting for confirmation from the payment server. Please contact our customer service email for details about the status of your order.';
if(@$debugmode){
	print 'POST parameters<br />';
	foreach($_POST as $key => $val){
		print 'POST: ' . $key . ' : ' . $val . '<br />';
	}
	print 'GET parameters<br />';
	foreach($_GET as $key => $val){
		print 'GET: ' . $key . ' : ' . $val . '<br />';
	}
}
function wait_paypal_ipn($ppordid){
	print '<form id="ppectform" method="post" action="' . @$_SERVER['PHP_SELF'] . (@$_SERVER['QUERY_STRING']!='' ? '?' . @$_SERVER['QUERY_STRING'] : '') . '">';
	foreach($_POST as $key => $val){
		print whv($key,$val);
	}
	print '</form>';
?>
<script type="text/javascript">/* <![CDATA[ */
var totpptries=0;
var ajaxobj;
function checkipncallback(){
	if(ajaxobj.readyState==4){
		if(ajaxobj.responseText=='1'){
			document.getElementById("ppectform").submit();
		}else{
			totpptries++;
			if(totpptries<30)
				setTimeout('checkipnarrived()',1000);
			else
				document.getElementById("orderfail").innerHTML='<p><strong><?php print jsescape($GLOBALS['xxPPTOUT'])?></strong></p>';
		}
	}
}
function checkipnarrived(){
	ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
	ajaxobj.onreadystatechange=checkipncallback;
	ajaxobj.open("GET","vsadmin/ajaxservice.php?action=ipnarrived&oid=<?php print $ppordid?>",true);
	ajaxobj.send(null);
}
checkipnarrived();
/* ]]> */</script><?php
}
function order_failed(){
	order_failed_htmldisp(TRUE);
}
function order_failed_htmldisp($dohtmldisplay){
	global $storeurl,$errtext,$success,$showclickreload,$paypalwaitipn,$ordID;
	$success=FALSE;
?>
	<div class="ectdiv">
		<div class="ectmessagescreen">
			<div id="orderfail" style="width:80%"><?php
		print $GLOBALS['xxThkErr'];
		if($errtext!='') print '<p><strong>' . ($dohtmldisplay?htmldisplay($errtext):$errtext) . '</strong></p>';
		if($paypalwaitipn){
			wait_paypal_ipn($ordID);
			print '<p><img style="margin:30px" src="images/preloader.gif" alt="Loading" /></p>';
		}elseif($showclickreload)
			print '<br />&nbsp;<br />&nbsp;<br />&nbsp;<br /><input type="button" value="'.$GLOBALS['xxClkRel'].'" onclick="window.location.reload()" /><br />&nbsp;<br />&nbsp;<br />';
?>				<a class="ectlink" href="<?php print $storeurl?>"><strong><?php print $GLOBALS['xxCntShp']?></strong></a>
			</div>
		</div>
	</div>
<?php
}
$alreadygotadmin=getadminsettings();
if(getpost('pprov')=='21'&&is_numeric(getpost('ordernumber'))){ // Amazon Pay
	function calculateStringToSignV2(){
		global $scripturl,$endpointpath,$demomode;
		return 'POST' . "\n" . $scripturl . "\n" . $endpointpath . "\n" . getParametersAsString();
	}
	function getParametersAsString(){
		global $amazonprms;
		$queryParameters = array();
		foreach ($amazonprms as $key => $value) {
			$queryParameters[] = $key . '=' . str_replace('%7E', '~', rawurlencode($value));
		}
		return implode('&', $queryParameters);
	}
	function calculateSignatureAndParametersToString(){
		global $amazonprms,$amazonstr,$data3;
		uksort($amazonprms, 'strcmp');
		//$this->createServiceUrl();
		$amazonstr=getParametersAsString();
	}
	function amazonparam2($nam, $val){
		global $amazonstr,$amazonprms;
		$amazonprms[$nam]=replaceaccents($val);
	}
	$ordID=getpost('ordernumber');
	if(getpayprovdetails(21,$data1,$data2,$data3,$demomode,$ppmethod)){
		$success=TRUE;
		$alreadyprocessed=FALSE;
		$scripturl='mws-eu.amazonservices.com';
		if($GLOBALS['origCountryCode']=='US') $scripturl='mws.amazonservices.com';
		if($GLOBALS['origCountryCode']=='JP') $scripturl='mws.amazonservices.jp';
		$endpointpath='/OffAmazonPayments' . ($demomode?'_Sandbox':'') . '/2013-01-01';
		$endpoint='https://' . $scripturl . $endpointpath;
		
		$data2arr=explode('&',$data2);
		$data2=$data2arr[0];
		$sellerid=@$data2arr[1];
		
		$amazonstr=$amazonprms='';
		$timestamp=gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
		
		$itemtotal=0;
		$sSQL="SELECT ordAuthNumber,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordHandling,ordTotal,ordDiscount,ordAuthNumber,ordTransID,ordEmail,ordStatus FROM orders WHERE ordPayProvider=21 AND ordTransID='" . escape_string(getpost('amzrefid')) . "' AND ordID='" . escape_string($ordID) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$email=$rs['ordEmail'];
			$itemtotal=($rs['ordShipping']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordHSTTax']+$rs['ordTotal']+$rs['ordHandling'])-$rs['ordDiscount'];
			$ordStatus=$rs['ordStatus'];
			$ordTransID=$rs['ordTransID'];
			$alreadyprocessed=$ordStatus>=3;
		}else{
			$success=FALSE;
			$errtext='The Order ID could not be found.';
		}
		ect_free_result($result);
		
		if($alreadyprocessed){
			do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
		}elseif(!$success)
			order_failed();
		else{
			amazonparam2('AWSAccessKeyId',$data2);
			amazonparam2('Action','SetOrderReferenceDetails');
			amazonparam2('AmazonOrderReferenceId',getpost('amzrefid'));
			amazonparam2('OrderReferenceAttributes.OrderTotal.Amount',number_format($itemtotal,2,'.',''));
			amazonparam2('OrderReferenceAttributes.OrderTotal.CurrencyCode',$countryCurrency);
			amazonparam2('OrderReferenceAttributes.SellerOrderAttributes.SellerOrderId',$ordID);
			amazonparam2('SellerId',$sellerid);
			amazonparam2('SignatureMethod','HmacSHA256');
			amazonparam2('SignatureVersion',2);
			amazonparam2('Timestamp',$timestamp);
			amazonparam2('Version','2013-01-01');
			
			calculateSignatureAndParametersToString();
			$amazonprms['Signature']=base64_encode(hash_hmac('sha256', calculateStringToSignV2($amazonprms), $data3, true));
			$amazonstr=getParametersAsString();
			if(!callcurlfunction($endpoint,$amazonstr,$res,'',$errormsg,FALSE))
				$success=FALSE;
			
			if($success){
				$amazonstr=$amazonprms='';
				$timestamp=gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
				
				amazonparam2('AWSAccessKeyId',$data2);
				amazonparam2('Action','ConfirmOrderReference');
				amazonparam2('AmazonOrderReferenceId',getpost('amzrefid'));
				amazonparam2('SellerId',$sellerid);
				amazonparam2('SignatureMethod','HmacSHA256');
				amazonparam2('SignatureVersion',2);
				amazonparam2('Timestamp',$timestamp);
				amazonparam2('Version','2013-01-01');
				
				calculateSignatureAndParametersToString();
				$amazonprms['Signature']=base64_encode(hash_hmac('sha256', calculateStringToSignV2($amazonprms), $data3, true));
				$amazonstr=getParametersAsString();
				if(!callcurlfunction($endpoint,$amazonstr,$res,'',$errormsg,FALSE))
					$success=FALSE;

				$amazonstr=$amazonprms='';
				$timestamp=gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
				
				amazonparam2('AWSAccessKeyId',$data2);
				amazonparam2('Action','GetOrderReferenceDetails');
				//amazonparam2('AddressConsentToken',ZZZ);
				amazonparam2('AmazonOrderReferenceId',getpost('amzrefid'));
				amazonparam2('SellerId',$sellerid);
				amazonparam2('SignatureMethod','HmacSHA256');
				amazonparam2('SignatureVersion',2);
				amazonparam2('Timestamp',$timestamp);
				amazonparam2('Version','2013-01-01');
			
				calculateSignatureAndParametersToString();
				$amazonprms['Signature']=base64_encode(hash_hmac('sha256', calculateStringToSignV2($amazonprms), $data3, true));
				$amazonstr=getParametersAsString();
			}
			if($success&&callcurlfunction($endpoint,$amazonstr,$res,'',$errormsg,FALSE)){
				$ordEmail=$ordName=$ordAddress=$ordCity=$ordPhone='';
				
				$xmlDoc=new vrXMLDoc($res);
				$nodeList=$xmlDoc->nodeList->childNodes[0];
				for($i=0; $i < $nodeList->length; $i++){
					if($nodeList->nodeName[$i]=='Error'){
						$amazonpayment=$checkoutmode='';
						$e=$nodeList->childNodes[$i];
						for($j=0; $j < $e->length; $j++){
							if($e->nodeName[$j]=='Message'){
								$errtext='Amazon Error: ' . $e->nodeValue[$j];
								$success=FALSE;
							}
						}
					}elseif($nodeList->nodeName[$i]=='GetOrderReferenceDetailsResult'){
						$e=$nodeList->childNodes[$i];
						for($j=0; $j < $e->length; $j++){
							if($e->nodeName[$i]=='OrderReferenceDetails'){
								$f=$e->childNodes[$j];
								for($k=0; $k < $f->length; $k++){
									if($f->nodeName[$k]=='Constraints'){
										$g=$f->childNodes[$k];
										for($l=0; $l < $g->length; $l++){
											if($g->nodeName[$l]=='Constraint'){
												$h=$g->childNodes[$l];
												$isamountconstraint=FALSE;
												for($m=0; $m < $h->length; $m++){
													if($h->nodeName[$m]=='Description'){
														$errtext=$h->nodeValue[$m];
														$success=FALSE;
													}
												}
											}
										}
									}elseif($f->nodeName[$k]=='Destination'){
										$g=$f->childNodes[$k];
										for($l=0; $l < $g->length; $l++){
											if($g->nodeName[$l]=='PhysicalDestination'){
												$h=$g->childNodes[$l];
												for($m=0; $m < $h->length; $m++){
													if($h->nodeName[$m]=='Name'){
														$ordName=$h->nodeValue[$m];
													}elseif($h->nodeName[$m]=='AddressLine1'){
														$ordAddress=$h->nodeValue[$m];
													}elseif($h->nodeName[$m]=='City'){
														$ordCity=$h->nodeValue[$m];
													}elseif($h->nodeName[$m]=='StateOrRegion'){
														$ordState=$h->nodeValue[$m];
													}elseif($h->nodeName[$m]=='PostalCode'){
														$ordZip=$h->nodeValue[$m];
													}elseif($h->nodeName[$m]=='Phone'){
														$ordPhone=$h->nodeValue[$m];
													}elseif($h->nodeName[$m]=='Email'){
														$ordEmail=$h->nodeValue[$m];
													}
												}
											}
										}
									}elseif($f->nodeName[$k]=='Buyer'){
										$g=$f->childNodes[$k];
										for($l=0; $l < $g->length; $l++){
											if($g->nodeName[$l]=='Email'){
												$ordEmail=$g->nodeValue[$l];
											}
										}
									}
								}
							}
						}
					}
				}
				if($success){
					$sSQL="UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'";
					ect_query($sSQL) or ect_error();
					$sSQL="UPDATE orders SET ordStatus=3,ordAuthStatus='',ordAuthNumber='" . escape_string(getpost('amzrefid')) . "',ordName='" . escape_string($ordName) . "',ordAddress='" . escape_string($ordAddress) . "',ordCity='" . escape_string($ordCity) . "',ordPhone='" . escape_string($ordPhone) . "',ordEmail='" . escape_string($ordEmail) . "' WHERE ordPayProvider=21 AND ordID='" . escape_string($ordID) . "'";
					ect_query($sSQL) or ect_error();
					
					do_order_success($ordID,$emailAddr,! $alreadyprocessed,TRUE,! $alreadyprocessed,! $alreadyprocessed,! $alreadyprocessed);
					$_SESSION['AmazonLogin']='';
					$_SESSION['AmazonLoginTimeout']='';
				}else
					order_failed();
			}
		}
	}
}elseif(getpost('pprov')=='23'&&getpost('stripeToken')!=''&&getpost('stripeEmail')!=''&&is_numeric(getpost('ordernumber'))){
	$ordID=getpost('ordernumber');
	if(getpayprovdetails(23,$data1,$data2,$data3,$demomode,$ppmethod)){
		$amount=0;
		$success=TRUE;
		$isstripedotcom=TRUE; // For callxmlfunction
		$ordStatus=3;
		$ordTransID='xxxxx';
		$alreadyprocessed=FALSE;
		$token=getpost('stripeToken');
		$chargeid='';

		$sSQL="SELECT ordAuthNumber,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordHandling,ordTotal,ordDiscount,ordAuthNumber,ordTransID,ordEmail,ordStatus FROM orders WHERE ordPayProvider=23 AND ordID='" . escape_string($ordID) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$email=$rs['ordEmail'];
			$amount=($rs['ordShipping']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordHSTTax']+$rs['ordTotal']+$rs['ordHandling'])-$rs['ordDiscount'];
			$ordStatus=$rs['ordStatus'];
			$ordTransID=$rs['ordTransID'];
		}else{
			$success=FALSE;
			$errtext='The Order ID could not be found.';
		}
		ect_free_result($result);
		if($ordStatus>=3){
			if($ordTransID==$token)
				$alreadyprocessed=TRUE;
			else{
				$success=FALSE;
				$errtext='The Stripe.com Order Has Already Been Processed.';
			}
		}elseif($success){
			$sXML="amount=".round($amount*100)."&currency=".strtolower($countryCurrency)."&card=".$token."&capture=".($ppmethod==1?"false":"true")."&description=".$email;
			$xmlfnheaders=array("User-Agent: Stripe/v1 RubyBindings/1.12.0","Authorization: Bearer ".$data1,"Content-Type: application/x-www-form-urlencoded");
			$success=callcurlfunction('https://api.stripe.com/v1/charges',$sXML,$xmlres,"",$errormsg,FALSE);
			if($success&&$http_status=='200'){
				$idpos=strpos($xmlres,'"id":');
				if($idpos!==FALSE){
					$startpos=strpos($xmlres,'"',$idpos+6)+1;
					$endpos=strpos($xmlres,'"',$startpos);
					$chargeid=substr($xmlres,$startpos,$endpos-$startpos);
				}
			}else{
				$success=FALSE;
				$idpos=strpos($xmlres,'"message":');
				if($idpos!==FALSE){
					$startpos=strpos($xmlres,'"',$idpos+10)+1;
					$endpos=strpos($xmlres,'"',$startpos);
					$errtext=substr($xmlres,$startpos,$endpos-$startpos);
				}
			}
		}
		if($success){
			if(! $alreadyprocessed){
				$sSQL="UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'";
				ect_query($sSQL) or ect_error();
				$sSQL="UPDATE orders SET ordStatus=3,ordAuthStatus='',ordAuthNumber='" . escape_string(substr($chargeid,0,48)) . "',ordTransID='" . escape_string(substr($token,0,48)) . "' WHERE ordPayProvider=23 AND ordID='" . escape_string($ordID) . "'";
				ect_query($sSQL) or ect_error();
			}
			do_order_success($ordID,$emailAddr,! $alreadyprocessed,TRUE,! $alreadyprocessed,! $alreadyprocessed,! $alreadyprocessed);
		}else
			order_failed();
	}else{
		$errtext='Payment method not set.';
		order_failed();
	}
}elseif(getget('ectprnm')=='wpconfirm'&&is_numeric(getget('ordid'))&&is_numeric(getget('pprov'))){
	if(!getpayprovdetails(getget('pprov'),$data1,$data2,$data3,$demomode,$ppmethod)){
		$errtext='Payment method not set.';
		order_failed();
	}else{
		$ordID=getget('ordid');
		$rethash=getget('rethash');
		$sSQL="SELECT ordSessionID FROM orders WHERE ordID='".escape_string($ordID)."'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result))$sessionid=$rs['ordSessionID']; else $sessionid='xxx';
		ect_free_result($result);
		$ourhash=strtoupper(md5($ordID.'WPCONFHash'.getget('pprov').$sessionid.'1234'.$adminSecret));
		if($ourhash==$rethash){
			do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
		}else{
			$errtext='Hash values do not match';
			order_failed();
		}
	}
}elseif(getget('PNREF')!='' && getget('SECURETOKEN')!='' && getget('SECURETOKENID')!=''){ // PayPal Advanced
	$txn_id=getget('PPREF');
	$ordID=getget('INVOICE');
	print '<script language="javascript">if(window!=top)top.location.href=location.href</script>';
	$success=FALSE;
	if(getget('RESULT')=='0' && is_numeric($ordID)){
		if($txn_id!=''){
			$sSQL="SELECT ordAuthNumber FROM orders WHERE ordPayProvider=22 AND ordStatus>=3 AND ordAuthNumber='" . escape_string($txn_id) . "' AND ordID='" . escape_string($ordID) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result))
				$success=(trim($rs['ordAuthNumber'])!='');
			ect_free_result($result);
		}
		if($success)
			do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
		else{
			$sSQL="SELECT ordAuthNumber,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordHandling,ordTotal,ordDiscount,ordAuthNumber,ordEmail FROM orders WHERE ordPayProvider=8 AND ordID='" . escape_string($ordID) . "'";
			$result=ect_query($sSQL) or ect_error();
			$ispayflowtxn=FALSE;
			if($rs=ect_fetch_assoc($result)){
				$amount=number_format(($rs['ordShipping']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordHSTTax']+$rs['ordTotal']+$rs['ordHandling'])-$rs['ordDiscount'],2,'.','');
				$ispayflowtxn=TRUE;
			}
			ect_free_result($result);
			if($ispayflowtxn && getpayprovdetails(8,$data1,$data2,$data3,$demomode,$ppmethod)){
				$vsdetails=explode('&', $data1);
				$vs1=@$vsdetails[0];
				$vs2=@$vsdetails[1];
				$vs3=@$vsdetails[2];
				$vs4=@$vsdetails[3];
				$sXML='TRXTYPE=I&TENDER=C&PARTNER='.$vs3.'&VENDOR='.$vs2.'&USER='.$vs1.'&PWD='.$vs4.'&SECURETOKEN='.getget('SECURETOKEN').'&SECURETOKENID='.getget('SECURETOKENID').'&VERBOSITY=HIGH';
				$success=callcurlfunction('https://' . ($demomode?'pilot-':'') . 'payflowpro.paypal.com', $sXML, $curString, '', $errormsg, TRUE);
				$resparr=explode('&',$curString);
				$AUTHCODE=$RESPMSG=$AVSADDR=$AVSZIP=$CVV2MATCH=$RESULT=$TRANSTIME=$AMT='';
				foreach($resparr as $val){
					$itemarr=explode('=',$val);
					if($itemarr[0]=='AUTHCODE') $AUTHCODE=$itemarr[1];
					if($itemarr[0]=='RESPMSG') $errtext=$itemarr[1];
					if($itemarr[0]=='AVSADDR ') $AVSADDR =$itemarr[1];
					if($itemarr[0]=='AVSZIP') $AVSZIP=$itemarr[1];
					if($itemarr[0]=='CVV2MATCH') $CVV2MATCH=$itemarr[1];
					if($itemarr[0]=='RESULT') $RESULT=$itemarr[1];
					if($itemarr[0]=='TRANSTIME') $TRANSTIME=$itemarr[1];
					if($itemarr[0]=='AMT') $AMT=$itemarr[1];
				}
				if($AUTHCODE=='') $AUTHCODE=$txn_id;
				$daysago=abs(time() - strtotime($TRANSTIME)) / (60*60*24);
				if($RESULT=='0' && $AUTHCODE!='' & $daysago<=1 && abs($amount-(double)$AMT)<=0.01){
					$alreadysentemail=TRUE;
					$result=ect_query("SELECT ordStatus FROM orders WHERE ordID='" . escape_string($ordID) . "'") or ect_error();
					if($rs=ect_fetch_assoc($result)) $alreadysentemail=$rs['ordStatus']>=3;
					ect_free_result($result);
					ect_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'") or ect_error();
					ect_query("UPDATE orders SET ordStatus=3,ordAVS='".escape_string($AVSADDR.$AVSZIP)."',ordCVV='".escape_string($CVV2MATCH)."',ordAuthNumber='".escape_string($AUTHCODE)."',ordTransID='".escape_string(getget('PNREF'))."' WHERE ordPayProvider=8 AND ordID='" . escape_string($ordID) . "'") or ect_error();
					do_order_success($ordID,$emailAddr,$sendEmail && ! $alreadysentemail,TRUE,! $alreadysentemail,! $alreadysentemail);
				}else
					order_failed();
			}else{
				ect_query("UPDATE cart SET cartCompleted=2 WHERE cartCompleted=0 AND cartOrderID='" . escape_string($ordID) . "'") or ect_error();
				ect_query("UPDATE orders SET ordAuthNumber='no ipn' WHERE ordAuthNumber='' AND ordPayProvider=22 AND ordID='" . escape_string($ordID) . "'") or ect_error();
				$GLOBALS['xxThkErr']='';
				$errtext='&nbsp;<br />&nbsp;<br />&nbsp;<br />';
				if(strtolower(getrequest('PENDINGREASON'))=='pending') $errtext.=$GLOBALS['xxPPPend']; else{ $errtext=$GLOBALS['xxPPWIPN']; $paypalwaitipn=TRUE; }
				order_failed();
			}
		}
	}else{
		$errtext=urldecode(getget('RESPMSG'));
		order_failed();
	}
}elseif(@$paypalhostedsolution && getget('tx')!=''){
	if(!getpayprovdetails(18,$data1,$data2,$data3,$demomode,$ppmethod)){
		$errtext='Payment method not set.';
		order_failed();
	}else{
		$data2arr=explode('&',$data2);
		$data2=urldecode(@$data2arr[0]);
		$isthreetoken=(trim(urldecode(@$data2arr[2]))=='1');
		$signature=''; $sslcertpath='';
		if($isthreetoken) $signature=urldecode(@$data2arr[1]); else $sslcertpath=urldecode(@$data2arr[1]);
		if(strpos($data1,'@AB@')!==FALSE){
			$isthreetoken=TRUE;
			$signature='AB';
		}
		$sXML='PWD=' . $data2 . '&USER=' . $data1 . ($signature!='' ? '&SIGNATURE=' . $signature : '') . '&METHOD=GetTransactionDetails&VERSION=84.0&TRANSACTIONID=' . getget('tx');
		if(callcurlfunction('https://api-3t' . ($demomode ? '.sandbox' : '') . '.paypal.com/nvp', $sXML, $res, $sslcertpath, $errtext, FALSE)){
			$lines=explode('&', $res);
			$payment_status='';
			$pending_reason='';
			$txn_id='';
			for ($i=1; $i<(count($lines)-1);$i++){
				list($key,$val)=explode('=', $lines[$i]);
				if($key=='ACK') $success=($val=='Success');
				if($key=='PAYMENTSTATUS') $payment_status=$val;
				if($key=='PENDINGREASON') $pending_reason=$val;
				if($key=='CUSTOM') $ordID=str_replace("'",'',$val);
				if($key=='TRANSACTIONID') $txn_id=str_replace("'",'',$val);
				if($key=='L_LONGMESSAGE0') $errtext=urldecode($val);
			}
			if($success){
				$sSQL="SELECT ordAuthNumber FROM orders WHERE ordPayProvider=1 AND ordStatus>=3 AND ordAuthNumber='" . escape_string($txn_id) . "' AND ordID='" . escape_string($ordID) . "'";
				$result=ect_query($sSQL) or ect_error();
				$success=FALSE;
				if($rs=ect_fetch_assoc($result))
					$success=(trim($rs["ordAuthNumber"])!="");
				ect_free_result($result);
				if($success)
					do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
				else{
					ect_query("UPDATE cart SET cartCompleted=2 WHERE cartCompleted=0 AND cartOrderID='" . escape_string($ordID) . "'") or ect_error();
					ect_query("UPDATE orders SET ordAuthNumber='no ipn' WHERE ordAuthNumber='' AND ordPayProvider=1 AND ordID='" . escape_string($ordID) . "'") or ect_error();
					$GLOBALS['xxThkErr']='';
					if($payment_status=='Pending') $errtext=$GLOBALS['xxPPPend']; else{ $errtext=$GLOBALS['xxPPWIPN']; $paypalwaitipn=TRUE; }
					order_failed();
				}
			}else
				order_failed();
		}
	}
}elseif(getget('amt')!='' && getget('tx')!='' && getget('st')!='' && getget('cc')!='' && getget('cm')!=''){
	$ordID='';
	if(!getpayprovdetails(1,$data1,$data2,$data3,$demomode,$ppmethod)){
		$errtext='Payment method not set.';
		order_failed();
	}elseif($data2==''){
		$errtext='Identity token for PayPal Payment Data Transfer (PDT) not set.';
		order_failed();
	}else{
		$success=TRUE;
		$req='cmd=_notify-synch';
		$req.='&tx=' . getget('tx') . '&at=' . $data2;
		if(@$usefsockforpaypalipn){
			$header="POST /cgi-bin/webscr HTTP/1.1\r\n" .
				"Content-Type: application/x-www-form-urlencoded\r\n" .
				"Content-Length: " . strlen($req) . "\r\n" .
				"Host: www.paypal.com\r\n" .
				"Connection: close\r\n\r\n";
			if($fp=fsockopen('ssl://www' . ($demomode ? '.sandbox' : '') . '.paypal.com', 443, $errno, $errtext, 30)){
				fputs($fp, $header . $req);
				$res='';
				$headerdone=false;
				while(!feof($fp)){
					$line=fgets ($fp, 1024);
					if(strcmp($line, "\r\n")==0)
						$headerdone=true;
					elseif($headerdone)
						$res.=$line;
				}
				fclose($fp);
			}else{
				$success=FALSE;
				order_failed();
			}
		}else{
			if(!callcurlfunction('https://www' . ($demomode ? '.sandbox' : '') . '.paypal.com/cgi-bin/webscr', $req, $res, '', $errtext, 30)){
				$success=FALSE;
				order_failed();
			}
		}
		if($success){
			$lines=explode("\n", $res);
			if(strcmp ($lines[0], "SUCCESS")==0){
				$payment_status='';
				$pending_reason='';
				$txn_id='';
				for ($i=1; $i<(count($lines)-1);$i++){
					list($key,$val)=explode("=", $lines[$i]);
					if($key=='payment_status') $payment_status=$val;
					if($key=='pending_reason') $pending_reason=$val;
					if($key=='custom') $ordID=$val;
					if($key=='txn_id') $txn_id=$val;
				}
				$sSQL="SELECT ordAuthNumber FROM orders WHERE ordPayProvider=1 AND ordStatus>=3 AND ordAuthNumber='" . escape_string($txn_id) . "' AND ordID='" . escape_string($ordID) . "'";
				$result=ect_query($sSQL) or ect_error();
				$success=FALSE;
				if($rs=ect_fetch_assoc($result))
					$success=(trim($rs["ordAuthNumber"])!="");
				ect_free_result($result);
				if($success)
					do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
				else{
					ect_query("UPDATE cart SET cartCompleted=2 WHERE cartCompleted=0 AND cartOrderID='" . escape_string($ordID) . "'") or ect_error();
					ect_query("UPDATE orders SET ordAuthNumber='no ipn' WHERE ordAuthNumber='' AND ordPayProvider=1 AND ordID='" . escape_string($ordID) . "'") or ect_error();
					$GLOBALS['xxThkErr']='';
					if($payment_status=='Pending') $errtext.=$GLOBALS['xxPPPend']; else{ $errtext=$GLOBALS['xxPPWIPN']; $paypalwaitipn=TRUE; }
					order_failed();
				}
			}else{
				$errtext=$res;
				order_failed();
			}
		}
	}
}elseif(getpost('custom')!=''){ // PayPal
	$ordID=getpost('custom');
	$txn_id=getpost('txn_id');
	$sSQL="SELECT ordAuthNumber FROM orders WHERE ordPayProvider=1 AND ordStatus>=3 AND ordAuthNumber='" . escape_string($txn_id) . "' AND ordID='" . escape_string($ordID) . "'";
	$result=ect_query($sSQL) or ect_error();
	$success=FALSE;
	if($rs=ect_fetch_assoc($result))
		$success=(trim($rs["ordAuthNumber"])!="");
	ect_free_result($result);
	if($success)
		do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
	else{
		ect_query("UPDATE cart SET cartCompleted=2 WHERE cartCompleted=0 AND cartOrderID='" . escape_string($ordID) . "'") or ect_error();
		ect_query("UPDATE orders SET ordAuthNumber='no ipn' WHERE ordAuthNumber='' AND ordPayProvider=1 AND ordID='" . escape_string($ordID) . "'") or ect_error();
		$GLOBALS['xxThkErr']='';
		$errtext='&nbsp;<br />&nbsp;<br />&nbsp;<br />';
		if(getpost('payment_status')=="Pending") $errtext.=$GLOBALS['xxPPPend']; else{ $errtext=$GLOBALS['xxPPWIPN']; $paypalwaitipn=TRUE; }
		order_failed();
	}
}elseif(getpost('method')=='paypalexpress' && getpost('token')!=''){ // PayPal Express
	if($success=getpayprovdetails(19,$username,$password,$data3,$demomode,$ppmethod)){
		$data2arr=explode('&',$password);
		$password=urldecode(@$data2arr[0]);
		$isthreetoken=(trim(urldecode(@$data2arr[2]))=='1');
		$signature=''; $sslcertpath='';
		if($isthreetoken){
			$signature=urldecode(@$data2arr[1]);
			if(strpos($username,'/')!==FALSE){
				$username=explode('/',$username);$username=trim($username[0]);
				$password=explode('/',$password);$password=trim($password[0]);
				$signature=explode('/',$signature);$signature=trim($signature[0]);
			}
		}else
			$sslcertpath=urldecode(@$data2arr[1]);
		if(strpos($username,'@AB@')!==FALSE){
			$isthreetoken=TRUE;
			$signature='AB';
		}
	}
	$ordID=getpost('ordernumber');
	$token=getpost('token');
	$payerid=getpost('payerid');
	$ordAuthNumber='';
	$txn_id=$status=$pendingreason='';
	if($demomode) $sandbox='.sandbox'; else $sandbox='';
	$sSQL="SELECT ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordHandling,ordTotal,ordDiscount,ordAuthNumber,ordEmail FROM orders WHERE ordID='" . escape_string($ordID) . "'";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		if($rs["ordEmail"]==getpost('email')) $ordAuthNumber=$rs["ordAuthNumber"];
	}else
		$success=FALSE;
	ect_free_result($result);
	if($success&&@$GLOBALS['termsandconditions']){
		if(getpost('termsandconds')!='1'){
			$errtext='You must agree to our terms and conditions in order to complete your order. Please click below to go back and try again.';
			$errtext.='<br />&nbsp;<br />&nbsp;<br />&nbsp;<br /><input type="button" value="'.$GLOBALS['xxGoBack'].'" onclick="history.go(-1)" /><br />&nbsp;<br />&nbsp;<br />';
			$success=FALSE;
		}
	}
	if($success){
		if($ordAuthNumber==''){
			$amount=number_format(($rs['ordShipping']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordHSTTax']+$rs['ordTotal']+$rs['ordHandling'])-$rs['ordDiscount'],2,'.','');
			$sXML=ppsoapheader($username, $password, $signature) .
				'<soap:Body>' .
				'  <DoExpressCheckoutPaymentReq xmlns="urn:ebay:api:PayPalAPI">' .
				'    <DoExpressCheckoutPaymentRequest>' .
				'      <Version xmlns="urn:ebay:apis:eBLBaseComponents">60.00</Version>' .
				'      <DoExpressCheckoutPaymentRequestDetails xmlns="urn:ebay:apis:eBLBaseComponents">' .
				'        <PaymentAction>' . ($ppmethod==1?'Authorization':'Sale') . '</PaymentAction>' .
				'        <Token>' . $token . '</Token><PayerID>' . $payerid . '</PayerID>' .
				'        <PaymentDetails>' .
				'          <OrderTotal currencyID="' . $countryCurrency . '">' . $amount . '</OrderTotal>' .
				'          <ButtonSource>ecommercetemplates_Cart_EC_US</ButtonSource>' .
				'    <NotifyURL>' . $storeurl . 'vsadmin/ppconfirm.php</NotifyURL>' .
				'        </PaymentDetails>' .
				'      </DoExpressCheckoutPaymentRequestDetails>' .
				'    </DoExpressCheckoutPaymentRequest>' .
				'  </DoExpressCheckoutPaymentReq>' .
				'</soap:Body></soap:Envelope>';
			if(callcurlfunction('https://api' . ($isthreetoken ? '-3t' : '') . $sandbox . '.paypal.com/2.0/', $sXML, $res, $sslcertpath, $errtext, FALSE)){
				$xmlDoc=new vrXMLDoc($res);
				$nodeList=$xmlDoc->nodeList->childNodes[0];
				for($i=0; $i < $nodeList->length; $i++){
					if($nodeList->nodeName[$i]=='SOAP-ENV:Body'){
						$e=$nodeList->childNodes[$i];
						for($j=0; $j < $e->length; $j++){
							if($e->nodeName[$j]=='DoExpressCheckoutPaymentResponse'){
								$ee=$e->childNodes[$j];
								for($jj=0; $jj < $ee->length; $jj++){
									if($ee->nodeName[$jj]=='Token'){
										$token=$ee->nodeValue[$jj];
									}elseif($ee->nodeName[$jj]=='DoExpressCheckoutPaymentResponseDetails'){
										$ff=$ee->childNodes[$jj];
										for($kk=0; $kk < $ff->length; $kk++){
											if($ff->nodeName[$kk]=='PaymentInfo'){
												$gg=$ff->childNodes[$kk];
												for($ll=0; $ll < $gg->length; $ll++){
													if($gg->nodeName[$ll]=='PaymentStatus'){
														$status=$gg->nodeValue[$ll];
													}elseif($gg->nodeName[$ll]=='PendingReason'){
														$pendingreason=$gg->nodeValue[$ll];
													}elseif($gg->nodeName[$ll]=='TransactionID'){
														$txn_id=$gg->nodeValue[$ll];
													}
												}
											}
										}
									}elseif($ee->nodeName[$jj]=='Errors'){
										$ff=$ee->childNodes[$jj];
										for($kk=0; $kk < $ff->length; $kk++){
											if($ff->nodeName[$kk]=='ShortMessage'){
												//$errtext=$ff->nodeValue[$kk].'<br>'.$errtext;
											}elseif($ff->nodeName[$kk]=='LongMessage'){
												$errtext.=$ff->nodeValue[$kk];
											}elseif($ff->nodeName[$kk]=='ErrorCode'){
												$errcode=$ff->nodeValue[$kk];
												$errtext='(' . $errcode . ') ' . $errtext;
												if($errcode=='10486' || $errcode=='10422'){
													if(ob_get_length()===FALSE){
														print '<meta http-equiv="Refresh" content="0; URL=https://www'.$sandbox.'.paypal.com/webscr?cmd=_express-checkout&token=' . $token . '">';
													}else
														header('Location: https://www'.$sandbox.'.paypal.com/webscr?cmd=_express-checkout&token=' . $token);
													print '<p align="center">' . $GLOBALS['xxAutFo'] . '</p>';
													print '<p align="center">' . $GLOBALS['xxForAut'] . ' <a class="ectlink" href="https://www'.$sandbox.'.paypal.com/webscr?cmd=_express-checkout&token=' . $token . '">' . $GLOBALS['xxClkHere'] . '</a></p>';
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}else
				$success=FALSE;
		}else
			$status="Refresh";
		if($status=="Completed" || $status=="Pending"){
			if($pendingreason=='authorization') $pendingreason='Capture';
			if($status=='Pending' && $pendingreason!='') $pendingreason='Pending: ' . $pendingreason; else $pendingreason='';
			ect_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'") or ect_error();
			ect_query("UPDATE orders SET ordStatus=3,ordAuthNumber='" . $txn_id . "',ordAuthStatus='" . escape_string($pendingreason) . "' WHERE ordPayProvider=19 AND ordID='" . escape_string($ordID) . "'") or ect_error();
			do_order_success($ordID,$emailAddr,$sendEmail,TRUE,TRUE,TRUE,TRUE);
		}elseif($status=='Refresh'){
			do_order_success($ordID,$emailAddr,$sendEmail,TRUE,FALSE,FALSE,FALSE);
		}else
			order_failed();
	}else
		order_failed_htmldisp(FALSE);
}elseif(getget('ncretval')!='' && getget('ncsessid')!=''){ // NOCHEX
	$ordID=getget('ncretval');
	$ncsessid=getget('ncsessid');
	$sSQL="SELECT ordAuthNumber FROM orders WHERE ordPayProvider=6 AND ordStatus>=3 AND ordSessionID='" . escape_string($ncsessid) . "' AND ordID='" . escape_string($ordID) . "'";
	$result=ect_query($sSQL) or ect_error();
	$success=FALSE;
	if($rs=ect_fetch_assoc($result))
		$success=(trim($rs["ordAuthNumber"])!="");
	ect_free_result($result);
	if($success)
		do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
	else{
		ect_query("UPDATE cart SET cartCompleted=2 WHERE cartCompleted=0 AND cartOrderID='" . escape_string($ordID) . "'") or ect_error();
		ect_query("UPDATE orders SET ordAuthNumber='no apc' WHERE ordAuthNumber='' AND ordPayProvider=6 AND ordID='" . escape_string($ordID) . "'") or ect_error();
		$errtext=$GLOBALS['xxNoCnf'];
		$GLOBALS['xxThkErr']='';
		$showclickreload=TRUE;
		order_failed();
	}
}elseif(getpost('xxpreauth')!=''){
	$ordID=getpost('xxpreauth');
	$thesessionid=trim(str_replace("'",'',getpost('thesessionid')));
	$themethod=trim(str_replace("'",'',getpost('xxpreauthmethod')));
	if($success=getpayprovdetails($themethod,$data1,$data2,$data3,$demomode,$ppmethod)){
		$sSQL="SELECT ordAuthNumber FROM orders WHERE ordSessionID='" . escape_string($thesessionid) . "' AND ordID='" . escape_string($ordID) . "'";
		$result=ect_query($sSQL) or ect_error();
		$success=FALSE;
		if($rs=ect_fetch_assoc($result))
			$success=(trim($rs['ordAuthNumber'])!='');
		ect_free_result($result);
	}
	if($success)
		do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
	else
		order_failed();
}elseif(is_numeric(getpost('cart_order_id')) && getpost('order_number')!=''){ // 2Checkout Transaction
	if(getpost('credit_card_processed')=='Y'){
		$ordID=getpost('cart_order_id');
		$success=getpayprovdetails(2,$acctno,$md5key,$data3,$demomode,$ppmethod);
		$keysmatch=TRUE;
		if($md5key!=''){
			$theirkey=getpost('key');
			$ourkey=trim(strtoupper(md5($md5key . $acctno . ($demomode ? '1' : getpost('order_number')) . getpost('total'))));
			if($ourkey==$theirkey) $keysmatch=TRUE; else $keysmatch=FALSE;
		}
		if($success && $keysmatch){
			ect_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'") or ect_error();
			ect_query("UPDATE orders SET ordStatus=3,ordAuthStatus='',ordAuthNumber='" . escape_string(getpost('order_number')) . "' WHERE ordPayProvider=2 AND ordID='" . escape_string($ordID) . "'") or ect_error();
			order_success($ordID,$emailAddr,$sendEmail);
		}else{
			order_failed();
		}
	}else{
		order_failed();
	}
}elseif(getpost('CUSTID')!='' && (getpost('AUTHCODE')!=''||getpost('PNREF')!='')){ // PayFlow Link
	$success=getpayprovdetails(8,$data1,$data2,$data3,$demomode,$ppmethod);
	if($success && getpost('RESULT')=='0'){
		$ordID=getpost('CUSTID');
		ect_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'") or ect_error();
		ect_query("UPDATE orders SET ordStatus=3,ordAuthStatus='',ordAVS='" . escape_string(getpost('AVSDATA')) . "',ordCVV='" . escape_string(getpost('CSCMATCH')) . "',ordAuthNumber='" . escape_string(getpost('AUTHCODE')) . "' WHERE ordPayProvider=8 AND ordID='" . escape_string($ordID) . "'") or ect_error();
		order_success($ordID,$emailAddr,$sendEmail);
	}else{
		$errtext=getpost('RESPMSG');
		order_failed();
	}
}elseif(getpost('emailorder')!='' || getpost('secondemailorder')!=''){
	$ordGndTot=1;
	if(@$emailorderstatus!='') $ordStatus=$emailorderstatus; else $ordStatus=3;
	if(getpost('emailorder')!=''){
		$ordID=trim(str_replace("'",'',getpost('emailorder')));
		$ppid=4;
	}else{
		$ordID=trim(str_replace("'",'',getpost('secondemailorder')));
		$ppid=17;
	}
	$thesessionid=trim(str_replace("'",'',getpost('thesessionid')));
	if(! is_numeric($ordID)) $ordID=0;
	$sSQL="SELECT ordAuthNumber,((ordShipping+ordStateTax+ordCountryTax+ordHSTTax+ordTotal+ordHandling)-ordDiscount) AS ordGndTot FROM orders WHERE ordSessionID='" . escape_string($thesessionid) . "' AND ordID='" . escape_string($ordID) . "'";
	$result=ect_query($sSQL) or ect_error();
	$success=FALSE;
	if($rs=ect_fetch_assoc($result)){
		$success=TRUE;
		$ordGndTot=round($rs['ordGndTot'],2);
	}
	ect_free_result($result);
	$sSQL="SELECT payProvShow FROM payprovider WHERE (payProvEnabled=1 OR ".$ordGndTot."=0) AND payProvID=" . $ppid;
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		$authnumber=$rs['payProvShow'];
		if($ordGndTot==0){ // Check if it was a gift cert
			$sSQL="SELECT gcaGCID FROM giftcertsapplied WHERE gcaOrdID='" . escape_string($ordID) . "'";
			$result2=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result2)>0) $authnumber=$GLOBALS['xxGifCtc'];
			ect_free_result($result2);
		}
		if($authnumber=='') $authnumber='Email';
	}else
		$success=FALSE;
	ect_free_result($result);
	if($success){
		ect_query("UPDATE cart SET cartDateAdded='" . date('Y-m-d',time()+($dateadjust*60*60)) . "',cartCompleted=1 WHERE cartCompleted<>1 AND cartOrderID='" . escape_string($ordID) . "'") or ect_error();
		ect_query("UPDATE orders SET ordStatus=" . $ordStatus . ",ordAuthStatus='',ordAuthNumber='" . escape_string(substr($authnumber,0,48)) . "',ordDate='" . date('Y-m-d H:i:s',time()+($dateadjust*60*60)) . "' WHERE ordAuthNumber='' AND (ordPayProvider=" . $ppid . " OR (ordTotal-ordDiscount)<=0) AND ordID='" . escape_string($ordID) . "'") or ect_error();
		order_success($ordID,$emailAddr,$sendEmail);
	}else
		order_failed();
}elseif(getget('OrderID')!='' && getget('TransRefNumber')!=''){ // PSiGate
	$sSQL='SELECT payProvID FROM payprovider WHERE payProvEnabled=1 AND payProvID=11 OR payProvID=12';
	$result=ect_query($sSQL) or ect_error();
	$success=(ect_num_rows($result) > 0);
	ect_free_result($result);
	if(getget('Approved') != 'APPROVED') $success=FALSE;
	if(getget('CustomerRefNo') != substr(md5(getget('OrderID').':'.@$secretword), 0, 24)) $success=FALSE;
	if($success){
		$ordID=getget('OrderID');
		ect_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'") or ect_error();
		ect_query("UPDATE orders SET ordStatus=3,ordAuthStatus='',ordAVS='" . escape_string(getget('AVSResult').'/'.getget('IPResult')) . "',ordCVV='" . escape_string(getget('CardIDResult')) . "',ordAuthNumber='" . escape_string(getget('CardAuthNumber')) . "',ordTransID='" . escape_string(getget('CardRefNumber')) . "' WHERE (ordPayProvider=11 OR ordPayProvider=12) AND ordID='" . escape_string($ordID) . "'") or ect_error();
		order_success($ordID,$emailAddr,$sendEmail);
	}else{
		$errtext=getget('ErrMsg');
		order_failed();
	}
}elseif(getpost('ponumber')!='' && (getpost('approval_code')!='' || getpost('failReason')!='')){ // Linkpoint
	$ordID=escape_string(getpost('ponumber'));
	$ordIDa=explode('.', $ordID);
	$ordID=$ordIDa[0];
	if(is_numeric($ordID) && getpayprovdetails(16,$data1,$data2,$data3,$demomode,$ppmethod)){
		$theauthcode=escape_string(getpost('approval_code'));
		$thesuccess=strtolower(getpost('status'));
		if(($thesuccess=="approved" || $thesuccess=="submitted") && $theauthcode!=''){
			$autharr=explode(':', $theauthcode);
			if($autharr[0]=='Y' && count($autharr) >= 3){
				$theauthcode=$autharr[1];
				$theavscode=$autharr[2];
				$sSQL="SELECT ordID FROM orders WHERE ordAuthNumber='" . substr($theauthcode,0,6) . "' AND ordPayProvider=16 AND ordID='" . escape_string($ordID) . "'";
				$result=ect_query($sSQL) or ect_error();
				$foundorder=(ect_num_rows($result)>0);
				ect_free_result($result);
				if($foundorder){
					do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
				}else{
					ect_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='$ordID'") or ect_error();
					ect_query("UPDATE orders SET ordStatus=3,ordAuthStatus='',ordAVS='" . escape_string(substr($theavscode,0,3)) . "',ordCVV='" . escape_string(substr($theavscode,3)) . "',ordAuthNumber='" . substr($theauthcode,0,6) . "',ordTransID='" . substr($theauthcode,6) . "' WHERE ordPayProvider=16 AND ordID='" . escape_string($ordID) . "'") or ect_error();
					order_success($ordID,$emailAddr,$sendEmail);
				}
			}else{
				$errtext='Invalid auth code';
				order_failed();
			}
		}else{
			$errtext=getpost('failReason');
			$errtextarr=explode(':', $errtext);
			if(@$errtextarr[1]!='') $errtext=$errtextarr[1];
			order_failed();
		}
	}else
		order_failed();
}elseif(getpost('oid')!='' && getpost('response_hash')!='' && getpost('txndate_processed')!=''){ // Linkpoint 2.0
	$ordID=getpost('oid');
	$ordIDa=explode('.', $ordID);
	$ordID=$ordIDa[0];
	$theauthcode=trim(replace(getpost('approval_code'),"'",''));
	$lphash=getpost('response_hash');
	if(is_numeric($ordID) && getpayprovdetails(16,$data1,$data2,$data3,$demomode,$ppmethod)){
		$sSQL="SELECT ordPrivateStatus FROM orders WHERE ordID='" . escape_string($ordID) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)) $txndatetime=$rs['ordPrivateStatus']; else $txndatetime='';
		ect_free_result($result);
		$str=$data3 . $theauthcode . getpost('chargetotal') . '840' . $txndatetime . $data1;
		$hex_str='';
		for($i=0; $i < strlen($str); $i++){
			$hex_str.=dechex(ord($str[$i]));
		}
		$ourhash=hash('sha256', $hex_str);
		if($ourhash!=$lphash){
			$errtext='Invalid Response Hash';
			order_failed();
		}elseif(strtoupper(getpost('status'))=='APPROVED' || strtoupper(getpost('status'))=='SUBMITTED'){
			$autharr=explode(':', $theauthcode);
			if($autharr[0]=='Y' && count($autharr) >= 3){
				$theauthcode=$autharr[1];
				$theavscode=$autharr[2];
				$sSQL="SELECT ordID FROM orders WHERE ordAuthNumber='" . substr($theauthcode,0,6) . "' AND ordPayProvider=16 AND ordID='" . $ordID . "'";
				$result=ect_query($sSQL) or ect_error();
				$foundorder=(ect_num_rows($result)>0);
				ect_free_result($result);
				if($foundorder){
					do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
				}else{
					ect_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='$ordID'") or ect_error();
					ect_query("UPDATE orders SET ordStatus=3,ordAuthStatus='',ordAVS='" . escape_string(substr($theavscode,0,3)) . "',ordCVV='" . escape_string(substr($theavscode,3)) . "',ordAuthNumber='" . substr($theauthcode,0,6) . "',ordTransID='" . substr($theauthcode,6) . "' WHERE ordPayProvider=16 AND ordID='" . escape_string($ordID) . "'") or ect_error();
					order_success($ordID,$emailAddr,$sendEmail);
				}
			}else{
				$errtext='Invalid auth code';
				order_failed();
			}
		}else{
			$errtext=getpost('fail_reason');
			order_failed();
		}
	}else
		order_failed();
}elseif(substr(getget('crypt'),0,1)=='@' && getpayprovdetails(24,$data1,$data2,$data3,$demomode,$ppmethod)){ // SagePay
	function removePKCS5Padding($input){
		global $StatusDetail;
		$blockSize = 16;
		$padChar = ord($input[strlen($input) - 1]);
		if ($padChar > $blockSize){
			$StatusDetail='Invalid encryption string';
			return('');
		}
		if (strspn($input, chr($padChar), strlen($input) - $padChar) != $padChar){
			$StatusDetail='Invalid encryption string';
			return('');
		}
		$unpadded = substr($input, 0, (-1) * $padChar);
		if (preg_match('/[[:^print:]]/', $unpadded)){
			$StatusDetail='Invalid encryption string';
			return('');
		}
		return $unpadded;
	}
	function decryptAes($strIn, $password){
		global $StatusDetail;
		$strInitVector = $password;
		$hex = substr($strIn, 1);
		if (!preg_match('/^[0-9a-fA-F]+$/', $hex)){
			$StatusDetail='Invalid encryption string';
			return('');
		}
		$strIn = pack('H*', $hex);
		$string = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $password, $strIn, MCRYPT_MODE_CBC, $strInitVector);
		return removePKCS5Padding($string);
	}
	function getToken($thisString){
		$Tokens = array('Status','StatusDetail','VendorTxCode','VPSTxId','TxAuthNo','Amount','AVSCV2','AddressResult','GiftAid');
		$output = array();
		$resultArray = array();
		for ($i = count($Tokens)-1; $i >= 0 ; $i--){
			$start = strpos($thisString, $Tokens[$i]);
			if ($start !== false){
				$resultArray[$i]=new stdClass();
				$resultArray[$i]->start = $start;
				$resultArray[$i]->token = $Tokens[$i];
			}
		}
		sort($resultArray);
		for ($i = 0; $i<count($resultArray); $i++){
			$valueStart = $resultArray[$i]->start + strlen($resultArray[$i]->token) + 1;
			if ($i==(count($resultArray)-1)) {
				$output[$resultArray[$i]->token] = substr($thisString, $valueStart);
			}else{
				$valueLength = $resultArray[$i+1]->start - $resultArray[$i]->start - strlen($resultArray[$i]->token) - 2;
				$output[$resultArray[$i]->token] = substr($thisString, $valueStart, $valueLength);
			}
		}
		return $output;
	}
	function randomise() {
		list($usec, $sec) = explode(' ', microtime());
		return (float) $sec + ((float) $usec * 100000);
	}
	if(($Decoded=decryptAes(getget('crypt'),$data2))!=''){
		$values = getToken($Decoded);

		$ordID = @$values['VendorTxCode'];
		$Status = @$values['Status'];
		$StatusDetail = @$values['StatusDetail'];
		$VPSTxId = @$values['VPSTxId'];
		$TxAuthNo = @$values['TxAuthNo'];
		$Amount = @$values['Amount'];
		$AVSCV2 = @$values['AVSCV2'];
		$currorderstat=0;
		if($AVSCV2=='ALL DATA MATCHED' || $AVSCV2=='ALL DATA MATCH' || $AVSCV2=='ALL MATCH'){
			$ordAVS='Y';
			$ordCVV='Y';
		}elseif($AVSCV2=='SECURITY CODE MATCH ONLY'){
			$ordAVS='N';
			$ordCVV='Y';
		}elseif($AVSCV2=='ADDRESS MATCH ONLY'){
			$ordAVS='Y';
			$ordCVV='N';
		}elseif($AVSCV2=='NO DATA MATCHES'){
			$ordAVS='N';
			$ordCVV='N';
		}elseif($AVSCV2=='DATA NOT CHECKED'){
			$ordAVS='X';
			$ordCVV='X';
		}else{
			$ordAVS='E';
			$ordCVV='E';
		}
	}
	if($ordID!='' && ($TxAuthNo!=''||$Status=='AUTHENTICATED') && ($Status=='OK'||$Status=='AUTHENTICATED')){
		$theorderarray = explode('-', $ordID);
		$ordID = $theorderarray[0];
		$sSQL="SELECT ordStatus FROM orders WHERE ordPayProvider=24 AND ordID='" . escape_string($ordID) . "'";
		$result = ect_query($sSQL) or ect_error();
		if($rs = ect_fetch_assoc($result))
			$currorderstat=(int)$rs['ordStatus'];
		if($currorderstat==2){
			$ordAuthStatus='';
			$sSQL="UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'";
			ect_query($sSQL) or ect_error();
			if($TxAuthNo==''){
				$ordAuthStatus='Pending: Settlement';
				$TxAuthNo='UNSETTLED';
			}
			$sSQL="UPDATE orders SET ordStatus=3,ordAVS='".$ordAVS."',ordCVV='".$ordCVV."',ordTransID='" . escape_string($VPSTxId) . "',ordAuthNumber='" . escape_string($TxAuthNo) . "',ordAuthStatus='" . escape_string($ordAuthStatus) . "' WHERE ordPayProvider=24 AND ordID='" . escape_string($ordID) . "'";
			ect_query($sSQL) or ect_error();
			order_success($ordID,$emailAddr,$sendEmail);
		}else
			do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
	}else{
		$errtext=$StatusDetail;
		order_failed();
	}
}elseif(getget('OrdNo')!='' && getget('ErrMsg')!=''){ // PSiGate Error Reporting
	$errtext=getget('ErrMsg');
	order_failed();
}else{
	if(@$GLOBALS['mobilebrowser'] && count(@$_POST)==0 && count(@$_GET)==0 && getpayprovdetails(1,$data1,$data2,$data3,$demomode,$ppmethod)){
		print '<div class="ppmobilereturn0" style="text-align:center;margin:30px"><img src="images/paypalacceptmark.gif" alt="PayPal" /></div><div class="ppmobilereturn1" style="text-align:center;margin:30px">Your PayPal order details have now been received.</div>';
		print '<div class="ppmobilereturn2" style="text-align:center;margin:30px">For those that have paid by PayPal, please check your email inbox for your receipt and order details.</div>';
	}else{
		@include './vsadmin/inc/customppreturn.php';
	}
}
if((@$googleanalyticsinfo==TRUE||@$usegoogleuniversal) && is_numeric($ordID)){
	// Order ID, Affiliation, Total, Tax, Shipping, City, State, Country
	if(@$usegoogleuniversal)
		$googleanalyticstrackorderinfo="ga('ecommerce:addTransaction',{'id':'".$ordID."','affiliation':'".$affilID."','revenue':'".$ordTotal."','shipping':'".$ordShipping."','tax':'".($ordStateTax+$ordHSTTax+$ordCountryTax)."'});\r\n";
	else
		$googleanalyticstrackorderinfo="\r\n" . (@$usegoogleasync ? "_gaq.push(['_addTrans'," : "pageTracker._addTrans(") . '"' . $ordID . '","' . $affilID . '","' . $ordTotal . '","' . ($ordStateTax+$ordHSTTax+$ordCountryTax) . '","' . ($ordShipping+$ordHandling) . '","' . (@$usegoogleasync ? str_replace('"','\\"',$ordCity) . '","' : '') . str_replace('"','\\"',$ordState) . '","' . str_replace('"','\\"',$ordCountry) . '"' . (@$usegoogleasync ? ']' : '') . ');' . "\r\n";
	$sSQL='SELECT cartProdID,cartProdName,cartProdPrice,cartQuantity,'.getlangid('sectionName',256).",pSKU FROM cart INNER JOIN products ON cart.cartProdID=products.pID INNER JOIN sections ON products.pSection=sections.sectionID WHERE cartOrderID='".escape_string($ordID)."' ORDER BY cartID";
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		// Order ID, SKU, Product Name , Category, Price, Quantity
		if(@$usegoogleuniversal)
			$googleanalyticstrackorderinfo.="ga('ecommerce:addItem',{'id':'".$ordID."','name':'".jsescape($rs['cartProdName'])."','sku':'".jsescape($rs['cartProdID'])."','category':'".jsescape($rs[getlangid('sectionName',256)])."','price':'".$rs['cartProdPrice']."','quantity':'".$rs['cartQuantity']."'});\r\n";
		else
			$googleanalyticstrackorderinfo.=(@$usegoogleasync ? "_gaq.push(['_addItem'," : "pageTracker._addItem(") . '"' . $ordID . '","' . str_replace('"','\\"',$rs['cartProdID']) . '","' . str_replace('"','\\"',$rs['cartProdName']) . '","' . str_replace('"','\\"',$rs[getlangid('sectionName',256)]) . '","' . $rs['cartProdPrice'] . '","' . $rs['cartQuantity'] . '"' . (@$usegoogleasync ? ']' : '') . ');' . "\r\n";
	}
	ect_free_result($result);
	if(@$usegoogleuniversal)
		$googleanalyticstrackorderinfo.="ga('ecommerce:send');" . "\r\n";
	else
		$googleanalyticstrackorderinfo.=(@$usegoogleasync ? "_gaq.push(['_trackTrans']);" : "pageTracker._trackTrans();") . "\r\n";
}
?>