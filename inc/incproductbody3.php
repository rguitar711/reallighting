<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
global $cpdcolumns,$shortdescriptionlimit,$nostripshortdescription;
if(!@isset($GLOBALS['xxNoFrSh']))$GLOBALS['xxNoFrSh']='This product does not qualify for Free Shipping';
for($cpnindex=0; $cpnindex < $adminProdsPerPage; $cpnindex++) $aDiscSection[$cpnindex][0]="";
$prodoptions=$nooptionshtml=$optionshtml='';
$extraimages=$hasmultipurchase=$totprice=0;
// id,name,discounts,listprice,price,priceinctax,options,quantity,currency,instock,rating,buy
if(@$cpdcolumns=='') $cpdcolumns='id,name,discounts,listprice,price,priceinctax,instock,quantity,buy';
$cpdarray=explode(',',strtolower($cpdcolumns));
$noproductoptions=TRUE;
$savetaxinclusive=@$GLOBALS['showtaxinclusive'];
$GLOBALS['showtaxinclusive']=0;
$GLOBALS['ectbody3layouttaxinc']=FALSE;
$hascurrency=FALSE;
$noupdateprice=TRUE;
if(@$currencyseparator=='') $currencyseparator=' ';
if(@$_SESSION['clientID']=='' || @$enablewishlists==FALSE || @$wishlistonproducts=='') $wishlistonproducts=FALSE;
if(@$overridecurrency!=TRUE || @$orcdecimals=='') $orcdecimals='.';
if(@$overridecurrency!=TRUE || @$orcthousands=='') $orcthousands=',';
function docallupdatepricescript(){
	global $noproductoptions,$hasmultipurchase,$optionshtml,$prodoptions,$sstrong,$estrong,$optdiff,$thetax,$rs,$updatepricecalled,$giftcertificateid,$donationid,$optjs;
	updatepricescript();
	$hasmultipurchase=0;
	$optionshtml=$optjs='';
	if(is_array($prodoptions)){
		if(@$noproductoptions==TRUE){
			$hasmultipurchase=2;
		}else{
			$optionshtml=displayproductoptions($sstrong . '<span class="prodoption">','</span>' . $estrong,$optdiff,$thetax,FALSE,$hasmultipurchase,$optjs);
			$rs['pPrice']+=$optdiff;
		}
	}
	displayformvalidator();
	if($optjs!='') print '<script type="text/javascript">/* <![CDATA[ */'.$optjs.'/* ]]> */</script>';
	if($rs['pId']==$giftcertificateid || $rs['pId']==$donationid) $hasmultipurchase=2;
	$updatepricecalled=TRUE;
}
foreach($cpdarray as $cpdindex => $cpdarrval){
	switch(trim($cpdarrval)){
		case 'options':
			$noproductoptions=FALSE;
		break;
		case 'price':
			$noupdateprice=FALSE;
		break;
		case 'priceinctax':
			$GLOBALS['showtaxinclusive']=$savetaxinclusive;
			$GLOBALS['ectbody3layouttaxinc']=TRUE;
		break;
		case 'currency':
			$hascurrency=TRUE;
		break;
	}
}
if(! $hascurrency){$currSymbol1=''; $currSymbol2=''; $currSymbol3='';}
if(@$imgcheckoutbutton=='') $imgcheckoutbutton='images/checkout.gif';
productdisplayscript(@$noproductoptions!=TRUE,FALSE); ?>
		<table width="98%" border="0" cellspacing="3" cellpadding="3">
