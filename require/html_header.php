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
/* * *****************************************************AFFICHAGE HTML DU HEADER****************************************** */
html_header();

//on affiche l'entete de la page
if (!isset($protectedGet["popup"])) {
//si unlock de l'interface
    if (isset($protectedPost['LOCK']) && $protectedPost['LOCK'] == 'RESET') {
        if (is_defined($_SESSION['OCS']["TRUE_mesmachines"])) {
            $_SESSION['OCS']["mesmachines"] = $_SESSION['OCS']["TRUE_mesmachines"];
        } else {
            unset($_SESSION['OCS']["mesmachines"]);
        }
        unset($_SESSION['OCS']["TRUE_mesmachines"]);
    }
}
?>

<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand header-logo" href="index.php?first">
                <img alt="OCS Inventory" src="image/banniere-ocs.png">
            </a>
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#ocs-navbar" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="ocs-navbar">
            <?php
            if ($_SESSION['OCS']['profile']) {
                if (!isset($protectedGet["popup"])) {
                    show_menu();
                }
            }
            ?>
            <?php
            if (isset($_SESSION['OCS']["loggeduser"]) && !isset($protectedGet["popup"])) :
                ?>
                <ul class="nav nav navbar-nav navbar-right">
                    <?php if (isset($_SESSION['OCS']["TRUE_mesmachines"])) : ?>
                        <li class="dropdown">
                            <a onclick="return pag('RESET', 'LOCK', 'log_out')">
                                <img src="image/cadena_op.png" alt="settings">
                                <?= $l->g(891) ?>
                            </a>
                        </li>
                    <?php endif;
                    ?>
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" >
                            <span class="glyphicon glyphicon-cog" id="menu_settings"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right">

                            <!-- DEBUG = 1011 -->
                            <li>
                                <a href="index.php?<?= PAG_INDEX ?>=<?= $pages_refs['ms_config_account'] ?>&head=1">
                                    <?= $l->g(1361) ?>
                                </a>
                            </li>
                            <?php
                            //pass in debug mode if plugin debug exist
                            if (isset($pages_refs['ms_debug'])) :
                                ?>
                                <?php if ((isset($_SESSION['OCS']['DEBUG']) && $_SESSION['OCS']['DEBUG'] == 'ON') || (isset($_SESSION['OCS']['MODE_LANGUAGE']) && $_SESSION['OCS']['MODE_LANGUAGE'] == "ON")) : ?>
                                    <li>
                                        <a href="index.php?<?= PAG_INDEX ?>=<?= $pages_refs['ms_debug'] ?>&head=1">
                                            <font color="red">
                                            <?= $l->g(1011) ?>
                                            </font>
                                        </a>
                                    </li>

                                    <?php if ($_SESSION['OCS']['DEBUG'] == 'ON') : ?>
                                        <li class="dropdown-header">
                                            CACHE:&nbsp;
                                            <font color="<?= ($_SESSION['OCS']["usecache"] ? 'green' : 'red') ?>">
                                            <?= ($_SESSION['OCS']["usecache"] ? 'ON' : 'OFF') ?>
                                            </font>
                                        </li>
                                        <li class='dropdown-header'>
                                            <span id='tps'>wait...</span>
                                        </li>
                                        <?php
                                    endif;
                                elseif (!isset($_SESSION['OCS']['DEBUG'])) :
                                    if (($_SESSION['OCS']['profile'] && $_SESSION['OCS']['profile']->hasPage('ms_debug')) || (is_array($_SESSION['OCS']['TRUE_PAGES']) && array_search('ms_debug', $_SESSION['OCS']['TRUE_PAGES']))) :
                                        ?>
                                        <li>
                                            <a href="index.php?<?= PAG_INDEX ?>=<?= $pages_refs['ms_debug'] ?>&head=1">
                                                <font color='green'>
                                                <?= $l->g(1011) ?>
                                                </font>
                                            </a>
                                        </li>
                                        <?php
                                    endif;
                                endif;
                            endif;
                            if (!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['HTTP_AUTH_USER'])) :
                                ?>
                                <li>
                                    <a onclick="return pag('ON', 'LOGOUT', 'log_out')">
                                        <?= $l->g(251) ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <?= open_form('log_out', 'index.php'); ?>
                                <input type='hidden' name='LOGOUT' id='LOGOUT' value=''>
                                <input type='hidden' name='LOCK' id='LOCK' value=''>
                                <?= close_form(); ?>
                            </li>
                        </ul>
                </ul>
            <?php endif;
            ?>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>

