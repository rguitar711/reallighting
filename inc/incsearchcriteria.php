<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
$addsuccess=FALSE;
$success=FALSE;
$maxcatsperpage=500;
$showaccount=FALSE;
$dorefresh=FALSE;
$alreadygotadmin=getadminsettings();
if(@$defaultcatimages=='') $defaultcatimages='images/';
if(getget("act")=="repair"){
	$sSQL="SELECT mSCpID,mSCscID FROM (multisearchcriteria LEFT JOIN products ON multisearchcriteria.mSCpID=products.pID) LEFT JOIN searchcriteria ON multisearchcriteria.mSCscID=searchcriteria.scID WHERE pID IS NULL OR scID IS NULL";
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		ect_query("DELETE FROM multisearchcriteria WHERE mSCpID='".$rs['mSCpID']."' AND mSCscID=".$rs['mSCscID']) or ect_error();
	}
	ect_free_result($result);
}elseif(getpost("act")=="newattribute" && is_numeric(getpost("group"))){
	$haveuniqueindex=FALSE;
	$uniqueindex=1;
	while(! $haveuniqueindex){
		$result=ect_query("SELECT scID FROM searchcriteria WHERE scID=".$uniqueindex) or ect_error();
		if(ect_num_rows($result)==0) $haveuniqueindex=TRUE; else $uniqueindex++;
		ect_free_result($result);
	}
	$sSQL="SELECT MAX(scOrder) AS maxorder FROM searchcriteria WHERE scGroup=".getpost("group");
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $maxorder=$rs['maxorder']; else $maxorder=0;
	ect_free_result($result);
	if(is_null($maxorder))
		$maxorder=1;
	elseif($maxorder>0)
		$maxorder++;
	$sSQL="INSERT INTO searchcriteria (scID,scWorkingName,scName,scName2,scName3,scGroup,scOrder) VALUES (" . $uniqueindex . "," .
		"'" . escape_string(getpost("newwn")!=''?getpost("newwn"):getpost("newname")) . "'," .
		"'" . escape_string(getpost("newname")) . "'," .
		"'" . escape_string(getpost("newname2")!=''?getpost("newname2"):getpost("newname")) . "'," .
		"'" . escape_string(getpost("newname3")!=''?getpost("newname3"):getpost("newname")) . "'," .
		getpost("group") . "," . $maxorder . ")";
	ect_query($sSQL) or ect_error();
	$dorefresh=TRUE;
}elseif(getpost('act')=='dodiscounts'){
	$sSQL='INSERT INTO cpnassign (cpaCpnID,cpaType,cpaAssignment) VALUES ('.getpost('assdisc').",3,'".getpost('id')."')";
	ect_query($sSQL);
	$dorefresh=TRUE;
}elseif(getpost('act')=='deletedisc'){
	$sSQL='DELETE FROM cpnassign WHERE cpaType=3 AND cpaID='.getpost('id');
	ect_query($sSQL);
	$dorefresh=TRUE;
}elseif(getpost('act')=='newgroup'){
	$haveuniqueindex=FALSE;
	$uniqueindex=0;
	while(! $haveuniqueindex){
		$result=ect_query("SELECT scgID FROM searchcriteriagroup WHERE scgID=".$uniqueindex) or ect_error();
		if(ect_num_rows($result)==0) $haveuniqueindex=TRUE; else $uniqueindex++;
		ect_free_result($result);
	}
	$sSQL="SELECT MAX(scgOrder) AS maxorder FROM searchcriteriagroup";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $maxorder=$rs['maxorder']; else $maxorder=0;
	ect_free_result($result);
	if(is_null($maxorder)) $maxorder=1; else $maxorder++;
	$sSQL="INSERT INTO searchcriteriagroup (scgID,scgWorkingName,scgTitle,scgTitle2,scgTitle3,scgOrder) VALUES (" . $uniqueindex . "," .
		"'" . escape_string(getpost("newwn")!=''?getpost("newwn"):getpost("newname")) . "'," .
		"'" . escape_string(getpost("newname")) . "'," .
		"'" . escape_string(getpost("newname2")!=''?getpost("newname2"):getpost("newname")) . "'," .
		"'" . escape_string(getpost("newname3")!=''?getpost("newname3"):getpost("newname")) . "'," .
		$maxorder . ")";
	ect_query($sSQL) or ect_error();
	$dorefresh=TRUE;
}elseif(getpost('act')=='changepos'){
	$theid=(int)getpost('id');
	$neworder=((int)getpost('newval'))-1;
	$rc=0;
	if(getget('act')=='modifyatts'){
		if(getpost('alphabetically')=='1'){
			$sSQL = "UPDATE searchcriteria SET scOrder=0 WHERE scGroup=".getpost("group");
			ect_query($sSQL) or ect_error();
		}else{
			$sSQL='SELECT scID,scOrder FROM searchcriteria WHERE scGroup IN ('.getpost("group").') ORDER BY scOrder';
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){
				if($rs['scID']==$theid)
					$sSQL="UPDATE searchcriteria SET scOrder=".$neworder." WHERE scID=".$theid;
				else
					$sSQL="UPDATE searchcriteria SET scOrder=".($rc<$neworder?$rc:$rc+1)." WHERE scID=".$rs['scID'];
				ect_query($sSQL) or ect_error();
				$rc++;
			}
		}
	}else{
		$sSQL="SELECT scgID,scgOrder FROM searchcriteriagroup ORDER BY scgOrder";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){
			if($rs['scgID']==$theid)
				$sSQL="UPDATE searchcriteriagroup SET scgOrder=".$neworder." WHERE scgID=".$theid;
			else
				$sSQL="UPDATE searchcriteriagroup SET scgOrder=".($rc<$neworder?$rc:$rc+1)." WHERE scgID=".$rs['scgID'];
			ect_query($sSQL) or ect_error();
			$rc+=1;
		}
		ect_free_result($result);
	}
	print '<meta http-equiv="refresh" content="0; url=adminsearchcriteria.php?pg='. getpost("pg") . (getget("act")=="modifyatts"?"&act=modifyatts&id=".getpost("group"):'') .'">';
}elseif(getpost("act")=="domodifygroup"){
	$scworkingname=getpost("scworkingname");
	if($scworkingname=="") $scworkingname=getpost("scname");
	$sSQL="UPDATE searchcriteriagroup SET " .
		"scgTitle='".escape_string(getpost("scname")) . "',";
	for($index=2; $index<=$adminlanguages+1; $index++){
		if(($adminlangsettings & 131072)==131072)
			$sSQL.="scgTitle".$index."='".escape_string(getpost("scname" . $index)) . "',";
	}
	$sSQL.= "scgWorkingName='".escape_string($scworkingname) . "' " .
		"WHERE scgID=" . replace(getpost("scID"),"'","");
	ect_query($sSQL) or ect_error();
	$dorefresh=TRUE;
}elseif(getpost('act')=='domodify'){
	$scworkingname=getpost('scworkingname');
	if($scworkingname=='') $scworkingname=getpost('scname');
	$sSQL="UPDATE searchcriteria SET scName='" . escape_string(getpost('scname')) . "'," .
		"scURL='".escape_string(getpost("scurl")) . "'," .
		"scDescription='".escape_string(getpost("scDescription")) . "'," .
		"scHeader='".escape_string(getpost("scHeader")) . "',";
	for($index=2; $index<=$adminlanguages+1; $index++){
		if(($adminlangsettings & 131072)==131072)
			$sSQL.='scName'.$index."='".escape_string(@$_POST['scname' . $index]) . "',";
		if(($adminlangsettings & 8192)==8192)
			$sSQL.='scURL'.$index."='".escape_string(getpost('scurl' . $index)) . "',";
		if(($adminlangsettings & 16384)==16384)
			$sSQL.='scDescription'.$index."='".escape_string(getpost('scDescription' . $index)) . "',";
		if(($adminlangsettings & 524288)==524288)
			$sSQL.='scHeader'.$index."='".escape_string(getpost('scHeader' . $index)) . "',";
	}
	$sSQL.="scWorkingName='".escape_string($scworkingname) . "'," .
		"scNotes='".escape_string(getpost('scnotes')) . "'," .
		"scLogo='".escape_string(getpost('sclogo')) . "'," .
		"scEmail='".escape_string(getpost('scemail')) . "' " .
		'WHERE scID=' . str_replace("'",'',getpost('scID'));
	ect_query($sSQL) or ect_error();
	$dorefresh=TRUE;
}elseif(getpost('act')=='doaddnew'){
	// Not used as you have to create it first.
}elseif(getpost('act')=='delete'){
	$sSQL='DELETE FROM multisearchcriteria WHERE mSCscID=' . getpost('id');
	ect_query($sSQL) or ect_error();
	$sSQL='DELETE FROM searchcriteria WHERE scID=' . getpost('id');
	ect_query($sSQL) or ect_error();
	$dorefresh=TRUE;
}elseif(getpost('act')=='deletegroup'){
	$sSQL='DELETE FROM multisearchcriteria WHERE mSCscID IN (SELECT scID FROM searchcriteria WHERE scGroup=' . getpost('id') . ')';
	ect_query($sSQL) or ect_error();
	$sSQL='DELETE FROM searchcriteria WHERE scGroup=' . getpost('id');
	ect_query($sSQL) or ect_error();
	$sSQL='DELETE FROM searchcriteriagroup WHERE scgID=' . getpost('id');
	ect_query($sSQL) or ect_error();
	$dorefresh=TRUE;
}
if($dorefresh){
	if(getpost('act')=='newattribute' || getpost('act')=='domodify' || getpost('act')=='delete')
		print '<meta http-equiv="refresh" content="1; url=adminsearchcriteria.php?act=modifyatts&id='.getpost("group").'">';
	else
		print '<meta http-equiv="refresh" content="1; url=adminsearchcriteria.php">';
}
if($dorefresh){
?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="adminsearchcriteria.php"><strong><?php print $yyClkHer?></strong></a>.<br /><br />&nbsp;
                </td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}elseif((getpost('act')=="modify" || getpost('act')=="addnew") && is_numeric(getpost('group'))){
	$sSQL="SELECT scgTitle FROM searchcriteriagroup WHERE scgID=".getpost("group");
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $groupname=$rs['scgTitle']; else $groupname='';
	ect_free_result($result);
	if(getpost('act')=='modify'){
		$scID=getpost('id');
		$sSQL="SELECT scName,scName2,scName3,scWorkingName,scGroup,scLogo,scURL,scURL2,scURL3,scEmail,scNotes,scHeader,scHeader2,scHeader3 FROM searchcriteria WHERE scID=".$scID;
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$scworkingname=$rs['scWorkingName'];
			$scgroup=$rs['scGroup'];
			for($index=1; $index<=3; $index++){
				$scaName[$index]=$rs['scName'.($index==1?'':$index)];
				$scURL[$index]=$rs['scURL'.($index==1?'':$index)];
			}
			$scEmail=$rs['scEmail'];
			$scLogo=$rs['scLogo'];
			$scNotes=$rs['scNotes'];
			$scHeader=$rs['scHeader'];
			for($index=2; $index<=$adminlanguages+1; $index++){
				$scHeaders[$index]=$rs['scHeader' . $index];
			}
		}
		ect_free_result($result);
	}else{
		$scworkingname='';
		$scgroup='';
		for($index=1; $index<=3; $index++){
			$scaName[$index]='';
		}
		$scHeader=$rs['scHeader'];
		for($index=2; $index<=$adminlanguages+1; $index++){
			$scHeaders[$index]='';
		}
	}
		if(@$htmleditor=='ckeditor'){ ?>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<?php	} ?>