<?php	if(! (@isset($showcategories) && @$showcategories==FALSE)){ ?>
		  <tr>
			<td class="prodnavigation" colspan="2" align="left"><?php print $sstrong . '<p class="prodnavigation">' . $tslist . '</p>' . $estrong?></td>
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
	if($iNumOfPages > 1 && @$pagebarattop==1){
?>		  <tr>
			<td colspan="3" align="center" class="pagenums"><p class="pagenums"><?php print writepagebar($CurPage,$iNumOfPages,$GLOBALS['xxPrev'],$GLOBALS['xxNext'],$pblink,$nofirstpg) ?></p></td>
		  </tr><?php
	}
	if(ect_num_rows($allprods)==0){
		print '<tr><td colspan="3" align="center"><p class="noproducts">'.$GLOBALS['xxNoPrds'].'</p></td></tr>';
	}else{
	print '<tr><td colspan="3"><table class="cobtbl cpd" width="100%" border="0" cellspacing="1" cellpadding="3">';
	if(@$cpdheaders!=''){
		$cpdheadarray=explode(',',$cpdheaders);
		print '<tr>';
		foreach($cpdheadarray as $cpdindex => $cpdheadarrval){
			print '<td class="cobhl cpdhl"><div class="cpdhl' . @$cpdarray[$cpdindex] . '">' . $cpdheadarrval . '</div></td>';
		}
		print '</tr>';
	}
	while($rs=ect_fetch_assoc($allprods)){
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
				$startlink='<a class="ectlink" href="' . $thedetailslink . '">';
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
		$optionshavestock=true;
		print '<tr class="cpdtr">';
		if(@$perproducttaxrate==TRUE && ! is_null($rs['pTax'])) $thetax=$rs['pTax']; else $thetax=$countryTaxRate;

		$updatepricecalled=FALSE;
		foreach($cpdarray as $cpdindex => $cpdarrval){
			switch(trim($cpdarrval)){
			case 'id': ?>
			<td class="cobll cpdll"><?php if(! $updatepricecalled) docallupdatepricescript(); ?><div class="prod3id"><?php print $startlink . $rs['pId'] . $endlink ?></div></td>
<?php		break;
			case 'sku': ?>
			<td class="cobll cpdll"><div class="prod3sku"><?php print $startlink . $rs['pSKU'] . $endlink ?></div></td>
<?php		break;
			case 'manufacturer': ?>
			<td class="cobll cpdll"><div class="prod3manufacturer"><?php print $rs[getlangid('scName',131072)]?></div></td>
<?php		break;
			case 'name': ?>
			<td class="cobll cpdll"><div class="prod3name"><?php print $rs[getlangid('pName',1)] ?></div></td>
<?php		break;
			case 'description': ?>
			<td class="cobll cpdll"><div class="prod3description"><?php
				$shortdesc=$rs[getlangid('pDescription',2)];
				if(@$shortdescriptionlimit=='') print $shortdesc; else{ if(@$nostripshortdescription!=TRUE)$shortdesc=strip_tags($shortdesc); print substr($shortdesc, 0, $shortdescriptionlimit) . (strlen($shortdesc)>$shortdescriptionlimit && $shortdescriptionlimit!=0 ? '...' : ''); } ?></div></td>
<?php		break;
			case 'image': ?>
			<td class="cobll cpdll"><?php
			if(! $updatepricecalled) docallupdatepricescript();
			if(! is_array($allimages)){
				print '&nbsp;';
			}else{
				if($numallimages>1 && !@$thumbnailsonproducts) print '<table border="0" cellspacing="1" cellpadding="1"><tr><td colspan="3">';
				$magictooloptionsproducts=str_replace(array('rel=','"'),array(';',''),@$magictooloptionsproducts);
				print (@$magictoolboxproducts!='' && $plargeimage!=''?'<a id="mzprodimage'.$Count.$magictooloptionsproducts.'" rel="group:g'.$Count.'" href="'.$plargeimage.'" class="' . $magictoolboxproducts . '">':$startlink).'<img id="prodimage'.$Count.'" class="'.@$cs.'prod3image" src="'.str_replace('%s','',$allimages[0]['imageSrc']).'" style="border:0" alt="'.str_replace('"', '&quot;', strip_tags($rs[getlangid('pName',1)])).'" />'.(@$magictoolboxproducts!='' && $plargeimage!=''?'</a>':$endlink);
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
			} ?></td>
<?php		break;
			case 'discounts': ?>
			<td class="cobll cpdll"><div class="prod3discounts"><?php
				if($alldiscounts!='') print $alldiscounts;
				if($noapplydiscounts!='') print '<div class="discountsnotapply">'.$GLOBALS['xxDsNoAp'].'</div>'.$noapplydiscounts;
				if(($rs['pExemptions']&16)==16&&$hasshippingdiscount&&@$GLOBALS['xxNoFrSh']!='') print '<div class="'.@$cs.'freeshippingexempt">'.$GLOBALS['xxNoFrSh'].'</div>'; elseif($alldiscounts=='') print '&nbsp;';?></div></td>
<?php		break;
			case 'details': ?>
			<td class="cobll cpdll"><div class="prod3details"><?php if($startlink!='') print $startlink . '<strong>' . $GLOBALS['xxPrDets'] . '</strong></a>&nbsp;'; else print '&nbsp;'; ?></div></td>
<?php		break;
			case 'options': ?>
			<td class="cobll cpdll">
<?php			if(! $updatepricecalled) docallupdatepricescript();
				print '<form method="post" name="tForm' . $Count . '" id="ectform' . $Count . '" action="cart.php" onsubmit="return formvalidator' . $Count . '(this)">';
				writehiddenvar('id', $rs['pId']);
				writehiddenvar('mode', 'add');
				if($wishlistonproducts) writehiddenvar('listid', '');
				print '<input type="hidden" name="quant" id="qnt'.$Count.'x" value=""/>';
				if(is_array($prodoptions)){
					if($hasmultipurchase==2)
						print '&nbsp;';
					else{
						print '<div class="prod3options"><table class="prodoptions" border="0" cellspacing="1" cellpadding="1" width="100%">';
						print $optionshtml . '</table></div>';
					}
				}else{
					print '&nbsp;';
				}
				print '</form>';
?>			</td>
<?php		break;
			case 'listprice': ?>
			<td class="cobll cpdll"><div class="prod3listprice"><?php if((double)$rs['pListPrice']!=0.0){ $plistprice=(@$GLOBALS['showtaxinclusive']===2 && ($rs['pExemptions'] & 2)!=2?$rs['pListPrice']+($rs['pListPrice']*$thetax/100.0):$rs['pListPrice']); print FormatEuroCurrency($plistprice) . (@$GLOBALS['yousavetext']!=''?str_replace('%s', FormatEuroCurrency($plistprice-(@$GLOBALS['showtaxinclusive']===2 && ($rs['pExemptions'] & 2)!=2 ? $rs['pPrice']+($rs['pPrice']*$thetax/100.0) : $rs['pPrice'])), $GLOBALS['yousavetext']):'');} else print '&nbsp;' ?></div></td>
<?php		break;
			case 'price': ?>
			<td class="cobll cpdll"><?php if(! $updatepricecalled) docallupdatepricescript(); ?><div class="prod3price"><?php
						if($rs['pId']==$giftcertificateid || $rs['pId']==$donationid)
							print '-';
						else
							print '<span class="price" id="pricediv' . $Count . '">' . ((double)$rs['pPrice']==0 && @$pricezeromessage!= '' ? $pricezeromessage : FormatEuroCurrency($rs['pPrice'])) . '</span>'; ?></div></td>
<?php		break;
			case 'priceinctax': ?>
			<td class="cobll cpdll"><div class="prod3pricetaxinc"><?php
						if($rs['pId']==$giftcertificateid || $rs['pId']==$donationid)
							print '-';
						elseif((double)$rs['pPrice']==0 && @$pricezeromessage!='')
							print '<span class="price" id="pricedivti' . $Count . '"> &nbsp; </span>';
						else{
							print '<span class="price" id="pricedivti' . $Count . '">';
							if(($rs['pExemptions'] & 2)==2) print FormatEuroCurrency($rs['pPrice']); else print FormatEuroCurrency($rs['pPrice']+($rs['pPrice']*$thetax/100.0));
							print '</span>';
						} ?></div></td>
<?php		break;
			case 'currency': ?>
			<td class="cobll cpdll"><?php
						$extracurr='';
						if($currRate1!=0 && $currSymbol1!='') $extracurr=str_replace('%s',number_format($rs['pPrice']*$currRate1,checkDPs($currSymbol1),$orcdecimals,$orcthousands),$currFormat1) . $currencyseparator;
						if($currRate2!=0 && $currSymbol2!='') $extracurr.=str_replace('%s',number_format($rs['pPrice']*$currRate2,checkDPs($currSymbol2),$orcdecimals,$orcthousands),$currFormat2) . $currencyseparator;
						if($currRate3!=0 && $currSymbol3!='') $extracurr.=str_replace('%s',number_format($rs['pPrice']*$currRate3,checkDPs($currSymbol3),$orcdecimals,$orcthousands),$currFormat3);
						if($rs['pPrice']==0 && @$pricezeromessage!='') $extracurr='';
						if($extracurr!='') print '<div class="prod3currency"><span class="extracurr" id="pricedivec' . $Count . '">' . $extracurr . '</span></div>';
						?></td>
<?php		break;
			case 'quantity': ?>
			<td class="cobll cpdll"><div class="prod3quant" style="white-space:nowrap"><?php if($hasmultipurchase>0) print '&nbsp;'; else print '<input type="text" id="w'.$Count.'quant" size="2" maxlength="5" value="1" title="'.$GLOBALS['xxQuant'].'" onchange="document.getElementById(\'qnt'.$Count.'x\').value=this.value" class="quantityinput" ' . (@$quantityupdown?'style="float:left" ':'') . '/>' . (@$quantityupdown?'<img src="images/quantarrowu.png" onclick="quantup('.$Count.',1)" alt="" /><br /><img src="images/quantarrowd.png" onclick="quantup('.$Count.',0)" alt="" />':'&nbsp;') ?></div></td>
<?php		break;
			case 'instock': ?>
			<td class="cobll cpdll"><div class="prod3instock"><?php if((int)$rs['pStockByOpts']!=0 || $rs['pId']==$giftcertificateid || $rs['pId']==$donationid) print '-'; else print max(0,$rs['pInStock']); ?></div></td>
<?php		break;
			case 'rating': ?>
			<td class="cobll cpdll"><?php if($rs['pNumRatings']>0) print showproductreviews(3, 'prod3rating'); else print '&nbsp;'; ?></td>
<?php		break;
			case 'buy': ?>
			<td class="cobll cpdll"><?php if(! $updatepricecalled) docallupdatepricescript(); ?><div class="prod3buy"><?php
	if($useStockManagement)
		if($rs['pStockByOpts']!=0) $isinstock=$optionshavestock; else $isinstock=((int)($rs['pInStock']) > 0);
	else
		$isinstock=($rs['pSell']!=0);
	if($rs['pPrice']==0 && @$nosellzeroprice==TRUE){
		print '&nbsp;';
	}else{
		$isbackorder=! $isinstock && $rs['pBackOrder']!=0;
		if(! $isinstock && !($useStockManagement && $hasmultipurchase==2) && $rs['pBackOrder']==0 && @$notifybackinstock!=TRUE){
			print '<div class="outofstock">' . $sstrong . $GLOBALS['xxOutStok'] . $estrong . '</div>';
		}elseif($hasmultipurchase==2)
			print imageorbutton(@$imgconfigoptions,$GLOBALS['xxConfig'],'configbutton',$thedetailslink, FALSE);
		else{
			$isbackorder=! $isinstock && $rs['pBackOrder']!=0;
			if($isbackorder)
				print imageorbutton(@$imgbackorderbutton,$GLOBALS['xxBakOrd'],'buybutton backorder',(@$usehardaddtocart?'subformid':'ajaxaddcart').'('.$Count.')', TRUE);
			elseif(! $isinstock && @$notifybackinstock)
				print '<div class="outofstock notifystock">' . imageorlink(@$imgnotifyinstock,$GLOBALS['xxNotBaS'],'',"return notifyinstock(false,'".str_replace("'","\\'",$rs['pId'])."','".str_replace("'","\\'",$rs['pId'])."',".($rs['pStockByOpts']!=0&&!@$optionshavestock?'-1':'0').")", TRUE) . '</div>';
			else
				print imageorbutton(@$imgbuybutton,$GLOBALS['xxAddToC'],'buybutton',(@$usehardaddtocart?'subformid':'ajaxaddcart').'('.$Count.')',TRUE);
			if($wishlistonproducts) print '<br />' . imageorlink(@$imgaddtolist,$GLOBALS['xxAddLis'],'','gtid='.$Count.';return displaysavelist(event,window)',TRUE);
		}
	}
?></div></td>
<?php		break;
			}
		}
		if(@$noproductoptions==TRUE){
			$nooptionshtml.='<form method="post" name="tForm'.$Count.'" id="ectform' . $Count . '" action="cart.php" onsubmit="return formvalidator'.$Count."(this)\">\r\n";
			$nooptionshtml.='<input type="hidden" name="quant" id="qnt'.$Count.'x" />';
			$nooptionshtml.='<input type="hidden" name="id" value="'. $rs['pId'].'" />';
			$nooptionshtml.='<input type="hidden" name="mode" value="add" />';
			if($wishlistonproducts) $nooptionshtml.='<input type="hidden" name="listid" value="" />';
			$nooptionshtml.="</form>\r\n";
		}
		print '</tr>';
		$Count++;
	}
	print '</table>' . $nooptionshtml . '</td></tr>';
	}
	if($iNumOfPages>1 && @$nobottompagebar<>TRUE){ ?>
		  <tr><td colspan="3" align="center" class="pagenums"><p class="pagenums"><?php print writepagebar($CurPage,$iNumOfPages,$GLOBALS['xxPrev'],$GLOBALS['xxNext'],$pblink,$nofirstpg); ?></p></td></tr>
<?php
	} ?>
		</table>
<?php if($defimagejs!='') print '<script type="text/javascript">'.$defimagejs.'</script>'; ?>