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

/*
 * Page de fonction communes aux détails d'une machine
 *
 */

//fonction de traitement de l'ID envoyé
function info($GET, $post_systemid) {
    global $l, $protectedPost;
    //send post
    if ($post_systemid != '') {
        $systemid = $protectedPost['systemid'];
    }
    //you can see computer's detail by deviceid
    if (isset($GET['deviceid']) && !isset($systemid)) {
        $querydeviceid = "SELECT ID FROM hardware WHERE deviceid='%s'";
        $argdevicedid = mb_strtoupper($GET['deviceid']);
        $resultdeviceid = mysql2_query_secure($querydeviceid, $_SESSION['OCS']["readServer"], $argdevicedid);
        $item = mysqli_fetch_object($resultdeviceid);
        $GET['systemid'] = $item->ID;
        //echo $GET['systemid'];
        if ($GET['systemid'] == "") {
            return $l->g(837);
        }
    }

    //you can see computer's detail by md5(deviceid)
    if (isset($GET['crypt'])) {
        $querydeviceid = "SELECT ID FROM hardware WHERE md5(deviceid)='%s'";
        $argdevicedid = ($GET['crypt']);
        $resultdeviceid = mysql2_query_secure($querydeviceid, $_SESSION['OCS']["readServer"], $argdevicedid);
        $item = mysqli_fetch_object($resultdeviceid);
        $GET['systemid'] = $item->ID;
        //echo $GET['systemid'];
        if ($GET['systemid'] == "") {
            return $l->g(837);
        }
    }

    //si le systemid de la machine existe
    if (isset($GET['systemid']) && !isset($systemid)) {
        $systemid = $GET['systemid'];
    }
    //problème sur l'id
    if (empty($systemid) || !is_numeric($systemid)) {
        return $l->g(837);
    }
    //recherche des infos de la machine
    $querydeviceid = "SELECT * FROM hardware h left join accountinfo a on a.hardware_id=h.id
						 WHERE h.id=" . $systemid . " ";
    if ($_SESSION['OCS']['profile']->getRestriction('GUI') == "YES"
            and isset($_SESSION['OCS']['mesmachines'])
            and $_SESSION['OCS']['mesmachines'] != ''
            and ! isset($GET['crypt'])) {
        $querydeviceid .= " and (" . $_SESSION['OCS']['mesmachines'] . " or a.tag is null or a.tag='')";
    }
    $resultdeviceid = mysqli_query($_SESSION['OCS']["readServer"], $querydeviceid) or mysqli_error($_SESSION['OCS']["readServer"]);
    $item = mysqli_fetch_object($resultdeviceid);
    if ($item->ID == "") {
        return $l->g(837);
    }
    return $item;
}

function subnet_name($systemid) {
    if (!is_numeric($systemid)) {
        return false;
    }
    $reqSub = "select NAME,NETID from subnet left join networks on networks.ipsubnet = subnet.netid
				where  networks.status='Up' and hardware_id=" . $systemid;
    $resSub = mysqli_query($_SESSION['OCS']["readServer"], $reqSub) or die(mysqli_error($_SESSION['OCS']["readServer"]));
    while ($valSub = mysqli_fetch_object($resSub)) {

        $returnVal[] = $valSub->NAME . "  (" . $valSub->NETID . ")";
    }
    return $returnVal ?? '';
}

function print_item_header($text) {
    echo '<h4 class="item-header">' . mb_strtoupper($text, "UTF-8") . '</h4>';
}

function bandeau($data, $lbl, $link = array()) {
    if (!is_array($link)) {
        $link = array();
    }

    $nb_col = 2;
    echo "<table ALIGN = 'Center' class='mlt_bordure' border=0 width:100%><tr><td align =center>";
    echo "		<table align=center border='0' width='100%'  ><tr>";
    $i = 0;
    foreach ($data as $name => $value) {
        if (trim($value) == '') {
            // Only if we have datas...
            continue;
        }

        if ($i == $nb_col) {
            echo "</tr><tr>";
            $i = 0;
        }
        if (!array_key_exists($name, $link)) {
            //$value=htmlentities($value,ENT_COMPAT,'UTF-8');
            $value = strip_tags_array($value);
        }

        if ($name == "IPADDR") {
            $value = preg_replace('/([x0-9])\//', '$1 / ', $value);
        }

        echo "<td>&nbsp;<b>" . $lbl[$name] . ": </b></td><td >" . $value . "</td>";
        $i++;
    }

    echo "</tr></table></td>";
    echo "</tr></table>";
}

?>