<script type="text/javascript">
<!--
function checkform(frm){
if(frm.name.value==""){
	alert("<?php print jscheck($yyPlsEntr . ' "' . $yyName)?>\".");
	frm.scname.focus();
	return (false);
}
return (true);
}
function uploadimage(imfield){
	var addthumb=0;
	var winwid=360; var winhei=220;
	if(imfield.substring(0,2)=='pG'){ addthumb=2; winhei=300; }
	if(imfield.substring(0,2)=='pL'){ addthumb=1; winhei=280; }
	var prnttext='<html><head><link rel="stylesheet" type="text/css" href="adminstyle.css"/><script type="text/javascript">function getCookie(c_name){if(document.cookie.length>0){var c_start=document.cookie.indexOf(c_name + "=");if(c_start!=-1){c_start=c_start+c_name.length+1;var c_end=document.cookie.indexOf(";",c_start);if(c_end==-1)c_end=document.cookie.length;return unescape(document.cookie.substring(c_start,c_end));}}return "";}';
	prnttext+='function checkcookies(){ for(var ind=0; ind<='+addthumb+'; ind++){\r\n';
	prnttext+='document.getElementById("newdim"+ind).value=getCookie("newdim"+ind);\r\n';
	prnttext+='if(getCookie("suffix"+ind)!="")document.getElementById("suffix"+ind).value=getCookie("suffix"+ind);\r\n';
	prnttext+='if(getCookie("thumbdim"+ind)!="")document.getElementById("thumbdim"+ind).selectedIndex=getCookie("thumbdim"+ind);}\r\n';
	prnttext+='}<'+'/script></head><body<?php if(extension_loaded('gd')) print ' onload="checkcookies()"'?>>\n';
	prnttext+='<form name="mainform" method="post" action="doupload.php?defimagepath=<?php print $defaultcatimages?>" enctype="multipart/form-data">';
	prnttext+='<input type="hidden" name="defimagepath" value="<?php print $defaultcatimages?>" />';
	prnttext+='<input type="hidden" name="imagefield" value="'+imfield+'" />';
	prnttext+='<table border="0" cellspacing="1" cellpadding="1" width="100%">';
	prnttext+='<tr><td align="center" colspan="2">&nbsp;<br /><strong><?php print str_replace("'","\\'", $yyUplIma)?></strong><br />&nbsp;</td></tr>';
	prnttext+='<tr><td align="center" colspan="2"><?php print str_replace("'","\\'", $yyPlsSUp)?><br />&nbsp;</td></tr>';
	prnttext+='<tr><td align="center" colspan="2"><?php print str_replace("'","\\'", $yyLocIma)?>:<input type="file" name="imagefile" /></td></tr>';
<?php	if(extension_loaded('gd')){
			$winhei=260; ?>
	prnttext+='<tr><td colspan="2">&nbsp;</td></tr><tr><td align="right"><select size="1" name="thumbdim0" id="thumbdim0"><option value="">Don\'t Resize Image</option><option value="1">Resize to Width:</option><option value="2">Resize to Height:</option></select></td><td><input type="text" name="newdim0" id="newdim0" size="3" />:px&nbsp;&nbsp;</td></tr>';
<?php	}else
			$winhei=200; ?>
	prnttext+='<tr><td colspan="2" align="center">&nbsp;<br /><input type="submit" value="<?php print str_replace("'","\\'", $yySubmit)?>" /></td></tr>';
	prnttext+='</table></form>';
	prnttext+='<p align="center"><a href="javascript:window.close()"><strong><?php print str_replace("'","\\'", $yyClsWin)?></strong></a></p>';
	prnttext+='</body></html>';
	var scrwid=screen.width; var scrhei=screen.height;
	var newwin=window.open("","uploadimage",'menubar=no,scrollbars=yes,width='+winwid+',height='+winhei+',left='+((scrwid-winwid)/2)+',top=100,directories=no,location=no,resizable=yes,status=no,toolbar=no');
	newwin.document.open();
	newwin.document.write(prnttext);
	newwin.document.close();
	newwin.focus();
}
//-->
</script>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
		    <form method="post" action="adminsearchcriteria.php" onsubmit="return checkform(this)">
			<input type="hidden" name="group" value="<?php print getpost('group')?>" />
	<?php	if(getpost('act')=='modify'){ ?>
			<input type="hidden" name="act" value="domodify" />
	<?php	}else{ ?>
			<input type="hidden" name="act" value="doaddnew" />
	<?php	} ?>
			<input type="hidden" name="scID" value="<?php print $scID?>" />
			  <table width="100%" border="0" cellspacing="0" cellpadding="3">
				<tr>
				  <td width="100%" align="center" colspan="2"><strong><?php print $yySeaCri.' : Group - ' . $groupname?><br />&nbsp;</td>
				</tr>
				<tr>
				  <td align="right"><?php print $redasterix.$yyName?>:</td>
				  <td align="left"><input type="text" name="scname" size="30" value="<?php print htmlspecials($scaName[1])?>" /></td>
				</tr>
<?php		for($index=2; $index<=$adminlanguages+1; $index++){
				if(($adminlangsettings & 131072)==131072){ ?>
				<tr>
				  <td align="right"><?php print $redasterix.$yyName?> <?php print $index?></td>
				  <td align="left"><input type="text" name="scname<?php print $index?>" size="30" value="<?php print htmlspecials($scaName[$index])?>" />
				  </td>
				</tr>
<?php			}
			} ?>
				<tr>
				  <td align="right"><?php print $yyWrkNam?>:</td>
				  <td align="left"><input type="text" name="scworkingname" size="30" value="<?php print htmlspecials($scworkingname)?>" /></td>
				</tr>
				<tr>
				  <td align="right"><?php print $yyEmail?>:</td>
				  <td align="left"><input type="text" name="scemail" size="25" value="<?php print htmlspecials($scEmail)?>" /></td>
				</tr>
				<tr>
				  <td align="right">Attribute Logo:</td>
				  <td align="left"><input type="text" name="sclogo" id="sclogo" size="30" value="<?php print htmlspecials($scLogo)?>" /> <input type="button" name="smallimup" value="..." onclick="uploadimage('sclogo')" /></td>
				</tr>
<?php		if(getpost("act")=="modify"){
				$sSQL="SELECT scDescription FROM searchcriteria WHERE scID=".$scID;
				$result=ect_query($sSQL) or ect_error();
				$rs=ect_fetch_assoc($result);
				$scDescription=$rs['scDescription'];
				ect_free_result($result);
			}else
				$scDescription="";
?>
				<tr>
				  <td align="right">Description</td>
				  <td align="left"><textarea name="scDescription" id="scDescription" cols="38" rows="8" wrap=virtual><?php print htmlspecials($scDescription)?></textarea></td>
				</tr>
<?php		for($index=2; $index<=$adminlanguages+1; $index++){
				if(($adminlangsettings & 16384)==16384){
					if(getpost("act")=="modify"){
						$sSQL="SELECT scDescription".$index." FROM searchcriteria WHERE scID=".$scID;
						$result=ect_query($sSQL) or ect_error();
						while($rs=ect_fetch_assoc($result))
							$scDescription=$rs['scDescription'.$index];
						ect_free_result($result);
					}else
						$scDescription="";
?>
				<tr>
				  <td align="right">Description <?php print $index?></td>
				  <td align="left"><textarea name="scDescription<?php print $index?>" id="scDescription<?php print $index?>" cols="38" rows="8"><?php print htmlspecials($scDescription)?></textarea></td>
				</tr>
<?php			}
			} ?>
				<tr>
				  <td align="right">Static Page URL (Optional)</td>
				  <td align="left"><input type="text" name="scurl" size="30" value="<?php print htmlspecials($scURL[1])?>" /></td>
				</tr>
<?php		for($index=2; $index<=$adminlanguages+1; $index++){
				if(($adminlangsettings & 8192)==8192){ ?>
				<tr>
				  <td align="right">Static Page URL <?php print $index?> (Optional)</td>
				  <td align="left"><input type="text" name="scurl<?php print $index?>" size="30" value="<?php print htmlspecials($scURL[$index])?>" /></td>
				</tr>
<?php			}
			} ?>
			<tr id="sectionHeadTR">
				<td align="right">Attribute Header:</td><td><textarea name="scHeader" id="scHeader" cols="48" rows="8"><?php print $scHeader?></textarea></td>
			</tr>
<?php	for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 524288)==524288){ ?>
			  <tr id="sectionHeadTR<?php print $index?>">
				<td align="right"><?php print 'Attribute Header' . " " . $index?>:</td>
                <td><textarea name="scHeader<?php print $index?>" id="scHeader<?php print $index?>" cols="55" rows="8"><?php print htmlspecials($scHeaders[$index])?></textarea></td>
			  </tr>
<?php		}
		} ?>
				<tr>
				  <td align="right">Notes:</td>
				  <td align="left"><textarea name="scnotes" cols="38" rows="8" wrap=virtual><?php print htmlspecials($scNotes)?></textarea></td>
				</tr>
				<tr>
				  <td align="center" colspan="2"><input type="submit" value="<?php print $yySubmit?>" /> <input type="reset" value="<?php print $yyReset?>" /> </td>
				</tr>
				<tr><td align="center" colspan="2">&nbsp;</td></tr>
			  </table>
			</form>
		  </td>
        </tr>
      </table>
