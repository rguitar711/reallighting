<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$dateadjust=='') $dateadjust=0;
if(@$dateformatstr=='') $dateformatstr='m/d/Y';
$admindatestr='Y-m-d';
if(@$admindateformat=='') $admindateformat=0;
if($admindateformat==1)
	$admindatestr='m/d/Y';
elseif($admindateformat==2)
	$admindatestr='d/m/Y';
if(@$storesessionvalue=='') $storesessionvalue='virtualstore'.time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
if(getget('doedit')=='true' || getget('id')=='new') $doedit=TRUE; else $doedit=FALSE;
$isinvoice=(getget('invoice')=='true');
if(@$maxordersperpage=='')$maxordersperpage=250;
$iNumOfPages=0;
if(! is_numeric(getget('pg')) || strlen(getget('pg'))>8) $CurPage=1; else $CurPage=max(1, (int)getget('pg'));
function trimoldcartitems($cartitemsdel){
	global $dateadjust;
	if(@$dateadjust=='') $dateadjust=0;
	$thetocdate=time() + ($dateadjust*60*60);
	$sSQL="SELECT adminDelUncompleted,adminClearCart FROM admin WHERE adminID=1";
	$result=ect_query($sSQL) or ect_error();
	$rs=ect_fetch_assoc($result);
	$delAfter=$rs['adminDelUncompleted'];
	$delSavedCartAfter=$rs['adminClearCart'];
	ect_free_result($result);
	if($delAfter!=0){
		$sSQL="SELECT ordID FROM orders WHERE ordAuthNumber='' AND ordDate<'" . date("Y-m-d H:i:s", $thetocdate-($delAfter*60*60*24)) . "' AND ordStatus=2 LIMIT 0,1000";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){
			release_stock($rs['ordID']);
			ect_query("UPDATE cart SET cartOrderID=0 WHERE cartOrderID=".$rs['ordID']);
			ect_query("DELETE FROM orders WHERE ordID=".$rs['ordID']);
		}
		ect_free_result($result);
	}
	$sSQL='SELECT cartID,listOwner FROM cart LEFT JOIN customerlists ON cart.cartListID=customerlists.listID WHERE cartCompleted=0 AND cartOrderID=0 AND ';
	$sSQL.="((cartClientID=0 AND cartDateAdded<'" . date("Y-m-d H:i:s", $cartitemsdel) . "') ";
	if($delSavedCartAfter!=0) $sSQL.="OR (cartDateAdded<'" . date("Y-m-d H:i:s", $thetocdate-($delSavedCartAfter*60*60*24)) . "') ";
	$sSQL.=') LIMIT 0,1000';
	$result=ect_query($sSQL) or ect_error();
	$rowcounter=0;
	if(ect_num_rows($result) > 0){
		$delOptions=$addcomma='';
		while($rs=ect_fetch_assoc($result)){
			if(! is_null($rs['listOwner'])){
				ect_query("UPDATE cart SET cartCompleted=3,cartClientID=" . $rs['listOwner'] . " WHERE cartID=" . $rs['cartID']) or ect_error();
			}else{
				$delOptions.=$addcomma . $rs['cartID'];
				$addcomma=',';
			}
			$rowcounter++;
		}
		if($delOptions!='') ect_query("DELETE FROM cartoptions WHERE coCartID IN (" . $delOptions . ')');
		if($delOptions!='') ect_query("DELETE FROM cart WHERE cartID IN (" . $delOptions . ')');
	}
	ect_free_result($result);
	return($rowcounter>950?'':'1');
}
function editspecial($data,$col,$size,$special){
	global $doedit;
	if($doedit) return('<input type="text" id="' . $col . '" name="' . $col . '" value="' . htmlspecialsucode($data) . '" size="' . $size . '" '.$special.' />'); else return(htmldisplay($data));
}
function editfunc($data,$col,$size){
	global $doedit;
	if($doedit) return('<input type="text" id="' . $col . '" name="' . $col . '" value="' . htmlspecialsucode($data) . '" size="' . $size . '" />'); else return(htmldisplay($data));
}
function editnumeric($data,$col,$size){
	global $doedit;
	if($doedit) return('<input type="text" id="' . $col . '" name="' . $col . '" value="' . number_format(strip_tags($data),2,'.','') . '" size="' . $size . '" />'); else return(FormatEuroCurrency(strip_tags($data)));
}
function getNumericField($fldname){
	$fldval=getpost($fldname);
	if(! is_numeric($fldval)) return(0); else return((double)$fldval);
}
function decodehtmlentities($thestr){
	return(str_replace(array('&quot;','&nbsp;'), array('"', ' '), $thestr));
}
function writesearchparams(){
	writehiddenvar('fromdate', @$_SESSION['fromdate']);
	writehiddenvar('todate', @$_SESSION['todate']);
	writehiddenvar('notstatus', @$_SESSION['notstatus']);
	writehiddenvar('notsearchfield', @$_SESSION['notsearchfield']);
	writehiddenvar('searchtext', @$_SESSION['searchtext']);
	if(is_array($_SESSION['ordStatus'])){
		foreach($_SESSION['ordStatus'] as $key => $val)
			writehiddenvar('ordStatus[]', $val);
	}
	if(is_array($_SESSION['ordstate'])){
		foreach($_SESSION['ordstate'] as $key => $val)
			writehiddenvar('ordstate[]', $val);
	}
	if(is_array($_SESSION['ordcountry'])){
		foreach($_SESSION['ordcountry'] as $key => $val)
			writehiddenvar('ordcountry[]', $val);
	}
	if(is_array($_SESSION['payprovider'])){
		foreach($_SESSION['payprovider'] as $key => $val)
			writehiddenvar('payprovider[]', $val);
	}
	writehiddenvar('ordersearchfield', @$_COOKIE['ordersearchfield']);
}
function showgetoptionsselect($oid){
	return('<div style="position:absolute"><select id="'.$oid.'" size="15" ' .
		'style="display:none;position:absolute;min-width:280px;top:0px;left:0px;" ' .
		'onblur="this.style.display=\'none\'" ' .
		'onchange="comboselect_onchange(this)" ' .
		'onclick="comboselect_onclick(this)" ' .
		'onkeyup="comboselect_onkeyup(event.keyCode,this)">' .
		'<option value="">Populating...</option>' .
		'</select></div>');
}
function updateorderstatus($iordid, $ordstatus){
	global $htmlemails,$yyTrackT,$ordstatusemail,$dateformatstr,$dateadjust,$emlNl,$emailencoding,$emailAddr,$trackingnumtext,$ordstatussubject,$storeurl,$adminlangsettings,$languageid,$loyaltypoints,$giftcertificateid;
	$ordauthno='';
	$oldordstatus=999;
	$payprovider=0;
	$ordClientID=0;
	$loyaltypointtotal=0;
	$savelangid=@$languageid;
	$replaceone=FALSE;
	if($iordid!=''){
		$result=ect_query("SELECT ordStatus,ordAuthNumber,ordEmail,ordDate,".getlangid("statPublic",64).",ordStatusInfo,ordName,ordLastName,ordTrackNum,ordPayProvider,ordLang,ordClientID,loyaltyPoints,ordTotal,ordDiscount,pointsRedeemed FROM orders INNER JOIN orderstatus ON orders.ordStatus=orderstatus.statID WHERE ordID=" . $iordid) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$oldordstatus=$rs['ordStatus'];
			$ordauthno=$rs['ordAuthNumber'];
			$ordemail=$rs['ordEmail'];
			$orddate=strtotime($rs['ordDate']);
			$oldstattext=$rs[getlangid('statPublic',64)];
			$ordstatinfo=$rs['ordStatusInfo'];
			if(@$htmlemails==TRUE) $ordstatinfo=str_replace("\r\n", '<br />', $ordstatinfo);
			$ordername=trim($rs['ordName'] . ' ' . $rs['ordLastName']);
			$trackingnum=trim($rs['ordTrackNum']);
			$payprovider=$rs['ordPayProvider'];
			$languageid=$rs['ordLang']+1;
			$ordClientID=$rs['ordClientID'];
			$loyaltypointtotal=$rs['loyaltyPoints'];
			$ordTotal=$rs['ordTotal'];
			$ordDiscount=$rs['ordDiscount'];
			$pointsredeemed=$rs['pointsRedeemed'];
		}
		ect_free_result($result);
		$result=ect_query("SELECT ".getlangid("statPublic",64)." FROM orders INNER JOIN orderstatus ON orders.ordStatus=orderstatus.statID WHERE ordID=" . $iordid) or ect_error();
		if($rs=ect_fetch_assoc($result))
			$oldstattext=$rs[getlangid('statPublic',64)];
		ect_free_result($result);
	}
	ect_query('UPDATE cart SET cartCompleted='.($ordstatus==2?0:1).' WHERE cartOrderID='.$iordid) or ect_error();
	if((@$GLOBALS['loyaltypointsnowholesale'] || @$GLOBALS['loyaltypointsnopercentdiscount']) && $ordClientID!=0){
		$sSQL="SELECT clActions FROM customerlogin WHERE clID=" . $ordClientID;
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			if(@$GLOBALS['loyaltypointsnowholesale'] && ($rs['clActions'] & 8)==8) $ordClientID=0;
			if(@$GLOBALS['loyaltypointsnopercentdiscount'] && ($rs['clActions'] & 16)==16) $ordClientID=0;
		}
		ect_free_result($result);
	}
	if($oldordstatus!=999 && ($oldordstatus<3 && $ordstatus>=3)){
		if($ordauthno=='') ect_query("UPDATE orders SET ordAuthNumber='". escape_string($yyManAut) . "' WHERE ordID=" . $iordid) or ect_error();
		$sSQL="SELECT cartProdId,cartProdName,cartProdPrice,cartQuantity,cartID FROM cart LEFT JOIN products ON cart.cartProdId=products.pID WHERE cartOrderID='" . escape_string($iordid) . "'";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){
			if($rs['cartProdId']==$giftcertificateid){
				$sSQL='UPDATE giftcertificate SET gcAuthorized=1,gcOrigAmount='.$rs['cartProdPrice'].',gcRemaining='.$rs['cartProdPrice'].' WHERE gcAuthorized=0 AND gcCartID=' . $rs['cartID'];
				ect_query($sSQL) or ect_error();
			}
		}
		ect_free_result($result);
		if(@$loyaltypoints!=''){
			$loyaltypointtotal=(int)(($ordTotal-$ordDiscount)*$loyaltypoints);
			ect_query("UPDATE orders SET loyaltyPoints=" . $loyaltypointtotal . " WHERE ordID=" . $iordid) or ect_error();
			if($ordClientID!=0) ect_query("UPDATE customerlogin SET loyaltyPoints=loyaltyPoints+" . $loyaltypointtotal . " WHERE clID=" . $ordClientID);
		}
	}elseif($oldordstatus!=999 && ($oldordstatus>=3 && $ordstatus<3)){
		ect_query("UPDATE giftcertificate SET gcAuthorized=0 WHERE gcOrderID=" . $iordid) or ect_error();
		if($ordClientID!=0 && @$loyaltypoints!='') ect_query("UPDATE customerlogin SET loyaltyPoints=loyaltyPoints-" . $loyaltypointtotal . " WHERE clID=" . $ordClientID);
	}
	if($oldordstatus!=999 && ($oldordstatus<2 && $ordstatus>=2)){
		if($ordClientID!=0) ect_query("UPDATE customerlogin SET loyaltyPoints=loyaltyPoints-" . $pointsredeemed . " WHERE clID=" . $ordClientID);
	}elseif($oldordstatus!=999 && ($oldordstatus>=2 && $ordstatus<2)){
		if($ordClientID!=0) ect_query("UPDATE customerlogin SET loyaltyPoints=loyaltyPoints+" . $pointsredeemed . " WHERE clID=" . $ordClientID);
	}
	if($oldordstatus!=999 && ($oldordstatus<=1 && $ordstatus>1) && (time()-$orddate) < (86400*365)) stock_subtract($iordid);
	if($oldordstatus!=999 && ($oldordstatus>1 && $ordstatus<=1) && (time()-$orddate) < (86400*365)) release_stock($iordid);
	if($iordid!='' && $ordstatus!==''){
		if($oldordstatus!=(int)$ordstatus && getpost('emailstat')=='1' && $ordstatus!=1){
			$result=ect_query("SELECT ".getlangid('statPublic',64).",emailstatus FROM orderstatus WHERE statID=" . $ordstatus);
			if($rs=ect_fetch_assoc($result)){
				$newstattext=$rs[getlangid('statPublic',64)];
				$emailstatus=($rs['emailstatus']!=0);
			}else
				$emailstatus=FALSE;
			ect_free_result($result);
			if(($adminlangsettings & 4096)==0) $languageid=1;
			if(@$ordstatussubject[$languageid]!='') $emailsubject=$ordstatussubject[$languageid]; else $emailsubject='Order status updated';
			$ose=$ordstatusemail[$languageid];
			$timestest=0;
			for($uoindex=0; $uoindex<=18; $uoindex++){
				$replaceone=TRUE;
				while($replaceone && $timestest++ < 30){
					$ose=replaceemailtxt($ose, '%statusid' . $uoindex . '%', $uoindex==$ordstatus ? '%ectpreserve%' : '', $replaceone);
				}
			}
			$ose=str_replace('%orderid%', $iordid, $ose);
			$ose=str_replace('%orderdate%', date($dateformatstr, $orddate) . ' ' . date('H:i', $orddate), $ose);
			$ose=str_replace('%oldstatus%', $oldstattext, $ose);
			$ose=str_replace('%newstatus%', $newstattext, $ose);
			$thetime=time() + ($dateadjust*60*60);
			$ose=str_replace('%date%', date($dateformatstr, $thetime) . ' ' . date('H:i', $thetime), $ose);
			$ose=str_replace('%ordername%', $ordername, $ose);
			$ose=replaceemailtxt($ose, '%statusinfo%', $ordstatinfo, $replaceone);
			$tracknumarr=explode(',', $trackingnum);
			foreach($tracknumarr as $key => $value){
				$ose=replaceemailtxt($ose, '%trackingnum%', $value, $replaceone);
			}
			while(strpos($ose, '%trackingnum%')!==FALSE){
				$ose=replaceemailtxt($ose, '%trackingnum%', '', $replaceone);
			}
			$reviewlinks=$norepeatlinks='';
			if(strpos($ose, '%reviewlinks%')!==FALSE){
				$sSQL="SELECT cartProdID,cartOrigProdID FROM cart WHERE cartOrderID=".$iordid;
				$result2=ect_query($sSQL) or ect_error();
				while($rs2=ect_fetch_assoc($result2)){
					if(trim($rs2['cartOrigProdID'])!='') $cartprodid=$rs2['cartOrigProdID']; else $cartprodid=$rs2['cartProdID'];
					if(strpos($norepeatlinks,",'".$cartprodid."'")===FALSE){
						$norepeatlinks.=",'".$cartprodid."'";
						$sSQL="SELECT pID,".getlangid('pName',1).",pStaticPage,pStaticURL,pDisplay FROM products WHERE pDisplay<>0 AND pID='".escape_string($cartprodid)."'";
						$result=ect_query($sSQL) or ect_error();
						if($rs=ect_fetch_assoc($result)){
							$thelink=$storeurl . getdetailsurl($rs['pID'],$rs['pStaticPage'],$rs[getlangid('pName',1)],$rs['pStaticURL'],'review=true','');
							if(@$htmlemails==TRUE) $thelink='<a href="' . $thelink . '">' . $thelink . '</a>';
							$reviewlinks.=$thelink . $emlNl;
						}
						ect_free_result($result);
					}
				}
				ect_free_result($result2);
			}
			$ose=replaceemailtxt($ose, '%reviewlinks%', $reviewlinks, $replaceone);
			$ose=str_replace(array('%nl%','<br />'), $emlNl, $ose);
			if($emailstatus!=0) dosendemail($ordemail, $emailAddr, '', $emailsubject, $ose);
		}
		if($oldordstatus!=(int)$ordstatus) ect_query("UPDATE orders SET ordStatus=" . $ordstatus . ",ordStatusDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "' WHERE ordID=" . $iordid) or ect_error();
	}
	$languageid=$savelangid;
}
if(@$htmlemails==TRUE) $emlNl='<br />'; else $emlNl="\n";
$success=true;
$alreadygotadmin=getadminsettings();
$homecountrytaxrate=$countryTaxRate;
if(getpost('updatestatus')=='1' || getpost('act')=='status'){
	$sSQL='SELECT orderstatussubject,orderstatussubject2,orderstatussubject3,orderstatusemail,orderstatusemail2,orderstatusemail3 FROM emailmessages WHERE emailID=1';
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		$ordstatussubject[1]=$rs['orderstatussubject'];
		$ordstatusemail[1]=$rs['orderstatusemail'];
		$ordstatussubject[2]=$rs['orderstatussubject2'];
		$ordstatusemail[2]=$rs['orderstatusemail2'];
		$ordstatussubject[3]=$rs['orderstatussubject3'];
		$ordstatusemail[3]=$rs['orderstatusemail3'];
	}
	ect_free_result($result);
}
if(getpost('updatestatus')=='1'){
	ect_query("UPDATE orders SET ordTrackNum='" . escape_string(getpost('ordTrackNum')) . "',ordStatusInfo='" . escape_string(getpost('ordStatusInfo')) . "',ordPrivateStatus='" . escape_string(getpost('ordPrivateStatus')) . "',ordInvoice='" . escape_string(getpost('ordInvoice')) . "'" . (getpost('shipcarrier')!='' ? ',ordShipCarrier=' . getpost('shipcarrier') : '') . " WHERE ordID='" . escape_string(getpost('orderid')) . "'") or ect_error();
	ect_query("UPDATE orders set ordAuthNumber='" . escape_string(getpost('authcode')!='' ? getpost('authcode') : $yyManAut) . "' WHERE ordStatus>=3 AND ordAuthNumber='' AND ordID='" . escape_string(getpost('orderid')) . "'") or ect_error();
	ect_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string(getpost('orderid')) . "'") or ect_error();
	updateorderstatus(getpost('orderid'), (int)getpost('ordStatus'));
}elseif(getget('id')!='' && getget('id')!='multi' && getget('id')!='new'){
	if(getpost('delccdets')!='')
		ect_query("UPDATE orders SET ordCNum='' WHERE ordID=" . getget('id')) or ect_error();
}else{
	$delccafter=0;
	if($delccafter!=0) ect_query("UPDATE orders SET ordCNum='' WHERE ordDate<'" . date("Y-m-d H:i:s", time()-($delccafter*60*60*24)) . "'") or ect_error();
	if(@$persistentcart=='') $persistentcart=3;
	if(@$_SESSION['hasdeletedoldcart']!='1'){ trimoldcartitems(time()-($persistentcart*60*60*24)); $_SESSION['hasdeletedoldcart']='1'; }
}
$numstatus=0;
$sSQL="SELECT statID,statPrivate FROM orderstatus WHERE statPrivate<>'' ORDER BY statID";
$result=ect_query($sSQL) or ect_error();
while($rs=ect_fetch_assoc($result)){
	$allstatus[$numstatus++]=$rs;
}
ect_free_result($result);
if(getpost('updatestatus')=='1'){
?>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<form id="searchparamsform" method="post" action="adminorders.php">
<?php		writesearchparams(); ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr>
                <td width="100%" colspan="4" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?><br/><br/><input type="submit" value="<?php print $yyClkHer?>"><br /><br />&nbsp;</td>
			  </tr>
			</table>
			</form>
		  </td>
		</tr>
	  </table>
<script type="text/javascript">
setTimeout('document.getElementById("searchparamsform").submit()', 500);
</script>
<?php
}elseif(getpost('doedit')=='true'){
	$OWSP='';
	$orderid=getpost('orderid');
	$ordstatus=(int)getpost('ordStatus');
	$oldordstatus=0;
	$ordComLoc=0;
	$thecustomerid=0;
	if($orderid!='new'){
		$sSQL="SELECT ordSessionID,ordClientID,ordAuthStatus,ordShipType,loyaltyPoints,ordStatus FROM orders WHERE ordID='" . $orderid . "'";
		$result=ect_query($sSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		$thesessionid=$rs['ordSessionID'];
		$thecustomerid=$rs['ordClientID'];
		$loyaltypointtotal=$rs['loyaltyPoints'];
		$oldordstatus=$rs['ordStatus'];
		ect_free_result($result);
		if($oldordstatus>=2&&getpost('updatestock')=='ON') release_stock($orderid);
		if($thecustomerid!=0 && @$loyaltypoints!='' && $loyaltypointtotal!=0 && $oldordstatus>=3) ect_query('UPDATE customerlogin SET loyaltyPoints=loyaltyPoints-' . $loyaltypointtotal . ' WHERE clID=' . $thecustomerid) or ect_error();
		if($rs['ordAuthStatus']=='MODWARNOPEN' || is_null($rs['ordAuthStatus'])) ect_query("UPDATE orders SET ordAuthStatus='' WHERE ordID='" . $orderid . "'") or ect_error();
		if($rs['ordShipType']=='MODWARNOPEN' || is_null($rs['ordShipType'])) ect_query("UPDATE orders SET ordShipType='' WHERE ordID='" . $orderid . "'") or ect_error();
	}
	if(getpost('commercialloc')=='Y') $ordComLoc=1;
	if(getpost('wantinsurance')=='Y') $ordComLoc+=2;
	if(getpost('saturdaydelivery')=='Y') $ordComLoc+=4;
	if(getpost('signaturerelease')=='Y') $ordComLoc+=8;
	if(getpost('insidedelivery')=='Y') $ordComLoc+=16;
	if(getpost('custid')!='' && is_numeric(getpost('custid')))
		$thecustomerid=getpost('custid');
	if($orderid=='new'){
		$thesessionid='A1';
		$sSQL='INSERT INTO orders (ordSessionID,ordClientID,ordName,ordLastName,ordAddress,ordAddress2,ordCity,ordState,ordZip,ordCountry,ordEmail,ordPhone,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipZip,ordShipCountry,ordShipPhone,ordPayProvider,ordAuthNumber,ordTransID,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordLang,ordHandling,ordShipType,ordShipCarrier,loyaltyPoints,ordTotal,ordDate,ordStatusInfo,ordPrivateStatus,ordStatus,ordAuthStatus,ordStatusDate,ordComLoc,ordIP,ordAffiliate,ordExtra1,ordExtra2,ordShipExtra1,ordShipExtra2,ordCheckoutExtra1,ordCheckoutExtra2,ordDiscount,ordDiscountText,ordInvoice,ordAddInfo) VALUES (';
		$sSQL.="'".$thesessionid."',";
		$sSQL.="'".$thecustomerid."',";
		$sSQL.="'" . escape_string(getpost('name')) . "',";
		$sSQL.="'" . escape_string(getpost('lastname')) . "',";
		$sSQL.="'" . escape_string(getpost('address')) . "',";
		$sSQL.="'" . escape_string(getpost('address2')) . "',";
		$sSQL.="'" . escape_string(getpost('city')) . "',";
		$sSQL.="'" . escape_string(getpost('state')) . "',";
		$sSQL.="'" . escape_string(getpost('zip')) . "',";
		$sSQL.="'" . escape_string(getpost('country')) . "',";
		$sSQL.="'" . escape_string(getpost('email')) . "',";
		$sSQL.="'" . escape_string(getpost('phone')) . "',";
		$sSQL.="'" . escape_string(getpost('sname')) . "',";
		$sSQL.="'" . escape_string(getpost('slastname')) . "',";
		$sSQL.="'" . escape_string(getpost('saddress')) . "',";
		$sSQL.="'" . escape_string(getpost('saddress2')) . "',";
		$sSQL.="'" . escape_string(getpost('scity')) . "',";
		$sSQL.="'" . escape_string(getpost('sstate')) . "',";
		$sSQL.="'" . escape_string(getpost('szip')) . "',";
		$sSQL.="'" . escape_string(getpost('scountry')) . "',";
		$sSQL.="'" . escape_string(getpost('sphone')) . "',";
		$sSQL.="'4',"; // ordPayProvider
		$sSQL.="'" . escape_string(getpost('ordAuthNumber')) . "',";
		$sSQL.="'" . escape_string(getpost('ordTransID')) . "',";
		$sSQL.="'" . escape_string(getpost('ordShipping')) . "',";
		$sSQL.="'" . escape_string(getpost('ordStateTax')) . "',";
		$sSQL.="'" . escape_string(getpost('ordCountryTax')) . "',";
		if($origCountryID==2) $sSQL.="'" . escape_string(getpost('ordHSTTax')) . "',"; else $sSQL.="'0',";
		$sSQL.="'" . (is_numeric(getpost('ordlang'))?getpost('ordlang'):0) . "',";
		$sSQL.="'" . escape_string(getpost('ordHandling')) . "',";
		$sSQL.="'" . escape_string(getpost('shipmethod')) . "',";
		$sSQL.="'" . escape_string(getpost('shipcarrier')) . "',";
		$sSQL.="'" . getNumericField('loyaltyPoints') . "',";
		$sSQL.="'" . escape_string(getpost('ordtotal')) . "',";
		$sSQL.="'" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "',";
		$sSQL.="'" . escape_string(getpost('ordStatusInfo')) . "',";
		$sSQL.="'" . escape_string(getpost('ordPrivateStatus')) . "',";
		$sSQL.="'" . escape_string($ordstatus) . "',";
		$sSQL.="'',"; // ordAuthStatus
		$sSQL.="'" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "',";
		$sSQL.="'" . $ordComLoc . "',";
		$sSQL.="'" . escape_string(getpost('ipaddress')) . "',";
		$sSQL.="'" . getpost('PARTNER') . "',";
		$sSQL.="'" . escape_string(getpost('extra1')) . "',";
		$sSQL.="'" . escape_string(getpost('extra2')) . "',";
		$sSQL.="'" . escape_string(getpost('shipextra1')) . "',";
		$sSQL.="'" . escape_string(getpost('shipextra2')) . "',";
		$sSQL.="'" . escape_string(getpost('checkoutextra1')) . "',";
		$sSQL.="'" . escape_string(getpost('checkoutextra2')) . "',";
		$sSQL.="'" . escape_string(getpost('ordDiscount')) . "',";
		$sSQL.="'" . escape_string(str_replace(array("\r\n","\n","\r"),array('<br />','<br />','<br />'),getpost('discounttext'))) . "',";
		$sSQL.="'" . escape_string(getpost('ordInvoice')) . "',";
		$sSQL.="'" . escape_string(getpost('ordAddInfo')) . "')";
		ect_query($sSQL) or ect_error();
		$orderid=ect_insert_id();
	}else{
		$sSQL="UPDATE orders SET ";
		$sSQL.="ordName='" . escape_string(getpost('name')) . "',";
		$sSQL.="ordLastName='" . escape_string(getpost('lastname')) . "',";
		$sSQL.="ordAddress='" . escape_string(getpost('address')) . "',";
		$sSQL.="ordAddress2='" . escape_string(getpost('address2')) . "',";
		$sSQL.="ordCity='" . escape_string(getpost('city')) . "',";
		$sSQL.="ordState='" . escape_string(getpost('state')) . "',";
		$sSQL.="ordZip='" . escape_string(getpost('zip')) . "',";
		$sSQL.="ordCountry='" . escape_string(getpost('country')) . "',";
		$sSQL.="ordEmail='" . escape_string(getpost('email')) . "',";
		$sSQL.="ordPhone='" . escape_string(getpost('phone')) . "',";
		$sSQL.="ordShipName='" . escape_string(getpost('sname')) . "',";
		$sSQL.="ordShipLastName='" . escape_string(getpost('slastname')) . "',";
		$sSQL.="ordShipAddress='" . escape_string(getpost('saddress')) . "',";
		$sSQL.="ordShipAddress2='" . escape_string(getpost('saddress2')) . "',";
		$sSQL.="ordShipCity='" . escape_string(getpost('scity')) . "',";
		$sSQL.="ordShipState='" . escape_string(getpost('sstate')) . "',";
		$sSQL.="ordShipZip='" . escape_string(getpost('szip')) . "',";
		$sSQL.="ordShipCountry='" . escape_string(getpost('scountry')) . "',";
		$sSQL.="ordShipPhone='" . escape_string(getpost('sphone')) . "',";
		$sSQL.="ordShipType='" . escape_string(getpost('shipmethod')) . "',";
		$sSQL.="ordShipCarrier='" . escape_string(getpost('shipcarrier')) . "',";
		$sSQL.="ordIP='" . escape_string(getpost('ipaddress')) . "',";
		$sSQL.="ordComLoc=" . $ordComLoc . ",";
		$sSQL.="ordAffiliate='" . getpost('PARTNER') . "',";
		$sSQL.="ordAddInfo='" . escape_string(getpost('ordAddInfo')) . "',";
		$sSQL.="ordStatusInfo='" . escape_string(getpost('ordStatusInfo')) . "',";
		$sSQL.="ordPrivateStatus='" . escape_string(getpost('ordPrivateStatus')) . "',";
		$sSQL.="ordStatus='" . escape_string($ordstatus) . "',";
		$sSQL.="ordTrackNum='" . escape_string(getpost('ordTrackNum')) . "',";
		$sSQL.="ordDiscountText='" . escape_string(str_replace(array("\r\n","\n","\r"),array('<br />','<br />','<br />'),getpost('discounttext'))) . "',";
		$sSQL.="ordInvoice='" . escape_string(getpost('ordInvoice')) . "',";
		$sSQL.="ordExtra1='" . escape_string(getpost('extra1')) . "',";
		$sSQL.="ordExtra2='" . escape_string(getpost('extra2')) . "',";
		$sSQL.="ordShipExtra1='" . escape_string(getpost('shipextra1')) . "',";
		$sSQL.="ordShipExtra2='" . escape_string(getpost('shipextra2')) . "',";
		$sSQL.="ordCheckoutExtra1='" . escape_string(getpost('checkoutextra1')) . "',";
		$sSQL.="ordCheckoutExtra2='" . escape_string(getpost('checkoutextra2')) . "',";
		$sSQL.="ordShipping='" . escape_string(getpost('ordShipping')) . "',";
		$sSQL.="ordStateTax='" . escape_string(getpost('ordStateTax')) . "',";
		$sSQL.="ordCountryTax='" . escape_string(getpost('ordCountryTax')) . "',";
		if($origCountryID==2) $sSQL.="ordHSTTax='" . escape_string(getpost('ordHSTTax')) . "',";
		$sSQL.="ordDiscount='" . escape_string(getpost('ordDiscount')) . "',";
		$sSQL.="ordHandling='" . escape_string(getpost('ordHandling')) . "',";
		$ordauthnumber=getpost('ordAuthNumber');
		if((int)getpost('ordStatus')>2 && $ordauthnumber=='') $ordauthnumber='manual auth';
		$sSQL.="ordAuthNumber='" . escape_string($ordauthnumber) . "',";
		$sSQL.="ordTransID='" . escape_string(getpost('ordTransID')) . "',";
		$sSQL.="loyaltyPoints='" . getNumericField('loyaltyPoints') . "',";
		$sSQL.="ordTotal='" . escape_string(getpost('ordtotal')) . "'";
		$sSQL.=" WHERE ordID='" . getpost('orderid') . "'";
		ect_query($sSQL) or ect_error();
	}
	if($ordstatus>2) ect_query("UPDATE giftcertificate SET gcAuthorized=1 WHERE gcOrderID=" . $orderid) or ect_error();
	if($ordstatus<=2) ect_query("UPDATE giftcertificate SET gcAuthorized=0 WHERE gcOrderID=" . $orderid) or ect_error();
	if($thecustomerid!=0 && @$loyaltypoints!='' && $ordstatus>=3) ect_query('UPDATE customerlogin SET loyaltyPoints=loyaltyPoints+' . getNumericField('loyaltyPoints') . ' WHERE clID=' . $thecustomerid) or ect_error();
	foreach($_POST as $objItem => $objValue){
		if(substr($objItem,0,6)=='prodid'){
			$idno=(int)substr($objItem, 6);
			$cartid=getpost('cartid' . $idno);
			$prodid=getpost('prodid' . $idno);
			$quant=getpost('quant' . $idno);
			if(! is_numeric($quant)) $quant=1;
			$theprice=getpost('price' . $idno);
			if(! is_numeric($theprice)) $theprice=0;
			$prodname=getpost('prodname' . $idno);
			$delitem=getpost('del_' . $idno);
			$sSQL="SELECT pWeight FROM products WHERE pID='".escape_string($prodid)."'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)) $thepweight=$rs['pWeight']; else $thepweight=0;
			ect_free_result($result);
			if($delitem=='yes' || ($cartid!='' && trim($prodid)=='')){
				ect_query("DELETE FROM cart WHERE cartID=" . $cartid) or ect_error();
				ect_query("DELETE FROM cartoptions WHERE coCartID=" . $cartid) or ect_error();
				$cartid='';
			}elseif($cartid!=''){
				$sSQL="UPDATE cart SET cartProdID='" . escape_string($prodid) . "',cartProdPrice=" . $theprice . ",cartProdName='" . escape_string($prodname) . "',cartQuantity=" . $quant . ",cartCompleted=0 WHERE cartID=" . $cartid;
				ect_query($sSQL) or ect_error();
				ect_query("DELETE FROM cartoptions WHERE coCartID=" . $cartid) or ect_error();
			}else{
				$sSQL='INSERT INTO cart (cartSessionID,cartClientID,cartProdID,cartQuantity,cartCompleted,cartProdName,cartProdPrice,cartOrderID,cartDateAdded) VALUES (';
				$sSQL.="'" . $thesessionid . "','" . $thecustomerid . "','" . escape_string($prodid) . "',";
				$sSQL.=$quant . ",0,'" . escape_string($prodname) . "','" . $theprice . "'," . $orderid . ",";
				$sSQL.="'" . date('Y-m-d', time() + ($dateadjust*60*60)) . "')";
				ect_query($sSQL) or ect_error();
				$cartid=ect_insert_id();
			}
			if($cartid!=''){
				if($ordstatus!=2) ect_query("UPDATE cart SET cartCompleted=1 WHERE cartID=".$cartid) or ect_error();
				$optprefix="optn" . $idno . '_';
				$prefixlen=strlen($optprefix);
				foreach($_POST as $kk => $kkval){
					if(substr($kk,0,$prefixlen)==$optprefix && trim($kkval)!=''){
						$optidarr=explode('|', $kkval);
						$optid=$optidarr[0];
						if(@$_POST['v' . $kk]==""){
							$sSQL='SELECT optID,'.getlangid('optGrpName',16).','.getlangid('optName',32).',' . $OWSP . "optPriceDiff,optWeightDiff,optType,optFlags FROM options LEFT JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optID='" . escape_string($kkval) . "'";
							$result=ect_query($sSQL) or ect_error();
							if($rs=ect_fetch_assoc($result)){
								if(abs($rs['optType'])!=3){
									$sSQL='INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff) VALUES (' . $cartid . ',' . $rs['optID'] . ",'" . escape_string($rs[getlangid('optGrpName',16)]) . "','" . escape_string($rs[getlangid('optName',32)]) . "',";
									$sSQL.=$optidarr[1] . ',';
									if(($rs['optFlags']&2)==0) $sSQL.=$rs['optWeightDiff'] . ')'; else $sSQL.=(($thepweight*$rs['optWeightDiff'])/100.0) . ')';
								}else
									$sSQL='INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff) VALUES (' . $cartid . ',' . $rs['optID'] . ",'" . escape_string($rs[getlangid('optGrpName',16)]) . "','',0,0)";
								ect_query($sSQL) or ect_error();
							}
							ect_free_result($result);
						}else{
							$sSQL='SELECT optID,'.getlangid('optGrpName',16).','.getlangid('optName',32).",optTxtCharge,optMultiply,optAcceptChars FROM options LEFT JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optID='" . escape_string($kkval) . "'";
							$result=ect_query($sSQL) or ect_error();
							if($rs=ect_fetch_assoc($result)){
								$theopttoadd=getpost('v' . $kk);
								$optPriceDiff=($rs['optTxtCharge']<0&&$theopttoadd!=''?abs($rs['optTxtCharge']):$rs['optTxtCharge']*strlen($theopttoadd));
								$optmultiply=0;
								if($rs['optMultiply']!=0){
									if(is_numeric($theopttoadd)) $optmultiply=(double)$theopttoadd; else $theopttoadd='#NAN';
								}
								$sSQL='INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff,coMultiply) VALUES (' . $cartid . ',' . $rs['optID'] . ",'" . escape_string($rs[getlangid('optGrpName',16)]) . "','" . escape_string(getpost('v' . $kk)) . "',".$optPriceDiff.',0,' . $rs['optMultiply'] . ')';
								ect_query($sSQL) or ect_error();
							}
							ect_free_result($result);
						}
					}
				}
			}
		}
	}
	if($ordstatus>=2&&getpost('updatestock')=='ON') stock_subtract($orderid); ?>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<form id="searchparamsform" method="post" action="adminorders.php">
<?php		writesearchparams();
			if(getpost('orderid')!='new') writehiddenvar('ctrlmod', 2); ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr>
                <td width="100%" colspan="4" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?><br/><br/><input type="submit" value="<?php print $yyClkHer?>"><br /><br />&nbsp;</td>
			  </tr>
			</table>
			</form>
		  </td>
		</tr>
	  </table>
<script type="text/javascript">
setTimeout('document.getElementById("searchparamsform").submit()', 500);
</script>
<?php
}elseif(getget('id')!=''){
	if(getget('id')=='new')
		$idlist=array('0');
	elseif(getget('id')=='multi')
		$idlist=@$_POST['ids'];
	else
		$idlist=array(getget('id'));
	$numids=count($idlist);
	$numorders=0;
	if(! is_array($idlist)){
		print 'No id list specified<br>';
	}else foreach($idlist as $theid){
		$noloyaltypoints=FALSE;
		$numids--;
		$allorders='';
		$statetaxrate=0;
		$countrytaxrate=0;
		$hsttaxrate=0;
		$countryorder=0;
		if(getget('id')=='new'){
			$alldata['ordStatus']=3; $alldata['ordAuthStatus']=''; $alldata['ordStatusDate']=time(); $alldata['ordID']=''; $alldata['ordName']=''; $alldata['ordLastName']=''; $alldata['ordAddress']=''; $alldata['ordAddress2']=''; $alldata['ordCity']=''; $alldata['ordState']=''; $alldata['ordZip']=''; $alldata['ordCountry']=''; $alldata['ordEmail']=''; $alldata['ordPhone']=''; $alldata['ordShipName']=''; $alldata['ordShipLastName']=''; $alldata['ordShipAddress']=''; $alldata['ordShipAddress2']=''; $alldata['ordShipCity']=''; $alldata['ordShipState']=''; $alldata['ordShipZip']=''; $alldata['ordShipCountry']=''; $alldata['ordShipPhone']=''; $alldata['ordPayProvider']=0; $alldata['ordAuthNumber']='manual auth'; $alldata['ordTransID']=''; $alldata['ordTotal']=0; $alldata['ordDate']=time(); $alldata['ordStateTax']=0; $alldata['ordCountryTax']=0; $alldata['ordShipping']=0; $alldata['ordShipType']=''; $alldata['ordShipCarrier']=0; $alldata['ordIP']=getipaddress(); $alldata['ordAffiliate']=''; $alldata['ordDiscount']=0; $alldata['ordDiscountText']=''; $alldata['ordHandling']=0; $alldata['ordComLoc']=0; $alldata['ordExtra1']=''; $alldata['ordExtra2']=''; $alldata['ordShipExtra1']=''; $alldata['ordShipExtra2']=''; $alldata['ordCheckoutExtra1']=''; $alldata['ordCheckoutExtra2']=''; $alldata['ordHSTTax']=0; $alldata['ordTrackNum']=''; $alldata['ordInvoice']=''; $alldata['ordClientID']=0; $alldata['ordReferer']=''; $alldata['loyaltyPoints']=''; $alldata['ordAddInfo']=''; $alldata['ordStatusInfo']=''; $alldata['ordPrivateStatus']='';
		}else{
			if(! is_numeric($theid)) $theid=-1;
			if(@$viewordersort=='') $viewordersort='cartID';
			if($isprinter && @$packingslipsort!='') $viewordersort=$packingslipsort;
			if($isinvoice && @$invoicesort!='') $viewordersort=$invoicesort;
			$sSQL="SELECT cartProdId,cartProdName,cartProdPrice,cartQuantity,cartID,pStockByOpts,pExemptions,cartGiftWrap,cartGiftMessage FROM cart LEFT JOIN products on cart.cartProdID=products.pId WHERE cartOrderID=" . $theid . ' ORDER BY ' . $viewordersort;
			$allorders=ect_query($sSQL) or ect_error();
			$numorders=ect_num_rows($allorders);
			$sSQL="SELECT ordID,ordStatus,ordAuthStatus,ordStatusDate,ordName,ordLastName,ordAddress,ordAddress2,ordCity,ordState,ordZip,ordCountry,ordEmail,ordPhone,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipZip,ordShipCountry,ordShipPhone,ordPayProvider,ordAuthNumber,ordTransID,ordTotal,ordDate,ordStateTax,ordCountryTax,ordHSTTax,ordShipping,ordShipType,ordShipCarrier,ordIP,ordAffiliate,ordDiscount,ordHandling,ordDiscountText,ordComLoc,ordExtra1,ordExtra2,ordShipExtra1,ordShipExtra2,ordCheckoutExtra1,ordCheckoutExtra2,ordAddInfo,ordTrackNum,ordInvoice,ordClientID,ordReferer,ordQuerystr,loyaltyPoints,ordLang,ordStatusInfo,ordPrivateStatus FROM orders LEFT JOIN payprovider ON payprovider.payProvID=orders.ordPayProvider WHERE ordID='" . escape_string($theid) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($alldata=ect_fetch_assoc($result)){
				$alldata['ordDate']=strtotime($alldata['ordDate']);
				$alldata['ordStatusDate']=strtotime($alldata['ordStatusDate']);
			}else
				$alldata=array('ordID'=>0, 'ordStatus'=>0, 'ordAuthStatus'=>'', 'ordStatus'=>time(), 'ordName'=>'&nbsp;', 'ordLastName'=>'', 'ordAddress'=>'', 'ordAddress2'=>'', 'ordCity'=>'', 'ordState'=>'', 'ordZip'=>'', 'ordCountry'=>'', 'ordEmail'=>'', 'ordPhone'=>'', 'ordShipName'=>'', 'ordShipLastName'=>'', 'ordShipAddress'=>'', 'ordShipAddress2'=>'', 'ordShipCity'=>'', 'ordShipState'=>'', 'ordShipZip'=>'', 'ordShipCountry'=>'', 'ordShipPhone'=>'', 'ordPayProvider'=>0, 'ordAuthNumber'=>'', 'ordTransID'=>'', 'ordTotal'=>0, 'ordDate'=>0, 'ordStateTax'=>0, 'ordCountryTax'=>0, 'ordShipping'=>0, 'ordShipType'=>'', 'ordShipCarrier'=>0, 'ordIP'=>'', 'ordAffiliate'=>'', 'ordDiscount'=>0, 'ordDiscountText'=>'', 'ordHandling'=>0, 'ordComLoc'=>0, 'ordExtra1'=>'', 'ordExtra2'=>'', 'ordShipExtra1'=>'', 'ordShipExtra2'=>'', 'ordCheckoutExtra1'=>'', 'ordCheckoutExtra2'=>'', 'ordHSTTax'=>0, 'ordTrackNum'=>'', 'ordInvoice'=>'', 'ordClientID'=>0, 'ordReferer'=>'', 'ordQuerystr'=>'', 'loyaltyPoints'=>'', 'ordAddInfo'=>'', 'ordStatusInfo'=>'', 'ordPrivateStatus'=>'');
			ect_free_result($result);
			if((@$GLOBALS['loyaltypointsnowholesale'] || @$GLOBALS['loyaltypointsnopercentdiscount']) && $alldata['ordClientID']!=0){
				$sSQL="SELECT clActions FROM customerlogin WHERE clID=" . $alldata['ordClientID'];
				$result=ect_query($sSQL) or ect_error();
				if($rs=ect_fetch_assoc($result)){
					if(@$GLOBALS['loyaltypointsnowholesale'] && ($rs['clActions'] & 8)==8) $noloyaltypoints=TRUE;
					if(@$GLOBALS['loyaltypointsnopercentdiscount'] && ($rs['clActions'] & 16)==16) $noloyaltypoints=TRUE;
				}
				ect_free_result($result);
			}
			if(! $isprinter && ! $doedit){ // previous and next id
				$sSQL="SELECT ordID FROM orders WHERE ordID<".$theid." ORDER BY ordID DESC LIMIT 0,1";
				$result2=ect_query($sSQL) or ect_error();
				if($rs=ect_fetch_assoc($result2)) $previousid=$rs['ordID'];
				ect_free_result($result2);
				$sSQL="SELECT ordID FROM orders WHERE ordID>".$theid." ORDER BY ordID LIMIT 0,1";
				$result2=ect_query($sSQL) or ect_error();
				if($rs=ect_fetch_assoc($result2)) $nextid=$rs['ordID'];
				ect_free_result($result2);
			}
		}
		if($doedit){
			print '<form method="post" name="editform" id="editform" action="adminorders.php" onsubmit="return confirmedit()"><input type="hidden" name="orderid" value="' . getget('id') . '" /><input type="hidden" name="doedit" value="true" />';
			$overridecurrency=TRUE;
			$orcsymbol='';
			$orcdecplaces=2;
			$orcpreamount=TRUE;
			$orcdecimals=".";
			$orcthousands='';
		}
		if(! $isprinter){
?>
<script type="text/javascript">
/* <![CDATA[ */
var newwin="";
var plinecnt=0;
var numcartitems=<?php print $numorders?>;
function popupaddress(isship){
	var tntry=document.getElementById('ord'+isship+'country').innerHTML;
	document.getElementById('addresstextarea').value='';
	if(document.getElementById('ord'+isship+'extra1')&&document.getElementById('ord'+isship+'extra1').innerHTML!='')document.getElementById('addresstextarea').value+=document.getElementById('ord'+isship+'extra1').innerHTML + "\r\n";
	document.getElementById('addresstextarea').value+=document.getElementById('ord'+isship+'name').innerHTML + "\r\n" +
				document.getElementById('ord'+isship+'address').innerHTML + "\r\n";
	if(document.getElementById('ord'+isship+'address2')&&document.getElementById('ord'+isship+'address2').innerHTML!='')document.getElementById('addresstextarea').value+=document.getElementById('ord'+isship+'address2').innerHTML + "\r\n";
	if(document.getElementById('ord'+isship+'city').innerHTML!='')document.getElementById('addresstextarea').value+=document.getElementById('ord'+isship+'city').innerHTML + ", ";
	document.getElementById('addresstextarea').value+=document.getElementById('ord'+isship+'state').innerHTML + " " + document.getElementById('ord'+isship+'zip').innerHTML + ((tntry!='USA'&&tntry!='United States of America')<?php if($origCountryID!=1)print '||true'?>?"\r\n" + tntry:'');
	document.getElementById('addressdiv').style.display='block';
	document.getElementById('addresstextarea').select();
}
function openemailpopup(id){
  popupWin=window.open('popupemail.php?'+id,'emailpopup','menubar=no, scrollbars=no, width=300, height=250, directories=no,location=no,resizable=yes,status=no,toolbar=no')
}
function uaajaxcallback(){
	if(ajaxobj.readyState==4){
		var restxt=ajaxobj.responseText;
		resarr=restxt.split('==LISTELM==');
		if(resarr.length>0){
			document.getElementById("custid").value=resarr[0];
			document.getElementById("name").value=resarr[1];
<?php	if(@$usefirstlastname) print 'document.getElementById("lastname").value=resarr[2];' . "\r\n" ?>
		}
		if(resarr.length>5){
			document.getElementById("address").value=resarr[3];
<?php	if(@$useaddressline2) print 'document.getElementById("address2").value=resarr[4];' . "\r\n" ?>
			document.getElementById("city").value=resarr[5];
			document.getElementById("state").value=resarr[6];
			document.getElementById("zip").value=resarr[7];
			cntry=document.getElementById("country");
			cntxt=resarr[8];
			for(index=0; index<cntry.length; index++){
				if(cntry.options[index].text==cntxt||cntry.options[index].value==cntxt){
					cntry.selectedIndex=index;
				}
			}
			document.getElementById("phone").value=resarr[9];
<?php
	if(trim(@$extraorderfield1)!='') print 'document.getElementById("extra1").value=resarr[10];' . "\r\n";
	if(trim(@$extraorderfield2)!='') print 'document.getElementById("extra2").value=resarr[11];' . "\r\n" ?>
			setstatetax();
			setcountrytax();
		}
	}
}
function updateaddress(id){
	document.getElementById('percdisc').value=(adiscnts[id][1]=='1'?adiscnts[id][2]:'');
	document.getElementById('wholesaledisc').checked=(adiscnts[id][0]=='1');
	ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
	ajaxobj.onreadystatechange=uaajaxcallback;
	ajaxobj.open("POST", "ajaxservice.php?action=getlist&listtype=adddets", true);
	ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxobj.send('listtext='+adds[id]);
}
function upajaxcallback(){
	if(ajaxobj.readyState==4){
		var restxt=ajaxobj.responseText.replace(/^\s+|\s+$/g,"");
		resarr=restxt.split('==LISTELM==');
		document.getElementById('optionsspan'+resarr[0]).innerHTML=resarr[1];
		try{eval(resarr[2]);}catch(err){document.getElementById('optionsspan'+resarr[0]).innerHTML='javascript error'}
	}
}
function updateoptions(id){
	prodid=document.getElementById('prodid'+id).value;
	if(prodid!=''){
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange=upajaxcallback;
		ajaxobj.open("POST", 'ajaxservice.php?action=updateoptions&index='+id+'&wsp='+(document.getElementById('wholesaledisc').checked?'1':'0')+'&perc='+document.getElementById('percdisc').value.replace(/[^0-9\.]/g, ''), true);
		ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxobj.send('productid='+prodid);
	}
	return(false);
}
function extraproduct(plusminus){
var productspan=document.getElementById('productspan');
var thetable=document.getElementById('producttable');
if(plusminus=='+'){
numcartitems++;
newrow=thetable.insertRow(numcartitems);
newrow.className='cobll';
newcell=newrow.insertCell(0);
newcell.vAlign='top';
newcell.innerHTML='<input type="button" value="..." onclick="updateoptions('+(plinecnt+1000)+')" />&nbsp;<input name="prodid'+(plinecnt+1000)+'" size="18" id="prodid'+(plinecnt+1000)+'" AUTOCOMPLETE="off" onkeydown="return combokey(this,event)" onkeyup="combochange(this,event)" /><input type="hidden" id="stateexempt'+(plinecnt+1000)+'" value="false" /><input type="hidden" id="countryexempt'+(plinecnt+1000)+'" value="false" />';
newcell.innerHTML+= '<?php print str_replace("'","\\'",showgetoptionsselect('xxxx'))?>'.replace(/xxxx/,'selectprodid'+(plinecnt+1000));
newcell=newrow.insertCell(1);
newcell.vAlign='top';
newcell.innerHTML='<input type="text" id="prodname'+(plinecnt+1000)+'" name="prodname'+(plinecnt+1000)+'" size="24" AUTOCOMPLETE="off" onkeydown="return combokey(this,event)" onkeyup="combochange(this,event)" />';
newcell.innerHTML+= '<?php print str_replace("'","\\'",showgetoptionsselect('xxxx'))?>'.replace(/xxxx/,'selectprodname'+(plinecnt+1000));
newcell=newrow.insertCell(2);
newcell.innerHTML='<span id="optionsspan'+(plinecnt+1000)+'">-</span>';
newcell=newrow.insertCell(3);
newcell.vAlign='top';
newcell.innerHTML='<input type="text" id="quant'+(plinecnt+1000)+'" name="quant'+(plinecnt+1000)+'" size="5" value="1" />';
newcell=newrow.insertCell(4);
newcell.vAlign='top';
newcell.innerHTML='<input type="text" id="price'+(plinecnt+1000)+'" name="price'+(plinecnt+1000)+'" value="0" size="7" /><br /><input type="hidden" id="optdiffspan'+(plinecnt+1000)+'" value="0" />';
newcell=newrow.insertCell(5);
newcell.innerHTML='&nbsp;';
plinecnt++;
}else{
if(plinecnt>0){
thetable.deleteRow(numcartitems);
plinecnt--;
numcartitems--;
}
}
}
function confirmedit(){
<?php
	if($GLOBALS['useStockManagement']){ ?>
var stockwarn="The following items do not have sufficient stock\n\n";
var outstock=false;
var oostock=new Array();
var oostockqnt=new Array();
var inputs=document.forms['editform'].getElementsByTagName("input");
for(ceindex=0;ceindex<inputs.length;ceindex++){
	var thename=inputs[ceindex].name;
	if(thename.substr(0,5)=="quant"){
		var theid=thename.substr(5);
		delbutton=document.getElementById("del_"+theid);
		if(delbutton==null)
			isdeleted=false;
		else
			isdeleted=delbutton.checked;
		if(! isdeleted){
			var pid=document.getElementById("prodid"+theid).value;
			var stocklevel=stock['pid_' + pid];
			var quant=document.getElementById("quant"+theid).value
			if(typeof(stocklevel)=="undefined"){
				// Do nothing, pid not defined.
			}else if(stocklevel=="bo"){ // By Options
				for(var ii in document.forms.editform){
					var opttext="optn"+theid+"_";
					if(ii.substr(0,opttext.length)==opttext){
						theitem=document.getElementById(ii);
						if(document.getElementById('v'+ii)==null){
							thevalue=theitem[theitem.selectedIndex].value.split('|')[0];
							stocklevel=stock['oid_'+thevalue];
							if(typeof(oostockqnt['oid_'+thevalue])=="undefined")
								oostockqnt['oid_'+thevalue]=parseInt(quant);
							else
								oostockqnt['oid_'+thevalue]+=parseInt(quant);
							if(parseInt(stocklevel)<oostockqnt['oid_'+thevalue]){
								oostock['oid_'+thevalue]=document.getElementById("prodname"+theid).value + " (" + theitem[theitem.selectedIndex].text + ") : Required " + oostockqnt['oid_'+thevalue] + " available ";
							}
						}
					}
				}
			}else{
				if(typeof(oostockqnt['pid_' + pid])=="undefined")
					oostockqnt['pid_' + pid]=parseInt(quant);
				else
					oostockqnt['pid_' + pid]+=parseInt(quant);
				if(parseInt(stocklevel)<oostockqnt['pid_' + pid]){
					oostock['pid_' + pid]=document.getElementById("prodname"+theid).value + ": Required " + oostockqnt['pid_' + pid] + " available ";
				}
			}
		}
	}
}
for(var i in oostock){
	outstock=true;
	stockwarn+=oostock[i] + stock[i] + "\n";
}
if(outstock){
	if(! confirm(stockwarn+"\nPress \"OK\" to submit changes or cancel to adjust quantities\n"))
		return(false);
}
<?php
	} ?>
if(confirm("<?php print jscheck($yyChkRec)?>"))
	return(true);
return(false);
}
function calcshipping(){
	var txturl='shipservice.php?';
	var editformelems=document.getElementById('editform').elements;
	for(var iix=0; iix<editformelems.length;iix++){
		var ii=editformelems[iix].name;
		if(ii.substr(0,6)=='prodid'){
			var theid=ii.substr(6);
			txturl+=ii+"="+editformelems[iix].value+"&";
			var thequant=parseInt(document.getElementById("quant"+theid).value);
			if(isNaN(thequant)) thequant=0;
			txturl+="quant"+theid+"="+thequant+"&";
			for(var iix2=0; iix2<editformelems.length;iix2++){
				var ii2=editformelems[iix2].name;
				var opttext="optn"+theid+"_";
				if(ii2.substr(0,opttext.length)==opttext){
					theitem=document.getElementById(ii2);
					if(document.getElementById('v'+ii2)==null){
						thevalue=theitem[theitem.selectedIndex].value;
						txturl+="optn"+theid+"_"+iix2+"="+thevalue.split('|')[0]+"&";
					}
				}
			}
		}
	}
	var isship=(document.getElementById('sstate').value!=''&&document.getElementById('szip').value!=''?'s':'');
	var shipstate=encodeURIComponent(document.getElementById(isship+'state').value);
	var shipzip=encodeURIComponent(document.getElementById(isship+'zip').value);
	var shipcountry=encodeURIComponent(document.getElementById(isship+'country').value);
	var comloc=document.getElementById('commercialloc')[document.getElementById('commercialloc').selectedIndex].value;
	var popupWin=window.open(txturl+"action=admincalc&destzip="+shipzip+"&sc="+shipcountry+"&sta="+shipstate+"&cl="+comloc,'calcshipping','menubar=no, scrollbars=yes, width=500, height=400, directories=no,location=no,resizable=yes,status=no,toolbar=no');
}
var opttxtcharge=[];
function dorecalc(onlytotal){
var thetotal=0,totoptdiff=0,statetaxabletotal=0,countrytaxabletotal=0;
for(var zz=0; zz < document.forms.editform.length; zz++){
var iq=document.forms.editform[zz].name;
if(iq.substr(0,5)=="quant"){
	theid=iq.substr(5);
	totopts=0;
	delbutton=document.getElementById("del_"+theid);
	if(delbutton==null)
		isdeleted=false;
	else
		isdeleted=delbutton.checked;
	if(! isdeleted){
		var editformelems=document.getElementById('editform').elements;
        for(var iix=0; iix<editformelems.length;iix++){
			var ii=editformelems[iix].name;
			var opttext="optn"+theid+"_";
			if(ii.substr(0,opttext.length)==opttext){
				theitem=document.getElementById(ii);
				if(document.getElementById('v'+ii)==null){
					thevalue=theitem[theitem.selectedIndex].value;
					if(thevalue.indexOf('|')>0){
						totopts+=parseFloat(thevalue.substr(thevalue.indexOf('|')+1));
					}
				}else{
					optid=parseInt(ii.substr(opttext.length));
					if(opttxtcharge[optid]){
						if(opttxtcharge[optid]>0){
							totopts+=opttxtcharge[optid]*document.getElementById('v'+ii).value.length;
						}else if(document.getElementById('v'+ii).value.length>0){
							totopts+=Math.abs(opttxtcharge[optid]);
						}
					}
				}
			}
		}
		thequant=parseInt(document.getElementById(iq).value);
		if(isNaN(thequant)) thequant=0;
		theprice=parseFloat(document.getElementById("price"+theid).value);
		if(isNaN(theprice)) theprice=0;
		document.getElementById("optdiffspan"+theid).value=totopts;
		optdiff=parseFloat(document.getElementById("optdiffspan"+theid).value);
		if(isNaN(optdiff)) optdiff=0;
		thetotal+=thequant * (theprice + optdiff);
		if(!document.getElementById("stateexempt"+theid)||document.getElementById("stateexempt"+theid).value!='true')
			statetaxabletotal+=thequant * (theprice + optdiff);
		if(!document.getElementById("countryexempt"+theid)||document.getElementById("countryexempt"+theid).value!='true')
			countrytaxabletotal+=thequant * (theprice + optdiff);
		totoptdiff+=thequant * optdiff;
	}
}
}
document.getElementById("optdiffspan").innerHTML=totoptdiff.toFixed(2);
document.getElementById("ordtotal").value=thetotal.toFixed(2);
if(onlytotal==true) return;<?php
if($origCountryID==2) print "\r\nvar ssa=getshipstateabbrev();" ?>
statetaxrate=parseFloat(document.getElementById("staterate").value);
if(isNaN(statetaxrate)) statetaxrate=0;
var homecountrytaxrate=<?php print $homecountrytaxrate?>;
countrytaxrate=parseFloat(document.getElementById("countryrate").value);
if(isNaN(countrytaxrate)) countrytaxrate=0;
discount=parseFloat(document.getElementById("ordDiscount").value);
if(isNaN(discount)){
	discount=0;
	document.getElementById("ordDiscount").value=0;
}
statetaxtotal=(statetaxrate * Math.max(statetaxabletotal-discount,0)) / 100.0;
<?php if($showtaxinclusive==3){ ?>
countrytaxtotal=Math.round((countrytaxabletotal*100) / ((100+homecountrytaxrate)/homecountrytaxrate))/100.0;
thetotal-=countrytaxtotal;
document.getElementById("ordtotal").value=thetotal.toFixed(2);
if(countrytaxrate!=homecountrytaxrate)
	if(countrytaxrate!=0) countrytaxtotal=countrytaxtotal*(countrytaxrate/homecountrytaxrate); else countrytaxtotal=0;
<?php }else{ ?>
countrytaxtotal=(countrytaxrate * Math.max(countrytaxabletotal-discount,0)) / 100.0;
<?php } ?>
shipping=parseFloat(document.getElementById("ordShipping").value);
if(isNaN(shipping)){
	shipping=0;
	document.getElementById("ordShipping").value=0;
}
handling=parseFloat(document.getElementById("ordHandling").value);
if(isNaN(handling)){
	handling=0;
	document.getElementById("ordHandling").value=0;
}
<?php	if(@$taxShipping==2){ ?>
statetaxtotal+=(statetaxrate * shipping) / 100.0;
countrytaxtotal+=(countrytaxrate * shipping) / 100.0;
<?php	}
		if(@$taxHandling==2){ ?>
statetaxtotal+=(statetaxrate * handling) / 100.0;
countrytaxtotal+=(countrytaxrate * handling) / 100.0;
<?php	} ?>
var hsttax=0;
<?php	if($origCountryID==2){ ?>
	if(getshipcountry()=='canada'){
		if(ssa=="NB" || ssa=="NF" || ssa=="NS" || ssa=="ON" || ssa=="PE"){
			hsttax=statetaxtotal+countrytaxtotal;
			statetaxtotal=0;
			countrytaxtotal=0;
		}
	}
	document.getElementById("ordHSTTax").value=hsttax.toFixed(2);
<?php	} ?>
statetaxtotal=roundNumber(statetaxtotal,2);
countrytaxtotal=roundNumber(countrytaxtotal,2);
document.getElementById("ordStateTax").value=statetaxtotal.toFixed(2);
document.getElementById("ordCountryTax").value=countrytaxtotal.toFixed(2);
grandtotal=(thetotal + shipping + handling + statetaxtotal + countrytaxtotal + hsttax) - discount;
document.getElementById("grandtotalspan").innerHTML=grandtotal.toFixed(2);
<?php	if(@$loyaltypoints!=''){ ?>
	document.getElementById("loyaltyPoints").value=Math.round((thetotal.toFixed(2)-discount)*<?php print ($noloyaltypoints?0:$loyaltypoints)?>);
<?php	} ?>
}
function roundNumber(num, dec){
	var result=Math.round(Math.round(num * Math.pow(10, dec+1) ) / 10) / Math.pow(10,dec);
	return result;
}
function ppajaxcallback(){
	if(ajaxobj.readyState==4){
		document.getElementById("googleupdatespan").innerHTML=ajaxobj.responseText;
	}
}
function updategoogleorder(theprocessor,theact,ordid){
	if(confirm('Inform '+theprocessor+' of change to order id ' + ordid + "?")){
		document.getElementById("googleupdatespan").innerHTML='';
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange=ppajaxcallback;
		extraparams='';
		if(theact=='ship'){
			shipcar=document.getElementById("shipcarrier");
			if(shipcar!= null){
				trackno=document.getElementById("ordTrackNum").value
				if(trackno!='' && confirm('Include tracking and carrier info?')){
					extraparams='&carrier='+(shipcar.options[shipcar.selectedIndex].value)+'&trackno='+document.getElementById("ordTrackNum").value;
				}
			}
		}
		if(document.getElementById("txamount")){
			extraparams+='&amount='+document.getElementById("txamount").value;
		}
		document.getElementById("googleupdatespan").innerHTML='Connecting...';
		ajaxobj.open("GET", "ajaxservice.php?processor="+theprocessor+"&gid="+ordid+"&act="+theact+extraparams, true);
		ajaxobj.send(null);
	}
}
function updatepaypalorder(theprocessor,ordid){
	if(confirm('Inform '+theprocessor+' of change to order id ' + ordid + "?")){
		document.getElementById("googleupdatespan").innerHTML='';
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange=ppajaxcallback;
		var additionalcapture=document.getElementById("additionalcapture")[document.getElementById("additionalcapture").selectedIndex].value;
		var theact=document.getElementById("paypalaction")[document.getElementById("paypalaction").selectedIndex].value;
		document.getElementById("googleupdatespan").innerHTML='Connecting...';
		postdata="additionalcapture=" + additionalcapture + "&amount=" + encodeURIComponent(document.getElementById("captureamount").value) + "&comments=" + encodeURIComponent(document.getElementById("buyernote").value)
		ajaxobj.open("POST", "ajaxservice.php?processor="+theprocessor+"&gid="+ordid+"&act="+theact, true);
		ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxobj.send(postdata);
	}
}
function setpaypalelements(){
	var theact=document.getElementById("paypalaction")[document.getElementById("paypalaction").selectedIndex].value;
	if(theact=='void'){
		document.getElementById("captureamount").disabled=true;
		document.getElementById("additionalcapture").disabled=true;
	}else if(theact=='reauth'){
		document.getElementById("captureamount").disabled=false;
		document.getElementById("additionalcapture").disabled=true;
	}else{
		document.getElementById("captureamount").disabled=false;
		document.getElementById("additionalcapture").disabled=false;
	}
}
function copybillingtoshipping(){
<?php	if(trim(@$extraorderfield1)!='') print 'document.getElementById("shipextra1").value=document.getElementById("extra1").value;' ?>
	document.getElementById("sname").value=document.getElementById("name").value;
<?php	if(@$usefirstlastname) print 'document.getElementById("slastname").value=document.getElementById("lastname").value;' ?>
	document.getElementById("saddress").value=document.getElementById("address").value;
<?php	if(@$useaddressline2==TRUE) print 'document.getElementById("saddress2").value=document.getElementById("address2").value;' ?>
	document.getElementById("scity").value=document.getElementById("city").value;
	document.getElementById("sstate").value=document.getElementById("state").value;
	document.getElementById("szip").value=document.getElementById("zip").value;
	document.getElementById("scountry").selectedIndex=document.getElementById("country").selectedIndex;
	document.getElementById("sphone").value=document.getElementById("phone").value;
<?php	if(trim(@$extraorderfield2)!='') print 'document.getElementById("shipextra2").value=document.getElementById("extra2").value;' ?>
}
<?php		if($doedit){ ?>
function setCookie(c_name,value,expiredays){
	var exdate=new Date();
	exdate.setDate(exdate.getDate()+expiredays);
	document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}
var adds=[];
var opensels=[];
var adiscnts=[];
document.getElementById('main').onclick=function(){
	for(var ii=0; ii<opensels.length; ii++)
		document.getElementById(opensels[ii]).style.display='none';
};
function addopensel(id){
	for(var ii=0; ii<opensels.length; ii++)
		if(id==opensels[ii]) return;
	opensels.push(id);
}
function plajaxcallback(){
	if(ajaxobj.readyState==4){
		var resarr=ajaxobj.responseText.replace(/^\s+|\s+$/g,"").split('==LISTOBJ==');
		var index,isname=false;
		oSelect=document.getElementById(resarr[0]);
		var act=resarr[0].replace(/\d/g,'');
		for(index=0; index<resarr.length-2; index++){
			var splitelem=resarr[index+1].split('==LISTELM==');
			var val1=splitelem[0];
			var val2=splitelem[1];
			var haswsdisc=0,hasperdisc=0,perdisc=0;
			if(splitelem.length>=2) adds[index]=splitelem[2];
			if(splitelem.length>=5) haswsdisc=splitelem[3];
			if(splitelem.length>=5) hasperdisc=splitelem[4];
			if(splitelem.length>=5) perdisc=splitelem[5];
			adiscnts[index]=new Array(haswsdisc,hasperdisc,perdisc);
			if(index<oSelect.length)
				var y=oSelect.options[index];
			else
				var y=document.createElement('option');
			if(act=='selectprodname'){
				y.text=val2;
				y.value=val1;
			}else if(act=='selectemail'){
				y.text=val2;
				y.title=val2;
				y.value=val1;
			}else{
				y.text=val1;
				y.value=val1;
			}
			if(y.text=='----------------') y.disabled=true; else y.disabled=false;
			if(index>=oSelect.length){
				try{oSelect.add(y, null);} // FF etc
				catch(ex){oSelect.add(y);} // IE
			}
		}
		if(oSelect){
			for(var ii=oSelect.length;ii>=index;ii--){
				oSelect.remove(ii);
			}
		}
	}
}
var gsid;
var gltyp;
var gtxt;
var tmrid;
function populatelist(){
	var objid=gsid;
	var listtype=gltyp;
	var stext=gtxt;
	ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
	ajaxobj.onreadystatechange=plajaxcallback;
	ajaxobj.open("POST", "ajaxservice.php?action=getlist&objid="+objid+"&listtype="+listtype, true);
	ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxobj.send('listtext='+stext);
}
function combochange(oText,e){
	if(document.getElementById("autocomplete").checked==false)
		return;
	keyCode=e.keyCode;
	if(keyCode<32&&keyCode!=8)return true;
	oSelect=document.getElementById('select'+oText.id);
	addopensel(oSelect.id);
	oSelect.style.display='';
	toFind=oText.value.toLowerCase();
	gsid=oSelect.id;
	gltyp=oText.id.replace(/\d/g,'');
	gtxt=toFind;
	clearTimeout(tmrid);
	tmrid=setTimeout("populatelist()",800);
}
function writedbg(msg){
	document.getElementById("debugdiv").innerHTML+=msg.replace(/</g,'&lt;').replace(/\r\n/g,'<br>')+"<br />";
}
function combokey(oText,e){
	if(document.getElementById("autocomplete").checked==false)
		return
	oSelect=document.getElementById('select'+oText.id);
	keyCode=e.keyCode;
	if(keyCode==40 || keyCode==38){ // Up / down arrows
		addopensel(oSelect.id);
		oSelect.style.display='';
		oSelect.focus();
		comboselect_onchange(oSelect);
	}
	else if(keyCode==13){
		oSelect.style.display='none';
		oText.focus();
		updateoptions(oText.id.replace(/prodid|prodname/,''));
		return getvalsfromserver(oSelect);
	}
	return true;
}
function getvalsfromserver(oSelect){
	var act=oSelect.id.replace(/\d/g,'');
	oText=document.getElementById(oSelect.id.replace('select',''));
	if(oSelect.selectedIndex!=-1){
		if(act=='selectprodname'){
			oText.value=oSelect.options[oSelect.selectedIndex].text;
			document.getElementById(oText.id.replace('prodname','prodid')).value=oSelect.options[oSelect.selectedIndex].value;
		}else
			oText.value=oSelect.options[oSelect.selectedIndex].value;
		oSelect.style.display='none';
		oText.focus();
		if(act=='selectemail')
			updateaddress(oSelect.selectedIndex);
		else
			updateoptions(oText.id.replace(/prodid|prodname/,''));
	}
	return false;
}
function comboselect_onclick(oSelect){
	return(getvalsfromserver(oSelect));
}
function comboselect_onchange(oSelect){
	oText=document.getElementById(oSelect.id.replace('select',''));
	if(oSelect.selectedIndex!=-1){
		if(oText.id.indexOf('prodname')!=-1)
			oText.value=oSelect.options[oSelect.selectedIndex].text;
		else
			oText.value=oSelect.options[oSelect.selectedIndex].value;
	}
}
function comboselect_onkeyup(keyCode,oSelect){
	if(keyCode==13){
		getvalsfromserver(oSelect);
	}
	return(false);
}
var countrytaxrates=[];
var statetaxrates=[];
var stateabbrevs=[];
<?php
	$sSQL="SELECT stateName,stateAbbrev,stateTax,stateCountryID FROM states WHERE stateTax<>0";
	$result=ect_query($sSQL) or ect_error();
	while($rs2=ect_fetch_assoc($result)){
		print 'statetaxrates["'.strtolower($rs2['stateName']).'"]='.$rs2['stateTax'].";\r\n";
		if($rs2['stateCountryID']==1 || $rs2['stateCountryID']==2){
			print 'statetaxrates["'.strtolower($rs2['stateAbbrev']).'"]='.$rs2['stateTax'].";\r\n";
			print 'stateabbrevs["'.strtolower($rs2['stateName']).'"]="'.$rs2['stateAbbrev']."\";\r\n";
		}
	}
	ect_free_result($result);
	$sSQL='SELECT countryName,countryTax FROM countries WHERE countryTax<>0';
	$result=ect_query($sSQL) or ect_error();
	while($rs2=ect_fetch_assoc($result)){
		print 'countrytaxrates["'.strtolower($rs2['countryName']).'"]='.$rs2['countryTax'].";\r\n";
	}
	ect_free_result($result); ?>
function setstatetax(){
	var addans='';
	if(document.getElementById('saddress').value!='') addans='s';
	var rgnname=document.getElementById(addans+'state').value.toLowerCase();
	if(statetaxrates[rgnname]) statetaxrate=parseFloat(statetaxrates[rgnname]); else statetaxrate=0;
	document.getElementById("staterate").value=statetaxrate;
}
function setcountrytax(){
	var addans='';
	if(document.getElementById('saddress').value!='') addans='s';
	var tobj=document.getElementById(addans+'country');
	var rgnname=tobj.options[tobj.selectedIndex].value.toLowerCase();
	if(countrytaxrates[rgnname]) countrytaxrate=parseFloat(countrytaxrates[rgnname]); else countrytaxrate=0;
	document.getElementById("countryrate").value=countrytaxrate;
}
function getshipstateabbrev(){
	var addans='';
	if(document.getElementById('saddress').value!='') addans='s';
	var rgnname=document.getElementById(addans+'state').value.toLowerCase();
	if(stateabbrevs[rgnname]){
		document.getElementById('staterate').value=statetaxrates[rgnname];
		return(stateabbrevs[rgnname]);
	}else
		return document.getElementById(addans+'state').value;
}
function getshipcountry(){
	var addans='';
	if(document.getElementById('saddress').value!='') addans='s';
	var tobj=document.getElementById(addans+'country');
	return(tobj.options[tobj.selectedIndex].value.toLowerCase());
}
<?php		} ?>
/* ]]> */
</script>
<?php		if(!$doedit){ ?>
<div id="addressdiv" onclick="this.style.display='none'" style="display:none;position:absolute;width:100%;height:2000px;background-image:url(adminimages/opaquepixel.png);top:0px;left:0px;text-align:center;z-index:10000;">
<br /><br /><br /><br /><br /><br /><br /><br />
<textarea id="addresstextarea" rows="10" cols="40" onclick="return false"></textarea>
</div>
<?php		}
		} // ! $isprinter ?>
<table border="0" cellspacing="0" cellpadding="0" width="100%" align="center" <?php if($numids>0) print 'style="page-break-after: always"'?>>
  <tr>
	<td width="100%">
	  <table width="100%" border="0" cellspacing="0" cellpadding="2">
<?php	if($isprinter && ! @isset($packingslipheader)) $packingslipheader=@$invoiceheader;
		if($isinvoice && @$invoiceheader!=''){ ?>
		<tr><td width="100%" colspan="2"><?php print $invoiceheader?></td></tr>
<?php	}elseif($isprinter && @$packingslipheader!=''){ ?>
		<tr><td width="100%" colspan="2"><?php print $packingslipheader?></td></tr>
<?php	} ?>
		<tr><td width="100%" colspan="2" align="center">
		  <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td align="left" width="30%">&nbsp; <?php
		if($doedit) print '&nbsp;<input type="checkbox" value="ON" name="autocomplete" id="autocomplete" onclick="setCookie(\'ectautocomp\',this.checked?1:0,600)" '.(@$_COOKIE['ectautocomp']=="1"?'checked="checked" ':'').'/> <strong>'.$yyUsAuCo.'</strong>';
		if(! $isprinter && ! $doedit){
			if(@$previousid!='') print '<input style="width:100px" type="button" value="&laquo; '.$yyPrev.'" onclick="document.location=\'adminorders.php?id='.$previousid.'\'" />';
		} ?>
			</td><td align="center"><strong><?php
		print $xxOrdNum . ' ' . (getget('id')=='new'?'('.$yyNewOrd.')':$alldata['ordID']);
		if($doedit && $adminlanguages>0){
			print ' - Language ID: <select size="1" name="ordlang">';
			for($index=0;$index<=$adminlanguages;$index++){
				print '<option value="'.$index.'"'.($index==$alldata['ordLang']?' selected="selected"':'').'>'.($index+1).'</option>';
			}
			print '</select>';
		}
		print '<br /><br />';
		if(@$fordertimeformatstr!=''){
			setlocale(LC_TIME, $adminLocale);
			print strftime($fordertimeformatstr, $alldata['ordDate']);
		}else
			print date($dateformatstr, $alldata['ordDate']) . ' ' . date('H:i', $alldata['ordDate']);
		?></strong></td><td align="right" width="30%">
<?php	if(! $isprinter && ! $doedit){
			if(@$nextid!='') print '<input style="width:100px" type="button" value="'.$yyNext.' &raquo;" onclick="document.location=\'adminorders.php?id='.$nextid.'\'" />';
		} ?>
			&nbsp;</td></tr></table>
		</td></tr>
<?php	if($isprinter && ! @isset($packingslipaddress)) $packingslipaddress=@$invoiceaddress;
		if($isinvoice && @$invoiceaddress!=''){ ?>
		<tr><td width="100%" colspan="2"><?php print $invoiceaddress?></td></tr>
<?php	}elseif($isprinter && @$packingslipaddress!=''){ ?>
		<tr><td width="100%" colspan="2"><?php print $packingslipaddress?></td></tr>
<?php	} ?>
		<tr>
		  <td width="50%" valign="top">
			<input type="hidden" name="custid" id="custid" value="" />
			<table class="ordtbl" width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td>
				  <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
					<tr>
					  <td width="100%" align="center" colspan="2"><strong><?php if(! ($isprinter || $doedit)) print '<input type="button" value="'.$yyBilDet.'" onclick="popupaddress(\'\')" />'; else print $yyBilDet.'.'?></strong></td>
					</tr>
<?php	if(trim(@$extraorderfield1)!='' && (! $isprinter || trim($alldata['ordExtra1'])!='')){ ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $extraorderfield1 ?>:</strong></td>
					  <td align="<?php print $tleft?>" id="ordextra1"><?php print editfunc($alldata['ordExtra1'],'extra1',25)?></td>
					</tr>
<?php	} ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $yyName?>:</strong></td>
					  <td align="<?php print $tleft?>" id="ordname"><?php if(@$usefirstlastname) print editfunc($alldata['ordName'],'name',11).' '.editfunc($alldata['ordLastName'],'lastname',11); else print editfunc($alldata['ordName'],'name',25)?></td>
					</tr>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $xxAddress?>:</strong></td>
					  <td align="<?php print $tleft?>" id="ordaddress"><?php print editfunc($alldata['ordAddress'],'address',25)?></td>
					</tr>
<?php	if(($doedit && @$useaddressline2) || trim($alldata['ordAddress2'])!=''){ ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print ($isprinter?'&nbsp;':$xxAddress2.':')?></strong></td>
					  <td align="<?php print $tleft?>" id="ordaddress2"><?php print editfunc($alldata['ordAddress2'],'address2',25)?></td>
					</tr>
<?php	}
		if($isprinter){ ?>
					<tr>
					  <td>&nbsp;</td>
					  <td align="<?php print $tleft?>"><?php print $alldata['ordCity'].(trim($alldata['ordCity'])!='' && trim($alldata['ordState'])!='' ? ', ' : '').$alldata['ordState']?></td>
					</tr>
<?php	}else{ ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $xxCity?>:</strong></td>
					  <td align="<?php print $tleft?>" id="ordcity"><?php print editfunc($alldata['ordCity'],'city',25)?></td>
					</tr>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $xxAllSta?>:</strong></td>
					  <td align="<?php print $tleft?>" id="ordstate"><?php print editspecial($alldata['ordState'],'state',25,'onblur="setstatetax()"')?></td>
					</tr>
<?php	} ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print ($isprinter?'&nbsp;':$xxZip.':')?></strong></td>
					  <td align="<?php print $tleft?>" id="ordzip"><?php print editfunc($alldata['ordZip'],'zip',15)?></td>
					</tr>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print ($isprinter?'&nbsp;':$xxCountry.':')?></strong></td>
					  <td align="<?php print $tleft?>" id="ordcountry"><?php
		if($doedit){
			$foundmatch=FALSE;
			print '<select name="country" id="country" size="1" onchange="setcountrytax()">';
			$sSQL="SELECT countryName,countryTax,countryOrder FROM countries ORDER BY countryOrder DESC, countryName";
			$result=ect_query($sSQL) or ect_error();
			while($rs2=ect_fetch_assoc($result)){
				print '<option value="' . htmlspecials($rs2['countryName']) . '"';
				if($alldata['ordCountry']==$rs2['countryName'] || (getget('id')=='new' && ! $foundmatch)){
					print ' selected="selected"';
					$foundmatch=TRUE;
					$countrytaxrate=$rs2['countryTax'];
					$countryorder=$rs2['countryOrder'];
				}
				print '>' . $rs2['countryName'] . "</option>\r\n";			}
			ect_free_result($result);
			if(! $foundmatch) print '<option value="' . htmlspecials($alldata['ordCountry']) . '" selected="selected">' . $alldata['ordCountry'] . "</option>\r\n";
			print '</select>';
			if($countryorder==2){
				$sSQL="SELECT stateTax FROM states WHERE stateName='" . escape_string($alldata['ordState']) . "' OR stateAbbrev='" . escape_string($alldata['ordState']) . "'";
				$result=ect_query($sSQL) or ect_error();
				if($rs2=ect_fetch_assoc($result))
					$statetaxrate=$rs2['stateTax'];
				ect_free_result($result);
			}
		}else
			print $alldata['ordCountry'];?></td>
					</tr>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $xxPhone?>:</strong></td>
					  <td align="<?php print $tleft?>"><?php print editfunc($alldata['ordPhone'],'phone',25)?></td>
					</tr>
<?php	if(trim(@$extraorderfield2)!='' && (! $isprinter || trim($alldata['ordExtra2'])!='')){ ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print @$extraorderfield2 ?>:</strong></td>
					  <td align="<?php print $tleft?>" id="ordextra2"><?php print editfunc($alldata['ordExtra2'],'extra2',25)?></td>
					</tr>
<?php	} ?>
				  </table>
				</td>
			  </tr>
			</table>
		  </td>
		  <td width="50%">
<?php	if(trim($alldata['ordShipName'])!='' || trim($alldata['ordShipAddress'])!='' || trim($alldata['ordShipCity'])!='' || trim($alldata['ordShipExtra1'])!='' || $doedit){ ?>
			<table class="ordtbl" width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td>
				  <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
					<tr>
					  <td width="100%" align="center" colspan="2"><strong><?php if(! ($isprinter || $doedit)) print '<input type="button" value="'.$xxShpDet.'" onclick="popupaddress(\'s\')" />'; else print $xxShpDet.'.'?><?php if($doedit) print ' &raquo; <a href="#" onclick="copybillingtoshipping(); return(false);"><strong>'.$yyCopBil.'</strong></a>'?></strong></td>
					</tr>
<?php		if(trim(@$extraorderfield1)!='' && (! $isprinter || trim($alldata['ordShipExtra1'])!='')){ ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print @$extraorderfield1 ?>:</strong></td>
					  <td align="<?php print $tleft?>" id="ordsextra1"><?php print editfunc($alldata['ordShipExtra1'],'shipextra1',25)?></td>
					</tr>
<?php		} ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $yyName?>:</strong></td>
					  <td align="<?php print $tleft?>" id="ordsname"><?php if(@$usefirstlastname) print editfunc($alldata['ordShipName'],'sname',11).' '.editfunc($alldata['ordShipLastName'],'slastname',11); else print editfunc($alldata['ordShipName'],'sname',25)?></td>
					</tr>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $xxAddress?>:</strong></td>
					  <td align="<?php print $tleft?>" id="ordsaddress"><?php print editspecial($alldata['ordShipAddress'],'saddress',25,'onblur="setstatetax();setcountrytax();"')?></td>
					</tr>
<?php		if(($doedit && @$useaddressline2) || trim($alldata['ordShipAddress2'])!=''){ ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print ($isprinter?'&nbsp;':$xxAddress2.':')?></strong></td>
					  <td align="<?php print $tleft?>" id="ordsaddress2"><?php print editfunc($alldata['ordShipAddress2'],'saddress2',25)?></td>
					</tr>
<?php		}
			if($isprinter){ ?>
					<tr>
					  <td>&nbsp;</td>
					  <td align="<?php print $tleft?>"><?php print $alldata['ordShipCity'].(trim($alldata['ordShipCity'])!='' && trim($alldata['ordShipState'])!='' ? ', ' : '').$alldata['ordShipState']?></td>
					</tr>
<?php		}else{ ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $xxCity?>:</strong></td>
					  <td align="<?php print $tleft?>" id="ordscity"><?php print editfunc($alldata['ordShipCity'],'scity',25)?></td>
					</tr>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $xxAllSta?>:</strong></td>
					  <td align="<?php print $tleft?>" id="ordsstate"><?php print editspecial($alldata['ordShipState'],'sstate',25,'onblur="setstatetax()"')?></td>
					</tr>
<?php		} ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print ($isprinter?'&nbsp;':$xxZip.':')?></strong></td>
					  <td align="<?php print $tleft?>" id="ordszip"><?php print editfunc($alldata['ordShipZip'],'szip',15)?></td>
					</tr>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print ($isprinter?'&nbsp;':$xxCountry.':')?></strong></td>
					  <td align="<?php print $tleft?>" id="ordscountry"><?php
			if($doedit){
				if(trim($alldata['ordShipName'])!='' || trim($alldata['ordShipAddress'])!='') $usingshipcountry=TRUE; else $usingshipcountry=FALSE;
				$foundmatch=(getget('id')=='new');
				print '<select name="scountry" id="scountry" size="1" onchange="setcountrytax()">';
				$sSQL="SELECT countryName,countryTax,countryOrder FROM countries ORDER BY countryOrder DESC, countryName";
				$result=ect_query($sSQL) or ect_error();
				while($rs2=ect_fetch_assoc($result)){
					print '<option value="' . htmlspecials($rs2['countryName']) . '"';
					if($alldata['ordShipCountry']==$rs2['countryName']){
						print ' selected="selected"';
						$foundmatch=TRUE;
						if($usingshipcountry) $countrytaxrate=$rs2['countryTax'];
						$countryorder=$rs2['countryOrder'];
					}
					print '>' . $rs2['countryName'] . "</option>\r\n";			}
				ect_free_result($result);
				if(! $foundmatch) print '<option value="' . htmlspecials($alldata['ordShipCountry']) . '" selected="selected">' . $alldata['ordShipCountry'] . "</option>\r\n";
				print '</select>';
				if($countryorder==2 && $usingshipcountry){
					$sSQL="SELECT stateTax FROM states WHERE stateName='" . escape_string($alldata['ordShipState']) . "' OR stateAbbrev='" . escape_string($alldata['ordShipState']) . "'";
					$result=ect_query($sSQL) or ect_error();
					if($rs2=ect_fetch_assoc($result))
						$statetaxrate=$rs2['stateTax'];
					ect_free_result($result);
				}
			}else
				print $alldata['ordShipCountry']?></td>
					</tr>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $xxPhone?>:</strong></td>
					  <td align="<?php print $tleft?>"><?php print editfunc($alldata['ordShipPhone'],'sphone',25)?></td>
					</tr>
<?php		if(trim(@$extraorderfield2)!='' && (! $isprinter || trim($alldata['ordShipExtra2'])!='')){ ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $extraorderfield2 ?>:</strong></td>
					  <td align="<?php print $tleft?>" id="ordsextra2"><?php print editfunc($alldata['ordShipExtra2'],'shipextra2',25)?></td>
					</tr>
<?php		} ?>
				  </table>
				</td>
			  </tr>
			</table>
<?php	}else{
			print '&nbsp;';
		} ?>
		  </td>
		</tr>
		<tr>
		  <td colspan="2">
			<table class="ordtbl" width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td>
				  <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
					<tr><td colspan="4" align="center"><strong><?php print $yyAddDet?>.</strong></td></tr>
					<tr>
					  <td align="<?php print $tright?>"><?php if(! $isprinter && $alldata['ordAuthNumber']!='' && ! $doedit) print '<input type="button" value="Resend" onclick="openemailpopup(\'id=' . $alldata['ordID'] . '\')" />' ?>
					  <strong><?php print $xxEmail?>:</strong></td>
					  <td align="<?php print $tleft?>"><?php
		if($isprinter || $doedit) print editspecial($alldata['ordEmail'],'email',35,'AUTOCOMPLETE="off" onkeydown="return combokey(this,event)" onkeyup="combochange(this,event)"'); else print '<a href="mailto:' . htmlspecials($alldata['ordEmail']) . '">' . htmlspecials($alldata['ordEmail']) . '</a>';
		if($doedit) print showgetoptionsselect('selectemail'); ?></td>
					</tr>
<?php	if(trim(@$extracheckoutfield1)!=''){
		$checkoutfield1='<strong>' . $extracheckoutfield1 . '</strong>';
		$checkoutfield2=editfunc($alldata['ordCheckoutExtra1'],'checkoutextra1',25)
?>					<tr>
					  <td align="<?php print $tright?>"><?php if(@$extracheckoutfield1reverse) print $checkoutfield2; else print $checkoutfield1 . '<strong>:</strong>' ?></td>
					  <td align="<?php print $tleft?>"><?php if(@$extracheckoutfield1reverse) print $checkoutfield1; else print $checkoutfield2 ?></td>
					</tr>
<?php	}
		if(trim(@$extracheckoutfield2)!=''){
			$checkoutfield1='<strong>' . $extracheckoutfield2 . '</strong>';
			$checkoutfield2=editfunc($alldata['ordCheckoutExtra2'],'checkoutextra2',25)
?>					<tr>
					  <td align="<?php print $tright?>"><?php if(@$extracheckoutfield2reverse) print $checkoutfield2; else print $checkoutfield1 . '<strong>:</strong>' ?></td>
					  <td align="<?php print $tleft?>" colspan="3"><?php if(@$extracheckoutfield2reverse) print $checkoutfield1; else print $checkoutfield2 ?></td>
					</tr>
<?php	}
		if(! $isprinter){ ?>
					<tr>
					  <td align="right"><strong><?php print $yyIPAdd?>:</strong></td>
					  <td align="left"><?php if($doedit) print editfunc($alldata['ordIP'],'ipaddress',15); else print '<a href="http://www.infosniper.net/index.php?lang=1&ip_address='.urlencode($alldata['ordIP']).'" target="_blank">'.htmlspecials($alldata['ordIP']).'</a>'?></td>
					  <td align="right"><strong><?php print $yyAffili?>:</strong></td>
					  <td align="left"><?php print editfunc($alldata['ordAffiliate'],'PARTNER',15)?></td>
					</tr>
<?php	}
		if((trim($alldata['ordDiscountText'])!='' && (! $isprinter || $isinvoice)) || $doedit){ ?>
					<tr>
					  <td align="right" valign="top"><strong><?php print $xxAppDs?>:</strong></td>
					  <td align="left" colspan="3"><?php if($doedit) print '<textarea name="discounttext" cols="50" rows="2">' . str_replace(array('<br />','<'), array("\r\n",'&lt;'), $alldata['ordDiscountText']) . '</textarea>'; else print str_replace("\r\n",'<br />',htmlspecials(str_replace('<br />',"\r\n",$alldata['ordDiscountText']))); ?></td>
					</tr>
<?php	}
		if(! $isprinter){
			$sSQL="SELECT gcaGCID,gcaAmount FROM giftcertsapplied WHERE gcaOrdID=".$theid;
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result))
				print '<tr><td align="right"><strong>' . $yyCerNum . '</strong></td><td>' . $rs['gcaGCID'] . ' ' . FormatEuroCurrency($rs['gcaAmount']) . ' ' . '<a href="admingiftcert.php?id=' . $rs['gcaGCID'] . '">' . $yyClkVw . '</a></td></tr>';
			ect_free_result($result);
		}
		if(! $isprinter && ! $doedit) print '<form method="post" action="adminorders.php"><input type="hidden" name="updatestatus" value="1" /><input type="hidden" name="orderid" value="' . getget('id') . '" />';
		if($alldata['ordShipCarrier']!=0 || $alldata['ordShipType']!='' || $doedit){ ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $xxShpMet?>:</strong></td>
					  <td align="<?php print $tleft?>"><?php	if(! $isprinter){ ?>
							<select name="shipcarrier" id="shipcarrier" size="1">
							<option value="<?php print $alldata['ordShipCarrier']?>"><?php print $yyOther?></option>
							<option value="3"<?php if((int)$alldata['ordShipCarrier']==3) print ' selected="selected"'?>>USPS</option>
							<option value="4"<?php if((int)$alldata['ordShipCarrier']==4) print ' selected="selected"'?>>UPS</option>
							<option value="6"<?php if((int)$alldata['ordShipCarrier']==6) print ' selected="selected"'?>>CanPos</option>
							<option value="7"<?php if((int)$alldata['ordShipCarrier']==7) print ' selected="selected"'?>>FedEx</option>
							<option value="8"<?php if((int)$alldata['ordShipCarrier']==8) print ' selected="selected"'?>>FedEx SmartPost</option>
							<option value="9"<?php if((int)$alldata['ordShipCarrier']==9) print ' selected="selected"'?>>DHL</option>
							<option value="10"<?php if((int)$alldata['ordShipCarrier']==10) print ' selected="selected"'?>>Australia Post</option>
							</select> <?php		}
												print editfunc($alldata['ordShipType']=='MODWARNOPEN'?$yyMoWarn:$alldata['ordShipType'],'shipmethod',25); ?></td>
					  <td align="<?php print $tright?>"><strong><?php if($doedit) print $xxCLoc . ':'?></strong></td>
					  <td align="<?php print $tleft?>"><?php	if($doedit){
													print '<select name="commercialloc" id="commercialloc" size="1">';
													print '<option value="N">' . $yyNo . '</option>';
													print '<option value="Y"' . (($alldata['ordComLoc']&1)==1 ? ' selected="selected"' : '') . '>' . $yyYes . '</option>';
													print '</select>';
												}?></td>
					</tr>
<?php		if($doedit){ ?>
					<tr>
					  <td align="right"><strong><?php print $xxShpIns?>:</strong></td>
					  <td align="left"><?php	print '<select name="wantinsurance" size="1">';
												print '<option value="N">' . $yyNo . '</option>';
												print '<option value="Y"' . (($alldata['ordComLoc'] & 2)==2 ? ' selected="selected"' : '') . '>' . $yyYes . '</option>';
												print '</select>'; ?></td>
					  <td align="right"><strong><?php print $xxSatDe2?>:</strong></td>
					  <td align="left"><?php	print '<select name="saturdaydelivery" size="1">';
												print '<option value="N">' . $yyNo . '</option>';
												print '<option value="Y"' . (($alldata['ordComLoc'] & 4)==4 ? ' selected="selected"' : '') . '>' . $yyYes . '</option>';
												print '</select>' ?></td>
					</tr>
					<tr>
					  <td align="right"><strong><?php print $xxSigRe2?>:</strong></td>
					  <td align="left"><?php	print '<select name="signaturerelease" size="1">';
												print '<option value="N">' . $yyNo . '</option>';
												print '<option value="Y"' . (($alldata['ordComLoc'] & 8)==8 ? ' selected="selected"' : '') . '>' . $yyYes . '</option>';
												print '</select>' ?></td>
					  <td align="right"><strong><?php print $xxInsDe2?>:</strong></td>
					  <td align="left"><?php	print '<select name="insidedelivery" size="1">';
												print '<option value="N">' . $yyNo . '</option>';
												print '<option value="Y"' . (($alldata['ordComLoc'] & 16)==16 ? ' selected="selected"' : '') . '>' . $yyYes . '</option>';
												print '</select>' ?></td>
					</tr>
<?php		}elseif($alldata['ordComLoc'] > 0){
				if($isprinter) $thestyle=''; else $thestyle=' style="color:#FF0000"';
				$shipopts='<strong>Shipping options:</strong>';
				if(($alldata['ordComLoc'] & 1)==1){ print '<tr><td align="right">' . $shipopts.'</td><td align="left" colspan="3"'.$thestyle.'>' . $xxCerCLo . '</td></tr>'; $shipopts='';}
				if(($alldata['ordComLoc'] & 2)==2){ print '<tr><td align="right">' . $shipopts.'</td><td align="left" colspan="3"'.$thestyle.'>' . $xxShiInI . '</td></tr>'; $shipopts='';}
				if(($alldata['ordComLoc'] & 4)==4){ print '<tr><td align="right">' . $shipopts.'</td><td align="left" colspan="3"'.$thestyle.'>' . $xxSatDeR . '</td></tr>'; $shipopts='';}
				if(($alldata['ordComLoc'] & 8)==8){ print '<tr><td align="right">' . $shipopts.'</td><td align="left" colspan="3"'.$thestyle.'>' . $xxSigRe2 . '</td></tr>'; $shipopts='';}
				if(($alldata['ordComLoc'] & 16)==16){ print '<tr><td align="right">' . $shipopts.'</td><td align="left" colspan="3"'.$thestyle.'>' . $xxInsDe2 . '</td></tr>'; $shipopts='';}
			}
		}
		$ordAuthNumber=trim($alldata['ordAuthNumber']);
		$ordTransID=trim($alldata['ordTransID']);
		if(! $isprinter && ($ordAuthNumber!='' || $ordTransID!='' || $doedit)){ ?>
					<tr>
					  <td align="right"><strong><?php print $yyAutCod?>:</strong></td>
					  <td align="left"><?php print editfunc($ordAuthNumber,'ordAuthNumber',15) ?></td>
					  <td align="right"><strong><?php print $yyTranID?>:</strong></td>
					  <td align="left"><?php print editfunc($ordTransID,'ordTransID',15) ?></td>
					</tr>
<?php	}
		$ordAddInfo=Trim($alldata['ordAddInfo']);
		if($ordAddInfo!='' || $doedit){ ?>
					<tr>
					  <td align="<?php print $tright?>" valign="top"><strong><?php print str_replace('  ', '&nbsp;&nbsp;', $xxAddInf)?>:</strong></td>
					  <td align="<?php print $tleft?>" colspan="3"><?php
			if($doedit)
				print '<textarea name="ordAddInfo" cols="50" rows="4">' . strip_tags($ordAddInfo) . '</textarea>';
			else
				print str_replace(array("\r\n","\n"),array('<br />','<br />'),strip_tags($ordAddInfo)); ?></td>
					</tr>
<?php	}
		if(! $isprinter){
			//if($alldata['ordPayProvider']==20){
			if(FALSE){
				$ordCNum=$alldata['ordCNum'];
				if($ordCNum!=''){ ?>
					<tr>
					  <td align="right"><strong>Partial CC Number:</strong></td>
					  <td align="left" colspan="3">-<?php print htmlspecials($ordCNum) ?></td>
					</tr>
<?php			}
			}
		?>			<tr>
					  <td align="right"><strong><?php print $yyTraNum?>:</strong></td>
					  <td align="left"><input type="text" name="ordTrackNum" id="ordTrackNum" size="35" value="<?php print htmlspecials($alldata['ordTrackNum'])?>" /></td>
					  <td align="right"><strong><?php print $yyInvNum?>:</strong></td>
					  <td align="left"><input type="text" name="ordInvoice" size="25" value="<?php print htmlspecials($alldata['ordInvoice'])?>" /></td>
					</tr>
					<tr>
					  <td align="right"><strong><?php print $yyOrdSta?>:</strong></td>
					  <td align="left"<?php if(@$loyaltypoints=='') print ' colspan="3"'?>><select name="ordStatus" size="1"><?php
		for($index=0; $index < $numstatus; $index++){
			print '<option value="' . $allstatus[$index]['statID'] . '"';
			if($alldata['ordStatus']==$allstatus[$index]['statID']) print ' selected="selected">' . $allstatus[$index]['statPrivate'] . ' ' . date($admindatestr, $alldata['ordStatusDate']) . ' ' . date('H:i', $alldata['ordStatusDate']) . '</option>'; else print '>' . $allstatus[$index]['statPrivate'] . '</option>';
		} ?></select>&nbsp;&nbsp;<?php if(! $doedit){ ?><input type="checkbox" name="emailstat" value="1" <?php if(getpost('emailstat')=="1" || @$alwaysemailstatus==TRUE) print "checked"?>/> <?php print $yyEStat?><?php } ?></td>
<?php		if(@$loyaltypoints!=''){ ?>
					  <td align="right"><strong><?php print $xxLoyPoi?>:</strong></td>
					  <td align="left"><?php print editfunc($alldata['loyaltyPoints'],'loyaltyPoints',10) ?></td>
<?php		} ?>
					</tr>
					<tr>
					  <td align="right" valign="top"><strong><?php print $yyStaInf?>:</strong></td>
					  <td align="left" colspan="3">
						<table cellspacing="0" cellpadding="0" border="0"><tr>
						<td><textarea name="ordStatusInfo" id="ordStatusInfo" cols="50" rows="4"><?php print htmlspecials($alldata['ordStatusInfo'])?></textarea></td>
						<td>&nbsp;</td>
						</tr></table>
					  </td>
					</tr>
					<tr>
					  <td align="right" valign="top"><strong><?php print $yyPriSta?>:</strong></td>
					  <td align="left" colspan="3">
						<table cellspacing="0" cellpadding="0" border="0"><tr>
						<td><textarea name="ordPrivateStatus" cols="50" rows="4"><?php print htmlspecials($alldata['ordPrivateStatus'])?></textarea></td>
						<td>&nbsp;&nbsp;<?php if(! $doedit) print '<input type="submit" value="' . $yyUpdate . '" />'?></td>
						</tr></table>
					  </td>
					</tr>
<?php		if($alldata['ordReferer']!=''){ ?>
					<tr>
					  <td align="right"><strong>Referer:</strong></td>
					  <td align="left" colspan="3"><input type="text" name="ordreferer" value="<?php print str_replace('"', '&quot;', $alldata['ordReferer'] . ($alldata['ordQuerystr']!='' ? '?' . $alldata['ordQuerystr'] : ''))?>" size="80" /></td>
					</tr>
<?php		}
			if(($alldata['ordPayProvider']==1 || $alldata['ordPayProvider']==3 || $alldata['ordPayProvider']==13 || $alldata['ordPayProvider']==18 || $alldata['ordPayProvider']==19 || $alldata['ordPayProvider']==20 || $alldata['ordPayProvider']==99921) && $alldata['ordAuthNumber']!=''){
				if($alldata['ordPayProvider']==20){ ?>
					<tr><td align="center" colspan="4"><strong>Update Google Account Status:</strong> <span id="googleupdatespan"></span></td></tr>
					<tr>
					  <td align="center" colspan="4">
						<input type="button" value="Charge Order" onclick="updategoogleorder('Google','charge',<?php print $alldata['ordID']?>)" />
						<input type="button" value="Cancel Order" onclick="updategoogleorder('Google','cancel',<?php print $alldata['ordID']?>)" />
						<input type="button" value="Refund Order" onclick="updategoogleorder('Google','refund',<?php print $alldata['ordID']?>)" />
						<input type="button" value="Ship Order" onclick="updategoogleorder('Google','ship',<?php print $alldata['ordID']?>)" />
					  </td>
					</tr>
<?php			}elseif($alldata['ordPayProvider']==21){ ?>
					<tr><td align="center" colspan="4"><strong>Amazon Settle / Refund:</strong> <span id="googleupdatespan"></span></td></tr>
					<tr>
					  <td align="center" colspan="4">
						<input type="button" value="Settle Order" onclick="updategoogleorder('Amazon','settle',<?php print $alldata['ordID']?>)" />
						<input type="button" value="Refund Order" onclick="updategoogleorder('Amazon','refund',<?php print $alldata['ordID']?>)" />
						<input type="button" value="Partial Refund:" onclick="updategoogleorder('Amazon','partialrefund',<?php print $alldata['ordID']?>)" />
						<input type="text" name="txamount" id="txamount" size="5" value="<?php print number_format(($alldata['ordTotal']+$alldata['ordStateTax']+$alldata['ordCountryTax']+$alldata['ordHSTTax']+$alldata['ordShipping']+$alldata['ordHandling'])-$alldata['ordDiscount'], (@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2),'.','')?>" />
					  </td>
					</tr>
<?php			}elseif($alldata['ordPayProvider']==1 || $alldata['ordPayProvider']==18 || $alldata['ordPayProvider']==19){ ?>
					<tr><td align="center" colspan="4"><strong>PayPal Authorization / Capture:</strong> <span id="googleupdatespan"></span></td></tr>
					<tr>
					  <td align="right"><strong>Capture Amount:</strong></td>
					  <td align="left" colspan="3"><input type="text" name="captureamount" id="captureamount" size="10" value="<?php print number_format(($alldata['ordTotal']+$alldata['ordStateTax']+$alldata['ordCountryTax']+$alldata['ordHSTTax']+$alldata['ordShipping']+$alldata['ordHandling'])-$alldata['ordDiscount'], (@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2),'.','')?>" />
					  <select name="additionalcapture" id="additionalcapture" size="1"><option value="0">Close Authorization</option><option value="1">Leave Open for Additional Capture</option></select>
					  </td>
					</tr>
					<tr>
					  <td align="right"><strong>Note to buyer:</strong></td>
					  <td align="left" colspan="3"><textarea name="buyernote" id="buyernote" cols="50" rows="4"></textarea></td>
					</tr>
					<tr>
					  <td align="right"><strong>Action:</strong></td>
					  <td align="left" colspan="3"><select name="paypalaction" id="paypalaction" size="1" onchange="setpaypalelements()"><option value="charge">Capture</option><option value="void">Void</option><option value="reauth">Reauthorization</option></select>
					  <input type="button" value="Inform PayPal" onclick="updatepaypalorder('PayPal',<?php print $alldata['ordID']?>)" />
					  </td>
					</tr>
<?php			}else{ ?>
					<tr><td align="center" colspan="4"><input type="button" value="Capture Funds" onclick="openemailpopup('oid=<?php print $alldata['ordID']?>')" /></td></tr>
<?php			}
			}
			if(! $doedit) print '</form>';
			//if((int)$alldata["ordPayProvider"]==10){
			if(FALSE){			?>
					<tr>
					  <td align="center" colspan="4"><hr width="50%">
					  </td>
					</tr>
<?php			if(@$_SERVER["HTTPS"]!="on" && (@$_SERVER["SERVER_PORT"]!="443") && @$nochecksslserver!=TRUE){ ?>
					<tr>
					  <td align="center" colspan="4"><span style="color:#FF0000;font-weight:bold">You do not appear to be viewing this page on a secure (https) connection. Credit card information cannot be shown.</span></td>
					</tr>
<?php			}else{
					$ordCNum=$alldata["ordCNum"];
					if($ordCNum!=''){
						$cnumarr="";
						$encryptmethod=strtolower(@$encryptmethod);
						if($encryptmethod=="none"){
							$cnumarr=explode("&",$ordCNum);
						}elseif($encryptmethod=="mcrypt"){
							if(@$mcryptalg=="") $mcryptalg=MCRYPT_BLOWFISH;
							$td=mcrypt_module_open($mcryptalg, '', 'cbc', '');
							$thekey=@$ccencryptkey;
							$thekey=substr($thekey, 0, mcrypt_enc_get_key_size($td));
							$cnumarr=explode(" ", $ordCNum);
							$iv=@$cnumarr[0];
							$iv=@pack("H" . strlen($iv), $iv);
							$ordCNum=@pack("H" . strlen(@$cnumarr[1]), @$cnumarr[1]);
							mcrypt_generic_init($td, $thekey, $iv);
							$cnumarr=explode("&", mdecrypt_generic($td, $ordCNum));
							mcrypt_generic_deinit($td);
							mcrypt_module_close($td);
						}elseif($encryptmethod=="publickey"){
						}else{
							print '<tr><td colspan="4">WARNING: $encryptmethod is not set. Please see http://www.ecommercetemplates.com/phphelp/ecommplus/parameters.asp#encryption</td></tr>';
						}
					}
					if($encryptmethod=="publickey"){ ?>
					<tr>
					  <td align="center" colspan="4">
				  <table>
					<tr>
					  <td align="right" colspan="2"><strong><?php print "Encrypted Data"?>:</strong></td>
					  <td align="left" colspan="2"><textarea cols="70" rows="4" id="ordcnumenctxt"><?php
							print $ordCNum ?></textarea></td>
					</tr>
				  </table>
<script type="text/javascript">
document.getElementById('ordcnumenctxt').select();
</script>
					  </td>
					</tr>
<?php				}else{ ?>
					<tr>
					  <td width="50%" align="right" colspan="2"><strong><?php print $xxCCName?>:</strong></td>
					  <td width="50%" align="left" colspan="2"><?php
							if(@$encryptmethod!=""){
									if(is_array(@$cnumarr)) print trim(htmlspecials(URLDecode(@$cnumarr[4])));
							} ?></td>
					</tr>
					<tr>
					  <td align="right" colspan="2"><strong><?php print $yyCarNum?>:</strong></td>
					  <td align="left" colspan="2"><?php
							if($ordCNum!=''){
								if(is_array($cnumarr)) print htmlspecials($cnumarr[0]);
							}else{
								print "(no data)";
							} ?></td>
					</tr>
					<tr>
					  <td align="right" colspan="2"><strong><?php print $yyExpDat?>:</strong></td>
					  <td align="left" colspan="2"><?php
							if(@$encryptmethod!=""){
									if(is_array(@$cnumarr)) print htmlspecials(@$cnumarr[1]);
							} ?></td>
					</tr>
					<tr>
					  <td align="right" colspan="2"><strong>CVV Code:</strong></td>
					  <td align="left" colspan="2"><?php
							if(@$encryptmethod!=""){
									if(is_array(@$cnumarr)) print htmlspecials(@$cnumarr[2]);
							} ?></td>
					</tr>
					<tr>
					  <td align="right" colspan="2"><strong>Issue Number:</strong></td>
					  <td align="left" colspan="2"><?php
							if(@$encryptmethod!=""){
									if(is_array(@$cnumarr)) print htmlspecials(@$cnumarr[3]);
							} ?></td>
					</tr>
<?php				}
					if($ordCNum!='' && !$doedit){ ?>
				  <form method="post" action="adminorders.php?id=<?php print getget('id')?>">
					<input type="hidden" name="delccdets" value="<?php print getget('id')?>" />
					<tr>
					  <td width="100%" align="center" colspan="4"><input type="submit" value="<?php print $yyDelCC?>" /></td>
					</tr>
				  </form>
<?php				}
				}
			}
		}elseif($isinvoice && trim($alldata['ordInvoice'])!=''){ ?>
					<tr>
					  <td align="right"><strong><?php print $yyInvNum?>:</strong></td>
					  <td align="left" colspan="3"><?php print editfunc($alldata['ordInvoice'],'ordInvoice',15)?></td>
					</tr>
<?php
		} ?>
				  </table>
				</td>
			  </tr>
			</table>
<?php	@include './inc/customppplugin.php'; ?>
		  </td>
		</tr>
	  </table>
<div id="debugdiv"></div>
<span id="productspan">
<?php	$WSP=''; $OWSP=''; $percdisc=''; $wholesaledisc=FALSE;
		if($alldata['ordClientID']!=0){
			$sSQL="SELECT clActions,clPercentDiscount FROM customerlogin WHERE clID='".$alldata['ordClientID']."'";
			$result=ect_query($sSQL) or ect_error();
			if($rs2=ect_fetch_assoc($result)){
				if(($rs2['clActions'] & 8)==8){
					$WSP='pWholesalePrice AS ';
					$wholesaledisc=TRUE;
					if(@$wholesaleoptionpricediff==TRUE) $OWSP='optWholesalePriceDiff AS ';
				}
				if(($rs2['clActions'] & 16)==16){
					$WSP=((100.0-(double)$rs['clPercentDiscount'])/100.0) . '*'.(($rs2['clActions'] & 8)==8?'pWholesalePrice':'pPrice').' AS ';
					$percdisc=$rs['clPercentDiscount'];
					$OWSP=((100.0-$rs2['clPercentDiscount'])/100.0) . '*'.(($rs2['clActions'] & 8)==8 && @$wholesaleoptionpricediff?'optWholesalePriceDiff':'optPriceDiff').' AS ';
				}
			}
			ect_free_result($result);
		}
?>
	<table width="100%" border="0" cellspacing="2" cellpadding="0" bgcolor="#FFFFFF">
	  <tr>
		<td>
		  <table class="ordtbl" id="producttable" width="100%" border="0" cellspacing="2" cellpadding="4">
			<tr class="cobll">
			  <td><strong><?php print $xxPrId?></strong></td>
			  <td><strong><?php print $xxPrNm?></strong></td>
			  <td><strong><?php print $xxPrOpts?></strong></td>
<?php	if($isinvoice) print '<td><strong>' . $xxUnitPr . '</strong></td>'; ?>
			  <td><strong><?php print $xxQuant?></strong></td>
<?php	if(! $isprinter || $isinvoice) print '<td><strong>' . ($doedit ? $xxUnitPr : $xxPrice) . '</strong></td>';
		if($doedit) print '<td align="center"><strong>DEL</strong></td>' ?>
			</tr>
<?php	$totoptpricediff=0;
		$stockjs='';
		if($allorders!='' && ect_num_rows($allorders)>0){
			$totoptpricediff=0;
			$rowcounter=0;
			while($rsOrders=ect_fetch_assoc($allorders)){
				$optpricediff=0;
				$cartGiftMessage=trim($rsOrders['cartGiftMessage']);
				if($rsOrders['pStockByOpts']==0 && $alldata['ordAuthStatus']!='MODWARNOPEN') $stockjs.="stock['pid_" . $rsOrders['cartProdId'] . "']+=" . $rsOrders['cartQuantity'] . ";\r\n";
?>
			<tr <?php if($rsOrders['cartGiftWrap']!=0 && ! $isinvoice) print 'style="background-color: #AAFFAA"'; else print 'class="cobll"'?>>
			  <td valign="top" style="white-space:nowrap;"><?php
				if($doedit) print '<input type="button" value="..." onclick="updateoptions(' . $rowcounter . ')">&nbsp;<input type="hidden" name="cartid' . $rowcounter . '" value="' . htmlspecials($rsOrders['cartID']) . '" /><input type="hidden" id="stateexempt' . $rowcounter . '" value="' . (($rsOrders['pExemptions'] AND 1)==1?'true':'false') . '" /><input type="hidden" id="countryexempt' . $rowcounter . '" value="' . (($rsOrders['pExemptions'] & 2)==2?'true':'false') . '" />';
				print '<strong>';
				if($doedit || $isprinter) print editspecial($rsOrders['cartProdId'],'prodid' . $rowcounter,18,'AUTOCOMPLETE="off" onkeydown="return combokey(this,event)" onkeyup="combochange(this,event)"'); else print ($rsOrders['cartProdId']!=$giftwrappingid?'<a href="../proddetail.php?prod=' . urlencode(@$usepnamefordetaillinks?str_replace(' ',@$detlinkspacechar,$rsOrders['cartProdName']):$rsOrders['cartProdId']) . '" target="_blank">':'') . htmlspecials($rsOrders['cartProdId']) . ($rsOrders['cartProdId']!=$giftwrappingid?'</a>':'');
				print '</strong>';
				if($rsOrders['cartProdId']==$giftcertificateid){
					$sSQL="SELECT gcID FROM giftcertificate WHERE gcCartID=" . $rsOrders['cartID'];
					$result=ect_query($sSQL) or ect_error();
					if($rs=ect_fetch_assoc($result)){
						print '<input type="button" value="'.$yyView.'" onclick="document.location=\'admingiftcert.php?id='.$rs['gcID'].'\'" />';
					}
					ect_free_result($result);
				}
				if($doedit) print showgetoptionsselect('selectprodid'.$rowcounter);
				?></td>
			  <td valign="top"><?php
				print str_replace('&amp;','&',editspecial(decodehtmlentities($rsOrders['cartProdName']),'prodname' . $rowcounter,24,'AUTOCOMPLETE="off" onkeydown="return combokey(this,event)" onkeyup="combochange(this,event)"'));
				if($doedit){
					print showgetoptionsselect('selectprodname'.$rowcounter);
				}elseif(! $isinvoice){
					$sSQL="SELECT productpackages.pID,quantity,pName,quantity FROM productpackages INNER JOIN products on productpackages.pID=products.pID WHERE packageID='" . $rsOrders['cartProdId'] . "'";
					$result=ect_query($sSQL) or ect_error();
					if(ect_num_rows($result)>0){
						print '<table style="font-size:0.9em;color:#404040">';
						while($rs=ect_fetch_assoc($result))
							print '<tr><td>&nbsp;&gt;</td><td>' . $rs['pID'] . ':</td><td>' . $rs['pName'] . '</td><td>' . $rs['quantity'] . '</td></tr>';
						print '</table>';
					}
					ect_free_result($result);
				}
			?></td>
			  <td valign="top"><?php
				if($doedit) print '<span id="optionsspan' . $rowcounter . '">';
				$sSQL="SELECT coOptGroup,coCartOption,coPriceDiff,coOptID,optGroup FROM cartoptions LEFT JOIN options ON cartoptions.coOptID=options.optID WHERE coCartID=" . $rsOrders["cartID"] . " ORDER BY coID";
				$result=ect_query($sSQL) or ect_error();
				if(ect_num_rows($result) > 0 || ($rsOrders['cartGiftWrap']!=0 && $cartGiftMessage!='' && ! $isinvoice)){
					$rs2=ect_fetch_assoc($result);
					if($rsOrders['pStockByOpts']!=0 && $alldata['ordAuthStatus']!='MODWARNOPEN') $stockjs.="stock['oid_" . $rs2['coOptID'] . "']+=" . $rsOrders['cartQuantity'] . ";\r\n";
					if($doedit) print '<table border="0" cellspacing="0" cellpadding="1" width="100%">';
					if(ect_num_rows($result) > 0) do{
						if($doedit){
							print '<tr><td align="right"><strong>' . $rs2["coOptGroup"] . ':</strong></td><td>';
							if(is_null($rs2["optGroup"])){
								print 'xxxxxx';
							}else{
								$sSQL="SELECT optID," . getlangid('optName',32) . ','.$OWSP."optPriceDiff,optType,optFlags,optStock,optTxtCharge,optPriceDiff AS optDims FROM options INNER JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optGroup=" . $rs2["optGroup"] . ' ORDER BY optID';
								$result2=ect_query($sSQL) or ect_error();
								if($rsl=ect_fetch_assoc($result2)){
									if(abs($rsl['optType'])==1 || abs($rsl['optType'])==2 || abs($rsl['optType'])==4){
										print '<select onchange="dorecalc(true)" name="optn' . $rowcounter . '_' . $rs2["coOptID"] . '" id="optn' . $rowcounter . '_' . $rs2["coOptID"] . '" size="1">';
										do {
											print '<option value="' . $rsl["optID"] . "|" . (($rsl["optFlags"] & 1)==1 ? ($rsOrders["cartProdPrice"]*$rsl["optPriceDiff"])/100.0 : $rsl["optPriceDiff"]) . '"';
											if($rsl["optID"]==$rs2["coOptID"]) print ' selected="selected"';
											print '>' . $rsl[getlangid("optName",32)];
											if((double)$rsl["optPriceDiff"]!=0){
												print ' ';
												if((double)$rsl["optPriceDiff"] > 0) print '+';
												if(($rsl["optFlags"] & 1)==1)
													print number_format(($rsOrders["cartProdPrice"]*$rsl["optPriceDiff"])/100.0,2,'.','');
												else
													print number_format($rsl["optPriceDiff"],2,'.','');
											}
											print '</option>';
										} while($rsl=ect_fetch_assoc($result2));
										print '</select>';
									}else{
										if($rsl['optTxtCharge']!=0) print '<script type="text/javascript">opttxtcharge[' . $rsl['optID'] . ']=' . $rsl['optTxtCharge'] . ';</script>';
										print "<input type='hidden' name='optn" . $rowcounter . '_' . $rs2["coOptID"] . "' value='" . $rsl["optID"] . "' /><textarea name='voptn" . $rowcounter . '_' . $rs2["coOptID"] . "' id='voptn". $rowcounter. '_' . $rs2["coOptID"] . "' cols='30' rows='3'>";
										print htmlspecials($rs2['coCartOption']) . '</textarea>';
									}
								}
								ect_free_result($result2);
							}
							print '</td></tr>';
						}else{
							print '<strong>' . $rs2["coOptGroup"] . ':</strong> ' . str_replace(array('  ',"\r\n","\n"),array('&nbsp;&nbsp;','<br />','<br />'),htmlspecials($rs2['coCartOption'])) . '<br />';
						}
						if($doedit)
							$optpricediff+=$rs2["coPriceDiff"];
						else
							$rsOrders["cartProdPrice"]+=$rs2["coPriceDiff"];
					}while($rs2=ect_fetch_assoc($result));
					if($rsOrders['cartGiftWrap']!=0 && $cartGiftMessage!='' && ! $isinvoice){
						print ($doedit?'<tr><td align="right">':'') . '<strong>' . 'Gift Wrap Message:' . '</strong> ' . ($doedit?'</td><td>':'') . $cartGiftMessage . ($doedit?'</td></tr>':'<br />');
					}
					if($doedit) print '</table>';
				}else{
					print ' - ';
				}
				ect_free_result($result);
				if($doedit) print '</span>' ?></td>
<?php			if($isinvoice) print '<td valign="top">' . FormatEuroCurrency($rsOrders['cartProdPrice']) . '</td>'; ?>
			  <td valign="top"><?php print editfunc($rsOrders["cartQuantity"],'quant' . $rowcounter . '" onchange="dorecalc(true)',5)?></td>
<?php			if(! $isprinter || $isinvoice){ ?>
			  <td valign="top"><?php if($doedit) print editnumeric($rsOrders['cartProdPrice'],'price' . $rowcounter . '" onchange="dorecalc(true)',7); else print FormatEuroCurrency($rsOrders["cartProdPrice"]*$rsOrders["cartQuantity"])?>
<?php					if($doedit){
							print '<input type="hidden" id="optdiffspan' . $rowcounter . '" value="' . $optpricediff . '">';
							$totoptpricediff+=($optpricediff*$rsOrders["cartQuantity"]);
						}
			?></td>
<?php			}
				if($doedit) print '<td align="center"><input type="checkbox" name="del_' . $rowcounter . '" id="del_' . $rowcounter . '" value="yes" /></td>' ?>
			</tr>
<?php				$rowcounter++;
			}
		}
		if($allorders!='') ect_free_result($allorders);
		if($doedit){ ?>
			<tr class="cobll">
			  <td align="right" colspan="4">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				  <tr>
					<td align="center"><?php if($doedit) print '<input style="width:30px;" type="button" value="-" onclick="extraproduct(\'-\')"> ' . $yyMoProd . ' <input style="width:30px;" type="button" value="+" onclick="extraproduct(\'+\')"> &nbsp; <input type="button" value="' . $yyRecal . '" onclick="dorecalc(false)">'?></td>
					<td align="right" width="100"><strong><?php print str_replace(' ', '&nbsp;', $yyOptTot)?></strong></td>
				  </tr>
				</table></td>
			  <td align="left" colspan="2"><span id="optdiffspan"><?php print number_format($totoptpricediff, 2, '.', '')?></span><script type="text/javascript">
			var stock=new Array();
<?php		$optgroups='';
			$addcomma='';
			if($theid!='0'){
				$sSQL="SELECT DISTINCT cartID,pID,pInStock,pStockByOpts FROM cart INNER JOIN products ON cart.cartProdId=products.pID WHERE cartOrderID=".$theid;
				$result=ect_query($sSQL) or ect_error();
				while($rs=ect_fetch_assoc($result)){
					print "stock['pid_".$rs['pID']."']=";
					if($rs['pStockByOpts']==0)
						print $rs['pInStock'].";\r\n";
					else{
						print "'bo';\r\n";
						$sSQL="SELECT coID,optStock,coOptID,optGrpID FROM cart INNER JOIN cartoptions ON cart.cartID=cartoptions.coCartID INNER JOIN options ON cartoptions.coOptID=options.optID INNER JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optType IN (-4,-2,-1,1,2,4) AND cartID=".$rs['cartID'];
						$result2=ect_query($sSQL) or ect_error();
						while($rs2=ect_fetch_assoc($result2)){
							$optgroups.=$addcomma . $rs2['optGrpID'];
							$addcomma=',';
						}
						ect_free_result($result2);
					}
				}
				ect_free_result($result);
			}
			if($optgroups!=''){
				$sSQL="SELECT optID,optStock FROM options WHERE optGroup IN (" . $optgroups . ")";
				$result=ect_query($sSQL) or ect_error();
				while($rs=ect_fetch_assoc($result)){
					print "stock['oid_" . $rs['optID']."']=" . $rs['optStock'] . ";\r\n";
				}
				ect_free_result($result);
			}
			print $stockjs;
			if(getget('id')=='new') print "extraproduct('+');\r\n";
?></script></td>
			</tr>
<?php	}
		if(! $isprinter || $isinvoice){ ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				  <tr>
					<td align="center"><?php if($GLOBALS['useStockManagement'] && $doedit) print ' '; else print '&nbsp;' ?><?php
			if($doedit){
				print '<table class="cobtbl" border="0" cellspacing="1" cellpadding="3"><tr>';
				if($GLOBALS['useStockManagement']) print '<td class="cobll">&nbsp;<input type="checkbox" name="updatestock" value="ON" checked> <strong>' . $yyUpStLv . '</strong>&nbsp;</td>';
				print '<td class="cobll">&nbsp;<input type="checkbox" id="wholesaledisc" value="ON"'.($wholesaledisc?' checked="checked"':'').' /> <strong>' . $yyWholPr . '</strong>&nbsp;</td>';
				print '<td class="cobll">&nbsp;<input type="text" id="percdisc" size="3" value="'.$percdisc.'" /> <strong>' . $yyPerDis . '</strong>&nbsp;</td>';
				print '</tr></table>';
			}
?></td>
					<td align="<?php print $tright?>" width="100"><strong><?php print str_replace(' ', '&nbsp;', $xxOrdTot)?>:</strong></td>
				  </tr>
				</table></td>
			  <td align="<?php print $tleft?>"><?php print editnumeric($alldata['ordTotal'],'ordtotal',7)?></td>
<?php			if($doedit) print '<td align="center">&nbsp;</td>' ?>
			</tr>
<?php		if($isprinter && @$combineshippinghandling==TRUE){ ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>"><strong><?php print $xxShipHa?>:</strong></td>
			  <td align="<?php print $tleft?>"><?php print FormatEuroCurrency($alldata['ordShipping']+$alldata['ordHandling'])?></td>
			</tr>
<?php		}else{
				if((double)$alldata['ordShipping']!=0.0 || $doedit){ ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>"><strong><?php print $xxShippg?>:</strong></td>
			  <td align="<?php print $tleft?>"><?php print editnumeric($alldata['ordShipping'],'ordShipping',7)?></td>
<?php			if($doedit) print '<td align="center"><input type="button" value="'.'Calculate'.'" onclick="calcshipping()" /></td>' ?>
			</tr>
<?php			}
				if((double)$alldata['ordHandling']!=0.0 || $doedit){ ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>"><strong><?php print $xxHndlg?>:</strong></td>
			  <td align="<?php print $tleft?>"><?php print editnumeric($alldata['ordHandling'],'ordHandling',7)?></td>
<?php				if($doedit) print '<td align="center">&nbsp;</td>' ?>
			</tr>
<?php			}
			}
			if((double)$alldata['ordDiscount']!=0.0 || $doedit){ ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>"><strong><?php print $xxDscnts?>:</strong></td>
			  <td align="<?php print $tleft?>"><span style="color:#FF0000"><?php print editnumeric($alldata['ordDiscount'],'ordDiscount',7)?></span></td>
<?php			if($doedit) print '<td align="center">&nbsp;</td>' ?>
			</tr>
<?php		}
			if((double)$alldata['ordStateTax']!=0.0 || $doedit){ ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>"><strong><?php print $xxStaTax?>:</strong></td>
			  <td align="<?php print $tleft?>"><?php print editnumeric($alldata['ordStateTax'],'ordStateTax',7)?></td>
<?php			if($doedit) print '<td align="center" style="white-space:nowrap;"><input type="text" style="text-align:right" name="staterate" id="staterate" size="2" value="' . $statetaxrate . '">%</td>' ?>
			</tr>
<?php		}
			if((double)$alldata['ordCountryTax']!=0.0 || $doedit){ ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>"><strong><?php print $xxCntTax?>:</strong></td>
			  <td align="<?php print $tleft?>"><?php print editnumeric($alldata['ordCountryTax'],'ordCountryTax',7)?></td>
<?php			if($doedit) print '<td align="center" style="white-space:nowrap;"><input type="text" style="text-align:right" name="countryrate" id="countryrate" size="2" value="' . $countrytaxrate . '">%</td>' ?>
			</tr>
<?php		}
			if((double)$alldata['ordHSTTax']!=0.0 || ($doedit && $origCountryID==2)){ ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>"><strong><?php print $xxHST?>:</strong></td>
			  <td align="<?php print $tleft?>"><?php print editnumeric($alldata['ordHSTTax'],'ordHSTTax',7)?></td>
<?php			if($doedit) print '<td align="center" style="white-space:nowrap;"><input type="text" style="text-align:right" name="hstrate" id="hstrate" size="2" value="' . $hsttaxrate . '">%</td>' ?>
			</tr>
<?php		} ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>"><strong><?php print $xxGndTot?>:</strong></td>
			  <td align="<?php print $tleft?>"><span id="grandtotalspan"><?php print FormatEuroCurrency(($alldata['ordTotal']+$alldata['ordStateTax']+$alldata['ordCountryTax']+$alldata['ordHSTTax']+$alldata['ordShipping']+$alldata['ordHandling'])-$alldata['ordDiscount'])?></span></td>
<?php		if($doedit) print '<td align="center">&nbsp;</td>' ?>
			</tr>
<?php	} // ! $isprinter || $isinvoice ?>
		  </table>
		</td>
	  </tr>
	</table>
</span>
		  </td>
		</tr>
<?php	if($isprinter && ! @isset($packingslipfooter)) $packingslipfooter=@$invoicefooter;
		if($isinvoice && @$invoicefooter!=''){ ?>
		<tr><td width="100%"><?php print $invoicefooter?></td></tr>
<?php	}elseif($isprinter && @$packingslipfooter!=''){ ?>
		<tr><td width="100%"><?php print $packingslipfooter?></td></tr>
<?php	}elseif($doedit){ ?>
		<tr> 
          <td align="center" width="100%">&nbsp;<br /><input type="submit" value="<?php print $yyUpdate?>" /><br />&nbsp;</td>
		</tr>
<?php	} ?>
	  </table>
<?php
		if($doedit) print '</form>';
	} // foreach($idlist as $theid)
}else{
	$sSQL="SELECT ordID FROM orders WHERE ordStatus=1";
	if(getpost('act')!="purge") $sSQL.=" AND ordStatusDate<'" . date("Y-m-d H:i:s", time()-(3*60*60*24)) . "'";
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		$theid=$rs["ordID"];
		$delOptions="";
		$addcomma="";
		$result2=ect_query("SELECT cartID FROM cart WHERE cartOrderID=" . $theid) or ect_error();
		while($rs2=ect_fetch_assoc($result2)){
			$delOptions.=$addcomma . $rs2["cartID"];
			$addcomma=",";
		}
		ect_free_result($result2);
		if($delOptions!=''){
			$sSQL="DELETE FROM cartoptions WHERE coCartID IN (" . $delOptions . ")";
			ect_query($sSQL) or ect_error();
		}
		ect_query("DELETE FROM cart WHERE cartOrderID=" . $theid) or ect_error();
		ect_query("DELETE FROM orders WHERE ordID=" . $theid) or ect_error();
		ect_query("DELETE FROM giftcertificate WHERE gcOrderID=" . $theid) or ect_error();
		ect_query("DELETE FROM giftcertsapplied WHERE gcaOrdID=" . $theid) or ect_error();
	}
	ect_free_result($result);
	if(getpost('act')=='authorize'){
		ect_query("UPDATE orders set ordAuthNumber='" . escape_string(getpost('authcode')!='' ? getpost('authcode') : $yyManAut) . "' WHERE ordID=" . getpost('id')) or ect_error();
		ect_query('UPDATE cart SET cartCompleted=1 WHERE cartOrderID=' . getpost('id')) or ect_error();
		updateorderstatus(getpost('id'), 3);
	}elseif(getpost('act')=='unpending'){
		ect_query("UPDATE orders set ordAuthStatus='' WHERE ordID=" . getpost('id')) or ect_error();
		ect_query("UPDATE orders set ordShipType='" . escape_string($yyMoWarn) . "' WHERE ordShipType='MODWARNOPEN' AND ordID=" . getpost('id')) or ect_error();
		ect_query("UPDATE orders set ordAuthNumber='" . escape_string($yyManAut) . "' WHERE ordAuthNumber='' AND ordID=" . getpost('id')) or ect_error();
		ect_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID=" . getpost('id')) or ect_error();
		$sSQL="SELECT ordStatus FROM orders WHERE ordID=" . getpost('id');
		$result=ect_query($sSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		$oldordstatus=$rs['ordStatus'];
		ect_free_result($result);
		if($oldordstatus<3) updateorderstatus(getpost('id'), $oldordstatus<3 ? 3 : $oldordstatus);
	}elseif(getpost('act')=='editablefield'){
		setcookie('editablefield', getpost('id'), time()+31536000, '/', '', @$_SERVER['HTTPS']=='on');
	}elseif(getpost('act')=='searchfield'){
		setcookie('searchfield', getpost('id'), time()+31536000, '/', '', @$_SERVER['HTTPS']=='on');
	}elseif(getpost('act')=='status' && getpost('theeditablefield')!='' && getpost('theeditablefield')!='status'){
		$maxitems=(int)getpost('maxitems');
		$editfield=getpost('theeditablefield');
		for($mindex=0; $mindex < $maxitems; $mindex++){
			$iordid=getpost('ordid' . $mindex);
			ect_query("UPDATE orders SET ord" . getpost('theeditablefield') . "='" . escape_string(@$_POST[$editfield . $mindex]) . "' WHERE ordID=" . $iordid);
		}
	}elseif(getpost('act')=="status"){
		$maxitems=(int)getpost('maxitems');
		for($mindex=0; $mindex < $maxitems; $mindex++){
			updateorderstatus(getpost('ordid' . $mindex), (int)getpost('ordStatus' . $mindex));
		}
	}
	$hasfromdate=FALSE;
	$hastodate=FALSE;
	$fromdate=trim(@$_REQUEST['fromdate']);
	$todate=trim(@$_REQUEST['todate']);
	if($fromdate!=''){
		$hasfromdate=TRUE;
		if(is_numeric($fromdate))
			$thefromdate=time()-($fromdate*60*60*24);
		else
			$thefromdate=parsedate($fromdate);
	}else
		$thefromdate=strtotime(date('Y-m-d', time()+($dateadjust*60*60)));
	if($todate!=''){
		$hastodate=TRUE;
		if(is_numeric($todate))
			$thetodate=time()-($todate*60*60*24);
		else
			$thetodate=parsedate($todate);
	}else
		$thetodate=strtotime(date('Y-m-d', time()+($dateadjust*60*60)));
	if($hasfromdate && $hastodate){
		if($thefromdate > $thetodate){
			$tmpdate=$thetodate;
			$thetodate=$thefromdate;
			$thefromdate=$tmpdate;
		}
	}
	$sSQL='SELECT DISTINCT ordID,ordName,ordLastName,payProvName,ordAuthNumber,ordDate,ordStatus,ordTotal-ordDiscount AS ordTot,ordTransID,ordAVS,ordCVV,ordPayProvider,ordAuthStatus,ordTrackNum,ordInvoice,ordShipType,ordEmail FROM orders INNER JOIN payprovider ON payprovider.payProvID=orders.ordPayProvider';
	$whereSQL='';
	$origsearchtext=getrequest('searchtext');
	$searchtext=escape_string(getrequest('searchtext'));
	$ordersearchfield=getrequest('ordersearchfield');
	if($ordersearchfield!='')setcookie('ordersearchfield',$ordersearchfield,time()+31536000, '/', '', @$_SERVER['HTTPS']=='on');
	$ordstatus=@$_REQUEST['ordStatus'];
	$ordstate=@$_REQUEST['ordstate'];
	$ordcountry=@$_REQUEST['ordcountry'];
	$payprovider=@$_REQUEST['payprovider'];
	if($ordersearchfield=='product' && $searchtext!='') $whereSQL.=' INNER JOIN cart ON orders.ordID=cart.cartOrderID ';
	if($ordersearchfield=='ordid' && $searchtext!='' && is_numeric($searchtext)){
		$whereSQL.=" WHERE ordID='" . $searchtext . "' ";
	}else{
		if(is_array($ordstatus)) $whereSQL.=' WHERE ' . (getrequest('notstatus')=='ON' ? 'NOT ' : '') . '(ordStatus IN (' . implode(',', $ordstatus) . '))'; else $whereSQL.=' WHERE ordStatus<>1';
		if(is_array($ordstate)) $whereSQL.=' AND ' . (getrequest('notsearchfield')=='ON' ? 'NOT ' : '') . "(ordState IN ('" . implode("','", $ordstate) . "'))";
		if(is_array($ordcountry)) $whereSQL.=' AND ' . (getrequest('notsearchfield')=='ON' ? 'NOT ' : '') . "(ordCountry IN ('" . implode("','", $ordcountry) . "'))";
		if(is_array($payprovider)) $whereSQL.=' AND ' . (getrequest('notsearchfield')=='ON' ? 'NOT ' : '') . '(ordPayprovider IN (' . implode(',', $payprovider) . '))';
		if($hasfromdate)
			$whereSQL.=" AND ordDate BETWEEN '" . date('Y-m-d', $thefromdate) . "' AND '" . date('Y-m-d', ($hastodate ? $thetodate+96400 : $thefromdate+96400)) . "'";
		elseif($searchtext=='' && $ordstatus=='' && $ordstate=='' && $ordcountry=='' && $payprovider=='')
			$whereSQL.=" AND ordDate BETWEEN '" . date('Y-m-d', time()+($dateadjust*60*60)) . "' AND '" . date('Y-m-d', time()+($dateadjust*60*60)+96400) . "'";
		if($searchtext!=''){
			if($ordersearchfield=='ordid' || $ordersearchfield=='name'){
				if(@$usefirstlastname){
					splitfirstlastname($searchtext,$firstname,$lastname);
					if($lastname=='')
						$namesql="(ordName LIKE '%".$firstname."%' OR ordLastName LIKE '%".$firstname."%')";
					else
						$namesql="(ordName LIKE '%".$firstname."%' AND ordLastName LIKE '%".$lastname."%')";
				}else
					$namesql="ordName LIKE '%".$searchtext."%'";
			}
			if($ordersearchfield=='ordid')
				$whereSQL.=" AND (ordEmail LIKE '%" . $searchtext . "%' OR ".$namesql.')';
			elseif($ordersearchfield=='email')
				$whereSQL.=" AND ordEmail LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='authcode')
				$whereSQL.=" AND (ordAuthNumber LIKE '%" . $searchtext . "%' OR ordTransID LIKE '%" . $searchtext . "%')";
			elseif($ordersearchfield=='name')
				$whereSQL.=" AND " . $namesql;
			elseif($ordersearchfield=='product')
				$whereSQL.=" AND (cartProdID LIKE '%" . $searchtext . "%' OR cartProdName LIKE '%" . $searchtext . "%')";
			elseif($ordersearchfield=='address')
				$whereSQL.=" AND (ordAddress LIKE '%" . $searchtext . "%' OR ordAddress2 LIKE '%" . $searchtext . "%' OR ordCity LIKE '%" . $searchtext . "%' OR ordState LIKE '%" . $searchtext . "%' OR ordShipAddress LIKE '%" . $searchtext . "%' OR ordShipAddress2 LIKE '%" . $searchtext . "%' OR ordShipCity LIKE '%" . $searchtext . "%' OR ordShipState LIKE '%" . $searchtext . "%')";
			elseif($ordersearchfield=='phone')
				$whereSQL.=" AND ordPhone LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='zip')
				$whereSQL.=" AND ordZip LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='invoice')
				$whereSQL.=" AND ordInvoice LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='tracknum')
				$whereSQL.=" AND ordTrackNum LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='affiliate')
				$whereSQL.=" AND ordAffiliate='" . $searchtext . "'";
			elseif($ordersearchfield=='extra1')
				$whereSQL.=" AND ordExtra1 LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='extra2')
				$whereSQL.=" AND ordExtra2 LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='checkout1')
				$whereSQL.=" AND ordCheckoutExtra1 LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='checkout2')
				$whereSQL.=" AND ordCheckoutExtra2 LIKE '%" . $searchtext . "%'";
		}
	}
	$sSQL.=$whereSQL;
	$editablefield=@$_COOKIE['editablefield'];
	$sortorder=@$_COOKIE['ordersort'];
	if($sortorder=='oidd')
		$sSQL.=' ORDER BY ordID DESC';
	elseif($sortorder=='orna')
		$sSQL.=' ORDER BY ordName';
	elseif($sortorder=='ornd')
		$sSQL.=' ORDER BY ordName DESC';
	elseif($sortorder=='orda')
		$sSQL.=' ORDER BY ordDate';
	elseif($sortorder=='ordd')
		$sSQL.=' ORDER BY ordDate DESC';
	elseif($sortorder=='oraa')
		$sSQL.=' ORDER BY ordAuthNumber';
	elseif($sortorder=='orad')
		$sSQL.=' ORDER BY ordAuthNumber DESC';
	elseif($sortorder=='orpa')
		$sSQL.=' ORDER BY ordPayProvider';
	elseif($sortorder=='orpd')
		$sSQL.=' ORDER BY ordPayProvider DESC';
	elseif($sortorder=='orsa' || $sortorder=='orsd'){
		if($editablefield=='tracknum')
			$sSQL.=' ORDER BY ordTrackNum';
		elseif($editablefield=='tracknum')
			$sSQL.=' ORDER BY ordInvoice';
		elseif($editablefield=='email')
			$sSQL.=' ORDER BY ordEmail';
		else
			$sSQL.=' ORDER BY ordStatus';
		if($sortorder=='orsd') $sSQL.=' DESC';
	}else
		$sSQL.=' ORDER BY ordID';
	$alldata=ect_query('SELECT COUNT(*) AS bar FROM orders'.$whereSQL) or ect_error();
	$rs=ect_fetch_assoc($alldata);
	$iNumOfPages=ceil($rs['bar']/$maxordersperpage);
	ect_free_result($alldata);
	$alldata=ect_query($sSQL.' LIMIT ' . ($maxordersperpage*($CurPage-1)).','.$maxordersperpage) or ect_error();
	$hasdeleted=FALSE;
	$sSQL='SELECT COUNT(*) AS NumDeleted FROM orders WHERE ordStatus=1';
	$result=ect_query($sSQL) or ect_error();
	$rs=ect_fetch_assoc($result);
	if($rs["NumDeleted"] > 0) $hasdeleted=TRUE;
	ect_free_result($result);
?>
<script type="text/javascript" src="popcalendar.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
try{languagetext('<?php print @$adminlang?>');}catch(err){}
function delrec(id){
if(confirm("<?php print jscheck($yyConDel)?>\n")){
	document.psearchform.id.value=id;
	document.psearchform.act.value="delete";
	document.psearchform.submit();
}
}
function authrec(id, currauth){
var aucode;
if(currauth=='')currauth='<?php print $yyManAut?>';
if((aucode=prompt("<?php print jscheck($yyEntAuth)?>",currauth))!=null){
	document.psearchform.id.value=id;
	document.psearchform.act.value="authorize";
	document.psearchform.authcode.value=aucode;
	document.psearchform.submit();
}
}
function unpendrec(id){
if(confirm("<?php print $yyWarni?>This will not make any changes at your payment processor!\n\nRemove pending status of this order?")){
	document.psearchform.id.value=id;
	document.psearchform.act.value="unpending";
	document.psearchform.submit();
}
}
function unmodwarn(id){
<?php	$yyModWar='The customer changed cart contents after creating this order.\\nBefore authorizing this order check order totals carefully.\\n\\nPlease click "OK" to edit the order and check stock levels as stock has not yet been subtracted for this order.';
		if($GLOBALS['useStockManagement']){ ?>
if(confirm("<?php print jscheck($yyWarni)?>\n\n<?php print jscheck($yyModWar)?>")){
	document.location='adminorders.php?doedit=true&id='+id;
}
<?php	}else{ ?>
if(confirm("<?php print jscheck($yyWarni)?>\n\n<?php print jscheck($yyModWar)?>")){
	document.psearchform.id.value=id;
	document.psearchform.act.value="unpending";
	document.psearchform.submit();
}
<?php	} ?>
}
var ctrlset=false;
function setmodstate(evt){
	if(!evt)evt=window.event;
	ctrlset=evt.ctrlKey;
}
function checkcontrol(tt,evt){
	if(!evt)evt=window.event;
	if(typeof(evt.ctrlKey)!='undefined')ctrlset=evt.ctrlKey;
	if(ctrlset){
		maxitems=document.psearchform.maxitems.value;
		for(index=0;index<maxitems;index++){
			isdisabled=eval('document.psearchform.ordStatus'+index+'.disabled');
			if(! isdisabled){
				if(eval('document.psearchform.ordStatus'+index+'.length') > tt.selectedIndex){
					eval('document.psearchform.ordStatus'+index+'.selectedIndex='+tt.selectedIndex);
					eval('document.psearchform.ordStatus'+index+'.options['+tt.selectedIndex+'].selected=true');
				}
			}
		}
	}
}
function checkprinter(tt,evt){
<?php if(strstr(@$_SERVER['HTTP_USER_AGENT'], 'Gecko')){ ?>
if(evt.ctrlKey || evt.altKey || document.psearchform.ctrlmod[document.psearchform.ctrlmod.selectedIndex].value=="1"){
	tt.href+="&printer=true";
	window.location.href=tt.href;
}else if(document.psearchform.ctrlmod[document.psearchform.ctrlmod.selectedIndex].value=="3"){
	tt.href+="&invoice=true";
	window.location.href=tt.href;
}else if(document.psearchform.ctrlmod[document.psearchform.ctrlmod.selectedIndex].value=="2"){
	tt.href+="&doedit=true";
	window.location.href=tt.href;
}
<?php }else{ ?>
theevnt=window.event;
if(theevnt.ctrlKey || document.psearchform.ctrlmod[document.psearchform.ctrlmod.selectedIndex].value=="1")tt.href+="&printer=true";
if(document.psearchform.ctrlmod[document.psearchform.ctrlmod.selectedIndex].value=="3")tt.href+="&invoice=true";
if(document.psearchform.ctrlmod[document.psearchform.ctrlmod.selectedIndex].value=="2")tt.href+="&doedit=true";
<?php } ?>
return(true);
}
function setdumpformat(){
formatindex=document.forms.psearchform.filedump[document.forms.psearchform.filedump.selectedIndex].value;
if(formatindex==1)
	document.psearchform.act.value='dumporders';
else if(formatindex==2)
	document.psearchform.act.value='dumpdetails';
else if(formatindex==3)
	document.psearchform.act.value='quickbooks';
else if(formatindex==4)
	document.psearchform.act.value='ouresolutionsxmldump';
document.psearchform.action='dumporders.php';
document.psearchform.submit();
}
function docheckall(){
	allcbs=document.getElementsByName('ids[]');
	mainidchecked=document.getElementById('xdocheckall').checked;
	for(i=0;i<allcbs.length;i++){
		allcbs[i].checked=mainidchecked;
	}
	return(true);
}
function checkchecked(printorinvoice){
	allcbs=document.getElementsByName('ids[]');
	var onechecked=false;
	for(i=0;i<allcbs.length;i++){
		if(allcbs[i].checked)onechecked=true;
	}
	if(onechecked){
		document.forms.psearchform.action='adminorders.php?'+printorinvoice+'=true&id=multi';
		document.forms.psearchform.submit();
	}else{
		alert("<?php print jscheck($yyNoSelO)?>");
	}
}
function changeselectfield(whichfield){
	var editablefield=document.getElementById(whichfield);
	var editfieldval=editablefield[editablefield.selectedIndex].value;
		if(editfieldval=='orsa'||editfieldval=='orsd'){
		changesortorder(editfieldval)
	}else{
		document.psearchform.reset();
		document.psearchform.action='adminorders.php';
		document.psearchform.id.value=editfieldval;
		document.psearchform.act.value=whichfield;
		document.psearchform.submit();
	}
}
function setCookie(c_name,value,expiredays){
	var exdate=new Date();
	exdate.setDate(exdate.getDate()+expiredays);
	document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}
function changesortorder(ord){
	setCookie('ordersort',ord,600);
	document.forms.psearchform.submit();
}
var dazzleorightml='<br /><br /><span style="background:#FFFFFF;padding:5px"> Please copy your Dazzle / WorldShip file contents below </span><br /><br /><div style="text-align:center"><textarea id="dazzletextarea" rows="18" cols="120" style="white-space:nowrap;overflow:scroll;" wrap="off"></textarea></div><div style="text-align:center"><input type="button" value="Submit" onclick="processdazzle()" /> <input type="button" value="Cancel" onclick="document.getElementById(\'dazzlediv\').style.display=\'none\'" /></div>';
function dodazzle(){
	document.getElementById('dazzleinner').innerHTML=dazzleorightml;
	document.getElementById('dazzlediv').style.display='';
}
function dazupdajaxcallback(){
	if(ajaxobj.readyState==4){
		var restxt=ajaxobj.responseText;
		if(restxt.search('SUCCESS')!=-1){
			var rowid=restxt.split('|')[1];
			document.getElementById('dazdet'+rowid).style.visibility='hidden';
			document.getElementById('dazdet'+rowid).style.display='none';
			document.getElementById('dazrow'+rowid).style.visibility='hidden';
			document.getElementById('dazrow'+rowid).style.display='none';
			document.getElementById('dazhr'+rowid).style.visibility='hidden';
			document.getElementById('dazhr'+rowid).style.display='none';
		}else
			alert('Error updating');
		if(dazisprocall)dazprocall();
	}
}
function dazzleupd(tordid,ttrnum,rowid,hasduplicate){
	var statussel=document.getElementById('dazordstatus');
	ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
	ajaxobj.onreadystatechange=dazupdajaxcallback;
	ajaxobj.open("GET", "ajaxservice.php?action=dazzleupd&rowid="+rowid+"&ordid="+tordid+"&trackno="+encodeURIComponent(ttrnum)+"&emstatus="+(document.getElementById('dazemstatus').checked==true?'1':'0')+"&ordstatus="+statussel[statussel.selectedIndex].value+(hasduplicate?"&noemail=true":''), true);
	ajaxobj.send(null);
}
function dazprocall(){
	dazisprocall=true;
	if(dazall.length>0){
		var tind=dazall.pop();
		if(document.getElementById('dazid'+tind)){
			var hasduplicate=false;
			var tordid=document.getElementById('dazid'+tind).value;
			for(var idind=0;idind<dazall.length;idind++){
				if(document.getElementById('dazid'+dazall[idind])){
					var mordid=document.getElementById('dazid'+dazall[idind]).value;
					if(mordid==tordid) hasduplicate=true;
				}
			}
			dazzleupd(tordid,document.getElementById('daztr'+tind).innerHTML,tind,hasduplicate);
		}else dazprocall();
	}
}
var dazisprocall=false;
var statarr=[];
var dazall=[];
function dazajaxcallback(){
	var allstatus='<select id="dazordstatus" size="1" onchange="setCookie(\'dazordstatus\',this[this.selectedIndex].value,600);"><option value="">No Change</option><?php
			$statarr='';
			for($index=0; $index < $numstatus; $index++){
				if(is_numeric(getget('dazordstatus'))) $wantstatus=(int)getget('dazordstatus'); else $wantstatus=0;
				if($allstatus[$index]['statID']>=3) print '<option value="' . $allstatus[$index]['statID'] . '"' . ($allstatus[$index]['statID']==$wantstatus?' selected="selected"':'') . '>' . jsescape($allstatus[$index]['statPrivate']) . '</option>';
				$statarr.='statarr['.$allstatus[$index]['statID'].']="'.$allstatus[$index]['statPrivate'].'";';
			} ?></select>';
	<?php print $statarr?>
	if(ajaxobj.readyState==4){
		var restxt=ajaxobj.responseText;
		if(restxt=='ERRORFILEFORMAT'){
			alert('Error in file format. Only Dazzle And WorldShip CSV file formats are supported.');
		}else{
			document.getElementById('dazzleinner').innerHTML='<br />&nbsp;<br /><table style="margin:0 auto;" class="cobtbl" cellspacing="1" cellpadding="3" id="dazzletable"><tr><td class="cobhl" colspan="2">Change Status To:'+allstatus+' | Email Status Change: <input type="checkbox" id="dazemstatus" value="ON" onchange="setCookie(\'dazemstatus\',this.checked?1:0,600);" <?php if(@$_COOKIES['dazemstatus']=='1') print 'checked="checked" '?>/></td><td class="cobhl"><input type="button" value="Process All" onclick="dazprocall()" /></td></tr></table><br /><input type="button" value="Close Window" onclick="document.getElementById(\'dazzlediv\').style.display=\'none\'" />';
			var thetable=document.getElementById('dazzletable');
			var tarr=restxt.split('==DAZZLELINE==');
			for(var tind=1;tind<tarr.length;tind++){
				var newrow=thetable.insertRow(-1);
				newrow.id="dazdet"+tind;
				newrow.className='cobhl';
				var tlin=tarr[tind].split('==MATCHLINE==');
				var origdets=tlin[0].split('==ORIGADD==');
				newcell=newrow.insertCell(0);
				newcell.innerHTML=origdets[1];

				newcell=newrow.insertCell(1);
				newcell.innerHTML='<div id="daztr'+tind+'">'+origdets[0]+'</div>';

				newcell=newrow.insertCell(2);
				if(tlin.length<2){
					newcell.innerHTML='No Match';
				}else{
					dazall.push(tind);
					newcell.innerHTML=' - ';
					var newrow=thetable.insertRow(-1);
					newrow.id="dazrow"+tind;
					var ordstatus=0;
					var ordid=0;
					newrow.className='cobll';
					newcell=newrow.insertCell(0);
					newcell.className='cobll';
					var seltxt=tlin.length>2?'<select id="dazsel'+tind+'" size="1" onchange="document.getElementById(\'dazid'+tind+'\').value=this[this.selectedIndex].value.split(\'|\')[0];document.getElementById(\'dazstat'+tind+'\').innerHTML=statarr[this[this.selectedIndex].value.split(\'|\')[1]]">':'';
					for(var tind2=1;tind2<tlin.length;tind2++){
						linspl=tlin[tind2].split('==FULLADD==');
						ordid=linspl[0].split('|')[0];
						seltxt+=(tlin.length>2?'<option value="'+linspl[0]+'">':'')+ordid+' - '+linspl[1]+(tlin.length>2?'</option>':'');
					}
					ordstatus=tlin[1].split('==FULLADD==')[0].split('|')[1];
					newcell.innerHTML=seltxt+(tlin.length>2?'</select>':'')+'<input type="hidden" id="dazid'+tind+'" value="'+tlin[1].split('==FULLADD==')[0].split('|')[0]+'" />';

					newcell=newrow.insertCell(1);
					newcell.className='cobll';
					newcell.id='dazstat'+tind;
					newcell.innerHTML=statarr[ordstatus];

					newcell=newrow.insertCell(2);
					newcell.className='cobll';
					newcell.innerHTML='<input type="button" value="Update" onclick="dazisprocall=false;dazzleupd(document.getElementById(\'dazid'+tind+'\').value,\''+origdets[0]+'\','+tind+',false)" />';
				}
				var newrow=thetable.insertRow(-1);
				newrow.id="dazhr"+tind;
				newrow.className='cobll';
				newcell=newrow.insertCell(0);
				newcell.colSpan=3;
				newcell.innerHTML='<hr width="80%">';
			}
		}
	}
}
function processdazzle(){
	var dazzletext=encodeURIComponent(document.getElementById('dazzletextarea').value);
	if(dazzletext==''){
		alert("No input specified.");
	}else{
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange=dazajaxcallback;
		ajaxobj.open("POST", "ajaxservice.php?action=dazzle", true);
		ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxobj.send('dazzletext='+dazzletext);
	}
}
/* ]]> */
</script>
<div id="dazzlediv" style="display:none;position:absolute;width:100%;height:2000px;background-image:url(adminimages/opaquepixel.png);top:0px;left:0px;text-align:center;z-index:10000;"><br /><br /><br /><br /><br /><br /><div id="dazzleinner" style="margin:0 auto;background:#FFFFFF;width:800px;height:600px"></div></div>
<?php	$themask='yyyy-mm-dd';
		if($admindateformat==1)
			$themask='mm/dd/yyyy';
		elseif($admindateformat==2)
			$themask='dd/mm/yyyy';
		if(! $success) print '<div style="text-align:center;color:#FF0000">' . $errmsg . '</div>';
		if(getpost('act')=='editablefield') $editablefield=getpost('id'); else $editablefield=@$_COOKIE['editablefield'];
		if(getpost('act')=='searchfield') $searchfield=getpost('id'); else $searchfield=@$_COOKIE['searchfield'];
		if(getpost('ordersearchfield')!='') $ordersearchfield=getpost('ordersearchfield'); else $ordersearchfield=@$_COOKIE['ordersearchfield'];
		$_SESSION['fromdate']=$fromdate;
		$_SESSION['todate']=$todate;
		$_SESSION['notstatus']=@$_REQUEST['notstatus'];
		$_SESSION['notsearchfield']=@$_REQUEST['notsearchfield'];
		$_SESSION['searchtext']=$origsearchtext;
		$_SESSION['ordStatus']=@$_REQUEST['ordStatus'];
		$_SESSION['ordstate']=@$_REQUEST['ordstate'];
		$_SESSION['ordcountry']=@$_REQUEST['ordcountry'];
		$_SESSION['payprovider']=@$_REQUEST['payprovider']; ?>
<h2><?php print $yyAdmOrd?></h2>
<form method="post" action="adminorders.php<?php if($CurPage!=1) print '?pg='.$CurPage?>" name="psearchform">
            <input type="hidden" name="act" value="" />
			<input type="hidden" name="id" value="" />
			<input type="hidden" name="authcode" value="" />
            <input type="hidden" name="theeditablefield" value="<?php print $editablefield?>" />
			<input type="hidden" name="thesearchfield" value="<?php print $searchfield?>" />
            <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr>
                <td class="cobhl" align="right" width="25%" style="white-space:nowrap"><strong><?php print $yyOrdFro?>:</strong></td>
				<td class="cobll" align="left" width="25%" style="white-space:nowrap"><input type="text" size="14" name="fromdate" value="<?php print $fromdate; ?>" /> <input type="button" onclick="popUpCalendar(this, document.forms.psearchform.fromdate, '<?php print $themask?>', 0)" value='DP' /> <input type="button" onclick="document.forms.psearchform.fromdate.value='<?php print date($admindatestr, time() + ($dateadjust*60*60))?>'" value="<?php print $yyToday?>" /></td>
				<td class="cobhl" align="right" width="16%" style="white-space:nowrap"><strong><?php print $yyOrdTil?>:</strong></td>
				<td class="cobll" align="left" width="34%" style="white-space:nowrap"><input type="text" size="14" name="todate" value="<?php print $todate; ?>" /> <input type="button" onclick="popUpCalendar(this, document.forms.psearchform.todate, '<?php print $themask?>', -205)" value='DP' /> <input type="button" onclick="document.forms.psearchform.todate.value='<?php print date($admindatestr, time() + ($dateadjust*60*60))?>'" value="<?php print $yyToday?>" /></td>
			  </tr>
			  <tr>
				<td class="cobhl" align="center" style="white-space:nowrap"><strong><?php print $yyOrdSta?></strong>&nbsp;&nbsp;<input type="checkbox" name="notstatus" value="ON" <?php if(getrequest('notstatus')=='ON') print 'checked '?>/><strong>...<?php print $yyNot?></strong></td>
				<td class="cobhl" align="center" style="white-space:nowrap"><select name="searchfield" id="searchfield" size="1" onchange="changeselectfield('searchfield')">
					<option value="state" <?php if($searchfield=='state') print 'selected="selected"'?>><?php print $yyState?></option>
					<option value="country" <?php if($searchfield=='country') print 'selected="selected"'?>><?php print $yyCountry?></option>
					<option value="payprovider" <?php if($searchfield=='payprovider' || $searchfield=='') print 'selected="selected"'?>><?php print $yyPayMet?></option>
					</select>&nbsp;&nbsp;<input type="checkbox" name="notsearchfield" value="ON" <?php if(getrequest('notsearchfield')=='ON') print "checked "?>/><strong>...<?php print $yyNot?></strong></td>
				<td class="cobhl" align="right" style="white-space:nowrap"><strong><?php print $yySeaTxt?>:</strong></td>
				<td class="cobll" align="left" style="white-space:nowrap"><input type="text" size="24" name="searchtext" value="<?php print $origsearchtext?>" />&nbsp;<select name="ordersearchfield" size="1">
					<option value="ordid" <?php if($ordersearchfield=='ordid') print 'selected="selected"'?>><?php print $xxOrdId?></option>
					<option value="email" <?php if($ordersearchfield=='email') print 'selected="selected"'?>><?php print $yyEmail?></option>
					<option value="authcode" <?php if($ordersearchfield=='authcode') print 'selected="selected"'?>><?php print $yyAutCod?></option>
					<option value="name" <?php if($ordersearchfield=='name') print 'selected="selected"'?>><?php print $yyName?></option>
					<option value="product" <?php if($ordersearchfield=='product') print 'selected="selected"'?>><?php print $yyPrName?>/ID</option>
					<option value="address" <?php if($ordersearchfield=='address') print 'selected="selected"'?>><?php print $yyAddress?></option>
					<option value="zip" <?php if($ordersearchfield=='zip') print 'selected="selected"'?>><?php print $yyZip?></option>
					<option value="phone" <?php if($ordersearchfield=='phone') print 'selected="selected"'?>><?php print $yyTelep?></option>
					<option value="invoice" <?php if($ordersearchfield=='invoice') print 'selected="selected"'?>><?php print $yyInvNum?></option>
					<option value="tracknum" <?php if($ordersearchfield=='tracknum') print 'selected="selected"'?>><?php print $yyTraNum?></option>
					<option value="affiliate" <?php if($ordersearchfield=='affiliate') print 'selected="selected"'?>><?php print $yyAffili?></option>
<?php				if(@$extraorderfield1!='') print '<option value="extra1" ' . ($ordersearchfield=='extra1' ? 'selected="selected"' : '') . '>' . htmlspecials(substr(strip_tags($extraorderfield1), 0, 16)) . '</option>';
					if(@$extraorderfield2!='') print '<option value="extra2" ' . ($ordersearchfield=='extra2' ? 'selected="selected"' : '') . '>' . htmlspecials(substr(strip_tags($extraorderfield2), 0, 16)) . '</option>';
					if(@$extracheckoutfield1!='') print '<option value="checkout1" ' . ($ordersearchfield=='checkout1' ? 'selected="selected"' : '') . '>' . htmlspecials(substr(strip_tags($extracheckoutfield1), 0, 16)) . '</option>';
					if(@$extracheckoutfield2!='') print '<option value="checkout2" ' . ($ordersearchfield=='checkout2' ? 'selected="selected"' : '') . '>' . htmlspecials(substr(strip_tags($extracheckoutfield2), 0, 16)) . '</option>';
?>					</select>
				</td>
			  </tr>
			  <tr>
				<td class="cobll" align="center">
		<select name="ordStatus[]" size="5" multiple="multiple"><?php
		$ordstatus="";
		$addcomma="";
		if(is_array(@$_REQUEST['ordStatus'])){
			foreach($_REQUEST['ordStatus'] as $objValue){
				if(is_array($objValue))$objValue=$objValue[0];
				$ordstatus.=$addcomma . $objValue;
				$addcomma=",";
			}
		}else
			$ordstatus=getrequest('ordStatus');
		$ordstatusarr=explode(",", $ordstatus);
		for($index=0; $index < $numstatus; $index++){
			print '<option value="' . $allstatus[$index]["statID"] . '"';
			if(is_array($ordstatusarr)){
				foreach($ordstatusarr as $objValue)
					if($objValue==$allstatus[$index]["statID"]) print ' selected="selected"';
			}
			print ">" . $allstatus[$index]["statPrivate"] . "</option>";
		} ?></select></td>
				<td class="cobll" align="center">
<?php
	if(@$searchfield=='state'){ ?>
		<select name="ordstate[]" size="5" multiple="multiple"><?php
		$ordstate=@$_REQUEST['ordstate'];
		$sSQL='SELECT stateID,stateName,stateAbbrev FROM states WHERE stateCountryID=' . $origCountryID . ' AND stateEnabled=1 ORDER BY stateName';
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){
			print '<option value="' . htmlspecials(@$usestateabbrev==TRUE?$rs['stateAbbrev']:$rs['stateName']) . '"';
			if(is_array($ordstate)){
				foreach($ordstate as $objValue){
					if($objValue==(@$usestateabbrev==TRUE?$rs['stateAbbrev']:$rs['stateName'])) print ' selected="selected"';
				}
			}
			print '>' . $rs['stateName'] . "</option>\n";
		}
		ect_free_result($result); ?></select><?php
	}elseif(@$searchfield=='country'){ ?>
		<select name="ordcountry[]" size="5" multiple="multiple"><?php
		$ordcountry=@$_REQUEST['ordcountry'];
		$sSQL="SELECT countryID,countryName FROM countries WHERE countryEnabled=1 ORDER BY countryOrder DESC, countryName";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){
			print '<option value="' . htmlspecials($rs['countryName']) . '"';
			if(is_array($ordcountry)){
				foreach($ordcountry as $objValue){
					if($objValue==$rs['countryName']) print ' selected="selected"';
				}
			}
			print '>' . $rs['countryName'] . "</option>\n";
		}
		ect_free_result($result); ?></select><?php
	}else{ ?>
		<select name="payprovider[]" size="5" multiple="multiple"><?php
		$payprovider=@$_REQUEST['payprovider'];
		$sSQL="SELECT payProvID,payProvName FROM payprovider WHERE payProvEnabled=1 ORDER BY payProvOrder";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){
			print '<option value="' . $rs['payProvID'] . '"';
			if(is_array($payprovider)){
				foreach($payprovider as $objValue){
					if($objValue==$rs['payProvID']) print ' selected="selected"';
				}
			}
			print '>' . $rs['payProvName'] . '</option>';
		}
		ect_free_result($result); ?></select>
