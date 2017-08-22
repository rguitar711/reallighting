<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
global $alreadygotadmin,$storeurl,$customeraccounturl,$pathtossl,$forceloginonhttps,$minicssaction,$enableclientlogin,$forceclientlogin,$thesessionid;
if(@$GLOBALS['xxCkCoVC']=='') $GLOBALS['xxCkCoVC']='Please click checkout to view your cart contents.';
$alreadygotadmin=getadminsettings();
$path_parts=pathinfo(@$_SERVER['PHP_SELF']);
if($path_parts['dirname']=='/'||$path_parts['dirname']=='\\')$path_parts['dirname']='';
if(@$GLOBALS['cartpageonhttps']&&@$GLOBALS['pathtossl']!='') $pageurl=@$GLOBALS['pathtossl']; else $pageurl=$storeurl;
if(@$minicssaction=='onelineminicart' || @$minicssaction=='minicart' || @$minicssaction==''){
	if(trim(@$_POST['sessionid'])!='')
		$thesessionid=trim(@$_POST['sessionid']);
	else
		$thesessionid=getsessionid();
	$thesessionid=str_replace("'",'',$thesessionid);
	$useEuro=false;
	$mcgndtot=0;
	$totquant=0;
	$shipping=0;
	$mcdiscounts=0;
	if(@$_SESSION['xscountrytax']!='') $xscountrytax=$_SESSION['xscountrytax']; else $xscountrytax=0;
	$optPriceDiff=0;
	$mcpdtxt='';
	if(@$_POST['mode']=='checkout'){
		if(@$_POST['checktmplogin']!=''){
			$sSQL="SELECT tmploginname FROM tmplogin WHERE tmploginid='" . escape_string(@$_POST['sessionid']) . "' AND tmploginchk='" . escape_string(@$_POST['checktmplogin']) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result))
				$_SESSION['clientID']=$rs['tmploginname'];
		}else{
			$_SESSION['clientID']=NULL; unset($_SESSION['clientID']);
		}
	}
	$sSQL='SELECT cartID,cartProdID,cartProdName,cartProdPrice,cartQuantity FROM cart WHERE cartCompleted=0 AND ' . getsessionsql();
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		$optPriceDiff=0;
		$mcpdtxt.='<div class="minicartcnt">' . $rs['cartQuantity'] . ' ' . $rs['cartProdName'] . '</div>';
		$sSQL='SELECT SUM(coPriceDiff) AS sumDiff FROM cartoptions WHERE coCartID=' . $rs['cartID'];
		$result2=ect_query($sSQL) or ect_error();
		$rs2=ect_fetch_assoc($result2);
		if(! is_null($rs2['sumDiff'])) $optPriceDiff=$rs2['sumDiff'];
		ect_free_result($result2);
		$subtot=(($rs['cartProdPrice']+$optPriceDiff)*(int)$rs['cartQuantity']);
		$totquant+=(int)$rs['cartQuantity'];
		$mcgndtot+=$subtot;
	}
	ect_free_result($result);
}elseif(@$minicssaction=='minilogin' || @$minicssaction=='onelineminilogin'){
	$pageqs='';
	foreach(@$_GET as $objQS=>$objValue)
		if(!($objQS=='mode' && ($objValue=='login' || $objValue=='logout'))) $pageqs.=($pageqs!=''?'&':'').$objQS . '=' . $objValue;
	if(@$forceloginonhttps && (@$_SERVER['HTTPS']!='on' && @$_SERVER['SERVER_PORT']!='443') && (str_replace('http:','https:',@$storeurl)!=@$pathtossl)) $pagename=''; else $pagename=@$_SERVER['PHP_SELF'].($pageqs!=''?'?'.$pageqs:'');
}
if(@$minicssaction=='minilogin'){ ?>
<div class="minicart">
	<div class="minicartcnt">
	<img src="<?php print $path_parts['dirname']?>/images/minipadlock.png" style="vertical-align:text-top;" alt="<?php print $GLOBALS['xxMLLIS']?>" />
<?php	if(@$_SESSION['clientID']!='' && @$customeraccounturl!=''){ ?>
			&nbsp;<a class="ectlink mincart" href="<?php print $customeraccounturl?>"><?php print $GLOBALS['xxYouAcc']?></a>
<?php	}else{
            print '&nbsp;'.$GLOBALS['xxMLLIS'];
		} ?>
	</div>
<?php	if(@$enableclientlogin!=TRUE && @$forceclientlogin!=TRUE){ ?> 
	<div class="minicartcnt">Client login not enabled</div>
<?php	}elseif(@$_SESSION['clientID']!=''&&@$_GET['mode']!='logout'){ ?>
	<div class="minicartcnt"><?php print $GLOBALS['xxMLLIA']?><br /><?php print htmlspecials($_SESSION['clientUser'])?></div>
	<div class="minicartcnt">&raquo; <a class="ectlink mincart" href="<?php print $pageurl?>cart.php?mode=logout"><?php print $GLOBALS['xxLogout']?></a></div>
<?php	}else{ ?>
	<div class="minicartcnt"><?php print $GLOBALS['xxMLNLI']?></div>

	<div class="minicartcnt">&raquo; <a class="ectlink mincart" href="<?php print $pageurl?>cart.php?mode=login&amp;refurl=<?php print urlencode($pagename)?>"><?php print $GLOBALS['xxLogin']?></a></div>
<?php	} ?>
</div>
<?php
}elseif(@$minicssaction=='onelineminilogin'){ ?>
<div class="minicartoneline">
	<div class="minicartoneline1"><img src="<?php print $path_parts['dirname']?>/images/minipadlock.png" style="vertical-align:text-top;" alt="<?php print $GLOBALS['xxMLLIS']?>" />
<?php	if(@$_SESSION['clientID']!='' && @$customeraccounturl!=''){ ?>
			&nbsp;<a class="ectlink mincart" href="<?php print @$customeraccounturl?>"></a>
<?php	}else{ ?>
            &nbsp;
<?php	} ?></div>
<?php	if(@$enableclientlogin!=TRUE && @$forceclientlogin!=TRUE){ ?> 
		<div class="minicartoneline1">Client login not enabled</div>
<?php	}elseif(@$_SESSION['clientID']!=''&&@$_GET['mode']!='logout'){ ?>
	<div class="minicartoneline2"><?php print $GLOBALS['xxMLLIA'].' '.htmlspecials($_SESSION['clientUser'])?></div>
	<div class="minicartoneline3">&nbsp; &raquo; <a class="ectlink mincart" href="<?php print $pageurl?>cart.php?mode=logout"><?php print $GLOBALS['xxLogout']?></a></div>
<?php	}else{ ?>
	<div class="minicartoneline2"><?php print $GLOBALS['xxMLNLI']?></div>
	<div class="minicartoneline3">&nbsp; &raquo; <a class="ectlink mincart" href="<?php print $pageurl?>cart.php?mode=login&amp;refurl=<?php print urlencode($pagename)?>"><?php print $GLOBALS['xxLogin']?></a></div>
<?php	} ?>
</div>
<?php
}elseif(@$minicssaction=='minisignup'){
	if(@$_SESSION['MLSIGNEDUP']==TRUE || @$_POST['mode']=='mailinglistsignup'){
		print '<div class="minimailsignup">'.$GLOBALS['xxThkSub'].'</div>';
	}else{
		$therp=@$_SERVER['PHP_SELF'] . (@$_SERVER['QUERY_STRING'] !='' ? '?' . @$_SERVER['QUERY_STRING'] : '');
?>
<script type="text/javascript">/* <![CDATA[ */
function mlvalidator(frm){
	var mlsuemail=document.getElementById('mlsuname');
	if(mlsuemail.value==""){
		alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxName'])?>\".");
		mlsuemail.focus();
		return(false);
	}
	var mlsuemail=document.getElementById('mlsuemail');
	if(mlsuemail.value==""){
		alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxEmail'])?>\".");
		mlsuemail.focus();
		return(false);
	}
	var regex=/[^@]+@[^@]+\.[a-z]{2,}$/i;
	if(!regex.test(mlsuemail.value)){
		alert("<?php print jscheck($GLOBALS['xxValEm'])?>");
		mlsuemail.focus();
		return(false);
	}
	document.getElementById('mlsectgrp1').value=(document.getElementById('mlsuemail').value.split('@')[0].length);
	document.getElementById('mlsectgrp2').value=(document.getElementById('mlsuemail').value.split('@')[1].length);
	return (true);
}
/* ]]> */
</script>
<div class="minimailsignup">
	<form action="cart.php" method="post" onsubmit="return mlvalidator(this)">
		<input type="hidden" name="mode" value="mailinglistsignup" />
		<input type="hidden" name="mlsectgrp1" id="mlsectgrp1" value="7418" />
		<input type="hidden" name="mlsectgrp2" id="mlsectgrp2" value="6429" />
		<input type="hidden" name="rp" value="<?php print str_replace('&','&amp;',str_replace(array('<','"'),'',$therp))?>" />
		<label class="minimailsignup"><?php print $GLOBALS['xxName']?></label>
		<input class="minimailsignup" type="text" name="mlsuname" id="mlsuname" value="" maxlength="50" />
		<label class="minimailsignup"><?php print $GLOBALS['xxEmail']?></label>
		<input class="minimailsignup" type="text" name="mlsuemail" id="mlsuemail" value="" maxlength="50" />
		<?php print imageorsubmit(@$imgmailformsubmit, $GLOBALS['xxSubmt'], 'minimailsignup minimailsubmit')?>
		<div class="spacer"></div>
		<input type="hidden" name="posted" value="1" />
	</form>
</div>
<?php
	}
}elseif(@$minicssaction=='onelineminicart'){
	if(@$_SESSION['discounts']!=''&&!@$GLOBALS['nopriceanywhere']) $mcdiscounts=(double)$_SESSION['discounts'];
?>
<div class="minicartoneline">
<?php	if(@$_POST['mode']=='movetocart'){ ?>  
	<div class="minicartoneline1"><?php print $GLOBALS['xxCkCoVC']?></div>
<?php	}elseif(@$_POST['mode']=='update'){ ?> 
	<div class="minicartoneline1"><?php print $GLOBALS['xxMainWn']?></div>
<?php	}else{ ?>
	<div class="minicartoneline1"><span class="ectMCquant"><?php print $totquant . '</span> ' . $GLOBALS['xxMCIIC'] ?> | </div>
	<div class="minicartoneline2"><?php print $GLOBALS['xxTotal'] . ' <span class="ectMCtot">' . FormatEuroCurrency($mcgndtot-$mcdiscounts)?></span> | </div>
<?php	} ?>
	<div class="minicartoneline3"> <img src="<?php print $path_parts['dirname']?>/images/littlecart1.png" style="vertical-align:text-top;" width="16" height="16" alt="<?php print $GLOBALS['xxMCSC']?>" /> &nbsp;<a class="ectlink mincart" href="<?php print $pageurl?>cart.php"><?php print $GLOBALS['xxMCSC']?></a></div>
</div>
<?php
}else{ ?>
<div class="minicart">
	<div class="minicartcnt">
	<img src="<?php print $path_parts['dirname']?>/images/littlecart1.png" style="vertical-align:text-top;" width="16" height="16" alt="<?php print $GLOBALS['xxMCSC']?>" /> &nbsp;<a class="ectlink mincart" href="<?php print $pageurl?>cart.php"><?php print $GLOBALS['xxMCSC']?></a>
	</div>
<?php	if(@$_POST['mode']=='movetocart'){ ?>  
	<div class="minicartcnt"><?php print $GLOBALS['xxCkCoVC']?></div>
<?php	}elseif(@$_POST['mode']=='update'){ ?> 
	<div class="minicartcnt"><?php print $GLOBALS['xxMainWn']?></div>
<?php	}else{ ?>
	<div class="minicartcnt"><span class="ectMCquant"><?php print $totquant . '</span> ' . $GLOBALS['xxMCIIC'] ?></div>
	<div class="mcLNitems"><?php print $mcpdtxt?></div>
<?php		if($mcpdtxt!='' && @$_SESSION['discounts']!='')$mcdiscounts=(double)$_SESSION['discounts']; ?>
	<div class="ecHidDsc minicartcnt"<?php if($mcdiscounts==0) print ' style="display:none"'?>><span class="minicartdsc"><?php print $GLOBALS['xxDscnts'] . ' <span class="mcMCdsct">' . FormatEuroCurrency($mcdiscounts)?></span></span></div>
<?php			if($mcpdtxt!='' && (string)@$_SESSION['xsshipping']!=''){
					$shipping=(double)$_SESSION['xsshipping'];
					if($shipping==0) $showshipping=' minicartdsc">'.$GLOBALS['xxFree']; else $showshipping='">'.FormatEuroCurrency($shipping); ?>
   	<div class="minicartcnt"><?php print $GLOBALS['xxMCShpE'] . ' <span class="ectMCship' . $showshipping.'</span>'?></div>
<?php		}
			if($mcpdtxt=='') $xscountrytax=0;
			if(!@$GLOBALS['nopriceanywhere']){ ?>
	<div class="minicartcnt"><?php print $GLOBALS['xxTotal'] . ' <span class="ectMCtot">' . FormatEuroCurrency(($mcgndtot+$shipping+$xscountrytax)-$mcdiscounts)?></span></div>
<?php		}
		} ?>
	<div class="minicartcnt">&raquo; <a class="ectlink mincart" href="<?php print $pageurl?>cart.php"><?php print $GLOBALS['xxMCCO']?></a></div>
</div>
<?php
} ?>