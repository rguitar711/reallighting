<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
$addsuccess = TRUE;
$success = TRUE;
$successlines = 0;
$faillines = 0;
$pidnotfoundlines = 0;
$stoppedonerror = FALSE;
$showaccount = TRUE;
$dorefresh = FALSE;
$isstockupdate=FALSE;
$isimagesupdate=FALSE;
$alreadygotadmin = getadminsettings();
$show_errors=(getpost('show_errors')=='ON');
$stop_errors=(getpost('stop_errors')=='ON');
$isupdate=(getpost('theaction')=='update');
$progressevery=500;
if(@$admindateformat=='') $admindateformat=0;
@set_time_limit(180);
// ini_set('upload_max_filesize','3M');
function ect_errorno(){
	print(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->errno:mysql_errno());
}
function ect_affected_rows(){
	return(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->affected_rows:mysql_affected_rows());
}
function toplevel_database_error($clist){
	global $success, $errmsg;
	$errmsg=ect_error();
	$open_basedir = trim(@ini_get('open_basedir'));
	if($open_basedir!='') print "open_basedir: YES<br />";
	$errmsg.='<br />Column list is: "'  . $clist . '"<br />';
	$success=FALSE;
}
function csv_database_error(){
	global $show_errors, $stop_errors, $stoppedonerror, $successlines, $faillines, $line_num, $pidnotfoundlines;
	if($show_errors)
		print 'Line ' . $line_num . ', ' . ect_error() . '<br />';
	$theerrno=ect_errorno();
	if($stop_errors)
		$stoppedonerror=TRUE;
	if($theerrno==1062)
		$pidnotfoundlines++;
	else
		$faillines++;
	$successlines--;
}
function checkmanufacturer($pmanufacturer){
	global $manufacturerarray;
	if(@$manufacturerarray[$pmanufacturer]!='')
		return($pmanufacturer);
	return(0);
}
function updatemanufacturer($pid,$pmanufacturer){
	global $manufacturerarray,$allmanufacturers;
	if($allmanufacturers!='')
		ect_query('DELETE FROM multisearchcriteria WHERE mSCscID IN ('.$allmanufacturers.") AND mSCpID='".escape_string($pid)."'") or ect_error;
	if($pmanufacturer!=0){
		if(@$manufacturerarray[$pmanufacturer]!=''){
			ect_query('INSERT INTO multisearchcriteria (mSCscID,mSCpID) VALUES ('.$pmanufacturer.",'".escape_string($pid)."')") or ect_error;
		}
	}
}
function execute_sql(){
	global $csvarray,$valuesarray,$columnarray,$columncount,$isupdate,$isstockupdate,$keycolumn,$column_list,$successlines,$faillines,$pidnotfoundlines,$isimagesupdate,$hasmanufacturer;
	if($isimagesupdate){
		$sSQL = "SELECT * FROM products WHERE pID='" . escape_string($valuesarray[0]) . "'";
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result)>0){
			if($isupdate){
				if($valuesarray[1]=='')
					$sSQL = "DELETE FROM productimages WHERE imageproduct='" . escape_string($valuesarray[0]) . "' AND imagetype=" . $valuesarray[2] . " AND imagenumber=" . $valuesarray[3];
				else
					$sSQL = "UPDATE productimages SET imagesrc='" . escape_string($valuesarray[1]) . "' WHERE imageproduct='" . escape_string($valuesarray[0]) . "' AND imagetype=" . $valuesarray[2] . " AND imagenumber=" . $valuesarray[3];
				ect_query($sSQL) or ect_error();
			}else{
				$sSQL="DELETE FROM productimages WHERE imageproduct='" . escape_string($valuesarray[0]) . "' AND imagetype=" . $valuesarray[2] . " AND imagenumber>=" . $valuesarray[3];
				ect_query($sSQL) or ect_error();
				$sSQL = 'INSERT INTO productimages (imageproduct,imagesrc,imagetype,imagenumber) VALUES (';
				$sSQL.="'" . escape_string($valuesarray[0]) . "','" . escape_string($valuesarray[1]) . "'," . $valuesarray[2] . "," . $valuesarray[3] . ")";
				ect_query($sSQL) or ect_error();
			}
		}else{
			$pidnotfoundlines++;
			$successlines--;
		}
		ect_free_result($result);
	}elseif($isstockupdate){
		if(trim($valuesarray[4])!='')
			$sSQL = "UPDATE options SET optStock=" . $valuesarray[3] . " WHERE optID=" . $valuesarray[4];
		else
			$sSQL = "UPDATE products SET pInStock=" . $valuesarray[3] . " WHERE pID='" . trim($valuesarray[0]) . "'";
		ect_query($sSQL) or ect_error();
		if(ect_affected_rows()==0){
			$pidnotfoundlines++;
			$successlines--;
		}
	}elseif($isupdate){
		$sSQL = 'UPDATE products SET ';
		$pid='';
		$pimage='';
		$plargeimage='';
		$addcomma='';
		$hasdimensions=FALSE;
		$dimspattern='PLENxPWIDxPHEI';
		$pmanufacturer=0;
		for($i=0; $i < $columncount; $i++){
			if($i != $keycolumn){
				if($columnarray[$i]=='plength' || $columnarray[$i]=='pwidth' || $columnarray[$i]=='pheight' || $columnarray[$i]=='pimage' || $columnarray[$i]=='plargeimage'){
					if($columnarray[$i]=='plength' || $columnarray[$i]=='pwidth' || $columnarray[$i]=='pheight'){
						if($columnarray[$i]=='plength') $dimspattern = str_replace('PLEN',$valuesarray[$i],$dimspattern);
						if($columnarray[$i]=='pwidth') $dimspattern = str_replace('PWID',$valuesarray[$i],$dimspattern);
						if($columnarray[$i]=='pheight') $dimspattern = str_replace('PHEI',$valuesarray[$i],$dimspattern);
						$hasdimensions=TRUE;
					}
					if($columnarray[$i]=='pimage') $pimage=$valuesarray[$i];
					if($columnarray[$i]=='plargeimage') $plargeimage = $valuesarray[$i];
				}else{
					if($columnarray[$i]=='pmanufacturer'){
						$valuesarray[$i]=checkmanufacturer($valuesarray[$i]);
						$pmanufacturer=$valuesarray[$i];
					}
					$sSQL.=$addcomma . $columnarray[$i] . "='" . escape_string($valuesarray[$i]) . "'";
					$addcomma=',';
				}
			}
		}
		if($hasdimensions){
			$dimspattern = str_replace('PLEN',0,$dimspattern);
			$dimspattern = str_replace('PWID',0,$dimspattern);
			$dimspattern = str_replace('PHEI',0,$dimspattern);
			$sSQL.=$addcomma . 'pDims' . "='" . $dimspattern . "'";
		}
		$sSQL.=" WHERE pID='" . escape_string($valuesarray[$keycolumn]) . "'";
		// print $sSQL . "<br />";
		ect_query($sSQL) or ect_error();
		if(ect_affected_rows()==0){
			$pidnotfoundlines++;
			$successlines--;
		}else{
			if($pimage!='') ect_query("UPDATE productimages SET imageSrc='" . escape_string($pimage) . "' WHERE imageProduct='" . escape_string($valuesarray[$keycolumn]) . "' AND imageNumber=0 AND imageType=0") or csv_database_error();
			if($plargeimage!='') ect_query("UPDATE productimages SET imageSrc='" . escape_string($plargeimage) . "' WHERE imageProduct='" . escape_string($valuesarray[$keycolumn]) . "' AND imageNumber=0 AND imageType=1") or csv_database_error();
		}
		if($hasmanufacturer) updatemanufacturer($valuesarray[$keycolumn],$pmanufacturer);
	}else{
		$hasdimensions=FALSE;
		$dimspattern='PLENxPWIDxPHEI';
		$pid='';
		$pimage='';
		$plargeimage='';
		$addcomma='';
		$pcolumns='(';
		$pvalues=') VALUES (';
		$pmanufacturer=0;
		for($i=0; $i < $columncount; $i++){
			if($columnarray[$i]=='plength' || $columnarray[$i]=='pwidth' || $columnarray[$i]=='pheight' || $columnarray[$i]=='pimage' || $columnarray[$i]=='plargeimage'){
				if($columnarray[$i]=='plength' || $columnarray[$i]=='pwidth' || $columnarray[$i]=='pheight'){
					if($columnarray[$i]=='plength') $dimspattern = str_replace('PLEN',$valuesarray[$i],$dimspattern);
					if($columnarray[$i]=='pwidth') $dimspattern = str_replace('PWID',$valuesarray[$i],$dimspattern);
					if($columnarray[$i]=='pheight') $dimspattern = str_replace('PHEI',$valuesarray[$i],$dimspattern);
					$hasdimensions=TRUE;
				}
				if($columnarray[$i]=='pimage') $pimage=$valuesarray[$i];
				if($columnarray[$i]=='plargeimage') $plargeimage = $valuesarray[$i];
			}else{
				if($columnarray[$i]=='pid') $pid = $valuesarray[$i];
				if($columnarray[$i]=='pmanufacturer'){
					$valuesarray[$i]=checkmanufacturer($valuesarray[$i]);
					$pmanufacturer=$valuesarray[$i];
				}
				$pcolumns.=$addcomma . $columnarray[$i];
				$pvalues.=$addcomma . "'" . escape_string($valuesarray[$i]) . "'";
				$addcomma=',';
			}
		}
		if($hasdimensions){
			$dimspattern = str_replace('PLEN',0,$dimspattern);
			$dimspattern = str_replace('PWID',0,$dimspattern);
			$dimspattern = str_replace('PHEI',0,$dimspattern);
			$pcolumns.=$addcomma . 'pDims';
			$pvalues.=$addcomma . "'" . escape_string($dimspattern) . "'";
		}
		$sSQL = 'INSERT INTO products ' . $pcolumns . $pvalues . ')';
		// print $sSQL . "<br />";
		ect_query($sSQL) or csv_database_error();
		if($pimage!='') ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('" . escape_string($pid) . "','" . escape_string($pimage) . "',0,0)") or csv_database_error();
		if($plargeimage!='') ect_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('" . escape_string($pid) . "','" . escape_string($plargeimage) . "',0,1)") or csv_database_error();
		if($hasmanufacturer) updatemanufacturer($pid,$pmanufacturer);
	}
}
function microtime_float(){
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}
function getsection($swn){
	$sSQL = "SELECT sectionID FROM sections WHERE rootSection=1 AND sectionWorkingName='".escape_string($swn)."'";
	$result2=ect_query($sSQL) or ect_error();
	if($rs2=ect_fetch_assoc($result2)) $secid=$rs2['sectionID']; else $secid=0;
	ect_free_result($result2);
	return($secid);
}
function getmanufacturer($swn){
	$sSQL = "SELECT scID FROM searchcriteria WHERE scGroup=0 AND scName='".escape_string($swn)."'";
	$result2=ect_query($sSQL) or ect_error();
	if($rs2=ect_fetch_assoc($result2)) $secid=$rs2['scID']; else $secid=0;
	ect_free_result($result2);
	return($secid);
}
if(getpost('posted')=="1"){
	// print '<meta http-equiv="refresh" content="2; url=admincsv.php">';
	error_reporting (E_ALL);
	@ini_set("display_errors",'On');
	$time_start = microtime_float();
	$csvfile = $_FILES['csvfile'];
	$manufacturerarray=array();
	$allmanufacturers='';
	$hasmanufacturer=FALSE;
	//foreach(@$csvfile as $objItem => $objValue){
	//	print $objItem . ":" . $objValue . "<br>";
	//}
	if($csvfile['error']=='1'){
		$max_size = ini_get('upload_max_filesize');
		$success=FALSE;
		$errmsg='CSV File was not uploaded successfully. This could be that it is larger than the maximum upload size or that the temporary upload directory is not writeable.';
		$errmsg.=' '.'The maximum upload filesize is ' . $max_size . '.';
	}else{
		if($csvfile['error']!=UPLOAD_ERR_OK) print "Upload error number: " . $csvfile['error'] . "<br />";
		ini_set('auto_detect_line_endings', true);
		$csvarray = file($csvfile['tmp_name']);
		if($csvarray===FALSE) print "Could not read CSV File<br>";
		$column_list = strtolower(str_replace('"','',trim($csvarray[0])));
		// pid,pname,pprice,pinstock,optid,optiongroup,options
		if($column_list=="imageproduct,imagesrc,imagetype,imagenumber")
			$isimagesupdate=TRUE;
		elseif($column_list=='pid,pname,pprice,pinstock,optid,optiongroup,option')
			$isstockupdate=TRUE;
		else{
			$column_list_copy = str_replace(' ','',$column_list);
			$column_list_copy = str_replace('plength','',$column_list_copy);
			$column_list_copy = str_replace('pwidth','',$column_list_copy);
			$column_list_copy = str_replace('pheight','',$column_list_copy);
			$column_list_copy = str_replace('pimage','',$column_list_copy);
			$column_list_copy = str_replace('plargeimage','',$column_list_copy);
			while($column_list_copy != str_replace(',,',',',$column_list_copy)){
				$column_list_copy = str_replace(',,',',',$column_list_copy);
			}
			ect_query('SELECT ' . $column_list_copy . " FROM products WHERE pID='abcwxyz'") or toplevel_database_error($column_list_copy);
		}
		if($success){
			$columnarray = explode(',', $column_list);
			$valuesarray = $columnarray;
			$columncount = count($columnarray);
			$iscontinue=FALSE;
			$columnnum=0;
			$keycolumn='';
			for($i=0; $i < $columncount; $i++){
				$columnarray[$i]=trim($columnarray[$i]);
				if($columnarray[$i]=='pid') $keycolumn=$i;
				if($columnarray[$i]=='pmanufacturer') $hasmanufacturer=TRUE;
			}
			if($keycolumn==='' && $isimagesupdate==FALSE){
				$success=FALSE;
				$errmsg="There was no pID column specified.";
			}
		}
	}
	if($success){
		if($isupdate)
			print '&nbsp;Updating row: ';
		else
			print '&nbsp;Adding row: ';
		if($hasmanufacturer){
			$sSQL="SELECT scID,scGroup,scName FROM searchcriteria WHERE scGroup=0";
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){
				$manufacturerarray[$rs['scID']]=$rs['scName'];
				$allmanufacturers.=$rs['scID'].' ';
			}
			$allmanufacturers=str_replace(' ',',',trim($allmanufacturers));
			ect_free_result($result);
		}
		foreach ($csvarray as $line_num => $line) {
			if($line_num != 0){
				$line.="\r";
				if(! $iscontinue) $gotquotes = FALSE;
				$iscontinue=FALSE;
				$linelen = strlen($line);
				$index=0;
				while($index < $linelen){
					if(! $gotquotes){
						if($line[$index]=='"'){
							$needquote=TRUE;
							$index++;
						}else
							$needquote=FALSE;
						$thiscol='';
						$gotquotes=TRUE;
					}
					$thechar = $line[$index];
					if(! $needquote){
						if($thechar != ',' && $thechar != "\r" && $thechar != "\n"){
							$thiscol.=$thechar;
						}else{
							if($thechar=="\r" || $thechar=="\n") $index = $linelen;
							if(strtolower($thiscol)=='null' || strtolower($thiscol)==''){
								if($columnarray[$columnnum]=='ptax' || $columnarray[$columnnum]=='pshipping' || $columnarray[$columnnum]=='pshipping2' || $columnarray[$columnnum]=='pweight' || $columnarray[$columnnum]=='pprice' || $columnarray[$columnnum]=='pwholesaleprice' || $columnarray[$columnnum]=='plistprice' || $columnarray[$columnnum]=='pexemptions' || $columnarray[$columnnum]=='pinstock' || $columnarray[$columnnum]=='porder' || $columnarray[$columnnum]=='pmanufacturer' || $columnarray[$columnnum]=='ptotrating' || $columnarray[$columnnum]=='pnumratings')
									$thiscol='0';
								else
									$thiscol='';
							}
							if($columnarray[$columnnum]=='psection'){
								if(!is_numeric($thiscol)) $thiscol = getsection($thiscol);
								$valuesarray[$columnnum++]=$thiscol;
							}elseif($columnarray[$columnnum]=='pmanufacturer'){
								if(!is_numeric($thiscol)) $thiscol = getmanufacturer($thiscol);
								$valuesarray[$columnnum++]=$thiscol;
							}elseif($columnarray[$columnnum]=='pdateadded' && strpos($thiscol, '-')===FALSE)
								$valuesarray[$columnnum++]=date('Y-m-d', parsedate($thiscol));
							else
								$valuesarray[$columnnum++]=$thiscol;
							// print "Adding col:" . $columnarray[$columnnum-1] . ": " . $valuesarray[$columnnum-1] . "<br>";
							$gotquotes=FALSE;
						}
					}elseif($thechar=='"'){
						if($index+1 < $linelen){
							if($line[$index+1]=='"'){
								$thiscol.='"';
								$index++;
							}else
								$needquote=FALSE;
						}else
							$needquote=FALSE;
					}else{
						$pos = strpos($line, '"', $index);
						if($pos===FALSE){
							$thiscol.=substr($line, $index, $linelen - $index);
							$index = $linelen-1;
						}else{
							$pos--;
							$thiscol.=substr($line, $index, $pos - ($index-1));
							$index+=$pos - $index;
						}
						if($index+1==$linelen) $iscontinue=TRUE; // continues over line
					}
					$index++;
				}
				if(! $iscontinue){
					$successlines++;
					$columnnum=0;
					execute_sql();
					if(($line_num % $progressevery)==0){
						print $line_num . ", ";
						flush();
					}
				}
			}
			if($stoppedonerror) break;
		}
		print $line_num . "</p>";
	}
	$time_end = microtime_float();
