$.fn.dataTableExt.oApi.fnFakeRowspan = function(oSettings) {
	_.each(oSettings.aoData, function(oData) {
		var cellsToRemove = [];
		for (var iColumn = 0; iColumn < oData.nTr.childNodes.length; iColumn++) {
			var cell = oData.nTr.childNodes[iColumn];
			var rowspan = $(cell).data('rowspan');
			var hide = $(cell).data('hide');
			//var hide = $(cell).data('hide');
			if (hide) {
				cellsToRemove.push(cell);
			} else if (rowspan > 1) {
				cell.rowSpan = rowspan;
			}
		}
		// Remove the cells at the end, so you're not editing the current array
		_.each(cellsToRemove, function(cell) {
			oData.nTr.removeChild(cell);
		});
	});

	oSettings.aoDrawCallback.push({
		"sName" : "fnFakeRowspan"
	});
	return this;
};

/**
 * Datatables client side sorting hooks
 */
// Support for sorting a column with checkboxes
$.fn.dataTableExt.afnSortData['dom-checkbox'] = function  ( oSettings, iColumn ) {
	return $.map( oSettings.oApi._fnGetTrNodes(oSettings), function (tr, i) {
		return $('td:eq('+iColumn+') input', tr).prop('checked') ? '1' : '0';
	} );
};

// Support for sorting the details column based on titles
$.fn.dataTableExt.afnSortData['dom-details'] = function  ( oSettings, iColumn ) {
	return $.map( oSettings.oApi._fnGetTrNodes(oSettings), function (tr, i) {
		return $('td:eq('+iColumn+') span.item-title', tr).text();
	} );
};

/**
 * Datatables client side range filter
 */
// Date range filter supporting single text field with date range in format 2013-01-01 - 2013-12-12
// based on http://www.datatables.net/forums/discussion/313/filter-date-range/p1 and
// http://www.datatables.net/plug-ins/filtering
$.fn.dataTableExt.afnFiltering.push(function(oSettings, aData, iDataIndex) {
	if (!document.getElementById(window.currentDataTableDateFilterField)) {
		return true;
	}
	var dateRange = document.getElementById(window.currentDataTableDateFilterField).value;
	// parse the range from a single field into min and max, remove " - "
	dateMin = dateRange.substring(0, 4) + dateRange.substring(5, 7) + dateRange.substring(8, 10);
	dateMax = dateRange.substring(13, 17) + dateRange.substring(18, 20) + dateRange.substring(21, 23);

	// Set the column where dates are
	var date = aData[2];
	// remove the time stamp out of my date
	// 2010-04-11 20:48:22 -> 2010-04-11
	date = date.substring(0, 10);
	// remove the "-" characters
	// 2010-04-11 -> 20100411
	date = date.substring(0, 4) + date.substring(5, 7) + date.substring(8, 10);

	// run through cases
	if (dateMin == "" && dateMax == "") {
		return true;
	} else if (dateMin == "" && date <= dateMax) {
		return true;
	} else if (dateMin <= date && dateMax == "") {
		return true;
	} else if (dateMin <= date && date <= dateMax) {
		return true;
	}
	// all failed
	return false;
});

// Number range filter supporting multiple text fields max & min
// based on http://www.datatables.net/plug-ins/filtering
$.fn.dataTableExt.afnFiltering.push(function(oSettings, aData, iDataIndex) {
	var iColumn = 2;
	var iMin = document.getElementById(window.currentDataTableNumberFilterMinField);
	var iMax = document.getElementById(window.currentDataTableNumberFilterMaxField);
	if (!iMin || !iMax) {
		return true;
	}
	iMin = iMin.value * 1;
	iMax = iMax.value * 1;

	var iFieldValue = aData[iColumn] == "-" ? 0 : aData[iColumn] * 1;
	if (iMin == "" && iMax == "") {
		return true;
	} else if (iMin == "" && iFieldValue < iMax) {
		return true;
	} else if (iMin < iFieldValue && "" == iMax) {
		return true;
	} else if (iMin < iFieldValue && iFieldValue < iMax) {
		return true;
	}
	return false;
});


