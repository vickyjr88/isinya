(function($) {

	/* initialize the external events
	 -----------------------------------------------------------------*/

	$('#external-events div.external-event').each(function() {

		// create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
		// it doesn't need to have a start or end
		var eventObject = {
			title : $.trim($(this).text()) // use the element's text as the event title
		};

		// store the Event Object in the DOM element so we can get to it later
		$(this).data('eventObject', eventObject);

		// make the event draggable using jQuery UI
		
		$(this).draggable({
					zIndex : 999,
					revert : true, // will cause the event to go back to its
					revertDuration : 0 //  original position after the drag
				});
	});
	/* initialize the calendar
	 -----------------------------------------------------------------*/

	var date = new Date();
	var d = date.getDate();
	var m = date.getMonth();
	var y = date.getFullYear();

	var calendar = $('#calendar').fullCalendar({
		buttonText : {
			prev : '<i class="icon-chevron-left"></i>',
			next : '<i class="icon-chevron-right"></i>'
		},

		header : {
			left : 'prev,next today',
			center : 'title',
			right : 'month,agendaWeek,agendaDay'
		},
		editable : true,
		droppable : true, // this allows things to be dropped onto the calendar !!!
		//drop : function(date, allDay) {// this function is called when something is dropped
		drop: function(date, allDay) { 
			// retrieve the dropped element's stored Event Object
			var originalEventObject = $(this).data('eventObject');
			var $extraEventClass = $(this).attr('data-class');
			// we need to copy it, so that multiple events don't have a reference to the same object
			var copiedEventObject = $.extend({}, originalEventObject);
			var jobdate = date;var jobtitle = "txtinputjob";
			//showAjaxModalJobs('jobs/orders','',copiedEventObject.title);
			//alert(copiedEventObject.title);
				var form = $('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h3>New Order</h3></div>');
			form.append('<form class="form-horizontal" action="jobs/schedule" id="create_order_frm"><div class="control-group"><label class="control-label" for="inputDescription" >Order Description</label>'+
		    '<div class="controls"><textarea rows="2" id="inputDescription" name="orderDescription" placeholder ="Enter order description"> </textarea>'+
		    '</div></div><div class="control-group"><label class="control-label" for="jobQuantity" >Output quantity</label><div class="controls"><input type="text" id="jobQuantity" name="jobQuantity" placeholder =""></div></div>'+
			'<div class ="control-group"><label class="control-label" for="inputClients">Client</label><div class="controls"><select id ="inputClients"> <option value ="default">Select</option><optgroup label ="Not Found">'+
			'<option value ="new_item">Create New Client</option></optgroup><optgroup label ="List Of Clients" id ="org_client"><option>CircleK</option><option>KSolutions</option><option>CircSolutions</option>'+
			'</optgroup></select></div></div><div class ="control-group"><label class="control-label" for="inputJob">Job Profile</label><div class="controls">'+  
			'<input type="text" id="txtinputOrder" disabled="disabled" name="txtinputjob" value="'+originalEventObject.title+'">'+
			'</div></div><div class="control-group"><label class="control-label">&nbsp;</label><div class="controls"><label><input type="checkbox" id="repeatOrder" name="repeatOrder"><span class="lbl"> Repeat Order?</span>'+
			'</label></div></div><div class="control-group"> <label class="control-label" for="orderStartDate">Start Date</label><div class="controls"><input type="text" class="date-picker" disabled="disabled" name="orderStartDate" id="order_start_date" value="'+date+'">'+
			'</div></div><div class="control-group"> <label class="control-label" for="orderEndDate">Promise Date</label><div class="controls"><input type="text" class="date-picker" name="orderEndDate" id="order_stop_date">'+
		  	'</div></div></form>'+
		  	'<script>'+
			'</script>');			

			var div = bootbox.dialog(form, [{
				"label" : "<i class='icon-remove'></i> Close",
				"class" : "btn-small"
				},
				
				{"label" : "</i> Save Order",
					"class" : "btn btn-primary save_order btn-small submit-order",
					"callback" : function() {
						title = form.find("select#inputJob").val();
						end = form.find("input[name='orderEndDate']").val();
							//if (title !== null) {
								
								//form.find("input[name='txtinputjob']").val(copiedEventObject.title);
							//}	
					}
	
			},
			], {
				// prompts need a few extra options
				"onEscape" : function() {
					div.modal("hide");
				}
			});
			
			
			//showAjaxModal2("jobs/scheduler");
			// assign it the date that was reported
			copiedEventObject.start = date;
			copiedEventObject.allDay = allDay;
			if ($extraEventClass)
				copiedEventObject['className'] = [$extraEventClass];

			// render the event on the calendar
			// the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
			$('#calendar').fullCalendar('renderEvent', copiedEventObject, true);

			// is the "remove after drop" checkbox checked?
			if ($('#drop-remove').is(':checked')) {
				// if so, remove the element from the "Draggable Events" list
				$(this).remove();
			}

		},
		selectable : true,
		selectHelper : true,
		select : function(start, end, allDay) {
			
			
			var form = $('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h3>New Order</h3></div>');
						form.append('<form class="form-horizontal" action="jobs/schedule" id="create_order_frm"><div class="control-group"><label class="control-label" for="inputDescription" >Order Description</label>'+
						'<div class="controls"><textarea rows="2" id="inputDescription" name="orderDescription" placeholder ="Enter order description"> </textarea>'+
						'</div></div><div class="control-group"><label class="control-label" for="jobQuantity" >Output quantity</label><div class="controls"><input type="text" id="jobQuantity" name="jobQuantity" placeholder =""></div></div>'+
						'<div class ="control-group"><label class="control-label" for="inputClients">Client</label><div class="controls"><select id ="inputClients"> <option value ="default">Select</option><optgroup label ="Not Found">'+
						'<option value ="new_item">Create New Client</option></optgroup><optgroup label ="List Of Clients" id ="org_client"><option>CircleK</option><option>KSolutions</option><option>CircSolutions</option>'+
						'</optgroup></select></div></div><div class ="control-group"><label class="control-label" for="inputJob">Job Profile</label><div class="controls"><select id ="inputJob">'+  
						'<option value = "not">Select</option><optgroup label ="Not Found"><option value ="new_item">Create New Job</option></optgroup><optgroup label ="List Of Jobs" id ="org_jobs"><option>Iphone</option><option>Computer</option><option>Cooking</option>'+
						'</optgroup></select></div></div><div class="control-group"><label class="control-label">&nbsp;</label><div class="controls"><label><input type="checkbox" id="repeatOrder" name="repeatOrder"><span class="lbl"> Repeat Order?</span>'+
						'</label></div></div><div class="control-group"> <label class="control-label" for="orderStartDate">Start Date</label><div class="controls"><input type="text" class="date-picker" disabled="disabled" name="orderStartDate" id="order_start_date" value="'+start+'">'+
						'</div></div><div class="control-group"> <label class="control-label" for="orderEndDate">Promise Date</label><div class="controls"><input type="text" class="date-picker" name="orderEndDate" id="order_stop_date">'+
						  '</div></div></form>');			
			
						var div = bootbox.dialog(form, [{
							"label" : "<i class='icon-remove'></i> Close",
							"class" : "btn-small"
							},
							
							{"label" : "</i> Save Order",
								"class" : "btn btn-primary save_order btn-small submit-order",
								"callback" : function() {
									title = form.find("select#inputJob").val();
									end = form.find("input[name='orderEndDate']").val();
										if (title !== null) {
																				   var my_event = {
												 title : title,
											   start : new Date(),
											   end : new Date(end),
											   className : 'label-success',
											   allDay : false
											 };
																					   calendar.fullCalendar('renderEvent', {
												title : title,
												start : new Date(),
												end :  new Date(end),
												class: 'label-success',
												allDay : true
											}, true // make the event "stick"
										);	
										}
								}
				
						},
						], {
							// prompts need a few extra options
							"onEscape" : function() {
								div.modal("hide");
							}
						});
			
			
			calendar.fullCalendar('unselect');

		},
		eventClick : function(calEvent, jsEvent, view) {
			
			showAjaxModal2("jobs/scheduler");
		},
		eventRender: function(event, element, view) {
			
		 	$("#calendar").mousedown(function (e) {
			    if (e.button === 2) {
			    	
			    	//if($(e.target).parents(".fc-event").length > 0) return;
			    	if($(e.target).parents(".fc-event").length > 0){
					   $.contextMenu({
							 selector: '.fc-event', 
							 callback: function(key, options) {
								 var m = "clicked: " + key;
								 //window.console && console.log(m) || alert(m); 
							 },
							 items: {
								 "Schedule": {
							                name: "Schedule", 
							                // superseeds "global" callback
							                callback: function(key, options) {
							                    var m = "edit was clicked";
							                    //window.console && console.log(m) || alert(m);
							                    showAjaxModal('jobs/scheduler'); 
							                    //showAjaxModalJobs('jobs/schedule','',event.title);
							                }
							           },
						           "View Schedule": {
						                name: "View Schedule", 
						                // superseeds "global" callback
						                callback: function(key, options) {
						                    var m = "view schedule was clicked";
						                    //window.console && console.log(m) || alert(m);
						                    showAjaxModal('jobs/schedule'); 
						                }
						           }
							  }
						 });
						}
					 }
				});
		 
		  },

	});
		
	

})(jQuery);

