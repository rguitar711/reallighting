<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
global $alreadygotadmin,$thesessionid,$Count,$usecsslayout,$storeurl,$csalsoboughttitle,$useStockManagement,$noshowoutofstock,$csrecommendedtitle,$csrelatedtitle,$csbestsellerstitle,$crosssellnotsection,$forcedetailslink,$prodlist,$orsortby,$productcolumns,$csnobuyorcheckout,$csnoshowdiscounts,$noproductoptions,$csnoproductoptions,$prodseparator,$crosssellcolumns,$crosssellrows,$noshowdiscounts,$csrecommendedtitle,$crosssellaction,$csstyleprefix,$countryTaxRate,$defimagejs,$optjs;
if(@$_SERVER['CONTENT_LENGTH']!='' && $_SERVER['CONTENT_LENGTH'] > 10000) exit;
$WSP=$OWSP='';
$TWSP='pPrice';
$cs=@$csstyleprefix;
get_wholesaleprice_sql();
if(@$crosssellcolumns==''){ if(@$productcolumns=='') $crosssellcolumns=3; else $crosssellcolumns=$productcolumns; }
if(@$crosssellrows=='') $crosssellrows=1;
$numberofproducts = $crosssellcolumns * $crosssellrows;
$productcolumns=$crosssellcolumns;
if(@$csnobuyorcheckout==TRUE) $nobuyorcheckout=TRUE;
if(@$csnoshowdiscounts==TRUE) $noshowdiscounts=TRUE;
$cssaveproductoptions=$noproductoptions;
if(@$csnoproductoptions==TRUE) $noproductoptions=TRUE;
if(! @isset($forcedetailslink)) $forcedetailslink=TRUE;
$iNumOfPages=1;
$showcategories=FALSE;
$magictoolboxproducts='';
$isrootsection=TRUE;
if(! @isset($Count)) $Count=0; else $Count=($Count+$crosssellcolumns)-($Count % $crosssellcolumns);
$catid = '0';
if(is_numeric(@$_REQUEST['sortby'])) $_SESSION['sortby']=(int)$_REQUEST['sortby'];
if(@$_SESSION['sortby']!='') $dosortby=$_SESSION['sortby'];
if(@$orsortby!='') $dosortby=$orsortby;
if(@$dosortby==2 || @$dosortby==12)
	$sSortBy=' ORDER BY products.pId'.(@$dosortby==12?' DESC':'');
elseif(@$dosortby==3||@$dosortby==4)
	$sSortBy=' ORDER BY '.$TWSP.(@$dosortby==4?' DESC,pId':',pId');
elseif(@$dosortby==5)
	$sSortBy='';
elseif(@$dosortby==6||@$dosortby==7)
	$sSortBy=' ORDER BY pOrder'.(@$dosortby==7?' DESC,pId':',pId');
elseif(@$dosortby==8||@$dosortby==9)
	$sSortBy=' ORDER BY pDateAdded'.(@$dosortby==9?' DESC,pId':',pId');
elseif(@$dosortby==10)
	$sSortBy=' ORDER BY pManufacturer,pID';
else
	$sSortBy=' ORDER BY '.getlangid('pName',1).(@$dosortby==11?' DESC,pId':',pId');
