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
    $menu_serializer = new XMLMenuSerializer();
    $menu = $menu_serializer->unserialize(file_get_contents('config/computer/menu.xml'));

    $menu_renderer = new ComputerMenuRenderer($computer_id, $_SESSION['OCS']['url_service']);

    echo "<div class='left-menu col col-md-2'>";
    echo "<ul class='nav nav-pills nav-stacked navbar-left'>";

    foreach ($menu->getChildren() as $menu_elem) {
        $url = $menu_renderer->getUrl($menu_elem);
        $label = $menu_renderer->getLabel($menu_elem);
        echo "<li><a href='" . $url . "'>" . $label . "</a></li>";
    }

    echo '</ul>';
    echo '</div>';
}

function show_computer_title($computer) {
    global $l;

    $urls = $_SESSION['OCS']['url_service'];

    echo '<h3>';
    echo $computer->NAME;
    if ($_SESSION['OCS']['profile']->getRestriction('EXPORT_XML', 'NO') == "NO") {
        echo ' <small><a href="index.php?' . PAG_INDEX . '=' . $urls->getUrl('ms_export_ocs') . '&no_header=1&systemid=' . $computer->ID . '" target="_blank">' . $l->g(1304) . '</a></small>';
    }
    echo '</h3>';
}

function show_computer_summary($computer) {
    global $l;

    $urls = $_SESSION['OCS']['url_service'];

    $labels = array(
        'SYSTEM' => array(
            'USERID' => $l->g(24),
            'OSNAME' => $l->g(274),
            'OSVERSION' => $l->g(275),
            'OSCOMMENTS' => $l->g(286),
            'DESCRIPTION' => $l->g(53),
            'WINCOMPANY' => $l->g(51),
            'WINOWNER' => $l->g(348),
            'WINPRODID' => $l->g(111),
            'WINPRODKEY' => $l->g(553),
            'VMTYPE' => $l->g(1267),
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
            'ARCH' => $l->g(1247)
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

    foreach ($labels as $cat) {
        foreach ($cat as $key => $lbl) {
            if ($key == "MEMORY") {
                $sqlMem = "SELECT SUM(capacity) AS 'capa' FROM memories WHERE hardware_id=%s";
                $argMem = $computer->ID;
                $resMem = mysql2_query_secure($sqlMem, $_SESSION['OCS']["readServer"], $argMem);
                $valMem = mysqli_fetch_array($resMem);

                if ($valMem["capa"] > 0) {
                    $memory = $valMem["capa"];
                } else {
                    $memory = $computer->$key;
                }
                $data[$key] = $memory;
            } elseif ($key == "LASTDATE" || $key == "LASTCOME") {
                $data[$key] = dateTimeFromMysql($computer->$key);
            } elseif ($key == "NAME_RZ") {
                $data[$key] = "";
                $data_RZ = subnet_name($computer->ID);
                $nb_val = count($data_RZ);

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
                $data[$key] = $computer->$key . " <a href=# OnClick='confirme(\"\",\"WOL\",\"bandeau\",\"WOL\",\"" . $l->g(1283) . "\");'><i>WOL</i></a>";
                $link[$key] = true;
            } elseif ($computer->$key != '') {
                $data[$key] = $computer->$key;
            }
        }
    }

    echo open_form("bandeau", '', '', 'form-horizonal');

    show_summary($data, $labels, $cat_labels, $link);
    echo "<input type='hidden' id='WOL' name='WOL' value=''>";

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
