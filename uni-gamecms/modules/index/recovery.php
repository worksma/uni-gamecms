<?php
if(is_auth()){
	header('Location: ../');
	exit();
}

$message = '';
if(isset($_GET['data']) && isset($_GET['a'])) {
	$id = check($_GET['a'], "int");

	$STH = $pdo->prepare("SELECT `id`, `login`, `email`, `password` FROM `users` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $id ));
	$row = $STH->fetch();
	if(empty($row->id)) {
		show_error_page();
	}

	if($_GET['data'] != md5($row->id.$conf->salt.$row->password.$row->email.date("Y-m-d"))) {
		$message = '<p class=\'text-danger\'>'.$messages['Link_not_active'].'</p>';
	} else {
		$U = new Users($pdo);

		$password = crate_pass(7, 1);
		$password2 = $U->convert_password($password, $conf->salt);

		$STH = $pdo->prepare("UPDATE `users` SET `password`=:password WHERE `id`=:id LIMIT 1");
		if ($STH->execute(array( ':password' => $password2, ':id' => $id )) == '1') {
			include_once "inc/notifications.php";
			$letter = recovery_letter($conf->name, $row->login, $password);
			sendmail($row->email, $letter['subject'], $letter['message'], $pdo);
			$message = '<p class=\'text-success\'>'.$messages['Recovery_pass1'].$row->email.$messages['Recovery_pass2'].'</p>';
			write_log("User reinstated pass #".$row->email);
		} else {
			$message = '<p class=\'text-danger\'>'.$messages['Error_page'].'</p>';
		}
	}
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
$tpl->set("{url}", $page->full_url);
$tpl->set("{other}", '');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$menu = $tpl->get_menu($pdo);

$nav = array(
	$PI->to_nav('main', 0, 0),
	$PI->to_nav('recovery', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

include_once "inc/not_authorized.php";

$tpl->load_template('/index/recovery.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
$tpl->set("{message}", $message);
$tpl->compile( 'content' );
$tpl->clear();
?>