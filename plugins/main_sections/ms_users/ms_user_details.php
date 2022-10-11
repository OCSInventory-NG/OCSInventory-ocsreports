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
if (!AJAX) {
	require_once 'views/users_views.php';

    echo "<div class='col col-md-2'>";
	show_users_left_menu('ms_users');
    echo "</div>";
	
	echo '<div class="col col-md-10">';
}

require_once 'require/function_users.php';

if (isset($protectedPost['Valid_modif'])) {
    $msg = add_user($protectedPost, get_profile_labels());
    if ($msg != $l->g(374)) {
        msg_error($msg);
    } else {
        msg_success($l->g(1186));
    }
}
echo open_form('my_account', '', '', 'form-horizontal');

admin_user($protectedGet['user_id']);

?>
    <div class="row">
        <div class="col-md-12">
            <input type="submit" name="Valid_modif" value="<?php echo $l->g(1363) ?>" class="btn btn-success">
            <input type="submit" name="Reset_modif" value="<?php echo $l->g(1364) ?>" class="btn btn-danger">
        </div>
    </div>
<?php

echo close_form();

if (!AJAX) {
    echo '</div>';
}

?>