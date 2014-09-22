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


$chiffres="onKeyPress=\"return scanTouche(event,/[0-9]/)\" onkeydown='convertToUpper(this)'
		  onkeyup='convertToUpper(this)' 
		  onblur='convertToUpper(this)'
		  onclick='convertToUpper(this)'";
$majuscule="onKeyPress=\"return scanTouche(event,/[0-9 a-z A-Z]/)\" onkeydown='convertToUpper(this)'
		  onkeyup='convertToUpper(this)' 
		  onblur='convertToUpper(this)'";
$sql_field="onKeyPress=\"return scanTouche(event,/[0-9a-zA-Z_-]/)\" onkeydown='convertToUpper(this)'
		  onkeyup='convertToUpper(this)' 
		  onblur='convertToUpper(this)'";

if( ! function_exists ( "utf8_decode" )) {
	function utf8_decode($st) {
		return $st;
	}
}
 
 
function printEnTete($ent) {
	echo "<h3>$ent</h3>";
}
 
 
/**
  * Includes the javascript datetime picker
  */
function incPicker() {

	global $l;

	echo "<script language=\"javascript\">
	var MonthName=[";
	
	for( $mois=527; $mois<538; $mois++ )
		echo "\"".$l->g($mois)."\",";
	echo "\"".$l->g(538)."\"";
	
	echo "];
	var WeekDayName=[";
	
	for( $jour=539; $jour<545; $jour++ )
		echo "\"".$l->g($jour)."\",";
	echo "\"".$l->g(545)."\"";	
	
	echo "];
	</script>	
		<script language=\"javascript\" type=\"text/javascript\" src=\"js/datetimepicker.js\">
	</script>";
}
 
 
function dateOnClick($input, $checkOnClick=false) {
	global $l;
	$dateForm = $l->g(269) == "%m/%d/%Y" ? "MMDDYYYY" : "DDMMYYYY" ;
	if( $checkOnClick ) $cOn = ",'$checkOnClick'";
	$ret = "OnClick=\"javascript:NewCal('$input','$dateForm',false,24{$cOn});\"";
	return $ret;
}

function datePick($input, $checkOnClick=false) {
	global $l;
	$dateForm = $l->g(269) == "%m/%d/%Y" ? "MMDDYYYY" : "DDMMYYYY" ;
	if( $checkOnClick ) $cOn = ",'$checkOnClick'";
	$ret = "<a href=\"javascript:NewCal('$input','$dateForm',false,24{$cOn});\">";
	$ret .= "<img src=\"image/cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Pick a date\"></a>";
	return $ret;
}
 
 
 
 /*
  * 
  * This function check an mail addresse 
  * 
  */  
 function VerifyMailadd($addresse)
{
   $Syntaxe='#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#';
   if(preg_match($Syntaxe,$addresse))
      return true;
   else
     return false;
}
 
function send_mail($mail_to,$subjet,$body){
	global $l;
// few personnes
	$to="";
	if (is_array($mail_to)){
		$to = implode(',',$mail_to);
	}else
     $to  = $mail_to;

     // message
     $message = '
     <html>
      <head>
       <title>' . $subjet . '</title>
      </head>
      <body>
       ' . $body . '
      </body>
     </html>
     ';

     // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
     $headers  = 'MIME-Version: 1.0' . "\r\n";
     $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

     // En-têtes additionnels
     $headers .= 'To: '. implode(',',$mail_to) . "\r\n";
     $headers .= 'From: Ocsinventory <Ocsinventory@ocsinventory.com>' . "\r\n";
  //   $headers .= 'Cc: anniversaire_archive@example.com' . "\r\n";
   //  $headers .= 'Bcc: anniversaire_verif@example.com' . "\r\n";

     // Envoi
     $test_mail=@mail($to, $subject, $message, $headers);
	if (!$test_mail){
		echo "<script>alert('" . $l->g(1057)."');</script>";		
	}
	
	
}
 
 
function replace_entity_xml($txt){
	$cherche = array("&","<",">","\"","'");
	$replace = array( "&amp;","&lt;","&gt;", "&quot;", "&apos;");
	return str_replace($cherche, $replace, $txt);		
}  


 
function printEnTete_tab($ent) {
	echo "<br><table border=0 WIDTH = '62%' ALIGN = 'Center' CELLPADDING='5'>
	<tr height=40px bgcolor=#f2f2f2 align=center><td><b>".$ent."</b></td></tr></table>";
}
 
//function for escape_string before use database
function escape_string($array){
	if (is_array($array)){
		foreach ($array as $key=>$value){
			$trait_array[$key]=mysqli_real_escape_string($_SESSION['OCS']["readServer"],$value);
		}
		return ($trait_array);
	}else
	return array();	
}

function xml_escape_string($array){
	foreach ($array as $key=>$value){
		$trait_array[$key]=utf8_encode($value);
		$trait_array[$key]=htmlspecialchars($value,ENT_QUOTES);
	}
	return ($trait_array);
}

function xml_encode( $txt ) {
	$cherche = array("&","<",">","\"","'","é","è","ô","Î","î","à","ç","ê","â");
	$replace = array( "&amp;","&lt;","&gt;", "&quot;", "&apos;","&eacute;","&egrave;","&ocirc;","&Icirc;","&icirc;","&agrave;","&ccedil;","&ecirc;","&acirc;");
	return str_replace($cherche, $replace, $txt);		

}

function xml_decode( $txt ) {
	$cherche = array( "&acirc;","&ecirc;","&ccedil;","&agrave;","&lt;","&gt;", "&quot;", "&apos;","&eacute;","&egrave;","&ocirc;","&Icirc;","&icirc;","&amp;");
	$replace = array( "â","ê","ç","à","<",">","\"","'","é","è","ô","Î","î", "&" );
	return str_replace($cherche, $replace, $txt);		
}


//fonction qui permet d'afficher un tableau dynamique de donn�es
/*
 * Columns : Each available column of the table
 * $columns = array {  
 * 						'NAME'=>'h.name', ...
 * 						'Column name' => Database value,
 * 						 }
 * Default_fields : Default columns displayed
 * $default_fields= array{
 * 						'NAME'=>'NAME', ...
 * 						'Column name' => 'Column name',
 * 						}
 * Option : All the options for the specific table
 * $option= array{
 * 						'form_name'=> "show_all",....
 * 						'Option' => value,
 * 	
 * 						}
 * List_col_cant_del : All the columns that will always be displayed
 * $list_col_cant_del= array {  
 * 						'NAME'=>'NAME', ...
 * 						'Column name' => 'Column name',
 * 						}
 */
 function ajaxtab_entete_fixe($columns,$default_fields,$option=array(),$list_col_cant_del)
 {
	global $protectedGet,$protectedPost,$l,$pages_refs;
	//Translated name of the column  
	$lbl_column=array("ACTIONS"=>$l->g(1381),
					  "CHECK"=>"<input type='checkbox' name='ALL' id='checkboxALL' Onclick='checkall();'>");
	if (!isset($tab_options['NO_NAME']['NAME']))
		$lbl_column["NAME"]=$l->g(23);
	
	if(!empty($option['LBL'])){
		$lbl_column= array_merge($lbl_column,$option['LBL']);
	}
	$columns_special = array("CHECK",
							"SUP",
							"NBRE",
							"NULL",
							"MODIF",
							"SELECT",
							"ZIP",
							"OTHER",
							"STAT",
							"ACTIVE",
							"MAC",
							);
	//If the column selected are different from the default columns 
	if(!empty($_COOKIE[$option['table_name']."_col"])){
		$visible_col = unserialize($_COOKIE[$option['table_name']."_col"]);
	}

 	$input = $columns;
 	
 	//Don't allow to hide columns that should not be hidden
	foreach($list_col_cant_del as $key=>$col_cant_del){
		unset($input[$col_cant_del]);
		unset($input[$key]);
	}
	$list_col_can_del = $input;
	$columns_unique = array_unique($columns);
	if(isset($columns['CHECK'])){
		$column_temp = $columns['CHECK'];
		unset($columns['CHECK']);
		$columns_temp['CHECK'] = $column_temp;
		$columns = $columns_temp + $columns;
	}
	$actions = array(
				"MODIF",
				"SUP",
				"ZIP",
				"STAT",
				"ACTIVE",
	);
	$action_visible = false;
	$temp = $columns;
	
	foreach($actions as $action){
		if(isset($columns[$action])){
			$action_visible=true;
			$columns['ACTIONS']="h.ID";
			break;
		}
	}
	//Set the ajax requested address  
	if (isset($_SERVER['QUERY_STRING'])){ 
		if(isset($option['computersectionrequest'])){
			parse_str($_SERVER['QUERY_STRING'],$addressoption);
			unset($addressoption['all']);
			unset($addressoption['cat']);
			$addressoption['option']=$option['computersectionrequest'];
			$address = "ajax.php?".http_build_query($addressoption);
		}else{
			$address = isset($_SERVER['QUERY_STRING'])? "ajax.php?".$_SERVER['QUERY_STRING']: "";
		}
	}
	
?>

<div align=center>
	<div class="table_top_settings">
<?php

	//Display the Column selector
	if (!empty($list_col_can_del)){
	?>
<div>
	<select id="select_col<?php echo $option['table_name']; ?>">
	<?php 
	foreach($list_col_can_del as $key => $col){
		$name = explode('.',$col);
		$name = explode(' as ',end($name));
		$value = end($name);
		if (!empty($option['REPLACE_COLUMN_KEY'][$key])){
			$value = $option['REPLACE_COLUMN_KEY'][$key];
		}
		if(array_key_exists($key,$lbl_column)){
			echo "<option value='$value'>$lbl_column[$key]</option>";
		}
		else{
			echo "<option value='$value'>$key</option>";
		}
	}
	?>
	</select>
	<button type="button" id="disp<?php echo $option['table_name']; ?>"> <?php echo $l->g(1349); ?></button>
</div>
<?php 
	}
	?>


	<div id="<?php echo $option['table_name']; ?>_csv_download"
		style="display: none">
	<?php
	//Display of the result count 
	if (!isset($option['no_download_result'])){
		echo "<div id='".$option['table_name']."_csv_page'><label id='infopage_".$option['table_name']."'></label> ".$l->g(90)."<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_csv']."&no_header=1&tablename=".$option['table_name']."&base=".$tab_options['BASE']."'><small> (".$l->g(183).")</small></a></div>";
		echo "<div id='".$option['table_name']."_csv_total'><label id='infototal_".$option['table_name']."'></label> ".$l->g(90)."<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_csv']."&no_header=1&tablename=".$option['table_name']."&nolimit=true&base=".$tab_options['BASE']."'><small> (".$l->g(183).")</small></a></div>";
	}
	?>
	</div>
	<?php 
	echo "<a href='#' id='reset".$option['table_name']."' onclick='delete_cookie(\"".$option['table_name']."_col\");window.location.reload();' style='display: none;' >".$l->g(1380)."</a>";
	?>
	</div>
	<script>	
	//Check all the checkbox
	function checkall()
	{
	var table_id ="table#<?php echo $option['table_name']; ?>";
	$(table_id+" tbody tr td input:checkbox").each(function(){
			value = !$(this).attr('checked');
			document.getElementById($(this).attr('id')).checked = value ;
	 });
	}	
	$(document).ready(function() {
		var table_name = "<?php echo $option['table_name']; ?>";
		var table_id ="table#<?php echo $option['table_name']; ?>";
		var form_name = "form#<?php echo $option['form_name']; ?>";
		var csrfid = "input#CSRF_<?php echo $_SESSION['OCS']['CSRFNUMBER']; ?>";
		/* 
		Table Skeleton Creation. 
		A Full documentation about DataTable constructor can be found at 
		https://datatables.net/manual/index
		*/
		var table = $(table_id).dataTable({
	        "processing": true,
	        "serverSide": true,
	    	'dom':'<<"wrapper"lf><t><"row"<"col-xs-6"i><"col-xs-6"p>>>',
        	"ajax": {
           
            	 'url': '<?php echo $address; ?>&no_header=true&no_footer=true',
            	 "type": "POST",
            	 //Error handling
        		 "error": function (xhr, error, thrown) {
            		 var statusErrorMap = {
         	                '400' : "<?php echo $l->g(1352); ?>",
         	                '401' : "<?php echo $l->g(1353); ?>",
         	                '403' : "<?php echo $l->g(1354); ?>",
         	                '404' : "<?php echo $l->g(1355); ?>",
         	                '414' : "<?php echo $l->g(1356); ?>",
         	                '500' : "<?php echo $l->g(1357); ?>",
         	                '503' : "<?php echo $l->g(1358); ?>"
         	         };
            		 if(statusErrorMap[xhr.status]!=undefined){
            			 if(xhr.status == 401){
                			 window.location.reload();
						 }        	 
          	    	}},
          	    //Set the $_POST request to the ajax file. d contains all datatables needed info
            	 "data": function ( d ) {
            		 	if ($(table_id).width() < $(this).width()){
     						$(table_id).width('100%');
     						$(".dataTables_scrollHeadInner").width('100%');
     						$(".dataTables_scrollHeadInner>table").width('100%');
     					}
     					//Add CSRF
            	        d.CSRF_<?php echo $_SESSION['OCS']['CSRFNUMBER'];?> = $(csrfid).val();
            	        var visible =[];
            	        if (document.getElementById('checkboxALL')){
            	        	document.getElementById('checkboxALL').checked = false;
            	        }
            	        $.each(d.columns,function(index,value){
                	        var col = "."+this['data'];
            	       		if($(table_id).DataTable().column(col).visible()){
								visible.push(index);
            	        	}
            	 		});
						var ocs=[];
						//Add the actual $_POST to the $_POST of the ajax request 
						<?php 
						foreach ($protectedPost as $key => $value){
							if(!is_array($value)){
								echo "d['".$key."'] = '".$value."'; \n";
							}
						}
						?>
        	        	ocs.push($(form_name).serialize());
            	        d.visible = visible;
            	        d.ocs = ocs;
                	    },
               	"dataSrc": function ( json ) {
                	        if(json.customized){
                	        	$("#reset"+table_name).show();
                	        }else{
                	        	$("#reset"+table_name).hide();
                	        }
                	        if(json.debug){
                	        	$("<p>"+json.debug+"</p><hr>").hide().prependTo('#'+table_name+'_debug div').fadeIn(1000);
                	        	$(".datatable_request").show();
                	        }
                	        return json.data;
                	      },

                   		
            	},

           	//Column definition 
        	"columns": [
    	        	<?php 
    	        	$index = 0;
    	        	//Visibility handling 
    	        	foreach($columns as $key=>$column){
    	        		if (!empty($visible_col)){
    	        			if ((in_array($index,$visible_col))) {
    	        				$visible = 'true';
    	        			}
    	        			else{
    	        				$visible = 'false';
    	        			}
    	        			$index ++;
    	        		}
    	        		else{
    	        			if((		  (in_array($key,$default_fields))
									||(in_array($key,$list_col_cant_del))
									||array_key_exists($key,$default_fields)
    	        					||($key == "ACTIONS" ))
									&& !(in_array($key, $actions))
    	        			){
    	        				
    	        				$visible = 'true';
    	        			}
    	        			else{
    	        				$visible = 'false';	 
    	        			}		
    	        		}
    	        		//Can the column be ordered
    	        		if (in_array($key,$columns_special)||!empty($option['NO_TRI'][$key])||$key=="ACTIONS"){
    	        			$orderable = 'false';
    	        		}
    	        		else{
    	        			$orderable = 'true';
    	        		}
    	        		//Cannot search in Delete or checkbox columns 
    	        		if (!array_key_exists($key, $columns_unique) || in_array($key, $columns_special)){
							if (!empty($option['REPLACE_COLUMN_KEY'][$key])){
								$key = $option['REPLACE_COLUMN_KEY'][$key];
							}
    	        			echo  "{'data' : '".$key."' , 'class':'".$key."',
								 'name':'".$key."', 'defaultContent': ' ',
								 'orderable':  ".$orderable.",'searchable': false,
			 					 'visible' : ".$visible."}, \n" ;
    	        		}	
    	        		else{		
    	        			$name = explode('.',$column);
    	        			$name = explode(' as ',end($name));
    	        			$name = end($name);
    	        			if (!empty($option['REPLACE_COLUMN_KEY'][$key])){
									$name = $option['REPLACE_COLUMN_KEY'][$key];
							}
    	        			echo  "{ 'data' : '".$name."' , 'class':'".$name."', 
								 'name':'".$column."', 'defaultContent': ' ',
								 'orderable':  ".$orderable.", 'visible' : ".$visible."},\n " ;
    	        		}
    	        		
   	        		}

    	        	?>
    	    ],
    	    //Translation
    	    "language": {
    	       		"sEmptyTable":     "<?php echo $l->g(1334); ?>",
    	       		"sInfo":           "<?php echo $l->g(1335); ?>",
    	        	"sInfoEmpty":      "<?php echo $l->g(1336); ?>",
    	        	"sInfoFiltered":   "<?php echo $l->g(1337); ?>",
    	        	"sInfoPostFix":    "",
    	        	"sInfoThousands":  "<?php echo $l->g(1350); ?>",
    	        	"decimal": 		   "<?php echo $l->g(1351); ?>",
    	        	"sLengthMenu":     "<?php echo $l->g(1338); ?>",
    	        	"sLoadingRecords": "<?php echo $l->g(1339); ?>",
    	        	"sProcessing":     "<?php echo $l->g(1340); ?>",
    	        	"sSearch":         "<?php echo $l->g(1341); ?>",
    	        	"sZeroRecords":    "<?php echo $l->g(1342); ?>",
    	        	"oPaginate": {
    	        		"sFirst":      "<?php echo $l->g(1343); ?>",
    	        		"sLast":       "<?php echo $l->g(1344); ?>",
    	        		"sNext":       "<?php echo $l->g(1345); ?>",
    	        		"sPrevious":   "<?php echo $l->g(1346); ?>",
    	        	},
    	        	"oAria": {
    	        		"sSortAscending":  ": <?php echo $l->g(1347); ?>",
    	        		"sSortDescending": ": <?php echo $l->g(1348); ?>",
    	        	}
			},
			"scrollX":'auto',
       	});

       	//Column Show/Hide
		$("body").on("click","#disp"+table_name,function(){
			var col = "."+$("#select_col"+table_name).val();
			$(table_id).DataTable().column(col).visible(!($(table_id).DataTable().column(col).visible()));
			$(table_id).DataTable().ajax.reload();
		});
		<?php
		//Csv Export 
		if (!isset($option['no_download_result'])){
		?>
			$(table_id).on( 'draw.dt', function () {
				var start = $(table_id).DataTable().page.info().start +1 ;
				var end = $(table_id).DataTable().page.info().end;
				var total = $(table_id).DataTable().page.info().recordsDisplay;
				//Show one line only if results fit in one page
				if (total == 0){
					$('#'+table_name+'_csv_download').hide();
				}
				else{
				if (end != total || start != 1){
					$('#'+table_name+'_csv_page').show();
					$('#infopage_'+table_name).text(start+"-"+end);
				}
				else{
					$('#'+table_name+'_csv_page').hide();
				}
				$('#infototal_'+table_name).text(total);
				$('#'+table_name+'_csv_download').show();
				}
			});
		<?php 
		}
		?>
	});
	
	</script>
	<?php
	if ($titre != "")
		printEnTete_tab($titre);
	echo "<br><div class='tableContainer'><table id='".$option['table_name']."' class='table table-striped table-bordered table-condensed table-hover'><thead><tr>";
		//titre du tableau
	foreach($columns as $k=>$v)
	{		
		if(array_key_exists($k,$lbl_column)){
			echo "<th><font >".$lbl_column[$k]."</font></th>";
		}
		else{
			echo "<th><font >".$k."</font></th>";	
		}	
	}

	echo "</tr>
    </thead>";
			
    echo "</table></div></div>";
	echo "<input type='hidden' id='SUP_PROF' name='SUP_PROF' value=''>";
	echo "<input type='hidden' id='MODIF' name='MODIF' value=''>";
	echo "<input type='hidden' id='SELECT' name='SELECT' value=''>";
	echo "<input type='hidden' id='OTHER' name='OTHER' value=''>";
	echo "<input type='hidden' id='ACTIVE' name='ACTIVE' value=''>";
	echo "<input type='hidden' id='CONFIRM_CHECK' name='CONFIRM_CHECK' value=''>";
	echo "<input type='hidden' id='OTHER_BIS' name='OTHER_BIS' value=''>";
	echo "<input type='hidden' id='OTHER_TER' name='OTHER_TER' value=''>";
	
	
	if ($_SESSION['OCS']['DEBUG'] == 'ON'){
		?><center>
		<div id="<?php echo $option['table_name']; ?>_debug" class="alert alert-info" role="alert">
		<b>[DEBUG]TABLE REQUEST[DEBUG]</b>
		<hr>
		<b class="datatable_request" style="display:none;">LAST REQUEST:</b>
		<div></div>
		</div>
		</center><?php
	}
	return true;
}




