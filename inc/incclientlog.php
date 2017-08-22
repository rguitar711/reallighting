<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=='') $storesessionvalue='virtualstore'.time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
$cartisincluded=TRUE;
include './inc/inccart.php';
$success=TRUE;
if(@$dateadjust=='') $dateadjust=0;
if(@$dateformatstr=='') $dateformatstr = 'm/d/Y';
$admindatestr='Y-m-d';
if(@$admindateformat=='') $admindateformat=0;
if($admindateformat==1)
	$admindatestr='m/d/Y';
elseif($admindateformat==2)
	$admindatestr='d/m/Y';
$alreadygotadmin = getadminsettings();
$sSQL = '';
$dorefresh=FALSE;
if(@$maxloginlevels=='') $maxloginlevels=5;
if(getpost('oldcntryname')!='' && getpost('newcntryname')!=''){
	if(getpost('newcntryname')=='xxxdeletexxx')
		ect_query("DELETE FROM address WHERE addCountry='".escape_string(getpost('oldcntryname'))."'");
	else
		ect_query("UPDATE address SET addCountry='".escape_string(getpost("newcntryname"))."' WHERE addCountry='".escape_string(getpost('oldcntryname'))."'") or ect_error();
}elseif(getpost('posted')=='1'){
    
    
    
    
    
    
    
    
    
    //DELETE Customer from customerlogin table
    

	if(getpost('act')=='delete'){
		$sSQL = "DELETE FROM customerlogin WHERE clID='" . getpost('id') . "'";
		ect_query($sSQL) or ect_error();
		$sSQL = "DELETE FROM address WHERE addCustID='" . getpost('id') . "'";
		ect_query($sSQL) or ect_error();
		$sSQL = "UPDATE orders SET ordClientID=0 WHERE ordClientID='" . getpost('id') . "'";
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}elseif(getpost('act')=='deleteaddress'){
		$sSQL = "DELETE FROM address WHERE addID='" . getpost('theid') . "'";
		ect_query($sSQL) or ect_error();
	}elseif(getpost('act')=='doeditaddress' || getpost('act')=='donewaddress'){
		$addID=str_replace("'",'',getpost('theid'));
		if(!is_numeric($addID))$addID=0;
		$ordName=getpost('name');
		$ordLastName=getpost('lastname');
		$ordAddress=getpost('address');
		$ordAddress2=getpost('address2');
		$ordState=getpost('state2');
		if(getpost('state')!='')
			$ordState = getpost('state');
		$ordCity=getpost('city');
		$ordZip=getpost('zip');
		$ordPhone=getpost('phone');
		$ordCountry=getcountryfromid(getpost('country'));
		$ordExtra1=getpost('ordextra1');
		$ordExtra2=getpost('ordextra2');
		if(getpost('act')=='doeditaddress')
			$sSQL = "UPDATE address SET addName='" . escape_string($ordName) . "',addLastName='" . escape_string($ordLastName) . "',addAddress='" . escape_string($ordAddress) . "',addAddress2='" . escape_string($ordAddress2) . "',addCity='" . escape_string($ordCity) . "',addState='" . escape_string($ordState) . "',addZip='" . escape_string($ordZip) . "',addCountry='" . escape_string($ordCountry) . "',addPhone='" . escape_string($ordPhone) . "',addExtra1='" . escape_string($ordExtra1) . "',addExtra2='" . escape_string($ordExtra2) . "' WHERE addID=" . $addID;
		else
			$sSQL = "INSERT INTO address (addCustID,addIsDefault,addName,addLastName,addAddress,addAddress2,addCity,addState,addZip,addCountry,addPhone,addExtra1,addExtra2) VALUES (" . getpost('id') . ",0,'" . escape_string($ordName) . "','" . escape_string($ordLastName) . "','" . escape_string($ordAddress) . "','" . escape_string($ordAddress2) . "','" . escape_string($ordCity) . "','" . escape_string($ordState) . "','" . escape_string($ordZip) . "','" . escape_string($ordCountry) . "','" . escape_string($ordPhone) . "','" . escape_string($ordExtra1) . "','" . escape_string($ordExtra2) . "')";
		ect_query($sSQL) or ect_error();
	}elseif(getpost('act')=='domodify'){
		$sSQL = "SELECT clEmail FROM customerlogin WHERE clID<>'" . getpost('id') . "' AND clEmail='" . escape_string(getpost('clEmail')) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$success=FALSE;
			$errmsg=$yyEmReg . '<br />' . htmlspecials(getpost('clEmail'));
		}
		ect_free_result($result);
		if(getpost('clUserName')==''){
			$success=FALSE;
			$errmsg='Username is NULL';
		}
                
                
                // Vendor and procurement database operations
		if($success){
                    
                                
                    if(isset($_POST['INT_CO_CODE']) && !empty($_POST['INT_CO_CODE'])){
                    
                    //check for empty dropdown if property code text box is filled
                     if(!isset($_POST['ddl_vendor']) || empty($_POST['ddl_vendor']) )
                        {
                    
                            //redirect2("http://".$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI].'?custID='.$_POST["custID"].'&FormMode=Edit&Error=2');
                          
                    
                        }
                        else
                        {
                            $vendorId = $_POST['ddl_vendor'];
                            
                            if($vendorId == '1')
                            {
                                $checkSql = "Select propertyCode from customerlogin where propertyCode = '" .@$_POST['INT_CO_CODE']."' and clID <> " . getpost('id');
                            }
                            if($vendorId == '2')
                            {
                                $checkSql = "Select INT_CO_CODE from customerlogin where INT_CO_CODE = '" .@$_POST['INT_CO_CODE']."' and clID <> " . getpost('id');   
                            }
                            //Coupa
                            if($vendorId == '3')
                            {
                                $checkSql = "Select fce_property_code from customerlogin where fce_property_code = '" .@$_POST['INT_CO_CODE']."' and clID <> " .getpost('id');  
                            }
                           
                        }
                    
                    
		//check for dupLicate of property code
		$resultCheck = ect_query($checkSql)  or ect_error();
                
                        if(ect_num_rows($resultCheck)>0){

                        //redirect2("http://".$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI].'?custID='.$_POST["custID"].'&FormMode=Edit&Error=1');

                        }
                        else
                        {
                                $vendorId = $_POST['ddl_vendor'];
                                    //nexus
                                    if($vendorId == '1')
                                    {

                                        $updateCode = "update customerlogin set propertyCode = '" .@$_POST['INT_CO_CODE']."', INT_CO_CODE = '', fce_property_code='' where clID ="  . getpost('id');
                                    }
                                    //birchstreet
                                    if($vendorId == '2')
                                    {

                                        $updateCode = "update customerlogin set INT_CO_CODE = '" .@$_POST['INT_CO_CODE']."',propertyCode = '',fce_property_code='' where clID="  . getpost('id');

                                    }
                                    
                                      if($vendorId == '3')
                                    {

                                        $updateCode = "update customerlogin set fce_property_code = '" .@$_POST['INT_CO_CODE']."',propertyCode = '', INT_CO_CODE='' where clID ="  . getpost('id');
                                          
                                    }



                                $resultCheck = ect_query($updateCode)  or ect_error();
                        }
                 }
                 
                 
                 //UPDATE Customer on customerlogin table
                 
                   
                        //$myq = "update customers set propertyCode = '" .@$_POST['INT_CO_CODE']."', INT_CO_CODE = '', fce_property_code='' where custId ="  . getpost('id');
                            
                        //ect_query($myq) or ect_error();
                         //echo '<script type="text/javascript"> alert("'.$myq.'"); </script>';
			$sSQL = "UPDATE customerlogin SET clUserName='" . escape_string(getpost('clUserName')) . "'";
			if(getpost('clPW')!='') $sSQL.=",clPW='" . escape_string(dohashpw(getpost('clPW'))) . "'";
			$sSQL.=",clLoginLevel=" . getpost('clLoginLevel');
			$sSQL.=",loyaltyPoints=" . (is_numeric(getpost('loyaltyPoints'))?getpost('loyaltyPoints'):0);
			$cpd = getpost('clPercentDiscount');
			$sSQL.=",clPercentDiscount=" . (is_numeric($cpd) ? $cpd : 0);
			if(trim(@$extraclientfield1)!='') $sSQL.=",clientCustom1='" . escape_string(getpost('clientCustom1')) . "'";
			if(trim(@$extraclientfield2)!='') $sSQL.=",clientCustom2='" . escape_string(getpost('clientCustom2')) . "'";
			$sSQL.=",clientAdminNotes='" . escape_string(getpost('clientAdminNotes')) . "'";
			$sSQL.=",clEmail='" . escape_string(getpost('clEmail')) . "'";
			$clActions=0;
			if(is_array(@$_POST['clActions'])){
				foreach(@$_POST['clActions'] as $objValue){
					if(is_array($objValue)) $objValue = $objValue[0];
					$clActions+=$objValue;
				}
			}
			$sSQL.=",clActions=" . $clActions;
			$sSQL.=" WHERE clID='" . getpost('id') . "'";
			ect_query($sSQL) or ect_error();
                       
			if(getpost('clAllowEmail')=='ON')
				@ect_query("INSERT INTO mailinglist (email,isconfirmed,mlConfirmDate,mlIPAddress) VALUES ('" . escape_string(strtolower(getpost('clEmail'))) . "',1,'".date('Y-m-d', time())."','".escape_string(getipaddress())."')");
			else
				ect_query("DELETE FROM mailinglist WHERE email='" . escape_string(getpost('clEmail')) . "'");
			$dorefresh=TRUE;
                       
		}
                
                
                
                
                
                
                
                
                
                
                
                
                
                 //ADD New Customer to customerlogin table 
                
	}elseif(getpost('act')=='doaddnew'){
		$sSQL = "SELECT clEmail FROM customerlogin WHERE clEmail='" . escape_string(getpost('clEmail')) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$success=FALSE;
			$errmsg=$yyEmReg . '<br />' . htmlspecials(getpost('clEmail'));
		}
		ect_free_result($result);
		if(getpost('clUserName')==''){
			$success=FALSE;
			$errmsg='Username is NULL';
		}
                
                
              
                
		if($success){
			$sSQL = "INSERT INTO customerlogin (clUserName,clPW,clLoginLevel,loyaltyPoints,clPercentDiscount,clientCustom1,clientCustom2,clientAdminNotes,clEmail,clDateCreated,clActions) VALUES (";
			$sSQL.="'" . escape_string(getpost('clUserName')) . "'";
			$sSQL.=",'" . escape_string(dohashpw(getpost('clPW'))) . "'";
			$sSQL.="," . getpost('clLoginLevel');
			$sSQL.="," . (is_numeric(getpost('loyaltyPoints'))?getpost('loyaltyPoints'):0);
			$cpd = getpost('clPercentDiscount');
			$sSQL.="," . (is_numeric($cpd) ? $cpd : 0);
			$sSQL.=",'" . escape_string(getpost('clientCustom1')) . "'";
			$sSQL.=",'" . escape_string(getpost('clientCustom2')) . "'";
			$sSQL.=",'" . escape_string(getpost('clientAdminNotes')) . "'";
			$sSQL.=",'" . escape_string(getpost('clEmail')) . "'";
			$sSQL.=",'" . date('Y-m-d', time() + ($dateadjust*60*60)) . "'";
			$clActions=0;
			if(is_array(@$_POST['clActions'])){
				foreach(@$_POST['clActions'] as $objValue){
					if(is_array($objValue)) $objValue = $objValue[0];
					$clActions+=$objValue;
				}
			}
			$sSQL.=',' . $clActions . ')';
			ect_query($sSQL) or ect_error();
                        
                        
                     
                        
                        
                        
			if(getpost('clAllowEmail')=='ON')
				ect_query("INSERT INTO mailinglist (email,isconfirmed,mlConfirmDate,mlIPAddress) VALUES ('" . escape_string(strtolower(getpost('clEmail'))) . "',1,'".date('Y-m-d', time())."','".escape_string(getipaddress())."')");
			else
				ect_query("DELETE FROM mailinglist WHERE email='" . escape_string(getpost('clEmail')) . "'");
			$dorefresh=TRUE;
		}
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
	}elseif(getpost('act')=='addorphans'){
		$sSQL = "SELECT clEmail FROM customerlogin WHERE clID='" . getpost('id') . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$theemail = $rs['clEmail'];
		}
		ect_free_result($result);
		if(@$loyaltypoints!=''){
			$loyaltypointtotal=0;
			$sSQL = "SELECT SUM(loyaltyPoints) AS pointsSum FROM orders WHERE ordClientID=0 AND ordEmail='" . escape_string($theemail) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result))
				if($rs['pointsSum']!=NULL) $loyaltypointtotal = $rs['pointsSum'];
			ect_free_result($result);
			$sSQL = "UPDATE customerlogin SET loyaltyPoints=loyaltyPoints+" . $loyaltypointtotal . " WHERE clID='" . escape_string(getpost('id')) . "'";
			ect_query($sSQL) or ect_error();
		}
		$sSQL = "UPDATE orders SET ordClientID='".escape_string(getpost('id'))."' WHERE ordEmail='" . escape_string($theemail) . "'";
		ect_query($sSQL) or ect_error();
	}elseif(getpost('act')=='addorphan'){
		if(@$loyaltypoints!=''){
			$loyaltypointtotal=0;
			$sSQL = "SELECT loyaltyPoints FROM orders WHERE ordClientID=0 AND ordID='" . escape_string(getpost('theid')) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result))
				$loyaltypointtotal = $rs['loyaltyPoints'];
			ect_free_result($result);
			$sSQL = "UPDATE customerlogin SET loyaltyPoints=loyaltyPoints+" . $loyaltypointtotal . " WHERE clID='" . escape_string(getpost('id')) . "'";
			ect_query($sSQL) or ect_error();
		}
		$sSQL = "UPDATE orders SET ordClientID='".escape_string(getpost('id'))."' WHERE ordID='" . escape_string(getpost('theid')) . "'";
		ect_query($sSQL) or ect_error();
	}
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=adminclientlog.php';
	print '?stext=' . urlencode(getpost('stext')) . '&accdate=' . urlencode(getpost('accdate')) . '&slevel=' . urlencode(getpost('slevel')) . '&stype=' . urlencode(getpost('stype')) . '&daterange=' . urlencode(getpost('daterange')) . '&pg=' . urlencode(getpost('pg'));
	print '">';
}
?>
<script type="text/javascript">
<!--
function formvalidator(theForm){
if (theForm.clUserName.value==""){
alert("<?php print jscheck($yyPlsEntr . ' "' . $yyLiName)?>\".");
theForm.clUserName.focus();
return (false);
}
return (true);
}
function vieworder(theid){
	document.location="adminorders.php?id="+theid;
}
function editaddress(theid){
	document.forms.mainform.act.value="editaddress";
	document.forms.mainform.theid.value=theid;
	document.forms.mainform.submit();
}
function newaddress(){
	document.forms.mainform.act.value="newaddress";
	document.forms.mainform.submit();
}
function editaccount(){
	document.forms.mainform.act.value="modify";
	document.forms.mainform.submit();
}
function addorphan(theid){
	if(confirm("<?php print jscheck($yySureCa)?>")){
		document.forms.mainform.act.value="addorphan";
		document.forms.mainform.theid.value=theid;
		document.forms.mainform.submit();
	}
}
function addorphans(){
	if(confirm("<?php print jscheck($yySureCa)?>")){
		document.forms.mainform.act.value="addorphans";
		document.forms.mainform.submit();
	}
}
function deleteaddress(theid){
	if(confirm("<?php print jscheck($GLOBALS['xxDelAdd'])?>")){
		document.forms.mainform.act.value="deleteaddress";
		document.forms.mainform.theid.value=theid;
		document.forms.mainform.submit();
	}
}
//-->
</script>
<?php	if(getpost('posted')=='1' && (getpost('act')=='modify' || getpost('act')=='addnew')){
			if(getpost('act')=='modify' && is_numeric(getpost('id'))){
				$sSQL = "SELECT clUserName,clPW,clLoginLevel,clActions,clPercentDiscount,clEmail,clDateCreated,loyaltyPoints,clientCustom1,clientCustom2,clientAdminNotes FROM customerlogin WHERE clID='" . getpost('id') . "'";

				$result=ect_query($sSQL) or ect_error();
				$rs=ect_fetch_assoc($result);
				$clUserName=$rs['clUserName'];
				$clPW='';
				$clLoginLevel=$rs['clLoginLevel'];
				$clActions=$rs['clActions'];
				$clPercentDiscount=$rs['clPercentDiscount'];
				$clEmail=$rs['clEmail'];
				$clDateCreated=$rs['clDateCreated'];
				$clLoyaltyPoints=$rs['loyaltyPoints'];
				$clientCustom1=$rs['clientCustom1'];
				$clientCustom2=$rs['clientCustom2'];
				$clientAdminNotes=$rs['clientAdminNotes'];
				ect_free_result($result);
				$sSQL = "SELECT email FROM mailinglist WHERE email='" . escape_string($clEmail) . "'";
				$result=ect_query($sSQL) or ect_error();
				if(ect_num_rows($result)>0) $clAllowEmail=1; else $clAllowEmail=0;
				ect_free_result($result);
			}else{
				$clUserName='';
				$clPW='';
				$clLoginLevel=0;
				$clActions=0;
				$clPercentDiscount=0;
				$clEmail='';
				$clDateCreated=date('Y-m-d');
				$clAllowEmail=0;
				$clLoyaltyPoints=0;
				$clientCustom1='';
				$clientCustom2='';
				$clientAdminNotes='';
			}
                        
  ?>
	<form name="mainform" method="post" action="adminclientlog.php" onsubmit="return formvalidator(this)">
            
           
 
		
<?php		writehiddenvar('posted', '1');
			if(getpost('act')=='modify')
				writehiddenvar('act', 'domodify');
			else
				writehiddenvar('act', 'doaddnew');
			writehiddenvar('stext', getpost('stext'));
			writehiddenvar('accdate', getpost('accdate'));
			writehiddenvar('daterange', getpost('daterange'));
			writehiddenvar('slevel', getpost('slevel'));
			writehiddenvar('stype', getpost('stype'));
			writehiddenvar('pg', getpost('pg'));
			writehiddenvar('id', getpost('id')); ?>
            <table width="100%" border="0" cellspacing="2" cellpadding="2">
			  <tr> 
                            <td width="100%" colspan="4" align="center"><strong><?php print $yyLiAdm?></strong><br /><br /><?php print '<strong>' . $yyDateCr. ':</strong> ' . date($admindatestr, strtotime($clDateCreated)); ?><br /><br /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print 'Full Name'//print $yyLiName?>:</strong></td>
				<td align="left"><input type="text" name="clUserName" size="20" value="<?php print htmlspecials($clUserName)?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyEmail?>:</strong></td>
				<td align="left"><input type="text" name="clEmail" size="30" value="<?php print htmlspecials($clEmail)?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyReset.' '.$yyPass?>:</strong></td>
				<td align="left"><input type="text" name="clPW" size="20" value="" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyLiLev?>:</strong></td>
				<td align="left"><select name="clLoginLevel" size="1">
				<?php	for($rowcounter=0; $rowcounter<=$maxloginlevels; $rowcounter++){
							print '<option value="' . $rowcounter . '"';
							if($rowcounter==(int)$clLoginLevel) print ' selected="selected"';
							print '>&nbsp; ' . $rowcounter . " </option>\r\n";
						} ?>
				</select></td>
			  </tr>
                          
            <?php 
                       if($_POST['act'] != 'addnew'){
                            $strSQL = "SELECT * from customerlogin where clID = " . getpost('id');
                           
                            
                            $result2 = ect_query($strSQL) or ect_error();
                            $row=ect_fetch_assoc($result2);
            
                            if(!empty($row['INT_CO_CODE']) )
                            {
                                //this is birchsteet
                                $vendorType = $row['INT_CO_CODE'];
                                $selected = 2;
                            }
                            
                             elseif(!empty($row['propertyCode']) )
                            {
                                 //this is Nexus
                                $vendorType = $row['propertyCode'];
                                $selected = 1;
                            }
                            elseif(!empty($row['fce_property_code']))
                            {
                                 $vendorType = $row['fce_property_code'];
                                 $selected = 3;
                                
                                
                            }
                            else
                            {
                                $vendorType = "";
                            }
                        
                       
                            $sql = 'select id, company from procurement order by company';
                            $result2 =  ect_query($sql) or ect_error();
                            
                            
                        echo '<tr><td align="right"><strong>Vendor:</strong></td><td align="left">
                                <select id="ddl_vendor" name="ddl_vendor">
                                    <option value="0"></option>';
            
                   while($rw = ect_fetch_assoc($result2)){
                        if($selected == $rw['id']) {
                        echo "<option value='" .$rw['id']."' selected>" .$rw['company']."</option>";
                        }
                        else
                        {
                        echo "<option value='" .$rw['id']."'>" .$rw['company']."</option>";
                        }
                    }
                       
             
             
                              echo '</select></td></tr>';
                            echo  '<tr><td align="right"><strong>Property Code:</strong></td>';
                            echo '<td align="left">';
                            echo '<input type="text" name="INT_CO_CODE" value="'; if(isset($_POST['INT_CO_CODE'])){ echo $_POST['INT_CO_CODE'];} else { echo $vendorType;}; 
                            echo '" />';
                            echo '<span style="color:#FF0000;">'; print $warning; 
                            echo '</span></td></tr>';
                        }
             ?>
			  <tr>
				<td align="right" valign="top"><strong><?php print $yyActns?>:</strong></td>
				<td align="left" valign="top"><select name="clActions[]" size="6" multiple="multiple">
				<option value="1"<?php if(($clActions & 1)==1) print ' selected="selected"' ?>><?php print $yyExStat?></option>
				<option value="2"<?php if(($clActions & 2)==2) print ' selected="selected"' ?>><?php print $yyExCoun?></option>
				<option value="4"<?php if(($clActions & 4)==4) print ' selected="selected"' ?>><?php print $yyExShip?></option>
				<option value="32"<?php if(($clActions & 32)==32) print ' selected="selected"' ?>><?php print $yyExHand?></option>
				<option value="8"<?php if(($clActions & 8)==8) print ' selected="selected"' ?>><?php print $yyWholPr?></option>
				<option value="16"<?php if(($clActions & 16)==16) print ' selected="selected"' ?>><?php print $yyPerDis?></option>
				</select></td>
			  </tr>
                                                  
			  <tr>
				<td align="right"><strong><?php print $yyPerDis?>:</strong></td>
				<td align="left"><input type="text" name="clPercentDiscount" size="10" value="<?php print $clPercentDiscount?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyAllEml?>:</strong></td>
				<td align="left"><input type="checkbox" name="clAllowEmail" value="ON"<?php if($clAllowEmail!=0) print ' checked'?> /></td>
			  </tr>
<?php		if(@$loyaltypoints!=''){ ?>
			  <tr>
				<td align="right" height="22"><strong><?php print $GLOBALS['xxLoyPoi']?>:</strong></td>
				<td align="left"><input type="text" name="loyaltyPoints" size="10" value="<?php print $clLoyaltyPoints?>" /></td>
			  </tr>
<?php		}
			if(trim(@$extraclientfield1)!=''){ ?>
			  <tr>
				<td align="right" height="22"><strong><?php print $extraclientfield1?>:</strong></td>
				<td align="left"><input type="text" name="clientCustom1" size="30" value="<?php print $clientCustom1?>" /></td>
			  </tr>
<?php		}
			if(trim(@$extraclientfield2)!=''){ ?>
			  <tr>
				<td align="right" height="22"><strong><?php print $extraclientfield2?>:</strong></td>
				<td align="left"><input type="text" name="clientCustom2" size="30" value="<?php print $clientCustom2?>" /></td>
			  </tr>
<?php		} ?>
			  <tr>
				<td align="right" height="22"><strong>Client Admin Notes:</strong></td>
				<td align="left"><textarea name="clientAdminNotes" cols="60" rows="10"><?php print htmlspecials($clientAdminNotes)?></textarea></td>
			  </tr>
			  <tr>
                <td width="100%" colspan="4" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" />&nbsp;<input type="reset" value="<?php print $yyReset?>" /><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table>
	</form>
<?php	}elseif(getpost('act')=='editaddress' || getpost('act')=='newaddress'){
			$addID=str_replace("'",'',getpost('theid'));
			if(!is_numeric($addID))$addID=0;
			$addIsDefault='';
			$addName='';
			$addLastName='';
			$addAddress='';
			$addAddress2='';
			$addState='';
			$addCity='';
			$addZip='';
			$addPhone='';
			$addCountry='';
			$addExtra1='';
			$addExtra2='';
			$sSQL = 'SELECT stateID FROM states INNER JOIN countries ON states.stateCountryID=countries.countryID WHERE countryEnabled<>0 AND stateEnabled<>0 AND (loadStates=2 OR countryID=' . $origCountryID . ') ORDER BY stateCountryID,stateName';
			$result=ect_query($sSQL) or ect_error();
			$hasstates = (ect_num_rows($result)>0);
			ect_free_result($result);
			$sSQL = "SELECT countryName,countryOrder,".getlangid("countryName",8).",countryID,loadStates FROM countries WHERE countryEnabled=1 ORDER BY countryOrder DESC," . getlangid("countryName",8);
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){
				$allcountries[$numallcountries++]=$rs;
			}
			ect_free_result($result);
			for($index=0;$index<$numallcountries;$index++){
				if($allcountries[$index]['loadStates']==0){ $nonhomecountries=TRUE; break; }
			}
			if(! $nonhomecountries){
				for($index=0;$index<$numallcountries;$index++){
					if($allcountries[$index]['loadStates']>0){
						$sSQL = 'SELECT stateID FROM states WHERE stateEnabled<>0 AND stateCountryID=' . $allcountries[$index]['countryID'];
						$result=ect_query($sSQL) or ect_error();
						if(ect_num_rows($result)==0) $nonhomecountries=TRUE;
						ect_free_result($result);
						if($nonhomecountries) break;
					}
				}
			}
			if(getpost('act')=='editaddress'){
				$sSQL = "SELECT addID,addIsDefault,addName,addLastName,addAddress,addAddress2,addState,addCity,addZip,addPhone,addCountry,addExtra1,addExtra2 FROM address WHERE addID=" . $addID;
				$result=ect_query($sSQL) or ect_error();
				if($rs=ect_fetch_assoc($result)){
					$addIsDefault=$rs['addIsDefault'];
					$addName=$rs['addName'];
					$addLastName=$rs['addLastName'];
					$addAddress=$rs['addAddress'];
					$addAddress2=$rs['addAddress2'];
					$addState=$rs['addState'];
					$ordState=$addState;
					$addCity=$rs['addCity'];
					$addZip=$rs['addZip'];
					$addPhone=$rs['addPhone'];
					$addCountry=$rs['addCountry'];
					$addExtra1=$rs['addExtra1'];
					$addExtra2=$rs['addExtra2'];
				}
				ect_free_result($result);
			} ?>
	<form method="post" name="mainform" action="" onsubmit="return checkform(this)">
	<input type="hidden" name="act" value="<?php if(getpost('act')=='editaddress') print 'doeditaddress'; else print 'donewaddress' ?>" />
	<input type="hidden" name="theid" value="<?php print $addID?>" />
	<input type="hidden" name="id" value="<?php print getpost('id')?>" />
	<input type="hidden" name="posted" value="1" />
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr><td align="center" class="cobhl" colspan="2" height="32"><strong><?php print $GLOBALS['xxEdAdd']?></strong></td></tr>
		<?php	if(trim(@$extraorderfield1)!=''){ ?>
		<tr><td align="right" class="cobhl"><strong><?php print (@$extraorderfield1required==TRUE ? $redstar : '') . $extraorderfield1 ?>:</strong></td><td class="cobll"><?php if(@$extraorderfield1html!='') print $extraorderfield1html; else print '<input type="text" name="ordextra1" id="ordextra1" size="20" value="' . htmlspecials($addExtra1) . '" />'?></td></tr>
		<?php	} ?>
		<tr><td align="right" width="40%" class="cobhl"><strong><?php print $redstar . $GLOBALS['xxName']?>:</strong></td><td class="cobll"><?php
		if(@$usefirstlastname){
			$thestyle='';
			if($addName=='' && $addLastName==''){ $addName=$GLOBALS['xxFirNam']; $addLastName=$GLOBALS['xxLasNam']; $thestyle='style="color:#BBBBBB" '; }
			print '<input type="text" name="name" size="11" value="'.htmlspecials($addName).'" alt="'.$GLOBALS['xxFirNam'].'" onfocus="if(this.value==\''.$GLOBALS['xxFirNam'].'\'){this.value=\'\';this.style.color=\'\';}" '.$thestyle.'/> <input type="text" name="lastname" size="11" value="'.htmlspecials($addLastName).'" alt="'.$GLOBALS['xxLasNam'].'" onfocus="if(this.value==\''.$GLOBALS['xxLasNam'].'\'){this.value=\'\';this.style.color=\'\';}" '.$thestyle.'/>';
		}else
			print '<input type="text" name="name" id="name" size="20" value="'.htmlspecials($addName).'" />';
		?></td></tr>
		<tr><td align="right" class="cobhl"><strong><?php print $redstar . $GLOBALS['xxAddress']?>:</strong></td><td class="cobll"><input type="text" name="address" id="address" size="25" value="<?php print htmlspecials($addAddress)?>" /></td></tr>
		<?php	if(@$useaddressline2==TRUE){ ?>
		<tr><td align="right" class="cobhl"><strong><?php print $GLOBALS['xxAddress2']?>:</strong></td><td class="cobll"><input type="text" name="address2" id="address2" size="25" value="<?php print htmlspecials($addAddress2)?>" /></td></tr>
		<?php	} ?>
		<tr><td align="right" class="cobhl"><strong><?php print $redstar . $GLOBALS['xxCity']?>:</strong></td><td class="cobll"><input type="text" name="city" id="city" size="20" value="<?php print htmlspecials($addCity)?>" /></td></tr>
		<?php	if($hasstates || $nonhomecountries){ ?>
		<tr><td align="right" class="cobhl"><strong><?php print replace($redstar,'<span','<span id="statestar"')?><span id="statetxt"><?php print $GLOBALS['xxState']?></span>:</strong></td><td class="cobll"><select name="state" id="state" size="1" onchange="dosavestate('')"><?php $havestate = show_states($addState) ?></select><input type="text" name="state2" id="state2" size="20" value="<?php if(! $havestate) print htmlspecials($addState)?>" /></td></tr>
		<?php	} ?>
		<tr><td align="right" class="cobhl"><strong><?php print $redstar . $GLOBALS['xxCountry']?>:</strong></td><td class="cobll"><select name="country" id="country" size="1" onchange="checkoutspan('')" ><?php show_countries($addCountry,FALSE) ?></select></td></tr>
		<tr><td align="right" class="cobhl"><strong><?php print replace($redstar,'<span','<span id="zipstar"') . '<span id="ziptxt">' . $GLOBALS['xxZip'] . '</span>'?>:</strong></td><td class="cobll"><input type="text" name="zip" id="zip" size="10" value="<?php print htmlspecials($addZip)?>" /></td></tr>
		<tr><td align="right" class="cobhl"><strong><?php print $redstar . $GLOBALS['xxPhone']?>:</strong></td><td class="cobll"><input type="text" name="phone" id="phone" size="20" value="<?php print htmlspecials($addPhone)?>" /></td></tr>
		<?php	if(trim(@$extraorderfield2)!=''){ ?>
		<tr><td align="right" class="cobhl"><strong><?php print (@$extraorderfield2required==true ? $redstar : '') . $extraorderfield2 ?>:</strong></td><td class="cobll"><?php if(@$extraorderfield2html!='') print $extraorderfield2html; else print '<input type="text" name="ordextra2" id="ordextra2" size="20" value="' . htmlspecials($addExtra2) . '" />'?></td></tr>
		<?php	} ?>
		<tr><td align="center" colspan="2" class="cobll"><input type="submit" value="<?php print $GLOBALS['xxSubmt']?>" /> <input type="button" value="Cancel" onclick="history.go(-1)" /></td></tr>
	  </table>
	</form>
<script type="text/javascript">
/* <![CDATA[ */
var checkedfullname=false;
function zipoptional(cntobj){
var cntid=cntobj[cntobj.selectedIndex].value;
if(cntid==85 || cntid==91 || cntid==154 || cntid==200)return true; else return false;
}
function stateoptional(cntobj){
var cntid=cntobj[cntobj.selectedIndex].value;
if(false<?php
$result=ect_query('SELECT countryID FROM countries WHERE countryEnabled<>0 AND loadStates<0') or ect_error();
while($rs=ect_fetch_assoc($result)) print '||cntid==' . $rs['countryID'];
ect_free_result($result);
?>)return true; else return false;
}
function checkform(frm)
{
<?php if(trim(@$extraorderfield1)!='' && @$extraorderfield1required==true){ ?>
if(frm.ordextra1.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $extraorderfield1)?>\".");
	frm.ordextra1.focus();
	return (false);
}
<?php } ?>
if(frm.name.value==""||frm.name.value=="<?php print $GLOBALS['xxFirNam']?>"){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . @$usefirstlastname ? $GLOBALS['xxFirNam'] : $GLOBALS['xxName'])?>\".");
	frm.name.focus();
	return (false);
}
<?php	if(@$usefirstlastname){ ?>
if(frm.lastname.value==""||frm.lastname.value=="<?php print $GLOBALS['xxLasNam']?>"){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxLasNam'])?>\".");
	frm.lastname.focus();
	return (false);
}
<?php	} ?>
if(frm.address.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxAddress'])?>\".");
	frm.address.focus();
	return (false);
}
if(frm.city.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxCity'])?>\".");
	frm.city.focus();
	return (false);
}
if(stateoptional(document.getElementById('country'))){
	}else if(stateselectordisabled[0]==false){
<?php
	if($hasstates){ ?>
	if(frm.state.selectedIndex==0){
		alert("<?php print jscheck($GLOBALS['xxPlsSlct']) . ' '?>" + document.getElementById('statetxt').innerHTML);
		frm.state.focus();
		return(false);
	}
<?php	} ?>
	}else{
<?php	if($nonhomecountries){ ?>
	if(frm.state2.value==""){
		alert("<?php print jscheck($GLOBALS['xxPlsEntr'])?> \"" + document.getElementById('statetxt').innerHTML + "\".");
		frm.state2.focus();
		return(false);
	}
<?php	} ?>}
if(frm.zip.value=="" && ! zipoptional(document.getElementById('country'))){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxZip'])?>\".");
	frm.zip.focus();
	return(false);
}
if(frm.phone.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxPhone'])?>\".");
	frm.phone.focus();
	return (false);
}
<?php if(trim(@$extraorderfield2)!='' && @$extraorderfield2required==TRUE){ ?>
if(frm.ordextra2.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $extraorderfield2)?>\".");
	frm.ordextra2.focus();
	return (false);
}
<?php } ?>
return (true);
}
<?php if(@$termsandconditions==TRUE){ ?>
function showtermsandconds(){
newwin=window.open("termsandconditions.php","Terms","menubar=no, scrollbars=yes, width=420, height=380, directories=no,location=no,resizable=yes,status=no,toolbar=no");
}
<?php } ?>
var savestate=0;
var ssavestate=0;
function dosavestate(shp){
	thestate = eval('document.forms.mainform.'+shp+'state');
	eval(shp+'savestate = thestate.selectedIndex');
}
function checkoutspan(shp){
	document.getElementById(shp+'zipstar').style.display=(zipoptional(document.getElementById(shp+'country'))?'none':'');
	document.getElementById(shp+'statestar').style.display=(stateoptional(document.getElementById(shp+'country'))?'none':'');<?php
	if($hasstates){
		print "thestate=document.getElementById(shp+'state');\r\n";
		print "dynamiccountries(document.getElementById(shp+'country'),shp);\r\n";
	}
	print "if(stateselectordisabled[shp=='s'?1:0]==false&&!stateoptional(document.getElementById(shp+'country'))){\r\n";
	print "if(document.getElementById(shp+'state2'))document.getElementById(shp+'state2').style.display='none';\r\n";
	if($hasstates){
		print "thestate.disabled=false;\r\n";
		print "eval('thestate.selectedIndex='+shp+'savestate');\r\n";
		print "document.getElementById(shp+'state').style.display='';\r\n";
	} ?>
}else{<?php
	print "if(document.getElementById(shp+'state2'))document.getElementById(shp+'state2').style.display='';\r\n";
	if($hasstates){ ?>
		document.getElementById(shp+'state').style.display='none';
		if(thestate.disabled==false){
		thestate.disabled=true;
		eval(shp+'savestate = thestate.selectedIndex');
		thestate.selectedIndex=0;}
<?php
	} ?>
}}
<?php
	createdynamicstates('SELECT stateAbbrev,stateName,stateName2,stateName3,stateCountryID,countryName FROM states INNER JOIN countries ON states.stateCountryID=countries.countryID WHERE countryEnabled<>0 AND stateEnabled<>0 AND (loadStates=2 OR countryID=' . $origCountryID . ') ORDER BY stateCountryID,' . getlangid('stateName',1048576));
	print "checkoutspan('');setinitialstate('');\r\n";
