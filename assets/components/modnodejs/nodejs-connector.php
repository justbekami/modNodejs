<?php
define('MODX_API_MODE', true);
require $_SERVER['DOCUMENT_ROOT'] . '/config.core.php';
require MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();

$action = $_REQUEST['action'];
$ctx = $_REQUEST['data']['ctx'];
if(!$ctx || !$action) return;
$modx->initialize($ctx, array('anonymous_sessions' => false, 'session_enabled' => false));
$token = $_REQUEST['token'] == $modx->getOption('modnodejs_token');
if(!$token) return;
$response = array();

if($action == 'check_user') {

	// default response for auth
	$response = array(
		'id' => 0,
	    'username' => $modx->getOption('default_username'),
	);

	$PHPSESSID = $_REQUEST['data']['PHPSESSID'];
	if($session = $modx->getObject('modSession', $PHPSESSID)) {
		if($data = $session->get('data')) {
			session_start();
			session_decode($data);
			if($loginContexts = $_SESSION['modx.user.contextTokens']) {
				if(isset($loginContexts[$ctx]) && $user_id = $loginContexts[$ctx]) {
					if($user = $modx->getObject('modUser', $user_id)) {
						if($profile = $user->getOne('Profile')) {
							$response = array(
								'id' => $user->id,
								'username' => $user->username,
								'fullname' => $profile->fullname,
								'photo' => $profile->photo,
								'groups' => $user->getUserGroupNames(),
							);
						}
					}
				}
			}
		}
	}
} else {

	// check user and auth
	$user_id = $_REQUEST['data']['user_id'];
	if($user_id && $user_id != 0) {
		$modx->user = null;
		$modx->user = $modx->getObject('modUser', $user_id);
	}

	$modnodejs = $modx->getService('modnodejs');
	unset($_REQUEST['data']['ctx']);

	// invoke plugins
	$response = $modnodejs->invokeEvent('OnNodejsRequest', array(
		'action' => $action,
		'data' => $_REQUEST['data']
	));

}

if(empty($response)) {
	echo json_encode(array('success' => false));
} else {
	echo json_encode(array('success' => true, 'data' => $response));
}

exit;