<?php
	print '<script type="text/javascript">';
	if(@$htmleditor=='ckeditor'){
		if(@$pathtossl!='' && (@$_SERVER['HTTPS']=='on' || @$_SERVER['SERVER_PORT']=='443')){
			if(substr($pathtossl,-1) != "/") $storeurl=$pathtossl . "/"; else $storeurl=$pathtossl;
		}
		$pathtovsadmin=dirname(@$_SERVER['PHP_SELF']);
		print 'function loadeditors(){';
		$streditor="var scDescription=CKEDITOR.replace('scDescription',{extraPlugins : 'stylesheetparser,autogrow',autoGrow_maxHeight : 800,removePlugins : 'resize', toolbarStartupExpanded : false, toolbar : 'Basic', filebrowserBrowseUrl :'ckeditor/filemanager/browser/default/browser.html?Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserImageBrowseUrl : 'ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserFlashBrowseUrl :'ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=File',filebrowserImageUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=Image',filebrowserFlashUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=Flash'});\r\n";
		$streditor.="scDescription.on('instanceReady',function(event){var myToolbar='Basic';event.editor.on( 'beforeMaximize', function(){if(event.editor.getCommand('maximize').state==CKEDITOR.TRISTATE_ON && myToolbar != 'Basic'){scDescription.setToolbar('Basic');myToolbar='Basic';scDescription.execCommand('toolbarCollapse');}else if(event.editor.getCommand('maximize').state==CKEDITOR.TRISTATE_OFF && myToolbar != 'Full'){scDescription.setToolbar('Full');myToolbar='Full';scDescription.execCommand('toolbarCollapse');}});event.editor.on('contentDom', function(e){event.editor.document.on('blur', function(){if(!scDescription.isToolbarCollapsed){scDescription.execCommand('toolbarCollapse');scDescription.isToolbarCollapsed=true;}});event.editor.document.on('focus',function(){if(scDescription.isToolbarCollapsed){scDescription.execCommand('toolbarCollapse');scDescription.isToolbarCollapsed=false;}});});scDescription.fire('contentDom');scDescription.isToolbarCollapsed=true;});\r\n";
		print $streditor;
		if(getpost("group")=='0') print str_replace('scDescription', 'scHeader', $streditor);
		for($index=2; $index <= $adminlanguages+1; $index++){
			if(getpost("group")=='0' && ($adminlangsettings & 524288)==524288) print str_replace('scDescription', 'scHeader' . $index, $streditor);
			if(($adminlangsettings & 16384)==16384) print str_replace('scDescription', 'scDescription' . $index, $streditor);
		}
		print '}window.onload=function(){loadeditors();}';
	}
	print '</script>';
}elseif(getpost("act")=="modifygroup"){
	$scID=getpost("id");
	$sSQL="SELECT scgID,scgTitle,scgTitle2,scgTitle3,scgWorkingName FROM searchcriteriagroup WHERE scgID=".$scID;
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		$scworkingname=$rs['scgWorkingName'];
		for($index=1; $index<=3; $index++){
			$scagName[$index]=$rs['scgTitle'.($index==1?'':$index)];
		}
	}
	ect_free_result($result);
