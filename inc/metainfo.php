<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
global $seocategoryurls,$alreadygotadmin,$detlinkspacechar,$prodid,$catid,$manid,$usemetalongdescription,$usecategoryname,$usepnamefordetaillinks,$seodetailurls,$pagetitle,$sectionname,$sectiondescription,$topsection,$productid,$productname,$productdescription;
$magicq=(get_magic_quotes_gpc()==1);
if(trim(@$prodid=='')) $prodid=str_replace(@$detlinkspacechar,' ',mi_unstripslashes((string)@$_GET['prod']));
if(trim(@$catid=='')) $catid=mi_unstripslashes(@$_GET['cat']);
if(trim(@$manid=='')) $manid=mi_unstripslashes(@$_GET['man']);
if(@$seocategoryurls){$usecategoryname=TRUE;$catid=str_replace(@$detlinkspacechar,' ',$catid);$manid=str_replace(@$detlinkspacechar,' ',$manid);}
if(@$seodetailurls)$usepnamefordetaillinks=TRUE;
$productid=$productname=$productdescription=$sectionname=$sectiondescription=$topsection=$pagetitle=$metadescription='';
$sntxt='sectionName';
$sutxt='sectionURL';
$sdtxt='sectionDescription';
$pntxt='pName';
$scnametxt='scName';
if(@$usemetalongdescription==TRUE) $pdtxt='pLongDescription'; else $pdtxt='pDescription';
$GLOBALS['canonicalnopage']='';
if(@$_GET['pg']!=''){
	$GLOBALS['canonicalnopage']='<link rel="canonical" href="' . @$_SERVER['PHP_SELF'];
	$canonqs='';
	$addsep='?';
	foreach(@$_GET as $objitem => $objvalue){
		if($objitem!='pg'){
			$canonqs.=$addsep . urlencode(strip_tags($objitem)) . '=' . urlencode(strip_tags($objvalue));
			$addsep='&';
		}
	}
	$GLOBALS['canonicalnopage'].=$canonqs . "\" />\r\n";
}
function mi_unstripslashes($slashedText){
	global $magicq;
	return($magicq?trim(stripslashes((string)$slashedText)):trim((string)$slashedText));
}
function mi_query($ectsql){
	return(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->query($ectsql):mysql_query($ectsql));
}
function mi_fetch_assoc($ectres){
	return(@$GLOBALS['ectdatabase']?$ectres->fetch_assoc():mysql_fetch_assoc($ectres));
}
function mi_error(){
	print(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->error:mysql_error());
}
function mi_free_result($ectres){
	@$GLOBALS['ectdatabase']?$ectres->free_result():mysql_free_result($ectres);
}
function mi_escape_string($estr){
	return(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->real_escape_string($estr):mysql_real_escape_string($estr));
}
if(function_exists('getadminsettings')){
	$alreadygotadmin=getadminsettings();
	$sntxt=getlangid('sectionName',256);
	$sutxt=getlangid('sectionURL',2048);
	$sdtxt=getlangid('sectionDescription',512);
	$pntxt=getlangid('pName',1);
	$scnametxt=getlangid('scName',131072);
	if(@$usemetalongdescription==TRUE) $pdtxt=getlangid('pLongDescription',4); else $pdtxt=getlangid('pDescription',2);
}
if(@$usecategoryname && $catid!=''){
	$sSQL='SELECT sectionID FROM sections WHERE '.(@$seocategoryurls?$sutxt."='".mi_escape_string($catid)."' OR (":'').$sntxt."='".mi_escape_string($catid)."'".(@$seocategoryurls?' AND '.$sutxt."='')":'');
	$result=mi_query($sSQL) or mi_error();
	if($rs=mi_fetch_assoc($result)){ $catname=$catid; $catid=$rs['sectionID']; }
	mi_free_result($result);
}
if(@$usecategoryname && $manid!=''){
	$sSQL='SELECT scID FROM searchcriteria WHERE '.$scnametxt."='".mi_escape_string($manid)."'";
	$result=mi_query($sSQL) or mi_error();
	if($rs=mi_fetch_assoc($result)){ $manname=$manid; $manid=$rs['scID']; }
	mi_free_result($result);
}
if($prodid!=''){
	$result=mi_query('SELECT pID,'.$pntxt.','.$pdtxt.','.$sntxt.",pTitle,pMetaDesc FROM products INNER JOIN sections ON products.pSection=sections.sectionID WHERE " . (@$usepnamefordetaillinks&&trim((string)@$_GET['prod'])!=''?$pntxt:'pID') . "='" . mi_escape_string($prodid) . "'" . (@$seodetailurls?" OR pStaticURL='".mi_escape_string($prodid)."'":'')) or mi_error();
	if($rs=mi_fetch_assoc($result)){
		$productid=str_replace('"','&quot;',strip_tags($rs['pID']));
		$productname=str_replace('"','&quot;',strip_tags($rs[$pntxt]));
		$productdescription=str_replace('"','&quot;',strip_tags($rs[$pdtxt]));
		$sectionname=str_replace('"','&quot;',strip_tags($rs[$sntxt]));
		$pagetitle=$rs['pTitle'];
		if(trim($rs['pMetaDesc'])!='')$productdescription=str_replace('"','&quot;',$rs['pMetaDesc']);
	}
	if($catid!='' && is_numeric($catid)){
		$result=mi_query('SELECT '.$sntxt." FROM sections WHERE sectionID=" . $catid) or mi_error();
		if($rs=mi_fetch_assoc($result)) $sectionname=str_replace('"','&quot;',strip_tags($rs[$sntxt]));
	}
}elseif($catid!='' && (is_numeric($catid) || @$usecategoryname)){
	$topsection=0;
	if(is_numeric($catid)) $sSQL="sectionID=".mi_escape_string($catid); else $sSQL="sectionName='".mi_escape_string($catid)."'";
	$result=mi_query('SELECT '.$sntxt.','.$sdtxt.",topSection,sTitle,sMetaDesc FROM sections WHERE " . $sSQL) or mi_error();
	if($rs=mi_fetch_assoc($result)){
		$sectionname=str_replace('"','&quot;',strip_tags($rs[$sntxt]));
		$sectiondescription=str_replace('"','&quot;',strip_tags($rs[$sdtxt]));
		$topsection=$rs['topSection'];
		$pagetitle=$rs['sTitle'];
		if(trim($rs['sMetaDesc'])!='')$sectiondescription=str_replace('"','&quot;',$rs['sMetaDesc']);
	}
	if($topsection!=0){
		$result=mi_query('SELECT '.$sntxt.' FROM sections WHERE sectionID=' . $topsection) or mi_error();
		if($rs=mi_fetch_assoc($result))
			$topsection=str_replace('"','&quot;',strip_tags($rs[$sntxt]));
	}else
		$topsection='';
}elseif($manid!='' && (is_numeric($manid) || @$usecategoryname)){
	$topsection='';
	if(function_exists('getadminsettings')){ $sdtxt=getlangid('scDescription',16384); $sntext=getlangid('scName',131072); }else{ $sdtxt='scDescription'; $sntext='scName'; }
	if(is_numeric($manid)) $sSQL="scID=".mi_escape_string($manid); else $sSQL=$sntext."='".mi_escape_string($manid)."'";
	$result=mi_query('SELECT '.$sntext.','.$sdtxt.' FROM searchcriteria WHERE ' . $sSQL) or mi_error();
	if($rs=mi_fetch_assoc($result)){
		$sectionname=str_replace('"','&quot;',strip_tags($rs[$sntext]));
		$sectiondescription=str_replace('"','&quot;',strip_tags($rs[$sdtxt]));
	}
}
?>