<?php
/*
 * Add tags for users
 * 
 */
 
//require ('fichierConf.class.php');
$form_name='taguser';
//$ban_head='no';
//$no_error='YES';
//require_once("header.php");
if (!($_SESSION["lvluser"] == SADMIN or $_SESSION['TRUE_LVL'] == SADMIN))
	die("FORBIDDEN");
printEnTete($l->g(616)." ".$protectedGet["id"] );
if( $protectedPost['ADD_TAG'] != "" ) {
	$tab_options['CACHE']='RESET';
	$tbi = $protectedPost["newtag"] ;
	@mysql_query( "INSERT INTO tags(tag,login) VALUES('".$tbi."','".$protectedGet["id"]."')", $_SESSION["writeServer"]  );
}
//suppression d'une liste de tag
if (isset($protectedPost['del_check']) and $protectedPost['del_check'] != ''){
	$list = "'".implode("','", explode(",",$protectedPost['del_check']))."'";
	$sql_delete="DELETE FROM tags WHERE tag in (".$list.") AND login='".$protectedGet["id"]."'";
	mysql_query($sql_delete, $_SESSION["writeServer"]) or die(mysql_error($_SESSION["writeServer"]));	
	$tab_options['CACHE']='RESET';	
}

if(isset($protectedPost['SUP_PROF'])) {
	//$tbd = $protectedGet["supptag"];
	@mysql_query( "DELETE FROM tags WHERE tag='".$protectedPost['SUP_PROF']."' AND login='".$protectedGet["id"]."'", $_SESSION["writeServer"]  );
}
echo "<br><form name='".$form_name."' id='".$form_name."' method='POST'>";
$reqTags ="select tag from tags where login='".$protectedGet['id']."'";
$resTags = mysql_query( $reqTags, $_SESSION["readServer"] );
$valTags = mysql_fetch_array( $resTags );
if (isset($valTags['tag'])){
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';
	if (!(isset($protectedPost["pcparpage"])))
		 $protectedPost["pcparpage"]=5;
	$list_fields= array($_SESSION['TAG_LBL']=>'tag',
						'SUP'=>'tag',
						'CHECK'=>'tag');
	$list_col_cant_del=array('ID'=>'ID','SUP'=>'SUP','CHECK'=>'CHECK');
	$default_fields=$list_fields; 
	$queryDetails = 'SELECT ';
	foreach ($list_fields as $key=>$value){
		if($key != 'SUP' and $key != 'CHECK')
		$queryDetails .= $value.',';		
	} 
	$queryDetails=substr($queryDetails,0,-1);
	$queryDetails .= " FROM tags where login='".$protectedGet['id']."'";
	$tab_options['FILTRE']=array($_SESSION['TAG_LBL']=>$_SESSION['TAG_LBL']);
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,100,$tab_options);
	//traitement par lot
	$img['image/sup_search.png']=$l->g(162);
	echo "<script language=javascript>
			function garde_check(image,id)
			 {
				var idchecked = '';
				for(i=0; i<document.".$form_name.".elements.length; i++)
				{
					if(document.".$form_name.".elements[i].name.substring(0,5) == 'check'){
				        if (document.".$form_name.".elements[i].checked)
							idchecked = idchecked + document.".$form_name.".elements[i].name.substring(5) + ',';
					}
				}
				idchecked = idchecked.substr(0,(idchecked.length -1));
				confirme('',idchecked,\"".$form_name."\",\"del_check\",\"".$l->g(900)."\");
			}
		</script>";
		echo "<table align='center' width='30%' border='0'>";
		echo "<tr><td>";
		//foreach ($img as $key=>$value){
			echo "<td align=center><a href=# onclick=garde_check(\"image/sup_search.png\",\"\")><img src='image/sup_search.png' title='".$l->g(162)."' ></a></td>";
		//}
	 echo "</tr></tr></table>";
	 echo "<input type='hidden' id='del_check' name='del_check' value=''>";
	
	
}	
//
echo "<FONT FACE='tahoma' SIZE=2>";
echo $l->g(617)." ".$_SESSION['TAG_LBL'].": <input type='text' id='newtag' name='newtag' value='".$protectedPost['newtag']."'>
		<input type='submit' name='ADD_TAG' value='envoyer'>";
echo "</form>";
?>