?>
<script type="text/javascript">
<!--
function checkform(frm){
if(frm.scname.value==""){
	alert("<?php print jscheck($yyPlsEntr.' "'.$yyName)?>\".");
	frm.scname.focus();
	return(false);
}
<?php		for($index=2; $index<=$adminlanguages+1; $index++){
				if(($adminlangsettings & 131072)==131072){ ?>
if(frm.scname<?php print $index?>.value==""){
	alert("<?php print jscheck($yyPlsEntr.' "'.$yyName." ".$index)?>\".");
	frm.scname<?php print $index?>.focus();
	return(false);
}
<?php			}
			} ?>
return (true);
}
//-->
</script>
<h2><?php print $yyAdmSeC?></h2>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
		    <form method="post" action="adminsearchcriteria.php" onsubmit="return checkform(this)">
	<?php	if(getpost("act")=="modifygroup"){ ?>
			<input type="hidden" name="act" value="domodifygroup" />
	<?php	}else{ ?>
			<input type="hidden" name="act" value="doaddnewgroup" />
	<?php	} ?>
			<input type="hidden" name="scID" value="<?php print $scID?>" />
			  <table width="100%" border="0" cellspacing="0" cellpadding="3">
				<tr>
				  <td width="100%" align="center" colspan="2"><strong><?php print $yyPrAtGr?></strong><br />&nbsp;</td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $redasterix.$yyName?>:</strong></td>
				  <td align="left"><input type="text" name="scname" size="30" value="<?php print $scagName[1]?>" /></td>
				</tr>
<?php		for($index=2; $index<=$adminlanguages+1; $index++){
				if(($adminlangsettings & 131072)==131072){ ?>
				<tr>
				  <td align="right"><strong><?php print $redasterix.$yyName?> <?php print $index?></strong></td>
				  <td align="left"><input type="text" name="scname<?php print $index?>" size="30" value="<?php print htmlspecials($scagName[$index])?>" />
				  </td>
				</tr>
<?php			}
			} ?>
				  <td width="50%" align="right"><strong><?php print $yyWrkNam?>:</strong></td>
				  <td align="left"><input type="text" name="scworkingname" size="30" value="<?php print $scworkingname?>" /></td>
				</tr>
				<tr><td align="center" colspan="2">&nbsp;</td></tr>
				<tr>
				  <td align="center" colspan="2"><input type="submit" value="<?php print $yySubmit?>" /> <input type="reset" value="<?php print $yyReset?>" /> </td>
				</tr>
				<tr><td align="center" colspan="2">&nbsp;</td></tr>
			  </table>
			</form>
		  </td>
        </tr>
      </table>
<?php
}elseif(getpost('act')=='discounts'){
		$sSQL="SELECT scWorkingName FROM searchcriteria WHERE scID=" . getpost('id');
		$result=ect_query($sSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		$thisname=$rs['scWorkingName'];
		ect_free_result($result);
		$numassigns=0;
		$sSQL="SELECT cpaID,cpaCpnID,cpnWorkingName,cpnSitewide,cpnEndDate,cpnType FROM cpnassign LEFT JOIN coupons ON cpnassign.cpaCpnID=coupons.cpnID WHERE cpaType=3 AND cpaAssignment='" . getpost('id') . "'";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result))
			$alldata[$numassigns++]=$rs;
		ect_free_result($result);
		$numcoupons=0;
		$sSQL="SELECT cpnID,cpnWorkingName,cpnSitewide FROM coupons WHERE (cpnSitewide=0 OR cpnSitewide=3) AND cpnEndDate >='" . date("Y-m-d",time()) ."'";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result))
			$alldata2[$numcoupons++]=$rs;
		ect_free_result($result);
