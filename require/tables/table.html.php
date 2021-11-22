<?php

?>
<div align="center">
    <script>tables.showTable(<?php 
echo $tableName ?>
?>, <?php 
echo $csrfNumber ?>
?>, <?php 
echo $url ?>
?>, <?php 
echo $postData ?>
?>, <?php 
echo $columns ?>
?>);</script>

    <div class="tableContainer">
        <table id="<?php 
echo htmlspecialchars($table->getName()) ?>
?>" class="table table-striped table-bordered table-condensed table-hover">
            <thead><tr><?php 
foreach ($table->getColumns() as $col) {
                        echo "<th>" . $col->getLabel() . "</th>";
                    }
?></tr></thead>
        </table>
    </div>
</div>

<input type="hidden" id="SUP_PROF" name="SUP_PROF" value="">
<input type="hidden" id="MODIF" name="MODIF" value="">
<input type="hidden" id="SELECT" name="SELECT" value="">
<input type="hidden" id="OTHER" name="OTHER" value="">
<input type="hidden" id="ACTIVE" name="ACTIVE" value="">
<input type="hidden" id="CONFIRM_CHECK" name="CONFIRM_CHECK" value="">
<input type="hidden" id="OTHER_BIS" name="OTHER_BIS" value="">
<input type="hidden" id="OTHER_TER" name="OTHER_TER" value="">

<?php 
if ($_SESSION['OCS']['DEBUG'] == 'ON'): ?>
    <center>
        <div id="<?php echo htmlspecialchars($table->getName()) ?>_debug" class="alert alert-info" role="alert">
            <b>[DEBUG]TABLE REQUEST[DEBUG]</b>
            <hr>
            <b class="datatable_request" style="display:none;">LAST REQUEST:</b>
            <div></div>
        </div>
    </center>
    <?php
 endif ?>
