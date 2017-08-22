<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
$success=TRUE;
if(@$admincatsperpage=='')$admincatsperpage=200;
if(@$maxloginlevels=='') $maxloginlevels=5;
$dorefresh=FALSE;
if(strtolower($adminencoding)=='iso-8859-1') $raquo='»'; else $raquo='>';
function writemenulevel($id,$itlevel){
	global $allcatsa,$numcats,$thecat,$raquo;
	if($itlevel<10){
		for($wmlindex=0; $wmlindex < $numcats; $wmlindex++){
			if($allcatsa[$wmlindex]['topSection']==$id){
				print "<option value='" . $allcatsa[$wmlindex]['sectionID'] . "'";
				if($thecat==$allcatsa[$wmlindex]['sectionID']) print ' selected="selected">'; else print ">";
				for($index=0; $index < $itlevel-1; $index++)
					print $raquo . ' ';
				print $allcatsa[$wmlindex]['sectionWorkingName'] . "</option>\n";
				if($allcatsa[$wmlindex]['rootSection']==0) writemenulevel($allcatsa[$wmlindex]['sectionID'],$itlevel+1);
			}
		}
	}
}
$sSQL='';
$alldata='';
$alreadygotadmin=getadminsettings();
if(@$defaultcatimages=='') $defaultcatimages='images/';
if(getpost('act')=='changepos'){
	$theid=(int)getpost('id');
	$neworder=((int)getpost('newval'))-1;
	$sSQL="SELECT sectionOrder,topSection FROM sections WHERE sectionID=" . $theid;
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $topsection=$rs['topSection'];
	ect_free_result($result);
	$rc=0;
	if(@$menucategoriesatroot && $catalogroot!=0){
		$sSQL="SELECT sectionID,topSection FROM sections WHERE (sectionID=".$topsection." OR topSection=".$topsection.") AND sectionID=".$catalogroot;
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)) $topsection=$rs['sectionID'].','.$rs['topSection'];
		ect_free_result($result);
	}
	$sSQL='SELECT sectionID,sectionOrder FROM sections WHERE topSection IN ('.$topsection.') ORDER BY sectionOrder';
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		if($rs['sectionID']==$theid)
			$sSQL="UPDATE sections SET sectionOrder=".$neworder." WHERE sectionID=".$theid;
		else
			$sSQL="UPDATE sections SET sectionOrder=".($rc<$neworder?$rc:$rc+1)." WHERE sectionID=".$rs['sectionID'];
		ect_query($sSQL) or ect_error();
		$rc++;
	}
	ect_free_result($result);
	$dorefresh=TRUE;
}elseif(getpost('posted')=='1'){
	if(getpost('act')=='delete'){
		$sSQL="DELETE FROM cpnassign WHERE cpaType=1 AND cpaAssignment='" . getpost('id') . "'";
		ect_query($sSQL) or ect_error();
		$sSQL="DELETE FROM sections WHERE sectionID=" . getpost('id');
		ect_query($sSQL) or ect_error();
		$sSQL="DELETE FROM multisections WHERE pSection=" . getpost('id');
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}elseif(getpost('act')=='domodify'){
		$olddisabled=0;
		$sSQL="SELECT sectionDisabled FROM sections WHERE sectionID=" . getpost('id');
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$olddisabled=$rs['sectionDisabled'];
		}
		ect_free_result($result);
		$sSQL="UPDATE sections SET sectionName='" . escape_string(getpost('secname')) . "',sectionDescription='" . escape_string(getpost('secdesc')) . "',sectionImage='" . escape_string(getpost('secimage')) . "',topSection=" . getpost('tsTopSection') . ",rootSection=" . getpost('catfunction');
		$workname=escape_string(getpost('secworkname'));
		if($workname!='')
			$sSQL.=",sectionWorkingName='" . $workname . "'";
		else
			$sSQL.=",sectionWorkingName='" . escape_string(getpost('secname')) . "'";
		for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 256)==256) $sSQL.=",sectionName" . $index . "='" . escape_string(getpost('secname' . $index)) . "'";
			if(($adminlangsettings & 512)==512) $sSQL.=",sectionDescription" . $index . "='" . escape_string(getpost('secdesc' . $index)) . "'";
			if(($adminlangsettings & 2048)==2048) $sSQL.=',sectionurl' . $index . "='" . escape_string(getpost('sectionurl' . $index)) . "'";
		}
		$sSQL.=",sectionDisabled=" . getpost('sectionDisabled');
		$sSQL.=",sectionHeader='" . escape_string(getpost('sectionHeader')) . "'";
		for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 524288)==524288) $sSQL.=",sectionHeader" . $index . "='" . escape_string(getpost('sectionHeader' . $index)) . "'";
		}
		$sSQL.=",sectionurl='" . escape_string(getpost('sectionurl')) . "',sTitle='" . escape_string(getpost('sTitle')) . "',sMetaDesc='" . escape_string(getpost('sMetaDesc')) . "'";
		$sSQL.=' WHERE sectionID=' . getpost('id');
		ect_query($sSQL) or ect_error();
		if(getpost('catalogroot')=='ON'){
			if($catalogroot!=(int)getpost('id'))
				ect_query('UPDATE admin SET catalogRoot='.getpost('id').' WHERE adminID=1') or ect_error();
		}else{
			if($catalogroot==(int)getpost('id'))
				ect_query('UPDATE admin SET catalogRoot=0 WHERE adminID=1') or ect_error();
		}
		if(($olddisabled!=(int)getpost('sectionDisabled') || getpost('forcesubsection')=='1') && getpost('forcesubsection')!='2'){
			$idlist=getpost('id');
			ect_query('UPDATE sections SET sectionDisabled=' . getpost('sectionDisabled') . ' WHERE topSection=' . $idlist) or ect_error();
			for($index=1; $index<=10; $index++){
				$sSQL='SELECT sectionID,sectionDisabled,rootSection FROM sections WHERE rootSection=0 AND topSection IN (' . $idlist . ')';
				$idlist='';
				$result=ect_query($sSQL) or ect_error();
				while($rs=ect_fetch_assoc($result)){
					$sSQL='UPDATE sections SET sectionDisabled=' . getpost('sectionDisabled') . ' WHERE topSection=' . $rs['sectionID'];
					ect_query($sSQL) or ect_error();
					$idlist.=$rs['sectionID'].',';
				}
				ect_free_result($result);
				if($idlist!='') $idlist=substr($idlist,0,-1); else break;
			}
		}
		$dorefresh=TRUE;
	}elseif(getpost('act')=="doaddnew"){
		$haveuniqueindex=FALSE;
		$uniqueindex=1;
		while(!$haveuniqueindex){
			$result=ect_query("SELECT sectionID FROM sections WHERE sectionID=".$uniqueindex) or ect_error();
			if(ect_num_rows($result)==0) $haveuniqueindex=TRUE; else $uniqueindex++;
			ect_free_result($result);
		}
		$sSQL="SELECT MAX(sectionOrder) AS mxOrder FROM sections";
		$result=ect_query($sSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		$mxOrder=$rs["mxOrder"];
		if(is_null($mxOrder) || $mxOrder=="") $mxOrder=1; else $mxOrder++;
		ect_free_result($result);
		$sSQL="INSERT INTO sections (sectionID,sectionName,sectionName2,sectionName3,sectionDescription,sectionDescription2,sectionDescription3,sectionImage,sectionOrder,topSection,rootSection,sectionWorkingName";
		$sSQL.=',sectionDisabled,sectionHeader,sectionHeader2,sectionHeader3';
		$sSQL.=',sectionurl,sectionurl2,sectionurl3,sTitle,sMetaDesc) VALUES ('.$uniqueindex.",'" . escape_string(getpost('secname')) . "','" . escape_string(getpost('secname2')) . "','" . escape_string(getpost('secname3')) . "','" . escape_string(getpost('secdesc')) . "','" . escape_string(getpost('secdesc2')) . "','" . escape_string(getpost('secdesc3')) . "','" . escape_string(getpost('secimage')) . "'," . $mxOrder . "," . getpost('tsTopSection') . "," . getpost('catfunction');
		$workname=escape_string(getpost('secworkname'));
		if($workname!='')
			$sSQL.=",'" . $workname . "'";
		else
			$sSQL.=",'" . escape_string(getpost('secname')) . "'";
		$sSQL.=',' . getpost('sectionDisabled');
		$sSQL.=",'" . escape_string(getpost('sectionHeader')) . "','" . escape_string(getpost('sectionHeader2')) . "','" . escape_string(getpost('sectionHeader3')) . "'";
		$sSQL.=",'" . escape_string(getpost('sectionurl')) . "','" . escape_string(getpost('sectionurl2')) . "','" . escape_string(getpost('sectionurl3')) . "','" . escape_string(getpost('sTitle')) . "','" . escape_string(getpost('sMetaDesc')) . "')";
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}elseif(getpost('act')=='dodiscounts'){
		$sSQL='INSERT INTO cpnassign (cpaCpnID,cpaType,cpaAssignment) VALUES (' . getpost('assdisc') . ",1,'" . getpost('id') . "')";
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}elseif(getpost('act')=='deletedisc'){
		$sSQL='DELETE FROM cpnassign WHERE cpaType=1 AND cpaID=' . getpost('id');
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}elseif(getpost('act')=='sort'){
		setcookie('catsort', getpost('sort'), time()+31536000, '/', '', @$_SERVER['HTTPS']=='on');
	}
}elseif(getget('catorman')!=''){
	setcookie('ccatorman', getget('catorman'), time()+80000000, '/', '', @$_SERVER['HTTPS']=='on');
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="'.(getpost('act')=='changepos'?0:1).'; url=admincats.php';
	print '?stext=' . urlencode(@$_REQUEST['stext']) . '&catfun=' . @$_REQUEST['catfun'] . '&stype=' . @$_REQUEST['stype'] . '&scat=' . @$_REQUEST['scat'] . '&pg=' . @$_REQUEST['pg'];
	print '" />' . "\r\n";
}else{
?>
<script type="text/javascript">
/* <![CDATA[ */
function formvalidator(theForm){
  if(theForm.secname.value==""){
    alert("<?php print jscheck($yyPlsEntr . ' "' . $yyCatNam)?>\".");
    theForm.secname.focus();
    return (false);
  }
  if(theForm.tsTopSection[theForm.tsTopSection.selectedIndex].value==""){
    alert("<?php print jscheck($yyPlsSel . ' "' . $yyCatSub)?>\".");
    theForm.tsTopSection.focus();
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
function checkheaders(tmen){
	if(tmen.selectedIndex=='1'){
		if(document.getElementById('sectionHeadTR')) document.getElementById('sectionHeadTR').style.display='none';
		if(document.getElementById('sectionHeadTR2')) document.getElementById('sectionHeadTR2').style.display='none';
		if(document.getElementById('sectionHeadTR3')) document.getElementById('sectionHeadTR3').style.display='none';
	}else{
		if(document.getElementById('sectionHeadTR')) document.getElementById('sectionHeadTR').style.display='';
		if(document.getElementById('sectionHeadTR2')) document.getElementById('sectionHeadTR2').style.display='';
		if(document.getElementById('sectionHeadTR3')) document.getElementById('sectionHeadTR3').style.display='';
	}
}
/* ]]> */
</script>
<?php
}
if(getpost('posted')=='1' && (getpost('act')=='modify' || getpost('act')=='addnew' || getpost('act')=='clone')){
		if(@$htmleditor=='ckeditor'){ ?>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<?php	}elseif(@$htmleditor=='fckeditor'){ ?>
<script type="text/javascript" src="fckeditor.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
function FCKeditor_OnComplete(editorInstance){
	editorInstance.Events.AttachEvent('OnBlur', FCKeditor_OnBlur);
	editorInstance.Events.AttachEvent('OnFocus', FCKeditor_OnFocus);
	editorInstance.ToolbarSet.Collapse();
}
function FCKeditor_OnBlur(editorInstance){
	editorInstance.ToolbarSet.Collapse();
}
function FCKeditor_OnFocus(editorInstance){
	editorInstance.ToolbarSet.Expand();
}
var sBasePath=document.location.pathname.substring(0,document.location.pathname.lastIndexOf('admincats.php'));
/* ]]> */
</script>
<?php	}
		$ntopsections=0;
		$sectionID='';
		$sectionName='';
		$sectionDescription='';
		for($index=2; $index <= $adminlanguages+1; $index++){
			$sectionNames[$index]='';
			$sectionDescriptions[$index]='';
			$sectionurls[$index]='';
		}
		$sectionImage='';
		$sectionWorkingName='';
		$topSection=0;
		$sectionDisabled=0;
		$rootSection=1;
		$sectionurl='';
		$sTitle='';
		$sMetaDesc='';
		$sectionHeader='';
		$sSQL="SELECT sectionID, sectionWorkingName FROM sections WHERE rootSection=0 ORDER BY sectionWorkingName";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result))
			$alltopsections[$ntopsections++]=$rs;
		ect_free_result($result);
		if((getpost('act')=='modify' || getpost('act')=='clone') && is_numeric(getpost('id'))){
			$sSQL="SELECT sectionID,sectionName,sectionName2,sectionName3,sectionDescription,sectionDescription2,sectionDescription3,sectionImage,sectionWorkingName,topSection,sectionDisabled,rootSection,sectionurl";
			if(($adminlangsettings & 2048)==2048){
				if($adminlanguages>=1) $sSQL.=',sectionurl2';
				if($adminlanguages>=2) $sSQL.=',sectionurl3';
			}
			$sSQL.=',sTitle,sMetaDesc,sectionHeader,sectionHeader2,sectionHeader3 FROM sections WHERE sectionID=' . getpost('id');
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				$sectionID=$rs['sectionID'];
				$sectionName=$rs['sectionName'];
				$sectionDescription=$rs['sectionDescription'];
				for($index=2; $index <= $adminlanguages+1; $index++){
					$sectionNames[$index]=$rs['sectionName' . $index];
					$sectionDescriptions[$index]=$rs['sectionDescription' . $index];
					if(($adminlangsettings & 2048)==2048) $sectionurls[$index]=$rs['sectionurl' . $index];
				}
				$sectionImage=$rs['sectionImage'];
				$sectionWorkingName=$rs['sectionWorkingName'];
				$topSection=$rs['topSection'];
				$sectionDisabled=$rs['sectionDisabled'];
				$rootSection=$rs['rootSection'];
				$sectionurl=$rs['sectionurl'];
				$sectionHeader=$rs['sectionHeader'];
				$sTitle=$rs['sTitle'];
				$sMetaDesc=$rs['sMetaDesc'];
				for($index=2; $index<=$adminlanguages+1; $index++){
					$sectionHeaders[$index]=$rs['sectionHeader' . $index];
				}
			}
			ect_free_result($result);
		}else{
			for($index=2; $index<=$adminlanguages+1; $index++){
				$sectionHeaders[$index]='';
			}
		}
?>
		  <form name="mainform" method="post" action="admincats.php" onsubmit="return formvalidator(this)">
			<input type="hidden" name="posted" value="1" />
			<?php if(getpost('act')=='modify'){ ?>
			<input type="hidden" name="act" value="domodify" />
			<?php }else{ ?>
			<input type="hidden" name="act" value="doaddnew" />
			<?php }
			writehiddenvar('stext', getpost('stext'));
			writehiddenvar('stype', getpost('stype'));
			writehiddenvar('catfun', getpost('catfun'));
			writehiddenvar('scat', getpost('scat'));
			writehiddenvar('pg', getpost('pg'));
			writehiddenvar('id', getpost('id')); ?>
            <table width="100%" border="0" cellspacing="2" cellpadding="3">
			  <tr>
                <td width="100%" colspan="2" align="center"><strong><?php print (getpost('act')=='clone'?$yyClone.': ':(getpost('act')=='modify'?$yyModify.': ':'')) . $yyCatAdm?></strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td align="right"><?php print $redasterix . $yyCatNam?>:</td><td><input type="text" name="secname" size="30" value="<?php print htmlspecialsucode($sectionName)?>" /></td>
			  </tr>
<?php	for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 256)==256){ ?>
			  <tr>
				<td align="right"><?php print $yyCatNam . " " . $index ?>:</td>
				<td><input type="text" name="secname<?php print $index?>" size="30" value="<?php print htmlspecialsucode($sectionNames[$index])?>" /></td>
			  </tr>
<?php		}
		} ?>
			<tr>
				<td align="right"><?php print $yyCatWrNa?>:</td>
				<td><input type="text" name="secworkname" size="30" value="<?php print htmlspecialsucode($sectionWorkingName)?>" /></td>
			</tr>
			<tr>
				<td align="right"><?php print $yyCatSub?>:</td>
				<td><select name="tsTopSection" size="1"><option value="0"><?php print $yyCatHom?></option>
				<?php	$foundcat=($topSection==0);
						for($index=0;$index<$ntopsections; $index++){
							if($alltopsections[$index]["sectionID"] != $sectionID){
								print '<option value="' . $alltopsections[$index]["sectionID"] . '"';
								if($topSection==$alltopsections[$index]["sectionID"]){
									print ' selected="selected"';
									$foundcat=TRUE;
								}
								print ">" . $alltopsections[$index]["sectionWorkingName"] . "</option>\n";
							}
						}
						if(! $foundcat) print '<option value="" selected="selected">**undefined**</option>';
					?></select>
                </td>
			</tr>
			<tr>
				<td align="right"><?php print $yyCatFn?>:</td>
				<td><select name="catfunction" id="catfunction" size="1" onchange="checkheaders(this)">
				  <option value="1"><?php print $yyCatPrd?></option>
				  <option value="0" <?php if($rootSection==0) print 'selected="selected"'?>><?php print $yyCatCat?></option>
				  </select></td>
			</tr>
			<tr>
				<td align="right"><?php print $yyCatImg?>:</td>
				<td><input type="text" name="secimage" id="secimage" size="30" value="<?php print str_replace("\"","&quot;",$sectionImage)?>" /> <input type="button" name="smallimup" value="..." onclick="uploadimage('secimage')" /></td>
			</tr>
			<tr>
				<td align="right"><?php print $yyCatDes?>:</td><td><textarea name="secdesc" id="sectionDescription" cols="48" rows="8"><?php print $sectionDescription?></textarea></td>
			</tr>
