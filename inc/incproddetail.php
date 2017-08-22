<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(!@$GLOBALS['incfunctionsdefined']){print 'No incfunctions.php file';exit;}
global $seocategoryurls,$seourlsthrow301,$seocaturlpattern,$seoprodurlpattern,$alreadygotadmin,$thesessionid,$detlinkspacechar,$usecsslayout,$detailpagelayout,$dateformatstr,$imgcheckoutbutton,$numcustomerratings,$wishlistonproducts,$enablewishlists,$wishlistondetail,$overridecurrency,$orcdecimals,$orcthousands,$usepnamefordetaillinks,$redirecttostatic;
if(trim(@$explicitid)!='') $prodid=trim($explicitid); else $prodid=str_replace(@$detlinkspacechar,' ',getget('prod'));
$message = getget('message');
if($message){

	echo '<script type="text/javascript">alert("Product Added to Homepage");</script>';
}
$WSP=$OWSP=$tslist=$thecatid=$optionshtml=$previousid=$nextid='';
$TWSP='pPrice';
if(@$dateformatstr=='') $dateformatstr='m/d/Y';
get_wholesaleprice_sql();
$Count=0;
$hasmultipurchase=FALSE;
$hascustomlayout=FALSE;
if(@$detailpagelayout=='' || ! @$usecsslayout) $detailpagelayout='productimage,productid,manufacturer,sku,productname,discounts,instock,description,listprice,price,currency,options,addtocart,previousnext,emailfriend'.(@$GLOBALS['showsearchwords']?',searchwords':''); else $hascustomlayout=TRUE;
$customlayoutarray=explode(',',strtolower(str_replace(' ','',$detailpagelayout)));
if(@$imgcheckoutbutton=='') $imgcheckoutbutton='images/checkout.gif';
if(@$numcustomerratings=='') $numcustomerratings=6;
$reviewsshown=FALSE;
if(@$wishlistonproducts==TRUE) $wishlistondetail=TRUE;
if(@$_SESSION['clientID']=='' || @$enablewishlists==FALSE || @$wishlistondetail=='') $wishlistondetail=FALSE;
if(@$overridecurrency!=TRUE || @$orcdecimals=='') $orcdecimals='.';
if(@$overridecurrency!=TRUE || @$orcthousands=='') $orcthousands=',';
if(@$_SESSION['clientID']!='' && @$_SESSION['clientLoginLevel']!='') $minloglevel=$_SESSION['clientLoginLevel']; else $minloglevel=0;
if(@$GLOBALS['seodetailurls'])$GLOBALS['usepnamefordetaillinks']=TRUE;
if(@$GLOBALS['bmlbannerdetails']!='' && @$GLOBALS['paypalpublisherid']!='') displaybmlbanner($GLOBALS['paypalpublisherid'],$GLOBALS['bmlbannerdetails']);