?>
<script type="text/javascript">
/* <![CDATA[ */
function delrec(id){
if(confirm("<?php print jscheck($yyConAss)?>\n")) {
	document.mainform.id.value=id;
	document.mainform.act.value="deletedisc";
	document.mainform.submit();
}
}
/* ]]> */
</script>
		  <form name="mainform" method="post" action="adminsearchcriteria.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="dodiscounts" />
			<input type="hidden" name="id" value="<?php print getpost('id')?>" />
			<input type="hidden" name="pg" value="<?php print getpost('pg')?>" />
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="4" align="center"><strong>Assign Discounts to Attribute &quot;<?php print $thisname?>&quot;.</strong><br />&nbsp;</td>
			  </tr>
<?php
	$gotone=FALSE;
	if($numcoupons>0){
		$thestr='<tr><td colspan="4" align="center">' . $yyAsDsCp . ': <select name="assdisc" size="1">';
		for($index=0;$index < $numcoupons;$index++){
			$alreadyassign=FALSE;
			if($numassigns>0){
				for($index2=0;$index2<$numassigns;$index2++){
					if($alldata2[$index]["cpnID"]==$alldata[$index2]["cpaCpnID"]) $alreadyassign=TRUE;
				}
			}
			if(! $alreadyassign){
				$thestr.="<option value='" . $alldata2[$index]["cpnID"] . "'>" . $alldata2[$index]["cpnWorkingName"] . "</option>\n";
				$gotone=TRUE;
			}
		}
		$thestr.='</select> <input type="submit" value="'.$yyGo.'" /></td></tr>';
	}
	if($gotone){
		print $thestr;
	}else{
?>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br /><strong><?php print $yyNoDis?></td>
			  </tr>
<?php
	}
	if($numassigns>0){
?>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br /><strong><?php print $yyCurDis?> &quot;<?php print $thisname?>&quot;.</strong><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td><strong><?php print $yyWrkNam?></strong></td>
				<td><strong><?php print $yyDisTyp?></strong></td>
				<td><strong><?php print $yyExpire?></strong></td>
				<td align="center"><strong><?php print $yyDelete?></strong></td>
			  </tr>
<?php
		for($index=0;$index<$numassigns;$index++){
			$prefont='';
			$postfont='';
			if((int)$alldata[$index]["cpnSitewide"]==1 || ($alldata[$index]["cpnEndDate"] != '3000-01-01 00:00:00' && strtotime($alldata[$index]["cpnEndDate"])-time() < 0)){
				$prefont='<span style="color:#FF0000">';
				$postfont='</span>';
			}
?>
			  <tr> 
                <td><?php	print $prefont . $alldata[$index]["cpnWorkingName"] . $postfont ?></td>
				<td><?php	if($alldata[$index]["cpnType"]==0)
								print $prefont . $yyFrSShp . $postfont;
							elseif($alldata[$index]["cpnType"]==1)
								print $prefont . $yyFlatDs . $postfont;
							elseif($alldata[$index]["cpnType"]==2)
								print $prefont . $yyPerDis . $postfont; ?></td>
				<td><?php	print $prefont;
							if($alldata[$index]["cpnEndDate"]=='3000-01-01 00:00:00')
								print $yyNever;
							elseif(strtotime($alldata[$index]["cpnEndDate"])-time() < 0)
								print $yyExpird;
							else
								print date("Y-m-d",strtotime($alldata[$index]["cpnEndDate"]));
							print $postfont; ?></td>
				<td align="center"><input type="button" name="discount" value="Delete Assignment" onclick="delrec('<?php print $alldata[$index]["cpaID"]?>')" /></td>
			  </tr>
<?php
		}
	}else{
?>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br /><strong><?php print $yyNoAss?></strong></td>
			  </tr>
<?php
	}
