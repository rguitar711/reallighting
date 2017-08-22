<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
$success=TRUE;
$maxcatsperpage = 100;
$dorefresh=FALSE;
$alreadygotadmin = getadminsettings();
$sSQL = '';
if(getpost('posted')=="1"){
	if(getpost('act')=='delete'){
		$sSQL = "DELETE FROM contentregions WHERE contentID=".getpost('id');
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}elseif(getpost('act')=='domodify'){
		$sSQL = "UPDATE contentregions SET contentName='".escape_string(getpost('contentname'))."',contentX=".getpost('contentX').",contentData='".escape_string(getpost('contentdata'))."'";
		for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 32768)==32768)
				$sSQL.=",contentData" . $index . "='" . escape_string(getpost('contentdata' . $index)) . "'";
		}
		$sSQL.=" WHERE contentID=".getpost('id');
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}elseif(getpost('act')=="doaddnew"){
		$sSQL = "INSERT INTO contentregions (contentName,contentX,contentData";
		for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 32768)==32768)
				$sSQL.=',contentData' . $index;
		}
		$sSQL.=") VALUES (";
		$sSQL.="'".escape_string(getpost('contentname'))."'";
		$sSQL.=",".getpost('contentX');
		$sSQL.=",'".escape_string(getpost('contentdata'))."'";
		for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 32768)==32768)
				$sSQL.=",'".escape_string(getpost('contentdata' . $index))."'";
		}
		$sSQL.=")";
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=admincontent.php';
	print '?stext=' . urlencode(@$_REQUEST['stext']) . "&stype=" . @$_REQUEST['stype'] . "&pg=" . @$_REQUEST['pg'];
	print '">';
}
?>
<script type="text/javascript">
/* <![CDATA[ */
function formvalidator(theForm){
  if (theForm.contentname.value==""){
    alert("<?php print jscheck($yyPlsEntr)?> \"Region Name\".");
    theForm.contentname.focus();
    return (false);
  }
  return (true);
}
function editsize(dir){
	var contentX=document.getElementById('contentX').value;
<?php	if(@$htmleditor=='fckeditor' || @$htmleditor=='ckeditor'){ ?>
			var wid=(contentX*6);
			var amt=6;
			if(dir=='++'||dir=='--') amt=60;
			if(dir=='+'||dir=='++')
				contentX=(Math.min(parseInt(wid)+amt,900));
			else
				contentX=(Math.max(parseInt(wid)-amt,180));
<?php	}else{ ?>
			var wid=contentX;
			var amt=1;
			if(dir=='++'||dir=='--') amt=10;
			if(dir=='+'||dir=='++')
				contentX=(Math.min(parseInt(wid)+amt,150));
			else
				contentX=(Math.max(parseInt(wid)-amt,30));
<?php	} ?>
	for(var ix=1;ix<=3;ix++){
		if(ix==1)ixt='';else ixt=ix;
		if(contab = document.getElementById('<?php print (@$htmleditor=='fckeditor'||@$htmleditor=='ckeditor'?'contenttable':'contentdata')?>'+ixt)){
<?php	if(@$htmleditor=='fckeditor'||@$htmleditor=='ckeditor'){ ?>
			contab.style.width=contentX+'px';
			document.getElementById('contentX').value=parseInt(contentX/6.0);
<?php	}else{ ?>
			contab.cols=contentX;
			document.getElementById('contentX').value=contentX;
<?php	} ?>
		}
	}
}
/* ]]> */
</script>
<?php	if(getpost('posted')=='1' && (getpost('act')=='modify' || getpost('act')=='addnew')){
			if(@$htmleditor=='ckeditor'){ ?>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<?php		}elseif(@$htmleditor=='fckeditor'){ ?>
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
var sBasePath = document.location.pathname.substring(0,document.location.pathname.lastIndexOf('admincontent.php'));
/* ]]> */
</script>
<?php		}
		} ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
