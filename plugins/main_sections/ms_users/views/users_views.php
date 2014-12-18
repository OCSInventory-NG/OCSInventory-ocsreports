<?php

function show_users_left_menu($activeMenu = null) {
	$urls = $_SESSION['OCS']['url_service'];
	
	// TODO translations
	$menu = new Menu(array(
		'users' => new MenuElem('Users', "?".PAG_INDEX."=".$urls->getUrl('ms_users')),
		'profiles' => new MenuElem('Profiles', "?".PAG_INDEX."=".$urls->getUrl('ms_profiles')),
		'add_user' => new MenuElem('Create user', "?".PAG_INDEX."=".$urls->getUrl('ms_add_user')),
		'add_profile' => new MenuElem('Create profile', "?".PAG_INDEX."=".$urls->getUrl('ms_add_profile')),
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