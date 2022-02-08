<?php
if(!is_admin()){
	show_error_page('not_adm');
}

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $page->title);
$tpl->set("{name}", $conf->name);
$tpl->compile( 'title' );
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{image}", $page->image);
$tpl->set("{other}", '');
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
	$PI->to_nav('admin_servers', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl', 1);

$tpl->load_template('page_top.tpl');
$tpl->set("{nav}", $nav);
$tpl->compile( 'content' );
$tpl->clear();

$servers = '<option value="0" data-server-type="0">Не импортировать</option>';
$STH = $pdo->query("SELECT `id`, `name`, `game` FROM `servers` WHERE `type` != '0' ORDER BY `trim`"); $STH->setFetchMode(PDO::FETCH_OBJ);  
while($row = $STH->fetch()) {
	if($row->game == 'Counter-Strike: 1.6') {
		$row->game = 0;
	} else {
		$row->game = 1;
	}
	$servers .= '<option value="'.$row->id.'" data-game="'.$row->game.'" class="disp-b">'.$row->name.'</option>';
}

$STH = $pdo->query("SELECT `mon_gap`, `mon_key`, `mon_api` FROM `config__secondary`"); $STH->setFetchMode(PDO::FETCH_OBJ);  
$row = $STH->fetch();
if($row->mon_api == 1){
	$act = 'active';
	$act2 = '';
} elseif ($row->mon_api == 2) {
	$act = '';
	$act2 = 'active';
}

$tpl->load_template('servers.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{servers}", $servers);
$tpl->set("{api_active}", $act);
$tpl->set("{api_active2}", $act2);
$tpl->set("{mon_gap}", $row->mon_gap);
$tpl->set("{mon_key}", $row->mon_key);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('bottom.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();
?>