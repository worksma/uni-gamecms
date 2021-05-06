<?php
if(!is_admin()){
	show_error_page('not_adm');
}
$server = 0;
if(isset($_GET['server'])) {
	$server = clean($_GET['server'],"int");

	$STH = $pdo->prepare("SELECT `id`,`binds`,`type` FROM `servers` WHERE `id`=:id and type != '0' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array( ':id' => $server ));
	$row = $STH->fetch();
	if(empty($row->id)) {
		show_error_page();
	}
} else {
	$STH = $pdo->query("SELECT `id`,`binds`,`type` FROM `servers` WHERE type != '0' ORDER BY trim LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(isset($row->id)) {
		$server = $row->id;
	}
}

if(isset($row->id)) {
	$server_type = $row->type;
	$binds = explode(';', $row->binds);
} else {
	$server_type = 0;
	$binds = array(0, 0, 0);
}
$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $page->title);
$tpl->set("{name}", $conf->name);
$tpl->compile( 'title' );
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{image}", $page->image);
$tpl->set("{other}", '<link rel="stylesheet" href="{site_host}templates/admin/css/timepicker.css">');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('top.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{site_name}", $conf->name);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('menu.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$nav = array(
	$PI->to_nav('admin', 0, 0),
	$PI->to_nav('admin_admins', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl', 1);

$tpl->load_template('page_top.tpl');
$tpl->set("{nav}", $nav);
$tpl->compile( 'content' );
$tpl->clear();

$servers = '';
$STH = $pdo->query("SELECT id,name,type FROM servers WHERE type != '0' ORDER BY trim"); $STH->setFetchMode(PDO::FETCH_OBJ);  
while($row = $STH->fetch()) {
	if($row->id == $server) {
		$servers .= '<option value="'.$row->id.'" title="'.$row->type.'" selected>'.$row->name.'</option>';
	} else {
		$servers .= '<option value="'.$row->id.'" title="'.$row->type.'">'.$row->name.'</option>';
	}
}

$tpl->load_template('admins.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{servers}", $servers);
$tpl->set("{server}", $server);
$tpl->set("{server_type}", $server_type);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('bottom.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();
?>