<?PHP
	if($page->privacy == 1 && !is_auth()):
		show_error_page("not_auth");
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
	->set("{site_name}", $conf->name)
	->set("{image}", $page->image)
	->set("{robots}", $page->robots)
	->set("{type}", $page->kind)
	->set("{description}", $page->description)
	->set("{keywords}", $page->keywords)
	->set("{url}", $page->full_url)
	->set("{other}", '')
	->set("{token}", $token)
	->set("{cache}", $conf->cache)
	->set("{template}", $conf->template)
	->set("{site_host}", $site_host)
	->compile("content")
	->clear();

	/*
		Формирование меню и навигации
	*/
	$menu = tpl()->get_menu(pdo());

	$nav = [
		$PI->to_nav("main", 0, 0),
		$PI->to_nav("store", 0, 0),
		$PI->to_nav("prefixes", 1, 0)
	];

	$nav = tpl()->get_nav($nav, "elements/nav_li.tpl");

	include_once(isset($_SESSION['id']) ? "inc/authorized.php" : "inc/not_authorized.php");

	/*
		Загрузка класса Префиксов и работа с ним
	*/
	$prefixes	= new Prefixes();
	$cnf 		= $prefixes->conf();

	/*
		Загрузка контента
	*/
	tpl()
	->load_template("/home/prefixes.tpl")
	->set("{site_host}", $site_host)
	->set("{template}", $conf->template)
	->set("{servers}", $prefixes->get_servers())
	->set("{term}", $prefixes->get_term(null))
	->set("{nick}", ((empty($user->nick) || $user->nick == '---') ? "" : $user->nick))
	->set("{steam_id}", ((empty($user->steam_id) || $user->steam_id == '---') ? "" : $user->steam_id))
	->set("{bind_nick_pass}", $cnf->bind_nick_pass)
	->set("{bind_steam}", $cnf->bind_steam)
	->set("{bind_steam_pass}", $cnf->bind_steam_pass)
	->compile("content")
	->clear();