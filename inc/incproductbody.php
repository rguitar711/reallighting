<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
global $shortdescriptionlimit,$nostripshortdescription;
if(!@isset($GLOBALS['xxNoFrSh']))$GLOBALS['xxNoFrSh']='This product does not qualify for Free Shipping';
for($cpnindex=0; $cpnindex < $adminProdsPerPage; $cpnindex++) $aDiscSection[$cpnindex][0]='';
$prodoptions='';
$extraimages=0;
if(@$imgcheckoutbutton=='') $imgcheckoutbutton='images/checkout.gif';
if(@$currencyseparator=='') $currencyseparator=' ';
if(@$_SESSION['clientID']=='' || @$enablewishlists==FALSE || @$wishlistonproducts=='') $wishlistonproducts=FALSE;
if(@$overridecurrency!=TRUE || @$orcdecimals=='') $orcdecimals='.';
if(@$overridecurrency!=TRUE || @$orcthousands=='') $orcthousands=',';
productdisplayscript(@$noproductoptions!=TRUE,FALSE); ?>
		<table class="products" width="98%" border="0" cellspacing="3" cellpadding="3">
<?php	if(! (@isset($showcategories) && @$showcategories==FALSE)){ ?>
		  <tr>
			<td class="prodnavigation" colspan="2" align="left"><?php print $sstrong . '<p class="prodnavigation">' . $tslist . '</p>' . $estrong ?></td>
			<td align="right">&nbsp;<?php if(@$nobuyorcheckout!=TRUE) print imageorbutton($imgcheckoutbutton,$GLOBALS['xxCOTxt'],'checkoutbutton','cart.php', FALSE)?></td>
		  </tr>
<?php	}
	if(@$isproductspage) dofilterresults(3);
if(@$globaldiscounttext!=''){ ?>
		  <tr>
			<td align="left" class="allproddiscounts" colspan="3">
				<div class="discountsapply allproddiscounts"<?php print (@$nomarkup?'':' style="font-weight:bold;"')?>><?php print $GLOBALS['xxDsProd']?></div><div class="proddiscounts allproddiscounts"<?php print (@$nomarkup?'':' style="font-size:9px;color:#FF0000;"')?>><?php
					print $globaldiscounttext; ?></div>
			</td>
		  </tr>
<?php
}
	if($iNumOfPages>1 && @$pagebarattop==1){
?>		  <tr>
			<td colspan="3" align="center" class="pagenums"><p class="pagenums"><?php print writepagebar($CurPage,$iNumOfPages,$GLOBALS['xxPrev'],$GLOBALS['xxNext'],$pblink,$nofirstpg) ?></p></td>
		  </tr><?php
	}
	if(ect_num_rows($allprods)==0)
		print '<tr><td colspan="3" align="center"><p class="noproducts">'.$GLOBALS['xxNoPrds'].'</p></td></tr>';
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
		getperproductdiscounts(); ?>
              <tr> 
                <td width="26%" rowspan="3" align="center" class="prodimage"><?php
			if(@$perproducttaxrate==TRUE && ! is_null($rs['pTax'])) $thetax=$rs['pTax']; else $thetax=$countryTaxRate;
			updatepricescript();
			if(! is_array($allimages)){
				print '&nbsp;';
			}else{
				if($numallimages>1 && !@$thumbnailsonproducts) print '<table border="0" cellspacing="1" cellpadding="1"><tr><td colspan="3">';
			
				$magictooloptionsproducts=str_replace(array('rel=','"'),array(';',''),@$magictooloptionsproducts);
				print (@$magictoolboxproducts!='' && $plargeimage!=''?'<a id="mzprodimage'.$Count.'" rel="group:g'.$Count.$magictooloptionsproducts.'" href="'.$plargeimage.'" class="' . $magictoolboxproducts . '">':$startlink).'<img id="prodimage'.$Count.'" class="'.@$cs.'prodimage" src="'.str_replace('%s','',$allimages[0]['imageSrc']).'" style="border:0" alt="'.str_replace('"', '&quot;', strip_tags($rs[getlangid('pName',1)])).'" />'.(@$magictoolboxproducts!='' && $plargeimage!=''?'</a>':$endlink);
				if($numallimages>1 && !@$thumbnailsonproducts) print '</td></tr><tr><td align="left"><img src="images/leftimage.gif" onclick="return updateprodimage('.$Count.', false);" onmouseover="this.style.cursor=\'pointer\'" style="float:left;margin:0px;" alt="'.$GLOBALS['xxPrev'].'" /></td><td align="center"><span class="extraimage extraimagenum" id="extraimcnt'.$Count.'">1</span> <span class="extraimage">'.$GLOBALS['xxOf'].' '.$extraimages.'</span></td><td align="right"><img src="images/rightimage.gif" onclick="return updateprodimage('.$Count.', true);" onmouseover="this.style.cursor=\'pointer\'" style="float:right;margin:0px;" alt="'.$GLOBALS['xxNext'].'" /></td></tr></table>';
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
			} ?></td><td width="59%">
