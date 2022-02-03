<?php
$Pm = new Payments;

$bankConf = pdo()->query("SELECT * FROM config__bank LIMIT 1")->fetch(PDO::FETCH_OBJ);

if(isset($_GET['lava']) && $_GET['lava'] == 'get'):
	$result = json_decode(file_get_contents("php://input"));
	$payMethod = "lava";
	
	if(Lava::is_valid($result->invoice_id)):
		$amount = intval($result->amount);
		$uid = $result->custom_fields;
		$udata = Lava::getUser(pdo(), $uid);
		
		if(empty($udata->id)):
			Lava::paymentLog($payMethod, "unknown user", pdo(), $udata, 2);
		else:
			if(Lava::issetPay(null, $payMethod, $result->order_id)):
				exit("OK");
			endif;
			
			if(trading()->is_bonuses()):
				trading()->add_bonuses($udata->id, $amount);
			endif;
			
			Lava::doPayAction(pdo(), $udata, $amount, $conf->bank, $payMethod, $result->order_id, $messages['RUB']);
			exit("OK");
		endif;
	endif;
	
	http_response_code(404);
	exit;
endif;

if(isset($_GET['result_qw']) && $_GET['result_qw'] == 'get') {
	$payMethod = 'qw';

	$data = json_decode(file_get_contents('php://input'), true);
	if(array_key_exists('HTTP_X_API_SIGNATURE_SHA256', $_SERVER)) {
		$signature = $_SERVER['HTTP_X_API_SIGNATURE_SHA256'];
	} else {
		$signature = null;
	}

	if(empty($data) || empty($signature)) {
		$Pm->paymentLog($payMethod, "empty data", $pdo, 0, 2);
		http_response_code(204);
		exit('Error: [empty data]');
	}

	$status    = $data['bill']['status']['value'];
	$amount    = clean($data['bill']['amount']['value'], 'float');
	$currency  = clean($data['bill']['amount']['currency']);
	$payNumber = clean($data['bill']['billId'], 'int');
	$userId    = clean($data['bill']['customer']['account'], 'int');

	$currentCurrency = Payments::getCashierCurrency($payMethod);
	if($currentCurrency != $currency) {
		throw new Exception('invalid request');
	}

	$Qiwi = new Qiwi($bankConf->qw_pass);

	if(!$Qiwi->checkNotificationSignature($signature, $data, $bankConf->qw_pass) && $status == 'PAID') {
		$Pm->paymentLog($payMethod, "bad sign", $pdo, $userId, 2);
		http_response_code(400);
		exit("Error: [bad signature]");
	} else {
		$userInfo = $Pm->getUser($pdo, $userId);
		if(empty($userInfo->id)) {
			$Pm->paymentLog($payMethod, "unknown user", $pdo, $userId, 2);
			http_response_code(404);
			exit('Error: [User does not exist]');
		} else {
			if($Pm->issetPay($pdo, $payMethod, $payNumber)) {
				exit('OK');
			}

			$playground = new Playground(pdo(), $conf);

			if($playground->is_bonuses()):
				$playground->add_bonuses($userInfo->id, $amount);
			endif;

			$Pm->doPayAction($pdo, $userInfo, $amount, $conf->bank, $payMethod, $payNumber, $messages['RUB']);
			exit('OK');
		}
	}
}

if(isset($_GET['result_ya']) && $_GET['result_ya'] == 'get') {
	$payMethod = 'ya';

	if(empty($_POST["amount"]) || empty($_POST["operation_id"]) || empty($_POST["label"]) || empty($_POST["sha1_hash"]) || empty($_POST["unaccepted"])) {
		$Pm->paymentLog($payMethod, "empty data", $pdo, 0, 2);
		exit('Error: [empty data]');
	}

	$amount    = clean($_POST['withdraw_amount'], 'float');
	$payNumber = clean($_POST["label"], 'int');
	$userId    = (int)clean(substr($payNumber, 0, 7), 'int');

	if(
		sha1(
			$_POST['notification_type']
			. '&' . $_POST['operation_id']
			. '&' . $_POST['amount']
			. '&' . $_POST['currency']
			. '&' . $_POST['datetime']
			. '&' . $_POST['sender']
			. '&' . $_POST['codepro']
			. '&' . $bankConf->ya_key
			. '&' . $_POST["label"]
		) != $_POST['sha1_hash']
	) {
		$Pm->paymentLog($payMethod, "bad sign", $pdo, $payNumber, 2);
		exit("bad sign\n");
	} else {
		$userInfo = $Pm->getUser($pdo, $userId);
		if(empty($userInfo->id)) {
			$Pm->paymentLog($payMethod, "unknown user", $pdo, $userId, 2);
			exit('Error: [User does not exist]');
		} else {
			if($Pm->issetPay($pdo, $payMethod, $payNumber)) {
				exit('OK');
			}

			$playground = new Playground(pdo(), $conf);

			if($playground->is_bonuses()):
				$playground->add_bonuses($userInfo->id, $amount);
			endif;

			$Pm->doPayAction($pdo, $userInfo, $amount, $conf->bank, $payMethod, $payNumber, $messages['RUB']);
			exit('OK');
		}
	}
}

