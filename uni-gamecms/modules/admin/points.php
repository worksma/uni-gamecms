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
	$tpl->set("{other}", '<script src="{site_host}modules/editors/tinymce/tinymce.min.js"></script>');
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
		$PI->to_nav('admin_store_points', 1, 0)
	);
	$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl', 1);

	$tpl->load_template('page_top.tpl');
	$tpl->set("{nav}", $nav);
	$tpl->compile( 'content' );
	$tpl->clear();
	
		/* load product points */
	$q = $injSql->query("SELECT * FROM `points__product` WHERE 1");
	
	if($injSql->rows($q)) {
		while($row = $injSql->arr($q))
			$product_list .= "<div class=\"col-lg-3 mb-4\"><div class=\"card h-100\"><img src=\"{$row['file']}\" class=\"card-img-top\" style=\"width:100%;height:184px;\"><div class=\"card-body\"><h5 class=\"card-title h4\">{$row['name']}</h5><button class=\"btn btn-sm btn-danger btn-block\" OnClick=\"delPoints({$row['id']});\">Удалить</button></div></div></div>";
	}
	else {
		$product_list = "Товары отсутствуют.";
	}
	
	$q = $injSql->query("SELECT * FROM `points__category` WHERE 1");
	
	if($injSql->rows($q)) {
		while($row = $injSql->arr($q))
			$product_category .= "<option value=\"{$row['id']}\">{$row['name']}</option>";
	}
	else {
		$product_category = "<option selected disabled>Категория отсутствует.</option>";
	}
	
	$tpl->load_template('points.tpl');
	$tpl->set("{site_host}", $site_host);
	$tpl->set("{points_category}", $product_category);
	$tpl->set("{product_points}", $product_list);
	
	$tpl->compile( 'content' );
	$tpl->clear();

	$tpl->load_template('bottom.tpl');
	$tpl->set("{site_host}", $site_host);
	$tpl->compile( 'content' );
	$tpl->clear();