module.exports = exports =  function(socket, io, onlineUsers){

		socket.on('msOnCreateOrder', function(data){
			var data = JSON.parse(data);
			io.in('Administrator').emit('msOnCreateOrder', data);
		});

}