?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><?php
					if(! $success) print '<p>ERROR: ' . $errmsg . '</p>';
					if($isupdate || $isimagesupdate){
						print '<p>Rows successfully updated ' . $successlines . '</p>';
						if($faillines > 0) print '<p>Error rows ' . $faillines . '</p>';
						if($pidnotfoundlines > 0) print '<p>Rows unchanged or where pID not found ' . $pidnotfoundlines . '</p>';
					}else{
						print '<p>Rows successfully added ' . $successlines . '</p>';
						if($faillines > 0) print '<p>Error rows ' . $faillines . '</p>';
						if($pidnotfoundlines > 0) print '<p>Rows with duplicate product id (pID) ' . $pidnotfoundlines . '</p>';
					}
					print "<p>This page took: " . round($time_end - $time_start,4) . " seconds</p>";
					if($successlines + $faillines > 0) print "<p>That is " . round(($time_end - $time_start) / ($successlines + $faillines), 4) . " seconds per row</p>";
                ?></td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php if($stoppedonerror) print '<span style="color:#FF0000">' . $yyOpFai . '</span>'; else print $yyUpdSuc?></strong><br /><br /><br /><br />
                        Please <a href="admin.php"><strong><?php print $yyClkHer?></strong></a> for the admin home page or <a href="javascript:history.go(-1)"><strong><?php print $yyClkHer?></strong></a> to go back and try again.<br />
                        <br /><br />&nbsp;
                </td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}else{
?>
		  <form name="mainform" method="post" action="admincsv.php" enctype="multipart/form-data">
		  <input type="hidden" name="posted" value="1">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr>
				<td width="100%" align="center" colspan="2"><strong><?php print$yyCSVUpl?></strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print$yyCSVFlN?>:</strong></td>
				<td><input type="file" name="csvfile" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print$yyAct?>:</strong></td>
				<td><select name="theaction" size="1">
					<option value="add"><?php print$yyAddDB?></option>
					<option value="update"><?php print$yyUpdDB?></option>
					</select></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print$yyShwErr?>:</strong></td>
				<td><input type="checkbox" name="show_errors" value="ON" checked /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print$yyStpErr?>:</strong></td>
				<td><input type="checkbox" name="stop_errors" value="ON" checked /></td>
			  </tr>
			  <tr>
				<td width="100%" align="center" colspan="2">&nbsp;<br /><input type="submit" value="<?php print $yySubmit?>" /><br />&nbsp;</td>
			  </tr>
			  <tr> 
				<td width="100%" align="center" colspan="2"><br />
					  <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
			</table>
		  </form>
<?php
}
?>