$(document).delegate('#create_order_frm input[type=checkbox]', 'change', function()
	{
		if (this.checked)
		{
			switch ($(this).attr('id'))
			{
				case 'repeatOrder':
					$ajaxChildModal.data('width', $(this).data('width') || defaultModalWidth);
					$ajaxChildModal.load('jobs/repeat', '', function() {
					$ajaxChildModal.modal();
					});
				break;
			}
		}
	});//end event on the checkboxes
							
	$(document).delegate('#create_order_frm select', 'change', function()
	{

		if ($(this).val() == 'new_item')
		{
			switch($(this).attr('id'))
			{
				case 'inputClients':
					$ajaxChildModal.data('width', $(this).data('width') || defaultModalWidth);
					$ajaxChildModal.load('jobs/client', '', function() {
						$ajaxChildModal.modal();
					});
					break;
				case 'inputJob':
					$ajaxChildModal.data('width', $(this).data('width') || defaultModalWidth);
					$ajaxChildModal.load('jobs/profile', '', function() {
						$ajaxChildModal.modal();
					});
					break;
			}
		}
	});//end select event for creating new users and clients
	// Function to show bootstrap-based modal boxes
var showAjaxModalJobs = function(url, width, data) {
	width = width || defaultModalWidth;
	$ajaxModal.data("extra-id", data);
	$ajaxModal.data('width', width);
	// create the backdrop and wait for next modal to be triggered
	$('body').modalmanager('loading');
	if (url.search('\\?') > -1)
		url += '&uid=' + uniqueId();
	else
		url += '?uid=' + uniqueId();
	$ajaxModal.load(url, '', function() {
		$ajaxModal.modal();
	});
};
var showAjaxModal2 = function(url, width, data) {
	width = width || defaultModalWidth;
	$ajaxModal.data("extra-id", data);
	$ajaxModal.data('width', width);
	// create the backdrop and wait for next modal to be triggered
	$('body').modalmanager('loading');
/*
	if (url.search('\\?') > -1)
		url += '&uid=' + uniqueId();
	else
		url += '?uid=' + uniqueId();*/

	$ajaxModal.load(url, '', function() {
		$ajaxModal.modal();
	});
};


$('body').on('click', '.submit-order', function(e) {
	var $form = $('#' + $(this).data('form'));
	$modal = $(this).closest('.modal');
	//$modal.find('.modal-body').html('');
 	//$modal.modal('loading');
 	//$modal('hide');
 	//$modal("hide");
	var url = $form.attr('action');
	
	showAjaxModal2("jobs/scheduler");
	/*
	$.ajax({
				url : "jobs/scheduler",
				data : "",
				method : "POST",
				success : function(response) {
					//$modal.html(response);
					//$modal.modal();
					var form2 = response;
					var div = bootbox.dialog(form2, [{
					"label" : "<i class='icon-remove'></i> Close",
					"class" : "btn-small"
					},
					
					{"label" : "</i> Save Order",
						"class" : "btn btn-primary save_order btn-small submit-order-schedule",
						"callback" : function() {
						}
		
				},
				], {
					// prompts need a few extra options
					"onEscape" : function() {
						div.modal("hide");
					}
				});
				}
		});*/
	
	
});
