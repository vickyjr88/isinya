(function($) {
	$(function() {
		/**
		 * General UI util functions that aren't specific to a certain component
		 */
		// function to get data attributes with a given prefix
		window.getDataObject = function(element, dataPrefix) {
			var data = $(element).data();
			var result = {};
			var dataRegex = new RegExp("^" + dataPrefix + "[A-Z]+");
			for (var p in data) {
				console.log(p, data.hasOwnProperty(p), dataRegex.test(p));
				if (data.hasOwnProperty(p) && dataRegex.test(p)) {
					var shortName = p[dataPrefix.length].toLowerCase() + p.substr(dataPrefix.length + 1);
					result[shortName] = data[p];
				}
			}
			return result;
		};

		/**
		 * Improve responsiveness of UI
		 */
		$('#ace-settings-container').addClass('hidden-phone');

		/**
		 * Put any default bootstrap components initialization here
		 */

		//$('.tooltip').tooltip();

		/**
		 * Editables logic
		 */
		// function to convert editable values to custom server format
		var setEditableAjaxParams = function(params) {
			//originally params contain pk, name and value
			var data = {
				'id' : params.pk,
				'field' : params.name,
				'value' : params.value
			};
			return data;
		};

		// set global editables settings
		$.fn.editable.defaults.mode = 'popup';
		$.fn.editableform.loading = "<div class='editableform-loading'><i class='light-blue icon-2x icon-spinner icon-spin'></i></div>";
		$.fn.editableform.buttons = '<button type="submit" class="btn btn-info editable-submit"><i class="icon-ok icon-white"></i></button>' + '<button type="button" class="btn editable-cancel"><i class="icon-remove"></i></button>';

		// begin editables
		window.initEditableField = function(element) {
			$(element).editable({
				type : $(this).data('type') || 'text',
				mode : $(this).data('edit-mode') || 'popup',
				emptytext : 'None',
				params : function(params) {
					return setEditableAjaxParams(params);
				},
			});
		};

		// use for displaying date picker (with support for time precision too)
		window.initDateTimePickerEditable = function(element) {
			$(element).editable({
				type : 'date',
				mode : $(this).data('edit-mode') || 'popup',
				format : $(this).data('date-format') || 'yyyy-mm-dd hh:ii',
				viewformat : $(this).data('date-display-format') || 'd M yyyy hh:ii',
				params : function(params) {
					return setEditableAjaxParams(params);
				},
				datepicker : {
					weekStart : 1
				}
			});
		};

		// use for displaying time picker only, no dates (uses momentjs time format)
		window.initTimePickerEditable = function(element) {
			$(element).editable({
				type : 'combodate',
				mode : $(this).data('edit-mode') || 'popup',
				format : $(this).data('time-format') || 'HH:mm',
				viewformat : $(this).data('time-display-format') || 'h:mm a',
				template : 'hh : mm : a',
				value : $(this).data('value'),
				params : function(params) {
					return setEditableAjaxParams(params);
				},
				combodate : {
					minuteStep : 1
				}
			});
		};

		// use for displaying spinner control
		window.initSpinnerEditable = function(element) {
			$(element).editable({
				type : 'spinner',
				mode : $(this).data('edit-mode') || 'popup',
				params : function(params) {
					return setEditableAjaxParams(params);
				},
				spinner : {
					min : $(this).data('spinner-min') || 1,
					max : $(this).data('spinner-max') || 50,
					step : $(this).data('spinner-step') || 1
				},
				success : function(response, newValue) {
					var prefix = $(this).data('prefix');
					prefix = prefix ? prefix + ' ' : '';
					var suffix = $(this).data('suffix');
					suffix = suffix ? ' ' + suffix : '';
					$(this).html(prefix + newValue + suffix);
				}
			});
		};

		// use for displaying slider control
		window.initSliderEditable = function(element) {
			$(element).editable({
				type : 'slider',
				mode : $(this).data('edit-mode') || 'popup',
				params : function(params) {
					return setEditableAjaxParams(params);
				},
				slider : {
					min : $(this).data('slider-min') || 1,
					max : $(this).data('slider-max') || 50,
					width : $(this).data('slider-width') || 100
				},
				success : function(response, newValue) {
					var prefix = $(this).data('prefix');
					prefix = prefix ? prefix + ' ' : '';
					var suffix = $(this).data('suffix');
					suffix = suffix ? ' ' + suffix : '';
					$(this).html(prefix + newValue + suffix);
				}
			});
		};

		/*
		* Ajax-driven select boxes
		*/
		// sample select2 data
		var selectData = {
			1 : 'Red',
			2 : 'Blue',
			3 : 'Pink',
			4 : 'Purple',
			5 : 'Orange',
			6 : 'Indigo',
			7 : 'Maroon',
			8 : 'Violet',
			9 : 'Yellow',
			10 : 'Green',
		};

		window.initAjaxSingleSelect = function(element) {
			searchUrl = $(element).data('src');
			$(element).editable({
				type : 'select2',
				mode : $(this).data('edit-mode') || 'popup',
				onblur : 'submit',
				emptytext : 'None',
				params : function(params) {
					return setEditableAjaxParams(params);
				},
				select2 : {
					allowClear : true,
					width : '180px',
					minimumInputLength : 2,
					id : function(data) {
						return data.id;
					},
					ajax : {
						url : searchUrl,
						dataType : 'json',
						data : function(term, page) {
							return {
								query : term
							};
						},
						results : function(data, page) {
							return {
								results : data
							};
						}
					},
					formatResult : function(item) {
						return item.text;
					},
					formatSelection : function(item) {
						return item.text;
					},
					initSelection : function(element, callback) {
						original_el = $('.editable-container').siblings()[0];
						// console.log(element.val(), $original_el.data('option'));
						var data = {
							id : element.val(),
							text : $(original_el).data('option')
						};
						callback(data);
					}
				}
			});
		};

		window.initAjaxMultiSelect = function(element) {
			searchUrl = $(element).data('src');
			$(element).editable({
				type : 'select2',
				mode : $(this).data('edit-mode') || 'popup',
				onblur : 'submit',
				emptytext : 'None',
				params : function(params) {// setup ajax params
					var values = [];
					$.each(params.value, function(i, v) {
						values.push(v.split(':')[0]);
					});
					var data = {
						'id' : params.pk,
						'field' : params.name,
						'value' : values
					};
					return data;
				},
				select2 : {
					multiple : true,
					width : '220px',
					minimumInputLength : 2,
					id : function(data) {
						return data.id + ':' + data.text;
					},
					ajax : {
						url : searchUrl,
						dataType : 'json',
						data : function(term, page) {
							return {
								query : term
							};
						},
						results : function(data, page) {
							return {
								results : data
							};
						}
					},
					formatResult : function(item) {
						return item.text;
					},
					formatSelection : function(item, element) {
						return item.text;
					},
					initSelection : function(element, callback) {// called when select control in focus
						var data = [];
						$(element.val().split(",")).each(function(i) {
							var item = this.split(':');
							data.push({
								id : item[0],
								text : item[1]
							});
						});
						callback(data);
					}
				},
				display : function(value, response) {
					//display options as comma-separated values
					var html = [], checked = value;//$.fn.editableutils.itemsByValue(value, response);
					//console.log(value, response, checked);
					if (checked.length) {
						$.each(checked, function(i, v) {
							html.push($.fn.editableutils.escape(v.split(':')[1]));
						});
						$(this).html(html.join(', '));
					} else {
						$(this).empty();
					}
				}
			});
		};

		initEditableField('.editable-field');
		initDateTimePickerEditable('.editable-field-date-time');
		initTimePickerEditable('.editable-field-time');
		initSpinnerEditable('.editable-field-spinner');
		initSliderEditable('.editable-field-slider');
		initAjaxSingleSelect('.editable-field-ajax-select');//, '/DataSearch');
		initAjaxMultiSelect('.editable-field-ajax-multi-select');//, '/DataSearch');

		/*$.mockjax({
			url : '/DataLookupById',
			responseTime : 100,
			response : function(settings) {
				console.log(settings);
				this.responseText = {
					id : settings.data.id,
					text : selectData[settings.data.id]
				};
			}
		});*/

		$.mockjax({
			url : '/DataSearch',
			responseTime : 400,
			response : function(settings) {
				var res = [];
				$.each(selectData, function(i, item) {
					if (item.toLowerCase().search(settings.data.query.toLowerCase()) > -1) {
						res.push({
							id : i,
							text : item
						});
					}
				});
				this.responseText = res;
			}
		});

		//ajax save emulation. Select "blue" to see error message
		$.mockjax({
			url : '/save_url',
			responseTime : 400,
			response : function(settings) {
				if (settings.data.value == '2' || (jQuery.isArray(settings.data.value) && jQuery.inArray('2', settings.data.value) !== -1)) {
					this.status = 400;
					// any status that isn't 200 implies there's an error
					this.responseText = 'Validation error! You are not allowed to select Blue';
				} else {
					this.responseText = '';
				}
			}
		});
	});
})(jQuery);