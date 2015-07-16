<?php

function show_computer_menu($computer_id) {
	$menu_serializer = new XMLMenuSerializer();
	$menu = $menu_serializer->unserialize(file_get_contents('config/computer/menu.xml'));
	
	$menu_renderer = new ComputerMenuRenderer($computer_id, $_SESSION['OCS']['url_service']);
	
	echo '<div class="navbar navbar-default">';
	echo $menu_renderer->render($menu);
	echo '</div>';
}

function show_computer_title($computer) {
	global $l;
	
	$urls = $_SESSION['OCS']['url_service'];
	
	echo '<h3>';
	echo $computer->NAME;
	if ($_SESSION['OCS']['profile']->getRestriction('EXPORT_XML', 'NO') == "NO") {
		echo ' <small><a href="index.php?'.PAG_INDEX.'='.$urls->getUrl('ms_export_ocs').'&no_header=1&systemid='.$computer->ID.'" target="_blank">'.$l->g(1304).'</a></small>';
	}
	echo '</h3>';
}

 function show_computer_summary($computer) {
	global $l;
	
	$urls = $_SESSION['OCS']['url_service'];
	
	$labels = array(
		'SYSTEM' => array(
			'USERID' =>		$l->g(24),
			'OSNAME' =>		$l->g(274),
			'OSVERSION' =>	$l->g(275),
			'OSCOMMENTS' =>	$l->g(286),
			'DESCRIPTION'=>	$l->g(53),
			'WINCOMPANY' =>	$l->g(51),
			'WINOWNER' =>	$l->g(348),
			'WINPRODID' =>	$l->g(111),
			'WINPRODKEY' =>	$l->g(553),
			'VMTYPE' =>		$l->g(1267),
		),
		'NETWORK' => array(
			'WORKGROUP' =>	$l->g(33),
			'USERDOMAIN' =>	$l->g(557),
			'IPADDR' =>		$l->g(34),
			'NAME_RZ' =>	$l->g(304),
		),
		'HARDWARE' => array(
			'SWAP' =>		$l->g(50),
			'MEMORY' =>		$l->g(26),
			'UUID' =>		$l->g(1268),
			'ARCH' =>		$l->g(1247)
		),
		'AGENT' => array(
			'USERAGENT' =>	$l->g(357),
			'LASTDATE' =>	$l->g(46),
			'LASTCOME'=>	$l->g(820),
		),
	);
	
	$cat_labels = array(
		'SYSTEM' => $l->g(1387),
		'NETWORK' => $l->g(1388),
		'HARDWARE' => $l->g(1389),
		'AGENT' => $l->g(1390),
	);
	
	foreach ($labels as $cat_key => $cat) {
		foreach ($cat as $key => $lbl) {
			if ($key == "MEMORY") {
				$sqlMem = "SELECT SUM(capacity) AS 'capa' FROM memories WHERE hardware_id=%s";
				$argMem = $computer->ID;
				$resMem = mysql2_query_secure($sqlMem, $_SESSION['OCS']["readServer"], $argMem);
				$valMem = mysqli_fetch_array($resMem);
				
				if ($valMem["capa"] > 0) {
					$memory = $valMem["capa"];
				} else {
					$memory = $computer->$key;
				}
				$data[$key]=$memory;
			} elseif ($key == "LASTDATE" or $key == "LASTCOME") {
				$data[$key]=dateTimeFromMysql($computer->$key);
			} elseif ($key == "NAME_RZ") {
				$data[$key]="";
				$data_RZ=subnet_name($computer->ID);
				$nb_val=count($data_RZ);
				
				if ($nb_val == 1) {
					$data[$key] = $data_RZ[0];
				} elseif (isset($data_RZ)) {
					foreach($data_RZ as $index => $value) {
						$data[$key] .= $index." => ".$value."<br>";
					}
				}
			} elseif ($key == "VMTYPE" and $computer->UUID != '') {
				$sqlVM = "select vm.hardware_id,vm.vmtype, h.name from virtualmachines vm left join hardware h on vm.hardware_id=h.id where vm.uuid='%s' order by h.name DESC";
				$argVM = $computer->UUID;
				$resVM = mysql2_query_secure($sqlVM,$_SESSION['OCS']["readServer"],$argVM);
				$valVM = mysqli_fetch_array( $resVM );
				$data[$key]=$valVM['vmtype'];
				$link_vm="<a href='index.php?".PAG_INDEX."=".$urls->getUrl('ms_computer')."&head=1&systemid=".$valVM['hardware_id']."'  target='_blank'><font color=red>".$valVM['name']."</font></a>";
				$link[$key]=true;
				
				if ($data[$key] != '') {
					msg_info($l->g(1266)."<br>".$l->g(1269).': '.$link_vm);
				}
			} elseif ($key == "IPADDR" and $_SESSION['OCS']['profile']->getRestriction('WOL', 'NO')=="NO") {
				$data[$key] = $computer->$key." <a href=# OnClick='confirme(\"\",\"WOL\",\"bandeau\",\"WOL\",\"".$l->g(1283)."\");'><i>WOL</i></a>";
				$link[$key] = true;
			} elseif ($computer->$key != '') {
				$data[$key] = $computer->$key;
			}
		}
	}
	
 	echo open_form("bandeau");
	
 	show_summary($data, $labels, $cat_labels, $link);
 	echo "<input type='hidden' id='WOL' name='WOL' value=''>";
	
 	echo close_form();
 }

function show_summary($data, $labels, $cat_labels, $links = array()) {
	global $protectedGet, $pages_refs;
	
	$data = data_encode_utf8($data);
	$nb_col = 2;
	$i = 0;
	
	echo '<table class="summary">';
	foreach ($labels as $cat_key => $cat) {
		if ($i % $nb_col == 0) {
			echo '<tr class="summary-row">';
		}
		
		echo '<td class="summary-cell">';
		echo '<h5>'.mb_strtoupper($cat_labels[$cat_key]).'</h5>';
		
		foreach ($cat as $name => $label) {
			$value = $data[$name];
			
			if (trim($value) != '') {
				if (!array_key_exists($name, $links)) {
					$value = strip_tags_array($value);
				}
	
				if ($name == "IPADDR") {
					$value = preg_replace('/([x0-9])\//', '$1 / ', $value);
				}
				
				echo '<div class="summary-header">'.$label.' :</div>';
				echo '<div class="summary-value">'.$value.'</div>';
			}
		}
		echo '</td>';
		
		$i++;
		if ($i % $nb_col == 0) {
			echo '</tr>';
		}
	}
	
	if ($i % $nb_col != 0) {
		echo '</tr>';
	}
	
	echo '</table>';	
}

?>