if(isset($_GET['result_wo']) && $_GET['result_wo'] == 'get') {
	$payMethod = 'wo';

	$userId = clean($_POST['user_id'], 'int');

	if(!isset($_POST["WMI_SIGNATURE"])) {
		$Pm->paymentLog($payMethod, "invalid request", $pdo, $userId, 2);
		exit('WMI_RESULT=RETRY&WMI_DESCRIPTION=Empty WMI_SIGNATURE');
	}
	if(!isset($_POST["WMI_PAYMENT_NO"])) {
		$Pm->paymentLog($payMethod, "invalid request", $pdo, $userId, 2);
		exit('WMI_RESULT=RETRY&WMI_DESCRIPTION=Empty WMI_PAYMENT_NO');
	}
	if(!isset($_POST["WMI_ORDER_STATE"])) {
		$Pm->paymentLog($payMethod, "invalid request", $pdo, $userId, 2);
		exit('WMI_RESULT=RETRY&WMI_DESCRIPTION=Empty WMI_ORDER_STATE');
	}

	$amount    = clean($_POST['WMI_PAYMENT_AMOUNT'], 'float');
	$payNumber = clean($_POST['WMI_PAYMENT_NO'], 'int');
	foreach($_POST as $name => $value) {
		if($name !== "WMI_SIGNATURE") {
			$params[$name] = $value;
		}
	}

	uksort($params, "strcasecmp");
	$values = "";
	foreach($params as $name => $value) {
		$values .= $value;
	}
	if(base64_encode(pack("H*", md5($values . $bankConf->wo_pass))) != $_POST["WMI_SIGNATURE"]) {
		$Pm->paymentLog($payMethod, "bad sign", $pdo, $userId, 2);
		exit('WMI_RESULT=RETRY&WMI_DESCRIPTION=bad sign');
	} else {
		if(strtoupper($_POST["WMI_ORDER_STATE"]) == "ACCEPTED") {
			$userInfo = $Pm->getUser($pdo, $userId);
			if(empty($userInfo->id)) {
				$Pm->paymentLog($payMethod, "unknown user", $pdo, $userId, 2);
				exit('WMI_RESULT=RETRY&WMI_DESCRIPTION=User does not exist');
			} else {
				if($Pm->issetPay($pdo, $payMethod, $payNumber)) {
					exit("WMI_RESULT=OK");
				}

				$playground = new Playground(pdo(), $conf);

				if($playground->is_bonuses()):
					$playground->add_bonuses($userInfo->id, $amount);
				endif;

				$Pm->doPayAction($pdo, $userInfo, $amount, $conf->bank, $payMethod, $payNumber, $messages['RUB']);
			}
			exit("WMI_RESULT=OK");
		} else {
			$Pm->paymentLog($payMethod, "invalid request", $pdo, $userId, 2);
			exit('WMI_RESULT=RETRY&WMI_DESCRIPTION=bad sign');
		}
	}
}

