<?php
// This code is copyright Internet Business Solutions SL.
// Unauthorized copying, use or transmittal without the
// express permission of Internet Business Solutions SL
// is strictly prohibited.
// Author: Vince Reid, vince@virtualred.net
$sVersion='PHP v6.5.4';
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>Create Ecommerce Plus mySQL database version <?php print $sVersion?></title>
<style type="text/css">
<!--
p {  font: 11pt  Arial, Helvetica, sans-serif}
BODY {  font: 11pt Arial, Helvetica, sans-serif}
-->
</style>
</head>
<body>
<div style="padding:24px;border: 1px solid #F63;width: 680px; margin: 0 auto;background:#ebf4fb;-moz-border-radius:10px;-webkit-border-radius:10px;margin-top:40px;">
<?php
include 'vsadmin/db_conn_open.php';

$haserrors=FALSE;
$tablenotcreated='';
$txtcollen=1024;

function escape_string($estr){
	return(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->real_escape_string($estr):mysql_real_escape_string($estr));
}
function ect_query($ectsql){
	return(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->query($ectsql):mysql_query($ectsql));
}
function ect_fetch_assoc($ectres){
	return(@$GLOBALS['ectdatabase']?$ectres->fetch_assoc():mysql_fetch_assoc($ectres));
}
function ect_num_rows($ectres){
	return(@$GLOBALS['ectdatabase']?$ectres->num_rows:mysql_num_rows($ectres));
}
function ect_free_result($ectres){
	@$GLOBALS['ectdatabase']?$ectres->free_result():mysql_free_result($ectres);
}
function ect_error(){
	print(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->error:mysql_error());
}
function print_sql_error($tablename){
	global $haserrors,$tablenotcreated;
	$theerror=(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->error:mysql_error());
	if($tablename!=''){
		if(strpos($theerror,'already exists')===FALSE)
			$haserrors=TRUE;
		else{
			$theerror='';
			print('Table ' . $tablename . " already exists. Not creating.<br />");
		}
	}else
		$haserrors=TRUE;
	if($theerror!='') print('<font color="#FF0000">' . $theerror . "</font><br />");
	$tablenotcreated[$tablename]=TRUE;
}
function checktablecreated($tablename){
	global $tablenotcreated;
	if(@$tablenotcreated[$tablename]){
		print('Table ' . $tablename . ' already exists. No default data added.<br />');
		return(FALSE);
	}else{
		print('Adding ' . $tablename . ' table default data<br />');
		return(TRUE);
	}
}

if(@$_POST["posted"]=="1"){

// ect_query("DROP TABLE address,admin,adminlogin,affiliates,cart,cartoptions,clientlogin,countries,coupons,cpnassign,customerlogin,dropshipper,installedmods,ipblocking,mailinglist,multibuyblock,multisections,optiongroup,options,orders,orderstatus,payprovider,postalzones,pricebreaks,prodoptions,products,relatedprods,sections,states,tmplogin,uspsmethods,zonecharges") or print_sql_error('');

$databaseengine="myisam";
if(@$_POST['databaseengine']=='innodb') $databaseengine="innodb";

$sSQL="CREATE TABLE address (addID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="addCustID INT DEFAULT 0,";
$sSQL.="addIsDefault TINYINT DEFAULT 0,";
$sSQL.="addName VARCHAR(255) NULL,";
$sSQL.="addLastName VARCHAR(255) NULL,";
$sSQL.="addAddress VARCHAR(255) NULL,";
$sSQL.="addAddress2 VARCHAR(255) NULL,";
$sSQL.="addCity VARCHAR(255) NULL,";
$sSQL.="addState VARCHAR(255) NULL,";
$sSQL.="addZip VARCHAR(255) NULL,";
$sSQL.="addCountry VARCHAR(255) NULL,";
$sSQL.="addPhone VARCHAR(255) NULL,";
$sSQL.="addShipFlags TINYINT DEFAULT 0,";
$sSQL.="addExtra1 VARCHAR(255) NULL,";
$sSQL.="addExtra2 VARCHAR(255) NULL,";
$sSQL.="INDEX (addCustID)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('address');
	
$sSQL="CREATE TABLE admin (adminID INT NOT NULL PRIMARY KEY,";
$sSQL.="adminVersion VARCHAR(100),";
$sSQL.="adminUser VARCHAR(50) NULL,";
$sSQL.="adminPassword VARCHAR(50) NULL,";
$sSQL.="adminEmail VARCHAR(255) NOT NULL,";
$sSQL.="smtpserver VARCHAR(100) NULL,";
$sSQL.="emailUser VARCHAR(50) NULL,";
$sSQL.="emailPass VARCHAR(50) NULL,";
$sSQL.="adminStoreURL VARCHAR(255) NULL,";
$sSQL.="adminProdsPerPage INT DEFAULT 0,";
$sSQL.="adminShipping INT DEFAULT 0,";
$sSQL.="adminIntShipping INT DEFAULT 0,";
$sSQL.="adminCountry INT DEFAULT 0,";
$sSQL.="adminZipCode VARCHAR(50) NULL,";
$sSQL.="adminUSPSUser VARCHAR(255) NULL,";
$sSQL.="adminUSPSpw VARCHAR(255) NULL,";
$sSQL.="adminUPSUser VARCHAR(255) NULL,";
$sSQL.="adminUPSpw VARCHAR(255) NULL,";
$sSQL.="adminUPSAccess VARCHAR(255) NULL,";
$sSQL.="FedexAccountNo VARCHAR(255) NULL,";
$sSQL.="FedexMeter VARCHAR(255) NULL,";
$sSQL.="adminCanPostUser VARCHAR(255) NULL,";
$sSQL.="adminCanPostLogin VARCHAR(255) NULL,";
$sSQL.="adminCanPostPass VARCHAR(255) NULL,";
$sSQL.="adminEmailConfirm TINYINT DEFAULT 0,";
$sSQL.="adminPacking TINYINT DEFAULT 0,";
$sSQL.="adminDelUncompleted INT DEFAULT 0,";
$sSQL.="adminUSZones TINYINT DEFAULT 0,";
$sSQL.="adminUnits TINYINT DEFAULT 0,";
$sSQL.="adminStockManage INT DEFAULT 0,";
$sSQL.="adminHandling DOUBLE DEFAULT 0,";
$sSQL.="adminHandlingPercent DOUBLE DEFAULT 0,";
$sSQL.="adminTweaks INT DEFAULT 0,";
$sSQL.="adminUPSLicense TEXT NULL,";
$sSQL.="adminDelCC INT DEFAULT 0,";
$sSQL.="adminClearCart INT DEFAULT 0,";
$sSQL.="adminlanguages INT DEFAULT 0,";
$sSQL.="adminlangsettings INT DEFAULT 0,";
$sSQL.="adminlang VARCHAR(10) NULL,";
$sSQL.="storelang VARCHAR(10) NULL,";
$sSQL.="adminSecret VARCHAR(255),";
$sSQL.="updLastCheck DATE,";
$sSQL.="updRecommended VARCHAR(255) NULL,";
$sSQL.="updSecurity TINYINT(1) DEFAULT 0,";
$sSQL.="updShouldUpd TINYINT(1) DEFAULT 0,";
$sSQL.="adminUPSAccount VARCHAR(255) NULL,";
$sSQL.="adminUPSNegotiated TINYINT DEFAULT 0,";
$sSQL.="currRate1 DOUBLE DEFAULT 0,";
$sSQL.="currSymbol1 VARCHAR(50) NULL,";
$sSQL.="currRate2 DOUBLE DEFAULT 0,";
$sSQL.="currSymbol2 VARCHAR(50) NULL,";
$sSQL.="currRate3 DOUBLE DEFAULT 0,";
$sSQL.="currSymbol3 VARCHAR(50) NULL,";
$sSQL.="currConvUser VARCHAR(50) NULL,";
$sSQL.="currConvPw VARCHAR(50) NULL,";
$sSQL.="catalogRoot INT DEFAULT 0,";
$sSQL.="cardinalProcessor VARCHAR(255) NULL,";
$sSQL.="cardinalMerchant VARCHAR(255) NULL,";
$sSQL.="cardinalPwd VARCHAR(255) NULL,";
$sSQL.="prodFilter INT DEFAULT 0,";
$sSQL.="prodFilterText VARCHAR(255) NULL,";
$sSQL.="prodFilterText2 VARCHAR(255) NULL,";
$sSQL.="prodFilterText3 VARCHAR(255) NULL,";
$sSQL.="prodFilterOrder VARCHAR(255) NULL,";
$sSQL.="sideFilter INT DEFAULT 0,";
$sSQL.="sideFilterText VARCHAR(255) NULL,";
$sSQL.="sideFilterText2 VARCHAR(255) NULL,";
$sSQL.="sideFilterText3 VARCHAR(255) NULL,";
$sSQL.="sideFilterOrder VARCHAR(255) NULL,";
$sSQL.="FedexUserKey VARCHAR(50) NULL,";
$sSQL.="FedexUserPwd VARCHAR(50) NULL,";
$sSQL.="DHLSiteID VARCHAR(50) NULL,";
$sSQL.="DHLSitePW VARCHAR(50) NULL,";
$sSQL.="DHLAccountNo VARCHAR(50) NULL,";
$sSQL.="smartPostHub VARCHAR(15),";
$sSQL.="AusPostAPI VARCHAR(255),";
$sSQL.="adminPWLastChange DATETIME,";
$sSQL.="adminUserLock INT DEFAULT 0,";
$sSQL.="sortOrder INT DEFAULT 0,";
$sSQL.="sortOptions INT DEFAULT 0,";
$sSQL.="adminAltRates INT DEFAULT 0,";
$sSQL.="currLastUpdate DATETIME) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('admin');

$sSQL="CREATE TABLE adminlogin (adminloginid INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="adminloginname VARCHAR(255) NOT NULL,";
$sSQL.="adminloginpassword VARCHAR(255) NOT NULL,";
$sSQL.="adminLoginLastChange DATETIME,";
$sSQL.="adminLoginLock INT DEFAULT 0,";
$sSQL.="adminloginpermissions VARCHAR(255) NOT NULL) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('adminlogin');

$sSQL="CREATE TABLE affiliates (affilID VARCHAR(32) NOT NULL PRIMARY KEY,";
$sSQL.="affilPW VARCHAR(32),";
$sSQL.="affilEmail VARCHAR(128),";
$sSQL.="affilName VARCHAR(255),";
$sSQL.="affilAddress VARCHAR(255),";
$sSQL.="affilCity VARCHAR(255),";
$sSQL.="affilState VARCHAR(255),";
$sSQL.="affilZip VARCHAR(255),";
$sSQL.="affilCountry VARCHAR(255),";
$sSQL.="affilInform TINYINT DEFAULT 0,";
$sSQL.="affilDate DATE,";
$sSQL.="affilCommision DOUBLE DEFAULT 0) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('affiliates');

ect_query("CREATE TABLE alternaterates (altrateid INT PRIMARY KEY,altratename VARCHAR(255) NOT NULL,altratetext VARCHAR(255) NULL,altratetext2 VARCHAR(255) NULL,altratetext3 VARCHAR(255) NULL, usealtmethod INT DEFAULT 0, usealtmethodintl INT DEFAULT 0, altrateorder INT DEFAULT 0) ENGINE=" . $databaseengine) or print_sql_error('alternaterates');
if(checktablecreated('alternaterates')){
	for($index=1; $index<=10; $index++){
		$sSQL="INSERT INTO alternaterates (altrateid,altratename,altratetext,altratetext2,altratetext3,usealtmethod,usealtmethodintl) VALUES (";

		if($index==1) $altratename='Flat Rate Shipping';
		if($index==2) $altratename='Weight Based Shipping';
		if($index==3) $altratename='U.S.P.S. Shipping';
		if($index==4) $altratename='UPS Shipping';
		if($index==5) $altratename='Price Based Shipping';
		if($index==6) $altratename='Canada Post';
		if($index==7) $altratename='FedEx Shipping';
		if($index==8) $altratename='FedEx SmartPost&reg;';
		if($index==9) $altratename='DHL Shipping';
		if($index==10) $altratename='Australia Post';

		$sSQL.=$index . ",'" . $altratename . "','" . $altratename . "','" . $altratename . "','" . $altratename . "',0,0)";
		ect_query($sSQL) or print_sql_error('');
	}
}

$sSQL="CREATE TABLE auditlog (logID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,userID VARCHAR(50),eventType VARCHAR(50),eventDate DATETIME,eventSuccess TINYINT DEFAULT 0,eventOrigin VARCHAR(50),areaAffected VARCHAR(50)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('auditlog');

$sSQL="CREATE TABLE cart (cartID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="cartSessionID VARCHAR(100),";
$sSQL.="cartProdID VARCHAR(255),";
$sSQL.="cartOrigProdID VARCHAR(255),";
$sSQL.="cartProdName VARCHAR(255),";
$sSQL.="cartProdPrice DOUBLE,";
$sSQL.="cartDateAdded DATETIME,";
$sSQL.="cartQuantity INT DEFAULT 0,";
$sSQL.="cartOrderID INT DEFAULT 0,";
$sSQL.="cartClientID INT DEFAULT 0,";
$sSQL.="cartCompleted TINYINT,";
$sSQL.="cartGiftWrap TINYINT(1),";
$sSQL.="cartGiftMessage TEXT,";
$sSQL.="cartListID INT DEFAULT 0,";
$sSQL.="INDEX (cartClientID),INDEX (cartCompleted),INDEX (cartDateAdded),INDEX (cartOrderID),INDEX (cartListID),INDEX(cartProdID),INDEX(cartSessionID)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('cart');

$sSQL="CREATE TABLE cartoptions (coID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="coCartID INT,";
$sSQL.="coOptID INT,";
$sSQL.="coOptGroup VARCHAR(255),";
$sSQL.="coCartOption VARCHAR(" . $txtcollen . "),";
$sSQL.="coPriceDiff DOUBLE DEFAULT 0,";
$sSQL.="coWeightDiff DOUBLE DEFAULT 0,";
$sSQL.="coMultiply TINYINT(1) NOT NULL DEFAULT 0,";
$sSQL.="INDEX (coCartID), INDEX (coOptID)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('cartoptions');

$sSQL="CREATE TABLE contentregions (contentID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="contentName VARCHAR(255) NULL,contentX INT DEFAULT 0,contentY INT DEFAULT 0,contentData TEXT NULL,contentData2 TEXT NULL,contentData3 TEXT NULL) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('contentregions');

$sSQL="CREATE TABLE countries (countryID INT NOT NULL PRIMARY KEY,";
$sSQL.="countryName VARCHAR(255),";
$sSQL.="countryName2 VARCHAR(255),";
$sSQL.="countryName3 VARCHAR(255),";
$sSQL.="countryEnabled TINYINT DEFAULT 0,";
$sSQL.="countryTax DOUBLE DEFAULT 0,";
$sSQL.="countryOrder INT DEFAULT 0,";
$sSQL.="countryZone INT DEFAULT 0,";
$sSQL.="loadStates INT DEFAULT 0,";
$sSQL.="countryLCID VARCHAR(50),";
$sSQL.="countryCurrency VARCHAR(50),";
$sSQL.="countryCode VARCHAR(50),";
$sSQL.="countryNumCurrency INT DEFAULT 0,";
$sSQL.="countryFreeShip TINYINT DEFAULT 0,";
$sSQL.="INDEX (countryName)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('countries');

$sSQL="CREATE TABLE coupons (cpnID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="cpnName VARCHAR(255) NULL,";
$sSQL.="cpnName2 VARCHAR(255) NULL,";
$sSQL.="cpnName3 VARCHAR(255) NULL,";
$sSQL.="cpnWorkingName VARCHAR(255),";
$sSQL.="cpnNumber VARCHAR(255),";
$sSQL.="cpnType INT DEFAULT 0,";
$sSQL.="cpnEndDate DATETIME,";
$sSQL.="cpnDiscount DOUBLE DEFAULT 0,";
$sSQL.="cpnThreshold DOUBLE DEFAULT 0,";
$sSQL.="cpnThresholdMax DOUBLE DEFAULT 0,";
$sSQL.="cpnThresholdRepeat DOUBLE DEFAULT 0,";
$sSQL.="cpnQuantity INT DEFAULT 0,";
$sSQL.="cpnQuantityMax INT DEFAULT 0,";
$sSQL.="cpnQuantityRepeat INT DEFAULT 0,";
$sSQL.="cpnNumAvail INT DEFAULT 0,";
$sSQL.="cpnCntry TINYINT DEFAULT 0,";
$sSQL.="cpnIsCoupon TINYINT DEFAULT 0,";
$sSQL.="cpnHandling TINYINT(1) DEFAULT 0,";
$sSQL.="cpnLoginLevel INT DEFAULT 0,";
$sSQL.="cpnSitewide TINYINT DEFAULT 0) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('coupons');

$sSQL="CREATE TABLE cpnassign (cpaID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="cpaCpnID INT DEFAULT 0,";
$sSQL.="cpaType TINYINT DEFAULT 0,";
$sSQL.="cpaAssignment VARCHAR(255),";
$sSQL.="INDEX(cpaAssignment), INDEX(cpaCpnID)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('cpnassign');

$sSQL="CREATE TABLE customerlists (listID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="listName VARCHAR(255) NOT NULL,";
$sSQL.="listOwner INT NOT NULL DEFAULT 0,";
$sSQL.="listAccess VARCHAR(255) NOT NULL,";
$sSQL.="INDEX (listOwner)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('customerlists');

