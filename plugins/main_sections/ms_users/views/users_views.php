<?php

function show_users_left_menu($activeMenu = null) {
    global $l;
    $urls = $_SESSION['OCS']['url_service'];

    $menu = array(
        'admin_user' => array($l->g(1400), 'ms_users'),
        'admin_profiles' => array($l->g(1401), 'ms_profiles'),
        'admin_add_user' => array($l->g(1403), 'ms_add_user'),
        'admin_add_profile' => array($l->g(1399), 'ms_add_profile'),
    );


    echo '<ul class="nav nav-pills nav-stacked navbar-left">';
    foreach ($menu as $key=>$value){

        echo "<li ";
        if ($activeMenu == $value[1]) {
            echo "class='active'";
        }
        echo " ><a href='?function=".$key."'>".$value[0]."</a></li>";
    }
    echo '</ul>';
}

?>