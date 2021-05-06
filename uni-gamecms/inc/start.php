<?PHP
	if(!isset($protection)) {
		$protection = 1;
	}

	include_once 'db.php';
	include_once 'config.php';
	include_once 'functions.php';
	include_once 'dictionary.php';
	include_once "config_additional.php";

	$SC = new SessionsCookies($conf->salt, $host);
	$token = $SC->set_token();

	if(empty($_SERVER['HTTP_USER_AGENT'])) {
		$_SERVER['HTTP_USER_AGENT'] = 'undefined';
	}

	if(isset($_COOKIE['id']) && isset($_COOKIE['cache'])) {
		$_SESSION['cache'] = clean($_COOKIE['cache'], null);
		$_SESSION['id'] = clean($_COOKIE['id'], "int");
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

	if(isset($_SESSION['id'])) {
		$STH = $pdo->query("SELECT `id`, `stickers`, `proc`, `rights`, `email`, `password`, `protect`, `dell` FROM `users` WHERE `id`='$_SESSION[id]' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$user = $STH->fetch();

		if($user->protect == 1) {
			$SC->ip = get_ip();
		}

		$_SESSION['rights'] = $user->rights;
		$_SESSION['stickers'] = $user->stickers;

		if(empty($user->id) or ($_SESSION['cache'] != $SC->get_cache($user->password)) or ($user->dell == 1) or (is_worthy("z"))) {
			include_once $_SERVER["DOCUMENT_ROOT"]."/modules/exit/index.php";
		}

		if(is_worthy("x")) {
			$ban = true;
			include_once $_SERVER["DOCUMENT_ROOT"]."/modules/exit/index.php";
		}
	} else {
		$_SESSION['stickers'] = 0;
	}

	if(isset($_SESSION['id'])) {
		$sessionid = $_SESSION['id'];
	} else {
		$sessionid = null;
	}

	$conf->template = get_template($conf);