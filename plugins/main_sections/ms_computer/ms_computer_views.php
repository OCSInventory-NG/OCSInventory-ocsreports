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

function show_computer_menu($computer_id) {
    global $protectedGet;
    $menu_serializer = new XMLMenuSerializer();
    $menu = $menu_serializer->unserialize(file_get_contents(CD_CONFIG_DIR . 'menu.xml'));

    $menu_renderer = new ComputerMenuRenderer($computer_id, $_SESSION['OCS']['url_service']);


    echo "<div class='left-menu col col-md-2'>";
    echo "<ul class='nav nav-pills nav-stacked navbar-left' data-spy='affix'>";


    foreach ($menu->getChildren() as $menu_elem) {

        $url = $menu_elem->getUrl();
        $label = $menu_renderer->getLabel($menu_elem);
        echo "<li ";
        if ($protectedGet['cat'] == explode('=',$url)[1]) {
            echo "class='active'";
        }
        echo " ><a href=' ".$menu_renderer->getUrl($menu_elem) ."'>" . $label . "</a></li>";
    }

	echo '</ul>';
	echo '</div>';
}

function show_computer_title($computer) {
    global $l;

    echo '<h3>';
    echo $computer->NAME;
    echo '</h3>';
}

function show_computer_actions($computer){
    global $protectedGet;
    global $l;

    $urls = $_SESSION['OCS']['url_service'];

    echo '<div style="text-align: center"> ';

    if ($_SESSION['OCS']['profile']->getRestriction('EXPORT_XML', 'NO') == "NO") {
        echo ' <button class= "btn btn-action" onclick=\'location.href="index.php?' . PAG_INDEX . '=' . $urls->getUrl('ms_export_ocs') . '&no_header=1&systemid=' . $computer->ID . '";\' target="_blank">' . $l->g(1304) . '</button>';
    }
    echo '</h3>';
    echo "&nbsp;&nbsp;";

    if ($_SESSION['OCS']['profile']->getRestriction('WOL', 'NO') == "NO" && isset($protectedGet['cat']) && $protectedGet['cat'] == 'admin') {
        echo "<button class='btn btn-action' OnClick='confirme(\"\",\"WOL\",\"bandeau\",\"WOL\",\"" . $l->g(1283) . "\");'>WOL</button> ";
    }

    echo "&nbsp;&nbsp;";

    // archive btn -> if computer already archived : restore, else : archive
    if ($_SESSION['OCS']['profile']->getConfigValue('ARCHIVE_COMPUTERS') == "YES" && isset($protectedGet['cat']) && $protectedGet['cat'] == 'admin') {
        $archive = new ArchiveComputer();
        if (mysqli_num_rows($archive->isArchived($computer->ID)) != 0) {
            $archive_action = $l->g(1552);
        } else {
            $archive_action = $l->g(1551);
        }
        echo "<button class='btn btn-action' OnClick='confirme(\"\",\"". $archive_action ."\",\"bandeau\",\"ARCHIVE\",\"Do you want to ". strtolower($archive_action) ." this computer ?\");'>". strtoupper($archive_action)."</button> ";
    }
    echo "</div>";
}

