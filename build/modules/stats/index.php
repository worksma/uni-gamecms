<?php
if($page->privacy == 1 && !is_auth()) {
	show_error_page('not_auth');
}

if(isset($_GET['page'])){
	$number = clean($_GET['page'],"int");
} else {
	$number = 0;
}

$STH = $pdo->query("SELECT stats_lim FROM config__secondary LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
$row = $STH->fetch();
$limit = $row->stats_lim;

if($number){
	$start = ($number - 1) * $limit;
}else{
	$number = 0;
	$start = 0;
}

if(isset($_GET['server'])){
	$server = clean($_GET['server'], "int");
	$STH = $pdo->query("SELECT id,st_db_host,st_db_user,st_db_pass,st_db_db,st_type,st_db_table,ip,port FROM servers WHERE st_type!=0 and id='$server' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
} else {
	$STH = $pdo->query("SELECT id,st_db_host,st_db_user,st_db_pass,st_db_db,st_type,st_db_table,ip,port FROM servers WHERE st_type!=0 ORDER BY trim LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
}
$row = $STH->fetch();
if(empty($row->id)){
	$empty = 1;
	$server = 0;
	$error = '';
	$count = '';
	$stages = '';
	$page_name = '';
	$type = 0;
} else {
	$empty = 0;
	$server = $row->id;
	$page_name = "../stats?server=".$row->id."&";
	$db_host = $row->st_db_host;
	$db_user = $row->st_db_user;
	$db_pass = $row->st_db_pass;
	$db_db = $row->st_db_db;
	$type = $row->st_type;
	$table = $row->st_db_table;
	$ip = $row->ip;
	$port = $row->port;
	$count = 0;
	$error = "";

	if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
		$error = $messages['errorConnectingToDatabase'];
	} else {
		if($type == '1' or $type == '2') {
			$STH = $pdo2->query("SELECT COUNT(*) as count FROM csstats_players WHERE frags!=0");
		} elseif($type == '3') {
			$STH = $pdo2->query("SELECT COUNT(*) as count FROM $table WHERE kills!=0");
		} elseif($type == '4') {
			$STH = $pdo2->prepare("SELECT `game` FROM `hlstats_Servers` WHERE `address`=:address AND `port`=:port LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':address' => $ip, ':port' => $port ));
			$game = $STH->fetch();

			if(empty($game->game)) {
				$game = 'csgo';
			} else {
				$game = $game->game;
			}

			$STH = $pdo2->query("SELECT COUNT(*) as count FROM hlstats_Players WHERE kills!=0 AND game='$game'");
		} elseif($type == '5') {
			$STH = $pdo2->query("SELECT COUNT(*) as count FROM $table WHERE kills!=0");
		} elseif($type == '6') {
			$STH = $pdo2->query("SELECT COUNT(*) as count FROM $table");
		}

		$STH->setFetchMode(PDO::FETCH_ASSOC);
		$row = $STH->fetch();
		$count = $row['count'];
	}

	$stages = 3;
	if(($number*$limit - $count) > $limit){
		header('Location: ../stats');
		exit();
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
$tpl->set("{url}", $page->url);
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
	$PI->to_nav('stats', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

if(isset($_SESSION['id'])) {
	include_once "inc/authorized.php";
} else {
	include_once "inc/not_authorized.php";
}

$i = 0;
$servers = '';
$STH = $pdo->query("SELECT id,name FROM servers WHERE st_type!=0 ORDER BY trim"); $STH->setFetchMode(PDO::FETCH_OBJ);
while($row = $STH->fetch()) {
	if($row->id == $server){
		$servers .= '<li class="active"><a href="../stats?server='.$row->id.'">'.$row->name.'</a></li>';
	} else {
		if($i == 0 and empty($server)){
			$servers .= '<li class="active"><a href="../stats?server='.$row->id.'">'.$row->name.'</a></li>';
		} else {
			$servers .= '<li><a href="../stats?server='.$row->id.'">'.$row->name.'</a></li>';
		}
	}
	$i++;
}

$tpl->load_template('/home/stats.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
$tpl->set("{start}", $start);
$tpl->set("{limit}", $limit);
$tpl->set("{server}", $server);
$tpl->set("{error}", $error);
$tpl->set("{empty}", $empty);
$tpl->set("{servers}", $servers);
$tpl->set("{type}", $type);
$tpl->set("{pagination}", $tpl->get_paginator($number,$count,$limit,$stages,$page_name));
$tpl->compile( 'content' );
$tpl->clear();
?>