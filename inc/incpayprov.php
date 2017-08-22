<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
$success=TRUE;
$warning1=FALSE;
$demomodeavailable=TRUE;
if(@$maxloginlevels=="") $maxloginlevels=5;
$alreadygotadmin = getadminsettings();
ect_query('UPDATE payprovider SET payProvAvailable=0,payProvEnabled=0 WHERE payProvID=20') or ect_error();
if(getpost('act')=='domodify' && is_numeric(getpost('id'))){
	$sSQL = 'SELECT payProvName FROM payprovider WHERE payProvID='.getpost('id');
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $payprovname=$rs['payProvName']; else $payprovname='NOT KNOWN';
	ect_free_result($result);
	logevent(@$_SESSION['loginuser'],'PAYPROVIDER',TRUE,'adminpayprov.php','MODIFY '.$payprovname);
	$isenabled=0;
	$demomode=0;
	if(getpost('isenabled')=='1') $isenabled=1;
	if(getpost('demomode')=='1') $demomode=1;
	$sSQL = "UPDATE payprovider SET payProvShow='" . escape_string(getpost('showas')) . "',payProvEnabled=" . $isenabled . ",payProvDemo=" . $demomode . ",payProvLevel=" . (is_numeric(getpost('payProvLevel'))?getpost('payProvLevel'):0) . ",ppHandlingCharge=" . (is_numeric(getpost('pphandlingcharge'))?getpost('pphandlingcharge'):0) . ",ppHandlingPercent=" . (is_numeric(getpost('pphandlingpercent'))?getpost('pphandlingpercent'):0) . ",";
	@include './inc/customppplugin.php';
	if(@$customppplugin==TRUE){
	}elseif(getpost('id')=='5') // WorldPay
		$sSQL.="payProvData1='" . escape_string(getpost('data1')) . "',payProvData2='" . escape_string(getpost('data2')) . '&' . escape_string(getpost('data3')) . "'";
	elseif(getpost('id')=='7'||getpost('id')=='22'||(getpost('id')=='8' && (getpost('data1')!='' || getpost('data4')!=''))) // PayFlow Pro OR PayPal Advanced
		$sSQL.="payProvData1='" . escape_string(getpost('data1')) . '&' . escape_string(getpost('data2')) . '&' . escape_string(getpost('data3')) . '&' . escape_string(getpost('data4')) . "'";
	elseif(getpost('id')=='8') // Payflow Link
		$sSQL.="payProvData1='" . escape_string(getpost('data2')) . "',payProvData2='" . escape_string(getpost('data3')) . "',payProvData3=''";
	elseif(getpost('id')=='9') // PayPoint.net
		$sSQL.="payProvData1='" . escape_string(getpost('data1')) . "',payProvData2='" . escape_string(getpost('data2') . '&' . urlencode(getpost('data2supp'))) . "',payProvData3='" . escape_string(getpost('data3')) . "'";
	elseif(getpost('id')=='10'){ // Capture Card
		$data1 = "";
		for($index=1;$index<=20;$index++){
			if(@$_POST["cardtype" . $index]=="X")
				$data1.="X";
			else
				$data1.="O";
		}
		$sSQL.="payProvData1='" . $data1 . "'";
		if(getpost('data2')!=''){
			$admincert = getpost('data2');
			$admincert = str_replace('-----BEGIN PUBLIC KEY-----','',$admincert);
			$admincert = str_replace('-----END PUBLIC KEY-----','',$admincert);
			$admincert = trim($admincert);
			if(strlen($admincert)>500 || strpos($admincert,'PRIVATE')!==FALSE){
				$success=FALSE;
				$errmsg='Please do not upload the private key. You should only upload the public key.';
			}else{
				ect_query("UPDATE admin SET adminCert='" . escape_string($admincert) . "' WHERE adminID=1") or ect_error();
			}
		}
	}elseif(getpost('id')=='18' || getpost('id')=='19'){ // PayPal Payment Pro
		if(getpost('ppexpressab')=="AB")
			$sSQL.="payProvData1='@AB@" . escape_string(getpost('ppexpressabemail')) . "',payProvData2='" . escape_string(urlencode(getpost('data2'))) . '&' . escape_string(urlencode(getpost('data3'))) . '&' . getpost('apimethod') . '&' . (getpost('billmelater')=='1'?'1':'0') . "'";
		else
			$sSQL.="payProvData1='" . escape_string(getpost('data1')) . "',payProvData2='" . escape_string(urlencode(getpost('data2'))) . '&' . escape_string(urlencode(getpost('data3'))) . '&' . getpost('apimethod') . '&' . (getpost('billmelater')=='1'?'1':'0') . "'";
	}elseif(getpost('id')=='21'){ // Amazon Pay
		$thedata2 = str_replace('&','',getpost('data2')) . '&' . str_replace('&','',getpost('data2b'));
		$sSQL.="payProvData1='" . escape_string(getpost('data1')) . "',payProvData2='" . escape_string($thedata2) . "',payProvData3='" . escape_string(getpost('data3')) . "'";
	}else{
		$thedata1 = getpost('data1');
		$thedata2 = getpost('data2');
		$thedata3 = getpost('data3');
		if(@$secretword!='' && (getpost('id')=="3" || getpost('id')=="13")){
			$thedata1 = upsencode($thedata1, $secretword);
			$thedata2 = upsencode($thedata2, $secretword);
		}
		$sSQL.="payProvData1='" . escape_string($thedata1) . "',payProvData2='" . escape_string($thedata2) . "',payProvData3='" . escape_string($thedata3) . "'";
	}
	for($index=2; $index <= $adminlanguages+1; $index++){
		if(($adminlangsettings & 128)==128) $sSQL.=",payProvShow" . $index . "='" . escape_string(@$_POST["showas" . $index]) . "'";
	}
	for($index=1; $index <= $adminlanguages+1; $index++){
		$languageid = $index;
		if($index==1 || ($adminlangsettings & 4096)==4096){
			$pprovheaders = getpost('pprovheaders' . $index);
			$pprovdropshipheaders = getpost('pprovdropshipheaders' . $index);
			if(! (@$htmlemails && (@$htmleditor=='fckeditor'||@$htmleditor=='ckeditor'))){
				$pprovheaders = str_replace("\r\n", '<br />', $pprovheaders);
				$pprovdropshipheaders = str_replace("\r\n", '<br />', $pprovdropshipheaders);
			}
			$sSQL.=',' . getlangid('pProvHeaders',4096) . "='" . escape_string($pprovheaders) . "'";
			$sSQL.=',' . getlangid('pProvDropShipHeaders',4096) . "='" . escape_string($pprovdropshipheaders) . "'";
		}
	}
	if(getpost('transtype')!='') $sSQL.=",payProvMethod=" . getpost('transtype');
	$sSQL.=" WHERE payProvID=" . getpost('id');
	ect_query($sSQL) or ect_error();
	if(getpost('id')=='18' || getpost('id')=='19'){
		$sSQL = "UPDATE payprovider SET payProvDemo=" . $demomode . ",payProvData1='" . escape_string(getpost('data1')) . "',payProvData2='" . escape_string(urlencode(getpost('data2'))) . '&' . escape_string(urlencode(getpost('data3'))) . '&' . getpost('apimethod') . '&' . (getpost('billmelater')=='1'?'1':'0') . "',payProvMethod=" . getpost('transtype');
		if(getpost('ppexpressab')=='AB')
			$sSQL = "UPDATE payprovider SET payProvDemo=" . $demomode . ",payProvEnabled=0";
		elseif(getpost('id')=='18'){
			if($isenabled==1) $sSQL.=',payProvEnabled=1';
			$sSQL.=' WHERE payProvID=19';
		}
		if(getpost('id')=='19') $sSQL.=' WHERE payProvID=18';
		ect_query($sSQL) or ect_error();
	}
	if($success){
		print '<meta http-equiv="refresh" content="1; url=adminpayprov.php';
		if(getpost('offerpaypal')=='ON')
			print '?act=modify&from=wizard2&id=1';
		else{
			if(getpost('from')=='wizard' || getpost('from')=='wizard2') print '?act=alternate';
		}
		print '" />';
	}
}elseif(getpost('act')=='changepos'){
	$currentorder = (int)getpost('selectedq');
	$neworder = (int)getpost('newval');
	$sSQL = "SELECT payProvID FROM payprovider ORDER BY payProvEnabled DESC,payProvOrder";
	$result=ect_query($sSQL) or ect_error();
	$rowcounter=1;
	while($rs=ect_fetch_assoc($result)){
		$theorder = $rowcounter;
		if($currentorder==$theorder)
			$theorder = $neworder;
		elseif(($currentorder > $theorder) && ($neworder <= $theorder))
			$theorder++;
		elseif(($currentorder < $theorder) && ($neworder >= $theorder))
			$theorder--;
		$sSQL="UPDATE payprovider SET payProvOrder=" . $theorder . " WHERE payProvID=" . $rs["payProvID"];
		ect_query($sSQL) or ect_error();
		$rowcounter++;
	}
	ect_free_result($result);
	print '<meta http-equiv="refresh" content="1; url=adminpayprov.php">';
}
?>
<script type="text/javascript">
/* <![CDATA[ */
function modrec(id) {
	document.mainform.id.value = id;
	document.mainform.act.value = "modify";
	document.mainform.submit();
}
function validate_index(currindex)
{
	var i = eval("document.mainform.newpos"+currindex+".selectedIndex")+1;
	document.mainform.newval.value = i;
	document.mainform.selectedq.value = currindex;
	document.mainform.act.value = "changepos";
	if(i==document.mainform.selectedq.value){
		alert("No change in position");
		return (false);
	}
	document.mainform.submit();
}
function switchheader(id){
	var thestyle = document.getElementById(id).style.display;
	if(thestyle=='block')
		document.getElementById(id).style.display='none';
	else
		document.getElementById(id).style.display='block';
}
/* ]]> */
</script>
<?php if(getpost('act')=="domodify" && $success){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
				<?php print $yyNoAuto?> <a href="adminpayprov.php<?php (getpost('offerpaypal')=='ON'?'?act=modify&from=wizard2&id=1':'')?>"><strong><?php print $yyClkHer?></strong></a>.<br /><br />&nbsp;
                </td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}elseif(getpost('act')=="domodify"){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a><br />&nbsp;</td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}elseif(@$_REQUEST['act']=='alternate'){
		$sSQL = "SELECT payProvID,payProvEnabled FROM payprovider WHERE payProvID in (4,10,14,19)";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){
			if($rs['payProvID']==4) $emailenabled=($rs['payProvEnabled']!=0);
			if($rs['payProvID']==10) $capturecardenabled=($rs['payProvEnabled']!=0);
			if($rs['payProvID']==14) $customenabled=($rs['payProvEnabled']!=0);
			if($rs['payProvID']==19) $ppexpressenabled=($rs['payProvEnabled']!=0);
		}
		ect_free_result($result);