?>/* ]]> */
</script>
<?php	}elseif((getpost('act')=='viewacct' || getpost('act')=='deleteaddress' || getpost('act')=='addorphans' || getpost('act')=='addorphan') AND is_numeric(getpost('id'))){
			$clID = getpost('id');
			$sSQL = "SELECT clUserName,clPW,clLoginLevel,clActions,clPercentDiscount,clEmail,clDateCreated,loyaltyPoints,clientCustom1,clientCustom2,clientAdminNotes FROM customerlogin WHERE clID='" . $clID . "'";
			$result=ect_query($sSQL) or ect_error();
			$rs=ect_fetch_assoc($result);
			$clUserName=$rs['clUserName'];
			$clPW=$rs['clPW'];
			$clLoginLevel=$rs['clLoginLevel'];
			$clActions=$rs['clActions'];
			$clPercentDiscount=$rs['clPercentDiscount'];
			$clEmail=$rs['clEmail'];
			$clDateCreated=$rs['clDateCreated'];
			$clLoyaltyPoints=$rs['loyaltyPoints'];
			$clientCustom1=$rs['clientCustom1'];
			$clientCustom2=$rs['clientCustom2'];
			$clientAdminNotes=$rs['clientAdminNotes'];
			ect_free_result($result);
			$sSQL = "SELECT email FROM mailinglist WHERE email='" . escape_string($clEmail) . "'";
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result)>0) $clAllowEmail=1; else $clAllowEmail=0;
			ect_free_result($result);
			$ordersnotinacct=0;
			$sSQL = "SELECT COUNT(*) AS thecnt FROM orders WHERE ordClientID=0 AND ordEmail='" . escape_string($clEmail) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				if(! is_null($rs['thecnt'])) $ordersnotinacct=$rs['thecnt'];
			}
			ect_free_result($result); ?>
		  <form method="post" name="mainform" action="">
