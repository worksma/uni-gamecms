<?php
if(isset($_SESSION['error_msg'])) {
	$error_msg = clean($_SESSION['error_msg'], null);
} else {
	$error_msg = $messages['404'];
}

unset($_SESSION['error_msg']);

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
	$PI->to_nav('error_page', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

if(isset($_SESSION['id'])) {
	include_once "inc/authorized.php";
} else {
	include_once "inc/not_authorized.php";
}

$tpl->load_template('error.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
$tpl->set("{message}", $error_msg);
$tpl->compile( 'content' );
$tpl->clear();

if((isset($error_type) && $error_type == '404') || ($error_msg == $messages['404'])) {
	header("HTTP/1.1 404 Not Found");

	$tpl->load_template("bottom.tpl");
	$tpl->set("{template}", $conf->template);
	$tpl->set("{site_host}", $site_host);
	$tpl->set("{site_name}", $conf->name);
	$tpl->set("{gamecms_copyright}", "");
	$tpl->compile( 'content' );
	$tpl->clear();

	$tpl->set( '{content}', $tpl->result['content'] );
	$tpl->load_template('main.tpl');

	$tpl->compile('main');
	eval('?>'.$tpl->result['main'].'<?php ');
	$tpl->global_clear();
	exit();
}
?>