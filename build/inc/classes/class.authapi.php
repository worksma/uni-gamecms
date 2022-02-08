<?php
class AuthApi {
	function auth_api_info($pdo) {
		$STH = $pdo->query("SELECT `vk_api`, `vk_id`, `vk_key`, `vk_service_key`, `steam_api`, `steam_key`, `fb_api`, `fb_id`, `fb_key` FROM `config__secondary` LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$auth_api = $STH->fetch();
		return $auth_api;
	}

	function generate_login_str($pdo, $login){
		$i = 0;
		$user = $login;
		do {
			if($i != 0) {
				$user = $login.'('.$i.')';
			}
			$STH = $pdo->prepare("SELECT `id` FROM `users` WHERE `login`=:login LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':login' => $user ));
			$row = $STH->fetch();
			if(isset($row->id)) {
				$temp = null;
			} else {
				$temp = 1;
			}
			$i++;
		} while (empty($temp));
		return $user;
	}

	function redirect_fix($type){
		if (!isset($_SESSION['reg_session_'.$type])) {
			$_SESSION['reg_session_'.$type] = 1;
		}
		if($_SESSION['reg_session_'.$type] == 3){
			unset($_SESSION['reg_session_'.$type]);
			header('Location: ../index');
			exit();
		}
		$_SESSION['reg_session_'.$type]++;
	}

	function redirect_page($pdo) {
		$pages = array();
		$STH = $pdo->query("SELECT `url`, `name` FROM `pages` WHERE `name`='main' OR `name`='settings'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		while($row = $STH->fetch()) {
			if(empty($row->url)) {
				$row->url = "index";
			}
			$pages[$row->name] = $row->url;
		}

		return $pages;
	}

	function setAttachCache($pdo) {
		$STH = $pdo->prepare("SELECT password FROM users WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':id' => $_SESSION['id'] ));
		$row = $STH->fetch();

		$_SESSION['attachCache'] = md5($_SESSION['id'].$row->password);
	}

	function isAttachCacheCorrect($password) {
		if(isset($_SESSION['attachCache']) && ($_SESSION['attachCache'] == md5($_SESSION['id'].$password))) {
			return true;
		} else {
			return false;
		}
	}
}