<?php		writehiddenvar('posted', '1');
			writehiddenvar('act', 'none');
			writehiddenvar('theid', '');
			writehiddenvar('stext', getpost('stext'));
			writehiddenvar('accdate', getpost('accdate'));
			writehiddenvar('daterange', getpost('daterange'));
			writehiddenvar('slevel', getpost('slevel'));
			writehiddenvar('stype', getpost('stype'));
			writehiddenvar('pg', getpost('pg'));
			writehiddenvar('id', $clID); ?>
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
              <tr> 
                <td class="cobhl" align="center" height="34"><strong><?php print $GLOBALS['xxAccDet']?></strong></td>
			  </tr>
			  <tr> 
                <td class="cobll" height="34" align="center">
				  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php		$sSQL = "SELECT email,isconfirmed FROM mailinglist WHERE email='" . escape_string($clEmail) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){ $allowemail=1; $isconfirmed=$rs['isconfirmed']; }else{ $allowemail=0; $isconfirmed=FALSE; }
			ect_free_result($result);
?>
					<tr><td class="cobhl" align="right" width="25%" height="22"><strong><?php print $GLOBALS['xxName']?>:</strong></td>
					<td class="cobll" align="left" width="25%"><?php print htmlspecials($clUserName)?></td>
					<td class="cobhl" align="right" width="25%"><strong><?php print $yyActns?>:</strong></td>
					<td class="cobll" align="left" width="25%"><?php
						if(($clActions & 1)==1) print 'STE ';
						if(($clActions & 2)==2) print 'CTE ';
						if(($clActions & 4)==4) print 'SHE ';
						if(($clActions & 32)==32) print 'HAE ';
						if(($clActions & 8)==8) print 'WSP ';
						if(($clActions & 16)==16) print 'PED '; ?>&nbsp;</td>
					</tr>
					<tr><td class="cobhl" align="right" height="22"><strong><?php print $GLOBALS['xxEmail']?>:</strong></td>
					<td class="cobll" align="left"><?php print htmlspecials($clEmail)?></td>
					<td class="cobhl" align="right"><strong><?php print $GLOBALS['xxAlPrEm']?>:</strong></td>
					<td class="cobll" align="left"><?php if(@$noconfirmationemail!=TRUE && $allowemail!=0 && $isconfirmed==0) print $GLOBALS['xxWaiCon']; else print '<input type="checkbox" name="allowemail" value="ON"' . ($allowemail!=0 ? ' checked="checked"' : '') . ' disabled="disabled" />'?></td>
					</tr>
					<tr><td class="cobhl" align="right" height="22"><strong><?php print $yyPerDis?>:</strong></td>
					<td class="cobll" align="left"><?php if(($clActions & 16)==16) print $clPercentDiscount; else print '-';?></td>
					<td class="cobhl" align="right"><strong><?php print $yyLiLev?>:</strong></td>
					<td class="cobll" align="left"><?php print $clLoginLevel?></td>
					</tr>