function tab_entete_fixe($entete_colonne,$data,$titre,$width,$height,$lien=array(),$option=array())
{
	echo "<div align=center>";
	global $protectedGet,$l;
	if ($protectedGet['sens'] == "ASC"){
		$sens="DESC";
		
	}
	else
	{
		$sens="ASC";
	}

	if(isset($data))
	{
		?>
	<script>		
	function changerCouleur(obj, state) {
			if (state == true) {
				bcolor = obj.style.backgroundColor;
				fcolor = obj.style.color;
				obj.style.backgroundColor = '#FFDAB9';
				obj.style.color = 'red';
				return true;
			} else {
				obj.style.backgroundColor = bcolor;
				obj.style.color = fcolor;
				return true;
			}
			return false;
		}
	</script>
	<?php
	if ($titre != "")
	printEnTete_tab($titre);
	echo "<div class='tableContainer' id='data' style=\"width:".$width."%;\"><table cellspacing='0' class='ta'><tr>";
		//titre du tableau
	$i=1;
	foreach($entete_colonne as $k=>$v)
	{
		if (in_array($v,$lien))
			echo "<th class='ta' >".$v."</th>";
		else
			echo "<th class='ta'><font size=1 align=center>".$v."</font></th>";	
		$i++;		
	}
	echo "
    </tr>
    <tbody class='ta'>";
	
//	$i=0;
	$j=0;
	//lignes du tableau
//	while (isset($data[$i]))
	//{
	foreach ($data as $k2=>$v2){
			($j % 2 == 0 ? $color = "#f2f2f2" : $color = "#ffffff");
			echo "<tr class='ta' bgcolor='".$color."'  onMouseOver='changerCouleur(this, true);' onMouseOut='changerCouleur(this, false);'>";
			foreach ($v2 as $k=>$v)
			{
				if (isset($option['B'][$i])){
					$begin="<b>";
					$end="</b>";				
				}else{
					$begin="";
					$end="";	
				}
				
				
				if ($v == "") $v="&nbsp";
				echo "<td class='ta' >".$begin.$v.$end."</td>";
				
			}
			$j++;
			echo "</tr><tr>";
			//$i++;
	
	}
	echo "</tr></tbody></table></div></div>";	
	}
	else{
		msg_warning($l->g(766));
		return FALSE;
	}
	return TRUE;
}














































//variable pour la fonction champsform
$num_lig=0;
/* fonction li�e � show_modif
 * qui permet de cr�er une ligne dans le tableau de modification/ajout
 * $title = titre � l'affichage du champ
 * $value_default = - pour un champ text ou input, la valeur par d�faut du champ.
 * 					- pour un champ select, liste des valeurs du champ
 * $input_name = nom du champ que l'on va r�cup�rer en $protectedPost
 * $input_type = 0 : <input type='text'>
 * 				 1 : <textarea>
 * 				 2 : <select><option>
 * $donnees = tableau qui contient tous les champs � afficher � la suite
 * $nom_form = si un select doit effectuer un reload, on y met le nom du formulaire � reload
*/
function champsform($title,$value_default,$input_name,$input_type,&$donnees,$nom_form=''){
	global $num_lig;
	$donnees['tab_name'][$num_lig]=$title;	
	$donnees['tab_typ_champ'][$num_lig]['DEFAULT_VALUE']=$value_default;
	$donnees['tab_typ_champ'][$num_lig]['INPUT_NAME']=$input_name;
	$donnees['tab_typ_champ'][$num_lig]['INPUT_TYPE']=$input_type;
	if ($nom_form != "")
	$donnees['tab_typ_champ'][$num_lig]['RELOAD']=$nom_form;
	$num_lig++;
	return $donnees;
	
}

/*
 * fonction li�e � tab_modif_values qui permet d'afficher le champ d�fini avec la fonction champsform
 * $name = nom du champ
 * $input_name = nom du champ r�cup�r� dans le $protectedPost
 * $input_type = 0 : <input type='text'>
 * 				 1 : <textarea>
 * 				 2 : <select><option>
 * $input_reload = si un select doit effectuer un reload, on y met le nom du formulaire � reload
 * 
 */