/**
 * Datatables initialization functions
 */
var scrollArea = 340;
var initDataTable = function(selector, ignore, ajaxUrl) {
	var $table = $(selector);
	ignore = ignore || [];
	if (!jQuery.isArray(ignore) || !$table.length)
		return null;
	var column3Format = {
		"sType": "html",
		"aTargets": [ 2 ]
	};
	$table.find('thead th:nth-of-type(3)').each(function() {
		$this = $(this);
		if ($this.hasClass('data-date')) {
			column3Format = {
				"sType": "date",
				"aTargets": [ 2 ]
			};
		} else if ($this.hasClass('data-numeric')) {
			column3Format = {
				"sType": "numeric",
				"aTargets": [ 2 ]
			};
		}
	});

	// generate table ID's if not set
	var tableId = $table.attr('id');
	if (!tableId) {
		tableId = "static-data-table-" + 1; // TODO: Make this numbers dynamic
		$table.attr('id', tableId);
	}
	var newTable = $table.dataTable({
		"aoColumnDefs" : [
			{
				"bSortable" : false,
				"aTargets" : [0, -1],// ignore sorting first & last columns
			},
			/*{
				"sSortDataType": "dom-checkbox",
				"aTargets": [ 0 ]
			},*/
			{
				"sSortDataType": "dom-details",
				"aTargets": [ 1 ]
			},
			column3Format
		],
		"aaSorting" : [[1, "asc"]], // Sort by 2nd column ascending
		"aLengthMenu" : [[10, 20, 50, 75, 100, 150, 250, 350, 500, -1], [10, 20, 50, 75, 100, 150, 250, 350, 500, "All"]],
		"iDisplayLength" : 100,
		"bRetrieve" : false,
		"bDestroy" : true,
		//"sDom": "<'row-fluid'<'span6'l><'span6 filter-bar'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
		//"bInfo": false,
		//"bLengthChange": true,
		"sScrollY" : scrollArea + "px",
		//"bScrollCollapse": true,
		//"bPaginate": false
	});
	// setup table ui
	$('.dataTables_length').parent().addClass('hidden-phone');
	col3FilterControls = '';
	var $filter = newTable.parents('.dataTables_wrapper').find('.dataTables_filter');
	$filter.find('label:first input').attr('placeholder', 'enter detail(s)');
	if (column3Format.sType == "date") {
		var filterCtrlId = tableId + '-date-range-filter';
		col3FilterControls = '<label>from: ';
		col3FilterControls += '<input type="text" placeholder="yyyy-mm-dd - yyyy-mm-dd" class="date-range-filter" id="' + filterCtrlId + '">';
		col3FilterControls += '</label>';
		$filter.append(col3FilterControls);
		$('#' + filterCtrlId).keyup( function() { window.currentDataTableDateFilterField = $(this).attr('id'); newTable.fnDraw(); } );
	} else if (column3Format.sType == "numeric") {
		var filterCtrlId = tableId + '-number-range-filter';
		col3FilterControls = '<label>min: ';
		col3FilterControls += '<input type="text" class="number-range-filter" id="' + filterCtrlId + '-min">';
		col3FilterControls += ' max: ';
		col3FilterControls += '<input type="text" class="number-range-filter" id="' + filterCtrlId + '-max">';
		col3FilterControls += '</label>';
		$filter.append(col3FilterControls);
		$('#' + filterCtrlId + '-min').ace_spinner({value:0, step:1, icon_up:'icon-caret-up', icon_down:'icon-caret-down'});
		$('#' + filterCtrlId + '-max').ace_spinner({value:0, step:1, icon_up:'icon-caret-up', icon_down:'icon-caret-down'});
		$('#' + filterCtrlId + '-min').keyup( function() { window.currentDataTableNumberFilterMinField = $(this).attr('id'); newTable.fnDraw(); } );
		$('#' + filterCtrlId + '-max').keyup( function() { window.currentDataTableNumberFilterMaxField = $(this).attr('id'); newTable.fnDraw(); } );
	}

	activateDataTableScroll(scrollArea);
	resizeDataTables(newTable);
	return newTable;
};

