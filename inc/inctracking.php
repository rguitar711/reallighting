<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(!@$GLOBALS['incfunctionsdefined']){print 'No incfunctions.php file';exit;}
global $alreadygotadmin;
if(@$_SERVER['CONTENT_LENGTH']!='' && $_SERVER['CONTENT_LENGTH'] > 10000) exit;
// ActivityList(0) = Address
// ActivityList(1) = SignedForByName
// ActivityList(2) = Not Used
// ActivityList(3) = Activity -> Status -> StatusType -> Code
// ActivityList(4) = Activity -> Status -> StatusType -> Description
// ActivityList(5) = Activity -> Status -> StatusCode -> Code
// ActivityList(6) = Activity -> Date
// ActivityList(7) = Activity -> Time
$alreadygotadmin = getadminsettings();
$incupscopyright=FALSE;
$incfedexcopyright=FALSE;
$alternateratesusps=FALSE;
$alternateratesups=FALSE;
$alternateratesfedex=FALSE;
$alternateratescanadapost=FALSE;
$alternateratesdhl=FALSE;
function dumpxmloutput($sentxml,$recvdxml){
	print str_replace('<','<br />&lt;',str_replace('</','&lt;/',$sentxml)) . "<br />\n";
	print str_replace('<','<br />&lt;',str_replace('</','&lt;/',$recvdxml)) . "<br />\n";
}
if($adminAltRates>0){
	$sSQL = "SELECT altrateid FROM alternaterates WHERE usealtmethod<>0 OR usealtmethodintl<>0";
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		if($rs['altrateid']==3) $alternateratesusps=TRUE;
		if($rs['altrateid']==4) $alternateratesups=TRUE;
		if($rs['altrateid']==6) $alternateratescanadapost=TRUE;
		if($rs['altrateid']==7 || $rs['altrateid']==8) $alternateratesfedex=TRUE;
		if($rs['altrateid']==9) $alternateratesdhl=TRUE;
	}
	ect_free_result($result);
}
$theshiptype='';
$canadaposttrackurl='https://' . (@$canadaposttestmode?'ct.':'') . 'soa-gw.canadapost.ca/vis/soap/track';
if(@$_REQUEST['carrier']!='')
	$theshiptype=$_REQUEST['carrier'];
