<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
global $incfunctionsdefined,$alreadygotadmin,$storeurl,$thesessionid;
$thesessionid=getsessionid();
if(getpost('sessionid')!='') $thesessionid=getpost('sessionid');
$thesessionid=str_replace("'",'',$thesessionid);
function join2pathsrv($stourl,$securl){
	if(strpos($securl,'://')!==FALSE) return($securl);
	if($securl[0]!='/') return($stourl.$securl);
	if($securl[0]=='/'&&$stourl!=''){
		$urlparts=parse_url($stourl);
		$pos=strrpos($stourl,$urlparts['path']);
		if($pos!==FALSE) $stourl=substr_replace($stourl,'',$pos,strlen($urlparts['path']));
	}
	return($stourl.$securl);
}
if(@$incfunctionsdefined==TRUE){
	$alreadygotadmin=getadminsettings();
}else{
	$sSQL='SELECT countryLCID,countryCurrency,adminStoreURL FROM admin INNER JOIN countries ON admin.adminCountry=countries.countryID WHERE adminID=1';
	$result=ect_query($sSQL) or ect_error();
	$rs=ect_fetch_assoc($result);
	$adminLocale=$rs['countryLCID'];
	$storeurl=$rs['adminStoreURL'];
	if((substr(strtolower($storeurl),0,7) != 'http://') && (substr(strtolower($storeurl),0,8) != 'https://'))
		$storeurl='http://' . $storeurl;
	if(substr($storeurl,-1) != '/') $storeurl.='/';
	ect_free_result($result);
}
if(getpost('mode')!='checkout'){
	$sSQL="SELECT rvProdName,rvProdURL,sectionName FROM recentlyviewed INNER JOIN sections ON recentlyviewed.rvProdSection=sections.sectionID WHERE rvProdID<>'".escape_string(@$prodid)."' AND " . (@$_SESSION['clientID']!='' ? 'rvCustomerID=' . escape_string(@$_SESSION['clientID']) : "(rvCustomerID=0 AND rvSessionID='".$thesessionid."')").' ORDER BY rvDate DESC';
	$result=ect_query($sSQL) or ect_error();
	if(ect_num_rows($result)>0){ ?>
      <table class="mincart" width="130" bgcolor="#FFFFFF">
        <tr> 
          <td class="mincart" bgcolor="#F0F0F0" align="center"><img src="<?php print $path_parts['dirname']?>/images/recentview.png" style="vertical-align:text-top;" width="16" height="15" alt="<?php print $GLOBALS['xxRecVie']?>" />
            &nbsp;<strong><a class="ectlink mincart" href="<?php print $storeurl?>cart.php"><?php print $GLOBALS['xxRecVie']?></a></strong></td>
        </tr>
<?php	while($rs=ect_fetch_assoc($result)){ ?>
         <tr><td class="mincart" bgcolor="#F0F0F0" align="center">
		<span style="font-family:Verdana">&raquo;</span> <?php print $rs['sectionName']?><br />
		<a class="ectlink mincart" href="<?php print join2pathsrv($storeurl,$rs['rvProdURL'])?>"><?php print $rs['rvProdName']?></a></td></tr>
<?php	} ?>
      </table>
<?php
	}
	ect_free_result($result);
}
?>