$sSQL="CREATE TABLE customerlogin (clID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="clUserName VARCHAR(50) NULL,";
$sSQL.="clPW VARCHAR(50) NULL,";
$sSQL.="clLoginLevel TINYINT DEFAULT 0,";
$sSQL.="clPercentDiscount DOUBLE DEFAULT 0,";
$sSQL.="clActions INT DEFAULT 0,";
$sSQL.="clEmail VARCHAR(255) NULL,";
$sSQL.="loyaltyPoints INT DEFAULT 0,";
$sSQL.="clientCustom1 VARCHAR(255) NULL,";
$sSQL.="clientCustom2 VARCHAR(255) NULL,";
$sSQL.="clientAdminNotes TEXT NULL,";
$sSQL.="clDateCreated DATETIME) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('customerlogin');

$sSQL="CREATE TABLE dropshipper (dsID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="dsName VARCHAR(255) NULL,";
$sSQL.="dsEmail VARCHAR(255) NULL,";
$sSQL.="dsAddress VARCHAR(255) NULL,";
$sSQL.="dsCity VARCHAR(255) NULL,";
$sSQL.="dsState VARCHAR(255) NULL,";
$sSQL.="dsZip VARCHAR(255) NULL,";
$sSQL.="dsCountry VARCHAR(255) NULL,";
$sSQL.="dsEmailHeader TEXT NULL,";
$sSQL.="dsAction INT DEFAULT 0) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('dropshipper');

$sSQL="CREATE TABLE emailmessages (emailID INT PRIMARY KEY,";
$sSQL.="giftcertsubject VARCHAR(255) NULL,";
$sSQL.="giftcertsubject2 VARCHAR(255) NULL,";
$sSQL.="giftcertsubject3 VARCHAR(255) NULL,";
$sSQL.="giftcertemail TEXT NULL,";
$sSQL.="giftcertemail2 TEXT NULL,";
$sSQL.="giftcertemail3 TEXT NULL,";
$sSQL.="giftcertsendersubject VARCHAR(255) NULL,";
$sSQL.="giftcertsendersubject2 VARCHAR(255) NULL,";
$sSQL.="giftcertsendersubject3 VARCHAR(255) NULL,";
$sSQL.="giftcertsender TEXT NULL,";
$sSQL.="giftcertsender2 TEXT NULL,";
$sSQL.="giftcertsender3 TEXT NULL,";
$sSQL.="emailsubject VARCHAR(255) NULL,";
$sSQL.="emailsubject2 VARCHAR(255) NULL,";
$sSQL.="emailsubject3 VARCHAR(255) NULL,";
$sSQL.="emailheaders TEXT NULL,";
$sSQL.="emailheaders2 TEXT NULL,";
$sSQL.="emailheaders3 TEXT NULL,";
$sSQL.="receiptheaders TEXT NULL,";
$sSQL.="receiptheaders2 TEXT NULL,";
$sSQL.="receiptheaders3 TEXT NULL,";
$sSQL.="dropshipsubject VARCHAR(255) NULL,";
$sSQL.="dropshipsubject2 VARCHAR(255) NULL,";
$sSQL.="dropshipsubject3 VARCHAR(255) NULL,";
$sSQL.="dropshipheaders TEXT NULL,";
$sSQL.="dropshipheaders2 TEXT NULL,";
$sSQL.="dropshipheaders3 TEXT NULL,";
$sSQL.="notifystocksubject VARCHAR(255) NULL,notifystocksubject2 VARCHAR(255) NULL,notifystocksubject3 VARCHAR(255) NULL,";
$sSQL.="notifystockemail TEXT NULL,notifystockemail2 TEXT NULL,notifystockemail3 TEXT NULL,";
$sSQL.="orderstatussubject VARCHAR(255) NULL,orderstatussubject2 VARCHAR(255) NULL,orderstatussubject3 VARCHAR(255) NULL,";
$sSQL.="orderstatusemail TEXT NULL,orderstatusemail2 TEXT NULL,orderstatusemail3 TEXT NULL) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('emailmessages');

$sSQL="CREATE TABLE giftcertificate (gcID VARCHAR(255) PRIMARY KEY,";
$sSQL.="gcTo VARCHAR(255) NULL,";
$sSQL.="gcFrom VARCHAR(255) NULL,";
$sSQL.="gcEmail VARCHAR(255) NULL,";
$sSQL.="gcOrigAmount DOUBLE DEFAULT 0,";
$sSQL.="gcRemaining DOUBLE DEFAULT 0,";
$sSQL.="gcDateCreated DATE,";
$sSQL.="gcDateUsed DATE,";
$sSQL.="gcCartID INT DEFAULT 0 NOT NULL,";
$sSQL.="gcOrderID INT DEFAULT 0 NOT NULL,";
$sSQL.="gcAuthorized TINYINT(1) DEFAULT 0,";
$sSQL.="gcMessage TEXT NULL, INDEX (gcCartID), INDEX (gcOrderID)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('giftcertificate');

$sSQL="CREATE TABLE giftcertsapplied (gcaGCID VARCHAR(255) NOT NULL,";
$sSQL.="gcaOrdID INT DEFAULT 0 NOT NULL,";
$sSQL.="gcaAmount DOUBLE DEFAULT 0,";
$sSQL.="PRIMARY KEY(gcaGCID,gcaOrdID)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('giftcertsapplied');

$sSQL="CREATE TABLE installedmods (modkey VARCHAR(255) PRIMARY KEY,modtitle VARCHAR(255) NOT NULL, modauthor VARCHAR(255) NULL, modauthorlink VARCHAR(255) NULL, modversion VARCHAR(255) NULL, modectversion VARCHAR(255) NULL, modlink VARCHAR(255) NULL, moddate DATETIME NOT NULL, modnotes TEXT NULL) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('installedmods');

$sSQL="CREATE TABLE ipblocking (dcid INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="dcip1 INT DEFAULT 0,";
$sSQL.="dcip2 INT DEFAULT 0) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('ipblocking');

$sSQL="CREATE TABLE mailinglist (email VARCHAR(255) PRIMARY KEY,";
$sSQL.="mlName VARCHAR(255),";
$sSQL.="emailFormat TINYINT DEFAULT 0,";
$sSQL.="mlConfirmDate DATE,";
$sSQL.="mlIPAddress VARCHAR(255),";
$sSQL.="emailsent TINYINT(1) DEFAULT 0,";
$sSQL.="selected TINYINT(1) DEFAULT 0,";
$sSQL.="isconfirmed TINYINT(1) DEFAULT 0) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('mailinglist');

$sSQL="CREATE TABLE multibuyblock (ssdenyid INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="ssdenyip VARCHAR(255) NOT NULL,";
$sSQL.="sstimesaccess INT DEFAULT 0,";
$sSQL.="lastaccess DATETIME,";
$sSQL.="INDEX (ssdenyip), UNIQUE (ssdenyip)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('multibuyblock');

$sSQL="CREATE TABLE multisearchcriteria (mSCpID VARCHAR(128) NOT NULL,mSCscID INT DEFAULT 0 NOT NULL, PRIMARY KEY(mSCpID,mSCscID)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('multisearchcriteria');

$sSQL="CREATE TABLE multisections (pID VARCHAR(128) NOT NULL,";
$sSQL.="pSection INT DEFAULT 0 NOT NULL,";
$sSQL.="PRIMARY KEY (pID, pSection)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('multisections');

ect_query("CREATE TABLE notifyinstock (nsProdID VARCHAR(150) NOT NULL,nsOptID INT DEFAULT 0,nsTriggerProdID VARCHAR(255) NOT NULL,nsEmail VARCHAR(75) NOT NULL,nsDate DATETIME, PRIMARY KEY(nsTriggerProdID,nsEmail)) ENGINE=" . $databaseengine) or print_sql_error('notifyinstock');

$sSQL="CREATE TABLE optiongroup (optGrpID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="optGrpName VARCHAR(255),";
$sSQL.="optGrpName2 VARCHAR(255),";
$sSQL.="optGrpName3 VARCHAR(255),";
$sSQL.="optGrpWorkingName VARCHAR(255),";
$sSQL.="optType INT DEFAULT 0,";
$sSQL.="optFlags INT DEFAULT 0,";
$sSQL.="optMultiply TINYINT(1) DEFAULT 0,";
$sSQL.="optAcceptChars VARCHAR(255),";
$sSQL.="optTxtMaxLen INT DEFAULT 0,";
$sSQL.="optTxtCharge DOUBLE DEFAULT 0,";
$sSQL.="optTooltip TEXT,";
$sSQL.="optGrpSelect TINYINT(1) DEFAULT 0) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('optiongroup');

$sSQL="CREATE TABLE options (optID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="optGroup INT,";
$sSQL.="optName VARCHAR(255),";
$sSQL.="optName2 VARCHAR(255),";
$sSQL.="optName3 VARCHAR(255),";
$sSQL.="optDependants VARCHAR(255),";
$sSQL.="optPriceDiff DOUBLE DEFAULT 0,";
$sSQL.="optWholesalePriceDiff DOUBLE DEFAULT 0,";
$sSQL.="optWeightDiff DOUBLE DEFAULT 0,";
$sSQL.="optStock INT DEFAULT 0,";
$sSQL.="optRegExp VARCHAR(255),";
$sSQL.="optPlaceholder VARCHAR(255),";
$sSQL.="optPlaceholder2 VARCHAR(255),";
$sSQL.="optPlaceholder3 VARCHAR(255),";
$sSQL.="optDefault TINYINT(1) DEFAULT 0,";
$sSQL.="optAltImage VARCHAR(255),";
$sSQL.="optAltLargeImage VARCHAR(255),";
$sSQL.="INDEX (optGroup)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('options');

$sSQL="CREATE TABLE orders (ordID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="ordSessionID VARCHAR(255),";
$sSQL.="ordName VARCHAR(255),";
$sSQL.="ordLastName VARCHAR(255),";
$sSQL.="ordAddress VARCHAR(255),";
$sSQL.="ordAddress2 VARCHAR(255),";
$sSQL.="ordCity VARCHAR(255),";
$sSQL.="ordState VARCHAR(255),";
$sSQL.="ordZip VARCHAR(255),";
$sSQL.="ordCountry VARCHAR(255),";
$sSQL.="ordEmail VARCHAR(255),";
$sSQL.="ordPhone VARCHAR(255),";
$sSQL.="ordShipName VARCHAR(255),";
$sSQL.="ordShipLastName VARCHAR(255),";
$sSQL.="ordShipAddress VARCHAR(255),";
$sSQL.="ordShipAddress2 VARCHAR(255),";
$sSQL.="ordShipCity VARCHAR(255),";
$sSQL.="ordShipState VARCHAR(255),";
$sSQL.="ordShipZip VARCHAR(255),";
$sSQL.="ordShipCountry VARCHAR(255),";
$sSQL.="ordShipPhone VARCHAR(255),";
$sSQL.="ordAuthNumber VARCHAR(255),";
$sSQL.="ordAuthStatus VARCHAR(255),";
$sSQL.="ordAffiliate VARCHAR(255),";
$sSQL.="ordPayProvider INT DEFAULT 0,";
$sSQL.="ordTransID VARCHAR(255) NULL,";
$sSQL.="ordShipping DOUBLE DEFAULT 0,";
$sSQL.="ordStateTax DOUBLE DEFAULT 0,";
$sSQL.="ordCountryTax DOUBLE DEFAULT 0,";
$sSQL.="ordHSTTax DOUBLE DEFAULT 0,";
$sSQL.="ordHandling DOUBLE DEFAULT 0,";
$sSQL.="ordShipType VARCHAR(255),";
$sSQL.="ordShipCarrier INT DEFAULT 0,";
$sSQL.="ordClientID INT DEFAULT 0,";
$sSQL.="ordTotal DOUBLE DEFAULT 0,";
$sSQL.="ordDate DATETIME,";
$sSQL.="ordIP VARCHAR(255),";
$sSQL.="ordDiscount DOUBLE DEFAULT 0,";
$sSQL.="ordDiscountText VARCHAR(255),";
$sSQL.="ordExtra1 VARCHAR(255) NULL,";
$sSQL.="ordExtra2 VARCHAR(255) NULL,";
$sSQL.="ordShipExtra1 VARCHAR(255) NULL,";
$sSQL.="ordShipExtra2 VARCHAR(255) NULL,";
$sSQL.="ordCheckoutExtra1 VARCHAR(255) NULL,";
$sSQL.="ordCheckoutExtra2 VARCHAR(255) NULL,";
$sSQL.="ordTrackNum VARCHAR(255) NULL,";
$sSQL.="ordAVS VARCHAR(255) NULL,";
$sSQL.="ordCVV VARCHAR(255) NULL,";
$sSQL.="ordAddInfo TEXT,";
$sSQL.="ordPrivateStatus TEXT,";
$sSQL.="ordCNum TEXT NULL,";
$sSQL.="ordComLoc TINYINT DEFAULT 0,";
$sSQL.="ordStatus TINYINT DEFAULT 0,";
$sSQL.="ordStatusDate DATETIME,";
$sSQL.="ordStatusInfo TEXT NULL,";
$sSQL.="ordInvoice VARCHAR(255) NULL,";
$sSQL.="ordReferer VARCHAR(255) NULL,";
$sSQL.="ordQuerystr VARCHAR(255) NULL,";
$sSQL.="ordLang TINYINT DEFAULT 0,";
$sSQL.="loyaltyPoints INT DEFAULT 0,";
$sSQL.="pointsRedeemed INT DEFAULT 0,";
$sSQL.="INDEX (ordClientID), INDEX (ordDate), INDEX (ordSessionID), INDEX (ordStatus)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('orders');

$sSQL="CREATE TABLE orderstatus (statID INT PRIMARY KEY,";
$sSQL.="statPrivate VARCHAR(255) NULL,";
$sSQL.="emailstatus TINYINT(1) DEFAULT 0,";
$sSQL.="statPublic VARCHAR(255) NULL,";
$sSQL.="statPublic2 VARCHAR(255) NULL,";
$sSQL.="statPublic3 VARCHAR(255) NULL) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('orderstatus');

$sSQL="CREATE TABLE passwordhistory (pwhID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,liID INT DEFAULT 0,pwhPwd VARCHAR(50) NULL,datePWChanged DATETIME) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('passwordhistory');

$sSQL="CREATE TABLE payprovider (payProvID INT NOT NULL PRIMARY KEY,";
$sSQL.="payProvName VARCHAR(255),";
$sSQL.="payProvShow VARCHAR(255),";
$sSQL.="payProvShow2 VARCHAR(255),";
$sSQL.="payProvShow3 VARCHAR(255),";
$sSQL.="payProvEnabled TINYINT,";
$sSQL.="payProvAvailable TINYINT,";
$sSQL.="payProvDemo TINYINT,";
$sSQL.="payProvData1 VARCHAR(2048),";
$sSQL.="payProvData2 VARCHAR(2048),";
$sSQL.="payProvData3 VARCHAR(2048),";
$sSQL.="payProvOrder INT DEFAULT 0,";
$sSQL.="payProvMethod INT DEFAULT 0,";
$sSQL.="ppHandlingCharge DOUBLE DEFAULT 0,";
$sSQL.="ppHandlingPercent DOUBLE DEFAULT 0,";
$sSQL.="pProvHeaders TEXT NULL,";
$sSQL.="pProvHeaders2 TEXT NULL,";
$sSQL.="pProvHeaders3 TEXT NULL,";
$sSQL.="pProvDropShipHeaders TEXT NULL,";
$sSQL.="pProvDropShipHeaders2 TEXT NULL,";
$sSQL.="pProvDropShipHeaders3 TEXT NULL,";
$sSQL.="payProvLevel INT DEFAULT 0) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('payprovider');

$sSQL="CREATE TABLE postalzones (pzID INT NOT NULL PRIMARY KEY,";
$sSQL.="pzName VARCHAR(50),";
$sSQL.="pzMultiShipping TINYINT DEFAULT 0,";
$sSQL.="pzMethodName1 VARCHAR(255) NULL,";
$sSQL.="pzMethodName2 VARCHAR(255) NULL,";
$sSQL.="pzMethodName3 VARCHAR(255) NULL,";
$sSQL.="pzMethodName4 VARCHAR(255) NULL,";
$sSQL.="pzMethodName5 VARCHAR(255) NULL,";
$sSQL.="pzFSA TINYINT DEFAULT 1) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('postalzones');

$sSQL="CREATE TABLE pricebreaks (pbQuantity INT NOT NULL,";
$sSQL.="pbProdID VARCHAR(255) NOT NULL,";
$sSQL.="pPrice DOUBLE DEFAULT 0,";
$sSQL.="pWholesalePrice DOUBLE DEFAULT 0,";
$sSQL.="PRIMARY KEY(pbProdID,pbQuantity)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('pricebreaks');

$sSQL="CREATE TABLE prodoptions (poID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="poProdID VARCHAR(128),";
$sSQL.="poOptionGroup INT,";
$sSQL.="INDEX (poProdID), INDEX (poOptionGroup)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('prodoptions');