else{
	if(@$_REQUEST['trackno']!=''){
		$trackno=str_replace(' ','',trim($_REQUEST['trackno']));

		if(preg_match('/^((\d{30})|(9\d{21})|(82\d{8})|((CF|CJ|CP|EA|EO|EC|LJ|LN|LZ|RA)\d{9}US))$/', $trackno)>0)
			$theshiptype='usps';
		
		if($theshiptype==''){
			if(preg_match('/^1Z\w{16}$/', $trackno)>0)
				$theshiptype='ups';
		}

		if($theshiptype==''){
			if(preg_match('/^((\d{12})|(\d{15}))$/', $trackno)>0)
				$theshiptype='fedex';
		}
		
		if($theshiptype==''){
			if(preg_match('/^(\d{10})$/', $trackno)>0)
				$theshiptype='dhl';
		}
		
		if($theshiptype==''){
			if(preg_match('/^((\d{16})|(\w\w\d{9}\w\w)|(\w{13}CA))$/i', $trackno)>0)
				$theshiptype='canadapost';
		}
	}
	if($theshiptype==''){
		$possshiptypes=0;
		if(@$defaulttrackingcarrier!='') $theshiptype=$defaulttrackingcarrier; else $theshiptype='ups';
		if($shipType==3 || $alternateratesusps || strpos(strtolower(@$trackingcarriers), 'usps')!==FALSE){
			$theshiptype='usps';
			$possshiptypes++;
		}
		if(@$shipType==4 || $alternateratesups || strpos(strtolower(@$trackingcarriers), 'ups')!==FALSE){
			$theshiptype='ups';
			$incupscopyright=TRUE;
			$possshiptypes++;
		}
		if(@$shipType==6 || $alternateratescanadapost || strpos(strtolower(@$trackingcarriers), 'canadapost')!==FALSE){
			$theshiptype='canadapost';
			$possshiptypes++;
		}
		if($shipType==7 || $shipType==8 || $alternateratesfedex || strpos(strtolower(@$trackingcarriers), 'fedex')!==FALSE){
			$theshiptype='fedex';
			$incfedexcopyright=TRUE;
			$possshiptypes++;
		}
		if(@$shipType==9 || $alternateratesdhl || strpos(strtolower(@$trackingcarriers), 'dhl')!==FALSE){
			$theshiptype='dhl';
			$possshiptypes++;
		}
		if($possshiptypes>1) $theshiptype='undecided';
	}
}
?>
<script type="text/javascript">
<!--
function viewlicense()
{
	var prnttext = '<html><head><STYLE TYPE="text/css">A:link {COLOR: #333333; TEXT-DECORATION: none}A:visited {COLOR: #333333; TEXT-DECORATION: none}A:active {COLOR: #333333; TEXT-DECORATION: none}A:hover {COLOR: #f39000; TEXT-DECORATION: none}TD {FONT-FAMILY: Verdana;}P {FONT-FAMILY: Verdana;}HR {color: #637BAD;height: 1px;}</STYLE></head><body><table width="100%" border="0" cellspacing="1" cellpadding="3">\n';
	prnttext+='<tr><td colspan="2" align="center"><a href="javascript:window.close()"><strong>Close Window</strong></a></td></tr>';
	prnttext+='<tr><td width="40"><img src="images/upslogo.png"  alt="UPS" /></td><td><p><span style="font-size:16px;font-family:Verdana;font-weight:bold">UPS Tracking Terms and Conditions</span></p></td></tr>';
	prnttext+='<tr><td colspan="2"><p><span style="font-size:12px;font-family:Verdana">The UPS package tracking systems accessed via this Web Site (the &quot;Tracking Systems&quot;) and tracking information obtained through this Web Site (the &quot;Information&quot;) are the private property of UPS. UPS authorizes you to use the Tracking Systems solely to track shipments tendered by or for you to UPS for delivery and for no other purpose. Without limitation, you are not authorized to make the Information available on any web site or otherwise reproduce, distribute, copy, store, use or sell the Information for commercial gain without the express written consent of UPS. This is a personal service, thus your right to use the Tracking Systems or Information is non-assignable. Any access or use that is inconsistent with these terms is unauthorized and strictly prohibited.</span></p></td></tr>';
	prnttext+='<tr><td colspan="2" align="center"><hr /><span style="font-size:10px;font-family:Verdana"><?php print str_replace("'","\'",$GLOBALS['xxUPStm'])?></span></td></tr>';
	prnttext+='<tr><td colspan="2" align="center">&nbsp;<br /><a href="javascript:window.close()"><strong>Close Window</strong></a></td></tr>';
	prnttext+='</table></body></html>';
	var newwin = window.open("","viewlicense",'menubar=no, scrollbars=yes, width=500, height=420, directories=no,location=no,resizable=yes,status=no,toolbar=no');
	newwin.document.open();
	newwin.document.write(prnttext);
	newwin.document.close();
}
function checkaccept()
{
  if (document.trackform.agreeconds.checked==false)
  {
    alert("Please note: To track your package(s), you must accept the UPS Tracking Terms and Conditions by selecting the checkbox below.");
    return (false);
  }else{
	document.trackform.submit();
  }
  return (true);
}
//-->
</script>
<?php
if($theshiptype=='canadapost'){ ?>
	<form method="post" name="trackform" action="tracking.php">
	<input type="hidden" name="carrier" value="canadapost" />
      <div class="ectdiv ecttracking">
		<div class="ectdivhead">
			<div class="trackinglogo"><img src="images/canadapost.gif" alt="CanadaPost" /></div>
			<div class="trackingtext">Canada Post<small>&reg;</small> Tracking Tool</div>
		</div>
<?php
function ParseCanadaPostTrackingOutput($sXML, &$totActivity, &$deliverydate, &$serviceDesc, &$packagecount, &$shiptoaddress, &$scheddeldate, &$signedforby, &$errormsg, &$activityList){
	$noError = TRUE;
	$totalCost = 0;
	$packCost = 0;
	$index = 0;
	$errormsg = "";
	$gotxml=FALSE;
	$theaddress="";
	// 1111111332936901 1371134583769923
	$xmlDoc = new vrXMLDoc($sXML);
	$nodeList = $xmlDoc->nodeList->childNodes[0];
	for($i = 0; $i < $nodeList->length; $i++){
		if(strpos($nodeList->nodeName[$i],':Body')!==FALSE){
			$nodeList = $nodeList->childNodes[$i];
		}
	}
	for($i = 0; $i < $nodeList->length; $i++){
		if($nodeList->nodeName[$i]=='soap:Fault'){
			$noError = FALSE;
			$e = $nodeList->childNodes[$i];
			for($j = 0; $j < $e->length; $j++){
				if($e->nodeName[$j]=='faultstring'){
					$errormsg = $e->nodeValue[$j];
					$noError=FALSE;
					if(strpos($errormsg,'element pin value')!==FALSE){
						if($storelang=='fr')$errormsg='Num&eacute;ro de Rep&eacute;rage Invalide'; else $errormsg='Invalid Tracking Number';
					}
				}
			}
		}elseif($nodeList->nodeName[$i]=='tns:get-tracking-detail-response'){
			$e = $nodeList->childNodes[$i];
			for($xi = 0; $xi < $e->length; $xi++){
				if($e->nodeName[$xi]=='messages'){
					$e = $e->childNodes[$xi];
					for($j = 0; $j < $e->length; $j++){
						if($e->nodeName[$j]=='message'){
							$t = $e->childNodes[$j];
							for($k = 0; $k < $t->length; $k++){
								if($t->nodeName[$k]=='description'){
									$errormsg = $t->nodeValue[$k];
									$noError=FALSE;
								}
							}
						}
					}
				}elseif($e->nodeName[$xi]=='tracking-detail'){
					$e = $e->childNodes[$xi];
					for($j = 0; $j < $e->length; $j++){
						if($e->nodeName[$j]=='expected-delivery-date'){
						}elseif($e->nodeName[$j]=='significant-events'){
							$t = $e->childNodes[$j];
							for($k = 0; $k < $t->length; $k++){
								if($t->nodeName[$k]=='occurrence'){
									$activityList[$totActivity][0]='';
									$ep = $t->childNodes[$k];
									for($pj = 0; $pj < $ep->length; $pj++){
										if($ep->nodeName[$pj]=='event-date'){
											$activityList[$totActivity][6] = $ep->nodeValue[$pj];
										}elseif($ep->nodeName[$pj]=='event-time'){
											$activityList[$totActivity][7] = $ep->nodeValue[$pj];
										}elseif($ep->nodeName[$pj]=='event-description'){
											$activityList[$totActivity][4] = $ep->nodeValue[$pj];
										}elseif($ep->nodeName[$pj]=='event-site'){
											$activityList[$totActivity][0] = $ep->nodeValue[$pj];
										}elseif($ep->nodeName[$pj]=='event-province'){
											$activityList[$totActivity][0].=', ' . $ep->nodeValue[$pj];
										}
									}
									$totActivity++;
								}
							}
						}
					}
				}
			}
		}
	}
	return $noError;
}
function CanadaPostTrack($trackNo){
	global $adminCanPostLogin,$adminCanPostPass,$canadaposttrackurl,$storelang;
	$lastloc="xxxxxx";
	$success = true;
	
	// (getpost('activity')=="LAST" ? "false" : "true") . "</v4:IncludeDetailedScans>"
	$sXML = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:trac="http://www.canadapost.ca/ws/soap/track">
   <soapenv:Header><wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsse:UsernameToken><wsse:Username>' . $adminCanPostLogin . '</wsse:Username><wsse:Password>' . $adminCanPostPass . '</wsse:Password></wsse:UsernameToken></wsse:Security></soapenv:Header>
   <soapenv:Body>
      <trac:get-tracking-detail-request>' . ($storelang=='fr'?'<locale>FR</locale>':'') . '<pin>' . $trackNo . '</pin>
      </trac:get-tracking-detail-request>
   </soapenv:Body></soapenv:Envelope>';

	$success = callcurlfunction($canadaposttrackurl, $sXML, $xmlres, '', $errormsg, FALSE);
	if($success){
		$totActivity = 0;
		if(@$GLOBALS['dumpshippingxml']) dumpxmloutput($sXML,$xmlres);
		$success = ParseCanadaPostTrackingOutput($xmlres, $totActivity, $deliverydate, $serviceDesc, $packagecount, $shiptoaddress, $scheduleddeliverydate, $signedforby, $errormsg, $activityList);
		if($success){
			for($index2=0; $index2 < $totActivity-1; $index2++){
				for($index=0; $index < $totActivity-1; $index++){
					if(($activityList[$index][6] . $activityList[$index][7]) > ($activityList[$index+1][6] . $activityList[$index+1][7])){
						$tempArr = $activityList[$index];
						$activityList[$index]=$activityList[$index+1];
						$activityList[$index+1]=$tempArr;
					}
				}
			}
			if(trim($serviceDesc)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Service Description</div>
		<div class="ectdivright"><?php print $serviceDesc?></div>
	  </div>
	<?php	}
			if(trim($packagecount)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Package Count</div>
		<div class="ectdivright"><?php print $packagecount?></div>
	  </div>
	<?php	}
			if(trim($shiptoaddress)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Ship-To Address</div>
		<div class="ectdivright"><?php print $shiptoaddress?></div>
	  </div>
	<?php	}
			if(trim($signedforby)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Signed For By</div>
		<div class="ectdivright"><?php print $signedforby?></div>
	  </div>
	<?php	}
			if(trim($deliverydate)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Delivery Date</div>
		<div class="ectdivright"><?php print $deliverydate?></div>
	  </div>
	<?php	} ?>
			<div class="ecttrackingresults" style="display:table">
			  <div style="display:table-row" class="tracktablehead">
				<div style="display:table-cell">Location</div>
				<div style="display:table-cell">Description</div>
				<div style="display:table-cell">Date&nbsp;/&nbsp;Time</div>
			  </div>
<?php		for($index=0; $index < $totActivity; $index++){
				$cellbg='class="ect'.(($index % 2)==0?"low":"high").'light"'; ?>
			  <div style="display:table-row">
			    <div style="display:table-cell" <?php print $cellbg?>><?php
									if($lastloc==$activityList[$index][0])
										print '<div style="text-align:center">&quot;</div>';
									else{
										print $activityList[$index][0];
										$lastloc = $activityList[$index][0];
									} ?></div>
				<div style="display:table-cell" <?php print $cellbg?>><?php print $activityList[$index][4];
									if(@$activityList[$index][1]!='') print "<br />Signed By :" . $activityList[$index][1]; ?></div>
				<div style="display:table-cell" <?php print $cellbg?>><?php
					$fxtimestamp = strtotime($activityList[$index][6]);
					$theDate=date('Y-m-d',$fxtimestamp);
					$theTime=date('H:m:s',$fxtimestamp);
					print $theDate . '<br />' . $theTime;?></div>
			  </div>
<?php		} ?>
			</div>
<?php	}else{ ?>
		<div class="ectdiv2column ectwarning">The tracking system returned the following error : <?php print $errormsg?></div>
<?php	}
	}
	return $success;
}
if(getpost('trackno')!='')
	CanadaPostTrack(getpost('trackno'));
?>
			  <div class="ectdivcontainer">
				<div class="ectdivleft">Please enter your Canada Post Tracking Number</div>
				<div class="ectdivright"><input type="text" size="30" name="trackno" value="<?php print htmlspecials(getrequest('trackno'))?>" /></div>
			  </div>
			  <div class="ectdivcontainer">
				<div class="ectdivleft">Show Activity</div>
				<div class="ectdivright"><select name="activity" size="1"><option value="LAST">Show Last Activity Only</option><option value="ALL"<?php if(getpost('activity')=="ALL") print ' selected="selected"'?>>Show All Activity</option></select></div>
			  </div>
			  <div class="ectdiv2column"><?php print imageorsubmit(@$imgtrackpackage,'Track Package','trackpackage')?></div>
			</div>
	</form>
<?php
}elseif($theshiptype=="ups"){
?>
	<form method="post" name="trackform" action="tracking.php">
	<input type="hidden" name="carrier" value="ups" />
      <div class="ectdiv ecttracking">
		<div class="ectdivhead">
			<div class="trackinglogo"><img src="images/upslogo.png" alt="UPS" /></div>
			<div class="trackingtext">UPS OnLine Tools&reg; Tracking</div>
		</div>
<?php
function getAddress($u, &$theAddress){
	$signedby = "";
	for($l = 0;$l < $u->length; $l++){
		//print "AddName : " . $u->nodeName[$l] . ", AddVal : " . $u->nodeValue[$l] . "<br />";
		if($u->nodeName[$l]=="AddressLine1")
			$addressline1 = $u->nodeValue[$l];
		elseif($u->nodeName[$l]=="AddressLine2")
			$addressline2 = $u->nodeValue[$l];
		elseif($u->nodeName[$l]=="AddressLine3")
			$addressline3 = $u->nodeValue[$l];
		elseif($u->nodeName[$l]=="City")
			$city = $u->nodeValue[$l];
		elseif($u->nodeName[$l]=="StateProvinceCode")
			$statecode = $u->nodeValue[$l];
		elseif($u->nodeName[$l]=="PostalCode")
			$postcode = $u->nodeValue[$l];
		elseif($u->nodeName[$l]=="CountryCode"){
			$sSQL = "SELECT countryName FROM countries WHERE countryCode='" . $u->nodeValue[$l] . "'";
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result) > 0){
				$rs=ect_fetch_assoc($result);
				$countrycode = $rs["countryName"];
			}else
				$countrycode = $u->nodeValue[$l];
			ect_free_result($result);
		}
	}
	$theAddress = "";
	if(@$addressline1!='') $theAddress.=$addressline1 . "<br />";
	if(@$addressline2!='') $theAddress.=$addressline2 . "<br />";
	if(@$addressline3!='') $theAddress.=$addressline3 . "<br />";
	if(@$city!='') $theAddress.=$city . "<br />";
	if(@$statecode!='' && @$postcode!='')
		$theAddress.=$statecode . ", " . $postcode . "<br />";
	else{
		if(@$statecode!='') $theAddress.=$statecode . "<br />";
		if(@$postcode!='') $theAddress.=$postcode . "<br />";
	}
	if(@$countrycode!='') $theAddress.=$countrycode . "<br />";
}
function ParseUPSTrackingOutput($sXML, &$totActivity, &$shipperNo, &$serviceDesc, &$shipperaddress, &$shiptoaddress, &$scheddeldate, &$rescheddeldate, &$errormsg, &$activityList){
	$noError = TRUE;
	$totalCost = 0;
	$packCost = 0;
	$index = 0;
	$errormsg = "";
	$gotxml=FALSE;
	$theaddress="";
	// print str_replace("<","<br />&lt;",$sXML) . "<br />\n";
	$xmlDoc = new vrXMLDoc($sXML);
	// Set t2 = xmlDoc.getElementsByTagName("TrackResponse").Item(0)
	$nodeList = $xmlDoc->nodeList->childNodes[0];
	for($ii = 0; $ii < $nodeList->length; $ii++){
		if($nodeList->nodeName[$ii]=="Response"){
			$e = $nodeList->childNodes[$ii];
			for($j = 0; $j < $e->length; $j++){
				if($e->nodeName[$j]=="ResponseStatusCode"){
					$noError = ((int)$e->nodeValue[$j])==1;
				}
				if($e->nodeName[$j]=="Error"){
					$errormsg = "";
					$t = $e->childNodes[$j];
					for($k = 0; $k < $t->length; $k++){
						if($t->nodeName[$k]=="ErrorSeverity"){
							if($t->nodeValue[$k]=="Transient")
								$errormsg = "This is a temporary error. Please wait a few moments then refresh this page.<br />" . $errormsg;
						}elseif($t->nodeName[$k]=="ErrorDescription"){
							$errormsg.=$t->nodeValue[$k];
						}
					}
				}
			}
		}elseif($nodeList->nodeName[$ii]=="Shipment"){ // no Top-level Error
			$e = $nodeList->childNodes[$ii];
			for($i = 0;$i < $e->length; $i++){
				// print "Nodename is : " . $e->nodeName[$i] . "<br />";
				switch($e->nodeName[$i]){
					case "Shipper":
						$t = $e->childNodes[$i];
						for($k = 0; $k < $t->length; $k++){
							if($t->nodeName[$k]=="ShipperNumber")
								$shipperNo = $t->nodeValue[$k];
							elseif($t->nodeName[$k]=="Address")
								getAddress($t->childNodes[$k], $shipperaddress);
						}
					break;
					case "ShipTo":
						$t = $e->childNodes[$i];
						for($k = 0; $k < $t->length; $k++){
							if($t->nodeName[$k]=="Address")
								getAddress($t->childNodes[$k], $shiptoaddress);
						}
					break;
					case "ScheduledDeliveryDate":
						$scheddeldate = $e->nodeValue[$i];
					break;
					case "Service":
						$t = $e->childNodes[$i];
						for($k = 0; $k < $t->length; $k++){
							if($t->nodeName[$k]=="X_Code_X"){
								switch((int)$t->nodeValue[$k]){
									case 1:
										$serviceDesc = "Next Day Air";
										break;
									case 2:
										$serviceDesc = "2nd Day Air";
										break;
									case 3:
										$serviceDesc = "Ground Service";
										break;
									case 7:
										$serviceDesc = "Worldwide Express";
										break;
									case 8:
										$serviceDesc = "Worldwide Expedited";
										break;
									case 11:
										$serviceDesc = "Standard service";
										break;
									case 12:
										$serviceDesc = "3-Day Select";
										break;
									case 13:
										$serviceDesc = "Next Day Air Saver";
										break;
									case 14:
										$serviceDesc = "Next Day Air Early AM";
										break;
									case 54:
										$serviceDesc = "Worldwide Express Plus";
										break;
									case 59:
										$serviceDesc = "2nd Day Air AM";
										break;
									case 64:
										$serviceDesc = "UPS Express NA1";
										break;
									case 65:
										$serviceDesc = "Express Saver";
										break;
								}
								// print "The service code is : " . $t->nodeName[$k] . ":" . $t->nodeValue[$k] . "<br />";
							}elseif($t->nodeName[$k]=="Description"){
								$serviceDesc = $t->nodeValue[$k];
							}
						}
					break;
					case "Package":
						$t = $e->childNodes[$i];
						for($k = 0; $k < $t->length; $k++){
							if($t->nodeName[$k]=="RescheduledDeliveryDate"){
								$rescheddeldate = $t->nodeValue[$k];
							}elseif($t->nodeName[$k]=="Activity"){
								$u = $t->childNodes[$k];
								for($l = 0; $l < $u->length; $l++){
									if($u->nodeName[$l]=="ActivityLocation"){
										$v = $u->childNodes[$l];
										for($m = 0; $m < $v->length; $m++){
											if($v->nodeName[$m]=="Address")
												getAddress($v->childNodes[$m], $activityList[$totActivity][0]);
											elseif($v->nodeName[$m]=="Description")
												$description = $v->nodeValue[$m];
											elseif($v->nodeName[$m]=="SignedForByName")
												$activityList[$totActivity][1] = $v->nodeValue[$m];
										}
									}elseif($u->nodeName[$l]=="Status"){
										$v = $u->childNodes[$l];
										for($m = 0; $m < $v->length; $m++){
											if($v->nodeName[$m]=="StatusType"){
												$w = $v->childNodes[$m];
												for($nn = 0; $nn < $w->length; $nn++){
													if($w->nodeName[$nn]=="Code")
														$activityList[$totActivity][3]=$w->nodeValue[$nn];
													elseif($w->nodeName[$nn]=="Description")
														$activityList[$totActivity][4]=$w->nodeValue[$nn];
												}
											}elseif($v->nodeName[$m]=="StatusCode"){
												$w = $v->childNodes[$m];
												for($nn = 0; $nn < $w->length; $nn++){
													if($w->nodeName[$nn]=="Code")
														$activityList[$totActivity][5]=$w->nodeValue[$nn];
												}
											}
										}
									}else{
										if($u->nodeName[$l]=="Date")
											$activityList[$totActivity][6]=$u->nodeValue[$l];
										elseif($u->nodeName[$l]=="Time")
											$activityList[$totActivity][7]=$u->nodeValue[$l];
									}
								}
								$totActivity++;
							}
						}
					break;
				}
			}
		}
	}
	return $noError;
}
function UPSTrack($trackNo){
	global $upsAccess,$upsUser,$upsPw,$pathtocurl,$curlproxy;
	$lastloc="xxxxxx";
	$success = true;

	$sXML = '<?xml version="1.0"?><AccessRequest xml:lang="en-US"><AccessLicenseNumber>' . $upsAccess . "</AccessLicenseNumber><UserId>" . $upsUser . "</UserId><Password>" . $upsPw . "</Password></AccessRequest>";
	$sXML.='<?xml version="1.0"?><TrackRequest xml:lang="en-US"><Request><TransactionReference><CustomerContext>Example 3</CustomerContext><XpciVersion>1.0001</XpciVersion></TransactionReference><RequestAction>Track</RequestAction><RequestOption>';
	if(getpost('activity')=="LAST") $sXML.="none"; else $sXML.="activity";
	$sXML.="</RequestOption></Request>";
	if(FALSE){
		$sXML.="<ReferenceNumber><Value>" . $trackNo . "</Value></ReferenceNumber>";
		$sXML.="<ShipperNumber>116593</ShipperNumber></TrackRequest>";
	}else
		$sXML.="<TrackingNumber>" . $trackNo . "</TrackingNumber></TrackRequest>";
	if(@$pathtocurl!=''){
		exec($pathtocurl . ' --data-binary ' . escapeshellarg($sXML) . ' https://www.ups.com/ups.app/xml/Track', $res, $retvar);
		$res = implode("\n",$res);
	}else{
		if (!$ch = curl_init()) {
			$success = false;
			$errormsg = "cURL package not installed in PHP";
		}else{
			curl_setopt($ch, CURLOPT_URL,'https://www.ups.com/ups.app/xml/Track'); 
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $sXML);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if(@$curlproxy!=''){
				curl_setopt($ch, CURLOPT_PROXY, $curlproxy);
			}
			$res = curl_exec($ch);
			curl_close($ch);
			// print str_replace("<","<br />&lt;",$res) . "<br />\n";
		}
	}
	if($success){
		$totActivity = 0;
		$success = ParseUPSTrackingOutput($res, $totActivity, $shipperNo, $serviceDesc, $shipperaddress, $shiptoaddress, $scheduleddeliverydate, $rescheddeliverydate, $errormsg, $activityList);

		if($success){
			for($index2=0; $index2 < $totActivity-1; $index2++){
				for($index=0; $index < $totActivity-1; $index++){
					if((int)($activityList[$index][6] . $activityList[$index][7]) > (int)($activityList[$index+1][6] . $activityList[$index+1][7])){
						$tempArr = $activityList[$index];
						$activityList[$index]=$activityList[$index+1];
						$activityList[$index+1]=$tempArr;
					}
				}
			}
			if(trim($shipperNo)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Shipper Number</div>
		<div class="ectdivright"><?php print $shipperNo?></div>
	  </div>
	<?php	}
			if(trim($serviceDesc)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Service Description</div>
		<div class="ectdivright"><?php print $serviceDesc?></div>
	  </div>
	<?php	}
			if(trim($shipperaddress)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Shipper Address</div>
		<div class="ectdivright"><?php print $shipperaddress?></div>
	  </div>
	<?php	}
			if(trim($shiptoaddress)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Ship-To Address</div>
		<div class="ectdivright"><?php print $shiptoaddress?></div>
	  </div>
	<?php	}
			if(trim($scheduleddeliverydate)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Sched. Delivery Date</div>
		<div class="ectdivright"><?php print date("m-d-Y",mktime(0,0,0,substr($scheduleddeliverydate,4,2),substr($scheduleddeliverydate,6,2),substr($scheduleddeliverydate,0,4)))?></div>
	  </div>
	<?php	}
			if(trim($rescheddeliverydate)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">ReSched. Delivery Date</div>
		<div class="ectdivright"><?php print date("m-d-Y",mktime(0,0,0,substr($rescheddeliverydate,4,2),substr($rescheddeliverydate,6,2),substr($rescheddeliverydate,0,4)))?></div>
	  </div>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Note</div>
		<div class="ectdivright">Your package is in the UPS system and has a rescheduled delivery date of <?php print date("m-d-Y",mktime(0,0,0,substr($rescheddeliverydate,4,2),substr($rescheddeliverydate,6,2),substr($rescheddeliverydate,0,4)))?></div>
	  </div>
	<?php	} ?>
		<div class="ecttrackingresults" style="display:table">
		  <div style="display:table-row" class="tracktablehead">
			<div style="display:table-cell">Location</div>
			<div style="display:table-cell">Description</div>
			<div style="display:table-cell">Date&nbsp;/&nbsp;Time</div>
		  </div>
<?php	for($index=0; $index < $totActivity; $index++){ 
			$cellbg='class="ect'.(($index % 2)==0?"low":"high").'light"';
?>
			  <div style="display:table-row">
			    <div style="display:table-cell" <?php print $cellbg?>><?php
									if($lastloc==$activityList[$index][0])
										print '<div style="text-align:center">&quot;</div>';
									else{
										print $activityList[$index][0];
										$lastloc = $activityList[$index][0];
									} ?></div>
				<div style="display:table-cell" <?php print $cellbg?>><?php print $activityList[$index][4];
									if(@$activityList[$index][1]!='') print "<br />Signed By :" . $activityList[$index][1]; ?></div>
				<div style="display:table-cell" <?php print $cellbg?>><?php
					$theDate = $activityList[$index][6];
					$theTime = $activityList[$index][7];
					print date("m-d-Y\<\B\R\>H:i:s",mktime(substr($theTime,0,2),substr($theTime,2,2),substr($theTime,4,2),substr($theDate,4,2),substr($theDate,6,2),substr($theDate,0,4)))?></div>
			  </div>
<?php	} ?>
		</div>
<?php	}else{ ?>
		<div class="ectdiv2column ectwarning">The tracking system returned the following error : <?php print $errormsg?></div>
<?php	}
	}
	return $success;
}
if(getpost('trackno')!='')
	UPSTrack(getpost('trackno'));