?>
			  <tr>
                <td width="100%" colspan="4" align="center"><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </form>
<?php
}elseif(getrequest("act")=="modifyatts" && is_numeric(getrequest("id"))){ ?>
<script type="text/javascript">
<!--
function popsel(x,theid,grpid){
	if(x.length>1) return;
	for(index=theid-1; index>0; index--){
		var y=document.createElement('option');
		y.text=index;
		y.value=index;
		var sel=x.options[0];
		try{
			x.add(y, sel); // FF etc
		}
		catch(ex){
			x.add(y, 0); // IE
		}
	}
	for(index=theid+1; index<=totrows; index++){
		var y=document.createElement('option');
		y.text=index;
		y.value=index;
		try{
			x.add(y, null); // FF etc
		}
		catch(ex){
			x.add(y); // IE
		}
	}
}
function chi(id,obj){
	document.mainform.action="adminsearchcriteria.php?act=modifyatts";
	document.mainform.newval.value=obj.selectedIndex+1;
	document.mainform.id.value=id;
	document.mainform.act.value="changepos";
	document.mainform.submit();
}
function sortalphabetically(){
	if(confirm("<?php print jscheck($yySureCa)?>")){
		document.mainform.action="adminsearchcriteria.php?act=modifyatts";
		document.mainform.newval.value=0;
		document.mainform.id.value=0;
		document.mainform.act.value = "changepos";
		document.mainform.alphabetically.value = "1";
		document.mainform.submit();
	}
}
function modrec(id){
	document.mainform.id.value=id;
	document.mainform.act.value="modify";
	document.mainform.submit();
}
function newrec(id){
	document.mainform.id.value=id;
	document.mainform.act.value="addnew";
	document.mainform.submit();
}
function dsc(id){
	document.mainform.action="adminsearchcriteria.php";
	document.mainform.id.value=id;
	document.mainform.act.value="discounts";
	document.mainform.submit();
}
function delrec(id){
if(confirm("<?php print jscheck($yyConDel)?>\n")){
	document.mainform.id.value=id;
	document.mainform.act.value="delete";
	document.mainform.submit();
}
}
function addnewcriteria(){
	if(document.getElementById('newname').value==''){
		alert("<?php print jscheck($yyPlsEntr.' "'.$yyName)?>\".");
		document.getElementById('newname').focus();
	}else{
		document.mainform.action="adminsearchcriteria.php?group=<?php print getrequest("id")?>";
		document.mainform.id.value='';
		document.mainform.act.value="newattribute";
		document.mainform.submit();
	}
}
// -->
</script>
<h2><?php print $yyAdmSeC?></h2>
<?php
	$sSQL="SELECT scgWorkingName FROM searchcriteriagroup WHERE scgID=".getrequest("id");
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $thegroupname=$rs['scgWorkingName']; else $thegroupname="";
	ect_free_result($result);
?>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%" align="center">
		  <div style="text-align:center"><span style="font-weight:bold">Modify Attributes for Group:</span> <?php print $thegroupname?><br />&nbsp;</div>
			<form name="mainform" method="post" action="adminsearchcriteria.php">
			<input type="hidden" name="id" value="xxx" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="selectedq" value="1" />
			<input type="hidden" name="newval" value="1" />
			<input type="hidden" name="alphabetically" value="" />
			<input type="hidden" name="group" value="<?php print getrequest("id")?>" />
			  <table width="80%" border="0" cellspacing="0" cellpadding="2">
				<tr>
				  <th width="5%">&nbsp;<strong><?php print $yyOrder?></strong></th>
				  <th width="10%"><strong><?php print $yyID?></strong></th>
				  <th align="left"><strong><?php print $yyWrkNam?></strong></th>
				  <th align="left"><strong><?php print $yyName?></strong></th>
				  <th class="minicell"><?php print $yyDiscnt?></th>
				  <th class="minicell"><?php print $yyModify?></th>
				  <th class="minicell"><?php print $yyDelete?></th>
				</tr>
<?php
	$allcoupon=array();
	$numcoupons=0;
	$sSQL='SELECT DISTINCT cpaAssignment FROM cpnassign WHERE cpaType=3';
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result))
		$allcoupon[$numcoupons++]=$rs['cpaAssignment'];
	ect_free_result($result);
	$rowcounter=1;
	$sSQL="SELECT scID,scWorkingName,scGroup,scName,scOrder FROM searchcriteria WHERE scGroup=".getrequest("id")." ORDER BY scOrder,scName";
	$result=ect_query($sSQL) or ect_error();
	$ordingroup=1;
	$rowsingrp="";
	$showalphabetbutton=FALSE;
	while($rs=ect_fetch_assoc($result)){
		if(@$bgcolor=='altdark') $bgcolor="altlight"; else $bgcolor="altdark";
		if($rs['scOrder']!=0) $showalphabetbutton=TRUE;
		$hascoupon=FALSE;
		foreach($allcoupon as $key=>$val){
			if($val==$rs['scID']){ $hascoupon=TRUE; break; }
		}
?>		<tr class="<?php print $bgcolor?>">
			<td>&nbsp;<?php
		print '<select name="newpos" onchange="chi('.$rs['scID'].',this)" onmouseover="popsel(this,'.$ordingroup.",".$rs['scGroup'].')">';
		print '<option value="" selected="selected">'.$ordingroup.($ordingroup<100?'&nbsp;':'').'</option>';
		print "</select>" ?></td>
			<td class="minicell"><?php print $rs['scID']?></td>
			<td align="left"><?php print $rs['scWorkingName']?>&nbsp;</td>
			<td align="left"><?php print $rs['scName']?></td>
			<td class="minicell"><input type="button" <?php print ($hascoupon?'style="color:#F4E64B" ':'')?>value="<?php print htmlspecials($yyAssign)?>" onclick="dsc(<?php print $rs['scID']?>)" /></td>
			<td class="minicell"><input type="button" value="<?php print $yyModify?>" onclick="modrec(<?php print $rs['scID']?>)" /></td>
			<td class="minicell"><input type="button" value="<?php print $yyDelete?>" onclick="delrec(<?php print $rs['scID']?>)" /></td>
		</tr><?php
		$rowcounter++;
		$ordingroup++;
	}
