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
$chiffres = "onKeyPress=\"return scanTouche(event,/[0-9]/)\" onkeydown='convertToUpper(this)'
		  onkeyup='convertToUpper(this)'
		  onblur='convertToUpper(this)'
		  onclick='convertToUpper(this)'";
$majuscule = "onKeyPress=\"return scanTouche(event,/[0-9 a-z A-Z]/)\" onkeydown='convertToUpper(this)'
		  onkeyup='convertToUpper(this)'
		  onblur='convertToUpper(this)'";
$sql_field = "onKeyPress=\"return scanTouche(event,/[0-9a-zA-Z_-]/)\" onkeydown='convertToUpper(this)'
		  onkeyup='convertToUpper(this)'
		  onblur='convertToUpper(this)'";

function printEnTete($ent) {
    echo "<h3 class='text-center'>$ent</h3>";
}

/**
 * Includes the javascript datetime picker
 */
function incPicker() {
    global $l;

    echo "<script type='text/javascript'>
	var MonthName=[";

    for ($mois = 527; $mois < 538; $mois++) {
        echo "\"" . $l->g($mois) . "\",";
    }
    echo "\"" . $l->g(538) . "\"";

    echo "];
	var WeekDayName=[";

    for ($jour = 539; $jour < 545; $jour++) {
        echo "\"" . $l->g($jour) . "\",";
    }
    echo "\"" . $l->g(545) . "\"";

    echo "];
	</script>
	<script type='text/javascript' src='js/bootstrap-datetimepicker.js'></script>";
}


function datePick($input, $checkOnClick = false) {
    global $l;
    $dateForm = $l->g(1270);
    if ($checkOnClick) {
        $cOn = ",'$checkOnClick'";
    }
    $ret = "<span class=\"glyphicon glyphicon-calendar\"></span>";
    return $ret . ("<script type=\"text/javascript\">
	      $(\".form_datetime\").datetimepicker({
	          format: \"".$dateForm."\",
	          autoclose: true,
	          todayBtn: true,
	          pickerPosition: \"bottom-left\"
	      });
	    </script>");
}

function replace_entity_xml($txt) {
    $cherche = array("&", "<", ">", "\"", "'");
    $replace = array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;");
    return str_replace($cherche, $replace, $txt);
}

function printEnTete_tab($ent) {
    echo "<br><table border=0 WIDTH = '62%' ALIGN = 'Center' CELLPADDING='5'>
	<tr height=40px bgcolor=#f2f2f2 align=center><td><b>" . $ent . "</b></td></tr></table>";
}

function xml_encode($txt) {
    $cherche = array("&", "<", ">", "\"", "'", "é", "è", "ô", "Î", "î", "à", "ç", "ê", "â");
    $replace = array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;", "&eacute;", "&egrave;", "&ocirc;", "&Icirc;", "&icirc;", "&agrave;", "&ccedil;", "&ecirc;", "&acirc;");
    return str_replace($cherche, $replace, $txt);
}

function xml_decode($txt) {
    $cherche = array("&acirc;", "&ecirc;", "&ccedil;", "&agrave;", "&lt;", "&gt;", "&quot;", "&apos;", "&eacute;", "&egrave;", "&ocirc;", "&Icirc;", "&icirc;", "&amp;");
    $replace = array("â", "ê", "ç", "à", "<", ">", "\"", "'", "é", "è", "ô", "Î", "î", "&");
    return str_replace($cherche, $replace, $txt);
}

//fonction qui permet d'afficher un tableau dynamique de données
/*
 * Columns : Each available column of the table
 * $columns = array {
 * 						'NAME'=>'h.name', ...
 * 						'Column name' => Database value,
 * 						 }
 * Default_fields : Default columns displayed
 * $default_fields= array{
 * 						'NAME'=>'NAME', ...
 * 						'Column name' => 'Column name',
 * 						}
 * Option : All the options for the specific table
 * $option= array{
 * 						'form_name'=> "show_all",....
 * 						'Option' => value,
 *
 * 						}
 * List_col_cant_del : All the columns that will always be displayed
 * $list_col_cant_del= array {
 * 						'NAME'=>'NAME', ...
 * 						'Column name' => 'Column name',
 * 						}
 */
function ajaxtab_entete_fixe($columns, $default_fields, $option = array(), $list_col_cant_del) {
    global $protectedPost, $l, $pages_refs;
	$layout = new Layout($option['table_name']);
    //Translated name of the column
    $lbl_column = array("ACTIONS" => $l->g(1381),
        "CHECK" => "<input type='checkbox' name='ALL' id='checkboxALL' Onclick='checkall();'>");
    if (!isset($tab_options['NO_NAME']['NAME'])) {
        $lbl_column["NAME"] = $l->g(23);
    }

    if (!empty($option['LBL'])) {
        $lbl_column = array_merge($lbl_column, $option['LBL']);
    }
    $columns_special = array("CHECK",
        "SUP",
        "NBRE",
        "NULL",
        "MODIF",
        "SELECT",
        "ZIP",
        "OTHER",
        "STAT",
        "ACTIVE",
        "MAC",
		"EDIT_DEPLOY",
		"SHOW_DETAILS",
		"ARCHIVER",
		"RESTORE",
		"AFFECT_AGAIN",
		"NEW_WINDOW"
    );
    //If the column selected are different from the default columns
    if (!empty($_COOKIE[$option['table_name'] . "_col"])) {
        $visible_col = json_decode($_COOKIE[$option['table_name'] . "_col"]);
    }

    $input = $columns;

    //Don't allow to hide columns that should not be hidden
    foreach ($list_col_cant_del as $key => $col_cant_del) {
        unset($input[$col_cant_del]);
        unset($input[$key]);
    }
    $list_col_can_del = $input;
    $columns_unique = array_unique($columns);
    if (isset($columns['CHECK'])) {
        $column_temp = $columns['CHECK'];
        unset($columns['CHECK']);
        $columns_temp['CHECK'] = $column_temp;
        $columns = $columns_temp + $columns;
    }
    $actions = array(
        "MODIF",
		"EDIT_DEPLOY",
        "SUP",
        "ZIP",
        "STAT",
		"ACTIVE",
		"SHOW_DETAILS",
		"ARCHIVER",
		"RESTORE",
		"AFFECT_AGAIN",
		"NEW_WINDOW"
    );
    $action_visible = false;
    foreach ($actions as $action) {
        if (isset($columns[$action])) {
            $action_visible = true;
            $columns['ACTIONS'] = "h.ID";
            break;
        }
    }
    //Set the ajax requested address
    if (isset($_SERVER['QUERY_STRING'])) {
        if (isset($option['computersectionrequest'])) {
            parse_str($_SERVER['QUERY_STRING'], $addressoption);
            unset($addressoption['all']);
            unset($addressoption['cat']);
            $addressoption['option'] = $option['computersectionrequest'];
            $address = "ajax.php?" . http_build_query($addressoption);
        } else {
            $address = isset($_SERVER['QUERY_STRING']) ? "ajax.php?" . $_SERVER['QUERY_STRING'] : "";
        }
    }
    $opt = false;
    ?>

    <div align=center>
        <div class="<?php echo $option['table_name']; ?>_top_settings" style="display:none;">
        </div>
        <?php

		if (!isset ($protectedPost['COL_SEARCH'])){
			$selected_col='ALL';
		} else {
			$selected_col = $protectedPost['COL_SEARCH'];
		}

        //Display the Column selector
        if (!empty($list_col_can_del)) {
            // Sort columns show / hide select by default
            ksort($list_col_can_del);

            $opt = true;
            ?>

            <div class="row">
                <div class="col col-md-4 col-xs-offset-0 col-md-offset-4">
                    <div class="form-group">
                        <label class="control-label col-sm-4" for="select_col<?php echo $option['table_name']; ?>"><?php echo $l->g(1349); ?> :</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="select_col<?php echo $option['table_name']; ?>" name="select_col<?php echo $option['table_name']; ?>">
                                <option value="default"><?php echo $l->g(6001); ?></option>
                                <?php
                                foreach ($list_col_can_del as $key => $col) {
                                    $name = explode('.', $col);
                                    $name = explode(' as ', end($name));
                                    $value = end($name);
                                    if (!empty($option['REPLACE_COLUMN_KEY'][$key])) {
                                        $value = $option['REPLACE_COLUMN_KEY'][$key];
                                    }
                                    if (array_key_exists($key, $lbl_column)) {
                                        echo "<option value='$value'>$lbl_column[$key]</option>";
                                    } else {
                                        echo "<option value='$value'>$key</option>";
                                    }
                                }
                                ?>
                            </select><br>

                        </div>
						<?php
						// if user is on multisearch page, do not display layouts buttons
						if (isset($option['table_name']) && $option['table_name'] != 'affich_multi_crit') {
							// layouts
							$cols = $layout->displayLayoutButtons($_SESSION['OCS']['loggeduser'], $protectedPost['layout'] ?? '----', $option['table_name']);

							$visible_col_tmp = null;
							if(isset($cols['VISIBLE_COL'])) {
								$visible_col_tmp = json_decode((string)$cols['VISIBLE_COL'] ?? null, true) ?? $visible_col ?? null;
							}

							if(!is_null(($visible_col_tmp))) {
								$indexCol = 0;
								foreach($columns as $key => $value) {
									if((in_array($key, $visible_col_tmp) || in_array($value, $visible_col_tmp)) && !in_array($indexCol, $visible_col ?? [])) {
										$visible_col[] = $indexCol;
									}
									$indexCol++;
								}
							}

							if(empty($visible_col)) {
								$visible_col = $visible_col_tmp;
							}
						}
						?>
                    </div>
                </div>
            </div>

            <?php
        }
        ?>


        <div id="<?php echo $option['table_name']; ?>_csv_download"
             style="display: none">
                 <?php
				 
                 //Display of the result count
                 if (!isset($option['no_download_result'])) {
                     echo "<div id='" . $option['table_name'] . "_csv_page'><label id='infopage_" . $option['table_name'] . "'></label> " . $l->g(90) . "<a href='index.php?" . PAG_INDEX . "=" . $pages_refs['ms_csv'] . "&no_header=1&tablename=" . $option['table_name'] ."'><small> (" . $l->g(183) . ")</small></a></div>";
                     echo "<div id='" . $option['table_name'] . "_csv_total'><label id='infototal_" . $option['table_name'] . "'></label> " . $l->g(90) . " <a href='index.php?" . PAG_INDEX . "=" . $pages_refs['ms_csv'] . "&no_header=1&tablename=" . $option['table_name'] . "&nolimit=true'><small>(" . $l->g(183) . ")</small></a></div>";
                 }
                 ?>
        </div>
        <?php
        echo "<a href='#' id='reset" . $option['table_name'] . "' onclick='delete_cookie(\"" . $option['table_name'] . "_col\");window.history.replaceState(null, null, window.location.href);window.location.reload();' style='display: none;' >" . $l->g(1380) . "</a><br>";
		?>

    </div>

    <script>
	 // Check all the checkboxes
        function checkall()
        {
            var table_id = "table#<?php echo $option['table_name']; ?>";
            $(table_id + " tbody tr td input:checkbox").each(function () {
                value = !$(this).attr('checked');
                document.getElementById($(this).attr('id')).checked = value;
            });
        }
        $(document).ready(function () {
            var table_name = "<?php echo $option['table_name']; ?>";
            var table_id = "table#<?php echo $option['table_name']; ?>";
            var form_name = "form#<?php echo $option['form_name']; ?>";
            var csrfid = "input#CSRF_<?php echo $_SESSION['OCS']['CSRFNUMBER']; ?>";

            /*
             Table Skeleton Creation.
             A Full documentation about DataTable constructor can be found at
             https://datatables.net/manual/index
             */
            var dom = '<<"row"lf ' +
                    '<"dataTables_processing" r>><"#' + table_name + '_settings" >' +
                    't<"row" <"col-md-2" i><"col-md-10" p>>>';

            var table = $(table_id).dataTable({
                "processing": true,
                "serverSide": true,
                "dom": dom,
                "ajax": {
                    'url': '<?php echo $address; ?>&no_header=true&no_footer=true',
                    "type": "POST",
                    //Error handling
                    "error": function (xhr, error, thrown) {
                        var statusErrorMap = {
                            '400': "<?php echo $l->g(1352); ?>",
                            '401': "<?php echo $l->g(1353); ?>",
                            '403': "<?php echo $l->g(1354); ?>",
                            '404': "<?php echo $l->g(1355); ?>",
                            '414': "<?php echo $l->g(1356); ?>",
                            '500': "<?php echo $l->g(1357); ?>",
                            '503': "<?php echo $l->g(1358); ?>"
                        };
                        if (statusErrorMap[xhr.status] != undefined) {
                            if (xhr.status == 401) {
                                window.location.reload();
                            }
                        }
                    },
                    //Set the $_POST request to the ajax file. d contains all datatables needed info
                    "data": function (d) {
                        if ($(table_id).width() < $(this).width()) {
                            $(table_id).width('100%');
                            $(".dataTables_scrollHeadInner").width('100%');
                            $(".dataTables_scrollHeadInner>table").width('100%');
                        }
                        //Add CSRF
                        d.CSRF_<?php echo $_SESSION['OCS']['CSRFNUMBER']; ?> = $(csrfid).val();
                        var visible = [];
                        if (document.getElementById('checkboxALL')) {
                            document.getElementById('checkboxALL').checked = false;
                        }
                        $.each(d.columns, function (index, value) {
                            var col = "." + this['data'];
                            if ($(table_id).DataTable().column(col).visible()) {
                                visible.push(index);
                            }
                        });
                        var ocs = [];
                        //Add the actual $_POST to the $_POST of the ajax request
						<?php
						foreach ($protectedPost as $key => $value) {
							if (!is_array($value)) {
								echo "d['" . preg_replace("/[^A-Za-z0-9\._]/", "", $key) . "'] = '" . $value . "'; \n";
							}
							if($key == "visible_col") {
								$visible_col = $value;
							}
						}
						?>
                        ocs.push($(form_name).serialize());
                        d.visible_col = visible;
                        d.ocs = ocs;
                    },
                    "dataSrc": function (json) {
                        if (json.customized) {
                            $("#reset" + table_name).show();
                        } else {
                            $("#reset" + table_name).hide();
                        }
                        if (json.debug) {
                            $("<p>" + json.debug + "</p><hr>").hide().prependTo('#' + table_name + '_debug div').fadeIn(1000);
                            $(".datatable_request").show();
                        }
                        return json.data;
                    },

                },

                //Save datatable state (page length, sort order, ...) in localStorage
                "stateSave": true,
                "stateDuration": 0,
                //Override search filter and page start after loading last datatable state
                "stateLoadParams": function (settings, data) {
                    data.search.search = "";
                    data.start = 0;
                },
				"conditionalPaging": true,
				"lengthMenu": [ 10, 25, 50, 100, 250, 500, 1000],
                //Column definition
                "columns": [
    <?php

	$index = 0;
	// Unset visible columns session var
    unset($_SESSION['OCS']['visible_col'][$option['table_name']]);

	//Visibility handling
    foreach ($columns as $key => $column) {
        if (!empty($visible_col)) {
            if ((in_array($index, $visible_col))) {
                // add visibles columns
                $_SESSION['OCS']['visible_col'][$option['table_name']][$key] = $column;
                $visible = 'true';
            } else {
                $visible = 'false';
            }
            $index ++;
        } else {
            if (( (in_array($key, $default_fields)) || (in_array($key, $list_col_cant_del)) || array_key_exists($key, $default_fields) || ($key == "ACTIONS" )) && !(in_array($key, $actions))) {
                // add visibles columns
                $_SESSION['OCS']['visible_col'][$option['table_name']][$key] = $column;
                $visible = 'true';
            } else {
                $visible = 'false';
            }
        }
        //Can the column be ordered
        if (in_array($key, $columns_special) || !empty($option['NO_TRI'][$key]) || $key == "ACTIONS") {
            $orderable = 'false';
        } else {
            $orderable = 'true';
        }
        //Cannot search in Delete or checkbox columns
        if (!array_key_exists($key, $columns_unique) || in_array($key, $columns_special)) {
            if (!empty($option['REPLACE_COLUMN_KEY'][$key])) {
                $key = $option['REPLACE_COLUMN_KEY'][$key];
            }

            echo "{'data' : '" . $key . "' , 'class':'" . $key . "',
'name':'" . $key . "', 'defaultContent': ' ',
'orderable':  " . $orderable . ",'searchable': false,
'visible' : " . $visible . "}, \n";
        } else {
            $name = explode('.', $column);
            $name = explode(' as ', end($name));
            $name = end($name);
            if (!empty($option['REPLACE_COLUMN_KEY'][$key])) {
                $name = $option['REPLACE_COLUMN_KEY'][$key];
            }

            echo "{ 'data' : '" . $name . "' , 'class':'" . $name . "',
'name':'" . $column . "', 'defaultContent': ' ',
'orderable':  " . $orderable . ", 'visible' : " . $visible . "},\n ";
        }

    }

    ?>
                ],
                //Translation
                "language": {
                    "sEmptyTable": "<?php echo $l->g(1334); ?>",
                    "sInfo": "<?php echo $l->g(1335); ?>",
                    "sInfoEmpty": "<?php echo $l->g(1336); ?>",
                    "sInfoFiltered": "<?php echo $l->g(1337); ?>",
                    "sInfoPostFix": "",
                    "sInfoThousands": "<?php echo $l->g(1350); ?>",
                    "decimal": "<?php echo $l->g(1351); ?>",
                    "sLengthMenu": "<?php echo $l->g(1338); ?>",
                    "sLoadingRecords": "<?php echo $l->g(1339); ?>",
                    "sProcessing": "<?php echo $l->g(1340); ?>",
                    "sSearch": "<?php echo $l->g(1341); ?>",
                    "sZeroRecords": "<?php echo $l->g(1342); ?>",
                    "oPaginate": {
                        "sFirst": "<?php echo $l->g(1343); ?>",
                        "sLast": "<?php echo $l->g(1344); ?>",
                        "sNext": "<?php echo $l->g(1345); ?>",
                        "sPrevious": "<?php echo $l->g(1346); ?>",
                    },
                    "oAria": {
                        "sSortAscending": ": <?php echo $l->g(1347); ?>",
                        "sSortDescending": ": <?php echo $l->g(1348); ?>",
                    }
                },
                "scrollX": 'true'
            });

            //Column Show/Hide
            $("#select_col" + table_name).change(function () {
                var col = "." + $(this).val();
                $(table_id).DataTable().column(col).visible(!($(table_id).DataTable().column(col).visible()));
				$(table_id).DataTable().ajax.reload();
				$("#select_col" + table_name).val('default');
            });

            //$("<span id='" + table_name + "_settings_toggle' class='glyphicon glyphicon-chevron-down table_settings_toggle'></span>").hide().appendTo("#" + table_name + "_filter label");
            $("#" + table_name + "_settings").hide();
            $("." + table_name + "_top_settings").contents().appendTo("#" + table_name + "_settings");
            $("#" + table_name + "_settings").addClass('table_settings');
            $("body").on("click", "#" + table_name + "_settings_toggle", function () {
                $("#" + table_name + "_settings_toggle").toggleClass("glyphicon-chevron-up");
                $("#" + table_name + "_settings_toggle").toggleClass("glyphicon-chevron-down");
                $("#<?php echo $option['table_name']; ?>_settings").fadeToggle();

            });
    <?php if ($opt) { ?>
                $("#" + table_name + "_settings_toggle").show();
        <?php
    }
//Csv Export
    if (!isset($option['no_download_result'])) {
        ?>
                $(table_id).on('draw.dt', function () {
                    var start = $(table_id).DataTable().page.info().start + 1;
                    var end = $(table_id).DataTable().page.info().end;
                    var total = $(table_id).DataTable().page.info().recordsDisplay;
                    //Show one line only if results fit in one page
                    if (total == 0) {
                        $('#' + table_name + '_csv_download').hide();
                        $("#" + table_name + "_settings_toggle").hide();
                    } else {
                        if (end != total || start != 1) {
                            $('#' + table_name + '_csv_page').show();
                            $('#infopage_' + table_name).text(start + "-" + end);
                        } else {
                            $('#' + table_name + '_csv_page').hide();
                        }
                        $('#infototal_' + table_name).text(total);
                        $('#' + table_name + '_csv_download').show();
                        $("#" + table_name + "_settings_toggle").show();
                    }
                });
        <?php
    }
    ?>
        });

    </script>
    <?php
	$layout_visib = json_encode($layout->prepareInsert($visible_col ?? [], $protectedPost['columns'] ?? []) ?? null);
	$_SESSION['OCS']['layout_visib'] = $layout_visib;
	
	$archiveClass = "";

	if(isset($protectedPost["onglet"]) && $protectedPost["onglet"] == "ARCHIVE") {
		$archiveClass = "archive";
	}

    if (!empty($titre)) {
        printEnTete_tab($titre);
    }
    echo "<div class='tableContainer'>";
    echo "<table id='" . $option['table_name'] . "' width='100%' class='table table-striped table-condensed table-hover cell-border $archiveClass'><thead><tr>";
    //titre du tableau
    foreach ($columns as $k => $v) {
        if (array_key_exists($k, $lbl_column)) {
            echo "<th><font >" . $lbl_column[$k] . "</font></th>";
        } else {
            echo "<th><font >" . $k . "</font></th>";
        }
    }
    echo "</tr>
    </thead>";

    echo "</table></div>";
    echo "<input type='hidden' id='SUP_PROF' name='SUP_PROF' value=''>";
    echo "<input type='hidden' id='MODIF' name='MODIF' value=''>";
    echo "<input type='hidden' id='SELECT' name='SELECT' value=''>";
    echo "<input type='hidden' id='OTHER' name='OTHER' value=''>";
    echo "<input type='hidden' id='ACTIVE' name='ACTIVE' value=''>";
    echo "<input type='hidden' id='CONFIRM_CHECK' name='CONFIRM_CHECK' value=''>";
    echo "<input type='hidden' id='OTHER_BIS' name='OTHER_BIS' value=''>";
    echo "<input type='hidden' id='OTHER_TER' name='OTHER_TER' value=''>";
	echo "<input type='hidden' id='EDIT_DEPLOY' name='EDIT_DEPLOY' value=''>";
	echo "<input type='hidden' id='SHOW_DETAILS' name='SHOW_DETAILS' value=''>";
	echo "<input type='hidden' id='ARCHIVER' name='ARCHIVER' value=''>";
	echo "<input type='hidden' id='RESTORE' name='RESTORE' value=''>";
	echo "<input type='hidden' id='AFFECT_AGAIN' name='AFFECT_AGAIN' value=''>";
	echo "<input type='hidden' id='NEW_WINDOW' name='NEW_WINDOW' value=''>";
	
    if (isset($_SESSION['OCS']['DEBUG']) && $_SESSION['OCS']['DEBUG'] == 'ON') {
        ?><center>
            <div id="<?php echo $option['table_name']; ?>_debug" class="alert alert-info" role="alert">
                <b>[DEBUG]TABLE REQUEST[DEBUG]</b>
                <hr>
                <b class="datatable_request" style="display:none;">LAST REQUEST:</b>
                <div></div>
            </div>
        </center><?php
    }
    return true;
}