?>
		  <form name="mainform" method="post" action="adminpayprov.php?from=wizard2">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="domodify" />
			<input type="hidden" name="id" value="" />
			<input type="hidden" name="from" value="wizard2" />
            <table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><strong><?php print $yyPPAdm?></strong><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="2" align="center">
				  <table width="80%" border="0" cellspacing="2" cellpadding="2" bgcolor="#FFFFFF">
					<tr>
					  <td align="left" valign="top" bgcolor="#FFFFFF">
						<div>&nbsp;</div>
						<div style="font-size:18px;font-weight:bold;">Set-up Other Payment Options</div>
					  </td>
					</tr>
				  </table>
				  <br />&nbsp;<br />
				  <table width="80%" border="0" cellspacing="2" cellpadding="2" bgcolor="#BFC9E0">
					<tr>
					  <td align="left" valign="top" bgcolor="#<?php if($ppexpressenabled) print 'E6E6E6'; else print 'FFFFFF'?>">
						<div style="font-size:12px;font-weight:bold;margin:5px;"><input type="checkbox" <?php if(! $ppexpressenabled) print 'onclick="modrec(19)"'; else print 'checked="checked" disabled="disabled"'?>/> <a <?php if(! $ppexpressenabled) print 'href="javascript:modrec(19)"'?>>PayPal Express Checkout</a></div>
						<div style="font-size:11px;margin:15px;">According to Jupiter Research, 23% of online shoppers consider PayPal one of their favourite ways to pay online.* 
						Accepting PayPal in addition to Credit Cards is proven to increase your sales.**
						<p align="right" style="margin:0px;font-weight:bold;"><a href="" onclick="newwin=window.open('http://www.paypal.com/en_US/m/demo/wppro/paypal_demo_load_560x355.html','PayPalDemo','menubar=no,scrollbars=yes,width=578,height=372,directories=no,location=no,resizable=yes,status=no,toolbar=no');return false;">See quick demo...</a></p>
						</div>
					  </td>
					</tr>
				  </table>
				  <br />&nbsp;<br />
				  <table width="80%" border="0" cellspacing="2" cellpadding="2" bgcolor="#BFC9E0">
					<tr>
					  <td align="left" valign="top" bgcolor="#FFFFFF">
						<div style="font-size:12px;font-weight:bold;margin:5px;"><input type="checkbox" onclick="modrec(4)" <?php if($emailenabled) print 'checked="checked"'?>/> <a href="javascript:modrec(4)">Email Payment Method</a></div>
						<div style="font-size:11px;margin:15px;">This payment method will simply collect order information and notify the store owner by email if required. It 
						can be used for instace for a Cash-On-Delivery type payment method.</div>
					  </td>
					</tr>
				  </table>
				  <br />&nbsp;<br />
				  <table width="80%" border="0" cellspacing="2" cellpadding="2" bgcolor="#BFC9E0">
					<tr>
					  <td align="left" valign="top" bgcolor="#FFFFFF">
						<div style="font-size:12px;font-weight:bold;margin:5px;"><input type="checkbox" onclick="modrec(10)" <?php if($capturecardenabled) print 'checked="checked"'?>/> <a href="javascript:modrec(10)">Capture Card</a></div>
						<div style="font-size:11px;margin:15px;">This payment method will collect credit card numbers and store them in your database. Unless you are really sure 
						of what you are doing you are highly recommended to use an online payment gateway or PayPal. Using this method means you are responsible for your 
						the security of your customers credit card details.
						<p align="right" style="margin:0px;font-weight:bold;"><a href="http://www.ecommercetemplates.com/help/ecommplus/capture_card.asp" target="_blank">More Details...</a></p>
						</div>
					  </td>
					</tr>
				  </table>
				  <br />&nbsp;<br />
				  <table width="80%" border="0" cellspacing="2" cellpadding="2" bgcolor="#BFC9E0">
					<tr>
					  <td align="left" valign="top" bgcolor="#FFFFFF">
						<div style="font-size:12px;font-weight:bold;margin:5px;"><input type="checkbox" onclick="modrec(14)" <?php if($customenabled) print 'checked="checked"'?>/> <a href="javascript:modrec(14)">Custom Payment Provider</a></div>
						<div style="font-size:11px;margin:15px;">Select this method to configure a custom payment provider. Please click on the link to see a list of the customer payment providers supported.
						<p align="right" style="margin:0px;font-weight:bold;"><a href="http://www.ecommercetemplates.com/help/ecommplus/capture_card.asp" target="_blank">Custom Payment Providers...</a></p>
						</div>
					  </td>
					</tr>
				  </table>
				  <table width="80%" border="0" cellspacing="2" cellpadding="2" bgcolor="#FFFFFF">
					<tr>
					  <td align="left" valign="top" bgcolor="#FFFFFF">
						<p><span style="font-size:11px;font-weight:bold;"><a href="adminpayprov.php?act=list">See full list of payment processors</a></span></p>
					  </td>
					</tr>
				  </table>
				  <br />&nbsp;
				</td>
			  </tr>
			</table>
		  </form>
