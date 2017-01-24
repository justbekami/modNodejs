# modNodejs - Integration of Node.js & socket.io to MODx
Web server socket bundle based on node.js & socket.io and MODx
Below is a sample installation of the bundle on modhost.pro

### Settings Node.js

Upload the folder modnodejs_components and modnodejs.js to s****/
The file indicates the modnodejs.js token (create yourself)

Execute the commands in the console:

```
npm install socket.io
npm install forever
npm install request
node_modules/forever/bin/forever start modnodejs.js
```
If the console does not report errors - the server is running, and everything works fine

### Installing modNodejs in MODx
Set component. 

In System Setting modnodejs_token specify the token that is indicated in modnodejs.js



[Examples and discussion on modx.pro](https://modx.pro/development/10998-modnodejs-integrate-nodejs-in-modx/)
