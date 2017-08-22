<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(trim(@$_GET['id1'])!='' && trim(@$_GET['id2'])!=''){
setcookie('id1',@$_GET['id1'],time()+16000000, '/', '', @$_SERVER['HTTPS']=='on');
setcookie('id2',@$_GET['id2'],time()+16000000, '/', '', @$_SERVER['HTTPS']=='on');
}elseif(trim(@$_GET['PARTNER'])!=''){
setcookie('PARTNER',trim(@$_GET['PARTNER']),time()+(60*60*24*(int)@$_GET['EXPIRES']), '/');
}elseif(trim(@$_GET['DELCK'])=='yes'){
setcookie('WRITECKL', '', (time() - 2592000), '/', '', 0);
setcookie('WRITECKP', '', (time() - 2592000), '/', '', 0);
}elseif(trim(@$_GET['WRITECLL'])!=''){
	$thetimelim=0;
	if(trim(@$_GET['permanent'])=='Y')
		$thetimelim = (time()+(60*60*24*365));
	setcookie('WRITECLL', trim(@$_GET['WRITECLL']), $thetimelim, '/', '', @$_SERVER['HTTPS']=='on');
	setcookie('WRITECLP', trim(@$_GET['WRITECLP']), $thetimelim, '/', '', @$_SERVER['HTTPS']=='on');
}elseif(trim(@$_GET['DELCLL'])!=''){
	setcookie('WRITECLL', '', time() - 2592000, '/', '', 0);
	setcookie('WRITECLP', '', time() - 2592000, '/', '', 0);
}
flush();
?>
