<?php
$pages_urls = $AA->redirect_page($pdo);

if(!is_auth()) {
	$U = new Users($pdo);

	if(isset($_GET['ref'])) {
		$invited = clean($_GET['ref'], "int");
		$STH     = $pdo->prepare("SELECT id FROM users WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':id' => $invited]);
		$row = $STH->fetch();
		if(isset($row->id)) {
			$SC->set_cookie("invited", $row->id);
		}
	}

	$conf_mess = '';
	if(isset($_GET['id']) and isset($_GET['key'])) {
		$id   = clean($_GET['id'], "int");
		$code = clean($_GET['key'], null);

		$STH = $pdo->prepare("SELECT id, rights, password, login, protect, active, multi_account, invited FROM users WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':id' => $id]);
		$user = $STH->fetch();
		if(empty($user->id)) {
			$conf_mess = '<p class="text-danger">' . $messages['Account_does_not_exist'] . '</p>';
		} else {
			if($user->active == 1) {
				$conf_mess = '<p class="text-success">' . $messages['Account_has_been_activated'] . '</p>';
			} elseif($code == $U->convert_password($user->login, $conf->salt)) {
				$STH = $pdo->prepare("UPDATE users SET active=:active WHERE id='$id' LIMIT 1");
				$STH->execute(['active' => '1']);

				$ES = new EventsRibbon($pdo);
				$ES->new_user($user->id, $user->login);

				if($user->invited != 0) {
					$noty = new_referal($user->id, $user->login);
					send_noty($pdo, $noty, $user->invited, 2);
				}

				$U->auth_user(
					$SC,
					$user->protect,
					$user->password,
					$user->login,
					$user->id,
					$user->rights,
					$user->multi_account
				);
				header('Location: ../' . $pages_urls['main']);
				exit();
			} else {
				$conf_mess = '<p class="text-danger">' . $messages['Invalid_key'] . '</p>';
			}
		}
	}

	$reg                    = 0;
	$auth                   = 0;
	$user_info['vk_api']    = 0;
	$user_info['steam_api'] = 0;
	$user_info['fb_api']    = 0;

	$user_info['fb']       = 0;
	$user_info['steam_id'] = 0;

	if($auth_api->steam_api == 1) {
		if(isset($_GET['steam_reg']) or isset($_GET['steam_auth'])) {
			$openid = new LightOpenID($host);

			if($openid->mode == 'cancel') {
				$conf_mess = '<p class="text-danger">' . $messages['Error'] . '</p>';
			} else {
				if(isset($_GET['steam_reg'])) {
					if(isset($_GET['email'])) {
						$user_info['email'] = check($_GET['email'], null);
					}

					if(empty($user_info['email'])) {
						$conf_mess = '<p class="text-danger">' . $messages['Empty_email'] . '</p>';
					} elseif(!$U->check_email($user_info['email'])) {
						$conf_mess = '<p class="text-danger">' . $messages['Invalid_email'] . '</p>';
					} else {
						if($openid->validate()) {
							preg_match(
								"/^https:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/",
								$openid->__get("identity"),
								$matches
							);
							$user_info['steam_api'] = $matches[1];

							$content = file_get_contents_curl(
								"https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key="
								. $auth_api->steam_key
								. "&steamids=" . $user_info['steam_api']
							);
							$content = json_decode($content, true);

							$user_info['avatar']   = clean($content['response']['players'][0]['avatarfull'], null);
							$user_info['login']    = clean($content['response']['players'][0]['personaname'], null);
							$user_info['password'] = 'none';

							$reg = 1;
						} else {
							$conf_mess = '<p class="text-danger">' . $messages['Error'] . '</p>';
						}
					}
				} elseif(isset($_GET['steam_auth'])) {
					$user_info['vk_api'] = 0;

					if($openid->validate()) {
						preg_match(
							"/^https:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/",
							$openid->__get("identity"),
							$matches
						);
						$user_info['steam_api'] = $matches[1];
						$auth                   = 1;
					} else {
						$conf_mess = '<p class="text-danger">' . $messages['Error'] . '</p>';
					}
				} else {
					$conf_mess = '<p class="text-danger">' . $messages['Error'] . '</p>';
				}
			}
		}
	}
	if($auth_api->vk_api == 1) {
		if(isset($_GET['code']) && empty($_GET['fb_reg']) && empty($_GET['fb_auth'])) {
			$AA->redirect_fix('vk');
			$api_version = '5.73';
			$result      = false;
			$params      = [
				'client_id'     => $auth_api->vk_id,
				'client_secret' => $auth_api->vk_key,
				'code'          => $_GET['code'],
				'redirect_uri'  => $full_site_host . $pages_urls['main'],
				'v'             => $api_version
			];

			$vk_token = json_decode(
				file_get_contents_curl(
					str_replace(
						"&amp;",
						"&",
						'https://oauth.vk.com/access_token?' . urldecode(http_build_query($params))
					)
				),
				true
			);

			if(isset($vk_token['access_token'])) {
				$params = [
					'user_id'      => $vk_token['user_id'],
					'fields'       => 'id,first_name,last_name,photo_max,has_photo,bdate',
					'access_token' => $vk_token['access_token'],
					'v'            => $api_version
				];

				$userInfo = json_decode(
					file_get_contents_curl(
						str_replace(
							"&amp;",
							"&",
							'https://api.vk.com/method/users.get?' . urldecode(http_build_query($params))
						)
					),
					true
				);
				if(isset($userInfo['response'][0]['id'])) {
					$userInfo = $userInfo['response'][0];
					$result   = true;
				}
			}

			if($result) {
				$user_info['vk_api'] = $userInfo['id'];
				if($_GET['state'] == 'login') {
					$auth = 1;
				} else {
					if(isset($_GET['state'])) {
						$user_info['email'] = check($_GET['state'], null);
					}

					if(empty($user_info['email'])) {
						$conf_mess = '<p class="text-danger">' . $messages['Empty_email'] . '</p>';
					} elseif(!$U->check_email($user_info['email'])) {
						$conf_mess = '<p class="text-danger">' . $messages['Invalid_email'] . '</p>';
					} else {
						$user_info['vk']       = "id" . $userInfo['id'];
						$user_info['password'] = 'none';
						$user_info['login']    = clean($userInfo['first_name'] . " " . $userInfo['last_name'], null);
						if($userInfo['has_photo'] == 1) {
							$user_info['avatar'] = $userInfo['photo_max'];
						} else {
							$user_info['avatar'] = "files/avatars/no_avatar.jpg";
						}

						$reg = 1;
					}
				}
			} else {
				$conf_mess = '<p class="text-danger">' . $messages['Error'] . '</p>';
			}
		}
	}
	if($auth_api->fb_api == 1) {
		if(isset($_GET['code']) && (isset($_GET['fb_reg']) || isset($_GET['fb_auth']))) {
			$AA->redirect_fix('fb');

			$result = false;
			if(isset($_GET['fb_reg'])) {
				$method = 'fb_reg';
			} else {
				$method = 'fb_auth';
			}
			$params = [
				'client_id'     => $auth_api->fb_id,
				'redirect_uri'  => $full_site_host . $pages_urls['main'] . "?" . $method . "=1",
				'client_secret' => $auth_api->fb_key,
				'code'          => $_GET['code']
			];

			$fb_token = null;
			$fb_token = json_decode(
				file_get_contents(
					str_replace(
						"&amp;",
						"&",
						'https://graph.facebook.com/oauth/access_token?' . http_build_query($params)
					)
				),
				true
			);

			if(isset($fb_token['access_token'])) {
				$params   = ['access_token' => $fb_token['access_token']];
				$userInfo = json_decode(
					file_get_contents(
						str_replace(
							"&amp;",
							"&",
							'https://graph.facebook.com/me?' . urldecode(http_build_query($params))
						)
					),
					true
				);

				if(isset($userInfo['id'])) {
					$result = true;
				}
			}

			if($result) {
				$user_info['fb_api'] = $userInfo['id'];
				if(isset($_GET['fb_auth'])) {
					$auth = 1;
				} else {
					if(isset($_GET['state'])) {
						$user_info['email'] = check($_GET['state'], null);
					}

					if(empty($user_info['email'])) {
						$conf_mess = '<p class="text-danger">' . $messages['Empty_email'] . '</p>';
					} elseif(!$U->check_email($user_info['email'])) {
						$conf_mess = '<p class="text-danger">' . $messages['Invalid_email'] . '</p>';
					} else {
						$user_info['fb_api'] = $userInfo['id'];
						$user_info['password'] = 'none';
						$user_info['login']    = clean($userInfo['name'], null);
						$user_info['avatar']   = 'https://graph.facebook.com/' . $userInfo['id'] . '/picture?type=large';

						$reg = 1;
					}
				}
			} else {
				$conf_mess = '<p class="text-danger">' . $messages['Error'] . '</p>';
			}
		}
	}

	if($reg == 1) {
		if($user_info['vk_api'] != 0) {
			$user_info['fb_api']    = 0;
			$user_info['steam_api'] = 0;

			$STH = $pdo->prepare("SELECT id FROM users WHERE vk_api=:vk_api LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute([':vk_api' => $user_info['vk_api']]);
		} elseif($user_info['steam_api'] != 0) {
			$user_info['fb_api'] = 0;
			$user_info['vk']     = '---';
			$user_info['vk_api'] = 0;

			$STH = $pdo->prepare("SELECT id FROM users WHERE steam_api=:steam_api LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute([':steam_api' => $user_info['steam_api']]);
		} elseif($user_info['fb_api'] != 0) {
			$user_info['vk']        = '---';
			$user_info['vk_api']    = 0;
			$user_info['steam_api'] = 0;

			$STH = $pdo->prepare("SELECT id FROM users WHERE fb_api=:fb_api LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute([':fb_api' => $user_info['fb_api']]);
		}
		$row = $STH->fetch();
		if(isset($row->id)) {
			$auth = 1;
		} else {
			$STH = $pdo->prepare("SELECT id FROM users WHERE login=:login LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute([':login' => $user_info['login']]);
			$row = $STH->fetch();
			if(isset($row->id)) {
				$user_info['login'] = $AA->generate_login_str($pdo, $user_info['login']);
			}

			if(!$U->check_email_busyness($user_info['email'])) {
				$conf_mess = '<p class="text-danger">' . $messages['Email_already_registed'] . '</p>';
			} else {
				$user_info['regdate'] = date("Y-m-d H:i:s");
				if(
					isset($userInfo['bdate'])
					&& !empty($userInfo['bdate'])
					&& preg_match(
						"/^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}$/",
						$userInfo['bdate']
					)
				) {
					$birth = explode('.', $userInfo['bdate']);
				}
				if(isset($birth)) {
					$birth[0] = (int)$birth[0];
					$birth[1] = (int)$birth[1];

					if($birth[0] < 10) {
						$birth[0] = '0' . $birth[0];
					}
					if($birth[1] < 10) {
						$birth[1] = '0' . $birth[1];
					}
					$user_info['birth'] = $birth[2] . '-' . $birth[1] . '-' . $birth[0];
				} else {
					$user_info['birth'] = '1900-01-01';
				}

				if($user_info['avatar'] == 'http://vk.com/images/camera_b.gif') {
					$user_info['avatar'] = "files/avatars/no_avatar.jpg";
				} else {
					$date = time();
					$file = file_get_contents($user_info['avatar']);
					file_put_contents('files/avatars/' . $date . '.jpg', $file);
					$user_info['avatar'] = 'files/avatars/' . $date . '.jpg';
				}

				if(
					$U->entry_user(
						$user_info['login'],
						$user_info['password'],
						$user_info['email'],
						$conf->conf_us,
						$user_info['vk'],
						$user_info['vk_api'],
						$user_info['fb'],
						$user_info['fb_api'],
						$user_info['steam_id'],
						$user_info['steam_api'],
						$user_info['avatar'],
						$user_info['birth']
					)
				) {
					$answer = $U->after_registration_actions(
						$SC,
						$conf->salt,
						$conf->name,
						$user_info['login'],
						$full_site_host . $pages_urls['main']
					);

					if($answer['message'] != 'error') {
						$conf_mess = '<p class="text-success">' . $answer['message'] . '</p>';
						sendmail($user_info['email'], $answer['letter']['subject'], $answer['letter']['message'], $pdo);
					} else {
						$conf_mess = '<p class="text-danger">' . $messages['Error'] . '</p>';
					}

					if(isset($_SESSION['id'])) {
						header('Location: ../' . $pages_urls['main']);
						exit();
					}
				} else {
					$conf_mess = '<p class="text-danger">' . $messages['Error_You_are_not_logged_in'] . '</p>';
				}
			}
		}
	}

	if($auth == 1) {
		if($user_info['vk_api'] != 0) {
			$STH = $pdo->prepare("SELECT id,password,login,rights,active,protect,multi_account FROM users WHERE vk_api=:vk_api LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute([':vk_api' => $user_info['vk_api']]);
			$row   = $STH->fetch();
			$modal = $messages['Reg_modal_vk'];
		} elseif($user_info['steam_api'] != 0) {
			$STH = $pdo->prepare("SELECT id,password,login,rights,active,protect,multi_account FROM users WHERE steam_api=:steam_api LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute([':steam_api' => $user_info['steam_api']]);
			$row   = $STH->fetch();
			$modal = $messages['Reg_modal_steam'];
		} elseif($user_info['fb_api'] != 0) {
			$STH = $pdo->prepare("SELECT id,password,login,rights,active,protect,multi_account FROM users WHERE fb_api=:fb_api LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute([':fb_api' => $user_info['fb_api']]);
			$row   = $STH->fetch();
			$modal = $messages['Reg_modal_fb'];
		}

		if(isset($row->id)) {
			if($row->active != 1) {
				$conf_mess = '<p class="text-danger">' . $messages['please_act_account'] . '</p>';
			} else {
				$U->auth_user(
					$SC,
					$row->protect,
					$row->password,
					$row->login,
					$row->id,
					$row->rights,
					$row->multi_account
				);

				if(is_worthy("z")) {
					log_error($messages['Trying_to_auth_ban']);
					$SC->unset_user_session();

					$conf_mess = '<p class="text-danger">' . $messages['You_blocked_Try_later'] . '</p>';
				} elseif(is_worthy("x")) {
					log_error($messages['Trying_to_auth_ban']);
					$SC->unset_user_session();

					$STH = $pdo->prepare("INSERT INTO `users__blocked` (ip) values (:ip)");
					$STH->execute(['ip' => $ip]);
					$SC->set_cookie("point", "1");
					$conf_mess = '<p class="text-danger">' . $messages['You_blocked_Try_later'] . '</p>';
				} else {
					header('Location: ../' . $pages_urls['main']);
					exit();
				}
			}
		} else {
			$conf_mess = $modal . '<p class="text-danger">' . $messages['Account_does_not_exist'] . '</p>';
		}
	}

	$tpl->load_template('elements/title.tpl');
	$tpl->set("{title}", $page->title);
	$tpl->set("{name}", $conf->name);
	$tpl->compile('title');
	$tpl->clear();

	$tpl->load_template('head.tpl');
	$tpl->set("{title}", $tpl->result['title']);
	$tpl->set("{site_name}", $conf->name);
	$tpl->set("{image}", $page->image);
	$tpl->set("{robots}", $page->robots);
	$tpl->set("{type}", $page->kind);
	$tpl->set("{description}", $page->description);
	$tpl->set("{keywords}", $page->keywords);
	$tpl->set("{url}", $page->full_url);
	$tpl->set("{other}", '');
	$tpl->set("{token}", $token);
	$tpl->set("{cache}", $conf->cache);
	$tpl->set("{template}", $conf->template);
	$tpl->set("{site_host}", $site_host);
	$tpl->compile('content');
	$tpl->clear();

	$menu = $tpl->get_menu($pdo);

	$nav = [$PI->to_nav('main', 1, 0)];
	$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

	include_once "inc/not_authorized.php";

	$tpl->load_template('/index/body.tpl');
	$tpl->set("{site_host}", $site_host);
	$tpl->set("{conf_mess}", $conf_mess);
	$tpl->set("{template}", $conf->template);
	$tpl->compile('content');
	$tpl->clear();
} else {
	$tpl->load_template('elements/title.tpl');
	$tpl->set("{title}", $page->title);
	$tpl->set("{name}", $conf->name);
	$tpl->compile('title');
	$tpl->clear();

	$tpl->load_template('head.tpl');
	$tpl->set("{title}", $tpl->result['title']);
	$tpl->set("{site_name}", $conf->name);
	$tpl->set("{image}", $page->image);
	$tpl->set("{robots}", $page->robots);
	$tpl->set("{type}", $page->kind);
	$tpl->set("{description}", $page->description);
	$tpl->set("{keywords}", $page->keywords);
	$tpl->set("{url}", $page->full_url);
	$tpl->set("{other}", '<script src="{site_host}ajax/sound/jquery.jplayer.min.js"></script>');
	$tpl->set("{token}", $token);
	$tpl->set("{cache}", $conf->cache);
	$tpl->set("{template}", $conf->template);
	$tpl->set("{site_host}", $site_host);
	$tpl->compile('content');
	$tpl->clear();

	$menu = $tpl->get_menu($pdo);

	$nav = [$PI->to_nav('main', 1, 0)];
	$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

	include_once "inc/authorized.php";

	$tpl->result['notifications'] = '';
	$STH                          = $pdo->query("SELECT * FROM `notifications` WHERE `user_id`='$_SESSION[id]' and `status`='0' ORDER BY `date` DESC");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		if($row->type == 1) {
			$class = 'info';
		}
		if($row->type == 2) {
			$class = 'success';
		}
		if($row->type == 3) {
			$class = 'error';
		}

		$text = find_img_mp3($row->message, 1);
		$tpl->load_template('/elements/notification.tpl');
		$tpl->set("{class}", $class);
		$tpl->set("{date}", expand_date($row->date, 7));
		$tpl->set("{text}", $text);
		$tpl->set("{function}", 'close_notification');
		$tpl->set("{id}", $row->id);
		$tpl->compile('notifications');
		$tpl->clear();
		$tpl->result['notifications'] .= '<script>$("#notifications_line").removeClass("disp-n"); $("#notifications_line").addClass("disp-b");</script>';
	}

	$tpl->load_template('/home/index.tpl');
	$tpl->set("{notifications}", $tpl->result['notifications']);
	$tpl->set("{site_host}", $site_host);
	$tpl->set("{template}", $conf->template);
	$tpl->compile('content');
	$tpl->clear();

	$tpl->load_template('/home/right.tpl');
	$tpl->set("{login}", $_SESSION['login']);
	$tpl->set("{avatar}", $user->avatar);
	$tpl->set("{id}", $user->id);
	$tpl->set("{site_host}", $site_host);
	$tpl->set("{template}", $conf->template);
	$tpl->compile('content');
	$tpl->clear();
}
?>