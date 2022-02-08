<?php
include_once '../inc/start_lite.php';

$AA       = new AuthApi;
$auth_api = $AA->auth_api_info($pdo);

/* Vk
=========================================*/
if(isset($_POST['get_vk_auth_link'])) {
	$url = '#';

	if($auth_api->vk_api == 1) {
		$pages_urls = $AA->redirect_page($pdo);

		$params = [
			'client_id'     => $auth_api->vk_id,
			'redirect_uri'  => $full_site_host . $pages_urls['main'],
			'display'       => 'popup',
			'response_type' => 'code',
			'state'         => 'login',
			'v'             => configs()->vk_api_version
		];

		$url = str_replace(
			"&amp;",
			"&",
			'https://oauth.vk.com/authorize?' . urldecode(http_build_query($params))
		);
	}

	exit(json_encode(['url' => $url]));
}
if(isset($_POST['attach_user_vk']) && is_auth()) {
	$url = '#';

	if($auth_api->vk_api == 1) {
		$pages_urls = $AA->redirect_page($pdo);
		$AA->setAttachCache($pdo);

		$params = [
			'client_id'     => $auth_api->vk_id,
			'redirect_uri'  => $full_site_host . $pages_urls['settings'],
			'display'       => 'popup',
			'response_type' => 'code',
			'state'         => md5($SC->set_token()),
			'v'             => configs()->vk_api_version
		];

		$url = str_replace(
			"&amp;",
			"&",
			'https://oauth.vk.com/authorize?' . urldecode(http_build_query($params))
		);
	}

	exit(json_encode(['url' => $url]));
}
/* Steam
=========================================*/
if(isset($_POST['get_steam_auth_link'])) {
	$url = '#';

	if($auth_api->steam_api == 1) {
		$pages_urls = $AA->redirect_page($pdo);

		try {
			$openid            = new LightOpenID($host);
			$openid->returnUrl = $full_site_host . $pages_urls['main'] . "?steam_auth=1";

			if(!$openid->mode) {
				$openid->__set("identity", "https://steamcommunity.com/openid");
				$url = $openid->authUrl();
			}
		} catch(ErrorException $e) {
			$url = '#';
		}
	}

	exit(json_encode(['url' => $url]));
}
if(isset($_POST['attach_user_steam']) && is_auth()) {
	$url = '#';

	if($auth_api->steam_api == 1) {
		$pages_urls = $AA->redirect_page($pdo);
		$AA->setAttachCache($pdo);

		try {
			$openid            = new LightOpenID($host);
			$openid->returnUrl = $full_site_host . $pages_urls['settings'] . "?steam_attach=1&state=" . md5($SC->set_token());

			if(!$openid->mode) {
				$openid->__set("identity", "https://steamcommunity.com/openid");
				$url = $openid->authUrl();
			}
		} catch(ErrorException $e) {
			$url = '#';
		}
	}

	exit(json_encode(['url' => $url]));
}
/* Facebook
=========================================*/
if(isset($_POST['get_fb_auth_link'])) {
	$url = '#';

	if($auth_api->fb_api == 1) {
		$pages_urls = $AA->redirect_page($pdo);

		$params = [
			'client_id'     => $auth_api->fb_id,
			'redirect_uri'  => $full_site_host . $pages_urls['main'] . "?fb_auth=1",
			'response_type' => 'code'
		];

		$url = str_replace(
			"&amp;",
			"&",
			'https://www.facebook.com/dialog/oauth?' . urldecode(http_build_query($params))
		);
	}

	exit(json_encode(['url' => $url]));
}
if(isset($_POST['attach_user_fb']) && is_auth()) {
	$url = '#';

	if($auth_api->fb_api == 1) {
		$pages_urls = $AA->redirect_page($pdo);
		$AA->setAttachCache($pdo);

		$params = [
			'client_id'     => $auth_api->fb_id,
			'redirect_uri'  => $full_site_host . $pages_urls['settings'] . "?fb_attach=1&state=" . md5($SC->set_token()),
			'response_type' => 'code'
		];

		$url = str_replace(
			"&amp;",
			"&",
			'https://www.facebook.com/dialog/oauth?' . urldecode(http_build_query($params))
		);
	}

	exit(json_encode(['url' => $url]));
}
/* Reg
=========================================*/
if(isset($_POST['reg_by_api'])) {
	$email = checkJs($_POST['email'], null);
	$type  = checkJs($_POST['type'], null);
	if(empty($email)) {
		exit(json_encode(['data' => '<p class="text-danger">Введите e-mail!</p>']));
	}

	$U = new Users($pdo);

	if(!$U->check_email($email)) {
		exit(json_encode(['data' => '<p class="text-danger">Неверно введен е-mail!</p>']));
	}
	if(!$U->check_email_busyness($email)) {
		exit(json_encode(['data' => '<p class="text-danger">Введеный Вами E-mail уже зарегистрирован!</p>']));
	}

	$pages_urls = $AA->redirect_page($pdo);

	if($type == 'vk') {
		if($auth_api->vk_api == 1) {
			$params = [
				'client_id'     => $auth_api->vk_id,
				'redirect_uri'  => $full_site_host . $pages_urls['main'],
				'display'       => 'popup',
				'response_type' => 'code',
				'state'         => $email,
				'v'             => '5.131'
			];
			$url    = 'https://oauth.vk.com/authorize?' . urldecode(http_build_query($params));
			$url    = str_replace("&amp;", "&", $url);
			exit(
				json_encode(
					[
						'data' => '<script>$("#api_reg_btn").fadeOut(0); document.location.href = "' . $url
							. '";</script><p class="text-success">Если Вас не перенаправило на сайт Вконтакте автоматически, то нажмите на ссылку: <a href="'
							. $url . '">перейти</a></p>'
					]
				)
			);
		} else {
			exit(json_encode(['data' => '<p class="text-danger">Регистрация через Вконтакте недоступна!</p>']));
		}
	} elseif($type == 'steam') {
		if($auth_api->steam_api == 1) {

			try {
				$openid            = new LightOpenID($host);
				$openid->returnUrl = $full_site_host . $pages_urls['main'] . "?steam_reg=1&email=" . $email;

				if(!$openid->mode) {
					$openid->__set("identity", "https://steamcommunity.com/openid");
					$url = $openid->authUrl();
				} else {
					exit(json_encode(['data' => '<p class="text-danger">Ошибка</p>']));
				}
			} catch(ErrorException $e) {
				exit(json_encode(['data' => '<p class="text-danger">Ошибка: ' . $e . '</p>']));
			}

			exit(
			json_encode(
				['data' => '<script>$("#api_reg_btn").fadeOut(0); document.location.href = "' . $url . '";</script><p class="text-success">Если Вас не перенаправило на сайт Steam автоматически, то нажмите на ссылку: <a href="' . $url . '">перейти</a></p>']
			)
			);
		} else {
			exit(json_encode(['data' => '<p class="text-danger">Регистрация через Steam недоступна!</p>']));
		}
	} elseif($type == 'fb') {
		if($auth_api->fb_api == 1) {

			$params = [
				'client_id'     => $auth_api->fb_id,
				'redirect_uri'  => $full_site_host . $pages_urls['main'] . "?fb_reg=1",
				'response_type' => 'code',
				'state'         => $email
			];
			$url    = 'https://www.facebook.com/dialog/oauth?' . urldecode(http_build_query($params));
			$url    = str_replace("&amp;", "&", $url);

			exit(
				json_encode(
					[
						'data' => '<script>$("#api_reg_btn").fadeOut(0); document.location.href = "' . $url
							. '";</script><p class="text-success">Если Вас не перенаправило на сайт Facebook автоматически, то нажмите на ссылку: <a href="'
							. $url . '">перейти</a></p>'
					]
				)
			);
		} else {
			exit(json_encode(['data' => '<p class="text-danger">Регистрация через Facebook недоступна!</p>']));
		}
	} else {
		exit(json_encode(['data' => '<p class="text-danger">Ошибка</p>']));
	}
}
/* Profile info
=========================================*/
if(isset($_POST['get_vk_profile_info'])) {
	$vk_api = checkJs($_POST['vk_api'], null);

	if(empty($vk_api) || $auth_api->vk_api == 2) {
		exit(json_encode(['avatar' => 'none', 'first_name' => 'none', 'last_name' => 'none']));
	}

	if(empty($auth_api->vk_service_key)) {
		$content['response'][0]['photo_50'] = null;
	} else {
		$content = file_get_contents_curl(
			"https://api.vk.com/method/users.get?user_id="
			. $vk_api . "&v=5.131&lang=ru&fields=photo_50&access_token="
			. $auth_api->vk_service_key . "&callback=?"
		);
		$content = json_decode($content, true);
	}

	if(!empty($content['response'][0]['photo_50'])) {
		$avatar     = $content['response'][0]['photo_50'];
		$first_name = clean($content['response'][0]['first_name'], null);
		$last_name  = clean($content['response'][0]['last_name'], null);
	} else {
		$avatar     = 'none';
		$first_name = $vk_api;
		$last_name  = '';
	}

	exit(json_encode(['avatar' => $avatar, 'first_name' => $first_name, 'last_name' => $last_name]));
}
if(isset($_POST['get_user_steam_info'])) {
	$steam_api = checkJs($_POST['steam_api'], null);

	if(empty($steam_api) || $auth_api->steam_api == 2) {
		exit(json_encode(['avatar' => '../files/avatars/no_avatar.jpg', 'login' => 'Неизвестно']));
	}

	$content = file_get_contents_curl(
		"https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key="
		. $auth_api->steam_key . "&steamids=" . $steam_api
	);

	$content = json_decode($content, true);

	exit(
		json_encode(
			[
				'avatar' => $content['response']['players'][0]['avatarfull'],
				'login'  => clean($content['response']['players'][0]['personaname'], null)
			]
		)
	);
}
if(isset($_POST['get_fb_profile_info'])) {
	$fb_api = checkJs($_POST['fb_api'], null);

	if(empty($fb_api) || $auth_api->fb_api == 2) {
		exit(json_encode(['login' => 'none']));
	}

	$content = file_get_contents_curl(
		'https://graph.facebook.com/' . $fb_api . '?fields=id,name&access_token=' . $auth_api->fb_id
		. '|' . $auth_api->fb_key . ''
	);
	
	$content = json_decode($content, true);

	if(isset($content['name'])) {
		exit(json_encode(['login' => clean($content['name'], null)]));
	} else {
		exit(json_encode(['login' => 'none']));
	}
}