function show_modif($name,$input_name,$input_type,$input_reload = "",$configinput=array('MAXLENGTH'=>100,'SIZE'=>20,'JAVASCRIPT'=>"",'DEFAULT'=>"YES",'COLS'=>30,'ROWS'=>5))
{
	global $protectedPost,$l,$pages_refs;
	
	if ($configinput == "")
		$configinput=array('MAXLENGTH'=>100,'SIZE'=>20,'JAVASCRIPT'=>"",'DEFAULT'=>"YES",'COLS'=>30,'ROWS'=>5);
	//del stripslashes if $name is not an array
	if (!is_array($name)){
		$name=htmlspecialchars($name, ENT_QUOTES);
	}
		if ($input_type == 1){
			
		return "<textarea name='".$input_name."' id='".$input_name."' cols='".$configinput['COLS']."' rows='".$configinput['ROWS']."'  class='down' >".$name."</textarea>";
	
	}elseif ($input_type ==0)
	return "<input type='text' name='".$input_name."' id='".$input_name."' SIZE='".$configinput['SIZE']."' MAXLENGTH='".$configinput['MAXLENGTH']."' value=\"".$name."\" class='down'\" ".$configinput['JAVASCRIPT'].">";
	elseif($input_type ==2){
		$champs="<select name='".$input_name."' id='".$input_name."' ".(isset($configinput['JAVASCRIPT'])?$configinput['JAVASCRIPT']:'');
		if ($input_reload != "") $champs.=" onChange='document.".$input_reload.".submit();'";
		$champs.=" class='down' >";
		if (isset($configinput['DEFAULT']) and $configinput['DEFAULT'] == "YES")
		$champs.= "<option value='' class='hi' ></option>";
		$countHl=0;		
		if ($name != ''){
			natcasesort($name);
			foreach ($name as $key=>$value){
				$champs.= "<option value=\"".$key."\"";
				if ($protectedPost[$input_name] == $key )
					$champs.= " selected";
				$champs.= ($countHl%2==1?" class='hi'":" class='down'")." >".$value."</option>";
				$countHl++;
			}
		}
		$champs.="</select>";
		return $champs;
	}elseif($input_type == 3){
		$hid="<input type='hidden' id='".$input_name."' name='".$input_name."' value='".$name."'>";
	//	echo $name."<br>";
		return $name.$hid;
	}elseif ($input_type == 4)
	 return "<input size='".$configinput['SIZE']."' type='password' name='".$input_name."' class='hi' />";
	elseif ($input_type == 5 and isset($name) and is_array($name)){	
		foreach ($name as $key=>$value){
			$champs.= "<input type='checkbox' name='".$input_name."_".$key."' id='".$input_name."_".$key."' ";
			if ($protectedPost[$input_name."_".$key] == 'on' )
			$champs.= " checked ";
			if ($input_reload != "") $champs.=" onChange='document.".$input_reload.".submit();'";
			$champs.= " >" . $value . " <br>";
		}
		return $champs;
	}elseif($input_type == 6){
		if (isset($configinput['NB_FIELD']))
			$i=$configinput['NB_FIELD'];
		else
			$i=6;
		$j=0;
		echo $name;
		while ($j<$i){
			$champs.="<input type='text' name='".$input_name."_".$j."' id='".$input_name."_".$j."' SIZE='".$configinput['SIZE']."' MAXLENGTH='".$configinput['MAXLENGTH']."' value=\"".$protectedPost[$input_name."_".$j]."\" class='down'\" ".$configinput['JAVASCRIPT'].">";
			$j++;
		}
		return $champs;		
	}elseif($input_type == 7)
		return "<input type='hidden' id='".$input_name."' name='".$input_name."' value='".$name."'>";
	elseif ($input_type == 8){
		return "<input type='button' id='".$input_name."' name='".$input_name."' value='".$l->g(1048)."' OnClick='window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_upload_file_popup']."&head=1&n=".$input_name."&tab=".$name."&dde=".$configinput['DDE']."\",\"active\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=350\")'>";
	}elseif ($input_type == 9){
		$aff="";
		if (is_array($name)){
			foreach ($name as $key=>$value){
				$aff.="<a href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_view_file']."&prov=dde_wk&no_header=1&value=".$key."\">".
						$value."</a><br>";
			}
		}
		return $aff;
	}elseif ($input_type == 10){
		//le format de de $name doit etre sous la forme d'une requete sql avec éventuellement
		//des arguments. Dans ce cas, les arguments sont séparés de la requête par $$$$
		//et les arguments entre eux par des virgules
		//echo $name;
		$sql=explode('$$$$',$name);
		if (isset($sql[1])){
			$arg_sql=explode(',',$sql[1]);	
			$i=0;
			while ($arg_sql[$i]){
				$arg[$i]=$protectedPost[$arg_sql[$i]];
				$i++;	
			}
		}		
		if (isset($arg_sql))
		$result = mysql2_query_secure($sql[0], $_SESSION['OCS']["readServer"],$arg);
		else
		$result = mysql2_query_secure($sql[0], $_SESSION['OCS']["readServer"]);
		if (isset($result) and $result != ''){
			$i=0;
			while($colname = mysqli_fetch_field($result))
			$entete2[$i++]=$colname->name;
			
			$i=0;		
			while ($item = mysqli_fetch_object($result)){
				$j=0;
				while ($entete2[$j]){
					$data2[$i][$entete2[$j]]=$item ->$entete2[$j];
					$j++;
				}
				$i++;
			}
		}
		return tab_entete_fixe($entete2,$data2,"",60,300);		
	}elseif($input_type == 11 and isset($name) and is_array($name)){	
		foreach ($name as $key=>$value){
			$champs.= "<input type='radio' name='".$input_name."' id='".$input_name."' value='" . $key . "'";
			if ($protectedPost[$input_name] == $key ){
				$champs.= " checked ";
			}
			$champs.= " >" . $value . " <br>";
		}
		return $champs;		
	}elseif($input_type == 12){ //IMG type
		$champs="<img src='".$configinput['DEFAULT']."' ";
		if ($configinput['SIZE'] != '20')
			$champs.=$configinput['SIZE']." ";
	
		if ($configinput['JAVASCRIPT'] != '')
			$champs.=$configinput['JAVASCRIPT']." ";
		$champs.=">";
		return $champs;
		//"<img src='index.php?".PAG_INDEX."=".$pages_refs['ms_qrcode']."&no_header=1&systemid=".$protectedGet['systemid']."' width=60 height=60 onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_qrcode']."&no_header=1&systemid=".$protectedGet['systemid']."\")>";
		
	}elseif($input_type == 13){
		
		return "<input id='".$input_name."' name='".$input_name."' type='file' accept='archive/zip'>";
	
	}
}

function tab_modif_values($field_labels, $fields, $hidden_fields, $options = array()) {
	global $l;
	
	$options = array_merge(array(
		'title' => null,
		'comment' => null,
		'button_name' => 'modif',
		'show_button' => true,
		'form_name' => 'CHANGE',
		'top_action' => null,
		'show_frame' => true
	), $options);

	if ($options['form_name'] != 'NO_FORM') {
		echo open_form($options['form_name']);
	}
	
	if ($options['show_frame']) {
		echo '<div class="form-frame form-frame-'.$options['form_name'].'">';
	}
	
	if ($options['top_action']) {
		echo "<table align='right' border='0'><tr><td colspan=10 align='right'>".$options['top_action']."</td></tr></table>";
	}

	if ($options['title']) {
		echo '<h3>'.$options['title'].'</h3>';
	}
	
	if (is_array($field_labels)) {
	    foreach ($field_labels as $key => $label) {
	    	$field = $fields[$key];
	    	
	    	echo '<div class="field field-'.$field['INPUT_NAME'].'">';
	    	echo '<label>'.$label.'</label>';
	    	
	    	if ($field['COMMENT_BEFORE']) {
				echo '<span class="comment_before">'.$field['COMMENT_BEFORE'].'</span>';
	    	}
	    	
			echo show_modif($field['DEFAULT_VALUE'], $field['INPUT_NAME'], $field['INPUT_TYPE'], $field['RELOAD'], $field['CONFIG']);
	    	
	    	if ($field['COMMENT_AFTER']) {
				echo '<span class="comment_after">'.$field['COMMENT_AFTER'].'</span>';
	    	}
	    	
	    	echo '</div>';
		}
	} else {
		echo $field_labels;
	}
	
	if ($options['comment']) {
	 	echo '<div class="form-field"><i>'.$options['comment'].'</i></div>';
	}
	
	if ($options['show_button'] === 'BUTTON') {
		echo '<div class="form-buttons">';
		echo '<input type="submit" name="Valid_'.$options['button_name'].'" value="'.$l->g(13).'"/>';
		echo '</div>';
	} else if ($options['show_button']) {
		echo '<div class="form-buttons">';
		echo '<input type="submit" name="Valid_'.$options['button_name'].'" value="'.$l->g(1363).'"/>';
		echo '<input type="submit" name="Reset_'.$options['button_name'].'" value="'.$l->g(1364).'"/>';
		echo '</div>';
 	}

 	if ($options['show_frame']) {
	    echo "</div>";
 	}
    
    if ($hidden_fields) {
		foreach ($hidden_fields as $key => $value) {
			echo "<input type='hidden' name='".$key."' id='".$key."' value='".htmlspecialchars($value, ENT_QUOTES)."'>";
		}
    }
    
    if ($options['form_name'] != 'NO_FORM') {
		echo close_form();
    }
}

function show_field($name_field,$type_field,$value_field,$config=array()){
	global $protectedPost;
	foreach($name_field as $key=>$value){
		$tab_typ_champ[$key]['DEFAULT_VALUE']=$value_field[$key];
		$tab_typ_champ[$key]['INPUT_NAME']=$name_field[$key];
		$tab_typ_champ[$key]['INPUT_TYPE']=$type_field[$key];
		
		
		if (!isset($config['ROWS'][$key]) or $config['ROWS'][$key] == '')
			$tab_typ_champ[$key]['CONFIG']['ROWS']=7;
		else
			$tab_typ_champ[$key]['CONFIG']['ROWS']=$config['ROWS'][$key];
			
		if (!isset($config['COLS'][$key]) or $config['COLS'][$key] == '')
			$tab_typ_champ[$key]['CONFIG']['COLS']=40;
		else
			$tab_typ_champ[$key]['CONFIG']['COLS']=$config['COLS'][$key];		
		
		if (!isset($config['SIZE'][$key]) or $config['SIZE'][$key] == '')
			$tab_typ_champ[$key]['CONFIG']['SIZE']=50;
		else
			$tab_typ_champ[$key]['CONFIG']['SIZE']=$config['SIZE'][$key];
		
		if (!isset($config['MAXLENGTH'][$key]) or $config['MAXLENGTH'][$key] == '')
			$tab_typ_champ[$key]['CONFIG']['MAXLENGTH']=255;
		else
			$tab_typ_champ[$key]['CONFIG']['MAXLENGTH']=$config['MAXLENGTH'][$key];
			
		if (isset($config['COMMENT_AFTER'][$key]))	{
			$tab_typ_champ[$key]['COMMENT_AFTER']=	$config['COMMENT_AFTER'][$key];
		}		
		
			
		if (isset($config['DDE'][$key]))	{
			$tab_typ_champ[$key]['CONFIG']['DDE']=$config['DDE'][$key];
		}	
		
		if (isset($config['SELECT_DEFAULT'][$key]))	{
			$tab_typ_champ[$key]['CONFIG']['DEFAULT']=$config['SELECT_DEFAULT'][$key];
		}
		if (isset($config['JAVASCRIPT'][$key]))	{
			$tab_typ_champ[$key]['CONFIG']['JAVASCRIPT']=$config['JAVASCRIPT'][$key];
		}
	}
//	$i=0;
//	while ($name_field[$i]){
//		$tab_typ_champ[$i]['DEFAULT_VALUE']=$value_field[$i];
//		$tab_typ_champ[$i]['INPUT_NAME']=$name_field[$i];
//		$tab_typ_champ[$i]['INPUT_TYPE']=$type_field[$i];
//		$tab_typ_champ[$i]['CONFIG']['ROWS']=7;
//		$tab_typ_champ[$i]['CONFIG']['COLS']=40;
//		$tab_typ_champ[$i]['CONFIG']['SIZE']=50;
//		$tab_typ_champ[$i]['CONFIG']['MAXLENGTH']=255;
//		$i++;
//	}
	return $tab_typ_champ;
}

