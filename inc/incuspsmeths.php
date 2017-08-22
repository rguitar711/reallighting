<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
$success=TRUE;
$alreadygotadmin=getadminsettings();
$method=trim(@$_REQUEST['method']);
if($method!='') $shipType=(int)$method;
$shipmet='USPS';
if($shipType==4) $shipmet='UPS';
if($shipType==6) $shipmet=$yyCanPos;
if($shipType==7) $shipmet='FedEx';
if($shipType==8) $shipmet='FedEx SmartPost';
if($shipType==9) $shipmet='DHL';
if($shipType==10) $shipmet='Australia Post';
function checkisdocument($st,$serv){
	$cid='';
	if($st==9){
		if($serv=='2' || $serv=='5' || $serv=='6' || $serv=='7' || $serv=='9' || $serv=='B' || $serv=='C' || $serv=='D' || $serv=='G' || $serv=='I' || $serv=='K' || $serv=='L' || $serv=='N' || $serv=='R' || $serv=='S' || $serv=='T' || $serv=='U' || $serv=='W' || $serv=='X')
			$cid=' <strong>(document)</strong>';
	}
	return($cid);
}
if(getpost('posted')=='1'){
	if(getpost('doadmin')!=''){
		if($shipType==3){
			$sSQL="UPDATE admin SET adminUSPSUser='".escape_string(getpost('adminUSPSUser'))."' WHERE adminID=1";
			ect_query($sSQL) or ect_error();
		}elseif($shipType==4){
			$sSQL="UPDATE admin SET adminUPSNegotiated=".getpost('UPSNegotiated')." WHERE adminID=1";
			ect_query($sSQL) or ect_error();
		}elseif($shipType==6){
			$sSQL="UPDATE admin SET adminCanPostUser='".escape_string(getpost('adminCanPostUser'))."' WHERE adminID=1";
			ect_query($sSQL) or ect_error();
		}elseif($shipType==8){
			$sSQL="UPDATE admin SET smartPostHub='".escape_string(getpost('smartPostHub'))."' WHERE adminID=1";
			ect_query($sSQL) or ect_error();
		}elseif($shipType==9){
			$sSQL="UPDATE admin SET DHLSiteID='" . escape_string(getpost('DHLSiteID')) . "',DHLSitePW='" . escape_string(getpost('DHLSitePW')) . "',DHLAccountNo='" . escape_string(getpost('DHLAccountNo')) . "' WHERE adminID=1";
			ect_query($sSQL) or ect_error();
		}elseif($shipType==10){
			$sSQL="UPDATE admin SET AusPostAPI='" . escape_string(getpost('AusPostAPI')) . "' WHERE adminID=1";
			ect_query($sSQL) or ect_error();
		}
	}else{
		if($shipType==3||$shipType==10){
			for($index=1+($shipType==10?600:0);$index<=50+($shipType==10?600:0);$index++){
				if(trim(@$_POST['methodshow' . $index])!=''){
					$sSQL="UPDATE uspsmethods SET uspsShowAs='" . escape_string(getpost('methodshow' . $index)) . "',";
					if(@$_POST['methodfsa' . $index]=='ON')
						$sSQL.='uspsFSA=1,';
					else
						$sSQL.='uspsFSA=0,';
					if(@$_POST['methoduse' . $index]=='ON')
						$sSQL.='uspsUseMethod=1 WHERE uspsID=' . $index;
					else
						$sSQL.='uspsUseMethod=0 WHERE uspsID=' . $index;
					ect_query($sSQL) or ect_error();
				}
			}
		}elseif($shipType==4 || $shipType==6 || $shipType==7 || $shipType==8 || $shipType==9){
			$indexadd=0;
			if($shipType==6) $indexadd=100; elseif($shipType==7) $indexadd=200; elseif($shipType==8) $indexadd=300; elseif($shipType==9) $indexadd=400;
			for($index=100+$indexadd;$index<=155+$indexadd;$index++){
				if(trim(@$_POST['methodshow' . $index])!=''){
					$sSQL='UPDATE uspsmethods SET ';
					if(@$_POST['methodfsa' . $index]=='ON')
						$sSQL.='uspsFSA=1,';
					else
						$sSQL.="uspsFSA=0,";
					if(@$_POST["methoduse" . $index]=="ON")
						$sSQL.="uspsUseMethod=1 WHERE uspsID=" . $index;
					else
						$sSQL.="uspsUseMethod=0 WHERE uspsID=" . $index;
					ect_query($sSQL) or ect_error();
				}
			}
		}
	}
	print '<meta http-equiv="refresh" content="2; url=adminuspsmeths.php">';
}
$cpurl="https://" . (@$canadaposttestmode?"ct.":"") . "soa-gw.canadapost.ca/ot/soap/merchant/registration";
if(getget("token-id")!='' && getget("registration-status")!=''){
	print "<h2>Canada Post Registration</h2>";
	if(getget("registration-status")=="SUCCESS"){
		$sXML='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:reg="http://www.canadapost.ca/ws/soap/merchant/registration">' .
		'<soapenv:Header><wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsse:UsernameToken><wsse:Username>' . (@$canadaposttestmode?"3e726c38d754ea80":"2de7ca2bc2f0a552") . "</wsse:Username><wsse:Password>" . (@$canadaposttestmode?"a47e23e9d34ee61fda2199":"1d3ac063ca9baccdc2ca69") . '</wsse:Password></wsse:UsernameToken></wsse:Security></soapenv:Header>' .
		'<soapenv:Body><reg:get-merchant-registration-info-request><locale>EN</locale><token-id>' . getget("token-id") . '</token-id></reg:get-merchant-registration-info-request></soapenv:Body></soapenv:Envelope>';
		$success=callcurlfunction($cpurl, $sXML, $res, '', $errormsg, FALSE);
		$xmlDoc=new vrXMLDoc($res);
		$nodeList=$xmlDoc->nodeList->childNodes[0];
		$customernumber=$nodeList->getValueByTagName('customer-number');
		$merchantusername=$nodeList->getValueByTagName('merchant-username');
		$merchantpassword=$nodeList->getValueByTagName('merchant-password');
		$sSQL="UPDATE admin SET adminCanPostUser='".escape_string($customernumber)."',adminCanPostLogin='".escape_string($merchantusername)."',adminCanPostPass='".escape_string($merchantpassword)."' WHERE adminID=1";
		ect_query($sSQL) or ect_error();
		print '<div style="text-align:center;margin:15px">The Canada Post Registration system has completed successfully.</div>';
		print '<div style="text-align:center;margin:15px"><a href="admin.php"><strong>'.$yyAdmHom.'</strong></a></div>';
	}else
		print '<div style="text-align:center;font-weight:bold;margin:15px">Sorry - An error occurred!</div>';
}elseif(getget("canadapost")=="register"){
	print "<h2>Canada Post Registration</h2>";
	$thetokenid='';
	$sXML='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:reg="http://www.canadapost.ca/ws/soap/merchant/registration">' .
	'<soapenv:Header><wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsse:UsernameToken><wsse:Username>' . (@$canadaposttestmode?"3e726c38d754ea80":"2de7ca2bc2f0a552") . "</wsse:Username><wsse:Password>" . (@$canadaposttestmode?"a47e23e9d34ee61fda2199":"1d3ac063ca9baccdc2ca69") . '</wsse:Password></wsse:UsernameToken></wsse:Security></soapenv:Header>' .
	'<soapenv:Body><reg:get-merchant-registration-token-request></reg:get-merchant-registration-token-request></soapenv:Body></soapenv:Envelope>';
	$success=callcurlfunction($cpurl, $sXML, $res, '', $errormsg, FALSE);
	$xmlDoc=new vrXMLDoc($res);
	$nodeList=$xmlDoc->nodeList->childNodes[0];
	$thetokenid=$nodeList->getValueByTagName('token-id');
	if($thetokenid==''){
		$faultcode=$nodeList->getValueByTagName('faultcode');
		$faultstring=$nodeList->getValueByTagName('faultstring');
		print '<div style="text-align:center;margin:15px">There was an error connecting with the Canada Post Registration Server. Please ask your host to make sure the following URL is not blocked by the server firewall...</div>';
		print '<div style="text-align:center;margin:15px">' . $cpurl . "</div>";
		print '<div style="text-align:center;margin:15px">If you have done this and still get an error, please quote the following when contacting <a href="http://www.ecommercetemplates.com/support" target="_blank">support at Ecommerce Templates</a>.</div>';
		print '<div style="text-align:center;margin:15px">' . $faultcode . ' : ' . $faultstring . '</div>';
	}else{
?>
<form method="post" id="canposform" action="https://www.canadapost.ca/cpotools/apps/drc/merchant">
<div style="text-align:center;margin:15px">In a few seconds you will be taken to the Canada Post website to complete the registration process...</div>
<div style="text-align:center;margin:15px">If that does not happen automatically, please <a href="javascript:document.getElementById('canposform').submit()">Click Here</a></div>
<input type="hidden" name="return-url" value="<?php print (@$_SERVER["HTTPS"]=="on"?"https://":"http://").@$_SERVER["HTTP_HOST"].@$_SERVER["PHP_SELF"]?>" />
<input type="hidden" name="token-id" value="<?php print $thetokenid?>" />
<input type="hidden" name="platform-id" value="0008107483" />
</form>
<script type="text/javascript">
setTimeout('document.getElementById("canposform").submit()', 4000);
</script>
<?php
	}
}elseif(getget('royalmail')=='setup'){ ?>
<p>&nbsp;</p>
<p align="center">Proceeding will replace all your weight based shipping tables with Royal Mail 2013 rates</p>
<p align="center">Product weights are assumed to be in metric (kg)</p>
<p align="center">Please note, clicking below will wipe all your current postal zone inforamtion and cannot be undone.</p>
<p>&nbsp;</p>
<form method="post" action="adminuspsmeths.php">
<input type="hidden" name="royalmail" value="dosetup" />
<p align="center">
<table border="0" width="100%">
<tr><td align="right" width="25%"><input type="checkbox" name="addrecorded" value="ON" /></td><td align="left">Add Recorded Signed For option to First, Second and Standard Parcel rates? (&pound;1.10 extra)</td></tr>
<tr><td align="right"><input type="checkbox" name="addinternationalsigned" value="ON" /></td><td align="left">Add International Signed For option to International rates? (&pound;5.30 extra)</td></tr>
<tr><td align="right"><input type="checkbox" name="addspecial" value="ON" /></td><td align="left">Add Special Delivery 9am and 1pm Services?</td></tr>
</table>
</p>
<p>&nbsp;</p>
<p align="center"><input type="submit" value="Apply Royal Mail Rates" /></p>
</form>
<p>&nbsp;</p>
<?php
}elseif(getpost('royalmail')=='dosetup'){ ?>
<p>&nbsp;</p>
<p align="center">The process has completed successfully</p>
<p align="center">You still need to select "Weight Based Shipping" as your shipping method in the admin main settings page.</p>
<p>&nbsp;</p>
<?php
	$addrecorded=FALSE;
	$addinternationalsigned=FALSE;
	if(getpost('addrecorded')=='ON') $addrecorded=TRUE;
	if(getpost('addinternationalsigned')=='ON') $addinternationalsigned=TRUE;
	function doaddrate($zczone,$zcweight,$zcrate,$zcrate2,$zcrate3,$zcrate4){
		global $addrecorded,$addinternationalsigned;
		if($zczone==1 && $addrecorded && $zcrate>0) $zcrate+=1.10;
		if($zczone==1 && $addrecorded && $zcrate2>0) $zcrate2+=1.10;
		if($zczone>1 && $addinternationalsigned && $zcrate>0) $zcrate+=5.30;
		ect_query("INSERT INTO zonecharges (zcZone,zcWeight,zcRate,zcRate2,zcRate3,zcRate4) VALUES (" . $zczone . "," . $zcweight . "," . $zcrate . "," . $zcrate2 . "," . $zcrate3 . "," . $zcrate4 . ")") or ect_error();
	}
	function addpostalzone($zoneid,$pzname,$pzmultishipping,$pzmethodname1,$pzmethodname2,$pzmethodname3,$pzmethodname4){
		$sSQL="UPDATE postalzones SET pzName='" . $pzname . "',pzMultiShipping=" . $pzmultishipping . ",pzMethodName1='" . $pzmethodname1 . "',pzMethodName2='" . $pzmethodname2 . "',pzMethodName3='" . $pzmethodname3 . "',pzMethodName4='" . $pzmethodname4 . "' WHERE pzID=" . $zoneid;
		ect_query($sSQL) or ect_error();
		return($zoneid);
	}

	ect_query("DELETE FROM zonecharges") or ect_error();
	ect_query("UPDATE admin SET adminUSZones=0") or ect_error();
	ect_query("UPDATE countries SET countryZone=99999") or ect_error();
	
	$zoneid=addpostalzone(1,"Great Britain",getpost('addspecial')=='ON'?3:1,"First Class","Second Class","Special Delivery Next Day (1:00pm)","Special Delivery Next Day (9:00am)");
	doaddrate(1,0.10, 0.90, 0.69, 6.22,17.64);
	doaddrate(1,0.25, 1.20, 1.10, 6.22,17.64);
	doaddrate(1,0.50, 1.60, 1.40, 6.95,19.92);
	doaddrate(1,0.75, 2.30, 1.90, 6.95,19.92);
	doaddrate(1,1.00, 3.00, 2.60, 8.25,21.60);
	doaddrate(1,2.00, 6.85, 5.60,11.00,26.16);
	doaddrate(1,5.00,15.10,13.35,11.00,-99999);
	doaddrate(1,10.0,21.25,19.65,25.80,-99999);
	doaddrate(1,20.0,32.40,27.70,40.00,-99999);
	doaddrate(1,20.0001,-99999,-99999,-99999,-99999);
	 
	ect_query("UPDATE countries SET countryZone=" . $zoneid . " WHERE countryID IN (107,142,201,214,216)") or ect_error();
	
	$zoneid=addpostalzone(2,"Europe",0,"Standard Shipping","","","");
	doaddrate(2,0.10, 3.00,0,0,0);
	doaddrate(2,0.25, 3.50,0,0,0);
	doaddrate(2,0.50, 4.95,0,0,0);
	doaddrate(2,0.75, 6.40,0,0,0);
	doaddrate(2,1.00, 7.85,0,0,0);
	doaddrate(2,1.25, 9.30,0,0,0);
	doaddrate(2,1.50,10.75,0,0,0);
	doaddrate(2,1.75,12.20,0,0,0);
	doaddrate(2,2.00,13.65,0,0,0);
	for($indexar=1; $indexar<=12; $indexar++)
		doaddrate(2,2+($indexar/4.0),13.65+($indexar*1.45),0,0,0);
	doaddrate(2,5.01,-99999,0,0,0);
	ect_query("UPDATE countries SET countryZone=" . $zoneid . " WHERE countryID IN (4,6,12,15,16,21,22,28,32,46,48,49,50,59,62,64,65,70,71,73,74,75,86,87,91,93,97,103,108,109,110,112,118,123,124,133,143,152,153,156,157,163,175,170,171,175,182,183,186,194,195,199,203,205,217,218,219,221,223)") or ect_error();
	
	$zoneid=addpostalzone(3,"World Zone 1",0,"Standard Shipping","","","");
	doaddrate(3,0.10, 3.50,0,0,0);
	doaddrate(3,0.25, 4.50,0,0,0);
	doaddrate(3,0.50, 7.20,0,0,0);
	doaddrate(3,0.75, 9.90,0,0,0);
	doaddrate(3,1.00,12.60,0,0,0);
	doaddrate(3,1.25,15.30,0,0,0);
	doaddrate(3,1.50,18.00,0,0,0);
	doaddrate(3,1.75,20.70,0,0,0);
	doaddrate(3,2.00,23.40,0,0,0);
	for($indexar=1; $indexar<=12; $indexar++)
		doaddrate(3,2+($indexar/4.0),23.4+($indexar*2.7),0,0,0);
	doaddrate(3,5.01,-99999,0,0,0);
	ect_query("UPDATE countries SET countryZone=" . $zoneid . " WHERE countryZone=99999") or ect_error();
	
	$zoneid=addpostalzone(4,"World Zone 2",0,"Standard Shipping","","","");
	doaddrate(4,0.10, 3.50,0,0,0);
	doaddrate(4,0.25, 4.70,0,0,0);
	doaddrate(4,0.50, 7.55,0,0,0);
	doaddrate(4,0.75,10.40,0,0,0);
	doaddrate(4,1.00,13.25,0,0,0);
	doaddrate(4,1.25,16.10,0,0,0);
	doaddrate(4,1.50,18.95,0,0,0);
	doaddrate(4,1.75,21.80,0,0,0);
	doaddrate(4,2.00,24.65,0,0,0);
	for($indexar=1; $indexar<=12; $indexar++)
		doaddrate(4,2+($indexar/4.0),24.65+($indexar*2.85),0,0,0);
	doaddrate(4,5.01,-99999,0,0,0);
	ect_query("UPDATE countries SET countryZone=" . $zoneid . " WHERE countryID IN (14,63,67,99,111,131,135,136,140,141,147,151,162,169,172,190,191,197)") or ect_error();
}elseif(getget('royalmail')=='register'){ ?>
	<h2>Royal Mail Registration</h2>
	<div style="text-align:center;margin:50px">
	
	Registering with the Royal Mail is not necessary as there is no Online Shipping Rates service and instead we have setup the rates using our Weight Based Shipping tables.<br /><br />
	To apply Royal Mail rates to your weight based shipping tables, please click the button below.<br /><br />
	<input type="button" value="Setup Royal Mail Rates" onclick="document.location='adminuspsmeths.php?royalmail=setup'" />
	<br /><br >
	After doing this you must select &quot;Weight Based Shipping&quot; from the admin main settings page.<br /><br />
	There are more details about setting up Royal Mail rates here...<br />
	<a href="http://www.ecommercetemplates.com/help/royal-mail.asp" target="_blank">http://www.ecommercetemplates.com/help/royal-mail.asp</a>
	
	</div>
<?php
}elseif(getget('dhl')=='register'){ ?>
	<h2>DHL Registration</h2>
	<div style="text-align:center;margin:50px">
	
	To register with DHL you need to contact your DHL Account Manager to apply for a Site ID and Password. Once you have received these, return to the shipping methods admin page here 
	in the Ecommerce Plus admin and click on the &quot;DHL Admin&quot; button where you can enter these along with your DHL Account Number.<br /><br />
	There are more details about setting up shipping rates with DHL here...<br />
	<a href="http://www.ecommercetemplates.com/help/dhl.asp" target="_blank">http://www.ecommercetemplates.com/help/dhl.asp</a>
	
	</div>
<?php
}elseif(getget('auspost')=='register'){ ?>
	<h2>Australia Post Registration</h2>
	<div style="text-align:center;margin:50px">
	
	To register with Australia Post please click on the link below. This will take you to the Australia Post website where you can register for an API Key. Once you 
	have received your API Key, return to the shipping methods admin page here in the Ecommerce Plus admin and click on the &quot;Australia Post Admin&quot; button where you can enter your API Key.
	<br /><br />
	<a href="https://auspost.com.au/forms/pacpcs-registration.html" target="_blank">https://auspost.com.au/forms/pacpcs-registration.html</a>
	<br /><br />
	
	</div>
<?php
}elseif(getget('admin')!=''){
	$sSQL='SELECT adminUSPSUser,adminUPSUser,adminUPSPw,adminUPSAccess,adminUPSAccount,adminUPSNegotiated,adminCanPostUser,DHLSiteID,DHLSitePW,DHLAccountNo,AusPostAPI,smartPostHub FROM admin WHERE adminID=1';
	$result=ect_query($sSQL) or ect_error();
	$rs=ect_fetch_assoc($result);
?>
		  <form method="post" action="adminuspsmeths.php">
<?php
	writehiddenvar('doadmin', '1');
	writehiddenvar('method', getget('admin'));
	writehiddenvar('posted', '1'); ?>
			<table width="100%" border="0" cellspacing="2" cellpadding="3">
<?php
	if(getget('admin')=='3'){ ?>
			  <tr>
                <td colspan="2" align="center"><strong>USPS Admin</strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td width="100%" align="center" colspan="2"><hr width="70%" /><?php print $yyIfUSPS?><br /></td>
			  </tr>
			  <tr>
				<td width="50%" align="right"><strong><?php print $yyUname?>: </strong></td>
				<td width="50%" align="left"><input type="text" size="15" name="adminUSPSUser" value="<?php print $rs['adminUSPSUser']?>" /></td>
			  </tr>
<?php
	}elseif(getget('admin')=='4'){ ?>
			  <tr>
                <td colspan="2" align="center"><strong>UPS Admin</strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td width="100%" align="center" colspan="2"><hr width="70%" /><p>To obtain your UPS Rate Code you need to use the registration form <a href="adminupslicense.php"><strong>here</strong></a>.</p>
				<p>To use UPS Negotiated Rates, you need to register first and specify your UPS Shipper Number in the registration form. Then forward your UPS Rate Code and Shipper Number to your UPS Account Manager who will enable UPS Negotiated Rates once approved.</p></td>
			  </tr>
			  <tr>
				<td width="50%" align="right"><strong>UPS Rate Code: </strong></td>
				<td width="50%" align="left"><?php print upsdecode($rs['adminUPSUser'], '')?></td>
			  </tr>
			  <tr>
				<td width="50%" align="right"><strong>UPS Shipper Number: </strong></td>
				<td width="50%" align="left"><?php print $rs['adminUPSAccount']?></td>
			  </tr>
			  <tr>
				<td width="50%" align="right"><strong>Use Negotiated Rates: </strong></td>
				<td width="50%" align="left"><select size="1" name="UPSNegotiated">
					<option value="0">Use Published Rates</option>
<?php	if(trim($rs['adminUPSUser'])!='' && trim($rs['adminUPSAccount'])!='') print '<option value="1"' . ((int)$rs['adminUPSNegotiated']!=0 ? ' selected="selected"' : '') . '>Use Negotiated Rates</option>' ?>
					</select>
				</td>
			  </tr>
<?php
	}elseif(getget('admin')=='6'){ ?>
			  <tr>
                <td colspan="2" align="center"><strong><?php print $yyCanPos?> Admin</strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td width="100%" align="center" colspan="2"><hr width="70%" /><?php print $yyEnMerI?></td>
			  </tr>
			  <tr>
				<td colspan="2" align="center"><strong><?php print $yyRetID?>: </strong><input type="text" size="36" name="adminCanPostUser" value="<?php print $rs['adminCanPostUser']?>" /></td>
			  </tr>
<?php
	}elseif(getget('admin')=='9'){ ?>
			  <tr>
                <td colspan="2" align="center"><strong>DHL Admin</strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td width="100%" align="center" colspan="2">&nbsp;</td>
			  </tr>
			  <tr>
				<td align="right" width="45%"><strong>Site ID: </strong></td><td><input type="text" size="36" name="DHLSiteID" value="<?php print $rs['DHLSiteID']?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong>Site Password: </strong></td><td><input type="password" size="36" name="DHLSitePW" value="<?php print $rs['DHLSitePW']?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong>Account Number: </strong></td><td><input type="text" size="36" name="DHLAccountNo" value="<?php print $rs['DHLAccountNo']?>" /></td>
			  </tr>
<?php
	}elseif(getget('admin')=='10'){ ?>
			  <tr>
                <td colspan="2" align="center"><h2>Australia Post Admin</h2><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td width="100%" align="center" colspan="2">&nbsp;</td>
			  </tr>
			  <tr>
				<td align="right" width="40%"><strong>API Key: </strong></td><td><input type="text" size="36" name="AusPostAPI" value="<?php print $rs['AusPostAPI']?>" /></td>
			  </tr>
<?php
	}elseif(getget('admin')=='8'){ ?>
			  <tr>
                <td colspan="2" align="center"><h2>FedEx SmartPost Admin</h2><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td width="100%" align="center" colspan="2">&nbsp;</td>
			  </tr>
			  <tr>
				<td align="right" width="40%"><strong>SmartPost Hub ID: </strong></td><td><input type="text" size="36" name="smartPostHub" value="<?php print $rs['smartPostHub']?>" /></td>
			  </tr>
<?php
	} ?>
			  <tr>
				<td width="100%" align="center" colspan="2">&nbsp;</td>
			  </tr>
			  <tr>
				<td width="100%" align="center" colspan="2"><input type="submit" value="<?php print $yySubmit?>" /> <input type="reset" value="<?php print $yyReset?>" /></td>
			  </tr>
<?php
	if(getget('admin')=='4'){ ?>
			  <tr>
				<td width="100%" align="center" colspan="2"><br /><span style="font-size:10px">Please note: Subsequent registrations for UPS OnLine® Tools will change the UPS Rate Code
within this application. In the event Negotiated Rates functionality was enabled under a previous UPS Rate Code, the
Negotiated Rates functionality will be disabled.</span></td>
			  </tr>
<?php
	} ?>
			  <tr>
				<td width="100%" align="center" colspan="2"><br />&nbsp;<br />&nbsp;<br /><a href="adminuspsmeths.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
			</table>
		  </form>
<?php
	ect_free_result($result);
}elseif($method==''){ ?>
			<table width="100%" border="0" cellspacing="2" cellpadding="3">
			  <tr>
                <td align="center"><h2><?php print $yyShpAdm?></h2>
			<table width="100%" class="stackable admin-table-a sta-white">
			  <tr>
				<th class="cobhl" height="30"><strong>Shipping Carrier</strong></th>
				<th class="cobhl"><strong>Registration</strong></th>
				<th class="cobhl"><strong>Administration</strong></th>
				<th class="cobhl"><strong>Shipping Method</strong></th>
			  </tr>
			  <tr>
				<td class="cobhl" height="30"><strong>Australia Post</strong></td>
				<td class="cobll"><input type="button" value="<?php print str_replace("UPS","Australia Post",$yyRegUPS)?>" onclick="document.location='adminuspsmeths.php?auspost=register'" /></td>
				<td class="cobll"><input type="button" value="Australia Post Admin" onclick="document.location='adminuspsmeths.php?admin=10'" /></td>
				<td class="cobll"><input type="button" value="<?php print $yyEdit.' '.$yyShpMet?>" onclick="document.location='adminuspsmeths.php?method=10'" /></td>
			  </tr>
			  <tr>
				<td class="cobhl" height="30"><strong>Canada Post</strong></td>
				<td class="cobll"><input type="button" value="<?php print str_replace("UPS","Canada Post",$yyRegUPS)?>" onclick="document.location='adminuspsmeths.php?canadapost=register'" /></td>
				<td class="cobll"><input type="button" value="Canada Post Admin" onclick="document.location='adminuspsmeths.php?admin=6'" /></td>
				<td class="cobll"><input type="button" value="<?php print $yyEdit.' '.$yyShpMet?>" onclick="document.location='adminuspsmeths.php?method=6'" /></td>
			  </tr>
			  <tr>
				<td class="cobhl" height="30"><strong>DHL</strong></td>
				<td class="cobll"><input type="button" value="<?php print str_replace("UPS","DHL",$yyRegUPS)?>" onclick="document.location='adminuspsmeths.php?dhl=register'" /></td>
				<td class="cobll"><input type="button" value="DHL Admin" onclick="document.location='adminuspsmeths.php?admin=9'" /></td>
				<td class="cobll"><input type="button" value="<?php print $yyEdit.' '.$yyShpMet?>" onclick="document.location='adminuspsmeths.php?method=9'" /></td>
			  </tr>
			  <tr>
				<td class="cobhl" height="30"><strong>FedEx</strong></td>
				<td class="cobll"><input type="button" value="<?php print str_replace("UPS","FedEx",$yyRegUPS)?>" onclick="document.location='adminfedexlicense.php'" /></td>
				<td class="cobll">&nbsp;</td>
				<td class="cobll"><input type="button" value="<?php print $yyEdit.' '.$yyShpMet?>" onclick="document.location='adminuspsmeths.php?method=7'" /></td>
			  </tr>
			  <tr>
				<td class="cobhl" height="30"><strong>FedEx SmartPost</strong></td>
				<td class="cobll"><input type="button" value="<?php print str_replace("UPS","FedEx",$yyRegUPS)?>" onclick="document.location='adminfedexlicense.php'" /></td>
				<td class="cobll"><input type="button" value="FedEx SmartPost Admin" onclick="document.location='adminuspsmeths.php?admin=8'" /></td>
				<td class="cobll"><input type="button" value="<?php print $yyEdit.' '.$yyShpMet?>" onclick="document.location='adminuspsmeths.php?method=8'" /></td>
			  </tr>
			  <tr>
				<td class="cobhl" height="30"><strong>Royal Mail</strong></td>
				<td class="cobll"><input type="button" value="<?php print str_replace("UPS","Royal Mail",$yyRegUPS)?>" onclick="document.location='adminuspsmeths.php?royalmail=register'" /></td>
				<td class="cobll"><input type="button" value="Setup Royal Mail Rates" onclick="document.location='adminuspsmeths.php?royalmail=setup'" /></td>
				<td class="cobll"><input type="button" value="<?php print $yyEdit.' Postal Zones'?>" onclick="document.location='adminzones.php'" /></td>
			  </tr>
			  <tr>
				<td class="cobhl" height="30"><strong>UPS</strong></td>
				<td class="cobll"><input type="button" value="<?php print $yyRegUPS?>" onclick="document.location='adminupslicense.php'" /></td>
				<td class="cobll"><input type="button" value="UPS Admin" onclick="document.location='adminuspsmeths.php?admin=4'" /></td>
				<td class="cobll"><input type="button" value="<?php print $yyEdit.' '.$yyShpMet?>" onclick="document.location='adminuspsmeths.php?method=4'" /></td>
			  </tr>
			  <tr>
				<td class="cobhl" height="30"><strong>USPS</strong></td>
				<td class="cobll"><input type="button" value="Register with USPS" onclick="window.open('https://reg.usps.com/register','USPSSignup','')" /></strong></td>
				<td class="cobll"><input type="button" value="USPS Admin" onclick="document.location='adminuspsmeths.php?admin=3'" /></td>
				<td class="cobll"><input type="button" value="<?php print $yyEdit.' '.$yyShpMet?>" onclick="document.location='adminuspsmeths.php?method=3'" /></td>
			  </tr>
			  <tr>
				<td class="cobhl" height="30"><strong>Weight / Price Based</strong></td>
				<td class="cobll">&nbsp; </td>
				<td class="cobll">&nbsp; </td>
				<td class="cobll"><input type="button" value="<?php print $yyEdit.' Postal Zones'?>" onclick="document.location='adminzones.php'" /></td>
			  </tr>
			</table>

			<br />&nbsp;<br />&nbsp;<br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;
			
				</td>
			  </tr>
			</table>
			<br />&nbsp;
<?php
}elseif(getpost('posted')=="1" && $success){ ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="admin.php"><strong><?php print $yyClkHer?></strong></a>.<br /><br />&nbsp;
                </td>
			  </tr>
			</table>
<?php
}else{ ?>
		  <form method="post" action="adminuspsmeths.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="method" value="<?php print $method?>" />
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr> 
                <td width="100%" colspan="5" align="center"><br /><strong><?php print $yyUsUpd . " " . $shipmet . " " . $yyShpMet?>.</strong><br />&nbsp;</td>
			  </tr>
<?php	if(! $success){ ?>
			  <tr> 
                <td width="100%" colspan="5" align="center"><br /><span style="color:#FF0000"><?php print $errmsg?></span>
                </td>
			  </tr>
<?php	}
		$sSQL='SELECT uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal,uspsFSA FROM uspsmethods ';
		if($shipType==3)
			$sSQL.='WHERE uspsID<100 ';
		elseif($shipType==4)
			$sSQL.='WHERE uspsID>100 AND uspsID<200 ';
		elseif($shipType==6)
			$sSQL.='WHERE uspsID>200 AND uspsID<300 ';
		elseif($shipType==7)
			$sSQL.='WHERE uspsID>300 AND uspsID<400 ';
		elseif($shipType==8)
			$sSQL.='WHERE uspsID>400 AND uspsID<500 ';
		elseif($shipType==9)
			$sSQL.='WHERE uspsID>500 AND uspsID<600 ';
		elseif($shipType==10)
			$sSQL.='WHERE uspsID>600 AND uspsID<700 ';
		$sSQL.='ORDER BY uspsLocal DESC, uspsShowAs, uspsID';
		$result=ect_query($sSQL) or ect_error();
		if($shipType==3||$shipType==10){
?>
			  <tr>
				<td colspan="5"><ul><?php
			if($shipType==4)
				print '<li><span style="font-size:10px">'.$yyUSS3.'</span></li>';
			elseif($shipType==3)
				print '<li><span style="font-size:10px">'.$yyUSS1.'</span></li>';
?>
			<li><span style="font-size:10px">You can use this page to set which <?php print $shipmet?> shipping methods qualify for free shipping discounts by checking the FSA (Free Shipping Available) checkbox.</span></li>
				<li><span style="font-size:10px"><?php
			print str_replace('USPS',$shipmet,$yyUSS2);
			if($shipType==3){ ?>
				<a href="http://www.usps.com">http://www.usps.com</a>
<?php		}elseif($shipType==4){ ?>
				<a href="http://www.ups.com">http://www.ups.com</a>
<?php		}elseif($shipType==6){ ?>
				<a href="http://www.canadapost.ca" target="_blank">http://www.canadapost.ca</a>.
<?php		}elseif($shipType==9){ ?>
				<a href="http://www.dhl.com" target="_blank">http://www.dhl.com</a>.
<?php		}elseif($shipType==10){ ?>
				<a href="http://auspost.com.au" target="_blank">http://auspost.com.au</a>.
<?php		}else{ ?>
				<a href="http://www.fedex.com" target="_blank">http://www.fedex.com</a>.
<?php		} ?>
				</span></li>
				</ul></td>
			  </tr>
<?php		while($allmethods=ect_fetch_assoc($result)){ ?>
			  <tr>
			    <td align="right"><?php print $shipType==10?'AusPost Method':$yyUSPSMe?>:</td>
				<td align="left"><span style="font-size:10px;font-weight:bold"><?php
				if($shipType==3){
					if($allmethods['uspsID']=='1')
						print 'Express Mail';
					elseif($allmethods['uspsID']=='2')
						print 'Priority Mail';
					elseif($allmethods['uspsID']=='3')
						print 'Parcel Post';
					elseif($allmethods['uspsID']=='14')
						print 'Media Mail';
					elseif($allmethods['uspsID']=='15')
						print 'Bound Printed Matter';
					elseif($allmethods['uspsID']=='16')
						print 'First Class Mail';
					elseif($allmethods['uspsID']=='30')
						print 'Global Express Guaranteed Document';
					elseif($allmethods['uspsID']=='31')
						print 'Global Express Guaranteed Non-Document Rectangular';
					elseif($allmethods['uspsID']=='32')
						print 'Global Express Guaranteed Non-Document Non-Rectangular';
					elseif($allmethods['uspsID']=='33')
						print 'Express Mail International (EMS)';
					elseif($allmethods['uspsID']=='34')
						print 'Express Mail International (EMS) Flat Rate Envelope';
					elseif($allmethods['uspsID']=='35')
						print 'Priority Mail International';
					elseif($allmethods['uspsID']=='36')
						print 'Priority Mail International Flat Rate Envelope';
					elseif($allmethods['uspsID']=='37')
						print 'Priority Mail International Regular Flat-Rate Boxes';
					elseif($allmethods['uspsID']=='38')
						print 'First Class Mail International Letters';
					elseif($allmethods['uspsID']=='39')
						print 'First Class Mail International Large Envelope';
					elseif($allmethods['uspsID']=='40')
						print 'First Class Mail International Package';
					elseif($allmethods['uspsID']=='41')
						print 'Priority Mail International Large Flat-Rate Box';
					elseif($allmethods['uspsID']=='42')
						print 'Priority Mail International Small Flat Rate Box';
					elseif($allmethods['uspsID']=='43')
						print 'Express Mail International Legal Flat Rate Envelope';
					elseif($allmethods['uspsID']=='44')
						print 'Priority Mail International Small Flat Rate Envelope';
					elseif($allmethods['uspsID']=='45')
						print 'Priority Mail International DVD Flat Rate Box';
					elseif($allmethods['uspsID']=='46')
						print 'Express Mail International Flat Rate Box';
					else
						print $allmethods['uspsID'];
				}else{ // Australia Post
					if($allmethods['uspsMethod']=='AUS_PARCEL_REGULAR')
						print 'Parcel Post';
					elseif($allmethods['uspsMethod']=='AUS_PARCEL_REGULAR_SATCHEL_3KG')
						print 'Parcel Post Medium (3Kg) Satchel';
					elseif($allmethods['uspsMethod']=='AUS_PARCEL_EXPRESS_SATCHEL_3KG')
						print 'Express Post Medium (3Kg) Satchel';
					elseif($allmethods['uspsMethod']=='AUS_PARCEL_EXPRESS')
						print 'Express Post';
					elseif($allmethods['uspsMethod']=='INTL_SERVICE_ECI_PLATINUM')
						print 'Express Courier International Platinum';
					elseif($allmethods['uspsMethod']=='INTL_SERVICE_ECI_M')
						print 'Express Courier International Merchandise';
					elseif($allmethods['uspsMethod']=='INTL_SERVICE_ECI_D')
						print 'Express Courier International Documents';
					elseif($allmethods['uspsMethod']=='INTL_SERVICE_EPI')
						print 'Express Post International';
					elseif($allmethods['uspsMethod']=='INTL_SERVICE_PTI')
						print 'Pack and Track International';
					elseif($allmethods['uspsMethod']=='INTL_SERVICE_RPI')
						print 'Registered Post International';
					elseif($allmethods['uspsMethod']=='INTL_SERVICE_AIR_MAIL')
						print 'Air Mail';
					elseif($allmethods['uspsMethod']=='INTL_SERVICE_SEA_MAIL')
						print 'Sea Mail';
					elseif($allmethods['uspsMethod']=='INTL_SERVICE_EPI_B4')
						print 'Express Post International B4';
					elseif($allmethods['uspsMethod']=='INTL_SERVICE_RPI_DLE')
						print 'Registered Post International DLE';
					elseif($allmethods['uspsMethod']=='INTL_SERVICE_RPI_B4')
						print 'Registered Post International B4';
					elseif($allmethods['uspsMethod']=='INTL_SERVICE_EPI_C5')
						print 'Express Post International C5';
				}
					?></span></td>
				<td align="center"><?php print $yyUseMet?></td>
				<td align="center"><acronym title="<?php print $yyFSApp?>"><?php print $yyFSA?></acronym></td>
				<td align="center"><?php print $yyType?></td>
			  </tr>
			  <tr>
			    <td align="right"><?php print $yyShwAs?>:</td>
				<td align="left"><input type="text" name="methodshow<?php print $allmethods["uspsID"]?>" value="<?php print $allmethods["uspsShowAs"]?>" size="36" /></td>
				<td align="center"><input type="checkbox" name="methoduse<?php print $allmethods["uspsID"]?>" value="ON" <?php if((int)$allmethods["uspsUseMethod"]==1) print 'checked="checked"'?> /></td>
				<td align="center"><input type="checkbox" name="methodfsa<?php print $allmethods["uspsID"]?>" value="ON" <?php if((int)$allmethods["uspsFSA"]==1) print 'checked="checked"'?> /></td>
				<td align="center"><?php if($allmethods["uspsLocal"]==1) print '<span style="color:#FF0000">Domestic</span>'; else print '<span style="color:#0000FF">Internat.</span>';?></td>
			  </tr>
			  <tr>
				<td colspan="5" align="center"><hr width="80%" /></td>
			  </tr>
<?php		}
		}else{
			while($allmethods=ect_fetch_assoc($result)){ ?>
			  <tr>
			    <td align="right"><input type="hidden" name="methodshow<?php print $allmethods["uspsID"]?>" value="1" /><strong><?php print $yyShipMe?>:</strong></td>
				<td align="left"> &nbsp; <?php print $allmethods["uspsShowAs"] . checkisdocument($shipType,$allmethods['uspsMethod'])?></td>
				<td align="center"><strong><?php print ($shipType==4 || $shipType==6 || $shipType==7 || $shipType==9?$yyUseMet:'&nbsp;')?></strong></td>
				<td align="center"><acronym title="<?php print $yyFSApp?>"><?php print $yyFSA?></acronym></td>
				<td>&nbsp;</td>
			  </tr>
			  <tr>
				<td colspan="2">&nbsp;</td>
				<td align="center"><input type="<?php print ($shipType==4 || $shipType==6 || $shipType==7 || $shipType==9?'checkbox':'hidden')?>" name="methoduse<?php print $allmethods["uspsID"]?>" value="ON" <?php if((int)$allmethods["uspsUseMethod"]==1) print 'checked="checked"'?> /></td>
				<td align="center"><input type="checkbox" name="methodfsa<?php print $allmethods['uspsID']?>" value="ON" <?php if((int)$allmethods["uspsFSA"]==1) print 'checked="checked"'?> /></td>
				<td>&nbsp;</td>
			  </tr>
			  <tr>
				<td colspan="5" align="center"><hr width="80%" /></td>
			  </tr>
<?php		}
		}
		ect_free_result($result); ?>
			  <tr> 
                <td width="100%" colspan="5" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" /><br />&nbsp;</td>
			  </tr>
            </table>
		  </form>
<?php
} ?>