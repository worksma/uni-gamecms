<?php
if(empty($conf->id)) {
	$STH = $pdo->query("SELECT * FROM config LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$conf = $STH->fetch();
}

$actionsForForbiddenWordsProtections = [
	'send_first_message' => 'message_text',
	'send_message' => 'message_text',
	'chat_send_message' => 'message_text',
	'send_new_comment' => 'text',
	'send_ban_comment' => 'text',
	'add_topic' => 'text',
	'send_answer' => 'text',
	'send_user_comment' => 'text',
];
if($actionKey = isSomeKeyInArrayExists(array_keys($actionsForForbiddenWordsProtections), $_POST)) {
	$STH = $pdo->prepare("SELECT data FROM config__strings WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => 5]);
	$row = $STH->fetch();

	if(!empty($row->data)) {
		$forbiddenWords = explode(';sp;', $row->data);
	}

	if(!empty($forbiddenWords)) {
		$content = $_POST[$actionsForForbiddenWordsProtections[$actionKey]];

		foreach($forbiddenWords as $forbiddenWord) {
			if(!empty($forbiddenWord) && mb_stristr($content, $forbiddenWord, false, 'UTF-8') !== false) {
				exit('ForbiddenWord: prohibited content found');
			}
		}
	}
}

$actionsForGagProtections = [
	'chat_send_message',
	'drop_img',
	'send_new_comment',
	'send_ban_comment',
	'add_topic',
	'add_topic_img',
	'send_answer',
	'send_user_comment',
];
if(isSomeKeyInArrayExists($actionsForGagProtections, $_POST) && is_auth()) {
	if(empty($user->gag)) {
		$STH = $pdo->prepare("SELECT gag FROM users WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':id' => $_SESSION['id'] ));
		$row = $STH->fetch();

		$gag = $row->gag;
	} else {
		$gag = $user->gag;
	}

	if($gag == 1) {
		exit('Gag: it is forbidden to perform this action');
	}
}

if($conf->protect == 1 && $conf->captcha != 2) {
	$actionsForCaptchaProtections = array_merge(
		$actionsForGagProtections,
		[
			'create_dialog',
			'send_first_message',
			'send_message',
			'loadImages',
			'send_ticket_answer',
			'open_case',
			'load_file'
		]
	);

	if(isSomeKeyInArrayExists($actionsForCaptchaProtections, $_POST) && is_auth()) {
		$STH = $pdo->prepare("SELECT id, date, count FROM last_actions WHERE user_id=:user_id AND action_type=:action_type LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':user_id' => $_SESSION['id'], ':action_type' => 5]);
		$row = $STH->fetch();

		if(empty($row->id)) {
			$STH = $pdo->prepare("INSERT INTO last_actions (date,user_id,action_type,count) VALUES (:date, :user_id, :action_type, :count)");
			$STH->execute([':date' => time(), ':user_id' => $_SESSION['id'], ':action_type' => 5, ':count' => 0]);
		} else {
			if($row->count > $conf->violations_number) {
				exit('Flood: pass a bot check [' . $conf->captcha_client_key . "]");
			}

			if((time() - $row->date) < $conf->violations_delta) {
				$row->count++;

				$STH = $pdo->prepare("UPDATE last_actions SET date=:date, count=:count WHERE id=:id LIMIT 1");
				$STH->execute([':date' => time(), ':count' => $row->count, ':id' => $row->id]);
			} else {
				$STH = $pdo->prepare("UPDATE last_actions SET date=:date WHERE id=:id LIMIT 1");
				$STH->execute([':date' => time(), ':id' => $row->id]);
			}
		}
	}
}