function tab_entete_fixe($entete_colonne, $data, $titre, $width, $lien = array(), $option = array()) {
    echo "<div align=center>";
    global $protectedGet, $l;
    if ($protectedGet['sens'] == "ASC") {
        $sens = "DESC";
    } else {
        $sens = "ASC";
    }

    if (isset($data)) {
        ?>
        <script>
            function changerCouleur(obj, state) {
                if (state == true) {
                    bcolor = obj.style.backgroundColor;
                    fcolor = obj.style.color;
                    obj.style.backgroundColor = '#FFDAB9';
                    obj.style.color = 'red';
                    return true;
                } else {
                    obj.style.backgroundColor = bcolor;
                    obj.style.color = fcolor;
                    return true;
                }
                return false;
            }
        </script>
        <?php
        if ($titre != "") {
            printEnTete_tab($titre);
        }
        echo "<div class='tableContainer' id='data' style=\"width:" . $width . "%;\"><table cellspacing='0' class='ta'><tr>";
        //titre du tableau
        $i = 1;

        foreach ($entete_colonne as $v) {
            if (in_array($v, $lien)) {
                echo "<th class='ta' >" . $v . "</th>";
            } else {
                echo "<th class='ta'><font size=1 align=center>" . $v . "</font></th>";
            }
            $i++;
        }
        echo "
    </tr>
    <tbody class='ta'>";

        $j = 0;
        //lignes du tableau
        foreach ($data as $v2) {
            ($j % 2 == 0 ? $color = "#f2f2f2" : $color = "#ffffff");
            echo "<tr class='ta' bgcolor='" . $color . "'  onMouseOver='changerCouleur(this, true);' onMouseOut='changerCouleur(this, false);'>";
            foreach ($v2 as $v) {
                if (isset($option['B'][$i])) {
                    $begin = "<b>";
                    $end = "</b>";
                } else {
                    $begin = "";
                    $end = "";
                }


                if ($v == "") {
                    $v = "&nbsp";
                }
                echo "<td class='ta' >" . $begin . $v . $end . "</td>";
            }
            $j++;
            echo "</tr><tr>";
        }
        echo "</tr></tbody></table></div></div>";
    } else {
        msg_warning($l->g(766));
        return false;
    }
    return true;
}