<?php		if(@$loyaltypoints!=''){ ?>
					<tr><td class="cobhl" align="right" height="22"><strong><?php print $GLOBALS['xxLoyPoi']?>:</strong></td>
					<td class="cobll" colspan="3" align="left"><?php print $clLoyaltyPoints?></td>
					</tr>
<?php		}
			if(trim(@$extraclientfield1)!=''){ ?>
					<tr><td class="cobhl" align="right" height="22"><strong><?php print $GLOBALS['extraclientfield1']?>:</strong></td>
					<td class="cobll" colspan="3" align="left"><?php print htmlspecials($clientCustom1)?></td>
					</tr>
<?php		}
			if(trim(@$extraclientfield2)!=''){ ?>
					<tr><td class="cobhl" align="right" height="22"><strong><?php print $GLOBALS['extraclientfield2']?>:</strong></td>
					<td class="cobll" colspan="3" align="left"><?php print htmlspecials($clientCustom2)?></td>
					</tr>
<?php		} ?>
					<tr><td class="cobhl" align="right" height="22"><strong>Client Admin Notes:</strong></td>
					<td class="cobll" colspan="3" align="left"><?php print ($clientAdminNotes!=''?$clientAdminNotes:'-')?></td>
					</tr>
					<tr><td class="cobll" align="left" colspan="4"><br /><ul><li><?php print $GLOBALS['xxChaAcc']?> <a class="ectlink" href="javascript:editaccount()"><strong><?php print $GLOBALS['xxClkHere']?></strong></a>.</li>
