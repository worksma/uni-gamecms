<?php

class Users {
	private $pdo;
	private $ban_time = 900;
	private $flood_time = 300;
	private $now = 300;

	function __construct($pdo = null) {
		$this->now = date("Y-m-d H:i:s");

		if(isset($pdo)) {
			$this->pdo = $pdo;
		}
	}

	public function convert_password($password, $salt = '') {
		$password = md5($password.$salt);
		$password = strrev($password);
		$password = $password."a";
		return $password;
	}

	public function check_to_invalid_auth($ip) {
		$this->pdo->exec("DELETE FROM `users__blocked` WHERE (`date` != '0000-00-00 00:00:00') AND (UNIX_TIMESTAMP('$this->now') - UNIX_TIMESTAMP(date) > $this->ban_time)");

		$STH = $this->pdo->prepare("SELECT `col` FROM `users__blocked` WHERE `ip`=:ip LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':ip' => $ip));
		$row = $STH->fetch();

		if(!isset($row->col)) {
			return 0;
		} else {
			return $row->col;
		}
	}

	public function up_invalid_auths($ip) {
		$STH = $this->pdo->prepare("SELECT `ip`, `col` FROM `users__blocked` WHERE `ip`=:ip LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':ip' => $ip));
		$tmp = $STH->fetch();

		if(isset($tmp->ip)) {
			$invalid_auths = $tmp->col + 1;
			$STH           = $this->pdo->prepare("UPDATE `users__blocked` SET `col`=:col, `date`=:date WHERE `ip`=:ip LIMIT 1");
			$STH->execute(array('col' => $invalid_auths, 'date' => date("Y-m-d H:i:s"), ':ip' => $ip));
		} else {
			$invalid_auths = 1;
			$STH           = $this->pdo->prepare("INSERT INTO `users__blocked` (`ip`,`date`,`col`) VALUES (:ip, :date, :col)");
			$STH->execute(array('ip' => $ip, 'date' => date("Y-m-d H:i:s"), 'col' => $invalid_auths));
		}

		return $invalid_auths;
	}

	public function dell_invalid_auths($ip) {
		$this->pdo->exec("DELETE FROM `users__blocked` WHERE `ip`='$ip' LIMIT 1");

		return true;
	}

	public function check_login_length($login) {
		if(
			isStringLengthLess($login, 3)
			|| isStringLengthMore($login, 30)
		) {
			return false;
		} else {
			return true;
		}
	}

	public function check_route_length($route) {
		if(
			isStringLengthLess($route, 3)
			|| isStringLengthMore($route, 32)
		) {
			return false;
		} else {
			return true;
		}
	}

	public function check_login_composition($login) {
		if(clean_str($login) != $login) {
			return false;
		} else {
			return true;
		}
	}

	public function check_route_composition($login) {
		if(preg_replace('/[^a-zA-Z0-9_-]/ui', '', $login) != $login) {
			return false;
		} else {
			return true;
		}
	}

	public function check_login_busyness($login, $id = 0) {
		$STH = $this->pdo->prepare("SELECT * FROM users WHERE login=:login LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':login' => $login]);
		$row = $STH->fetch();

		if($id == 0) {
			if(empty($row->id)) {
				return true;
			} else {
				return false;
			}
		} else {
			if(!empty($row->id) && $id != $row->id) {
				return false;
			} else {
				return true;
			}
		}
	}

	public function check_route_busyness($route, $id = 0) {
		$STH = $this->pdo->prepare("SELECT * FROM users WHERE route=:route LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':route' => $route]);
		$row = $STH->fetch();

		if($id == 0) {
			if(empty($row->id)) {
				return true;
			} else {
				return false;
			}
		} else {
			if(!empty($row->id) && $id != $row->id) {
				return false;
			} else {
				return true;
			}
		}
	}

	public function check_password_length($password) {
		if(mb_strlen($password, 'UTF-8') < 6 or mb_strlen($password, 'UTF-8') > 15) {
			return false;
		} else {
			return true;
		}
	}

	public function check_email($email) {
		if(
			preg_match(
				"/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/",
				$email
			)
		) {
			return true;
		} else {
			return false;
		}
	}

	public function check_email_busyness($email) {
		$STH = $this->pdo->query("SELECT `id` FROM `users` WHERE email='$email'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(isset($row->id)) {
			return false;
		} else {
			return true;
		}
	}

	public function check_busyness($name, $value, $id = 0) {
		$STH = $this->pdo->query("SELECT `id` FROM `users` WHERE $name='$value'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(!empty($row->id) && $row->id != $id) {
			return false;
		} else {
			return true;
		}
	}

	public function check_inviting() {
		if(isset($_COOKIE['invited'])) {
			$_COOKIE['invited'] = clean($_COOKIE['invited'], "int");
		}

		if(empty($_COOKIE['invited'])) {
			$_COOKIE['invited'] = 0;
		}

		$STH = $this->pdo->prepare("SELECT `id` FROM `users` WHERE `id`=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':id' => $_COOKIE['invited']));
		$row = $STH->fetch();
		if(isset($row->id)) {
			return $row->id;
		} else {
			return 0;
		}
	}

	public function entry_user(
		$login,
		$password,
		$email,
		$active,
		$vk = null,
		$vk_api = null,
		$fb = null,
		$fb_api = null,
		$steam_id = null,
		$steam_api = null,
		$avatar = null,
		$birth = null
	) {
		$invited = $this->check_inviting();
		$regdate = date("Y-m-d H:i:s");

		if($active == 2) {
			$active = 1;
		} elseif($active == 1) {
			$active = 0;
		}

		if(empty($avatar)) {
			$avatar = "files/avatars/no_avatar.jpg";
		}
		if(empty($birth)) {
			$birth = '1960-01-01';
		}
		if(empty($vk)) {
			$vk = '---';
		}
		if(empty($vk_api)) {
			$vk_api = 0;
		}
		if(empty($fb)) {
			$fb = 0;
		}
		if(empty($fb_api)) {
			$fb_api = 0;
		}
		if(empty($steam_id)) {
			$steam_id = 0;
		}
		if(empty($steam_api)) {
			$steam_api = 0;
		}

		if($password == "none") {
			$password .= "_".crate_pass(10, 2);
		}

		if(empty($_SERVER['HTTP_USER_AGENT'])) {
			$_SERVER['HTTP_USER_AGENT'] = 'undefined';
		}

		$browser = md5($_SERVER['HTTP_USER_AGENT']);
		$ip      = get_ip();

		$STH = $this->pdo->query("SELECT `stand_rights`, `stand_balance` FROM `config__secondary` LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row      = $STH->fetch();
		$rights   = $row->stand_rights;
		$shilings = $row->stand_balance;

		$STH = $this->pdo->prepare("INSERT INTO users (login,password,email,avatar,regdate,birth,rights,active,shilings,invited,signature,ip,browser,steam_id,steam_api,vk,vk_api,fb,fb_api) VALUES (:login, :password, :email, :avatar, :regdate, :birth, :rights, :active, :shilings, :invited, :signature, :ip, :browser, :steam_id, :steam_api, :vk, :vk_api, :fb, :fb_api)");
		$STH->execute(
			[
				'login'     => $login,
				'password'  => $password,
				'email'     => $email,
				'avatar'    => $avatar,
				'regdate'   => $regdate,
				'birth'     => $birth,
				'rights'    => $rights,
				'active'    => $active,
				'shilings'  => $shilings,
				'invited'   => $invited,
				'signature' => '',
				'ip'        => $ip,
				'browser'   => $browser,
				'steam_id'  => $steam_id,
				'steam_api' => $steam_api,
				'vk'        => $vk,
				'vk_api'    => $vk_api,
				'fb'        => $fb,
				'fb_api'    => $fb_api
			]
		);

		return $this->getUserByLoginPassword($login, $password);
	}

	public function getUserByLoginPassword($login, $password) {
		$STH = $this->pdo->prepare("SELECT * FROM users WHERE login=:login AND password=:password LIMIT 1");
		$STH->execute([':login' => $login, ':password' => $password]);

		return $STH->fetch(PDO::FETCH_OBJ);
	}

	public function after_registration_actions($SC, $salt, $site_name, $userId, $full_site_host) {
		incNotifications();

		$user = self::getUserData($this->pdo, $userId);

		if(empty($user->id)) {
			$answer['message'] = 'error';
			return $answer;
		}

		if($user->active == 1) {
			if($user->id == 1) {
				$STH = $this->pdo->prepare("UPDATE `users` SET `rights`=:rights WHERE `id`='$user->id' LIMIT 1");
				$STH->execute(array('rights' => '1'));
				$user->rights = 1;
			}

			$this->auth_user($SC, $user->protect, $user->password, $user->login, $user->id, $user->rights, $user->multi_account);
			welcome_noty($this->pdo, $user->login, $user->id);

			$ES = new EventsRibbon($this->pdo);
			$ES->new_user($user->id, $user->login);

			if($user->invited != 0) {
				$noty = new_referal($user->id, $user->login);
				send_noty($this->pdo, $noty, $user->invited, 2);
			}

			$answer['letter']  = reg_letter($site_name, $user->login);
			$answer['message'] = '<script>reset_page();</script>';
		} else {
			welcome_noty($this->pdo, $user->login, $user->id);

			$code = $this->convert_password($user->login, $salt);
			$link = $full_site_host."?id=".$user->id."&key=".$code;

			$answer['letter']  = reg_letter_with_key($site_name, $user->login, $link);
			$answer['message'] = 'Вы успешно зарегистрированы! Инструкция по активации аккаунта указана в письме, которое мы выслали на Ваш e-mail';
		}

		write_log("Зарегистрирован новый пользователь: ID ".$user->id);

		return $answer;
	}

	public function auth_user($SC, $protect, $password, $login, $id, $rights, $multi_account) {
		if($protect == 1) {
			$SC->ip = get_ip();
		}

		$_SESSION['cache']  = $SC->get_cache($password);
		$_SESSION['login']  = $login;
		$_SESSION['id']     = $id;
		$_SESSION['rights'] = $rights;

		$SC->set_user_cookie();
		$this->check_to_multi_account($multi_account);

		if(is_worthy("z")) {
			log_error($messages['Trying_to_auth_ban']);
			$SC->unset_user_session();

			return ['status' => false, 'response' => 'Вы заблокированы на 15 минут. Попробуйте позже'];
		} elseif(is_worthy("x")) {
			log_error($messages['Trying_to_auth_ban'] . ' (ip+cookies)');
			$SC->unset_user_session();

			$STH = $pdo->prepare("INSERT INTO `users__blocked` (`ip`) VALUES (:ip)");
			$STH->execute(array('ip' => $ip));
			$SC->set_cookie("point", "1");

			return ['status' => false, 'response' => 'Вы заблокированы'];
		} else {
			write_log("Авторизация на сайте");

			return ['status' => true];
		}
	}

	public function check_to_multi_account($multi_accounts, $id = 0) {
		if($id == 0) {
			if(empty($_SERVER['HTTP_USER_AGENT'])) {
				$_SERVER['HTTP_USER_AGENT'] = 'undefined';
			}

			$browser = md5($_SERVER['HTTP_USER_AGENT']);
			$ip      = get_ip();

			$STH = $this->pdo->prepare("UPDATE `users` SET `ip`=:ip, `browser`=:browser WHERE `id`=:id LIMIT 1");
			$STH->execute(array(':ip' => $ip, ':browser' => $browser, ':id' => $_SESSION['id']));
		} else {
			$STH = $this->pdo->prepare("SELECT `id`, `ip`, `browser`, `multi_account` FROM `users` WHERE `id`=:id LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':id' => $id));
			$row = $STH->fetch();

			if(empty($row->id)) {
				return true;
			}

			$multi_accounts = $row->multi_account;
			$browser        = $row->browser;
			$ip             = $row->ip;
		}

		$multi_accounts_new = array();
		$i                  = 0;

		if($id != 0) {
			$user_id = $id;
		} else {
			$user_id = $_SESSION['id'];
		}

		$STH = $this->pdo->prepare("SELECT `id`, `ip`, `browser` FROM `users` WHERE (`ip`=:ip AND `browser`=:browser) AND `id`!=:id AND `ip`!='127.0.0.1' AND `browser`!='5e543256c480ac577d30f76f9120eb74'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':ip' => $ip, ':browser' => $browser, ':id' => $user_id));
		while($row = $STH->fetch()) {
			$multi_accounts_new[$i]['id'] = $row->id;
			if($ip == $row->ip && $browser == $row->browser) {
				$multi_accounts_new[$i]['type'] = 3; //пока оставляем только этот вариант
			} elseif($browser == $row->browser) {
				$multi_accounts_new[$i]['type'] = 2;
			} else {
				$multi_accounts_new[$i]['type'] = 1;
			}
			$i++;
		}

		if($i != 0) {
			$multi_account_save = $multi_accounts;

			if($multi_accounts != 0) {
				$multi_accounts = explode(';', $multi_accounts);
				for($i = 0; $i < count($multi_accounts); $i++) {
					$multi_accounts[$i] = explode(':', $multi_accounts[$i]);

					for($j = 0; $j < count($multi_accounts_new); $j++) {
						if($multi_accounts_new[$j]['id'] == $multi_accounts[$i][0]) {
							if($multi_accounts_new[$j]['type'] > $multi_accounts[$i][1]) {
								$multi_accounts[$i][1] = $multi_accounts_new[$j]['type'];
							}
							$multi_accounts_new[$j]['id'] = 0;
						}
					}
				}
				$j = count($multi_accounts);
			} else {
				$multi_accounts = array();
				$j              = 0;
			}

			for($i = 0; $i < count($multi_accounts_new); $i++) {
				if($multi_accounts_new[$i]['id'] != 0) {
					$multi_accounts[$j][0] = $multi_accounts_new[$i]['id'];
					$multi_accounts[$j][1] = $multi_accounts_new[$i]['type'];
					$j++;
				}
			}

			$i                  = 0;
			$multi_accounts_str = '';
			while(mb_strlen($multi_accounts_str, 'UTF-8') <= 32 && !empty($multi_accounts[$i])) {
				$STH = $this->pdo->prepare("SELECT `id` FROM `users` WHERE `id`=:id LIMIT 1");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array(':id' => $multi_accounts[$i][0]));
				$row = $STH->fetch();
				if(isset($row->id)) {
					$multi_accounts_str .= $multi_accounts[$i][0].':'.$multi_accounts[$i][1].';';
				}
				$i++;
			}
			if($multi_accounts_str == '') {
				$multi_accounts_str = '0';
			} else {
				$multi_accounts_str = substr($multi_accounts_str, 0, -1);
			}

			if($multi_account_save != $multi_accounts_str) {
				$STH = $this->pdo->prepare("UPDATE `users` SET `multi_account`=:multi_account WHERE `id`=:id LIMIT 1");
				$STH->execute(array(':multi_account' => $multi_accounts_str, ':id' => $user_id));
			}
		}

		if($id == 0) {
			if(is_array($multi_accounts)) {
				for($i = 0; $i < count($multi_accounts); $i++) {
					if(!empty($multi_accounts[$i][0])) {
						$this->check_to_multi_account('0', $multi_accounts[$i][0]);
					}
				}
			}
		}

		return true;
	}

	public function dell_multi_account_relation($id, $multi_accounts) {
		if($multi_accounts == 0) {
			return '0';
		}

		$multi_accounts = explode(';', $multi_accounts);
		for($i = 0; $i < count($multi_accounts); $i++) {
			$multi_accounts[$i] = explode(':', $multi_accounts[$i]);
		}

		$i                  = 0;
		$multi_accounts_str = '';
		while(mb_strlen($multi_accounts_str, 'UTF-8') <= 32 && !empty($multi_accounts[$i])) {
			if($multi_accounts[$i][0] != $id) {
				$multi_accounts_str .= $multi_accounts[$i][0].':'.$multi_accounts[$i][1].';';
			}
			$i++;
		}
		if($multi_accounts_str == '') {
			$multi_accounts_str = '0';
		} else {
			$multi_accounts_str = substr($multi_accounts_str, 0, -1);
		}

		return $multi_accounts_str;
	}

	public function check_to_flood($captcha) {
		if($captcha != '2') {
			return true;
		}

		if(empty($_SERVER['HTTP_USER_AGENT'])) {
			$_SERVER['HTTP_USER_AGENT'] = 'undefined';
		}

		$browser = md5($_SERVER['HTTP_USER_AGENT']);
		$ip      = get_ip();

		$STH = $this->pdo->prepare("SELECT `id` FROM `users` WHERE (UNIX_TIMESTAMP('$this->now') - UNIX_TIMESTAMP(regdate) < $this->flood_time) AND `ip`=:ip AND `browser`=:browser LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':ip' => $ip, ':browser' => $browser));
		$row = $STH->fetch();

		if(isset($row->id)) {
			return false;
		} else {
			return true;
		}
	}

	public static function isUserExists($pdo, $userId = 0) {
		$STH = $pdo->prepare("SELECT id FROM users WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':id' => $userId]);
		$row = $STH->fetch();

		return (empty($row->id)) ? false : true;
	}

	public static function getUserData($pdo, $userId = 0) {
		$STH = $pdo->prepare("SELECT * FROM users WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':id' => $userId]);
		$row = $STH->fetch();

		return (empty($row->id)) ? false : $row;
	}

	public static function getIdByRoute($pdo, $route) {
		$STH = $pdo->prepare("SELECT id FROM users WHERE route=:route LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':route' => $route]);
		$row = $STH->fetch();

		return (empty($row->id)) ? false : $row;
	}

	public static function getRouteById($pdo, $userId = 0) {
		$STH = $pdo->prepare("SELECT route FROM users WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':id' => $userId]);
		$row = $STH->fetch();

		return (empty($row->route)) ? false : $row;
	}

	public static function isGroupStronger($newGroupId, $userId, $pdo) {
		$STH = $pdo->prepare("SELECT rights FROM users__groups WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':id' => $newGroupId ));
		$row = $STH->fetch();
		$newGroupRights = preg_replace('/[^a-zA-Z]/ui', '', $row->rights);

		$STH = $pdo->prepare(
			"SELECT 
    				users__groups.rights 
				FROM 
				    users
						INNER JOIN users__groups ON users.rights = users__groups.id
				WHERE users.id=:id LIMIT 1"
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':id' => $userId ));
		$row = $STH->fetch();
		$currentGroupRights = preg_replace('/[^a-zA-Z]/ui', '', $row->rights);

		if (mb_strlen($newGroupRights, 'UTF-8') > mb_strlen($currentGroupRights, 'UTF-8')) {
			return true;
		} else {
			return false;
		}
	}
}