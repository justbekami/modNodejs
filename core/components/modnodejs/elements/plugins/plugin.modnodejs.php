<?php
switch($modx->event->name) {

	// retrieve request from nodejs
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

	// authorization
	case 'OnNodejsRequest':
	    if ($action != 'login' || !$PHPSESSID = $data['PHPSESSID']) return;

		 // check session
        $session = $modx->getObject('modSession', $PHPSESSID);
        if (!$session) return;

        // check user
        $profile = $modx->getObject('modUserProfile', array(
            'sessionid' => $PHPSESSID,
            'blocked' => 0,
        ));

        // get user (profile / guest)
        if ($profile && $user = $profile->getOne('User')) {
            $modx->user = $user;
            $response = array(
                'id' => $modx->user->id,
                'username' => $modx->user->username,
                'fullname' => $profile->fullname,
                'email' => $profile->email,
                'photo' => $profile->photo,
                'groups' => $modx->user->getUserGroupNames(),
                'auth' => $modx->user->getSessionContexts(),
            );
        } else {
            $response = array(
              'id' => 0,
              'username' => '(anonymous)',
            );
        }

        $modx->event->_output = $response;
        break;

	// frontend nodejs connection
	case 'OnLoadWebDocument':
	case 'OnManagerPageBeforeRender':
		$modNodejs = $modx->getService('modnodejs', 'modNodejs', $modx->getOption('core_path') .  'components/modnodejs/model/modnodejs/');
		$modNodejs->initialize($modx->context->key);
		break;
}