if(isset($_GET['result_ik']) && $_GET['result_ik'] == 'get') {
	$payMethod = 'ik';
	$currentCurrency = Payments::getCashierCurrency($payMethod);

	try {
		if(
			empty($_POST['ik_sign'])
			|| empty($_POST['ik_am'])
			|| empty($_POST['ik_x_id'])
			|| empty($_POST['ik_pm_no'])
			|| empty($_POST['ik_cur'])
			|| empty($_POST['ik_inv_st'])
		) {
			throw new Exception('empty data');
		}

		$amount    = clean($_POST['ik_am'], 'float');
		$payNumber = clean($_POST['ik_pm_no'], 'int');
		$userId    = clean($_POST['ik_x_id'], 'int');

		$sign = $_POST['ik_sign'];
		unset($_POST['ik_sign']);

		ksort($_POST, SORT_STRING);
		array_push($_POST, $bankConf->ik_pass1);
		$currentSign = implode(':', $_POST);
		$currentSign = base64_encode(md5($currentSign, true));

		if($sign != $currentSign) {
			throw new Exception('bad sign');
		}

		if($_POST['ik_inv_st'] != 'success') {
			exit();
		}

		if($currentCurrency != $_POST['ik_cur']) {
			throw new Exception('invalid request');
		}

		$userInfo = $Pm->getUser($pdo, $userId);
		if(empty($userInfo->id)) {
			throw new Exception('unknown user');
		} else {
			if($Pm->issetPay($pdo, $payMethod, $payNumber)) {
				exit('OK');
			}

			$playground = new Playground(pdo(), $conf);

			if($playground->is_bonuses()):
				$playground->add_bonuses($userInfo->id, $amount);
			endif;

			$Pm->doPayAction($pdo, $userInfo, $amount, $conf->bank, $payMethod, $payNumber, $messages['RUB']);
			exit('OK');
		}
	} catch(Exception $e) {
		if(empty($userId)) {
			$userId = 0;
		}

		$Pm->paymentLog($payMethod, $e->getMessage(), $pdo, $userId, 2);
		http_response_code(500);
		exit($e->getMessage());
	}
}

if(isset($_GET['amarapay']) && $_GET['amarapay'] == 'get'):
	$payMethod			= "amarapay";
	$amount				= $_POST['amount'];
	$hash				= $_POST['hash'];
	$userId 			= $_POST['label'];
	$id					= $_POST['id'];
	$apay 				= $bankConf->amarapay_id;
	$secret 			= $bankConf->amarapay_secret;
	$myhash 			= hash("sha256", $apay . $amount . $secret . $id);
	
	if($hash != $myhash):
		$Pm->paymentLog($payMethod, "bad sign", pdo(), $userId, 2);
		die("no hash");
	endif;
	
	$userInfo = $Pm->getUser(pdo(), $userId);
	$amount				= intval($amount);
	
	if(empty($userInfo->id)):
		$Pm->paymentLog($payMethod, "unknown user", pdo(), $userId, 2);
		die("unknown user");
	else:
		if(trading()->is_bonuses()):
			trading()->add_bonuses($userId, $amount);
		endif;
		
		$Pm->doPayAction(pdo(), $userInfo, $amount, $conf->bank, $payMethod, $userId, $messages['RUB']);
	endif;
	
	die('200');
endif;

if(isset($_GET['result_fk']) && $_GET['result_fk'] == 'get') {
	$payMethod = 'fk_new';

	$amount    = clean($_POST['AMOUNT'], 'float');
	$payNumber = clean($_POST['MERCHANT_ORDER_ID'], 'int');
	$userId    = clean($_POST['us_user'], 'int');

	$sign = md5($bankConf->fk_new_login . ':' . $_POST['AMOUNT'] . ':' . htmlspecialchars_decode($bankConf->fk_new_pass2) . ':' . $_POST['MERCHANT_ORDER_ID']);

	if($sign != $_POST['SIGN']) {
		$Pm->paymentLog($payMethod, "bad sign", $pdo, $userId, 2);
		exit("bad sign");
	}

	$userInfo = $Pm->getUser($pdo, $userId);
	if(empty($userInfo->id)) {
		$Pm->paymentLog($payMethod, "unknown user", $pdo, $userId, 2);
		exit('Error: [User does not exist]');
	} else {
		if($Pm->issetPay($pdo, $payMethod, $payNumber)) {
			exit('YES');
		}

		$playground = new Playground(pdo(), $conf);

		if($playground->is_bonuses()):
			$playground->add_bonuses($userInfo->id, $amount);
		endif;

		$Pm->doPayAction($pdo, $userInfo, $amount, $conf->bank, $payMethod, $payNumber, $messages['RUB']);
		exit('YES');
	}
}

