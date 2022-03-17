<?php

class Payments
{
	private static $cashiers = [
		[
			'slug' => 'ik',
			'name' => 'InterKassa'
		],
		[
			'slug' => 'payeer',
			'name' => 'Payeer'
		],
		[
			'slug' => 'perfectmoney',
			'name' => 'PerfectMoney'
		],
		[
			'slug' => 'ya',
			'name' => 'ЮMoney'
		],
		[
			'slug' => 'fk',
			'name' => 'Free-Kassa'
		],
		[
			'slug' => 'fk_new',
			'name' => 'FreeKassa'
		],
		[
			'slug' => 'rb',
			'name' => 'RoboKassa'
		],
		[
			'slug' => 'wb',
			'name' => 'WebMoney'
		],
		[
			'slug' => 'up',
			'name' => 'UnitPay'
		],
		[
			'slug' => 'ps',
			'name' => 'PaySera'
		],
		[
			'slug' => 'wo',
			'name' => 'WalletOne'
		],
		[
			'slug' => 'qw',
			'name' => 'QIWI'
		],
		[
			'slug' => 'lp',
			'name' => 'LiqPay'
		],
		[
			'slug' => 'ap',
			'name' => 'AnyPay'
		],
		[
			'slug' => 'enot',
			'name' => 'Enot'
		],
		[
			'slug' => 'amarapay',
			'name' => 'AmaraPay'
		],
		[
			'slug' => 'lava',
			'name' => 'Lava'
		]
	];

	public static function selectPayment($payment)
	{
		return '[' . self::$cashiers[self::getCashierKey($payment)]['name'] . '] : ';
	}

	public static function getCashierKey($slug)
	{
		return array_search($slug, array_column(self::$cashiers, 'slug'));
	}

	public static function getCashier($cashierKey)
	{
		return self::$cashiers[$cashierKey];
	}

	public static function getCashierCurrency($slug)
	{
		global $config_additional;

		return (isset($config_additional) && array_key_exists($slug . 'Currency', $config_additional))
			? $config_additional[$slug . 'Currency']
			: 'RUB';
	}

	public static function generatePayId()
	{
		return time() . '0' . rand(1, 9);
	}

	public static function generatePayDescription($siteName)
	{
		return 'Пополнение баланса на ' . $siteName;
	}

