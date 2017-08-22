<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(!@$GLOBALS['incfunctionsdefined']){print 'No incfunctions.php file';exit;}
global $seocategoryurls,$seourlsthrow301,$seocaturlpattern,$seoprodurlpattern,$seomanufacturerpattern,$usecategoryname,$alreadygotadmin,$usecsslayout,$categorycolumns,$manufacturerpageurl,$usecategoryformat,$catseparator,$catalogroot;
$catname=$caturl=$catrootsection=''; $catrootsection=0;
$alreadygotadmin=getadminsettings();
if(getget('cat')!='') $theid=trim(getget('cat')); else $theid='';
if(@$seocategoryurls){$usecategoryname=TRUE;$theid=str_replace(@$detlinkspacechar,' ',$theid);}
if(is_numeric(@$explicitid))
	$theid=@$explicitid;
elseif(@$usecategoryname && $theid!=''){
	$sSQL='SELECT sectionID FROM sections WHERE '.(@$seocategoryurls?getlangid('sectionurl',2048)."='".escape_string($theid)."' OR (":'').getlangid('sectionName',256)."='".escape_string($theid)."'".(@$seocategoryurls?' AND '.getlangid('sectionurl',2048)."='')":'');

	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){ $catname=$theid; $theid=$rs['sectionID']; }
	ect_free_result($result);
}
if(@$seocategoryurls && $catname=='' && @$seourlsthrow301 && is_numeric($theid)){
	$sSQL='SELECT sectionID,'.getlangid('sectionName',256).','.getlangid('sectionName',256).','.getlangid('sectionurl',2048).',rootSection FROM sections WHERE sectionID='.$theid;


	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){ $catname=$rs[getlangid('sectionName',256)]; $theid=$rs['sectionID']; $caturl=trim($rs[getlangid('sectionurl',2048)]); $catrootsection=$rs['rootSection']; }
	ect_free_result($result);
}
if(@$manufacturerpageurl=='') $manufacturerpageurl='manufacturers.php';
if(strpos(strtolower(@$_SERVER['PHP_SELF']), strtolower($manufacturerpageurl))!==FALSE||getget('man')=='all') $manufacturers=TRUE; else $manufacturers=FALSE;
if(@$seocategoryurls && @$seourlsthrow301 && @$_SERVER['REDIRECT_URL']==''){
	if($caturl!='')
		$newloc=getfullurl(getcatid($caturl,$caturl,$catrootsection==1?($manufacturers==TRUE?$seomanufacturerpattern:$seoprodurlpattern):$seocaturlpattern));
	else
		$newloc=getfullurl(getcatid($theid,$catname,$manufacturers==TRUE?$seomanufacturerpattern:$seocaturlpattern));
	$addand=$newqs='';
	foreach($_GET as $key=>$val){
		if($key!='cat'){ $newqs.=$addand.$key.'='.urlencode($val); $addand='&'; }
	}
	ob_end_clean();
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: '.$newloc.($newqs!=''?'?'.$newqs:''));
	exit;
}
if(@$GLOBALS['bmlbannercategories']!='' && @$GLOBALS['paypalpublisherid']!='') displaybmlbanner($GLOBALS['paypalpublisherid'],$GLOBALS['bmlbannercategories']);
if(! is_numeric($theid)) $theid=$catalogroot;
if(! is_numeric(@$categorycolumns) || $categorycolumns=='') $categorycolumns=1;
$cellwidth=(int)(100/$categorycolumns);
if(@$usecsslayout){
	$usecategoryformat=1;
	$afterimage='';
	$beforedesc='';
}elseif(@$usecategoryformat==3){
	$afterimage='<br />';
	$beforedesc='';
}elseif(@$usecategoryformat==2){
	$afterimage='';
	$beforedesc='';
}else{
	$usecategoryformat=1;
	$afterimage='';
	$beforedesc='</td></tr><tr><td class="catdesc" colspan="2">';
}
$border=0;
if(! @isset($catseparator)) $catseparator=@$usecsslayout?'':'<br />&nbsp;';
$tslist='';
$thetopts=$theid;
$topsectionids=$theid;
$success=TRUE;
if(@$_SESSION['clientID']!='' && @$_SESSION['clientLoginLevel']!='') $minloglevel=$_SESSION['clientLoginLevel']; else $minloglevel=0;
$columncount=0;
if($manufacturers){
	$tslist='<a class="ectlink" href="'.$GLOBALS['xxHomeURL'].'">'.$GLOBALS['xxHome'].'</a> &raquo; ' . $GLOBALS['xxManuf'];
	$GLOBALS['xxAlProd']='';
}else{
	for($index=0; $index <= 10; $index++){
		if($thetopts==$catalogroot){	
			$caturl=$GLOBALS['xxHomeURL'];
			if($catalogroot!=0){
				$sSQL='SELECT sectionID,topSection,'.getlangid('sectionName',256).',rootSection,sectionDisabled,'.getlangid('sectionurl',2048)." FROM sections WHERE sectionID='" . $catalogroot . "'";
				$result=ect_query($sSQL) or ect_error();
				if($rs=ect_fetch_assoc($result)){
					$GLOBALS['xxHome']=$rs[getlangid('sectionName',256)];
					if(trim($rs[getlangid('sectionurl',2048)])!='') $caturl=$rs[getlangid('sectionurl',2048)];
				}
				ect_free_result($result);
			}
			if($theid==$catalogroot) $tslist=$GLOBALS['xxHome'].' '.$tslist; else $tslist='<a class="ectlink" href="'.$caturl.'">'.$GLOBALS['xxHome'].'</a> '.$tslist;
			break;
		}elseif($index==10){
			$tslist='<strong>Loop</strong>' . $tslist;
		}else{
			$sSQL='SELECT sectionID,topSection,'.getlangid('sectionName',256).',rootSection,sectionDisabled,'.getlangid('sectionurl',2048).' AS sectionurl FROM sections WHERE sectionID=' . $thetopts;
			$result2=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result2) > 0){
				$rs=ect_fetch_assoc($result2);
				if($rs['sectionDisabled'] > $minloglevel)
					$success=FALSE;
				elseif($rs['sectionID']==(int)$theid){
					$tslist=' &raquo; ' . $rs[getlangid('sectionName',256)] . $tslist;
					if(@$explicitid=='' && trim($rs['sectionurl'])!='' && @$redirecttostatic==TRUE){
						ob_end_clean();
						header('HTTP/1.1 301 Moved Permanently');
						header('Location: '.getfullurl($rs['sectionurl']));
						exit;
					}
				}elseif(trim($rs['sectionurl'])!='')
					$tslist=' &raquo; <a class="ectlink" href="' . getcatid($rs['sectionurl'],$rs['sectionurl'],$rs['rootSection']==1?$seoprodurlpattern:$seocaturlpattern) . '">' . $rs[getlangid('sectionName',256)] . '</a>' . $tslist;
				elseif($rs['rootSection']==1)
					$tslist=' &raquo; <a class="ectlink" href="' . (!@$seocategoryurls?'products.php?cat=':'') . getcatid($rs['sectionID'],$rs[getlangid('sectionName',256)],$seoprodurlpattern) . '">' . $rs[getlangid('sectionName',256)] . '</a>' . $tslist;
				else
					$tslist=' &raquo; <a class="ectlink" href="' . (!@$seocategoryurls?'categories.php?cat=':'') . getcatid($rs['sectionID'],$rs[getlangid('sectionName',256)],$seocaturlpattern) . '">' . $rs[getlangid('sectionName',256)] . '</a>' . $tslist;
				$thetopts=$rs['topSection'];
				$topsectionids.=',' . $thetopts;
			}else{
				$tslist='Top Section Deleted' . $tslist;
				break;
			}
			ect_free_result($result2);
		}
	}
}
if(@$GLOBALS['xxAlProd']!='') $tslist.=' &raquo; <a class="ectlink" href="' . (@$seocategoryurls?'':'products.php') . ($theid=='0'||$theid==$catalogroot ? (@$seocategoryurls?str_replace('%s','',$seoprodurlpattern):'') : (@$seocategoryurls?'':'?cat=') . getcatid($theid,$catname,$seoprodurlpattern)) . '">' . $GLOBALS['xxAlProd'] . '</a>';
if($manufacturers==TRUE){
	$showdiscounts=FALSE;
	$sSQL='SELECT scID AS sectionID,'.getlangid('scName',131072).' AS sectionName,'.getlangid('scDescription',16384).' AS sectionDescription,scLogo AS sectionImage,scOrder AS sectionOrder,1 AS rootSection,'.getlangid('scURL',8192).' AS sectionurl FROM searchcriteria WHERE scGroup=0 ORDER BY scOrder,' . getlangid('scName',131072);
}else
	$sSQL='SELECT sectionID,'.getlangid('sectionName',256).' AS sectionName,'.(@$nocategorydescription==TRUE?"''":getlangid('sectionDescription',512)).' AS sectionDescription,sectionImage,sectionOrder,rootSection,'.getlangid('sectionurl',2048).' AS sectionurl FROM sections WHERE topSection=' . $theid . ' AND sectionDisabled<=' . $minloglevel . ' ORDER BY ' . (@$sortcategoriesalphabetically==TRUE ? getlangid('sectionName',256) : 'sectionOrder');