<?php				if(@$showproductid==TRUE) print '<div class="prodid">' . $sstrong . $GLOBALS['xxPrId'] . ': ' . $estrong . $rs['pId'] . '</div>';
					if(@$manufacturerfield!='' && ! is_null($rs[getlangid('scName',131072)])) print '<div class="prodmanufacturer">' . $sstrong . $manufacturerfield . ': ' . $estrong . $rs[getlangid('scName',131072)] . '</div>';
					if(@$showproductsku!='' && $rs['pSKU']!='') print '<div class="prodsku"><strong>' . $showproductsku . ':</strong> ' . $rs['pSKU'] . '</div>';
					print $sstrong . '<div class="prodname">'.$startlink.$rs[getlangid('pName',1)].$endlink.$GLOBALS['xxDot'];
					if($alldiscounts!='') print ' ' . (@$nomarkup?'':'<font color="#FF0000">') . '<span class="discountsapply">' . $GLOBALS['xxDsApp'] . '</span>' . (@$nomarkup?'':'</font>') . '</div>' . $estrong . '<div class="proddiscounts"' . (@$nomarkup?'':' style="font-size:11px;color:#FF0000;"') . '>' . $alldiscounts . '</div>'; else print '</div>' . $estrong;
					if($noapplydiscounts!='') print ' ' . (@$nomarkup?'':'<font color="#FF0000">') . '<span class="discountsnotapply">' . $GLOBALS['xxDsNoAp'] . '</span>' . (@$nomarkup?'':'</font>') . '</div>' . $estrong . '<div class="prodnoapplydiscounts"' . (@$nomarkup?'':' style="font-size:11px;color:#FF0000;"') . '>' . $noapplydiscounts . '</div>'; else print '</div>' . $estrong;
					if(($rs['pExemptions']&16)==16&&$hasshippingdiscount&&@$GLOBALS['xxNoFrSh']!='') print '<div class="freeshippingexempt">'.$GLOBALS['xxNoFrSh'].'</div>';
					if($useStockManagement && @$showinstock==TRUE){ if((int)$rs['pStockByOpts']==0) print '<div class="prodinstock"><strong>' . $GLOBALS['xxInStoc'] . ':</strong> ' . max(0,$rs['pInStock']) . '</div>'; }
					if(@$ratingsonproductspage==TRUE && $rs['pNumRatings']>0) print showproductreviews(1, 'prodrating'); ?>
                </td>
				<td width="15%" align="right" valign="top"><?php
            		if($startlink!='')
                		print '<p>' . $startlink . '<strong>'.$GLOBALS['xxPrDets'].'</strong></a>&nbsp;</p>';
                	else
                		print '&nbsp;';
              ?></td>
			  </tr>
			  <tr>
			    <td colspan="2" class="proddescription"><form method="post" name="tForm<?php print $Count; ?>" id="ectform<?php print $Count;?>" action="cart.php" style="margin:0;padding:0;" onsubmit="return formvalidator<?php print $Count;?>(this)"><?php
	writehiddenvar('id', $rs['pId']);
	writehiddenvar('mode', 'add');
	if($wishlistonproducts) writehiddenvar('listid', '');
	print '<input type="hidden" name="quant" id="qnt'.$Count.'x" value="" />';
	print '<div class="proddescription">';	
	$shortdesc=$rs[getlangid('pDescription',2)];
	if(@$shortdescriptionlimit=='') print $shortdesc; else{ if(@$nostripshortdescription!=TRUE)$shortdesc=strip_tags($shortdesc); print substr($shortdesc, 0, $shortdescriptionlimit) . (strlen($shortdesc)>$shortdescriptionlimit && $shortdescriptionlimit!=0 ? '...' : ''); }
	print '</div>';
	$optionshavestock=true;
	$hasmultipurchase=0;
	$optjs='';
	if(is_array($prodoptions)){
		if(@$noproductoptions==TRUE){
			$hasmultipurchase=2;
		}else{
			if($prodoptions[0]['optType']==4 && @$noproductoptions!=TRUE) $thestyle=''; else $thestyle=' width="100%"';
			$optionshtml=displayproductoptions('<strong><span class="prodoption">','</span></strong>',$optdiff,$thetax,FALSE,$hasmultipurchase,$optjs);
			if($optionshtml!='') print '<div class="prodoptions"><table class="prodoptions" border="0" cellspacing="1" cellpadding="1"'.$thestyle.'>' . $optionshtml . '</table></div>';
			$rs['pPrice']+=$optdiff;
		}
	}
	displayformvalidator();
	if($optjs!='') print '<script type="text/javascript">/* <![CDATA[ */'.$optjs.'/* ]]> */</script>';
