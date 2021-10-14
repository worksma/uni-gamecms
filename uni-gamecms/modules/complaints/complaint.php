<?php
if($page->privacy == 1 && !is_auth()) {
	show_error_page('not_auth');
}

if (isset($_GET['id'])) {
	$id = clean($_GET['id'],"int");
} else {
	show_error_page('not_settings');
}

$STH = $pdo->prepare(
	"SELECT 
			    complaints.*,
    			author.id as author_id,
    			author.login as author_login,
    			author.avatar as author_avatar,
    			author.rights as author_rights,
    			accused.id as accused_id,
    			accused.login as accused_login,
    			accused.avatar as accused_avatar,
    			accused.rights as accused_rights,
			    accused.id as accused_id,
    			judge.id as judge_id,
    			judge.login as judge_login,
    			judge.avatar as judge_avatar,
    			judge.rights as judge_rights,
				servers.name as server_name,
				servers.id as server_id
			FROM 
			     complaints
					LEFT JOIN users author ON complaints.author_id = author.id
			        LEFT JOIN users accused ON complaints.accused_profile_id = accused.id
					LEFT JOIN users judge ON complaints.judge_id = judge.id
			        LEFT JOIN admins accused_admin ON complaints.accused_admin_id = accused_admin.id
			        LEFT JOIN servers ON complaints.accused_admin_server_id = servers.id
			WHERE complaints.id=:id 
			LIMIT 1"
);
$STH->setFetchMode(PDO::FETCH_OBJ);
$STH->execute([':id' => $id]);
$complaint = $STH->fetch();

if(empty($complaint->id)){
	show_error_page();
}

$accusedLoginForTitle = empty($complaint->accused_login) ? $messages['ToUser'] : $complaint->accused_login;

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $PI->compile_str($page->title, $accusedLoginForTitle));
$tpl->set("{name}", $conf->name);
$tpl->compile( 'title' );
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{site_name}", $conf->name);
$tpl->set("{image}", $page->image);
$tpl->set("{robots}", $page->robots);
$tpl->set("{type}", $page->kind);
$tpl->set("{description}", $PI->compile_str($page->description, $accusedLoginForTitle));
$tpl->set("{keywords}", $PI->compile_str($page->keywords, $accusedLoginForTitle));
$tpl->set("{url}", $page->full_url);
$tpl->set("{other}", '<script src="{site_host}modules/editors/tinymce/tinymce.min.js"></script>');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$menu = $tpl->get_menu($pdo);

$nav = array(
	$PI->to_nav('main', 0, 0),
	$PI->to_nav('complaints', 0, 0),
	$PI->to_nav('complaints_complaint', 1, 0, $PI->compile_str($page->title, $accusedLoginForTitle))
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

if(isset($_SESSION['id'])) {
	include_once "inc/authorized.php";
} else {
	include_once "inc/not_authorized.php";
}

if($complaint->screens != '0') {
	$data = explode(";", $complaint->screens);

	$screens = '<div id="imgs">';
	foreach($data as $item) {
		if(!empty($item)) {
			$screens .= '<a class="thumbnail" data-lightbox="1" href="../'.$item.'"><img class="thumbnail-img" src="../'.$item.'"></a>';
		}
	}
	$screens .= '</div>';
}

$status = Complaints::getComplaintStatus($complaint->sentence);

$editor_settings = get_editor_settings($pdo);
$tpl->load_template('/complaints/complaint.tpl');
$tpl->set("{file_manager}", $editor_settings['file_manager']);
$tpl->set("{file_manager_theme}", $editor_settings['file_manager_theme']);

$tpl->set("{id}", $complaint->id);
$tpl->set("{sentence}", $complaint->sentence);
$tpl->set("{status}", $status['data']);
$tpl->set("{color}", $status['color']);
$tpl->set("{screens}", empty($screens) ? '' : $screens);
$tpl->set("{demo}", empty($complaint->demo) ? '' : $complaint->demo);
$tpl->set("{date}", expand_date($complaint->date,1));
$tpl->set("{description}", $complaint->description);

$tpl->set("{author_id}", $complaint->author_id);
$tpl->set("{author_login}", $complaint->author_login);
$tpl->set("{author_avatar}", $complaint->author_avatar);
$tpl->set("{author_group_name}", $users_groups[$complaint->author_rights]['name']);
$tpl->set("{author_group_color}", $users_groups[$complaint->author_rights]['color']);

$tpl->set("{accused_id}", $complaint->accused_profile_id);
$tpl->set("{accused_login}", empty($complaint->accused_login) ? '' : $complaint->accused_login);
$tpl->set("{accused_avatar}", empty($complaint->accused_avatar) ? '' : $complaint->accused_avatar);
$tpl->set("{accused_admin_id}", empty($complaint->accused_admin_id) ? '' : $complaint->accused_admin_id);
$tpl->set("{accused_admin_nick}", empty($complaint->accused_admin_nick) ? '' : isNeedHideAdminId() ? hidePlayerId($complaint->accused_admin_nick) : $complaint->accused_admin_nick);
$tpl->set("{accused_group_name}", empty($complaint->accused_rights) ? '' : $users_groups[$complaint->accused_rights]['name']);
$tpl->set("{accused_group_color}", empty($complaint->accused_rights) ? '' : $users_groups[$complaint->accused_rights]['color']);
$tpl->set("{numberOfOtherComplaints}", Complaints::getNumberOfOtherComplaints($complaint->accused_id));

$tpl->set("{judge_id}", empty($complaint->judge_id) ? '' : $complaint->judge_id);
$tpl->set("{judge_login}", empty($complaint->judge_id) ? '' : $complaint->judge_login);
$tpl->set("{judge_avatar}", empty($complaint->judge_id) ? '' : $complaint->judge_avatar);
$tpl->set("{judge_group_name}", empty($complaint->judge_id) ? '' : $users_groups[$complaint->judge_rights]['name']);
$tpl->set("{judge_group_color}", empty($complaint->judge_id) ? '' : $users_groups[$complaint->judge_rights]['color']);

$tpl->set("{server_name}", $complaint->server_name);
$tpl->set("{server_id}", $complaint->server_id);

$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template); 
$tpl->compile( 'content' );
$tpl->clear();
?>