<?php
}elseif(@$_REQUEST['act']=='modify' && is_numeric(@$_REQUEST['id'])){
		$sSQL = 'SELECT payProvID,payProvName,payProvShow,payProvDemo,payProvEnabled,payProvData1,payProvData2,payProvData3,payProvMethod,payProvShow2,payProvShow3,payProvLevel,ppHandlingCharge,ppHandlingPercent FROM payprovider WHERE payProvAvailable=1 AND payProvID=' . @$_REQUEST['id'];
		$result=ect_query($sSQL) or ect_error();
		$alldata=ect_fetch_assoc($result);
		ect_free_result($result);
		$data2name=$data3name=$signuppage='';
		if($alldata['payProvID']==1){ // PayPal
			$alldata['payProvName']='PayPal Payments Standard';
			$signuppage='https://www.paypal.com/us/webapps/mpp/referral/paypal-payments-standard?partner_id=39HT54MCDMV8E';
			$data1name=$yyEmail;
			$data2name='Identity Token<br /><span style="font-size:10px">(Only when using PDT)</span>';
			$demomodeavailable=TRUE;
			$yyDemoMo='Sandbox';
		}elseif($alldata['payProvID']==2){ // 2Checkout
			$signuppage='https://www.2checkout.com/referral?r=etemplates';
			$data1name=$yyAccNum;
			$data2name=$yyMD5H;
			$warning1=TRUE;
		}elseif($alldata['payProvID']==3 || $alldata['payProvID']==13){ // Authorize.net
			$signuppage='https://www.e-onlinedata.com/ecommercetemplates/';
			$data1name=$yyMercLID;
			$data2name=$yyTrnKey;
			if($alldata['payProvID']==3) $data3name=$yyMD5H;
			if(@$secretword!=''){
				$alldata['payProvData1'] = upsdecode($alldata['payProvData1'], $secretword);
				$alldata['payProvData2'] = upsdecode($alldata['payProvData2'], $secretword);
			}
		}elseif($alldata['payProvID']==4 || $alldata['payProvID']==17){ // Email
			$data1name=$yyEAOrd;
			$demomodeavailable=FALSE;
		}elseif($alldata['payProvID']==5){ // World Pay
			$signuppage='https://business.worldpay.com/partner/ecommerce-templates';
			$data1name=$yyAccNum;
			$data2name=$yyMD5H;
			$warning1=TRUE;
		}elseif($alldata['payProvID']==6){ // NOCHEX
			$signuppage='https://secure.nochex.com/apply/merchant.aspx?partner_id=213200427';
			$data1name=$yyEmail;
		}elseif($alldata['payProvID']==7){ // Payflow Pro
			$signuppage='https://www.paypal.com/us/webapps/mpp/referral/paypal-payflow-gateway?partner_id=39HT54MCDMV8E';
			$alldata['payProvName']='PayPal Payflow Pro';
		}elseif($alldata['payProvID']==8){ // Payflow Link
			$signuppage='https://www.paypal.com/us/webapps/mpp/referral/paypal-payflow-gateway?partner_id=39HT54MCDMV8E';
			$alldata['payProvName']='PayPal Payflow Link';
		}elseif($alldata['payProvID']==9){ // PayPoint.net
			$data1name=$yyMercID;
			$data2name=$yyMD5H;
			$warning1=TRUE;
		}elseif($alldata['payProvID']==10) // Capture Card
			$demomodeavailable=FALSE;
		elseif($alldata['payProvID']==11 || $alldata['payProvID']==12){ // PSiGate
			$data1name=$yyMercID;
		}elseif($alldata['payProvID']==14){ // Custom Payment Processor
			$data1name='Data 1';
			$data2name='Data 2';
			$data3name='Data 3';
		}elseif($alldata['payProvID']==15){ // Netbanx
			$signuppage='http://www1.netbanx.com/campaign/REF_ECOMT_PROG.html';
			$data1name=$yyMercID;
			$data2name='Checksum';
			$demomodeavailable=FALSE;
		}elseif($alldata['payProvID']==16){ // Linkpoint
			$signuppage='http://ecommercetemplates.cardpay-solutions.com/';
			$data1name=$yyNumSto;
			$data2name=$yyOwnSit;
		}elseif($alldata['payProvID']==18 || $alldata['payProvID']==19){ // PayPal Payment Pro
			if($alldata['payProvID']==18) $alldata['payProvName']='PayPal Direct Payments'; else $alldata['payProvName']='PayPal Express Payments';
			$signuppage='https://www.paypal.com/us/webapps/mpp/referral/paypal-payments-pro?partner_id=39HT54MCDMV8E';
			$data1name=$yyApiAcN;
			$data2name=$yyApiPw.'.<br />('.$yyNoPPP.')';
			$yyDemoMo='Sandbox';
			$data2arr = explode('&',trim($alldata['payProvData2']));
			$data2pw=urldecode(@$data2arr[0]);
			$data2path=urldecode(@$data2arr[1]);
			$isthreetoken=(trim(@$data2arr[2])=='1'||($alldata['payProvData1']==''&&$alldata['payProvData2']==''));
			$wantbillmelater=(trim(@$data2arr[3])=='1');
		}elseif($alldata['payProvID']==20){ // Google Checkout
			$signuppage='http://checkout.google.com/sell?promo=sectem';
			$data1name=$yyGMerID;
			$data2name=$yyGMerKe;
			$yyDemoMo='Sandbox';
		}elseif($alldata['payProvID']==21){ // Amazon Pay
			$signuppage='';
			$data1name='Client ID';
			$data2name='AWS Access Key';
			$data3name='Secret Access Key';
			$yyDemoMo='Sandbox';
		}elseif($alldata['payProvID']==22){ // PayPal Advanced
			$signuppage='https://www.paypal.com/us/webapps/mpp/referral/paypal-payments-advanced?partner_id=39HT54MCDMV8E';
			$alldata['payProvName']='PayPal Payments Advanced';
		}elseif($alldata['payProvID']==23){ // Stripe.com
			$data1name='Secret Key';
			$data2name='Publishable Key';
			$data3name='Store Name';
			$demomodeavailable=FALSE;
		}elseif($alldata['payProvID']==24){ // SagePay
			$signuppage='https://support.sagepay.com/apply/default.aspx?PartnerID=%7B7B0AD331-0388-44EA-BE3A-D05D3FB9FE28%7D';
			$data1name='Vendor name';
			$data2name='Encryption Password';
		}else
			$data1name='Data 1';
		if(@$htmlemails!=TRUE) $htmleditor='';
		if(@$htmleditor=='ckeditor'){ ?>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<?php	}elseif(@$htmleditor=='fckeditor'){ ?>
<script type="text/javascript" src="fckeditor.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
function FCKeditor_OnComplete(editorInstance){
	editorInstance.Events.AttachEvent('OnBlur', FCKeditor_OnBlur);
	editorInstance.Events.AttachEvent('OnFocus', FCKeditor_OnFocus);
	editorInstance.ToolbarSet.Collapse();
}
function FCKeditor_OnBlur(editorInstance){
	editorInstance.ToolbarSet.Collapse();
}
function FCKeditor_OnFocus(editorInstance){
	editorInstance.ToolbarSet.Expand();
}
var sBasePath = document.location.pathname.substring(0,document.location.pathname.lastIndexOf('adminpayprov.php'));
/* ]]> */
</script>
<?php	} ?>
<script type="text/javascript">
/* <![CDATA[ */
function validateform(){
	if(document.getElementById("data1")) document.getElementById("data1").disabled=false;
	if(document.getElementById("data2")) document.getElementById("data2").disabled=false;
	if(document.getElementById("data3")) document.getElementById("data3").disabled=false;
	if(document.getElementById("apimethod")) document.getElementById("apimethod").disabled=false;
	return true;
}
function disablepaypalapi(disbd){
	if(disbd){
		document.getElementById("data1span").style.color='#A0A0A0';
		document.getElementById("data2span").style.color='#A0A0A0';
		document.getElementById("data3span").style.color='#A0A0A0';
		document.getElementById("apimethodspan").style.color='#A0A0A0';
		document.getElementById("data1").disabled=true;
		document.getElementById("data2").disabled=true;
		document.getElementById("data3").disabled=true;
		document.getElementById("apimethod").disabled=true;
		document.getElementById("ppexpressabemail").disabled=false;
	}else{
		document.getElementById("data1span").style.color='#000000';
		document.getElementById("data2span").style.color='#000000';
		document.getElementById("data3span").style.color='#000000';
		document.getElementById("apimethodspan").style.color='#000000';
		document.getElementById("data1").disabled=false;
		document.getElementById("data2").disabled=false;
		document.getElementById("data3").disabled=false;
		document.getElementById("apimethod").disabled=false;
		document.getElementById("ppexpressabemail").disabled=true;
	}
}
function advertisingopts(){
	var advtext='<html><head><title>Advertising Options</title><link rel="stylesheet" type="text/css" href="adminstyle.css" /></head><body>' +
		'<div id="header1"><p align="center" style="font-weight:bold;margin:30px;font-size:20px">PayPal Credit&reg; Advertising Options</p></div>' +
		'<div id="main"><p>You can advertise PayPal Credit&reg; to your customers in order to encourage them to use this service from PayPal. In order to do so you will need to get your PayPal publisher id from the PayPal site. This widget will help you do so.</p>' +
		'<p>Once you have your publisher id, please check <a href="http://www.ecommercetemplates.com/help/ecommplus/paypal-express-checkout.asp" style="font-weight:bold" target="_blank">this page</a> to view details about how to setup PayPal Credit&reg; advertising banners on your site.</p>' +
		'<p>&nbsp;</p><p align="center"><input type="button" value="Please Click Here To Get Your Publisher ID" onclick="document.location=\'https://financing.paypal.com/ppfinportal/cart/index?dcp=54d773b600a9fe642a805cb9f8c514d3634acbc7\'"></p>' +
		'<p>&nbsp;</p>' +
		'<p align="center"><input type="button" value="<?php print replace($yyClsWin,"'","\'")?>" onclick="window.close()"></p></div></body></html>';
	newwin=window.open('','AdvOpts','menubar=no,scrollbars=no,width=400,height=550,resizable=yes,status=no,toolbar=no,location=no');
	newwin.document.open();
	newwin.document.write(advtext);
	newwin.document.close();
	return false;
}
/* ]]> */
</script>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
		  <form name="mainform" method="post" action="adminpayprov.php" onsubmit="return validateform()">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="domodify" />
			<input type="hidden" name="id" value="<?php print $alldata['payProvID']?>" />
			<input type="hidden" name="from" value="<?php print getget('from')?>" />
            <table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><strong><?php print $yyPPAdm?></strong><br />&nbsp;</td>
			  </tr>
<?php	if(getget('from')=='wizard2'){ ?>
			  <tr> 
                <td width="100%" colspan="2" align="center">
				  <table width="80%" border="0" cellspacing="2" cellpadding="2" bgcolor="#BFC9E0">
					<tr>
					  <td align="left" valign="top" bgcolor="#FFFFFF">
						<div style="font-size:12px;margin:5px;">
						You can now setup your PayPal account details. If you don't yet have a PayPal account and wish to create one please
						 <a href="<?php print $signuppage?>" target="_blank"><strong><?php print $yyClkHer?></strong></a><br />&nbsp;
						</div>
					  </td>
					</tr>
				  </table><br />&nbsp;
				</td>
			  </tr>
<?php	}elseif($signuppage!=''){ ?>
			  <tr> 
                <td width="100%" colspan="2" align="center"><?php print $yySignUp?> <a href="<?php print $signuppage?>" target="_blank"><strong><?php print $yyClkHer?></strong></a><br />&nbsp;</td>
			  </tr>
<?php	} ?>
			  <tr>
				<td align="right" width="50%"><strong><?php print $yyPPName?> : </strong></td>
				<td align="left"><strong><?php print $alldata['payProvName']?></strong></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyShwAs?> : </strong></td>
				<td align="left"><input type="text" name="showas" value="<?php print $alldata['payProvShow']?>" size="25" /></td>
			  </tr>
<?php	for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 128)==128){ ?>
			  <tr>
				<td align="right"><strong><?php print $yyShwAs . " " . $index?> : </strong></td>
				<td align="left"><input type="text" name="showas<?php print $index?>" value="<?php print $alldata['payProvShow' . $index]?>" size="25" /></td>
			  </tr>
<?php		}
		} ?>
			  <tr>
				<td align="right"><strong><?php print $yyEnable?> : </strong></td>
				<td align="left"><input type="checkbox" name="isenabled" value="1" <?php if($alldata['payProvEnabled']==1) print 'checked="checked"'?> /></td>
			  </tr>
