<?PHP
	require("{$_SERVER['DOCUMENT_ROOT']}/inc/start.php");
	
	if(empty($_POST['phpaction'])) {
		exit(json_encode(['status' => '2']));
	}
	
	if($_SESSION['token'] != $_POST['token']) {
		exit(json_encode(['status' => '2']));
	}
	
	if(!is_admin()) {
		echo 'Ошибка: [Доступно только администратору]';
		exit(json_encode(['status' => '2']));
	}
	
	/*
		Загрузка категорий
	*/
	if(isset($_POST['load_category'])) {
		$sth = $pdo->query("SELECT * FROM `playground__category` WHERE 1");
		
		if($sth->rowCount()) {
			$sth->setFetchMode(PDO::FETCH_OBJ);
			
			$category = '<option disabled selected>- выбрать -</option>';
			
			while($row = $sth->fetch()) {
				$category .= "<option value=\"{$row->id}\">{$row->name}</option>";
			}
			
			exit(json_encode(['status' => '2', 'html' => $category]));
		}
		
		exit(json_encode(['status' => '2', 'html' => '<option disabled selected>- категорий нет -</option>']));
	}
	
	if(isset($_POST['load_product'])) {
		$sth = $pdo->query("SELECT * FROM `playground__product` WHERE 1 ORDER BY `id` DESC");
		
		if($sth->rowCount()) {
			$sth->setFetchMode(PDO::FETCH_OBJ);
			$playground = new Playground($pdo, $conf);
			
			$playground->clear_element();
			while($row = $sth->fetch()) {
				$playground->load_element("table/product", true);
				
				$playground->set_element("{id}", $row->id);
				$playground->set_element("{name}", $row->name);
				$playground->set_element("{price}", $row->price);
				
				$ath = $pdo->query("SELECT `name` FROM `playground__category` WHERE `id`='{$row->id_category}'");
				$ath->setFetchMode(PDO::FETCH_OBJ);
				
				$playground->set_element("{category}", $ath->fetch()->name);
				$playground->set_element("{resource}", $row->resource);
				$playground->set_element("{availability}", $row->availability);
			}
			
			exit(json_encode(['status' => '1', 'html' => $playground->show_element()]));
		}
		
		exit(json_encode(['status' => '2', 'html' => '<td colspan="7"><center>Товаров нет.</center></td>']));
	}
	
	if(isset($_POST['remove'])) {
		$pdo->query("DELETE FROM `playground__product` WHERE `id`='{$_POST['id_product']}'");
		$pdo->query("DELETE FROM `playground__purchases` WHERE `pid`='{$_POST['id_product']}'");
		$pdo->query("DELETE FROM `playground__sale` WHERE `id_product`='{$_POST['id_product']}'");
		
		exit(json_encode(['status' => '1']));
	}
	
	if(isset($_POST['remove_category'])) {
		$pdo->query("DELETE FROM `playground__category` WHERE `id`='{$_POST['id_category']}'");
		$pdo->query("DELETE FROM `playground__product` WHERE `id_category`='{$_POST['id_category']}'");
		$pdo->query("DELETE FROM `playground__purchases` WHERE `id_category`='{$_POST['id_category']}'");
		$pdo->query("DELETE FROM `playground__sale` WHERE `id_category`='{$_POST['id_category']}'");
		
		exit(json_encode(['status' => '1']));
	}
	
	if(isset($_POST['add_category'])) {
		$pdo->query("INSERT INTO `playground__category`(`name`, `code_name`) VALUES ('{$_POST['name']}', '{$_POST['code']}')");
		exit(json_encode(['status' => '1']));
	}
	
	if(isset($_POST['add_product'])) {
		$dir = $_SERVER['DOCUMENT_ROOT'] . '/files/playground';
		
		$ath = $pdo->query("SELECT `code_name` FROM `playground__category` WHERE `id`='{$_POST['category']}'");
		$ath->setFetchMode(PDO::FETCH_OBJ);
		$patch = $ath->fetch()->code_name . '/' . date("siH") . rand(100, 10000) . '_' . $_FILES['resource']['name'];
		
		ignore_user_abort(true);
		set_time_limit(0);
		
		if(!file_exists($dir . '/' . $ath->fetch()->code_name)) {
			mkdir($dir . '/' . $ath->fetch()->code_name);
		}
		
		if($_POST['resource'] != 'undefined' && 0 < $_FILES['resource']['error']) {
			exit(json_encode(['status' => '2', 'message' => $_FILES['resource']['error']]));
		}
		
		$orgpatch = $dir . '/' . $patch;
		if(!move_uploaded_file($_FILES['resource']['tmp_name'], $orgpatch)) {
			exit(json_encode(['status' => '2', 'message' => 'Ошибка при загрузке файла..']));
		}
		
		if($pdo->query("INSERT INTO `playground__product`(`name`, `price`, `resource`, `executor`, `id_category`) VALUES ('{$_POST['name']}', '{$_POST['price']}', '{$patch}', '{$_POST['executor']}', '{$_POST['category']}')")) {
			exit(json_encode(['status' => '1']));
		}
	}
	
	if(isset($_POST['load_sels_product'])) {
		$sth = $pdo->query("SELECT * FROM `playground__product` WHERE 1 ORDER BY `id` DESC");
		$sth->setFetchMode(PDO::FETCH_OBJ);
		
		$products = "<option disabled selected>- выбрать -</option>";
		
		while($row = $sth->fetch()) {
			$products .= "<option value=\"{$row->id}\">{$row->name}</option>";
		}
		
		exit(json_encode(['status' => '1', 'html' => $products]));
	}
	
	if(isset($_POST['edit_currency'])) {
		$pdo->query("UPDATE `playground` SET `currency`='{$_POST['name']}' WHERE 1 LIMIT 1");
		exit(json_encode(['status' => '1']));
	}
	
	if(isset($_POST['edit_course'])) {
		$pdo->query("UPDATE `playground` SET `course`='{$_POST['course']}' WHERE 1 LIMIT 1");
		exit(json_encode(['status' => '1']));
	}

	if(isset($_POST['edit_secret'])):
		$pdo->query("UPDATE `playground` SET `secret`='{$_POST['secret']}' WHERE 1 LIMIT 1");
		exit(json_encode(['status' => '1']));
	endif;

	if(isset($_POST['edit_limit_product'])):
		$pdo->query("UPDATE `playground` SET `limit_product`='{$_POST['limit_product']}' WHERE 1 LIMIT 1");
		exit(json_encode(['status' => '1']));
	endif;

	if(isset($_POST['edit_bonuses'])):
		if(pdo()->query("UPDATE `playground` SET `bonuses`='{$_POST['value']}' LIMIT 1")):
			exit(json_encode(['alert' => 'success']));
		endif;

		exit(json_encode(['alert' => 'error']));
	endif;
	
	if(isset($_POST['edit_product'])) {
		$pid = clean($_POST['pid'], "int");
		$name = clean($_POST['name']);
		$price = clean($_POST['price'], "int");
		$availability = clean($_POST['availability'], "int");
		
		if(empty($pid) || empty($name) || empty($availability) || empty($price)) {
			result(['alert' => 'warning', 'message' => 'Один из параметров пуст.']);
		}
		
		if(pdo()->prepare("UPDATE `playground__product` SET `name`=:name, `price`=:price, `availability`=:availability WHERE `id`=:pid LIMIT 1")->execute([
			':pid' => $pid,
			':name' => $name,
			':price' => $price,
			':availability' => $availability
		])) {
			result(['alert' => 'success']);
		}
		
		result(['alert' => 'error']);
	}
	
	if(isset($_POST['getRcon'])) {
		result(['content' => Trading::getCommands($_POST['pid'])]);
	}
	
	if(isset($_POST['addRcon'])) {
		$sid = clean($_POST['server'], "int");
		
		if(empty($sid)) {
			result(['alert' => 'warning', 'message' => 'Выберите сервер']);
		}
		
		$command = clean($_POST['command']);
		
		if(empty($command)) {
			result(['alert' => 'warning', 'message' => 'Введите комманду']);
		}
		
		$pid = clean($_POST['pid'], "int");
		
		if(empty($pid)) {
			result(['alert' => 'warning', 'message' => 'Утерян индекс товара']);
		}
		
		if(Trading::addCommand($pid, $sid,  $command)) {
			result(['alert' => 'success', 'content' => Trading::getCommands($pid)]);
		}
	}
	
	if(isset($_POST['removeCommand'])) {
		if(Trading::removeCommand($_POST['id'])) {
			result(['alert' => 'success', 'content' => Trading::getCommands($_POST['pid'])]);
		}
	}