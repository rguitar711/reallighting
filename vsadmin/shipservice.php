<?php
@include 'adminsession.php';
session_cache_limiter('none');
session_start();
ob_start();
header('Cache-Control: no-cache');
header('Pragma: no-cache');
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
$isadmincalc=(@$_GET['action']=='admincalc') && (@$_SESSION['loggedon']!='');
$isaddtocart=(@$_GET['action']=='addtocart');
if(@$_GET['action']=='admincalc'){ ?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Admin Shipping Calculator</title>
<link rel="stylesheet" type="text/css" href="adminstyle.css"/>
<style type="text/css">
td {font-size:12px;}
div.shiprateline{
width:100%;
float:left;
padding:1px;
}
div.shiptableline{
width:100%;
float:left;
}
div.shiprateradio{
width:10%;
float:left;
}
div.shipratemethod{
width:65%;
float:left;
}
div.shiptablelogo{
height: 10em;
position: relative;
width:80px;
height:60px;
float:left;
}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=<?php print $adminencoding?>"/>
<script type="text/javascript">/* <![CDATA[ */
function updateshiprate(objitem,usrindex){
	window.opener.document.getElementById('ordShipping').value=document.getElementById('shipcost'+usrindex).value;
	window.opener.document.getElementById('shipmethod').value=document.getElementById('shipmethod'+usrindex).value;
	window.opener.dorecalc();
	window.close();
}
function changeshipcarrier(){
	var tobj=document.getElementById('shipcarrselect');
	var tshiptype=tobj[tobj.selectedIndex].value;
	var tquery=window.location.search.substring(1).split('&shiptype=')[0];
<?php	$querystr='';
	foreach(@$_GET as $key=>$val){
		if($key!='shiptype' && $key!='destzip' && $key!='sc' && $key!='scc' && $key!='sta' && $key!='cl') $querystr.= $key . '=' . $val . '&';
	} ?>
	var tcntry=document.getElementById('country');
	var comloc=document.getElementById('commercialloc')?document.getElementById('commercialloc')[document.getElementById('commercialloc').selectedIndex].value:'N';
	window.location.href='shipservice.php?<?php print $querystr?>shiptype='+tshiptype+'&destzip='+document.getElementById('destzip').value+'&scc='+tcntry[tcntry.selectedIndex].value+'&sta='+document.getElementById('sta').value+'&cl='+comloc;
}
/* ]]> */</script>
</head>
<body><?php
}
include 'inc/incfunctions.php';
$cartisincluded=TRUE;
if($isaddtocart){
	$checkoutmode='add';
	$theid=trim(getpost('id'));
}
include './inc/uspsshipping.php';
include './inc/inccart.php';
if($isaddtocart) exit;
$adminIntShipping=0; // So shipping doesn't get changed
$handlingeligableitem=FALSE;
$standalonetestmode=TRUE;
$debginfo='';
$thesessionid=getget('sessionid');
$destZip=getget('destzip');
$shipCountryCode=getget('scc');
$shipcountry=getget('sc');
$shipstate=$shipStateAbbrev=getget('sta');
if(getget('shiptype')!='') $shipType=(int)getget('shiptype');
if($isadmincalc){
	$shippingoptionsasradios=TRUE;
	$sSQL="SELECT stateAbbrev FROM states WHERE stateName='".escape_string($shipStateAbbrev)."' OR stateAbbrev='".escape_string($shipStateAbbrev)."'";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $shipStateAbbrev=$rs['stateAbbrev'];
	ect_free_result($result);
	$sSQL="SELECT countryName,countryCode FROM countries WHERE " . ($shipCountryCode!=''?"countryCode='".$shipCountryCode."' OR ":'') . "countryName='".escape_string($shipcountry)."' OR countryName2='".escape_string($shipcountry)."' OR countryName3='".escape_string($shipcountry)."'";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		$shipCountryCode=$rs['countryCode'];
		$shipcountry=$rs['countryName'];
	}
	ect_free_result($result);
	$commercialloc_=(getget('cl')=='Y');
}else
	$commercialloc_=@$_SESSION['commercialloc_'];
