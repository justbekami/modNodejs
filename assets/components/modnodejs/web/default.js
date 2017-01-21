var socket = io((modNodejsConfig.host == 'localhost' ? document.location.origin : modNodejsConfig.host) + ':' + modNodejsConfig.port, {query: 'ctx=' + modNodejsConfig.ctx});

