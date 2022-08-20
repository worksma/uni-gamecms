<?PHP
	if(is_auth()) {
		redirect("/");
	}
	
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
	->set("{description}", $page->description)
	->set("{keywords}", $page->keywords)
	->set("{url}", $page->full_url)
	->set("{other}", '')
	->set("{token}", $token)
	->set("{cache}", $conf->cache)
	->set("{template}", $conf->template)
	->set("{site_host}", $site_host)
	->set("{site_name}", $conf->name)
	->set("{robots}", $conf->robots)
	->set("{type}", $conf->kind)
	->compile("content")
	->clear();
	
	$menu = tpl()->get_menu(pdo());
	
	$nav = [
		$PI->to_nav("main", 0, 0),
		$PI->to_nav("login", 1, 0)
	];
	
	$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');
	
	if(isset($_SESSION['id'])) {
		include_once("inc/authorized.php");
	}
	else {
		include_once("inc/not_authorized.php");
	}
	
	tpl()
	->load_template("index/login.tpl")
	->set("{site_host}", $site_host)
	->set("{template}", configs()->template)
	->compile("content")
	->clear();