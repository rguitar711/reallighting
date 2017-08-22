<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
$success=TRUE;
$alreadygotadmin = getadminsettings();
$dorefresh=FALSE;
if(getpost('posted')=="1"){
	if(getpost('act')=="domodify"){
		if(($adminlangsettings & 4096)==4096) $maxlangs=$adminlanguages; else $maxlangs=0;
		for($index=0; $index <= $maxlangs; $index++){
			if($index==0) $mesgid=''; else $mesgid=$index+1;
			$sSQL = "UPDATE emailmessages SET ";
			$themessage = getpost('emtextarea' . ($index+1));
			if(! (@$htmlemails && (@$htmleditor=='fckeditor'||@$htmleditor=='ckeditor')))
				$themessage = str_replace("\r\n", '<br />', $themessage);
			if(getpost('id')=="nexus"){
				$sSQL.="nexussubject".$mesgid."='" . escape_string(getpost('eminputtext' . ($index+1))) . "',";
				$sSQL.="nexusemail".$mesgid."='" . escape_string($themessage) . "'";
			}	elseif(getpost('id')=="marketbasket"){
				$sSQL.="marketbasketsubject".$mesgid."='" . escape_string(getpost('eminputtext' . ($index+1))) . "',";
				$sSQL.="marketbasketemail".$mesgid."='" . escape_string($themessage) . "'";
			
			}	elseif(getpost('id')=="forestcity"){
				$sSQL.="forestcitysubject".$mesgid."='" . escape_string(getpost('eminputtext' . ($index+1))) . "',";
				$sSQL.="forestcityemail".$mesgid."='" . escape_string($themessage) . "'";
			}
			$sSQL.=' WHERE emailID=1';
			ect_query($sSQL) or ect_error();
		}
		$dorefresh=TRUE;
	}
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=adminerrormsgs.php';
	print '?id=' . urlencode(getpost('id'));
	print '">';
}
if(getpost('id')!='' && getpost('act')=='modify'){
	if(@$htmlemails!=TRUE) $htmleditor='';
	if(@$htmleditor=='ckeditor'){ ?>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<?php
	}elseif(@$htmleditor=='fckeditor'){ ?>
<script type="text/javascript" src="fckeditor.js"></script>
<script type="text/javascript">
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
var sBasePath = document.location.pathname.substring(0,document.location.pathname.lastIndexOf('adminprods.php'));
</script>
<?php
	} ?>
<script type="text/javascript">
<!--
function formvalidator(theForm){
return (true);
}
//-->
</script>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
		  <td width="100%" align="center">
		  <form name="mainform" method="post" action="adminerrormsgs.php" onsubmit="return formvalidator(this)">
<?php	writehiddenvar('posted', '1');
		writehiddenvar('act', 'domodify');
		writehiddenvar('id', getpost('id'));
?>
            <table width="100%" border="0" cellspacing="2" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><strong><?php print $yyEmlAdm . ': ' . getpost('id') . '<br />&nbsp;'; ?></strong></td>
			  </tr>
<?php	$theid = getpost('id');
		if(($adminlangsettings & 4096)==4096) $maxlangs=$adminlanguages; else $maxlangs=0;
		for($index=0; $index <= $maxlangs; $index++){
			$replacementfields = '';
			$subjectreplacementfields = '';
			$hassubject=FALSE;
			$languageid=$index+1;
		
			if($theid=='nexus'){
				$fieldlist = getlangid('nexussubject',4096).','.getlangid('nexusemail',4096);
				$replacementfields = '';
				$subjectreplacementfields='';
				$hassubject=FALSE;
			
			}elseif($theid=='marketbasket'){
				$fieldlist = getlangid('marketbasketsubject',4096).','.getlangid('marketbasketemail',4096);
				$replacementfields = '';
				$subjectreplacementfields='';
				$hassubject=FALSE;
			
				}elseif($theid=='forestcity'){
				$fieldlist = getlangid('forestcitysubject',4096).','.getlangid('forestcityemail',4096);
				$replacementfields = '';
				$subjectreplacementfields='';
				$hassubject=FALSE;
			}
			$sSQL = "SELECT ".$fieldlist." FROM emailmessages WHERE emailID=1";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
			if($theid=='nexus'){
					$thesubject = trim($rs[getlangid('nexussubject',4096)]);
					$themessage = trim($rs[getlangid('nexusemail',4096)]);

				}elseif($theid=='marketbasket'){
					$thesubject = trim($rs[getlangid('marketbasketsubject',4096)]);
					$themessage = trim($rs[getlangid('marketbasketemail',4096)]);

				}elseif($theid=='forestcity'){
					$thesubject = trim($rs[getlangid('forestcitysubject',4096)]);
					$themessage = trim($rs[getlangid('forestcityemail',4096)]);

				}else
					print 'id not set';
			}
			ect_free_result($result);
			if(! ($htmlemails && (@$htmleditor=='fckeditor'||@$htmleditor=='ckeditor'))){
				$themessage = str_replace('<br />', "\r\n", $themessage);
				$themessage = str_replace('<br>', "\r\n", $themessage);
				$themessage = str_replace('%nl%', "\r\n", $themessage);
			}else{
				$themessage = str_replace(array('<br>','%nl%'), '<br />', $themessage);
				$themessage = str_replace('<', '&lt;', $themessage);
			}
			if($adminlanguages > 0){ ?>
			  <tr>
				<td align="center" colspan="2"><strong><?php print $yyLanID . ': ' . ($index+1)?></strong></td>
			  </tr>
<?php		}
		
			if($replacementfields!=''){ ?>
			  <tr>
				<td align="right"><strong><?php print $yyRepFld?>:</strong></td>
				<td align="left"><?php print $replacementfields?></td>
			  </tr>
<?php		} ?>
			  <tr>
				<td align="right"><strong><?php print $yyMessag?>:</strong></td>
				<td align="left"><textarea name="emtextarea<?php print ($index+1)?>" cols="90" rows="15"><?php print $themessage?></textarea></td>
			  </tr>
<?php	} ?>
			  <tr>
                <td width="100%" colspan="2" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" />&nbsp;<input type="reset" value="<?php print $yyReset?>" />&nbsp;<input type="button" value="<?php print $yyCancel?>" onclick="document.location='adminerrormsgs.php?id=<?php print getpost('id')?>'" /><br />&nbsp;</td>
			  </tr>
			  <tr>
                <td width="100%" colspan="2" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
      </table>
