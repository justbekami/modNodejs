var io = require('socket.io').listen(9091);
var request = require('request');
var onlineUsers = {};
var config = {
	components: './modnodejs_components',
	token: '<TOKEN>',
	domain: 'http://localhost/',
};

// авторизация
io.use(function(socket, next){
    var PHPSESSID = getCookie('PHPSESSID', socket.handshake.headers.cookie);
	var token = socket.handshake.headers['sec-websocket-token'];
	var ctx = socket.handshake.query.ctx;

	if (token && token == config.token) {
		// проверка на modx подключение ($modNodejs->emit)
		socket.handshake.modx = true;
		next();
	} else if (PHPSESSID && ctx) {
		// проверка на авторизацию в modx (по контекстам) + добавление\обновление списка онлайн пользователей
		modx.request('login', {
			PHPSESSID: PHPSESSID,
			ctx: ctx,
		}, function(response){
			if (response.username) {
				onlineUsers[ctx] = onlineUsers[ctx] || [];
				if (response.id != 0) {
					var user_id = response.id;
					// добавление список онлайн-пользователей
					var isOnline = onlineUsers[ctx].findIndex(user => user['id'] === user_id);
					if (isOnline !== -1) {
						onlineUsers[ctx][isOnline].socketid = socket.id;
					} else {
						response.socketid = socket.id;
						onlineUsers[ctx].push(response);
					}

					// добавление в группы, по названиям из modx
					if (response.groups.length > 0) {
						for (var i=0; i < response.groups.length; i++) {
							socket.join(response.groups[i]);
						}
					}
				}
				socket.handshake.user = response;
				socket.handshake.ctx = ctx;
				next();
			}
		});
	}
});

// работа с эвентами при успешной авторизации + подключение компонентов
io.on('connection', function(socket){
	var ctx = socket.handshake.ctx;

	// удаление пользователя из онлайн списка
	socket.on('disconnect', function(){
		if(!ctx || !socket.user) return;
		var isOnline = onlineUsers[ctx].findIndex(user => user.socketid === socket.id);
		if (isOnline === -1) return;
		onlineUsers[ctx].splice(isOnline, 1);
	});

	// подключение компонентов (всех .js файлов в папке config.components)
	require('fs').readdirSync(config.components).forEach(function (file) {
		module.exports = require(config.components + '/' + file)(socket, io, onlineUsers, modx);
	});

});

var modx = {
	// отправка запросов в modx
	request: function(action, data, callback){
		request.post({
			url: config.domain,
			form: {
				nodejs: 1,
				token: config.token,
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