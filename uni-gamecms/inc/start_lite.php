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

if(is_auth()) {
	$row = Users::getUserData($pdo, $_SESSION['id']);

	if($row->protect == 1) {
		$SC->ip = get_ip();
	}

	$_SESSION['rights']   = $row->rights;
	$_SESSION['stickers'] = $row->stickers;

	if(
		empty($row->id)
		|| ($_SESSION['cache'] != $SC->get_cache($row->password))
		|| ($row->dell == 1)
	) {
		require_once __DIR__ . '/../modules/exit/index.php';
	}
} else {
	$_SESSION['stickers'] = 0;
}

$conf->template = get_template($conf);