function filtre($tab_field,$form_name,$query,$arg='',$arg_count=''){
	global $protectedPost,$l;
// 	if ($protectedPost['RAZ_FILTRE'] == "RAZ")
// 	unset($protectedPost['FILTRE_VALUE'],$protectedPost['FILTRE']);
	if ($protectedPost['FILTRE_VALUE'] and $protectedPost['FILTRE']){
		$temp_query=explode("GROUP BY",$query);
		if ($temp_query[0] == $query)
		$temp_query=explode("group by",$query);
		
		if (substr_count(mb_strtoupper ($temp_query[0]), "WHERE")>0){
			$t_query=explode("WHERE",$temp_query[0]);
			if ($t_query[0] == $temp_query[0])
			$t_query=explode("where",$temp_query[0]);
			$temp_query[0]= $t_query[0]." WHERE (".$t_query[1].") and ";
		
		}else
		$temp_query[0].= " where ";
	if (substr($protectedPost['FILTRE'],0,2) == 'a.'){
		require_once('require/function_admininfo.php');
		$id_tag=explode('_',substr($protectedPost['FILTRE'],2));
		if (!isset($id_tag[1]))
			$tag=1;
		else
			$tag=$id_tag[1];
		$list_tag_id= find_value_in_field($tag,$protectedPost['FILTRE_VALUE']);
	}
	if ($list_tag_id){
		$query_end= " in (".implode(',',$list_tag_id).")";		
	}else{	
		if ($arg == '')
			$query_end = " like '%".$protectedPost['FILTRE_VALUE']."%' ";
		else{
			$query_end = " like '%s' ";
			array_push($arg,'%' . $protectedPost['FILTRE_VALUE'] . '%');
			if (is_array($arg_count))	
				array_push($arg_count,'%' . $protectedPost['FILTRE_VALUE'] . '%');
			else
				$arg_count[] = '%' . $protectedPost['FILTRE_VALUE'] . '%';
		}
	}
	$query= $temp_query[0].$protectedPost['FILTRE'].$query_end;
	if (isset($temp_query[1]))
		$query.="GROUP BY ".$temp_query[1];
	}
	$view=show_modif($tab_field,'FILTRE',2);
	$view.=show_modif($protectedPost['FILTRE_VALUE'],'FILTRE_VALUE',0);
	
	echo $l->g(883).": ".$view."<input type='submit' value='".$l->g(1109)."' name='SUB_FILTRE'><a href=# onclick='return pag(\"RAZ\",\"RAZ_FILTRE\",\"".$form_name."\");'><img src=image/delete-small.png></a></td></tr><tr><td align=center>";
	echo "<input type=hidden name='RAZ_FILTRE' id='RAZ_FILTRE' value=''>";
	return array('SQL'=>$query,'ARG'=>$arg,'ARG_COUNT'=>$arg_count);
}





function tab_list_error($data,$title)
{
	global $l;

	echo "<br>";
		echo "<table align='center' width='50%' border='0'  bgcolor='#C7D9F5' style='border: solid thin; border-color:#A1B1F9'>";
		echo "<tr><td colspan=20 align='center'><font color='RED'>".$title."</font></td></tr><tr>";	
		$i=0;
		$j=0;
		while ($data[$i])
		{
			if ($j == 10)
			{
				echo "</tr><tr>";
				$j=0;	
			}
			echo "<td align='center'>".$data[$i]."<td>";
			$i++;
			$j++;
		}
		echo "</td></tr></table>";
	
}

function nb_page($form_name = '',$taille_cadre='80',$bgcolor='#C7D9F5',$bordercolor='#9894B5',$table_name=''){
	global $protectedPost,$l;

	//catch nb result by page
	if (isset($_SESSION['OCS']['nb_tab'][$table_name]))
		$protectedPost["pcparpage"]=$_SESSION['OCS']['nb_tab'][$table_name];
	elseif(isset($_COOKIE[$table_name.'_nbpage']))
		$protectedPost["pcparpage"]=$_COOKIE[$table_name.'_nbpage'];	
	

	if ($protectedPost['old_pcparpage'] != $protectedPost['pcparpage'])
		$protectedPost['page']=0;
		
	if (!(isset($protectedPost["pcparpage"])) or $protectedPost["pcparpage"] == ""){
		$protectedPost["pcparpage"]=PC4PAGE;
		
	}
	$html_show = "<table align=center width='80%' border='0' bgcolor=#f2f2f2>";
	//gestion d"une phrase d'alerte quand on utilise le filtre
	if (isset($protectedPost['FILTRE_VALUE']) and $protectedPost['FILTRE_VALUE'] != '' and $protectedPost['RAZ_FILTRE'] != 'RAZ')
		$html_show .= msg_warning($l->g(884));
	$html_show .= "<tr><td align=right>";
	
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = "SHOW";
	if ($protectedPost['SHOW'] == 'SHOW')
		$html_show .= "<a href=# OnClick='pag(\"NOSHOW\",\"SHOW\",\"".$form_name."\");'><img src=image/no_show.png></a>";
	elseif ($protectedPost['SHOW'] != 'NEVER_SHOW')
		$html_show .= "<a href=# OnClick='pag(\"SHOW\",\"SHOW\",\"".$form_name."\");'><img src=image/show.png></a>";
		
	$html_show .= "</td></tr></table>";
	$html_show .= "<table align=center width='80%' border='0' bgcolor=#f2f2f2";
	
	if($protectedPost['SHOW'] == 'NOSHOW' or $protectedPost['SHOW'] == 'NEVER_SHOW')
		$html_show .= " style='display:none;'";
		
	$html_show .= "><tr><td align=center>";
	$html_show .= "<table cellspacing='5' width='".$taille_cadre."%' BORDER='0' ALIGN = 'Center' CELLPADDING='0' BGCOLOR='".$bgcolor."' BORDERCOLOR='".$bordercolor."'><tr><td align=center>";
	$machNmb = array(5=>5,10=>10,15=>15,20=>20,50=>50,100=>100,200=>200,1000000=>$l->g(215));
    $pcParPageHtml= $l->g(340).": ".show_modif($machNmb,'pcparpage',2,$form_name,array('DEFAULT'=>'NO'));
	$pcParPageHtml .=  "</td></tr></table>
	</td></tr><tr><td align=center>";
	$html_show .= $pcParPageHtml;


	if (isset($protectedPost["pcparpage"])){
		$deb_limit=$protectedPost['page']*$protectedPost["pcparpage"];
		$fin_limit=$deb_limit+$protectedPost["pcparpage"]-1;		
	}

	$html_show .= "<input type='hidden' id='SHOW' name='SHOW' value='".$protectedPost['SHOW']."'>";
	if ($form_name != '')
	echo $html_show;
	
	return (array("BEGIN"=>$deb_limit,"END"=>$fin_limit));
}

function show_page($valCount,$form_name){
	global $protectedPost;
	if (isset($protectedPost["pcparpage"]) and $protectedPost["pcparpage"] != 0)
	$nbpage= ceil($valCount/$protectedPost["pcparpage"]);
	if ($nbpage >1){
	$up=$protectedPost['page']+1;
	$down=$protectedPost['page']-1;
	echo "<table align='center' width='99%' border='0' bgcolor=#f2f2f2>";
	echo "<tr><td align=center>";
	if ($protectedPost['page'] > 0)
	echo "<img src='image/prec24.png' OnClick='pag(\"".$down."\",\"page\",\"".$form_name."\")'> ";
	//if ($nbpage<10){
		$i=0;
		$deja="";
		while ($i<$nbpage){			
			$point="";
			if ($protectedPost['page'] == $i){
				if ($i<$nbpage-10 and  $i>10  and $deja==""){
				$point=" ... ";
				$deja="ok";	
				}
				if($i<$nbpage-10 and  $i>10){
					$point2=" ... ";
				}
				echo $point."<font color=red>".$i."</font> ".$point2;
			}
			elseif($i>$nbpage-10 or $i<10)
			echo "<a OnClick='pag(\"".$i."\",\"page\",\"".$form_name."\")'>".$i."</a> ";
			elseif ($i<$nbpage-10 and  $i>10 and $deja==""){
				echo " ... ";
				$deja="ok";	
			}
			$i++;
		}

	if ($protectedPost['page']< $nbpage-1)
	echo "<img src='image/proch24.png' OnClick='pag(\"".$up."\",\"page\",\"".$form_name."\")'> ";
	
	}
	echo "</td></tr></table>";
	echo "<input type='hidden' id='page' name='page' value='".$protectedPost['page']."'>";
	echo "<input type='hidden' id='old_pcparpage' name='old_pcparpage' value='".$protectedPost['pcparpage']."'>";
}


function onglet($def_onglets,$form_name,$post_name,$ligne)
{
	global $protectedPost;
/*	$protectedPost['onglet_soft']=stripslashes($protectedPost['onglet_soft']);
	$protectedPost['old_onglet_soft']=stripslashes($protectedPost['old_onglet_soft']);*/
	if ($protectedPost["old_".$post_name] != $protectedPost[$post_name]){
	$protectedPost['page']=0;
	}
	if (!isset($protectedPost[$post_name]) and is_array($def_onglets)){
		foreach ($def_onglets as $key=>$value){
			$protectedPost[$post_name]=$key;
			break;
		}		
	}
	/*This fnction use code of Douglas Bowman (Sliding Doors of CSS)
	http://www.alistapart.com/articles/slidingdoors/
	THANKS!!!!
		$def_onglets is array like :  	$def_onglets[$l->g(499)]=$l->g(499); //Serveur
										$def_onglets[$l->g(728)]=$l->g(728); //Inventaire
										$def_onglets[$l->g(312)]=$l->g(312); //IP Discover
										$def_onglets[$l->g(512)]=$l->g(512); //T�l�d�ploiement
										$def_onglets[$l->g(628)]=$l->g(628); //Serveur de redistribution 
		
	behing this function put this lign:
	echo open_form($form_name);
	
	At the end of your page, close this form
	$post_name is the name of var will be post
	$ligne is if u want have onglet on more ligne*/
	if ($def_onglets != ""){
	echo "<LINK REL='StyleSheet' TYPE='text/css' HREF='css/onglets.css'>\n";
	echo "<table cellspacing='0' BORDER='0' ALIGN = 'Center' CELLPADDING='0'><tr><td><div id='header'>";
	echo "<ul>";
	$current="";
	$i=0;
	  foreach($def_onglets as $key=>$value){
	  	
	  	if ($i == $ligne){
	  		echo "</ul><ul>";
	  		$i=0;
	  		
	  	}
	  	echo "<li ";
	  	if (is_numeric($protectedPost[$post_name])){
			if ($protectedPost[$post_name] == $key or (!isset($protectedPost[$post_name]) and $current != 1)){
			 echo "id='current'";  
	 		 $current=1;
			}
	  	}else{
			if (mysqli_real_escape_string($_SESSION['OCS']["readServer"],stripslashes($protectedPost[$post_name])) === mysqli_real_escape_string($_SESSION['OCS']["readServer"],stripslashes($key)) or (!isset($protectedPost[$post_name]) and $current != 1)){
				 echo "id='current'";  
	 			 $current=1;
			}
		}
	
	  	echo "><a OnClick='pag(\"".htmlspecialchars($key, ENT_QUOTES)."\",\"".$post_name."\",\"".$form_name."\")'>".htmlspecialchars($value, ENT_QUOTES)."</a></li>";
	  $i++;	
	  }	
	echo "</ul>
	</div></td></tr></table>";
	echo "<input type='hidden' id='".$post_name."' name='".$post_name."' value='".$protectedPost[$post_name]."'>";
	echo "<input type='hidden' id='old_".$post_name."' name='old_".$post_name."' value='".$protectedPost[$post_name]."'>";
	}
	
}