?>
			  <div class="ectdivcontainer">
				<div class="ectdivleft">Please enter your UPS Tracking Number</div>
				<div class="ectdivright"><input type="text" size="30" name="trackno" value="<?php print htmlspecials(getrequest('trackno'))?>" /></div>
			  </div>
			  <div class="ectdivcontainer">
				<div class="ectdivleft">Show Activity</div>
				<div class="ectdivright"><select name="activity" size="1"><option value="LAST">Show Last Activity Only</option><option value="ALL"<?php if(getpost('activity')=="ALL") print ' selected="selected"'?>>Show All Activity</option></select></div>
			  </div>
			  <div class="ectdiv2column"><?php print imageorbutton(@$imgviewlicense,'View License','viewlicense','viewlicense()',TRUE).' '.imageorbutton(@$imgtrackpackage,'Track Package','trackpackage','checkaccept()',TRUE)?></div>
			  <div class="ectdiv2column"><input type="checkbox" name="agreeconds" value="ON" <?php if(getpost('agreeconds')=="ON") print "checked"?> /> By selecting this box and the "Track Package" button, I agree to these <a class="ectlink" href="javascript:viewlicense();">Terms and Conditions</a>.</div>
			  <div class="trackingcopyright"><?php print str_replace("'","\'",$GLOBALS['xxUPStm'])?></div>
	  </div>
	</form>
