<?php
if(!is_auth()){
	show_error_page('not_auth');
}

$AA = new AuthApi;

$conf_mess = '';
if($auth_api->vk_api == 1 && isset($_GET['code']) && empty($_GET['fb_attach'])) {

	$result = false;
	$api_version = '5.73';

	$params = [
		'client_id'     => $auth_api->vk_id,
		'client_secret' => $auth_api->vk_key,
		'code'          => $_GET['code'],
		'redirect_uri'  => $full_site_host . 'settings',
		'v'             => $api_version
	];

	$vk_token = json_decode(
		file_get_contents_curl(
			'https://oauth.vk.com/access_token?'
				. urldecode(http_build_query($params))
		),
		true
	);

	if(isset($vk_token['access_token'])) {
		$params = [
			'user_id' => $vk_token['user_id'],
			'access_token' => $vk_token['access_token'],
			'v' => $api_version
		];

		$userInfo = json_decode(
			file_get_contents_curl(
				'https://api.vk.com/method/users.get?'
					. urldecode(http_build_query($params))
			),
			true
		);

		if(isset($userInfo['response'][0]['id'])) {
			$userInfo = $userInfo['response'][0];
			$result   = true;
		}
	}

	if ($result == true && $_GET['state'] == md5($SC->set_token())) {
		$vk_id = $userInfo['id'];
		$STH = $pdo->prepare("SELECT id FROM users WHERE vk_api=:vk_api LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':vk_api' => $vk_id]);
		$row = $STH->fetch();
		if(empty($row->id) && $AA->isAttachCacheCorrect($user->password)) {
			$STH = $pdo->prepare("UPDATE `users` SET `vk_api`=:vk_api, `vk`=:vk WHERE `id`=:id LIMIT 1");
			if(
				$STH->execute(
					[
						':vk_api' => $vk_id,
						':vk'     => 'id' . $vk_id,
						':id'     => $_SESSION['id']
					]
				) == '1'
			) {
				header('Location: ../settings#vk_area');
				exit();
			}
		}
	}

	$conf_mess = '<span class="m-icon icon-remove"></span> ' . $messages['Error'];
}

$conf_mess2 = '';
if($auth_api->steam_api == 1 && isset($_GET['steam_attach'])) {

	$result = false;
	$openid = new LightOpenID($host);

	if($openid->mode == 'cancel') {
		$conf_mess2 = '<span class="m-icon icon-remove"></span> ' . $messages['Error'];
	} else {
		if($openid->validate()) {
			preg_match(
				"/^https:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/",
				$openid->identity,
				$matches
			);
			$steam_api = $matches[1];
			$result    = true;
		} else {
			$conf_mess2 = '<span class="m-icon icon-remove"></span> ' . $messages['Error'];
		}
	}

	if($result == true && $_GET['state'] == md5($SC->set_token())) {
		$STH = $pdo->prepare("SELECT id FROM users WHERE steam_api=:steam_api LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':steam_api' => $steam_api]);
		$row = $STH->fetch();
		if(empty($row->id) && $AA->isAttachCacheCorrect($user->password)) {
			$STH = $pdo->prepare("UPDATE users SET steam_api=:steam_api WHERE id=:id LIMIT 1");
			if(
				$STH->execute(
					[
						':steam_api' => $steam_api,
						 ':id' => $_SESSION['id']
					]
				) == '1'
			) {
				header('Location: ../settings#steam_area');
				exit();
			}
		}
	}

	$conf_mess2 = '<span class="m-icon icon-remove"></span> ' . $messages['Error'];
}

$conf_mess3 = '';
if($auth_api->fb_api == 1 && isset($_GET['code']) && isset($_GET['fb_attach'])) {

	$result = false;
	$params = [
		'client_id'     => $auth_api->fb_id,
		'redirect_uri'  => $full_site_host . "settings?fb_attach=1",
		'client_secret' => $auth_api->fb_key,
		'code'          => $_GET['code']
	];

	$fb_token = null;
	$fb_token = json_decode(
		file_get_contents(
			str_replace(
				"&amp;",
				"&",
				'https://graph.facebook.com/oauth/access_token?'
					. http_build_query($params)
			)
		),
		true
	);

	if(isset($fb_token['access_token'])) {
		$params = ['access_token' => $fb_token['access_token']];
		$userInfo = json_decode(
			file_get_contents(
				str_replace(
					"&amp;",
					"&",
					'https://graph.facebook.com/me?'
						. urldecode(http_build_query($params))
				)
			),
			true
		);

		if(isset($userInfo['id'])) {
			$result = true;
		}
	}

	if($result == true && $_GET['state'] == md5($SC->set_token())) {
		$fb_api = $userInfo['id'];
		$STH = $pdo->prepare("SELECT id FROM users WHERE fb_api=:fb_api LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':fb_api' => $fb_api]);
		$row = $STH->fetch();
		if(empty($row->id) && $AA->isAttachCacheCorrect($user->password)) {
			$STH = $pdo->prepare("UPDATE users SET fb_api=:fb_api WHERE id=:id LIMIT 1");
			if(
				$STH->execute(
					[':fb_api' => $fb_api, ':id' => $_SESSION['id']]
				) == '1'
			) {
				header('Location: ../settings#fb_area');
				exit();
			}
		}
	}

	$conf_mess3 = '<span class="m-icon icon-remove"></span> '.$messages['Error'];
}

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $page->title);
$tpl->set("{name}", $conf->name);
$tpl->compile( 'title' );
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{site_name}", $conf->name);
$tpl->set("{image}", $page->image);
$tpl->set("{robots}", $page->robots);
$tpl->set("{type}", $page->kind);
$tpl->set("{description}", $page->description);
$tpl->set("{keywords}", $page->keywords);
$tpl->set("{url}", $page->url);
$tpl->set("{other}", '<script src="{site_host}modules/editors/tinymce/tinymce.min.js"></script>');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$menu = $tpl->get_menu($pdo);

$nav = array(
	$PI->to_nav('main', 0, 0),
	$PI->to_nav('users', 0, 0),
	$PI->to_nav('profile', 0, $_SESSION['id'], $user->login),
	$PI->to_nav('settings', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

include_once "inc/authorized.php";

$STH = $pdo->query("SELECT `referral_program`, `referral_percent` FROM `config__prices` LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
$ref = $STH->fetch();

if(empty($user->signature)) {
	$user->signature = '';
}
if(empty($user->fb)) {
	$user->fb = 0;
}
if(empty($user->fb_api)) {
	$user->fb_api = 0;
}

if(substr($user->password, 0, 5) == "none_") {
	$user->password = "none";
}

$editor_settings = get_editor_settings($pdo);
$tpl->load_template('/home/settings.tpl');
$tpl->set("{file_manager}", $editor_settings['file_manager']);
$tpl->set("{file_manager_theme}", $editor_settings['file_manager_theme']);
$tpl->set("{token}", $token);
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
$tpl->set("{referral_link}", $full_site_host.'?ref='.$_SESSION['id']);
$tpl->set("{referral_program}", $ref->referral_program);
$tpl->set("{referral_percent}", $ref->referral_percent);
$tpl->set("{login}", $user->login);
$tpl->set("{avatar}", $user->avatar);
$tpl->set("{regdate}", expand_date($user->regdate,2));
$tpl->set("{name}", $user->name);
$tpl->set("{nick}", $user->nick);
$tpl->set("{skype}", $user->skype);
$tpl->set("{discord}", $user->discord);
$tpl->set("{vk}", $user->vk);
$tpl->set("{vk_api}", $user->vk_api);
$tpl->set("{fb}", $user->fb);
$tpl->set("{fb_api}", $user->fb_api);
$tpl->set("{signature}", $user->signature);
$tpl->set("{steam_id}", $user->steam_id);
$tpl->set("{steam_api}", $user->steam_api);
$tpl->set("{telegram}", $user->telegram);
$birth = explode("-", $user->birth);
$birth_day = "";
for ($x = 31; $x > 0; $x--){
	$birth_day = $birth_day.'<option value="'.$x.'" ';
	if($birth[2]==$x) $birth_day = $birth_day.' selected';
	$birth_day = $birth_day.'>'.$x.'</option>';
}

$birth_month = "";
for ($x = 12; $x > 0; $x--){
	$birth_month = $birth_month.'<option value="'.$x.'" ';
	if($birth[1]==$x) $birth_month = $birth_month.' selected';
	$birth_month = $birth_month.'>'.get_month($x).'</option>';
}

$birth_year = "";
for ($x = (date('Y')); $x > 1899; $x--){
	$birth_year = $birth_year.'<option value="'.$x.'" ';
	if($birth[0]==$x) $birth_year = $birth_year.' selected';
	$birth_year = $birth_year.'>'.$x.'</option>';
}

$tpl->set("{birth_day}", $birth_day);
$tpl->set("{birth_month}", $birth_month);
$tpl->set("{birth_year}", $birth_year);

$act = get_active($user->im, 2);
$tpl->set("{im_radio_1}", $act[0]);
$tpl->set("{im_radio_2}", $act[1]);

$act = get_active($user->protect, 2);
$tpl->set("{protect_radio_1}", $act[0]);
$tpl->set("{protect_radio_2}", $act[1]);

$act = get_active($user->email_notice, 2);
$tpl->set("{notice_radio_1}", $act[0]);
$tpl->set("{notice_radio_2}", $act[1]);

$tpl->set("{conf_mess}", $conf_mess);
$tpl->set("{conf_mess2}", $conf_mess2);
$tpl->set("{conf_mess3}", $conf_mess3);
$tpl->compile( 'content' );
$tpl->clear();
?>