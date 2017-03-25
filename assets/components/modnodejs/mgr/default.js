Ext.onReady(function() {
	var socket = io(modNodejsConfig.host, {query: "ctx=" + modNodejsConfig.ctx});
	var avatar = document.getElementById("user-avatar");

	socket.on('connect', function() {
		avatar.className = 'online';
	});

	socket.on('connect_error', function() {
		avatar.className = 'offline';
	});

});