if(isset($_GET['result_wb']) && $_GET['result_wb'] == 'get') {
	$payMethod = 'wm';

	if(isset($_POST['LMI_PREREQUEST']) && $_POST['LMI_PREREQUEST'] == 1) {
		$userId   = clean($_POST['id'], 'int');
		$userInfo = $Pm->getUser($pdo, $userId);
		if(empty($userInfo->id)) {
			$Pm->paymentLog($payMethod, "unknown user", $pdo, $userId, 2);
			exit("ERR: User does not exist");
		}

		if(trim($_POST['LMI_PAYEE_PURSE']) != $bankConf->wb_num) {
			$Pm->paymentLog($payMethod, "invalid purse", $pdo, $userId, 2);
			exit("ERR: Invalid purse");
		}

		exit('YES');
	} else {
		$secret_key    = $bankConf->wb_pass1;
		$common_string = $_POST['LMI_PAYEE_PURSE'] . $_POST['LMI_PAYMENT_AMOUNT'] . $_POST['LMI_PAYMENT_NO'] . $_POST['LMI_MODE'] . $_POST['LMI_SYS_INVS_NO'] . $_POST['LMI_SYS_TRANS_NO'] . $_POST['LMI_SYS_TRANS_DATE'] . $secret_key . $_POST['LMI_PAYER_PURSE'] . $_POST['LMI_PAYER_WM'];
		$hash          = strtoupper(hash("sha256", $common_string));

		$amount    = clean($_POST['LMI_PAYMENT_AMOUNT'], 'float');
		$payNumber = clean($_POST['LMI_PAYMENT_NO'], 'int');
		$userId    = clean($_POST['id'], 'int');

		if($hash != $_POST['LMI_HASH']) {
			$Pm->paymentLog($payMethod, "bad sign", $pdo, $userId, 2);
			exit("ERR: Bad key");
		} else {
			$userInfo = $Pm->getUser($pdo, $userId);
			if(empty($userInfo->id)) {
				$Pm->paymentLog($payMethod, "unknown user", $pdo, $userId, 2);
				exit('Error: [User does not exist]');
			} else {
				if($Pm->issetPay($pdo, $payMethod, $payNumber)) {
					exit('OK');
				}

				$playground = new Playground(pdo(), $conf);

				if($playground->is_bonuses()):
					$playground->add_bonuses($userInfo->id, $amount);
				endif;

				$Pm->doPayAction($pdo, $userInfo, $amount, $conf->bank, $payMethod, $payNumber, $messages['RUB']);
				exit('OK');
			}
		}
	}
}

if(isset($_GET['result_rb']) && $_GET['result_rb'] == 'get') {
	$payMethod = 'rb';

	$mrh_pass2 = $bankConf->rb_pass2;
	$out_summ  = $_POST["OutSum"];
	$inv_id    = $_POST["InvId"];
	$Shp_zuser = $_POST["Shp_zuser"];
	$crc       = $_POST["SignatureValue"];
	$crc       = strtoupper($crc);
	$my_crc    = strtoupper(md5("$out_summ:$inv_id:$mrh_pass2:Shp_zuser=$Shp_zuser"));
	if(strtoupper($my_crc) != strtoupper($crc)) {
		$Pm->paymentLog($payMethod, "bad sign", $pdo, $Shp_zuser, 2);
		exit("bad sign\n");
	}
	echo "OK$inv_id\n";

	$amount    = clean($out_summ, 'float');
	$payNumber = clean($inv_id, 'int');
	$userId    = clean($Shp_zuser, 'int');
	$userInfo  = $Pm->getUser($pdo, $userId);
	if(empty($userInfo->id)) {
		$Pm->paymentLog($payMethod, "unknown user", $pdo, $userId, 2);
		exit('Error: [User does not exist]');
	} else {
		if($Pm->issetPay($pdo, $payMethod, $payNumber)) {
			exit('OK');
		}

		$playground = new Playground(pdo(), $conf);

		if($playground->is_bonuses()):
			$playground->add_bonuses($userInfo->id, $amount);
		endif;

		$Pm->doPayAction($pdo, $userInfo, $amount, $conf->bank, $payMethod, $payNumber, $messages['RUB']);
		exit('OK');
	}
}