function show_tabs($def_onglets,$form_name,$post_name,$ligne)
{
	global $protectedPost;
/*	$protectedPost['onglet_soft']=stripslashes($protectedPost['onglet_soft']);
	$protectedPost['old_onglet_soft']=stripslashes($protectedPost['old_onglet_soft']);*/
	if ($protectedPost["old_".$post_name] != $protectedPost[$post_name]){
	$protectedPost['page']=0;
	}
	if (!isset($protectedPost[$post_name]) and is_array($def_onglets)){
		foreach ($def_onglets as $key=>$value){
			$protectedPost[$post_name]=$key;
			break;
		}		
	}
	/*This fnction use code of Douglas Bowman (Sliding Doors of CSS)
	http://www.alistapart.com/articles/slidingdoors/
	THANKS!!!!
		$def_onglets is array like :  	$def_onglets[$l->g(499)]=$l->g(499); //Serveur
										$def_onglets[$l->g(728)]=$l->g(728); //Inventaire
										$def_onglets[$l->g(312)]=$l->g(312); //IP Discover
										$def_onglets[$l->g(512)]=$l->g(512); //T�l�d�ploiement
										$def_onglets[$l->g(628)]=$l->g(628); //Serveur de redistribution 
		
	behing this function put this lign:
	echo open_form($form_name);
	
	At the end of your page, close this form
	$post_name is the name of var will be post
	$ligne is if u want have onglet on more ligne*/
	if ($def_onglets != ""){
	echo "<LINK REL='StyleSheet' TYPE='text/css' HREF='css/onglets.css'>\n";
	echo "<div class='left-menu'><div class='navbar navbar-default'>";
	echo "<ul class='nav navbar-nav'>";
	$current="";
	$i=0;
	  foreach($def_onglets as $key=>$value){
	  	echo "<li ";
	  	if (is_numeric($protectedPost[$post_name])){
			if ($protectedPost[$post_name] == $key or (!isset($protectedPost[$post_name]) and $current != 1)){
			 echo "id='current'";  
	 		 $current=1;
			}
	  	}else{
			if (mysqli_real_escape_string($_SESSION['OCS']["readServer"],stripslashes($protectedPost[$post_name])) === mysqli_real_escape_string($_SESSION['OCS']["readServer"],stripslashes($key)) or (!isset($protectedPost[$post_name]) and $current != 1)){
				 echo "id='current'";  
	 			 $current=1;
			}
		}
	
	  	echo "><a OnClick='pag(\"".htmlspecialchars($key, ENT_QUOTES)."\",\"".$post_name."\",\"".$form_name."\")'>".htmlspecialchars($value, ENT_QUOTES)."</a></li>";
	  $i++;	
	  }	
	echo "</ul>
	</div></div>";
	echo "<input type='hidden' id='".$post_name."' name='".$post_name."' value='".$protectedPost[$post_name]."'>";
	echo "<input type='hidden' id='old_".$post_name."' name='old_".$post_name."' value='".$protectedPost[$post_name]."'>";
	}
	

}





function gestion_col($entete,$data,$list_col_cant_del,$form_name,$tab_name,$list_fields,$default_fields,$id_form='form'){
	global $protectedPost,$l;
	//search in cookies columns values
	if (isset($_COOKIE[$tab_name]) and $_COOKIE[$tab_name] != '' and !isset($_SESSION['OCS']['col_tab'][$tab_name])){
		$col_tab=explode("///", $_COOKIE[$tab_name]);
		foreach ($col_tab as $key=>$value){
				$_SESSION['OCS']['col_tab'][$tab_name][$value]=$value;
		}			
	}
	if (isset($protectedPost['SUP_COL']) and $protectedPost['SUP_COL'] != ""){
		unset($_SESSION['OCS']['col_tab'][$tab_name][$protectedPost['SUP_COL']]);
	}
	if ($protectedPost['restCol'.$tab_name]){
		$_SESSION['OCS']['col_tab'][$tab_name][$protectedPost['restCol'.$tab_name]]=$protectedPost['restCol'.$tab_name];
	}
	if ($protectedPost['RAZ'] != ""){
		unset($_SESSION['OCS']['col_tab'][$tab_name]);
		$_SESSION['OCS']['col_tab'][$tab_name]=$default_fields;
	}
	if (!isset($_SESSION['OCS']['col_tab'][$tab_name])){
		$_SESSION['OCS']['col_tab'][$tab_name]=$default_fields;
	}
	//add all fields we must have
	if (is_array($list_col_cant_del)){
		if (!is_array($_SESSION['OCS']['col_tab'][$tab_name]))
			$_SESSION['OCS']['col_tab'][$tab_name]=array();
		foreach ($list_col_cant_del as $key=>$value){
			if (!in_array($key,$_SESSION['OCS']['col_tab'][$tab_name])){
				$_SESSION['OCS']['col_tab'][$tab_name][$key]=$key;
			}
		}
	}
	
	if (is_array($entete)){
		if (!is_array($_SESSION['OCS']['col_tab'][$tab_name]))
			$_SESSION['OCS']['col_tab'][$tab_name]=array();
		foreach ($entete as $k=>$v){
			if (in_array($k,$_SESSION['OCS']['col_tab'][$tab_name])){
				$data_with_filter['entete'][$k]=$v;	
				if (!isset($list_col_cant_del[$k]))
				 $data_with_filter['entete'][$k].="<a href=# onclick='return pag(\"".xml_encode($k)."\",\"SUP_COL\",\"".$id_form."\");'><img src=image/delete-small.png></a>";
			}	
			else
			$list_rest[$k]=$v;
	
			
		}
	}
	if (is_array($data)){
		if (!is_array($_SESSION['OCS']['col_tab'][$tab_name]))
		$_SESSION['OCS']['col_tab'][$tab_name]=array();
		foreach ($data as $k=>$v){
			foreach ($v as $k2=>$v2){
				if (in_array($k2,$_SESSION['OCS']['col_tab'][$tab_name])){
					$data_with_filter['data'][$k][$k2]=$v2;
				}
			}
	
		}
	}
	if (is_array ($list_rest)){
		//$list_rest=lbl_column($list_rest);
		$select_restCol= $l->g(349).": ".show_modif($list_rest,'restCol'.$tab_name,2,$form_name);
		$select_restCol .=  "<a href=# OnClick='pag(\"".$tab_name."\",\"RAZ\",\"".$id_form."\");'><img src=image/delete-small.png></a></td></tr></table>"; //</td></tr><tr><td align=center>
		echo $select_restCol;
	}else
		echo "</td></tr></table>";
	echo "<input type='hidden' id='SUP_COL' name='SUP_COL' value=''>";
	echo "<input type='hidden' id='TABLE_NAME' name='TABLE_NAME' value='".$tab_name."'>";
	echo "<input type='hidden' id='RAZ' name='RAZ' value=''>";
	return( $data_with_filter);
	
	
}

function lbl_column($list_fields){
	//p($list_rest);
	require_once('maps.php');
	$return_fields=array();
	$return_default=array();
	foreach($list_fields as $poub=>$table){
		if (isset($lbl_column[$table])){
			foreach($lbl_column[$table] as $field=>$lbl){
				//echo $field;
				if (isset($alias_table[$table])){
					$return_fields[$lbl]=$alias_table[$table].'.'.$field;
					if (isset($default_column[$table])){
						foreach($default_column[$table] as $poub2=>$default_field)
							$return_default[$lbl_column[$table][$default_field]]=$lbl_column[$table][$default_field];
					}else{
						msg_error($table.' DEFAULT VALUES NOT DEFINE IN MAPS.PHP');
						return false;						
					}
				}else{
					msg_error($table.' ALIAS NOT DEFINE IN MAPS.PHP');
					return false;
				}
					
			}			
			
		}else{
			msg_error($table.' NOT DEFINE IN MAPS.PHP');
			return false;
		}
	}
	ksort($return_fields);
	return array('FIELDS'=>$return_fields,'DEFAULT_FIELDS'=>$return_default);
}



//fonction qui permet de ne selectionner que certaines lignes du tableau
/*
 * Columns : Each available column of the table
* $queryDetails = string 'SELECT QUERY'
* Tab_options : All the options for the specific table
* $tab_options= array{
* 						'form_name'=> "show_all",....
* 						'Option' => value,
* 						}
*/
function ajaxfiltre($queryDetails,$tab_options){
	// Research field of the table
	if ($tab_options["search"] && $tab_options["search"]['value']!=""){
		$search = mysqli_real_escape_string($_SESSION['OCS']["readServer"],$tab_options["search"]['value']);
		$search = str_replace('%','%%',$search);
		$sqlword['WHERE']= preg_split("/where/i", $queryDetails);
		$sqlword['GROUPBY']= preg_split("/group by/i", $queryDetails);
		$sqlword['HAVING']= preg_split("/having/i", $queryDetails);
		$sqlword['ORDERBY']= preg_split("/order by/i", $queryDetails);
		foreach ($sqlword as $word=>$filter){
			if (!empty($filter['1'])){
				foreach ($filter as  $key => $row){
					if ($key == 1){
						
						$rang =0;
						foreach($tab_options['visible'] as $index=>$column){
							$searchable =  ($tab_options['columns'][$column]['searchable'] == "true") ? true : false;
							$name = $tab_options['columns'][$column]['name'];
							if (!empty($tab_options["replace_query_arg"][$name])){
								$name= $tab_options["replace_query_arg"][$name];
							}
							if(is_array($tab_options['HAVING'])&&isset($tab_options['HAVING'][$name])){
								$searchable =false;
							}
							if (!empty($tab_options['NO_SEARCH'][$tab_options['columns'][$column]['name']])){
								$searchable = false;
							}
							if ($searchable){
								
								if ($rang == 0){
									$filtertxt =  " WHERE (( ".$name." LIKE '%%".$search."%%' ) ";
								}
								else{
									$filtertxt .= " OR  ( ".$name." LIKE '%%".$search."%%' ) ";
								}
								$rang++;
							}
						}
						if ($word == "WHERE"){
							$queryDetails .= $filtertxt.") AND ".$row;
						}
						else{
							$queryDetails .= $filtertxt.")  ".$row;
						}
					}
					else {
						if($key>1){
						 $queryDetails.=" ".$word." ".$row;
						}else{
							$queryDetails = $row;
						}
						
					}
				}
				return $queryDetails;
			}
		}
		//REQUET SELECT FROM
		$queryDetails .= " WHERE ";
		$index =0;
		foreach($tab_options['visible'] as $column){
			$searchable =  ($tab_options['columns'][$column]['searchable'] == "true") ? true : false;
			if(is_array($tab_options['HAVING'])&&isset($tab_options['HAVING'][$column])){
				$searchable =false;
			}
			
			if ($searchable){
				$name = $tab_options['columns'][$column]['name'];
				if (!empty($tab_options["replace_query_arg"][$name])){
					$name= $tab_options["replace_query_arg"][$name];
				}
				if ($index == 0){
					$filter =  "(( ".$name." LIKE '%%".$search."%%' ) ";
				}
				else{
					$filter .= " OR  ( ".$name." LIKE '%%".$search."%%' ) ";
				}
				$index++;
			}
		}
		$queryDetails .= $filter.") ";
	}
	return $queryDetails;
}
/*
 *  NOT USED YET
 *   
 * 	SUPPOSED TO ADD HAVING CLAUSE WHEN FILTERING TABLES RESULTS 
 */

// function ajaxfiltrehaving($queryDetails,$tab_options){
	
// 	if ($tab_options["search"] && $tab_options["search"]['value']!="" && is_numeric($tab_options["search"]['value']) ){
// 		if ( !empty($tab_options['HAVING'])){
// 			$search = mysqli_real_escape_string($_SESSION['OCS']["readServer"],$tab_options["search"]['value']);
// 			$sqlword['HAVING']= preg_split("/having/i", $queryDetails);
// 			$sqlword['ORDERBY']= preg_split("/order by/i", $queryDetails);
// 			foreach ($sqlword as $word=>$filter){
// 				if (!empty($filter['1'])){
// 					foreach ($filter as  $key => $row){
// 						if ($key == 1){
// 							foreach($tab_options['visible'] as $index=>$column){
// 								$name = $tab_options['columns'][$column]['name'];
// 								if (!empty($tab_options["replace_query_arg"][$name])){
// 									$name= $tab_options["replace_query_arg"][$name];
// 								}
// 								$searchable =  ($tab_options['columns'][$column]['searchable'] == "true") ? true : false;
								
// 								if(is_array($tab_options['HAVING'])&&isset($tab_options['HAVING'][$name])){
// 									$searchable =true;
// 								}else{
// 									$searchable=false;
// 								}
// 								if (!empty($tab_options['NO_SEARCH'][$tab_options['columns'][$column]['name']])){
// 									$searchable = false;
// 								}
// 								if ($searchable){
// 									$name = $tab_options['HAVING'][$name]['name'];
// 									if ($rang == 0){
// 										$filtertxt =  " HAVING (( ".$name." == '".$search."' ) ";
// 									}
// 									else{
// 										$filtertxt .= " OR  ( ".$name." == '".$search."' ) ";
// 									}
// 									$rang++;
// 								}
// 							}
// 							if ($word == "HAVING"){
// 								$queryDetails .= $filtertxt.") AND ".$row;
// 							}
// 							else{
// 								$queryDetails .= $filtertxt.")  ".$row;
// 							}
// 						}
// 						else {
// 							if($key>1){
// 								$queryDetails.=" ".$word." ".$row;
// 							}else{
// 								$queryDetails = $row;
// 							}
							
// 						}
// 					}
// 				return $queryDetails;
// 				}
// 			}
// 			$queryDetails .= " HAVING ";
// 			$index =0;
// 			foreach($tab_options['visible'] as $column){
// 				$name = $tab_options['columns'][$column]['name'];
// 				if (!empty($tab_options["replace_query_arg"][$name])){
// 					$name= $tab_options["replace_query_arg"][$name];
// 				}
// 				$searchable =  ($tab_options['columns'][$column]['searchable'] == "true") ? true : false;
				