function displaytabs($thedesc){
	global $ecttabs,$ecttabsspecials,$reviewsshown,$prodid,$languageid,$enablecustomerratings,$relatedtabtemplate,$shortdescriptionlimit,$nostripshortdescription,$relatedproductsbothways,$defaultdescriptiontab,$thetax,$ratingslanguages,$WSP,$usecsslayout,$usepnamefordetaillinks,$detlinkspacechar,$seodetailurls,$nocatid,$sSortBy,$isdesc;
	$hasdesctab=(strpos($thedesc, '<ecttab')!==FALSE);
	$hasdesschema=TRUE;
	if($hasdesctab || @$ecttabsspecials!='' || @$ecttabs!='' || @$defaultdescriptiontab!=''){
		if(@$defaultdescriptiontab=='')
			$defaultdescriptiontab='<ecttab title="'.$GLOBALS['xxDescr'].'" special="ectdescription">';
		elseif(strpos($defaultdescriptiontab,' special="ectdescription"')===FALSE)
			$defaultdescriptiontab=preg_replace('/>/',' special="ectdescription">',$defaultdescriptiontab,1);
		if(! $hasdesctab && $thedesc!='')
			$thedesc=$defaultdescriptiontab . $thedesc;
		elseif(strpos($thedesc,' itemprop="description"')===FALSE)
			$hasdesschema=FALSE;
		if(strpos(@$ecttabsspecials, '%tabs%')!==FALSE) $thedesc=str_replace('%tabs%', $thedesc, $ecttabsspecials); else $thedesc.=@$ecttabsspecials;
		if($ecttabs=='slidingpanel'){
			$displaytabs='<div class="slidingTabPanelWrapper"><ul class="slidingTabPanel">';
			$tabcontent='<div id="slidingPanel"><div'.($hasdesschema?'':' itemprop="description"').'>';
		}else{
			$displaytabs='<div class="TabbedPanels" id="TabbedPanels1"><ul class="TabbedPanelsTabGroup">';
			$tabcontent='<div class="TabbedPanelsContentGroup"'.($hasdesschema?'':' itemprop="description"').'>';
		}
		$dind=strpos($thedesc, '<ecttab');
		$tabindex=1;
		while($dind!==FALSE){
			$dind+=8;
			$dind2=strpos($thedesc, '>', $dind);
			if($dind2!==FALSE){
				$dtitle=''; $dimage=''; $dimageov=''; $dspecial='';
				$tproperties=substr($thedesc,$dind,$dind2-$dind);
				$pind=strpos($tproperties, 'title=');
				if($pind!==FALSE){
					$pind=strpos($tproperties, '"', $pind)+1;
					$pind2=strpos($tproperties, '"', $pind+1);
					$dtitle=substr($tproperties,$pind,$pind2-$pind);
				}
				$pind=strpos($tproperties, 'img=');
				if($pind!==FALSE){
					$pind=strpos($tproperties, '"', $pind)+1;
					$pind2=strpos($tproperties, '"', $pind+1);
					$dimage=substr($tproperties,$pind,$pind2-$pind);
				}
				$pind=strpos($tproperties, 'imgov=');
				if($pind!==FALSE){
					$pind=strpos($tproperties, '"', $pind)+1;
					$pind2=strpos($tproperties, '"', $pind+1);
					$dimageov=substr($tproperties,$pind,$pind2-$pind);
				}
				$pind=strpos($tproperties, 'special=');
				if($pind!==FALSE){
					$pind=strpos($tproperties, '"', $pind)+1;
					$pind2=strpos($tproperties, '"', $pind+1);
					$dspecial=substr($tproperties,$pind,$pind2-$pind);
				}
				$dind2++;
				$dind=strpos($thedesc, '<ecttab', $dind2);
				if($dind===FALSE) $dcontent=substr($thedesc,$dind2); else $dcontent=substr($thedesc,$dind2,$dind-$dind2);
				$hascontent=TRUE;
				$isdescriptiontab=FALSE;
				if($dspecial=='reviews'){
					if(@$enablecustomerratings){
						$sSQL="SELECT rtID,rtRating,rtPosterName,rtHeader,rtDate,rtComments FROM ratings WHERE rtApproved<>0 AND rtProdID='".escape_string($prodid)."'";
						if(@$ratingslanguages!='') $sSQL.=' AND rtLanguage+1 IN ('.$ratingslanguages.')'; elseif(@$languageid!='') $sSQL.=' AND rtLanguage='.((int)$languageid-1); else $sSQL.=' AND rtLanguage=0';
						$sSQL.=' ORDER BY rtDate DESC,rtRating DESC';
						$dcontent=(@$usecsslayout ? '<div class="reviewtab">' : '<table border="0" cellspacing="0" cellpadding="0" width="100%">') . showreviews($sSQL,FALSE) . (@$usecsslayout ? '</div>' : '</table>');
						$reviewsshown=TRUE;
					}else
						$hascontent=FALSE;
				}elseif($dspecial=='quantitypricing'){
					$quantpri=pddquantitypricing();
					if($quantpri!='') $dcontent.=$quantpri; else $hascontent=FALSE;
				}elseif($dspecial=='related'){
					$dcontent=(@$usecsslayout ? '<div class="reltab">' : '<table class="reltab" width="100%">');
					if(@$relatedtabtemplate==''){
						if(@$usecsslayout)
							$relatedtabtemplate='<div class="reltabimage">%img%</div><div class="reltabname">%name% - %price%</div>' .
								'<div class="reltabdescription">%description%</div>';
						else
							$relatedtabtemplate='<tr><td class="reltabimage" rowspan="2">%img%</td><td class="reltabname">%name% - %price%</td></tr>' .
								'<tr><td class="reltabdescription">%description%</td></tr>';
					}
					$sSQL='SELECT pId,pSection,'.getlangid('pName',1).','.$WSP.'pPrice,pStaticPage,pStaticURL,pExemptions,'.getlangid('pDescription',2)." FROM products INNER JOIN relatedprods ON products.pId=relatedprods.rpRelProdID WHERE pDisplay<>0 AND rpProdID='".$prodid."'";
					if(@$relatedproductsbothways==TRUE) $sSQL.=' UNION SELECT pId,pSection,'.getlangid('pName',1).','.$WSP.'pPrice,pStaticPage,pStaticURL,pExemptions,'.getlangid('pDescription',2)." FROM products INNER JOIN relatedprods ON products.pId=relatedprods.rpProdID WHERE pDisplay<>0 AND rpRelProdID='".$prodid."'";
					if(@$sSortBy!='') $sSQL.=' ORDER BY ' . $sSortBy . (@$isdesc?' DESC':'');
					$result=ect_query($sSQL) or ect_error();
					if(ect_num_rows($result)==0)
						$hascontent=FALSE;
					else{
						while($rs2=ect_fetch_assoc($result)){

					



							$rpsmallimage=''; $rplargeimage='';
							$sSQL="SELECT imageSrc,imageType FROM productimages WHERE imageProduct='" . $rs2['pId'] . "' AND (imageType=0 OR imageType=1) AND imageNumber=0";
							$result3=ect_query($sSQL) or ect_error();
							while($rs3=ect_fetch_assoc($result3)){
								if($rs3['imageType']==0) $rpsmallimage=$rs3['imageSrc']; else $rplargeimage=$rs3['imageSrc'];
							}
							ect_free_result($result3);
							$thedetailslink=getdetailsurl($rs2['pId'],$rs2['pStaticPage'],$rs2[getlangid('pName',1)],$rs2['pStaticURL'],(@$catid!='' && @$catid!='0' && $catid!=$rs2['pSection'] && @$nocatid!=TRUE ? '?cat=' . $catid : ''),@$GLOBALS['pathtohere']);
							if(@$detailslink!=''){
								$startlink=str_replace('%pid%', $rs2['pId'], str_replace('%largeimage%', $rplargeimage, $detailslink));
								$endlink=@$detailsendlink;
							}else{
								$startlink='<a class="ectlink" href="'. htmlspecials($thedetailslink) .'">';
								$endlink='</a>';
							}
							$rtc=str_replace('%img%', ($rpsmallimage!='' ? $startlink . '<img class="reltabimage" src="' . $rpsmallimage . '" style="border:0" alt="'.htmlspecials(strip_tags($rs2[getlangid('pName',1)])).'" />' . $endlink : '&nbsp;'), $relatedtabtemplate);
							$rtc=str_replace('%name%', $startlink . $rs2[getlangid('pName',1)] . $endlink, $rtc);
							$rtc=str_replace('%id%', $startlink . $rs2['pId'] . $endlink, $rtc);
							$rtc=str_replace('%price%', ($rs2['pPrice']==0 && @$pricezeromessage!='' ? $pricezeromessage : FormatEuroCurrency(@$GLOBALS['showtaxinclusive']===2 && ($rs2['pExemptions'] & 2)!=2 ? $rs2['pPrice']+($rs2['pPrice']*$thetax/100.0) : $rs2['pPrice'])), $rtc);
							$shortdesc=$rs2[getlangid('pDescription',2)];
							if(@$shortdescriptionlimit!=''){ if(@$nostripshortdescription!=TRUE)$shortdesc=strip_tags($shortdesc); $shortdesc=substr($shortdesc, 0, $shortdescriptionlimit) . (strlen($shortdesc)>$shortdescriptionlimit && $shortdescriptionlimit!=0 ? '...' : ''); }
							$rtc=str_replace('%description%', $shortdesc, $rtc);
							$dcontent.=$rtc;
						}
					}
					ect_free_result($result);
					$dcontent.=(@$usecsslayout ? '</div>' : '</table>');
				}elseif($dspecial=='ectdescription')
					$isdescriptiontab=TRUE;
				if($hascontent){
					if(@$ecttabs=='slidingpanel')
						$displaytabs.='<li><a href="#" id="ecttab'.$tabindex.'" class="tab'.($tabindex==1?'Active':'').'" title="'.$dtitle.'">';
					else
						$displaytabs.='<li class="TabbedPanelsTab" tabindex="0">';
					if($dimage!=''){
						$displaytabs.='<img src="'.$dimage.'" alt="'.htmlspecials($dtitle).'" ';
						if($dimageov!='') $displaytabs.='onmouseover="this.src=\''.$dimageov.'\'" onmouseout="this.src=\''.$dimage.'\'" ';
						$displaytabs.='/>';
					}else
						$displaytabs.=str_replace(' ','&nbsp;',$dtitle);
					if(@$ecttabs=='slidingpanel'){
						$displaytabs.='</a></li>';
						$tabcontent.='<div id="ecttab'.$tabindex.'Panel" class="tabpanelcontent"'.($isdescriptiontab?' itemprop="description"':'').'>'.$dcontent.'</div>';
					}else{
						$displaytabs.='</li>';
						$tabcontent.='<div class="tabpanelcontent"'.($isdescriptiontab?' itemprop="description"':'').'>'.$dcontent.'</div>';
					}
				}
				$tabindex++;
			}
		}
		if(@$ecttabs=='slidingpanel'){
			$displaytabs.='</ul></div>'.$tabcontent.'</div></div>';
			$displaytabs.='<script type="text/javascript">var sp2;var quotes;var lastTab="ecttab1";';
			$displaytabs.='function switchTab(tab){if(tab!=lastTab){document.getElementById(tab).className=("tabActive");document.getElementById(lastTab).className=("tab");sp2.showPanel(tab+"Panel");lastTab=tab;}}';
			$displaytabs.='Spry.Utils.addLoadListener(function(){';
			$displaytabs.="	Spry.$$('.slidingTabPanelWrapper').setStyle('display: block');";
			$displaytabs.="	Spry.$$('#ecttab1";
			for($i=2;$i<=$tabindex-1;$i++){
				$displaytabs.=',#ecttab'.$i;
			}
			$displaytabs.="').addEventListener('click', function(){ switchTab(this.id); return false; }, false);";
			$displaytabs.="	Spry.$$('#slidingPanel').addClassName('SlidingPanels').setAttribute('tabindex', '0');";
			$displaytabs.="	Spry.$$('#slidingPanel > div').addClassName('SlidingPanelsContentGroup');";
			$displaytabs.="	Spry.$$('#slidingPanel .SlidingPanelsContentGroup > div').addClassName('SlidingPanelsContent');";
			$displaytabs.="	sp2=new Spry.Widget.SlidingPanels('slidingPanel');";
			$displaytabs.='});</script>';
		}else{
			$displaytabs.='</ul>'.$tabcontent.'</div></div>';
			$displaytabs.='<script type="text/javascript">var TabbedPanels1=new Spry.Widget.TabbedPanels("TabbedPanels1");</script>';
		}
		return('>' . $displaytabs);
	}else
		return(' itemprop="description">' . $thedesc);
}
if(@$GLOBALS['magictoolbox']!=''){ $GLOBALS['magictoolboxjs']=$GLOBALS['magictoolbox']; $GLOBALS['magictoolbox']=str_replace('MagicZoomPlus','MagicZoom',$GLOBALS['magictoolbox']); $GLOBALS['magictool']=$GLOBALS['magictoolbox']; $GLOBALS['giantimageinpopup']=FALSE; }
function showdetailimages(){
	global $Count,$rs,$extraimages,$magictoolbox,$magictooloptionsjs,$magictooloptions,$magic360images,$allimages,$allgiantimages,$numallimages,$numallgiantimages,$psmallimage,$thumbnailstyle,$magictouchid,$usecsslayout,$magicscrollthumbnails,$magicscrollthumbnailsjs;
	if(@$thumbnailstyle=='') $thumbnailstyle='width:75px;padding:3px';
	if(is_array($allimages)){
		if(@$magictoolbox!='' && (is_array($allgiantimages) || strtolower($magictoolbox)=='magic360')){
			print '<script src="' . ($magictoolbox=='MagicTouch' ? 'http://www.magictoolbox.com/mt/' . $magictouchid . '/magictouch.js' : strtolower($GLOBALS['magictoolboxjs']) . '/' . strtolower($GLOBALS['magictoolboxjs']) . '.js') . '" type="text/javascript"></script>' . @$magictooloptionsjs;
			if(@$magicscrollthumbnails) print '<script src="magicscroll/magicscroll.js" type="text/javascript"></script>' . @$magicscrollthumbnailsjs;
			if($magictoolbox=='MagicSlideshow' || $magictoolbox=='MagicScroll'){
				print '<div class="' . $magictoolbox . '">';
				for($index=0;$index<$numallimages;$index++){
					if($index<$numallgiantimages) $giantimage=$allgiantimages[$index]['imageSrc']; else $giantimage='';
					print '<img itemprop="image" src="' . $allimages[$index]['imageSrc'] . '" alt="" '.($giantimage!=''&&$magictoolbox=='MagicSlideshow'?'data-fullscreen-image="'.$giantimage.'" ':'').'/>';
				}
				print '</div>';
			}elseif(strtolower($magictoolbox)=='magic360'){
				$magictoolbox=replace($magictoolbox,'magic360','Magic360');
				if(@$magic360images=='') $magic360images=18;
				$imgpattern=replace($allimages[0]['imageSrc'],'01','{col}');
				if(strpos($imgpattern,'/')!==FALSE) $imgpattern=substr($imgpattern,strrpos($imgpattern,'/')+1);
				if(is_array($allgiantimages)) $giantimage=$allgiantimages[0]['imageSrc']; else $giantimage='#';
				print '<a href="'.$giantimage.'" class="'.$magictoolbox.'" data-magic360-options="columns:'.$magic360images.';filename:'.$imgpattern.';"><img itemprop="image" src="'.$allimages[0]['imageSrc'].'" alt="" /></a>';
			}elseif($magictoolbox=='magic360plus' || $magictoolbox=='Magic360Flash'){
				$anchorstr='<a class="' . $magictoolbox . '" href="#" rel="' . $allgiantimages[0]['imageSrc'];
				$imagesstr='<img itemprop="image" src="' . $allimages[0]['imageSrc'] . '" alt="' . strip_tags($rs[getlangid('pName',1)]) . '" rel="' . $allimages[0]['imageSrc'];
				if(@$magic360images=='') $magic360images=18;
				for($magind=2;$magind<=$magic360images;$magind++){
					if($magictoolbox=='magic360plus') $anchorstr.= '*' . str_replace('01', ($magind<10 ? '0' . $magind : $magind), $allgiantimages[0]['imageSrc']);
					$imagesstr.= '*' . str_replace('01', ($magind<10 ? '0' . $magind : $magind), $allimages[0]['imageSrc']);
				}
				if($magictoolbox=='magic360plus') print $anchorstr . '">' . $imagesstr . '" /></a>'; else print $imagesstr . '" class="' . $magictoolbox . '" />';
			}elseif($magictoolbox=='MagicZoom' || $magictoolbox=='MagicZoomPlus' || $magictoolbox=='MagicTouch' || $magictoolbox=='MagicMagnify' || $magictoolbox=='MagicMagnifyPlus' || $magictoolbox=='MagicThumb'){
				if($numallimages>1 && ! @$usecsslayout) print '<table class="detailimage" border="0" cellspacing="1" cellpadding="1"><tr><td class="mainimage">';
				print '<a href="' . $allgiantimages[0]['imageSrc'] . '" class="' . $magictoolbox . '" ' . @$magictooloptions . ' id="zoom1"><img itemprop="image" id="prodimage'.$Count.'" class="detailimage" src="' . $allimages[0]['imageSrc'] . '" style="border:0" alt="' . strip_tags($rs[getlangid('pName',1)]) . '" /></a>';
				if($magictoolbox=='MagicThumb') $relid='thumb-id:'; else $relid='';
				if($magictoolbox=='MagicZoom' || $magictoolbox=='MagicZoomPlus') $relid='zoom-id:';
				if($numallimages>1){
					if(@$usecsslayout) print '<div class="thumbnailimage detailthumbnailimage">'; else print '</td></tr><tr><td class="thumbnailimage detailthumbnailimage" align="center">';
					if(@$magicscrollthumbnails) print '<div class="MagicScroll">';
					for($index=0;$index<$numallimages;$index++){
						if($index<$numallgiantimages) print '<a href="' . $allgiantimages[$index]['imageSrc'] . '" rev="' . $allimages[$index]['imageSrc'] . '" rel="' . $relid . 'zoom1"><img src="' . $allimages[$index]['imageSrc'] . '" style="' . $thumbnailstyle . '" alt="" /></a>';
					}
					if(@$magicscrollthumbnails) print '</div>';
					if(@$usecsslayout) print '</div>'; else print '</td></tr></table>';
				}
			}else
				print 'Magic Toolbox Option Not Recognized : ' . $magictoolbox . '<br />';
		}else{
			if(($numallimages>1 || is_array($allgiantimages)) && ! @$usecsslayout) print '<table class="detailimage" border="0" cellspacing="1" cellpadding="1"><tr><td class="mainimage">';
			print '<img itemprop="image" id="prodimage'.$Count.'" class="detailimage" src="' . $allimages[0]['imageSrc'] . '" alt="'.htmlspecials(strip_tags($rs[getlangid('pName',1)])).'" />';
			$showimagelink=(is_array($allgiantimages) ? '<span class="extraimage">(<a class="ectlink" href="javascript:showgiantimage(\'' . $allgiantimages[0]['imageSrc'] . '\')">'.$GLOBALS['xxEnlrge'].'</a>)</span>' : '');
			if($numallimages>1 || is_array($allgiantimages)) print (@$usecsslayout ? '<div class="imagenavigator detailimagenavigator">' : '</td></tr><tr><td class="imagenavigator detailimagenavigator" align="center">') . ($numallimages>1 ? '<img src="images/leftimage.gif" onclick="return updateprodimage('.$Count.', false);" onmouseover="this.style.cursor=\'pointer\'" alt="'.$GLOBALS['xxPrev'].'"' . (! @$usecsslayout ? ' style="vertical-align:middle;margin:0px;"' : '') . ' />' : '&nbsp;').' '.($numallimages>1 ? '<span class="extraimage extraimagenum" id="extraimcnt'.$Count.'">1</span> <span class="extraimage">'.$GLOBALS['xxOf'].' '.$extraimages.'</span> ' : ''). $showimagelink . ' '.($numallimages>1 ? '<img src="images/rightimage.gif" onclick="return updateprodimage('.$Count.', true);" onmouseover="this.style.cursor=\'pointer\'" alt="'.$GLOBALS['xxNext'].'" style="vertical-align:middle;margin:0px;" />' : '&nbsp;') . (@$usecsslayout ? '</div>' : '</td></tr></table>');
		}
	}elseif($psmallimage!=''){
		if(@$usecsslayout) print '<div class="detailimage">';
		print '<img itemprop="image" id="prodimage'.$Count.'" class="detailimage" src="' . $psmallimage . '" alt="'.htmlspecials(strip_tags($rs[getlangid('pName',1)])).'" />';
		if(@$usecsslayout) print '</div>';
	}else
		print '&nbsp;';
}
function writepreviousnextlinks(){
	global $previousid,$previousidname,$previousidstatic,$previousstaticurl,$previousidcat,$nextid,$nextidname,$nextidstatic,$nextstaticurl,$nextidcat,$thecatid,$catid,$nocatid;
	$currcat=(int)($thecatid!='' ? $thecatid : $catid);
	if($previousid!='') print '<a class="ectlink" href="' . getdetailsurl($previousid,$previousidstatic,$previousidname,$previousstaticurl,($previousidcat!=$currcat && @$nocatid!=TRUE?'cat='.$currcat:''),@$GLOBALS['pathtohere']).'">';
	print '<strong>&laquo; ' . $GLOBALS['xxPrev'] . '</strong>';
	if($previousid!='') print '</a>';
	print ' | ';
	if($nextid!='') print '<a class="ectlink" href="' . getdetailsurl($nextid,$nextidstatic,$nextidname,$nextstaticurl,($nextidcat!=$currcat && @$nocatid!=TRUE?'cat='.$currcat:''),@$GLOBALS['pathtohere']).'">';
	print '<strong>' . $GLOBALS['xxNext'] . ' &raquo;</strong>';
	if($nextid!='') print '</a>';
}
function detailpageurl($params){
	global $hasstaticpage,$rs,$prodid;
	$detailpageurl=getdetailsurl($prodid,$hasstaticpage,$rs[getlangid('pName',1)],$rs['pStaticURL'],$params,@$GLOBALS['pathtohere']);
	return($detailpageurl);
}
function showreviews($theSQL,$showall){
	global $prodid,$thecatid,$numcustomerratings,$customerratinglength,$onlyclientratings,$allreviewspagesize,$languageid,$dateformatstr,$rs,$catid,$ratingslanguages,$usecsslayout;
	$srv='';
	$numreviews=0; $totrating=0;
	$totSQL="SELECT COUNT(*) as numreviews, SUM(rtRating) AS totrating FROM ratings WHERE rtApproved<>0 AND rtProdID='" . escape_string($prodid) . "'";
	// if(@$ratingslanguages!='') $totSQL.=' AND rtLanguage+1 IN ('.$ratingslanguages.')'; elseif(@$languageid!='') $totSQL.=' AND rtLanguage='.((int)$languageid-1); else $totSQL.=' AND rtLanguage=0';
	$result=ect_query($totSQL) or ect_error();
	$rs2=ect_fetch_assoc($result);
	if(! is_null($rs2['numreviews'])){
		$numreviews=$rs2['numreviews'];
		$totrating=$rs2['totrating'];
	}
	ect_free_result($result);
	$srv=(@$usecsslayout ? '<div' : '<tr><td') . ' class="review" id="reviews">&nbsp;<br /><span class="review numreviews"' . ($numreviews<>0 ? ' itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"' : '') . '>' . ($numreviews<>0 ? '<span class="count" itemprop="ratingCount">' . $numreviews . '</span> ' : '') . $GLOBALS['xxRvPrRe'];
	if($numreviews > 0)
		$srv.=' - '.$GLOBALS['xxRvAvRa'].' <span class="rating average" itemprop="ratingValue">'.round(($totrating/$numreviews)/2,1).'</span> / 5';
	$srv.='</span><span class="review showallreview">';
	if($showall){
		$srv.=' (<a class="ectlink" href="' . detailpageurl('review=all' . ($thecatid!='' ? '&amp;cat='.$thecatid : '').'&amp;ro=1') . '">'.$GLOBALS['xxRvBest'].'</a>';
		$srv.=' | <a class="ectlink" href="' . detailpageurl('review=all' . ($thecatid!='' ? '&amp;cat='.$thecatid : '').'&amp;ro=2') . '">'.$GLOBALS['xxRvWors'].'</a>';
		$srv.=' | <a class="ectlink" href="' . detailpageurl('review=all' . ($thecatid!='' ? '&amp;cat='.$thecatid : '')) . '">'.$GLOBALS['xxRvRece'].'</a>';
		$srv.=' | <a class="ectlink" href="' . detailpageurl('review=all' . ($thecatid!='' ? '&amp;cat='.$thecatid : '').'&amp;ro=3') . '">'.$GLOBALS['xxRvOld'].'</a>)';
	}elseif($numreviews > 0)
		$srv.=' (<a class="ectlink" href="' . detailpageurl('review=all' . ($thecatid!='' ? '&amp;cat='.$thecatid : '')) . '">'.$GLOBALS['xxShoAll'].'</a>)';
	$srv.='</span><br /><hr class="review" />';
	if(@$allreviewspagesize=='') $allreviewspagesize=30;
	if($showall) $thepagesize=$allreviewspagesize; else $thepagesize=$numcustomerratings;
	$iNumOfPages=ceil($numreviews/$thepagesize);
	if(! is_numeric(getget('pg'))) $CurPage=1; else $CurPage=max(1, (int)getget('pg'));
	if($numreviews > 0){
		$theSQL.= ' LIMIT ' . ($thepagesize*($CurPage-1)) . ', ' . $thepagesize;
		$result=ect_query($theSQL) or ect_error();
		if(! (@$onlyclientratings && @$_SESSION['clientID']=='')) $srv.='<span class="review clickreview"><a class="ectlink" rel="nofollow" href="' . detailpageurl('review=true' . ($thecatid!='' ? '&amp;cat='.$thecatid : '')) . '">'.$GLOBALS['xxClkRev'].'</a></span><br /><hr class="review" />';
		while($rs2=ect_fetch_assoc($result)){
			$srv.='<div class="ecthreview" itemprop="review" itemscope itemtype="http://schema.org/Review"><span class="rating" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating"><meta itemprop="worstRating" content="1" /><meta itemprop="bestRating" content="5" /><meta itemprop="ratingValue" content="' . round($rs2['rtRating']/2) . '" />';
			for($index=1; $index <= (int)$rs2['rtRating'] / 2; $index++)
				$srv.='<img src="images/reviewcart.gif" alt="" style="vertical-align:middle;margin:0px;" />';
			$ratingover=$rs2['rtRating'];
			if($ratingover / 2 > (int)($ratingover / 2)){
				$srv.='<img src="images/reviewcarthg.gif" alt="" style="vertical-align:middle;margin:0px;" />';
				$ratingover++;
			}
			for($index=((int)$ratingover / 2) + 1; $index <= 5; $index++)
				$srv.='<img src="images/reviewcartg.gif" alt="" style="vertical-align:middle;margin:0px;" />';
			$srv.='</span> <span class="review reviewheader" itemprop="name">' . $rs2['rtHeader'] . '</span>';
			$srv.='<br /><br /><span class="review reviewname"><span class="reviewer" itemprop="author" itemscope itemtype="http://schema.org/Person">' . $rs2['rtPosterName'] . '</span> - <span class="dtreviewed">' . date($dateformatstr, strtotime($rs2['rtDate'])) . '<meta itemprop="datePublished" content="' . $rs2['rtDate'] . '" /></span></span>';
			$thecomments=$rs2['rtComments'];
			if(! $showall){
				if(@$customerratinglength=='') $customerratinglength=255;
				if(strlen($thecomments)>$customerratinglength) $thecomments=substr($thecomments, 0, $customerratinglength) . '...';
			}
			$srv.='<br /><br /><span class="summary review reviewcomments" itemprop="reviewBody">' . str_replace("\r\n", '<br />', $thecomments) . '</span><br /><hr class="review" />';
			$srv.='</div>';
		}
		ect_free_result($result);
	}else
		$srv.='<span class="review noreview">' . $GLOBALS['xxRvNone'] . '</span><br /><hr class="review" />';
	if(! (@$onlyclientratings && @$_SESSION['clientID']=='')) $srv.='<span class="review clickreview"><a class="ectlink" rel="nofollow" href="' . detailpageurl('review=true' . ($thecatid!='' ? '&amp;cat='.$thecatid : '')) . '">'.$GLOBALS['xxClkRev'].'</a></span><br /><hr class="review" />';
	$srv.=(@$usecsslayout ? '</div>' : '</td></tr>');
	$pblink='<a class="vrectlink" href="'.htmlentities(@$_SERVER['PHP_SELF']).'?';
	foreach(@$_GET as $objQS => $objValue)
		if($objQS!='cat' && $objQS!='id' AND $objQS!='pg') $pblink.=urlencode($objQS) . '=' . urlencode($objValue) . '&amp;';
	if($catid!='0' && @$explicitid=='') $pblink.='cat=' . $catid . '&amp;pg='; else $pblink.='pg=';
	if($showall && $iNumOfPages > 1) $srv.=(@$usecsslayout ? '<div' : '<tr><td align="center"') . ' class="pagenums">' . writepagebar($CurPage,$iNumOfPages,$GLOBALS['xxPrev'],$GLOBALS['xxNext'],$pblink,TRUE) . (@$usecsslayout ? '</div>' : '</td></tr>');
	return($srv);
}
function schemaconditionavail(){
	global $rs,$useStockManagement,$optionshavestock;
	if(@$GLOBALS['setschemacondition']) print '<meta itemprop="itemCondition" itemtype="http://schema.org/OfferItemCondition" content="http://schema.org/'.(@$GLOBALS['schemaitemcondition']!=''?$GLOBALS['schemaitemcondition']:'NewCondition').'" />';
	if(@$GLOBALS['setschemaavailability']){
		$isinstock=($rs['pSell']!=0);
		if($useStockManagement){
			if($rs['pStockByOpts']!=0) $isinstock=$optionshavestock; else $isinstock=((int)($rs['pInStock']) > 0);
		}
		print '<meta itemprop="availability" content="http://schema.org/'.($isinstock?'InStock':'OutOfStock').'" />';
	}
}
function pddsearchwords(){
	global $usecsslayout,$showproductid,$rs,$searchwordsseparator;
	if(trim($rs['pSearchParams'])!=''){
		$searchprms=explode(@$searchwordsseparator!=''?$searchwordsseparator:' ',$rs['pSearchParams']);
		print '<div class="searchwords">';
		if(@$GLOBALS['searchwordsheading']!='') print '<div class="searchwordsheading">' . $GLOBALS['searchwordsheading'] . '</div>';
		foreach($searchprms as $key=>$val){
			print ($key==0?'':' ') . '<a class="ectlink searchwords" href="search.php?pg=1&amp;stext='.htmlspecials($val).(@$GLOBALS['searchwordsnobox']?'&amp;nobox=true':'').'">'.htmlspecials($val).'</a>';
		}
		print '</div>';
	}
}
function pddquantitypricing(){
	global $rs,$WSP;
	$retval='';
	$numquantprices=0;
	$sSQL='SELECT '.$WSP."pPrice,pbQuantity FROM pricebreaks WHERE pbProdID='".escape_string($rs['pId'])."' ORDER BY pbQuantity";
	$result2=ect_query($sSQL) or ect_error();
	if(ect_num_rows($result2)>0){
		$retval='<div class="detailquantpricingwrap"><div class="detailquantpricing" style="display:table">';
		if(@$GLOBALS['xxQuaPri']!='') $retval.='<div class="detailqpheading" style="display:table-row">' . $GLOBALS['xxQuaPri'] . '</div>';
		$retval.='<div class="detailqpheaders" style="display:table-row"><div class="detailqpheadquant" style="display:table-cell">' . $GLOBALS['xxQuanti'] . '</div><div class="detailqpheadprice" style="display:table-cell">' . $GLOBALS['xxPriQua'] . '</div></div>';
		while($rs2=ect_fetch_assoc($result2))
			$quantpricearray[$numquantprices++]=$rs2;
		for($index=0;$index<$numquantprices;$index++){
			if($index<$numquantprices-1){
				$nextquant=$quantpricearray[$index+1]['pbQuantity']-1;
				$nextquant=$nextquant>$quantpricearray[$index]['pbQuantity']?'-'.$nextquant:'';
			}else
				$nextquant='+';
			$retval.='<div class="detailqprow" style="display:table-row"><div class="detailqpquant" style="display:table-cell">' . $quantpricearray[$index]['pbQuantity'] . $nextquant . '</div><div class="detailqpprice" style="display:table-cell">' . FormatEuroCurrency($quantpricearray[$index]['pPrice']) . '</div></div>';
		}
		$retval.='</div></div>';
	}
	ect_free_result($result2);
	return($retval);
}
function pddreviewstars($issmall){
	global $rs;
	if($rs['pNumRatings']>0){
		print '<div class="detailreviewstars"><a href="#reviews">';
		$therating=(int)($rs['pTotRating']/$rs['pNumRatings']);
		for($index=1; $index <= (int)($therating / 2); $index++){
			print '<img class="detailreviewstars" src="images/'.$issmall.'reviewcart.gif" alt="" style="vertical-align:middle;margin:0px;border:0px;" />';
		}
		$ratingover=$therating;
		if($ratingover / 2 > (int)($ratingover / 2)){
			print '<img class="detailreviewstars" src="images/'.$issmall.'reviewcarthg.gif" alt="" style="vertical-align:middle;margin:0px;border:0px;" />';
			$ratingover++;
		}
		for($index=(int)($ratingover / 2) + 1; $index <= 5; $index++){
			print '<img class="detailreviewstars" src="images/'.$issmall.'reviewcartg.gif" alt="" style="vertical-align:middle;margin:0px;border:0px;" />';
		}
		print '</a><span class="prodratingtext">';
		if(@$GLOBALS['detailreviewstarstext']!='') print str_replace(array('%numratings%','%totrating%'),array($rs['pNumRatings'],round($rs['pTotRating']/$rs['pNumRatings']/2,1)),$GLOBALS['detailreviewstarstext']);
		print '</span></div>';
	}else
		print @$GLOBALS['detailreviewnoratings'];
}
function pddreviews(){
	global $usecsslayout,$enablecustomerratings,$prodid,$ratingslanguages,$reviewsshown,$productindb,$thecatid,$onlyclientratings;
	if(getpost('review')=='true' || getget('review')=='all'){
		// Do nothing
	}elseif(@$enablecustomerratings==TRUE && getget('review')=='true'){
		if(@$onlyclientratings && @$_SESSION['clientID']=='')
			print '<tr><td align="center">Only logged in customers can review products.</td></tr>';
		else{
			if(! @$usecsslayout) print '<tr><td>' ?>
	<script type="text/javascript">
	/* <![CDATA[ */
	function checkratingform(frm){
	if(frm.ratingstars.selectedIndex==0){
		alert("<?php print jscheck($GLOBALS['xxRvPlsS'])?>.");
		frm.ratingstars.focus();
		return(false);
	}
	if(frm.reviewposter.value==""){
		alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxRvPosb'])?>\".");
		frm.reviewposter.focus();
		return(false);
	}
	if(frm.reviewheading.value==""){
		alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxRvHead'])?>\".");
		frm.reviewheading.focus();
		return(false);
	}
	document.getElementById('rfsectgrp1').value=document.getElementById('ratingstars')[document.getElementById('ratingstars').selectedIndex].value;
	document.getElementById('rfsectgrp2').value=document.getElementById('reviewposter').value.length;
	return(true);
	}
	/* ]]> */
	</script>
		<form method="post" action="<?php print detailpageurl($thecatid!='' ? 'cat='.$thecatid : '')?>" style="margin:0px;padding:0px;"  onsubmit="return checkratingform(this)">
		<input type="hidden" name="review" value="true" />
		<input type="hidden" name="rfsectgrp1" id="rfsectgrp1" value="6344" />
		<input type="hidden" name="rfsectgrp2" id="rfsectgrp2" value="923" />
	<?php	if(@$usecsslayout) print '<div class="reviewformblock">'; else print '<table border="0" cellspacing="0" cellpadding="2" width="100%" align="center">';
			if(@$usecsslayout) print '<div class="ectformline reviewformline">'; else print '<tr><td align="right">'?><div class="review reviewform reviewlabels"><span class="review reviewstar" style="color:#FF0000">*</span><?php print $GLOBALS['xxRvRati']?>:</div><?php if(!@$usecsslayout) print '</td><td>'?><div class="review reviewform reviewfields"><select size="1" name="ratingstars" id="ratingstars" class="review reviewform"><option value=""><?php print $GLOBALS['xxPlsSel']?></option><?php
				for($index=1; $index<=5; $index++){
					print '<option value="'.$index.'">'.$index.' '.$GLOBALS['xxStars'].'</option>';
				} ?></select></div><?php if(@$usecsslayout) print '</div>'; else print '</td></tr>';
			if(@$usecsslayout) print '<div class="ectformline reviewformline">'; else print '<tr><td align="right">'?><div class="review reviewform reviewlabels"><span class="review reviewstar" style="color:#FF0000">*</span><?php print $GLOBALS['xxRvPosb']?>:</div><?php if(!@$usecsslayout) print '</td><td>'?><div class="review reviewform reviewfields"><input type="text" size="20" name="reviewposter" id="reviewposter" maxlength="64" value="<?php print htmlspecials(@$_SESSION['clientUser'])?>" class="review reviewform" /></div><?php if(@$usecsslayout) print '</div>'; else print '</td></tr>';
			if(@$usecsslayout) print '<div class="ectformline reviewformline">'; else print '<tr><td align="right">'?><div class="review reviewform reviewlabels"><span class="review reviewstar" style="color:#FF0000">*</span><?php print $GLOBALS['xxRvHead']?>:</div><?php if(!@$usecsslayout) print '</td><td>'?><div class="review reviewform reviewfields"><input type="text" size="40" name="reviewheading" maxlength="253" class="review reviewform" /></div><?php if(@$usecsslayout) print '</div>'; else print '</td></tr>';
			if(@$usecsslayout) print '<div class="ectformline reviewformline">'; else print '<tr><td align="right">'?><div class="review reviewform reviewlabels"><?php print $GLOBALS['xxRvComm']?>:</div><?php if(!@$usecsslayout) print '</td><td>'?><div class="review reviewform reviewfields"><textarea name="reviewcomments" cols="38" rows="8" class="review reviewform"></textarea></div><?php if(@$usecsslayout) print '</div>'; else print '</td></tr>';
			if(@$usecsslayout) print '<div class="ectformline reviewformline">'; else print '<tr><td align="right">&nbsp;'?><?php if(!@$usecsslayout) print '</td><td>'?><div class="review reviewform reviewfields"><input type="submit" value="<?php print $GLOBALS['xxSubmt']?>" class="review reviewform reviewsubmit" /></div><?php if(@$usecsslayout) print '</div>'; else print '</td></tr>';
			if(@$usecsslayout) print '</div>'; else print '</table>' ?>
		</form>
	<?php	if(! @$usecsslayout) print '</td></tr>';
		}
	}elseif(@$enablecustomerratings==TRUE){
		$sSQL="SELECT rtID,rtRating,rtPosterName,rtHeader,rtDate,rtComments FROM ratings WHERE rtApproved<>0 AND rtProdID='".escape_string($prodid)."'";
		if(@$ratingslanguages!='') $sSQL.=' AND rtLanguage+1 IN ('.$ratingslanguages.')'; elseif(@$languageid!='') $sSQL.=' AND rtLanguage='.((int)$languageid-1); else $sSQL.=' AND rtLanguage=0';
		$sSQL.=' ORDER BY rtDate DESC,rtRating DESC';
		if(! $reviewsshown && $productindb) print showreviews($sSQL,FALSE);
	}
}
function pddprodnavigation(){
	global $showcategories,$usecsslayout,$sstrong,$tslist,$estrong;
	if(! @$usecsslayout) print '<table width="100%" border="0" cellspacing="3" cellpadding="3"><tr>';
	print (@$usecsslayout ? '<div' : '<td colspan="3" align="left" valign="top"') . ' class="prodnavigation detailprodnavigation">' . $sstrong . (! @$usecsslayout ? '<p class="prodnavigation detailprodnavigation">' : '') . $tslist . (! @$usecsslayout ? '</p>' . $estrong . '</td>' : '</div>');
}
function pddcheckoutbutton(){
	global $usecsslayout,$isinstock,$proddetailtopbuybutton,$nobuyorcheckout,$imgbackorderbutton,$imgbuybutton,$Count,$imgcheckoutbutton,$isbackorder,$usehardaddtocart;
	print (@$usecsslayout ? '<div' : '<td align="right" valign="top"') . ' class="checkoutbutton detailcheckoutbutton">&nbsp;';
	if($isinstock && @$proddetailtopbuybutton==TRUE && @$nobuyorcheckout!=TRUE && trim(@$_REQUEST['review'])==''){
		if($isbackorder)
			print imageorbutton(@$imgbackorderbutton,$GLOBALS['xxBakOrd'],'buybutton backorder detailbuybutton detailbackorder',(@$usehardaddtocart?'subformid':'ajaxaddcart').'('.$Count.')',TRUE) . '&nbsp;';
		else
			print imageorbutton(@$imgbuybutton,$GLOBALS['xxAddToC'],'buybutton detailbuybutton',(@$usehardaddtocart?'subformid':'ajaxaddcart').'('.$Count.')', TRUE) . '&nbsp;';
	}
	if(@$nobuyorcheckout!=TRUE) print imageorbutton($imgcheckoutbutton,$GLOBALS['xxCOTxt'],'checkoutbutton detailcheckoutbutton','cart.php', FALSE);
	print (@$usecsslayout ? '</div>' : '</td></tr></table>');
}
function pddproductimage(){
	global $usecsslayout;
	if(! @$usecsslayout) print '<table width="100%" border="0" cellspacing="3" cellpadding="3"><tr>';
	print (@$usecsslayout ? '<div' : '<td width="30%" align="center"') . ' class="detailimage">';
	showdetailimages();
	print (@$usecsslayout ? '</div>' : '</td>');
}
function pddproductid(){
		global $usecsslayout,$showproductid,$rs;
		if(! @$usecsslayout) print '<td>&nbsp;</td><td width="70%" valign="top" class="detail">';
		if(@$showproductid==TRUE) print '<div class="detailid"><strong>' . $GLOBALS['xxPrId'] . ':</strong> <span itemprop="productID">' . $rs['pId'] . '</span></div>';
}
function pddmanufacturer(){
	global $manufacturerfield,$rs;
	if(@$manufacturerfield!='' && ! is_null($rs[getlangid('scName',131072)])) print '<div class="prodmanufacturer detailmanufacturer"><strong>' . $manufacturerfield . ':</strong> <span itemprop="manufacturer">' . $rs[getlangid('scName',131072)] . '</span></div>';
}
function pddsku(){
	global $showproductsku,$rs;
	if(@$showproductsku!='' && $rs['pSKU']!='') print '<div class="detailsku"><strong>' . $showproductsku . ':</strong> <span itemprop="sku">' . $rs['pSKU'] . '</span></div>';
}
function pddcustom($custid){
	global $detailcustomlabel1,$detailcustomlabel2,$detailcustomlabel3,$rs;
	eval('$customlabel=@$detailcustomlabel' . $custid . ';');
	if(trim($rs['pCustom'.$custid])!='') print '<div class="detailcustom'.$custid.'">' . $customlabel . $rs['pCustom'.$custid] . '</div>';
}
function pdddateadded(){
	global $rs,$dateaddedlabel,$dateformatstr;
	if(trim($rs['pDateAdded'])!='') print '<div class="detaildateadded">' . (@$dateaddedlabel!=''?'<div class="detaildateaddedlabel">' . $dateaddedlabel . '</div>':'') . '<div class="detaildateaddeddate">' . date($dateformatstr,strtotime($rs['pDateAdded'])) . '</div></div>';
}
function pdddetailname(){
	global $sstrong,$estrong,$rs,$alldiscounts,$nomarkup,$detailnameh1;
	print $sstrong . '<div class="detailname"><'.(@$detailnameh1?'h1':'span').' itemprop="name">' . $rs[getlangid('pName',1)] . '</'.(@$detailnameh1?'h1':'span').'>' . $GLOBALS['xxDot'];
	if($alldiscounts!='') print ' ' . (@$nomarkup?'':'<font color="#FF0000">') . '<span class="discountsapply detaildiscountsapply">' . $GLOBALS['xxDsApp'] . '</span>' . (@$nomarkup?'':'</font>');
	print '</div>' . $estrong;
}
function pdddiscounts(){
	global $alldiscounts,$nomarkup;
	if($alldiscounts!='') print '<div class="detaildiscounts"' . (@$nomarkup?'':' style="font-size:11px;color:#FF0000;"') . '>' . $alldiscounts . '</div>';
}
function pddinstock(){
	global $useStockManagement,$showinstock,$rs;
	if($useStockManagement && @$showinstock==TRUE){ if((int)$rs['pStockByOpts']==0) print '<div class="prodinstock detailinstock"><strong>' . $GLOBALS['xxInStoc'] . ':</strong> ' . max(0,$rs['pInStock']) . '</div>'; }
}
function pddshortdescription(){
	global $rs;
	print '<div class="detailshortdescription">'.$rs[getlangid('pDescription',2)].'</div>';
}
function pdddescription(){
	global $usecsslayout,$usedetailbodyformat,$rs,$longdesc;
	if(! @$usecsslayout) print '<br />';
	if(@$usedetailbodyformat==3){
	}elseif($longdesc!='')
		print '<div class="detaildescription"' . displaytabs($longdesc) . '</div>';
	elseif(trim($rs[getlangid('pDescription',2)])!='')
		print '<div class="detaildescription" itemprop="description">' . $rs[getlangid('pDescription',2)] . '</div>';
}
function pddlistprice(){
	global $noprice,$rs,$thetax;
	if(@$noprice==TRUE)
		print '&nbsp;';
	elseif((double)$rs['pListPrice']!=0.0){
		$plistprice=(@$GLOBALS['showtaxinclusive']===2 && ($rs['pExemptions'] & 2)!=2?$rs['pListPrice']+($rs['pListPrice']*$thetax/100.0):$rs['pListPrice']);
		print '<div class="detaillistprice">' . str_replace('%s', FormatEuroCurrency($plistprice), $GLOBALS['xxListPrice']) . (@$GLOBALS['yousavetext']!=''?str_replace('%s', FormatEuroCurrency($plistprice-(@$GLOBALS['showtaxinclusive']===2 && ($rs['pExemptions'] & 2)!=2 ? $rs['pPrice']+($rs['pPrice']*$thetax/100.0) : $rs['pPrice'])), $GLOBALS['yousavetext']):'') . '</div>';
	}
		
}
function pddprice(){
	global $Count,$rs,$pricezeromessage,$thetax,$ssIncTax,$noprice,$countryCurrency;
	if(@$noprice!=TRUE){
		$separatetaxinc=@$GLOBALS['showtaxinclusive']==1 && ($rs['pExemptions'] & 2)!=2;
		print '<div class="detailprice"' . ($rs['pPrice']!=0?' itemprop="offers" itemscope itemtype="http://schema.org/Offer"><meta itemprop="priceCurrency" content="'.$countryCurrency.'"':'') . '><strong>' . $GLOBALS['xxPrice'].($GLOBALS['xxPrice']!=''?':':'') . '</strong> <span class="price" id="pricediv' . $Count . '"'.($rs['pPrice']!=0&&!$separatetaxinc?' itemprop="price"':'').'>' . ($rs['pPrice']==0 && @$pricezeromessage!='' ? $pricezeromessage : FormatEuroCurrency(@$GLOBALS['showtaxinclusive']===2 && ($rs['pExemptions'] & 2)!=2 ? $rs['pPrice']+($rs['pPrice']*$thetax/100.0) : $rs['pPrice'])) . '</span> ';
		if($separatetaxinc) printf('<span id="taxmsg' . $Count . '"' . ($rs['pPrice']==0 ? ' style="display:none"' : '') . '>' . $ssIncTax . '</span>','<span id="pricedivti' . $Count . '" itemprop="price">' . ($rs['pPrice']==0 ? '-' : FormatEuroCurrency($rs['pPrice']+($rs['pPrice']*$thetax/100.0))) . '</span> ');
		schemaconditionavail();
		print '</div>';
	}
}
function pddextracurrency(){
	global $hascustomlayout,$currRate1,$currRate2,$currRate3,$currSymbol1,$currSymbol2,$currSymbol3,$currFormat1,$currFormat2,$currFormat3,$rs,$currencyseparator,$Count,$usecsslayout,$orcdecimals,$orcthousands,$noprice,$showquantitypricing;
	if(@$noprice!=TRUE || $hascustomlayout){
		$extracurr='';
		if($currRate1!=0 && $currSymbol1!='') $extracurr=str_replace('%s',number_format($rs['pPrice']*$currRate1,checkDPs($currSymbol1),$orcdecimals,$orcthousands),$currFormat1) . $currencyseparator;
		if($currRate2!=0 && $currSymbol2!='') $extracurr.=str_replace('%s',number_format($rs['pPrice']*$currRate2,checkDPs($currSymbol2),$orcdecimals,$orcthousands),$currFormat2) . $currencyseparator;
		if($currRate3!=0 && $currSymbol3!='') $extracurr.=str_replace('%s',number_format($rs['pPrice']*$currRate3,checkDPs($currSymbol3),$orcdecimals,$orcthousands),$currFormat3);
		if($extracurr!='') print '<div class="detailcurrency"><span class="extracurr" id="pricedivec' . $Count . '">' . ($rs['pPrice']==0 ? '' : $extracurr) . '</span></div>';
		if(@$GLOBALS['showquantitypricing']&&!@$hascustomlayout)
			print pddquantitypricing();
		if(! @$usecsslayout) print '<hr width="80%" class="detailhr detailcurrencyhr currencyhr" />';
	}
}
function pddquantity(){
	global $hascustomlayout,$Count,$isinstock,$isbackorder,$nobuyorcheckout,$showquantondetail,$hasmultipurchase,$quantityupdown;
	if($hascustomlayout){
		if(($isinstock || $isbackorder) && @$nobuyorcheckout!=TRUE && (@$showquantondetail==TRUE || ! @isset($showquantondetail)) && $hasmultipurchase==0){
			print '<div class="detailquantity"><div class="detailquantitytext">' . $GLOBALS['xxQuant'] . ':' . '</div><div class="quantitydiv detailquantityinput" style="'.(@$quantityupdown?'margin-right:12px;':'').'white-space:nowrap"><input type="text" name="quant" id="w'.$Count.'quant" maxlength="5" size="4" value="1" title="' . $GLOBALS['xxQuant'] . '" ' . (@$quantityupdown?'style="float:left" ':'') . '/>' . (@$quantityupdown?'<img src="images/quantarrowu.png" onclick="quantup('.$Count.',1)" alt="" /><br /><img src="images/quantarrowd.png" onclick="quantup('.$Count.',0)" alt="" />':'') . '</div></div>';
		}
	}
}
function pddoptions(){
	global $hascustomlayout,$Count,$prodoptions,$optjs,$optionshtml,$usecsslayout,$isinstock,$isbackorder,$nobuyorcheckout,$showquantondetail,$hasmultipurchase,$quantityupdown;
	displayformvalidator();
	if($optjs!='') $optionshtml.='<script type="text/javascript">/* <![CDATA[ */'.$optjs.'/* ]]> */</script>';
	if(is_array($prodoptions)){
		if($prodoptions[0]['optType']==4) $thestyle=''; else $thestyle=' width="100%"';
		if($optionshtml!='') $optionshtml='<div class="detailoptions"' . (! @$usecsslayout ? ' align="center"' : '') . '>' . (! @$usecsslayout ? '<table class="prodoptions detailoptions" border="0" cellspacing="1" cellpadding="1"' . $thestyle . '>' : '') . $optionshtml;
		if($optionshtml!='') $optionshtml.=(@$usecsslayout ? '</div>' : '');
		if(! $hascustomlayout && ($isinstock || $isbackorder) && @$nobuyorcheckout!=TRUE && (@$showquantondetail==TRUE || ! @isset($showquantondetail)) && $hasmultipurchase==0){
			$optionshtml.=(@$usecsslayout ? '<div class="detailquantity"><div class="detailquantitytext">' : '<tr><td align="right">') . $GLOBALS['xxQuant'] . ':' . (@$usecsslayout ? '</div>' : '</td><td align="left">') . '<div class="quantitydiv detailquantityinput" style="'.(@$quantityupdown?'margin-right:12px;':'').'white-space:nowrap"><input type="text" name="quant" id="w'.$Count.'quant" maxlength="5" size="4" value="1" title="' . $GLOBALS['xxQuant'] . '" ' . (@$quantityupdown?'style="float:left" ':'') . '/>' . (@$quantityupdown?'<img src="images/quantarrowu.png" onclick="quantup('.$Count.',1)" alt="" /><br /><img src="images/quantarrowd.png" onclick="quantup('.$Count.',0)" alt="" />':'') . '</div>' . (@$usecsslayout ? '</div>' : '</td></tr>');
		}
		if($optionshtml!='') $optionshtml.=(! @$usecsslayout ? '</table></div>' : '');
	}elseif(! $hascustomlayout){
		if(($isinstock || $isbackorder) && @$nobuyorcheckout!=TRUE && (@$showquantondetail==TRUE || ! @isset($showquantondetail))){
			$optionshtml.=(@$usecsslayout ? '<div class="detailquantity"><div class="detailquantitytext">' : '<table border="0" cellspacing="1" cellpadding="1" width="100%"><tr><td align="right">');
			$optionshtml.=$GLOBALS['xxQuant'] . ':';
			$optionshtml.=(@$usecsslayout ? '</div>' : '</td><td>');
			$optionshtml.='<div class="quantitydiv detailquantityinput" style="'.(@$quantityupdown?'margin-right:12px;':'').'white-space:nowrap"><input type="text" name="quant" id="w'.$Count.'quant" maxlength="5" size="4" value="1" title="' . $GLOBALS['xxQuant'] . '" ' . (@$quantityupdown?'style="float:left" ':'') . '/>' . (@$quantityupdown?'<img src="images/quantarrowu.png" onclick="quantup('.$Count.',1)" alt="" /><br /><img src="images/quantarrowd.png" onclick="quantup('.$Count.',0)" alt="" />':'') . '</div>' . (@$usecsslayout ? '</div>' : '</td></tr></table>');
		}
	}
}
$isfirstaddtocart=TRUE;
function pddaddtocart(){
	global $usecsslayout,$nobuyorcheckout,$rs,$nosellzeroprice,$isinstock,$isbackorder,$wishlistondetail,$imgbackorderbutton,$custombuybutton,$atcmu;
	global $imgbuybutton,$imgaddtolist,$Count,$notifybackinstock,$imgnotifyinstock,$optionshavestock,$sstrong,$estrong,$usehardaddtocart,$isfirstaddtocart;
	$atcmu=(! @$usecsslayout ? '<p align="center">' : '');
	
	if(@$nobuyorcheckout==TRUE)
		$atcmu.='&nbsp;';
	else{
		if($rs['pPrice']==0 && @$nosellzeroprice==TRUE){
			$atcmu.='&nbsp;';
		}elseif($isinstock || $isbackorder){
			if($isfirstaddtocart){
				writehiddenvar('id', $rs['pId']);
				writehiddenvar('mode', 'add');
				if($wishlistondetail) writehiddenvar('listid', '');
			}
			if(@$usecsslayout) $atcmu.='<div class="addtocart detailaddtocart">';
			if($isbackorder){
				if(@$usehardaddtocart) $atcmu.=imageorsubmit(@$imgbackorderbutton,$GLOBALS['xxBakOrd'],'buybutton backorder detailbuybutton detailbackorder'); else $atcmu.=imageorbutton(@$imgbackorderbutton,$GLOBALS['xxBakOrd'],'buybutton backorder detailbuybutton detailbackorder','ajaxaddcart('.$Count.')',TRUE);
			}else{
				if(@$custombuybutton!='')
					$atcmu.=$custombuybutton;
				else{
					if(@$usehardaddtocart) $atcmu.=imageorsubmit(@$imgbuybutton,$GLOBALS['xxAddToC'],'buybutton detailbuybutton'); else $atcmu.=imageorbutton(@$imgbuybutton,$GLOBALS['xxAddToC'],'buybutton detailbuybutton','ajaxaddcart('.$Count.')',TRUE);
				}
			}
			if($wishlistondetail) $atcmu.='<div class="detailwishlist">' . imageorlink(@$imgaddtolist,$GLOBALS['xxAddLis'],'','gtid='.$Count.';return displaysavelist(event,window)',TRUE) . '</div>';
			if(@$usecsslayout) $atcmu.='</div>';
		}else{
			if(@$notifybackinstock)
				$atcmu.='<div class="notifystock detailnotifystock">' . imageorlink(@$imgnotifyinstock,$GLOBALS['xxNotBaS'],'',"return notifyinstock(false,'".str_replace("'","\\'",$rs['pId'])."','".str_replace("'","\\'",$rs['pId'])."',".($rs['pStockByOpts']!=0&&!@$optionshavestock?'-1':'0').")", TRUE) . '</div>';
			else
				$atcmu.='<div class="outofstock detailoutofstock">' . $sstrong . $GLOBALS['xxOutStok'] . $estrong . '</div>';
			$atcmu.='<br />';
		}
	}
	$isfirstaddtocart=FALSE;
}