/*
 * fonction liée à tab_modif_values qui permet d'afficher le champ défini avec la fonction champsform
 * $name = nom du champ
 * $input_name = nom du champ récupéré dans le $protectedPost
 * $input_type = 0 : <input type='text'>
 * 				 1 : <textarea>
 * 				 2 : <select><option>
 * $input_reload = si un select doit effectuer un reload, on y met le nom du formulaire à reload
 *
 */
function show_modif($name, $input_name, $input_type, $input_reload = "", $configinput = array('MAXLENGTH' => 100, 'SIZE' => 20, 'JAVASCRIPT' => "", 'DEFAULT' => "YES", 'COLS' => 30, 'ROWS' => 5))
{
	global $protectedPost, $l, $pages_refs;

	if ($configinput == "") {
		$configinput = array('MAXLENGTH' => 100, 'SIZE' => 20, 'JAVASCRIPT' => "", 'DEFAULT' => "YES", 'COLS' => 30, 'ROWS' => 5);
	}

	//del stripslashes if $name is not an array
	if (!is_array($name)) {
		$name = htmlspecialchars($name, ENT_QUOTES);
	}

	// Switch input type
	switch($input_type) {
		// textarea
		case 1:
			return "<textarea name='" . $input_name . "' id='" . $input_name . "' cols='" . $configinput['COLS'] . "' rows='" . $configinput['ROWS'] . "'  class='down' >" . $name . "</textarea>";
		// select
		case 2:
			$champs = "<div class='form-group'>";
			echo "<div class='col col-sm-10 col-sm-offset-2'>";
			$champs .= "<select name='" . $input_name . "' id='" . $input_name . "' " . (isset($configinput['JAVASCRIPT']) ? $configinput['JAVASCRIPT'] : '');
			
			if ($input_reload != "") {
				$champs .= " onChange='document." . $input_reload . ".submit();'";
			} 

			$champs .= " class='down form-control' >";

			if (isset($configinput['DEFAULT']) and $configinput['DEFAULT'] == "YES") {
				$champs .= "<option value='' class='hi' ></option>";
			}
				
			$countHl = 0;

			if ($name != '') {
				natcasesort($name);
				foreach ($name as $key => $value) {
					$champs .= "<option value=\"" . $key . "\"";

					if (!empty($protectedPost[$input_name]) && $protectedPost[$input_name] == $key) {
						$champs .= " selected";
					}

					$champs .= ($countHl % 2 == 1 ? " class='hi'" : " class='down'") . " >" . $value . "</option>";
					$countHl++;
				}
			}
			return $champs . "</select></div></div>";
		// Default text
		default:
			return "<input type='text' name='" . $input_name . "' id='" . $input_name . "' SIZE='" . $configinput['SIZE'] . "' MAXLENGTH='" . $configinput['MAXLENGTH'] . "' value=\"" . $name . "\" class='form-control'\" " . $configinput['JAVASCRIPT'] . ">";
	}	
}

function tab_modif_values($field_labels, $fields, $hidden_fields, $options = array()) {
	global $l;

	$options = array_merge(array(
		'title' => null,
		'comment' => null,
		'button_name' => 'modif',
		'show_button' => true,
		'form_name' => 'CHANGE',
		'top_action' => null,
		'show_frame' => true
	), $options);

	if ($options['form_name'] != 'NO_FORM') {
		echo open_form($options['form_name'], '', '', 'form-horizontal');
	}

	if ($options['show_frame']) {
		echo '<div class="form-frame form-frame-'.$options['form_name'].'">';
	}
	if ($options['title']) {
		echo '<h3>'.$options['title'].'</h3>';
	}

	if (is_array($field_labels)) {
	    foreach ($field_labels as $key => $label) {
	    	$field = $fields[$key];

			/**
			 * 0 = text
			 * 1 = textarea
			 * 2 = select
			 * 3 = hidden
			 * 4 = password
			 * 5 = checkbox
			 * 6 = text multiple
			 * 7 = hidden
			 * 8 = button
			 * 9 = link
			 * 10 = ?
			 **/


			//formGroup($field['INPUT_TYPE']);
	    	echo '<div class="field field-'.$field['INPUT_NAME'].'">';
	    	echo '<label>'.$label.'</label>';

	    	if ($field['COMMENT_BEFORE']) {
				echo '<span class="comment_before">'.$field['COMMENT_BEFORE'].'</span>';
	    	}

			echo show_modif($field['DEFAULT_VALUE'], $field['INPUT_NAME'], $field['INPUT_TYPE'], $field['RELOAD'], $field['CONFIG']);

	    	if ($field['COMMENT_AFTER']) {
				echo '<span class="comment_after">'.$field['COMMENT_AFTER'].'</span>';
	    	}

	    	echo '</div>';
		}
	} else {
		echo $field_labels;
	}

	if ($options['comment']) {
	 	echo '<div class="form-field"><i>'.$options['comment'].'</i></div>';
	}

	if ($options['show_button'] === 'BUTTON') {
		echo '<div class="form-buttons">';
		echo '<input type="submit" name="Valid_'.$options['button_name'].'" value="'.$l->g(13).'"/>';
		echo '</div>';
	} else if ($options['show_button']) {
		echo '<div class="form-buttons">';
		echo '<input type="submit" name="Valid_'.$options['button_name'].'" value="'.$l->g(1363).'"/>';
		echo '<input type="submit" name="Reset_'.$options['button_name'].'" value="'.$l->g(1364).'"/>';
		echo '</div>';
 	}

 	if ($options['show_frame']) {
	    echo "</div>";
 	}

    if ($hidden_fields) {
		foreach ($hidden_fields as $key => $value) {
			echo "<input type='hidden' name='".$key."' id='".$key."' value='".htmlspecialchars($value, ENT_QUOTES)."'>";
		}
    }

    if ($options['form_name'] != 'NO_FORM') {
		echo close_form();
    }
}

function show_field($name_field,$type_field,$value_field,$config=array()){
	global $protectedPost;
	foreach($name_field as $key=>$value){
		$tab_typ_champ[$key]['DEFAULT_VALUE']=$value_field[$key] ?? "";
		$tab_typ_champ[$key]['INPUT_NAME']=$name_field[$key] ?? "";
		$tab_typ_champ[$key]['INPUT_TYPE']=$type_field[$key] ?? "";


		if (!isset($config['ROWS'][$key]) or $config['ROWS'][$key] == '')
			$tab_typ_champ[$key]['CONFIG']['ROWS']=7;
		else
			$tab_typ_champ[$key]['CONFIG']['ROWS']=$config['ROWS'][$key];

		if (!isset($config['COLS'][$key]) or $config['COLS'][$key] == '')
			$tab_typ_champ[$key]['CONFIG']['COLS']=40;
		else
			$tab_typ_champ[$key]['CONFIG']['COLS']=$config['COLS'][$key];

		if (!isset($config['SIZE'][$key]) or $config['SIZE'][$key] == '')
			$tab_typ_champ[$key]['CONFIG']['SIZE']=50;
		else
			$tab_typ_champ[$key]['CONFIG']['SIZE']=$config['SIZE'][$key];

		if (!isset($config['MAXLENGTH'][$key]) or $config['MAXLENGTH'][$key] == '')
			$tab_typ_champ[$key]['CONFIG']['MAXLENGTH']=255;
		else
			$tab_typ_champ[$key]['CONFIG']['MAXLENGTH']=$config['MAXLENGTH'][$key];

		if (isset($config['COMMENT_AFTER'][$key]))	{
			$tab_typ_champ[$key]['COMMENT_AFTER']=	$config['COMMENT_AFTER'][$key];
		}


		if (isset($config['DDE'][$key]))	{
			$tab_typ_champ[$key]['CONFIG']['DDE']=$config['DDE'][$key];
		}

		if (isset($config['SELECT_DEFAULT'][$key]))	{
			$tab_typ_champ[$key]['CONFIG']['DEFAULT']=$config['SELECT_DEFAULT'][$key];
                        if($tab_typ_champ[$key]['CONFIG']['DEFAULT'] == 'YES'){
                            $tab_typ_champ[$key]['CONFIG']['SELECTED_VALUE'] = $config['SELECTED_VALUE'][$key];
                        }
		}
		if (isset($config['JAVASCRIPT'][$key]))	{
			$tab_typ_champ[$key]['CONFIG']['JAVASCRIPT']=$config['JAVASCRIPT'][$key];
		}
	}

	return $tab_typ_champ;
}