<?php
if (isset($_SESSION['OCS']["loggeduser"]) && $_SESSION['OCS']['profile']->getConfigValue('ALERTE_MSG') == 'YES') {
    /*     * ************************************************   ALERT MESSAGES ******************************************************* */
    $msg_header_error = array();
    $msg_header_error_sol = array();
//install.php already exist ?
    if (is_readable("install.php")) {
        $msg_header_error[] = $l->g(2020);
        $msg_header_error_sol[] = $l->g(2023);
    }
// OCS update available ? and warn update on yes ?
    $need_display = look_config_default_values("WARN_UPDATE");
    if ($need_display['ivalue']['WARN_UPDATE'] == '1') {
        $data = get_update_json();
        if (GUI_VER_SHOW < $data->version) {
            $txt = $l->g(2118) . " " . $data->version . " " . $l->g(2119);
            $txt .= "<br><a href=" . $data->download . ">" . $l->g(2120) . "</a>";

            msg_warning($txt, true);
        }
    }
//defaut user already exist on databases?
    try {
        $link_read = mysqli_connect(SERVER_READ, DFT_DB_CMPT, DFT_DB_PSWD);
        $link_write = mysqli_connect(SERVER_WRITE, DFT_DB_CMPT, DFT_DB_PSWD);
        mysqli_select_db($link_read, DB_NAME);
        mysqli_select_db($link_write, DB_NAME);
        $msg_header_error[] = $l->g(2024) . ' ' . DB_NAME;
        $msg_header_error_sol[] = $l->g(2025);
    } catch (Exception $e) {

    }


//admin user already exist on data base with defaut password?
    $reqOp = "SELECT id,user_group FROM operators WHERE id='%s' and passwd ='%s'";
    $arg_reqOp = array(DFT_GUI_CMPT, md5(DFT_GUI_PSWD));
    $resOp = mysql2_query_secure($reqOp, $_SESSION['OCS']["readServer"], $arg_reqOp);
    $rowOp = mysqli_fetch_object($resOp);
    if (isset($rowOp->id)) {
        $msg_header_error[] = $l->g(2026);
        $msg_header_error_sol[] = $l->g(2027);
    }
    /*     * *************************************************** WARNING MESSAGES **************************************************** */
    $msg_header_warning = array();
//Demo mode activate?
    if (DEMO) {
        $msg_header_warning[] = $l->g(2104) . " " . GUI_VER_SHOW . "<br>";
    }


    if ($_SESSION['OCS']['LOG_GUI'] == 1) {
//check if the GUI logs directory is writable
        $rep_ok = is_writable($_SESSION['OCS']['LOG_DIR']);
        if (!$rep_ok) {
            $msg_header_warning[] = $l->g(2021);
        }
    }

    if (version_compare(phpversion(), '5.3.7', '<')) {
        $msg_header_warning[] = $l->g(2113) . " " . phpversion() . " ) ";
    }

//Error are detected
    if ($msg_header_error != array()) {
        js_tooltip();
        $msg_tooltip = '';
        foreach ($msg_header_error as $poub => $values) {
            if (isset($msg_header_error_sol[$poub])) {
                $tooltip = tooltip($msg_header_error_sol[$poub]);
                $msg_tooltip .= "<div " . $tooltip . ">" . $values . "</div>";
            }
        }
        msg_error("<big>" . $l->g(1263) . "</big><br>" . $msg_tooltip, "top_msg_alert");
    }
//warning are detected
    if ($msg_header_warning != array()) {
        msg_warning(implode('<br>', $msg_header_warning), "top_msg_warning");
    }
}

if (isset($_SESSION['OCS']['TRUE_USER'])) {
    msg_info($_SESSION['OCS']['TRUE_USER'] . " " . $l->g(889) . " " . $_SESSION['OCS']["loggeduser"]);
}

if (isset($_SESSION['OCS']["TRUE_mesmachines"])) {
    msg_info($l->g(890));
}

echo "<div class='container-fluid'>";

if ($_SESSION['OCS']["mesmachines"] == "NOTAG" && !(array_search('ms_debug', $_SESSION['OCS']['TRUE_PAGES']['ms_debug']) && $protectedGet[PAG_INDEX] == $pages_refs['ms_debug'])) {
    if (isset($LIST_ERROR)) {
        $msg_error = $LIST_ERROR;
    } else {
        $msg_error = $l->g(893);
    }
    msg_error($msg_error);
    require_once(FOOTER_HTML);
    die();
}
?>