<script>
	function convert(bytes) {
	   if(bytes == 0) return '0 Byte';
	   var k = 1024;
	   var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
	   var i = Math.floor(Math.log(bytes) / Math.log(k));
	   return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
	}
	$(document).ready(function() {
		$.get( "libraries/linfo/index.php?out=jsonp", function( data ) {
		  $('#os').children(".summary-value").text(data.OS);
		  $('#ram').children(".summary-value").text(convert(data.RAM.total));
		  $('#freeram').children(".summary-value").text(convert(data.RAM.free));
		  $('#cpu').children(".summary-value").text(data.CPU[0].Model+" "+data.CPUArchitecture);
		  if(data.Kernel){
			$(".system").append('<div id="kernel"><div class="summary-header"><?php echo $l->g(1372)?>  :</div><div class="summary-value">'+data.Kernel+'</div></div>');
		  }
		  if(data.Distro){
				$(".system").append('<div id="distro"><div class="summary-header"><?php echo $l->g(1373)?>  :</div><div class="summary-value">'+data.Distro.name+'  '+data.Distro.version+'</div></div>');
			  }
		});
	});
</script>
<h3 class="h3">
<?php echo $l->g(1360)?>
</h3>
<table class="summary" style="width:80%;margin:auto;">
    <tbody>
        <tr class="summary-row">
        	<td class="summary-cell system">
            	<h4><?php echo $l->g(25)?></h4>
            	<div id="os"><div class="summary-header"><?php echo $l->g(274)?> :</div><div class="summary-value"></div></div>
            	<div id="ram"><div class="summary-header"><?php echo $l->g(1379)?> :</div><div class="summary-value"></div></div>
            	<div id="freeram"><div class="summary-header"><?php echo $l->g(1378)?> :</div><div class="summary-value"></div></div>
            	<div id="cpu"><div class="summary-header"><?php echo $l->g(1368)?> :</div><div class="summary-value"></div></div>  
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

