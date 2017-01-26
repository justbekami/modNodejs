# modNodejs - Integration of Node.js & socket.io with MODx
Web server socket bundle based on node.js, socket.io and MODx.
Below is a sample installation of the bundle on modhost.pro

### Installing Node.js

```
npm i modnodejs-server
node node_modules/modnodejs-server
```
In node_modules/modnodejs-server/index.js you need setup TOKEN and DOMAIN
If the console does not report errors - the server is running, and everything works fine

### Installing modNodejs in MODx
Install component from [https://modstore.pro/packages/other/modnodejs](https://modstore.pro/packages/other/modnodejs) or http://yourdomain.com/modNodejs/_build/build.transport.php 

In system settings you need setup modnodejs_token similar in node_modules/modnodejs-server/index.js and change modnodejs_domain if need.

[Russian version here](https://github.com/but1head/modNodejs/blob/master/readme.md)
[Examples and discussion on modx.pro](https://modx.pro/development/10998-modnodejs-integrate-nodejs-in-modx/)