<?php		if($ordersnotinacct!=0) print '<li>' . $ordersnotinacct . " orders with this email are not registered to the account. To add them all please" . ' <a class="ectlink" href="javascript:addorphans()"><strong>'.$GLOBALS['xxClkHere'].'</strong></a>.</li>' ?>
					</ul></td>
					</tr>
				  </table>
				</td>
			  </tr>
              <tr> 
                <td class="cobhl" align="center" height="34"><strong><?php print $GLOBALS['xxAddMan']?></strong></td>
			  </tr>
			  <tr> 
                <td class="cobll" height="34" align="center">
				  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php		$sSQL = "SELECT addID,addIsDefault,addName,addLastName,addAddress,addAddress2,addState,addCity,addZip,addPhone,addCountry FROM address WHERE addCustID=" . $clID . " ORDER BY addIsDefault";
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result)>0){
				while($rs=ect_fetch_assoc($result)){
					print '<tr><td width="50%" class="cobll" align="left">' . htmlspecials(trim($rs['addName'].' '.$rs['addLastName'])) . '<br />' . htmlspecials($rs['addAddress']) . (trim($rs['addAddress2'])!='' ? '<br />' . htmlspecials($rs['addAddress2']) : '') . '<br />' . htmlspecials($rs['addCity']) . ', ' . htmlspecials($rs['addState']) . ($rs['addZip']!='' ? '<br />' . htmlspecials($rs['addZip']) : '') . '<br />' . htmlspecials($rs['addCountry']) . '</td>';
					print '<td class="cobhl" align="left"><ul><li><a class="ectlink" href="javascript:editaddress(' . $rs['addID'] . ')">' . $GLOBALS['xxEdAdd'] . '</a><br /><br /></li><li><a class="ectlink" href="javascript:deleteaddress(' . $rs['addID'] . ')">' . $GLOBALS['xxDeAdd'] . '</a></li></ul></td></tr>';
				}
			}else{
				print '<tr><td class="cobll" align="center" colspan="2" height="34">' . $GLOBALS['xxNoAdd'] . '</td></tr>';
			}
			ect_free_result($result);
