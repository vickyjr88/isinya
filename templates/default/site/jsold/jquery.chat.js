/*
 * Pusher chat
 * version: 1.0
 * Authors : Brian Mwadime , Joseph Bosire, Hezron Obuchele
 * © 2013 Codehive
 */
(function($) {

	$.fn.pusherChat = function(options) {
		//options
		var settings = $.extend({
			'debug' : false // enable debug mode
		}, options);

		var pageTitle = $('title').html();
		// just to update page title when message is triggered
		var timer = null;
		//Used to blink browser title on new message
		var socketConn = new ab.Session('ws://' + socketServerUrl// ws://vps.circleksolutions.com:8080//The
		// host (our Ratchet WebSocket server) to connect to
		, function() {
			console.log('Socket Connection established with server');
			// Once the connection has been established
			socketConn.subscribe(pushUid + '/onlineUsers', function(topic, data) {
				console.log(data);
				if (data.type == 'online') {
					// TODO: Use data.latest key from server to highlight new users on list
					if (data.users) {
						$.each(data.users, function(i, user) {
							onUserOnline(user);
						});
					} else if (data.msg) {
						onUserOnline(data.msg);
					}
				}
				if (data.type == 'offline') {
					onUserOffline(data.msg);
				}
			});
			socketConn.publish(pushUid + '/onlineUsers', {
				eventName : "onNewUser",
				eventDetails : currentUser
			}, false);
			socketConn.subscribe('notification', function(topic, data) {
				console.log(data);
			});
			socketConn.subscribe(pushUid + '/channel-' + currentUser.id, function(topic, data) {
				console.log(data);

				console.log(pushUid + '/channel-' + currentUser.id);
				receiveChatMsg(topic, data, data.type)

			});
		}, function() {// When the connection is closed
			console.warn('WebSocket connection closed');
		}, {// Additional parameters, we're ignoring the WAMP sub-protocol for older browsers
			'skipSubprotocolCheck' : true,
			'maxRetries': 10,
		    'retryDelay': 2000
		});
		/*************************************************************
		 *Function to send message on keypress
		 ************************************************************/
		$('body').on('keypress', 'textarea[name="message"]', function(e) {
			if (e.which == 13) {
				console.log(e);
				var $this = $(this);
				id = e.currentTarget.id.substr(8);
				sendChatMsg({
					id : id,
					username : $this.data('user')
				}, $this.val());
				$this.val('');
				$this.focus();
			}
		});

		$('body').on('click', 'a[data-action="collapse"], a[data-action="close"]', function(e) {
			if ($(this).data('action') == 'collapse') {
				id = e.currentTarget.id.substr(4);
				toggleChatBoxGrowth(id);
				$icon = $(this).find('i');
				if ($icon.hasClass('icon-chevron-down')) {
					$icon.removeClass('icon-chevron-down');
					$icon.addClass('icon-chevron-up');
				} else {
					$icon.addClass('icon-chevron-down');
					$icon.removeClass('icon-chevron-up');
				}
			} else if ($(this).data('action') == 'close') {
				id = e.currentTarget.id.substr(6);
				closeChatBox(id);
			}
			updateBoxPosition();

		});

		// Callback when user clicks a list item on the offline messages list
		$('body').on('click', '.offline-message', function(e) {
			console.log("start chat with " + " (id: " + $(this).data('message-id') + ")");
			if ($(this).data('message-id')) {
				offlineMessage($(this).data('message-id'));
			} else {
				console.log('Not found the message id');
			}
			e.preventDefault();
			return false;
		});

		var onNewMsg = function(msg) {
			if (typeof msg != 'object' || !msg.username || !userInfo.msg)
				return;

			$('#chatbox_' + msg.username + ' .dialogs').loadFromTemplate({
				template : "chat-box-item",
				data : {
					'user_info' : userInfo
				},
				callback : function() {
					refreshOnlineUserCount();
				},
				callbackPerEach : function(index, element) {
					//console.log("index: ", index, "element", element);
				}
			});
		};
		/************************************************************
		 *Function to toggle hide/show chatboxes.
		 *
		 ************************************************************/
		function toggleChatBoxGrowth(chatboxuser) {
			if ($('#chatbox_' + chatboxuser + ' .widget-body').css('display') == 'none') {

				var minimizedChatBoxes = new Array();

				if ($.cookie('chatbox_minimized')) {
					minimizedChatBoxes = $.cookie('chatbox_minimized').split(/\|/);
				}

				var newCookie = '';

				for ( i = 0; i < minimizedChatBoxes.length; i++) {
					if (minimizedChatBoxes[i] != chatboxuser) {
						newCookie += chatboxuser + '|';
					}
				}

				newCookie = newCookie.slice(0, -1);

				$.cookie('chatbox_minimized', newCookie);
				$('#chatbox_' + chatboxuser + ' .widget-body').css('display', 'block');
				$("#chatbox_" + chatboxuser + " .widget-body .dialogs").scrollTop($("#chatbox_"+chatboxuser+" .widget-body .dialogs")[0].scrollHeight);
				$("#chatbox_" + chatboxuser + " .widget-body .dialogs").slimScroll({start:'bottom'});
			} else {

				var newCookie = chatboxuser;

				if ($.cookie('chatbox_minimized')) {
					newCookie += '|' + $.cookie('chatbox_minimized');
				}

				$.cookie('chatbox_minimized', newCookie);
				$('#chatbox_' + chatboxuser + ' .widget-body').css('display', 'none');
			}

		}

		/*************************************************************
		 *Function to handle new chatBoxes
		 ************************************************************/
		$('.chat-list-user').on('click', function() {
			console.log("clicked");
			if ($(this).data('user-id') in onlineUsers) {
				console.log(onlineUsers[$(this).data('user-id')]);
				chatWith('chat', onlineUsers[$(this).data('user-id')]);
			} else {
				var chatMan = {
					username : $(this).data('username'),
					id : $(this).data('user-id')
				};
				chatWith('offline-message', {
					username : $(this).data('username'),
					id : $(this).data('user-id')
				});
				toggleOffline($(this).data('username'));
			}
		});

		// some action when click on chat box
		$('.ChatBoxContainer').on('click', function() {
			var newMessage = false;
			clearInterval(timer);
			$(this).removeClass('recive');
			$('.ChatBoxContainer').each(function() {
				if ($(this).hasClass('recive')) {
					newMessage = true;
				}
			});
			if (newMessage == false)
				$('title').text(pageTitle);
		});

		$('body').on('focus', 'textarea[name="message"]', function(e) {
			username = $(this).data('user');
			chatmsgid = $("#chatbox_" + username + " .dialogs .itemdiv:last .chatboxmessagecontent").data("msgid");
			//if (chatmsgid) {
			markRead(chatmsgid);
			//}

		});
		/*
		 * Function to mark message in chatbox as read on server
		 * @param msgId int
		 * @author Joseph Bosire
		 */
		function markRead(msgId) {
			console.log("MArking message as read");
			console.log(msgId);
			$.post('chat/mark_as_read', {
				id : msgId
			}, function(response) {
				console.log("Response for marking message as read");
				console.log(response);
				$('title').text(pageTitle);
				clearInterval(timer);
			}, 'json');

		}

		var chatWith = function(topic, chatuser, callback) {
			if (!$('#chatbox_' + chatuser.username).html()) {
				createChatBox(topic, chatuser, callback);
			} else {
				$("#chatbox_" + chatuser.username).show();
				if ($('#chatbox_' + chatuser.username + ' .widget-body').css('display') == 'none') {
					$('#chatbox_' + chatuser.username + ' .widget-body').css('display', 'block');
					$("#chatbox_" + chatuser.username + " .widget-body .dialogs").scrollTop($("#chatbox_"+chatuser.username+" .widget-body .dialogs")[0].scrollHeight);
					$("#chatbox_" + chatuser.username + " .widget-body .dialogs").slimScroll({start:'bottom'});
				}
				updateBoxPosition();
				$("#chatbox_" + chatuser.username + " form.chat-form textarea[name='message']").focus();
			}
		};
		/*-----------------------------------------------------------*
		 * create a chat box from the html template
		 *-----------------------------------------------------------*/
		function createChatBox(obj, userGuy, callback) {
			if (!userGuy) {
				return;
			}

			if (!$('#chatbox_' + userGuy.username).html()) {
				$('#templateChatBox .ChatBoxContainer h4 .userName').html(userGuy.username);
				console.log("username created: " + userGuy.username);
				$('.chatBoxslide').prepend($('#templateChatBox .ChatBoxContainer').clone().attr('id', 'chatbox_' + userGuy.username));
				$('#chatbox_' + userGuy.username + ' .widget-toolbar .expandBox').attr('id', 'min-' + userGuy.username);
				$('#chatbox_' + userGuy.username + ' .widget-toolbar .closeBox').attr('id', 'close-' + userGuy.username);
				$('#chatbox_' + userGuy.username + ' .widget-header').attr('id', 'chat-header-' + userGuy.id);
				$('#chatbox_' + userGuy.username + ' form.chat-form textarea').attr('id', 'message-' + userGuy.id).data('user', userGuy.username);
				$('#chatbox_' + userGuy.username + ' #chat-username').html(userGuy.username.charAt(0).toUpperCase() + userGuy.username.slice(1));
				toggleOffline(userGuy);
				$("#chatbox_" + userGuy.username + " .dialogs").slimScroll({
					height : "200px"
				});
			} else if (!$('#chatbox_' + userGuy.username).is(':visible')) {
				clone = $('#chatbox_' + userGuy.username).clone();
				$('#chatbox_' + userGuy.username).remove();
				if (!$('.chatBoxslide .ChatBoxContainer:visible:first').html())
					$('.chatBoxslide').prepend(clone.show());
				else
					$(clone.show()).insertBefore('.chatBoxslide .ChatBoxContainer:visible:first');
			}

			$('#chatbox_' + userGuy.username + ' textarea').focus();
			updateBoxPosition();
			recentChatHistory(userGuy.id);
		}

		/*-----------------------------------------------------------*
		 * Closing the chatbox
		 *-----------------------------------------------------------*/
		var closeChatBox = function(chatboxuser) {
			$('#chatbox_' + chatboxuser).hide();
			updateBoxPosition();
		};
		/**********************************************************
		 * Function to fetch offline messages and append to chat box
		 * @param sender int (user_id)
		 * @author Joseph Bosire
		 *************************************************************/
		var offlineMessage = function(sender) {

			$.getJSON('chat/offline_messages/' + sender, null, function(response) {
				if (response.msg.type == 'success') {
					$.each(response.data, function(key, value) {
						console.log(value);
						var utcSeconds = value.time;
						var d = new Date(0);
						// The 0 there is the key, which sets the date to the epoch
						value.time = d.setUTCSeconds(utcSeconds);
						receiveChatMsg('chat', value);
					});
				}
			});

		};
		/*-----------------------------------------------------------*
		 * reorganize the chat box position on adding or removing
		 *-----------------------------------------------------------*/
		function updateBoxPosition() {
			var right = 0;
			var slideLeft = false;
			$('.chatBoxslide .ChatBoxContainer:visible').each(function() {
				$(this).css({
					'right' : right
				});

				right += $(this).width() + 20;

				$('.chatBoxslide').css({
					'width' : right
				});

				if ($(this).offset().left - 10 < 0) {
					$(this).addClass('overFlow');
					slideLeft = true;
				} else
					$(this).removeClass('overFlow');
			});
		}

		/************************************************
		 * Function to send chat message to app
		 * @param user object (with id and username)
		 * @param message string
		 *************************************************/
		var sendChatMsg = function(recipient, message) {
			//Dont move code into if as this allows what the user has typed to be appended directly
			//without having to wait for the ajaz request
			time = moment(new Date());
			message = message.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\"/g, "&quot;");
			$("#chatbox_" + recipient.username + " .dialogs").append('<div class="itemdiv dialogdiv"><div class="user"><img src="' +
			currentUser.avatar + '"/></div><div style="background-color: #FFFFFF" class="body"><span class="name">' +
			'me' + ':&nbsp;&nbsp;</span><span class="text"><p>' + message +
			'</p></span><span class="chat-time"><small><abbr class="timeago muted" title="' +
			time.format("h:mm a MMM D YYYY") + '">' + 'Sent at ' + time.format("h:mm a MMM D YYYY") +
			'</abbr></small><i class="icon-spinner message-sent icon-spin orange bigger-125"></i></span></div></div>');
			$("#chatbox_" + recipient.username + " .dialogs").scrollTop($("#chatbox_"+recipient.username+" .dialogs")[0].scrollHeight);
			$("#chatbox_" + recipient.username + " .dialogs").slimScroll({start:'bottom'});
			$("#chatbox_" + recipient.username + ' textarea[name="message"]').val('');

			//$("abbr.timeago").timeago();
			$.post("chat/chat/" + recipient.id, {
				to : recipient.id,
				message : message
			}, function(data) {
				if (data.msg.type == 'success') {
					$(".message-sent").hide();
				}
			}, 'json');
		};
		/*-----------------------------------------------------------*
		 * some css tricks
		 *-----------------------------------------------------------*/
		$('#pusherChat .chatBoxWrap').css({
			'width' : $(window).width() - 30
		});

		updateBoxPosition();

		$(window).resize(function() {
			$('#pusherChat .chatBoxWrap').css({
				'width' : $(window).width() - 30
			});
			updateBoxPosition();
		});

		/******************************************************
		 * Functions to track online users list
		 *Event handler for user going offline
		 ******************************************************/
		var onUserOnline = function(userInfo) {
			if ( typeof userInfo != 'object' || !userInfo.id || !userInfo.username || userInfo.id == currentUser.id)
				return;
			if (!(userInfo.id in onlineUsers)) {// add user to onlineUsers array
				onlineUsers[userInfo.id] = userInfo;
			}
			toggleOffline(userInfo);
			$userEl = $("#chat-list-holder .chat-list").find('a[data-user-id="' + userInfo.id + '"]');
			$statusEl = $userEl.find('.user-status');
			if ($userEl.length) {//user already in array, simply modify dom
				var $li = $userEl.parent('li');
				if (!$li.hasClass('online'))
					$li.addClass('online');
				$statusEl.removeClass('status-busy');
				$statusEl.addClass('status-online');
				refreshOnlineUserList();
				refreshOnlineUserCount();
			} else {
				$('#chat-list-holder .chat-list').loadFromTemplate({
					template : "online-users-box-item",
					data : {
						'user_info' : userInfo
					},
					callback : function() {
						$("#chat-list-holder .chat-list").find('a[data-user-id="' + userInfo.id + '"]').parent('li').addClass('online');
						$statusEl.removeClass('status-busy');
						$statusEl.addClass('status-online');
						refreshOnlineUserList();
						refreshOnlineUserCount();
					},
					callbackPerEach : function(index, element) {
						//console.log("index: ", index, "element", element);
					}
				});
			}
		};
		// Event handler for user going offline
		var onUserOffline = function(userInfo) {
			if ( typeof userInfo != 'object' || !userInfo.id)
				return;
			if (userInfo.id in onlineUsers) {// delete from list
				delete onlineUsers[userInfo.id];
			}
			toggleOffline(userInfo);
			$userEl = $("#chat-list-holder .chat-list").find('a[data-user-id="' + userInfo.id + '"]');
			$statusEl = $userEl.find('.user-status');
			$userEl.parent('li').removeClass('online');
			$statusEl.addClass('status-busy');
			$statusEl.removeClass('status-online');
			refreshOnlineUserList();
			refreshOnlineUserCount();
		};
		// Function to count number of users online in users list
		var refreshOnlineUserCount = function() {
			$("#online-users-count").html($('#chat-list-holder .chat-list li.online').length);
		};
		var refreshOnlineUserList = function() {
			// sort online users list & rerender it
			var chatList = $("#chat-list-holder .chat-list li");
			chatList.sort(function(a, b) {
				$a = $(a);
				$b = $(b);
				var statusA = $a.hasClass('online') ? 0 : 1;
				var statusB = $b.hasClass('online') ? 0 : 1;
				var a = $a.find('a').first().data("username").toLowerCase();
				var b = $b.find('a').first().data("username").toLowerCase();
				if (statusA === statusB) {
					return (a < b) ? -1 : (a > b) ? 1 : 0;
				}
				return statusA - statusB;
			});
			$("#chat-list-holder .chat-list").html(chatList);
			$("#chat-list-holder .chat-list li .chat-list-user").on('click', function(e) {
				if ($(this).data('user-id') in onlineUsers) {
					console.log(onlineUsers[$(this).data('user-id')]);
					//createChatBox('chat',onlineUsers[$(this).data('user-id')]);
					chatWith('chat', onlineUsers[$(this).data('user-id')]);

				} else {
					var chatMan = {
						username : $(this).data('username'),
						id : $(this).data('user-id')
					};
					chatWith('offline-message', {
						username : $(this).data('username'),
						id : $(this).data('user-id')
					});
					toggleOffline($(this).data('username'));
				}
			});
		};
		/************************************************************
		 *Function to Recieve Message
		 ************************************************************/
		var receiveChatMsg = function(topic, message, msgType) {
			var obj = message;
			if (msgType != "offline") {
				timer = window.setInterval(function() {
					if ($('title').text().search('New message - ') == -1) {
						$('title').prepend('New message - ');
					}
				}, 1000);
			} else {
				obj = onlineUsers[topic];
				topic = 'chat';
			}

			if (!$("#chatbox_" + obj.username).html()) {
				chatWith(topic, obj, function() {
					displayTime = moment(new Date(message.time));
					$("#chatbox_" + obj.username + " .dialogs").append('<div class="itemdiv dialogdiv"><div class="user"><img src="' + message.avatar + '"/></div><div style="background-color: #FFFFFF" class="body"><span class="name">' + message.username + ':&nbsp;&nbsp;</span><span class="chatboxmessagecontent" data-msgid="' + message.msg_id + '"><p>' + message.msg + '</p></span><small><abbr class="chat-time timeago muted" title="' + displayTime.displayTime.format('h:mm a MMM D YYYY') + '">' + displayTime.displayTime.format('h:mm a MMM D YYYY') + '</abbr><small></div>');
					$("#chatbox_" + obj.username + " .dialogs").scrollTop($("#chatbox_"+obj.username+" .dialogs")[0].scrollHeight);
					$("#chatbox_" + obj.username + " .dialogs").slimScroll({start:'bottom'});
					$("#chatbox_" + obj.username + ' textarea[name="message"]').val('');
					if ($("#chatbox_" + obj.username).length > 0) {
						playNotificationSound();
					}
					return false;
				});

			}

			displayTime = moment(new Date(message.time));
			$("#chatbox_" + obj.username).css('display', 'block');
			$("#chatbox_" + obj.username + " .dialogs").css('display', 'block');
			if ($('#chatbox_' + obj.username + ' .widget-body').css('display') == 'none') {
						$('#chatbox_' + obj.username + ' .widget-body').css('display', 'block');
			}
			$("#chatbox_" + obj.username + " .dialogs").append('<div class="itemdiv dialogdiv"><div class="user"><img src="' + message.avatar + '"/></div><div style="background-color: #FFFFFF" class="body"><span class="name">' + message.username + ':&nbsp;&nbsp;</span><span class="chatboxmessagecontent" data-msgid="' + message.msg_id + '"><p>' + message.msg + '</p></span><small><abbr class="chat-time timeago muted" title="' + displayTime.format('h:mm a MMM D YYYY') + '">' + 'Sent at ' + displayTime.format('h:mm a MMM D YYYY') + '</abbr><small></div>');
			$("#chatbox_" + obj.username + " .dialogs").scrollTop($("#chatbox_"+obj.username+" .dialogs")[0].scrollHeight);
			//$("#chatbox_" + obj.username + " .dialogs").scrollBottom($("#chatbox_"+obj.username+" .dialogs")[0].scrollHeight);
			$("#chatbox_" + obj.username + ' textarea[name="message"]').val('');
			if ($("#chatbox_" + obj.username).length > 0) {
				playNotificationSound();
			}

		};
		/**
		 * Function to play chat notification sound
		 * @author Joseph Bosire
		 */
		function playNotificationSound() {
			$('#chatAudio')[0].play();
		}

		/************************************************************
		 *Toggle online/offline classes for chatbox
		 ************************************************************/
		function toggleOffline(userInfo) {
			if (userInfo.id in onlineUsers) {// add user to onlineUsers array
				$('#chatbox_' + userInfo.username + ' #chat-header-' + userInfo.id).removeClass("header-color-dark").addClass("header-color-blue2");
				$('#chatbox_' + userInfo.username + ' #chat-header-' + userInfo.id + " .icon-comment").removeClass("red").addClass("green");
				$('#chatbox_' + userInfo.username + " .offline").hide();
			} else if (!(userInfo.id in onlineUsers)) {
				$('#chatbox_' + userInfo.username + ' #chat-header-' + userInfo.id).removeClass("header-color-blue2").addClass("header-color-dark");
				$('#chatbox_' + userInfo.username + ' #chat-header-' + userInfo.id + " .icon-comment").removeClass("green").addClass("red");
				$('#chatbox_' + userInfo.username + " .offline").show();
			}
		}

		/***************************************************************
		 * Function to fetch most recent chat and append to chatbox
		 * Activated when chatbox is created
		 * @param sender int (user_id)
		 * @author Joseph Bosire
		 ****************************************************************/
		var recentChatHistory = function(sender) {
			msgType = "";
			$.getJSON('chat/recent_chat_history/' + sender, null, function(response) {
				if (response.msg.type == 'success') {
					$(".chat-loader").hide();
					$.each(response.data, function(key, value) {
						msgType = "offline";
						receiveChatMsg(sender, value, msgType);
					});
				}
			});
		};
	};
})(jQuery);
/****************************************
 *Function to format date
 ***************************************/
function getFormattedDate(dt) {
	var msgTime = dt.toString();
	var tzPos = msgTime.search(' GMT') - ' GMT'.length;
	// minus bcoz it returns the last index of the search string
	msgTime = msgTime.substr(4, tzPos);
	return msgTime;
}
/***************************************************************
 * Function to toggle active contact person in conversation thread window
 ************************************************************************/
var toggleActiveContact = function(contact) {
	$('.contact-list-item').removeClass('active');
	$(contact).parent().addClass('active');
};
var toggleMsgStatus = function(message) {
	$('.chat-message-'+message.data.message_id+' .unread').remove();
	showUserMsg("Message marked as read", "success");
};
