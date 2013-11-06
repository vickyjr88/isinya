$(function() {

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
		events : [{
			title : 'Distribution of scratchcards',
			start : new Date(y, m, 1),
			end : new Date(y, m, 21),
			className : 'label-important'
		}, {
			title : 'Recycling and collection of used bottles',
			start : new Date(y, m, 1),
			end : new Date(y, m, 10),
			className : 'label-yellow'
		}, {
			title : 'Supply of scartchcards within Nairobi',
			start : new Date(y, m, 1),
			end : new Date(y, m, 15),
			className : 'label-success'
		}, {
			title : 'Handle packaging for core products',
			start : new Date(y, m, d - 19),
			end : new Date(y, m, d),
			className : 'label-grey'
		}, {
			title : 'Collection of raw tobacco from farmers',
			start : new Date(y, m, 10, 16, 0),
			end : new Date(y, m, d - 3),
			allDay : false
		}],
		editable : true,
		droppable : true, // this allows things to be dropped onto the calendar !!!
		drop : function(date, allDay) {// this function is called when something is dropped

			// retrieve the dropped element's stored Event Object
			var originalEventObject = $(this).data('eventObject');
			var $extraEventClass = $(this).attr('data-class');

			// we need to copy it, so that multiple events don't have a reference to the same object
			var copiedEventObject = $.extend({}, originalEventObject);

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

			bootbox.prompt("New Job Title:", function(title) {
				if (title !== null) {
					calendar.fullCalendar('renderEvent', {
						title : title,
						start : start,
						end : end,
						allDay : allDay
					}, true // make the event "stick"
					);
				}
			});

			calendar.fullCalendar('unselect');

		},
		eventClick : function(calEvent, jsEvent, view) {

			var form = $("<form class='form-inline'><label>Change job name &nbsp;</label></form>");
			form.append("<input autocomplete=off type=text value='" + calEvent.title + "' /> ");
			form.append("<button type='submit' class='btn btn-small btn-success'><i class='icon-ok'></i> Save</button>");

			var div = bootbox.dialog(form, [{
				"label" : "<i class='icon-trash'></i> Delete Job",
				"class" : "btn-small btn-danger",
				"callback" : function() {
					calendar.fullCalendar('removeEvents', function(ev) {
						return (ev._id == calEvent._id);
					});
				}
			}, {
				"label" : "<i class='icon-remove'></i> Close",
				"class" : "btn-small"
			}], {
				// prompts need a few extra options
				"onEscape" : function() {
					div.modal("hide");
				}
			});

			form.on('submit', function() {
				calEvent.title = form.find("input[type=text]").val();
				calendar.fullCalendar('updateEvent', calEvent);
				div.modal("hide");
				return false;
			});

			//console.log(calEvent.id);
			//console.log(jsEvent);
			//console.log(view);

			// change the border color just for fun
			//$(this).css('border-color', 'red');

		}
	});

});