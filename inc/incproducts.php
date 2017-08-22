<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(!@$GLOBALS['incfunctionsdefined']){print 'No incfunctions.php file';exit;}
global $seocategoryurls,$seourlsthrow301,$usecategoryname,$alreadygotadmin,$Count,$useproductbodyformat,$productcolumns,$pagebarattop,$catalogroot,$useStockManagement,$giftcertificateid,$donationid,$countryTaxRate,$prodfilter,$sstrong,$estrong,$defimagejs,$usecsslayout,$adminProdsPerPage,$currConvUser,$currConvPw,$currLastUpdate,$currRate1,$currSymbol1,$currRate2,$currSymbol2,$currRate3,$currSymbol3,$currSymbol1,$currFormat1,$currFormat2,$currFormat3,$usepnamefordetaillinks,$detlinkspacechar,$storeurl,$prodfilterorder,$optjs,$filterpricebands,$nosellzeroprice,$showquantonproduct,$magictoolboxproducts;
if(@$GLOBALS['xxDsNoAp']=='') $GLOBALS['xxDsNoAp']='The following discount(s) will not apply:';
$isproductspage=TRUE;$hasshippingdiscount=FALSE;
$catname=$caturl=$catrootsection=$globaldiscounttext='';
$catrootsection=1;
$iNumOfPages=$numscrid=$numscgroups=$maxglobaldiscounts=0;
if(! is_numeric(getget('pg')) || strlen(getget('pg'))>8) $CurPage=1; else $CurPage=max(1, (int)getget('pg'));
$alreadygotadmin=getadminsettings();
if(getget('cat')!='') $catid=getget('cat'); else $catid='';
if(getget('man')!='') $manid=getget('man'); else $manid='';
if(@$manufacturerpageurl=='') $manufacturerpageurl='manufacturers.php';
$scrid=commaseplist(getget('scri'));
$sprice=preg_replace('/[^\d\-\.]/','',getget('sprice'));
if($scrid=='' && is_numeric(@$explicitmanid)) $scrid=@$explicitmanid;
if($scrid!=''){ $scridarr=explode(',',$scrid); $numscrid=count($scridarr); }
if(is_numeric(@$explicitid)) $catid=@$explicitid;
if(@$explicitmanid!='' && is_numeric(@$explicitmanid)) $manid=@$explicitmanid;
if(is_numeric(@$_REQUEST['sortby'])) $_SESSION['sortby']=(int)$_REQUEST['sortby'];
if(@$_SESSION['sortby']!='') $dosortby=$_SESSION['sortby']; elseif(@$GLOBALS['orsortby']!='') $dosortby=$GLOBALS['orsortby'];
if(@$seocategoryurls){$usecategoryname=TRUE;$catid=str_replace(@$detlinkspacechar,' ',$catid);$manid=str_replace(@$detlinkspacechar,' ',$manid);}
if(@$GLOBALS['bmlbannerproducts']!='' && @$GLOBALS['paypalpublisherid']!='') displaybmlbanner($GLOBALS['paypalpublisherid'],$GLOBALS['bmlbannerproducts']);
if(@$usecategoryname && $catid!=''){
	$sSQL='SELECT sectionID FROM sections WHERE '.(@$seocategoryurls?getlangid('sectionurl',2048)."='".escape_string($catid)."' OR (":'').getlangid('sectionName',256)."='".escape_string($catid)."'".(@$seocategoryurls?' AND '.getlangid('sectionurl',2048)."='')":'');
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){ $catname=$catid; $catid=$rs['sectionID']; }
	ect_free_result($result);
}
if(@$usecategoryname && $manid!=''){
	$sSQL='SELECT scID FROM searchcriteria WHERE (('.getlangid('scURL',8192)."='' OR ".getlangid('scURL',8192)." IS NULL) AND ".getlangid('scName',131072)."='".escape_string($manid)."') OR ".getlangid('scURL',8192)."='".escape_string($manid)."' ORDER BY scGroup";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){ $manname=$manid; $manid=$rs['scID']; }
	ect_free_result($result);
}
if(@$seocategoryurls && $catname=='' && @$seourlsthrow301 && (is_numeric($catid) || is_numeric($manid))){
	if(is_numeric($catid))
		$sSQL='SELECT sectionID AS secid,'.getlangid('sectionName',256).' AS secname,'.getlangid('sectionurl',2048).' AS securl,rootSection FROM sections WHERE sectionID='.$catid;
	elseif(is_numeric($manid))
		$sSQL='SELECT scID AS secid,'.getlangid('scName',131072).' AS secname,'.getlangid('scURL',8192).' AS securl,1 AS rootSection FROM searchcriteria WHERE scID='.$manid;
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		if(is_numeric($catid)) $catid=$rs['secid']; else $manid=$rs['secid'];
		$catname=$rs['secname']; $caturl=trim($rs['securl']); $catrootsection=$rs['rootSection'];
	}
	ect_free_result($result);
}
if(!is_numeric($catid)) $catid=$catalogroot;
if(is_numeric($manid) || @$manufacturers==TRUE) $manufacturers=TRUE; else{ $manufacturers=FALSE; $manid=''; }
foreach(@$_GET as $key => $value) if($key=='man'&&is_numeric($value)) $manufacturers=TRUE;
$WSP=$OWSP='';
$TWSP='pPrice';
$sectionheader='';
if(@$seocategoryurls && @$seourlsthrow301 && @$_SERVER['REDIRECT_URL']==''){
	if($caturl!='' && ! is_numeric(@$explicitid))
		$newloc=getfullurl(getcatid($caturl,$caturl,$catrootsection==1?($manufacturers==TRUE?$seomanufacturerpattern:$seoprodurlpattern):$seocaturlpattern));
	else
		$newloc=getfullurl(getcatid($catid,$catname,$catrootsection==1?($manufacturers==TRUE?$seomanufacturerpattern:$seoprodurlpattern):$seocaturlpattern));
	$addand=$newqs='';
	foreach($_GET as $key=>$val){
		if($key!='cat'&&!($manufacturers&&$key=='man')){ $newqs.=$addand.$key.'='.urlencode($val); $addand='&'; }
	}
	ob_end_clean();
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: '.$newloc.($newqs!=''?'?'.$newqs:''));
	exit;
}
$sectionurl=htmlentities(strip_tags(@$seocategoryurls&&@$_SERVER['REQUEST_URI']!=''?current(explode('?',$_SERVER['REQUEST_URI'])):@$_SERVER['PHP_SELF']));
get_wholesaleprice_sql();
if(@$filterpricebands=='') $filterpricebands=100;
function isinscrid($cscrid){
	global $numscrid,$scridarr;
	$retval=FALSE;
	for($scrind=0; $scrind<$numscrid; $scrind++){
		if($cscrid==(int)$scridarr[$scrind]) $retval=TRUE;
	}
	return($retval);
}
function getlike($fie,$t,$tjn){
	global $sNOTSQL;
	if(substr($t, 0, 1)=='-'){ // pSKU excluded to work around NULL problems
		if($fie!='pSKU') $sNOTSQL.=$fie." LIKE '%".substr($t, 1)."%' OR ";
	}else
		return $fie . " LIKE '%".$t."%' ".$tjn;
}
function sortline($soid, $sotext){
	global $sortoptions,$dosortby;
	if(($sortoptions & pow(2,($soid-1)))!=0) print '<option value="'.$soid.'"'.($dosortby==$soid?' selected="selected"':'').'>'.$sotext.'</option>';
}
$nofirstpg=TRUE;
$pblink='<a class="ectlink" href="'.$sectionurl.'?';
foreach(@$_GET as $objQS => $objValue)
	if($objQS!='cat' && $objQS!='id' && $objQS!='man' && $objQS!='pg') $pblink.=urlencode($objQS) . '=' . urlencode($objValue) . '&amp;';
