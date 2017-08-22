<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
//Build: 6.5.4.002
$GLOBALS['incfunctionsdefined']=TRUE;
$GLOBALS['defimagejs']='';
@set_magic_quotes_runtime(0);
@ini_set('display_errors','On');
@error_reporting(E_ALL^(@$GLOBALS['debugmode']||@$GLOBALS['showwarnings']?0:E_NOTICE));
$magicq=(get_magic_quotes_gpc()==1);
if(@$GLOBALS['usecsslayout']){ $GLOBALS['nomarkup']=TRUE; $GLOBALS['useproductbodyformat']=2; $GLOBALS['usesearchbodyformat']=2; $GLOBALS['usedetailbodyformat']=4; }
if(@$GLOBALS['giftcertificateid']=='') $GLOBALS['giftcertificateid']='giftcertificate';
if(@$GLOBALS['donationid']=='') $GLOBALS['donationid']='donation';
if(@$GLOBALS['giftwrappingid']=='') $GLOBALS['giftwrappingid']='giftwrapping';
if(@$GLOBALS['mobilebrowser']!=TRUE) $GLOBALS['mobilebrowser']=detectmobilebrowser();
if($GLOBALS['mobilebrowser']){ $GLOBALS['inlinecheckout']=TRUE; $GLOBALS['hideshipaddress']=TRUE; $GLOBALS['usehardaddtocart']=TRUE; $GLOBALS['disableupdatechecker']=TRUE; if(@$GLOBALS['mobilebrowsercolumns']!='') $GLOBALS['productcolumns']=$GLOBALS['mobilebrowsercolumns']; }
if(@$GLOBALS['adminencoding']=='') $GLOBALS['adminencoding']='iso-8859-1';
if(@$GLOBALS['emailencoding']=='') $GLOBALS['emailencoding']=$GLOBALS['adminencoding'];
if(@$_SESSION['languageid']!='') $GLOBALS['languageid']=$_SESSION['languageid']; elseif(@$GLOBALS['languageid']=='') $GLOBALS['languageid']=1;
if(@$GLOBALS['emailcr']=='')$GLOBALS['emailcr']="\r\n";
if(@$GLOBALS['htmlemails']==TRUE) $GLOBALS['emlNl']='<br />'; else $GLOBALS['emlNl']=$GLOBALS['emailcr'];
if(@$GLOBALS['nomarkup']==TRUE){
	$GLOBALS['sstrong']=$GLOBALS['estrong']='';
}else{
	$GLOBALS['sstrong']='<strong>';
	$GLOBALS['estrong']='</strong>';
}
if(@$GLOBALS['customeraccounturl']=='') $GLOBALS['customeraccounturl']='clientlogin.php';
if(@$GLOBALS['fedextestmode']) $GLOBALS['fedexurl']='https://wsbeta.fedex.com:443/web-services'; else $GLOBALS['fedexurl']='https://ws.fedex.com:443/web-services';
if(@$GLOBALS['loyaltypointvalue']=='') $GLOBALS['loyaltypointvalue']=0.0001;
if(@$GLOBALS['detlinkspacechar']=='') $GLOBALS['detlinkspacechar']=' ';
if(@$GLOBALS['showtaxinclusive']===TRUE) $GLOBALS['showtaxinclusive']=1;
if(!is_numeric(@$GLOBALS['showtaxinclusive'])) $GLOBALS['showtaxinclusive']=0;
if(@$GLOBALS['nopriceanywhere']==TRUE) $GLOBALS['noprice']=TRUE;
$GLOBALS['redasterix']='<span style="color:#FF0000">*</span>';
$GLOBALS['redstar']='<span class="redstar" style="color:#FF1010">*</span>';
$GLOBALS['fedexcopyright']='FedEx service marks are owned by Federal Express Corporation and are used by permission.';
if(@$GLOBALS['righttoleft']==TRUE){ $GLOBALS['tright']='left'; $GLOBALS['tleft']='right'; }else{ $GLOBALS['tright']='right'; $GLOBALS['tleft']='left'; }
$GLOBALS['txtcollen']=1024;
$path_parts=pathinfo(@$_SERVER['PHP_SELF']);
if($path_parts['dirname']=='/'||$path_parts['dirname']=='\\')$path_parts['dirname']='';
@$GLOBALS['pathtohere']=$path_parts['dirname'].'/';
if(@$GLOBALS['seocaturlpattern']=='') $GLOBALS['seocaturlpattern']='/category/%s';
if(@$GLOBALS['seoprodurlpattern']=='') $GLOBALS['seoprodurlpattern']='/products/%s';
if(@$GLOBALS['seomanufacturerpattern']=='') $GLOBALS['seomanufacturerpattern']='/manufacturer/%s';
if(@$GLOBALS['orhomeurl']!='') $GLOBALS['xxHomeURL']=$GLOBALS['orhomeurl']; elseif(@$GLOBALS['seocategoryurls']) $GLOBALS['xxHomeURL']=str_replace('%s','',$GLOBALS['seocaturlpattern']);
if(@$GLOBALS['pathtossl']!=''&&substr(@$GLOBALS['pathtossl'],-1)!='/')$GLOBALS['pathtossl'].='/';
$GLOBALS['REMOTE_ADDR']=trim(str_replace("'",'',substr(@$_SERVER['HTTP_CF_CONNECTING_IP']!=''?@$_SERVER['HTTP_CF_CONNECTING_IP']:@$_SERVER['REMOTE_ADDR'],0,48)));
if($GLOBALS['REMOTE_ADDR']=='::1')$GLOBALS['REMOTE_ADDR']='127.0.0.1';
if(@$GLOBALS['dateformatstr']=='') $GLOBALS['dateformatstr']='m/d/Y';
function getadminsettings(){
	if(! @$GLOBALS['alreadygotadmin']){
		$sSQL='SELECT adminEmail,adminEmailConfirm,adminProdsPerPage,adminStoreURL,adminHandling,adminHandlingPercent,adminPacking,adminDelCC,adminUSZones,adminStockManage,adminShipping,adminIntShipping,adminCanPostUser,adminCanPostLogin,adminCanPostPass,adminZipCode,adminUnits,adminUSPSUser,smartPostHub,adminUSPSpw,adminUPSUser,adminUPSpw,adminUPSAccess,adminUPSAccount,adminUPSNegotiated,FedexAccountNo,FedexMeter,FedexUserKey,FedexUserPwd,DHLSiteID,DHLSitePW,DHLAccountNo,adminlanguages,adminlangsettings,currRate1,currSymbol1,currRate2,currSymbol2,currRate3,currSymbol3,currConvUser,currConvPw,currLastUpdate,adminSecret,countryLCID,countryCurrency,countryNumCurrency,countryName,countryCode,countryID,countryTax,cardinalProcessor,cardinalMerchant,cardinalPwd,catalogRoot,adminAltRates,prodFilter,prodFilterText,prodFilterText2,prodFilterText3,prodFilterOrder,sortOrder,sortOptions,storelang FROM admin LEFT JOIN countries ON admin.adminCountry=countries.countryID WHERE adminID=1';
		$result=ect_query($sSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		$GLOBALS['splitUSZones']=((int)$rs['adminUSZones']==1);
		$GLOBALS['adminLocale']=$rs['countryLCID'];
		$GLOBALS['countryCurrency']=$rs['countryCurrency'];
		$GLOBALS['countryNumCurrency']=$rs['countryNumCurrency'];
		if(@$GLOBALS['orcurrencyisosymbol']!='') $GLOBALS['countryCurrency']=$GLOBALS['orcurrencyisosymbol'];
		$GLOBALS['useEuro']=($GLOBALS['countryCurrency']=='EUR');
		$GLOBALS['storeurl']=$rs['adminStoreURL'];
		$GLOBALS['useStockManagement']=($rs['adminStockManage']!=0);
		$GLOBALS['adminProdsPerPage']=$rs['adminProdsPerPage'];
		$GLOBALS['countryTax']=(double)$rs['countryTax'];
		$GLOBALS['countryTaxRate']=(double)$rs['countryTax'];
		$GLOBALS['delccafter']=(int)$rs['adminDelCC'];
		$GLOBALS['handling']=(double)$rs['adminHandling'];
		$GLOBALS['handlingchargepercent']=(double)$rs['adminHandlingPercent'];
		$GLOBALS['adminCanPostUser']=trim($rs['adminCanPostUser']);
		$GLOBALS['adminCanPostLogin']=trim($rs['adminCanPostLogin']);
		$GLOBALS['adminCanPostPass']=trim($rs['adminCanPostPass']);
		$GLOBALS['packtogether']=((int)$rs['adminPacking']==1);
		$GLOBALS['origZip']=$rs['adminZipCode'];
		$GLOBALS['adminShipping']=$GLOBALS['shipType']=(int)$rs['adminShipping'];
		$GLOBALS['adminIntShipping']=(int)$rs['adminIntShipping'];
		$GLOBALS['origCountry']=$rs['countryName'];
		$GLOBALS['origCountryCode']=$rs['countryCode'];
		$GLOBALS['origCountryID']=$rs['countryID'];
		$GLOBALS['uspsUser']=$rs['adminUSPSUser'];
		$GLOBALS['uspsPw']=$rs['adminUSPSpw'];
		$GLOBALS['upsUser']=upsdecode($rs['adminUPSUser'], '');
		$GLOBALS['upsPw']=upsdecode($rs['adminUPSpw'], '');
		$GLOBALS['smartPostHub']=$rs['smartPostHub'];
		$GLOBALS['upsAccess']=$rs['adminUPSAccess'];
		$GLOBALS['upsAccount']=$rs['adminUPSAccount'];
		$GLOBALS['upsnegdrates']=$rs['adminUPSNegotiated'];
		$GLOBALS['fedexaccount']=$rs['FedexAccountNo'];
		$GLOBALS['fedexmeter']=$rs['FedexMeter'];
		$GLOBALS['fedexuserkey']=$rs['FedexUserKey'];
		$GLOBALS['fedexuserpwd']=$rs['FedexUserPwd'];
		$GLOBALS['DHLSiteID']=$rs['DHLSiteID'];
		$GLOBALS['DHLSitePW']=$rs['DHLSitePW'];
		$GLOBALS['DHLAccountNo']=$rs['DHLAccountNo'];
		$GLOBALS['adminUnits']=(int)$rs['adminUnits'];
		$GLOBALS['emailAddr']=$rs['adminEmail'];
		$GLOBALS['allStoreEmails']=$rs['adminEmail'];
		$GLOBALS['sendEmail']=($rs['adminEmailConfirm']&1)==1;
		$GLOBALS['adminEmailConfirm']=$rs['adminEmailConfirm'];
		$GLOBALS['adminTweaks']=0;
		$GLOBALS['adminlanguages']=(int)$rs['adminlanguages'];
		$storelangarr=explode('|',trim($rs['storelang']));
		$GLOBALS['storelang']=@$storelangarr[$GLOBALS['languageid']-1];
		$GLOBALS['adminlangsettings']=(int)$rs['adminlangsettings'];
		$GLOBALS['currRate1']=(double)$rs['currRate1'];
		$GLOBALS['currSymbol1']=trim($rs['currSymbol1']);
		$GLOBALS['currRate2']=(double)$rs['currRate2'];
		$GLOBALS['currSymbol2']=trim($rs['currSymbol2']);
		$GLOBALS['currRate3']=(double)$rs['currRate3'];
		$GLOBALS['currSymbol3']=trim($rs['currSymbol3']);
		$GLOBALS['currConvUser']=$rs['currConvUser'];
		$GLOBALS['currConvPw']=$rs['currConvPw'];
		$GLOBALS['currLastUpdate']=$rs['currLastUpdate'];
		$GLOBALS['adminSecret']=$rs['adminSecret'];
		$GLOBALS['cardinalprocessor']=$rs['cardinalProcessor'];
		$GLOBALS['cardinalmerchant']=$rs['cardinalMerchant'];
		$GLOBALS['cardinalpwd']=$rs['cardinalPwd'];
		$GLOBALS['catalogroot']=$rs['catalogRoot'];
		$GLOBALS['adminAltRates']=$rs['adminAltRates'];
		$GLOBALS['dosortby']=$rs['sortOrder'];
		$GLOBALS['sortoptions']=$rs['sortOptions'];
		$GLOBALS['prodfilter']=$rs['prodFilter'];
		$GLOBALS['prodfilterorder']=$rs['prodFilterOrder'];
		$GLOBALS['prodfiltertext']=$rs[getlangid('prodFilterText',262144)];
		ect_free_result($result);
	}
	// Overrides
	if(@$GLOBALS['orstoreurl']!='') $GLOBALS['storeurl']=$GLOBALS['orstoreurl'];
	if((substr(strtolower($GLOBALS['storeurl']),0,7)!='http://') && (substr(strtolower($GLOBALS['storeurl']),0,8)!='https://') && $GLOBALS['storeurl']!='')
		$GLOBALS['storeurl']='http://' . $storeurl;
	if(substr($GLOBALS['storeurl'],-1)!='/' && $GLOBALS['storeurl']!='') $GLOBALS['storeurl'].='/';
	if(@$GLOBALS['oremailaddr']!='') $GLOBALS['allStoreEmails']=$GLOBALS['oremailaddr'];
	$allemailsarray=explode(',',$GLOBALS['allStoreEmails']);
	$GLOBALS['emailAddr']=$allemailsarray[0];
	// Language
	if($GLOBALS['origCountryCode']=='GB' || $GLOBALS['origCountryCode']=='IE'){
		$GLOBALS['ssIncTax']=str_replace('Tax','VAT',@$GLOBALS['ssIncTax']);
		$GLOBALS['xxCntTax']='VAT';
	}elseif($GLOBALS['origCountryCode']=='AU' || $GLOBALS['origCountryCode']=='CA'){
		$GLOBALS['xxStaTax']='PST';
		$GLOBALS['xxCntTax']='GST';
		if($GLOBALS['storelang']=='fr' && $GLOBALS['origCountryCode']=='CA'){
			$GLOBALS['xxStaTax']='TVQ';
			$GLOBALS['xxCntTax']='TPS';
		}
	}
	if($GLOBALS['origCountryCode']=='CA' && $GLOBALS['storelang']=='')
		$GLOBALS['xxPostco']='Postal Code';
	return(TRUE);
}
function encodeimage($imurl){
	$imurl=str_replace('\\','/',$imurl);
	if($imurl=='prodimages/')$imurl='';
	$imurl=str_replace(array('*','|','<','>','?'),array('%2A','%7C','%3C','%3E','%3F'),$imurl);
	if(@$GLOBALS['noencodeimages']!=TRUE) $imurl=str_replace(array('prodimages/','.gif','.jpg','.png'),array('|','<','>','?'),$imurl);
	return(str_replace("'","\'",$imurl));
}
function replaceaccentsansi($surl){
	return(str_replace(array('�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�'),array('a','a','a','c','e','e','e','e','i','i','i','o','o','o','o','u','u','u','u','n','',''),$surl));
}
function replaceaccentsutf($surl){
	return(str_replace(array('à','â','á','ç','è','ê','é','ë','î','ï','í','ò','ô','ó','ö','ù','û','ú','ü','ñ','®','™'),array('a','a','a','c','e','e','e','e','i','i','i','o','o','o','o','u','u','u','u','n','',''),$surl));
}
function replaceaccents($surl){
	global $adminencoding;
	if(strtolower($adminencoding)=='iso-8859-1') return(replaceaccentsansi($surl)); else if(strtolower($adminencoding)=="utf-8") return(replaceaccentsutf($surl)); else return($surl);
}
function cleanforurl($surl){
global $urlfillerchar;
if(! @isset($urlfillerchar)) $urlfillerchar='_';
$surl=str_replace(' ',$urlfillerchar,strtolower(strip_tags($surl)));
$surl=replaceaccents($surl);
return(preg_replace('/[^a-z\\'.$urlfillerchar.'0-9]/','',$surl));
}
function vrxmlencode($xmlstr){
	return str_replace(array('&','"',"'",'<','>','�'),array('&amp;','&quot;','&apos;','&lt;','&gt;','&apos;'),$xmlstr);
}
function xmlencodecharref($xmlstr){
	$xmlstr=str_replace(array('&reg;','&','<','>','�'),array('','&#x26;','&#x3c;','&#x3e;',''),$xmlstr);
	$tmp_str='';
	for($i=0; $i < strlen($xmlstr); $i++){
		$ch_code=ord(substr($xmlstr,$i,1));
		if($ch_code<=130) $tmp_str.=substr($xmlstr,$i,1);
	}
	return($tmp_str);
}
function getlangid($col, $bfield){
	global $languageid, $adminlangsettings;
	if(@$languageid==1){
		return($col);
	}else{
		if(($adminlangsettings & $bfield)!=$bfield) return($col);
	}
	return($col . $languageid);
}
function parsedate($tdat){
	global $admindateformat;
	if($admindateformat==0)
		list($year, $month, $day)=sscanf($tdat, '%d-%d-%d');
	elseif($admindateformat==1)
		list($month, $day, $year)=sscanf($tdat, '%d/%d/%d');
	elseif($admindateformat==2)
		list($day, $month, $year)=sscanf($tdat, '%d/%d/%d');
	if(! is_numeric($year))
		$year=date('Y');
	elseif((int)$year < 39)
		$year=(int)$year + 2000;
	elseif((int)$year < 100)
		$year=(int)$year + 1900;
	if($year < 1970 || $year > 2038) $year=date('Y');
	if(! is_numeric($month))
		$month=date('m');
	if(! is_numeric($day))
		$day=date('d');
	return(mktime(0, 0, 0, $month, $day, $year));
}
function unstripslashes($slashedText){
	global $magicq;
	return($magicq?trim(stripslashes((string)$slashedText)):trim((string)$slashedText));
}
function getattributes($attlist,$attid){
	$pos=strpos($attlist, $attid.'=');
	if($pos===FALSE)
		return '';
	$pos+=strlen($attid) + 1;
	$quote=$attlist[$pos];
	$pos2=strpos($attlist, $quote, $pos + 1);
	$retstr=substr($attlist, $pos + 1, $pos2 - ($pos + 1));
	return($retstr); 
}
class vrNodeList{
	var $length;
	var $childNodes;
	var $nodeName;
	var $nodeValue;
	var $attributes;

	function createNodeList($xmlStr){
		$xLen=strlen($xmlStr);
		for($i=0; $i < $xLen; $i++){
			if(substr($xmlStr, $i, 1)=='<' && substr($xmlStr, $i+1, 1)!='/' && substr($xmlStr, $i+1, 1)!='?'){ // Got a tag
				$j=strpos($xmlStr,'>',$i);
				$l=strpos($xmlStr,' ',$i);
				if(is_integer($l) && $l < $j){
					$this->nodeName[$this->length]=substr($xmlStr,$i+1,$l-($i+1));
					$this->attributes[$this->length]=substr($xmlStr,$l+1,($j-$l)-1);
				}else
					$this->nodeName[$this->length]=substr($xmlStr,$i+1,$j-($i+1));
				$k=$i+1;
				$nodeNameLen=strlen($this->nodeName[$this->length]);
				if(substr($xmlStr, $j-1, 1)=='/'){
					$this->nodeValue[$this->length]=null;
				}else{
					$currLev=0;
					while($k < $xLen && $currLev >= 0){
						if(substr($xmlStr, $k, 2)=='</'){
							if($currLev==0 && substr($xmlStr, $k+2, $nodeNameLen)==$this->nodeName[$this->length])
								break;
							$currLev--;
						}elseif(substr($xmlStr, $k, 1)=='<')
							$currLev++;
						elseif(substr($xmlStr, $k, 2)=='/>')
							$currLev--;
						$k++;
					}
					$this->nodeValue[$this->length]=substr($xmlStr,$j+1,$k-($j+1));
				}
				$this->childNodes[$this->length]=new vrNodeList($this->nodeValue[$this->length]);
				$this->length++;
				$i=$k;
			}
		}
	}
	function vrNodeList($xmlStr){
		$this->length=0;
		$this->childNodes='';
		$this->createNodeList($xmlStr);
	}
	function getValueByTagName($tagname){
		for($i=0; $i < $this->length; $i++){
			if($this->nodeName[$i]==$tagname){
				return($this->nodeValue[$i]);
			}else{
				if($this->childNodes!=''){
					if(($retval=$this->childNodes[$i]->getValueByTagName($tagname))!=null)
						return($retval);
				}
			}
		}
		return(null);
	}
	function getAttributeByTagName($tagname, $attrib){
		for($i=0; $i < $this->length; $i++){
			if($this->nodeName[$i]==$tagname){
				return(getattributes($this->attributes[$i], $attrib));
			}else{
				if($this->childNodes!=''){
					if(($retval=$this->childNodes[$i]->getAttributeByTagName($tagname, $attrib))!=null)
						return($retval);
				}
			}
		}
		return(null);
	}
}
class vrXMLDoc{
	var $tXMLStr;
	var $nodeList;
	function vrXMLDoc($xmlStr){
		$this->tXMLStr=$xmlStr;
		$this->nodeList=new vrNodeList($xmlStr);
	}
	function getElementsByTagName($tagname){
		$currlevel=0;
		$taglen=strlen($tagname);
	}
}
$GLOBALS['codestr']='2952710692840328509902143349209039553396765';
function upsencode($thestr, $propcodestr){
	global $codestr;
	if($propcodestr=='') $localcodestr=$codestr; else $localcodestr=$propcodestr;
	$newstr='';
	for($index=0; $index < strlen($localcodestr); $index++){
		$thechar=substr($localcodestr,$index,1);
		if(! is_numeric($thechar)){
			$thechar=ord($thechar) % 10;
		}
		$newstr.=$thechar;
	}
	$localcodestr=$newstr;
	while(strlen($localcodestr) < 40)
		$localcodestr.=$localcodestr;
	$newstr='';
	for($index=0; $index < strlen($thestr); $index++){
		$thechar=substr($thestr,$index,1);
		$newstr.=chr(ord($thechar)+(int)substr($localcodestr,$index,1));
	}
	return $newstr;
}
function upsdecode($thestr, $propcodestr){
	global $codestr;
	if($propcodestr=='') $localcodestr=$codestr; else $localcodestr=$propcodestr;
	$newstr='';
	if($localcodestr=='') return('');
	for($index=0; $index < strlen($localcodestr); $index++){
		$thechar=substr($localcodestr,$index,1);
		if(! is_numeric($thechar)){
			$thechar=ord($thechar) % 10;
		}
		$newstr.=$thechar;
	}
	$localcodestr=$newstr;
	while(strlen($localcodestr) < 40)
		$localcodestr.=$localcodestr;
	if(is_null($thestr)){
		return '';
	}else{
		$newstr='';
		for($index=0; $index < strlen($thestr); $index++){
			$thechar=substr($thestr,$index,1);
			$newstr.=chr(ord($thechar)-(int)substr($localcodestr,$index,1));
		}
		return($newstr);
	}
}
$locale_info='';
function FormatEuroCurrency($amount){
	global $useEuro, $adminLocale, $locale_info, $overridecurrency, $orcsymbol, $orcdecplaces, $orcdecimals, $orcthousands, $orcpreamount;
	if(@$overridecurrency==TRUE){
		if($orcpreamount)
			return $orcsymbol . number_format($amount,$orcdecplaces,$orcdecimals,$orcthousands);
		else
			return number_format($amount,$orcdecplaces,$orcdecimals,$orcthousands) . $orcsymbol;
	}else{
		if(! is_array($locale_info)){
			setlocale(LC_MONETARY,$adminLocale);
			$locale_info=localeconv();
			setlocale(LC_MONETARY,'en_US');
		}
		if($useEuro)
			return number_format($amount,2,$locale_info['decimal_point'],$locale_info['thousands_sep']) . ' &euro;';
		else
			return $locale_info['currency_symbol'] . number_format($amount,2,$locale_info['decimal_point'],$locale_info['thousands_sep']);
	}
}
function FormatCurrencyZeroDP($amount){
	global $useEuro, $adminLocale, $locale_info, $overridecurrency, $orcsymbol, $orcdecplaces, $orcdecimals, $orcthousands, $orcpreamount;
	if(@$overridecurrency==TRUE){
		if($orcpreamount)
			return $orcsymbol . number_format($amount,0,$orcdecimals,$orcthousands);
		else
			return number_format($amount,0,$orcdecimals,$orcthousands) . $orcsymbol;
	}else{
		if(! is_array($locale_info)){
			setlocale(LC_MONETARY,$adminLocale);
			$locale_info=localeconv();
			setlocale(LC_MONETARY,'en_US');
		}
		if($useEuro)
			return number_format($amount,0,$locale_info['decimal_point'],$locale_info['thousands_sep']) . ' &euro;';
		else
			return $locale_info['currency_symbol'] . number_format($amount,0,$locale_info['decimal_point'],$locale_info['thousands_sep']);
	}
}
function FormatEmailEuroCurrency($amount){
	global $useEuro, $adminLocale, $locale_info, $overridecurrency, $orcemailsymbol, $orcdecplaces, $orcdecimals, $orcthousands, $orcpreamount;
	if(@$overridecurrency==TRUE){
		if($orcpreamount)
			return $orcemailsymbol . number_format($amount,$orcdecplaces,$orcdecimals,$orcthousands);
		else
			return number_format($amount,$orcdecplaces,$orcdecimals,$orcthousands) . $orcemailsymbol;
	}else{
		if(! is_array($locale_info)){
			setlocale(LC_ALL,$adminLocale);
			$locale_info=localeconv();
			setlocale(LC_ALL,'en_US');
		}
		if($useEuro)
			return number_format($amount,2,$locale_info['decimal_point'],$locale_info['thousands_sep']) . ' Euro';
		else
			return $locale_info['currency_symbol'] . number_format($amount,2,$locale_info['decimal_point'],$locale_info['thousands_sep']);
	}
}
if(trim(@$_GET['PARTNER'])!='' || trim(@$_GET['REFERER'])!=''){
	if(@$expireaffiliate=='') $expireaffiliate=30;
	if(trim(@$_GET['PARTNER'])!='') $thereferer=trim(strip_tags(@$_GET['PARTNER'])); else $thereferer=trim(strip_tags(@$_GET['REFERER']));
	ectsetcookie('PARTNER',htmlspecialsid($thereferer),$expireaffiliate, '/','',@$_SERVER['HTTPS']=='on');
}
function ectsetcookie($name,$value='',$expires=0,$path='',$domain=''){
	if(headers_sent())
		print '<script src="vsadmin/savecookie.php?' . urlencode(htmlspecialsid($name)) . '=' . urlencode(htmlspecialsid($value)) . ($name=='PARTNER'?'&EXPIRES=' . $expires:'') . '"></script>';
	else
		setcookie($name,$value,$expires==0?0:time()+(60*60*24*(int)$expires),$path,$domain,@$_SERVER['HTTPS']=='on');
}
function do_stock_management($smOrdId){
}
function stock_subtract($smOrdId){
	if($GLOBALS['useStockManagement']){
		$sSQL="SELECT cartID,cartProdID,cartQuantity,pStockByOpts FROM cart INNER JOIN products ON cart.cartProdID=products.pID WHERE cartOrderID='" . escape_string($smOrdId) . "'";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){
			if((int)$rs['pStockByOpts']!=0){
				$sSQL='SELECT coOptID FROM cartoptions INNER JOIN options ON cartoptions.coOptID=options.optID INNER JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optType IN (-4,-2,-1,1,2,4) AND coCartID=' . $rs['cartID'];
				$result2=ect_query($sSQL) or ect_error();
				while($rs2=ect_fetch_assoc($result2)){
					$sSQL='UPDATE options SET optStock=optStock-' . $rs['cartQuantity'] . ' WHERE optID=' . $rs2['coOptID'];
					ect_query($sSQL) or ect_error();
				}
				ect_free_result($result2);
			}else{
				$sSQL='UPDATE products SET pInStock=pInStock-' . $rs['cartQuantity'] . " WHERE pID='" . $rs['cartProdID'] . "'";
				ect_query($sSQL) or ect_error();
			}
			$sSQL="SELECT pID,quantity FROM productpackages WHERE packageID='" . $rs['cartProdID'] . "'";
			$result2=ect_query($sSQL) or ect_error();
			while($rs2=ect_fetch_assoc($result2)){
				$sSQL='UPDATE products SET pInStock=pInStock-' . ($rs['cartQuantity']*$rs2['quantity']) . " WHERE pID='" . $rs2['pID'] . "'";
				ect_query($sSQL) or ect_error();
			}
			ect_free_result($result2);
		}
		ect_free_result($result);
	}
}
function release_stock($smOrdId){
	if($GLOBALS['useStockManagement']){
		$sSQL="SELECT cartID,cartProdID,cartQuantity,pStockByOpts FROM cart LEFT JOIN orders ON cart.cartOrderID=orders.ordID INNER JOIN products ON cart.cartProdID=products.pID WHERE ordAuthStatus<>'MODWARNOPEN' AND cartOrderID=" . $smOrdId;
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){
			if(((int)$rs['pStockByOpts'] <> 0)){
				$sSQL='SELECT coOptID FROM cartoptions INNER JOIN options ON cartoptions.coOptID=options.optID INNER JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optType IN (-4,-2,-1,1,2,4) AND coCartID=' . $rs['cartID'];
				$result2=ect_query($sSQL) or ect_error();
				while($rs2=ect_fetch_assoc($result2)){
					$sSQL='UPDATE options SET optStock=optStock+' . $rs['cartQuantity'] . ' WHERE optID=' . $rs2['coOptID'];
					ect_query($sSQL) or ect_error();
				}
				ect_free_result($result2);
			}else{
				$sSQL='UPDATE products SET pInStock=pInStock+' . $rs['cartQuantity'] . " WHERE pID='" . $rs['cartProdID'] . "'";
				ect_query($sSQL) or ect_error();
			}
			$sSQL="SELECT pID,quantity FROM productpackages WHERE packageID='" . $rs['cartProdID'] . "'";
			$result2=ect_query($sSQL) or ect_error();
			while($rs2=ect_fetch_assoc($result2)){
				$sSQL='UPDATE products SET pInStock=pInStock-' . ($rs['cartQuantity']*$rs2['quantity']) . " WHERE pID='" . $rs2['pID'] . "'";
				ect_query($sSQL) or ect_error();
			}
			ect_free_result($result2);
		}
		ect_free_result($result);
	}
}
function emailfriendjavascript(){
?>
<script type="text/javascript">
<!--
function efchkextra(obid,fldtxt){
	var hasselected=false,fieldtype='';
	var ob=document.getElementById(obid);
	if(ob)fieldtype=(ob.type?ob.type:'radio');
	if(fieldtype=='text'||fieldtype=='textarea'||fieldtype=='password'){
		hasselected=ob.value!='';
	}else if(fieldtype=='select-one'){
		hasselected=ob.selectedIndex!=0;
	}else if(fieldtype=='radio'){
		for(var ii=0;ii<ob.length;ii++)if(ob[ii].checked)hasselected=true;
	}else if(fieldtype=='checkbox')
		hasselected=ob.checked;
	if(!hasselected){
		if(ob.focus)ob.focus();else ob[0].focus();
		alert("<?php print jscheck($GLOBALS['xxPlsEntr'])?> \""+fldtxt+"\".");
		return(false);
	}
	return(true);
}
function efformvalidator(theForm){
	if(document.getElementById('yourname').value==""){
		alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxEFNam'])?>\".");
		document.getElementById('yourname').focus();
		return(false);
	}
	if(document.getElementById('youremail').value==""){
		alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxEFEm'])?>\".");
		document.getElementById('youremail').focus();
		return(false);
	}
	if(document.getElementById('askq').value!='1'){
		if(document.getElementById('friendsemail').value==""){
			alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxEFFEm'])?>\".");
			document.getElementById('friendsemail').focus();
			return(false);
		}
	}else{
<?php	for($index=1;$index<=9;$index++){
			eval('$askquestionparam=@$GLOBALS["askquestionparam'.$index.'"];');
			eval('$askquestionrequired=@$GLOBALS["askquestionrequired'.$index.'"];');
			if($askquestionparam!=''){
				if($askquestionparam!='' && $askquestionrequired) print "if(!efchkextra('askquestionparam".$index."','".jscheck(strip_tags($askquestionparam))."'))return(false);\r\n";
			}
		} ?>
	}
	return(true);
}
function dosendefdata(){
	if(efformvalidator(document.getElementById('efform'))){
		var ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		var yourname=document.getElementById("yourname").value;
		var youremail=document.getElementById("youremail").value;
		var friendsemail=(document.getElementById('askq').value=='1'?'':document.getElementById("friendsemail").value);
		var yourcomments=document.getElementById("yourcomments").value;
		var efcheck=document.getElementById("efcheck").value;
		postdata="posted=1&efid=" + encodeURIComponent(document.getElementById('efid').value) + (document.getElementById('askq').value=='1'?'&askq=1':'') + "&yourname=" + encodeURIComponent(yourname) + "&youremail=" + encodeURIComponent(youremail) + "&friendsemail=" + encodeURIComponent(friendsemail) + "&efcheck=" + encodeURIComponent(efcheck) + (document.getElementById("origprodid")?"&origprodid="+encodeURIComponent(document.getElementById("origprodid").value):'') + "&yourcomments=" + encodeURIComponent(yourcomments);
		for(var index=0;index<10;index++){
			if(document.getElementById('askquestionparam'+index)){
				var tval,ob=document.getElementById('askquestionparam'+index)
				fieldtype=(ob.type?ob.type:'radio');
				if(fieldtype=='text'||fieldtype=='textarea'||fieldtype=='password'){
					tval=ob.value;
				}else if(fieldtype=='select-one'){
					tval=ob[ob.selectedIndex].value;
				}else if(fieldtype=='radio'){
					for(var ii=0;ii<ob.length;ii++)if(ob[ii].checked)tval=ob[ii].value;
				}else if(fieldtype=='checkbox')
					tval=ob.value;
				postdata+='&askquestionparam'+index+'='+encodeURIComponent(tval);
			}
		}
		ajaxobj.open("POST", "emailfriend.php",false);
		ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxobj.send(postdata);
		document.getElementById('efrcell').innerHTML=ajaxobj.responseText;
	}
}
//-->
</script>
<?php
}
function productdisplayscript($doaddprodoptions,$isdetail){
global $prodoptions,$countryTaxRate,$useStockManagement,$OWSP,$noupdateprice,$noprice,$hideoptpricediffs,$showinstock,$noshowoptionsinstock,$showtaxinclusive,$notifybackinstock,$absoptionpricediffs,$usecsslayout,$xxCOTxt,$xxCarCon,$xxSCCarT,$magictool,$imgsoftcartcheckout;
global $currSymbol1,$currFormat1,$currSymbol2,$currFormat2,$currSymbol3,$currFormat3,$pricecheckerisincluded,$sstrong,$estrong,$currRate1,$currSymbol1,$currRate2,$currSymbol2,$currRate3,$currSymbol3,$currencyseparator,$enablewishlists,$wishlistonproducts,$pricezeromessage,$inlinepopups,$txtcollen;
if($currSymbol1!='' && $currFormat1=='') $currFormat1='%s <span style="font-weight:bold">'  . $currSymbol1 . '</span>';
if($currSymbol2!='' && $currFormat2=='') $currFormat2='%s <span style="font-weight:bold">'  . $currSymbol2 . '</span>';
if($currSymbol3!='' && $currFormat3=='') $currFormat3='%s <span style="font-weight:bold">'  . $currSymbol3 . '</span>';
	if(! (@$pricecheckerisincluded==TRUE)){
		if(@$_SESSION['clientID']!='' && (@$wishlistonproducts || $isdetail)){ ?>
<div id="savelistdiv" style="position:absolute; visibility:hidden; top:0px; left:0px; width:auto; height:auto; z-index:10000;" onmouseover="oversldiv=true;" onmouseout="oversldiv=false;setTimeout('checksldiv()',1000)">
<table class="cobtbl" cellspacing="1" cellpadding="3">
<tr><td class="cobll" onmouseover="this.className='cobhl'" onmouseout="this.className='cobll'" style="white-space:nowrap"><a class="ectlink wishlistmenu" href="#" onclick="document.getElementById('ectform'+gtid).listid.value=0;return subformid(gtid);"><?php print $GLOBALS['xxMyWisL']?></a></td></tr>
<?php		$sSQL="SELECT listID,listName FROM customerlists WHERE listOwner=" . $_SESSION['clientID'];
			$result2=ect_query($sSQL) or ect_error();
			while($rs2=ect_fetch_assoc($result2)){
				print '<tr><td class="cobll" onmouseover="this.className=\'cobhl\'" onmouseout="this.className=\'cobll\'" style="white-space:nowrap"><a class="ectlink wishlistmenu" href="#" onclick="document.getElementById(\'ectform\'+gtid).listid.value='.$rs2['listID'].';return subformid(gtid);">'.htmlspecials($rs2['listName']).'</a></td></tr>';
			}
			ect_free_result($result2); ?>
</table></div>
<?php	}
		if(@$notifybackinstock){ ?>
<div id="notifyinstockcover" style="<?php print (strstr(@$_SERVER['HTTP_USER_AGENT'],'Gecko')?'':'filter:alpha(opacity=50);')?>opacity:0.5;background:#AAAAAA;position:fixed;visibility:hidden;top:0px;left:0px;width:100%;height:auto;z-index:99;">&nbsp;</div>
<div class="notifyinstock" id="notifyinstockdiv" style="background:#bbbbbb;position:absolute;visibility:hidden;margin:2px;padding:2px;width:340px;z-index:100;border-radius:8px;box-shadow: 5px 5px 2px #666;">
<div style="border-radius:8px 0px 0px 0px;padding:6px;background:#bbbbbb;float:left;width:266px;height:21px;"><?php print $GLOBALS['xxNotSor']?></div>  
<div style="border-radius:0px 8px 0px 0px;padding:6px;background:#bbbbbb;text-align:right;float:right;width:50px;"><a href="javascript:closeinstock()"><img src="images/close.gif" style="border:0" alt="Close" /></a></div>
<div style="border-left:6px solid #bbbbbb;border-right:6px solid #bbbbbb;padding:6px;background:#eeeeee;clear:both;"><?php print $GLOBALS['xxNotCur']?></div>
<div style="padding:5px;background:#bbbbbb;font-size:0.8em;"><?php print $GLOBALS['xxNotEnt']?></div>
<div style="border-radius:0px 0px 0px 8px;padding:2px 0px 4px 4px;background:#bbbbbb;float:left;width:250px;"><input style="border:1px solid #333;padding:5px;" id="nsemailadd" size="36" type="text" /></div>
<div style="border-radius:0px 0px 8px 0px;padding:4px 0px 6px 4px;background:#bbbbbb;float:right;width:82px;"><input value="<?php print $GLOBALS['xxEmaiMe']?>" style="cursor:pointer;background:#313140;color:#fff;border:0px;border-radius:2px;padding:3px 7px;" onclick="regnotifystock()" type="button" /></div>
</div>
<?php	}
?><input type="hidden" id="hiddencurr" value="<?php print str_replace('"','&quot;',FormatEuroCurrency(0))?>" /><div id="opaquediv" style="display:none;position:fixed;width:100%;height:100%;background-color:rgba(140,140,150,0.5);top:0px;left:0px;text-align:center;z-index:10000;"></div><script type="text/javascript">
/* <![CDATA[ */
<?php	if(@$enablewishlists==TRUE){ ?>
var oversldiv;
var gtid;
function displaysavelist(evt,twin){
	oversldiv=false
	var theevnt=(!evt)?twin.event:evt;//IE:FF
	var sld=document.getElementById('savelistdiv');
	var scrolltop=(document.documentElement.scrollTop?document.documentElement.scrollTop:document.body.scrollTop);
	var scrollleft=(document.documentElement.scrollLeft?document.documentElement.scrollLeft:document.body.scrollLeft);
	sld.style.left=((theevnt.clientX+scrollleft)-sld.offsetWidth)+'px';
    sld.style.top=(theevnt.clientY+scrolltop)+'px';
	sld.style.visibility="visible";
	setTimeout('checksldiv()',2000);
	return(false);
}
function checksldiv(){
	var sld=document.getElementById('savelistdiv');
	if(! oversldiv)
		sld.style.visibility='hidden';
}
<?php	}
		if(@$notifybackinstock){ ?>
var notifystockid;
var notifystocktid;
var notifystockoid;
var nsajaxobj;
function notifystockcallback(){
	if(nsajaxobj.readyState==4){
		var rstxt=nsajaxobj.responseText;
		if(rstxt!='SUCCESS')alert(rstxt);else alert("<?php print jscheck($GLOBALS['xxInStNo'])?>");
		closeinstock();
	}
}
function regnotifystock(){
	var regex=/[^@]+@[^@]+\.[a-z]{2,}$/i;
	var theemail=document.getElementById('nsemailadd');
	if(!regex.test(theemail.value)){
		alert("<?php print jscheck($GLOBALS['xxValEm'])?>");
		theemail.focus();
		return(false);
	}else{
		nsajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		nsajaxobj.onreadystatechange=notifystockcallback;
		nsajaxobj.open("GET", "vsadmin/ajaxservice.php?action=notifystock&pid="+encodeURIComponent(notifystockid)+'&tpid='+encodeURIComponent(notifystocktid)+'&oid='+encodeURIComponent(notifystockoid)+'&email='+encodeURIComponent(theemail.value),true);
		nsajaxobj.send(null);
	}
}
function closeinstock(){
	document.getElementById('notifyinstockdiv').style.visibility='hidden';
	document.getElementById('notifyinstockcover').style.visibility='hidden';
}
function notifyinstock(isoption,pid,tpid,oid){
	notifystockid=pid;
	notifystocktid=tpid;
	notifystockoid=oid;
	var ie=document.all && !window.opera;
	var bsd=document.getElementById('notifyinstockdiv');
	var isc=document.getElementById('notifyinstockcover');
	var viewportwidth=600;
	var viewportheight=400;
	if (typeof window.innerWidth!='undefined'){
		viewportwidth=window.innerWidth;
		viewportheight=window.innerHeight;
	}else if(typeof document.documentElement!='undefined' && typeof document.documentElement.clientWidth!='undefined' && document.documentElement.clientWidth!=0){
		viewportwidth=document.documentElement.clientWidth;
		viewportheight=document.documentElement.clientHeight;
	}
	var scrolltop=(document.documentElement.scrollTop?document.documentElement.scrollTop:document.body.scrollTop);
	var scrollleft=(document.documentElement.scrollLeft?document.documentElement.scrollLeft:document.body.scrollLeft);
	isc.style.height='2000px';
	isc.style.visibility='visible';
	bsd.style.left=(scrollleft+((viewportwidth-bsd.offsetWidth)/2))+'px';
    bsd.style.top=(scrolltop+((viewportheight-bsd.offsetHeight)/2))+'px';
	bsd.style.visibility='visible';
	return(false);
}
<?php	} ?>
function subformid(tid){
	var tform=document.getElementById('ectform'+tid);
	if(tform.onsubmit())tform.submit();
	return(false);
}
<?php	if(! @$usehardaddtocart){ ?>
function ajaxaddcartcb(){
	if(ajaxobj.readyState==4){
		var pparam,pname,pprice,pimage,optname,optvalue,retvals=ajaxobj.responseText.split('&');
		try{pimage=decodeURIComponent(retvals[6])}catch(err){pimage='ERROR'}
		var schtml='<div style="padding:3px;float:left;text-align:left" class="scart sccheckout"><?php print jsescapel(imageorbutton(@$imgsoftcartcheckout,$xxCOTxt,'sccheckout',(@$GLOBALS['cartpageonhttps']?@$GLOBALS['pathtossl']:'').'cart.php', FALSE))?></div><div style="padding:3px;float:right;text-align:right" class="scart scclose"><a href="#" onclick="document.getElementById(\'opaquediv\').style.display=\'none\';return false"><img src="images/close.gif" style="border:0" alt="<?php print $GLOBALS['xxClsWin']?>" /></a></div>' +
		'<div style="padding:3px;text-align:left;width:562px;border-top:1px solid #567CBB;clear:both" class="scart scprodsadded"><?php print $GLOBALS['xxSCAdOr']?></div>';
		if(retvals[0]!='') schtml+='<div style="padding:3px;text-align:center;background-color:#FFCFBF;border:1px solid #8C0000;clear:both" class="scart scnostock"><?php print jsescapel($GLOBALS['xxNotSto'])?>: "'+decodeURIComponent(retvals[0])+'"</div>';
		schtml+='<div style="float:left">'+ // Image and products container
		'<div style="padding:3px;float:left;width:160px" class="scart scimage"><img class="scimage" src="'+pimage+'" alt="" style="max-width:150px" /></div>' +
		'<div style="padding:3px;float:left" class="scart scproducts">'; // start outer div for products
		var baseind=7;
		for(var index=0;index<retvals[3];index++){
			try{pname=decodeURIComponent(retvals[baseind+1])}catch(err){pname='ERROR'}
			try{pprice=decodeURIComponent(retvals[baseind+3])}catch(err){pprice='ERROR'}
			schtml+='<div style="padding:3px;float:left;clear:left" class="scart scproduct"><div style="padding:3px;text-align:left" class="scart scprodname"> '+retvals[baseind+2]+' '+pname+' <?php print $GLOBALS['xxHasAdd']?></div>';
			var prhtml='<div style="padding:3px;text-align:left;clear:left;background:#f1f1f1;border-top:1px dotted #567CBB;font-weight:bold;color:#666<?php print @$GLOBALS['nopriceanywhere']?';display:none':''?>" class="scart scprice"><?php print $GLOBALS['xxPrice'].($GLOBALS['xxPrice']!=''?':':'')?>'+pprice+'</div>';
			var numoptions=retvals[baseind+5];
			baseind+=6;
			if(numoptions>0){
				schtml+='<div style="float:left;max-width:400px'+(numoptions>10?';height:200px;overflow-y:scroll':'')+'" class="scart scoptions">';
				for(var index2=0;index2<numoptions;index2++){
					try{optname=decodeURIComponent(retvals[baseind++])}catch(err){optname='ERROR'}
					try{optvalue=decodeURIComponent(retvals[baseind++])}catch(err){optvalue='ERROR'}
					schtml+='<div style="padding:3px;float:left;clear:left;margin-left:10px" class="scart scoption"><div style="padding:3px;float:left" class="scart optname">- '+optname+':</div><div style="padding:3px;float:left" class="scart optvalue">'+optvalue+'</div></div>';
				}
				schtml+='</div>';
			}
			schtml+=prhtml+'</div>';
		}
		schtml+='</div>'+ // end outer div for products
		'</div>'+ // end image and products container
		'<div style="clear:both">';
		try{pprice=decodeURIComponent(retvals[5])}catch(err){pprice='ERROR'}
		if(retvals[1]==1) schtml+='<div style="padding:3px;text-align:center;background-color:#FFCFBF;border:1px solid #8C0000" class="scart scnostock"><?php print jsescapel($GLOBALS['xxSCStkW'])?></div>';
		if(retvals[2]==1) schtml+='<div style="padding:3px;text-align:center;background-color:#FFCFBF;border:1px solid #8C0000" class="scart scbackorder"><?php print jsescapel($GLOBALS['xxSCBakO'])?></div>';
		schtml+='<div style="padding:3px;text-align:right" class="scart sccartitems"><?php print jsescapel($GLOBALS['xxCarCon'])?>:'+retvals[4]+' <?php print jsescapel($GLOBALS['xxSCItem'])?></div>' +
		'<div style="padding:3px;text-align:right;background:#f1f1f1;border-top:1px dotted #567CBB;font-weight:bold;color:#666<?php print @$GLOBALS['nopriceanywhere']?';display:none':''?>" class="scart sccarttotal"><span style="color:#8C0000;display:none" id="sccartdscnt" class="scart sccartdscnt">(<?php print jsescapel($GLOBALS['xxDscnts'])?>:<span id="sccartdscamnt" class="sccartdscamnt"></span>) / </span><?php print($GLOBALS['showtaxinclusive']!=0?jsescapel($GLOBALS['xxCntTax']).':<span id="sccarttax" class="sccarttax"></span> / ':'').jsescapel($GLOBALS['xxSCCarT'])?>:'+pprice+'</div>' +
		'<div style="padding:3px;text-align:right" class="scart sclinks"><a class="ectlink scclink" href="#" onclick="document.getElementById(\'opaquediv\').style.display=\'none\';return false"><?php print jsescapel($GLOBALS['xxCntShp'])?></a> | <a class="ectlink scclink" href="<?php print @$GLOBALS['cartpageonhttps']?@$GLOBALS['pathtossl']:''?>cart.php" onclick="document.getElementById(\'opaquediv\').style.display=\'none\';return true"><?php print jsescapel($GLOBALS['xxEdiOrd'])?></a></div>' +
		'</div>';
		document.getElementById('scdiv').innerHTML=schtml;
		if(document.getElementsByClassName){
			var ectMCpm=document.getElementsByClassName('ectMCquant');
			for(var index=0;index<ectMCpm.length;index++)ectMCpm[index].innerHTML=retvals[4];
			ectMCpm=document.getElementsByClassName('ectMCship');
			for(var index=0;index<ectMCpm.length;index++)ectMCpm[index].innerHTML='<a href="<?php print @$GLOBALS['cartpageonhttps']?@$GLOBALS['pathtossl']:''?>cart.php"><?php print jsescapel($GLOBALS['xxClkHere'])?></a>';
			ectMCpm=document.getElementsByClassName('ectMCtot');
			for(var index=0;index<ectMCpm.length;index++)ectMCpm[index].innerHTML=pprice;
			if(retvals.length>baseind){
				try{pparam=decodeURIComponent(retvals[baseind++])}catch(err){pparam='-'}
				if(ectMCpm=document.getElementById('sccarttax'))ectMCpm.innerHTML=pparam;
				try{pparam=decodeURIComponent(retvals[baseind++])}catch(err){pparam='-'}
				var ectMCpm=document.getElementsByClassName('mcMCdsct');
				for(var index=0;index<ectMCpm.length;index++)ectMCpm[index].innerHTML=pparam;
				document.getElementById('sccartdscamnt').innerHTML=pparam;
				try{pparam=decodeURIComponent(retvals[baseind++])}catch(err){pparam='-'}
				var ectMCpm=document.getElementsByClassName('ecHidDsc');
				for(var index=0;index<ectMCpm.length;index++)ectMCpm[index].style.display=(pparam=='0'?'none':'');
				document.getElementById('sccartdscnt').style.display=(pparam=='0'?'none':'');
				try{pparam=decodeURIComponent(retvals[baseind++])}catch(err){pparam='-'}
				var ectMCpm=document.getElementsByClassName('mcLNitems');
				for(var index=0;index<ectMCpm.length;index++)ectMCpm[index].innerHTML=pparam;
			}
		}
	}
}
function ajaxaddcart(frmid){
	var elem=document.getElementById('ectform'+frmid).elements;
	var str='';
	var postdata='ajaxadd=true';
	eval('var isvalidfm=formvalidator'+frmid+'(document.getElementById(\'ectform'+frmid+'\'))');
	if(isvalidfm){
		for(var ecti=0; ecti<elem.length; ecti++){
			if(elem[ecti].style.display=='none'){
			}else if(elem[ecti].type=='select-one'){
				if(elem[ecti].value!='')postdata+='&'+elem[ecti].name+'='+elem[ecti].value;
			}else if(elem[ecti].type=='text'||elem[ecti].type=='textarea'||elem[ecti].type=='hidden'){
				if(elem[ecti].value!='')postdata+='&'+elem[ecti].name+'='+encodeURIComponent(elem[ecti].value);
			}else if(elem[ecti].type=='radio'||elem[ecti].type=='checkbox'){
				if(elem[ecti].checked)postdata+='&'+elem[ecti].name+'='+elem[ecti].value;
			}
		}
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange=ajaxaddcartcb;
		ajaxobj.open("POST","vsadmin/shipservice.php?action=addtocart",true);
		ajaxobj.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		ajaxobj.send(postdata);
		document.getElementById('opaquediv').innerHTML='<div id="scdiv" class="scart scwrap" style="margin:120px auto 0 auto;background:#FFF;width:600px;padding:6px;border-radius:5px;box-shadow:1px 1px 5px #333"><img src="images/preloader.gif" alt="" style="margin:40px" /></div>';
		document.getElementById('opaquediv').style.display='';
	}
}
<?php	} ?>
var op=[]; // Option Price Difference
var aIM=[],aIML=[]; // Option Alternate Image
var dOP=[]; // Dependant Options
var dIM=[]; // Default Image
var pIM=[],pIML=[]; // Product Image
var pIX=[]; // Product Image Index
var ot=[]; // Option Text
var pp=[]; // Product Price
var pi=[]; // Alternate Product Image
var or=[]; // Option Alt Id
var cp=[]; // Current Price
var oos=[]; // Option Out of Stock Id
var rid=[]; // Resulting product Id
var otid=[]; // Original product Id
var opttype=[];
var optperc=[];
var optmaxc=[];
var optacpc=[];
var fid=[];
var baseid='';<?php
			if($useStockManagement){ ?>
var oS=[];
var ps=[];
function checkStock(x,i,backorder){
if(i!=''&&(oS[i]>0||or[i]))return(true);
if(backorder&&(globBakOrdChk||confirm("<?php print jscheck($GLOBALS['xxBakOpt'])?>")))return(globBakOrdChk=true);
<?php	if(@$notifybackinstock){ ?>
notifyinstock(true,x.form.id.value,x.form.id.value,i);
<?php	}else{ ?>
alert("<?php print jscheck($GLOBALS['xxOptOOS'])?>");
<?php	} ?>
x.focus();return(false);
}<?php		} ?>
var isW3=(document.getElementById&&true);
var tax=<?php print $countryTaxRate ?>;
function dummyfunc(){};
function pricechecker(cnt,i){
if(i!=''&&i in op)return(op[i]);return(0);}
function regchecker(cnt,i){
if(i!='')return(or[i]);return('');}
function enterValue(x){
alert("<?php print jscheck($GLOBALS['xxPrdEnt'])?>");
x.focus();return(false);}
function invalidChars(x){
alert("<?php print jscheck($GLOBALS['xxInvCha'])?>" + x);
return(false);}
function enterDigits(x){alert("<?php print jscheck($GLOBALS['xxDigits'])?>");x.focus();return(false);}
function enterMultValue(){alert("<?php print jscheck($GLOBALS['xxEntMul'])?>");return(false);}
function chooseOption(x){
alert("<?php print jscheck($GLOBALS['xxPrdChs'])?>");
x.focus();return(false);}
function dataLimit(x,numchars){
alert("<?php print jscheck($GLOBALS['xxPrd255'])?>".replace(255,numchars));
x.focus();return(false);}
var hiddencurr='';
function addCommas(ns,decs,thos){
if((dpos=ns.indexOf(decs))<0)dpos=ns.length;
dpos-=3;
while(dpos>0){
	ns=ns.substr(0,dpos)+thos+ns.substr(dpos);
	dpos-=3;
}
return(ns);
}
function formatprice(i, currcode, currformat){
<?php	$tempStr=FormatEuroCurrency(0);
	$hasdecimals=(strstr($tempStr,',') || strstr($tempStr,'.'));
	print "if(hiddencurr=='')hiddencurr=document.getElementById('hiddencurr').value;var pTemplate=hiddencurr;\n";
	print "if(currcode!='') pTemplate=' " . number_format(0,2,'.',',') . "' + (currcode!=' '?'<strong>'+currcode+'<\/strong>':'');";
	print 'if(currcode==" JPY"'.($hasdecimals?'':'||currcode==""').')i=Math.round(i).toString();';
	if($hasdecimals){ ?>
else if(i==Math.round(i))i=i.toString()+".00";
else if(i*10.0==Math.round(i*10.0))i=i.toString()+"0";
else if(i*100.0==Math.round(i*100.0))i=i.toString();
<?php	}
	print 'i=addCommas(i.toString()'.(strstr($tempStr,',')?".replace(/\\./,','),',','.'":",'.',','").');';
	print 'if(currcode!="")pTemplate=currformat.toString().replace(/%s/,i.toString());';
	print 'else pTemplate=pTemplate.toString().replace(/\d[,.]*\d*/,i.toString());';
	print 'return(pTemplate);';
?>}
function openEFWindow(id,askq){
<?php	if(@$inlinepopups!=TRUE){ ?>
window.open('emailfriend.php?'+(askq?'askq=1&':'')+'id='+id,'email_friend','menubar=no, scrollbars=no, width=430, height=430, directories=no,location=no,resizable=yes,status=no,toolbar=no')
<?php	}else{ ?>
var ecx=window.pageXOffset ? window.pageXOffset : document.documentElement && document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body ? document.body.scrollLeft : 0;
var ecy=window.pageYOffset ? window.pageYOffset : document.documentElement && document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body ? document.body.scrollTop : 0;
var viewportwidth=600;
if (typeof window.innerWidth!='undefined'){
	viewportwidth=window.innerWidth;
}else if(typeof document.documentElement!='undefined' && typeof document.documentElement.clientWidth!='undefined' && document.documentElement.clientWidth!=0){
	viewportwidth=document.documentElement.clientWidth;
}
efrdiv=document.createElement('div');
efrdiv.setAttribute('id', 'efrdiv');
efrdiv.style.zIndex=1000;
efrdiv.style.position='absolute';
efrdiv.style.width='100%';
efrdiv.style.height='2000px';
efrdiv.style.top='0px';
efrdiv.style.left=ecx + 'px';
efrdiv.style.textAlign='center';
efrdiv.style.backgroundImage='url(images/opaquepixel.png)';
document.body.appendChild(efrdiv);
ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
ajaxobj.open("GET", 'emailfriend.php?'+(askq?'askq=1&':'')+'id='+id, false);
ajaxobj.send(null);
efrdiv.innerHTML=ajaxobj.responseText;
document.getElementById('emftable').style.top=(ecy+100)+'px';
document.getElementById('emftable').style.left=(((viewportwidth-500)/2))+'px';
<?php	} ?>
}
function updateoptimage(theitem,themenu,opttype){
var imageitemsrc='',mzitem,theopt,theid,imageitem,imlist,imlistl,fn=window['updateprice'+theitem];
dependantopts(theitem);
fn();
if(opttype==1){
	theopt=document.getElementsByName('optn'+theitem+'x'+themenu)
	for(var i=0; i<theopt.length; i++)
		if(theopt[i].checked)theid=theopt[i].value;
}else{
	theopt=document.getElementById('optn'+theitem+'x'+themenu)
	theid=theopt.options[theopt.selectedIndex].value;
}
<?php	if(@$magictool!=''){ ?>
if(mzitem=(document.getElementById("zoom1")?document.getElementById("zoom1"):document.getElementById("mzprodimage"+theitem))){
	if(aIML[theid]){
		<?php print $magictool?>.update(mzitem,vsdecimg(aIML[theid]),vsdecimg(aIM[theid]));
	}else if(pIM[0]&&pIM[999]){
		imlist=pIM[0].split('*');imlistl=pIM[999].split('*');
		for(var index=0;index<imlist.length;index++)
			if(imlist[index]==aIM[theid]&&imlistl[index]){<?php print $magictool?>.update(mzitem.id,vsdecimg(imlistl[index]),vsdecimg(aIM[theid]));return;}
		if(aIM[theid])<?php print $magictool?>.update(mzitem.id,vsdecimg(aIM[theid]),vsdecimg(aIM[theid]));
	}else if(aIM[theid])
		<?php print $magictool?>.update(mzitem.id,vsdecimg(aIM[theid]),vsdecimg(aIM[theid]));
}else
<?php	} ?>
	if(imageitem=document.getElementById("prodimage"+theitem)){
		if(aIM[theid]){
			if(typeof(imageitem.src)!='unknown')imageitem.src=vsdecimg(aIM[theid]);
		}
	}
}
function vsdecimg(timg){
	return decodeURIComponent(timg<?php print @$GLOBALS['noencodeimages']?'':'.replace("|","prodimages/").replace("<",".gif").replace(">",".jpg").replace("?",".png")'?>);
}
function updateprodimage(theitem,isnext){
var imlist=pIM[theitem].split('*');
if(isnext) pIX[theitem]++; else pIX[theitem]--;
if(pIX[theitem]<0) pIX[theitem]=imlist.length-2;
if(pIX[theitem]>imlist.length-2) pIX[theitem]=0;
if(document.getElementById("prodimage"+theitem))document.getElementById("prodimage"+theitem).src=vsdecimg(imlist[pIX[theitem]]);
document.getElementById("extraimcnt"+theitem).innerHTML=pIX[theitem]+1;
<?php	if(@$magictool!=''){ ?>
if(pIML[theitem]){
	var imlistl=pIML[theitem].split('*');
	if(imlistl.length>=pIX[theitem])
		if(mzitem=document.getElementById("mzprodimage"+theitem))<?php print $magictool?>.update(mzitem,vsdecimg(imlistl[pIX[theitem]]),vsdecimg(imlist[pIX[theitem]]));
}
<?php	} ?>
return false;
}
<?php	if($doaddprodoptions){ ?>
function sz(szid,szprice,<?php if($useStockManagement) print 'szstock,'?>szimage){
<?php		if($useStockManagement) print 'ps[szid]=szstock;'; ?>
	pp[szid]=szprice;
	if(szimage!='')pi[szid]=szimage;
}
function gfid(tid){
	if(tid in fid)
		return(fid[tid]);
	fid[tid]=document.getElementById(tid);
	return(fid[tid]);
}
function applyreg(arid,arreg){
	if(arreg&&arreg!=''){
		arreg=arreg.replace('%s', arid);
		if(arreg.indexOf(' ')>0){
			var ida=arreg.split(' ', 2);
			arid=arid.replace(ida[0], ida[1]);
		}else
			arid=arreg;
	}
	return(arid);
}
function getaltid(theid,optns,prodnum,optnum,optitem,numoptions){
	var thereg='';
	for(var index=0; index<numoptions; index++){
		if(Math.abs(opttype[index])==4){
			thereg=or[optitem];
		}else if(Math.abs(opttype[index])==2){
			if(optnum==index)
				thereg=or[optns.options[optitem].value];
			else{
				var opt=gfid("optn"+prodnum+"x"+index);
				if(!opt.disabled)thereg=or[opt.options[opt.selectedIndex].value];
			}
		}else if(Math.abs(opttype[index])==1){
			opt=document.getElementsByName("optn"+prodnum+"x"+index);
			if(optnum==index){
				thereg=or[opt[optitem].value];
			}else{
				for(var y=0;y<opt.length;y++)
					if(opt[y].checked&&!opt[y].disabled) thereg=or[opt[y].value];
			}
		}else
			continue;
		theid=applyreg(theid,thereg);
	}
	return(theid);
}
function getnonaltpricediff(optns,prodnum,optnum,optitem,numoptions,theoptprice){
	var nonaltdiff=0;
	for(index=0; index<numoptions; index++){
		var optid='';
		if(Math.abs(opttype[index])==4){
			optid=optitem;
		}else if(Math.abs(opttype[index])==2){
			if(optnum==index)
				optid=optns.options[optitem].value;
			else{
				var opt=gfid("optn"+prodnum+"x"+index);
				if(opt.style.display=='none')continue;
				optid=opt.options[opt.selectedIndex].value;
			}
		}else if(Math.abs(opttype[index])==1){
			var opt=document.getElementsByName("optn"+prodnum+"x"+index);
			if(optnum==index)
				optid=opt[optitem].value;
			else{
				for(var y=0;y<opt.length;y++){ if(opt[y].checked&&opt[y].style.display!='none')optid=opt[y].value; }
			}
		}else
			continue;
		if(!or[optid]&&optid in op)
			if(optperc[index])nonaltdiff+=(op[optid]*theoptprice)/100.0;else nonaltdiff+=op[optid];
	}
	return(nonaltdiff);
}
function updateprice(numoptions,prodnum,prodprice,origid,thetax,stkbyopts,taxexmpt,backorder){
	baseid=origid;
	if(!isW3) return;
	oos[prodnum]='';
	var origprice=prodprice;
	var hasmultioption=false,canresolve=true,allbutlastselected=true;
	for(cnt=0; cnt<numoptions; cnt++){
		if(Math.abs(opttype[cnt])==2){
			optns=gfid("optn"+prodnum+"x"+cnt);
			if(!optns.disabled) baseid=applyreg(baseid,regchecker(prodnum,optns.options[optns.selectedIndex].value));
			if(optns.options[optns.selectedIndex].value==''&&cnt<numoptions-1)allbutlastselected=false;
		}else if(Math.abs(opttype[cnt])==1){
			optns=document.getElementsByName("optn"+prodnum+"x"+cnt);
			var hasonechecked=false;
			for(var i=0;i<optns.length;i++){ if(optns[i].checked&&!optns[i].disabled){hasonechecked=true;baseid=applyreg(baseid,regchecker(prodnum,optns[i].value));}}
			if(!hasonechecked&&cnt<numoptions-1)allbutlastselected=false;
		}
		if(baseid in pp)prodprice=pp[baseid];
	}
	var baseprice=prodprice;
	for(cnt=0; cnt<numoptions; cnt++){
		if(Math.abs(opttype[cnt])==2){
			optns=gfid("optn"+prodnum+"x"+cnt);
			if(optns.disabled)continue;
			if(optperc[cnt])
				prodprice+=((baseprice*pricechecker(prodnum,optns.options[optns.selectedIndex].value))/100.0);
			else
				prodprice+=pricechecker(prodnum,optns.options[optns.selectedIndex].value);
		}else if(Math.abs(opttype[cnt])==1){
			optns=document.getElementsByName("optn"+prodnum+"x"+cnt);
			if(optperc[cnt])
				for(var i=0;i<optns.length;i++){ if(optns[i].checked&&optns[i].style.display!='none') prodprice+=((baseprice*pricechecker(prodnum,optns[i].value))/100.0); }
			else
				for(var i=0;i<optns.length;i++){ if(optns[i].checked&&optns[i].style.display!='none') prodprice+=pricechecker(prodnum,optns[i].value); }
		}
	}
	var totalprice=prodprice;
	var prodtax=<?php print (@$showtaxinclusive===2?'(!taxexmpt?prodprice*thetax/100.0:0)':'0')?>;
	for(cnt=0; cnt<numoptions; cnt++){
		if(Math.abs(opttype[cnt])==2){
			var optns=gfid("optn"+prodnum+"x"+cnt);
			for(var i=0;i<optns.length;i++){
				if(optns.options[i].value!=''){
					theid=origid;
					optns.options[i].text=ot[optns.options[i].value];
					theid=getaltid(theid,optns,prodnum,cnt,i,numoptions);
					theoptprice=(theid in pp?pp[theid]:origprice);
					if(pi[theid]&&pi[theid]!=''&&or[optns.options[i].value]){aIM[optns.options[i].value]=pi[theid].split('*')[0];if(pi[theid].split('*')[1])aIML[optns.options[i].value]=pi[theid].split('*')[1];}<?php
	if($useStockManagement){ ?>
					theoptstock=((theid in ps&&or[optns.options[i].value])||!stkbyopts ? ps[theid] : oS[optns.options[i].value]);
					if(theoptstock<=0&&optns.selectedIndex==i){oos[prodnum]="optn"+prodnum+"x"+cnt;rid[prodnum]=theid;otid[prodnum]=origid;}<?php
	} ?>
					canresolve=(!or[optns.options[i].value]||theid in pp)?true:false;
					var staticpricediff=getnonaltpricediff(optns,prodnum,cnt,i,numoptions,theoptprice);
					theoptpricediff=(theoptprice+staticpricediff)-totalprice;
<?php
	if(@$noprice!=TRUE && @$hideoptpricediffs!=TRUE) print "if(Math.round(theoptpricediff*100)!=0)optns.options[i].text+=' ('+".(@$absoptionpricediffs?'':"(theoptpricediff>0?'+':'-')+").'formatprice(Math.abs(Math.round(('.(@$absoptionpricediffs?'prodprice+prodtax+':'').'theoptpricediff'.(@$showtaxinclusive===2?'+(!taxexmpt?theoptpricediff*thetax/100.0:0)':'').")*100)/100.0), '', '')+')';";
	if($useStockManagement && @$showinstock==TRUE && @$noshowoptionsinstock!=TRUE) print "if(stkbyopts&&canresolve)optns.options[i].text+='".str_replace("'","\'",$GLOBALS['xxOpSkTx'])."'.replace('%s',Math.max(theoptstock,0));";
	print "if(".($useStockManagement?'theoptstock>0||!stkbyopts||!canresolve':'true').")optns.options[i].className='';else optns.options[i].className='oostock';"; ?>
					if(allbutlastselected&&cnt==numoptions-1&&!canresolve)optns.options[i].className='oostock';
				}
			}
		}else if(Math.abs(opttype[cnt])==1){
			optns=document.getElementsByName("optn"+prodnum+"x"+cnt);
			for(var i=0;i<optns.length;i++){
				theid=origid;
				optn=gfid("optn"+prodnum+"x"+cnt+"y"+i);
				optn.innerHTML=ot[optns[i].value];
				theid=getaltid(theid,optns,prodnum,cnt,i,numoptions);
				theoptprice=(theid in pp?pp[theid]:origprice);
				if(pi[theid]&&pi[theid]!=''&&or[optns[i].value]){aIM[optns[i].value]=pi[theid].split('*')[0];if(pi[theid].split('*')[1])aIML[optns[i].value]=pi[theid].split('*')[1];}<?php
	if($useStockManagement){ ?>
				theoptstock=((theid in ps&&or[optns[i].value])||!stkbyopts ? ps[theid] : oS[optns[i].value]);
				if(theoptstock<=0&&optns[i].checked){oos[prodnum]="optn"+prodnum+"x"+cnt+"y"+i;rid[prodnum]=theid;otid[prodnum]=origid;}<?php
	} ?>
				canresolve=(!or[optns[i].value]||theid in pp)?true:false;
				var staticpricediff=getnonaltpricediff(optns,prodnum,cnt,i,numoptions,theoptprice);
				theoptpricediff=(theoptprice+staticpricediff)-totalprice;
<?php
	if(@$noprice!=TRUE && @$hideoptpricediffs!=TRUE) print "if(Math.round(theoptpricediff*100)!=0)optn.innerHTML+=' ('+".(@$absoptionpricediffs?'':"(theoptpricediff>0?'+':'-')+").'formatprice(Math.abs(Math.round(('.(@$absoptionpricediffs?'prodprice+prodtax+':'').'theoptpricediff'.(@$showtaxinclusive===2?'+(!taxexmpt?theoptpricediff*thetax/100.0:0)':'').")*100)/100.0), '', '')+')';";
	if($useStockManagement && @$showinstock==TRUE && @$noshowoptionsinstock!=TRUE) print "if(stkbyopts&&canresolve)optn.innerHTML+='".str_replace("'","\'",$GLOBALS['xxOpSkTx'])."'.replace('%s',Math.max(theoptstock,0));";
	print "if(".($useStockManagement?'theoptstock>0||!stkbyopts||!canresolve':'true').")optn.className='';else optn.className='oostock';"; ?>
				if(allbutlastselected&&cnt==numoptions-1&&!canresolve)optn.className='oostock';
			}
		}else if(Math.abs(opttype[cnt])==4){
			var tstr="optm"+prodnum+"x"+cnt+"y";
			var tlen=tstr.length;
			var optns=document.getElementsByTagName("input");
			hasmultioption=true;
			for(var i=0;i<optns.length;i++){
				if(optns[i].id.substr(0,tlen)==tstr){
					theid=origid;
					var oid=optns[i].name.substr(4);
					var optn=optns[i]
					var optnt=gfid(optns[i].id.replace(/optm/,"optx"));
					optnt.innerHTML='&nbsp;- '+ot[oid];
					theid=getaltid(theid,optns,prodnum,cnt,oid,numoptions);
					theoptprice=(theid in pp?pp[theid]:origprice);<?php
	if($useStockManagement){ ?>
				theoptstock=((theid in ps&&or[oid])||!stkbyopts ? ps[theid] : oS[oid]);
				if(theoptstock<=0&&optns[i].checked){oos[prodnum]="optm"+prodnum+"x"+cnt+"y"+i;rid[prodnum]=theid;otid[prodnum]=origid;}
				canresolve=(!or[oid]||(applyreg(theid,or[oid]) in ps))?true:false;<?php
	} ?>
				var staticpricediff=getnonaltpricediff(optns,prodnum,cnt,oid,numoptions,theoptprice);
				theoptpricediff=(theoptprice+staticpricediff)-totalprice;
<?php
	if(@$noprice!=TRUE && @$hideoptpricediffs!=TRUE) print "if(Math.round(theoptpricediff*100)!=0)optnt.innerHTML+=' ('+".(@$absoptionpricediffs?'':"(theoptpricediff>0?'+':'-')+").'formatprice(Math.abs(Math.round(('.(@$absoptionpricediffs?'prodprice+prodtax+':'').'theoptpricediff'.(@$showtaxinclusive===2?'+(!taxexmpt?theoptpricediff*thetax/100.0:0)':'').")*100)/100.0), '', '')+')';";
	if($useStockManagement && @$showinstock==TRUE && @$noshowoptionsinstock!=TRUE) print "if(stkbyopts&&canresolve&&!(or[oid]&&theoptstock<=0))optnt.innerHTML+='".str_replace("'","\'",$GLOBALS['xxOpSkTx'])."'.replace('%s',Math.max(theoptstock,0));";
	if($useStockManagement) print "if(theoptstock>0||(or[oid]&&!canresolve)||backorder){optn.className='multioption';optn.disabled=false;optn.style.backgroundColor='#FFFFFF';}else{optn.className='multioption oostock';optn.disabled=true;optn.style.backgroundColor='#EBEBE4';}"; ?>
				}
			}
		}
	}
	if(hasmultioption)oos[prodnum]='';
	if((!cp[prodnum]||cp[prodnum]==0)&&prodprice==0)return;
	cp[prodnum]=prodprice;
<?php
	if(@$noprice!=TRUE){
		print "if(document.getElementById('taxmsg'+prodnum))document.getElementById('taxmsg'+prodnum).style.display='';";
		if(@$noupdateprice!=TRUE) print "if(document.getElementById('pricediv'+prodnum))document.getElementById('pricediv'+prodnum).innerHTML=".(@$pricezeromessage!=''?"prodprice==0?'".str_replace("'","\'",$pricezeromessage)."':":'').'formatprice(Math.round((prodprice'.(@$showtaxinclusive===2?'+(!taxexmpt?prodprice*thetax/100.0:0)':'').")*100.0)/100.0, '', '');\r\n";
		if(@$showtaxinclusive==1||@$GLOBALS['ectbody3layouttaxinc']) print "if(!taxexmpt&&prodprice!=0){ if(document.getElementById('pricedivti'+prodnum))document.getElementById('pricedivti'+prodnum).innerHTML=formatprice(Math.round((prodprice+(prodprice*thetax/100.0))*100.0)/100.0, '', ''); }else{ if(document.getElementById('taxmsg'+prodnum))document.getElementById('taxmsg'+prodnum).style.display='none'; }\r\n";
		$extracurr='';
		if($currRate1!=0 && $currSymbol1!='') $extracurr="+formatprice(Math.round((prodprice*".$currRate1.")*100.0)/100.0, ' " . $currSymbol1 . "','" . str_replace("'","\'",$currFormat1) . "')+'".str_replace("'","\'",$currencyseparator)."'";
		if($currRate2!=0 && $currSymbol2!='') $extracurr.="+formatprice(Math.round((prodprice*".$currRate2.")*100.0)/100.0, ' " . $currSymbol2 . "','" . str_replace("'","\'",$currFormat2) . "')+'".str_replace("'","\'",$currencyseparator)."'";
		if($currRate3!=0 && $currSymbol3!='') $extracurr.="+formatprice(Math.round((prodprice*".$currRate3.")*100.0)/100.0, ' " . $currSymbol3 . "','" . str_replace("'","\'",$currFormat3) . "');";
		if($extracurr!='') print "document.getElementById('pricedivec'+prodnum).innerHTML=prodprice==0?'':''" . $extracurr . "\r\n";
	}
?>}
function dependantopts(frmnum){
	var objid,thisdep,depopt='',grpid,alldeps=[];
	var allformelms=document.getElementById('ectform'+frmnum).elements;
	for (var iallelems=0; iallelems<allformelms.length; iallelems++){
		objid=allformelms[iallelems];
		thisdep='';
		if(objid.type=='select-one'){
			thisdep=dOP[objid[objid.selectedIndex].value];
		}else if(objid.type=='checkbox'||objid.type=='radio'){
			if(objid.checked)thisdep=dOP[objid.value];
		}
		if(thisdep)alldeps=alldeps.concat(thisdep);
	}
	for(var iallelems=0;iallelems<allformelms.length;iallelems++){
		objid=allformelms[iallelems];
		if(grpid=parseInt(objid.getAttribute("data-optgroup"))){
			if(objid.getAttribute("data-isdep")){
				var isdisabled=(alldeps.indexOf(grpid)<0);
				var haschanged=isdisabled!=objid.disabled;
				objid.disabled=isdisabled;
				objid.style.display=isdisabled?'none':'';
				if(objid.parentNode.tagName=='TD'){
					if(objid.parentNode.parentNode.tagName=='TR')objid.parentNode.parentNode.style.display=isdisabled?'none ':'';
				}else if(objid.parentNode.tagName=='DIV'){
					var parentid=objid.parentNode.id;
					if(parentid.substr(0,4)=='divb')document.getElementById(parentid.replace('divb','diva')).style.display=isdisabled?'none':'';
					objid.parentNode.style.display=isdisabled?'none':'';
				}
				if(haschanged){if(objid.onchange)objid.onchange();else if(objid.onclick)objid.onclick();}
			}
		}
	}
}
var globBakOrdChk;
function ectvalidate(theForm,numoptions,prodnum,stkbyopts,backorder){
	globBakOrdChk=false,oneoutofstock=false;
	for(cnt=0; cnt<numoptions; cnt++){
		if(Math.abs(opttype[cnt])==4){
			var intreg=/^(\d*)$/;var inputs=theForm.getElementsByTagName('input');var tt='';
			for(var i=0;i<inputs.length;i++){if(inputs[i].type=='text'&&inputs[i].id.substr(0,4)=='optm'){if(! inputs[i].value.match(intreg))return(enterDigits(inputs[i]));tt+=inputs[i].value;<?php if($useStockManagement) print "if(inputs[i].value!=''&&oS[inputs[i].name.substr(4)]<=0)oneoutofstock=true;"?>}}if(tt=='')return(enterMultValue());
		}else if(Math.abs(opttype[cnt])==3){
			var voptn=eval('theForm.voptn'+cnt);
			if(voptn.style.display=='none')continue;
			if(optacpc[cnt].length>0){ var re=new RegExp("["+optacpc[cnt]+"]","g"); if(voptn.value.replace(re,"")!='')return(invalidChars(voptn.value.replace(re,""))); }
			if(opttype[cnt]==3&&voptn.value=='')return(enterValue(voptn));
			if(voptn.value.length>(optmaxc[cnt]>0?optmaxc[cnt]:<?php print $txtcollen?>))return(dataLimit(voptn,optmaxc[cnt]>0?optmaxc[cnt]:<?php print $txtcollen?>));
		}else if(Math.abs(opttype[cnt])==2){
			optn=document.getElementById("optn"+prodnum+"x"+cnt);
			if(optn.style.display=='none')continue;
			if(opttype[cnt]==2){ if(optn.selectedIndex==0)return(chooseOption(eval('theForm.optn'+cnt))); }
			if(stkbyopts&&optn.options[optn.selectedIndex].value!=''){ if(!checkStock(optn,optn.options[optn.selectedIndex].value,backorder))return(false); }
		}else if(Math.abs(opttype[cnt])==1){
			havefound='';optns=document.getElementsByName('optn'+prodnum+'x'+cnt);
			if(optns[0].style.display=='none')continue;
			if(opttype[cnt]==1){ for(var i=0; i<optns.length; i++) if(optns[i].checked)havefound=optns[i].value;if(havefound=='')return(chooseOption(optns[0])); }
			if(stkbyopts){ if(havefound!=''){if(!checkStock(optns[0],havefound,backorder))return(false);} }
		}
	}
<?php print @$customvalidator?>
<?php		if($useStockManagement){ ?>
if(backorder&&oneoutofstock&&!globBakOrdChk){if(!confirm("<?php print jscheck($GLOBALS['xxBakOpt'])?>"))return(false);}
<?php		} ?>
if(oos[prodnum]&&oos[prodnum]!=''&&!backorder){<?php if(@$notifybackinstock) print 'notifyinstock(true,otid[prodnum],rid[prodnum],0);'; else print 'alert("'.jscheck($GLOBALS['xxOptOOS']).'");'?>document.getElementById(oos[prodnum]).focus();return(false);}
return (true);
}
<?php	} // doaddprodoptions
?>
function quantup(tobjid,qud){
	tobj=document.getElementById('w'+tobjid+'quant');
	if(isNaN(parseInt(tobj.value)))tobj.value=1;else if(qud==1)tobj.value=parseInt(tobj.value)+1;else tobj.value=Math.max(1,parseInt(tobj.value)-1);
	if(document.getElementById('qnt'+tobjid+'x'))document.getElementById('qnt'+tobjid+'x').value=tobj.value;
}/* ]]> */
</script><?php
		$pricecheckerisincluded=TRUE;
	}
}
function updatepricescript(){
	global $rs,$extraimages,$giftcertificateid,$donationid,$useStockManagement,$showinstock,$noshowoptionsinstock,$prodoptions,$Count,$allimages,$numallimages,$alllgimages,$numalllgimages;
	$prodoptions='';
	$sSQL="SELECT poOptionGroup,optType,optFlags,optTxtMaxLen,optAcceptChars,0 AS isDepOpt FROM prodoptions INNER JOIN optiongroup ON optiongroup.optGrpID=prodoptions.poOptionGroup WHERE poProdID='".$rs['pId']."' AND NOT (poProdID='".$giftcertificateid."' OR poProdID='".$donationid."') ORDER BY poID";
	$result=ect_query($sSQL) or ect_error();
	for($rowcounter=0;$rowcounter<ect_num_rows($result);$rowcounter++){
		$prodoptions[$rowcounter]=ect_fetch_assoc($result);
	}
	ect_free_result($result);
	if(is_array($allimages) && $numallimages>1){
		print '<script type="text/javascript">/* <![CDATA[ */' . "\r\n" . 'pIX['.$Count.']=0;pIM['.$Count."]='".encodeimage($allimages[0]['imageSrc']).'*';
		$extraimages=1;
		for($index=1;$index<$numallimages;$index++){
			print encodeimage($allimages[$index]['imageSrc']).'*'; $extraimages++;
		}
		print "';\r\n";
		if(is_array($alllgimages) && $numalllgimages>1){
			print 'pIML['.$Count."]='".encodeimage($alllgimages[0]['imageSrc']).'*';
			for($index=1;$index<$numalllgimages;$index++)
				print encodeimage($alllgimages[$index]['imageSrc']).'*';
			print "';\r\n";
		}
		print '/* ]]> */</script>';
	}
}
function checkDPs($currcode){
	if($currcode=='JPY'||$currcode=='TWD') return(0); else return(2);
}
function checkCurrencyRates($currConvUser,$currConvPw,$currLastUpdate,&$currRate1,$currSymbol1,&$currRate2,$currSymbol2,&$currRate3,$currSymbol3){
	global $countryCurrency,$usecurlforfsock,$pathtocurl,$curlproxy;
	$ccsuccess=TRUE;
	if($currConvUser!="" && $currConvPw!="" && (strtotime($currLastUpdate) < time()-(60*60*24))){
		$str="";
		if($currSymbol1!='') $str.='&curr=' . $currSymbol1;
		if($currSymbol2!='') $str.='&curr=' . $currSymbol2;
		if($currSymbol3!='') $str.='&curr=' . $currSymbol3;
		if($str==''){
			ect_query("UPDATE admin SET currLastUpdate='" . date('Y-m-d H:i:s', time()) . "'") or ect_error();
			return;
		}
		$str='?source=' . $countryCurrency . '&user=' . $currConvUser . '&pw=' . $currConvPw . $str;
		if(@$usecurlforfsock){
			if(@$pathtocurl!=''){
				exec($pathtocurl . ' --data-binary \'X\' http://www.ecommercetemplates.com/currencyxml.asp' . $str, $res, $retvar);
				$sXML=implode("\n",$res);
			}else
				$ccsuccess=callcurlfunction('http://www.ecommercetemplates.com/currencyxml.asp' . $str, 'X', $sXML, '', $errormsg, FALSE);
		}else{
			$header='POST /currencyxml.asp' . $str . " HTTP/1.0\r\n";
			$header.="Content-Type: application/x-www-form-urlencoded\r\n";
			$header.="Content-Length: 1\r\n\r\n";
			$fp=fsockopen('www.ecommercetemplates.com', 80, $errno, $errstr, 30);
			if (!$fp){
				$errormsg=$errstr.' ('.$errno.')';
				$ccsuccess=FALSE;
			}else{
				fputs($fp, $header.'X');
				$sXML='';
				while (!feof($fp))
					$sXML.=fgets ($fp, 1024);
			}
		}
		if($ccsuccess){
			$xmlDoc=new vrXMLDoc($sXML);
			$nodeList=$xmlDoc->nodeList->childNodes[0];
			for($j=0; $j < $nodeList->length; $j++){
				if($nodeList->nodeName[$j]=='currError'){
					print $nodeList->nodeValue[$j];
					$ccsuccess=FALSE;
				}elseif($nodeList->nodeName[$j]=='selectedCurrency'){
					$e=$nodeList->childNodes[$j];
					$currRate=0;
					for($i=0; $i < $e->length; $i++){
						if($e->nodeName[$i]=='currSymbol')
							$currSymbol=$e->nodeValue[$i];
						elseif($e->nodeName[$i]=='currRate')
							$currRate=$e->nodeValue[$i];
					}
					if($currSymbol1==$currSymbol){
						$currRate1=$currRate;
						ect_query('UPDATE admin SET currRate1=' . $currRate . ' WHERE adminID=1') or ect_error();
					}
					if($currSymbol2==$currSymbol){
						$currRate2=$currRate;
						ect_query('UPDATE admin SET currRate2=' . $currRate . ' WHERE adminID=1') or ect_error();
					}
					if($currSymbol3==$currSymbol){
						$currRate3=$currRate;
						ect_query('UPDATE admin SET currRate3=' . $currRate . ' WHERE adminID=1') or ect_error();
					}
				}
			}
			if($ccsuccess) ect_query("UPDATE admin SET currLastUpdate='" . date('Y-m-d H:i:s', time()) . "'");
		}
	}
}
function getsectionids($thesecid, $delsections){
	global $returnalltopsections;
	$resolvedids='';
	$secarr=explode(',', $thesecid);
	$secid=''; $addcomma=''; $addcomma2='';
	foreach($secarr as $sect){
		if(is_numeric(trim($sect))) $secid.=$addcomma . $sect; $addcomma=',';
	}
	if($secid=='') $secid='0';
	$iterations=0;
	$iteratemore=TRUE;
	if(@$_SESSION['clientLoginLevel']!='') $minloglevel=$_SESSION['clientLoginLevel']; else $minloglevel=0;
	if($delsections) $nodel=''; else $nodel='sectionDisabled<=' . $minloglevel . ' AND ';
	while($iteratemore && $iterations<10){
		$sSQL2='SELECT DISTINCT sectionID,rootSection FROM sections WHERE ' . $nodel . '(topSection IN (' . $secid . ')';
		if($iterations==0) $sSQL2.=' OR (sectionID IN (' . $secid . ') AND rootSection=1))'; else $sSQL2.=')';
		$secid='';
		$iteratemore=FALSE;
		$result2=ect_query($sSQL2) or ect_error();
		$addcomma='';
		while($rs2=ect_fetch_assoc($result2)){
			if($rs2['rootSection']==0){
				if(@$returnalltopsections){ $resolvedids.=$addcomma2 . $rs2['sectionID']; $addcomma2=','; }
				$iteratemore=TRUE;
				$secid.=$addcomma . $rs2['sectionID'];
				$addcomma=',';
			}else{
				$resolvedids.=$addcomma2 . $rs2['sectionID'];
				$addcomma2=',';
			}
		}
		$iterations++;
	}
	if($resolvedids=='') $resolvedids='0';
	return($resolvedids);
}
function callcurlfunction($cfurl, $cfxml, &$cfres, $cfcert, &$cferrmsg, $settimeouts){
	global $curlproxy,$pathtocurl,$xmlfnheaders,$debugmode,$emailAddr,$htmlemails,$http_status;
	$cfsuccess=TRUE;
	if(@$pathtocurl!=''){
		exec($pathtocurl . ($cfcert!='' ? ' -E \'' . $cfcert . '\'' : '') . ' --data-binary ' . escapeshellarg($cfxml) . ' ' . $cfurl, $cfres, $retvar);
		$cfres=implode("\n",$cfres);
		if($cfres==''){ $cferrmsg='No response from path: '.$pathtocurl; $cfsuccess=FALSE; }
	}else{
		if(!function_exists('curl_init')||!$ch=curl_init()) {
			$cferrmsg='cURL package not installed in PHP. Set \$pathtocurl parameter.';
			$cfsuccess=FALSE;
		}else{
			curl_setopt($ch, CURLOPT_URL, $cfurl);
			if(is_array($xmlfnheaders))curl_setopt($ch, CURLOPT_HTTPHEADER, $xmlfnheaders);
			if($cfcert!='') curl_setopt($ch, CURLOPT_SSLCERT, $cfcert); 
			if($cfxml!='')curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			if($cfxml!='')curl_setopt($ch, CURLOPT_POSTFIELDS, $cfxml);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if($settimeouts){
				if($settimeouts>10)curl_setopt($ch, CURLOPT_TIMEOUT, $settimeouts); else curl_setopt($ch, CURLOPT_TIMEOUT, 120);
			}
			if(@$curlproxy!='')
				curl_setopt($ch, CURLOPT_PROXY, $curlproxy);
			$cfres=curl_exec($ch);
			$http_status=curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if(curl_error($ch)!=''){
				if($cfcert!='' && ! is_file($cfcert)){
					$cferrmsg='Certificate file not found: ' . $cfcert . '<br />';
				}else
					$cferrmsg=curl_error($ch) . '<br />';
				$cfsuccess=FALSE;
			}else
				curl_close($ch);
		}
	}
	if($debugmode){ dosendemail($emailAddr,$emailAddr,'','PHP XML Function Debug',(@$GLOBALS['htmlemails']?str_replace('<','&lt;',$cfxml):$cfxml).$GLOBALS['emlNl'].$GLOBALS['emlNl'].(@$GLOBALS['htmlemails']?str_replace('<','&lt;',$cfres):$cfres).$GLOBALS['emlNl'].$GLOBALS['emlNl'].$cfsuccess); }
	$xmlfnheaders='';
	return($cfsuccess);
}
function getpayprovdetails($ppid,&$ppdata1,&$ppdata2,&$ppdata3,&$ppdemo,&$ppmethod){
	$sSQL="SELECT payProvData1,payProvData2,payProvData3,payProvDemo,payProvMethod FROM payprovider WHERE payProvEnabled=1 AND payProvID='" . escape_string($ppid) . "'";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		$ppdata1=trim($rs['payProvData1']);
		$ppdata2=trim($rs['payProvData2']);
		$ppdata3=trim($rs['payProvData3']);
		$ppdemo=((int)$rs['payProvDemo']==1);
		$ppmethod=(int)$rs['payProvMethod'];
	}else
		return(FALSE);
	return(TRUE);
}
function writehiddenvar($hvname,$hvval){
print '<input type="hidden" name="' . $hvname . '" value="' . htmlspecials($hvval) . '" />' . "\r\n";
}
function writehiddenidvar($hvname,$hvval){
print '<input type="hidden" name="' . $hvname . '" id="' . $hvname . '" value="' . htmlspecials($hvval) . '" />' . "\r\n";
}
function ppsoapheader($username, $password, $signature){
return '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Header><RequesterCredentials xmlns="urn:ebay:api:PayPalAPI"><Credentials xmlns="urn:ebay:apis:eBLBaseComponents">' . (strpos($username,'@AB@')===FALSE ? '<Username>' . $username . '</Username><Password>' . $password . '</Password>' . ($signature!='' ? '<Signature>' . $signature . '</Signature>' : '') : '<Subject>'.str_replace('@AB@','',$username).'</Subject>') . '</Credentials></RequesterCredentials></soap:Header>';
}
function getoptpricediff($opd,$theid,$theexp,$pprice,&$pstock){
	global $WSP;
	$retval=(double)$opd;
	if($theexp!='' && substr($theexp, 0, 1)!='!'){
		$theexp=str_replace('%s', $theid, $theexp);
		if(strpos($theexp, ' ')!==FALSE){ // Search and replace
			$exparr=explode(' ', $theexp, 2);
			$theid=str_replace($exparr[0], $exparr[1], $theid);
		}else
			$theid=$theexp;
		$sSQL='SELECT '.$WSP."pPrice,pInStock FROM products WHERE pID='".escape_string($theid)."'";
		$result3=ect_query($sSQL) or ect_error();
		if($rs3=ect_fetch_assoc($result3)){ $retval=$rs3['pPrice']-$pprice; $pstock=$rs3['pInStock']; }
		ect_free_result($result3);
	}
	return($retval);
}
function addtoaltids($theexp, &$altidarr, &$altids){
	$theexp=trim($theexp);
	if($theexp!='' && substr($theexp, 0, 1)!='!'){
		if(! is_array($altidarr)){
			$altidarr=explode(' ', trim($altids));
			$altids='';
		}
		foreach($altidarr as $theid){
			if(strpos($altids,$theid.' ')===FALSE) $altids.=$theid . ' ';
			$theexpa=str_replace('%s', $theid, $theexp);
			if(strpos($theexpa, ' ')!==FALSE){ // Search and replace
				$exparr=explode(' ', $theexpa, 2);
				$theid=str_replace($exparr[0], $exparr[1], $theid);
			}else
				$theid=$theexpa;
			if(strpos($altids,$theid.' ')===FALSE) $altids.=$theid . ' ';
		}
	}
}
$optjsunique=',';
function addtooptionsjs(&$optionsjs, $isdetail, $origoptpricediff){
	global $rs2,$useStockManagement,$optjsunique,$magictoolboxproducts;
	if(strpos($optjsunique,','.$rs2['optID'].',')===FALSE){
		if($useStockManagement) $optionsjs.='oS['.$rs2['optID'].']='.$rs2['optStock'].';';
		if(($rs2['optRegExp']=='' || substr($rs2['optRegExp'],0,1)=='!') && $origoptpricediff!=0)$optionsjs.='op['.$rs2['optID'].']='.$origoptpricediff.';';
		if($rs2['optRegExp']!='' && substr($rs2['optRegExp'],0,1)!='!')$optionsjs.='or['.$rs2['optID']."]='".$rs2['optRegExp']."';";
		$optionsjs.='ot['.$rs2['optID'].']="'.jscheck($rs2[getlangid('optName',32)]).'";';
		if(trim($rs2['optAlt'.($isdetail?'Large':'').'Image'])!='') $optionsjs.='aIM['.$rs2['optID']."]='".encodeimage($rs2['optAlt'.($isdetail?'Large':'').'Image'])."';";
		if(trim($rs2['optDependants'])!='') $optionsjs.='dOP['.$rs2['optID'].']=['.$rs2['optDependants'].'];';
		if(@$magictoolboxproducts!='' && ! $isdetail && trim($rs2['optAltLargeImage'])!='') $optionsjs.='aIML['.$rs2['optID']."]='".encodeimage($rs2['optAltLargeImage'])."';";
		$optionsjs.="\r\n";
		$optjsunique.=$rs2['optID'].',';
	}
}
function displayproductoptions($grpnmstyle,$grpnmstyleend,&$optpricediff,$thetax,$isdetail,&$hasmulti,&$optionsjs){
	global $rs,$rs2,$prodoptions,$useStockManagement,$hideoptpricediffs,$pricezeromessage,$noprice,$WSP,$OWSP,$Count,$optionshavestock,$noshowoptionsinstock,$showinstock,$showtaxinclusive,$defimagejs,$multipurchasecolumns,$startlink,$endlink,$noselectoptionlabel,$optjsunique,$tleft,$tright,$usecsslayout,$removedefaultoptiontext,$mobilebrowser;
	$optshtml=$optionsjs=$defjs=$altidarr=$dependantoptions='';
	$optpricediff=$numdependantoptions=$rowcounter=0;
	$altids=$rs['pId'];
	$hasmulti=FALSE;
	$saveoptionsjs=$optionsjs;
	$saveoptjsunique=$optjsunique;
	$maxindex=count($prodoptions)-1;
	while($rowcounter<=$maxindex){
		$theopt=$prodoptions[$rowcounter];
		$opthasstock=FALSE;
		$sSQL='SELECT optID,'.getlangid('optName',32).','.getlangid('optGrpName',16).',' . $OWSP . 'optPriceDiff,optType,optGrpSelect,optFlags,optTxtMaxLen,optTxtCharge,optStock,optPriceDiff AS optDims,optDefault,optAltImage,optAltLargeImage,optRegExp,'.getlangid('optPlaceholder',16).','.($theopt['isDepOpt']==1?"'' AS ":'').'optDependants,optTooltip FROM options LEFT JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optGroup=' . $theopt['poOptionGroup'] . ' ORDER BY optID';
		$result=ect_query($sSQL) or ect_error();
		if($rs2=ect_fetch_assoc($result)){
			$opttooltip=trim($rs2['optTooltip']);
			if($opttooltip!=''){
				if(substr($opttooltip,0,2)=='##')
					$opttooltip=substr($opttooltip,2);
				else
					$opttooltip='&nbsp;<span class="opttooltip" '.($mobilebrowser?'onclick="thisstyle=this.getElementsByTagName(\'span\')[0].style;thisstyle.display=thisstyle.display==\'inline\'?\'none\':\'inline\'"':'onmouseover="this.getElementsByTagName(\'span\')[0].style.display=\'inline\'" onmouseout="this.getElementsByTagName(\'span\')[0].style.display=\'none\'"').'><img src="images/ectinfo.png" alt="" style="vertical-align:text-bottom" /><span style="display:none;border:1px solid;background:#EEE;position:absolute;z-index:100">'.$opttooltip.'</span></span>';
			}
			if(abs((int)$rs2['optType'])==3){ // Text
				$opthasstock=TRUE;
				$fieldHeight=round(((double)($rs2['optDims'])-(int)($rs2['optDims']))*100.0);
				$optshtml.=(@$usecsslayout ? '<div'.($theopt['isDepOpt']==1?' id="diva'.$Count.'x'.$rowcounter.'"':'') : '<tr><td align="' . $tright . '" width="30%"') . ' class="optiontext' . ($isdetail ? ' detailoptiontext' : '') . '">' . $grpnmstyle . '<label for="optn'.$Count.'x'.$rowcounter.'">' . $rs2[getlangid('optGrpName',16)] . '</label>' . $opttooltip . $grpnmstyleend . (@$usecsslayout ? '</div><div'.($theopt['isDepOpt']==1?' id="divb'.$Count.'x'.$rowcounter.'"':'') : '</td><td align="' . $tleft . '"') . ' class="option' . ($isdetail ? ' detailoption' : '') . '"> <input data-optgroup="'.$theopt['poOptionGroup'].'" '.($theopt['isDepOpt']==1?'data-isdep="1" ':'').'type="hidden" name="optn' . $rowcounter . '" value="' . $rs2['optID'] . '" />';
				if($fieldHeight!=1){
					$optshtml.='<textarea data-optgroup="'.$theopt['poOptionGroup'].'" '.($theopt['isDepOpt']==1?'data-isdep="1" ':'').'class="prodoption'.($isdetail?' detailprodoption':'').'" name="voptn' . $rowcounter . '" id="optn'.$Count.'x'.$rowcounter.'" cols="' . (int)$rs2["optDims"] . '" rows="' . $fieldHeight . '"' . (@$removedefaultoptiontext&&trim($rs2[getlangid('optName',32)])!=''?' onfocus="if(this.value==\'' . jsescape($rs2[getlangid('optName',32)]) . '\')this.value=\'\';"':'') . (trim($rs2[getlangid('optPlaceholder',16)])!=''?' placeholder="' . $rs2[getlangid('optPlaceholder',16)] . '"' : '') . ($theopt['isDepOpt']==1?' disabled="disabled"':'') . '>';
					$optshtml.=$rs2[getlangid('optName',32)] . '</textarea>';
				}else
					$optshtml.='<input data-optgroup="'.$theopt['poOptionGroup'].'" '.($theopt['isDepOpt']==1?'data-isdep="1" ':'').'type="text" class="prodoption'.($isdetail?' detailprodoption':'').'" maxlength="255" name="voptn' . $rowcounter . '" id="optn'.$Count.'x'.$rowcounter.'" size="' . (int)$rs2['optDims'] . '" value="' . htmldisplay($rs2[getlangid('optName',32)]) . '"' . (@$removedefaultoptiontext&&trim($rs2[getlangid('optName',32)])!=''?' onfocus="if(this.value==\'' . jsescape($rs2[getlangid('optName',32)]) . '\')this.value=\'\';"':'') . (trim($rs2[getlangid('optPlaceholder',16)])!=''?' placeholder="' . $rs2[getlangid('optPlaceholder',16)] . '"' : '') . ($theopt['isDepOpt']==1?' disabled="disabled"':'').' />';
				$optshtml.=(@$usecsslayout ? '</div>' : '</td></tr>');
			}elseif(abs((int)$rs2['optType'])==1){ // Checkbox / Radio
				$optshtml.=(@$usecsslayout ? '<div'.($theopt['isDepOpt']==1?' id="diva'.$Count.'x'.$rowcounter.'"':'') : '<tr><td align="' . $tright . '" valign="baseline" width="30%"') . ' class="optiontext' . ($isdetail ? ' detailoptiontext' : '') . '">' . $grpnmstyle . $rs2[getlangid('optGrpName',16)] . $opttooltip . $grpnmstyleend . (@$usecsslayout ? '</div><div'.($theopt['isDepOpt']==1?' id="divb'.$Count.'x'.$rowcounter.'"':'') : '</td><td align="' . $tleft . '"') . ' class="option' . ($isdetail ? ' detailoption' : '') . '"> ';
				$defjs.='updateoptimage('.$Count.','.$rowcounter.',1);';
				$index=0;
				do {
					if(trim($rs2['optDependants'])!='') $dependantoptions.=','.$rs2['optDependants'];
					$origoptpricediff=getoptpricediff($rs2['optPriceDiff'],$rs['pId'],trim($rs2['optRegExp']),$rs['pPrice'],$stocknotused);
					addtoaltids($rs2['optRegExp'], $altidarr, $altids);
					$optshtml.='<input type="'.(ect_num_rows($result)==1?'checkbox':'radio').'" data-optgroup="'.$theopt['poOptionGroup'].'" '.($theopt['isDepOpt']==1?'data-isdep="1" ':'').'class="prodoption'.($isdetail?' detailprodoption':'').'" style="vertical-align:middle" onclick="updateoptimage('.$Count.','.$rowcounter.',1)" name="optn'.$Count.'x'.$rowcounter.'" ';
					if((int)$rs2['optDefault']!=0) $optshtml.='checked="checked" ';
					$optshtml.='value="' . $rs2['optID'] . '" /><span id="optn'.$Count.'x'.$rowcounter.'y'.$index.'"';
					if($useStockManagement && $rs['pStockByOpts']!=0 && $rs2['optStock']<=0 && trim($rs2['optRegExp'])=='') $optshtml.=' class="oostock" '; else $opthasstock=TRUE;
					$optshtml.='>' . $rs2[getlangid('optName',32)];
					if(@$hideoptpricediffs!=TRUE && $origoptpricediff!=0 && trim($rs2['optRegExp'])==''){
						$optshtml.=' (';
						if($origoptpricediff>0) $optshtml.='+';
						if(($rs2['optFlags']&1)==1)$pricediff=($rs['pPrice']*$origoptpricediff)/100.0;else$pricediff=$origoptpricediff;
						if(@$showtaxinclusive===2 && ($rs['pExemptions'] & 2)!=2) $pricediff+=($pricediff*$thetax/100.0);
						$optshtml.=FormatEuroCurrency($pricediff) . ')';
						if($rs2['optDefault']!=0) $optpricediff+=$pricediff;
					}
					if($useStockManagement && @$showinstock==TRUE && @$noshowoptionsinstock!=TRUE && (int)$rs['pStockByOpts']!=0) $optshtml.=str_replace('%s', $rs2['optStock'], $GLOBALS['xxOpSkTx']);
					$optshtml.='</span>';
					if(($rs2['optFlags'] & 4)!=4) $optshtml.="<br />\r\n";
					$index++;
					addtooptionsjs($optionsjs, $isdetail, $origoptpricediff);
				} while($rs2=ect_fetch_assoc($result));
				unset($altidarr);
				$optshtml.=(@$usecsslayout ? '</div>' : '</td></tr>');
			}elseif(abs((int)$rs2['optType'])==4){ // Multi
				if(@$multipurchasecolumns=='') $multipurchasecolumns=2;
				$colwid=(int)(100/$multipurchasecolumns);
				if((int)$rs2['optGrpSelect']!=0 && ! $isdetail){
					$hasmulti=2;
					$optshtml='';
					$optionsjs='';
					$altids=$rs['pId'];
					$defjs='';
					$optionsjs=$saveoptionsjs;
					$optjsunique=$saveoptjsunique;
					$opthasstock=TRUE;
				}else{
					$optshtml.=(@$usecsslayout ? '<div' : '<tr><td align="center" colspan="2">&nbsp;<br /><table') . ' class="multioptiontable">';
					$index=0;
					do {
						$stocklevel=$rs2['optStock'];
						$origoptpricediff=getoptpricediff($rs2['optPriceDiff'],$rs['pId'],trim($rs2['optRegExp']),$rs['pPrice'],$stocklevel);
						addtoaltids($rs2['optRegExp'], $altidarr, $altids);
						if($useStockManagement && $rs['pStockByOpts']!=0 && $stocklevel<=0 && trim($rs2['optRegExp'])=='' && $rs['pBackOrder']!=0) $oostock=TRUE; else $oostock=FALSE;
						if(($index % $multipurchasecolumns)==0 && ! @$usecsslayout) $optshtml.='<tr>';
						$optshtml.=(@$usecsslayout ? '<div' : '<td width="'.$colwid.'%" align="' . $tleft . '" style="white-space:nowrap"') . ' class="optiontext' . ($isdetail ? ' detailoptiontext' : '') . ' multioptiontext' . ($isdetail ? ' detailmultioptiontext' : '') . '">';
						if(trim($rs2['optAlt'.($isdetail?'Large':'').'Image'])!='') $optshtml.='&nbsp;&nbsp;<img class="multiimage" src="'.trim($rs2['optAlt'.($isdetail?'Large':'').'Image']).'" alt="" />';
						$optshtml.='&nbsp;&nbsp;<input data-optgroup="'.$theopt['poOptionGroup'].'" '.($theopt['isDepOpt']==1?'data-isdep="1" ':'').'type="text" maxlength="5" name="optm'.$rs2['optID'].'" id="optm'.$Count.'x'.$rowcounter.'y'.$index.'" size="1" '.($oostock?'style="background-color:#EBEBE4" disabled="disabled"':'').'/>';
						$optshtml.='<label for="optm'.$Count.'x'.$rowcounter.'y'.$index.'"><span id="optx'.$Count.'x'.$rowcounter.'y'.$index.'" class="multioption';
						if($oostock) $optshtml.=' oostock"'; else{ $optshtml.='"'; $opthasstock=TRUE; }
						$optshtml.='> - ' . $rs2[getlangid('optName',32)];
						if(@$hideoptpricediffs!=TRUE && $origoptpricediff!=0){
							$optshtml.=' (';
							if($origoptpricediff > 0) $optshtml.='+';
							if(($rs2['optFlags']&1)==1 && trim($rs2['optRegExp'])=='')$pricediff=($rs['pPrice']*$origoptpricediff)/100.0;else $pricediff=$origoptpricediff;
							if(@$showtaxinclusive===2 && ($rs['pExemptions'] & 2)!=2) $pricediff+=($pricediff*$thetax/100.0);
							$optshtml.=FormatEuroCurrency($pricediff) . ')';
						}
						$optshtml.='</span></label>' . (@$usecsslayout ? '</div>' : '</td>');
						$index++;
						if(($index % $multipurchasecolumns)==0 && ! @$usecsslayout) $optshtml.='</tr>';
						addtooptionsjs($optionsjs, $isdetail, $origoptpricediff);
					} while($rs2=ect_fetch_assoc($result));
					if(($index % $multipurchasecolumns)!=0){
						while(($index % $multipurchasecolumns)!=0 && ! @$usecsslayout){
							if($index>=$multipurchasecolumns) $optshtml.='<td>&nbsp;</td>';
							$index++;
						}
						if(($index % $multipurchasecolumns)==0 && ! @$usecsslayout) $optshtml.='</tr>';
					}
					$hasmulti=1;
					$optshtml.=(@$usecsslayout ? '</div>' : '</table></td></tr>');
				}
			}else{ // Select
				$optshtml.=(@$usecsslayout ? '' : '<tr>') . (@$noselectoptionlabel!=TRUE ? '<' . (@$usecsslayout ? 'div'.($theopt['isDepOpt']==1?' id="diva'.$Count.'x'.$rowcounter.'"':'') : 'td align="' . $tright . '" width="30%"') . ' class="optiontext' . ($isdetail ? ' detailoptiontext' : '') . '">' . $grpnmstyle . '<label for="optn'.$Count.'x'.$rowcounter.'">' . $rs2[getlangid('optGrpName',16)] . '</label>' . $opttooltip . $grpnmstyleend . (@$usecsslayout ? '</div><div'.($theopt['isDepOpt']==1?' id="divb'.$Count.'x'.$rowcounter.'"':'') : '</td><td align="' . $tleft . '"') . ' class="option' . ($isdetail ? ' detailoption' : '') . '"> ' : (@$usecsslayout ? '<div' : '<td colspan="2"') . ' class="prodoption selectoption'.($isdetail?' detailprodoption':'').'">') . '<select data-optgroup="'.$theopt['poOptionGroup'].'" '.($theopt['isDepOpt']==1?'data-isdep="1" ':'').'class="prodoption'.($isdetail?' detailprodoption':'').'" onchange="updateoptimage('.$Count.','.$rowcounter.')" name="optn' . $rowcounter . '" id="optn'.$Count.'x'.$rowcounter.'" '.($theopt['isDepOpt']==1?'disabled="disabled" ':'').'size="1">';
				$defjs.="document.getElementById('optn".$Count.'x'.$rowcounter."').onchange();";
				$gotdefaultdiff=FALSE;
				$firstpricediff=0;
				$origoptpricediff=$rs2['optPriceDiff'];
				if((int)$rs2['optGrpSelect']!=0)
					$optshtml.='<option value="">' . $GLOBALS['xxPlsSel'] . '</option>';
				else
					if(($rs2['optFlags']&1)==1)$firstpricediff=($rs['pPrice']*$origoptpricediff)/100.0;else $firstpricediff=$origoptpricediff;
				do {
					if(trim($rs2['optDependants'])!='') $dependantoptions.=','.$rs2['optDependants'];
					$origoptpricediff=getoptpricediff($rs2['optPriceDiff'],$rs['pId'],trim($rs2['optRegExp']),$rs['pPrice'],$stocknotused);
					addtoaltids($rs2['optRegExp'], $altidarr, $altids);
					$optshtml.='<option ';
					if($useStockManagement && $rs['pStockByOpts']!=0 && $rs2['optStock'] <= 0 && trim($rs2['optRegExp'])=='') $optshtml.='class="oostock" '; else $opthasstock=TRUE;
					$optshtml.='value="' . $rs2['optID'] . '"'.((int)$rs2['optDefault']!=0?' selected="selected"':'').'>' . $rs2[getlangid('optName',32)];
					if(@$hideoptpricediffs!=TRUE && trim($rs2['optRegExp'])==''){
						if($origoptpricediff!=0){
							$optshtml.=' (';
							if($origoptpricediff>0) $optshtml.='+';
							if(($rs2['optFlags']&1)==1)$pricediff=($rs['pPrice']*$origoptpricediff)/100.0;else $pricediff=$origoptpricediff;
							if(@$showtaxinclusive===2 && ($rs['pExemptions'] & 2)!=2) $pricediff+=($pricediff*$thetax/100.0);
							$optshtml.=FormatEuroCurrency($pricediff) . ')';
							if($rs2['optDefault']!=0)$optpricediff+=$pricediff;
						}
						if($rs2['optDefault']!=0)$gotdefaultdiff=TRUE;
					}
					if($useStockManagement && @$showinstock==TRUE && @$noshowoptionsinstock!=TRUE && (int)$rs['pStockByOpts']!=0) $optshtml.=str_replace('%s', $rs2['optStock'], $GLOBALS['xxOpSkTx']);
					$optshtml.="</option>\n";
					addtooptionsjs($optionsjs, $isdetail, $origoptpricediff);
				} while($rs2=ect_fetch_assoc($result));
				unset($altidarr);
				if(@$hideoptpricediffs!=TRUE && ! $gotdefaultdiff) $optpricediff+=$firstpricediff;
				$optshtml.='</select>' . (@$usecsslayout ? '</div>' : '</td></tr>');
			}
		}
		ect_free_result($result);
		$optionshavestock=$optionshavestock && ($opthasstock||$theopt['isDepOpt']==1);
		$dependantoptions=commaseplist($dependantoptions);
		if($hasmulti==2) break;
		if($dependantoptions!=''){
			$sSQL="SELECT optGrpID AS poOptionGroup,optType,optFlags,optTxtMaxLen,optAcceptChars,1 AS isDepOpt FROM optiongroup WHERE optGrpID IN (".$dependantoptions.") ORDER BY FIND_IN_SET(optGrpID,'".$dependantoptions."')";
			$result2=ect_query($sSQL) or ect_error();
			if(($numsuboptions=ect_num_rows($result2))>0){
				$itemstomove=$maxindex-$rowcounter;
				$maxindex+=$numsuboptions;
				for($soindex=0;$soindex<$itemstomove;$soindex++){
					$moveto=$maxindex-$soindex;
					$movefrom=$rowcounter+$itemstomove-$soindex;
					$prodoptions[$moveto]=$prodoptions[$movefrom];
				}
				for($soindex=1;$soindex<=$numsuboptions;$soindex++){
					$rs2=ect_fetch_assoc($result2);
					$prodoptions[$rowcounter+$soindex]=$rs2;
				}
			}
			ect_free_result($result2);
			$dependantoptions='';
		}
		$rowcounter++;
	}
	$sSQL='SELECT pID,'.$WSP."pPrice,pInStock FROM products WHERE pID IN ('".str_replace(' ', "','", $altids)."')";
	$result=ect_query($sSQL) or ect_error();
	while($rs2=ect_fetch_assoc($result)){
		$sSQL="SELECT imageSrc FROM productimages WHERE imageProduct='".escape_string($rs2['pID'])."' AND imageNumber=0 AND imageType=".($isdetail?'1':'0').' LIMIT 0,1';
		$result3=ect_query($sSQL) or ect_error();
		if($rs3=ect_fetch_assoc($result3)) $pi=encodeimage($rs3['imageSrc']); else $pi='';
		ect_free_result($result3);
		if($pi!=''){
			$sSQL="SELECT imageSrc FROM productimages WHERE imageProduct='".escape_string($rs2['pID'])."' AND imageNumber=0 AND imageType=".($isdetail?'2':'1').' LIMIT 0,1';
			$result3=ect_query($sSQL) or ect_error();
			if($rs3=ect_fetch_assoc($result3)) $pi.='*'.encodeimage($rs3['imageSrc']);
			ect_free_result($result3);
		}
		$optionsjs.="sz('".$rs2['pID']."',".$rs2['pPrice'];
		if($useStockManagement) $optionsjs.=','.$rs2['pInStock'];
		$optionsjs.=",'".jsescapel($pi)."');";
	}
	ect_free_result($result);
	if($hasmulti!=2) $defimagejs.='updateprice'.$Count.'();'.$defjs;
	if($prodoptions!=''){
		$optionsjs.='function setvals'.$Count."(){\r\n";
		foreach($prodoptions as $rowcounter => $theopt){
			$optionsjs.='optacpc[' . $rowcounter . "]='" . jsescape($theopt['optAcceptChars']) . "';optmaxc[" . $rowcounter . ']=' . $theopt['optTxtMaxLen'] . ';opttype['.$rowcounter.']=' . (int)$theopt['optType'] . ';optperc['.$rowcounter.']=' . (($theopt['optFlags'] & 1)==1 ? 'true' : 'false') . ";\r\n";
		}
		$optionsjs.="}\r\n";
		$optionsjs.='function updateprice'.$Count."(){\r\n";
		$optionsjs.='setvals'.$Count.'();';
		$optionsjs.='updateprice('.count($prodoptions).','.$Count.','.$rs['pPrice'].",'".$rs['pId']."',".$thetax.','.($useStockManagement && $rs['pStockByOpts']!=0 ? 'true' : 'false').','.(($rs['pExemptions'] & 2)==2 ? 'true' : 'false').','.($rs['pBackOrder']!=0 ? 'true' : 'false').');';
		$optionsjs.="}\r\n";
	}
	return($optshtml);
}
function displayformvalidator(){
	global $optjs,$Count,$prodoptions,$useStockManagement,$rs;
	$optjs.='function formvalidator'.$Count."(theForm){\r\n";
	if($prodoptions!=''){
		$optjs.='setvals'.$Count.'();';
		$optjs.='return(ectvalidate(theForm,'.count($prodoptions).','.$Count.','.($useStockManagement && $rs['pStockByOpts']!=0 ? 'true' : 'false').','.($rs['pBackOrder']!=0 ? 'true' : 'false').'));';
	}else
		$optjs.='return(true);';
	$optjs.="}\r\n";
}
function CalcHmacSha1($data, $key){
    $blocksize=64;
    $hashfunc='sha1';
    if (strlen($key) > $blocksize){
        $key=pack('H*', $hashfunc($key));
    }
    $key=str_pad($key, $blocksize, chr(0x00));
    $ipad=str_repeat(chr(0x36), $blocksize);
    $opad=str_repeat(chr(0x5c), $blocksize);
    $hmac=pack('H*', $hashfunc(($key^$opad).pack('H*', $hashfunc(($key^$ipad).$data))));
    return $hmac;
}
function encodeemailsubject($in_str, $charset){
	$out_str=$in_str;
	if($out_str && $charset){
		// define start delimimter, end delimiter and spacer
		$end="?=";
		$start="=?" . $charset . "?B?";
		$spacer=$end . "\r\n " . $start;
		// determine length of encoded text within chunks and ensure length is even
		$length=75 - strlen($start) - strlen($end);
		$length=floor($length/2) * 2;
		// encode the string and split it into chunks with spacers after each chunk
		$out_str=base64_encode($out_str);
		$out_str=chunk_split($out_str, $length, $spacer);
		// remove trailing spacer and add start and end delimiters
		$spacer=preg_quote($spacer);
		$out_str=preg_replace("/" . $spacer . "$/", "", $out_str);
		$out_str=$start . $out_str . $end;
	}
	return $out_str;
}
if(@$enableclientlogin==TRUE || @$forceclientlogin==TRUE){
	if(@$_SESSION['clientID']!=''){
	}elseif(@$_POST['checktmplogin']!='' && @$_POST['sessionid']!=''){
		$sSQL="SELECT tmploginname FROM tmplogin WHERE tmploginid='" . escape_string(@$_POST['sessionid']) . "' AND tmploginchk='" . escape_string(@$_POST['checktmplogin']) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$_SESSION['clientID']=$rs['tmploginname'];
			ect_free_result($result);
			$sSQL="SELECT clUserName,clActions,clLoginLevel,clPercentDiscount FROM customerlogin WHERE clID='" . escape_string($_SESSION['clientID']) . "'";
		

			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				$_SESSION['clientUser']=$rs['clUserName'];
				$_SESSION['clientActions']=$rs['clActions'];
				$_SESSION['clientLoginLevel']=$rs['clLoginLevel'];
				$_SESSION['clientPercentDiscount']=(100.0-(double)$rs['clPercentDiscount'])/100.0;
			}
		}
		ect_free_result($result);
	}elseif(@$_COOKIE['WRITECLL']!=''){
		$clientEmail=str_replace("'",'',@$_COOKIE['WRITECLL']);
		$clientPW=str_replace("'",'',@$_COOKIE['WRITECLP']);
		$sSQL="SELECT clID,clUserName,clActions,clLoginLevel,clPercentDiscount FROM customerlogin WHERE (clEmail<>'' AND clEmail='" . escape_string($clientEmail) . "' AND clPW='" . escape_string($clientPW) . "') OR (clEmail='' AND clUserName='" . escape_string($clientEmail) . "' AND clPW='" . escape_string($clientPW) . "')";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$_SESSION['clientID']=$rs['clID'];
			$_SESSION['clientUser']=$rs['clUserName'];
			$_SESSION['clientActions']=$rs['clActions'];
			$_SESSION['clientLoginLevel']=$rs['clLoginLevel'];
			$_SESSION['clientPercentDiscount']=(100.0-(double)$rs['clPercentDiscount'])/100.0;
		}
		ect_free_result($result);
	}
	if(@$requiredloginlevel!=''){
		if((int)$requiredloginlevel > @$_SESSION['clientLoginLevel']){
			ob_end_clean();
			if(@$_SERVER['HTTPS']=='on' || @$_SERVER['SERVER_PORT']=='443')$prot='https://';else $prot='http://';
			header('Location: '.$prot.$_SERVER['HTTP_HOST'].str_replace('//','/',dirname($_SERVER['PHP_SELF']).'/cart.php?mode=login&refurl=').urlencode(@$_SERVER['PHP_SELF'].(@$_SERVER['QUERY_STRING']!=''?'?'.@$_SERVER['QUERY_STRING']:'')));
			exit;
		}
	}
	if((@$_SESSION['clientActions'] & 2)==2) $GLOBALS['showtaxinclusive']=0;
}
function getsessionsql(){
	global $thesessionid;
	return(@$_SESSION['clientID']!='' ? 'cartClientID=' . escape_string($_SESSION['clientID']) : "(cartClientID=0 AND cartSessionID='" . escape_string($thesessionid) . "')");
}
function getordersessionsql(){
	global $thesessionid;
	return("ordDate>'" . date('Y-m-d', time()-(2*60*60*24)) . "' AND ".(@$_SESSION['clientID']!='' ? 'ordClientID=' . escape_string($_SESSION['clientID']) : "(ordClientID=0 AND ordSessionID='" . escape_string($thesessionid) . "')"));
}
function htmldisplay($thestr){
	return(str_replace(array('>','<','"'), array('&gt;','&lt;','&quot;'), $thestr));
}
function htmlspecials($thestr){
	return(str_replace(array('&','>','<','"'), array('&amp;','&gt;','&lt;','&quot;'), $thestr));
}
function htmlspecialsid($thestr){
	return(str_replace(array('&','>','<','"',"'"), '', $thestr));
}
function htmlspecialsucode($thestr){
	return(str_replace(array('&','>','<','"','&amp;#','&#47;','&#92;'), array('&amp;','&gt;','&lt;','&quot;','&#','&amp;#47;','&amp;#92;'), $thestr));
}
function jsspecials($thestr){
	return(str_replace(array('\\','\'',"\r","\n"),array('\\\\','\\\'','','\\n'), htmldisplay($thestr)));
}
function jsescape($thestr){
	return(str_replace(array('\\','\'','<'),array('\\\\','\\\'',''), $thestr));
}
function jsescapel($thestr){
	return(str_replace(array('\\','\''),array('\\\\','\\\''), $thestr));
}
function addtomailinglist($theemail,$thename){
	global $storeurl,$emailAddr,$emailencoding,$noconfirmationemail,$htmlemails,$uspsUser,$upsUser,$origZip,$checksumtext,$warncheckspamfolder;
	$isspam=FALSE;
	$theemail=trim(strtolower(strip_tags(str_replace('"','',$theemail))));
	if(strpos($theemail, '@')!==FALSE && strpos($theemail, '.')!==FALSE && strlen($theemail)>5){
		$confirmdate=date('Y-m-d', time()-(60*60*24));
		$sSQL="SELECT email,isconfirmed,mlConfirmDate FROM mailinglist WHERE email='" . escape_string($theemail) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$confirmdate=$rs['mlConfirmDate'];
			$emailexists=TRUE;
			$isconfirmed=$rs['isconfirmed'];
		}else
			$emailexists=$isconfirmed=FALSE;
		ect_free_result($result);
		$emailarr=explode('@',$theemail);
		if(is_numeric($emailarr[0])) $isspam=TRUE;
		if(! $emailexists && ! $isspam){
			$sSQL="SELECT COUNT(*) AS thecnt FROM mailinglist WHERE mlConfirmDate='".date('Y-m-d', time())."' AND mlIPAddress='".escape_string(getipaddress())."'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)) $thecnt=$rs['thecnt']; else $thecnt=0;
			ect_free_result($result);
			if($thecnt<3) ect_query("INSERT INTO mailinglist (email,mlName,isconfirmed,mlConfirmDate,mlIPAddress) VALUES ('" . escape_string($theemail) . "','" . escape_string($thename) . "'," . (@$noconfirmationemail?1:0) . ",'".date('Y-m-d', time())."','".escape_string(getipaddress())."')"); else $isspam=TRUE;
		}
		if(! $isconfirmed && ! @$noconfirmationemail && ! $isspam){
			$warncheckspamfolder=TRUE;
			if($confirmdate!=date('Y-m-d', time())){
				ect_query("UPDATE mailinglist SET mlConfirmDate='".date('Y-m-d', time())."' WHERE email='" . escape_string($theemail) . "'") or ect_error();
				if(@$htmlemails==TRUE) $emlNl='<br />'; else $emlNl="\r\n";
				$thelink=$storeurl . 'cart.php?emailconf='.urlencode($theemail).'&check='.substr(md5($uspsUser.$upsUser.$origZip.@$checksumtext.':'.$theemail), 0, 10);
				if(@$htmlemails==TRUE) $thelink='<a href="' . $thelink . '">' . $thelink . '</a>';
				dosendemail($theemail, $emailAddr, '',@$GLOBALS['xxEMConf']!=''?$GLOBALS['xxEMConf']:$GLOBALS['xxMLConf'], $GLOBALS['xxConfEm'] . $emlNl . $emlNl . $thelink);
			}
		}
	}
}
function getipaddress(){
	if(trim(@$_SERVER['HTTP_X_FORWARDED_FOR'])!=''){
		$ip=explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		$ip=explode(':', $ip[0]);
		return($ip[0]);
	}else
		return(@$_SERVER['REMOTE_ADDR']);
}
function escape_string($estr){
	return(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->real_escape_string($estr):mysql_real_escape_string($estr));
}
function rethuni($tnum){
	return('\\u'.str_pad(dechex($tnum[1]),4,'0',STR_PAD_LEFT));
}
function jscheck($thetxt){
	return str_replace('"','\\"',preg_replace_callback('/&#(\d+);/m','rethuni',$thetxt));
}
function imageorlink($theimg,$thetext,$theclass,$thelink,$isjs){
	if($theimg!='')
		return '<img style="border:0" src="'.$theimg.'" '.($theclass!=''?'class="'.$theclass.'" ':'').'onmouseover="this.style.cursor=\'pointer\';window.status=\''.str_replace("'","\'",$thetext).'\';return true" onmouseout="window.status=\'\';return true" onclick="'.($isjs ? '' : "document.location=(((ECTbh=document.getElementsByTagName('base')).length>0?ECTbh[0].href+'/':'')+'") . $thelink . ($isjs ? '' : "').replace(/([^:]\/)\/+/g,'$1')").'" alt="'.$thetext.'" />';
	elseif($theimg=='button')
		return '<input type="button" value="'.$thetext.'" '.($theclass!=''?'class="'.$theclass.'" ':'').'onclick="'.($isjs ? '' : "document.location=(((ECTbh=document.getElementsByTagName('base')).length>0?ECTbh[0].href+'/':'')+'") . $thelink . ($isjs ? '' : "').replace(/([^:]\/)\/+/g,'$1')").'" />';
	else
		return '<a class="ectlink'.($theclass!=''?' '.$theclass:'').'" href="'.($isjs ? '#" onclick="' : '') . $thelink . '" onmouseover="window.status=\''.str_replace("'","\'",$thetext).'\';return true" onmouseout="window.status=\'\';return true"><strong>'.$thetext.'</strong></a>';
}
function imageorbutton($theimg,$thetext,$theclass,$thelink, $isjs){
	$isabsolute=strpos($thelink,'http://')!==FALSE||strpos($thelink,'https://')!==FALSE;
	if($theimg!='' && $theimg!='button')
		return '<img style="border:0" src="'.$theimg.'" '.($theclass!=''?'class="'.$theclass.'" ':'').'onmouseover="this.style.cursor=\'pointer\';window.status=\''.str_replace("'","\'",$thetext).'\';return true" onmouseout="window.status=\'\';return true" onclick="'.($isjs ? '' : "document.location=(((ECTbh=document.getElementsByTagName('base')).length>0?ECTbh[0].href+'/':'')+'") . $thelink . ($isjs ? '' : "').replace(/([^:]\/)\/+/g,'$1')").'" alt="'.$thetext.'" />';
	else
		return '<input type="button" value="'.$thetext.'" '.($theclass!=''?'class="'.$theclass.'" ':'').'onclick="'.($isjs ? '' : "document.location=" . ($isabsolute?'':"(((ECTbh=document.getElementsByTagName('base')).length>0?ECTbh[0].href+'/':'')+") . "'") . $thelink . ($isjs ? '' : "'" . ($isabsolute?'':").replace(/([^:]\/)\/+/g,'$1')")).'" />';
}
function imageorsubmit($theimg,$thetext,$theclass){
	if($theimg!='' && $theimg!='button')
		return '<input type="image" src="'.$theimg.'" alt="'.$thetext.'" '.($theclass!=''?'class="'.$theclass.'" ':'').'/>';
	else
		return '<input type="submit" value="'.$thetext.'" '.($theclass!=''?'class="'.$theclass.'" ':'').'/>';
}
function dosendemail($doseto, $dosefrom, $dosereplyto, $dosesubject, $dosebody){
	global $doencodeemailsubject,$customheaders,$emailencoding,$htmlemails,$debugmode,$usemailer,$smtphost,$smtpusername,$smtppassword,$smtpport,$smtpsecure,$emailflags,$emailcr,$emailfromname,$sendemailerrnum,$sendemailerrdesc;
	$sendemailerrnum=0;
	$sendemailerrdesc="";
	if(@$doencodeemailsubject) $dosesubject='=?'.$emailencoding.'?B?'.base64_encode($dosesubject).'?=';
	if(@$usemailer=='phpmailer'){
		if(file_exists('./vsadmin/inc/class.phpmailer.php')) $issiteroot=TRUE; else $issiteroot=FALSE;
		if($issiteroot){
			include_once('./vsadmin/inc/class.phpmailer.php');
			@include_once('./vsadmin/inc/class.smtp.php');
		}else{
			include_once('./inc/class.phpmailer.php');
			@include_once('./inc/class.smtp.php');
		}
		$mail=new PHPMailer();
		if($issiteroot) $mail->SetLanguage('en', './vsadmin/inc/'); else $mail->SetLanguage('en', './inc/');
		$mail->IsSMTP();
		if(@$debugmode) $mail->SMTPDebug=2;
		$mail->Host=$smtphost;
		$mail->SMTPAuth=(@$smtpusername!='' && @$smtppassword!='');
		if(@$smtpusername!='' && @$smtppassword!=''){
			$mail->Username=$smtpusername;
			$mail->Password=$smtppassword;
		}
		if(@$smtpport!='') $mail->Port=$smtpport;
		if(@$smtpsecure!='') $mail->SMTPSecure=$smtpsecure;
		$mail->From=$dosefrom;
		if(@$emailfromname!='')$mail->FromName=$emailfromname;
		$mail->AddAddress($doseto);
		if($dosereplyto!='') $mail->AddReplyTo($dosereplyto);
		// $mail->WordWrap=50;
		$mail->IsHTML(@$htmlemails==TRUE);
		$mail->Subject=$dosesubject;
		$mail->Body=$dosebody;
		// $mail->AltBody="Plain Text";
		if(!$mail->Send()){
			$sendemailerrdesc=$mail->ErrorInfo;
			if(@$debugmode) echo 'Failed to send mail: ' . $sendemailerrdesc;
			$sendemailerrnum=1;
		}
	}else{
		if(@$customheaders==''){
			$headers='MIME-Version: 1.0'.$emailcr;
			if(@$emailfromname!='') $headers.='From: %fromname% <%from%>'.$emailcr; else $headers.='From: %from%'.$emailcr;
			if($dosereplyto!='') $headers.='Reply-To: %replyto%'.$emailcr;
			if(@$htmlemails==TRUE)
				$headers.='Content-type: text/html; charset='.$emailencoding.$emailcr;
			else
				$headers.='Content-type: text/plain; charset='.$emailencoding.$emailcr;
		}else{
			$headers=$customheaders;
			if($dosereplyto==''){
				if(($startpos=strpos(strtolower($headers), 'reply-to'))!==FALSE){
					if(($endpos=strpos($headers,"\n",$startpos+1))!==FALSE){
						$headers=substr_replace($headers,'',$startpos,($endpos-$startpos)+1);
					}
				}
			}
		}
		$headers=str_replace('%from%',$dosefrom,$headers);
		$headers=str_replace('%fromname%',@$emailfromname,$headers);
		$headers=str_replace('%to%',$doseto,$headers);
		$headers=str_replace('%replyto%',$dosereplyto,$headers);
		$emailflags=str_replace('%from%',$dosefrom,@$emailflags);
		if($emailflags!=''){
			if(@$debugmode==TRUE)
				$sendemailerrnum=(mail($doseto, $dosesubject, $dosebody, $headers, $emailflags)?0:1);
			else
				$sendemailerrnum=(@mail($doseto, $dosesubject, $dosebody, $headers, $emailflags)?0:1);
		}else{
			if(@$debugmode==TRUE)
				$sendemailerrnum=(mail($doseto, $dosesubject, $dosebody, $headers)?0:1);
			else
				$sendemailerrnum=(@mail($doseto, $dosesubject, $dosebody, $headers)?0:1);
		}
	}
}
function getgcchar(){
	$tc='';
	while($tc=='' || $tc=='O' || $tc=='I' || $tc=='Q')
		$tc=chr(rand(65, 90));
	return($tc);
}
function getrndchar(){
	return(chr(rand(65, 90)));
}
function replaceemailtxt($thestr, $txtsearch, $txtreplace, &$didreplace){
	$inbrackets=FALSE;
	$countinscope=1;
	if($thestr=='') $i=FALSE; else $i=strpos($thestr, $txtsearch);
	$didreplace=($i!==FALSE);
	if($i!==FALSE){
		$t1=$i;
		$bcount=0;
		while($t1>=0 && ($bcount!=0 || $thestr[$t1]!='{')){
			if($thestr[$t1]=='{') $bcount++;
			if($thestr[$t1]=='}') $bcount--;
			$t1--;
		}
		if($t1<0) $t1=FALSE;
		
		$t4=$i;
		$bcount=0;
		while($t4<strlen($thestr) && ($bcount!=0 || $thestr[$t4]!='}')){
			if($thestr[$t4]=='{') $bcount++;
			if($thestr[$t4]=='}') $bcount--;
			$t4++;
		}
		if($t4>=strlen($thestr)) $t4=FALSE;
		$inbrackets=($t1!==FALSE && $t4!==FALSE);
	}
	if($i===FALSE){
		return($thestr);
	}elseif($txtreplace==''){ // want to replace all of txtsearch OR {...txtsearch...}
		if($inbrackets) return(substr($thestr, 0, $t1) . substr($thestr, $t4+1)); else return(str_replace($txtsearch, '', $thestr));
	}else{ // Want to remove the { AND }
		if($txtreplace=='%ectpreserve%') $txtreplace='';
		if($inbrackets) $thestr=substr($thestr, 0, $t1) . substr($thestr, $t1+1, ($t4-$t1)-1) . substr($thestr, $t4+1);
		if(($txtsearch=='%trackingnum%' && $inbrackets) || substr($txtsearch,0,9)=='%statusid'){
			if($txtsearch=='%trackingnum%')
				$countinscope=substr_count(substr($thestr, $t1+1, ($t4-$t1)-1), '%trackingnum%');
			return(preg_replace('/'.$txtsearch.'/', $txtreplace, $thestr, $countinscope));
		}else
			return(str_replace($txtsearch, $txtreplace, $thestr));
	}
}
function showproductreviews($disptype, $classname){
	global $rs,$thedetailslink;
	$spr='<div class="'.$classname.'"><a href="'.$thedetailslink.'#reviews">';
	$therating=(int)($rs['pTotRating']/$rs['pNumRatings']);
	for($index=1; $index <= (int)($therating / 2); $index++){
		$spr.='<img class="'.$classname.'" src="images/sreviewcart.gif" alt="" style="vertical-align:middle;margin:0px;border:0px;" />';
	}
	$ratingover=$therating;
	if($ratingover / 2 > (int)($ratingover / 2)){
		$spr.='<img class="'.$classname.'" src="images/sreviewcarthg.gif" alt="" style="vertical-align:middle;margin:0px;border:0px;" />';
		$ratingover++;
	}
	for($index=(int)($ratingover / 2) + 1; $index <= 5; $index++){
		$spr.='<img class="'.$classname.'" src="images/sreviewcartg.gif" alt="" style="vertical-align:middle;margin:0px;border:0px;" />';
	}
	$spr.='</a><span class="prodratingtext">';
	if($disptype==2) $spr.=' <a class="ectlink prodratinglink" href="'.$thedetailslink.'#reviews">' . str_replace('%s', $rs['pNumRatings'], $GLOBALS['xxBasRat']) . '</a>'; elseif($disptype==1) $spr.=' ' . str_replace('%s', $rs['pNumRatings'], $GLOBALS['xxBasRat']) . ' (<a class="ectlink prodratinglink" href="' . $thedetailslink . '#reviews">' . $GLOBALS['xxView'] . '</a>)';
	return($spr . '</span></div>');
}
function splitfirstlastname($thename,&$firstfull,&$lastname){
global $usefirstlastname;
	if(@$usefirstlastname&&strpos($thename, ' ')!==FALSE){
		$namearr=explode(' ',$thename,2);
		$firstfull=$namearr[0];
		$lastname=$namearr[1];
	}else{
		$firstfull=$thename;
		$lastname='';
	}
}
function getcatid($sid,$snam,$seopattern){
	global $usecategoryname,$seocategoryurls,$detlinkspacechar;
	if(@$seocategoryurls) return(str_replace('%s',rawurlencode(str_replace(' ',$detlinkspacechar,$snam)),$seopattern));
	if(@$usecategoryname && $snam!='') return(urlencode($snam)); else return($sid);
}
function cleanupemail($theemail){
	$tmpstr='';$gotat=FALSE;
	if(strlen($theemail)<50){
		$theemail=str_replace(array('"',' ',"'",'(',')'),'',$theemail);
		$theemail=strip_tags($theemail);
		for($i=0; $i < strlen($theemail); $i++){
			$ch=substr($theemail,$i,1);
			if(!($ch=='@'&&$gotat)) $tmpstr.=substr($theemail,$i,1);
			if($ch=='@')$gotat=TRUE;
		}
		if(!$gotat) $tmpstr='';
	}
	return($tmpstr);
}
function getfullurl($pagepart){
	$gfu='';
	if(substr($pagepart,0,5)!="http:" && substr($pagepart,0,6)!='https:'){
		$gfu='http'.(@$_SERVER['HTTPS']=='on' || @$_SERVER['SERVER_PORT']=='443'?'s':'').'://'.$_SERVER['HTTP_HOST'];
		if(substr($pagepart,0,1)!='/') $gfu.=(strrpos($_SERVER['PHP_SELF'],'/')>1?substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'],'/')+1):'/');
	}
	return($gfu.$pagepart);
}
function get_wholesaleprice_sql(){
	global $WSP,$OWSP,$TWSP,$wholesaleoptionpricediff,$nowholesalediscounts,$nodiscounts;
	if(@$_SESSION['clientUser']!=''){
		if(($_SESSION['clientActions'] & 8)==8){
			$WSP='pWholesalePrice AS ';
			$TWSP='pWholesalePrice';
			if(@$wholesaleoptionpricediff==TRUE) $OWSP='optWholesalePriceDiff AS ';
			if(@$nowholesalediscounts==TRUE) $nodiscounts=TRUE;
		}
		if(($_SESSION['clientActions'] & 16)==16){
			$WSP=$_SESSION['clientPercentDiscount'] . '*'.(($_SESSION['clientActions'] & 8)==8?'pWholesalePrice':'pPrice').' AS ';
			$TWSP=$_SESSION['clientPercentDiscount'] . '*'.(($_SESSION['clientActions'] & 8)==8?'pWholesalePrice':'pPrice');
			$OWSP=$_SESSION['clientPercentDiscount'] . '*'.((($_SESSION['clientActions'] & 8)==8)&&@$wholesaleoptionpricediff?'optWholesalePriceDiff':'optPriceDiff').' AS ';
			if(@$nowholesalediscounts==TRUE) $nodiscounts=TRUE;
		}
	}
}
function writepagebar($CurPage,$iNumPages,$sprev,$snext,$sLink,$nofirstpage){
	$startPage=max(1,round(floor((double)$CurPage/10.0)*10));
	$endPage=min($iNumPages,round(floor((double)$CurPage/10.0)*10)+10);
	if($CurPage > 1)
		$sStr=$sLink . '1"><span class="pagebarquo" style="font-family:Verdana;font-weight:bold">&laquo;</span></a> ' . $sLink . ($CurPage-1) . '"'.($CurPage>2?' rel="prev"':'').'>'.$sprev.'</a> | ';
	else
		$sStr='<span class="pagebarquo" style="font-family:Verdana;font-weight:bold">&laquo;</span> '.$sprev.' | ';
	for($i=$startPage;$i <= $endPage; $i++){
		if($i==$CurPage)
			$sStr.='<span class="currpage">' . $i . '</span> | ';
		else{
			$sStr.=$sLink . $i . '">';
			if($i==$startPage && $i > 1) $sStr.='...';
			$sStr.=$i;
			if($i==$endPage && $i < $iNumPages) $sStr.='...';
			$sStr.='</a> | ';
		}
	}
	if($CurPage < $iNumPages)
		$sStr.=$sLink . ($CurPage+1) . '" rel="next">'.$snext.'</a> ' . $sLink . $iNumPages . '"><span class="pagebarquo" style="font-family:Verdana;font-weight:bold">&raquo;</span></a>';
	else
		$sStr.=' '.$snext.' <span class="pagebarquo" style="font-family:Verdana;font-weight:bold">&raquo;</span>';
	if($nofirstpage) $sStr=str_replace(array('&amp;pg=1"','?pg=1"'),'" rel="start"',$sStr);
	return($sStr);
}
function addtag($tagname, $strValue){
	return('<' . $tagname . '>' . str_replace('<', '&lt;', str_replace('&', '&amp;', $strValue)) . '</' . $tagname . '>');
}
function whv($hvname,$hvval){
return('<input type="hidden" name="' . $hvname . '" value="' . htmlspecials($hvval) . '" />' . "\r\n");
}
function getsessionid(){
	global $persistentcart;
	if(is_numeric(@$persistentcart)&&(int)@$persistentcart>0){
		if(@$_COOKIE['ectcartcookie']!=''){
			return(str_replace("'",'',$_COOKIE['ectcartcookie']));
		}else{
			$gotunique=FALSE;
			while(! $gotunique){
				$sequence=substr(md5(uniqid('',TRUE).session_id()),0,26);
				$sSQL="SELECT cartSessionID FROM cart WHERE cartSessionID='" . $sequence . "'";
				$result=ect_query($sSQL) or ect_error();
				if(ect_num_rows($result)==0) $gotunique=TRUE;
				ect_free_result($result);
			}
			setcookie('ectcartcookie', $sequence, time()+($persistentcart*60*60*24), '/', '', FALSE);
			return($sequence);
		}
	}else
		return(session_id());
}
function dohashpw($thepw){
	if(trim($thepw)=='') return(''); else return(md5('ECT IS BEST'.trim($thepw)));
}
function logevent($userid,$eventtype,$eventsuccess,$eventorigin,$areaaffected){
	global $padssfeatures;
	if(@$padssfeatures==TRUE){
		$sSQL="SELECT logID FROM auditlog WHERE eventType='STARTLOG'";
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result)==0){
			$sSQL='INSERT INTO auditlog (userID,eventType,eventDate,eventSuccess,eventOrigin,areaAffected) VALUES (' .
				"'" . escape_string(substr($userid,0,48)) . "','STARTLOG','" . date('Y-m-d H:i:s') . "',1," .
				"'" . escape_string(substr($eventorigin,0,48)) . "','" . escape_string(substr($areaaffected,0,48)) . "')";
			ect_query($sSQL) or ect_error();
		}
		ect_free_result($result);
		$sSQL='INSERT INTO auditlog (userID,eventType,eventDate,eventSuccess,eventOrigin,areaAffected) VALUES (' .
			"'" . escape_string(substr($userid,0,48)) . "','" . escape_string(substr($eventtype,0,48)) . "'," .
			"'" . date('Y-m-d H:i:s') . "'," . ($eventsuccess?1:0) . "," .
			"'" . escape_string(substr($eventorigin,0,48)) . "','" . escape_string(substr($areaaffected,0,48)) . "')";
		ect_query($sSQL) or ect_error();
		ect_query("DELETE FROM auditlog WHERE eventDate<'" . date('Y-m-d H:i:s',time()-60*60*24*365) . "'") or ect_error();
	}
}
function splitname($thename, &$firstname, &$lastname){
	if(strstr($thename,' ')){
		list($firstname,$lastname)=explode(' ',$thename,2);
	}else{
		$firstname='';
		$lastname=$thename;
	}
}
function replace($str,$fnd,$rep){
	return(str_replace($fnd,$rep,$str));
}
function detectmobilebrowser(){
	$uagent=strtolower(@$_SERVER['HTTP_USER_AGENT']);
	$dmb=FALSE;
	if(strpos($uagent,'android')!==FALSE||strpos($uagent,'blackberry')!==FALSE||strpos($uagent,'iemobile')!==FALSE||strpos($uagent,'iphone')!==FALSE||strpos($uagent,'mobile')!==FALSE||strpos($uagent,'nokia')!==FALSE||strpos($uagent,'opera mini')!==FALSE||strpos($uagent,'pocketpc')!==FALSE||strpos($uagent,'samsung')!==FALSE||strpos($uagent,'symbian')!==FALSE||strpos($uagent,'smartphone')!==FALSE) $dmb=TRUE;
	return($dmb);
}
function getget($tval){
	return(unstripslashes(@$_GET[$tval]));
}
function getpost($tval){
	return(unstripslashes(@$_POST[$tval]));
}
function getrequest($tval){
	return(unstripslashes(trim(@$_GET[$tval])!=''?$_GET[$tval]:@$_POST[$tval]));
}
function ect_query($ectsql){
	$ectretval=@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->query($ectsql):mysql_query($ectsql);
	if(@$GLOBALS['debugmode']&&$ectretval==FALSE){
		$bt=debug_backtrace();
		$caller=array_shift($bt);
		echo '<strong>DATABASE ERROR:</strong> '.$caller['file']." on line ".$caller['line']."<br />".$ectsql,"<br />";
	}
	return($ectretval);
}
function ect_fetch_assoc($ectres){
	return(@$GLOBALS['ectdatabase']?$ectres->fetch_assoc():mysql_fetch_assoc($ectres));
}
function ect_num_rows($ectres){
	return(@$GLOBALS['ectdatabase']?$ectres->num_rows:mysql_num_rows($ectres));
}
function ect_insert_id(){
	return(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->insert_id:mysql_insert_id());
}
function ect_free_result($ectres){
	@$GLOBALS['ectdatabase']?$ectres->free_result():mysql_free_result($ectres);
}
function ect_error(){
	print(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->error:mysql_error());
}
function jsurlencode($tstr){
	return rawurlencode($tstr);
}
function jsenc($tstr){
	global $adminencoding;
	if(strtolower(@$adminencoding)!='utf-8') $tstr=utf8_encode($tstr);
	return($tstr);
}
function getdetailsurl($gdid,$gdstatic,$gdname,$gdurl,$gdqs,$gdpathtohere){
	global $seodetailurls,$detlinkspacechar,$usepnamefordetaillinks;
	$gdname=($gdurl!=''?$gdurl:$gdname);
	if(@$seodetailurls)
		return($gdpathtohere.rawurlencode(str_replace(' ',@$detlinkspacechar,$gdname)).($gdqs!=''?'?'.$gdqs:''));
	elseif($gdurl!='')
		return($gdpathtohere.$gdurl.($gdqs!=''?'?'.$gdqs:''));
	elseif($gdstatic!=0)
		return($gdpathtohere.cleanforurl($gdname).'.php'.($gdqs!=''?'?'.$gdqs:''));
	return($gdpathtohere.'proddetail.php?prod='.urlencode(@$usepnamefordetaillinks?str_replace(' ',@$detlinkspacechar,$gdname):$gdid).($gdqs!=''?'&amp;'.$gdqs:''));
}
function commaseplist($inlist){
	$inlist=preg_replace('/[^,\d]/','',$inlist);
	$inlist=preg_replace('/,+/',',',$inlist);
	return(preg_replace('/,$|^,/','',$inlist));
}
function getperproductdiscounts(){
	global $noshowdiscounts,$rs,$minloglevel,$topcpnids,$topsectionids,$isrootsection,$alldiscounts,$catid,$maxglobaldiscounts,$globaldiscounts,$noapplydiscounts;
	if(@$noshowdiscounts!=TRUE){
		$isglobaldiscount=FALSE; $localdiscounts=','; $lastcouponname=$attributelist='';
		$sSQL="SELECT mSCscID FROM multisearchcriteria WHERE mSCpID='".escape_string($rs['pId'])."'";
		$result2=ect_query($sSQL) or ect_error();
		while($rs2=ect_fetch_assoc($result2)){
			$attributelist.=$rs2['mSCscID'].' ';
		}
		ect_free_result($result2);
		$attributelist=str_replace(' ',"','",trim($attributelist));
		$sSQL='SELECT DISTINCT cpnID,'.getlangid('cpnName',1024)." FROM coupons LEFT OUTER JOIN cpnassign ON coupons.cpnID=cpnassign.cpaCpnID WHERE (cpnSitewide=0 OR cpnSitewide=3) AND cpnNumAvail>0 AND cpnEndDate>='" . date('Y-m-d',time()) ."' AND cpnIsCoupon=0 AND ((cpaType=2 AND cpaAssignment='" . escape_string($rs['pId']) . "')";
		if(! $isrootsection) $sSQL.=" OR (cpaType=1 AND cpaAssignment IN ('" . str_replace(',',"','",$topcpnids) . "') AND NOT cpaAssignment IN ('" . str_replace(',',"','",$topsectionids) . "'))";
		if($attributelist!='') $sSQL.=" OR (cpaType=3 AND cpaAssignment IN ('".$attributelist."'))";
		$sSQL.=') AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.')) ORDER BY '.getlangid('cpnName',1024);
		$result2=ect_query($sSQL) or ect_error();
		while($rs2=ect_fetch_assoc($result2)){
			for($index=0;$index<$maxglobaldiscounts;$index++){
				if($globaldiscounts[$index][0]=$rs2['cpnID']){ $isglobaldiscount=TRUE; $localdiscounts.=$rs2['cpnID'].','; }
			}
			if(! $isglobaldiscount && $rs2[getlangid('cpnName',1024)]!=$lastcouponname) $alldiscounts.=($lastcouponname=$rs2[getlangid('cpnName',1024)]) . '<br />';
		}
		ect_free_result($result2);
		if($catid!='0' && $topsectionids!=''){
			if(strpos(','.$topsectionids.',',','.$rs['pSection'].',')===FALSE){
				for($index=0;$index<$maxglobaldiscounts;$index++){
					if(strpos($localdiscounts,','.$globaldiscounts[$index][0].',')===FALSE){
						if($globaldiscounts[$index][2]=='xxx'){
							$rowcounter=0;
							$otherassignments='';
							$sSQL='SELECT cpaAssignment FROM cpnassign WHERE cpaCpnID='.$globaldiscounts[$index][0].' AND cpaType=1';
							$result2=ect_query($sSQL) or ect_error();
							while($rs2=ect_fetch_assoc($result2)){
								$otherassignments.=$rs2['cpaAssignment'].',';
								$rowcounter++;
							}
							ect_free_result($result2);
							if($rowcounter>1) $otherassignments=','.getsectionids(substr($otherassignments,-1),FALSE).','; else $otherassignments='';
							$globaldiscounts[$index][2]=$otherassignments;
						}
						if(strpos($globaldiscounts[$index][2],','.$rs['pSection'].',')===FALSE) $noapplydiscounts.=$globaldiscounts[$index][1];
					}
				}
			}
		}
	}
}
function displaybmlbanner($pubid,$bannerdims){ ?>
<div class="billmelaterbanner" style="text-align:center"><script type="text/javascript" data-pp-pubid="<?php print $pubid?>" data-pp-placementtype="<?php print $bannerdims?>"> (function (d, t) {
"use strict";
var s=d.getElementsByTagName(t)[0], n=d.createElement(t);
n.src="//paypal.adtag.where.com/merchant.js";
s.parentNode.insertBefore(n, s);
}(document, "script"));
</script></div><?php
}
if(@$_SESSION['httpreferer']=='' && @$_SERVER['HTTP_REFERER']!=''){
	$httpreferer=substr($_SERVER['HTTP_REFERER'], 0, 255);	
	if(strlen($httpreferer)>=255){
		$andpos=strrpos($httpreferer, '&');
		if($andpos > 0) $httpreferer=substr($httpreferer, 0, $andpos);
	}
	$_SESSION['httpreferer']=$httpreferer;
}
?>