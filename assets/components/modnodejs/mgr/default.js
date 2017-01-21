var socket = io((modNodejsConfig.host == 'localhost' ? document.location.origin : modNodejsConfig.host) + ':' + modNodejsConfig.port, {query: 'ctx=' + modNodejsConfig.ctx});

socket.on('msOnCreateOrder', function(data){
	notify('Новый заказ', {
		body: data.num + ' на сумму ' + data.cost + ' руб.',
		icon: window.location.origin + '/assets/components/minishop2/img/mgr/ms2_thumb.png',
	});
});

function notify(title, options) {
	if (!("Notification" in window)) {
		alert('Ваш браузер не поддерживает HTML Notifications, его необходимо обновить.');
	} else if (Notification.permission === "granted") {
		var notification = new Notification(title, options);
	} else if (Notification.permission !== 'denied') {
		Notification.requestPermission(function (permission) {
			if (permission === "granted") {
			var notification = new Notification(title, options);
		} else {
			alert('Вы запретили показывать уведомления'); // Юзер отклонил наш запрос на показ уведомлений
		}
	});
	}
}