<?php
}elseif($theshiptype=="usps"){
?>
	<form method="post" name="trackform" action="tracking.php">
	<input type="hidden" name="carrier" value="usps" />
      <div class="ectdiv ecttracking">
		<div class="ectdivhead">
			<div class="trackinglogo"><img src="images/usps_logo.gif" alt="USPS" /></div>
			<div class="trackingtext">USPS Tracking Tool</div>
		</div>
<?php
function ParseUSPSTrackingOutput($sXML, &$totActivity, $onlylast, &$serviceDesc, &$shipperaddress, &$shiptoaddress, &$scheddeldate, &$rescheddeldate, &$errormsg, &$activityList){
	$noError = TRUE;
	$totalCost = 0;
	$packCost = 0;
	$index = 0;
	$errormsg = "";
	$gotxml=FALSE;
	$theaddress="";
	// print str_replace("<","<br />&lt;",$sXML) . "<br />\n";
	$xmlDoc = new vrXMLDoc($sXML);

	if($xmlDoc->nodeList->nodeName[0]=="Error"){ // Top-level Error
		$noError = FALSE;
		$nodeList = $xmlDoc->nodeList->childNodes[0];
		for($i = 0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=="Description"){
				$errormsg = $nodeList->nodeValue[$i];
			}
		}
	}else{ // no Top-level Error
		$nodeList = $xmlDoc->nodeList->childNodes[0];
		for($i = 0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=="TrackInfo"){
				$e = $nodeList->childNodes[$i];
				for($j = 0; $j < $nodeList->childNodes[$i]->length; $j++){
					$companyname= "";
					$city="";
					$statecode="";
					$postcode="";
					$countrycode="";
					if($e->nodeName[$j]=="Error"){ // Lower-level error
						$t = $e->childNodes[$j];
						for($k = 0; $k < $t->length; $k++){
							if($t->nodeName[$k]=="Description"){
								$noError = FALSE;
								$errormsg = $t->nodeValue[$k];
							}
						}
					}elseif($e->nodeName[$j]=="TrackDetail"){
						if(!$onlylast){
							$t = $e->childNodes[$j];
							for($k = 0; $k <=7; $k++){
								$activityList[$totActivity][$k]='';
							}
							for($k = 0; $k < $t->length; $k++){
								switch($t->nodeName[$k]){
								case "EventDate":
									$activityList[$totActivity][6]=$t->nodeValue[$k];
									break;
								case "EventTime":
									$activityList[$totActivity][7]=$t->nodeValue[$k];
									break;
								case "Event":
									$activityList[$totActivity][4]=$t->nodeValue[$k];
									break;
								case "EventCity":
									$city = $t->nodeValue[$k];
									break;
								case "EventState":
									$statecode = $t->nodeValue[$k];
									break;
								case "EventZIPCode":
									$postcode = $t->nodeValue[$k];
									break;
								case "EventCountry":
									$countrycode = $t->nodeValue[$k];
									break;
								case "FirmName":
									$companyname = $t->nodeValue[$k];
									break;
								}
							}
							$theAddress = "";
							if(@$companyname!='') $theAddress.=$companyname . "<br />";
							if(@$city!='') $theAddress.=$city . "<br />";
							if(@$statecode!='' && @$postcode!='')
								$theAddress.=$statecode . ", " . $postcode . "<br />";
							else{
								if(@$statecode!='') $theAddress.=$statecode . "<br />";
								if(@$postcode!='') $theAddress.=$postcode . "<br />";
							}
							if(@$countrycode!='') $theAddress.=$countrycode . "<br />";
							$activityList[$totActivity][0] = $theAddress;
							$totActivity++;
						}
					}elseif($e->nodeName[$j]=="TrackSummary"){
						$t = $e->childNodes[$j];
						for($k = 0; $k < $t->length; $k++){
							switch($t->nodeName[$k]){
							case "EventDate":
								$scheddeldate=$t->nodeValue[$k] . $scheddeldate;
								break;
							case "EventTime":
								$scheddeldate=$scheddeldate . " " . $t->nodeValue[$k];
								break;
							case "Event":
								$serviceDesc=$t->nodeValue[$k];
								break;
							case "EventCity":
								$city = $t->nodeValue[$k];
								break;
							case "EventState":
								$statecode = $t->nodeValue[$k];
								break;
							case "EventZIPCode":
								$postcode = $t->nodeValue[$k];
								break;
							case "EventCountry":
								$countrycode = $t->nodeValue[$k];
								break;
							case "FirmName":
								$companyname = $t->nodeValue[$k];
								break;
							}
						}
						$theAddress = "";
						if(@$companyname!='') $theAddress.=$companyname . "<br />";
						if(@$city!='') $theAddress.=$city . "<br />";
						if(@$statecode!='' && @$postcode!='')
							$theAddress.=$statecode . ", " . $postcode . "<br />";
						else{
							if(@$statecode!='') $theAddress.=$statecode . "<br />";
							if(@$postcode!='') $theAddress.=$postcode . "<br />";
						}
						if(@$countrycode!='') $theAddress.=$countrycode . "<br />";
						$shiptoaddress = $theAddress;
					}
				}
				$totalCost+=$packCost;
				$packCost = 0;
			}
		}
	}
	return $noError;
}
function USPSTrack($trackNo){
	global $uspsUser,$pathtocurl,$curlproxy,$usecurlforfsock;
	$lastloc="xxxxxx";
	$success = true;
	$sXML = '<TrackFieldRequest USERID="'.$uspsUser.'"><TrackID ID="'.str_replace(' ','',getpost('trackno')).'"></TrackID></TrackFieldRequest>';
	//print str_replace("<","<br />&lt;",str_replace("</","&lt;/",$sXML)) . "<br />\n";
	$sXML = "API=TrackV2&XML=" . $sXML;
	if(@$usecurlforfsock){
		$success = callcurlfunction('http://production.shippingapis.com/ShippingAPI.dll', $sXML, $res, '', $errormsg, FALSE);
	}else{
		$header = "POST /ShippingAPI.dll HTTP/1.0\r\n";
		//$header = "POST /ShippingAPITest.dll HTTP/1.0\r\n";
		$header.="Content-Type: application/x-www-form-urlencoded\r\n";
		$header.='Content-Length: ' . strlen($sXML) . "\r\n\r\n";
		$fp = fsockopen ('production.shippingapis.com', 80, $errno, $errstr, 30);
		if (!$fp){
			echo "$errstr ($errno)"; // HTTP error handling
			return FALSE;
		}else{
			$res = "";
			fputs ($fp, $header . $sXML);
			while (!feof($fp)) {
				$res.=fgets ($fp, 1024);
			}
			fclose ($fp);
		}
	}
	//print str_replace("<","<br />&lt;",str_replace("</","&lt;/",$res)) . "<br />\n";
	if($success){
		$totActivity = 0;
		$success = ParseUSPSTrackingOutput($res, $totActivity, getpost('activity')=='LAST', $serviceDesc, $shipperaddress, $shiptoaddress, $scheduleddeliverydate, $rescheddeliverydate, $errormsg, $activityList);
		if($success){
			for($index2=0; $index2 < $totActivity-1; $index2++){
				for($index=0; $index < $totActivity-1; $index++){
					if(strtotime($activityList[$index][6] . " " . $activityList[$index][7]) > strtotime($activityList[$index+1][6] . ' ' . $activityList[$index+1][7])){
						$tempArr = $activityList[$index];
						$activityList[$index]=$activityList[$index+1];
						$activityList[$index+1]=$tempArr;
					}
				}
			}
			if(trim($serviceDesc)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Event</div>
		<div class="ectdivright"><?php print $serviceDesc?></div>
	  </div>
	<?php	}
			if(trim($shiptoaddress)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Address</div>
		<div class="ectdivright"><?php print $shiptoaddress?></div>
	  </div>
	<?php	}
			if(trim($scheduleddeliverydate)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Event Date</div>
		<div class="ectdivright"><?php print $scheduleddeliverydate?></div>
	  </div>
	<?php	}
			if($totActivity > 0){ ?>
		<div class="ecttrackingresults" style="display:table">
		  <div style="display:table-row" class="tracktablehead">
			<div style="display:table-cell">Location</div>
			<div style="display:table-cell">Description</div>
			<div style="display:table-cell">Date&nbsp;/&nbsp;Time</div>
		  </div>
<?php			for($index=0; $index < $totActivity; $index++){ 
					$cellbg='class="ect'.(($index % 2)==0?"low":"high").'light"'; ?>
			  <div style="display:table-row">
			    <div style="display:table-cell" <?php print $cellbg?>><?php
									if($lastloc==$activityList[$index][0])
										print '<div style="text-align:center">&quot;</div>';
									else{
										print $activityList[$index][0];
										$lastloc = $activityList[$index][0];
									} ?></div>
				<div style="display:table-cell" <?php print $cellbg?>><?php print $activityList[$index][4];
									if(@$activityList[$index][1]!='') print "<br />Signed By :" . $activityList[$index][1]; ?></div>
				<div style="display:table-cell" <?php print $cellbg?>><?php
					$theDate = $activityList[$index][6];
					$theTime = $activityList[$index][7];
					print $theDate . '<br />' . $theTime; ?></div>
			  </div>
<?php			} ?>
			</div>
<?php		}
		}else{ ?>
		<div class="ectdiv2column ectwarning">The tracking system returned the following error : <?php print $errormsg?></div>
<?php	}
	}
	return $success;
}
if(getpost('trackno')!='')
	USPSTrack(getpost('trackno'));
