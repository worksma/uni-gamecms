<?PHP
	class Lava extends Payments {
		const PING = "https://api.lava.ru/test/ping";										/* Проверяем правильность Токена */
		const INVOICE_CREATE = "https://api.lava.ru/invoice/create";						/* Создаём счёт пополнения */
		const INVOICE_INFO = "https://api.lava.ru/invoice/info";							/* Для проверки счёта */
		
		public static function conf() {
			$row = pdo()->query("SELECT * FROM `config__bank` LIMIT 1")->fetch(PDO::FETCH_OBJ);
			
			return (object)[
				'enable' => ($row->lava == 1 ? true : false),
				'token' => $row->lava_token,
				'wallet' => $row->lava_wallet,
				'domain' => "https://" . $_SERVER['SERVER_NAME'] . "/"
			];
		}
		
		public static function is_token($token = null) {
			if(empty($token)):
				$token = self::conf()->token;
			endif;
		
			$result = self::curl(self::PING, [
				"Authorization: $token"
			]);
			
			if($result->status == '1'):
				return true;
			endif;
			
			return $result->message;
		}
		
		public static function is_valid($id) {
			if(empty($id)):
				return false;
			endif;
			
			$result = self::curl(self::INVOICE_INFO, ['Authorization: ' . self::conf()->token], ['id' => $id]);
			
			if($result->status == 'success'):
				return true;
			endif;
			
			return false;
		}
		
		public static function g2p($count = 10.00) {
			if(!self::conf()->enable):
				exit("[Платёжная система отключена]");
			endif;
		
			$result = self::curl(self::INVOICE_CREATE, [
				"Authorization: " . self::conf()->token
			], [
				'wallet_to'		=> self::conf()->wallet,							/* Номер кошелька, на который приходит пополнение */
				'sum'			=> $count,											/* Сумма пополнения */
				'order_id'		=> time() * 1000,									/* Индекс операции */
				'hook_url'		=> self::conf()->domain . "purse?lava=get",			/* Уведомление о приходе пополнения */
				'success_url'	=> self::conf()->domain . "purse?result=success",	/* В случае успешного пополнения */
				'fail_url'		=> self::conf()->domain . "purse?result=fail",		/* В случае ошибки или отказа */
				'expire'		=> 300,												/* Время жизни операции */
				'subtract'		=> 1,												/* Кто платит комиссию (1 - клиент, 0 - магазин) */
				'custom_fields' => $_SESSION['id'],									/* Индекс пользователя */
				'comment'		=> 'Пополнение профиля: ' . user()->login,			/* Комментарий к пополнению */
				'merchant_name' => configs()->name									/* Наименование магазина */
			]);
			
			if($result->status == 'success'):
				return self::redirect($result->url);
			endif;
			
			return $result->message;
		}
		
		public static function curl($uri, $headers, $data = null) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $uri);
			
			if(isset($data)):
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			endif;
			
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$result = curl_exec($ch);
			curl_close($ch);
			
			return json_decode($result);
		}
		
		public static function redirect($uri = "/", $time = 1) {
			exit("<script>setTimeout('location.href = \"$uri\"', $time)</script>");
		}
	}