<?php
	if(getpost('posted')=='1' && (getpost('act')=='modify' || getpost('act')=='addnew')){
		if(getpost('act')=='modify'){
			$sSQL = "SELECT contentID,contentName,contentData,contentX FROM contentregions WHERE contentID=" . getpost('id');
			$result=ect_query($sSQL) or ect_error();
			$rs=ect_fetch_assoc($result);
			$contentID = $rs['contentID'];
			$contentName = $rs['contentName'];
			$contentX = $rs['contentX'];
			$contentData = $rs['contentData'];
			ect_free_result($result);
		}else{
			$contentID = "";
			$contentName = "";
			$contentX = 0;
			$contentData = "";
		}
		if($contentX==0) $contentX=100;
?>
        <tr>
		  <td width="100%">
		  <form name="mainform" method="post" action="admincontent.php" onsubmit="return formvalidator(this)">
			<input type="hidden" name="posted" value="1" />
			<?php if(getpost('act')=='modify'){ ?>
			<input type="hidden" name="act" value="domodify" />
			<?php }else{ ?>
			<input type="hidden" name="act" value="doaddnew" />
			<?php }
			writehiddenvar("stext", getpost('stext'));
			writehiddenvar("stype", getpost('stype'));
			writehiddenvar("pg", getpost('pg')); ?>
			<input type="hidden" name="id" value="<?php print getpost('id')?>" />
			<input type="hidden" id="contentX" name="contentX" value="<?php print $contentX?>" />
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><strong>Use this page to manage your CMS Content Regions</strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong>Region Name</strong></td><td><input type="text" name="contentname" size="30" value="<?php print htmlspecials($contentName)?>" /></td>
			  </tr>
			  <tr>
				<td align="center" colspan="2">&nbsp;<br /><input type="button" value=" -- " onclick="editsize('--')" /> <input type="button" value=" - " onclick="editsize('-')" /> <strong>Content Data</strong> <input type="button" value=" + " onclick="editsize('+')" /> <input type="button" value=" ++ " onclick="editsize('++')" /><br />
<?php			if(@$htmleditor=='fckeditor'||@$htmleditor=='ckeditor') print '<table width="'.($contentX*6).'" id="contenttable"><tr><td>'; ?>
				<textarea name="contentdata" id="contentdata" cols="<?php print $contentX?>" rows="30"><?php print htmlspecials($contentData)?></textarea>
<?php			if(@$htmleditor=='fckeditor'||@$htmleditor=='ckeditor') print '</td></tr></table>' ?>
				</td>
			  </tr>
<?php		for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 32768)==32768){
					if(getpost('act')=='modify'){
						$sSQL = "SELECT contentData".$index.' FROM contentregions WHERE contentID='.getpost('id');
						$result=ect_query($sSQL) or ect_error();
						$rs=ect_fetch_assoc($result);
						$contentData = trim($rs['contentData'.$index]);
						ect_free_result($result);
					}
?>
			  <tr>
				<td align="center" colspan="2">
<?php			if(@$htmleditor=='fckeditor'||@$htmleditor=='ckeditor') print '<table width="'.($contentX*6).'" id="contenttable' . $index . '"><tr><td>'; ?>
				&nbsp;<br /><strong>Content Data <?php print $index?></strong><br /><textarea name="contentdata<?php print $index?>" id="contentdata<?php print $index?>" cols="<?php print $contentX?>" rows="30"><?php print htmlspecials($contentData)?></textarea>
<?php			if(@$htmleditor=='fckeditor'||@$htmleditor=='ckeditor') print '</td></tr></table>' ?>
				</td>
			  </tr>
<?php			}
			} ?></td>
			  </tr>
			  <tr>
			    <td colspan="2" align="center">&nbsp;<br /><input type="submit" value="<?php print $yySubmit?>" /></td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="2" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table>
		  </form>