if(isset($_GET) && array_key_exists('method', $_GET)) {
	$payMethod = 'up';
	$currentCurrency = Payments::getCashierCurrency($payMethod);

	$unitPay = new UnitPay(
		$bankConf->up_type == 1 ? 'unitpay.money' : 'unitpay.ru',
		$bankConf->up_pass2
	);

	try {
		$unitPay->checkHandlerRequest();

		list($method, $params) = [$_GET['method'], $_GET['params']];

		if(!array_key_exists('params', $_GET) || !is_array($_GET['params'])) {
			$Pm->paymentLog($payMethod, 'invalid request', $pdo, 0, 2);
			throw new Exception('Invalid request');
		}

		if($currentCurrency != $params['orderCurrency']) {
			$Pm->paymentLog($payMethod, 'invalid request', $pdo, 0, 2);
			throw new Exception('Invalid request');
		}

		$userId   = clean($params['account'], 'int');
		$userInfo = $Pm->getUser($pdo, $userId);
		if(empty($userInfo->id)) {
			$Pm->paymentLog($payMethod, 'unknown user', $pdo, $userId, 2);
			throw new Exception('User does not exist');
		}

		switch($method) {
			case 'check':
				echo $unitPay->getSuccessHandlerResponse('Check Success. Ready to pay.');
				break;
			case 'pay':
				$amount    = clean($params['orderSum'], 'float');
				$payNumber = clean($params['unitpayId'], 'int');

				if($Pm->issetPay($pdo, $payMethod, $payNumber)) {
					echo $unitPay->getSuccessHandlerResponse('Pay Success');
					break;
				}

				$playground = new Playground(pdo(), $conf);

				if($playground->is_bonuses()):
					$playground->add_bonuses($userInfo->id, $amount);
				endif;

				$Pm->doPayAction($pdo, $userInfo, $amount, $conf->bank, $payMethod, $payNumber, $messages['RUB']);
				echo $unitPay->getSuccessHandlerResponse('Pay Success');

				break;
			case 'error':
				echo $unitPay->getSuccessHandlerResponse('Error logged');
				break;
			case 'refund':
				echo $unitPay->getSuccessHandlerResponse('Order canceled');
				break;
		}
	} catch(Exception $e) {
		echo $unitPay->getErrorHandlerResponse($e->getMessage());
	}
	exit();
}

if(isset($_GET['result_ps']) && $_GET['result_ps'] == 'get') {
	$payMethod = 'ps';

	require_once('inc/classes/class.paysera.php');

	try {
		$response = WebToPay::checkResponse(
			$_GET,
			[
				'projectid'     => $bankConf->ps_num,
				'sign_password' => $bankConf->ps_pass,
			]
		);

		$userId    = clean($response['zzz'], 'int');
		$amount    = clean($response['amount'] / 100, 'float');
		$payNumber = clean($response['orderid'], 'int');

		$userInfo = $Pm->getUser($pdo, $userId);
		if(empty($userInfo->id)) {
			$Pm->paymentLog($payMethod, "unknown user", $pdo, $userId, 2);
			exit("ERR: User does not exist");
		}

		if($Pm->issetPay($pdo, $payMethod, $payNumber)) {
			exit('OK');
		}

		$playground = new Playground(pdo(), $conf);

		if($playground->is_bonuses()):
			$playground->add_bonuses($userInfo->id, $amount);
		endif;

		$Pm->doPayAction($pdo, $userInfo, $amount, $conf->bank, $payMethod, $payNumber, $messages['RUB']);
		exit('OK');
	} catch(Exception $e) {
		$srt = get_class($e) . ': ' . $e->getMessage();
		$Pm->paymentLog($payMethod, $srt, $pdo, $userId, 2);
		exit("ERR: " . $srt);
	}
}

if(isset($_GET['result_lp']) && $_GET['result_lp'] == 'get') {
	$payMethod = 'lp';
	$currentCurrency = Payments::getCashierCurrency($payMethod);

	try {
		if(empty($_POST['data']) || empty($_POST['signature'])) {
			throw new Exception('empty data');
		}

		$LiqPay = new LiqPay($bankConf->lp_public_key, $bankConf->lp_private_key);
		$params = $LiqPay->decode_params($_POST['data']);

		if(
			base64_encode(
				sha1($bankConf->lp_private_key . $_POST['data'] . $bankConf->lp_private_key, 1)
			) != $_POST['signature']
		) {
			throw new Exception('bad sign');
		}

		if(
			empty($params['amount'])
			|| empty($params['currency'])
			|| empty($params['order_id'])
			|| empty($params['status'])
			|| empty($params['action'])
			|| empty($params['info'])
		) {
			throw new Exception('empty data');
		}

		$amount    = clean($params['amount'], 'float');
		$payNumber = clean($params['order_id'], 'int');
		$userId    = clean($params['info'], 'int');

		if($params['action'] != 'pay' || $params['status'] != 'success') {
			throw new Exception('status not success');
		}

		if($params['currency'] != $currentCurrency) {
			throw new Exception('invalid request');
		}

		$userInfo = $Pm->getUser($pdo, $userId);
		if(empty($userInfo->id)) {
			throw new Exception('unknown user');
		} else {
			if($Pm->issetPay($pdo, $payMethod, $payNumber)) {
				exit('OK');
			}

			$playground = new Playground(pdo(), $conf);

			if($playground->is_bonuses()):
				$playground->add_bonuses($userInfo->id, $amount);
			endif;

			$Pm->doPayAction($pdo, $userInfo, $amount, $conf->bank, $payMethod, $payNumber, $messages['RUB']);
			exit('OK');
		}
	} catch(Exception $e) {
		if(empty($userId)) {
			$userId = 0;
		}

		$Pm->paymentLog($payMethod, $e->getMessage(), $pdo, $userId, 2);
		http_response_code(500);
		exit($e->getMessage());
	}
}

