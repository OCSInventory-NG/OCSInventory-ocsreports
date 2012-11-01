<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Erwan GOALOU 2010 (erwan(at)ocsinventory-ng(pt)org)
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================

/*
 * New version of dico page 
 * 
 */
//require_once('require/function_table_html.php');
require_once('require/function_dico.php');
//use or not cache
if ($_SESSION['OCS']['usecache'])
	$table="softwares_name_cache";
else
	$table="softwares";
//form name
$form_name='admin_param';
//form open
echo open_form($form_name);
//definition of onglet
$def_onglets['CAT']=$l->g(1027); //Categories
$def_onglets['NEW']=$l->g(1028); //nouveau logiciels
$def_onglets['IGNORED']=$l->g(1029); //ignor.
$def_onglets['UNCHANGED']=$l->g(1030); //unchanged
//d�fault => first onglet
if ($protectedPost['onglet'] == "")
$protectedPost['onglet']="CAT";
//reset search
if ($protectedPost['RESET']=="RESET")
unset($protectedPost['search']);
//filtre
if ($protectedPost['search']){
	$search_cache=" and cache.name like '%".mysql_real_escape_string($protectedPost['search'])."%' ";
	$search_count=" and extracted like '%".mysql_real_escape_string($protectedPost['search'])."%' ";
}
else{
	$search="";
	$search_count = "";
}
//show first lign of onglet
onglet($def_onglets,$form_name,"onglet",0);
echo '<div class="mlt_bordure" >';
//attention=> result with restriction
if ($search_count != "" or $search_cache != "")
msg_warning($l->g(767));
/**************************************ACTION ON DICO SOFT**************************************/

//transfert soft
if($protectedPost['TRANS'] == "TRANS"){	
	if ($protectedPost['all_item'] != ''){
		$list_check=search_all_item($protectedPost['onglet'],$protectedPost['onglet_soft']);
	}else{
		
		foreach ($protectedPost as $key=>$value){
			if (substr($key, 0, 5) == "check"){
				$list_check[]=substr($key, 5);
			} 				
		}
	}
	if ($list_check != '')	
	trans($protectedPost['onglet'],$list_check,$protectedPost['AFFECT_TYPE'],$protectedPost['NEW_CAT'],$protectedPost['EXIST_CAT']);	
}
//delete a soft in list => return in 'NEW' liste
if ($protectedPost['SUP_PROF'] != ""){
	del_soft($protectedPost['onglet'],array($protectedPost['SUP_PROF']));
}
/************************************END ACTION**************************************/

