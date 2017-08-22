<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
$success=TRUE;
$alreadygotadmin = getadminsettings();
$countryCode = $origCountryCode;
if(@$upstestmode)
	$registerurl='https://wwwcie.ups.com/webservices/Registration';
else
	$registerurl='https://onlinetools.ups.com/webservices/Registration';
$allowedcountries="'AR','AU','AT','BE','BR','CA','CL','CN','CO','CR','DK','DO','FI','FR','DE','GR','GT','HK','IN','IE','IL','IT','JP','MY','MX','NL','NZ','NO','PA','PH','PT','PR','SG','KR','ES','SE','CH','TW','TH','GB','US'";
function ParseUPSLicenseOutput($sXML, $rootNodeName, &$thetext, &$errormsg){
	$noError = TRUE;
	$errormsg = "";
	$gotxml=FALSE;
	$thetext="";
	$xmlDoc = new vrXMLDoc($sXML);
//	if($xmlDoc->nodeList->nodeName[0] != $rootNodeName){
//		print "Error with rootnode " . $rootNodeName . ", is " . $xmlDoc->nodeList->nodeName[0] . "<br />";
//		return(false);
//	}
	if($xmlDoc->nodeList->getValueByTagName('err:Severity')=='Hard'){
		$noError=FALSE;
		$errormsg=$xmlDoc->nodeList->getValueByTagName('err:Description');
	}
	if($noError){
		$nodeList = $xmlDoc->nodeList->childNodes[0];
		for($i = 0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=='Response'){
				$e = $nodeList->childNodes[$i];
				for($j = 0; $j < $e->length; $j++){
					if($e->nodeName[$j]=='common:ResponseStatus'){
						$t = $e->childNodes[$j];
						for($k = 0; $k < $t->length; $k++){
							if($t->nodeName[$k]=='common:Code'){
								$noError = ($t->nodeValue[$k]==1);
							}elseif($t->nodeName[$k]=='common:Description'){
								// errormsg = errormsg & t.firstChild.nodeValue
							}
						}
					}elseif($e->nodeName[$j]=='ResponseStatusCode'){
						$noError = ((int)$e->nodeValue[$j])==1;
					}elseif($e->nodeName[$j]=='Error'){
						$errormsg = '';
						$t = $e->childNodes[$j];
						for($k = 0; $k < $t->length; $k++){
							if($t->nodeName[$k]=='ErrorSeverity'){
								if($t->nodeValue[$k]=='Transient')
									$errormsg = 'This is a temporary error. Please wait a few moments then refresh this page.<br />' . $errormsg;
							}elseif($t->nodeName[$k]=='ErrorDescription'){
								$errormsg.=$t->nodeValue[$k];
							}
						}
					}
					// print "The Nodename is : " . e.nodeName . ":" . e.firstChild.nodeValue . "<br />";
				}
			}elseif($nodeList->nodeName[$i]=='AccessLicenseNumber'){
				$thetext = $nodeList->nodeValue[$i];
			}elseif($nodeList->nodeName[$i]=='AccessLicenseText'){
				$thetext = $nodeList->nodeValue[$i];
				$_SESSION['adminUPSLicense'] = $nodeList->nodeValue[$i];
			//	$sSQL = "UPDATE admin SET adminUPSLicense='" . str_replace("'","\\'",$nodeList->nodeValue[$i]) . "' WHERE adminID=1";
			//	ect_query($sSQL) or ect_error();
			}elseif($nodeList->nodeName[$i]=='UserId'){
				$thetext = $nodeList->nodeValue[$i];
			}
		}
	}
	return($noError);
}
function registrationsuccess(){
	global $saveuser,$thepw,$success,$errormsg,$yyUPSWiz,$yyTryBac,$yyUPStm,$yySorErr,$yyClkHer,$yyRegSucc,$yyDone,$yyError,$yyAdmMai,$yyUPSLi5,$yyUPSLi6,$yyUPSLi7,$yyUPSLi8; ?>
	<form method="post" name="licform" action="admin.php">
	  <input type="hidden" name="upsstep" value="5" />
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr>
				<td rowspan="3" width="70" align="center" valign="top"><img src="../images/upslogo.png" border="0" alt="UPS" /><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;</td>
                <td width="100%" align="center"><strong><?php print $yyUPSWiz?> - <?php if($success) print $yyRegSucc; else print $yyError; ?></strong><br />&nbsp;
                </td>
			  </tr>
<?php
	if($success){
		$sSQL = "UPDATE admin SET adminUPSUser='" . upsencode($saveuser, "") . "',adminUPSpw='" . upsencode($thepw, "") . "'";
		ect_query($sSQL) or ect_error();
		$_SESSION['adminUPSLicense']='';
?>
			  <tr> 
                <td width="100%" align="left">
				  <p><strong><?php print $yyRegSucc?> !</strong></p>
				  <p><?php print $yyUPSLi5?></p>
				  <p><?php print $yyUPSLi6?> <a href="http://www.ups.com/content/us/en/bussol/browse/cat/developer_kit.html" target="_blank">http://www.ups.com/content/us/en/bussol/browse/cat/developer_kit.html</a>.</p>
				  <p><?php print $yyUPSLi7?> <a href="adminmain.php"><?php print $yyAdmMai?></a>.</p>
				  <p><?php print $yyUPSLi8?> <a href="http://www.ups.com/content/us/en/bussol/browse/internet_shipping.html" target="_blank"><?php print $yyClkHer?></a>.</p>
				  <p>&nbsp;</p>
				  <p align="center"><input type="submit" value="<?php print $yyDone?>" /></p>
				  <p>&nbsp;</p>
                </td>
			  </tr>
<?php
	}else{ ?>
			  <tr> 
                <td width="100%" align="center"><p><?php print $yySorErr?></strong></p>
				<p>&nbsp;</p>
				<p><?php print $errormsg ?></p>
				<p>&nbsp;</p>
				<p><?php print $yyTryBac?> <a href="javascript:history.go(-1)"><?php print $yyClkHer?></a>.</p>
				<p>&nbsp;</p>
                </td>
			  </tr>
<?php
	} ?>
			  <tr> 
                <td colspan="2" width="100%" align="center">
				  <p>&nbsp;</p><p><span style="font-size:10px"><?php print $yyUPStm?></span></p>
                </td>
			  </tr>
            </table>
          </td>
        </tr>
      </table>
	</form>
<?php
}
if(getpost('reregister')=='3'){
	$sXML='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:upss="http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0" xmlns="http://www.ups.com/XMLSchema/XOLTWS/Registration/v1.0" xmlns:common="http://www.ups.com/XMLSchema/XOLTWS/Common/v1.0">' .
		'<soapenv:Header><upss:UPSSecurity><upss:UsernameToken><upss:Username>vince2002</upss:Username><upss:Password>Ups332211</upss:Password></upss:UsernameToken><upss:ServiceAccessToken><upss:AccessLicenseNumber>DB9341F6791A3D7A</upss:AccessLicenseNumber></upss:ServiceAccessToken></upss:UPSSecurity></soapenv:Header>';
	$sXML.='<soapenv:Body>';
	$sXML.='<ManageAccountRequest>' . 
			'<ShipperAccount><AccountNumber>' . getpost('upsaccount') . '</AccountNumber>';
	$sXML.='<PostalCode>' . getpost('postcode') . '</PostalCode>' .
			'<CountryCode>' . getpost('country') . '</CountryCode>';
	if(getpost('invoicenumber')!=''){
		$sXML.='<InvoiceInfo>';
		$sXML.='<InvoiceNumber>' . vrxmlencode(getpost('invoicenumber')) . '</InvoiceNumber>';
		if(getpost('invoicedate')!='') $sXML.='<InvoiceDate>' . vrxmlencode(getpost('invoicedate')) . '</InvoiceDate>';
		if(getpost('invoicecurrency')!='') $sXML.='<CurrencyCode>' . vrxmlencode(getpost('invoicecurrency')) . '</CurrencyCode>';
		if(getpost('invoiceamount')!='') $sXML.='<InvoiceAmount>' . vrxmlencode(getpost('invoiceamount')) . '</InvoiceAmount>';
		if(getpost('invoicecontrolid')!='') $sXML.='<ControlID>' . vrxmlencode(getpost('invoicecontrolid')) . '</ControlID>';
		$sXML.='</InvoiceInfo>';
	}
	$sXML.='</ShipperAccount></ManageAccountRequest>' .
		'</soapenv:Body></soapenv:Envelope>';
	if(callcurlfunction($registerurl, $sXML, $xmlres, '', $errormsg, FALSE)){
		$success = ParseUPSLicenseOutput($xmlres, 'reg:ManageAccountResponse', $theuser, $errormsg);
	}
	registrationsuccess();
}elseif(getpost('reregister')=='1'){ ?>
	<form method="post" name="licform" action="adminupslicense.php">
		<input type="hidden" name="reregister" value="2" />
<?php
	$sSQL = 'SELECT adminUPSAccount FROM admin WHERE adminID=1';
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result))
		$adminUPSAccount=trim($rs['adminUPSAccount']);
	ect_free_result($result);
	writehiddenidvar('noupsaccount', '1') ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%" align="center">
			<p>&nbsp;</p>
            <table width="500" border="0" cellspacing="0" cellpadding="2">
			  <tr>
				<td rowspan="6" width="70" align="left" valign="top"><img src="../images/upslogo.png" border="0" alt="UPS" /><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;</td>
                <td width="100%" align="center" colspan="2"><p><strong><?php print $yyUPSWiz?> - </strong></p>
				<p>Please enter your UPS Shipper Number below then click &quot;Continue&quot;.<br />&nbsp;
                </td>
			  </tr>
			  <tr> 
                <td align="right"><p><strong>UPS Shipper Number:</strong></td>
				<td><input type="text" name="upsaccount" value="<?php print $adminUPSAccount?>" size="20" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $yyCountry?> : </strong></td>
				<td><select name="country" size="1">