?>
			  <div class="ectdivcontainer">
				<div class="ectdivleft">Please enter your USPS Tracking Number</div>
				<div class="ectdivright"><input type="text" size="30" name="trackno" value="<?php print htmlspecials(getrequest('trackno'))?>" /></div>
			  </div>
			  <div class="ectdivcontainer">
				<div class="ectdivleft">Show Activity</div>
				<div class="ectdivright"><select name="activity" size="1"><option value="LAST">Show Last Activity Only</option><option value="ALL"<?php if(getpost('activity')=="ALL" || getpost('activity')=='') print ' selected="selected"'?>>Show All Activity</option></select></div>
			  </div>
			  <div class="ectdiv2column"><?php print imageorsubmit(@$imgtrackpackage,'Track Package','trackpackage')?></div>
	  </div>
	</form>
<?php
}elseif($theshiptype=="fedex"){
?>
	<form method="post" name="trackform" action="tracking.php">
	<input type="hidden" name="carrier" value="fedex" />
      <div class="ectdiv ecttracking">
		<div class="ectdivhead">
			<div class="trackinglogo"><img src="images/fedexlogo.png" alt="FedEx" /></div>
			<div class="trackingtext">FedEx<small>&reg;</small> Tracking Tool</div>
		</div>
<?php
function getFedExAddress($u, &$theAddress){
	global $fedexnamespace;
	$fns=$fedexnamespace;
	if($fns!='')$fns.=':';
	$signedby = "";
	for($l = 0;$l < $u->length; $l++){
		//print "AddName : " . $u->nodeName[$l] . ", AddVal : " . $u->nodeValue[$l] . "<br />";
		if($u->nodeName[$l]=="AddressLine1")
			$addressline1 = $u->nodeValue[$l];
		elseif($u->nodeName[$l]=="AddressLine2")
			$addressline2 = $u->nodeValue[$l];
		elseif($u->nodeName[$l]=="AddressLine3")
			$addressline3 = $u->nodeValue[$l];
		elseif($u->nodeName[$l]==$fns."City")
			$city = $u->nodeValue[$l];
		elseif($u->nodeName[$l]==$fns."StateOrProvinceCode")
			$statecode = $u->nodeValue[$l];
		elseif($u->nodeName[$l]==$fns."PostalCode")
			$postcode = $u->nodeValue[$l];
		elseif($u->nodeName[$l]==$fns."CountryCode"){
			$sSQL = "SELECT countryName FROM countries WHERE countryCode='" . $u->nodeValue[$l] . "'";
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result) > 0){
				$rs=ect_fetch_assoc($result);
				$countrycode = $rs["countryName"];
			}else
				$countrycode = $u->nodeValue[$l];
			ect_free_result($result);
		}
	}
	$theAddress = "";
	if(@$addressline1!='') $theAddress.=$addressline1 . "<br />";
	if(@$addressline2!='') $theAddress.=$addressline2 . "<br />";
	if(@$addressline3!='') $theAddress.=$addressline3 . "<br />";
	if(@$city!='') $theAddress.=$city . "<br />";
	if(@$statecode!='' && @$postcode!='')
		$theAddress.=$statecode . ", " . $postcode . "<br />";
	else{
		if(@$statecode!='') $theAddress.=$statecode . "<br />";
		if(@$postcode!='') $theAddress.=$postcode . "<br />";
	}
	if(@$countrycode!='') $theAddress.=$countrycode . "<br />";
}
function ParseFedexTrackingOutput($sXML, &$totActivity, &$deliverydate, &$serviceDesc, &$packagecount, &$shiptoaddress, &$scheddeldate, &$signedforby, &$errormsg, &$activityList){
	global $fedexnamespace;
	$noError = TRUE;
	$totalCost = 0;
	$packCost = 0;
	$index = 0;
	$errormsg = "";
	$gotxml=FALSE;
	$theaddress="";
	$fns=$fedexnamespace;
	if($fns!='')$fns.=':';
	$xmlDoc = new vrXMLDoc($sXML);
	$nodeList = $xmlDoc->nodeList->childNodes[0];
	for($i = 0; $i < $nodeList->length; $i++){
		if(strpos(strtolower($nodeList->nodeName[$i]),'env:body')!==FALSE){
			$nodeList=$nodeList->childNodes[$i];
		}
	}
	for($i = 0; $i < $nodeList->length; $i++){
		if($nodeList->nodeName[$i]==$fns.'TrackReply'){
			$nodeList=$nodeList->childNodes[$i];
		}
	}
	for($i = 0; $i < $nodeList->length; $i++){
		switch($nodeList->nodeName[$i]){
			case $fns."HighestSeverity":
				$noError = ($nodeList->nodeValue[$i]!='ERROR' && $nodeList->nodeValue[$i]!='FAILURE');
			break;
			case $fns."Notifications":
				$t = $nodeList->childNodes[$i];
				for($k = 0; $k < $t->length; $k++){
					if($t->nodeName[$k]==$fns."Message"){
						$errormsg = $t->nodeValue[$k];
					}
				}
			break;
			case $fns."TrackDetails":
				$fxw = $nodeList->childNodes[$i];
				for($k = 0; $k < $fxw->length; $k++){
					switch($fxw->nodeName[$k]){
					case $fns."DeliverySignatureName":
						$signedforby = $fxw->nodeValue[$k];
					break;
					case $fns."DestinationAddress":
						getFedExAddress($fxw->childNodes[$k], $shiptoaddress);
					break;
					case "DeliveredDate":
						$deliverydate = $fxw->nodeValue[$k] . $deliverydate;
					break;
					case "DeliveredTime":
						$deliverydate.=' ' . $fxw->nodeValue[$k];
					break;
					case $fns."ServiceType":
						$serviceDesc = $fxw->nodeValue[$k];
					break;
					case $fns."PackageCount":
						$packagecount = $fxw->nodeValue[$k];
					break;
					case $fns."Events":
						$t = $fxw->childNodes[$k];
						for($kfx = 0; $kfx < $t->length; $kfx++){
							if($t->nodeName[$kfx]==$fns."Timestamp"){
								$activityList[$totActivity][6] = $t->nodeValue[$kfx];
							}elseif($t->nodeName[$kfx]=="Time"){
								$activityList[$totActivity][7] = $t->nodeValue[$kfx];
							}elseif($t->nodeName[$kfx]=="StatusExceptionCode"){
								$activityList[$totActivity][3] = $t->nodeValue[$kfx];
							}elseif($t->nodeName[$kfx]==$fns."EventDescription" || $t->nodeName[$kfx]=="StatusExceptionDescription"){
								if($t->nodeValue[$kfx] != "Package status") $activityList[$totActivity][4] = $t->nodeValue[$kfx];
							}elseif($t->nodeName[$kfx]==$fns."Address"){
								getFedExAddress($t->childNodes[$kfx], $activityList[$totActivity][0]);
							}
						}
						if($activityList[$totActivity][4]!='') $totActivity++;
					break;
					}
				}
			break;
		}
	}
	return $noError;
}
function FedexTrack($trackNo){
	global $fedexuserkey,$fedexuserpwd,$fedexaccount,$fedexmeter,$fedexurl,$fedexnamespace;
	$lastloc="xxxxxx";
	$success = true;
	$sXML ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v4="http://fedex.com/ws/track/v4">' .
"   <soapenv:Header/>" .
"   <soapenv:Body>" .
"      <v4:TrackRequest>" .
"         <v4:WebAuthenticationDetail>" .
"            <v4:CspCredential>" .
"               <v4:Key>mKOUqSP4CS0vxaku</v4:Key>" .
"               <v4:Password>IAA5db3Pmhg3lyWW6naMh4Ss2</v4:Password>" .
"            </v4:CspCredential>" .
"            <v4:UserCredential>" .
"               <v4:Key>" . $fedexuserkey . "</v4:Key>" .
"               <v4:Password>" . $fedexuserpwd . "</v4:Password>" .
"            </v4:UserCredential>" .
"         </v4:WebAuthenticationDetail>" .
"         <v4:ClientDetail>" .
"            <v4:AccountNumber>" . $fedexaccount . "</v4:AccountNumber>" .
"            <v4:MeterNumber>" . $fedexmeter . "</v4:MeterNumber>" .
"            <v4:ClientProductId>IBTB</v4:ClientProductId>" .
"            <v4:ClientProductVersion>3272</v4:ClientProductVersion>" .
"         </v4:ClientDetail>" .
"         <v4:TransactionDetail>" .
"            <v4:CustomerTransactionId>track Request</v4:CustomerTransactionId>" .
"         </v4:TransactionDetail>" .
"         <v4:Version>" .
"            <v4:ServiceId>trck</v4:ServiceId>" .
"            <v4:Major>4</v4:Major>" .
"            <v4:Intermediate>1</v4:Intermediate>" .
"            <v4:Minor>0</v4:Minor>" .
"         </v4:Version>" .
"         <v4:PackageIdentifier>" .
"            <v4:Value>" . $trackNo . "</v4:Value>" .
"            <v4:Type>TRACKING_NUMBER_OR_DOORTAG</v4:Type>" .
"         </v4:PackageIdentifier>" .
"         <v4:IncludeDetailedScans>" . (getpost('activity')=="LAST" ? "false" : "true") . "</v4:IncludeDetailedScans>" .
"      </v4:TrackRequest>" .
"   </soapenv:Body>" .
"</soapenv:Envelope>";

	$success = callcurlfunction($fedexurl, $sXML, $xmlres, '', $errormsg, FALSE);
	if($success){
		$totActivity = 0;
		if(@$GLOBALS['dumpshippingxml']) dumpxmloutput($sXML,$xmlres);
		$pattern = '/<(.{1,3}):TrackReply/';
		preg_match($pattern, $xmlres, $matches);
		$fedexnamespace=@$matches[1];
		$success = ParseFedexTrackingOutput($xmlres, $totActivity, $deliverydate, $serviceDesc, $packagecount, $shiptoaddress, $scheduleddeliverydate, $signedforby, $errormsg, $activityList);
		if($success){
			for($index2=0; $index2 < $totActivity-1; $index2++){
				for($index=0; $index < $totActivity-1; $index++){
					if(($activityList[$index][6] . @$activityList[$index][7]) > ($activityList[$index+1][6] . @$activityList[$index+1][7])){
						$tempArr = $activityList[$index];
						$activityList[$index]=$activityList[$index+1];
						$activityList[$index+1]=$tempArr;
					}
				}
			}
			if(trim($serviceDesc)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Service Description</div>
		<div class="ectdivright"><?php print str_replace('_',' ',$serviceDesc)?></div>
	  </div>
	<?php	}
			if(trim($packagecount)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Package Count</div>
		<div class="ectdivright"><?php print $packagecount?></div>
	  </div>
	<?php	}
			if(trim($shiptoaddress)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Ship-To Address</div>
		<div class="ectdivright"><?php print $shiptoaddress?></div>
	  </div>
	<?php	}
			if(trim($signedforby)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Signed For By</div>
		<div class="ectdivright"><?php print $signedforby?></div>
	  </div>
	<?php	}
			if(trim($deliverydate)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Delivery Date</div>
		<div class="ectdivright"><?php print $deliverydate?></div>
	  </div>
	<?php	} ?>
		<div class="ecttrackingresults" style="display:table">
		  <div style="display:table-row" class="tracktablehead">
			<div style="display:table-cell">Location</div>
			<div style="display:table-cell">Description</div>
			<div style="display:table-cell">Date&nbsp;/&nbsp;Time</div>
		  </div>
<?php		for($index=0; $index < $totActivity; $index++){
				$cellbg='class="ect'.(($index % 2)==0?"low":"high").'light"'; ?>
			  <div style="display:table-row">
			    <div style="display:table-cell" <?php print $cellbg?>><?php
									if($lastloc==$activityList[$index][0])
										print '<div style="text-align:center">&quot;</div>';
									else{
										print $activityList[$index][0];
										$lastloc = $activityList[$index][0];
									} ?></div>
				<div style="display:table-cell" <?php print $cellbg?>><?php print $activityList[$index][4];
									if(@$activityList[$index][1]!='') print "<br />Signed By :" . $activityList[$index][1]; ?></div>
				<div style="display:table-cell" <?php print $cellbg?>><?php
					$fxtimestamp = strtotime($activityList[$index][6]);
					$theDate=date('Y-m-d',$fxtimestamp);
					$theTime=date('H:m:s',$fxtimestamp);
					print $theDate . '<br />' . $theTime;?></div>
			  </div>
<?php		} ?>
		</div>>
<?php	}else{ ?>
		<div class="ectdiv2column ectwarning">The tracking system returned the following error : <?php print $errormsg?></div>
<?php	}
	}
	return $success;
}
if(getpost('trackno')!='')
	FedexTrack(getpost('trackno'));
