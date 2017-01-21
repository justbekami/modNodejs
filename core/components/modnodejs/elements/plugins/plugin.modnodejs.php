<?php
switch($modx->event->name) {

	// ответ на запросы из nodejs
	case 'OnHandleRequest':
		if($_REQUEST['nodejs'] != 1) return;
		$action = $_REQUEST['action'];
		$data = $_REQUEST['data'];
		$token = $_REQUEST['token'] == $modx->getOption('modnodejs_token');
		if(!$action || !$data || !$token) return;
		$modNodejs = $modx->getService('modnodejs', 'modNodejs', $modx->getOption('core_path') .  'components/modnodejs/model/modnodejs/');
		$response = $modNodejs->invokeEvent('OnNodejsRequest', array('action' => $action, 'data' => $data));
		exit($modx->toJSON($response));
		break;

	// уведомления о заказах
	case 'msOnCreateOrder':
		$modNodejs = $modx->getService('modnodejs', 'modNodejs', $modx->getOption('core_path') .  'components/modnodejs/model/modnodejs/');
		$modNodejs->emit('msOnCreateOrder', $msOrder->toArray());
		break;

	// авторизация
	case 'OnNodejsRequest':
		if ($action != 'login' || !$PHPSESSID = $data['PHPSESSID']) return;
		 // проверка существования сессии (пользователь \ гость)
        $session = $modx->getObject('modSession', $PHPSESSID);
        if (!$session) return;
		$values = & $modx->event->returnedValues;

        // провека пользователя
        $profile = $modx->getObject('modUserProfile', array(
            'sessionid' => $PHPSESSID,
            'blocked' => 0,
        ));

        // получение пользователя (профиль \ гость)
        if ($profile && $user = $profile->getOne('User')) {
            $modx->user = $user;
            $values['data'] = array(
                'id' => $modx->user->id,
                'username' => $modx->user->username,
                'fullname' => $profile->fullname,
                'email' => $profile->email,
                'photo' => $profile->photo,
                'groups' => $modx->user->getUserGroupNames(),
                'auth' => $modx->user->getSessionContexts(),
            );
        } else {
            $values['data'] = array(
              'id' => 0,
              'username' => '(anonymous)',
            );
        }
		break;

	// подключение к сокетам на фротэeде\бэкенде
	case 'OnLoadWebDocument':
	case 'OnManagerPageBeforeRender':
		$modNodejs = $modx->getService('modnodejs', 'modNodejs', $modx->getOption('core_path') .  'components/modnodejs/model/modnodejs/');
		$modNodejs->initialize($modx->context->key);
		break;
}