<?php
	} ?>
				</td>
				<td class="cobhl" colspan="2" align="center">
				<select name="filedump" size="1">
					<option value="1"><?php print $yyDmpOrd?></option>
					<option value="2"><?php print $yyDmpDet?></option>
<?php
	if(@$ouresolutionsxml!='') print '<option value="4">OurESolutions XML format</option>'; ?>
					</select> <input type="button" value="<?php print $yyGo?>" onclick="setdumpformat()" /> <input type="button" value="<?php print $yyNewOrd?>" onclick="document.forms.psearchform.action='adminorders.php?id=new';document.forms.psearchform.submit();" /><br /><br />
				  <input type="submit" value="<?php print $yySearch?>" onclick="document.forms.psearchform.action='adminorders.php';" /> <input type="button" value="Stats" onclick="document.forms.psearchform.action='adminstats.php';document.forms.psearchform.submit();" />
				  <input type="button" value="<?php print $yyPakSps?>" onclick="checkchecked('printer')" /> <input type="button" value="<?php print $yyInvces?>" onclick="checkchecked('invoice')" />
				  <input type="button" value="<?php if($origCountryID==1) print ' Dazzle / '?>WorldShip" onclick="dodazzle()" />
				</td>
			  </tr>
			</table>
<br/>
			<table width="100%" class="stackable admin-table-a sta-white">
			  <tr>
				<th class="acenter" width="1%"><input type="checkbox" id="xdocheckall" value="1" onclick="docheckall()" /></th>
                <th class="acenter"><strong><a href="javascript:changesortorder('<?php print ($sortorder=='oida'?'oidd':'oida')?>')"><?php print $yyOrdId?></a></strong></th>
				<th class="aleft"><strong><a href="javascript:changesortorder('<?php print ($sortorder=='orna'?'ornd':'orna')?>')"><?php print $yyName?></a></strong></th>
				<th class="acenter"><strong><a href="javascript:changesortorder('<?php print ($sortorder=='orpa'?'orpd':'orpa')?>')"><?php print $yyMethod?></a></strong></th>
				<th class="acenter" width="1%"><strong>AVS</strong></th>
				<th class="acenter" width="1%"><strong>CVV</strong></th>
				<th class="acenter"><strong><a href="javascript:changesortorder('<?php print ($sortorder=='oraa'?'orad':'oraa')?>')"><?php print $yyAutCod?></a></strong></th>
				<th class="acenter"><strong><a href="javascript:changesortorder('<?php print ($sortorder=='orda'?'ordd':'orda')?>')"><?php print $yyDate?></a></strong></th>
				<th class="acenter"><select name="editablefield" id="editablefield" size="1" onchange="changeselectfield('editablefield')">
					<option value="status"><?php print $yyStatus?></option>
					<option value="tracknum" <?php if($editablefield=='tracknum') print 'selected="selected"'?>><?php print $yyTraNum?></option>
					<option value="invoice" <?php if($editablefield=='invoice') print 'selected="selected"'?>><?php print $yyInvNum?></option>
					<option value="email" <?php if($editablefield=='email') print 'selected="selected"'?>><?php print $yyEmail?></option>
					<option value="" disabled="disabled">---------------</option>
					<option value="<?php print ($sortorder=='orsa'?'orsd':'orsa')?>">Sort On Column<?php print ($sortorder=='orsa'?' DESC':'')?></option>
				</select></th>
			  </tr>