<?php	for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 512)==512){ ?>
			  <tr>
				<td align="right"><?php print $yyCatDes . " " . $index ?>:</td>
				<td><textarea name="secdesc<?php print $index?>" id="sectionDescription<?php print $index?>" cols="48" rows="8"><?php print $sectionDescriptions[$index]?></textarea></td>
			  </tr>
<?php		}
		} ?>
			  <tr>
				<td align="right">Restrictions:</td>
				<td><select name="sectionDisabled" size="1">
				<option value="0"><?php print $yyNoRes?></option>
<?php	for($index=1; $index<= $maxloginlevels; $index++){
						print '<option value="' . $index . '"';
						if($sectionDisabled==$index) print ' selected="selected"';
						print '>' . $yyLiLev . ' ' . $index . '</option>';
		} ?>
				<option value="127"<?php if($sectionDisabled==127) print ' selected="selected"'?>><?php print $yyDisCat?></option>
				</select>
<?php	if(getpost('act')=='modify'){ ?>
				<select name="forcesubsection" size="1">
				<option value="0"><?php print $yySSForM?></option>
				<option value="1"><?php print $yySSForF?></option>
				<option value="2"><?php print $yySSForN?></option>
				</select>
<?php	} ?>
			  </td>
			</tr>
			<tr>
			  <td align="right">Page Title Tag<?php print ' ('.$yyOptnl.')'?>:</td>
			  <td><input type="text" name="sTitle" size="40" value="<?php print htmlspecials($sTitle)?>" /></td>
			</tr>
			<tr>
			  <td align="right">Meta Description<?php print ' ('.$yyOptnl.')'?>:</td>
			  <td><input type="text" name="sMetaDesc" size="40" value="<?php print htmlspecials($sMetaDesc)?>" maxlength="250" /></td>
			</tr>
			<tr>
			  <td align="right"><?php print $yyCatURL.' ('.$yyOptnl.')'?>:</td>
			  <td><input type="text" name="sectionurl" size="40" value="<?php print htmlspecials($sectionurl)?>" /></td>
			</tr>
