<?php
	include_once 'config.php';

	$tpl = new Template;
	$tpl->dir = 'templates/admin/tpl/'; 

	$tpl->load_template('head.tpl');
	$tpl->set("{title}", "Установка UNI GameCMS");
	$tpl->set("{image}", "../files/miniatures/standart.jpg");
	$tpl->set("{cache}", "1");
	$tpl->set("{site_host}", $site_host);
	$tpl->set("{other}", "");
	$tpl->set("{token}", "");
	$tpl->compile( 'content' );
	$tpl->clear();

	if(!isset($_SERVER['SERVER_SOFTWARE'])) {
		$_SERVER['SERVER_SOFTWARE'] = '0';
	}
	if(!isset($_SERVER['GATEWAY_INTERFACE'])) {
		$_SERVER['GATEWAY_INTERFACE'] = '0';
	}
	if(!isset($_SERVER['DOCUMENT_ROOT'])) {
		$_SERVER['DOCUMENT_ROOT'] = '0';
	}

	$key = md5($_SERVER['SERVER_SOFTWARE'].$_SERVER['GATEWAY_INTERFACE'].$_SERVER['DOCUMENT_ROOT']);

	$tpl->load_template('install.tpl');
	$tpl->set("{site_host}", $site_host);
	$tpl->set("{key}", $key);
	$tpl->compile( 'content' );
	$tpl->clear();

	$tpl->set( '{content}', $tpl->result['content'] );
	$tpl->load_template('main.tpl');
	$tpl->compile('main');
	eval(' ?>'.$tpl->result['main'].'<?php ');
	$tpl->global_clear();
	exit();