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
$tpl->set("{other}", '<script src="{site_host}modules/editors/ckeditor/ckeditor.js"></script>');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('top.tpl');
$tpl->set("{token}", $token);
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
	$PI->to_nav('admin_email_settings', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl', 1);

$tpl->load_template('page_top.tpl');
$tpl->set("{nav}", $nav);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('email_settings.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{site_name}", $conf->name);
$tpl->set("{template}", $conf->template);

$STH = $pdo->query("SELECT * FROM config__email LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);  
$email_conf = $STH->fetch();  
if($email_conf->use_email == 1){
	$pact = 'active';
	$pact2 = '';
} elseif ($email_conf->use_email == 2) {
	$pact = '';
	$pact2 = 'active';
}
$tpl->set("{pact}", $pact);
$tpl->set("{pact2}", $pact2);
$tpl->set("{email_username}", $email_conf->username);
$tpl->set("{email_port}", $email_conf->port);
$tpl->set("{email_host}", $email_conf->host);
$tpl->set("{email_password}", $email_conf->password);
$tpl->set("{email_charset}", $email_conf->charset);
$tpl->set("{from_email}", $email_conf->from_email);
$tpl->set("{use_email}", $email_conf->use_email);
$tpl->set("{verify_peers}", $email_conf->verify_peers);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('bottom.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();
?>