$result=ect_query($sSQL) or ect_error();
if(!$success || ect_num_rows($result)==0){
	$success=FALSE;
	$mess1=$GLOBALS['xxNoCats'];
}else{
	$success=TRUE;
	if(@$GLOBALS['xxClkPrd']!='') $mess1=$GLOBALS['xxClkPrd'] . '<br />&nbsp;'; else $mess1='';
}
if($usecategoryformat==1 || $usecategoryformat==2) $numcolumns=2*$categorycolumns; else $numcolumns=$categorycolumns;
if(! @$usecsslayout) $headtable='<table width="100%" border="0" cellspacing="3" cellpadding="3">'; else $headtable='';
if($mess1!=''){
	print $headtable; $headtable='';
	if(! @$usecsslayout) print '<tr><td align="center"' . ($numcolumns>1 ? ' colspan="' . $numcolumns . '"' : '') . '>';
	print '<div class="categorymessage' . ($success?'':' categorynotavailable" style="margin:40px 0px 40px 0px') . '">' . (@$usecsslayout?'':'<strong>') . $mess1 . (@$usecsslayout?'':'</strong>') . '</div>';
	if(! @$usecsslayout) print '</td></tr>';
}
if(@$nowholesalediscounts==TRUE && @$_SESSION['clientUser']!='')
	if((($_SESSION['clientActions'] & 8)==8) || (($_SESSION['clientActions'] & 16)==16)) $noshowdiscounts=TRUE;
