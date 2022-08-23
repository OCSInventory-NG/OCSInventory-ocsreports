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
require('require/function_telediff.php');

if (isset($protectedGet["actgrp"])) {
    //this id is it a group?
    $reqGroups = "SELECT h.id id
					  FROM hardware h
					  WHERE h.deviceid='_SYSTEMGROUP_' ";
    //If you hav'nt permission => see only visible groups
    if (!($_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES")) {
        $reqGroups .= " and h.workgroup = 'GROUP_4_ALL'";
    }
    $resGroups = mysql2_query_secure($reqGroups, $_SESSION['OCS']["readServer"]);
    $valGroups = mysqli_fetch_array($resGroups);
    if (isset($valGroups['id'])) {
        $reqDelete = "DELETE FROM groups_cache WHERE hardware_id=%s AND group_id=%s";

        if ($protectedGet["actgrp"] == 0) {
            $reqDelete .= " AND static<>0";
        }
        $argDelete = array($systemid, $protectedGet["grp"]);
        $reqInsert = "INSERT INTO groups_cache(hardware_id, group_id, static)
								VALUES (%s, %s, %s)";
        $argInsert = array($systemid, $protectedGet["grp"], $protectedGet["actgrp"]);
        mysql2_query_secure($reqDelete, $_SESSION['OCS']["writeServer"], $argDelete);
        if ($protectedGet["actgrp"] != 0) {
            mysql2_query_secure($reqInsert, $_SESSION['OCS']["writeServer"], $argInsert);
        }
    }
}

$queryDetails = "SELECT * FROM devices WHERE hardware_id=%s";
$argDetail = $systemid;
$resultDetails = mysql2_query_secure($queryDetails, $_SESSION['OCS']["readServer"], $argDetail);
$form_name = 'config_mach';

echo open_form($form_name, '', '', 'form-horizontal');
?>

<div class="row margin-top30">
    <div class="col-md-12">
        <?php
        if ($_SESSION['OCS']['profile']->getConfigValue('CONFIG') == "YES") {
            echo "<a href=\"index.php?" . PAG_INDEX . "=" . $pages_refs['ms_custom_param'] . "&head=1&idchecked=" . $systemid . "&origine=machine\" alt=". $l->g(2122) ." class='btn btn-success'>". $l->g(2122) ."</a>";
        }

        // if user has permission to add devices to groups (Manage groups perm), show the group selector
        if ($_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES") {
        ?>
    </div>
</div></br></br>
<div class="row">
    <div class="col-md-4 col-md-offset-2">
        <select name="groupcombo" id="groupcombo" class="form-control">

        <?php
        $hrefBase = "index.php?" . PAG_INDEX . "=" . $pages_refs['ms_computer'] . "&head=1&systemid=" . urlencode($systemid) . "&option=cd_configuration";

        $reqGroups = "SELECT h.name,h.id,h.workgroup
					  FROM hardware h,groups g
					  WHERE  g.hardware_id=h.id  and h.deviceid='_SYSTEMGROUP_'";
        if (!($_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES")) {
            $reqGroups .= " and workgroup = 'GROUP_4_ALL'";
        }
        $reqGroups .= " order by h.name";
        $resGroups = mysql2_query_secure($reqGroups, $_SESSION['OCS']["readServer"]);
        if ($resGroups) {
            while ($valGroups = mysqli_fetch_array($resGroups)) {
                echo "<option value='" . $valGroups["id"] . "'>" . $valGroups["name"] . "</option>";
            }
        }
        ?>
        </select>
    </div>
    <div class="col-md-4">
        <script>
            function url(id) {
                window.location="<?php echo $hrefBase; ?>&actgrp=1&grp=" + id.options[id.selectedIndex].value;
            }
        </script>
        <a class="btn btn-success" OnClick=url(document.getElementById("groupcombo")) ><?php echo $l->g(589) ?></a>
    </div>
</div></br></br>

<?php
}
while ($item = mysqli_fetch_array($resultDetails, MYSQLI_ASSOC)) {
    $optPerso[$item["NAME"]]["IVALUE"] = $item["IVALUE"];
    $optPerso[$item["NAME"]]["TVALUE"] = $item["TVALUE"];
}
$field_name = array('DOWNLOAD', 'DOWNLOAD_CYCLE_LATENCY', 'DOWNLOAD_PERIOD_LENGTH', 'DOWNLOAD_FRAG_LATENCY',
    'DOWNLOAD_PERIOD_LATENCY', 'DOWNLOAD_TIMEOUT', 'PROLOG_FREQ', 'SNMP');
$optdefault = look_config_default_values($field_name);

//IPDISCOVER

if (isset($optPerso["IPDISCOVER"])) {
    if ($optPerso["IPDISCOVER"]["IVALUE"] == 0) {
        $returnIP = $l->g(490);
    } else if ($optPerso["IPDISCOVER"]["IVALUE"] == 2) {
        $returnIP = $l->g(491) . " " . $optPerso["IPDISCOVER"]["TVALUE"];
    } else if ($optPerso["IPDISCOVER"]["IVALUE"] == 1) {
        $returnIP = $l->g(492) . " " . $optPerso["IPDISCOVER"]["TVALUE"];
    }
} else {
    $returnIP = $l->g(493);
}
optperso("IPDISCOVER", $l->g(489), "IPDISCOVER", $optPerso ?? '', '', $returnIP);

//FREQUENCY
if (isset($optPerso["FREQUENCY"])) {
    if ($optPerso["FREQUENCY"]["IVALUE"] == 0) {
        $returnFrequency = $l->g(485);
    } else if ($optPerso["FREQUENCY"]["IVALUE"] == -1) {
        $returnFrequency = $l->g(486);
    } else {
        $returnFrequency = $l->g(495) . " " . $optPerso["FREQUENCY"]["IVALUE"] . " " . $l->g(496);
    }
} else {
    $returnFrequency = $l->g(497);
}
optperso("FREQUENCY", $l->g(494), "FREQUENCY", $optPerso ?? '', '', $returnFrequency);

//DOWNLOAD_SWITCH
if (isset($optPerso["DOWNLOAD_SWITCH"])) {
    if ($optPerso["DOWNLOAD_SWITCH"]["IVALUE"] == 0) {
        $returnDL = $l->g(733);
    } else if ($optPerso["DOWNLOAD_SWITCH"]["IVALUE"] == 1) {
        $returnDL = $l->g(205);
    } else {
        $returnDL = "";
    }
} else {
    if ($optdefault['ivalue']["DOWNLOAD"] == 1) {
        $returnDL = $l->g(205);
    } else {
        $returnDL = $l->g(733);
    }
}
optperso("DOWNLOAD", $l->g(417), "DOWNLOAD", $optPerso ?? '', '', $returnDL);

//DOWNLOAD_CYCLE_LATENCY
optperso("DOWNLOAD_CYCLE_LATENCY", $l->g(720), "DOWNLOAD_CYCLE_LATENCY", $optPerso ?? '', $optdefault['ivalue']["DOWNLOAD_CYCLE_LATENCY"], $l->g(511));

//DOWNLOAD_FRAG_LATENCY
optperso("DOWNLOAD_FRAG_LATENCY", $l->g(721), "DOWNLOAD_FRAG_LATENCY", $optPerso ?? '', $optdefault['ivalue']["DOWNLOAD_FRAG_LATENCY"], $l->g(511));


//DOWNLOAD_PERIOD_LATENCY
optperso("DOWNLOAD_PERIOD_LATENCY", $l->g(722), "DOWNLOAD_PERIOD_LATENCY", $optPerso ?? '', $optdefault['ivalue']["DOWNLOAD_PERIOD_LATENCY"], $l->g(511));

//DOWNLOAD_PERIOD_LENGTH
optperso("DOWNLOAD_PERIOD_LENGTH", $l->g(723), "DOWNLOAD_PERIOD_LENGTH", $optPerso ?? '', $optdefault['ivalue']["DOWNLOAD_PERIOD_LENGTH"]);

//PROLOG_FREQ
optperso("PROLOG_FREQ", $l->g(724), "PROLOG_FREQ", $optPerso ?? '', $optdefault['ivalue']["PROLOG_FREQ"], $l->g(730));

//PROLOG_FREQ
optperso("DOWNLOAD_TIMEOUT", $l->g(424), "DOWNLOAD_TIMEOUT", $optPerso ?? '', $optdefault['ivalue']["DOWNLOAD_TIMEOUT"], $l->g(496));

//DOWNLOAD_SWITCH
optperso("SNMP_SWITCH", $l->g(1197), "SNMP_SWITCH", $optPerso ?? '', '', (isset($optPerso["SNMP_SWITCH"]["IVALUE"]) && $optPerso["SNMP_SWITCH"]["IVALUE"] == 1) ? $l->g(733) : $l->g(205));

//GROUPS
$sql_groups = "SELECT static, name, group_id,workgroup
				FROM groups_cache g, hardware h WHERE g.hardware_id=%s AND h.id=g.group_id";
$arg_groups = $systemid;
$resGroups = mysql2_query_secure($sql_groups, $_SESSION['OCS']["readServer"], $arg_groups);


if (mysqli_num_rows($resGroups) > 0) {
    while ($valGroups = mysqli_fetch_array($resGroups)) {

?>

<div class="row" xmlns="http://www.w3.org/1999/html">
    <div class="col-md-12">
        <p>
<?php
        if ($_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES" || $valGroups["workgroup"] == "GROUP_4_ALL") {
            echo $l->g(607)." <a href='index.php?" . PAG_INDEX . "=" . $pages_refs['ms_group_show'] . "&head=1&systemid=" . $valGroups["group_id"] . "' target='_blank'>" . $valGroups["name"] . "</a> (";
        } else {
            echo "<strong>" . $valGroups["name"] . "</strong>";
        }

        switch ($valGroups["static"]) {
            case 0: echo "<span class='text-success'>" . $l->g(81) . " " . $l->g(596) . "</span>";
                break;
            case 1: echo "<span class='text-info'>" . $l->g(610) . "</span>";
                break;
            case 2: echo "<span class='text-danger'>" . $l->g(597) . "</span>";
                break;
        }

        echo ")";
        echo "<br />";

        if ($_SESSION['OCS']['profile']->getConfigValue('GROUPS') == "YES" || $valGroups["workgroup"] == "GROUP_4_ALL") {
            $hrefBase = "index.php?" . PAG_INDEX . "=" . $pages_refs['ms_computer'] . "&head=1&systemid=" . urlencode($systemid) . "&option=cd_configuration&grp=" . $valGroups["group_id"];
            switch ($valGroups["static"]) {
                case 0: echo "<a href='$hrefBase&actgrp=1'>" . $l->g(598) . "</a> / <a href='$hrefBase&actgrp=2'>" . $l->g(600) . "</a>";
                    break;
                case 1: echo "<a href='$hrefBase&actgrp=0' alt='" . $l->g(818) . "'><span class='glyphicon glyphicon-remove delete-span delete-span-xs'></span></a>";
                    break;
                case 2: echo "<a href='$hrefBase&actgrp=1'>" . $l->g(598) . "</a> / <a href='$hrefBase&actgrp=0'>" . $l->g(41) . "</a>";
                    break;
            }
        }
        ?>
        </p>
    </div>
</div>

        <?php
    }
}
echo close_form();
?>
