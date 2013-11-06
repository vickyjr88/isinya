// Dom-ready code

$(function() {
	$.fn.modalmanager.defaults.resize = true;
	$.fn.loadFromTemplate.dafaults.path = 'templates/default/site/tpl/contentblocks/';
	$.fn.loadFromTemplate.dafaults.extension = ".handlebars";

	$.fn.pusherChat({
		'debug' : true
	});

	$('#chat-list-holder .chat-list').slimScroll({
		height : '345px'
	});
	//jQuery("abbr.timeago").timeago();
	// temp element to use to load templates
	var $tmpEl = $('<div id="temp-el"></div>');
	// preload templates
	$tmpEl.loadFromTemplate({
		template : "online-users-box-item",
		data : {}
	});
	$tmpEl.loadFromTemplate({
		template : "chat-box-item",
		data : {}
	});
	$tmpEl.loadFromTemplate({
		template : "chat-box",
		data : {}
	});
});

/*
* Utility functions and core helpers
*/
// Add foreach function support to browsers that don't support it
if (!Array.prototype.forEach) {
	Array.prototype.forEach = function(fn, scope) {
		for (var i = 0, len = this.length; i < len; ++i) {
			if ( i in this) {
				fn.call(scope, this[i], i, this);
			}
		}
	};
}

// Add support for Date.now() to IE < 9
Date.now = Date.now ||
function() {
	return +new Date;
	// +new Date is shorthand for ToInt32(GetValue(new Date) or new Date.valueOf()
};

// Function to generate a unique id based on the current timestamp
function uniqueId() {
	return Date.now();
};

// Function to convert Array from jQuery.serializeArray() to normal array
function getFlattenFormDataArray(formArray) {
	var obj = {};
	formArray.forEach(function(val, i) {
		obj[val.name] = val.value;
	});

	return obj;
}

/*
 * Modal and dialog management code
 */
var $ajaxModal = $('#ajax-modal');
var $ajaxChildModal = $('#ajax-child-modal');
var $ajaxBabyModal = $('#ajax-baby-modal');
var $confirmModal = $('#confirm-modal');
var defaultModalWidth = $ajaxModal.data('width');

// Function to show a native popup window
var showPrintWindow = function(url, width, height) {
	printWindow = window.open(url, uniqueId(), 'width=' + width + ',height=' + height + ',toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,left=0,top=0');
	if (window.focus) {
		printWindow.focus();
	}
	return false;
};
// Callback for print report buttons with specified class to show native popup window with report
$('body').on('click', '.print-report', function(e) {
	var $this = $(this);
	var url = $this.data('href');
	if (!url)
		return false;
	var width = $this.data('width') || 600;
	var height = $this.data('height') || 800;
	showPrintWindow(url, width, height);
});