$sSQL="CREATE TABLE productimages (imageProduct VARCHAR(128),imageSrc VARCHAR(255) NOT NULL,imageNumber INT DEFAULT 0 NOT NULL,imageType SMALLINT DEFAULT 0 NOT NULL, PRIMARY KEY(imageProduct,imageType,imageNumber)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('productimages');
ect_query("ALTER TABLE productimages ADD INDEX (imageProduct)") or ect_error();
ect_query("ALTER TABLE productimages ADD INDEX (imageType)") or ect_error();

$sSQL = "CREATE TABLE productpackages (packageID VARCHAR(128) NOT NULL,pID VARCHAR(128) NOT NULL,quantity INT NOT NULL DEFAULT 0,PRIMARY KEY(packageID,pID)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('productpackages');
ect_query("ALTER TABLE productpackages ADD INDEX (packageID)") or ect_error();
ect_query("ALTER TABLE productpackages ADD INDEX (pID)") or ect_error();

$sSQL="CREATE TABLE products (pID VARCHAR(128) NOT NULL PRIMARY KEY,";
$sSQL.="pName VARCHAR(255) NOT NULL,";
$sSQL.="pName2 VARCHAR(255),";
$sSQL.="pName3 VARCHAR(255),";
$sSQL.="pSection INT DEFAULT 0 NOT NULL,";
$sSQL.="pDescription TEXT,";
$sSQL.="pDescription2 TEXT,";
$sSQL.="pDescription3 TEXT,";
$sSQL.="pLongdescription TEXT,";
$sSQL.="pLongdescription2 TEXT,";
$sSQL.="pLongdescription3 TEXT,";
$sSQL.="pSearchParams TEXT NULL,";
$sSQL.="pDownload VARCHAR(255) NULL,";
$sSQL.="pStaticURL VARCHAR(255) NULL,";
$sSQL.="pPrice DOUBLE DEFAULT 0 NOT NULL,";
$sSQL.="pListPrice DOUBLE DEFAULT 0,";
$sSQL.="pWholesalePrice DOUBLE DEFAULT 0,";
$sSQL.="pShipping DOUBLE DEFAULT 0,";
$sSQL.="pShipping2 DOUBLE DEFAULT 0,";
$sSQL.="pWeight DOUBLE DEFAULT 0,";
$sSQL.="pDisplay TINYINT(1) DEFAULT 1,";
$sSQL.="pSell TINYINT(1) DEFAULT 1,";
$sSQL.="pStaticPage TINYINT(1) DEFAULT 0,";
$sSQL.="pStockByOpts TINYINT(1) DEFAULT 0,";
$sSQL.="pRecommend TINYINT(1) DEFAULT 0,";
$sSQL.="pGiftWrap TINYINT(1) DEFAULT 0,";
$sSQL.="pBackOrder TINYINT(1) DEFAULT 0,";
$sSQL.="pExemptions TINYINT DEFAULT 0,";
$sSQL.="pInStock INT DEFAULT 0,";
$sSQL.="pDropship INT DEFAULT 0,";
$sSQL.="pManufacturer INT DEFAULT 0,";
$sSQL.="pDims VARCHAR(255) NULL,";
$sSQL.="pSKU VARCHAR(255) NULL,";
$sSQL.="pTitle VARCHAR(255) NULL,";
$sSQL.="pMetaDesc VARCHAR(255) NULL,";
$sSQL.="pCustom1 VARCHAR(2048) NULL,";
$sSQL.="pCustom2 VARCHAR(2048) NULL,";
$sSQL.="pCustom3 VARCHAR(2048) NULL,";
$sSQL.="pDateAdded DATE,";
$sSQL.="pTax DOUBLE NULL,";
$sSQL.="pOrder INT DEFAULT 0,";
$sSQL.="pTotRating INT DEFAULT 0,";
$sSQL.="pNumRatings INT DEFAULT 0,";
$sSQL.="INDEX (pDateAdded),INDEX (pDisplay),INDEX (pManufacturer),INDEX (pName),INDEX (pOrder),INDEX (pPrice),INDEX (pSection)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('products');

$sSQL="CREATE TABLE ratings (rtID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="rtProdID VARCHAR(255) NOT NULL,";
$sSQL.="rtRating TINYINT DEFAULT 0,";
$sSQL.="rtLanguage TINYINT DEFAULT 0,";
$sSQL.="rtDate DATE,";
$sSQL.="rtApproved TINYINT(1) DEFAULT 0,";
$sSQL.="rtIPAddress VARCHAR(255) NULL,";
$sSQL.="rtPosterName VARCHAR(255),";
$sSQL.="rtPosterLoginID INT DEFAULT 0,";
$sSQL.="rtPosterEmail VARCHAR(255) NULL,";
$sSQL.="rtHeader VARCHAR(255) NULL,";
$sSQL.="rtComments TEXT NULL,";
$sSQL.="INDEX (rtProdID)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('ratings');

$sSQL="CREATE TABLE recentlyviewed (rvID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="rvProdID VARCHAR(255) NOT NULL,";
$sSQL.="rvProdName VARCHAR(255) NOT NULL,";
$sSQL.="rvProdSection INT NOT NULL DEFAULT 0,";
$sSQL.="rvProdURL VARCHAR(255) NOT NULL,";
$sSQL.="rvSessionID VARCHAR(50) NOT NULL,";
$sSQL.="rvCustomerID INT NOT NULL DEFAULT 0,";
$sSQL.="rvDate DATETIME NOT NULL,";
$sSQL.="INDEX (rvCustomerID),INDEX (rvDate),INDEX (rvProdId),INDEX (rvProdSection),INDEX (rvSessionID)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('recentlyviewed');

$sSQL="CREATE TABLE relatedprods (rpProdID VARCHAR(128) NOT NULL,";
$sSQL.="rpRelProdID VARCHAR(128) NOT NULL,";
$sSQL.="PRIMARY KEY (rpProdID, rpRelProdID)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('relatedprods');

$sSQL="CREATE TABLE searchcriteria (scID INT PRIMARY KEY,";
$sSQL.="scOrder INT DEFAULT 0,";
$sSQL.="scGroup INT DEFAULT 0,";
$sSQL.="scName VARCHAR(255) NULL,";
$sSQL.="scName2 VARCHAR(255) NULL,";
$sSQL.="scName3 VARCHAR(255) NULL,";
$sSQL.="scWorkingName VARCHAR(255) NULL,";
$sSQL.="scLogo VARCHAR(255) NULL,";
$sSQL.="scURL VARCHAR(255) NULL,";
$sSQL.="scURL2 VARCHAR(255) NULL,";
$sSQL.="scURL3 VARCHAR(255) NULL,";
$sSQL.="scEmail VARCHAR(255) NULL,";
$sSQL.="scDescription TEXT NULL,";
$sSQL.="scDescription2 TEXT NULL,";
$sSQL.="scDescription3 TEXT NULL,";
$sSQL.="scHeader TEXT NULL,";
$sSQL.="scHeader2 TEXT NULL,";
$sSQL.="scHeader3 TEXT NULL,";
$sSQL.="scNotes TEXT NULL,";
$sSQL.="INDEX (scGroup),INDEX (scOrder)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('searchcriteria');

$sSQL="CREATE TABLE searchcriteriagroup (scgID INT PRIMARY KEY,";
$sSQL.="scgOrder INT DEFAULT 0,scgTitle VARCHAR(128) NOT NULL,scgTitle2 VARCHAR(128),scgTitle3 VARCHAR(128),scgWorkingName VARCHAR(128),";
$sSQL.="INDEX (scgOrder)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('searchcriteriagroup');

$sSQL="CREATE TABLE sections (sectionID INT PRIMARY KEY,";
$sSQL.="sectionName VARCHAR(255) NOT NULL,";
$sSQL.="sectionName2 VARCHAR(255) NOT NULL,";
$sSQL.="sectionName3 VARCHAR(255) NOT NULL,";
$sSQL.="sectionWorkingName VARCHAR(255),";
$sSQL.="sectionurl VARCHAR(255) NOT NULL,";
$sSQL.="sectionurl2 VARCHAR(255) NOT NULL,";
$sSQL.="sectionurl3 VARCHAR(255) NOT NULL,";
$sSQL.="sTitle VARCHAR(255) NULL,";
$sSQL.="sMetaDesc VARCHAR(255) NULL,";
$sSQL.="sectionImage VARCHAR(255),";
$sSQL.="sectionDescription TEXT,";
$sSQL.="sectionDescription2 TEXT,";
$sSQL.="sectionDescription3 TEXT,";
$sSQL.="sectionHeader TEXT,";
$sSQL.="sectionHeader2 TEXT,";
$sSQL.="sectionHeader3 TEXT,";
$sSQL.="topSection INT DEFAULT 0,";
$sSQL.="rootSection INT DEFAULT 0,";
$sSQL.="sectionOrder INT DEFAULT 0,";
$sSQL.="sectionDisabled TINYINT DEFAULT 0,";
$sSQL.="INDEX (sectionDisabled),INDEX (sectionOrder),INDEX (topSection)) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('sections');

ect_query("CREATE TABLE shipoptions (soIndex INT NOT NULL DEFAULT 0,soOrderID INT NOT NULL DEFAULT 0,soFreeShipExempt INT NOT NULL DEFAULT 0,soMethodName VARCHAR(255) NULL,soCost DOUBLE DEFAULT 0,soFreeShip TINYINT DEFAULT 0,soShipType INT DEFAULT 0,soDeliveryTime VARCHAR(255) NULL,soDateAdded DATETIME NOT NULL, PRIMARY KEY(soIndex,soOrderID), INDEX(soDateAdded)) ENGINE=" . $databaseengine) or print_sql_error('shipoptions');

$sSQL="CREATE TABLE states (stateID INT NOT NULL PRIMARY KEY,";
$sSQL.="stateName VARCHAR(50),";
$sSQL.="stateName2 VARCHAR(50),";
$sSQL.="stateName3 VARCHAR(50),";
$sSQL.="stateAbbrev VARCHAR(50),";
$sSQL.="stateTax DOUBLE DEFAULT 0,";
$sSQL.="stateEnabled TINYINT,";
$sSQL.="stateZone INT DEFAULT 0,";
$sSQL.="stateCountryID INT DEFAULT 0,";
$sSQL.="stateFreeShip TINYINT DEFAULT 1) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('states');

$sSQL="CREATE TABLE tmplogin (tmploginid VARCHAR(100) PRIMARY KEY,";
$sSQL.="tmploginname VARCHAR(50) NULL,";
$sSQL.="tmploginchk DOUBLE DEFAULT 0,";
$sSQL.="tmplogindate DATETIME) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('tmplogin');

$sSQL="CREATE TABLE uspsmethods (uspsID INT PRIMARY KEY,";
$sSQL.="uspsMethod VARCHAR(150) NOT NULL,";
$sSQL.="uspsShowAs VARCHAR(150) NOT NULL,";
$sSQL.="uspsUseMethod TINYINT DEFAULT 0,";
$sSQL.="uspsFSA TINYINT DEFAULT 0,";
$sSQL.="uspsLocal TINYINT DEFAULT 0) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('uspsmethods');

$sSQL="CREATE TABLE zonecharges (zcID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,";
$sSQL.="zcZone INT DEFAULT 0,";
$sSQL.="zcWeight DOUBLE DEFAULT 0,";
$sSQL.="zcRate DOUBLE DEFAULT 0,";
$sSQL.="zcRate2 DOUBLE DEFAULT 0,";
$sSQL.="zcRate3 DOUBLE DEFAULT 0,";
$sSQL.="zcRate4 DOUBLE DEFAULT 0,";
$sSQL.="zcRate5 DOUBLE DEFAULT 0,";
$sSQL.="zcRatePC TINYINT(1) DEFAULT 0,";
$sSQL.="zcRatePC2 TINYINT(1) DEFAULT 0,";
$sSQL.="zcRatePC3 TINYINT(1) DEFAULT 0,";
$sSQL.="zcRatePC4 TINYINT(1) DEFAULT 0,";
$sSQL.="zcRatePC5 TINYINT(1) DEFAULT 0) ENGINE=" . $databaseengine;
ect_query($sSQL) or print_sql_error('zonecharges');

