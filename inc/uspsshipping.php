<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
function getshippingerror($shiperrmsg){
	global $adminAltRates;
	return '<div class="shippingerror">There seems to be a problem connecting to the shipping rates server. Please wait a few moments and refresh your browser' . ($adminAltRates<>0?', or try a different shipping carrier':'') . '.</div><div class="shiperrortechdetails" style="font-size:10px;color:#000000;margin-top:3px">' . $shiperrmsg . '</div>';
}
function sortshippingarray(){
	global $intShipping,$maxshipoptions;
	$maxallocateditem=0;
	for($ssaindex=0; $ssaindex < $maxshipoptions; $ssaindex++){
		if($intShipping[$ssaindex][3]) $maxallocateditem=$ssaindex;
	}
	for($ssaindex=0; $ssaindex<=$maxallocateditem; $ssaindex++){
		$intShipping[$ssaindex][2]=(double)$intShipping[$ssaindex][2];
		if($intShipping[$ssaindex][3]){
			for($ssaindex2=$ssaindex+1; $ssaindex2<=$maxallocateditem; $ssaindex2++){
				if($intShipping[$ssaindex][3] && $intShipping[$ssaindex2][3] && ($intShipping[$ssaindex][0]==$intShipping[$ssaindex2][0])){
					if($intShipping[$ssaindex][2]<$intShipping[$ssaindex2][2]){
						if($intShipping[$ssaindex2][4]==0) $intShipping[$ssaindex2][3]=0;
					}else{
						if($intShipping[$ssaindex][4]==0) $intShipping[$ssaindex][3]=0;
					}
				}
			}
		}
	}
	$maxshipoptions=$maxallocateditem+1;
	for($ssaindex2=0; $ssaindex2 < $maxshipoptions; $ssaindex2++){
		for($ssaindex=1; $ssaindex < $maxshipoptions; $ssaindex++){
			if(!$intShipping[$ssaindex-1][3] || ($intShipping[$ssaindex][3] && ((double)$intShipping[$ssaindex][2] < (double)$intShipping[$ssaindex-1][2]))){
				$tt=$intShipping[$ssaindex];
				$intShipping[$ssaindex]=$intShipping[$ssaindex-1];
				$intShipping[$ssaindex-1]=$tt;
			}
		}
	}
}
function ParseDHLXMLOutput($sXML, $international, &$errormsg, &$errorcode, &$intShipping){
	global $xxDays,$numuspsmeths,$uspsmethods,$discountshippingdhl;
	$noError=TRUE;
	if(trim($sXML)=='')
		$errormsg='DHL returned no data';
	else{
		$xmlDoc=new vrXMLDoc($sXML);
		$nodeList=$xmlDoc->nodeList->childNodes[0];
		$errormsg=$nodeList->getValueByTagName('ConditionData');
	}
	if($errormsg!=''){
		$noError=FALSE;
	}else{
		for($i=0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=='GetQuoteResponse'){
				$nodeList2=$nodeList->childNodes[$i];
				for($j=0; $j < $nodeList2->length; $j++){
					if($nodeList2->nodeName[$j]=='BkgDetails'){
						$nodeList3=$nodeList2->childNodes[$j];
						for($k=0; $k < $nodeList3->length; $k++){
							if($nodeList3->nodeName[$k]=='QtdShp'){
								$nodeList4=$nodeList3->childNodes[$k];
								$shippingcharge=(double)$nodeList4->getValueByTagName('ShippingCharge');
								if($shippingcharge>0){
									$serviceid=$nodeList4->getValueByTagName('GlobalProductCode');
									$l=0;
									while($intShipping[$l][5]!='' && $intShipping[$l][5]!=$serviceid)
										$l++;
									$intShipping[$l][5]=$serviceid;
									if(!@$GLOBALS['noshipdateestimate']) $intShipping[$l][1]=$nodeList4->getValueByTagName('TotalTransitDays') . ' ' . $xxDays;
									$shiptax=(double)$nodeList4->getValueByTagName('TotalTaxAmount');
									$intShipping[$l][2]=$shippingcharge-$shiptax;
									$wantthismethod=FALSE;
									for($index2=0;$index2<$numuspsmeths;$index2++){
										if($serviceid==$uspsmethods[$index2]['uspsMethod']){ $intShipping[$l][0]=$uspsmethods[$index2]['uspsShowAs']; $wantthismethod=TRUE; break; }
									}
									if(! $wantthismethod)
										$intShipping[$l][3]=0;
									else{
										if(@$discountshippingdhl!='') $intShipping[$l][2]=round($intShipping[$l][2]*(1+$discountshippingdhl/100.0),2);
										$intShipping[$l][3]=TRUE;
									}
								}
							}
						}
					}
				}
			}
		}
	}
	return($noError);
}
function ParseUSPSXMLOutput($sXML, $international,&$errormsg,&$intShipping){
	global $xxDay,$xxDays,$dumpshippingxml,$numuspsmeths,$uspsmethods,$discountshippingusps;
	$noError=TRUE;
	$packCost=0;
	$errormsg='';
	$xmlDoc=new vrXMLDoc($sXML);
	if($xmlDoc->nodeList->nodeName[0]=='Error'){ // Top-level Error
		$noError=FALSE;
		$nodeList=$xmlDoc->nodeList->childNodes[0];
		for($i=0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=='Description'){
				$errormsg=$nodeList->nodeValue[$i];
			}
		}
	}else{ // no Top-level Error
		$nodeList=$xmlDoc->nodeList->childNodes[0];
		for($i=0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=='Package'){
				$tmpArr=explode('xx', getattributes($nodeList->attributes[$i], 'ID'));
				$quantity=(int)$tmpArr[2];
				$thisService=$tmpArr[0];
				$e=$nodeList->childNodes[$i];
				for($j=0; $j < $nodeList->childNodes[$i]->length; $j++){
					if($e->nodeName[$j]=='Error'){ // Lower-level error
						$t=$e->childNodes[$j];
						$errnum=0; $errdesc='';
						for($k=0; $k < $t->length; $k++){
							if($t->nodeName[$k]=='Number')
								$errnum=$t->nodeValue[$k];
							elseif($t->nodeName[$k]=='Description'){
								$errdesc=$t->nodeValue[$k];
								if(@$dumpshippingxml) print 'USPS warning: ' . $t->nodeValue[$k] . '<br>';
							}
						}
						if($errnum=='-2147219497' || $errnum=='-2147219498' || $errnum=='-2147219433'){ // Invalid Zip
							$noError=FALSE;
							if($errnum='-2147219497') $errormsg=$GLOBALS['xxInvZip']; else $errormsg=$errdesc;
						}
					}else{
						if($e->nodeName[$j]=='Postage'){
							if($international==''){
								$therate=$e->getValueByTagName('Rate');
								$l=0;
								while($intShipping[$l][5] != $thisService && $intShipping[$l][5]!='')
									$l++;
								$intShipping[$l][5]=$thisService;
								if(@$GLOBALS['noshipdateestimate'])
									$intShipping[$l][1]='';
								elseif($thisService=='PARCEL')
									$intShipping[$l][1]='2-7 ' . $xxDays;
								elseif($thisService=='EXPRESS')
									$intShipping[$l][1]='Overnight to most areas';
								elseif($thisService=='PRIORITY')
									$intShipping[$l][1]='2-3 ' . $xxDays;
								elseif($thisService=='BPM')
									$intShipping[$l][1]='2-7 ' . $xxDays;
								elseif($thisService=='Media')
									$intShipping[$l][1]='2-7 ' . $xxDays;
								elseif($thisService=='FIRST-CLASS')
									$intShipping[$l][1]='1-3 ' . $xxDays;
								$intShipping[$l][2]=$intShipping[$l][2] + ($therate * $quantity);
								$intShipping[$l][3]=$intShipping[$l][3] + 1;
								$wantthismethod=FALSE;
								for($index2=0;$index2<$numuspsmeths;$index2++){
									if(str_replace('-',' ',$thisService)==str_replace('-',' ',$uspsmethods[$index2]['uspsMethod'])){ $intShipping[$l][0]=$uspsmethods[$index2]['uspsShowAs']; $wantthismethod=TRUE; break; }
								}
								if(! $wantthismethod) $intShipping[$l][3]=0;
							}
						}elseif($e->nodeName[$j]=='Service'){
							if($international!=''){
								$serviceerror=$wantthismethod=FALSE;
								$serviceid=getattributes($e->attributes[$j], 'ID');
								$t=$e->childNodes[$j];
								$SvcCommitments='';
								for($k=0; $k < $t->length; $k++){
									if($t->nodeName[$k]=='SvcDescription')
										$SvcDescription=$t->nodeValue[$k];
									elseif($t->nodeName[$k]=='SvcCommitments')
										$SvcCommitments=$t->nodeValue[$k];
									elseif($t->nodeName[$k]=='Postage')
										$Postage=$t->nodeValue[$k];
									elseif($t->nodeName[$k]=='ServiceErrors')
										$serviceerror=TRUE;
								}
								$l=0;
								while($intShipping[$l][5]!='' && $intShipping[$l][5]!=$serviceid)
									$l++;
								$intShipping[$l][5]=$serviceid;
								if(!$serviceerror){
									if(!@$GLOBALS['noshipdateestimate']) $intShipping[$l][1]=str_replace(' to many major markets','',$SvcCommitments);
									$intShipping[$l][2]+=($Postage * $quantity);
									$intShipping[$l][3]++;
									for($index2=0;$index2<$numuspsmeths;$index2++){
										if($serviceid==$uspsmethods[$index2]['uspsMethod']){ $intShipping[$l][0]=$uspsmethods[$index2]['uspsShowAs']; $wantthismethod=TRUE; break; }
									}
								}
								if(! $wantthismethod) $intShipping[$l][3]=0;
							}else
								$thisService=$e->nodeValue[$j];
						}
					}
				}
				$packCost=0;
			}
		}
	}
	if(@$discountshippingusps!=''){
		for($uspsind=0;$uspsind<$numuspsmeths;$uspsind++){
			if($intShipping[$uspsind][3]>0) $intShipping[$uspsind][2]=round($intShipping[$uspsind][2]*(1+$discountshippingusps/100.0),2);
		}
	}
	return $noError;
}
function checkUPSShippingMeth($method, &$discountsApply, &$showAs){
	global $numuspsmeths, $uspsmethods;
	$discountsApply=0;
	for($index=0; $index < $numuspsmeths; $index++){
		if($method==$uspsmethods[$index]['uspsMethod']){
			$discountsApply=$uspsmethods[$index]['uspsFSA'];
			$showAs=$uspsmethods[$index]['uspsShowAs'];
			return(TRUE);
		}
	}
	return(FALSE);
}
function ParseUPSXMLOutput($sXML,$international,&$errormsg,&$errorcode,&$intShipping){
	global $xxDay,$xxDays,$upsnegdrates,$origCountryCode,$shipCountryCode,$ordComLoc,$discountshippingups;
	$noError=TRUE;
	$errormsg='';
	$l=0;
	$discntsApp='';
	if(strlen($sXML)<40){
		$noError=FALSE;
		$errormsg='Invalid Response From UPS Server';
	}else{
		$xmlDoc=new vrXMLDoc($sXML);
		$nodeList=$xmlDoc->nodeList->childNodes[0];
		for($i=0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=='Response'){
				$e=$nodeList->childNodes[$i];
				for($j=0; $j < $e->length; $j++){
					if($e->nodeName[$j]=='ResponseStatusCode'){
						$noError=((int)$e->nodeValue[$j])==1;
					}
					if($e->nodeName[$j]=='Error'){
						$errormsg='';
						$t=$e->childNodes[$j];
						for($k=0; $k < $t->length; $k++){
							if($t->nodeName[$k]=='ErrorCode'){
								$errorcode=$t->nodeValue[$k];
							}elseif($t->nodeName[$k]=='ErrorSeverity'){
								if($t->nodeValue[$k]=='Transient')
									$errormsg='This is a temporary error. Please wait a few moments then refresh this page.<br />' . $errormsg;
							}elseif($t->nodeName[$k]=='ErrorDescription'){
								$errormsg.=$t->nodeValue[$k];
							}
						}
					}
				}
			}elseif($nodeList->nodeName[$i]=='RatedShipment'){ // no Top-level Error
				$wantthismethod=TRUE;
				$nodeList=$xmlDoc->nodeList->childNodes[0];
				$e=$nodeList->childNodes[$i];
				$negotiatedrate='';
				for($j=0; $j < $e->length; $j++){
					if($e->nodeName[$j]=='Service'){ // Lower-level error
						$t=$e->childNodes[$j];
						for($k=0; $k < $t->length; $k++){
							if($t->nodeName[$k]=='Code'){
								if($t->nodeValue[$k]=='01')
									$intShipping[$l][0]='UPS Next Day Air&reg;';
								elseif($t->nodeValue[$k]=='02')
									$intShipping[$l][0]='UPS 2nd Day Air&reg;';
								elseif($t->nodeValue[$k]=='03')
									$intShipping[$l][0]='UPS Ground';
								elseif($t->nodeValue[$k]=='07')
									$intShipping[$l][0]='UPS Worldwide Express&reg;';
								elseif($t->nodeValue[$k]=='08')
									$intShipping[$l][0]='UPS Worldwide Expedited&reg;';
								elseif($t->nodeValue[$k]=='11')
									$intShipping[$l][0]='UPS Standard';
								elseif($t->nodeValue[$k]=='12')
									$intShipping[$l][0]='UPS 3 Day Select&reg;';
								elseif($t->nodeValue[$k]=='13')
									$intShipping[$l][0]='UPS Next Day Air Saver&reg;';
								elseif($t->nodeValue[$k]=='14')
									$intShipping[$l][0]='UPS Next Day Air&reg; Early A.M.&reg;';
								elseif($t->nodeValue[$k]=='54')
									$intShipping[$l][0]='UPS Worldwide Express Plus&reg;';
								elseif($t->nodeValue[$k]=='59')
									$intShipping[$l][0]='UPS 2nd Day Air A.M.&reg;';
								elseif($t->nodeValue[$k]=='65'){
									if($origCountryCode=='US' && $shipCountryCode!='US')
										$intShipping[$l][0]='UPS Worldwide Saver&reg;';
									else
										$intShipping[$l][0]='UPS Express Saver&reg;';
								}
								$wantthismethod=checkUPSShippingMeth($t->nodeValue[$k], $discntsApp, $notUsed);
								$intShipping[$l][4]=$discntsApp;
							}
						}
					}elseif($e->nodeName[$j]=='TotalCharges'){
						$t=$e->childNodes[$j];
						for($k=0; $k < $t->length; $k++){
							if($t->nodeName[$k]=='MonetaryValue'){
								$intShipping[$l][2]=(double)$t->nodeValue[$k];
							}
						}
					}elseif($e->nodeName[$j]=='GuaranteedDaysToDelivery'&&!@$GLOBALS['noshipdateestimate']){
						if(strlen($e->nodeValue[$j])>0){
							if($e->nodeValue[$j]=='1')
								$intShipping[$l][1]='1 ' . $xxDay . $intShipping[$l][1];
							else
								$intShipping[$l][1]=$e->nodeValue[$j] . ' ' . $xxDays . $intShipping[$l][1];
						}
					}elseif($e->nodeName[$j]=='ScheduledDeliveryTime'&&!@$GLOBALS['noshipdateestimate']){
						if(strlen($e->nodeValue[$j])>0){
							$intShipping[$l][1].=' by ' . $e->nodeValue[$j];
						}
					}elseif($e->nodeName[$j]=='NegotiatedRates'){ // Lower-level error
						$t=$e->childNodes[$j];
						$negrate=$t->getValueByTagName('MonetaryValue');
						if($negrate!=null) $negotiatedrate=$negrate;
					}elseif($e->nodeName[$j]=='RatedShipmentWarning'){
						if(strpos($e->nodeValue[$j],'Commercial to Residential')!==FALSE){
							$commercialloc_=FALSE;
							if(($ordComLoc & 1)==1) $ordComLoc-=1;
						}
					}
				}
				if($negotiatedrate!='' && @$upsnegdrates==TRUE){
					$intShipping[$l][2]=(double)$negotiatedrate;
				}
				if($wantthismethod){
					if(@$discountshippingups!='') $intShipping[$l][2]=round($intShipping[$l][2]*(1+$discountshippingups/100.0),2);
					$intShipping[$l][3]=TRUE;
					$l++;
				}else
					$intShipping[$l][1]='';
				$wantthismethod=TRUE;
			}
		}
	}
	return $noError;
}
function ParseCanadaPostXMLOutput($sXML, $international,&$errormsg,&$errorcode,&$intShipping){
	global $xxDay,$xxDays,$destZip,$storelang,$discountshippingcanadapost;
	$noError=TRUE;
	$errormsg='';
	$discntsApp='';
	$l=0;
	$cphandlingcharge=0;
	$xmlDoc=new vrXMLDoc($sXML);
	$nodeList=$xmlDoc->nodeList->childNodes[0];
	for($i=0; $i < $nodeList->length; $i++){
		if(strpos($nodeList->nodeName[$i],':Body')!==FALSE){
			$nodeList=$nodeList->childNodes[$i];
		}
	}
	for($i=0; $i < $nodeList->length; $i++){
		if($nodeList->nodeName[$i]=='soap:Fault'){
			$noError=FALSE;
			$e=$nodeList->childNodes[$i];
			for($j=0; $j < $e->length; $j++){
				if($e->nodeName[$j]=='faultcode'){
					$errorcode=$e->nodeValue[$j];
				}elseif($e->nodeName[$j]=='faultstring'){
					$errormsg=$e->nodeValue[$j];
					if(strpos($errormsg,'}PostalCodeType')!==FALSE||strpos($errormsg,'}ZipCodeType')!==FALSE){
						if($storelang=='fr')$errormsg='Code Postal Invalide: ' . $destZip; else $errormsg='Invalid Postal Code: ' . $destZip;
					}
				}
			}
		}elseif($nodeList->nodeName[$i]=='tns:get-rates-response'){ // no Top-level Error
			$wantthismethod=TRUE;
			$e=$nodeList->childNodes[$i];
			for($xi=0; $xi < $e->length; $xi++){
				if($e->nodeName[$xi]=='price-quotes'){
					$e=$e->childNodes[$xi];
				}
			}
			for($j=0; $j < $e->length; $j++){
				if($e->nodeName[$j]=='price-quote'){
					$wantthismethod=TRUE;
					$t=$e->childNodes[$j];
					for($k=0; $k < $t->length; $k++){
						if($t->nodeName[$k]=='service-code'){
							$wantthismethod=checkUPSShippingMeth($t->nodeValue[$k], $discntsApp, $notUsed);
							$intShipping[$l][4]=$discntsApp;
						}elseif($t->nodeName[$k]=='service-name'){
							$intShipping[$l][0]=$t->nodeValue[$k];
						}elseif($t->nodeName[$k]=='price-details'){
							$ep=$t->childNodes[$k];
							for($pj=0; $pj < $ep->length; $pj++){
								if($ep->nodeName[$pj]=='due'){
									$intShipping[$l][2]=(double)$ep->nodeValue[$pj];
								}
							}
						}elseif($t->nodeName[$k]=='service-standard'&&!@$GLOBALS['noshipdateestimate']){
							$ep=$t->childNodes[$k];
							for($pj=0; $pj < $ep->length; $pj++){
								if($ep->nodeName[$pj]=='expected-delivery-date'){
									$today=getdate();
									$daytoday=$today['yday'];
									if(($ttimeval=strtotime($ep->nodeValue[$pj])) <= 0){
										$intShipping[$l][1]=$ep->nodeValue[$pj] . $intShipping[$l][1];
									}else{
										$deldate=getdate($ttimeval);
										$daydeliv=$deldate['yday'];
										if($daydeliv < $daytoday) $daydeliv+=365;
										$intShipping[$l][1]=($daydeliv - $daytoday) . ' ' . ($daydeliv - $daytoday < 2?$xxDay:$xxDays) . $intShipping[$l][1];
									}
								}
							}
						}
					}
					if($wantthismethod){
						if(@$discountshippingcanadapost!='') $intShipping[$l][2]=round($intShipping[$l][2]*(1+$discountshippingcanadapost/100.0),2);
						$intShipping[$l][3]=TRUE;
						$l++;
					}else
						$intShipping[$l][1]='';
					$wantthismethod=TRUE;
				}
			}
		}
	}
	return $noError;
}
function getuspscontainer($gpcweight,$theservice){
	global $packdims;
	$ispriority=$theservice=='PRIORITY';
	$isexpress=$theservice=='EXPRESS';
	$getuspscont='';
	if($ispriority && $gpcweight<=70 && ($packdims[0]<=12.25 && $packdims[1]<=12.25 && $packdims[2]<=6)) $getuspscont='lg flat rate box';
	if($ispriority && $gpcweight<=70 && (($packdims[0]<=11 && $packdims[1]<=8.5 && $packdims[2]<=5.5) || ($packdims[0]<=13.625 && $packdims[1]<=11.875 && $packdims[2]<=3.375))) $getuspscont='md flat rate box';
	if($ispriority && $gpcweight<=70 && ($packdims[0]<=8.625 && $packdims[1]<=5.375 && $packdims[2]<=1.625)) $getuspscont='sm flat rate box';
	if($gpcweight<=70 && ($packdims[0]<=12.5 && $packdims[1]<=9.5 && $packdims[2]<=1)) $getuspscont='flat rate envelope';
	if($packdims[0]<=0 || $packdims[1]<=0 || $packdims[2]<=0)$getuspscont='';
	return($getuspscont);
}
function addUSPSDomestic($id,$service,$orig,$dest,$iWeight,$quantity,$size,$machinable){
	global $numuspsmeths,$uspsmethods,$firstclassmailtype,$uspsprioritycontainer,$uspsexpresscontainer,$packdims,$adminUnits,$iTotItems;
	$iTotItems++;
	$sXML='';
	$pounds=(int)$iWeight;
	$ounces=round(($iWeight-$pounds)*16.0);
	if($pounds==0 && $ounces==0) $ounces=1;
	if(($adminUnits & 12)!=0){
		$totaldims=$packdims[0] + (2 * ($packdims[1] + $packdims[2]));
		if($totaldims>84) $size='LARGE';
		if($totaldims>108) $size='OVERSIZE';
	}
	for($index=0;$index<$numuspsmeths;$index++){
		$packsize=$size;
		if($uspsmethods[$index]['uspsMethod']!=''){
			$sXML.='<Package ID="' . str_replace(' ','-',$uspsmethods[$index]['uspsMethod']) . 'xx' . $id . 'xx' . $quantity . '">';
			$sXML.='<Service>' . $uspsmethods[$index]['uspsMethod'] . '</Service>';
			if($uspsmethods[$index]['uspsMethod']=='FIRST CLASS') $sXML.='<FirstClassMailType>' . (@$firstclassmailtype!='' ? $firstclassmailtype : 'PARCEL') . '</FirstClassMailType>';
			$sXML.='<ZipOrigination>' . $orig . '</ZipOrigination><ZipDestination>' . substr($dest, 0, 5) . '</ZipDestination>';
			$sXML.='<Pounds>' . $pounds . '</Pounds><Ounces>' . $ounces . '</Ounces>';
			$thecontainer='VARIABLE';
			if($uspsprioritycontainer=='flat rate box') $uspsprioritycontainer='md flat rate box';
			if(strpos($uspsexpresscontainer,'flat rate box')!==FALSE) $uspsexpresscontainer='';
			$tempcontainer=$uspsprioritycontainer;
			if($uspsmethods[$index]['uspsMethod']=='PRIORITY'){
				if(($adminUnits & 12)!=0){
					if((($packdims[0] * $packdims[1] * $packdims[2])>1728) && $packsize=='REGULAR') $packsize='LARGE';
					if(@$tempcontainer!=''){
						$tempcontainer=getuspscontainer($iWeight,'PRIORITY');
						if($uspsprioritycontainer!='auto'){
							if($tempcontainer=='' || ($uspsprioritycontainer=='md flat rate box' && $tempcontainer=='lg flat rate box') || ($uspsprioritycontainer=='sm flat rate box' && ($tempcontainer=='lg flat rate box' || $tempcontainer=='md flat rate box')) || ($uspsprioritycontainer=='flat rate envelope' && ($tempcontainer=='lg flat rate box' || $tempcontainer=='md flat rate box' || $tempcontainer=='sm flat rate box')))
								$uspsmethods[$index]['uspsMethod']='';
							else
								$tempcontainer=$uspsprioritycontainer;
						}
					}
				}
				if(@$tempcontainer=='' || @$tempcontainer=='auto') $thecontainer=($packsize=='LARGE'?'rectangular':''); else $thecontainer=$tempcontainer;
			}
			$tempcontainer=$uspsexpresscontainer;
			if($uspsmethods[$index]['uspsMethod']=='EXPRESS'){
				if(($adminUnits & 12)!=0 && @$tempcontainer!='') $tempcontainer=getuspscontainer($iWeight,'EXPRESS');
				if($uspsexpresscontainer!='auto'){
					if($uspsexpresscontainer=='flat rate envelope' && $tempcontainer=='flat rate box') $uspsmethods[$index]['uspsMethod']=''; else $tempcontainer=$uspsexpresscontainer;
				}
				if(@$tempcontainer=='' || @$tempcontainer=='auto') $thecontainer=''; else $thecontainer=$tempcontainer;
			}
			$sXML.='<Container>' . $thecontainer . '</Container><Size>' . $packsize . '</Size>';
			if((($adminUnits & 12)!=0) && $packdims[0]>0 && $packdims[1]>0 && $packdims[2]>0) $sXML.='<Width>' . round($packdims[1],1) . '</Width><Length>' . round($packdims[0],1) . '</Length><Height>' . round($packdims[2],1) . '</Height>';
			$sXML.='<Machinable>' . $machinable . '</Machinable></Package>';
		}
	}
	return $sXML;
}
function doesfitinbox($blen,$bwid,$bhei){
	global $packdims;
	$dfb=TRUE;
	if($packdims[0]>$blen || $packdims[1]>$bwid || $packdims[2]>$bhei)
		$dfb=FALSE;
	if(! $dfb && $packdims[7]>=3){
		if($packdims[4]<=$blen && $packdims[5]<=$bwid && $packdims[6]<=$bhei && $packdims[4]<=($blen*$bwid*$bhei))
			$dfb=TRUE;
	}
}
function addUSPSInternational($id,$iWeight,$quantity,$mailtype,$country,$packcost){
	global $packdims,$numuspsmeths,$uspsmethods,$adminUnits,$shipCountryCode,$iTotItems,$origZip;
	$iTotItems++;
	if(($adminUnits & 12)!=0){
		$lenplusgirth=$packdims[0] + (2 * ($packdims[1] + $packdims[2]));
		for($xx=0; $xx < $numuspsmeths; $xx++){
			if($shipCountryCode=='AD' || $shipCountryCode=='AT' || $shipCountryCode=='BE' || $shipCountryCode=='CH' || $shipCountryCode=='CN' || $shipCountryCode=='CZ' || $shipCountryCode=='DE' || $shipCountryCode=='DK' || $shipCountryCode=='ES' || $shipCountryCode=='FI' || $shipCountryCode=='FR' || $shipCountryCode=='GR' || $shipCountryCode=='HK' || $shipCountryCode=='IE' || $shipCountryCode=='IT' || $shipCountryCode=='JP' || $shipCountryCode=='LI' || $shipCountryCode=='LU' || $shipCountryCode=='MC' || $shipCountryCode=='MT' || $shipCountryCode=='NL' || $shipCountryCode=='NO' || $shipCountryCode=='PT' || $shipCountryCode=='SE' || $shipCountryCode=='VA'){
				if($packdims[0]>60 || $lenplusgirth>108){ // Express Mail
					if($uspsmethods[$xx]['uspsMethod']=='1') $uspsmethods[$xx]['uspsMethod']='xxx';
				}
			}elseif($shipCountryCode=='CA'){
				if($packdims[0]>42 || $lenplusgirth>79){
					if($uspsmethods[$xx]['uspsMethod']=='1') $uspsmethods[$xx]['uspsMethod']='xxx';
				}
			}else{
				if($packdims[0]>36 || $lenplusgirth>79){
					if($uspsmethods[$xx]['uspsMethod']=='1') $uspsmethods[$xx]['uspsMethod']='xxx';
				}
			}
			if($shipCountryCode=='CA' || $shipCountryCode=='HK'){ // Priority Mail
				if($lenplusgirth>108){
					if($uspsmethods[$xx]['uspsMethod']=='2') $uspsmethods[$xx]['uspsMethod']='xxx';
				}
			}elseif($shipCountryCode=='AD' || $shipCountryCode=='AT' || $shipCountryCode=='BE' || $shipCountryCode=='CH' || $shipCountryCode=='CZ' || $shipCountryCode=='DE' || $shipCountryCode=='DK' || $shipCountryCode=='ES' || $shipCountryCode=='FI' || $shipCountryCode=='FR' || $shipCountryCode=='GI' || $shipCountryCode=='GB' || $shipCountryCode=='GR' || $shipCountryCode=='IE' || $shipCountryCode=='IT' || $shipCountryCode=='JP' || $shipCountryCode=='LI' || $shipCountryCode=='LU' || $shipCountryCode=='MC' || $shipCountryCode=='MT' || $shipCountryCode=='NL' || $shipCountryCode=='NO' || $shipCountryCode=='NZ' || $shipCountryCode=='PL' || $shipCountryCode=='PT' || $shipCountryCode=='SE' || $shipCountryCode=='VA'){
				if($packdims[0]>60 || $lenplusgirth>108){
					if($uspsmethods[$xx]['uspsMethod']=='2') $uspsmethods[$xx]['uspsMethod']='xxx';
				}
			}else{
				if($packdims[0]>42 || $lenplusgirth>79){
					if($uspsmethods[$xx]['uspsMethod']=='2') $uspsmethods[$xx]['uspsMethod']='xxx';
				}
			}
			if($iWeight>70 || $packdims[0]>46 || $packdims[1]>46 || $packdims[2]>35 || $lenplusgirth>108){
				if($uspsmethods[$xx]['uspsMethod']=='4' || $uspsmethods[$xx]['uspsMethod']=='6' || $uspsmethods[$xx]['uspsMethod']=='7') $uspsmethods[$xx]['uspsMethod']='xxx'; // GXG
			}
			if($uspsmethods[$xx]['uspsMethod']=='24') if($iWeight>4 || ! doesfitinbox(7.5625,5.4375,0.625)) $uspsmethods[$xx]['uspsMethod']=='xxx'; // DVD FRB
			if($uspsmethods[$xx]['uspsMethod']=='16') if($iWeight>4 || ! doesfitinbox(8.625, 5.375, 1.625)) $uspsmethods[$xx]['uspsMethod']=='xxx'; // Small FRB
			if($uspsmethods[$xx]['uspsMethod']=='20') if($iWeight>4 || ! doesfitinbox(10,6,0.75)) $uspsmethods[$xx]['uspsMethod']=='xxx'; // Small FRE
			if($uspsmethods[$xx]['uspsMethod']=='9' || $uspsmethods[$xx]['uspsMethod']=='26') if($iWeight>20 || (! doesfitinbox(11,8.5,5.5) && ! doesfitinbox(13.625,11.875,3.375))) $uspsmethods[$xx]['uspsMethod']=='xxx'; // FRB
			if($uspsmethods[$xx]['uspsMethod']=='13') if($iWeight>4 || ! doesfitinbox(11.5,6.125,0.25)) $uspsmethods[$xx]['uspsMethod']=='xxx'; // FirstClass Letter
			if($uspsmethods[$xx]['uspsMethod']=='11') if($iWeight>20 || ! doesfitinbox(12,12,5.5)) $uspsmethods[$xx]['uspsMethod']=='xxx'; // LFRB
			if($uspsmethods[$xx]['uspsMethod']=='8' || $uspsmethods[$xx]['uspsMethod']=='10') if($iWeight>4 || ! doesfitinbox(12.5,9.5,1)) $uspsmethods[$xx]['uspsMethod']=='xxx'; // FRE
			if($uspsmethods[$xx]['uspsMethod']=='17') if($iWeight>4 || ! doesfitinbox(15,9.5,0.75)) $uspsmethods[$xx]['uspsMethod']=='xxx'; // Legal FRE
			if($uspsmethods[$xx]['uspsMethod']=='14') if($iWeight>4 || ! doesfitinbox(15,12,0.75)) $uspsmethods[$xx]['uspsMethod']=='xxx'; // FirstClass L-E
		}
	}
	$pounds=(int)$iWeight;
	$ounces=round(($iWeight-$pounds)*16.0);
	if($pounds==0 && $ounces==0) $ounces=1;
	$sXML='<Package ID="xx' . $id . 'xx' . $quantity . '"><Pounds>' . $pounds . '</Pounds><Ounces>' . $ounces . '</Ounces><MailType>ALL</MailType>';
	$sXML.='<GXG><POBoxFlag>N</POBoxFlag><GiftFlag>N</GiftFlag></GXG>';
	$sXML.='<ValueOfContents>' . ceil($packcost) . '</ValueOfContents>';
	$sXML.='<Country>' . $country . '</Country><Container>RECTANGULAR</Container><Size>REGULAR</Size>';
	if(($adminUnits & 12)!=0 && ceil($packdims[0])>0 && ceil($packdims[1])>0 && ceil($packdims[2])>0) $sXML.='<Width>' . round($packdims[2],2) . '</Width><Length>' . round($packdims[0],2) . '</Length><Height>' . round($packdims[1],2) . '</Height><Girth>' . ceil(($packdims[1]*2)+($packdims[2]*2)) . '</Girth>'; else $sXML.='<Width>0.1</Width><Length>0.1</Length><Height>0.1</Height><Girth></Girth>';
	$sXML.='<OriginZip>'.$origZip.'</OriginZip>';
	return $sXML . '<CommercialFlag>N</CommercialFlag></Package>';
}
function addUPSInternational($iWeight,$adminUnits,$packTypeCode,$country,$packcost,&$dimens){
	global $addshippinginsurance,$countryCurrency,$adminUnits,$payproviderpost,$wantinsurance_,$signatureoption;
	if($iWeight<0.1) $iWeight=0.1;
	$sXML='<Package><PackagingType><Code>' . $packTypeCode . '</Code><Description>Package</Description></PackagingType>';
	if($dimens[0]>0 && $dimens[1]>0 && $dimens[2]>0) $sXML.='<Dimensions><Length>' . round($dimens[0],0) . '</Length><Width>' . round($dimens[1],0) . '</Width><Height>' . round($dimens[2],0) . '</Height><UnitOfMeasurement><Code>' . (($adminUnits & 12)==4 ? 'IN' : 'CM') . '</Code></UnitOfMeasurement></Dimensions>';
	$sXML.='<Description>Rate Shopping</Description><PackageWeight><UnitOfMeasurement><Code>' . (($adminUnits & 1)==1 ? 'LBS' : 'KGS') . '</Code></UnitOfMeasurement><Weight>' . $iWeight . '</Weight></PackageWeight><PackageServiceOptions>';
	if(abs(@$addshippinginsurance)==1 || (abs(@$addshippinginsurance)==2 && $wantinsurance_)){
		if($packcost>50000) $packcost=50000;
		$sXML.='<InsuredValue><CurrencyCode>' . $countryCurrency . '</CurrencyCode><MonetaryValue>' . number_format($packcost,2,'.','') . '</MonetaryValue></InsuredValue>';
	}
	if($payproviderpost!=''){
		if((int)$payproviderpost==@$codpaymentprovider) $sXML.='<COD><CODFundsCode>0</CODFundsCode><CODCode>3</CODCode><CODAmount><CurrencyCode>'. $countryCurrency . '</CurrencyCode><MonetaryValue>' . number_format($packcost,2,'.','') . '</MonetaryValue></CODAmount></COD>';
	}
	if(@$signatureoption=='indirect')
		$sXML.='<DeliveryConfirmation><DCISType>1</DCISType></DeliveryConfirmation>';
	elseif(@$signatureoption=='direct')
		$sXML.='<DeliveryConfirmation><DCISType>2</DCISType></DeliveryConfirmation>';
	elseif(@$signatureoption=='adult')
		$sXML.='<DeliveryConfirmation><DCISType>3</DCISType></DeliveryConfirmation>';
	return $sXML . '</PackageServiceOptions></Package>';
}
function addDHLPackage($iWeight,$adminUnits,$packTypeCode,$country,$packcost,$dimens){
	global $packnumber;
	$tempXML='<Piece><PieceID>' . $packnumber . '</PieceID>';
	if($dimens[0]>0 && $dimens[1]>0 && $dimens[2]>0) $tempXML.='<Height>' . round($dimens[0],0) . '</Height><Depth>' . round($dimens[1],0) . '</Depth><Width>' . round($dimens[2],0) . '</Width>';
	$tempXML.='<Weight>' . round($iWeight,2) . '</Weight></Piece>';
	$packnumber++;
	return($tempXML);
}
function dhlcalculate($sXML,$international,&$errormsg,&$intShipping){
	global $dumpshippingxml,$upstestmode,$shipCountryID,$destZip;
	if($destZip=='' && ! zipisoptional($shipCountryID)){
		$errormsg=$xxPlsZip;
		return(FALSE);
	}elseif(callcurlfunction('https://xmlpi' . (@$upstestmode?'test':'') . '-ea.dhl.com/XMLShippingServlet', $sXML, $xmlres, '', $errormsg, FALSE)){
		if(@$dumpshippingxml) dumpxmloutput($sXML,$xmlres);
		$success=ParseDHLXMLOutput($xmlres,$international,$errormsg,$errorcode,$intShipping);
		sortshippingarray();
	}else{
		$errormsg=getshippingerror($errormsg);
		return(FALSE);
	}
	return($success);
}
function addCanadaPostPackage($iWeight,$adminUnits,$packTypeCode,$country,$packcost,&$dimens){
	global $packtogether,$adminUnits;
	if($iWeight<0.1) $iWeight=0.1;
	$tmpXML='<parcel-characteristics><weight>' . round(($adminUnits & 1)==1 ? $iWeight * 0.453592 : $iWeight,3) . '</weight>';
	if(($adminUnits & 12)==4){ $dimens[0]*=2.54; $dimens[1]*=2.54; $dimens[2]*=2.54; }
	if($dimens[0]>0 && $dimens[1]>0 && $dimens[2]>0) $tmpXML.='<dimensions><length>' . round($dimens[0],1) . '</length><width>' . round($dimens[1],1) . '</width><height>' . round($dimens[2],1) . '</height></dimensions>';
	$tmpXML.='</parcel-characteristics>';
	return $tmpXML;
}
function addFedexPackage($iWeight,$packcost,&$dimens){
	global $adminUnits,$addshippinginsurance,$wantinsurance_,$allowsignaturerelease,$signaturerelease_,$signatureoption,$ordPayProvider,$codpaymentprovider,$countryCurrency,$shipType;
	$tmpXML='<v9:RequestedPackageLineItems>';
	if($iWeight<0.1) $iWeight=0.1;
	if($shipType==8 && $iWeight<1) $iWeight=1;
	if($shipType!=8 && (abs(@$addshippinginsurance)==1 || (abs(@$addshippinginsurance)==2 && $wantinsurance_))){
		$tmpXML.='<v9:InsuredValue><v9:Currency>' . $countryCurrency . '</v9:Currency><v9:Amount>' . number_format($packcost,2,'.','') . '</v9:Amount></v9:InsuredValue>';
	}
	$tmpXML.='<v9:Weight><v9:Units>' . (($adminUnits & 1)==1 ? 'LB' : 'KG') . '</v9:Units><v9:Value>' . number_format($iWeight,1,'.','') . '</v9:Value></v9:Weight>';
	if($dimens[0]>0 && $dimens[1]>0 && $dimens[2]>0){
		if(($adminUnits & 12)==4){ $dimens[0]=max($dimens[0],6); $dimens[1]=max($dimens[1],4); $dimens[2]=max($dimens[2],1); }else{ $dimens[0]=max($dimens[0],15); $dimens[1]=max($dimens[1],10); $dimens[2]=max($dimens[2],3); }
		$tmpXML.='<v9:Dimensions><v9:Length>' . round($dimens[0],0) . '</v9:Length><v9:Width>' . round($dimens[1],0) . '</v9:Width><v9:Height>' . round($dimens[2],0) . '</v9:Height><v9:Units>' . (($adminUnits & 12)==4 ? 'IN' : 'CM') . '</v9:Units></v9:Dimensions>';
	}
	if(@$GLOBALS['packaging']!='' && $shipType==8) $tmpXML.='<v9:PhysicalPackaging>'.strtoupper($GLOBALS['packaging']).'</v9:PhysicalPackaging>';
	$tmpXML.='<v9:SpecialServicesRequested>';
	if($signaturerelease_ && @$allowsignaturerelease==TRUE){
	}elseif(@$signatureoption=='indirect')
		$tmpXML.='<v9:SpecialServiceTypes>SIGNATURE_OPTION</v9:SpecialServiceTypes>';
	elseif(@$signatureoption=='direct')
		$tmpXML.='<v9:SpecialServiceTypes>SIGNATURE_OPTION</v9:SpecialServiceTypes>';
	elseif(@$signatureoption=='adult')
		$tmpXML.='<v9:SpecialServiceTypes>SIGNATURE_OPTION</v9:SpecialServiceTypes>';
	elseif(@$signatureoption=='none')
		$tmpXML.='<v9:SpecialServiceTypes>SIGNATURE_OPTION</v9:SpecialServiceTypes>';
	if(@$nonstandardcontainer==TRUE) $tmpXML.='<v9:SpecialServiceTypes>NON_STANDARD_CONTAINER</v9:SpecialServiceTypes>';
	if(@$dryice==TRUE) $tmpXML.='<v9:SpecialServiceTypes>DRY_ICE</v9:SpecialServiceTypes><v9:DryIceWeight><v9:Units>KG</v9:Units><v9:Value>5</v9:Value></v9:DryIceWeight>';
	if(@$dangerousgoods==TRUE) $tmpXML.='<v9:SpecialServiceTypes>DANGEROUS_GOODS</v9:SpecialServiceTypes><v9:DangerousGoodsDetail><v9:Accessibility>ACCESSIBLE</v9:Accessibility><v9:CargoAircraftOnly>1</v9:CargoAircraftOnly></v9:DangerousGoodsDetail>';
	if(@$ordPayProvider!=''){
		if((int)$ordPayProvider==$codpaymentprovider) $tmpXML.='<v9:SpecialServiceTypes>COD</v9:SpecialServiceTypes><v9:CodDetail><v9:CodCollectionAmount><v9:Currency>CAD</v9:Currency><v9:Amount>XXXFEDEXGRANDTOTXXX</v9:Amount></v9:CodCollectionAmount><v9:CollectionType>ANY</v9:CollectionType></v9:CodDetail>';
	}
	if($signaturerelease_ && @$allowsignaturerelease==TRUE){
	}elseif(@$signatureoption=='indirect')
		$tmpXML.='<v9:SignatureOptionDetail><v9:OptionType>INDIRECT</v9:OptionType></v9:SignatureOptionDetail>';
	elseif(@$signatureoption=='direct')
		$tmpXML.='<v9:SignatureOptionDetail><v9:OptionType>DIRECT</v9:OptionType></v9:SignatureOptionDetail>';
	elseif(@$signatureoption=='adult')
		$tmpXML.='<v9:SignatureOptionDetail><v9:OptionType>ADULT</v9:OptionType></v9:SignatureOptionDetail>';
	elseif(@$signatureoption=='none')
		$tmpXML.='<v9:SignatureOptionDetail><v9:OptionType>NO_SIGNATURE_REQUIRED</v9:OptionType></v9:SignatureOptionDetail>';
	$tmpXML.='</v9:SpecialServicesRequested>';
	return($tmpXML . '</v9:RequestedPackageLineItems>');
}
function USPSCalculate($sXML,$international,&$errormsg,&$intShipping){
	global $usecurlforfsock,$pathtocurl,$curlproxy,$destZip,$xxPlsZip,$maxshipoptions,$dumpshippingxml,$shipCountryID;
	$success=TRUE;
	if($destZip=='' && ! zipisoptional($shipCountryID)){
		$errormsg=$xxPlsZip;
		return(FALSE);
	}
	$sXML='API=' . $international . 'Rate' . ($international=='' ? 'V4' : 'V2') . '&XML=' . $sXML;
	if(@$usecurlforfsock){
		$success=callcurlfunction('http://production.shippingapis.com/ShippingAPI.dll', $sXML, $res, '', $errormsg, FALSE);
	}else{
		$header="POST /ShippingAPI.dll HTTP/1.0\r\n";
		$header.="Content-Type: application/x-www-form-urlencoded\r\n";
		$header.='Content-Length: ' . strlen($sXML) . "\r\n\r\n";
		$fp=@fsockopen('production.shippingapis.com', 80, $errno, $errstr, 30);
		if(!$fp){
			$errormsg=$errstr.' ('.$errno.')';
			$success=FALSE;
		}else{
			$res='';
			fputs ($fp, $header . $sXML);
			while (!feof($fp)) {
				$res.=fgets ($fp, 1024);
			}
			fclose ($fp);
		}
	}
	if($success){
		if(@$dumpshippingxml) dumpxmloutput($sXML,$res);
		$success=ParseUSPSXMLOutput($res, $international,$errormsg,$intShipping);
		for($ind1=0; $ind1 < $maxshipoptions; $ind1++){
			for($ind2=$ind1+1; $ind2 < $maxshipoptions; $ind2++){
				if($intShipping[$ind1][3]!=0 && $intShipping[$ind2][3]!=0 && $intShipping[$ind1][5]==$intShipping[$ind2][5] && $intShipping[$ind2][5]!=''){
					if((double)$intShipping[$ind1][2]<(double)$intShipping[$ind2][2]) $intShipping[$ind2][3]=0; else $intShipping[$ind1][3]=0;
				}
			}
		}
		sortshippingarray();
	}else
		$errormsg=getshippingerror($errormsg);
	return $success;
}
function UPSCalculate($sXML,$international,&$errormsg, &$intShipping){
	global $pathtocurl,$curlproxy,$xxPlsZip,$upstestmode,$dumpshippingxml,$shipCountryID,$destZip;
	if(@$upstestmode==TRUE){ $upsurl='wwwcie.ups.com'; }else $upsurl='www.ups.com';
	if($destZip=='' && ! zipisoptional($shipCountryID)){
		$errormsg=$xxPlsZip;
		return(FALSE);
	}elseif($success=callcurlfunction('https://'.$upsurl.'/ups.app/xml/Rate', $sXML, $res, '', $errormsg, FALSE)){
		if(@$dumpshippingxml) dumpxmloutput($sXML,$res);
		$success=ParseUPSXMLOutput($res, $international,$errormsg,$errorcode,$intShipping);
		sortshippingarray();
		if($errorcode==111210) $errormsg=$GLOBALS['xxInvZip'];
		if($errorcode==110971) $errormsg=''; // May differ from published rates.
		if($errorcode==119070) $errormsg=''; // Large package surcharge.
	}else
		$errormsg=getshippingerror($errormsg);
	return $success;
}