// Function to show bootstrap-based modal boxes
var showAjaxModal = function(url, width) {
	width = width || defaultModalWidth;
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
// Callback for when bootstrap dialogs are displayed
$ajaxModal.on('show', function(e) {
	//intialize datepicker
	$('.date-picker').datepicker({autoclose:true});
	var modalId = $(this).attr('id');

	initEditableField('#' + modalId + ' .editable-field');
	initDateTimePickerEditable('#' + modalId + ' .editable-field-date-time');
	initTimePickerEditable('#' + modalId + ' .editable-field-time');
	initSpinnerEditable('#' + modalId + ' .editable-field-spinner');
	initSliderEditable('#' + modalId + ' .editable-field-slider');
	initAjaxSingleSelect('#' + modalId + ' .editable-field-ajax-select');
	initAjaxMultiSelect('#' + modalId + ' .editable-field-ajax-multi-select');
	//console.log("visible");
});

// Callback for when bootstrap dialogs are closed
$ajaxModal.on('hide', function(e) {
	//console.log("hidden");
	// redraw datatable headers
	if (window.mainDataTable && typeof window.mainDataTable.fnAdjustColumnSizing === 'function') {
		console.log('attempting datatables refresh');
		setTimeout(function() {
			window.mainDataTable.fnAdjustColumnSizing();
		}, 400);
	}
});

// Callback for elements with specified class to show level-1 bootstrap modal
$('body').on('click', '.ajax-modal', function(e) {
	var width = $(this).data('width');
	var url = $(this).data('href');
	showAjaxModal(url, width);
	e.preventDefault();
});

// Callback for elements with specified class to show level-2 bootstrap modal
$('body').on('click', '#ajax-modal .child-modal', function() {
	var $button = $(this);
	$button.attr('data-loading-text', '<i class="icon-spinner icon-spin"></i>');
	$button.button('loading');
	$ajaxChildModal.data('width', $(this).data('width') || defaultModalWidth);
	var url = $(this).data('href');
	if (url.search('\\?') > -1)
		url += '&uid=' + uniqueId();
	else
		url += '?uid=' + uniqueId();
	$ajaxChildModal.load(url, '', function() {
		//console.log(url);
		$ajaxChildModal.modal();
		$button.button('reset');
	});
});

// Callback for elements with specified class to show level-3 bootstrap modal
$('body').on('click', '#ajax-child-modal .child-modal', function() {
	var $button = $(this);
	$button.attr('data-loading-text', '<i class="icon-spinner icon-spin"></i>');
	$button.button('loading');
	$ajaxBabyModal.data('width', $(this).data('width') || defaultModalWidth);
	var url = $(this).data('href');
	if (url.search('\\?') > -1)
		url += '&uid=' + uniqueId();
	else
		url += '?uid=' + uniqueId();
	$ajaxBabyModal.load(url, '', function() {
		//console.log(url);
		$ajaxBabyModal.modal();
		$button.button('reset');
	});
});

// Callback function to submit bootstrap dialogs
$('body').on('click', '.submit-form', function(e) {
	var $form = $('#' + $(this).data('form'));
	//These is a hack for IE 8 and below . Form serailize on works if can only work if called before
	// accessing any other form elements/innerhtml
	var data = $form.serialize();
	var $this = $(this);
	var validation = $this.data('validation');
	var beforeHook = $this.data('before-submit');
	if (validation && !window[validation]())
		return;
	$modal = $(this).closest('.modal');
	$modal.find('.modal-body').html('');
	$modal.modal('loading');
	var url = $form.attr('action');
	if (url.search('\\?') > -1)
		url += '&uid=' + uniqueId();
	else
		url += '?uid=' + uniqueId();
	var fields = $(this).data('row-data');
	var blockfields = $(this).data('block-data');

	if ( typeof beforeHook == 'string' && beforeHook in window) {
		var moreData = window[beforeHook]();
		//console.log(moreData);
		if ( typeof (moreData) == 'object' || typeof (moreData) == 'Array')
			data += '&' + $.param(moreData);
		else if ( typeof (moreData) == 'string' && moreData.search("&") != -1)
			data += '&' + moreData;
	}

	var upload = false;
	var attr = $form.attr('enctype');
	// For some browsers, `attr` is undefined; for others, `attr` is false.  Check for both.
	// IE sets attribute default to application/x-www-form-urlencoded, check for that too
	if ( typeof attr !== 'undefined' && attr !== false && attr !== 'application/x-www-form-urlencoded') {
		upload = true;
	}

	if (upload) {

		$form.ajaxSubmit({
			url : url,
			method : $form.attr('method'),
			success : function(response) {
				$modal.html(response);
				$modal.modal();
				//console.log('I made it here');
				var formID = $this.data('form');
				if (formID.lastIndexOf('-add') != -1)
					formID = formID.substring(0, formID.lastIndexOf('-add'));
				//console.log(formID);

				formData = getFlattenFormDataArray($('#' + formID).serializeArray());
				console.log(formData.personnel_active);
				var newBlock = parseInt(formData['new_block']);
				var blockType = $.trim(formData['block_type']);
				var saved = parseInt(formData['saved']);

				if ( typeof blockfields == 'string' && blockfields in window && saved) {

					if (formData.personnel_active == 1) {
						formData.personnel_active = "Active";
					} else {
						formData.personnel_active = "Inactive";
					}

					blockData = window[blockfields](formData, newBlock);

					if (newBlock) {
						blockData = window[blockfields](formData, newBlock);
						var group_char = "", newAddBlock = "";
						//The block to be added
						if (blockType == "e") {
							newAddBlock = fnAddEquipmentBlockData(formData, blockData);
							//First letter of the equipment name
							group_char = formData.equipment_name;
						} else if (blockType == "p") {
							newAddBlock = fnAddPersonnelBlockData(formData, blockData);
							//First letter of the personnel name
							group_char = formData.personnel_name;
						}

						//Check if the group alredy exists
						if ($('#grid-view').find('.group[data-group="' + group_char.substring(0, 1).toLowerCase() + '"]').length) {
							//Loop through matched items.
							$('#grid-view').find('.group[data-group="' + group_char.substring(0, 1).toLowerCase() + '"]').each(function() {
								$(this).append(newAddBlock);
							});

						} else {
							//Add new grouping with the new equipment item as child
							$('#grid-view').append('<div class="group" data-group="' + group_char.substring(0, 1).toLowerCase() + '"><div class="vcard-separator"><span class="group-heading">' + group_char.substring(0, 1).toLowerCase() + '</span></div>' + newAddBlock + '</div>');

						}

					} else {
						//Set personnel status if available in formData
						//console.log(blockData);
						//Picks the currently edited block for updating
						var blockIndex = $('div[data-id="' + formData.id + '"]');
						//Convert the aquired div children into an array of elements
						iblocked = _fnBlockToColumnIndex('div[data-id="' + formData.id + '"]');

						$.each(blockData, function(i, val) {
							if (val)
								//Updates the children or iblocked with newly saved content
								//Specifically updates the img tag since we use a different property 'src' to add image source
								if (iblocked[i].hasOwnProperty('src')) {
									iblocked[i].src = 'assets/avatars/' + val;
								} else {
									iblocked[i].textContent = val;
								}

						});

						if (formData.personnel_active.length) {
							status = (formData.personnel_active == "Active") ? "label-success" : "label-warning";
							$('#list-item-status-' + formData.id).removeClass();
							$('#list-item-status-' + formData.id).addClass('label ' + status);
						}
					}
				}
			}
		});
	} else {

		$.ajax({
			url : url,
			data : data,
			method : $form.attr('method'),
			//dataType: 'text/html',
			success : function(response) {
				//response = '"' + response + '"';
				//alert (response);

				$modal.html(response);
				$modal.modal();
				// get freshly returned form data
				var formID = $this.data('form');
				if (formID.lastIndexOf('-add') != -1)
					formID = formID.substring(0, formID.lastIndexOf('-add'));
				//console.log(formID);
				formData = getFlattenFormDataArray($('#' + formID).serializeArray());

				var newRow = parseInt(formData['new_row']);

				var saved = parseInt(formData['saved']);
				//&& fields in window

				if ( typeof fields == 'string' && fields in window && saved) {

					rowData = window[fields](formData, mainDataTable.fnGetData(), newRow);

					if (newRow) {
						//console.log('new row');
						lastRow = mainDataTable.fnAddData(rowData);
						lastRow = lastRow.pop();
						$(mainDataTable.$('tr')[lastRow]).attr('data-id', formData['id']);
						$(mainDataTable.$('tr')[lastRow]).find("td:first").addClass("center");
					} else {
						console.log('exisitng row');
						var rowIndex = mainDataTable.fnGetPosition(mainDataTable.$('tr[data-id="' + formData.id + '"]')[0]);

						//$this.closest('tr')[0] );
						//console.log('rowIndex ', rowIndex);
						$.each(rowData, function(i, val) {
							if (val) {
								console.log(val, rowIndex, i);
								mainDataTable.fnUpdate(val, rowIndex, i);
							}
						});
					}
				}
			}
		});
	}

});

/*
* Chat messaging code & functions
*/
// Callback when user clicks a list item on the chat users list
//$('body').on('click', '.chat-list-user', function(e) {
//	console.log("start chat with " + $(this).find('.user-name').text() + " (id: " + $(this).data('user-id') + ")");
//	//startChat(currentUser.id, $(this).data('user-id'), receiveChatMsg);
//	if ($(this).data('user-id') in onlineUsers)	{
//		console.log(onlineUsers[$(this).data('user-id')]);
//		//chatWith('chat', onlineUsers[$(this).data('user-id')]);
//		//createChatBox(onlineUsers[$(this).data('user-id')]);
//	} else {
//		//chatWith('offline-message', {username: $(this).data('username'), id: $(this).data('user-id')});
//		//createChatBox(onlineUsers[$(this).data('user-id')]);
//		//toggleOffline($(this).data('username'));
//	}
//	e.preventDefault();
//	return false;
//});

//// Callback when user clicks a list item on the offline messages list
//$('body').on('click', '.offline-message', function(e) {
//	console.log("start chat with " + " (id: " + $(this).data('message-id') + ")");
//	//startChat(currentUser.id, $(this).data('user-id'), receiveChatMsg);
//	if ($(this).data('message-id'))	{
//		offlineMessage($(this).data('message-id'))
//	} else {
//		console.log('Not found the message id');
//	}
//	e.preventDefault();
//	return false;
//});

///*
// * Functions to track online users list
// */
//// Event handler for user going offline
//var onUserOnline = function(userInfo){
//	if (typeof userInfo != 'object' || !userInfo.id || !userInfo.username || userInfo.id == currentUser.id)
//		return;
//	if (!(userInfo.id in onlineUsers)) {// add user to onlineUsers array
//		onlineUsers[userInfo.id] = userInfo;
//	}
//	toggleOffline(userInfo);
//	$userEl = $("#chat-list-holder .chat-list").find('a[data-user-id="' + userInfo.id + '"]');
//	$statusEl = $userEl.find('.user-status');
//	if ($userEl.length) { //user already in array, simply modify dom
//		var $li = $userEl.parent('li');
//		if (!$li.hasClass('online'))
//			$li.addClass('online');
//		$statusEl.removeClass('icon-eye-close');
//		$statusEl.addClass('icon-eye-open');
//		refreshOnlineUserCount();
//	} else {
//		$('#chat-list-holder .chat-list').loadFromTemplate({
//			template: "online-users-box-item",
//			data: {'user_info' : userInfo},
//			callback: function(){
//				$("#chat-list-holder .chat-list").find('a[data-user-id="' + userInfo.id + '"]').parent('li').addClass('online');
//				$statusEl.removeClass('icon-eye-close');
//				$statusEl.addClass('icon-eye-open');
//				refreshOnlineUserCount();
//			},
//			callbackPerEach: function(index, element){
//				//console.log("index: ", index, "element", element);
//			}
//		});
//	}
//}
//
//// Event handler for user going offline
//var onUserOffline = function(userInfo){
//	if (typeof userInfo != 'object' || !userInfo.id)
//		return;
//	if (userInfo.id in onlineUsers) {// delete from list
//		delete onlineUsers[userInfo.id];
//	}
//	toggleOffline(userInfo);
//	$userEl = $("#chat-list-holder .chat-list").find('a[data-user-id="' + userInfo.id + '"]');
//	$statusEl = $userEl.find('.user-status');
//	$userEl.parent('li').removeClass('online');
//	$statusEl.addClass('icon-eye-close');
//	$statusEl.removeClass('icon-eye-open');
//	//$("#chat-list-holder .chat-list").find('a[data-user-id="' + userInfo.id + '"]').parent('li').remove();
//	refreshOnlineUserCount();
//}
//
//// Function to count number of users online in users list
//var refreshOnlineUserCount = function(){
//	$("#online-users-count").html($('#chat-list-holder .chat-list li.online').length);
//}

// TODO: Document below here

//Assign avatar name to hidden avatar field
$('input[name=avatar]').change(function() {
	$('#item_avatar').val($("input[name='avatar']").val());
});

//Changes the passed div into an array of child elements

function _fnBlockToColumnIndex(iBlock) {
	var map = { };
	$(iBlock).children().each(function(_, node) {
		map[_] = { };
		//map[ _ ][ node.nodeName ] = node.textContent;
		map[_] = node;
	});

	return map;

}

//Create new Equipment item block
function fnAddEquipmentBlockData(rowData, iAction) {
	bHtml = "";
	bHtml = '<div class="well well-small vcard" data-id="' + rowData.id + '">';
	bHtml += '<img src="assets/avatars/' + rowData.equipment_avatar + '" class="img-polaroid" />';
	bHtml += '<span class="title">' + rowData.equipment_name + '</span>';
	bHtml += '<span class="serial">' + rowData.serial_number + '</span>';
	bHtml += '<span class="capacity">' + rowData.production_capacity + '</span>';
	bHtml += iAction[4];
	bHtml += '</div>';

	return bHtml;
}

function fnAddPersonnelBlockData(rowData, iAction) {
	pHtml = "";
	pHtml = '<div class="well well-small vcard" data-id="' + rowData.id + '">';
	pHtml += '<img src="assets/avatars/' + rowData.personnel_avatar + '" class="img-polaroid" />';
	status = (rowData.personnel_active == "1") ? "label-success" : "label-warning";
	pHtml += '<span id="list-item-status-' + rowData.id + '" class="label ' + status + '" style="float:right">' + rowData.personnel_active + '</span>';
	pHtml += '<span class="title">' + rowData.personnel_name + '</span>';
	pHtml += '<span class="position">' + rowData.personnel_title + '</span>';
	pHtml += iAction[4];
	pHtml += '</div>';

	return pHtml;
}


$('body').on('click', '.submit-form-ajax', function(e) {

	var $form = $('#' + $(this).data('form'));
	var comment=$('#' + $(this).data('comment'));
	if ($(this).data('comment')){
		    var comment=$('#' + $(this).data('comment'));
	}else{
			var comment=false;
	}
	$modal = $(this).closest('.modal');
	var url = $form.attr('action');
	if (url.search('\\?') > -1)
		url += '&uid=' + uniqueId();
	else
		url += '?uid=' + uniqueId();
	//console.log(url);
	var upload = false;
	var attr = $form.attr('enctype');
	// For some browsers, `attr` is undefined; for others,
	// `attr` is false.  Check for both.
	if ( typeof attr !== 'undefined' && attr !== false) {
		upload = true;
	}
	if (upload) {
		$form.ajaxSubmit({
			url : url,
			method : $form.attr('method'),
			success : function(response) {
				$modal.html(response);
				$modal.modal();
				//console.log('I made it here');
			}
		});
		//console.log('I made it here');
	} else {

		$.ajax({
			url : url,
			dataType : 'json',
			data : $form.serialize(),
			method : $form.attr('method'),
			success : function(response) {
					if(comment){
						var $comment_data=getFlattenFormDataArray($form.serializeArray());
						addcomments_on_modal($comment_data.supply_note_description, $comment_data.user);
					}
				if (response.msg.type == 'error')

					$('#ajax-message').html('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>' + response.msg.message_body + '</div>');
				else
					$('#ajax-message').html('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button>' + response.msg.message_body + '</div>');
				$modal.modal();
			}
		});
	}

});

$('body').on('click', '.close[data-dismiss="alert"]', function(e) {
	$(this).closest('.message-container').remove();
});

$('body').on('click', '.ajax-load-tab', function(e) {
	var $this = $(this);
	var url = $this.data('href');
	if (url.search('\\?') > -1)
		url += '&uid=' + uniqueId();
	else
		url += '?uid=' + uniqueId();
	var id = $this.attr('href');
	if ($(id).html() == '<p>Loading items..</p>')
		$(id).load(url, '', function() {
			//console.log('loaded tabs');
		});
});

$('body').on('click', '.confirm-action', function(e) {
	var url = $(this).data('href');
	var ajax_load = $(this).data('ajax');
	$confirmModal.find("#confirm-message").html($(this).data('msg'));
	$confirmModal.modal();
	$confirmModal.on('click', '.btn-confirm', function() {
		if (ajax_load)
			showAjaxModal(url, 480);
		else
			window.location.href = url;
	});
	e.preventDefault();
});
//TODO confirm reason for this rather than use normal href
$('body').on('click', '.perform-action', function(e) {
	var url = $(this).data('href');
	window.location.href = url;
	e.preventDefault();
});

$('body').on('keyup', '.validate-number', function(e) {
	//console.log('validating.. ', this.value);
	var max = $(this).attr('max');
	var min = $(this).attr('min');
	var val;
	if ((!min && max && this.value < max ) || (!max && min && this.value < min) || (max && this.value > max && min && this.value < min)) {
		val = this.value.match(/[0-9]*/);
	} else {
		val = parseInt(this.value);
	}
	$(this).val(val || 0);
});

$('body').on('change', 'select[rel="chained-list"]', function(e) {
	var $chainedList = $($(this).data('chain'));
	if ($chainedList) {
		$chainedList.find("option").first().html("Loading...");
		$chainedList.load($(this).data('href') + $(this).val());
		$chainedList.removeAttr('disabled');
	}
});
//TODO COnfirm use of this function else deprecated
//Avatar Upload
function uploadAvatar() {
	$('#fileupload').fileupload({
		dataType : 'json',
		done : function(data) {
			//console.log(data);
		}
	});
}

/* REMOVES A LIST ITEM FROM THE DOM
 * ================================ */
function deleteListItem(object) {
	$('#list-item-' + object.data.id).remove();
	showUserMsg(object.msg.message_body, object.type);
}

/* PERFORMS NORMAL AJAX REQUEST BASED ON LINK CLICKED
 * ================================================== */
$('body').on('click', '.ajax-link', function(e) {
	$.post($(this).attr('data-url'), null, function(response) {

		console.log('Success');
		if ($($(e.currentTarget).hasClass('delete-list-item'))) {
			deleteListItem(response);
		} else {
			$('#supply-thumb-' + response.data).remove();
		}
	}, 'json');
});
//TODO: To be deprecated and make use of general functions
$('body').on('click', '.confirm-delete', function(e) {
	var url = $(this).data('url');
	var ajax_load = $(this).data('ajax');
	$confirmModal.find("#confirm-message").html($(this).data('msg'));
	$confirmModal.modal();
	$confirmModal.on('click', '.btn-confirm', function() {
		$.post(url, null, function(response) {
			console.log('Success');
			$(response.data.id).remove();
			$('#supply-thumb-' + response.data.id).attr("src", "assets/avatars/default.png");
			$('#list-item-' + response.data.id + ' img.avatar').attr("src", "assets/avatars/default.png");
		}, 'json');
	});
	e.preventDefault();
});

//avatar upload
var sizeBox = document.getElementById('sizeBox'), // container for file size info
progress = document.getElementById('progress');
// the element we're using for a progress bar

// var uploader = new ss.SimpleUpload({
// button : 'uploadButton', // file upload button
// url : 'uploadHandler.php', // server side handler
// name : 'uploadfile', // upload parameter name
// progressUrl : 'uploadProgress.php', // enables cross-browser progress support (more info below)
// responseType : 'json',
// allowedExtensions : ['jpg', 'jpeg', 'png', 'gif'],
// maxSize : 1024, // kilobytes
// hoverClass : 'ui-state-hover',
// focusClass : 'ui-state-focus',
// disabledClass : 'ui-state-disabled',
// onSubmit : function(filename, extension) {
// this.setFileSizeBox(sizeBox);
// // designate this element as file size container
// this.setProgressBar(progress);
// // designate as progress bar
// },
// onComplete : function(filename, response) {
// if (!response) {
// alert(filename + 'upload failed');
// return false;
// }
// // do something with response...
// }
// });

function showUserMsg(msg, type) {
	type = type || 'info';
	$('#ajax-message').html('<div class="alert alert-' + type + '"><button type="button" class="close" data-dismiss="alert">&times;</button>' + msg + '</div>');
}

//TODO: Confirm that below code is of no use anymore thus to be deleted as it was
//moved to jquery.chat
/*
* Establish a Socket Connection to the Server
* and attach to the socketConn variable to be used
* across all scripts
*/
//var socketConn;
//$(function(){socketConn = new ab.Session(
//        'ws://' + socketServerUrl // ws://vps.circleksolutions.com:8080//The host (our Ratchet
// WebSocket server) to connect to
//        ,function() {
//      	   console.log('Socket Connection established with server');
//      	   // Once the connection has been established
//      	   socketConn.subscribe('onlineUsers', function(topic, data) {
//      	   	console.log(data);
//      	   	if(data.type == 'online'){
//      	   		// TODO: Use data.latest key from server to highlight new users on list
//      	   		if (data.users) {
//      	   			$.each(data.users, function(i, user){
//			    		 onUserOnline(user);
//			    	});
//			    }else if (data.msg){
//			    	onUserOnline(data.msg);
//			    }
//      	   	}
//      	   	if(data.type=='offline'){
//      	   		onUserOffline(data.msg);
//      	   	}
//      	   	});
//      	   socketConn.publish('onlineUsers',{eventName: "onNewUser", eventDetails:
// currentUser},false);
//           socketConn.subscribe('notification', function(topic, data) {console.log(data)});
//           socketConn.subscribe('channel-'+currentUser.id, function(topic, data){
// console.log(data);receiveChatMsg(topic,data)});
//        }
//      , function() {            // When the connection is closed
//            console.warn('WebSocket connection closed');
//        }
//      , {                       // Additional parameters, we're ignoring the WAMP sub-protocol for
// older browsers
//            'skipSubprotocolCheck': true
//        }
//    );});
// function sendMsg(id){
// console.log(id+ $('#message-'+id).val());
// sendChatMsg(id,$('#message-'+id).val());
// this.preventDefault();
// return false;
// }

//jQuery(document).ready(function() {
//   $("abbr.timeago").timeago();
// });

$(document).ready(function() {
	/*
	 * Enable scrolling in html elements with class selector
	 * for conversations
	 */
	$('.scroll').slimScroll({
		height : '500px'
	});
	/*
	 * Initializes date picker on elements associated with the date-picker class
	 */
	$('body').on('click','.date-picker',function(e){$('.date-picker').datepicker({autoclose:true});});
	/*
	 * Enable list filtering on inbox contacts
	 */
	var listOptions = {
		valueNames : ['name', 'text']
	};
	if ( typeof window.List === 'function') {
		var contactList = new List('contact-list', listOptions);
	}
});
/*
 * Performs normal ajax GET request and appends response inside specified element
 * Add the class on ajax-response to html element that is to be clicked
 * use the data-response attribute to specify the class or id of html element
 * to append response input.e.g data-response = '#response-body' or '.response-body'
 * @author Joseph Bosire
 */
$('body').on('click', '.ajax-response', function(e) {
	console.log('Clicked on ajax response');
	var $this, url, responseTag, callback;
	$this = $(this);
	url = $this.data('href');
	responseTag = $this.data('response');
	callback = $this.data('callback');
	$.get(url, null, function(response) {
		console.log('ajax response from server to be put on '+responseTag);
		$(responseTag).html('');
		$(responseTag).html(response);
		if (callback !== 'undefined' || callback !== false) {
			console.log('callback being called');
			executeFunctionByName(callback, window, $this);
		}
		console.log('after callback performed');
		$('.scroll').slimScroll({
			height : '500px'
		});
	});
});
/*
 * General function to perform a normal ajax form submission when not in a modal
 * use the data-response attribute to specify the class or id of html element
 * to append response input.e.g data-response = '#response-body' or '.response-body'
 * @author Joseph Bosire
 */
$('body').on('click', '.post-form-ajax', function(e) {
	var form, data, url, responseTag;
	form = $('#' + $(this).data('form'));
	data = form.serialize();
	url = form.attr('action');
	responseTag = $(this).data('response');
	$.post(url, data, function(response) {
		$(responseTag).html('');
		$(responseTag).html(response);
		//Change to utiltiy function
		$('.scroll').slimScroll({
			height : '500px'
		});
	});
});
/*
 * General function to perform a json ajax request when not in a modal
 * use the data-callback attribute to specify the function to call
 * on the response returned
 * @author Joseph Bosire
 */
$('body').on('click', '.json-ajax-response', function(e) {
	var $this, url, callback;
	$this = $(this);
	url = $this.data('href');
	callback = $this.data('callback');
	$.getJSON(url, null, function(response) {
		if (response.msg.type == 'success') {
			if (callback !== 'undefined' || callback !== false) {
				executeFunctionByName(callback, window, response);
			}
			console.log(response);
		}
		showUserMsg(response.msg,response.msg.type);
	});
});
/*
 * Utility function that calls a function given the string name and arguments
 */
function executeFunctionByName(functionName, context, args) {
	var args = Array.prototype.slice.call(arguments).splice(2);
	var namespaces = functionName.split(".");
	var func = namespaces.pop();
	for (var i = 0; i < namespaces.length; i++) {
		context = context[namespaces[i]];
	}
	return context[func].apply(this, args);
}
/*
 * function that add  a comment on the comment modal after user had added a comment
 */
function addcomments_on_modal(comment,user,timestamp){
 	var $html="<blockquote>"+comment+"<small class='pull-right'><span class='label label-info'>"+user+"</span>";
 	$html=$html+" <span class='label label-warning'>"+moment(new Date()).format('YYYY-MM-DDTHH:mm:ss'); +"</span></small></blockquote>";
	$('.well').append($html);

 }
