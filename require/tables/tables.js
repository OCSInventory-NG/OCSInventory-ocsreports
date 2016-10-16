var tables = {
    handleError: function (xhr, error, thrown) {
        if (xhr.status == 401) {
            window.location.reload();
        }
    },

    handleData: function ($table, csrfNumber, csrfToken, postData, data) {
        var $checkAll = $('#' + $table.attr('id') + '_wrapper .check-all'),
                visible = [];

        if ($table.width() < $(window).width()) {
            $(".dataTables_scrollHeadInner, .dataTables_scrollHeadInner > table").add($table).width('100%');
        }

        data['CSRF_' + csrfNumber] = csrfToken;

        $checkAll.prop('checked', false).trigger('change');

        $.each(data.columns, function (i, col) {
            if ($table.DataTable().column('.' + col.data).visible()) {
                visible.push(i);
            }
        });

        return $.extend(data, postData, {
            visible_col: visible,
            ocs: [$table.serialize()]
        });
    },

    handleDataSrc: function (tableName, json) {
        $(".reset-" + tableName).toggle(json.customized);

        if (json.debug) {
            $("<p>" + json.debug + "</p><hr>").hide().prependTo('#' + tableName + '_debug div').fadeIn(1000);
            $(".datatable_request").show();
        }

        return json.data;
    },

    addMarkup: function (tableName) {
        $("<span/>", {id: tableName + "_settings_toggle", 'class': 'glyphicon glyphicon-chevron-down table_settings_toggle'}).hide().appendTo("#" + tableName + "_filter label");
        $("#" + tableName + "_settings").hide();
        $("." + tableName + "_top_settings").contents().appendTo("#" + tableName + "_settings");
        $("#" + tableName + "_settings").addClass('table_settings');
    },

    checkAll: function ($table, checked) {
        if (typeof (checked) === 'undefined')
            checked = true;

        $table.find('.check-row').prop('checked', checked);
    },

    showTable: function (tableName, csrfNumber, url, postData, columns) {
        $(document).ready(function () {
            var $table = $('#' + tableName),
                    csrfToken = $('#CSRF_' + csrfNumber).val(),
                    dom = '<<"row"lf <"dataTables_processing" r>><"#' + tableName + '_settings" >t<"row" <"col-md-2" i><"col-md-10" p>>>';

            $table.dataTable({
                processing: true,
                serverSide: true,
                dom: dom,
                ajax: {
                    url: url,
                    type: "POST",
                    error: tables.handleError,
                    data: function (data) {
                        return tables.handleData($table, csrfNumber, csrfToken, postData, data);
                    },
                    dataSrc: function (json) {
                        return tables.handleDataSrc(tableName, json);
                    }
                },
                columns: columns,
                language: tables.language, // tables.language is set dynamically in php, see TableRenderer.php
                scrollX: true
            });

            tables.addMarkup(tableName);

            // Handle show/hide column
            $("body").on("click", "#select_col" + tableName, function () {
                var col = "." + $(this).val();

                $table.DataTable().column(col).visible(!($table.DataTable().column(col).visible()));
                $table.DataTable().ajax.reload();
            });

            $("body").on("click", "#" + tableName + "_settings_toggle", function () {
                $(this).toggleClass("glyphicon-chevron-up").toggleClass("glyphicon-chevron-down");
                $("#" + tableName + "_settings").fadeToggle();
            });

            $('#' + tableName + '_wrapper .check-all').change(function () {
                tables.checkAll($table, this.checked);
            });
        });
    }
};