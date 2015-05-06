<?php

function show_users_left_menu($activeMenu = null) {
	global $l;
	$urls = $_SESSION['OCS']['url_service'];
	
	$menu = new Menu(array(
		'users' => new MenuElem($l->g(1400), "?".PAG_INDEX."=".$urls->getUrl('ms_users')),
		'profiles' => new MenuElem($l->g(1401), "?".PAG_INDEX."=".$urls->getUrl('ms_profiles')),
		'add_user' => new MenuElem($l->g(1403), "?".PAG_INDEX."=".$urls->getUrl('ms_add_user')),
		'add_profile' => new MenuElem($l->g(1399), "?".PAG_INDEX."=".$urls->getUrl('ms_add_profile')),
	));

	$menu_renderer = new MenuRenderer();
	
	if ($activeMenu) {
		$menu_renderer->setActiveLink("?".PAG_INDEX."=".$urls->getUrl($activeMenu));
	}
	
	echo '<div class="left-menu">';
	echo '<div class="navbar navbar-default">';
	echo $menu_renderer->render($menu);
	echo '</div>';
	echo '</div>';
}

?>