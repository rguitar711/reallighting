<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(!@$GLOBALS['incfunctionsdefined']){print 'No incfunctions.php file';exit;}
global $alreadygotadmin;
include './vsadmin/inc/incemail.php';
$ordGrandTotal=$ordTotal=$ordStateTax=$ordHSTTax=$ordCountryTax=$ordShipping=$ordHandling=$ordDiscount=0;
$ordID=$affilID=$ordCity=$ordState=$ordCountry=$ordDiscountText=$ordEmail='';
if(@$_SERVER['CONTENT_LENGTH']!='' && $_SERVER['CONTENT_LENGTH'] > 10000) exit;
if(@$dateformatstr=='') $dateformatstr='m/d/Y';
$success=true;
$digidownloads=false;
$alreadygotadmin=getadminsettings();
if(getpost('posted')=='1'){
	$email=escape_string(getpost('email'));
	$ordid=escape_string(getpost('ordid'));
	if(! is_numeric($ordid)){
		$success=false;
		$errormsg=$GLOBALS['xxStaEr1'];
	}elseif($email!='' && $ordid!=''){
		$sSQL='SELECT ordStatus,ordStatusDate,'.getlangid('statPublic',64).',ordTrackNum,ordAuthNumber,ordStatusInfo FROM orders INNER JOIN orderstatus ON orders.ordStatus=orderstatus.statID WHERE ordID=' . $ordid . " AND ordEmail='" . $email . "'";
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result)>0){
			$rs=ect_fetch_assoc($result);
			$ordStatus=$rs['ordStatus'];
			$ordStatusDate=strtotime($rs['ordStatusDate']);
			$statPublic=$rs[getlangid('statPublic',64)];
			$ordAuthNumber=trim($rs['ordAuthNumber']);
			$ordStatusInfo=trim($rs['ordStatusInfo']);
			$ordTrackNum=trim($rs['ordTrackNum']);
			if(@$trackingnumtext=='') $trackingnumtext=$GLOBALS['xxTrackT'];
			if($ordTrackNum!='') $trackingnum=str_replace('%s', $ordTrackNum, $trackingnumtext); else $trackingnum='';
		}else{
			$success=false;
			$errormsg=$GLOBALS['xxStaEr2'];
		}
		ect_free_result($result);
	}else{
		$success=false;
		$errormsg=$GLOBALS['xxStaEnt'];
	}
}
?>		<form method="post" name="statusform" action="orderstatus.php">
		  <input type="hidden" name="posted" value="1" />
			<div class="ectdiv ectorderstatus">
<?php	if(getpost('posted')=='1' && $success){ ?>
			  <div class="ectdivhead"><?php print $GLOBALS['xxStaVw']?></div>
			  <div class="ectdiv2column"><?php print $GLOBALS['xxStaCur'] . " " . $ordid?></div>
			  <div class="ectdivcontainer">
			    <div class="ectdivleft"><?php print $GLOBALS['xxStatus']?></div>
				<div class="ectdivright"><?php print $statPublic?></div>
			  </div>
			  <div class="ectdivcontainer">
			    <div class="ectdivleft"><strong><?php print $GLOBALS['xxDate']?></div>
				<div class="ectdivright"><?php print date($dateformatstr, $ordStatusDate)?></div>
			  </div>
			  <div class="ectdivcontainer">
			    <div class="ectdivleft"><strong><?php print $GLOBALS['xxTime']?></div>
				<div class="ectdivright"><?php print date("H:i", $ordStatusDate)?></div>
			  </div>
<?php		if($trackingnum!=''){ ?>
			  <div class="ectdivcontainer">
			    <div class="ectdivleft"><?php print $GLOBALS['xxTraNum']?></div>
				<div class="ectdivright"><?php print $trackingnum?></div>
			  </div>
<?php		}
			if($ordStatusInfo!=''){ ?>
			  <div class="ectdivcontainer">
			    <div class="ectdivleft"><?php print $GLOBALS['xxAddInf']?></div>
				<div class="ectdivright"><?php print $ordStatusInfo?></div>
			  </div>
<?php		}
			if($ordAuthNumber!=''){ ?>
			  <div class="ectdiv2column"><?php
					$GLOBALS['xxThkYou']='';
					$GLOBALS['xxRecEml']='';
					do_order_success($ordid,'',FALSE,TRUE,FALSE,FALSE,FALSE) ?>
			  </div>
<?php		}
		}else{ ?>
			  <div class="ectdivhead"><?php print $GLOBALS['xxStaVw']?></div>
<?php	} ?>
			  <div class="ectdiv2column"><?php print $GLOBALS['xxStaEnt']?></div>
<?php	if($success==false){ ?>
			  <div class="ectdivcontainer">
			    <div class="ectdivleft"><?php print $GLOBALS['xxStaErr']?></div>
				<div class="ectdivright ectwarning"><?php print $errormsg?></div>
			  </div>
<?php	} ?>
			  <div class="ectdivcontainer">
			    <div class="ectdivleft"><?php print $GLOBALS['xxOrdId']?></div>
				<div class="ectdivright"><input type="text" size="20" name="ordid" value="<?php print htmlspecials(getpost('ordid'))?>" /></div>
			  </div>
			  <div class="ectdivcontainer">
			    <div class="ectdivleft"><?php print $GLOBALS['xxEmail']?></div>
				<div class="ectdivright"><input type="text" size="30" name="email" value="<?php print htmlspecials(getpost('email'))?>" /></div>
			  </div>
			  <div class="ectdiv2column"><?php print imageorsubmit(@$imgvieworderstatus,$GLOBALS['xxStaVw'],'vieworderstatus')?></div>
			</div>
		  </form>