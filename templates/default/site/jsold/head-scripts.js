var onlineUsers = {};
var currentUser = {{json_user_info}};
var hbsContext = {{hbs_js_context}};
var socketServerUrl = "{{socket_server_url}}";
var assetsUrl = "{{assets_url}}";
var pushUid = "{{pushUid}}";
//var socketConn = new ab.Session("ws://vps.circleksolutions.com:8080");
var user_permissions = {
	'INVENTORY_EDIT' : "1",
	'INVENTORY_DELETE' : "1"
};