var initDataTableAjax = function(selector, columnDefs) {
	var tables = $(selector);
	if (tables[0] != null) {
		var ajaxTables = [];
		for (var i = 0; i < tables.length; i++) {
			var table = $(tables[i]);
			var ajax_url = table.attr("ajax-url");
			var newTableAjax = $(table).dataTable({
				"bProcessing" : true,
				"sAjaxSource" : ajax_url,
				"sScrollY" : scrollArea + "px",
				"bScrollCollapse" : true,
				"aaSorting" : [[0, "desc"]], // Sort by first column descending
				"aLengthMenu" : [[10, 20, 50, 75, 100, 150, 250, 350, 500, -1], [10, 20, 50, 75, 100, 150, 250, 350, 500, "All"]],
				"iDisplayLength" : 50,
				"bRetrieve" : false,
				"bDestroy" : true,
				//"bServerSide": true,
				"aoColumnDefs" : columnDefs,
			});
			resizeDataTables(newTableAjax);
			ajaxTables.push(newTableAjax);
		}
		activateDataTableScroll(scrollArea);
		return ajaxTables;
	}
	return null;
};

// initialize new data tables with custom filtering capability
var initAjaxDataTable = function(selector){
	var $table = $(selector);
	if (!$table.length)
		return null;
	// preload table row template
	window.queuedTpls = [], window.loadedTpls = [];
	$table.each(function(i) {
		var tpl = $(this).data('row-tpl');
		if (window.queuedTpls.indexOf(tpl) == -1) {
			var table = this;
			window.queuedTpls.push(tpl);
			//console.log(loadedTpls, tpl, table);
	        var $tmpEl = $('<div id="temp-el"></div>');
	        $tmpEl.loadFromTemplate({
	            template : tpl,
	            data : {},
	            callback : function(res) {
	            	if (window.loadedTpls.indexOf(tpl) == -1) {
	            		window.loadedTpls.push(tpl);
	            	}
	            }
	        });
		}
	});

	// initialize tables
	var tables = {};
	$table.each(function(i) {
		// generate table ID's if not set
		if (!$(this).attr('id')) {
			$(this).attr('id', "ajax-data-table-" + $(this).data('ajax-source'));
		}
		var newTable = $table.dataTableAjaxCustomFilter({
	        "bProcessing": true,
	        "bServerSide": true,
	        "bFilter" : false,
	        //"sPaginationType": "full_numbers",
	        //"sServerMethod": "GET",
	        "aaSorting" : [[1, "asc"]], // Sort by 2nd column ascending
			"aLengthMenu" : [[10, 20, 50, 75, 100, 150, 250, 350, 500, -1], [10, 20, 50, 75, 100, 150, 250, 350, 500, "All"]],
			"iDisplayLength" : 100,
			"sScrollY" : scrollArea + "px",
			"fnPreDrawCallback": function(oSettings ) {
				var tpl = $(this).data('row-tpl');
				var id = $(this).attr('id');
				//console.log(tpl, oSettings, this, $(this));
				if (window.loadedTpls.indexOf(tpl) == -1) {
					var ix = setInterval(function() {
						//console.log('interval loop', tpl, oSettings);
						console.log(tpl, id);
						if (window.loadedTpls.indexOf(tpl) != -1) {
							tables[id].fnDraw();
							clearInterval(ix);
							// console.log('interval loop cleared');
						}
					}, 1000);
					// console.log("cancelling draw");
					return false;
				}
			},
	        "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
	        	var tpl = $(this).data('row-tpl');
	        	var id = $(this).attr('id');
	        	//console.log(iDisplayIndex, iDisplayIndexFull);
				if (window.loadedTpls.indexOf(tpl) != -1) {
					if (!tables[id].initialDataCache[iDisplayIndex]) {
			            // load row template/handlebars from server and pass aData object into it
			            var $tmpEl = $('<div id="temp-el"></div>');
			            aData = jQuery.extend(
			            	aData,
			            	window.user_permissions,
			            	{
				            	"assets_url": window.hbsContext.assets_url,
				            	"base_url": window.hbsContext.base_url,
				            	"component_base_url": window.hbsContext.component_base_url,
				            	"controller_base_url": window.hbsContext.controller_base_url,
				            	"current_url" : window.hbsContext.current_url
			            	}
			            );
			            tplContext = jQuery.extend(window.hbsContext, {'content_data' : [aData]});
			            console.log(tplContext);
			            //console.log(this, aData);
			            $tmpEl.loadFromTemplate({
			                template : tpl,
			                data : tplContext,
			                callback : function() {
			                	//console.log("Data returned", $tmpEl.find('td'));
			                	$tmpEl.find('td').each(function(i) {
			                		//console.log("cell ", i, this);
			                		//console.log($(this).html(), $('td:eq(' + i + ')', nRow).html());
			                		$('td:eq(' + i + ')', nRow).html($(this).html());
					            });
							}
						});
					} else {
						//console.log('intial row draw');
						$(tables[id].initialDataCache[iDisplayIndex]).filter('td').each(function(i) {
							//console.log(i);
	                		if (!$('td:eq(' + i + ')', nRow).html())
	                			$('td:eq(' + i + ')', nRow).html($(this).html());
			            });
			            // destroy initial row cache
						tables[id].initialDataCache[iDisplayIndex] = null;
					}
				}
			}
		});

		tables[$(this).attr('id')] = newTable;
	});

	resizeDataTables(tables);
	activateDataTableScroll(scrollArea);
	return tables;
};

