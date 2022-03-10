<?PHP
	require($_SERVER['DOCUMENT_ROOT'] . '/inc/start.php');
	
	if(empty($_POST['phpaction']) || $_SESSION['token'] != $_POST['token']) {
		result(['alert' => 'error']);
	}
	
	if(empty($_SESSION['id'])) {
		result(['alert' => 'error', 'message' => 'Доступно только авторизованным']);
	}
	
	if(isset($_POST['buy'])) {
		$pid = clean($_POST['pid'], "int");
		
		if(empty($pid) || !Trading::IsValidProduct($pid)) {
			result(['alert' => 'warning', 'message' => 'Неверный индекс товара.']);
		}
		
		$Product = Trading::GetProduct($pid);
		
		if($Product->availability) {
			if(Trading::GetBalance($_SESSION['id']) >= $Product->price) {
				if(Trading::SetProduct($pid, 'availability', ($Product->availability - 1))) {
					Trading::SetBalance($_SESSION['id'], (Trading::GetBalance($_SESSION['id']) - $Product->price));
					Trading::addPurchases($_SESSION['id'], $pid, $Product->price);
					
					Trading::SendRcon($pid, [
						'uid' => $_SESSION['id'],
						'steamid' => user()->steam_id,
						'pid' => $pid,
						'price' => $Product->price
					]);
					
					Trading::RemoteNoty($pid, [
						'uid' => $_SESSION['id'],
						'pid' => $pid,
						'price' => $Product->price
					]);
					
					result(['alert' => 'info', 'message' => 'Товар добавлен в Инвентарь', 'count' => ($Product->availability - 1)]);
				}
			}
			else {
				result(['alert' => 'warning', 'message' => 'Недостаточно средств.']);
			}
		}
		
		result(['alert' => 'warning', 'message' => 'Товар распродан.']);
	}
	
	if(isset($_POST['on'])) {
		$pid = clean($_POST['pid'], "int");
		
		if(empty($pid)) {
			result(['alert' => 'warning', 'message' => 'Отсутствует индекс']);
		}
		
		if(!Trading::IsUserPurchases($_SESSION['id'], $pid)) {
			result(['alert' => 'warning', 'message' => 'Нет предмета в Инвентаре']);
		}
		
		Trading::OffUserPurchases($_SESSION['id'], Trading::GetPurchases($pid)->category);
		Trading::SetPurchases($pid, 'enable', 1);
		result(['alert' => 'success']);
	}
	
	if(isset($_POST['off'])) {
		$pid = clean($_POST['pid'], "int");
		
		if(empty($pid)) {
			result(['alert' => 'warning', 'message' => 'Отсутствует индекс']);
		}
		
		if(!Trading::IsUserPurchases($_SESSION['id'], $pid)) {
			result(['alert' => 'warning', 'message' => 'Нет предмета в Инвентаре']);
		}
		
		Trading::SetPurchases($pid, 'enable', 0);
		result(['alert' => 'success']);
	}
	
	/*
		Калькулятор
	*/
	if(isset($_POST['clc'])) {
		$playground = new Playground($pdo, $conf);
		$get_configs = $playground->get_configs();
		
		exit(json_encode([
			'status' => '1',
			'recoil' => $_POST['value'] * $get_configs->course . ' руб.',
			'receiving' => $_POST['value'] . ' ' . $get_configs->currency
		]));
	}
	
	/*
		Обмен валюты
	*/
	if(isset($_POST['on_exchange'])) {
		$count = clean($_POST['value'], "int");
		$price = $count * Trading::conf()->course;
		
		if(user()->shilings >= $price) {
			$shilings = user()->shilings - $price;
			
			if(pdo()->prepare("UPDATE `users` SET `shilings`=:shilings WHERE `id`=:id")->execute([':shilings' => $shilings, ':id' => $_SESSION['id']])) {
				Trading::SetBalance($_SESSION['id'], Trading::GetBalance($_SESSION['id']) + $count);
				result([
					'status' => '1',
					'html' => '<font class="text-success">Успешный обмен валюты!</font>'
				]);
			}
		}
		
		result([
			'status' => '1',
			'html' => '<font class="text-danger">Недостаточно средств</font>'
		]);
	}