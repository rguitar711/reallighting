<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
$success=TRUE;
$alldata="";
$alreadygotadmin=getadminsettings();
$errmsg='';
$resultcounter=0;
$dorefresh=FALSE;
if(@$htmlemails==TRUE) $emlNl='<br />'; else $emlNl="\n";
function dodeleteoption($oid){
	global $success,$yyPOUse,$errmsg;
	$index=0;
	$sSQL='SELECT poID,poProdID FROM prodoptions INNER JOIN products ON prodoptions.poProdID=products.pID WHERE poOptionGroup=' . $oid . ' LIMIT 0,100';
	$result=ect_query($sSQL) or ect_error();
	if(ect_num_rows($result)>0){
		$success=FALSE;
		$errmsg.=$yyPOUse . '<br /><br />';
		while($rs=ect_fetch_assoc($result)){
			$errmsg.='<form method="post" action="adminprods.php" style="display:inline">' . whv("posted",1) . whv("act","modify") . '<input type="submit" name="id" value="' . $rs['poProdID'] . '"></form>';
			$index++;
			if($index>=10){ print '<br />'; $index=0; }
		}
	}
	ect_free_result($result);
	$index=0;
	$showmessage=TRUE;
	$sSQL="SELECT optGroup,optName,optDependants FROM options WHERE optDependants LIKE '%" . $oid . "%' LIMIT 0,100";
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		if(strpos(','.$rs['optDependants'].',',','.$oid.',')!==FALSE){
			if($showmessage) $errmsg.='<br /><br />This option is a dependent option of the following options:<br /><br />';
			$showmessage=FALSE;
			$errmsg.='<form method="post" action="adminprodopts.php" style="display:inline">' . whv('posted',1) . whv('act','modify') . whv('id',$rs['optGroup']) . '<input type="submit" value="' . htmlspecials($rs['optName']) . '"></form>';
			$index++;
			if($index>=10){ print '<br />'; $index=0; }
			$success=FALSE;
		}
	}
	ect_free_result($result);
	if($success){
		ect_query("DELETE FROM options WHERE optGroup=" . $oid) or ect_error();
		ect_query("DELETE FROM optiongroup WHERE optGrpID=" . $oid) or ect_error();
		ect_query("DELETE FROM prodoptions WHERE poOptionGroup=" . $oid) or ect_error();
	}
	return($success);
}
function checknotifystock($theoid){
	global $notifybackinstock,$storeurl,$htmlemails,$emailAddr,$emlNl,$usepnamefordetaillinks,$detlinkspacechar,$seodetailurls;
	if($GLOBALS['useStockManagement'] && $notifybackinstock){
		$sSQL='SELECT '.getlangid('notifystocksubject',4096).','.getlangid('notifystockemail',4096).' FROM emailmessages WHERE emailID=1';
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$oemailsubject=trim($rs[getlangid('notifystocksubject',4096)]);
			$oemailmessage=$rs[getlangid('notifystockemail',4096)];
		}
		ect_free_result($result);
		
		$idlist='';
		$sSQL="SELECT DISTINCT nsProdID FROM notifyinstock INNER JOIN prodoptions ON notifyinstock.nsProdID=prodoptions.poProdID INNER JOIN options ON prodoptions.poOptionGroup=options.optGroup WHERE nsOptID=-1 AND optID=".$theoid;
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){
			$gotall=TRUE;
			$sSQL="SELECT poOptionGroup FROM prodoptions INNER JOIN optiongroup ON prodoptions.poOptionGroup=optiongroup.optGrpID WHERE poProdID='".escape_string($rs['nsProdID'])."'";
			$result2=ect_query($sSQL) or ect_error();
			while($rs2=ect_fetch_assoc($result2)){
				$sSQL="SELECT optID FROM options WHERE optStock>0 AND optGroup=".$rs2['poOptionGroup'];
				$result3=ect_query($sSQL) or ect_error();
				if(ect_num_rows($result3)==0) $gotall=FALSE;
				ect_free_result($result3);
			}
			ect_free_result($result2);
			if($gotall) $idlist.="'".escape_string($rs['nsProdID'])."',";
		}
		ect_free_result($result);
		if($idlist!='') $idlist=substr($idlist,0,-1);
		
		$pStockByOpts=0;
		$sSQL="SELECT pId,pName,pStockByOpts,pStaticPage,pStaticURL,pInStock,nsEmail FROM products INNER JOIN notifyinstock ON products.pID=notifyinstock.nsProdID WHERE nsOptId=".$theoid;
		if($idlist!='') $sSQL.=' OR (nsOptID=-1 AND nsProdID IN ('.$idlist.'))';
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){
			$nspid=$rs['pId'];
			$pName=trim($rs['pName']);
			$pStockByOpts=$rs['pStockByOpts'];
			$pStaticPage=$rs['pStaticPage'];
			$pStaticURL=$rs['pStaticURL'];
			$pInStock=$rs['pInStock'];
			$theemail=$rs['nsEmail'];
			$thelink=$storeurl . getdetailsurl($nspid,$pStaticPage,$pName,$pStaticURL,'','');
			if(@$htmlemails==TRUE && $thelink!='') $thelink='<a href="' . $thelink . '">' . $thelink . '</a>';
			$emailsubject=str_replace('%pid%',trim($nspid),$oemailsubject);
			$emailsubject=str_replace('%pname%',$pName,$emailsubject);
			$emailmessage=str_replace('%pid%',trim($nspid),$oemailmessage);
			$emailmessage=str_replace('%pname%',$pName,$emailmessage);
			$emailmessage=str_replace('%link%',$thelink,$emailmessage);
			$emailmessage=str_replace('%storeurl%',$storeurl,$emailmessage);
			$emailmessage=str_replace('<br />',$emlNl,$emailmessage);
			$emailmessage=str_replace('%nl%',$emlNl,$emailmessage);
			dosendemail($rs['nsEmail'],$emailAddr,'',$emailsubject,$emailmessage);
		}
		ect_free_result($result);
		$sSQL='DELETE FROM notifyinstock WHERE nsOptId='.$theoid;
		if($idlist!='') $sSQL.=' OR (nsOptID=-1 AND nsProdID IN ('.$idlist.'))';
		ect_query($sSQL) or ect_error();
	}
}
if(getpost('posted')=="1"){
	if(getpost('act')=="delete"){
		if(dodeleteoption(getpost('id')))
			$dorefresh=TRUE;
		else
			$errmsg=$yyPOErr . "<br />" . $errmsg;
	}elseif(getpost('act')=='quickupdate'){
		foreach(@$_POST as $objItem => $objValue){
			if(substr($objItem, 0, 4)=='pra_'){
				$theid=str_replace('ect_dot_xzq','.',substr($objItem, 4));
				$theval=trim(unstripslashes($objValue));
				$pract=getpost('pract');
				$sSQL='';
				if($pract=='del'){
					if($theval=='del') dodeleteoption($theid);
					$sSQL='';
				}elseif($pract=='own'){
					$sSQL="UPDATE optiongroup SET optGrpWorkingName='" . escape_string($theval) . "'";
				}elseif($pract=='oty'){
					ect_query("UPDATE optiongroup SET optType='" . escape_string($theval) . "' WHERE optType>0 AND optGrpID='".escape_string($theid)."'") or ect_error();
					ect_query("UPDATE optiongroup SET optType='-" . escape_string($theval) . "' WHERE optType<0 AND optGrpID='".escape_string($theid)."'") or ect_error();
				}elseif($pract=='opn'){
					$sSQL="UPDATE optiongroup SET optGrpName='" . escape_string($theval) . "'";
				}elseif($pract=='opn2'){
					$sSQL="UPDATE optiongroup SET optGrpName2='" . escape_string($theval) . "'";
				}elseif($pract=='opn3'){
					$sSQL="UPDATE optiongroup SET optGrpName3='" . escape_string($theval) . "'";
				}
				if($sSQL!=''){
					$sSQL.=" WHERE optGrpID='".escape_string($theid)."'";
					ect_query($sSQL) or ect_error();
				}
			}
		}
		if($success) $dorefresh=TRUE; else $errmsg=$yyPOErr . '<br />' . $errmsg;
	}elseif(getpost('act')=='domodify' || getpost('act')=='doaddnew'){
		$sSQL="";
		$bOption=FALSE;
		$maxoptnumber=getpost('maxoptnumber');
		$optFlags=0;
		if(getpost('pricepercent')=='1') $optFlags=1;
		if(getpost('weightpercent')=='1') $optFlags+=2;
		if(getpost('singleline')=='1') $optFlags+=4;
		if(getpost('optdefault')!='') $optDefault=(int)getpost('optdefault'); else $optDefault=-1;
		for($rowcounter=0; $rowcounter < $maxoptnumber; $rowcounter++){
			if(getpost('opt' . $rowcounter)!='') $bOption=TRUE;
			$aOption[$rowcounter][0]=escape_string(getpost('opt' . $rowcounter));
			for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 32)==32)
					$aOption[$rowcounter][8+$index]=escape_string(getpost('opl' . $index . 'x' . $rowcounter));
			}
			if(is_numeric(getpost('pri' . $rowcounter)))
				$aOption[$rowcounter][1]=getpost('pri' . $rowcounter);
			else
				$aOption[$rowcounter][1]=0;
			if(is_numeric(getpost('wsp' . $rowcounter)))
				$aOption[$rowcounter][4]=getpost('wsp' . $rowcounter);
			else
				$aOption[$rowcounter][4]=0;
			if(is_numeric(getpost('wei' . $rowcounter)))
				$aOption[$rowcounter][2]=getpost('wei' . $rowcounter);
			else
				$aOption[$rowcounter][2]=0;
			if(is_numeric(getpost('optStock' . $rowcounter)))
				$aOption[$rowcounter][3]=getpost('optStock' . $rowcounter);
			else
				$aOption[$rowcounter][3]=0;
			$aOption[$rowcounter][5]=escape_string(getpost('regexp' . $rowcounter));
			$aOption[$rowcounter][6]=getpost('orig' . $rowcounter);
			$aOption[$rowcounter][7]=escape_string(getpost('altimg' . $rowcounter));
			$aOption[$rowcounter][8]=escape_string(getpost('altlimg' . $rowcounter));
			$aOption[$rowcounter][9]='';
			$depotpnum=1;
			while(getpost('depopts'.$rowcounter.'_'.$depotpnum)!=''){
				if(is_numeric(getpost('depopts'.$rowcounter.'_'.$depotpnum))) $aOption[$rowcounter][9].=getpost('depopts'.$rowcounter.'_'.$depotpnum).',';
				$depotpnum++;
			}
			if($aOption[$rowcounter][9]!='') $aOption[$rowcounter][9]=substr($aOption[$rowcounter][9],0,-1);
		}
		if((getpost('secname')=='' || ! $bOption) && getpost('optType')!='3'){
			$success=FALSE;
			$errmsg=$yyPOErr . '<br />';
			$errmsg.=$yyPOOne;
		}else{
			if(getpost('optType')=='3'){ // Text option
				$fieldDims=getpost('pri0') . '.';
				if((int)getpost('fieldheight') < 10) $fieldDims.='0';
				$fieldDims.=getpost('fieldheight');
				$optTxtCharge=getpost('optTxtCharge');
				if(! is_numeric($optTxtCharge)) $optTxtCharge=0;
				if(getpost('act')=='doaddnew'){
					$sSQL="INSERT INTO optiongroup (optGrpName,";
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16)
							$sSQL.="optGrpName" . $index . ",";
					}
					$sSQL.="optType,optTxtMaxLen,optTxtCharge,optMultiply,optAcceptChars,optGrpWorkingName,optTooltip,optFlags) VALUES (";
					$sSQL.="'" . escape_string(getpost('secname')) . "',";
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16)
							$sSQL.="'" . escape_string(getpost('secname' . $index)) . "',";
					}
					if(getpost('forceselec')=="ON") $sSQL.="'3',"; else $sSQL.="'-3',";
					$sSQL.=getpost('optTxtMaxLen').','.(getpost('iscostperentry')=='1'? 0-$optTxtCharge : $optTxtCharge);
					$sSQL.=',' . (getpost('optMultiply')=="ON" ? 1 : 0) . ",'" . escape_string(getpost('optAcceptChars')) . "'";
					$sSQL.=",'" . escape_string(getpost('workingname')==""?getpost('secname'):getpost('workingname'));
					$sSQL.="','" . escape_string(getpost('opttooltip'));
					$sSQL.="'," . $optFlags . ")";
					ect_query($sSQL) or ect_error();
					$iID =ect_insert_id();
					$sSQL='INSERT INTO options (optGroup,optName,optPlaceholder,optPriceDiff';
					for($index=2; $index<=$adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16){
							$sSQL.=',optName' . $index;
							$sSQL.=',optPlaceholder' . $index;
						}
					}
					$sSQL.=",optWeightDiff) VALUES (" . $iID . ",'" . escape_string(getpost('opt0')) . "','" . escape_string(getpost('oph0')) . "'," . $fieldDims;
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16){
							$sSQL.=",'" . escape_string(getpost('opl' . $index . 'x0')) . "'";
							$sSQL.=",'" . escape_string(getpost('oph' . $index . 'x0')) . "'";
						}
					}
					$sSQL.=",0)";
					ect_query($sSQL) or ect_error();
				}else{
					$iID=getpost('id');
					$sSQL="UPDATE optiongroup SET optGrpName='" . escape_string(getpost('secname')) . "'";
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16)
							$sSQL.=",optGrpName" . $index . "='" . escape_string(getpost('secname' . $index)) . "'";
					}
					$sSQL.=",optType=" . (getpost('forceselec')=="ON"?"'3'":"'-3'");
					$sSQL.=",optTxtMaxLen=" . getpost('optTxtMaxLen');
					$sSQL.=",optTxtCharge=" . (getpost('iscostperentry')=='1' ? 0-$optTxtCharge : $optTxtCharge);
					$sSQL.=",optMultiply=" . (getpost('optMultiply')=='ON' ? 1 : 0);
					$sSQL.=",optAcceptChars='" . escape_string(getpost('optAcceptChars')) . "'";
					$sSQL.=",optGrpWorkingName='" . (getpost('workingname')==""?escape_string(getpost('secname')):escape_string(getpost('workingname'))) . "'";
					$sSQL.=",optFlags=" . $optFlags;
					$sSQL.=",optTooltip='" . escape_string(getpost('opttooltip')) . "'";
					$sSQL.=" WHERE optGrpID=" . $iID;
					ect_query($sSQL) or ect_error();
					$sSQL="UPDATE options SET optName='" . escape_string(getpost('opt0')) . "',optPlaceholder='" . escape_string(getpost('oph0')) . "',optPriceDiff=" . $fieldDims;
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16)
							$sSQL.=',optName' . $index . "='" . escape_string(getpost('opl' . $index . 'x0')) . "',optPlaceholder" . $index . "='" . escape_string(getpost('oph' . $index)) . "'";
					}
					$sSQL.=' WHERE optGroup=' . $iID;
					ect_query($sSQL) or ect_error();
				}
			}else{ // Non-text Option
				if(getpost('act')=='doaddnew'){
					$sSQL='INSERT INTO optiongroup (optGrpName';
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16)
							$sSQL.=',optGrpName' . $index;
					}
					$sSQL.=',optType,optGrpWorkingName,optFlags,optGrpSelect,optTooltip) VALUES (';
					$sSQL.="'" . escape_string(getpost('secname')) . "',";
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16)
							$sSQL.="'" . escape_string(getpost('secname' . $index)) . "',";
					}
					$sSQL.="'".(getpost('forceselec')=='ON'?getpost('optType'):0-(int)getpost('optType'))."',";
					$sSQL.="'" . escape_string(getpost('workingname')==''?getpost('secname'):getpost('workingname'));
					$sSQL.="'," . $optFlags . ',' . (getpost('optgrpselect')=='1' ? 1 : 0) . ",'" . escape_string(getpost('opttooltip')) . "')";
					ect_query($sSQL) or ect_error();
					$iID=ect_insert_id();
				}else{
					$iID=getpost('id');
					$sSQL="UPDATE optiongroup SET optGrpName='" . escape_string(getpost('secname')) . "'";
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16)
							$sSQL.=",optGrpName" . $index . "='" . escape_string(getpost('secname' . $index)) . "'";
					}
					$sSQL.=",optType='".(getpost('forceselec')=="ON"?getpost('optType'):0-(int)getpost('optType'))."'";
					$sSQL.=",optGrpWorkingName='" . escape_string(getpost('workingname')==""?getpost('secname'):getpost('workingname')) . "',";
					$sSQL.="optFlags=" . $optFlags;
					$sSQL.=",optGrpSelect=" . (getpost('optgrpselect')=='1' ? 1 : 0);
					$sSQL.=",optTooltip='" . escape_string(getpost('opttooltip')) . "'";
					$sSQL.=" WHERE optGrpID=" . $iID;
					ect_query($sSQL) or ect_error();
				}
				for($rowcounter=0; $rowcounter < $maxoptnumber; $rowcounter++){
					if(trim($aOption[$rowcounter][0])!=''){
						if($aOption[$rowcounter][6]!=''){
							$sSQL="UPDATE options SET optName='" . $aOption[$rowcounter][0] . "',optRegExp='" . $aOption[$rowcounter][5] . "',optAltImage='" . $aOption[$rowcounter][7] . "',optAltLargeImage='" . $aOption[$rowcounter][8] . "',optPriceDiff=" . $aOption[$rowcounter][1] . ",optWeightDiff=" . $aOption[$rowcounter][2] . ",optStock=" . $aOption[$rowcounter][3];
							if(@$wholesaleoptionpricediff==TRUE) $sSQL.=",optWholesalePriceDiff=" . $aOption[$rowcounter][4];
							for($index=2; $index <= $adminlanguages+1; $index++){
								if(($adminlangsettings & 32)==32)
									$sSQL.=",optName" . $index . "='" . $aOption[$rowcounter][8+$index] . "'";
							}
							$sSQL.=',optDefault=' . ($rowcounter==$optDefault ? '1' : '0');
							$sSQL.=",optDependants='" . $aOption[$rowcounter][9] . "'";
							$sSQL.=" WHERE optID=" . $aOption[$rowcounter][6];
							ect_query($sSQL) or ect_error();
							if($aOption[$rowcounter][3])
								checknotifystock($aOption[$rowcounter][6]);
						}else{
							$sSQL="INSERT INTO options (optGroup,optName,optRegExp,optAltImage,optAltLargeImage,optPriceDiff,optWeightDiff,optStock,optDefault,optDependants";
							if(@$wholesaleoptionpricediff==TRUE) $sSQL.=",optWholesalePriceDiff";
							for($index=2; $index <= $adminlanguages+1; $index++){
								if(($adminlangsettings & 32)==32) $sSQL.=",optName" . $index;
							}
							$sSQL.=") VALUES (" . $iID . ",'" . $aOption[$rowcounter][0] . "','" . $aOption[$rowcounter][5] . "','" . $aOption[$rowcounter][7] . "','" . $aOption[$rowcounter][8] . "'," . $aOption[$rowcounter][1] . "," . $aOption[$rowcounter][2] . "," . $aOption[$rowcounter][3] . ',' . ($rowcounter==$optDefault ? '1' : '0') . ",'" . $aOption[$rowcounter][9] . "'";
							if(@$wholesaleoptionpricediff==TRUE) $sSQL.="," . $aOption[$rowcounter][4];
							for($index=2; $index <= $adminlanguages+1; $index++){
								if(($adminlangsettings & 32)==32) $sSQL.=",'" . $aOption[$rowcounter][8+$index] ."'";
							}
							$sSQL.=")";
							ect_query($sSQL) or ect_error();
						}
					}else{
						if($aOption[$rowcounter][6]!=''){
							$sSQL="DELETE FROM options WHERE optID='" . $aOption[$rowcounter][6] . "'";
							ect_query($sSQL) or ect_error();
						}
					}
				}
			}
		}
		if($success)
			$dorefresh=TRUE;
	}
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=adminprodopts.php';
	print '?disp=' . getpost('disp') . '&stext=' . urlencode(getpost('stext')) . '&stype=' . getpost('stype') . '&pg=1'; // . getpost('pg');
	print '">';
}
?>
<script type="text/javascript">
/* <![CDATA[ */
var oAR=new Array();
<?php
	$sSQL='SELECT optGrpID,optGrpWorkingName,optType FROM optiongroup ORDER BY optGrpWorkingName';
	$result=ect_query($sSQL) or ect_error();
	$rowcounter=0;
	while($rs=ect_fetch_assoc($result)){
		print "oAR[".$rowcounter."]=[".$rs['optGrpID'].",'".jsescape($rs['optGrpWorkingName'])."',".$rs['optType']."];\r\n";
		$rowcounter++;
	}