function CanadaPostCalculate($sXML,$international,&$errormsg,&$intShipping){
	global $pathtocurl,$usecurlforfsock,$curlproxy,$destZip,$xxPlsZip,$dumpshippingxml,$shipCountryID,$canadaposttestmode;
	$success=true;
	if($destZip=='' && ! zipisoptional($shipCountryID)){
		$errormsg=$xxPlsZip;
		return(FALSE);
	}
	$success=callcurlfunction('https://'.(@$canadaposttestmode?'ct.':'').'soa-gw.canadapost.ca/rs/soap/rating/v2',$sXML,$res,'',$errormsg,FALSE);
	if(@$dumpshippingxml) dumpxmloutput($sXML,$res);
	if($success){
		$success=ParseCanadaPostXMLOutput($res,$international,$errormsg,$errorcode,$intShipping);
		sortshippingarray();
	}else
		$errormsg=getshippingerror($errormsg);
	return $success;
}
function parsefedexXMLoutput($sXML, $international, &$errormsg, &$errorcode, &$intShipping){
	global $xxDay,$xxDays,$uselistshippingrates,$commercialloc_,$origCountryCode,$shipCountryCode,$nofedexinternationalground,$fedextestmode,$fedexnamespace,$discountshippingfedex;
	$noError=TRUE;
	$errormsg='';
	$discntsApp='';
	$l=strpos($sXML, ']>');
	if($l>0) $sXML=substr($sXML, $l+2);
	$l=0;
	$fns=$fedexnamespace;
	$xmlDoc=new vrXMLDoc($sXML);
	$nodeList=$xmlDoc->nodeList->childNodes[0];
	for($i=0; $i < $nodeList->length; $i++){
		if(strtolower($nodeList->nodeName[$i])=='soapenv:body'||strtolower($nodeList->nodeName[$i])=='soap-env:body'){
			$nodeList=$nodeList->childNodes[$i];
		}
	}
	for($i=0; $i < $nodeList->length; $i++){
		if($nodeList->nodeName[$i]==$fns.'RateReply'){
			$nodeList=$nodeList->childNodes[$i];
		}
	}
	for($i=0; $i < $nodeList->length; $i++){
		if($nodeList->nodeName[$i]==$fns.'HighestSeverity'){
			if($nodeList->nodeValue[$i]=='ERROR'){
				$noError=FALSE;
				$e=$nodeList->childNodes[$i];
				for($j=0; $j < $e->length; $j++){
					if($e->nodeName[$j]==$fns.'Message'){
						$errormsg=$e->nodeValue[$j];
					}elseif($e->nodeName[$j]==$fns.'Code'){
						$errorcode=$e->nodeValue[$j];
					}
				}
			}
		}elseif($nodeList->nodeName[$i]==$fns.'Notifications'){
			$iserror=FALSE;
			$themessage='';
			$thecode='';
			$e=$nodeList->childNodes[$i];
			for($j=0; $j < $e->length; $j++){
				if($e->nodeName[$j]==$fns.'Message'){
					$themessage=$e->nodeValue[$j];
				}elseif($e->nodeName[$j]==$fns.'Code'){
					$thecode=$e->nodeValue[$j];
				}elseif($e->nodeName[$j]==$fns.'Severity'){
					$iserror=$e->nodeValue[$j]=='ERROR';
				}
			}
			if($iserror){
				$errormsg=$themessage;
				$errorcode=$thecode;
			}
		}elseif($nodeList->nodeName[$i]==$fns.'RateReplyDetails'){
			$wantthismethod=FALSE;
			$e=$nodeList->childNodes[$i];
			$entryweight=$e->getValueByTagName('BilledWeight');
			for($j=0; $j < $e->length; $j++){
				if($e->nodeName[$j]==$fns.'ServiceType'){
					$theservicename=str_replace('_','',$e->nodeValue[$j]);
					$wantthismethod=checkUPSShippingMeth($theservicename, $discntsApp, $showAs);
					// if($e->nodeValue[$j]=='FEDEXGROUND' && $shipCountryCode!='CA' && $shipCountryCode!='PR' && !$commercialloc_ && $entryweight<=70.0) $wantthismethod=FALSE;
					if($origCountryCode!=$shipCountryCode){
						// if(strpos($showAs,'FedEx Ground')!==FALSE && @$nofedexinternationalground==TRUE) $wantthismethod=FALSE;
						$showAs=str_replace('FedEx Ground', 'FedEx International Ground', $showAs);
					}
					if($wantthismethod){
						$intShipping[$l][0]=$showAs;
						$intShipping[$l][4]=$discntsApp;
					}
				}elseif($e->nodeName[$j]==$fns.'RatedShipmentDetails'){
					$t=$e->childNodes[$j];
					for($k=0; $k < $t->length; $k++){
						if($t->nodeName[$k]==$fns.'ShipmentRateDetail'){
							$intShipping[$l][2]=0;
							$u=$t->childNodes[$k];
							for($kk=0; $kk < $u->length; $kk++){
								if($u->nodeName[$kk]==$fns.'TotalNetFedExCharge'){
									$intShipping[$l][2]+=(double)$u->childNodes[$kk]->getValueByTagName($fns.'Amount');
								}elseif($u->nodeName[$kk]==$fns.'TotalFreightDiscounts'){
									if(@$uselistshippingrates==TRUE) $intShipping[$l][2]+=(double)$u->childNodes[$kk]->getValueByTagName($fns.'Amount');
								}
							}
						}
					}
				}elseif($e->nodeName[$j]==$fns.'DeliveryTimestamp'&&!@$GLOBALS['noshipdateestimate']){
					$today=getdate();
					$daytoday=$today['yday'];
					if(($ttimeval=strtotime($e->nodeValue[$j])) < 0){
						$intShipping[$l][1]=$e->nodeValue[$j] . $intShipping[$l][1];
					}else{
						$deldate=getdate($ttimeval);
						$daydeliv=$deldate['yday'];
						if($daydeliv < $daytoday) $daydeliv+=365;
						for($index=0; $index<=($daydeliv-$daytoday); $index++){
							$ckwekday=getdate(time()+60*60*24*$index);
							if($ckwekday['wday']==0 || $ckwekday['wday']==6) $daydeliv+=1;
						}
						$intShipping[$l][1]=($daydeliv - $daytoday) . ' ' . ($daydeliv - $daytoday < 2?$xxDay:$xxDays) . $intShipping[$l][1];
					}
				}elseif($e->nodeName[$j]==$fns.'TransitTime'&&!@$GLOBALS['noshipdateestimate']){
					if($e->nodeValue[$j]=='ONE_DAY')$intShipping[$l][1]='1 ' . $xxDay;
					if($e->nodeValue[$j]=='TWO_DAYS')$intShipping[$l][1]='2 ' . $xxDays;
					if($e->nodeValue[$j]=='THREE_DAYS')$intShipping[$l][1]='3 ' . $xxDays;
					if($e->nodeValue[$j]=='FOUR_DAYS')$intShipping[$l][1]='4 ' . $xxDays;
					if($e->nodeValue[$j]=='FIVE_DAYS')$intShipping[$l][1]='5 ' . $xxDays;
					if($e->nodeValue[$j]=='SIX_DAYS')$intShipping[$l][1]='6 ' . $xxDays;
					if($e->nodeValue[$j]=='SEVEN_DAYS')$intShipping[$l][1]='7 ' . $xxDays;
				}
			}
			if($wantthismethod){
				if(@$discountshippingfedex!='') $intShipping[$l][2]=round($intShipping[$l][2]*(1+$discountshippingfedex/100.0),2);
				$intShipping[$l][3]=TRUE;
				$l++;
			}else
				$intShipping[$l][1]='';
		}
	}
	return $noError;
}
function fedexcalculate($sXML,$international,&$errormsg,&$intShipping){
	global $destZip,$xxPlsZip,$payproviderpost,$dumpshippingxml,$fedexurl,$fedexnamespace,$shipCountryID;
	if($destZip=='' && ! zipisoptional($shipCountryID)){
		$errormsg=$xxPlsZip;
		return(FALSE);
	}
	if($success=callcurlfunction($fedexurl, $sXML, $xmlres, '', $errormsg, FALSE)){
		if(@$dumpshippingxml) dumpxmloutput($sXML,$xmlres);
		$pattern='/<(.{1,3}):RateReply/';
		if(preg_match($pattern, $xmlres, $matches)) $fedexnamespace=$matches[1].':'; else $fedexnamespace='';
		$success=parsefedexXMLoutput($xmlres, $international, $errormsg, $errorcode, $intShipping);
	}
	if($success) sortshippingarray(); else $errormsg=getshippingerror($errormsg);
	return $success;
}
function parseauspostXMLoutput($sXML,$international,&$errormsg,&$errorcode,&$intShipping){
	global $xxDay,$xxDays,$uselistshippingrates,$commercialloc_,$origCountryCode,$shipCountryCode,$nofedexinternationalground,$fedextestmode,$fedexnamespace,$discountshippingfedex;
	$noError=TRUE;
	$errormsg=$discntsApp='';
	$l=0;
	$xmlDoc=new vrXMLDoc($sXML);
	$nodeList=$xmlDoc->nodeList->childNodes[0];
	for($i=0; $i < $nodeList->length; $i++){
		if($nodeList->nodeName[$i]=='errorMessage'){
			$noError=FALSE;
			$errormsg=$nodeList->nodeValue[$i];
		}elseif($nodeList->nodeName[$i]=='service'){
			$wantthismethod=FALSE;
			$e=$nodeList->childNodes[$i];
			for($j=0; $j < $e->length; $j++){
				if($e->nodeName[$j]=='code'){
					$theservicename=$e->nodeValue[$j];
					$wantthismethod=checkUPSShippingMeth($theservicename,$discntsApp,$showAs);
					if($wantthismethod){
						$intShipping[$l][0]=$showAs;
						$intShipping[$l][4]=$discntsApp;
					}
				}elseif($e->nodeName[$j]=='price'){
					$intShipping[$l][2]=(double)$e->nodeValue[$j];
				}
			}
			if($wantthismethod){
				if(@$discountshippingauspost!='') $intShipping[$l][2]=round($intShipping[$l][2]*(1+$discountshippingauspost/100.0),2);
				$intShipping[$l][3]=TRUE;
				$l++;
			}else
				$intShipping[$l][1]='';
		}
	}
	return $noError;
}
function auspostcalculate($appackweight,$international,&$errormsg,&$intShipping){
	global $shipCountryCode,$origZip,$destZip,$packdims,$xmlfnheaders,$dumpshippingxml;
	$result=ect_query('SELECT AusPostAPI FROM admin WHERE adminID=1') or ect_error();
	$rs=ect_fetch_assoc($result);
	$authkey=$rs['AusPostAPI'];
	ect_free_result($result);
	if($international!='')
		$sXML='international/service.xml?country_code='.$shipCountryCode.'&weight='.$appackweight;
	else
		$sXML='domestic/service.xml?from_postcode='.$origZip.'&to_postcode='.$destZip.'&weight='.$appackweight.'&length=' . max(1,round($packdims[0],1)) . '&width=' . max(1,round($packdims[1],1)) . '&height=' . max(1,round($packdims[2],1));
	$xmlfnheaders=array('AUTH-KEY: ' . $authkey);
	$theurl='https://auspost.com.au/api/postage/parcel/'.$sXML;
	if($authkey==''){
		$success=FALSE;
		$errormsg='You must set your Australia Post API Key';
	}else{
		$success=callcurlfunction($theurl, '', $xmlres, '', $errormsg, FALSE);
	}
	if($success){
		if($success=parseauspostXMLoutput($xmlres,$international,$errormsg,$errorcode,$intShipping))
			sortshippingarray();
	}else
		$errormsg=getshippingerror($errormsg);
	if(@$dumpshippingxml) dumpxmloutput($sXML,$xmlres);
	return($success);
}
function dumpxmloutput($sentxml,$recvdxml){
	print str_replace('<','<br />&lt;',str_replace('</','&lt;/',$sentxml)) . "<br />\n";
	print str_replace('<','<br />&lt;',str_replace('</','&lt;/',$recvdxml)) . "<br />\n";
}
?>