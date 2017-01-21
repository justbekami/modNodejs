var io = require('socket.io').listen(9090);
var request = require('request');
var onlineUsers = {};
var config = {
	components: './modnodejs_components',
	token: '<TOKEN>',
	domain: 'localhost',
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
			if (response.data) {
				onlineUsers[ctx] = onlineUsers[ctx] || [];
				if (response.data.id != 0) {
					// добавление список онлайн-пользователей
					var isOnline = onlineUsers[ctx].findIndex(user => user.id === response.data.id);
					if (isOnline !== -1) {
						onlineUsers[ctx][isOnline].socketid = socket.id;
					} else {
						response.data.socketid = socket.id;
						onlineUsers[ctx].push(response.data);
					}

					// добавление в группы, по названиям из modx
					if (response.data.groups.length > 0) {
						for (var i=0; i < response.data.groups.length; i++) {
							socket.join(response.data.groups[i]);
						}
					}
				}
				socket.handshake.user = response.data;
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
		module.exports = require(config.components + '/' + file)(socket, io, onlineUsers);
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