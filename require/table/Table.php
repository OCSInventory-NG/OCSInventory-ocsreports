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

/**
 * Handle tables display
 */
class Table {
    private $params;

    public function __construct() {
        include("Columns.php");
    }

    public function getColumns() {
        $this->getColumns();
    }

    /*
     * Called by ajax_tab_entetefixe
     */

    public function createTable($tablename, $formid) {
        $this->generateJavascript($tablename, $formid);
    }

    /*
     * Generate javascript code for the table
     */

    private function generateJavascript($tablename, $formid) {
        global $protectedGet, $protectedPost, $l;
        $tableid = "table#$tablename";
        print_r($protectedGet);
        print_r($protectedPost)
        ?>
        <script>
            console.log();
            //Check all the checkbox
            function checkall()
            {
                var table_id = "<?php echo $tableid; ?>";
                $(table_id + " tbody tr td input:checkbox").each(function () {
                    value = !$(this).attr('checked');
                    document.getElementById($(this).attr('id')).checked = value;
                });
            }
            $(document).ready(function () {
                var table_name = "<?php echo $tablename; ?>";
                var table_id = "<?php echo $tableid; ?>";
                var form_name = "<?php echo $formid; ?>";
                var csrfid = "input#CSRF_<?php echo $_SESSION['OCS']['CSRFNUMBER']; ?>";
                /*
                 Table Skeleton Creation.
                 A Full documentation about DataTable constructor can be found at
                 https://datatables.net/manual/index
                 */
                var table = $(table_id).dataTable({
                    "processing": true,
                    "serverSide": true,
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
                                console.log(col);
                                if ($(table_id).DataTable().column(col).visible()) {
                                    visible.push(index);
                                }
                            });
                            var ocs = [];
                            //Add the actual $_POST to the $_POST of the ajax request
        <?php
        foreach ($protectedPost as $key => $value) {
            if (!is_array($value)) {
                echo "d['" . $key . "'] = '" . $value . "'; \n";
            }
        }
        ?>
                            ocs.push($(form_name).serialize());
                            d.visible = visible;
                            d.ocs = ocs;
                        },
                    },

                    //Column definition
                    "columns": [
        <?php
        $index = 0;
//Visibility handling
        foreach ($columns as $key => $column) {
            if (!empty($visible_col)) {
                if ((in_array($index, $visible_col))) {
                    $visible = 'true';
                } else {
                    $visible = 'false';
                }
                $index ++;
            } else {
                if ((in_array($key, $default_fields)) || (in_array($key, $list_col_cant_del)) || in_array($key, $columns_special) || array_key_exists($key, $default_fields)) {
                    $visible = 'true';
                } else {
                    $visible = 'false';
                }
            }
            //Can the column be ordered
            if (in_array($key, $columns_special) || !empty($this->params['NO_TRI'][$key])) {
                $orderable = 'false';
            } else {
                $orderable = 'true';
            }
            //Cannot search in Delete or checkbox columns
            if (!array_key_exists($key, $columns_unique) || in_array($key, $columns_special)) {
                if (!empty($this->params['REPLACE_COLUMN_KEY'][$key])) {
                    $key = $this->params['REPLACE_COLUMN_KEY'][$key];
                }
                echo "{'data' : '" . $key . "' , 'class':'" . $key . "',
                 'name':'" . $key . "', 'defaultContent': ' ',
                 'orderable':  " . $orderable . ",'searchable': false,
                 'visible' : " . $visible . "}, \n";
            } else {
                $name = explode('.', $column);
                $name = explode(' as ', end($name));
                $name = end($name);
                if (!empty($this->params['REPLACE_COLUMN_KEY'][$key])) {
                    $name = $this->params['REPLACE_COLUMN_KEY'][$key];
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
                    "scrollX": 'auto',
                });

                //Column Show/Hide
                $("body").on("click", "#disp" + table_name, function () {
                    var col = "." + $("#select_col" + table_name).val();
                    $(table_id).DataTable().column(col).visible(!($(table_id).DataTable().column(col).visible()));
                    $(table_id).DataTable().ajax.reload();
                });
        <?php
//Csv Export
        if (!isset($this->params['no_download_result'])) {
            ?>
                    $(table_id).on('draw.dt', function () {
                        var start = $(table_id).DataTable().page.info().start + 1;
                        var end = $(table_id).DataTable().page.info().end;
                        var total = $(table_id).DataTable().page.info().recordsDisplay;
                        //Show one line only if results fit in one page
                        if (total == 0) {
                            $('#' + table_name + '_csv_download').hide();
                        } else {
                            if (end != total || start != 1) {
                                $('#' + table_name + '_csv_page').show();
                                $('#infopage_' + table_name).text(start + "-" + end);
                            } else {
                                $('#' + table_name + '_csv_page').hide();
                            }
                            $('#infototal_' + table_name).text(total);
                            $('#' + table_name + '_csv_download').show();
                        }
                    });
            <?php
        }
        ?>
            });

        </script>
        <?php
    }

    /*
     * Called by tab_req
     */

    public function updateTable() {

    }

    public function setParams($params) {
        $this->params = $params;
    }

    public function getParams() {
        return $this->params;
    }

}
?>