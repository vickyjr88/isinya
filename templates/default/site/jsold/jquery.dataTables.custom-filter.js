(function ($) {
	// based on http://justinmichaels.net/custom-serverside-filtering-with-jquery-datables-and-asp-net-mvc3
    jQuery.fn.dataTableAjaxCustomFilter = function (settings) {
    	// function to get data attributes with a given prefix
		this.getDataObject = function(dataPrefix, toLower) {
			var data = $(this).data();
			toLower = toLower || true;
			var result = {};
			var dataRegex = new RegExp("^" + dataPrefix + "[A-Z]+");
			for (var p in data) {
				//console.log(p, data.hasOwnProperty(p), dataRegex.test(p));
				if (data.hasOwnProperty(p) && dataRegex.test(p)) {
					var shortName = toLower ? p[dataPrefix.length].toLowerCase() + p.substr(dataPrefix.length + 1) : p;
					result[shortName] = data[p];
				}
			}
			return result;
		};
        // alias the original jQuery object passed in since there is a possibility of multiple dataTables and search containers on a single page.
        // If we don't do this then we run the risk of having the wrong jQuery object before forcing a dataTable.fnDraw() call
        var $dataTable = this,
        searchCriteria = [],
        filterOptions = settings.filterOptions;
        if (filterOptions === undefined) {
        	var dataOpts = $dataTable.getDataObject('search');
			if (dataOpts) {
				filterOptions = {
					searchButton: dataOpts.btn,
					clearSearchButton: dataOpts.clearBtn,
					searchContainer: dataOpts.container
				};
			}
        } else {
	        // remove the filterOptions object from the object literal (json) that will be passed to dataTables
	        delete settings.filterOptions;
	    }
	    // set ajax source
	    if (!settings.sAjaxSource) {
	    	settings.sAjaxSource = $(this).data('ajax-source');
	    }
		// activate deferred loading if table was preloaded with markup
		var $rows = $(this).find('tbody tr');
	    if ($rows.length > 1) {
	    	settings.iDeferLoading = $rows.length;
			// cache initial table row markup
		    $dataTable.initialDataCache = [];
		    $rows.each(function(i) {
				$dataTable.initialDataCache.push(this.innerHTML);
			});
	    }
		// check for missing configs
        if (filterOptions === undefined) {
            throw {
                name: 'filterOptionsUndefinedError',
                message: 'Please define a filterOptions property in the object literal'
            };
        }
        if (filterOptions.searchButton === undefined) {
            throw {
                name: 'searchButtonUndefinedError',
                message: 'Please define a searchButton in the filterOptions'
            };
        }
        if (filterOptions.searchContainer === undefined) {
            throw {
                name: 'searchContainerUndefinedError',
                message: 'Please define a searchContainer in the filterOptions'
            };
        }
        // retrieves all inputs that we want to filter by in the searchContainer
        var $searchContainerInputs = $('#' + filterOptions.searchContainer).find('input[type="text"],input[type="radio"],input[type="checkbox"],select,textarea');
        // set column options
        var columns = $(this).data('columns');
        if (columns && !settings.aoColumns) {
        	var aoColumns = [];
	        $($(this).data('columns').split(",")).each(function(i) {
				var item = this;
				if (this.toLowerCase() == 'null') {
					aoColumns.push({
						"mData": null,
						"bSortable": false
					});
				} else {
					aoColumns.push({"mData": this});
				}
			});
			settings.aoColumns = aoColumns;
		}
        $searchContainerInputs.keypress(function (e) {
            if (e.keyCode === 13) {
                // if an enter key was pressed on one of our inputs then force the searchButton click event to happen
                $("#" + filterOptions.searchButton).click();
            }
        });
        $("#" + filterOptions.searchButton).click(function (e) {
            searchCriteria = [];
            var searchContainer = $("#" + filterOptions.searchContainer);
            searchContainer.find('input[type="text"],input[type="radio"]:checked,input[type="checkbox"]:checked,textarea[value!=""],select[value!=""]').each(function () {
                // all textboxes, radio buttons, checkboxes, textareas, and selects that actually have a value associated with them
                var element = $(this), value = element.val();
                if (value.length) {
	                if (typeof value === "string") {
	                    searchCriteria.push({ "name": element.attr("name"), "value": value });
	                }
	                else if (Object.prototype.toString.apply(value) === '[object Array]') {
	                    // multi select since it has an array of selected values
	                    var i;
	                    for (i = 0; i < value.length; i++) {
	                        searchCriteria.push({ "name": element.attr("name"), "value": value[i] });
	                    }
	                }
                }
            });
            // force dataTables to make a server-side request
            $dataTable.fnDraw();
            e.preventDefault();
        });
        if (filterOptions.clearSearchButton !== undefined) {
	        $("#" + filterOptions.clearSearchButton).click(function () {
	            searchCriteria = [];
	            $searchContainerInputs.each(function () {
	                var $input = $(this),
	                tagName = this.tagName.toLowerCase();
	                if (tagName === "input") {
	                    var type = $input.attr("type").toLowerCase();
	                    if (type === "checkbox" || type === "radio") {
	                        $input.removeAttr("checked");
	                    }
	                    else if (type === "text") {
	                        $input.val("");
	                    }
	                }
	                else if (tagName === "select") {
	                    if ($input.attr("multiple") !== undefined) {
	                        $input.val([]);
	                    }
	                    else {
	                        $input.val("");
	                    }
	                }
	                else if (tagName === "textarea") {
	                    $input.val("");
	                }
	            });
	            $dataTable.fnDraw();
	        });
        }
        settings.fnServerParams = function (aoData) {
            var i;
            for (i = 0; i < searchCriteria.length; i++) {
                // pushing each name/value pair that was found from the searchButton click event in to the aoData array
                // which will be sent to the server in the request
                aoData.push(searchCriteria[i]);
            }
        };
        return $dataTable.dataTable(settings);
    };
} (jQuery));
