<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(!@$GLOBALS['incfunctionsdefined']){print 'No incfunctions.php file';exit;}
global $packdims,$estimateshipping,$alreadygotadmin,$splitUSZones,$adminLocale,$countryCurrency,$countryNumCurrency,$orcurrencyisosymbol,$useEuro,$storeurl,$useStockManagement,$adminProdsPerPage,$countryTax,$countryTaxRate,$delccafter,$handling,$handlingchargepercent,$adminCanPostUser,$packtogether,$origZip,$shipType,$adminIntShipping,$origCountry,$origCountryCode,$origCountryID,$uspsUser,$uspsPw,$upsUser,$upsPw,$upsAccess,$upsAccount,$upsnegdrates,$fedexaccount,$fedexmeter,$fedexuserkey,$fedexuserpwd,$adminUnits,$emailAddr,$sendEmail,$adminTweaks,$adminlanguages,$adminlangsettings,$currRate1,$currSymbol1,$currRate2,$currSymbol2,$currRate3,$currSymbol3,$currConvUser,$currConvPw,$currLastUpdate,$adminSecret,$cardinalprocessor,$cardinalmerchant,$cardinalpwd,$catalogroot,$adminAltRates,$prodfilter,$prodfiltertext,$dosortby,$sortoptions,$DHLSiteID,$DHLSitePW,$DHLAccountNo,$adminShipping,$storelang;
global $pathtossl,$forceloginonhttps,$quantity,$outofstockarr,$giftcertificateid,$donationid,$giftwrappingid,$loyaltypointvalue,$minloglevel,$addedprods,$numaddedprods,$thepprice,$theid,$customeraccounturl,$thesessionid;
if(@$cartisincluded!=TRUE){
	include './vsadmin/inc/uspsshipping.php';
	include './vsadmin/inc/incemail.php';
}

					$counter = 0;
if(@$dateadjust=='') $dateadjust=0;
$errormsg='';
$demomode=FALSE;
$maxshipoptions=40;
$allfreeshipexempt=$success=$shiphomecountry=TRUE;
$packnumber=1;
$fromshipselector=$nodiscounts=$usehst=$multipleoptions=$stockwarning=$backorder=$cartEmpty=$handlingeligableitem=$noshowcart=$isavsmismatch=FALSE;
$willpickup_=$insidedelivery_=$commercialloc_=$wantinsurance_=$saturdaydelivery_=$signaturerelease_=$hasstates=$returntocustomerdetails=FALSE;
$shipping=$iTotItems=$stateTaxRate=$countryTax=$stateTax=$outofstockcnt=$numallcountries=$ordComLoc=$select_location=0;
$cartrows=$alldata=$shipMethod=$WSP=$OWSP=$appliedcouponname=$ordAVS=$ordCVV=$stateAbbrev=$international=$cpnmessage=$cpnerror=$shipselectoraction=$altrate='';
$appliedcouponamount=$totalquantity=$statetaxfree=$countrytaxfree=$shipfreegoods=$totalgoods=$handlingeligablegoods=$shippingtax=0;
$freeshippingincludeshandling=$somethingToShip=$freeshippingapplied=$warncheckspamfolder=$homecountry=$gotcpncode=$freeshipmethodexists=FALSE;
$errordname=$errordaddress=$errordcity=$errordstate=$errordshipstate=$errordcountry=$errordzip=$errordphone=$errordemail=$errtermsandconditions=$errordshipaddress=$errordshipcountry=FALSE;
$selectedshiptype=$numshipoptions=$freeshipamnt=$rowcounter=$totalshipitems=$stockrelitems=$thePQuantity=$thepweight=$grandtotal=$totaldiscounts=$giftcertsamount=$loyaltypointdiscount=0;
$payerid=$rgcpncode=$token='';
$ordShipName=$ordShipLastName=$ordShipAddress=$ordShipAddress2=$ordShipCity=$ordShipState=$ordShipZip=$ordShipPhone=$ordShipCountry=$ordAffiliate=$ordAddInfo=$ordExtra1=$ordExtra2=$ordShipExtra1=$ordShipExtra2=$ordCheckoutExtra1=$ordCheckoutExtra2='';
$outofstockarr=array();
	
if(@$imgcheckoutbutton=='') $imgcheckoutbutton='images/checkout.gif';
if(@$imgcheckoutbutton2=='') $imgcheckoutbutton2=$imgcheckoutbutton;
if(@$imgcheckoutbutton3=='') $imgcheckoutbutton3=$imgcheckoutbutton;
if(@$checkoutmode=='')$checkoutmode='';
$alreadygotadmin=getadminsettings();
if(@$GLOBALS['nopriceanywhere']) $adminAltRates=0;
$adminShipping=$shipType; // Delete for v6.2
$homeCountryTaxRate=$countryTaxRate;
if(@$GLOBALS['zipposition']==''){
	$zipposition=1;
	if($origCountryID==65) $zipposition=2;
	if($origCountryID==133) $zipposition=4;
}
if(@$GLOBALS['xxAuNetR']=='') $GLOBALS['xxAuNetR']='Thank you! Your order has been received and for security reasons is currently being reviewed. We will be in touch as soon as possible!';
if(@$GLOBALS['xxChoIns']=='') $GLOBALS['xxChoIns']='Please choose if you would like to add shipping insurance';
$requirecvv=TRUE;
if(@$cartisincluded!=TRUE){
	if(@$_SERVER['CONTENT_LENGTH']!='' && $_SERVER['CONTENT_LENGTH']>100000) exit;
	$cartisincluded=FALSE;
	$rgcpncode=trim(str_replace(array("'",'"'),'',strip_tags(@$_REQUEST['cpncode'])));
	if(strpos(strtolower(@$_SESSION['cpncode']), strtolower($rgcpncode) . ' ')!==FALSE || strpos(strtolower(@$_SESSION['giftcerts']), strtolower($rgcpncode) . ' ')!==FALSE) $rgcpncode='';
	if($rgcpncode!=''){ // Check for gift certs
		$sSQL="SELECT gcID FROM giftcertificate WHERE gcRemaining>0 AND gcAuthorized<>0 AND gcID='" . escape_string($rgcpncode) . "'";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){
			if(strpos(@$_SESSION['giftcerts'], $rs['gcID'] . ' ')===FALSE) @$_SESSION['giftcerts'].=$rs['gcID'] . ' ';
			$rgcpncode='';
		}
		ect_free_result($result);
	}
	if($rgcpncode!=''){
		if(trim(@$_SESSION['cpncode'])!='') $cpnerror=$GLOBALS['xxCanApp'] . ' ' . $rgcpncode . '. ' . $GLOBALS['xxOnOnCp'] . '<br />'; else @$_SESSION['cpncode']=trim($rgcpncode) . ' ';
	}
	$rgcpncode=trim(@$_SESSION['cpncode']);
	if(getpost('payerid')!='') $payerid=getpost('payerid'); else $payerid='';
	$token=trim(@$_REQUEST['token']);
	if(str_replace(array("'",'"'),'',strip_tags(getpost('sessionid')))!='') $thesessionid=str_replace(array("'",'"'),'',strip_tags(getpost('sessionid'))); else $thesessionid=getsessionid();
	$theid=escape_string(getpost('id'));
	$checkoutmode=getpost('mode');
	$_SESSION['commercialloc_']=$commercialloc_=(getpost('commercialloc')=='Y');
	$_SESSION['wantinsurance_']=$wantinsurance_=(getpost('wantinsurance')=='Y');
	$_SESSION['saturdaydelivery_']=$saturdaydelivery_=(getpost('saturdaydelivery')=='Y');
	$_SESSION['signaturerelease_']=$signaturerelease_=(getpost('signaturerelease')=='Y');
	$insidedelivery_=(getpost('insidedelivery')=='Y');
	$_SESSION['willpickup_']=$willpickup_=(getpost('willpickup')=='Y');
	$ordPayProvider=getpost('payprovider');
	

insertlocation();




	if(! is_numeric($ordPayProvider)) $ordPayProvider='';
	if(getget('token')!='' && $ordPayProvider=='') $ordPayProvider=19;
	if(getget('action')=='paypalcancel') $checkoutmode='paypalcancel';
	$shipselectoraction	= getpost('shipselectoraction');
	if(getpost('shipselectoraction')=='selector') $fromshipselector=TRUE;
	if(getpost('noredeempoints')=='1') $_SESSION['noredeempoints']=TRUE;
	if(is_numeric(getpost('altrates'))) $altrate=(int)getpost('altrates');


}


$amazonpayment=$paypalexpress=FALSE;
get_wholesaleprice_sql();
$thefrompage=strip_tags(getget('rp')!='' ? getget('rp') : @$_SERVER['HTTP_REFERER']);
if(getget('rp')==''){
	$pu=parse_url($thefrompage);
	if(@strpos(strtolower($storeurl), str_replace('www.','',@$pu['host']))===FALSE) $thefrompage='';
}
if(@$pathtossl!='') $storeurlssl=$pathtossl.(substr($pathtossl,-1)!='/'?'/':''); else $storeurlssl=$storeurl;
if(strpos(strtolower($thefrompage),'javascript:')!==FALSE||strpos(strtolower($thefrompage),'cart.php')!==FALSE||strpos(strtolower($thefrompage),'thanks.php')!==FALSE) $thefrompage='';
if(@$_SESSION['clientID']!='' && @$_SESSION['clientLoginLevel']!='') $minloglevel=$_SESSION['clientLoginLevel']; else $minloglevel=0;
$countryTax=0; // At present both countryTaxRate and countryTax are set in incfunctions
$origShipType=$shipType;
$orighandling=$handling;
$orighandlingpercent=$handlingchargepercent;

function insertlocation(){

	//if(isset($_POST['ck_homepage'])){
		unset($_SESSION['checkboxvalue'] );
		$_SESSION['checkboxvalue'] = $_POST['ck_homepage'];
		unset($_SESSION['location'] );
		$_SESSION['product'] =  $_POST['sel_product'];
		$_SESSION['location'] =$_POST['sel_location'];
		$thechekbox = $_POST['ck_homepage'];

//count the selection boxes.... will be equal to quantity fo producst
		$count = count($_SESSION['location']);
      				
			


					for($i = 0; $i< $count; $i++){
	
						$sql ="SELECT * FROM productandlocation WHERE clientID = '" .  $_SESSION['clientID'] . "' AND prodID = '" . $_SESSION['product'][$i]  . "'";
						$result = ect_query($sql) or ect_error();

						if(isset($_POST['ck_homepage-'.$i])){
						
							if(ect_num_rows($result) > 0)
							{
								$sql ="UPDATE productandlocation SET locID = '" .  $_SESSION['location'][$i] . "' WHERE prodID = '".  $_SESSION['product'][$i] . "' AND clientID = '". $_SESSION['clientID'] . "'";
								ect_query($sql) or ect_error();		
								
							}
							else{
								$sql ="INSERT INTO productandlocation (clientID, locID, prodID) VALUES('" .  $_SESSION['clientID'] . "','" .$_SESSION['location'][$i] .  "','" .  $_SESSION['product'][$i] . "' ) ";
								ect_query($sql) or ect_error();	
							}

						
						}else{
							$sql ="DELETE FROM productandlocation WHERE clientID = '" .  $_SESSION['clientID'] . "' AND locID = '" .$_SESSION['location'][$i] .  "' AND prodID= '" .  $_SESSION['product'][$i] . "'";
								ect_query($sql) or ect_error();	
						}

						
				}		
}
							

				
						
						
							

				
				
					




function getamazonjsurl($isdemomode){
	$scripturl='static-eu.payments-amazon.com/OffAmazonPayments/uk/' . ($isdemomode?'sandbox/':'');
	if($GLOBALS['origCountryCode']=='US') $scripturl='static-na.payments-amazon.com/OffAmazonPayments/us/' . ($isdemomode?'sandbox/':'');
	if($GLOBALS['origCountryCode']=='JP') $scripturl='origin-na.ssl-images-amazon.com/images/G/09/EP/offAmazonPayments/sandbox/prod/lpa/';
	return('https://' . $scripturl . 'js/Widgets.js');
}
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
	global $amazonprms,$amazonstr;
	uksort($amazonprms, 'strcmp');
	$amazonstr=getParametersAsString();
}
function amazonparam2($nam, $val){
	global $amazonstr,$amazonprms;
	$amazonprms[$nam]=replaceaccents($val);
}
function iseuropean($cntryid){
	return($cntryid=='BE' || $cntryid=='BG' || $cntryid=='CZ' || $cntryid=='DK' || $cntryid=='DE' || $cntryid=='EE' || $cntryid=='IE' || $cntryid=='EL' || $cntryid=='ES' || $cntryid=='FR' || $cntryid=='GB' || $cntryid=='HR' || $cntryid=='IT' || $cntryid=='CY' || $cntryid=='LV' || $cntryid=='LT' || $cntryid=='LU' || $cntryid=='HU' || $cntryid=='MT' || $cntryid=='NL' || $cntryid=='AT' || $cntryid=='PL' || $cntryid=='PT' || $cntryid=='RO' || $cntryid=='SI' || $cntryid=='SK' || $cntryid=='FI' || $cntryid=='SE' || $cntryid=='UK');
}
function labeltxt($lbltxt,$lblid){
	return '<label for="'.$lblid.'">'.$lbltxt.'</label>';
}
function getstateabbrev($statename){
	$stateabbrev='';
	$sSQL="SELECT stateAbbrev FROM states WHERE (stateCountryID=1 OR stateCountryID=2) AND (stateName='" . escape_string($statename) . "' OR stateAbbrev='" . escape_string($statename) . "')";
	$result2=ect_query($sSQL) or ect_error();
	if($rs2=ect_fetch_assoc($result2)) $stateabbrev=$rs2['stateAbbrev'];
	ect_free_result($result2);
	return($stateabbrev);
}
function zipisoptional($sci){
	if($sci==85 || $sci==91 || $sci==154 || $sci==200) return(TRUE); else return(FALSE);
}
function getDPs($currcode){
	global $overridecurrency,$orcdecplaces;
	return(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : checkDPs($currcode));
}
function createdynamicstates($sSQL){
	global $origCountry,$origCountryID,$usestateabbrev,$ordState,$ordShipState,$origCountryCode,$mobilebrowser;
?>	function getziptext(cntid){
		if(cntid==1) return("<?php print jsescape($GLOBALS['xxZip'])?>"); else return("<?php print jsescape($GLOBALS['xxPostco'])?>");
	}
	function dynamiccountries(citem,stateid){
		var st,smen,cntid=citem[citem.selectedIndex].value;
		if(st=document.getElementById(stateid+'statetxt')){
			if(cntid==1) st.innerHTML='<?php print jsescape($GLOBALS['xxStateD'])?>';
			else if(cntid==2||cntid==175) st.innerHTML='<?php print jsescape($GLOBALS['xxProvin'])?>';
			else if(cntid==142||cntid==201) st.innerHTML='<?php print jsescape($GLOBALS['xxCounty'])?>';
			else st.innerHTML='<?php print jsescape($GLOBALS['xxStaPro'])?>';
		}
		if(st=document.getElementById(stateid+'ziptxt')){
			st.innerHTML=getziptext(cntid);
		}
		if(smen=document.getElementById(stateid+'state')){
			smen.disabled=false;
			if(countryhasstates[cntid]){
				smen.options[0].value='';
				if(cntid==1) smen.options[0].innerHTML='<?php print jsescape($GLOBALS['xxPSelUS'])?>';
				else if(cntid==2) smen.options[0].innerHTML='<?php print jsescape($GLOBALS['xxPSelCA'])?>';
				else if(cntid==201) smen.options[0].innerHTML='<?php print jsescape($GLOBALS['xxPSelUK'])?>';
				else smen.options[0].innerHTML='<?php print jsescape($GLOBALS['xxPlsSel'])?>';
				for(var cind=0;cind<dynst[cntid].length;cind++){
					if(cind>=smen.length-1)
						smen.options[cind+1]=new Option();
					smen.options[cind+1].value=dynab[cntid][cind];
					smen.options[cind+1].innerHTML=((cntid==1||cntid==2)&&<?php print($mobilebrowser?'true':'false') ?>?dynab[cntid][cind]:dynst[cntid][cind]);
				}
				smen.length=cind+1;
				stateselectordisabled[stateid=='s'?1:0]=false;
			}else{
				smen.options[0].innerHTML='<?php print jsescape($GLOBALS['xxOutsid'] . ' ' . $origCountryCode)?>';
				smen.disabled=true;
				stateselectordisabled[stateid=='s'?1:0]=true;
			}
			smen.selectedIndex=0;
		}
	}
	function setinitialstate(isshp){
		var initstate=['<?php print jsescape(getpost('state')!='' ? getpost('state') : $ordState)?>','<?php print jsescape(getpost('sstate')!='' ? getpost('sstate') : $ordShipState)?>'];
		var gotstate=false;
		if(document.getElementById(isshp+"state")){
			var smen=document.getElementById(isshp+"state");
			for(var cind=0; cind<smen.length; cind++){
				if(smen.options[cind].value==initstate[isshp=='s'?1:0]){
					smen.selectedIndex=cind;
					gotstate=true;
					break;
				}
			}
		}
		if(document.getElementById(isshp+"state2"))
			document.getElementById(isshp+"state2").value=(gotstate?'':initstate[isshp=='s'?1:0]);
	}
	function adst(cntid,stnam,stab){
		dynst[cntid].push(stnam);dynab[cntid].push(stab!='' ? stab : stnam);
	}
	var stateselectordisabled=[true,true];
	var dynst=[];var dynab=[];var countryhasstates=[];
	var savstates=[];var savstatab=[];
<?php
	$currcountry=0;
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		if($currcountry!=$rs['stateCountryID']){
			$currcountry=$rs['stateCountryID'];
			print 'dynst[' . $currcountry . ']=new Array();dynab[' . $currcountry . "]=new Array();countryhasstates['" . $currcountry . "']=" . $currcountry . ";\r\n";
		}
		print 'adst(' . $currcountry . ",'" . jsescape($rs[getlangid('stateName',1048576)]) . "','" . (@$usestateabbrev==TRUE && ($currcountry==1 || $currcountry==2) ? jsescape($rs['stateAbbrev']) : ($rs[getlangid('stateName',1048576)]!=$rs['stateName'] ? $rs[getlangid('stateName',1048576)] : '')) . "');\r\n";
	}
}
function updategiftwrap(){
	global $giftwrappingid,$giftwrappingcost,$quantity,$theid;
	$quantity=0;
	$currquant=0;
	$theid=@$giftwrappingid;
	$sSQL="SELECT SUM(cartQuantity) AS cartquant FROM cart WHERE cartGiftWrap<>0 AND cartCompleted=0 AND " . getsessionsql();
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		if(! is_null($rs['cartquant'])) $quantity=(int)$rs['cartquant'];
	}
	ect_free_result($result);
	$sSQL="SELECT cartQuantity FROM cart WHERE cartProdID='" . $giftwrappingid . "' AND cartCompleted=0 AND " . getsessionsql();
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $currquant=$rs['cartQuantity']; else $currquant=-1;
	ect_free_result($result);
	if($quantity!=$currquant){
		if($currquant===-1){
			if(is_numeric(@$giftwrappingcost) && @$giftwrappingcost!=0 && $quantity>0) additemtocart($GLOBALS['xxGifPro'],$giftwrappingcost);
		}elseif($quantity==0 || ! is_numeric($giftwrappingcost)){
			ect_query("DELETE FROM cart WHERE cartProdID='" . $giftwrappingid . "' AND cartCompleted=0 AND " . getsessionsql()) or ect_error();
		}else
			ect_query("UPDATE cart SET cartQuantity=" . $quantity . ",cartProdPrice=" . $giftwrappingcost . " WHERE cartProdID='" . $giftwrappingid . "' AND cartCompleted=0 AND " . getsessionsql()) or ect_error();
	}
}
function getshiplogo($stype){
	global $shippinglogo,$mobilebrowser;
	if($stype==3)
		$gsl='<img src="images/usps_logo.gif" alt="USPS Logo" '.(@$mobilebrowser?'width="40px" ':'').'/>';
	elseif($stype==4)
		$gsl='<img src="images/upslogo.png" alt="UPS Logo" '.(@$mobilebrowser?'width="40px" ':'').'/>';
	elseif($stype==6)
		$gsl='<img src="images/canadapost.gif" alt="CanadaPost Logo" '.(@$mobilebrowser?'width="40px" ':'').'/>';
	elseif($stype==7 || $stype==8)
		$gsl='<img src="images/fedexlogo.png" alt="FedEx Logo" '.(@$mobilebrowser?'width="40px" ':'').'/>';
	elseif($stype==9)
		$gsl='<img src="images/dhllogo.gif" alt="DHL Logo" '.(@$mobilebrowser?'width="40px" ':'').'/>';
	else
		$gsl='<img src="'.(@$shippinglogo!=''?$shippinglogo:'images/defaultshiplogo.png').'" alt="Logo" '.(@$mobilebrowser?'width="40px" ':'').'/>';
	return $gsl;
}
function writealtshipline($altmethod,$altid,$pretext,$defpretext,$rhs){
	global $shippingoptionsasradios,$shipType,$origShipType;
	if(@$shippingoptionsasradios==TRUE){
		if($altmethod!='' || $origShipType==$altid) print '<div class="shipline"' . ($shipType==$altid?' style="font-weight:bold"':'') . '>' . ($rhs?'':'<div class="shiplinetext">' . ($shipType==$altid?$defpretext:$pretext) . $altmethod . '</div>') . '<div class="shipradio"><input type="radio" class="shipradio" style="vertical-align:bottom" value="' . $altid . '"' . ($shipType==$altid?' checked="checked"':'') . ' onclick="selaltrate(' . $altid . ')" /></div>' . ($rhs?'<div class="shiplinetext">' . ($shipType==$altid?$defpretext:$pretext) . $altmethod . '</div>':'') . '</div>';
	}else{
		if($altmethod!='' || $origShipType==$altid) print '<option value="'.$altid.'"'.($shipType==$altid?' selected="selected"':'').'>'.($shipType==$altid?$defpretext:$pretext).$altmethod.'</option>';
	}
}
function retrieveorderdetails($ordid, $sessid){
	global $ordName,$ordLastName,$ordAddress,$ordAddress2,$ordCity,$ordState,$ordZip,$ordCountry,$ordEmail,$ordPhone,$ordShipName,$ordShipLastName,$ordShipAddress,$ordShipAddress2,$ordShipCity,$ordShipState,$ordShipZip,$ordShipCountry,$ordShipPhone,$ordPayProvider,$ordComLoc,$ordExtra1,$ordExtra2,$ordShipExtra1,$ordShipExtra2,$ordCheckoutExtra1,$ordCheckoutExtra2,$ordAffiliate,$ordAVS,$ordCVV,$ordAddInfo;
	global $insidedelivery_,$commercialloc_,$wantinsurance_,$saturdaydelivery_,$signaturerelease_;
	$result=ect_query("SELECT ordName,ordLastName,ordAddress,ordAddress2,ordCity,ordState,ordZip,ordCountry,ordEmail,ordPhone,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipZip,ordShipCountry,ordShipPhone,ordPayProvider,ordComLoc,ordExtra1,ordExtra2,ordShipExtra1,ordShipExtra2,ordCheckoutExtra1,ordCheckoutExtra2,ordAffiliate,ordAVS,ordCVV,ordAddInfo FROM orders WHERE ordID='".escape_string($ordid)."' AND ordSessionID='".escape_string($sessid)."'");
	if($rs=ect_fetch_assoc($result)){
		$ordName=$rs['ordName'];
		$ordLastName=$rs['ordLastName'];
		$ordAddress=$rs['ordAddress'];
		$ordAddress2=$rs['ordAddress2'];
		$ordCity=$rs['ordCity'];
		$ordState=$rs['ordState'];
		$ordZip=$rs['ordZip'];
		$ordCountry=$rs['ordCountry'];
		$ordEmail=$rs['ordEmail'];
		$ordPhone=$rs['ordPhone'];
		$ordShipName=$rs['ordShipName'];
		$ordShipLastName=$rs['ordShipLastName'];
		$ordShipAddress=$rs['ordShipAddress'];
		$ordShipAddress2=$rs['ordShipAddress2'];
		$ordShipCity=$rs['ordShipCity'];
		$ordShipState=$rs['ordShipState'];
		$ordShipZip=$rs['ordShipZip'];
		$ordShipCountry=$rs['ordShipCountry'];
		$ordShipPhone=$rs['ordShipPhone'];
		$ordPayProvider=$rs['ordPayProvider'];
		$ordComLoc=$rs['ordComLoc'];
		$ordExtra1=$rs['ordExtra1'];
		$ordExtra2=$rs['ordExtra2'];
		$ordShipExtra1=$rs['ordShipExtra1'];
		$ordShipExtra2=$rs['ordShipExtra2'];
		$ordCheckoutExtra1=$rs['ordCheckoutExtra1'];
		$ordCheckoutExtra2=$rs['ordCheckoutExtra2'];
		$ordAffiliate=$rs['ordAffiliate'];
		$ordAVS=$rs['ordAVS'];
		$ordCVV=$rs['ordCVV'];
		$ordAddInfo='';
		if(getpost("changeaction")!=''){
			$thebit=pow(2,(int)substr(getpost("changeaction"),0,1));
			if(substr(getpost("changeaction"),1,1)=='y'){
				if(($ordComLoc & $thebit)!=$thebit) $ordComLoc+=$thebit;
			}else{
				if(($ordComLoc & $thebit)==$thebit) $ordComLoc-=$thebit;
			}
			ect_query("UPDATE orders SET ordComLoc='" . $ordComLoc . "' WHERE ordStatus=2 AND ordID=".str_replace("'","",$ordid)." AND ordSessionID='".escape_string($sessid)."'") or ect_error();
		}
		if(($ordComLoc & 1)==1) $commercialloc_=TRUE;
		if(($ordComLoc & 2)==2 || abs(@$addshippinginsurance)==1) $wantinsurance_=TRUE;
		if(($ordComLoc & 4)==4) $saturdaydelivery_=TRUE;
		if(($ordComLoc & 8)==8) $signaturerelease_=TRUE;
		if(($ordComLoc & 16)==16) $insidedelivery_=TRUE;
	}
	ect_free_result($result);
}
function getpayprovhandling(){
	global $ordPayProvider,$handling,$orighandling,$handlingchargepercent,$orighandlingpercent;
	if($ordPayProvider!='' && is_numeric($ordPayProvider)){
		$result=ect_query("SELECT ppHandlingCharge,ppHandlingPercent FROM payprovider WHERE payProvID=".$ordPayProvider) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$handling+=$rs['ppHandlingCharge'];
			$handlingchargepercent+=$rs['ppHandlingPercent'];
		}
		ect_free_result($result);
	}
	$orighandling=$handling;
	$orighandlingpercent=$handlingchargepercent;
}
if(@$_SESSION['couponapply']!=''){
	ect_query('UPDATE coupons SET cpnNumAvail=cpnNumAvail+1 WHERE cpnID IN (0' . $_SESSION['couponapply'] . ')') or ect_error();
	$_SESSION['couponapply']='';
}
function getcctypefromnum($thecardnum){
	if(substr($thecardnum, 0, 1)=='5')
		return('MasterCard');
	elseif(substr($thecardnum, 0, 1)=='6')
		return('Discover');
	elseif(substr($thecardnum, 0, 1)=='3')
		return('Amex');
	return('Visa');
}
function show_states($tstate){
	global $origCountryCode;
	print '<option value="">' . $GLOBALS['xxOutsid'] . ' ' . $origCountryCode . '</option>';
	return FALSE;
}
function getcountryfromid($cntryid){
	$cntname='';
	if(is_numeric($cntryid)){
		$sSQL="SELECT countryName FROM countries WHERE countryID=" . $cntryid;
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)) $cntname=$rs['countryName'];
		ect_free_result($result);
	}
	return($cntname);
}
function getidfromcountry($cntry){
	$cntryid=1;
	$sSQL="SELECT countryID FROM countries WHERE countryName='" . escape_string($cntry) . "'";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $cntryid=$rs['countryID'];
	ect_free_result($result);
	return($cntryid);
}
function show_countries($tcountry,$showplssel){
	global $allcountries,$numallcountries;
	if($numallcountries>1&&$showplssel) print '<option value="">'.$GLOBALS['xxPlsSel'].'</option>';
	for($index=0;$index<$numallcountries;$index++){
		print '<option value="' . $allcountries[$index]['countryID'] . '"';
		if($tcountry==$allcountries[$index]['countryName']) print ' selected="selected"';
		print '>' . $allcountries[$index]['countryName'] . "</option>\n";
	}
}
function checkuserblock($thepayprov){
	global $blockmultipurchase,$multipurchaseblockmessage,$shipselectoraction,$REMOTE_ADDR;
	if(@$multipurchaseblockmessage=='') $multipurchaseblockmessage="I'm sorry. We are experiencing temporary difficulties at the moment. Please try your purchase again later.";
	$multipurchaseblocked=FALSE;
	if($thepayprov!='7' && $thepayprov!='13'){
		$theip=$REMOTE_ADDR;
		if($theip=='') $theip='none';
		if(@$blockmultipurchase!='' && $shipselectoraction==''){
			ect_query("DELETE FROM multibuyblock WHERE lastaccess<'" . date('Y-m-d H:i:s', time()-(60*60*24)) . "'") or ect_error();
			$sSQL="SELECT ssdenyid,sstimesaccess FROM multibuyblock WHERE ssdenyip='" . escape_string($theip) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				ect_query("UPDATE multibuyblock SET sstimesaccess=sstimesaccess+1,lastaccess='" . date('Y-m-d H:i:s', time()) . "' WHERE ssdenyid=" . $rs['ssdenyid']) or ect_error();
				if($rs['sstimesaccess']>=$blockmultipurchase) $multipurchaseblocked=TRUE;
			}else{
				ect_query("INSERT INTO multibuyblock (ssdenyip,lastaccess) VALUES ('" . escape_string($theip) . "','" . date('Y-m-d H:i:s', time()) . "')") or ect_error();
			}
			ect_free_result($result);
		}
		if($theip=='none' || ip2long($theip)==FALSE)
			$sSQL='SELECT dcid FROM ipblocking LIMIT 0,1';
		else
			$sSQL='SELECT dcid FROM ipblocking WHERE (dcip1=' . ip2long($theip) . ' AND dcip2=0) OR (dcip1 <= ' . ip2long($theip) . ' AND ' . ip2long($theip) . ' <= dcip2 AND dcip2<>0)';
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result)>0)
			$multipurchaseblocked=TRUE;
	}
	return($multipurchaseblocked);
}
function checkpricebreaks($cpbpid,$origprice){
	global $WSP;
	$newprice='';
	$sSQL='SELECT SUM(cartQuantity) AS totquant FROM cart WHERE cartCompleted=0 AND ' . getsessionsql() . " AND cartProdID='".escape_string($cpbpid)."'";
	$result=ect_query($sSQL) or ect_error();
	$rs=ect_fetch_assoc($result);
	if(is_null($rs['totquant'])) $thetotquant=0; else $thetotquant=$rs['totquant'];
	$sSQL='SELECT '.$WSP.'pPrice FROM pricebreaks WHERE '.$thetotquant.">=pbQuantity AND pbProdID='".escape_string($cpbpid)."' ORDER BY pbQuantity DESC";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $thepricebreak=$rs['pPrice']; else $thepricebreak=$origprice;
	ect_free_result($result);
	$sSQL='UPDATE cart SET cartProdPrice='.round($thepricebreak,2).' WHERE cartCompleted=0 AND ' . getsessionsql() . " AND cartProdID='".escape_string($cpbpid)."'";
	ect_query($sSQL) or ect_error();
	$sSQL='SELECT cartID FROM cart WHERE cartCompleted=0 AND ' . getsessionsql() . " AND cartProdID='".escape_string($cpbpid)."'";
	$result2=ect_query($sSQL) or ect_error();
	while($rs2=ect_fetch_assoc($result2)){
		$sSQL='SELECT coCartOption FROM cartoptions WHERE coMultiply<>0 AND coCartID=' . $rs2['cartID'];
		$result3=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result3)>0){
			$totaloptmultiplier=1;
			while($rs3=ect_fetch_assoc($result3)){
				if(is_numeric($rs3['coCartOption'])) $totaloptmultiplier*=(double)$rs3['coCartOption']; else $totaloptmultiplier=0;
			}
			$sSQL="UPDATE cart SET cartProdPrice=".round($thepricebreak*$totaloptmultiplier,2).' WHERE cartID=' . $rs2['cartID'];
			ect_query($sSQL) or ect_error();
		}
		ect_free_result($result3);
	}
	ect_free_result($result2);
	return($thepricebreak);
}
function multShipWeight($theweight, $themul){
	return(($theweight*$themul)/100.0);
}
function subtaxesfordiscounts($theExemptions, $discAmount){
	global $statetaxfree,$countrytaxfree,$shipfreegoods;
	if(($theExemptions & 1)==1) $statetaxfree -= $discAmount;
	if(($theExemptions & 2)==2) $countrytaxfree -= $discAmount;
	if(($theExemptions & 4)==4) $shipfreegoods -= $discAmount;
}
function addadiscount($resset, $groupdiscount, $dscamount, $subcpns, $cdcpncode, $statetaxhandback, $countrytaxhandback, $theexemptions, $thetax){
	global $totaldiscounts,$cpnmessage,$statetaxfree,$countrytaxfree,$gotcpncode,$perproducttaxrate,$countryTax,$appliedcouponname,$appliedcouponamount,$minloglevel;
	$totaldiscounts+=$dscamount;
	if($groupdiscount){
		$statetaxfree -= ($dscamount * $statetaxhandback);
		$countrytaxfree -= ($dscamount * $countrytaxhandback);
	}else{
		subtaxesfordiscounts($theexemptions, $dscamount);
		if(@$perproducttaxrate) $countryTax -= (($dscamount * $thetax) / 100.0);
	}
	if(stristr($cpnmessage,'<br />' . $resset[getlangid('cpnName',1024)] . '<br />')==FALSE) $cpnmessage.=$resset[getlangid('cpnName',1024)] . '<br />';
	if($subcpns){
		$theres=ect_query('SELECT cpnID FROM coupons WHERE cpnNumAvail>0 AND cpnNumAvail<30000000 AND cpnID=' . $resset['cpnID'] . ' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))') or ect_error();
		if($theresset=ect_fetch_assoc($theres)) @$_SESSION['couponapply'].=',' . $resset['cpnID'];
		ect_query('UPDATE coupons SET cpnNumAvail=cpnNumAvail-1 WHERE cpnNumAvail>0 AND cpnNumAvail<30000000 AND cpnID=' . $resset['cpnID'] . ' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))') or ect_error();
	}
	if($cdcpncode!='' && strtolower(trim($resset['cpnNumber']))==strtolower($cdcpncode)){ $gotcpncode=TRUE; $appliedcouponname=$resset['cpnName']; $appliedcouponamount=$dscamount; }
}
function timesapply($taquant,$tathresh,$tamaxquant,$tamaxthresh,$taquantrepeat,$tathreshrepeat){
	if($tamaxquant==0) $taquantrepeat=0;
	if($tamaxthresh==0) $tathreshrepeat=0;
	if($taquantrepeat==0 && $tathreshrepeat==0)
		$tatimesapply=1.0;
	elseif($tamaxquant==0)
		$tatimesapply=(int)(($tathresh - $tamaxthresh) / $tathreshrepeat)+1;
	elseif($tamaxthresh==0)
		$tatimesapply=(int)(($taquant - $tamaxquant) / $taquantrepeat)+1;
	else{
		$ta1=(int)(($taquant - $tamaxquant) / $taquantrepeat)+1;
		$ta2=(int)(($tathresh - $tamaxthresh) / $tathreshrepeat)+1;
		if($ta2 < $ta1) $tatimesapply=$ta2; else $tatimesapply=$ta1;
	}
	return($tatimesapply);
}
function jschk($str){
	return(str_replace(array('\\',"'",'<','>'),array('\\\\',"\\'",'\\<','\\>'), $str));
}
function calculatediscounts($cdgndtot, $subcpns, $cdcpncode){
	global $totaldiscounts,$cpnmessage,$statetaxfree,$countrytaxfree,$nodiscounts,$WSP,$rgcpncode,$gotcpncode,$thesessionid,$countryTaxRate,$countryTax,$giftcertificateid,$donationid,$giftwrappingid,$dateadjust,$minloglevel;
	$totaldiscounts=0;
	$cpnmessage='<br />';
	$cdtotquant=0;
	if($cdgndtot==0){
		$statetaxhandback=0.0;
		$countrytaxhandback=0.0;
	}else{
		$statetaxhandback=1.0 - (($cdgndtot - $statetaxfree) / $cdgndtot);
		$countrytaxhandback=1.0 - (($cdgndtot - $countrytaxfree) / $cdgndtot);
	}
	if(! $nodiscounts){
		$sSQL='SELECT cartProdID,SUM(cartProdPrice*cartQuantity) AS thePrice,SUM(cartQuantity) AS sumQuant,pSection,COUNT(cartProdID),pExemptions,pTax FROM products INNER JOIN cart ON cart.cartProdID=products.pID WHERE cartProdID<>\''.$giftcertificateid.'\' AND cartProdID<>\''.$donationid.'\' AND cartProdID<>\''.$giftwrappingid.'\' AND cartCompleted=0 AND ' . getsessionsql() . ' GROUP BY cartProdID,pSection,pExemptions,pTax';
		$cdresult=ect_query($sSQL) or ect_error();
		$cdadindex=0;
		while($cdrs=ect_fetch_assoc($cdresult)){
			$cdalldata[$cdadindex++]=$cdrs;
		}
		for($index=0; $index<$cdadindex; $index++){
			$cdrs=$cdalldata[$index];
			// if(($cdrs['cartProdID']==$giftcertificateid || $cdrs['cartProdID']==$donationid || $cdrs['cartProdID']==$giftwrappingid) && is_null($cdrs['pExemptions'])) $cdrs['pExemptions']=15;
			$sSQL='SELECT SUM(coPriceDiff*cartQuantity) AS totOpts FROM cart INNER JOIN cartoptions ON cart.cartID=cartoptions.coCartID WHERE cartCompleted=0 AND ' . getsessionsql() . " AND cartProdID='" . $cdrs['cartProdID'] . "'";
			$cdresult2=ect_query($sSQL) or ect_error();
			$cdrs2=ect_fetch_assoc($cdresult2);
			if(! is_null($cdrs2['totOpts'])) $cdrs['thePrice']+=$cdrs2['totOpts'];
			$cdtotquant+=$cdrs['sumQuant'];
			$topcpnids=$cdrs['pSection'];
			$thetopts=$cdrs['pSection'];
			if(is_null($cdrs['pTax'])) $cdrs['pTax']=$countryTaxRate;
			for($cpnindex=0; $cpnindex<= 10; $cpnindex++){
				if($thetopts==0)
					break;
				else{
					$sSQL='SELECT topSection FROM sections WHERE sectionID=' . $thetopts;
					$result2=ect_query($sSQL) or ect_error();
					if($rs2=ect_fetch_assoc($result2)){
						$thetopts=$rs2['topSection'];
						$topcpnids.=',' . $thetopts;
					}else
						break;
				}
			}
			$attributelist='';
			$sSQL="SELECT mSCscID FROM multisearchcriteria WHERE mSCpID='".escape_string($cdrs['cartProdID'])."'";
			$result2=ect_query($sSQL) or ect_error();
			while($rs2=ect_fetch_assoc($result2)){
				$attributelist.=$rs2['mSCscID'].' ';
			}
			ect_free_result($result2);
			$sSQL='SELECT DISTINCT cpnID,cpnDiscount,cpnType,cpnNumber,'.getlangid('cpnName',1024).",cpnThreshold,cpnQuantity,cpnSitewide,cpnThresholdRepeat,cpnQuantityRepeat FROM coupons LEFT OUTER JOIN cpnassign ON coupons.cpnID=cpnassign.cpaCpnID WHERE cpnNumAvail>0 AND cpnEndDate>='" . date('Y-m-d', time()+($dateadjust*60*60)) ."' AND (cpnIsCoupon=0";
			if($cdcpncode!='') $sSQL.=" OR (cpnIsCoupon=1 AND cpnNumber='" . $cdcpncode . "')";
			$sSQL.=') AND cpnThreshold<=' . $cdrs['thePrice'] . ' AND (cpnThresholdMax>' . $cdrs['thePrice'] . ' OR cpnThresholdMax=0) AND cpnQuantity<=' . $cdrs['sumQuant'] . ' AND (cpnQuantityMax>' . $cdrs['sumQuant'] . ' OR cpnQuantityMax=0) AND (cpnSitewide=0 OR cpnSitewide=2) AND ' .
				"(cpnSitewide=2 OR (cpaType=2 AND cpaAssignment='" . $cdrs['cartProdID'] . "') ";
			if($attributelist!='') $sSQL.="OR (cpaType=3 AND cpaAssignment IN ('".str_replace(' ',"','",trim($attributelist))."')) ";
			$sSQL.="OR (cpaType=1 AND cpaAssignment IN ('" . str_replace(',',"','",$topcpnids) . "')))" .
				' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))';
			$result2=ect_query($sSQL) or ect_error();
			while($rs2=ect_fetch_assoc($result2)){
				if($rs2['cpnType']==1){ // Flat Rate Discount
					$thedisc=(double)$rs2['cpnDiscount'] * timesapply($cdrs['sumQuant'], $cdrs['thePrice'], $rs2['cpnQuantity'], $rs2['cpnThreshold'], $rs2['cpnQuantityRepeat'], $rs2['cpnThresholdRepeat']);
					if($cdrs['thePrice'] < $thedisc) $thedisc=$cdrs['thePrice'];
					addadiscount($rs2, FALSE, $thedisc, $subcpns, $cdcpncode, $statetaxhandback, $countrytaxhandback, $cdrs['pExemptions'], $cdrs['pTax']);
				}elseif($rs2['cpnType']==2){ // Percentage Discount
					addadiscount($rs2, FALSE, (((double)$rs2['cpnDiscount'] * (double)$cdrs['thePrice']) / 100.0), $subcpns, $cdcpncode, $statetaxhandback, $countrytaxhandback, $cdrs['pExemptions'], $cdrs['pTax']);
				}
			}
		}
		$sSQL='SELECT DISTINCT cpnID,cpnDiscount,cpnType,cpnNumber,'.getlangid('cpnName',1024).",cpnSitewide,cpnThreshold,cpnThresholdMax,cpnQuantity,cpnQuantityMax,cpnThresholdRepeat,cpnQuantityRepeat FROM coupons WHERE cpnNumAvail>0 AND cpnEndDate>='" . date('Y-m-d', time()+($dateadjust*60*60)) ."' AND (cpnIsCoupon=0";
		if($cdcpncode!='') $sSQL.=" OR (cpnIsCoupon=1 AND cpnNumber='" . $cdcpncode . "')";
		$sSQL.=') AND cpnThreshold<=' . $cdgndtot . ' AND cpnQuantity<=' . $cdtotquant . ' AND (cpnSitewide=1 OR cpnSitewide=3) AND (cpnType=1 OR cpnType=2)' .
			' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))';
		$result2=ect_query($sSQL) or ect_error();
		while($rs2=ect_fetch_assoc($result2)){
			if($rs2['cpnSitewide']==3){
				$sSQL='SELECT cpaType,cpaAssignment FROM cpnassign WHERE (cpaType=1 OR cpaType=3) AND cpacpnID=' . $rs2['cpnID'];
				$result3=ect_query($sSQL) or ect_error();
				$secids=$attributeids='';
				while($rs3=ect_fetch_assoc($result3)){
					if($rs3['cpaType']==1)$secids.=$rs3['cpaAssignment'].' '; else $attributeids.=$rs3['cpaAssignment'].' ';
					$addcomma=',';
				}
				if($secids!=''||$attributeids!=''){
					$sectionidsql=' AND (';
					if($secids!='') $sectionidsql.='products.pSection IN (' . getsectionids(str_replace(' ',',',trim($secids)),FALSE) . ')';
					if($attributeids!='') $sectionidsql.=($secids!=''?' OR ':'') . 'multisearchcriteria.mSCscID IN (' . str_replace(' ',',',trim($attributeids)) . ')';
					$sectionidsql.=')';
				}else
					$sectionidsql='notassigned';
			}else // cpnSitewide==1
				$sectionidsql='';
			$totquant=$totprice=0;
			if($sectionidsql!='notassigned'){
				$sSQL="SELECT DISTINCT cartID,cartProdPrice,cartQuantity FROM (products INNER JOIN cart ON cart.cartProdID=products.pID) LEFT JOIN multisearchcriteria ON cart.cartProdID=multisearchcriteria.mSCpID WHERE cartProdID<>'".$giftcertificateid."' AND cartProdID<>'".$donationid."' AND cartProdID<>'".$giftwrappingid."' AND cartCompleted=0 AND " . getsessionsql() . $sectionidsql;
				$result4=ect_query($sSQL) or ect_error();
				while($rs4=ect_fetch_assoc($result4)){
					$totprice+=($rs4['cartProdPrice']*$rs4['cartQuantity']);
					$totquant+=$rs4['cartQuantity'];
					$sSQL="SELECT coPriceDiff FROM cartoptions WHERE coCartID=" . $rs4['cartID'];
					$result3=ect_query($sSQL) or ect_error();
					while($rs3=ect_fetch_assoc($result3)){
						$totprice+=($rs3['coPriceDiff']*$rs4['cartQuantity']);
					}
					ect_free_result($result3);
				}
				ect_free_result($result4);
			}
			if($totquant>0 && $rs2['cpnThreshold'] <= $totprice && ($rs2['cpnThresholdMax']>$totprice || $rs2['cpnThresholdMax']==0) && $rs2['cpnQuantity'] <= $totquant && ($rs2['cpnQuantityMax']>$totquant || $rs2['cpnQuantityMax']==0)){
				if($rs2['cpnType']==1){ // Flat Rate Discount
					$thedisc=(double)$rs2['cpnDiscount'] * timesapply($totquant, $totprice, $rs2['cpnQuantity'], $rs2['cpnThreshold'], $rs2['cpnQuantityRepeat'], $rs2['cpnThresholdRepeat']);
					if($totprice < $thedisc) $thedisc=$totprice;
				}elseif($rs2['cpnType']==2){ // Percentage Discount
					$thedisc=((double)$rs2['cpnDiscount'] * (double)$totprice) / 100.0;
				}
				addadiscount($rs2, TRUE, $thedisc, $subcpns, $cdcpncode, $statetaxhandback, $countrytaxhandback, 3, 0);
				if(@$perproducttaxrate && $cdgndtot>0){
					for($index=0; $index<$cdadindex; $index++){
						$cdrs=$cdalldata[$index];
						$applicdisc =0;
						if($rs2['cpnType']==1 && $cdrs['sumQuant']>0) // Flat Rate Discount
							$applicdisc=$thedisc / ($cdtotquant / $cdrs['sumQuant']);
						elseif($rs2['cpnType']==2 && $cdrs['thePrice']>0) // Percentage Discount
							$applicdisc=$thedisc / ($cdgndtot / $cdrs['thePrice']);
						if(($cdrs['pExemptions'] & 2)!=2) $countryTax -= (($applicdisc * $cdrs['pTax']) / 100.0);
					}
				}
			}
		}
	}
	if($statetaxfree < 0) $statetaxfree=0;
	if($countrytaxfree < 0) $countrytaxfree=0;
	$totaldiscounts=round($totaldiscounts, 2);
}
function calculateshippingdiscounts($subcpns){
	global $freeshippingapplied,$nodiscounts,$totalgoods,$totalquantity,$rgcpncode,$freeshipavailtodestination,$freeshipmethodexists,$cpnmessage,$shipping,$freeshipamnt,$gotcpncode,$handling,$handlingchargepercent,$freeshippingincludeshandling,$dateadjust;
	global $somethingToShip,$shipType,$maxshipoptions,$intShipping,$selectedshiptype,$iTotItems,$numuspsmeths,$uspsmethods,$shipMethod,$fromshipselector,$minloglevel,$shippingafterproductdiscounts,$totaldiscounts,$allfreeshipexempt;
	$freeshipamnt=0;
	if($allfreeshipexempt) $freeshipmethodexists=FALSE;
	unset($_SESSION['tofreeshipquant']);
	unset($_SESSION['tofreeshipamount']);
	if(! $nodiscounts){
		$sSQL='SELECT cpnID,'.getlangid('cpnName',1024).',cpnNumber,cpnDiscount,cpnThreshold,cpnCntry,cpnHandling FROM coupons WHERE cpnType=0 AND cpnSitewide=1 AND cpnNumAvail>0 AND cpnThreshold<='.($totalgoods-(@$shippingafterproductdiscounts?$totaldiscounts:0)).' AND (cpnThresholdMax>'.($totalgoods-(@$shippingafterproductdiscounts?$totaldiscounts:0)).' OR cpnThresholdMax=0) AND cpnQuantity<='.$totalquantity.' AND (cpnQuantityMax>'.$totalquantity." OR cpnQuantityMax=0) AND cpnEndDate>='" . date('Y-m-d', time()+($dateadjust*60*60)) ."' AND (cpnIsCoupon=0 OR (cpnIsCoupon=1 AND cpnNumber='".$rgcpncode."'))" .
			' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))';
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){
			if($freeshipavailtodestination || (int)$rs['cpnCntry']==0){
				if($rgcpncode!='' && strtolower(trim($rs['cpnNumber']))==strtolower($rgcpncode)){ $gotcpncode=TRUE; $appliedcouponname=$rs['cpnName']; }
				if($freeshipmethodexists){
					if($fromshipselector){
						if($intShipping[$selectedshiptype][4]==1){
							$freeshipamnt=$intShipping[$selectedshiptype][2] - $intShipping[$selectedshiptype][7];
							if(stristr($cpnmessage,'<br />' . $rs[getlangid('cpnName',1024)] . '<br />')==FALSE) $cpnmessage.=$rs[getlangid('cpnName',1024)] . '<br />';
						}
					}else{
						$freeshipamnt=$intShipping[$selectedshiptype][2] - $intShipping[$selectedshiptype][7];
						if(stristr($cpnmessage,'<br />' . $rs[getlangid('cpnName',1024)] . '<br />')==FALSE) $cpnmessage.=$rs[getlangid('cpnName',1024)] . '<br />';
					}
					if($rs['cpnHandling']!=0){ $freeshippingincludeshandling=TRUE; $handling=0; $handlingchargepercent=0; }
					if($subcpns){
						$theres=ect_query('SELECT cpnID FROM coupons WHERE cpnNumAvail>0 AND cpnNumAvail<30000000 AND cpnID=' . $rs['cpnID'] . ' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))') or ect_error();
						if($theresset=ect_fetch_assoc($theres)) @$_SESSION['couponapply'].=',' . $rs['cpnID'];
						ect_query('UPDATE coupons SET cpnNumAvail=cpnNumAvail-1 WHERE cpnNumAvail>0 AND cpnNumAvail<30000000 AND cpnID=' . $rs['cpnID']) or ect_error();
					}
					$freeshippingapplied=TRUE;
				}
			}
		}
		ect_free_result($result);
	}
	if($somethingToShip && ! $fromshipselector){
		$gotshipping=FALSE;
		if($shipType>=1){
			if($shipType==2 || $shipType==5) sortshippingarray();
			for($indexmso=0; $indexmso<$maxshipoptions; $indexmso++){
				if($intShipping[$indexmso][3]==TRUE){
					if(!$gotshipping || ($intShipping[$indexmso][4]&&$freeshippingapplied)){
						$shipping=$intShipping[$indexmso][2];
						$shipMethod=$intShipping[$indexmso][0];
						$selectedshiptype=$indexmso;
						$gotshipping=TRUE;
					}
					if($intShipping[$indexmso][4]&&$freeshippingapplied) $freeshipamnt=$intShipping[$indexmso][2] - $intShipping[$indexmso][7];
				}
			}
		}
		if(! $freeshippingapplied && $freeshipmethodexists){
			$sSQL='SELECT MIN(cpnQuantity) AS minquant,MIN(cpnThreshold) as minthreshold FROM coupons WHERE cpnType=0 AND cpnSitewide=1 AND cpnNumAvail>0 AND (cpnThresholdMax>'.($totalgoods-(@$shippingafterproductdiscounts?$totaldiscounts:0)).' OR cpnThresholdMax=0) AND (cpnQuantityMax>'.$totalquantity." OR cpnQuantityMax=0) AND cpnEndDate>='" . date('Y-m-d', time()+($dateadjust*60*60)) ."' AND (cpnIsCoupon=0 OR (cpnIsCoupon=1 AND cpnNumber='".$rgcpncode."'))" .
				' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))';
			$result=ect_query($sSQL) or ect_error();
			$rs=ect_fetch_assoc($result);
			if(! is_null($rs['minquant'])){
				if($rs['minquant']-$totalquantity>0) $_SESSION['tofreeshipquant']=$rs['minquant']-$totalquantity;
				if($rs['minthreshold']-($totalgoods-(@$shippingafterproductdiscounts?$totaldiscounts:0))>0) $_SESSION['tofreeshipamount']=$rs['minthreshold']-($totalgoods-(@$shippingafterproductdiscounts?$totaldiscounts:0));
			}
			ect_free_result($result);
		}
	}
	if($freeshipamnt>$shipping) $freeshipamnt=$shipping;
}
function getshiptype(){
	global $shipType,$adminIntShipping,$shipcountry,$origCountry,$shipCountryCode,$usandcausedomesticservice,$cartisincluded;
	if($adminIntShipping!=0 && $shipcountry!=$origCountry && ! (($shipCountryCode=='US' || $shipCountryCode=='CA') && @$usandcausedomesticservice)){
		if($cartisincluded || getpost('altrates')=='') $shipType=$adminIntShipping;
	}
	return $shipType;
}
function initshippingmethods(){
	global $shipType,$adminShipping,$adminIntShipping,$allzones,$numzones,$splitUSZones,$shiphomecountry,$numshipoptions,$intShipping,$success,$errormsg,$commercialloc_,$codpaymentprovider,$signaturerelease_,$allowsignaturerelease,$signatureoption,$saturdaydelivery_,$saturdaypickup,$insidedelivery_,$insidepickup,$thesessionid,$willpickup_,$selectedshiptype,$currShipType,$ordShipCity,$countryCurrency,$DHLSiteID,$DHLSitePW,$shipCountryID,$adminCanPostLogin,$adminCanPostPass,$packtogether,$returntocustomerdetails;
	global $uspsmethods,$numuspsmeths,$international,$shipcountry,$maxshipoptions,$origCountry,$willpickuptext,$willpickupcost,$shipstate,$shipinsuranceamt,$fedexaccount,$fedexmeter,$stateAbbrev,$shipStateAbbrev,$usestateabbrev,$ordPayProvider,$adminAltRates,$altrate,$shipping,$willpickupnohandling,$handling,$handlingchargepercent,$shipMethod,$freeshipmethodexists,$multipleoptions,$fedexuserkey,$fedexuserpwd,$fedexpickuptype,$smartPostHub,$smartpostindicia,$smartpostancendorsement,$paypalexpress;
	global $sXML,$uspsUser,$uspsPw,$upsAccess,$upsUser,$upsPw,$upspickuptype,$origZip,$origCountryCode,$destZip,$shipCountryCode,$adminCanPostUser,$packaging,$adminUnits,$homedelivery,$originstatecode,$ordCity,$ordAddress,$ordAddress2,$ordShipAddress,$ordShipAddress2,$upsnegdrates,$upsnegotiatedaccess,$upsnegotiateduser,$upsnegotiatedpw,$upsAccount,$defaultshipstate,$fromshipselector,$thepweight,$initialpackweight,$combineshippinghandling,$storelang,$splitpackat,$nosplitlargepacks;
	if(@$initialpackweight!='') $thepweight=$initialpackweight;
	if($shipcountry!=$origCountry && ! ($shipType==3 && $shipCountryCode=='PR')){
		$international='Intl';
		$willpickuptext='';
		$willpickup_=FALSE;
	}
	if($willpickup_){
		$shipType=0;
		$adminAltRates=0;
		if(@$willpickupcost!='') $shipping=$willpickupcost; else $shipping=0;
		$shipMethod=$willpickuptext;
		if(@$willpickupnohandling) $handlingchargepercent=$handling=0;
	}
	if($adminAltRates>0){
		$result=ect_query('SELECT altrateid FROM alternaterates WHERE usealtmethod'.$international.'<>0 OR altrateid='.($international==''||$adminIntShipping==0?$adminShipping:$adminIntShipping)) or ect_error();
		if(ect_num_rows($result)<2) $adminAltRates=0;
		ect_free_result($result);
	}
	if($altrate!='' && $adminAltRates>0){
		$result=ect_query('SELECT altrateid FROM alternaterates WHERE (usealtmethod'.$international.'<>0 OR altrateid='.($international==''||$adminIntShipping==0?$adminShipping:$adminIntShipping).') AND altrateid='.$altrate) or ect_error();
		if(ect_num_rows($result)>0) $shipType=$altrate;
		ect_free_result($result);
	}
	for($i=0; $i < $maxshipoptions; $i++){
		$intShipping[$i][0]=''; // Name
		$intShipping[$i][1]=''; // Delivery
		$intShipping[$i][2]=0; // Cost
		$intShipping[$i][3]=0; // Used
		$intShipping[$i][4]=0; // FSA
		$intShipping[$i][5]=''; // Service ID (USPS)
		$intShipping[$i][6]=$shipType; // shipType
		$intShipping[$i][7]=0; // Cost for Free Ship Exempt
	}
	if($fromshipselector){
		if(is_numeric(getpost('orderid')) && is_numeric(getpost('shipselectoridx'))){
			$numshipoptions=0;
			$sSQL='SELECT soMethodName,soCost,soFreeShipExempt,soFreeShip,soShipType,soDeliveryTime FROM shipoptions WHERE soOrderID=' . getpost('orderid') . ' ORDER BY soIndex';
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){
				$intShipping[$numshipoptions][0]=$rs['soMethodName'];
				$intShipping[$numshipoptions][1]=$rs['soDeliveryTime'];
				$intShipping[$numshipoptions][2]=$rs['soCost'];
				$intShipping[$numshipoptions][3]=TRUE;
				$intShipping[$numshipoptions][4]=$rs['soFreeShip'];
				$freeshipmethodexists=($freeshipmethodexists || $intShipping[$numshipoptions][4]);
				$intShipping[$numshipoptions][6]=$rs['soShipType'];
				$intShipping[$numshipoptions][7]=$rs['soFreeShipExempt'];
				$numshipoptions++;
			}
			ect_free_result($result);
			$selectedshiptype=(int)getpost('shipselectoridx');
			$shipping=$intShipping[$selectedshiptype][2];
			$shipMethod=$intShipping[$selectedshiptype][0];
			$shipType=$intShipping[$selectedshiptype][6];
			$currShipType=$intShipping[0][6];
			$multipleoptions=TRUE;
			$numshipoptions--;
		}
	}elseif($shipType==1){ // Flat rate shipping
		$intShipping[0][0]=(@$combineshippinghandling ? $GLOBALS['xxShipHa'] : $GLOBALS['xxShippg']);
		$intShipping[0][3]=TRUE;
		$intShipping[0][4]=1;
	}elseif($shipType==2 || $shipType==5){ // Weight / Price based shipping
		$allzones='';
		$numzones=0;
		$zoneid=0;
		if($splitUSZones && $shiphomecountry && is_numeric($shipCountryID)){
			if($paypalexpress && $shipCountryID==201) $shipstate=str_replace(
				array('East Ayrshire','North Ayrshire','South Ayrshire','East Riding of Yorkshire','Greater Manchester','Argyll and Bute','Edinburgh City','Aberdeen City','Clackmannan','Glasgow (City of)','Antrim','Armagh','Down','Fermanagh','Londonderry','Tyrone','Humberside','North East Lincolnshire','Dumfries and Galloway','East Dunbartonshire','East Renfrewshire','North Lanarkshire','Perthshire and Kinross','South Lanarkshire','Stirling','West Dunbartonshire','Blaenau Gwent','Isle of Anglesey','Merthyr Tydfil','Caerphilly','Conwy','Newport','Swansea','Torfaen','The Vale of Glamorgan','Wrexham','Neath Port Talbot','Bridgend','Western Isles','Inverclyde','Falkirk'),
				array('Ayrshire','Ayrshire','Ayrshire','Yorkshire','Manchester','Argyll','Edinburgh','Aberdeenshire','Clackmannanshire','Glasgow','County Antrim','County Armagh','County Down','County Fermanagh','County Londonderry','County Tyrone','North Humberside','Lincolnshire','Dumfriesshire','Dunbartonshire','Renfrewshire','Lanarkshire','Perthshire','Lanarkshire','Stirlingshire','Dunbartonshire','Gwent','Anglesey','Glamorgan','Glamorgan','Clwyd','Gwent','Glamorgan','Monmouthshire','Glamorgan','Clwyd','West Glamorgan','Mid Glamorgan','Inverness-shire','Renfrewshire','Stirlingshire'),
					$shipstate);
			$sSQL='states INNER JOIN postalzones ON postalzones.pzID=states.stateZone WHERE stateCountryID=' . $shipCountryID . " AND (stateName='" . escape_string($shipstate) . "' OR stateAbbrev='" . escape_string($shipstate) . "')";
		}else
			$sSQL="countries INNER JOIN postalzones ON postalzones.pzID=countries.countryZone WHERE countryName='" . escape_string($shipcountry) . "'";
		$result=ect_query('SELECT pzID,pzMultiShipping,pzFSA,pzMethodName1,pzMethodName2,pzMethodName3,pzMethodName4,pzMethodName5 FROM '.$sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$zoneid=$rs['pzID'];
			$numshipoptions=$rs['pzMultiShipping']+1;
			for($index3=0; $index3 < $numshipoptions; $index3++){
				$intShipping[$index3][0]=$rs['pzMethodName' . ($index3+1)];
				$intShipping[$index3][3]=TRUE;
				$intShipping[$index3][4]=(($rs['pzFSA'] & (1 << $index3))>0 ? 1 : 0);
			}
		}else{
			$success=FALSE;
			if($splitUSZones && $shiphomecountry && $shipstate=='') $errormsg=$GLOBALS['xxPlsSta']; else $errormsg='Country / state shipping zone is unassigned.';
			$returntocustomerdetails=TRUE;
			return(FALSE);
		}
		ect_free_result($result);
		$sSQL='SELECT zcWeight,zcRate,zcRate2,zcRate3,zcRate4,zcRate5,zcRatePC,zcRatePC2,zcRatePC3,zcRatePC4,zcRatePC5 FROM zonecharges WHERE zcZone=' . $zoneid . ' ORDER BY zcWeight';
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result))
			$allzones[$numzones++]=$rs;
		ect_free_result($result);
	}elseif($shipType==3 || $shipType==4 || $shipType>=6){ // USPS / UPS / Canada Post / FedEx / SmartPost / DHL
		$uspsmethods='';
		$numuspsmeths=0;
		if($shipType==4||$shipType==7||$shipType==8)$shipinsuranceamt='';
		if($shipType==3){
			$sSQL=' uspsID<100 AND uspsLocal='.($international==''?'1':'0');
		}elseif($shipType==4)
			$sSQL=' uspsID>100 AND uspsID<200';
		elseif($shipType==6)
			$sSQL=' uspsID>200 AND uspsID<300';
		elseif($shipType==7)
			$sSQL=' uspsID>300 AND uspsID<400'.($international==''&&$commercialloc_?" AND uspsMethod<>'GROUNDHOMEDELIVERY'":'');
		elseif($shipType>=8)
			$sSQL=' uspsID>'.($shipType-4).'00 AND uspsID<'.($shipType-3).'00';
		$result=ect_query('SELECT uspsMethod,uspsFSA,uspsShowAs,uspsLocal FROM uspsmethods WHERE'.$sSQL.' AND uspsUseMethod=1') or ect_error();
		if(ect_num_rows($result)>0){
			while($rs=ect_fetch_assoc($result))
				$uspsmethods[$numuspsmeths++]=$rs;
		}else{
			$success=FALSE;
			$errormsg='Admin Error: ' . $GLOBALS['xxNoMeth'];
		}
		ect_free_result($result);
	}
	if(($shipType==4 || $shipType==7 || $shipType==8) && $shipCountryCode=='US' && $shipStateAbbrev=='PR') $shipCountryCode='PR';
	if($shipType==3 && $shipCountryCode=='PR'){ $shipCountryCode='US'; $shipStateAbbrev='PR'; }
	if(($shipCountryCode=='PR' || ($shipCountryCode=='US' && $shipStateAbbrev=='PR')) && strlen($destZip)==3) $destZip='00'.$destZip;
	if($shipType==3)
		$sXML='<' . $international . 'Rate' . ($international==''?'V4':'V2') . 'Request USERID="' . $uspsUser . '"><Revision>2</Revision>';
	elseif($shipType==4){
		if($shipCountryCode=='US' && $shipStateAbbrev=='VI') $shipCountryCode='VI';
		$sXML='<?xml version="1.0"?><AccessRequest xml:lang="en-US">' . addtag('AccessLicenseNumber',$upsAccess) . addtag('UserId',$upsUser) . addtag('Password',$upsPw) . '</AccessRequest><?xml version="1.0"?>' .
			'<RatingServiceSelectionRequest xml:lang="en-US"><Request><TransactionReference><CustomerContext>Rating and Service</CustomerContext><XpciVersion>1.0001</XpciVersion></TransactionReference>' .
			'<RequestAction>Rate</RequestAction><RequestOption>shop</RequestOption></Request>';
		if(@$upspickuptype!='') $sXML.='<PickupType><Code>' . $upspickuptype . '</Code></PickupType>';
		$sXML.='<Shipment><Shipper>' . (@$upsnegdrates? addtag('ShipperNumber',$upsAccount) : '') . '<Address>' . (@$upsnegdrates ? addtag('StateProvinceCode',$defaultshipstate) : '') . addtag('PostalCode',$origZip) . addtag('CountryCode',$origCountryCode) . '</Address></Shipper>' .
			'<ShipTo><Address>' . addtag('AddressLine1',$ordShipAddress!=''?$ordShipAddress:$ordAddress) . addtag('AddressLine2',$ordShipAddress2!=''?$ordShipAddress2:$ordAddress2) . addtag('City',$ordShipCity!=''?$ordShipCity:$ordCity) . ($shipCountryCode=='US'||$shipCountryCode=='CA'?addtag('StateProvinceCode',$shipStateAbbrev):'') . addtag('PostalCode',$destZip) . addtag('CountryCode',$shipCountryCode) . (! $commercialloc_ ? '<ResidentialAddressIndicator/>' : '') . '</Address></ShipTo>';
	}elseif($shipType==6){
		$packtogether=TRUE; $splitpackat=''; $nosplitlargepacks=TRUE; // Canada Post cannot handle more than one package
		$sXML='<soapenv:Envelope xmlns:rate="http://www.canadapost.ca/ws/soap/ship/rate/v2" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">' .
			'<soapenv:Header><wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsse:UsernameToken><wsse:Username>' . $adminCanPostLogin . '</wsse:Username><wsse:Password>' . $adminCanPostPass . '</wsse:Password></wsse:UsernameToken></wsse:Security></soapenv:Header>' .
			'<soapenv:Body><rate:get-rates-request><platform-id>0008107483</platform-id>' . ($storelang=='fr'?'<locale>FR</locale>':'') . '<mailing-scenario><customer-number>' . $adminCanPostUser . '</customer-number><origin-postal-code>' . strtoupper(str_replace(' ','',$origZip)) . '</origin-postal-code><destination>';
		if($shipCountryCode=='CA')
			$sXML.='<domestic><postal-code>' . strtoupper(str_replace(' ','',$destZip)) . '</postal-code></domestic>';
		elseif($shipCountryCode=='US')
			$sXML.='<united-states><zip-code>' . $destZip . '</zip-code></united-states>';
		else
			$sXML.='<international><country-code>' . $shipCountryCode . '</country-code></international>';
		$sXML.='</destination>';
	}elseif($shipType==7 || $shipType==8){ // FedEx
		if(@$packaging!='') $fedexpackaging='FEDEX_' . strtoupper($packaging); else $fedexpackaging='YOUR_PACKAGING';
		if(@$fedexpickuptype=='') $fedexpickuptype='REGULAR_PICKUP';
		$fedextimestamp='';
		if($saturdaydelivery_==TRUE)
			$fedextimestamp='2010-03-12';
		elseif(@$GLOBALS['noweekendshipment']){
			if((int)date('w')==0)$fedextimestamp=time()+(60*60*24);
			if((int)date('w')==6)$fedextimestamp=time()+(2*60*60*24);
			if($fedextimestamp!='') $fedextimestamp=date('Y-m-d',$fedextimestamp);
		}
		if($fedextimestamp!='') $fedextimestamp=$fedextimestamp='<v9:ShipTimestamp>'.$fedextimestamp.'T10:00:00-04:00</v9:ShipTimestamp>';
		$sXML='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v9="http://fedex.com/ws/rate/v9">' .
		"<soapenv:Header/><soapenv:Body><v9:RateRequest><v9:WebAuthenticationDetail><v9:CspCredential><v9:Key>mKOUqSP4CS0vxaku</v9:Key><v9:Password>IAA5db3Pmhg3lyWW6naMh4Ss2</v9:Password></v9:CspCredential>" .
		"<v9:UserCredential><v9:Key>" . $fedexuserkey . "</v9:Key><v9:Password>" . $fedexuserpwd . "</v9:Password></v9:UserCredential></v9:WebAuthenticationDetail>" .
		"<v9:ClientDetail><v9:AccountNumber>" . $fedexaccount . "</v9:AccountNumber><v9:MeterNumber>" . $fedexmeter . "</v9:MeterNumber><v9:ClientProductId>IBTP</v9:ClientProductId><v9:ClientProductVersion>3272</v9:ClientProductVersion></v9:ClientDetail>" .
		"<v9:TransactionDetail><v9:CustomerTransactionId>Rate Request</v9:CustomerTransactionId></v9:TransactionDetail>" .
		"<v9:Version><v9:ServiceId>crs</v9:ServiceId><v9:Major>9</v9:Major><v9:Intermediate>0</v9:Intermediate><v9:Minor>0</v9:Minor></v9:Version>" .
		"<v9:ReturnTransitAndCommit>true</v9:ReturnTransitAndCommit>" .
		'<v9:RequestedShipment>' . $fedextimestamp . '<v9:DropoffType>' . $fedexpickuptype . "</v9:DropoffType>" . ($shipType==8?'<v9:ServiceType>SMART_POST</v9:ServiceType>':'<v9:PackagingType>'.$fedexpackaging.'</v9:PackagingType>') .
		"<v9:Shipper><v9:Address><v9:PostalCode>" . $origZip . "</v9:PostalCode><v9:CountryCode>" . $origCountryCode . "</v9:CountryCode>" .
		"</v9:Address></v9:Shipper><v9:Recipient><v9:Address>";
		if($ordShipAddress!='') $sXML.="<v9:StreetLines>" . vrxmlencode($ordShipAddress) . "</v9:StreetLines>";
		if($ordShipCity!='') $sXML.="<v9:City>" . $ordShipCity . "</v9:City>";
		if($shipCountryCode=="US" || $shipCountryCode=="CA") $sXML.="<v9:StateOrProvinceCode>" . $shipStateAbbrev . "</v9:StateOrProvinceCode>";
		$sXML.="<v9:PostalCode>" . $destZip . "</v9:PostalCode>" .
		"<v9:CountryCode>" . $shipCountryCode . "</v9:CountryCode><v9:Residential>" . ($commercialloc_ ? "false" : "true") . "</v9:Residential></v9:Address></v9:Recipient>";
		if($shipType==8){
			if(@$smartpostindicia=='') $smartpostindicia='PARCEL_SELECT';
			$sXML.="<v9:SmartPostDetail><v9:Indicia>" . $smartpostindicia . "</v9:Indicia>" . (@$smartpostancendorsement!='' ? "<v9:AncillaryEndorsement>" . $smartpostancendorsement . "</v9:AncillaryEndorsement>" : '') . "<v9:HubId>" . $smartPostHub . "</v9:HubId></v9:SmartPostDetail>";
		}else{
			$sXML.="<v9:SpecialServicesRequested>";
			if($saturdaydelivery_==TRUE) $sXML.="<v9:SpecialServiceTypes>SATURDAY_DELIVERY</v9:SpecialServiceTypes>";
			if($saturdaypickup==TRUE) $sXML.="<v9:SpecialServiceTypes>SATURDAY_PICKUP</v9:SpecialServiceTypes>";
			if($insidedelivery_==TRUE) $sXML.="<v9:SpecialServiceTypes>INSIDE_DELIVERY</v9:SpecialServiceTypes>";
			if($insidepickup==TRUE) $sXML.="<v9:SpecialServiceTypes>INSIDE_PICKUP</v9:SpecialServiceTypes>";
			if(@$emailnotification==TRUE) $sXML.="<v9:SpecialServiceTypes>EMAIL_NOTIFICATION</v9:SpecialServiceTypes>";
			if(getpost('homedelivery')!='') $sXML.="<v9:SpecialServiceTypes>HOME_DELIVERY_PREMIUM</v9:SpecialServiceTypes><v9:HomeDeliveryPremiumDetail><v9:HomeDeliveryPremiumType>" . getpost('homedelivery') . "</v9:HomeDeliveryPremiumType></v9:HomeDeliveryPremiumDetail>";
			if($ordPayProvider!=''){
				//if(int(ordPayProvider)=codpaymentprovider then $sXML.="<v9:SpecialServiceTypes>COD</v9:SpecialServiceTypes><v9:CodDetail><v9:CodCollectionAmount><v9:Currency>CAD</v9:Currency><v9:Amount>XXXFEDEXGRANDTOTXXX</v9:Amount></v9:CodCollectionAmount><v9:CollectionType>ANY</v9:CollectionType></v9:CodDetail>"
			}
			if(@$holdatlocation==TRUE) $sXML.="<v9:SpecialServiceTypes>HOLD_AT_LOCATION</v9:SpecialServiceTypes><v9:HoldAtLocationDetail><v9:PhoneNumber>9052125251</v9:PhoneNumber><v9:LocationContactAndAddress><v9:Address><v9:StreetLines>HAL Address Line 1</v9:StreetLines><v9:City>St-Laurent</v9:City><v9:StateOrProvinceCode>QC</v9:StateOrProvinceCode><v9:PostalCode>H4T2A3</v9:PostalCode><v9:CountryCode>CA</v9:CountryCode></v9:Address></v9:LocationContactAndAddress></v9:HoldAtLocationDetail>";
			$sXML.="</v9:SpecialServicesRequested>";
			$sXML.="<v9:CustomsClearanceDetail>" . (@$customsaccountnumber!='' ? "<v9:DutiesPayment><v9:PaymentType>SENDER</v9:PaymentType></v9:DutiesPayment>" : '') . "<v9:CustomsValue><v9:Currency>" . $countryCurrency . "</v9:Currency><v9:Amount>XXXFEDEXGRANDTOTXXX</v9:Amount></v9:CustomsValue></v9:CustomsClearanceDetail>";
		}
		//$sXML.="<v9:TotalInsuredValue><v9:Currency>" . countryCurrency . "</v9:Currency><v9:Amount>XXXFEDEXGRANDTOTXXX</v9:Amount></v9:TotalInsuredValue>"
		$sXML.="<v9:RateRequestTypes>ACCOUNT</v9:RateRequestTypes><v9:PackageDetail>INDIVIDUAL_PACKAGES</v9:PackageDetail>";
	}elseif($shipType==9){
		$sXML='<?xml version="1.0" encoding="utf-8" ?><q1:DCTRequest xmlns:q1="http://www.dhl.com"><GetQuote>' .
		'<Request><ServiceHeader><SiteID>' . $DHLSiteID . '</SiteID><Password>' . $DHLSitePW . '</Password></ServiceHeader></Request>' .
		'<From><CountryCode>' . $origCountryCode . '</CountryCode><Postalcode>' . $origZip . '</Postalcode></From>' .
		'<BkgDetails><PaymentCountryCode>' . $origCountryCode . '</PaymentCountryCode>' .
		'<Date>' . date('Y-m-d', time()+86400) . '</Date><ReadyTime>PT9H</ReadyTime>' .
		'<DimensionUnit>' . (($adminUnits & 12)==4 || (($adminUnits & 12)==0 && ($adminUnits & 1)==1) ? 'IN' : 'CM') . '</DimensionUnit><WeightUnit>' . (($adminUnits & 1)==1 ? 'LB' : 'KG') . '</WeightUnit><Pieces>';
	}elseif($shipType==10){ // Australia Post
		$packtogether=TRUE; $splitpackat=''; $nosplitlargepacks=TRUE; // Australia Post cannot handle more than one package
	}
	return(TRUE);
}
$packdims=array(0,0,0,0,0,0,0,0); // len : wid : hei : vol : maxlen : maxwid : maxhei : items
function zeropackdims(){
	global $packdims;
	for($zpd=0;$zpd<=7;$zpd++) $packdims[$zpd]=0;
}
function reorderpackagedimensions(){
	global $packdims;
	if($packdims[2]>$packdims[1]){ $apdtemp=$packdims[1]; $packdims[1]=$packdims[2]; $packdims[2]=$apdtemp; }
	if($packdims[1]>$packdims[0]){ $apdtemp=$packdims[0]; $packdims[0]=$packdims[1]; $packdims[1]=$apdtemp; }
	if($packdims[2]>$packdims[1]){ $apdtemp=$packdims[1]; $packdims[1]=$packdims[2]; $packdims[2]=$apdtemp; }
}
function reorderproddims(&$pdims){
	$pdims[0]=(double)$pdims[0]; $pdims[1]=(double)$pdims[1]; $pdims[2]=(double)$pdims[2];
	if($pdims[2]>$pdims[1]){ $apdtemp=$pdims[1]; $pdims[1]=$pdims[2]; $pdims[2]=$apdtemp; }
	if($pdims[1]>$pdims[0]){ $apdtemp=$pdims[0]; $pdims[0]=$pdims[1]; $pdims[1]=$apdtemp; }
	if($pdims[2]>$pdims[1]){ $apdtemp=$pdims[1]; $pdims[1]=$pdims[2]; $pdims[2]=$apdtemp; }
}
function addpackagedimensions($dimens, $apdquant){
	global $packdims,$adminUnits;
	if(($adminUnits & 12)!=0){
		$origdimens=$packdims;
		$proddims=explode('x',$dimens);
		if(@$proddims[0]!=''&&@$proddims[1]!=''&&@$proddims[2]!=''){
			reorderproddims($proddims);
			if($proddims[0]>$packdims[4]) $packdims[4]=$proddims[0];
			if($proddims[1]>$packdims[5]) $packdims[5]=$proddims[1];
			if($proddims[2]>$packdims[6]) $packdims[6]=$proddims[2];
			$proddims[2]=$proddims[2] * $apdquant;
			$thelength=$proddims[0];
			reorderproddims($proddims);
			while($apdquant>4 && $proddims[0]>$proddims[2] * 2 && $proddims[0]>$thelength){
				$proddims[0]=$proddims[0] / 2; $proddims[2]=$proddims[2] * 2; $apdquant=$apdquant / 2;
				reorderproddims($proddims);
			}
			$thelength=$proddims[0]; $thewidth=$proddims[1]; $theheight=$proddims[2];
			$objvol=$thelength * $thewidth * $theheight;
			if($thelength>$packdims[0]) $packdims[0]=$thelength;
			if($thewidth>$packdims[1]) $packdims[1]=$thewidth;
			if($theheight>$packdims[2]) $packdims[2]=$theheight;
			if($objvol + $packdims[3]>$packdims[0] * $packdims[1] * $packdims[2]) $packdims[2]=$packdims[2] + ($origdimens[2]>0 && $origdimens[2] < $theheight ? $origdimens[2] : $theheight);
			if($objvol + $packdims[3]>$packdims[0] * $packdims[1] * $packdims[2]) $packdims[1]=$packdims[1] + ($origdimens[1]>0 && $origdimens[1] < $thewidth ? $origdimens[1] : $thewidth);
			if($objvol + $packdims[3]>$packdims[0] * $packdims[1] * $packdims[2]) $packdims[0]=$packdims[0] + ($origdimens[0]>0 && $origdimens[0] < $thelength ? $origdimens[0] : $thelength);
			$packdims[3]=$packdims[3] + $objvol;
			reorderpackagedimensions();
		}
	}
	$packdims[7]+=$apdquant;
	// print "Bin is : " . $packdims[0] . ':' . $packdims[1] . ':' . $packdims[2] . '=' . ($packdims[0]*$packdims[1]*$packdims[2]) . '<br />';
}
function splitlargepacks(){
	global $packdims,$shipType,$adminUnits,$nosplitlargepacks,$thepweight,$splitpackat,$adminCanPostLogin;
	$slpweight=$thepweight;
	$slpnumpacks=1;
	if($packdims[7]<=1&&@$nosplitlargepacks==TRUE) return 1;
	if($shipType==6){
		if(($adminUnits & 12)==4){ $maxlenplusgirth=118; $maxlength=78; }else{ $maxlenplusgirth=300; $maxlength=200; }
		if(($adminUnits & 3)==1) $maxweight=66; else $maxweight=30;
		if($adminCanPostLogin!='') $nosplitlargepacks=TRUE;
	}elseif($shipType==4 || $shipType==7 || $shipType==8 || $shipType==9){
		if(($adminUnits & 12)==4){ $maxlenplusgirth=165; $maxlength=108; }else{ $maxlenplusgirth=419; $maxlength=274; }
		if(($adminUnits & 3)==1) $maxweight=150; else $maxweight=68;
	}else{ // USPS Default
		if(($adminUnits & 12)==8) $maxlenplusgirth=330; else $maxlenplusgirth=130;
		$maxlength=0;
		$maxweight=70;
	}
	if(is_numeric(@$splitpackat)) $maxweight=(double)$splitpackat;
	if(@$nosplitlargepacks!=TRUE && ($adminUnits & 12)!=0){
		if($packdims[0] + (($packdims[1] + $packdims[2]) * 2)>$maxlenplusgirth){ // Max Length + Girth
			$divisor=1;
			while(($packdims[0]/sqrt($divisor)) + ((($packdims[1]/sqrt($divisor)) + $packdims[2]) * 2)>$maxlenplusgirth)
				$divisor++;
			if($packdims[0]/sqrt($divisor)>$maxlength && $maxlength!=0 && ($packdims[0]/$divisor) + (($packdims[1] + $packdims[2]) * 2) <= $maxlenplusgirth)
				$packdims[0]=$packdims[0]/$divisor;
			else{
				$packdims[0]=$packdims[0]/sqrt($divisor);
				$packdims[1]=$packdims[1]/sqrt($divisor);
			}
			$slpnumpacks *= $divisor;
			$slpweight /= $divisor;
			reorderpackagedimensions();
		}
		if($packdims[0]>$maxlength && $maxlength!=0){
			$packdims[0]=$packdims[0] / 2;
			$slpnumpacks *= 2;
			$slpweight /= 2;
			reorderpackagedimensions();
		}
	}
	if($slpweight>$maxweight && ($packdims[7]<=$slpnumpacks || @$nosplitlargepacks!=TRUE)){
		$slpindex=2;
		while(TRUE){
			if($slpweight / $slpindex < $maxweight){
				$packdims[0]/=$slpindex;
				$slpnumpacks*=$slpindex;
				reorderpackagedimensions();
				break;
			}
			$slpindex++;
		}
	}
	return($slpnumpacks);
}
$packageweight=0;
$packagefreeexemptweight=0;
function islastpacktogether($prodindex){
	global $itemsincart,$cartrows;
	for($tindex=$prodindex+1;$tindex<$itemsincart;$tindex++)
		if(($cartrows[$tindex]['pExemptions'] & 32)!=32) return(FALSE);
	return(TRUE);
}
function addproducttoshipping($apsrs, $prodindex){
	global $shipping,$shipType,$packtogether,$shipThisProd,$somethingToShip,$itemsincart,$intShipping,$international,$shipcountry,$fromshipselector,$packageweight,$packagefreeexemptweight;
	global $rowcounter,$origZip,$destZip,$sXML,$numshipoptions,$allzones,$numzones,$dHighWeight,$adminUnits,$shipCountryCode,$countryCode,$totalshipitems,$allfreeshipexempt;
	global $upspacktype,$splitpackat,$thePQuantity,$thepweight,$totalgoods,$shipfreegoods,$packaging,$packdims,$initialpackweight,$saturdaydelivery_,$royalmail;
	$shipThisProd=TRUE;
	if(($apsrs['pExemptions'] & 32)==32){
		$savepacktogether=$packtogether;
		$savethepweight=$thepweight;
		$savepackageweight=$packageweight;
		$savepackdims=$packdims;
		zeropackdims();
		$packtogether=FALSE;
	}
	if(($apsrs['pExemptions'] & 4)==4){ // No Shipping on this product
		$shipThisProd=FALSE;
	}else
		addpackagedimensions($apsrs['pDims'], ($packtogether ? (int)$apsrs['cartQuantity'] : 1));
	if(($apsrs['pExemptions'] & 16)!=16) $allfreeshipexempt=FALSE;
	if($fromshipselector){
	}elseif($shipType==1){ // Flat rate shipping
		if($shipThisProd){
			// $shipping+=$apsrs['pShipping'] + $apsrs['pShipping2'] * ($apsrs['cartQuantity']-1);
			$intShipping[0][2]+=$apsrs['pShipping'] + $apsrs['pShipping2'] * ($apsrs['cartQuantity']-1);
			if(($apsrs['pExemptions'] & 16)==16) $intShipping[0][7]+=$apsrs['pShipping'] + $apsrs['pShipping2'] * ($apsrs['cartQuantity']-1);
			$somethingToShip=TRUE;
		}
	}elseif($shipType==2 || $shipType==5){ // Weight / Price based shipping
		$havematch=FALSE;
		for($index3=0; $index3 < $numshipoptions; $index3++)
			$dHighest[$index3]=0;
		if(is_array($allzones)){
			if($shipThisProd){
				$somethingToShip=TRUE;
				if($shipType==2) $tmpweight=(double)$apsrs['pWeight']; else $tmpweight=(double)$apsrs['cartProdPrice'];
				if($packtogether){
					$thepweight+=((double)($apsrs['cartQuantity'])*$tmpweight);
					$thePQuantity=1;
				}else{
					$thepweight=$tmpweight + (@$initialpackweight!=''?$initialpackweight:0);
					$thePQuantity=(double)$apsrs['cartQuantity'];
				}
				$packageweight+=(double)($apsrs['cartQuantity'])*$tmpweight;
				if(($apsrs['pExemptions'] & 16)==16) $packagefreeexemptweight+=(double)($apsrs['cartQuantity'])*$tmpweight;
			}
			if(((!$packtogether && $shipThisProd) || ($packtogether && islastpacktogether($prodindex))) && ($thepweight>0 || $shipType==5)){ // Only calculate pack together when we have the total
				for($index2=0; $index2 < $numzones; $index2++){
					if($allzones[$index2]['zcWeight']>=$thepweight){
						$havematch=TRUE;
						for($index3=0; $index3 < $numshipoptions; $index3++){
							if($allzones[$index2]['zcRatePC'.($index3==0?'':$index3+1)]!=0) // Percentage
								$intShipping[$index3][2]+=((double)$allzones[$index2]['zcRate'.($index3==0?'':$index3+1)]*$thePQuantity*$thepweight)/100.0;
							else
								$intShipping[$index3][2]+=((double)$allzones[$index2]['zcRate'.($index3==0?'':$index3+1)]*$thePQuantity);
							if((double)$allzones[$index2]['zcRate'.($index3==0?'':$index3+1)]==-99999.0) $intShipping[$index3][3]=FALSE;
							if($shipCountryCode==$countryCode && $saturdaydelivery_ && @$royalmail){
								if($index3==2 || $index3==3){
									if($index3==2) $intShipping[$index3][2]*=1.2;
									$intShipping[$index3][2]+=3;
								}else
									$intShipping[$index3][3]=FALSE;
							}
						}
						break;
					}
					$dHighWeight=$allzones[$index2]['zcWeight'];
					for($index3=0; $index3 < $numshipoptions; $index3++){
						if($allzones[$index2]['zcRatePC'.($index3==0?'':$index3+1)]!=0) // Percentage
							$dHighest[$index3]=($allzones[$index2]['zcRate'.($index3==0?'':$index3+1)]*$dHighWeight)/100.0;
						else
							$dHighest[$index3]=$allzones[$index2]['zcRate'.($index3==0?'':$index3+1)];
					}
				}
				if(! $havematch){
					for($index3=0; $index3 < $numshipoptions; $index3++){
						$intShipping[$index3][2]+=$dHighest[$index3];
						if($dHighest[$index3]==-99999.0) $intShipping[$index3][3]=FALSE;
					}
					if($allzones[0]['zcWeight']<0){
						$dHighWeight=$thepweight - $dHighWeight;
						while($dHighWeight>0){
							for($index3=0; $index3 < $numshipoptions; $index3++)
								$intShipping[$index3][2]+=((double)($allzones[0]['zcRate'.($index3==0?'':$index3+1)])*$thePQuantity);
							$dHighWeight+=$allzones[0]['zcWeight'];
						}
					}
				}
			}
		}
	}elseif($shipType==3){ // USPS Shipping
		if($packtogether){
			if($shipThisProd){
				$somethingToShip=TRUE;
				$thepweight+=((double)$apsrs['pWeight'] * (int)$apsrs['cartQuantity']);
				$packageweight+=((double)$apsrs['pWeight'] * (int)$apsrs['cartQuantity']);
				if(($apsrs['pExemptions'] & 16)==16) $packagefreeexemptweight+=((double)$apsrs['pWeight'] * (int)$apsrs['cartQuantity']);
			}
			if(islastpacktogether($prodindex) && $thepweight>0){
				$numpacks=splitlargepacks();
				if($international!='')
					$sXML.=addUSPSInternational($rowcounter,$thepweight / $numpacks,$numpacks,'Package',$shipcountry,$totalgoods-$shipfreegoods);
				else
					$sXML.=addUSPSDomestic($rowcounter,'Parcel',$origZip,$destZip,$thepweight / $numpacks,$numpacks,'REGULAR','True');
				$rowcounter++;
				zeropackdims();
			}
		}else{
			if($shipThisProd){
				$somethingToShip=TRUE;
				$thepweight=$apsrs['pWeight'] + (@$initialpackweight!=''?$initialpackweight:0);
				$packageweight+=$thepweight;
				if(($apsrs['pExemptions'] & 16)==16) $packagefreeexemptweight+=$thepweight;
				$numpacks=splitlargepacks();
				if($international!='')
					$sXML.=addUSPSInternational($rowcounter,$thepweight / $numpacks,$apsrs['cartQuantity']*$numpacks,'Package',$shipcountry,$apsrs['cartProdPrice']);
				else
					$sXML.=addUSPSDomestic($rowcounter,'Parcel',$origZip,$destZip,$thepweight / $numpacks,$apsrs['cartQuantity']*$numpacks,'REGULAR','True');
				$rowcounter++;
				zeropackdims();
			}
		}
	}elseif($shipType==4 || $shipType>=6){ // UPS Shipping OR Canada Post OR FedEx OR DHL
		if($shipType==4){
			$packaging='02';
			if(@$packaging!=''){
				if($packaging=='envelope') $packaging='01';
				if($packaging=='pak') $packaging='04';
				if($packaging=='box') $packaging='21';
				if($packaging=='tube') $packaging='03';
				if($packaging=='10kgbox') $packaging='25';
				if($packaging=='25kgbox') $packaging='24';
			}elseif(@$upspacktype!='')
				$packaging=$upspacktype;
		}
		if($packtogether){
			if($shipThisProd){
				$somethingToShip=TRUE;
				$thepweight+=((double)$apsrs['pWeight'] * (int)$apsrs['cartQuantity']);
				$packageweight+=((double)$apsrs['pWeight'] * (int)$apsrs['cartQuantity']);
				if(($apsrs['pExemptions'] & 16)==16) $packagefreeexemptweight+=((double)$apsrs['pWeight'] * (int)$apsrs['cartQuantity']);
			}
			if(islastpacktogether($prodindex) && $thepweight>0){
				$numpacks=splitlargepacks();
				for($index3=0;$index3 < $numpacks; $index3++)
					if($shipType==4)
						$sXML.=addUPSInternational($thepweight / $numpacks,$adminUnits,$packaging,$shipCountryCode,$totalgoods-$shipfreegoods,$packdims);
					elseif($shipType==6)
						$sXML.=addCanadaPostPackage($thepweight / $numpacks,$adminUnits,$packaging,$shipCountryCode,$totalgoods-$shipfreegoods,$packdims);
					elseif($shipType==9)
						$sXML.=addDHLPackage($thepweight / $numpacks,$adminUnits,$packaging,$shipCountryCode,$totalgoods-$shipfreegoods,$packdims);
					elseif($shipType==7||$shipType==8)
						$sXML.=addFedexPackage($thepweight / $numpacks,$totalgoods-$shipfreegoods,$packdims);
				if($shipType!=10) zeropackdims();
			}
		}else{
			if($shipThisProd){
				$somethingToShip=TRUE;
				$thepweight=$apsrs['pWeight'] + (@$initialpackweight!=''?$initialpackweight:0);
				$packageweight+=$thepweight;
				if(($apsrs['pExemptions'] & 16)==16) $packagefreeexemptweight+=$thepweight;
				$numpacks=splitlargepacks();
				for($index2=0;$index2 < (int)$apsrs['cartQuantity']; $index2++)
					for($index3=0;$index3 < $numpacks; $index3++)
						if($shipType==4)
							$sXML.=addUPSInternational($thepweight / $numpacks,$adminUnits,$packaging,$shipCountryCode,$apsrs['cartProdPrice'],$packdims);
						elseif($shipType==6)
							$sXML.=addCanadaPostPackage($thepweight / $numpacks,$adminUnits,$packaging,$shipCountryCode,$apsrs['cartProdPrice'],$packdims);
						elseif($shipType==9)
							$sXML.=addDHLPackage($thepweight / $numpacks,$adminUnits,$packaging,$shipCountryCode,$apsrs['cartProdPrice'],$packdims);
						elseif($shipType==7||$shipType==8)
							$sXML.=addFedexPackage($thepweight / $numpacks,$apsrs['cartProdPrice'],$packdims);
				zeropackdims();
			}
		}
	}
	if(($apsrs['pExemptions'] & 32)==32){
		$thepweight=$savethepweight;
		$packageweight=$savepackageweight;
		$packtogether=$savepacktogether;
		$packdims=$savepackdims;
	}
}
function calculateshipping(){
	global $shipType,$freeshipmethodexists,$multipleoptions,$somethingToShip,$willpickuptext,$willpickupcost,$numshipoptions,$upsUser,$upsPw,$shipCountryCode,$destZip,$totalgoods,$thesessionid,$handling,$selectedshiptype,$willpickup_,$DHLAccountNo,$origCountryCode,$countryCurrency,$smartPostHub;
	global $shipping,$shipMethod,$success,$errormsg,$sXML,$intShipping,$international,$iTotItems,$uspsmethods,$numuspsmeths,$shipstate,$maxshipoptions,$saturdaydelivery_,$saturdaypickup,$upsnegdrates,$fromshipselector,$packageweight,$packagefreeexemptweight,$shipCountryID,$ordCity;
	if($fromshipselector){
	}elseif($shipType==1){
		$freeshipmethodexists=TRUE;
	}elseif($shipType==3 && $somethingToShip){
		$sXML.='</' . $international . 'Rate' . ($international=='' ? 'V4' : 'V2') . 'Request>';
		$success=USPSCalculate($sXML,$international,$errormsg,$intShipping);
		if(substr($errormsg, 0, 30)=='Warning - Bound Printed Matter') $success=TRUE;
		if($success){
			$maxsopt=0;
			for($indexmso=0; $indexmso<$maxshipoptions; $indexmso++){
				$shipRow=$intShipping[$indexmso];
				if($iTotItems==$shipRow[3]){
					$intShipping[$indexmso][3]=TRUE;
					$maxsopt=$indexmso;
					for($index2=0;$index2<$numuspsmeths;$index2++){
						if(str_replace('-',' ',strtolower($shipRow[5]))==str_replace('-',' ',strtolower($uspsmethods[$index2]['uspsMethod']))){
							$intShipping[$indexmso][4]=$uspsmethods[$index2]['uspsFSA'];
						}
					}
				}else
					$intShipping[$indexmso][3]=FALSE;
			}
			for($ssaindex2=0; $ssaindex2 <= $maxsopt-1; $ssaindex2++){
				if($intShipping[$ssaindex2][3]==TRUE){
					$csmatch=$intShipping[$ssaindex2][0];
					for($ssaindex=$ssaindex2+1; $ssaindex <= $maxsopt; $ssaindex++){
						if($csmatch==$intShipping[$ssaindex][0]&&!$intShipping[$ssaindex][4]) $intShipping[$ssaindex][3]=FALSE;
					}
				}
			}
		}
	}elseif($shipType==4 && $somethingToShip){
		$sXML.='<ShipmentServiceOptions>' . ($saturdaydelivery_ ? '<SaturdayDelivery/>' : '') . (@$saturdaypickup==TRUE ? '<SaturdayPickup/>' : '') . '</ShipmentServiceOptions>' . (@$upsnegdrates==TRUE?'<RateInformation><NegotiatedRatesIndicator /></RateInformation>':'') . '</Shipment></RatingServiceSelectionRequest>';
		if(trim($upsUser)!='' && trim($upsPw)!='')
			$success=UPSCalculate($sXML,$international,$errormsg,$intShipping);
		else{
			$success=FALSE;
			$errormsg='You must register with UPS by logging on to your online admin section and clicking the &quot;Register with UPS&quot; link before you can use the UPS OnLine&reg; Shipping Rates and Services Selection';
		}
	}elseif($shipType==6 && $somethingToShip){
		if(getpost('shipping')==''){
			$sXML.='</mailing-scenario></rate:get-rates-request></soapenv:Body></soapenv:Envelope>';
			$success=CanadaPostCalculate($sXML,$international,$errormsg,$intShipping);
		}
	}elseif(($shipType==7 || $shipType==8) && $somethingToShip){
		$sXML=str_replace('XXXFEDEXGRANDTOTXXX',$totalgoods,$sXML);
		$sXML.='</v9:RequestedShipment></v9:RateRequest></soapenv:Body></soapenv:Envelope>';
		if($shipType==8 && $smartPostHub==''){ $success=FALSE; $errormsg='SmartPost Hub ID not set'; }else $success=fedexcalculate($sXML,$international,$errormsg,$intShipping);
	}elseif($shipType==9 && $somethingToShip){
		if($shipCountryCode=='IE' && $ordCity=='') $ordCity='Dublin';
		$sXML.='</Pieces><PaymentAccountNumber>' . $DHLAccountNo . '</PaymentAccountNumber><IsDutiable>' . ($origCountryCode==$shipCountryCode || (iseuropean($origCountryCode) && iseuropean($shipCountryCode)) ? 'N' : 'Y') . '</IsDutiable>' .
			'<NetworkTypeCode>AL</NetworkTypeCode></BkgDetails><To><CountryCode>' . $shipCountryCode . '</CountryCode><Postalcode>' . $destZip . '</Postalcode>' . (zipisoptional($shipCountryID)||$shipCountryID==65?'<City>'.$ordCity.'</City>':'') . '</To>' .
			'<Dutiable><DeclaredCurrency>' . $countryCurrency . '</DeclaredCurrency><DeclaredValue>' . $totalgoods . '</DeclaredValue></Dutiable></GetQuote></q1:DCTRequest>';
		$success=dhlcalculate($sXML,$international,$errormsg,$intShipping);
	}elseif($shipType==10 && $somethingToShip)
		$success=auspostcalculate($packageweight,$international,$errormsg,$intShipping);
	if($success && getpost('shipping')=='' && $somethingToShip && ! $fromshipselector && $shipType>=1){
		$totShipOptions=0;
		$multipleoptions=TRUE;
		for($indexmso=0; $indexmso<$maxshipoptions; $indexmso++){
			if($intShipping[$indexmso][3]==TRUE){
				$totShipOptions++;
				if($intShipping[$indexmso][4]) $freeshipmethodexists=TRUE;
			}
			if($shipType>=2 && $packageweight>0){
				$intShipping[$indexmso][7]=$intShipping[$indexmso][2]*($packagefreeexemptweight/$packageweight);
			}
		}
		if($totShipOptions==0 && ! $willpickup_){
			$multipleoptions=FALSE;
			$success=FALSE;
			$errormsg=$GLOBALS['xxNoMeth'];
		}
		if($willpickup_) $multipleoptions=TRUE;
	}
	return($success);
}
function saveshippingoptions(){
	global $shipType,$intShipping,$orderid,$maxshipoptions;
	$maxindex=0;
	if($shipType>=1 && is_numeric($orderid)){
		$sSQL="SELECT MAX(soIndex) AS maxindex FROM shipoptions WHERE soOrderID='" . escape_string($orderid) . "'";
		$result=ect_query($sSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		if(! is_null($rs['maxindex'])) $maxindex=$rs['maxindex']+1;
		ect_free_result($result);
		for($indexmso=0; $indexmso<$maxshipoptions; $indexmso++){
			if($intShipping[$indexmso][3]==TRUE){
				$sSQL='INSERT INTO shipoptions (soOrderID,soIndex,soMethodName,soCost,soFreeShipExempt,soFreeShip,soShipType,soDeliveryTime,soDateAdded) VALUES (' .
					$orderid . ',' . $maxindex . ",'" . escape_string($intShipping[$indexmso][0]) . "'," . $intShipping[$indexmso][2] . ',' . $intShipping[$indexmso][7] . ',' .
					$intShipping[$indexmso][4] . ',' . $shipType . ",'" . escape_string($intShipping[$indexmso][1]) . "','" . date('Y-m-d', time()) . "')";
				ect_query($sSQL) or ect_error();
				$maxindex++;
			}
		}
	}
}
$numshiprate=0; $numshiprateingroup=0;
function writeshippingoption($shipcost,$freeshipexempt,$freeship,$shipmethod,$isselected,$wsodelivery){
	global $combineshippinghandling,$numshiprate,$numshiprateingroup,$shippingoptionsasradios,$orighandling,$taxHandling,$orighandlingpercent,$stateTaxRate,$countryTaxRate,$freeshippingapplied,$totalgoods,$totaldiscounts,$freeshippingincludeshandling,$handlingeligableitem,$handlingeligablegoods,$shipType,$mobilebrowser;
	if($freeshippingapplied && $freeship==1) $wsofreeshipamnt=($shipcost-$freeshipexempt); else $wsofreeshipamnt=0;
	$wsohandling=round($orighandling, 2);
	if($handlingeligableitem==FALSE)
		$wsohandling=0;
	elseif(@$orighandlingpercent!=0){
		$temphandling=((($totalgoods + $shipcost + $wsohandling) - ($totaldiscounts + $wsofreeshipamnt)) * $orighandlingpercent / 100.0);
		if($handlingeligablegoods < $totalgoods && $totalgoods>0) $temphandling=$temphandling * ($handlingeligablegoods / $totalgoods);
		$wsohandling+=$temphandling;
	}
	if($taxHandling==1) $wsohandling+=((double)$wsohandling*((double)$stateTaxRate+(double)$countryTaxRate))/100.0;
	if($freeship==1 && @$freeshippingincludeshandling==TRUE) $wsohandling=0;
	if(@$shippingoptionsasradios==TRUE)
		print '<div class="shiprateline"><div class="shiprateradio"><input type="radio" value="RATE"'.($isselected?' checked="checked"':'').' onclick="updateshiprate(this,'.$numshiprate.')" /></div><div class="shipratemethod'.($numshiprateingroup==0?' shipratemethodselected':'').'">'.str_replace(' ','&nbsp;',$shipmethod.' '.($wsodelivery!='' && ! $mobilebrowser?'(' . $wsodelivery . ') ':'').(@$combineshippinghandling?FormatEuroCurrency(($shipcost+$wsohandling)-$wsofreeshipamnt):FormatEuroCurrency($shipcost-$wsofreeshipamnt))).'</div></div>';
	else
		print '<option value="RATE"'.($isselected?' selected="selected"':'').'>'.$shipmethod.' '.($wsodelivery!='' && ! $mobilebrowser?'(' . $wsodelivery . ') ':'').(@$combineshippinghandling?FormatEuroCurrency(($shipcost+$wsohandling)-$wsofreeshipamnt):FormatEuroCurrency($shipcost-$wsofreeshipamnt)).'</option>';
	$numshiprate++;
	$numshiprateingroup++;
}
$currShipType='';
function showshippingselect(){
	global $fromshipselector,$shipType,$shippingoptionsasradios,$numshiprate,$currShipType,$selectedshiptype,$maxshipoptions,$intShipping,$numshiprateingroup,$saturdaydelivery_,$royalmail,$ordComLoc;
	if(! $fromshipselector) calculateshippingdiscounts(FALSE);
	if($shipType>=1){
		if(@$shippingoptionsasradios!=TRUE){
			print '<select size="1" onchange="updateshiprate(this,(this.selectedIndex'.($fromshipselector?'-1':'').')+'.$numshiprate.')">';
			if($fromshipselector) print '<option value="">'.$GLOBALS['xxPlsSel'].'</option>';
		}
		for($indexmso=0; $indexmso<$maxshipoptions; $indexmso++){
			if($intShipping[$indexmso][3]){
				if($currShipType=='') $currShipType=$intShipping[$indexmso][6];
				if($currShipType!=$intShipping[$indexmso][6]){
					$currShipType=$intShipping[$indexmso][6];
					$numshiprateingroup=0;
					if(@$shippingoptionsasradios!=TRUE) print '</select>';
					print '</div></div><div class="shiptableline"><div class="shiptablelogo">' . getshiplogo($currShipType) . '</div><div class="shiptablerates">';
					if(@$shippingoptionsasradios!=TRUE) print '<select size="1" onchange="updateshiprate(this,(this.selectedIndex-1)+'.$numshiprate.')"><option value="">'.$GLOBALS['xxPlsSel'].'</option>';
				}
				writeshippingoption(round($intShipping[$indexmso][2], 2), round($intShipping[$indexmso][7], 2), $intShipping[$indexmso][4], $intShipping[$indexmso][0], $indexmso==$selectedshiptype, $intShipping[$indexmso][1]);
			}
		}
		if(@$shippingoptionsasradios!=TRUE) print '</select>';
		if(@$royalmail && $shipType==2 && $saturdaydelivery_)
			print '<div class="nosaturdaydelivery">First and Second Class post are not available with Saturday Delivery.</div>';
	}
}
function getuspsinsurancerate($theamount){
	if($theamount<=0)
		return(0);
	elseif($theamount<=50)
		return(1.75);
	elseif($theamount<=100)
		return(2.25);
	elseif($theamount<=200)
		return(2.75);
	else
		return(4.70 + (1.0 * floor(($theamount-200.01) / 100.0)));
}
function insuranceandtaxaddedtoshipping(){
	global $shipinsuranceamt,$somethingToShip,$wantinsurance_,$addshippinginsurance,$maxshipoptions,$useuspsinsurancerates;
	global $totalgoods,$shipping,$taxShipping,$stateTaxRate,$countryTaxRate,$intShipping,$shipType;
	if((is_numeric(@$shipinsuranceamt) || @$useuspsinsurancerates==TRUE) && $somethingToShip){
		if((($wantinsurance_ && abs(@$addshippinginsurance)==2) || abs(@$addshippinginsurance)==1) && @$useuspsinsurancerates==TRUE && $shipType==3){
			for($index3=0; $index3 < $maxshipoptions; $index3++)
				$intShipping[$index3][2]+=getuspsinsurancerate((double)$totalgoods);
			$shipping+=getuspsinsurancerate((double)$totalgoods);
		}elseif(! is_numeric(@$shipinsuranceamt)){
			// Nothing
		}elseif(($wantinsurance_ && @$addshippinginsurance==2) || @$addshippinginsurance==1){
			for($index3=0; $index3 < $maxshipoptions; $index3++)
				$intShipping[$index3][2]+=(((double)$totalgoods*(double)$shipinsuranceamt)/100.0);
			$shipping+=(((double)$totalgoods*(double)$shipinsuranceamt)/100.0);
		}elseif(($wantinsurance_ && @$addshippinginsurance==-2) || @$addshippinginsurance==-1){
			for($index3=0; $index3 < $maxshipoptions; $index3++)
				$intShipping[$index3][2]+=$shipinsuranceamt;
			$shipping+=$shipinsuranceamt;
		}
	}
	if(@$taxShipping==1){
		for($index3=0; $index3 < $maxshipoptions; $index3++)
			$intShipping[$index3][2]+=((double)$intShipping[$index3][2]*((double)$stateTaxRate+(double)$countryTaxRate))/100.0;
		$shipping+=((double)$shipping*((double)$stateTaxRate+(double)$countryTaxRate))/100.0;
	}
}
function calculatetaxandhandling(){
	global $handlingchargepercent,$handling,$totalgoods,$shipping,$totaldiscounts,$freeshipamnt,$taxHandling,$stateTaxRate,$countryTaxRate,$taxShipping,$showtaxinclusive,$overridecurrency,$orcdecplaces,$homeCountryTaxRate;
	global $stateTax,$countryTax,$origCountryID,$shipCountryID,$shipStateAbbrev,$usehst,$statetaxfree,$countrytaxfree,$proratashippingtax,$perproducttaxrate,$handlingeligablegoods,$handlingeligableitem,$shiphomecountry,$shippingtax;
	if($handlingeligableitem==FALSE)
		$handling=0;
	else{
		if($handlingchargepercent!=0){
			$temphandling=((($totalgoods + $shipping + $handling) - ($totaldiscounts + $freeshipamnt)) * $handlingchargepercent / 100.0);
			if($handlingeligablegoods < $totalgoods && $totalgoods>0) $temphandling=$temphandling * ($handlingeligablegoods / $totalgoods);
			$handling+=$temphandling;
		}
		if(@$taxHandling==1) $handling+=((double)$handling*((double)$stateTaxRate+(double)$countryTaxRate))/100.0;
	}
	if($origCountryID==2 && $shipCountryID==2 && ($shipStateAbbrev=='NB' || $shipStateAbbrev=='NF' || $shipStateAbbrev=='NS' || $shipStateAbbrev=='ON' || $shipStateAbbrev=='PE')) $usehst=TRUE; else $usehst=FALSE;
	if($totalgoods>0){
		$stateTax=((double)$totalgoods-((double)$totaldiscounts+(double)$statetaxfree))*(double)$stateTaxRate/100.0;
		if(@$perproducttaxrate!=TRUE) $countryTax=((double)$totalgoods-((double)$totaldiscounts+(double)$countrytaxfree))*(double)$countryTaxRate/100.0;
		if(@$showtaxinclusive===3 && $homeCountryTaxRate>0){
			$countryTax=round(($totalgoods-($totaldiscounts+$countrytaxfree)) / ((100+$homeCountryTaxRate)/$homeCountryTaxRate),2);
			$totalgoods-=$countryTax;
			if($countryTaxRate!=$homeCountryTaxRate){
				if($countryTaxRate!=0) $countryTax*=($countryTaxRate/$homeCountryTaxRate); else $countryTax=0;
			}
		}
	}
	if(@$taxShipping==2 && ($shipping - $freeshipamnt>0)){
		if(@$proratashippingtax==TRUE){
			if($totalgoods>0) $stateTax+=(((double)$totalgoods-((double)$totaldiscounts+(double)$statetaxfree)) / $totalgoods) * (((double)$shipping-(double)$freeshipamnt)*(double)$stateTaxRate/100.0);
		}else
			$stateTax+=(((double)$shipping-(double)$freeshipamnt)*(double)$stateTaxRate/100.0);
		$shippingtax=(((double)$shipping-(double)$freeshipamnt)*(double)$countryTaxRate/100.0);
		$countryTax+=$shippingtax;
	}
	if(@$taxHandling==2){
		$stateTax+=((double)$handling*(double)$stateTaxRate/100.0);
		$countryTax+=((double)$handling*(double)$countryTaxRate/100.0);
	}
	if($stateTax < 0) $stateTax=0;
	if($countryTax < 0) $countryTax=0;
	if($usehst){
		$countryTax=round($stateTax+$countryTax,(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
		$stateTax=0;
	}else{
		$stateTax=round($stateTax,(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
		$countryTax=round($countryTax,(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
	}
	$handling=round($handling,(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
	if($GLOBALS['showtaxinclusive']!=0) $_SESSION['xscountrytax']=$countryTax;
	if($GLOBALS['showtaxinclusive']==3) $_SESSION['xscountrytax']=$shippingtax;
}
function do_stock_check($sublevels,&$hasbackorder,&$hasstockwarning){
	global $quantity,$cartID,$outofstockarr,$WSP,$addedprods,$numaddedprods;
	$sameitemstock=array();
	$gotstock=TRUE;
	$hasbackorder=FALSE;
	$sSQL='SELECT cartID,cartQuantity FROM cart WHERE cartCompleted=0 AND ' . getsessionsql() . ' ORDER BY cartDateAdded';
	$result3=ect_query($sSQL) or ect_error();
	while($rs3=ect_fetch_assoc($result3)){
		$cartID=$rs3['cartID'];
		$thequant=$rs3['cartQuantity'];
		$pID='';
		$sSQL='SELECT pInStock,pID,pStockByOpts,'.$WSP."pPrice,pBackOrder,pSell FROM cart LEFT JOIN products ON cart.cartProdId=products.pID WHERE cartID='" . $cartID . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$pID=trim($rs['pID']);
			$pInStock=(int)$rs['pInStock'];
			$pStockByOpts=(int)$rs['pStockByOpts'];
			$pPrice=$rs['pPrice'];
			$pBackOrder=($rs['pBackOrder']!=0);
			$pSell=$rs['pSell'];
		}
		ect_free_result($result);
		if($pID!=''){
			if($GLOBALS['useStockManagement']){
				$thisiteminstock=TRUE;
				if(($quantity=$thequant)==0){
					$gotstock=FALSE;
				}elseif((int)$pStockByOpts!=0){
					$sSQL="SELECT coID,optStock,coOptID FROM cart INNER JOIN cartoptions ON cart.cartID=cartoptions.coCartID INNER JOIN options ON cartoptions.coOptID=options.optID INNER JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optType IN (-4,-2,-1,1,2,4) AND cartID='" . $cartID . "'";
					$result=ect_query($sSQL) or ect_error();
					while($rs=ect_fetch_assoc($result)){
						$sameitems=0;
						foreach($sameitemstock as $samestockitem){
							if($samestockitem[0]==$rs['coOptID'] && $samestockitem[2]==TRUE) $sameitems+=$samestockitem[1];
						}
						$pInStock=(int)$rs['optStock'];
						if(($pInStock-$sameitems) < $quantity){
							$thisiteminstock=FALSE;
							$quantity=($pInStock-$sameitems);
							if($quantity < 0) $quantity=0;
							if($sublevels && ! $pBackOrder) ect_query('UPDATE cart SET cartQuantity='.$quantity.' WHERE (cartCompleted=0 OR cartCompleted=3) AND cartID='.$cartID) or ect_error();
							if($pBackOrder) $hasbackorder=TRUE; else $gotstock=FALSE;
							array_push($outofstockarr, array($rs['coID'], TRUE, $pID, $pPrice, $pBackOrder));
						}
						array_push($sameitemstock, array($rs['coOptID'], $thequant, TRUE));
					}
					ect_free_result($result);
				}else{
					$sameitems=0;
					foreach($sameitemstock as $samestockitem){
						if($samestockitem[0]==$pID && $samestockitem[2]==FALSE) $sameitems+=$samestockitem[1];
					}
					if($pInStock < ($thequant+$sameitems)){
						$thisiteminstock=FALSE;
						$quantity=($pInStock-$sameitems);
						if($quantity < 0) $quantity=0;
						if($sublevels && ! $pBackOrder) ect_query('UPDATE cart SET cartQuantity='.$quantity.' WHERE (cartCompleted=0 OR cartCompleted=3) AND cartID='.$cartID) or ect_error();
						if($pBackOrder) $hasbackorder=TRUE; else $gotstock=FALSE;
						array_push($outofstockarr, array($cartID, FALSE, $pID, $pPrice, $pBackOrder));
					}
					array_push($sameitemstock, array($pID, $thequant, FALSE));
				}
				for($index2=0; $index2<$numaddedprods; $index2++){
					if($addedprods[$index2][5]==$cartID){
						if(! $thisiteminstock) $addedprods[$index2][4]=($pBackOrder?3:2);
						if(! $pBackOrder) $addedprods[$index2][2]=$quantity;
					}
				}
			}elseif($pSell==0 && $pBackOrder!=0){
				$hasbackorder=TRUE;
				for($index2=0; $index2<$numaddedprods; $index2++){
					if($addedprods[$index2][5]==$cartID) $addedprods[$index2][4]=3;
				}
			}
		}
	}
	$hasstockwarning=! $gotstock;
	if($sublevels){
		foreach($outofstockarr as $outofstockitem)
			checkpricebreaks($outofstockitem[2], $outofstockitem[3]);
	}
}
function vrhmac2($key, $text){
	$idatastr='                                                                ';
	$odatastr='                                                                ';
	$hkey=(string)substr($key,0,64);
	$idatastr.=$text;
	for($i=0; $i<64; $i++){
		$idata[$i]=$ipad[$i]=0x36;
		$odata[$i]=$opad[$i]=0x5C;
	}
	for($i=0; $i< strlen($hkey); $i++){
		$ipad[$i] ^= ord($hkey{$i});
		$opad[$i] ^= ord($hkey{$i});
		$idata[$i]=($ipad[$i] & 0xFF);
		$odata[$i]=($opad[$i] & 0xFF);
	}
	for($i=0; $i< strlen($text); $i++)
		$idata[64+$i]=ord($text{$i}) & 0xFF;
	for($i=0; $i< strlen($idatastr); $i++)
		$idatastr{$i}=chr($idata[$i] & 0xFF);
	for($i=0; $i< strlen($odatastr); $i++)
		$odatastr{$i}=chr($odata[$i] & 0xFF);
	$innerhashout=md5($idatastr);
	for($i=0; $i<16; $i++)
		$odatastr.=chr(hexdec(substr($innerhashout,$i*2,2)));
	return md5($odatastr);
}
function checkdeletecart($thecartid){
	global $giftwrappingid;
	$sSQL="SELECT cartID,cartListID,cartClientID,listOwner,cartProdID FROM cart LEFT JOIN customerlists ON cart.cartListID=customerlists.listID WHERE (cartCompleted=0 OR cartCompleted=3) AND cartID='".escape_string($thecartid)."' AND " . getsessionsql();
	$result=ect_query($sSQL) or ect_error();
	if($rs2=ect_fetch_assoc($result)){
		if(! is_null($rs2['listOwner'])) $listowner=(int)$rs2['listOwner']; else $listowner=0;
		if($rs2['cartListID']>0 && $listowner!=@$_SESSION['clientID'])
			ect_query("UPDATE cart SET cartCompleted=3,cartOrderID=0,cartClientID=".$rs2['listOwner']." WHERE cartID='".escape_string($thecartid)."'") or ect_error();
		else{
			if($rs2['cartProdID']==$giftwrappingid) ect_query("UPDATE cart SET cartGiftWrap=0 WHERE " . getsessionsql()) or ect_error();
			ect_query("DELETE FROM cart WHERE cartID='".escape_string($thecartid)."'") or ect_error();
			ect_query("DELETE FROM cartoptions WHERE coCartID='".escape_string($thecartid)."'") or ect_error();
			ect_query("DELETE FROM giftcertificate WHERE gcCartID='".escape_string($thecartid)."'") or ect_error();
			updategiftwrap();
		}
	}elseif(@$_SESSION['clientID']!=''){
		ect_free_result($result);
		$sSQL="SELECT cartID FROM cart INNER JOIN customerlists ON cart.cartListID=customerlists.listID WHERE cartID='".escape_string($thecartid)."' AND listOwner=" . $_SESSION['clientID'];
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result)>0) ect_query("UPDATE cart SET cartListID=0 WHERE cartID='".escape_string($thecartid)."'") or ect_error();
	}
	ect_free_result($result);
}
function writeshippingflags($costage){
	global $willpickuptext,$willpickupcost,$willpickup_,$commercialloc,$saturdaydelivery,$addshippinginsurance,$allowsignaturerelease,$signatureoption,$insidedelivery,$ordComLoc,$redstar,$holdatlocation,$homedelivery,$tleft,$tright,$ordName,$ordPayProvider;
	$hasshipflag=FALSE;
	if($willpickup_ || @$commercialloc || @$saturdaydelivery || abs(@$addshippinginsurance)==2 || (@$allowsignaturerelease && @$signatureoption!='') || @$insidedelivery || @$holdatlocation || @$homedelivery) $hasshipflag=TRUE;
	if($hasshipflag) print '<div class="coshipflagscontainer">';
	if(@$willpickuptext!=''){ ?>
			<div class="billformrow"><div class="cobhl cobhl<?php print $costage?> cdshipftflag cdformtwillpickup"><input type="checkbox" name="willpickup" value="Y" <?php
				if($willpickup_) print 'checked="checked" ';
				if($costage==3) print 'onchange="setchangeflag(this.checked,\'w\')" '?>/></div>
			<div class="cobll cobll<?php print $costage?> cdshipflag cdformwillpickup"><?php print $willpickuptext . (@$willpickupcost!=''?' (' . FormatEuroCurrency($willpickupcost) . ')' : '')?></div></div>
<?php
	}
	if(!$willpickup_){
		if(@$commercialloc==TRUE){ ?>
			<div class="billformrow"><div class="cobhl cobhl<?php print $costage?> cdshipftflag"><input type="checkbox" name="commercialloc" value="Y" <?php
			if(($ordComLoc & 1)==1 || ($ordName=='' && @$commercialloc===2)) print 'checked="checked" ';
			if($costage==3) print 'onchange="setchangeflag(this.checked,0)" '?>/></div>
			<div class="cobll cobll<?php print $costage?> cdshipflag"><?php print $GLOBALS['xxComLoc']?></div></div>
<?php	}
		if(@$saturdaydelivery==TRUE){ ?>
			<div class="billformrow"><div class="cobhl cobhl<?php print $costage?> cdshipftflag"><input type="checkbox" name="saturdaydelivery" value="Y" <?php
			if(($ordComLoc & 4)==4) print 'checked="checked" ';
			if($costage==3) print 'onchange="setchangeflag(this.checked,2)" '?>/></div>
			<div class="cobll cobll<?php print $costage?> cdshipflag"><?php print $GLOBALS['xxSatDel']?></div></div>
<?php	}
		if(abs(@$addshippinginsurance)==2){ ?>
			<div class="billformrow"><div class="cobhl cobhl<?php print $costage?> cdshipftflag"><?php
				if(@$GLOBALS['forceinsuranceselection']&&$costage!=3)
					print '<select name="wantinsurance" size="1"><option value="">'.$GLOBALS['xxPlsSel'].'</option><option value="">'.$GLOBALS['xxNo'].'</option><option value="Y">'.$GLOBALS['xxYes'].'</option></select>';
				else
					print '<input type="checkbox" name="wantinsurance" value="Y" '.(($ordComLoc & 2)==2?'checked="checked"':'').($costage==3?'onchange="setchangeflag(this.checked,1)" ':'').'/>';?></div>
			<div class="cobll cobll<?php print $costage?> cdshipflag"><?php print @$GLOBALS['forceinsuranceselection']?$GLOBALS['xxChoIns']:$GLOBALS['xxWantIns']?></div></div>
<?php	}
		if(@$allowsignaturerelease==TRUE && @$signatureoption!=''){ ?>
			<div class="billformrow"><div class="cobhl cobhl<?php print $costage?> cdshipftflag"><input type="checkbox" name="signaturerelease" value="Y" <?php
			if(($ordComLoc & 8)==8) print 'checked="checked" ';
			'onchange="setchangeflag(this.checked,3)" '?>/></div>
			<div class="cobll cobll<?php print $costage?> cdshipflag"><?php print $GLOBALS['xxSigRel']?></div></div>
<?php	}
		if(@$insidedelivery==TRUE){ ?>
			<div class="billformrow"><div class="cobhl cobhl<?php print $costage?> cdshipftflag"><input type="checkbox" name="insidedelivery" value="Y" <?php
			if(($ordComLoc & 16)==16) print 'checked="checked" ';
			'onchange="setchangeflag(this.checked,4)" '?>/></div>
			<div class="cobll cobll<?php print $costage?> cdshipflag"><?php print $GLOBALS['xxInsDel']?></div></div>
<?php	}
		if(@$holdatlocation==TRUE){ ?>
			<div class="billformrow"><div class="cobhl cobhl<?php print $costage?> cdshipftflag"><input type="checkbox" name="holdatlocation" value="Y" /></div>
			<div class="cobll cobll<?php print $costage?> cdshipflag">Please click here to Hold at Location</div></div>
<?php	}
		if(@$homedelivery==TRUE){ ?>
			<div class="billformrow"><div class="cobhl cobhl<?php print $costage?> cdshipftflag">Delivery Options:</div>
			<div class="cobll cobll<?php print $costage?> cdshipflag"><select name="homedelivery" size="1">
			<option value="">Standard Delivery</option>
			<option value="EVENING">Evening Home Delivery</option>
			<option value="DATE_CERTAIN">Date Certain Home Delivery</option>
			<option value="APPOINTMENT">Appointment Home Delivery</option>
			</select></div></div>
<?php	}
	}
	if(@$GLOBALS['termsandconditions']&&$costage==3&&($ordPayProvider=='19'||$ordPayProvider=='21')){ ?>
		<div class="billformrow">
		  <div class="cobhl cobhl2 cdshiptterms"><input type="checkbox" name="termsandconds" id="termsandconds" value="1" onclick="document.getElementById('sftermsandconds').value=this.checked?1:''" <?php print (getpost('sftermsandconds')=='1'?'checked="checked" ':'')?>/></div>
		  <div class="cobll cobll2 cdshipterms"><?php print $GLOBALS['xxTermsCo']?></div>
		</div>
<?php
	}
	if($hasshipflag) print '</div>';
}
$amzrefid_=getrequest('amzrefid');
if($amzrefid_!=''){ // Amazon Payment
	if(getpayprovdetails(21,$data1,$data2,$data3,$demomode,$ppmethod)){
		$ordPayProvider='21';
		$data2arr=explode('&',$data2);
		$data2=$data2arr[0];
		$sellerid=@$data2arr[1];
		$amazonstr='';
		$amazonprms='';
		$ordCountry=$ordPhone=$ordEmail=$ordName=$ordLastName='';
		$countryid=$ordComLoc=0;
		$checkoutmode='go';
		$amazonpayment=TRUE;

		$timestamp=gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
		$scripturl='mws-eu.amazonservices.com';
		if($GLOBALS['origCountryCode']=='US') $scripturl='mws.amazonservices.com';
		if($GLOBALS['origCountryCode']=='JP') $scripturl='mws.amazonservices.jp';
		$endpointpath='/OffAmazonPayments' . ($demomode?'_Sandbox':'') . '/2013-01-01';
		$endpoint='https://' . $scripturl . $endpointpath;

		amazonparam2('AWSAccessKeyId',$data2);
		amazonparam2('Action','GetOrderReferenceDetails');
		//amazonparam2('AddressConsentToken',ZZZ);
		amazonparam2('AmazonOrderReferenceId',$amzrefid_);
		amazonparam2('SellerId',$sellerid);
		amazonparam2('SignatureMethod','HmacSHA256');
		amazonparam2('SignatureVersion',2);
		amazonparam2('Timestamp',$timestamp);
		amazonparam2('Version','2013-01-01');
	// &OrderReferenceAttributes.PlatformId=PLATFORM_ID_HERE&OrderReferenceAttributes.SellerNote=Lorem%20ipsum&OrderReferenceAttributes.SellerOrderAttributes.StoreName=YOUR_STORE_NAME 
		calculateSignatureAndParametersToString();
		$amazonprms['Signature']=base64_encode(hash_hmac('sha256', calculateStringToSignV2($amazonprms), $data3, true));
		$amazonstr=getParametersAsString();
		
		if(callcurlfunction($endpoint,$amazonstr,$res,'',$errormsg,FALSE)){
			// print str_replace("<","&lt;",$res);
			$xmlDoc=new vrXMLDoc($res);
			$nodeList=$xmlDoc->nodeList->childNodes[0];
			for($i=0; $i < $nodeList->length; $i++){
				if($nodeList->nodeName[$i]=='Error'){
					$amazonpayment=$checkoutmode='';
					$e=$nodeList->childNodes[$i];
					for($j=0; $j < $e->length; $j++){
						if($e->nodeName[$j]=='Message'){
							$carterror='Amazon Error: ' . $e->nodeValue[$j];
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
												if($h->nodeName[$m]=='ConstraintID'){
													if($h->nodeValue[$m]=='AmountNotSet'){
														$isamountconstraint=TRUE;
													}
												}elseif($h->nodeName[$m]=='Description'){
													$thisconstraint=$h->nodeValue[$m];
												}
											}
											if(!$isamountconstraint){
												$amazonpayment=$checkoutmode='';
												$carterror=$thisconstraint;
											}
										}
									}
								}elseif($f->nodeName[$k]=='Destination'){
									$g=$f->childNodes[$k];
									for($l=0; $l < $g->length; $l++){
										if($g->nodeName[$l]=='PhysicalDestination'){
											$h=$g->childNodes[$l];
											for($m=0; $m < $h->length; $m++){
												if($h->nodeName[$m]=='StateOrRegion'){
													$ordState=$h->nodeValue[$m];
												}elseif($h->nodeName[$m]=='City'){
													$ordCity=$h->nodeValue[$m];
												}elseif($h->nodeName[$m]=='CountryCode'){
													$tmpcntry=str_replace("'",'',$h->nodeValue[$m]);
													$sSQL='SELECT countryName,countryID,countryOrder FROM countries WHERE countryEnabled=1 AND ';
													if($tmpcntry=='GB')
														$sSQL.='countryID=201';
													elseif($tmpcntry=='FR')
														$sSQL.='countryID=65';
													elseif($tmpcntry=='PT')
														$sSQL.='countryID=153';
													elseif($tmpcntry=='ES')
														$sSQL.='countryID=175';
													else
														$sSQL.="countryCode='" . escape_string($tmpcntry) . "'";
													$result=ect_query($sSQL) or ect_error();
													if($rs=ect_fetch_assoc($result)){
														$ordCountry=$rs['countryName'];
														$countryid=$rs['countryID'];
														$homecountry=($countryid==$origCountryID);
													}else{
														$errormsg='Purchasing from your country is not supported.';
														$success=FALSE;
													}
													ect_free_result($result);
												}elseif($h->nodeName[$m]=='PostalCode'){
													$ordZip=$h->nodeValue[$m];
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
			if($success){
				$ordEmail='XXX';
				$ordName='XXX';
				$ordAddress='XXX';
				$ordCity='XXX';
				$ordPhone='XXX';
			}
		}else
			print "curl failed<br>";
	}
}elseif(getget('token')!='' && $checkoutmode!='paypalcancel'){ // PayPal Express
	if(getpayprovdetails(19,$username,$password,$data3,$demomode,$ppmethod)){
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
	$sXML=ppsoapheader($username, $password, $signature) .
		'<soap:Body><GetExpressCheckoutDetailsReq xmlns="urn:ebay:api:PayPalAPI"><GetExpressCheckoutDetailsRequest><Version xmlns="urn:ebay:apis:eBLBaseComponents">60.00</Version>' .
		'  ' . addtag('Token',getget('token')) . 
		'</GetExpressCheckoutDetailsRequest></GetExpressCheckoutDetailsReq></soap:Body></soap:Envelope>';
	if($demomode) $sandbox=".sandbox"; else $sandbox='';
	if(callcurlfunction('https://api' . ($isthreetoken ? '-3t' : '') . $sandbox . '.paypal.com/2.0/', $sXML, $res, $sslcertpath, $errormsg, 25)){
		$xmlDoc=new vrXMLDoc($res);
		$nodeList=$xmlDoc->nodeList->childNodes[0];
		$success=FALSE;
		$ordPhone=$ordEmail=$ordName=$ordLastName='';
		$countryid=0;
		$ordPayProvider='19';
		$ordComLoc=0;
		$gotaddress=FALSE;
		$token=getget('token');
		if(abs(@$addshippinginsurance)==1) $ordComLoc+=2;
		for($i=0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=='SOAP-ENV:Body'){
				$e=$nodeList->childNodes[$i];
				for($j=0; $j < $e->length; $j++){
					if($e->nodeName[$j]=='GetExpressCheckoutDetailsResponse'){
						$ee=$e->childNodes[$j];
						for($jj=0; $jj < $ee->length; $jj++){
							if($ee->nodeName[$jj]=='Ack'){
								if($ee->nodeValue[$jj]=='Success' || $ee->nodeValue[$jj]=='SuccessWithWarning')
									$success=TRUE;
							}elseif($ee->nodeName[$jj]=='GetExpressCheckoutDetailsResponseDetails'){
								$ff=$ee->childNodes[$jj];
								for($kk=0; $kk < $ff->length; $kk++){
									if($ff->nodeName[$kk]=='PayerInfo'){
										$gg=$ff->childNodes[$kk];
										for($ll=0; $ll < $gg->length; $ll++){
											if($gg->nodeName[$ll]=='Payer'){
												$ordEmail=$gg->nodeValue[$ll];
											}elseif($gg->nodeName[$ll]=='PayerID'){
												$payerid=$gg->nodeValue[$ll];
											}elseif($gg->nodeName[$ll]=='PayerStatus'){
												$ordCVV='U';
												$payer_status=strtolower($gg->nodeValue[$ll]);
												if($payer_status=='verified') $ordCVV='Y';
												elseif($payer_status=='unverified') $ordCVV='N';
											}elseif($gg->nodeName[$ll]=='PayerName'){
											}elseif($gg->nodeName[$ll]=='Address'){
												$hh=$gg->childNodes[$ll];
												for($mm=0; $mm < $hh->length; $mm++){
													if($hh->nodeName[$mm]=='Name'){
														splitfirstlastname(trim($hh->nodeValue[$mm]),$ordName,$ordLastName);
													}elseif($hh->nodeName[$mm]=='Street1'){
														$ordAddress=$hh->nodeValue[$mm];
													}elseif($hh->nodeName[$mm]=='Street2'){
														$ordAddress2=$hh->nodeValue[$mm];
													}elseif($hh->nodeName[$mm]=='CityName'){
														$ordCity=$hh->nodeValue[$mm];
													}elseif($hh->nodeName[$mm]=='StateOrProvince'){
														$ordState=$hh->nodeValue[$mm];
													}elseif($hh->nodeName[$mm]=='Country'){
														$tmpcntry=str_replace("'",'',$hh->nodeValue[$mm]);
														$sSQL='SELECT countryName,countryID,countryOrder FROM countries WHERE countryEnabled=1 AND ';
														if($tmpcntry=='GB')
															$sSQL.='countryID=201';
														elseif($tmpcntry=='FR')
															$sSQL.='countryID=65';
														elseif($tmpcntry=='PT')
															$sSQL.='countryID=153';
														elseif($tmpcntry=='ES')
															$sSQL.='countryID=175';
														else
															$sSQL.="countryCode='" . escape_string($tmpcntry) . "'";
														$result=ect_query($sSQL) or ect_error();
														if($rs=ect_fetch_assoc($result)){
															$ordCountry=$rs['countryName'];
															$countryid=$rs['countryID'];
															$homecountry=($countryid==$origCountryID);
														}else{
															$errormsg='Purchasing from your country is not supported.';
															$success=FALSE;
														}
														ect_free_result($result);
													}elseif($hh->nodeName[$mm]=='PostalCode'){
														$ordZip=$hh->nodeValue[$mm];
													}elseif($hh->nodeName[$mm]=='AddressStatus'){
														$ordAVS='U';
														$address_status=strtolower($hh->nodeValue[$mm]);
														$gotaddress=($address_status!='none');
														if($address_status=='confirmed') $ordAVS='Y';
														elseif($address_status=='unconfirmed') $ordAVS='N';
													}
												}
											}
										}
									}elseif($ff->nodeName[$kk]=='Custom'){
										$customarr=explode(':', $ff->nodeValue[$kk]);
										$thesessionid=@$customarr[0];
										$ordAffiliate=@$customarr[1];
										if(substr($thesessionid,0,3)=='cid'){
											$_SESSION['clientID']=str_replace("'",'',substr($thesessionid,3));
											$sSQL="SELECT clID,clUserName,clActions,clLoginLevel,clPercentDiscount FROM customerlogin WHERE clID='" . escape_string($_SESSION['clientID']) ."'";
											$result=ect_query($sSQL) or ect_error();
											if($rs=ect_fetch_assoc($result)){
												$_SESSION['clientUser']=$rs['clUserName'];
												$_SESSION['clientActions']=$rs['clActions'];
												$_SESSION['clientLoginLevel']=$rs['clLoginLevel'];
												$_SESSION['clientPercentDiscount']=(100.0-(double)$rs['clPercentDiscount'])/100.0;
											}
										}else
											$thesessionid=str_replace("'",'',substr($thesessionid,3));
									}elseif($ff->nodeName[$kk]=='ContactPhone'){
										$ordPhone=$ff->nodeValue[$kk];
									}
								}
							}elseif($ee->nodeName[$jj]=="Errors"){
								$ff=$ee->childNodes[$jj];
								for($kk=0; $kk < $ff->length; $kk++){
									if($ff->nodeName[$kk]=="ShortMessage"){
										$errormsg=$ff->nodeValue[$kk].'<br>'.$errormsg;
									}elseif($ff->nodeName[$kk]=="LongMessage"){
										$errormsg.=$ff->nodeValue[$kk];
									}elseif($ff->nodeName[$kk]=="ErrorCode"){
										$errcode=$ff->nodeValue[$kk];
									}
								}
							}
						}
					}
				}
			}
		}
		if(! $gotaddress){
			if(ob_get_length()!==FALSE)
				header('Location: ' . $storeurl . 'cart.php');
			else
				print '<meta http-equiv="Refresh" content="0; URL=' . $storeurl . 'cart.php">';
			$cartisincluded=TRUE;
		}elseif($success){
			$paypalexpress=TRUE;
			if(($countryid==1 || $countryid==2) && $homecountry && @$usestateabbrev!=TRUE){
				$sSQL="SELECT stateName FROM states WHERE (stateCountryID=1 OR stateCountryID=2) AND stateAbbrev='" . escape_string($ordState) . "'";
				$result=ect_query($sSQL) or ect_error();
				if($rs=ect_fetch_assoc($result))
					$ordState=$rs['stateName'];
				ect_free_result($result);
			}
		}else{
			$carterror='PayPal Express Error: '.$errormsg;
			$checkoutmode='paypalcancel';
		}
	}else{
		$carterror='PayPal Express Error: '.$errormsg;
		$checkoutmode='paypalcancel';
	}
}elseif($checkoutmode=='paypalexpress1'||$checkoutmode=='billmelater'){ // PayPal Express
	if(getpayprovdetails(19,$username,$password,$data3,$demomode,$ppmethod)){
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
	if($demomode) $sandbox='.sandbox'; else $sandbox='';
	if(@$pathtossl!=''){
		if(substr($pathtossl,-1)!='/') $storeurl=$pathtossl . '/'; else $storeurl=$pathtossl;
	}
	$theestimate=round((double)getpost('estimate'),2);
	$sXML=ppsoapheader($username, $password, $signature) .
		'<soap:Body><SetExpressCheckoutReq xmlns="urn:ebay:api:PayPalAPI"><SetExpressCheckoutRequest><Version xmlns="urn:ebay:apis:eBLBaseComponents">72.00</Version>' .
		'<SetExpressCheckoutRequestDetails xmlns="urn:ebay:apis:eBLBaseComponents">' .
		($checkoutmode=='billmelater'?'<FundingSourceDetails><UserSelectedFundingSource>BML</UserSelectedFundingSource></FundingSourceDetails>':'') .
		'<OrderTotal currencyID="' . $countryCurrency . '">' . $theestimate . '</OrderTotal>' .
		'<ReturnURL>' . $storeurl . 'cart.php</ReturnURL><CancelURL>' . $storeurl . 'cart.php?action=paypalcancel</CancelURL>' .
		'<Custom>' . (@$_SESSION['clientID']!='' ? 'cid' . $_SESSION['clientID'] : 'sid' . $thesessionid) . ':' . strip_tags(getpost('PARTNER')) . '</Custom>' .
		addtag('PaymentAction',$ppmethod==1?'Authorization':'Sale');
	$itemtotal=0;
	$sXML.='<PaymentDetails>';
	$sSQL="SELECT cartID,cartProdID,cartProdName,cartProdPrice,cartQuantity,pShipping,pShipping2,pExemptions,pTax,pDescription FROM cart LEFT JOIN products ON cart.cartProdID=products.pId WHERE cartCompleted=0 AND " . getsessionsql();
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		$itemtotal+=$rs['cartProdPrice']*$rs['cartQuantity'];
		$description=''; $addcomma='';
		$optiontotal=0;
		$sSQL="SELECT coOptGroup,coCartOption,coPriceDiff FROM cartoptions WHERE coCartID=" . $rs['cartID'];
		$result2=ect_query($sSQL) or ect_error();
		while($rs2=ect_fetch_assoc($result2)){
			$optiontotal+=$rs2['coPriceDiff'];
			$description.=$addcomma . vrxmlencode(strip_tags($rs2['coOptGroup'])) . ' : ' . vrxmlencode(strip_tags($rs2['coCartOption']));
			$addcomma=', ';
		}
		ect_free_result($result2);
		$itemtotal+=$optiontotal*$rs['cartQuantity'];
		$sXML.='<PaymentDetailsItem><Number>' . vrxmlencode(strip_tags($rs['cartProdID'])) . '</Number><Name>' . vrxmlencode(strip_tags($rs['cartProdName'])) . '</Name><Description>' . substr($description,0,122) . (strlen($description)>122?'...':'') . '</Description><Amount currencyID="' . $countryCurrency . '">' . ($rs['cartProdPrice']+$optiontotal) . '</Amount><Quantity>' . $rs['cartQuantity'] . '</Quantity></PaymentDetailsItem>';
	}
	ect_free_result($result);
	if($itemtotal!=$theestimate){
		$sXML.='<PaymentDetailsItem><Name>' . vrxmlencode($GLOBALS['xxPPEst1']) . '</Name><Description>' . vrxmlencode($GLOBALS['xxPPEst2']) . '</Description><Amount currencyID="' . $countryCurrency . '">' . round($theestimate-$itemtotal, 2) . '</Amount><Quantity>1</Quantity></PaymentDetailsItem>';
	}
	$sXML.='</PaymentDetails>';
	if(@$paypallc!='') $sXML.=addtag('LocaleCode',$paypallc);
	$sXML.='  </SetExpressCheckoutRequestDetails>' .
		'</SetExpressCheckoutRequest></SetExpressCheckoutReq></soap:Body></soap:Envelope>';
	if($username==''){
		print '<meta http-equiv="Refresh" content="0; URL=https://www.paypal.com/us/webapps/mpp/referral/paypal-payments-pro?partner_id=39HT54MCDMV8E">';
		print '<p align="center">' . $GLOBALS['xxAutFo'] . '</p>';
		print '<p align="center">' . $GLOBALS['xxForAut'] . ' <a class="ectlink" href="https://www.paypal.com/us/webapps/mpp/referral/paypal-payments-pro?partner_id=39HT54MCDMV8E">' . $GLOBALS['xxClkHere'] . '</a></p>';
	}elseif(callcurlfunction('https://api' . ($isthreetoken ? '-3t' : '') . $sandbox . '.paypal.com/2.0/', $sXML, $res, $sslcertpath, $errormsg, 25)){
		$xmlDoc=new vrXMLDoc($res);
		$nodeList=$xmlDoc->nodeList->childNodes[0];
		$success=FALSE;
		$token='';
		for($i=0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=="SOAP-ENV:Body"){
				$e=$nodeList->childNodes[$i];
				for($j=0; $j < $e->length; $j++){
					if($e->nodeName[$j]=="SetExpressCheckoutResponse"){
						$ee=$e->childNodes[$j];
						for($jj=0; $jj < $ee->length; $jj++){
							if($ee->nodeName[$jj]=='Ack'){
								if($ee->nodeValue[$jj]=='Success' || $ee->nodeValue[$jj]=='SuccessWithWarning')
									$success=TRUE;
							}elseif($ee->nodeName[$jj]=="Token"){
								$token=$ee->nodeValue[$jj];
							}elseif($ee->nodeName[$jj]=="Errors"){
								$ff=$ee->childNodes[$jj];
								for($kk=0; $kk < $ff->length; $kk++){
									if($ff->nodeName[$kk]=="ShortMessage"){
										$errormsg=$ff->nodeValue[$kk].'<br />'.$errormsg;
									}elseif($ff->nodeName[$kk]=="LongMessage"){
										$errormsg.=$ff->nodeValue[$kk];
									}elseif($ff->nodeName[$kk]=="ErrorCode"){
										$errcode=$ff->nodeValue[$kk];
									}
								}
							}
						}
					}
				}
			}
		}
		if($success){
			if(ob_get_length()===FALSE){
				print '<meta http-equiv="Refresh" content="0; URL=https://www'.$sandbox.'.paypal.com/webscr?cmd=_express-checkout&token=' . $token . '">';
			}else
				header('Location: https://www'.$sandbox.'.paypal.com/webscr?cmd=_express-checkout&token=' . $token);
			print '<p align="center">' . $GLOBALS['xxAutFo'] . '</p>';
			print '<p align="center">' . $GLOBALS['xxForAut'] . ' <a class="ectlink" href="https://www'.$sandbox.'.paypal.com/webscr?cmd=_express-checkout&token=' . $token . '">' . $GLOBALS['xxClkHere'] . '</a></p>';
		}else{
			print "PayPal Payment Pro error: " . $errormsg;
		}
	}else{
		print "PayPal Payment Pro error: " . $errormsg;
	}
}elseif($checkoutmode=='update' || $checkoutmode=='savecart' || $checkoutmode=='movetocart'){
	$_SESSION['xsshipping']=NULL; unset($_SESSION['xsshipping']);
	unset($_SESSION['tofreeshipquant']);
	unset($_SESSION['tofreeshipamount']);
	$_SESSION['discounts']=NULL; unset($_SESSION['discounts']);
	$_SESSION['xscountrytax']=NULL; unset($_SESSION['xscountrytax']);
	$sSQL="SELECT ordID FROM orders WHERE ordStatus>1 AND ordAuthNumber='' AND " . getordersessionsql();
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		release_stock($rs['ordID']);
		ect_query("UPDATE cart SET cartSessionID='".escape_string(getsessionid())."',cartClientID='" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "' WHERE cartCompleted=0 AND cartOrderID=" . $rs['ordID']) or ect_error();
		ect_query("UPDATE orders SET ordAuthStatus='MODWARNOPEN',ordShipType='MODWARNOPEN' WHERE ordID=" . $rs['ordID']) or ect_error();
	}
	ect_free_result($result);
	$listid='';
	if($checkoutmode=='savecart' && getpost('listid')!='' && is_numeric(getpost('listid')) && @$_SESSION['clientID']!=''){
		$result=ect_query("SELECT listID FROM customerlists WHERE listID='".escape_string(getpost('listid'))."' AND listOwner='" . escape_string($_SESSION['clientID']) . "'") or ect_error();
		if($rs=ect_fetch_assoc($result)) $listid=$rs['listID'];
		ect_free_result($result);
	}
	foreach(@$_POST as $objItem => $objValue){
		if(substr($objItem,0,5)=='quant' || substr($objItem,0,5)=='delet'){
			$thecartid=(int)substr($objItem, 5);
			$pPrice=0;
			$pID='';
			$sSQL='SELECT cartProdID,'.$WSP."pPrice FROM cart INNER JOIN products ON cart.cartProdId=products.pID WHERE cartID='" . $thecartid . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				$pID=trim($rs['cartProdID']);
				$pPrice=$rs['pPrice'];
			}
			ect_free_result($result);
			if($checkoutmode=='movetocart'){
				if(substr($objItem,0,5)=='delet'){
					$sSQL="UPDATE cart SET cartCompleted=0,cartListID=0,cartDateAdded='" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "' WHERE cartCompleted=3 AND cartID='".escape_string($thecartid)."' AND " . getsessionsql();
					if(getget('pli')!='' && is_numeric(getget('pli')) && getget('pla')!=''){
						$sSQL="SELECT listID FROM customerlists WHERE listID='".escape_string(getget('pli'))."' AND listAccess='".escape_string(getget('pla'))."'";
						$result=ect_query($sSQL) or ect_error();
						if($rs=ect_fetch_assoc($result)) $sSQL="UPDATE cart SET cartCompleted=0,cartDateAdded='" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "',cartSessionID='".escape_string(getsessionid())."',cartClientID='" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "' WHERE cartCompleted=3 AND cartID='".escape_string($thecartid)."' AND cartListID=" . $rs['listID']; else $sSQL='';
						ect_free_result($result);
					}
					if($sSQL!='') ect_query($sSQL) or ect_error();
				}
			}elseif($checkoutmode=='savecart' && $pID!=@$giftwrappingid){
				if(substr($objItem,0,5)=='delet')
					ect_query("UPDATE cart SET cartOrderID=0,cartCompleted=3,cartListID='".escape_string($listid!=''?$listid:'0')."',cartDateAdded='" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "' WHERE (cartCompleted=0 OR cartCompleted=3) AND cartID='".escape_string($thecartid)."' AND " . getsessionsql());
			}else{
				if(substr($objItem,0,5)=='quant'){
					if((int)$objValue==0){
						checkdeletecart($thecartid);
					}else{
						$thequant=abs((int)$objValue);
						if($thequant>99999)$thequant=99999;
						if($pID!='' && $pID!=@$giftwrappingid){
							ect_query('UPDATE cart SET cartQuantity=' . $thequant . ",cartDateAdded='" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "' WHERE cartQuantity<>".abs((int)$objValue)." AND (cartCompleted=0 OR cartCompleted=3) AND cartID='" . $thecartid . "'") or ect_error();
						}
					}
				}elseif(substr($objItem,0,5)=='delet'){
					checkdeletecart(substr($objItem, 5));
				}
			}
			if($pID!=$giftcertificateid && $pID!=$donationid) checkpricebreaks($pID,$pPrice);
		}
	}
	updategiftwrap();
}
function additemtocart($ainame,$aiprice){
	global $thesessionid,$theid,$origid,$dateadjust,$quantity;
	$cartListID=0;
	$cartCompleted=0;
	if(getpost('listid')==='0' && @$_SESSION['clientID']!=''){
		$cartCompleted=3;
	}elseif(getpost('listid')!='' && is_numeric(getpost('listid')) && @$_SESSION['clientID']!=''){
		$sSQL="SELECT listID FROM customerlists WHERE listOwner='".escape_string($_SESSION['clientID'])."' AND listID='".escape_string(getpost('listid'))."'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){ $cartListID=$rs['listID']; $cartCompleted=3; }
		ect_free_result($result);
	}
	$sSQL="SELECT COUNT(*) AS cartcnt FROM cart WHERE (cartCompleted=0 OR cartCompleted=3) AND " . getsessionsql();
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result))$cartfloodcontrol=$rs['cartcnt']>1000;
	ect_free_result($result);
	if($cartfloodcontrol)
		return(0);
	else{
		$sSQL='INSERT INTO cart (cartSessionID,cartClientID,cartProdID,cartOrigProdID,cartQuantity,cartCompleted,cartProdName,cartProdPrice,cartOrderID,cartDateAdded,cartListID) VALUES (' .
		"'" . escape_string($thesessionid) . "','" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "','" . escape_string($theid) . "','" . ($theid!=$origid?escape_string($origid):'') . "'," . $quantity . ",".$cartCompleted.",'" . escape_string(strip_tags($ainame)) . "','" . round($aiprice,2) . "',0,'" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "',".$cartListID.")";
		ect_query($sSQL) or ect_error();
                
                //insert statement for location of product and putting it on home page
					//if checkbox is checked
				


	
	}
	return(ect_insert_id());
}
function addoption($opttoadd){
	global $addalternateoptions,$theid,$origid,$cartID,$OWSP,$totalquantity,$thepname,$thepprice,$thepweight,$txtcollen;
	$optvalue=trim(@$_POST[$opttoadd]);
	if(! is_numeric($optvalue)) $optvalue='';
	if((substr($opttoadd,0,4)=='optn' || substr($opttoadd,0,4)=='optm') && $optvalue!=''){
		if(substr($opttoadd,0,4)=='optm'){
			$optID=substr($opttoadd, 4);
			$quantity=$optvalue;
			if(is_numeric($optID) && is_numeric($quantity)){
				if($quantity>0){
					$totalquantity+=$quantity;
					if($theid==$origid || @$addalternateoptions){
						$sSQL="SELECT optID,".getlangid('optGrpName',16).','.getlangid('optName',32).',' . $OWSP . "optPriceDiff,optWeightDiff,optType,optFlags,optRegExp FROM options LEFT JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optID='" . escape_string($optID) . "'";
						$result=ect_query($sSQL) or ect_error();
						if($rs=ect_fetch_assoc($result)){
							$sSQL='INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff) VALUES (' . $cartID . ',' . $rs['optID'] . ",'" . escape_string($rs[getlangid('optGrpName',16)]) . "','" . escape_string($rs[getlangid('optName',32)]) . "',";
							if(($rs['optFlags']&1)==0) $sSQL.=(trim($rs['optRegExp'])!=''?0:$rs['optPriceDiff']) . ','; else $sSQL.=round(($rs['optPriceDiff'] * $thepprice)/100.0, 2) . ',';
							if(($rs['optFlags']&2)==0) $sSQL.=$rs['optWeightDiff'] . ')'; else $sSQL.=multShipWeight($thepweight,$rs['optWeightDiff']) . ')';
							ect_query($sSQL) or ect_error();
						}
						ect_free_result($result);
					}
					checkpricebreaks($theid,$thepprice);
				}
			}
		}elseif(trim(@$_POST['v' . $opttoadd])==''){
			$sSQL='SELECT optID,'.getlangid('optGrpName',16).','.getlangid('optName',32).',' . $OWSP . "optPriceDiff,optWeightDiff,optType,optFlags,optRegExp FROM options LEFT JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optID='" . escape_string($optvalue) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				if(@$addalternateoptions!=TRUE && trim($rs['optRegExp'])!='' && substr($rs['optRegExp'], 0, 1)!='!'){
					// Do nothing
				}elseif(abs($rs['optType'])!=3){
					$sSQL='INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff) VALUES (' . $cartID . ',' . $rs['optID'] . ",'" . escape_string($rs[getlangid('optGrpName',16)]) . "','" . escape_string($rs[getlangid('optName',32)]) . "',";
					if(($rs['optFlags']&1)==0) $sSQL.=(@$addalternateoptions==TRUE && trim($rs['optRegExp']) ? 0 : $rs['optPriceDiff']) . ','; else $sSQL.=round(($rs['optPriceDiff'] * $thepprice)/100.0, 2) . ',';
					if(($rs['optFlags']&2)==0) $sSQL.=$rs['optWeightDiff'] . ')'; else $sSQL.=multShipWeight($thepweight,$rs['optWeightDiff']) . ')';
				}else
					$sSQL='INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff) VALUES (' . $cartID . ',' . $rs['optID'] . ",'" . escape_string($rs[getlangid('optGrpName',16)]) . "','',0,0)";
				ect_query($sSQL) or ect_error();
			}
			ect_free_result($result);
		}else{
			$sSQL='SELECT optID,'.getlangid('optGrpName',16).','.getlangid('optName',32).",optTxtCharge,optMultiply,optAcceptChars FROM options LEFT JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optID='" . escape_string($optvalue) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				$theopttoadd=getpost('v' . $opttoadd);
				$optPriceDiff=($rs['optTxtCharge']<0&&$theopttoadd!=''?abs($rs['optTxtCharge']):$rs['optTxtCharge']*strlen($theopttoadd));
				$optmultiply=0;
				if($rs['optMultiply']!=0){
					if(is_numeric($theopttoadd)) $optmultiply=(double)$theopttoadd; else $theopttoadd='#NAN';
				}
				$sSQL='INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff,coMultiply) VALUES (' . $cartID . ',' . $rs['optID'] . ",'" . escape_string($rs[getlangid('optGrpName',16)]) . "','" . escape_string(substr($theopttoadd,0,$txtcollen)) . "',".$optPriceDiff.',0,' . $rs['optMultiply'] . ')';
				ect_query($sSQL) or ect_error();
			}
			ect_free_result($result);
		}
	}
}
function addproduct($theid){
	global $thepname,$thepprice,$thepweight,$cartID,$objItem,$optarray,$numoptions,$WSP,$addedprods,$numaddedprods,$quantity;
	$idexists=1;
	$sSQL='SELECT '.getlangid('pName',1).','.$WSP.'pPrice,pWeight FROM products WHERE '.(!$GLOBALS['useStockManagement']?'(pSell<>0 OR pBackOrder<>0) AND':'')." pID='" . escape_string($theid) . "'";
	$result2=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result2)){
		$thepname=$rs[getlangid('pName',1)];
		$thepprice=round($rs['pPrice'],2);
		$thepweight=$rs['pWeight'];
	}else{
		$idexists=0;
		$thepname="Product ID Error";
		$thepprice=0;
		$thepweight=0;
	}
	ect_free_result($result2);
	$addedprods[$numaddedprods][0]=$theid;
	$addedprods[$numaddedprods][1]=$thepname;
	$addedprods[$numaddedprods][2]=$quantity;
	$addedprods[$numaddedprods][3]=$thepprice;
	$addedprods[$numaddedprods][4]=$idexists;
	$addedprods[$numaddedprods][5]='';
	if($idexists){
		$cartID=additemtocart($thepname,$thepprice);
		if($cartID==0) return;
		for($index=0; $index<$numoptions; $index++){
			if($optarray[$index]=='multioption') addoption($objItem); else addoption($optarray[$index]);
		}
		$addedprods[$numaddedprods][5]=$cartID;
	}
	$numaddedprods++;
}
if($checkoutmode=='add'){
	$origid=$theid;
	$optarray='';
	$addedprods='';
	$errid='';
	$thesessionid=getsessionid();
	if(@$_SESSION['clientID']!='' && getpost('listid')!='' && is_numeric(getpost('listid'))) $listid=getpost('listid'); else $listid='';
	$_SESSION['xsshipping']=NULL; unset($_SESSION['xsshipping']);
	unset($_SESSION['tofreeshipquant']);
	unset($_SESSION['tofreeshipamount']);
	$_SESSION['discounts']=NULL; unset($_SESSION['discounts']);
	$_SESSION['xscountrytax']=NULL; unset($_SESSION['xscountrytax']);
	$sSQL="SELECT ordID FROM orders WHERE ordStatus>1 AND ordAuthNumber='' AND " . getordersessionsql();
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		release_stock($rs['ordID']);
		ect_query("UPDATE cart SET cartSessionID='".escape_string(getsessionid())."',cartClientID='" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "' WHERE cartCompleted=0 AND cartOrderID=" . $rs['ordID']) or ect_error();
		ect_query("UPDATE orders SET ordAuthStatus='MODWARNOPEN',ordShipType='MODWARNOPEN' WHERE ordID=" . $rs['ordID']) or ect_error();
	}
	ect_free_result($result);
	if(! is_numeric(getpost('quant'))) $quantity=1; else $quantity=abs((int)getpost('quant'));
	
	if($quantity<1)$quantity=1;
	if($quantity>99999)$quantity=99999;
	$hasmultioption=FALSE;
	$origquantity=$quantity;
	$altids='';
	$numoptions=0;
	$numaddedprods=0;
	foreach(@$_POST as $objItem => $objValue){ // Check if the product id is modified
		if(substr($objItem,0,4)=='optn' && is_numeric($objValue)){
			$doaddoption=FALSE;
			$sSQL="SELECT optRegExp FROM options WHERE optID='" . escape_string($objValue) . "'";
			$result2=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result2)) $theexp=trim($rs['optRegExp']); else $theexp='';
			if($theexp!='' && substr($theexp, 0, 1)!='!'){
				$theexp=str_replace('%s', $theid, $theexp);
				$altids.=':'.$objValue.':';
				if(strpos($theexp,' ')!==FALSE){ // Search and replace
					$exparr=explode(' ', $theexp, 2);
					$theid=str_replace($exparr[0], $exparr[1], $theid);
				}else
					$theid=$theexp;
				if(@$addalternateoptions==TRUE) $doaddoption=TRUE;
			}else
				$doaddoption=TRUE;
			if($doaddoption){
				$optarray[$numoptions]=$objItem;
				$numoptions++;
			}
			ect_free_result($result2);
		}elseif(substr($objItem,0,4)=='optm' && is_numeric($objValue)){
			if(! $hasmultioption){
				$optarray[$numoptions]='multioption';
				$numoptions++;
			}
			$hasmultioption=TRUE;
		}
	}
	if($hasmultioption){
		foreach(@$_POST as $objItem => $objValue){
			if(substr($objItem,0,4)=='optm' && is_numeric($objValue)){
				$quantity=abs((int)$objValue);
				if($quantity>99999)$quantity=99999;
				$theid=$origid;
				$sSQL="SELECT optRegExp FROM options WHERE optID='" . escape_string(substr($objItem, 4)) . "'";
				$result2=ect_query($sSQL) or ect_error();
				if($rs=ect_fetch_assoc($result2)) $theexp=trim($rs['optRegExp']); else $theexp='';
				if($theexp!='' && substr($theexp, 0, 1)!='!'){
					$theexp=str_replace('%s', $theid, $theexp);
					if(strpos($theexp, ' ') !== FALSE){ // Search and replace
						$exparr=explode(' ', $theexp, 2);
						$theid=str_replace($exparr[0], $exparr[1], $theid);
					}else
						$theid=$theexp;
				}
				ect_free_result($result2);
				addproduct($theid);
			}
		}
	}else
		addproduct($theid);
	// Check duplicates
	$sSQL="SELECT cartID,cartProdID,cartQuantity FROM cart WHERE cartCompleted=".($listid==''?0:3)." AND " . getsessionsql() . ($listid==''?'':" AND cartListID='".$listid."'") . ' ORDER BY cartID';
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		$hasoptions=FALSE;
		$thecartid='';
		$sSQL="SELECT cartID,cartQuantity FROM cart WHERE cartID>" . $rs['cartID'] . " AND cartCompleted=0 AND " . getsessionsql() . ($listid==''?'':" AND cartListID='".$listid."'") . " AND cartProdID='" . escape_string($rs['cartProdID']) . "'";
		$result2=ect_query($sSQL) or ect_error();
		while($rs2=ect_fetch_assoc($result2)){
			$thecartid=$rs2['cartID'];
			$thequant=$rs2['cartQuantity'];
			$hasoptions=TRUE;
			if($thecartid!=''){ // check options
				$optarr1cnt=0; $optarr2cnt=0;
				$sSQL="SELECT coOptID,coCartOption FROM cartoptions WHERE coCartID=" . $rs['cartID'];
				$result3=ect_query($sSQL) or ect_error();
				while($rs3=ect_fetch_assoc($result3))
					$optarr1[$optarr1cnt++]=$rs3;
				ect_free_result($result3);
				$sSQL="SELECT coOptID,coCartOption FROM cartoptions WHERE coCartID=" . $thecartid;
				$result3=ect_query($sSQL) or ect_error();
				while($rs3=ect_fetch_assoc($result3))
					$optarr2[$optarr2cnt++]=$rs3;
				ect_free_result($result3);
				if($optarr1cnt!=$optarr2cnt) $hasoptions=FALSE;
				if($optarr1cnt>0 && $optarr2cnt>0){
					if($hasoptions){
						for($index2=0; $index2 < $optarr1cnt; $index2++){
							$hasthisoption=FALSE;
							for($index3=0; $index3 < $optarr2cnt; $index3++){
								if($optarr1[$index2]['coOptID']==$optarr2[$index3]['coOptID'] && $optarr1[$index2]['coCartOption']==$optarr2[$index3]['coCartOption']) $hasthisoption=TRUE;
							}
							if(! $hasthisoption) $hasoptions=FALSE;
						}
					}
				}
			}
			if($hasoptions) break;
		}
		ect_free_result($result2);
		if($thecartid!='' && $hasoptions){
			ect_query("DELETE FROM cartoptions WHERE coCartID=".$thecartid) or ect_error();
			ect_query("DELETE FROM cart WHERE cartID=".$thecartid) or ect_error();
			ect_query("UPDATE cart SET cartQuantity=cartQuantity+".$thequant." WHERE cartID=".$rs['cartID']) or ect_error();
			for($index=0; $index<$numaddedprods; $index++){
				if($addedprods[$index][5]==$thecartid) $addedprods[$index][5]=$rs['cartID'];
			}
		}
	}
	ect_free_result($result);
	for($index=0; $index<$numaddedprods; $index++){
		if($addedprods[$index][4]) checkpricebreaks($theid,$thepprice); else{ $actionaftercart=0; $cartrefreshseconds=3; }
	}
	if(getpost('ajaxadd')=='true'){
		$failedprods=0;
		do_stock_check(TRUE,$backorder,$stockwarning);
		$scidnoexistflag=''; $scbackorderflag=$scinstockflag=0;
		for($index2=0; $index2<$numaddedprods; $index2++){
			if($addedprods[$index2][4]==0){ $scidnoexistflag=$addedprods[$index2][0]; $failedprods++; }
			if($addedprods[$index2][4]==2) $scinstockflag=1;
			if($addedprods[$index2][4]==3) $scbackorderflag=1;
		}
		ob_clean();
		print jsurlencode(jsenc($scidnoexistflag)) . '&' . $scinstockflag . '&' . $scbackorderflag . '&' . ($numaddedprods-$failedprods);
		$sSQL='SELECT cartID,cartProdPrice,cartQuantity,pExemptions,pTax FROM cart INNER JOIN products ON cart.cartProdId=products.pID WHERE cartCompleted=0 AND ' . getsessionsql();
		$result=ect_query($sSQL) or ect_error();
		$totoptdiff=$sctotquant=$totalgoods=0;
		while($rs=ect_fetch_assoc($result)){
			$optPriceDiff=0;
			$pexemptions=$rs['pExemptions'];
			$thetax=$homeCountryTaxRate;
			if(@$perproducttaxrate==TRUE && ! is_null($rs['pTax'])) $thetax=$rs['pTax'];
			$sSQL='SELECT SUM(coPriceDiff) AS sumDiff FROM cartoptions WHERE coCartID='.$rs['cartID'];
			$result2=ect_query($sSQL) or ect_error();
			$rs2=ect_fetch_assoc($result2);
			if(! is_null($rs2['sumDiff'])) $optPriceDiff=$rs2['sumDiff'];
			ect_free_result($result2);
			$subtot=(($rs['cartProdPrice']+$optPriceDiff)*$rs['cartQuantity']);
			$sctotquant+=$rs['cartQuantity'];
			$totalgoods+=$subtot;
			if(@$GLOBALS['perproducttaxrate']){
				if(($pexemptions & 2)!=2) $countryTax+=($subtot*$thetax/100.0);
			}else{
				if(($pexemptions & 2)==2) $countrytaxfree+=$subtot;
			}
			for($index=0; $index<$numaddedprods; $index++){
				if($addedprods[$index][5]==$rs['cartID']) $addedprods[$index][3]=$rs['cartProdPrice'];
			}
		}
		ect_free_result($result);
		calculatediscounts($totalgoods,FALSE,$rgcpncode);
		if($totaldiscounts>$totalgoods) $totaldiscounts=$totalgoods;
		if($GLOBALS['showtaxinclusive']!=0) calculatetaxandhandling(); else $countryTax=0;
		print '&'.$sctotquant.'&'.jsurlencode(jsenc(FormatEuroCurrency(@$GLOBALS['nopriceanywhere']?0:($totalgoods-$totaldiscounts)+$countryTax))).'&';
		$sSQL="SELECT imageSrc FROM productimages WHERE imageType=0 AND imageProduct='".escape_string($theid)."' ORDER BY imageNumber LIMIT 0,1";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)) print jsurlencode(jsenc($rs['imageSrc']));
		ect_free_result($result);
		for($index=0; $index<$numaddedprods; $index++){
			if($addedprods[$index][5]!=''){
				$pexemptions=0;
				$thetax=$homeCountryTaxRate;
				$sSQL="SELECT pExemptions,pTax FROM products WHERE pID='".escape_string($theid)."'";
				$result=ect_query($sSQL) or ect_error();
				if($rs=ect_fetch_assoc($result)){
					$pexemptions=$rs['pExemptions'];
					if(@$perproducttaxrate==TRUE && ! is_null($rs['pTax'])) $thetax=$rs['pTax'];
				}
				ect_free_result($result);
				$sSQL='SELECT coOptGroup,coCartOption,coPriceDiff FROM cartoptions WHERE coCartID=' . $addedprods[$index][5] . ' ORDER BY coID';
				$result=ect_query($sSQL) or ect_error();
				if(($numitems=ect_num_rows($result))>0){
					$optresult='&'.$numitems;
					while($rs=ect_fetch_assoc($result)){
						$totoptdiff+=$rs['coPriceDiff'];
						$optresult.='&'.jsurlencode(jsenc($rs['coOptGroup'])).'&'.jsurlencode(jsenc($rs['coCartOption']));
					}
				}else
					$optresult='&0';
				ect_free_result($result);
				$totitemcost=$addedprods[$index][3]+$totoptdiff;
				for($index2=0; $index2<=4; $index2++){
					print '&';
					if($index2==3) print jsurlencode(jsenc(FormatEuroCurrency(@$GLOBALS['nopriceanywhere']?0:$totitemcost))); else print jsurlencode(jsenc($addedprods[$index][$index2]));
				}
				print $optresult;
			}
		}
		print '&'.jsurlencode(jsenc(FormatEuroCurrency(@$GLOBALS['nopriceanywhere']?0:$countryTax))).'&'.jsurlencode(jsenc(FormatEuroCurrency(@$GLOBALS['nopriceanywhere']?0:$totaldiscounts)));
		if($totaldiscounts==0){ $_SESSION['discounts']=NULL; unset($_SESSION['discounts']); print '&0&';} else{ $_SESSION['discounts']=$totaldiscounts; print '&1&';}
		$sSQL='SELECT cartID,cartProdID,cartProdName,cartProdPrice,cartQuantity FROM cart WHERE cartCompleted=0 AND '.getsessionsql();
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result))
			print jsurlencode(jsenc('<div class="minicartcnt">'.$rs['cartQuantity'].' '.$rs['cartProdName'].'</div>'));
		ect_free_result($result);
	}else{
		print '<div style="text-align:center;padding:40px">';
		do_stock_check(FALSE,$backorder,$stockwarning);
		if($stockwarning) $actionaftercart=4;
		if(! @isset($cartrefreshseconds)) $cartrefreshseconds=3;
		if($listid!='') $listidurl='&mode=sc' . ($listid!='0'?'&lid='.$listid:''); else $listidurl='';
		if($thefrompage!='' && @$actionaftercart==3){
			if($cartrefreshseconds==0 && ob_get_length()!==FALSE)
				header('Location: ' . $thefrompage);
			else
				print '<meta http-equiv="Refresh" content="'.$cartrefreshseconds.'; URL=' . $thefrompage . '">';
		}elseif(@$actionaftercart==4 || $cartrefreshseconds==0){
			$urllink='?rp='.urlencode($thefrompage);
			if($listid!='' && @$_SESSION['clientID']!='')
				$urllink.=$listidurl;
			elseif($stockwarning)
				$urllink.='&mode=add';
			if(ob_get_length()===FALSE) print '<meta http-equiv="Refresh" content="0; URL=cart.php'.$urllink.'">'; else header('Location: '.$storeurl.'cart.php'.$urllink);
		}else
			print '<meta http-equiv="Refresh" content="'.$cartrefreshseconds.'; URL=cart.php?rp='.urlencode($thefrompage).$listidurl.'">';
		print '<div class="hardcarttable" style="display:table;padding:10px;width:auto;margin-left:auto;margin-right:auto;border:1px solid grey">';
		if($stockwarning) print '<div class="hardcartstockwarn" style="text-align:center">' . $GLOBALS['xxInsMul'] . '</div>';
		for($index=0; $index<$numaddedprods; $index++){
			print '<div class="hardcartaddproductline" style="display:table-row"><div class="hardcartaddproductquant" style="display:table-cell;text-align:right;padding:6px">' . ($addedprods[$index][4]?$addedprods[$index][2]:'X') . '</div><div class="hardcartaddproduct" style="display:table-cell;text-align:left;padding:6px;">' . ($addedprods[$index][4] ? $addedprods[$index][1] . ' ' . $GLOBALS['xxAddOrd'] : '<span class="ectwarning">The product id <span style="color:#000000">' . htmlspecials($addedprods[$index][0]) . '</span> does not exist in the product database.</span></span>') . '</div></div>';
		}
		print '</div>';
		print '<div class="hardcartpleasewait" style="padding:10px;margin:50px 10px">' . $GLOBALS['xxPlsWait'] . ' <a class="ectlink" href="';
		if($thefrompage!='' && @$actionaftercart==3) print $thefrompage; else print 'cart.php?rp='.urlencode($thefrompage).$listidurl;
		print '"><strong>' . $GLOBALS['xxClkHere'] . '</strong></a>.</div>';
		print '</div>';
	}
}elseif($checkoutmode=='go' || $paypalexpress || $amazonpayment){
	function setcheckouterr($coerrmsg){
		global $success,$checkoutmode,$returntocustomerdetails,$errormsg;
		$success=FALSE; $checkoutmode='checkout'; $returntocustomerdetails=TRUE; $errormsg.='<div>' . $coerrmsg . '</div>';
		return(TRUE);
	}
	

	if(getpost('orderid')!='' && is_numeric(getpost('orderid')) && getpost('sessionid')!=''){
		retrieveorderdetails(getpost('orderid'), getpost('sessionid'));
	}elseif(! ($paypalexpress || $amazonpayment)){
		if((@$enableclientlogin==TRUE || @$forceclientlogin==TRUE) && @$_SESSION['clientID']!=''){
			$sSQL="SELECT clEmail FROM customerlogin WHERE clEmail<>'' AND clID='" . escape_string($_SESSION['clientID']) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)) $ordEmail=trim($rs['clEmail']); else $ordEmail=cleanupemail(getpost('email'));
			ect_free_result($result);
		}else
			$ordEmail=cleanupemail(getpost('email'));
		if((@$enableclientlogin==TRUE || @$forceclientlogin==TRUE) && getpost('addressid')!='' && getpost('addaddress')=='' && @$_SESSION['clientID']!=''){
			$sSQL="SELECT addName,addLastName,addAddress,addAddress2,addCity,addState,addZip,addCountry,addPhone,addExtra1,addExtra2 FROM address WHERE addCustID='" . escape_string($_SESSION['clientID']) . "' AND addID='" . escape_string(getpost('addressid')) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				$ordName=$rs['addName'];
				$ordLastName=$rs['addLastName'];
				$ordAddress=$rs['addAddress'];
				$ordAddress2=$rs['addAddress2'];
				$ordCity=$rs['addCity'];
				$ordState=$rs['addState'];
				$ordZip=$rs['addZip'];
				$ordCountry=$rs['addCountry'];
				$ordPhone=$rs['addPhone'];
				$ordExtra1=$rs['addExtra1'];
				$ordExtra2=$rs['addExtra2'];
				ect_query("UPDATE address SET addIsDefault=0 WHERE addCustID='" . escape_string($_SESSION['clientID']) . "'") or ect_error();
				ect_query("UPDATE address SET addIsDefault=1 WHERE addCustID='" . escape_string($_SESSION['clientID']) . "' AND addID='" . escape_string(getpost('addressid')) . "'") or ect_error();
			}
			ect_free_result($result);
		}else{
			$ordName=substr((getpost('name')==$GLOBALS['xxFirNam'] ? '' : strip_tags(getpost('name'))),0,100);
			$ordLastName=substr((getpost('lastname')==$GLOBALS['xxLasNam'] ? '' : strip_tags(getpost('lastname'))),0,100);
			$ordAddress=substr(strip_tags(getpost('address')),0,150);
			$ordAddress2=substr(strip_tags(getpost('address2')),0,150);
			$ordCity=substr(strip_tags(getpost('city')),0,75);
			$ordState=substr(strip_tags(getpost('state'.(getpost('state')==''?'2':''))),0,75);
			$ordZip=substr(strip_tags(getpost('zip')),0,50);
			$ordCountry=substr(getcountryfromid(getpost('country')),0,50);
			$ordPhone=substr(strip_tags(getpost('phone')),0,50);
			$ordExtra1=substr(strip_tags(getpost('ordextra1')),0,255);
			$ordExtra2=substr(strip_tags(getpost('ordextra2')),0,255);
		}
		if(getpost('allowemail')=='ON') addtomailinglist($ordEmail,trim($ordName.' '.$ordLastName));
		if((@$enableclientlogin==TRUE || @$forceclientlogin==TRUE) && getpost('saddressid')!='' && getpost('saddaddress')=='' && @$_SESSION['clientID']!=''){
			$sSQL="SELECT addName,addLastName,addAddress,addAddress2,addCity,addState,addZip,addCountry,addPhone,addExtra1,addExtra2 FROM address WHERE addCustID='" . escape_string($_SESSION['clientID']) . "' AND addID='" . escape_string(getpost('saddressid')) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				$ordShipName=$rs['addName'];
				$ordShipLastName=$rs['addLastName'];
				$ordShipAddress=$rs['addAddress'];
				$ordShipAddress2=$rs['addAddress2'];
				$ordShipCity=$rs['addCity'];
				$ordShipState=$rs['addState'];
				$ordShipZip=$rs['addZip'];
				$ordShipCountry=$rs['addCountry'];
				$ordShipPhone=$rs['addPhone'];
				$ordShipExtra1=$rs['addExtra1'];
				$ordShipExtra2=$rs['addExtra2'];
			}
			ect_free_result($result);
		}else{
			if((@$_SESSION['clientID']=='' && getpost('shipdiff')=='1') || (@$_SESSION['clientID']!='' && (getpost('saddaddress')=='add' || getpost('saddaddress')=='edit'))){
				$ordShipName=substr((getpost('sname')==$GLOBALS['xxFirNam'] ? '' : strip_tags(getpost('sname'))),0,100);
				$ordShipLastName=substr((getpost('slastname')==$GLOBALS['xxLasNam'] ? '' : strip_tags(getpost('slastname'))),0,100);
				$ordShipAddress=substr(strip_tags(getpost('saddress')),0,150);
				$ordShipAddress2=substr(strip_tags(getpost('saddress2')),0,150);
				$ordShipCity=substr(strip_tags(getpost('scity')),0,75);
				$ordShipState=substr(strip_tags(getpost('sstate'.(getpost('sstate')==''?'2':''))),0,75);
				$ordShipZip=substr(strip_tags(getpost('szip')),0,50);
				$ordShipCountry=substr(getcountryfromid(getpost('scountry')),0,50);
				$ordShipPhone=substr(strip_tags(getpost('sphone')),0,50);
				$ordShipExtra1=substr(strip_tags(getpost('ordsextra1')),0,255);
				$ordShipExtra2=substr(strip_tags(getpost('ordsextra2')),0,255);
				if((trim($ordShipName . $ordShipLastName)=='' || $ordShipAddress=='' || $ordShipCity=='' || $ordShipState=='' || $ordShipZip=='') && trim($ordShipName . $ordShipLastName . $ordShipAddress . $ordShipCity . $ordShipState . $ordShipZip . $ordShipPhone)!='')
					$errordshipaddress=setcheckouterr('If you enter a separate ship address you must enter all address details.');
			}
		}
		if(@$_SESSION['clientID']!=''){
			if(trim($ordName . $ordLastName)!='' && $ordAddress!='' && $ordCity!='' && $ordState!='' && $ordCountry!='' && $ordZip!='' && $ordPhone!=''){
				if(getpost('addaddress')=='add'){
					$sSQL="SELECT addID FROM address WHERE addCustID='" . escape_string(@$_SESSION['clientID']) . "' AND addName='".escape_string($ordName)."' AND addLastName='".escape_string($ordLastName)."' AND addAddress='".escape_string($ordAddress)."' AND addAddress2='".escape_string($ordAddress2)."' AND addCity='".escape_string($ordCity)."' AND addState='".escape_string($ordState)."' AND addZip='".escape_string($ordZip)."' AND addCountry='".escape_string($ordCountry)."' AND addPhone='".escape_string($ordPhone)."' AND addExtra1='".escape_string($ordExtra1)."' AND addExtra2='".escape_string($ordExtra2)."'";
					$result=ect_query($sSQL) or ect_error();
					$hasaddress=(ect_num_rows($result)>0);
					ect_free_result($result);
					$sSQL="INSERT INTO address (addCustID,addIsDefault,addName,addLastName,addAddress,addAddress2,addCity,addState,addZip,addCountry,addPhone,addExtra1,addExtra2) VALUES ('" . escape_string($_SESSION['clientID']) . "',0,'".escape_string($ordName)."','".escape_string($ordLastName)."','".escape_string($ordAddress)."','".escape_string($ordAddress2)."','".escape_string($ordCity)."','".escape_string($ordState)."','".escape_string($ordZip)."','".escape_string($ordCountry)."','".escape_string($ordPhone)."','".escape_string($ordExtra1)."','".escape_string($ordExtra2)."')";
					if(! $hasaddress) ect_query($sSQL) or ect_error();
				}elseif(getpost('addaddress')=='edit'){
					$sSQL="UPDATE address SET addName='".escape_string($ordName)."',addLastName='".escape_string($ordLastName)."',addAddress='".escape_string($ordAddress)."',addAddress2='".escape_string($ordAddress2)."',addCity='".escape_string($ordCity)."',addState='".escape_string($ordState)."',addZip='".escape_string($ordZip)."',addCountry='".escape_string($ordCountry)."',addPhone='".escape_string($ordPhone)."',addExtra1='".escape_string($ordExtra1)."',addExtra2='".escape_string($ordExtra2)."' WHERE addCustID='" . escape_string(@$_SESSION['clientID']) . "' AND addID='" . escape_string(getpost('addressid')) . "'";
					ect_query($sSQL) or ect_error();
				}
			}
			if(trim($ordShipName . $ordShipLastName)!='' && $ordShipAddress!='' && $ordShipCity!='' && $ordShipState!='' && $ordShipCountry!='' && $ordShipZip!=''){
				if(getpost('saddaddress')=='add'){
					$sSQL="SELECT addID FROM address WHERE addCustID='" . escape_string(@$_SESSION['clientID']) . "' AND addName='".escape_string($ordShipName)."' AND addLastName='".escape_string($ordShipLastName)."' AND addAddress='".escape_string($ordShipAddress)."' AND addAddress2='".escape_string($ordShipAddress2)."' AND addCity='".escape_string($ordShipCity)."' AND addState='".escape_string($ordShipState)."' AND addZip='".escape_string($ordShipZip)."' AND addCountry='".escape_string($ordShipCountry)."' AND addPhone='".escape_string($ordShipPhone)."' AND addExtra1='".escape_string($ordShipExtra1)."' AND addExtra2='".escape_string($ordShipExtra2)."'";
					$result=ect_query($sSQL) or ect_error();
					$hasaddress=(ect_num_rows($result)>0);
					ect_free_result($result);
					$sSQL="INSERT INTO address (addCustID,addIsDefault,addName,addLastName,addAddress,addAddress2,addCity,addState,addZip,addCountry,addPhone,addExtra1,addExtra2) VALUES ('" . escape_string($_SESSION['clientID']) . "',0,'".escape_string($ordShipName)."','".escape_string($ordShipLastName)."','".escape_string($ordShipAddress)."','".escape_string($ordShipAddress2)."','".escape_string($ordShipCity)."','".escape_string($ordShipState)."','".escape_string($ordShipZip)."','".escape_string($ordShipCountry)."','".escape_string($ordShipPhone)."','".escape_string($ordShipExtra1)."','".escape_string($ordShipExtra2)."')";
					if(! $hasaddress) ect_query($sSQL) or ect_error();
				}elseif(getpost('saddaddress')=='edit'){
					$sSQL="UPDATE address SET addName='".escape_string($ordShipName)."',addLastName='".escape_string($ordShipLastName)."',addAddress='".escape_string($ordShipAddress)."',addAddress2='".escape_string($ordShipAddress2)."',addCity='".escape_string($ordShipCity)."',addState='".escape_string($ordShipState)."',addZip='".escape_string($ordShipZip)."',addCountry='".escape_string($ordShipCountry)."',addPhone='".escape_string($ordShipPhone)."',addExtra1='".escape_string($ordShipExtra1)."',addExtra2='".escape_string($ordShipExtra2)."' WHERE addCustID='" . escape_string(@$_SESSION['clientID']) . "' AND addID='" . escape_string(getpost('saddressid')) . "'";
					ect_query($sSQL) or ect_error();
				}
			}
		}
		$ordAddInfo=substr(getpost('ordAddInfo'),0,4096);
		if($commercialloc_) $ordComLoc=1; else $ordComLoc=0;
		if($wantinsurance_ || abs(@$addshippinginsurance)==1) $ordComLoc+=2;
		if($saturdaydelivery_) $ordComLoc+=4;
		if($signaturerelease_) $ordComLoc+=8;
		if($insidedelivery_) $ordComLoc+=16;
		$ordAffiliate=trim(strip_tags(substr(getpost('PARTNER'),0,48)));
		$ordCheckoutExtra1=trim(strip_tags(substr(getpost('ordcheckoutextra1'),0,255)));
		$ordCheckoutExtra2=trim(strip_tags(substr(getpost('ordcheckoutextra2'),0,255)));
	}
	if($ordShipAddress!=''){
		$shipcountry=$ordShipCountry;
		$shipstate=$ordShipState;
		$destZip=$ordShipZip;
	}else{
		$shipcountry=$ordCountry;
		$shipstate=$ordState;
		$destZip=$ordZip;
		if(@$autobillingtoshipping==TRUE){
			$ordShipName=@$ordName;
			$ordShipLastName=@$ordLastName;
			$ordShipAddress=@$ordAddress;
			$ordShipAddress2=@$ordAddress2;
			$ordShipCity=@$ordCity;
			$ordShipState=@$ordState;
			$ordShipZip=@$ordZip;
			$ordShipCountry=@$ordCountry;
			$ordShipPhone=@$ordPhone;
			$ordShipExtra1=@$ordExtra1;
			$ordShipExtra2=@$ordExtra2;
		}
	}
	$sSQL="SELECT countryID,countryCode,loadStates FROM countries WHERE countryName='" . escape_string($ordCountry) . "'";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		$countryID=$rs['countryID'];
		$countryCode=$rs['countryCode'];
		$homecountry=($rs['countryID']==$origCountryID);
		if($rs['loadStates']!=-1 && $ordState=='') $errordstate=setcheckouterr('You must select a state before proceeding.');
	}else
		$success=FALSE;
	ect_free_result($result);
	if(! $homecountry) $perproducttaxrate=FALSE;
	$sSQL="SELECT countryID,countryTax,countryCode,countryFreeShip,loadStates FROM countries WHERE countryName='" . escape_string($shipcountry) . "'";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		$countryTaxRate=$rs['countryTax'];
		$shipCountryID=$rs['countryID'];
		$shipCountryCode=$rs['countryCode'];
		$freeshipavailtodestination=($rs['countryFreeShip']==1);
		$shiphomecountry=($rs['countryID']==$origCountryID) || (($rs['countryID']==1 || $rs['countryID']==2) && @$usandcasplitzones);
		if($rs['loadStates']!=-1 && $shipstate=='' && $ordShipAddress!='') $errordshipstate=setcheckouterr('You must select a state before proceeding.');
	}else{
		if($shipcountry!=$ordCountry) $errordshipcountry=setcheckouterr('You must select a ship country.');
		if($shipcountry!='') $errormsg.='<div>Invalid countryName:' . htmldisplay($shipcountry) . '</div>';
	}
	ect_free_result($result);
	$allcart=$orderid=$ordauthstatus='';
	if($success){
		if($countryID==1||$countryID==2) $stateAbbrev=getstateabbrev($ordState);
		if($shipCountryID==1||$shipCountryID==2) $shipStateAbbrev=getstateabbrev($shipstate);
		if($shiphomecountry&&$shipCountryID!=''){
			$sSQL="SELECT stateTax,stateAbbrev,stateFreeShip FROM states WHERE stateCountryID='" . $shipCountryID . "' AND (stateName='" . escape_string($shipstate) . "'";
			if($shipCountryID==1||$shipCountryID==2) $sSQL.=" OR stateAbbrev='" . escape_string($shipstate) . "')"; else $sSQL.=')';
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				if($shipCountryID==$origCountryID) $stateTaxRate=$rs['stateTax']; else $stateTaxRate=0;
				$freeshipavailtodestination=($freeshipavailtodestination && ($rs['stateFreeShip']==1));
			}
			ect_free_result($result);
			if($willpickup_){
				if(@isset($homestatetaxrate))
					$stateTaxRate=$homestatetaxrate;
				else{
					$result=ect_query("SELECT MAX(stateTax) as maxtax FROM states WHERE stateCountryID='" . $shipCountryID . "' AND stateEnabled=1") or ect_error();
					if($rs=ect_fetch_assoc($result)) $stateTaxRate=$rs['maxtax'];
					ect_free_result($result);
				}
			}
		}
		if(($shipType==4 || $shipType==7 || $shipType==8) && $shipCountryID==1 && @$shipStateAbbrev=='GU') $shipCountryCode='GU';
		if(trim(@$_SESSION["clientUser"])!=''){
			if(((int)$_SESSION["clientActions"] & 1)==1) $stateTaxRate=0;
			if(((int)$_SESSION["clientActions"] & 2)==2) $countryTaxRate=0;
		}
		getpayprovhandling();
		$shipType=getshiptype();
		if(! initshippingmethods()){ $success=FALSE; $checkoutmode='checkout'; }
		$sSQL="SELECT ordID,ordAuthStatus FROM orders WHERE ordStatus>1 AND ordAuthNumber='' AND " . getordersessionsql();
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){ $orderid=$rs['ordID']; $ordauthstatus=$rs['ordAuthStatus']; }
		ect_free_result($result);
		$sSQL='SELECT cartID,cartProdID,cartProdPrice,cartQuantity,pWeight,pShipping,pShipping2,pExemptions,pSection,topSection,pDims,pTax FROM cart LEFT JOIN products ON cart.cartProdID=products.pId LEFT OUTER JOIN sections ON products.pSection=sections.sectionID WHERE cartCompleted=0 AND ' . getsessionsql() . ' ORDER BY cartID';
		$allcart=ect_query($sSQL) or ect_error();
	}
	if($ordCountry=='')
		$errordcountry=setcheckouterr('You must select a country.');
	else{
		if($ordEmail=='') $errordemail=setcheckouterr('You must enter a valid email address.');
		if(trim($ordName . $ordLastName)=='') $errordname=setcheckouterr('You must enter your name.');
		if($ordAddress=='') $errordaddress=setcheckouterr('You must enter your address.');
		if($ordCity=='') $errordcity=setcheckouterr('You must enter a city.');
		if($ordZip=='') $errordzip=setcheckouterr('You must enter a zip / postal code.');
		if($ordPhone=='') $errordphone=setcheckouterr('You must enter a phone number.');
	}
	if(! is_numeric($ordPayProvider)) $errordpayprovider=setcheckouterr('You must select a payment method.');
	if(($orderid=='' && getpost('shipselectoraction')!='')){
		$success=FALSE;
		$errormsg='Invalid Data';
	}
	if(@$GLOBALS['termsandconditions'] && getpost("license")!="1" && getpost("shipselectoraction")=='' && $ordPayProvider!='19' && $ordPayProvider!='21') $errtermsandconditions=setcheckouterr("Please proceed only if you are in acceptance of our terms and conditions.");
	if($success && (($itemsincart=ect_num_rows($allcart))>0)){
		$rowcounter=0;
		$index=0;
		while($rsCart=ect_fetch_assoc($allcart))
			$cartrows[$index++]=$rsCart;
		for($index=0;$index<$itemsincart;$index++){
			$rsCart=$cartrows[$index];
			if(is_null($rsCart['pWeight'])) $rsCart['pWeight']=0;
			if(($rsCart['cartProdID']==$giftcertificateid || $rsCart['cartProdID']==$donationid) && is_null($rsCart['pExemptions'])) $rsCart['pExemptions']=15;
			if($rsCart['cartProdID']==$giftwrappingid && is_null($rsCart['pExemptions'])) $rsCart['pExemptions']=12;
			$sSQL='SELECT SUM(coPriceDiff) AS coPrDff FROM cartoptions WHERE coCartID='. $rsCart['cartID'];
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				$rsCart['cartProdPrice']+=(double)$rs['coPrDff'];
			}
			ect_free_result($result);
			$sSQL='SELECT SUM(coWeightDiff) AS coWghtDff FROM cartoptions WHERE coCartID='. $rsCart['cartID'];
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				$rsCart['pWeight']+=(double)$rs['coWghtDff'];
			}
			ect_free_result($result);
			$runTot=$rsCart['cartProdPrice'] * (int)($rsCart['cartQuantity']);
			$totalquantity+=(int)($rsCart['cartQuantity']);
			$totalgoods+=$runTot;
			$thistopcat=0;
			if(trim(@$_SESSION['clientID'])!=''){ $rsCart['pExemptions']=((int)$rsCart['pExemptions'] | ((int)$_SESSION['clientActions'] & 7)); if(((int)$_SESSION['clientActions'] & 32)==32) $rsCart['pExemptions']|=8; }
			if(($shipType==2 || $shipType==3 || $shipType==4 || $shipType>=6) && (double)$rsCart['pWeight']<=0.0)
				$rsCart['pExemptions']=($rsCart['pExemptions'] | 4);
			if(($rsCart['pExemptions'] & 1)==1) $statetaxfree+=$runTot;
			if(($rsCart['pExemptions'] & 8)!=8){ $handlingeligableitem=TRUE; $handlingeligablegoods+=$runTot; }
			if(@$perproducttaxrate==TRUE){
				if(is_null($rsCart['pTax'])) $rsCart['pTax']=$countryTaxRate;
				if(($rsCart['pExemptions'] & 2)!=2) $countryTax+=(($rsCart['pTax'] * $runTot) / 100.0);
			}else{
				if(($rsCart['pExemptions'] & 2)==2) $countrytaxfree+=$runTot;
			}
			if(($rsCart['pExemptions'] & 4)==4) $shipfreegoods+=$runTot;
			addproducttoshipping($rsCart,$index);
		}
		calculatediscounts(round($totalgoods,2),TRUE,$rgcpncode);
		calculateshipping();
		if(! $fromshipselector) insuranceandtaxaddedtoshipping();
		calculateshippingdiscounts(TRUE);
		if(@$_SESSION['clientID']!='' && @$_SESSION['clientActions']!=0) $cpnmessage.=$GLOBALS['xxLIDis'] . strip_tags(str_replace('"','',$_SESSION['clientUser'])) . '<br />';
		$cpnmessage=substr($cpnmessage,6);
		calculatetaxandhandling();
		$totalgoods=round($totalgoods,(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
		$shipping=round($shipping,(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
		$freeshipamnt=round($freeshipamnt,(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
		$loyaltypointsused=0;
		if(@$loyaltypoints!='' && @$_SESSION['clientID']!='' && @$_SESSION['noredeempoints']!=TRUE){
			if(! ((@$GLOBALS['loyaltypointsnowholesale'] && (@$_SESSION['clientActions'] & 8)==8) || (@$GLOBALS['loyaltypointsnopercentdiscount'] && (@$_SESSION['clientActions'] & 16)==16))){
				if($orderid!=''){
					$pointsRedeemed=0;
					$result=ect_query("SELECT pointsRedeemed FROM orders WHERE ordID=" . $orderid) or ect_error();
					if($rs=ect_fetch_assoc($result))
						$pointsRedeemed=$rs['pointsRedeemed'];
					ect_free_result($result);
					if($pointsRedeemed>0){
						ect_query("UPDATE customerlogin SET loyaltyPoints=loyaltyPoints+" . $pointsRedeemed . " WHERE clID=" . $_SESSION['clientID']) or ect_error();
						ect_query("UPDATE orders SET loyaltyPoints=0 WHERE ordID=" . $orderid) or ect_error();
					}
				}
				$result=ect_query("SELECT loyaltyPoints FROM customerlogin WHERE clID=" . $_SESSION['clientID']) or ect_error();
				if($rs=ect_fetch_assoc($result))
					$loyaltypointsused=$rs['loyaltyPoints'];
				ect_free_result($result);
				if(round($loyaltypointsused*$loyaltypointvalue,2)>=(@$loyaltypointminimum!=''?$loyaltypointminimum:0.05)){
					$loyaltypointdiscount=$loyaltypointsused*$loyaltypointvalue;
					if($loyaltypointdiscount>$totalgoods-$totaldiscounts){ $loyaltypointdiscount=$totalgoods-$totaldiscounts; $loyaltypointsused=(int)($loyaltypointdiscount/$loyaltypointvalue); }
					$totaldiscounts+=round($loyaltypointdiscount,2);
					ect_query("UPDATE customerlogin SET loyaltyPoints=loyaltyPoints-" . $loyaltypointsused . " WHERE clID=" . $_SESSION['clientID']) or ect_error();
					$cpnmessage.=$GLOBALS['xxLoyPod'] . ': ' . FormatEuroCurrency($loyaltypointdiscount) . "<br />";
				}
			}
		}
		if($totaldiscounts>$totalgoods) $totaldiscounts=$totalgoods;
		if(@$addshippingtodiscounts){
			$totaldiscounts+=$freeshipamnt;
			$freeshipamnt=0;
		}
		$totaldiscounts=round($totaldiscounts,(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
		$grandtotal=round(($totalgoods + $shipping + $stateTax + $countryTax + $handling) - ($totaldiscounts + $freeshipamnt),(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
		if($grandtotal < 0) $grandtotal=0;
		if($ordShipName=='' && $ordShipLastName=='' && $ordShipAddress=='' && $ordShipAddress2=='' && $ordShipCity=='') $ordShipCountry='';
		do_stock_check(FALSE,$backorder,$stockwarning);
		if(getpost('shipselectoraction')!=''){ $stockwarning=FALSE; $backorder=FALSE; }
		if($stockwarning){
			$checkoutmode='';
			if(ob_get_length()!==FALSE)
				header('Location: ' . $storeurl . 'cart.php');
			else
				print '<meta http-equiv="Refresh" content="0; URL=' . $storeurl . 'cart.php"></body></html>';
			flush();
			exit;
		}
		if(($success || getpost('shipselectoraction')=='') && ! $stockwarning){
			if($orderid==''){
				$isneworder=TRUE;
				$referer=@$_SESSION['httpreferer'];
				$storeurlpos=strpos(strtolower($referer), str_replace(array('http://','https://'),'',strtolower($storeurl)));
				if(@$pathtossl!='')$pathtosslpos=strpos(strtolower($referer), str_replace(array('http://','https://'),'',strtolower(@$pathtossl)));else $pathtosslpos=FALSE;
				if(($storeurlpos!==FALSE&&$storeurlpos<10) || (@$pathtossl!=''&&$pathtosslpos!==FALSE&&$pathtosslpos<10)) $referer='';
				$referarr=explode('?', $referer, 2);
				$sSQL='INSERT INTO orders (ordSessionID,ordClientID,ordName,ordLastName,ordAddress,ordAddress2,ordCity,ordState,ordZip,ordCountry,ordEmail,ordPhone,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipZip,ordShipCountry,ordShipPhone,ordPayProvider,ordAuthNumber,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordHandling,ordShipType,ordShipCarrier,ordTotal,ordDate,ordStatus,ordAuthStatus,pointsRedeemed,ordStatusDate,ordComLoc,ordIP,ordAffiliate,ordExtra1,ordExtra2,ordShipExtra1,ordShipExtra2,ordCheckoutExtra1,ordCheckoutExtra2,ordAVS,ordCVV,ordLang,ordReferer,ordQuerystr,ordDiscount,ordDiscountText,ordAddInfo) VALUES (' .
					"'" . escape_string($thesessionid) . "'," .
					"'" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "'," .
					"'" . escape_string($ordName) . "','" . escape_string($ordLastName) . "','" . escape_string($ordAddress) . "','" . escape_string($ordAddress2) . "'," .
					"'" . escape_string($ordCity) . "','" . escape_string($ordState) . "','" . escape_string($ordZip) . "','" . escape_string($ordCountry) . "'," .
					"'" . escape_string($ordEmail) . "','" . escape_string($ordPhone) . "'," .
					"'" . escape_string($ordShipName) . "','" . escape_string($ordShipLastName) . "','" . escape_string($ordShipAddress) . "','" . escape_string($ordShipAddress2) . "'," .
					"'" . escape_string($ordShipCity) . "','" . escape_string($ordShipState) . "','" . escape_string($ordShipZip) . "','" . escape_string($ordShipCountry) . "'," .
					"'" . escape_string($ordShipPhone) . "'," .
					"'" . escape_string($ordPayProvider) . "','','" . escape_string($shipping-$freeshipamnt) . "'," .
					($usehst?'0,0,' . ($stateTax + $countryTax) . ',' : "'" . escape_string($stateTax) . "','" . escape_string($countryTax) . "',0,") .
					"'" . escape_string($handling) . "','" . escape_string($shipMethod) . "','" . escape_string($shipType) . "','" . escape_string($totalgoods) . "'," .
					"'" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "',2,''," . $loyaltypointsused . ",'" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "'," .
					"'" . $ordComLoc . "','" . escape_string(getipaddress()) . "','" . escape_string($ordAffiliate) . "'," .
					"'" . escape_string($ordExtra1) . "','" . escape_string($ordExtra2) . "','" . escape_string($ordShipExtra1) . "','" . escape_string($ordShipExtra2) . "','" . escape_string($ordCheckoutExtra1) . "','" . escape_string($ordCheckoutExtra2) . "'," .
					"'" . escape_string($ordAVS) . "','" . escape_string($ordCVV) . "'," .
					"'" . escape_string((@$languageid==''?1:$languageid)-1) . "'," .
					"'" . escape_string($referarr[0]) . "','" . escape_string(@$referarr[1]) . "'," .
					"'" . escape_string($totaldiscounts) . "'," .
					"'" . escape_string(substr($cpnmessage,0,255)) . "'," .
					"'" . escape_string($ordAddInfo) . "')";
				ect_query($sSQL) or ect_error();
				$orderid=ect_insert_id();
				

			}else{
				$isneworder=FALSE;
				$sSQL='UPDATE orders SET ';
				if(getpost('shipselectoraction')==''){
					$sSQL.="ordSessionID='" . escape_string($thesessionid) . "'," .
						"ordClientID='" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "'," .
						"ordName='" . escape_string($ordName) . "',ordLastName='" . escape_string($ordLastName) . "',ordAddress='" . escape_string($ordAddress) . "',ordAddress2='" . escape_string($ordAddress2) . "'," .
						"ordCity='" . escape_string($ordCity) . "',ordState='" . escape_string($ordState) . "',ordZip='" . escape_string($ordZip) . "',ordCountry='" . escape_string($ordCountry) . "'," .
						"ordEmail='" . escape_string($ordEmail) . "',ordPhone='" . escape_string($ordPhone) . "'," .
						"ordShipName='" . escape_string($ordShipName) . "',ordShipLastName='" . escape_string($ordShipLastName) . "',ordShipAddress='" . escape_string($ordShipAddress) . "',ordShipAddress2='" . escape_string($ordShipAddress2) . "'," .
						"ordShipCity='" . escape_string($ordShipCity) . "',ordShipState='" . escape_string($ordShipState) . "',ordShipZip='" . escape_string($ordShipZip) . "',ordShipCountry='" . escape_string($ordShipCountry) . "',ordShipPhone='" . escape_string($ordShipPhone) . "'," .
						"ordPayProvider='" . escape_string($ordPayProvider) . "',ordAuthNumber=''," .
						"ordComLoc=" . $ordComLoc . ",ordIP='" . escape_string(getipaddress()) . "',ordAffiliate='" . escape_string($ordAffiliate) . "'," .
						"ordExtra1='" . escape_string($ordExtra1) . "',ordExtra2='" . escape_string($ordExtra2) . "'," .
						"ordShipExtra1='" . escape_string($ordShipExtra1) . "',ordShipExtra2='" . escape_string($ordShipExtra2) . "'," .
						"ordCheckoutExtra1='" . escape_string($ordCheckoutExtra1) . "',ordCheckoutExtra2='" . escape_string($ordCheckoutExtra2) . "'," .
						"ordAVS='" . escape_string($ordAVS) . "',ordCVV='" . escape_string($ordCVV) . "'," .
						"ordLang='" . escape_string((@$languageid==''?1:$languageid)-1) . "'," .
						"ordDiscount='" . $totaldiscounts . "'," .
						"ordAddInfo='" . escape_string($ordAddInfo) . "',";
				}
				$sSQL.="ordDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "',ordStatusDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "'," .
					"ordShipping='" . ($shipping - $freeshipamnt) . "'," .
					"ordDiscountText='" . escape_string(substr($cpnmessage,0,255)) . "',ordTotal='" . $totalgoods . "',ordStateTax=" . ($usehst ? "0,ordCountryTax=0,ordHSTTax=" . ($stateTax + $countryTax) : "'" . $stateTax . "',ordCountryTax='" . $countryTax . "',ordHSTTax=0") . ",ordHandling='" . $handling . "'," .
					"ordShipType='" . escape_string($shipMethod) . "',ordShipCarrier='" . $shipType . "',ordAuthStatus='',pointsRedeemed=" . $loyaltypointsused .
					" WHERE ordID='" . $orderid . "'";
				ect_query($sSQL) or ect_error();
			


			}

			
					


			$sSQL="UPDATE cart SET cartOrderID=". $orderid . " WHERE cartCompleted=0 AND " . getsessionsql();
			ect_query($sSQL) or ect_error();
			if($isneworder || $ordauthstatus=='MODWARNOPEN') stock_subtract($orderid);
			$sSQL="SELECT gcaGCID,gcaAmount FROM giftcertsapplied WHERE gcaOrdID='" . $orderid . "'";
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){
				ect_query("UPDATE giftcertificate SET gcRemaining=gcRemaining+".round($rs['gcaAmount'], 2)." WHERE gcID='".$rs['gcaGCID']."'");
			}
			ect_free_result($result);
			ect_query("DELETE FROM giftcertsapplied WHERE gcaOrdID='" . $orderid . "'");
			if(@$_SESSION['giftcerts']!='' && $grandtotal>0){
				$sSQL="SELECT gcID,gcRemaining FROM giftcertificate WHERE gcRemaining>0 AND gcAuthorized<>0 AND gcID IN ('" . str_replace(' ',"','",escape_string(@$_SESSION['giftcerts'])) . "')";
				$result=ect_query($sSQL) or ect_error();
				while($rs=ect_fetch_assoc($result)){
					if($giftcertsamount>=$grandtotal) break;
					$thiscertamount=min($grandtotal-$giftcertsamount, $rs['gcRemaining']);
					ect_query("INSERT INTO giftcertsapplied (gcaGCID,gcaOrdID,gcaAmount) VALUES ('" . $rs['gcID'] . "','" . $orderid . "'," . $thiscertamount . ')');
					ect_query("UPDATE giftcertificate SET gcRemaining=gcRemaining-" . round($thiscertamount, 2) . ",gcDateUsed='" . date("Y-m-d", time() + ($dateadjust*60*60)) . "' WHERE gcID='" . $rs['gcID'] . "'");
					$giftcertsamount+=$thiscertamount;
				}
				ect_free_result($result);
				$totaldiscounts+=$giftcertsamount;
				$grandtotal -= $giftcertsamount;
				$cpnmessage.=$GLOBALS['xxAppGC'] . ' ' . FormatEuroCurrency($giftcertsamount) . ($cpnmessage!='' ? '<br />' : '');
				$sSQL="UPDATE orders SET ordDiscount=" . $totaldiscounts . ",ordDiscountText='" . escape_string($cpnmessage) . "' WHERE ordID='" . $orderid . "'";
				ect_query($sSQL) or ect_error();
			}
			$descstr='';
			$addcomma='';
			$sSQL="SELECT cartID,cartProdID,cartQuantity,cartProdName FROM cart WHERE cartOrderID=" . $orderid . " AND cartCompleted=0";
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){
				if($rs['cartProdID']==$giftcertificateid) ect_query("UPDATE giftcertificate SET gcOrderID='" . $orderid . "' WHERE gcCartID='" . $rs['cartID'] . "'") or ect_error();
				$descstr.=$addcomma . $rs['cartQuantity'] . ' ' . strip_tags($rs['cartProdName']);
				$addcomma=', ';
			}
			ect_free_result($result);
			$descstr=str_replace('"','',$descstr);
			if(! $fromshipselector){
				ect_query("DELETE FROM shipoptions WHERE soOrderID=".$orderid." OR soDateAdded<'".date('Y-m-d', time() - (24*60*60))."'") or ect_error();
				saveshippingoptions();
			}
			if(getpost('remember')=='1'){
				ectsetcookie('id1',$orderid,186, '/', '');
				ectsetcookie('id2',$thesessionid,186, '/', '');
			}
			

		}
	}else
		$success=FALSE;
	if($allcart) ect_free_result($allcart);
	if($stockwarning||$returntocustomerdetails){
		$success=FALSE;
	}elseif($success&&$ordPayProvider!=''){
		$blockuser=checkuserblock($ordPayProvider);
		if($blockuser){
			$orderid=0;
			$thesessionid='';
			$GLOBALS['xxMstClk']=$multipurchaseblockmessage;
			$data1=$data2=$data3='';
		}else
			getpayprovdetails($ordPayProvider,$data1,$data2,$data3,$demomode,$ppmethod);
		$origstoreurl=$storeurl;
		if(@$pathtossl!=''){
			if(substr($pathtossl,-1)!="/") $pathtossl.="/";
			$storeurl=$pathtossl;
		}
		if(@$wpconfirmpage=='') $wpconfirmpage='wpconfirm.php';
		if(@$GLOBALS['nopriceanywhere']) $grandtotal=0;
		if($success==FALSE){
			print '<form method="post" action="cart.php">';
		}elseif($grandtotal>0 && $ordPayProvider=='1'){ // PayPal
			$php_version=explode('.', phpversion());
			if(((int)$php_version[0])<5) print 'Please note, you are using PHP 4 and PHP 5 is required for PayPal.';
			if(strpos($data1,'/')!==FALSE){
				$data1arr=explode('/',$data1);
				if($grandtotal<12) $data1=trim($data1arr[1]); else $data1=trim($data1arr[0]);
			}
			if(@$paypalhostedsolution){
				print '<form method="post" action="https://securepayments.' . ($demomode ? 'sandbox.' : '') . 'paypal.com/cgi-bin/acquiringweb">';
				print whv('cmd', '_hosted-payment');
			}else{
				print '<form method="post" action="https://www.'.($demomode?'sandbox.':'').'paypal.com/cgi-bin/webscr">';
				print whv('cmd', '_ext-enter') . whv('redirect_cmd', '_xclick') . whv('rm', '2');
			}
			print whv('business', $data1) . whv('return', $storeurl.'thanks.php');
			print whv('notify_url', $storeurl.'vsadmin/ppconfirm.php') . whv('item_name', substr($descstr,0,127)) . whv('custom', $orderid) . whv('invoice', $orderid) . whv('no_note','1');
			if(@$paypallc!='') print whv('lc', $paypallc);
			if(@$paypalhostedsolution){
				print whv('subtotal', number_format($grandtotal, getDPs($countryCurrency),'.',''));
			}elseif(@$splitpaypalshipping){
				print whv('shipping', number_format(($shipping + $handling) - $freeshipamnt, getDPs($countryCurrency),'.',''));
				print whv('amount', number_format(($totalgoods + $stateTax + $countryTax) - $totaldiscounts, getDPs($countryCurrency),'.',''));
			}else{
				print whv('amount', number_format($grandtotal, getDPs($countryCurrency),'.',''));
			}
			print whv('currency_code', $countryCurrency) . whv('bn', 'ecommercetemplates_Cart_WPS_US');
			if(@$usefirstlastname){
				print whv('first_name', $ordName) . whv('last_name', $ordLastName);
				if(@$paypalhostedsolution) print whv('billing_first_name', $ordName) . whv('billing_last_name', $ordLastName);
			}elseif(strpos(trim($ordName), ' ')!==FALSE){
				$namearr=explode(' ',trim($ordName),2);
				print whv('first_name', $namearr[0]) . whv('last_name', $namearr[1]);
				if(@$paypalhostedsolution) print whv('billing_first_name', $namearr[0]) . whv('billing_last_name', $namearr[1]);
			}else{
				print whv('last_name', $thename);
				if(@$paypalhostedsolution) print whv('billing_last_name', $thename);
			}
			if((trim($ordShipName)!='' || trim($ordShipLastName)!='' || trim($ordShipAddress)!='') && @$paypalhostedsolution)
				print whv('address1', $ordShipAddress) . whv('address2', $ordShipAddress2) . whv('city', $ordShipCity) . whv('state', $shipCountryID==1 && $shipStateAbbrev!='' ? $shipStateAbbrev : $ordShipState) . whv('country', $shipCountryCode) . whv('zip', $ordShipZip);
			else
				print whv('address1', $ordAddress) . whv('address2', $ordAddress2) . whv('city', $ordCity) . whv('state', $countryID==1 && $stateAbbrev!='' ? $stateAbbrev : $ordState) . whv('country', $countryCode) . whv('zip', $ordZip);
			print whv('email', $ordEmail);
			if(@$paypalhostedsolution) print whv('billing_address1', $ordAddress) . whv('billing_address2', $ordAddress2) . whv('billing_city', $ordCity) . whv('billing_state', $countryID==1 && $stateAbbrev!='' ? $stateAbbrev : $ordState) . whv('billing_country', $countryCode) . whv('buyer_email', $ordEmail) . whv('billing_zip', $ordZip);
			print whv('cancel_return', $origstoreurl.'cart.php');
			if($countryCode!='US' && $countryCode!='CA') print whv('night_phone_b', $ordPhone);
			if($ppmethod==1) print whv('paymentaction', 'authorization');
		}elseif($grandtotal>0 && $ordPayProvider=="2"){ // 2Checkout
			$courl='https://www.2checkout.com/cgi-bin/sbuyers/cartpurchase.2c';
			if(is_numeric($data1))
				if($data1>200000 || @$use2checkoutv2==TRUE) $courl='https://www2.2checkout.com/2co/buyer/purchase';
			if(@$use2checkoutfastcheckout==TRUE) $courl='https://www.2checkout.com/checkout/spurchase';
			print '<form method="post" action="' . $courl . '">';
			print whv('cart_order_id', $orderid) . whv('merchant_order_id', $orderid) . whv('sid', $data1) . whv('total', $grandtotal) . whv('card_holder_name', trim($ordName.' '.$ordLastName)) . whv('street_address', $ordAddress . ($ordAddress2!='' ? ', ' . $ordAddress2 : ''));
			if($countryID==1 || $countryID==2)
				print whv('city', $ordCity) . whv('state', $ordState);
			else
				print whv('city', $ordCity . ($ordState!='' ? ', ' . $ordState : '')) . whv('state', 'Outside US and Canada');
			print whv('zip', $ordZip) . whv('country', $countryCode) . whv('email', $ordEmail) . whv('phone', $ordPhone);
			print whv('id_type', '1');
			$sSQL='SELECT cartID,cartProdID,cartProdName,cartProdPrice,cartQuantity,' . (@$digidownloads==TRUE ? 'pDownload,' : '') . 'pDescription FROM cart LEFT JOIN products on cart.cartProdID=products.pID WHERE cartCompleted=0 AND ' . getsessionsql();
			$result=ect_query($sSQL) or ect_error();
			$index=1;
			while($rs=ect_fetch_assoc($result)){
				$thedesc=substr(trim(preg_replace("(\r\n|\n|\r)",'\\n',strip_tags($rs['pDescription']))),0,254);
				if($thedesc=='') $thedesc=substr(trim(preg_replace("(\r\n|\n|\r)",'\\n',strip_tags($rs['cartProdName']))),0,254);
				print whv('c_prod_' . $index, str_replace(',','&#44;',$rs['cartProdID']) . ',' . $rs['cartQuantity']);
				print whv('c_name_' . $index, strip_tags($rs['cartProdName']));
				print whv('c_description_' . $index, $thedesc);
				print whv('c_price_' . $index, number_format($rs['cartProdPrice'],2,'.',''));
				if(@$digidownloads==TRUE)
					if(trim($rs['pDownload'])!='') print whv('c_tangible_' . $index, 'N');
				$index++;
			}
			if(trim($ordShipName)!='' || trim($ordShipLastName)!='' || trim($ordShipAddress)!='')
				print whv('ship_name', trim($ordShipName.' '.$ordShipLastName)) . whv('ship_street_address', $ordShipAddress . ($ordShipAddress2!='' ? ', ' . $ordShipAddress2 : '')) . whv('ship_city', $ordShipCity) . whv('ship_state', $ordShipState) . whv('ship_zip', $ordShipZip) . whv('ship_country', $ordShipCountry);
			if($demomode) print whv('demo', 'Y');
			print whv('pay_method', 'CC') . whv('fixed', 'Y');
		}elseif($grandtotal>0 && $ordPayProvider=="3"){ // Authorize.net SIM
			if(@$authnetemulateurl=='') $authnetemulateurl='https://secure.authorize.net/gateway/transact.dll';
			if(@$secretword!=''){
				$data1=upsdecode($data1, $secretword);
				$data2=upsdecode($data2, $secretword);
			}
			print '<form method="post" action="' . $authnetemulateurl . '">';
			print whv('x_Version', '3.0') . whv('x_Login', $data1) . whv('x_Show_Form', 'PAYMENT_FORM');
			if($ppmethod==1) print whv('x_type', 'AUTH_ONLY');
			if(@$usefirstlastname){
				print whv('x_first_name', $ordName) . whv('x_last_name', $ordLastName);
			}elseif(strpos(trim($ordName), ' ')!==FALSE){
				$namearr=explode(' ',trim($ordName),2);
				print whv('x_first_name', $namearr[0]) . whv('x_last_name', $namearr[1]);
			}else
				print whv('x_last_name', $ordName);
			$sequence=$orderid;
			if(@$authnetadjust!='') $tstamp=time() + $authnetadjust; else $tstamp=time();
			$fingerprint=vrhmac2($data2, $data1 . "^" . $sequence . "^" . $tstamp . "^" . number_format($grandtotal,2,'.','') . "^");
			print whv('x_fp_sequence', $sequence) . whv('x_fp_timestamp', $tstamp) . whv('x_fp_hash', $fingerprint);
			print whv('x_address', $ordAddress . (trim($ordAddress2)!='' ? ', ' . $ordAddress2 : '')) . whv('x_city', $ordCity) . whv('x_country', $ordCountry);
			print whv('x_phone', $ordPhone) . whv('x_state', $ordState) . whv('x_zip', $ordZip);
			print whv('x_invoice_num', $orderid) . whv('x_email', $ordEmail) . whv('x_description', substr($descstr,0,255));
			if(@$_SESSION['clientID']!='') print whv('x_cust_id', $_SESSION['clientID']);
			if(trim($ordShipName)!='' || trim($ordShipLastName)!='' || trim($ordShipAddress)!=''){
				if(@$usefirstlastname){
					print whv('x_ship_to_first_name', $ordShipName) . whv('x_ship_to_last_name', $ordShipLastName);
				}elseif(strpos(trim($ordName), ' ')!==FALSE){
					$namearr=explode(' ',trim($ordShipName),2);
					print whv('x_ship_to_first_name', $namearr[0]) . whv('x_ship_to_last_name', $namearr[1]);
				}else
					print whv('x_ship_to_last_name', $ordShipName);
				print whv('x_ship_to_address', $ordShipAddress . (trim($ordShipAddress2)!='' ? ', ' . $ordShipAddress2 : '')) . whv('x_ship_to_city', $ordShipCity) . whv('x_ship_to_country', $ordShipCountry) . whv('x_ship_to_state', $ordShipState) . whv('x_ship_to_zip', $ordShipZip);
			}
			print whv('x_Amount', number_format($grandtotal,2,'.',''));
			print whv('x_Relay_Response', 'TRUE') . whv('x_Relay_URL', $storeurl.'vsadmin/'.$wpconfirmpage);
			if($demomode) print whv('x_Test_Request', 'TRUE');
		}elseif($grandtotal==0 || $ordPayProvider=="4"){ // Email
			print '<form method="post" name="checkoutform" action="thanks.php">' . whv('emailorder', $orderid) . whv('thesessionid', $thesessionid);
		}elseif($grandtotal>0 && $ordPayProvider=="17"){ // Email 2
			print '<form method="post" action="thanks.php">' . whv('secondemailorder', $orderid) . whv('thesessionid', $thesessionid);
		}elseif($grandtotal>0 && $ordPayProvider=="5"){ // WorldPay
			print '<form method="post" action="https://secure' . ($demomode ? '-test' : '') . '.worldpay.com/wcc/purchase">';
			print whv('instId', $data1) . whv('cartId', $orderid) . whv('amount', number_format($grandtotal,2,'.','')) . whv('currency', $countryCurrency);
			print whv('desc', substr($descstr,0,255));
			print whv('name', trim($ordName.' '.$ordLastName)) . whv('address', $ordAddress . (trim($ordAddress2)!='' ? ', ' . $ordAddress2 : '') . '&#10;' . $ordCity . '&#10;' . $ordState);
			print whv('postcode', $ordZip) . whv('country', $countryCode) . whv('tel', $ordPhone) . whv('email', $ordEmail);
			print whv('authMode', $ppmethod==1 ? 'E' : 'A') . whv('testMode', $demomode ? '100' : '0');
			$data2arr=explode('&',$data2);
			$data2=@$data2arr[0];
			if($data2!=''){
				$sigfields='amount:currency:cartId:testMode';
				print whv('signatureFields', $sigfields) . whv('signature', md5($data2 . ';' . $sigfields . ';' . number_format($grandtotal,2,'.','') . ';' . $countryCurrency . ';' . $orderid . ';' . ($demomode?'100':'0')));
			}
		}elseif($grandtotal>0 && $ordPayProvider=="6"){ // NOCHEX
			print '<form method="post" action="https://secure.nochex.com/">';
			print whv('merchant_id', $data1);
			print whv('success_url', $storeurl . 'thanks.php?ncretval=' . $orderid . '&ncsessid=' . $thesessionid) . whv('callback_url', $storeurl.'vsadmin/ncconfirm.php');
			print whv('description', substr($descstr,0,255));
			print whv('order_id', $orderid) . whv('amount', number_format($grandtotal,2,'.',''));
			print whv('billing_fullname', trim($ordName.' '.$ordLastName)) . whv('billing_address', $ordAddress . (trim($ordAddress2)!='' ? ', ' . $ordAddress2 : '')) . whv('billing_postcode', $ordZip) . whv('email_address', $ordEmail) . whv('customer_phone_number', $ordPhone);
			if(trim($ordShipName)!='' || trim($ordShipAddress)!=''){
				print whv('delivery_fullname', trim($ordShipName.' '.$ordShipLastName)) . whv('delivery_address', $ordShipAddress . (trim($ordShipAddress2)!='' ? ', ' . $ordShipAddress2 : '')) . whv('delivery_postcode', $ordShipZip);
			}
			if($demomode) print whv('test_transaction', '100');
		}elseif($grandtotal>0 && $ordPayProvider=="7"){ // Payflow Pro
			print '<form method="post" action="cart.php" onsubmit="return isvalidcard(this)">';
			print whv('mode', 'authorize') . whv('method', '7') . whv('ordernumber', $orderid);
		}elseif($grandtotal>0 && $ordPayProvider=="8"){ // Payflow Link
			if(strpos($data1,'&')!==FALSE){
				print '<form method="post" action="cart.php" onsubmit="return isvalidcard(this)">';
				print whv('mode', 'authorize') . whv('method', '8') . whv('ordernumber', $orderid);
			}else{
				$paymentlink='https://payflowlink.paypal.com';
				print '<form method="post" action="' . $paymentlink . '">';
				print whv('LOGIN', $data1) . whv('PARTNER', $data2) . whv('CUSTID', $orderid) . whv('AMOUNT', number_format($grandtotal,2,'.',''));
				print whv('TYPE',$ppmethod==1?'A':'S');
				print whv('DESCRIPTION', substr($descstr,0,255));
				print whv('NAME', trim($ordName.' '.$ordLastName)) . whv('ADDRESS', $ordAddress . (trim($ordAddress2)!='' ? ', ' . $ordAddress2 : '')) . whv('CITY', $ordCity) . whv('STATE', $ordState) . whv('ZIP', $ordZip) . whv('COUNTRY', ($countryCode=='US'?'USA':$ordCountry));
				print whv('EMAIL', $ordEmail) . whv('PHONE', $ordPhone);
				print whv('METHOD', 'CC') . whv('ORDERFORM', 'TRUE') . whv('SHOWCONFIRM', 'FALSE') . whv('BUTTONSOURCE', 'EcommerceTemplatesUS_Cart_PPA');
				if(trim($ordShipName)!='' || trim($ordShipAddress)!=''){
					print whv('NAMETOSHIP', trim($ordShipName.' '.$ordShipLastName)) . whv('ADDRESSTOSHIP', $ordShipAddress . (trim($ordShipAddress2)!='' ? ', ' . $ordShipAddress2 : '')) . whv('CITYTOSHIP', $ordShipCity) . whv('STATETOSHIP', $ordShipState) . whv('ZIPTOSHIP', $ordShipZip) . whv('COUNTRYTOSHIP', ($shipCountryCode=='US'?'USA':$ordShipCountry));
				}
			}
		}elseif($grandtotal>0 && $ordPayProvider=="9"){ // PayPoint.net
			print '<form method="post" action="https://www.secpay.com/java-bin/ValCard">';
			print whv('merchant', $data1) . whv('trans_id', $orderid) . whv('amount', number_format($grandtotal,2,'.',''));
			print whv('callback', $storeurl.'vsadmin/'.$wpconfirmpage) . whv('currency', $countryCurrency) . whv('cb_post', 'true');
			print whv('bill_name', trim($ordName.' '.$ordLastName)) . whv('bill_addr_1', $ordAddress) . whv('bill_addr_2', $ordAddress2) . whv('bill_city', $ordCity) . whv('bill_state', $ordState) . whv('bill_post_code', $ordZip) . whv('bill_country', $ordCountry) . whv('bill_email', $ordEmail) . whv('bill_tel', $ordPhone);
			if(trim($ordShipName)!='' || trim($ordShipLastName)!='' || trim($ordShipAddress)!=''){
				print whv('ship_name', trim($ordShipName.' '.$ordShipLastName)) . whv('ship_addr_1', $ordShipAddress) . whv('ship_addr_2', $ordShipAddress2) . whv('ship_city', $ordShipCity) . whv('ship_state', $ordShipState) . whv('ship_post_code', $ordShipZip) . whv('ship_country', $ordShipCountry);
			}
			$data2arr=explode('&',$data2);
			$data2md5=@$data2arr[0];
			$data2tpl=urldecode(@$data2arr[1]);
			if(trim($data2md5)!=''){
				print whv('digest', md5($orderid . number_format($grandtotal,2,'.','') . $data2md5));
				print whv('md_flds', 'trans_id:amount:callback');
			}
			print whv('mpi_description', substr($descstr,0,125));
			if(trim($data2tpl)!='') print whv('template', $data2tpl);
			if($ppmethod==1) print whv('deferred', 'reuse:5:5');
			if(@$requirecvv==TRUE) print whv('req_cv2', 'true');
			if($data3=='1') print whv('ssl_cb', 'true');
			if($demomode) print whv('options', 'test_status=true,dups=false');
		}elseif($grandtotal>0 && $ordPayProvider=='10'){ // Capture Card
			print 'DISABLED!!<br />';
		}elseif($grandtotal>0 && ($ordPayProvider=="11" || $ordPayProvider=="12")){ // PSiGate
			print '<form method="post" action="https://' . ($demomode ? 'dev' : 'checkout') . '.psigate.com/HTMLPost/HTMLMessenger"' . ($ordPayProvider=='12' ? ' onsubmit="return isvalidcard(this)"' : '') . '>';
			print whv('MerchantID', $data1) . whv('Oid', $orderid) . whv('FullTotal', number_format($grandtotal,2,'.','')) . whv('ThanksURL', $storeurl.'thanks.php') . whv('NoThanksURL', $storeurl.'thanks.php') . whv('CustomerRefNo', substr(md5($orderid.':'.@$secretword), 0, 24)) . whv('ChargeType', $ppmethod=='1' ? '1' : '0');
			if($ordPayProvider=='11') print whv('Bname', trim($ordName.' '.$ordLastName));
			print whv('Baddr1', $ordAddress) . whv('Baddr2', $ordAddress2) . whv('Bcity', $ordCity) . whv('IP', $REMOTE_ADDR) . whv('Bstate', $countryID==1 && $stateAbbrev!='' ? $stateAbbrev : $ordState) . whv('Bzip', $ordZip) . whv('Bcountry', $countryCode) . whv('Email', $ordEmail) . whv('Phone', $ordPhone);
			if(trim($ordShipName)!='' || trim($ordShipLastName)!='' || trim($ordShipAddress)!=''){
				print whv('Sname', trim($ordShipName.' '.$ordShipLastName)) . whv('Saddr1', $ordShipAddress) . whv('Saddr2', $ordShipAddress2) . whv('Scity', $ordShipCity) . whv('Sstate', $ordShipState) . whv('Szip', $ordShipZip) . whv('Scountry', $ordShipCountry);
			}
			if($demomode) print whv('Result', '1');
		}elseif($grandtotal>0 && $ordPayProvider=="13"){ // Authorize.net AIM
			print '<form method="post" action="cart.php" onsubmit="return isvalidcard(this)">';
			print whv('mode', 'authorize') . whv('method', '13') . whv('ordernumber', $orderid) . whv('description', substr($descstr,0,254));
		}elseif($grandtotal>0 && $ordPayProvider=="14"){ // Custom Pay Provider
			include './vsadmin/inc/customppsend.php';
		}elseif($grandtotal>0 && $ordPayProvider=='15'){ // Netbanx
			$sequence=rand(1000000,9999999);
			print '<form method="post" action="https://pay.netbanx.com/' . $data1 . '">';
			print whv('nbx_merchant_reference', $orderid.'.' . $sequence) . whv('nbx_payment_amount', (int)($grandtotal*100)) . whv('nbx_currency_code', $countryCurrency) . whv('nbx_cardholder_name', trim($ordName.' '.$ordLastName)) . whv('nbx_email', $ordEmail) . whv('nbx_postcode', $ordZip);
			print whv('nbx_return_url', $storeurl.'categories.php');
			print whv('nbx_success_url', $storeurl.'vsadmin/ncconfirm.php');
			if($data2!='') print whv('nbx_checksum', sha1((int)($grandtotal*100).$countryCurrency.$orderid.'.'.$sequence.$data2));
		}elseif($grandtotal>0 && $ordPayProvider=='16'){ // Linkpoint
			$lpsubtotal=round($totalgoods - $totaldiscounts, 2);
			$lpshipping=round(($shipping + $handling) - $freeshipamnt, 2);
			$lptax=round($stateTax + $countryTax, 2);
			$sequence='.'.time();
			if($data3!='')
				$payurl='https://connect.'.($demomode?'merchanttest.':'').'firstdataglobalgateway.com/IPGConnect/gateway/processing';
			else
				$payurl='https://www.'.($demomode?'staging.':'').'linkpointcentral.com/lpc/servlet/lppay';
			print '<form action="' . $payurl . '" method="post"' . ($data2=='1'?' onsubmit="return isvalidcard(this)"':'') . '>';
			print whv('storename', $data1) . whv('mode', 'payonly') . whv('ponumber', $orderid) . whv('oid', $orderid.$sequence) . whv('responseURL', $storeurl.'thanks.php');
			print whv('subtotal', number_format($lpsubtotal,2,'.','')) . whv('chargetotal', number_format($lpsubtotal+$lpshipping+$lptax,2,'.','')) . whv('shipping', number_format($lpshipping,2,'.','')) . whv('tax', number_format($lptax,2,'.',''));
			if($data2!='1') print whv('bname', trim($ordName.' '.$ordLastName));
			print whv('baddr1', $ordAddress) . whv('baddr2', $ordAddress2) . whv('bcity', $ordCity);
			if($countryID==1 && $stateAbbrev!='') print whv('bstate', $stateAbbrev); else print whv('bstate2', $ordState);
			print whv('bzip', $ordZip) . whv('bcountry', $countryCode) . whv('email', $ordEmail) . whv('phone', $ordPhone);
			print whv('txntype', $ppmethod==1?'preauth':'sale');
			if(trim($ordShipName)!='' || trim($ordShipLastName)!='' || trim($ordShipAddress)!=''){
				print whv('sname', trim($ordShipName.' '.$ordShipLastName)) . whv('saddr1', $ordShipAddress) . whv('saddr2', $ordShipAddress2) . whv('scity', $ordShipCity) . whv('sstate', $ordShipState) . whv('szip', $ordShipZip) . whv('scountry', $shipCountryCode);
			}
			if($data3!=''){
				$formattedDate=gmdate('Y:m:d-H:i:s');
				$str=$data1 . $formattedDate . number_format($lpsubtotal+$lpshipping+$lptax,2,'.','') . $data3;
				$hex_str='';
				for($i=0; $i < strlen($str); $i++){
					$hex_str.=dechex(ord($str[$i]));
				}
				print whv('txndatetime' ,$formattedDate);
				ect_query("UPDATE orders SET ordPrivateStatus='" . escape_string($formattedDate) . "' WHERE ordID='" . escape_string($orderid) . "'") or ect_error();
				print whv('timezone', 'UTC');
				print whv('hash', hash('sha256', $hex_str));
			}
		}elseif($grandtotal>0 && $ordPayProvider=="18"){ // PayPal Direct Payment
			$php_version=explode('.', phpversion());
			if(((int)$php_version[0])<5) print 'Please note, you are using PHP 4 and PHP 5 is required for PayPal.';
			print '<form method="post" action="cart.php" onsubmit="return isvalidcard(this)">';
			print whv('mode', 'authorize') . whv('method', '18') . whv('ordernumber', $orderid) . whv('description', substr($descstr,0,254));
		}elseif($grandtotal>0 && $ordPayProvider=="19"){ // PayPal Express Payment
			$php_version=explode('.', phpversion());
			if(((int)$php_version[0])<5) print 'Please note, you are using PHP 4 and PHP 5 is required for PayPal.';
			print '<form method="post" action="thanks.php" onsubmit="return docheckform(this)">';
			print whv('token', $token) . whv('method', 'paypalexpress') . whv('ordernumber', $orderid) . whv('payerid', $payerid) . whv('email', $ordEmail);
		}elseif($grandtotal>0 && $ordPayProvider=='21'){ // Amazon Pay
			print '<form method="post" action="thanks.php" onsubmit="return docheckform(this)">';
			print whv('pprov', '21') . whv('ordernumber', $orderid) . whv('amzrefid', $amzrefid_);
			$sSQL="UPDATE orders SET ordTransID='" . escape_string($amzrefid_) . "' WHERE ordID='" . escape_string($orderid) . "' AND ordSessionID='" . escape_string($thesessionid) . "'";
			if($amazonpayment) ect_query($sSQL) or ect_error();
		}elseif($grandtotal>0 && $ordPayProvider=='22'){ // PayPal Advanced
			print '<form method="post" action="cart.php">';
			print whv('mode', 'authorize') . whv('method', '22') . whv('ordernumber', $orderid) . whv('sessionid', $thesessionid);
		}elseif($grandtotal>0 && $ordPayProvider=='23'){ // Stripe.com
			print '<form method="post" action="thanks.php">';
			print whv('pprov', '23') . whv('ordernumber', $orderid);
		}elseif($grandtotal>0 && $ordPayProvider=='24'){ // SagePay
			$twoletterstateerr="Please Enter a 2 Letter State Code for US States";
			function spchkalphanum($ectinstr){
				$ectinstr=preg_replace('/[^a-zA-Z0-9]/', '', $ectinstr);
				return($ectinstr);
			}
			function randomise(){
				list($usec, $sec)=explode(' ', microtime());
				return (float) $sec + ((float) $usec * 100000);
			}
			if(trim(@$ordShipAddress)==''){
				$ordShipName=@$ordName;
				$ordShipLastName=@$ordLastName;
				$ordShipAddress=@$ordAddress;
				$ordShipAddress2=@$ordAddress2;
				$ordShipCity=@$ordCity;
				$ordShipState=@$ordState;
				$ordShipZip=@$ordZip;
				$ordShipCountry=@$ordCountry;
				$ordShipPhone=@$ordPhone;
				$ordShipExtra1=@$ordExtra1;
				$ordShipExtra2=@$ordExtra2;
				$shipStateAbbrev=@$stateAbbrev;
			}
			srand(time());
			$randid=rand(1000000,9999999);
			$sSQL='SELECT cartID,cartProdID,cartProdName,cartProdPrice,cartQuantity FROM cart WHERE cartCompleted=0 AND ' . getsessionsql();
			$result=ect_query($sSQL) or ect_error();
			$index=0;
			$thecart='';
			while($rs=ect_fetch_assoc($result)){
				$sSQL='SELECT SUM(coPriceDiff) as totpricediff FROM cartoptions WHERE coCartID=' . $rs['cartID'];
				$result2=ect_query($sSQL) or ect_error();
				$rs2=ect_fetch_assoc($result2);
				$totpricediff=(double)$rs2['totpricediff'];
				$thecart .= ':[' . str_replace(array(':','&'),'',$rs['cartProdID']) . ']' . strip_tags(str_replace(array(':','&'),'',$rs['cartProdName'])) . ':' . $rs['cartQuantity'] . ':' . number_format($rs['cartProdPrice']+$totpricediff,2,'.','') . ':0.00:' . number_format($rs['cartProdPrice']+$totpricediff,2,'.','') . ':' . number_format(($rs['cartProdPrice']+$totpricediff)*$rs['cartQuantity'],2,'.','');
				$index++;
			}
			if($stateTax + $countryTax > 0){
				$thecart.=':Taxes:---:---:---:---:' . number_format($stateTax + $countryTax,2,'.','');
				$index++;
			}
			if($totaldiscounts > 0){
				$thecart.=':Discounts:---:---:---:---:' . number_format(0 - $totaldiscounts,2,'.','');
				$index++;
			}
			if(($shipping + $handling) - $freeshipamnt){
				$thecart.=':Delivery:---:---:---:---:' . number_format(($shipping + $handling) - $freeshipamnt,2,'.','');
				$index++;
			}
			$thecart=str_replace('&pound;','�',$thecart);
			$thecart=($index.$thecart);
			$spzipcode=trim($ordZip);
			if($spzipcode=='') $spzipcode='NA';
			$spshipzipcode=trim($ordShipZip);
			if($spshipzipcode=='') $spshipzipcode='NA';
			$stuff='VendorTxCode=' . $orderid . '-' . $randid;
			$stuff.='&Amount=' . number_format($grandtotal,2,'.','') . '&Currency=' . $countryCurrency . '&Description=' . substr($descstr, 0, 99);
			$stuff.='&Basket=' . $thecart . '&SuccessURL=' . $storeurl . 'thanks.php&FailureURL=' . $storeurl . 'thanks.php';
			$stuff.=(@$GLOBALS['nosagepayemail']?'':'&CustomerEMail=' . $ordEmail) . '&CustomerName=' . trim($ordName.' '.@$ordLastName);
			$stuff.='&ContactNumber=' . spchkalphanum($ordPhone) . (@$GLOBALS['nosagepayemail']?'':'&VendorEMail=' . $emailAddr);
			if(@$usefirstlastname){
				$stuff.='&BillingFirstnames=' . $ordName . '&BillingSurname=' . $ordLastName;
			}elseif(strpos(trim($ordName), ' ')!==FALSE){
				$namearr=explode(' ',trim($ordName),2);
				$stuff.='&BillingFirstnames=' . $namearr[0] . '&BillingSurname=' . $namearr[1];
			}else
				$stuff.='&BillingFirstnames=&BillingSurname=' . $ordName;
			$stuff.='&BillingAddress1=' . $ordAddress;
			if(trim($ordAddress2)!='') $stuff.='&BillingAddress2=' . $ordAddress2;
			$stuff.='&BillingCity=' . $ordCity;
			if($countryID==1){
				$spstatefield=strtoupper($stateAbbrev!=''?$stateAbbrev:$ordState);
				if(strlen($spstatefield)!=2){
					$success=FALSE;
					$errormsg=$twoletterstateerr . '<br /><div style="text-align:center"><input type="button" onclick="history.go(-1)" value="'.$xxGoBack.'" /></div>';
				}
				$stuff.='&BillingState=' . $spstatefield;
			}
			$stuff.='&BillingCountry=' . $countryCode . '&BillingPostCode=' . $spzipcode . '&BillingPhone=' . spchkalphanum($ordPhone);
			if(@$usefirstlastname&&$ordShipLastName!=''){
				$stuff.='&DeliveryFirstnames=' . $ordShipName . '&DeliverySurname=' . $ordShipLastName;
			}elseif(strpos(trim($ordShipName), ' ')!==FALSE){
				$namearr=explode(' ',trim($ordShipName),2);
				$stuff.='&DeliveryFirstnames=' . $namearr[0] . '&DeliverySurname=' . $namearr[1];
			}else
				$stuff.='&DeliveryFirstnames=' . $ordShipName . '&DeliverySurname=' . $ordShipName;
			$stuff.='&DeliveryAddress1=' . $ordShipAddress;
			if(trim($ordShipAddress2)!='') $stuff.='&DeliveryAddress2=' . $ordShipAddress2;
			$stuff.='&DeliveryCity=' . $ordShipCity;
			if($shipCountryID==1){
				$spstatefield=strtoupper($shipStateAbbrev!=''?$shipStateAbbrev:$ordShipState);
				if(strlen($spstatefield)!=2){
					$success=FALSE;
					$errormsg=$twoletterstateerr . '<br /><div style="text-align:center"><input type="button" onclick="history.go(-1)" value="'.$xxGoBack.'" /></div>';
				}
				$stuff.='&DeliveryState=' . $spstatefield;
			}
			$stuff.='&DeliveryCountry=' . $shipCountryCode . '&DeliveryPostCode=' . $spshipzipcode . '&DeliveryPhone=' . spchkalphanum($ordShipPhone);
			$stuff.='&ReferrerID=7B0AD331-0388-44EA-BE3A-D05D3FB9FE28';
			function addPKCS5Padding($input){
				$blockSize=16;
				$padd='';
				$length=$blockSize - (strlen($input) % $blockSize);
				for ($i=1; $i <= $length; $i++){
					$padd .= chr($length);
				}
				return $input . $padd;
			}
			function encryptAes($string, $key){
				$string=addPKCS5Padding($string);
				$crypt=mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $string, MCRYPT_MODE_CBC, $key);
				return '@' . strtoupper(bin2hex($crypt));
			}
			$crypt=encryptAes($stuff,$data2);
			print '<form action="https://' . ($demomode?'test':'live') . '.sagepay.com/gateway/service/vspform-register.vsp" method="post" id="form1">' . "\r\n";
			print whv('VPSProtocol','3.00') . whv('TxType',$ppmethod==1?'DEFERRED':'PAYMENT') . whv('Vendor',$data1) . whv('Crypt',$crypt);
		}
		eval('$payprovextraparams=@$payprovextraparams' . $ordPayProvider . ';');
		print $payprovextraparams;
	}
	if(!$returntocustomerdetails&&!@$GLOBALS['nopriceanywhere']){
		if(@$GLOBALS['xxCoStp3']!='') print '<div class="checkoutsteps">' . $GLOBALS['xxCoStp3'] . '</div>'; ?>
			<div class="cart3details">
			  <div class="cobhl cart3header cartheader"><?php print $GLOBALS['xxChkCmp']?></div>
<?php	if(($rgcpncode!='' || ($ordPayProvider=='19' && getget('token')!='')) && (! $gotcpncode || @$cpnerror!='') && @$nogiftcertificate!=TRUE){ ?>
			  <div>
			    <div class="cobhl cobhl3"><?php if($rgcpncode!='' && $ordPayProvider=='19' && ! $gotcpncode) print '<span style="color:#FF0000">' . $GLOBALS['xxCpnNoF'] . '</span>'; else print labeltxt($GLOBALS['xxGifCer'],'cpncode').':'?></div>
				<div class="cobll cobll3" style="font-size:10px"><?php
			if($ordPayProvider=='19' && ! $gotcpncode && getget('token')!=''){
				print '<input type="text" name="cpncode" id="cpncode" size="20" value="' . htmlspecials($rgcpncode) . '" /> <input type="button" value="' . $GLOBALS['xxAppCpn'] . '" onclick="document.location=\'cart.php?token='.getget('token').'&cpncode=\'+document.getElementById(\'cpncode\').value" />';
			}else{
				print $cpnerror;
				if($rgcpncode!='' && ! $gotcpncode) printf($GLOBALS['xxNoGfCr'],$rgcpncode,1);
			} ?></div>
			  </div>
<?php	}
		if($backorder){ ?>
			  <div class="cart3row">
				<div class="cobll cart2column ectwarning"><?php print $GLOBALS['xxBakOrW']?></div>
			  </div>
<?php	}
		if(($warncheckspamfolder==TRUE || getpost('warncheckspamfolder')=='true') && @$noconfirmationemail!=TRUE){ ?>
			  <div class="cart3row">
			    <div class="cobhl cobhl3"><?php print $GLOBALS['xxThkSub']?></div>
				<div class="cobll cobll3 ectwarning"><?php print $GLOBALS['xxSpmWrn']?></div>
			  </div>
<?php	}
		if($cpnmessage!=''){ ?>
			  <div class="cart3row">
			    <div class="cobhl cobhl3"><?php print $GLOBALS['xxAppDs']?></div>
				<div class="cobll cobll3"><?php print $cpnmessage?></div>
			  </div>
<?php	} ?>
			  <div class="cart3row">
			    <div class="cobhl cobhl3 cart3totgoodst"><?php print $GLOBALS['xxTotGds']?></strong></div>
				<div class="cobll cobll3 cart3totgoods"><?php print FormatEuroCurrency($totalgoods)?>
<script type="text/javascript">/* <![CDATA[ */
function updateshiprate(obj,theselector){
	if(obj.value!=''){
		document.getElementById("shipselectoridx").value=theselector;
		document.getElementById("shipselectoraction").value="selector";
		document.forms.shipform.submit();
	}
}
function selaltrate(id){
	document.getElementById('altrates').value=id;
	document.getElementById('shipselectoraction').value='altrates';
	document.forms.shipform.submit();
}
function setchangeflag(tisset,tname){
	if(tname=='w')
		document.getElementById('willpickup').value=tisset?'Y':'';
	else
		document.getElementById('changeaction').value=tname+(tisset?'y':'n');
	document.getElementById('shipselectoraction').value='altrates';
	document.forms.shipform.submit();
}
<?php	if(@$closeorderimmediately){
		$_SESSION['sessionid']=$thesessionid; ?>
function docloseorder(){
	ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
	ajaxobj.open("GET", "vsadmin/ajaxservice.php?action=clord", false);
	ajaxobj.send(null);
}
<?php	}
	if(@$adminAltRates==2){
		$sSQL='SELECT altrateid FROM alternaterates WHERE (usealtmethod'.$international.'<>0 OR altrateid=' . ($international==''||$adminIntShipping==0?$adminShipping:$adminIntShipping) . ') AND altrateid<>'.$shipType.' ORDER BY altrateorder,altrateid';
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result)>0){
			print 'var extraship=[';
			$addcomma='';
			while($rs=ect_fetch_assoc($result)){
				print $addcomma . $rs['altrateid'];
				$addcomma=',';
			}
			print "];\r\n" ?>
function acajaxcallback(){
	if(ajaxobj.readyState==4){
		var restxt=ajaxobj.responseText;
		var gssr=restxt.split('SHIPSELPARAM=');
		document.getElementById('shipoptionstable').innerHTML+='<div class="shiptableline"><div class="shiptablelogo">'+decodeURIComponent(gssr[1])+'</div><div class="shiptablerates">'+gssr[0]+'</div></div>';
		if(decodeURIComponent(gssr[2])!='ERROR')
			document.getElementById('numshiprate').value=gssr[4];
		getalternatecarriers();
	}
}
function getalternatecarriers(){
	if(extraship.length>0){
		var shiptype=extraship.shift();
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange=acajaxcallback;
		ajaxobj.open("GET", "vsadmin/shipservice.php?shiptype="+shiptype+"&numshiprate="+document.getElementById('numshiprate').value+"&sessionid=<?php print urlencode($thesessionid)?>&destzip=<?php print urlencode($destZip)?>&sc=<?php print urlencode($shipcountry)?>&scc=<?php print urlencode($shipCountryCode)?>&sta=<?php print urlencode($shipStateAbbrev)?>&orderid=<?php print $orderid?>", true);
		ajaxobj.send(null);
	}
}
<?php	}
		ect_free_result($result);
	} ?>
function docheckform(){
<?php	if(@$GLOBALS['termsandconditions']&&($ordPayProvider=='19'||$ordPayProvider=='21')){ ?>
if(document.getElementById('termsandconds').checked==false){
	alert("<?php print jscheck($GLOBALS['xxPlsProc'])?>");
	document.getElementById('termsandconds').focus();
	return(false);
}
<?php	} ?>
return(true);
}
/* ]]> */</script>
				</div>
			  </div>
<?php	if($shipType==0) $combineshippinghandling=FALSE;
		if($shipType!=0){
			if($currShipType=='') $currShipType=$shipType;
			$doshowlogo=(! (($shipType==1||$shipType==2||$shipType==5)&&@$shippinglogo==''&&$adminAltRates==0));
?>			  <div class="cart3row">
			    <div class="cobhl cobhl3 cart3shippingt"><?php print (@$combineshippinghandling ? $GLOBALS['xxShipHa'] : $GLOBALS['xxShippg'])?></div>
				<div class="cobll cobll3 cart3shipping"><?php
			print '<div class="shipoptionstable" id="shipoptionstable"><div class="shiptableline"><div class="shiptablelogo">' . getshiplogo($currShipType) . '</div><div class="shiptablerates">';
			if(! $success){
				print '<span style="color:#FF0000">' . $errormsg . '</span>';
			}else{
				if($shipType!=0 || ($shipping-$freeshipamnt)!=0 || $willpickup_){
					if(! $multipleoptions) print FormatEuroCurrency(((double)$shipping+(@$combineshippinghandling ? $handling : 0))-$freeshipamnt) . ($shipMethod!='' ? ' - ' . $shipMethod : ''); else showshippingselect();
				}
			}
			print '</div></div></div>'; ?>
				</div>
			  </div>
<?php	}
		if($success && $handling!=0 && @$combineshippinghandling!=TRUE){ ?>
			  <div class="cart3row">
			    <div class="cobhl cobhl3 cart3handlingt"><?php print $GLOBALS['xxHndlg']?></div>
				<div class="cobll cobll3 cart3handling"><?php print FormatEuroCurrency($handling)?></div>
			  </div>
<?php	}
		if($adminAltRates==1){
			$sSQL='SELECT altrateid,altratename,'.getlangid('altratetext',65536).',usealtmethod,usealtmethodintl FROM alternaterates WHERE usealtmethod'.$international.'<>0 OR altrateid='.($international==''||$adminIntShipping==0?$adminShipping:$adminIntShipping).' ORDER BY altrateorder,altrateid';
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result)>0){ ?>
			  <div class="cart3row">
			    <div class="cobhl cobhl3"><?php print $GLOBALS['xxOrCom']?></div>
				<div class="cobll cobll3"><?php
				if(@$shippingoptionsasradios!=TRUE) print '<select id="altratesselect" size="1" onchange="selaltrate(this[this.selectedIndex].value)">';
				while($rs=ect_fetch_assoc($result)){
					writealtshipline($rs[getlangid('altratetext',65536)],$rs['altrateid'],'','',TRUE);
				}
				if(@$shippingoptionsasradios!=TRUE) print '</select>';
?>				</div>
			  </div>
<?php		}
			ect_free_result($result);
		}
		writeshippingflags(3);
		if($totaldiscounts!=0&&(($totalgoods+$shipping+$handling)-($totaldiscounts+$freeshipamnt))>=0 && $GLOBALS['showtaxinclusive']!=3){ ?>
			  <div class="cart3row">
			    <div class="cobhl cobhl3 ectdscntt"><?php print $GLOBALS['xxTotDs']?></div>
				<div class="cobll cobll3 ectdscnt"><?php print FormatEuroCurrency($totaldiscounts)?></div>
			  </div>
			  <div class="cart3row">
			    <div class="cobhl cobhl3"><?php print $GLOBALS['xxSubTot']?></div>
				<div class="cobll cobll3 cart3sustot"><?php print FormatEuroCurrency(($totalgoods+$shipping+$handling)-($totaldiscounts+$freeshipamnt))?></div>
			  </div>
<?php	}
		if($usehst){ ?>
			  <div class="cart3row">
			    <div class="cobhl cobhl3"><?php print $GLOBALS['xxHST']?></div>
				<div class="cobll cobll3"><?php print FormatEuroCurrency($stateTax+$countryTax)?></div>
			  </div>
<?php	}else{
			if($stateTax!=0.0){ ?>
			  <div class="cart3row">
			    <div class="cobhl cobhl3"><?php print $GLOBALS['xxStaTax']?></div>
				<div class="cobll cobll3"><?php print FormatEuroCurrency($stateTax)?></div>
			  </div>
<?php		}
			if($countryTax!=0.0){ ?>
			  <div class="cart3row">
			    <div class="cobhl cobhl3"><?php print $GLOBALS['xxCntTax']?></div>
				<div class="cobll cobll3"><?php print FormatEuroCurrency($countryTax)?></div>
			  </div>
<?php		}
		}
		if($totaldiscounts!=0&&((($totalgoods+$shipping+$handling)-($totaldiscounts+$freeshipamnt))<0 || $GLOBALS['showtaxinclusive']==3)){ ?>
			  <div class="cart3row">
			    <div class="cobhl cobhl3 ectdscntt"><?php print $GLOBALS['xxTotDs']?></div>
				<div class="cobll cobll3 ectdscnt"><?php print FormatEuroCurrency($totaldiscounts)?></span></div>
			  </div>
<?php	}?>
			  <div class="cart3row">
			    <div class="cobhl cobhl3 cart3gndtot"><?php print $GLOBALS['xxGndTot']?></div>
				<div class="cobll cobll3 cart3gndtott"><?php if(! $success) print '-'; else print FormatEuroCurrency($grandtotal)?></div>
			  </div>
<?php	if(! ($ordPayProvider=='7' || $ordPayProvider=='13' || $ordPayProvider=='18')) $cardinalprocessor='';
		if($success && $grandtotal>0 && ($ordPayProvider=='7' || $ordPayProvider=='10' || $ordPayProvider=='12' || $ordPayProvider=='13' || ($ordPayProvider=='14' && @$customppacceptcc) || ($ordPayProvider=='16' && $data2=='1') || $ordPayProvider=='18')){ // Payflow Pro || PSiGate || Auth.NET AIM || PayPal Pro
			if($ordPayProvider!='10'&&!(@$customppdefinecardtypes&&$ordPayProvider=='14')) $data1='XXXXXXX0XXXXXXXXXXXXXXXXX';
			if($ordPayProvider!='10'&&($origCountryCode=='GB'||$origCountryCode=='IE')&&!(@$customppdefinecardtypes&&$ordPayProvider=='14')) $data1='XXXXXXXXXXXXXXXXXXXXXXXXX';
			$isPSiGate=($ordPayProvider=='12');
			$isLinkpoint=($ordPayProvider=='16');
			if($isPSiGate){
				$sscardname='Bname';
				$sscardnum='CardNumber';
				$ssexmon='CardExpMonth';
				$ssexyear='CardExpYear';
				$sscvv2='CardIDNumber';
			}elseif($isLinkpoint){
				$sscardname='bname';
				$sscardnum='cardnumber';
				$ssexmon='expmonth';
				$ssexyear='expyear';
				$sscvv2='cvm';
			}else{
				$sscardname='cardname';
				$sscardnum='ACCT';
				$ssexmon='EXMON';
				$ssexyear='EXYEAR';
				$sscvv2='CVV2';
			}
			$acceptecheck=((@$acceptecheck==TRUE) && ($ordPayProvider=='13' || (@$customppacceptecheck && $ordPayProvider=='14')));
?>
<input type="hidden" name="sessionid" value="<?php print $thesessionid?>" />
<script type="text/javascript">/* <![CDATA[ */
var isswitchcard=false;
function clearcc(){
	document.getElementById("<?php print $sscardnum?>").value="";
	document.getElementById("<?php print $sscvv2?>").value="";
	document.getElementById("<?php print $ssexmon?>").selectedIndex=0;
	document.getElementById("<?php print $ssexyear?>").selectedIndex=0;
}
function donecc(){
	return true;
}
if(window.addEventListener){
	window.addEventListener("load", clearcc, false);
	window.addEventListener("unload",donecc,false);
}else if(window.attachEvent){
	window.attachEvent("onload", clearcc);
}
function isCreditCard(st){
  if(st.length>19)return(false);
  sum=0; mul=1; l=st.length;
  for(i=0; i < l; i++){
	digit=st.substring(l-i-1,l-i);
	tproduct=parseInt(digit ,10)*mul;
	if(tproduct>=10)
		sum+=(tproduct % 10) + 1;
	else
		sum+=tproduct;
	if(mul==1)mul++;else mul--;
  }
  return((sum % 10)==0);
}
function isVisa(cc){
  if(((cc.length==16) || (cc.length==13)) && (cc.substr(0,1)==4))
	return isCreditCard(cc);
  return false;
}
function isMasterCard(cc){
  firstdig=cc.substr(0,1);
  seconddig=cc.substr(1,1);
  if((cc.length==16) && (firstdig==5) && ((seconddig>=1) && (seconddig <= 5)))
	return isCreditCard(cc);
  return false;
}
function isAmericanExpress(cc){
  firstdig=cc.substr(0,1);
  seconddig=cc.substr(1,1);
  if(cc.length==15 && firstdig==3 && (seconddig==4 || seconddig==7))
	return isCreditCard(cc);
  return false;
}
function isDinersClub(cc){
  firstdig=cc.substr(0,1);
  seconddig=cc.substr(1,1);
  if(cc.length==14 && firstdig==3 && (seconddig==0 || seconddig==6 || seconddig==8))
	return isCreditCard(cc);
  return false;
}
function isDiscover(cc){
  first4digs=cc.substr(0,4);
  if(cc.length==16 && (first4digs=="6011" || cc.substr(0,3)=="622" || cc.substr(0,2)=="64" || cc.substr(0,2)=="65"))
	return isCreditCard(cc);
  return false;
}
function isAusBankcard(cc){
  first4digs=cc.substr(0,4);
  if(cc.length==16 && (first4digs=="5610"||first4digs=="5602"))
	return isCreditCard(cc);
  return false;
}
function isEnRoute(cc){
  first4digs=cc.substr(0,4);
  if(cc.length==15 && (first4digs=="2014" || first4digs=="2149"))
	return isCreditCard(cc);
  return false;
}
function isJCB(cc){
  first4digs=cc.substr(0,4);
  if(cc.length==16 && (first4digs=="3088" || first4digs=="3096" || first4digs=="3112" || first4digs=="3158" || first4digs=="3337" || first4digs=="3528" || first4digs=="3589"))
	return isCreditCard(cc);
  return false;
}
function isSwitch(cc){
  first4digs=cc.substr(0,4);
  if((cc.length>=16 && cc.length<=19) && (first4digs=="4903" || first4digs=="4911" || first4digs=="4936" || first4digs=="5018" || first4digs=="5020" || first4digs=="5038" || first4digs=="5641" || first4digs=="6304" || first4digs=="6333" || first4digs=="6334" || first4digs=="6759" || first4digs=="6761" || first4digs=="6763" || first4digs=="6767")){
	isswitchcard=true;
	return(isCreditCard(cc));
  }
  return false;
}
function isLaser(cc){
  first4digs=cc.substr(0,4);
  if((cc.length>=16 && cc.length<=19) && (first4digs=="6304" || first4digs=="6706" || first4digs=="6771" || first4digs=="6709"))
	return(isCreditCard(cc));
  return false;
}
function isvalidcard(theForm){
  cc=theForm.<?php print $sscardnum?>.value;
  newcode="";
  var l=cc.length;
  for(i=0;i<l;i++){
	digit=cc.substring(i,i+1);
	digit=parseInt(digit ,10);
	if(!isNaN(digit)) newcode+=digit;
  }
  cc=newcode;
  if(theForm.<?php print $sscardname?>.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr']) . ' \"' . jscheck($GLOBALS['xxCCName']) . '\"' ?>");
	theForm.<?php print $sscardname?>.focus();
	return false;
  }
<?php if($acceptecheck==true){ ?>
if(cc!="" && theForm.accountnum.value!=""){
alert("Please enter either Credit Card OR ECheck details");
return(false);
}else if(theForm.accountnum.value!=""){
  if(theForm.accountname.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'])?> \"Account Name\".");
	theForm.accountname.focus();
	return false;
  }
  if(theForm.bankname.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'])?> \"Bank Name\".");
	theForm.bankname.focus();
	return false;
  }
  if(theForm.routenumber.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'])?> \"Routing Number\".");
	theForm.routenumber.focus();
	return false;
  }
  if(theForm.accounttype.selectedIndex==0){
	alert("Please select your account type: (Checking / Savings).");
	theForm.accounttype.focus();
	return false;
  }
<?php	if(@$wellsfargo==TRUE){ ?>
  if(theForm.orgtype.selectedIndex==0){
	alert("Please select your account type: (Personal / Business).");
	theForm.orgtype.focus();
	return false;
  }
  if(theForm.taxid.value=="" && theForm.licensenumber.value==""){
	alert("Please enter either a Tax ID number or Drivers License Details.");
	theForm.taxid.focus();
	return false;
  }
  if(theForm.taxid.value==""){
	if(theForm.licensestate.selectedIndex==0){
		alert("Please select your Drivers License State.");
		theForm.licensestate.focus();
		return false;
	}
	if(theForm.dldobmon.selectedIndex==0){
		alert("Please select your Drivers License D.O.B. Month.");
		theForm.dldobmon.focus();
		return false;
	}
	if(theForm.dldobday.selectedIndex==0){
		alert("Please select your Drivers License D.O.B. Day.");
		theForm.dldobday.focus();
		return false;
	}
	if(theForm.dldobyear.selectedIndex==0){
		alert("Please select your Drivers License D.O.B. year.");
		theForm.dldobyear.focus();
		return false;
	}
  }
<?php	} ?>
}else{
<?php } ?>
  if(true <?php
		if(substr($data1,7,1)=='X') print '&& !isSwitch(cc) ';
		if(substr($data1,0,1)=='X') print '&& !isVisa(cc) ';
		if(substr($data1,1,1)=='X') print '&& !isMasterCard(cc) ';
		if(substr($data1,2,1)=='X') print '&& !isAmericanExpress(cc) ';
		if(substr($data1,3,1)=='X') print '&& !isDinersClub(cc) ';
		if(substr($data1,4,1)=='X') print '&& !isDiscover(cc) ';
		if(substr($data1,5,1)=='X') print '&& !isEnRoute(cc) ';
		if(substr($data1,6,1)=='X') print '&& !isJCB(cc) ';
		if(substr($data1,8,1)=='X') print '&& !isAusBankcard(cc) ';
		if(substr($data1,9,1)=='X') print '&& !isLaser(cc) '; ?>){
	<?php if($acceptecheck==TRUE) $GLOBALS['xxValCC']='Please enter a valid credit card number or bank account details if paying by ECheck.'; ?>
	alert("<?php print jscheck($GLOBALS['xxValCC'])?>");
	theForm.<?php print $sscardnum?>.focus();
	return false;
  }
  if(theForm.<?php print $ssexmon?>.selectedIndex==0){
	alert("<?php print jscheck($GLOBALS['xxCCMon'])?>");
	theForm.<?php print $ssexmon?>.focus();
	return false;
  }
  if(theForm.<?php print $ssexyear?>.selectedIndex==0){
	alert("<?php print jscheck($GLOBALS['xxCCYear'])?>");
	theForm.<?php print $ssexyear?>.focus();
	return false;
  }
<?php if(substr($data1,7,1)=="X"){ ?>
	theForm.IssNum.value=theForm.IssNum.value.replace(/[^0-9]/g, '');
  if(theForm.IssNum.value=="" && isswitchcard){
	alert("Please enter an issue number / start date for Maestro/Solo cards.");
	theForm.IssNum.focus();
	return false;
  }
<?php }
	  if(@$requirecvv==TRUE){ ?>
  if(theForm.<?php print $sscvv2?>.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr']) . ' \"' . jscheck($GLOBALS['xx34code']) . '\"'?>");
	theForm.<?php print $sscvv2?>.focus();
	return false;
  }
<?php }
	  if(@$acceptecheck==TRUE) print '}'; ?>
	theForm.<?php print $sscardnum?>.value=cc;
	return true;
}
<?php if(@$cardinalprocessor!='' && @$cardinalmerchant!='' && @$cardinalpwd!=''){ ?>
vbvtext='<html><head><title>Verified by Visa</title><style type="text/css">body {font-family: verdana,sans-serif;font-size:10pt;}</style></head><body><p><h3><?php print str_replace("'","\'",$GLOBALS['xxVBV1'])?></h3></p><p><?php print str_replace("'","\'",$GLOBALS['xxVBV2'])?><img src="images/vbv_logo.gif" border="0" style="float:<?php print $tright?>;margin:4px;" /></p><p><?php print str_replace("'","\'",$GLOBALS['xxVBV3'])?></p><p><?php print str_replace("'","\'",$GLOBALS['xxVBV4'])?></p><p><?php print str_replace("'","\'",$GLOBALS['xxVBV5'])?></p><p align="center"><input type="button" value="<?php print str_replace("'","\'",$GLOBALS['xxClsWin'])?>" onclick="window.close()"></p></body></html>';
<?php } ?>
/* ]]> */</script>
<?php		if(@$_SERVER['HTTPS']!='on' && (@$_SERVER['SERVER_PORT']!='443') && @$nochecksslserver!=TRUE){ ?>
			  <div>
			    <div class="cobhl cart2column ectwarning">This site may not be secure. Do not enter real Credit Card numbers.</div>
			  </div>
<?php		} ?>
			  <div><div class="cobhl cart4header cartheader"><?php print $GLOBALS['xxCCDets']?></div></div>
			  <div class="cart4row">
			    <div class="cobhl cobhl4"><?php print labeltxt($GLOBALS['xxCCName'],$sscardname)?></div>
				<div class="cobll cobll4"><input type="text" name="<?php print $sscardname?>" id="<?php print $sscardname?>" size="21" value="<?php print trim($ordName.' '.$ordLastName)?>" AUTOCOMPLETE="off" /></div>
			  </div>
			  <div class="cart4row">
			    <div class="cobhl cobhl4"><?php print labeltxt($GLOBALS['xxCrdNum'],$sscardnum)?></div>
				<div class="cobll cobll4"><input type="text" name="<?php print $sscardnum?>" id="<?php print $sscardnum?>" size="21" AUTOCOMPLETE="off" />
<?php			if(@$cardinalprocessor!='' && @$cardinalmerchant!='' && @$cardinalpwd!=''){ ?>
				<span class="verifiedbyvisa"><a href="" onclick="newwin=window.open('','LearnMore','10,10,width=551,height=380,scrollbars=yes,resizable=yes');newwin.document.open();newwin.document.write(vbvtext);newwin.document.close();return false;"><img src="images/vbv_learn_more.gif" alt="Verified by Visa Learn More" border="0" style="vertical-align:middle" /></a><a href="" onclick="window.open('http://www.mastercardbusiness.com/mcbiz/index.jsp?template=/orphans&content=securecodepopup','LearnMore','10,10,width=551,height=380,scrollbars=yes,resizable=yes');return false;"><img src="images/mcsc_learn_more.gif" alt="MasterCard SecureCode Learn More" border="0" style="vertical-align:middle" /></a></span>
<?php			} ?>
				</div>
			  </div>
			  <div class="cart4row">
			    <div class="cobhl cobhl4"><?php print $GLOBALS['xxExpEnd']?></div>
				<div class="cobll cobll4">
				  <select name="<?php print $ssexmon?>" id="<?php print $ssexmon?>" size="1">
					<option value=""><?php print $GLOBALS['xxMonth']?></option>
					<?php	for($index=1; $index<=12; $index++){
								if($index < 10) $themonth="0" . $index; else $themonth=$index;
								print "<option value='" . $themonth . "'>" . $themonth . "</option>\n";
							} ?>
				  </select> / <select name="<?php print $ssexyear?>" id="<?php print $ssexyear?>" size="1">
					<option value=""><?php print $GLOBALS['xxYear']?></option>
					<?php	$thisyear=date("Y", time());
							for($index=$thisyear; $index <= $thisyear+10; $index++){
								print "<option value='" . ($isPSiGate?substr($index,-2):$index) . "'>" . $index . "</option>\n";
							} ?></select>
				</div>
			  </div>
			  <div class="cart4row">
			    <div class="cobhl cobhl4"><?php print labeltxt($GLOBALS['xx34code'],$sscvv2)?></div>
				<div class="cobll cobll4"><input type="text" name="<?php print $sscvv2?>" id="<?php print $sscvv2?>" size="4" AUTOCOMPLETE="off" /> <?php if(@$requirecvv!=TRUE)print $GLOBALS['xxIfPres']?></div>
			  </div>
<?php		if(substr($data1,7,1)=="X"){ ?>
			  <div class="cart4row">
			    <div class="cobhl cobhl4"><?php print labeltxt('Issue Number / Start Date (mmyy)','IssNum')?></div>
				<div class="cobll cobll4"><input type="text" name="IssNum" id="IssNum" size="4" AUTOCOMPLETE="off" /> (Maestro/Solo Only)</div>
			  </div>
<?php		}
			if($acceptecheck==TRUE){ // Auth.net ?>
			  <div class="cart4row">
			    <div class="cobhl cobhl4 cart2column"><div class="cartecheck">ECheck Details</div><div class="echeckeither">Please enter either Credit Card OR ECheck details</div></div>
			  </div>
			  <div class="cart4row">
			    <div class="cobhl cobhl4"><?php print labeltxt('Account Name','accountname')?></div>
				<div class="cobll cobll4"><input type="text" name="accountname" id="accountname" size="21" AUTOCOMPLETE="off" value="<?php print trim($ordName.' '.$ordLastName)?>" /></div>
			  </div>
			  <div class="cart4row">
			    <div class="cobhl cobhl4"><?php print labeltxt('Account Number','accountnum')?></div>
				<div class="cobll cobll4"><input type="text" name="accountnum" id="accountnum" size="21" AUTOCOMPLETE="off" /></div>
			  </div>
			  <div class="cart4row">
			    <div class="cobhl cobhl4"><?php print labeltxt('Bank Name','bankname')?></div>
				<div class="cobll cobll4"><input type="text" name="bankname" id="bankname" size="21" AUTOCOMPLETE="off" /></div>
			  </div>
			  <div class="cart4row">
			    <div class="cobhl cobhl4"><?php print labeltxt('Routing Number','routenumber')?></div>
				<div class="cobll cobll4"><input type="text" name="routenumber" id="routenumber" size="10" AUTOCOMPLETE="off" /></div>
			  </div>
			  <div class="cart4row">
			    <div class="cobhl cobhl4"><?php print labeltxt('Account Type','accounttype')?></div>
				<div class="cobll cobll4"><select name="accounttype" id="accounttype" size="1"><option value=""><?php print $GLOBALS['xxPlsSel']?></option><option value="CHECKING">Checking</option><option value="SAVINGS">Savings</option><option value="BUSINESSCHECKING">Business Checking</option></select></div>
			  </div>
<?php			if(@$wellsfargo==TRUE){ ?>
			  <div class="cart4row">
			    <div class="cobhl cobhl4"><?php print labeltxt('Personal or Business Acct.','orgtype')?></div>
				<div class="cobll cobll4"><select name="orgtype" id="orgtype" size="1"><option value=""><?php print $GLOBALS['xxPlsSel']?></option><option value="I">Personal</option><option value="B">Business</option></select></div>
			  </div>
			  <div class="cart4row">
			    <div class="cobhl cobhl4"><?php print labeltxt('Tax ID','taxid')?></div>
				<div class="cobll cobll4"><input type="text" name="taxid" id="taxid" size="21" AUTOCOMPLETE="off" /></div>
			  </div>
			  <div class="cart4row">
			    <div class="cobhl cobhl4 cart2column carttaxidnot">If you have provided a Tax ID then the following information is not necessary</div>
			  </div>
			  <div class="cart4row">
			    <div class="cobhl cobhl4"><?php print labeltxt('Drivers License Number','licensenumber')?></div>
				<div class="cobll cobll4"><input type="text" name="licensenumber" id="licensenumber" size="21" AUTOCOMPLETE="off" /></div>
			  </div>
			  <div class="cart4row">
			    <div class="cobhl cobhl4"><?php print labeltxt('Drivers License State','licensestate')?></div>
				<div class="cobll cobll4"><select size="1" name="licensestate" id="licensestate"><option value=""><?php print $GLOBALS['xxPlsSel']?></option><?php
					$sSQL='SELECT stateName,stateAbbrev FROM states WHERE stateCountryID=1 ORDER BY stateName';
					$result=ect_query($sSQL) or ect_error();
					while($rs=ect_fetch_assoc($result))
						print '<option value="' . str_replace('"','&quot;',$rs['stateAbbrev']) . '">' . $rs['stateName'] . '</option>';
					ect_free_result($result); ?></select></div>
			  </div>
			  <div class="cart4row">
			    <div class="cobhl cobhl4"><?php print labeltxt('Date Of Birth On License','dldobmon')?></div>
				<div class="cobll cobll4"><select name="dldobmon" id="dldobmon" size="1"><option value=""><?php print $GLOBALS['xxMonth']?></option>
<?php				for($index=1; $index <= 12; $index++) print '<option value="' . $index . '">' . date("M", mktime(1,0,0,$index,1,1990)) . '</option>'; ?>
				</select> <select name="dldobday" size="1"><option value="">Day</option>
<?php				for($index=1; $index <= 31; $index++) print '<option value="' . $index . '">' . $index . '</option>'; ?>
				</select> <select name="dldobyear" size="1"><option value=""><?php print $GLOBALS['xxYear']?></option>
<?php				$thisyear=date("Y");
					for($index=$thisyear-100; $index <= $thisyear; $index++) print '<option value="' . $index . '">' . $index . '</option>'; ?>
				</select></div>
			  </div>
<?php			}
			}
		}
		if($success){ ?>
			  <div class="cart4row">
			    <div class="cobhl cart2column"><?php if(@$cardinalprocessor!='' && @$cardinalmerchant!='' && @$cardinalpwd!='') print $GLOBALS['xxCentl']; else print $GLOBALS['xxMstClk']?></div>
			  </div>
			  <div class="cobll cart2column checkoutbutton3"><?php
			if($orderid!=0){
				if($ordPayProvider=='23' && $grandtotal>0)
					print '<script src="https://checkout.stripe.com/checkout.js" class="stripe-button" data-key="'.$data2.'" data-amount="'.round($grandtotal*100).'" data-currency="'.$countryCurrency.'" data-email="'.$ordEmail.'" ' . ($data3!=''?'data-name="'.$data3.'" ':'') . 'data-description="' . substr(htmlspecials($descstr),0,255) . '" data-image="/128x128.png"></script>';
				else
					print imageorsubmit($imgcheckoutbutton3,(@$GLOBALS['xxCOTxt3']!=''?$GLOBALS['xxCOTxt3']:$GLOBALS['xxCOTxt']).(@$closeorderimmediately?'" onclick="docloseorder()':''),'checkoutbutton checkoutbutton3');
			} ?>
			  </div>
<?php	} ?>
			</div>
<?php	if($shipType==4){ ?>
			<div class="carriertm" style="text-align:center;font-size:10px;padding:6px"><?php print $GLOBALS['xxUPStm']?></div>
<?php	}elseif($shipType==7 || $shipType==8){ ?>
			<div class="carriertm" style="text-align:center;font-size:10px;padding:6px"><?php print $fedexcopyright?></div>
<?php	}
		print '</form>' . "\r\n" . '<form method="post" name="shipform" id="shipform" action="cart.php">';
		print whv('mode','go') . whv('sessionid',$thesessionid) . whv('orderid',$orderid) . whv('cpncode',$rgcpncode) . whv('token',$token) . whv('payerid',$payerid) . whv('remember',getpost('remember'));
		writehiddenidvar('altrates',$shipType);
		writehiddenidvar('shipselectoridx','');
		writehiddenidvar('shipselectoraction','');
		writehiddenidvar('numshiprate',$numshiprate);
		writehiddenidvar('changeaction','');
		writehiddenidvar('willpickup','');
		writehiddenidvar('sftermsandconds',getpost('sftermsandconds'));
		if($amazonpayment) writehiddenidvar('amzrefid',$amzrefid_);
		if($warncheckspamfolder) print whv('warncheckspamfolder','true');
		print '</form>';
		$_SESSION['shipselectoridx']=(is_numeric(getpost('shipselectoridx')) ? getpost('shipselectoridx') : '');
		$_SESSION['shipselectoraction']=(getpost('shipselectoraction')=='selector' || getpost('shipselectoraction')=='altrates' ? getpost('shipselectoraction') : '');
		if(! $fromshipselector && $adminAltRates==2) print '<script type="text/javascript">getalternatecarriers();</script>';
	}
	if(@$GLOBALS['nopriceanywhere']) print '</form><script type="text/javascript">document.checkoutform.submit();</script>';
}elseif(getget('amazon')=='logout'){
	$_SESSION['AmazonLogin']='';
	$_SESSION['AmazonLoginTimeout']='';
}
if($checkoutmode=='checkout'){
	$sSQL="SELECT ordID FROM orders WHERE ordStatus>1 AND ordAuthNumber='' AND " . getordersessionsql();
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		release_stock($rs['ordID']);
		ect_query("UPDATE cart SET cartSessionID='".escape_string($thesessionid)."',cartClientID='" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "' WHERE cartCompleted=0 AND cartOrderID=" . $rs['ordID']) or ect_error();
		ect_query("UPDATE orders SET ordAuthStatus='MODWARNOPEN',ordShipType='MODWARNOPEN' WHERE ordID=" . $rs['ordID']) or ect_error();
	}
	ect_free_result($result);
	$remember=FALSE;
	$havestate=FALSE;
	if(getpost('checktmplogin')!=''){
		$sSQL="SELECT tmploginname FROM tmplogin WHERE tmploginid='" . escape_string(getpost('sessionid')) . "' AND tmploginchk='" . escape_string(getpost('checktmplogin')) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$_SESSION['clientID']=$rs['tmploginname'];
			ect_free_result($result);
			$sSQL="SELECT clUserName,clActions,clLoginLevel,clPercentDiscount,clEmail,clPW FROM customerlogin WHERE clID='" . escape_string($_SESSION['clientID']) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				$_SESSION['clientUser']=$rs['clUserName'];
				$_SESSION['clientActions']=$rs['clActions'];
				$_SESSION['clientLoginLevel']=$rs['clLoginLevel'];
				$_SESSION['clientPercentDiscount']=(100.0-(double)$rs['clPercentDiscount'])/100.0;
				get_wholesaleprice_sql();
				if($rs['clEmail']!=@$_COOKIE['WRITECLL'] || $rs['clPW']!=@$_COOKIE['WRITECLP']){
					ectsetcookie('WRITECLL',$rs['clEmail'],0,'/','');
					ectsetcookie('WRITECLP',$rs['clPW'],0,'/','');
				}
			}
		}
		ect_free_result($result);
	}else{
		$_SESSION['clientID']=NULL; unset($_SESSION['clientID']); $_SESSION['clientUser']=NULL; unset($_SESSION['clientUser']); $_SESSION['clientActions']=NULL; unset($_SESSION['clientActions']); $_SESSION['clientLoginLevel']=NULL; unset($_SESSION['clientLoginLevel']); $_SESSION['clientPercentDiscount']=NULL; unset($_SESSION['clientPercentDiscount']);
	}
	if(@$_COOKIE['id1']!='' && @$_COOKIE['id2']!='' && ! $returntocustomerdetails){
		retrieveorderdetails($_COOKIE['id1'],$_COOKIE['id2']);
		$remember=TRUE;
	}
	if(@$ordZip=='') $ordZip=@$_SESSION['zip'];
	if(@$ordState=='') $ordState=@$_SESSION['state'];
	if(@$ordCountry=='') $ordCountry=@$_SESSION['country'];
	$sSQL='SELECT stateID FROM states INNER JOIN countries ON states.stateCountryID=countries.countryID WHERE countryEnabled<>0 AND stateEnabled<>0 AND loadStates=2 ORDER BY stateCountryID,stateName';
	$result=ect_query($sSQL) or ect_error();
	$hasstates=(ect_num_rows($result)>0);
	ect_free_result($result);
	$sSQL="SELECT countryName,countryOrder,".getlangid('countryName',8)." AS countryName,countryID,loadStates FROM countries WHERE countryEnabled=1 ORDER BY countryOrder DESC," . getlangid("countryName",8);
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		$allcountries[$numallcountries++]=$rs;
	}
	ect_free_result($result);
	$addresses='';
	$numaddresses=0;
	if((@$enableclientlogin==TRUE || @$forceclientlogin==TRUE) && @$_SESSION['clientID']!=''){
		$sSQL="SELECT addID,addIsDefault,addName,addLastName,addAddress,addAddress2,addState,addCity,addZip,addPhone,addCountry,addExtra1,addExtra2 FROM address INNER JOIN countries ON address.addCountry=countries.countryName WHERE addCustID='" . escape_string($_SESSION['clientID']) . "' ORDER BY addAddress";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result))
			$addresses[$numaddresses++]=$rs;
		ect_free_result($result);
	} ?>
			<form method="post" name="mainform" action="cart.php" onsubmit="return checkform(this)">
<?php
	if(is_array($addresses)){ ?>
<script type="text/javascript">/* <![CDATA[ */
var addrs=new Array();
addrs[0]=new Array();addrs[0]['name']='';addrs[0]['lastname']='';addrs[0]['address']='';addrs[0]['address2']='';addrs[0]['city']='';addrs[0]['state']='';addrs[0]['zip']='';addrs[0]['phone']='';addrs[0]['country']='';addrs[0]['extra1']='';addrs[0]['extra2']='';
function checkeditbutton(isshipping){
	adidobj=document.getElementById(isshipping + 'addressid');
	theaddy=adidobj[adidobj.selectedIndex].value;
	if(theaddy=='') document.getElementById(isshipping + 'editbutton').disabled=true; else document.getElementById(isshipping + 'editbutton').disabled=false;
}
function editaddress(isshipping,isaddnew){
	eval(isshipping+'checkaddress=true;');
	adidobj=document.getElementById(isshipping + 'addressid');
	theaddy=adidobj[adidobj.selectedIndex].value;
	if(isaddnew)theaddy=0;
	document.getElementById(isshipping + 'name').value=addrs[theaddy]['name'];
<?php	if(@$usefirstlastname==TRUE) print "document.getElementById(isshipping + 'lastname').value=addrs[theaddy]['lastname'];" ?>
	document.getElementById(isshipping + 'address').value=addrs[theaddy]['address'];
<?php	if(@$useaddressline2==TRUE) print "document.getElementById(isshipping + 'address2').value=addrs[theaddy]['address2'];" ?>
	document.getElementById(isshipping + 'city').value=addrs[theaddy]['city'];
	document.getElementById(isshipping + 'zip').value=addrs[theaddy]['zip'];
	document.getElementById(isshipping + 'phone').value=addrs[theaddy]['phone'];
<?php	if(trim(@$extraorderfield1)!='') print "setdefs(document.getElementById('ord'+isshipping+'extra1'),addrs[theaddy]['extra1']);";
		if(trim(@$extraorderfield2)!='') print "setdefs(document.getElementById('ord'+isshipping+'extra2'),addrs[theaddy]['extra2']);"; ?>
	thecntry=document.getElementById(isshipping + 'country')
	foundcntry=9999;
	for(var ind=0; ind < thecntry.length; ind++){
		if(thecntry[ind].value==addrs[theaddy]['countryid']){
			thecntry.selectedIndex=ind;
			foundcntry=ind;
		}
	}
	if(foundcntry==9999)thecntry.selectedIndex=0;
	dynamiccountries(document.getElementById(isshipping+'country'),isshipping);
	foundstate=0;
	checkoutspan(isshipping);
<?php	if($hasstates){ ?>
	thestate=document.getElementById(isshipping + 'state');
	if(countryhasstates[addrs[theaddy]['countryid']]){
		for(var ind=0; ind < thestate.length; ind++){
			if(thestate[ind].value==addrs[theaddy]['state'])
				foundstate=ind;
		}
	}else
		document.getElementById(isshipping+'state2').value=addrs[theaddy]['state'];
	thestate.selectedIndex=foundstate;
<?php	} ?>
	showshipform(1,thecntry);
}
<?php	for($ii=0; $ii<$numaddresses; $ii++){
			print 'addrs[' . $addresses[$ii]['addID'] . "]=new Array();\r\n";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['name']='" . jschk($addresses[$ii]['addName']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['lastname']='" . jschk($addresses[$ii]['addLastName']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['address']='" . jschk($addresses[$ii]['addAddress']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['address2']='" . jschk($addresses[$ii]['addAddress2']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['state']='" . jschk($addresses[$ii]['addState']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['city']='" . jschk($addresses[$ii]['addCity']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['zip']='" . jschk($addresses[$ii]['addZip']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['phone']='" . jschk($addresses[$ii]['addPhone']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['country']='" . jschk($addresses[$ii]['addCountry']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['countryid']='" . getidfromcountry($addresses[$ii]['addCountry']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['extra1']='" . jschk($addresses[$ii]['addExtra1']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['extra2']='" . jschk($addresses[$ii]['addExtra2']) . "';\r\n";
		} ?>
/* ]]> */</script>
<?php
	}
	print whv('mode','go');
	print whv('sessionid',strip_tags(trim($thesessionid)));
	print whv('PARTNER',strip_tags(getpost('PARTNER')));
	print whv('altrates',strip_tags(getpost('altrates')));
	$colspan2='';
	$colspan3=''; ?>
	<input type="hidden" name="addaddress" id="addaddress" value="<?php print ($numaddresses>0 ? '' : 'add')?>" />
	<input type="hidden" name="saddaddress" id="saddaddress" value="<?php print ($numaddresses>0 ? '' : 'add')?>" />
<?php if(@$GLOBALS['xxCoStp2']!='') print '<div class="checkoutsteps">' . $GLOBALS['xxCoStp2'] . '</div>'?>
		<div class="cart2details">
			<div class="cobhl cart2header cartheader"><?php print $GLOBALS['xxCstDtl']?></div>
<?php
	if($returntocustomerdetails && $errormsg!='') print '<div class="cobhl cart2column ectwarning">' . $errormsg . "</div>\r\n";
	if(getpayprovdetails(21,$data1,$data2,$data3,$demomode,$ppmethod)){
		print '<div class="amazonpaybutton" id="AmazonPayButton"></div>';
		if(@$GLOBALS['xxAmzPBT']=='') $GLOBALS['xxAmzPBT']='- or enter your customer and payment details below -';
		print '<div class="amazonpaybuttontext">' . $GLOBALS['xxAmzPBT'] . '</div>';
		print '<script type="text/javascript">window.onAmazonLoginReady=function(){amazon.Login.setClientId("' . $data1 . '");};</script>';
		print '<script type="text/javascript" src="' . getamazonjsurl($demomode) . '"></script>';
?>					
<script type="text/javascript">
var authRequest;
OffAmazonPayments.Button("AmazonPayButton", "<?php print $data2?>", { // MERCHANT_ID
	type: "<?php print @$_SESSION['AmazonLogin']!=''?'PwA':'LwA'?>",
	authorization: function () {
		loginOptions = { scope: "profile postal_code payments:widget payments:shipping_address", popup: true };
		authRequest = amazon.Login.authorize(loginOptions, "<?php print $storeurlssl.'cart.php?amazonpay=go'?>");
	},
	onError: function (error) {
		alert("handle error function");
	}
});
function amazonlogout(){
	amazon.Login.logout();
	document.location='cart.php?amazon=logout';
	return false;
}
</script>
<?php
	}
	if($numaddresses>0){ ?>
				<div>
				  <div class="cdformtitle"><?php print $GLOBALS['xxBilAdd']?></div>
				  <div class="cdformtitlell">
<?php	function writeaddressspans($isshp){
			global $useaddressline2,$extraorderfield1html,$extraorderfield2html,$extraorderfield1required,$extraorderfield2required,$extraorderfield1,$extraorderfield2,$numaddresses,$addresses,$usefirstlastname,$redstar,$tleft,$tright,$hasstates,$colspan2,$errordname,$errordaddress,$errordcity,$errordstate,$errordcountry,$errordzip,$errordphone;
?>		<span id="<?php print $isshp?>addressspan1" style="display:block"><select name="<?php print $isshp?>addressid" id="<?php print $isshp?>addressid" size="1" onchange="checkeditbutton('<?php print $isshp?>')"><?php
		if($isshp=='s') print '<option value="">' . $GLOBALS['xxSamAs'] . '</option>';
		for($index=0; $index < $numaddresses; $index++){
			print '<option value="' . $addresses[$index]['addID'] . '"' . ($addresses[$index]['addIsDefault']==($isshp=='s'?2:1) ? ' selected="selected"' : '') . '>' . htmlspecials(trim($addresses[$index]['addName'] . (@$usefirstlastname?' '.$addresses[$index]['addLastName']:''))) . ', ' . htmlspecials($addresses[$index]['addAddress']) . (trim($addresses[$index]['addAddress2'])!='' ? ', ' . htmlspecials($addresses[$index]['addAddress2']) : '') . ', ' . htmldisplay($addresses[$index]['addState']) . '</option>';
		} ?></select> <input type="button" value="<?php print $GLOBALS['xxEdit']?>" id="<?php print $isshp?>editbutton" onclick="editaddress('<?php print $isshp?>',false);document.getElementById('<?php print $isshp?>addressspan1').style.display='none';document.getElementById('<?php print $isshp?>addressspan2').style.display='block';document.getElementById('<?php print $isshp?>addaddress').value='edit';"> <input type="button" value="<?php print $GLOBALS['xxNew']?>" onclick="editaddress('<?php print $isshp?>',true);document.getElementById('<?php print $isshp?>addressspan1').style.display='none';document.getElementById('<?php print $isshp?>addressspan2').style.display='block';document.getElementById('<?php print $isshp?>addaddress').value='add';">
		</span><div id="<?php print $isshp?>addressspan2" style="display:none">
			<?php	if(trim(@$extraorderfield1)!=''){ ?>
			<div class="billformrow"><div class="cobhl cobhl2 cdformtextra1"><?php print (@$extraorderfield1required==TRUE ? $redstar : '') . labeltxt($extraorderfield1,'ord'.$isshp.'extra1') ?>:</div><div class="cobll cobll2 cdformextra1"><?php if(@$extraorderfield1html!='') print str_replace(array('ectfield','ordextra1'),'ord'.$isshp.'extra1',$extraorderfield1html); else print '<input type="text" name="ord'.$isshp.'extra1" id="ord'.$isshp.'extra1" size="20" />'?></div></div>
			<?php	} ?>
			<div class="billformrow"><div class="cobhl cobhl2 cdformtname<?php print $isshp=='' && $errordname?' ectwarning':''?>"><?php print $redstar . labeltxt($GLOBALS['xxName'],$isshp.'name')?>:</div><div class="cobll cobll2 cdformname"><?php
			if(@$usefirstlastname)
				print '<input type="text" name="'.$isshp.'name" id="'.$isshp.'name" size="11" onfocus="if(this.value==\''.$GLOBALS['xxFirNam'].'\'){this.value=\'\';this.style.color=\'\';}" /> <input type="text" name="'.$isshp.'lastname" id="'.$isshp.'lastname" size="11" onfocus="if(this.value==\''.$GLOBALS['xxLasNam'].'\'){this.value=\'\';this.style.color=\'\';}" />';
			else
				print '<input type="text" name="'.$isshp.'name" id="'.$isshp.'name" size="20" />';
			?></div></div>
			<div class="billformrow"><div class="cobhl cobhl2 cdformtaddress<?php print $isshp=='' && $errordaddress?' ectwarning':''?>"><?php print $redstar . labeltxt($GLOBALS['xxAddress'],$isshp.'address')?>:</div><div class="cobll cobll2 cdformaddress"><input type="text" name="<?php print $isshp?>address" id="<?php print $isshp?>address" size="25" /></div></div>
			<?php	if(@$useaddressline2==TRUE){ ?>
			<div class="billformrow"><div class="cobhl cobhl2 cdformtaddress2"><?php print labeltxt($GLOBALS['xxAddress2'],$isshp.'address2')?>:</div><div class="cobll cobll2 cdformaddress2"><input type="text" name="<?php print $isshp?>address2" id="<?php print $isshp?>address2" size="25" /></div></div>
			<?php	} ?>
			<div class="billformrow"><div class="cobhl cobhl2 cdformtcity<?php print $isshp=='' && $errordcity?' ectwarning':''?>"><?php print $redstar . labeltxt($GLOBALS['xxCity'],$isshp.'city')?>:</div><div class="cobll cobll2 cdformcity"><input type="text" name="<?php print $isshp?>city" id="<?php print $isshp?>city" size="20" /></div></div>
			<div class="billformrow"><div class="cobhl cobhl2 cdformtstate<?php print $isshp=='' && $errordstate?' ectwarning':''?>"><?php print replace($redstar,'<span','<span id="'.$isshp.'statestar"')?><span id="<?php print $isshp?>statetxt"><?php print labeltxt($GLOBALS['xxState'],$isshp.'state')?></span>:</div><div class="cobll cobll2 cdformstate"><?php if($hasstates){ ?><select name="<?php print $isshp?>state" id="<?php print $isshp?>state" size="1" onchange="dosavestate('')"><?php $havestate=show_states(-1) ?></select><?php } ?><input type="text" name="<?php print $isshp?>state2" id="<?php print $isshp?>state2" size="20" /></div></div>
			<div><div class="cobhl cobhl2 cdformtcountry<?php print $isshp=='' && $errordcountry?' ectwarning':''?>"><?php print $redstar . labeltxt($GLOBALS['xxCountry'],$isshp.'country')?>:</div><div class="cobll cobll2 cdformcountry"><select name="<?php print $isshp?>country" id="<?php print $isshp?>country" size="1" onchange="checkoutspan('<?php print $isshp?>');showshipform(1,this)"><?php show_countries(-1,TRUE) ?></select></div></div>
			<div class="billformrow"><div class="cobhl cobhl2 cdformtzip<?php print $isshp=='' && $errordzip?' ectwarning':''?>"><?php print replace($redstar,'<span','<span id="'.$isshp.'zipstar"') . labeltxt($GLOBALS['xxZip'],$isshp.'zip')?>:</div><div class="cobll cobll2 cdformzip"><input type="text" name="<?php print $isshp?>zip" id="<?php print $isshp?>zip" size="10" autocapitalize="characters" /></div></div>
			<div class="billformrow"><div class="cobhl cobhl2 cdformtphone<?php print $isshp=='' && $errordphone?' ectwarning':''?>"><?php if($isshp=='') print $redstar; print labeltxt($GLOBALS['xxPhone'],$isshp.'phone')?>:</div><div class="cobll cobll2 cdformphone"><input type="tel" name="<?php print $isshp?>phone" id="<?php print $isshp?>phone" size="20" /></div></div>
			<?php	if(trim(@$extraorderfield2)!=''){ ?>
			<div class="billformrow"><div class="cobhl cobhl2 cdformtextra2"><?php print (@$extraorderfield2required==TRUE ? $redstar : '') . labeltxt($extraorderfield2,'ord'.$isshp.'extra2')?>:</div><div class="cobll cobll2 cdformextra2"><?php if(@$extraorderfield2html!='') print str_replace(array('ectfield','ordextra2'),'ord'.$isshp.'extra2',$extraorderfield2html); else print '<input type="text" name="ord'.$isshp.'extra2" id="ord'.$isshp.'extra2" size="20" />'?></div></div>
			<?php	} ?>
			<div class="cobll cdform2column"><input type="button" value="<?php print $GLOBALS['xxCancel']?>" onclick="document.getElementById('<?php print $isshp?>addressspan2').style.display='none';document.getElementById('<?php print $isshp?>addressspan1').style.display='block';document.getElementById('<?php print $isshp?>addaddress').value='';<?php print $isshp?>checkaddress=false;"></div>
		</div>
<?php	}
		writeaddressspans(''); ?>
				  </div>
				</div>
<?php	writeshippingflags(2);
		if(@$noshipaddress!=TRUE){ ?>
				<div>
				  <div class="cdformtitle"><?php print $GLOBALS['xxShpAdd']?></div>
				  <div class="cdformtitlell"><?php writeaddressspans('s'); ?></div>
				</div>
<?php	}
	}else{
		if(@$_SESSION['clientID']!=''){
			$result=ect_query("SELECT clUserName,clEmail FROM customerlogin WHERE clID='" . escape_string($_SESSION['clientID']) . "'") or ect_error();
			if($rs=ect_fetch_assoc($result)){
				$ordName=trim($rs['clUserName']);
				if(@$usefirstlastname){
					if(strpos(trim($ordName), ' ')!==FALSE){
						$namearr=explode(' ',$ordName,2);
						$ordName=$namearr[0];
						$ordLastName=$namearr[1];
					}else
						$ordName='';
				}
				$ordEmail=$rs['clEmail'];
			}
			ect_free_result($result);
		}
		function displayzip($isship){
			global $tright,$redstar,$ordShipZip,$ordZip,$errordzip,$errordshipaddress;?>
				<div class="<?php print $isship?'ship':'bill'?>formrow">
				  <div class="cobhl cobhl2 cdformtzip<?php print ($isship=='' && $errordzip) || ($isship=="s" && $errordshipaddress && $ordShipZip=='')?' ectwarning':''?>"><?php print replace($redstar,'<span','<span id="'.$isship.'zipstar"')?><span id="<?php print $isship?>ziptxt"><?php print labeltxt($GLOBALS['xxZip'],$isship.'zip')?></span></div>
				  <div class="cobll cobll2 cdformzip"><input type="text" name="<?php print $isship?>zip" class="cdforminput cdformzip" id="<?php print $isship?>zip" size="10" value="<?php print $isship?@$ordShipZip:@$ordZip?>" autocapitalize="characters" /></div>
				</div>
<?php	}
		if(trim(@$extraorderfield1)!=''){ ?>
				<div class="billformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtextra1"><?php if(@$extraorderfield1required==TRUE) print $redstar;
									print labeltxt($extraorderfield1,'ordextra1')?></div>
				  <div class="cobll cobll2 cdformextra1"><?php if(@$extraorderfield1html!='') print str_replace('ectfield','ordextra1',$extraorderfield1html); else print '<input type="text" name="ordextra1" class="cdforminput cdformextra1" id="ordextra1" size="20" value="' . @$ordExtra1 . '" />'?></div>
				</div>
<?php	} ?>
				<div class="billformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtname<?php print $errordname?' ectwarning':''?>"><?php print $redstar . labeltxt($GLOBALS['xxName'],'name')?></div>
				  <div class="cobll cobll2 cdformname" style="white-space:nowrap"><?php
		if(@$usefirstlastname){
			$thestyle='';
			if(@$ordName=='' && @$ordLastName=='') $thestyle='style="color:#BBBBBB" ';
			print '<input type="text" name="name" class="cdforminput cdformname" id="name" size="10" value="' . (@$ordName==''?$GLOBALS['xxFirNam']:htmlspecials($ordName)) . '" onfocus="if(this.value==\'' . $GLOBALS['xxFirNam'] . '\'){this.value=\'\';this.style.color=\'\';}" ' . $thestyle . '/> <input type="text" name="lastname" class="cdforminput cdformlastname" id="lastname" size="10" value="' . (@$ordLastName==''?$GLOBALS['xxLasNam']:htmlspecials($ordLastName)) . '" onfocus="if(this.value==\'' . $GLOBALS['xxLasNam'] . '\'){this.value=\'\';this.style.color=\'\';}" ' . $thestyle . '/>';
		}else
			print '<input type="text" name="name" class="cdforminput cdformname" id="name" size="20" value="' . htmlspecials(@$ordName) . '" />'; ?></div>
				</div>
				<div class="billformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtemail<?php print $errordemail?' ectwarning':''?>"><?php print $redstar . labeltxt($GLOBALS['xxEmail'],'email')?></div>
				  <div class="cobll cobll2 cdformemail"><input type="email" name="email" class="cdforminput cdformemail" id="email" size="25" value="<?php print @$ordEmail?>" /></div>
				</div>
				<div class="billformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtaddress<?php print $errordaddress?' ectwarning':''?>"><?php print $redstar . labeltxt($GLOBALS['xxAddress'],'address')?></div>
				  <div class="cobll cobll2 cdformaddress"><input type="text" name="address" id="address" class="cdforminput cdformaddress" size="25" value="<?php print @$ordAddress?>" /></div>
				</div>
<?php	if(@$useaddressline2==TRUE){ ?>
				<div class="billformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtaddress2"><?php print labeltxt($GLOBALS['xxAddress2'],'address2')?></div>
				  <div class="cobll cobll2 cdformaddress2"><input type="text" name="address2" class="cdforminput cdformaddress2" id="address2" size="25" value="<?php print @$ordAddress2?>" /></div>
				</div>
<?php	}
		if($GLOBALS['zipposition']==4) displayzip('') ?>
				<div class="billformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtcity<?php print $errordcity?' ectwarning':''?>"><?php print $redstar . labeltxt($GLOBALS['xxCity'],'city')?></div>
				  <div class="cobll cobll2 cdformcity"><input type="text" name="city" class="cdforminput cdformcity" id="city" size="20" value="<?php print @$ordCity?>" /></div>
				</div>
<?php	if($GLOBALS['zipposition']==3) displayzip('') ?>
				<div class="billformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtstate<?php print $errordstate?' ectwarning':''?>"><?php print replace($redstar,'<span','<span id="statestar"')?><span id="statetxt"><?php print labeltxt($GLOBALS['xxState'],'state')?></span></div>
				  <div class="cobll cobll2 cdformstate"><?php if($hasstates){ ?><select name="state" class="cdformselect cdformstate" id="state" size="1" onchange="dosavestate('')"><?php $havestate=show_states($ordState) ?></select><?php } ?><input type="text" name="state2" class="cdforminput cdformstate" id="state2" style="display:none" size="20" value="<?php if(! $havestate) print $ordState?>" /></div>
				</div>
<?php	if($GLOBALS['zipposition']==2) displayzip('') ?>
				<div>
				  <div class="cobhl cobhl2 cdformtcountry<?php print $errordcountry?' ectwarning':''?>"><?php print $redstar . labeltxt($GLOBALS['xxCountry'],'country')?></div>
				  <div class="cobll cobll2 cdformcountry"><select name="country" class="cdformselect cdformcountry" id="country" size="1" onchange="checkoutspan('');showshipform(1,this)"><?php show_countries(@$ordCountry,TRUE) ?></select></div>
				</div>
<?php	if($GLOBALS['zipposition']==1) displayzip('') ?>
				<div class="billformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtphone<?php print $errordphone?' ectwarning':''?>"><?php print $redstar . labeltxt($GLOBALS['xxPhone'],'phone')?></div>
				  <div class="cobll cobll2 cdformphone"><input type="tel" name="phone" class="cdforminput cdformphone" id="phone" size="20" value="<?php print @$ordPhone?>" /></div>
				</div>
<?php	if(trim(@$extraorderfield2)!=''){ ?>
				<div>
				  <div class="cobhl cobhl2 cdformtextra2"><?php if(@$extraorderfield2required==TRUE) print $redstar;
									print labeltxt($extraorderfield2,'ordextra2')?></div>
				  <div class="cobll cobll2 cdformextra2"><?php if(@$extraorderfield2html!='') print str_replace('ectfield','ordextra2',$extraorderfield2html); else print '<input type="text" name="ordextra2" class="cdforminput cdformextra2" id="ordextra2" size="20" value="' . @$ordExtra2 . '" />'?></div>
				</div>
<?php	}
		writeshippingflags(2);
		if(@$noshipaddress!=TRUE){ ?>
				<div>
				  <div class="cobhl cobhl2 cdformshipdiff">
					<input name="shipdiff" class="cdformcb cdformshipdiff" id="shipdiff" value="1" type="checkbox" onclick="checkoutspan('s');showshipform(2,document.getElementById('scountry'))" <?php print getpost('shipdiff')=='1' || (trim($ordShipName.$ordShipLastName)!='' && trim($ordShipAddress)!='')?'checked="checked" ':'' ?>/><?php print $GLOBALS['xxShpDff']?>
				  </div>
				</div>
<?php		if(trim(@$extraorderfield1)!=''){ ?>
				<div class="shipformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtextra1"><?php if(@$extraorderfield1required==TRUE) print $redstar;
									print labeltxt($extraorderfield1,'ordsextra1')?></div>
				  <div class="cobll cobll2 cdformextra1"><?php if(@$extraorderfield1html!='') print str_replace(array('ordextra1','ectfield'),'ordsextra1',$extraorderfield1html); else print '<input type="text" name="ordsextra1" class="cdforminput cdformextra1" id="ordsextra1" size="20" value="' . @$ordShipExtra1 . '" />'?></div>
				</div>
<?php		} ?>
				<div class="shipformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtname<?php print $errordshipaddress && $ordShipName==''?' ectwarning':''?>"><?php print $redstar . labeltxt($GLOBALS['xxName'],'sname')?></div>
				  <div class="cobll cobll2 cdformname"><?php
		if(@$usefirstlastname){
			$thestyle='';
			if(@$ordShipName=='' && @$ordShipLastName=='') $thestyle='style="color:#BBBBBB" ';
			print '<input type="text" name="sname" class="cdforminput cdformname" id="sname" size="10" value="' . (@$ordShipName==''?$GLOBALS['xxFirNam']:htmlspecials($ordShipName)) . '" onfocus="if(this.value==\'' . $GLOBALS['xxFirNam'] . '\'){this.value=\'\';this.style.color=\'\';}" ' . $thestyle . '/> <input type="text" name="slastname" id="slastname" size="10" value="' . (@$ordShipLastName==''?$GLOBALS['xxLasNam']:htmlspecials($ordShipLastName)) . '" onfocus="if(this.value==\'' . $GLOBALS['xxLasNam'] . '\'){this.value=\'\';this.style.color=\'\';}" ' . $thestyle . '/>';
		}else
			print '<input type="text" name="sname" class="cdforminput cdformname" id="sname" size="20" value="' . htmlspecials(@$ordShipName) . '" />'; ?></div>
				</div>
				<div class="shipformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtaddress<?php print $errordshipaddress && $ordShipAddress==''?' ectwarning':''?>"><?php print $redstar . labeltxt($GLOBALS['xxAddress'],'saddress')?></div>
				  <div class="cobll cobll2 cdformaddress"><input type="text" name="saddress" class="cdforminput cdformaddress" id="saddress" size="25" value="<?php print trim(@$ordShipAddress)?>" /></div>
				</div>
<?php		if(@$useaddressline2==TRUE){ ?>
				<div class="shipformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtaddress2"><?php print labeltxt($GLOBALS['xxAddress2'],'saddress2')?></div>
				  <div class="cobll cobll2 cdformaddress2"><input type="text" name="saddress2" class="cdforminput cdformaddress2" id="saddress2" size="25" value="<?php print @$ordShipAddress2?>" /></div>
				</div>
<?php		}
			if($GLOBALS['zipposition']==4) displayzip('s') ?>
				<div class="shipformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtcity<?php print $errordshipaddress && $ordShipCity==''?' ectwarning':''?>"><?php print $redstar . labeltxt($GLOBALS['xxCity'],'scity')?></div>
				  <div class="cobll cobll2 cdformcity"><input type="text" name="scity" class="cdforminput cdformcity" id="scity" size="20" value="<?php print @$ordShipCity?>" /></div>
				</div>
<?php		if($GLOBALS['zipposition']==3) displayzip('s') ?>
				<div class="shipformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtcountry<?php print $errordshipaddress && $ordShipCountry==''?' ectwarning':''?>"><?php print $redstar . labeltxt($GLOBALS['xxCountry'],'scountry')?></div>
				  <div class="cobll cobll2 cdformcountry"><select name="scountry" class="cdformselect cdformcountry" id="scountry" size="1" onchange="checkoutspan('s')"><option value=""><?php print $GLOBALS['xxPlsSel']?>...</option><?php show_countries(@$ordShipCountry,FALSE) ?></select></div>
				</div>
<?php		if($GLOBALS['zipposition']==2) displayzip('s') ?>
				<div class="shipformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtstate<?php print $errordshipaddress && $ordShipState==''?' ectwarning':''?>"><?php print replace($redstar,'<span','<span id="sstatestar"')?><span id="sstatetxt"><?php print labeltxt($GLOBALS['xxState'],'sstate')?></span></div>
				  <div class="cobll cobll2 cdformstate"><?php if($hasstates){ ?><select name="sstate" class="cdformselect cdformstate" id="sstate" size="1" onchange="dosavestate('s')"><?php $havestate=show_states($ordShipState) ?></select><?php } ?><input type="text" name="sstate2" class="cdforminput cdformstate" id="sstate2" style="display:none" size="20" value="<?php if(! $havestate) print $ordShipState?>" /></div>
				</div>
<?php		if($GLOBALS['zipposition']==1) displayzip('s') ?>
				<div class="shipformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtphone"><?php print labeltxt($GLOBALS['xxPhone'],'sphone')?></div>
				  <div class="cobll cobll2 cdformphone"><input type="tel" name="sphone" class="cdforminput cdformphone" id="sphone" size="20" value="<?php print @$ordShipPhone?>" /></div>
				</div>
<?php		if(trim(@$extraorderfield2)!=''){ ?>
				<div class="shipformrow" style="display:none">
				  <div class="cobhl cobhl2 cdformtextra2"><?php if(@$extraorderfield2required==TRUE) print $redstar;
									print labeltxt($extraorderfield2,'ordsextra2')?></div>
				  <div class="cobll cobll2 cdformextra2"><?php if(@$extraorderfield2html!='') print str_replace(array('ordextra2','ectfield'),'ordsextra2',$extraorderfield2html); else print '<input type="text" name="ordsextra2" class="cdforminput cdformextra2" id="ordsextra2" size="20" value="' . @$ordShipExtra2 . '" />'?></div>
				</div>
<?php		}
		} // noshipaddress
	} // ($numaddresses>0) ?>
				<div class="cobhl cartheader cart2subheader cart2column"><?php print $GLOBALS['xxMisc']?></div>
<?php
	if(@$noadditionalinfo!=TRUE){ ?>
				<div class="checkoutadditionals">
				  <div class="cobhl cobhl2 cdformtaddinfo"><?php print $GLOBALS['xxAddInf']?></div>
				  <div class="cobll cobll2 cdformaddinfo"><textarea name="ordAddInfo" class="addinfo" rows="3" cols="44"><?php print @$ordAddInfo?></textarea></div>
				</div>
<?php
	}
	if(trim(@$extracheckoutfield1)!=''){
		$checkoutfield1=(@$extracheckoutfield1required==TRUE ? $redstar : '') . labeltxt($extracheckoutfield1,'ordcheckoutextra1');
		$checkoutfield2=(@$extracheckoutfield1html!='' ? str_replace('ectfield','ordcheckoutextra1',$extracheckoutfield1html) : '<input type="text" name="ordcheckoutextra1" class="cdforminput cdformextraco1" id="ordcheckoutextra1" size="20" value="' . @$ordCheckoutExtra1 . '" />');
?>				<div class="checkoutadditionals">
				  <div class="cobhl cobhl2 cdformtextraco1"><?php if(@$extracheckoutfield1reverse) print $checkoutfield2; else print $checkoutfield1 ?></div>
				  <div class="cobll cobll2 cdformextraco1"><?php if(@$extracheckoutfield1reverse) print $checkoutfield1; else print $checkoutfield2 ?></div>
				</div>
<?php
	}
	if(trim(@$extracheckoutfield2)!=''){
		$checkoutfield1=(@$extracheckoutfield2required==TRUE ? $redstar : '') . labeltxt($extracheckoutfield2,'ordcheckoutextra2');
		$checkoutfield2=(@$extracheckoutfield2html!='' ? str_replace('ectfield','ordcheckoutextra2',$extracheckoutfield2html) : '<input type="text" name="ordcheckoutextra2" class="cdforminput cdformextraco1" id="ordcheckoutextra2" size="20" value="' . @$ordCheckoutExtra2 . '" />');
?>				<div class="checkoutadditionals">
				  <div class="cobhl cobhl2 cdformtextraco2"><?php if(@$extracheckoutfield2reverse) print $checkoutfield2; else print $checkoutfield1 ?></div>
				  <div class="cobll cobll2 cdformextraco2"><?php if(@$extracheckoutfield2reverse) print $checkoutfield1; else print $checkoutfield2 ?></div>
				</div>
<?php
	}
	if(@$GLOBALS['termsandconditions']){ ?>
				<div class="checkoutadditionals">
				  <div class="cobhl cobhl2 cdformtterms<?php print $errtermsandconditions?' ectwarning':'' ?>"><input type="checkbox" name="license" class="cdformcb cdformterms" value="1" /></div>
				  <div class="cobll cobll2 cdformterms"><?php print $GLOBALS['xxTermsCo']?></div>
				</div>
<?php
	}
	if(@$_SESSION['clientID']=='' && @$noremember!=TRUE){ ?>
				<div class="checkoutadditionals">
				  <div class="cobhl cobhl2 cdformtremember"><input type="checkbox" name="remember" class="cdformcb cdformremember" value="1" <?php if($remember) print 'checked="checked"'?> /></div>
				  <div class="cobll cobll2 cdformremember"><?php print $GLOBALS['xxRemMe']?><br /><span class="ectsmallnote"><?php print $GLOBALS['xxOpCook']?></span></div>
				</div>
<?php
	}
	if(@$GLOBALS['nomailinglist']!=TRUE){ ?>
				<div class="checkoutadditionals">
				  <div class="cobhl cobhl2 cdformtmailing"><input type="checkbox" name="allowemail" class="cdformcb cdformmailing" value="ON" <?php if(@$allowemaildefaulton) print 'checked="checked"'?> /></div>
				  <div class="cobll cobll2 cdformmailing"><?php print $GLOBALS['xxAlPrEm']?><br /><span class="ectsmallnote"><?php print $GLOBALS['xxNevDiv']?></span></div>
				</div>
<?php
	}
	if(@$nogiftcertificate!=TRUE){ ?>
				<div><div class="cobhl cobhl2 cdformtcoupon"><?php print labeltxt($GLOBALS['xxGifNum'],'cpncode')?></div><div class="cobll cobll2 cdformcoupon">
			<div><input type="text" name="cpncode" class="cdforminput cdformcoupon" id="cpncode" size="<?php print $mobilebrowser?14:20?>" /> <?php print imageorbutton(@$imgapplycoupon,$GLOBALS['xxApply'],'applycoupon applycoupon2','applycert()',TRUE) ?></div>
		<div id="cpncodespan"><?php
		if(@$_SESSION['giftcerts']!='' || @$_SESSION['cpncode']!=''){
			print '<div style="display:table">';
			if(trim(@$_SESSION['giftcerts'])!=''){
				$gcarr=explode(' ', trim(@$_SESSION['giftcerts']));
				foreach($gcarr as $key => $value){
					print '<div style="display:table-row"><div style="display:table-cell">' . $GLOBALS['xxAppGC'] . '</div><div style="display:table-cell">' . $value . '</div><div style="display:table-cell">(<a href="#" onclick="return removecert(\''.$value.'\')">'.$GLOBALS['xxRemove'].'</a>)</div></div>';
				}
			}
			if(trim(@$_SESSION['cpncode'])!=''){
				$cpnarr=explode(' ', trim(@$_SESSION['cpncode']));
				foreach($cpnarr as $key => $value){
					print '<div style="display:table-row"><div style="display:table-cell">' . $GLOBALS['xxApdCpn'] . '</div><div style="display:table-cell">' . $value . '</div><div style="display:table-cell">(<a href="#" onclick="return removecert(\''.$value.'\')">'.$GLOBALS['xxRemove'].'</a>)</div></div>';
				}
			}
			print '</div>';
		} ?>
		</div>
		</div></div>
<?php
	}
	print whv('token', $token) . whv('payerid', $payerid) . whv('checktmplogin',getpost('checktmplogin'));
	if(! @isset($noemailgiftcertorders)) $noemailgiftcertorders='4';
	$sSQL="SELECT cartID FROM cart WHERE cartCompleted=0 AND (cartProdID='".$giftcertificateid."' OR cartProdID='".$donationid."') AND " . getsessionsql();
	$result=ect_query($sSQL) or ect_error();
	if(ect_num_rows($result)!=0 && $noemailgiftcertorders!='') $exclemail=$noemailgiftcertorders.','; else $exclemail='';
	ect_free_result($result);
	$sSQL='SELECT payProvID,'.getlangid('payProvShow',128).' FROM payprovider WHERE payProvEnabled=1 AND payProvLevel<=' . $minloglevel . (($ordPayProvider=='19' && getget('token')!='')?' AND payProvID=19 ':' AND NOT (payProvID IN ('.$exclemail.'19,20,21' . (@$paypalhostedsolution?',18':'') . '))') . ' ORDER BY payProvOrder';
	$result=ect_query($sSQL) or ect_error();
	if(($ppsuccess=ect_num_rows($result))==0){ ?>
				<div class="cobhl cart2column ectwarning"><?php print @$errormsg!=''?$errormsg:$GLOBALS['xxNoPay']?></div>
<?php
	}elseif(ect_num_rows($result)==1){
		$rs=ect_fetch_assoc($result);
		print whv('payprovider',$rs['payProvID']);
		$nodefaultpayprovider=FALSE;
		$payproviderradios='';
	}else{ ?>
				<div><div class="cobhl cobhl2 cdformtpayment"><?php print $GLOBALS['xxPlsChz']?></div>
				  <div class="cobll cobll2 cdformpayment"><?php
		if(@$payproviderradios==1 || @$payproviderradios==2){
			print '<div class="payprovider">';
			while($rs=ect_fetch_assoc($result)){
				print ($payproviderradios==1?'<div class="payprovline">':'').'<input type="radio" name="payprovider" class="cdformradio cdformpayment" value="' . $rs['payProvID'] . '"';
				if(@$ordPayProvider==$rs['payProvID'] || ($ordPayProvider=='' && @$nodefaultpayprovider!=TRUE)){ print ' checked="checked"'; $ordPayProvider='-1'; }
				print ' />' . $rs[getlangid('payProvShow',128)] . ($rs['payProvID']==1?'<img src="images/paypalacceptmark.gif" alt="PayPal Payments" />':'') . (@$payproviderradios==1?'</div>':'');
			}
			print '</div>';
		}else{
			print '<select name="payprovider" class="cdformselect cdformpayment" size="1">';
			if(@$nodefaultpayprovider==TRUE) print '<option value="">'.$GLOBALS['xxPlsSel'].'</option>';
			while($rs=ect_fetch_assoc($result)){
				print '<option value="' . $rs['payProvID'] . '"';
				if(@$ordPayProvider==$rs['payProvID']) print ' selected="selected"';
				print '>' . $rs[getlangid('payProvShow',128)] . '</option>';
			}
			print '</select>';
		} ?></div></div>
<?php
	}
	ect_free_result($result);
	if($ppsuccess){ ?>
				<div>
			      <div class="cobll cart2column checkoutbutton2"><?php print imageorsubmit($imgcheckoutbutton2,(@$GLOBALS['xxCOTxt2']!=''?$GLOBALS['xxCOTxt2']:$GLOBALS['xxCOTxt']),'checkoutbutton checkoutbutton2')?></div>
				</div><?php
	} ?>	  </div>
			</form>
<script type="text/javascript">/* <![CDATA[ */
var globcurobj=[];
function doshowshipform(itm){
	var elem=document.getElementsByTagName('div');
	for(var i=0; i<elem.length; i++){
		var classes=elem[i].className;
		var issel=((itm==2?document.getElementById('shipdiff').checked:globcurobj[itm].selectedIndex!=0||globcurobj[itm].options.length<=1)?'':'none');
		if(classes.indexOf((itm==1?'bill':'ship')+'formrow')!=-1){
			if(elem[i].style.display!=issel){
				elem[i].style.display=issel;
				setTimeout("doshowshipform("+itm+");", 30);
				return;
			}
		}
	}
}
function showshipform(itm,curobj){
	globcurobj[itm]=curobj;
	doshowshipform(itm);
}
var checkedfullname=false;
var checkaddress=true,scheckaddress=true;
var shipaddcan=false;
function unselectshipadd(){
	document.getElementById('shipdiff').checked=false;
	showshipform(2,document.getElementById('scountry'));
	shipaddcan=true;
}
function chkextra(isship,ob,fldtxt){
	var hasselected=false,fieldtype='';
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
		if(isship){if(!confirm("<?php print jscheck($GLOBALS['xxPlsEntr'].' "'.$GLOBALS['xxShpDet'].' / ')?>"+fldtxt+"\".\n\n<?php print jscheck($GLOBALS['xxNoShip'])?>"))unselectshipadd();
		}else alert("<?php print jscheck($GLOBALS['xxPlsEntr'])?> \""+fldtxt+"\".");
		return(false);
	}
	return(true);
}
function setdefs(ob,deftxt){
	var fieldtype='';
	if(ob)fieldtype=(ob.type?ob.type:'radio');<?php if(@$debugmode) print 'else alert("Extra order field id not found");'?>
	if(fieldtype=='text'||fieldtype=='textarea'||fieldtype=='password'){
		ob.value=deftxt;
	}else if(fieldtype=='select-one'){
		for(var ii=0;ii<ob.length;ii++)if(ob[ii].value==deftxt)ob[ii].selected=true;
	}else if(fieldtype=='radio'){
		for(var ii=0;ii<ob.length;ii++)if(ob[ii].value==deftxt)ob[ii].checked=true;
	}else if(fieldtype=='checkbox'){
		if(ob.value==deftxt)ob.checked=true;
	}
}
function zipoptional(cntobj){
var cntid=cntobj[cntobj.selectedIndex].value;
if(cntid==85 || cntid==91 || cntid==154 || cntid==200)return true; else return false;
}
function stateoptional(cntobj){
var cntid=cntobj[cntobj.selectedIndex].value;
if(false<?php
$result=ect_query('SELECT countryID FROM countries WHERE countryEnabled<>0 AND loadStates<0') or ect_error();
while($rs=ect_fetch_assoc($result)) print '||cntid==' . $rs['countryID'];
ect_free_result($result);
?>)return true; else return false;
}
<?php	if($numaddresses==0){
			if(trim(@$extraorderfield1)!='' && trim(@$ordExtra1)!='') print "setdefs(document.forms.mainform.ordextra1,'".jsspecials($ordExtra1)."');\r\n";
			if(trim(@$extraorderfield2)!='' && trim(@$ordExtra2)!='') print "setdefs(document.forms.mainform.ordextra2,'".jsspecials($ordExtra2)."');\r\n";
			if(@$noshipaddress!=TRUE){
				if(trim(@$extraorderfield1)!='' && trim(@$ordShipExtra1)!='') print "setdefs(document.forms.mainform.ordsextra1,'".jsspecials($ordShipExtra1)."');\r\n";
				if(trim(@$extraorderfield2)!='' && trim(@$ordShipExtra2)!='') print "setdefs(document.forms.mainform.ordsextra2,'".jsspecials($ordShipExtra2)."');\r\n";
			}
		}
		if(trim(@$extracheckoutfield1)!='' && trim(@$ordCheckoutExtra1)!='') print "setdefs(document.forms.mainform.ordcheckoutextra1,'".jsspecials($ordCheckoutExtra1)."');\r\n";
		if(trim(@$extracheckoutfield2)!='' && trim(@$ordCheckoutExtra2)!='') print "setdefs(document.forms.mainform.ordcheckoutextra2,'".jsspecials($ordCheckoutExtra2)."');\r\n"; ?>
function chkfocus(tobj,ttxt){
	alert(ttxt);
	tobj.focus();
	return false;
}
function checkform(frm){
	var cntelem=document.getElementById('country');
	var scntelem=document.getElementById('scountry');
	shipaddcan=false;
if(checkaddress){
if(frm.country[frm.country.selectedIndex].value=='') return(chkfocus(frm.country,"<?php print jscheck($GLOBALS['xxPlsSlct'] . ' ' . $GLOBALS['xxCountry'])?>"));
<?php	if(trim(@$extraorderfield1)!='' && @$extraorderfield1required==TRUE) print 'if(!chkextra(false,frm.ordextra1,"'.jsspecials(strip_tags($extraorderfield1)).'"))return(false);'."\r\n";?>
if(frm.name.value==""||frm.name.value=="<?php print $GLOBALS['xxFirNam']?>") return(chkfocus(frm.name,"<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . (@$usefirstlastname ? $GLOBALS['xxFirNam'] : $GLOBALS['xxName']))?>\"."));
<?php	if(@$usefirstlastname==TRUE){ ?>
if(frm.lastname.value==""||frm.lastname.value=="<?php print $GLOBALS['xxLasNam']?>") return(chkfocus(frm.lastname,"<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxLasNam'])?>\"."));
<?php	}else{ ?>
var regex=/ /;
if(!checkedfullname && !regex.test(frm.name.value)){
	alert("<?php print jscheck($GLOBALS['xxFulNam'] . ' "' . $GLOBALS['xxName'])?>\".");
	frm.name.focus();
	checkedfullname=true;
	return(false);
}
<?php	}
		if(! is_array($addresses)){ ?>
var regex=/[^@]+@[^@]+\.[a-z]{2,}$/i;
if(!regex.test(frm.email.value)){
	alert("<?php print jscheck($GLOBALS['xxValEm'])?>");
	frm.email.focus();
	return(false);
}
<?php	} ?>
if(frm.address.value=="") return(chkfocus(frm.address,"<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxAddress'])?>\"."));
if(frm.city.value=="") return(chkfocus(frm.city,"<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxCity'])?>\"."));
	if(stateoptional(cntelem)){
	}else if(stateselectordisabled[0]==false){
<?php	if($hasstates){ ?>
		if(frm.state.selectedIndex==0) return(chkfocus(frm.state,"<?php print jscheck($GLOBALS['xxPlsSlct']) . ' '?>"+document.getElementById('statetxt').innerHTML));
<?php	} ?>
	}else if(frm.state2.value=="")
		return(chkfocus(frm.state2,"<?php print jscheck($GLOBALS['xxPlsEntr'])?> \""+document.getElementById('statetxt').innerHTML+"\"."));
if(frm.zip.value==""&&!zipoptional(cntelem)) return(chkfocus(frm.zip,"<?php print jscheck($GLOBALS['xxPlsEntr'])?> \""+getziptext(cntelem[cntelem.selectedIndex].value)+"\"."));
if(frm.phone.value=="") return(chkfocus(frm.phone,"<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxPhone'])?>\"."));
<?php if(trim(@$extraorderfield2)!='' && @$extraorderfield2required==TRUE) print 'if(!chkextra(false,frm.ordextra2,"'.jsspecials(strip_tags($extraorderfield2)).'"))return(false);'."\r\n";?>
}
<?php
if(abs(@$addshippinginsurance)==2 && @$GLOBALS['forceinsuranceselection']){ ?>
if(frm.wantinsurance.selectedIndex==0){
	alert("<?php print jscheck(strip_tags(str_replace('<br />',"\n",$GLOBALS['xxChoIns'])))?>");
	frm.wantinsurance.focus();
	return(false);
}
<?php
}
if(@$noshipaddress!=TRUE){ ?>
function chkconfship(tobj,ttxt){
	if(!confirm(ttxt)){
		unselectshipadd();
		return(true);
	}else
		tobj.focus();
	return(false);
}
var xxnoship="\n\n<?php print jscheck($GLOBALS['xxNoShip'])?>";
if(scheckaddress&&document.getElementById('shipdiff').checked){
	if(frm.scountry[frm.scountry.selectedIndex].value==''&&!chkconfship(frm.country,"<?php print jscheck($GLOBALS['xxPlsSlct'].' "'.$GLOBALS['xxShpDet'].' / '.$GLOBALS['xxCountry'])?>\"."+xxnoship)) return false;
<?php	if(trim(@$extraorderfield1)!='' && @$extraorderfield1required==TRUE) print 'if(!chkextra(true,frm.ordsextra1,"'.jsspecials(strip_tags($extraorderfield1)).'"))return(false);'."\r\n";?>
	if(!shipaddcan&&(frm.sname.value==""||frm.sname.value=="<?php print $GLOBALS['xxFirNam']?>")&&!chkconfship(frm.sname,"<?php print jscheck($GLOBALS['xxPlsEntr'].' "'.$GLOBALS['xxShpDet'].' / '.$GLOBALS['xxName'])?>\"."+xxnoship)) return false;
	if(!shipaddcan&&frm.saddress.value==""&&!chkconfship(frm.saddress,"<?php print jscheck($GLOBALS['xxPlsEntr'].' "'.$GLOBALS['xxShpDet'].' / '.$GLOBALS['xxAddress'])?>\"."+xxnoship)) return false;
	if(!shipaddcan&&frm.scity.value==""&&!chkconfship(frm.scity,"<?php print jscheck($GLOBALS['xxPlsEntr'].' "'.$GLOBALS['xxShpDet'].' / '.$GLOBALS['xxCity'])?>\"."+xxnoship)) return false;
	if(stateoptional(scntelem)){
	}else if(stateselectordisabled[1]==false){
<?php	if($hasstates){ ?>
		if(!shipaddcan&&frm.sstate.selectedIndex==0&&!chkconfship(frm.sstate,"<?php print jscheck($GLOBALS['xxPlsSlct'].' "'.$GLOBALS['xxShpDet'].' / ')?>"+document.getElementById('sstatetxt').innerHTML+"\"."+xxnoship)) return false;
<?php	} ?>
	}else if(!shipaddcan&&frm.sstate2.value==""&&!chkconfship(frm.sstate2,"<?php print jscheck($GLOBALS['xxPlsEntr'].' "'.$GLOBALS['xxShpDet'].' / ')?>"+document.getElementById('sstatetxt').innerHTML+"\"."+xxnoship))
		return false;
	if(!shipaddcan&&frm.szip.value==""&&!zipoptional(scntelem)&&!chkconfship(frm.szip,"<?php print jscheck($GLOBALS['xxPlsEntr'].' "'.$GLOBALS['xxShpDet'].' / ')?> \""+getziptext(scntelem[scntelem.selectedIndex].value)+"\"."+xxnoship)) return false;
<?php	if(trim(@$extraorderfield2)!='' && @$extraorderfield2required==TRUE) print 'if(!chkextra(true,frm.ordsextra2,"'.jsspecials(strip_tags($extraorderfield2)).'"))return(false);'."\r\n";?>
}
<?php }
		if(trim(@$extracheckoutfield1)!='' && @$extracheckoutfield1required==TRUE) print 'if(!chkextra(false,frm.ordcheckoutextra1,"'.jsspecials(strip_tags($extracheckoutfield1)).'"))return(false);'."\r\n";
		if(trim(@$extracheckoutfield2)!='' && @$extracheckoutfield2required==TRUE) print 'if(!chkextra(false,frm.ordcheckoutextra2,"'.jsspecials(strip_tags($extracheckoutfield2)).'"))return(false);'."\r\n";
		if(@$_SESSION['clientID']=='' && @$noremember!=TRUE){ ?>
if(frm.remember.checked==false&&!shipaddcan){
	if(confirm("<?php print jscheck($GLOBALS['xxWntRem'])?>")){
		frm.remember.checked=true
	}
}
<?php	}
		if(@$GLOBALS['termsandconditions']){ ?>
if(frm.license.checked==false&&!shipaddcan){
	alert("<?php print jscheck($GLOBALS['xxPlsProc'])?>");
	frm.license.focus();
	return(false);
}
<?php	}
		if(@$payproviderradios!=''){ ?>
hasselected=false;
for(var ii=0;ii<frm.payprovider.length;ii++)if(frm.payprovider[ii].checked)hasselected=true;
if(!hasselected){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxPlsChz'])?>\".");
	return(false);
}
<?php	}elseif(@$nodefaultpayprovider){ ?>
if(frm.payprovider.selectedIndex==0) return(chkfocus(frm.payprovider,"<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxPlsChz'])?>\"."));
<?php	} ?>
return(!shipaddcan);
}
<?php if(@$GLOBALS['termsandconditions']){ ?>
function showtermsandconds(){
newwin=window.open("termsandconditions.php","Terms","menubar=no, scrollbars=yes, width=420, height=380, directories=no,location=no,resizable=yes,status=no,toolbar=no");
}
<?php } ?>
var savestate=0;
var ssavestate=0;
function applycertcallback(){
	if(ajaxobj.readyState==4){
		document.getElementById("cpncodespan").innerHTML=ajaxobj.responseText;
	}
}
function applycert(){
	cpncode=document.getElementById("cpncode").value;
	if(cpncode!=""){
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange=applycertcallback;
		document.getElementById("cpncodespan").innerHTML="<?php print $GLOBALS['xxAplyng']?>...";
		ajaxobj.open("GET", "vsadmin/ajaxservice.php?action=applycert&cpncode="+cpncode, true);
		ajaxobj.send(null);
	}
}
function removecert(cpncode){
	if(cpncode!=""){
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange=applycertcallback;
		document.getElementById("cpncodespan").innerHTML="<?php print $GLOBALS['xxDeltng']?>...";
		ajaxobj.open("GET", "vsadmin/ajaxservice.php?action=applycert&act=delete&cpncode="+cpncode, true);
		ajaxobj.send(null);
		document.getElementById("cpncode").value="";
	}
	return false;
}
function dosavestate(shp){
	thestate=eval('document.forms.mainform.'+shp+'state');
	eval(shp+'savestate=thestate.selectedIndex');
}
function checkoutspan(shp){
	document.getElementById(shp+'zipstar').style.display=(zipoptional(document.getElementById(shp+'country'))?'none':'');
	document.getElementById(shp+'statestar').style.display=(stateoptional(document.getElementById(shp+'country'))?'none':'');
<?php
	if($hasstates) print "thestate=document.getElementById(shp+'state');\r\n";
	print "dynamiccountries(document.getElementById(shp+'country'),shp);\r\n";
	print "if(stateselectordisabled[shp=='s'?1:0]==false&&!stateoptional(document.getElementById(shp+'country'))){\r\n";
	print "if(document.getElementById(shp+'state2'))document.getElementById(shp+'state2').style.display='none';\r\n";
	if($hasstates){
		print "thestate.disabled=false;\r\n";
		print "eval('thestate.selectedIndex='+shp+'savestate');\r\n";
		print "document.getElementById(shp+'state').style.display='';\r\n";
	} ?>
}else{<?php
	print "if(document.getElementById(shp+'state2'))document.getElementById(shp+'state2').style.display='';\r\n";
	if($hasstates){ ?>
		document.getElementById(shp+'state').style.display='none';
		if(thestate.disabled==false){
		thestate.disabled=true;
		eval(shp+'savestate=thestate.selectedIndex');
		thestate.selectedIndex=0;}
<?php
	} ?>
}}
<?php
	createdynamicstates('SELECT stateAbbrev,stateName,stateName2,stateName3,stateCountryID,countryName FROM states INNER JOIN countries ON states.stateCountryID=countries.countryID WHERE countryEnabled<>0 AND stateEnabled<>0 AND loadStates=2 ORDER BY stateCountryID,' . getlangid('stateName',1048576));
	if(is_array($addresses)) print "checkaddress=false;scheckaddress=false;\r\n";
	if(is_array($addresses) && @$noshipaddress!=TRUE) print "checkeditbutton('s');";
	print "checkoutspan('');\r\n";
	if(@$noshipaddress!=TRUE) print "checkoutspan('s');\r\n";
	print "setinitialstate('');setinitialstate('s');\r\n";
	if($numaddresses==0){
		print "showshipform(1,document.getElementById('country'));\r\n";
		if(@$noshipaddress!=TRUE) print "showshipform(2,document.getElementById('scountry'));\r\n";
	}
?>/* ]]> */</script><?php
}elseif(getpost('mode')=='authorize'){
	$iframe=$ordauthstatus='';
	$blockuser=checkuserblock('');
	$ordID=escape_string(str_replace("'",'',getpost('ordernumber')));
	$vsRESULT='x';
	$vsRESPMSG=$vsAVSADDR=$vsAVSZIP=$vsTRANSID='';
	$centinelenrolled='N';
	if(is_numeric($ordID) && is_numeric(getpost('method'))){
		if(getpayprovdetails(getpost('method'),$data1,$data2,$data3,$demomode,$ppmethod)){
			$sSQL="SELECT ordID,ordAuthStatus FROM orders WHERE ordID='" . escape_string($ordID) . "' AND " . getordersessionsql();
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)) $ordauthstatus=$rs['ordAuthStatus']; else $ordID=0;
			ect_free_result($result);
			$centinelerror=@$_SESSION['ErrorDesc'];
			eval('$authorizeextraparams=@$authorizeextraparams' . (int)getpost('method') . ';');
			if(getpost('method')=='14' && @$custompp3ds!=TRUE) $cardinalprocessor='';
		}else
			$ordID=0;
	}else
		$ordID=0;
	if($ordID!=0 && $ordauthstatus!='MODWARNOPEN' && @$cardinalprocessor!='' && @$cardinalmerchant!='' && @$cardinalpwd!='' && @$_SESSION['centinelok']==''){
		$cardnum=str_replace(' ', '', getpost('ACCT'));
		$exmon=getpost('EXMON');
		$exyear=getpost('EXYEAR');
		$cardname=getpost('cardname');
		$cvv2=getpost('CVV2');
		$issuenum=getpost('IssNum');
		$sSQL="SELECT ordID,ordName,ordLastName,ordCity,ordState,ordCountry,ordPhone,ordHandling,ordZip,ordEmail,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordTotal,ordDiscount,ordAddress,ordAddress2,ordIP,ordAuthNumber,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipCountry,ordShipZip FROM orders WHERE ordID='" . escape_string($ordID) . "'";
		$result=ect_query($sSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		$sXML='<CardinalMPI>' .
			addtag('Version','1.7') . addtag('MsgType','cmpi_lookup') . addtag('ProcessorId',$cardinalprocessor) . addtag('MerchantId',$cardinalmerchant) . addtag('TransactionPwd',$cardinalpwd) . addtag('TransactionType','C') .
			addtag('Amount',(int)((($rs['ordShipping']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordHSTTax']+$rs['ordTotal']+$rs['ordHandling']+0.001)-$rs['ordDiscount'])*100)) .
			addtag('CurrencyCode',$countryNumCurrency) . addtag('OrderNumber',$ordID) . addtag('OrderDescription','Order id ' . $ordID) . addtag('EMail',$rs['ordEmail']) .
			addtag('UserAgent',@$_SERVER['HTTP_USER_AGENT']) . addtag('BrowserHeader',@$_SERVER['HTTP_ACCEPT']) . addtag('IPAddress',$REMOTE_ADDR) .
			addtag('CardNumber',$cardnum) . addtag('CardExpMonth',$exmon) . addtag('CardExpYear',(strlen($exyear)==2?'20':'').$exyear) .
			'</CardinalMPI>';
		ect_free_result($result);
		$theurl='https://'.(getpost('method')=='7'||getpost('method')=='18'?'paypal':'centinel400').'.cardinalcommerce.com/maps/txns.asp';
		if(@$cardinaltestmode) $theurl='https://centineltest.cardinalcommerce.com/maps/txns.asp';
		if(@$cardinalurl!='') $theurl=$cardinalurl;
		if(callcurlfunction($theurl, 'cmpi_msg=' . urlencode($sXML), $res, '', $errormsg, 12)){
			$xmlDoc=new vrXMLDoc($res);
			$nodeList=$xmlDoc->nodeList->childNodes[0];
			for($i=0; $i < $nodeList->length; $i++){
				if($nodeList->nodeName[$i]=='ACSUrl') $acsurl=$nodeList->nodeValue[$i];
				if($nodeList->nodeName[$i]=='Payload') $_SESSION['cardinal_pareq']=$nodeList->nodeValue[$i];
				if($nodeList->nodeName[$i]=='Enrolled'){ $centinelenrolled=$nodeList->nodeValue[$i]; $_SESSION['centinel_enrolled']=$centinelenrolled; }
				if($nodeList->nodeName[$i]=='OrderId') $_SESSION['cardinal_orderid']=$nodeList->nodeValue[$i];
				if($nodeList->nodeName[$i]=='TransactionId') $_SESSION['cardinal_transaction']=$nodeList->nodeValue[$i];
				if($nodeList->nodeName[$i]=='EciFlag') $_SESSION['EciFlag']=$nodeList->nodeValue[$i];
				if($nodeList->nodeName[$i]=='ErrorDesc') $centinelerror=$nodeList->nodeValue[$i];
				if($nodeList->nodeName[$i]=='ErrorNo' && $nodeList->nodeValue[$i]=='1360'){ $centinelerror=''; break; }
			}
			if($centinelenrolled=='Y'){
				$_SESSION['cardinal_method']=getpost('method');
				$_SESSION['cardinal_ordernum']=$ordID;
				$_SESSION['cardinal_sessionid']=$thesessionid;
				$_SESSION['cardinal_cardnum']=$cardnum;
				$_SESSION['cardinal_exmon']=getpost('EXMON');
				$_SESSION['cardinal_exyear']=getpost('EXYEAR');
				$_SESSION['cardinal_cardname']=getpost('cardname');
				$_SESSION['cardinal_cvv2']=getpost('CVV2');
				$_SESSION['cardinal_issnum']=getpost('IssNum');
				print '<div style="font-weight:bold;padding:5px;margin:5px;text-align:center;">' . $GLOBALS['xxComOrd'] . '<br /><br />' . $GLOBALS['xxNoBack'] . '<br /><br /><iframe id="centinelwin" src="vsadmin/ajaxservice.php?action=centinel&url='.urlencode($acsurl).'" width="440" height="400">Browser error.</iframe></div>';
			}
		}
	}elseif($ordID!=0 && @$_SESSION['centinelok']=='Y'){
		$cardnum=$_SESSION['cardinal_cardnum'];
		$exmon=$_SESSION['cardinal_exmon'];
		$exyear=$_SESSION['cardinal_exyear'];
		$cardname=$_SESSION['cardinal_cardname'];
		$cvv2=$_SESSION['cardinal_cvv2'];
		$issuenum=$_SESSION['cardinal_issnum'];
		$_SESSION['cardinal_cardnum']=NULL; unset($_SESSION['cardinal_cardnum']);
		$_SESSION['cardinal_exmon']=NULL; unset($_SESSION['cardinal_exmon']);
		$_SESSION['cardinal_exyear']=NULL; unset($_SESSION['cardinal_exyear']);
		$_SESSION['cardinal_cardname']=NULL; unset($_SESSION['cardinal_cardname']);
		$_SESSION['cardinal_cvv2']=NULL; unset($_SESSION['cardinal_cvv2']);
		$_SESSION['cardinal_issnum']=NULL; unset($_SESSION['cardinal_issnum']);
	}elseif($ordID!=0){
		$cardnum=str_replace(' ', '', getpost('ACCT'));
		$exmon=getpost('EXMON');
		$exyear=getpost('EXYEAR');
		$cardname=getpost('cardname');
		$cvv2=getpost('CVV2');
		$issuenum=getpost('IssNum');
	}
	if($ordID==0||$ordauthstatus=='MODWARNOPEN'){
		if($ordauthstatus=='MODWARNOPEN') $vsRESPMSG=$GLOBALS['xxMoWnRC']; else $vsRESPMSG='Error';
	}elseif($centinelenrolled=='Y'){
		// Do Nothing
	}elseif(@$_SESSION['centinelok']=='N'||$centinelerror!=''){
		$vsRESPMSG=($centinelerror!=''?$centinelerror.'<br />':'').$GLOBALS['xx3DSFai'];
	}elseif(getpost('method')=='7'||getpost('method')=='8'||getpost('method')=='22'){ // Payflow Pro / Payflow Link / PayPal Advanced
		$vsdetails=explode('&', $data1);
		$vs1=@$vsdetails[0];
		$vs2=@$vsdetails[1];
		$vs3=@$vsdetails[2];
		$vs4=@$vsdetails[3];
		$sSQL="SELECT ordName,ordLastName,ordZip,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordHandling,ordTotal,ordDiscount,ordAddress,ordAddress2,ordCity,ordState,ordCountry,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipCountry,ordShipZip,ordAuthNumber,ordEmail FROM orders WHERE ordID='" . $ordID . "'";
		$result=ect_query($sSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		$vsAUTHCODE=$rs['ordAuthNumber'];
		$sSQL="SELECT countryID,countryCode FROM countries WHERE countryName='" . escape_string($rs['ordCountry']) . "'";
		$result2=ect_query($sSQL) or ect_error();
		if($rs2=ect_fetch_assoc($result2)){
			$countryid=$rs2['countryID'];
			$countryCode=$rs2['countryCode'];
			$homecountry=($countryid==$origCountryID);
		}
		ect_free_result($result2);
		$sSQL="SELECT countryCode FROM countries WHERE countryName='" . (trim($rs['ordShipAddress'])!='' ? escape_string($rs['ordShipCountry']) : escape_string($rs['ordCountry'])) . "'";
		$result2=ect_query($sSQL) or ect_error();
		if($rs2=ect_fetch_assoc($result2))
			$shipCountryCode=$rs2['countryCode'];
		ect_free_result($result2);
		if(trim($rs['ordShipAddress'])!='') $isshp='Ship'; else $isshp='';
		$ordState=$rs['ordState'];
		$ordShipState=$rs['ord'.$isshp.'State'];
		if(($countryid==1 || $countryid==2) && $homecountry && @$usestateabbrev!=TRUE){
			$ordState=getstateabbrev($ordState);
			$ordShipState=getstateabbrev($ordShipState);
		}
		splitname(getpost('method')=='8'||getpost('method')=='22'?trim($rs['ordName'].' '.$rs['ordLastName']):$cardname, $firstname, $lastname);
		splitname(trim($rs['ord'.$isshp.'Name'].' '.$rs['ord'.$isshp.'LastName']), $shipfirstname, $shiplastname);
		$sXML='PARTNER='.$vs3.'&VENDOR='.$vs2.'&TRXTYPE='.($ppmethod==1?'A':'S').'&TENDER=C&ZIP['.strlen($rs['ordZip']).']='.$rs['ordZip'].'&STREET['.strlen($rs['ordAddress']).']='.$rs['ordAddress']. ($rs['ordAddress2']!='' ? '&STREET2['.strlen($rs['ordAddress2']).']='.$rs['ordAddress2'] : '') . '&CITY['.strlen($rs['ordCity']).']='.$rs['ordCity'].'&STATE['.strlen($ordState).']='.$ordState.'&BILLTOCOUNTRY['.strlen($countryCode).']='.$countryCode.'&FIRSTNAME['.strlen($firstname).']='.$firstname.'&LASTNAME['.strlen($lastname).']='.$lastname.'&EMAIL='.$rs['ordEmail'];
		$sXML.='&SHIPTOZIP['.strlen($rs['ord'.$isshp.'Zip']).']='.$rs['ord'.$isshp.'Zip'].'&SHIPTOSTREET['.strlen($rs['ord'.$isshp.'Address']).']='.$rs['ord'.$isshp.'Address']. ($rs['ord'.$isshp.'Address2']!='' ? '&SHIPTOSTREET2['.strlen($rs['ord'.$isshp.'Address2']).']='.$rs['ord'.$isshp.'Address2'] : '') . '&SHIPTOCITY['.strlen($rs['ord'.$isshp.'City']).']='.$rs['ord'.$isshp.'City'].'&SHIPTOSTATE['.strlen($ordShipState).']='.$ordShipState.'&SHIPTOCOUNTRYCODE['.strlen($shipCountryCode).']='.$shipCountryCode.'&SHIPTOCOUNTRY['.strlen($shipCountryCode).']='.$shipCountryCode.'&SHIPTOFIRSTNAME['.strlen($shipfirstname).']='.$shipfirstname.'&SHIPTOLASTNAME['.strlen($shiplastname).']='.$shiplastname;
		if($issuenum!=''){
			if(strlen($issuenum)==2) $sXML.='&CARDISSUE=' . $issuenum; else $sXML.='&CARDSTART=' . $issuenum;
		}
		$sXML.='&CUSTIP=' . $REMOTE_ADDR . '&PWD=' . $vs4 . '&USER=' . $vs1 . '&CURRENCY=' . $countryCurrency . '&AMT=' . number_format(($rs['ordShipping']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordHSTTax']+$rs['ordTotal']+$rs['ordHandling'])-$rs['ordDiscount'],2,'.','') . '&BUTTONSOURCE=EcommerceTemplatesUS_Cart_PPA' . $authorizeextraparams;
		ect_free_result($result);
		if(getpost('method')=='8'||getpost('method')=='22'){
			$securetokenid='ECTP' . md5($ordID . time() . $adminSecret . $vs4 . $lastname . $firstname);
			$sXML.='&INVNUM=' . $ordID . '&RETURNURL='.$storeurlssl.'thanks.php&ERRORURL='.$storeurlssl.'thanks.php&NOTIFYURL='.$storeurlssl.'vsadmin/ppconfirm.php&CREATESECURETOKEN=Y&TEMPLATE=MINLAYOUT&DISABLERECEIPT=TRUE&SECURETOKENID=' . $securetokenid;
			$success=callcurlfunction('https://' . ($demomode?'pilot-':'') . 'payflowpro.paypal.com', $sXML, $curString, '', $errormsg, TRUE);
			$resparr=explode('&',$curString);
			foreach($resparr as $val){
				$itemarr=explode('=',$val);
				if($itemarr[0]=='SECURETOKEN') $SECURETOKEN=$itemarr[1];
				if($itemarr[0]=='RESPMSG') $RESPMSG=$itemarr[1];
			}
			if($RESPMSG=='Approved'){
				$iframe='<iframe style="border:none" src="https://payflowlink.paypal.com/?SECURETOKEN=' . $SECURETOKEN . '&SECURETOKENID=' . $securetokenid . ($demomode?'&MODE=test':'') . '" width="510" height="610"></iframe>';
				print $iframe;
			}else
				$vsRESPMSG=$RESPMSG;
		}else{
			$sXML.='&COMMENT1=' . $ordID . '&ACCT=' . $cardnum . '&CVV2=' . $cvv2 . '&EXPDATE=' . $exmon . substr($exyear, -2);
			if(@$cardinalprocessor!='' && @$cardinalmerchant!='' && @$cardinalpwd!=''){
				$sXML.='&AUTHSTATUS3DS=' . @$_SESSION['PAResStatus'] . '&MPIVENDOR3DS=' . @$_SESSION['centinel_enrolled'] . '&CAVV=' . @$_SESSION['Cavv'] . '&ECI=' . @$_SESSION['EciFlag'] . '&XID=' . @$_SESSION['Xid'];
			}
			if($vsAUTHCODE==''){
				$xmlfnheaders=array('X-VPS-REQUEST-ID: ' . $ordID.'.'.rand(1000000,9999999));
				$success=callcurlfunction('https://' . ($demomode?'pilot-':'') . 'payflowpro.paypal.com', $sXML, $curString, '', $vsRESPMSG, TRUE);
				if(!is_array($curString)){
					$curStringArr=array();
					while(strlen($curString)!=0){
						if(strpos($curString,'&')!==FALSE) $varString=substr($curString, 0, strpos($curString , "&" )); else $varString=$curString;
						$name=substr($varString, 0, strpos($varString,'='));
						$curStringArr[$name]=substr($varString, (strlen($name)+1) - strlen($varString));
						if(strlen($curString)!=strlen($varString)) $curString=substr($curString,  (strlen($varString)+1) - strlen($curString)); else $curString='';
					}
					$curString=$curStringArr;
				}
				$vsRESULT=$curString['RESULT'];
				$vsRESPMSG=@$curString['RESPMSG'];
				$vsAUTHCODE=@$curString['AUTHCODE'];
				if(array_key_exists('PNREF', $curString))$vsTRANSID=$curString['PNREF'];
				if(array_key_exists('PPREF', $curString))$vsTRANSID=$curString['PPREF'];
				$vsAVSADDR=@$curString['AVSADDR'];
				if(array_key_exists('AVSCODE', $curString))$vsAVSADDR=$curString['AVSCODE'];
				$vsAVSZIP=@$curString['AVSZIP'];
				$vsIAVS=@$curString['IAVS'];
				$vsCVV2=@$curString['CVV2MATCH'];
				if(array_key_exists('ACK', $curString)){
					if($curString['ACK']=='Success'){ $vsRESULT='0'; $vsRESPMSG=$GLOBALS['xxTranAp']; } else $vsRESULT='';
				}
				if(array_key_exists('L_LONGMESSAGE0', $curString))
					$vsRESPMSG=urldecode($curString['L_LONGMESSAGE0']);
				if(array_key_exists('L_ERRORCODE0', $curString))
					$vsERRCODE=$curString['L_ERRORCODE0'];
				if(array_key_exists('DUPLICATE', $curString)){
					if($curString['DUPLICATE']=='1'){ $vsRESULT=''; $vsRESPMSG='DUPLICATE'; $success=FALSE; }
				}
				if($vsRESULT=='0' || $vsRESULT=='126'){
					if($vsRESULT=='126'){ $underreview='Fraud Review:<br />';$vsRESPMSG='Approved'; }else $underreview='';
					ect_query("UPDATE cart SET cartDateAdded='" . date('Y-m-d',time()+($dateadjust*60*60)) . "',cartCompleted=1 WHERE cartCompleted<>1 AND cartOrderID='" . $ordID . "'") or ect_error();
					ect_query("UPDATE orders SET ordStatus=3,ordAuthStatus='',ordAVS='" . escape_string($vsAVSADDR . $vsAVSZIP) . "',ordCVV='" . escape_string($vsCVV2) . "',ordAuthNumber='" . escape_string($underreview . $vsAUTHCODE) . "',ordTransID='" . escape_string($vsTRANSID) . "',ordDate='" . date('Y-m-d H:i:s',time()+($dateadjust*60*60)) . "' WHERE ordAuthNumber='' AND ordID='" . $ordID . "'") or ect_error();
					do_order_success($ordID,$emailAddr,$sendEmail,FALSE,TRUE,TRUE,TRUE);
					$vsRESULT='0';
				}
			}else{
				$vsRESULT='0';
				$vsRESPMSG='Approved';
			}
		}
	}elseif(getpost('method')=='13'){ // Auth.net AIM
		if(@$secretword!=''){
			$data1=upsdecode($data1, $secretword);
			$data2=upsdecode($data2, $secretword);
		}
		$sSQL="SELECT ordID,ordStatus,ordCity,ordState,ordCountry,ordPhone,ordHandling,ordZip,ordEmail,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordTotal,ordDiscount,ordAddress,ordAddress2,ordIP,ordAuthNumber,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipCountry,ordShipZip FROM orders WHERE ordID='" . $ordID . "'";
		$result=ect_query($sSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		ect_free_result($result);
		$vsAUTHCODE=trim($rs['ordAuthNumber']);
		$ordstatus=trim($rs['ordStatus']);
		$sXML='x_version=3.1&x_delim_data=TRUE&x_relay_response=FALSE&x_delim_char=|&x_duplicate_window=15';
		$sXML.='&x_login=' . $data1 . '&x_tran_key=' . $data2 . (@$_SESSION['clientID']!='' ? '&x_cust_id=' . $_SESSION['clientID'] : '') . '&x_invoice_num=' . $rs['ordID'];
		$sXML.='&x_amount=' . number_format(($rs['ordShipping']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordHSTTax']+$rs['ordTotal']+$rs['ordHandling'])-$rs['ordDiscount'],2,'.','');
		$sXML.='&x_currency_code=' . $countryCurrency . '&x_description=' . substr(urlencode(str_replace('&quot;','"',getpost('description'))),0,255);
		if(getpost('accountnum')!=''){
			$sXML.='&x_method=ECHECK&x_echeck_type=WEB&x_recurring_billing=NO';
			$sXML.='&x_bank_acct_name=' . urlencode(getpost('accountname')) . '&x_bank_acct_num=' . urlencode(getpost('accountnum'));
			$sXML.='&x_bank_name=' . urlencode(getpost('bankname')) . '&x_bank_aba_code=' . urlencode(getpost('routenumber'));
			$sXML.='&x_bank_acct_type=' . urlencode(getpost('accounttype')) . '&x_type=AUTH_CAPTURE';
			if(@$wellsfargo==TRUE){
				$sXML.='&x_customer_organization_type=' . getpost('orgtype');
				if(getpost('taxid')!='')
					$sXML.='&x_customer_tax_id=' . urlencode(getpost('taxid'));
				else
					$sXML.='&x_drivers_license_num=' . urlencode(getpost('licensenumber')) . '&x_drivers_license_state=' . urlencode(getpost('licensestate')) . '&x_drivers_license_dob=' . urlencode(getpost('dldobyear') . '/' . getpost('dldobmon') . '/' . getpost('dldobday'));
			}
		}else{
			$sXML.='&x_method=CC&x_card_num=' . urlencode($cardnum) . '&x_exp_date=' . $exmon . $exyear;
			if($cvv2!='') $sXML.='&x_card_code=' . $cvv2;
			if($ppmethod==1) $sXML.='&x_type=AUTH_ONLY'; else $sXML.='&x_type=AUTH_CAPTURE';
		}
		if(@$cardinalprocessor!='' && @$cardinalmerchant!='' && @$cardinalpwd!=''){
			$sXML.='&x_cardholder_authentication_value=' . urldecode(@$_SESSION['Cavv']) . '&x_authentication_indicator=' . (int)@$_SESSION['EciFlag'];
		}
		if($cardname!=''){
			if(strstr($cardname,' ')){
				$namearr=explode(' ',$cardname,2);
				$sXML.='&x_first_name=' . urlencode($namearr[0]) . '&x_last_name=' . urlencode($namearr[1]);
			}else
				$sXML.='&x_last_name=' . urlencode($cardname);
		}
		$sXML.='&x_address=' . urlencode($rs['ordAddress']);
		if($rs['ordAddress2']!='') $sXML.=urlencode(', ' . $rs['ordAddress2']);
		$sXML.='&x_city=' . urlencode($rs['ordCity']) . '&x_state=' . urlencode($rs['ordState']) . '&x_zip=' . urlencode($rs['ordZip']) . '&x_country=' . urlencode($rs['ordCountry']) . '&x_phone=' . urlencode($rs['ordPhone']) . '&x_email=' . urlencode($rs['ordEmail']);
		if(trim($rs['ordShipName'])!='' || trim($rs['ordShipLastName'])!='' || $rs['ordShipAddress']!=''){
			if(@$usefirstlastname)
				$sXML.='&x_ship_to_first_name=' . urlencode(trim($rs['ordShipName'])) . '&x_ship_to_last_name=' . urlencode(trim($rs['ordShipLastName']));
			elseif(strpos(trim($rs['ordShipName']),' ')!==FALSE){
				$namearr=explode(' ',trim($rs['ordShipName']),2);
				$sXML.='&x_ship_to_first_name=' . urlencode($namearr[0]) . '&x_ship_to_last_name=' . urlencode($namearr[1]);
			}else
				$sXML.='&x_ship_to_last_name=' . urlencode(trim($rs['ordShipName']));
			$sXML.='&x_ship_to_address=' . urlencode($rs['ordShipAddress']);
			if($rs['ordShipAddress2']!='') $sXML.=urlencode(', ' . $rs['ordShipAddress2']);
			$sXML.='&x_ship_to_city=' . urlencode($rs['ordShipCity']) . '&x_ship_to_state=' . urlencode($rs['ordShipState']) . '&x_ship_to_zip=' . urlencode($rs['ordShipZip']) . '&x_ship_to_country=' . urlencode($rs['ordShipCountry']);
		}
		if(trim($rs['ordIP'])!='') $sXML.='&x_customer_ip=' . urlencode(trim($rs['ordIP']));
		if($demomode) $sXML.='&x_test_request=TRUE';
		if($vsAUTHCODE==''||$ordstatus<3){
			if(@$authnetemulateurl=='') $authnetemulateurl='https://secure.authorize.net/gateway/transact.dll';
			$success=TRUE;
			if($blockuser){
				$success=FALSE;
				$vsRESPMSG=$multipurchaseblockmessage;
			}else
				$success=callcurlfunction($authnetemulateurl, $sXML . $authorizeextraparams, $res, '', $vsRESPMSG, TRUE);
			if($success){
				$varString=explode('|', $res);
				if(count($varString)<20){
					$vsRESPMSG='Invalid response: ' . $res;
				}else{
					$vsRESULT=$varString[0];
					$vsERRCODE=(int)$varString[2];
					$vsRESPMSG=$varString[3];
					$vsAUTHCODE=$varString[4];
					$vsAVSADDR=$varString[5];
					$vsTRANSID=$varString[6];
					$vsCVV2=$varString[38];
					if((int)$vsRESULT==1||$vsERRCODE==253){
						if($vsERRCODE==253) $pendingreason='Pending: FDS'; else $pendingreason='';
						if(getpost('accountnum')!='') $vsAUTHCODE='eCheck';
						$vsRESULT='0'; // Keep in sync with Payflow Pro
						ect_query("UPDATE cart SET cartDateAdded='" . date('Y-m-d',time()+($dateadjust*60*60)) . "',cartCompleted=1 WHERE cartCompleted<>1 AND cartOrderID='" . $ordID . "'") or ect_error();
						ect_query("UPDATE orders SET ordStatus=3,ordAuthStatus='".$pendingreason."',ordAVS='".escape_string($vsAVSADDR)."',ordCVV='".escape_string($vsCVV2)."',ordAuthNumber='" . escape_string($vsAUTHCODE) . "',ordTransID='" . escape_string($vsTRANSID) . "',ordDate='" . date('Y-m-d H:i:s',time()+($dateadjust*60*60)) . "' WHERE ordAuthNumber='' AND ordID='" . $ordID . "'") or ect_error();
						do_order_success($ordID,$emailAddr,$sendEmail,FALSE,TRUE,TRUE,TRUE);
					}elseif($vsERRCODE==252){
						ect_query("UPDATE cart SET cartCompleted=2 WHERE cartCompleted=0 AND cartOrderID=".$ordID) or ect_error();
						ect_query("UPDATE orders SET ordAuthNumber='FDS Review' WHERE ordAuthNumber='' AND ordID=".$ordID) or ect_error();
						$vsRESPMSG=$xxAuNetR;
					}elseif($vsERRCODE==27||$vsERRCODE==127)
						$isavsmismatch=TRUE;
				}
			}
		}else{
			$vsRESULT='0';
			$vsRESPMSG=$GLOBALS['xxTranAp'];
			$pos=strpos($vsAUTHCODE, '-');
			if(! ($pos===FALSE))
				$vsAUTHCODE=substr($vsAUTHCODE, $pos + 1);
		}
	}elseif(getpost('method')=='14'){ // Custom Payment Processor
		retrieveorderdetails($ordID, $thesessionid);
		$sSQL="SELECT ordID,ordHandling,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordTotal,ordDiscount,ordIP,ordAuthNumber FROM orders WHERE ordID='" . $ordID . "'";
		$result=ect_query($sSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		$ordShipping=$rs['ordShipping'];
		$ordStateTax=$rs['ordStateTax'];
		$ordCountryTax=$rs['ordCountryTax'];
		$ordHSTTax=$rs['ordHSTTax'];
		$ordTotal=$rs['ordTotal'];
		$ordHandling=$rs['ordHandling'];
		$ordDiscount=$rs['ordDiscount'];
		$ordIP=$rs['ordIP'];
		$ordAuthNumber=trim($rs['ordAuthNumber']);
		$vsAUTHCODE=$ordAuthNumber;
		ect_free_result($result);
		$grandtotal=($ordShipping+$ordStateTax+$ordCountryTax+$ordHSTTax+$ordTotal+$ordHandling)-$ordDiscount;
		if($vsAUTHCODE==''){
			include './vsadmin/inc/customppreturn.php';
		}else{
			$vsRESULT='0';
			$vsRESPMSG=$GLOBALS['xxTranAp'];
		}
	}elseif(getpost('method')=='18'){ // PayPal Direct
		@set_time_limit(120);
		$sSQL="SELECT ordID,ordName,ordLastName,ordCity,ordState,ordCountry,ordPhone,ordHandling,ordZip,ordEmail,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordTotal,ordDiscount,ordAddress,ordAddress2,ordIP,ordAuthNumber,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipCountry,ordShipZip FROM orders WHERE ordID='" . $ordID . "'";
		$result=ect_query($sSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		ect_free_result($result);
		$grandtotal=($rs['ordShipping']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordHSTTax']+$rs['ordTotal']+$rs['ordHandling'])-$rs['ordDiscount'];
		$data2arr=explode('&',$data2);
		$password=urldecode(@$data2arr[0]);
		$isthreetoken=(trim(urldecode(@$data2arr[2]))=='1');
		$signature=''; $sslcertpath='';
		if($isthreetoken){
			$signature=urldecode(@$data2arr[1]);
			if(strpos($data1,'/')!==FALSE){
				$data1arr=explode('/',$data1);
				if($grandtotal<12) $data1=trim($data1arr[1]); else $data1=trim($data1arr[0]);
				$data1arr=explode('/',$password);
				if($grandtotal<12 && strpos($password,'/')!==FALSE) $password=trim($data1arr[1]); else $password=trim($data1arr[0]);
				$data1arr=explode('/',$signature);
				if($grandtotal<12 && strpos($signature,'/')!==FALSE) $signature=trim($data1arr[1]); else $signature=trim($data1arr[0]);
			}
		}else
			$sslcertpath=urldecode(@$data2arr[1]);
		$sSQL="SELECT countryCode FROM countries WHERE countryName='" . escape_string($rs['ordCountry']) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs2=ect_fetch_assoc($result))
			$countryCode=$rs2['countryCode'];
		ect_free_result($result);
		$sSQL="SELECT countryCode FROM countries WHERE countryName='" . escape_string($rs['ordShipCountry']) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs2=ect_fetch_assoc($result))
			$shipCountryCode=$rs2['countryCode'];
		else
			$shipCountryCode='';
		ect_free_result($result);
		if($countryCode=='US' || $countryCode=='CA'){
			$sSQL="SELECT stateAbbrev FROM states WHERE (stateCountryID=1 OR stateCountryID=2) AND stateName='" . escape_string($rs['ordState']) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs2=ect_fetch_assoc($result)) $rs['ordState']=$rs2['stateAbbrev'];
			ect_free_result($result);
		}
		if($shipCountryCode=='US' || $shipCountryCode=='CA'){
			$sSQL="SELECT stateAbbrev FROM states WHERE (stateCountryID=1 OR stateCountryID=2) AND stateName='" . escape_string($rs['ordShipState']) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs2=ect_fetch_assoc($result)) $rs['ordShipState']=$rs2['stateAbbrev'];
			ect_free_result($result);
		}
		$vsAUTHCODE=trim($rs['ordAuthNumber']);
		splitname($cardname, $firstname, $lastname);
		$cardtype=getcctypefromnum($cardnum);
		if(trim($rs['ordShipAddress'])!='') $doship='Ship'; else $doship='';
		$sXML=ppsoapheader($data1, $password, $signature) .
			'<soap:Body><DoDirectPaymentReq xmlns="urn:ebay:api:PayPalAPI">' .
			'<DoDirectPaymentRequest><Version xmlns="urn:ebay:apis:eBLBaseComponents">60.00</Version>' .
			'  <DoDirectPaymentRequestDetails xmlns="urn:ebay:apis:eBLBaseComponents">' .
			addtag('PaymentAction',$ppmethod==1?'Authorization':'Sale') .
			'    <PaymentDetails>' .
			'      <OrderTotal currencyID="' . $countryCurrency . '">' . number_format($grandtotal,2,'.','') . '</OrderTotal>' .
			addtag('ButtonSource','ecommercetemplates_Cart_DP_US') .
			addtag('NotifyURL',$storeurl . 'vsadmin/ppconfirm.php') .
			addtag('Custom',$ordID) .
			'      <ShipToAddress><Name>' . vrxmlencode(trim($rs['ord'.$doship.'Name'].' '.$rs['ord'.$doship.'LastName'])) . '</Name><Street1>' . vrxmlencode($rs['ord'.$doship.'Address']) . '</Street1><Street2>' . vrxmlencode($rs['ord'.$doship.'Address2']) . '</Street2><CityName>' . $rs['ord'.$doship.'City'] . '</CityName><StateOrProvince>' . $rs['ord'.$doship.'State'] . '</StateOrProvince><Country>' . ($doship!='' ? $shipCountryCode : $countryCode) . '</Country><PostalCode>' . $rs['ord'.$doship.'Zip'] . '</PostalCode></ShipToAddress>' .
			'    </PaymentDetails><CreditCard>' .
			addtag('CreditCardType',$cardtype) . addtag('CreditCardNumber',vrxmlencode($cardnum)) . addtag('ExpMonth',$exmon) . addtag('ExpYear',$exyear) .
			'      <CardOwner>' .
			addtag('Payer',vrxmlencode($rs['ordEmail'])) .
			'<PayerName>' . addtag('FirstName',vrxmlencode($firstname)) . addtag('LastName',vrxmlencode($lastname)) . '</PayerName>' . addtag('PayerCountry',$countryCode) .
			'        <Address>' . addtag('Street1',vrxmlencode($rs['ordAddress'])) . addtag('Street2',vrxmlencode($rs['ordAddress2'])) . addtag('CityName',$rs['ordCity']) . addtag('StateOrProvince',$rs['ordState']) . addtag('Country',$countryCode) . addtag('PostalCode',$rs['ordZip']) . '</Address>' .
			'      </CardOwner>' .
			addtag('CVV2',$cvv2);
		if($issuenum!=''){
			if(strlen($issuenum)==2) $sXML.=addtag('IssueNumber',$issuenum); else $sXML.=addtag('StartMonth',substr($issuenum,0,2)) . addtag('StartYear',substr($issuenum,2));
		}
		if(@$cardinalprocessor!='' && @$cardinalmerchant!='' && @$cardinalpwd!=''){
			$sXML.='<ThreeDSecureRequest>' . addtag('AuthStatus3ds',@$_SESSION['PAResStatus']) . addtag('MpiVendor3ds',@$_SESSION['centinel_enrolled']) . addtag('Cavv',@$_SESSION['Cavv']) . addtag('Eci3ds',@$_SESSION['EciFlag']) . addtag('Xid',@$_SESSION['Xid']) . '</ThreeDSecureRequest>';
		}
		$sXML.=' </CreditCard>' .
			addtag('IPAddress',trim($rs['ordIP'])) . addtag('MerchantSessionId',$rs['ordID']) . 
			'  </DoDirectPaymentRequestDetails>' .
			'</DoDirectPaymentRequest></DoDirectPaymentReq></soap:Body></soap:Envelope>';
		if($demomode) $sandbox='.sandbox'; else $sandbox='';
		$vsRESULT='-1';
		if($vsAUTHCODE==''){
			if($blockuser){
				$success=FALSE;
				$vsRESPMSG=$multipurchaseblockmessage;
			}else
				$success=callcurlfunction('https://api' . ($isthreetoken ? '-3t' : '') . $sandbox . '.paypal.com/2.0/', $sXML, $res, $sslcertpath, $vsRESPMSG, 25);
			if($success){
				$xmlDoc=new vrXMLDoc($res);
				$vsAUTHCODE='';$vsERRCODE='';$vsRESPMSG='';$vsAVSADDR='';$vsTRANSID='';$vsCVV2='';
				$nodeList=$xmlDoc->nodeList->childNodes[0];
				for($i=0; $i < $nodeList->length; $i++){
					if($nodeList->nodeName[$i]=='SOAP-ENV:Body'){
						$e=$nodeList->childNodes[$i];
						for($j=0; $j < $e->length; $j++){
							if($e->nodeName[$j]=='DoDirectPaymentResponse'){
								$ee=$e->childNodes[$j];
								for($jj=0; $jj < $ee->length; $jj++){
									if($ee->nodeName[$jj]=='Ack'){
										if($ee->nodeValue[$jj]=='Success' || $ee->nodeValue[$jj]=='SuccessWithWarning'){
											$vsRESULT=1;
											$vsRESPMSG=$GLOBALS['xxTranAp'];
										}
									}elseif($ee->nodeName[$jj]=='TransactionID'){
										$vsAUTHCODE=$ee->nodeValue[$jj];
									}elseif($ee->nodeName[$jj]=='AVSCode'){
										$vsAVSADDR=$ee->nodeValue[$jj];
									}elseif($ee->nodeName[$jj]=='CVV2Code'){
										$vsCVV2=$ee->nodeValue[$jj];
									}elseif($ee->nodeName[$jj]=='Errors'){
										$shortmsg=$themsg=$thecode='';
										$iswarning=FALSE;
										$ff=$ee->childNodes[$jj];
										for($kk=0; $kk < $ff->length; $kk++){
											if($ff->nodeName[$kk]=='ShortMessage'){
												$shortmsg=$ff->nodeValue[$kk];
											}elseif($ff->nodeName[$kk]=='LongMessage'){
												$themsg=$ff->nodeValue[$kk];
											}elseif($ff->nodeName[$kk]=='ErrorCode'){
												$thecode=$ff->nodeValue[$kk];
											}elseif($ff->nodeName[$kk]=='SeverityCode'){
												$iswarning=($ff->nodeValue[$kk]=='Warning');
											}
										}
										if(! $iswarning){
											$vsRESPMSG=($themsg!=''?$themsg:$shortmsg) . ($vsRESPMSG!=''?'<br />'.$vsRESPMSG:'');
											$vsERRCODE=$thecode;
										}
									}
								}
							}
						}
					}
				}
				if((int)$vsRESULT==1){
					$vsRESULT='0'; // Keep in sync with Payflow Pro
					ect_query("UPDATE cart SET cartDateAdded='" . date('Y-m-d',time()+($dateadjust*60*60)) . "',cartCompleted=1 WHERE cartCompleted<>1 AND cartOrderID='" . $ordID . "'") or ect_error();
					ect_query("UPDATE orders SET ordStatus=3,ordAuthStatus='',ordAVS='".escape_string($vsAVSADDR)."',ordCVV='".escape_string($vsCVV2)."',ordAuthNumber='" . escape_string($vsAUTHCODE) . "',ordDate='" . date('Y-m-d H:i:s',time()+($dateadjust*60*60)) . "' WHERE ordAuthNumber='' AND ordID='" . $ordID . "'") or ect_error();
					do_order_success($ordID,$emailAddr,$sendEmail,FALSE,TRUE,TRUE,TRUE);
				}elseif($vsERRCODE!='')
					$vsERRCODE=(int)$vsERRCODE;
			}
		}else{
			$vsRESULT='0';
			$vsRESPMSG=$GLOBALS['xxTranAp'];
			$pos=strpos($vsAUTHCODE, "-");
			if(! ($pos===FALSE))
				$vsAUTHCODE=substr($vsAUTHCODE, $pos + 1);
		}
	}elseif(getpost('method')=='10'){ // Capture Card
		print 'DISABLED!!<br />';
	}else{
		print 'Error';
		exit;
	}
	$_SESSION['centinelok']='';
	if($centinelenrolled!='Y'&&((getpost('method')!='8'&&getpost('method')!='22')||$iframe=='')){
		logevent(substr(getipaddress(), 0, 24),'TRANSACTION',$vsRESULT=='0','cart.php','ORDERS');
		if($vsRESPMSG==$xxAuNetR){ ?>
		<div class="cart4details">
			<div class="cart4header cartheader"><?php print $xxTnxOrd?></div>
			<div>
				<div class="cobhl cobhl4"><?php print $xxTrnRes?></div><div class="cobll" align="left"><?php print $vsRESPMSG?></div>
			</div>
			<div>
				<div class="cobhl cobhl4"><?php print $xxOrdNum?></div><div class="cobll cobll4"><?php print $ordID?></div>
			</div>
			<div>
				<div class="cobhl cobhl4"><?php print $xxAutCod?></div><div class="cobll cobll4">FDS Review</div>
			</div>
		</div>
<?php	}elseif($vsRESULT=='0'){ ?>
	  <form method="post" action="thanks.php" name="checkoutform">
		<input type="hidden" name="xxpreauth" value="<?php print $ordID?>" />
		<input type="hidden" name="xxpreauthmethod" value="<?php print (int)getpost('method')?>" />
		<input type="hidden" name="thesessionid" value="<?php print $thesessionid?>" />
		<div class="cart4details">
			<div class="cart4header cartheader"><?php print $GLOBALS['xxTnxOrd']?></div>
			<div>
				<div class="cobhl cobhl4"><?php print $GLOBALS['xxTrnRes']?></div><div class="cobll cobll4"><?php print $vsRESPMSG?></div>
			</div>
			<div>
				<div class="cobhl cobhl4"><?php print $GLOBALS['xxOrdNum']?></div><div class="cobll cobll4"><?php print $ordID?></div>
			</div>
			<div>
				<div class="cobhl cobhl4"><?php print $GLOBALS['xxAutCod']?></div><div class="cobll cobll4"><?php print $vsAUTHCODE?></div>
			</div>
			<div class="cobll cart2column cart4buttons"><?php print imageorsubmit(@$imgclickforreceipt,$GLOBALS['xxCliCon'],'clickforreceipt')?></div>
		</div>
	  </form>
	  <script type="text/javascript">setTimeout("document.checkoutform.submit()",5000);</script>
<?php	}else{ ?>
	  <form method="post" action="cart.php" name="checkoutform">
		<input type="hidden" name="mode" value="<?php print $isavsmismatch?'checkout':'go'?>" />
		<input type="hidden" name="orderid" value="<?php print $ordID?>" />
		<input type="hidden" name="sessionid" value="<?php print $thesessionid?>" />
		<input type="hidden" name="shipselectoridx" value="<?php print @$_SESSION['shipselectoridx']?>" />
		<input type="hidden" name="shipselectoraction" value="<?php print @$_SESSION['shipselectoraction']?>" />
		<input type="hidden" name="altrates" value="<?php print @$_SESSION['altrates']?>" />
		<input type="hidden" name="product_location" value="7117" />
	
	
		<div class="cart4details">
			<div class="cart4header cartheader"><?php print $GLOBALS['xxSorTrn']?></div>
			<div>
				<div class="cobhl cobhl4"><?php print $GLOBALS['xxTrnRes']?></div><div class="cobll cobll4 ectwarning"><?php print (@$vsERRCODE!='' ? '(' . $vsERRCODE . ') ' : '') . $vsRESPMSG?></div>
			</div>
			<div class="cobll cart2column cart4buttons"><?php print imageorsubmit(@$imggoback,$GLOBALS['xxGoBack'],'gobackbutton')?></div>
		</div>
	  </form>
<?php	}
	}
}elseif($checkoutmode=='mailinglistsignup'){
	$validsignup=TRUE;
	if(strpos(getpost('mlsuemail'),'@')===FALSE || ! (is_numeric(getpost('mlsectgrp1')) && is_numeric(getpost('mlsectgrp2'))))
		$validsignup=FALSE;
	else{
		$suarr=explode('@',getpost('mlsuemail'));
		if(strlen($suarr[0])!=(int)getpost('mlsectgrp1') || strlen($suarr[1])!=(int)getpost('mlsectgrp2')) $validsignup=FALSE;
	}
	if($validsignup) addtomailinglist(getpost('mlsuemail'),getpost('mlsuname'));
	print '<div style="padding:24px;text-align:center;font-weight:bold">&nbsp;<br />&nbsp;<br />' . ($validsignup?$GLOBALS['xxThkSub']:'Sorry, there was a checksum error signing you up to the mailing list') . '</div>';
	if($warncheckspamfolder==TRUE) print '<div class="chkspamfolder ectwarning">' . $GLOBALS['xxSpmWrn'] . '</div>';
	if(getpost('rp')!='') $thehref=htmlspecials(str_replace(array('"','<'),'',getpost('rp'))); else $thehref=$GLOBALS['xxHomeURL'];
	print '<div style="padding:24px;text-align:center;font-weight:bold">' . imageorbutton(@$imgcontinueshopping, $GLOBALS['xxCntShp'], 'continueshopping', $thehref, FALSE) . '<br />&nbsp;</div>';
	$_SESSION['MLSIGNEDUP']=TRUE;
}
if(getget('emailconf')!='' || getget('unsubscribe')!=''){
	if(getget('emailconf')!='') $theemail=getget('emailconf'); else $theemail=getget('unsubscribe');
	$sSQL="SELECT email,isconfirmed FROM mailinglist WHERE email='" . escape_string($theemail) . "'";
	$result=ect_query($sSQL) or ect_error();
	$foundemail=FALSE;
	if($rs=ect_fetch_assoc($result)){
		$foundemail=TRUE;
		$isconfirmed=($rs['isconfirmed']!=0);
	}
	ect_free_result($result);
	print '<div class="cartemailconf"><div class="cartemailconftitle">' . $GLOBALS['xxMLConf'] . '</div><div class="cartemailconfaction">';
	if(! $foundemail)
		print $GLOBALS['xxEmNtFn'];
	elseif(getget('unsubscribe')!=''){
		ect_query("DELETE FROM mailinglist WHERE email='" . escape_string($theemail) . "'") or ect_error();
		print $GLOBALS['xxSucUns'];
	}elseif($isconfirmed)
		print $GLOBALS['xxAllSub'];
	else{
		$thecheck=substr(md5($uspsUser.$upsUser.$origZip.@$checksumtext.':'.$theemail), 0, 10);
		if($thecheck==getget('check')){
			ect_query("UPDATE mailinglist SET isconfirmed=1 WHERE email='" . escape_string($theemail) . "'") or ect_error();
			print $GLOBALS['xxSubAct'];
		}else
			print $GLOBALS['xxSubNAc'];
	}
	print '</div><div class="cartemailconfcontinue"><a class="ectlink" href="' . $GLOBALS['xxHomeURL'] . '" onmouseover="window.status=\'' . jsescape($GLOBALS['xxCntShp']) . '\';return true" onmouseout="window.status=\'\';return true">' . jsescape($GLOBALS['xxCntShp']) . '</a></div>';
	print '</div>';
}elseif(getget('mode')=='gw'){
	print '<form method="post" action="cart.php?mode=gw">' . whv("doupdate","1") . '<div class="cartgiftwrapdiv">';
	print '<div class="giftwrapdetails_cntnr">';
		print '<div class="giftwrapdetails giftwrapid">' . $GLOBALS['xxCODets'] . '</div>';
		print '<div class="giftwrapdetails giftwrapname">' . $GLOBALS['xxCOName'] . '</div>';
		print '<div class="giftwrapdetails giftwrapquant">' . $GLOBALS['xxQuant'] . '</div>';
		print '<div class="giftwrapdetails giftwrapyes">' . $GLOBALS['xxGifWra'] . '</div>';
	print '</div>';
	if(getpost('doupdate')=='1'){
		foreach(@$_POST as $objItem => $objValue){
			if(substr($objItem,0,5)=='gwset' && is_numeric($objValue)){
				$thecartid=substr($objItem,5);
				if(is_numeric($thecartid) && is_numeric($objValue)){
					$sSQL="UPDATE cart SET cartGiftWrap=" . $objValue . ",cartGiftMessage='" . escape_string(strip_tags(@$_POST['gwmessage' . $thecartid])) . "' WHERE cartID=" . $thecartid . " AND " . getsessionsql();
					ect_query($sSQL) or ect_error();
				}
			}
		}
		updategiftwrap();
		print '<div class="giftwrapupdate">';
		print '<meta http-equiv="Refresh" content="2; URL=cart.php">';
		print '<div class="giftwrapupdating">' . $GLOBALS['xxGifUpd'] . '</div>';
		print '<div class="giftwrappleasewait">' . $GLOBALS['xxPlsWait'] . ' <a class="ectlink" href="cart.php">' . $GLOBALS['xxClkHere'] . '</a>.</div></div>';
	}else{
		$sSQL='SELECT cartID,cartProdID,cartProdName,cartProdPrice,cartQuantity,pStaticPage,pDisplay,pGiftWrap,cartGiftWrap,cartGiftMessage FROM cart LEFT JOIN products ON cart.cartProdID=products.pID WHERE pGiftWrap<>0 AND cartCompleted=0 AND ' . getsessionsql() . ' ORDER BY cartID';
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result)==0){
			print '<class="giftwrapupdate">' . $GLOBALS['xxGifNop'] . '</div>';
		}else{
			while($rs=ect_fetch_assoc($result)){
				print '<div class="giftwrapline">';
					print '<div class="giftwraplineid">' . $rs['cartProdID'] . '</div>';
					print '<div class="giftwraplinename">' . $rs['cartProdName'] . '</div>';
					print '<div class="giftwraplinequant">' . $rs['cartQuantity'] . '</div>';
					print '<div class="giftwraplineyes"><select size="1" name="gwset' . $rs['cartID'] . '" onchange="document.getElementById(\'gwmessage' . $rs['cartID'] . '\').disabled=this[this.selectedIndex].value!=\'1\';"><option value="0">' . $GLOBALS['xxNo'] . '</option><option value="1"' . ($rs['cartGiftWrap']!=0 ? ' selected="selected"' : '') . '>' . $GLOBALS['xxYes'] . '</option></select></div>';
					print '<div class="giftwrapmessage"><div class="giftwraptmessage">' . $GLOBALS['xxGifMes'] . '</div><div class="giftwraplinemessage"><textarea class="gwmessage' . $rs['cartID'] . '" name="gwmessage' . $rs['cartID'] . '" id="gwmessage' . $rs['cartID'] . '" rows="3" cols="34"' . ($rs['cartGiftWrap']==0 ? ' disabled="disabled"' : '') . '>' . htmlspecials($rs['cartGiftMessage']) . '</textarea></div></div>';
				print '</div>';
			}
			print '<div class="giftwrapbuttons"><input type="submit" value="Update Selections" class="giftwrapsubmit" /> <input type="button" value="' . $GLOBALS['xxCancel'] . '" onclick="document.location=\'cart.php\'" class="giftwrapcancel" /></div>';
		}
		ect_free_result($result);
	}
	print '</div></form>';
}elseif((getget('token')=='' || $checkoutmode=='paypalcancel') && ($checkoutmode=='dologin' || $checkoutmode=='donewaccount' || $checkoutmode=='update' || $checkoutmode=='paypalcancel' || $checkoutmode=='savecart' || $checkoutmode=='movetocart' || $checkoutmode=='') && $cartisincluded!=TRUE){
	if(getsessionid()=='') print 'The PHP session has not been started. This can cause problems with the shopping cart function. For help please go to <a class="ectlink" href="http://www.ecommercetemplates.com/support/">http://www.ecommercetemplates.com/support/</a>';
	if(@$_SESSION['AmazonLoginTimeout']!=''&&time()>=$_SESSION['AmazonLoginTimeout']){
		$_SESSION['AmazonLogin']='';
		$_SESSION['AmazonLoginTimeout']='';
	}
	$amazonpaycheckout=@$_SESSION['AmazonLogin']!=''&&@$_SESSION['AmazonLoginTimeout']!=''&&getget('amazonpay')=='go';
	$requiressl=FALSE;
	if(@$pathtossl==''){
		$sSQL='SELECT payProvID FROM payprovider WHERE payProvEnabled=1 AND (payProvID IN (7,10,12,13' . (@$paypalhostedsolution?'':',18') . ") OR (payProvID=16 AND payProvData2='1'))"; // All the ones that require SSL
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result)>0) $requiressl=TRUE;
		ect_free_result($result);
	}
	if($requiressl || @$pathtossl!=''){
		if(@$pathtossl!=''){
			if(substr($pathtossl,-1)!='/') $pathtossl.='/';
			$cartpath=$pathtossl . 'cart.php';
		}else
			$cartpath=str_replace('http:','https:',$storeurl) . 'cart.php';
	}else
		$cartpath='cart.php';
	$loginerror='';
	if(getget('mode')=='logout'){
		$_SESSION['clientID']=NULL; unset($_SESSION['clientID']);
		$_SESSION['clientUser']=NULL; unset($_SESSION['clientUser']);
		$_SESSION['clientActions']=NULL; unset($_SESSION['clientActions']);
		$_SESSION['clientLoginLevel']=NULL; unset($_SESSION['clientLoginLevel']);
		$_SESSION['clientPercentDiscount']=NULL; unset($_SESSION['clientPercentDiscount']);
		$GLOBALS['xxSryEmp']=$GLOBALS['xxLOSuc'];
		ectsetcookie('WRITECLL', 'x', 365, '/', '');
		ectsetcookie('WRITECLP', '', 365, '/', '');
		if(@$pathtossl!='') print '<script src="'.$pathtossl.'vsadmin/savecookie.php?WRITECLL=x&WRITECLP=&permanent=Y"></script>';
	}
	$loginsuccess=FALSE;
	if($checkoutmode=='dologin' || ($checkoutmode=='donewaccount' && @$allowclientregistration==TRUE)){
		$loginsuccess=TRUE;
		$clientEmail=cleanupemail(getpost('email'));
		$clientPW=trim(str_replace("'",'',dohashpw(getpost('pass'))));
		if($checkoutmode=='donewaccount'){
			if(getpost('name')!='' && $clientPW!='' && strpos($clientEmail,'@')!==FALSE && strpos($clientEmail,'.')!==FALSE){
				$sSQL="SELECT clID FROM customerlogin WHERE clEmail='" . escape_string($clientEmail) . "'";
				$result=ect_query($sSQL) or ect_error();
				if(ect_num_rows($result)>0){
					$loginsuccess=FALSE;
					$loginerror=$GLOBALS['xxEmExi'];
				}
			}else{
				$loginsuccess=FALSE;
				$loginerror='Invalid login details';
			}
			if($loginsuccess && (strpos(getpost('name'),'<')!==FALSE || strpos(getpost('name'),'>')!==FALSE)){
				$loginsuccess=FALSE;
				$loginerror='Invalid Characters in Login Name';
			}
			if($loginsuccess){
				if(@$defaultcustomerloginlevel=='') $defaultcustomerloginlevel=0;
				if(@$defaultcustomerloginactions=='') $defaultcustomerloginactions=0;
				if(@$defaultcustomerlogindiscount=='') $defaultcustomerlogindiscount=0; else $defaultcustomerloginactions=(((int)$defaultcustomerloginactions)|16);
				$sSQL="INSERT INTO customerlogin (clUserName,clEmail,clPw,clDateCreated,clLoginLevel,clActions,clPercentDiscount,clientCustom1,clientCustom2) VALUES ('" . escape_string(getpost('name')) . "','" . escape_string($clientEmail) . "','" . escape_string($clientPW) . "','" . date('Y-m-d', time()+($dateadjust*60*60)) . "','".$defaultcustomerloginlevel."','".$defaultcustomerloginactions."','".$defaultcustomerlogindiscount."','" . escape_string(strip_tags(getpost('extraclientfield1'))) . "','" . escape_string(strip_tags(getpost('extraclientfield2'))) . "')";
				ect_query($sSQL) or ect_error();
				if(getpost('allowemail')=='ON') addtomailinglist($clientEmail,getpost('name'));
				if(($GLOBALS['adminEmailConfirm'] & 4)==4){
					$emailmessage='There has been a new customer signup at your store: ' . $emlNl .
						'Email: ' . $clientEmail . $emlNl .
						'Name: ' . getpost('name') . $emlNl;
					dosendemail($emailAddr,$emailAddr,$clientEmail,'New Customer Signup',$emailmessage);
				}
			}
		}
		if($loginsuccess){
			$sSQL="SELECT clID,clUserName,clActions,clLoginLevel,clPercentDiscount FROM customerlogin WHERE (clEmail<>'' AND clEmail='" . escape_string($clientEmail) . "' AND clPW='" . escape_string($clientPW) . "') OR (clEmail='' AND clUserName='" . escape_string($clientEmail) . "' AND clPW='" . escape_string($clientPW) . "')";
			$result=ect_query($sSQL) or ect_error();
			$loginsuccess=FALSE;
			if($rs=ect_fetch_assoc($result)){
				$_SESSION['clientID']=$rs['clID'];
				$_SESSION['clientUser']=$rs['clUserName'];
				$_SESSION['clientActions']=$rs['clActions'];
				$_SESSION['clientLoginLevel']=$rs['clLoginLevel'];
				$_SESSION['clientPercentDiscount']=(100.0-(double)$rs['clPercentDiscount'])/100.0;
				get_wholesaleprice_sql();
				ectsetcookie('WRITECLL', $clientEmail, getpost('cook')=='ON'?365:0, '/', '');
				ectsetcookie('WRITECLP', $clientPW, getpost('cook')=='ON'?365:0, '/', '');
				$loginsuccess=TRUE;
			}else
				$loginerror=$GLOBALS['xxNoLogD'];
		}
		if($loginsuccess){
			$cartchanged=FALSE;
			$sSQL="SELECT ordID FROM orders WHERE ordStatus>1 AND ordAuthNumber='' AND (ordSessionID='" . escape_string($thesessionid) . "' OR ordClientID='" . escape_string($_SESSION['clientID']) . "')";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				release_stock($rs['ordID']);
				ect_query("UPDATE cart SET cartSessionID='".escape_string(getsessionid())."',cartClientID='" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "' WHERE cartCompleted=0 AND cartOrderID=" . $rs['ordID']) or ect_error();
				ect_query("UPDATE orders SET ordAuthStatus='MODWARNOPEN',ordShipType='MODWARNOPEN' WHERE ordID=" . $rs['ordID']) or ect_error();
			}
			ect_free_result($result);
			$sSQL="SELECT cartID,cartProdID FROM cart WHERE cartCompleted=0 AND cartClientID='" . escape_string($_SESSION['clientID']) . "'";
			$result=ect_query($sSQL) or ect_error();
			while($cartarr=ect_fetch_assoc($result)){
				$hasoptions=TRUE;
				$sSQL="SELECT cartID,cartQuantity FROM cart WHERE cartClientID=0 AND cartCompleted=0 AND cartSessionID='" . escape_string($thesessionid) . "' AND cartProdID='" . escape_string($cartarr['cartProdID']) . "'";
				$result2=ect_query($sSQL) or ect_error();
				if($rs=ect_fetch_assoc($result2)){ $thecartid=$rs['cartID']; $thequant=$rs['cartQuantity']; } else $thecartid='';
				if($thecartid!=''){ // check options
					$optarr1cnt=0; $optarr2cnt=0;
					$sSQL="SELECT coOptID,coCartOption FROM cartoptions WHERE coCartID=" . $cartarr['cartID'];
					$result3=ect_query($sSQL) or ect_error();
					while($rs2=ect_fetch_assoc($result3))
						$optarr1[$optarr1cnt++]=$rs2;
					$sSQL="SELECT coOptID,coCartOption FROM cartoptions WHERE coCartID=" . $thecartid;
					$result3=ect_query($sSQL) or ect_error();
					while($rs2=ect_fetch_assoc($result3))
						$optarr2[$optarr2cnt++]=$rs2;
					if($optarr1cnt!=$optarr2cnt) $hasoptions=FALSE;
					if($optarr1cnt>0 && $optarr2cnt>0){
						if($hasoptions){
							for($index2=0; $index2 < $optarr1cnt; $index2++){
								$hasthisoption=FALSE;
								for($index3=0; $index3 < $optarr2cnt; $index3++){
									if($optarr1[$index2]['coOptID']==$optarr2[$index3]['coOptID'] && $optarr1[$index2]['coCartOption']==$optarr2[$index3]['coCartOption']) $hasthisoption=TRUE;
								}
								if(! $hasthisoption) $hasoptions=FALSE;
							}
						}
					}
				}
				if($thecartid!='' && $hasoptions){
					ect_query("DELETE FROM cart WHERE cartID='".escape_string($cartarr['cartID'])."'") or ect_error();
					ect_query("DELETE FROM cartoptions WHERE coCartID='".escape_string($cartarr['cartID'])."'") or ect_error();
				}
			}
			ect_free_result($result);
			$sSQL="UPDATE cart SET cartClientID='" . escape_string($_SESSION['clientID']) . "' WHERE cartClientID=0 AND cartCompleted=0 AND cartSessionID='" . escape_string($thesessionid) . "'";
			ect_query($sSQL) or ect_error();
			$sSQL="SELECT cartID,cartProdID,cartProdPrice,pID,".$WSP."pPrice FROM cart LEFT JOIN products ON cart.cartProdId=products.pID WHERE cartClientID='" . escape_string($_SESSION['clientID']) . "' AND cartCompleted=0 AND cartProdID<>'".$giftcertificateid."' AND cartProdID<>'".$donationid."' AND cartProdID<>'".$giftwrappingid."'";
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){
				if(is_null($rs['pID'])){
					$cartchanged=TRUE;
					ect_query("DELETE FROM cart WHERE cartID='".escape_string($rs['cartID'])."'") or ect_error();
					ect_query("DELETE FROM cartoptions WHERE coCartID='".escape_string($rs['cartID'])."'") or ect_error();
				}else{
					$newprice=checkpricebreaks($rs['cartProdID'],$rs['pPrice']);
					if($rs['cartProdPrice']!=$newprice) $cartchanged=TRUE; // recalculate wholesale price plus quant discounts
					$sSQL='SELECT coID,coPriceDiff,'.$OWSP."optPriceDiff,optFlags FROM cart INNER JOIN cartoptions ON cart.cartID=cartoptions.coCartID INNER JOIN options ON cartoptions.coOptID=options.optID INNER JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optType IN (-4,-2,-1,1,2,4) AND cartID='".$rs['cartID']."'";
					$result2=ect_query($sSQL) or ect_error();
					while($rs2=ect_fetch_assoc($result2)){
						$sSQL='UPDATE cartoptions SET coPriceDiff='.(($rs2['optFlags']&1)==0 ? $rs2['optPriceDiff'] : round(($rs2['optPriceDiff'] * $newprice)/100.0, 2))." WHERE coID='".$rs2['coID']."'";
						ect_query($sSQL) or ect_error();
					}
					ect_free_result($result2);
				}
			}
			ect_free_result($result);
			eval('$theref=@$clientloginref' . @$_SESSION['clientLoginLevel'] . ';');
			if($theref!='') $clientloginref=$theref;
			if($cartchanged)
				$refURL='cart.php?cartchanged=true';
			elseif($checkoutmode=='donewaccount' && $warncheckspamfolder)
				$refURL='cart.php?warncheckspamfolder=true';
			elseif(@$clientloginref=='referer' || @$clientloginref=='')
				if(getpost('refurl')!='') $refURL=getpost('refurl'); else $refURL='cart.php';
			else
				$refURL=$clientloginref;
			$_SESSION['xsshipping']=NULL; unset($_SESSION['xsshipping']);
			$_SESSION['discounts']=NULL; unset($_SESSION['discounts']);
			$_SESSION['xscountrytax']=NULL; unset($_SESSION['xscountrytax']);
			unset($_SESSION['tofreeshipquant']);
			unset($_SESSION['tofreeshipamount']);
			print '<script type="text/javascript">setTimeout(function(){document.location=\'' . jsescape($refURL) . "'},1000)</script>";
		}
	}
	$addextrarows=0;
	if($shipType==0) $estimateshipping=FALSE;
	$wantstateselector=(FALSE||@$forcestateselector||@$defaultshipstate!='')&&@$estimateshipping;
	$wantcountryselector=FALSE;
	$wantzipselector=FALSE;
	if(@$estimateshipping==TRUE){
		if(@$GLOBALS['commercialloc']===2) $commercialloc_=TRUE;
		if($cartisincluded!=TRUE){
			if(@$_SESSION['clientID']!='' && getpost('country')=='' && @$_SESSION['country']=='' && $shipType>=1){
				$sSQL="SELECT addState,addCountry,addZip FROM address INNER JOIN countries ON address.addCountry=countries.countryName WHERE addCustID='".escape_string($_SESSION['clientID'])."' ORDER BY addIsDefault DESC";
				$result=ect_query($sSQL) or ect_error();
				if($rs=ect_fetch_assoc($result)){
					$_SESSION['country']=$rs['addCountry']; $_SESSION['state']=$rs['addState']; $_SESSION['zip']=$rs['addZip'];
				}
				ect_free_result($result);
			}
			if(getpost('state')!=''){
				$shipstate=getpost('state');
				$_SESSION['state']=getpost('state');
			}elseif(@$_SESSION['state']!='')
				$shipstate=$_SESSION['state'];
			else
				$shipstate=@$defaultshipstate;
			if(getpost('country')!=''){
				$shipcountry=getcountryfromid(getpost('country'));
				$_SESSION['country']=$shipcountry;
			}elseif(@$_SESSION['country']!='')
				$shipcountry=$_SESSION['country'];
			else{
				$shipCountryCode=$origCountryCode;
				$shipcountry=$origCountry;
			}
		}
		$sSQL="SELECT countryID,countryTax,countryCode,countryFreeShip FROM countries WHERE countryName='" . escape_string($shipcountry) . "'";
		for($index=1;$index<=2;$index++){
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				if(trim(@$_SESSION['clientID'])!='' && ((int)$_SESSION['clientActions'] & 2)==2) $countryTaxRate=0; else $countryTaxRate=$rs['countryTax'];
				$shipCountryID=$rs['countryID'];
				$shipCountryCode=$rs['countryCode'];
				$freeshipavailtodestination=($rs['countryFreeShip']==1);
				$shiphomecountry=($rs['countryID']==$origCountryID) || (($rs['countryID']==1 || $rs['countryID']==2) && @$usandcasplitzones);
				break;
			}
			$sSQL='SELECT countryID,countryTax,countryCode,countryFreeShip FROM admin INNER JOIN countries ON admin.adminCountry=countries.countryID WHERE adminID=1';
		}
		ect_free_result($result);
		if($cartisincluded!=TRUE){
			if(getpost('zip')!=''){
				$destZip=getpost('zip');
				$_SESSION['zip']=getpost('zip');
			}elseif(@$_SESSION['zip']!='')
				$destZip=$_SESSION['zip'];
			elseif(@$nodefaultzip!=TRUE && $origCountryCode==$shipCountryCode)
				$destZip=$origZip;
			else
				$destZip='';
		}
		if($shipCountryID==1 || $shipCountryID==2) $shipStateAbbrev=getstateabbrev($shipstate);
		if($shiphomecountry){
			$sSQL='SELECT stateTax,stateAbbrev,stateFreeShip FROM states WHERE stateCountryID=' . $shipCountryID . " AND (stateAbbrev='" . escape_string($shipstate) . "' OR stateName='" . escape_string($shipstate) . "')";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				if($shipCountryID==$origCountryID) $stateTaxRate=$rs['stateTax']; else $stateTaxRate=0;
				$freeshipavailtodestination=($freeshipavailtodestination && ($rs['stateFreeShip']==1));
			}
			ect_free_result($result);
		}else
			$shipstate='';
		$ordState=$shipstate;
		$shipType=getshiptype();
		$addextrarows=1;
		if($shipType==2 || $shipType==5){ // weight / price based
			$wantcountryselector=TRUE;
			if($splitUSZones)$wantstateselector=TRUE;
		}elseif($shipType==3 || $shipType==4 || $shipType>=6){
			$wantzipselector=TRUE;
			$wantcountryselector=TRUE;
		}
		if($shipType==4 && @$upsnegdrates==TRUE) $wantstateselector=TRUE;
		if(! @$nodiscounts && ! $wantstateselector){
			$sSQL="SELECT cpnID FROM coupons WHERE cpnCntry=1 AND cpnType=0 AND cpnNumAvail>0 AND cpnEndDate>='" . date('Y-m-d', time()+($dateadjust*60*60)) ."' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<=".$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))';
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)) $statelimiteddiscount=TRUE; else $statelimiteddiscount=FALSE;
			ect_free_result($result);
			if($statelimiteddiscount){
				$sSQL='SELECT stateID FROM states WHERE stateFreeShip=0 AND stateEnabled<>0 AND stateCountryID=' . $origCountryID;
				$result=ect_query($sSQL) or ect_error();
				if(ect_num_rows($result)>0) $wantstateselector=TRUE;
				ect_free_result($result);
			}
		}
		if(($adminAltRates==1 || $adminAltRates==2) && (! $wantzipselector || ! $wantcountryselector)){
			$sSQL='SELECT altrateid FROM alternaterates WHERE (usealtmethod<>0 OR usealtmethodintl<>0) AND altrateid IN (3,4,6,7,8,9)';
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result)>0){ $wantzipselector=TRUE; $wantcountryselector=TRUE; }
			ect_free_result($result);
		}
		if(($adminAltRates==1 || $adminAltRates==2) && ! $wantstateselector && $splitUSZones){
			$sSQL='SELECT altrateid FROM alternaterates WHERE (usealtmethod<>0 OR usealtmethodintl<>0) AND altrateid IN (2,5)';
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result)>0) $wantstateselector=TRUE;
			ect_free_result($result);
		}
		if(($adminAltRates==1 || $adminAltRates==2) && ! $wantcountryselector){
			$sSQL='SELECT altrateid FROM alternaterates WHERE (usealtmethod<>0 OR usealtmethodintl<>0) AND altrateid>=2';
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result)>0) $wantcountryselector=TRUE;
			ect_free_result($result);
		}
		if($wantstateselector){
			$stateSQL='SELECT stateAbbrev,stateName,stateName2,stateName3,stateCountryID,countryName FROM states INNER JOIN countries ON states.stateCountryID=countries.countryID WHERE countryEnabled<>0 AND stateEnabled<>0 AND (stateCountryID=' . $origCountryID . (($shipType==4 && $upsnegdrates==TRUE) || $origCountryID==1 || $origCountryID==2 ? ' OR stateCountryID=1 OR stateCountryID=2' : '') . ') ORDER BY stateCountryID,' . getlangid('stateName',1048576);
 			$result=ect_query($stateSQL) or ect_error();
			if(ect_num_rows($result)==0){ $wantstateselector=FALSE; $splitUSZones=FALSE; }
			ect_free_result($result);
		}
		if($wantstateselector){$wantcountryselector=TRUE;$addextrarows++;}else{$shipstate='';$shipStateAbbrev='';}
		if($wantcountryselector)$addextrarows++;
		if($wantzipselector)$addextrarows++;
		if(@$_SESSION['xsshipping']=='') initshippingmethods();
	}else{
		$_SESSION['xsshipping']=NULL; unset($_SESSION['xsshipping']);
	}
	$loyaltypointsavailable=0;
	$redeempoints=TRUE;
	$_SESSION['noredeempoints']='';
	if(@$loyaltypoints!='' && @$_SESSION['clientID']!=''){
		if(getget('redeempoints')=='no'){
			$_SESSION['noredeempoints']=TRUE;
			$redeempoints=FALSE;
		}
		$sSQL='SELECT loyaltyPoints FROM customerlogin WHERE clID=' . $_SESSION['clientID'];
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$loyaltypointsavailable=$rs['loyaltyPoints']; $addextrarows++;
		}
		ect_free_result($result);
	}
	$stockalreadysubtracted=FALSE;
	$sSQL="SELECT ordID FROM orders WHERE ordStatus>1 AND ordAuthNumber='' AND ordAuthStatus<>'MODWARNOPEN' AND " . getordersessionsql();
	$result=ect_query($sSQL) or ect_error();
	if(ect_num_rows($result)>0) $stockalreadysubtracted=TRUE;
	ect_free_result($result);
	if(getget('mode')=='sc') $checkoutmode='savedcart';
	if(@$showtaxinclusive!=0) $addextrarows++;
	if($stockalreadysubtracted) $stockwarning=FALSE; else do_stock_check(TRUE,$backorder,$stockwarning);
	$alldata='';
	if(getget('pla')!='') $GLOBALS['hideoptpricediffs']=TRUE;
	if(@$_SESSION['clientID']!='' && @$enablewishlists==TRUE){ // Wish List Popup
		print '<div id="savecartlist" style="position:absolute;visibility:hidden;top:0px;left:0px;z-index:10000;;display:table" onmouseover="cartoversldiv=true;" onmouseout="cartoversldiv=false;setTimeout(\'cartchecksldiv()\',1000)">';
		print '<div style="display:table-row"><div class="cobll" onmouseover="this.className=\'cobhl\'" onmouseout="this.className=\'cobll\'" onclick="dosaveitem(\'\')" style="display:table-cell"><a class="ectlink wishlistmenu" href="#" onclick="dosaveitem(\'\')">' . $GLOBALS['xxMyWisL'] . "</a></div></div>\r\n";
		$sSQL="SELECT listID,listName FROM customerlists WHERE listOwner='".escape_string($_SESSION['clientID'])."'";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result))
			print '<div style="display:table-row"><div class="cobll" onmouseover="this.className=\'cobhl\'" onmouseout="this.className=\'cobll\'" onclick="dosaveitem(' . $rs['listID'] . ')" style="display:table-cell"><a class="ectlink wishlistmenu" href="#" onclick="dosaveitem('.$rs['listID'].')">' . htmlspecials($rs['listName']) . "</a></div></div>\r\n";
		ect_free_result($result);
		print '<div style="display:table-row"><div id="savelistcartrow" class="cobll" onmouseover="this.className=\'cobhl\'" onmouseout="this.className=\'cobll\'" onclick="document.location=\'cart.php\'" style="display:table-cell"><a class="ectlink wishlistmenu" href="#" onclick="document.location=\'cart.php\'">' . $GLOBALS['xxSwCart'] . "</a></div></div>\r\n";
		print '<div style="display:table-row"><div id="savelistcreaterow" class="cobll" onmouseover="this.className=\'cobhl\'" onmouseout="this.className=\'cobll\'" onclick=""document.location=\'' . $customeraccounturl . '#list\'" style="display:table-cell"><a class="ectlink wishlistmenu" href="#" onclick="document.location=\'' . $customeraccounturl . '#list\'">' . $GLOBALS['xxCreaGR'] . "</a></div></div>\r\n";
		print '</div>';
	}
	if(getget('lid')!='' && is_numeric(getget('lid'))) $listid=getget('lid'); else $listid='';
	if(@$_SESSION['clientID']!='' && $checkoutmode=='savedcart' && $listid!=''){
		$sSQL="SELECT listID,listName FROM customerlists WHERE listID='".escape_string($listid)."' AND listOwner='".escape_string($_SESSION['clientID'])."'";
		$result=ect_query($sSQL) or ect_error();
		if(! ($rs=ect_fetch_assoc($result))) $querystr='cartCompleted=0 AND '.getsessionsql(); else{ $listname=$rs['listName']; $querystr='cartCompleted>=0 AND cartListID='.$listid; }
		ect_free_result($result);
	}elseif($checkoutmode=='savedcart'){
		$querystr='cartCompleted=3 AND cartListID=0 AND '.getsessionsql();
	}else
		$querystr='cartCompleted=0 AND '.getsessionsql();
	if(getget('pli')!='' && is_numeric(getget('pli')) && getget('pla')!=''){
		$sSQL="SELECT listID,listName FROM customerlists WHERE listID='".escape_string(getget('pli'))."' AND listAccess='".escape_string(getget('pla'))."'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$checkoutmode='savedcart';
			$listid=$rs['listID'];
			$listname=$rs['listName'];
			$querystr='cartCompleted=3 AND cartListID='.$listid;
		}else{
			// Error case
		}
		ect_free_result($result);
	}
	if($checkoutmode=='dologin' || $checkoutmode=='donewaccount' || getget('mode')=='login' || getget('mode')=='newaccount'){
		$itemsincart=0;
		$result='';
	}else{
		$sSQL="SELECT cartID,cartProdID,cartProdName,cartProdPrice,cartQuantity,pWeight,pShipping,pShipping2,pExemptions,pSection,pDims,pTax,pStaticPage,pDisplay,'' AS pImage,'' AS pLargeImage,cartCompleted,pGiftWrap,cartGiftWrap,pStaticURL,".getlangid('pDescription',2).','.getlangid('pLongDescription',4).' FROM cart LEFT JOIN products ON cart.cartProdID=products.pID LEFT OUTER JOIN sections ON products.pSection=sections.sectionID WHERE ' . $querystr . ' ORDER BY cartID';
		$result=ect_query($sSQL) or ect_error();
		$itemsincart=ect_num_rows($result);
	}
?><script type="text/javascript">/* <![CDATA[ */
var checkedfullname=false;
function checknewaccount(){
var frm=document.forms.checkoutform;
if(frm.name.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxName'])?>\".");
	frm.name.focus();
	return(false);
}
var regex=/ /;
if(!checkedfullname && !regex.test(frm.name.value)){
	alert("<?php print jscheck($GLOBALS['xxFulNam'] . ' "' . $GLOBALS['xxName'])?>\".");
	frm.name.focus();
	checkedfullname=true;
	return(false);
}
var regex=/[^@]+@[^@]+\.[a-z]{2,}$/i;
if(!regex.test(frm.email.value)){
	alert("<?php print jscheck($GLOBALS['xxValEm'])?>");
	frm.email.focus();
	return(false);
}
if(frm.pass.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxPwd'])?>\".");
	frm.pass.focus();
	return(false);
}
var regex=/^[0-9A-Za-z\_\@\.\-]+$/;
if(!regex.test(frm.pass.value)){
    alert("<?php print jscheck($GLOBALS['xxAlphaNu'] . ' "' . $GLOBALS['xxPwd'])?>\".");
    frm.pass.focus();
    return(false);
}
<?php	if(@$extraclientfield1required){ ?>
if(frm.extraclientfield1.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . @$GLOBALS['extraclientfield1'])?>\".");
	frm.extraclientfield1.focus();
	return(false);
}
<?php	}
		if(@$extraclientfield2required){ ?>
if(frm.extraclientfield2.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . @$GLOBALS['extraclientfield2'])?>\".");
	frm.extraclientfield2.focus();
	return(false);
}
<?php	} ?>
frm.mode.value='donewaccount';
frm.action='<?php if(@$forceloginonhttps) print $pathtossl?>cart.php';
return true;
}
function checkchecked(){
	ischecked=false
	var inputs=document.getElementsByTagName('input');
	for(var i=0; i < inputs.length; i++)
		if(inputs[i].type=='checkbox'){
			if(inputs[i].checked&&inputs[i].name.substr(0,5)=='delet') ischecked=true;
		}
	if(! ischecked) alert("<?php print jscheck($GLOBALS['xxNotSel'])?>");
	return(ischecked);
}
<?php	$theqs='';
	foreach(@$_GET as $key => $val){
		$theqs.=urlencode(strip_tags($key)) . '=' . urlencode(strip_tags($val)) . '&';
	}
	if($theqs!='') $theqs='?' . substr($theqs, 0, -1);
?>
function dodelete(cid){
var ECinput=document.createElement("input");
ECinput.setAttribute("type", "hidden");
ECinput.setAttribute("name", "delet"+cid);
ECinput.setAttribute("value", "ON");
document.forms.checkoutform.appendChild(ECinput);
doupdate();
return false;
}
function updateestimator(){
if(<?php	if($wantzipselector) print "document.getElementById('zip').value!=''&&";
			if($wantstateselector) print "(document.getElementById('state').disabled||document.getElementById('state').selectedIndex!=0)&&";
?>true)return doupdate();
}
function doupdate(){
	document.forms.checkoutform.mode.value='update';
	document.forms.checkoutform.action='cart.php<?php print $theqs ?>';
	document.forms.checkoutform.onsubmit='';
	document.forms.checkoutform.submit();
	return false;
}
var savemenuaction='saveitem';
function dosaveitem(lid){
	if(savemenuaction=='saveitem'){
		var ECinput=document.createElement("input");
		ECinput.setAttribute("type", "hidden");
		ECinput.setAttribute("name", "delet"+whichcartid);
		ECinput.setAttribute("value", "ON");
		document.forms.checkoutform.appendChild(ECinput);
		document.forms.checkoutform.mode.value='savecart';
		document.forms.checkoutform.listid.value=lid;
		document.forms.checkoutform.action='cart.php<?php print $theqs ?>';
		document.forms.checkoutform.onsubmit='';
		document.forms.checkoutform.submit();
	}else{
		document.location='cart.php?mode=sc&lid='+lid;
	}
}
function movetocart(cid){
	var ECinput=document.createElement("input");
	ECinput.setAttribute("type", "hidden");
	ECinput.setAttribute("name", "delet"+cid);
	ECinput.setAttribute("value", "ON");
	document.forms.checkoutform.appendChild(ECinput);
	document.forms.checkoutform.mode.value='movetocart';
	document.forms.checkoutform.action='cart.php<?php print $theqs ?>';
	document.forms.checkoutform.onsubmit='';
	document.forms.checkoutform.submit();
	return(false);
}
var cartoversldiv,whichcartid;
function cartdispsavelist(clid,isleft,wantextras,evt,twin){
	whichcartid=clid;
	cartoversldiv=false
	var theevnt=(!evt)?twin.event:evt;//IE:FF
	if(wantextras){
		document.getElementById('savelistcartrow').style.display='';
		document.getElementById('savelistcreaterow').style.display='';
	}else{
		document.getElementById('savelistcartrow').style.display='none';
		document.getElementById('savelistcreaterow').style.display='none';
	}
	var sld=document.getElementById('savecartlist');
	var scrolltop=(document.documentElement.scrollTop?document.documentElement.scrollTop:document.body.scrollTop);
	var scrollleft=(document.documentElement.scrollLeft?document.documentElement.scrollLeft:document.body.scrollLeft);
	sld.style.left=((theevnt.clientX+scrollleft)-(isleft?0:sld.offsetWidth))+'px';
    sld.style.top=(theevnt.clientY+scrolltop)+'px';
	sld.style.visibility="visible";
	setTimeout('cartchecksldiv()',2000);
	return(false);
}
function cartchecksldiv(){
	var sld=document.getElementById('savecartlist');
	if(! cartoversldiv)
		sld.style.visibility='hidden';
}
function selaltrate(id){
	document.forms.checkoutform.altrates.value=id;
	doupdate();
}
<?php
	if(($adminAltRates==2 && @$_SESSION['xsshipping']!='')||!@$estimateshipping) $adminAltRates=0;
	if($adminAltRates==2){
		$sSQL='SELECT altrateid,'.getlangid('altratetext',65536).' FROM alternaterates WHERE usealtmethod'.$international.'<>0 OR altrateid='.($international==''||$adminIntShipping==0?$adminShipping:$adminIntShipping).' ORDER BY altrateorder,altrateid';
		$result2=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result2)>0){
			print "var shipservicetext=[];\r\n";
			print 'var extraship=[';
			$addcomma='';
			$servicetext='';
			while($rs=ect_fetch_assoc($result2)){
				$servicetext.='shipservicetext[' . $rs['altrateid'] . ']="' . $rs[getlangid('altratetext',65536)] . '";' . "\r\n";
				if($rs['altrateid']!=$shipType){
					print $addcomma . $rs['altrateid'];
					$addcomma=',';
				}
			}
			print "];\r\n";
			print $servicetext . "\r\n"; ?>
function addCommas(ns,decs,thos){
if((dpos=ns.indexOf(decs))<0)dpos=ns.length;
dpos-=3;
while(dpos>0){
	ns=ns.substr(0,dpos)+thos+ns.substr(dpos);
	dpos-=3;
}
return(ns);
}
function formatestprice(i){
<?php
	$tempStr=FormatEuroCurrency(0);
	print "var pTemplate='".$tempStr."';\r\n";
	if(strstr($tempStr,',') || strstr($tempStr,'.')){ ?>
if(i==Math.round(i))i=i.toString()+".00";
else if(i*10.0==Math.round(i*10.0))i=i.toString()+"0";
else if(i*100.0==Math.round(i*100.0))i=i.toString();
<?php
	}
	print 'i=addCommas(i.toString()'.(strstr($tempStr,',')?".replace(/\\./,','),',','.'":",'.',','").');';
	print 'pTemplate=pTemplate.toString().replace(/\d[,.]*\d*/,i.toString());';
	print 'return(pTemplate);';
?>}
function acajaxcallback(){
	if(ajaxobj.readyState==4){
		var restxt=ajaxobj.responseText;
		var gssr=restxt.split('SHIPSELPARAM=');
		if(gssr[2]!='ERROR'&&parseFloat(gssr[1])<bestestimate){
			if(document.getElementById('estimatorcell')){
				document.getElementById('estimatorcell').colSpan='1';
				document.getElementById('estimatorcell').align='right';
				var newcell=document.getElementById('estimatorrow').insertCell(-1);
				newcell.className='cobll';
				newcell.innerHTML='&nbsp;';
				document.getElementById('estimatorcell').id='';
			}
			bestestimate=parseFloat(gssr[1]);
			bestcarrier=parseInt(gssr[4]);
			document.getElementById('estimatorspan').innerHTML=formatestprice(bestestimate);
			if(document.getElementById('shippingtotal_cntnr'))document.getElementById('shippingtotal_cntnr').style.display='';
			var discounts=0;
			if(document.getElementById('discountspan')){
				discounts=document.getElementById('discountspan').innerHTML.replace(/[^0-9\.\,]+/g,'');
				var testlatin=/\,\d\d$/;
				if(testlatin.test(discounts))
					discounts=parseFloat(discounts.replace(/\./g,'').replace(/,/g,'.'));
				else
					discounts=parseFloat(discounts.replace(/,/g,''));
			}
<?php	if(@$showtaxinclusive!=0) print "var countrytax=parseFloat(gssr[3]);document.getElementById('countrytaxspan').innerHTML=formatestprice(countrytax);\r\n"; else print "var countrytax=0;\r\n"; ?>
			document.getElementById('grandtotalspan').innerHTML=(formatestprice(Math.round((vstotalgoods+bestestimate+countrytax-discounts)*100)/100.0));
				}else if(gssr[2]=='ERROR'&&document.getElementById('estimatorerrors')){
			if(document.getElementById('estimatorerrors').innerHTML.indexOf(gssr[0])==-1){
				if(gssr[0]=='<?php print jsescape($GLOBALS['xxInvZip'])?>'||gssr[0]=='<?php print jsescape($GLOBALS['xxPlsZip'])?>'||gssr[0].indexOf('The postal code')>=0){
					var x=document.getElementsByClassName("cartzipselectortext");
					for(var aci=0;aci<x.length;aci++) if(x[aci].className.indexOf('ectwarning')==-1)x[aci].className+=' ectwarning';
					var x=document.getElementsByClassName("cartzipselector");
					for(var aci=0;aci<x.length;aci++) if(x[aci].className.indexOf('ectwarning')==-1)x[aci].className+=' ectwarning';

				}
				if(gssr[0]=='<?php print jsescape($GLOBALS['xxPlsSta'])?>'){
					var x=document.getElementsByClassName("cartstateselectortext");
					for(var aci=0;aci<x.length;aci++) if(x[aci].className.indexOf('ectwarning')==-1)x[aci].className+=' ectwarning';
					var x=document.getElementsByClassName("cartstateselector");
					for(var aci=0;aci<x.length;aci++) if(x[aci].className.indexOf('ectwarning')==-1)x[aci].className+=' ectwarning';

				}
				document.getElementById('estimatorerrors').innerHTML+='<div class="estimatorerror">'+gssr[0]+'</div>';
			}
		}
		getalternatecarriers();
	}
}
function getalternatecarriers(){
	if(extraship.length>0){
		var shiptype=extraship.shift();
		if(document.getElementById('estimatorchecktext')){
			document.getElementById('estimatorchecktext').innerHTML='Checking carrier';
			document.getElementById('estimatorcheckcarrier').innerHTML=shipservicetext[shiptype];
		}else
			document.getElementById('checkaltspan').innerHTML='Checking ' + shipservicetext[shiptype] + ":";
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange=acajaxcallback;
		ajaxobj.open("GET", "vsadmin/shipservice.php?ratetype=estimator&best="+bestestimate+"&shiptype="+shiptype+"&sessionid=<?php print urlencode($thesessionid)?>&destzip=<?php print urlencode($destZip)?>&sc=<?php print urlencode($shipcountry)?>&scc=<?php print urlencode($shipCountryCode)?>&sta=<?php print urlencode($shipStateAbbrev)?>", true);
		ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxobj.send(null);
	}else{
		if(document.getElementById('estimatorchecktext')){
			document.getElementById('estimatorchecktext').innerHTML="<?php print $GLOBALS['xxBesRaU']?>";
			document.getElementById('estimatorcheckcarrier').innerHTML=shipservicetext[bestcarrier];
		}else
			document.getElementById('checkaltspan').innerHTML="<?php print $GLOBALS['xxBesRaU']?> " + shipservicetext[bestcarrier] + ":";
		document.forms.checkoutform.altrates.value=bestcarrier;
	}
}
<?php	}
		ect_free_result($result2);
	} ?>
function applycertcallback(){
	if(ajaxobj.readyState==4){
		var retvals=ajaxobj.responseText.split('&');
		alert(retvals[1]);
		if(retvals[0]=='success'){document.getElementById("cpncode").value='';document.location.reload();}
	}
}
function applycert(){
	var cpncode=document.getElementById("cpncode").value;
	if(cpncode!=""){
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange=applycertcallback;
		ajaxobj.open("GET", "vsadmin/ajaxservice.php?action=applycert&stg1=1&cpncode="+cpncode, true);
		ajaxobj.send(null);
	}
}
function removecert(cpncode){
	if(cpncode!=''){
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange=applycertcallback;
		ajaxobj.open("GET", "vsadmin/ajaxservice.php?action=applycert&stg1=1&act=delete&cpncode="+cpncode, true);
		ajaxobj.send(null);
	}
}
/* ]]> */</script>
<?php
	print '<form method="post" name="checkoutform" action="' . $cartpath . '"' . ($itemsincart>0?' onsubmit="return changechecker(this)"':'') . '>';
	
	print whv('mode','checkout') . whv('sessionid',getsessionid()) . whv('PARTNER',strip_tags(trim(@$_COOKIE['PARTNER']))) . whv('cart','') . whv('listid','');
	if(@$_SESSION['noredeempoints']==TRUE) writehiddenidvar('noredeempoints', '1');
	if($adminAltRates!=0) print whv('altrates',getpost('altrates'));
	if(@$GLOBALS['xxCoStp1']!='' && (getrequest('mode')=='' || getrequest('mode')=='update') && $itemsincart>0) print '<div class="checkoutsteps">' . $GLOBALS['xxCoStp1'] . '</div>';
	print '<div class="cartcontentsdiv">';
	function customerlistselector($currlist){
		global $enablewishlists,$listname,$checkoutmode,$imgsaveitems;
		if(@$enablewishlists==TRUE){
			if($listname==''){
				if(@$checkoutmode=='savedcart') $clslistname=$GLOBALS['xxMyWisL']; else $clslistname=$GLOBALS['xxVGifRe'];
			}else
				$clslistname=$listname;
			print ' - <span class="cartwishlists">' . imageorlink(@$imgsaveitems,htmldisplay($clslistname),'',"savemenuaction='switchlist';return cartdispsavelist(0,true,true,event,window)", TRUE) . '</span>';
		}
	}
	function displaycartactions($cid){
		global $checkoutmode,$imgdelete,$imgsaveitems,$enablewishlists;
		print '<div class="cartdelete"><a href="#"><img class="cartdelete" src="images/delete.png" alt="'.$GLOBALS['xxDelete'].'" onclick="return dodelete('.$cid.')" /></a></div>';
		if($checkoutmode=='savedcart')
			print '<div class="movetocart">'.imageorlink(@$imgsaveitems,$GLOBALS['xxMovCar'],'','return movetocart('.$cid.')', TRUE).'</div>';
		if(@$_SESSION['clientID']!='' && @$enablewishlists==TRUE)
			print '<div class="savetolist">'.imageorlink(@$imgsaveitems,$GLOBALS['xxAddLis'],'','savemenuaction=\'saveitem\';return cartdispsavelist('.$cid.',false,false,event,window)', TRUE).'</div>';
	}
	function writeestimatormenu(){
		global $shippingoptionsasradios,$adminAltRates,$international,$freeshipamnt,$shipping,$handling,$adminShipping,$adminIntShipping,$mobilebrowser;
		if($adminAltRates==2){
			print '<div class="estimatorchecktext" id="estimatorchecktext"></div><div class="estimatorcheckcarrier" id="estimatorcheckcarrier"></div>';
		}elseif($adminAltRates==1){
			$sSQL='SELECT altrateid,altratename,'.getlangid('altratetext',65536).',usealtmethod,usealtmethodintl FROM alternaterates WHERE usealtmethod'.$international.'<>0 OR altrateid='.($international==''||$adminIntShipping==0?$adminShipping:$adminIntShipping).' ORDER BY altrateorder,altrateid';
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result)>0){
				if(@$shippingoptionsasradios!=TRUE) print '<select id="altratesselect" size="1" onchange="selaltrate(this[this.selectedIndex].value)"'.($mobilebrowser?' style="font-size:10px"':'').'>'; else print '<div'.($mobilebrowser?' style="font-size:10px"':'').'>';
				while($rs=ect_fetch_assoc($result)){
					writealtshipline($rs[getlangid('altratetext',65536)],$rs['altrateid'],$GLOBALS['xxOrCom'].': ',$GLOBALS['xxShEsWi'].': ',FALSE);
				}
				if(@$shippingoptionsasradios!=TRUE) print '</select>'; else print '</div>';
			}
			ect_free_result($result);
		}
	}
	if((@$enableclientlogin==TRUE || @$forceclientlogin==TRUE) && !$amazonpaycheckout){
		if((getget('mode')=='newaccount' && @$allowclientregistration==TRUE) || ($checkoutmode=='donewaccount' && @$loginerror!='')){
			$noshowcart=TRUE;
			if(@$forceloginonhttps && (@$_SERVER['HTTPS']!='on' && @$_SERVER['SERVER_PORT']!='443') && strpos(@$pathtossl,'https')!==FALSE){ header('Location: '.$pathtossl.basename($_SERVER['PHP_SELF']).(@$_SERVER['QUERY_STRING']!='' ? '?'.strip_tags(@$_SERVER['QUERY_STRING']) : '')); exit; }
			print '<div class="cartnewaccount_cntnr">';
				print '<div class="cartnewaccountheader' . (@$loginerror!='' ? ' ectwarning cartnewaccloginerror' : '') . '">' . (@$loginerror!='' ? $loginerror : $GLOBALS['xxNewAcc']) . '</div>';
				print '<div class="cartacctloginname_cntnr"><div class="cartacctloginnametext">' . $redstar . labeltxt($GLOBALS['xxName'],'name') . '</div><div class="cartacctloginname"><input type="text" name="name" id="name" size="' . ($mobilebrowser?16:30) . '" value="' . htmlspecials(getpost('name')) . '" /></div></div>';
				print '<div class="cartaccloginemail_cntnr"><div class="cartaccloginemailtext">' . $redstar . labeltxt($GLOBALS['xxEmail'],'email') . '</div><div class="cartaccloginemail"><input type="email" name="email" id="email" size="' . ($mobilebrowser?16:30) . '" value="' . htmlspecials(getpost('email')) . '" /></div></div>';
				print '<div class="cartaccloginpassword_cntnr"><div class="cartaccloginpasswordtext">' . $redstar . labeltxt($GLOBALS['xxPwd'],'pass') . '</div><div class="cartaccloginpassword"><input type="password" name="pass" id="pass" size="14" value="' . htmlspecials(getpost('pass')) . '" autocomplete="off" /></div></div>';
				if(@$GLOBALS['extraclientfield1']!='' || @$GLOBALS['extraclientfield2']!=''){
					if(trim(@$GLOBALS['extraclientfield1'])!='')
						print '<div class="cartaccloginextra1_cntnr"><div class="cartaccloginextra1text">' . (@$GLOBALS['extraclientfield1required']?$redstar:'') . labeltxt(@$GLOBALS['extraclientfield1'],"extraclientfield1") . '</div><div class="cartaccloginextra1"><input type="text" name="extraclientfield1" id="extraclientfield1" size="16" value="' . htmlspecials(getpost("extraclientfield1")) . '" /></div></div>';
					if(trim(@$GLOBALS['extraclientfield2'])!='')
						print '<div class="cartaccloginextra2_cntnr"><div class="cartaccloginextra2text">' . (@$GLOBALS['extraclientfield2required']?$redstar:'') . labeltxt(@$GLOBALS['extraclientfield2'],"extraclientfield2") . '</div><div class="cartaccloginextra2"><input type="text" name="extraclientfield2" id="extraclientfield2" size="16" value="' . htmlspecials(getpost("extraclientfield2")) . '" /></div></div>';
				}
				if(@$GLOBALS['nomailinglist']!=TRUE){
					print '<div class="cartaccloginallowpromo_cntnr">';
						print '<div class="cartaccloginallowpromo"><input type="checkbox" name="allowemail" value="ON"' . (@$allowemaildefaulton==TRUE || getpost('allowemail')=='ON' ? ' checked="checked"' : '') . ' /></div>';
						print '<div class="cartaccloginallowpromotext">' . $GLOBALS['xxAlPrEm'] . '<div class="cartacclogineverdivulge">' . $GLOBALS['xxNevDiv'] . '</div></div>';
					print '</div>';
				}
				print '<div class="cartaccloginalsubmit">' . imageorsubmit(@$imgcreateaccount,$GLOBALS['xxCrNwAc'].'" onclick="return checknewaccount();','createaccount') . '</div>';
			print '</div>';
		}elseif(getget('mode')!='login' && @$loginerror==''){
			if(@$_SESSION['clientID']!=''){
				print '<div class="cartloggedin">' . $GLOBALS['xxLogInA'] . ' <span class="cartloginname">' . htmlspecials($_SESSION['clientUser']) . '</span>';
				customerlistselector($listid);
				print ' - <a class="ectlink" href="cart.php?mode=logout">' . $GLOBALS['xxLogout'] . ' </a>';
				if(getget('warncheckspamfolder')=='true') print '<div class="thanksubscribe">' . $GLOBALS['xxThkSub'] . '</div><div class="spamwarn">' . $GLOBALS['xxSpmWrn'] . '</div>';
				if(getget('cartchanged')=='true') print '<div class="cartchanged">'.$GLOBALS['xxCarCha'].'</div>';
				print '</div>';
			}elseif(@$noclientloginprompt!=TRUE && getget('pli')==''){
				print '<div class="loginprompt">' .
					'<div class="logintoaccount">' . imageorbutton(@$imgloginaccount,$GLOBALS['xxLogAcc'],'logintoaccount',(@$forceloginonhttps?$pathtossl:'').'cart.php?mode=login',FALSE) . '</div>';
				if(@$allowclientregistration) print '<div class="createaccount">' . imageorbutton(@$imgcreateaccount,$GLOBALS['xxCreAcc'],'createaccount','cart.php?mode=newaccount',FALSE).'</div>';
				print '</div>';
			}
		}else{
			$noshowcart=TRUE;
			if(@$forceloginonhttps && (@$_SERVER['HTTPS']!='on' && @$_SERVER['SERVER_PORT']!='443') && strpos(@$pathtossl,'https')!==FALSE){ header('Location: '.$pathtossl.basename($_SERVER['PHP_SELF']).(@$_SERVER['QUERY_STRING']!='' ? '?'.strip_tags(@$_SERVER['QUERY_STRING']) : '')); exit; }
			print '<div class="cartlogin_cntnr">' . whv('refurl',strip_tags(@$_REQUEST['refurl'])) . '<div class="cartloginheader' . (@$loginerror!=''?' ectwarning':'') . '">' . (@$loginerror!='' ? $loginerror : $GLOBALS['xxLiDets']) . '</div>';
			print '<div class="cartloginemail_cntnr"><div class="cartloginemailtext">' . labeltxt($GLOBALS['xxEmail'],'email') . '</div><div class="cartloginemail"><input type="email" name="email" id="email" size="31" value="' . htmlspecials(getpost('email')) . '" /></div></div>';
			print '<div class="cartloginpwd_cntnr"><div class="cartloginpwdtext">' . labeltxt($GLOBALS['xxPwd'],'pass') . '</div><div class="cartloginpwd"><input type="password" name="pass" id="pass" size="20" value="' . htmlspecials(getpost('pass')) . '" autocomplete="off" /></div></div>';
			print '<div class="cartlogincookie_cntnr"><div class="cartlogincookie"><input type="checkbox" name="cook" id="cook" value="ON" /></div><div class="cartlogincookietext">' . labeltxt($GLOBALS['xxRemLog'],'cook') . '</div></div>';
			print '<div class="cartloginbuttons_cntnr"><div class="cartloginbuttons">';
			print '<div class="cartloginlogin">' . imageorsubmit(@$imgcartaccountlogin,$GLOBALS['xxSubmt']."\" onclick=\"document.forms.checkoutform.action='" .(@$forceloginonhttps?$pathtossl:''). "cart.php';document.forms.checkoutform.mode.value='dologin';",'cartaccountlogin') . '</div>';
			if(@$allowclientregistration==TRUE) print '<div class="cartloginnewacct">' . imageorbutton(@$imgnewaccount,$GLOBALS['xxNewAcc'],'newaccount','cart.php?mode=newaccount',FALSE) . '</div>';
			print '<div class="cartloginforgotpwd">' . imageorbutton(@$imgforgotpassword,$GLOBALS['xxForPas'],'forgotpassword',$customeraccounturl.'?mode=lostpassword',FALSE) . '</div></div></div></div>';
		}
	}
	if(getget('pli')!='' && is_numeric(getget('pli')) && getget('pla')!=''){
		print '<div class="cartviewinglist">' . $GLOBALS['xxArVwLi'] . ': <span class="listname">' . htmlspecials($listname) . '</span></div>';
	}
	if(@$carterror!=''){
		print '<div class="carterror ectwarning">' . $carterror . '</div>';
	}
	if($noshowcart){
		// do nothing
	}elseif($loginsuccess){
		print '<div class="cartloginsuccess">' . $GLOBALS['xxLISuc'] . '</div><div class="cartloginsuccesswait"><a class="ectlink" href="cart.php">' . $GLOBALS['xxPlWtFw'] . '</a></div>';
	}elseif($itemsincart>0){
		if(!$amazonpaycheckout){
			if($stockwarning || $backorder){
				print '<div class="cartstockbackorder_cntnr">';
				if($stockwarning){
					print '<div class="cartstockwarning"><div class="cartoutstock">' . $GLOBALS['xxNoStok'].'</div><div class="cartstockacceptlevel">'.$GLOBALS['xxStkUTo'].'<a class="ectlink" href="cart.php">' . $GLOBALS['xxClkHere'] . '</a></div>';
					if(getget('mode')!='add' && $checkoutmode!='update') print '<div class="cartstockjustpurchased">('.$GLOBALS['xxJusBuy'].')</div>';
					print '</div>';
				}
				if($backorder) print '<div class="cartbackorder">' . $GLOBALS['xxBakOrW'] . '</div>';
				print '</div>';
			}
			print '<div class="cartdetails_cntnr">';
			print '<div class="cartdetails cartdetailsid">' . $GLOBALS['xxCODets'] . '</div>';
			print '<div class="cartdetails cartdetailsname">' . $GLOBALS['xxCOName'] . '</div>';
			if(@$GLOBALS['nopriceanywhere']!=TRUE) print '<div class="cartdetails cartdetailsprice">' . $GLOBALS['xxCOUPri'] . '</div>';
			print '<div class="cartdetails cartdetailsquant">' . $GLOBALS['xxQuanty'] . '</div>';
		                    
			//if(@$GLOBALS['nopriceanywhere']!=TRUE) print '<div class="cartdetails cartdetailstotal">' . $GLOBALS['xxTotal'] . '</div>';
                        if(@$GLOBALS['nopriceanywhere']!=TRUE) print '<div class="cartdetails cartdetailstotal">' . $GLOBALS['xxTotal'] . '</div>';
                        print '<div class="cartdetails cartdetailscheckbox">Home</div>';
                        print '<div class="cartdetails cartdetailslocationdropbox">Location</div>';
				print '</div>';
		}
		$totaldiscounts=0;
		$changechecker='';
		$index=0;
		while($rsCart=ect_fetch_assoc($result))
			$cartrows[$index++]=$rsCart;
		for($index=0;$index<$itemsincart;$index++){
			$alldata=$cartrows[$index];
			$sSQL="SELECT imageSrc FROM productimages WHERE imageType=0 AND imageProduct='" . escape_string($alldata['cartProdID']) . "' ORDER BY imageNumber LIMIT 0,1";
			$result2=ect_query($sSQL) or ect_error();
			if($rs2=ect_fetch_assoc($result2)) $alldata['pImage']=$rs2['imageSrc'];
			ect_free_result($result2);
			if(is_null($alldata['pWeight'])) $alldata['pWeight']=0;
			if(is_null($alldata['pExemptions'])){
				if($alldata['cartProdID']==$giftcertificateid || $alldata['cartProdID']==$donationid) $alldata['pExemptions']=15;
				if($alldata['cartProdID']==$giftwrappingid) $alldata['pExemptions']=12;
			}
			$changechecker.='if(document.checkoutform.quant' . $alldata['cartID'] . '.value!=' . $alldata['cartQuantity'] . ") dowarning=true;\n";
			$theoptions='';
			$theoptionspricediff=0;
			$isoutofstock=FALSE;
			$sSQL="SELECT coID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff,optAltImage,optType FROM cartoptions LEFT JOIN options ON cartoptions.coOptID=options.optID LEFT JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE coCartID=" . $alldata['cartID'] . ' ORDER BY coID';
			$opts=ect_query($sSQL) or ect_error();
			$optPriceDiff=0;
			while($rs=ect_fetch_assoc($opts)){
				$optoutofstock=FALSE;
				foreach($outofstockarr as $outofstockitem){
					if($outofstockitem[0]==$rs['coID'] && $outofstockitem[1]==TRUE && $outofstockitem[4]==FALSE){ $optoutofstock=TRUE; $isoutofstock=TRUE; }
				}
				$theoptionspricediff+=$rs['coPriceDiff'];
				$alldata['pWeight']+=(double)$rs['coWeightDiff'];
				if(trim($rs['optAltImage'])!='' && @$useimageincart && $rs['optType']!=4){
					if(strpos(trim($alldata['pImage']), '%s')!==FALSE) $alldata['pImage']=str_replace('%s', $rs['optAltImage'], trim($alldata['pImage'])); else $alldata['pImage']=$rs['optAltImage'];
				}
				$theoptions.='<div class="cartoptionsline"><div class="cartoptiongroup">' . $rs['coOptGroup'] . '</div>' .
					'<div class="cartoption">' . str_replace(array(' ',"\r\n","\n"),array('&nbsp;','<br />','<br />'),htmlspecials($rs['coCartOption'])) . '</div>';
				if(!@$GLOBALS['nopriceanywhere']) $theoptions.='<div class="cartoptionprice">' . ($rs['coPriceDiff']==0 || @$GLOBALS['hideoptpricediffs']==TRUE ? '- ' : FormatEuroCurrency($rs['coPriceDiff'])) . '</div>' .
					'<div class="cartoptionoutstock">' . ($optoutofstock ? $GLOBALS['xxLimSto'] : '&nbsp;') . '</div>';
				if(!@$GLOBALS['nopriceanywhere']) $theoptions.='<div class="cartoptiontotal">' . ($rs['coPriceDiff']==0 || @$GLOBALS['hideoptpricediffs']==TRUE ? '- ' : FormatEuroCurrency($rs['coPriceDiff']*$alldata['cartQuantity'])) . '</div>' .
					'<div class="cartoptionspacer"></div></div>' . "\r\n";
				$totalgoods+=($rs['coPriceDiff']*(int)$alldata['cartQuantity']);
				if(($alldata['pExemptions'] & 8)!=8) $handlingeligablegoods+=$rs['coPriceDiff']*(int)$alldata['cartQuantity'];
			}
			ect_free_result($opts);
			foreach($outofstockarr as $outofstockitem){
				if($outofstockitem[0]==$alldata['cartID'] && $outofstockitem[1]==FALSE && $outofstockitem[4]==FALSE) $isoutofstock=TRUE;
			}
			$opts=ect_query("SELECT imageSrc FROM productimages WHERE imageType=0 AND imageProduct='" . escape_string($alldata['cartProdID']) . "' ORDER BY imageNumber LIMIT 0,1") or ect_error();
			if($rs=ect_fetch_assoc($opts)) $alldata['pImage']=$rs['imageSrc'];
			ect_free_result($opts);
			$opts=ect_query("SELECT imageSrc FROM productimages WHERE imageType=1 AND imageProduct='" . escape_string($alldata['cartProdID']) . "' ORDER BY imageNumber LIMIT 0,1") or ect_error();
			if($rs=ect_fetch_assoc($opts)) $alldata['pLargeImage']=$rs['imageSrc'];
			ect_free_result($opts);
			if($alldata['pDisplay']!=0 && @$linkcartproducts==TRUE && (@$forcedetailslink==TRUE || trim($alldata[getlangid('pLongDescription',4)])!='' || trim($alldata['pLargeImage'])!='')){
				$thedetailslink=getdetailsurl($alldata['cartProdID'],$alldata['pStaticPage'],$alldata['cartProdName'],$alldata['pStaticURL'],'',@$GLOBALS['pathtohere']);
				if(@$detailslink!=''){
					$sSQL="SELECT imageSrc FROM productimages WHERE imageType=1 AND imageProduct='" . escape_string($alldata['cartProdID']) . "' ORDER BY imageNumber LIMIT 0,1";
					$result2=ect_query($sSQL) or ect_error();
					if($rs2=ect_fetch_assoc($result2)) $alldata['pLargeImage']=$rs2['imageSrc'];
					ect_free_result($result2);
					$startlink=str_replace('%pid%', $alldata['cartProdID'], str_replace('%largeimage%', $alldata['pLargeImage'], $detailslink));
					$endlink=@$detailsendlink;
				}else{
					$startlink='<a class="ectlink" href="'.$thedetailslink.'">';
					$endlink='</a>';
				}
			}else{
				$startlink='';
				$endlink='';
			}
			if(!$amazonpaycheckout){ // Cart line
				print '<div class="cartandoptsline"><div class="cartline">';
					print '<div class="cartlineid">';
					if(@$useimageincart && ! (trim($alldata['pImage'])=='' || trim($alldata['pImage'])=='prodimages/')) print $startlink . '<img class="cartimage" src="' . $alldata['pImage'] . '" border="0" alt="' . strip_tags($alldata['cartProdName']) . '" />' . $endlink; else print $startlink . $alldata['cartProdID'] . $endlink;
					print '</div><div class="cartlinename">';
					print $startlink . $alldata['cartProdName'] . $endlink;
					if($alldata['pGiftWrap']!=0) print '<div class="giftwrap"><a href="cart.php?mode=gw">' . ($alldata['cartGiftWrap']!=0?$GLOBALS['xxGWrSel']:$GLOBALS['xxGWrAva']) . '</a></div>';
					$sSQL="SELECT quantity,pName,quantity FROM productpackages INNER JOIN products on productpackages.pID=products.pID WHERE packageID='" . escape_string($alldata['cartProdID']) . "'";
					$result2=ect_query($sSQL) or ect_error();
					if(ect_num_rows($result2)>0){
						print '<div class="packagediv">';
						while($rs2=ect_fetch_assoc($result2))
							print '<div class="packagerow">' . $rs2['pName'] . ' (' . $rs2['quantity'] . ')</div>';
						print '</div>';
					}
					ect_free_result($result2);
					print '</div>';
					if(@$GLOBALS['nopriceanywhere']!=TRUE) print '<div class="cartlineprice">' . (@$GLOBALS['hideoptpricediffs']==TRUE ? FormatEuroCurrency($alldata['cartProdPrice'] + $theoptionspricediff) : FormatEuroCurrency($alldata['cartProdPrice'])) . '</div>';
					print '<div class="cartlinequant">';
					if(getget('pla')!='') print $alldata['cartQuantity']; else print '<input class="cartquant" type="text" name="quant' . $alldata['cartID'] . '" value="' . $alldata['cartQuantity'] . '" size="2" maxlength="5" ' . ($isoutofstock ? 'style="background-color:#FF7070;"' : '') . ' />';
					print '</div>';
					
					print '<div class="cartlinecheck">';
					if($checkoutmode!='savedcart'){
					}elseif($alldata['cartCompleted']==0 || $alldata['cartCompleted']==2)
						print $GLOBALS['xxPurcha'] . '<br />';
					elseif($alldata['cartCompleted']==1)
						print $GLOBALS['xxPurchd'] . '<br />';
					displaycartactions($alldata['cartID']);
                                        
					print '</div>';
                                       
					if(@$GLOBALS['nopriceanywhere']!=TRUE) print '<div class="cartlinetotal">' . (@$GLOBALS['hideoptpricediffs']==TRUE ? FormatEuroCurrency(($alldata['cartProdPrice'] + $theoptionspricediff)*$alldata['cartQuantity']) : FormatEuroCurrency($alldata['cartProdPrice']*$alldata['cartQuantity'])) . '</div>';


					
//-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		   $thelocation = '';
		   $theproduct = '';

		   	//CHECK TO SEE IF THE LOCATION HAS BEEN ALREADY CHOSEN FOR THE PRODUCT AND REFLECT THAT IN DROPDOWN

        $sql2 = "SELECT prodID, locID FROM productandlocation WHERE clientID ='" . $_SESSION['clientID'] . "' AND prodID =  '" . $alldata['cartProdID'] . "'";
		   
		$result2=ect_query($sql2);
		while($rs=ect_fetch_assoc($result2)){

				$thelocation = $rs['locID'];
				$theproduct = $rs['prodID'];

			}


		//IF VALUE OF CHECKBOX EQUALS THEPRODUCT THEN CHECK IT
		if($theproduct == escape_string($alldata['cartProdID']) ){

 		print '<div class="cartlinecheckbox"><input type="checkbox" name="ck_homepage-' . $counter . '" id="ck_homepage" value="' . escape_string($alldata['cartProdID']) . '"  checked></div>'; 

		}else
		{
		print '<div class="cartlinecheckbox"><input type="checkbox" name="ck_homepage-' . $counter . '" id="ck_homepage" value="' . escape_string($alldata['cartProdID']) . '"/></div>'; 
			
		}

	
		//POPULATE LOCATOIN DROPDOWN
		$sql = "SELECT id,location FROM productlocation WHERE clientid ='" . $_SESSION['clientID'] . "'";
		$result=ect_query($sql);



		

		 $counter = $counter + 1;
		
         print '<div class="cartlinedropbox"><select name="sel_location[]">';
		 print '<option value="">No Location</option></div>'; 

			while($rs=ect_fetch_assoc($result)){
		
				if($rs['id'] == $thelocation ){
				
					print "<option value = " . $rs['id'] . " selected >" .  $rs['location'] . "</option>";
				}
				else
				{
					print "<option value = " . $rs['id'] . ">" .  $rs['location'] . "</option>";
				}
				
				
			}

			print '</select>';

    		print '<div class="cartdetails"><input type="hidden" name="sel_product[]" value="' . escape_string($alldata['cartProdID']) . '" </div> '; 
									
				print "</div>\r\n";
				print $theoptions . '</div>';


			}

			





			$runTot=$alldata['cartProdPrice'] * (int)$alldata['cartQuantity'];
			$totalquantity+=(int)$alldata['cartQuantity'];
			$totalgoods+=($alldata['cartProdPrice']*(int)$alldata['cartQuantity']);
			$alldata['cartProdPrice']+=$theoptionspricediff;
			if(trim(@$_SESSION['clientID'])!=''){ $alldata['pExemptions']=((int)$alldata['pExemptions'] | ((int)$_SESSION['clientActions'] & 7)); if(((int)$_SESSION['clientActions'] & 32)==32) $alldata['pExemptions']|=8; }
			if(($shipType==2 || $shipType==3 || $shipType==4 || $shipType>=6) && (double)$alldata['pWeight']<=0.0)
				$alldata['pExemptions']=($alldata['pExemptions'] | 4);
			if(@$perproducttaxrate==TRUE){
				if(is_null($alldata['pTax'])) $alldata['pTax']=$countryTaxRate;
				if(($alldata['pExemptions'] & 2)!=2) $countryTax+=(($alldata['pTax'] * $alldata['cartProdPrice'] * (int)$alldata['cartQuantity']) / 100.0);
			}else{
				if(($alldata['pExemptions'] & 2)==2) $countrytaxfree+=$runTot + ($theoptionspricediff * (int)($alldata['cartQuantity']));
			}
			if(($alldata['pExemptions'] & 4)==4) $shipfreegoods+=$runTot; else $somethingToShip=TRUE;
			if(($alldata['pExemptions'] & 8)!=8){ $handlingeligableitem=TRUE; $handlingeligablegoods+=$runTot; }
			if(@$estimateshipping==TRUE && @$_SESSION['xsshipping']=='')
				addproducttoshipping($alldata, $index);
		}
		if(@$showtaxinclusive==0){
			$stateTaxRate=0;
			$countryTaxRate=0;
		}
		if($checkoutmode=='savedcart'||@$GLOBALS['nopriceanywhere']){
			$estimateshipping=FALSE;
			$addextrarows=0;
			$showtaxinclusive=0;
		}else{
			calculatediscounts($totalgoods,FALSE,$rgcpncode);
			if(@$_SESSION['giftcerts']!=''){
				$sSQL="SELECT gcID,gcRemaining FROM giftcertificate WHERE gcRemaining>0 AND gcAuthorized<>0 AND gcID IN ('" . str_replace(' ',"','",escape_string(@$_SESSION['giftcerts'])) . "')";
				$result=ect_query($sSQL) or ect_error();
				while($rs=ect_fetch_assoc($result)) $giftcertsamount+=$rs['gcRemaining'];
				ect_free_result($result);
			}
			if($totaldiscounts>$totalgoods) $totaldiscounts=$totalgoods;
			if($totaldiscounts==0)
				$_SESSION['discounts']='';
			else{
				$_SESSION['discounts']=$totaldiscounts;
				$addextrarows++;
			}
		}
		if(@$estimateshipping==TRUE){
			if(@$_SESSION['xsshipping']=='' && $success){
				if(calculateshipping()){
					insuranceandtaxaddedtoshipping();
					calculateshippingdiscounts(FALSE);
					calculatetaxandhandling();
					$_SESSION['xsshipping']=($shipping+$handling)-$freeshipamnt;
				}else{
					calculatetaxandhandling();
					$handling=0;
				}
			}elseif(@$_SESSION['xsshipping']=='' && ! $success){
				calculatetaxandhandling();
				$handling=0;
			}else{
				$shipping=@$_SESSION['xsshipping'];
				$countryTax=@$_SESSION['xscountrytax'];
				$handling=0;
				calculatetaxandhandling();
			}
		}else
			calculatetaxandhandling();
		if(!$amazonpaycheckout){
			print '<div class="cartshippingandtotals">'; // { cartshippingandtotals
			if($checkoutmode!='savedcart' && round($loyaltypointsavailable*$loyaltypointvalue,2)>0){
				if(@$_SESSION['noredeempoints']!=TRUE){
					$loyaltypointdiscount=$loyaltypointsavailable*$loyaltypointvalue;
					if($loyaltypointdiscount>$totalgoods-$totaldiscounts) $loyaltypointdiscount=$totalgoods-$totaldiscounts;
				}
				print '<div class="cartloyaltypoint_cntnr"><div class="cartloyaltypointmenu">';
				print '<select size="1" onchange="document.location=\'cart.php?redeempoints=\'+(this.selectedIndex==1?\'no\':\'yes\')"><option value="">' . $GLOBALS['xxReLPts'] . '</option><option value=""' . (@$_SESSION['noredeempoints']==TRUE ? ' selected="selected"' : '') . '>' . $GLOBALS['xxSaLPts'] . ' (' . $loyaltypointsavailable . ')</option></select>';
				print '</div><div class="cartloyaltypoints">';
				if(@$_SESSION['noredeempoints']==TRUE) print '-'; else print FormatEuroCurrency($loyaltypointdiscount);
				print '</div></div>';
			}
			if(@$estimateshipping!=TRUE || @$nohandlinginestimator==TRUE){ $handling=0; $handlingchargepercent=0; }
			print '<div class="cartshippingdetails">';
			if(@$estimateshipping==TRUE){
				if(strpos($errormsg,$GLOBALS['xxInvZip'])!==FALSE || strpos($errormsg,$GLOBALS['xxPlsZip'])!==FALSE) $invalidzip=TRUE; else $invalidzip=FALSE;
				if(strpos($errormsg,$GLOBALS['xxPlsSta'])!==FALSE) $invalidstate=TRUE; else $invalidstate=FALSE;
				print (@$GLOBALS['xxEstimatorTitle']!='' ? '<div class="estimatortitle">' . $GLOBALS['xxEstimatorTitle'] . '</div>' : '') . '<div class="estimatorerrors" id="estimatorerrors">' . $errormsg . '</div>';
				print '<div class="shipestimatemarkup_cntnr"><div class="cartestimatortext">';
				writeestimatormenu();
				print "</div></div>\r\n";
				if($wantstateselector){
					print '<div class="cartstateselector_cntnr"><div class="cartstateselectortext' . ($invalidstate ? ' ectwarning' : '') . '">';
					print labeltxt($GLOBALS['xxAllSta'],"state");
					print '</div><div class="cartstateselector' . ($invalidstate ? ' ectwarning' : '') . '">';
					print '<select name="state" id="state" class="cartstateselector' . ($invalidstate ? ' ectwarning' : '') . '" size="1" onchange="updateestimator()">'; show_states($shipstate); print '</select>';
					print "</div></div>\r\n";
				}
				if($wantcountryselector){
					print '<div class="cartcountryselector_cntnr"><div class="cartcountryselectortext">';
					print labeltxt($GLOBALS['xxCountry'],'country');
					print '</div><div class="cartcountryselector">';
					print '<select name="country" id="country" size="1"' . ($mobilebrowser?' style="font-size:10px"':'') . ' onchange="' . ($wantstateselector ? "dynamiccountries(this,'');" : '') . 'updateestimator()">';
					$sSQL='SELECT countryID,countryName,countryCode,' . getlangid('countryName',8) . ' AS cnameshow FROM countries WHERE countryEnabled=1 ORDER BY countryOrder DESC,' . getlangid('countryName',8);
					$result=ect_query($sSQL) or ect_error();
					while($rs=ect_fetch_assoc($result)){
						print '<option value="' . $rs['countryID'] . '"';
						if($shipcountry==$rs['countryName']) print ' selected="selected"';
						$cnameshow=$rs['cnameshow'];
						if($cnameshow=='United States of America' && $mobilebrowser) $cnameshow='USA';
						print '>' . $cnameshow . "</option>\r\n";
					}
					ect_free_result($result);
					print '</select>';
					print "</div></div>\r\n";
				}
				if($wantzipselector){
					print '<div class="cartzipselector_cntnr"><div class="cartzipselectortext' . ($invalidzip ? ' ectwarning' : '') . '">';
					print labeltxt($GLOBALS['xxZip'],'zip');
					print '</div><div class="cartzipselector' . ($invalidzip ? ' ectwarning' : '') . '">';
					print '<input type="text" name="zip" id="zip" class="cartzipselector' . ($invalidzip ? ' ectwarning' : '') . '" size="8" value="' . htmlspecials($destZip) . '" onchange="updateestimator()" autocapitalize="characters" />';
					print "</div></div>\r\n";
				}
			}
			if(@$GLOBALS['xxEstimatorEnd']!='' && $checkoutmode!='savedcart') print '<div class="cartestimatorend">' . $GLOBALS['xxEstimatorEnd'] . '</div>';
			print '</div>';
			print '<div class="carttotals">'; // {
			print '<div class="cartsubtotal_cntnr"><div class="cartsubtotaltext">' . $GLOBALS['xxSubTot'] . '</div><div class="cartsubtotal">' . FormatEuroCurrency($totalgoods) . "</div></div>\r\n";
			if($estimateshipping){
				print '<div class="shippingtotal_cntnr" id="shippingtotal_cntnr"' . ($errormsg!='' ? ' style="display:none"' : '') . '><div class="shippingtotaltext">' . ($handling==0 || $errormsg!='' ? $GLOBALS['xxShpEst'] : $GLOBALS['xxShHaEs']) . '</div><div class="shippingtotal" id="estimatorspan">';
				if($errormsg!='')
					print $errormsg;
				elseif($freeshipamnt==($shipping+$handling))
					print $GLOBALS['xxFree'];
				else
					print FormatEuroCurrency(($shipping+$handling)-$freeshipamnt);
				print '</div></div>';
			}
			if($totaldiscounts>0 && $showtaxinclusive!=3)
				print '<div class="cartdiscounts_cntnr"><div class="cartdiscountstext ectdscntt">' . $GLOBALS['xxDsApp'] . '</div><div class="cartdiscounts ectdscnt" id="discountspan">' . FormatEuroCurrency($totaldiscounts) . "</div></div>\r\n";
			if(@$showtaxinclusive!=0){
				print '<div class="cartcountrytax_cntnr"><div class="cartcountrytaxtext">';
				print $GLOBALS['xxCntTax'];
				print '</div><div class="cartcountrytax">';
				print '<span id="countrytaxspan">' . FormatEuroCurrency($countryTax) . '</span>';
				print "</div></div>\r\n";
				if($totaldiscounts>0 && $showtaxinclusive==3)
					print '<div class="cartdiscounts_cntnr"><div class="cartdiscountstext ectdscntt">' . $GLOBALS['xxDsApp'] . '</div><div class="cartdiscounts ectdscnt" id="discountspan">' . FormatEuroCurrency($totaldiscounts) . "</div></div>\r\n";
			}else
				$countryTax=0;
			if(!@$GLOBALS['nopriceanywhere']){
				print '<div class="cartgrandtotal_cntnr"><div class="cartgrandtotaltext">';
				if(getget('pla')==''){
					print ($checkoutmode=='savedcart' ? $GLOBALS['xxItmTot'] : $GLOBALS['xxGndTot']);
					print '</div><div class="cartgrandtotal" id="grandtotalspan">';
					print FormatEuroCurrency(($totalgoods+$shipping+$handling+$countryTax)-($totaldiscounts+$freeshipamnt+$loyaltypointdiscount));
				}
				print '</div></div>';
			}
			if($giftcertsamount!=0){
				print '<div class="cartgiftcert_cntnr"><div class="cartgiftcerttext ectdscntt">' . $GLOBALS['xxAppGC'] . '</div><div class="cartgiftcert ectdscnt">' . FormatEuroCurrency(min($giftcertsamount,($totalgoods+$shipping+$handling+$countryTax)-($totaldiscounts+$freeshipamnt+$loyaltypointdiscount))) . '</div></div>';
			}
			if($checkoutmode!='savedcart'){
				$sSQL='SELECT cpnID FROM coupons WHERE cpnIsCoupon<>0 AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.')) LIMIT 0,1';
				$result=ect_query($sSQL) or ect_error();
				$hasacoupon=ect_num_rows($result)>0;
				ect_free_result($result);
				if($hasacoupon){
					print '<div class="cartcoupon_cntnr">';
					print '<div class="cartcoupontext">' . labeltxt($GLOBALS['xxGifNum'],'cpncode') . '</div>';
					if(trim(@$_SESSION['giftcerts'])!=''){
						$cpnarr=explode(' ', trim(@$_SESSION['giftcerts']));
						foreach($cpnarr as $key => $value)
							print '<div class="cartcouponapplied">' . imageorlink('',$GLOBALS['xxRemove'].' : '.$value,'applycoupon removecoupon1',"removecert('".$value."')",TRUE) . '</div>';
					}
					if(trim(@$_SESSION['cpncode'])!=''){
						$cpnarr=explode(' ', trim(@$_SESSION['cpncode']));
						foreach($cpnarr as $key => $value)
							print '<div class="cartcouponapplied">' . imageorlink('',$GLOBALS['xxRemove'].' : '.$value,'applycoupon removecoupon1',"removecert('".$value."')",TRUE) . '</div>';
					}
					print '<div class="cartcoupon"><input type="text" name="cpncode" id="cpncode" size="' . ($mobilebrowser?13:18) . '" style="font-size:11px" /> ' . imageorbutton(@$GLOBALS['imgapplycoupon'],$GLOBALS['xxApply'],'applycoupon applycoupon1','applycert()',TRUE) . '</div>';
					print '</div>';
				}
			}
			print '</div></div>'; // carttotals cartshippingandtotals } }
			if($checkoutmode!='savedcart'){
				print '<div class="cartcontinueupdate">';
				print '<div class="cartcontinue">';
				if($thefrompage!='' && (@$actionaftercart==2 || @$actionaftercart==3)) $thehref=htmlspecials($thefrompage); else $thehref=$GLOBALS['xxHomeURL'];
				print imageorlink(@$imgcontinueshopping,$GLOBALS['xxCntShp'],'',$thehref,FALSE);
				print '</div><div class="cartupdate">' . imageorlink(@$GLOBALS['imgupdatetotals'],$GLOBALS['xxUpdTot'],'','return doupdate()',TRUE) . '</div>';
				print '</div>';
			}
			if($checkoutmode!='savedcart'){
				if(@$_SESSION['tofreeshipamount']!='')
					print '<div class="tofreeshipping">' . replace($GLOBALS['xxToFSAm'],'%s',FormatEuroCurrency($_SESSION['tofreeshipamount'])) . '</div>';
				elseif(@$_SESSION['tofreeshipquant']!='')
					print '<div class="tofreeshipping">' . replace($GLOBALS['xxToFSQu'],'%s',$_SESSION['tofreeshipquant']) . '</div>';
			}
		}
		if($amazonpaycheckout || @$_SESSION['AmazonLogin']!=''){
			if(getpayprovdetails(21,$data1,$data2,$data3,$demomode,$ppmethod)){
				print '<script type="text/javascript">window.onAmazonLoginReady=function(){amazon.Login.setClientId("' . $data1 . '");};</script>';
				print '<script type="text/javascript" src="' . getamazonjsurl($demomode) . '"></script>';
			}
		}
		if($amazonpaycheckout){
			if(time()>=$_SESSION['AmazonLoginTimeout']){
				$_SESSION['AmazonLogin']=$_SESSION['AmazonLoginTimeout']='';
			}else{
				if(getpayprovdetails(21,$data1,$data2,$data3,$demomode,$ppmethod)){
					$data2arr=explode('&',$data2);
					$data2=$data2arr[0];
					$sellerid=@$data2arr[1];

					print '<div class="amazoncontent">';
						print '<div class="amazonaddressandwallet">';
							print '<div id="addressBookWidgetDiv"></div>';
							print '<div id="walletWidgetDiv"></div>';
						print '</div>';
					print '<div class="amazonbuttons"><div class="paynowamazon">' . imageorbutton(@$imgamazonpaynow,'Click to Check Totals'.($shipType!=0?' / Select Shipping':''),'amazonpaynow','amazonpaynow()',TRUE) . '</div><div class="amazonlogout2">' . imageorlink(@$imgamazonlogout,'Logout of your Amazon account','amazonlogout','return amazonlogout()',TRUE) . '</div></div>';
					print '</div>';
?>
<script type="text/javascript">
var amznorderreferenceid='';
var addressselected=false,paymentselected=false;
new OffAmazonPayments.Widgets.AddressBook({
  sellerId: '<?php print $sellerid?>',
  onOrderReferenceCreate: function(orderReference) {
    amznorderreferenceid=orderReference.getAmazonOrderReferenceId();
  },
  onAddressSelect: function(orderReference) {
	addressselected=true;
	paymentselected=false;
  },
  design: {
    designMode: 'responsive'
  },
  onError: function(error) {
	alert(error.getErrorMessage());
  }
}).bind("addressBookWidgetDiv");

new OffAmazonPayments.Widgets.Wallet({
  sellerId: '<?php print $sellerid?>',
  onPaymentSelect: function(orderReference) {
	paymentselected=true;
  },
  design: {
    designMode: 'responsive'
  },
  onError: function(error) {
  }
}).bind("walletWidgetDiv");
function amazonpaynow(){
	if(amznorderreferenceid!='')
		document.location='cart.php?amzrefid='+amznorderreferenceid;
	else
		alert("Please select an address and payment method.");
}
</script>
<?php			}
			}
		}else{ // cartcheckoutbuttons
			print '<div class="cartcheckoutbuttons">';
			if(trim(@$_SESSION['clientID'])!=''){
				srand((double)microtime()*1000000);
				$sequence=ip2long($REMOTE_ADDR);
				if($sequence===FALSE) $sequence=0;
				ect_query("DELETE FROM tmplogin WHERE tmplogindate < '" . date("Y-m-d H:i:s", time()-(3*60*60*24)) . "' OR tmploginid='" . escape_string($thesessionid) . "'") or ect_error();
				ect_query("INSERT INTO tmplogin (tmploginid, tmploginname, tmploginchk, tmplogindate) VALUES ('" . escape_string($thesessionid) . "','" . escape_string($_SESSION['clientID']) . "','" . $sequence . "','" . date('Y-m-d H:i:s', time()) . "')") or ect_error();
				print whv('checktmplogin',$sequence);
				if(($_SESSION['clientActions'] & 8)==8 || ($_SESSION['clientActions'] & 16)==16){
					if(@$minwholesaleamount!='') $minpurchaseamount=$minwholesaleamount;
					if(@$minwholesalemessage!='') $minpurchasemessage=$minwholesalemessage;
				}
			}
			$estimate=($totalgoods+$shipping+$handling+$stateTax+$countryTax)-($totaldiscounts+$freeshipamnt+$loyaltypointdiscount);
			if($checkoutmode=='savedcart'){
				// Do nothing
			}elseif($totalgoods < @$minpurchaseamount){
				print '<div class="checkoutopts cominpurchase">' . @$minpurchasemessage . '</div>';
			}elseif(@$forceclientlogin==TRUE && @$_SESSION['clientID']==''){
				print '<div class="checkoutopts coforcelogin">' . $GLOBALS['xxBfChk'] . ' <a class="ectlink" href="cart.php?mode=login">' . $GLOBALS['xxLogin'] . '</a>' . (@$allowclientregistration==TRUE ? ' ' . $GLOBALS['xxOr'] . ' <a class="ectlink" href="cart.php?mode=newaccount">' . $GLOBALS['xxCrAc'] . '</a>' : '') . '.</div>';
			}elseif($stockwarning){
				// Do nothing
			}else{
				$sSQL='SELECT payProvID,payProvData1,payProvData2,payProvDemo FROM payprovider WHERE payProvEnabled=1 AND payProvLevel<=' . $minloglevel . (@$paypalhostedsolution?' AND payProvID<>18':'') . ($estimate<=0?' AND payProvID<>19':'') . ' ORDER BY payProvOrder';
				$result=ect_query($sSQL) or ect_error();
				$regularcheckoutshown=FALSE;
				while($rs=ect_fetch_assoc($result)){
					if($rs['payProvID']==21){
						if(getget('access_token')!=''&&getget('token_type')=='bearer'&&getget('expires_in')!=''&&getget('scope')!=''){
							if(callcurlfunction('https://api.' . ($rs['payProvDemo']?'sandbox.':'') . 'amazon.com/auth/o2/tokeninfo?access_token=' . urlencode(getget('access_token')),'',$res,'',$errormsg, FALSE)){
								if($res!=''){
									$resarray=explode(',',trim($res,'{}'));
									foreach($resarray as $key){
										$keypair=explode(':',$key);
										if($keypair[0]=='"aud"'&&$keypair[1]=='"'.$rs['payProvData1'].'"'){
											$_SESSION['AmazonLogin']=trim($res,'{}');
										//	$xmlfnheaders=array('Authorization: bearer ' . getget('access_token'));
										//	if(callcurlfunction('https://api.' . ($rs['payProvDemo']?'sandbox.':'') . 'amazon.com/user/profile','',$res2,'',$errormsg, FALSE)){
										//		$_SESSION['AmznJSON']=$res2;
										//		$resarray2=explode(',',trim($res2,'{}'));
										//		foreach($resarray2 as $key){
										//			$keypair=explode(':',$key);
										//			if($keypair[0]=='"postal_code"'){ $_SESSION['xsshipping']=''; $_SESSION['zip']=trim($keypair[1],'"'); }
										//		}
												if(ob_get_length()!==FALSE)
													header('Location: ' . $storeurlssl . 'cart.php?amazonpay=go');
												else
													print '<meta http-equiv="Refresh" content="0; URL=' . $storeurlssl . 'cart.php?amazonpay=go">';
										//	}
										}elseif($keypair[0]=='"exp"'){
											$_SESSION['AmazonLoginTimeout']=time()+(int)$keypair[1];
										}	
									}
								}
							}
						}
					}
					if(@$_SESSION['AmazonLogin']!=''){
						$shownamazonviewother=TRUE;
					}elseif($rs['payProvID']==19){
						$wantbillmelater=FALSE;
						$data2arr=explode('&',$rs['payProvData2']);
						$wantbillmelater=(trim(urldecode(@$data2arr[3]))=='1');
						if($wantbillmelater){
							print '<div class="checkoutopts coopt' . $rs['payProvID'] . '" style="display:inline-block;padding:10px;vertical-align:top"><input type="image" src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_bml_SM.png" onclick="document.forms.checkoutform.cart.value=\'\';document.forms.checkoutform.mode.value=\'billmelater\';" alt="Bill Me Later" title="' . $GLOBALS['xxPPPBlu'] . "\" /></div>\r\n";
						}
						print '<div class="checkoutopts coopt' . $rs['payProvID'] . '" style="display:inline-block;padding:10px;vertical-align:top"><input type="image" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckoutsm.gif" onclick="document.forms.checkoutform.cart.value=\'\';document.forms.checkoutform.mode.value=\'paypalexpress1\';" alt="PayPal Express" title="' . $GLOBALS['xxPPPBlu'] . "\" /></div>\r\n";
					}elseif(! $regularcheckoutshown){
						$regularcheckoutshown=TRUE;
						print '<div class="checkoutopts coopt' . $rs['payProvID'] . '" style="display:inline-block;padding:10px;vertical-align:top">' . imageorsubmit($imgcheckoutbutton,$GLOBALS['xxCOTxt'] . '" onclick="document.forms.checkoutform.action=\''.$cartpath.'\';document.forms.checkoutform.cart.value=\'\';document.forms.checkoutform.mode.value=\'checkout\';" title="'.$GLOBALS['xxPrsChk'],'checkoutbutton checkoutbutton1') . "</div>\r\n";
					}
				}
				ect_free_result($result);
				if(@$_SESSION['AmazonLogin']!='') print imageorlink(@$imgamazonlogout,@$shownamazonviewother?'Logout of Amazon (and view other payment methods)':'Logout of your Amazon account','amazonlogout','return amazonlogout()',TRUE);
			}
			print '</div>'; // } cartcheckoutbuttons
		} ?>
<script type="text/javascript">/* <![CDATA[ */
<?php
	if(@$_SESSION['AmazonLogin']!=''){ ?>
function amazonlogout(){
	amazon.Login.logout();
	document.location='cart.php?amazon=logout';
	return false;
}
<?php
	}
	if($wantstateselector){
		createdynamicstates($stateSQL);
		print "dynamiccountries(document.getElementById('country'),'');setinitialstate('');\r\n";
	}
	if($adminAltRates==2 && ((($shipping+$handling)-$freeshipamnt)>0 || $errormsg!='')){
		print "var bestcarrier=".$shipType.";var bestestimate=" . ((($shipping+$handling)-$freeshipamnt) + ($errormsg!=''?1000000:0)) . ";\r\n";
		print 'var vstotalgoods=' . $totalgoods . ";\r\ngetalternatecarriers();\r\n";
	}
?>
function changechecker(){
<?php if($totalgoods < @$minpurchaseamount) print 'if((document.forms.checkoutform.mode.value!="dologin")&&(document.forms.checkoutform.mode.value!="donewaccount"))return false;' ?>
dowarning=false;
<?php print $changechecker?>
if(document.getElementById("cpncode"))document.getElementById("cpncode").value="";
if(dowarning){
	if(confirm("<?php print jscheck($GLOBALS['xxWrnChQ'])?>")) return doupdate(); else return(true);
}
return true;
}
/* ]]> */</script>
<input type="hidden" name="estimate" value="<?php print number_format($estimate,2,'.','') ?>" />
<?php
	}else{
		$cartEmpty=TRUE;
		print '<div class="emptycart">';
		if($checkoutmode=='savedcart')
			print '<div class="mywishlistemptycart">' . (@$listname==''?$GLOBALS['xxMyWisL']:htmlspecials($listname)) . '</div><div class="emptycartemptylist">' . $GLOBALS['xxLisEmp'] . '</div><div class="emptycartviewmain">' . $GLOBALS['xxViewMC'] . ' <a class="ectlink" href="cart.php"><strong>'.$GLOBALS['xxClkHere'].'</strong></a>.</div>';
		else{
			if(getget('mode')!='login' && getget('mode')!="newaccount") print '<div class="sorrycartempty">' . $GLOBALS['xxSryEmp'] . '</div>';
			if(getget('mode')!="logout") print '<div class="cartemptyclickhere">' . $GLOBALS['xxGetSta'] . ' <a class="ectlink" href="'.$GLOBALS['xxHomeURL'].'"><strong>'.$GLOBALS['xxClkHere'].'</strong></a>.</div>';
		}
?>
<script type="text/javascript">/* <![CDATA[ */
var ectvalue=Math.floor(Math.random()*10000 + 1);
document.cookie="ECTTESTCART=" + ectvalue + "; path=/";
if((document.cookie+";").indexOf("ECTTESTCART=" + ectvalue + ";") < 0) document.write("<?php print str_replace('"', '\"', $GLOBALS['xxNoCk'] . " " . $GLOBALS['xxSecWar'])?>");
/* ]]> */</script>
<noscript><?php print $GLOBALS['xxNoJS'] . " " . $GLOBALS['xxSecWar']?></noscript>
<?php	print '<div class="emptycartcontinue">';
		if($thefrompage!='' && (@$actionaftercart==2 || @$actionaftercart==3)) $thehref=htmlspecials($thefrompage); else $thehref=$GLOBALS['xxHomeURL'];
		print imageorlink(@$imgcontinueshopping,$GLOBALS['xxCntShp'],'',$thehref,FALSE);
		print '</div></div>';
	}
	print '</div>';
	print '</form>';
}
?>