// 				if(is_array($tab_options['HAVING'])&&isset($tab_options['HAVING'][$name])){
// 					$searchable =true;
// 				}else{
// 					$searchable=false;
// 				}
// 				if (!empty($tab_options['NO_SEARCH'][$tab_options['columns'][$column]['name']])){
// 					$searchable = false;
// 				}
// 				if ($searchable){
// 					$name = $tab_options['HAVING'][$name]['name'];
// 					if ($index == 0){
// 						$filtertxt =  " HAVING (( ".$name." == '".$search."' ) ";
// 					}
// 					else{
// 						$filtertxt .= " OR  ( ".$name." == '".$search."' ) ";
// 					}
// 					$index++;
// 				}
// 			}
// 			$queryDetails .= $filter.") ";
// 		}
// 	}
// 	return $queryDetails;
// }
					
						
						
						
						
						
						
						

//fonction qui retourne un string contenant le bloc généré ORDER BY de la requete
/*
* Tab_options : All the options for the specific table
* &$tab_options= array{
* 						'form_name'=> "show_all",....
* 						'Option' => value,
* 						}
*/
function ajaxsort(&$tab_options){
	if ($tab_options['columns'][$tab_options['order']['0']['column']]['orderable'] == "true"){
		$name = $tab_options['columns'][$tab_options['order']['0']['column']]['name'];
		
		if (!empty($tab_options["replace_query_arg"][$name])){
			$name= $tab_options["replace_query_arg"][$name];
		}
		$tri = $name;
		$sens = $tab_options['order']['0']['dir'];
	} else if ($tab_options['columns']) {
		foreach($tab_options['columns'] as $column){
			if ($column['orderable']=="true"){
				$tri = $column['name'];
				$sens = "asc";
				break;
			}
		}
	}
	$sort ="";
	if (!empty($tri) && !empty($sens)){
	$tab_iplike=array('H.IPADDR','IPADDRESS','IP','IPADDR');
	if (in_array(mb_strtoupper($tri),$tab_iplike)){
		$sort= " order by INET_ATON(".$tri.") ".$sens;
	}elseif ($tab_options['TRI']['SIGNED'][$tri]){
		$sort= " order by cast(".$$tri." as signed) ".$sens;
	}
	elseif($tab_options['TRI']['DATE'][$tri]){
	
		if(isset($tab_options['ARG_SQL'])){
			$sort =" order by STR_TO_DATE(%s,'%s') %s";
			$tab_options['ARG_SQL'][]=$tri;
			$tab_options['ARG_SQL'][]=$tab_options['TRI']['DATE'][$tri];
			$tab_options['ARG_SQL'][]=$sens;
		}else{
			$sort= " order by STR_TO_DATE(".$tri.",'".$tab_options['TRI']['DATE'][$tri]."') ".$sens;
		}
	}else{
		$sort= " order by ".$tri." ".$sens;
	}
	
	}
	return $sort;
	
}

//fonction qui retourne un string contenant le bloc généré LIMIT de la requete
/*
* Tab_options : All the options for the specific table
* $tab_options= array{
* 						'form_name'=> "show_all",....
* 						'Option' => value,
* 						}
*/
function ajaxlimit($tab_options){
	if (isset($tab_options['start'])){
		$limit = " limit ".$tab_options['start']." , ";
	}else{
		$limit = " limit 0 , ";
	}
	if (isset($tab_options['length'])){
		$limit .= $tab_options['length']." ";
	}else{
		$limit .= "10 ";
	}
	return $limit;	
}


//fonction qui met en forme les resultats
/*
* ResultDetails : Query return 
* $resultDetails = mysqli_result 
* $list_fields : Each available column of the table
* $list_fields = array {  
* 						'NAME'=>'h.name', ...
* 						'Column name' => Database value,
* 						 }
* Tab_options : All the options for the specific table
* $tab_options= array{
* 						'form_name'=> "show_all",....
* 						'Option' => value,
* 						}
*/
function ajaxgestionresults($resultDetails,$list_fields,$tab_options){
	global $protectedPost,$l,$pages_refs;
	$form_name=$tab_options['form_name'];
	$_SESSION['OCS']['list_fields'][$tab_options['table_name']]=$list_fields;
	$_SESSION['OCS']['col_tab'][$tab_options['table_name']]= array_flip($list_fields);
	if($resultDetails){
		if (isset($tab_options['JAVA']['CHECK'])){
			$javascript="OnClick='confirme(\"".htmlspecialchars($row_temp[$tab_options['JAVA']['CHECK']['NAME']], ENT_QUOTES)."\",".$value_of_field.",\"".$form_name."\",\"CONFIRM_CHECK\",\"".htmlspecialchars($tab_options['JAVA']['CHECK']['QUESTION'], ENT_QUOTES)." \")'";
		}else
			$javascript="";
		while($row = mysqli_fetch_assoc($resultDetails))
		{
			if (isset($tab_options['AS'])){
				foreach($tab_options['AS'] as $k=>$v){
					if($v!="SNAME"){
						$n = explode('.',$k);
						$n = end($n);
						$row[$n]= $row[$v];
					}
				}
			}
			$row_temp = $row;
			foreach($row as $rowKey=>$rowValue){
				$row[$rowKey]=htmlentities($rowValue);
			}
			foreach($list_fields as $key=>$column){
				$name = explode('.',$column);
				$column = end($name);
				$value_of_field = $row[$column];
				switch($key){
					case "CHECK":
						if ($value_of_field!= '&nbsp;'){
							$row[$key] = "<input type='checkbox' name='check".$value_of_field."' id='check".$value_of_field."' ".$javascript." ".(isset($tab_options['check'.$value_of_field])? " checked ": "").">";
						}
						break;
					case "SUP":
						if ( $value_of_field!= '&nbsp;'){
							if (isset($tab_options['LBL_POPUP'][$key])){
								if (isset($row[$tab_options['LBL_POPUP'][$key]]))
									$lbl_msg=$l->g(640)." ".$row_temp[$tab_options['LBL_POPUP'][$key]];
								else
									$lbl_msg=$tab_options['LBL_POPUP'][$key];
							}else
								$lbl_msg=$l->g(640)." ".$value_of_field;
							$row[$key]="<a href=# OnClick='confirme(\"\",\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"".$form_name."\",\"SUP_PROF\",\"".htmlspecialchars($lbl_msg, ENT_QUOTES)."\");'><span class='glyphicon glyphicon-remove'></span></a>";					
						}
						break;
					case "NAME":
						if ( !isset($tab_options['NO_NAME']['NAME'])){
							$link_computer="index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1";
							if ($row['ID'])
								$link_computer.="&systemid=".$row['ID'];
							if ($row['MD5_DEVICEID'])
								$link_computer.= "&crypt=".$row['MD5_DEVICEID'];
							$row[$column]="<a href='".$link_computer."'>".$value_of_field."</a>";
						}
						break;
					case "GROUP_NAME":
						$row['NAME']="<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_group_show']."&head=1&systemid=".$row['ID']."' target='_blank'>".$value_of_field."</a>";
						break;
					case "NULL":
						$row[$key]="&nbsp";
						break;
					case "MODIF":
						if (!isset($tab_options['MODIF']['IMG']))
							$image="image/modif_tab.png";
						else
							$image=$tab_options['MODIF']['IMG'];
						$row[$key]="<a href=# OnClick='pag(\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"MODIF\",\"".$form_name."\");'><img src=".$image."></a>";
						break;
					case "SELECT":
						$row[$key]="<a href=# OnClick='confirme(\"\",\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"".$form_name."\",\"SELECT\",\"".htmlspecialchars($tab_options['QUESTION']['SELECT'],ENT_QUOTES)."\");'><img src=image/prec16.png></a>";
						$lien = 'KO';
						break;
					case "OTHER":
						$row[$key]="<a href=#  OnClick='pag(\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"OTHER\",\"".$form_name."\");'><img src=image/red.png></a>";
						break;
					case "ZIP":
						$row[$key]="<a href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_tele_compress']."&no_header=1&timestamp=".$value_of_field."&type=".$tab_options['TYPE']['ZIP']."\"><img src=image/archives.png></a>";
						break;
					case "STAT":
						$row[$key]="<a href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_tele_stats']."&head=1&stat=".$value_of_field."\"><img src='image/stat.png'></a>";
						break;
					case "ACTIVE":
						$row[$key]="<a href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_tele_popup_active']."&head=1&active=".$value_of_field."\"><img src='image/activer.png' ></a>";
						break;
					case "SHOWACTIVE":					
						if(!empty($tab_options['SHOW_ONLY'][$key][$row['FILEID']])){
							$row[$column]="<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_tele_actives']."&head=1&timestamp=".$row['FILEID']."' >".$value_of_field."</a>";
						}
						break;
					case "MAC":
						if (isset($_SESSION['OCS']["mac"][mb_strtoupper(substr($value_of_field,0,8))]))
							$constr=$_SESSION['OCS']["mac"][mb_strtoupper(substr($value_of_field,0,8))];
						else
							$constr="<font color=red>".$l->g(885)."</font>";
						$row[$key]=$value_of_field." (<small>".$constr."</small>)";
						break;
					default :
						if (substr($key,0,11) == "PERCENT_BAR"){
							//require_once("function_graphic.php");
							//echo percent_bar($value_of_field);
							$row[$column]="<CENTER>".percent_bar($value_of_field)."</CENTER>";
						}
						if (!empty($tab_options['REPLACE_VALUE'][$key])){
							$row[$column]=$tab_options['REPLACE_VALUE'][$key][$value_of_field];				
						}
						if(!empty($tab_options['VALUE'][$key])){
							if(!empty($tab_options['LIEN_CHAMP'][$key])){
								$value_of_field=$tab_options['VALUE'][$key][$row[$tab_options['LIEN_CHAMP'][$key]]];
							}else{
								$row[$column] = $tab_options['VALUE'][$key][$row['ID']];
							}
						}
						if(!empty($tab_options['REPLACE_VALUE_ALL_TIME'][$key][$row[$tab_options['FIELD_REPLACE_VALUE_ALL_TIME']]])){
							$row[$column]=$tab_options['REPLACE_VALUE_ALL_TIME'][$key][$row[$tab_options['FIELD_REPLACE_VALUE_ALL_TIME']]];
						}
						if (!empty($tab_options['LIEN_LBL'][$key])){
							$row[$column]= "<a href='".$tab_options['LIEN_LBL'][$key].$row[$tab_options['LIEN_CHAMP'][$key]]."'>".$value_of_field."</a>";
						}
						if (!empty($tab_options['REPLACE_COLUMN_KEY'][$key])){
							$row[$tab_options['REPLACE_COLUMN_KEY'][$key]]=$row[$column];
							unset($row[$column]);
						}
						
					}
				if(!empty($tab_options['COLOR'][$key])){
					$row[$column]= "<font color='".$tab_options['COLOR'][$key]."'>".$row[$column]."</font>";
				}
				if(!empty($tab_options['SHOW_ONLY'][$key])){
					if(empty($tab_options['SHOW_ONLY'][$key][$value_of_field])&& empty($tab_options['EXIST'][$key])
									||(reset($tab_options['SHOW_ONLY'][$key]) == $row[$tab_options['EXIST'][$key]])){
						$row[$key]="";
					}
				}
				
			}
			$actions = array(
				"MODIF",
				"SUP",
				"ZIP",
				"STAT",
				"ACTIVE",
			);
			foreach($actions as $action){
				$row['ACTIONS'].= " ".$row[$action];
			}
			$rows[] = $row;
		}
	}else{
		$rows = 0;
	}
	return $rows;
}

