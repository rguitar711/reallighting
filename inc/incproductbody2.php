<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
global $shortdescriptionlimit,$nostripshortdescription,$productpagelayout;
if(!@isset($GLOBALS['xxNoFrSh']))$GLOBALS['xxNoFrSh']='This product does not qualify for Free Shipping';
$path_parts=pathinfo(@$_SERVER['PHP_SELF']);
if($path_parts['dirname']=='/'||$path_parts['dirname']=='\\')$path_parts['dirname']='';
for($cpnindex=0; $cpnindex < $adminProdsPerPage; $cpnindex++) $aDiscSection[$cpnindex][0]='';
$prodoptions='';
$extraimages=0;
$hasmultipurchase=FALSE;
$hascustomlayout=FALSE;
if(@$productpagelayout!='') $usecsslayout=TRUE;
if(@$productpagelayout=='' || ! $usecsslayout) $productpagelayout='productid,manufacturer,sku,productimage,productname,discounts,reviewstars,instock,description,options,listprice,price,currency,addtocart'; else $hascustomlayout=TRUE;
$customlayoutarray=explode(',',strtolower(str_replace(' ','',$productpagelayout)));
if(@$imgcheckoutbutton=='') $imgcheckoutbutton='images/checkout.gif';
if(@$cs=='')$cs='';
$localcount=0;
if(@$currencyseparator=='') $currencyseparator=' ';
if(@$_SESSION['clientID']=='' || @$enablewishlists==FALSE || @$wishlistonproducts=='') $wishlistonproducts=FALSE;
if(@$overridecurrency!=TRUE || @$orcdecimals=='') $orcdecimals='.';
if(@$overridecurrency!=TRUE || @$orcthousands=='') $orcthousands=',';
productdisplayscript(@$noproductoptions!=TRUE,FALSE);
if(! @$usecsslayout) print '<table class="' . $cs . 'products" width="98%" border="0" cellspacing="3" cellpadding="3">';
if(@$productcolumns=="") $productcolumns=1;
if(! (@isset($showcategories) && @$showcategories==FALSE)){
	if(! @$usecsslayout) print '<tr><td colspan="' . $productcolumns . '"><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>';
	print '<' . (@$usecsslayout ? 'div' : 'td align="left"') . ' class="prodnavigation">' . $sstrong . (! @$usecsslayout ? '<p class="prodnavigation">' : '') . $tslist . (! @$usecsslayout ? '</p>' : '') . $estrong . '</' . (@$usecsslayout ? 'div' : 'td') . '>' . "\r\n";
	print '<' . (@$usecsslayout ? 'div' : 'td align="right"') . ' class="checkoutbutton">&nbsp;' . (@$nobuyorcheckout!=TRUE ? imageorbutton($imgcheckoutbutton,$GLOBALS['xxCOTxt'],'checkoutbutton',$path_parts['dirname'] . '/cart.php', FALSE) : '') . '</' . (@$usecsslayout ? 'div' : 'td') . '>' . "\r\n";
	if(! @$usecsslayout) print '</tr></table></td></tr>' . "\r\n";
}
if(@$isproductspage) dofilterresults($productcolumns);
if(@$globaldiscounttext!=''){
	if(! @$usecsslayout) print '<tr><td align="left" class="allproddiscounts" colspan="' . $productcolumns . '">';
	print '<div class="discountsapply allproddiscounts"' . (@$nomarkup?'':' style="font-weight:bold;"') . '>' . $GLOBALS['xxDsProd'] . '</div><div class="proddiscounts allproddiscounts"' . (@$nomarkup?'':' style="font-size:9px;color:#FF0000;"') . '>' . $globaldiscounttext . '</div>';
	if(! @$usecsslayout) print '</td></tr>';
}
	if($iNumOfPages > 1 && @$pagebarattop==1){
		if(@$usecsslayout) print '<div class="pagenums" style="width:100%">' . "\r\n"; else print '<tr><td colspan="' . $productcolumns . '" align="center" class="pagenums"><p class="pagenums">';
		print writepagebar($CurPage,$iNumOfPages,$GLOBALS['xxPrev'],$GLOBALS['xxNext'],$pblink,$nofirstpg);
		if(@$usecsslayout) print "</div>\r\n"; else print '</p></td></tr>';
	}
	if(@$usecsslayout) print '<div class="' . $cs . 'products">';
	$totrows=ect_num_rows($allprods);
	if(ect_num_rows($allprods)==0)
		print (! @$usecsslayout ? '<tr><td colspan="' . $productcolumns . '" align="center">' : '') . '<p class="noproducts">'.$GLOBALS['xxNoPrds'].'</p>' . (! @$usecsslayout ? '</td></tr>' : '');
	else while($rs=ect_fetch_assoc($allprods)){
		$thedetailslink=getdetailsurl($rs['pId'],$rs['pStaticPage'],$rs[getlangid('pName',1)],$rs['pStaticURL'],'',@$GLOBALS['pathtohere']);
		$allimages=$alllgimages=$plargeimage='';
		$numallimages=$numalllgimages=0;
		$needdetaillink=trim(str_replace('<br />','',$rs[getlangid('pLongDescription',4)]))!='';
		$result2=ect_query("SELECT imageSrc FROM productimages WHERE imageType=0 AND imageProduct='" . escape_string($rs['pId']) . "' ORDER BY imageNumber") or ect_error();
		while($rs2=ect_fetch_assoc($result2)) $allimages[$numallimages++]=$rs2;
		ect_free_result($result2);
		if(@$magictoolboxproducts!=''&&$numallimages>0){
			$result2=ect_query("SELECT imageSrc FROM productimages WHERE imageType=1 AND imageProduct='" . escape_string($rs['pId']) . "' ORDER BY imageNumber") or ect_error();
			if(ect_num_rows($result2)>0){
				while($rs2=ect_fetch_assoc($result2)) $alllgimages[$numalllgimages++]=$rs2;
				$needdetaillink=TRUE;
				$plargeimage=$alllgimages[0]['imageSrc'];
			}elseif(@$GLOBALS['thumbnailsonproducts']){
				$alllgimages=$allimages;
				$numalllgimages=$numallimages;
				$plargeimage=$alllgimages[0]['imageSrc'];
			}
			ect_free_result($result2);
		}
		if((@$forcedetailslink!=TRUE && ! $needdetaillink) || @$detailslink!=''){
			$result2=ect_query("SELECT imageSrc FROM productimages WHERE imageType=1 AND imageProduct='" . escape_string($rs['pId']) . "' ORDER BY imageNumber LIMIT 0,1") or ect_error();
			if($rs2=ect_fetch_assoc($result2)){ $needdetaillink=TRUE; $plargeimage=$rs2['imageSrc']; }
			ect_free_result($result2);
		}
		$startlink=$endlink='';
		if(@$forcedetailslink==TRUE || $needdetaillink){
			if(@$detailslink!=''){
				$startlink=str_replace('%pid%', $rs['pId'], str_replace('%largeimage%', $plargeimage, $detailslink));
				$endlink=@$detailsendlink;
			}else{
				$startlink='<a class="ectlink" href="'. $thedetailslink .'">';
				$endlink='</a>';
			}
		}
		if(! $isrootsection){
			$thetopts=$rs["pSection"];
			$gotdiscsection=FALSE;
			for($cpnindex=0; $cpnindex < $adminProdsPerPage; $cpnindex++){
				if($aDiscSection[$cpnindex][0]==$thetopts){
					$gotdiscsection=TRUE;
					break;
				}elseif($aDiscSection[$cpnindex][0]=="")
					break;
			}
			$aDiscSection[$cpnindex][0]=$thetopts;
			if(! $gotdiscsection){
				$topcpnids=$thetopts;
				for($index=0; $index<= 10; $index++){
					if($thetopts==0)
						break;
					else{
						$sSQL="SELECT topSection FROM sections WHERE sectionID=" . $thetopts;
						$result2=ect_query($sSQL) or ect_error();
						if(ect_num_rows($result2) > 0){
							$rs2=ect_fetch_assoc($result2);
							$thetopts=$rs2["topSection"];
							$topcpnids.="," . $thetopts;
						}else
							break;
					}
				}
				$aDiscSection[$cpnindex][1]=$topcpnids;
			}else
				$topcpnids=$aDiscSection[$cpnindex][1];
		}
		$alldiscounts=$noapplydiscounts='';
		getperproductdiscounts();
		if(($localcount % $productcolumns)==0 && ! @$usecsslayout) print '<tr>';
		if(! @$usecsslayout) print '<td width="' . (int)(100 / $productcolumns) . '%" align="center" valign="top" class="' . $cs . 'product">';
		print '<div class="' . $cs . 'product">';
		if(@$perproducttaxrate==TRUE && ! is_null($rs['pTax'])) $thetax=$rs['pTax']; else $thetax=$countryTaxRate;
		updatepricescript();
		$shortdesc=trim($rs[getlangid('pDescription',2)]);
		if(@$shortdescriptionlimit!=''){ if(@$nostripshortdescription!=TRUE)$shortdesc=strip_tags($shortdesc); $shortdesc=substr($shortdesc, 0, $shortdescriptionlimit) . (strlen($shortdesc)>$shortdescriptionlimit && $shortdescriptionlimit!=0 ? '...' : ''); }
		print '<form method="post" name="tForm' . $Count . '" id="ectform' . $Count . '" action="' . $path_parts['dirname'] . '/cart.php" style="margin:0;padding:0;" onsubmit="return formvalidator' . $Count . '(this)">';
		if(! @$usecsslayout) print '<table width="100%" border="0" cellspacing="4" cellpadding="4">';
		$hasformvalidator=$isbackorder=FALSE;
		$optionshavestock=$isinstock=TRUE;
		$hasmultipurchase=0;
		$atcmu=$optionshtml=$optjs='';
		// Options Markup
		if(is_array($prodoptions)){
			if(@$noproductoptions==TRUE){
				$hasmultipurchase=2;
			}else{
				if($prodoptions[0]['optType']==4 && @$noproductoptions!=TRUE) $thestyle=''; else $thestyle=' width="100%"';
				$optionshtml=displayproductoptions($sstrong . '<span class="prodoption">','</span>'.$estrong,$optdiff,$thetax,FALSE,$hasmultipurchase,$optjs);
				if($optionshtml!='') $optionshtml='<div class="'.$cs.'prodoptions">' . (! @$usecsslayout ? '<table class="'.$cs.'prodoptions" border="0" cellspacing="1" cellpadding="1"'.$thestyle.'>' : '') . $optionshtml . (! @$usecsslayout ? '</table>' : '') . '</div>';
				$rs['pPrice']+=$optdiff;
			}
		}
		displayformvalidator();
		if($optjs!='') $optionshtml.='<script type="text/javascript">/* <![CDATA[ */'.$optjs.'/* ]]> */</script>';
		// Add to Cart Markup
		if(@$nobuyorcheckout!=TRUE){
			if($rs['pId']==$giftcertificateid || $rs['pId']==$donationid) $hasmultipurchase=2;
			if(! @$usecsslayout) $atcmu.='<tr><td align="center">';
			if($useStockManagement)
				if($rs['pStockByOpts']!=0) $isinstock=$optionshavestock; else $isinstock=((int)($rs['pInStock']) > 0);
			else
				$isinstock=($rs['pSell']!=0);
			if($rs['pPrice']==0 && @$nosellzeroprice==TRUE)
				$atcmu.='&nbsp;';
			else{
				if(@$usecsslayout) $atcmu.='<div class="addtocart">';
				if(! $isinstock && !($useStockManagement && $hasmultipurchase==2) && $rs['pBackOrder']==0 && @$notifybackinstock!=TRUE){
					$atcmu.='<div class="outofstock">' . $sstrong . $GLOBALS['xxOutStok'] . $estrong . '</div>';
				}elseif($hasmultipurchase==2){
					if(@$usecsslayout) $atcmu.='<div class="configbutton">';
					$atcmu.=imageorbutton(@$imgconfigoptions,$GLOBALS['xxConfig'],'configbutton',$thedetailslink, FALSE);
					if(@$usecsslayout) $atcmu.='</div>';
				}else{
					$isbackorder=! $isinstock && $rs['pBackOrder']!=0;
					writehiddenvar('id', $rs['pId']);
					writehiddenvar('mode', 'add');
					if($wishlistonproducts) writehiddenvar('listid', '');
					if(! $hascustomlayout && @$showquantonproduct && $hasmultipurchase==0 && ($isinstock || $isbackorder)){
						$atcmu.=(@$usecsslayout ? '' : '<table><tr><td align="center">') . '<div class="quantitydiv" style="'.(@$quantityupdown?'margin-right:12px;':'').'white-space:nowrap">';
						$atcmu.='<input type="text" name="quant" id="w'.$Count.'quant" size="2" maxlength="5" value="1" title="'.$GLOBALS['xxQuant'].'" class="quantityinput" ' . (@$quantityupdown?'style="float:left" ':'') . '/>' . (@$quantityupdown?'<img src="images/quantarrowu.png" onclick="quantup('.$Count.',1)" alt="" /><br /><img src="images/quantarrowd.png" onclick="quantup('.$Count.',0)" alt="" />':'') . '</div>' . (@$usecsslayout ? '' : '</td><td align="center">');
					}
					if($isbackorder){
						if(@$usehardaddtocart) $atcmu.=imageorsubmit(@$imgbackorderbutton,$GLOBALS['xxBakOrd'],'buybutton backorder'); else $atcmu.=imageorbutton(@$imgbackorderbutton,$GLOBALS['xxBakOrd'],'buybutton backorder','ajaxaddcart('.$Count.')',TRUE);
					}elseif(! $isinstock && @$notifybackinstock)
						$atcmu.='<div class="outofstock notifystock">' . imageorlink(@$imgnotifyinstock,$GLOBALS['xxNotBaS'],'',"return notifyinstock(false,'".str_replace("'","\\'",$rs['pId'])."','".str_replace("'","\\'",$rs['pId'])."',".($rs['pStockByOpts']!=0&&!@$optionshavestock?'-1':'0').")", TRUE) . '</div>';
					else{
						if(@$custombuybutton!='')
							$atcmu.=$custombuybutton;
						else{
							if(@$usehardaddtocart) $atcmu.=imageorsubmit(@$imgbuybutton,$GLOBALS['xxAddToC'],'buybutton'); else $atcmu.=imageorbutton(@$imgbuybutton,$GLOBALS['xxAddToC'],'buybutton','ajaxaddcart('.$Count.')',TRUE);
						}
					}
					if($wishlistonproducts) $atcmu.='<br />' . imageorlink(@$imgaddtolist,$GLOBALS['xxAddLis'],'','gtid='.$Count.';return displaysavelist(event,window)',TRUE);
					if(@$showquantonproduct && $hasmultipurchase==0 && ($isinstock || $isbackorder)) $atcmu.=(! @$usecsslayout ? '</td></tr></table>' : '');
				}
				if(@$usecsslayout) $atcmu.='</div>';
			}
			if(! @$usecsslayout) $atcmu.='</td></tr>';
		}
		foreach($customlayoutarray as $layoutoption){
			// *****************************
			if($layoutoption=='productid'){
		if(@$showproductid==TRUE || $hascustomlayout) print (! @$usecsslayout ? '<tr><td>' : '') . '<div class="'.$cs.'prodid">' . $sstrong . $GLOBALS['xxPrId'] . ': ' . $estrong . $rs['pId'] . '</div>' . (! @$usecsslayout ? '</td></tr>' : '');
			// *****************************
			}elseif($layoutoption=='manufacturer'){
		if(@$manufacturerfield!='' && ! is_null($rs[getlangid('scName',131072)])) print (! @$usecsslayout ? '<tr><td>' : '') . '<div class="'.$cs.'prodmanufacturer">' . $sstrong . $manufacturerfield . ': ' . $estrong . $rs[getlangid('scName',131072)] . '</div>' . (! @$usecsslayout ? '</td></tr>' : '');
			// *****************************
			}elseif($layoutoption=='sku'){
		if(@$showproductsku!='' && $rs['pSKU']!='') print (! @$usecsslayout ? '<tr><td>' : '') . '<div class="'.$cs.'prodsku">' . $sstrong . $showproductsku . ': ' . $estrong . $rs['pSKU'] . '</div>' . (! @$usecsslayout ? '</td></tr>' : '');
			// *****************************
			}elseif($layoutoption=='productimage'){
		if(! @$usecsslayout) print '<tr><td width="100%" align="center" class="'.$cs.'prodimage">';
		if(! is_array($allimages)){
			print '&nbsp;';
		}else{
			if(@$usecsslayout)
				print '<div class="prodimage">';
			elseif($numallimages>1 && !@$thumbnailsonproducts)
				print '<table border="0" cellspacing="1" cellpadding="1"><tr><td colspan="3">';
			$magictooloptionsproducts=str_replace(array('rel=','"'),array(';',''),@$magictooloptionsproducts);
			print (@$magictoolboxproducts!='' && $plargeimage!=''?'<a id="mzprodimage'.$Count.'" rel="group:g'.$Count.$magictooloptionsproducts.'" href="'.$plargeimage.'" class="' . $magictoolboxproducts . '">':$startlink).'<img id="prodimage'.$Count.'" class="'.@$cs.'prodimage" src="'.str_replace('%s','',$allimages[0]['imageSrc']).'" style="border:0" alt="'.str_replace('"', '&quot;', strip_tags($rs[getlangid('pName',1)])).'" />'.(@$magictoolboxproducts!='' && $plargeimage!=''?'</a>':$endlink);
			if($numallimages>1 && !@$thumbnailsonproducts)
				print (@$usecsslayout ? '<div class="'.$cs.'imagenavigator">' : '</td></tr><tr><td class="'.$cs.'imagenavigator" align="left">') . '<img class="previousimage" src="images/leftimage.gif" onclick="return updateprodimage(' . $Count . ', false);" onmouseover="this.style.cursor=\'pointer\'" alt="' . $GLOBALS['xxPrev'] . '" ' . (@$usecsslayout ? '/>' : 'style="float:left;margin:0px;" /></td><td align="center">') . '<span class="extraimage extraimagenum" id="extraimcnt' . $Count . '">1</span> <span class="extraimage">' . $GLOBALS['xxOf'] . ' ' . $extraimages . '</span>' . (@$usecsslayout ? '' : '</td><td align="right">') . '<img class="nextimage" src="images/rightimage.gif" onclick="return updateprodimage(' . $Count . ', true);" onmouseover="this.style.cursor=\'pointer\'" alt="' . $GLOBALS['xxNext'] . '" ' . (@$usecsslayout ? '/></div>' : 'style="float:right;margin:0px;" /></td></tr></table>');
			if(@$usecsslayout) print '</div>';
			if(@$magictoolboxproducts!='' && $numallimages>1 && @$thumbnailsonproducts){
				if($magictoolboxproducts=='MagicThumb') $relid='thumb-id:'; else $relid='';
				if($magictoolboxproducts=='MagicZoom' || $magictoolboxproducts=='MagicZoomPlus') $relid='zoom-id:';
				if(@$thumbnailstyleproducts=='') $thumbnailstyleproducts='width:50px;padding:2px';
				if(@$usecsslayout) print '<div class="thumbnailimage productsthumbnail">'; else print '</td></tr><tr><td class="thumbnailimage productsthumbnail" align="center">';
				if(@$magicscrollthumbnailsproducts) print '<div class="MagicScroll">';
				for($index=0;$index<$numallimages;$index++){
					if($index < $numalllgimages) print '<a href="' . $alllgimages[$index]['imageSrc'] . '" rev="' . $allimages[$index]['imageSrc'] . '" rel="' . $relid . 'mzprodimage'.$Count.'"><img src="' . $allimages[$index]['imageSrc'] . '" style="' . $thumbnailstyleproducts . '" alt="" /></a>';
				}
				if(@$magicscrollthumbnailsproducts) print '</div>';
				if(@$usecsslayout) print '</div>'; else print '</td></tr></table>';
			}
		}
		if(! @$usecsslayout) print '</td></tr>';
			// *****************************
			}elseif($layoutoption=='productname'){
		if(! @$usecsslayout) print '<tr><td width="100%">';
		print $sstrong . '<div class="'.$cs.'prodname">' . $startlink . $rs[getlangid("pName",1)] . $endlink . $GLOBALS['xxDot'] . '</div>' . $estrong;
			// *****************************
			}elseif($layoutoption=='discounts'){
		if($alldiscounts!='') print ' ' . (@$nomarkup?'':'<font color="#FF0000">') .$sstrong.'<span class="discountsapply">' . $GLOBALS['xxDsApp'] . '</span>'.$estrong . (@$nomarkup?'':'</font>') . '<br /><div class="'.$cs.'proddiscounts"' . (@$nomarkup?'':' style="font-size:11px;color:#FF0000;"') . '>' . $alldiscounts . '</div>';
		if($noapplydiscounts!='') print ' ' . (@$nomarkup?'':'<font color="#FF0000">') .$sstrong.'<span class="discountsnotapply">' . $GLOBALS['xxDsNoAp'] . '</span>'.$estrong . (@$nomarkup?'':'</font>') . '<br /><div class="'.$cs.'prodnoapplydiscounts"' . (@$nomarkup?'':' style="font-size:11px;color:#FF0000;"') . '>' . $noapplydiscounts . '</div>';
		if(($rs['pExemptions']&16)==16&&$hasshippingdiscount&&@$GLOBALS['xxNoFrSh']!='') print '<div class="freeshippingexempt">'.$GLOBALS['xxNoFrSh'].'</div>';
			// *****************************
			}elseif($layoutoption=='reviewstars'){
		if(@$ratingsonproductspage==TRUE || $hascustomlayout){
			if($rs['pNumRatings']>0) print showproductreviews(2, $cs.'prodrating'); else print @$GLOBALS['prodreviewnoratings'];
		}
			// *****************************
			}elseif($layoutoption=='instock'){
		if($useStockManagement && @$showinstock==TRUE){ if((int)$rs['pStockByOpts']==0) print '<div class="'.$cs.'prodinstock">' . $sstrong . $GLOBALS['xxInStoc'] . ': ' . $estrong . max(0,$rs['pInStock']) . '</div>'; }
			// *****************************
			}elseif($layoutoption=='description'){
		if($shortdesc!='') print '<div class="'.$cs.'proddescription">' . $shortdesc . '</div>'; else print '<br />';
			// *****************************
			}elseif($layoutoption=='options'){
		print $optionshtml;
		$hasformvalidator=TRUE;
			// *****************************
			}elseif($layoutoption=='listprice'){
		if($rs['pId']!=$giftcertificateid && $rs['pId']!=$donationid && @$noprice!=TRUE){
			if((double)$rs['pListPrice']!=0.0){ $plistprice=(@$GLOBALS['showtaxinclusive']===2 && ($rs['pExemptions'] & 2)!=2?$rs['pListPrice']+($rs['pListPrice']*$thetax/100.0):$rs['pListPrice']); print '<div class="'.$cs.'listprice">' . str_replace('%s', FormatEuroCurrency($plistprice), $GLOBALS['xxListPrice']) . (@$GLOBALS['yousavetext']!=''?str_replace('%s', FormatEuroCurrency($plistprice-(@$GLOBALS['showtaxinclusive']===2 && ($rs['pExemptions'] & 2)!=2 ? $rs['pPrice']+($rs['pPrice']*$thetax/100.0) : $rs['pPrice'])), $GLOBALS['yousavetext']):'') . '</div>';}
		}
			// *****************************
			}elseif($layoutoption=='price'){
		if($rs['pId']!=$giftcertificateid && $rs['pId']!=$donationid && @$noprice!=TRUE){
			print '<div class="'.$cs.'prodprice"><strong>' . $GLOBALS['xxPrice'].($GLOBALS['xxPrice']!=''?':':'') . '</strong> <span class="price" id="pricediv' . $Count . '">' . ($rs['pPrice']==0 && @$pricezeromessage!='' ? $pricezeromessage : FormatEuroCurrency(@$GLOBALS['showtaxinclusive']===2 && ($rs['pExemptions'] & 2)!=2 ? $rs['pPrice']+($rs['pPrice']*$thetax/100.0) : $rs['pPrice'])) . '</span> ';
			if(@$GLOBALS['showtaxinclusive']==1 && ($rs['pExemptions'] & 2)!=2) printf('<span id="taxmsg' . $Count . '"' . ($rs['pPrice']==0 ? ' style="display:none"' : '') . '>' . $ssIncTax . '</span>','<span id="pricedivti' . $Count . '">' . ($rs['pPrice']==0 ? '-' : FormatEuroCurrency($rs['pPrice']+($rs['pPrice']*$thetax/100.0))) . '</span> ');
		
			print '</div>';
		}
			// *****************************
			}elseif($layoutoption=='currency'){
		if($rs['pId']!=$giftcertificateid && $rs['pId']!=$donationid && @$noprice!=TRUE){
			$extracurr='';
			if($currRate1!=0 && $currSymbol1!='') $extracurr=str_replace('%s',number_format($rs['pPrice']*$currRate1,checkDPs($currSymbol1),$orcdecimals,$orcthousands),$currFormat1) . $currencyseparator;
			if($currRate2!=0 && $currSymbol2!='') $extracurr.=str_replace('%s',number_format($rs['pPrice']*$currRate2,checkDPs($currSymbol2),$orcdecimals,$orcthousands),$currFormat2) . $currencyseparator;
			if($currRate3!=0 && $currSymbol3!='') $extracurr.=str_replace('%s',number_format($rs['pPrice']*$currRate3,checkDPs($currSymbol3),$orcdecimals,$orcthousands),$currFormat3);
			if($extracurr!='') print '<div class="'.$cs.'prodcurrency"><span class="extracurr" id="pricedivec' . $Count . '">' . ($rs['pPrice']==0 ? '' : $extracurr) . '</span></div>';
		}
			// *****************************
			}elseif($layoutoption=='quantity'){
		if(! ($rs['pPrice']==0 && @$nosellzeroprice==TRUE) && $hasmultipurchase==0 && ($isinstock || $isbackorder)){
			print (@$usecsslayout ? '' : '<table><tr><td align="center">') . '<div class="quantitydiv" style="'.(@$quantityupdown?'margin-right:12px;':'').'white-space:nowrap">';
			print '<input type="text" name="quant" id="w'.$Count.'quant" size="2" maxlength="5" value="1" title="'.$GLOBALS['xxQuant'].'" class="quantityinput" ' . (@$quantityupdown?'style="float:left" ':'') . '/>' . (@$quantityupdown?'<img src="images/quantarrowu.png" onclick="quantup('.$Count.',1)" alt="" /><br /><img src="images/quantarrowd.png" onclick="quantup('.$Count.',0)" alt="" />':'') . '</div>' . (@$usecsslayout ? '' : '</td><td align="center">');
		}
			// *****************************
			}elseif($layoutoption=='addtocart'){
		if(! @$usecsslayout) print '</td></tr>';
		print $atcmu;
			// *****************************
			}elseif($layoutoption=='custom1'){
				if(trim($rs['pCustom1'])!='') print '<div class="'.$cs.'prodcustom1">' . @$prodcustomlabel1 . $rs['pCustom1'] . '</div>';
			// *****************************
			}elseif($layoutoption=='custom2'){
				if(trim($rs['pCustom2'])!='') print '<div class="'.$cs.'prodcustom2">' . @$prodcustomlabel2 . $rs['pCustom2'] . '</div>';
			// *****************************
			}elseif($layoutoption=='custom3'){
				if(trim($rs['pCustom3'])!='') print '<div class="'.$cs.'prodcustom3">' . @$prodcustomlabel3 . $rs['pCustom3'] . '</div>';
			}elseif($layoutoption=='detaillink'){
				print '<div class="'.$cs.'detaillink">' . imageorlink(@$imgdetaillink,$GLOBALS['xxPrDets'],$cs.'detaillink',$thedetailslink,FALSE) . '</div>';
			}elseif($layoutoption=='dateadded'){
				if(trim($rs['pDateAdded'])!='') print '<div class="'.$cs.'proddateadded">' . (@$dateaddedlabel!=''?'<div class="'.$cs.'proddateaddedlabel">' . $dateaddedlabel . '</div>':'') . '<div class="'.$cs.'proddateaddeddate">' . date($GLOBALS['dateformatstr'],strtotime($rs['pDateAdded'])) . '</div></div>';
			}elseif(trim($layoutoption)!='')
				print 'UNKNOWN LAYOUT OPTION:'.$layoutoption.'<br />';
		}
		if(!$hasformvalidator){
			$optjs=$defimagejs=$prodoptions='';
			$optionshavestock=TRUE;
			$hasmultipurchase=0;
			displayformvalidator();
			if($optjs!='') print '<script type="text/javascript">/* <![CDATA[ */'.$optjs.'/* ]]> */</script>';
		}
		if(! @$usecsslayout) print '</table>';
		print '</form></div>';
		if(! @$usecsslayout) print '</td>';
		$Count++;
		$localcount++;
		if((($localcount % $productcolumns)==0) && ! @$usecsslayout){
			print '</tr>';
			if(! ($localcount==$totrows) && $localcount < $adminProdsPerPage){
				if(@$noproductseparator!=TRUE){
					print '<tr>';
					for($index=1; $index <= $productcolumns; $index++)
						print '<td class="prodseparator">' . (@$prodseparator!='' ? $prodseparator : '<hr class="prodseparator" width="70%" align="center"/>') . '</td>';
					print '</tr>';
				}
			}
		}
	}
	if((($localcount % $productcolumns)!=0) && ! @$usecsslayout){
		while($localcount % $productcolumns!=0){
			print '<td class="'.$cs.'noproduct" width="' . (int)(100 / $productcolumns) . '%" align="center">&nbsp;</td>';
			$localcount++;
		}
		print '</tr>';
	}
	if($iNumOfPages>1 && @$nobottompagebar!=TRUE){
		if(@$usecsslayout) print '<div class="pagenums" style="width:100%">' . "\r\n"; else print '<tr><td colspan="' . $productcolumns . '" align="center" class="pagenums"><p class="pagenums">';
		print writepagebar($CurPage,$iNumOfPages,$GLOBALS['xxPrev'],$GLOBALS['xxNext'],$pblink,$nofirstpg);
		if(@$usecsslayout) print "</div>\r\n"; else print '</p></td></tr>';
	}
	if(@$usecsslayout) print '</div>'; else print '</table>';
	if($defimagejs!='') print '<script type="text/javascript">' . $defimagejs . '</script>';
?>