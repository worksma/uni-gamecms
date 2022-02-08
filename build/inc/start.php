<?php
if(!isset($protection)) {
	$protection = 1;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/dictionary.php';
require_once __DIR__ . '/config_additional.php';

$SC    = new SessionsCookies($conf->salt, $host);
$token = $SC->set_token();

if(empty($_SERVER['HTTP_USER_AGENT'])) {
	$_SERVER['HTTP_USER_AGENT'] = 'undefined';
}

if(isset($_COOKIE['id']) && isset($_COOKIE['cache'])) {
	$_SESSION['cache'] = clean($_COOKIE['cache'], null);
	$_SESSION['id']    = clean($_COOKIE['id'], "int");
} else {
	$SC->clean_user_session();
}

if(isset($_SESSION['admin']) && isset($_SESSION['admin_cache'])) {
	if($conf->ip_protect == 1) {
		$SC->admin_ip = get_ip();
	}

	$_SESSION['dev_mode'] = $dev_mode;

	if($_SESSION['admin_cache'] != $SC->get_admin_cache($conf->password)) {
		$SC->clean_admin_session();
	}
} else {
	$SC->clean_admin_session();
}

$users_groups = get_groups($pdo);

if(is_auth()) {
	$user = Users::getUserData($pdo, $_SESSION['id']);

	if($user->protect == 1) {
		$SC->ip = get_ip();
	}

	$_SESSION['rights']   = $user->rights;
	$_SESSION['stickers'] = $user->stickers;

	if(
		empty($user->id)
		|| ($_SESSION['cache'] != $SC->get_cache($user->password))
		|| ($user->dell == 1)
		|| (is_worthy("z"))
	) {
		require_once __DIR__ . '/../modules/exit/index.php';
	}

	if(is_worthy("x")) {
		$ban = true;
		require_once __DIR__ . '/../modules/exit/index.php';
	}
} else {
	$_SESSION['stickers'] = 0;
}

$conf->template = get_template($conf);