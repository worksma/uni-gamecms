<?PHP
	if($page->privacy == 1 && !is_auth()) {
		show_error_page('not_auth');
	}
		
	/*
		PHP отладка
	*/
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	/*
		Конец отладки
	*/

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
	$tpl->set("{other}", '<script src="../ajax/addons/playground/ajax-main.js?v={cache}"></script>');
	$tpl->set("{token}", $token);
	$tpl->set("{cache}", $conf->cache);
	$tpl->set("{template}", $conf->template);
	$tpl->set("{site_host}", $site_host);
	$tpl->compile( 'content' );
	$tpl->clear();
	
	$menu = $tpl->get_menu($pdo);

	$nav = array(
		$PI->to_nav('main', 0, 0),
		$PI->to_nav('playground', 1, 0)
	);
	$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

	if(isset($_SESSION['id'])) {
		include_once("inc/authorized.php");
	}
	else {
		include_once("inc/not_authorized.php");
	}
	
	if(isset($_GET['category'])) {
		$tpl->set("{category}", $_GET['category']);
	}
	else {
		$tpl->set("{category}", "");
	}
	
	$playground = new Playground($pdo, $conf);
	$tpl->set("{balance}", $playground->get_balance($_SESSION['id']) . ' ' . $playground->get_configs()->currency);
	$tpl->set("{course}", $playground->get_configs()->course);
	$tpl->load_template('playground/index.tpl');

	$tpl->set("{product}", "");
	$tpl->set("{page_index}", isset($_GET['page']) ? $_GET['page'] : 1);

	if(isset($_GET['page'])):
		$n_page = $_GET['page'];
	else:
		$n_page = 1;
	endif;

	if(isset($_GET['category'])):
		$rowCount = pdo()->query("SELECT * FROM `playground__product` WHERE `id_category`='".$playground->get_category($_GET['category'])->id."'")->rowCount();
	else:
		$rowCount = pdo()->query("SELECT * FROM `playground__product` WHERE 1")->rowCount();
	endif;

	$tpl->set("{pagination}", $tpl->get_paginator($n_page, $rowCount, $playground->get_configs()->limit_product, 3, (isset($_GET['category']) ? "/market?category={$_GET['category']}&" : "/market?")));

	$tpl->set("{site_host}", $site_host);
	$tpl->set("{template}", $conf->template);
	$tpl->compile( 'content' );
	$tpl->clear();