<option value=''><?php print $yySelect?></option>
<?php
$sSQL = 'SELECT countryName,countryCode FROM countries WHERE countryCode IN (' . $allowedcountries . ') ORDER BY countryName';
$result=ect_query($sSQL) or ect_error();
while($rs=ect_fetch_assoc($result)){
	print '<option value="'.$rs['countryCode'].'"';
	if($origCountryCode==$rs['countryCode']) print ' selected="selected"';
	print '>'.$rs['countryName'].'</option>'."\r\n";
}
ect_free_result($result);
?>
				</select></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $yyPCode?> : </strong></td>
				<td><input type="text" name="postcode" size="15" value="<?php print $origZip?>" /></td>
			  </tr>
			  <tr> 
                <td align="right">&nbsp;</td>
				<td><p>&nbsp;</p><p><input type="submit" value="<?php print $yyContin?>" /></p></td>
			  </tr>
			  <tr> 
                <td colspan="2" width="100%" align="center">
				  <p>&nbsp;</p><p><span style="font-size:10px"><?php print $yyUPStm?></span></p>
                </td>
			  </tr>
            </table>
          </td>
        </tr>
      </table>
	</form>
<?php
}elseif(getpost('upsstep')=='4' && getpost('upsaccount')=='' && getpost('noupsaccount')!='1'){ ?>
	<form method="post" name="licform" action="adminupslicense.php">
<?php
	foreach(@$_POST as $key=>$val){
		if($key!='upsaccount') writehiddenidvar($key, $val);
	}
	writehiddenidvar('noupsaccount', '1'); ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%" align="center">
			<p>&nbsp;</p>
            <table width="500" border="0" cellspacing="0" cellpadding="2">
			  <tr>
				<td rowspan="3" width="70" align="left" valign="top"><img src="../images/upslogo.png" border="0" alt="UPS" /><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;</td>
                <td width="100%" align="center"><p><strong><?php print $yyUPSWiz?> - </strong></p>
				<p>You did not provide a UPS Shipper Number with your registration. 
				If you have a UPS Shipper Number, please enter it in the space provided below before clicking &quot;Continue&quot;.<br />&nbsp;
                </td>
			  </tr>
			  <tr> 
                <td align="left"><p><strong>UPS Shipper Number:</strong>
				<input type="text" name="upsaccount" value="" size="20" /></p>
				<p>&nbsp;</p>
				<p><input type="submit" value="<?php print $yyContin?>" /></td>
			  </tr>
			  <tr> 
                <td colspan="2" width="100%" align="center">
				  <p>&nbsp;</p><p><span style="font-size:10px"><?php print $yyUPStm?></span></p>
                </td>
			  </tr>
            </table>
          </td>
        </tr>
      </table>
	</form>
<?php
}elseif(getpost('reregister')=='2' || (getpost('upsstep')=='4' && getpost('upsaccount')!='' && getpost('invoicenumber')=='' && getpost('noinvoicenumber')!='1')){ ?>
	<form method="post" name="licform" action="adminupslicense.php">
<?php
	foreach(@$_POST as $key=>$val){
		if($key=='reregister')
			writehiddenvar("reregister", "3");
		else
			writehiddenvar($key, $val);
	}
	writehiddenidvar("noinvoicenumber", "1") ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%" align="center">
			<p>&nbsp;</p>
            <table width="600" border="0" cellspacing="0" cellpadding="2">
			  <tr>
				<td rowspan="3" width="70" align="left" valign="top"><img src="../images/upslogo.png" border="0" alt="UPS" /><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;</td>
                <td width="100%" align="center"><p><strong><?php print $yyUPSWiz?> - </strong></p>
				<p>If you have received a UPS invoice in the past, please enter the invoice information in the space provided below 
				before clicking &quot;Continue&quot;. This will authenticate your account and allow you to view negotiated rates.<br />&nbsp;
                </td>
			  </tr>
			  <tr> 
                <td align="left">
					<table>
						<tr><td align="right"><strong>Invoice Number:</strong></td>
						<td><input type="text" name="invoicenumber" value="" size="20" /></td></tr>
						<tr><td align="right"><strong>Invoice Date:</strong></td>
						<td><input type="text" name="invoicedate" value="" size="20" /> (E.g. 20120225)</td></tr>
						<tr><td align="right"><strong>Invoice Amount:</strong></td>
						<td><input type="text" name="invoiceamount" value="" size="20" /></td></tr>
						<tr><td align="right"><strong>Invoice Currency:</strong></td>
						<td><select name="invoicecurrency" size="1">
<option value=""><?php print $yySelect?></option>
<?php
$sSQL = 'SELECT DISTINCT countryCurrency FROM countries WHERE countryCode IN (' . $allowedcountries . ') ORDER BY countryName';
$result=ect_query($sSQL) or ect_error();
while($rs=ect_fetch_assoc($result)){
	print '<option value="'.$rs['countryCurrency'].'">'.$rs['countryCurrency'].'</option>'."\r\n";
}
ect_free_result($result);
?>
				</select></td></tr>
						<tr><td align="right"><strong>Invoice Control ID:</strong></td>
						<td><input type="text" name="invoicecontrolid" value="" size="20" /></td></tr>
						<tr><td align="right">&nbsp;</td>
						<td>Optional, but this value is required if it is present on your invoice.</td></tr>
						<tr><td>
						<p>&nbsp;</p>
						<p><input type="submit" value="<?php print $yyContin?>" /></td></tr>
					</table>
				</td>
			  </tr>
			  <tr> 
                <td colspan="2" width="100%" align="center">
				  <p>&nbsp;</p><p><span style="font-size:10px"><?php print $yyUPStm?></span></p>
                </td>
			  </tr>
            </table>
          </td>
        </tr>
      </table>
	</form>
<?php
}elseif(getpost('upsstep')=='4'){
	$sXML = '<?xml version="1.0" encoding="ISO-8859-1"?>';
	$sXML.='<AccessLicenseRequest xml:lang="en-US"><Request><TransactionReference><CustomerContext>Ecomm Plus UPS Reg</CustomerContext><XpciVersion>1.0001</XpciVersion></TransactionReference>';
	$sXML.="<RequestAction>AccessLicense</RequestAction><RequestOption>AllTools</RequestOption></Request>";
	$sXML.="<CompanyName>" . getpost('company') . "</CompanyName>";
	$sXML.="<Address><AddressLine1>" . getpost('address') . "</AddressLine1>";
	if(getpost('address2')!='') $sXML.="<AddressLine2>" . getpost('address2') . "</AddressLine2>";
	$sXML.="<City>" . getpost('city') . "</City>";
	if(getpost('country')=="US" || getpost('country')=="CA")
		$sXML.="<StateProvinceCode>" . getpost('usstate') . "</StateProvinceCode>";
	else
		$sXML.="<StateProvinceCode>XX</StateProvinceCode>";
	if(getpost('postcode')!='') $sXML.="<PostalCode>" . getpost('postcode') . "</PostalCode>";
	$sXML.="<CountryCode>" . getpost('country') . "</CountryCode></Address>";
	$sXML.="<PrimaryContact><Name>" . getpost('contact') . "</Name><Title>" . getpost('ctitle') . "</Title>";
	$sXML.="<EMailAddress>" . getpost('email') . "</EMailAddress><PhoneNumber>" . getpost('telephone') . "</PhoneNumber></PrimaryContact>";
	$sXML.="<CompanyURL>" . getpost('websiteurl') . "</CompanyURL>";
	$sXML.="<DeveloperLicenseNumber>BB9341E83CC05B12</DeveloperLicenseNumber>";
	$sXML.="<AccessLicenseProfile><CountryCode>" . getpost('countryCode') . "</CountryCode><LanguageCode>" . getpost('languageCode') . "</LanguageCode>";
	$sXML.="<AccessLicenseText>" . $_SESSION['adminUPSLicense'] . "</AccessLicenseText>";
	$sXML.="</AccessLicenseProfile>";
	$sXML.="<OnLineTool><ToolID>RateXML</ToolID><ToolVersion>1.0</ToolVersion></OnLineTool><OnLineTool><ToolID>TrackXML</ToolID><ToolVersion>1.0</ToolVersion></OnLineTool>";
	$sXML.="<ClientSoftwareProfile><SoftwareInstaller>" . getpost('upsrep') . "</SoftwareInstaller><SoftwareProductName>Ecommerce Plus Templates</SoftwareProductName><SoftwareProvider>Internet Business Solutions SL</SoftwareProvider><SoftwareVersionNumber>2.5</SoftwareVersionNumber></ClientSoftwareProfile>";
	$sXML.="</AccessLicenseRequest>";
	if(@$pathtocurl!=''){
		exec($pathtocurl . ' --data-binary ' . escapeshellarg($sXML) . ' https://www.ups.com/ups.app/xml/License', $res, $retvar);
		$res = implode("\n",$res);
		$success = ParseUPSLicenseOutput($res, "AccessLicenseResponse", $accessnumber, $errormsg);
	}else{
		if (!$ch = curl_init()) {
			$success = false;
			$errormsg = "cURL package not installed in PHP";
		}else{
			curl_setopt($ch, CURLOPT_URL,'https://www.ups.com/ups.app/xml/License'); 
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $sXML);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if(@$curlproxy!=''){
				curl_setopt($ch, CURLOPT_PROXY, $curlproxy);
			}
			$res = curl_exec($ch);
			curl_close($ch);
			$success = ParseUPSLicenseOutput($res, "AccessLicenseResponse", $accessnumber, $errormsg);
		}
	}
	if($success){
		$sSQL = "UPDATE admin SET adminUPSAccess='" . $accessnumber . "'";
		if(getpost('upsaccount')!='') $sSQL.=",adminUPSAccount='" . escape_string(getpost('upsaccount')) . "'";
		$sSQL.=',adminUPSNegotiated=0 WHERE adminID=1';
		ect_query($sSQL) or ect_error();
		$noloops=0;
		srand((double)microtime()*1000000);
		$upperbound = '999999';
		$lowerbound = '100000';
		if(getpost('myupsuser')!='' && getpost('myupspw')!=''){
			$saveuser = $theuser = getpost('myupsuser');
			$thepw = getpost('myupspw');
		}else{
			$thepw = 'ecp' . rand($lowerbound, $upperbound);
			$theuser = 'ecu' . rand($lowerbound, $upperbound);
			while($theuser!='' && $success && $noloops < 5){
				$saveuser = $theuser;
				$sXML='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:upss="http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0" xmlns="http://www.ups.com/XMLSchema/XOLTWS/Registration/v1.0" xmlns:common="http://www.ups.com/XMLSchema/XOLTWS/Common/v1.0">' .
					'<soapenv:Header><upss:UPSSecurity><upss:UsernameToken><upss:Username>vince2002</upss:Username><upss:Password>Ups332211</upss:Password></upss:UsernameToken><upss:ServiceAccessToken><upss:AccessLicenseNumber>DB9341F6791A3D7A</upss:AccessLicenseNumber></upss:ServiceAccessToken></upss:UPSSecurity></soapenv:Header>';
				$sXML.='<soapenv:Body><RegisterRequest>';
				$sXML.='<Username>'.$theuser.'</Username><Password>'.$thepw.'</Password>' .
					'<CompanyName>' . getpost('company') . '</CompanyName><CustomerName>' . getpost('contact') . '</CustomerName>' .
					'<Title>' . getpost('ctitle') . '</Title>';
				$sXML.='<Address><AddressLine>' . getpost('address') . '</AddressLine>';
				if(getpost('address2')!='') $sXML.='<AddressLine>' . getpost('address2') . '</AddressLine>';
				$sXML.='<City>' . getpost('city') . '</City>';
				if(getpost('country')=='US' || getpost('country')=='CA')
					$sXML.='<StateProvinceCode>' . getpost('usstate') . '</StateProvinceCode>';
				else
					$sXML.='<StateProvinceCode>XX</StateProvinceCode>';
				$sXML.='<PostalCode>' . getpost('postcode') . '</PostalCode><CountryCode>' . getpost('country') . '</CountryCode>' .
					'</Address>' .
					'<PhoneNumber>' . getpost('telephone') . '</PhoneNumber>' .
					'<EmailAddress>' . getpost('email') . '</EmailAddress>' .
					'<NotificationCode>00</NotificationCode>';
				if(getpost('upsaccount')!=''){
					$sXML.='<ShipperAccount>';
					if(getpost('upsaccount')!='') $sXML.='<AccountNumber>' . getpost('upsaccount') . '</AccountNumber>';
					$sXML.='<PostalCode>' . getpost('postcode') . '</PostalCode>' .
						'<CountryCode>' . getpost('country') . '</CountryCode>';
					if(getpost('invoicenumber')!=''){
						$sXML.='<InvoiceInfo>';
						$sXML.='<InvoiceNumber>' . vrxmlencode(getpost('invoicenumber')) . '</InvoiceNumber>';
						if(getpost('invoicedate')!='') $sXML.='<InvoiceDate>' . vrxmlencode(getpost('invoicedate')) . '</InvoiceDate>';
						if(getpost('invoicecurrency')!='') $sXML.='<CurrencyCode>' . vrxmlencode(getpost('invoicecurrency')) . '</CurrencyCode>';
						if(getpost('invoiceamount')!='') $sXML.='<InvoiceAmount>' . vrxmlencode(getpost('invoiceamount')) . '</InvoiceAmount>';
						if(getpost('invoicecontrolid')!='') $sXML.='<ControlID>' . vrxmlencode(getpost('invoicecontrolid')) . '</ControlID>';
						$sXML.='</InvoiceInfo>';
					}
					$sXML.='</ShipperAccount>';
				}
				$sXML.='<SuggestUsernameIndicator>Y</SuggestUsernameIndicator>' .
					'</RegisterRequest></soapenv:Body></soapenv:Envelope>';
				if(callcurlfunction($registerurl, $sXML, $xmlres, '', $errormsg, FALSE)){
					$success = ParseUPSLicenseOutput($xmlres, 'reg:RegisterResponse', $theuser, $errormsg);
				}
				$noloops++;
			}
		}
	}
	registrationsuccess();
}elseif(getpost('upsstep')=='3' && getpost('doagree')=='1'){
?>
<script type="text/javascript">
<!--
function checkforamp(checkObj){
  checkStr = checkObj.value;
  for (i = 0;  i < checkStr.length;  i++){
	if (checkStr.charAt(i)=="&"){
	  alert("Please do not use the ampersand \"&\" character in any field.");
	  checkObj.focus();
	  return(false);
	}
  }
  return(true);
}
function formvalidator(theForm)
{
  if(theForm.contact.value==""){
    alert("<?php print jscheck($yyPlsEntr . ' "' . $yyConNam)?>\".");
    theForm.contact.focus();
    return (false);
  }
  if(!checkforamp(theForm.contact)) return(false);
  if(theForm.ctitle.value==""){
    alert("<?php print jscheck($yyPlsEntr . ' "' . $yyTitle)?>\".");
    theForm.ctitle.focus();
    return (false);
  }
  if(!checkforamp(theForm.ctitle)) return(false);
  if(theForm.company.value==""){
    alert("<?php print jscheck($yyPlsEntr . ' "' . $yyComNam)?>\".");
    theForm.company.focus();
    return (false);
  }
  if(!checkforamp(theForm.company)) return(false);
  if(theForm.address.value==""){
    alert("<?php print jscheck($yyPlsEntr . ' "' . $yyStrAdd)?>\".");
    theForm.address.focus();
    return (false);
  }
  if(!checkforamp(theForm.address)) return(false);
  if(theForm.city.value==""){
    alert("<?php print jscheck($yyPlsEntr . ' "' . $yyCity)?>\".");
    theForm.city.focus();
    return (false);
  }
  if(!checkforamp(theForm.city)) return(false);
  var cntry = theForm.country[theForm.country.selectedIndex].value;
  if(cntry=="US" || cntry=="CA"){
	if (theForm.usstate.selectedIndex==0){
      alert("<?php print jscheck($yyPlsSel . ' "' . $yyState)?>\".");
      theForm.usstate.focus();
      return (false);
	}
  }
  if(theForm.country.selectedIndex==0){
    alert("<?php print jscheck($yyPlsSel . ' "' . $yyCountry)?>\".");
    theForm.country.focus();
    return (false);
  }
  if(cntry!='CL' && cntry!='CO' && cntry!='CR' && cntry!='DO' && cntry!='GT' && cntry!='HK' && cntry!='IE' && cntry!='PA'){
	if (theForm.postcode.value==""){
	  alert("<?php print jscheck($yyPlsEntr . ' "' . $yyPCode)?>\".");
	  theForm.postcode.focus();
	  return (false);
	}
  }
  if(!checkforamp(theForm.postcode)) return(false);
  if(theForm.telephone.value==""){
    alert("<?php print jscheck($yyPlsEntr . ' "' . $yyTelep)?>\".");
    theForm.telephone.focus();
    return (false);
  }
  if(theForm.telephone.value.length < 10 || theForm.telephone.value.length > 14){
    alert("<?php print jscheck($yyValTN)?>");
    theForm.telephone.focus();
    return (false);
  }
  var checkOK = "0123456789";
  var checkStr = theForm.telephone.value;
  var allValid = true;
  for (i = 0;  i < checkStr.length;  i++)
  {
    ch = checkStr.charAt(i);
    for (j = 0;  j < checkOK.length;  j++)
      if (ch==checkOK.charAt(j))
        break;
    if (j==checkOK.length)
    {
      allValid = false;
      break;
    }
  }
  if(!allValid)
  {
    alert("<?php print jscheck($yyOnDig . ' "' . $yyTelep)?>\".");
    theForm.telephone.focus();
    return (false);
  }
  if(theForm.websiteurl.value==""){
    alert("<?php print jscheck($yyPlsEntr . ' "' . $yyWebURL)?>\".");
    theForm.websiteurl.focus();
    return (false);
  }
  if(!checkforamp(theForm.contact)) return(false);
  var checkStr = theForm.websiteurl.value;
  var gotDot = false;
  var gotAt = false;
  for (i = 0;  i < checkStr.length;  i++)
  {
    ch = checkStr.charAt(i);
    if (ch=="@") gotAt = true;
	if (ch==".") gotDot = true;
  }
  if(!(gotDot))
  {
    alert("<?php print jscheck($yyValEnt . ' "' . $yyWebURL)?>\".");
    theForm.websiteurl.focus();
    return (false);
  }
  if(theForm.email.value==""){
    alert("<?php print jscheck($yyPlsEntr . ' "' . $yyEmail)?>\".");
    theForm.email.focus();
    return (false);
  }
  var checkStr = theForm.email.value;
  var gotDot = false;
  var gotAt = false;
  for (i = 0;  i < checkStr.length;  i++)
  {
    ch = checkStr.charAt(i);
    if (ch=="@") gotAt = true;
	if (ch==".") gotDot = true;
  }
  if (!(gotDot && gotAt))
  {
    alert("<?php print jscheck($yyValEnt . ' "' . $yyEmail)?>\".");
    theForm.email.focus();
    return (false);
  }
  if(theForm.upsrep[0].checked==false && theForm.upsrep[1].checked==false){
    alert("<?php print jscheck($yyUPSrep)?>");
    return (false);
  }
  return (true);
}
//-->
</script>
	<form method="post" name="licform" action="adminupslicense.php" onsubmit="return formvalidator(this)">
	  <input type="hidden" name="upsstep" value="4" />
	  <input type="hidden" name="countryCode" value="<?php print getpost('countryCode')?>" />
	  <input type="hidden" name="languageCode" value="<?php print getpost('languageCode')?>" />
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr>
				<td rowspan="20" width="70" align="center" valign="top"><img src="../images/upslogo.png" border="0" alt="UPS" /><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;</td>
                <td width="100%" align="center" colspan="2"><strong><?php print $yyUPSWiz?> - <?php print $yyStep?> 2</strong><br />&nbsp;
                </td>
			  </tr>
			  <tr> 
                <td width="40%" align="right"><strong><?php print $yyConNam?> : </strong></td>
				<td width="60%"><input type="text" name="contact" size="30" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $yyTitle?> : </strong></td>
				<td><input type="text" name="ctitle" size="10" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $yyComNam?> : </strong></td>
				<td><input type="text" name="company" size="30" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $yyStrAdd?> : </strong></td>
				<td><input type="text" name="address" size="30" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $yyAddr2?> : </strong></td>
				<td><input type="text" name="address2" size="30" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $yyCity?> : </strong></td>
				<td><input type="text" name="city" size="30" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $yyState?> <?php print $yyUSCan?> : </strong></td>
				<td><select name="usstate" size="1">
<option value=''><?php print $yyOutUS?></option>
<option value='AL'>Alabama</option>
<option value='AK'>Alaska</option>
<option value='AB'>Alberta</option>
<option value='AZ'>Arizona</option>
<option value='AR'>Arkansas</option>
<option value='BC'>British Columbia</option>
<option value='CA'>California</option>
<option value='CO'>Colorado</option>
<option value='CT'>Connecticut</option>
<option value='DE'>Delaware</option>
<option value='DC'>District Of Columbia</option>
<option value='FL'>Florida</option>
<option value='GA'>Georgia</option>
<option value='HI'>Hawaii</option>
<option value='ID'>Idaho</option>
<option value='IL'>Illinois</option>
<option value='IN'>Indiana</option>
<option value='IA'>Iowa</option>
<option value='KS'>Kansas</option>
<option value='KY'>Kentucky</option>
<option value='LA'>Louisiana</option>
<option value='ME'>Maine</option>
<option value='MB'>Manitoba</option>
<option value='MD'>Maryland</option>
<option value='MA'>Massachusetts</option>
<option value='MI'>Michigan</option>
<option value='MN'>Minnesota</option>
<option value='MS'>Mississippi</option>
<option value='MO'>Missouri</option>
<option value='MT'>Montana</option>
<option value='NE'>Nebraska</option>
<option value='NV'>Nevada</option>
<option value='NB'>New Brunswick</option>
<option value='NH'>New Hampshire</option>
<option value='NJ'>New Jersey</option>
<option value='NM'>New Mexico</option>
<option value='NY'>New York</option>
<option value='NF'>Newfoundland</option>
<option value='NC'>North Carolina</option>
<option value='ND'>North Dakota</option>
<option value='NT'>Northwest Territories</option>
<option value='NS'>Nova Scotia</option>
<option value='NU'>Nunavut</option>
<option value='OH'>Ohio</option>
<option value='OK'>Oklahoma</option>
<option value='ON'>Ontario</option>
<option value='OR'>Oregon</option>
<option value='PA'>Pennsylvania</option>
<option value='PE'>Prince Edward Island</option>
<option value='QC'>Quebec</option>
<option value='RI'>Rhode Island</option>
<option value='SK'>Saskatchewan</option>
<option value='SC'>South Carolina</option>
<option value='SD'>South Dakota</option>
<option value='TN'>Tennessee</option>
<option value='TX'>Texas</option>
<option value='UT'>Utah</option>
<option value='VT'>Vermont</option>
<option value='VA'>Virginia</option>
<option value='WA'>Washington</option>
<option value='WV'>West Virginia</option>
<option value='WI'>Wisconsin</option>
<option value='WY'>Wyoming</option>
<option value='YT'>Yukon</option>
</select></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $yyCountry?> : </strong></td>
				<td><select name="country" size="1" onchange="document.getElementById('upslink').href='http://www.ups.com/content/XXXX/en/index.jsx'.replace('XXXX',this[this.selectedIndex].value.toLowerCase())">
<option value=""><?php print $yySelect?></option><?php
$sSQL = 'SELECT countryName,countryCode FROM countries WHERE countryCode IN (' . $allowedcountries . ') ORDER BY countryName';
$result=ect_query($sSQL) or ect_error();
while($rs=ect_fetch_assoc($result)){
	print '<option value="'.$rs['countryCode'].'">'.$rs['countryName'].'</option>'."\r\n";
}
ect_free_result($result);
?>
				</select></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $yyPCode?> : </strong></td>
				<td><input type="text" name="postcode" size="15" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $yyTelep?> : </strong></td>
				<td><input type="text" name="telephone" size="30" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $yyWebURL?> : </strong></td>
				<td><input type="text" name="websiteurl" size="30" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $yyEmail?> : </strong></td>
				<td><input type="text" name="email" size="30" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $yyUPSac?> : </strong></td>
				<td><input type="text" name="upsaccount" size="15" maxlength="6" /></td>
			  </tr>
			  <tr> 
                <td align="center" colspan="2">
					<?php print $yyUPSsr?><br /><input type="radio" name="upsrep" value="yes" /> <strong><?php print $yyYes?></strong> <input type="radio" name="upsrep" value="no" /> <strong><?php print $yyNo?></strong>
				</td>
			  </tr>
<?php	if(FALSE){ ?>
			  <tr> 
                <td align="center" width="100%" colspan="2">If you are already in posession of a My UPS User ID and Password please enter this below. Otherwise just leave blank and one will be created for you.</td>
			  </tr>
			  <tr>
                <td align="right"><strong>My UPS User ID : </strong></td>
				<td><input type="text" name="myupsuser" size="20" /></td>
			  </tr>
			  			  <tr> 
                <td align="right"><strong>My UPS Password : </strong></td>
				<td><input type="text" name="myupspw" size="20" /></td>
			  </tr>
<?php	} ?>
			  <tr>
                <td width="100%" align="center" colspan="2"><br />&nbsp;<input type="submit" name="agree" value="&nbsp;&nbsp;<?php print $yyNext?>&nbsp;&nbsp;" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="button" value="<?php print $yyCancel?>" onclick="window.location='admin.php';" />
                </td>
			  </tr>
			  <tr> 
                <td align="center" colspan="2"><p><span style="font-size:10px"><?php print $yyUPSop?> <a href="http://www.ups.com/content/us/en/index.jsx" target="_blank" id="upslink"><?php print $yyClkHer?></a> <?php print $yyUPScl?><br />
				<?php print $yyUPSMI?> <a href="http://www.ups.com/content/us/en/bussol/browse/cat/developer_kit.html" target="_blank"><?php print $yyClkHer?></a>.<br />
				<?php print $yyUPshp?> <a href="http://www.ups.com/content/us/en/bussol/browse/internet_shipping.html" target="_blank"><?php print $yyClkHer?></a></span></p>
				</td>
			  </tr>
			  <tr> 
                <td colspan="3" width="100%" align="center">
				  <p>&nbsp;</p><p><span style="font-size:10px"><?php print $yyUPStm?></span></p>
                </td>
			  </tr>
            </table>
          </td>
        </tr>
      </table>
	</form>
<?php
}elseif(getpost('upsstep')=="2"){
	$languageCode="EN";
	if($countryCode=="AR" || $countryCode=="ES" || $countryCode=="MX" || $countryCode=="CA" || $countryCode=="DO" || $countryCode=="GT" || $countryCode=="CR" || $countryCode=="CO" || $countryCode=="PA" || $countryCode=="PR" || $countryCode=="CL")
		$languageCode="ES";
	elseif($countryCode=="AT" || $countryCode=="DE")
		$languageCode="DE";
	elseif($countryCode=="PT" || $countryCode=="BR")
		$languageCode="PT";
	elseif($countryCode=="FR" || $countryCode=="CH" || $countryCode=="BE")
		$languageCode="FR";
	elseif($countryCode=="CN" || $countryCode=="HK")
		$languageCode="ZH";
	elseif($countryCode=="DK")
		$languageCode="DA";
	elseif($countryCode=="FI")
		$languageCode="FI";
	elseif($countryCode=="GR")
		$languageCode="EL";
	elseif($countryCode=="IN")
		$languageCode="GU";
	elseif($countryCode=="IL")
		$languageCode="IW";
	elseif($countryCode=="IT")
		$languageCode="IT";
	elseif($countryCode=="JP")
		$languageCode="JA";
	elseif($countryCode=="MY")
		$languageCode="MS";
	elseif($countryCode=="NL")
		$languageCode="NL";
	elseif($countryCode=="NO")
		$languageCode="NO";
	elseif($countryCode=="KR")
		$languageCode="KO";
	elseif($countryCode=="SE")
		$languageCode="SV";
	elseif($countryCode=="TH")
		$languageCode="TH";
	$sXML = '<?xml version="1.0" encoding="ISO-8859-1"?>';
	$sXML.="<AccessLicenseAgreementRequest><Request><RequestOption>AllTools</RequestOption><TransactionReference><CustomerContext>Ecomm Plus UPS License</CustomerContext><XpciVersion>1.0001</XpciVersion></TransactionReference>";
	$sXML.="<RequestAction>AccessLicense</RequestAction></Request><DeveloperLicenseNumber>8B8CC9F752512834</DeveloperLicenseNumber>";
	$sXML.="<AccessLicenseProfile><CountryCode>" . $countryCode . "</CountryCode><LanguageCode>" . $languageCode . "</LanguageCode></AccessLicenseProfile>";
	$sXML.="<OnLineTool><ToolID>RateXML</ToolID><ToolVersion>1.0</ToolVersion></OnLineTool><OnLineTool><ToolID>TrackXML</ToolID><ToolVersion>1.0</ToolVersion></OnLineTool></AccessLicenseAgreementRequest>";

	// print str_replace("<","<br />&lt;",$sXML) . "<HR>\n";

	if(@$pathtocurl!=''){
		exec($pathtocurl . ' --data-binary ' . escapeshellarg($sXML) . ' https://www.ups.com/ups.app/xml/License', $res, $retvar);
		$res = implode("\n",$res);
		$success = ParseUPSLicenseOutput($res, "AccessLicenseAgreementResponse", $lictext, $errormsg);
	}else{
		if(!$ch = curl_init()) {
			$success = false;
			$errormsg = "cURL package not installed in PHP";
		}else{
			curl_setopt($ch, CURLOPT_URL,'https://www.ups.com/ups.app/xml/License'); 
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $sXML);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if(@$curlproxy!=''){
				curl_setopt($ch, CURLOPT_PROXY, $curlproxy);
			}
			$res = curl_exec($ch);
			if(curl_error($ch)!=''){
				$errormsg = 'Error connecting to UPS server: ' . curl_error($ch) . '<br />';
				$success=FALSE;
			}
			curl_close($ch);
			// print str_replace("<","<br />&lt;",$res) . "<br />\n";
			if($success) $success = ParseUPSLicenseOutput($res, "AccessLicenseAgreementResponse", $lictext, $errormsg);
		}
	}
?>
<script type="text/javascript">
<!--
var origlictext="";
function printlicense(){
	var prnttext = '<html><body>\n';
	if(origlictext != document.licform.lictext.value){
		alert("It appears that the license text has been modified. Cannot print license.");
		return;
	}
	prnttext+=document.licform.lictext.value.replace(/\n|\r\n/g,'<br />');
	prnttext+='</body></html>';
	var newwin = window.open("","printlicense",'menubar=no, scrollbars=yes, width=500, height=400, directories=no,location=no,resizable=yes,status=no,toolbar=no');
	newwin.document.open();
	newwin.document.write(prnttext);
	newwin.document.close();
	newwin.print();
}
function checkaccept(theForm){
  if(origlictext != document.licform.lictext.value){
	alert("It appears that the license text has been modified. Cannot proceed.");
	return (false);
  }
  if (theForm.doagree[0].checked==false){
    alert("<?php print jscheck($yyUPSLi4)?>");
    return (false);
  }
  return (true);
}
var hasscrolled=false;
function checkscroll(tarea){
	if(tarea.offsetHeight+tarea.scrollTop+1>=tarea.scrollHeight){
		hasscrolled=true;
	}
}
function checkhasscrolled(radbut){
	if(! hasscrolled){
		radbut.checked=false;
		alert("You must scroll through the whole license agreement before you can select this option.");
	}
}
//-->
</script>
	<form method="post" name="licform" action="adminupslicense.php" onsubmit="return checkaccept(this)">
	  <input type="hidden" name="upsstep" value="3" />
	  <input type="hidden" name="countryCode" value="<?php print $countryCode?>" />
	  <input type="hidden" name="languageCode" value="<?php print $languageCode?>" />
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
            <table width="100%" border="0" cellspacing="2" cellpadding="0">
			  <tr>
                <td width="100%" align="center"><img src="../images/upslogo.png" border="0" align="middle" alt="UPS" />&nbsp;&nbsp;<strong><?php print $yyUPSWiz?> - <?php print $yyStep?> 1</strong><br />&nbsp;
                </td>
			  </tr>
<?php	if($success){ ?>
			  <tr> 
                <td width="100%" align="center"><textarea cols="80" rows="20" name="lictext" onscroll="checkscroll(this)"><?php print $lictext?></textarea><br /><br />
				<p><?php print $yyUPSTer?></p>
				<p><?php print $yyAgree?> <input type="radio" name="doagree" value="1" onclick="checkhasscrolled(this)" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php print $yyNoAgre?> <input type="radio" name="doagree" value="0" /></p>
				<p>&nbsp;</p>
                </td>
			  </tr>
<script type="text/javascript">
<!--
var origlictext=document.licform.lictext.value;
//-->
</script>
<?php	}else{ ?>
			  <tr> 
                <td width="100%" align="center"><p><?php print $yySorErr?></strong></p>
				<p>&nbsp;</p>
				<p><?php print $errormsg?></p>
				<p>&nbsp;</p>
                </td>
			  </tr>
<?php	} ?>
			  <tr> 
                <td width="100%" align="center"><?php if($success){ ?><input type="button" value="&nbsp;<?php print $yyPrint?>&nbsp;" onclick="printlicense();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="agree" value="&nbsp;&nbsp;<?php print $yyNext?>&nbsp;&nbsp;" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php } ?>
				<input type="button" value="<?php print $yyCancel?>" onclick="window.location='admin.php';" />
                </td>
			  </tr>
			  <tr> 
                <td align="center"><p><span style="font-size:10px"><?php print $yyUPSop?> <a href="http://www.ups.com/content/us/en/index.jsx" target="_blank">click here</a> or call 1-800-PICK-UPS.<br />
				<?php print $yyUPSMI?> <a href="http://www.ups.com/content/us/en/bussol/browse/cat/developer_kit.html" target="_blank"><?php print $yyClkHer?></a>.<br />
				<?php print $yyUPshp?> <a href="http://www.ups.com/content/us/en/bussol/browse/internet_shipping.html" target="_blank"><?php print $yyClkHer?></a>.</span></p>
				</td>
			  </tr>
			  <tr> 
                <td width="100%" align="center">
				  <p>&nbsp;</p><p><span style="font-size:10px"><?php print $yyUPStm?></span></p>
                </td>
			  </tr>
            </table>
          </td>
        </tr>
      </table>
	</form>
<?php
}else{
	$isregistered=FALSE;
	if(trim($upsUser)!='' && trim($upsPw)!='') $isregistered=TRUE; ?>
	<form method="post" id="licform" action="adminupslicense.php">
	  <input type="hidden" name="upsstep" value="2" />
	  <input type="hidden" name="reregister" id="reregister" value="" />
      <table border="0" cellspacing="3" cellpadding="3" width="100%" align="center">	
		  <tr>
			<td rowspan="5" width="70" align="center" valign="top"><img src="../images/upslogo.png" border="0" alt="UPS" /><br />&nbsp;</td>
			<td width="100%" align="center"><strong><?php print $yyUPSWiz?></strong><br />&nbsp;
			</td>
		  </tr>
<?php	if($isregistered){ ?>
		  <tr> 
			<td width="100%"><p>&nbsp;</p></td>
		  </tr>
		  <tr> 
			<td width="100%">You have already successfully completed the UPS licensing and registration wizard. If you would like to re-register then please 
			click the "Re-register" button below. If you would just like to update your UPS account information then please click the "Update Account" button below.
			<p>&nbsp;</p>
			</td>
		  </tr>
		  <tr> 
			<td width="100%" align="center"><input type="submit" name="agree" onclick="document.getElementById('reregister').value='';" value="&nbsp;&nbsp;Re-Register&nbsp;&nbsp;" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="button" value="Update Account" onclick="document.getElementById('reregister').value=1;document.getElementById('licform').submit()" />
			</td>
		  </tr>
<?php	}else{ ?>
		  <tr> 
			<td width="100%"><ul><li><?php print $yyUPSLi1?><br /><br /></li>
			<li><?php print $yyUPSLi2?><br /><br /></li>
			<li><?php print $yyUPSLi3?> <?php print $yyNoCou?> <a href="adminmain.php"><?php print $yyClkHer?></a>.<br /><br /></li>
			<li><?php print $yyUPSMI?> <a href="http://www.ups.com/content/us/en/bussol/browse/cat/developer_kit.html" target="_blank"><?php print $yyClkHer?></a>.<br /><br /></li>
			<li><?php print $yyUPshp?> <a href="http://www.ups.com/content/us/en/bussol/browse/internet_shipping.html" target="_blank"><?php print $yyClkHer?></a>.</li>
			</ul>
			<p>&nbsp;</p>
			</td>
		  </tr>
		  <tr> 
			<td width="100%" align="center"><input type="submit" name="agree" value="&nbsp;&nbsp;<?php print $yyNext?>&nbsp;&nbsp;" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="button" value="<?php print $yyCancel?>" onclick="window.location='admin.php';" />
			</td>
		  </tr>
		  <tr> 
			<td align="center" colspan="2"><p><span style="font-size:10px"><?php print $yyUPSop?> <a href="http://www.ups.com/content/us/en/index.jsx" target="_blank"><?php print $yyClkHer?></a> <?php print $yyUPScl?><br />
			<?php print $yyUPSMI?> <a href="http://www.ups.com/content/us/en/bussol/browse/cat/developer_kit.html" target="_blank"><?php print $yyClkHer?></a>.<br />
			<?php print $yyUPshp?> <a href="http://www.ups.com/content/us/en/bussol/browse/internet_shipping.html" target="_blank"><?php print $yyClkHer?></a>.</span></p>
			</td>
		  </tr>
<?php	} ?>
		  <tr> 
			<td width="100%" align="center">
			  <p>&nbsp;</p><p><span style="font-size:10px"><?php print $yyUPStm?></span></p>
			</td>
		  </tr>
		</table>
	</form>
<?php
}
?>