if(($catid!='0' || ($manufacturers && $manid!='')) && @$explicitid=='' && @$explicitmanid=='' && ! (@$seocategoryurls&&@$_SERVER['REDIRECT_URL']!='')) $pblink.=($manufacturers?'man=' . $_GET['man']:'cat=' . getcatid($catid,@$catname,$seoprodurlpattern)) . '&amp;pg='; else $pblink.='pg=';
if(@$magictoolboxproducts!=''){
	print '<script src="' . ($magictoolboxproducts=='MagicTouch' ? 'http://www.magictoolbox.com/mt/' . $magictouchid . '/magictouch.js' : strtolower($magictoolboxproducts) . '/' . strtolower($magictoolboxproducts) . '.js') . '" type="text/javascript"></script>' . @$magictooloptionsjsproducts;
	$magictoolboxproducts=str_replace('MagicZoomPlus','MagicZoom',$magictoolboxproducts);
	$magictool=$magictoolboxproducts;
}
$filterurl=$manfilterurl='';
foreach(@$_GET as $objQS=>$objVal){
	if($objQS!='filter' && $objQS!='pg' && $objQS!='sortby' && $objQS!='perpage' && ! (($objQS=='cat' || $objQS=='man') && @$seocategoryurls)){
		$filterurl.=urlencode($objQS) . '=' . urlencode(unstripslashes($objVal)) . '&';
		if($objQS!='sman' && $objQS!='scri' && $objQS!='sprice') $manfilterurl.=urlencode($objQS) . '=' . urlencode(unstripslashes($objVal)) . '&';
	}
}
if($filterurl=='') $filterurl=$sectionurl.'?filter='; else $filterurl=$sectionurl.'?'.$filterurl.'filter=';
if($manfilterurl=='') $manfilterurl=$sectionurl.'?'; else $manfilterurl=$sectionurl.'?'.$manfilterurl;
function dofilterresults($numfcols){
	global $prodfilter,$prodfiltertext,$sectionurl,$imgfilterproducts,$manufacturers,$manid,$scrid,$filterpricebands,$sortoptions,$sectionids,$prodsperpage,$numscrid,$scridarr,$filtersql,$filterurl,$manfilterurl,$sprice;
	global $sortoption1,$sortoption2,$sortoption3,$sortoption4,$sortoption5,$sortoption6,$sortoption7,$sortoption8,$sortoption9,$sortoption10,$sortoption11,$sortoption12,$countryTaxRate,$TWSP,$usecsslayout,$numscgroups;
	if($prodfilter!=0 && ! ($prodfilter==8 && $sortoptions==0)){
		if(($prodfilter & 2)==2){
			$searchcriterialist='';
			$currgroupid=-1;
			if(! @$GLOBALS['hascheckedectfilters']){
				$sSQL='SELECT COUNT(DISTINCT products.pID) as tcount,scID,'.getlangid('scName',131072).',scGroup,scOrder,scgTitle FROM (searchcriteria INNER JOIN searchcriteriagroup ON searchcriteria.scGroup=searchcriteriagroup.scgID) ' .
					'INNER JOIN multisearchcriteria ON multisearchcriteria.mSCscID=searchcriteria.scID INNER JOIN (products'.($sectionids!=''?' LEFT JOIN multisections ON products.pId=multisections.pId':'').') ON multisearchcriteria.mSCpID=products.pID ' .
					'WHERE pDisplay<>0';
				if($sectionids!='') $sSQL.=' AND (products.pSection IN ('.$sectionids.') OR multisections.pSection IN ('.$sectionids.'))';
				$sSQL.=$filtersql.' AND (multisearchcriteria.mSCpID IN (' . "\r\n" . 'SELECT products.pID FROM ';
				if($numscrid>1) $sSQL.=str_repeat('(',$numscrid-1);
				$sSQL.='(products'.($sectionids!=''?' LEFT JOIN multisections ON products.pId=multisections.pId':'').')' . ($scrid!=''?' INNER JOIN multisearchcriteria ON multisearchcriteria.mSCpID=products.pID':'');
				for($scrindex=1; $scrindex<=$numscrid-1; $scrindex++){
					$sSQL.=') INNER JOIN multisearchcriteria msc'.$scrindex.' ON products.pID=msc'.$scrindex.'.mSCpID';
				}
				$sSQL.=' WHERE 1=1';
				if($manid!='0' && $manid!='') $sSQL.=' AND pManufacturer=' . $manid;
				//if($sectionids!=''){ $sSQL.=' AND (products.pSection IN ('.$sectionids.') OR multisections.pSection IN ('.$sectionids.'))'; $whereand=' AND '; }
				if($scrid!=''){
					$sSQL.=' AND (multisearchcriteria.mSCscID='.$scridarr[0];
					for($scrindex=1; $scrindex<=$numscrid-1; $scrindex++){
						$sSQL.=' AND msc'.$scrindex.'.mSCscID='.$scridarr[$scrindex];
					}
					$sSQL.=')';
				}
				$sSQL.=')'."\r\n".($numscrid>0?' OR scID IN('.$scrid.')':'').') GROUP BY scID,'.getlangid('scName',131072).',scGroup,scOrder,scgOrder,scgTitle ORDER BY scgOrder,scGroup,scOrder,'.getlangid('scName',131072);
				$result2=ect_query($sSQL) or ect_error();
				$GLOBALS['nectfiltercache']=0;
				while($rs2=ect_fetch_assoc($result2))
					$GLOBALS['ectfiltercache'][$GLOBALS['nectfiltercache']++]=$rs2;
				ect_free_result($result2);
			}
			$GLOBALS['hascheckedectfilters']=TRUE;
			if($GLOBALS['nectfiltercache']>0){
				for($cacheindex=0; $cacheindex<$GLOBALS['nectfiltercache']; $cacheindex++){
					if($currgroupid!=$GLOBALS['ectfiltercache'][$cacheindex]['scGroup']){
						$numscgroups++;
						if($searchcriterialist!='') $searchcriterialist.='</select>';
						$searchcriterialist.='<select name="scri" class="prodfilter" id="scri'.$numscgroups.'" size="1" onchange="filterbyman(1)" style="min-width:130px"><option value="" style="font-weight:bold">== All ' . $GLOBALS['ectfiltercache'][$cacheindex]['scgTitle'] . " ==</option>\r\n";
						$currgroupid=$GLOBALS['ectfiltercache'][$cacheindex]['scGroup'];
					}
					$searchcriterialist.='<option value="'.$GLOBALS['ectfiltercache'][$cacheindex]['scID'].'"'.(isinscrid($GLOBALS['ectfiltercache'][$cacheindex]['scID'])?' selected="selected"':'').'>' . $GLOBALS['ectfiltercache'][$cacheindex][getlangid('scName',131072)] . (! isinscrid($GLOBALS['ectfiltercache'][$cacheindex]['scID'])?' ('.$GLOBALS['ectfiltercache'][$cacheindex]['tcount'].')':'') . "</option>\r\n";
				}
			}else
				$prodfilter-=2;
			if($searchcriterialist!='') $searchcriterialist.='</select>';
		}
		$maxprice=$minprice=0;
		if(($prodfilter & 4)==4){
			$sSQL='SELECT MAX(' . $TWSP . ') AS maxprice,MIN(' . $TWSP . ') AS minprice FROM products WHERE pDisplay<>0';
			if($sectionids!='') $sSQL='SELECT MAX(' . $TWSP . ') AS maxprice,MIN(' . $TWSP . ') AS minprice FROM (products LEFT JOIN multisections ON products.pId=multisections.pId) WHERE pDisplay<>0 AND (products.pSection IN (' . $sectionids . ') OR multisections.pSection IN (' . $sectionids . '))';
			$result2=ect_query($sSQL) or ect_error();
			if($rs2=ect_fetch_assoc($result2)){ if(! is_null($rs2['maxprice'])){ $maxprice=$rs2['maxprice']; $minprice=$rs2['minprice']; } }
			if(@$GLOBALS['showtaxinclusive']===2){ $maxprice+=$maxprice*($countryTaxRate/100.0); $minprice+=$minprice*($countryTaxRate/100.0); }
			ect_free_result($result2);
		}
		$filtertext=explode('&',$prodfiltertext);
		for($index=0; $index<9; $index++)
			$filtertext[$index]=str_replace('%26','&',@$filtertext[$index]);
		if(@$usecsslayout) print '<div class="prodfilterbar">'; else print '<tr class="prodfilterbar"><td class="prodfilterbar" colspan="' . $numfcols . '">';
?><script type="text/javascript">
/* <![CDATA[ */
function filterbyman(caller){
var furl="<?php print str_replace(array('<','"'),array('','\\"'),$manfilterurl)?>";
var allscri='';
if(document.getElementById('sman')){
	var smanobj=document.getElementById('sman');
	if(smanobj.selectedIndex!=0) furl+='sman='+smanobj[smanobj.selectedIndex].value+'&';
}
<?php	for($index=1; $index<=$numscgroups; $index++){ ?>
	var smanobj=document.getElementById('scri<?php print $index?>');
	if(smanobj.selectedIndex!=0) allscri+=smanobj[smanobj.selectedIndex].value+',';
<?php	} ?>
	if(allscri!='') furl+='scri='+allscri.substr(0,allscri.length-1)+'&';
if(document.getElementById('spriceobj')){
	var spriceobj=document.getElementById('spriceobj');
	if(spriceobj.selectedIndex!=0) furl+='sprice='+spriceobj[spriceobj.selectedIndex].value+'&';
}
if(document.getElementById('ectfilter')){
	if(document.getElementById('ectfilter').value!='')
		furl+='filter='+encodeURIComponent(document.getElementById('ectfilter').value)+'&';
}
document.location=furl.substr(0,furl.length-1);
}
function changelocation(fact,tobj){
document.location='<?php print $filterurl?>'.replace(/filter=/,fact+'='+tobj[tobj.selectedIndex].value<?php if(($prodfilter & 32)==32) print "+'&filter='+encodeURIComponent(document.getElementById('ectfilter').value)" ?>);
}
function changelocfiltertext(tkeycode,tobj){
if(tkeycode==13)document.location='<?php print $filterurl?>'+tobj.value;
}
/* ]]> */</script>
<?php	if(@$prodfilterorder=='') $prodfilterorder='1,2,4,8,16,32';
		if(! @$usecsslayout) print '<table class="prodfilterbar"><tr>';
		$filterorderarray=explode(',',$prodfilterorder);
		for($indexfilterorder=0; $indexfilterorder<count($filterorderarray); $indexfilterorder++){
			switch($filterorderarray[$indexfilterorder]){
			case 2:
			if(($prodfilter & 2)==2){ // Product Attributes
				if($filtertext[1]!='') print (@$usecsslayout ? '<div' : '<td align="right" style="white-space:nowrap"') . ' class="prodfilter filtertext ectpfatttext">' . $filtertext[1] . (@$usecsslayout ? '</div>' : '</td>');
				print (@$usecsslayout ? '<div' : '<td') . ' class="prodfilter ectpfatt">' . $searchcriterialist . (@$usecsslayout ? '</div>' : '</td>');
			}
			break;
			case 4:
			if(($prodfilter & 4)==4){ // Price bands
				if($filtertext[2]!='') print (@$usecsslayout ? '<div' : '<td align="right" style="white-space:nowrap"') . ' class="prodfilter filtertext ectpfpricetext">' . $filtertext[2] . (@$usecsslayout ? '</div>' : '</td>');
				$rowcounter=2;
				$currpriceband=getget('sprice');
				print (@$usecsslayout ? '<div' : '<td') . ' class="prodfilter ectpfprice">';
				?><select name="sprice" class="prodfilter" id="spriceobj" size="1" onchange="filterbyman(4)">
				<option value="0"><?php print $GLOBALS['xxPlsSel']?></option>
<?php			if($minprice==0 || $filterpricebands>=$minprice){ ?>
				<option value="1"<?php if($currpriceband=="1") print ' selected="selected"'?>><?php print $GLOBALS['xxFilUnd'].' '.FormatCurrencyZeroDP($filterpricebands)?></option>
<?php			}
				if(strpos($sprice,'-')!==FALSE && $paminprice!='' && $pamaxprice!='' && $filterpricebands>=$paminprice){ ?>
				<option value="<?php print $sprice?>" selected="selected"><?php print FormatCurrencyZeroDP($paminprice)." - ".FormatCurrencyZeroDP($pamaxprice)?></option>
<?php			}
				for($index=$filterpricebands; $index<$maxprice; $index+=$filterpricebands){
					if(strpos($sprice,'-')!==FALSE && $paminprice!='' && $pamaxprice!='' && $index<=$paminprice && ($index+$filterpricebands)>=$paminprice){ ?>
				<option value="<?php print $sprice?>" selected="selected"><?php print FormatCurrencyZeroDP($paminprice)." - ".FormatCurrencyZeroDP($pamaxprice)?></option>
<?php				}
					if($minprice==0 || ($index+$filterpricebands)>=$minprice){ ?>
				<option value="<?php print $rowcounter?>"<?php if($currpriceband==$rowcounter) print ' selected="selected"'?>><?php print FormatCurrencyZeroDP($index)." - ".FormatCurrencyZeroDP($index+$filterpricebands)?></option>
<?php				}
					$rowcounter++;
					if($rowcounter>1000) break;
				} ?>
			  </select><?php
				print (@$usecsslayout ? '</div>' : '</td>');
			}
			break;
			case 8:
			if(($prodfilter & 8)==8 && $sortoptions!=0){
				if($filtertext[3]!='') print (@$usecsslayout ? '<div' : '<td align="right" style="white-space:nowrap"') . ' class="prodfilter filtertext ectpfsorttext">' . $filtertext[3] . (@$usecsslayout ? '</div>' : '</td>');
				print (@$usecsslayout ? '<div' : '<td') . ' class="prodfilter ectpfsort">';
				?><select class="prodfilter" size="1" onchange="changelocation('sortby',this)">
				<option value="0"><?php print $GLOBALS['xxPlsSel']?></option>
<?php			sortline(1, @$sortoption1!=''?$sortoption1:'Sort Alphabetically');
				sortline(11, @$sortoption11!=''?$sortoption11:'Alphabetically (Desc.)');
				sortline(2, @$sortoption2!=''?$sortoption2:'Sort by Product ID');
				sortline(12, @$sortoption12!=''?$sortoption12:'Product ID (Desc.)');
				sortline(3, @$sortoption3!=''?$sortoption3:'Sort Price (Asc.)');
				sortline(4, @$sortoption4!=''?$sortoption4:'Sort Price (Desc.)');
				sortline(5, @$sortoption5!=''?$sortoption5:'Database Order');
				sortline(6, @$sortoption6!=''?$sortoption6:'Product Order');
				sortline(7, @$sortoption7!=''?$sortoption7:'Product Order (Desc.)');
				sortline(8, @$sortoption8!=''?$sortoption8:'Date Added (Asc.)');
				sortline(9, @$sortoption9!=''?$sortoption9:'Date Added (Desc.)');
				sortline(10, @$sortoption10!=''?$sortoption10:'Sort by Manufacturer');
?>			  </select><?php
				print (@$usecsslayout ? '</div>' : '</td>');
			}
			break;
			case 16:
			if(($prodfilter & 16)==16){
				if($filtertext[4]!='') print (@$usecsslayout ? '<div' : '<td align="right" style="white-space:nowrap"') . ' class="prodfilter filtertext ectpfpagetext">' . $filtertext[4] . (@$usecsslayout ? '</div>' : '</td>');
				print (@$usecsslayout ? '<div' : '<td') . ' class="prodfilter ectpfpage">';
				?><select class="prodfilter" size="1" onchange="changelocation('perpage',this)">
<?php			for($index=1; $index<=5; $index++){
					print '<option value="'.$index.'"'.(@$_SESSION['perpage']==$index?' selected="selected"':'').'>'.($prodsperpage*$index).' '.$GLOBALS['xxPerPag'].'</option>';
				}
?>		 	 </select><?php
				print (@$usecsslayout ? '</div>' : '</td>');
			}
			break;
			case 32:
			if(($prodfilter & 32)==32){
				if($filtertext[5]!='') print (@$usecsslayout ? '<div' : '<td align="right" style="white-space:nowrap"') . ' class="prodfilter filtertext ectpfkeywordtext">' . $filtertext[5] . (@$usecsslayout ? '</div>' : '</td>');
				print (@$usecsslayout ? '<div' : '<td style="white-space:nowrap"') . ' class="prodfilter ectpfkeyword">' ?><input onkeydown="changelocfiltertext(event.keyCode,this)" type="text" class="prodfilter" size="20" id="ectfilter" name="filter" value="<?php print htmlspecials(getget('filter'))?>" /><?php
				print imageorbutton(@$imgfilterproducts,$GLOBALS['xxGo'],"prodfilter","document.location='".str_replace('&','&amp;',$filterurl)."'+encodeURIComponent(document.getElementById('ectfilter').value)",TRUE);
				print ($usecsslayout ? '</div>' : '</td>');
			}
			}
		}
		if(@$usecsslayout) print '</div>'; else print '</tr></table></td></tr>';
	}
}
if(@$orprodsperpage!='') $adminProdsPerPage=$orprodsperpage;
$prodsperpage=$adminProdsPerPage;
checkCurrencyRates($currConvUser,$currConvPw,$currLastUpdate,$currRate1,$currSymbol1,$currRate2,$currSymbol2,$currRate3,$currSymbol3);
$tslist="";
$thetopts=$catid;
$topsectionids=$catid;
$isrootsection=FALSE;
$sectiondisabled=FALSE;
if(@$_SESSION['clientID']!='' && @$_SESSION['clientLoginLevel']!='') $minloglevel=$_SESSION['clientLoginLevel']; else $minloglevel=0;
if($manufacturers){
	$sSQL='SELECT '.getlangid('scName',131072).','.getlangid('scHeader',524288).' FROM searchcriteria WHERE scID=' . $manid;
	$result2=ect_query($sSQL) or ect_error();
	if($rs2=ect_fetch_assoc($result2)){ $mfname=$rs2[getlangid('scName',131072)]; $sectionheader=$rs2[getlangid('scHeader',524288)]; }else $mfname='Not Found';
	ect_free_result($result2);
	$tslist='<a class="ectlink" href="'.$GLOBALS['xxHomeURL'].'">'.$GLOBALS['xxHome'].'</a> &raquo; <a class="ectlink" href="'.$manufacturerpageurl.'">'.$GLOBALS['xxManuf'].'</a> &raquo; ' . $mfname;
	if(@$explicitmanid!='') $sectionurl=htmlentities(@$_SERVER['PHP_SELF']);
	$isrootsection=TRUE;
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
			$tslist='<a class="ectlink" href="'.$caturl.'">' . $GLOBALS['xxHome'] . '</a> ' . $tslist;
			break;
		}elseif($index==10){
			$tslist='<strong>Loop</strong>' . $tslist;
		}else{
			$sSQL='SELECT sectionID,topSection,'.getlangid('sectionName',256).',rootSection,sectionDisabled,'.getlangid('sectionurl',2048).'  AS sectionurl,'.getlangid('sectionHeader',524288).' FROM sections WHERE sectionID=' . $thetopts;
			$result2=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result2) > 0){
				$rs2=ect_fetch_assoc($result2);
				if($rs2['sectionID']==(int)$catid){ $isrootsection=($rs2['rootSection']==1); $sectionheader=$rs2[getlangid('sectionHeader',524288)]; }
				if($rs2['sectionDisabled']>$minloglevel) $catid=-1;
				if($rs2['sectionID']==(int)$catid && $isrootsection){
					$tslist=' &raquo; ' . $rs2[getlangid('sectionName',256)] . $tslist;
					if(@$explicitid!='' && trim($rs2['sectionurl'])!='') $sectionurl=trim($rs2['sectionurl']);
					if(@$explicitid=='' && trim($rs2['sectionurl'])!='' && @$redirecttostatic==TRUE){
						ob_end_clean();
						header('HTTP/1.1 301 Moved Permanently');
						if($rs2['sectionurl']{0}=='/')$thelocation='http://'.$_SERVER['HTTP_HOST'].$rs2['sectionurl'];elseif(substr(strtolower($rs2['sectionurl']),0,7)=='http://')$thelocation=$rs2['sectionurl'];else $thelocation='http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'],'/')).'/'.$rs2['sectionurl'];
						header('Location: '.$thelocation);
						exit;
					}
				}elseif(trim($rs2['sectionurl'])!=''){
					$tslist=' &raquo; <a class="ectlink" href="' . getcatid($rs2['sectionurl'],$rs2['sectionurl'],$rs2['rootSection']==1?$seoprodurlpattern:$seocaturlpattern) . '">' . $rs2[getlangid('sectionName',256)] . '</a>' . $tslist;
					if(@$explicitid!='' && $rs2['sectionID']==(int)$catid) $sectionurl=trim($rs2['sectionurl']);
				}elseif($rs2['rootSection']==1)
					$tslist=' &raquo; <a class="ectlink" href="' . (!@$seocategoryurls?'products.php?cat=':'') . getcatid($rs2['sectionID'],$rs2[getlangid('sectionName',256)],$seoprodurlpattern) . '">' . $rs2[getlangid('sectionName',256)] . '</a>' . $tslist;
				else
					$tslist=' &raquo; <a class="ectlink" href="' . (!@$seocategoryurls?'categories.php?cat=':'') . getcatid($rs2['sectionID'],$rs2[getlangid('sectionName',256)],$seocaturlpattern) . '">' . $rs2[getlangid('sectionName',256)] . '</a>' . $tslist;
				$thetopts=$rs2['topSection'];
				$topsectionids.=',' . $thetopts;
			}else{
				$tslist='Top Section Deleted ' . $tslist;
				break;
			}
			ect_free_result($result2);
		}
	}
}
if(! $isrootsection && @$GLOBALS['xxAlProd']!='') $tslist.=' &raquo; ' . $GLOBALS['xxAlProd'];
$filtersql='';
if(getget('filter')!=''){
	$Xstext=escape_string(substr(getget('filter'), 0, 1024));
	$aText=explode(' ',$Xstext);
	$aFields[0]='products.pId';
	$aFields[1]=getlangid('pName',1);
	$aFields[2]=getlangid('pDescription',2);
	$aFields[3]=getlangid('pLongDescription',4);
	$aFields[4]='pSKU';
	$aFields[5]='pSearchParams';
	$sNOTSQL=$sYESSQL='';
	foreach($aText as $theopt){
		$tmpSQL='';
		$arrelms=count($aText);
		for($index=0;$index<=5;$index++){
			if(is_array($theopt))$theopt=$theopt[0];
			if(! ((@$nosearchdescription==TRUE && $index==2) || (@$nosearchlongdescription==TRUE && $index==3) || (@$nosearchsku==TRUE && $index==4) || (@$nosearchparams==TRUE && $index==5)))
				$tmpSQL.=getlike($aFields[$index], $theopt, 'OR ');
		}
		if($tmpSQL!='') $sYESSQL.= '(' . substr($tmpSQL, 0, strlen($tmpSQL)-3) . ') ';
		if($tmpSQL!='') $sYESSQL.='AND ';
	}
	$sYESSQL=substr($sYESSQL, 0, -4);
	if($sYESSQL!='') $filtersql=' AND (' . $sYESSQL . ') ';
	if($sNOTSQL!='') $filtersql.=' AND NOT (' . substr($sNOTSQL, 0, strlen($sNOTSQL)-4) . ')';
}
$paminprice=$pamaxprice='';
if($sprice!=''){
	$taxlevel=1;
	if(@$GLOBALS['showtaxinclusive']===2) $taxlevel+=($countryTaxRate/100.0);
	if(strpos($sprice,'-')!==FALSE){
		$spricearr=explode('-',$sprice);
		$paminprice=$spricearr[0];
		$pamaxprice=$spricearr[1];
		if(is_numeric($pamaxprice)){
			if(! is_numeric($paminprice)) $paminprice=0;
		}
	}elseif(is_numeric($sprice)){
		$priceband=(int)$sprice;
		$paminprice=($priceband-1)*$filterpricebands;
		$pamaxprice=$priceband*$filterpricebands;
	}
	if($paminprice!=='' && $pamaxprice!='') $filtersql.=' AND ((' . $TWSP . '*'.$taxlevel.')>=' . $paminprice . ' AND (' . $TWSP . '*'.$taxlevel.')<=' . $pamaxprice . ')';
}
if(($prodfilter & 1)==1 && ! $manufacturers){
	$manid=getget('sman');
	if(! is_numeric($manid)) $manid="";
}
$sectionids='';
$result2=ect_query('SELECT sectionID FROM sections WHERE sectionDisabled>'.$minloglevel.' LIMIT 0,1') or ect_error();
if($rs2=ect_fetch_assoc($result2)) $disabledsections=TRUE; else $disabledsections=FALSE;
ect_free_result($result2);
if($catid==$catalogroot){
	$sSQL='SELECT SQL_CALC_FOUND_ROWS products.pId FROM ';
	if($numscrid>1) $sSQL.=str_repeat('(',$numscrid-1);
	$sSQL.='products' . ($disabledsections?' INNER JOIN sections ON products.pSection=sections.sectionID':'') . ($scrid!=''?' INNER JOIN multisearchcriteria ON multisearchcriteria.mSCpID=products.pID':'');
	for($scrindex=1; $scrindex<=$numscrid-1; $scrindex++){
		$sSQL.=') INNER JOIN multisearchcriteria msc'.$scrindex.' ON products.pID=msc'.$scrindex.'.mSCpID';
	}
	$sSQL.=' WHERE' . ($disabledsections?' sectionDisabled<='.$minloglevel.' AND':'') . ' pDisplay<>0'.$filtersql;
}else{
	$sectionids=getsectionids($catid, FALSE);
	$sSQL='SELECT SQL_CALC_FOUND_ROWS DISTINCT products.pId FROM ';
	if($numscrid>1) $sSQL.=str_repeat('(',$numscrid-1);
	$sSQL.='(products LEFT JOIN multisections ON products.pId=multisections.pId)' . ($disabledsections?' INNER JOIN sections ON products.pSection=sections.sectionID':'') . ($scrid!=''?' INNER JOIN multisearchcriteria ON multisearchcriteria.mSCpID=products.pID':'');
	for($scrindex=1; $scrindex<=$numscrid-1; $scrindex++){
		$sSQL.=') INNER JOIN multisearchcriteria msc'.$scrindex.' ON products.pID=msc'.$scrindex.'.mSCpID';
	}
	$sSQL.=' WHERE' . ($disabledsections?' sectionDisabled<='.$minloglevel.' AND':'') . ' pDisplay<>0'.$filtersql.' AND (products.pSection IN (' . $sectionids . ') OR multisections.pSection IN (' . $sectionids . '))';
}
if($manid!='0' && $manid!='') $sSQL.=' AND pManufacturer=' . $manid;
if($scrid!=''){
	$sSQL.=' AND (multisearchcriteria.mSCscID=' . $scridarr[0];
	for($scrindex=1; $scrindex<=$numscrid-1; $scrindex++){
		$sSQL.=' AND msc'.$scrindex.'.mSCscID='.$scridarr[$scrindex];
	}
	$sSQL.=')';
}
if($scrid!=''||$sprice!=''||getget('filter')!='')$GLOBALS['xxNoPrds']=$GLOBALS['xxNoMatc'].'<div class="resetfilters">'.imageorbutton(@$resetfilters,$GLOBALS['xxResFil'],'resetfilters',$manfilterurl,FALSE).'</div>';
if($useStockManagement && @$noshowoutofstock==TRUE) $sSQL.=' AND (pInStock>0 OR pStockByOpts<>0)';
if(@$_REQUEST['perpage']!='' && is_numeric(@$_REQUEST['perpage'])) $_SESSION['perpage']=(int)@$_REQUEST['perpage'];
if(@$_SESSION['perpage']!='' && is_numeric(@$_SESSION['perpage'])) $adminProdsPerPage=(int)$_SESSION['perpage']*$prodsperpage;
if($adminProdsPerPage>1000) $adminProdsPerPage=$prodsperpage;
if(@$dosortby==2 || @$dosortby==12)
	$sSortBy=' ORDER BY products.pId'.(@$dosortby==12?' DESC':'');
