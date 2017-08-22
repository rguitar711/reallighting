<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$GLOBALS['menupoplimit']=='') $GLOBALS['menupoplimit']=9;
$GLOBALS['menuid']='';
if(@$GLOBALS['menustyle']=='') $GLOBALS['menustyle']='';
$ectself=explode('?',@$_SERVER['PHP_SELF']);
$ecturi=explode('?',@$_SERVER['REQUEST_URI']);
global $hasrewrite;
$hasrewrite=$ectself[0]!=$ecturi[0];
function join2paths($stourl,$securl){
	global $hasrewrite;
	if(strpos($securl,'://')!==FALSE)
		return($securl);
	if($securl[0]!='/'&&$hasrewrite)
		return(($stourl!=''?$stourl:str_replace('//','/',dirname(@$_SERVER['PHP_SELF']).'/')).$securl);
	if($securl[0]=='/'&&$stourl!=''){
		$urlparts=parse_url($stourl);
		$pos=strrpos($stourl,$urlparts['path']);
		if($pos!==FALSE) $stourl=substr_replace($stourl,'',$pos,strlen($urlparts['path']));
	}
	return($stourl.$securl);
}
function mwritemenulevel($id,$itlevel,$incatalogroot){
	global $mAlldata,$numrows,$menupoplimit,$menuprestr,$storeurl,$menucategoriesatroot,$incstoreurl,$menustyle,$jsstr,$menuid,$catalogroot,$seocategoryurls,$seocaturlpattern,$seoprodurlpattern;
	$hassub=FALSE;
	if($itlevel<=$menupoplimit){
		if(! (@$menucategoriesatroot===2 && $id==0)){
			for($mIndex=0;$mIndex < $numrows;$mIndex++){
				if($mAlldata[$mIndex]['topSection']==$id){
					$jsstr.='em['.$mAlldata[$mIndex]['sectionID'].']='.$mAlldata[$mIndex]['topSection'].';';
					if(($menustyle=='horizontalmenu1' || $menustyle=='verticalmenu3') && ! $hassub){
						print '<ul id="ecttop'.$menuid.'_'.$id.'" style="list-style:none;margin:0px;border:0px;'.($id!=0?'display:none;position:absolute;':'').'" class="ectmenu'.($menuid+1).($id!=0?' ectsubmenu'.($menuid+1):'').'">';
						$jsstr.='emt['.$menuid.']['.$id.']=false;';
					}
					$hassub=TRUE;
					$mTID=$mAlldata[$mIndex]['topSection'];
					if($mTID==0) $mTID='';
					$sectionurl=trim($mAlldata[$mIndex][getlangid('sectionurl',2048)]);
					if($menustyle=='horizontalmenu1' || $menustyle=='verticalmenu3'){
						if(! (@$menucategoriesatroot===TRUE && $mAlldata[$mIndex]['sectionID']==$catalogroot)){
							print '<li id="ect'.$menuid.'_'.$mAlldata[$mIndex]['sectionID'].'" class="ectmenu'.($menuid+1).($id!=0?' ectsubmenu'.($menuid+1):'').'" onmouseover="openpop(this,'.($menustyle=='verticalmenu3'?'true':'false').')" onmouseout="closepop(this)" style="list-style:none;'.($id!=0 || $menustyle=='verticalmenu3'?'margin-bottom:-1px':'display:inline;margin-right:-1px').'">';
							if($sectionurl!=''){
								if($incatalogroot && strpos($sectionurl,'://')===FALSE) $caturl=getcatid($sectionurl,@$seocategoryurls?$sectionurl:'',$mAlldata[$mIndex]['rootSection']==1?$seoprodurlpattern:$seocaturlpattern); else $caturl=$sectionurl;
								print '<a href="'.join2paths($incstoreurl,$caturl).'">'.str_replace('<','&lt;',$mAlldata[$mIndex][getlangid('sectionName',256)])."</a>\r\n";
							}else{
								if($mAlldata[$mIndex]['rootSection']==0)
									print '<a href="'.join2paths($incstoreurl,(!@$seocategoryurls?'categories.php?cat=':'').getcatid($mAlldata[$mIndex]['sectionID'],$mAlldata[$mIndex][getlangid('sectionName',256)],$seocaturlpattern)).'">'.str_replace('<','&lt;',$mAlldata[$mIndex][getlangid('sectionName',256)])."</a>\r\n";
								else
									print '<a href="'.join2paths($incstoreurl,(!@$seocategoryurls?'products.php?cat=':'').getcatid($mAlldata[$mIndex]['sectionID'],$mAlldata[$mIndex][getlangid('sectionName',256)],$seoprodurlpattern)).'">'.str_replace('<','&lt;',$mAlldata[$mIndex][getlangid('sectionName',256)])."</a>\r\n";
							}
							print '</li>';
						}
					}else{
						$menuheadsec='mymenu.addSubMenu("products' . $mTID . '",';
						if(@$menucategoriesatroot===1) $menuheadsec='mymenu.addMenu(';
						if($sectionurl!=''){
							print $menuheadsec.'"products' . $mAlldata[$mIndex]['sectionID'] . '","' . @$menuprestr . str_replace('"','\"',$mAlldata[$mIndex][getlangid('sectionName',256)]) . @$menupoststr . '","' . join2paths($incstoreurl,$sectionurl) . "\");\n";
						}else{
							if($mAlldata[$mIndex]['rootSection']==0)
								print $menuheadsec.'"products' . $mAlldata[$mIndex]['sectionID'] . '","' . @$menuprestr . str_replace('"','\"',$mAlldata[$mIndex][getlangid('sectionName',256)]) . @$menupoststr . '","'.join2paths($incstoreurl,(!@$seocategoryurls?'categories.php?cat=':'') . getcatid($mAlldata[$mIndex]['sectionID'],$mAlldata[$mIndex][getlangid('sectionName',256)],$seocaturlpattern)) . "\");\n";
							else
								print $menuheadsec.'"products' . $mAlldata[$mIndex]['sectionID'] . '","' . @$menuprestr . str_replace('"','\"',$mAlldata[$mIndex][getlangid('sectionName',256)]) . @$menupoststr . '","'.join2paths($incstoreurl,(!@$seocategoryurls?'products.php?cat=':'') . getcatid($mAlldata[$mIndex]['sectionID'],$mAlldata[$mIndex][getlangid('sectionName',256)],$seoprodurlpattern)) . "\");\n";
						}
					}
				}
			}
			if(($menustyle=='horizontalmenu1' || $menustyle=='verticalmenu3') && $hassub) print '</ul>';
		}
		for($mIndex=0;$mIndex < $numrows;$mIndex++)
			if($mAlldata[$mIndex]['topSection']==$id && $mAlldata[$mIndex]['rootSection']==0 && @$menucategoriesatroot!==1) mwritemenulevel($mAlldata[$mIndex]['sectionID'],$itlevel+1,$incatalogroot || $mAlldata[$mIndex]['sectionID']==$catalogroot);
	}
}
function mstrdpth($mstr,$dep){
	$mstrd='';
	for($index=2; $index<=$dep; $index++){
		$mstrd.=$mstr.' ';
	}
	return($mstrd);
}
if(strtolower($GLOBALS['adminencoding'])=='iso-8859-1') $GLOBALS['raquo']='»'; else $GLOBALS['raquo']='&raquo;';
function cssmenulevel($id,$itlevel,$incatalogroot){
	global $menupoplimit,$menucategoriesatroot,$mAlldata,$jsstr,$numrows,$menuid,$catalogroot,$incstoreurl,$seocategoryurls,$seocaturlpattern,$seoprodurlpattern,$raquo;
	if($itlevel<=$menupoplimit){
		for($mIndex=0;$mIndex < $numrows;$mIndex++){
			if($mAlldata[$mIndex]['topSection']==$id && ! (@$menucategoriesatroot===TRUE && $mAlldata[$mIndex]['sectionID']==$catalogroot)){
				$jsstr.='em['.$mAlldata[$mIndex]['sectionID'].']='.$mAlldata[$mIndex]['topSection'].';';
				$sectionurl=trim($mAlldata[$mIndex][getlangid('sectionurl',2048)]);
				if($mAlldata[$mIndex]['rootSection']==0){
					if($itlevel==$menupoplimit)$mlink=join2paths($incstoreurl,(!@$seocategoryurls?'categories.php?cat=':'').getcatid($mAlldata[$mIndex]['sectionID'],$mAlldata[$mIndex][getlangid('sectionName',256)],$seocaturlpattern)); else $mlink='#';
				}elseif($sectionurl!=''){
					if($incatalogroot && strpos($sectionurl,'://')===FALSE) $caturl=getcatid($sectionurl,@$seocategoryurls?$sectionurl:'',$mAlldata[$mIndex]['rootSection']==1?$seoprodurlpattern:$seocaturlpattern); else $caturl=$sectionurl;
					$mlink=join2paths($incstoreurl,$caturl);
				}else
					$mlink=join2paths($incstoreurl,(!@$seocategoryurls?'products.php?cat=':'').getcatid($mAlldata[$mIndex]['sectionID'],$mAlldata[$mIndex][getlangid('sectionName',256)],$seoprodurlpattern));
				print '<li class="ectmenu'.($menuid+1).($id!=0?' ectsubmenu'.($menuid+1):'').'" id="ect'.$menuid.'_'.$mAlldata[$mIndex]['sectionID'].'" onclick="return(ectChCk(this))" style="'.($mAlldata[$mIndex]['topSection']!=0?'display:none;':'').'margin-bottom:-1px"><a style="display:block" href="' . $mlink . '">' . ($id!=0?mstrdpth($raquo,$itlevel):'') . str_replace('<','&lt;',$mAlldata[$mIndex][getlangid('sectionName',256)]) . "</a></li>\r\n";
				$jsstr.='emt['.$menuid.']['.$id.']=false;';
				cssmenulevel($mAlldata[$mIndex]['sectionID'],$itlevel+1,$incatalogroot || $mAlldata[$mIndex]['sectionID']==$catalogroot);
			}
		}
	}
}
function writesubmenus(){
	global $menucategoriesatroot,$catalogroot;
	$menucategoriesatroot=2;
	mwritemenulevel(0,2,$catalogroot==0);
}
function displayectmenu($menstyle){
	global $jsstr,$menupoplimit,$numrows,$mAlldata,$menuid,$menustyle,$menucategoriesatroot,$catalogroot,$alreadygotadmin,$sortcategoriesalphabetically,$incstoreurl,$storeurl;
	if(@$_SESSION['clientLoginLevel']!='') $minloglevel=$_SESSION['clientLoginLevel']; else $minloglevel=0;
	if(@$menuid==='') $menuid=0; else $menuid++;
	$menustyle=$menstyle;
	$alreadygotadmin=getadminsettings();
	if(@$_SERVER['HTTPS']=='on' || @$_SERVER['SERVER_PORT']=='443') $incstoreurl=$storeurl; else $incstoreurl='';
	//print '$incstoreurl: ' . $incstoreurl . "<br>";
	$sSQL='SELECT sectionID,'.getlangid('sectionName',256).',topSection,rootSection,'.getlangid('sectionurl',2048).' FROM sections WHERE sectionDisabled<=' . $minloglevel . ($menupoplimit<=1?' AND topSection=0':'') . ' ORDER BY ' . (@$sortcategoriesalphabetically==TRUE ? getlangid('sectionName',256) : 'sectionOrder') . (@$menustyle=='verticalmenu2'?',topSection':'');
	$result=ect_query($sSQL) or ect_error();
	$numrows=0;
	$jsstr='';
	if(ect_num_rows($result) > 0){
		$theroot=$catalogroot;
		while($rs=ect_fetch_assoc($result)){
			$mAlldata[$numrows++]=$rs;
			if($rs['sectionID']==$catalogroot) $theroot=$rs['topSection'];
		}
		if(@$menucategoriesatroot===TRUE && ($menustyle=='verticalmenu2' || $menustyle=='horizontalmenu1' || $menustyle=='verticalmenu3')){
			for($mIndex=0;$mIndex < $numrows;$mIndex++){
				if($mAlldata[$mIndex]['topSection']==$catalogroot) $mAlldata[$mIndex]['topSection']=$theroot;
			}
		}
		if($menustyle=='verticalmenu2'){
			print '<ul class="ectmenu'.($menuid+1).'" style="list-style:none">';
			cssmenulevel(0,1,$catalogroot==0);
			print '</ul>';
		}elseif($menustyle=='horizontalmenu1' || $menustyle=='verticalmenu3')
			mwritemenulevel(0,1,$catalogroot==0);
		else
			mwritemenulevel(0,1,$catalogroot==0);
	}
	ect_free_result($result);
	if($menustyle=='horizontalmenu1' || $menustyle=='verticalmenu2' || $menustyle=='verticalmenu3'){ ?>
<script type="text/javascript">
/* <![CDATA[ */
<?php
		if($menuid==0){
			print 'var curmen=[];var lastmen=[];var em=[];var emt=[];' . "\r\n";
			writemenuscripts();
		}
		print 'emt['.$menuid.']=new Array();curmen['.$menuid."]=0;\r\n";
		print $jsstr . "\r\n";
		print 'addsubsclass('.$menuid.",0)\r\n";
		print '/* ]]> */</script>';
	}
}
function writemenuscripts(){
?>
function closepopdelay(menid){
	var re=new RegExp('ect\\d+_');
	var theid=menid.replace(re,'');
	var mennum=menid.replace('ect','').replace(/_\d+/,'');
	for(var ei in emt[mennum]){
		if(ei!=0&&emt[mennum][ei]==true&&!insubmenu(ei,mennum)){
			document.getElementById('ecttop'+mennum+"_"+ei).style.display='none';
			emt[mennum][ei]=false; // closed
		}
	}
}
function closepop(men){
	var mennum=men.id.replace('ect','').replace(/_\d+/,'');
	lastmen[mennum]=curmen[mennum];
	curmen[mennum]=0;
	setTimeout("closepopdelay('"+men.id+"')",1000);
}
function getPos(el){
	for (var lx=0,ly=0; el!=null; lx+=el.offsetLeft,ly+=el.offsetTop, el=el.offsetParent){
	};
	return{x:lx,y:ly};
}
function openpop(men,ispopout){
	var re=new RegExp('ect\\d+_');
	var theid=men.id.replace(re,'');
	var mennum=men.id.replace('ect','').replace(/_\d+/,'');
	curmen[mennum]=theid;
	if(lastmen[mennum]!=0)
		closepopdelay('ect'+mennum+'_'+lastmen[mennum]);
	if(mentop=document.getElementById('ecttop'+mennum+'_'+theid)){
		var px=getPos(men);
		if(em[theid]==0&&!ispopout){
			mentop.style.left=px.x+'px';
			mentop.style.top=(px.y+men.offsetHeight-1)+'px';
			mentop.style.display='';
		}else{
			mentop.style.left=(px.x+men.offsetWidth-1)+'px';
			mentop.style.top=px.y+'px';
			mentop.style.display='';
		}
		emt[mennum][theid]=true; // open
	}
}
function hassubs(men){
	var re=new RegExp('ect\\d+_');
	var theid=men.id.replace(re,'');
	for(var ei in em){
		if(em[ei]==theid)
			return(true);
	}
	return(false);
}
function closecascade(men){
	var re=new RegExp('ect\\d+_');
	var theid=men.id.replace(re,'');
	var mennum=men.id.replace('ect','').replace(/_\d+/,'');
	curmen[mennum]=0;
	for(var ei in emt[mennum]){
		if(ei!=0&&emt[mennum][ei]==true&&!insubmenu(ei,mennum)){
			for(var ei2 in em){
				if(em[ei2]==ei){
					document.getElementById('ect'+mennum+"_"+ei2).style.display='none';
				}
			}
		}
	}
	emt[mennum][theid]=false; // closed
	return(false);
}
function opencascade(men){
	var re=new RegExp('ect\\d+_');
	var theid=men.id.replace(re,'');
	var mennum=men.id.replace('ect','').replace(/_\d+/,'');
	if(emt[mennum][theid]==true) return(closecascade(men));
	var mennum=men.id.replace('ect','').replace(/_\d+/,'');
	curmen[mennum]=theid;
	for(var ei in em){
		if(em[ei]==theid){
			document.getElementById('ect'+mennum+'_'+ei).style.display='';
			emt[mennum][theid]=true; // open
		}
	}
	return(false);
}
function ectChCk(men){
return(hassubs(men)?opencascade(men):true)
}
function writedbg(txt){
	if(document.getElementById('debugdiv')) document.getElementById('debugdiv').innerHTML+=txt+"<br />";
}
function insubmenu(mei,mid){
	if(curmen[mid]==0)return(false);
	curm=curmen[mid];
	maxloops=0;
	while(curm!=0){
		if(mei==curm)return(true);
		curm=em[curm];
		if(maxloops++>10) break;
	}
	return(false);
}
function addsubsclass(mennum,menid){
	for(var ei in em){
		if(typeof(emt[mennum][ei])=='boolean'){
			men=document.getElementById('ect'+mennum+'_'+ei);
			if(men.className.indexOf('ectmenuhassub')==-1)men.className+=' ectmenuhassub'+(mennum+1);
		}
	}
}
<?php
} // writemenuscripts
if(@$GLOBALS['menuid']==''){
	displayectmenu($GLOBALS['menustyle']);
}
?>