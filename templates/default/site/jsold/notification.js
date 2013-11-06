function startChat(from,to,callback){
  socketConn.subscribe('channel-'+from, function(topic, data){ console.log('Chat Channel -> '+data);callback(topic,data)});}
function subscribeNotification(){
  socketConn.subscribe('notification', function(topic, data) {userNotification(data)});
}
function endChat(from,to){
  socketConn.unsubscribe('chat/'+from+'/'+to, function(topic, data) {chat(data)});
}
function getChatUser(id){
	$.getJSON('personnel/user_profile',{userId:id},function(response){
		if(response.msg.type=='success'){
			//console.log(response.data);
			return response.data;
		}else{
			return false;
		}
	});
}