function filtre($tab_field,$form_name,$query,$arg='',$arg_count=''){
	global $protectedPost,$l;
// 	if ($protectedPost['RAZ_FILTRE'] == "RAZ")
// 	unset($protectedPost['FILTRE_VALUE'],$protectedPost['FILTRE']);
	if ($protectedPost['FILTRE_VALUE'] and $protectedPost['FILTRE']){
		$temp_query=explode("GROUP BY",$query);
		if ($temp_query[0] == $query)
		$temp_query=explode("group by",$query);

		if (substr_count(mb_strtoupper ($temp_query[0]), "WHERE")>0){
			$t_query=explode("WHERE",$temp_query[0]);
			if ($t_query[0] == $temp_query[0])
			$t_query=explode("where",$temp_query[0]);
			$temp_query[0]= $t_query[0]." WHERE (".$t_query[1].") and ";

		}else
		$temp_query[0].= " where ";
	if (substr($protectedPost['FILTRE'],0,2) == 'a.'){
		require_once('require/admininfo/Admininfo.php');
		$Admininfo = new Admininfo();

		$id_tag=explode('_',substr($protectedPost['FILTRE'],2));
		if (!isset($id_tag[1]))
			$tag=1;
		else
			$tag=$id_tag[1];
		$list_tag_id= $Admininfo->find_value_in_field($tag,$protectedPost['FILTRE_VALUE']);
	}
	if ($list_tag_id){
		$query_end= " in (".implode(',',$list_tag_id).")";
	}else{
		if ($arg == '')
			$query_end = " like '%".$protectedPost['FILTRE_VALUE']."%' ";
		else{
			$query_end = " like '%s' ";
			array_push($arg,'%' . $protectedPost['FILTRE_VALUE'] . '%');
			if (is_array($arg_count))
				array_push($arg_count,'%' . $protectedPost['FILTRE_VALUE'] . '%');
			else
				$arg_count[] = '%' . $protectedPost['FILTRE_VALUE'] . '%';
		}
	}
	$query= $temp_query[0].$protectedPost['FILTRE'].$query_end;
	if (isset($temp_query[1]))
		$query.="GROUP BY ".$temp_query[1];
	}
	$view=show_modif($tab_field,'FILTRE',2);
	$view.=show_modif($protectedPost['FILTRE_VALUE'],'FILTRE_VALUE',0);

	echo $l->g(883).": ".$view."<input type='submit' value='".$l->g(1109)."' name='SUB_FILTRE'><a href=# onclick='return pag(\"RAZ\",\"RAZ_FILTRE\",\"".$form_name."\");'><img src=image/delete-small.png></a></td></tr><tr><td align=center>";
	echo "<input type=hidden name='RAZ_FILTRE' id='RAZ_FILTRE' value=''>";
	return array('SQL'=>$query,'ARG'=>$arg,'ARG_COUNT'=>$arg_count);
}





function tab_list_error($data,$title)
{
	global $l;

	echo "<br>";
		echo "<table align='center' width='50%' border='0'  bgcolor='#C7D9F5' style='border: solid thin; border-color:#A1B1F9'>";
		echo "<tr><td colspan=20 align='center'><font color='RED'>".$title."</font></td></tr><tr>";
		$i=0;
		$j=0;
		while ($data[$i])
		{
			if ($j == 10)
			{
				echo "</tr><tr>";
				$j=0;
			}
			echo "<td align='center'>".$data[$i]."<td>";
			$i++;
			$j++;
		}
		echo "</td></tr></table>";

}

function nb_page($form_name = '',$taille_cadre='80',$bgcolor='#C7D9F5',$bordercolor='#9894B5',$table_name=''){
	global $protectedPost,$l;

	//catch nb result by page
	if (isset($_SESSION['OCS']['nb_tab'][$table_name]))
		$protectedPost["pcparpage"]=$_SESSION['OCS']['nb_tab'][$table_name];
	elseif(isset($_COOKIE[$table_name.'_nbpage']))
		$protectedPost["pcparpage"]=$_COOKIE[$table_name.'_nbpage'];


	if ($protectedPost['old_pcparpage'] != $protectedPost['pcparpage'])
		$protectedPost['page']=0;

	if (!(isset($protectedPost["pcparpage"])) or $protectedPost["pcparpage"] == ""){
		$protectedPost["pcparpage"]=PC4PAGE;

	}
	$html_show = "<table align=center width='80%' border='0' bgcolor=#f2f2f2>";
	//gestion d"une phrase d'alerte quand on utilise le filtre
	if (isset($protectedPost['FILTRE_VALUE']) and $protectedPost['FILTRE_VALUE'] != '' and $protectedPost['RAZ_FILTRE'] != 'RAZ')
		$html_show .= msg_warning($l->g(884));
	$html_show .= "<tr><td align=right>";

	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = "SHOW";
	if ($protectedPost['SHOW'] == 'SHOW')
		$html_show .= "<a href=# OnClick='pag(\"NOSHOW\",\"SHOW\",\"".$form_name."\");'><img src=image/no_show.png></a>";
	elseif ($protectedPost['SHOW'] != 'NEVER_SHOW')
		$html_show .= "<a href=# OnClick='pag(\"SHOW\",\"SHOW\",\"".$form_name."\");'><img src=image/show.png></a>";

	$html_show .= "</td></tr></table>";
	$html_show .= "<table align=center width='80%' border='0' bgcolor=#f2f2f2";

	if($protectedPost['SHOW'] == 'NOSHOW' or $protectedPost['SHOW'] == 'NEVER_SHOW')
		$html_show .= " style='display:none;'";

	$html_show .= "><tr><td align=center>";
	$html_show .= "<table cellspacing='5' width='".$taille_cadre."%' BORDER='0' ALIGN = 'Center' CELLPADDING='0' BGCOLOR='".$bgcolor."' BORDERCOLOR='".$bordercolor."'><tr><td align=center>";
	$machNmb = array(5=>5,10=>10,15=>15,20=>20,50=>50,100=>100,200=>200,1000000=>$l->g(215));
    $pcParPageHtml= $l->g(340).": ".show_modif($machNmb,'pcparpage',2,$form_name,array('DEFAULT'=>'NO'));
	$pcParPageHtml .=  "</td></tr></table>
	</td></tr><tr><td align=center>";
	$html_show .= $pcParPageHtml;


	if (isset($protectedPost["pcparpage"])){
		$deb_limit=$protectedPost['page']*$protectedPost["pcparpage"];
		$fin_limit=$deb_limit+$protectedPost["pcparpage"]-1;
	}

	$html_show .= "<input type='hidden' id='SHOW' name='SHOW' value='".$protectedPost['SHOW']."'>";
	if ($form_name != '')
	echo $html_show;

	return (array("BEGIN"=>$deb_limit,"END"=>$fin_limit));
}

function show_page($valCount,$form_name){
	global $protectedPost;
	if (isset($protectedPost["pcparpage"]) and $protectedPost["pcparpage"] != 0)
	$nbpage= ceil($valCount/$protectedPost["pcparpage"]);
	if ($nbpage >1){
	$up=$protectedPost['page']+1;
	$down=$protectedPost['page']-1;
	echo "<table align='center' width='99%' border='0' bgcolor=#f2f2f2>";
	echo "<tr><td align=center>";
	if ($protectedPost['page'] > 0)
	echo "<img src='image/prec24.png' OnClick='pag(\"".$down."\",\"page\",\"".$form_name."\")'> ";
	//if ($nbpage<10){
		$i=0;
		$deja="";
		while ($i<$nbpage){
			$point="";
			if ($protectedPost['page'] == $i){
				if ($i<$nbpage-10 and  $i>10  and $deja==""){
				$point=" ... ";
				$deja="ok";
				}
				if($i<$nbpage-10 and  $i>10){
					$point2=" ... ";
				}
				echo $point."<font color=red>".$i."</font> ".$point2;
			}
			elseif($i>$nbpage-10 or $i<10)
			echo "<a OnClick='pag(\"".$i."\",\"page\",\"".$form_name."\")'>".$i."</a> ";
			elseif ($i<$nbpage-10 and  $i>10 and $deja==""){
				echo " ... ";
				$deja="ok";
			}
			$i++;
		}

	if ($protectedPost['page']< $nbpage-1)
	echo "<img src='image/proch24.png' OnClick='pag(\"".$up."\",\"page\",\"".$form_name."\")'> ";

	}
	echo "</td></tr></table>";
	echo "<input type='hidden' id='page' name='page' value='".$protectedPost['page']."'>";
	echo "<input type='hidden' id='old_pcparpage' name='old_pcparpage' value='".$protectedPost['pcparpage']."'>";
}


function onglet($def_onglets,$form_name,$post_name,$ligne)
{
	global $protectedPost;
	/*	$protectedPost['onglet_soft']=stripslashes($protectedPost['onglet_soft']);
        $protectedPost['old_onglet_soft']=stripslashes($protectedPost['old_onglet_soft']);*/
	if (!isset($protectedPost["old_".$post_name]) || $protectedPost["old_".$post_name] != $protectedPost[$post_name]){
		$protectedPost['page']=0;
	}
	if (!isset($protectedPost[$post_name]) and is_array($def_onglets)){
		foreach ($def_onglets as $key=>$value){
			$protectedPost[$post_name]=$key;
			break;
		}
	}

	if ($def_onglets != ""){

		echo "<ul class=\"nav nav-pills\" style='display: inline-block' role=\"tablist\">";

		$current="";
		
		foreach($def_onglets as $key=>$value){
			echo "<li ";
			if (is_numeric($protectedPost[$post_name])){
				if ($protectedPost[$post_name] == $key or (!isset($protectedPost[$post_name]) and $current != 1)){
					echo "class='active'";
					$current=1;
				}
			}else{
				if (mysqli_real_escape_string($_SESSION['OCS']["readServer"],stripslashes($protectedPost[$post_name])) === mysqli_real_escape_string($_SESSION['OCS']["readServer"],stripslashes($key)) or (!isset($protectedPost[$post_name]) and $current != 1)){
					if($protectedPost[$post_name] == "ARCHIVE") {
						echo "class='active archive'";
					} else {
						echo "class='active'";
					}
					
					$current=1;
				}
			}

			echo "><a OnClick='pag(\"".htmlspecialchars($key, ENT_QUOTES)."\",\"".$post_name."\",\"".$form_name."\")'>".htmlspecialchars($value, ENT_QUOTES)."</a></li>";
		}
		echo "</ul>";
		echo "<input type='hidden' id='".$post_name."' name='".$post_name."' value='".$protectedPost[$post_name]."'>";
		echo "<input type='hidden' id='old_".$post_name."' name='old_".$post_name."' value='".$protectedPost[$post_name]."'>";
	}

}


