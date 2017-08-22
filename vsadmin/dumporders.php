<?php
@include 'adminsession.php';
session_cache_limiter('none');
session_start();
ob_start();
//=========================================
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property
//of Internet Business Solutions SL. Any use, reproduction, disclosure or copying
//of any kind without the express and written permission of Internet Business 
//Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
include "db_conn_open.php";
include "includes.php";
include "inc/incfunctions.php";
if(@$dateadjust=='') $dateadjust=0;
@ob_clean();
function twodp($theval){
	return(number_format($theval,2,'.',''));
}
function xmlstrip($name2){
	$name2=str_replace(
		array('&','’','–','-',"'",'€','£','é','è','™','ú','á','ñ','ü','ö','®','"','“','”','©','å'),
		array('chr(11)','chr(146)','chr(150)','chr(150)','chr(39)chr(39)','chr(128)','chr(163)','chr(130)','chr(138)','','u','a','n','chr(129)','chr(148)','','','','','','a'),
		$name2);
	$tmp_str="";
	for($i=0; $i < strlen($name2); $i++){
		$ch_code=ord(substr($name2,$i,1));
		if($ch_code>130) $tmp_str.='chr(' . $ch_code . ')'; else $tmp_str.=substr($name2,$i,1);
	}
	return($tmp_str);
}
function getsearchparams(){
	global $sd, $ed, $dateadjust;
	$tmpsql='';
	$hasfromdate=FALSE;
	$hastodate=FALSE;
	$fromdate=trim(@$_REQUEST['fromdate']);
	$todate=trim(@$_REQUEST['todate']);
	if($fromdate != ''){
		$hasfromdate=TRUE;
		if(is_numeric($fromdate))
			$thefromdate=time()-($fromdate*60*60*24);
		else
			$thefromdate=parsedate($fromdate);
	}else
		$thefromdate=strtotime(date('Y-m-d', time()+($dateadjust*60*60)));
	if($todate != ''){
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
	$origsearchtext=unstripslashes(@$_POST['searchtext']);
	$searchtext=escape_string(unstripslashes(@$_POST['searchtext']));
	$ordersearchfield=trim(@$_POST['ordersearchfield']);
	$ordstatus=@$_POST['ordStatus'];
	$ordstate=@$_POST['ordstate'];
	$ordcountry=@$_POST['ordcountry'];
	$payprovider=@$_POST['payprovider'];
	if($ordersearchfield=='ordid' && $searchtext != '' && is_numeric($searchtext)){
		$tmpsql.=" WHERE ordID='" . $searchtext . "' ";
	}else{
		if(is_array($ordstatus)) $tmpsql.=' WHERE ' . (@$_POST['notstatus']=='ON' ? 'NOT ' : '') . '(ordStatus IN (' . implode(',', $ordstatus) . '))'; else $tmpsql.=' WHERE ordStatus<>1';
		if(is_array($ordstate)) $tmpsql.=' AND ' . (@$_POST['notsearchfield']=='ON' ? 'NOT ' : '') . "(ordState IN ('" . implode("','", $ordstate) . "'))";
		if(is_array($ordcountry)) $tmpsql.=' AND ' . (@$_POST['notsearchfield']=='ON' ? 'NOT ' : '') . "(ordCountry IN ('" . implode("','", $ordcountry) . "'))";
		if(is_array($payprovider)) $tmpsql.=' AND ' . (@$_POST['notsearchfield']=='ON' ? 'NOT ' : '') . '(ordPayprovider IN (' . implode(',', $payprovider) . '))';
		if($hasfromdate)
			$tmpsql.=" AND ordDate BETWEEN '" . date('Y-m-d', $thefromdate) . "' AND '" . date('Y-m-d', ($hastodate ? $thetodate+96400 : $thefromdate+96400)) . "'";
		elseif($searchtext=='' && $ordstatus=='' && $ordstate=='' && $ordcountry=='' && $payprovider=='')
			$tmpsql.=" AND ordDate BETWEEN '" . date('Y-m-d', time()+($dateadjust*60*60)) . "' AND '" . date('Y-m-d', time()+($dateadjust*60*60)+96400) . "'";
		if($searchtext != ''){
			if($ordersearchfield=='ordid')
				$tmpsql.=" AND (ordEmail LIKE '%" . $searchtext . "%' OR ordName LIKE '%" . $searchtext . "%' OR ordLastName LIKE '%" . $searchtext . "%')";
			elseif($ordersearchfield=='email')
				$tmpsql.=" AND ordEmail LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='authcode')
				$tmpsql.=" AND (ordAuthNumber LIKE '%" . $searchtext . "%' OR ordTransID LIKE '%" . $searchtext . "%')";
			elseif($ordersearchfield=='name')
				$tmpsql.=" AND (ordName LIKE '%" . $searchtext . "%' OR ordLastName LIKE '%" . $searchtext . "%')";
			elseif($ordersearchfield=='address')
				$tmpsql.=" AND (ordAddress LIKE '%" . $searchtext . "%' OR ordAddress2 LIKE '%" . $searchtext . "%' OR ordCity LIKE '%" . $searchtext . "%' OR ordState LIKE '%" . $searchtext . "%')";
			elseif($ordersearchfield=='phone')
				$tmpsql.=" AND ordPhone LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='zip')
				$tmpsql.=" AND ordZip LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='invoice')
				$tmpsql.=" AND ordInvoice LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='affiliate')
				$tmpsql.=" AND ordAffiliate='" . $searchtext . "'";
		}
	}
	return($tmpsql);
}
if(@$storesessionvalue=="") $storesessionvalue="virtualstore";
if(@$_SESSION["loggedon"] != $storesessionvalue || @$disallowlogin==TRUE){
	if(@$_SERVER["HTTPS"]=="on" || @$_SERVER["SERVER_PORT"]=="443")$prot='https://';else $prot='http://';
	header('Location: '.$prot.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/login.php');
	exit;
}
$hasdetails=(@$_POST['act']=='dumpdetails');
header('Content-type: unknown/exe');
if(@$_POST['act']=='stockinventory')
	header('Content-Disposition: attachment;filename=stockinventory.csv');
elseif(@$_POST['act']=='productimages')
	header('Content-Disposition: attachment;filename=productimages.csv');
elseif(@$_POST['act']=='dump2COinventory')
	header('Content-Disposition: attachment;filename=inventory2co.csv');
elseif(@$_POST['act']=='fullinventory')
	header('Content-Disposition: attachment;filename=inventory.csv');
elseif(@$_POST['act']=='catinventory')
	header('Content-Disposition: attachment;filename=categoryinventory.csv');
elseif(@$_POST['act']=='dumpaffiliate')
	header('Content-Disposition: attachment;filename=affilreport.csv');
elseif(@$_POST['act']=='quickbooks'){
}elseif(@$_POST['act']=='ouresolutionsxmldump'){
	header('Content-Disposition: attachment;filename=oes_ordersdata.xml');
}elseif(@$_POST['act']=='dumpemails'){
	header('Content-Disposition: attachment;filename=mailinglist.csv');
}elseif(@$_POST['act']=='dumpevents'){
	header('Content-Disposition: attachment;filename=eventlog.csv');
}elseif($hasdetails)
	header('Content-Disposition: attachment;filename=orderdetails.csv');
else
	header('Content-Disposition: attachment;filename=dumporders.csv');
$alreadygotadmin=getadminsettings();
$admindatestr='Y-m-d';
if(@$admindateformat=='') $admindateformat=0;
if($admindateformat==1)
	$admindatestr='m/d/Y';
elseif($admindateformat==2)
	$admindatestr='d/m/Y';
if(@$_POST['sd'] != '')
	$sd=@$_POST['sd'];
elseif(@$_GET['sd'] != '')
	$sd=@$_GET['sd'];
else
	$sd=date($admindatestr);
if(@$_POST['ed'] != '')
	$ed=@$_POST['ed'];
elseif(@$_GET['ed'] != '')
	$ed=@$_GET['ed'];
else
	$ed=date($admindatestr);
$sd=parsedate($sd);
$ed=parsedate($ed);
$sslok=TRUE;
if(@$_SERVER["HTTPS"] != "on" && (@$_SERVER["SERVER_PORT"] != "443") && @$nochecksslserver != TRUE) $sslok=FALSE;
if(@$_POST["act"]=="dumpaffiliate"){
	print "Affiliate report for " . date($admindatestr, $sd) . " to " . date($admindatestr, $ed) . "\r\n";
	print '"ID","Name","Address","City","State","Zip","Country","Email","Total"' . "\r\n";
	$sSQL="SELECT affilID,affilName,affilAddress,affilCity,affilState,affilZip,affilCountry,affilEmail FROM affiliates ORDER BY affilID";
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		print '"' . str_replace('"','""',$rs["affilID"]) . '",';
		print '"' . str_replace('"','""',$rs["affilName"]) . '",';
		print '"' . str_replace('"','""',$rs["affilAddress"]) . '",';
		print '"' . str_replace('"','""',$rs["affilCity"]) . '",';
		print '"' . str_replace('"','""',$rs["affilState"]) . '",';
		print '"' . str_replace('"','""',$rs["affilZip"]) . '",';
		print '"' . str_replace('"','""',$rs["affilCountry"]) . '",';
		print '"' . str_replace('"','""',$rs["affilEmail"]) . '",';
		$sSQL2="SELECT SUM(ordTotal-ordDiscount) AS sumTot FROM affiliates LEFT JOIN orders ON affiliates.affilID=orders.ordAffiliate WHERE affilID='" . $rs["affilID"] . "' AND ordStatus>=3 AND ordDate BETWEEN '" . date("Y-m-d", $sd) . "' AND '" . date("Y-m-d", $ed) . " 23:59:59'";
		$alldata2=ect_query($sSQL2) or ect_error();
		$rs2=ect_fetch_assoc($alldata2);
		print $rs2['sumTot'] . "\r\n";
		ect_free_result($alldata2);
	}
	ect_free_result($result);
}elseif(@$_POST["act"]=="stockinventory"){
	$sSQL2="SELECT pID,pName,pPrice,pInStock,pStockByOpts FROM products";
	$result=ect_query($sSQL2) or ect_error();
	print "pID,pName,pPrice,pInStock,optID,OptionGroup,Option\r\n";
	while($rs=ect_fetch_assoc($result)){
		if((int)$rs['pStockByOpts'] != 0){
			$result2=ect_query("SELECT optID,optGrpName,optName,optStock FROM optiongroup INNER JOIN options ON optiongroup.optGrpID=options.optGroup INNER JOIN prodoptions ON options.optGroup=prodoptions.poOptionGroup WHERE prodoptions.poProdID='" . escape_string($rs["pID"]) . "'") or ect_error();
			while($rs2=ect_fetch_assoc($result2)){
				print '"' . str_replace('"','""',$rs['pID']) . '",';
				print '"' . str_replace('"','""',$rs['pName']) . '",';
				print '"' . $rs['pPrice'] . '",';
				print $rs2['optStock'] . ",";
				print $rs2['optID'] . ",";
				print '"' . str_replace('"','""',$rs2['optGrpName']) . '",';
				print '"' . str_replace('"','""',$rs2['optName']) . '"' . "\r\n";
			}
			ect_free_result($result2);
		}else{
			print '"' . str_replace('"','""',$rs['pID']) . '",';
			print '"' . str_replace('"','""',$rs['pName']) . '",';
			print '"' . $rs['pPrice'] . '",';
			print $rs['pInStock'] . ",,,\r\n";
		}
	}
	ect_free_result($result);
}elseif(@$_POST["act"]=="productimages"){
	$sSQL2='SELECT imageProduct,imageSrc,imageType,imageNumber FROM productimages ORDER BY imageProduct,imageType,imageNumber';
	$result=ect_query($sSQL2) or ect_error();
	print "imageProduct,imageSrc,imageType,imageNumber\r\n";
	while($rs=ect_fetch_assoc($result)){
		print '"' . str_replace('"','""',$rs['imageProduct']) . '",';
		print '"' . str_replace('"','""',$rs['imageSrc']) . '",';
		print $rs['imageType'] . ',' . $rs['imageNumber'] . "\r\n";
	}
	ect_free_result($result);
}elseif(@$_POST['act']=='fullinventory'){
	$fieldlist='pID,pName';
	for($index=2; $index <= $adminlanguages+1; $index++){
		if(($adminlangsettings & 1)==1) $fieldlist.=',pName' . $index;
	}
	$fieldlist.=',pSection,pPrice,pWholesalePrice,pListPrice,pShipping,pShipping2,pWeight,pDisplay,pSell,pBackOrder,pGiftWrap,pExemptions,pInStock,pDims,pTax,pDropship';
	if(@$digidownloads==TRUE) $fieldlist.=',pDownload';
	if(strpos(@$productpagelayout.@$detailpagelayout,'custom1')!==FALSE) $fieldlist.=',pCustom1';
	if(strpos(@$productpagelayout.@$detailpagelayout,'custom2')!==FALSE) $fieldlist.=',pCustom2';
	if(strpos(@$productpagelayout.@$detailpagelayout,'custom3')!==FALSE) $fieldlist.=',pCustom3';
	$fieldlist.=',pStaticPage,pStockByOpts,pRecommend,pOrder,pSKU,pManufacturer,pSearchParams,pTitle,pMetaDesc,pStaticURL,pDescription';
	for($index=2; $index <= $adminlanguages+1; $index++){
		if(($adminlangsettings & 2)==2) $fieldlist.=',pDescription' . $index;
	}
	$fieldlist.=',pLongDescription';
	for($index=2; $index <= $adminlanguages+1; $index++){
		if(($adminlangsettings & 4)==4) $fieldlist.=',pLongDescription' . $index;
	}
	$sSQL='SELECT ' . $fieldlist . ' FROM products';
	$whereand=' WHERE ';
	if(@$_POST['scat']!=''){
		$sectionids=getsectionids($_POST['scat'], TRUE);
		if($sectionids!='') $sSQL.=' WHERE products.pSection IN (' . $sectionids . ')';
		 $whereand=' AND ';
	}
	if(@$_POST['disp']!=''){
		// if(@$_POST['disp']=='4'){ $sSQL.=$whereand . 'rootSection IS NULL'; $whereand=' AND '; }
		if(@$_POST['disp']=='3'){ $sSQL.=$whereand . '(pInStock<=0 AND pStockByOpts=0)'; $whereand=' AND '; }
		if(@$_POST['disp']=='5'){ $sSQL.=$whereand . 'pDisplay<>0'; $whereand=' AND '; }
		if(@$_POST['disp']=='2'){ $sSQL.=$whereand . 'pDisplay=0'; $whereand=' AND '; }
	}
	$result=ect_query($sSQL) or ect_error();
	$fieldlistarr=explode(',', $fieldlist);
	$addcomma='';
	foreach($fieldlistarr as $flarrval){
		print $addcomma;
		print '"' . $flarrval . '"';
		$addcomma=',';
	}
	print "\r\n";
	while($rs=ect_fetch_assoc($result)){
		$addcomma='';
		foreach($fieldlistarr as $flarrval){
			print $addcomma;
			print '"' . str_replace('"','""',$rs[$flarrval]). '"';
			$addcomma=',';
		}
		print "\r\n";
	}
	ect_free_result($result);
}elseif(@$_POST['act']=='catinventory'){
	$fieldlist='sectionID,sectionName';
	for($index=2; $index <= $adminlanguages+1; $index++){
		if(($adminlangsettings & 256)==256) $fieldlist.=',sectionName' . $index;
	}
	$fieldlist.=',sectionWorkingName,sectionImage,topSection,sectionOrder,rootSection,sectionDisabled,sectionURL';
	for($index=2; $index <= $adminlanguages+1; $index++){
		if(($adminlangsettings & 2048)==2048) $fieldlist.=',sectionURL' . $index;
	}
	$fieldlist.=',sectionDescription';
	for($index=2; $index <= $adminlanguages+1; $index++){
		if(($adminlangsettings & 512)==512) $fieldlist.=',sectionDescription' . $index;
	}
	$sSQL='SELECT ' . $fieldlist . ' FROM sections';
	$result=ect_query($sSQL) or ect_error();
	$fieldlistarr=explode(',', $fieldlist);
	$addcomma='';
	foreach($fieldlistarr as $flarrval){
		print $addcomma;
		print '"' . $flarrval . '"';
		$addcomma=',';
	}
	print "\r\n";
	while($rs=ect_fetch_assoc($result)){
		$addcomma='';
		foreach($fieldlistarr as $flarrval){
			print $addcomma;
			print '"' . str_replace('"','""',$rs[$flarrval]). '"';
			$addcomma=',';
		}
		print "\r\n";
	}
	ect_free_result($result);
}elseif(@$_POST["act"]=="dump2COinventory"){
	$sSQL2="SELECT payProvData1 FROM payprovider WHERE payProvID=2";
	$result=ect_query($sSQL2) or ect_error();
	$rs=ect_fetch_assoc($result);
	print $rs["payProvData1"] . "\r\n";
	ect_free_result($result);
	$sSQL2="SELECT pID,pName,pPrice," . (@$digidownloads==TRUE ? "pDownload," : "") . "pDescription FROM products";
	$result=ect_query($sSQL2) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		print str_replace(',', '&#44;', $rs["pID"]) . ",";
		print preg_replace("(\r\n|\n|\r)",' ',str_replace(',', '&#44;',strip_tags($rs["pName"]))) . ",";
		print ",";
		print $rs["pPrice"] . ",";
		print ",,";
		if(@$digidownloads==TRUE)
			print (trim($rs["pDownload"]) != "" ? "N" : "Y") . ",";
		else
			print 'Y,';
		print preg_replace("(\r\n|\n|\r)",'\\n',str_replace(',','&#44;',strip_tags($rs["pDescription"]))) . "\r\n";
	}
	ect_free_result($result);
}elseif(@$_POST["act"]=="quickbooks"){
}elseif(@$_POST["act"]=="ouresolutionsxmldump"){
	print '<?xml version="1.0"?>' . "\r\n";
	print '<DATABASE NAME="DataBaseCopy.mdb" >' . "\r\n";
	$sSQL="SELECT ordID,cartProdId,cartProdName,cartProdPrice,cartQuantity,cartID FROM cart INNER JOIN orders ON cart.cartOrderId=orders.ordID";
	$sSQL.=getsearchparams();
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		$theoptionspricediff=0;
		$sSQL="SELECT coPriceDiff,coOptGroup,coCartOption FROM cartoptions WHERE coCartID=" . $rs['cartID'];
		$result2=ect_query($sSQL) or ect_error();
		while($rs2=ect_fetch_assoc($result2)){
			$theoptionspricediff+=$rs2['coPriceDiff'];
		}
		$theunitprice=$rs['cartProdPrice']+$theoptionspricediff;
		$sSQL="SELECT pName,pDescription,pDropShip FROM products WHERE pID='" . $rs['cartProdId'] . "'";
		$result2=ect_query($sSQL) or ect_error();
		if($rs2=ect_fetch_assoc($result2)){
			$prodname=strip_tags($rs2['pName']);
			$proddesc=strip_tags($rs2['pDescription']);
			$supplier=$rs2['pDropShip'];
		}else{
			$prodname='';
			$proddesc='';
			$supplier=0;
		}
		if($ouresolutionsxml==1)
			$itemname=strip_tags($rs['cartProdId']) . 'chr(60)brchr(62)' . $proddesc;
		elseif($ouresolutionsxml==3)
			$itemname=strip_tags($rs['cartProdId']);
		elseif($ouresolutionsxml==4)
			$itemname=$prodname;
		elseif($ouresolutionsxml==5)
			$itemname=strip_tags($rs['cartProdId']) . 'chr(60)brchr(62)' . $prodname;
		else // default to "2"
			$itemname=$prodname . 'chr(60)brchr(62)' . $proddesc;
		print "<DATA TABLE='oitems' ORDERITEMID='" . $rs['cartID'] . "' ORDERID='" . $rs['ordID'] . "' CATALOGID='" . $rs['cartID'] . "' NUMITEMS='" . $rs['cartQuantity'] . "' ITEMNAME='" . xmlstrip($itemname) . "' UNITPRICE='" . twodp($theunitprice) . "' DUALPRICE='0' SUPPLIERID='" . $supplier . "' ADDRESS='' />" . "\r\n";
	}
	ect_free_result($result);
	$sSQL="SELECT ordID,ordName,ordLastName,ordAddress,ordAddress2,ordCity,ordState,ordZip,ordCountry,ordEmail,ordPhone,ordExtra1,ordExtra2,ordShipExtra1,ordShipExtra2,ordCheckoutExtra1,ordCheckoutExtra2,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipZip,ordShipCountry,ordShipPhone,ordPayProvider,ordAuthNumber,ordTotal,ordDate,ordStateTax,ordCountryTax,ordHSTTax,ordShipping,ordHandling,ordShipType,ordDiscount,ordAffiliate,ordDiscountText,ordStatus,statPrivate,ordAddInfo FROM orders INNER JOIN orderstatus ON orders.ordStatus=orderstatus.statID";
	$sSQL.=getsearchparams();
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		$ordGrandTotal=($rs['ordTotal']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordHSTTax']+$rs['ordShipping']+$rs['ordHandling'])-$rs['ordDiscount'];
		if(@$usefirstlastname){
			$firstname=xmlstrip(trim($rs['ordName']));
			$lastname=xmlstrip(trim($rs['ordLastName']));
		}else{
			$thename=xmlstrip(trim($rs['ordName']));
			if($thename != ''){
				if(strstr($thename,' ')){
					$namearr=explode(' ',$thename,2);
					$firstname=$namearr[0];
					$lastname=$namearr[1];
				}else{
					$firstname='';
					$lastname=$thename;
				}
			}
		}
		print "<DATA TABLE='orders' ORDERID='" . $rs['ordID'] . "' OCUSTOMERID='" . $rs['ordID'] . "' ODATE='" . date($admindatestr, strtotime($rs['ordDate'])) . "' ORDERAMOUNT='" . twodp($ordGrandTotal) . "' OFIRSTNAME='" . $firstname . "' OLASTNAME='" . $lastname . "' OEMAIL='" . xmlstrip($rs['ordEmail']) . "' OADDRESS='" . xmlstrip($rs['ordAddress'] . (trim($rs['ordAddress2']) != '' ? ', ' . $rs['ordAddress2'] : '')) . "' OCITY='" . xmlstrip($rs['ordCity']) . "' OPOSTCODE='" . xmlstrip($rs['ordZip']) . "' OSTATE='" . xmlstrip($rs['ordState']) . "' OCOUNTRY='" . xmlstrip($rs['ordCountry']) . "' OPHONE='" . substr(xmlstrip(str_replace(array(' ','.','-',')','('), '', $rs['ordPhone'])), -10) . "' OFAX='' OCOMPANY='" . (@$extra1iscompany==TRUE ? xmlstrip($rs['ordExtra1']) : '') . "' OCARDTYPE='' ";
		if(@$dumpccnumber){
			if($sslok==FALSE){
				print "OCARDNO='No SSL' OCARDNAME='No SSL' OCARDEXPIRES='No SSL' OCARDADDRESS='No SSL' ";
			}else{
				$result2=ect_query("SELECT ordCNum,ordPayProvider FROM orders WHERE ordID=" . $rs["ordID"]) or ect_error();
				$rs2=ect_fetch_assoc($result2);
				$ordCNum=$rs2['ordCNum'];
				$encryptmethod=strtolower(@$encryptmethod);
				if(trim($ordCNum)=='' || is_null($ordCNum) || $rs2['ordPayProvider']!=10){
					print "OCARDNO='' OCARDNAME='' OCARDEXPIRES='' OCARDADDRESS='' ";
				}elseif($encryptmethod=='mcrypt'){
					if(@$mcryptalg=='') $mcryptalg=MCRYPT_BLOWFISH;
					$td=mcrypt_module_open($mcryptalg, '', 'cbc', '');
					$thekey=@$ccencryptkey;
					$thekey=substr($thekey, 0, mcrypt_enc_get_key_size($td));
					$cnumarr=explode(' ', $ordCNum);
					$iv=@$cnumarr[0];
					$iv=@pack("H" . strlen($iv), $iv);
					$ordCNum=@pack("H" . strlen(@$cnumarr[1]), @$cnumarr[1]);
					mcrypt_generic_init($td, $thekey, $iv);
					$cnumarr=explode("&", mdecrypt_generic($td, $ordCNum));
					mcrypt_generic_deinit($td);
					mcrypt_module_close($td);
					if(is_array($cnumarr)){
						print "OCARDNO='" . $cnumarr[0] . "' OCARDNAME='" . $cnumarr[3] . "' OCARDEXPIRES='" . $cnumarr[1] . "' OCARDADDRESS='" . $rs["ordAddress"] . (trim($rs["ordAddress2"]) != '' ? ', ' . $rs["ordAddress2"] : '') . "' ";
					}else
						print "OCARDNO='' OCARDNAME='' OCARDEXPIRES='' OCARDADDRESS='' ";
				}elseif($encryptmethod=="none"){
					$cnumarr=explode("&",$ordCNum);
					if(is_array($cnumarr)){
						print "OCARDNO='" . $cnumarr[0] . "' OCARDNAME='" . $cnumarr[3] . "' OCARDEXPIRES='" . $cnumarr[1] . "' OCARDADDRESS='" . $rs["ordAddress"] . (trim($rs["ordAddress2"]) != '' ? ', ' . $rs["ordAddress2"] : '') . "' ";
					}else
						print "OCARDNO='' OCARDNAME='' OCARDEXPIRES='' OCARDADDRESS='' ";
				}
				ect_free_result($result2);
			}
		}
		print "OPROCESSED='' OCOMMENT='" . xmlstrip($rs['ordAddInfo']) . "' OTAX='" . twodp($rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordHSTTax']) . "' OPROMISEDSHIPDATE='' OSHIPPEDDATE='' OSHIPMETHOD='0' OSHIPCOST='" . twodp($rs['ordShipping']) . "' ";
		print "OSHIPNAME='" . xmlstrip(trim($rs['ordShipName'].' '.$rs['ordShipLastName'])) . "' OSHIPCOMPANY='' OSHIPEMAIL='' OSHIPMETHODTYPE='" . xmlstrip($rs['ordShipType']) . "' OSHIPADDRESS='" . xmlstrip($rs['ordShipAddress'] . (trim($rs['ordShipAddress2']) != '' ? ', ' . $rs['ordShipAddress2'] : '')) . "' OSHIPTOWN='" . xmlstrip($rs['ordShipCity']) . "' OSHIPZIP='" . xmlstrip($rs['ordShipZip']) . "' OSHIPCOUNTRY='" . xmlstrip($rs['ordShipCountry']) . "' OSHIPSTATE='" . xmlstrip($rs['ordShipState']) . "' ";
		print "OPAYMETHOD='" . $rs['ordPayProvider'] . "' OTHER1='" . (@$extra1iscompany==TRUE ? '' : xmlstrip($rs['ordExtra1'])) . "' OTHER2='" . xmlstrip($rs['ordExtra2']) . "' OTIME='' OAUTHORIZATION='' OERRORS='' ODISCOUNT='" . twodp($rs['ordDiscount']) . "' OSTATUS='" . xmlstrip($rs['statPrivate']) . "' OAFFID='' ODUALTOTAL='0' ODUALTAXES='0' ODUALSHIPPING='0' ODUALDISCOUNT='0' OHANDLING='" . twodp($rs['ordHandling']) . "' COUPON='" . xmlstrip(strip_tags($rs['ordDiscountText'])) . "' COUPONDISCOUNT='0' COUPONDISCOUNTDUAL='0' GIFTCERTIFICATE='' GIFTAMOUNTUSED='0' GIFTAMOUNTUSEDDUAL='0' CANCELED='" . ($rs['ordStatus']<2 ? "True" : "False") . "' />\r\n";
	}
	print "</DATABASE>\r\n";
	ect_free_result($result);
}elseif(@$_POST['act']=='dumpevents'){
	if(@$_SESSION['loginid']==0){
		$success=TRUE;
		$sSQL="SELECT userID,eventType,eventDate,eventSuccess,eventOrigin,areaAffected FROM auditlog ORDER BY logID DESC";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){
			print '"'.str_replace('"','""',$rs['userID']).'",';
			print '"'.str_replace('"','""',$rs['eventType']).'",';
			print str_replace('"','""',$rs['eventDate']).",";
			print '"'.str_replace('"','""',$rs['eventSuccess']).'",';
			print '"'.str_replace('"','""',$rs['eventOrigin']).'",';
			print '"'.str_replace('"','""',$rs['areaAffected']).'",'."\r\n";
		}
		ect_free_result($result);
	}else{
		$success=FALSE;
		print 'No Access Privileges.';
	}
	logevent(@$_SESSION['loginuser'],'EVENTLOG',$success,'dumporders.php','DUMP LOG');
}elseif(@$_POST['act']=='dumpemails'){
	$sSQL="SELECT email,mlName,mlIPAddress,mlConfirmDate FROM mailinglist WHERE isconfirmed<>0";
	if(@$_GET['entirelist']!='1') $sSQL.=" AND selected<>0";
	$result=ect_query($sSQL) or ect_error();
	print "Email,Full Name\r\n";
	while($rs=ect_fetch_assoc($result)){
		print '"'.str_replace('"','""',$rs['email']).'",';
		print '"'.str_replace('"','""',$rs['mlName']).'",';
		print '"'.str_replace('"','""',$rs['mlIPAddress']).'",';
		$thedate=$rs['mlConfirmDate'];
		if($thedate!=''){
			print '"'.str_replace('"','""',$rs['mlConfirmDate']).'",'."\r\n";
		}else{
			print '"",'."\r\n";
		}
	}
	ect_free_result($result);
}else{
	$sSQL2="SELECT statID,statPrivate FROM orderstatus";
	$result=ect_query($sSQL2) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		$allstatus[$rs['statID']]=$rs['statPrivate'];
	}
	ect_free_result($result);
	if($hasdetails)
		$sSQL2="SELECT ordID,ordName,ordLastName,ordAddress,ordAddress2,ordCity,ordState,ordZip,ordCountry,ordEmail,ordPhone,ordExtra1,ordExtra2,ordShipExtra1,ordShipExtra2,ordCheckoutExtra1,ordCheckoutExtra2,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipZip,ordShipCountry,ordShipPhone,payProvName,ordAuthNumber,ordTotal,ordDate,ordStateTax,ordCountryTax,ordHSTTax,ordShipping,ordHandling,ordDiscount,ordAddInfo,ordShipType,ordStatus,ordAuthStatus,cartProdId,cartProdName,cartProdPrice,cartQuantity,cartID FROM cart LEFT JOIN orders ON cart.cartOrderId=orders.ordID LEFT JOIN payprovider ON payprovider.payProvID=orders.ordPayProvider";
	else
		$sSQL2="SELECT ordID,ordName,ordLastName,ordAddress,ordAddress2,ordCity,ordState,ordZip,ordCountry,ordEmail,ordPhone,ordExtra1,ordExtra2,ordShipExtra1,ordShipExtra2,ordCheckoutExtra1,ordCheckoutExtra2,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipZip,ordShipCountry,ordShipPhone,payProvName,ordAuthNumber,ordTotal,ordDate,ordStateTax,ordCountryTax,ordHSTTax,ordShipping,ordHandling,ordDiscount,ordAddInfo,ordShipType,ordStatus,ordAuthStatus FROM orders LEFT JOIN payprovider ON payprovider.payProvID=orders.ordPayProvider";
	$sSQL2.=getsearchparams();
	$result=ect_query($sSQL2 . ' ORDER BY ordID' . ($hasdetails?',cartID':'')) or ect_error();
	print '"OrderID",';
	if(@$extraorderfield1 != '') print '"' . str_replace('"','""',$extraorderfield1) . '",';
	if(@$usefirstlastname) print '"FirstName","LastName",'; else print '"CustomerName",';
	print '"Address",';
	if(@$useaddressline2==TRUE) print '"Address2",';
	print '"City","State","Zip","Country","Email","Phone",';
	if(@$extraorderfield2 != '') print '"' . str_replace('"','""',$extraorderfield2) . '",';
	if(@$extraorderfield1 != '') print '"' . str_replace('"','""',$extraorderfield1) . '",';
	print '"ShipName",';
	if(@$usefirstlastname) print '"ShipLastName",';
	print '"ShipAddress",';
	if(@$useaddressline2==TRUE) print '"ShipAddress2",';
	print '"ShipCity","ShipState","ShipZip","ShipCountry","ShipPhone",';
	if(@$extraorderfield2 != '') print '"' . str_replace('"','""',$extraorderfield2) . '",';
	print '"PaymentMethod","AuthNumber","Total","Date","StateTax","CountryTax",';
	if($origCountryID==2) print '"HST",';
	print '"Shipping","Handling","Discounts","AddInfo","ShippingMethod","Status","AuthStatus"';
	if(@$dumpccnumber) print ',"Card Number","Expiry Date","CVV Code","Issue Number","Card Name"';
	if($hasdetails) print ',"ProductID","ProductName","ProductPrice","Quantity","Options"';
	print "\r\n";
	while($rs=ect_fetch_assoc($result)){
		print $rs["ordID"] . ",";
		if(@$extraorderfield1 != '') print '"' . str_replace('"','""',$rs["ordExtra1"]) . '",';
		print '"' . str_replace('"','""',$rs["ordName"]) . '",';
		if(@$usefirstlastname) print '"' . str_replace('"','""',$rs['ordLastName']) . '",';
		print '"' . str_replace('"','""',$rs["ordAddress"]) . '",';
		if(@$useaddressline2==TRUE) print '"' . str_replace('"','""',$rs["ordAddress2"]) . '",';
		print '"' . str_replace('"','""',$rs["ordCity"]) . '",';
		print '"' . str_replace('"','""',$rs["ordState"]) . '",';
		print '"' . str_replace('"','""',$rs["ordZip"]) . '",';
		print '"' . str_replace('"','""',$rs["ordCountry"]) . '",';
		print '"' . str_replace('"','""',$rs["ordEmail"]) . '",';
		print '"' . str_replace('"','""',$rs["ordPhone"]) . '",';
		if(@$extraorderfield2 != '') print '"' . str_replace('"','""',$rs["ordExtra2"]) . '",';
		if(@$extraorderfield1 != '') print '"' . str_replace('"','""',$rs["ordShipExtra1"]) . '",';
		print '"' . str_replace('"','""',$rs["ordShipName"]) . '",';
		if(@$usefirstlastname) print '"' . str_replace('"','""',$rs['ordShipLastName']) . '",';
		print '"' . str_replace('"','""',$rs["ordShipAddress"]) . '",';
		if(@$useaddressline2==TRUE) print '"' . str_replace('"','""',$rs["ordShipAddress2"]) . '",';
		print '"' . str_replace('"','""',$rs["ordShipCity"]) . '",';
		print '"' . str_replace('"','""',$rs["ordShipState"]) . '",';
		print '"' . str_replace('"','""',$rs["ordShipZip"]) . '",';
		print '"' . str_replace('"','""',$rs["ordShipCountry"]) . '",';
		print '"' . str_replace('"','""',$rs["ordShipPhone"]) . '",';
		if(@$extraorderfield2 != '') print '"' . str_replace('"','""',$rs["ordShipExtra2"]) . '",';
		print '"' . str_replace('"','""',$rs["payProvName"]) . '",';
		print '"' . str_replace('"','""',$rs["ordAuthNumber"]) . '",';
		print '"' . $rs["ordTotal"] . '",';
		print '"' . $rs["ordDate"] . '",';
		print '"' . $rs["ordStateTax"] . '",';
		print '"' . $rs["ordCountryTax"] . '",';
		if($origCountryID==2) print '"' . $rs["ordHSTTax"] . '",';
		print '"' . $rs["ordShipping"] . '",';
		print '"' . $rs["ordHandling"] . '",';
		print '"' . $rs["ordDiscount"] . '",';
		print '"' . str_replace('"','""',$rs["ordAddInfo"]) . '",';
		print '"' . str_replace('"','""',$rs["ordShipType"]) . '",';
		print '"' . str_replace('"','""',@$allstatus[$rs["ordStatus"]]) . '",';
		print '"' . str_replace('"','""',@$allstatus[$rs["ordAuthStatus"]]) . '"';
		if(@$dumpccnumber){
			if($sslok==FALSE){
				print ",No SSL,No SSL,No SSL,No SSL,No SSL";
			}else{
				$result2=ect_query("SELECT ordCNum,ordPayProvider FROM orders WHERE ordID=" . $rs["ordID"]) or ect_error();
				$rs2=ect_fetch_assoc($result2);
				$ordCNum=$rs2["ordCNum"];
				$encryptmethod=strtolower(@$encryptmethod);
				if(trim($ordCNum)=="" || is_null($ordCNum) || $rs2['ordPayProvider']!=10){
					print ',"(no data)","","","",""';
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
					if(is_array($cnumarr)){
						print ',"""' . $cnumarr[0] . '"""';
						print ',"""' . @$cnumarr[1] . '"""';
						print ',"' . @$cnumarr[2] . '"';
						print ',"' . @$cnumarr[3] . '"';
						print ',"' . urldecode(@$cnumarr[4]) . '"';
					}else
						print ',"(no data)","","","",""';
				}elseif($encryptmethod=="none"){
					$cnumarr=explode("&",$ordCNum);
					if(is_array($cnumarr)){
						print ',"""' . $cnumarr[0] . '"""';
						print ',"""' . @$cnumarr[1] . '"""';
						print ',"' . @$cnumarr[2] . '"';
						print ',"' . @$cnumarr[3] . '"';
						print ',"' . urldecode(@$cnumarr[4]) . '"';
					}else
						print ',"(no data)","","","",""';
				}
				ect_free_result($result2);
			}
		}
		if($hasdetails){
			$theOptions="";
			$thePriceDiff=0;
			$result2=ect_query("SELECT coPriceDiff,coOptGroup,coCartOption FROM cartoptions WHERE coCartID=" . $rs['cartID'] . ' ORDER BY coID') or ect_error();
			while($rs2=ect_fetch_assoc($result2)){
				$theOptions.=',' . '"' . str_replace('"','""',$rs2['coOptGroup']) . ' - ' . str_replace('"','""',$rs2['coCartOption']) . '"';
				$thePriceDiff+=$rs2['coPriceDiff'];
			}
			print ',"' . str_replace('"','""',$rs['cartProdId']) . '"';
			print ',"' . str_replace('"','""',$rs['cartProdName']) . '"';
			print ',' . ($rs['cartProdPrice'] + $thePriceDiff);
			print ',' . $rs['cartQuantity'];
			print $theOptions;
			ect_free_result($result2);
		}
		print "\r\n";
	}
	ect_free_result($result);
}
?>