if($success){
	if(@$noshowdiscounts != TRUE){
		if($theid=='0')
			$sSQL='SELECT DISTINCT '.getlangid('cpnName',1024)." FROM coupons WHERE (cpnSitewide=1 OR cpnSitewide=2)";
		else
			$sSQL='SELECT DISTINCT '.getlangid('cpnName',1024)." FROM coupons LEFT OUTER JOIN cpnassign ON coupons.cpnID=cpnassign.cpaCpnID WHERE (((cpnSitewide=0 OR cpnSitewide=3) AND cpaType=1 AND cpaAssignment IN ('" . str_replace(',',"','",$topsectionids) . "')) OR cpnSitewide=1 OR cpnSitewide=2)";
		$sSQL.=" AND cpnNumAvail>0 AND cpnEndDate>='" . date('Y-m-d',time()) ."' AND cpnIsCoupon=0 AND ((cpnLoginLevel>=0 AND cpnLoginLevel<=".$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))';
		$result2=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result2) > 0){
			print $headtable; $headtable='';
			if(! @$usecsslayout) print '<tr><td align="left" class="allcatdiscounts"' . ($numcolumns>1 ? ' colspan=" ' . $numcolumns . '"' : '') . '>';
			print '<div class="discountsapply allcatdiscounts"' . (@$nomarkup ? '' : ' style="font-weight:bold;"') . '>' . $GLOBALS['xxDsCat'] . '</div><div class="catdiscounts allcatdiscounts"' . (@$nomarkup ? '' : ' style="font-size:9px;color:#FF0000;"') . '>';
			while($rs=ect_fetch_assoc($result2)){
				print $rs[getlangid('cpnName',1024)] . '<br />';
			}
			print '&nbsp;</div>';
			if(! @$usecsslayout) print '</td></tr>';
		}
		ect_free_result($result2);
	}
	if(! @$usecsslayout&&$headtable=='') print '</table>';
	if(! (@isset($showcategories) && @$showcategories==FALSE)){
		if(@$usecsslayout) print '<div class="prodnavigation catnavwrapper">'; else print '<table width="98%" border="0" cellspacing="3" cellpadding="3"><tr>';
		if(@$allproductsimage!='') print (@$usecsslayout ? '<div' : '<td width="5%" align="right"') . ' class="catimage"><a class="ectlink" href="' . (@$seocategoryurls?str_replace('%s','',$seoprodurlpattern):'products.php') . '"><img class="catimage" src="' . @$allproductsimage . '" border="0" alt="' . $GLOBALS['xxAlProd'] . '" /></a>' . $afterimage . (@$usecsslayout ? '</div>' : '</td>');
		if(@$usecsslayout) print '<div class="catnavigation">' . $tslist . '</div>'; else print '<td class="catnavigation"><p class="catnavigation"><strong>' . $tslist . '</strong></p>';
		if($GLOBALS['xxAlPrCa']!='' && ! $manufacturers) print (@$usecsslayout ? '<div' : '<p') . ' class="navdesc">' . $GLOBALS['xxAlPrCa'] . (@$usecsslayout ? '</div>' : '</p>');
		if(@$usecsslayout) print '</div>'; else print '</td></tr></table>';
	}
	if(@$usecsslayout) print '<div class="categories">'; else print '<table width="98%" border="0" cellspacing="' . ($usecategoryformat==1 && $categorycolumns>1 ? 0 : 3) . '" cellpadding="' . ($usecategoryformat==1 && $categorycolumns>1 ? 0 : 3) . '">';
	while($rs=ect_fetch_assoc($result)){
		if(trim($rs['sectionurl'])!='')
			$startlink='<a class="ectlink" href="' . getcatid($rs['sectionurl'],@$seocategoryurls?$rs['sectionurl']:'',$rs['rootSection']==1?($manufacturers==TRUE?$seomanufacturerpattern:$seoprodurlpattern):$seocaturlpattern) . '">';
		elseif($rs['rootSection']==0)
			$startlink='<a class="ectlink" href="' . (!@$seocategoryurls?'categories.php?cat=':'') . getcatid($rs['sectionID'],$rs['sectionName'],$seocaturlpattern) . '">';
		else
			$startlink='<a class="ectlink" href="' . (!@$seocategoryurls?'products.php?' . ($manufacturers==TRUE?'man=':'cat='):'') . getcatid($rs['sectionID'],$rs['sectionName'],$manufacturers==TRUE?$seomanufacturerpattern:$seoprodurlpattern) . '">';
		$sSQL='SELECT DISTINCT '.getlangid('cpnName',1024)." FROM coupons LEFT OUTER JOIN cpnassign ON coupons.cpnID=cpnassign.cpaCpnID WHERE (cpnSitewide=0 OR cpnSitewide=3) AND cpnNumAvail>0 AND cpnEndDate>='" . date('Y-m-d',time()) ."' AND cpnIsCoupon=0 AND cpaType=".($manufacturers?3:1)." AND cpaAssignment='" . $rs['sectionID'] . "'" .
			' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))';
		$alldiscounts='';
		if(@$noshowdiscounts != TRUE){
			$result2=ect_query($sSQL) or ect_error();
			while($rs2=ect_fetch_assoc($result2))
				$alldiscounts.=$rs2[getlangid('cpnName',1024)] . '<br />';
			ect_free_result($result2);
		}
		$secdesc=trim($rs['sectionDescription']);
		$noimage=(trim($rs['sectionImage'])=='');
		if(@$usecsslayout)
			print '<div class="category">';
		else{
			if($columncount==0) print '<tr>';
			if($usecategoryformat==1 && $categorycolumns>1) print '<td width="' . $cellwidth . '%" valign="top"><table width="100%" border="0" cellspacing="3" cellpadding="3"><tr>';
		}
		if(($usecategoryformat==1 || $usecategoryformat==2) && ! $noimage){
			$cellwidth -= 5;
			print (@$usecsslayout ? '<div' : '<td width="5%" align="right"') . ' class="catimage">' . $startlink . '<img alt="' . str_replace('"','',$rs['sectionName']) . '" class="catimage" src="' . $rs['sectionImage'] . '" border="0" /></a>' . $afterimage . (@$usecsslayout ? '</div>' : '</td>');
		}
		if(! @$usecsslayout) print '<td class="catname" width="' . ($usecategoryformat==1 && $categorycolumns>1 ? 95 : $cellwidth) . '%"' . (($usecategoryformat==1 || $usecategoryformat==2) && $noimage ? ' colspan="2"' : "") . '>';
		if(($usecategoryformat==1 || $usecategoryformat==2) && ! $noimage) $cellwidth+=5;
		if($usecategoryformat != 1 && $usecategoryformat != 2 && ! $noimage) print $startlink . '<img alt="' . str_replace('"','',$rs['sectionName']) . '" class="catimage" src="' . $rs['sectionImage'] . '" border="0" /></a>' . $afterimage;
		if(@$nocategoryname!=TRUE) print (@$usecsslayout ? '<div class="catname">' : '<p class="catname"><strong>') . $startlink . $rs['sectionName'] . '</a>' . $GLOBALS['xxDot'] . (! @$usecsslayout ? '</strong>' : '');
		if($alldiscounts!='') print ' <span class="eachcatdiscountsapply eachcatdiscount"' . (@$nomarkup ? '' : ' style="color:#FF0000;font-weight:bold;"') . '>' . $GLOBALS['xxDsApp'] . '</span>' . (@$nomarkup?'':'<font size="1">') . '<div class="catdiscounts eachcatdiscount">' . $alldiscounts . '</div>' . (@$nomarkup?'':'</font>');
		if($secdesc=='') print @$catseparator;
		if(@$nocategoryname!=TRUE) print (@$usecsslayout ? '</div>' : '</p>');
		if($secdesc!='') print (@$usecsslayout ? '<div' : $beforedesc . '<p') . ' class="catdesc">' . $secdesc . $catseparator . (@$usecsslayout ? '</div>' : '</p>');
		print (@$usecsslayout ? '</div>' : '</td>') . "\r\n";
		if($usecategoryformat==1 && $categorycolumns>1 AND ! @$usecsslayout) print '</tr></table></td>';
		$columncount++;
		if($columncount==$categorycolumns && ! @$usecsslayout){
			print '</tr>';
			$columncount=0;
		}
	}
	if($columncount<$categorycolumns && $columncount != 0 && ! @$usecsslayout){
		while($columncount<$categorycolumns){
			print '<td ' . ($usecategoryformat==2 ? ' colspan="2"' : '') . '>&nbsp;</td>';
			$columncount++;
		}
		print '</tr>';
	}
	if(@$usecsslayout) print "</div>";
}
if(! @$usecsslayout) print "</table>";
ect_free_result($result);
?>