function show_tabs($def_onglets,$form_name,$post_name, $onclick = false)
{
	global $protectedPost;

	if (isset($protectedPost["old_".$post_name]) && $protectedPost["old_".$post_name] != $protectedPost[$post_name]){
	$protectedPost['page']=0;
	}
	if (!isset($protectedPost[$post_name]) and is_array($def_onglets)){
		foreach ($def_onglets as $key=>$value){
			$protectedPost[$post_name]=$key;
			break;
		}
	}
	if ($def_onglets != ""){
	echo "<div class='col col-md-2'>";
	echo "<ul class='nav nav-pills nav-stacked navbar-left'>";
	$current="";
	$i=0;
	  foreach($def_onglets as $key=>$value){
	  	echo "<li ";
	  	if (is_numeric($protectedPost[$post_name])){
			if ($protectedPost[$post_name] == $key or (!isset($protectedPost[$post_name]) and $current != 1)){
			 echo "id='current' class='active'";
	 		 $current=1;
			}
	  	}else{
			if (mysqli_real_escape_string($_SESSION['OCS']["readServer"],stripslashes($protectedPost[$post_name])) === mysqli_real_escape_string($_SESSION['OCS']["readServer"],stripslashes($key)) or (!isset($protectedPost[$post_name]) and $current != 1)){
				 echo "id='current' class='active'";
	 			 $current=1;
			}
		}
		$clickjs = "OnClick='pag(\"".htmlspecialchars($key, ENT_QUOTES)."\",\"".$post_name."\",\"".$form_name."\")'";
	  	echo "><a ";
	  	echo ($onclick == true) ? $clickjs : '';
	  	echo " >".htmlspecialchars($value, ENT_QUOTES)."</a></li>";
	  $i++;
	  }
	echo "</ul>
	</div>";
	echo "<input type='hidden' id='".$post_name."' name='".$post_name."' value='".$protectedPost[$post_name]."'>";
	echo "<input type='hidden' id='old_".$post_name."' name='old_".$post_name."' value='".$protectedPost[$post_name]."'>";
	}


}





function gestion_col($entete,$data,$list_col_cant_del,$form_name,$tab_name,$list_fields,$default_fields,$id_form='form'){
	global $protectedPost,$l;
	//search in cookies columns values
	if (isset($_COOKIE[$tab_name]) and $_COOKIE[$tab_name] != '' and !isset($_SESSION['OCS']['col_tab'][$tab_name])){
		$col_tab=explode("///", $_COOKIE[$tab_name]);
		foreach ($col_tab as $value){
				$_SESSION['OCS']['col_tab'][$tab_name][$value]=$value;
		}
	}
	if (isset($protectedPost['SUP_COL']) and $protectedPost['SUP_COL'] != ""){
		unset($_SESSION['OCS']['col_tab'][$tab_name][$protectedPost['SUP_COL']]);
	}
	if ($protectedPost['restCol'.$tab_name]){
		$_SESSION['OCS']['col_tab'][$tab_name][$protectedPost['restCol'.$tab_name]]=$protectedPost['restCol'.$tab_name];
	}
	if ($protectedPost['RAZ'] != ""){
		unset($_SESSION['OCS']['col_tab'][$tab_name]);
		$_SESSION['OCS']['col_tab'][$tab_name]=$default_fields;
	}
	if (!isset($_SESSION['OCS']['col_tab'][$tab_name])){
		$_SESSION['OCS']['col_tab'][$tab_name]=$default_fields;
	}
	//add all fields we must have
	if (is_array($list_col_cant_del)){
		if (!is_array($_SESSION['OCS']['col_tab'][$tab_name]))
			$_SESSION['OCS']['col_tab'][$tab_name]=array();
		foreach ($list_col_cant_del as $key=>$value){
			if (!in_array($key,$_SESSION['OCS']['col_tab'][$tab_name])){
				$_SESSION['OCS']['col_tab'][$tab_name][$key]=$key;
			}
		}
	}

	if (is_array($entete)){
		if (!is_array($_SESSION['OCS']['col_tab'][$tab_name]))
			$_SESSION['OCS']['col_tab'][$tab_name]=array();
		foreach ($entete as $k=>$v){
			if (in_array($k,$_SESSION['OCS']['col_tab'][$tab_name])){
				$data_with_filter['entete'][$k]=$v;
				if (!isset($list_col_cant_del[$k]))
				 $data_with_filter['entete'][$k].="<a href=# onclick='return pag(\"".xml_encode($k)."\",\"SUP_COL\",\"".$id_form."\");'><img src=image/delete-small.png></a>";
			}
			else
			$list_rest[$k]=$v;


		}
	}
	if (is_array($data)){
		if (!is_array($_SESSION['OCS']['col_tab'][$tab_name]))
		$_SESSION['OCS']['col_tab'][$tab_name]=array();
		foreach ($data as $k=>$v){
			foreach ($v as $k2=>$v2){
				if (in_array($k2,$_SESSION['OCS']['col_tab'][$tab_name])){
					$data_with_filter['data'][$k][$k2]=$v2;
				}
			}

		}
	}
	if (is_array ($list_rest)){
		//$list_rest=lbl_column($list_rest);
		$select_restCol= $l->g(349).": ".show_modif($list_rest,'restCol'.$tab_name,2,$form_name);
		$select_restCol .=  "<a href=# OnClick='pag(\"".$tab_name."\",\"RAZ\",\"".$id_form."\");'><img src=image/delete-small.png></a></td></tr></table>"; //</td></tr><tr><td align=center>
		echo $select_restCol;
	}else
		echo "</td></tr></table>";
	echo "<input type='hidden' id='SUP_COL' name='SUP_COL' value=''>";
	echo "<input type='hidden' id='TABLE_NAME' name='TABLE_NAME' value='".$tab_name."'>";
	echo "<input type='hidden' id='RAZ' name='RAZ' value=''>";
	return( $data_with_filter);


}

function lbl_column($list_fields){
	//p($list_rest);
	require_once('maps.php');
	$return_fields=array();
	$return_default=array();
	foreach($list_fields as $table){
		if (isset($lbl_column[$table])){
			foreach($lbl_column[$table] as $field=>$lbl){
				//echo $field;
				if (isset($alias_table[$table])){
					$return_fields[$lbl]=$alias_table[$table].'.'.$field;
					if (isset($default_column[$table])){
						foreach($default_column[$table] as $default_field)
							$return_default[$lbl_column[$table][$default_field]]=$lbl_column[$table][$default_field];
					}else{
						msg_error($table.' DEFAULT VALUES NOT DEFINE IN MAPS.PHP');
						return false;
					}
				}else{
					msg_error($table.' ALIAS NOT DEFINE IN MAPS.PHP');
					return false;
				}

			}

		}else{
			msg_error($table.' NOT DEFINE IN MAPS.PHP');
			return false;
		}
	}
	ksort($return_fields);
	return array('FIELDS'=>$return_fields,'DEFAULT_FIELDS'=>$return_default);
}



