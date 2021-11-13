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
	$tpl->set("{other}", '<script src="{site_host}modules/editors/tinymce/tinymce.min.js?v={cache}"></script>
	<script src="{site_host}ajax/addons/playground/ajax-admin.js?v={cache}"></script>');
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
		$PI->to_nav('admin_market', 1, 0)
	);
	$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl', 1);

	$tpl->load_template('page_top.tpl');
	$tpl->set("{nav}", $nav);
	$tpl->compile( 'content' );
	$tpl->clear();
	
	$playground = new Playground($pdo, $conf);
	
	$tpl->load_template('playground.tpl');
	$tpl->set("{site_host}", $site_host);
	$tpl->set("{currency}", $playground->get_configs()->currency);
	$tpl->set("{course}", $playground->get_configs()->course);
	$tpl->set("{secret}", $playground->get_configs()->secret);
	$tpl->set("{limit_product}", $playground->get_configs()->limit_product);
	$tpl->compile( 'content' );
	$tpl->clear();

	$tpl->load_template('bottom.tpl');
	$tpl->set("{site_host}", $site_host);
	$tpl->compile( 'content' );
	$tpl->clear();