function pddpreviousnext(){
	global $previousid,$nextid,$usecsslayout;
	if($previousid!='' || $nextid!=''){
		print (@$usecsslayout ? '<div class="previousnext">' : '</p><p class="pagenums" align="center">');
		writepreviousnextlinks();
		print (@$usecsslayout ? '</div>' : '<br />');
	}
}
function pddemailfriend(){
	global $usecsslayout,$longdesc,$rs,$usedetailbodyformat,$useemailfriend,$emailfriendlink;
	if(@$usedetailbodyformat==3 && @$useemailfriend) print '<br />' . $emailfriendlink;
	if(@$usedetailbodyformat==4 && @$useemailfriend) print '<div class="emailfriend">' . $emailfriendlink . '</div>';
	if(! @$usecsslayout) print '</p><hr width="80%" class="detailhr detailhrbottom" />';
	if(@$usedetailbodyformat==2 && @$useemailfriend) print '<p align="center">' . $emailfriendlink . '</p>';
	if(! @$usecsslayout) print '</td></tr>';
	if(@$usedetailbodyformat==2 || @$usedetailbodyformat==4){
	}elseif($longdesc!='')
		print (! @$usecsslayout ? '<tr><td colspan="3" class="detaildescription">' : '') . '<div class="detaildescription"' . displaytabs($longdesc) . '</div>' . (! @$usecsslayout ? '</td></tr>' : '');
	elseif(trim($rs[getlangid('pDescription',2)])!='')
		print (! @$usecsslayout ? '<tr><td colspan="3" class="detaildescription">' : '') . '<div class="detaildescription" itemprop="description">' . $rs[getlangid('pDescription',2)] . '</div>' . (! @$usecsslayout ? '</td></tr>' : '');
	if(! @$usecsslayout) print '</table>';
}
$alreadygotadmin=getadminsettings();
$thesessionid=getsessionid();
$hasextracurrency=FALSE;
foreach($customlayoutarray as $layoutoption){
	if($layoutoption=='currency') $hasextracurrency=TRUE;
}
if($hasextracurrency)
	checkCurrencyRates($currConvUser,$currConvPw,$currLastUpdate,$currRate1,$currSymbol1,$currRate2,$currSymbol2,$currRate3,$currSymbol3);
