<?PHP
	if(is_worthy("p")){
		$STH = $pdo->query("SELECT `id` FROM `tickets` WHERE `have_answer`='0'");
		$STH->execute();
		$tickets = $STH->fetchAll();
		$tickets = count($tickets);
	} else {
		$tickets = 0;
	}
	if(is_worthy("i")){
		$STH = $pdo->query("SELECT `id` FROM `bans` WHERE `have_answer`='0'");
		$STH->execute();
		$bans = $STH->fetchAll();
		$bans = count($bans);
	} else {
		$bans = 0;
	}

	if(empty($menu)) {
		$menu = $tpl->get_menu($pdo);
	}

	$tpl->load_template('/home/top.tpl');
	$tpl->set("{site_host}", $site_host);
	$tpl->set("{template}", $conf->template);
	$tpl->set("{site_name}", $conf->name);
	$tpl->set("{group}", $users_groups[$_SESSION['rights']]['name']);
	$tpl->set("{user_id}", $user->id);
	$tpl->set("{login}", $user->login);
	$tpl->set("{balance}", $user->shilings);
	$tpl->set("{proc}", $user->proc);
	$tpl->set("{avatar}", $user->avatar);
	$tpl->set("{menu}", $menu);
	$tpl->set("{tickets}", $tickets);
	$tpl->set("{bans}", $bans);
	$tpl->set("{nav}", $nav);
	$tpl->compile( 'content' );
	$tpl->clear();

	$tpl->load_template('/home/left.tpl');
	$tpl->set("{site_host}", $site_host);
	$tpl->set("{template}", $conf->template);
	$tpl->set("{site_name}", $conf->name);
	$tpl->set("{group}", $users_groups[$_SESSION['rights']]['name']);
	$tpl->set("{user_id}", $user->id);
	$tpl->set("{login}", $user->login);
	$tpl->set("{balance}", $user->shilings);
	$tpl->set("{proc}", $user->proc);
	$tpl->set("{avatar}", $user->avatar);
	$tpl->set("{menu}", $menu);
	$tpl->set("{tickets}", $tickets);
	$tpl->set("{bans}", $bans);
	$tpl->set("{nav}", $nav);
	$tpl->compile( 'content' );
	$tpl->clear();

	$tpl->load_template('/home/page_top.tpl');
	$tpl->set("{site_host}", $site_host);
	$tpl->set("{template}", $conf->template);
	$tpl->set("{site_name}", $conf->name);
	$tpl->set("{group}", $users_groups[$_SESSION['rights']]['name']);
	$tpl->set("{user_id}", $user->id);
	$tpl->set("{login}", $user->login);
	$tpl->set("{balance}", $user->shilings);
	$tpl->set("{proc}", $user->proc);
	$tpl->set("{avatar}", $user->avatar);
	$tpl->set("{menu}", $menu);
	$tpl->set("{tickets}", $tickets);
	$tpl->set("{bans}", $bans);
	$tpl->set("{nav}", $nav);
	$tpl->compile( 'content' );
	$tpl->clear();