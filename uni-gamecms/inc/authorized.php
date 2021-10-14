<?php
$countOfOpenTickets = 0;
$countOfOpenBans = 0;
$countOfOpenComplaints = 0;

if(is_worthy('p')) {
	$STH = pdo()->query("SELECT COUNT(*) as count FROM tickets WHERE have_answer = '0'");
	$countOfOpenTickets = $STH->fetchColumn();
}

if(is_worthy('i')) {
	$STH = pdo()->query("SELECT COUNT(*) as count FROM bans WHERE have_answer = '0'");
	$countOfOpenBans = $STH->fetchColumn();
}

if(is_worthy('k')) {
	$STH = pdo()->query("SELECT COUNT(*) as count FROM complaints WHERE have_answer = '0'");
	$countOfOpenComplaints = $STH->fetchColumn();
}

if(empty($menu)) {
	$menu = tpl()->get_menu(pdo());
}

global $nav;

foreach(['/home/top.tpl', '/home/left.tpl', '/home/page_top.tpl'] as $template) {
	tpl()->load_template($template);
	tpl()->set("{site_host}", '../');
	tpl()->set("{template}", configs()->template);
	tpl()->set("{site_name}", configs()->name);

	tpl()->set("{group_name}", users_groups()[$_SESSION['rights']]['name']);
	tpl()->set("{group_color}", users_groups()[$_SESSION['rights']]['color']);

	tpl()->set("{user_id}", user()->id);
	tpl()->set("{login}", user()->login);
	tpl()->set("{avatar}", user()->avatar);
	tpl()->set("{balance}", user()->shilings);
	tpl()->set("{proc}", user()->proc);

	tpl()->set("{menu}", $menu);
	tpl()->set("{nav}", $nav);

	tpl()->set("{countOfOpenTickets}", $countOfOpenTickets);
	tpl()->set("{countOfOpenBans}", $countOfOpenBans);
	tpl()->set("{countOfOpenComplaints}", $countOfOpenComplaints);
	tpl()->compile('content');
	tpl()->clear();
}