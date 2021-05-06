<?PHP
	if($page->privacy == 1 && !is_auth()) {
		show_error_page('not_auth');
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
		$PI->to_nav('store', 1, 0)
	);
	$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

	if(isset($_SESSION['id'])) {
		include_once("inc/authorized.php");
	}
	else {
		include_once("inc/not_authorized.php");
	}
	
		/* load product points */
	if(isset($_GET['category'])) {
		$q = $injSql->query("SELECT * FROM `points__product` WHERE `category`='{$_GET['category']}'");
	}
	else {
		$q = $injSql->query("SELECT * FROM `points__product` ORDER BY `category` ASC");
	}
	
	if($injSql->rows($q)) {
		while($row = $injSql->arr($q))
			$product_list .= "<div class=\"col-lg-4 card-to-points\"><div class=\"card\"><img src=\"{$row['file']}\" class=\"card-img-top\" style=\"height:239.833px;\"><div class=\"category-point\"><a href=\"?category={$row['category']}\" class=\"badge badge-pill badge-dark\">".$injSql->fqr(["SELECT * FROM `points__category` WHERE `id`='".$row['category']."'",'name'])."</a></div><div class=\"card-body\"><h5 class=\"card-title h4\">{$row['name']}</h5><button class=\"btn btn-sm btn-success btn-block\" OnClick=\"buyPoints({$row['id']});\"><i class=\"fab fa-monero\"></i> {$row['price']}</button></div></div></div>";
	}
	else {
		$product_list = "<div class=\"col-lg-12\"><center>Товары отсутствуют.</center></div>";
	}
	
	$q = $injSql->query("SELECT * FROM `points__category` WHERE 1");
	
	if($injSql->rows($q)) {
		$product_category = "<li class=\"{act-0}\"><a href=\"/store/points\">Все</a></li>";
		
		while($row = $injSql->arr($q))
			$product_category .= "<li><a href=\"?category={$row['id']}\" class=\"{act-{$row['id']}}\">{$row['name']}</a></li>";
		
		if(isset($_GET['category']) && $_GET['category'] > 0) {
			$product_category = str_replace("{act-{$_GET['category']}}", "active", $product_category);
		}
		else {
			$product_category = str_replace("{act-0}", "active", $product_category);
		}
	}
	else {
		$product_category = "<center>Категории отсутствуют.</center>";
	}
	
	$tpl->load_template('/home/points.tpl');
	$tpl->set("{site_host}", $site_host);
	$tpl->set("{template}", $conf->template);
	$tpl->set("{product_points_list}", $product_list);
	$tpl->set("{category_points_list}", $product_category);
	$tpl->set("{points}", $user->points);
	$tpl->compile( 'content' );
	$tpl->clear();