<?php
	if(@$htmleditor=='fckeditor'||@$htmleditor=='ckeditor'){
		if(@$pathtossl!='' && (@$_SERVER['HTTPS']=='on' || @$_SERVER['SERVER_PORT']=='443')){
			if(substr($pathtossl,-1) != "/") $storeurl = $pathtossl . "/"; else $storeurl = $pathtossl;
		}
		$pathtovsadmin=dirname(@$_SERVER['PHP_SELF']);
		print '<script type="text/javascript">function loadeditors(){';
		if($htmleditor=='ckeditor'){
			$streditor = "var emtextarea=CKEDITOR.replace('emtextarea',{extraPlugins : 'stylesheetparser,autogrow',autoGrow_maxHeight : 800,removePlugins : 'resize', toolbarStartupExpanded : false, toolbar : 'Basic', filebrowserBrowseUrl :'ckeditor/filemanager/browser/default/browser.html?Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserImageBrowseUrl : 'ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserFlashBrowseUrl :'ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=File',filebrowserImageUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=Image',filebrowserFlashUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=Flash'});\r\n";
			$streditor.="emtextarea.on('instanceReady',function(event){var myToolbar = 'Basic';event.editor.on( 'beforeMaximize', function(){if(event.editor.getCommand('maximize').state==CKEDITOR.TRISTATE_ON && myToolbar != 'Basic'){emtextarea.setToolbar('Basic');myToolbar = 'Basic';emtextarea.execCommand('toolbarCollapse');}else if(event.editor.getCommand('maximize').state==CKEDITOR.TRISTATE_OFF && myToolbar != 'Full'){emtextarea.setToolbar('Full');myToolbar = 'Full';emtextarea.execCommand('toolbarCollapse');}});event.editor.on('contentDom', function(e){event.editor.document.on('blur', function(){if(!emtextarea.isToolbarCollapsed){emtextarea.execCommand('toolbarCollapse');emtextarea.isToolbarCollapsed=true;}});event.editor.document.on('focus',function(){if(emtextarea.isToolbarCollapsed){emtextarea.execCommand('toolbarCollapse');emtextarea.isToolbarCollapsed=false;}});});emtextarea.fire('contentDom');emtextarea.isToolbarCollapsed=true;});\r\n";
		}else
			$streditor = "var oFCKeditor = new FCKeditor('emtextarea');oFCKeditor.BasePath=sBasePath;oFCKeditor.Config.BaseHref='".$storeurl."';oFCKeditor.ToolbarSet = 'Basic';oFCKeditor.ReplaceTextarea();\r\n";
		if(($adminlangsettings & 4096)==4096) $maxlangs=$adminlanguages; else $maxlangs=0;
		for($index=1; $index <= $maxlangs+1; $index++)
			print str_replace("emtextarea", "emtextarea" . $index, $streditor);
		print '}window.onload=function(){loadeditors();}</script>';
	}
}elseif(getpost('posted')=="1" && $success){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="adminerrormsgs.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />&nbsp;<br />&nbsp;
                </td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}elseif(getpost('posted')=='1'){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a><p>&nbsp;</p><p>&nbsp;</p></td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}else{
?>
<script type="text/javascript">
<!--
function mrec(id) {
	// document.mainform.id.value = id;
}
// -->
</script>
<h2><?php print 'Login Error Messages' ?></h2>
		  <form name="mainform" method="post" action="adminerrormsgs.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="modify" />
			<input type="hidden" name="id" id="idset" value="" />
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
				<tr>
				<td class="cobhl" colspan="2" align="center"><strong>Custom Error Messages</strong></td>
			  </tr>
				
				
					<tr>
				<td class="cobhl" align="right">Birchstreet</td>
				<td class="cobll"><input type="button" value="Edit Message" onclick="document.getElementById('idset').value='marketbasket';document.forms.mainform.submit()" /></td>
			  </tr>
				<tr>
				<td class="cobhl" align="right">Coupa</td>
				<td class="cobll"><input type="button" value="Edit Message" onclick="document.getElementById('idset').value='forestcity';document.forms.mainform.submit()" /></td>
			  </tr>
					<tr>
				<td class="cobhl" align="right">Nexus</td>
				<td class="cobll"><input type="button" value="Edit Message" onclick="document.getElementById('idset').value='nexus';document.forms.mainform.submit()" /></td>
			  </tr>
			
			  
			  <tr> 
                <td class="cobll" colspan="2" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
			</table>
		  </form>
<?php
}
?>