<?php	@include './inc/customppplugin.php';
	if(@$customppplugin!=TRUE||$alldata['payProvID']!=14){
		if($demomodeavailable){ ?>
			  <tr>
				<td align="right"><strong><?php print $yyDemoMo?> : </strong></td>
				<td align="left"><input type="checkbox" name="demomode" value="1" <?php if($alldata['payProvDemo']==1) print 'checked="checked"'?> /></td>
			  </tr>
<?php	}
		$disableapi=FALSE;
		if($alldata['payProvID']==19){
			if(strpos($alldata['payProvData1'],"@AB@")!==FALSE || ($alldata['payProvData2']=="" && $alldata['payProvData3']=="")) $disableapi=TRUE;
			if($disableapi){
				$paypalemail = str_replace("@AB@","",$alldata['payProvData1']);
				if($disableapi && $paypalemail=="") $paypalemail=$emailAddr;
				$alldata['payProvData1']="";
			} ?>
			  <tr>
				<td align="right"><strong>Enable PayPal Credit:</strong></td>
				<td align="left"><input type="checkbox" name="billmelater" value="1" <?php if($wantbillmelater) print 'checked="checked"'?> /> (<a href="#" onclick="return advertisingopts()">Please click here to view advertising options</a>).</td>
			  </tr>
			  <tr>
				<td colspan="2" align="center">
			<table>
			  <tr>
				<td align="right"><input type="radio" name="ppexpressab" value="AB" onclick="disablepaypalapi(true)" <?php if($disableapi==TRUE) print 'checked="checked" '?>/></td>
				<td align="left"><strong><?php print $yyPPEmal?> : </strong><input type="text" name="ppexpressabemail" id="ppexpressabemail" value="<?php if($disableapi==TRUE) print $paypalemail?>" <?php if($disableapi==FALSE) print 'disabled="disabled" ' ?>size="35" /></td>
			  </tr>
			  <tr>
				<td align="right"><input type="radio" name="ppexpressab" value="" onclick="disablepaypalapi(false)" <?php if($disableapi==FALSE) print 'checked="checked" '?>/></td>
				<td align="left"><strong><?php print $yyPPAPIC?></strong><br />
					(<?php print $yyCanLat?>)</td>
			  </tr>
			</table>
				</td>
			  </tr>
<?php	}
		if($alldata['payProvID']==7||$alldata['payProvID']==8||$alldata['payProvID']==22){ // Payflow Pro / Payflow LInk / PayPal Advanced
			if(strpos($alldata['payProvData1'],'&')===FALSE){
				$vs1='';
				$vs2=$alldata['payProvData1'];
				$vs3=$alldata['payProvData2'];
				$vs4='';
			}else{
				$vsdetails = explode('&',$alldata['payProvData1']);
				$vs1=@$vsdetails[0];
				$vs2=@$vsdetails[1];
				$vs3=@$vsdetails[2];
				$vs4=@$vsdetails[3];
			} ?>
			  <tr>
				<td colspan="2" align="center">Please Note: The login information below is the same login you use for PayPal Manager.</td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyPartner?> : </strong></td>
				<td align="left"><input type="text" name="data3" value="<?php print $vs3?>" size="25" /> <input type="button" value="?" title="Your Partner Name is &quot;PayPal&quot;" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyVendor?> : </strong></td>
				<td align="left"><input type="text" name="data2" value="<?php print $vs2?>" size="25" /> <input type="button" value="?" title="This is the login name you created when<?php print "\r\n"?>signing up for PayPal <?php if($alldata['payProvID']==7) print 'PayFlow Pro'; else print 'Payments Advanced'?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyUserID?> : </strong></td>
				<td align="left"><input type="text" name="data1" value="<?php print $vs1?>" size="25" /> <input type="button" value="?" title="Instead of entering a Merchant Login, you can<?php print "\r\n"?>enter a User Login. A User Login is what PayPal<?php print "\r\n"?>recommends because it provides enhanced<?php print "\r\n"?>security and prevents service interruption if you<?php print "\r\n"?>change your Merchant Login password. You<?php print "\r\n"?>can set up a User Login and profile in PayPal<?php print "\r\n"?>Manager." /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyPass?> : </strong></td>
				<td align="left"><input type="text" name="data4" value="<?php print $vs4?>" size="25" /> <input type="button" value="?" title="This is the password you created when signing<?php print "\r\n"?>up for PayPal <?php if($alldata['payProvID']==7) print 'PayFlow Pro'; else print 'Payments Advanced'?> or the<?php print "\r\n"?>password you created for API calls." /></td>
			  </tr>
<?php	}elseif($alldata['payProvID']==10){ ?>
			  <tr>
				<td align="center" colspan="2"><hr width="50%"><strong>Disabled</strong><br />&nbsp;</td>
			  </tr>
<?php	}else{ ?>
			  <tr>
				<td align="right"><strong><span id="data1span"<?php if($disableapi==TRUE) print ' style="color:#A0A0A0;"' ?>><?php print $data1name?> : </span></strong></td>
				<td align="left"><input type="text" name="data1" id="data1" value="<?php print $alldata['payProvData1']?>" <?php if($disableapi==TRUE) print 'disabled="disabled" ' ?>size="35" /></td>
			  </tr>
<?php	}
		if($alldata['payProvID']==5){
			$data2arr = explode('&',trim($alldata['payProvData2']));
			$data2md5=@$data2arr[0];
			$data2cbp=@$data2arr[1];
?>
			  <tr>
				<td align="right"><strong>MD5 Secret (Optional) : </strong></td>
				<td align="left"><input type="text" name="data2" value="<?php print $data2md5?>" size="25" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong>Payment Response password (Optional) : </strong></td>
				<td align="left"><input type="text" name="data3" value="<?php print $data2cbp?>" size="25" /></td>
			  </tr>
<?php	}elseif($alldata['payProvID']==9){ // PayPoint.net
		$data2arr = explode('&',trim($alldata['payProvData2']));
		$data2md5=@$data2arr[0];
		$data2template=urldecode(@$data2arr[1]);
?>			  <tr>
				<td align="right"><strong><?php print $yyMD5H?> : </strong></td>
				<td align="left"><input type="text" name="data2" value="<?php print $data2md5?>" size="25" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong>Payment Template (Optional) : </strong></td>
				<td align="left"><input type="text" name="data2supp" value="<?php print $data2template?>" size="25" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong>Callback URL on SSL Connection : </strong></td>
				<td align="left"><select name="data3" size="1"><option value=""><?php print $yyNo?></option><option value="1" <?php if($alldata['payProvData3']=='1') print 'selected="selected"'?>><?php print $yyYes?></option></select></td>
			  </tr>
<?php	}elseif($alldata['payProvID']==16){ ?>
			  <tr>
				<td align="right"><strong>Shared Secret (Connect 2.0 Only) : </strong></td>
				<td align="left"><input type="text" name="data3" value="<?php print $alldata['payProvData3']?>" size="35" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $data2name?> : </strong></td>
				<td align="left"><select name="data2" size="1"><option value="0"><?php print $yyLPSit?></option><option value="1" <?php if($alldata['payProvData2']=="1") print 'selected="selected"'?>><?php print $yyYesOS?></option></select></td>
			  </tr>
<?php	}elseif($alldata['payProvID']==18 || $alldata['payProvID']==19){ ?>
			  <tr>
				<td align="right"><?php if($alldata['payProvID']==18) print whv('billmelater',$wantbillmelater?"1":"0") ?><strong><span id="data2span"<?php if($disableapi==TRUE) print ' style="color:#A0A0A0;"' ?>><?php print $data2name?> : </span></strong></td>
				<td align="left"><input type="text" name="data2" id="data2" value="<?php print $data2pw?>" size="25" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><span id="data3span"<?php if($disableapi==TRUE) print ' style="color:#A0A0A0;"' ?>><?php if($isthreetoken) print $yySigHas; else print $yyPAtoCE; ?></span> : </strong></td>
				<td align="left"><input type="text" name="data3" id="data3" value="<?php print $data2path?>" size="35" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><span id="apimethodspan"<?php if($disableapi==TRUE) print ' style="color:#A0A0A0;"' ?>>API Method : </span></strong></td>
				<td align="left"><select name="apimethod" id="apimethod" size="1" onchange="document.getElementById('data3span').innerHTML=(document.getElementById('apimethod').selectedIndex==1 ? '<?php print str_replace("'","\'",$yySigHas)?>' : '<?php print str_replace("'","\'",$yyPAtoCE)?>')"><option value="">API Certificate</option><option value="1" <?php if($isthreetoken) print 'selected="selected"';?>>API Signature</option></select></td>
			  </tr>
<?php	}elseif($alldata['payProvID']==21){
			$data2arr=explode('&',$alldata['payProvData2']);
			$data2=$data2arr[0];
			$sellerid=@$data2arr[1];
?>
			  <tr>
				<td align="right"><strong>Seller ID : </strong></td>
				<td align="left"><input type="text" name="data2b" value="<?php print $sellerid?>" size="25" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $data2name?> : </strong></td>
				<td align="left"><input type="text" name="data2" value="<?php print $data2?>" size="25" /></td>
			  </tr>
<?php	}elseif($data2name!=''){ ?>
			  <tr>
				<td align="right"><strong><?php print $data2name?> : </strong></td>
				<td align="left"><input type="text" name="data2" value="<?php print $alldata['payProvData2']?>" size="25" /></td>
			  </tr>
<?php	}
		if($data3name!=''){ ?>
			  <tr>
				<td align="right"><strong><?php print $data3name?> : </strong></td>
				<td align="left"><input type="text" name="data3" value="<?php print $alldata['payProvData3']?>" size="25" /></td>
			  </tr>
<?php	}
		if($alldata['payProvID']==1 || $alldata['payProvID']==3 || $alldata['payProvID']==5 || $alldata['payProvID']==7 || $alldata['payProvID']==8 || $alldata['payProvID']==9 || $alldata['payProvID']==11 || $alldata['payProvID']==12 || $alldata['payProvID']==13 || $alldata['payProvID']==14 || $alldata['payProvID']==16 || $alldata['payProvID']==18 || $alldata['payProvID']==19 || $alldata['payProvID']==21 || $alldata['payProvID']==22 || $alldata['payProvID']==23 || $alldata['payProvID']==24){ // Pay Providers we can set authorization type
			if($alldata['payProvID']==1||$alldata['payProvID']==7||$alldata['payProvID']==8||$alldata['payProvID']==18||$alldata['payProvID']==19||$alldata['payProvID']==22){
				$yyAuthCp='Sale';
				$yyAuthOn='Authorization';
			} ?>
			  <tr>
				<td align="right"><strong><?php print $yyTrnTyp?> : </strong></td>
				<td align="left"><select name="transtype" size="1"><option value="0"><?php print $yyAuthCp?></option><option value="1" <?php if($alldata['payProvMethod']=="1") print 'selected="selected"' ?>><?php print $yyAuthOn?></option></select></td>
			  </tr>
<?php	}
	} ?>
			  <tr>
				<td align="right"><strong><?php print $yyLiLev ?> : </strong></td>
				<td align="left"><select name="payProvLevel" size="1">
				<option value="0"><?php print $yyNoRes?></option>
