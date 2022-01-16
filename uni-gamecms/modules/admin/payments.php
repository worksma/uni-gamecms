<?php
if(!is_admin()) {
	show_error_page('not_adm');
}

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $page->title);
$tpl->set("{name}", $conf->name);
$tpl->compile('title');
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{image}", $page->image);
$tpl->set("{other}", '');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile('content');
$tpl->clear();

$tpl->load_template('top.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{site_name}", $conf->name);
$tpl->compile('content');
$tpl->clear();

$tpl->load_template('menu.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->compile('content');
$tpl->clear();

$nav = [
	$PI->to_nav('admin', 0, 0),
	$PI->to_nav('admin_payments', 1, 0)
];
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl', 1);

$tpl->load_template('page_top.tpl');
$tpl->set("{nav}", $nav);
$tpl->compile('content');
$tpl->clear();

$STH = $pdo->query("SELECT * FROM config__bank LIMIT 1");
$STH->setFetchMode(PDO::FETCH_OBJ);
$bank_conf = $STH->fetch();

$rbact  = get_active($bank_conf->rb, 2);
$fkact  = get_active($bank_conf->fk, 2);
$fknewact  = get_active($bank_conf->fk_new, 2);
$wact   = get_active($bank_conf->wb, 2);
$upact  = get_active($bank_conf->up, 2);
$psact  = get_active($bank_conf->ps, 2);
$pstest = get_active($bank_conf->ps_test, 1);
$ikact  = get_active($bank_conf->ik, 2);
$woact  = get_active($bank_conf->wo, 2);
$yaact  = get_active($bank_conf->ya, 2);
$qwact  = get_active($bank_conf->qw, 2);
$lpact  = get_active($bank_conf->lp, 2);
$apact  = get_active($bank_conf->ap, 2);
$enotact  = get_active($bank_conf->enot, 2);
$amarapayact = get_active($bank_conf->amarapay, 2);
$psRUB  = '';
$psEUR  = '';
$psUSD  = '';
if($bank_conf->ps_currency == 'RUB') {
	$psRUB = 'active';
}
if($bank_conf->ps_currency == 'USD') {
	$psUSD = 'active';
}
if($bank_conf->ps_currency == 'EUR') {
	$psEUR = 'active';
}

$tpl->load_template('payments.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{full_site_host}", $full_site_host);
$tpl->set("{fk_login}", $bank_conf->fk_login);
$tpl->set("{fk_pass1}", $bank_conf->fk_pass1);
$tpl->set("{fk_pass2}", $bank_conf->fk_pass2);
$tpl->set("{fk_new_login}", $bank_conf->fk_new_login);
$tpl->set("{fk_new_pass1}", $bank_conf->fk_new_pass1);
$tpl->set("{fk_new_pass2}", $bank_conf->fk_new_pass2);
$tpl->set("{rb_login}", $bank_conf->rb_login);
$tpl->set("{rb_pass1}", $bank_conf->rb_pass1);
$tpl->set("{rb_pass2}", $bank_conf->rb_pass2);
$tpl->set("{up_type}", $bank_conf->up_type);
$tpl->set("{up_pass1}", $bank_conf->up_pass1);
$tpl->set("{up_pass2}", $bank_conf->up_pass2);
$tpl->set("{wb_login}", $bank_conf->wb_login);
$tpl->set("{wb_pass1}", $bank_conf->wb_pass1);
$tpl->set("{ik_login}", $bank_conf->ik_login);
$tpl->set("{ik_pass1}", $bank_conf->ik_pass1);
$tpl->set("{wo_login}", $bank_conf->wo_login);
$tpl->set("{wo_pass}", $bank_conf->wo_pass);
$tpl->set("{wb_num}", $bank_conf->wb_num);
$tpl->set("{ps_pass}", $bank_conf->ps_pass);
$tpl->set("{ps_num}", $bank_conf->ps_num);
$tpl->set("{ya_key}", $bank_conf->ya_key);
$tpl->set("{ya_num}", $bank_conf->ya_num);
$tpl->set("{qw_pass}", $bank_conf->qw_pass);
$tpl->set("{lp_public_key}", $bank_conf->lp_public_key);
$tpl->set("{lp_private_key}", $bank_conf->lp_private_key);
$tpl->set("{ap_project_id}", $bank_conf->ap_project_id);
$tpl->set("{ap_private_key}", $bank_conf->ap_private_key);
$tpl->set("{enot_id}", $bank_conf->enot_id);
$tpl->set("{enot_key}", $bank_conf->enot_key);
$tpl->set("{enot_key2}", $bank_conf->enot_key2);
$tpl->set("{rbact}", $rbact[0]);
$tpl->set("{rbact2}", $rbact[1]);
$tpl->set("{wact}", $wact[0]);
$tpl->set("{wact2}", $wact[1]);
$tpl->set("{upact}", $upact[0]);
$tpl->set("{upact2}", $upact[1]);
$tpl->set("{psact}", $psact[0]);
$tpl->set("{psact2}", $psact[1]);
$tpl->set("{fkact}", $fkact[0]);
$tpl->set("{fkact2}", $fkact[1]);
$tpl->set("{fknewact}", $fknewact[0]);
$tpl->set("{fknewact2}", $fknewact[1]);
$tpl->set("{pstest}", $pstest[0]);
$tpl->set("{pstest2}", $pstest[1]);
$tpl->set("{ikact}", $ikact[0]);
$tpl->set("{ikact2}", $ikact[1]);
$tpl->set("{psRUB}", $psRUB);
$tpl->set("{psEUR}", $psEUR);
$tpl->set("{psUSD}", $psUSD);
$tpl->set("{woact}", $woact[0]);
$tpl->set("{woact2}", $woact[1]);
$tpl->set("{yaact}", $yaact[0]);
$tpl->set("{yaact2}", $yaact[1]);
$tpl->set("{qwact}", $qwact[0]);
$tpl->set("{qwact2}", $qwact[1]);
$tpl->set("{lpact}", $lpact[0]);
$tpl->set("{lpact2}", $lpact[1]);
$tpl->set("{apact}", $apact[0]);
$tpl->set("{apact2}", $apact[1]);
$tpl->set("{enotact}", $enotact[0]);
$tpl->set("{enotact2}", $enotact[1]);

$tpl
->set("{amarapay_act}", $amarapayact[0])
->set("{amarapay_act2}", $amarapayact[1])
->set("{amarapay_id}", $bank_conf->amarapay_id)
->set("{amarapay_public}", $bank_conf->amarapay_public)
->set("{amarapay_secret}", $bank_conf->amarapay_secret);

$tpl->compile('content');
$tpl->clear();

$tpl->load_template('bottom.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->compile('content');
$tpl->clear();
?>