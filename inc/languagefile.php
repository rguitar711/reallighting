<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property
//of Internet Business Solutions SL. Any use, reproduction, disclosure or copying
//of any kind without the express and written permission of Internet Business 
//Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$_SESSION['languageid']!='') $GLOBALS['languageid']=$_SESSION['languageid']; elseif(@$GLOBALS['languageid']=='') $GLOBALS['languageid']=1;
function lfect_query($ectsql){
	return(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->query($ectsql):mysql_query($ectsql));
}
function lfect_fetch_assoc($ectres){
	return(@$GLOBALS['ectdatabase']?$ectres->fetch_assoc():mysql_fetch_assoc($ectres));
}
function lfect_free_result($ectres){
	@$GLOBALS['ectdatabase']?$ectres->free_result():mysql_free_result($ectres);
}
if(@$orstorelang!='')
	$storelang=$orstorelang;
else{
	$result=lfect_query("SELECT storelang FROM admin WHERE adminid=1") or print(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->error:mysql_error());
	if($rs=lfect_fetch_assoc($result)){
		$storelangarr=explode('|',trim($rs['storelang']));
		$storelang=@$storelangarr[$GLOBALS['languageid']-1];
	}
	lfect_free_result($result);
}
if(@$isvsadmindir) $dirpath=''; else $dirpath='/vsadmin';
if(@$storelang=='') $storelang='en'; // de dk en es fr it nl pt
include '.' . $dirpath . '/inc/languagefile_'.$storelang.'.php';
?>