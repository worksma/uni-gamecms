<?php
include_once "../inc/start.php";
include_once "../inc/protect.php";

if(empty($_POST['phpaction'])) {
	log_error("Прямой вызов actions_m.php");
	exit(json_encode(['status' => '2']));
}
if(!is_auth()) {
	exit(json_encode(['status' => '2']));
}
if($conf->token == 1 && ($_SESSION['token'] != clean($_POST['token'], null))) {
	log_error("Неверный токен");
	exit(json_encode(['status' => '2']));
}

if(isset($_POST['refill_balance'])) {
	$amount      = check($_POST['number'], 'float');
	$cashierSlug = check($_POST['type'], null);

	try {
		if(empty($amount)) {
			throw new Exception('Вы не указали сумму!');
		}

		if($amount > 10000) {
			throw new Exception('Не более 10000 ' . $messages['RUB']);
		}

		$row = pdo()->query("SELECT min_amount FROM config__secondary LIMIT 1")->fetch(PDO::FETCH_OBJ);

		if($amount < $row->min_amount) {
			throw new Exception('Не менее ' . $row->min_amount . $messages['RUB']);
		}

		$Pm = new Payments;

		$payId      = $Pm->generatePayId();
		$orderDesc  = $Pm->generatePayDescription(clean_name($conf->name));
		$cashierKey = $Pm->getCashierKey($cashierSlug);

		if(empty($cashierKey) && $cashierKey !== 0) {
			throw new Exception('Неверный тип');
		}

		if(!$Pm->isCashierEnable(pdo(), $cashierKey)) {
			throw new Exception('Способ оплаты неактивен');
		}

		$cashier = $Pm->getCashier($cashierKey);

		$cashierSettings = pdo()->query("SELECT * FROM config__bank LIMIT 1")->fetch(PDO::FETCH_OBJ);
		$cashierSettings->rubusd = configs()->usd;

		switch($cashier['slug']) {
			case 'amarapay':
				if(empty($cashierSettings->amarapay_id) || empty($cashierSettings->amarapay_public)) {
					throw new Exception('Способ оплаты не настроен');
				}
				
				$id 			= $cashierSettings->amarapay_id;
				$public			= $cashierSettings->amarapay_public;
				$orderNumber	= substr($payId, 5);
				
				$parameters = [
					'shop_id'			=> $id,
					'amount'			=> $amount,
					'label'				=> $_SESSION['id'],
					'hash'				=> hash("sha256", $id.$amount.$public.$_SESSION['id'])
				];

				$url = 'https://amarapay.com/merchant';
				Payments::showLink($url, $parameters);

				break;
			break;
			case 'ik':
				if(empty($cashierSettings->ik_login) || empty($cashierSettings->ik_pass1)) {
					throw new Exception('Способ оплаты не настроен');
				}

				$currency = Payments::getCashierCurrency('ik');

				$parameters = [
					'ik_am'    => $amount,
					'ik_co_id' => $cashierSettings->ik_login,
					'ik_cur'   => $currency,
					'ik_desc'  => $orderDesc,
					'ik_pm_no' => $payId,
					'ik_x_id'  => $user->id
				];
				ksort($parameters, SORT_STRING);

				$url                   = 'https://sci.interkassa.com/';
				$parameters['ik_sign'] = base64_encode(
					md5(implode(':', array_merge($parameters, [$cashierSettings->ik_pass1])), true)
				);

				Payments::showForm($url, $parameters);

				break;
			case 'payeer':
				if(empty($cashierSettings->payeer_id) || empty($cashierSettings->payeer_secret)) {
					throw new Exception('Способ оплаты не настроен');
				}

				$amount = number_format($amount,2); //IMPORTANT !!!
				$currency = Payments::getCashierCurrency('payeer');
				$parameters = array(
					'm_shop' => $cashierSettings->payeer_id,
					'm_amount'    => $amount,
					'm_curr'   => $currency,
					'm_orderid' => $user->id."_".$payId,
					'm_desc'  => base64_encode($orderDesc)
				);
				$arHash = array($cashierSettings->payeer_id, $parameters['m_orderid'], $amount, $currency, $parameters['m_desc'], $cashierSettings->payeer_secret);
				$url                   = 'https://payeer.com/merchant/';
				$parameters['m_sign'] = strtoupper(hash('sha256', implode(':', $arHash)));

				Payments::showForm($url, $parameters);

				break;
			case 'perfectmoney':
				if(empty($cashierSettings->perfectmoney_id) || empty($cashierSettings->perfectmoney_secret)) {
					throw new Exception('Способ оплаты не настроен');
				}
				$currency = Payments::getCashierCurrency('perfectmoney');
				// ????
				if ($cashierSettings->rubusd)
				$amount = $amount / $cashierSettings->rubusd;
				$amount = number_format($amount,2); //IMPORTANT !!!
				$currency = 'USD';
				
				$parameters = array(
					'PAYEE_ACCOUNT' => $cashierSettings->perfectmoney_id,
					'PAYEE_NAME' => $conf->name,
					'PAYMENT_AMOUNT' => $amount,
					'PAYMENT_UNITS' => $currency,
					'SUGGESTED_MEMO' => substr($orderDesc, 0, 100),
					'PAYMENT_ID' => $user->id."_".$payId,
					'PAYMENT_URL' => $full_site_host . 'purse?result=success',
					'PAYMENT_URL_METHOD' => 'POST',
					'NOPAYMENT_URL' => $full_site_host . 'purse?result=fail',
					'NOPAYMENT_URL_METHOD' => 'POST',
					'STATUS_URL' => $full_site_host . 'purse?pm=get',
				);
				$url = 'https://perfectmoney.is/api/step1.asp';
				Payments::showForm($url, $parameters);
				
				break;
			case 'ya':
				if(empty($cashierSettings->ya_num) || empty($cashierSettings->ya_key)) {
					throw new Exception('Способ оплаты не настроен');
				}

				$payId = str_pad($user->id, 7, "0", STR_PAD_LEFT) . substr($payId, 5);

				$url        = 'https://yoomoney.ru/quickpay/confirm.xml';
				$parameters = [
					'receiver'      => $cashierSettings->ya_num,
					'formcomment'   => '',
					'short-dest'    => '',
					'label'         => $payId,
					'quickpay-form' => 'donate',
					'targets'       => $orderDesc,
					'sum'           => $amount,
					'comment'       => '',
					'need-fio'      => 'false',
					'need-email'    => 'false',
					'need-phone'    => 'false',
					'need-address'  => 'false',
					'paymentType'   => '',
					'successURL'    => $full_site_host . 'purse?result_ya=success',
				];

				Payments::showForm($url, $parameters);

				break;
			case 'fk_new':
				if(empty($cashierSettings->fk_new_login) || empty($cashierSettings->fk_new_pass1)) {
					throw new Exception('Способ оплаты не настроен');
				}

				$cashierId        = $cashierSettings->fk_new_login;
				$cashierPublicKey = htmlspecialchars_decode($cashierSettings->fk_new_pass1);
				$orderNumber      = substr($payId, 5);
				$currency         = Payments::getCashierCurrency('fk_new');

				$parameters = [
					'm'        => $cashierId,
					'oa'       => $amount,
					'o'        => $orderNumber,
					'currency' => $currency,
					's'        => md5(
						"$cashierId:$amount:$cashierPublicKey:$currency:$orderNumber"
					),
					'us_user'  => $user->id
				];

				if(!empty($user->email) && (substr($user->email, 0, 6) != 'vk_id_')) {
					$parameters['em'] = $user->email;
				}

				$url = 'https://pay.freekassa.ru/';

				Payments::showLink($url, $parameters);

				break;
			case 'rb':
				if(empty($cashierSettings->rb_login) || empty($cashierSettings->rb_pass1)) {
					throw new Exception('Способ оплаты не настроен');
				}

				$url        = 'https://auth.robokassa.ru/Merchant/Index.aspx';
				$payId      = substr($payId, 5);
				$parameters = [
					'MrchLogin'      => $cashierSettings->rb_login,
					'OutSum'         => $amount,
					'InvId'          => $payId,
					'Desc'           => $orderDesc,
					'SignatureValue' => md5(
						"$cashierSettings->rb_login:$amount:$payId:$cashierSettings->rb_pass1:Shp_zuser=$user->id"
					),
					'Shp_zuser'      => $user->id
				];

				Payments::showLink($url, $parameters);

				break;
			case 'wb':
				if(empty($cashierSettings->wb_login) || empty($cashierSettings->wb_num)) {
					throw new Exception('Способ оплаты не настроен');
				}

				$url        = 'https://merchant.webmoney.ru/lmi/payment.asp';
				$parameters = [
					'LMI_PAYMENT_NO'          => time(),
					'LMI_PAYMENT_AMOUNT'      => $amount,
					'LMI_PAYMENT_DESC_BASE64' => base64_encode($cashierSettings->wb_login),
					'LMI_PAYEE_PURSE'         => $cashierSettings->wb_num,
					'id'                      => $user->id,
				];
				Payments::showForm($url, $parameters);

				break;
			case 'up':
				if(empty($cashierSettings->up_pass1) || empty($cashierSettings->up_pass2)) {
					throw new Exception('Способ оплаты не настроен');
				}

				$unitPay = new UnitPay(
					$cashierSettings->up_type == 1 ? 'unitpay.money' : 'unitpay.ru',
					$cashierSettings->up_pass2
				);

				if($cashierSettings->up_type == 2) {
					$unitPay->setCustomerEmail($user->email);
					$cashItem = new CashItem($orderDesc, 1, $amount);
					$unitPay->setCashItems([$cashItem]);
				}

				$url = $unitPay->form(
					$cashierSettings->up_pass1,
					$amount,
					$user->id,
					$orderDesc,
					Payments::getCashierCurrency('up')
				);

				Payments::showLink($url);

				break;
			case 'ps':
				if(empty($cashierSettings->ps_pass) || empty($cashierSettings->ps_num)) {
					throw new Exception('Способ оплаты не настроен');
				}

				require_once('../inc/classes/class.paysera.php');

				try {
					WebToPay::redirectToPayment(
						[
							'projectid'     => $cashierSettings->ps_num,
							'sign_password' => $cashierSettings->ps_pass,
							'orderid'       => $payId,
							'amount'        => $amount * 100,
							'currency'      => $cashierSettings->ps_currency,
							'accepturl'     => $full_site_host . 'purse?result_ps=success',
							'cancelurl'     => $full_site_host . 'purse?result_ps=fail',
							'callbackurl'   => $full_site_host . 'purse?result_ps=get',
							'test'          => $cashierSettings->ps_test,
							'zzz'           => $user->id,
						]
					);
				} catch(WebToPayException $exception) {
					Payments::showError($exception->getMessage());
				}

				break;
			case 'wo':
				if(empty($cashierSettings->wo_login) || empty($cashierSettings->wo_pass)) {
					throw new Exception('Способ оплаты не настроен');
				}

				$fields = [
					'WMI_MERCHANT_ID'    => $cashierSettings->wo_login,
					'WMI_PAYMENT_AMOUNT' => $amount . '.00',
					'WMI_CURRENCY_ID'    => '643',
					'WMI_PAYMENT_NO'     => substr($payId, 5),
					'WMI_DESCRIPTION'    => 'BASE64:' . base64_encode($orderDesc),
					'WMI_SUCCESS_URL'    => $full_site_host . 'purse?result_wo=success',
					'WMI_FAIL_URL'       => $full_site_host . 'purse?result_wo=fail',
					'user_id'            => $user->id,
				];

				uksort($fields, 'strcasecmp');

				$fieldValues = '';
				foreach($fields as $value) {
					$value       = iconv('utf-8', 'windows-1251', $value);
					$fieldValues .= $value;
				}

				$fields['WMI_SIGNATURE'] = base64_encode(pack('H*', md5($fieldValues . $cashierSettings->wo_pass)));
				$url                     = 'https://wl.walletone.com/checkout/checkout/Index';

				Payments::showForm($url, $fields);

				break;
			case 'qw':
				if(empty($cashierSettings->qw_pass)) {
					throw new Exception('Способ оплаты не настроен');
				}

				try {
					$Qiwi = new Qiwi($cashierSettings->qw_pass);



					$data = [
						'amount'             => $amount * 1.00,
						'currency'           => Payments::getCashierCurrency('qw'),
						'comment'            => $orderDesc,
						'expirationDateTime' => $Qiwi->getLifetimeByDay(1),
						'email'              => $user->email,
						'account'            => $user->id,
						'successUrl'         => $full_site_host . 'purse?result_qw=success',
					];

					if(
							isset($config_additional)
							&& array_key_exists('qiwiThemeCode', $config_additional)
					) {
						$data['customFields'] = [
								'themeCode' => $config_additional['qiwiThemeCode']
						];
					}

					$result = $Qiwi->createBill($Qiwi->generateId(), $data);

					Payments::showLink($result['payUrl']);
				} catch(Exception $exception) {
					Payments::showError($exception->getMessage());
				}

				break;
			case "lava":
				if(empty($cashierSettings->lava_token)) {
					throw new Exception('Способ оплаты не настроен');
				}
				try {
					return Lava::g2p($amount);
				}
				catch(Exception $exception) {
					exit(json_encode(['alert' => 'error', 'message' => $e->getMessage()]));
				}
			break;
			case 'lp':
				if(empty($cashierSettings->lp_public_key) || empty($cashierSettings->lp_private_key)) {
					throw new Exception('Способ оплаты не настроен');
				}

				$LiqPay = new LiqPay($cashierSettings->lp_public_key, $cashierSettings->lp_private_key);

				$parameters = $LiqPay->cnb_form_raw(
					[
						'public_key'  => $cashierSettings->lp_public_key,
						'amount'      => $amount,
						'version'     => '3',
						'action'      => 'pay',
						'order_id'    => $payId,
						'currency'    => Payments::getCashierCurrency('lp'),
						'description' => $orderDesc,
						'info'        => $user->id,
						'result_url'  => $full_site_host . 'purse',
						'server_url'  => $full_site_host . 'purse?result_lp=get',
					]
				);

				Payments::showForm(
					$parameters['url'],
					[
						'data'      => $parameters['data'],
						'signature' => $parameters['signature']
					]
				);

				break;
			case 'ap':
				if(empty($cashierSettings->ap_project_id) || empty($cashierSettings->ap_private_key)) {
					throw new Exception('Способ оплаты не настроен');
				}

				$url        = 'https://anypay.io/merchant';
				$parameters = [
					'merchant_id' => $cashierSettings->ap_project_id,
					'pay_id'      => $payId,
					'amount'      => $amount,
					'currency'    => Payments::getCashierCurrency('ap'),
					'desc'        => $orderDesc,
					'user_id'     => $user->id,
				];

				$parameters['sign'] = md5(
					implode(
						':',
						[
							$parameters['currency'],
							$parameters['amount'],
							$cashierSettings->ap_private_key,
							$cashierSettings->ap_project_id,
							$parameters['pay_id']
						]
					)
				);

				Payments::showForm($url, $parameters);
				break;
			case 'enot':
				if(
					empty($cashierSettings->enot_id)
					|| empty($cashierSettings->enot_key)
					|| empty($cashierSettings->enot_key2)
				) {
					throw new Exception('Способ оплаты не настроен');
				}

				$url        = 'https://enot.io/pay';
				$parameters = [
					'm' => $cashierSettings->enot_id,
					'oa' => $amount,
					'o' => $payId,
					'cr' => Payments::getCashierCurrency('ap'),
					'c' => $orderDesc,
					'cf' => $user->id,
					's' => md5(
						$cashierSettings->enot_id . ':' . $amount . ':' . $cashierSettings->enot_key . ':' . $payId
					)
				];

				Payments::showForm($url, $parameters);
				break;
		}

		?>
		<script>
          NProgress.done();
          setTimeout(show_ok, 500);
		</script>
		<?php
	} catch(Exception $e) {

		?>
		<script>
          show_input_error(
            'number_<?php echo $cashierSlug; ?>',
            '<?php echo $e->getMessage(); ?>'
          );
          NProgress.done();
          setTimeout(show_error, 500);
		</script>
		<?php
	}

	exit;
}
if(isset($_POST['get_operations'])) {

	if(array_key_exists('page', $_POST)) {
		$page = checkJs($_POST['page'], "int");
	}

	if(empty($page)) {
		$page = 1;
	}

	$limit        = 10;
	$start        = ($page - 1) * $limit;
	$globalRowNum = $start;
	$localRowNum  = 0;

	$tpl      = new Template;
	$tpl->dir = '../templates/' . $conf->template . '/tpl/';

	$STH = $pdo->prepare(
		"SELECT 
					    `money__actions`.*, 
					    `money__actions_types`.`name`, 
					    `money__actions_types`.`class` 
					FROM 
					    `money__actions` 
						INNER JOIN 
						        `money__actions_types` 
						            ON 
						                `money__actions_types`.`id` = `money__actions`.`type`
					WHERE 
					    `money__actions`.`author`=:author 
					ORDER BY `money__actions`.`date` DESC LIMIT " . $start . ", " . $limit
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':author' => $_SESSION['id']]);
	while($row = $STH->fetch()) {
		$globalRowNum++;
		$localRowNum++;

		$tpl->load_template('elements/money_action.tpl');
		$tpl->set("{shilings}", $row->shilings);
		$tpl->set("{type}", collect_consumption_str(1, $row->type, $row->class, $row->name, $pdo, $row->gave_out));
		$tpl->set("{date}", expand_date($row->date, 7));
		$tpl->compile('content');
		$tpl->clear();
	}
	if($globalRowNum == 0) {
		$tpl->result['content'] = '<tr><td colspan="10"><span class="empty-element">Пусто</span></td></tr>';
	} elseif(($localRowNum > $limit - 1)) {
		$page++;
		$tpl->result['content'] .= '<tr id="loader' . $page . '" class="c-p" onclick="get_operations(\'' . $page . '\');"><td colspan="10">Подгрузить записи</td></tr>';
	}
	$tpl->show($tpl->result['content']);
	$tpl->global_clear();
	exit();
}
if(isset($_POST['buy_service'])) {
	ignore_user_abort(1);
	set_time_limit(0);

	$server   = checkJs($_POST['server'], "int");
	$service  = checkJs($_POST['service'], "int");
	$tarif    = checkJs($_POST['tarifs'], "int");
	$type     = checkJs($_POST['type'], "int");
	$nick     = checkJs($_POST['nick'], null);
	$pass     = checkJs($_POST['pass'], null);
	$steam_id = checkJs($_POST['steam_id'], null);
	$check1   = checkJs($_POST['check1'], "int");
	$check2   = checkJs($_POST['check2'], "int");

	if(empty($check1)) {
		$check1 = 0;
	}
	if(empty($check2)) {
		$check2 = 0;
	}

	if($conf->cont == 1) {
		$STH = $pdo->query("SELECT `vk` FROM `users` WHERE `id`='$_SESSION[id]' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if($row->vk == '---' or empty($row->vk)) {
			exit (
				json_encode(
					[
						'status' => '3',
						'data'   => 'Укажите свой аккаунт Вконтакте в <a href="../settings" target="_blank">профиле</a>'
					]
				)
			);
		}
	}

	if(empty($server) or empty($service) or empty($tarif) or empty($type)) {
		exit (json_encode(['status' => '3', 'data' => 'Заполните все поля!']));
	}

	$STH = $pdo->query(
		"SELECT id,type,ip,port,name,pass_prifix,discount,binds FROM servers WHERE id='$server' LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$server = $STH->fetch();
	if(empty($server->id)) {
		exit (json_encode(['status' => '3', 'data' => 'Данного сервера не существует']));
	}
	if(empty($server->type)) {
		exit (json_encode(['status' => '3', 'data' => 'Невозможно подключение к FTP и DB серверу']));
	}
	$server->address = $server->ip . ':' . $server->port;
	$binds           = explode(';', $server->binds);

	if($type != '1' and $type != '2' and $type != '3') {
		exit (json_encode(['status' => '3', 'data' => 'Неверно указан тип!']));
	}

	if(($binds[0] == 0 and $type == 1) || ($binds[1] == 0 and $type == 2) || ($binds[2] == 0 and $type == 3)) {
		exit (json_encode(['status' => '3', 'data' => 'Данный тип запрещен!']));
	}

	$SIDO = new SteamIDOperations();

	if($type == '1') {
		$admin['type'] = 'a';
		if(empty($nick)) {
			exit (json_encode(['status' => '2', 'input' => 'player_nick', 'reply' => 'Заполните!']));
		}
		if(mb_strlen($_POST['nick'], 'UTF-8') > 32) {
			exit (json_encode(['status' => '2', 'input' => 'player_nick', 'reply' => 'Не более 32 символов!']));
		}
		if(empty($pass)) {
			exit (json_encode(['status' => '2', 'input' => 'player_pass', 'reply' => 'Заполните!']));
		}
		if(mb_strlen($pass, 'UTF-8') > 32) {
			exit (json_encode(['status' => '2', 'input' => 'player_pass', 'reply' => 'Не более 32 символов!']));
		}
		$admin['name']     = $nick;
		$admin['pass']     = $pass;
		$admin['pass_md5'] = md5($pass);
	}
	if($type == '2') {
		$admin['type'] = 'ce';
		if(empty($steam_id)) {
			exit (json_encode(['status' => '2', 'input' => 'player_steam_id', 'reply' => 'Заполните!']));
		}
		if(mb_strlen($steam_id, 'UTF-8') > 32) {
			exit (json_encode(['status' => '2', 'input' => 'player_steam_id', 'reply' => 'Не более 32 символов!']));
		}
		if(!$steam_id = $SIDO->GetSteamID32($steam_id)) {
			exit (json_encode(['status' => '2', 'input' => 'player_steam_id', 'reply' => 'Неверный STEAM ID!']));
		}
		$admin['name']     = $steam_id;
		$admin['pass']     = '';
		$admin['pass_md5'] = '';
	}
	if($type == '3') {
		$admin['type'] = 'ca';
		if(empty($steam_id)) {
			exit (json_encode(['status' => '2', 'input' => 'player_steam_id', 'reply' => 'Заполните!']));
		}
		if(mb_strlen($steam_id, 'UTF-8') > 32) {
			exit (json_encode(['status' => '2', 'input' => 'player_steam_id', 'reply' => 'Не более 32 символов!']));
		}
		if(!$steam_id = $SIDO->GetSteamID32($steam_id)) {
			exit (json_encode(['status' => '2', 'input' => 'player_steam_id', 'reply' => 'Неверный STEAM ID!']));
		}
		if(empty($pass)) {
			exit (json_encode(['status' => '2', 'input' => 'player_pass', 'reply' => 'Заполните!']));
		}
		if(mb_strlen($pass, 'UTF-8') > 32) {
			exit (json_encode(['status' => '2', 'input' => 'player_pass', 'reply' => 'Не более 32 символов!']));
		}
		$admin['name']     = $steam_id;
		$admin['pass']     = $pass;
		$admin['pass_md5'] = md5($pass);
	}

	$AM = new AdminsManager;
	if(!$AM->check_for_bad_nicks($pdo, $admin['name'])) {
		exit (json_encode(['status' => '3', 'data' => 'Использовать данный идентификатор запрещено!']));
	}

	if($admin['type'] == 'ce' || $admin['type'] == 'ca') {
		$STH = $pdo->prepare(
			"SELECT id,user_id,active,pause,name FROM admins WHERE name LIKE :name AND server=:server LIMIT 1"
		);

		$adminNameForCheck = '%' . strip_data(':' . explode(':', $admin['name'])[2]);
	} else {
		$STH = $pdo->prepare(
			"SELECT id,user_id,active,pause,name FROM admins WHERE name=:name AND server=:server LIMIT 1"
		);

		$adminNameForCheck = $admin['name'];
	}

	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':name' => $adminNameForCheck, ':server' => $server->id]);
	$row = $STH->fetch();
	if(isset($row->id)) {
		if(
				$row->user_id == $_SESSION['id']
				&& $row->name == $admin['name']
		) {
			if($check1 == 0) {
				exit (json_encode(['status' => '4']));
			}
		} else {
			exit (json_encode(['status' => '3', 'data' => 'На сервере уже имеется администратор с такими данными!']));
		}

		if($row->active == 2) {
			exit (json_encode(['status' => '3', 'data' => 'Данный аккаунт заблокирован!']));
		}

		if($row->pause != 0) {
			exit (json_encode(['status' => '3', 'data' => 'Данный аккаунт приостановлен!']));
		}

		$admin['id']         = $row->id;
		$admin['has_rights'] = 1;
	} else {
		$admin['has_rights'] = 0;
	}

	$STH = $pdo->query("SELECT * FROM services WHERE id='$service' and server='$server->id' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$service = $STH->fetch();
	if(empty($service->id)) {
		exit (json_encode(['status' => '3', 'data' => 'Услуга не найдена']));
	}
	if($service->sale == 2) {
		exit (json_encode(['status' => '3', 'data' => 'Услуга не продается']));
	}
	if($service->users_group != 0) {
		if($check2 == 0) {
			$STH = $pdo->prepare("SELECT name FROM users__groups WHERE id=:id LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute([':id' => $service->users_group]);
			$row = $STH->fetch();

			exit (json_encode(['status' => '5', 'group' => $row->name]));
		}
	}

	if(isset($admin['id'])) {
		$STH = $pdo->prepare("SELECT id FROM admins__services WHERE service=:service AND admin_id=:admin_id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':service' => $service->id, ':admin_id' => $admin['id']]);
		$row = $STH->fetch();
		if(isset($row->id)) {
			exit (
				json_encode(
					[
						'status' => '3',
						'data'   => 'У Вас уже имеется данная услуга. Если Вы хотите её продлить, перейдите в раздел <a href="../my_stores" target="_blank">Мои услуги</a>'
					]
				)
			);
		}
	}

	$STH = $pdo->query(
		"SELECT id,price,time,discount FROM services__tarifs WHERE id='$tarif' and service='$service->id' LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tarif = $STH->fetch();
	if(empty($tarif->id)) {
		exit (json_encode(['status' => '3', 'data' => 'Тариф не найден']));
	}

	$STH = $pdo->query("SELECT discount FROM config__prices LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	$proc                   = calculate_discount(
		$server->discount,
		$row->discount,
		$user->proc,
		$service->discount,
		$tarif->discount
	);
	$price                  = calculate_price($tarif->price, $proc);
	$admin['irretrievable'] = calculate_return($price, $tarif->time);

	$STH = $pdo->query("SELECT id,shilings FROM users WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(empty($row->id)) {
		exit (json_encode(['status' => '3', 'data' => 'Пользователь не найден']));
	}
	if($row->shilings < $price) {
		$price_delta = round_shilings($price - $row->shilings);
		exit (
			json_encode(
				[
					'status' => '3',
					'data'   => 'У Вас недостаточно средств <span class="m-icon icon-bank"></span><br><a href="../purse?price=' . $price_delta . '">Пополните баланс на ' . $price_delta . $messages['RUB'] . '.</a>'
				]
			)
		);
	}
	$shilings = round_shilings($row->shilings - $price);

	if($server->type == 4) {
		$STH = $pdo->query("SELECT nick FROM users WHERE id='$_SESSION[id]' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if($row->nick == '---' or empty($row->nick)) {
			exit (
				json_encode(
					[
						'status' => '3',
						'data'   => 'Заполните в своем <a href="../settings" target="_blank">профиле</a> поле ник'
					]
				)
			);
		}

		if($admin['has_rights'] == 1) {
			if($service->sb_group != '') {
				$STH = $pdo->query(
					"SELECT `admins__services`.`id` FROM `admins__services` LEFT JOIN `services` ON `admins__services`.`service` = `services`.`id` WHERE `services`.`sb_group`!='' AND `admins__services`.`admin_id` = '$admin[id]' LIMIT 1"
				);
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$row = $STH->fetch();
				if(isset($row->id)) {
					exit (json_encode(['status' => '3', 'data' => 'Данные услуги объединить невозможно!']));
				}
			}
		}
	}

	if($server->type == 1 || $server->type == 3) {
		if(stristr(htmlspecialchars_decode($admin['name'], ENT_QUOTES), '"') !== false) {
			exit (json_encode(['status' => '3', 'data' => 'Ваш идентификатор содержит запрещенный символ: "']));
		}
		if(stristr(htmlspecialchars_decode($admin['name'], ENT_QUOTES), '#') !== false) {
			exit (json_encode(['status' => '3', 'data' => 'Ваш идентификатор содержит запрещенный символ: #']));
		}

		if(stristr(htmlspecialchars_decode($admin['pass'], ENT_QUOTES), '"') !== false) {
			exit (json_encode(['status' => '3', 'data' => 'Ваш пароль содержит запрещенный символ: "']));
		}
		if(stristr(htmlspecialchars_decode($admin['pass'], ENT_QUOTES), '#') !== false) {
			exit (json_encode(['status' => '3', 'data' => 'Ваш пароль содержит запрещенный символ: #']));
		}
	}

	if(!$AM->checking_server_status($pdo, $server->id)) {
		exit (json_encode(['status' => '3', 'data' => $messages['server_connect_error']]));
	}

	$admin['ending_date']  = $AM->get_ending_date($tarif->time);
	$admin['bought_date']  = date("Y-m-d H:i:s");
	$admin['service_time'] = $tarif->id;
	$admin['service']      = $service->id;

	if($admin['has_rights'] == 1) {
		$STH = $pdo->prepare(
			"UPDATE admins SET name=:name, pass=:pass, pass_md5=:pass_md5, type=:type WHERE id=:id LIMIT 1"
		);
		if(
			!$STH->execute(
				[
					'name'     => $admin['name'],
					'pass'     => $admin['pass'],
					'pass_md5' => $admin['pass_md5'],
					'type'     => $admin['type'],
					'id'       => $admin['id']
				]
			) == '1'
		) {
			exit (json_encode(['status' => '3', 'data' => 'Ошибка записи админа в базу данных.']));
		}
	} else {
		$STH = $pdo->prepare(
			"INSERT INTO admins (name,pass,pass_md5,type,server,user_id) VALUES (:name, :pass, :pass_md5, :type, :server, :user_id)"
		);
		if(
			!$STH->execute(
				[
					'name'     => $admin['name'],
					'pass'     => $admin['pass'],
					'pass_md5' => $admin['pass_md5'],
					'type'     => $admin['type'],
					'server'   => $server->id,
					'user_id'  => $_SESSION['id']
				]
			) == '1'
		) {
			exit (json_encode(['status' => '3', 'data' => 'Ошибка записи админа в базу данных.']));
		}
	}

	if(empty($admin['id'])) {
		$STH = $pdo->prepare("SELECT id FROM admins WHERE name=:name AND server=:server LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':name' => $admin['name'], ':server' => $server->id]);
		$row         = $STH->fetch();
		$admin['id'] = $row->id;
	}

	if($service->users_group != 0 and $check2 == 1) {
		$STH = $pdo->prepare(
			"SELECT admins__services.previous_group FROM admins__services 
			LEFT JOIN admins ON admins.id = admins__services.admin_id WHERE admins.user_id=:user_id AND admins__services.previous_group!='0' LIMIT 1"
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':user_id' => $_SESSION['id']]);
		$row = $STH->fetch();

		if(isset($row->previous_group)) {
			$admin['previous_group'] = $row->previous_group;
		} else {
			$admin['previous_group'] = $_SESSION['rights'];
		}
	} else {
		$admin['previous_group'] = 0;
	}

	$STH = $pdo->prepare(
		"INSERT INTO admins__services (admin_id,service,service_time,bought_date,ending_date,irretrievable,previous_group) VALUES (:admin_id, :service, :service_time, :bought_date, :ending_date, :irretrievable, :previous_group)"
	);
	if(
		!$STH->execute(
			[
				':admin_id'       => $admin['id'],
				':service'        => $admin['service'],
				':service_time'   => $admin['service_time'],
				':bought_date'    => $admin['bought_date'],
				':ending_date'    => $admin['ending_date'],
				':irretrievable'  => $admin['irretrievable'],
				':previous_group' => $admin['previous_group']
			]
		) == '1'
	) {
		exit (json_encode(['status' => '3', 'data' => 'Ошибка записи прав в базу данных.']));
	}

	if($server->type == 1 || $server->type == 3) {
		if(!$AM->export_to_users_ini($pdo, $server->id, 'BUY_SERVICE')) {
			exit (json_encode(['status' => '3', 'data' => 'Не удалось экспортировать администраторов в файл']));
		}
	} else {
		if(!$AM->export_admin($pdo, $admin['id'], $server->id, 'BUY_SERVICE')) {
			exit (
				json_encode(
					['status' => '3', 'data' => 'Не удалось экспортировать администратора в базу данных сервера']
				)
			);
		}
	}

	if($service->users_group != 0 and $check2 == 1) {
		$STH = $pdo->prepare("UPDATE users SET shilings=:shilings, rights=:rights WHERE id=:id LIMIT 1");
		$STH->execute([':shilings' => $shilings, ':rights' => $service->users_group, ':id' => $_SESSION['id']]);
	} else {
		$STH = $pdo->prepare("UPDATE users SET shilings=:shilings WHERE id=:id LIMIT 1");
		$STH->execute([':shilings' => $shilings, ':id' => $_SESSION['id']]);
	}

	$STH = $pdo->prepare(
		"INSERT INTO money__actions (date,shilings,author,type) VALUES (:date, :shilings, :author, :type)"
	);
	$STH->execute(['date' => date("Y-m-d H:i:s"), 'shilings' => -$price, 'author' => $_SESSION['id'], 'type' => '2']);

	incNotifications();
	$noty = success_buy_noty(
		$admin['name'],
		$admin['pass'],
		$tarif->time,
		$admin['ending_date'],
		$server->name,
		$server->address,
		$service->name,
		$server->pass_prifix
	);
	send_noty($pdo, $noty, $_SESSION['id'], 2);
	$full_mess = $noty;
	$noty      = success_buy_noty_for_admin(
		$_SESSION['id'],
		$_SESSION['login'],
		$tarif->time,
		$admin['ending_date'],
		$server->name,
		$server->address,
		$service->name
	);
	send_noty($pdo, $noty, 0, 2);

	if($tarif->time == 0) {
		$tarif->time = 'Навсегда';
	} else {
		$tarif->time = $tarif->time . " суток";
	}

	service_log(
		"Куплены права - " . $service->name . " (" . $tarif->time . ")",
		$admin['id'],
		$server->id,
		$pdo,
		$service->id
	);

	exit (
		json_encode(
			[
				'status'   => '1',
				'data'     => '<h4>Услуга успешно приобретена!</h4><div>' . $full_mess . '</div>',
				'shilings' => $shilings
			]
		)
	);
}
if(isset($_POST['buy_unban'])) {
	$server = checkJs($_POST['server'], "int");
	$id     = checkJs($_POST['id'], "int");

	if(empty($server) or $server == null or empty($id) or $id == null) {
		exit(json_encode(['status' => '2']));
	}

	$STH = $pdo->query("SELECT * FROM config__prices");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$prices = $STH->fetch();

	$STH = $pdo->query(
		"SELECT id,db_host,db_user,db_pass,db_db,db_prefix,type,db_code FROM servers WHERE id='$server' LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$server = $STH->fetch();
	if(empty($server->id)) {
		exit(json_encode(['status' => '2']));
	}
	if(!in_array($server->type, [2, 3, 4, 5, 6])) {
		exit(json_encode(['status' => '2']));
	}

	if(!$pdo2 = db_connect($server->db_host, $server->db_db, $server->db_user, $server->db_pass)) {
		exit (json_encode(['status' => '2']));
	}
	set_names($pdo2, $server->db_code);

	$table = set_prefix($server->db_prefix, 'bans');
	if($server->type == '2' || $server->type == '3' || $server->type == '5') {
		$table = set_prefix($server->db_prefix, 'bans');
		$STH   = $pdo2->query("SELECT * FROM $table WHERE bid = '$id'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$ban = $STH->fetch();
	} elseif($server->type == '4') {
		$table1 = set_prefix($server->db_prefix, 'bans');
		$STH    = $pdo2->query(
			"SELECT $table1.bid,$table1.ip AS player_ip,$table1.RemoveType AS expired,$table1.authid AS player_id,$table1.name AS player_nick,$table1.created AS ban_created,$table1.length AS ban_length,$table1.reason AS ban_reason FROM $table1 WHERE $table1.bid = '$id'"
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$ban = $STH->fetch();
	}
	if(empty($ban->bid)) {
		exit(json_encode(['status' => '2', 'info' => 'Бан не найден']));
	}

	if($server->type == '2' || $server->type == '3' || $server->type == '5') {
		$ban_length = $ban->ban_length * 60;
	} elseif($server->type == '4') {
		$ban_length = $ban->ban_length;
	}
	$ban_created = $ban->ban_created;
	$temp_time   = date("Y-m-d H:i:s", ($ban_created + $ban_length));
	if($ban->expired == 1 || $ban->expired == "E" || $ban->expired == "U") {
		exit(json_encode(['status' => '2', 'info' => 'Игрок уже разбанен!']));
	} else {
		if($ban_length == 0) {
			$price = $prices->price3;
		} else {
			$now  = time();
			$time = expand_date($temp_time, 1);
			if(($ban_created + $ban_length) < $now) {
				exit(json_encode(['status' => '2', 'info' => 'Игрок уже разбанен!']));
			} else {
				$date = diff_date($temp_time, date("Y-m-d H:i:s"));
				if($date['2'] < '7' and $date['1'] == '0' and $date['0'] == '0') {
					$price = $prices->price1;
				} else {
					$price = $prices->price2;
				}
			}
		}
	}
	$user = $_SESSION['id'];
	$STH  = $pdo->query("SELECT id,shilings FROM users WHERE id='$user' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(empty($row->id)) {
		exit(json_encode(['status' => '2']));
	}
	if($row->shilings < $price) {
		exit (json_encode(['status' => '2', 'info' => 'У Вас недостаточно средств!']));
	}
	$shilings = round_shilings($row->shilings - $price);
	$table    = set_prefix($server->db_prefix, 'bans');
	if($server->type == '2' || $server->type == '3' || $server->type == '5') {
		$STH = $pdo2->prepare(
			"UPDATE `$table` SET `expired`=:expired, `unban_type`=:unban_type, `ban_closed`=:ban_closed WHERE `bid`=:id LIMIT 1"
		);
		$STH->execute([':expired' => '1', ':unban_type' => '-2', ':ban_closed' => $_SESSION['id'], ':id' => $id]);
	} elseif($server->type == '4') {
		$STH = $pdo2->prepare(
			"UPDATE `$table` SET `RemovedBy`=:RemovedBy,`RemoveType`=:RemoveType, `unban_type`=:unban_type, `ban_closed`=:ban_closed WHERE `bid`=:id LIMIT 1"
		);
		$STH->execute(
			[
				':RemovedBy'  => '0',
				':RemoveType' => 'U',
				':unban_type' => '-2',
				':ban_closed' => $_SESSION['id'],
				':id'         => $id
			]
		);
	}
	$ban_nick    = check($ban->player_nick, null);
	$ban_steamid = $ban->player_id;
	$ban_ip      = $ban->player_ip;
	$ban_id      = $ban->bid;

	$STH = $pdo->prepare("UPDATE users SET shilings=:shilings WHERE id='$user' LIMIT 1");
	$STH->execute(['shilings' => $shilings]);

	$STH = $pdo->prepare(
		"INSERT INTO money__actions (date,shilings,author,type) VALUES (:date, :shilings, :author, :type)"
	);
	$STH->execute(['date' => date("Y-m-d H:i:s"), 'shilings' => -$price, 'author' => $user, 'type' => '4']);

	incNotifications();
	$noty = success_buy_unban_noty($ban_nick, $ban_ip, $ban_steamid, $ban_id);
	send_noty($pdo, $noty, $_SESSION['id'], 2);

	$noty = success_buy_unban_noty_for_admin(
		$_SESSION['id'],
		$_SESSION['login'],
		$ban_nick,
		$ban_ip,
		$ban_steamid,
		$ban_id
	);
	send_noty($pdo, $noty, 0, 2);

	exit (json_encode(['status' => '1']));
}
if(isset($_POST['buy_stickers'])) {
	$STH = $pdo->query("SELECT price4 FROM config__prices LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$price = $STH->fetch();
	$price = $price->price4;

	$user = $_SESSION['id'];
	$STH  = $pdo->query("SELECT id,shilings,stickers FROM users WHERE id='$user' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(empty($row->id)) {
		exit(json_encode(['status' => '2']));
	}
	if($row->stickers == 1) {
		$result = ['status' => '2', 'info' => 'У Вас уже куплены стикеры!'];
		exit (json_encode($result));
	}
	if($row->shilings < $price) {
		$result = ['status' => '2', 'info' => 'У Вас недостаточно средств!'];
		exit (json_encode($result));
	}
	$shilings = round_shilings($row->shilings - $price);
	$stickers = 1;

	$STH = $pdo->prepare("UPDATE users SET shilings=:shilings,stickers=:stickers WHERE id='$user' LIMIT 1");
	$STH->execute(['shilings' => $shilings, 'stickers' => 1]);

	$STH = $pdo->prepare(
		"INSERT INTO money__actions (date,shilings,author,type) VALUES (:date, :shilings, :author, :type)"
	);
	$STH->execute(['date' => date("Y-m-d H:i:s"), 'shilings' => -$price, 'author' => $user, 'type' => '5']);
	$_SESSION['stickers'] = 1;
	exit (json_encode(['status' => '1']));
}
if(isset($_POST['activate_voucher'])) {
	$voucher_key = checkJs($_POST['voucher_key'], null);
	if(empty($voucher_key)) {
		exit('<p class="text-danger">Вы не ввели ваучер</p>');
	}
	$STH = $pdo->prepare("SELECT id,val,status FROM vouchers WHERE `key`=:key LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':key' => $voucher_key]);
	$row = $STH->fetch();
	if(empty($row->id)) {
		exit('<p class="text-danger">Ваучера не существует</p>');
	} else {
		if($row->status != 0) {
			exit('<p class="text-danger">Ваучер уже активирован</p>');
		}
	}
	$sum        = $row->val;
	$voucher_id = $row->id;
	$user_id    = $_SESSION['id'];

	$STH = $pdo->query("SELECT id,shilings FROM users WHERE id='$user_id' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(empty($row->id)) {
		exit('<p class="text-danger">Неверный ID пользователя</p>');
	}
	$shilings = round_shilings($row->shilings + $sum);
	$STH      = $pdo->prepare("UPDATE users SET shilings=:shilings WHERE id='$user_id' LIMIT 1");
	$STH->execute(['shilings' => $shilings]);

	$STH = $pdo->prepare("UPDATE vouchers SET status=:status WHERE id='$voucher_id' LIMIT 1");
	$STH->execute(['status' => $user_id]);

	$STH = $pdo->prepare(
		"INSERT INTO money__actions (date,shilings,author,type) VALUES (:date, :shilings, :author, :type)"
	);
	$STH->execute(['date' => date("Y-m-d H:i:s"), 'shilings' => $sum, 'author' => $user_id, 'type' => '8']);

	incNotifications();
	$noty = successful_activation_voucher_for_admin($user_id, $_SESSION['login'], $sum, $voucher_key);
	send_noty($pdo, $noty, 0, 2);

	$noty = successful_activation_voucher($sum);
	send_noty($pdo, $noty, $_SESSION['id'], 2);

	write_log("Активирован ваучер на сумму " . $sum . $messages['RUB']);
	exit ('<div class="bs-callout bs-callout-success mt-10"><h4>Ваучер успешно активирован</h4>' . $noty . '</div><script>$("#balance").empty();$("#balance").append("' . $shilings . '");$("#my_balance").empty();$("#my_balance").append("' . $shilings . '");$("#voucher_key").val("");</script>');
}
if(isset($_POST['buy_unmute'])) {
	$server = checkJs($_POST['server'], "int");
	$id     = checkJs($_POST['id'], "int");

	if(empty($server) or $server == null or empty($id) or $id == null) {
		exit(json_encode(['status' => '2']));
	}

	$STH = $pdo->query("SELECT * FROM config__prices");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$prices = $STH->fetch();

	$STH = $pdo->query(
		"SELECT id,db_host,db_user,db_pass,db_db,db_prefix,type,db_code FROM servers WHERE id='$server' and type!=0  LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$serv_info = $STH->fetch();
	if(empty($serv_info->id)) {
		exit(json_encode(['status' => '2']));
	}

	$db_host   = $serv_info->db_host;
	$db_user   = $serv_info->db_user;
	$db_pass   = $serv_info->db_pass;
	$db_db     = $serv_info->db_db;
	$db_prefix = $serv_info->db_prefix;
	$type      = $serv_info->type;
	$db_code   = $serv_info->db_code;

	if($type == '1' || $type == '2' || $type == '3' || $type == '5') {
		if(check_table('comms', $pdo)) {
			$table = 'comms';
		} else {
			exit(json_encode(['status' => '2']));
		}

		$STH = $pdo->query(
			"SELECT $table.bid,$table.expired,$table.authid AS player_id,$table.name AS player_nick,$table.created AS ban_created,$table.length AS ban_length,$table.reason AS ban_reason FROM $table WHERE $table.bid = '$id'"
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$ban = $STH->fetch();

		if(empty($ban->bid)) {
			exit(json_encode(['status' => '2', 'info' => 'Мут не найден']));
		}

		if(($ban->expired < time() and $ban->ban_length != 0) || !empty($ban->modified_by)) {
			exit(json_encode(['status' => '2', 'info' => 'Игрок уже разблокирован!']));
		}
	} else {
		if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
			exit(json_encode(['status' => '2']));
		}
		set_names($pdo2, $db_code);
		$table = set_prefix($db_prefix, 'comms');
		$STH   = $pdo2->query(
			"SELECT $table.bid,$table.RemoveType AS expired,$table.authid AS player_id,$table.name AS player_nick,$table.created AS ban_created,$table.length AS ban_length,$table.reason AS ban_reason FROM $table WHERE $table.bid = '$id'"
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$ban = $STH->fetch();

		if(empty($ban->bid)) {
			exit(json_encode(['status' => '2', 'info' => 'Мут не найден']));
		}

		if($ban->expired == 1 or $ban->expired == "E" or $ban->expired == "U") {
			exit(json_encode(['status' => '2', 'info' => 'Игрок уже разблокирован!']));
		}
	}

	$ban_length = $ban->ban_length;
	if($type == '1' || $type == '2' || $type == '3' || $type == '5') {
		$ban_length = $ban_length * 60;
	}
	$ban_created = $ban->ban_created;
	$temp_time   = date("Y-m-d H:i:s", ($ban_created + $ban_length));

	if($ban_length == 0) {
		$price = $prices->price2_3;
	} else {
		$now  = time();
		$time = expand_date($temp_time, 1);
		if(($ban_created + $ban_length) < $now) {
			exit(json_encode(['status' => '2', 'info' => 'Игрок уже разблокирован!']));
		} else {
			$date = diff_date($temp_time, date("Y-m-d H:i:s"));
			if($date['2'] < '7' and $date['1'] == '0' and $date['0'] == '0') {
				$price = $prices->price2_1;
			} else {
				$price = $prices->price2_2;
			}
		}
	}

	$user = $_SESSION['id'];
	$STH  = $pdo->query("SELECT id,shilings FROM users WHERE id='$user' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(empty($row->id)) {
		exit(json_encode(['status' => '2']));
	}
	if($row->shilings < $price) {
		exit (json_encode(['status' => '2', 'info' => 'У Вас недостаточно средств!']));
	}
	$shilings = round_shilings($row->shilings - $price);

	if($type == '1' || $type == '2' || $type == '3' || $type == '5') {
		$STH = $pdo->prepare("UPDATE $table SET expired=:expired, modified_by=:modified_by WHERE `bid`=:id LIMIT 1");
		$STH->execute([':expired' => '-2', ':modified_by' => $_SESSION['id'], ':id' => $id]);
	} else {
		$STH = $pdo2->prepare(
			"UPDATE $table SET RemovedBy=:RemovedBy, RemoveType=:RemoveType, `unban_type`=:unban_type, `ban_closed`=:ban_closed WHERE `bid`=:id LIMIT 1"
		);
		$STH->execute(
			[
				':RemovedBy'  => '0',
				':RemoveType' => 'U',
				':unban_type' => '-2',
				':ban_closed' => $_SESSION['id'],
				':id'         => $id
			]
		);
	}

	$ban_nick    = check($ban->player_nick, null);
	$ban_steamid = $ban->player_id;
	$ban_id      = $ban->bid;

	$STH = $pdo->prepare("UPDATE users SET shilings=:shilings WHERE id='$user' LIMIT 1");
	$STH->execute(['shilings' => $shilings]);

	$date = date("Y-m-d H:i:s");
	$STH  = $pdo->prepare(
		"INSERT INTO money__actions (date,shilings,author,type) VALUES (:date, :shilings, :author, :type)"
	);
	$STH->execute(['date' => $date, 'shilings' => -$price, 'author' => $user, 'type' => '9']);

	incNotifications();
	$noty = success_buy_unmute_noty($ban_nick, $ban_steamid, $ban_id);
	send_noty($pdo, $noty, $_SESSION['id'], 2);

	$noty = success_buy_unmute_noty_for_admin($_SESSION['id'], $_SESSION['login'], $ban_nick, $ban_steamid, $ban_id);
	send_noty($pdo, $noty, 0, 2);

	exit (json_encode(['status' => '1']));
}
if(isset($_POST['get_user_srotes'])) {
	$tpl      = new Template;
	$tpl->dir = '../templates/' . $conf->template . '/tpl/';

	$j                     = 0;
	$tpl->result['admins'] = '';

	$STH = $pdo->prepare(
		"SELECT admins.id, admins.name, admins.pause, admins.cause, admins.price, admins.link, admins.active, servers.name AS server_name FROM admins
						  LEFT JOIN servers ON servers.id = admins.server
						  WHERE admins.user_id=:user_id ORDER BY admins.server"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':user_id' => $_SESSION['id']]);
	while($row = $STH->fetch()) {
		$j++;
		$tpl->load_template('elements/store.tpl');
		$tpl->set("{id}", $row->id);
		$tpl->set("{server}", $row->server_name);
		$tpl->set("{active}", $row->active);
		$tpl->set("{pause}", $row->pause);
		$tpl->set("{cause}", $row->cause);
		$tpl->set("{price}", $row->price);
		$tpl->set("{link}", $row->link);
		$tpl->set("{name}", $row->name);
		$tpl->set("{i}", $j);
		$tpl->compile('stores');
		$tpl->clear();
	}
	if($j == 0) {
		$tpl->result['stores'] = '<tr><td colspan="10">Привилегий нет</td></tr>';
	}

	$tpl->show($tpl->result['stores']);
	$tpl->global_clear();
	exit();
}
if(isset($_POST['get_stores_info'])) {
	$id = check($_POST['id'], "int");
	if(empty($id)) {
		exit ();
	}

	$STH = $pdo->prepare(
		"SELECT admins.*, servers.type AS server_type, servers.binds FROM admins
						  LEFT JOIN servers ON servers.id = admins.server
						  WHERE admins.id=:id"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $id]);
	$admin = $STH->fetch();

	if($admin->user_id != $_SESSION['id']) {
		exit ();
	}

	if(empty($admin->pass) and empty($admin->pass_md5)) {
		$admin->pass = '';
	} elseif(empty($admin->pass_md5)) {
		$admin->pass = $admin->pass;
	} elseif(empty($admin->pass)) {
		$admin->pass = '';
	}

	if($admin->active == 2) {
		$class    = "danger";
		$disabled = "disabled";
	} elseif($admin->pause != 0) {
		$class    = "warning";
		$disabled = "disabled";
	} else {
		$class    = "";
		$disabled = "";
	}

	$tpl      = new Template;
	$tpl->dir = '../templates/' . $conf->template . '/tpl/';

	$STH = $pdo->query("SELECT return_services FROM config__secondary LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$conf2 = $STH->fetch();

	$i   = 0;
	$STH = $pdo->prepare(
		"SELECT admins__services.id, admins__services.rights_und, admins__services.sb_group_und, 
		services.name, admins__services.service, admins__services.bought_date, admins__services.ending_date, 
		services.rights, services.sb_group, services.discount AS service_discount, admins__services.irretrievable, servers.discount 
		FROM admins__services 
		LEFT JOIN services ON admins__services.service = services.id 
		LEFT JOIN servers ON services.server=servers.id 
		WHERE admins__services.admin_id = :admin_id"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':admin_id' => $admin->id]);
	while($row = $STH->fetch()) {
		$tpl->load_template('elements/store_service.tpl');

		$i++;
		if(!empty($row->service)) {
			$rights = $row->rights;
			if($admin->server_type == 4) {
				$sb_group = $row->sb_group;
			}
		} else {
			$row->name = 'Неизвестно';
			$rights    = $row->rights_und;
			if($admin->server_type == 4) {
				$sb_group = $row->sb_group_und;
			}
		}
		if(!empty($sb_group) and !empty($rights)) {
			$rights = $rights . '+' . $sb_group;
		} else {
			if(!empty($rights)) {
				$rights = $rights;
			}
			if(!empty($sb_group)) {
				$rights = $sb_group;
			}
		}
		$disp = "";
		if($row->ending_date == '0000-00-00 00:00:00') {
			$left             = "Вечность";
			$color            = "success";
			$disp             = "disp-n";
			$row->ending_date = 'Никогда';
		} else {
			$left = strtotime($row->ending_date) - time();
			if($left > 60 * 60 * 24 * 5) {
				$color = "success";
			} elseif($left > 60 * 60 * 24) {
				$color = "warning";
			} else {
				$color = "danger";
			}
			$return = floor($left / 3600 / 24) - 1;
			if($return < 1) {
				$row->irretrievable = 0;
			} else {
				$row->irretrievable = round($return * $row->irretrievable, 2);
			}
			$left             = expand_seconds2($left, 2);
			$row->ending_date = date('d.m.Y H:i', strtotime($row->ending_date));
			$row->ending_date = expand_date($row->ending_date, 1);
		}
		if($row->bought_date != '0000-00-00 00:00:00') {
			$bought_date_original = $row->bought_date;
			$row->bought_date     = expand_date($row->bought_date, 1);
		} else {
			$row->bought_date = 'Неизвестно';
		}
		$services = "";
		if($admin->active != 2 and $disabled != "disabled" and $disabled == "" and $disp == "") {
			$STH2 = $pdo->query("SELECT discount FROM config__prices LIMIT 1");
			$STH2->setFetchMode(PDO::FETCH_OBJ);
			$disc     = $STH2->fetch();
			$discount = $disc->discount;
			$services .= '<select class="form-control input-sm pd-0" id="extend_time' . $row->id . '">';
			$STH2     = $pdo->query(
				"SELECT 
							    id,
		                        CASE
							        WHEN price_renewal = 0 THEN price
							        ELSE price_renewal
							    END AS price,
							    time,
							    discount 
							FROM 
							    services__tarifs 
							WHERE service = '$row->service' 
							ORDER BY price"
			);
			$STH2->setFetchMode(PDO::FETCH_OBJ);
			while($service = $STH2->fetch()) {
				if($service->time == 0) {
					$time = 'Навсегда';
				} else {
					$time = $service->time . ' дня(ей)';
				}

				$proc  = calculate_discount(
					$row->discount,
					$discount,
					$user->proc,
					$row->service_discount,
					$service->discount
				);
				$price = calculate_price($service->price, $proc);
				if($price != $service->price) {
					$services .= '<option value="' . $service->id . '">' . $time . ' - ' . $price . ' ' . $messages['RUB'] . ' (с учетом скидки в ' . $proc . '%)</option>';
				} else {
					$services .= '<option value="' . $service->id . '">' . $time . ' - ' . $price . ' ' . $messages['RUB'] . '</option>';
				}
			}
			$services .= '</select>';
		}

		if($conf2->return_services == 2) {
			$row->irretrievable = 0;
		}
		if(isset($config_additional['store_return_time'])) { // в течение какого времени можно сделать возврат
			if((time() - strtotime($bought_date_original)) > 60 * 60 * $config_additional['store_return_time']) {
				$row->irretrievable = 0;
			}
		}
		$tpl->set("{active}", $admin->active);
		$tpl->set("{pause}", $admin->pause);
		$tpl->set("{disabled}", $disabled);
		$tpl->set("{i}", $i);
		$tpl->set("{name}", $row->name);
		$tpl->set("{id}", $row->id);
		$tpl->set("{id2}", $admin->id);
		$tpl->set("{left}", $left);
		$tpl->set("{disp}", $disp);
		$tpl->set("{color}", $color);
		$tpl->set("{bought_date}", $row->bought_date);
		$tpl->set("{ending_date}", $row->ending_date);
		$tpl->set("{rights}", $rights);
		$tpl->set("{services}", $services);
		$tpl->set("{sum}", $row->irretrievable);
		$tpl->compile('servies');
		$tpl->clear();
	}

	$peg_1 = '2';
	$peg_2 = '2';
	$peg_3 = '2';
	$binds = explode(';', $admin->binds);
	if($binds[0]) {
		$peg_1 = '1';
	}
	if($binds[1]) {
		$peg_2 = '1';
	}
	if($binds[2]) {
		$peg_3 = '1';
	}

	$tpl->load_template('elements/store_info.tpl');
	$tpl->set("{active}", $admin->active);
	$tpl->set("{pause}", $admin->pause);
	$tpl->set("{id}", $admin->id);
	$tpl->set("{price}", $admin->price);
	$tpl->set("{type}", $admin->type);
	$tpl->set("{name}", $admin->name);
	$tpl->set("{pass}", $admin->pass);
	$tpl->set("{class}", $class);
	$tpl->set("{peg_1}", $peg_1);
	$tpl->set("{peg_2}", $peg_2);
	$tpl->set("{peg_3}", $peg_3);
	$tpl->set("{disabled}", $disabled);
	$tpl->set("{services}", $tpl->result['servies']);
	$tpl->compile('content');
	$tpl->clear();

	$tpl->show($tpl->result['content']);
	$tpl->global_clear();
	exit();
}
if(isset($_POST['edit_store'])) {
	$id    = checkJs($_POST['id'], "int");
	$type  = checkJs($_POST['type'], null);
	$param = checkJs($_POST['param'], null);

	if(empty($id) or empty($type) or empty($param)) {
		exit (json_encode(['status' => '2', 'reply' => 'Заполните все поля!']));
	}

	$STH = $pdo->prepare(
		"SELECT admins.id, admins.active, admins.pause, admins.type, admins.name, admins.pass, admins.pass_md5, servers.type AS server_type, servers.id AS server_id, servers.binds FROM admins 
		LEFT JOIN servers ON servers.id = admins.server
		WHERE admins.id=:id AND admins.user_id=:user_id LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $id, ':user_id' => $_SESSION['id']]);
	$admin = $STH->fetch();
	if(empty($admin->id)) {
		exit (json_encode(['status' => '2', 'reply' => 'Ошибка!']));
	}

	if($admin->active == 2) {
		exit(json_encode(['status' => '2', 'reply' => 'Ваша услуга заблокирована!']));
	}

	if($admin->pause != 0) {
		exit (json_encode(['status' => '2', 'reply' => 'Услуга приостановлена!']));
	}

	$AM = new AdminsManager;
	if(!$AM->checking_server_status($pdo, $admin->server_id)) {
		exit (json_encode(['status' => '2', 'reply' => $messages['server_connect_error']]));
	}

	$SIDO = new SteamIDOperations();

	$old_name = null;
	if($type == 'type') {
		if($conf->col_type == 0) {
			exit (json_encode(['status' => '2', 'reply' => 'Смена типа привязки запрещена']));
		}

		if($param != '1' and $param != '2' and $param != '3') {
			exit (json_encode(['status' => '2', 'reply' => 'Неверно указан тип!']));
		}

		$binds = explode(';', $admin->binds);
		if(($binds[0] == 0 and $param == 1) || ($binds[1] == 0 and $param == 2) || ($binds[2] == 0 and $param == 3)) {
			exit (json_encode(['status' => '2', 'reply' => 'Данный тип запрещен!']));
		}

		$date = time() - 24 * 60 * 60 * $conf->col_type;
		$pdo->exec(
			"DELETE FROM last_actions WHERE date<'$date' and user_id='$_SESSION[id]' and action_type = '4' LIMIT 1"
		);

		$STH = $pdo->query("SELECT id,date FROM last_actions WHERE user_id = '$_SESSION[id]' and action_type = '4'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(!empty($row->id)) {
			$delta = time() - $row->date;
			if($delta < (24 * 60 * 60 * $conf->col_type)) {
				exit (
				json_encode(
					['status' => '2', 'reply' => 'Тип привязки разрешено менять раз в ' . $conf->col_type . ' дн.']
				)
				);
			}
		}

		if($param == 1) {
			if(empty($admin->pass) and empty($admin->pass_md5)) {
				exit (json_encode(['status' => '2', 'reply' => 'Сначала укажите пароль!']));
			}

			$STH = $pdo->prepare("UPDATE admins SET type=:type WHERE id=:id LIMIT 1");
			$STH->execute([':type' => 'a', ':id' => $id]);
		}
		if($param == 2) {
			if(!$admin->name = $SIDO->GetSteamID32($admin->name)) {
				exit (json_encode(['status' => '2', 'reply' => 'Введите корректный STEAM ID!']));
			}

			$STH = $pdo->prepare("UPDATE admins SET type=:type WHERE id=:id LIMIT 1");
			$STH->execute([':type' => 'ce', ':id' => $id]);
		}
		if($param == 3) {
			if(empty($admin->pass) and empty($admin->pass_md5)) {
				exit (json_encode(['status' => '2', 'reply' => 'Сначала укажите пароль!']));
			}

			if(!$admin->name = $SIDO->GetSteamID32($admin->name)) {
				exit (json_encode(['status' => '2', 'reply' => 'Введите корректный STEAM ID!']));
			}

			$STH = $pdo->prepare("UPDATE admins SET type=:type WHERE id=:id LIMIT 1");
			$STH->execute([':type' => 'ca', ':id' => $id]);

			$STH = $pdo->prepare(
				"INSERT INTO last_actions (user_id,action_type,date) values (:user_id, :action_type, :date)"
			);
			$STH->execute(['user_id' => $_SESSION['id'], 'action_type' => '4', 'date' => time()]);
		}
	}

	if($type == 'name') {
		if($conf->col_nick == 0) {
			exit (json_encode(['status' => '2', 'reply' => 'Смена идентификатора запрещена']));
		}
		$date = time() - 24 * 60 * 60 * $conf->col_nick;
		$pdo->exec(
			"DELETE FROM last_actions WHERE date<'$date' and user_id='$_SESSION[id]' and action_type = '2' LIMIT 1"
		);

		$STH = $pdo->query("SELECT id,date FROM last_actions WHERE user_id = '$_SESSION[id]' and action_type = '2'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(!empty($row->id)) {
			$delta = time() - $row->date;
			if($delta < (24 * 60 * 60 * $conf->col_nick)) {
				exit (
				json_encode(
					['status' => '2', 'reply' => 'Идентификатор разрешено менять раз в ' . $conf->col_nick . ' дн.']
				)
				);
			}
		}

		if($admin->type == 'a') {
			if(mb_strlen($_POST['param'], 'UTF-8') > 32) {
				exit (json_encode(['status' => '2', 'reply' => 'Не более 32 символов!']));
			}
		}
		if($admin->type == 'ce' or $admin->type == 'ca') {
			if(mb_strlen($param, 'UTF-8') > 32) {
				exit (json_encode(['status' => '2', 'reply' => 'Не более 32 символов!']));
			}
			if(!$param = $SIDO->GetSteamID32($param)) {
				exit (json_encode(['status' => '2', 'reply' => 'Введите корректный STEAM ID!']));
			}
		}

		if($admin->server_type == 1 || $admin->server_type == 3) {
			if(stristr(htmlspecialchars_decode($param, ENT_QUOTES), '"') !== false) {
				exit (json_encode(['status' => '2', 'reply' => 'Ваш идентификатор содержит запрещенный символ: "']));
			}
			if(stristr(htmlspecialchars_decode($param, ENT_QUOTES), '#') !== false) {
				exit (json_encode(['status' => '2', 'reply' => 'Ваш идентификатор содержит запрещенный символ: #']));
			}
		}

		if(!$AM->check_for_bad_nicks($pdo, $param)) {
			exit (json_encode(['status' => '3', 'data' => 'Использовать данный идентификатор запрещено!']));
		}

		if($admin->type == 'ce' or $admin->type == 'ca') {
			$STH = $pdo->prepare(
				"SELECT id FROM admins WHERE name LIKE :name AND server=:server LIMIT 1"
			);

			$adminNameForCheck = '%' . strip_data(':' . explode(':', $param)[2]);
		} else {
			$STH = $pdo->prepare("SELECT id FROM admins WHERE name=:name AND server=:server LIMIT 1");

			$adminNameForCheck = $param;
		}

		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':name' => $adminNameForCheck, ':server' => $admin->server_id]);
		$row = $STH->fetch();
		if(isset($row->id)) {
			if($row->id == $id) {
				exit (json_encode(['status' => '1']));
			} else {
				exit (json_encode(['status' => '2', 'reply' => 'Идентификатор уже используется другим игроком!']));
			}
		}
		$STH = $pdo->prepare("UPDATE admins SET name=:name WHERE id=:id LIMIT 1");
		$STH->execute([':name' => $param, ':id' => $id]);

		$STH = $pdo->prepare(
			"INSERT INTO last_actions (user_id,action_type,date) VALUES (:user_id, :action_type, :date)"
		);
		$STH->execute(['user_id' => $_SESSION['id'], 'action_type' => '2', 'date' => time()]);

		$old_name = $admin->name;
	}
	if($type == 'pass') {
		if($conf->col_pass == 0) {
			exit (json_encode(['status' => '2', 'reply' => 'Смена пароля запрещена']));
		}

		$date = time() - 24 * 60 * 60 * $conf->col_pass;
		$pdo->exec(
			"DELETE FROM last_actions WHERE date<'$date' and user_id='$_SESSION[id]' and action_type = '1' LIMIT 1"
		);

		$STH = $pdo->query("SELECT id,date FROM last_actions WHERE user_id = '$_SESSION[id]' and action_type = '1'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(!empty($row->id)) {
			$delta = time() - $row->date;
			if($delta < (24 * 60 * 60 * $conf->col_pass)) {
				exit (
					json_encode(
						['status' => '2', 'reply' => 'Пароль разрешено менять раз в ' . $conf->col_pass . ' дн.']
					)
				);
			}
		}

		if(mb_strlen($param, 'UTF-8') > 32) {
			exit (json_encode(['status' => '2', 'reply' => 'Не более 32 символов!']));
		}
		if($admin->server_type == 1 || $admin->server_type == 3) {
			if(stristr(htmlspecialchars_decode($param, ENT_QUOTES), '"') !== false) {
				exit (json_encode(['status' => '2', 'reply' => 'Ваш пароль содержит запрещенный символ: "']));
			}
			if(stristr(htmlspecialchars_decode($param, ENT_QUOTES), '#') !== false) {
				exit (json_encode(['status' => '2', 'reply' => 'Ваш пароль содержит запрещенный символ: #']));
			}
		}

		$STH = $pdo->prepare("UPDATE admins SET pass=:pass, pass_md5=:pass_md5 WHERE id=:id LIMIT 1");
		$STH->execute([':pass' => $param, ':pass_md5' => md5($param), ':id' => $id]);

		$STH = $pdo->prepare(
			"INSERT INTO last_actions (user_id,action_type,date) VALUES (:user_id, :action_type, :date)"
		);
		$STH->execute(['user_id' => $_SESSION['id'], 'action_type' => '1', 'date' => time()]);
	}

	if($admin->server_type == 1 or $admin->server_type == 3) {
		if(!$AM->export_to_users_ini($pdo, $admin->server_id, 'EDIT_STORE')) {
			exit (json_encode(['status' => '2', 'reply' => 'Не удалось экспортировать администраторов в файл']));
		}
	} else {
		if(!$AM->export_admin($pdo, $id, $admin->server_id, 'EDIT_STORE', $old_name)) {
			exit (
			json_encode(
				['status' => '2', 'reply' => 'Не удалось экспортировать администратора в базу данных сервера']
			)
			);
		}
	}

	service_log("Пользователь сменил " . $type . " на " . $param, $id, $admin->server_id, $pdo);
	exit (json_encode(['status' => '1']));
}
if(isset($_POST['start_srote'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit(json_encode(['status' => '2']));
	}

	$STH = $pdo->prepare(
		"SELECT admins.*,servers.db_host, servers.ip, servers.port, servers.db_code, servers.type AS server_type, servers.id AS server_id, servers.name AS server_name, servers.db_user, servers.db_pass, servers.db_db, servers.db_prefix FROM servers 
		LEFT JOIN admins ON admins.server = servers.id 
		WHERE admins.id=:id AND admins.user_id=:user_id LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $id, ':user_id' => $_SESSION['id']]);
	$info = $STH->fetch();
	if(empty($info->id)) {
		exit(json_encode(['status' => '2']));
	}

	if($info->active == 1) {
		exit (json_encode(['status' => '2', 'data' => 'Права уже разблокированы']));
	}

	$STH = $pdo->query("SELECT id,shilings FROM users WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(empty($row->id)) {
		exit(json_encode(['status' => '2']));
	}
	if($row->shilings < $info->price) {
		$price_delta = round($info->price - $row->shilings, 2);
		exit (
			json_encode(
				[
					'status' => '2',
					'data'   => 'У Вас недостаточно средств! Пополните баланс на ' . $price_delta . $messages['RUB']
				]
			)
		);
	}
	$shilings = round_shilings($row->shilings - $info->price);

	if(empty($info->server_type)) {
		exit (json_encode(['status' => '2', 'data' => 'Невозможно подключение к FTP и DB серверу']));
	}

	$AM = new AdminsManager;
	if(!$AM->checking_server_status($pdo, $info->server_id)) {
		exit (json_encode(['status' => '2', 'data' => $messages['server_connect_error']]));
	}

	$STH = $pdo->prepare(
		"UPDATE admins SET active=:active,cause=:cause,link=:link,price=:price WHERE id='$id' LIMIT 1"
	);
	$STH->execute(['active' => '1', 'cause' => '', 'link' => '', 'price' => 0]);

	$STH = $pdo->prepare("UPDATE users SET shilings=:shilings WHERE id=:id LIMIT 1");
	$STH->execute([':shilings' => $shilings, ':id' => $_SESSION['id']]);

	$STH = $pdo->prepare(
		"INSERT INTO money__actions (date,shilings,author,type) values (:date, :shilings, :author, :type)"
	);
	$STH->execute(
		['date' => date("Y-m-d H:i:s"), 'shilings' => -$info->price, 'author' => $_SESSION['id'], 'type' => '7']
	);

	if($info->server_type == 1 || $info->server_type == 3) {
		if(!$AM->export_to_users_ini($pdo, $info->server_id, 'START_STORE')) {
			exit (json_encode(['status' => '2', 'data' => 'Не удалось экспортировать администраторов в файл']));
		}
	}
	if($info->server_type == 2 || $info->server_type == 4) {
		if(!$pdo2 = db_connect($info->db_host, $info->db_db, $info->db_user, $info->db_pass)) {
			exit (json_encode(['status' => '2', 'data' => 'Не удалось подключиться к DB серверу']));
		}
		set_names($pdo2, $info->db_code);

		$info->name = htmlspecialchars_decode($info->name, ENT_QUOTES);
		if(!empty($info->pass)) {
			$info->pass = htmlspecialchars_decode($info->pass, ENT_QUOTES);
		}

		if($info->server_type == 2) {
			if(
				!$admin_id = $AM->get_admin_id2(
					$info->id,
					$info->name,
					$info->pass,
					$info->pass_md5,
					$info->server_id,
					1,
					$pdo,
					$pdo2,
					$info->db_prefix
				)
			) {
				exit (json_encode(['status' => '2', 'data' => 'Не найден ID админа']));
			}

			$table = set_prefix($info->db_prefix, "serverinfo");
			$STH   = $pdo2->prepare("SELECT `id` FROM `$table` WHERE `address`=:address LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute([':address' => $info->ip . ':' . $info->port]);
			$row = $STH->fetch();

			$table = set_prefix($info->db_prefix, "admins_servers");
			$STH   = $pdo2->prepare(
				"INSERT INTO $table (admin_id,server_id,use_static_bantime,custom_flags) values (:admin_id, :server_id, :use_static_bantime, :custom_flags)"
			);
			$STH->execute(
				['admin_id' => $admin_id, 'server_id' => $row->id, 'use_static_bantime' => 'no', 'custom_flags' => '']
			);
		} else {
			if(
				!$admin_id = $AM->get_admin_id2(
					$info->id,
					$info->name,
					$info->pass,
					$info->pass_md5,
					$info->server_id,
					2,
					$pdo,
					$pdo2,
					$info->db_prefix
				)
			) {
				exit (json_encode(['status' => '2', 'data' => 'Не найден ID админа']));
			}

			$table = set_prefix($info->db_prefix, "servers");
			$STH   = $pdo2->prepare("SELECT `sid` FROM `$table` WHERE `ip`=:ip AND `port`=:port LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute([':ip' => $info->ip, ':port' => $info->port]);
			$row = $STH->fetch();

			$table = set_prefix($info->db_prefix, "admins_servers_groups");
			$STH   = $pdo2->prepare(
				"INSERT INTO $table (admin_id,server_id,group_id,srv_group_id) values (:admin_id, :server_id, :group_id, :srv_group_id)"
			);
			$STH->execute(
				['admin_id' => $admin_id, 'server_id' => $row->sid, 'group_id' => '0', 'srv_group_id' => '-1']
			);
		}
	}

	try {
		(new OurSourceQuery())->reloadAdmins($info->server_id);
	} catch(Exception $e) {
		log_error($e->getMessage());
	}

	incNotifications();
	$noty = unlock_service_noty(clean($info->name, null), $info->server_name);
	send_noty($pdo, $noty, $info->user_id, 2);

	$noty = unlock_service_noty_for_admin(
		$_SESSION['id'],
		$_SESSION['login'],
		clean($info->name, null),
		$info->server_name
	);
	send_noty($pdo, $noty, 0, 2);

	service_log("Покупка разблокировки прав", $id, $info->server_id, $pdo);
	exit (json_encode(['status' => '1']));
}
if(isset($_POST['get_return'])) {
	$STH = $pdo->query("SELECT return_services FROM config__secondary LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if($row->return_services == 2) {
		exit(json_encode(['status' => '2']));
	}

	ignore_user_abort(1);
	set_time_limit(0);

	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit(json_encode(['status' => '2']));
	}

	$STH = $pdo->prepare(
		"SELECT 
				    services.users_group, 
				    services.id AS service_id, 
				    admins.user_id,
				    admins.active, 
				    admins.pause,
				    admins__services.admin_id, 
				    admins__services.irretrievable, 
				    admins__services.ending_date, 
				    admins__services.bought_date,
				    servers.type AS server_type, 
				    admins.server 
				FROM admins__services 
					LEFT JOIN admins ON admins__services.admin_id = admins.id 
					LEFT JOIN servers ON servers.id = admins.server
					LEFT JOIN services ON services.id = admins__services.service 
					WHERE admins__services.id=:id AND admins.user_id=:user_id LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $id, ':user_id' => $_SESSION['id']]);
	$admin = $STH->fetch();
	if(empty($admin->admin_id)) {
		exit(json_encode(['status' => '2']));
	}

	if($admin->active == 2) {
		exit(json_encode(['status' => '2', 'data' => 'Ваша услуга заблокирована!']));
	}

	if($admin->pause != 0) {
		exit (json_encode(['status' => '2', 'data' => 'Услуга приостановлена!']));
	}

	if($admin->irretrievable == 0) {
		exit(json_encode(['status' => '2']));
	}
	if(isset($config_additional['store_return_time'])) {
		if(time() - strtotime($admin->bought_date) > 24 * 60 * 60 * $config_additional['store_return_time']) {
			exit(json_encode(['status' => '2']));
		}
	}

	$AM = new AdminsManager;
	if(!$AM->checking_server_status($pdo, $admin->server)) {
		exit (json_encode(['status' => '2', 'data' => $messages['server_connect_error']]));
	}

	$left   = strtotime($admin->ending_date) - time();
	$return = floor($left / 3600 / 24) - 1;
	if($return < 1) {
		exit(json_encode(['status' => '2']));
	} else {
		$admin->irretrievable = round($return * $admin->irretrievable, 2);
	}

	$AM->set_admin_group($pdo, $admin->user_id, 0, $id);

	service_log(
		"Пользователь выполнил возврат на сумму: " . $admin->irretrievable . "р",
		$admin->admin_id,
		$admin->server,
		$pdo,
		$admin->service_id
	);

	$STH = $pdo->prepare("SELECT id FROM admins__services WHERE admin_id=:id ");
	$STH->execute([':id' => $admin->admin_id]);
	$row   = $STH->fetchAll();
	$count = count($row);
	if($count == 1) {
		if(!$AM->dell_admin_full($pdo, $admin->admin_id, "GET_RETURN")) {
			exit(json_encode(['status' => '2']));
		}
		$id = $admin->admin_id;
	} else {
		$STH = $pdo->prepare("DELETE FROM admins__services WHERE id=:id LIMIT 1");
		$STH->execute([':id' => $id]);

		if($admin->server_type == 1 || $admin->server_type == 3) {
			if(!$AM->export_to_users_ini($pdo, $admin->server, 'GET_RETURN')) {
				exit (json_encode(['status' => '2']));
			}
		} else {
			if(!$AM->export_admin($pdo, $admin->admin_id, $admin->server, 'GET_RETURN')) {
				exit (json_encode(['status' => '2']));
			}
		}

		$id = 0;
	}

	$STH = $pdo->query("SELECT id,shilings FROM users WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(empty($row->id)) {
		exit(json_encode(['status' => '2']));
	}
	$shilings = round_shilings($row->shilings + $admin->irretrievable);

	$STH = $pdo->prepare("UPDATE users SET shilings=:shilings WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->execute(['shilings' => $shilings]);

	$STH = $pdo->prepare(
		"INSERT INTO money__actions (date,shilings,author,type) VALUES (:date, :shilings, :author, :type)"
	);
	$STH->execute(
		[
			'date'     => date("Y-m-d H:i:s"),
			'shilings' => $admin->irretrievable,
			'author'   => $_SESSION['id'],
			'type'     => '10'
		]
	);

	exit(json_encode(['status' => '1', 'id' => $id, 'shilings' => $shilings]));
}
if(isset($_POST['buy_extend'])) {
	ignore_user_abort(1);
	set_time_limit(0);

	$id   = checkJs($_POST['id'], "int");
	$time = checkJs($_POST['time'], null);

	if(empty($id) or empty($time)) {
		exit (json_encode(['status' => '2']));
	}

	$STH = $pdo->prepare(
		"SELECT 
				    services.name AS service_name, 
    				services.id AS service_id, 
				    admins__services.service, 
				    admins__services.ending_date, 
				    admins.id, 
				    admins.active, 
				    admins.pause, 
				    admins.name, 
				    admins__services.irretrievable, 
				    admins.user_id, 
				    servers.type AS server_type, 
				    servers.id AS server_id, 
				    servers.name AS server_name, 
				    servers.discount, 
				    services.discount AS service_discount 
				FROM admins__services 
					LEFT JOIN admins ON admins__services.admin_id = admins.id 
					LEFT JOIN servers ON admins.server = servers.id
					LEFT JOIN services ON services.id = admins__services.service
					WHERE admins__services.id=:id AND admins.user_id=:user_id LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $id, ':user_id' => $_SESSION['id']]);
	$admin = $STH->fetch();
	if(empty($admin->id)) {
		exit (json_encode(['status' => '2']));
	}

	if($admin->active == 2) {
		exit(json_encode(['status' => '2', 'data' => 'Ваша услуга заблокирована!']));
	}

	if($admin->pause != 0) {
		exit (json_encode(['status' => '2', 'data' => 'Услуга приостановлена!']));
	}

	$AM = new AdminsManager;
	if(!$AM->checking_server_status($pdo, $admin->server_id)) {
		exit (json_encode(['status' => '2', 'data' => $messages['server_connect_error']]));
	}

	$STH = $pdo->query(
		"SELECT 
					    id,
					    time,
					    CASE
							WHEN price_renewal = 0 THEN price
							ELSE price_renewal
						END AS price,
					    discount 
					FROM 
					    services__tarifs 
					WHERE id='$time' and service='$admin->service'"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tarif = $STH->fetch();
	if(empty($tarif->id)) {
		exit(json_encode(['status' => '2']));
	} else {
		$STH = $pdo->query("SELECT discount FROM config__prices LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$disc = $STH->fetch();

		$proc         = calculate_discount(
			$admin->discount,
			$disc->discount,
			$user->proc,
			$admin->service_discount,
			$tarif->discount
		);
		$tarif->price = calculate_price($tarif->price, $proc);
	}

	$STH = $pdo->query("SELECT id,shilings FROM users WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(empty($row->id)) {
		exit(json_encode(['status' => '2']));
	}

	if($row->shilings < $tarif->price) {
		$price_delta = round($tarif->price - $row->shilings, 2);
		exit (
		json_encode(
			[
				'status' => '2',
				'data'   => 'У Вас недостаточно средств! Пополните баланс на ' . $price_delta . $messages['RUB']
			]
		)
		);
	}
	$shilings = round_shilings($row->shilings - $tarif->price);

	if($tarif->time == 0) {
		$date          = '0000-00-00 00:00:00';
		$irretrievable = 0;
	} else {
		$date = strtotime($admin->ending_date) + $tarif->time * 24 * 3600;
		$date = date('Y-m-d H:i:s', $date);

		$old_left       = floor((strtotime($admin->ending_date) - time()) / 3600 / 24);
		$old_full_price = $old_left * $admin->irretrievable;
		$irretrievable  = calculate_return($tarif->price + $old_full_price, $tarif->time + $old_left);
	}

	if(isset($one_day_extension)) {
		if(strtotime($admin->ending_date) - time() > 24 * 60 * 60 * $one_day_extension) {
			exit (
				json_encode(
					['status' => '2', 'data' => 'Услугу можно продлить за ' . $one_day_extension . ' день до ее окончания.']
				)
			);
		}
	}

	$STH = $pdo->prepare(
		"UPDATE admins__services SET ending_date=:ending_date, service_time=:service_time, irretrievable=:irretrievable WHERE id=:id LIMIT 1"
	);
	$STH->execute(
		[':ending_date' => $date, ':service_time' => $time, ':irretrievable' => $irretrievable, ':id' => $id]
	);

	if($admin->server_type == 1 || $admin->server_type == 3) {
		if(!$AM->export_to_users_ini($pdo, $admin->server_id, 'BUY_EXTEND')) {
			exit (json_encode(['status' => '2']));
		}
	} else {
		if(!$AM->export_admin($pdo, $admin->id, $admin->server_id, 'BUY_EXTEND')) {
			exit (json_encode(['status' => '2']));
		}
	}

	incNotifications();
	if($date == '0000-00-00 00:00:00') {
		$date = 'Навсегда';
	} else {
		$date = expand_date($date, 1);
	}
	$noty = buy_extend_noty($admin->name, $admin->service_name, $admin->server_name, $date);
	send_noty($pdo, $noty['message'], $_SESSION['id'], $noty['type']);

	$STH = $pdo->prepare("UPDATE users SET shilings=:shilings WHERE id='$_SESSION[id]' LIMIT 1");
	$STH->execute(['shilings' => $shilings]);

	$STH = $pdo->prepare(
		"INSERT INTO money__actions (date,shilings,author,type) values (:date, :shilings, :author, :type)"
	);
	$STH->execute(
		['date' => date("Y-m-d H:i:s"), 'shilings' => -$tarif->price, 'author' => $_SESSION['id'], 'type' => '6']
	);

	service_log("Пользователь продлил права до: " . $date, $admin->id, $admin->server_id, $pdo, $admin->service_id);

	exit (json_encode(['status' => '1', 'shilings' => $shilings]));
}