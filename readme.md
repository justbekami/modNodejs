# modNodejs - Интеграция Node.js & socket.io в MODx
Готовая связка веб-сокет сервера на базе node.js & socket.io и MODx.
Ниже приведен пример установка связки на modhost.pro

### Установка Node.js

```
npm i modnodejs-server
```
В node_modules/modnodejs-server/index.js необходимо указать 2 параметра:
- config.token: секретный ключ который будет указан в modx
- config.domain: адрес установки modx, пример: http://site.ru

Далее выполняем команду для запуска сервера
```
node node_modules/modnodejs-server
```


### Установка modNodejs в MODx
Устанавливаем компонент из [https://modstore.pro/packages/other/modnodejs](https://modstore.pro/packages/other/modnodejs) или http://yourdomain.com/modNodejs/_build/build.transport.php 

Системные настройки:
 - modnodejs_token: секретный ключ, который указан в node_modules/modnodejs-server/index.js 
 - modnodejs_host: адрес для подключения к nodejs (выставляется автоматически), пример: http://site.ru:9090
 Остальные настройки отвечают за подключение js и css файлов


[English version here](https://github.com/but1head/modNodejs/blob/master/readme.en.md)

### Примеры
[Уведомление о новом заказе (администраторам) в браузере](gist.github.com/but1head/d7997501b066513281067617e4a21c7c)


[Обсуждение на modx.pro](https://modx.pro/development/10998-modnodejs-integrate-nodejs-in-modx/)