<?php
	if(ect_num_rows($alldata) > 0){
		$rowcounter=0;
		$ordTot=0;
		while($rs=ect_fetch_assoc($alldata)){
			if($rs['ordStatus']>=3) $ordTot+=$rs['ordTot'];
			if(trim($rs['ordAuthNumber'])==''){
				$startfont='<span style="color:#FF0000">';
				$endfont='</span>';
			}else{
				$startfont='';
				$endfont='';
			}
			if(@$bgcolor=='cobll') $bgcolor='cobhl'; else $bgcolor='cobll';
				if($rs['ordAuthStatus']=='MODWARNOPEN' || $rs['ordShipType']=='MODWARNOPEN') $bgcolor='cobwarn';
?>			  <tr class="<?php print $bgcolor?>">
				<td align="center"><input type="checkbox" name="ids[]" value="<?php print $rs['ordID']?>" /></td>
				<td align="center"><a onclick="return(checkprinter(this,event));" href="adminorders.php?id=<?php print $rs['ordID']?>"><?php print '<strong>' . $startfont . $rs['ordID'] . $endfont . '</strong>'?></a></td>
				<td><a onclick="return(checkprinter(this,event));" href="adminorders.php?id=<?php print $rs['ordID']?>"><?php print $startfont . htmlspecialsucode(trim($rs['ordName'].' '.$rs['ordLastName'])) . $endfont?></a></td>
				<td align="center"><?php print $startfont . htmlspecials($rs['payProvName']) . ($rs['payProvName']=='PayPal' && trim($rs['ordTransID'])!='' ? ' CC' : '') . $endfont?></td>
				<td align="center" width="1%"><?php if(trim($rs['ordAVS'])!='') print htmlspecials($rs['ordAVS']); else print '&nbsp;' ?></td>
				<td align="center" width="1%"><?php if(trim($rs['ordCVV'])!='') print htmlspecials($rs['ordCVV']); else print '&nbsp;' ?></td>
				<td align="center"><?php
					if($rs['ordAuthStatus']=='MODWARNOPEN' || $rs['ordShipType']=='MODWARNOPEN'){
						$isauthorized=FALSE;
						print '<input type="button" value="' . $yyMoWarn . '" onclick="unmodwarn(\'' . $rs['ordID'] . '\')" /><br />';
					}else{
						if(trim($rs['ordAuthStatus'])!='') print '<input type="button" value="' . $rs['ordAuthStatus'] . '" onclick="unpendrec(\'' . $rs['ordID'] . '\')" /><br />';
						if(trim($rs['ordAuthNumber'])==''){
							$isauthorized=FALSE;
							print '<input type="button" name="auth" value="' . $yyAuthor . '" onclick="authrec(\'' . $rs['ordID'] . '\',\'\')" />';
						}else{
							print '<a href="#" title="' . FormatEuroCurrency($rs['ordTot']) . '" onclick="authrec(\'' . $rs['ordID'] . '\',\''.$rs['ordAuthNumber'].'\');return(false);">' . $startfont . $rs['ordAuthNumber'] . $endfont . '</a>';
							$isauthorized=TRUE;
						}
					}
				?></td>
				<td align="center"><span style="font-size:10px"><?php print $startfont . date($admindatestr . "\<\\b\\r\ />H:i:s", strtotime($rs["ordDate"])) . $endfont?></span></td>
				<td align="center"><input type="hidden" name="ordid<?php print $rowcounter?>" value="<?php print $rs['ordID']?>" />
<?php		if($editablefield=='tracknum')
				print '<input type="text" name="tracknum'.$rowcounter.'" size="24" value="' . $rs['ordTrackNum'] . '" />';
			elseif($editablefield=='invoice')
				print '<input type="text" name="invoice'.$rowcounter.'" size="24" value="' . $rs['ordInvoice'] . '" />';
			elseif($editablefield=='email')
				print '<input type="text" name="email'.$rowcounter.'" size="34" value="' . $rs['ordEmail'] . '" />';
			else{ ?>
					<select name="ordStatus<?php print $rowcounter?>" size="1" onclick="setmodstate(event)" onchange="checkcontrol(this,event)"<?php if($rs['ordPayProvider']==20) print ' disabled'?>><?php
						$gotitem=FALSE;
						for($index=0; $index<$numstatus; $index++){
							if(! $isauthorized && $allstatus[$index]['statID']>2) break;
							if(! ($rs['ordStatus']!=2 && $allstatus[$index]['statID']==2)){
								print '<option value="' . $allstatus[$index]['statID'] . '"';
								if($rs['ordStatus']==$allstatus[$index]['statID']){
									print ' selected="selected"';
									$gotitem=TRUE;
								}
								print '>' . $allstatus[$index]['statPrivate'] . '</option>';
							}
						}
						if(! $gotitem) print '<option value="'.$allstatus[$index]['statID'].'" selected="selected">' . $yyUndef . '</option>' ?></select>
<?php		} ?>
				</td>
			  </tr>
<?php		$rowcounter++;
		}
		if($iNumOfPages>1){
			$pblink='<a class="ectlink" href="adminorders.php?';
			foreach(@$_REQUEST as $objQS => $objValue)
				if($objQS!='pg'&&$objValue!=''&&($objQS=='searchtext'||$objQS=='ordersearchfield'||$objQS=='notstatus'||$objQS=='notsearchfield'||$objQS=='fromdate'||$objQS=='todate')) $pblink.=urlencode($objQS) . '=' . urlencode($objValue) . '&amp;';
			if(is_array(@$_REQUEST['ordStatus'])){
				foreach($_REQUEST['ordStatus'] AS $objQS => $objValue)
					$pblink.='ordStatus[]='.$objValue.'&amp;';
			}elseif(@$_REQUEST['ordStatus']!='')
				$pblink.='ordStatus[]='.$_REQUEST['ordStatus'];
			if(is_array(@$_REQUEST['payprovider']))
				foreach($_REQUEST['payprovider'] AS $objQS => $objValue)
					$pblink.='payprovider[]='.$objValue.'&amp;';
			if(is_array(@$_REQUEST['state']))
				foreach($_REQUEST['state'] AS $objQS => $objValue)
					$pblink.='state[]='.$objValue.'&amp;';
			if(is_array(@$_REQUEST['country']))
				foreach($_REQUEST['country'] AS $objQS => $objValue)
					$pblink.='country[]='.$objValue.'&amp;';
			$pblink.='pg=';
			print '<tr class="cobll" style="line-height:30px"><td colspan="9" align="center">' . writepagebar($CurPage,$iNumOfPages,$GLOBALS['yyPrev'],$GLOBALS['yyNext'],$pblink,FALSE) . '</td></tr>';
		} ?>
			  <tr class="cobll">
				<td>&nbsp;</td>
				<td align="center"><?php print FormatEuroCurrency($ordTot)?></td>
				<td align="center"><?php if($hasdeleted){ ?><input type="submit" value="<?php print $yyPurDel?>" onclick="document.psearchform.action='adminorders.php';document.psearchform.act.value='purge';" /><?php }else print '&nbsp;'; ?></td>
				<td align="center" colspan="5"><select name="ctrlmod" size="1"><option value="0"><?php print $yyVieDet?></option><option value="1"><?php print $yyPPSlip?></option><option value="3"><?php print $yyPPInv?></option><option value="2" <?php if(getpost('ctrlmod')=='2') print 'selected="selected"';?>><?php print $yyEdOrd?></option></select>
				&nbsp;&nbsp;&nbsp;<input type="checkbox" name="emailstat" value="1" <?php if(getpost('emailstat')=="1" || @$alwaysemailstatus==TRUE) print "checked"?>/> <?php print $yyEStat?></td>
				<td align="center"><input type="hidden" name="maxitems" value="<?php print $rowcounter?>" /><input type="submit" value="<?php print $yyUpdate?>" onclick="document.forms.psearchform.action='adminorders.php<?php if($CurPage!=1) print '?pg='.$CurPage?>';document.psearchform.act.value='status';" /> <input type="reset" value="<?php print $yyReset?>" /></td>
			  </tr>
<?php
	}else{
?>
			  <tr class="cobll"> 
                <td colspan="9" width="100%" align="center">
					<p>&nbsp;</p>
					<p><?php print $yyNoMat1;?></p>
					<p>&nbsp;</p>
				</td>
			  </tr>
			  <?php if($hasdeleted){ ?>
			  <tr class="cobll">
				<td colspan="2">&nbsp;</td>
				<td width="20%" align="center"><input type="submit" value="<?php print $yyPurDel?>" onclick="document.psearchform.action='adminorders.php';document.psearchform.act.value='purge';" /></td>
				<td colspan="6">&nbsp;</td>
			  </tr>
			  <?php } ?>
<?php
	}
	ect_free_result($alldata); ?>
			</table>
			<table width="100%" border="0" cellspacing="1" cellpadding="2">
			  <tr> 
                <td colspan="4" width="100%" align="center">
				  <p><br />
					<a href="adminorders.php?fromdate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thefromdate)-1,date("d",$thefromdate),date("Y",$thefromdate)))?>&amp;todate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thetodate)-1,date("d",$thetodate),date("Y",$thetodate)))?>"><strong>- <?php print $yyMonth?></strong></a> | 
					<a href="adminorders.php?fromdate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thefromdate),date("d",$thefromdate)-7,date("Y",$thefromdate)))?>&amp;todate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thetodate),date("d",$thetodate)-7,date("Y",$thetodate)))?>"><strong>- <?php print $yyWeek?></strong></a> | 
					<a href="adminorders.php?fromdate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thefromdate),date("d",$thefromdate)-1,date("Y",$thefromdate)))?>&amp;todate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thetodate),date("d",$thetodate)-1,date("Y",$thetodate)))?>"><strong>- <?php print $yyDay?></strong></a> | 
					<a href="adminorders.php"><strong><?php print $yyToday?></strong></a> | 
					<a href="adminorders.php?fromdate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thefromdate),date("d",$thefromdate)+1,date("Y",$thefromdate)))?>&amp;todate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thetodate),date("d",$thetodate)+1,date("Y",$thetodate)))?>"><strong><?php print $yyDay?> +</strong></a> | 
					<a href="adminorders.php?fromdate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thefromdate),date("d",$thefromdate)+7,date("Y",$thefromdate)))?>&amp;todate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thetodate),date("d",$thetodate)+7,date("Y",$thetodate)))?>"><strong><?php print $yyWeek?> +</strong></a> | 
					<a href="adminorders.php?fromdate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thefromdate)+1,date("d",$thefromdate),date("Y",$thefromdate)))?>&amp;todate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thetodate),date("d",$thetodate)+1,date("Y",$thetodate)))?>"><strong><?php print $yyMonth?> +</strong></a>
				  </p>
				</td>
			  </tr>
			</table>
		  </form>
<?php
}
?>