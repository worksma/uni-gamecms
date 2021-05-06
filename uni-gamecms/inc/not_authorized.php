<?PHP
	if(empty($menu)) {
		$menu = $tpl->get_menu($pdo);
	}

	$tpl->load_template("/index/top.tpl");
	$tpl->set("{site_host}", $site_host);
	$tpl->set("{template}", $conf->template);
	$tpl->set("{site_name}", $conf->name);
	$tpl->set("{menu}", $menu);
	$tpl->set("{conf_mess}", "");
	$tpl->set("{page_name}", $nav);
	$tpl->compile( "content" );
	$tpl->clear();