<?php				for($index=1; $index<= $maxloginlevels; $index++){
						print '<option value="' . $index . '"';
						if($alldata['payProvLevel']==$index) print ' selected="selected"';
						print '>' . $yyLiLev . ' ' . $index . '</option>';
					} ?></select></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyHanChg?> : </strong></td>
				<td align="left"><input type="text" name="pphandlingcharge" size="5" value="<?php print $alldata['ppHandlingCharge']?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyHanChg . ' (' . $yyPercen . ')'?> : </strong></td>
				<td align="left"><input type="text" name="pphandlingpercent" size="5" value="<?php print $alldata['ppHandlingPercent']?>" /></td>
			  </tr>
<?php
	$sSQL = "SELECT pProvHeaders,pProvHeaders2,pProvHeaders3 FROM payprovider WHERE payProvID=" . @$_REQUEST['id'];
	$result=ect_query($sSQL) or ect_error();
	$rs=ect_fetch_assoc($result);
	for($index=0; $index <= $adminlanguages; $index++){
		$languageid = $index+1;
		if($index==0 || ($adminlangsettings & 4096)==4096){
			$theheader = trim($rs[getlangid('pProvHeaders',4096)]);
			$theheader = str_replace('%nl%', '<br />', $theheader);
			$theheader = str_replace('<br>', '<br />', $theheader);
			if(! (@$htmlemails==TRUE && (@$htmleditor=='fckeditor'||@$htmleditor=='ckeditor'))){
				$theheader = str_replace('<br />', "\r\n", $theheader);
			}else
				$theheader = str_replace('<', '&lt;', $theheader); ?>
			  <tr>
				<td align="right"><strong><?php print $yyEmlHdr?> :</strong></td><td align="left"><input type="button" value="&nbsp;Edit&nbsp;" onclick="switchheader('spanheaders<?php print ($index+1)?>')" /></td>
			  </tr>
			  <tr>
				<td align="center" colspan="2"><span id="spanheaders<?php print ($index+1)?>" style="display:none"><textarea name="pprovheaders<?php print ($index+1)?>" cols="70" rows="6"><?php print $theheader?></textarea></span></td>
			  </tr>
<?php	}
	}
	ect_free_result($result);
	$sSQL = "SELECT pProvDropShipHeaders,pProvDropShipHeaders2,pProvDropShipHeaders3 FROM payprovider WHERE payProvID=" . @$_REQUEST['id'];
	$result=ect_query($sSQL) or ect_error();
	$rs=ect_fetch_assoc($result);
	for($index=0; $index <= $adminlanguages; $index++){
		$languageid = $index+1;
		if($index==0 || ($adminlangsettings & 4096)==4096){
			$theheader = trim($rs[getlangid('pProvDropShipHeaders',4096)]);
			$theheader = str_replace('%nl%', '<br />', $theheader);
			$theheader = str_replace('<br>', '<br />', $theheader);
			if(! (@$htmlemails && (@$htmleditor=='fckeditor'||@$htmleditor=='ckeditor'))){
				$theheader = str_replace('<br />', "\r\n", $theheader);
			}else
				$theheader = str_replace('<', '&lt;', $theheader);  ?>
			  <tr>
				<td align="right"><strong><?php print $yyDrSppr . ' ' . $yyEmlHdr?> :</strong></td><td align="left"><input type="button" value="&nbsp;Edit&nbsp;" onclick="switchheader('spandropshipheaders<?php print ($index+1)?>')" /></td>
			  </tr>
			  <tr>
				<td align="center" colspan="2"><span id="spandropshipheaders<?php print ($index+1)?>" style="display:none"><textarea name="pprovdropshipheaders<?php print ($index+1)?>" cols="70" rows="6"><?php print $theheader?></textarea></span></td>
			  </tr>
<?php		}
	}
	ect_free_result($result); ?>

			  <tr>
				<td colspan="2">&nbsp;</td>
			  </tr>
