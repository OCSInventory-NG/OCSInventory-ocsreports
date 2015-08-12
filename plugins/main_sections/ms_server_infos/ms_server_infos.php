<h3 class="h3">
<?php echo $l->g(1360)?>
</h3>
<table class="summary" style="width:80%;margin:auto;">
    <tbody>
        <tr class="summary-row">
        	<td class="summary-cell system">
            	<h4><?php echo $l->g(25)?></h4>
            	<div id="os"><div class="summary-header"><?php echo $l->g(274)?> :</div><div class="summary-value"><?php
					$os = php_uname("s");
					echo ($os == "Linux" ? $os . ' ' . php_uname('m') : $os);
					echo '</div></div><div id="kernel"><div class="summary-header">' . $l->g($os == "Linux" ? 1372 : 277).
						' :</div><div class="summary-value">' . php_uname("r");
					$meminfo = @file_get_contents('/proc/meminfo');
					if ($meminfo && preg_match("/MemTotal: *([0-9]*)/", $meminfo, $res)) {
						$res = sprintf("%.2f Gio", intval($res[1]) / 1024 / 1024);
						echo '</div></div><div id="ram"><div class="summary-header">' . $l->g(1379).
							' :</div><div class="summary-value">' . $res;
					}
					if ($meminfo && preg_match("/MemAvailable: *([0-9]*)/", $meminfo, $res)) {
						$res = sprintf("%.2f Gio", intval($res[1]) / 1024 / 1024);
						echo '</div></div><div id="freeram"><div class="summary-header">' . $l->g(1378).
							' :</div><div class="summary-value">' . $res;
					}
					$cpuinfo = @file_get_contents('/proc/cpuinfo');
					if ($cpuinfo && preg_match("/model name(.*): (.*)\n/", $cpuinfo, $res)) {
						echo '</div></div><div id="cpu"><div class="summary-header">' . $l->g(1368).
							' :</div><div class="summary-value">' . $res[2];
					}
					// TODO: other distro
					$distro = false;
					foreach(array('/etc/debian_version', '/etc/redhat-release', '/etc/SuSE-release') as $fic) {
						if (file_exists($fic)) {
							$distro = file_get_contents($fic);
						}
					}
					if ($distro) {
						echo '</div></div><div id="distro"><div class="summary-header">' . $l->g(1373).
							' :</div><div class="summary-value">' . $distro;
					}
            	?></div></div>
        	</td>
        	<td class="summary-cell">
            	<h4><?php echo $l->g(20)?></h4>
            	<div id="phpversion"><div class="summary-header"><?php echo $l->g(1369)?> :</div><div class="summary-value"><?php
					echo PHP_VERSION . ' (' . PHP_SAPI . ')';
				?></div></div>
            	<div id="serverversion"><div class="summary-header"><?php echo $l->g(1370)?> :</div><div class="summary-value"><?php
					echo $_SERVER['SERVER_SOFTWARE'];
				?></div></div>
            	<div id="mysqlversion"><div class="summary-header"><?php echo $l->g(1371)?> :</div><div class="summary-value"><?php
					$sql = "SELECT @@sql_mode as mode, @@version AS vers, @@version_comment AS stype";
					$res = mysqli_query($_SESSION['OCS']["readServer"], $sql);
					$info = mysqli_fetch_object($res);
					echo $info->stype . ' version '. $info->vers;
            	?></div></div>
        	</td>
        </tr>	
        <tr class="summary-row">
			<td class="summary-cell">
            	<h4><?php echo $l->g(1367)?></h4>
            	<div id="ip"><div class="summary-header">IP :</div><div class="summary-value"><?php echo $_SERVER['REMOTE_ADDR']; ?></div></div>
 			</td>
        </tr>
    </tbody>
</table>

