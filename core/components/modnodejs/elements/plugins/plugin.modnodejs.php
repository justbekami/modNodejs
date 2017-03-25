<?php
switch($modx->event->name) {
	case 'OnLoadWebDocument':
	case 'OnManagerPageBeforeRender':
		$modNodejs = $modx->getService('modnodejs');
		$modNodejs->initialize($modx->context->key);
		break;
}