?>
				<tr class="<?php print $bgcolor?>">
				  <td colspan="2"><?php if($showalphabetbutton) print '<input type="button" value="Sort Alphabetically" onclick="sortalphabetically()" />'; else print '&nbsp;'; ?></td>
				  <td align="left"><input type="text" name="newwn" size="24" value="" />&nbsp;</td>
				  <td align="left"><input type="text" id="newname" name="newname" size="24" value="" placeholder="Attribute Name" />
<?php
	for($index=2; $index<=$adminlanguages+1; $index++){
		if(($adminlangsettings & 131072)==131072)
			print '<br /><input type="text" name="newname'.$index.'" value="" size="24" placeholder="Language '.$index.'" />';
	} ?>
				  </td>
				  <td colspan="2"><input type="button" value="<?php print $yyAddNew?>" onclick="addnewcriteria()" /></td>
				</tr>
				<tr> 
				  <td width="100%" colspan="6" align="center"><br /><input type="button" value="Back to Attribute Groups" onclick="document.location='adminsearchcriteria.php'" /><br />&nbsp;</td>
				</tr>
				<tr> 
				  <td width="100%" colspan="6" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
				</tr>
			  </table>
			</form>
<script type="text/javascript">
/* <![CDATA[ */
var totrows=<?php print $rowcounter-1?>
/* ]]> */
</script>
		  </td>
        </tr>
      </table>