<?php
	if(@$htmleditor=='fckeditor'||@$htmleditor=='ckeditor'){
		if(@$pathtossl!='' && (@$_SERVER['HTTPS']=='on' || @$_SERVER['SERVER_PORT']=='443')){
			if(substr($pathtossl,-1) != "/") $storeurl = $pathtossl . "/"; else $storeurl = $pathtossl;
		}
		$pathtovsadmin=dirname(@$_SERVER['PHP_SELF']);
		print '<script type="text/javascript">function loadeditors(){';
		if($htmleditor=='ckeditor'){
			$streditor = "var contentdata=CKEDITOR.replace('contentdata',{extraPlugins : 'stylesheetparser,autogrow',autoGrow_maxHeight : 800,removePlugins : 'resize', toolbarStartupExpanded : false, toolbar : 'Basic', filebrowserBrowseUrl :'ckeditor/filemanager/browser/default/browser.html?Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserImageBrowseUrl : 'ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserFlashBrowseUrl :'ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=File',filebrowserImageUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=Image',filebrowserFlashUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=Flash'});\r\n";
			$streditor.="contentdata.on('instanceReady',function(event){var myToolbar = 'Basic';event.editor.on( 'beforeMaximize', function(){if(event.editor.getCommand('maximize').state==CKEDITOR.TRISTATE_ON && myToolbar != 'Basic'){contentdata.setToolbar('Basic');myToolbar = 'Basic';contentdata.execCommand('toolbarCollapse');}else if(event.editor.getCommand('maximize').state==CKEDITOR.TRISTATE_OFF && myToolbar != 'Full'){contentdata.setToolbar('Full');myToolbar = 'Full';contentdata.execCommand('toolbarCollapse');}});event.editor.on('contentDom', function(e){event.editor.document.on('blur', function(){if(!contentdata.isToolbarCollapsed){contentdata.execCommand('toolbarCollapse');contentdata.isToolbarCollapsed=true;}});event.editor.document.on('focus',function(){if(contentdata.isToolbarCollapsed){contentdata.execCommand('toolbarCollapse');contentdata.isToolbarCollapsed=false;}});});contentdata.fire('contentDom');contentdata.isToolbarCollapsed=true;});\r\n";
		}else
			$streditor = "var oFCKeditor = new FCKeditor('contentdata');oFCKeditor.BasePath=sBasePath;oFCKeditor.Config.BaseHref='".$storeurl."';oFCKeditor.ToolbarSet = 'Basic';oFCKeditor.ReplaceTextarea();\r\n";
		print $streditor;
		for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 32768)==32768) print str_replace("contentdata", "contentdata" . $index, $streditor);
		}
		print '}window.onload=function(){loadeditors();}</script>';
	} ?>
		  </td>
        </tr>
