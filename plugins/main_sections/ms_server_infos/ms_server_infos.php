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
printEnTete($l->g(1360));
?>
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h4><?php echo $l->g(25) ?></h4>
            <br />
            <div class="col-xs-4">
                <ul class="server-information-ul">
                    <li><?php echo $l->g(274) ?> :</li>
                    <li><?php echo $l->g(isset($os) && $os == "Linux" ? 1372 : 277) ?> :</li>
                    <li><?php echo $l->g(1379); ?> :<li>
                    <li><?php echo $l->g(1378); ?> :</li>
                    <li><?php echo $l->g(1368); ?> :</li>
                    <li><?php echo $l->g(1373); ?> :</li>
                </ul>
            </div>
            <div class="col-xs-8">
                <ul class="server-information-ul-li">
                    <li><?php
                        $os = php_uname("s");
                        echo ($os == "Linux" ? $os . ' ' . php_uname('m') : $os);
                        ?></li>
                    <li><?php echo php_uname("r"); ?></li>
                    <li><?php
                        $meminfo = @file_get_contents('/proc/meminfo');
                        if ($meminfo && preg_match("/MemTotal: *([0-9]*)/", $meminfo, $res)) {
                            $res = sprintf("%.d " . $l->g(1240), intval($res[1]) / 1024);
                            echo $res;
                        }
                        ?></li>
                    <li><?php
                        if ($meminfo && preg_match("/MemAvailable: *([0-9]*)/", $meminfo, $res)) {
                            $res = sprintf("%.d " . $l->g(1240), intval($res[1]) / 1024);
                            echo $res;
                        }
                        ?></li>
                    <li><?php
                        $cpuinfo = @file_get_contents('/proc/cpuinfo');
                        if ($cpuinfo && preg_match("/model name(.*): (.*)\n/", $cpuinfo, $res)) {
                            echo $res[2];
                        };
                        ?></li>
                    <?php
                    $distro = false;
                    foreach (array('/etc/debian_version', '/etc/redhat-release', '/etc/SuSE-release', '/etc/os-release') as $fic) {
                        if (file_exists($fic)) {
                            $distro = file_get_contents($fic);

                            if ($fic == '/etc/os-release') {
                                $distro = parse_ini_file($fic);
                                $distro = $distro['PRETTY_NAME'];
                            }
                        }
                    }
                    ?>
                    <li><?php echo $distro; ?></li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <h4><?php echo $l->g(20); ?></h4>
            <br />
            <div class="col-xs-4">
                <ul class="server-information-ul">
                    <li><?php echo $l->g(1369) ?> :</li>
                    <li><?php echo $l->g(1370) ?> :</li>
                    <li><?php echo $l->g(1371); ?> :</li>
                    <li><?php echo $l->g(277)?> OCSReports:</li>
                </ul>
            </div>
            <div class="col-xs-8">
                <ul class="server-information-ul-li">
                    <li><?php echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION.".".PHP_RELEASE_VERSION; ?></li>
                    <li><?php echo $_SERVER['SERVER_SOFTWARE']; ?></li>
                    <li><?php
                        $sql = "SELECT @@sql_mode as mode, @@version AS vers, @@version_comment AS stype";
                        $res = mysqli_query($_SESSION['OCS']["readServer"], $sql);
                        $info = mysqli_fetch_object($res);
                        echo $info->stype . ' version ' . $info->vers;
                        ?></li>
                    <li><?php echo GUI_VER_SHOW; ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <br />
            <h4><?php echo $l->g(1367); ?></h4>
            <br />
            <div class="col-xs-4">
                <ul class="server-information-ul">
                    <li>IP:</li>
                </ul>
            </div>
            <div class="col-xs-8">
                <ul class="server-information-ul-li">
                    <li><?php echo $_SERVER['SERVER_ADDR']; ?></li>
                </ul>
            </div>
        </div>
        <div class="col-md-6"></div>
    </div>
</div>