if(isset($_GET['result_ap']) && $_GET['result_ap'] == 'get') {
	$payMethod = 'ap';
	$currentCurrency = Payments::getCashierCurrency($payMethod);

	try {
		if(
			empty($_POST['amount'])
			|| empty($_POST['merchant_id'])
			|| empty($_POST['currency'])
			|| empty($_POST['pay_id'])
			|| empty($_POST['sign'])
			|| empty($_POST['status'])
			|| empty($_POST['user_id'])
		) {
			throw new Exception('empty data');
		}

		$amount    = clean($_POST['amount'], 'float');
		$payNumber = clean($_POST['pay_id'], 'int');
		$userId    = clean($_POST['user_id'], 'int');

		if($_POST['status'] != 'paid') {
			exit();
		}

		if($_POST['currency'] != $currentCurrency) {
			throw new Exception('invalid request');
		}

		$sign = md5(
			implode(
				':',
				[
					$_POST['merchant_id'],
					$_POST['amount'],
					$_POST['pay_id'],
					$bankConf->ap_private_key
				]
			)
		);

		if($sign != $_POST['sign']) {
			throw new Exception('bad sign');
		}

		$userInfo = $Pm->getUser($pdo, $userId);
		if(empty($userInfo->id)) {
			throw new Exception('unknown user');
		} else {
			if($Pm->issetPay($pdo, $payMethod, $payNumber)) {
				exit('OK');
			}

			$playground = new Playground(pdo(), $conf);

			if($playground->is_bonuses()):
				$playground->add_bonuses($userInfo->id, $amount);
			endif;

			$Pm->doPayAction($pdo, $userInfo, $amount, $conf->bank, $payMethod, $payNumber, $messages['RUB']);
			exit('OK');
		}
	} catch(Exception $e) {
		if(empty($userId)) {
			$userId = 0;
		}

		$Pm->paymentLog($payMethod, $e->getMessage(), $pdo, $userId, 2);
		http_response_code(500);
		exit($e->getMessage());
	}
}

if(isset($_GET['enot']) && $_GET['enot'] == 'pay') {
	$payMethod = 'enot';
	$currentCurrency = Payments::getCashierCurrency($payMethod);

	try {
		if(
			empty($_POST['amount'])
			|| empty($_POST['merchant'])
			|| empty($_POST['merchant_id'])
			|| empty($_POST['currency'])
			|| empty($_POST['custom_field'])
			|| empty($_POST['sign_2'])
		) {
			throw new Exception('empty data');
		}

		$amount    = clean($_POST['amount'], 'float');
		$payNumber = clean($_POST['merchant_id'], 'int');
		$userId    = clean($_POST['custom_field'], 'int');

		if($_POST['currency'] != $currentCurrency) {
			throw new Exception('invalid request');
		}

		$sign = md5(
			$_POST['merchant'] . ':' . $_POST['amount'] . ':' . $bankConf->enot_key2 . ':' . $_POST['merchant_id']
		);

		if($sign != $_POST['sign_2']) {
			throw new Exception('bad sign');
		}

		$userInfo = $Pm->getUser($pdo, $userId);
		if(empty($userInfo->id)) {
			throw new Exception('unknown user');
		} else {
			if($Pm->issetPay($pdo, $payMethod, $payNumber)) {
				exit('OK');
			}

			$playground = new Playground(pdo(), $conf);

			if($playground->is_bonuses()):
				$playground->add_bonuses($userInfo->id, $amount);
			endif;

			$Pm->doPayAction($pdo, $userInfo, $amount, $conf->bank, $payMethod, $payNumber, $messages['RUB']);
			exit('OK');
		}
	} catch(Exception $e) {
		if(empty($userId)) {
			$userId = 0;
		}

		$Pm->paymentLog($payMethod, $e->getMessage(), $pdo, $userId, 2);
		http_response_code(500);
		exit($e->getMessage());
	}
}

