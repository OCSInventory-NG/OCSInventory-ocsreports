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
	
	show_users_left_menu('ms_add_user');
	
	echo '<div class="right-content">';
}

require_once 'require/function_users.php';

if (isset($protectedPost['Valid_modif'])) {
	$msg = add_user($_POST, get_profile_labels());
	if ($msg != $l->g(373)) {
		msg_error($msg);
	} else {
		msg_success($l->g(1186));
	}
}

admin_user();

if (!AJAX) {
	echo '</div>';
}

?>