?>
					<tr><td class="cobhl" colspan="2" align="left"><br /><ul><li><?php print $GLOBALS['xxPCAdd']?> <a class="ectlink" href="javascript:newaddress()"><strong><?php print $GLOBALS['xxClkHere']?></strong></a>.</li></ul></td></tr>
				  </table>
				</td>
			  </tr>
			  <tr> 
                <td class="cobhl" align="center" height="34"><strong><?php print $GLOBALS['xxOrdMan']?></strong></td>
			  </tr>
			  <tr> 
                <td class="cobll" height="34" align="center">
				  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php		$hastracknum=FALSE;
			$sSQL = "SELECT ordID FROM orders WHERE ordClientID=" . $clID . " AND ordTrackNum<>''";
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result)>0) $hastracknum=TRUE;
			ect_free_result($result);
			$hasorphan=FALSE;
			$sSQL = "SELECT ordID FROM orders WHERE ordClientID=0 AND ordEmail='" . escape_string($clEmail) . "'";
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result)>0) $hasorphan=TRUE;
			ect_free_result($result); ?>
					<tr><td class="cobhl"><?php print $GLOBALS['xxOrdId']?></td>
					<td class="cobhl"><?php print $GLOBALS['xxDate']?></td>
					<td class="cobhl"><?php print $GLOBALS['xxStatus']?></td>
<?php		if($hastracknum) print '<td class="cobhl">' . $GLOBALS['xxTraNum'] . '</td>'; ?>
					<td class="cobhl"><?php print $GLOBALS['xxGndTot']?></td>
<?php		if($hasorphan) print '<td class="cobhl">' . 'Account' . '</td>'; ?>
					<td class="cobhl"><?php print $GLOBALS['xxCODets']?></td></tr>			
