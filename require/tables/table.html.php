<div align="center">
	<script>tables.showTable(<?php echo $tableName ?>, <?php echo $csrfNumber ?>, <?php echo $url ?>, <?php echo $postData ?>, <?php echo $columns ?>);</script>
	
	<div class="tableContainer">
		<table id="<?php echo htmlspecialchars($table->getName()) ?>" class="table table-striped table-bordered table-condensed table-hover">
			<thead><tr><?php
				
				foreach($table->getColumns() as $name => $col) {
					echo "<th>".$col->getLabel()."</th>";
				}
				
			?></tr></thead>
		</table>
	</div>
</div>

<?php if ($_SESSION['OCS']['DEBUG'] == 'ON'): ?>
	<center>
		<div id="<?php echo htmlspecialchars($table->getName()) ?>_debug" class="alert alert-info" role="alert">
		<b>[DEBUG]TABLE REQUEST[DEBUG]</b>
		<hr>
		<b class="datatable_request" style="display:none;">LAST REQUEST:</b>
		<div></div>
		</div>
	</center>
<?php endif ?>