elseif(@$dosortby==3||@$dosortby==4)
	$sSortBy=' ORDER BY '.$TWSP.(@$dosortby==4?' DESC,pId':',pId');
elseif(@$dosortby==5)
	$sSortBy='';
elseif(@$dosortby==6||@$dosortby==7)
	$sSortBy=' ORDER BY pOrder'.(@$dosortby==7?' DESC,pId':',pId');
elseif(@$dosortby==8||@$dosortby==9)
	$sSortBy=' ORDER BY pDateAdded'.(@$dosortby==9?' DESC,pId':',pId');
elseif(@$dosortby==10)
	$sSortBy=' ORDER BY pManufacturer,pID';
else
	$sSortBy=' ORDER BY '.getlangid('pName',1).(@$dosortby==11?' DESC,pId':',pId');
if(strpos($sSQL,'DISTINCT'))
	$tmpSQL=preg_replace('/DISTINCT products.pId/','COUNT(DISTINCT products.pId) AS bar',$sSQL, 1);
else
	$tmpSQL=preg_replace('/products.pId/','COUNT(*) AS bar',$sSQL, 1);
$sSQL.= $sSortBy . ' LIMIT ' . ($adminProdsPerPage*($CurPage-1)) . ', '.$adminProdsPerPage;
$allprods=ect_query($sSQL) or ect_error();
$foundrows=ect_query('SELECT FOUND_ROWS() AS bar') or ect_error();
$rs=ect_fetch_assoc($foundrows);
$iNumOfPages=ceil($rs['bar']/$adminProdsPerPage);
ect_free_result($foundrows);		
if(ect_num_rows($allprods) > 0){
	$prodlist='';
	$addcomma='';
	while($rs=ect_fetch_assoc($allprods)){
		$prodlist.=$addcomma . "'" . $rs['pId'] . "'";
		$addcomma=',';
	}
	ect_free_result($allprods);
	$wantmanufacturer=(@$manufacturerfield!='' || (@$useproductbodyformat==3 && strpos(@$cpdcolumns, 'manufacturer')!==FALSE));
	$sSQL='SELECT pId,pSKU,'.getlangid('pName',1).','.$WSP.'pPrice,pListPrice,pSection,pSell,pStockByOpts,pStaticPage,pStaticURL,pInStock,pExemptions,pTax,pTotRating,pNumRatings,pBackOrder,pCustom1,pCustom2,pCustom3,pDateAdded,'.($wantmanufacturer?getlangid("scName",131072).',':'').(@$shortdescriptionlimit===0?"'' AS ":'').getlangid('pDescription',2).','.getlangid('pLongDescription',4).' FROM products '.($wantmanufacturer?'LEFT OUTER JOIN searchcriteria on products.pManufacturer=searchcriteria.scID ':'').'WHERE pId IN (' . $prodlist . ')' . $sSortBy;
	$allprods=ect_query($sSQL) or ect_error();
}
$Count=0;
if(@$nowholesalediscounts==TRUE && @$_SESSION['clientUser']!='')
	if((($_SESSION['clientActions'] & 8)==8) || (($_SESSION['clientActions'] & 16)==16)) $noshowdiscounts=TRUE;
