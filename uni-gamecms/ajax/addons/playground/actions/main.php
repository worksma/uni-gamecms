<?PHP
	require("{$_SERVER['DOCUMENT_ROOT']}/inc/start.php");
	
	if(empty($_POST['phpaction'])) {
		exit(json_encode(['status' => '2']));
	}
	
	if($_SESSION['token'] != $_POST['token']) {
		exit(json_encode(['status' => '2']));
	}
	
	if(empty($_SESSION['id'])) {
		echo 'Ошибка: [Доступно только авторизованным]';
		exit(json_encode(['status' => '2']));
	}
	
	/*
		Загрузка категорий
	*/
	if(isset($_POST['load_category'])) {
		$sth = $pdo->query("SELECT * FROM `playground__category` WHERE 1");
		
		if($sth->rowCount()) {
			$sth->setFetchMode(PDO::FETCH_OBJ);
			
			$category = "<li class=\"".($_POST['category'] ? "" : "active")."\"><a href=\"/market\">Все предметы</a></li>";
			
			while($row = $sth->fetch()) {
				$category .= "<li class=\"".($_POST['category'] == $row->code_name ? "active" : "")."\"><a href=\"/market?category={$row->code_name}\">{$row->name}</a></li>";
			}
			
			exit(json_encode([
				'status' => '1',
				'html' => $category
			]));
		}
		
		exit(json_encode([
			'status' => '2',
			'html' => '<center>Категорий нет</center>'
		]));
	}
	
	/*
		Получение товаров на продажу
	*/
	if(isset($_POST['load_product_sell'])) {
		if(isset($_POST['category'])) {
			$sth_category = $pdo->query("SELECT * FROM `playground__category` WHERE `code_name`='{$_POST['category']}' ORDER BY `id` DESC");
			
			if($sth_category->rowCount()) {
				$sth_category->setFetchMode(PDO::FETCH_OBJ);
				$category = $sth_category->fetch();
				
				$sth = $pdo->query("SELECT * FROM `playground__product` WHERE `id_category`='{$category->id}' ORDER BY `id` DESC");
			}
			else {
				$sth = $pdo->query("SELECT * FROM `playground__product` WHERE 1 ORDER BY `id` DESC");
			}
		}
		else {
			$sth = $pdo->query("SELECT * FROM `playground__product` WHERE 1 ORDER BY `id` DESC");
		}
		
		if($sth->rowCount()) {
			$sth->setFetchMode(PDO::FETCH_OBJ);
			$playground = new Playground($pdo, $conf);
			$playground->clear_element();
			
			while($row = $sth->fetch()) {
				$playground->load_element("table/product-sell");
				
				$playground->set_element("{id}", $row->id);
				$playground->set_element("{name}", $row->name);
				$playground->set_element("{image}", $row->resource);
				$playground->set_element("{price}", $row->price . ' ' . $playground->get_configs()->currency);
				
				$sth2 = $pdo->query("SELECT * FROM `playground__sale` WHERE `id_product`='{$row->id}'");
				$playground->set_element("{count}", $sth2->rowCount());
			}
			
			exit(json_encode([
				'status' => '1',
				'html' => $playground->show_element()
			]));
		}
		
		exit(json_encode([
			'status' => '2',
			'html' => "<tr style=\"background:unset;\"><td colspan=\"3\"><center>Товаров нет</center></td></tr>"
		]));
	}
	
	/*
		Покупка товара
	*/
	if(isset($_POST['playground_buy'])) {
		$sth = $pdo->query("SELECT * FROM `playground__product` WHERE `id`='{$_POST['id_product']}'");
		
		if($sth->rowCount()) {
			$ath = $pdo->query("SELECT * FROM `playground__sale` WHERE `id_product`='{$_POST['id_product']}'");
			
			if(!$ath->rowCount()) {
				exit(json_encode([
					'status' => '2',
					'message' => 'Нет в наличие.'
				]));
			}
			
			$sth->setFetchMode(PDO::FETCH_OBJ);
			$playground = new Playground($pdo, $conf);
			$row = $sth->fetch();
			
			if($playground->get_balance($_SESSION['id']) >= $row->price) {
				if($playground->min_balance($_SESSION['id'], $row->price) && $pdo->query("INSERT INTO `playground__purchases`(`id_product`, `id_category`, `id_user`, `price`, `buy_time`) VALUES ('{$_POST['id_product']}', '{$row->id_category}', '{$_SESSION['id']}', '{$row->price}', '".time()."')")) {
					$ath->setFetchMode(PDO::FETCH_OBJ);
					$se = $ath->fetch();
					$pdo->query("DELETE FROM `playground__sale` WHERE `id`='{$se->id}'");
					
					if($se->id_seller != 0) {
						$playground->add_balance($se->id_seller, $row->price);
					}
					
					exit(json_encode([
						'status' => '1',
						'message' => 'Успешная покупка!'
					]));
				}
			}
			else {
				exit(json_encode([
					'status' => '2',
					'message' => 'Недостаточно средств.'
				]));
			}
		}
		
		exit(json_encode([
			'status' => '2',
			'message' => 'Запрашиваемый товар не найден.'
		]));
	}
	
	/*
		Загрузка предметов пользователя
	*/
	if(isset($_POST['load_items'])) {
		$sth = $pdo->query("SELECT * FROM `playground__purchases` WHERE `id_user`='{$_SESSION['id']}'");
		
		if($sth->rowCount()) {
			$sth->setFetchMode(PDO::FETCH_OBJ);
			$playground = new Playground($pdo, $conf);
			
			while($row = $sth->fetch()) {
				$playground->load_element("card/items");
				
				$ath = $pdo->query("SELECT * FROM `playground__product` WHERE `id`='{$row->id_product}'");
				$ath->setFetchMode(PDO::FETCH_OBJ);
				$arow = $ath->fetch();
				
				$playground->set_element("{id_purchases}", $row->id);
				$playground->set_element("{name}", $arow->name);
				$playground->set_element("{image}", $arow->resource);
				$playground->set_element("{active}", ($row->active ? "active" : ""));
			}
			
			exit(json_encode([
				'status' => '1',
				'html' => $playground->show_element()
			]));
		}
		
		exit(json_encode([
			'status' => '2',
			'html' => '<center style="flex:content;">Инвентарь пуст.</center>'
		]));
	}
	
	if(isset($_POST['playground_enable'])) {
		$sth = $pdo->query("SELECT * FROM `playground__purchases` WHERE `id`='{$_POST['id_purchases']}' and `id_user`='{$_SESSION['id']}'");
		
		if($sth->rowCount()) {
			$sth->setFetchMode(PDO::FETCH_OBJ);
			$row = $sth->fetch();
			
			if(!$row->active) {
				$pdo->query("UPDATE `playground__purchases` SET `active`='0' WHERE `id_user`='{$_SESSION['id']}' and `id_category`='{$row->id_category}'");
				$pdo->query("UPDATE `playground__purchases` SET `active`='1' WHERE `id`='{$_POST['id_purchases']}'");
			}
			else {
				$pdo->query("UPDATE `playground__purchases` SET `active`='0' WHERE `id`='{$_POST['id_purchases']}'");
			}
			
			$ath = $pdo->query("SELECT * FROM `playground__product` WHERE `id`='{$row->id_product}'");
			$ath->setFetchMode(PDO::FETCH_OBJ);
			$arow = $ath->fetch();
			
			exit(json_encode([
				'status' => '1',
				'message' => ($row->active ? "Вы выключили {$arow->name}" : "Вы включили {$arow->name}"),
				'info' => ($row->active ? "info" : "success")
			]));
		}
		
		exit(json_encode([
			'status' => '2',
			'message' => 'Произошла ошибка..'
		]));
	}
	
	/*
		Загрузка предметов на продажу
	*/
	if(isset($_POST['sell_load_items'])) {
		$sth = $pdo->query("SELECT * FROM `playground__purchases` WHERE `id_user`='{$_SESSION['id']}'");
		
		if($sth->rowCount()) {
			$sth->setFetchMode(PDO::FETCH_OBJ);
			$playground = new Playground($pdo, $conf);
			
			while($row = $sth->fetch()) {
				$playground->load_element("modal/sell");
				
				$ath = $pdo->query("SELECT * FROM `playground__product` WHERE `id`='{$row->id_product}'");
				$ath->setFetchMode(PDO::FETCH_OBJ);
				$arow = $ath->fetch();
				
				$playground->set_element("{id_purchases}", $row->id);
				$playground->set_element("{image}", $arow->resource);
			}
			
			exit(json_encode([
				'status' => '1',
				'html' => $playground->show_element()
			]));
		}
		
		exit(json_encode([
			'status' => '2',
			'html' => '<center style="flex:content;">Инвентарь пуст.</center>'
		]));
	}
	
	/*
		Продажа предмета пользователем
	*/
	if(isset($_POST['sell_product'])) {
		$sth = $pdo->query("SELECT * FROM `playground__purchases` WHERE `id`='{$_POST['id_purchases']}' and `id_user`='{$_SESSION['id']}'");
		
		if($sth->rowCount()) {
			$sth->setFetchMode(PDO::FETCH_OBJ);
			$row = $sth->fetch();
			
			if($pdo->query("INSERT INTO `playground__sale`(`id_product`, `id_category`, `id_seller`) VALUES ('{$row->id_product}', '{$row->id_category}', '{$_SESSION['id']}')")) {
				$pdo->query("DELETE FROM `playground__purchases` WHERE `id`='{$row->id}'");
				exit(json_encode([
					'status' => '1', 'message' => 'Предмет выставлен на продажу.'
				]));
			}
		}
		
		exit(json_encode([
			'status' => '2', 'message' => 'У Вас отсутствует данный предмет.'
		]));
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
		$sth = $pdo->query("SELECT * FROM `users` WHERE `id`='{$_SESSION['id']}'");
		$sth->setFetchMode(PDO::FETCH_OBJ);
		$row = $sth->fetch();
		
		$playground = new Playground($pdo, $conf);
		$get_configs = $playground->get_configs();
		
		if($row->shilings >= $_POST['value'] * $get_configs->course) {
			$shilings = $row->shilings - $_POST['value'] * $get_configs->course;
			
			if($pdo->query("UPDATE `users` SET `shilings`='{$shilings}' WHERE `id`='{$_SESSION['id']}'")) {
				$playground->add_balance($_SESSION['id'], $_POST['value']);
				exit(json_encode([
					'status' => '1',
					'html' => '<font class="text-success">Успешный обмен валюты!</font>'
				]));
			}
		}
		
		exit(json_encode([
			'status' => '1',
			'html' => '<font class="text-danger">Недостаточно средств.</font>'
		]));
	}