//fonction qui permet de ne selectionner que certaines lignes du tableau
/*
 * Columns : Each available column of the table
* $queryDetails = string 'SELECT QUERY'
* Tab_options : All the options for the specific table
* $tab_options= array{
* 						'form_name'=> "show_all",....
* 						'Option' => value,
* 						}
*/
function ajaxfiltre($queryDetails,$tab_options){
	// Research field of the table
	if ($tab_options["search"] && $tab_options["search"]['value']!=""){
		$search = mysqli_real_escape_string($_SESSION['OCS']["readServer"],$tab_options["search"]['value']);
		$search = str_replace('%','%%',$search);
		$sqlword['WHERE']= preg_split("/where/i", $queryDetails);
		$sqlword['GROUPBY']= preg_split("/group by/i", $queryDetails);
		$sqlword['HAVING']= preg_split("/having/i", $queryDetails);
		$sqlword['ORDERBY']= preg_split("/order by/i", $queryDetails);
		foreach ($sqlword as $word=>$filter){
			if (!empty($filter['3'])){
				foreach ($filter as  $key => $row){
					if ($key == 3){

						$rang =0;
						foreach($tab_options['visible_col'] as $column){

							if(isset($tab_options['NO_SEARCH'][$tab_options['columns'][$column]['name']]) && $tab_options['columns'][$column]['name'] == $tab_options['NO_SEARCH'][$tab_options['columns'][$column]['name']]){
								$tab_options['columns'][$column]['searchable'] = false;
							}
							$searchable =  ($tab_options['columns'][$column]['searchable'] == "true") ? true : false;
							$name = preg_replace("/[^A-Za-z0-9\._]/", "", $tab_options['columns'][$column]['name']);
							if (!empty($tab_options["replace_query_arg"][$name])){
								$name= $tab_options["replace_query_arg"][$name];
							}
							if(is_array($tab_options['HAVING'])&&isset($tab_options['HAVING'][$name])){
								$searchable =true;
							}
							if (!empty($tab_options['NO_SEARCH'][$tab_options['columns'][$column]['name']])){
								$searchable = false;
							}
							
							if ($searchable){

								if (isset($tab_options['COL_SEARCH']) && $name != 'c' && $tab_options['COL_SEARCH'] == 'default') {
									if ($rang == 0){
										$filtertxt =  " HAVING (( ".$name." LIKE '%%".$search."%%' ) ";
									} else {
										$filtertxt .= " OR  ( ".$name." LIKE '%%".$search."%%' ) ";
									}
								} else if (empty($tab_options["COL_SEARCH"])) {
									if ($rang == 0){
										$filtertxt =  " HAVING (( ".$name." LIKE '%%".$search."%%' ) ";
									} else {
										$filtertxt .= " OR  ( ".$name." LIKE '%%".$search."%%' ) ";
									}
								}
								$rang++;
							}
						}

						if(!isset($tab_options['SPECIAL_SEARCH'])) {
							if ($word == "HAVING"){
								$queryDetails .= $filtertxt.") AND ".$row;
							} else {
								$queryDetails .= $filtertxt.")  ".$row;
							}
						}
					} else {
						if(!isset($tab_options['SPECIAL_SEARCH'])) {
							if($key>1){
								$queryDetails.=" ".$word." ".$row;
							}else{
								$queryDetails = $row;
							}
						}
					}
				}

				// Special process for ipdiscover query
				if(isset($tab_options['SPECIAL_SEARCH']) && $tab_options['SPECIAL_SEARCH'] == "IPD" && !is_null($filtertxt)) {
					$explodeIPDQuery = explode(") ipd ", $queryDetails);
					$queryDetails = $explodeIPDQuery[0].$filtertxt.")) ipd ".$explodeIPDQuery[1];
				}

				return $queryDetails;
			}
		}

		// Check if at least one of the column used in the query if full-text indexed
		foreach ($tab_options['visible_col'] as $column) {
			if (isset($tab_options['columns'][$column]['ft_index']) && $tab_options['columns'][$column]['ft_index'] == 'true') {
				// Find the correct place where to do the full-text search in the query
				if (count($sqlword['WHERE'])>1) {
						$ft_queryDetails1 = $sqlword['WHERE'][0];
						$ft_queryDetails2 = $sqlword['WHERE'][1];
						$ft_place = 'WHERE';
				} elseif (count($sqlword['GROUPBY'])>1) {
						$ft_queryDetails1 = $sqlword['GROUPBY'][0];
						$ft_queryDetails2 = $sqlword['GROUPBY'][1];
						$ft_place = 'GROUP BY';
				}
				break;
			}
		}

		// Add filtering criteria
		if (!empty($ft_place)) {

			// Search with at least 1 full-text indexed columns
			$index = 0;

			foreach ($tab_options['visible_col'] as $column) {
				$cname = $tab_options['columns'][$column]['name'];

				// Find out if the column is searchable
				if($tab_options['columns'][$column]['name'] == $tab_options['NO_SEARCH'][$tab_options['columns'][$column]['name']]){
					$tab_options['columns'][$column]['searchable'] = false;
				}
				$searchable =  ($tab_options['columns'][$column]['searchable'] == "true") ? true : false;

				// Find out if the column is searchable and is full-text indexed
				if ($searchable && $tab_options['columns'][$column]['ft_index'] == 'true') {
					// Add a '+' in front of, and a '*' at the end of, each work  when $search contains several words
					$search = trim($search);
					if (stripos($search, ' ') !== false) {
							$search1 = '+'.implode(' +', explode(' ',$search));
							$search1  = implode(explode(' ',$search1),'* ')."*";
					} else {
							$search1 = $search . "*";
					}
					// Append the search term
					if ($index==0) {
							$ft_queryDetails1 .= " WHERE (MATCH ($cname) AGAINST ('$search1' IN BOOLEAN MODE)";
					} else {
							$ft_queryDetails1 .= " OR MATCH ($cname) AGAINST ('$search1' IN BOOLEAN MODE)";
					}
					$index++;
				} elseif ($searchable && $tab_options['columns'][$column]['ft_index'] == 'false') {
					// Column is searchable but isn't full-text indexed
					if ($index==0) {
							$ft_queryDetails1 .= " WHERE ( $cname LIKE '%%$search%%')";
					} else {
							$ft_queryDetails1 .= " OR $cname LIKE '%%$search%%')";
					}
					$index++;
				}
			}

			// Close the full-text search clause if we added any
			if ($index>0) {
				if ($ft_place == 'WHERE') {
					$queryDetails = $ft_queryDetails1 . ") AND " . $ft_queryDetails2;
				} else {
					$queryDetails = $ft_queryDetails1 . ") GROUP BY " . $ft_queryDetails2;
				}
			}

			return $queryDetails;
		}

		// REQUEST SELECT FROM
		$queryDetails .= " HAVING ";
		$index =0;
		foreach($tab_options['visible_col'] as $column){
			$cname = preg_replace("/[^A-Za-z0-9\._]/", "", $tab_options['columns'][$column]['name']);
			$account_select = null;

			// Special treatment if accountinfo select type
			if (substr($cname,0,2) == 'a.'){
				require_once('require/admininfo/Admininfo.php');

				$Admininfo = new Admininfo();

				$id_tag=explode('_',substr($cname,2));
				if($id_tag[0] != 'TAG') {
					$info_tag = $Admininfo->find_info_accountinfo($id_tag[1]);
					if($info_tag[$id_tag[1]]['type'] == 2) {
						$info = $Admininfo->find_value_field('ACCOUNT_VALUE_' . $info_tag[$id_tag[1]]['name']);
						foreach($info as $key => $value) {
							if(strpos(strtolower($value), strtolower($search)) !== false) {
								$acc_select[$key] = $key;
							}
						}
						if(is_defined($acc_select) && $acc_select != null) {
							$account_select = implode(',', $acc_select);
						}
					}
				}
			}

			// (Cyrille: The following 2 tests are used at least 3 times in this file. Wouldn't it be a good time to create a function?)
			if(isset($tab_options['NO_SEARCH']) && ((isset($tab_options['columns'][$column]['name']) && isset($tab_options['NO_SEARCH'][$tab_options['columns'][$column]['name']])) && $tab_options['columns'][$column]['name'] == $tab_options['NO_SEARCH'][$tab_options['columns'][$column]['name']])){
				$tab_options['columns'][$column]['searchable'] = false;
			}
			$searchable =  ($tab_options['columns'][$column]['searchable'] == "true") ? true : false;

			// (Cyrille: What the hell is the purpose of this "HAVING" array?)
			if(isset($tab_options['HAVING'][$column]) && is_array($tab_options['HAVING'])){
				$searchable =true;
			}

			// if account info select -> change comparator
			if($account_select != null) {
				$search_arg = "IN (".$account_select.")";
			} else {
				$search_arg = "LIKE '%%".$search."%%'";
			}

			// If column is searchable and doesn't have a full-text index 
			if ($searchable && (empty($tab_options['columns'][$column]['ft_index']) || $tab_options['columns'][$column]['ft_index'] == 'false')) {
				if ($cname != 'c' && isset($tab_options['COL_SEARCH']) && $tab_options['COL_SEARCH'] == 'default') {
						if ($index == 0){
							$filter =  " (( ".$cname." ".$search_arg." ) ";
						} else {
							$filter .= " OR  ( ".$cname." ".$search_arg." ) ";
						}
				} else if (empty($tab_options["COL_SEARCH"])) {
						if ($index == 0){
							$filter =  " (( ".$cname." ".$search_arg." ) ";
						} else {
							$filter .= " OR  ( ".$cname." ".$search_arg." ) ";
						}
				}
				$index++;
			}
		}
		// Special process for computer details teledeploy query
		if(isset($tab_options['SPECIAL_SEARCH']) && $tab_options['SPECIAL_SEARCH'] == "COMP_DEPLOY" && !is_null($filter)) {
			$explodeQuery = explode("d.name='DOWNLOAD' and a.name != '' and pack_loc != '' AND d.hardware_id=%s", $queryDetails);
			$queryDetails = $explodeQuery[0]."d.name='DOWNLOAD' and a.name != '' and pack_loc != '' AND d.hardware_id=%s HAVING".$filter.")".$explodeQuery[1];
		}

		$queryDetails .= $filter.") ";
	}
	return $queryDetails;
}


//fonction qui retourne un string contenant le bloc généré ORDER BY de la requete
/*
* Tab_options : All the options for the specific table
* &$tab_options= array{
* 						'form_name'=> "show_all",....
* 						'Option' => value,
* 						}
*/
function ajaxsort(&$tab_options) {
	$tri = '';
	$tab_iplike = array('H.IPADDR','IPADDRESS','IP','IPADDR','IP_MIN','IP_MAX');

	if ($tab_options['columns'][$tab_options['order']['0']['column']]['orderable'] == "true") {
		// reset
		foreach ($tab_options['order'] as $index => $v ) {
			// get column name
			$name = $tab_options['columns'][$tab_options['order'][$index]['column']]['name'];
			// sanitize column name (keep only "xxxx.yyyy", "xxyy" or "xxxx as yyyy" format)
			if (preg_match('/([A-Za-z0-9_-]+\.[A-Za-z0-9_-]+|^[A-Za-z0-9_-]+$)/', $name, $cleanname) || preg_match('/(?<!\([^()])(?![^()]*\))(?<=\bas\s)(\w+)/i', $name, $cleanname)) {
				$cleanname = $cleanname[0];
			}

			if (!empty($tab_options["replace_query_arg"][$name]) && (preg_match('/([A-Za-z0-9_-]+\.[A-Za-z0-9_-]+|^[A-Za-z0-9_-]+$)/', $tab_options["replace_query_arg"][$name], $cleanreplace) || preg_match('/(?<!\([^()])(?![^()]*\))(?<=\bas\s)(\w+)/i', $tab_options["replace_query_arg"][$name], $cleanreplace))) {
				$cleanname = $cleanreplace[0];
			}

			if(isset($v['dir'])) {
				$v['dir'] = preg_replace("/([^A-Za-z])/", "", $v['dir']);
			}

			// field name is IP format alike
			if (in_array(mb_strtoupper($cleanname),$tab_iplike)) {
				$tri .= " INET_ATON(".$cleanname.") ".$v['dir'].", ";
			} else if(isset($tab_options['TRI']['DATE'][$cleanname])) {
				if(isset($tab_options['ARG_SQL'])) {
					$tri .= " STR_TO_DATE(%s,'%s') %s";
					$tab_options['ARG_SQL'][] = $cleanname;
					$tab_options['ARG_SQL'][] = $tab_options['TRI']['DATE'][$cleanname];
					$tab_options['ARG_SQL'][] = $v['dir'];
				} else {
					$tri .= " STR_TO_DATE(".$cleanname.",'".$tab_options['TRI']['DATE'][$cleanname]."') ".$v['dir'];
				}
			} else {
				if ( strpos($cleanname,".") === false ) {
					$tri .= "".$cleanname." ".$v['dir'].", ";
				} else {
					$tri .= $cleanname . " ".$v['dir'].", ";
				}
			}
		}

		$tri = rtrim($tri, ", ");
	}

	if($tri != "") {
		return " order by ".$tri;
	} else {
		return "";
	}
}

//fonction qui retourne un string contenant le bloc généré LIMIT de la requete
/*
* Tab_options : All the options for the specific table
* $tab_options= array{
* 						'form_name'=> "show_all",....
* 						'Option' => value,
* 						}
*/
function ajaxlimit($tab_options){
	if (isset($tab_options['start'])){
		// Remove all characters except number in start value
		$limit = " limit ".preg_replace("/[^0-9]/", "", $tab_options['start'])." , ";
	}else{
		$limit = " limit 0 , ";
	}
	if (isset($tab_options['length'])){
		// Remove all characters except number in length value
		$limit .= preg_replace("/[^0-9]/", "", $tab_options['length'])." ";
	}else{
		$limit .= "10 ";
	}
	return $limit;
}


