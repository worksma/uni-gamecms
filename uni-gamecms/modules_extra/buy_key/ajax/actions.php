<?php
include_once "../../../inc/start.php";
include_once "../../../inc/protect.php";
if(empty($_POST['phpaction'])) {
	log_error("Прямой вызов actions.php");
	exit(json_encode(array('status' => '2')));
}
if($conf->token == 1 && ($_SESSION['token'] != clean($_POST['token'], null))) {
	log_error("Неверный токен");
	exit(json_encode(array('status' => '2')));
}

if(!is_auth() && !is_admin()) {
	exit(json_encode(array('status' => '2', 'data' => 'Доступно только авторизованному')));
}
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
if(isset($_POST['get_services'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit ();
	}
	$i    = 0;
	$data = '';
	$STH  = $pdo->query("SELECT `id`, `name`, `sale` FROM `bk_services` WHERE `server` = '$id' ORDER BY `trim`");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		if($row->sale != 2) {
			if($i == 0) {
				$data .= '<script>bk_get_tarifs('.$row->id.');</script>';
				$i++;
			}
			$data .= '<option value="'.$row->id.'">'.$row->name.'</option>';
		}
	}
	$data = array('status' => '1', 'data' => $data);
	exit(json_encode($data));
}
if(isset($_POST['get_tarifs'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit ();
	}

	$STH = $pdo->query("SELECT `bk_services`.`text`, `servers`.`discount` FROM `bk_services` LEFT JOIN `servers` ON `bk_services`.`server`=`servers`.`id` WHERE `bk_services`.`id` = '$id' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row             = $STH->fetch();
	$text            = $row->text;
	$server_discount = $row->discount;

	$STH = $pdo->query("SELECT discount FROM config__prices LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$disc     = $STH->fetch();
	$discount = $disc->discount;

	$data = '';
	$STH  = $pdo->query("SELECT `id`, `price`, `time` FROM `bk_services_times` WHERE `service` = '$id' ORDER BY `price`");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		if($row->time == 0) {
			$time = 'Навсегда';
		} else {
			$time = $row->time.' дней';
		}

		$proc  = calculate_discount($server_discount, $discount, $user->proc);
		$price = calculate_price($row->price, $proc);

		if($price != $row->price) {
			$data .= '<option value="'.$row->id.'">'.$time.' - '.$price.' '.$messages['RUB'].' (с учетом скидки)</option>';
		} else {
			$data .= '<option value="'.$row->id.'">'.$time.' - '.$price.' '.$messages['RUB'].'</option>';
		}
	}
	exit(json_encode(array('status' => '1', 'data' => $data, 'text' => $text)));
}
if(isset($_POST['buy_key'])) {
	$server  = checkJs($_POST['server'], "int");
	$service = checkJs($_POST['service'], "int");
	$tarif   = checkJs($_POST['tarif'], "int");

	if(empty($server) || empty($service) || empty($tarif)) {
		exit(json_encode(array('status' => '2', 'info' => '')));
	}

	$STH = $pdo->prepare("SELECT id, ip, port, name, bk_host, bk_user, bk_pass, bk_db, bk_code, discount FROM servers WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':id' => $server));
	$server = $STH->fetch();
	if(empty($server->id) || empty($server->bk_host)) {
		exit(json_encode(array('status' => '2', 'info' => '')));
	}
	if(!$pdo2 = db_connect($server->bk_host, $server->bk_db, $server->bk_user, $server->bk_pass)) {
		exit(json_encode(array('status' => '2', 'Ошибка подключения к базе данных!')));
	}
	set_names($pdo2, $server->bk_code);

	$STH = $pdo->prepare("SELECT id, shilings, proc FROM users WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':id' => $_SESSION['id']));
	$row = $STH->fetch();
	if(empty($row->id)) {
		exit(json_encode(array('status' => '2', 'info' => '')));
	}
	$proc     = $row->proc;
	$shilings = $row->shilings;

	$STH = $pdo->prepare("SELECT bk_services_times.price, bk_services.name, bk_services_times.time FROM bk_services LEFT JOIN bk_services_times ON bk_services.id = bk_services_times.service WHERE bk_services.server=:server AND bk_services.id=:service AND bk_services_times.id=:tarif LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':server' => $server->id, ':service' => $service, ':tarif' => $tarif));
	$row = $STH->fetch();
	if(empty($row->price)) {
		exit(json_encode(array('status' => '2', 'info' => '')));
	}
	$price = $row->price;
	$time  = $row->time;
	$name  = $row->name;

	$STH = $pdo->query("SELECT discount FROM config__prices LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$disc     = $STH->fetch();
	$discount = $disc->discount;

	$proc  = calculate_discount($server->discount, $discount, $proc);
	$price = calculate_price($price, $proc);

	if($shilings < $price) {
		$price_delta = round_shilings($price - $shilings);
		exit (json_encode(array('status' => '2',
								'info'   => 'У Вас недостаточно средств.<br><a href="../purse?price='.$price_delta.'">Пополните баланс на '.$price_delta.$messages['RUB'].'.</a>')));
	}
	$shilings = round_shilings($shilings - $price);

	$key = crate_pass(20, 2);
	$STH = $pdo2->prepare("SELECT key_name FROM table_keys WHERE key_name=:key LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':key' => $key));
	$row = $STH->fetch();
	if(isset($row->key_name)) {
		$key = crate_pass(21, 2);
	}

	$STH = $pdo2->prepare("SELECT sid FROM keys_servers WHERE address=:address LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':address' => $server->ip.":".$server->port));
	$row = $STH->fetch();
	if(empty($row->sid)) {
		exit (json_encode(array('status' => '2', 'info' => '')));
	} else {
		$sid = $row->sid;
	}

	$STH = $pdo2->prepare("INSERT INTO table_keys (key_name,type,expires,uses,sid,param1,param2,active) VALUES (:key_name, :type, :expires, :uses, :sid, :param1, :param2, :active)");
	$STH->execute(array(':key_name' => $key,
						':type'     => 'vip_add',
						':expires'  => '0',
						':uses'     => '1',
						':sid'      => $sid,
						':param1'   => $name,
						':param2'   => $time * 24 * 60 * 60,
						':active'   => '1'));

	$date = date("Y-m-d H:i:s");
	$STH  = $pdo->prepare("INSERT INTO money__actions (date,shilings,author,type) VALUES (:date, :shilings, :author, :type)");
	$STH->execute(array('date' => $date, 'shilings' => $price, 'author' => $_SESSION['id'], 'type' => '2'));

	$STH = $pdo->prepare("UPDATE users SET shilings=:shilings WHERE id=:id LIMIT 1");
	$STH->execute(array(':shilings' => $shilings, ':id' => $_SESSION['id']));

	$mess = "Поздравляем Вас с успешной покупкой <b>".$name."</b> на сервере <b>".$server->name."</b><br>";
	$mess .= "Использование ключа: введите в консоль <b>key ".$key."</b>";
	$STH  = $pdo->prepare("INSERT INTO notifications (message,date,user_id,type) VALUES (:message, :date, :user_id, :type)");
	$STH->execute(array('message' => $mess, 'date' => $date, 'user_id' => $_SESSION['id'], 'type' => '2'));

	$mess2 = "Куплен ".$name." на сервере ".$server->name." пользователем: <a href='../profile?id=".$_SESSION['id']."'>".$_SESSION['login']."</a>\r\n";
	$mess2 .= "Его ключ: <b>".$key."</b> \r\n";

	$STH = $pdo->prepare("INSERT INTO notifications (message,date,user_id,type) VALUES (:message, :date, :user_id, :type)");
	$STH->execute(array('message' => $mess2, 'date' => $date, 'user_id' => '1', 'type' => '2'));

	$file_name = get_log_file_name("buy_key");
	if(file_exists($_SERVER['DOCUMENT_ROOT']."/logs/".$file_name)) {
		$i = "a";
	} else {
		$i = "w";
	}
	$file = fopen($_SERVER['DOCUMENT_ROOT']."/logs/".$file_name, $i);
	fwrite($file, "[".$date." | Пользователь: ".$_SESSION['login']." - ".$_SESSION['id']."] : [Куплен ".$name." на сервере ".$server->name." за ".$price."р, его ключ: ".$key."] \r\n");
	fclose($file);

	exit(json_encode(array('status' => '3', 'info' => $mess, 'shilings' => $shilings)));
}

if(!is_admin()) {
	exit(json_encode(array('status' => '2', 'data' => 'Досутпно только администратору')));
}

if(isset($_POST['load_servers'])) {
	$i   = 0;
	$STH = $pdo->query("SELECT name,ip,port,id,bk_host,bk_code,bk_user,bk_pass,bk_db FROM servers WHERE type = '4' ORDER BY trim");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		?>
		<div class="col-md-6">
			<form id="serv_<?php echo $row->id ?>" class="block">
				<div class="block_head">
					<?php echo $row->name ?> (<?php echo $row->ip ?>:<?php echo $row->port ?>)
				</div>

				<div class="form-group">
					<label>
						<h4>
							db хост
						</h4>
					</label>
					<input value="<?php echo $row->bk_host ?>" type="text" class="form-control" name="bk_host" maxlength="64" autocomplete="off">
				</div>
				<div class="form-group">
					<label>
						<h4>
							db логин
						</h4>
					</label>
					<input value="<?php echo $row->bk_user ?>" type="text" class="form-control" name="bk_user" maxlength="32" autocomplete="off">
				</div>
				<div class="form-group">
					<label>
						<h4>
							db пароль
						</h4>
					</label>
					<input value="<?php echo $row->bk_pass ?>" type="password" class="form-control" name="bk_pass" maxlength="32" autocomplete="off">
				</div>
				<div class="form-group">
					<label>
						<h4>
							db база
						</h4>
					</label>
					<input value="<?php echo $row->bk_db ?>" type="text" class="form-control" name="bk_db" maxlength="32" autocomplete="off">
				</div>
				<div class="form-group">
					<label>
						<h4>
							Кодировка
						</h4>
					</label><br>
					<select class="form-control" name="bk_code">
						<option value="0" <?php if($row->bk_code == '0') { ?> selected <?php } ?>>Своя</option>
						<option value="1" <?php if($row->bk_code == '1') { ?> selected <?php } ?>>utf-8</option>
						<option value="2" <?php if($row->bk_code == '2') { ?> selected <?php } ?>>latin1</option>
					</select>
				</div>

				<div class="mt-10">
					<div id="edit_serv_result<?php echo $row->id ?>" class="mt-10"></div>
					<button onclick="bk_edit_server('<?php echo $row->id ?>', 0);" type="button" class="btn2">Сохранить</button>
					<button type="button" class="btn2 btn-cancel" onclick="bk_edit_server('<?php echo $row->id ?>', 1);">Очистить</button>
				</div>
			</form>
		</div>
		<?php
		if($i % 2 == 1) {
			echo "<div class='clearfix'></div>";
		}
		$i++;
	}

	if($i == 0) {
		exit ('Серверов нет');
	}
}
if(isset($_POST['edit_server'])) {
	foreach($_POST as $key => $value) {
		switch($key) {
			case 'id':
				$$key = check($value, "int");
				break;
			case 'bk_code':
				$$key = check($value, "int");
				break;
			default:
				$$key = check($value, null);
				break;
		}
	}

	if(empty($bk_code)) {
		$bk_code = 0;
	}
	if(empty($id)) {
		exit (json_encode(array('status' => '2')));
	}

	if($_POST['clean'] == '1') {
		$bk_host = '';
		$bk_user = '';
		$bk_pass = '';
		$bk_db   = '';
		$bk_code = '0';
	} else {
		if(empty($bk_host) or empty($bk_user) or empty($bk_pass) or empty($bk_db)) {
			exit('<p class="text-danger">Заполните поля: db хост, db логин, db пароль</p><script>setTimeout(show_error, 500);</script>');
		} else {
			if(!$pdo2 = db_connect($bk_host, $bk_db, $bk_user, $bk_pass)) {
				exit('<p class="text-danger">Ошибка подключения к базе данных!</p><script>setTimeout(show_error, 500);</script>');
			}
			if(!check_table('table_keys', $pdo2)) {
				exit('<p class="text-danger">Не найдена таблица table_keys в базе данных.</p><script>setTimeout(show_error, 500);</script>');
			}
			if(!check_table('keys_servers', $pdo2)) {
				exit('<p class="text-danger">Не найдена таблица keys_servers в базе данных.</p><script>setTimeout(show_error, 500);</script>');
			}
		}

		$STH = $pdo2->query("SHOW COLUMNS FROM table_keys");
		$STH->execute();
		$row          = $STH->fetchAll();
		$if['active'] = 0;
		for($i = 0; $i < count($row); $i++) {
			if($row[$i]['Field'] == 'active') {
				$if['active']++;
			}
		}
		if($if['active'] == 0) {
			$pdo2->exec("ALTER TABLE table_keys ADD active INT(1) NOT NULL DEFAULT '0' AFTER sid;");
		}

		$STH = $pdo->prepare("SELECT ip, port FROM servers WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':id' => $id));
		$row = $STH->fetch();
		if(empty($row->ip)) {
			exit (json_encode(array('status' => '2')));
		} else {
			$address = $row->ip.":".$row->port;
		}

		$STH = $pdo2->prepare("SELECT sid FROM keys_servers WHERE address=:address LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':address' => $address));
		$row = $STH->fetch();
		if(empty($row->sid)) {
			$STH = $pdo2->prepare("INSERT INTO keys_servers (address) VALUES (:address)");
			$STH->execute(array(':address' => $address));
		}
	}

	$STH = $pdo->prepare("UPDATE servers SET bk_host=:bk_host,bk_user=:bk_user,bk_pass=:bk_pass,bk_db=:bk_db,bk_code=:bk_code WHERE id='$id' LIMIT 1");
	if($STH->execute(array('bk_host' => $bk_host, 'bk_user' => $bk_user, 'bk_pass' => $bk_pass, 'bk_db' => $bk_db, 'bk_code' => $bk_code)) == '1') {
		exit('<p class="text-success">Сервер успешно изменен</p><script>setTimeout(show_ok, 500);</script>');
	}
}

if(isset($_POST['load_services'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit ('<p class="mt-10 mb-0">Услуг нет</p>');
	}
	$type = checkJs($_POST['type'], "int");
	if(empty($type)) {
		exit ('<p class="mt-10 mb-0">Услуг нет</p>');
	}
	if($type == 1) {
		$STH = $pdo->prepare("SELECT id, name FROM bk_services WHERE server=:id ORDER BY trim");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':id' => $id));
		while($row = $STH->fetch()) {
			echo '<option value="'.$row->id.'">'.$row->name.'</option>';
		}
	} else {
		$STH = $pdo->prepare("SELECT id,name,type FROM servers WHERE id=:id AND type = '4' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':id' => $id));
		$server = $STH->fetch();
		?>
		<div class="block">
			<?php
			$STH = $pdo->prepare("SELECT id, name, text, sale, trim FROM bk_services WHERE server=:id ORDER BY trim");
			$STH->execute(array(':id' => $server->id));
			$services = $STH->fetchAll();
			$count    = count($services);
			if($count != 0) {
				for($i = 0; $i < $count; $i++) {
					$id = $services[$i]['id'];
					?>
					<div class="row mb-10" id="service<?php echo $id ?>">
						<form class="col-md-6" id="form_service<?php echo $id ?>">
							<div class="block_head">Услуга #<?php echo $i + 1; ?></div>
							<select class="form-control mt-10" id="sale<?php echo $id ?>" name="sale">
								<option value="1" <?php if($services[$i]['sale'] == '1') {
									echo 'selected';
								} ?>>Продажа: включена
								</option>
								<option value="2" <?php if($services[$i]['sale'] == '2') {
									echo 'selected';
								} ?>>Продажа: выключена
								</option>
							</select>
							<input value="<?php echo $services[$i]['name'] ?>" class="form-control mt-10" type="text" maxlength="255" id="name<?php echo $id ?>" name="name" placeholder="Название услуги" autocomplete="off">
							<br>
							<textarea id="text<?php echo $id ?>" class="form-control" rows="5"><?php echo $services[$i]['text'] ?></textarea>
							<script>
								init_tinymce('text<?php echo $id ?>', '<?php echo md5($conf->code); ?>', 'lite');
							</script>
							<button class="btn btn-default mt-10" onclick="bk_edit_service(<?php echo $id ?>);" type="button">Изменить</button>
							<button class="btn btn-default mt-10" onclick="bk_dell_service(<?php echo $id ?>);" type="button">Удалить</button>
							<button class="btn btn-default mt-10" onclick="bk_up_service(<?php echo $id ?>);" type="button">Поднять</button>
							<button class="btn btn-default mt-10" onclick="bk_down_service(<?php echo $id ?>);" type="button">Опустить</button>
						</form>
						<div class="col-md-6">
							<div class="block_head">Тарифы услуги #<?php echo $i + 1; ?></div>
							<div class="tarifs">
								<table class="table table-bordered table-condensed mb-0">
									<thead>
									<tr>
										<td>#</td>
										<td>Время</td>
										<td>Цена</td>
										<td>Действие</td>
									</tr>
									</thead>
									<tbody>
									<?php
									$STH = $pdo->prepare("SELECT id, time, price FROM bk_services_times WHERE service = :id");
									$STH->execute(array(':id' => $id));
									$STH->execute();
									$tarifs = $STH->fetchAll();
									$count2 = count($tarifs);
									for($j = 0; $j < $count2; $j++) {
										if($tarifs[$j]['time'] == 0) {
											$tarifs[$j]['time'] = 'Навсегда';
										}
										?>
										<tr id="tarif<?php echo $tarifs[$j]['id'] ?>">
											<td width="1%"><?php echo $j + 1; ?></td>
											<td>
												<input value="<?php echo $tarifs[$j]['time'] ?>" class="form-control" type="text" maxlength="6" id="time<?php echo $tarifs[$j]['id'] ?>" placeholder="Время" autocomplete="off">
											</td>
											<td>
												<input value="<?php echo $tarifs[$j]['price'] ?>" class="form-control" type="text" maxlength="6" id="price<?php echo $tarifs[$j]['id'] ?>" placeholder="Цена" autocomplete="off">
											</td>
											<td width="30%">
												<div class="btn-group" role="group">
													<button onclick="bk_edit_tarif (<?php echo $tarifs[$j]['id'] ?>);" class="btn btn-default" type="button">
														<span class="glyphicon glyphicon-pencil"></span></button>
													<button onclick="bk_dell_tarif (<?php echo $tarifs[$j]['id'] ?>);" class="btn btn-default" type="button">
														<span class="glyphicon glyphicon-trash"></span></button>
												</div>
											</td>
										</tr>
										<?php
									}
									?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<?php
				}
			} else {
				?>
				<p class="mt-10 mb-0">Услуг нет</p>
				<?php
			}
			?>
		</div>
		<?php
	}
	exit();
}
if(isset($_POST['add_service'])) {
	foreach($_POST as $key => $value) {
		switch($key) {
			case 'server':
				$$key = check($value, "int");
				break;
			case 'sale':
				$$key = check($value, "int");
				break;
			case 'text':
				incNotifications();
				$text = HTMLPurifier()->purify($_POST['text']);
				$text = find_img_mp3($text, $_SESSION['id'], 1);
				break;
			default:
				$$key = check($value, null);
				break;
		}
	}

	if(empty($server)) {
		exit (json_encode(array('status' => '2', 'input' => 'server', 'reply' => 'Заполните!')));
	}
	if(empty($name)) {
		exit (json_encode(array('status' => '2', 'input' => 'name', 'reply' => 'Заполните!')));
	}
	if(mb_strlen($name, 'UTF-8') > 255) {
		exit (json_encode(array('status' => '2', 'input' => 'name', 'reply' => 'Не более 255 символов!')));
	}
	if(mb_strlen($text, 'UTF-8') > 5000) {
		exit (json_encode(array('status' => '2', 'input' => 'text', 'reply' => 'Слишком длинный контент.')));
	}
	if($sale != 1 and $sale != 2) {
		exit (json_encode(array('status' => '2', 'input' => 'sale', 'reply' => 'Неверное значение!')));
	}

	$STH = $pdo->prepare("SELECT id,bk_host,bk_user,bk_pass,bk_db,bk_code,type FROM servers WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':id' => $server));
	$server = $STH->fetch();
	if($server->type != 4) {
		exit (json_encode(array('status' => '2', 'input' => 'server', 'reply' => 'Неверный тип сервера!')));
	}

	$STH = $pdo->prepare("SELECT trim FROM bk_services WHERE server=:server ORDER BY trim DESC LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':server' => $server->id));
	$tmp = $STH->fetch();
	if(isset($tmp->trim)) {
		$trim = $tmp->trim + 1;
	} else {
		$trim = 1;
	}

	$STH = $pdo->prepare("INSERT INTO bk_services (name,server,text,trim,sale) VALUES (:name, :server, :text, :trim, :sale)");
	if($STH->execute(array('name' => $name, 'server' => $server->id, 'text' => $text, 'trim' => $trim, 'sale' => $sale)) == '1') {
		exit(json_encode(array('status' => '1')));
	}
}
if(isset($_POST['edit_service'])) {
	foreach($_POST as $key => $value) {
		switch($key) {
			case 'id':
				$$key = check($value, "int");
				break;
			case 'server':
				$$key = check($value, "int");
				break;
			case 'sale':
				$$key = check($value, "int");
				break;
			case 'text':
				incNotifications();
				$text = HTMLPurifier()->purify($_POST['text']);
				$text = find_img_mp3($text, $_SESSION['id'], 1);
				break;
			default:
				$$key = check($value, null);
				break;
		}
	}

	if(empty($id)) {
		exit(json_encode(array('status' => '2')));
	}
	if(empty($name)) {
		exit (json_encode(array('status' => '2', 'input' => 'name', 'reply' => 'Заполните!')));
	}
	if(mb_strlen($name, 'UTF-8') > 255) {
		exit (json_encode(array('status' => '2', 'input' => 'name', 'reply' => 'Не более 255 символов!')));
	}
	if(mb_strlen($text, 'UTF-8') > 10000) {
		exit (json_encode(array('status' => '2', 'input' => 'text', 'reply' => 'Слишком длинный контент.')));
	}
	if($sale != 1 and $sale != 2) {
		exit (json_encode(array('status' => '2', 'input' => 'sale', 'reply' => 'Неверное значение!')));
	}

	$STH = $pdo->prepare("SELECT server FROM bk_services WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':id' => $id));
	$row = $STH->fetch();
	if(empty($row->server)) {
		exit (json_encode(array('status' => '2', 'input' => 'name', 'reply' => 'Услуга с данным id не найдена')));
	}

	$STH = $pdo->prepare("SELECT id,bk_host,bk_user,bk_pass,bk_db,bk_code,type FROM servers WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':id' => $server));
	$server = $STH->fetch();
	if($server->type != 4) {
		exit (json_encode(array('status' => '2', 'input' => 'server', 'reply' => 'Неверный тип сервера!')));
	}

	$STH = $pdo->prepare("UPDATE bk_services SET name=:name,text=:text,sale=:sale WHERE id='$id' LIMIT 1");
	if($STH->execute(array('name' => $name, 'text' => $text, 'sale' => $sale)) == '1') {
		exit(json_encode(array('status' => '1')));
	}
}
if(isset($_POST['up_service'])) {
	$number = check($_POST['id'], "int");

	$STH = $pdo->query("SELECT server FROM bk_services WHERE id='$number' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row    = $STH->fetch();
	$server = $row->server;

	if(empty($number) or empty($server)) {
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->query("SELECT id,trim FROM bk_services WHERE id='$number' and server='$server' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	if(empty($tmp->id)) {
		exit(json_encode(array('status' => '2')));
	}
	if($tmp->trim == 1) {
		exit(json_encode(array('status' => '2')));
	}
	$poz  = $tmp->trim;
	$poz2 = $tmp->trim - 1;

	$STH = $pdo->prepare("UPDATE bk_services SET trim=:trim WHERE trim='$poz2' and server='$server' LIMIT 1");
	if($STH->execute(array('trim' => $poz)) == '1') {
		$STH = $pdo->prepare("UPDATE bk_services SET trim=:poz2 WHERE id='$number' and server='$server' LIMIT 1");
		if($STH->execute(array('poz2' => $poz2)) == '1') {
			exit(json_encode(array('status' => '1')));
		} else {
			exit(json_encode(array('status' => '2')));
		}
	} else {
		exit(json_encode(array('status' => '2')));
	}
}
if(isset($_POST['down_service'])) {
	$number = check($_POST['id'], "int");

	$STH = $pdo->query("SELECT server FROM bk_services WHERE id='$number' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row    = $STH->fetch();
	$server = $row->server;

	if(empty($number) or empty($server)) {
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->query("SELECT id,trim from bk_services WHERE id='$number' and server='$server' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	if(empty($tmp->id)) {
		exit(json_encode(array('status' => '2')));
	}
	$poz  = $tmp->trim;
	$poz2 = $tmp->trim + 1;
	$STH  = $pdo->query("SELECT trim from bk_services WHERE server='$server' ORDER BY trim DESC LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	$max = $tmp->trim;

	if($poz == $max) {
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->prepare("UPDATE bk_services SET trim=:trim WHERE trim='$poz2' and server='$server' LIMIT 1");
	if($STH->execute(array('trim' => $poz)) == '1') {
		$STH = $pdo->prepare("UPDATE bk_services SET trim=:trim WHERE id='$number' and server='$server' LIMIT 1");
		if($STH->execute(array('trim' => $poz2)) == '1') {
			exit(json_encode(array('status' => '1')));
		} else {
			exit(json_encode(array('status' => '2')));
		}
	} else {
		exit(json_encode(array('status' => '2')));
	}
}
if(isset($_POST['dell_service'])) {
	$main_id = checkJs($_POST['id'], "int");
	if(empty($main_id)) {
		exit (json_encode(array('status' => '2')));
	}

	$STH = $pdo->query("SELECT server FROM bk_services WHERE id='$main_id' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row    = $STH->fetch();
	$server = $row->server;

	$STH = $pdo->query("SELECT trim from bk_services WHERE id='$main_id' and server='$server' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();

	$STH = $pdo->query("SELECT id,trim from bk_services WHERE trim>'$tmp->trim' and server='$server'");
	$STH->execute();
	$row   = $STH->fetchAll();
	$count = count($row);

	if($count == 0) {
		$pdo->exec("DELETE FROM bk_services_times WHERE service='$main_id'");
		$pdo->exec("DELETE FROM bk_services WHERE id='$main_id' LIMIT 1");
		exit(json_encode(array('status' => '1')));
	}

	for($i = 0; $i < $count; $i++) {
		$id   = $row[$i]['id'];
		$STH  = $pdo->prepare("UPDATE bk_services SET trim=:trim WHERE id='$id' and server='$server' LIMIT 1");
		$trim = $row[$i]['trim'] - 1;
		if($STH->execute(array('trim' => $trim)) != '1') {
			exit(json_encode(array('status' => '2')));
		}
	}

	$pdo->exec("DELETE FROM bk_services_times WHERE service='$main_id'");
	$pdo->exec("DELETE FROM bk_services WHERE id='$main_id' LIMIT 1");
	exit(json_encode(array('status' => '1')));
}

if(isset($_POST['add_tarif'])) {
	$service = check($_POST['service'], "int");
	$type    = 0;
	if(($_POST['time'] == 0) or (strnatcasecmp($_POST['time'], 'навсегда') == 0)) {
		$time = 0;
	} else {
		if(strpos($_POST['time'], '-') == false) {
			$time = check($_POST['time'], "int");
		} else {
			$time    = explode("-", $_POST['time']);
			$time[0] = check($time[0], "int");
			$time[1] = check($time[1], "int");
			$type    = 1;
		}
	}
	$price = check($_POST['price'], "int");

	if(empty($service)) {
		$result = array('status' => '2', 'input' => 'services', 'reply' => 'Заполните!');
		exit (json_encode($result));
	}

	if(empty($price)) {
		$result = array('status' => '2', 'input' => 'price', 'reply' => 'Заполните!');
		exit (json_encode($result));
	}
	if(mb_strlen($price, 'UTF-8') > 6) {
		$result = array('status' => '2', 'input' => 'price', 'reply' => 'Не более 6 символов!');
		exit (json_encode($result));
	}

	$STH = $pdo->query("SELECT id FROM bk_services WHERE id='$service' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(empty($row->id)) {
		exit(json_encode(array('status' => '2')));
	}

	if($type == 0) {
		if(empty($time) and $time != 0) {
			$result = array('status' => '2', 'input' => 'time', 'reply' => 'Заполните!');
			exit (json_encode($result));
		}
		if(mb_strlen($time, 'UTF-8') > 6) {
			$result = array('status' => '2', 'input' => 'time', 'reply' => 'Не более 6 символов!');
			exit (json_encode($result));
		}

		$data = array('service' => $service, 'price' => $price, 'time' => $time);
		$STH  = $pdo->prepare("INSERT INTO bk_services_times (service,price,time) VALUES (:service, :price, :time)");
		if($STH->execute($data) == '1') {
			exit(json_encode(array('status' => '1')));
		}
	} elseif($type == 1) {
		if((empty($time[0]) and $time[0] != 0) or (empty($time[1]) and $time[1] != 0) or ($time[0] == $time[1]) or ($time[0] > $time[1])) {
			$result = array('status' => '2', 'input' => 'time', 'reply' => 'Укажите корректный диапазон!');
			exit (json_encode($result));
		}
		if(mb_strlen($time[0], 'UTF-8') > 3 or mb_strlen($time[1], 'UTF-8') > 3) {
			$result = array('status' => '2', 'input' => 'time', 'reply' => 'Не более 3 символов на каждый конец диапазона!');
			exit (json_encode($result));
		}

		for($i = $time[0]; $i <= $time[1]; $i++) {
			$price2 = $price * $i;
			$data   = array('service' => $service, 'price' => $price2, 'time' => $i);
			$STH    = $pdo->prepare("INSERT INTO bk_services_times (service,price,time) VALUES (:service, :price, :time)");
			$STH->execute($data);
		}
		exit(json_encode(array('status' => '1')));
	}
}
if(isset($_POST['edit_tarif'])) {
	$id = check($_POST['id'], "int");
	if(($_POST['time'] == 0) or (strnatcasecmp($_POST['time'], 'навсегда') == 0)) {
		$time = 0;
	} else {
		$time = check($_POST['time'], "int");
	}
	$price = check($_POST['price'], "int");

	if(empty($id)) {
		exit(json_encode(array('status' => '2')));
	}

	if(empty($time) and $time != 0) {
		$result = array('status' => '2', 'input' => 'time', 'reply' => 'Заполните!');
		exit (json_encode($result));
	}

	if(empty($price)) {
		$result = array('status' => '2', 'input' => 'price', 'reply' => 'Заполните!');
		exit (json_encode($result));
	}

	if(mb_strlen($time, 'UTF-8') > 6) {
		$result = array('status' => '2', 'input' => 'time', 'reply' => 'Не более 6 символов!');
		exit (json_encode($result));
	}

	if(mb_strlen($price, 'UTF-8') > 6) {
		$result = array('status' => '2', 'input' => 'price', 'reply' => 'Не более 6 символов!');
		exit (json_encode($result));
	}

	$data = array('time' => $time, 'price' => $price);
	$STH  = $pdo->prepare("UPDATE bk_services_times SET time=:time,price=:price WHERE id='$id' LIMIT 1");
	if($STH->execute($data) == '1') {
		exit(json_encode(array('status' => '1')));
	}
}
if(isset($_POST['dell_tarif'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit (json_encode(array('status' => '2')));
	}
	$pdo->exec("DELETE FROM bk_services_times WHERE id='$id'");
	exit(json_encode(array('status' => '1')));
}
?>