<?php	for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 2048)==2048){ ?>
			<tr>
			  <td align="right"><?php print $yyCatURL.' '.$index.' ('.$yyOptnl.')'?>:</td>
			  <td><input type="text" name="sectionurl<?php print $index?>" size="40" value="<?php print htmlspecials($sectionurls[$index])?>" /></td>
			</tr>
<?php		}
		} ?>
			<tr>
			  <td align="right">Catalog Root (Optional):</td>
			  <td><input type="checkbox" name="catalogroot" value="ON" <?php if($catalogroot==$sectionID) print 'checked="checked" '?>/> Check to make this category the product catalog root.</td>
			</tr>
			<tr id="sectionHeadTR">
				<td align="right">Category Header:</td><td><textarea name="sectionHeader" id="sectionHeader" cols="48" rows="8"><?php print $sectionHeader?></textarea></td>
			</tr>
<?php	for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 524288)==524288){ ?>
			  <tr id="sectionHeadTR<?php print $index?>">
				<td align="right"><?php print 'Category Header' . " " . $index?>:</td>
                <td><textarea name="sectionHeader<?php print $index?>" id="sectionHeader<?php print $index?>" cols="55" rows="8"><?php print htmlspecials($sectionHeaders[$index])?></textarea></td>
			  </tr>
<?php		}
		} ?>
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" /></td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="2"><br /><ul>
				  <li><?php print $yyCatEx1?></li>
				  <li><?php print $yyCatEx2?></li>
				  </ul></td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="2" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table>
		  </form>