//fonction qui met en forme les resultats
/*
* ResultDetails : Query return
* $resultDetails = mysqli_result
* $list_fields : Each available column of the table
* $list_fields = array {
* 						'NAME'=>'h.name', ...
* 						'Column name' => Database value,
* 						 }
* Tab_options : All the options for the specific table
* $tab_options= array{
* 						'form_name'=> "show_all",....
* 						'Option' => value,
* 						}
*/
function ajaxgestionresults($resultDetails,$list_fields,$tab_options){
	global $protectedPost,$l,$pages_refs;
	$form_name=$tab_options['form_name'];
	$_SESSION['OCS']['list_fields'][$tab_options['table_name']]=$list_fields;
	$_SESSION['OCS']['col_tab'][$tab_options['table_name']]= array_flip($list_fields);
	if(!empty($resultDetails->num_rows)){
		while($row = mysqli_fetch_assoc($resultDetails))
		{
			if (isset($tab_options['AS'])){
				foreach($tab_options['AS'] as $k=>$v){
					if($v!="SNAME"){
						$n = explode('.',$k);
						$n = end($n);
						$row[$n]= $row[$v];
					}
				}
			}
			$row_temp = $row;
			foreach($row as $rowKey=>$rowValue){
				$row[$rowKey]= isset($rowValue) ? htmlspecialchars($rowValue, ENT_QUOTES, "UTF-8") : '';
			}
			foreach($list_fields as $key=>$column){
				$name = explode('.',$column);
				$column = end($name);
				$value_of_field = $row[$column] ?? '';
				switch($key){
					case "CHECK":
						// condition below added to fix static grp visbility checkbox
						if (isset($tab_options['JAVA']['CHECK'])){
							$grp_name = array();
							// workaround to get grp name (matches anything btw > and <) 
							preg_match('/(?<=>)(.*?)(?=<)/', $row['NAME'], $grp_name);
							$javascript="OnClick='confirme(\"".htmlspecialchars($grp_name[0], ENT_QUOTES)."\",".$value_of_field.",\"".$form_name."\",\"CONFIRM_CHECK\",\"".htmlspecialchars($tab_options['JAVA']['CHECK']['QUESTION'], ENT_QUOTES)." \")'";
						}else{
							$javascript="";
						}

						if ($value_of_field!= '&nbsp;'){
							$row[$key] = "<input type='checkbox' name='check".$value_of_field."' id='check".$value_of_field."' ".$javascript." ".(isset($tab_options['check'.$value_of_field])? " checked ": "").">";
						}
						break;
					case "SUP":
						if ( $value_of_field!= '&nbsp;'){
							if (isset($tab_options['LBL_POPUP'][$key])) {
								if (isset($row[$tab_options['LBL_POPUP'][$key]])) {
									$lbl_msg=$l->g(640)." ".$row_temp[$tab_options['LBL_POPUP'][$key]];
								} else {
									$lbl_msg=$tab_options['LBL_POPUP'][$key];
								}
							} else {
								$lbl_msg=$l->g(640)." ".$value_of_field;
							}
							if($form_name == "admins" && $_SESSION['OCS']["loggeduser"] == htmlspecialchars($value_of_field, ENT_QUOTES)) {
								// Do nothing 
							} elseif ($form_name == "affich_save_query" && ($row['WHO_CAN_SEE'] == 'ALL' || $row['WHO_CAN_SEE'] == 'GROUP') && $_SESSION['OCS']['profile']->getConfigValue('MANAGE_SAVED_SEARCHES') == 'NO') {
								// again do nothing (do not show the delete action if user does not have the right to manage saved searches)
							} elseif ($form_name == "layouts" && ($row['VISIBILITY_SCOPE'] == 'ALL' || $row['VISIBILITY_SCOPE'] == 'GROUP') && $_SESSION['OCS']['profile']->getConfigValue('MANAGE_LAYOUTS') == 'NO') {
								// still nothing
							} else {
								$row[$key]="<a href=# OnClick='confirme(\"\",\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"".$form_name."\",\"SUP_PROF\",\"".htmlspecialchars($lbl_msg, ENT_QUOTES)."\");'><span class='glyphicon glyphicon-remove'></span></a>";
							}	
						}
						break;
					case "NAME":
						if ( !isset($tab_options['NO_NAME']['NAME'])){
							$link_computer="index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1";
							if (isset($row['ID']))
								$link_computer.="&systemid=".$row['ID'];
							elseif(isset($row['hardwareID']))
								$link_computer.="&systemid=".$row['hardwareID'];

							if (isset($row['MD5_DEVICEID']))
								$link_computer.= "&crypt=".$row['MD5_DEVICEID'];
							$row[$column]="<a href='".$link_computer."'>".$value_of_field."</a>";
						}
						break;
					case "GROUP_NAME":
						$row['NAME']="<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_group_show']."&head=1&systemid=".$row['ID']."'>".$value_of_field."</a>";
						break;
					case "NULL":
						$row[$key]="&nbsp";
						break;
					case "MODIF":
						if ($form_name == "layouts" && ($row['VISIBILITY_SCOPE'] == 'ALL' || $row['VISIBILITY_SCOPE'] == 'GROUP') && $_SESSION['OCS']['profile']->getConfigValue('MANAGE_LAYOUTS') == 'NO') {
							
						} else {
							$row[$key]="<a href=# OnClick='pag(\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"MODIF\",\"".$form_name."\");'><span class='glyphicon glyphicon-edit'></span></a>";
						}
						break;
					case "SELECT":
						$row[$key]="<a href=# OnClick='confirme(\"\",\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"".$form_name."\",\"SELECT\",\"".htmlspecialchars($tab_options['QUESTION']['SELECT'],ENT_QUOTES)."\");'><img src=image/prec16.png></a>";
						$lien = 'KO';
						break;
					case "OTHER":
						$row[$key]="<a href=#  OnClick='pag(\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"OTHER\",\"".$form_name."\");'><img src=image/red.png></a>";
						break;
					case "ZIP":
						$row[$key]="<a href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_tele_compress']."&no_header=1&timestamp=".$value_of_field."&type=".$tab_options['TYPE']['ZIP']."\"><span class='glyphicon glyphicon-download-alt' title='".$l->g(2120)."'></span></a>";
						break;
					case "STAT":
						$row[$key]="<a href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_tele_stats']."&head=1&stat=".$value_of_field."\"><span class='glyphicon glyphicon-stats' title='".$l->g(1251)."'></span></a>";
						break;
					case "ACTIVE":
						$row[$key]="<a href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_tele_popup_active']."&head=1&active=".$value_of_field."\"><span class='glyphicon glyphicon-ok' title='".$l->g(431)."'></span></a>";
						break;
					case "SHOWACTIVE":
						if(!empty($tab_options['SHOW_ONLY'][$key][$row['FILEID']])){
							$row[$column]="<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_tele_actives']."&head=1&timestamp=".$row['FILEID']."' >".$value_of_field."</a>";
						}
						break;
					case "MAC":
						$row[$key]=$value_of_field;
						require_once('require/ipdiscover/Ipdiscover.php');
						$oui = Ipdiscover::getMacOui($value_of_field);
						if('' !== $oui) $row[$key] .= ' (<small>'.$oui.'</small>)';
						break;
					case "MOD_TAGS":
						if ($value_of_field!= '&nbsp;'){
							$row[$key]="<center><a href='index.php?".PAG_INDEX."=".$pages_refs['ms_custom_perim']."&head=1&id=".$value_of_field."' ><span class='glyphicon glyphicon-edit'></span></a><center>";
						}
						break;
					case "SHOW_DETAILS":
						$row[$key]='<a href="#'.$value_of_field.'" data-toggle="modal" data-target="#'.$value_of_field.'" title="'.$l->g(9039).'"><span class="glyphicon glyphicon-search"></span></a>';
						break;
					case "ARCHIVER":
						if ($value_of_field!= '&nbsp;'){
							$lbl_msg=$l->g(1550)." ".$value_of_field;
							$row[$key]="&nbsp;<a href=# OnClick='confirme(\"\",\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"".$form_name."\",\"ARCHIVER\",\"".htmlspecialchars($lbl_msg, ENT_QUOTES)."\");'><span class='glyphicon glyphicon-save' title='".$l->g(1551)."'></span></a>";
						}	
						break;
					case "RESTORE":
						if ($value_of_field!= '&nbsp;'){
							$lbl_msg=$l->g(1553)." ".$value_of_field;
							$row[$key]="<a href=# OnClick='confirme(\"\",\"".htmlspecialchars($value_of_field, ENT_QUOTES)."\",\"".$form_name."\",\"RESTORE\",\"".htmlspecialchars($lbl_msg, ENT_QUOTES)."\");'><span class='glyphicon glyphicon-open' title='".$l->g(1552)."'></span></a>";
						}	
						break;
					case "AFFECT_AGAIN":
						if ($value_of_field != '&nbsp;'){
							$explode = explode(";", $value_of_field);
							if(is_defined($explode[1]) && !is_null($explode[1]) && (strstr($explode[1], 'ERR_') || strstr($explode[1], 'EXIT_CODE'))) {
								$lbl_msg=$l->g(9971);
								$row[$key]="&nbsp;<a href=# OnClick='confirme(\"\",\"".htmlspecialchars($explode[0], ENT_QUOTES)."\",\"".$form_name."\",\"AFFECT_AGAIN\",\"".htmlspecialchars($lbl_msg, ENT_QUOTES)."\");'><span class='glyphicon glyphicon-repeat' title='".$l->g(9972)."'></span></a>";
							}
						}
						break;
					case "NEW_WINDOW":
						if ($value_of_field!= '&nbsp;'){
							$explode = explode(";",$value_of_field);
							$row[$key]="&nbsp;<a href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_snmp_detail']."&head=1&type=".$explode[1]."&id=".$explode[0]."\"><span class='glyphicon glyphicon-new-window' title='".$l->g(9013)."'></span></a>";
						}
						break;
					default :
						if (substr($key,0,11) == "PERCENT_BAR"){
							//require_once("function_graphic.php");
							//echo percent_bar($value_of_field);
							$row[$column]="<CENTER>".percent_bar($value_of_field)."</CENTER>";
						}

						if (isset($tab_options['REPLACE_VALUE'][$key])){
 							$temp_val=explode('&&&',$value_of_field);
 							if (count($temp_val)==1) {
 								$temp_val=explode('&amp;&amp;&amp;',$value_of_field);
 							}
 							if (count($temp_val)!=1) {
 								$multi_value=0;
 								$temp_value_of_field="";
 								while (isset($temp_val[$multi_value])){
 									$temp_value_of_field.=$temp_val[$multi_value]."<br>";
 									$multi_value++;
 								}
 								$temp_value_of_field=substr($temp_value_of_field,0,-4);
 								$value_of_field=$temp_value_of_field;
 								$row[$column]=$value_of_field;
 							}
 							else {
 								$row[$column]=$tab_options['REPLACE_VALUE'][$key][$value_of_field] ?? null;
 							}
						}
						if(!empty($tab_options['VALUE'][$key])){
							if(!empty($tab_options['LIEN_CHAMP'][$key])){
								$value_of_field=$tab_options['VALUE'][$key][$row[$tab_options['LIEN_CHAMP'][$key]]] ?? '';
							}else{
								if(isset($tab_options['VALUE'][$key][$row['ID']])) {
								$row[$column] = $tab_options['VALUE'][$key][$row['ID']];
							}
						}
						}
						if(isset($tab_options['REPLACE_VALUE_ALL_TIME']) && !empty($tab_options['REPLACE_VALUE_ALL_TIME'][$key][$row[$tab_options['FIELD_REPLACE_VALUE_ALL_TIME']]])){
							$row[$column]=$tab_options['REPLACE_VALUE_ALL_TIME'][$key][$row[$tab_options['FIELD_REPLACE_VALUE_ALL_TIME']]];
						}
						if (!empty($tab_options['LIEN_LBL'][$key])){
							if(strpos($row[$tab_options['LIEN_CHAMP'][$key]], '+')){
								$row[$tab_options['LIEN_CHAMP'][$key]] = str_replace("+", "%2B", $row[$tab_options['LIEN_CHAMP'][$key]]);
							}
							$row[$column]= "<a href='".$tab_options['LIEN_LBL'][$key].$row[$tab_options['LIEN_CHAMP'][$key]]."'>".$value_of_field."</a>";
						}
						if (!empty($tab_options['REPLACE_COLUMN_KEY'][$key])){
							$row[$tab_options['REPLACE_COLUMN_KEY'][$key]]=$row[$column];
							unset($row[$column]);
						}

					}
				if(!empty($tab_options['COLOR'][$key])){
					$row[$column]= "<font color='".$tab_options['COLOR'][$key]."'>".($row[$column] ?? '')."</font>";
				}
				if(!empty($tab_options['SHOW_ONLY'][$key])){
					if(empty($tab_options['SHOW_ONLY'][$key][$value_of_field])&& empty($tab_options['EXIST'][$key])
									||(isset($tab_options['EXIST']) && reset($tab_options['SHOW_ONLY'][$key]) == $row[$tab_options['EXIST'][$key]])){
						$row[$key]="";
					}
				}

			}
			$actions = array(
				"MODIF",
				"EDIT_DEPLOY",
				"SUP",
				"ZIP",
				"STAT",
				"ACTIVE",
				"SHOW_DETAILS",
				"ARCHIVER",
				"RESTORE",
				"AFFECT_AGAIN",
				"NEW_WINDOW"
			);

			$row['ACTIONS'] = '';
			foreach($actions as $action){	
				if(isset($row[$action])) {
					$row['ACTIONS'].= " ".$row[$action];
				}
			}
			$rows[] = $row;
			
		}
	}else{
		$rows = 0;
	}
	return $rows;
}

