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
if(getget('act')=='version'){ ?>
	<form method="post" name="licform" action="admin.php">
	  <input type="hidden" name="upsstep" value="5" />
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr>
				<td rowspan="3" width="70" align="center" valign="top"><img src="../images/fedexlogo.png" border="0" alt="FedEx" /><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;</td>
                <td width="100%" align="center"><strong><?php print $yyFdxWiz?> - Updating FedEx® version information.</strong><br />&nbsp;
                </td>
			  </tr>
			  <tr> 
                <td width="100%" align="left">
				  <p>&nbsp;</p>
				  <p>Please wait while we update your FedEx version information.</p>
				  <p>&nbsp;</p>
				  <p>Step 1, getting location id. <span name="step1span" id="step1span"><strong>Please wait!</strong></span></p>
				  <p>&nbsp;</p>
				  <p>Step 2, updating version. <span name="step2span" id="step2span"><strong>Please wait!</strong></span></p>
				  <p>&nbsp;</p>
				  <p align="center" name="donebutton" id="donebutton" style="display:none"><input type="submit" value="<?php print $yyDone?>" /></p>
				  <p>&nbsp;</p>
                </td>
			  </tr>
			  <tr> 
                <td colspan="2" width="100%" align="center">
				  <p><br />&nbsp;</p>
				  <p><span style="font-size:10px"><?php print $fedexcopyright?></span></p>
                </td>
			  </tr>
            </table>
          </td>
        </tr>
      </table>
	</form>
<?php
	flush();
	$sSQL = "SELECT adminVersion,FedexAccountNo,FedexMeter,adminZipCode,countryCode FROM admin INNER JOIN countries ON admin.adminCountry=countries.countryID WHERE adminID=1";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		$version = trim($rs['adminVersion']);
		$fedexacctno = trim($rs['FedexAccountNo']);
		$fedexmeter = trim($rs['FedexMeter']);
		$zipcode = trim($rs['adminZipCode']);
		$countrycode = trim($rs['countryCode']);
	}
	$versionarray = explode(' v', $version);
	$version = $versionarray[1];
	$versionarray = explode('.', $version);
	if((int)$versionarray[0]<10) $version = '0' . $versionarray[0] . $versionarray[1] . '0'; else $version = $versionarray[0] . $versionarray[1] . '0';
	$sXML = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns="http://fedex.com/ws/packagemovementinformationservice/v4">';
	$sXML.="<soapenv:Header/><soapenv:Body><PostalCodeInquiryRequest><WebAuthenticationDetail><CspCredential><Key>mKOUqSP4CS0vxaku</Key><Password>IAA5db3Pmhg3lyWW6naMh4Ss2</Password></CspCredential>";
	$sXML.="<UserCredential><Key>" . $fedexuserkey . "</Key><Password>" . $fedexuserpwd . "</Password></UserCredential>";
	$sXML.="</WebAuthenticationDetail><ClientDetail><AccountNumber>" . $fedexacctno . "</AccountNumber><MeterNumber>" . $fedexmeter . "</MeterNumber><ClientProductId>IBTP</ClientProductId><ClientProductVersion>3272</ClientProductVersion></ClientDetail>";
	$sXML.="<TransactionDetail><CustomerTransactionId>123xyz</CustomerTransactionId></TransactionDetail>";
	$sXML.="<Version><ServiceId>pmis</ServiceId><Major>4</Major><Intermediate>0</Intermediate><Minor>0</Minor></Version>";
	$sXML.="<CarrierCode>FDXE</CarrierCode><PostalCode>" . $zipcode . "</PostalCode><CountryCode>" . $countrycode . "</CountryCode></PostalCodeInquiryRequest></soapenv:Body></soapenv:Envelope>";
	$success = callcurlfunction($fedexurl, $sXML, $xmlres, '', $errormsg, FALSE);
	if($success){
		$xmlDoc = new vrXMLDoc($xmlres);
		$nodeList = $xmlDoc->nodeList->childNodes[0];
		for($i = 0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=='v4:LocationId'){
				$locationid = $nodeList->nodeValue[$i];
			}
		}
		print '<script type="text/javascript">document.getElementById(\'step1span\').innerHTML=\'<strong>Completed!</strong>\';</script>';
		flush();
		$sXML = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v2="http://fedex.com/ws/registration/v2"><soapenv:Header/><soapenv:Body><v2:VersionCaptureRequest><v2:WebAuthenticationDetail>';
		$sXML.="<v2:CspCredential><v2:Key>mKOUqSP4CS0vxaku</v2:Key><v2:Password>IAA5db3Pmhg3lyWW6naMh4Ss2</v2:Password></v2:CspCredential>";
		$sXML.="<v2:UserCredential><v2:Key>" . $fedexuserkey . "</v2:Key><v2:Password>" . $fedexuserpwd . "</v2:Password></v2:UserCredential></v2:WebAuthenticationDetail>";
		$sXML.="<v2:ClientDetail><v2:AccountNumber>" . $fedexacctno . "</v2:AccountNumber><v2:MeterNumber>" . $fedexmeter . "</v2:MeterNumber>";
		$sXML.="<v2:ClientProductId>IBTP</v2:ClientProductId><v2:ClientProductVersion>3272</v2:ClientProductVersion>";
		$sXML.="<v2:Region>US</v2:Region></v2:ClientDetail><v2:TransactionDetail><v2:CustomerTransactionId>Version Capture Request</v2:CustomerTransactionId></v2:TransactionDetail>";
		$sXML.="<v2:Version><v2:ServiceId>fcas</v2:ServiceId><v2:Major>2</v2:Major><v2:Intermediate>1</v2:Intermediate><v2:Minor>0</v2:Minor></v2:Version>";
		$sXML.="<v2:OriginLocationId>" . trim($locationid) . "</v2:OriginLocationId>";
		$sXML.="<v2:VendorProductPlatform>Windows OS</v2:VendorProductPlatform></v2:VersionCaptureRequest></soapenv:Body></soapenv:Envelope>";
		$success = callcurlfunction($fedexurl, $sXML, $xmlres, '', $errormsg, FALSE);
		print '<script type="text/javascript">document.getElementById(\'step2span\').innerHTML=\'<strong>Completed!</strong>\';document.getElementById(\'donebutton\').style.display=\'block\';</script>';
	}else{
		print '<script type="text/javascript">document.getElementById(\'step2span\').innerHTML=\'<strong>Failed!</strong>\';document.getElementById(\'donebutton\').style.display=\'block\';</script>';
	}
}elseif(getpost('upsstep')=="3"){
	splitname(getpost('contact'), $firstname, $lastname);
	$sXML='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v2="http://fedex.com/ws/registration/v2"><soapenv:Header/><soapenv:Body>' .
		"<v2:RegisterWebCspUserRequest><v2:WebAuthenticationDetail><v2:CspCredential>" .
		"<v2:Key>mKOUqSP4CS0vxaku</v2:Key>" .
		"<v2:Password>IAA5db3Pmhg3lyWW6naMh4Ss2</v2:Password>" .
		"</v2:CspCredential></v2:WebAuthenticationDetail>" .
		"<v2:ClientDetail>" .
		"<v2:AccountNumber>" . getpost('fedexaccount') . "</v2:AccountNumber>" .
		"<v2:ClientProductId>IBTP</v2:ClientProductId>" .
		"<v2:ClientProductVersion>3272</v2:ClientProductVersion>" .
		"</v2:ClientDetail>" .
		"<v2:Version><v2:ServiceId>fcas</v2:ServiceId><v2:Major>2</v2:Major><v2:Intermediate>1</v2:Intermediate><v2:Minor>0</v2:Minor></v2:Version>" .
		"<v2:BillingAddress>" .
		"<v2:StreetLines>" . getpost('address') . "</v2:StreetLines>" .
		"<v2:City>" . getpost('city') . "</v2:City>" .
		"<v2:StateOrProvinceCode>" . getpost('usstate') . "</v2:StateOrProvinceCode>" .
		"<v2:PostalCode>" . getpost('postcode') . "</v2:PostalCode>" .
		"<v2:CountryCode>" . getpost('country') . "</v2:CountryCode>" .
		"</v2:BillingAddress>" .
		"<v2:UserContactAndAddress>" .
		"<v2:Contact>" .
		"<v2:PersonName>" .
		"<v2:FirstName>" . $firstname . "</v2:FirstName>" .
		"<v2:LastName>" . $lastname . "</v2:LastName>" .
		"</v2:PersonName>" .
		"<v2:CompanyName>" . getpost('company') . "</v2:CompanyName>" .
		"<v2:PhoneNumber>" . getpost('telephone') . "</v2:PhoneNumber>" .
		"<v2:EMailAddress>" . getpost('email') . "</v2:EMailAddress>" .
		"</v2:Contact>" .
		"<v2:Address>" .
		"<v2:StreetLines>" . getpost('address') . "</v2:StreetLines>" .
		"<v2:City>" . getpost('city') . "</v2:City>" .
		"<v2:StateOrProvinceCode>" . getpost('usstate') . "</v2:StateOrProvinceCode>" .
		"<v2:PostalCode>" . getpost('postcode') . "</v2:PostalCode>" .
		"<v2:CountryCode>" . getpost('country') . "</v2:CountryCode>" .
		"</v2:Address>" .
		"</v2:UserContactAndAddress>" .
		"</v2:RegisterWebCspUserRequest>" .
		"</soapenv:Body>" .
		"</soapenv:Envelope>";
	// print str_replace("<","<br />&lt;",str_replace("</","&lt;/",$sXML)) . "<br />\n";
	$success = callcurlfunction($fedexurl, $sXML, $xmlres, '', $errormsg, FALSE);
	// print str_replace("<","<br />&lt;",str_replace("</","&lt;/",$xmlres)) . "<br />\n";
	if($success){
		$gcXmlDoc = new vrXMLDoc($xmlres);
		$nodeList = $gcXmlDoc->nodeList->childNodes[0];
		$severity = $nodeList->getValueByTagName('v2:Severity');
		$success = $severity=="SUCCESS";
	}
	if($success){
		$userkey=$nodeList->getValueByTagName('v2:Key');
		$userpwd=$nodeList->getValueByTagName('v2:Password');
		if(! ($userkey!="" && $userpwd!="")){
			$success=FALSE;
		}
	}else
		$errormsg=$nodeList->getValueByTagName('v2:Message');

	if($success){
		$sXML='<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">' .
			"<soap:Body>" .
			'<SubscriptionRequest xmlns="http://fedex.com/ws/registration/v2">' .
			"<WebAuthenticationDetail>" .
			"<CspCredential>" .
			"<Key>mKOUqSP4CS0vxaku</Key>" .
			"<Password>IAA5db3Pmhg3lyWW6naMh4Ss2</Password>" .
			"</CspCredential>" .
			"<UserCredential>" .
			"<Key>" . $userkey . "</Key>" .
			"<Password>" . $userpwd . "</Password>" .
			"</UserCredential>" .
			"</WebAuthenticationDetail>" .
			"<ClientDetail>" .
			"<AccountNumber>" . getpost('fedexaccount') . "</AccountNumber>" .
			"<MeterNumber/>" .
			"<ClientProductId>IBTP</ClientProductId><ClientProductVersion>3272</ClientProductVersion>" .
			"</ClientDetail>" .
			"<Version><ServiceId>fcas</ServiceId><Major>2</Major><Intermediate>1</Intermediate><Minor>0</Minor></Version>" .
			"<CspSolutionId>100</CspSolutionId>" .
			"<CspType>CERTIFIED_SOLUTION_PROVIDER</CspType>" .
			"<Subscriber>" .
			"<AccountNumber>" . getpost('fedexaccount') . "</AccountNumber>" .
			"<Contact>" .
			"<PersonName>" . getpost('contact') . "</PersonName>" .
			"<CompanyName/>" .
			"<PhoneNumber>" . getpost('telephone') . "</PhoneNumber>" .
			"<FaxNumber/>" .
			"<EMailAddress>" . getpost('email') . "</EMailAddress>" .
			"</Contact><Address>" .
			"<StreetLines>" . getpost('address') . "</StreetLines>" .
			"<City>" . getpost('city') . "</City>" .
			"<StateOrProvinceCode>" .trim( getpost('usstate')) . "</StateOrProvinceCode>" .
			"<PostalCode>" . getpost('postcode') . "</PostalCode>" .
			"<CountryCode>" . getpost('country') . "</CountryCode>" .
			"</Address></Subscriber>" .
			"<AccountShippingAddress>" .
			"<StreetLines>" . getpost('address') . "</StreetLines>" .
			"<City>" . getpost('city') . "</City>" .
			"<StateOrProvinceCode>" . getpost('usstate') . "</StateOrProvinceCode>" .
			"<PostalCode>" . getpost('postcode') . "</PostalCode>" .
			"<CountryCode>" . getpost('country') . "</CountryCode>" .
			"</AccountShippingAddress>" .
			"</SubscriptionRequest></soap:Body></soap:Envelope>";

		$success = callcurlfunction($fedexurl, $sXML, $xmlres, '', $errormsg, FALSE);
		$errormsg="Unknown Error";
	
		if($success){
			$gcXmlDoc = new vrXMLDoc($xmlres);
			$nodeList = $gcXmlDoc->nodeList->childNodes[0];
			$severity = $nodeList->getValueByTagName('v2:Severity');
			$success = $severity=="SUCCESS";
		}
		if($success){
			$fedexmeter=$nodeList->getValueByTagName('v2:MeterNumber');
			if($fedexmeter=='') $success=FALSE;
		}else{
			$errormsg=$nodeList->getValueByTagName('v2:Message');
		}
	}
?>
	<form method="post" name="licform" action="admin.php">
	  <input type="hidden" name="upsstep" value="5" />
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr>
				<td rowspan="3" width="70" align="center" valign="top"><img src="../images/fedexlogo.png" border="0" alt="FedEx" /><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;</td>
                <td width="100%" align="center"><strong><?php print $yyFdxWiz?> - <?php if($success) print $yyRegSucc; else print $yyError; ?></strong><br />&nbsp;
                </td>
			  </tr>
<?php
	if($success){
		$sSQL = "UPDATE admin SET FedexAccountNo='" . getpost('fedexaccount') . "',FedexMeter='" . $fedexmeter . "',FedexUserKey='" . $userkey . "',FedexUserPwd='" . $userpwd . "'";
		ect_query($sSQL) or ect_error();
?>
			  <tr> 
                <td width="100%" align="left">
				  <p><strong><?php print $yyRegSucc?> !</strong></p>
				  <p>Thank you for registering.</p>
				  <p>To learn more about FedEx&reg; shipping services please go to <a href="http://www.fedex.com" target="_blank">fedex.com</a>.</p>
				  <p>To begin using FedEx shipping calculations please don't forget to select FedEx Shipping from the <strong>Shipping Type</strong> dropdown in the page <a href="adminmain.php"><?php print $yyAdmMai?></a>.</p>
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
				  <p><br />&nbsp;</p>
				  <p><span style="font-size:10px"><?php print $fedexcopyright?></span></p>
                </td>
			  </tr>
            </table>
          </td>
        </tr>
      </table>
	</form>
<?php
}elseif(getpost('upsstep')=="2"){
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
  if (theForm.postcode.value==""){
	alert("<?php print jscheck($yyPlsEntr . ' "' . $yyPCode)?>\".");
	theForm.postcode.focus();
	return (false);
  }
  if(!checkforamp(theForm.postcode)) return(false);
  if(theForm.telephone.value==""){
    alert("<?php print jscheck($yyPlsEntr . ' "' . $yyTelep)?>\".");
    theForm.telephone.focus();
    return (false);
  }
  if(theForm.telephone.value.length < 6 || theForm.telephone.value.length > 16){
    alert("<?php print jscheck($yyValTN)?>");
    theForm.telephone.focus();
    return (false);
  }
  var checkOK = "0123456789";
  var checkStr = theForm.telephone.value;
  var allValid = true;
  for (i = 0;  i < checkStr.length;  i++){
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
  if(!allValid){
    alert("<?php print jscheck($yyOnDig . ' "' . $yyTelep)?>\".");
    theForm.telephone.focus();
    return (false);
  }
  if (theForm.email.value==""){
	alert("<?php print jscheck($yyPlsEntr . ' "' . $yyEmail)?>\".");
	theForm.email.focus();
	return (false);
  }
  if(!checkforamp(theForm.fedexaccount)) return(false);
  if(theForm.fedexaccount.value==""){
    alert("<?php print jscheck($yyPlsEntr)?> \"FedEx Account Number\".");
    theForm.fedexaccount.focus();
    return (false);
  }
  var checkOK = "0123456789";
  var checkStr = theForm.fedexaccount.value;
  var allValid = true;
  for (i = 0;  i < checkStr.length;  i++){
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
  if(!allValid){
    alert("<?php print jscheck($yyOnDig)?> \"FedEx Account Number\".");
    theForm.fedexaccount.focus();
    return (false);
  }
  return (true);
}
//-->
</script>
	<form method="post" name="licform" action="adminfedexlicense.php" onsubmit="return formvalidator(this)">
	  <input type="hidden" name="upsstep" value="3" />
	  <input type="hidden" name="countryCode" value="<?php print getpost('countryCode')?>" />
	  <input type="hidden" name="languageCode" value="<?php print getpost('languageCode')?>" />
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr>
				<td rowspan="18" width="70" align="center" valign="top"><img src="../images/fedexlogo.png" border="0" alt="FedEx" /><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;</td>
                <td width="100%" align="center" colspan="2"><strong><?php print $yyFdxWiz?></strong><br />&nbsp;
                </td>
			  </tr>
			  <tr> 
                <td width="40%" align="right"><strong><?php print $redasterix.$yyConNam?> : </strong></td>
				<td width="60%"><input type="text" name="contact" size="30" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $yyComNam?> : </strong></td>
				<td><input type="text" name="company" size="15" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong>Department : </strong></td>
				<td><input type="text" name="department" size="10" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $redasterix.$yyStrAdd?> : </strong></td>
				<td><input type="text" name="address" size="30" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $yyAddr2?> : </strong></td>
				<td><input type="text" name="address2" size="30" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $redasterix.$yyCity?> : </strong></td>
				<td><input type="text" name="city" size="30" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $redasterix.$yyState?> <?php print $yyUSCan?> : </strong></td>
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
                <td align="right"><strong><?php print $redasterix.$yyCountry?> : </strong></td>
				<td><select name="country" size="1">
<option value=''><?php print $yySelect?></option>
<option value='CA'>Canada</option>
<option value='US'>United States</option>
				</select></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $redasterix.$yyPCode?> : </strong></td>
				<td><input type="text" name="postcode" size="15" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $redasterix.$yyTelep?> : </strong></td>
				<td><input type="text" name="telephone" size="30" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong>Pager Number : </strong></td>
				<td><input type="text" name="pager" size="30" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong>Fax Number : </strong></td>
				<td><input type="text" name="fax" size="30" /></td>
			  </tr>
			  <tr> 
                <td align="right"><strong><?php print $redasterix . $yyEmail?> : </strong></td>
				<td><input type="text" name="email" size="30" /></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $redasterix?>FedEx Account Number : </strong></td>
				<td><input type="text" name="fedexaccount" size="30" /></td>
			  </tr>
			  <tr>
                <td width="100%" align="center" colspan="2"><br />&nbsp;<input type="submit" name="agree" value="&nbsp;&nbsp;<?php print $yyNext?>&nbsp;&nbsp;" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="button" value="<?php print $yyCancel?>" onclick="window.location='admin.php';" />

                </td>
			  </tr>
			  <tr> 
                <td colspan="2" width="100%" align="center">
				  <p><br />&nbsp;</p>
				  <p><span style="font-size:10px"><?php print $fedexcopyright?></span></p>
                </td>
			  </tr>
            </table>
          </td>
        </tr>
      </table>
	</form>
<?php
}else{ ?>
	<form method="post" action="adminfedexlicense.php">
	  <input type="hidden" name="upsstep" value="2" />
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr>
				<td rowspan="5" width="70" align="center" valign="top"><img src="../images/fedexlogo.png" border="0" alt="FedEx" /><br />&nbsp;</td>
                <td width="100%" align="center"><strong><?php print $yyFdxWiz?></strong><br />&nbsp;
                </td>
			  </tr>
<?php	$isregistered=FALSE;
		$sSQL = "SELECT FedexAccountNo,FedexMeter FROM admin WHERE adminID=1";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			if(trim($rs['FedexAccountNo'])!='' && trim($rs['FedexMeter'])!='') $isregistered=TRUE;
		}
		ect_free_result($result);
		if($isregistered){ ?>
			  <tr> 
                <td width="100%">You have already successfully completed the FedEx licensing and registration wizard. If you would like to re-register then please 
				click the "Re-register" button below. If you would just like to update your Ecommerce Plus version information with 
				FedEx then please click the "Update Version" button below.
				<p>&nbsp;</p>
                </td>
			  </tr>
			  <tr> 
                <td width="100%" align="center"><input type="submit" name="agree" value="&nbsp;&nbsp;Re-Register&nbsp;&nbsp;" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="button" value="Update Version" onclick="window.location='adminfedexlicense.php?act=version';" />
                </td>
			  </tr>
<?php	}else{ ?>
			  <tr> 
                <td width="100%"><ul><li>This wizard will assist you in completing the necessary licensing and registration requirements to activate and use the FedEx&reg; Rates and Tracking services from your Ecommerce Plus Template.<br /><br /></li>
				<li>If you do not wish to use any of the functions that utilize the FedEx Rates and Tracking services, click the Cancel button and those functions will not be enabled. If, at a later time, you wish to use these services, return to this section and complete the FedEx licensing and registration process.<br /><br /></li>
				<li>For more information about FedEx services, please <a href="http://www.fedex.com" target="_blank"><?php print $yyClkHer?></a>.<br /><br /></li>
				</ul>
				<p>&nbsp;</p>
                </td>
			  </tr>
			  <tr> 
                <td width="100%" align="center"><input type="submit" name="agree" value="&nbsp;&nbsp;<?php print $yyNext?>&nbsp;&nbsp;" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="button" value="<?php print $yyCancel?>" onclick="window.location='admin.php';" />
                </td>
			  </tr>
<?php	} ?>
			  <tr> 
                <td align="center" colspan="2"><p><span style="font-size:10px"><br />To open a FedEx account, please <a href="https://www.fedex.com/us/OADR/index.html?link=4" target="_blank"><strong><?php print $yyClkHer?></strong></a><br /> </span></p></td>
			  </tr>
			  <tr> 
                <td width="100%" align="center">
				  <p><br />&nbsp;</p>
				  <p><span style="font-size:10px"><?php print $fedexcopyright?></span></p>
                </td>
			  </tr>
            </table>
          </td>
        </tr>
      </table>
	</form>
<?php
}
?>