<?php
	print '<script type="text/javascript">';
	print "checkheaders(document.getElementById('catfunction'));\r\n";
	if(@$htmleditor=='fckeditor'||@$htmleditor=='ckeditor'){
		if(@$pathtossl!='' && (@$_SERVER['HTTPS']=='on' || @$_SERVER['SERVER_PORT']=='443')){
			if(substr($pathtossl,-1) != "/") $storeurl=$pathtossl . "/"; else $storeurl=$pathtossl;
		}
		$pathtovsadmin=dirname(@$_SERVER['PHP_SELF']);
		print 'function loadeditors(){';
		if($htmleditor=='ckeditor'){
			$streditor="var sectionHeader=CKEDITOR.replace('sectionHeader',{extraPlugins : 'stylesheetparser,autogrow',autoGrow_maxHeight : 800,removePlugins : 'resize', toolbarStartupExpanded : false, toolbar : 'Basic', filebrowserBrowseUrl :'ckeditor/filemanager/browser/default/browser.html?Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserImageBrowseUrl : 'ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserFlashBrowseUrl :'ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=File',filebrowserImageUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=Image',filebrowserFlashUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=Flash'});\r\n";
			$streditor.="sectionHeader.on('instanceReady',function(event){var myToolbar='Basic';event.editor.on( 'beforeMaximize', function(){if(event.editor.getCommand('maximize').state==CKEDITOR.TRISTATE_ON && myToolbar != 'Basic'){sectionHeader.setToolbar('Basic');myToolbar='Basic';sectionHeader.execCommand('toolbarCollapse');}else if(event.editor.getCommand('maximize').state==CKEDITOR.TRISTATE_OFF && myToolbar != 'Full'){sectionHeader.setToolbar('Full');myToolbar='Full';sectionHeader.execCommand('toolbarCollapse');}});event.editor.on('contentDom', function(e){event.editor.document.on('blur', function(){if(!sectionHeader.isToolbarCollapsed){sectionHeader.execCommand('toolbarCollapse');sectionHeader.isToolbarCollapsed=true;}});event.editor.document.on('focus',function(){if(sectionHeader.isToolbarCollapsed){sectionHeader.execCommand('toolbarCollapse');sectionHeader.isToolbarCollapsed=false;}});});sectionHeader.fire('contentDom');sectionHeader.isToolbarCollapsed=true;});\r\n";
		}else
			$streditor="var oFCKeditor=new FCKeditor('sectionHeader');oFCKeditor.BasePath=sBasePath;oFCKeditor.Config.BaseHref='".$storeurl."';oFCKeditor.ToolbarSet='Basic';oFCKeditor.ReplaceTextarea();\r\n";
		print $streditor;
		print replace($streditor, 'sectionHeader', 'sectionDescription');
		for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 512)==512) print str_replace('sectionHeader', 'sectionDescription' . $index, $streditor);
			if(($adminlangsettings & 524288)==524288) print str_replace('sectionHeader', 'sectionHeader' . $index, $streditor);
		}
		print '}window.onload=function(){loadeditors();}';
	}
	print '</script>';
}elseif(getpost('act')=='discounts'){
		$sSQL="SELECT sectionName FROM sections WHERE sectionID=" . getpost('id');
		$result=ect_query($sSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		$thisname=$rs["sectionName"];
		ect_free_result($result);
		$numassigns=0;
		$sSQL="SELECT cpaID,cpaCpnID,cpnWorkingName,cpnSitewide,cpnEndDate,cpnType FROM cpnassign LEFT JOIN coupons ON cpnassign.cpaCpnID=coupons.cpnID WHERE cpaType=1 AND cpaAssignment='" . getpost('id') . "'";
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
		  <form name="mainform" method="post" action="admincats.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="dodiscounts" />
			<input type="hidden" name="id" value="<?php print getpost('id')?>" />
			<input type="hidden" name="pg" value="<?php print getpost('pg')?>" />
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="4" align="center"><strong><?php print $yyAssDis?> &quot;<?php print $thisname?>&quot;.</strong><br />&nbsp;</td>
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
}elseif(getpost('act')=="changepos"){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%" align="center">
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p><strong><?php print $yyUpdat?> . . . . . . . </strong></p>
			<p>&nbsp;</p>
			<p><?php print $yyNoFor?> <a href="admincats.php"><?php print $yyClkHer?></a>.</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
		  </td>
		</tr>
	  </table>
<?php
}elseif(getpost('posted')=='1' && getpost('act')!='sort' && $success){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="admincats.php"><strong><?php print $yyClkHer?></strong></a>.<br />&nbsp;<br />&nbsp;
                </td>
			  </tr>
			</table></td>
        </tr>
	  </table>
<?php
}elseif(getpost('posted')=='1' && getpost('act')!='sort'){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table></td>
        </tr>
	  </table>
<?php
}else{
	$pract='';
	if(getpost('sort')!='') $sortorder=getpost('sort'); else $sortorder=@$_COOKIE['catsort'];
	if(getget('catorman')!='') $catorman=getget('catorman'); else $catorman=@$_COOKIE['ccatorman'];
	$allcoupon='';
	$numcoupons=0;
	$sSQL='SELECT DISTINCT cpaAssignment FROM cpnassign WHERE cpaType=1';
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result))
		$allcoupon[$numcoupons++]=$rs;
	ect_free_result($result);
	$modclone=@$_COOKIE['modclone'];
