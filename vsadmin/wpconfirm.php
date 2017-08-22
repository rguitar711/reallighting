<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
$isvsadmindir=TRUE;
include "db_conn_open.php";
include "inc/languagefile.php";
include "includes.php";
include "inc/incemail.php";
include "inc/incfunctions.php";
if(@$htmlemails==TRUE) $emlNl = '<br />'; else $emlNl="\n";
$emailtxt='';
		if(@$wpconfirmpage==''){ ?>
<html>
<head>
<title>Thanks for shopping with us</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php print $adminencoding ?>">
<style type="text/css">
<!--
A:link{ COLOR:#FFFFFF; TEXT-DECORATION:none }
A:visited{ COLOR:#FFFFFF; TEXT-DECORATION:none }
A:active{ COLOR:#FFFFFF; TEXT-DECORATION:none }
A:hover{ COLOR:#f39000; TEXT-DECORATION:underline }
TD{ FONT-FAMILY:Verdana; FONT-SIZE:13px }
P{ FONT-FAMILY:Verdana; FONT-SIZE:13px }
-->
</style>
</head>
<?php
		} // wpconfirmpage
$success=FALSE;
$worldpaycallbackerror=FALSE;
$errtext="";
$errormsg="";
$thereference="";
$orderText="";
$ordGrandTotal = $ordTotal = $ordStateTax = $ordHSTTax = $ordCountryTax = $ordShipping = $ordHandling = $ordDiscount = 0;
$ordID = $affilID = $ordCity = $ordState = $ordCountry = $ordDiscountText = '';
$_SESSION['couponapply']=NULL; unset($_SESSION['couponapply']);
$_SESSION['giftcerts']=NULL; unset($_SESSION['giftcerts']);
$_SESSION['cpncode']=NULL; unset($_SESSION['cpncode']);
$alreadygotadmin = getadminsettings();
$success=FALSE;
$isworldpay=FALSE;
$isauthnet=FALSE;
$isnetbanx=FALSE;
$issecpay=FALSE;
$wpconfreturl='';
if(trim(@$_POST['transStatus']) != ''){ // WorldPay
	$isworldpay=TRUE;
	$transstatus=trim(@$_POST['transStatus']);
	$data2cbp='';
	$origstoreurl=$storeurl;
	if(@$pathtossl!=''){
		if(substr($pathtossl,-1)!='/') $pathtossl.='/';
		$storeurl = $pathtossl;
	}
	$ordID = trim(@$_POST['cartId']);
	if(getpayprovdetails(5,$acctno,$data2,$data3,$demomode,$ppmethod)&&is_numeric($ordID)){
		$data2arr = explode('&',$data2,2);
		$data2md5 = @$data2arr[0];
		$data2cbp = @$data2arr[1];
		if($data2cbp != ''){
			if($data2cbp != @$_POST['callbackPW']){
				$transstatus='';
				$errormsg = 'Callback password incorrect';
				$worldpaycallbackerror=TRUE;
			}
		}
		if($transstatus=='Y'){
			$avscode = trim(@$_POST['AVS']);
			if(trim(@$_POST['wafMerchMessage']) != '') $avscode = trim(@$_POST['wafMerchMessage']) . "\r\n" . $avscode;
			ect_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'") or ect_error();
			ect_query("UPDATE orders SET ordStatus=3,ordAVS='" . escape_string($avscode) . "',ordAuthNumber='" . escape_string(@$_POST['transId']) . "' WHERE ordPayProvider=5 AND ordID='" . escape_string($ordID) . "'") or ect_error();
			do_order_success($ordID,$emailAddr,$sendEmail,FALSE,TRUE,TRUE,TRUE);
			$success=TRUE;
			$sSQL = "SELECT ordSessionID FROM orders WHERE ordID='".escape_string($ordID)."'";
			$result = ect_query($sSQL) or ect_error();
			if($rs = ect_fetch_assoc($result))$sessionid=$rs['ordSessionID']; else $sessionid='notvalid';
			ect_free_result($result);
			$retprms='?ectprnm=wpconfirm&pprov=5&ordid='.$ordID.'&rethash='.strtoupper(md5($ordID.'WPCONFHash'.'5'.$sessionid.'1234'.$adminSecret));
			$wpconfreturl=$storeurl . 'thanks.php' . $retprms;
			print '<meta http-equiv="refresh" content="0; URL=' . $wpconfreturl . '">';
		}
	}
	$storeurl=$origstoreurl;
}elseif(trim(@$_POST['x_response_code']) != ''){ // Authorize.net
	$origstoreurl=$storeurl;
	if(@$pathtossl!=''){
		if(substr($pathtossl,-1)!='/') $pathtossl.='/';
		$storeurl = $pathtossl;
	}
	$ordID = trim(@$_POST['x_invoice_num']);
	if(getpayprovdetails(3,$data1,$data2,$data3,$demomode,$ppmethod)&&is_numeric($ordID)){
		$isauthnet=TRUE;
		$hashstr='';
		if(trim($data3)!=''){
			$hashstr=strtoupper(md5(trim($data3) . trim($data1) . @$_POST['x_trans_id'] . number_format((double)@$_POST['x_amount'],2,'.','')));
		}
		$emailtxt .= "MYHASH:" . $hashstr . $emlNl;
		if(trim(@$_POST['x_response_code'])=='1' && $ordID != '' && (($hashstr==strtoupper(@$_POST['x_MD5_Hash'])) || (trim($data3)==''))){
			$vsAUTHCODE = trim(@$_POST['x_auth_code']);
			if($vsAUTHCODE=='' && trim(@$_POST['x_method'])=='ECHECK') $vsAUTHCODE='eCheck';
			ect_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'") or ect_error();
			ect_query("UPDATE orders SET ordStatus=3,ordAVS='" . escape_string(@$_POST['x_avs_code']) . "',ordCVV='" . escape_string(@$_POST['x_cvv2_resp_code']) . "',ordAuthNumber='" . escape_string($vsAUTHCODE) . "',ordTransID='" . escape_string(@$_POST['x_trans_id']) . "' WHERE ordPayProvider=3 AND ordID='" . escape_string($ordID) . "'") or ect_error();
			do_order_success($ordID,$emailAddr,$sendEmail,FALSE,TRUE,TRUE,TRUE);
			$success=TRUE;
			$sSQL = "SELECT ordSessionID FROM orders WHERE ordID='".escape_string($ordID)."'";
			$result = ect_query($sSQL) or ect_error();
			if($rs = ect_fetch_assoc($result))$sessionid=$rs['ordSessionID']; else $sessionid='notvalid';
			ect_free_result($result);
			$retprms='?ectprnm=wpconfirm&pprov=3&ordid='.$ordID.'&rethash='.strtoupper(md5($ordID.'WPCONFHash'.'3'.$sessionid.'1234'.$adminSecret));
			$wpconfreturl=$storeurl . 'thanks.php' . $retprms;
			print '<meta http-equiv="refresh" content="0; URL=' . $wpconfreturl . '">';
		}else{
			if(trim($data3)!='' && ($hashstr!=strtoupper(@$_POST['x_MD5_Hash'])))
				$errormsg='Invalid Hash Value';
			else
				$errormsg = @$_POST['x_response_code'] . ' (' . trim(@$_POST['x_response_reason_code']) . ') ' . trim(@$_POST['x_response_reason_text']);
		}
	}
	$storeurl=$origstoreurl;
}elseif(trim(@$_REQUEST["trans_id"]) != ""){ // Secpay / PayPoint
	if(getpayprovdetails(9,$data1,$data2,$data3,$demomode,$ppmethod)){
		$issecpay=TRUE;
		$data2arr = explode("&",trim($data2),2);
		$data2md5=@$data2arr[0];
		$callbacksuccess=TRUE;
		$origstoreurl = $storeurl;
		if(@$pathtossl!=''){
			if(substr($pathtossl,-1)!="/") $pathtossl .= "/";
			$storeurl = $pathtossl;
		}
		if(trim(@$_REQUEST['valid'])=='true' && trim(@$_REQUEST['auth_code'])!=''){
			$ordID = trim(@$_REQUEST['trans_id']);
			if($data2md5 != ''){
				$thehash = md5('trans_id=' . $ordID . '&amount=' . trim(@$_REQUEST['amount']) . '&callback=' . $storeurl . 'vsadmin/' . (@$wpconfirmpage=='' ? 'wpconfirm.php' : $wpconfirmpage) . '&' . $data2md5);
				if(@$_REQUEST['hash'] != $thehash) $callbacksuccess=FALSE;
			}
			if(! $callbacksuccess){
				$errormsg = 'Callback password incorrect';
			}else{
				ect_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'") or ect_error();
				ect_query("UPDATE orders SET ordStatus=3,ordAVS='" . escape_string(@$_REQUEST["cv2avs"]) . "',ordAuthNumber='" . escape_string(@$_REQUEST["auth_code"]) . "' WHERE ordPayProvider=9 AND ordID='" . escape_string($ordID) . "'") or ect_error();
				do_order_success($ordID,$emailAddr,$sendEmail,FALSE,TRUE,TRUE,TRUE);
				$success=TRUE;
			}
		}else
			$errormsg = trim(@$_REQUEST["message"]);
		$storeurl=$origstoreurl;
	}
}elseif(trim(@$_POST['netbanx_reference']) != ''){ // Netbanx
	if(getpayprovdetails(15,$data1,$data2,$data3,$demomode,$ppmethod)){
		$isnetbanx=TRUE;
		$thereference = trim(@$_POST['netbanx_reference']);
		if(trim(@$_SERVER['REMOTE_ADDR']) != '195.224.77.2' && trim(@$_SERVER['REMOTE_ADDR']) != '80.65.254.6')
			$errormsg = 'Error: This transaction does not appear to have been initiated by Netbanx';
		elseif($thereference!='0' && trim(@$_POST['order_id'])!=''){
			$ordID = trim(@$_POST['order_id']);
			ect_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'") or ect_error();
			$allchecks = 'X';
			if(trim(@$_POST['houseno_auth'])=='Matched')
				$allchecks = 'Y';
			elseif(trim(@$_POST['houseno_auth'])=='Not matched')
				$allchecks = 'N';
			if(trim(@$_POST['postcode_auth'])=='Matched')
				$allchecks .= 'Y';
			elseif(trim(@$_POST['postcode_auth'])=='Not matched')
				$allchecks .= 'N';
			else
				$allchecks .= 'X';
			$cvv = 'X';
			if(trim(@$_POST['CV2_auth'])=='Matched')
				$cvv = 'Y';
			elseif(trim(@$_POST['CV2_auth'])=='Not matched')
				$cvv = 'N';
			ect_query("UPDATE orders SET ordStatus=3,ordAVS='" . $allchecks . "',ordCVV='" . $cvv . "',ordAuthNumber='" . $thereference . "' WHERE ordPayProvider=15 AND ordID='" . escape_string($ordID) . "'") or ect_error();
			do_order_success($ordID,$emailAddr,$sendEmail,FALSE,TRUE,TRUE,TRUE);
			$success=TRUE;
		}else
			$errormsg = 'Transaction Declined';
	}
}
		if(@$wpconfirmpage==''){
?>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#F39900">
  <tr>
    <td>
      <table width="100%" border="1" cellspacing="1" cellpadding="3">
        <tr> 
          <td rowspan="4" bgcolor="#333333">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
          <td width="100%" bgcolor="#333333" align="center"><span style="color:#FFFFFF;font-family:Verdana,Helvetica,sans-serif;font-weight:bold"><?php print $GLOBALS['xxInAssc'] . "&nbsp;";
		if($isworldpay)
			print "WorldPay";
		elseif($isauthnet)
			print "Authorize.Net";
		elseif($isnetbanx)
			print "Netbanx";
		elseif($issecpay)
			print "SECPay";
		else
			print '<a href="http://www.ecommercetemplates.com">EcommerceTemplates.com</a>' ?></span></td>
          <td rowspan="4" bgcolor="#333333">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        </tr>
        <tr> 
          <td width="100%" bgcolor="#637BAD" align="center"><span style="color:#FFFFFF;font-family:Verdana,Helvetica,sans-serif;font-weight:bold;font-size:16px"><?php print $GLOBALS['xxTnkStr']?></span></td>
        </tr>
        <tr> 
          <td width="100%" align="center" bgcolor="#F5F5F5"> 
<?php	} // wpconfirmpage
		if($isworldpay){ ?>
			<p>&nbsp;</p>
			<p align="center"><span style="font-family:Verdana,Helvetica,sans-serif;font-weight:bold;font-size:12px"><?php print $GLOBALS['xxTnkWit']?> <WPDISPLAY ITEM=compName></span></p>
<?php		if($worldpaycallbackerror){ ?>
			<table width="100%" border="0" cellspacing="3" cellpadding="3" bgcolor="">
			  <tr> 
				<td width="100%" colspan="2" align="center"><?php print $GLOBALS['xxThkErr']?>
				<p>The error report returned by the server was:<br /><strong><?php print $errormsg?></strong></p>
				<a href="<?php print $storeurl?>"><span style="color:#637BAD"><strong><?php print $GLOBALS['xxCntShp']?></strong></span></a><br />
				<p>&nbsp;</p>
				</td>
			  </tr>
			</table>
            <p><wpdisplay item="banner"></p>
			<p><span style="font-size:10px;font-weight:bold"><?php print $GLOBALS['xxPlsNt1'] . ' ' . $GLOBALS['xxMerRef'] . ' ' . $GLOBALS['xxPlsNt2']?></span></p>
<?php		}else{ ?>
			<div style="text-align:center">You will now be forwarded to view your receipt.</div>
			<div style="text-align:center"><?php print $xxForAut.' <a href="'.$wpconfreturl.'">'.$xxClkHere.'</a>'?></div>
<?php		} ?>
			<p>&nbsp;</p>
<?php	}elseif($isauthnet&&$success){ ?>
			<div style="text-align:center">You will now be forwarded to view your receipt.</div>
			<div style="text-align:center"><?php print $xxForAut.' <a href="'.$wpconfreturl.'">'.$xxClkHere.'</a>'?></div>
			<p>&nbsp;</p>
<?php	}elseif($success){ ?>
		  <table border="0" cellspacing="0" cellpadding="0" width="100%" bgcolor="" align="center">
			<tr>
			  <td width="100%" align="center">
				<table width="80%" border="0" cellspacing="3" cellpadding="3" bgcolor="">
				  <tr> 
					<td width="100%" align="center"><?php print $GLOBALS['xxThkYou']?>
					</td>
				  </tr>
<?php		if(@$digidownloads==TRUE&&!$isworldpay){
				print '</table>';
				$noshowdigiordertext=TRUE;
				include "inc/digidownload.php";
				print '<table width="80%" border="0" cellspacing="3" cellpadding="3" bgcolor="">';
			} ?>
				  <tr> 
					<td width="100%"><?php print str_replace(array("\r\n","\n"),array("<br />","<br />"),$orderText)?>
					</td>
				  </tr>
				  <tr> 
					<td width="100%" align="center"><br /><br />
					<?php print $GLOBALS['xxRecEml']?><br /><br />
					<a href="<?php print $storeurl?>"><span style="color:#637BAD"><strong><?php print $GLOBALS['xxCntShp']?></strong></span></a><br />
					<p>&nbsp;</p>
					</td>
				  </tr>
				</table>
			  </td>
			</tr>
		  </table>
<?php	}else{ ?>
		  <p>&nbsp;</p>
		  <table border="0" cellspacing="0" cellpadding="0" width="100%" bgcolor="" align="center">
			<tr>
			  <td width="100%">
				<table width="100%" border="0" cellspacing="3" cellpadding="3" bgcolor="">
				  <tr> 
					<td width="100%" colspan="2" align="center"><?php print $GLOBALS['xxThkErr']?>
					<p>The error report returned by the server was:<br /><strong><?php print $errormsg?></strong></p>
					<a href="<?php print $storeurl?>"><span style="color:#637BAD"><strong><?php print $GLOBALS['xxCntShp']?></strong></span></a><br />
					<p>&nbsp;</p>
					</td>
				  </tr>
				</table>
			  </td>
			</tr>
		  </table>
<?php	}
$googleanalyticstrackorderinfo='';
if(@$googleanalyticsinfo==TRUE && is_numeric($ordID) && !$isworldpay && !$isauthnet){
	// Order ID, Affiliation, Total, Tax, Shipping, City, State, Country
	$googleanalyticstrackorderinfo = "\r\n" . (@$usegoogleasync ? "_gaq.push(['_addTrans'," : "pageTracker._addTrans(") . '"' . $ordID . '","' . $affilID . '","' . $ordTotal . '","' . ($ordStateTax+$ordHSTTax+$ordCountryTax) . '","' . ($ordShipping+$ordHandling) . '","' . (@$usegoogleasync ? str_replace('"','\\"',$ordCity) . '","' : '') . str_replace('"','\\"',$ordState) . '","' . str_replace('"','\\"',$ordCountry) . '"' . (@$usegoogleasync ? ']' : '') . ');' . "\r\n";
	$sSQL = 'SELECT cartProdID,cartProdName,cartProdPrice,cartQuantity,'.getlangid('sectionName',256).",pSKU FROM cart INNER JOIN products ON cart.cartProdID=products.pID INNER JOIN sections ON products.pSection=sections.sectionID WHERE cartOrderID='".escape_string($ordID)."' ORDER BY cartID";
	$result = ect_query($sSQL) or ect_error();
	while($rs = ect_fetch_assoc($result)){
		// Order ID, SKU, Product Name , Category, Price, Quantity
		$googleanalyticstrackorderinfo .= (@$usegoogleasync ? "_gaq.push(['_addItem'," : "pageTracker._addItem(") . '"' . $ordID . '","' . str_replace('"','\\"',$rs['cartProdID']) . '","' . str_replace('"','\\"',$rs['cartProdName']) . '","' . str_replace('"','\\"',$rs[getlangid('sectionName',256)]) . '","' . $rs['cartProdPrice'] . '","' . $rs['cartQuantity'] . '"' . (@$usegoogleasync ? ']' : '') . ');' . "\r\n";
	}
	ect_free_result($result);
	$googleanalyticstrackorderinfo .= (@$usegoogleasync ? "_gaq.push(['_trackTrans']);" : "pageTracker._trackTrans();") . "\r\n";
}
		if(@$wpconfirmpage==''){ ?>
          </td>
        </tr>
        <tr> 
          <td width="100%" bgcolor="#333333" align="center"><span style="color:#FFFFFF;font-family:Verdana,Helvetica,sans-serif;font-weight:bold;font-size:12px"><a href="<?php print $storeurl?>"><?php print $GLOBALS['xxClkBck']?></a></span></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php
if(@$googleanalyticstrackorderinfo!='' && @$googleanalyticstrackid!=''){
?>
<script type="text/javascript">
var gaJsHost = (("https:"==document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("<?php print $googleanalyticstrackid?>");
pageTracker._trackPageview();
} catch(err) {}<?php print $googleanalyticstrackorderinfo?></script>
<?php
} ?>
</body>
</html>
<?php	}
if(@$debugmode==TRUE){
	foreach(@$_POST as $key => $val){
		$emailtxt .= $key . ' : ' . $val . $emlNl;
	}
	dosendemail($emailAddr, $emailAddr, '', 'wpconfirm.php debug', $emailtxt);
} ?>