if(@$noshowdiscounts!=TRUE){
	$sSQL='SELECT DISTINCT cpnID,'.getlangid('cpnName',1024).',cpnType,cpnSitewide,cpaType FROM coupons LEFT OUTER JOIN cpnassign ON coupons.cpnID=cpnassign.cpaCpnID WHERE (';
	$addor='';
	if($catid!='0'||$manufacturers){
		$sSQL.=$addor . '((cpnSitewide=0 OR cpnSitewide=3) AND cpaType='.($manufacturers?3:1)." AND cpaAssignment IN ('" . ($manufacturers?$manid:str_replace(",","','",$topsectionids)) . "'))";
		$addor=' OR ';
	}
	$sSQL.=$addor . "(cpnSitewide=1 OR cpnSitewide=2)) AND cpnNumAvail>0 AND cpnEndDate>='" . date('Y-m-d',time()) ."' AND cpnIsCoupon=0 AND ((cpnLoginLevel>=0 AND cpnLoginLevel<=".$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.')) ORDER BY '.getlangid('cpnName',1024);
	$result2=ect_query($sSQL) or ect_error();
	if(ect_num_rows($result2) > 0){
		$lastcouponname='';
		while($rs2=ect_fetch_assoc($result2)){
			if($rs2[getlangid('cpnName',1024)]!=$lastcouponname) $globaldiscounttext.='<div class="adiscount">' . $lastcouponname=$rs2[getlangid('cpnName',1024)] . '</div>';
			if($rs2['cpnType']==0) $hasshippingdiscount=TRUE;
			if($catid!='0'||$manid!=''){
				if(($rs2['cpnSitewide']==0 || $rs2['cpnSitewide']==3) && ($rs2['cpaType']==1||$rs2['cpaType']==3)){
					$globaldiscounts[$maxglobaldiscounts][0]=$rs2['cpnID'];
					$globaldiscounts[$maxglobaldiscounts][1]=$rs2[getlangid('cpnName',1024)];
					$globaldiscounts[$maxglobaldiscounts][2]='xxx';
					$maxglobaldiscounts++;
				}
			}
		}
	}
	ect_free_result($result2);
}

		if(! @$usecsslayout){
?>
	<table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
		<tr> 
			<td colspan="3" width="100%">
<?php	}else
			print '<div>';
		if($sectionheader) print '<div class="catheader">' . $sectionheader . '</div>';
if(@$useproductbodyformat==3)
	include './vsadmin/inc/incproductbody3.php';
elseif(@$useproductbodyformat==2)
	include './vsadmin/inc/incproductbody2.php';
else
	include './vsadmin/inc/incproductbody.php';
ect_free_result($allprods);
		if(! @$usecsslayout){ ?>
			</td>
		</tr>
	</table>
<?php	}else
			print '</div>';
?>