if(@$prodlist=='') $prodlist='';
if(getpost('mode')!='checkout' && getpost('mode')!='add' && getpost('mode')!='go' && getpost('mode')!='paypalexpress1' && getpost('mode')!='authorize'){
	$alreadygotadmin = getadminsettings();
	$prodfilter=0;
	$thesessionid=getsessionid();
	if(@$_SESSION['clientID']!='' && @$_SESSION['clientLoginLevel']!='') $minloglevel=$_SESSION['clientLoginLevel']; else $minloglevel=0;
	$result2=ect_query('SELECT sectionID FROM sections WHERE sectionDisabled>'.$minloglevel) or ect_error();
	$addcomma='';
	if(@$crosssellnotsection=='') $crosssellnotsection=''; else $addcomma=',';
	while($rs2=ect_fetch_assoc($result2)){
		$crosssellnotsection.=$addcomma . $rs2['sectionID'];
		$addcomma=',';
	}
	ect_free_result($result2);
	$crosssellactionarr = explode(',', @$crosssellaction);
	for($csindex=0; $csindex < count($crosssellactionarr); $csindex++){
		$crosssellaction=trim($crosssellactionarr[$csindex]);
		$addcomma=''; $relatedlist='';
		if($crosssellaction=='alsobought'){ // Those who bought what's in your cart also bought.
			if(@$csalsoboughttitle=='') $crossselltitle='Customers who bought these products also bought.'; else $crossselltitle=$csalsoboughttitle;
			if($prodlist==''){
				$addcomma='';
				$sSQL = "SELECT cartProdID FROM cart WHERE cartCompleted=0 AND cartSessionID='" . escape_string($thesessionid) . "'";
				$result=ect_query($sSQL) or ect_error();
				while($rs=ect_fetch_assoc($result)){
					$prodlist.=$addcomma . "'" . escape_string($rs['cartProdID']) . "'";
					$addcomma=',';
				}
				ect_free_result($result);
			}
			$addcomma=$relatedlist='';
			$thecount=0;
			$alldone=FALSE;
			if($prodlist!=''){
				$sSQL="SELECT cartOrderID FROM cart WHERE cartOrderID<>0 AND cartProdID IN (".$prodlist.") AND cartSessionID<>'".str_replace("'",'',$thesessionid)."' ORDER BY cartOrderID DESC";
				$result=ect_query($sSQL) or ect_error();
				while($rs=ect_fetch_assoc($result)){
					$sSQL="SELECT cartProdID FROM cart WHERE cartProdID NOT IN (".$prodlist.($relatedlist!=''?','.$relatedlist:'').") AND cartOrderID=" . $rs['cartOrderID'];
					$result2=ect_query($sSQL) or ect_error();
					while($rs2=ect_fetch_assoc($result2)){
						$relatedlist.=$addcomma . "'" . escape_string($rs2['cartProdID']) . "'";
						$addcomma=",";
						$thecount++;
						if($thecount>=$numberofproducts){ $alldone=TRUE; break; }
					}
					ect_free_result($result2);
					if($alldone) break;
				}
				ect_free_result($result);
			}
		}elseif($crosssellaction=='recommended'){ // Recommended products (Needs v5.1)
			if(@$csrecommendedtitle=='') $crossselltitle='These products are our current recommendations for you.'; else $crossselltitle=$csrecommendedtitle;
			if($prodlist==''){
				$sSQL = "SELECT cartProdID FROM cart WHERE cartCompleted=0 AND cartSessionID='" . escape_string($thesessionid) . "'";
				$result=ect_query($sSQL) or ect_error();
				$addcomma='';
				while($rs=ect_fetch_assoc($result)){
					$prodlist.=$addcomma . "'" . escape_string($rs['cartProdID']) . "'";
					$addcomma=',';
				}
				ect_free_result($result);
			}
			$sSQL = 'SELECT pID FROM products WHERE pDisplay<>0 AND pRecommend<>0';
			if($prodlist!='') $sSQL.=' AND NOT (pID IN (' . $prodlist . '))';
			if($crosssellnotsection!='') $sSQL.=' AND NOT (pSection IN (' . $crosssellnotsection . '))';
			$addcomma=''; $relatedlist='';
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){
				$relatedlist.=$addcomma . "'" . escape_string($rs['pID']) . "'";
				$addcomma=',';
			}
			ect_free_result($result);
		}elseif($crosssellaction=='related'){ // Products recommended with this product (Would need v5.1)
			if(@$csrelatedtitle=='') $crossselltitle='These products are recommended with items in your cart.'; else $crossselltitle=$csrelatedtitle;
			if($prodlist==''){
				$addcomma='';
				$sSQL = "SELECT cartProdID FROM cart WHERE cartCompleted=0 AND cartSessionID='" . escape_string($thesessionid) . "'";
				$result=ect_query($sSQL) or ect_error();
				while($rs=ect_fetch_assoc($result)){
					$prodlist.=$addcomma . "'" . escape_string($rs['cartProdID']) . "'";
					$addcomma=',';
				}
				ect_free_result($result);
			}
			if($prodlist!=''){
				$sSQL = 'SELECT rpRelProdID FROM relatedprods WHERE (rpProdID IN (' . $prodlist . ') AND NOT (rpRelProdID IN (' . $prodlist . ')))';
				if(@$relatedproductsbothways==TRUE) $sSQL.=' UNION SELECT rpProdID FROM relatedprods WHERE (rpRelProdID IN (' . $prodlist . ') AND NOT (rpProdID IN (' . $prodlist . ')))';
				$addcomma=''; $relatedlist='';
				$result=ect_query($sSQL) or ect_error();
				while($rs=ect_fetch_assoc($result)){
						$relatedlist.=$addcomma . "'" . escape_string($rs['rpRelProdID']) . "'";
						$addcomma=',';
				}
				ect_free_result($result);
			}
		}elseif($crosssellaction=='bestsellers'){ // Top X best sellers
			if(@$csbestsellerstitle=='') $crossselltitle='These are our current best sellers.'; else $crossselltitle=$csbestsellerstitle;
			$sSQL = 'SELECT cartProdID,COUNT(cartProdID) AS pidcount FROM cart INNER JOIN products ON cart.cartProdID=products.pID WHERE cartCompleted<>0 AND pDisplay<>0 ' . (@$crosssellsection!='' ? ' AND pSection IN (' . $crosssellsection . ')' : '') . (@$crosssellnotsection!='' ? ' AND NOT (pSection IN (' . $crosssellnotsection . '))' : '');
			if(@$bestsellerlimit!='') $sSQL.=" AND cartDateAdded>'".date('Y-m-d H:i:s', time()-($bestsellerlimit*60*60*24))."'";
			$sSQL.=' GROUP BY cartProdID ORDER BY pidcount DESC LIMIT 0,' . $numberofproducts;
			$relatedlist='';
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){
				$relatedlist.=$addcomma . "'" . escape_string($rs['cartProdID']) . "'";
				$addcomma=',';
			}
			ect_free_result($result);
		}else
			if($crosssellaction!='') print '<p>Unrecognized crosssell action ' . $crosssellaction . '</p>';
		if($relatedlist!=''){
			$saveprodlist=$prodlist;
			$prodlist=$relatedlist;
			$sSQL = 'SELECT pId,pSKU,' . getlangid('pName',1) . ',' . $WSP . 'pPrice,pListPrice,pSection,pSell,pStockByOpts,pStaticPage,pStaticURL,pInStock,pExemptions,pTax,pTotRating,pNumRatings,pBackOrder,pCustom1,pCustom2,pCustom3,pDateAdded,'.(@$manufacturerfield!=''?getlangid('scName',131072).',':'')."'' AS " . getlangid('pDescription',2) . ',' . getlangid('pLongDescription',4) . ' FROM products '.(@$manufacturerfield!=''?'LEFT OUTER JOIN searchcriteria on products.pManufacturer=searchcriteria.scID ':'').'WHERE pDisplay<>0 AND pId IN (' . $prodlist . ')';
			$sSQL.=(@$crosssellnotsection!='' && $crosssellaction=='related' ? ' AND NOT (pSection IN (' . $crosssellnotsection . '))' : '');
			if($useStockManagement && @$noshowoutofstock==TRUE) $sSQL.=' AND (pInStock>0 OR pStockByOpts<>0)';
			$sSQL.=$sSortBy;
			$allprods=ect_query($sSQL) or ect_error();
			$adminProdsPerPage=ect_num_rows($allprods);
			if(ect_num_rows($allprods) > 0){
				print '<p class="cstitle"><strong>' . $crossselltitle . '</strong></p>';
				include './vsadmin/inc/incproductbody2.php';
			}
			ect_free_result($allprods);
			$prodlist=$saveprodlist;
		}
	}
}
$noproductoptions=$cssaveproductoptions;
?>