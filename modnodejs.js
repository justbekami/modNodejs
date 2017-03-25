var config = {
	port: 9090,
	components: './modnodejs_components', // dir of plugins for modnodejs
	allowed_domains: [], // allow request only from this domains, dont write config.modx.domain - its automaticly
	modx: {
		token: '18ba1c0e14329a0599abbfde2f411757', // token in modx sys
		scheme: 'http', // http or https
		domain: 's8255.h7.modhost.pro', // modx domain, without slashes and http/https
		assets_path: 'assets', // modx assets path
	},
};

config.allowed_domains.push(config.modx.domain);

var io = require('socket.io').listen(config.port);
var request = require('request');
var url = require('url');
var onlineUsers = {};


io.use(function(socket, next) {

	// check origin in config.allowed_domains
	var origin = url.parse(socket.handshake.headers.origin || socket.handshake.headers.referer);
	if(config.allowed_domains.indexOf(origin.hostname) == -1) {
		next(new Error('error'));
		return;
	}

    var PHPSESSID = getCookie('PHPSESSID', socket.handshake.headers.cookie);
	var token = socket.handshake.headers['sec-websocket-token'];
	var ctx = socket.handshake.query.ctx;

	if (token && token == config.modx.token) {
		// if request from modx  ($modNodejs->emit)
		socket.handshake.modx = true;
		next();
	} else if (PHPSESSID && ctx) {
		//  check modx auth (by context key)
		modx.request('check_user', {
			PHPSESSID: PHPSESSID,
			ctx: ctx,
		}, function(response) {
			if (response.id > 0) {
				onlineUsers[ctx] = onlineUsers[ctx] || [];
				var user_id = response.id;

				// push in onlineUsers
				// add socketid if already online (multiple tabs)
				var idx = onlineUsers[ctx].findIndex(user => user['id'] === user_id);
				if (idx > -1) {
					onlineUsers[ctx][idx].socketid.push(socket.id);
					response.socketid = onlineUsers[ctx][idx].socketid;
				} else {
					response.socketid = [socket.id];
					onlineUsers[ctx].push(response);
				}

				// user groups (same modx)
				if (response.groups.length > 0) {
					for (var i=0; i < response.groups.length; i++) {
						socket.join(response.groups[i]);
					}
				}

				socket.handshake.user = response;
				socket.handshake.ctx = ctx;
				socket.handshake.PHPSESSID = PHPSESSID;
			}

			next();
		});
	}
});

// if connected, user = modx user (id, name, fullname, photo)
io.on('connection', function(socket) {
	var user = socket.handshake.user;
	var ctx = socket.handshake.ctx;

	// remove from online users or remove socketid (multiple tabs)
	socket.on('disconnect', function() {
		if(!user) return;
		var idx = onlineUsers[ctx].findIndex(ouser => ouser.id === user.id);
		if(user.socketid.length > 1) {
			var _idx = onlineUsers[ctx][idx].socketid.indexOf(socket.id);
			if (_idx > -1) {
			    onlineUsers[ctx][idx].socketid.splice(_idx, 1);
				user.socketid = socket.handshake.user.socketid = onlineUsers[ctx][idx].socketid;
			}
		} else {
			onlineUsers[ctx].splice(idx, 1);
		}
	});

	// include all .js files in config.components dir
	require('fs').readdirSync(config.components).forEach(function (file) {
		module.exports = require(config.components + '/' + file)(socket, io, onlineUsers, modx);
	});

});

var modx = {
	// send request to modx
	request: function(action, data, callback){
		request.post({
			url: config.modx.scheme + '://' + config.modx.domain + '/' + config.modx.assets_path + '/components/modnodejs/nodejs-connector.php',
			form: {
				token: config.modx.token,
				action: action,
				data: data
			}
		}, function(error, response, body){
			if (callback && typeof(callback) === "function" && !error && response.statusCode == 200) {
				var response = JSON.parse(body);
				if(response.success) callback(response.data, response.entry);
			}
		});
	},
};

function getCookie(name, cookies) {
    var value = "; " + cookies;
    var parts = value.split("; " + name + "=");
    if (parts.length == 2) return parts.pop().split(";").shift();
}