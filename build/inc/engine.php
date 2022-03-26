<?PHP
	require('dictionary.php');
	
	if(!isset($protection)) {
		$protection = 1;
	}
	
	if(!file_exists(__DIR__ . '/configs')) {
		if(!mkdir(__DIR__ . '/configs')) {
			exit("Не удалось создать папку: inc/configs, создайте её вручную.");
		}
	}
	
	if($_SERVER['DOCUMENT_ROOT'] . '/robots.txt') {
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/robots.txt', "User-agent: *\nDisallow: /admin/\nHost: {$_SERVER['SERVER_NAME']}\nSitemap: https://{$_SERVER['SERVER_NAME']}/sitemap.xml");
	}
	
	require('start.php');
	require('protect.php');
	
	$user = new Users($pdo);
	$cookie = new SessionsCookies($conf->salt, $host);
	$token = $cookie->set_token();
	
	if(empty($_SERVER['HTTP_USER_AGENT'])) {
		$_SERVER['HTTP_USER_AGENT'] = 'undefined';
	}
	
	$ip = get_ip();
	
	if($conf->global_ban == 2 && isset($_COOKIE['point'])) {
		$cookie->set_cookie('point', '');
	}
	
	if(isset($_COOKIE['id']) && isset($_COOKIE['cache'])) {
		$_SESSION['cache'] = clean($_COOKIE['cache']);
		$_SESSION['id'] = clean($_COOKIE['id'], 'int');
	}
	else {
		$cookie->clean_user_session();
	}
	
	$sth = $pdo->prepare("SELECT `id` FROM `users__blocked` WHERE `ip`=:ip AND `date`=:date LIMIT 1");
	$sth->execute([
		':ip' => $ip, ':date' => '0000-00-00 00:00:00'
	]);
	
	$row = $sth->fetch(PDO::FETCH_OBJ);
	
	if(!empty($_COOKIE['point']) && empty($row->id)) {
		$pdo->prepare("INSERT INFO `users__blocked` (`ip`) VALUES (:ip)")->execute([
			':ip' => $ip
		]);
	}
	
	if(!empty($row->id) || !empty($_COOKIE['point'])) {
		if(!empty($_SESSION['id'])) {
			$pdo->exec("DELETE FROM `users__online` WHERE `user_id`='" . $_SESSION['id'] . "' LIMIT 1");
			
			$sth->prepare("UPDATE `users` SET `last_activity`=:last_activity WHERE `id`=:uid LIMIT 1")->execute([
				':last_activity' => date("Y-m-d H:i:s"),
				':uid' => $_SESSION['id']
			]);
			
			$cookie->unset_user_session();
		}
		
		$cookie->set_cookie('point', '1');
		http_response_code(403);
		die;
	}
	
	$time = time();
	$users_groups = get_groups($pdo);
	
	if(is_auth()) {
		$user = Users::getUserData($pdo, $_SESSION['id']);
		
		if(empty($user->id)) {
			require_once(__DIR__ . '/../modules/exit/index.php');
		}
		
		if($user->protect == 1) {
			$cookie->id = $ip;
		}
		
		$_SESSION['login'] = $user->login;
		$_SESSION['rights'] = $user->rights;
		$_SESSION['stickers'] = $user->stickers;
		
		if($_SESSION['cache'] != $cookie->get_cache($user->password) || $user->dell == 1 || is_worthy('z')) {
			require_once(__DIR__ . '/../modules/exit/index.php');
		}
		
		if(is_worthy('x')) {
			$ban = true;
			require_once(__DIR__ . '/../modules/exit/index.php');
		}
		
		$browser = md5($_SERVER['HTTP_USER_AGENT']);
		
		if($user->ip != $ip) {
			$pdo->prepare("UPDATE `users` SET `ip`=:ip WHERE `id`=:id LIMIT 1")->execute([
				':ip' => $ip, ':id' => $_SESSION['id']
			]);
		}
		
		if($user->browser != $browser) {
			$pdo->prepare("UPDATE `users` SET `browser`=:browser WHERE `id`=:id LIMIT 1")->execute([
				':browser' => $browser, ':id' => $_SESSION['id']
			]);
		}
		
		$sth = $pdo->prepare("SELECT `id` FROM `users__online` WHERE `user_id`=:uid LIMIT 1");
		$sth->execute([
			':uid' => $_SESSION['id']
		]);
		
		$tmp = $sth->fetch(PDO::FETCH_OBJ);
		
		if(empty($tmp->id)) {
			$pdo->prepare("INSERT INTO `users__online` (`user_id`, `time`) VALUES (:uid, :time)")->execute([
				':uid' => $_SESSION['id'], ':time' => $time
			]);
		}
		else {
			$pdo->prepare("UPDATE `users__online` SET `time`=:time WHERE `user_id`=:uid LIMIT 1")->execute([
				':uid' => $_SESSION['id'], ':time' => $time
			]);
		}
		
		if($conf->disp_last_online == 1) {
			$sth = $pdo->prepare("SELECT `id` FROM `last_online` WHERE `user_id`=:uid LIMIT 1");
			$sth->execute([
				':uid' => $_SESSION['id']
			]);
			
			$tmp = $sth->fetch(PDO::FETCH_OBJ);
			
			if(empty($tmp->id)) {
				$pdo->prepare("INSERT INTO `last_online` (`user_id`) VALUES (:uid)")->execute([
					':uid' => $_SESSION['id']
				]);
			}
		}
	}
	else {
		$_SESSION['stickers'] = 0;
	}
	
	$sth = $pdo->prepare("SELECT `user_id` FROM `users__online` WHERE (:time - `time`) > :inactive");
	$sth->execute([
		':time' => $time,
		':inactive' => $inactive_time
	]);
	
	while($row = $sth->fetch(PDO::FETCH_OBJ)) {
		$pdo->prepare("UPDATE `users` SET `last_activity`=:last_activity WHERE `id`=:uid LIMIT 1")->execute([
			':last_activity' => date("Y-m-d H:i:s", $time - $inactive_time),
			':uid' => $row->user_id
		]);
	}
	
	
	$pdo->prepare("DELETE FROM `users__online` WHERE (:time - `time`) > :inactive")->execute([
		':time' => $time,
		':inactive' => $inactive_time
	]);
	
	$AdminsManager = new AdminsManager;
	if($conf->dell_admin_time < date("Y-m-d H:i:s") && !$AdminsManager->dell_old_admins($pdo, $conf->name)) {
		log_error($messages['Admins_del_error']);
	}
	
	if($conf->date < date("Y-m-d")) {
		dell_old_users($pdo, $conf->name);
		$sth = $pdo->prepare("UPDATE `config` SET `date`=:date LIMIT 1");
		$sth->execute([
			':date' => date("Y-m-d H:i:s")
		]);
		
		$pdo->exec("TRUNCATE TABLE `last_online`");
		$AdminsManager->send_noty_for_admins($pdo, $conf->name);
		
		if(!$AdminsManager->dell_old_admins($pdo, $conf->name)) {
			log_error($messages['Admins_del_error']);
		}
	}
	
	unset($AdminsManager);
	
	if(strtotime($conf->date_cbr) < time()) {
		$result = @file_get_contents("https://www.cbr.ru/scripts/XML_daily.asp");
		if(strpos($result, '<ValCurs Date') !== false) {
			$xml = new SimpleXMLElement($result);
			
			foreach($xml->Valute as $val) {
				if($val->NumCode == 840) {
					$usd = clean($val->Value, 'float');
				}
			}
		}
		
		$pdo->prepare("UPDATE `config` SET `date_cbr`=:date, `usd`=:usd LIMIT 1")->execute([
			':usd' => $usd,
			':date' => date("Y-m-d H:i:s", strtotime("+30 minutes")),
		]);
		
		unset($result);
		unset($xml);
		unset($usd);
	}
	
	$STH = $pdo->query("SELECT * FROM modules WHERE active='1'");
	$STH->execute();
	$modules = $STH->fetchAll();
	$modules_files = "";
	
	if (isset($modules[0]["id"])) {
		for ($i = 0; $i < count($modules); $i++) {
			$modules_files .= $modules[$i]["files"] . "\n";
			if ($modules[$i]["tpls"] != "none") {
				$modules_tpls[$i] = explode(";", trim($modules[$i]["tpls"]));
				$modules_tpls[$i]["name"] = $modules[$i]["name"];
				
				for ($r = 0; $r < count($modules_tpls[$i]) - 1; $r++) {
					$modules_tpls[$i][$r] = trim($modules_tpls[$i][$r]);
					if ($modules_tpls[$i][$r] != "" && isset($modules_tpls[$i][$r])) {
						$modules_tpls[$i][$r] = explode(" ", $modules_tpls[$i][$r]);
					} else {
						unset($modules_tpls[$i][$r]);
					}
				}
				sort($modules_tpls[$i]);
			}
		}
		
		if (isset($modules_tpls) && count($modules_tpls) != 0) {
			sort($modules_tpls);
		}
		
		unset($modules);
	}
	
	$AA = new AuthApi();
	$auth_api = $AA->auth_api_info($pdo);
	$conf->template = get_template($conf);
	
	if (empty($_SESSION["news"]) || $_SESSION["time"] < time()) {
		$_SESSION["news"] = "";
		$_SESSION["time"] = time() + 180;
	}
	
	if (empty($_SESSION["topics"]) || $_SESSION["time"] < time()) {
		$_SESSION["topics"] = "";
		$_SESSION["time"] = time() + 180;
	}
	
	$STH = $pdo->query("SELECT admins_ids AS ids FROM config__secondary LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$main_admins = $STH->fetch();
	$vk_groups = explode(",", $conf->vk_group_id);
	$vk_admins = explode(",", $conf->vk_admin_id);
	$main_admins = explode(",", $main_admins->ids);
	
	if ($conf->stat == 1) {
		$file = $_SERVER["DOCUMENT_ROOT"] . "/logs/stat.log";
		if (strstr($_SERVER["HTTP_USER_AGENT"], "YandexBot")) {
			$bot = "YandexBot";
		}
		else {
			if (strstr($_SERVER["HTTP_USER_AGENT"], "Googlebot")) {
				$bot = "Googlebot";
			}
			else {
				$bot = $_SERVER["HTTP_USER_AGENT"];
			}
		}
		
		$date = date("H:i:s d.m.Y");
		$home = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		if (file_exists($file)) {
			$lines = file($file);
			while ($conf->stat_number < count($lines)) {
				array_shift($lines);
			}
		}
		$lines[] = $date . "|" . $bot . "|" . $ip . "|" . $home . "|\r\n";
		file_put_contents($file, $lines);
	}
	include_once __DIR__ . "/config_additional.php";
	$PI = new PagesInfo($pdo);
	$PI->full_host = $full_site_host;
	$page = $PI->page_info();
	$tpl = new Template();
	
	if ($page->type == 1) {
		$tpl->dir = "templates/" . $conf->template . "/tpl/";
	}
	else {
		$tpl->dir = "templates/admin/tpl/";
	}
	
	$tpl->files = $modules_files;
	unset($modules_files);
	
	if (isset($modules_tpls)) {
		$tpl->modules_tpls = $modules_tpls;
		unset($modules_tpls);
	}
	else {
		unset($tpl->modules_tpls);
	}
	
	require_once($page->file);
	
	switch($page->type) {
		case 2:
			$tpl->set("{content}", $tpl->result['content'])->load_template('main.tpl');
		break;
		default:
			$tpl->load_template("bottom.tpl")
			->set("{template}", $conf->template)
			->set("{site_host}", $full_site_host)
			->set("{site_name}", $conf->name)
			->set("{unigamecms_copyright}", 'Сайт работает на <a href="https://github.com/worksma/uni-gamecms">UNI GameCMS</a>')
			->compile('content');
			
			$tpl->clear();
			$tpl->set("{content}", $tpl->result['content'])->load_template('main.tpl');
		break;
	}
	
	$tpl->compile("main");
	eval(" ?>" . $tpl->result["main"] . "<?php ");
	$tpl->global_clear();