?>
<script type="text/javascript">
/* <![CDATA[ */
function setCookie(c_name,value,expiredays){
	var exdate=new Date();
	exdate.setDate(exdate.getDate()+expiredays);
	document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}
var rowsingrp=[];
function cpu(x,theid,grpid,secid){
	if(x.length>1) return;
	x.onchange=function(){chi(secid,x);};
	var totrows=rowsingrp[grpid];
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
	document.mainform.action="admincats.php?catfun=<?php print @$_REQUEST['catfun']?>&stext=<?php print urlencode(@$_REQUEST['stext'])?>&sprice=<?php print urlencode(@$_REQUEST['sprice'])?>&stype=<?php print @$_REQUEST['stype']?>&scat=<?php print @$_REQUEST['scat']?>&pg=<?php print (getget('pg')=='' ? 1 : getget('pg'))?>";
	document.mainform.newval.value=obj.selectedIndex+1;
	document.mainform.id.value=id;
	document.mainform.act.value="changepos";
	document.mainform.submit();
}
function mr(id){
	document.mainform.action="admincats.php";
	document.mainform.id.value=id;
	document.mainform.act.value="modify";
	document.mainform.submit();
}
function cr(id){
	document.mainform.action="admincats.php";
	document.mainform.id.value=id;
	document.mainform.act.value="clone";
	document.mainform.submit();
}
function newrec(id){
	document.mainform.action="admincats.php";
	document.mainform.id.value=id;
	document.mainform.act.value="addnew";
	document.mainform.submit();
}
function dsc(id) {
	document.mainform.action="admincats.php";
	document.mainform.id.value=id;
	document.mainform.act.value="discounts";
	document.mainform.submit();
}
function dr(id){
if(confirm("<?php print jscheck($yyConDel)?>\n")) {
	document.mainform.action="admincats.php";
	document.mainform.id.value=id;
	document.mainform.act.value="delete";
	document.mainform.submit();
}
}
function startsearch(){
	document.mainform.action="admincats.php";
	document.mainform.act.value="search";
	document.mainform.posted.value='';
	document.mainform.submit();
}
function inventorymenu(){
	themenuitem=document.mainform.inventoryselect.options[document.mainform.inventoryselect.selectedIndex].value;
	if(themenuitem=="1") document.mainform.act.value="catinventory";
	document.mainform.action="dumporders.php";
	document.mainform.submit();
}
function changesortorder(men){
	document.mainform.action="admincats.php<?php if(getpost('act')=='search' || getget('pg')!='') print '?pg=1'?>";
	document.mainform.id.value=men.options[men.selectedIndex].value;
	document.mainform.act.value="sort";
	document.mainform.posted.value="1";
	document.mainform.submit();
}
function switchcatorman(obj){
	document.location="admincats.php?catorman="+obj[obj.selectedIndex].value+"&stext=<?php print urlencode(@$_REQUEST['stext'])?>&stype=<?php print @$_REQUEST['stype']?>&pg=<?php print (getget('pg')=='' && getpost('act')=='search' ? 1 : getget('pg'))?>";
}
function changemodclone(modclone){
	setCookie('modclone',modclone[modclone.selectedIndex].value,600);
	startsearch();
}
/* ]]> */
</script>
<?php
$numcats=0;
$thecat=@$_REQUEST['scat'];
if($thecat!='') $thecat=(int)$thecat;
if(@$noadmincategorysearch!=TRUE){
	$sSQL="SELECT sectionID,sectionWorkingName,topSection,rootSection FROM sections WHERE rootSection=0 ORDER BY sectionOrder";
	$allcats=ect_query($sSQL) or ect_error();
	while($row=ect_fetch_assoc($allcats)){
		$allcatsa[$numcats++]=$row;
	}
	ect_free_result($allcats);
} ?>
<h2><?php print $yyAdmCat?></h2>
		  <form name="mainform" method="post" action="admincats.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="id" value="xxxxx" />
			<input type="hidden" name="pg" value="<?php if(getpost('act')=='search') print '1'; else print getget('pg')?>" />
			<input type="hidden" name="newval" value="1" />
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr> 
				<td class="cobhl" width="25%" align="right"><?php print $yySrchFr?>:</td>
				<td class="cobll" width="25%"><input type="text" name="stext" size="20" value="<?php print @$_REQUEST['stext']?>" /></td>
				<td class="cobhl" width="25%" align="right"><?php print str_replace('...','',$yyCatFn)?>:</td>
				<td class="cobll" width="25%"><select name="catfun" size="1">
					<option value=""><?php print $yySrchAC?></option>
					<option value="1"<?php if(@$_REQUEST['catfun']=='1') print ' selected="selected"'?>><?php print $yyCatPrd?></option>
					<option value="2"<?php if(@$_REQUEST['catfun']=='2') print ' selected="selected"'?>><?php print $yyCatCat?></option>
					<option value="3"<?php if(@$_REQUEST['catfun']=='3') print ' selected="selected"'?>>Restricted Categories</option>
					<option value="4"<?php if(@$_REQUEST['catfun']=='4') print ' selected="selected"'?>>Disabled Categories</option>
				</select></td>
			  </tr>
			  <tr>
				<td class="cobhl" width="25%" align="right"><?php print $yySrchTp?>:</td>
				<td class="cobll" width="25%"><select name="stype" size="1">
					<option value=""><?php print $yySrchAl?></option>
					<option value="any"<?php if(@$_REQUEST['stype']=='any') print ' selected="selected"'?>><?php print $yySrchAn?></option>
					<option value="exact"<?php if(@$_REQUEST['stype']=='exact') print ' selected="selected"'?>><?php print $yySrchEx?></option>
					</select>
				</td>
				<td class="cobhl" width="25%" align="right"><select size="1" name="catorman" onchange="switchcatorman(this)">
					<option value="cat"><?php print $yySrchCt?></option>
					<option value="non"<?php if($catorman=='non') print ' selected="selected"'?>><?php print $yyNone?></option>
					</select></td>
				<td class="cobll" width="25%">
