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
$tpl->set("{other}", '');
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
	$PI->to_nav('admin_stat', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl', 1);

$tpl->load_template('page_top.tpl');
$tpl->set("{nav}", $nav);
$tpl->compile( 'content' );
$tpl->clear();

if(file_exists("logs/stat.log")) {
	$file = file("logs/stat.log");
	$col = sizeof($file);
	if ($col > sizeof($file)) {
		$col = sizeof($file);
	}
	$data = '';
	for ($i = sizeof($file) - 1; $i + 1 > sizeof($file) - $col; $i--) {
		$string = explode("|",$file[$i]);

		for ($j = 0; $j < count($string); $j++) {
			if(empty($string[$j])) {
				$string[$j] = '';
			}
		}

		$data .= "
		<tr>
			<td>".$i."</td>
			<td>".$string[0]."</td>
			<td>".$string[1]."</td>
			<td>".$string[2]."</td>
			<td>".$string[3]."</td>
		</tr>
		";
	}
} else {
	$data = '';
}

$tpl->load_template('stat.tpl');
$act = 0; $act2 = 0;
if($conf->stat == 1){
	$act = 'active';
} elseif ($conf->stat == 2) {
	$act2 = 'active';
}
$tpl->set("{act_st}", $act);
$tpl->set("{act2_st}", $act2);
$tpl->set("{stat_number}", $conf->stat_number);
$tpl->set("{site_host}", $site_host);
$tpl->set("{data}", $data);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('bottom.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();
?>