// Dumping admin table
$guessURL = str_replace('\\', '/', "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']));
if(substr($guessURL, -1) != '/') $guessURL .= '/';

if(checktablecreated('admin')){
	ect_query("INSERT INTO admin (adminID,adminVersion,adminUser,adminPassword,adminEmail,adminStoreURL,adminProdsPerPage,adminShipping,adminCountry,adminZipCode,adminUSPSUser,adminUSPSpw,adminEmailConfirm,adminPacking,adminDelUncompleted,adminUSZones,adminUnits,adminStockManage,adminHandling,adminTweaks,adminDelCC,adminUPSUser,adminUPSpw,adminUPSAccess,currLastUpdate) VALUES (1,'Ecommerce Plus " . $sVersion . "','mystore','50481f28d0f9c62842ad64b8985ab91c','you@yourstoreurl.com','" . escape_string($guessURL) . "',8,2,1,'YOURZIP','','',0,0,4,0,1,0,0,0,7,'','','','" . date("Y-m-d H:i:s", time()-100000) . "')");
	ect_query("UPDATE admin SET sideFilter=127,sideFilterText='&Attributes&Price&Sort Order&Per Page&Filter By',sideFilterText2='&Attributes&Price&Sort Order&Per Page&Filter By',sideFilterText3='&Attributes&Price&Sort Order&Per Page&Filter By'");
}
// Audit log initial entry
if(checktablecreated('auditlog')){
	$sSQL="INSERT INTO auditlog (userID,eventType,eventDate,eventSuccess,eventOrigin,areaAffected) VALUES ('INSTALL','CREATELOG','" . date('Y-m-d H:i:s') . "',1,'','AUDITLOG')";
	ect_query($sSQL);
}
// Dumping cart table
if(checktablecreated('cart')){
	ect_query("INSERT INTO cart (cartID,cartSessionID,cartProdID,cartProdName,cartProdPrice,cartDateAdded,cartQuantity,cartOrderID,cartCompleted) VALUES (1,'935000845','pc001','#1 PC multimedia package',1200,'" . date("Y-m-d H:i:s", time()) . "',1,501,1)");
}
// Dumping cartoptions table
if(checktablecreated('cartoptions')){
	ect_query("INSERT INTO cartoptions (coID,coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff) VALUES (1,1,23,'Processor','Intel Pentium IV 1.5GHz',25.5)");
	ect_query("INSERT INTO cartoptions (coID,coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff) VALUES (2,1,28,'Hard Disk','60 Gigabytes',34)");
	ect_query("INSERT INTO cartoptions (coID,coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff) VALUES (3,1,30,'Monitor','15\" Standard',0)");
	ect_query("INSERT INTO cartoptions (coID,coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff) VALUES (4,1,35,'Network Card','Yes',15)");
}
// Dumping countries table
if(checktablecreated('countries')){
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (1,'United States of America',1,0,2,1,'en_US','USD','US')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (2,'Canada',1,0,0,2,'en_CA','CAD','CA')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (3,'Afghanistan',0,0,0,4,'','AFA','AF')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (4,'Albania',0,0,0,4,'','ALL','AL')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (5,'Algeria',0,0,0,4,'','DZD','DZ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (6,'Andorra',0,0,0,3,'','EUR','AD')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (7,'Angola',0,0,0,4,'','AOA','AO')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (8,'Anguilla',0,0,0,4,'','XCD','AI')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (10,'Antigua and Barbuda',0,0,0,4,'','XCD','AG')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (11,'Argentina',1,0,0,2,'es_AR','ARS','AR')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (12,'Armenia',0,0,0,4,'','AMD','AM')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (13,'Aruba',0,0,0,4,'','AWG','AW')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (14,'Australia',1,0,0,4,'en_AU','AUD','AU')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (15,'Austria',1,0,0,3,'de_AT','EUR','AT')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (16,'Azerbaijan',0,0,0,4,'','AZM','AZ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (17,'Bahamas',1,0,0,4,'en_US','BSD','BS')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (18,'Bahrain',0,0,0,4,'','BHD','BH')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (19,'Bangladesh',0,0,0,4,'','BDT','BD')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (20,'Barbados',0,0,0,4,'','BBD','BB')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (21,'Belarus',0,0,0,4,'','BYR','BY')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (22,'Belgium',1,0,0,3,'fr_BE','EUR','BE')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (23,'Belize',0,0,0,4,'','BZD','BZ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (24,'Benin',0,0,0,4,'','XOF','BJ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (25,'Bermuda',0,0,0,4,'','BMD','BM')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (26,'Bhutan',0,0,0,4,'','BTN','BT')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (27,'Bolivia',0,0,0,2,'','BOB','BO')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (28,'Bosnia-Herzegovina',0,0,0,4,'','BAM','BA')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (29,'Botswana',0,0,0,4,'','BWP','BW')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (30,'Brazil',1,0,0,2,'pt_BR','BRL','BR')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (31,'Brunei Darussalam',0,0,0,4,'','BND','BN')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (32,'Bulgaria',0,0,0,4,'','BGN','BG')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (33,'Burkina Faso',0,0,0,4,'','XOF','BF')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (34,'Burundi',0,0,0,4,'','BIF','BI')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (35,'Cambodia',0,0,0,4,'','KHR','KH')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (36,'Cameroon',0,0,0,4,'','XAF','CM')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (37,'Cape Verde',0,0,0,4,'','CVE','CV')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (38,'Cayman Islands',0,0,0,4,'','KYD','KY')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (39,'Central African Republic',0,0,0,4,'','XAF','CF')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (40,'Chad',0,0,0,4,'','XAF','TD')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (41,'Chile',1,0,0,2,'es_CL','CLP','CL')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (42,'China',0,0,0,4,'zh_CN','CNY','CN')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (43,'Colombia',0,0,0,2,'es_CO','COP','CO')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (44,'Comoros',0,0,0,4,'','KMF','KM')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (45,'Costa Rica',1,0,0,2,'es_CR','CRC','CR')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (46,'Croatia',0,0,0,4,'hr_HR','HRK','HR')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (47,'Cuba',0,0,0,4,'','CUP','CU')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (48,'Cyprus',0,0,0,4,'el_CY','EUR','CY')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (49,'Czech Republic',0,0,0,4,'cs_CZ','CZK','CZ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (50,'Denmark',1,0,0,3,'da_DK','DKK','DK')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (51,'Djibouti',0,0,0,4,'','DJF','DJ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (52,'Dominica',0,0,0,4,'','XCD','DM')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (53,'Dominican Republic',1,0,0,4,'','DOP','DO')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (54,'East Timor',0,0,0,4,'','IDR','TP')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (55,'Ecuador',0,0,0,4,'es_EC','USD','EC')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (56,'Egypt',0,0,0,4,'','EGP','EG')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (57,'El Salvador',0,0,0,2,'es_SV','USD','SV')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (58,'Equatorial Guinea',0,0,0,4,'','XAF','GQ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (59,'Estonia',0,0,0,4,'et_EE','EEK','EE')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (60,'Ethiopia',0,0,0,4,'','ETB','ET')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (61,'Falkland Islands',0,0,0,4,'','FKP','FK')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (62,'Faroe Islands',0,0,0,4,'fo_FO','DKK','FO')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (63,'Fiji',0,0,0,4,'','FJD','FJ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (64,'Finland',1,0,0,3,'su_FI','EUR','FI')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (65,'France',1,0,0,3,'fr_FR','EUR','FR')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (66,'French Guiana',0,0,0,4,'','EUR','GF')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (67,'French Polynesia',0,0,0,4,'','XPF','PF')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (68,'Gabon',0,0,0,4,'','XAF','GA')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (69,'Gambia',0,0,0,4,'','GMD','GM')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (70,'Georgia, Republic of',0,0,0,4,'ka_GE','GEL','GE')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (71,'Germany',1,0,0,3,'de_DE','EUR','DE')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (72,'Ghana',0,0,0,4,'','GHC','GH')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (73,'Gibraltar',1,0,0,3,'en_EN','GBP','GI')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (74,'Greece',1,0,0,3,'el_GR','EUR','GR')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (75,'Greenland',1,0,0,3,'kl_GL','DKK','GL')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (76,'Grenada',0,0,0,4,'','XCD','GD')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (77,'Guadeloupe',0,0,0,4,'','EUR','GP')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (78,'Guam',0,0,0,1,'en_GU','USD','GU')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (79,'Guatemala',1,0,0,2,'es_GT','GTQ','GT')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (80,'Guinea',0,0,0,4,'','GNF','GN')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (81,'Guinea-Bissau',0,0,0,4,'','XOF','GW')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (82,'Guyana',0,0,0,2,'','GYD','GY')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (83,'Haiti',0,0,0,4,'','USD','HT')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (84,'Honduras',0,0,0,2,'es_HN','HNL','HN')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (85,'Hong Kong',1,0,0,4,'en_HK','HKD','HK')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (86,'Hungary',0,0,0,4,'hu_HU','HUF','HU')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (87,'Iceland',1,0,0,3,'is_IS','ISK','IS')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (88,'India',0,0,0,4,'en_IN','INR','IN')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (89,'Indonesia',0,0,0,4,'id_ID','IDR','ID')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (90,'Iraq',0,0,0,4,'ar_IQ','IQD','IQ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (91,'Ireland',1,0,0,3,'en_IE','EUR','IE')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (92,'Israel',1,0,0,4,'he_IL','ILS','IL')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (93,'Italy',1,0,0,3,'it_IT','EUR','IT')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (94,'Jamaica',0,0,0,4,'en_JM','JMD','JM')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (95,'Japan',1,0,0,4,'jp_JP','JPY','JP')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (96,'Jordan',0,0,0,4,'ar_JO','JOD','JO')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (97,'Kazakhstan',0,0,0,4,'','KZT','KZ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (98,'Kenya',0,0,0,4,'','KES','KE')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (99,'Kiribati',0,0,0,4,'','AUD','KI')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (100,'North Korea',0,0,0,4,'ko_KR','KPW','KP')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (101,'South Korea',0,0,0,4,'ko_KR','KRW','KR')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (102,'Kuwait',0,0,0,4,'ar_KW','KWD','KW')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (103,'Latvia',0,0,0,4,'lv_LV','LVL','LV')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (104,'Lebanon',0,0,0,4,'ar_LB','LBP','LB')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (105,'Lesotho',0,0,0,4,'st_LS','LSL','LS')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (106,'Liberia',0,0,0,4,'','LRD','LR')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (107,'England',0,0,0,3,'','GBP','GB')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (108,'Liechtenstein',0,0,0,4,'de_LI','CHF','LI')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (109,'Lithuania',0,0,0,4,'lt_LT','LTL','LT')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (110,'Luxembourg',1,0,0,3,'de_LU','EUR','LU')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (111,'Macao',0,0,0,4,'','MOP','MO')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (112,'Macedonia, Republic of',0,0,0,4,'mk_MK','MKD','MK')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (113,'Madagascar',0,0,0,4,'','MGF','MG')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (114,'Malawi',0,0,0,4,'','MWK','MW')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (115,'Malaysia',1,0,0,4,'ms_MY','MYR','MY')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (116,'Maldives',0,0,0,4,'dv_MV','MVR','MV')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (117,'Mali',0,0,0,4,'','XOF','ML')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (118,'Malta',0,0,0,4,'en_MT','EUR','MT')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (119,'Martinique',0,0,0,4,'','EUR','MQ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (120,'Mauritania',0,0,0,4,'','MRO','MR')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (121,'Mauritius',0,0,0,4,'','MUR','MU')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (122,'Mexico',1,0,0,2,'es_MX','MXN','MX')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (123,'Moldova',0,0,0,4,'ro_MD','MDL','MD')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (124,'Monaco',1,0,0,3,'fr_MC','EUR','MC')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (125,'Mongolia',0,0,0,4,'mn_CN','MNT','MN')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (126,'Montserrat',0,0,0,4,'','XCD','MS')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (127,'Morocco',0,0,0,4,'ar_MA','MAD','MA')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (128,'Mozambique',0,0,0,4,'','MZM','MZ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (129,'Myanmar',0,0,0,4,'','MMK','MM')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (130,'Namibia',0,0,0,4,'','NAD','NA')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (131,'Nauru',0,0,0,4,'','AUD','NR')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (132,'Nepal',0,0,0,4,'ne_NP','NPR','NP')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (133,'Netherlands',1,0,0,3,'nl_NL','EUR','NL')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (134,'Netherlands Antilles',0,0,0,4,'','ANG','AN')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (135,'New Caledonia',0,0,0,4,'','XPF','NC')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (136,'New Zealand',1,0,0,4,'en_NZ','NZD','NZ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (137,'Nicaragua',0,0,0,2,'','NIO','NI')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (138,'Niger',0,0,0,4,'','XOF','NE')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (139,'Nigeria',0,0,0,4,'','NGN','NG')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (140,'Niue',0,0,0,4,'','NZD','NU')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (141,'Norfolk Island',0,0,0,4,'','AUD','NF')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (142,'Northern Ireland',1,0,0,3,'en_GB','GBP','GB')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (143,'Norway',1,0,0,3,'no_NO','NOK','NO')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (144,'Oman',0,0,0,4,'ar_OM','OMR','OM')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (145,'Pakistan',0,0,0,4,'en_PK','PKR','PK')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (146,'Panama',1,0,0,2,'es_PA','PAB','PA')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (147,'Papua New Guinea',0,0,0,4,'','PGK','PG')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (148,'Paraguay',0,0,0,4,'','PYG','PY')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (149,'Peru',0,0,0,2,'es_PE','PEN','PE')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (150,'Philippines',0,0,0,4,'en_PH','PHP','PH')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (151,'Pitcairn Island',0,0,0,4,'','NZD','PN')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (152,'Poland',0,0,0,4,'pl_PL','PLN','PL')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (153,'Portugal',1,0,0,3,'pt_PT','EUR','PT')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (154,'Qatar',0,0,0,4,'ar_QA','QAR','QA')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (155,'Reunion',0,0,0,4,'','EUR','RE')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (156,'Romania',0,0,0,4,'ro_RO','RON','RO')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (157,'Russia',0,0,0,4,'ru_RU','RUB','RU')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (158,'Rwanda',0,0,0,4,'','RWF','RW')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (159,'Saint Kitts',0,0,0,4,'','XCD','KN')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (160,'Saint Lucia',0,0,0,4,'','XCD','LC')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (161,'Saint Vincent and the Grenadines',0,0,0,4,'','XCD','VC')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (162,'Western Samoa',0,0,0,4,'','WST','WS')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (163,'San Marino',0,0,0,4,'','EUR','SM')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (164,'Sao Tome and Principe',0,0,0,4,'','STD','ST')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (165,'Saudi Arabia',0,0,0,4,'ar_SA','SAR','SA')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (166,'Senegal',0,0,0,4,'','XOF','SN')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (167,'Seychelles',0,0,0,4,'','SCR','SC')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (168,'Sierra Leone',0,0,0,4,'','SLL','SL')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (169,'Singapore',1,0,0,4,'en_SG','SGD','SG')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (170,'Slovak Republic',0,0,0,4,'','SKK','SK')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (171,'Slovenia',0,0,0,4,'sl_SI','EUR','SI')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (172,'Solomon Islands',0,0,0,4,'','SBD','SB')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (173,'Somalia',0,0,0,4,'','SOS','SO')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (174,'South Africa',0,0,0,4,'en_ZA','ZAR','ZA')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (175,'Spain',1,0,0,3,'es_ES','EUR','ES')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (176,'Sri Lanka',0,0,0,4,'si_LK','LKR','LK')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (177,'Saint Helena',0,0,0,4,'','SHP','SH')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (178,'Saint Pierre and Miquelon',0,0,0,4,'','EUR','PM')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (179,'Sudan',0,0,0,4,'ar_SD','SDD','SD')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (180,'Suriname',0,0,0,4,'','SRG','SR')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (181,'Swaziland',0,0,0,4,'','SZL','SZ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (182,'Sweden',1,0,0,3,'sv_SE','SEK','SE')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (183,'Switzerland',1,0,0,3,'fr_CH','CHF','CH')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (184,'Syrian Arab Republic',0,0,0,4,'ar_SY','SYP','SY')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (185,'Taiwan',1,0,0,4,'zh_TW','TWD','TW')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (186,'Tajikistan',0,0,0,4,'tg_TJ','TJS','TJ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (187,'Tanzania',0,0,0,4,'','TZS','TZ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (188,'Thailand',1,0,0,4,'th_TH','THB','TH')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (189,'Togo',0,0,0,4,'ee_TG','XOF','TG')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (190,'Tokelau',0,0,0,4,'','NZD','TK')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (191,'Tonga',0,0,0,4,'to_TO','TOP','TO')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (192,'Trinidad and Tobago',0,0,0,4,'en_TT','TTD','TT')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (193,'Tunisia',0,0,0,4,'ar_TN','TND','TN')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (194,'Turkey',0,0,0,4,'tr_TR','TRY','TR')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (195,'Turkmenistan',0,0,0,4,'','TMM','TM')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (196,'Turks and Caicos Islands',0,0,0,4,'','USD','TC')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (197,'Tuvalu',0,0,0,4,'','TVD','TV')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (198,'Uganda',0,0,0,4,'','UGX','UG')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (199,'Ukraine',0,0,0,4,'','UAH','UA')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (200,'United Arab Emirates',0,0,0,4,'ar_AE','AED','AE')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (201,'Great Britain',1,0,1,3,'en_GB','GBP','GB')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (202,'Uruguay',0,0,0,4,'es_UY','UYU','UY')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (203,'Uzbekistan',0,0,0,4,'uz_UZ','UZS','UZ')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (204,'Vanuatu',0,0,0,4,'','VUV','VU')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (205,'Vatican City',1,0,0,3,'','EUR','VA')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (206,'Venezuela',0,0,0,2,'es_VE','VEF','VE')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (207,'Vietnam',0,0,0,4,'vi_VN','VND','VN')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (208,'British Virgin Islands',0,0,0,4,'en_VI','USD','VG')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (209,'Wallis and Futuna Islands',0,0,0,4,'','XPF','WF')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (210,'Yemen',0,0,0,4,'ar_YE','YER','YE')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (211,'Zambia',0,0,0,4,'','ZMK','ZM')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (212,'Zimbabwe',0,0,0,4,'','ZWD','ZW')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (213,'Iran',0,0,0,4,'fa_IR','IRR','IR')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (214,'Channel Islands',0,0,0,3,'','GBP','GB')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (215,'Puerto Rico',0,0,0,3,'es_PR','USD','PR')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (216,'Isle of Man',0,0,0,3,'','GBP','GB')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (217,'Azores',0,0,0,3,'','EUR','PT')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (218,'Corsica',0,0,0,3,'','EUR','FR')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (219,'Balearic Islands',0,0,0,3,'','EUR','ES')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (221,'Serbia',0,0,0,3,'sr_RS','RSD','RS')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (222,'Ivory Coast',0,0,0,3,'','XOF','CI')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (223,'Montenegro',0,0,0,3,'','EUR','ME')");
	ect_query("INSERT INTO countries (countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryLCID,countryCurrency,countryCode) VALUES (224,'American Samoa',0,0,0,3,'en_AS','USD','AS')");

	ect_query("UPDATE countries SET countryNumCurrency=784 WHERE countryCurrency='AED'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=971 WHERE countryCurrency='AFN'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=008 WHERE countryCurrency='ALL'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=051 WHERE countryCurrency='AMD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=532 WHERE countryCurrency='ANG'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=973 WHERE countryCurrency='AOA'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=032 WHERE countryCurrency='ARS'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=036 WHERE countryCurrency='AUD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=533 WHERE countryCurrency='AWG'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=944 WHERE countryCurrency='AZN'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=977 WHERE countryCurrency='BAM'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=052 WHERE countryCurrency='BBD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=050 WHERE countryCurrency='BDT'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=975 WHERE countryCurrency='BGN'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=048 WHERE countryCurrency='BHD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=108 WHERE countryCurrency='BIF'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=060 WHERE countryCurrency='BMD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=096 WHERE countryCurrency='BND'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=068 WHERE countryCurrency='BOB'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=984 WHERE countryCurrency='BOV'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=986 WHERE countryCurrency='BRL'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=044 WHERE countryCurrency='BSD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=064 WHERE countryCurrency='BTN'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=072 WHERE countryCurrency='BWP'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=974 WHERE countryCurrency='BYR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=084 WHERE countryCurrency='BZD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=124 WHERE countryCurrency='CAD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=976 WHERE countryCurrency='CDF'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=947 WHERE countryCurrency='CHE'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=756 WHERE countryCurrency='CHF'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=948 WHERE countryCurrency='CHW'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=990 WHERE countryCurrency='CLF'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=152 WHERE countryCurrency='CLP'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=156 WHERE countryCurrency='CNY'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=170 WHERE countryCurrency='COP'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=970 WHERE countryCurrency='COU'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=188 WHERE countryCurrency='CRC'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=931 WHERE countryCurrency='CUC'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=192 WHERE countryCurrency='CUP'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=132 WHERE countryCurrency='CVE'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=203 WHERE countryCurrency='CZK'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=262 WHERE countryCurrency='DJF'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=208 WHERE countryCurrency='DKK'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=214 WHERE countryCurrency='DOP'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=012 WHERE countryCurrency='DZD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=233 WHERE countryCurrency='EEK'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=818 WHERE countryCurrency='EGP'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=232 WHERE countryCurrency='ERN'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=230 WHERE countryCurrency='ETB'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=978 WHERE countryCurrency='EUR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=242 WHERE countryCurrency='FJD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=238 WHERE countryCurrency='FKP'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=826 WHERE countryCurrency='GBP'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=981 WHERE countryCurrency='GEL'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=936 WHERE countryCurrency='GHS'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=292 WHERE countryCurrency='GIP'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=270 WHERE countryCurrency='GMD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=324 WHERE countryCurrency='GNF'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=320 WHERE countryCurrency='GTQ'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=328 WHERE countryCurrency='GYD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=344 WHERE countryCurrency='HKD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=340 WHERE countryCurrency='HNL'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=191 WHERE countryCurrency='HRK'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=332 WHERE countryCurrency='HTG'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=348 WHERE countryCurrency='HUF'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=360 WHERE countryCurrency='IDR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=376 WHERE countryCurrency='ILS'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=356 WHERE countryCurrency='INR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=368 WHERE countryCurrency='IQD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=364 WHERE countryCurrency='IRR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=352 WHERE countryCurrency='ISK'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=388 WHERE countryCurrency='JMD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=400 WHERE countryCurrency='JOD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=392 WHERE countryCurrency='JPY'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=404 WHERE countryCurrency='KES'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=417 WHERE countryCurrency='KGS'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=116 WHERE countryCurrency='KHR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=174 WHERE countryCurrency='KMF'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=408 WHERE countryCurrency='KPW'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=410 WHERE countryCurrency='KRW'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=414 WHERE countryCurrency='KWD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=136 WHERE countryCurrency='KYD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=398 WHERE countryCurrency='KZT'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=418 WHERE countryCurrency='LAK'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=422 WHERE countryCurrency='LBP'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=144 WHERE countryCurrency='LKR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=430 WHERE countryCurrency='LRD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=426 WHERE countryCurrency='LSL'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=440 WHERE countryCurrency='LTL'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=428 WHERE countryCurrency='LVL'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=434 WHERE countryCurrency='LYD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=504 WHERE countryCurrency='MAD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=498 WHERE countryCurrency='MDL'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=969 WHERE countryCurrency='MGA'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=807 WHERE countryCurrency='MKD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=104 WHERE countryCurrency='MMK'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=496 WHERE countryCurrency='MNT'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=446 WHERE countryCurrency='MOP'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=478 WHERE countryCurrency='MRO'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=480 WHERE countryCurrency='MUR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=462 WHERE countryCurrency='MVR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=454 WHERE countryCurrency='MWK'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=484 WHERE countryCurrency='MXN'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=979 WHERE countryCurrency='MXV'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=458 WHERE countryCurrency='MYR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=943 WHERE countryCurrency='MZN'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=516 WHERE countryCurrency='NAD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=566 WHERE countryCurrency='NGN'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=558 WHERE countryCurrency='NIO'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=578 WHERE countryCurrency='NOK'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=524 WHERE countryCurrency='NPR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=554 WHERE countryCurrency='NZD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=512 WHERE countryCurrency='OMR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=590 WHERE countryCurrency='PAB'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=604 WHERE countryCurrency='PEN'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=598 WHERE countryCurrency='PGK'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=608 WHERE countryCurrency='PHP'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=586 WHERE countryCurrency='PKR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=985 WHERE countryCurrency='PLN'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=600 WHERE countryCurrency='PYG'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=634 WHERE countryCurrency='QAR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=946 WHERE countryCurrency='RON'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=941 WHERE countryCurrency='RSD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=643 WHERE countryCurrency='RUB'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=646 WHERE countryCurrency='RWF'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=682 WHERE countryCurrency='SAR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=090 WHERE countryCurrency='SBD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=690 WHERE countryCurrency='SCR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=938 WHERE countryCurrency='SDG'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=752 WHERE countryCurrency='SEK'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=702 WHERE countryCurrency='SGD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=654 WHERE countryCurrency='SHP'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=694 WHERE countryCurrency='SLL'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=706 WHERE countryCurrency='SOS'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=968 WHERE countryCurrency='SRD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=678 WHERE countryCurrency='STD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=760 WHERE countryCurrency='SYP'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=748 WHERE countryCurrency='SZL'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=764 WHERE countryCurrency='THB'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=972 WHERE countryCurrency='TJS'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=934 WHERE countryCurrency='TMT'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=788 WHERE countryCurrency='TND'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=776 WHERE countryCurrency='TOP'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=949 WHERE countryCurrency='TRY'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=780 WHERE countryCurrency='TTD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=901 WHERE countryCurrency='TWD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=834 WHERE countryCurrency='TZS'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=980 WHERE countryCurrency='UAH'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=800 WHERE countryCurrency='UGX'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=840 WHERE countryCurrency='USD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=997 WHERE countryCurrency='USN'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=998 WHERE countryCurrency='USS'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=858 WHERE countryCurrency='UYU'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=860 WHERE countryCurrency='UZS'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=937 WHERE countryCurrency='VEF'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=704 WHERE countryCurrency='VND'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=548 WHERE countryCurrency='VUV'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=882 WHERE countryCurrency='WST'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=950 WHERE countryCurrency='XAF'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=961 WHERE countryCurrency='XAG'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=959 WHERE countryCurrency='XAU'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=955 WHERE countryCurrency='XBA'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=956 WHERE countryCurrency='XBB'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=957 WHERE countryCurrency='XBC'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=958 WHERE countryCurrency='XBD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=951 WHERE countryCurrency='XCD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=960 WHERE countryCurrency='XDR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=952 WHERE countryCurrency='XOF'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=964 WHERE countryCurrency='XPD'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=953 WHERE countryCurrency='XPF'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=962 WHERE countryCurrency='XPT'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=886 WHERE countryCurrency='YER'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=710 WHERE countryCurrency='ZAR'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=894 WHERE countryCurrency='ZMK'") or print_sql_error('');
	ect_query("UPDATE countries SET countryNumCurrency=932 WHERE countryCurrency='ZWL'") or print_sql_error('');
	
	ect_query('UPDATE countries SET loadStates=2') or print_sql_error('');
}
// Email Messages table data
if(checktablecreated('emailmessages')){
	ect_query("INSERT INTO emailmessages (emailID) VALUES (1)") or print_sql_error('');
	ect_query("UPDATE emailmessages SET giftcertsubject='You received a gift certificate from %fromname%'") or print_sql_error('');
	ect_query("UPDATE emailmessages SET giftcertemail='Hi %toname%, %fromname% has sent you a gift certificate to the value of %value%!<br />{Your friend left the following message: %message%}<br />To redeem your gift certificate, simply pop along to our online store at:<br />%storeurl%<br />Then select the goods you require and when checking out enter the gift certificate code below:<br />%certificateid%'") or print_sql_error('');
	ect_query("UPDATE emailmessages SET giftcertsendersubject='You sent a gift certificate to %toname%'") or print_sql_error('');
	ect_query("UPDATE emailmessages SET giftcertsender='You sent a gift certificate to %toname%.<br />Below is a copy of the email they will receive. You may want to check it was delivered.'") or print_sql_error('');

	ect_query("UPDATE emailmessages SET emailsubject='Thank you for your order'") or print_sql_error('');
	ect_query("UPDATE emailmessages SET emailheaders='%emailmessage%<br />'") or print_sql_error('');

	ect_query("UPDATE emailmessages SET dropshipsubject='We have received the following order'") or print_sql_error('');
	ect_query("UPDATE emailmessages SET dropshipheaders='%emailmessage%<br />'") or print_sql_error('');
	ect_query("UPDATE emailmessages SET orderstatussubject='Order status updated'") or print_sql_error('');
	ect_query("UPDATE emailmessages SET orderstatusemail='Dear %ordername%<br />Your order id %orderid% from %orderdate% has been updated from %oldstatus% to %newstatus% on %date%.<br />{Your tracking number is %trackingnum%<br />}{Additional Info: %statusinfo%<br />}'") or print_sql_error('');

	ect_query("UPDATE emailmessages SET notifystocksubject='We now have stock for %pname%',notifystockemail='The product %pid% / %pname% is now back in stock.%nl%%nl%You can find this in our store at the following location:%nl%%link%%nl%%nl%Many Thanks%nl%%nl%%storeurl%%nl%'") or print_sql_error('');

	ect_query("UPDATE emailmessages SET giftcertsubject3=giftcertsubject,giftcertsubject2=giftcertsubject") or print_sql_error('');
	ect_query("UPDATE emailmessages SET giftcertemail3=giftcertemail,giftcertemail2=giftcertemail") or print_sql_error('');
	ect_query("UPDATE emailmessages SET giftcertsendersubject3=giftcertsendersubject,giftcertsendersubject2=giftcertsendersubject") or print_sql_error('');
	ect_query("UPDATE emailmessages SET giftcertsender3=giftcertsender,giftcertsender2=giftcertsender") or print_sql_error('');
	ect_query("UPDATE emailmessages SET emailsubject3=emailsubject,emailsubject2=emailsubject") or print_sql_error('');
	ect_query("UPDATE emailmessages SET emailheaders3=emailheaders,emailheaders2=emailheaders") or print_sql_error('');
	ect_query("UPDATE emailmessages SET dropshipsubject3=dropshipsubject,dropshipsubject2=dropshipsubject") or print_sql_error('');
	ect_query("UPDATE emailmessages SET dropshipheaders3=dropshipheaders,dropshipheaders2=dropshipheaders") or print_sql_error('');
	ect_query("UPDATE emailmessages SET orderstatussubject3=orderstatussubject,orderstatussubject2=orderstatussubject") or print_sql_error('');
	ect_query("UPDATE emailmessages SET orderstatusemail3=orderstatusemail,orderstatusemail2=orderstatusemail") or print_sql_error('');
	ect_query("UPDATE emailmessages SET notifystocksubject2=notifystocksubject,notifystocksubject3=notifystocksubject,notifystockemail2=notifystockemail,notifystockemail3=notifystockemail") or print_sql_error('');
}
// Dumping optionGroup table
if(checktablecreated('optiongroup')){
	ect_query("INSERT INTO optiongroup (optGrpID,optGrpName,optGrpWorkingName,optType,optGrpSelect) VALUES (1,'Color','Color',2,1)");
	ect_query("INSERT INTO optiongroup (optGrpID,optGrpName,optGrpWorkingName,optType,optGrpSelect) VALUES (2,'Size','Size (Jackets)',2,1)");
	ect_query("INSERT INTO optiongroup (optGrpID,optGrpName,optGrpWorkingName,optType,optGrpSelect) VALUES (4,'Size','Size (Socks)',2,1)");
	ect_query("INSERT INTO optiongroup (optGrpID,optGrpName,optGrpWorkingName,optType,optGrpSelect) VALUES (6,'Processor','Processor (Multimedia)',2,1)");
	ect_query("INSERT INTO optiongroup (optGrpID,optGrpName,optGrpWorkingName,optType,optGrpSelect) VALUES (7,'Hard Disk','Hard Disk',2,1)");
	ect_query("INSERT INTO optiongroup (optGrpID,optGrpName,optGrpWorkingName,optType,optGrpSelect) VALUES (8,'Monitor','Monitor',2,1)");
	ect_query("INSERT INTO optiongroup (optGrpID,optGrpName,optGrpWorkingName,optType,optGrpSelect) VALUES (9,'Network Card','Network Card',-2,0)");
	ect_query("INSERT INTO optiongroup (optGrpID,optGrpName,optGrpWorkingName,optType,optGrpSelect) VALUES (10,'Processor','Processor (Portables)',2,1)");
}
// Dumping options table
if(checktablecreated('options')){
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (1,1,'Blue',0,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (2,1,'Red',0,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (3,1,'Green',0,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (4,1,'Yellow',0,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (5,2,'Small',0,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (6,2,'Medium',1,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (7,2,'Large',1.5,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (8,2,'X-Large',2,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (9,2,'XX-Large',2.2,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (12,4,'8',0,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (13,4,'8 1/2',0.1,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (14,4,'9',0.15,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (15,4,'9 1/2',0.2,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (16,4,'10',0.25,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (21,6,'Intel Pentium III 1.3GHz',0,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (22,6,'Intel Pentium III 1.4GHz',15,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (23,6,'Intel Pentium IV 1.5GHz',25.5,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (24,6,'Intel Pentium IV 1.7GHz',45,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (25,6,'Intel Pentium IV 2.0GHz',65,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (26,7,'20 Gigabytes',0,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (27,7,'40 Gigabytes',10,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (28,7,'60 Gigabytes',34,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (29,7,'80 Gigabytes',44.5,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (30,8,'15\" Standard',0,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (31,8,'17\" Trinitron',22,5)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (32,8,'19\" Flatron',75,10)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (33,8,'21\" Supertron',185,20)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (34,9,'No',0,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (35,9,'Yes',15,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (36,10,'Pentium III 1.0 GHz',0,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (37,10,'Pentium III 1.3 GHz',33,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (38,10,'Pentium IV 1.5 GHz',50,0)");
	ect_query("INSERT INTO options (optID,optGroup,optName,optPriceDiff,optWeightDiff) VALUES (39,10,'Pentium IV 1.7 GHz',75,0)");
}
// Dumping orders table
if(checktablecreated('orders')){
	ect_query("INSERT INTO orders (ordID,ordSessionID,ordName,ordAddress,ordCity,ordState,ordZip,ordCountry,ordEmail,ordPhone,ordShipName,ordShipAddress,ordShipCity,ordShipState,ordShipZip,ordShipCountry,ordPayProvider,ordAuthNumber,ordShipping,ordStateTax,ordCountryTax,ordShipType,ordTotal,ordDate,ordStatusDate,ordIP,ordHandling,ordAddInfo,ordStatus,ordStatusInfo) VALUES (501,'935000845','A Customer','1212 The Street','San Jose','California','90210','United States of America','info@ecommercetemplates.com','1121212121212','','','','','','United States of America',4,'Email Only',2.5,0,0,'',1274.5,'" . date("Y-m-d H:i:s", time()) . "','" . date("Y-m-d H:i:s", time()) . "','192.168.0.1',0,'This is just an example order. It is also here to make sure your order numbers do not start at zero, which just doesn\'t look good.',3,'')");
}
// Dumping orderstatus table
if(checktablecreated('orderstatus')){
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (0,'Cancelled','Order Cancelled',0)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (1,'Deleted','Order Deleted',0)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (2,'Unauthorized','Awaiting Payment',0)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (3,'New Order','Order Received',0)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (4,'Authorized','Payment Received',1)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (5,'Packing','In Packing',1)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (6,'Shipped','Order Shipped',1)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (7,'Completed','Order Completed',1)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (8,'','',1)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (9,'','',1)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (10,'','',1)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (11,'','',1)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (12,'','',1)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (13,'','',1)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (14,'','',1)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (15,'','',1)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (16,'','',1)");
	ect_query("INSERT INTO orderstatus (statID,statPrivate,statPublic,emailstatus) VALUES (17,'','',1)");
}
// Dumping payprovider table
if(checktablecreated('payprovider')){
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (1,'PayPal','PayPal',0,1,0,'','',1)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (2,'2Checkout','Credit Card',0,1,0,'','',2)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (3,'Auth.net SIM','Credit Card',0,1,0,'','',3)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (4,'Email','Email',1,1,0,'','',4)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (5,'World Pay','Credit Card',0,1,0,'','',5)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (6,'NOCHEX','NOCHEX',0,1,0,'','',6)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (7,'Payflow Pro','Credit Card',0,1,0,'','',7)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (8,'Payflow Link','Credit Card',0,1,0,'','',8)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (9,'PayPoint.net','Credit Card',0,1,0,'','',9)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (10,'Capture Card','Credit Card',0,0,0,'XXXXXOOOOOOO','',10)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (11,'PSiGate','Credit Card',0,1,0,'','',11)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (12,'PSiGate SSL','Credit Card',0,1,0,'','',12)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (13,'Auth.net AIM','Credit Card',0,1,0,'','',13)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (14,'Custom','Credit Card',0,1,0,'','',14)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (15,'Netbanx','Credit Card',0,1,0,'','',15)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (16,'Linkpoint','Credit Card',0,1,0,'','',16)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (17,'Email 2','Email 2',0,1,0,'','',17)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (18,'PayPal Direct','Credit Card',0,1,0,'','',18)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (19,'PayPal Express','PayPal Express',1,1,0,'','',19)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (20,'Google Checkout','Google Checkout',0,0,0,'','',20)") or print_sql_error('');
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (21,'Amazon Pay','Amazon Pay',0,1,0,'','',21)") or print_sql_error('');
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (22,'PayPal Advanced','Credit Card',0,1,0,'','',22)") or print_sql_error('');
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvShow2,payProvShow3,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (23,'Stripe','Credit Card','Credit Card','Credit Card',0,1,0,'','',23)");
	ect_query("INSERT INTO payprovider (payProvID,payProvName,payProvShow,payProvShow2,payProvShow3,payProvEnabled,payProvAvailable,payProvDemo,payProvData1,payProvData2,payProvOrder) VALUES (24,'SagePay','Credit Card','Credit Card','Credit Card',0,1,0,'','',24)");
}
// Dumping postalzones table
if(checktablecreated('postalzones')){
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (1,'United States')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (2,'Zone 2')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (3,'Zone 3')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (4,'Zone 4')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (5,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (6,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (7,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (8,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (9,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (10,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (11,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (12,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (13,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (14,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (15,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (16,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (17,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (18,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (19,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (20,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (21,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (22,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (23,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (24,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (101,'All States')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (102,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (103,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (104,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (105,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (106,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (107,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (108,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (109,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (110,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (111,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (112,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (113,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (114,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (115,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (116,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (117,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (118,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (119,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (120,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (121,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (122,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (123,'')");
	ect_query("INSERT INTO postalzones (pzID,pzName) VALUES (124,'')");
	ect_query("UPDATE postalzones SET pzMethodName1='Standard Shipping',pzMethodName2='Express Shipping'");
}
// Dumping prodoptions table
if(checktablecreated('prodoptions')){
	ect_query("INSERT INTO prodoptions (poID,poProdID,poOptionGroup) VALUES (9,'monitor001',8)") or ect_error();
	ect_query("INSERT INTO prodoptions (poID,poProdID,poOptionGroup) VALUES (21,'palmtop001',6)") or ect_error();
	ect_query("INSERT INTO prodoptions (poID,poProdID,poOptionGroup) VALUES (22,'palmtop001',7)") or ect_error();
	ect_query("INSERT INTO prodoptions (poID,poProdID,poOptionGroup) VALUES (23,'mouse001',1)") or ect_error();
	ect_query("INSERT INTO prodoptions (poID,poProdID,poOptionGroup) VALUES (25,'portable001',10)") or ect_error();
	ect_query("INSERT INTO prodoptions (poID,poProdID,poOptionGroup) VALUES (26,'pc001',8)") or ect_error();
	ect_query("INSERT INTO prodoptions (poID,poProdID,poOptionGroup) VALUES (27,'pc001',6)") or ect_error();
	ect_query("INSERT INTO prodoptions (poID,poProdID,poOptionGroup) VALUES (28,'pc001',7)") or ect_error();
	ect_query("INSERT INTO prodoptions (poID,poProdID,poOptionGroup) VALUES (29,'pc001',9)") or ect_error();
	ect_query("INSERT INTO prodoptions (poID,poProdID,poOptionGroup) VALUES (30,'testproduct',4)") or ect_error();
}
// Dumping Products table
if(checktablecreated('productimages')){
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('fscanner001','prodimages/scanner2.gif',0,0)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('inkjet001','prodimages/inkjetprinter.gif',0,0)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('keyboard001','prodimages/keyboard.gif',0,0)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('lprinter001','prodimages/laserprinter.gif',0,0)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('monitor001','prodimages/monitor.gif',0,0)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('mouse001','prodimages/mouse.gif',0,0)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('palmtop001','prodimages/palmtop.gif',0,0)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('pc001','prodimages/pc.gif',0,0)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('portable001','prodimages/portable.gif',0,0)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('scanner001','prodimages/scanner.gif',0,0)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('serialcab001','prodimages/computercable.gif',0,0)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('testproduct','prodimages/computercable.gif',0,0)") or ect_error();

	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('fscanner001','prodimages/lscanner2.gif',0,1)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('inkjet001','prodimages/linkjetprinter.gif',0,1)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('keyboard001','prodimages/lkeyboard.gif',0,1)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('lprinter001','prodimages/llaserprinter.gif',0,1)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('monitor001','prodimages/lmonitor.gif',0,1)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('mouse001','prodimages/lmouse.gif',0,1)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('palmtop001','prodimages/lpalmtop.gif',0,1)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('pc001','prodimages/lpc.gif',0,1)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('portable001','prodimages/lportable.gif',0,1)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('scanner001','prodimages/lscanner.gif',0,1)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('serialcab001','prodimages/lcomputercable.gif',0,1)") or ect_error();
	ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('testproduct','prodimages/lcomputercable.gif',0,1)") or ect_error();
}
// Dumping Products table
if(checktablecreated('products')){
	ect_query("INSERT INTO products (pID,pName,pSection,pDescription,pLongdescription,pPrice,pShipping,pShipping2,pWeight,pDisplay,pSell,pExemptions,pInStock) VALUES ('fscanner001','Professional Scanner',2,'600 dpi full color quality for professional quality scanning results. Twice the resolution and twice the quality for your scans, but at an incredible low price.','600 dpi full color quality for professional quality scanning results. Twice the resolution and twice the quality for your scans, but at an incredible low price.<br />As well as a larger image, you can use this \"Long Description\" to add extra detail or information about your products.',120,5,0,4.04,1,1,0,4)") or ect_error();
	ect_query("INSERT INTO products (pID,pName,pSection,pDescription,pLongdescription,pPrice,pShipping,pShipping2,pWeight,pDisplay,pSell,pExemptions,pInStock) VALUES ('inkjet001','Inkjet Printer',4,'This inkjet printer really packs a punch for the home user. Full color prints at photo quality. Perfect for everything from letters to the bank manager, to printing out your favourite digital family pictures.','This inkjet printer really packs a punch for the home user. Full color prints at photo quality. Perfect for everything from letters to the bank manager, to printing out your favourite digital family pictures.<br />As well as a larger image, you can use this \"Long Description\" to add extra detail or information about your products.',95,4,0,2.02,1,1,0,0)") or ect_error();
	ect_query("INSERT INTO products (pID,pName,pSection,pDescription,pLongdescription,pPrice,pShipping,pShipping2,pWeight,pDisplay,pSell,pExemptions,pInStock) VALUES ('keyboard001','PC Keyboard',3,'With ergonomic tactile key action, this is a \"must buy\" for all PC users, home and professional alike. Connects via your PC\\'s serial or PS2 port.','With ergonomic tactile key action, this is a \"must buy\" for all PC users, home and professional alike. Connects via your PC\\'s serial or PS2 port.<br />As well as a larger image, you can use this \"Long Description\" to add extra detail or information about your products.',19,5,0,1,1,0,0,0)") or ect_error();
	ect_query("INSERT INTO products (pID,pName,pSection,pDescription,pLongdescription,pPrice,pShipping,pShipping2,pWeight,pDisplay,pSell,pExemptions,pInStock) VALUES ('lprinter001','Laser Printer',4,'For the small or home office, this laser printer is the perfect solution. Up to 15 black and white pages per minute, and a full 600dpi resolution for the quality your business demands.','For the small or home office, this laser printer is the perfect solution. Up to 15 black and white pages per minute, and a full 600dpi resolution for the quality your business demands.<br />As well as a larger image, you can use this \"Long Description\" to add extra detail or information about your products.',499,5,0,2,1,1,0,0)") or ect_error();
	ect_query("INSERT INTO products (pID,pName,pSection,pDescription,pLongdescription,pPrice,pShipping,pShipping2,pWeight,pDisplay,pSell,pExemptions,pInStock) VALUES ('monitor001','PC Monitor',3,'17\" full color flat screen monitor, with 0.25 dot resolution and 16.25\" viewable area.','17\" full color flat screen monitor, with 0.25 dot resolution and 16.25\" viewable area.<br />As well as a larger image, you can use this \"Long Description\" to add extra detail or information about your products.',299,5,0,2.15,1,1,0,0)") or ect_error();
	ect_query("INSERT INTO products (pID,pName,pSection,pDescription,pLongdescription,pPrice,pShipping,pShipping2,pWeight,pDisplay,pSell,pExemptions,pInStock) VALUES ('mouse001','PC Mouse',3,'Indispensible for using your PC, this mouse has easyglide action and simple connectivity to get your PC up and surfing the internet in no time.','Indispensible for using your PC, this mouse has easyglide action and simple connectivity to get your PC up and surfing the internet in no time.<br />As well as a larger image, you can use this \"Long Description\" to add extra detail or information about your products.',7,1,0,0.15,1,1,0,0)") or ect_error();
	ect_query("INSERT INTO products (pID,pName,pSection,pDescription,pLongdescription,pPrice,pShipping,pShipping2,pWeight,pDisplay,pSell,pExemptions,pInStock) VALUES ('palmtop001','Palmtop Computer',1,'The very latest in palmtop technology. All the power of a PC in a pocket sized system. Great for the mobile business person.','The very latest in palmtop technology. All the power of a PC in a pocket sized system. Great for the mobile business person.<br />As well as a larger image, you can use this \"Long Description\" to add extra detail or information about your products.',199,5,0,4.12,1,1,0,0)") or ect_error();
	ect_query("INSERT INTO products (pID,pName,pSection,pDescription,pLongdescription,pPrice,pShipping,pShipping2,pWeight,pDisplay,pSell,pExemptions,pInStock) VALUES ('pc001','#1 PC multimedia package',1,'This is an example of how you can use the product options to create advanced product descriptions with automatic price calculations.','Internet ready PC package. Just choose your monitor, hard disk size, processor speed and network card.<br />As well as a larger image, you can use this \"Long Description\" to add extra detail or information about your products. You can also include HTML Markup in the short and long product descriptions.',1200,10,0,6,1,1,0,10)") or ect_error();
	ect_query("INSERT INTO products (pID,pName,pSection,pDescription,pLongdescription,pPrice,pShipping,pShipping2,pWeight,pDisplay,pSell,pExemptions,pInStock) VALUES ('portable001','Portable PC',1,'For those on the go, this portable PC is just the thing. Your choice of processor, 256mb ram and 4gb harddisk make this the perfect solution for all types of applications. Buy now while stocks last.','For those on the go, this portable PC is just the thing. Your choice of processor, 256mb ram and 4gb harddisk make this the perfect solution for all types of applications. Buy now while stocks last.<br />As well as a larger image, you can use this \"Long Description\" to add extra detail or information about your products.',1250,6,0,2,1,1,0,0)") or ect_error();
	ect_query("INSERT INTO products (pID,pName,pSection,pDescription,pLongdescription,pPrice,pShipping,pShipping2,pWeight,pDisplay,pSell,pExemptions,pInStock) VALUES ('scanner001','Flatbed scanner',2,'Up to 300 dpi full color resolution and incredible speed make this a top choice for all your scanning needs. Scan professional quality photos, text or artwork in seconds.','Up to 300 dpi full color resolution and incredible speed make this a top choice for all your scanning needs. Scan professional quality photos, text or artwork in seconds.<br />As well as a larger image, you can use this \"Long Description\" to add extra detail or information about your products.',89,6,0,5.1,1,1,0,0)") or ect_error();
	ect_query("INSERT INTO products (pID,pName,pSection,pDescription,pLongdescription,pPrice,pShipping,pShipping2,pWeight,pDisplay,pSell,pExemptions,pInStock) VALUES ('serialcab001','PC Serial Cable',3,'Can be used for connecting PC systems to peripheral devices such as serial printers and scanners.','Can be used for connecting PC systems to peripheral devices such as serial printers and scanners.<br />As well as a larger image, you can use this \"Long Description\" to add extra detail or information about your products.',2.5,0.2,0,0.1,1,1,0,0)") or ect_error();
	ect_query("INSERT INTO products (pID,pName,pSection,pDescription,pLongdescription,pPrice,pShipping,pShipping2,pWeight,pDisplay,pSell,pExemptions,pInStock) VALUES ('testproduct','Cheap Test Product',3,'This is a cheap product for testing. Note how you can use HTML Markup in product descriptions.<br />Also note that as you change the product options, the price changes automatically.','This is a cheap product for testing. Note how you can use HTML Markup in product descriptions.<br />In the long description you can go into more detail about products.',0.01,0,0,3,1,1,0,21)") or ect_error();
}
// Dumping sections table
if(checktablecreated('sections')){
	ect_query("INSERT INTO sections (sectionID,sectionName,sectionName2,sectionName3,sectionWorkingName,sectionImage,sectionDescription,topSection,sectionOrder,rootSection,sectionurl,sectionurl2,sectionurl3) VALUES (1,'Systems','','','Systems','','Complete PC systems including tower systems, laptops and palmtop computers. The very best in PC power.',5,3,1,'','','')") or ect_error();
	ect_query("INSERT INTO sections (sectionID,sectionName,sectionName2,sectionName3,sectionWorkingName,sectionImage,sectionDescription,topSection,sectionOrder,rootSection,sectionurl,sectionurl2,sectionurl3) VALUES (2,'Scanners','','','Scanners','','RGB color scanners and scanner based systems for everything from digital snaps to professional prints.',6,5,1,'','','')") or ect_error();
	ect_query("INSERT INTO sections (sectionID,sectionName,sectionName2,sectionName3,sectionWorkingName,sectionImage,sectionDescription,topSection,sectionOrder,rootSection,sectionurl,sectionurl2,sectionurl3) VALUES (3,'Peripherals','','','Peripherals','','Keyboards, mice, cables and mousemats and all your other PC peripheral needs.',5,2,1,'','','')") or ect_error();
	ect_query("INSERT INTO sections (sectionID,sectionName,sectionName2,sectionName3,sectionWorkingName,sectionImage,sectionDescription,topSection,sectionOrder,rootSection,sectionurl,sectionurl2,sectionurl3) VALUES (4,'Printers','','','Printers','','Inkjet and laser printers for the very best in home and small office printing systems.',6,6,1,'','','')") or ect_error();
	ect_query("INSERT INTO sections (sectionID,sectionName,sectionName2,sectionName3,sectionWorkingName,sectionImage,sectionDescription,topSection,sectionOrder,rootSection,sectionurl,sectionurl2,sectionurl3) VALUES (5,'Computer Parts','','','Computer Parts','','Bits and pieces for your computer',0,1,0,'','','')") or ect_error();
	ect_query("INSERT INTO sections (sectionID,sectionName,sectionName2,sectionName3,sectionWorkingName,sectionImage,sectionDescription,topSection,sectionOrder,rootSection,sectionurl,sectionurl2,sectionurl3) VALUES (6,'Printers and Scanners','','','Printers and Scanners','','Printers and scanners for your PC',0,4,0,'','','')") or ect_error();
}
// Dumping States table
if(checktablecreated('states')){
	$nextfreeid=1;
	function addstate($stateCountryID,$stateName,$stateAbbrev){
		doaddstate($stateCountryID,$stateName,$stateAbbrev,1);
	}
	function adddisabledstate($stateCountryID,$stateName,$stateAbbrev){
		doaddstate($stateCountryID,$stateName,$stateAbbrev,0);
	}
	function doaddstate($stateCountryID,$stateName,$stateAbbrev,$stateEnabled){
		global $nextfreeid;
		$gotstateid=FALSE;
		while(! $gotstateid){
			$result = ect_query("SELECT stateID FROM states WHERE stateID=" . $nextfreeid) or ect_error();
			if(ect_num_rows($result)==0) $gotstateid=TRUE; else $nextfreeid++;
			ect_free_result($result);
		}
		ect_query("INSERT INTO states (stateID,stateCountryID,stateName,stateAbbrev,stateTax,stateEnabled,stateZone,stateFreeShip) VALUES (" . $nextfreeid . "," . $stateCountryID . ",'" . escape_string($stateName) . "','" . escape_string($stateAbbrev) . "',0," . $stateEnabled . ",0,0)") or ect_error();
	}
	// USA
	addstate(1,"Alabama","AL");
	addstate(1,"Alaska","AK");
	addstate(1,"American Samoa","AS");
	addstate(1,"Arizona","AZ");
	addstate(1,"Arkansas","AR");
	addstate(1,"California","CA");
	addstate(1,"Colorado","CO");
	addstate(1,"Connecticut","CT");
	addstate(1,"Delaware","DE");
	addstate(1,"District Of Columbia","DC");
	addstate(1,"Fdr. States Of Micronesia","FM");
	addstate(1,"Florida","FL");
	addstate(1,"Georgia","GA");
	addstate(1,"Guam","GU");
	addstate(1,"Hawaii","HI");
	addstate(1,"Idaho","ID");
	addstate(1,"Illinois","IL");
	addstate(1,"Indiana","IN");
	addstate(1,"Iowa","IA");
	addstate(1,"Kansas","KS");
	addstate(1,"Kentucky","KY");
	addstate(1,"Louisiana","LA");
	addstate(1,"Maine","ME");
	addstate(1,"Marshall Islands","MH");
	addstate(1,"Maryland","MD");
	addstate(1,"Massachusetts","MA");
	addstate(1,"Michigan","MI");
	addstate(1,"Minnesota","MN");
	addstate(1,"Mississippi","MS");
	addstate(1,"Missouri","MO");
	addstate(1,"Montana","MT");
	addstate(1,"Nebraska","NE");
	addstate(1,"Nevada","NV");
	addstate(1,"New Hampshire","NH");
	addstate(1,"New Jersey","NJ");
	addstate(1,"New Mexico","NM");
	addstate(1,"New York","NY");
	addstate(1,"North Carolina","NC");
	addstate(1,"North Dakota","ND");
	addstate(1,"Northern Mariana Islands","MP");
	addstate(1,"Ohio","OH");
	addstate(1,"Oklahoma","OK");
	addstate(1,"Oregon","OR");
	addstate(1,"Palau","PW");
	addstate(1,"Pennsylvania","PA");
	addstate(1,"Puerto Rico","PR");
	addstate(1,"Rhode Island","RI");
	addstate(1,"South Carolina","SC");
	addstate(1,"South Dakota","SD");
	addstate(1,"Tennessee","TN");
	addstate(1,"Texas","TX");
	addstate(1,"Utah","UT");
	addstate(1,"Vermont","VT");
	addstate(1,"Virgin Islands","VI");
	addstate(1,"Virginia","VA");
	addstate(1,"Washington","WA");
	addstate(1,"West Virginia","WV");
	addstate(1,"Wisconsin","WI");
	addstate(1,"Wyoming","WY");
	adddisabledstate(1,"Armed Forces Africa","AE");
	adddisabledstate(1,"Armed Forces Americas","AA");
	adddisabledstate(1,"Armed Forces Canada","AE");
	adddisabledstate(1,"Armed Forces Europe","AE");
	adddisabledstate(1,"Armed Forces Middle East","AE");
	adddisabledstate(1,"Armed Forces Pacific","AP");
	// Canada
	addstate(2,"Alberta","AB");
	addstate(2,"British Columbia","BC");
	addstate(2,"Manitoba","MB");
	addstate(2,"New Brunswick","NB");
	addstate(2,"Newfoundland","NF");
	addstate(2,"North West Territories","NT");
	addstate(2,"Nova Scotia","NS");
	addstate(2,"Nunavut","NU");
	addstate(2,"Ontario","ON");
	addstate(2,"Prince Edward Island","PE");
	addstate(2,"Quebec","QC");
	addstate(2,"Saskatchewan","SK");
	addstate(2,"Yukon Territory","YT");
	// Australia
	addstate(14,"Australian Capital Territory","ACT");
	addstate(14,"New South Wales","NSW");
	addstate(14,"Northern Territory","NT");
	addstate(14,"Queensland","QLD");
	addstate(14,"South Australia","SA");
	addstate(14,"Tasmania","TA");
	addstate(14,"Victoria","VIC");
	addstate(14,"Western Australia","WA");
	// Ireland
	addstate(91,"Carlow","CA");
	addstate(91,"Cavan","CV");
	addstate(91,"Clare","CL");
	addstate(91,"Cork","CO");
	addstate(91,"Donegal","DO");
	addstate(91,"Dublin","DU");
	addstate(91,"Galway","GA");
	addstate(91,"Kerry","KE");
	addstate(91,"Kildare","KI");
	addstate(91,"Kilkenny","KL");
	addstate(91,"Laois","LA");
	addstate(91,"Leitrim","LE");
	addstate(91,"Limerick","LI");
	addstate(91,"Longford","LO");
	addstate(91,"Louth","LU");
	addstate(91,"Mayo","MA");
	addstate(91,"Meath","ME");
	addstate(91,"Monaghan","MO");
	addstate(91,"Offaly","OF");
	addstate(91,"Roscommon","RO");
	addstate(91,"Sligo","SL");
	addstate(91,"Tipperary","TI");
	addstate(91,"Waterford","WA");
	addstate(91,"Westmeath","WE");
	addstate(91,"Wexford","WX");
	addstate(91,"Wicklow","WI");
	// New Zealand
	addstate(136,"Ashburton","AS");
	addstate(136,"Auckland","AU");
	addstate(136,"Bay of Plenty","BP");
	addstate(136,"Buller","BU");
	addstate(136,"Canterbury","CB");
	addstate(136,"Carterton","CA");
	addstate(136,"Central Otago","CO");
	addstate(136,"Clutha","CL");
	addstate(136,"Counties Manukau","CM");
	addstate(136,"Dunedin City","DC");
	addstate(136,"Far North","FN");
	addstate(136,"Franklin","FR");
	addstate(136,"Gisborne","GS");
	addstate(136,"Gore","GO");
	addstate(136,"Grey","GR");
	addstate(136,"Hamilton City","HC");
	addstate(136,"Hastings","HS");
	addstate(136,"Hauraki","HI");
	addstate(136,"Hawke's Bay","HB");
	addstate(136,"Horowhenua","HW");
	addstate(136,"Hurunui","HU");
	addstate(136,"Hutt Valley","HV");
	addstate(136,"Invercargill","IC");
	addstate(136,"Kaikoura","KK");
	addstate(136,"Kaipara","KP");
	addstate(136,"Kapiti Coast","KC");
	addstate(136,"Kawerau","KW");
	addstate(136,"Manawatu","MW");
	addstate(136,"Marlborough","MB");
	addstate(136,"Masteron","MS");
	addstate(136,"Matamata Piako","MP");
	addstate(136,"New Plymouth","NP");
	addstate(136,"North Shore City","NS");
	addstate(136,"Otaki","OT");
	addstate(136,"Otorohanga","OT");
	addstate(136,"Palmerston North","PN");
	addstate(136,"Papakura","PK");
	addstate(136,"Porirua City","PC");
	addstate(136,"Queenstown Lakes","QL");
	addstate(136,"Rotorua","RT");
	addstate(136,"Ruapehu","RU");
	addstate(136,"Selwyn","SN");
	addstate(136,"South Taranaki","ST");
	addstate(136,"South Waikato","SW");
	addstate(136,"South Wairarapa","SA");
	addstate(136,"Southland","SL");
	addstate(136,"Stratford","SF");
	addstate(136,"Tasman","TM");
	addstate(136,"Taupo","TP");
	addstate(136,"Tauranga","TR");
	addstate(136,"Thames Coromandel","TC");
	addstate(136,"Timaru","TM");
	addstate(136,"Waikato","WK");
	addstate(136,"Waimakariri","WM");
	addstate(136,"Waimate","WE");
	addstate(136,"Waiora","WO");
	addstate(136,"Waipa","WP");
	addstate(136,"Waitakere","WT");
	addstate(136,"Waitaki","WI");
	addstate(136,"Waitomo","Wa");
	addstate(136,"Wellington City","WC");
	addstate(136,"Western Bay of Plenty","WB");
	addstate(136,"Westland","WL");
	addstate(136,"Whakatane","WH");
	addstate(136,"Whanganui","WG");
	addstate(136,"Whangarei","WE");
	// South Africa
	addstate(174,"Eastern Cape","EP");
	addstate(174,"Free State","OFS");
	addstate(174,"Gauteng","GA");
	addstate(174,"Kwazulu-Natal","KZN");
	addstate(174,"Mpumalanga","MP");
	addstate(174,"Northern Cape","NC");
	addstate(174,"Limpopo","LI");
	addstate(174,"North West Province","NWP");
	addstate(174,"Western Cape","WC");
	// UK
	addstate(201,"Aberdeenshire","AB");
	addstate(201,"Angus","AG");
	addstate(201,"Argyll","AR");
	addstate(201,"Avon","AV");
	addstate(201,"Ayrshire","AY");
	addstate(201,"Banffshire","BF");
	addstate(201,"Bedfordshire","Beds");
	addstate(201,"Berkshire","Berks");
	addstate(201,"Buckinghamshire","Bucks");
	addstate(201,"Caithness","CN");
	addstate(201,"Cambridgeshire","Cambs");
	addstate(201,"Ceredigion","CE");
	addstate(201,"Cheshire","CH");
	addstate(201,"Clackmannanshire","CL");
	addstate(201,"Cleveland","CV");
	addstate(201,"Clwyd","CW");
	addstate(201,"County Antrim","Co Antrim");
	addstate(201,"County Armagh","Co Armagh");
	addstate(201,"County Down","Co Down");
	addstate(201,"Durham","Durham");
	addstate(201,"County Fermanagh","Co Fermanagh");
	addstate(201,"County Londonderry","Co Londonderry");
	addstate(201,"County Tyrone","Co Tyrone");
	addstate(201,"Cornwall","CO");
	addstate(201,"Cumbria","CU");
	addstate(201,"Derbyshire","DB");
	addstate(201,"Devon","DV");
	addstate(201,"Dorset","DO");
	addstate(201,"Dumfriesshire","DF");
	addstate(201,"Dunbartonshire","DU");
	addstate(201,"Dyfed","DY");
	addstate(201,"East Lothian","EL");
	addstate(201,"East Sussex","E Sussex");
	addstate(201,"Essex","EX");
	addstate(201,"Fife","FI");
	addstate(201,"Gloucestershire","Glos");
	addstate(201,"Gwent","GW");
	addstate(201,"Gwynedd","GY");
	addstate(201,"Hampshire","Hants");
	addstate(201,"Herefordshire","HE");
	addstate(201,"Hertfordshire","Herts");
	addstate(201,"Inverness-shire","IS");
	addstate(201,"Isle of Mull","IsMu");
	addstate(201,"Shetland","IsSh");
	addstate(201,"Isle of Skye","IsSk");
	addstate(201,"Isle of Wight","IsWi");
	addstate(201,"Isles of Scilly","IsSc");
	addstate(201,"Kent","KE");
	addstate(201,"Kincardineshire","KI");
	addstate(201,"Kinross-shire","KR");
	addstate(201,"Kirkcudbrightshire","KK");
	addstate(201,"Lanarkshire","LK");
	addstate(201,"Lancashire","Lancs");
	addstate(201,"Leicestershire","Leics");
	addstate(201,"Lincolnshire","Lincs");
	addstate(201,"London","LO");
	addstate(201,"Merseyside","ME");
	addstate(201,"Mid Glamorgan","M Glam");
	addstate(201,"Midlothian","MI");
	addstate(201,"Middlesex","Middx");
	addstate(201,"Moray","MO");
	addstate(201,"Nairnshire","NA");
	addstate(201,"Norfolk","NO");
	addstate(201,"North Humberside","N Humberside");
	addstate(201,"North Yorkshire","N Yorkshire");
	addstate(201,"Northamptonshire","Northants");
	addstate(201,"Northumberland","Northd");
	addstate(201,"Nottinghamshire","Notts");
	addstate(201,"Oxfordshire","Oxon");
	addstate(201,"Peebleshire","PE");
	addstate(201,"Perthshire","PR");
	addstate(201,"Powys","PO");
	addstate(201,"Renfrewshire","RE");
	addstate(201,"Ross-shire","RO");
	addstate(201,"Roxburghshire","RX");
	addstate(201,"Selkirkshire","SK");
	addstate(201,"Shropshire","SR");
	addstate(201,"Somerset","SO");
	addstate(201,"South Glamorgan","S Glam");
	addstate(201,"South Humberside","S Humberside");
	addstate(201,"South Yorkshire","S Yorkshire");
	addstate(201,"Staffordshire","Staffs");
	addstate(201,"Stirlingshire","SS");
	addstate(201,"Suffolk","SF");
	addstate(201,"Surrey","SY");
	addstate(201,"Sutherland","SU");
	addstate(201,"Tyne and Wear","Tyne & Wear");
	addstate(201,"Warwickshire","Warks");
	addstate(201,"West Glamorgan","W Glam");
	addstate(201,"West Lothian","WL");
	addstate(201,"West Midlands","W Midlands");
	addstate(201,"West Sussex","W Sussex");
	addstate(201,"West Yorkshire","W Yorkshire");
	addstate(201,"Wigtownshire","WT");
	addstate(201,"Wiltshire","Wilts");
	addstate(201,"Worcestershire","Worcs");
	addstate(201,"Yorkshire","EY");
	addstate(201,"Carmarthenshire","CS");
	addstate(201,"Berwickshire","BS");
	addstate(201,"Anglesey","AN");
	addstate(201,"Pembrokeshire","PK");
	addstate(201,"Flintshire","FS");
	addstate(201,"Rutland","RD");
	addstate(201,"Glamorgan","AA");
	addstate(201,"Cardiff","AA");
	addstate(201,"Bristol","AA");
	addstate(201,"Manchester","AA");
	addstate(201,"Birmingham","AA");
	addstate(201,"Glasgow","AA");
	addstate(201,"Edinburgh","AA");
	
	adddisabledstate(201,"BFPO","FO");
	adddisabledstate(201,"APO/FPO","AO");
	
	adddisabledstate(201,'Orkney','ORK');
	adddisabledstate(201,'Denbighshire','DEN');
	adddisabledstate(201,'Monmouthshire','MON');
	adddisabledstate(201,'Rhondda Cynon Taff','RON');
	adddisabledstate(201,'Channel Islands','CHI');
	adddisabledstate(201,'Isle of Man','ISM');

	// Denmark
	addstate(50,"Bornholm","BH");
	addstate(50,"Falster","FA");
	addstate(50,"Fyn","FY");
	addstate(50,"Jylland","JY");
	addstate(50,"Sjaelland","SJ");
	// France
	addstate(65,"Ain","01");
	addstate(65,"Aisne","02");
	addstate(65,"Allier","03");
	addstate(65,"Alpes de Haute Provence","04");
	addstate(65,"Hautes Alpes","05");
	addstate(65,"Alpes Maritimes","06");
	addstate(65,"Ard&egrave;che","07");
	addstate(65,"Ardennes","08");
	addstate(65,"Ari&egrave;ge","09");
	addstate(65,"Aube","10");
	addstate(65,"Aude","11");
	addstate(65,"Averyon","12");
	addstate(65,"Bouche du Rh&ocirc;ne","13");
	addstate(65,"Calvados","14");
	addstate(65,"Cantal","15");
	addstate(65,"Charente","16");
	addstate(65,"Charente Maritime","17");
	addstate(65,"Cher","18");
	addstate(65,"Corr&egrave;ze","19");
	addstate(65,"Corse du Sud","2a");
	addstate(65,"Haute Corse","2b");
	addstate(65,"C&ocirc;te d'Or","21");
	addstate(65,"C&ocirc;tes d'Armor","22");
	addstate(65,"Creuse","23");
	addstate(65,"Dordogne","24");
	addstate(65,"Doubs","25");
	addstate(65,"Dr&ocirc;me","26");
	addstate(65,"Eure","27");
	addstate(65,"Eure et Loire","28");
	addstate(65,"Finist&egrave;re","29");
	addstate(65,"Gard","30");
	addstate(65,"Haute Garonne","31");
	addstate(65,"Gers","32");
	addstate(65,"Gironde","33");
	addstate(65,"Herault","34");
	addstate(65,"Ille et Vilaine","35");
	addstate(65,"Indre","36");
	addstate(65,"Indre et Loire","37");
	addstate(65,"Is&egrave;re","38");
	addstate(65,"Jura","39");
	addstate(65,"Landes","40");
	addstate(65,"Loir et Cher","41");
	addstate(65,"Loire","42");
	addstate(65,"Haute Loire","43");
	addstate(65,"Loire Atlantique","44");
	addstate(65,"Loiret","45");
	addstate(65,"Lot","46");
	addstate(65,"Lot et Garonne","47");
	addstate(65,"Loz&egrave;re","48");
	addstate(65,"Maine et Loire","49");
	addstate(65,"Manche","50");
	addstate(65,"Marne","51");
	addstate(65,"Haute Marne","52");
	addstate(65,"Mayenne","53");
	addstate(65,"Meurthe et Moselle","54");
	addstate(65,"Meuse","55");
	addstate(65,"Morbihan","56");
	addstate(65,"Moselle","57");
	addstate(65,"Ni&egrave;vre","58");
	addstate(65,"Nord","59");
	addstate(65,"Oise","60");
	addstate(65,"Orne","61");
	addstate(65,"Pas de Calais","62");
	addstate(65,"Puy de D&ocirc;me","63");
	addstate(65,"Pyren&eacute;es Atlantiques","64");
	addstate(65,"Haute Pyren&eacute;es","65");
	addstate(65,"Pyren&eacute;es orientales","66");
	addstate(65,"Bas Rhin","67");
	addstate(65,"Haut Rhin","68");
	addstate(65,"Rh&ocirc;ne","69");
	addstate(65,"Haute Sa&ocirc;ne","70");
	addstate(65,"Sa&ocirc;ne et Loire","71");
	addstate(65,"Sarthe","72");
	addstate(65,"Savoie","73");
	addstate(65,"Haute Savoie","74");
	addstate(65,"Paris","75");
	addstate(65,"Seine Maritime","76");
	addstate(65,"Seine et Marne","77");
	addstate(65,"Yvelines","78");
	addstate(65,"Deux S&egrave;vres","79");
	addstate(65,"Somme","80");
	addstate(65,"Tarn","81");
	addstate(65,"Tarn et Garonne","82");
	addstate(65,"Var","83");
	addstate(65,"Vaucluse","84");
	addstate(65,"Vend&eacute;e","85");
	addstate(65,"Vienne","86");
	addstate(65,"Haute Vienne","87");
	addstate(65,"Vosges","88");
	addstate(65,"Yonne","89");
	addstate(65,"Territoire de Belfort","90");
	addstate(65,"Essonne","91");
	addstate(65,"Hauts de Seine","92");
	addstate(65,"Seine Saint Denis","93");
	addstate(65,"Val de Marne","94");
	addstate(65,"Val d'Oise","95");
	// Germany
	addstate(71,"Baden-W&uuml;rttenberg","01");
	addstate(71,"Bayern","02");
	addstate(71,"Berlin","03");
	addstate(71,"Brandenburg","04");
	addstate(71,"Bremen","05");
	addstate(71,"Hamburg","06");
	addstate(71,"Hessen","07");
	addstate(71,"Mecklenburg-Vorpommern","08");
	addstate(71,"Niedersachsen","09");
	addstate(71,"Nordrhein-Westfalen","10");
	addstate(71,"Rheinland-Pfalz","11");
	addstate(71,"Saarland","12");
	addstate(71,"Sachsen","13");
	addstate(71,"Sachsen Anhalt","14");
	addstate(71,"Schleswig Holstein","15");
	addstate(71,"Th&uuml;ringen","16");
	// Switzerland
	addstate(183,"Aargau","AG");
	addstate(183,"Appenzell Innerrhoden","AI");
	addstate(183,"Appenzell Ausserrhoden","AR");
	addstate(183,"Basel-Stadt","BS");
	addstate(183,"Basel-Landschaft","BL");
	addstate(183,"Bern","BE");
	addstate(183,"Freiburg","FR");
	addstate(183,"Genf","GE");
	addstate(183,"Glarus","GL");
	addstate(183,"Graub&uuml;nden","GR");
	addstate(183,"Jura","JU");
	addstate(183,"Luzern","LU");
	addstate(183,"Neuenburg","NE");
	addstate(183,"Nidwalden","NW");
	addstate(183,"Obwalden","OW");
	addstate(183,"Schaffhausen","SH");
	addstate(183,"Schwyz","SZ");
	addstate(183,"Solothurn","SO");
	addstate(183,"St. Gallen","SG");
	addstate(183,"Thurgau","TG");
	addstate(183,"Tessin","TI");
	addstate(183,"Uri","UR");
	addstate(183,"Wallis","VS");
	addstate(183,"Waadt","VD");
	addstate(183,"Zug","ZG");
	addstate(183,"Z&uuml;rich","ZH");
	// Italy
	addstate(93,"Abruzzo","AL");
	addstate(93,"Basilicata","AK");
	addstate(93,"Calabria","AS");
	addstate(93,"Campania","AZ");
	addstate(93,"Emilia Romagna","AR");
	addstate(93,"Friuli Venezia Giulia","CA");
	addstate(93,"Lazio","CO");
	addstate(93,"Liguria","CT");
	addstate(93,"Lombardia","DE");
	addstate(93,"Marche","DC");
	addstate(93,"Piemonte","FM");
	addstate(93,"Puglia","FL");
	addstate(93,"Sardegna","GA");
	addstate(93,"Sicilia","GU");
	addstate(93,"Toscana","HI");
	addstate(93,"Trentino Alto Adige","ID");
	addstate(93,"Umbria","IL");
	addstate(93,"Valle d'Aosta","IN");
	addstate(93,"Veneto","IA");
	// Portugal
	addstate(153,"Aveiro","AB");
	addstate(153,"Beja","AG");
	addstate(153,"Braga","AR");
	addstate(153,"Braganca","AV");
	addstate(153,"Castelo Branco","AY");
	addstate(153,"Coimbra","BF");
	addstate(153,"Evora","BE");
	addstate(153,"Faro","BK");
	addstate(153,"Guarda","BU");
	addstate(153,"Leiria","CN");
	addstate(153,"Lisboa","CB");
	addstate(153,"Portalegre","CH");
	addstate(153,"Porto","CL");
	addstate(153,"Santarem","CV");
	addstate(153,"Setubal","CW");
	addstate(153,"Viana do Castelo","CAn");
	addstate(153,"Vila Real","CL");
	addstate(153,"Viseu","CL");
	addstate(153,"Madeira","MA");
	addstate(153,"A&ccedil;ores","AC");
	// Spain
	addstate(175,"Alava","VI");
	addstate(175,"Albacete","AB");
	addstate(175,"Alicante","A");
	addstate(175,"Almer&iacute;a","AL");
	addstate(175,"Asturias","O");
	addstate(175,"Avila","AV");
	addstate(175,"Badajoz","BA");
	addstate(175,"Barcelona","B");
	addstate(175,"Burgos","BU");
	addstate(175,"C&aacute;ceres","CC");
	addstate(175,"C&aacute;diz","CA");
	addstate(175,"Cantabria","S");
	addstate(175,"Castell&oacute;n","CS");
	addstate(175,"Ceuta","CE");
	addstate(175,"Ciudad Real","CR");
	addstate(175,"C&oacute;rdoba","CO");
	addstate(175,"Cuenca","CU");
	addstate(175,"Guip&uacute;zcoa","SS");
	addstate(175,"Girona","GI");
	addstate(175,"Granada","GR");
	addstate(175,"Guadalajara","GU");
	addstate(175,"Huelva","H");
	addstate(175,"Huesca","HU");
	addstate(175,"Islas Baleares","IB");
	addstate(175,"Ja&eacute;n","J");
	addstate(175,"La Coru&ntilde;a","C");
	addstate(175,"La Rioja","LO");
	addstate(175,"Las Palmas","GC");
	addstate(175,"Le&oacute;n","LE");
	addstate(175,"L&eacute;rida","LL");
	addstate(175,"Lugo","LU");
	addstate(175,"Madrid","M");
	addstate(175,"M&aacute;laga","MA");
	addstate(175,"Melilla","ML");
	addstate(175,"Murcia","MU");
	addstate(175,"Navarra","NA");
	addstate(175,"Orense","OR");
	addstate(175,"Palencia","P");
	addstate(175,"Pontevedra","PO");
	addstate(175,"Salamanca","SA");
	addstate(175,"Tenerife","TF");
	addstate(175,"Segovia","SG");
	addstate(175,"Sevilla","SE");
	addstate(175,"Soria","SO");
	addstate(175,"Tarragona","T");
	addstate(175,"Teruel","TE");
	addstate(175,"Toledo","TO");
	addstate(175,"Valencia","V");
	addstate(175,"Valladolid","VA");
	addstate(175,"Vizcaya","BI");
	addstate(175,"Zamora","ZA");
	addstate(175,"Zaragoza","Z");

	ect_query("UPDATE states SET stateName2=stateName");
	ect_query("UPDATE states SET stateName3=stateName");

}
// Dumping uspsmethods table
if(checktablecreated('uspsmethods')){
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (1,'EXPRESS','Express Mail',0,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (2,'PRIORITY','Priority Mail',0,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal,uspsFSA) VALUES (3,'PARCEL','Parcel Post',1,1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (14,'Media','Media Mail',0,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (15,'BPM','Bound Printed Matter',0,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (16,'FIRST CLASS','First-Class Mail',0,1)");

	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (30,'4','Global Express Guaranteed',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (31,'6','Global Express Guaranteed',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (32,'7','Global Express Guaranteed',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (33,'1','Express Mail International',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (34,'10','Express Mail International',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (35,'2','Priority Mail International',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (36,'8','Priority Mail International',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (37,'9','Priority Mail International',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (38,'13','First-Class Mail',1,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (39,'14','First-Class Mail',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (40,'15','First-Class Mail',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (41,'11','Priority Mail International',0,0)");

	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (42,'16','Priority Mail International',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (43,'17','Express Mail International',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (44,'20','Priority Mail International',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (45,'24','Priority Mail International',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (46,'26','Express Mail International',0,0)");

	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (101,'01','UPS Next Day Air&reg;',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (102,'02','UPS 2nd Day Air&reg;',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal,uspsFSA) VALUES (103,'03','UPS Ground',1,1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (104,'07','UPS Worldwide Express',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (105,'08','UPS Worldwide Expedited',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (106,'11','UPS Standard',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (107,'12','UPS 3 Day Select&reg;',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (108,'13','UPS Next Day Air Saver&reg;',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (109,'14','UPS Next Day Air&reg; Early A.M.&reg;',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (110,'54','UPS Worldwide Express Plus',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (111,'59','UPS 2nd Day Air A.M.&reg;',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (112,'65','UPS Express Saver',1,1)");

	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal,uspsFSA) VALUES (201,'DOM.RP','Regular Parcel',1,1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (202,'1020','Expedited',1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (203,'1030','Xpresspost',1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (204,'1040','Priority Courier',1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (205,'1120','Expedited Evening',1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (206,'1130','XpressPost Evening',1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (207,'1220','Expedited Saturday',1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (208,'1230','XpressPost Saturday',1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (210,'2005','Small Packets Surface',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (211,'2010','Surface USA',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (212,'2015','Small Packets Air USA',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (213,'2020','Air USA',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (214,'2025','Expedited USA Commercial',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (215,'2030','XPressPost USA',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (216,'2040','Purolator USA',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (217,'2050','PuroPak USA',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (218,'3005','Small Packets Surface International',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (221,'3010','Parcel Surface International',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (222,'3015','Small Packets Air International',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (223,'3020','Air International',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (224,'3025','XPressPost International',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (225,'INT.TP','Tracked Packet - International',1,0)") or print_sql_error('');

	// ! ! ! ! These update the methods above ! ! ! ! 
	ect_query("UPDATE uspsmethods SET uspsMethod='DOM.EP',uspsShowAs='Expedited Parcel' WHERE uspsID=202");
	ect_query("UPDATE uspsmethods SET uspsMethod='DOM.XP',uspsShowAs='Xpresspost' WHERE uspsID=203");
	ect_query("UPDATE uspsmethods SET uspsMethod='DOM.XP.CERT',uspsShowAs='Xpresspost Certified' WHERE uspsID=204");
	ect_query("UPDATE uspsmethods SET uspsMethod='DOM.PC',uspsShowAs='Priority' WHERE uspsID=205");
	ect_query("UPDATE uspsmethods SET uspsMethod='DOM.LIB',uspsShowAs='Library Books' WHERE uspsID=206");
	ect_query("UPDATE uspsmethods SET uspsMethod='USA.EP',uspsShowAs='Expedited Parcel USA' WHERE uspsID=207");
	ect_query("UPDATE uspsmethods SET uspsMethod='USA.PW.ENV',uspsShowAs='Priority Worldwide Envelope USA' WHERE uspsID=208");
	ect_query("UPDATE uspsmethods SET uspsMethod='USA.PW.PAK',uspsShowAs='Priority Worldwide pak USA' WHERE uspsID=210");
	ect_query("UPDATE uspsmethods SET uspsMethod='USA.PW.PARCEL',uspsShowAs='Priority Worldwide Parcel USA' WHERE uspsID=211");
	ect_query("UPDATE uspsmethods SET uspsMethod='USA.SP.AIR',uspsShowAs='Small Packet USA Air' WHERE uspsID=212");
	ect_query("UPDATE uspsmethods SET uspsMethod='USA.SP.SURF',uspsShowAs='Small Packet USA Surface' WHERE uspsID=213");
	ect_query("UPDATE uspsmethods SET uspsMethod='USA.XP',uspsShowAs='Xpresspost USA' WHERE uspsID=214");
	ect_query("UPDATE uspsmethods SET uspsMethod='INT.XP',uspsShowAs='Xpresspost International' WHERE uspsID=215");
	ect_query("UPDATE uspsmethods SET uspsMethod='INT.IP.AIR',uspsShowAs='International Parcel Air' WHERE uspsID=216");
	ect_query("UPDATE uspsmethods SET uspsMethod='INT.IP.SURF',uspsShowAs='International Parcel Surface' WHERE uspsID=217");
	ect_query("UPDATE uspsmethods SET uspsMethod='INT.PW.ENV',uspsShowAs='Priority Worldwide Envelope Int\'l' WHERE uspsID=218");
	ect_query("UPDATE uspsmethods SET uspsMethod='INT.PW.PAK',uspsShowAs='Priority Worldwide pak Int\'l' WHERE uspsID=221");
	ect_query("UPDATE uspsmethods SET uspsMethod='INT.PW.PARCEL',uspsShowAs='Priority Worldwide parcel Int\'l' WHERE uspsID=222");
	ect_query("UPDATE uspsmethods SET uspsMethod='INT.SP.AIR',uspsShowAs='Small Packet International Air' WHERE uspsID=223");
	ect_query("UPDATE uspsmethods SET uspsMethod='INT.SP.SURF',uspsShowAs='Small Packet International Surface' WHERE uspsID=224");

	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (301,'PRIORITYOVERNIGHT','FedEx Priority Overnight&reg;',1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (302,'STANDARDOVERNIGHT','FedEx Standard Overnight&reg;',1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (303,'FIRSTOVERNIGHT','FedEx First Overnight&reg;',1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (304,'FEDEX2DAY','FedEx 2Day&reg;',1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (305,'FEDEXEXPRESSSAVER','FedEx Express Saver&reg;',1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (306,'INTERNATIONALPRIORITY','FedEx International Priority&reg;',1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (307,'INTERNATIONALECONOMY','FedEx International Economy&reg;',1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (308,'INTERNATIONALFIRST','FedEx International Next Flight&reg;',1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (310,'FEDEX1DAYFREIGHT','FedEx 1Day Freight&reg;',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (311,'FEDEX2DAYFREIGHT','FedEx 2Day Freight&reg;',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (312,'FEDEX3DAYFREIGHT','FedEx 3Day Freight&reg;',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal,uspsFSA) VALUES (313,'FEDEXGROUND','FedEx Ground&reg;',1,0,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (314,'GROUNDHOMEDELIVERY','FedEx Home Delivery&reg;',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (315,'INTERNATIONALPRIORITYFREIGHT','FedEx International Priority Freight&reg;',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (316,'INTERNATIONALECONOMYFREIGHT','FedEx International Economy Freight&reg;',1,0)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (317,'EUROPEFIRSTINTERNATIONALPRIORITY','FedEx Europe First&reg; - Int''l Priority',1,1)") or print_sql_error('');
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (401,'SMARTPOST','FedEx SmartPost&reg;',1,1)") or print_sql_error('');
	
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (501,'3','DHL Easy Shop',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (502,'4','DHL Jetline',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (503,'8','DHL Express Easy',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (504,'E','DHL Express 9:00',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (505,'F','DHL Freight Worldwide',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (506,'H','DHL Economy Select',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (507,'J','DHL Jumbo Box',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (508,'M','DHL Express 10:30',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (509,'P','DHL Express Worldwide',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (510,'Q','DHL Medical Express',0,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (511,'V','DHL Europack',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (512,'Y','DHL Express 12:00',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (513,'2','DHL Easy Shop',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (514,'5','DHL Sprintline',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (515,'6','DHL Secureline',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (516,'7','DHL Express Easy',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (517,'9','DHL Europack',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (518,'B','DHL Break Bulk Express',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (519,'C','DHL Medical Express',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (520,'D','DHL Express Worldwide',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (521,'G','DHL Domestic Economy Express',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (522,'I','DHL Break Bulk Economy',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (523,'K','DHL Express 9:00',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (524,'L','DHL Express 10:30',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (525,'N','DHL Domestic Express',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (526,'R','DHL Global Mail Business',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (527,'S','DHL Same Day',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (528,'T','DHL Express 12:00',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (529,'U','DHL Express Worldwide',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (530,'W','DHL Economy Select',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (531,'X','DHL Express Envelope',1,1)");
	
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal,uspsFSA) VALUES (601,'AUS_PARCEL_REGULAR','Parcel Post',1,1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (602,'AUS_PARCEL_REGULAR_SATCHEL_3KG','Parcel Post',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (603,'AUS_PARCEL_EXPRESS','Express Post',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (604,'AUS_PARCEL_EXPRESS_SATCHEL_3KG','Express Post',1,1)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (605,'INTL_SERVICE_ECI_PLATINUM','Express Courier International',1,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (606,'INTL_SERVICE_ECI_M','Express Courier International',1,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (607,'INTL_SERVICE_ECI_D','Express Courier International',1,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (608,'INTL_SERVICE_EPI','Express Post International',1,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (609,'INTL_SERVICE_PTI','Pack and Track International',1,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (610,'INTL_SERVICE_RPI','Registered Post International',1,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (611,'INTL_SERVICE_AIR_MAIL','Air Mail',1,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (612,'INTL_SERVICE_SEA_MAIL','Sea Mail',1,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (613,'INTL_SERVICE_EPI_B4','Express Post International',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (614,'INTL_SERVICE_RPI_DLE','Registered Post International',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (615,'INTL_SERVICE_RPI_B4','Registered Post International',0,0)");
	ect_query("INSERT INTO uspsmethods (uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal) VALUES (616,'INTL_SERVICE_EPI_C5','Express Post International',0,0)");
}
// Dumping zonecharges table
if(checktablecreated('zonecharges')){
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (1,1,0.2,0.3,0.4)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (2,1,0.5,0.5,0.6)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (3,1,1,0.9,1.0)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (4,1,1.5,1.3,1.4)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (5,1,2,1.5,1.6)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (6,1,5,2,2.1)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (7,1,-1,0.5,0.6)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (8,2,0.2,0.4,0.5)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (9,2,0.5,0.7,0.8)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (10,2,1,1.1,1.2)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (11,2,1.5,1.6,1.7)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (12,2,2,2,2.1)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (13,2,5,3,3.1)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (14,2,-1,0.7,0.8)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (15,3,-1.1,0.8,0.9)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (16,3,0.2,0.5,0.6)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (17,3,0.5,0.8,0.9)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (18,3,1,1.2,1.3)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (19,3,1.5,1.7,1.8)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (20,3,2,2.2,2.3)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (21,3,5,3.2,3.3)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (22,4,-1,1,1.1)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (23,4,1,1.5,1.6)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (24,4,2,2.8,2.9)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (25,4,3,3.8,3.9)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (26,4,4,4.8,4.9)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (27,101,-1,1,1.1)");
	ect_query("INSERT INTO zonecharges (zcID,zcZone,zcWeight,zcRate,zcRate2) VALUES (28,101,1,1,1.1)");
}
if(checktablecreated('countries')){
	ect_query("UPDATE countries SET countryName2=countryName,countryName3=countryName") or print_sql_error('');
}
if(checktablecreated('orderstatus')){
	ect_query("UPDATE orderstatus SET statPublic2=statPublic,statPublic3=statPublic") or print_sql_error('');
}
if(checktablecreated('payprovider')){
	ect_query("UPDATE payprovider SET payProvShow2=payProvShow,payProvShow3=payProvShow") or print_sql_error('');
}

if($haserrors)
	print('<font color="#FF0000"><b>Completed, but with errors !</b></font><br />');
else
	print('<font color="#FF0000"><b>Everything installed successfully !</b></font><br />');

}else{

?>
<form action="createdb.php" method="POST">
<input type="hidden" name="posted" value="1">
<table width="100%">
<tr><td align="center" width="100%">
<p>&nbsp;</p>
<p><?php print "When reporting support issues, please quote your PHP version number which is " . phpversion();?></p>
<?php
$sSQL="SELECT version() AS theversion";
$result = ect_query($sSQL) or ect_error();
$rs = ect_fetch_assoc($result);
print "<p>mySQL Version is " . $rs["theversion"] . "</p>";
ect_free_result($result);
?>
<p>&nbsp;</p>
<p>Please click below to start your installation.</p>
<p>&nbsp;</p>
<p>After performing the installation, please delete this file from your web.</p>
<p>&nbsp;</p>
<p>Normally it is recommended to use the MyISAM database engine, but if you prefer to change this, please specify</p>
<select name="databaseengine" size="1">
<option value="">Use MyISAM Engine (Recommended)</option>
<option value="innodb">Use InnoDB Engine</option>
</select>
<p>&nbsp;</p>
<input style="background:#399908; color:#fff;cursor:pointer;padding:5px 10px;-moz-border-radius:10px;-webkit-border-radius:10px" type="submit" value="Install Ecommerce Plus version <?php print $sVersion?>">
<p>&nbsp;</p>
<p>&nbsp;</p>
</td></tr>
</table>
</form>
<?php
}
?>
</div>
</body>
</html>