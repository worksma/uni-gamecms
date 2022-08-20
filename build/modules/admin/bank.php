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
$tpl->set("{other}", '<script src="{site_host}templates/admin/js/dropzone.js"></script>
<link rel="stylesheet" href="{site_host}templates/admin/css/dropzone.css">
<script src="https://www.gstatic.com/charts/loader.js"></script>');
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
	$PI->to_nav('admin_bank', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl', 1);

$tpl->load_template('page_top.tpl');
$tpl->set("{nav}", $nav);
$tpl->compile( 'content' );
$tpl->clear();

$bank1 = $conf->bank;
$bank2 = 0;
$bank3 = 0;
$STH = $pdo->prepare("SELECT `shilings` FROM `money__actions` WHERE `type`=:type and MONTH(`date`) = MONTH(NOW()) AND YEAR(`date`) = YEAR(NOW())"); $STH->setFetchMode(PDO::FETCH_OBJ);
$STH->execute(array( ':type' => 1 ));
while($row = $STH->fetch()) { 
	$bank2 = $bank2 + $row->shilings;
}
$STH = $pdo->prepare("SELECT `shilings` FROM `money__actions` WHERE `type`=:type and MONTH(`date`) = MONTH(DATE_ADD(NOW(), INTERVAL -1 MONTH)) and YEAR(`date`) = YEAR(DATE_ADD(NOW(), INTERVAL -1 MONTH))"); $STH->setFetchMode(PDO::FETCH_OBJ);
$STH->execute(array( ':type' => 1 ));
while($row = $STH->fetch()) { 
	$bank3 = $bank3 + $row->shilings;
}

$STH = $pdo->query("SELECT * FROM `config__prices` LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);  
$bank_conf = $STH->fetch();
$discount = $bank_conf->discount;

$STH = $pdo->query("SELECT `return_services`, `bonuses`, `stand_balance`, `bad_nicks_act`, `min_amount` FROM `config__secondary` LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
$row = $STH->fetch();

$tpl->load_template('bank.tpl');

$act = get_active($conf->cont, 2);
$tpl->set("{act}", $act[0]);
$tpl->set("{act2}", $act[1]);

$act = get_active($row->return_services, 2);
$tpl->set("{rs_act}", $act[0]);
$tpl->set("{rs_act2}", $act[1]);

$act = get_active($row->bad_nicks_act, 2);
$tpl->set("{bn_act}", $act[0]);
$tpl->set("{bn_act2}", $act[1]);

$act = get_active($bank_conf->referral_program, 2);
$tpl->set("{ref_act}", $act[0]);
$tpl->set("{ref_act2}", $act[1]);

$act = get_active($row->bonuses, 2);
$tpl->set("{bns_act}", $act[0]);
$tpl->set("{bns_act2}", $act[1]);

$currency = sys()->currency();
$tpl
->set("{code}", $currency->code)
->set("{lang}", $currency->lang)
->set("{html}", htmlspecialchars($currency->html));

$tpl->set("{site_host}", $site_host);
$tpl->set("{referral_percent}", $bank_conf->referral_percent );
$tpl->set("{col_pass}", $conf->col_pass);
$tpl->set("{discount}", $discount);
$tpl->set("{col_nick}", $conf->col_nick);
$tpl->set("{col_type}", $conf->col_type);
$tpl->set("{bank1}", $bank1);
$tpl->set("{bank2}", $bank2);
$tpl->set("{bank3}", $bank3);
$tpl->set("{price1}", $bank_conf->price1);
$tpl->set("{price2}", $bank_conf->price2);
$tpl->set("{price3}", $bank_conf->price3);
$tpl->set("{price2_1}", $bank_conf->price2_1);
$tpl->set("{price2_2}", $bank_conf->price2_2);
$tpl->set("{price2_3}", $bank_conf->price2_3);
$tpl->set("{price4}", $bank_conf->price4);
$tpl->set("{stand_balance}", $row->stand_balance);
$tpl->set("{min_amount}", $row->min_amount);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('bottom.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();
?>