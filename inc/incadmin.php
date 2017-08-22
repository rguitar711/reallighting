<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
function microtime_float(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
$time_start = microtime_float();
$success=0;
if(@$storesessionvalue=='') $storesessionvalue='virtualstore'.time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
define('ecthelpbaseurl','http://www.ecommercetemplates.com/phphelp/ecommplus/');
if(@$_SESSION['loginid']==0 && getget('act')=='events'){
	logevent(@$_SESSION['loginuser'],'EVENTLOG',TRUE,'admin.php','VIEW LOG');
	$sSQL = "SELECT userID,eventType,eventDate,eventSuccess,eventOrigin,areaAffected FROM auditlog ORDER BY logID DESC";
?>
<div class="heading">
	<form method="post" action="dumporders.php">
	<input type="hidden" name="act" value="dumpevents" />
	<input type="submit" value="Dump Event Log" /> Event Log
	</form>
</div>
<table width="100%" class="stackable admin-table-a">
  <thead>
	<tr>
	  <th scope="col">User ID</th>
	  <th scope="col">Event Type</th>
	  <th scope="col">Success</th>
	  <th scope="col">Origin</th>
	  <th scope="col">Area Affected</th>
	  <th scope="col">Date</th>
	</tr>
  </thead>
<?php
	$result=ect_query($sSQL) or ect_error();
	if(ect_num_rows($result)>0){
		while($rs=ect_fetch_assoc($result)){
			if($rs['eventSuccess']!=0){ $startfont=''; $endfont=''; }else{ $startfont='<span style="color:#FF0000">'; $endfont='</span>'; } ?>
  <tr>
	<td><?php print $startfont . htmlspecials(trim($rs['userID'])!=''?$rs['userID']:'-') . $endfont?></td>
	<td><?php print $startfont . htmlspecials(trim($rs['eventType'])!=''?$rs['eventType']:'-') . $endfont?></td>
	<td><?php print $startfont . htmlspecials($rs['eventSuccess']!=0?'TRUE':'FALSE') . $endfont?></td>
	<td><?php print $startfont . htmlspecials(trim($rs['eventOrigin'])!=''?$rs['eventOrigin']:'-') . $endfont?></td>
	<td><?php print $startfont . htmlspecials(trim($rs['areaAffected'])!=''?$rs['areaAffected']:'-') . $endfont?></td>
	<td><?php print $startfont . htmlspecials(trim($rs['eventDate'])!=''?$rs['eventDate']:'-') . $endfont?></td>
  </tr>
<?php	}
	}else{ ?>
  <tr>
    <td class="new" colspan="6" align="center">No events in log.</td>
  </tr>
<?php
	}
	ect_free_result($result);
?>
</table>
<?php
}else{
	if(@$dateadjust=='') $dateadjust=0;
	$sSQL = 'SELECT adminVersion,adminUser,adminPassword FROM admin WHERE adminID=1';
	$result=ect_query($sSQL) or ect_error();
	$rs=ect_fetch_assoc($result);
	$storeVersion = $rs['adminVersion'];
	$adminUser = $rs['adminUser'];
	$adminPassword = $rs['adminPassword'];
	ect_free_result($result);
	$alreadygotadmin = getadminsettings();
	if(getget('writeck')=='no'){
		setcookie('WRITECKL', '', (time() - 2592000), '/', '', 0);
		setcookie('WRITECKP', '', (time() - 2592000), '/', '', 0);
		print '<meta http-equiv="Refresh" content="2; URL=admin.php">';
		$success=1;
	}
	$admindatestr='Y-m-d';
	if(@$admindateformat=='') $admindateformat=0;
	if($admindateformat==1)
		$admindatestr='m/d/Y';
	elseif($admindateformat==2)
		$admindatestr='d/m/Y';
	if($success==1){ ?>
			  <tr> 
				<td colspan="2" width="100%" align="center"><p>&nbsp;</p><p>&nbsp;</p>
				  <p><strong><?php print $yyOpSuc?></strong></p><p>&nbsp;</p>
				  <p><span style="font-size:10px"><?php print $yyNowFrd?><br /><br /><?php print $yyNoAuto?> <a href="admin.php"><?php print $yyClkHer?></a>.</span></td>
			  </tr>
<?php
	}elseif($success==2){ ?>
			  <tr> 
				<td colspan="2" width="100%" align="center"><p>&nbsp;</p><p>&nbsp;</p>
				  <p><strong><?php print $yyOpFai?></strong></p><p>&nbsp;</p>
				  <p><?php print $yyCorCoo?> <?php print $yyCorLI?> <a href="login.php"><?php print $yyClkHer?></a>.</p></td>
			  </tr>
<?php
	}else{
		if(substr(@$_SESSION['loggedonpermissions'],1,1)=='X'){ ?>
<div class="row">
	<div class="one_fourth home_boxes">
		<div class="full_width round_all box">
		<div class="box_title round-top" id="newordersdiv"><a href="<?php print ecthelpbaseurl?>help.asp#orders" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a>
		<h3><a href="adminorders.php"><?php print $yyVwOrd?></a></h3>
		</div>
		<div class="box_new" id="neworders" onclick="document.location='adminorders.php'">-</div>
		</div>
		<div class="full_width round_all box">
		<div class="box_title round-top" id="newgiftcertdiv"><a href="<?php print ecthelpbaseurl?>help.asp#giftcert" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a>
		<h3><a href="admingiftcert.php">Gift Certificates</a></h3>
		</div>
		<div class="box_new" id="newgiftcert" onclick="document.location='admingiftcert.php'">-</div>
		</div>
	</div>
	<div class="three_fourths last">
		<table id="latestorders" width="100%" class="quickstats neworders" style="margin-top:0;">
		<tr><th>Customer</th><th>Date</th><th>Status</th><th>Total</th></tr>
		</table>
	</div>
</div>
<div id="equalize" class="row home_boxes">
	<div class="one_sixth round_all box">
		<div class="box_title round-top" id="newaffiliatediv"><a href="<?php print ecthelpbaseurl?>help.asp#affiliate" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a>
		<h3><a href="adminaffil.php"><?php print $yyVwAff?></a></h3>
		</div>
		<div class="box_new" id="newaffiliate" onclick="document.location='adminaffil.php'">-</div>
	</div>
	<div class="one_sixth round_all box">
		<div class="box_title round-top" id="newratingsdiv"><a href="<?php print ecthelpbaseurl?>help.asp#ratings" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a>
		<h3><a href="adminratings.php"><?php print $yyVwRat?></a></h3>
		</div>
		<div class="box_new" id="newratings" onclick="document.location='adminratings.php'">-</div>
	</div>
	<div class="one_sixth last_third round_all box">
		<div class="box_title round-top" id="newaccountsdiv"><a href="<?php print ecthelpbaseurl?>help.asp#clientlogin" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a>
		<h3><a href="adminclientlog.php"><?php print $yyCliLog?></a></h3>
		</div>
		<div class="box_new" id="newaccounts" onclick="document.location='adminclientlog.php'">-</div>
	</div>
	<?php	if(@$notifybackinstock){ ?>
	<div class="one_sixth round_all box">
		<div class="box_title round-top" id="newstocknotifydiv"><a href="<?php print ecthelpbaseurl?>help.asp#notifystock" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a>
		<h3><a href="adminprods.php?act=stknot"><?php print $yyStkNot?></a></h3>
		</div>
		<div class="box_new" id="newstocknotify" onclick="document.location='adminprods.php?act=stknot'">-</div>
	</div>
	<?php	}
			if(@$_SESSION['loginid']==0){ ?>
	<div class="one_sixth round_all box">
		<div class="box_title round-top" id="newlogeventsdiv"><a href="<?php print ecthelpbaseurl?>help.asp#actlog" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a>
		<h3><a href="admin.php?act=events">Activity Log</a></h3>
		</div>
		<div class="box_new" id="newlogevents" onclick="document.location='admin.php?act=events'">-</div>
	</div>
	<?php	} ?>
	<div class="one_sixth last round_all box">
		<div class="box_title round-top" id="newmaillistdiv"><a href="<?php print ecthelpbaseurl?>help.asp#maillist" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a>
		<h3><a href="adminmailinglist.php"><?php print $yyMaLiMa?></a></h3>
		</div>
		<div class="box_new" id="newmaillist" onclick="document.location='adminmailinglist.php'">-</div>
	</div>
</div>
<div class="row">
<?php
// this month, last month and this month last year order totals	
if(@$dumpadminstats) print "Section 1 : " . (microtime_float()-$time_start)."<br />";
$thismonthorders=$thismonthtotal=$lastmonthorders=$lastmonthtotal=$yearorders=$yeartotal='';
if(@$homeordersstatus!='') $ordersstatus='ordStatus IN ('.$homeordersstatus.')'; else $ordersstatus='ordStatus>=3';
$sSQL = " SELECT COUNT(*) AS totalorders, SUM(ordTotal) AS totalvalue FROM orders WHERE ".$ordersstatus." AND YEAR(ordDate) = YEAR(CURRENT_DATE ) AND MONTH(ordDate) = MONTH(CURRENT_DATE)";
$result=ect_query($sSQL) or ect_error();
if($rs=ect_fetch_assoc($result)){
	if(!is_null($rs['totalorders'])){$thismonthorders[0]=$rs['totalorders'];$thismonthtotal[0]=$rs['totalvalue'];}
}
if(@$dumpadminstats) print "Section 2 : " . (microtime_float()-$time_start)."<br />";
$sSQL = " SELECT COUNT(*) AS totalorders, SUM(ordTotal) AS totalvalue FROM orders WHERE ".$ordersstatus." AND YEAR(ordDate) = YEAR(CURRENT_DATE  - INTERVAL 12 MONTH) AND MONTH(ordDate) = MONTH(CURRENT_DATE - INTERVAL 12 MONTH)";
$result=ect_query($sSQL) or ect_error();
if($rs=ect_fetch_assoc($result)){
	if(!is_null($rs['totalorders'])){$thismonthorders[1]=$rs['totalorders'];$thismonthtotal[1]=$rs['totalvalue'];}
}
if(@$dumpadminstats) print "Section 3 : " . (microtime_float()-$time_start)."<br />";
$sSQL = " SELECT COUNT(*) AS totalorders, SUM(ordTotal) AS totalvalue FROM orders WHERE ".$ordersstatus." AND YEAR(ordDate) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)  AND MONTH(ordDate) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)";
$result=ect_query($sSQL) or ect_error();
if($rs=ect_fetch_assoc($result)){
	if(!is_null($rs['totalorders'])){$lastmonthorders[0]=$rs['totalorders'];$lastmonthtotal[0]=$rs['totalvalue'];}
}
if(@$dumpadminstats) print "Section 4 : " . (microtime_float()-$time_start)."<br />";
$sSQL = " SELECT COUNT(*) AS totalorders, SUM(ordTotal) AS totalvalue FROM orders WHERE ".$ordersstatus." AND YEAR(ordDate) = YEAR(CURRENT_DATE - INTERVAL 13 MONTH)  AND MONTH(ordDate) = MONTH(CURRENT_DATE - INTERVAL 13 MONTH)";
$result=ect_query($sSQL) or ect_error();
if($rs=ect_fetch_assoc($result)){
	if(!is_null($rs['totalorders'])){$lastmonthorders[1]=$rs['totalorders'];$lastmonthtotal[1]=$rs['totalvalue'];}
}
if(@$dumpadminstats) print "Section 5 : " . (microtime_float()-$time_start)."<br />";
$sSQL = " SELECT COUNT(*) AS totalorders, SUM(ordTotal) AS totalvalue FROM orders WHERE ".$ordersstatus." AND ordDate BETWEEN '" . date("Y-01-01") . "' AND '" . date("Y-m-d",strtotime("+1 day")) . "'";
$result=ect_query($sSQL) or ect_error();
if($rs=ect_fetch_assoc($result)){
	if(!is_null($rs['totalorders'])){$yearorders[0]=$rs['totalorders'];$yeartotal[0]=$rs['totalvalue'];}
}
if(@$dumpadminstats) print "Section 6 : " . (microtime_float()-$time_start)."<br />";
$sSQL = " SELECT COUNT(*) AS totalorders, SUM(ordTotal) AS totalvalue FROM orders WHERE ".$ordersstatus." AND ordDate BETWEEN '" . date("Y-01-01",strtotime("-1 year")) . "' AND '" . date("Y-m-d",strtotime("-1 year")) . "'";
$result=ect_query($sSQL) or ect_error();
if($rs=ect_fetch_assoc($result)){
	if(!is_null($rs['totalorders'])){$yearorders[1]=$rs['totalorders'];$yeartotal[1]=$rs['totalvalue'];}
}
if(@$dumpadminstats) print "Section 7 : " . (microtime_float()-$time_start)."<br />";
$sSQL = " SELECT COUNT(*) AS totalorders, SUM(ordTotal) AS totalvalue FROM orders WHERE ".$ordersstatus." AND ordDate BETWEEN '" . date("Y-m-d",strtotime("-1 year")) . "' AND '" . date("Y-m-d",strtotime("+1 day")) . "'";
$result=ect_query($sSQL) or ect_error();
if($rs=ect_fetch_assoc($result)){
	if(!is_null($rs['totalorders'])){$last12[0]=$rs['totalorders'];$last12total[0]=$rs['totalvalue'];}
}
if(@$dumpadminstats) print "Section 8 : " . (microtime_float()-$time_start)."<br />";
$sSQL = " SELECT COUNT(*) AS totalorders, SUM(ordTotal) AS totalvalue FROM orders WHERE ".$ordersstatus." AND ordDate BETWEEN '" . date("Y-m-d",strtotime("-2 year")) . "' AND '" . date("Y-m-d",strtotime("-1 year")) . "'";
$result=ect_query($sSQL) or ect_error();
if($rs=ect_fetch_assoc($result)){
	if(!is_null($rs['totalorders'])){$last12[1]=$rs['totalorders'];$last12total[1]=$rs['totalvalue'];}
}
if(@$dumpadminstats) print "Section 9 : " . (microtime_float()-$time_start)."<br />";
$sSQL = " SELECT COUNT(*) AS totalorders, SUM(ordTotal) AS totalvalue FROM orders WHERE ".$ordersstatus."";
$result=ect_query($sSQL) or ect_error();
if($rs=ect_fetch_assoc($result)){
	if(!is_null($rs['totalorders'])){$alltime=$rs['totalorders'];$alltimetotal=$rs['totalvalue'];}
}
if(@$dumpadminstats) print "Section 10 : " . (microtime_float()-$time_start)."<br />";
// END this month, last month and this month last year order totals	

$sincedate = date('l jS F', strtotime('-30 days'));
$sinceweekdate = date('l jS F', strtotime('-7 days'));
$dbdate = date('Y-m-d H:i:s', strtotime('-30 days'));
$dbweekdate = date('Y-m-d H:i:s', strtotime('-7 days'));
$thismonth=date('M');
$lastmonth = date('M', strtotime('first day of last month'));
$lastyear = date('M Y', strtotime('-1 year'));
$lastlastyear = date('M y', strtotime('-2 year'));
// echo $sincedate.' '.$dbdate;
?>
<div class="one_third">
<h3 class="round_top">Order Stats<br><span style="font-weight:normal;font-size:10px">This month, last month, last year</span></h3>
<table class="quickstats">
<tr><th>&nbsp;</th><th>&nbsp;</th><th style="text-align:right;">No</th><th style="text-align:right;">Value</th></tr>
<tr>
	<td style="text-align:right;vertical-align:middle" rowspan="2"><?php print $thismonth?></td>
	<td style="text-align:right;font-size:0.9em">This Year</td>
	<td style="text-align:right;font-size:0.9em" title="<?php print FormatEuroCurrency($thismonthorders[0]==0?0:$thismonthtotal[0]/$thismonthorders[0]);?>"><?php print $thismonthorders[0] . '</td><td style="text-align:right;font-size:0.9em">' . FormatEuroCurrency($thismonthtotal[0]);?></td>
</tr>
<tr>
	<td style="text-align:right;font-size:0.9em;color:#FF6060">Last Year</td>
	<td style="text-align:right;font-size:0.9em;color:#FF6060" title="<?php print FormatEuroCurrency($thismonthorders[1]==0?0:$thismonthtotal[1]/$thismonthorders[1]);?>"><?php print $thismonthorders[1] . '</td><td style="text-align:right;font-size:0.9em;color:#FF6060">' . FormatEuroCurrency($thismonthtotal[1]);?></td>
</tr>
<tr>
	<td style="text-align:right;vertical-align:middle" rowspan="2"><?php print $lastmonth?></td>
	<td style="text-align:right;font-size:0.9em">This Year</td>
	<td style="text-align:right;font-size:0.9em" title="<?php print FormatEuroCurrency($lastmonthorders[0]==0?0:$lastmonthtotal[0]/$lastmonthorders[0]);?>"><?php print $lastmonthorders[0] . '</td><td style="text-align:right;font-size:0.9em">' . FormatEuroCurrency($lastmonthtotal[0]);?></td>
</tr>
<tr>
	<td style="text-align:right;font-size:0.9em;color:#FF6060">Last Year</td>
	<td style="text-align:right;font-size:0.9em;color:#FF6060" title="<?php print FormatEuroCurrency($lastmonthorders[1]==0?0:$lastmonthtotal[1]/$lastmonthorders[1]);?>"><?php print $lastmonthorders[1] . '</td><td style="text-align:right;font-size:0.9em;color:#FF6060">' . FormatEuroCurrency($lastmonthtotal[1]);?></td>
</tr>
<tr>
	<td style="text-align:right;vertical-align:middle" rowspan="2"><div title="January 1 - Now"><?php print "Jan 1 &raquo;"?></div></td>
	<td style="text-align:right;font-size:0.9em">This Year</td>
	<td style="text-align:right;font-size:0.9em" title="<?php print FormatEuroCurrency($yearorders[0]==0?0:$yeartotal[0]/$yearorders[0]);?>"><?php print $yearorders[0] . '</td><td style="text-align:right;font-size:0.9em">' . FormatEuroCurrency($yeartotal[0]);?></td>
</tr>
<tr>
	<td style="text-align:right;font-size:0.9em;color:#FF6060">Last Year</td>
	<td style="text-align:right;font-size:0.9em;color:#FF6060" title="<?php print FormatEuroCurrency($yearorders[1]==0?0:$yeartotal[1]/$yearorders[1]);?>"><?php print $yearorders[1] . '</td><td style="text-align:right;font-size:0.9em;color:#FF6060">' . FormatEuroCurrency($yeartotal[1]);?></td>
</tr>
<tr>
	<td style="text-align:right;vertical-align:middle" rowspan="2"><div title="Last 12 Months"><?php print '12 Mo.'?></div></td>
	<td style="text-align:right;font-size:0.9em">This Year</td>
	<td style="text-align:right;font-size:0.9em" title="<?php print FormatEuroCurrency($last12[0]==0?0:$last12total[0]/$last12[0]);?>"><?php print $last12[0] . '</td><td style="text-align:right;font-size:0.9em">' . FormatEuroCurrency($last12total[0]);?></td>
</tr>
<tr>
	<td style="text-align:right;font-size:0.9em;color:#FF6060">Last Year</td>
	<td style="text-align:right;font-size:0.9em;color:#FF6060" title="<?php print FormatEuroCurrency($last12[1]==0?0:$last12total[1]/$last12[1]);?>"><?php print $last12[1] . '</td><td style="text-align:right;font-size:0.9em;color:#FF6060">' . FormatEuroCurrency($last12total[1]);?></td>
</tr>
<tr>
	<td style="text-align:right;white-space:nowrap"><div title="All Time Sales"><?php print 'All Time'?></div></td>
	<td style="font-size:0.9em">&nbsp;</td>
	<td style="text-align:right;font-size:0.9em" title="<?php print FormatEuroCurrency($alltime==0?0:$alltimetotal/$alltime);?>"><?php print $alltime . '</td><td style="text-align:right;font-size:0.9em">' . FormatCurrencyZeroDP($alltimetotal);?></td>
</tr>
</table>
</div>

<div class="one_third">
<h3 class="round_top">Top Sellers: Last 30 days<br><span style="font-weight:normal;font-size:10px">Since <?php print $sincedate;?></span></h3>
<table class="quickstats">
<tr><th style="width:10%"></th><th style="text-align:left;">Prod</th><th>Sold</th><th style="text-align:right;">Value</th></tr>
<?php 
$count=1;
$prevbought=0;
$sSQL="SELECT cartProdName, SUM(cartQuantity) AS numbought, SUM(cartProdPrice*cartQuantity) AS totalvalue FROM cart WHERE cartDateAdded>='".$dbdate."' AND cartCompleted=1 GROUP BY cartProdName ORDER BY numbought DESC,totalvalue DESC LIMIT 0,10";
$result2=ect_query($sSQL) or ect_error();
if(ect_num_rows($result2)>0){
		while($rs2=ect_fetch_assoc($result2)){
			print '<tr><td style="width:10%"><strong>'.$count.'</strong></td><td style="text-align:left;">'.$rs2['cartProdName'].'</td><td>'.$rs2['numbought'].'</td><td style="text-align:right;">'.FormatEuroCurrency($rs2['totalvalue']).'</td></tr>';
			$count++;
		}
}
?>
</table>
</div>
<div class="one_third last">
<h3 class="round_top">Top Customers: Last 30 days<br><span style="font-weight:normal;font-size:10px">Since <?php print $sincedate;?></span></h3>
<table class="quickstats">
<tr><th style="width:10%"></th><th style="text-align:left;">Customer</th><th style="text-align:right;">Spent</th></tr>
<?php
$count=1;
$sSQL = "SELECT ordName,ordLastName,ordTotal FROM orders WHERE ordDate>='".$dbdate."' AND ".$ordersstatus;
$totalsarray=array();
$result3=ect_query($sSQL) or ect_error();
if(ect_num_rows($result3)>0){
		while($rs3=ect_fetch_assoc($result3)){
			$thename=ucwords(trim($rs3['ordName'].' '.$rs3['ordLastName']));
			if(array_key_exists($thename,$totalsarray)) $totalsarray[$thename]+=$rs3['ordTotal']; else $totalsarray[$thename]=$rs3['ordTotal'];
		}
}
arsort($totalsarray);
$count=1;
foreach($totalsarray as $key=>$val){
	print '<tr><td style="width:10%"><strong>'.$count++.'</strong></td><td style="text-align:left;">'.$key.'</td><td style="text-align:right;">'.FormatEuroCurrency($val).'</td></tr>';
	if($count>10) break;
}
?>
</table>
</div>
</div>
<script type="text/javascript">
/* <![CDATA[ */
var dashajob;
function updatedashboardcb(){
	if(dashajob.readyState==4){
		var dbarray=dashajob.responseText.split("&"),newrow,newcell;
		document.getElementById('neworders').innerHTML=dbarray[0];
		if(dbarray[0]>0&&document.getElementById('newordersdiv').className.indexOf('new_alert')<0)document.getElementById('newordersdiv').className+=' new_alert';
		document.getElementById('newratings').innerHTML=dbarray[1];
		if(dbarray[1]>0&&document.getElementById('newratingsdiv').className.indexOf('new_alert')<0)document.getElementById('newratingsdiv').className+=' new_alert';
		document.getElementById('newaccounts').innerHTML=dbarray[2];
		if(dbarray[2]>0&&document.getElementById('newaccountsdiv').className.indexOf('new_alert')<0)document.getElementById('newaccountsdiv').className+=' new_alert';
		document.getElementById('newmaillist').innerHTML=dbarray[3];
		if(dbarray[3]>0&&document.getElementById('newmaillistdiv').className.indexOf('new_alert')<0)document.getElementById('newmaillistdiv').className+=' new_alert';
		document.getElementById('newaffiliate').innerHTML=dbarray[4];
		if(dbarray[4]>0&&document.getElementById('newaffiliatediv').className.indexOf('new_alert')<0)document.getElementById('newaffiliatediv').className+=' new_alert';
		document.getElementById('newgiftcert').innerHTML=dbarray[5];
		if(dbarray[5]>0&&document.getElementById('newgiftcertdiv').className.indexOf('new_alert')<0)document.getElementById('newgiftcertdiv').className+=' new_alert';
<?php	if(@$notifybackinstock){ ?>
		document.getElementById('newstocknotify').innerHTML=dbarray[6];
		if(dbarray[6]>0&&document.getElementById('newstocknotifydiv').className.indexOf('new_alert')<0)document.getElementById('newstocknotifydiv').className+=' new_alert';
<?php	}
		if(@$_SESSION['loginid']==0){ ?>
		document.getElementById('newlogevents').innerHTML=dbarray[7];
		if(dbarray[7]>0&&document.getElementById('newlogeventsdiv').className.indexOf('new_alert')<0)document.getElementById('newlogeventsdiv').className+=' new_alert';
<?php	} ?>
		var ordtable=document.getElementById('latestorders');
		for(var dbind=0;dbind<dbarray.length-8;dbind++){
			var orddetails=dbarray[8+dbind].split('|');
			if(ordtable.rows.length<dbind+2){
				newrow=ordtable.insertRow(-1);
				for(var ncind=0;ncind<4;ncind++)newrow.insertCell(-1);
			}else
				newrow=ordtable.rows[dbind+1];
			var ordid=orddetails[0];
			newrow.setAttribute('onclick', 'document.location="adminorders.php?id='+ordid+'"');
			newrow.cells[0].innerHTML='<a href="adminorders.php?id='+ordid+'">'+decodeURIComponent(orddetails[1])+'</a>';
			newrow.cells[1].innerHTML=decodeURIComponent(orddetails[3]);
			newrow.cells[2].innerHTML=decodeURIComponent(orddetails[4]);
			newrow.cells[3].innerHTML=decodeURIComponent(orddetails[5]);
		}
		setTimeout(updatedashboard,90000);
	}
}
function updatedashboard(){
	dashajob=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
	dashajob.onreadystatechange=updatedashboardcb;
	dashajob.open("GET", "ajaxservice.php?action=dashboard", true);
	dashajob.send(null);
}
updatedashboard();
/* ]]> */
</script>
<?php	} ?>
<div class="row">
<h3 class="round_top"><?php print $yyStoAdm?></h3>
<table width="100%" class="admin-table-b">
  <thead>
	<tr>
	  <th scope="col"><?php print $yyAdmLnk?></th>
	  <th scope="col"><?php print $yyDesc?></th>
	  <th scope="col"><?php print $yyHlpFil?></th>
	</tr>
  </thead>
  <tr>
    <td><a href="adminmain.php"><?php print $yyEdAdm?></a></td>
    <td><?php print $yyDBGlob?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#admin" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
 <tr>
    <td><a href="adminlogin.php"><?php print $yyCngPw?></a></td>
    <td><?php print $yyDBLogA?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#uname" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
     <tr>
    <td><a href="adminpayprov.php"><?php print $yyEdPPro?></a></td>
    <td><?php print $yyDBConP?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#payprov" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
    <tr>
    <td><a href="adminordstatus.php"><?php print $yyEdOSta?></a></td>
    <td><?php print $yyDBConO?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#ordstat" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
      <tr>
    <td><a href="adminemailmsgs.php"><?php print $yyEmlAdm?></a></td>
    <td><?php print $yyDBConE?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#emailadmin" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
  <tr>
    <td><a href="admincontent.php"><?php print $yyContReg?></a></td>
    <td><?php print $yyContExp?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#contreg" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
  <tr>
    <td><a href="adminipblock.php"><?php print $yyIPBlock?></a></td>
    <td><?php print $yyDBBkIP?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#ipblock" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
</table>
	
<h3 class="round_top double_top"><?php print $yyPrdAdm?></h3>
<table width="100%" class="admin-table-b">
  <thead>
	<tr>
	  <th scope="col"><?php print $yyAdmLnk?></th>
	  <th scope="col"><?php print $yyDesc?></th>
	  <th scope="col"><?php print $yyHlpFil?></th>
	</tr>
  </thead>
  <tr>
    <td><a href="adminprods.php"><?php print $yyEdPrd?></a></td>
    <td><?php print $yyDBMaPI?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#prods" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
    <tr>
    <td><a href="adminprodopts.php"><?php print $yyEdOpt?></a></td>
    <td><?php print $yyDBPrAt?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#prodopt" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
  <tr>
    <td><a href="admincats.php"><?php print $yyEdCat?></a></td>
    <td><?php print $yyDBCats?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#cats" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
  <tr>
    <td><a href="admindiscounts.php"><?php print $yyDisCou?></a></td>
    <td><?php print $yyDBSOFS?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#discounts" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
  <tr>
    <td><a href="adminpricebreak.php"><?php print $yyEdPrBk?></a></td>
    <td><?php print $yyDBBuPr?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#pricebreak" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
  <tr>
    <td><a href="admingiftcert.php"><?php print $yyGCMan?></a></td>
    <td><?php print $yyDBGifC?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#giftcert" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
    <tr>
    <td><a href="adminmanufacturer.php"><?php print $yyEdManu?></a></td>
    <td><?php print $yyDBManD?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#manuf" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
  <tr>
    <td><a href="adminsearchcriteria.php"><?php print $yyEdSeCr?></a></td>
    <td><?php print $yyCrSeCr?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#searcr" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
  <tr>
    <td><a href="admincsv.php"><?php print $yyCSVUpl?></a></td>
    <td><?php print $yyDBBUpI?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#csv" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
</table>	
	
<h3 class="round_top double_top"><?php print $yyShpAdm?></h3>
<table width="100%" class="admin-table-b">
  <thead>
	<tr>
	  <th scope="col"><?php print $yyAdmLnk?></th>
	  <th scope="col"><?php print $yyDesc?></th>
	  <th scope="col"><?php print $yyHlpFil?></th>
	</tr>
  </thead>
  <tr>
    <td><a href="adminstate.php"><?php print $yyEdSta?></a></td>
    <td><?php print $yyDBStat?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#state" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
  <tr>
    <td><a href="admincountry.php"><?php print $yyEdCnt?></a></td>
    <td><?php print $yyDBCoun?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#country" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
  <tr>
    <td><a href="adminzones.php"><?php print $yyEdPzon?></a></td>
    <td><?php print $yyDBSZon?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#pzone" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
  <tr>
    <td><a href="adminuspsmeths.php"><?php print $yyShmReg?></a></td>
    <td><?php print $yyDBMSHO?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#shipmeth" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
  <tr>
     <td><a href="admindropship.php"><?php print $yyEdDrSp?></a></td>
    <td><?php print $yyDBDSDe?></td>
    <td><a href="<?php print ecthelpbaseurl?>help.asp#droshp" target="ttshelp" class="online_help" title="<?php print $yyOnlHlp?>">?</a></td>
  </tr>
</table>

<h3 class="round_top double_top">Reporting Tool</h3>
<table width="100%" class="admin-table-b">
  <tr>
	  <th scope="col"><?php print $yyAdmLnk?></th>
	  <th scope="col"><?php print $yyDesc?></th>
	</tr>
<tr>
<td>
<a href="/reportgenerationtool1a/index.php">Reporting Tool</a>
</td>
<td>
Reports for Real Lighting
</td>



</tr>
</table>

<h3 class="round_top double_top">Debug Info</h3>
<table width="100%" class="admin-table-b">
  <tr>
    <td>MySQL Version:</td>
    <td><?php
$sSQL = 'SELECT version() AS theversion';
$result=ect_query($sSQL) or ect_error();
$rs=ect_fetch_assoc($result);
print $rs['theversion'];
ect_free_result($result);
	?></td>
  </tr>
  <tr>
    <td>PHP Version:</td>
    <td><?php print phpversion()?></td>
  </tr>
</table>
<?php
	$sSQL = "SELECT modkey,modtitle,modauthor,modauthorlink,modversion,modectversion,modlink,moddate FROM installedmods ORDER BY moddate";
	$result=ect_query($sSQL) or ect_error();
	if(ect_num_rows($result) > 0){
		print '<table width="98%" align="center">';
		print '<tr><td align="center" colspan="2">&nbsp;<br /><strong>---------------| Installed 3rd Party MODs |---------------<br />&nbsp;</strong></td></tr>';
		print '<tr><td align="center" colspan="2"><table border="0" cellspacing="0" cellpadding="0" width="100%">';
		print '<tr><td align="left"><strong>Title</strong></td><td align="left"><strong>Author</strong></td><td align="left"><strong>MOD Version</strong></td><td align="left"><strong>ECT Version</strong></td><td align="left"><strong>Admin Link</strong></td><td align="left"><strong>Install Date</strong></td></tr>';
		while($rs=ect_fetch_assoc($result)){
			$modauthorlink=trim($rs['modauthorlink']);
			print '<tr><td align="left">' . $rs['modtitle'] . '</td>';
			print '<td align="left"><a href="' . (substr($modauthorlink,7)!='http://'&&substr($modauthorlink,8)!='https://'?'http://':'') . '" target="_blank">' . $rs['modauthor'] . '</a></td>';
			print '<td align="left">' . $rs['modversion'] . '</td>';
			print '<td align="left">' . $rs['modectversion'] . '</td>';
			print '<td align="left"><strong>' . (trim($rs['modlink'])!='' ? '<a href="' . $rs['modlink'] . '">Admin Page</a>' : '&nbsp;') . '</strong></td>';
			print '<td align="left">' . date($admindatestr, strtotime($rs['moddate'])) . '</td>';
		}
		print '</table><br />&nbsp;</td></tr></table>';
	}
	ect_free_result($result);
	}
} 
if(@$dumpadminstats) print "Section 20 : " . (microtime_float()-$time_start)."<br />";
?>
</div>