$numshipmethods=$freeshipamnt=0;
$rgcpncode=trim(@$_SESSION['cpncode']);
$wantinsurance_=@$_SESSION['wantinsurance_'];
$saturdaydelivery_=@$_SESSION['saturdaydelivery_'];
$signaturerelease_=@$_SESSION['signaturerelease_'];
$willpickup_=@$_SESSION['willpickup_'];
$sSQL="SELECT countryID,countryTax,countryCode,countryFreeShip,countryOrder FROM countries WHERE countryCode='" . escape_string($shipCountryCode) . "'";
$result=ect_query($sSQL) or ect_error();
if($rs=ect_fetch_assoc($result)){
	$countryTaxRate=$rs['countryTax'];
	$shipCountryID=$rs['countryID'];
	$shipCountryCode=$rs['countryCode'];
	$freeshipavailtodestination=($rs['countryFreeShip']==1);
	$shiphomecountry=($rs['countryID']==$origCountryID) || (($rs['countryID']==1 || $rs['countryID']==2) && @$usandcasplitzones);
}
ect_free_result($result);
if($shiphomecountry){
	$sSQL="SELECT stateFreeShip FROM states WHERE stateAbbrev='" . escape_string($shipStateAbbrev) . "'";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $freeshipavailtodestination=($freeshipavailtodestination && ($rs['stateFreeShip']==1));
	ect_free_result($result);
}
initshippingmethods();
$numshiprateingroup=$totalgoods=0;
$cartrows=$alldata='';
$success=TRUE;
$numshiprate=(int)getget('numshiprate');
if($isadmincalc){
	print '<table class="cobtbl" width="100%" border="0" cellspacing="2" cellpadding="2">';
	print '<tr><td class="cobhl" align="right" width="50%">State:</td><td class="cobll"><input type="text" size="10" id="sta" value="' . $shipStateAbbrev . '" /></td></tr>';
	print '<tr><td class="cobhl" align="right">Zip:</td><td class="cobll"><input type="text" size="6" id="destzip" value="' . $destZip . '" /></td></tr>';
	print '<tr><td class="cobhl" align="right">Country:</td><td class="cobll"><select name="country" id="country" size="1">';
	$sSQL='SELECT countryID,countryName,countryCode,'.getlangid('countryName',8).' AS cnameshow FROM countries WHERE countryEnabled=1 ORDER BY countryOrder DESC,'.getlangid('countryName',8);
	$result=ect_query($sSQL) or ect_error();
	$gotcountry=FALSE;
	while($rs=ect_fetch_assoc($result)){
		print '<option value="'.$rs['countryCode'].'"';
		if($shipCountryCode==$rs['countryCode']&&!$gotcountry) print ' selected="selected"';
		if($shipcountry==$rs['countryName']) $gotcountry=TRUE;
		$cnameshow=$rs['cnameshow'];
		if($cnameshow=='United States of America') $cnameshow='USA';
		print '>' . $cnameshow . "</option>\r\n";
	}
	ect_free_result($result);
	print '</select></td></tr>';
	print '<tr><td class="cobll" align="right"><select size="1" id="shipcarrselect">';
	$sSQL='SELECT altrateid,altratename FROM alternaterates WHERE ' . ($adminAltRates>0?'usealtmethod<>0 OR usealtmethodintl<>0 OR ':'') . 'altrateid='.$adminShipping.' OR altrateid='.$adminIntShipping.' ORDER BY altrateorder,altrateid';
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		print '<option value="' . $rs['altrateid'] . '"';
		if($rs['altrateid']==$shipType) print ' selected="selected"';
		print '>' . $rs['altratename'] . '</option>';
	}
	ect_free_result($result);
	print '</select></td>';
	print '<td class="cobll">';
	if($shipType==3 || $shipType==4 || $shipType>=6)
		print '<select id="commercialloc" size="1"><option value="N">RES</option><option value="Y"'.(getget('cl')=='Y'?' selected="selected"':'').'>COM</option></select>&nbsp;';
	print '<input type="button" value="Calculate" onclick="changeshipcarrier()" /></td></tr></table>';
	$shipmet='USPS';
	if($shipType==1) $shipmet='Flat Rate';
	if($shipType==2) $shipmet='Weight Based';
	if($shipType==4) $shipmet='UPS';
	if($shipType==5) $shipmet='Price Based';
	if($shipType==6) $shipmet='Canada Post';
	if($shipType==7) $shipmet='FedEx';
	if($shipType==8) $shipmet='FedEx SmartPost';
	if($shipType==9) $shipmet='DHL';
	print '&nbsp;<br /><table width="100%" cellspacing="2" cellpadding="2" border="0" class="cobtbl"><tr><td align="center" class="cobll">';
	print '<table cellspacing="2" cellpadding="2" border="0"><tr><td align="right">' . replace(replace(getshiplogo($shipType),'images','../images'),'&nbsp;','') . '</td><td style="font-weight:bold">' . $shipmet . ' ' . $GLOBALS['xxShippg'] . '</td></tr></table>';
	$productids='';
	$itemsincart=0;
	foreach(@$_GET as $key=>$val){
		if(substr($key,0,6)=='prodid'){
			$prodindex=(int)substr($key, 6);
			if(is_numeric($prodindex)&&is_numeric(@$_GET['quant' . $prodindex])){
				$sSQL='SELECT 0 AS cartID,pID AS cartProdID,pName AS cartProdName,pPrice AS cartProdPrice,' . @$_GET['quant' . $prodindex] . " AS cartQuantity,pWeight,pShipping,pShipping2,pExemptions,pSection,0 AS topSection,pDims,pTax,1 AS cartCompleted,'' AS pDescription FROM products WHERE pID='" . escape_string($val) . "'";
				$result=ect_query($sSQL) or ect_error();
				while($rs=ect_fetch_assoc($result)){
					$cartrows[$itemsincart]=$rs;
					$optpricediff=0;
					$optweightdiff=0;
					foreach(@$_GET as $optkey=>$optval){
						if(substr($optkey,0,strlen('optn'.$prodindex.'_'))==('optn'.$prodindex.'_')&&is_numeric($optval)){
							$sSQL='SELECT optID,optPriceDiff,optWeightDiff,optType,optFlags,optRegExp FROM options INNER JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optID='.$optval;
							$result2=ect_query($sSQL) or ect_error();
							if($rs2=ect_fetch_assoc($result2)){
								if(abs($rs2['optType'])!=3){
									if(($rs2['optFlags']&1)==0) $optpricediff+= (trim($rs2['optRegExp'])!=''?0:$rs2['optPriceDiff']); else $optpricediff+=round(($rs2['optPriceDiff'] * $rs['pPrice'])/100.0, 2);
									if(($rs2['optFlags']&2)==0) $optweightdiff+= $rs2['optWeightDiff']; else $optweightdiff+=multShipWeight($rs['pWeight'],$rs2['optWeightDiff']);
								}
							}
							ect_free_result($result2);
						}
					}
					$cartrows[$itemsincart]['cartProdPrice']+=$optpricediff;
					$cartrows[$itemsincart]['pWeight']+=$optweightdiff;
					$itemsincart++;
				}
				ect_free_result($result);
			}
		}
	}
}else{
	$sSQL='SELECT cartID,cartProdID,cartProdName,cartProdPrice,cartQuantity,pWeight,pShipping,pShipping2,pExemptions,pSection,pDims,pTax,cartCompleted,'.getlangid('pDescription',2).' FROM cart LEFT JOIN products ON cart.cartProdID=products.pID LEFT OUTER JOIN sections ON products.pSection=sections.sectionID WHERE cartCompleted=0 AND ' . getsessionsql();
	$result=ect_query($sSQL) or ect_error();
	$itemsincart=ect_num_rows($result);
	$index=0;
	while($alldata=ect_fetch_assoc($result)){
		$cartrows[$index]=$alldata;
		$index++;
	}
	ect_free_result($result);
}
if($itemsincart>0){
	for($indexserv=0; $indexserv<$itemsincart; $indexserv++){
		$alldata=$cartrows[$indexserv];
		if(is_numeric($alldata['cartID'])){
			if(is_null($alldata['pWeight'])) $alldata['pWeight']=0;
			if(($alldata['cartProdID']==$giftcertificateid || $alldata['cartProdID']==$donationid) && is_null($alldata['pExemptions'])) $alldata['pExemptions']=15;
			if($alldata['cartProdID']==$giftwrappingid && is_null($alldata['pExemptions'])) $alldata['pExemptions']=12;
			$sSQL='SELECT SUM(coPriceDiff) AS coPrDff FROM cartoptions WHERE coCartID='. $alldata['cartID'];
			$optresult=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($optresult)){
				$alldata['cartProdPrice']+=(double)$rs['coPrDff'];
			}
			ect_free_result($optresult);
			$sSQL='SELECT SUM(coWeightDiff) AS coWghtDff FROM cartoptions WHERE coCartID='. $alldata['cartID'];
			$optresult=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($optresult)){
				$alldata['pWeight']+=(double)$rs['coWghtDff'];
			}
			ect_free_result($optresult);
			$runTot=$alldata['cartProdPrice'] * (int)($alldata['cartQuantity']);
			$totalquantity+=(int)($alldata['cartQuantity']);
			$totalgoods+=$runTot;
			$thistopcat=0;
			if(trim(@$_SESSION['clientID'])!='') $alldata['pExemptions']=((int)$alldata['pExemptions'] | ((int)$_SESSION['clientActions'] & 7));
			if(($shipType==2 || $shipType==3 || $shipType==4 || $shipType==6 || $shipType==7) && (double)$alldata['pWeight']<=0.0)
				$alldata['pExemptions']=($alldata['pExemptions'] | 4);
			if(($alldata['pExemptions'] & 1)==1) $statetaxfree+=$runTot;
			if(($alldata['pExemptions'] & 8)!=8){ $handlingeligableitem=TRUE; $handlingeligablegoods+=$runTot; }
			if(@$perproducttaxrate==TRUE){
				if(is_null($alldata['pTax'])) $alldata['pTax']=$countryTaxRate;
				if(($alldata['pExemptions'] & 2)!=2) $countryTax+=(($alldata['pTax'] * $runTot) / 100.0);
			}else{
				if(($alldata['pExemptions'] & 2)==2) $countrytaxfree+=$runTot;
			}
			if(($alldata['pExemptions'] & 4)==4) $shipfreegoods+=$runTot;
			addproducttoshipping($alldata, $indexserv);
		}
	}
}else{
	$errormsg="Error, couldn't find cart.";
	$success=FALSE;
}
calculatediscounts($totalgoods, FALSE, $rgcpncode);
if($totaldiscounts > $totalgoods) $totaldiscounts=$totalgoods;
$shipsellogo=getshiplogo($shipType);
if($success && calculateshipping()){
	if(getget('ratetype')=='estimator'){
		if(@$nohandlinginestimator){ $handling=0; $handlingchargepercent=0; }
		if((is_numeric(@$shipinsuranceamt) || (@$useuspsinsurancerates==TRUE && $shipType==3)) && abs(@$addshippinginsurance)==1) $shipping+=(@$useuspsinsurancerates==TRUE && $shipType==3) ? getuspsinsurancerate((double)$totalgoods) : ($addshippinginsurance==1 ? (((double)$totalgoods*(double)$shipinsuranceamt)/100.0) : $shipinsuranceamt);
		if(@$taxShipping==1 && @$GLOBALS['showtaxinclusive']!=0) $shipping+=((double)$shipping*(double)$countryTaxRate)/100.0;
		calculateshippingdiscounts(FALSE);
		if($handlingeligableitem==FALSE)
			$handling=0;
		else{
			if(@$handlingchargepercent!=0){
				$temphandling=((($totalgoods + $shipping + $handling) - ($totaldiscounts + $freeshipamnt)) * $handlingchargepercent / 100.0);
				if($handlingeligablegoods < $totalgoods && $totalgoods > 0) $temphandling=$temphandling * ($handlingeligablegoods / $totalgoods);
				$handling+=$temphandling;
			}
			if(@$taxHandling==1 && @$GLOBALS['showtaxinclusive']!=0) $handling+=((double)$handling*(double)$countryTaxRate)/100.0;
		}
		if(@$perproducttaxrate!=TRUE) $countryTax=round(((($totalgoods-$countrytaxfree)+(@$taxShipping==2 ? $shipping-$freeshipamnt : 0)+(@$taxHandling==2 ? $handling : 0))-$totaldiscounts)*$countryTaxRate/100.0, 2);
		$countryTax=round($countryTax,2);
		if(is_numeric(getget('best'))) $currbest=(double)getget('best'); else $currbest=100000000;
		if((($shipping+$handling)-$freeshipamnt) < $currbest){
			$_SESSION['xsshipping']=(($shipping+$handling)-$freeshipamnt);
			$_SESSION['xscountrytax']=$countryTax;
			$_SESSION['altrates']=$shipType;
		}
		print '&nbsp;';
		print 'SHIPSELPARAM=' . (($shipping+$handling)-$freeshipamnt);
		print 'SHIPSELPARAM=SUCCESS';
		print 'SHIPSELPARAM=' . $countryTax;
		print 'SHIPSELPARAM=' . $shipType;
	}else{
		if($isadmincalc){ $orderid=0; print '<table><tr><td>'; }else $orderid=getget('orderid');
		if(is_numeric($orderid)){
			retrieveorderdetails($orderid, $thesessionid);
			$freeshippingincludeshandling=FALSE;
			insuranceandtaxaddedtoshipping();
			calculateshippingdiscounts(FALSE);
			calculatetaxandhandling();
			$cpnmessage=substr($cpnmessage,6);
			if($shipType>=2){
				if(@$shippingoptionsasradios!=TRUE) print '<select size="1" onchange="updateshiprate(this,(this.selectedIndex-1)+'.$numshiprate.')"><option value="">'.$GLOBALS['xxPlsSel'].' ('.$GLOBALS['xxFromSE'].': '.FormatEuroCurrency(($shipping+(@$combineshippinghandling?$handling:0))-$freeshipamnt).')</option>';
				for($indexmso=0; $indexmso<$maxshipoptions; $indexmso++){
					$shipRow=$intShipping[$indexmso];
					if($shipRow[3]){
						calculatetaxandhandling();
						if($isadmincalc){
							writehiddenidvar("shipmethod".$numshiprate,$shipRow[0]);
							writehiddenidvar("shipcost".$numshiprate,$shipRow[2]);
						}
						writeshippingoption(round($shipRow[2], 2), round($shipRow[7], 2), $shipRow[4], $shipRow[0], FALSE, $shipRow[1]);
					}
				}
				if($shippingoptionsasradios!=TRUE) print '</select>';
			}
			if(! $isadmincalc) saveshippingoptions();
		}
		if($isadmincalc){
			print '</td></tr></table>';
		}else{
			print 'SHIPSELPARAM='.str_replace('+','%20',urlencode($shipsellogo));
			print 'SHIPSELPARAM=REMOVEME';
			print 'SHIPSELPARAM=REMOVEME';
			print 'SHIPSELPARAM='.$numshiprate;
		}
	}
}else{
	$success=FALSE;
	print '&nbsp;' . $errormsg;
	if(getget('action')!='admincalc'){
		print 'SHIPSELPARAM='.str_replace('+','%20',urlencode($shipsellogo));
		print 'SHIPSELPARAM=ERROR';
	}
}
if(getget('action')=='admincalc'){
	if(@$_SESSION['loggedon']==''){
		print '<table width="100%" cellspacing="2" cellpadding="2" border="0"><tr><td align="center">';
		print '&nbsp;<br />&nbsp;<br />&nbsp;<br /><strong>Session Timed Out.</strong><br /><br />Please close this window and click "Calculate" again.<br />';
	}
	print '<br />&nbsp;</td></tr></table>'; ?>
</body>
</html>
<?php
}
?>