?>		</form></td>
			  </tr>
			  <tr>
				<td width="59%" align="center"><?php
					if(@$noprice==TRUE || $rs['pId']==$giftcertificateid || $rs['pId']==$donationid){
						print '&nbsp;';
					}else{
						
						if((double)$rs['pListPrice']!=0.0){ $plistprice=(@$GLOBALS['showtaxinclusive']===2 && ($rs['pExemptions'] & 2)!=2?$rs['pListPrice']+($rs['pListPrice']*$thetax/100.0):$rs['pListPrice']); print '<div class="listprice">' . str_replace('%s', FormatEuroCurrency($plistprice), $GLOBALS['xxListPrice']) . (@$GLOBALS['yousavetext']!=''?str_replace('%s', FormatEuroCurrency($plistprice-(@$GLOBALS['showtaxinclusive']===2 && ($rs['pExemptions'] & 2)!=2 ? $rs['pPrice']+($rs['pPrice']*$thetax/100.0) : $rs['pPrice'])), $GLOBALS['yousavetext']):'') . '</div>';}
						print '<div class="prodprice"><strong>' . $GLOBALS['xxPrice'].($GLOBALS['xxPrice']!=''?':':'') . '</strong> <span class="price" id="pricediv' . $Count . '">' . ($rs['pPrice']==0 && @$pricezeromessage!='' ? $pricezeromessage : FormatEuroCurrency(@$GLOBALS['showtaxinclusive']===2 && ($rs['pExemptions'] & 2)!=2 ? $rs['pPrice']+($rs['pPrice']*$thetax/100.0) : $rs['pPrice'])) . '</span> ';
						if(@$GLOBALS['showtaxinclusive']==1 && ($rs['pExemptions'] & 2)!=2) printf('<span id="taxmsg' . $Count . '"' . ($rs['pPrice']==0 ? ' style="display:none"' : '') . '>' . $ssIncTax . '</span>','<span id="pricedivti' . $Count . '">' . ($rs['pPrice']==0 ? '-' : FormatEuroCurrency($rs['pPrice']+($rs['pPrice']*$thetax/100.0))) . '</span> ');
						print '</div>';
						$extracurr='';
						if($currRate1!=0 && $currSymbol1!='') $extracurr=str_replace('%s',number_format($rs['pPrice']*$currRate1,checkDPs($currSymbol1),$orcdecimals,$orcthousands),$currFormat1) . $currencyseparator;
						if($currRate2!=0 && $currSymbol2!='') $extracurr.=str_replace('%s',number_format($rs['pPrice']*$currRate2,checkDPs($currSymbol2),$orcdecimals,$orcthousands),$currFormat2) . $currencyseparator;
						if($currRate3!=0 && $currSymbol3!='') $extracurr.=str_replace('%s',number_format($rs['pPrice']*$currRate3,checkDPs($currSymbol3),$orcdecimals,$orcthousands),$currFormat3);
						if($extracurr!='') print '<div class="prodcurrency"><span class="extracurr" id="pricedivec' . $Count . '">' . ($rs['pPrice']==0 ? '' : $extracurr) . '</span></div>';
						print 'asdf';
						
					} ?>
                </td>
			    <td align="right" valign="bottom" style="white-space:nowrap;"><?php
		if(@$nobuyorcheckout==TRUE)
			print '&nbsp;';
		else{
			if($rs['pId']==$giftcertificateid || $rs['pId']==$donationid) $hasmultipurchase=2;
			if($useStockManagement)
				if($rs['pStockByOpts']!=0) $isinstock=$optionshavestock; else $isinstock=((int)($rs['pInStock']) > 0);
			else
				$isinstock=($rs['pSell']!=0);
			if($rs['pPrice']==0 && @$nosellzeroprice==TRUE){
				print '&nbsp;';
			}else{
				if(! $isinstock && !($useStockManagement && $hasmultipurchase==2) && $rs['pBackOrder']==0 && @$notifybackinstock!=TRUE){
					print '<div class="outofstock">' . $sstrong . $GLOBALS['xxOutStok'] . $estrong . '</div>';
				}elseif($hasmultipurchase==2)
					print imageorbutton(@$imgconfigoptions,$GLOBALS['xxConfig'],'configbutton',$thedetailslink, FALSE);
				else{
					$isbackorder=! $isinstock && $rs['pBackOrder']!=0;
					if(@$showquantonproduct && $hasmultipurchase==0 && ($isinstock || $isbackorder)) print '<table><tr><td align="center"><div class="quantitydiv" style="'.(@$quantityupdown?'margin-right:12px;':'').'white-space:nowrap"><input type="text" id="w'.$Count.'quant" size="2" maxlength="5" value="1" title="'.$GLOBALS['xxQuant'].'" onchange="document.getElementById(\'qnt'.$Count.'x\').value=this.value" class="quantityinput" ' . (@$quantityupdown?'style="float:left" ':'') . '/>' . (@$quantityupdown?'<img src="images/quantarrowu.png" onclick="quantup('.$Count.',1)" alt="" /><br /><img src="images/quantarrowd.png" onclick="quantup('.$Count.',0)" alt="" />':'') . '</div></td><td align="center">';
					if($isbackorder)
						print imageorbutton(@$imgbackorderbutton,$GLOBALS['xxBakOrd'],'buybutton backorder',(@$usehardaddtocart?'subformid':'ajaxaddcart').'('.$Count.')', TRUE);
					elseif(! $isinstock && @$notifybackinstock)
						print '<div class="outofstock notifystock">' . imageorlink(@$imgnotifyinstock,$GLOBALS['xxNotBaS'],'',"return notifyinstock(false,'".str_replace("'","\\'",$rs['pId'])."','".str_replace("'","\\'",$rs['pId'])."',".($rs['pStockByOpts']!=0&&!@$optionshavestock?'-1':'0').")", TRUE) . '</div>';
					else
						print imageorbutton(@$imgbuybutton,$GLOBALS['xxAddToC'],'buybutton',(@$usehardaddtocart?'subformid':'ajaxaddcart').'('.$Count.')',TRUE);
					if($wishlistonproducts) print '<br />' . imageorlink(@$imgaddtolist,$GLOBALS['xxAddLis'],'','gtid='.$Count.';return displaysavelist(event,window)',TRUE);
					if(@$showquantonproduct && $hasmultipurchase==0 && ($isinstock || $isbackorder)) print '</td></tr></table>';
					
				}
			}
		}	  ?></td>
			  </tr>
<?php	if(@$noproductseparator!=TRUE){
			print '<tr><td colspan="3" class="prodseparator">' . (@$prodseparator!='' ? $prodseparator : '<hr class="prodseparator" width="70%" align="center"/>') . '</td></tr>';
		}
		$Count++;
	}
	if($iNumOfPages>1 && @$nobottompagebar<>TRUE){ ?>
		  <tr><td colspan="3" align="center" class="pagenums"><p class="pagenums"><?php print writepagebar($CurPage,$iNumOfPages,$GLOBALS['xxPrev'],$GLOBALS['xxNext'],$pblink,$nofirstpg); ?></p></td></tr>
<?php
	} ?>
		</table>
<?php if($defimagejs!='') print '<script type="text/javascript">'.$defimagejs.'</script>'; ?>