<?php
	if(getget('from')=='wizard' && $alldata['payProvID']!=1 && $alldata['payProvID']!=18 && $alldata['payProvID']!=19){ ?>
			  <tr>
				<td colspan="2" align="center">
				  <table width="80%" border="0" cellspacing="2" cellpadding="2" bgcolor="#BFC9E0">
					<tr>
					  <td align="left" valign="top" bgcolor="#FFFFFF">
						<img src="adminimages/paypalexample.gif" border="0" style="float:right;margin:5px;" />
					    <div style="font-size:14px;font-weight:bold;margin:5px;"><input type="checkbox" name="offerpaypal" value="ON" checked="checked" />&nbsp;Offer the option to pay with PayPal</div>
						<div style="font-size:12px;color:#3263B3;margin:5px;">According to Jupiter Research, 23% of online shoppers consider PayPal one of their favorite 
						ways to pay online.*<br />
						Accepting PayPal in addition to credit cards is proven to increase your sales.**</div>
						<div style="font-size:12px;margin:5px;">*<span style="font-style:italic;"> Payment Preferences Online</span>, Jupiter Research, September 2000<br />
						** Applies to online businesses doing up to $10 million/year in online sales. Based on a Q4 2007 survey of PayPal shoppers conducted by Northstar Research, and PayPal internal data on Express Checkout transactions.</div>
					  </td>
					</tr>
				  </table><br />&nbsp;
				</td>
			  </tr>
<?php
	} ?>
			  <tr>
				<td align="center" colspan="2"><input type="submit" value="<?php print $yySubmit?>" /> <input type="reset" value="<?php print $yyReset?>" /></td>
			  </tr>
			  <tr>
				<td colspan="2">&nbsp;</td>
			  </tr>
			</table>
		  </form>
<?php
	if(@$htmleditor=='fckeditor'||@$htmleditor=='ckeditor'){
		if(@$pathtossl!='' && (@$_SERVER['HTTPS']=='on' || @$_SERVER['SERVER_PORT']=='443')){
			if(substr($pathtossl,-1) != "/") $storeurl = $pathtossl . "/"; else $storeurl = $pathtossl;
		}
		$pathtovsadmin=dirname(@$_SERVER['PHP_SELF']);
		print '<script type="text/javascript">function loadeditors(){';
		if($htmleditor=='ckeditor'){
			$streditor = "var pprovheaders=CKEDITOR.replace('pprovheaders',{extraPlugins : 'stylesheetparser,autogrow',autoGrow_maxHeight : 800,removePlugins : 'resize', toolbarStartupExpanded : false, toolbar : 'Basic', filebrowserBrowseUrl :'ckeditor/filemanager/browser/default/browser.html?Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserImageBrowseUrl : 'ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserFlashBrowseUrl :'ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=File',filebrowserImageUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=Image',filebrowserFlashUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=Flash'});\r\n";
			$streditor.="pprovheaders.on('instanceReady',function(event){var myToolbar = 'Basic';event.editor.on( 'beforeMaximize', function(){if(event.editor.getCommand('maximize').state==CKEDITOR.TRISTATE_ON && myToolbar != 'Basic'){pprovheaders.setToolbar('Basic');myToolbar = 'Basic';pprovheaders.execCommand('toolbarCollapse');}else if(event.editor.getCommand('maximize').state==CKEDITOR.TRISTATE_OFF && myToolbar != 'Full'){pprovheaders.setToolbar('Full');myToolbar = 'Full';pprovheaders.execCommand('toolbarCollapse');}});event.editor.on('contentDom', function(e){event.editor.document.on('blur', function(){if(!pprovheaders.isToolbarCollapsed){pprovheaders.execCommand('toolbarCollapse');pprovheaders.isToolbarCollapsed=true;}});event.editor.document.on('focus',function(){if(pprovheaders.isToolbarCollapsed){pprovheaders.execCommand('toolbarCollapse');pprovheaders.isToolbarCollapsed=false;}});});pprovheaders.fire('contentDom');pprovheaders.isToolbarCollapsed=true;});\r\n";
		}else
			$streditor = "var oFCKeditor = new FCKeditor('pprovheaders');oFCKeditor.BasePath=sBasePath;oFCKeditor.Config.BaseHref='".$storeurl."';oFCKeditor.ToolbarSet = 'Basic';oFCKeditor.ReplaceTextarea();\r\n";
		for($index=1; $index<=$adminlanguages+1; $index++){
			if($index==1 || ($adminlangsettings & 4096)==4096){
				print str_replace('pprovheaders', 'pprovheaders' . $index, $streditor);
				print str_replace('pprovheaders', 'pprovdropshipheaders' . $index, $streditor);
			}
		}
		print '}window.onload=function(){loadeditors();}</script>';
	} ?>
		  </td>
		</tr>
      </table>
