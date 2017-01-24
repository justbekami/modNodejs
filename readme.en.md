# modNodejs - Интеграция Node.js & socket.io в MODx
Готовая связка веб-сокет сервера на базе node.js & socket.io и MODx.
Ниже приведен пример установка связки на modhost.pro

### Установка Node.js

Переносим папку modnodejs_components и modnodejs.js в s****/
В файле modnodejs.js указываем токен (придумываем сами)

Выполняем в консоли команды:

```
npm install socket.io
npm install forever
npm install request
node_modules/forever/bin/forever start modnodejs.js
```
Если в консоли не возникло ошибок - сервер запущен и все прекрасно работает

### Установка modNodejs в MODx
Устанавливаем компонент. 

В системной настройке modnodejs_token указываем токен, который указали в modnodejs.js



[Примеры и обсуждение на modx.pro](https://modx.pro/development/10998-modnodejs-integrate-nodejs-in-modx/)
