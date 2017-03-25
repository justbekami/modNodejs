<?php
/** @var modX $modx */
/** @var array $sources */

$settings = array();

$tmp = array(
    'token' => array(
        'xtype' => 'textfield',
        'value' => md5(uniqid()),
        'area' => 'modnodejs_main',
    ),
	 'host' => array(
        'xtype' => 'textfield',
        'value' => 'http://' . $_SERVER['HTTP_HOST'] . ':9090',
        'area' => 'modnodejs_main',
    ),
	'frontend_js' => array(
        'value' => 'web/default.js',
        'xtype' => 'textfield',
        'area' => 'modnodejs_frontend',
    ),
	'frontend_css' => array(
        'value' => 'web/default.css',
        'xtype' => 'textfield',
        'area' => 'modnodejs_frontend',
    ),
	'manager_js' => array(
        'value' => 'mgr/default.js',
        'xtype' => 'textfield',
        'area' => 'modnodejs_manager',
    ),
	'manager_css' => array(
        'value' => 'mgr/default.css',
        'xtype' => 'textfield',
        'area' => 'modnodejs_manager',
    ),
);

foreach ($tmp as $k => $v) {
    /** @var modSystemSetting $setting */
    $setting = $modx->newObject('modSystemSetting');
    $setting->fromArray(array_merge(
        array(
            'key' => 'modnodejs_' . $k,
            'namespace' => PKG_NAME_LOWER,
        ), $v
    ), '', true, true);

    $settings[] = $setting;
}
unset($tmp);

return $settings;
