<?php
if(!is_admin()){
	show_error_page('not_adm');
}

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $page->title);
$tpl->set("{name}", $conf->name);
$tpl->compile( 'title' );
$tpl->clear();

$STH = $pdo->query("SELECT `template` FROM `config` LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
$row = $STH->fetch();
if(empty($_COOKIE['template'])) {
	$conf->template = $row->template;
}

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{image}", $page->image);
$tpl->set("{other}", '<link rel="stylesheet" href="{site_host}modules/editors/editor/codemirror.css">
<link rel="stylesheet" href="{site_host}modules/editors/editor/show-hint.css">
<link rel="stylesheet" href="{site_host}modules/editors/editor/fullscreen.css">
<link rel="stylesheet" href="{site_host}modules/editors/editor/simplescrollbars.css">
<link rel="stylesheet" href="{site_host}modules/editors/editor/monokai.css">
<link rel="stylesheet" href="{site_host}modules/editors/editor/dialog.css">
<script src="{site_host}modules/editors/editor/codemirror.js"></script>
<script src="{site_host}modules/editors/editor/show-hint.js"></script>
<script src="{site_host}modules/editors/editor/xml-hint.js"></script>
<script src="{site_host}modules/editors/editor/html-hint.js"></script>
<script src="{site_host}modules/editors/editor/xml.js"></script>
<script src="{site_host}modules/editors/editor/javascript.js"></script>
<script src="{site_host}modules/editors/editor/css.js"></script>
<script src="{site_host}modules/editors/editor/htmlmixed.js"></script>
<script src="{site_host}modules/editors/editor/simplescrollbars.js"></script>
<script src="{site_host}modules/editors/editor/fullscreen.js"></script>
<script src="{site_host}modules/editors/editor/searchcursor.js"></script>
<script src="{site_host}modules/editors/editor/search.js"></script>
<script src="{site_host}modules/editors/editor/dialog.js"></script>
<style>body {min-width: 1200px !important;} .wapper{min-width: 1200px !important;}</style>');
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
	$PI->to_nav('admin_template', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl', 1);

$tpl->load_template('page_top.tpl');
$tpl->set("{nav}", $nav);
$tpl->compile( 'content' );
$tpl->clear();

$folders = scandir("templates/");
$templates = "";
$templates_mobile = "";
if(!isset($_COOKIE['template'])) {
	$templates_prsonal = "<option value='0' selected>Не задан</option>";
} else {
	$templates_prsonal = "<option value='0'>Не задан</option>";
}

for ($i=2; $i < count($folders); $i++) {
	if($folders[$i] != 'admin' and file_exists("templates/".$folders[$i]."/tpl/head.tpl")) {

		if(isset($_SESSION['original_template'])) {
			$original_template = $_SESSION['original_template'];
		} else {
			$original_template = $conf->template;
		}

		if($original_template == $folders[$i]) {
			$templates .= "<option value='".$folders[$i]."' selected>".$folders[$i]."</option>";
		} else {
			$templates .= "<option value='".$folders[$i]."'>".$folders[$i]."</option>";
		}
		if($conf->template_mobile == $folders[$i]) {
			$templates_mobile .= "<option value='".$folders[$i]."' selected>".$folders[$i]."</option>";
		} else {
			$templates_mobile .= "<option value='".$folders[$i]."'>".$folders[$i]."</option>";
		}
		if(isset($_COOKIE['template']) && $_COOKIE['template'] == $folders[$i]) {
			$templates_prsonal .= "<option value='".$folders[$i]."' selected>".$folders[$i]."</option>";
		} else {
			$templates_prsonal .= "<option value='".$folders[$i]."'>".$folders[$i]."</option>";
		}
	}
}

$tpl->load_template('template.tpl');
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->set("{templates}", $templates);
$tpl->set("{templates_mobile}", $templates_mobile);
$tpl->set("{templates_prsonal}", $templates_prsonal);
$tpl->set("{token}", $token);
$tpl->set("{template_tpls}", createDir("templates/".$conf->template."/tpl/", "html"));
$tpl->set("{template_css}", createDir("templates/".$conf->template."/css/", "css"));
$tpl->set("{template_imgs}", createDir("templates/".$conf->template."/img/", "img"));
if(file_exists("templates/".$conf->template."/images/")) {
	$tpl->set("{template_images}", createDir("templates/".$conf->template."/images/", "img", 1));
	$tpl->set("{have_images}", "1");
} else {
	$tpl->set("{template_images}", "");
	$tpl->set("{have_images}", "0");
}
$tpl->set("{engine_avatars}", createDir("files/avatars/", "img", 1));
$tpl->set("{engine_forums_imgs}", createDir("files/forums_imgs/", "img", 1));
$tpl->set("{engine_maps_imgs}", createDir("files/maps_imgs/", "img", 1));
$tpl->set("{engine_news_imgs}", createDir("files/news_imgs/", "img", 1));
$act = get_active($conf->caching, 2);
$tpl->set("{caching_act}", $act[0]);
$tpl->set("{caching_act2}", $act[1]);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('bottom.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();
?>