$(function() {
	//window.mainDataTableAjax = initDataTableAjax('.data-table-ajax', [-1]);
	window.mainDataTable = initDataTable('.data-table:not(.ajax-data-table)', [0, -1]);

	// initialize new ajax datatable
	window.AjaxDataTables = initAjaxDataTable('.data-table.ajax-data-table');

	// expandable table details
	$('body').on('click', 'table .show-item-meta', function(e) {
		$this = $(this);
		$itemMeta = $this.siblings('.item-meta');
		if ($itemMeta.is(':visible')) {
			$itemMeta.slideUp();
			$this.find('i').removeClass('icon-chevron-up');
			$this.find('i').addClass('icon-chevron-down');
			$this.html($this.html().replace('Hide', 'Show'));
		} else {
			$itemMeta.slideDown();
			$this.find('i').removeClass('icon-chevron-down');
			$this.find('i').addClass('icon-chevron-up');
			$this.html($this.html().replace('Show', 'Hide'));
		}
		e.preventDefault();
	});
	// select all checkboxes
	$('body').on('click', 'table th input:checkbox', function() {
		var that = this;
		if ($(this).parents('.dataTables_scrollHead').length) { // scrollable datatable
			$(this).parents('.dataTables_scrollHead').siblings('.slimScrollDiv').find('table tr > td:first-child input:checkbox').each(function() {
				this.checked = that.checked;
				$(this).closest('tr').toggleClass('selected');
			});
		} else { // normal datatable
			$(this).closest('table').find('tr > td:first-child input:checkbox').each(function() {
				this.checked = that.checked;
				$(this).closest('tr').toggleClass('selected');
			});
		}
	});
});

$('body').on('click', '#sidebar-collapse i', function() {
	//console.log('resizing datatables', uniqueId());
	//window.mainDataTable.css('width', window.mainDataTable.parent().width());
	resizeDataTables(window.mainDataTable);
	resizeDataTables(window.AjaxDataTables);
});

var resizeDataTables = function(instance) {
	if (instance) {
		if (jQuery.isArray(instance)) {
			instance.forEach(function(datatable, i) {
				datatable.fnAdjustColumnSizing();
			});
		} else {
			instance.fnAdjustColumnSizing();
		}
	}
};

var activateDataTableScroll = function(height) {
	height = (parseInt(height)) ? parseInt(height) : 340;
	$('.dataTables_scrollBody').slimScroll({
		alwaysVisible: true,
		railVisible: true,
		height: height
	});
};