else{
	$currRate1=0; $currRate2=0; $currRate3=0;
}
$disabledsection=FALSE;
$psmallimage='';
$allimages='';
$numallimages=0;
$allgiantimages='';
$numallgiantimages=0;
$sSQL='SELECT pId,pSKU,'.getlangid('pName',1).','.getlangid('pDescription',2).','.$WSP.'pPrice,pSection,pListPrice,pSell,pStockByOpts,pStaticPage,pStaticURL,pInStock,pBackOrder,pExemptions,'.(@$detailslink!='' ? "'' AS " : '').'pTax,pTotRating,pNumRatings,pOrder,pDateAdded,pSearchParams,pCustom1,pCustom2,pCustom3,'.(@$manufacturerfield!=''?getlangid('scName',131072).',':'').getlangid('pLongDescription',4).' FROM products '.(@$manufacturerfield!=''?'LEFT OUTER JOIN searchcriteria on products.pManufacturer=searchcriteria.scID ':'').'WHERE pDisplay<>0 AND (' . (@$usepnamefordetaillinks && trim(@$explicitid)==''?getlangid('pName',1):'pId') . "='" . escape_string($prodid) . "'" . (@$seodetailurls?" OR pStaticURL='".escape_string($prodid)."'":'') . ')';
$result=ect_query($sSQL) or ect_error();
$productindb=ect_num_rows($result)>0;
if($productindb){
	$origprodid=$prodid;
	$rs=ect_fetch_assoc($result);
	$longdesc=trim($rs[getlangid('pLongDescription',4)]);
	$prodid=$rs['pId'];
	$sectionid=$rs['pSection'];
	$sSQL="SELECT sectionDisabled,topSection FROM sections WHERE sectionID=" . $sectionid;
	$result2=ect_query($sSQL) or ect_error();
	if($rs2=ect_fetch_assoc($result2)){
		if($rs2['sectionDisabled']>$minloglevel) $disabledsection=TRUE;
	}
	ect_free_result($result2);
}
$prodlist="'" . escape_string($prodid) . "'";
$emailfriendlink='';
if(@$useemailfriend) $emailfriendlink='<a class="ectlink emailfriend" rel="nofollow" href="javascript:openEFWindow(\''.urlencode($prodid).'\',false)"><strong>'.$GLOBALS['xxEmFrnd'].'</strong></a>';
if(@$emailfriendseparator=='') $emailfriendseparator=(@$usedetailbodyformat==1 || @$usedetailbodyformat=='' ? '<br /><hr class="efseparator" />' : ' | ');
if(@$useaskaquestion) $emailfriendlink.=($emailfriendlink==''?'':$emailfriendseparator) . '<a class="ectlink emailfriend" rel="nofollow" href="javascript:openEFWindow(\''.urlencode($prodid).'\',true)"><strong>'.$GLOBALS['xxAskQue'].'</strong></a>';
$useemailfriend=@$useemailfriend || @$useaskaquestion;
$sSQL="SELECT imageSrc FROM productimages WHERE imageType=0 AND imageProduct='" . escape_string($prodid) . "' ORDER BY imageNumber LIMIT 0,1";
$result2=ect_query($sSQL) or ect_error();
if($rs2=ect_fetch_assoc($result2)) $psmallimage=$rs2['imageSrc'];
ect_free_result($result2);
$sSQL="SELECT imageSrc FROM productimages WHERE imageType=1 AND imageProduct='" . escape_string($prodid) . "' ORDER BY imageNumber";
$result2=ect_query($sSQL) or ect_error();
while($rs2=ect_fetch_assoc($result2)) $allimages[$numallimages++]=$rs2;
ect_free_result($result2);
$sSQL="SELECT imageSrc FROM productimages WHERE imageType=2 AND imageProduct='" . escape_string($prodid) . "' ORDER BY imageNumber";
$result2=ect_query($sSQL) or ect_error();
while($rs2=ect_fetch_assoc($result2)) $allgiantimages[$numallgiantimages++]=$rs2;
ect_free_result($result2);
if((! $productindb && $prodid!=$giftcertificateid && $prodid!=$donationid) || $disabledsection){
	print '<p align="center">&nbsp;<br />'.$GLOBALS['xxSryNA'].'<br />&nbsp;</p>';
	if(@$usepnamefordetaillinks&&getget('prod')!=''){
		$sSQL='SELECT '.getlangid('pName',1).",pStaticPage,pStaticURL FROM products WHERE pID='".escape_string(getget('prod'))."'";
		$result2=ect_query($sSQL) or ect_error();
		if($rs2=ect_fetch_assoc($result2)){
			$addand=$newqs='';
			foreach($_GET as $key=>$val){
				if($key!='prod'){ $newqs.=$addand.$key.'='.urlencode($val); $addand='&'; }
			}
			ob_end_clean();
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: '.getfullurl(getdetailsurl(getget('prod'),$rs2['pStaticPage'],$rs2[getlangid('pName',1)],$rs2['pStaticURL'],$newqs,@$GLOBALS['pathtohere'])));
			ect_free_result($result2);
			exit;
		}
		ect_free_result($result2);
	}
	header('HTTP/1.1 404 Not Found');
}else{
	$prodoptions='';
	if($prodid!=$giftcertificateid && $prodid!=$donationid){
		if(getget('prod')!='' && @$seodetailurls && @$seourlsthrow301 && (@$_SERVER['REDIRECT_URL']=='' || ($origprodid!=trim($rs['pStaticURL']) && trim($rs['pStaticURL'])!=''))){
			$addand=$newqs='';
			foreach($_GET as $key=>$val){
				if($key!='prod'){ $newqs.=$addand.$key.'='.urlencode($val); $addand='&'; }
			}
			ob_end_clean();
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: '.getfullurl(getdetailsurl($rs['pId'],$rs['pStaticPage'],$rs[getlangid('pName',1)],$rs['pStaticURL'],$newqs,@$GLOBALS['pathtohere'])));
			exit;
		}
		if(getget('prod')!='' && $rs['pStaticPage']!=0 && @$redirecttostatic==TRUE){
			ob_end_clean();
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: '.getfullurl(cleanforurl($rs[getlangid('pName',1)]) . '.php'));
			exit;
		}
		$hasstaticpage=($rs['pStaticPage']!=0);
		$catid=$rs['pSection'];
		if(getget('cat')!='' && is_numeric(getget('cat')) && getget('cat')!='0') $catid=getget('cat');
		if(getget('cat')!='' && is_numeric(getget('cat')) && getget('cat')!='0') $thecatid=getget('cat');
		$thetopts=$catid;
		$topsectionids=$catid;
		$isrootsection=FALSE;
		for($index=0; $index <= 10; $index++){
			if($thetopts==$catalogroot){
				$caturl=$GLOBALS['xxHomeURL'];
				if($catalogroot!=0){
					$sSQL='SELECT sectionID,topSection,'.getlangid('sectionName',256).',rootSection,sectionDisabled,'.getlangid('sectionurl',2048)." AS sectionurl FROM sections WHERE sectionID='" . $catalogroot . "'";
					$result2=ect_query($sSQL) or ect_error();
					if($rs2=ect_fetch_assoc($result2)){
						$GLOBALS['xxHome']=$rs2[getlangid('sectionName',256)];
						if(trim($rs2['sectionurl'])!='') $caturl=$rs2['sectionurl'];
					}
					ect_free_result($result2);
				}
				$tslist='<a class="ectlink" href="'.$caturl.'">' . $GLOBALS['xxHome'] . '</a> ' . $tslist;
				break;
			}elseif($index==10){
				$tslist='<strong>Loop</strong>' . $tslist;
			}else{
				$sSQL='SELECT sectionID,topSection,'.getlangid('sectionName',256).',rootSection,'.getlangid('sectionurl',2048).' AS sectionurl FROM sections WHERE sectionID=' . $thetopts;
				$result2=ect_query($sSQL) or ect_error();
				if(ect_num_rows($result2) > 0){
					$rs2=ect_fetch_assoc($result2);
					if($rs2['sectionurl']!='')
						$tslist=' &raquo; <a class="ectlink" href="' . getcatid($rs2['sectionurl'],@$seocategoryurls?$rs2['sectionurl']:'',$rs2['rootSection']==1?$seoprodurlpattern:$seocaturlpattern) . '">' . $rs2[getlangid('sectionName',256)] . '</a>' . $tslist;
					elseif($rs2['rootSection']==1)
						$tslist=' &raquo; <a class="ectlink" href="' . (!@$seocategoryurls?'products.php?cat=':'') . getcatid($rs2['sectionID'],$rs2[getlangid('sectionName',256)],$seoprodurlpattern) . '">' . $rs2[getlangid('sectionName',256)] . '</a>' . $tslist;
					else
						$tslist=' &raquo; <a class="ectlink" href="' . (!@$seocategoryurls?'categories.php?cat=':'') . getcatid($rs2['sectionID'],$rs2[getlangid('sectionName',256)],$seocaturlpattern) . '">' . $rs2[getlangid('sectionName',256)] . '</a>' . $tslist;
					$thetopts=$rs2['topSection'];
					$topsectionids.=',' . $thetopts;
				}else{
					$tslist='Top Section Deleted' . $tslist;
					break;
				}
				ect_free_result($result2);
			}
		}
		$nextid='';
		$previousid='';
		$sectionids=getsectionids($catid, FALSE);
		if(@$_SESSION['sortby']!='') $dosortby=$_SESSION['sortby'];
		if(@$dosortby==2 || @$dosortby==12 || @$dosortby==5)
			$sSortBy='';
		elseif(@$dosortby==3 || @$dosortby==4){
			$sSortBy=$TWSP;
			$sSortValue=$rs['pPrice'];
		}elseif(@$dosortby==6 || @$dosortby==7){
			$sSortBy='pOrder';
			$sSortValue=$rs['pOrder'];
		}elseif(@$dosortby==8 || @$dosortby==9){
			$sSortBy='pDateAdded';
			$sSortValue="'".$rs['pDateAdded']."'";
		}else{
			$sSortBy=getlangid('pName',1);
			$sSortValue="'".escape_string($rs[getlangid('pName',1)])."'";
		}
		if(@$dosortby==4 || @$dosortby==7 || @$dosortby==9 || @$dosortby==11 || @$dosortby==12) $isdesc=TRUE; else $isdesc=FALSE;
		if(@$nopreviousnextlinks!=TRUE){
			$sSQL='SELECT products.pId,'.getlangid('pName',1).',pStaticPage,pStaticURL,products.pSection FROM products LEFT JOIN multisections ON products.pId=multisections.pId WHERE (products.pSection IN (' . $sectionids . ') OR multisections.pSection IN (' . $sectionids . '))' . (($useStockManagement && @$noshowoutofstock==TRUE) ? ' AND (pInStock>0 OR pStockByOpts<>0)':'') . ' AND pDisplay<>0 AND (' . ($sSortBy!='' ? '(('.$sSortBy.'='.$sSortValue." AND products.pId>'" . escape_string($prodid) . "') OR " . $sSortBy . ($isdesc?'<':'>') . $sSortValue . ')' : 'products.pId'.($isdesc?'<':'>')."'" . escape_string($prodid) . "'") . ") AND products.pId NOT IN ('".escape_string($giftcertificateid)."','".escape_string($donationid)."') ORDER BY " . ($sSortBy!='' ? $sSortBy . ($isdesc?' DESC,':' ASC,'):'') . 'products.pId ASC LIMIT 1';
			$result2=ect_query($sSQL) or ect_error();
			if($rs2=ect_fetch_assoc($result2)){
				$nextid=@$usepnamefordetaillinks?str_replace(' ',@$detlinkspacechar,$rs2[getlangid('pName',1)]):$rs2['pId'];
				$nextidname=$rs2[getlangid('pName',1)];
				$nextidstatic=$rs2['pStaticPage'];
				$nextstaticurl=$rs2['pStaticURL'];
				$nextidcat=$rs2['pSection'];
			}
			ect_free_result($result2);
			$sSQL='SELECT products.pId,'.getlangid('pName',1).',pStaticPage,pStaticURL,products.pSection FROM products LEFT JOIN multisections ON products.pId=multisections.pId WHERE (products.pSection IN (' . $sectionids . ') OR multisections.pSection IN (' . $sectionids . '))' . (($useStockManagement && @$noshowoutofstock==TRUE) ? ' AND (pInStock>0 OR pStockByOpts<>0)':'') . ' AND pDisplay<>0 AND (' . ($sSortBy!='' ? '(('.$sSortBy.'='.$sSortValue." AND products.pId<'" . escape_string($prodid) . "') OR " . $sSortBy . ($isdesc?'>':'<') . $sSortValue . ')' : 'products.pId'.($isdesc?'>':'<')."'" . escape_string($prodid) . "'") . ") AND products.pId NOT IN ('".escape_string($giftcertificateid)."','".escape_string($donationid)."') ORDER BY " . ($sSortBy!='' ? $sSortBy . ($isdesc?' ASC,':' DESC,'):'') . 'products.pId DESC LIMIT 1';
			$result2=ect_query($sSQL) or ect_error();
			if($rs2=ect_fetch_assoc($result2)){
				$previousid=@$usepnamefordetaillinks?str_replace(' ',@$detlinkspacechar,$rs2[getlangid('pName',1)]):$rs2['pId'];
				$previousidname=$rs2[getlangid('pName',1)];
				$previousidstatic=$rs2['pStaticPage'];
				$previousstaticurl=$rs2['pStaticURL'];
				$previousidcat=$rs2['pSection'];
			}
			ect_free_result($result2);
		}
		$extraimages=0;
		if(@$currencyseparator=='') $currencyseparator=' ';
		productdisplayscript(TRUE,TRUE);
		if(@$perproducttaxrate==TRUE && ! is_null($rs['pTax'])) $thetax=$rs['pTax']; else $thetax=$countryTaxRate;
		updatepricescript();
		if(@$inlinepopups==TRUE) emailfriendjavascript(); ?>
<script type="text/javascript">
/* <![CDATA[ */<?php
$liscript='';
if($numallgiantimages>1){
	$liscript.='pIX[999]=0;pIM[999]="';
	for($index=0;$index<$numallgiantimages;$index++)
		$liscript.=encodeimage($allgiantimages[$index]['imageSrc']).'*';
	$liscript.='";';
}
if(@$GLOBALS['giantimageinpopup']==TRUE){
	$liscript='var pIM=new Array();var pIX=new Array();' . $liscript . 'function updateprodimage(theitem,isnext){var imlist=pIM[theitem].split("*");if(isnext) pIX[theitem]++; else pIX[theitem]--;if(pIX[theitem]<0) pIX[theitem]=imlist.length-2;if(pIX[theitem]>imlist.length-2) pIX[theitem]=0;document.getElementById("prodimage"+theitem).onload=function(){doresize(document.getElementById("prodimage"+theitem));};document.getElementById("prodimage"+theitem).src=vsdecimg(imlist[pIX[theitem]]);document.getElementById("extraimcnt"+theitem).innerHTML=pIX[theitem]+1;return false;};';
}else
	print $liscript."\r\n";
?>
function showgiantimage(imgname){
<?php
	if(@$GLOBALS['giantimageinpopup']==TRUE){
		if(@$giantimagepopupwidth=='') $giantimagepopupwidth=450;
		if(@$giantimagepopupheight=='') $giantimagepopupheight=600;
		print 'var winwid='.$giantimagepopupwidth.';var winhei='.$giantimagepopupheight.";\r\n"; ?>
scrwid=screen.width; scrhei=screen.height;
var newwin=window.open("","popupimage",'menubar=no,scrollbars=no,width='+winwid+',height='+winhei+',left='+((scrwid-winwid)/2)+',top=100,directories=no,location=no,resizable=yes,status=yes,toolbar=no');
newwin.document.open();
newwin.document.write('<html><head><title>Image PopUp</title><style type="text/css">body { margin:0px;font-family:Tahoma; }</style><' + 'script type="text/javascript">function vsdecimg(timg){return decodeURIComponent(timg<?php print @$GLOBALS['noencodeimages']?'':'.replace("|","prodimages/").replace("<",".gif").replace(">",".jpg").replace("?",".png")'?>)}function doresize(tim){window.moveTo(('+scrwid+'-(tim.width+44))/2,Math.max(('+scrhei+'-30)-(tim.height+130),0)/2);window.resizeTo(tim.width+44,tim.height+130);};<?php print str_replace(array('\\',"'"),array('\\\\',"\\\\'"),$liscript)?><' + '/script></head><body onload="doresize(document.getElementById(\'prodimage999\'))" >');
newwin.document.write('<p align="center"><table border="0" cellspacing="1" cellpadding="1" align="center">');
<?php	if($numallgiantimages>1){ ?>
newwin.document.write('<tr><td align="center" colspan="3"><img src="images/leftimage.gif" onclick="return updateprodimage(\'999\', false);" onmouseover="this.style.cursor=\'pointer\'" alt="<?php print $GLOBALS['xxPrev']?>" style="vertical-align:middle;margin:0px;" /> <span id="extraimcnt999">1</span> <?php print $GLOBALS['xxOf'].' '.$numallgiantimages?> <img src="images/rightimage.gif" onclick="return updateprodimage(\'999\', true);" onmouseover="this.style.cursor=\'pointer\'" alt="<?php print $GLOBALS['xxNext']?>" style="vertical-align:middle;margin:0px;" /></td></tr>');
<?php	}else{ ?>
newwin.document.write('<tr><td align="center" colspan="3">&nbsp;</td></tr>');
<?php	}
		if($numallgiantimages>0){ ?>
newwin.document.write('<tr><td align="center" colspan="3"><img id="prodimage999" class="giantimage prodimage" src="<?php print $allgiantimages[0]['imageSrc']?>" alt="<?php print str_replace(array("'",'"'), array("\\'",'&quot;'), strip_tags($rs[getlangid('pName',1)]))?>" <?php if($numallgiantimages>1) print 'onclick="return updateprodimage(\\\'999\\\', true);" onmouseover="this.style.cursor=\\\'pointer\\\'"'; ?> /></td></tr>');
<?php	}
		if($numallgiantimages>1){ ?>
newwin.document.write('<tr><td align="left"><img src="images/leftimage.gif" onclick="return updateprodimage(\'999\', false);" onmouseover="this.style.cursor=\'pointer\'" alt="<?php print $GLOBALS['xxPrev']?>" style="vertical-align:middle;margin:0px;" /></td><td align="center">&nbsp;</td><td align="right"><img src="images/rightimage.gif" onclick="return updateprodimage(\'999\', true);" onmouseover="this.style.cursor=\'pointer\'" alt="<?php print $GLOBALS['xxNext']?>" style="vertical-align:middle;margin:0px;" /></td></tr>');
<?php	} ?>
newwin.document.write('</table></p></body></html>');
newwin.document.close();
newwin.focus();
<?php
	}else{ ?>
document.getElementById('giantimgspan').style.display='';
document.getElementById('mainbodyspan').style.display='none';
document.getElementById('prodimage999').src=imgname;
<?php
	} ?>
}
function hidegiantimage(){
document.getElementById('giantimgspan').style.display='none';
document.getElementById('mainbodyspan').style.display='';
return(false);
}
/* ]]> */
</script>
<div id="giantimgspan" style="width:98%;text-align:center;display:none">
	<div><span class="giantimgname detailname"><?php print $rs[getlangid('pName',1)] . ' </span> <span class="giantimgback">(<a class="ectlink" href="' . detailpageurl($thecatid!='' ? 'cat='.$thecatid : '') . '" onclick="return hidegiantimage();" >' . $GLOBALS['xxRvBack'] . '</a>)</span>'; ?></div>
	<div class="giantimg" style="margin:0 auto;display:inline-block">
<?php	if($numallgiantimages>1){ ?>
		<div style="text-align:center"><img src="images/leftimage.gif" onclick="return updateprodimage('999', false);" onmouseover="this.style.cursor='pointer'" alt="<?php print $GLOBALS['xxPrev']?>" style="vertical-align:middle;margin:0px;" /> <span class="extraimage extraimagenum" id="extraimcnt999">1</span> <span class="extraimage"><?php print $GLOBALS['xxOf'] . ' ' . $numallgiantimages?></span> <img src="images/rightimage.gif" onclick="return updateprodimage('999', true);" onmouseover="this.style.cursor='pointer'" alt="<?php print $GLOBALS['xxNext']?>" style="vertical-align:middle;margin:0px;" /></div>
<?php	} ?>
		<div style="text-align:center"><img id="prodimage999" class="giantimage prodimage" src="" alt="<?php print str_replace('"', '&quot;', strip_tags($rs[getlangid('pName',1)]))?>" <?php if($numallgiantimages>1) print 'onclick="return updateprodimage(\'999\', true);" onmouseover="this.style.cursor=\'pointer\'"'; ?> style="margin:0px;" /></div>
<?php	if($numallgiantimages>1){ ?>
		<div><img src="images/leftimage.gif" onclick="return updateprodimage('999', false);" onmouseover="this.style.cursor='pointer'" alt="<?php print $GLOBALS['xxPrev']?>" style="vertical-align:middle;margin:0px;float:left" /><img src="images/rightimage.gif" onclick="return updateprodimage('999', true);" onmouseover="this.style.cursor='pointer'" alt="<?php print $GLOBALS['xxNext']?>" style="vertical-align:middle;margin:0px;float:right" /></div>
<?php	} ?>
	</div>
</div>
<?php
	}else{
		$proddetailtopbuybutton=FALSE;
	}
	$optionshavestock=TRUE;
	$optjs='';
	if(is_array($prodoptions) && @$_REQUEST['review']==''){
		if(@$usedetailbodyformat==1 || @$usedetailbodyformat=='')
			$optionshtml=displayproductoptions('<strong><span class="detailoption">','</span></strong>',$optdiff,$thetax,TRUE,$hasmultipurchase,$optjs);
		else
			$optionshtml=displayproductoptions('<span class="detailoption">','</span>',$optdiff,$thetax,TRUE,$hasmultipurchase,$optjs);
	}
	if($prodid==$giftcertificateid || $prodid==$donationid){
		$isinstock=TRUE; $isbackorder=FALSE;
	}else{
		if($useStockManagement)
			if($rs['pStockByOpts']!=0) $isinstock=$optionshavestock; else $isinstock=((int)($rs['pInStock']) > 0);
		else
			$isinstock=($rs['pSell']!=0);
		$isbackorder=! $isinstock && $rs['pBackOrder']!=0;
	}
	$theuagent=strtolower(@$_SERVER['HTTP_USER_AGENT']);
	if(@$recentlyviewed==TRUE){
		if(strpos($theuagent,'bingbot')!==FALSE || strpos($theuagent,'crawler')!==FALSE || strpos($theuagent,'exabot')!==FALSE || strpos($theuagent,'ezooms')!==FALSE ||
			strpos($theuagent,'googlebot')!==FALSE || strpos($theuagent,'gulliver')!==FALSE || strpos($theuagent,'ia_archiver')!==FALSE || strpos($theuagent,'infoseek')!==FALSE ||
			strpos($theuagent,'inktomi')!==FALSE || strpos($theuagent,'mj12bot')!==FALSE || strpos($theuagent,'scooter')!==FALSE || strpos($theuagent,'speedy spider')!==FALSE || strpos($theuagent,'yahoo!')!==FALSE ||
			strpos($theuagent,'yandexbot')!==FALSE)
			$recentlyviewed=FALSE;
	}
	if(@$recentlyviewed==TRUE && ! ($prodid==$giftcertificateid || $prodid==$donationid)){
		$tcnt=NULL;
		if(@$numrecentlyviewed=='') $numrecentlyviewed=6;
		$sSQL="DELETE FROM recentlyviewed WHERE rvDate<'".date('Y-m-d', time()-(60*60*24*3))."'";
		ect_query($sSQL) or ect_error();
		$sSQL="SELECT rvID FROM recentlyviewed WHERE rvProdID='".escape_string($prodid)."' AND " . (@$_SESSION['clientID']!='' ? 'rvCustomerID=' . escape_string(@$_SESSION['clientID']) : "(rvCustomerID=0 AND rvSessionID='".$thesessionid."')");
		$result2=ect_query($sSQL) or ect_error();
		if(! ($rs2=ect_fetch_assoc($result2))){
			$sSQL="INSERT INTO recentlyviewed (rvProdID,rvProdName,rvProdSection,rvProdURL,rvSessionID,rvCustomerID,rvDate) VALUES ('".escape_string($prodid)."','".escape_string($rs[getlangid('pName',1)])."',".(@$catid!=''?$catid:'0').",'".escape_string(detailpageurl((@$thecatid!=''?'cat='.$thecatid:'')))."','".$thesessionid."',".(@$_SESSION['clientID']!=''?$_SESSION['clientID']:0).",'".date('Y-m-d H:i:s')."')";
			ect_query($sSQL) or ect_error();
		}else{
			$sSQL="UPDATE recentlyviewed SET rvDate='".date('Y-m-d H:i:s')."' WHERE rvID=".$rs2['rvID'];
			ect_query($sSQL) or ect_error();
		}
		ect_free_result($result2);
		$sSQL='SELECT COUNT(*) AS tcnt FROM recentlyviewed WHERE ' . (@$_SESSION['clientID']!='' ? 'rvCustomerID=' . escape_string(@$_SESSION['clientID']) : "(rvCustomerID=0 AND rvSessionID='".$thesessionid."')");
		$result2=ect_query($sSQL) or ect_error();
		if($rs2=ect_fetch_assoc($result2)) $tcnt=$rs2['tcnt'];
		ect_free_result($result2);
		if(!is_null($tcnt)){
			if($tcnt>$numrecentlyviewed){
				$sSQL='SELECT rvID,MIN(rvDate) FROM recentlyviewed WHERE ' . (@$_SESSION['clientID']!='' ? 'rvCustomerID=' . escape_string(@$_SESSION['clientID']) : "(rvCustomerID=0 AND rvSessionID='".$thesessionid."')").' GROUP BY rvID';
				$result2=ect_query($sSQL) or ect_error();
				if($rs2=ect_fetch_assoc($result2)){
					ect_query('DELETE FROM recentlyviewed WHERE rvID='.$rs2['rvID']) or ect_error();
				}
				ect_free_result($result2);
			}
		}
	}
	if(@$usecsslayout) print '<div id="mainbodyspan" class="proddetail" itemscope itemtype="http://schema.org/Product">'; else print '<table id="mainbodyspan" border="0" cellspacing="0" cellpadding="0" width="100%" align="center" itemscope itemtype="http://schema.org/Product"><tr><td width="100%">';
	if(getget('review')!='true') print '<form method="post" name="tForm' . $Count . '" id="ectform0" action="' . ($prodid==$giftcertificateid || $prodid==$donationid ? str_replace('"','',strip_tags(@$_SERVER['REQUEST_URI'])) : 'cart.php') . '" onsubmit="return formvalidator' . $Count . '(this)" style="margin:0px;padding:0px;">';
	if(! $hascustomlayout && ! (@isset($showcategories) && @$showcategories==FALSE)){
		pddprodnavigation();
		pddcheckoutbutton();
	}
	$alldiscounts='';
	if(@$nowholesalediscounts==TRUE && @$_SESSION["clientUser"]!='')
		if((($_SESSION["clientActions"] & 8)==8) || (($_SESSION["clientActions"] & 16)==16)) $noshowdiscounts=TRUE;
	if(@$noshowdiscounts!=TRUE && $prodid!=$giftcertificateid && $prodid!=$donationid){
		$sSQL="SELECT DISTINCT ".getlangid("cpnName",1024)." FROM coupons LEFT OUTER JOIN cpnassign ON coupons.cpnID=cpnassign.cpaCpnID WHERE cpnNumAvail>0 AND cpnEndDate>='" . date("Y-m-d",time()) ."' AND cpnIsCoupon=0 AND " .
			"((cpnSitewide=1 OR cpnSitewide=2) OR (cpnSitewide=0 AND cpaType=2 AND cpaAssignment='" . $rs['pId'] . "') " .
			"OR ((cpnSitewide=0 OR cpnSitewide=3) AND cpaType=1 AND cpaAssignment IN ('" . str_replace(',',"','",$topsectionids) . "')))" .
			' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))';
		if(($rs['pExemptions'] & 16)==16) $sSQL.=' AND cpnType<>0';
		$result2=ect_query($sSQL) or ect_error();
		while($rs2=ect_fetch_assoc($result2))
			$alldiscounts.=$rs2[getlangid("cpnName",1024)] . "<br />";
		ect_free_result($result2);
	}
	if(@$enablecustomerratings==TRUE && getpost('review')=='true'){
		$hitlimit=FALSE;
		print '<table border="0" cellspacing="2" cellpadding="2" width="100%" align="center">';
		$sSQL="SELECT COUNT(*) as thecount FROM ratings WHERE rtDate='" . date('Y-m-d', time()) . "' AND rtIPAddress='" . escape_string(getipaddress()) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs2=ect_fetch_assoc($result)){
			if(@$dailyratinglimit=='') $dailyratinglimit=10;
			if(! is_null($rs2['thecount'])){
				if($rs2['thecount']>$dailyratinglimit) $hitlimit=TRUE;
			}
		}
		ect_free_result($result);
		$theip=@$_SERVER['REMOTE_ADDR'];
		if($theip=='') $theip='none';
		if($theip=='none' || ip2long($theip)==FALSE)
			$sSQL='SELECT dcid FROM ipblocking LIMIT 0,1';
		else
			$sSQL='SELECT dcid FROM ipblocking WHERE (dcip1=' . ip2long($theip) . ' AND dcip2=0) OR (dcip1 <= ' . ip2long($theip) . ' AND ' . ip2long($theip) . '<=dcip2 AND dcip2<>0)';
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result) > 0)
			$hitlimit=TRUE;
		$referer=@$_SERVER['HTTP_REFERER'];
		$host=@$_SERVER['HTTP_HOST'];
		if(strpos($referer, $host)===FALSE){
			print '<tr><td align="center">Sorry but your review could not be sent at this time.</td></tr>';
		}elseif($hitlimit)
			print '<tr><td>'.$GLOBALS['xxRvLim'].'</td></tr>';
		elseif(@$onlyclientratings && @$_SESSION['clientID']=='')
			print '<tr><td align="center">Only logged in customers can review products.</td></tr>';
		elseif(is_numeric(getpost('ratingstars')) && is_numeric(getpost('rfsectgrp1')) && is_numeric(getpost('rfsectgrp2')) && getpost('reviewposter')!='' && getpost('reviewheading')!=''){
			if((int)getpost('ratingstars')==(int)getpost('rfsectgrp1') && (int)getpost('rfsectgrp2')==strlen(@$_POST['reviewposter'])){
				$sSQL='INSERT INTO ratings (rtProdID,rtRating,rtPosterName,rtHeader,rtIPAddress,rtApproved,rtLanguage,rtDate,rtPosterLoginID,rtComments) VALUES ('
					. "'" . escape_string(strip_tags($prodid)) . "','" . (is_numeric(getpost('ratingstars')) ? escape_string((int)getpost('ratingstars') * 2) : 0) . "','" . escape_string(strip_tags(getpost('reviewposter'))) . "','" . escape_string(strip_tags(getpost('reviewheading'))) . "','" . escape_string(strip_tags(getipaddress())) . "',0,";
				if(@$languageid!='') $sSQL.=((int)$languageid-1).','; else $sSQL.='0,';
				$sSQL.="'" . date('Y-m-d', time()) . "'," . (@$_SESSION['clientID']!='' ? @$_SESSION['clientID'] : 0) . ",'" . escape_string(strip_tags(getpost('reviewcomments'))) . "')";
				ect_query($sSQL) or ect_error();
				if(($GLOBALS['adminEmailConfirm'] & 8)==8){
					$emailmessage='There has been a new customer review at your store: ' . $emlNl .
						'Product ID: ' . strip_tags($prodid) . $emlNl .
						'Rating: ' . strip_tags(getpost('ratingstars')) . $emlNl .
						'Poster: ' . strip_tags(getpost('reviewposter')) . $emlNl .
						'IP: ' . strip_tags($GLOBALS['REMOTE_ADDR']) . $emlNl .
						'Heading: ' . strip_tags(getpost('reviewheading')) . $emlNl .
						'Comments: ' . strip_tags(getpost('reviewcomments')) . $emlNl;
					dosendemail($emailAddr,$emailAddr,'','New Customer Review',$emailmessage);
				

				}
			}else
				$xxRvThks='Error, I\'m sorry but your review could not be recorded at this time.';
			print '<tr><td align="center">&nbsp;<br />&nbsp;<br />'.$GLOBALS['xxRvThks'].'<br />&nbsp;<br />&nbsp;';
			print $GLOBALS['xxRvRet'].' <a class="ectlink" href="' . detailpageurl($thecatid!='' ? 'cat='.$thecatid : '') . '">' . $GLOBALS['xxClkHere'] . '</a>';
			print '<br />&nbsp;<br />&nbsp;';
			print '<meta http-equiv="Refresh" content="3; URL=' . detailpageurl($thecatid!='' ? 'cat='.$thecatid : '') . '">';
			print '</td></tr>';
		}
		print '</table>';
	}elseif(@$enablecustomerratings==TRUE && getget('review')=='all'){
		print (@$usecsslayout ? '<div class="reviews">' : '<table border="0" cellspacing="2" cellpadding="2" width="100%" align="center"><tr><td>');
		if($psmallimage!='')
			print '<img align="middle" id="prodimage0" class="prodimage detailreviewimage" src="'.str_replace('%s','',$psmallimage).'" alt="'.strip_tags($rs[getlangid('pName',1)]).'" />&nbsp;';
		print '<span class="review reviewsforprod">'.$GLOBALS['xxRvRevP'].' - </span><span class="review reviewprod" itemprop="name">' . $rs[getlangid('pName',1)] . '</span> <span class="review reviewback">(<a class="ectlink" href="' . detailpageurl($thecatid!='' ? 'cat='.$thecatid : '') . '">' . $GLOBALS['xxRvBack'] . '</a>)</span><br />&nbsp;</td></tr>';
		$sSQL="SELECT rtID,rtRating,rtPosterName,rtHeader,rtDate,rtComments FROM ratings WHERE rtApproved<>0 AND rtProdID='" . escape_string($prodid) . "'";
		if(@$ratingslanguages!='') $sSQL.=' AND rtLanguage+1 IN ('.$ratingslanguages.')'; elseif(@$languageid!='') $sSQL.=' AND rtLanguage='.((int)$languageid-1); else $sSQL.=' AND rtLanguage=0';
		if(getget('ro')=='1')
			$sSQL.=' ORDER BY rtRating DESC';
		elseif(getget('ro')=='2')
			$sSQL.=' ORDER BY rtRating';
		elseif(getget('ro')=='3')
			$sSQL.=' ORDER BY rtDate';
		else
			$sSQL.=' ORDER BY rtDate DESC';
		print showreviews($sSQL,TRUE);
		print (@$usecsslayout ? '</div>' : '</table>');
	}elseif(@$enablecustomerratings==TRUE && getget('review')=='true'){
		print (@$usecsslayout ? '<div class="review reviewprod">' : '<table border="0" cellspacing="2" cellpadding="2" width="100%" align="center"><tr><td>');
		print '<span class="review reviewing">'.$GLOBALS['xxRvAreR'].' - </span><span class="review reviewprod" itemprop="name">' . $rs[getlangid('pName',1)] . '</span> <span class="review reviewback">(<a class="ectlink" href="' . detailpageurl($thecatid!='' ? 'cat='.$thecatid : '') . '">' . $GLOBALS['xxRvBack'] . '</a>)</span>';
		print (@$usecsslayout ? '</div>' : '<br />&nbsp;</td></tr></table>');
	}elseif($prodid==$giftcertificateid || $prodid==$donationid){
		$isincluded=TRUE;
		include './vsadmin/inc/incspecials.php';
	}elseif(@$usedetailbodyformat==1 || @$usedetailbodyformat==''){ ?>
			<table width="100%" border="0" cellspacing="3" cellpadding="3">
			  <tr> 
				<td width="100%" colspan="4" class="detail"> 
<?php	if(@$showproductid==TRUE) print '<div class="detailid"><strong>' . $GLOBALS['xxPrId'] . ':</strong> <span itemprop="productID">' . $rs['pId'] . '</span></div>';
		if(@$manufacturerfield!='' && ! is_null($rs[getlangid('scName',131072)])) print '<div class="prodmanufacturer detailmanufacturer"><strong>' . $manufacturerfield . ':</strong> <span itemprop="manufacturer">' . $rs[getlangid('scName',131072)] . '</span></div>';
		if(@$showproductsku!='' && $rs['pSKU']!='') print '<div class="detailsku"><strong>' . $showproductsku . ':</strong> <span itemprop="sku">' . $rs['pSKU'] . '</span></div>';
		print $sstrong . '<div class="detailname"><'.(@$detailnameh1?'h1':'span').' itemprop="name">' . $rs[getlangid('pName',1)] . '</'.(@$detailnameh1?'h1':'span').'>' . $GLOBALS['xxDot'];
		if($alldiscounts!='') print ' ' . (@$nomarkup?'':'<font color="#FF0000">') . '<span class="discountsapply detaildiscountsapply">' . $GLOBALS['xxDsApp'] . '</span>' . (@$nomarkup?'':'</font>') . '</div>' . $estrong . '<div class="detaildiscounts"' . (@$nomarkup?'':' style="font-size:11px;color:#FF0000;"') . '>' . $alldiscounts . '</div>'; else print '</div>' . $estrong;
		if($useStockManagement && @$showinstock==TRUE){ if((int)$rs["pStockByOpts"]==0) print '<div class="prodinstock detailinstock"><strong>' . $GLOBALS['xxInStoc'] . ':</strong> ' . max(0,$rs['pInStock']) . '</div>'; } ?>
				</td>
			  </tr>
			  <tr><td width="100%" colspan="4" align="center" class="detailimage"><?php showdetailimages(); ?></td></tr>
			  <tr> 
				<td width="100%" colspan="4" class="detaildescription"><?php
		$longdesc=trim($rs[getlangid("pLongDescription",4)]);
		if($longdesc!='')
			print '<div class="detaildescription"' . displaytabs($longdesc) . '</div>';
		elseif(trim($rs[getlangid("pDescription",2)])!='')
			print '<div class="detaildescription" itemprop="description">' . $rs[getlangid("pDescription",2)] . '</div>';
		else
			print '&nbsp;';
		print '&nbsp;<br />';
		if(is_array($prodoptions)){
			$rs['pPrice']+=$optdiff;
			if($optionshtml!='') print '<div class="detailoptions" align="center"><table class="prodoptions detailoptions" border="0" cellspacing="1" cellpadding="1">' . $optionshtml . '</table></div>';
		}
		displayformvalidator();
		if($optjs!='') print '<script type="text/javascript">/* <![CDATA[ */'.$optjs.'/* ]]> */</script>';
?>				</td>
			  </tr>
			  <tr>
				<td width="20%"><?php if(@$useemailfriend) print $emailfriendlink; else print '&nbsp;' ?></td>
				<td width="60%" align="center" colspan="2"><?php
		if(@$noprice==TRUE){
			print '&nbsp;';
		}else{
			if((double)$rs['pListPrice']!=0.0){ $plistprice=(@$GLOBALS['showtaxinclusive']===2 && ($rs['pExemptions'] & 2)!=2?$rs['pListPrice']+($rs['pListPrice']*$thetax/100.0):$rs['pListPrice']); print '<div class="detaillistprice">' . str_replace('%s', FormatEuroCurrency($plistprice), $GLOBALS['xxListPrice']) . (@$GLOBALS['yousavetext']!=''?str_replace('%s', FormatEuroCurrency($plistprice-(@$GLOBALS['showtaxinclusive']===2 && ($rs['pExemptions'] & 2)!=2 ? $rs['pPrice']+($rs['pPrice']*$thetax/100.0) : $rs['pPrice'])), $GLOBALS['yousavetext']):'') . '</div>';}
			print '<div class="detailprice" itemprop="offers" itemscope itemtype="http://schema.org/Offer"><meta itemprop="priceCurrency" content="'.$countryCurrency.'"><strong>' . $GLOBALS['xxPrice'].($GLOBALS['xxPrice']!=''?':':'') . '</strong> <span class="price" id="pricediv' . $Count . '"'.($rs['pPrice']!=0?' itemprop="price"':'').'>' . ($rs['pPrice']==0 && @$pricezeromessage!='' ? $pricezeromessage : FormatEuroCurrency(@$GLOBALS['showtaxinclusive']===2 && ($rs['pExemptions'] & 2)!=2 ? $rs['pPrice']+($rs['pPrice']*$thetax/100.0) : $rs['pPrice'])) . '</span> ';
			if(@$GLOBALS['showtaxinclusive']==1 && ($rs['pExemptions'] & 2)!=2) printf('<span id="taxmsg' . $Count . '"' . ($rs['pPrice']==0 ? ' style="display:none"' : '') . '>' . $ssIncTax . '</span>','<span id="pricedivti' . $Count . '">' . ($rs['pPrice']==0 ? '-' : FormatEuroCurrency($rs['pPrice']+($rs['pPrice']*$thetax/100.0))) . '</span> ');
			schemaconditionavail();
			print '</div>';
			$extracurr='';
			if($currRate1!=0 && $currSymbol1!='') $extracurr=str_replace('%s',number_format($rs['pPrice']*$currRate1,checkDPs($currSymbol1),$orcdecimals,$orcthousands),$currFormat1) . $currencyseparator;
			if($currRate2!=0 && $currSymbol2!='') $extracurr.=str_replace('%s',number_format($rs['pPrice']*$currRate2,checkDPs($currSymbol2),$orcdecimals,$orcthousands),$currFormat2) . $currencyseparator;
			if($currRate3!=0 && $currSymbol3!='') $extracurr.=str_replace('%s',number_format($rs['pPrice']*$currRate3,checkDPs($currSymbol3),$orcdecimals,$orcthousands),$currFormat3);
			if(@$GLOBALS['showquantitypricing']&&!@$hascustomlayout)
				print pddquantitypricing();
			if($extracurr!='') print '<div class="detailcurrency"><span class="extracurr" id="pricedivec' . $Count . '">' . ($rs['pPrice']==0 ? '' : $extracurr) . '</span></div>';
		} ?>
				</td><td width="20%" align="right">
<?php	if(@$nobuyorcheckout==TRUE)
			print '&nbsp;';
		else{
			if($rs['pPrice']==0 && @$nosellzeroprice==TRUE){
				print '&nbsp;';
			}elseif($isinstock || $isbackorder){
				writehiddenvar('id', $rs['pId']);
				writehiddenvar('mode', 'add');
				if($wishlistondetail) writehiddenvar('listid', '');
				if(@$showquantondetail && $hasmultipurchase==0) print '<table><tr><td align="center"><div class="quantitydiv" style="'.(@$quantityupdown?'margin-right:12px;':'').'white-space:nowrap"><input type="text" name="quant" id="w'.$Count.'quant" size="2" maxlength="5" value="1" title="'.$GLOBALS['xxQuant'].'" class="quantityinput" ' . (@$quantityupdown?'style="float:left" ':'') . '/>' . (@$quantityupdown?'<img src="images/quantarrowu.png" onclick="quantup('.$Count.',1)" alt="" /><br /><img src="images/quantarrowd.png" onclick="quantup('.$Count.',0)" alt="" />':'') . (@$showquantondetail && $hasmultipurchase==0 ? '</div></td><td align="center">' : '');
				if($isbackorder){
					if(@$usehardaddtocart) print imageorsubmit(@$imgbackorderbutton,$GLOBALS['xxBakOrd'],'buybutton backorder detailbuybutton detailbackorder'); else print imageorbutton(@$imgbackorderbutton,$GLOBALS['xxBakOrd'],'buybutton backorder detailbuybutton detailbackorder','ajaxaddcart('.$Count.')',TRUE);
				}else{
					if(@$custombuybutton!='')
						print $custombuybutton;
					else{
						if(@$usehardaddtocart) print imageorsubmit(@$imgbuybutton,$GLOBALS['xxAddToC'],'buybutton detailbuybutton'); else print imageorbutton(@$imgbuybutton,$GLOBALS['xxAddToC'],'buybutton detailbuybutton','ajaxaddcart('.$Count.')',TRUE);
					}
				}
				if($wishlistondetail) print '<br />' . imageorlink(@$imgaddtolist,$GLOBALS['xxAddLis'],'','gtid='.$Count.';return displaysavelist(event,window)',TRUE);
				if(@$showquantondetail && $hasmultipurchase==0) print '</td></tr></table>';
			}else{
				if(@$notifybackinstock)
					print '<div class="notifystock detailnotifystock">' . imageorlink(@$imgnotifyinstock,$GLOBALS['xxNotBaS'],'',"return notifyinstock(false,'".str_replace("'","\\'",$rs['pId'])."','".str_replace("'","\\'",$rs['pId'])."',".($rs['pStockByOpts']!=0&&!@$optionshavestock?'-1':'0').")", TRUE) . '</div>';
				else
					print '<div class="outofstock detailoutofstock">' . $sstrong . $GLOBALS['xxOutStok'] . $estrong . '</div>';
			}
		}	  ?></td>
			  </tr>
<?php	if($previousid!='' || $nextid!=''){
			print '<tr><td align="center" colspan="4" class="pagenums"><p class="pagenums">&nbsp;<br />';
			writepreviousnextlinks();
			print '</p></td></tr>';
		} ?>
			</table>
<?php
	}else{ // if($usedetailbodyformat==2/3/4)
		if(is_array($prodoptions)){
			$rs['pPrice']+=$optdiff;
		}
		$hasformvalidator=FALSE;
		$atcmu='';
		
		pddoptions();
		pddaddtocart();
		
		foreach($customlayoutarray as $layoutoption){
			if($layoutoption=='navigation') pddprodnavigation();
			elseif($layoutoption=='checkoutbutton') pddcheckoutbutton();
			elseif($layoutoption=='productimage') pddproductimage();
			elseif($layoutoption=='productid') pddproductid();
			elseif($layoutoption=='manufacturer') pddmanufacturer();
			elseif($layoutoption=='sku') pddsku();
			elseif($layoutoption=='productname') pdddetailname();
			elseif($layoutoption=='discounts') pdddiscounts();
			elseif($layoutoption=='instock') pddinstock();
			elseif($layoutoption=='shortdescription') pddshortdescription();
			elseif($layoutoption=='description') pdddescription();
		
			elseif($layoutoption=='listprice') pddlistprice();
			elseif($layoutoption=='price') pddprice();
			elseif($layoutoption=='currency') pddextracurrency();
			elseif($layoutoption=='options'){ print $optionshtml; $hasformvalidator=TRUE;}
			
			elseif($layoutoption=='quantity') pddquantity();
	
			elseif($layoutoption=='addtocart') print $atcmu;
			elseif($layoutoption=='previousnext') pddpreviousnext();
			elseif($layoutoption=='emailfriend') pddemailfriend();
			elseif($layoutoption=='reviews') pddreviews();
			elseif($layoutoption=='reviewstars') pddreviewstars('s');
			elseif($layoutoption=='reviewstarslarge') pddreviewstars('');
			elseif($layoutoption=='searchwords') pddsearchwords();
			elseif($layoutoption=='quantitypricing') print pddquantitypricing();
			elseif($layoutoption=='custom1') pddcustom(1);
			elseif($layoutoption=='custom2') pddcustom(2);
			elseif($layoutoption=='custom3') pddcustom(3);
			elseif($layoutoption=='dateadded') pdddateadded();
			elseif(trim($layoutoption)!='') print 'UNKNOWN LAYOUT OPTION:'.$layoutoption.'<br />';
		}



		
		
		if(! $hasformvalidator){
			$prodoptions=$optjs=$defimagejs='';
			displayformvalidator();
			if($optjs!='') print '<script type="text/javascript">/* <![CDATA[ */'.$optjs.'/* ]]> */</script>';
		}
	}
	if(getget('review')!='true') print '</form>';
	if(! @$usecsslayout) print '</td></tr>';
	if(! @$hascustomlayout || @$_REQUEST['review']!='') pddreviews();
	if(@$usecsslayout) print '</div>'; else print '</table>';
} // EOF
ect_free_result($result);
if($defimagejs!='') print '<script type="text/javascript">'.$defimagejs.'</script>'; 
if($_SESSION['clientID'] != ''){
	$sql = "SELECT clientID FROM productandlocation WHERE prodID = '" . $prodid . "' AND clientID = '" . $_SESSION['clientID'] . "' AND addID = '" . $_SESSION['addId'] . "'" ;
		$result=ect_query($sql) or ect_error();
		ect_query($sql) or ect_error();	
		if(ect_num_rows($result)==0){
			print '<form method="post" action="homepage.php?prodid='.getget('prod') . '">';
			print '<div style="float:right; font-weight:bold;margin-bottom:10px;"><input type="submit" value="Add to Homepage" name="addtohomepage" id="addtohomepage"></div>';
			print '</form>';
		}
}


?>