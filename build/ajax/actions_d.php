<?PHP
	include_once("{$_SERVER['DOCUMENT_ROOT']}/inc/start.php");
	
	if($conf->token == 1 && ($_SESSION['token'] != clean($_POST['token'], null))) {
		log_error("Неверный токен"); 
		exit(json_encode([
			'status' => '2',
			'message' => 'Ошибка токена..'
		]));
	}
	
	if(isset($_POST['i_user'])) {
		if(empty($_POST['phpaction'])) {
			log_error("Прямой вызов actions_panel.php");
			exit('Ошибка: [Прямой вызов инклуда]');
		}
		
		if(empty($_SESSION['id'])){
			exit(json_encode([
				'status' => '2',
				'message' => 'Сначала авторизуйтесь!'
			]));
		}
		
		if(empty($_POST['index'])){
			exit(json_encode([
				'status' => '2',
				'message' => 'Сначала укажите индекс покупки!'
			]));
		}
		
		if(isset($_POST['buy_point'])) {
			$q = $injSql->query("SELECT * FROM `users` WHERE `id`='{$_SESSION['id']}'");
			$u = $injSql->arr($q);
			
			$w = $injSql->fqr(["SELECT * FROM `points__product` WHERE `id`='{$_POST['index']}'"]);
			
			if($u['points'] >= $w['price']) {
				$r = min_user_points(['id' => $_SESSION['id'], 'points' => $w['price']]);
				
				if($r['status'] == 1 && $injSql->query("INSERT INTO `points__purchases`(`user_id`, `product_id`, `timebuy`, `last_file`, `enable`) VALUES ('{$_SESSION['id']}', '{$_POST['index']}', '".time()."', '', 0)")) {
					exit(json_encode([
						'status' => '1',
						'message' => "Вы совершили покупку \"{$w['name']}\", спасибо!"
					]));
				}
				else {
					exit(json_encode([
						'status' => '2',
						'message' => "{$r['message']}, ошибка запроса.."
					]));
				}
			}
			else {
				exit(json_encode([
					'status' => '2',
					'message' => "У Вас недостаточно бонусов, для покупки: {$w['name']}"
				]));
			}
		}
		
		if(isset($_POST['load_my_points'])) {
			$points = $injSql->query("SELECT * FROM `points__purchases` WHERE `user_id`='{$_SESSION['id']}'");

			if($injSql->rows($points)) {
				while($r = $injSql->arr($points))
					$p2 .= "<tr><td>{$r['id']}</td><td>".
						$injSql->fqr(["SELECT * FROM `points__product` WHERE `id`='{$r['product_id']}'", 'name'])
					."</td><td>".date("d.m.Y в H:i", $r['timebuy'])."</td><td>".($r['enable'] ? "<button class=\"btn btn-sm btn-danger btn-block text-white\" OnClick=\"enable_points({$r['id']}, 0);\">Отключить</button>" : "<button class=\"btn btn-sm btn-success btn-block text-white\" OnClick=\"enable_points({$r['id']}, 1);\">Включить</button>")."</td></tr>";
			}
			else {
				$p2 = "<tr><td colspan=\"10\"><a class=\"btn btn-sm btn-default\" href=\"/store/points\">Открыть магазин</a></td></tr>";
			}
			
			exit($p2);
		}
		
		if(isset($_POST['enable_point'])) {
			$q = $injSql->query("SELECT * FROM `users` WHERE `id`='{$_SESSION['id']}'");
			$u = $injSql->arr($q);
			
			$p = $injSql->fqr(["SELECT * FROM `points__purchases` WHERE `id`='{$_POST['index']}'"]);
			$w = $injSql->fqr(["SELECT * FROM `points__product` WHERE `id`='{$p['product_id']}'"]);
			$c = $injSql->fqr(["SELECT * FROM `points__category` WHERE `id`='{$w['category']}'"]);
			
			if($p['user_id'] != $_SESSION['id']) {
				exit(json_encode([
					'status' => '2',
					'message' => 'Товар Вам не принадлежит!'
				]));
			}
			
			if($injSql->query("UPDATE `points__purchases` SET `enable`='{$_POST['enable']}' WHERE `id`='{$p['id']}'")) {
				switch($c['code']) {
					case "user_avatar": {
						switch($_POST['enable']) {
							case '1': {
								if($injSql->query("UPDATE `users` SET `avatar`='{$w['file']}' WHERE `id`='{$_SESSION['id']}'") && $injSql->query("UPDATE `points__purchases` SET `last_file`='{$u['avatar']}' WHERE `id`='{$_POST['index']}'")) {
									exit(json_encode([
										'status' => '1',
										'message' => "Товар был ".($_POST['enable'] ? 'включен' : 'отключен').""
									]));
								}
							}
							default: {
								if($injSql->query("UPDATE `users` SET `avatar`='{$p['last_file']}' WHERE `id`='{$_SESSION['id']}'")) {
									exit(json_encode([
										'status' => '1',
										'message' => "Товар был ".($_POST['enable'] ? 'включен' : 'отключен').""
									]));
								}
							}
						}
						
						break;
					}
					case "user_background": {
						switch($_POST['enable']) {
							case '1': 
								if($injSql->query("INSERT INTO `points__backgrounds` (`id_user`, `image`, `id_purchases`) VALUES ('{$_SESSION['id']}', '{$w['file']}', '{$_POST['index']}')")) {
									exit(json_encode([
										'status' => '1',
										'message' => "Товар был ".($_POST['enable'] ? 'включен' : 'отключен').""
									]));
								}
								else {
									exit(json_encode([
										'status' => '2',
										'message' => "Ошибка запроса SQL."
									]));
								}
								
								
								break;
							default:
								$injSql->query("DELETE FROM `points__backgrounds` WHERE `id_purchases`='{$_POST['index']}'");
								
								exit(json_encode([
									'status' => '1',
									'message' => "Товар был ".($_POST['enable'] ? 'включен' : 'отключен').""
								]));
								
								break;
						}
						
						break;
					}
					case "user_frame": {
						switch($_POST['enable']) {
							case '1': 
								$injSql->query("INSERT INTO `points__frame`(`id_user`, `frame`, `id_purchases`) VALUES ('{$_SESSION['id']}', '{$w['file']}', '{$_POST['index']}')");
								
								exit(json_encode([
									'status' => '1',
									'message' => "Товар был ".($_POST['enable'] ? 'включен' : 'отключен').""
								]));
								
								break;
							default:
								$injSql->query("DELETE FROM `points__frame` WHERE `id_purchases`='{$_POST['index']}'");
								
								exit(json_encode([
									'status' => '1',
									'message' => "Товар был ".($_POST['enable'] ? 'включен' : 'отключен').""
								]));
								
								break;
						}
						
						break;
					}
				}
			}
			
			exit(json_encode([
				'status' => '2',
				'message' => 'Произошла ошибка.'
			]));
		}
		
		exit(json_encode([
			'status' => '2',
			'message' => 'Я не знаю почему.. Но, ты оказался в жопе парень..'
		]));
	}
	
	if(isset($_GET['uploadImage']) && isset($_GET['uip']) && is_admin()) {
		if(0 < $_FILES['file']['error']) {
			exit(json_encode([
				'status' => '2',
				'message' => "Куда прёш? Пидрила."
			]));
		}
		
		$fileName = date("siH") . rand(100, 10000) . "_{$_FILES['file']['name']}";
		
		if(!move_uploaded_file($_FILES['file']['tmp_name'], "{$_SERVER['DOCUMENT_ROOT']}/files/points/{$fileName}")) {
			exit(json_encode([
				'status' => '2',
				'message' => "Проблема загрузки картинки"
			]));
		}
		
		exit(json_encode([
			'status' => '1',
			'message' => 'Картинка загружена успешно!',
			'filename' => "/files/points/{$fileName}"
		]));
	}
	
	if(empty($_POST['phpaction'])) {
		log_error("Прямой вызов actions_panel.php");
		exit('Ошибка: [Прямой вызов инклуда]');
	}
	
	if(!is_admin()) {
		exit('Ошибка: [Доступно только администраторам]');
	}
	
	if(isset($_POST['add_category_points'])) {
		if(strlen($_POST['name']) < 4) {
			exit(json_encode([
				'status' => '2',
				'message' => 'Ячейка имени должна быть более 4 ед.'
			]));
		}
		
		if($injSql->query("INSERT INTO `points__category`(`name`, `code`) VALUES ('{$_POST['name']}', '{$_POST['code']}')")) {
			exit(json_encode([
				'status' => '1',
				'message' => 'Категория успешно добавлена!'
			]));
		}
	}
	
	if(isset($_POST['del_category_points'])) {
		if(empty($_POST['index'])) {
			exit(json_encode([
				'status' => '2',
				'message' => 'Сначала укажите индекс!'
			]));
		}
		
		$w = $injSql->fqr([
			"SELECT * FROM `points__category` WHERE `id`='{$_POST['index']}'"
		]);
		
		if($injSql->query("DELETE FROM `points__category` WHERE `id`='{$_POST['index']}'")) {
			if($injSql->query("DELETE FROM `points__product` WHERE `category`='{$w['id']}'")) {
				exit(json_encode([
					'status' => '1',
					'message' => 'Категория успешно удалена!'
				]));
			}
			
			exit(json_encode([
				'status' => '1',
				'message' => 'Категория успешно удалена!'
			]));
		}
		
		exit(json_encode([
			'status' => '2',
			'message' => 'Не фортануло, не получилось..'
		]));
	}
	
	if(isset($_POST['add_product_points'])) {
		if(strlen($_POST['name']) < 2 || strlen($_POST['price']) < 2 || strlen($_POST['file']) < 2 || empty($_POST['category'])) {
			if(file_exists($_SERVER['DOCUMENT_ROOT'] . $_POST['file'])) {
				unlink($_SERVER['DOCUMENT_ROOT'] . $_POST['file']);
			}
			
			exit(json_encode([
				'status' => '2',
				'message' => 'Заполните все данные!'
			]));
		}
		
		if($injSql->query("INSERT INTO `points__product`(`name`, `price`, `category`, `file`) VALUES ('{$_POST['name']}', '{$_POST['price']}', '{$_POST['category']}', '{$_POST['file']}')")) {
			exit(json_encode([
				'status' => '1',
				'message' => 'Категория успешно добавлена!'
			]));
		}
		
		if(file_exists($_SERVER['DOCUMENT_ROOT'] . $_POST['file'])) {
			unlink($_SERVER['DOCUMENT_ROOT'] . $_POST['file']);
		}
		
		exit(json_encode([
			'status' => '2',
			'message' => 'Ошибка при импорте в базу данных'
		]));
	}
	
	if(isset($_POST['del_point'])) {
		if(empty($_POST['index'])) {
			exit(json_encode([
				'status' => '2',
				'message' => 'Сначала укажите индекс!'
			]));
		}
		
		$w = $injSql->fqr([
			"SELECT * FROM `points__product` WHERE `id`='{$_POST['index']}'"
		]);
		
		if($injSql->query("DELETE FROM `points__product` WHERE `id`='{$_POST['index']}'") && unlink($_SERVER['DOCUMENT_ROOT'] . $w['file'])) {
			exit(json_encode([
				'status' => '1',
				'message' => 'Товар успешно удалён!'
			]));
		}
		else {
			exit(json_encode([
				'status' => '2',
				'message' => 'Произошла ошибка в SQL'
			]));
		}
	}