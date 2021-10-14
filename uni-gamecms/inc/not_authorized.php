<?php
if(empty($menu)) {
	$menu = tpl()->get_menu(pdo());
}

global $nav;

tpl()->load_template("/index/top.tpl");
tpl()->set("{site_host}", '../');
tpl()->set("{template}", configs()->template);
tpl()->set("{site_name}", configs()->name);
tpl()->set("{menu}", $menu);
tpl()->set("{conf_mess}", "");
tpl()->set("{page_name}", $nav);
tpl()->compile("content");
tpl()->clear();