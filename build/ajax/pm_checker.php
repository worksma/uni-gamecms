<?php
session_set_cookie_params(0, '/', '', false, true);
session_start();
include_once '../inc/db.php';
include_once '../inc/functions.php';

if(empty($_SESSION['id'])) {
	echo 'Ошибка: [Доступно только авторизованным]';
	exit(json_encode(array('status' => '2')));
}
if (isset($_POST['check_messages'])) {
	$id = checkJs($_POST['id'],"int");
	$status = 'r'.$_SESSION['id'];
	if(isset($id)){
		$STH = $pdo->query("SELECT id,new FROM pm__dialogs WHERE (user_id1='$_SESSION[id]' or user_id2='$_SESSION[id]') AND (new='$_SESSION[id]' or new='$status') AND (id!='$id')");
	} else {
		$STH = $pdo->query("SELECT id,new FROM pm__dialogs WHERE (user_id1='$_SESSION[id]' or user_id2='$_SESSION[id]') AND (new='$_SESSION[id]' or new='$status')");
	}
	$STH->execute();
	$row = $STH->fetchAll();
	$count = count($row);
	$res = array();
	if ($count == 0) {
		$res['status'] = '2';
	} else {
		$new1=0;
		for ($i=0; $i < $count; $i++) { 
			if($row[$i]['new'] == $_SESSION['id']){
				$new1++;
			}
		}
		if($new1>0){
			$STH = $pdo->prepare("UPDATE pm__dialogs SET new=:new WHERE (user_id1='$_SESSION[id]' or user_id2='$_SESSION[id]') AND (new='$_SESSION[id]')");
			$STH->execute(array( 'new' => $status ));
		}
		$res['status'] = '1';
		$res['val1'] = $new1;
		$res['val2'] = $count;
	}
	exit(json_encode($res));
}
exit(json_encode(array('status' => '2')));
?>