<?php
}elseif(getpost('posted')=="1" && $success){ ?>
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?><a href="admincontent.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />&nbsp;
                </td>
			  </tr>
			</table></td>
        </tr>
<?php
}elseif(getpost('posted')=="1"){ ?>
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table></td>
        </tr>
<?php
}else{ ?>
        <tr>
		  <td width="100%">
<script type="text/javascript">
/* <![CDATA[ */
function mrk(id) {
	document.mainform.id.value = id;
	document.mainform.act.value = "modify";
	document.mainform.submit();
}
function newrec(id){
	document.mainform.id.value = id;
	document.mainform.act.value = "addnew";
	document.mainform.submit();
}
function drk(id){
if(confirm("<?php print jscheck($yyConDel)?>\n")) {
	document.mainform.id.value = id;
	document.mainform.act.value = "delete";
	document.mainform.submit();
}
}
function startsearch(){
	document.mainform.action="admincontent.php";
	document.mainform.act.value = "search";
	document.mainform.posted.value = "";
	document.mainform.submit();
}
/* ]]> */
</script>
<h2><?php print $yyAdmCon?></h2>
		  <form name="mainform" method="post" action="admincontent.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="id" value="xxxxx" />
			<input type="hidden" name="pg" value="<?php print (getpost('act')=='search' ? '1' : getget('pg'))?>" />
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
				  <tr> 
	                <td class="cobhl" width="25%" align="right"><?php print $yySrchFr?>:</td>
					<td class="cobll" width="25%"><input type="text" name="stext" size="20" value="<?php print @$_REQUEST['stext']?>" /></td>
					<td class="cobhl" width="25%" align="right"><?php print $yySrchTp?>:</td>
					<td class="cobll" width="25%"><select name="stype" size="1">
						<option value=""><?php print $yySrchAl?></option>
						<option value="any"<?php if(@$_REQUEST['stype']=="any") print ' selected="selected"'?>><?php print $yySrchAn?></option>
						<option value="exact"<?php if(@$_REQUEST['stype']=="exact") print ' selected="selected"'?>><?php print $yySrchEx?></option>
						</select>
					</td>
				  </tr>
				  <tr>
				    <td class="cobhl" align="center">&nbsp;</td>
				    <td class="cobll" colspan="3"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					    <tr>
						  <td class="cobll" align="center" style="white-space:nowrap">
							<input type="submit" value="List Content Regions" onclick="startsearch();" />
							<input type="button" value="New Content Region" onclick="newrec()" />
						  </td>
						  <td class="cobll" height="26" width="20%" align="right" style="white-space:nowrap">
						&nbsp;
						  </td>
						</tr>
					  </table></td>
				  </tr>
				</table>
<br />
            <table width="100%" class="stackable admin-table-a sta-white">
<?php
if(getpost('act')=="search" || getget('pg')!=''){
	$CurPage = 1;
	if(getget('pg')!='') $CurPage = (int)getget('pg');
	$sSQL = "SELECT contentID,contentName,contentData FROM contentregions";
	$whereand=" WHERE ";
	if(trim(@$_REQUEST['stext'])!=''){
		$Xstext = escape_string(@$_REQUEST['stext']);
		$aText = explode(' ',$Xstext);
		if(@$nosearchadmindescription) $maxsearchindex=0; else $maxsearchindex=1;
		$aFields[0]='contentName';
		$aFields[1]='contentData';
		if(@$_REQUEST['stype']=='exact'){
			$sSQL.=$whereand . "(sectionWorkingName LIKE '%".$Xstext."%' OR ";
			for($index=1; $index<=$adminlanguages+1; $index++){
				$sSQL.="sectionName".($index==1?'':$index)." LIKE '%".$Xstext."%' OR sectionDescription".($index==1?'':$index)." LIKE '%".$Xstext."%'";
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
					$sSQL.=$aFields[$index] . " LIKE '%" . $theopt . "%' ";
					if(++$rowcounter < $arrelms) $sSQL.=$sJoin;
				}
				$sSQL.=') ';
				if($index < $maxsearchindex) $sSQL.='OR ';
			}
			$sSQL.=') ';
		}
	}
	$sSQL.=' ORDER BY contentName';
	$result=ect_query($sSQL) or ect_error();
	if(($totnumrows=ect_num_rows($result))>0){
		$rowcounter=0;
		$iNumOfPages = (int)(($totnumrows + ($maxcatsperpage-1)) / $maxcatsperpage);
		if($iNumOfPages > 1) print '<tr><td align="center" colspan="6">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,'<a href="admincontent.php?pg=',FALSE) . '<br /><br /></td></tr>';
?>
			  <tr>
				<th><strong>Region ID</strong></th>
				<th class="maincell"><strong>Region Name</strong></th>
				<th><strong>Example URL</strong></th>
				<th class="small minicell"><?php print $yyModify?></th>
				<th class="small minicell"><?php print $yyDelete?></th>
			  </tr>
<?php	while($rs=ect_fetch_assoc($result)){
			if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark'; ?>
<tr class="<?php print $bgcolor?>">
<td><?php print $rs['contentID']?></td>
<td><?php print $rs['contentName']?></td>
<td>default.php?region=<?php print $rs['contentID']?></td>
<td class="minicell"><input type="button" value="<?php print $yyModify?>" onclick="mrk('<?php print $rs['contentID']?>')" /></td>
<td class="minicell"><input type="button" value="<?php print $yyDelete?>" onclick="drk('<?php print $rs['contentID']?>')" /></td>
</tr><?php	$rowcounter++;
		}
		if($iNumOfPages > 1) print '<tr><td align="center" colspan="6">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,'<a href="admincontent.php?pg=',FALSE) . '<br /><br /></td></tr>';
	}else{ ?>
			  <tr><td width="100%" colspan="6" align="center"><br /><strong><?php print $yyItNone?><br />&nbsp;</td></tr>
<?php
	}
	ect_free_result($result);
} ?>
			  <tr> 
                <td width="100%" colspan="6" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
<?php
} ?>
      </table>