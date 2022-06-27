<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */
if (AJAX) {
    parse_str($protectedPost['ocs']['0'], $params);
    $protectedPost += $params;
    ob_start();
}

require_once('require/fonction.inc.php');
$form_name = "search_soft";
$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
echo open_form($form_name, '', '', 'form-horizontal');
//html
$xml_file = "index.php?" . PAG_INDEX . "=" . $pages_refs['ms_options'] . "&no_header=1";
echo "\n" . '<script type="text/javascript">
	window.onload = function(){initAutoComplete(document.getElementById(\'' . $form_name . '\'), document.getElementById(\'logiciel_text\'), document.getElementById(\'bouton-submit\'),\'' . $xml_file . '\')}
	</script>';
?>
<div class="row">
    <div class="col col-md-4 col-xs-offset-0 col-md-offset-4">
            <?php remplirListe("logiciel_select", $l->g(20)); ?>
    </div>
</div>
<div class="row">
    <div class="col col-md-4 col-xs-offset-0 col-md-offset-4">
        <div class="form-group">
            <label class="control-label col-sm-2" for="logiciel_text>">Text :</label>
            <div class="col-sm-10">
                <input class="form-control" type="text" name="logiciel_text"  id="logiciel_text" value="<?php echo $protectedPost['logiciel_text'] ?>">
                <p><?php echo $l->g(358) ?></p>
            </div>
        </div>
    </div>
</div>
<br />
<br />
<div class="row">
    <div class="col-md-12">
        <input type="submit" class="btn btn-success" id="bouton-submit" value="<?php echo $l->g(13); ?>" name="bouton-submit">
    </div>
</div>
<br />
<br />

<?php
echo '<div id="fr">';

// voir fonction.php
if (is_defined($protectedPost['logiciel_select']) || is_defined($protectedPost['logiciel_text'])) {                                   //logiciel du select name='logiciel'
    if (is_defined($protectedPost['logiciel_select'])) {
        $logiciel = $protectedPost['logiciel_select'];
    } else {
        $logiciel = $protectedPost['logiciel_text'];
        // Check for wildcard
        if (strpos($logiciel, '*') !== false || strpos($logiciel,'?') !== false) {
            $wildcard = true;
            $logiciel = str_replace("*", "%", $logiciel);
            $logiciel = str_replace("?", "_", $logiciel);
        }
    }

    $table_name = $form_name;

    $tab_options['table_name'] = $table_name;
    $list_fields = array('NAME' => 'h.NAME',
        'ip' => 'h.IPADDR',
        'domaine' => 'h.WORKGROUP',
        'user' => 'h.USERID',
        'snom' => 's.NAME as softname',
        'sversion' => 's.VERSION',
        'sfold' => 's.FOLDER');
    $list_col_cant_del = array(
        'ip' => 'h.IPADDR',
        'snom' => 's.NAME as softname',
    );
    $default_fields = $list_fields;
    $tab_options['AS']['s.NAME'] = 'SNAME';
    $queryDetails = "SELECT DISTINCT h.ID,";
    foreach ($list_fields as $value) {
        if ($value == 's.NAME') {
            $queryDetails .= $value . " as " . $tab_options['AS']['s.NAME'] . ",";
        } else {
            $queryDetails .= $value . ",";
        }
    }
    $queryDetails = substr($queryDetails, 0, -1);

    if($wildcard){
        $queryDetails .= " FROM hardware h INNER JOIN softwares s ON s.hardware_id = h.ID INNER JOIN accountinfo a ON a.hardware_id = h.ID
                       WHERE s.HARDWARE_ID =h.ID and s.NAME like '" . $logiciel . "' ";
    }else{
        $queryDetails .= " FROM hardware h INNER JOIN softwares s ON s.hardware_id = h.ID INNER JOIN accountinfo a ON a.hardware_id = h.ID
                               WHERE s.HARDWARE_ID =h.ID and s.NAME='" . $logiciel . "' ";
    }

    if (is_defined($_SESSION['OCS']["mesmachines"])) {
        $queryDetails .= "AND " . $_SESSION['OCS']["mesmachines"];
    }

    $tab_options['LBL']['NAME'] = $l->g(478);
    $tab_options['LBL']['ip'] = $l->g(176);
    $tab_options['LBL']['domaine'] = $l->g(680);
    $tab_options['LBL']['user'] = $l->g(24);
    $tab_options['LBL']['snom'] = $l->g(847);
    $tab_options['LBL']['sversion'] = $l->g(848);
    $tab_options['LBL']['sfold'] = $l->g(849);

    ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
}

echo '</div>';

echo close_form();

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $queryDetails, $tab_options);
}
?>