//fonction qui ggere le retour de la requete Ajax 
/*
* $list_fields : Each available column of the table
* $list_fields = array {  
* 						'NAME'=>'h.name', ...
* 						'Column name' => Database value,
* 						 }
* Default_fields : Default columns displayed
* $default_fields= array{
* 						'NAME'=>'NAME', ...
* 						'Column name' => 'Column name',
* 						}
* List_col_cant_del : All the columns that will always be displayed
* $list_col_cant_del= array {
* 						'NAME'=>'NAME', ...
* 						'Column name' => 'Column name',
* 						} 
* $queryDetails = string 'SELECT QUERY'
* Tab_options : All the options for the specific table
* $tab_options= array{
* 						'form_name'=> "show_all",....
* 						'Option' => value,
* 						}
*/
function tab_req($list_fields,$default_fields,$list_col_cant_del,$queryDetails,$tab_options)
{
	global $protectedPost,$l,$pages_refs;
	if($queryDetails === false){
		$res =  array("draw"=> $tab_options['draw'],"recordsTotal"=> 0,  "recordsFiltered"=> 0 , "data"=>0 );
		echo json_encode($res);
		die;
	}
	$columns_special = array("CHECK",
			"SUP",
			"GROUP_NAME",
			"NULL",
			"MODIF",
			"SELECT",
			"ZIP",
			"OTHER",
			"STAT",
			"ACTIVE",
			"MAC",
			"MD5_DEVICEID",
	);
	
	
	$actions = array(
				"MODIF",
				"SUP",
				"ZIP",
				"STAT",
				"ACTIVE",
	);
	foreach($actions as $action){
		if(isset($list_fields[$action])){
			$list_fields['ACTIONS']="h.ID";
			break;
		}
	}
	
	$visible = 0;
	foreach($list_fields as $key=>$column){
		if (((in_array($key,$default_fields))||(in_array($key,$list_col_cant_del))|| in_array($key, $columns_special)||array_key_exists($key,$default_fields) || $key=="ACTIONS") && !in_array($key,$actions)){
			$visible++;
		}
	}
	$data = serialize($tab_options['visible']);
	$customized=false;
	if (count($tab_options['visible'])!=$visible){
		$customized=true;
		setcookie($tab_options['table_name']."_col",$data,time()+31536000);
	}
	else{
		if (isset($_COOKIE[$tab_options['table_name']."_col"])){
			if($data !=  $_COOKIE[$tab_options['table_name']."_col"]){
				setcookie($tab_options['table_name']."_col",$data,time()+31536000);
			}
			else{
				setcookie($tab_options['table_name']."_col", FALSE, time() - 3600 );
			}
		}
	}
	if (isset($tab_options['REQUEST'])){
		foreach ($tab_options['REQUEST'] as $field_name => $value){
			$resultDetails = mysql2_query_secure($value, $_SESSION['OCS']["readServer"],$tab_options['ARG'][$field_name]);
			while($item = mysqli_fetch_object($resultDetails)){
				if ($item -> FIRST != "")
				$tab_options['SHOW_ONLY'][$field_name][$item -> FIRST]=$item -> FIRST;
			}
		}
	}
	$table_name = $tab_options['table_name'];
	//search static values
	if (isset($_SESSION['OCS']['SQL_DATA_FIXE'][$table_name])){
		foreach ($_SESSION['OCS']['SQL_DATA_FIXE'][$table_name] as $key=>$sql){
			if (!isset($_SESSION['OCS']['ARG_DATA_FIXE'][$table_name][$key]))
				$arg=array();
			else
				$arg=$_SESSION['OCS']['ARG_DATA_FIXE'][$table_name][$key];
			if ($table_name == "TAB_MULTICRITERE"){
				$sql.=" and hardware_id in (".implode(',',$_SESSION['OCS']['ID_REQ']).") group by hardware_id ";
				//ajout du group by pour r�gler le probl�me des r�sultats multiples sur une requete
				//on affiche juste le premier crit�re qui match
				$result = mysqli_query($_SESSION['OCS']["readServer"],$sql);
			}else{
				//add sort on column if need it
				if ($protectedPost['tri_fixe']!='' and strstr($sql,$protectedPost['tri_fixe'])){
					$sql.=" order by '%s' %s";
					array_push($protectedPost['tri_fixe'],$arg);
					array_push($protectedPost['sens_'.$table_name],$arg);
				}
				$sql.= $limit;
				$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
			}
			while($item = mysqli_fetch_object($result)){
				if ($item->HARDWARE_ID != "")
					$champs_index=$item->HARDWARE_ID;
				elseif($item->FILEID != "")
				$champs_index=$item->FILEID;
				//echo $champs_index."<br>";
				if (isset($tablename_fixe_value)){
					if (strstr($sql,$tablename_fixe_value[0]))
						$list_id_tri_fixe[]=$champs_index;
				}
				foreach ($item as $field=>$value){
					if ($field != "HARDWARE_ID" and $field != "FILEID" and $field != "ID"){
						$tab_options['NO_SEARCH'][$field]=$field;
						//			echo "<br>champs => ".$field."   valeur => ".$value;
						$tab_options['REPLACE_VALUE_ALL_TIME'][$field][$champs_index]=$value;
					}
				}
			}
		}
	} 
	$link=$_SESSION['OCS']["readServer"];
	
	
	$sqlfunctions[]='count';
	$sqlfunctions[]='sum';
	$sqlfunctions[]='min';
	$sqlfunctions[]='max';
	foreach($sqlfunctions as $sqlfunction){
		preg_match("/$sqlfunction\(.+\) \w*/i", $queryDetails, $matches);
		foreach ($matches as $match){
				$req = preg_split("/\)/", $match);
				$request=$req['0'].") ";
				$column = trim($req['1']);
				$tab_options['HAVING'][$column]['name']=$request ;
		}
	}
	
	
	$queryDetails = ajaxfiltre($queryDetails,$tab_options);
	// NOT USED YET
	//$queryDetails = ajaxfiltrehaving($queryDetails,$tab_options);
	
	$queryDetails .= ajaxsort($tab_options);
	$_SESSION['OCS']['csv']['SQLNOLIMIT'][$tab_options['table_name']]=$queryDetails;
	$queryDetails .= ajaxlimit($tab_options);
	$_SESSION['OCS']['csv']['SQL'][$tab_options['table_name']]=$queryDetails;
	$_SESSION['OCS']['csv']['REPLACE_VALUE'][$tab_options['table_name']]=$tab_options['REPLACE_VALUE'];
	
	if (isset($tab_options['ARG_SQL']))
		$_SESSION['OCS']['csv']['ARG'][$tab_options['table_name']]=$tab_options['ARG_SQL'];

	$queryDetails=substr_replace(ltrim($queryDetails),"SELECT SQL_CALC_FOUND_ROWS ", 0 , 6);
	if (isset($tab_options['ARG_SQL']))
		$resultDetails = mysql2_query_secure($queryDetails, $link,$tab_options['ARG_SQL']);
	else
		$resultDetails = mysql2_query_secure($queryDetails, $link);
	
	$rows = ajaxgestionresults($resultDetails,$list_fields,$tab_options);

	if (is_null($rows)){
		$rows=0;
	}
	
	if(is_array($_SESSION['OCS']['SQL_DEBUG']) && ($_SESSION['OCS']['DEBUG'] == 'ON')){
		$debug = end($_SESSION['OCS']['SQL_DEBUG']);
	}
	// Data set length after filtering
	$resFilterLength = mysql2_query_secure("SELECT FOUND_ROWS()",$link);
	$recordsFiltered = mysqli_fetch_row($resFilterLength);
	$recordsFiltered=intval($recordsFiltered[0]);
	if($rows === 0){
		$recordsFiltered = 0;
	}
	if($tab_options["search"] && $tab_options["search"]['value']==""){
		$_SESSION['OCS'][$tab_options['table_name']]['nb_resultat']=$recordsFiltered;
	}
	if (isset($_SESSION['OCS'][$tab_options['table_name']]['nb_resultat'])){
		$recordsTotal = $_SESSION['OCS'][$tab_options['table_name']]['nb_resultat'];
	
	}else{
		$recordsTotal=$recordsFiltered;
	}	
	if(is_array($_SESSION['OCS']['SQL_DEBUG']) && ($_SESSION['OCS']['DEBUG'] == 'ON')){
		$res =  array("draw"=> $tab_options['draw'],"recordsTotal"=> $recordsTotal,
				"recordsFiltered"=> $recordsFiltered, "data"=>$rows, "customized"=>$customized,
				"debug"=>$debug);
	}else{
		$res =  array("draw"=> $tab_options['draw'],"recordsTotal"=> $recordsTotal,  
				"recordsFiltered"=> $recordsFiltered, "data"=>$rows, "customized"=>$customized);
	}
	echo json_encode($res);
}