<?php	if($catorman=='non')
			print '&nbsp;';
		else{ ?>
				  <select name="scat" size="1">
				  <option value=""><?php print $yySrchAC?></option>
				<?php	writemenulevel(0,1); ?>
				  </select>
<?php	} ?>
				</td>
			  </tr>
			  <tr>
				<td class="cobhl" align="center">&nbsp;</td>
				<td class="cobll" colspan="3"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
					  <td class="cobll" align="center" style="white-space:nowrap">
						<select name="sort" size="1" onchange="changesortorder(this)">
						<option value="can"<?php if($sortorder=='can') print ' selected="selected"'?>>Sort - Cat Name</option>
						<option value="cwn"<?php if($sortorder=='cwn') print ' selected="selected"'?>>Sort - Working Name</option>
						<option value="act"<?php if($sortorder=='act'||$sortorder=='') print ' selected="selected"'?>>Sort - Actual Order</option>
						<option value="pra"<?php if($sortorder=='pra') print ' selected="selected"'?>>Sort - Products Assigned</option>
<?php	if($useStockManagement){ ?>
						<option value="sta"<?php if($sortorder=='sta') print ' selected="selected"'?>>Sort - Stock Assigned</option>
<?php	} ?>
						<option value="nsf"<?php if($sortorder=='nsf') print ' selected="selected"'?>>No Sort (Fastest)</option>
						</select>
						<input type="submit" value="List Categories" onclick="startsearch();" />
						<input type="button" value="<?php print $yyNewCat?>" onclick="newrec()" />
					  </td>
					  <td class="cobll" height="26" width="20%" align="right" style="white-space:nowrap">
					<select name="inventoryselect" size="1">
						<option value="1">Category Inventory</option>
					</select>&nbsp;<input type="button" value="<?php print $yyGo?>" onclick="inventorymenu();" />
					  </td>
					</tr>
				  </table></td>
			  </tr>
			</table>
<br />
            <table width="100%" class="stackable admin-table-a sta-white">
