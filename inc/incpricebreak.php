<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=='') $storesessionvalue='virtualstore'.time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
$success=TRUE;
if(@$maxbreaksperpage=='')$maxbreaksperpage=200;
$maxpricebreaks = 25;
$sSQL='';
$dropdown=(getpost('ddown')=='1');
$alldata='';
$dorefresh=FALSE;
if(getpost('posted')=='1'){
	if(getpost('act')=='delete'){
		$sSQL = "DELETE FROM pricebreaks WHERE pbProdID='" . escape_string(getpost('id')) . "'";
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}elseif(getpost('act')=="domodify"){
		$theprod=getpost('pid');
		$sSQL = "SELECT pID FROM products WHERE pID='" . str_replace("'","''",$theprod) . "'";
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result)<=0){
			$success=FALSE;
			$errmsg = "The specified product id (" . $theprod . ") does not exist.";
		}
		ect_free_result($result);
		if($success){
			ect_query("DELETE FROM pricebreaks WHERE pbProdID='" . escape_string($theprod) . "'") or ect_error();
			for($index=1; $index <= $maxpricebreaks; $index++){
				$thequant=getpost('quant' . $index);
				if(! is_numeric($thequant)) $thequant=0;
				$price=getpost('price' . $index);
				if(! is_numeric($price)) $price=0;
				$wprice=getpost('wprice' . $index);
				if(! is_numeric($wprice)) $wprice=0;
				if($thequant != 0 && ($price != 0 || $wprice != 0)){
					$sSQL = "INSERT INTO pricebreaks (pbProdID,pbQuantity,pPrice,pWholesalePrice) VALUES ('" . escape_string($theprod) . "',";
					$sSQL.=$thequant . ",";
					$sSQL.=$price . ",";
					$sSQL.=$wprice . ")";
					ect_query($sSQL) or ect_error();
				}
			}
			$dorefresh=TRUE;
		}
	}elseif(getpost('act')=="doaddnew"){
		$theprod=getpost('pid');
		$sSQL = "SELECT pbProdID FROM pricebreaks WHERE pbProdID='" . str_replace("'","''",$theprod) . "'";
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result)>0){
			$success=FALSE;
			$errmsg = 'Price breaks already exist for this product id. You should use the "Modify" option on the price breaks admin page';
		}
		ect_free_result($result);
		$sSQL = "SELECT pID FROM products WHERE pID='" . str_replace("'","''",$theprod) . "'";
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result)<=0){
			$success=FALSE;
			$errmsg = "The specified product id (" . $theprod . ") does not exist.";
		}
		ect_free_result($result);
		if($success){
			for($index=1; $index <= $maxpricebreaks; $index++){
				$thequant=getpost('quant' . $index);
				if(! is_numeric($thequant)) $thequant=0;
				$price=getpost('price' . $index);
				if(! is_numeric($price)) $price=0;
				$wprice=getpost('wprice' . $index);
				if(! is_numeric($wprice)) $wprice=0;
				if($thequant != 0 && ($price != 0 || $wprice != 0)){
					$sSQL = "INSERT INTO pricebreaks (pbProdID,pbQuantity,pPrice,pWholesalePrice) VALUES ('" . escape_string($theprod) . "',";
					$sSQL.=$thequant . ",";
					$sSQL.=$price . ",";
					$sSQL.=$wprice . ")";
					ect_query($sSQL) or ect_error();
				}
			}
			$dorefresh=TRUE;
		}
	}
	if($dorefresh)
		print '<meta http-equiv="refresh" content="1; url=adminpricebreak.php?stext=' . urlencode(@$_REQUEST['stext']) . '&sort=' . @$_REQUEST['sort'] . '&stype=' . @$_REQUEST['stype'] . '&ddown=' . @$_REQUEST['ddown'] . '&pg=' . @$_REQUEST['pg'] . '" />';
}
?>
<script type="text/javascript">
<!--
function formvalidator(theForm){
<?php if($dropdown){ ?>
  if (theForm.pid.selectedIndex==0){
    alert("<?php print jscheck($yyPlsSel . ' "' . $yyPrId)?>\".");
<?php }else{ ?>
  if (theForm.pid.value==""){
    alert("<?php print jscheck($yyPlsEntr . ' "' . $yyPrId)?>\".");
<?php } ?>
    theForm.pid.focus();
    return (false);
  }
  return (true);
}
//-->
</script>
<?php
if(getpost('posted')=='1' && (getpost('act')=='modify' || getpost('act')=='clone' || getpost('act')=='addnew')){ ?>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
		  <td align="center">
		  <form name="mainform" method="post" action="adminpricebreak.php" onsubmit="return formvalidator(this)">
			<input type="hidden" name="posted" value="1" />
			<?php if(getpost('act')=='clone' || getpost('act')=='addnew'){ ?>
			<input type="hidden" name="act" value="doaddnew" />
			<?php }else{ ?>
			<input type="hidden" name="act" value="domodify" />
			<input type="hidden" name="pid" value="<?php print getpost('id')?>" />
			<?php }
			writehiddenvar('ddown', getpost('ddown'));
			writehiddenvar('stext', getpost('stext'));
			writehiddenvar('sort', getpost('sort'));
			writehiddenvar('stype', getpost('stype'));
			writehiddenvar('pg', getpost('pg')); ?>
			<table width="320" border="0" cellspacing="0" cellpadding="1">
			  <tr> 
                <td colspan="3" align="center"><strong><?php print $yyPBKAdm?></strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td colspan="3" align="center"><strong><?php print $yyPBFID?>:</strong> <?php
				if($dropdown && (getpost('act')=='clone' || getpost('act')=='addnew')){
					print '<select size="1" name="pid"><option value="">' . $yySelect . "</option>";
					$sSQL = "SELECT pID FROM products LEFT JOIN pricebreaks ON products.pID=pricebreaks.pbProdID WHERE pbProdID IS NULL ORDER BY pID";
					$result=ect_query($sSQL) or ect_error();
					while($rs=ect_fetch_assoc($result))
						print '<option value="' . $rs["pID"] . '">' . $rs["pID"] . "</option>\r\n";
					ect_free_result($result);
					print '</select>';
				}elseif(getpost('act')=='clone' || getpost('act')=='addnew'){
					print '<input type="text" name="pid" size="20" />';
				}else{
					print getpost('id');
				} ?></td>
			  </tr>
			  <tr>
				<td align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyQuaFro?></span></strong></td>
				<td align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyPrPri?></span></strong></td>
				<td align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyWhoPri?></span></strong></td>
			  </tr>
<?php		$sSQL = "SELECT pbQuantity,pPrice,pWholesalePrice FROM pricebreaks WHERE pbProdID='" . trim(str_replace("'","''",getpost('id'))) . "' ORDER BY pbQuantity";
			$index=1;
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){ ?>
			  <tr>
				<td align="center"><input type="text" name="quant<?php print $index?>" size="12" value="<?php print $rs["pbQuantity"]?>" /></td>
				<td align="center"><input type="text" name="price<?php print $index?>" size="12" value="<?php print $rs["pPrice"]?>" /></td>
				<td align="center"><input type="text" name="wprice<?php print $index?>" size="12" value="<?php print $rs["pWholesalePrice"]?>" /></td>
			  </tr>
<?php			$index++;
			}
			ect_free_result($result);
			for($index2=$index; $index2 < $maxpricebreaks; $index2++){ ?>
			  <tr>
				<td align="center"><input type="text" name="quant<?php print $index2?>" size="12" value="" /></td>
				<td align="center"><input type="text" name="price<?php print $index2?>" size="12" value="" /></td>
				<td align="center"><input type="text" name="wprice<?php print $index2?>" size="12" value="" /></td>
			  </tr>
<?php		} ?>
			  <tr>
                <td width="100%" colspan="3" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" /></td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="3" align="center"><br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
	  </table>
<?php
}elseif(getpost('posted')=="1" && $success){ ?>
		<table width="100%" border="0" cellspacing="0" cellpadding="3">
		  <tr> 
			<td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
					<?php print $yyNoAuto?> <a href="adminpricebreak.php"><strong><?php print $yyClkHer?></strong></a>.<br />
					<br />&nbsp;</td>
		  </tr>
		</table>
<?php
}elseif(getpost('posted')=="1"){ ?>
		<table width="100%" border="0" cellspacing="0" cellpadding="2">
		  <tr> 
			<td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
			<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
		  </tr>
		</table>
<?php
}else{
	$jscript='';
	$sortorder=@$_REQUEST['sort'];
	$modclone = @$_COOKIE['modclone']; ?>
<script type="text/javascript">
<!--
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
function newrec(){
	document.mainform.act.value="addnew";
	document.mainform.submit();
}
function cr(id){
	document.mainform.id.value=id;
	document.mainform.act.value="clone";
	document.mainform.submit();
}
function dr(id){
if(confirm("<?php print jscheck($yyConDel)?>\n")){
	document.mainform.id.value=id;
	document.mainform.act.value="delete";
	document.mainform.submit();
}
}
function startsearch(){
	document.mainform.action="adminpricebreak.php";
	document.mainform.act.value="search";
	document.mainform.posted.value="";
	document.mainform.submit();
}
function changemodclone(modclone){
	setCookie('modclone',modclone[modclone.selectedIndex].value,600);
	startsearch();
}
// -->
</script>
<h2><?php print $yyAdmQua?></h2>
		  <form name="mainform" method="post" action="adminpricebreak.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="id" value="xxxxx" />
			<input type="hidden" name="pg" value="<?php print (getpost('act')=='search' ? '1' : getget('pg'))?>" />
			<input type="hidden" name="selectedq" value="1" />
			<input type="hidden" name="newval" value="1" />
	<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
	  <tr height="30"> 
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
	  <tr height="30">
		<td class="cobhl">&nbsp;</td>
		<td class="cobll" colspan="3" align="center">
				<select name="sort" size="1">
				<option value="bid">Sort - Product ID</option>
				<option value="bna"<?php if($sortorder=='bna') print ' selected="selected"'?>>Sort - Product Name</option>
				</select>
				<input type="submit" value="List Quantity Discounts" onclick="startsearch();" />
				<input type="button" value="<?php print $yyNewPBK?>" onclick="newrec()" />
				<select name="ddown" size="1"><option value="">Text Entry</option><option value="1"<?php if(@$_REQUEST['ddown']=="1") print ' selected="selected"'?>>Dropdown Menu</option></select>
	  </tr>
	</table>
<br />
            <table width="100%" class="stackable admin-table-a sta-white">
<?php
	if(getpost('act')=='search' || getget('pg')!=''){
		$hassearch=FALSE;
		$sSQL = '';
		$whereand=" WHERE ";
		if(trim(@$_REQUEST['stext'])!=''){
			$Xstext = escape_string(@$_REQUEST['stext']);
			$aText = explode(' ',$Xstext);
			$maxsearchindex=1;
			$aFields[0]='pbProdID';
			$aFields[1]='pName';
			if(@$_REQUEST['stype']=='exact'){
				$sSQL.=$whereand . "(pbProdID LIKE '%".$Xstext."%' OR pName LIKE '%".$Xstext."%') ";
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
		if($sortorder=='bna')
			$sSQL.=' ORDER BY pName';
		else
			$sSQL.=' ORDER BY pbProdID';
		if(! is_numeric(getget('pg')))
			$CurPage = 1;
		else
			$CurPage = (int)getget('pg');
		
		$tmpSQL = 'SELECT COUNT(DISTINCT pbProdID) AS bar FROM pricebreaks INNER JOIN products ON pricebreaks.pbProdID=products.pID' . $sSQL;
		$sSQL = "SELECT DISTINCT pbProdID,pName FROM pricebreaks INNER JOIN products ON pricebreaks.pbProdID=products.pID" . $sSQL;
		$sSQL.=" LIMIT " . ($maxbreaksperpage*($CurPage-1)) . ", $maxbreaksperpage";
		
		$result=ect_query($tmpSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		$numids = $rs['bar'];
		$iNumOfPages = ceil($numids/$maxbreaksperpage);
		ect_free_result($result);
		
		$result=ect_query($sSQL) or ect_error();
		if($numids > 0){
			$islooping=FALSE;
			$noproducts=FALSE;
			$hascatinprodsection=FALSE;
			$rowcounter=0;
			$bgcolor="";
			$pblink = '<a href="adminpricebreak.php?stext='.urlencode(@$_REQUEST['stext']).'&stype='.@$_REQUEST['stype'].'&ddown='.@$_REQUEST['ddown'].'&pg=';
			if($iNumOfPages > 1) print '<tr><td align="center" colspan="3">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '<br /><br /></td></tr>';
?>			  <tr>
				<th class="maincell"><strong><?php print $yyPrId?></strong></th>
				<th class="maincell"><strong><?php print $yyPrName?></strong></th>
				<th class="minicell"><?php print $yyModify?></th>
			  </tr>
<?php		while($rs=ect_fetch_assoc($result)){
				$jscript.='pa['.$rowcounter.']=['; ?>
<tr id="tr<?php print $rowcounter?>">
<td class="maincell"><?php print $rs['pbProdID']?></td>
<td class="maincell"><?php print $rs['pName']?></td>
<td>-</td>
</tr><?php		$jscript.="'".$rs['pbProdID']."'];\r\n";
				$rowcounter++;
			}
			if($iNumOfPages > 1) print '<tr><td align="center" colspan="3"><br />' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
		}else{ ?>
			  <tr><td width="100%" colspan="3" align="center"><br /><strong><?php print $yyItNone?></strong><br />&nbsp;</td></tr>
<?php	}
		ect_free_result($result);
	} ?>
			  <tr> 
                <td colspan="3" align="center"><br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </form>
<script type="text/javascript">
/* <![CDATA[ */
var pa=[];
<?php print $jscript?>
for(var pidind in pa){
	var ttr=document.getElementById('tr'+pidind);
	ttr.cells[2].style.textAlign='center';
	ttr.cells[2].style.whiteSpace='nowrap';
	ttr.cells[2].innerHTML='<input type="button" value="M" style="width:30px" onclick="mr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyModify))?>" />&nbsp;' +
		'<input type="button" value="C" style="width:30px" onclick="cr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyClone))?>" />&nbsp;' +
		'<input type="button" value="X" style="width:30px" onclick="dr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyDelete))?>" />';
}
/* ]]> */
</script>
<?php
}
?>