<?php
if ((isset($_GET['ban']) and $_GET['ban'] == md5($conf->code)) || (isset($ban) && $ban == true)) {
	$SC->set_cookie("point", "1");
	$STH = $pdo->prepare("INSERT INTO `users__blocked` (ip) values (:ip)"); 
	$STH->execute(array('ip' => $ip));
}
if(isset($_SESSION['id'])) {
	$pdo->exec("DELETE FROM `users__online` WHERE `user_id`='$_SESSION[id]' LIMIT 1");
	$STH = $pdo->prepare("UPDATE `users` SET `last_activity`=:last_activity WHERE `id`='$_SESSION[id]' LIMIT 1");  
	$STH->execute(array( 'last_activity' => date("Y-m-d H:i:s")));
}

$SC->unset_user_session();
header('Location: ../');
exit();