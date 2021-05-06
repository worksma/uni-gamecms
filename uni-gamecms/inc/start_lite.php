<?PHP
	if(!isset($protection)) {
		$protection = 1;
	}

	include_once 'db.php';
	include_once 'config.php';
	include_once 'functions.php';
	include_once "config_additional.php";

	$SC = new SessionsCookies($conf->salt, $host);
	$token = $SC->set_token();

	if(empty($_SERVER['HTTP_USER_AGENT'])) {
		$_SERVER['HTTP_USER_AGENT'] = 'undefined';
	}

	if (isset($_COOKIE['id']) && isset($_COOKIE['cache'])){
		$_SESSION['cache'] = clean($_COOKIE['cache'],NULL);
		$_SESSION['id'] = clean($_COOKIE['id'],"int");
	} else {
		$SC->clean_user_session();
	}

	if(isset($_SESSION['id'])){
		$STH = $pdo->query("SELECT `id`, `stickers`, `rights`, `password`, `protect`, `dell` FROM `users` WHERE `id`='$_SESSION[id]' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();

		if($row->protect == 1) {
			$SC->ip = get_ip();
		}

		$_SESSION['rights'] = $row->rights;
		$_SESSION['stickers'] = $row->stickers;

		if(empty($row->id) or ($_SESSION['cache'] != $SC->get_cache($row->password)) or ($row->dell == 1)){
			include_once "../modules/exit/index.php";
		}
	} else {
		$_SESSION['stickers'] = 0;
	}

	if(isset($_SESSION['rights'])) {
		$sessionrights = $_SESSION['rights'];
	} else {
		$sessionrights = 0;
		$_SESSION['rights'] = 0;
	}
	if(isset($_SESSION['id'])) {
		$sessionid = $_SESSION['id'];
	} else {
		$sessionid = null;
	}
	if(isset($_SESSION['admin'])) {
		$sessionadmin = $_SESSION['admin'];
	} else {
		$sessionadmin = null;
	}

	$conf->template = get_template($conf);