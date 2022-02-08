<?PHP
	if(!is_admin()):
		show_error_page("not_adm");
	endif;

	/*
		Загрузка головной части
	*/
	tpl()
	->load_template("elements/title.tpl")
	->set("{title}", $page->title)
	->set("{name}", $conf->name)
	->compile("title")
	->clear();

	tpl()
	->load_template("head.tpl")
	->set("{title}", tpl()->result['title'])
	->set("{image}", $page->image)
	->set("{other}", "")
	->set("{token}", $token)
	->set("{cache}", $conf->cache)
	->set("{template}", $conf->template)
	->set("{site_host}", $site_host)
	->compile("content")
	->clear();

	tpl()
	->load_template("top.tpl")
	->set("{site_host}", $site_host)
	->set("{site_name}", $conf->name)
	->compile("content")
	->clear();

	/*
		Формирование меню и навигации
	*/
	tpl()
	->load_template("menu.tpl")
	->set("{site_host}", $site_host)
	->compile("content")
	->clear();

	$nav = [
		$PI->to_nav("admin", 0, 0),
		$PI->to_nav("admin_store", 0, 0),
		$PI->to_nav("admin_prefixes", 1, 0)
	];

	$nav = tpl()->get_nav($nav, "elements/nav_li.tpl", 1);

	tpl()
	->load_template("page_top.tpl")
	->set("{nav}", $nav)
	->compile("content")
	->clear();

	/*
		Загрузка класса Префиксов и работа с ним
	*/
	$prefixes		= new Prefixes();
	$cnf 			= $prefixes->conf();
	
	/*
		Загрузка контента
	*/
	tpl()
	->load_template("prefixes.tpl")
	->set("{site_host}", $site_host)
	->set("{template}", $conf->template)
	->set("{servers}", $prefixes->get_servers())
	->set("{bind_nick_pass}", $cnf->bind_nick_pass)
	->set("{bind_steam}", $cnf->bind_steam)
	->set("{bind_steam_pass}", $cnf->bind_steam_pass)
	->compile("content")
	->clear();

	tpl()
	->load_template("bottom.tpl")
	->set("{site_host}", $site_host)
	->compile("content")
	->clear();