$fail = '';
if(
	(isset($_GET['result_wo']) && $_GET['result_wo'] == 'fail')
	|| (isset($_GET['result_ik']) && $_GET['result_ik'] == 'fail')
	|| (isset($_GET['result']) && $_GET['result'] == 'fail')
	|| (isset($_GET['result_rb']) && $_GET['result_rb'] == 'fail')
	|| (isset($_GET['result_wb']) && $_GET['result_wb'] == 'fail')
	|| (isset($_GET['result_ps']) && $_GET['result_ps'] == 'fail')
	|| (isset($_GET['method']) && $_GET['method'] == 'ERROR')
) {
	$fail = 1;
}

$success = '';
if(
	(isset($_GET['result_ps']) && $_GET['result_ps'] == 'success')
	|| (isset($_GET['result_rb']) && $_GET['result_rb'] == 'success')
	|| (isset($_GET['result_wb']) && $_GET['result_wb'] == 'success')
	|| (isset($_GET['result']) && $_GET['result'] == 'success')
	|| (isset($_GET['result_ik']) && $_GET['result_ik'] == 'success')
	|| (isset($_GET['result_qw']) && $_GET['result_qw'] == 'success')
	|| (isset($_GET['result_ya']) && $_GET['result_ya'] == 'success')
	|| (isset($_GET['result_wo']) && $_GET['result_wo'] == 'success')
) {
	$success = 1;
}

if(!is_auth()) {
	show_error_page('not_auth');
}

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $page->title);
$tpl->set("{name}", $conf->name);
$tpl->compile('title');
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{site_name}", $conf->name);
$tpl->set("{image}", $page->image);
$tpl->set("{robots}", $page->robots);
$tpl->set("{type}", $page->kind);
$tpl->set("{description}", $page->description);
$tpl->set("{keywords}", $page->keywords);
$tpl->set("{url}", $page->full_url);
$tpl->set("{other}", '');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile('content');
$tpl->clear();

$menu = $tpl->get_menu($pdo);

$nav = [
	$PI->to_nav('main', 0, 0),
	$PI->to_nav('users', 0, 0),
	$PI->to_nav('profile', 0, $user->id, $user->login),
	$PI->to_nav('purse', 1, 0)
];
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

include_once "inc/authorized.php";

if(isset($_GET['price'])) {
	$price = clean($_GET['price'], 'float');
} else {
	$price = '';
}

$bonusesActivity = pdo()->query("SELECT bonuses FROM config__secondary LIMIT 1")
	->fetch(PDO::FETCH_OBJ)
	->bonuses;

$bonuses = unserialize(
	pdo()->query("SELECT data FROM config__strings WHERE id = 3 LIMIT 1")
		->fetch(PDO::FETCH_OBJ)
		->data
);

	$tpl
	->load_template("/home/purse.tpl")
	->set("{site_host}", $site_host)
	->set("{template}", $conf->template)
	->set("{profile_id}", $user->id)
	->set("{balance}", $user->shilings)
	->set("{proc}", $user->proc)
	->set("{price}", $price)
	->set("{fail}", $fail)
	->set("{success}", $success)
	->set("{login}", $_SESSION['login'])
	->set("{bonusesActivity}", $bonusesActivity)
	->set("{rb}", $bankConf->rb)
	->set("{wb}", $bankConf->wb)
	->set("{up}", $bankConf->up)
	->set("{enot}", $bankConf->enot)
	->set("{ps}", $bankConf->ps)
	->set("{fk}", $bankConf->fk)
	->set("{fk_new}", $bankConf->fk_new)
	->set("{ik}", $bankConf->ik)
	->set("{wo}", $bankConf->wo)
	->set("{ya}", $bankConf->ya)
	->set("{qw}", $bankConf->qw)
	->set("{lava}", $bankConf->lava)
	->set("{lp}", $bankConf->lp)
	->set("{ap}", $bankConf->ap)
	->set("{amara}", $bankConf->amarapay)
	->compile('content')
	->clear();
?>