?>
			  <div class="ectdivcontainer">
				<div class="ectdivleft">Please enter your FedEx Tracking Number</div>
				<div class="ectdivright"><input type="text" size="30" name="trackno" value="<?php print htmlspecials(getrequest('trackno'))?>" /></div>
			  </div>
			  <div class="ectdivcontainer">
				<div class="ectdivleft">Show Activity</div>
				<div class="ectdivright"><select name="activity" size="1"><option value="LAST">Show Last Activity Only</option><option value="ALL"<?php if(getpost('activity')=="ALL") print ' selected="selected"'?>>Show All Activity</option></select></div>
			  </div>
			  <div class="ectdiv2column"><?php print imageorsubmit(@$imgtrackpackage,'Track Package','trackpackage')?></div>
			  <div class="trackingcopyright"><?php print $fedexcopyright?></p></div>
	  </div>
	</form>
<?php
}elseif($theshiptype=='dhl'){
?>
	<form method="post" name="trackform" action="tracking.php">
	<input type="hidden" name="carrier" value="dhl" />
      <div class="ectdiv ecttracking">
		<div class="ectdivhead">
			<div class="trackinglogo"><img src="images/dhllogo.gif" alt="DHL" style="margin-left:10px" /></div>
			<div class="trackingtext">DHL<small>&reg;</small> Tracking Tool</div>
		</div>
<?php
function getDHLDescription($u, &$theAddress){
	global $fedexnamespace;
	$fns=$fedexnamespace;
	if($fns!='')$fns.=':';
	$signedby = "";
	for($l = 0;$l < $u->length; $l++){
		if($u->nodeName[$l]=="Description")
			$addressline1 = $u->nodeValue[$l];
	}
	$theAddress=$addressline1;
}
function ParseDHLTrackingOutput($sXML, &$totActivity, &$deliverydate, &$origservicearea, &$shiptoaddress, &$scheddeldate, &$signedforby, &$errormsg, &$activityList){
	global $fedexnamespace;
	$noError = TRUE;
	$totalCost = 0;
	$packCost = 0;
	$index = 0;
	$errormsg = "";
	$gotxml=FALSE;
	$theaddress="";
	$xmlDoc = new vrXMLDoc($sXML);
	$nodeList = $xmlDoc->nodeList->childNodes[0];
	for($i = 0; $i < $nodeList->length; $i++){
		if(strpos(strtolower($nodeList->nodeName[$i]),'env:body')!==FALSE){
			$nodeList=$nodeList->childNodes[$i];
		}
	}
	for($i = 0; $i < $nodeList->length; $i++){
		if($nodeList->nodeName[$i]=='AWBInfo'){
			$nodeList=$nodeList->childNodes[$i];
		}
	}
	for($i = 0; $i < $nodeList->length; $i++){
		switch($nodeList->nodeName[$i]){
			case "Status":
				$t = $nodeList->childNodes[$i];
				for($k = 0; $k < $t->length; $k++){
					if($t->nodeName[$k]=="ActionStatus"){
						$noError = ($t->nodeValue[$k]=='success');
						$errormsg = $t->nodeValue[$k];
					}
				}
			break;
			case "ShipmentInfo":
				$fxw = $nodeList->childNodes[$i];
				for($k = 0; $k < $fxw->length; $k++){
					switch($fxw->nodeName[$k]){
					case "OriginServiceArea":
						getDHLDescription($fxw->childNodes[$k], $origservicearea);
					break;
					case "DestinationServiceArea":
						getDHLDescription($fxw->childNodes[$k], $shiptoaddress);
					break;
					case "DeliveredDate":
						$deliverydate = $fxw->nodeValue[$k] . $deliverydate;
					break;
					case "DeliveredTime":
						$deliverydate.=' ' . $fxw->nodeValue[$k];
					break;
					case "ServiceType":
						$serviceDesc = $fxw->nodeValue[$k];
					break;
					case "PackageCount":
						$packagecount = $fxw->nodeValue[$k];
					break;
					case "ShipmentEvent":
						$t = $fxw->childNodes[$k];
						for($kfx = 0; $kfx < $t->length; $kfx++){
							if($t->nodeName[$kfx]=="Date"){
								$activityList[$totActivity][6] = $t->nodeValue[$kfx];
							}elseif($t->nodeName[$kfx]=="Time"){
								$activityList[$totActivity][7] = $t->nodeValue[$kfx];
							}elseif($t->nodeName[$kfx]=="Signatory"){
								if($t->nodeValue[$kfx]!='') $signedforby=$t->nodeValue[$kfx];
							}elseif($t->nodeName[$kfx]=="ServiceEvent"){
								getDHLDescription($t->childNodes[$kfx], $activityList[$totActivity][4]);
								//$activityList[$totActivity][4] = $t->nodeValue[$kfx];
							}elseif($t->nodeName[$kfx]=="ServiceArea"){
								getDHLDescription($t->childNodes[$kfx], $activityList[$totActivity][0]);
							}
						}
						if($activityList[$totActivity][4]!='') $totActivity++;
					break;
					}
				}
			break;
		}
	}
	return $noError;
}
function DHLTrack($trackNo){
	global $DHLSiteID,$DHLSitePW;
	$lastloc="xxxxxx";
	$success = true;

	$sXML='<?xml version="1.0" encoding="utf-8" ?><req:KnownTrackingRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com TrackingRequestKnown.xsd">' .
	'<Request><ServiceHeader><SiteID>' . $DHLSiteID . '</SiteID><Password>' . $DHLSitePW . '</Password></ServiceHeader></Request>' .
	'<LanguageCode>en</LanguageCode><AWBNumber>'.$trackNo.'</AWBNumber><LevelOfDetails>' . (getpost('activity')=="LAST" ? "LAST_CHECK_POINT_ONLY" : "ALL_CHECK_POINTS") . '</LevelOfDetails><PiecesEnabled>S</PiecesEnabled></req:KnownTrackingRequest>';
	
	$success = callcurlfunction('https://xmlpi' . (@$upstestmode?'test':'') . '-ea.dhl.com/XMLShippingServlet', $sXML, $xmlres, '', $errormsg, FALSE);
	//print str_replace('<','<br />&lt;',str_replace('</','&lt;/',$sXML)) . "<br />\n";
	//print str_replace('<','<br />&lt;',str_replace('</','&lt;/',$xmlres)) . "<br />\n";
	if($success){
		$totActivity = 0;
		if(@$GLOBALS['dumpshippingxml']) dumpxmloutput($sXML,$xmlres);
		$success = ParseDHLTrackingOutput($xmlres, $totActivity, $deliverydate, $origservicearea,$shiptoservicearea, $scheduleddeliverydate, $signedforby, $errormsg, $activityList);
		if($success){
			for($index2=0; $index2 < $totActivity-1; $index2++){
				for($index=0; $index < $totActivity-1; $index++){
					if(($activityList[$index][6] . @$activityList[$index][7]) > ($activityList[$index+1][6] . @$activityList[$index+1][7])){
						$tempArr = $activityList[$index];
						$activityList[$index]=$activityList[$index+1];
						$activityList[$index+1]=$tempArr;
					}
				}
			}
			if(trim($origservicearea)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Origin Service Area</div>
		<div class="ectdivright"><?php print $origservicearea?></div>
	  </div>
	<?php	}
			if(trim($shiptoservicearea)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Destination Service Area</div>
		<div class="ectdivright"><?php print $shiptoservicearea?></div>
	  </div>
	<?php	}
			if(trim($signedforby)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Signed For By</div>
		<div class="ectdivright"><?php print $signedforby?></div>
	  </div>
	<?php	}
			if(trim($deliverydate)!=''){ ?>
	  <div class="ectdivcontainer">
		<div class="ectdivleft">Delivery Date</div>
		<div class="ectdivright"><?php print $deliverydate?></div>
	  </div>
	<?php	} ?>
		<div class="ecttrackingresults" style="display:table">
		  <div style="display:table-row" class="tracktablehead">
			<div style="display:table-cell">Location</div>
			<div style="display:table-cell">Description</div>
			<div style="display:table-cell">Date&nbsp;/&nbsp;Time</div>
		  </div>
<?php		for($index=0; $index < $totActivity; $index++){ 
			$cellbg='class="ect'.(($index % 2)==0?"low":"high").'light"'; ?>
			  <div style="display:table-row">
			    <div style="display:table-cell" <?php print $cellbg?>><?php
									if($lastloc==$activityList[$index][0])
										print '<div style="text-align:center">&quot;</div>';
									else{
										print $activityList[$index][0];
										$lastloc = $activityList[$index][0];
									} ?></div>
				<div style="display:table-cell" <?php print $cellbg?>><?php print $activityList[$index][4];
									if(@$activityList[$index][1]!='') print "<br />Signed By :" . $activityList[$index][1]; ?></div>
				<div style="display:table-cell" <?php print $cellbg?>><?php
					$fxtimestamp = $activityList[$index][6];
					print $fxtimestamp;?></div>
			  </div>
<?php	} ?>
		</div>
<?php	}else{ ?>
		<div class="ectdiv2column ectwarning">The tracking system returned the following error : <?php print $errormsg?></div>
<?php	}
	}
	return $success;
}
if(getpost('trackno')!='')
	DHLTrack(getpost('trackno'));
?>
			  <div class="ectdivcontainer">
				<div class="ectdivleft">Please enter your DHL Tracking Number</div>
				<div class="ectdivright"><input type="text" size="30" name="trackno" value="<?php print htmlspecials(getrequest('trackno'))?>" /></div>
			  </div>
			  <div class="ectdivcontainer">
				<div class="ectdivleft">Show Activity</div>
				<div class="ectdivright"><select name="activity" size="1"><option value="LAST">Show Last Activity Only</option><option value="ALL"<?php if(getpost('activity')=="ALL") print ' selected="selected"'?>>Show All Activity</option></select></div>
			  </div>
			  <div class="ectdiv2column"><?php print imageorsubmit(@$imgtrackpackage,'Track Package','trackpackage')?></div>
			</div>
	</form>
<?php
}else{ // undecided
?>
	<form method="post" action="tracking.php">
	<input type="hidden" name="carrier" id="carrier" value="xxxxxx" />
	  <div class="ectdiv ecttracking">
		<div class="ectdivhead trackingpleaseselect">Please select your shipping carrier.</div>
<?php	if(@$shipType==4 || $alternateratesups || strpos(strtolower(@$trackingcarriers), 'ups')!==FALSE){ ?>
		<div class="ectdivcontainer">
			<div class="trackingselectlogo"><img src="images/upslogo.png" alt="UPS" /></div>
			<div class="ectdivleft">Products shipped via UPS</div>
			<div class="ectdivright"><?php print imageorsubmit(@$imgtrackinggo,$GLOBALS['xxGo'].'" onclick="document.getElementById(\'carrier\').value=\'ups\'','trackinggo')?></div>
		</div>
<?php	}
		if($shipType==3 || $alternateratesusps || strpos(strtolower(@$trackingcarriers), 'usps')!==FALSE){ ?>
		<div class="ectdivcontainer">
			<div class="trackingselectlogo"><img src="images/usps_logo.gif" alt="USPS" /></div>
			<div class="ectdivleft">Products shipped via USPS</div>
			<div class="ectdivright"><?php print imageorsubmit(@$imgtrackinggo,$GLOBALS['xxGo'].'" onclick="document.getElementById(\'carrier\').value=\'usps\'','trackinggo')?></div>
		</div>
<?php	}
		if($shipType==7 || $shipType==8 || $alternateratesfedex || strpos(strtolower(@$trackingcarriers), 'fedex')!==FALSE){ ?>
		<div class="ectdivcontainer">
			<div class="trackingselectlogo"><img src="images/fedexlogo.png" alt="FedEx" /></div>
			<div class="ectdivleft">Products shipped via FedEx</div>
			<div class="ectdivright"><?php print imageorsubmit(@$imgtrackinggo,$GLOBALS['xxGo'].'" onclick="document.getElementById(\'carrier\').value=\'fedex\'','trackinggo')?></div>
		</div>
<?php	}
		if($shipType==9 || $alternateratesdhl || strpos(strtolower(@$trackingcarriers), 'dhl')!==FALSE){ ?>
		<div class="ectdivcontainer">
			<div class="trackingselectlogo"><img src="images/dhllogo.gif" alt="UPS" /></div>
			<div class="ectdivleft">Products shipped via DHL</div>
			<div class="ectdivright"><?php print imageorsubmit(@$imgtrackinggo,$GLOBALS['xxGo'].'" onclick="document.getElementById(\'carrier\').value=\'dhl\'','trackinggo')?></div>
		</div>
<?php	}
		if($shipType==6 || $alternateratescanadapost || strpos(strtolower(@$trackingcarriers), 'canadapost')!==FALSE){ ?>
		<div class="ectdivcontainer">
			<div class="trackingselectlogo"><img src="images/canadapost.gif" alt="Canada Post" /></div>
			<div class="ectdivleft">Products shipped via Canada Post</div>
			<div class="ectdivright"><?php print imageorsubmit(@$imgtrackinggo,$GLOBALS['xxGo'].'" onclick="document.getElementById(\'carrier\').value=\'canadapost\'','trackinggo')?></div>
		</div>
<?php	} ?>
	  </div>
	</form>
	  <div class="ectdiv ecttracking">
<?php	if($incupscopyright){ ?>
        <div class="trackingcopyright"><?php print str_replace("'","\'",$GLOBALS['xxUPStm'])?></div>
<?php	}
		if($incfedexcopyright){ ?>
		<div class="trackingcopyright"><?php print $fedexcopyright?></div>
<?php	} ?>
	  </div>
<?php
}
?>