//fonction qui permet de g�rer les donn�es � afficher dans le tableau
function gestion_donnees($sql_data,$list_fields,$tab_options,$form_name,$default_fields,$list_col_cant_del,$queryDetails,$table_name){
	global $l,$protectedPost,$pages_refs;
	
	//p($tab_options['REPLACE_VALUE_ALL_TIME']);
	$_SESSION['OCS']['list_fields'][$table_name]=$list_fields;
	//requete de condition d'affichage
	//attention: la requete doit etre du style:
	//select champ1 AS FIRST from table where...
	if (isset($tab_options['REQUEST'])){
		foreach ($tab_options['REQUEST'] as $field_name => $value){
			$tab_condition[$field_name]=array();
			$resultDetails = mysql2_query_secure($value, $_SESSION['OCS']["readServer"],$tab_options['ARG'][$field_name]);
			while($item = mysqli_fetch_object($resultDetails)){
				$tab_condition[$field_name][$item -> FIRST]=$item -> FIRST;
			}		
		}
	}
	if (isset($sql_data)){
		foreach ($sql_data as $i=>$donnees){
			foreach($list_fields as $key=>$value){
				$htmlentities=true;
				$truelabel=$key;
			//	p($tab_options);
				//gestion des as de colonne
				if (isset($tab_options['AS'][$value]))
				$value=$tab_options['AS'][$value];
				//echo $value."<br>";				
				$num_col=$key;
				if ($default_fields[$key])
				$correct_list_fields[$num_col]=$num_col;
				if ($list_col_cant_del[$key])
				$correct_list_col_cant_del[$num_col]=$num_col;
				$alias=explode('.',$value);
				if (isset($alias[1])){
					$no_alias_value=$alias[1];
				}else
				 	$no_alias_value=$value;

				//echo $no_alias_value;
				//si aucune valeur, on affiche un espace
				if ($donnees[$no_alias_value] == ""){
					$value_of_field = "&nbsp";
					$htmlentities=false;
				}else //sinon, on affiche la valeur
				{
					$value_of_field=$donnees[$no_alias_value];
				}
				
				//utf8 or not?
				$value_of_field=data_encode_utf8($value_of_field);
				
				$col[$i]=$key;
				if ($protectedPost['sens_'.$table_name] == "ASC")
					$sens="DESC";
				else
					$sens="ASC";
					
				$affich='OK';
				//on n'affiche pas de lien sur les colonnes non pr�sentes dans la requete
				if (isset($tab_options['NO_TRI'][$key]))					
					$lien='KO';	
				else
					$lien='OK';

				if (isset($tab_options['REPLACE_VALUE_ALL_TIME'][$key])){
					if (isset($tab_options['FIELD_REPLACE_VALUE_ALL_TIME']))
						$value_of_field=$tab_options['REPLACE_VALUE_ALL_TIME'][$key][$donnees[$tab_options['FIELD_REPLACE_VALUE_ALL_TIME']]];
					else
						$value_of_field=$tab_options['REPLACE_VALUE_ALL_TIME'][$key][$donnees['ID']];					
				}
				
				
	
				if (isset($tab_options['REPLACE_VALUE'][$key])){
					//if multi value, $temp_val[1] isset
					$temp_val=explode('&&&',$value_of_field);
					$multi_value=0;
					$temp_value_of_field="";	
					while (isset($temp_val[$multi_value])){
						$temp_value_of_field.=$tab_options['REPLACE_VALUE'][$key][$temp_val[$multi_value]]."<br>";	
						$multi_value++;
					}
					$temp_value_of_field=substr($temp_value_of_field,0,-4);
					$value_of_field=$temp_value_of_field;	
				}
				if (isset($tab_options['REPLACE_WITH_CONDITION'][$key][$value_of_field])){
					if (!is_array($tab_options['REPLACE_WITH_CONDITION'][$key][$value_of_field]))
						$value_of_field= $tab_options['REPLACE_WITH_CONDITION'][$key][$value_of_field];
					else{
						foreach ($tab_options['REPLACE_WITH_CONDITION'][$key][$value_of_field] as $condition=>$condition_value){
							if ($donnees[$condition] == '' or is_null($donnees[$condition]))
							{
								$value_of_field=$condition_value;
							}
						}
						
					}
				}

				if (isset($tab_options['REPLACE_WITH_LIMIT']['UP'][$key])){
					if ($value_of_field > $tab_options['REPLACE_WITH_LIMIT']['UP'][$key])
						$value_of_field= $tab_options['REPLACE_WITH_LIMIT']['UPVALUE'][$key];
				}
				
				if (isset($tab_options['REPLACE_WITH_LIMIT']['DOWN'][$key])){
					if ($value_of_field < $tab_options['REPLACE_WITH_LIMIT']['DOWN'][$key])
						$value_of_field = $tab_options['REPLACE_WITH_LIMIT']['DOWNVALUE'][$key];
				}
				
				unset($key2);
				if (isset($tab_condition[$key])){
						if ((!$tab_condition[$key][$donnees[$tab_options['FIELD'][$key]]] and !$tab_options['EXIST'][$key])
							or ($tab_condition[$key][$donnees[$tab_options['FIELD'][$key]]] and $tab_options['EXIST'][$key])){
							if ($key == "STAT" or $key == "SUP" or $key == "CHECK"){
								$key2 = "NULL";
							}else{
								$data[$i][$num_col]=$value_of_field;
								$affich="KO";
							}
						}
				}
				//if (!isset($entete[$num_col])){
					if (!isset($tab_options['LBL'][$key])){
						$entete[$num_col]=$key;
					}else
						$entete[$num_col]=$tab_options['LBL'][$key];
				//}
				
				if (isset($tab_options['NO_LIEN_CHAMP']['SQL'][$key])){
					$exit=false;
					foreach ($tab_options['NO_LIEN_CHAMP']['SQL'][$key] as $id=>$sql_rest){
						$sql=$sql_rest;
						if (isset($tab_options['NO_LIEN_CHAMP']['ARG'][$id][$key]))
							$arg=$donnees[$tab_options['NO_LIEN_CHAMP']['ARG'][$id][$key]];
						else
							$arg="";
						$result_lien = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
						if ($item = mysqli_fetch_object($result_lien)){
						  $data[$i][$num_col]="<a href='".$tab_options['LIEN_LBL'][$key][$id].$donnees[$tab_options['LIEN_CHAMP'][$key][$id]]."' target='_blank'>".$value_of_field."</a>";				
						 // $exit=true;
						 break;
						}else
						echo 'toto';	
									
						
					}
				}
				
				//si un lien doit �tre mis sur le champ
				//l'option $tab_options['NO_LIEN_CHAMP'] emp�che de mettre un lien sur certaines
				//valeurs du champs
				//exemple, si vous ne voulez pas mettre un lien si le champ est 0,
				//$tab_options['NO_LIEN_CHAMP'][$key] = array(0);
				if (isset($tab_options['LIEN_LBL'][$key]) and !is_array($tab_options['LIEN_LBL'][$key])
					and (!isset($tab_options['NO_LIEN_CHAMP'][$key]) or !in_array($value_of_field,$tab_options['NO_LIEN_CHAMP'][$key]))){
					$affich="KO";
				
					if (!isset($tab_options['LIEN_TYPE'][$key])){
						$data[$i][$num_col]="<a href='".$tab_options['LIEN_LBL'][$key].$donnees[$tab_options['LIEN_CHAMP'][$key]]."' target='_blank'>".$value_of_field."</a>";
					}else{
						if (!isset($tab_options['POPUP_SIZE'][$key]))
						$size="width=550,height=350";
						else
						$size=$tab_options['POPUP_SIZE'][$key];
						$data[$i][$num_col]="<a href=\"".$tab_options['LIEN_LBL'][$key].$donnees[$tab_options['LIEN_CHAMP'][$key]]."\")>".$value_of_field."</a>";
					
					}
				}	

				
				if (isset($tab_options['JAVA']['CHECK'])){
						$javascript="OnClick='confirme(\"".htmlspecialchars($donnees[$tab_options['JAVA']['CHECK']['NAME']], ENT_QUOTES)."\",".$value_of_field.",\"".$form_name."\",\"CONFIRM_CHECK\",\"".htmlspecialchars($tab_options['JAVA']['CHECK']['QUESTION'], ENT_QUOTES)." \")'";
				}else
						$javascript="";
				
				//si on a demander un affichage que sur certaine ID
				if (is_array($tab_options) and !$tab_options['SHOW_ONLY'][$key][$value_of_field] and $tab_options['SHOW_ONLY'][$key]){
					$key = "NULL";
				}		
				
				if (isset($tab_options['COLOR'][$key])){
					$value_of_field="<font color=".$tab_options['COLOR'][$key].">".$value_of_field."</font>";
					$htmlentities=false;
				}
				if ($affich == 'OK'){
					
					$lbl_column=array("SUP"=>$l->g(122),
									  "MODIF"=>$l->g(115),
									  "CHECK"=>$l->g(1119) . "<input type='checkbox' name='ALL' id='ALL' Onclick='checkall();'>");
					if (!isset($tab_options['NO_NAME']['NAME']))
							$lbl_column["NAME"]=$l->g(23);
					//modify lbl of column
					if (!isset($entete[$num_col]) 
						or ($entete[$num_col] == $key and !isset($tab_options['LBL'][$key]))){
						if (array_key_exists($key,$lbl_column))
							$entete[$num_col]=$lbl_column[$key];
						else
							$entete[$num_col]=$truelabel;
					}
					if ($key == "NULL" or isset($key2)){
						$data[$i][$num_col]="&nbsp";
						$lien = 'KO';
					}elseif ($key == "GROUP_NAME"){
						$data[$i][$num_col]="<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_group_show']."&head=1&systemid=".$donnees['ID']."' target='_blank'>".$value_of_field."</a>";
					}elseif ($key == "SUP" and $value_of_field!= '&nbsp;'){
						if (isset($tab_options['LBL_POPUP'][$key])){
							if (isset($donnees[$tab_options['LBL_POPUP'][$key]]))
								$lbl_msg=$l->g(640)." ".$donnees[$tab_options['LBL_POPUP'][$key]];
							else
								$lbl_msg=$tab_options['LBL_POPUP'][$key];
						}else
							$lbl_msg=$l->g(640)." ".$value_of_field;
						$data[$i][$num_col]="<a href=# OnClick='confirme(\"\",\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"".$form_name."\",\"SUP_PROF\",\"".htmlspecialchars($lbl_msg, ENT_QUOTES)."\");'><img src=image/delete-small.png></a>";
						$lien = 'KO';		
					}elseif ($key == "MODIF"){
						if (!isset($tab_options['MODIF']['IMG']))
						$image="image/modif_tab.png";
						else
						$image=$tab_options['MODIF']['IMG'];
						$data[$i][$num_col]="<a href=# OnClick='pag(\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"MODIF\",\"".$form_name."\");'><img src=".$image."></a>";
						$lien = 'KO';
					}elseif ($key == "SELECT"){
						$data[$i][$num_col]="<a href=# OnClick='confirme(\"\",\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"".$form_name."\",\"SELECT\",\"".htmlspecialchars($tab_options['QUESTION']['SELECT'],ENT_QUOTES)."\");'><img src=image/prec16.png></a>";
						$lien = 'KO';
					}elseif ($key == "OTHER"){
						$data[$i][$num_col]="<a href=#  OnClick='pag(\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"OTHER\",\"".$form_name."\");'><img src=image/red.png></a>";
						$lien = 'KO';
					}elseif ($key == "ZIP"){
						$data[$i][$num_col]="<a href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_tele_compress']."&no_header=1&timestamp=".$value_of_field."&type=".$tab_options['TYPE']['ZIP']."\"><img src=image/archives.png></a>";
						$lien = 'KO';
					}
					elseif ($key == "STAT"){
						$data[$i][$num_col]="<a href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_tele_stats']."&head=1&stat=".$value_of_field."\"><img src='image/stat.png'></a>";
						$lien = 'KO';
					}elseif ($key == "ACTIVE"){
						$data[$i][$num_col]="<a href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_tele_popup_active']."&head=1&active=".$value_of_field."\"><img src='image/activer.png' ></a>";
						$lien = 'KO';
					}elseif ($key == "SHOWACTIVE"){
						$data[$i][$num_col]="<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_tele_actives']."&head=1&timestamp=".$donnees['FILEID']."' target=_blank>".$value_of_field."</a>";
					}
					elseif ($key == "CHECK" and $value_of_field!= '&nbsp;'){
						$data[$i][$num_col]="<input type='checkbox' name='check".$value_of_field."' id='check".$value_of_field."' ".$javascript." ".(isset($protectedPost['check'.$value_of_field])? " checked ": "").">";
						$lien = 'KO';		
					}elseif ($key == "NAME" and !isset($tab_options['NO_NAME']['NAME'])){
							$link_computer="index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1";
							if ($donnees['ID'])
								$link_computer.="&systemid=".$donnees['ID'];
							if ($donnees['MD5_DEVICEID'])
								$link_computer.= "&crypt=".$donnees['MD5_DEVICEID'];
							$data[$i][$num_col]="<a href='".$link_computer."'  target='_blank'>".$value_of_field."</a>";
					}elseif ($key == "MAC"){
						//echo substr($value_of_field,0,8);
						//echo $_SESSION['OCS']["mac"][substr($value_of_field,0,8)];
						if (isset($_SESSION['OCS']["mac"][mb_strtoupper(substr($value_of_field,0,8))]))
						$constr=$_SESSION['OCS']["mac"][mb_strtoupper(substr($value_of_field,0,8))];
						else
						$constr="<font color=red>".$l->g(885)."</font>";
						//echo "=>".$constr."<br>";
						$data[$i][$num_col]=$value_of_field." (<small>".$constr."</small>)";						
					}elseif (substr($key,0,11) == "PERCENT_BAR"){
						require_once("function_graphic.php");
						$data[$i][$num_col]="<CENTER>".percent_bar($value_of_field)."</CENTER>";
						//$lien = 'KO';						
					}
					else{		
						if (isset($tab_options['OTHER'][$key][$value_of_field])){
							$end="<a href=# OnClick='pag(\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"OTHER\",\"".$form_name."\");'><img src=".$tab_options['OTHER']['IMG']."></a>";
						}elseif (isset($tab_options['OTHER_BIS'][$key][$value_of_field])){
							$end="<a href=# OnClick='pag(\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"OTHER_BIS\",\"".$form_name."\");'><img src=".$tab_options['OTHER_BIS']['IMG']."></a>";
						}elseif (isset($tab_options['OTHER_TER'][$key][$value_of_field])){
							$end="<a href=# OnClick='pag(\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"OTHER_TER\",\"".$form_name."\");'><img src=".$tab_options['OTHER_TER']['IMG']."></a>";
						}else{
							$end="";
						}
						if ($htmlentities)
							//$value_of_field=htmlentities($value_of_field,ENT_COMPAT,'UTF-8');
							$value_of_field=strip_tags_array($value_of_field);
							
						$data[$i][$num_col]=$value_of_field.$end;
						
					}
					
				}
	
				if ($lien == 'OK'){
					$deb="<a onclick='return tri(\"".$value."\",\"tri_".$table_name."\",\"".$sens."\",\"sens_".$table_name."\",\"".$form_name."\");' >";
					$fin="</a>";
					$entete[$num_col]=$deb.$entete[$num_col].$fin;
					if ($protectedPost['tri_'.$table_name] == $value){
						if ($protectedPost['sens_'.$table_name] == 'ASC')
							$img="<img src='image/down.png'>";
						else
							$img="<img src='image/up.png'>";
						$entete[$num_col]=$img.$entete[$num_col];
					}
				}

			}
			
			
		}
		if ($tab_options['UP']){
			$i=0;
			while($data[$i]){
				foreach ($tab_options['UP'] as $key=>$value){
					if ($data[$i][$key] == $value){
						$value_temp=$data[$i];				
						unset($data[$i]);
					}	
				}				
				$i++;	
			}
			array_unshift ($data, $value_temp);
		}
	//	echo $protectedPost['tri_'.$table_name];
	//	echo "<br><hr>";
		//p($tab_options['REPLACE_VALUE']);
		if(isset($tab_options['REPLACE_VALUE'][$protectedPost['tri_'.$table_name]])){
			//p($data);
//echo "<br><hr><br>";
			if ($protectedPost['sens_repart_tag'] == 'ASC')
				asort($data);
			else
				arsort($data);
			//	p($data);
		}
	 return array('ENTETE'=>$entete,'DATA'=>$data,'correct_list_fields'=>$correct_list_fields,'correct_list_col_cant_del'=>$correct_list_col_cant_del);
	}else
	return false;
}
function del_selection($form_name){
	global $l;
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
			echo "<td align=center><a href=# onclick=garde_check(\"image/delete.png\",\"\")><img src='image/delete.png' title='".$l->g(162)."' ></a></td>";
		//}
	 echo "</tr></tr></table>";
	 echo "<input type='hidden' id='del_check' name='del_check' value=''>";
}

function js_tooltip(){
	echo "<script language='javascript' type='text/javascript' src='js/tooltip.js'></script>";
	echo "<div id='mouse_pointer' class='tooltip'></div>";	
}

/*js_bulle_info();
$bulle=bulle_info("");
echo "<a ".$bulle.">testst</a>";*/

function tooltip($txt){
	return " onmouseover=\"show_me('".addslashes($txt)."');\" onmouseout='hidden_me();'";
}

function iframe($link){
	global $l;
	echo "<div class='iframe_div'>";
	echo "<p><a href='$link'  target='blank'   class='iframe_link' >".$l->g(1374)."</a></p>";
	echo "<div style='height:100%'><iframe  class='well well-sm' src=\"$link\">	</iframe></div>";
	echo "</div>";
}

?>
