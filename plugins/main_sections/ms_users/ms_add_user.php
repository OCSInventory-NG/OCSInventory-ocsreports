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

if (!AJAX) {
	require_once 'views/users_views.php';

    echo "<div class='col col-md-2'>";
    show_users_left_menu('ms_add_user');
    echo "</div>";

	echo '<div class="col col-md-10">';
}

require_once 'require/function_users.php';

if (isset($protectedPost['Valid_modif'])) {
	$msg = add_user($_POST, get_profile_labels());
	if ($msg != $l->g(373)) {
		msg_error($msg);
	} else {
		msg_success($l->g(1186));
        unset($protectedPost);
	}
}

echo open_form('my_account', '', '', 'form-horizontal');

admin_user();

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