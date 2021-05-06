<?PHP
	// Powered by WORKSMA
	// https://github.com/worksma/uni-gamecms

	if (!isset($protection)) {
		$protection = 1;
	}
	require 'inc/db.php';
	require 'inc/config.php';
	require 'inc/functions.php';
	require "inc/protect.php";

	$U = new Users($pdo);
	$SC = new SessionsCookies($conf->salt, $host);
	$token = $SC->set_token();
	if (empty($_SERVER['HTTP_USER_AGENT'])) {
		$_SERVER['HTTP_USER_AGENT'] = 'undefined';
	}
	$ip = get_ip();
	if ($conf->global_ban == 2 && isset($_COOKIE['point'])) {
		$SC->set_cookie("point", "");
	}
	if (isset($_COOKIE['id']) && isset($_COOKIE['cache'])) {
		$_SESSION['cache'] = clean($_COOKIE['cache'], NULL);
		$_SESSION['id'] = clean($_COOKIE['id'], "int");
	} else {
		$SC->clean_user_session();
	}

	if (isset($work_time)) {
		unset($work_time);
	}
	if (isset($temp_code)) {
		unset($temp_code);
	}
	if (isset($temp_file)) {
		unset($temp_file);
	}
	if (isset($result)) {
		unset($result);
	}
	if (isset($code)) {
		unset($code);
	}

	$STH = $pdo->prepare("SELECT `id` FROM `users__blocked` WHERE `ip`=:ip AND `date`='0000-00-00 00:00:00' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':ip' => $ip));
	$row = $STH->fetch();

	if (!empty($_COOKIE['point']) && empty($row->id)) {
		$STH = $pdo->prepare("INSERT INTO `users__blocked` (ip) values (:ip)");
		$STH->execute(array('ip' => $ip));
	}
	if (!empty($row->id) or !empty($_COOKIE['point'])) {
		if (!empty($_SESSION['id'])) {
			$pdo->exec("DELETE FROM `users__online` WHERE `user_id`='$_SESSION[id]' LIMIT 1");
			$STH = $pdo->prepare("UPDATE `users` SET `last_activity`=:last_activity WHERE `id`='$_SESSION[id]' LIMIT 1");
			$STH->execute(array('last_activity' => date("Y-m-d H:i:s")));
			$SC->unset_user_session();
		}
		$SC->set_cookie("point", "1");
		
		exit('<h1>Access denied</h1><h4>try again later</h4>');
	}

	if (isset($_SESSION['admin']) && isset($_SESSION['admin_cache'])) {
		if ($conf->ip_protect == 1) {
			$SC->admin_ip = $ip;
		}
		
		$_SESSION['dev_mode'] = $dev_mode;
		if ($_SESSION['admin_cache'] != $SC->get_admin_cache($conf->password)) {
			$SC->clean_admin_session();
		}
	} else {
		$SC->clean_admin_session();
	}

	$time = time();
	$users_groups = get_groups($pdo);

	if (isset($_SESSION['id'])) {
		$STH = $pdo->prepare("SELECT * FROM `users` WHERE `id`=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':id' => $_SESSION['id']));
		
		$user = $STH->fetch();
		if (empty($user->id)) {
			include_once "modules/exit/index.php";
		}
		if ($user->protect == 1) {
			$SC->ip = $ip;
		}
		$_SESSION['login'] = $user->login;
		$_SESSION['rights'] = $user->rights;
		$_SESSION['stickers'] = $user->stickers;
		
		if (($_SESSION['cache'] != $SC->get_cache($user->password)) || ($user->dell == 1) || (is_worthy("z"))) {
			include_once "modules/exit/index.php";
		}
		if (is_worthy("x")) {
			$ban = true;
			include_once "modules/exit/index.php";
		}
		
		$browser = md5($_SERVER['HTTP_USER_AGENT']);
		
		if ($user->ip != $ip) {
			$STH = $pdo->prepare("UPDATE `users` SET `ip`=:ip WHERE `id`=:id LIMIT 1");
			$STH->execute(array(':ip' => $ip, ':id' => $_SESSION['id']));
		}
		if ($user->browser != $browser) {
			$STH = $pdo->prepare("UPDATE `users` SET `browser`=:browser WHERE `id`=:id LIMIT 1");
			$STH->execute(array(':browser' => $browser, ':id' => $_SESSION['id']));
		}
		$STH = $pdo->prepare("SELECT `id` FROM `users__online` WHERE `user_id`=:user_id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':user_id' => $_SESSION['id']));
		$tmp = $STH->fetch();
		
		if (empty($tmp->id)) {
			$STH = $pdo->prepare("INSERT INTO `users__online` (`user_id`, `time`) values (:user_id, :time)");
			$STH->execute(array('user_id' => $_SESSION['id'], 'time' => $time));
		} else {
			$STH = $pdo->prepare("UPDATE `users__online` SET `time`=:time WHERE `user_id`='$_SESSION[id]' LIMIT 1");
			$STH->execute(array(':time' => $time));
		}
		
		if ($conf->disp_last_online == 1) {
			$STH = $pdo->prepare("SELECT `id` FROM `last_online` WHERE `user_id`=:user_id LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':user_id' => $_SESSION['id']));
			
			$tmp = $STH->fetch();
			
			if (empty($tmp->id)) {
				$STH = $pdo->prepare("INSERT INTO `last_online` (`user_id`) values (:user_id)");
				$STH->execute(array('user_id' => $_SESSION['id']));
			}
		}
	} else {
		$_SESSION['stickers'] = 0;
	}
	$STH = $pdo->query("SELECT `user_id` FROM `users__online` WHERE ($time-time)>$inactive_time");
	$STH->execute();
	$row = $STH->fetchAll();
	$count = count($row);
	for ($i = 0; $i < $count; $i++) {
		$id = $row[$i]['user_id'];
		$STH = $pdo->prepare("UPDATE `users` SET `last_activity`=:last_activity WHERE `id`='$id' LIMIT 1");
		$STH->execute(array('last_activity' => date("Y-m-d H:i:s", ($time - $inactive_time))));
	}

	$pdo->exec("DELETE FROM `users__online` WHERE ($time-time)>$inactive_time");

	$AM = new AdminsManager;
	if ($conf->dell_admin_time < date("Y-m-d H:i:s")) {
		if (!$AM->dell_old_admins($pdo, $conf->name)) {
			log_error($messages['Admins_del_error']);
		}
	}
	if ($conf->date < date("Y-m-d")) {
		dell_old_users($pdo, $conf->name);
		$STH = $pdo->prepare("UPDATE `config` SET `date`=:date LIMIT 1");
		$STH->execute(array('date' => date("Y-m-d")));
		$pdo->exec("TRUNCATE TABLE `last_online`");
		$AM->send_noty_for_admins($pdo, $conf->name);
		if (!$AM->dell_old_admins($pdo, $conf->name)) {
			log_error($messages['Admins_del_error']);
		}
	}
	unset($AM);
	$STH = $pdo->query("SELECT * FROM `modules` WHERE active='1'");
	$STH->execute();
	$modules = $STH->fetchAll();
	$modules_files = '';
	if (isset($modules[0]['id'])) {
		for ($i = 0; $i < count($modules); $i++) {
			   $modules_files .= $modules[$i]['files'] . "\n";
				if ($modules[$i]['tpls'] != "none") {
					$modules_tpls[$i] = explode(";", trim($modules[$i]['tpls']));
					$modules_tpls[$i]['name'] = $modules[$i]['name'];
					for ($r = 0; $r < count($modules_tpls[$i]) - 1; $r++) {
						$modules_tpls[$i][$r] = trim($modules_tpls[$i][$r]);
						if ($modules_tpls[$i][$r] != '' and isset($modules_tpls[$i][$r])) {
							$modules_tpls[$i][$r] = explode(" ", $modules_tpls[$i][$r]);
						} else {
							unset($modules_tpls[$i][$r]);
						}
					}
					
					sort($modules_tpls[$i]);
				}
		}
		if (isset($modules_tpls) and count($modules_tpls) != 0) {
			sort($modules_tpls);
		}
		unset($modules);
	}
	$AA = new AuthApi;
	$auth_api = $AA->auth_api_info($pdo);
	$conf->template = get_template($conf);

	if (empty($_SESSION['news']) or $_SESSION['time'] < time()) {
		$_SESSION['news'] = '';
		$_SESSION['time'] = time() + 180;
	}

	if (empty($_SESSION['topics']) or $_SESSION['time'] < time()) {
		$_SESSION['topics'] = '';
		$_SESSION['time'] = time() + 180;
	}
	if (isset($_SESSION['rights'])) {
		$sessionrights = $_SESSION['rights'];
	} else {
		$sessionrights = 0;
		$_SESSION['rights'] = 0;
	}
	if (isset($_SESSION['id'])) {
		$sessionid = $_SESSION['id'];
	} else {
		$sessionid = null;
	}
	if (isset($_SESSION['admin'])) {
		$sessionadmin = $_SESSION['admin'];
	} else {
		$sessionadmin = null;
	}
	$STH = $pdo->query("SELECT `admins_ids` AS `ids` FROM `config__secondary` LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$main_admins = $STH->fetch();

	$vk_groups = explode(",", $conf->vk_group_id);
	$vk_admins = explode(",", $conf->vk_admin_id);
	$main_admins = explode(",", $main_admins->ids);

	if ($conf->stat == 1) {
		$file = $_SERVER['DOCUMENT_ROOT'] . "/logs/stat.log";
		if (strstr($_SERVER['HTTP_USER_AGENT'], 'YandexBot')) {
			$bot = 'YandexBot';
		}
		else if (strstr($_SERVER['HTTP_USER_AGENT'], 'Googlebot')) {
			$bot = 'Googlebot';
		}
		else {
			$bot = $_SERVER['HTTP_USER_AGENT'];
		}
		
		$date = date("H:i:s d.m.Y");
		$home = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if (file_exists($file)) {
			$lines = file($file);
			while (count($lines) > $conf->stat_number) array_shift($lines);
		}
		
		$lines[] = $date . "|" . $bot . "|" . $ip . "|" . $home . "|\r\n";
		
		file_put_contents($file, $lines);
	}

	include "inc/config_additional.php";
	
	class PagesInfo {
		public $full_host;
		private $full_url = null;
		private $pdo;

		function __construct($pdo) {
			if (!isset($pdo)) {
				exit('[Class PagesInfo]: No connection to the database');
			}
			
			$this->pdo = $pdo;
		}

		private function get_url() {
			if (isset($_SERVER["PATH_INFO"]) && !empty($_SERVER["PATH_INFO"])) {
				$this->full_url = trim($_SERVER["PATH_INFO"], "/");
				$url_info = trim($_SERVER["PATH_INFO"], "/");
				
				if (substr($url_info, -5) == 'index') {
					$url_info = trim(substr($url_info, 0, -5), "/");
				}
			}
			else {
				unset($url_info);
			}
			
			if (isset($_SERVER["REQUEST_URI"]) && !empty($_SERVER["REQUEST_URI"])) {
				$this->full_url = trim($_SERVER["REQUEST_URI"], "/");
				$url_info2 = trim(preg_replace('/\?.*/', '', $_SERVER["REQUEST_URI"]), "/");
				if (substr($url_info2, -5) == 'index') {
					$url_info2 = trim(substr($url_info2, 0, -5), "/");
				}
			}
			else {
				unset($url_info2);
			}
			
			if(isset($url_info)) {
				$url = $url_info;
			}
			else if (isset($url_info2)) {
				$url = $url_info2;
			}
			
			if (empty($url)) {
				$url = '';
			}
			
			return $url;
		}

		public function page_info($url = null) {
			if (empty($url)) {
				$url = $this->get_url();
			}
			
			$STH = $this->pdo->prepare("SELECT * FROM `pages` WHERE `url`=:url AND `active`='1' LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':url' => $url));
			$row = $STH->fetch();
			
			if (empty($row->id)) {
				if (substr_count($url, '/') > 1) {
					show_error_page('wrong_url');
				}
				$STH = $this->pdo->query("SELECT * FROM `pages` WHERE `url`='error_page' LIMIT 1");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$row = $STH->fetch();
				if (empty($row->id)) {
					exit('[Class PagesInfo]: Page not found');
				}
			}
			
			if (!file_exists($_SERVER["DOCUMENT_ROOT"] . '/' . $row->file)) {
				exit('[Class PagesInfo]: File of module not found');
			}
			
			$row->kind = $this->set_kind($row->kind);
			$row->image = $this->full_host . $row->image;
			$row->robots = $this->set_robots($row->robots);
			$row->full_url = $this->full_host . $this->full_url;
			
			if ($row->privacy != 0 && $row->privacy != 1 && $row->privacy != 2) {
				$row->privacy = 1;
			}
			
			return $row;
		}

		private function set_kind($kind) {
			switch ($kind) {
				case '1': return 'website'; break;
				case '2': return 'article'; break;
				case '3': return 'profile'; break;
				default: return 'website'; break;
			}
		}

		private function set_robots($robots) {
			switch ($robots) {
				case '1': return 'all'; break;
				case '2': return 'none'; break;
				default: return 'all'; break;
			}
		}

		public function to_nav($name, $point = 0, $id = 0, $second_name = '') {
			$STH = $this->pdo->prepare("SELECT `id`, `title`, `url` FROM `pages` WHERE `name`=:name LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':name' => $name));
			$row = $STH->fetch();
			
			if (empty($row->id)) {
				$array[0] = '../';
				$array[1] = 'none';
			}
			else {
				$array[0] = '../' . $row->url;
				$array[1] = $row->title;
			}
			
			if ($point == 1) {
				$array[0] = 'active';
			}
			
			if ($id != 0) {
				$array[0] .= '?id=' . $id;
			}
			
			if ($second_name != '') {
				$array[1] = $second_name;
			}
			
			return $array;
		}

		public function compile_str($str, $value) {
			return str_replace("{value}", $value, $str);
		}

		public function compile_keywords($str) {
			return str_replace(',,', ',', str_replace(' ', ',', $str));
		}

		public function compile_img_str($img) {
			return $this->full_host . $img;
		}
	}

	include 'inc/classes/Random/random.php';
	$PI = new PagesInfo($pdo);
	$PI->full_host = $full_site_host;
	$page = $PI->page_info();

	$tpl = new Template;
	if ($page->type == 1) {
		$tpl->dir = 'templates/' . $conf->template . '/tpl/';
	}
	else {
		$tpl->dir = 'templates/admin/tpl/';
	}

	$tpl->files = $modules_files;
	unset($modules_files);

	if (isset($modules_tpls)) {
		$tpl->modules_tpls = $modules_tpls;
		unset($modules_tpls);
	} else {
		unset($tpl->modules_tpls);
	}

	include $page->file;
		
	if ($page->type == 2) {
		$tpl->set('{content}', $tpl->result['content']);
		$tpl->load_template('main.tpl');
	} else {
		$tpl->load_template("bottom.tpl");
		$tpl->set("{template}", $conf->template);
		$tpl->set("{site_host}", $full_site_host);
		$tpl->set("{site_name}", $conf->name);
		$tpl->set("{gamecms_copyright}", "Copyright © 2021 <a href=\"https://worksma.ru\">WORKSMA</a>. All rights reserved.");
		$tpl->compile('content');
		$tpl->clear();
		$tpl->set('{content}', $tpl->result['content']);
		$tpl->load_template('main.tpl');
	}

	$tpl->compile('main');
	eval(' ?>' . $tpl->result['main'] . '<?php ');
	$tpl->global_clear();
	exit();