//fonction qui ggere le retour de la requete Ajax
/*
* $list_fields : Each available column of the table
* $list_fields = array {
* 						'NAME'=>'h.name', ...
* 						'Column name' => Database value,
* 						 }
* Default_fields : Default columns displayed
* $default_fields= array{
* 						'NAME'=>'NAME', ...
* 						'Column name' => 'Column name',
* 						}
* List_col_cant_del : All the columns that will always be displayed
* $list_col_cant_del= array {
* 						'NAME'=>'NAME', ...
* 						'Column name' => 'Column name',
* 						}
* $queryDetails = string 'SELECT QUERY'
* Tab_options : All the options for the specific table
* $tab_options= array{
* 						'form_name'=> "show_all",....
* 						'Option' => value,
* 						}
*/
function tab_req($list_fields,$default_fields,$list_col_cant_del,$queryDetails,$tab_options)
{
	global $protectedPost,$l,$pages_refs;

	if($queryDetails === false){
		$res =  array("draw"=> $tab_options['draw'],"recordsTotal"=> 0,  "recordsFiltered"=> 0 , "data"=>0 );
		echo json_encode($res);
		die;
	}
	$columns_special = array("CHECK",
			"SUP",
			"GROUP_NAME",
			"NULL",
			"MODIF",
			"MOD_TAGS",
			"SELECT",
			"ZIP",
			"OTHER",
			"STAT",
			"ACTIVE",
			"MAC",
			"MD5_DEVICEID",
			"EDIT_DEPLOY",
			"SHOW_DETAILS",
			"ARCHIVER",
			"RESTORE",
			"AFFECT_AGAIN",
			"NEW_WINDOW"
	);


	$actions = array(
				"MODIF",
				"EDIT_DEPLOY",
				"SUP",
				"ZIP",
				"STAT",
				"ACTIVE",
				"SHOW_DETAILS",
				"ARCHIVER",
				"RESTORE",
				"AFFECT_AGAIN",
				"NEW_WINDOW"
	);
	foreach($actions as $action){
		if(isset($list_fields[$action])){
			$list_fields['ACTIONS']="h.ID";
			break;
		}
	}

	$visible = 0;
	foreach($list_fields as $key=>$column){
		if (((in_array($key,$default_fields))||(in_array($key,$list_col_cant_del))|| in_array($key, $columns_special)||array_key_exists($key,$default_fields) || $key=="ACTIONS") && !in_array($key,$actions)){
			$visible++;
		}
	}
	$data = json_encode($tab_options['visible_col'] ?? null);
	$customized=false;
	if (isset($tab_options['visible_col']) && count($tab_options['visible_col'])!=$visible){
		$customized=true;
		setcookie($tab_options['table_name']."_col",$data,time()+31536000);
	}
	else{
		if (isset($_COOKIE[$tab_options['table_name']."_col"])){
			if($data !=  $_COOKIE[$tab_options['table_name']."_col"]){
				setcookie($tab_options['table_name']."_col",$data,time()+31536000);
			}
			else{
				setcookie($tab_options['table_name']."_col", FALSE, time() - 3600 );
			}
		}
	}
	if (isset($tab_options['REQUEST'])){
		foreach ($tab_options['REQUEST'] as $field_name => $value){
			$resultDetails = mysql2_query_secure($value, $_SESSION['OCS']["readServer"],$tab_options['ARG'][$field_name] ?? '');
			while($item = mysqli_fetch_object($resultDetails)){
				if ($item -> FIRST != "")
				$tab_options['SHOW_ONLY'][$field_name][$item -> FIRST]=$item -> FIRST;
			}
		}
	}
	$table_name = $tab_options['table_name'];
	//search static values
	if (isset($_SESSION['OCS']['SQL_DATA_FIXE'][$table_name])){
		foreach ($_SESSION['OCS']['SQL_DATA_FIXE'][$table_name] as $key=>$sql){
			if (!isset($_SESSION['OCS']['ARG_DATA_FIXE'][$table_name][$key]))
				$arg=array();
			else
				$arg=$_SESSION['OCS']['ARG_DATA_FIXE'][$table_name][$key];
			if ($table_name == "TAB_MULTICRITERE"){
				$sql.=" and hardware_id in (".implode(',',$_SESSION['OCS']['ID_REQ']).") group by hardware_id ";
				//ajout du group by pour régler le problème des résultats multiples sur une requete
				//on affiche juste le premier critère qui match
				$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
			}else{
				//add sort on column if need it
				if (!empty($protectedPost['tri_fixe']) && strstr($sql,$protectedPost['tri_fixe'])){
					$sql.=" order by '%s' %s";
					array_push($protectedPost['tri_fixe'],$arg);
					array_push($protectedPost['sens_'.$table_name],$arg);
				}
				$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
			}
			while($item = mysqli_fetch_object($result)){
				if (!empty($item->HARDWARE_ID))
					$champs_index=$item->HARDWARE_ID;
				elseif($item->FILEID != "")
				$champs_index=$item->FILEID;
				//echo $champs_index."<br>";
				if (isset($tablename_fixe_value)){
					if (strstr($sql,$tablename_fixe_value[0]))
						$list_id_tri_fixe[]=$champs_index;
				}
				foreach ($item as $field=>$value){
					if ($field != "HARDWARE_ID" and $field != "FILEID" and $field != "ID"){
						$tab_options['NO_SEARCH'][$field]=$field;
						//			echo "<br>champs => ".$field."   valeur => ".$value;
						$tab_options['REPLACE_VALUE_ALL_TIME'][$field][$champs_index]=$value;
					}
				}
			}
		}
	}
	$link=$_SESSION['OCS']["readServer"];

	$sqlfunctions[]='count';
	$sqlfunctions[]='sum';
	$sqlfunctions[]='min';
	$sqlfunctions[]='max';
	foreach($sqlfunctions as $sqlfunction){
		preg_match("/$sqlfunction\(.+\) \w*/i", $queryDetails, $matches);
		foreach ($matches as $match){
				$req = preg_split("/\)/", $match);
				$request=$req['0'].") ";
				$column = trim($req['1']);
				$tab_options['HAVING'][$column]['name']=$request ;
		}
	}

	$queryDetails = ajaxfiltre($queryDetails,$tab_options);

	$queryDetails .= ajaxsort($tab_options);
	$_SESSION['OCS']['csv']['SQLNOLIMIT'][$tab_options['table_name']]=$queryDetails;
	$queryDetails .= ajaxlimit($tab_options);
	$_SESSION['OCS']['csv']['SQL'][$tab_options['table_name']]=$queryDetails;
	if(isset($tab_options['REPLACE_VALUE'])) {
	$_SESSION['OCS']['csv']['REPLACE_VALUE'][$tab_options['table_name']]=$tab_options['REPLACE_VALUE'];
	}

	if (isset($tab_options['ARG_SQL']))
		$_SESSION['OCS']['csv']['ARG'][$tab_options['table_name']]=$tab_options['ARG_SQL'];

	$queryDetails=substr_replace(ltrim($queryDetails),"SELECT SQL_CALC_FOUND_ROWS ", 0 , 6);
	if (isset($tab_options['ARG_SQL']))
		$resultDetails = mysql2_query_secure($queryDetails, $link,$tab_options['ARG_SQL']);
	else
		$resultDetails = mysql2_query_secure($queryDetails, $link);


	$rows = ajaxgestionresults($resultDetails,$list_fields,$tab_options);

	if (is_null($rows)){
		$rows=0;
	}

	if(isset($_SESSION['OCS']['SQL_DEBUG']) && is_array($_SESSION['OCS']['SQL_DEBUG']) && ($_SESSION['OCS']['DEBUG'] == 'ON')){
		$debug = end($_SESSION['OCS']['SQL_DEBUG']);
	}
	// Data set length after filtering
	$resFilterLength = mysql2_query_secure("SELECT FOUND_ROWS()",$link);
	$recordsFiltered = mysqli_fetch_row($resFilterLength);
	$recordsFiltered=intval($recordsFiltered[0]);
	if($rows === 0){
		$recordsFiltered = 0;
	}
	if($tab_options["search"] && $tab_options["search"]['value']==""){
		$_SESSION['OCS'][$tab_options['table_name']]['nb_resultat']=$recordsFiltered;
	}
	if (isset($_SESSION['OCS'][$tab_options['table_name']]['nb_resultat'])){
		$recordsTotal = $_SESSION['OCS'][$tab_options['table_name']]['nb_resultat'];

	}else{
		$recordsTotal=$recordsFiltered;
	}
	if(isset($_SESSION['OCS']['SQL_DEBUG']) && is_array($_SESSION['OCS']['SQL_DEBUG']) && ($_SESSION['OCS']['DEBUG'] == 'ON')){
		$res =  array("draw"=> $tab_options['draw'],"recordsTotal"=> $recordsTotal,
				"recordsFiltered"=> $recordsFiltered, "data"=>$rows, "customized"=>$customized,
				"debug"=>$debug);
	}else{
		$res =  array("draw"=> $tab_options['draw'],"recordsTotal"=> $recordsTotal,
				"recordsFiltered"=> $recordsFiltered, "data"=>$rows, "customized"=>$customized);
	}
	echo json_encode($res);
}

function del_selection($form_name){
	global $l;
?>
	<script language=javascript>
			function garde_check(image,id)
			 {
				var idchecked = '';
				for(i=0; i<document.<?php echo $form_name ?>.elements.length; i++)
				{
					if(document.<?php echo $form_name ?>.elements[i].name.substring(0,5) == 'check'){
				        if (document.<?php echo $form_name ?>.elements[i].checked)
							idchecked = idchecked + document.<?php echo $form_name ?>.elements[i].name.substring(5) + ',';
					}
				}
				idchecked = idchecked.substr(0,(idchecked.length -1));
				confirme('',idchecked,"<?php echo $form_name ?>","del_check","<?php echo $l->g(900) ?>");
			}
	</script>
<?php
		//foreach ($img as $key=>$value){
			echo "<a href=# onclick=garde_check()><span class='glyphicon glyphicon-remove delete-span' title='".$l->g(162)."' ></span></a>";
		//}
	 echo "<input type='hidden' id='del_check' name='del_check' value=''>";
}

function js_tooltip() {
    echo "<script language='javascript' type='text/javascript' src='js/tooltip.js'></script>";
    echo "<div id='mouse_pointer' class='tooltip'></div>";
}

function tooltip($txt) {
    return " onmouseover=\"show_me('" . addslashes($txt) . "');\" onmouseout='hidden_me();'";
}

function iframe($link) {
    global $l;
    echo "<div class='iframe_div'>";
    echo "<p><a href='$link'  target='blank'   class='iframe_link' >" . $l->g(1374) . "</a></p>";
    echo "<div style='height:100%'><iframe  class='well well-sm' src=\"$link\">	</iframe></div>";
    echo "</div>";
}
?>
