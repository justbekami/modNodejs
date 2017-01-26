# modNodejs - Интеграция Node.js & socket.io в MODx
Готовая связка веб-сокет сервера на базе node.js & socket.io и MODx.
Ниже приведен пример установка связки на modhost.pro

### Установка Node.js

```
npm i modnodejs-server
node node_modules/modnodejs-server
```
В node_modules/modnodejs-server/index.js указываем TOKEN и DOMAIN
Если в консоли не возникло ошибок - сервер запущен и все прекрасно работает

### Установка modNodejs в MODx
Устанавливаем компонент из [https://modstore.pro/packages/other/modnodejs](https://modstore.pro/packages/other/modnodejs) или http://yourdomain.com/modNodejs/_build/build.transport.php 

В системной настройке modnodejs_token указываем TOKEN, который указали в node_modules/modnodejs-server/index.js и исправляем modnodejs_domain если он не правильно определился


[English version here](https://github.com/but1head/modNodejs/blob/master/readme.en.md)

[Примеры и обсуждение на modx.pro](https://modx.pro/development/10998-modnodejs-integrate-nodejs-in-modx/)
