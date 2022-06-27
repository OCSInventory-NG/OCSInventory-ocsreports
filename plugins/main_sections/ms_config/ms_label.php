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
//UPDATE/DELETE
if (isset($protectedPost['Valid_modif']) && $protectedPost['Valid_modif'] != "") {
    $sql = "DELETE FROM deploy WHERE name='%s'";
    $arg = "label";
    $msg = $l->g(261);

    if (trim($protectedPost['lbl']) != "") {
        $protectedPost["lbl"] = str_replace(array("\t", "\n", "\r"), array("", "", ""), $protectedPost["lbl"]);
        mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
        $sql = "INSERT INTO deploy VALUES('%s','%s')";
        $arg = array('label', $protectedPost["lbl"]);
        $msg = $l->g(260);
    }

    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
    msg_success($msg);
}
//Looking for the label
$reqL = "SELECT content FROM deploy WHERE name='%s'";
$arg = "label";
$resL = mysql2_query_secure($reqL, $_SESSION['OCS']["readServer"], $arg);
$val = mysqli_fetch_object($resL);
printEntete($l->g(263));
$form_name = 'admin_info';
echo open_form($form_name);
?>
<div class="row">
    <div class="col-md-4 col-md-offset-4">
        <label for="lbl"><?php echo $l->g(262); ?> :</label>
        <input type="text" class="form-control" name="lbl" value="<?php echo $val->content ?? ''; ?>">
    </div>
</div>
<br />
<div class="row">
    <div class="col-md-12">
        <input type="submit" name="Valid_modif" value="<?php echo $l->g(1363) ?>" class="btn btn-success">
        <input type="submit" name="Reset_modif" value="<?php echo $l->g(1364) ?>" class="btn btn-danger">
    </div>
</div>
<?php
echo close_form();
?>