	public static function isCashierEnable($pdo, $cashierKey)
	{
		$cashierSlug = self::$cashiers[$cashierKey]['slug'];
		$STH         = $pdo->query("SELECT $cashierSlug FROM config__bank LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();

		if($row->$cashierSlug == 1) {
			return true;
		} else {
			return false;
		}
	}

	public static function showForm($url, $parameters)
	{
		?>
		<form id="pay_form" method="post" action="<?php echo $url; ?>">
			<?php
			foreach($parameters as $name => $value) {
				?>
				<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>"/>
				<?php
			}
			?>
		</form>
		<script>
          document.getElementById('pay_form').submit();
		</script>
		<?php
	}

	public static function showError($message)
	{
		?>
		<script>alert('<?php echo $message; ?>');</script>
		<?php
	}

	public static function showLink($url, $parameters = [])
	{
		if(empty($parameters)) {
			$link = $url;
		} else {
			$link = $url . '?' . http_build_query($parameters);
		}

		?>
		<script>document.location.href = '<?php echo $link; ?>';</script>
		<?php
	}

	function selectPaymentMess($mess)
	{
		switch($mess) {
			case 'bad sign':
				$mess = 'Неверная подпись';
				break;
			case 'unknown user':
				$mess = 'Неизвестный ID пользователя';
				break;
			case 'invalid purse':
				$mess = 'Неверный кошелек';
				break;
			case 'invalid request':
				$mess = 'Неверный запрос';
				break;
			case 'empty data':
				$mess = 'Пустые данные';
				break;
		}
		return $mess;
	}

	function getUser($pdo, $user_id)
	{
		$STH = $pdo->query("SELECT `id`, `shilings`, `invited`, `login` FROM `users` WHERE `id`='$user_id' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		return $STH->fetch();
	}

	public static function doPayAction($pdo, $user, $amount, $bank, $pay_method, $pay_number, $currency)
	{
		$amount = check($amount, "float");
		$bank   = $bank + $amount;
		$STH    = $pdo->prepare("UPDATE `users` SET `shilings`=:shilings WHERE `id`='$user->id' LIMIT 1");
		if($STH->execute(['shilings' => $user->shilings + $amount]) == '1') {
			$STH = $pdo->prepare("UPDATE config SET bank=:bank LIMIT 1");
			$STH->execute(['bank' => $bank]);
			$STH = $pdo->prepare(
				"INSERT INTO money__actions (date,shilings,author,type) VALUES (:date, :shilings, :author, :type)"
			);
			$STH->execute(['date' => date("Y-m-d H:i:s"), 'shilings' => $amount, 'author' => $user->id, 'type' => '1']);
			self::paymentLog($pay_method, $amount, $pdo, $user->id, 1);

			$STH = $pdo->query("SELECT bonuses FROM config__secondary LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$bonuses = $STH->fetch();
			if($bonuses->bonuses == 1) {
				$STH = $pdo->prepare("SELECT data FROM config__strings WHERE id=:id LIMIT 1");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute([':id' => '3']);
				$bonuses = $STH->fetch();

				if(!empty($bonuses->data)) {
					$bonuses = unserialize($bonuses->data);

					for($i = 0; $i < count($bonuses); $i++) {
						if(!empty($bonuses[$i]['value'])) {
							if($bonuses[$i]['start'] <= $amount && $amount <= $bonuses[$i]['end']) {
								if($bonuses[$i]['type'] == 1) {
									$bonus = $bonuses[$i]['value'];
								} else {
									$bonus = round($bonuses[$i]['value'] * $amount / 100, 2);
								}

								$STH = $pdo->prepare(
									"UPDATE `users` SET `shilings`=:shilings WHERE `id`='$user->id' LIMIT 1"
								);
								$STH->execute(['shilings' => $user->shilings + $amount + $bonus]);

								$STH = $pdo->prepare(
									"INSERT INTO money__actions (date,shilings,author,type) VALUES (:date, :shilings, :author, :type)"
								);
								$STH->execute(
									[
										'date'     => date("Y-m-d H:i:s"),
										'shilings' => $bonus,
										'author'   => $user->id,
										'type'     => '12'
									]
								);
							}
						}
					}
				}
			}

			if(!empty($user->invited)) {
				$STH = $pdo->query("SELECT referral_program, referral_percent FROM config__prices LIMIT 1");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$ref = $STH->fetch();
				if($ref->referral_program == 1) {
					$STH = $pdo->prepare("SELECT id, shilings FROM users WHERE id=:id LIMIT 1");
					$STH->setFetchMode(PDO::FETCH_OBJ);
					$STH->execute([':id' => $user->invited]);
					$inviter = $STH->fetch();
					$amount  = round($amount - calculate_price($amount, $ref->referral_percent), 2);
					$STH     = $pdo->prepare(
						"INSERT INTO money__actions (date,shilings,author,type,gave_out) VALUES (:date, :shilings, :author, :type, :gave_out)"
					);
					$STH->execute(
						[
							'date'     => date("Y-m-d H:i:s"),
							'shilings' => $amount,
							'author'   => $inviter->id,
							'type'     => '11',
							'gave_out' => $user->id
						]
					);

					$STH = $pdo->prepare("UPDATE `users` SET `shilings`=:shilings WHERE `id`='$inviter->id' LIMIT 1");
					$STH->execute(['shilings' => $inviter->shilings + $amount]);

					incNotifications();

					$noty = referal_money($amount . $currency, $user->id, $user->login);
					send_noty($pdo, $noty, $inviter->id, 2);
				}
			}
		}
		self::insertPay($pay_method, $pay_number, $pdo);
	}
	
	public static function issetPay($pdo = null, $pay_method, $pay_number) {
		return pdo()
		->query("SELECT `id` FROM `pays` WHERE `method`='$pay_method' and `payid`='$pay_number' LIMIT 1")
		->rowCount();
	}

	public static function insertPay($pay_method, $pay_number, $pdo)
	{
		$STH = $pdo->prepare("INSERT INTO pays (method,payid,date) VALUES (:method, :payid, :date)");
		$STH->execute([':method' => $pay_method, ':payid' => $pay_number, ':date' => date("Y-m-d H:i:s")]);
	}

	public static function paymentLog($payment, $log, $pdo, $user, $type)
	{
		global $messages;

		$payment = self::selectPayment($payment);

		if($type == 1) {
			$log  = "Пополнение счета на " . $log . $messages['RUB'];
			$file = get_log_file_name("payment_successes");
		} else {
			$log  = self::selectPaymentMess($log);
			$file = get_log_file_name("payment_errors");
		}

		if(file_exists($_SERVER['DOCUMENT_ROOT'] . "/logs/" . $file)) {
			$i = "a";
		} else {
			$i = "w";
		}

		if(empty($user) or $user == 0) {
			$user = 'Неизвестный';
		} else {
			$STH = $pdo->prepare("SELECT id, login FROM users WHERE id=:val LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute([':val' => $user]);
			$row = $STH->fetch();
			if(isset($row->id)) {
				$user = $row->login . ' - ' . $row->id;
			} else {
				$user = 'Неизвестный';
			}
		}

		$error_file = fopen($_SERVER['DOCUMENT_ROOT'] . "/logs/" . $file, $i);
		fwrite(
			$error_file,
			$payment . "[" . date("Y-m-d H:i:s")
			. " | " . $_SERVER["REMOTE_ADDR"]
			. " | " . $user . "] : [" . $log . "] \r\n"
		);
		fclose($error_file);
	}
}