<?php
function displayheaderrow(){
	global $sortorder,$yyStck,$yyOrder,$yyCatPat,$yyCatNam,$yyDiscnt,$yyModify; ?>
	  <tr>
		<th class="minicell"><strong><?php if($sortorder=='pra') print 'Products&nbsp;'; elseif($sortorder=='sta') print $yyStck; else print $yyOrder?></strong></th>
		<th class="maincell"><strong><?php print $yyCatPat?></strong></th>
		<th class="maincell"><strong><?php print $yyCatNam?></strong></th>
		<th class="minicell"><?php print $yyDiscnt?></th>
		<th class="minicell"><?php print $yyModify?></th>
	  </tr>
<?php
}
	$rowsingrp=$jscript='';
	if(getpost('act')=='search' || getget('pg')!=''){
		$CurPage=1;
		$roottopsection=0;
		if(@$menucategoriesatroot){
			$sSQL="SELECT topSection FROM sections WHERE sectionID=".$catalogroot;
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)) $roottopsection=$rs['topSection'];
			ect_free_result($result);
		}
		if(is_numeric(getget('pg'))) $CurPage=(int)(getget('pg'));
		$sSQL="SELECT COUNT(*) AS bar FROM sections";
		$result=ect_query($sSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		$iNumOfPages=ceil(($totalcats=$rs['bar'])/$admincatsperpage);
		ect_free_result($result);
		if(! @$menucategoriesatroot)
			$sSQL='SELECT sec1.sectionID,sec1.sectionWorkingName,sec1.sectionDescription,sec1.topSection AS topSection,sec1.rootSection,sec1.sectionDisabled,sec1.sectionOrder FROM sections AS sec1 LEFT JOIN sections AS sec2 ON sec1.topSection=sec2.sectionID';
		else
			$sSQL='SELECT sec1.sectionID,sec1.sectionWorkingName,sec1.sectionDescription,IF(sec1.topSection='.$catalogroot.','.$roottopsection.',sec1.topSection) AS topSection,sec1.rootSection,sec1.sectionDisabled,sec1.sectionOrder FROM sections AS sec1 LEFT JOIN sections AS sec2 ON IF(sec1.topSection='.$catalogroot.','.$roottopsection.',sec1.topSection)=sec2.sectionID';
		$whereand=' WHERE ';
		if($sortorder=='pra'||$sortorder=='sta'){
			$sSQL='SELECT sectionID,sectionWorkingName,sectionDescription,topSection,rootSection,sectionDisabled,'.($sortorder=='sta'?'SUM(pInStock)':'COUNT(pSection)').' AS sectionOrder FROM sections AS sec1 LEFT JOIN products ON sec1.sectionID=products.pSection WHERE rootSection=1';
			$whereand=' AND ';
		}
		if($thecat!=''){
			$returnalltopsections=TRUE;
			$sectionids=getsectionids($thecat, TRUE);
			if($sectionids!='')
				$sSQL.=$whereand . 'sec1.sectionID IN (' . $sectionids . ') ';
			$whereand=' AND ';
		}
		if(trim(@$_REQUEST['stext'])!=''){
			$Xstext=escape_string(@$_REQUEST['stext']);
			$aText=explode(' ',$Xstext);
			if(@$nosearchadmindescription) $maxsearchindex=0; else $maxsearchindex=1;
			$aFields[0]=getlangid('sectionName',256);
			$aFields[1]=getlangid('sectionDescription',512);
			if(@$_REQUEST['stype']=='exact'){
				$sSQL.=$whereand . "(sec1.sectionWorkingName LIKE '%".$Xstext."%' OR ";
				for($index=1; $index<=$adminlanguages+1; $index++){
					$sSQL.='sec1.sectionName'.($index==1?'':$index)." LIKE '%".$Xstext."%' OR sec1.sectionDescription".($index==1?'':$index)." LIKE '%".$Xstext."%'";
					if($index<$adminlanguages+1) $sSQL.=" OR ";
				}
				$sSQL.=") ";
				$whereand=" AND ";
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
						$sSQL.='sec1.' . $aFields[$index] . " LIKE '%" . $theopt . "%' ";
						if(++$rowcounter < $arrelms) $sSQL.=$sJoin;
					}
					$sSQL.=') ';
					if($index < $maxsearchindex) $sSQL.='OR ';
				}
				$sSQL.=') ';
			}
		}
		if(@$_REQUEST['catfun']=='1'){ $sSQL.=$whereand . "sec1.rootSection=1 "; $whereand=" AND "; }
		if(@$_REQUEST['catfun']=='2'){ $sSQL.=$whereand . "sec1.rootSection=0 "; $whereand=" AND "; }
		if(@$_REQUEST['catfun']=='3'){ $sSQL.=$whereand . "sec1.sectionDisabled<>0 "; $whereand=" AND "; }
		if(@$_REQUEST['catfun']=='4'){ $sSQL.=$whereand . "sec1.sectionDisabled=127 "; $whereand=" AND "; }
		if($sortorder=='can')
			$sSQL.=" ORDER BY sec1.sectionName";
		elseif($sortorder=='cwn')
			$sSQL.=" ORDER BY sec1.sectionWorkingName";
		elseif($sortorder=='nsf')
			; // Nothing
		elseif($sortorder=='pra')
			$sSQL.=" GROUP BY sectionID ORDER BY COUNT(pSection)";
		elseif($sortorder=='sta')
			$sSQL.=" GROUP BY sectionID ORDER BY SUM(pInStock)";
		else
			$sSQL.=" ORDER BY sec2.sectionOrder,sec2.sectionID,sec1.sectionOrder";
		$sSQL.=' LIMIT ' . ($admincatsperpage*($CurPage-1)) . ', ' . $admincatsperpage;
		$currgroup=-1;
		$result=ect_query($sSQL) or ect_error();
		if($totalcats > 0){
			$islooping=FALSE;
			$noproducts=FALSE;
			$hascatinprodsection=FALSE;
			$rowcounter=0;
			$pblink='<a href="admincats.php?scat='.@$_REQUEST['scat'].'&stext='.urlencode(@$_REQUEST['stext']).'&stype='.@$_REQUEST['stype'].'&catfun='.@$_REQUEST['catfun'].'&pg=';
			if($iNumOfPages > 1) print '<tr><td align="center" colspan="5">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '<br /><br /></td></tr>';
			displayheaderrow();
			$ordingroup=1;
			$checkfirstgroup=($CurPage!=1);
			while($rs=ect_fetch_assoc($result)){
				if($currgroup==-1) $currgroup=$rs['topSection'];
				if($currgroup!=$rs['topSection']){
					if($checkfirstgroup){
						$result2=ect_query('SELECT COUNT(*) AS catcnt FROM sections WHERE topSection='.$currgroup) or ect_error();
						if($rs2=ect_fetch_assoc($result2)) $ordingroup=$rs2['catcnt']+1;
						ect_free_result($result2);
						$checkfirstgroup=FALSE;
					}
					if($sortorder==''||$sortorder=='act') print '<tr><td colspan="5">&nbsp;</td></tr>';
					$rowsingrp.='rowsingrp['.$currgroup.']='.($ordingroup-1).";";
					$currgroup=$rs['topSection'];
					$ordingroup=1;
				}
				$jscript.='pa['.$rowcounter.']=['; ?>
<tr id="tr<?php print $rowcounter?>"><td class="minicell"><?php
				$currpos=min($totalcats,max(1,$rs['sectionOrder']));
				if($sortorder==''||$sortorder=='act'){
					print '<select onmouseover="cpu(this,'.$ordingroup.','.$rs['topSection'].','.$rs['sectionID'].')">';
					print '<option value="'.$currpos.'">'.$ordingroup.($ordingroup<100?'&nbsp;':'').'</option>';
					print '</select>';
				}elseif($sortorder=='pra'||$sortorder=='sta')
					print '&nbsp;' . $rs['sectionOrder'];
				else
					print '&nbsp;' ?></td><td class="maincell" style="font-size:10px"><?php
				$tslist='';
				$thetopts=$rs["topSection"];
				for($index=0; $index <= 10; $index++){
					if($thetopts==0){
						$tslist=substr($tslist,3);
						break;
					}elseif($index==10){
						$tslist='<span style="color:#FF0000;font-weight:bold">' . $yyLoop . '</span>' . $tslist;
						$islooping=TRUE;
					}else{
						$sSQL="SELECT sectionID,topSection,sectionWorkingName,rootSection FROM sections WHERE sectionID=" . $thetopts;
						$result2=ect_query($sSQL) or ect_error();
						if(ect_num_rows($result2) > 0){
							$rs2=ect_fetch_assoc($result2);
							$errstart=$errend='';
							if($rs2['rootSection']==1){
								$errstart='<span style="color:#FF0000;font-weight:bold">';
								$errend='</span>';
								$hascatinprodsection=TRUE;
							}
							$tslist=' '.$raquo.' '.$errstart.$rs2['sectionWorkingName'].$errend.$tslist;
							$thetopts=$rs2['topSection'];
						}else{
							$tslist='<span style="color:#FF0000;font-weight:bold">' . $yyTopDel . '</span>' . $tslist;
							break;
						}
						ect_free_result($result2);
					}
				}
				print $tslist . '</td><td>';
				if($rs['rootSection']==1) print '<strong>';
				if($rs['sectionDisabled']==127) print '<span style="color:#FF0000;text-decoration:line-through">';
				if($catalogroot==$rs['sectionID']) print '<span title="Catalog Root" style="padding-left:10px;text-decoration:underline overline;font-size:larger;">';
				print $rs['sectionWorkingName'] . ' (' . $rs['sectionID'] . ')';
				if($catalogroot==$rs['sectionID']) print '</span>';
				if($rs['sectionDisabled']==127) print '</span>';
				if($rs['rootSection']==1) print '</strong>';
				$hascoupon='0';
				for($index=0;$index<$numcoupons;$index++){
					if((int)$allcoupon[$index]['cpaAssignment']==$rs['sectionID']){
						$hascoupon='1';
						break;
					}
				}
		?></td><td>-</td><td>-</td></tr>
<?php			$jscript.=$rs['sectionID'].','.$hascoupon."];\r\n";
				$rowcounter++;
				$ordingroup++;
			}
			if($iNumOfPages > 1) print '<tr><td align="center" colspan="5"><br />' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
			if($islooping){ ?>
				  <tr><td width="100%" colspan="5"><br /><span style="color:#FF0000;font-weight:bold">** </span><?php print $yyCatEx3?></td></tr>
<?php		}
			if($hascatinprodsection){ ?>
				  <tr><td width="100%" colspan="5"><br /><ul><li><?php print $yyCPErr?></li></ul></td></tr>
<?php		} ?>
				  <tr><td width="100%" colspan="5"><br /><ul><li><?php print $yyCatEx4?></li></ul></td></tr>
<?php	}else{ ?>
				  <tr><td width="100%" colspan="5" align="center"><br /><strong><?php print $yyCatEx5?><br />&nbsp;</td></tr>
<?php	}
		ect_free_result($result);
		$result=ect_query('SELECT COUNT(*) AS catcnt FROM sections WHERE topSection='.$currgroup) or ect_error();
		if($rs=ect_fetch_assoc($result)) $ordingroup=$rs['catcnt']+1;
		ect_free_result($result);
		$rowsingrp.='rowsingrp['.$currgroup.']='.($ordingroup-1).';';
	}elseif(trim(@$detlinkspacechar)!=''){
		$rowcounter=0;
		$sSQL="SELECT sectionID,sectionWorkingName,sectionDescription,sectionURL,rootSection,sectionDisabled,0 AS sectionOrder FROM sections WHERE sectionURL LIKE '%\\".escape_string($detlinkspacechar)."%'";
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result)>0){
			print '<tr><td colspan="5" style="color:#FF0000">You have the $detlinkspacechar parameter set as &quot;' . $detlinkspacechar. '&quot; but have categories where the Static URL uses this character and these will not display properly. Consider removing the $detlinkspacechar parameter, or replacing it with a space in the Static URL for these products.</td></tr>';
			displayheaderrow();
			while($rs=ect_fetch_assoc($result)){
				$jscript.='pa['.$rowcounter.']=['.$rs['sectionID'].",0];\r\n"; ?>
<tr id="tr<?php print $rowcounter?>"><td class="minicell">-</td><td><?php print $rs['sectionURL']?></td><td><?php print $rs['sectionWorkingName']?></td><td>-</td><td>-</td></tr>
<?php			$rowcounter++;
			}
		}
	}
?>
			  <tr>
                <td width="100%" colspan="5" align="center"><br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
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
	ttr.cells[4].style.textAlign='center';
	ttr.cells[4].style.whiteSpace='nowrap';
	ttr.cells[3].innerHTML='<input type="button" '+(pa[pidind][1]?' style="color:#F4E64B"':'')+' value="<?php print jsescape(htmlspecials($yyAssign))?>" onclick="dsc(\''+pa[pidind][0]+'\')" />';
	ttr.cells[4].innerHTML='<input type="button" value="M" style="width:30px" onclick="mr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyModify))?>" />&nbsp;' +
		'<input type="button" value="C" style="width:30px" onclick="cr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyClone))?>" />&nbsp;' +
		'<input type="button" value="X" style="width:30px" onclick="dr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyDelete))?>" />';
}
<?php print @$rowsingrp?>
/* ]]> */
</script>
<?php
}
?>