?>
function addoptionselect(oSelect){
	oSelect.name=oSelect.id;
	var spanid=oSelect.id.split('_')[0];
	var optnum=parseInt(oSelect.id.split('_')[1]);	

	var select=document.createElement("select");
	select.setAttribute("id",spanid+'_'+(optnum+1));
	select.style.width="140px";
	select.onchange=function(){addoptionselect(this)};
	select.onmouseover=function(){populateoptionsselect(this)};
	var option;
	option=document.createElement("option");
	option.setAttribute("value","x");
	option.innerHTML="<?php print jscheck($yySelect)?>";
	select.appendChild(option);

	document.getElementById(spanid).appendChild(select);
	oSelect.onchange='';
}
function populateoptionsselect(oSelect){
	var insbefore=oSelect.selectedIndex!=0;
	var existingitem=oSelect.options[oSelect.selectedIndex];
	var osarray;
	osarray=oAR;
	for(var i=0;i<osarray.length;i++){
		if(existingitem.value==osarray[i][0]){
			insbefore=false;
		}else{
			var y=document.createElement('option');
			y.innerHTML=osarray[i][1];
			y.value=osarray[i][0];
			if(insbefore){
				try{oSelect.add(y,existingitem);} // FF etc
				catch(ex){oSelect.add(y,oSelect.selectedIndex);} // IE
			}else{
				try{oSelect.add(y,null);} // FF etc
				catch(ex){oSelect.add(y);} // IE
			}
		}
	}
	oSelect.onmouseover='';
}
function formvalidator(theForm){
	var maxrow=document.getElementById("maxoptnumber").value;
	if(theForm.secname.value==""){
		alert("<?php print jscheck($yyPlsEntr . ' "' . $yyPOName)?>\".");
		theForm.secname.focus();
		return (false);
	}
	for(index=0;index<maxrow;index++){
		document.getElementById("altimg" + index).disabled=(document.getElementById("altimg" + index).name=='xxx'?true:false);
		document.getElementById("altlimg" + index).disabled=(document.getElementById("altlimg" + index).name=='xxx'?true:false);
<?php	if($useStockManagement) print "document.getElementById('optStock' + index).disabled=(document.getElementById('optStock' + index).name=='xxx'?true:false);";
		if(@$wholesaleoptionpricediff==TRUE) print "document.getElementById('wsp' + index).disabled=(document.getElementById('wsp' + index).name=='xxx'?true:false);"; ?>
		document.getElementById("pri" + index).disabled=(document.getElementById("pri" + index).name=='xxx'?true:false);
		document.getElementById("regexp" + index).disabled=(document.getElementById("regexp" + index).name=='xxx'?true:false);
		document.getElementById("wei" + index).disabled=(document.getElementById("wei" + index).name=='xxx'?true:false);
	}
	return (true);
}
function changeunits(){
	var nopercentchar="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	var maxrow=document.getElementById("maxoptnumber").value;
	for(index=0;index<maxrow;index++){
		wel=document.getElementById("wunitspan" + index);
		pel=document.getElementById("punitspan" + index);
		if(document.forms.mainform.weightpercent.checked){
			wel.innerHTML='&nbsp;%&nbsp;';
		}else{
			wel.innerHTML=nopercentchar;
		}
		if(document.forms.mainform.pricepercent.checked){
			pel.innerHTML='&nbsp;%&nbsp;';
		}else{
			pel.innerHTML='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}
	}
}
function doswitcher(){
	var maxrow=document.getElementById("maxoptnumber").value;
	var switcher=document.getElementById("switcher");
	var hideraquo;
	if(switcher.selectedIndex==0){
		doswon='block';
		doswoff='none';
		depopts='none';
		hideraquo=false;
	}else if(switcher.options[1].disabled){
		switcher.selectedIndex=0;
		return;
	}else if(switcher.selectedIndex==2){ // Dependent Options
		doswon='none';
		doswoff='none';
		depopts='block';
		hideraquo=true;
	}else{
		doswon='none';
		doswoff='block';
		depopts='none';
		hideraquo=false;
	}
	for(index=-1;index<maxrow;index++){
		if(index==-1)theindex='';else theindex=index;
		document.getElementById("swprdiff" + theindex).style.display=doswon;
		document.getElementById("swaltid" + theindex).style.display=doswoff;
		document.getElementById("swwtdiff" + theindex).style.display=doswon;
		document.getElementById("swaltimg" + theindex).style.display=doswoff;
		document.getElementById("swstk" + theindex).style.display=doswon;
		document.getElementById("swaltlgim" + theindex).style.display=doswoff;
		document.getElementById("depopts" + theindex).style.display=depopts;
		document.getElementById("depcell" + theindex).style.textAlign=hideraquo?"left":"center";
		if(index>=0){
			hasaltid=(document.getElementById("regexp" + theindex).value.replace(/ /,'')!='');
<?php if($useStockManagement) print "document.getElementById('optStock' + theindex).disabled=hasaltid;" ?>
		}
	}
	var raquo=document.getElementById('raquo1');
	raquo.style.visibility=hideraquo?"collapse":"";
	if(raquo=document.getElementById('raquo1a'))
		raquo.style.visibility=hideraquo?"collapse":"";
	raquo=document.getElementById('raquo2');
	raquo.style.visibility=hideraquo?"collapse":"";
	raquo=document.getElementById('raquo3');
	raquo.style.visibility=hideraquo?"collapse":"";
}
<?php	if(@$adminlanguages>1 && (($adminlangsettings & 32)==32)){ ?>
function doswitchlang(){
var langid=document.getElementById("langid");
var theid=langid[langid.selectedIndex].value;
var maxrow=document.getElementById("maxoptnumber").value;
for(index=0;index<maxrow;index++){
<?php		for($index=2; $index <= $adminlanguages+1; $index++){ ?>
document.getElementById("lang<?php print $index?>x" + index).style.display='none';
<?php		} ?>
}
for(index=0;index<maxrow;index++){
document.getElementById("lang" + theid + "x" + index).style.display='block';
}
}
<?php	} ?>
function doaddrow(){
var rownumber=document.getElementById("maxoptnumber").value;
opttable=document.getElementById('optiontable');
newrow=opttable.insertRow(opttable.rows.length);
newcell=newrow.insertCell(0);
newcell.innerHTML='<input type="radio" name="optdefault" value="'+rownumber+'" />';
newcell=newrow.insertCell(1);
newcell.innerHTML='<input type="button" id="insertopt'+rownumber+'" value="+" onclick="insertoption(this)" />';
newcell=newrow.insertCell(2);
newcell.align='center';
newcell.innerHTML='<input type="text" name="opt'+rownumber+'" id="opt'+rownumber+'" size="20" value="" />';
newcell=newrow.insertCell(3);
newcell.innerHTML='<strong>&raquo;</strong>';
<?php
	$extracells=0;
	if($adminlanguages>=1 && ($adminlangsettings & 32)==32){
		$extracells=2;
		$langtext='';
		for($index=2; $index <= $adminlanguages+1; $index++){
			$langtext.='<span id="lang'.$index.'x\'+rownumber+\'"';
			if($index>2) $langtext.=' style="display:none">'; else $langtext.='>';
			$langtext.='<input type="text" name="opl'.$index.'x\'+rownumber+\'" id="opl'.$index.'x\'+rownumber+\'" size="20" /></span>';
		} ?>
newcell=newrow.insertCell(4);
newcell.align='center';
newcell.innerHTML='<?php print $langtext?>';

newcell=newrow.insertCell(5);
newcell.innerHTML='<strong>&raquo;</strong>';
<?php
	}

$langtext='<span id="swprdiff\'+rownumber+\'">';
$langtext.='&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="pri\'+rownumber+\'" id="pri\'+rownumber+\'" size="5" />';
if(@$wholesaleoptionpricediff==TRUE){
	$langtext.=' / <input type="text" name="wsp\'+rownumber+\'" id="wsp\'+rownumber+\'" size="5" />';
}
$langtext.='<span id="punitspan\'+rownumber+\'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
$langtext.='</span><span id="swaltid\'+rownumber+\'" style="display:none"><input type="text" name="regexp\'+rownumber+\'" id="regexp\'+rownumber+\'" size="12" /></span>';
?>
newcell=newrow.insertCell(<?php print (4+$extracells)?>);
newcell.align='center';
newcell.innerHTML='<?php print $langtext?>';

newcell=newrow.insertCell(<?php print (5+$extracells)?>);
newcell.innerHTML='<strong>&raquo;</strong>';

<?php
$langtext='<span id="swwtdiff\'+rownumber+\'">';
$langtext.='&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="wei\'+rownumber+\'" id="wei\'+rownumber+\'" size="5" /><span id="wunitspan\'+rownumber+\'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
$langtext.='</span><span id="swaltimg\'+rownumber+\'" style="display:none"><input type="text" name="altimg\'+rownumber+\'" id="altimg\'+rownumber+\'" size="20" /></span>';
?>
newcell=newrow.insertCell(<?php print (6+$extracells)?>);
newcell.align='center';
newcell.innerHTML='<?php print $langtext?>';

newcell=newrow.insertCell(<?php print (7+$extracells)?>);
newcell.whiteSpace='nowrap';
newcell.innerHTML='<strong>&raquo;</strong>';

<?php
$langtext='<span id="swstk\'+rownumber+\'">';
if(@$useStockManagement)
	$langtext.='<input type="text" name="optStock\'+rownumber+\'" id="optStock\'+rownumber+\'" size="4" />';
$langtext.='</span><span id="swaltlgim\'+rownumber+\'" style="display:none"><input type="text" name="altlimg\'+rownumber+\'" id="altlimg\'+rownumber+\'" size="20" /></span>' .
	'<span id="depopts\'+rownumber+\'" style="display:none">' .
	'<select id="depopts\'+rownumber+\'_1" onmouseover="populateoptionsselect(this)" onchange="addoptionselect(this)" style="width:140px"><option value="x">'.jscheck($yySelect).'</option></select>' .
	'</span>';
?>
newcell=newrow.insertCell(<?php print (8+$extracells)?>);
newcell.align='center';
newcell.innerHTML='<?php print $langtext?>';

document.getElementById("maxoptnumber").value=parseInt(rownumber)+1;
}
function addmorerows(){
	numextrarows=document.getElementById("numextrarows").value;
	numextrarows=parseInt(numextrarows);
	if(isNaN(numextrarows))numextrarows=1;
	if(numextrarows==0)numextrarows=1;
	if(numextrarows>100)numextrarows=100;
	for(index=0;index<numextrarows;index++){
		doaddrow();
	}
	doswitcher();
<?php	if($adminlanguages>1 && ($adminlangsettings & 32)==32){ ?>
	doswitchlang();
<?php	} ?>
}
function moveitemup(tid,tindex){
	if(document.getElementById('opt' + tindex).value!=''&&document.getElementById(tid + tindex).name=='xxx')
		document.getElementById(tid + tindex).name=document.getElementById(tid + tindex).id;
	document.getElementById(tid + tindex).value=document.getElementById(tid + (tindex-1)).value;
}
function insertoption(theval){
	var maxoptnumber=parseInt(document.getElementById("maxoptnumber").value);
	var theid=theval.id;
	theid=parseInt(theid.replace(/insertopt/, ''));
	if(document.getElementById('opt' + (maxoptnumber-1)).value!=''){
		doaddrow();
		doswitcher();
		doswitchlang();
		maxoptnumber++;
	}
	for(index=maxoptnumber-1;index>theid;index--){
		document.getElementById('opt' + index).value=document.getElementById('opt' + (index-1)).value;
<?php
	if(($adminlangsettings & 32)==32){
		for($index=2; $index <= $adminlanguages+1; $index++){
			print "moveitemup('opl".$index."x',index);\r\n";
		}
	}
	if(@$wholesaleoptionpricediff==TRUE) print "moveitemup('wsp',index);\r\n";
	if($useStockManagement) print "moveitemup('optStock',index);\r\n"
?>		moveitemup('pri',index);
		moveitemup('regexp',index);
		moveitemup('wei',index);
		moveitemup('altimg',index);
		moveitemup('altlimg',index);
	}
	document.getElementById('opt' + theid).value='';
<?php
	if(($adminlangsettings & 32)==32){
		for($index=2; $index <= $adminlanguages+1; $index++){
			print "document.getElementById('opl".$index."x' + theid).value='';\r\n";
		}
	}
	if(@$wholesaleoptionpricediff==TRUE) print "document.getElementById('wsp' + theid).value='';\r\n";
?>
	document.getElementById('pri' + theid).value='';
	document.getElementById('regexp' + index).value='';
	document.getElementById('wei' + index).value='';
	document.getElementById('altimg' + index).value='';
<?php	if($useStockManagement) print "document.getElementById('optStock' + index).value='';" ?>
	document.getElementById('altlimg' + index).value='';
}
function checkmultipurchase(opttype){
	var theopttype=opttype[opttype.selectedIndex];
	var maxrow=document.getElementById("maxoptnumber").value;
	var switcher=document.getElementById('switcher');
	document.getElementById('plsselspan').innerHTML=(theopttype.value==4?'<?php print str_replace(' ','&nbsp;',$yyDtPgOn)?>':'<?php print str_replace(' ','&nbsp;',$yyPlsSLi)?>');
	if(switcher.selectedIndex==2){
		switcher.selectedIndex=0;
		doswitcher();
	}
	switcher.options[2].disabled=(theopttype.value==4);
}
function switchtextinput(numrows){
	if(numrows>5) numrows=5;
	document.getElementById("opt0").rows=numrows;
	document.getElementById("opt0").style.whiteSpace=(numrows==1?"nowrap":"");
	document.getElementById("oph0").rows=numrows;
	document.getElementById("oph0").style.whiteSpace=(numrows==1?"nowrap":"");
<?php
	for($index=2; $index <= $adminlanguages+1; $index++){
		if(($adminlangsettings & 16)==16){
			print 'document.getElementById("opl'.$index.'x0").rows=numrows;' . "\r\n";
			print 'document.getElementById("opl'.$index.'x0").style.whiteSpace=(numrows==1?"nowrap":"");' . "\r\n";

			print 'document.getElementById("oph'.$index.'").rows=numrows;' . "\r\n";
			print 'document.getElementById("oph'.$index.'").style.whiteSpace=(numrows==1?"nowrap":"");' . "\r\n";
		}
	} ?>
}
function disableelem(theelemtxt,isdis){
	var theelem=document.getElementById(theelemtxt);
	if(isdis){
		theelem.disabled=true;
		theelem.style.backgroundColor="#DDDDDD";
	}else{
		theelem.disabled=false;
		theelem.style.backgroundColor="#FFFFFF";
	}
}
function checkre(theval){
if(document.getElementById('regexp'+theval).value!=''){
	disableelem('pri'+theval,true);
	disableelem('wei'+theval,true);
	if(document.getElementById('wsp'+theval)) disableelem('wsp'+theval,true);
<?php if($useStockManagement) print "disableelem('optStock'+theval,true);" ?>
}else{
	disableelem('pri'+theval,false);
	disableelem('wei'+theval,false);
	if(document.getElementById('wsp'+theval)) disableelem('wsp'+theval,false);
<?php if($useStockManagement) print "disableelem('optStock'+theval,false);" ?>
}
}
var curropttype=0;
/* ]]> */
</script>
<?php
if(getpost('posted')=='1' && (getpost('act')=='modify' || getpost('act')=='clone' || getpost('act')=='addnew')){
	$noptions=0;
	$iscloning=(getpost('act')=='clone');
	if((getpost('act')=='modify' || getpost('act')=='clone') && is_numeric(getpost('id'))){
		$doaddnew=false;
		$sSQL="SELECT optID,optName,optName2,optName3,optGrpName,optGrpName2,optGrpName3,optGrpWorkingName,optPriceDiff,optType,optWeightDiff,optFlags,optStock,optWholesalePriceDiff,optRegExp,optDefault,optGrpSelect,optAltImage,optAltLargeImage,optTxtMaxLen,optTxtCharge,optMultiply,optAcceptChars,optDependants,optPlaceholder,optPlaceholder2,optPlaceholder3,optTooltip FROM options LEFT JOIN optiongroup ON optiongroup.optGrpID=options.optGroup WHERE optGroup=" . getpost('id') . " ORDER BY optID";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){
			$alldata[$noptions++]=$rs;
		}
		ect_free_result($result);
		$optName=$alldata[0]['optName'];
		$optGrpName=$alldata[0]['optGrpName'];
		$optPlaceholder=$alldata[0]['optPlaceholder'];
		for($index=2; $index <= $adminlanguages+1; $index++){
			$optNames[$index]=$alldata[0]['optName' . $index];
			$optGrpNames[$index]=$alldata[0]['optGrpName' . $index];
			$optPlaceholders[$index]=$alldata[0]['optPlaceholder' . $index];
		}
		$optGrpWorkingName=$alldata[0]['optGrpWorkingName'];
		$optPriceDiff=$alldata[0]['optPriceDiff'];
		$optType=$alldata[0]['optType'];
		$optWeightDiff=$alldata[0]['optWeightDiff'];
		$optFlags=$alldata[0]['optFlags'];
		$optStock=$alldata[0]['optStock'];
		$optWholesalePriceDiff=$alldata[0]['optWholesalePriceDiff'];
		$optDefault=$alldata[0]['optDefault'];
		$optGrpSelect=$alldata[0]['optGrpSelect'];
		$optAltImage=$alldata[0]['optAltImage'];
		$optAltLargeImage=$alldata[0]['optAltLargeImage'];
		$optTxtMaxLen=$alldata[0]['optTxtMaxLen'];
		$optTxtCharge=$alldata[0]['optTxtCharge'];
		$optMultiply=$alldata[0]['optMultiply'];
		$optAcceptChars=$alldata[0]['optAcceptChars'];
		$opttooltip=$alldata[0]['optTooltip'];
	}else{
		$doaddnew=true;
		$optName=$optName2=$optName3='';
		$optGrpName=$optGrpName2=$optGrpName3='';
		$optPlaceholder='';
		for($index=2; $index <= $adminlanguages+1; $index++){
			$optNames[$index]='';
			$optGrpNames[$index]='';
			$optPlaceholders[$index]='';
		}
		$optGrpWorkingName='';
		$optPriceDiff=15;
		$optType=(int)getpost('optType');
		$optWeightDiff='';
		$optFlags=0;
		$optStock='';
		$optWholesalePriceDiff='';
		$optDefault='';
		$optGrpSelect=1;
		$optAltImage=$optAltLargeImage='';
		$optTxtMaxLen=0;
		$optTxtCharge=0;
		$optMultiply=0;
		$optAcceptChars=$opttooltip='';
	}
	$iscostperentry=($optTxtCharge<0);
	$optTxtCharge=abs($optTxtCharge);
?>
	<form name="mainform" method="post" action="adminprodopts.php" onsubmit="return formvalidator(this)">
	<input type="hidden" name="posted" value="1" />
	<?php	if($iscloning || getpost('act')=="addnew"){ ?>
	<input type="hidden" name="act" value="doaddnew" />
	<?php	}else{ ?>
	<input type="hidden" name="act" value="domodify" />
	<input type="hidden" name="id" value="<?php print getpost('id')?>" />
	<?php	}
			writehiddenvar('disp', getpost('disp'));
			writehiddenvar('stext', getpost('stext'));
			writehiddenvar('stype', getpost('stype'));
			writehiddenvar('pg', getpost('pg'));
			if(abs($optType)==3) print '<input type="hidden" name="optType" value="3" />'; ?>
	  <table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
		  <td align="center">
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
<?php		if(abs((int)$optType)==3){
				$fieldHeight=round(((double)($optPriceDiff)-floor($optPriceDiff))*100.0); ?>
			  <tr> 
                <td colspan="4" align="center"><strong><?php print (getpost('act')=='clone'?$yyClone:$yyModify).': '.$yyPOAdm?></strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td align="right" height="30"><?php print $yyPOName?>:</td><td align="left"><input type="text" name="secname" size="30" value="<?php print htmldisplay($optGrpName)?>" /></td>
				<td align="right"><?php print $yyDefTxt?>:</td><td align="left"><textarea name="opt0" id="opt0" cols="30" rows="<?php print $fieldHeight?>"><?php print htmldisplay($optName)?></textarea></td>
			  </tr>
			  <tr>
				<td align="right" height="30">&nbsp;</td><td align="left">&nbsp;</td>
				<td align="right">Placeholder:</td><td align="left"><textarea name="oph0" id="oph0" cols="30" rows="<?php print $fieldHeight?>"><?php print htmldisplay($optPlaceholder)?></textarea></td>
			  </tr>
<?php			for($index=2; $index <= $adminlanguages+1; $index++){
					if(($adminlangsettings & 16)==16){ ?>
			  <tr>
				<td align="right" height="30"><?php print $yyPOName . " " . $index?>:</td><td align="left"><input type="text" name="secname<?php print $index?>" size="30" value="<?php print htmldisplay($optGrpNames[$index])?>" /></td>
				<td align="right"><?php print $yyDefTxt . " " . $index?>:</td><td align="left"><textarea name="opl<?php print $index?>x0" id="opl<?php print $index?>x0" cols="30" rows="<?php print $fieldHeight?>"><?php print htmldisplay($optNames[$index])?></textarea></td>
			  </tr>
			  <tr>
				<td align="right" height="30">&nbsp;</td><td align="left">&nbsp;</td>
				<td align="right"><?php print "Placeholder" . " " . $index?>:</td><td align="left"><textarea name="oph<?php print $index?>" id="oph<?php print $index?>" cols="30" rows="<?php print $fieldHeight?>"><?php print htmldisplay($optPlaceholders[$index])?></textarea></td>
			  </tr><?php
					}
				} ?>
			  <tr>
				<td align="right" rowspan="3" height="30"><?php print $yyWrkNam?>:</td>
				<td align="left" rowspan="3"><input type="text" name="workingname" size="30" value="<?php print str_replace('"',"&quot;",$optGrpWorkingName)?>" /></td>
				<td align="right" height="30"><?php print $yyFldWdt?>:</td>
				<td align="left"><select name="pri0" size="1"><?php
					for($rowcounter=1; $rowcounter <= 35; $rowcounter++){
						print "<option value='" . $rowcounter . "'";
						if($rowcounter==(int)$optPriceDiff) print ' selected="selected"';
						print '>&nbsp; ' . $rowcounter . " </option>\n";
					}
				?>
				</select></td>
			  </tr>
			  <tr>
				<td align="right" height="30"><?php print $yyFldHgt?>:</td>
				<td align="left"><select name="fieldheight" size="1" onchange="switchtextinput(this.selectedIndex+1)"><?php
					for($rowcounter=1; $rowcounter <= 15; $rowcounter++){
						print "<option value='" . $rowcounter . "'";
						if($rowcounter==$fieldHeight) print ' selected="selected"';
						print '>&nbsp; ' . $rowcounter . " </option>\n";
					}
				?>
				</select></td>
			  </tr>
			  <tr>
				<td align="right" height="30"><?php print $yyMaxEnt?>:</td>
				<td align="left"><select name="optTxtMaxLen" size="1">
				<option value="0">MAX</option><?php
					for($rowcounter=1; $rowcounter <= 255; $rowcounter++){
						print "<option value='" . $rowcounter . "'";
						if($rowcounter==$optTxtMaxLen) print ' selected="selected"';
						print '>&nbsp; ' . $rowcounter . " </option>\n";
					}
				?>
				</select></td>
			  </tr>
			  <tr>
				<td align="right" height="30"><?php print $yyForSel?>:</td><td colspan="3" align="left"><input type="checkbox" name="forceselec" value="ON"<?php if($optType > 0) print ' checked="checked"'?> /></td>
			  </tr>			  
			  <tr>
				<td align="right" height="30"><?php print $yyCosPer?>:</td><td align="left" colspan="3"><select name="iscostperentry" size="1"><option value=""><?php print $yyCosCha?></option><option value="1"<?php if($iscostperentry) print ' selected="selected"'?>><?php print $yyCosEnt?></option></select> <input type="text" name="optTxtCharge" value="<?php print htmlspecials($optTxtCharge)?>" size="5" /></td>
			  </tr>
			  <tr>
				<td align="right" height="30"><?php print $yyIsMult?>:</td>
				<td align="left"><input type="checkbox" name="optMultiply"<?php if($optMultiply!=0) print ' checked="checked"'?> value="ON" /></td>
				<td align="right"><?php print $yyAccCha?>:</td>
				<td align="left"><input type="text" name="optAcceptChars" value="<?php print htmlspecials($optAcceptChars)?>" size="15" /></td>
			  </tr>
			  <tr>
				<td align="right">Tooltip:</td>
				<td colspan="3"><textarea name="opttooltip" cols="50" rows="10"><?php print $opttooltip?></textarea></td>
			  </tr>
			  <tr>
				<td colspan="4" align="left">
				  <ul>
				  <li><span style="font-size:10px"><?php print $yyPOEx1?></span></li>
				  <li><span style="font-size:10px"><?php print $yyPOEx2?></span></li>
				  <li><span style="font-size:10px"><?php print $yyPOEx3?></span></li>
				  </ul>
				  <input type="hidden" name="maxoptnumber" id="maxoptnumber" value="0" />
                </td>
			  </tr>
<?php	}else{ ?>
			  <tr>
				<td width="30%" align="center">
				  <table border="0" cellspacing="0" cellpadding="3">
				  <tr><td align="right"><strong><?php print str_replace(' ','&nbsp;',$yyPOName)?></strong></td><td align="left" colspan="3">
				  <input type="text" name="secname" size="30" value="<?php print htmldisplay($optGrpName)?>" /></td></tr>
<?php			for($index=2; $index <= $adminlanguages+1; $index++){
					if(($adminlangsettings & 16)==16){
						?><tr><td align="right"><strong><?php print $yyPOName . ' ' . $index?></strong></td><td align="left" colspan="3">
						<input type="text" name="secname<?php print $index?>" size="30" value="<?php print htmldisplay($optGrpNames[$index])?>" /></td></tr><?php
					}
				} ?>
				  <tr><td align="right"><strong><?php print str_replace(' ','&nbsp;',$yyWrkNam)?></strong></td><td align="left" colspan="3"><input type="text" name="workingname" size="30" value="<?php print htmldisplay($optGrpWorkingName)?>" /></td></tr>
				  <tr><td align="right"><strong><?php print str_replace(' ','&nbsp;',$yyOptSty)?></strong></td><td align="left" colspan="3"><select name="optType" id="optType" size="1" onclick="curropttype=this.selectedIndex" onchange="checkmultipurchase(this)"><option value="2"><?php print $yyDDMen?></option><option value="1"<?php if(abs($optType)==1) print ' selected="selected"'?>><?php print $yyRadBut?></option><option value="4"<?php if(abs($optType)==4) print ' selected="selected"'?>><?php print $yyMulPur?></option></select></td></tr>
				  <tr><td align="right" style="white-space:nowrap"><strong><?php print str_replace(' ','&nbsp;',$yyForSel)?></strong></td><td align="left" style="white-space:nowrap"><input type="checkbox" name="forceselec" value="ON"<?php if($optType > 0) print ' checked="checked"'?> />&nbsp;</td><td align="right" style="white-space:nowrap">&nbsp;<input type="radio" name="optdefault" value="" /></td><td align="left" style="white-space:nowrap"><strong><?php print str_replace(' ','&nbsp;',$yyNoDefa)?></strong></td></tr>
				  <tr><td align="right" style="white-space:nowrap"><strong><?php print str_replace(' ','&nbsp;',$yySinLin)?></strong></td><td align="left" style="white-space:nowrap"><input type="checkbox" name="singleline" value="1"<?php if(($optFlags & 4)==4) print ' checked="checked"'?> /></td><td align="right" style="white-space:nowrap"><input type="checkbox" name="optgrpselect" value="1"<?php if((int)$optGrpSelect!=0) print ' checked="checked"'?> /></td><td align="left" style="white-space:nowrap"><strong><span id="plsselspan"><?php print str_replace(' ','&nbsp;',(abs($optType)==4?$yyDtPgOn:$yyPlsSLi))?></span></strong></td></tr>
				  </table>
                </td>
				<td colspan="2" align="left">
				  <p align="center"><strong><?php print (getpost('act')=='clone'?$yyClone:$yyModify).': '.$yyPOAdm?></strong></p>
				  <ul>
				  <li><span style="font-size:10px"><?php print $yyPOEx1?></span></li>
				  <li><span style="font-size:10px"><?php print $yyPOEx4?></span></li>
				  <li><span style="font-size:10px"><?php print $yyPOEx5?></span></li>
				  <?php if($useStockManagement){ ?>
				  <li><span style="font-size:10px"><?php print $yyPOEx6?></span></li>
				  <?php } ?>
				  </ul>
				  <div style="text-align:center"><table style="margin:0 auto"><tr><td align="right"><strong>Tooltip</strong></td><td><textarea name="opttooltip" cols="50" rows="3" onfocus="this.rows=10" onblur="this.rows=3"><?php print $opttooltip?></textarea></td></tr></table></div>
                </td>
			  </tr>
			</table>
			<table id="optiontable" width="500" border="0" cellspacing="0" cellpadding="3">
			<col /><col /><col /><col id="raquo1" /><?php if($adminlanguages>=1 && ($adminlangsettings & 32)==32) print '<col /><col id="raquo1a" />'?><col /><col id="raquo2" /><col /><col id="raquo3" /><col />
			  <tr>
			  	<td><strong><?php print $yyDefaul?></strong></td>
				<td width="3%" align="center">&nbsp;</td>
				<td align="center"><select name="switcher" id="switcher" size="1" onchange="doswitcher()"><option value="1"><?php print $yyPOOpts.' / '.$yyVals?></option><option value="2"><?php print $yyPOOpts.' / '.$yyAlts?></option><option value="3"<?php if(abs($optType)==4) print ' disabled="disabled"'?>>Dependent Options</option></select></td>
				<td width="3%" align="center">&nbsp;</td>
<?php			if($adminlanguages>=1 && ($adminlangsettings & 32)==32){
					print '<td align="center"><select name="langid" id="langid" size="1" onchange="doswitchlang()">';
					for($index=2; $index <= $adminlanguages+1; $index++){
						print '<option value="'.$index.'">' . $yyPOOpts . ' Language ' . $index . '</option>';
					}
					print '</select></td><td align="center">&nbsp;</td>';
				} ?>
				<td align="center" style="white-space:nowrap;"><strong><span id="swprdiff"><?php if(@$wholesaleoptionpricediff==TRUE) print $yyPrWsa; else print $yyPOPrDf?>&nbsp;%<input class="noborder" type="checkbox" name="pricepercent" value="1" onclick="changeunits();"<?php if(($optFlags & 1)==1) print ' checked="checked"'?> /></span><span id="swaltid" style="display:none"><?php print $yyAltPId?></span></strong></td>
				<td width="3%" align="center">&nbsp;</td>
				<td align="center" style="white-space:nowrap;"><strong><span id="swwtdiff"><?php print $yyPOWtDf?>&nbsp;%<input class="noborder" type="checkbox" name="weightpercent" value="1" onclick="changeunits();"<?php if(($optFlags & 2)==2) print ' checked="checked"'?> /></span><span id="swaltimg" style="display:none"><?php print $yyAltIm?></span></strong></td>
				<td width="3%" align="center">&nbsp;</td>
				<td align="left" style="white-space:nowrap" id="depcell"><strong><span id="swstk"><?php print $yyStkLvl?></span><span id="swaltlgim" style="display:none"><?php print $yyAltLIm?></span><span id="depopts" style="display:none">Dependent Options</span></strong></td>
			  </tr>
<?php		if(($optFlags & 1)==1) $pdUnits="&nbsp;%&nbsp;"; else $pdUnits="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			if(($optFlags & 2)==2) $wdUnits="&nbsp;%&nbsp;"; else $wdUnits="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			for($rowcounter=0; $rowcounter < max(15, $noptions+5); $rowcounter++){ ?>
			  <tr>
				<td><input type="radio" name="optdefault" value="<?php print $rowcounter?>"<?php if($rowcounter < $noptions){ if($alldata[$rowcounter]['optDefault']!=0) print ' checked="checked"'; }?> /></td>
				<td align="center"><input type="button" id="insertopt<?php print $rowcounter?>" value="+" onclick="insertoption(this)" /></td>
				<td align="center"><?php
					if($rowcounter < $noptions && ! $iscloning) print '<input type="hidden" name="orig' . $rowcounter . '" value="' . $alldata[$rowcounter]['optID'] . '" />';
					print '<input type="text" name="opt' . $rowcounter . '" id="opt' . $rowcounter . '" size="20" value="';
					if($rowcounter < $noptions) print str_replace('"', '&quot;',$alldata[$rowcounter]["optName"]);
					print "\" /><br />\n";
				?></td><td><strong>&raquo;</strong></td>
<?php			if($adminlanguages>=1 && ($adminlangsettings & 32)==32){
					print '<td align="center">';
					for($index=2; $index <= $adminlanguages+1; $index++){
						print '<span id="lang'.$index.'x'.$rowcounter.'"';
						if($index>2) print ' style="display:none">'; else print '>';
						print '<input type="text" name="opl'.$index.'x'.$rowcounter.'" id="opl'.$index.'x'.$rowcounter.'" size="20" value="';
						if($rowcounter < $noptions) print str_replace('"', '&quot;',$alldata[$rowcounter]['optName' . $index]);
						print '" /></span>';
					}
					print '</td><td><strong>&raquo;</strong></td>';
				} ?>
				<td align="center"><span id="swprdiff<?php print $rowcounter?>"><?php
					if($rowcounter < $noptions) $optvalue=$alldata[$rowcounter]['optPriceDiff']; else $optvalue=0;
					print '&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="' . ($optvalue!=0?'pri' . $rowcounter:'xxx') . '" id="pri'.$rowcounter.'" size="5" value="';
					if($rowcounter < $noptions) print $alldata[$rowcounter]['optPriceDiff'];
					print '" onchange="this.name=this.id" />';
					if(@$wholesaleoptionpricediff==TRUE){
						if($rowcounter < $noptions) $optvalue=$alldata[$rowcounter]['optWholesalePriceDiff']; else $optvalue=0;
						print ' / <input type="text" name="' . ($optvalue!=0?'wsp' . $rowcounter:'xxx') . '" id="wsp'.$rowcounter.'" size="5" value="';
						if($rowcounter < $noptions) print $optvalue;
						print '" onchange="this.name=this.id" />';
					}
					print '<span id="punitspan'.$rowcounter.'">'.$pdUnits.'</span>';
					if($rowcounter < $noptions) $optvalue=$alldata[$rowcounter]['optRegExp']; else $optvalue='';
				?></span><span id="swaltid<?php print $rowcounter?>" style="display:none"><input type="text" name="<?php print ($optvalue!=''?'regexp' . $rowcounter:'xxx')?>" id="regexp<?php print $rowcounter?>" onchange="this.name=this.id;checkre(<?php print $rowcounter?>)" size="12" value="<?php print $optvalue; ?>" /></span></td>
				<td><strong>&raquo;</strong></td>
				<td align="center" style="white-space:nowrap;"><span id="swwtdiff<?php print $rowcounter?>"><?php
					if($rowcounter < $noptions) $optvalue=$alldata[$rowcounter]['optWeightDiff']; else $optvalue=0;
					print '&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="' . ($optvalue!=0?'wei' . $rowcounter:'xxx') . '" id="wei'.$rowcounter.'" size="5" value="';
					if($rowcounter < $noptions) print $optvalue;
					print '" onchange="this.name=this.id" /><span id="wunitspan'.$rowcounter.'">'.$wdUnits.'</span>';
					if($rowcounter < $noptions) $optvalue=$alldata[$rowcounter]['optAltImage']; else $optvalue='';
				?></span><span id="swaltimg<?php print $rowcounter?>" style="display:none"><input type="text" name="<?php print ($optvalue!=''?'altimg' . $rowcounter:'xxx')?>" id="altimg<?php print $rowcounter?>" size="20" value="<?php print $optvalue ?>" onchange="this.name=this.id" /></span></td>
				<td><strong>&raquo;</strong></td>
				<td align="center" style="white-space:nowrap" id="depcell<?php print $rowcounter?>"><span id="swstk<?php print $rowcounter?>"><?php
					if($rowcounter < $noptions) $optvalue=$alldata[$rowcounter]['optStock']; else $optvalue=0;
					if($useStockManagement){
						print '<input type="text" name="' . ($optvalue!=0?'optStock' . $rowcounter:'xxx') . '" id="optStock'.$rowcounter.'" size="4" value="';
						if($rowcounter < $noptions){
							print $optvalue;
							if(trim($alldata[$rowcounter]['optRegExp'])) print '" disabled="disabled';
						}
						print '" onchange="this.name=this.id" />';
					}
					if($rowcounter < $noptions) $optvalue=$alldata[$rowcounter]['optAltLargeImage']; else $optvalue='';
				?></span><span id="swaltlgim<?php print $rowcounter?>" style="display:none"><input type="text" name="<?php print ($optvalue!=''?'altlimg' . $rowcounter:'xxx')?>" id="altlimg<?php print $rowcounter?>" size="20" value="<?php print $optvalue; ?>" onchange="this.name=this.id" /></span>
					<span id="depopts<?php print $rowcounter?>" style="display:none">
<?php				if($rowcounter < $noptions) $optDependants=commaseplist($alldata[$rowcounter]['optDependants']); else $optDependants='';
					$optionindex=1;
					if($optDependants!=''){
						$sSQL='SELECT optGrpID,optGrpWorkingName FROM optiongroup WHERE optGrpID IN ('.$optDependants.')';
						$result2=ect_query($sSQL) or ect_error();
						$nalldependants=0;
						while($rs2=ect_fetch_assoc($result2)){
							$alldependants[$nalldependants++]=$rs2;
						}
						ect_free_result($result2);
						if($nalldependants>0){
							$depsarray=explode(',',$optDependants);
							for($index2=0;$index2<count($depsarray);$index2++){
								if(is_numeric($depsarray[$index2])){
									for($index=0;$index<$nalldependants;$index++){
										if((int)$depsarray[$index2]==$alldependants[$index]['optGrpID']){
											print '<select id="depopts'.$rowcounter."_".$optionindex.'" name="depopts'.$rowcounter."_".$optionindex.'" onmouseover="populateoptionsselect(this)" style="width:140px"><option value="x">'.$yySelect.'</option><option value="'.$alldependants[$index]['optGrpID'].'" selected="selected">'.$alldependants[$index]['optGrpWorkingName'].'</option></select>&nbsp;';
											$optionindex++;
										}
									}
								}
							}
						}
					}
					print '<select id="depopts'.$rowcounter."_".$optionindex.'" onmouseover="populateoptionsselect(this)" onchange="addoptionselect(this)" style="width:140px"><option value="x">'.$yySelect.'</option></select>';
?>					</span>
				</td>
			  </tr>
<?php		} ?>
			</table>
			<input type="hidden" name="maxoptnumber" id="maxoptnumber" value="<?php print $rowcounter?>" />
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
<?php	} ?>
			  <tr>
                <td width="100%" colspan="4" align="center"><br />
<?php	if(abs((int)$optType)!=3){ ?>
				<input type="text" name="numextrarows" id="numextrarows" value="10" size="4" /> <input type="button" value="<?php print $yyMore . ' ' . $yyPOOpts?>" onclick="addmorerows()" />&nbsp;&nbsp;&nbsp;&nbsp;
<?php	} ?>
				<input type="submit" value="<?php print $yySubmit?>" /><?php if(getpost('act')=='modify' || getpost('act')=='clone'){ ?>&nbsp;&nbsp;&nbsp;<input type="reset" value="<?php print $yyReset?>" /><?php } ?><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </td>
		</tr>
	  </table>
	</form>
<?php	if(abs((int)$optType)!=3){ ?>
<script type="text/javascript">
/* <![CDATA[ */
for(var ti=0; ti<=<?php print $noptions?>; ti++) checkre(ti);
/* ]]> */
</script>
<?php	}
}elseif(getpost('posted')=='1' && $success){ ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="adminprodopts.php"><strong><?php print $yyClkHer?></strong></a>.<br /><br />&nbsp;
                </td>
			  </tr>
			</table>
<?php
}elseif(getpost('posted')=="1"){ ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table>
<?php
}else{
	$pract=@$_COOKIE['practopt'];
	$modclone=@$_COOKIE['modclone']; ?>
<script type="text/javascript">
/* <![CDATA[ */
function setCookie(c_name,value,expiredays){
	var exdate=new Date();
	exdate.setDate(exdate.getDate()+expiredays);
	document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}
function mr(id){
	document.mainform.id.value=id;
	document.mainform.act.value="modify";
	document.mainform.submit();
}
function cr(id){
	document.mainform.id.value=id;
	document.mainform.act.value="clone";
	document.mainform.submit();
}
function newtextrec(id) {
	document.mainform.id.value=id;
	document.mainform.act.value="addnew";
	document.mainform.optType.value="3";
	document.mainform.submit();
}
function newrec(id) {
	document.mainform.id.value=id;
	document.mainform.act.value="addnew";
	document.mainform.optType.value="2";
	document.mainform.submit();
}
function quickupdate(){
	if(document.mainform.pract.value=="del"){
		if(!confirm("<?php print jscheck($yyConDel)?>\n"))
			return;
	}
	document.mainform.action="adminprodopts.php";
	document.mainform.act.value="quickupdate";
	document.mainform.posted.value="1";
	document.mainform.submit();
}
function dr(id){
if(confirm("<?php print jscheck($yyConDel)?>\n")) {
	document.mainform.id.value=id;
	document.mainform.act.value="delete";
	document.mainform.submit();
}
}
function startsearch(){
	document.mainform.action="adminprodopts.php";
	document.mainform.act.value="search";
	document.mainform.posted.value="";
	document.mainform.submit();
}
function changepract(obj){
	setCookie('practopt',obj[obj.selectedIndex].value,600);
	startsearch();
}
function changemodclone(modclone){
	setCookie('modclone',modclone[modclone.selectedIndex].value,600);
	startsearch();
}
function checkboxes(docheck){
	maxitems=document.getElementById("resultcounter").value;
	for(index=0;index<maxitems;index++){
		document.getElementById("chkbx"+index).checked=docheck;
	}
}
function setselects(tsmen){
	if(tsmen.selectedIndex==0){
		document.forms.mainform.reset();
	}else{
		maxitems=document.getElementById("resultcounter").value;
		for(index=0;index<maxitems;index++){
			if(document.getElementById("selbx"+index)) document.getElementById("selbx"+index).selectedIndex=tsmen.selectedIndex-1;
		}
	}
}
/* ]]> */
</script>
<?php
	$stext=getrequest('stext');
?>
<h2><?php print $YYAdmPrO?></h2>
		  <form name="mainform" method="post" action="adminprodopts.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="id" value="xxxxx" />
			<input type="hidden" name="optType" value="xxxxx" />
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
				  <tr><td class="cobhl" align="center" colspan="4" height="22"><strong><?php print $yyPOAdm?></strong></td></tr>
				  <tr> 
	                <td class="cobhl" width="25%" align="right"><?php print $yySrchFr?>:</td>
					<td class="cobll" width="25%"><input type="text" name="stext" size="20" value="<?php print $stext?>" /></td>
				    <td class="cobhl" width="25%" align="right"><?php print $yySrchTp?>:</td>
					<td class="cobll" width="25%"><select name="stype" size="1">
						<option value=""><?php print $yySrchAl?></option>
						<option value="any"<?php if(@$_REQUEST['stype']=='any') print ' selected="selected"'?>><?php print $yySrchAn?></option>
						<option value="exact"<?php if(@$_REQUEST['stype']=='exact') print ' selected="selected"'?>><?php print $yySrchEx?></option>
						</select>
					</td>
	              </tr>
				  <tr>
				    <td class="cobhl" align="center"><?php
					if(getpost('act')=='search' || getget('pg')!=''){
						if($pract=='del'){ ?>
						<input type="button" value="<?php print $yyCheckA?>" onclick="checkboxes(true);" /> <input type="button" value="<?php print $yyUCheck?>" onclick="checkboxes(false);" />
<?php					}elseif($pract=='oty')
							print '<select size="1" onchange="setselects(this)"><option value="">Change All Options...</option><option value="2">'.$yyDDMen.'</option><option value="1">'.$yyRadBut.'</option><option value="4">'.$yyMulPur.'</option></select>';
					}else
						print '&nbsp;' ?></td>
				    <td class="cobll" colspan="3"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					    <tr>
						  <td class="cobll" align="center" style="white-space:nowrap">
							<select name="disp" size="1">
							<option value="">All Options</option>
							<option value="2"<?php if(@$_REQUEST['disp']=='2') print ' selected="selected"'?>>Text Options</option>
							<option value="3"<?php if(@$_REQUEST['disp']=='3') print ' selected="selected"'?>>Multiple Purchase Options</option>
							<option value="4"<?php if(@$_REQUEST['disp']=='4') print ' selected="selected"'?>>Dropdown Options</option>
							<option value="5"<?php if(@$_REQUEST['disp']=='5') print ' selected="selected"'?>>Radio Options</option>
<?php					if($useStockManagement) print '<option value="6"'.(@$_REQUEST['disp']=='6'?' selected="selected"':'').'>'.$yyOOStoc.'</option>' ?>
							<option value="7"<?php if(@$_REQUEST['disp']=='7') print ' selected="selected"'?>>Unused Options</option>
							</select>
							<input type="submit" value="List Options" onclick="startsearch();" />
						  </td>
						  <td class="cobll" height="26" width="20%" align="right" style="white-space:nowrap">
							<input type="button" value="<?php print $yyPONew?>" onclick="newrec()" />&nbsp;&nbsp;
							<input type="button" value="<?php print $yyPONewT?>" onclick="newtextrec()" />
						  </td>
						</tr>
					  </table></td>
				  </tr>
				</table>
<br />
            <table width="100%" class="stackable admin-table-a sta-white">
<?php
$jscript='';
if(getpost('act')=='search' || getget('pg')!=''){
	$sSQL='SELECT optGrpID,optGrpName,optGrpName2,optGrpName3,optGrpWorkingName,optType FROM optiongroup';
	$whereand=' WHERE ';
	if(@$_REQUEST['disp']=='6')
		$sSQL="SELECT DISTINCT optGrpID,optGrpName,optGrpWorkingName FROM optiongroup INNER JOIN options ON optiongroup.optGrpID=options.optGroup INNER JOIN prodoptions ON options.optGroup=prodoptions.poOptionGroup INNER JOIN products ON prodoptions.poProdID=products.pID WHERE options.optStock<=0 AND (optRegExp='' OR optRegExp IS NULL) AND products.pStockByOpts<>0 AND optType IN (-4,-2,-1,1,2,4)";
	elseif(@$_REQUEST['disp']=='7')
		$sSQL='SELECT optGrpID,optGrpName,optGrpName2,optGrpName3,optGrpWorkingName,poProdID FROM optiongroup LEFT JOIN prodoptions ON optiongroup.optGrpID=prodoptions.poOptionGroup WHERE poProdID IS NULL';
	elseif(@$_REQUEST['disp']=='2')
		$sSQL.=' WHERE optType IN (-3,3)';
	elseif(@$_REQUEST['disp']=='3')
		$sSQL.=' WHERE optType IN (-4,4)';
	elseif(@$_REQUEST['disp']=='4')
		$sSQL.=' WHERE optType IN (-2,2)';
	elseif(@$_REQUEST['disp']=='5')
		$sSQL.=' WHERE optType IN (-1,1)';
	if(@$_REQUEST['disp']!='') $whereand=' AND ';
	if(trim($stext)!=''){
		$Xstext=escape_string($stext);
		$aText=explode(' ',$Xstext);
		$maxsearchindex=1;
		$aFields[0]='optGrpWorkingName';
		$aFields[1]='optGrpName';
		if(@$_REQUEST['stype']=='exact'){
			$sSQL.=$whereand . "(optGrpName LIKE '%" . $Xstext . "%' OR optGrpWorkingName LIKE '%" . $Xstext . "%') ";
			$whereand=' AND ';
		}else{
			$sJoin='AND ';
			if(@$_REQUEST['stype']=='any') $sJoin='OR ';
			$sSQL.=$whereand . '(';
			$whereand=' AND ';
			for($index=0;$index<=$maxsearchindex;$index++){
				$sSQL.='(';
				$rowcounter=0;
				$arrelms=count($aText);
				foreach($aText as $theopt){
					if(is_array($theopt))$theopt=$theopt[0];
					$sSQL.=$aFields[$index] . " LIKE '%" . $theopt . "%' ";
					if(++$rowcounter < $arrelms) $sSQL.=$sJoin;
				}
				$sSQL.=') ';
				if($index < $maxsearchindex) $sSQL.='OR ';
			}
			$sSQL.=') ';
		}
	}
	$sSQL.=' ORDER BY optGrpWorkingName,optGrpName';
	$result=ect_query($sSQL) or ect_error();
	if(ect_num_rows($result) > 0){ ?>
			  <tr>
				<th class="minicell">
					<select name="pract" id="pract" size="1" onchange="changepract(this)">
					<option value="none">Quick Entry...</option>
					<option value="opn"<?php if($pract=='opn') print ' selected="selected"'?>><?php print $yyPOName?></option>
<?php				for($index=2; $index<=$adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16) print '<option value="opn'.$index.'"'.($pract==("opn".$index)?' selected="selected"':'').'>'.$yyPOName.' '.$index.'</option>';
					} ?>
					<option value="own"<?php if($pract=='own') print ' selected="selected"'?>><?php print $yyWrkNam?></option>
					<option value="oty"<?php if($pract=='oty') print ' selected="selected"'?>><?php print $yyOptSty?></option>
					<option value="" disabled="disabled">------------------</option>
					<option value="del"<?php if($pract=='del') print ' selected="selected"'?>><?php print $yyDelete?></option>
					</select></th>
				<th class="maincell"><strong><?php print $yyPOName?></strong></th>
				<th class="maincell"><strong><?php print $yyWrkNam?></strong></th>
				<th class="minicell"><?php print $yyModify?></th>
			  </tr>
<?php	while($rs=ect_fetch_assoc($result)){
			$jscript.='pa['.$resultcounter.']=['; ?>
<tr id="tr<?php print $resultcounter?>"><td class="minicell"><?php
				if($pract=='opn')
					print '<input type="text" id="chkbx'.$resultcounter.'" size="18" name="pra_'.$rs['optGrpID'].'" value="' . $rs['optGrpName'] . '" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='opn2')
					print '<input type="text" id="chkbx'.$resultcounter.'" size="18" name="pra_'.$rs['optGrpID'].'" value="' . $rs['optGrpName2'] . '" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='opn3')
					print '<input type="text" id="chkbx'.$resultcounter.'" size="18" name="pra_'.$rs['optGrpID'].'" value="' . $rs['optGrpName3'] . '" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='own')
					print '<input type="text" id="chkbx'.$resultcounter.'" size="18" name="pra_'.$rs['optGrpID'].'" value="' . $rs['optGrpWorkingName'] . '" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='oty'){
					$opttype=abs($rs['optType']);
					if($opttype==3)
						print '-';
					else
						print '<select id="selbx'.$resultcounter.'" size="1" name="pra_'.$rs['optGrpID'].'" tabindex="'.($resultcounter+1).'"><option value="2"'.($opttype==2?' selected="selected"':'').'>DROPDOWN</option><option value="1"'.($opttype==1?' selected="selected"':'').'>RADIO</option><option value="4"'.($opttype==4?' selected="selected"':'').'>MULTIPLE</option></select>';
				}elseif($pract=='del')
					print '<input type="checkbox" id="chkbx'.$resultcounter.'" name="pra_'.$rs['optGrpID'].'" value="del" tabindex="'.($resultcounter+1).'"/>';
				else
					print '&nbsp;';
?><td><?php print $rs['optGrpName']?></td><td><?php print $rs['optGrpWorkingName']?></td><td>-</td></tr>
<?php		$jscript.=$rs['optGrpID']."];\r\n";
			$resultcounter++;
		}
	}else{
?>
			  <tr>
                <td width="100%" colspan="4" align="center"><br /><?php print $yyItNone?><br />&nbsp;</td>
			  </tr>
<?php
	}
	ect_free_result($result);
}
?>			  <tr>
				<td align="center" style="white-space:nowrap"><?php if($resultcounter>0 && $pract!='' && $pract!='none') print '<input type="hidden" name="resultcounter" id="resultcounter" value="'.$resultcounter.'" /><input type="button" value="'.$yyUpdate.'" onclick="quickupdate()" /> <input type="reset" value="'.$yyReset.'" />'; else print '&nbsp;'?></td>
                <td width="100%" colspan="4" align="center"><br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;<br /></td>
			  </tr>
            </table>
		  </form>
<script type="text/javascript">
/* <![CDATA[ */
var pa=[];
<?php print $jscript?>
for(var pidind in pa){
	var ttr=document.getElementById('tr'+pidind);
	ttr.cells[3].style.textAlign='center';
	ttr.cells[3].style.whiteSpace='nowrap';
	ttr.cells[3].innerHTML='<input type="button" value="M" style="width:30px" onclick="mr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyModify))?>" />&nbsp;' +
		'<input type="button" value="C" style="width:30px" onclick="cr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyClone))?>" />&nbsp;' +
		'<input type="button" value="X" style="width:30px" onclick="dr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyDelete))?>" />';
}
/* ]]> */
</script>
<?php
}
?>