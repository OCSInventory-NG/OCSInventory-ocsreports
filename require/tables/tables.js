var tables = {
	handleError: function(xhr, error, thrown) {
		if (xhr.status == 401) {
			window.location.reload();
		}
	},
	
	handleData: function($table, csrfNumber, csrfToken, postData, data) {
		var $checkAll = $('#'+$table.attr('id')+'_wrapper .check-all'),
			visible = [];
		
		if ($table.width() < $(window).width()) {
			$(".dataTables_scrollHeadInner, .dataTables_scrollHeadInner > table").add($table).width('100%');
		}
		
		data['CSRF_'+csrfNumber] = csrfToken;
		
		$checkAll.prop('checked', false).trigger('change');
		
		$.each(data.columns, function(i, col) {
		  	if ($table.DataTable().column('.'+col.data).visible()) {
				visible.push(i);
			}
		});
		
		return $.extend(data, postData, {
			visible_col: visible,
			ocs: [$table.serialize()]
		});
	},
	
	handleDataSrc: function(tableName, json) {
		$(".reset-"+tableName).toggle(json.customized);
		
		if (json.debug) {
			$("<p>"+json.debug+"</p><hr>").hide().prependTo('#'+tableName+'_debug div').fadeIn(1000);
			$(".datatable_request").show();
		}
		
		return json.data;
	},
	
	handleRowActions: function($table, csrfNumber, csrfToken) {
		$table.on('click', '.row-action', function(e) {
			e.preventDefault();
			e.stopPropagation();
			
			var $action = $(this),
				data = $action.data();
			
			if (data.confirm) {
				if (!confirm(data.confirm)) {
					return;
				}
			}
			
			if (data.ajax) {
				var ajaxOptions = {
					url: $action.attr('href'),
					type: data.method || 'GET'
				};
				
				if (data.method != 'GET') {
					ajaxOptions.data = {};
					ajaxOptions.data['CSRF_'+csrfNumber] = csrfToken;
				}
				
				$.ajax(ajaxOptions).always(function() {
					$table.DataTable().ajax.reload();
				});
			/*} else if (data.method && data.method != 'GET') {
				*/
			} else {
				window.location.href = $action.attr('href');
			}
		});
	},
	
	addMarkup: function(tableName) {
		$("<span/>", {id: tableName+"_settings_toggle", 'class': 'glyphicon glyphicon-chevron-down table_settings_toggle'}).hide().appendTo("#"+tableName+"_filter label");
		$("#"+tableName+"_settings").hide();
	 	$("."+tableName+"_top_settings").contents().appendTo("#"+tableName+"_settings");
	 	$("#"+tableName+"_settings").addClass('table_settings');
	},
	
	checkAll: function($table, checked) {
		if (typeof(checked) === 'undefined') checked = true;
		
		$table.find('.check-row').prop('checked', checked);
	},
	
	showTable: function(tableName, csrfNumber, url, postData, columns) {
		$(document).ready(function() {
			var $table = $('#'+tableName),
				csrfToken = $('#CSRF_'+csrfNumber).val(),
				dom = '<<"row"lf <"dataTables_processing" r>><"#'+tableName+'_settings" >t<"row" <"col-md-2" i><"col-md-10" p>>>';
			
			$table.dataTable({
				processing: true,
				serverSide: true,
				bFilter: false,
				bPaginate: false,
				bInfo: false,
				dom: dom,
				ajax: {
					url: url,
					type: "POST",
					error: tables.handleError,
					data: function(data) {
						return tables.handleData($table, csrfNumber, csrfToken, postData, data);
					},
					dataSrc: function(json) {
						return tables.handleDataSrc(tableName, json);
					}
				},
				columns: columns,
				language: tables.language, // tables.language is set dynamically in php, see TableRenderer.php
				scrollX: true
			});
			
			//tables.addMarkup(tableName);
			
			// Handle show/hide column
			$("body").on("click", "#select_col"+tableName, function() {
				var col = "."+$(this).val();
				
				$table.DataTable().column(col).visible(!($table.DataTable().column(col).visible()));
				$table.DataTable().ajax.reload();
			});

			$("body").on("click", "#"+tableName+"_settings_toggle", function(){
				$(this).toggleClass("glyphicon-chevron-up").toggleClass("glyphicon-chevron-down");
				$("#"+tableName+"_settings").fadeToggle();
			});
			
			// Handle checkboxes
			$('#'+tableName+'_wrapper .check-all').change(function() {
				tables.checkAll($table, this.checked);
			});
			
			tables.handleRowActions($table, csrfNumber, csrfToken);
		});
	}
};
/*

$(document).ready(function() {
	<?php if($opt){ ?>
	$("#"+tableName+"_settings_toggle").show();
	<?php  
		}
	//Csv Export 
	if (!isset($option['no_download_result'])){
	?>
		$table.on( 'draw.dt', function () {
			var start = $table.DataTable().page.info().start +1 ;
			var end = $table.DataTable().page.info().end;
			var total = $table.DataTable().page.info().recordsDisplay;
			//Show one line only if results fit in one page
			if (total == 0){
				$('#'+tableName+'_csv_download').hide();
				$("#"+tableName+"_settings_toggle").hide();
			}
			else{
			if (end != total || start != 1){
				$('#'+tableName+'_csv_page').show();
				$('#infopage_'+tableName).text(start+"-"+end);
			}
			else{
				$('#'+tableName+'_csv_page').hide();
			}
			$('#infototal_'+tableName).text(total);
			$('#'+tableName+'_csv_download').show();
			$("#"+tableName+"_settings_toggle").show();
			}
		});
	<?php 
	}
	?>
});*/