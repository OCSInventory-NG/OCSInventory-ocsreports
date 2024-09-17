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

?>
<script language='javascript'>

    function getext(filename) {
        var parts = filename.split('.');
        return(parts.pop());
    }

    function namefile(filename) {
        var parts = [];
        var parts2 = [];

        parts = filename.split('.');
        parts2 = parts[0].split('\\\');
                var part2return = parts2.pop();
        return(part2return);
    }

    function verif_file_format(champ) {

        var ExtList = new Array('exe');
        filename = document.getElementById(champ).value.toLowerCase();
        fileExt = getext(filename);
        for (i = 0; i < ExtList.length; i++)
        {
            if (fileExt == ExtList[i])
            {
                return (true);
            }
        }
        alert('<?php mysqli_real_escape_string($_SESSION['OCS']["readServer"], $l->g(168)) ?> ');
        return (false);
    }

</script>
<?php
$umf = "upload_max_filesize";
$valTumf = ini_get($umf);
$valBumf = return_bytes($valTumf);

$form_name = "upload_client";
$table_name = $form_name;

$tab_options = $protectedPost;
$tab_options['form_name'] = $form_name;
$tab_options['table_name'] = $table_name;
if (isset($_FILES['file_upload']['name'])) {
    if ($_FILES['file_upload']['size'] != 0) {
        $fname = preg_replace("/[^A-Za-z0-9\._]/", "", $_FILES['file_upload']['name']);
        $platform = "windows";
        $filename = $_FILES['file_upload']['tmp_name'];
        $fd = fopen($filename, "r");
        $contents = fread($fd, filesize($filename));
        fclose($fd);
        $binary = $contents;
        $sql = "DELETE FROM deploy where name='%s'";
        $arg = $fname;
        mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
        $sql = "INSERT INTO deploy values ('%s','%s')";
        $arg = array($fname, $binary);
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
        if (!$result) {
            msg_error($l->g(2003) . mysqli_errno($_SESSION['OCS']["writeServer"]) . "<br>" . mysqli_error($_SESSION['OCS']["writeServer"]));
        } else {
            msg_success($l->g(137) . " " . $fname . " " . $l->g(234));
            $tab_options['CACHE'] = 'RESET';
        }
    } else {
        msg_error($l->g(920));
    }
}

if (is_defined($protectedPost['SUP_PROF'])) {
    $sql = "DELETE FROM deploy where name='%s'";
    $arg = $protectedPost['SUP_PROF'];
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
}
if (!isset($protectedPost['ADD_FILE'])) {
    echo open_form($form_name, '', '', 'form-horizontal');
    $list_fields = array($l->g(283) => 'purpose',
        $l->g(49) => 'name',
        'SUP' => 'name'
    );
    $list_col_cant_del = $list_fields;
    $default_fields = $list_fields;

    $sql = "select '%s' as purpose,%s from deploy where name != 'label'";
    $tab_options['ARG_SQL'] = array($l->g(370), 'name');
    $tab_options['LIEN_LBL'][$l->g(49)] = 'index.php?' . PAG_INDEX . '=' . $pages_refs['ms_view_file'] . '&prov=agent&no_header=1&value=';
    $tab_options['LIEN_CHAMP'][$l->g(49)] = 'name';
    $tab_options['LIEN_TYPE'][$l->g(49)] = 'POPUP';
    $tab_options['POPUP_SIZE'][$l->g(49)] = "width=900,height=600";
    printEntete($l->g(1245));
    echo "<br />";
    ajaxtab_entete_fixe($list_fields, $default_fields, $tab_options, $list_col_cant_del);
    echo "<input type=submit class='btn' name=ADD_FILE value='" . $l->g(1048) . "'>";
    echo close_form();
}

if (is_defined($protectedPost['ADD_FILE'])) {
    $form_name1 = "SEND_FILE";
    //search max_allowed_packet value on mysql conf
    $sql = "SHOW VARIABLES LIKE 'max_allowed_packet'";
    $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
    $value = mysqli_fetch_array($result);
    //pass oct to Mo
    $upload_max_filesize = $value['Value'] / 1048576;

    msg_info($l->g(2022) . ' ' . $valBumf . $l->g(1240) . "<br>" . $l->g(2106) . " " . $upload_max_filesize . $l->g(1240));
    echo open_form($form_name1, '', "enctype='multipart/form-data' onsubmit=\"return verif_file_format('file_upload');\"", 'form-horizontal');
    echo '<div class="row">';
    echo '<div class="col-md-12">';
    formGroup('file', 'file_upload', $l->g(1048), '', '', '', '', '');
    echo "<input name='GO' class='btn btn-success' id='GO' type='submit' value='" . $l->g(13) . "'>";
    echo "</div>";
    echo "</div>";
    echo close_form();
    echo "<br>";
}

if (AJAX) {
    ob_end_clean();
    tab_req($list_fields, $default_fields, $list_col_cant_del, $sql, $tab_options);
}
?>