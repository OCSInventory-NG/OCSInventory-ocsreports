<?php
function hidden($protectedPost,$name_field){
	foreach ($protectedPost as $key=>$value){
			if ($key != 'cat' 
				and $key != 'old_cat' 
				and $key != 'onglet' 
				and $key != 'old_onglet'
				and !in_array($key ,$name_field) ){
				$tab_hidden[$key]=$value;
			}
		}
	return $tab_hidden;	
}

function update_field_gui($field,$data,$id_field_modif){
	
	
}


?>