<?php		$grandtotal=0;
			$sSQL = "SELECT ordID,ordDate,ordTrackNum,ordTotal,ordStateTax,ordCountryTax,ordShipping,ordHSTTax,ordHandling,ordDiscount," . getlangid('statPublic',64) . ",ordClientID FROM orders LEFT OUTER JOIN orderstatus ON orders.ordStatus=orderstatus.statID WHERE ordClientID=" . $clID . " OR ordEmail='" . escape_string($clEmail) . "' ORDER BY ordDate";
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result)>0){
				while($rs=ect_fetch_assoc($result)){
					$subtotal = ($rs['ordTotal']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordShipping']+$rs['ordHSTTax']+$rs['ordHandling'])-$rs['ordDiscount'];
					$grandtotal+=$subtotal;
					print '<tr><td class="cobll">' . $rs['ordID'] . '</td>';
					print '<td class="cobll">' . $rs['ordDate'] . '</td>';
					print '<td class="cobll">' . $rs[getlangid("statPublic",64)] . '</td>';
					if($hastracknum) print '<td class="cobll">' . ($rs['ordTrackNum']!=''?$rs['ordTrackNum']:'&nbsp;') . '</td>';
					print '<td class="cobll" align="right">' . FormatEuroCurrency($subtotal) . '&nbsp;</td>';
					if($hasorphan){
						print '<td class="cobll">';
						if($rs['ordClientID']==0) print '<a href="javascript:addorphan('.$rs['ordID'].')">'.'Link to Account'.'</a>'; else print '&nbsp;';
						print '</td>';
					}
					print '<td class="cobll"><a class="ectlink" href="javascript:vieworder(' . $rs['ordID'] . ')">' . $GLOBALS['xxClkHere'] . '</a></td></tr>';
				}
				if($subtotal!=$grandtotal) print '<tr><td class="cobll" colspan="'.($hastracknum ? '4' : '3') . '">&nbsp;</td><td class="cobll" align="right">' . FormatEuroCurrency($grandtotal) . '&nbsp;</td><td class="cobll"'.($hasorphan?' colspan="2"':'').'>&nbsp;</td></tr>';
			}else{
				print '<tr><td class="cobll" colspan="5" height="34" align="center">' . $GLOBALS['xxNoOrd'] . '</td></tr>';
			}
			ect_free_result($result);
?>
				  </table>
				</td>
			  </tr>
			</table>
		  </form>
<?php	}elseif(getget('loginas')!='' && is_numeric(getget('loginas'))){
			$sSQL = "SELECT clID,clUserName,clActions,clLoginLevel,clPercentDiscount FROM customerlogin WHERE clID=".getget('loginas');
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				$_SESSION['clientID']=$rs['clID'];
				$_SESSION['clientUser']=$rs['clUserName'];
				$_SESSION['clientActions']=$rs['clActions'];
				$_SESSION['clientLoginLevel']=$rs['clLoginLevel'];
				$_SESSION['clientPercentDiscount']=(100.0-(double)$rs['clPercentDiscount'])/100.0;
				$redirecturl = $storeurl;
				if(@$_SERVER['HTTPS']=='on') $redirecturl=str_replace('http:','https:',$redirecturl);
				header('Location: ' . $redirecturl . 'cart.php');
			}else
				print 'Login not found';
		}elseif(getpost('posted')=='1' && $success){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
<?php		if(getpost('act')=='doeditaddress' || getpost('act')=='donewaddress'){ ?>
					<form action="adminclientlog.php" method="post" id="postform">
					<input type="hidden" name="act" value="viewacct" />
					<input type="hidden" name="id" value="<?php print getpost('id')?>" />
					&nbsp;<br />&nbsp;<br />
					<?php print $yyNoAuto?><br />&nbsp;<br />
					<input type="submit" value="<?php print $yyClkHer?>" /><br />&nbsp;<br />&nbsp;
					</form>
<?php			print '<script type="text/javascript">document.getElementById("postform").submit();</script>' . "\r\n";
			}else{ ?>
					<?php print $yyNoAuto?> <a href="adminclientlog.php"><strong><?php print $yyClkHer?></strong></a>.<br />&nbsp;</br />
<?php		} ?>                </td>
			  </tr>
			</table></td>
        </tr>
	  </table>
<?php	}elseif(getpost('posted')=="1"){ ?>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a><br />&nbsp;<br />&nbsp;</td>
			  </tr>
			</table></td>
        </tr>
	  </table>
<?php	}else{ ?>
<script type="text/javascript" src="popcalendar.js"></script>
<script type="text/javascript">
<!--
try{languagetext('<?php print @$adminlang?>');}catch(err){}
function mrec(id){
	document.mainform.id.value = id;
	document.mainform.act.value = "viewacct";
	document.mainform.submit();
}
function newrec(id){
	document.mainform.id.value = id;
	document.mainform.act.value = "addnew";
	document.mainform.submit();
}
function lrec(id){
	window.open('adminclientlog.php?loginas='+id,'clientlogin','menubar=no, scrollbars=yes, width=950, height=700, directories=no,location=no,resizable=yes,status=yes,toolbar=no')
}
function drec(id){
if(confirm("<?php print jscheck($yyConDel)?>\n")){
	document.mainform.id.value = id;
	document.mainform.act.value = "delete";
	document.mainform.submit();
}
}
function startsearch(){
	document.mainform.action="adminclientlog.php";
	document.mainform.act.value = "search";
	document.mainform.posted.value = "";
	document.mainform.submit();
}
// -->
</script>
<h2><?php print $yyAdmCli?></h2>
<?php
	function dispcountries($ind){
		global $allcountries,$numallcountries,$yySelect;
		$dspc='<select size="1" id="newcntryname'.$ind.'" name="newcntryname'.$ind.'"><option value="">'.$yySelect.'</option>';
		for($index=0;$index<$numallcountries;$index++){
			$dspc.='<option value="' . htmlspecials($allcountries[$index]['countryName']) . '">'.$allcountries[$index]['countryName']."</option>\n";
		}
		$dspc.='<option value="" disabled="disabled">==============================</option>';
		$dspc.='<option value="xxxdeletexxx">'.'DELETE ADDRESS - Country No Longer Supported'.'</option>';
		return($dspc.'</select>');
	}
	$sSQL="SELECT DISTINCT addCountry,countryID FROM address LEFT JOIN countries ON address.addCountry=countries.countryName WHERE countryID IS NULL";
	$result=ect_query($sSQL) or ect_error();
	if(ect_num_rows($result)>0){ ?>
		<form id="updcntryid" method="post" action="adminclientlog.php">
		<input type="hidden" id="oldcntryname" name="oldcntryname" value="" />
		<input type="hidden" id="newcntryname" name="newcntryname" value="" />
		</form>
		<table border="1" cellspacing="3" cellpadding="3">
		  <tr><td colspan="3">There are countries in the client login table that do not now exist. These need to be mapped to actual countries.</td></tr>
<?php	$sSQL = "SELECT countryName,countryID FROM countries WHERE countryEnabled=1 ORDER BY countryOrder DESC,countryName";
		$result2=ect_query($sSQL) or ect_error();
		while($rs2=ect_fetch_assoc($result2))
			$allcountries[$numallcountries++]=$rs2;
		$index=0;
		while($rs=ect_fetch_assoc($result)){
			print "<tr><td>" . $rs['addCountry'] . "</td><td>" . dispcountries($index) . "</td><td>"; ?>
<input type="button" value="<?php print $yySubmit?>" onclick="document.getElementById('oldcntryname').value='<?php print jsspecials($rs['addCountry'])?>';document.getElementById('newcntryname').value=document.getElementById('newcntryname<?php print $index?>')[document.getElementById('newcntryname<?php print $index?>').selectedIndex].value;if(document.getElementById('newcntryname<?php print $index?>').selectedIndex==0)alert('Please select a country...');else document.getElementById('updcntryid').submit()" />
<?php		print "</td></tr>";
			$index++;
		} ?>
		</table>
<?php
	}
	ect_free_result($result); ?>
	<form name="mainform" method="post" action="adminclientlog.php">
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
		  <td width="100%">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="id" value="xxxxx" />
			<input type="hidden" name="pg" value="<?php print (getpost('act')=='search' ? '1' : getget('pg')) ?>" />
<?php	$themask = 'yyyy-mm-dd';
		if($admindateformat==1)
			$themask='mm/dd/yyyy';
		elseif($admindateformat==2)
			$themask='dd/mm/yyyy';
		$thelevel = @$_REQUEST['slevel'];
		if(@$thelevel!='') $thelevel = (int)$thelevel;
?>			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr> 
                <td class="cobhl" width="25%" align="right"><?php print $yySrchFr?>:</td>
				<td class="cobll" width="25%"><input type="text" name="stext" size="20" value="<?php print htmlspecials(@$_REQUEST['stext'])?>" /></td>
				<td class="cobhl" width="20%" align="right"><?php print $yyDate?>:</td>
				<td class="cobll">
					<select name="daterange" size="1">
					<option value=""><?php print $yySinc?></option>
					<option value="1"<?php if(@$_REQUEST['daterange']=="1") print ' selected="selected"'?>><?php print $yyTill?></option>
					<option value="2"<?php if(@$_REQUEST['daterange']=="2") print ' selected="selected"'?>><?php print $yyOn?></option>
					</select>
					<input type="text" size="14" name="accdate" value="<?php print htmlspecials(@$_REQUEST['accdate'])?>" /> <input type="button" onclick="popUpCalendar(this, document.forms.mainform.accdate, '<?php print $themask?>', -205)" value='DP' />
				</td>
			  </tr>
			  <tr>
			    <td class="cobhl" align="right"><?php print $yySrchTp?>:</td>
				<td class="cobll"><select name="stype" size="1">
					<option value=""><?php print $yySrchAl?></option>
					<option value="any" <?php if(@$_REQUEST['stype']=='any') print 'selected="selected"'?>><?php print $yySrchAn?></option>
					<option value="exact" <?php if(@$_REQUEST['stype']=='exact') print 'selected="selected"'?>><?php print $yySrchEx?></option>
					</select>
				</td>
				<td class="cobhl" align="right"><?php print $yyLiLev?>:</td>
				<td class="cobll">
				  <select name="slevel" size="1">
				  <option value=""><?php print $yyAllLev?></option>
<?php						for($rowcounter=0; $rowcounter <= $maxloginlevels; $rowcounter++){
								print "<option value='" . $rowcounter . "'";
								if($thelevel !== '' && $thelevel !== NULL){
									if($thelevel==$rowcounter) print ' selected="selected"';
								}
								print '>&nbsp; ' . $rowcounter . ' </option>';
							} ?>
				  </select>
				</td>
              </tr>
			  <tr>
				    <td class="cobhl">&nbsp;</td>
				    <td class="cobll" colspan="3"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					    <tr>
						  <td class="cobll" align="center"><input type="button" value="<?php print $yyListRe?>" onclick="startsearch();" />
							<input type="button" value="<?php print $yyCLNew?>" onclick="newrec();" />
						  </td>
						  <td class="cobll" height="26" width="20%" align="right">&nbsp;</td>
						</tr>
					  </table></td>
				  </tr>
			</table>
		<table width="100%" class="stackable admin-table-a sta-white">
<?php	if(getpost('act')=='search' || getget('pg')!=''){
			function displayprodrow($xrs){
				global $bgcolor,$yyModify,$yyDelete,$yyLogin;
			?>
                    <tr class="<?php print $bgcolor?>">
                            <td><?php print htmlspecials($xrs['clUserName'])?></td>
                            <td><?php print htmlspecials($xrs['clEmail'])?></td>
                            <td align="center"><?php print $xrs['clLoginLevel']?></td>
                            <td><?php	if(($xrs['clActions'] & 1)==1) print 'STE ';
							if(($xrs['clActions'] & 2)==2) print 'CTE ';
							if(($xrs['clActions'] & 4)==4) print 'SHE ';
							if(($xrs['clActions'] & 32)==32) print 'HAE ';
							if(($xrs['clActions'] & 8)==8) print 'WSP ';
							if(($xrs['clActions'] & 16)==16) print 'PED ';
				?>&nbsp;</td>
				<td class="minicell"><input type="button" value="<?php print $yyLogin?>" onclick="lrec('<?php print $xrs['clID']?>',event)" /></td>
				<td class="minicell"><input type="button" value="<?php print $yyModify?>" onclick="mrec('<?php print $xrs['clID']?>',event)" /></td>
				<td class="minicell"><input type="button" value="<?php print $yyDelete?>" onclick="drec('<?php print $xrs['clID']?>')" /></td></tr>
<?php		}
			function displayheaderrow(){
				global $yyLiName,$yyEmail,$yyPass,$yyLiLev,$yyActns,$yyModify,$yyDelete,$yyLogin;
?>
			  <tr>
				<!--<th class="maincell"><?php //print $yyLiName?></th>-->
                               <th class="maincell">Customer</th>
				<th class="maincell"><?php print $yyEmail?></th>
                                
				<th class="minicell"><?php print $yyLiLev?></th>
				<th class="minicell"><?php print $yyActns?></th>
				<th class="minicell"><?php print $yyLogin?></th>
				<th class="minicell"><?php print $yyModify?></th>
				<th class="minicell"><?php print $yyDelete?></th>
			  </tr>
<?php		}
		$whereand = ' WHERE ';
		$sSQL = "SELECT clID,clUserName,clActions,clLoginLevel,clPercentDiscount,clEmail,clPW FROM customerlogin";
		if($thelevel !== '' && $thelevel !== NULL){
			$sSQL.=$whereand . " clLoginLevel=" . $thelevel;
			$whereand=' AND ';
		}
		$accdate = trim(@$_REQUEST['accdate']);
		if($accdate!=''){
			$accdate = parsedate($accdate);
			if(@$_REQUEST['daterange']=='1') // Till
				$sSQL.=$whereand . "clDateCreated <= '" . date("Y-m-d", $accdate) . "' ";
			elseif(@$_REQUEST['daterange']=='2') // On
				$sSQL.=$whereand . "clDateCreated BETWEEN '"  . date("Y-m-d", $accdate) . "' AND '" . date("Y-m-d", $accdate+(60*60*24)) . "' ";
			else // Since
				$sSQL.=$whereand . "clDateCreated >= '" . date("Y-m-d", $accdate) . "' ";
			$whereand=' AND ';
		}
		if(trim(@$_REQUEST['stext'])!=''){
			$stext=getrequest('stext');
			$stype=trim(@$_REQUEST['stype']);
			$Xstext = escape_string($stext);
			$aText = explode(' ',$Xstext);
			$aFields[0]='clUserName';
			$aFields[1]='clPw';
			$aFields[2]='clEmail';
			if($stype=='exact'){
				$sSQL.=$whereand . "(clUserName LIKE '%" . $Xstext . "%' OR clPw LIKE '%" . $Xstext . "%' OR clEmail LIKE '%" . $Xstext . "%') ";
				$whereand=' AND ';
			}else{
				$sJoin='AND ';
				if($stype=='any') $sJoin='OR ';
				$sSQL.=$whereand . '(';
				$whereand=' AND ';
				for($index=0;$index<=2;$index++){
					$sSQL.='(';
					$rowcounter=0;
					$arrelms=count($aText);
					foreach($aText as $theopt){
						if(is_array($theopt))$theopt=$theopt[0];
						$sSQL.=$aFields[$index] . " LIKE '%" . $theopt . "%' ";
						if(++$rowcounter < $arrelms) $sSQL.=$sJoin;
					}
					$sSQL.=') ';
					if($index < 2) $sSQL.='OR ';
				}
				$sSQL.=') ';
			}
		}
                $sSQL.=$whereand ."TempDelete = '0'";
		$sSQL.=' ORDER BY clUserName';
		if(! @is_numeric(getget('pg')))
			$CurPage = 1;
		else
			$CurPage = (int)getget('pg');
		if(@$adminclientloginperpage=='') $adminclientloginperpage=200;
		// $tmpSQL = "SELECT COUNT(DISTINCT products.pId) AS bar" . $sSQL;
		$tmpSQL = str_replace('clID,clUserName,clActions,clLoginLevel,clPercentDiscount,clEmail,clPW', 'COUNT(*) AS bar', $sSQL);
		$allprods=ect_query($tmpSQL) or ect_error();
		$rs=ect_fetch_assoc($allprods);
		$iNumOfPages = ceil($rs['bar']/$adminclientloginperpage);
		ect_free_result($allprods);
		$sSQL.=' LIMIT ' . ($adminclientloginperpage*($CurPage-1)) . ', ' . $adminclientloginperpage;
		$result=ect_query($sSQL) or ect_error();
		$haveerrprods=FALSE;
		if(ect_num_rows($result) > 0){
			$pblink = '<a href="adminclientlog.php?rid=' . @$_REQUEST['rid'] . '&stext=' . urlencode(@$_REQUEST['stext']) . '&stype=' . @$_REQUEST['stype'] . '&slevel=' . @$_REQUEST['slevel'] . '&accdate=' . @$_REQUEST['accdate'] . '&daterange=' . urlencode(@$_REQUEST['daterange']) . '&pg=';
			if($iNumOfPages > 1) print '<tr><td colspan="6" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
			displayheaderrow();
			while($rs=ect_fetch_assoc($result)){
				if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark';
				displayprodrow($rs);
			}
			if($haveerrprods) print '<tr><td width="100%" colspan="6"><br />' . $redasterix . $yySeePr . '</td></tr>';
			if($iNumOfPages > 1) print '<tr><td colspan="6" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
		}else{
			print '<tr><td width="100%" colspan="6" align="center"><br />' . $yyItNone . '<br />&nbsp;</td></tr>';
		}
		ect_free_result($result);
	} ?>
			  <tr>
                <td width="100%" colspan="7" align="center"><br /><ul><li><?php print $yyCLTyp?></li></ul>
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </td>
        </tr>
      </table>
	</form>
<?php
}
?>