if ($protectedPost['onglet'] != $protectedPost['old_onglet'])
unset($protectedPost['onglet_soft']);
/*******************************************************CAS OF CATEGORIES*******************************************************/
if ($protectedPost['onglet'] == 'CAT'){
	//search all categories
	$sql_list_cat="select formatted  name
		  from dico_soft where extracted!=formatted ".$search_count." group by formatted";
	 $result_list_cat = mysql_query( $sql_list_cat, $_SESSION['OCS']["readServer"]);
	 $i=1;
	 while($item_list_cat = mysql_fetch_object($result_list_cat)){
	 	if ($i==1)
		$first_onglet=$i;
		$list_cat[$i]=$item_list_cat -> name;
		$i++;
	 }
	 //delete categorie
	if(isset($protectedPost['SUP_CAT']) and $protectedPost['SUP_CAT']!=""){	
		if ($protectedPost['SUP_CAT'] == 1)
		$first_onglet=2;
		$reqDcat = "DELETE FROM dico_soft WHERE formatted='".$list_cat[$protectedPost['SUP_CAT']]."'";
		mysql_query($reqDcat, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));
		unset($list_cat[$protectedPost['SUP_CAT']]);		
	}
	//no selected? default=>first onglet
	 
	 	 if ($protectedPost['onglet_soft']=="" or !isset($list_cat[$protectedPost['onglet_soft']]))
			 $protectedPost['onglet_soft']=$first_onglet;	
		 //show all categories
		 if ($i<=20)
		 onglet($list_cat,$form_name,"onglet_soft",5);
		 else
		 echo $l->g(398) . ": ".show_modif($list_cat,'onglet_soft',2,$form_name)."<br>";
		 //You can delete or not?
		  if ($i != 1 and isset($list_cat[$protectedPost['onglet_soft']]))
		 echo "<a href=# OnClick='return confirme(\"\",\"".$protectedPost['onglet_soft']."\",\"".$form_name."\",\"SUP_CAT\",\"".$l->g(640)."\");'>".$l->g(921)."</a></td></tr><tr><td>";
		$list_fields= array('SOFT_NAME'=>'EXTRACTED',
						'ID'=>'ID',
						'SUP'=>'ID',
						'CHECK'=>'ID'
								);
		$table_name="CAT_EXIST";
		$default_fields= array('SOFT_NAME'=>'SOFT_NAME','SUP'=>'SUP','CHECK'=>'CHECK');
		$list_col_cant_del=array('SOFT_NAME'=>'SOFT_NAME','CHECK'=>'CHECK');
		$querydico = 'SELECT ';
		foreach ($list_fields as $key=>$value){
			if($key != 'SUP' and $key != 'CHECK')
			$querydico .= $value.',';		
		} 
		$querydico=substr($querydico,0,-1);
		$querydico .= " from dico_soft left join ".$table." cache on dico_soft.extracted=cache.name
				 where formatted='".mysql_real_escape_string($list_cat[$protectedPost['onglet_soft']])."' ".$search_count." group by EXTRACTED";
}
/*******************************************************CAS OF NEW*******************************************************/
if ($protectedPost['onglet'] == 'NEW'){
  /* MG
	$search_dico_soft="select extracted name from dico_soft";
	$result_search_dico_soft = mysql_query( $search_dico_soft, $_SESSION['OCS']["readServer"]);
	$list_dico_soft="'";
	while($item_search_dico_soft = mysql_fetch_object($result_search_dico_soft)){
		$list_dico_soft.=addslashes($item_search_dico_soft -> name)."','";
	}
	$list_dico_soft=substr($list_dico_soft,0,-2);
	
	if($list_dico_soft == "")
		$list_dico_soft="''";
		
	$search_ignored_soft="select extracted name from dico_ignored";
	$result_search_ignored_soft = mysql_query( $search_ignored_soft, $_SESSION['OCS']["readServer"]);
	$list_ignored_soft="'";
	while($item_search_ignored_soft = mysql_fetch_object($result_search_ignored_soft)){
		$list_ignored_soft.=addslashes($item_search_ignored_soft -> name)."','";
	}
	$list_ignored_soft=substr($list_ignored_soft,0,-2);
	
	if($list_ignored_soft == "")
	$list_ignored_soft="''";

	$sql_list_alpha="select distinct substr(trim(name),1,1) alpha
				 from ".$table." cache 
				 where substr(trim(name),1,1) is not null and name not in (".$list_dico_soft.")
			and name not in (".$list_ignored_soft.") ".$search_cache;	
  */
  $sql_list_alpha="select
    distinct left(trim(name),1) alpha
    from ".$table." cache
    where name is not null
    and name not in (select extracted name from dico_soft)
    and name not in (select extracted name from dico_ignored) ".$search_cache;
	$first='';
	//execute the query only if necessary 
  /* MG
	if($_SESSION['OCS']['REQ_ONGLET_SOFT'] != $sql_list_alpha){
  */
		$result_list_alpha = mysql_query( $sql_list_alpha, $_SESSION['OCS']["readServer"]);
		$i=1;
		 while($item_list_alpha = mysql_fetch_object($result_list_alpha)){
		 	if (mb_strtoupper($item_list_alpha -> alpha) != ""){
					if ($first == ''){
						$first=$i;
					}
					$list_alpha[$i]=mb_strtoupper($item_list_alpha -> alpha);
					$i++;
		 	}
		}
		//execute the query only if necessary 
		$_SESSION['OCS']['REQ_ONGLET_SOFT'] = $sql_list_alpha;
		$_SESSION['OCS']['ONGLET_SOFT'] = $list_alpha;
		$_SESSION['OCS']['FIRST_DICO'] = $first;
  /* MG  
	}else{
		$list_alpha=$_SESSION['OCS']['ONGLET_SOFT'];
	}
  */
	if (!isset($protectedPost['onglet_soft']))
	$protectedPost['onglet_soft']=$_SESSION['OCS']['FIRST_DICO'];
	 onglet($list_alpha,$form_name,"onglet_soft",20);
	
	//search all soft for the tab as selected 
  /* MG
	$search_soft="select distinct name from ".$table." cache
			where name like '".$_SESSION['OCS']['ONGLET_SOFT'][$protectedPost['onglet_soft']]."%'
			and name not in (".$list_dico_soft.")
			and name not in (".$list_ignored_soft.") ".$search_cache;
	*/
  $search_soft="select distinct trim(name) name from ".$table." cache
    where name like '".$_SESSION['OCS']['ONGLET_SOFT'][$protectedPost['onglet_soft']]."%'
    and name not in (select extracted name from dico_soft)
    and name not in (select extracted name from dico_ignored) ".$search_cache;
  $result_search_soft = mysql_query( $search_soft, $_SESSION['OCS']["readServer"]);
	$list_soft="'";
 	while($item_search_soft = mysql_fetch_object($result_search_soft)){
		 		$list_soft.=addslashes($item_search_soft -> name)."','";
 	}
 	$list_soft=substr($list_soft,0,-2);
 	if ($list_soft == "")
 	$list_soft="''";

	$list_fields= array('SOFT_NAME'=>'NAME',
						'ID'=>'ID',
	 					 'QTE'=> 'QTE',
    					 'CHECK'=>'ID');
	$table_name="CAT_NEW";
	$default_fields= array('SOFT_NAME'=>'SOFT_NAME','QTE'=>'QTE','CHECK'=>'CHECK');
	$list_col_cant_del=array('SOFT_NAME'=>'SOFT_NAME','CHECK'=>'CHECK');
	$querydico = 'SELECT ';
	foreach ($list_fields as $key=>$value){
		if($key != 'CHECK' and $key != 'QTE')
		$querydico .= $value.',';		
		elseif ($key == 'QTE')
		$querydico .= ' count(NAME) as '.$value.',';
	} 
	$querydico=substr($querydico,0,-1);
	$querydico .= " from softwares 
			where name in (".$list_soft.") and name != ''
			group by name ";
}
/*******************************************************CAS OF IGNORED*******************************************************/
if ($protectedPost['onglet'] == 'IGNORED'){
	$list_fields= array('SOFT_NAME'=>'EXTRACTED',
						'ID'=>'ID',
						'SUP'=>'ID',
						'CHECK'=>'ID'
								);
	$table_name="CAT_IGNORED";
	$default_fields= array('SOFT_NAME'=>'SOFT_NAME','SUP'=>'SUP','CHECK'=>'CHECK');
	$list_col_cant_del=array('SOFT_NAME'=>'SOFT_NAME','CHECK'=>'CHECK');
	$querydico = 'SELECT ';
	foreach ($list_fields as $key=>$value){
		if($key != 'SUP' and $key != 'CHECK')
		$querydico .= $value.',';		
	} 
	if ($search_count != ""){
		$modif_search = " where ".substr($search_count,5);
	}
	$querydico=substr($querydico,0,-1);
	$querydico .= " from dico_ignored left join ".$table." cache on cache.name=dico_ignored.extracted ".$modif_search." group by EXTRACTED ";
}
/*******************************************************CAS OF UNCHANGED*******************************************************/
if ($protectedPost['onglet'] == 'UNCHANGED'){
	$list_fields= array('SOFT_NAME'=>'EXTRACTED',
						'ID'=>'ID',
						'SUP'=>'ID',
						'CHECK'=>'ID'
								);
	$table_name="CAT_UNCHANGE";
	$default_fields= array('SOFT_NAME'=>'SOFT_NAME','SUP'=>'SUP','CHECK'=>'CHECK');
	$list_col_cant_del=array('SOFT_NAME'=>'SOFT_NAME','CHECK'=>'CHECK');
	$querydico = 'SELECT ';
	foreach ($list_fields as $key=>$value){
		if($key != 'SUP' and $key != 'CHECK')
		$querydico .= $value.',';		
	} 
	$querydico=substr($querydico,0,-1);
	$querydico .= " from dico_soft left join ".$table." cache on cache.name=dico_soft.extracted
	 	where extracted=formatted ".$search_cache." group by EXTRACTED ";
}
if (isset($querydico)){
	$_SESSION['OCS']['query_dico']=$querydico;
	$tab_options['LIEN_LBL']['QTE']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_multi_search'].'&prov=allsoft&value=';
	$tab_options['LIEN_CHAMP']['QTE']='NAME';
	$tab_options['LBL']['SOFT_NAME']=$l->g(382);
	$tab_options['LBL']['QTE']=$l->g(55);
	$result_exist=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$querydico,$form_name,80,$tab_options); 
}
echo "</td></tr>";
$search=show_modif(stripslashes($protectedPost['search']),"search",'0');
$trans= "<input name='all_item' id='all_item' type='checkbox' ".(isset($protectedPost['all_item'])? " checked ": "").">".$l->g(384) . " ";
//r�cup�ration de toutes les cat�gories
$list_categories['IGNORED']="IGNORED";
$list_categories['UNCHANGED']="UNCHANGED";
$sql_list_categories="select distinct(formatted) name from dico_soft where formatted!=extracted order by formatted";
$result_list_categories = mysql_query( $sql_list_categories, $_SESSION['OCS']["readServer"]);
while($item_list_categories = mysql_fetch_object($result_list_categories)){
	$list_categories[$item_list_categories ->name]=$item_list_categories ->name;	
}
//d�finition de toutes les options possible
$choix_affect['NEW_CAT']=$l->g(385);
$choix_affect['EXIST_CAT']=$l->g(387);
$trans.=show_modif($choix_affect,"AFFECT_TYPE",'2',$form_name);
if ($protectedPost['AFFECT_TYPE'] == 'EXIST_CAT'){
	$trans.=show_modif($list_categories,"EXIST_CAT",'2');	
	$verif_field="EXIST_CAT";
}
elseif ($protectedPost['AFFECT_TYPE'] == 'NEW_CAT'){
	$trans.=show_modif(stripslashes($protectedPost['NEW_CAT']),"NEW_CAT",'0');
	$verif_field="NEW_CAT";
}	

if ($protectedPost['AFFECT_TYPE']!='')
$trans.= "<input type='button' name='TRANSF' value='".$l->g(13)."' onclick='return verif_field(\"".$verif_field."\",\"TRANS\",\"".$form_name."\");'>";

echo "<tr><td>".$search."<input type='submit' value='".$l->g(393)."'><input type='button' value='".$l->g(396)."' onclick='return pag(\"RESET\",\"RESET\",\"".$form_name."\");'>";
if ($result_exist != FALSE)
echo "<div align=right> ".$trans."</div>";
echo "</td></tr></table>";
echo '</div>';
echo "<input type='hidden' name='RESET' id='RESET' value=''>";
echo "<input type='hidden' name='TRANS' id='TRANS' value=''>";
echo "<input type='hidden' name='SUP_CAT' id='SUP_CAT' value=''>";
echo close_form();
?>