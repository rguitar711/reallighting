<SCRIPT language="php">
@include 'adminsession.php';
session_cache_limiter('none');
session_start();
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property
//of Internet Business Solutions SL. Any use, reproduction, disclosure or copying
//of any kind without the express and written permission of Internet Business 
//Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
$isvsadmindir=TRUE;
include 'db_conn_open.php';
include 'inc/languageadmin.php';
include 'inc/incfunctions.php';
$languageid=1;
$result=ect_query('SELECT storelang FROM admin WHERE adminid=1') or ect_error();
if($rs=ect_fetch_assoc($result)){
	$storelangarr=explode('|',$rsAdmin['storelang']);
	$storelang=@$storelangarr[0];
}
ect_free_result($result);
if(getpost('posted')=='1' && is_numeric(getpost('id'))){
	$result=ect_query('SELECT ordID,ordLang FROM orders WHERE ordID='.getpost('id')) or ect_error();
	if($rs=ect_fetch_assoc($result)) $languageid=$rs['ordLang']+1; else $languageid=1;
	ect_free_result($result);
	if($languageid>1){
		if($languageid==2) $storelang=@$storelangarr[1];
		if($languageid==3) $storelang=@$storelangarr[2];
	}
}
if(@$storelang=='') $storelang='en'; // de dk en es fr it nl pt
include 'inc/languagefile_'.$storelang.'.php';
include 'includes.php';
include 'inc/incemail.php';
if(@$storesessionvalue=="") $storesessionvalue="virtualstore";
if(@$_SESSION["loggedon"] != $storesessionvalue || @$disallowlogin==TRUE){
	if(@$_SERVER["HTTPS"]=="on" || @$_SERVER["SERVER_PORT"]=="443")$prot='https://';else $prot='http://';
	header('Location: '.$prot.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/login.php');
	exit;
}
$isprinter=FALSE;
</SCRIPT>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Email Popup</title>
<link rel="stylesheet" type="text/css" href="adminstyle.css"/>
<meta http-equiv="Content-Type" content="text/html; charset=<?php print $adminencoding ?>"/>
</head>
<body>
&nbsp;<br />
<div>
<form method="post" action="popupemail.php">
<?php
	$ordGrandTotal=$ordTotal=$ordStateTax=$ordHSTTax=$ordCountryTax=$ordShipping=$ordHandling=$ordDiscount=0;
	$affilID=$ordState=$ordCountry=$ordDiscountText='';
	if(@$_POST['posted']=='1'){
		$alreadygotadmin = getadminsettings();
		do_order_success(@$_POST['id'],$emailAddr,@$_POST['store']=='1',FALSE,@$_POST['customer']=='1',@$_POST['affiliate']=='1',@$_POST['manufacturer']=='1' ? 2 : FALSE);
?>
<p>&nbsp;</p>
<p align="center"><?php print $yyOpSuc?></p>
<p align="center"><a href="javascript:window.close()"><strong><?php print $GLOBALS['xxClsWin']?></strong></a></p>
<?php
	}elseif(@$_POST['posted']=='2' && is_numeric($_POST['oid'])){
		$ordID = str_replace("'",'',$_POST['oid']);
		$alreadygotadmin = getadminsettings();
		$sSQL = "SELECT ordTransID,ordPayProvider,ordAuthNumber,payProvData1,payProvData2,payProvDemo FROM orders INNER JOIN payprovider ON orders.ordPayProvider=payprovider.payProvID WHERE ordID='" . escape_string($ordID) . "'";
		$result = ect_query($sSQL) or ect_error();
		$rs = ect_fetch_assoc($result);
		$transid=$rs['ordTransID'];
		$authcode=$rs['ordAuthNumber'];
		$pos=strpos($authcode,'-');
		if (! ($pos === false))
			$authcode = substr($authcode, $pos + 1);
		$login = $rs['payProvData1'];
		$trankey = $rs['payProvData2'];
		if(@$secretword!=''){
			$login = upsdecode($login, $secretword);
			$trankey = upsdecode($trankey, $secretword);
		}
		$demomode=((int)$rs['payProvDemo']==1);
		$parmList = 'x_version=3.1&x_delim_data=True&x_relay_response=False&x_delim_char=|';
		$parmList .= "&x_login=" . $login;
		$parmList .= "&x_tran_key=" . $trankey;
		$parmList .= "&x_trans_id=" . $transid;
		$parmList .= "&x_auth_code=" . $authcode;
		$parmList .= "&x_type=PRIOR_AUTH_CAPTURE";
		if($demomode) $parmList .= "&x_test_request=TRUE";
		print '&nbsp;<br /><p align="center" id="process">Processing. Please wait...</p>';
		flush();
		$success=TRUE;
		if(@$pathtocurl != ""){
			exec($pathtocurl . ' --data-binary ' . escapeshellarg($parmList) . ' https://secure.authorize.net/gateway/transact.dll', $res, $retvar);
			$res = implode("\n",$res);
		}else{
			if (!$ch = curl_init()) {
				$vsRESPMSG = "cURL package not installed in PHP";
				$success=false;
			}else{
				curl_setopt($ch, CURLOPT_URL,'https://secure.authorize.net/gateway/transact.dll'); 
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $parmList);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$res = curl_exec($ch);
				if(curl_error($ch) != ""){
					$vsRESULT="x";
					$vsRESPMSG= "Error with cURL installation: " . curl_error($ch) . "<br />";
					$success=false;
				}else{
					curl_close($ch);
				}
			}
		}
		if($success){
			$varString = explode('|', $res);
			$vsRESULT=$varString[0];
			$vsRESPMSG=$varString[3];
			$success==FALSE;
			if((int)$vsRESULT==1){
				$success=TRUE;
				$vsRESPMSG=$yyOpSuc;
				if(@$capturedordstatus != ""){
					$sSQL="UPDATE orders SET ordStatus=" . $capturedordstatus . " WHERE ordID=" . $ordID;
					ect_query($sSQL) or ect_error();
				}
			}
		}
?>
<script type="text/javascript">
thestyle = document.getElementById('process').style;
thestyle.display = 'none';
</script>
<p align="center"><?php print $vsRESPMSG?></p>
<p align="center"><a href="javascript:window.close()"><strong><?php print $GLOBALS['xxClsWin']?></strong></a></p>
<?php
	}elseif(trim(@$_GET["id"])!=""){ ?>
<input type="hidden" name="posted" value="1">
<input type="hidden" name="id" value="<?php print @$_GET["id"]?>">
<table width="100%" cellspacing="2" cellpadding="2">
<tr><td colspan="2" align="center"><strong><?php print $yySendFo?></strong></td></tr>
<tr><td align="right" width="60%"><?php print $yyCusto?>: </td><td><input type="checkbox" name="customer" value="1" checked></td></tr>
<tr><td align="right"><?php print $yyAffili?>: </td><td><input type="checkbox" name="affiliate" value="1"></td></tr>
<tr><td align="right"><?php print $yyManDes?>: </td><td><input type="checkbox" name="manufacturer" value="1"></td></tr>
<tr><td align="right"><?php print $xxOrdStr?>: </td><td><input type="checkbox" name="store" value="1"></td></tr>
<tr><td colspan="2" align="center"><input type="submit" value="<?php print $yySubmit?>" /></td></tr>
</table>
<?php
	}elseif(trim(@$_GET["oid"])!=""){ ?>
&nbsp;<br />
<input type="hidden" name="posted" value="2">
<input type="hidden" name="oid" value="<?php print $_GET["oid"]?>">
<table width="100%" cellspacing="2" cellpadding="2">
<tr><td colspan="2" align="center"><strong>Capture funds for order id <?php print $_GET["oid"]?></strong><br />&nbsp;</td></tr>
<tr><td colspan="2" align="center"><input type="submit" value="<?php print $yySubmit?>" /></td></tr>
</table>
<?php
} ?>
</form>
</div>
</body>
</html>