function show_computer_summary($computer) {
    global $l;

    $urls = $_SESSION['OCS']['url_service'];

    $labels = array(
        'SYSTEM' => array(
            'USERID' => $l->g(24),
            'OSNAME' => $l->g(274),
            'OSVERSION' => $l->g(275),
            'ARCH' => $l->g(1247),
            'OSCOMMENTS' => $l->g(286),
            'DESCRIPTION' => $l->g(53),
            'WINCOMPANY' => $l->g(51),
            'WINOWNER' => $l->g(348),
            'WINPRODID' => $l->g(111),
            'WINPRODKEY' => $l->g(553),
            'VMTYPE' => $l->g(1267),
            'ASSET' => $l->g(2132),
        ),
        'NETWORK' => array(
            'WORKGROUP' => $l->g(33),
            'USERDOMAIN' => $l->g(557),
            'IPADDR' => $l->g(34),
            'NAME_RZ' => $l->g(304),
        ),
        'HARDWARE' => array(
            'SWAP' => $l->g(50),
            'MEMORY' => $l->g(26),
            'UUID' => $l->g(1268),

        ),
        'AGENT' => array(
            'USERAGENT' => $l->g(357),
            'LASTDATE' => $l->g(46),
            'LASTCOME' => $l->g(820),
        ),
    );

    $cat_labels = array(
        'SYSTEM' => $l->g(1387),
        'NETWORK' => $l->g(1388),
        'HARDWARE' => $l->g(1389),
        'AGENT' => $l->g(1390),
    );

    $link = array();

    foreach ($labels as $cat) {
        foreach ($cat as $key => $lbl) {
            $computer_info = addslashes($computer->$key);
            if ($key == "MEMORY") {
                $sqlMem = "SELECT SUM(capacity) AS 'capa' FROM memories WHERE hardware_id=%s";
                $argMem = $computer->ID;
                $resMem = mysql2_query_secure($sqlMem, $_SESSION['OCS']["readServer"], $argMem);
                $valMem = mysqli_fetch_array($resMem);

                if ($valMem["capa"] > 0) {
                    $memory = $valMem["capa"];
                } else {
                    $memory = $computer_info;
                }
                $data[$key] = $memory;
            } elseif ($key == "LASTDATE" || $key == "LASTCOME") {
                $data[$key] = dateTimeFromMysql($computer_info);
            } elseif ($key == "NAME_RZ") {
                $data[$key] = "";
                $data_RZ = subnet_name($computer->ID);

                if($data_RZ != null) {
                    $nb_val = count($data_RZ);
                }

                if ($nb_val == 1) {
                    $data[$key] = $data_RZ[0];
                } elseif (isset($data_RZ)) {
                    foreach ($data_RZ as $index => $value) {
                        $data[$key] .= $index . " => " . $value . "<br>";
                    }
                }
            } elseif ($key == "VMTYPE" && $computer->UUID != '') {
                $sqlVM = "select vm.hardware_id,vm.vmtype, h.name from virtualmachines vm left join hardware h on vm.hardware_id=h.id where vm.uuid='%s' order by h.name DESC";
                $argVM = $computer->UUID;
                $resVM = mysql2_query_secure($sqlVM, $_SESSION['OCS']["readServer"], $argVM);
                $valVM = mysqli_fetch_array($resVM);
                $data[$key] = $valVM['vmtype'];
                $link_vm = "<a href='index.php?" . PAG_INDEX . "=" . $urls->getUrl('ms_computer') . "&head=1&systemid=" . $valVM['hardware_id'] . "'  target='_blank'><font color=red>" . $valVM['name'] . "</font></a>";
                $link[$key] = true;

                if ($data[$key] != '') {
                    msg_info($l->g(1266) . "<br>" . $l->g(1269) . ': ' . $link_vm);
                }
            } elseif ($key == "IPADDR" && $_SESSION['OCS']['profile']->getRestriction('WOL', 'NO') == "NO") {
                $data[$key] = $computer->$key;
                $link[$key] = true;
            } elseif ($computer_info != '') {
                $data[$key] = $computer_info;
            } elseif ($key == "ASSET") {
                $sqlAsset = "SELECT CATEGORY_NAME FROM assets_categories LEFT JOIN hardware AS h ON h.CATEGORY_ID = assets_categories.ID WHERE h.ID = %s";
                $argAsset = array($computer->ID);
                $resAsset = mysql2_query_secure($sqlAsset, $_SESSION['OCS']["readServer"], $argAsset);
                $asset = mysqli_fetch_array($resAsset);
                $data[$key] = $asset['CATEGORY_NAME'];
            }
        }
    }

    echo open_form("bandeau", '', '', 'form-horizonal');

    show_summary($data, $labels, $cat_labels, $link);
    echo "<input type='hidden' id='WOL' name='WOL' value=''>";
    echo "<input type='hidden' id='ARCHIVE' name='ARCHIVE' value=''>";

    echo close_form();
}

function show_summary($data, $labels, $cat_labels, $links = array()) {

    $nb_col = 2;
    $i = 0;

    foreach ($labels as $cat_key => $cat) {
        if ($i % $nb_col == 0) {
            echo '<div class="row">';
        }

        echo '<div class="col col-md-6">';
        echo '<h5>' . mb_strtoupper($cat_labels[$cat_key]) . '</h5>';

        foreach ($cat as $name => $label) {
            $value = $data[$name];

            if (trim($value) != '') {
                if (!array_key_exists($name, $links)) {
                    $value = strip_tags_array($value);
                }

                if ($name == "IPADDR") {
                    $value = preg_replace('/([x0-9])\//', '$1 / ', $value);
                }

                echo '<span class="summary-header text-left">' . $label . ' :</span>';
                echo '<span class="summary-value text-left">' . $value . '</span>';
            }
        }
        echo '</div>';

        $i++;
        if ($i % $nb_col == 0) {
            echo '</div>';
        }
    }

}

?>
