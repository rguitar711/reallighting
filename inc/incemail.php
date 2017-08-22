<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$digidownloadsecret=='') $digidownloadsecret='this is some secwet text';
if(@$GLOBALS['xxNoWtIn']=='') @$GLOBALS['xxNoWtIn']=' (Shipping Insurance Declined)';
if(@$dateformatstr=='') $dateformatstr='m/d/Y';
if(@$emailcr=='')$emailcr="\r\n";
function vrhmac($key, $text){
	$idatastr="                                                                ";
	$odatastr="                                                                ";
	$hkey=(string)$key;
	$idatastr.=$text;
	for($i=0; $i<64; $i++){
		$idata[$i]=$ipad[$i]=0x36;
		$odata[$i]=$opad[$i]=0x5C;
	}
	for($i=0; $i< strlen($hkey); $i++){
		$ipad[$i] ^= ord($hkey{$i});
		$opad[$i] ^= ord($hkey{$i});
		$idata[$i]=($ipad[$i] & 0xFF);
		$odata[$i]=($opad[$i] & 0xFF);
	}
	for($i=0; $i< strlen($text); $i++){
		$idata[64+$i]=ord($text{$i}) & 0xFF;
	}
	for($i=0; $i< strlen($idatastr); $i++){
		$idatastr{$i}=chr($idata[$i] & 0xFF);
	}
	for($i=0; $i< strlen($odatastr); $i++){
		$odatastr{$i}=chr($odata[$i] & 0xFF);
	}
	$innerhashout=md5($idatastr);
	for($i=0; $i<16; $i++)
		$odatastr.=chr(hexdec(substr($innerhashout,$i*2,2)));
	return md5($odatastr);
}
function order_success($sorderid,$sEmail,$sendstoreemail){
	do_order_success($sorderid,$sEmail,$sendstoreemail,TRUE,TRUE,TRUE,TRUE);
}
function getrecpt($oid){
	global $xxOrdId,$xxBilAdd,$xxShpAdd,$xxPhone,$xxEmail,$xxShpMet,$xxWtIns,$xxNoWtIn,$xxCerCLo,$xxSatDeR,$xxAddInf,$xxCODets,$xxCOName,$xxCOUPri,$xxQuant,$xxTotal,$xxSubTot,$xxDscnts,$xxShipHa,$xxFree,$xxShippg,$xxHndlg,$xxStaTax,$xxHST,$xxCntTax,$xxGndTot,$ordGrandTotal;
	global $extraorderfield1,$extraorderfield2,$extracheckoutfield1,$extracheckoutfield2,$combineshippinghandling,$hideoptpricediffs,$digidownloads,$willpickuptext,$emailcr,$loyaltypoints,$nomarkup,$xxGWrSel;
	$recpt='';
	$sSQL="SELECT ordID,ordName,ordLastName,ordAddress,ordAddress2,ordCity,ordState,ordZip,ordCountry,ordEmail,ordPhone,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipZip,ordShipCountry,ordShipPhone,ordPayProvider,ordAuthNumber,ordTotal,ordDate,ordStateTax,ordCountryTax,ordHSTTax,ordHandling,ordShipping,ordAffiliate,ordShipType,ordShipCarrier,ordDiscount,ordDiscountText,ordComLoc,ordExtra1,ordExtra2,ordShipExtra1,ordShipExtra2,ordCheckoutExtra1,ordCheckoutExtra2,ordSessionID,payProvID,ordAddInfo FROM orders INNER JOIN payprovider ON payprovider.payProvID=orders.ordPayProvider WHERE ordAuthNumber<>'' AND ordID='".escape_string($oid)."'";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		$recpt.='<table width="100%" border="0" cellspacing="1" cellpadding="3">';
		$recpt.='  <tr>';
		$recpt.='	<td align="left" colspan="5">';
		$recpt.='	  <table width="100%" border="0" cellspacing="1" cellpadding="3">';
		if(trim($rs['ordShipAddress'])!='') $hasshipaddress=TRUE; else $hasshipaddress=FALSE;
		$recpt.='		<tr>';
		$recpt.='		  <td align="right"><strong>'.$xxOrdId.':</strong></td>';
		$recpt.='		  <td align="left">'.$rs['ordID'].'</td>';
		$recpt.='		  <td>&nbsp;</td><td>&nbsp;</td>';
		$recpt.='		</tr>'.$emailcr;
		if(trim(@$extraorderfield1)!=''){
			$recpt.='	<tr>';
			$recpt.='	  <td align="right"><strong>'.$extraorderfield1.':</strong></td>';
			$recpt.='	  <td align="left">'.$rs['ordExtra1'].'</td>';
			if($hasshipaddress){
				$recpt.=' <td align="right"><strong>'.$extraorderfield1.':</strong></td>';
				$recpt.=' <td align="left">'.$rs['ordShipExtra1'].'</td>';
			}
			$recpt.='	</tr>'.$emailcr;
		}
		$recpt.='		<tr>';
		$recpt.='		  <td align="right" width="20%"><strong>'.$xxBilAdd.':</strong></td>';
		$recpt.='		  <td align="left">';
		$recpt.=trim($rs['ordName'].' '.$rs['ordLastName']) . '<br />';
		$recpt.=$rs['ordAddress'] . '<br />';
		if(trim($rs['ordAddress2'])!='') $recpt.=$rs['ordAddress2'] . '<br />';
		$recpt.=$rs['ordCity'] . ', ' . $rs['ordState'] . '<br />';
		$recpt.=$rs['ordZip'] . '<br />';
		$recpt.=$rs['ordCountry'] . '<br />';
		$recpt.='		  </td>';
		if($hasshipaddress){
			$recpt.='	  <td align="right" width="20%"><strong>'.$xxShpAdd.':</strong></td>';
			$recpt.='	  <td align="left">';
			$recpt.=trim($rs['ordShipName'].' '.$rs['ordShipLastName']) . '<br />';
			$recpt.=$rs['ordShipAddress'] . '<br />';
			if(trim($rs['ordShipAddress2'])!='') $recpt.=$rs['ordShipAddress2'] . '<br />';
			$recpt.=$rs['ordShipCity'] . ', ' . $rs['ordShipState'] . '<br />';
			$recpt.=$rs['ordShipZip'] . '<br />';
			$recpt.=$rs['ordShipCountry'] . '<br />';
			$recpt.='	  </td>';
		}
		$recpt.='		</tr>'.$emailcr;
		if(trim(@$extraorderfield2)!=''){
			$recpt.='	<tr>';
			$recpt.='	  <td align="right"><strong>'.$extraorderfield2.':</strong></td>';
			$recpt.='	  <td align="left">'.$rs['ordExtra2'].'</td>';
			if($hasshipaddress){
				$recpt.='  <td align="right"><strong>'.$extraorderfield2.':</strong></td>';
				$recpt.='  <td align="left">'.$rs['ordShipExtra2'].'</td>';
			}
			$recpt.='	</tr>'.$emailcr;
		}
		$recpt.='		<tr>';
		$recpt.='		  <td align="right"><strong>'.$xxPhone.':</strong></td>';
		$recpt.='		  <td align="left">'.$rs['ordPhone'].'</td>';
		if($hasshipaddress){
			$recpt.='	  <td align="right"><strong>'.$xxPhone.':</strong></td>';
			$recpt.='	  <td align="left">'.$rs['ordShipPhone'].'</td>';
		}
		$recpt.='		</tr>'.$emailcr;
		$recpt.='		<tr>';
		$recpt.='		  <td align="right"><strong>'.$xxEmail.':</strong></td>';
		$recpt.='		  <td align="left">'.$rs['ordEmail'].'</td>';
		$ordShipType=$rs['ordShipType'];
		if($ordShipType!=''){
			$shiptext='<td align="right"><strong>' . $xxShpMet . ':</strong></td><td align="left">' . $ordShipType;
			if(@$willpickuptext!=$ordShipType){
				if(($rs['ordComLoc'] & 2)==2) $shiptext.=$xxWtIns; elseif(@$GLOBALS['forceinsuranceselection']) $shiptext.=$xxNoWtIn;
			}
			$shiptext.='<br />';
			if(($rs['ordComLoc'] & 1)==1) $shiptext.=$xxCerCLo . '<br />';
			if(($rs['ordComLoc'] & 4)==4) $shiptext.=$xxSatDeR . '<br />';
			$shiptext.='</td>';
		}else
			$shiptext='';
		if($hasshipaddress){
			if($shiptext=='') $recpt.='<td>&nbsp;</td><td>&nbsp;</td>'; else $recpt.=$shiptext;
		}
		$recpt.='		</tr>'.$emailcr;
		if(! $hasshipaddress){
			if($shiptext!='') $recpt.='<tr>' . $shiptext . '</tr>';
		}
		if(trim(@$extracheckoutfield1)!='' && trim($rs['ordCheckoutExtra1'])!=''){
			$recpt.='	<tr>';
			$recpt.='	  <td align="right"><strong>'.$extracheckoutfield1.':</strong></td>';
			$recpt.='	  <td align="left"'.($hasshipaddress?' colspan="3"':'').'>'.$rs['ordCheckoutExtra1'].'</td>';
			$recpt.='	</tr>'.$emailcr;
		}
		if(trim(@$extracheckoutfield2)!='' && trim($rs['ordCheckoutExtra2'])!=''){
			$recpt.='	<tr>';
			$recpt.='	  <td align="right"><strong>'.$extracheckoutfield2.':</strong></td>';
			$recpt.='	  <td align="left"'.($hasshipaddress?' colspan="3"':'').'>'.$rs['ordCheckoutExtra2'].'</td>';
			$recpt.='	</tr>'.$emailcr;
		}
		if(@$loyaltypoints!='') $recpt.='<!--%loyaltypointplaceholder%-->';
		$ordAddInfo=trim($rs['ordAddInfo']);
		if($ordAddInfo!=''){
			$recpt.='	<tr>';
			$recpt.='	  <td align="right"><strong>'.$xxAddInf.':</strong></td>';
			$recpt.='	  <td align="left" colspan="3">'.str_replace(array("\r","\n"),array('','<br />'),$ordAddInfo).'</td>';
			$recpt.='	</tr>'.$emailcr;
		}
		if(@$digidownloads==TRUE) $recpt.='<!--%digidownloadplaceholder%-->';
		$recpt.='	  </table>';
		$recpt.='	</td>';
		$recpt.='  </tr>'.$emailcr;
		if(@$digidownloads==TRUE) $recpt.='<!--%digidownloaditems%-->';
		$recpt.='  <tr><td align="center" colspan="5"><hr class="receipthr" width="80%"></td></tr>';
		$recpt.='  <tr>';
		$recpt.='	<td class="receiptheading" width="15%" height="25" align="left"><strong>'.$xxCODets.'</strong></td>';
		$recpt.='	<td class="receiptheading" width="33%" height="25" align="left"><strong>'.$xxCOName.'</strong></td>';
		if(!@$GLOBALS['nopriceanywhere']) $recpt.='	<td class="receiptheading" width="14%" height="25" align="right"><strong>'.$xxCOUPri.'</strong></td>';
		$recpt.='	<td class="receiptheading" width="14%" height="25" align="right"><strong>'.$xxQuant.'</strong></td>';
		if(!@$GLOBALS['nopriceanywhere']) $recpt.='	<td class="receiptheading" width="14%" height="25" align="right"><strong>'.$xxTotal.'</strong></td>';
		$recpt.='  </tr>'.$emailcr;
	}
	ect_free_result($result);
	$sSQL="SELECT cartID,cartProdID,cartProdName,cartProdPrice,cartQuantity,cartGiftWrap FROM cart WHERE cartOrderID='" . escape_string($oid) . "' ORDER BY cartID";
	$result=ect_query($sSQL) or ect_error();
	while($alldata=ect_fetch_assoc($result)){
		$theoptions='';
		$theoptionspricediff=0;
		$isoutofstock=FALSE;
		$sSQL='SELECT coID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff,optAltImage FROM cartoptions LEFT JOIN options ON cartoptions.coOptID=options.optID WHERE coCartID='.$alldata['cartID'].' ORDER BY coID';
		$opts=ect_query($sSQL) or ect_error();
		while($rs2=ect_fetch_assoc($opts)){
			$theoptionspricediff+=$rs2['coPriceDiff'];
			$theoptions.='<tr>';
			$theoptions.='<td class="receiptoption" align="right"><span style="font-size:0.82em"><strong>'.$rs2['coOptGroup'].':</strong></span></td>';
			$theoptions.='<td class="receiptoption" align="left"><span style="font-size:0.82em">' . (strpos($rs2['coCartOption'],"\n")===FALSE?'&nbsp;- ':'') . str_replace(array("\r","\n",$emailcr),array('','<br />','<br />'),strip_tags($rs2['coCartOption'])) . '</span></td>';
			if(!@$GLOBALS['nopriceanywhere']) $theoptions.='<td class="receiptoption" align="right"><span style="font-size:0.82em">' . ($rs2['coPriceDiff']==0 || @$hideoptpricediffs==TRUE ? '- ' : FormatEuroCurrency($rs2['coPriceDiff'])) . '</span></td>';
			$theoptions.='<td class="receiptoption">&nbsp;</td>';
			if(!@$GLOBALS['nopriceanywhere']) $theoptions.='<td class="receiptoption" align="right"><span style="font-size:0.82em">' . ($rs2['coPriceDiff']==0 || @$hideoptpricediffs==TRUE ? '- ' : FormatEuroCurrency($rs2['coPriceDiff']*$alldata['cartQuantity'])) . '</span></td>';
			$theoptions.='</tr>'.$emailcr;
		}
		ect_free_result($opts);
		$recpt.='  <tr>';
		$recpt.='	<td class="cobhl receipthl" align="left" height="25"><strong>' . $alldata['cartProdID'] . '</strong></td>';
		$recpt.='	<td class="cobhl receipthl" align="left">'.$alldata['cartProdName'];
		if($alldata['cartGiftWrap']!=0) $recpt.='<div class="giftwrap">' . $xxGWrSel . '</div>';
		if(!@$GLOBALS['nopriceanywhere']) $recpt.='</td><td class="cobhl receipthl" align="right">'.(@$hideoptpricediffs==TRUE ? FormatEuroCurrency($alldata['cartProdPrice'] + $theoptionspricediff) : FormatEuroCurrency($alldata['cartProdPrice'])).'</td>';
		$recpt.='	<td class="cobhl receipthl" align="right">'.$alldata['cartQuantity'].'</td>';
		if(!@$GLOBALS['nopriceanywhere']) $recpt.='	<td class="cobhl receipthl" align="right">'.(@$hideoptpricediffs==TRUE ? FormatEuroCurrency(($alldata['cartProdPrice'] + $theoptionspricediff)*$alldata['cartQuantity']) : FormatEuroCurrency($alldata['cartProdPrice']*$alldata['cartQuantity'])).'</td>';
		$recpt.='  </tr>'.$emailcr;
		$recpt.=$theoptions;
	}
	if(!@$GLOBALS['nopriceanywhere']){
		$recpt.='	  <tr>';
		$recpt.='		<td colspan="3">&nbsp;</td>';
		$recpt.='		<td align="right"><strong>'.$xxSubTot.':</strong></td>';
		$recpt.='		<td align="right">'.FormatEuroCurrency($rs['ordTotal']).'</td>';
		$recpt.='	  </tr>'.$emailcr;
		if($rs['ordDiscount']>0){
			$recpt.=' <tr>';
			$recpt.='	<td colspan="3">&nbsp;</td>';
			$recpt.='	<td align="right"><strong>'.$xxDscnts.':</strong></td>';
			$recpt.='	<td class="recptdiscount" align="right" style="color:#FF0000">'.FormatEuroCurrency($rs['ordDiscount']).'</td>';
			$recpt.=' </tr>'.$emailcr;
		}
		if($rs['ordShipCarrier']==0&&(($rs['ordShipping']+$rs['ordHandling'])==0)){
			// Do nothing
		}elseif(@$combineshippinghandling==TRUE){
			$recpt.=' <tr>';
			$recpt.='	<td colspan="2">&nbsp;</td>';
			$recpt.='	<td colspan="2" align="right"><strong>'.$xxShipHa.':</strong></td>';
			$recpt.='	<td align="right">'. (($rs['ordShipping']+$rs['ordHandling'])==0?'<p align="center"><span style="color:#FF0000;font-weight:bold">' . $xxFree . '</span></p>':FormatEuroCurrency($rs['ordShipping']+$rs['ordHandling'])).'</td>';
			$recpt.=' </tr>'.$emailcr;
		}else{
			if($rs['ordShipping']>0){
				$recpt.='  <tr>';
				$recpt.='	<td colspan="3">&nbsp;</td>';
				$recpt.='	<td align="right"><strong>'.$xxShippg.':</strong></td>';
				$recpt.='	<td align="right">'.FormatEuroCurrency($rs['ordShipping']).'</td>';
				$recpt.='  </tr>'.$emailcr;
			}
			if($rs['ordHandling']>0){
				$recpt.='	  <tr>';
				$recpt.='		<td colspan="3">&nbsp;</td>';
				$recpt.='		<td align="right"><strong>'.$xxHndlg.':</strong></td>';
				$recpt.='		<td align="right">'.FormatEuroCurrency($rs['ordHandling']).'</td>';
				$recpt.='	  </tr>'.$emailcr;
			}
		}
		if($rs['ordStateTax']>0){
			$recpt.='  <tr>';
			$recpt.='	<td colspan="3">&nbsp;</td>';
			$recpt.='	<td align="right"><strong>'.$xxStaTax.':</strong></td>';
			$recpt.='	<td align="right">'.FormatEuroCurrency($rs['ordStateTax']).'</td>';
			$recpt.='  </tr>'.$emailcr;
		}
		if($rs['ordHSTTax']>0){
			$recpt.='  <tr>';
			$recpt.='	<td colspan="3">&nbsp;</td>';
			$recpt.='	<td align="right"><strong>'.$xxHST.':</strong></td>';
			$recpt.='	<td align="right">'.FormatEuroCurrency($rs['ordHSTTax']).'</td>';
			$recpt.='  </tr>'.$emailcr;
		}
		if($rs['ordCountryTax']>0){
			$recpt.='  <tr>';
			$recpt.='	<td colspan="3">&nbsp;</td>';
			$recpt.='	<td align="right"><strong>'.$xxCntTax.':</strong></td>';
			$recpt.='	<td align="right">'.FormatEuroCurrency($rs['ordCountryTax']).'</td>';
			$recpt.='  </tr>'.$emailcr;
		}
		$recpt.='	  <tr>';
		$recpt.='		<td colspan="3">&nbsp;</td>';
		$recpt.='		<td class="cobhl receipthl" align="right"><strong>'.$xxGndTot.':</strong></td>';
		$recpt.='		<td class="cobhl receipthl" align="right">'.FormatEuroCurrency($ordGrandTotal).'</td>';
		$recpt.='	  </tr>'.$emailcr;
		$recpt.='	  <tr>';
		$recpt.='		<td align="center" colspan="5">&nbsp;</td>';
		$recpt.='	  </tr>';
	}
	$recpt.='	</table>'.$emailcr;
	return($recpt);
}
function do_order_success($sorderid,$sEmail,$sendstoreemail,$doshowhtml,$sendcustemail,$sendaffilemail,$sendmanufemail){
	global $thereference,$emlNl,$htmlemails,$extraorderfield1,$extraorderfield2,$extraorderfield3,$shipType,$emailheader,$emailfooter,$emailencoding,$hideoptpricediffs,$xxWtIns,$xxNoWtIn,$ordGrandTotal,$ordID,$digidownloads,$dropshipfooter,$dropshipheader,$digidownloademail,$xxPrint,$dropshipsubject,$xxHST,$encodecustomeremailsubject,$imgcontinueshopping,$imgprintversion,$giftcertificateid,$dateformatstr,$recpt,$receiptheader,$xxCerCLo;
	global $xxHndlg,$xxDscnts,$xxOrdId,$xxCusDet,$xxEmail,$xxPhone,$xxShpDet,$xxShpMet,$xxAddInf,$xxPrId,$xxPrNm,$xxQuant,$xxUnitPr,$xxOrdTot,$xxStaTax,$xxCntTax,$xxShippg,$xxGndTot,$xxOrdStr,$xxTnxOrd,$xxTouSoo,$xxAff1,$xxAff2,$xxAff3,$xxThnks,$xxThkYou,$xxRecEml,$storeurl,$xxHomeURL,$xxCntShp,$success,$ordAuthNumber,$orderText,$ordTotal,$digidownloadsecret,$useaddressline2,$combineshippinghandling,$xxShipHa,$extracheckoutfield1,$extracheckoutfield2;
	global $ordStateTax,$ordHSTTax,$ordCountryTax,$ordShipping,$ordHandling,$ordDiscount,$ordCity,$ordState,$ordCountry,$ordEmail,$affilID,$xxDigPro,$thankspagecontinue,$willpickuptext,$xxManRev,$emailcr,$loyaltypoints,$xxLoyPoi,$xxGWrSel,$xxGifMes,$xxSatDeR,$languageid;
	$success=TRUE;
	if(@$htmlemails==TRUE){
		$emlNl='<br />'.$emailcr;
		$xxThkYou='';
	}else
		$emlNl=$emailcr;
	$affilID='';
	$saveHeader='';
	if(! is_numeric($sorderid)){
		print '&nbsp;<br />&nbsp;<br />&nbsp;<br /><p align="center">Illegal Order ID</p><br />&nbsp;';
		return(FALSE);
	}
	$ordID=$sorderid;
	$hasdownload=FALSE;
	$orderloyaltypoints=0;
	$ordClientID=0;
	$ndropshippers=0;
	$savelangid=$languageid;
	$sSQL="SELECT ordID,ordName,ordLastName,ordAddress,ordAddress2,ordCity,ordState,ordZip,ordCountry,ordEmail,ordPhone,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipZip,ordShipCountry,ordShipPhone,ordPayProvider,ordAuthNumber,ordAuthStatus,ordTotal,ordDate,ordStateTax,ordCountryTax,ordHSTTax,ordHandling,ordShipping,ordAffiliate,ordDiscount,ordDiscountText,ordComLoc,ordExtra1,ordExtra2,ordShipExtra1,ordShipExtra2,ordCheckoutExtra1,ordCheckoutExtra2,ordSessionID,ordLang,ordAddInfo,ordShipType,payProvID,ordClientID,loyaltyPoints FROM orders LEFT JOIN payprovider ON payprovider.payProvID=orders.ordPayProvider WHERE ordAuthNumber<>'' AND ordID='" . escape_string($sorderid) . "'";
	$result=ect_query($sSQL) or ect_error();
	if(ect_num_rows($result) > 0){
		$rs=ect_fetch_assoc($result);
		$languageid=$rs['ordLang']+1;
		$orderloyaltypoints=$rs['loyaltyPoints'];
		$ordClientID=$rs['ordClientID'];
		$orderText='';
		$ordAuthNumber=$rs['ordAuthNumber'];
		$ordSessionID=$rs['ordSessionID'];
		$payprovid=$rs['payProvID'];
		$ordName=trim($rs['ordName'] . ' ' . $rs['ordLastName']);
		$ordDate=$rs['ordDate'];
		$ordDateToTime=strtotime($ordDate);
		if($rs['ordShipType']=='MODWARNOPEN'){
			print '<div style="font-weight:bold;text-align:center">&nbsp;<br />&nbsp;<br />&nbsp;<br />' . $xxManRev . '&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />';
			print imageorbutton(@$imgcontinueshopping,'&nbsp;'.$xxCntShp.'&nbsp;','continueshopping',(@$thankspagecontinue!=''?$thankspagecontinue:$storeurl), (@$thankspagecontinue=='javascript:history.go(-1)'?TRUE:FALSE)).'&nbsp;';
			print '</div>';
			$success=FALSE;
			ect_free_result($result);
			return;
		}
		$sSQL='SELECT '.getlangid('emailsubject',4096).','.getlangid('emailheaders',4096).','.getlangid('receiptheaders',4096).','.getlangid('dropshipsubject',4096).','.getlangid('dropshipheaders',4096).' FROM emailmessages WHERE emailID=1';
		$result2=ect_query($sSQL) or ect_error();
		if($rs2=ect_fetch_assoc($result2)){
			$emailsubject=trim($rs2[getlangid('emailsubject',4096)]);
			$emailheader=trim($rs2[getlangid('emailheaders',4096)]);
			$emailheader=str_replace('%emailmessage%','%messagebody%',$emailheader);
			$receiptheader=trim($rs2[getlangid('receiptheaders',4096)]);
			if(strpos($emailheader, '%messagebody%')===FALSE) $emailheader.='%messagebody%';
			if(strpos($receiptheader, '%messagebody%')===FALSE) $receiptheader.='%messagebody%';
			$dropshipsubject=trim($rs2[getlangid('dropshipsubject',4096)]);
			$dropshipheader=trim($rs2[getlangid('dropshipheaders',4096)]);
			$dropshipheader=str_replace('%emailmessage%','%messagebody%',$dropshipheader);
			if(strpos($dropshipheader, '%messagebody%')===FALSE) $dropshipheader.='%messagebody%';
		}
		ect_free_result($result2);
		$sSQL='SELECT '.getlangid('pProvHeaders',4096).','.getlangid('pProvDropShipHeaders',4096).' FROM payprovider WHERE payProvID=' . $payprovid;
		$result2=ect_query($sSQL) or ect_error();
		if($rs2=ect_fetch_assoc($result2)){
			$payprovheader=trim($rs2[getlangid('pProvHeaders',4096)]);
			$payprovheader=str_replace('%emailmessage%','%messagebody%',$payprovheader);
			if(strpos($payprovheader, '%messagebody%')===FALSE) $payprovheader.='%messagebody%';
			$payprovdropshipheader=trim($rs2[getlangid('pProvDropShipHeaders',4096)]);
			$payprovdropshipheader=str_replace('%emailmessage%','%messagebody%',$payprovdropshipheader);
			if(strpos($payprovdropshipheader, '%messagebody%')===FALSE) $payprovdropshipheader.='%messagebody%';
		}
		ect_free_result($result2);
		$emailheader=str_replace('%messagebody%', $payprovheader, $emailheader);
		$dropshipheader=str_replace('%messagebody%', $payprovdropshipheader, $dropshipheader);
		$emailheader=str_replace('%nl%', '<br />', str_replace('%ordername%', $ordName, $emailheader));
		if(@$fordertimeformatstr!='') setlocale(LC_TIME, $adminLocale);
		$emailheader=str_replace('%orderdate%', (@$fordertimeformatstr!='' ? strftime($fordertimeformatstr, $ordDateToTime) : date($dateformatstr, $ordDateToTime) . ' ' . date('H:i', $ordDateToTime)), $emailheader);
		$receiptheader=str_replace('%nl%', '<br />', str_replace('%ordername%', $ordName, $receiptheader));
		$receiptheader=str_replace('%orderdate%', (@$fordertimeformatstr!='' ? strftime($fordertimeformatstr, $ordDateToTime) : date($dateformatstr, $ordDateToTime) . ' ' . date('H:i', $ordDateToTime)), $receiptheader);
		$dropshipheader=str_replace('%nl%', '<br />', str_replace('%ordername%', $ordName, $dropshipheader));
		$dropshipheader=str_replace('%orderdate%', (@$fordertimeformatstr!='' ? strftime($fordertimeformatstr, $ordDateToTime) : date($dateformatstr, $ordDateToTime) . ' ' . date('H:i', $ordDateToTime)), $dropshipheader);

		$orderText.=$xxOrdId . ': ' . $rs['ordID'] . '<br />';
		if($thereference!='') $orderText.='Transaction Ref' . ': ' . $thereference . '<br />';
		$orderText.=$xxCusDet . ': ' . '<br />';
		if(trim(@$extraorderfield1)!='') $orderText.=$extraorderfield1 . ': ' . $rs['ordExtra1'] . '<br />';
		$orderText.=$ordName . '<br />';
		$orderText.=$rs['ordAddress'] . '<br />';
		if(trim($rs['ordAddress2'])!='') $orderText.=$rs['ordAddress2'] . '<br />';
		$orderText.=$rs['ordCity'] . ', ' . $rs['ordState'] . '<br />';
		$orderText.=$rs['ordZip'] . '<br />';
		$orderText.=$rs['ordCountry'] . '<br />';
		$orderText.=$xxEmail . ': ' . $rs['ordEmail'] . '<br />';
		$custEmail=$rs['ordEmail'];
		$orderText.=$xxPhone . ': ' . $rs['ordPhone'] . '<br />';
		if(trim(@$extraorderfield2)!='') $orderText.=$extraorderfield2 . ': ' . $rs['ordExtra2'] . '<br />';
		if(trim($rs['ordShipName'])!='' || trim($rs['ordShipLastName'])!='' || trim($rs['ordShipAddress'])!=''){
			$orderText.=$xxShpDet . ': ' . '<br />';
			if(trim(@$extraorderfield1)!='' && trim($rs['ordShipExtra1'])!='') $orderText.=$extraorderfield1 . ': ' . $rs['ordShipExtra1'] . '<br />';
			$orderText.=$rs['ordShipName'] . ($rs['ordShipName']!='' && $rs['ordShipLastName']!='' ? ' ' : '') . $rs['ordShipLastName'] . '<br />';
			$orderText.=$rs['ordShipAddress'] . '<br />';
			if(trim($rs['ordShipAddress2'])!='') $orderText.=$rs['ordShipAddress2'] . '<br />';
			$orderText.=$rs['ordShipCity'] . ', ' . $rs['ordShipState'] . '<br />';
			$orderText.=$rs['ordShipZip'] . '<br />';
			$orderText.=$rs['ordShipCountry'] . '<br />';
			if(trim($rs['ordShipPhone']!='')) $orderText.=$xxPhone . ': ' . $rs['ordShipPhone'] . '<br />';
			if(trim(@$extraorderfield2)!='' && trim($rs['ordShipExtra2'])!='') $orderText.=$extraorderfield2 . ': ' . $rs['ordShipExtra2'] . '<br />';
		}
		$ordShipType=$rs['ordShipType'];
		if($ordShipType!=''){
			$orderText.='<br />' . $xxShpMet . ': ' . $ordShipType;
			if(@$willpickuptext!=$ordShipType){
				if(($rs['ordComLoc'] & 2)==2) $orderText.=$xxWtIns; elseif(@$GLOBALS['forceinsuranceselection']) $orderText.=$xxNoWtIn;
			}
			$orderText.='<br />';
			if(($rs['ordComLoc'] & 1)==1) $orderText.=$xxCerCLo . '<br />';
			if(($rs['ordComLoc'] & 4)==4) $orderText.=$xxSatDeR . '<br />';
		}
		if(trim(@$extracheckoutfield1)!='' && trim($rs['ordCheckoutExtra1'])!='') $orderText.=$extracheckoutfield1 . ': ' . $rs['ordCheckoutExtra1'] . '<br />';
		if(trim(@$extracheckoutfield2)!='' && trim($rs['ordCheckoutExtra2'])!='') $orderText.=$extracheckoutfield2 . ': ' . $rs['ordCheckoutExtra2'] . '<br />';
		$ordAddInfo=trim($rs['ordAddInfo']);
		if($ordAddInfo!=''){
			$orderText.='<br />' . $xxAddInf . ': ' . '<br />';
			$orderText.=str_replace(array("\r","\n"),array('','<br />'),$ordAddInfo) . '<br />';
		}
		$ordTotal=$rs['ordTotal'];
		$ordStateTax=$rs['ordStateTax'];
		$ordDiscount=$rs['ordDiscount'];
		$ordDiscountText=$rs['ordDiscountText'];
		$ordCountryTax=$rs['ordCountryTax'];
		$ordHSTTax=$rs['ordHSTTax'];
		$ordShipping=$rs['ordShipping'];
		$ordHandling=$rs['ordHandling'];
		$affilID=trim($rs['ordAffiliate']);
		$ordCity=$rs['ordCity'];
		$ordState=$rs['ordState'];
		$ordCountry=$rs['ordCountry'];
		$ordEmail=$rs['ordEmail'];
	}else{
		$sendstoreemail=FALSE;
		$sendcustemail=FALSE;
		$sendaffilemail=FALSE;
		$sendmanufemail=FALSE;
		print '&nbsp;<br />&nbsp;<br />&nbsp;<br /><p align="center">Cannot find details for order id: ' . $sorderid . '</p><br />&nbsp;';
		return(FALSE);
	}
	ect_free_result($result);
	$saveCustomerDetails=$orderText;
	if(@$loyaltypoints!='') $orderText.='%loyaltypointplaceholder%';
	$orderText.='%digidownloadplaceholder%';
	$reviewlinks='';
	$loyaltypointtotal=0;
	$sSQL='SELECT cartProdId,cartOrigProdId,cartProdName,cartProdPrice,cartQuantity,cartID,cartGiftWrap,pDropship,pDisplay,pStaticPage,pStaticURL,pSKU'.(@$digidownloads==TRUE?',pDownload':'').",cartGiftMessage FROM cart LEFT JOIN products ON cart.cartProdId=products.pID WHERE cartOrderID='" . escape_string($sorderid) . "' ORDER BY cartID";
	$result=ect_query($sSQL) or ect_error();
	if(ect_num_rows($result) > 0){
		while($rs=ect_fetch_assoc($result)){
			if($rs['cartProdId']==$giftcertificateid){
				$sSQL='UPDATE giftcertificate SET gcAuthorized=1,gcOrigAmount='.$rs['cartProdPrice'].',gcRemaining='.$rs['cartProdPrice'].' WHERE gcCartID=' . $rs['cartID'];
				ect_query($sSQL) or ect_error();
				if($sendcustemail){
					$sSQL='SELECT '.getlangid('giftcertsubject',4096).','.getlangid('giftcertemail',4096).','.getlangid('giftcertsendersubject',4096).','.getlangid('giftcertsender',4096).' FROM emailmessages WHERE emailID=1';
					$result2=ect_query($sSQL) or ect_error();
					if($rs2=ect_fetch_assoc($result2)){
						$giftcertsubject=trim($rs2[getlangid('giftcertsubject',4096)]);
						$emailBody=trim($rs2[getlangid('giftcertemail',4096)]);
						$senderSubject=trim($rs2[getlangid('giftcertsendersubject',4096)]);
						$senderBody=trim($rs2[getlangid('giftcertsender',4096)]);
					}
					ect_free_result($result2);
					$sSQL='SELECT gcID,gcTo,gcFrom,gcEmail,gcMessage FROM giftcertificate WHERE gcCartID='.$rs['cartID'];
					$result2=ect_query($sSQL) or ect_error();
					if($rs2=ect_fetch_assoc($result2)){
						$emailBody=str_replace('%toname%', $rs2['gcTo'], $emailBody);
						$emailBody=str_replace('%fromname%', $rs2['gcFrom'], $emailBody);
						$emailBody=str_replace('%value%', FormatEuroCurrency($rs['cartProdPrice']), $emailBody);
						$emailBody=replaceemailtxt($emailBody, '%message%', trim($rs2['gcMessage']), $replaceone);
						$emailBody=str_replace('%storeurl%', $storeurl, $emailBody);
						$emailBody=str_replace('%certificateid%', $rs2['gcID'], $emailBody);
						$emailBody=str_replace('<br />', $emlNl, $emailBody);
						dosendemail($rs2['gcEmail'], $sEmail, '', str_replace('%fromname%', $rs2['gcFrom'], $giftcertsubject), $emailBody);
						$senderBody=str_replace('%toname%', $rs2['gcTo'], $senderBody);
						dosendemail($custEmail, $sEmail, '', str_replace('%toname%', $rs2['gcTo'], $senderSubject), $senderBody . $emlNl . $emailBody);
					}
					ect_free_result($result2);
				}
			}
			$reviewprodid=$rs['cartProdId'];
			$reviewisstatic=$rs['pStaticPage'];
			$reviewstaticurl=$rs['pStaticURL'];
			$reviewprodname=$rs['cartProdName'];
			if(trim($rs['cartOrigProdId'])!=''){
				$sSQL="SELECT pName,pStaticPage,pStaticURL FROM products WHERE pID='".escape_string($rs['cartOrigProdId'])."'";
				$result2=ect_query($sSQL) or ect_error();
				if($rs2=ect_fetch_assoc($result2)){
					$reviewprodid=$rs['cartOrigProdId'];
					$reviewisstatic=$rs2['pStaticPage'];
					$reviewstaticurl=$rs2['pStaticURL'];
					$reviewprodname=$rs2['pName'];
				}
				ect_free_result($result2);
			}
			if(is_null($reviewisstatic))
				$thelink='';
			else
				$thelink=$storeurl . getdetailsurl($reviewprodid,$reviewisstatic,$reviewprodname,$reviewstaticurl,'review=true','');
			if(@$htmlemails==TRUE&&$thelink!='') $thelink='<a href="' . $thelink . '">' . $thelink . '</a>'; else $thelink=str_replace('&amp;','&',$thelink);
			if($thelink!='') $reviewlinks.=$thelink . $emlNl;
			$localhasdownload=FALSE;
			if(@$digidownloads==TRUE)
				if(trim($rs['pDownload'])!='') $localhasdownload=TRUE;
			$saveCartItems='--------------------------' . '<br />';
			$saveCartItems.=$xxPrId . ': ' . $rs['cartProdId'] . '<br />';
			$saveCartItems.=$xxPrNm . ': ' . $rs['cartProdName'] . '<br />';
			$saveCartItems.=$xxQuant . ': ' . $rs['cartQuantity'] . '<br />';
			if($rs['cartGiftWrap']!=0){
				$saveCartItems.=$xxGWrSel . '<br />';
				if($rs['cartGiftMessage']!='')
					$saveCartItems.=$xxGifMes . ': ' . $rs['cartGiftMessage'] . '<br />';
			}
			$orderText.=$saveCartItems;
			$theoptions='';
			$theoptionspricediff=0;
			$sSQL='SELECT coOptGroup,coCartOption,coPriceDiff,optRegExp FROM cartoptions LEFT JOIN options ON cartoptions.coOptID=options.optID WHERE coCartID=' . $rs['cartID'] . ' ORDER BY coID';
			$result2=ect_query($sSQL) or ect_error();
			while($rs2=ect_fetch_assoc($result2)){
				$theoptionspricediff+=$rs2['coPriceDiff'];
				$optionline=(@$htmlemails==true?'&nbsp;&nbsp;&nbsp;&nbsp;>&nbsp;':'> > > ') . $rs2['coOptGroup'] . ' : ' . str_replace(array("\r\n",$emailcr),array('<br />','<br />'),$rs2['coCartOption']);
				$theoptions.=$optionline;
				$saveCartItems.=$optionline . '<br />';
				if($rs2['coPriceDiff']==0 || @$hideoptpricediffs==TRUE || @$GLOBALS['nopriceanywhere'])
					$theoptions.='<br />';
				else{
					$theoptions.=' (';
					if($rs2['coPriceDiff'] > 0) $theoptions.='+';
					$theoptions.=FormatEmailEuroCurrency($rs2['coPriceDiff']) . ')' . '<br />';
				}
				if($rs2['optRegExp']=='!!') $localhasdownload=FALSE;
			}
			if(!@$GLOBALS['nopriceanywhere']) $orderText.=$xxUnitPr . ': ' . (@$hideoptpricediffs==TRUE ? FormatEmailEuroCurrency($rs['cartProdPrice'] + $theoptionspricediff) : FormatEmailEuroCurrency($rs['cartProdPrice'])) . '<br />';
			$orderText.=$theoptions;
			if($rs['pDropship'] != 0){
				$index=0;
				for($index=0; $index<$ndropshippers; $index++){
					if($dropShippers[$index][0]==$rs['pDropship']) break;
				}
				if($index>=$ndropshippers){
					$ndropshippers=$index+1;
					$dropShippers[$index][1]='';
				}
				$dropShippers[$index][0]=$rs['pDropship'];
				$dropShippers[$index][1].=$saveCartItems . ($rs['pSKU']!=''?'SKU:'.$rs['pSKU'].'<br />':'');
			}
			if($localhasdownload==TRUE) $hasdownload=TRUE;
			ect_free_result($result2);
		}
		$orderText.='--------------------------' . '<br />';
		if(!@$GLOBALS['nopriceanywhere']){
			$orderText.=$xxOrdTot . ' : ' . FormatEmailEuroCurrency($ordTotal) . '<br />';
			if(@$combineshippinghandling==TRUE){
				$orderText.=$xxShipHa . ' : ' . FormatEmailEuroCurrency($ordShipping+$ordHandling) . '<br />';
			}else{
				if($shipType != 0) $orderText.=$xxShippg . ' : ' . FormatEmailEuroCurrency($ordShipping) . '<br />';
				if((double)$ordHandling!=0.0) $orderText.=$xxHndlg . ' : ' . FormatEmailEuroCurrency($ordHandling) . '<br />';
			}
			if((double)$ordDiscount!=0.0) $orderText.=$xxDscnts . ' : ' . FormatEmailEuroCurrency($ordDiscount) . '<br />';
			if((double)$ordStateTax!=0.0) $orderText.=$xxStaTax . ' : ' . FormatEmailEuroCurrency($ordStateTax) . '<br />';
			if((double)$ordCountryTax!=0.0) $orderText.=$xxCntTax . ' : ' . FormatEmailEuroCurrency($ordCountryTax) . '<br />';
			if((double)$ordHSTTax!=0.0) $orderText.=$xxHST . ' : ' . FormatEmailEuroCurrency($ordHSTTax) . '<br />';
			$ordGrandTotal=($ordTotal+$ordStateTax+$ordCountryTax+$ordHSTTax+$ordShipping+$ordHandling)-$ordDiscount;
			$orderText.=$xxGndTot . ' : ' . FormatEmailEuroCurrency($ordGrandTotal) . '<br />';
		}
	}else{
		print '&nbsp;<br />&nbsp;<br />&nbsp;<br /><p align="center">Cannot find details for cart id: ' . $sorderid . '</p><br />&nbsp;';
		return(FALSE);
	}
	ect_free_result($result);
	if(@$loyaltypoints!='' && $orderloyaltypoints==0 && $sendmanufemail){
		$loyaltypointtotal=(int)(($ordTotal-$ordDiscount)*$loyaltypoints);
		if($loyaltypointtotal>0){
			if(@$GLOBALS['loyaltypointsnowholesale'] || @$GLOBALS['loyaltypointsnopercentdiscount']){
				$sSQL="SELECT clActions FROM customerlogin WHERE clID=" . $ordClientID;
				$result=ect_query($sSQL) or ect_error();
				if($rs=ect_fetch_assoc($result)){
					if(@$GLOBALS['loyaltypointsnowholesale'] && ($rs['clActions'] & 8)==8) $loyaltypointtotal=0;
					if(@$GLOBALS['loyaltypointsnopercentdiscount'] && ($rs['clActions'] & 16)==16) $loyaltypointtotal=0;
				}
				ect_free_result($result);
			}
			$sSQL="UPDATE orders SET loyaltyPoints=" . $loyaltypointtotal . " WHERE ordID='" . escape_string($sorderid) . "'";
			ect_query($sSQL) or ect_error();
			$sSQL="UPDATE customerlogin SET loyaltyPoints=loyaltyPoints+" . $loyaltypointtotal . " WHERE clID=" . $ordClientID;
			ect_query($sSQL) or ect_error();
		}
	}
	$numloyaltypoints=0;
	if($loyaltypoints!=''){
		$sSQL="SELECT loyaltyPoints FROM orders WHERE ordID='" . escape_string($sorderid) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result))
			$numloyaltypoints=$rs['loyaltyPoints'];
		ect_free_result($result);
		if($numloyaltypoints>0)
			$orderText=str_replace('%loyaltypointplaceholder%',$xxLoyPoi . ': ' . $numloyaltypoints . '<br />',$orderText);
		else
			$orderText=str_replace('%loyaltypointplaceholder%','',$orderText);
	}
	if($hasdownload==TRUE && @$digidownloademail!=''){
		$fingerprint=vrhmac($digidownloadsecret, $sorderid . $ordAuthNumber . $ordSessionID);
		$fingerprint=substr($fingerprint, 0, 14);
		$digidownloademail=str_replace('%orderid%',$ordID,$digidownloademail);
		$digidownloademail=str_replace('%password%',$fingerprint,$digidownloademail);
		$digidownloademail=str_replace('%nl%',$emlNl,$digidownloademail);
		$orderEmailText=str_replace('%digidownloadplaceholder%',$digidownloademail,$orderText);
	}else
		$orderEmailText=str_replace('%digidownloadplaceholder%',"",$orderText);
	$orderText=str_replace('%digidownloadplaceholder%',"",$orderText);
	$emailheader=replaceemailtxt($emailheader, '%reviewlinks%', $reviewlinks, $replaceone);
	$receiptheader=replaceemailtxt($receiptheader, '%reviewlinks%', $reviewlinks, $replaceone);
	$recpt=getrecpt($ordID);
	if(@$loyaltypoints!=''){
		if($numloyaltypoints>0)
			$recpt=str_replace('<!--%loyaltypointplaceholder%-->','<tr><td align="right"><strong>' . $xxLoyPoi . ':</strong></td><td align="left" colspan="3">' . $numloyaltypoints . '</td></tr>',$recpt);
		else
			$recpt=str_replace('<!--%loyaltypointplaceholder%-->','',$recpt);
	}
	if($hasdownload==FALSE){
		$recpt=str_replace('<!--%digidownloadplaceholder%-->','',$recpt);
		$recpt=str_replace('<!--%digidownloaditems%-->','',$recpt);
	}
	$emlhdrs='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3c.org/TR/1999/REC-html401-19991224/loose.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><style type="text/css">body{font-size:11px; font-family: Tahoma, Helvetica, Arial, Verdana}hr{height: 0;border-width: 1px 0 0 0;border-style: solid;border-color: #006AC8;}</style></head>';
	if($sendstoreemail&&$GLOBALS['allStoreEmails']!=''){
		$allemailsarray=explode(',',$GLOBALS['allStoreEmails']);
		foreach($allemailsarray as $sstoreemail){
			if(@$htmlemails==TRUE)
				dosendemail($sstoreemail, $sEmail, $custEmail, str_replace(array('%orderid%','%ordername%'),array($sorderid,$ordName),$xxOrdStr),$emlhdrs . '<body class="receiptbody">' . str_replace('<br />', $emlNl, str_replace('%messagebody%', str_replace('<!--%digidownloadplaceholder%-->','<tr><td align="right"><strong>'.$xxDigPro.':</strong></td><td align="left" colspan="3">' . $digidownloademail . '</td></tr>',$recpt), $emailheader)) . '</body></html>');
			else
				dosendemail($sstoreemail, $sEmail, $custEmail, str_replace(array('%orderid%','%ordername%'),array($sorderid,$ordName),$xxOrdStr),str_replace('<br />', $emlNl, str_replace('%messagebody%', $orderEmailText, $emailheader)));
		}
	}
	// And one for the customer
	if($sendcustemail){
		$thesubject=str_replace('%ordername%', $ordName, $xxTnxOrd);
		if(@$encodecustomeremailsubject==TRUE) $thesubject=encodeemailsubject($thesubject, $emailencoding);
		if(@$htmlemails==TRUE)
			dosendemail($custEmail, $sEmail, '', str_replace(array('%orderid%','%ordername%'),array($sorderid,$ordName),$emailsubject),$emlhdrs . '<body class="receiptbody">' . str_replace('<br />', $emlNl, str_replace('%messagebody%', str_replace('<!--%digidownloadplaceholder%-->','<tr><td align="right"><strong>'.$xxDigPro.':</strong></td><td align="left" colspan="3">' . $digidownloademail . '</td></tr>',$recpt), $emailheader)) . '</body></html>');
		else
			dosendemail($custEmail, $sEmail, '', str_replace(array('%orderid%','%ordername%'),array($sorderid,$ordName),$emailsubject),(trim(@$xxTouSoo)!='' ? $xxTouSoo . $emlNl . $emlNl : '') . str_replace('<br />', $emlNl, str_replace('%messagebody%', $orderEmailText, $emailheader)));
	}
	$languageid=$savelangid;
	// Drop Shippers
	if($sendmanufemail){
		for($index=0; $index < $ndropshippers; $index++){
			$sSQL='SELECT dsEmail,dsAction,dsEmailHeader FROM dropshipper WHERE dsID=' . $dropShippers[$index][0];
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				if(($rs['dsAction'] & 1)==1 || (int)$sendmanufemail==2){
					dosendemail($rs['dsEmail'], $sEmail, '', str_replace('%orderid%',$sorderid,$dropshipsubject), str_replace('<br />', $emlNl, str_replace('%messagebody%', (trim($rs['dsEmailHeader'])!='' ? $emlNl . str_replace('%nl%', $emlNl, $rs['dsEmailHeader']) . $emlNl : '') . $saveCustomerDetails . $dropShippers[$index][1], $dropshipheader)));
				}
			}
			ect_free_result($result);
		}
	}
	if($sendaffilemail){
		if($affilID!=''){
			$sSQL="SELECT affilEmail,affilInform FROM affiliates WHERE affilID='" . escape_string($affilID) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				if((int)$rs['affilInform']==1){
					$affiltext=$xxAff1 . ' ' . FormatEmailEuroCurrency($ordTotal-$ordDiscount) . '.'.$emlNl.$emlNl.$xxAff2.$emlNl.$emlNl.$xxThnks.$emlNl;
					dosendemail(trim($rs['affilEmail']), $sEmail, '', str_replace('%orderid%',$sorderid,$xxAff3), $emlNl . $affiltext);
				}
			}
			ect_free_result($result);
		}
	}
	if($doshowhtml){
?>
<script type="text/javascript">
<!--
function doprintcontent()
{
	var prnttext='<html><body>\n';
	prnttext+=document.getElementById('printcontent').innerHTML;
	prnttext+='</body></html>';
	var newwin=window.open("","printit",'menubar=no, scrollbars=yes, width=600, height=450, directories=no,location=no,resizable=yes,status=no,toolbar=no');
	newwin.document.open();
	newwin.document.write(prnttext);
	newwin.document.close();
	newwin.print();
}
//-->
</script>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
            <table width="100%" border="0" cellspacing="3" cellpadding="3">
			  <tr> 
                <td width="100%" align="center"><?php print $xxThkYou?>
                </td>
			  </tr>
<?php			if(@$digidownloads!=TRUE){ ?>
			  <tr>
                <td width="100%" align="left">
				  <span id="printcontent"><?php print str_replace('%nl%','<br />',str_replace("%messagebody%", $recpt, $receiptheader))?></span>
                </td>
			  </tr>
			  <tr>
                <td width="100%" align="center"><br />
<?php				if(trim($xxRecEml)!='')print $xxRecEml . '<br /><br />';
					print imageorbutton(@$imgcontinueshopping,'&nbsp;'.$xxCntShp.'&nbsp;','continueshopping',(@$thankspagecontinue!=''?$thankspagecontinue:$storeurl), FALSE).'&nbsp;';
					print imageorbutton(@$imgprintversion,'&nbsp;'.$xxPrint.'&nbsp;','printversion','doprintcontent()', TRUE);
?><br/><br />&nbsp;
                </td>
			  </tr>
		<?php	} ?>
			</table>
		  </td>
        </tr>
      </table>
<?php
	}
}
?>