<?php
}else{ ?>
<script type="text/javascript">
<!--
function popsel(x,theid,grpid){
	if(x.length>1) return;
	for(index=theid-1; index>0; index--){
		var y=document.createElement('option');
		y.text=index;
		y.value=index;
		var sel=x.options[0];
		try{
			x.add(y, sel); // FF etc
		}
		catch(ex){
			x.add(y, 0); // IE
		}
	}
	for(index=theid+1; index<=totrows; index++){
		var y=document.createElement('option');
		y.text=index;
		y.value=index;
		try{
			x.add(y, null); // FF etc
		}
		catch(ex){
			x.add(y); // IE
		}
	}
}
function chi(id,obj){
	document.mainform.action="adminsearchcriteria.php";
	document.mainform.newval.value=obj.selectedIndex+1;
	document.mainform.id.value=id;
	document.mainform.act.value="changepos";
	document.mainform.submit();
}
function modrec(id) {
	document.mainform.id.value=id;
	document.mainform.act.value="modifygroup";
	document.mainform.submit();
}
function modatts(id) {
	document.mainform.id.value=id;
	document.mainform.act.value="modifyatts";
	document.mainform.submit();
}
function newrec(id) {
	document.mainform.id.value=id;
	document.mainform.act.value="addnewgroup";
	document.mainform.submit();
}
function delrec(id){
if(confirm("<?php print jscheck($yyConDel)?>\n")){
	document.mainform.id.value=id;
	document.mainform.act.value="deletegroup";
	document.mainform.submit();
}
}
function addnewgroup(){
	if(document.getElementById('newname').value==''){
		alert("<?php print jscheck($yyPlsEntr.' "'.$yyName)?>\".");
		document.getElementById('newname').focus();
	}else{
		document.mainform.action="adminsearchcriteria.php";
		document.mainform.id.value='';
		document.mainform.act.value="newgroup";
		document.mainform.submit();
	}
}
// -->
</script>
<h2><?php print $yyAdmSeC?></h2>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%" align="center"><?php
	$sSQL="SELECT COUNT(*) as countpid FROM (multisearchcriteria LEFT JOIN products ON multisearchcriteria.mSCpID=products.pID) LEFT JOIN searchcriteria ON multisearchcriteria.mSCscID=searchcriteria.scID WHERE pID IS NULL OR scID IS NULL";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $numberorphans=$rs['countpid']; else $numberorphans=0;
	ect_free_result($result);
	if($numberorphans>0) print '<div style="text-align:center;color:red;margin-bottom:10px"><input type="button" onclick="document.location=\'adminsearchcriteria.php?act=repair\'" value="There are ".$numberorphans." orphaned entries in the Product Attributes database table. Please click here to repair this" /></div>';
?>
			<form name="mainform" method="post" action="adminsearchcriteria.php">
			<input type="hidden" name="id" value="xxx" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="selectedq" value="1" />
			<input type="hidden" name="newval" value="1" />
			  <table width="80%" border="0" cellspacing="0" cellpadding="2">
				<tr>
				  <td width="5%"><strong><?php print $yyOrder?></strong></td>
				  <td width="10%"><strong><?php print $yyID?></strong></td>
				  <td align="left"><strong><?php print $yyWrkNam?></strong></td>
				  <td align="left"><strong>Attribute Group <?php print $yyName?></strong></td>
				  <td width="14%"><strong><?php print $yyModify." ".$yyGroup?></strong></td>
				  <td width="15%"><strong><?php print $yyModify." Attributes"?></strong></td>
				  <td width="10%"><strong><?php print $yyDelete?></strong></td>
				</tr>
<?php
	$rowcounter=1;
	$sSQL='SELECT scgID,scgWorkingName,scgTitle FROM searchcriteriagroup ORDER BY scgOrder';
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		$sSQL="SELECT COUNT(*) AS thecount FROM searchcriteria WHERE scGroup=".$rs['scgID'];
		$result2=ect_query($sSQL) or ect_error();
		if($rs2=ect_fetch_assoc($result2)) $numcriteria=$rs2['thecount']; else $numcriteria=0;
		ect_free_result($result2);
		if(@$bgcolor=="altdark") $bgcolor="altlight"; else $bgcolor="altdark"; ?>
			<tr class="<?php print $bgcolor?>">
			  <td><?php
				print '<select name="newpos" onchange="chi('.$rs['scgID'].',this)" onmouseover="popsel(this,'.$rowcounter.')">';
				print '<option value="" selected="selected">'.$rowcounter.($rowcounter<100?'&nbsp;':"")."</option>";
				print '</select>' ?></td>
			  <td><?php print $rs['scgID']?></td>
			  <td align="left"><?php print $rs['scgWorkingName']?></td>
			  <td align="left"><?php print $rs['scgTitle']?>&nbsp;</td>
			  <td><input type="button" value="<?php print $yyModify." ".$yyGroup?>" onclick="modrec('<?php print $rs['scgID']?>')" /></td>
			  <td><input type="button" value="<?php print $yyModify." Attributes (".$numcriteria.")"?>" onclick="modatts('<?php print $rs['scgID']?>')" /></td>
			  <td><input type="button" value="<?php print $yyDelete?>" onclick="delrec('<?php print $rs['scgID']?>')" /></td>
			</tr>
<?php	$rowcounter++;
	}
?>
				<tr class="<?php print $bgcolor?>">
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				  <td align="left"><input type="text" name="newwn" size="24" value="" />&nbsp;</td>
				  <td align="left"><input type="text" id="newname" name="newname" size="24" value="" placeholder="Group Name" />
<?php
	for($index=2; $index<=$adminlanguages+1; $index++){
		if(($adminlangsettings & 131072)==131072)
			print '<br /><input type="text" name="newname'.$index.'" value="" size="24" placeholder="Language '.$index.'" />';
	} ?>
				  </td>
				  <td colspan="3"><input type="button" value="<?php print $yyAddNew?>" onclick="addnewgroup()" /></td>
				</tr>
				<tr> 
				  <td width="100%" colspan="7" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
				</tr>
			  </table>
			</form>
<script type="text/javascript">
/* <![CDATA[ */
var totrows=<?php print $rowcounter-1?>
/* ]]> */
</script>
		  </td>
        </tr>
      </table>
<?php
}
?>