<?php
}elseif(getpost('act')=='changepos'){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%" align="center">
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p><strong><?php print $yyUpdat?> . . . . . . . </strong></p>
			<p>&nbsp;</p>
			<p><?php print $yyNoFor?> <a href="adminpayprov.php"><?php print $yyClkHer?></a>.</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
		  </td>
		</tr>
      </table>
<?php
	}elseif(getget('act')=='cccards'){ ?>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td align="center">
		  <table width="80%" height="100%" border="0" cellspacing="0" cellpadding="2">
			<tr>
			  <td align="left"><p style="font-size:18px;font-weight:bold;">Choose a solution to accept credit card payments</p>
					<p>&nbsp;</p>
			  </td>
			</tr>
		  </table>
		  <table width="80%" border="0" cellspacing="2" cellpadding="2" bgcolor="#BFC9E0">
			<tr>
			  <td width="50%" align="left" valign="top" bgcolor="#FFFFFF">
			  <p style="font-size:16px;font-weight:bold;">&nbsp;All-in-one Solution</p>
			  <div onclick="selectopt('allin1')" style="border:1px;font-size:12px;font-weight:bold;background-color:#E6E9F5;padding:4px;min-height:50px;border-style:solid;border-width:1px;">
			  <input type="radio" name="solntype" value="ALL1" id="allin1" /> 
			  I want an all-in-one payment solution that includes a payment gateway and an internet merchant account.</div>
			  &nbsp;
			  <div id="allin1div" style="border:1px;padding:4px;background-color:#E6E6E6;border-style:solid;border-width:1px;">
			  <div id="allin1div2" style="padding:8px;font-size:12px;font-weight:bold;color:#A0A0A0;">Choose your all-in-one solution</div>
			  <p>
			    <ul>
				  <li><a href="adminpayprov.php?act=modify&from=wizard&id=18" class="allin1">PayPal Payments Pro</a><br />&nbsp;</li>
				  <li><a href="adminpayprov.php?act=modify&from=wizard&id=1" class="allin1">PayPal Payments Standard</a><br />&nbsp;</li>
				  <li><a href="adminpayprov.php?act=modify&from=wizard&id=2" class="allin1">2Checkout</a><br />&nbsp;</li>
				  <li><a href="adminpayprov.php?act=modify&from=wizard&id=21" class="allin1">Amazon Simple Pay</a><br />&nbsp;</li>
				  <li><a href="adminpayprov.php?act=modify&from=wizard&id=20" class="allin1">Google Checkout</a><br />&nbsp;</li>
				  <li><a href="adminpayprov.php?act=modify&from=wizard&id=15" class="allin1">Netbanx</a><br />&nbsp;</li>
				  <li><a href="adminpayprov.php?act=modify&from=wizard&id=6" class="allin1">Nochex</a><br />&nbsp;</li>
				  <li><a href="adminpayprov.php?act=modify&from=wizard&id=5" class="allin1">RBS WorldPay</a><br />&nbsp;</li>
				</ul>
			  </p>
			  </div>
			  </td>
			  
			  <td align="left" valign="top" bgcolor="#FFFFFF">
			  <p style="font-size:16px;font-weight:bold;">&nbsp;Solution for existing merchant account</p>
			  <div onclick="selectopt('exis')" style="border:1px;font-size:12px;font-weight:bold;background-color:#E6E9F5;padding:4px;min-height:50px;border-style:solid;border-width:1px;">
			  <input type="radio" name="solntype" value="EXIS" id="exis" /> 
			  I prefer a payment gateway that works with my existing merchant account.</div>
			  &nbsp;
			  <div id="exisdiv" style="border:1px;padding:4px;background-color:#E6E6E6;border-style:solid;border-width:1px;">
			  <div id="exisdiv2" style="padding:8px;font-size:12px;font-weight:bold;color:#A0A0A0;">Choose your Gateway</div>
			  <p>
			    <ul>
				  <li><a href="adminpayprov.php?act=modify&from=wizard&id=7" class="exis">PayPal Payflow Pro</a><br />&nbsp;</li>
				  <li><a href="adminpayprov.php?act=modify&from=wizard&id=8" class="exis">PayPal Payflow Link</a><br />&nbsp;</li>
				  <li><a href="adminpayprov.php?act=modify&from=wizard&id=13" class="exis">Authorize.net (AIM)</a><br />&nbsp;</li>
				  <li><a href="adminpayprov.php?act=modify&from=wizard&id=3" class="exis">Authorize.net (SIM)</a><br />&nbsp;</li>
				  <li><a href="adminpayprov.php?act=modify&from=wizard&id=16" class="exis">Linkpoint</a><br />&nbsp;</li>
				  <li><a href="adminpayprov.php?act=modify&from=wizard&id=9" class="exis">PayPoint</a><br />&nbsp;</li>
				  <li><a href="adminpayprov.php?act=modify&from=wizard&id=11" class="exis">PSiGate</a><br />&nbsp;</li>
				  <li><a href="adminpayprov.php?act=modify&from=wizard&id=12" class="exis">PSiGate (SSL)</a><br />&nbsp;</li>
				</ul>
			  </p>
			  </div>
			  </td>
			</tr>
		  </table>
		  <table width="80%" border="0" cellspacing="0" cellpadding="2">
			<tr>
			  <td align="left">
<?php		if(FALSE){ ?>
				<p style="font-size:11px;font-weight:bold;">Don't see what you are looking for?</p>
				<p style="font-size:11px;font-weight:bold;"><a href="adminpayprov.php?act=list">See full list of payment processors</a></p>
<?php		} ?>
<p>&nbsp;</p>
			  </td>
			</tr>
		  </table>
		  <br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;
		</td>
	  </tr>
	</table>
<script type="text/javascript">
/* <![CDATA[ */
function disableAnchor(obj, disable){
  if(disable){
    var href = obj.href;
    if(href && href!="" && href!=null){
       obj.href_bak=href;
    }
	obj.vrdibled=true;
    obj.removeAttribute('href');
    obj.style.color="gray";
  }else{
	obj.vrdibled=false;
    obj.setAttribute('href',obj.href_bak);
    obj.style.color="";
  }
}
function selectopt(optid){
	document.getElementById(optid).checked=true;
	var thediv = document.getElementById(optid+'div');
	document.getElementById(optid+'div2').style.color='#000000';
	thediv.style.backgroundColor='#FFFFFF';
	var opts = thediv.getElementsByTagName('a');
	i=0;
	while(opt=opts[i++]){
		disableAnchor(opt,false);
	}
	if(optid=='allin1') otheropt='exis'; else otheropt='allin1';
	var thediv = document.getElementById(otheropt+'div');
	document.getElementById(otheropt+'div2').style.color='#A0A0A0';
	thediv.style.backgroundColor='#E6E6E6';
	var opts = thediv.getElementsByTagName('a');
	i=0;
	while(opt=opts[i++]){
		disableAnchor(opt,true);
	}
}
var thediv = document.getElementById('allin1div');
var opts = thediv.getElementsByTagName('a');
i=0;
while(opt=opts[i++]){
	disableAnchor(opt,true);
}
var thediv = document.getElementById('exisdiv');
var opts = thediv.getElementsByTagName('a');
i=0;
while(opt=opts[i++]){
	disableAnchor(opt,true);
}
/* ]]> */
</script>
<?php 
}elseif(getget('act')=='ccpaypal'){ ?>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	  <tr>
		<td align="center">
		  <table width="80%" height="100%" border="0" cellspacing="0" cellpadding="2">
			<tr>
			  <td align="left"><p style="font-size:18px;font-weight:bold;">Choose a solution to accept credit cards and PayPal</p>
					<p>&nbsp;</p>
			  </td>
			</tr>
		  </table>
		  <table width="80%" border="0" cellspacing="2" cellpadding="2" bgcolor="#BFC9E0">
			<tr>
			  <td align="left" valign="top" bgcolor="#FFFFFF" width="50%">
			  <p style="font-size:16px;font-weight:bold;">&nbsp;PayPal Payments Standard</p>
			  <div style="font-size:12px;font-weight:bold;background-color:#E6E9F5;padding:4px;">Easy to get started, no monthly fees.<br />&nbsp;<br />
			  <p align="right" style="margin:0px;"><a href="" onclick="newwin=window.open('http://www.paypal.com/en_US/m/demo/demo_wps/demo_WPS.html','PayPalDemo','menubar=no,scrollbars=yes,width=598,height=380,directories=no,location=no,resizable=yes,status=no,toolbar=no');return false;">See demo</a></p>
			  </div>
			  <p>
			    <ul>
				  <li>Accept Visa, MasterCard, American Express, Discover, PayPal and more at one low rate.<br />&nbsp;</li>
				  <li>Buyers enter credit card information on secure PayPal pages and immediately return to your site. Your buyers do NOT need a PayPal account.<br />&nbsp;</li>
				  <li>Start selling as soon as you sign up.<br />&nbsp;</li>
				</ul>
			  </p>
			  <p style="font-size:12px;font-weight:bold;">Pricing</p>
			  <p>
			    <ul>
				  <li>No monthly fees.<br />&nbsp;</li>
				  <li>No setup or cancellation fees.<br />&nbsp;</li>
				  <li>Transaction fees: 1.9% - 2.9% + $0.30 USD<br />
				  (Based on sales volume)<br />&nbsp;</li>
				</ul>
			  </p>
			  <div align="center"><input type="button" value="Select" onclick="document.location='adminpayprov.php?act=modify&from=wizard&id=1'" /></div>
			  </td>
			  
			  <td align="left" valign="top" bgcolor="#FFFFFF">
			  <p style="font-size:16px;font-weight:bold;">&nbsp;PayPal Payments Pro</p>
			  <div style="font-size:12px;font-weight:bold;background-color:#E6E9F5;padding:4px;">Advanced e-commerce solution for established businesses.
			  <p align="right" style="margin:0px;"><a href="" onclick="newwin=window.open('http://www.paypal.com/en_US/m/demo/wppro/paypal_demo_load_560x355.html','PayPalDemo','menubar=no,scrollbars=yes,width=578,height=372,directories=no,location=no,resizable=yes,status=no,toolbar=no');return false;">See demo</a></p>
			  </div>
			  <p>
			    <ul>
				  <li>Accept Visa, MasterCard, American Express, Discover, PayPal and more at one low rate.<br />&nbsp;</li>
				  <li>Buyers enter credit card info directly on your site, and do NOT need a PayPal account.<br />&nbsp;</li>
				  <li>Business credit application required to start selling. Decision usually comes within 24 hours.<br />&nbsp;</li>
				  <li>Includes Virtual Terminal - accept payments for orders taken via phone, fax and mail.<br />&nbsp;</li>
				</ul>
			  </p>
			  <p style="font-size:12px;font-weight:bold;">Pricing</p>
			  <p>
			    <ul>
				  <li>$30 per month.<br />&nbsp;</li>
				  <li>No setup or cancellation fees.<br />&nbsp;</li>
				  <li>Transaction fees: 2.2% - 2.9% + $0.30 USD<br />
				  (Based on sales volume)<br />&nbsp;</li>
				</ul>
			  </p>
			  <p align="center"><input type="button" value="Select" onclick="document.location='adminpayprov.php?act=modify&from=wizard&id=18'" /></p>
			  </td>
			</tr>
		  </table>
		  <table width="80%" border="0" cellspacing="0" cellpadding="2">
			<tr>
			  <td align="left">
				<p style="font-size:11px;font-weight:bold;">Don't see what you are looking for?</p>
				<p style="font-size:11px;font-weight:bold;"><a href="adminpayprov.php?act=list">See full list of payment processors</a></p>
			  </td>
			</tr>
		  </table>
		  <br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;
		</td>
	  </tr>
	</table>
<?php
}else{ ?>
	<form name="mainform" method="post" action="adminpayprov.php">
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%" align="center">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="modify" />
			<input type="hidden" name="id" value="1" />
			<input type="hidden" name="selectedq" value="1" />
			<input type="hidden" name="newval" value="1" />
            <table width="700" border="0" cellspacing="0" cellpadding="2">
<?php
	if(getget('act')!='list'){ ?>
			  <tr>
                <td width="100%" colspan="4" align="left">
				<div>&nbsp;</div>
				<div style="font-size:18px;font-weight:bold;">Set-up credit card processing</div>
				<div>&nbsp;</div>
				<p>
				  <ul>
					<li><span style="font-size:13px;font-weight:bold;"><a href="adminpayprov.php?act=ccpaypal">Accept Credit Cards and PayPal</a></span><br /><br />
					<a href="adminpayprov.php?act=ccpaypal">
					<img border="0" src="adminimages/logo_ccVisa.gif" alt="Visa" />
					<img border="0" src="adminimages/logo_ccMC.gif" alt="Mastercard" />
					<img border="0" src="adminimages/logo_ccAmex.gif" alt="American Express" />
					<img border="0" src="adminimages/logo_ccDiscover.gif" alt="Discover" />
					<img border="0" src="adminimages/logo_ccEcheck.gif" alt="eCheck" />
					<img border="0" src="adminimages/PayPal_mark_37x23.gif" alt="PayPal" />
					</a><br />&nbsp;
					</li>
					<li><span style="font-size:13px;font-weight:bold;"><a href="adminpayprov.php?act=cccards">Accept Credit Cards</a></span><br /><br />
					<a href="adminpayprov.php?act=cccards">
					<img border="0" src="adminimages/logo_ccVisa.gif" alt="Visa" />
					<img border="0" src="adminimages/logo_ccMC.gif" alt="Mastercard" />
					<img border="0" src="adminimages/logo_ccAmex.gif" alt="American Express" />
					<img border="0" src="adminimages/logo_ccDiscover.gif" alt="Discover" />
					</a><br />&nbsp;
					</li>
				  </ul>
				</p>
				<p>&nbsp;</p>
				<p><span style="font-size:12px;">Note: You will be able to add additional payment options later in this set-up process</span></p>
				<p>&nbsp;</p>
				<p><span style="font-size:11px;font-weight:bold;"><a href="adminpayprov.php?act=list">See full list of payment processors</a></span></p>
				</td>
			  </tr>
<?php
	}else{ ?>
			  <tr> 
                <td width="100%" colspan="4" align="center"><strong><?php print $yyPPAdm?></strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td width="8%" align="center"><strong>ID</strong></td>
				<td width="8%" align="center"><strong><?php print $yyOrder?></strong></td>
				<td width="49%" align="left"><strong><?php print $yyPPName?></strong></td>
				<td width="25%" align="center"><strong><?php print $yyConf?></strong></td>
				<td width="10%" align="center"><strong><?php print $yyHlpFil?></strong></td>
			  </tr>
<?php		function writeposition($currpos,$maxpos){
				$reqtext="<select name='newpos" . $currpos . "' size='1' onchange='javascript:validate_index(".$currpos.");'>";
				for($i = 1; $i <= $maxpos; $i++){
					$reqtext.="<option value='".$i."'";
					if($currpos==$i) $reqtext.=' selected="selected"';
					$reqtext.=">" . $i . "</option>";
				}
				return($reqtext . "</select>");
			};
			$sSQL = "SELECT COUNT(payProvID) AS enabledProv FROM payprovider WHERE payProvEnabled=1";
			$result=ect_query($sSQL) or ect_error();
			$rs=ect_fetch_assoc($result);
			$enabledProv = $rs["enabledProv"];
			ect_free_result($result);
			$showenabled=TRUE;
			for($index=0; $index<2; $index++){
				$sSQL = "SELECT payProvID,payProvName,payProvShow,payProvDemo,payProvEnabled,payProvData1,payProvData2 FROM payprovider WHERE payProvAvailable=1";
				if($showenabled)
					$sSQL.=" AND payProvEnabled=1 ORDER BY payProvOrder";
				else
					$sSQL.=" AND payProvEnabled=0 ORDER BY payProvName";
				$result=ect_query($sSQL) or ect_error();
				$rowcounter=1;
				while($alldata=ect_fetch_assoc($result)){
					$helplink='';
					if($alldata['payProvID']==1){ $helplink='http://www.ecommercetemplates.com/phphelp/ecommplus/paypal.asp'; $alldata['payProvName']='PayPal Payments Standard'; }
					if($alldata['payProvID']==2){ $helplink='http://www.ecommercetemplates.com/phphelp/ecommplus/2checkout.asp'; }
					if($alldata['payProvID']==3){ $helplink='http://www.ecommercetemplates.com/phphelp/ecommplus/authorizenet.asp'; }
					if($alldata['payProvID']==4){ $helplink=''; }
					if($alldata['payProvID']==5){ $helplink='http://www.ecommercetemplates.com/phphelp/ecommplus/worldpay.asp'; }
					if($alldata['payProvID']==6){ $helplink='http://www.ecommercetemplates.com/phphelp/ecommplus/nochex.asp'; }
					if($alldata['payProvID']==7){ $helplink='http://www.ecommercetemplates.com/phphelp/ecommplus/paypal-payflow-pro.asp'; $alldata['payProvName']='PayPal Payflow Pro'; }
					if($alldata['payProvID']==8){ $helplink='http://www.ecommercetemplates.com/phphelp/ecommplus/paypal-payflow-link.asp'; $alldata['payProvName']='PayPal Payflow Link'; }
					if($alldata['payProvID']==9){ $helplink='http://www.ecommercetemplates.com/phphelp/ecommplus/paypoint.asp'; }
					if($alldata['payProvID']==10){ $helplink=''; }
					if($alldata['payProvID']==11){ $helplink='http://www.ecommercetemplates.com/phphelp/ecommplus/psigate.asp'; }
					if($alldata['payProvID']==12){ $helplink='http://www.ecommercetemplates.com/phphelp/ecommplus/psigate.asp'; }
					if($alldata['payProvID']==13){ $helplink='http://www.ecommercetemplates.com/phphelp/ecommplus/authorizenet.asp'; }
					if($alldata['payProvID']==14){ $helplink=''; }
					if($alldata['payProvID']==15){ $helplink='http://www.ecommercetemplates.com/phphelp/ecommplus/netbanx.asp'; }
					if($alldata['payProvID']==16){ $helplink='http://www.ecommercetemplates.com/phphelp/ecommplus/linkpoint.asp'; }
					if($alldata['payProvID']==17){ $helplink=''; }
					if($alldata['payProvID']==18){ $helplink='http://www.ecommercetemplates.com/phphelp/ecommplus/paypal-pro.asp'; $alldata['payProvName']='PayPal Direct Payments'; }
					if($alldata['payProvID']==19){ $helplink='http://www.ecommercetemplates.com/help/ecommplus/paypal-express-checkout.asp'; $alldata['payProvName']='PayPal Express Payments'; }
					if($alldata['payProvID']==20){ $helplink=''; }
					if($alldata['payProvID']==21){ $helplink='http://www.ecommercetemplates.com/phphelp/ecommplus/amazon-payments.asp'; }
					if($alldata['payProvID']==22){ $helplink='http://www.ecommercetemplates.com/help/ecommplus/paypal-advanced.asp'; $alldata['payProvName']='PayPal Payments Advanced'; }
					if($alldata['payProvID']==23){ $helplink='http://www.ecommercetemplates.com/help/ecommplus/stripe.asp'; }
					if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark'; ?>
				  <tr class="<?php print $bgcolor?>">
					<td align="center"><?php print $alldata['payProvID'] ?></td>
					<td align="center"><?php if($alldata['payProvEnabled']==1) print writeposition($rowcounter,$enabledProv); else print "-"; ?></td>
					<td align="left">&nbsp;&nbsp;<?php if($alldata['payProvDemo']==1) print '<span style="color:#FF0000">'; ?><?php if($alldata['payProvEnabled']==1) print "<strong>"; ?><?php print $alldata['payProvName'];?><?php if($alldata['payProvEnabled']==1) print "</strong>"; ?><?php if($alldata['payProvDemo']==1) print "</span>"; ?></td>
					<td align="center"><input type="button" name="modify" value="<?php print $yyModify?>" onclick="modrec('<?php print $alldata['payProvID'];?>')" /></td>
					<td align="center"><?php if($helplink=='') print '&nbsp;'; else print '<a href="' . $helplink . '" target="_blank"><img src="adminimages/icon-help.png" alt="'.$yyHlpFil.'" /></a>'?></td>
				  </tr>
<?php				$rowcounter++;
				}
				ect_free_result($result);
				$showenabled=FALSE;
			} ?>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br /><?php print $yyPPEx1?><br />
				  <?php print $yyPPEx2?>&nbsp;</td>
			  </tr>
<?php
	} ?>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </td>
        </tr>
      </table>
	</form>
<?php
}
?>
