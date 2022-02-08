<?php
session_set_cookie_params(0, '/', '', false, true);
session_start();
include_once '../inc/db.php';
include_once '../inc/functions.php';

if (isset($_POST['check_news'])) {
	$res = array();
	$STH = $pdo->query("SELECT id FROM users__friends WHERE (id_taker='$_SESSION[id]') AND (accept='0')");
	$STH->execute();
	$row = $STH->fetchAll();
	$count = count($row);
	if ($count == 0) {
		$res['status1'] = '2';
	} else {
		$res['status1'] = '1';
		$res['val0'] = $count.' ';
	}

	$status = 'r'.$_SESSION['id'];
	$STH = $pdo->query("SELECT id,new FROM pm__dialogs WHERE (user_id1='$_SESSION[id]' or user_id2='$_SESSION[id]') AND (new='$_SESSION[id]' or new='$status')");
	$STH->execute();
	$row = $STH->fetchAll();
	$count = count($row);
	if ($count == 0) {
		$res['status2'] = '2';
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
		$res['status2'] = '1';
		$res['val1'] = $new1;
		$res['val2'] = $count;
	}
	exit(json_encode($res));
}
?>