<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
global $alreadygotadmin,$customeraccounturl,$pathtossl,$forceloginonhttps,$storeurl;
$alreadygotadmin = getadminsettings();
$pageqs='';
foreach(@$_GET as $objQS=>$objValue)
	if(!($objQS=='mode' && ($objValue=='login' || $objValue=='logout'))) $pageqs.=($pageqs!=''?'&':'').$objQS . '=' . $objValue;
if(@$forceloginonhttps && (@$_SERVER['HTTPS']!='on' && @$_SERVER['SERVER_PORT']!='443') && (str_replace('http:','https:',@$storeurl)!=@$pathtossl)) $pagename=''; else $pagename=@$_SERVER['PHP_SELF'].($pageqs!=''?'?'.$pageqs:'');
$path_parts = pathinfo(@$_SERVER['PHP_SELF']);
if($path_parts['dirname']=='/'||$path_parts['dirname']=='\\')$path_parts['dirname']='';
?>
      <table class="mincart" width="130" bgcolor="#FFFFFF">
        <tr> 
          <td class="mincart" bgcolor="#F0F0F0" align="center"><img src="<?php print $path_parts['dirname']?>/images/minipadlock.png" style="vertical-align:text-top;" alt="<?php print $GLOBALS['xxMLLIS']?>" />
<?php		if(@$_SESSION['clientID']!='' && @$customeraccounturl!=''){ ?>
			&nbsp;<a class="ectlink mincart" href="<?php print $customeraccounturl?>"><strong><?php print $GLOBALS['xxYouAcc']?></strong></a>
<?php		}else{ ?>
            &nbsp;<strong><?php print $GLOBALS['xxMLLIS']?></strong>
<?php		} ?>
		  </td>
        </tr>
<?php	if(@$GLOBALS['enableclientlogin']!=TRUE && @$$GLOBALS['forceclientlogin']!=TRUE){ ?>
		<tr>
		  <td class="mincart" bgcolor="#F0F0F0" align="center">
		  <p class="mincart">Client login not enabled</p>
		  </td>
		</tr>
<?php	}elseif(@$_SESSION['clientID']!=''&&@$_GET['mode']!='logout'){ ?>
		<tr>
		  <td class="mincart" bgcolor="#F0F0F0" align="center">
		  <p class="mincart"><?php print $GLOBALS['xxMLLIA']?><strong><br /><?php print htmlspecials($_SESSION['clientUser'])?></strong></p>
		  </td>
		</tr>
		<tr> 
          <td class="mincart" bgcolor="#F0F0F0" align="center"><span style="font-family:Verdana">&raquo;</span> <a class="ectlink mincart" href="<?php print $storeurl?>cart.php?mode=logout"><strong><?php print $GLOBALS['xxLogout']?></strong></a></td>
        </tr>
<?php	}else{ ?>
		<tr>
		  <td class="mincart" bgcolor="#F0F0F0" align="center">
		  <p class="mincart"><?php print $GLOBALS['xxMLNLI']?></p>
		  </td>
		</tr>
		<tr> 
          <td class="mincart" bgcolor="#F0F0F0" align="center"><span style="font-family:Verdana">&raquo;</span> <a class="ectlink mincart" href="<?php print $storeurl?>cart.php?mode=login&amp;refurl=<?php print urlencode($pagename)?>"><strong><?php print $GLOBALS['xxLogin']?></strong></a></td>
        </tr>
<?php	} ?>
      </table>