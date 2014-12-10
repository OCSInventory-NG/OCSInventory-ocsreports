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
	
	show_users_left_menu();
	
	echo '<div class="right-content">';
}

require_once 'require/function_users.php';

admin_user($_GET['user_id']);

if (!AJAX) {
	echo '</div>';
}

?>