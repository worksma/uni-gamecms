<?php
include_once __DIR__ . '/../inc/start.php';

$AjaxResponse = new AjaxResponse();

if(!isPostRequest() || !isRightToken() || !is_admin()) {
	$AjaxResponse->status(false)->alert('Ошибка')->send();
}

/* Выход админа
=========================================*/
if(isset($_POST['admin_exit'])) {
	$SC->unset_admin_session();
}

/* Редактор страниц
=========================================*/
if(isset($_POST['create_page'])) {
	$class       = check($_POST['class'], "int");
	$privacy     = check($_POST['privacy'], "int");
	$robots      = check($_POST['robots'], "int");
	$active      = check($_POST['active'], "int");
	$url         = check($_POST['url'], null);
	$title       = check($_POST['title'], null);
	$description = check($_POST['description'], null);
	$keywords    = check($_POST['keywords'], null);
	$image       = check($_POST['image'], null);
	$content     = magic_quotes($_POST['content']);

	if(check_for_php($content)) {
		exit('<p class="text-danger">Использование PHP кода в режиме безопасной эксплуатации запрещено, используйте синтаксис шаблонизатора:  https://worksma.ru/wiki/template_syntax</p>');
	}

	if(empty($class) or empty($privacy) or empty($robots) or empty($active) or empty($url) or empty($title) or empty($description) or empty($keywords) or empty($image)) {
		exit('<p class="text-danger">Заполните все поля!</p>');
	}

	if($host == 'test.worksma.ru') {
		exit('<p class="text-danger">Создание страниц в тестовой версии движка запрещено!</p>');
	}

	$url = translit($url);
	if(ValidateNameForUrl($url)) {
		exit('<p class="text-danger">Недопустимые символы в названии страницы!</p>');
	}

	if((($robots != '1') && ($robots != '2')) || (($privacy != '1') && ($privacy != '2')) || (($active != '1') && ($active != '2'))) {
		exit('<p class="text-danger">Недопустимые значения в параметрах страницы!</p>');
	}

	if($class != 1) {
		$STH = $pdo->prepare("SELECT id, name FROM pages__classes WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':id' => $class]);
		$row = $STH->fetch();
		if(empty($row->id)) {
			exit('<p class="text-danger">Категория не найдена!</p>');
		}

		$class_name = $row->name;
		$name       = $class_name . '_' . $url;
		$url        = $class_name . '/' . $url;
	} else {
		$name = $url;
	}

	if($privacy == 1 && $robots == 1) {
		$robots = 2;
	}

	$STH = $pdo->prepare("SELECT id FROM pages WHERE url=:url OR name=:name LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':url' => $url, ':name' => $name]);
	$row = $STH->fetch();
	if(!empty($row->id)) {
		exit('<p class="text-danger">Страница с таким названием или URL уже существует!</p>');
	}

	$STH = $pdo->prepare(
		"INSERT INTO pages (file,url,name,title,description,keywords,kind,image,robots,privacy,type,active,class,page) VALUES (:file, :url, :name, :title, :description, :keywords, :kind, :image, :robots, :privacy, :type, :active, :class, :page)"
	);
	if($STH->execute(
			[
				':file'        => 'modules/pages/index.php',
				':url'         => $url,
				':name'        => $name,
				':title'       => $title,
				':description' => $description,
				':keywords'    => $keywords,
				':kind'        => '1',
				':image'       => $image,
				':robots'      => $robots,
				':privacy'     => $privacy,
				':type'        => '1',
				':active'      => $active,
				':class'       => $class,
				':page'        => '1'
			]
		) == '1') {
		$page_id = get_ai($pdo, "pages");
		$page_id--;

		$STH = $pdo->prepare("INSERT INTO pages__content (page_id,content) VALUES (:page_id, :content)");
		$STH->execute([':page_id' => $page_id, ':content' => $content]);
		write_log("Создана страница " . $url);
		write_sitemap($full_site_host . $url);
		exit('<p class="text-success">Страница успешно создана! Ссылка: <a target="_blank" href="' . $full_site_host . $url . '">' . $full_site_host . $url . '</a></p>');
	} else {
		exit('<p class="text-danger">Ошибка! Страница не создана!</p>');
	}
}
if(isset($_POST['page_edit'])) {
	$class       = check($_POST['class'], "int");
	$privacy     = check($_POST['privacy'], "int");
	$robots      = check($_POST['robots'], "int");
	$active      = check($_POST['active'], "int");
	$url         = check($_POST['url'], null);
	$title       = check($_POST['title'], null);
	$description = check($_POST['description'], null);
	$keywords    = check($_POST['keywords'], null);
	$page_id     = check($_POST['page_id'], "int");
	$content     = magic_quotes($_POST['content']);

	if(check_for_php($content)) {
		exit('<p class="text-danger">Использование PHP кода в режиме безопасной эксплуатации запрещено, используйте синтаксис шаблонизатора:  https://worksma.ru/wiki/template_syntax</p>');
	}

	if(empty($class) or empty($privacy) or empty($robots) or empty($active) or empty($url) or empty($title) or empty($description) or empty($keywords)) {
		exit('<p class="text-danger">Заполните все поля!</p>');
	}

	if($host == 'test.worksma.ru') {
		exit('<p class="text-danger">Создание страниц в тестовой версии движка запрещено!</p>');
	}

	$url = translit($url);
	if(ValidateNameForUrl($url)) {
		exit('<p class="text-danger">Недопустимые символы в названии страницы!</p>');
	}

	if((($robots != '1') && ($robots != '2')) || (($privacy != '1') && ($privacy != '2')) || (($active != '1') && ($active != '2'))) {
		exit('<p class="text-danger">Недопустимые значения в параметрах страницы!</p>');
	}

	if($class != 1) {
		$STH = $pdo->prepare("SELECT id, name FROM pages__classes WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':id' => $class]);
		$row = $STH->fetch();
		if(empty($row->id)) {
			exit('<p class="text-danger">Категория не найдена!</p>');
		}

		$name = $row->name . '_' . $url;
		$url  = $row->name . '/' . $url;
	} else {
		$name = $url;
	}

	if($privacy == 1 && $robots == 1) {
		$robots = 2;
	}

	$STH = $pdo->prepare("SELECT id FROM pages WHERE url=:url OR name=:name LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':url' => $url, ':name' => $name]);
	$row = $STH->fetch();
	if(!empty($row->id) && $row->id != $page_id) {
		exit('<p class="text-danger">Страница с таким названием или URL уже существует!</p>');
	}

	$STH = $pdo->prepare(
		"UPDATE pages SET url=:url, name=:name, title=:title, description=:description, keywords=:keywords, robots=:robots, privacy=:privacy, active=:active, class=:class WHERE id=:id LIMIT 1"
	);
	if($STH->execute(
			[
				':url'         => $url,
				':name'        => $name,
				':title'       => $title,
				':description' => $description,
				':keywords'    => $keywords,
				':robots'      => $robots,
				':privacy'     => $privacy,
				':active'      => $active,
				':class'       => $class,
				':id'          => $page_id
			]
		) == '1') {
		$STH = $pdo->prepare("UPDATE pages__content SET content=:content WHERE page_id=:page_id LIMIT 1");
		$STH->execute([':content' => $content, ':page_id' => $page_id]);

		exit('<p class="text-success">Страница успешно изменена! Ссылка: <a target="_blank" href="' . $full_site_host . $url . '">' . $full_site_host . $url . '</a></p>');
	} else {
		exit('<p class="text-danger">Ошибка! Страница не создана!</p>');
	}
}
if(isset($_POST['load_page_image_2'])) {
	if(empty($_FILES['image']['name'])) {
		exit('<p>Выберите изображение</p>');
	} else {
		if(isset($_POST['id'])) {
			$id = check($_POST['id'], "int");
			if(empty($id)) {
				exit('<p>Неверный ID</p>');
			}

			$STH = $pdo->prepare("SELECT image, name FROM pages WHERE id=:id LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute([':id' => $id]);
			$row = $STH->fetch();

			if(empty($row->image)) {
				exit('<p>Неверный ID</p>');
			}

			if($row->image != 'files/miniatures/standart.jpg') {
				@unlink('../' . $row->image);
			}

			if(if_img($_FILES['image']['name'])) {
				$image = 'files/miniatures/' . $row->name . '.jpg';
				move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image);
				$STH = $pdo->prepare("UPDATE pages SET image=:image WHERE id=:id LIMIT 1");
				$STH->execute([':image' => $image, ':id' => $id]);
			} else {
				exit('<p>Изображение должено быть в формате JPG,GIF или PNG</p>');
			}

			exit('<script>$("#img").attr("src","../' . $image . '?rand=' . rand(0, 10) . '");</script>');
		} else {
			if(if_img($_FILES['image']['name'])) {
				$image = 'files/miniatures/' . time() . '.jpg';
				move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image);
			} else {
				exit('<p>Изображение должено быть в формате JPG,GIF или PNG</p>');
			}

			exit(
				'<script>$("#img").attr("src","../' . $image . '?rand=' . rand(
					0,
					10
				) . '");$("#input_image").val("' . $image . '");</script>'
			);
		}
	}
}
if(isset($_POST['load_pages'])) {
	$type = check($_POST['type'], "int");
	$i    = 1;

	if($type == 1) {
		$STH = $pdo->query(
			"SELECT id, title, type, url, description, keywords, kind, image, robots, privacy, active FROM pages WHERE page='0' ORDER BY id"
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);
		while($row = $STH->fetch()) {
			?>
			<tr>
				<td>
					<input class="form-control input-xs" id="title<?php echo $row->id ?>" type="text" value="<?php echo $row->title ?>" maxlength="80">
				</td>
				<td>
					<input class="form-control input-xs" id="description<?php echo $row->id ?>" type="text" value="<?php echo $row->description ?>" maxlength="150">
				</td>
				<td>
					<input class="form-control input-xs" id="keywords<?php echo $row->id ?>" type="text" value="<?php echo $row->keywords ?>" maxlength="150">
				</td>
				<td>
					<select class="form-control input-xs w-100px" id="kind<?php echo $row->id ?>" <?php if($row->type == '2' || $row->kind == '0') {
						echo "disabled";
					} ?>>
						<option value="1" <?php if($row->kind == '1') {
							echo "selected";
						} ?>>Системная
						</option>
						<option value="2" <?php if($row->kind == '2') {
							echo "selected";
						} ?>>Статья
						</option>
						<option value="3" <?php if($row->kind == '3') {
							echo "selected";
						} ?>>Профиль
						</option>
					</select>
				</td>
				<td>
					<a id="img_thumbnail<?php echo $row->id ?>" href="../<?php echo $row->image ?>" class="thumbnail" data-lightbox="<?php echo $row->id ?>">
						<img id="img<?php echo $row->id ?>" src="../<?php echo $row->image ?>" width="80px" alt="Миниатюра" class="thumbnail-img">
					</a>
					<form enctype="multipart/form-data" action="ajax/actions_panel.php" method="POST" id="image<?php echo $row->id ?>">
						<input type="hidden" name="token" value="<?php echo $token ?>">
						<input type="hidden" name="id" value="<?php echo $row->id ?>">
						<input type="hidden" name="load_page_image" value="1">
						<input type="hidden" name="phpaction" value="1">
						<input type="file" class="form-control input-xs" accept="image/*" name="image" id="image_input<?php echo $row->id ?>">
					</form>
					<script>
                      $('#image_input<?php echo $row->id ?>').on('change', function () {
                        if ($(this).val()) {
                          load_page_image(<?php echo $row->id ?>);
                        }
                      });
					</script>
				</td>
				<td>
					<select class="form-control input-xs w-100px" id="robots<?php echo $row->id ?>" <?php if($row->type == '2' || $row->privacy == '0') {
						echo "disabled";
					} ?>>
						<option value="1" <?php if($row->robots == '1') {
							echo "selected";
						} ?>>Включить
						</option>
						<option value="2" <?php if($row->robots == '2') {
							echo "selected";
						} ?>>Выключить
						</option>
					</select>
				</td>
				<td>
					<select class="form-control input-xs w-150px" id="privacy<?php echo $row->id ?>" <?php if($row->type == '2' || $row->privacy == '0') {
						echo "disabled";
					} ?>>
						<option value="1" <?php if($row->privacy == '1') {
							echo "selected";
						} ?>>Для авторизованных
						</option>
						<option value="2" <?php if($row->privacy == '2') {
							echo "selected";
						} ?>>Для всех
						</option>
					</select>
				</td>
				<td>
					<select class="form-control input-xs w-100px" id="active<?php echo $row->id ?>" <?php if($row->type == '2') {
						echo "disabled";
					} ?>>
						<option value="1" <?php if($row->active == '1') {
							echo "selected";
						} ?>>Включена
						</option>
						<option value="2" <?php if($row->active == '2') {
							echo "selected";
						} ?>>Выключена
						</option>
					</select>
				</td>
				<td>
					<div class="btn-group-vertical w-100">
						<a class="c-333" onclick="save_page(<?php echo $row->id ?>);">
							<div class="btn btn-default btn-sm w-100">
								<span class="glyphicon glyphicon-pencil"></span> Сохранить
							</div>
						</a>
						<div class="clearfix mt-10"></div>
						<a class="c-333" target="_blank" href="<?php echo $full_site_host . $row->url ?>">
							<div class="btn btn-default btn-sm w-100">
								<span class="glyphicon glyphicon-upload"></span> Перейти
							</div>
						</a>
					</div>
				</td>
			</tr>
			<?php
			$i++;
		}
	} else {
		$STH = $pdo->query(
			"SELECT pages.id, pages.title, pages.url, pages.description, pages.keywords, pages.image, pages.robots, pages.privacy, pages.active, pages.class, pages__classes.name AS class_name FROM pages LEFT JOIN pages__classes ON pages.class = pages__classes.id WHERE pages.page='1'"
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);
		while($row = $STH->fetch()) {
			?>
			<tr>
				<td><a href="../<?php echo $row->url; ?>" target="_blank"><?php echo $row->title; ?></a></td>
				<td><?php if($row->robots == 1) {
						echo "Индексируется";
					} else {
						echo "Не индексируется";
					} ?></td>
				<td><?php if($row->privacy == 1) {
						echo "Только авторизованным";
					} else {
						echo "Всем пользователям";
					} ?></td>
				<td><?php if($row->active == 1) {
						echo "Включена";
					} else {
						echo "<b>Выключена</b>";
					} ?></td>
				<td><?php if($row->class_name == '') {
						echo "Начальная";
					} else {
						echo $row->class_name;
					} ?></td>
				<td>
					<a href="../<?php echo $row->image; ?>" class="thumbnail" data-lightbox="<?php echo $row->id; ?>">
						<img src="../<?php echo $row->image; ?>" alt="Миниатюра" class="thumbnail-img" width="80px">
					</a>
				</td>
				<td>
					<div class="btn-group-vertical w-100">
						<a target="_blank" href="../admin/page_edit?id=<?php echo $row->id ?>">
							<div class="btn btn-default btn-sm w-100">
								<span class="glyphicon glyphicon-pencil"></span> Изменить
							</div>
						</a>
						<div class="clearfix mt-10"></div>
						<a class="c-333" onclick="dell_page(<?php echo $row->id ?>);">
							<div class="btn btn-default btn-sm w-100">
								<span class="glyphicon glyphicon-trash"></span> Удалить
							</div>
						</a>
					</div>
				</td>
			</tr>
			<?php
			$i++;
		}
		if($i == 1) {
			?>
			<tr>
				<td colspan="10">
					Страниц нет
				</td>
			</tr>
			<?php
		}
	}

	exit();
}
if(isset($_POST['save_page'])) {
	$id          = check($_POST['id'], "int");
	$title       = check($_POST['title'], null);
	$description = check($_POST['description'], null);
	$keywords    = check($_POST['keywords'], null);
	$kind        = check($_POST['kind'], "int");
	$robots      = check($_POST['robots'], "int");
	$privacy     = check($_POST['privacy'], "int");
	$active      = check($_POST['active'], "int");

	if(empty($id)) {
		exit(json_encode(['status' => '2']));
	}

	$STH = $pdo->prepare(
		"SELECT id, kind, image, robots, privacy, active, type FROM pages WHERE id=:id AND page='0' LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $id]);
	$row = $STH->fetch();
	if(empty($row->id)) {
		exit(json_encode(['status' => '2']));
	}
	if($row->type == 2) {
		$robots  = 0;
		$privacy = 0;
		$active  = 1;
		$kind    = 1;
	}
	if($row->privacy == 0) {
		$privacy = 0;
	}
	if($privacy == 0 || $privacy == 1) {
		$robots = 0;
	}
	if($privacy == 1) {
		$robots = 2;
	}
	if(empty($title)) {
		exit(json_encode(['status' => '2', 'data' => 'Заполните "Заголовок"']));
	}
	if(mb_strlen($title, 'UTF-8') > 80) {
		exit(json_encode(['status' => '2', 'data' => 'Заголовок должен состоять не более чем из 80 символов.']));
	}
	if(empty($description)) {
		exit(json_encode(['status' => '2', 'data' => 'Заполните "Описание"']));
	}
	if(mb_strlen($description, 'UTF-8') > 150) {
		exit(json_encode(['status' => '2', 'data' => 'Описание должно состоять не более чем из 150 символов.']));
	}
	if(empty($keywords)) {
		exit(json_encode(['status' => '2', 'data' => 'Заполните "Ключевые слова"']));
	}
	if(mb_strlen($keywords, 'UTF-8') > 150) {
		exit(json_encode(['status' => '2', 'data' => 'Ключевые слова должены состоять не более чем из 150 символов.']));
	}

	$STH = $pdo->prepare(
		"UPDATE pages SET title=:title, description=:description, keywords=:keywords, kind=:kind, robots=:robots, privacy=:privacy, active=:active WHERE id=:id LIMIT 1"
	);
	$STH->execute(
		[
			':title'       => $title,
			':description' => $description,
			':keywords'    => $keywords,
			':kind'        => $kind,
			':robots'      => $robots,
			':privacy'     => $privacy,
			':active'      => $active,
			':id'          => $id
		]
	);

	exit(json_encode(['status' => '1']));
}
if(isset($_POST['load_page_image'])) {
	if(empty($_FILES['image']['name'])) {
		exit('alert("Выберите изображение");');
	} else {
		$id = check($_POST['id'], "int");
		if(empty($id)) {
			exit('alert("Неверный ID");');
		}

		$STH = $pdo->prepare("SELECT image, name FROM pages WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':id' => $id]);
		$row = $STH->fetch();

		if(if_img($_FILES['image']['name'])) {
			$image = 'files/miniatures/' . $row->name . ".jpg";
			move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image);
			$STH = $pdo->prepare("UPDATE pages SET image=:image WHERE id=:id LIMIT 1");
			$STH->execute([':image' => $image, ':id' => $id]);

			exit(
				'$("#img_thumbnail' . $id . '").attr("href","../' . $image . '?rand=' . rand(
					0,
					100
				) . '");$("#img' . $id . '").attr("src","../' . $image . '?rand=' . rand(0, 100) . '");'
			);
		} else {
			exit('alert("Изображение должено быть в формате JPG,GIF или PNG");');
		}
	}
}
if(isset($_POST['dell_page'])) {
	$number = check($_POST['number'], null);

	if(empty($number)) {
		exit();
	}

	$pdo->exec("DELETE FROM `pages` WHERE `id`='$number' AND `page`='1' LIMIT 1");
	$pdo->exec("DELETE FROM `pages__content` WHERE `page_id`='$number' LIMIT 1");
	write_log("Удалена страница " . $number);
	exit();
}
if(isset($_POST['add_class'])) {
	$class_name = check($_POST['class_name'], null);
	$type       = check($_POST['type'], "int");
	switch($type) {
		case '1':
			$name = 'new';
			break;
		case '2':
			$name = 'page';
			break;
		default:
			exit(json_encode(['status' => '2', 'reply' => 'Неверный тип!']));
			break;
	}
	if(empty($class_name)) {
		exit (json_encode(['status' => '2', 'reply' => 'Заполните поле!']));
	}
	if($name == 'page') {
		$STH = $pdo->prepare("SELECT id FROM pages WHERE url=:url AND page = '0' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':url' => $class_name]);
		$row = $STH->fetch();
		if(isset($row->id)) {
			exit(json_encode(['status' => '2', 'reply' => 'Данное название зарезервировано системой!']));
		}
	}
	$STH = $pdo->query("SELECT id FROM " . $name . "s__classes WHERE name='$class_name' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(!empty($row->id)) {
		exit (json_encode(['status' => '2', 'reply' => 'Такая категория уже существует!']));
	}

	$STH = $pdo->prepare("INSERT INTO " . $name . "s__classes (name) values (:name)");
	if($STH->execute(['name' => $class_name]) == '1') {
		exit (json_encode(['status' => '1']));
	} else {
		exit (json_encode(['status' => '2', 'reply' => 'Произошла ошибка!']));
	}
}
if(isset($_POST['load_classes'])) {
	$type = check($_POST['type'], "int");
	switch($type) {
		case '1':
			$name = 'new';
			break;
		case '2':
			$name = 'page';
			break;
		default:
			exit (json_encode(['data' => 'none']));
			break;
	}

	$data = '';
	$STH  = $pdo->query('SELECT id,name FROM ' . $name . 's__classes');
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		if($row->name == '') {
			$row->name = $messages['Initial'];
		}
		$data .= '
		<div class="input-group" id="' . $row->id . '">
			<span class="input-group-btn">
				<button class="btn btn-default" type="button" onclick="change_class(' . $row->id . ', ' . $type . ');">Изменить</button>
				<button class="btn btn-default" type="button" onclick="dell_class(' . $row->id . ', ' . $type . ');">Удалить</button>
			</span>
			<input type="text" class="form-control" id="input' . $row->id . '" maxlength="255" autocomplete="off" value="' . $row->name . '">
		</div>
		';
	}

	exit (json_encode(['data' => $data]));
}
if(isset($_POST['dell_class'])) {
	$id   = checkJs($_POST['id'], "int");
	$type = check($_POST['type'], "int");
	switch($type) {
		case '1':
			$name = 'new';
			break;
		case '2':
			$name = 'page';
			break;
		default:
			exit(json_encode(['status' => '2', 'reply' => 'Неверный тип!']));
			break;
	}
	if(empty($id)) {
		exit(json_encode(['status' => '2', 'reply' => 'Пустой идентификатор!']));
	}

	if($name == 'new') {
		$STH = $pdo->query("SELECT id FROM news WHERE class='$id'");
		$STH->execute();
		$data       = $STH->fetchAll();
		$data_count = count($data);

		if($data_count != 0) {
			for($i = 0; $i < $data_count; $i++) {
				$val = $data[$i]['id'];
				$pdo->exec("DELETE FROM news__comments WHERE new_id='$val'");
			}
		}
	}
	if($name == 'page') {
		$STH = $pdo->prepare("SELECT name FROM pages__classes WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':id' => $id]);
		$row = $STH->fetch();
		if($row->name == '') {
			exit(json_encode(['status' => '2', 'reply' => 'Удаление данной категории невозможно!']));
		}
	}
	$pdo->exec("DELETE FROM " . $name . "s WHERE class='$id'");
	$pdo->exec("DELETE FROM " . $name . "s__classes WHERE id='$id' LIMIT 1");
	exit(json_encode(['status' => '1']));
}
if(isset($_POST['change_class'])) {
	$id         = check($_POST['id'], "int");
	$class_name = check($_POST['class_name'], null);
	$type       = check($_POST['type'], "int");
	switch($type) {
		case '1':
			$name = 'new';
			break;
		case '2':
			$name = 'page';
			break;
		default:
			exit(json_encode(['status' => '2', 'reply' => 'Неверный тип!']));
			break;
	}
	if(empty($id)) {
		exit(json_encode(['status' => '2', 'reply' => 'Пустой идентификатор!']));
	}
	if(empty($class_name)) {
		exit(json_encode(['status' => '2', 'reply' => 'Заполните поле!']));
	}
	if($name == 'page') {
		$STH = $pdo->prepare("SELECT id FROM pages WHERE url=:url AND page = '0' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':url' => $class_name]);
		$row = $STH->fetch();
		if(isset($row->id)) {
			exit(json_encode(['status' => '2', 'reply' => 'Данное название зарезервировано системой!']));
		}

		$STH = $pdo->prepare("SELECT name FROM pages__classes WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':id' => $id]);
		$row = $STH->fetch();
		if(empty($row->name)) {
			exit(json_encode(['status' => '2', 'reply' => 'Категория не найдена!']));
		}
		$old_name = $row->name;
		if($old_name == '') {
			exit(json_encode(['status' => '2', 'reply' => 'Редактирование данной категории невозможно!']));
		}

		$STH = $pdo->prepare("SELECT id, name, url FROM pages WHERE class=:class");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':class' => $id]);
		while($row = $STH->fetch()) {
			$STH2 = $pdo->prepare("UPDATE pages SET name=:name, url=:url WHERE id=:id LIMIT 1");
			$STH2->execute(
				[
					':name' => str_replace_once($old_name, $class_name, $row->name),
					':url'  => str_replace_once($old_name, $class_name, $row->url),
					':id'   => $row->id
				]
			);
		}
	}

	$STH = $pdo->query("SELECT `id` FROM " . $name . "s__classes WHERE `name`='$class_name' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(!empty($row->id) && $row->id != $id) {
		exit (json_encode(['status' => '2', 'reply' => 'Такая категория уже существует!']));
	}

	$STH = $pdo->prepare("UPDATE " . $name . "s__classes SET name=:name WHERE id='$id' LIMIT 1");
	$STH->execute([':name' => $class_name]);
	exit(json_encode(['status' => '1']));
}
/* Настройки сайта
=========================================*/
if(isset($_POST['edit_site_name'])) {
	$site_name = check($_POST['site_name'], null);

	if(empty($site_name)) {
		exit('<p class="text-danger">Вы не указали название!</p>');
	}

	$STH = $pdo->prepare("UPDATE config SET name=:site_name LIMIT 1");
	$STH->execute(array(':site_name' => $site_name));

	write_log("Название сайта изменено на ".$site_name);
	exit('<p class="text-success">Название сайта изменено!</p>');
}
if(isset($_POST['edit_ip_protect'])) {
	$type = check($_POST['type'], "int");

	if($type != 1 && $type != 2) {
		exit();
	}

	$STH = $pdo->prepare("UPDATE config SET ip_protect=:ip_protect LIMIT 1");
	$STH->execute(array(':ip_protect' => $type));

	if($type == 1) {
		$SC->admin_ip = get_ip();
		write_log("Включена защита сессий по ip");
	} else {
		$SC->admin_ip = '';
		write_log("Выключена защита сессий по ip");
	}
	$_SESSION['admin_cache'] = $SC->get_admin_cache($conf->password);
	exit();
}
if(isset($_POST['edit_site_time_zone'])) {
	$time_zone = check($_POST['time_zone'], null);

	if(empty($time_zone)) {
		exit('<p class="text-danger">Вы не указали часовой пояс!</p>');
	}

	$STH = $pdo->prepare("UPDATE config SET time_zone=:time_zone LIMIT 1");
	$STH->execute(array(':time_zone' => $time_zone));

	write_log("Часовой пояс сайта изменен на ".$time_zone);
	exit('<p class="text-success">Часовой пояс сайта изменен!</p>');
}
if(isset($_POST['edit_copyright_key'])) {
	$copyright_key = check($_POST['copyright_key'], null);

	if(empty($copyright_key)) {
		exit('<p class="text-danger mb-0">Вы не указали ключ!</p>');
	}

	$STH = $pdo->prepare("UPDATE config SET copyright_key=:copyright_key LIMIT 1");
	$STH->execute(array(':copyright_key' => $copyright_key));

	exit('<p class="text-success mb-0">Изменено!</p>');
}
if(isset($_POST['edit_violations_number'])) {
	$violations_number = check($_POST['violations_number'], 'int');

	if(empty($violations_number)) {
		exit('<p class="text-danger mb-0">Укажите число!</p>');
	}

	$STH = $pdo->prepare("UPDATE config SET violations_number=:violations_number LIMIT 1");
	$STH->execute(array(':violations_number' => $violations_number));

	write_log("Количество разрешаемых нарушений изменено на ".$violations_number);
	exit('<p class="text-success mb-0">Изменено!</p>');
}
if(isset($_POST['edit_col_pass'])) {
	$col_pass = check($_POST['col_pass'], 'int');

	if(empty($col_pass)) {
		$col_pass = 0;
	}

	if($col_pass > 999) {
		exit('<p class="text-danger">Укажите число не более 999!</p>');
	}

	$STH = $pdo->prepare("UPDATE config SET col_pass=:col_pass LIMIT 1");
	$STH->execute(array(':col_pass' => $col_pass));

	write_log("Смена пароля разрешена раз в ".$col_pass." сут");
	exit('<p class="text-success">Изменено!</p>');
}
if(isset($_POST['edit_col_type'])) {
	$col_type = check($_POST['col_type'], 'int');

	if(empty($col_type)) {
		$col_type = 0;
	}

	if($col_type > 999) {
		exit('<p class="text-danger">Укажите число не более 999!</p>');
	}

	$STH = $pdo->prepare("UPDATE config SET col_type=:col_type LIMIT 1");
	$STH->execute(array(':col_type' => $col_type));

	write_log("Смена типа разрешена раз в ".$col_type." сут");
	exit('<p class="text-success">Изменено!</p>');
}
if(isset($_POST['edit_col_nick'])) {
	$col_nick = check($_POST['col_nick'], 'int');

	if(empty($col_nick)) {
		$col_nick = 0;
	}

	if($col_nick > 999) {
		exit('<p class="text-danger">Укажите число не более 999!</p>');
	}

	$STH = $pdo->prepare("UPDATE config SET col_nick=:col_nick LIMIT 1");
	$STH->execute(array(':col_nick' => $col_nick));

	write_log("Смена ника разрешена раз в ".$col_nick." сут");
	exit('<p class="text-success">Изменено!</p>');
}
if(isset($_POST['edit_col_login'])) {
	$col_login = check($_POST['col_login'], 'int');

	if(empty($col_login)) {
		exit('<p class="text-danger">Укажите число!</p>');
	}

	if($col_login > 999) {
		exit('<p class="text-danger">Укажите числое более 999!</p>');
	}

	$STH = $pdo->prepare("UPDATE config__secondary SET col_login=:col_login LIMIT 1");
	$STH->execute(array(':col_login' => $col_login));

	write_log("Смена логина разрешена раз в ".$col_login." сут");
	exit('<p class="text-success">Изменено!</p>');
}
if(isset($_POST['edit_violations_delta'])) {
	$violations_delta = check($_POST['violations_delta'], 'int');

	if(empty($violations_delta)) {
		exit('<p class="text-danger mb-0">Укажите число!</p>');
	}

	$STH = $pdo->prepare("UPDATE config SET violations_delta=:violations_delta LIMIT 1");
	$STH->execute(array(':violations_delta' => $violations_delta));

	write_log("violations_delta изменено на ".$violations_delta);
	exit('<p class="text-success mb-0">Изменено!</p>');
}
if(isset($_POST['switch_widgets_type'])) {
	$type = check($_POST['type'], "int");

	if($type != 1 && $type != 2) {
		$type = 1;
	}

	$STH = $pdo->prepare("UPDATE config SET widgets_type=:widgets_type, vk_group=:vk_group, vk_admin=:vk_admin LIMIT 1");
	$STH->execute(array(':widgets_type' => $type, ':vk_group' => '2', ':vk_admin' => '2'));

	exit(json_encode(array('status' => '1')));
}
if(isset($_POST['switch_widget'])) {
	$type  = check($_POST['type'], "int");
	$input = check($_POST['input'], "int");

	if($type != 1 && $type != 2) {
		$type = 2;
	}
	if($input == 1) {
		$input = 'vk_group';
	} else {
		$input = 'vk_admin';
	}

	if($type == 1) {
		$STH = $pdo->query("SELECT `widgets_type` FROM `config` LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if($row->widgets_type == 1) {
			$STH = $pdo->query("SELECT vk_api, vk_id, vk_key, vk_service_key FROM config__secondary LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$row = $STH->fetch();
			if($row->vk_api == 2) {
				exit(json_encode(array('status' => '2', 'data' => 'Для работы виджета требуется включить регистрацию через Вконтакте')));
			}
			if(empty($row->vk_id) || empty($row->vk_key) || empty($row->vk_service_key)) {
				exit(json_encode(array('status' => '2', 'data' => 'Для работы виджета требуется заполнить все настройки для авторизации через Вконтакте')));
			}
		} else {
			$STH = $pdo->query("SELECT fb_api, fb_id, fb_key FROM config__secondary LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$row = $STH->fetch();
			if($row->fb_api == 2) {
				exit(json_encode(array('status' => '2', 'data' => 'Для работы виджета требуется включить регистрацию через Facebook')));
			}
			if(empty($row->fb_id) || empty($row->fb_key)) {
				exit(json_encode(array('status' => '2', 'data' => 'Для работы виджета требуется заполнить все настройки для авторизации через Facebook')));
			}
		}
	}

	$STH = $pdo->prepare("UPDATE config SET $input=:type LIMIT 1");
	$STH->execute(array(':type' => $type));

	exit(json_encode(array('status' => '1')));
}
if(isset($_POST['edit_vk_group_id'])) {
	$STH = $pdo->query("SELECT widgets_type FROM config LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if($row->widgets_type == 1) {
		$vk_group_id = explode(",", $_POST['vk_group_id']);
		if(is_array($vk_group_id)) {
			for($i = 0; $i < count($vk_group_id); $i++) {
				$temp = check($vk_group_id[$i], 'int');
				if(empty($temp)) {
					exit('<p class="text-danger">Укажите числовой id!</p>');
				}
			}
		}
	}

	$vk_group_id = check($_POST['vk_group_id'], null);

	$STH = $pdo->prepare("UPDATE config SET vk_group_id=:vk_group_id LIMIT 1");
	$STH->execute(array(':vk_group_id' => $vk_group_id));

	write_log("vk_group_id изменено на ".$vk_group_id);
	exit('<p class="text-success">Изменено!</p>');
}
if(isset($_POST['edit_vk_admin_id'])) {
	$vk_admin_id = explode(",", $_POST['vk_admin_id']);
	if(is_array($vk_admin_id)) {
		for($i = 0; $i < count($vk_admin_id); $i++) {
			$temp = check($vk_admin_id[$i], 'int');
			if(empty($temp)) {
				exit('<p class="text-danger">Укажите числовой id!</p>');
			}
		}
	}
	$vk_admin_id = check($_POST['vk_admin_id'], null);

	$STH = $pdo->prepare("UPDATE config SET vk_admin_id=:vk_admin_id LIMIT 1");
	$STH->execute(array(':vk_admin_id' => $vk_admin_id));

	write_log("vk_admin_id изменено на ".$vk_admin_id);
	exit('<p class="text-success">Изменено!</p>');
}
if(isset($_POST['edit_admins_ids'])) {
	$admins_ids = explode(",", $_POST['admins_ids']);
	if(is_array($admins_ids)) {
		for($i = 0; $i < count($admins_ids); $i++) {
			$admin_id = check($admins_ids[$i], 'int');
			if(empty($admin_id)) {
				exit('<p class="text-danger">Укажите числа через запятую без пробелов!</p>');
			} else {
				$STH = $pdo->prepare("SELECT id FROM users WHERE id=:id LIMIT 1");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array(':id' => $admin_id));
				$row = $STH->fetch();
				if(empty($row->id)) {
					exit('<p class="text-danger">Пользователя с ID:'.$admin_id.' не существует!</p>');
				}
			}
		}
	}
	$admins_ids = check($_POST['admins_ids'], null);

	$STH = $pdo->prepare("UPDATE config__secondary SET admins_ids=:admins_ids LIMIT 1");
	$STH->execute(array(':admins_ids' => $admins_ids));

	write_log("admins_ids изменено на ".$admins_ids);
	exit('<p class="text-success">Изменено!</p>');
}
if(isset($_POST['edit_off_message'])) {
	$off_message = check($_POST['off_message'], null);

	if(empty($off_message)) {
		exit('<p class="text-danger">Укажите сообщение для пользователей!</p>');
	}

	$STH = $pdo->prepare("UPDATE config__secondary SET off_message=:off_message LIMIT 1");
	$STH->execute(array(':off_message' => $off_message));

	write_log("off_message изменено на ".$off_message);
	exit('<p class="text-success">Изменено!</p>');
}
if(isset($_POST['edit_ban_time'])) {
	$ban_time = check($_POST['ban_time'], 'int');

	if(empty($ban_time)) {
		exit('<p class="text-danger mb-0">Укажите число!</p>');
	}

	$STH = $pdo->prepare("UPDATE config SET ban_time=:ban_time LIMIT 1");
	$STH->execute(array(':ban_time' => $ban_time));

	write_log("ban_time изменено на ".$ban_time);
	exit('<p class="text-success mb-0">Изменено!</p>');
}
if(isset($_POST['edit_show_news'])) {
	$show_news = check($_POST['show_news'], 'int');

	if(empty($show_news)) {
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->prepare("UPDATE config SET show_news=:show_news LIMIT 1");
	$STH->execute(array(':show_news' => $show_news));

	write_log("show_news изменено на ".$show_news);
	exit(json_encode(array('status' => '1')));
}
if(isset($_POST['edit_show_events'])) {
	$show_events = check($_POST['show_events'], 'int');

	if(empty($show_events)) {
		exit(json_encode(array('status' => '2')));
	}

	$STH = $pdo->prepare("UPDATE config SET show_events=:show_events LIMIT 1");
	$STH->execute(array(':show_events' => $show_events));

	write_log("show_events изменено на ".$show_events);
	exit(json_encode(array('status' => '1')));
}
if(isset($_POST['editTopDonatorsWidget'])) {
	$showSum = check($_POST['showSum'], 'int');
	$showCount = check($_POST['showCount'], 'int');

	if($showCount < 1 || $showCount > 99) {
		exit(json_encode(['status' => 2]));
	}

	if($showSum != 1 && $showSum != 2) {
		exit(json_encode(['status' => 2]));
	}

	$STH = $pdo->prepare("UPDATE config SET top_donators_count=:top_donators_count, top_donators_show_sum=:top_donators_show_sum LIMIT 1");
	$STH->execute([':top_donators_count' => $showCount, ':top_donators_show_sum' => $showSum]);

	write_log("Параметры виджета Топ донатеры изменены");

	exit(json_encode(['status' => 1]));
}
if(isset($_POST['edit_site_password'])) {
	$old_password = check($_POST['old_password'], null);
	$password     = check($_POST['password'], null);
	$password2    = check($_POST['password2'], null);

	if($host == 'test.worksma.ru') {
		exit('<p class="text-danger">Редактирование данных настроек в тестовой версии движка запрещено!</p>');
	}

	if(empty($old_password) or empty($password) or empty($password2)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}

	$U = new Users($pdo);

	$old_password = $U->convert_password($old_password, $conf->salt);

	if($old_password != $conf->password) {
		exit('<p class="text-danger">Неверно введен текущий пароль!</p>');
	}

	if($password != $password2) {
		exit('<p class="text-danger">Новые пароли не совпадают!</p>');
	}

	$password = $U->convert_password($password, $conf->salt);

	$STH = $pdo->prepare("UPDATE config SET password=:password LIMIT 1");
	$STH->execute(array(':password' => $password));

	if($conf->ip_protect == 1) {
		$SC->admin_ip = get_ip();
	}
	$_SESSION['admin_cache'] = $SC->get_admin_cache($password);

	write_log("Изменен пароль от админ центра");

	exit('<p class="text-success">Пароль успешно изменен!</p>');
}
if(isset($_POST['edit_paginator'])) {
	$users_lim      = check($_POST['users_lim'], "int");
	$bans_lim       = check($_POST['bans_lim'], "int");
	$bans_lim2      = check($_POST['bans_lim2'], "int");
	$muts_lim       = check($_POST['muts_lim'], "int");
	$news_lim       = check($_POST['news_lim'], "int");
	$stats_lim      = check($_POST['stats_lim'], "int");
	$complaints_lim = check($_POST['complaints_lim'], "int");

	if(
		empty($users_lim)
		|| empty($bans_lim)
		|| empty($bans_lim2)
		|| empty($muts_lim)
		|| empty($news_lim)
		|| empty($stats_lim)
		|| empty($complaints_lim)
	) {
		exit(json_encode(['data' => '<p class="text-danger">Вы заполнили не все поля!</p>']));
	}

	$STH = $pdo->prepare(
		"UPDATE config__secondary SET users_lim=:users_lim,bans_lim=:bans_lim,bans_lim2=:bans_lim2,muts_lim=:muts_lim,news_lim=:news_lim,stats_lim=:stats_lim,complaints_lim=:complaints_lim LIMIT 1"
	);
	$STH->execute(
		[
			':users_lim'      => $users_lim,
			':bans_lim'       => $bans_lim,
			':bans_lim2'      => $bans_lim2,
			':muts_lim'       => $muts_lim,
			':news_lim'       => $news_lim,
			':stats_lim'      => $stats_lim,
			':complaints_lim' => $complaints_lim
		]
	);

	exit(json_encode(['data' => '<p class="text-success">Настройки успешно изменены!</p>']));
}
if(isset($_POST['edit_vk_api'])) {
	$vk_id          = check($_POST['vk_id'], null);
	$vk_key         = check($_POST['vk_key'], null);
	$vk_service_key = check($_POST['vk_service_key'], null);

	if(empty($vk_id) or empty($vk_key) or empty($vk_service_key)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}

	$STH = $pdo->prepare("UPDATE config__secondary SET vk_id=:vk_id,vk_key=:vk_key,vk_service_key=:vk_service_key LIMIT 1");
	$STH->execute(array(':vk_id' => $vk_id, ':vk_key' => $vk_key, ':vk_service_key' => $vk_service_key));
	exit('<p class="text-success">Настройки успешно изменены!</p>');
}
if(isset($_POST['edit_steam_api'])) {
	$steam_key = check($_POST['steam_key'], null);

	if(empty($steam_key)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}
	$STH = $pdo->prepare("UPDATE config__secondary SET steam_key=:steam_key LIMIT 1");
	$STH->execute(array(':steam_key' => $steam_key));
	exit('<p class="text-success">Настройки успешно изменены!</p>');
}
if(isset($_POST['edit_fb_api'])) {
	$fb_id  = check($_POST['fb_id'], null);
	$fb_key = check($_POST['fb_key'], null);

	if(empty($fb_id) or empty($fb_key)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}

	$STH = $pdo->prepare("UPDATE config__secondary SET fb_id=:fb_id,fb_key=:fb_key LIMIT 1");
	$STH->execute(array(':fb_id' => $fb_id, ':fb_key' => $fb_key));
	exit('<p class="text-success">Настройки успешно изменены!</p>');
}

/*
	AmaraPay
*/
if(isset($_POST['edit_amarapay'])):
	$shop_id = check($_POST['amarapay_id'], null);
	$key_public = check($_POST['amarapay_public'], null);
	$key_secret= check($_POST['amarapay_secret'], null);
	
	if(empty($shop_id) || empty($key_public) || empty($key_secret)):
		exit(json_encode([
			'alert' => 'danger',
			'message' => 'Заполните все поля!'
		]));
	endif;
	
	$sth = $pdo->prepare("UPDATE `config__bank` SET `amarapay_id`=:amarapay_id, `amarapay_public`=:amarapay_public, `amarapay_secret`=:amarapay_secret LIMIT 1");
	$sth->execute([
		':amarapay_id' => $shop_id,
		':amarapay_public' => $key_public,
		':amarapay_secret' => $key_secret
	]);
	
	write_log("Отредактирована AmaraPay");
	exit(json_encode([
		'alert' => 'success',
		'message' => 'Настройки изменены!'
	]));
endif;

/*
	FreeKassa
*/
if(isset($_POST['edit_freekassa_new'])):
	$shop_id = check($_POST['freekassa_id'], null);
	$key_secret1 = check($_POST['freekassa_secret1'], null);
	$key_secret2 = check($_POST['freekassa_secret2'], null);
	
	if(empty($shop_id) || empty($key_secret1) || empty($key_secret2)):
		exit(json_encode([
			'alert' => 'danger',
			'message' => 'Заполните все поля!'
		]));
	endif;
	
	$sth = $pdo->prepare("UPDATE `config__bank` SET `freekassa_id`=:freekassa_id, `freekassa_secret1`=:freekassa_secret1, `freekassa_secret2`=:freekassa_secret2 LIMIT 1");
	$sth->execute([
		':freekassa_id' => $shop_id,
		':freekassa_secret1' => $key_secret1,
		':freekassa_secret2' => $key_secret2
	]);
	
	write_log("Отредактирована FreeKassa (NEW)");
	exit(json_encode([
		'alert' => 'success',
		'message' => 'Настройки изменены!'
	]));
endif;

if(isset($_POST['edit_freekassa'])) {
	$fk_login = check($_POST['fk_login'], 'int');
	$fk_pass1 = check($_POST['fk_pass1'], null);
	$fk_pass2 = check($_POST['fk_pass2'], null);
	$type = check($_POST['type'], null);

	if(empty($fk_login) or empty($fk_pass1) or empty($fk_pass2)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}

	if($type == 'new') {
		$STH = $pdo->prepare("UPDATE config__bank SET fk_new_login=:fk_login,fk_new_pass1=:fk_pass1,fk_new_pass2=:fk_pass2 LIMIT 1");
		write_log("Отредактирована freekassa new");
	} else {
		$STH = $pdo->prepare("UPDATE config__bank SET fk_login=:fk_login,fk_pass1=:fk_pass1,fk_pass2=:fk_pass2 LIMIT 1");
		write_log("Отредактирована freekassa");
	}

	$STH->execute([':fk_login' => $fk_login, ':fk_pass1' => $fk_pass1, ':fk_pass2' => $fk_pass2]);

	exit('<p class="text-success">Настройки изменены!</p>');
}

if(isset($_POST['edit_interkassa'])) {
	$ik_login = check($_POST['ik_login'], null);
	$ik_pass1 = check($_POST['ik_pass1'], null);

	if(empty($ik_login) or empty($ik_pass1)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}

	$STH = $pdo->prepare("UPDATE config__bank SET ik_login=:ik_login,ik_pass1=:ik_pass1 LIMIT 1");
	$STH->execute(array(':ik_login' => $ik_login, ':ik_pass1' => $ik_pass1));

	write_log("Отредактирована interkassa");
	exit('<p class="text-success">Настройки изменены!</p>');
}
if(isset($_POST['edit_walletone'])) {
	$wo_login = check($_POST['wo_login'], null);
	$wo_pass  = check($_POST['wo_pass'], null);

	if(empty($wo_login) or empty($wo_pass)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}

	$STH = $pdo->prepare("UPDATE config__bank SET wo_login=:wo_login,wo_pass=:wo_pass LIMIT 1");
	$STH->execute(array(':wo_login' => $wo_login, ':wo_pass' => $wo_pass));

	write_log("Отредактирована walletone");
	exit('<p class="text-success">Настройки изменены!</p>');
}
if(isset($_POST['edit_robokassa'])) {
	$rb_login = check($_POST['rb_login'], null);
	$rb_pass1 = check($_POST['rb_pass1'], null);
	$rb_pass2 = check($_POST['rb_pass2'], null);

	if(empty($rb_login) or empty($rb_pass1) or empty($rb_pass2)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}

	$STH = $pdo->prepare("UPDATE config__bank SET rb_login=:rb_login,rb_pass1=:rb_pass1,rb_pass2=:rb_pass2 LIMIT 1");
	$STH->execute(array(':rb_login' => $rb_login, ':rb_pass1' => $rb_pass1, ':rb_pass2' => $rb_pass2));

	write_log("Отредактирована robokassa");
	exit('<p class="text-success">Настройки изменены!</p>');
}
if(isset($_POST['edit_webmoney'])) {
	$wb_login = check($_POST['wb_login'], null);
	$wb_pass1 = check($_POST['wb_pass1'], null);
	$wb_num   = check($_POST['wb_num'], null);

	if(empty($wb_login) or empty($wb_pass1) or empty($wb_num)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}

	$STH = $pdo->prepare("UPDATE config__bank SET wb_login=:wb_login,wb_pass1=:wb_pass1,wb_num=:wb_num LIMIT 1");
	$STH->execute(array(':wb_login' => $wb_login, ':wb_pass1' => $wb_pass1, ':wb_num' => $wb_num));

	write_log("Отредактирован webmoney");
	exit('<p class="text-success">Настройки изменены!</p>');
}
if(isset($_POST['edit_yandexmoney'])) {
	$ya_num = check($_POST['ya_num'], null);
	$ya_key = check($_POST['ya_key'], null);

	if(empty($ya_num) or empty($ya_key)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}

	$STH = $pdo->prepare("UPDATE config__bank SET ya_num=:ya_num,ya_key=:ya_key LIMIT 1");
	$STH->execute([':ya_num' => $ya_num, ':ya_key' => $ya_key]);

	write_log("Отредактирован yandexmoney");
	exit('<p class="text-success">Настройки изменены!</p>');
}
if(isset($_POST['edit_unitpay'])) {
	$up_type  = check($_POST['up_type'], 'int');
	$up_pass1 = check($_POST['up_pass1'], null);
	$up_pass2 = check($_POST['up_pass2'], null);

	if(!in_array($up_type, [1, 2])) {
		exit('<p class="text-danger">Неверный тип лица</p>');
	}

	if(empty($up_type) or empty($up_pass1) or empty($up_pass2)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}

	$STH = $pdo->prepare("UPDATE config__bank SET up_type=:up_type,up_pass1=:up_pass1,up_pass2=:up_pass2 LIMIT 1");
	$STH->execute([':up_type' => $up_type, ':up_pass1' => $up_pass1, ':up_pass2' => $up_pass2]);

	write_log("Отредактирован UnitPay");
	exit('<p class="text-success">Настройки изменены!</p>');
}
if(isset($_POST['edit_paysera'])) {
	$ps_num  = check($_POST['ps_num'], null);
	$ps_pass = check($_POST['ps_pass'], null);

	if(empty($ps_num) or empty($ps_pass)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}

	$STH = $pdo->prepare("UPDATE config__bank SET ps_num=:ps_num,ps_pass=:ps_pass LIMIT 1");
	$STH->execute([':ps_num' => $ps_num, ':ps_pass' => $ps_pass]);

	write_log("Отредактирован Paysera");
	exit('<p class="text-success">Настройки изменены!</p>');
}

if(isset($_POST['onQiwiPaymentSystem'])) {
	if($protocol == 'http') {
		exit(
			'<p class="text-danger">'
			. 'Для использования qiwi требуется наличие '
			. '<a target="_blank" href="https://worksma.ru/wiki/SSL-sertifikat-pokupka-ustanovka">SSL сертификата!</a>'
			. '</p>'
			. '<script>'
			. '$("#qiwiTrigger label:nth-child(1)").removeClass("active");'
			. '$("#qiwiTrigger label:nth-child(2)").addClass("active");'
			. '</script>'
		);
	}

	$STH = $pdo->prepare("UPDATE config__bank SET qw=:qw LIMIT 1");
	$STH->execute([':qw' => 1]);

	exit();
}
if(isset($_POST['editQiwiPaymentSystem'])) {
	$qw_pass = check($_POST['qw_pass'], null);

	if(empty($qw_pass)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}

	$STH = $pdo->prepare("UPDATE config__bank SET qw_pass=:qw_pass LIMIT 1");
	$STH->execute([':qw_pass' => $qw_pass]);

	write_log("Отредактирован qiwi");
	exit('<p class="text-success">Настройки изменены!</p>');
}

if(isset($_POST['editLiqPayPaymentSystem'])) {
	$lp_public_key  = check($_POST['lp_public_key'], null);
	$lp_private_key = check($_POST['lp_private_key'], null);

	if(empty($lp_public_key) || empty($lp_private_key)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}

	$STH = $pdo->prepare(
		"UPDATE config__bank SET lp_public_key=:lp_public_key, lp_private_key=:lp_private_key LIMIT 1"
	);
	$STH->execute([':lp_public_key' => $lp_public_key, ':lp_private_key' => $lp_private_key]);

	write_log("Отредактирован liqpay");
	exit('<p class="text-success">Настройки изменены!</p>');
}

if(isset($_POST['editAnyPayPaymentSystem'])) {
	$ap_project_id  = check($_POST['ap_project_id'], null);
	$ap_private_key = check($_POST['ap_private_key'], null);

	if(empty($ap_project_id) || empty($ap_private_key)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}

	$STH = $pdo->prepare(
		"UPDATE config__bank SET ap_project_id=:ap_project_id, ap_private_key=:ap_private_key LIMIT 1"
	);
	$STH->execute([':ap_project_id' => $ap_project_id, ':ap_private_key' => $ap_private_key]);

	write_log("Отредактирован anypay");
	exit('<p class="text-success">Настройки изменены!</p>');
}

if(isset($_POST['editEnotPaymentSystem'])) {
	$id   = check($_POST['id'], 'int');
	$key  = check($_POST['key'], null);
	$key2 = check($_POST['key2'], null);

	if(empty($id) || empty($key) || empty($key2)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}

	pdo()->prepare(
		"UPDATE config__bank SET enot_id=:enot_id, enot_key=:enot_key, enot_key2=:enot_key2 LIMIT 1"
	)->execute([':enot_id' => $id, ':enot_key' => $key, ':enot_key2' => $key2]);

	write_log("Отредактирован enot");
	exit('<p class="text-success">Настройки изменены!</p>');
}

if(isset($_POST['edit_unban'])) {
	$price1 = checkJs($_POST['price1'], "float");
	$price2 = checkJs($_POST['price2'], "float");
	$price3 = checkJs($_POST['price3'], "float");

	if(empty($price1)) {
		$price1 = 0;
	}
	if(empty($price2)) {
		$price2 = 0;
	}
	if(empty($price3)) {
		$price3 = 0;
	}

	$STH = $pdo->prepare("UPDATE config__prices SET price1=:price1,price2=:price2,price3=:price3 LIMIT 1");
	$STH->execute([':price1' => $price1, ':price2' => $price2, ':price3' => $price3]);

	write_log(
		"Отредактирована цена разбана: price1 - " . $price1 . "; price2 - " . $price2 . "; price3 - " . $price3 . ";"
	);
	exit('<p class="text-success">Настройки изменены!</p>');
}
if(isset($_POST['edit_discount'])) {
	$discount = checkJs($_POST['discount'], "int");

	if(empty($discount)) {
		$discount = 0;
	}

	$STH = $pdo->prepare("UPDATE config__prices SET discount=:discount LIMIT 1");
	$STH->execute([':discount' => $discount]);

	write_log("Отредактирована скидка: discount - " . $discount . ";");
	exit('<p class="text-success">Настройки изменены!</p>');
}
if(isset($_POST['edit_min_amount'])) {
	$min_amount = checkJs($_POST['min_amount'], "int");

	if(empty($min_amount)) {
		$min_amount = 0;
	}

	$STH = $pdo->prepare("UPDATE config__secondary SET min_amount=:min_amount LIMIT 1");
	$STH->execute([':min_amount' => $min_amount]);

	write_log("Отредактирована минимальная сумма для пополнения : min_amount - " . $min_amount . ";");
	exit('<p class="text-success">Настройки изменены!</p>');
}
if(isset($_POST['edit_stand_balance'])) {
	$stand_balance = checkJs($_POST['stand_balance'], "float");

	if(empty($stand_balance)) {
		$stand_balance = 0;
	}

	$STH = $pdo->prepare("UPDATE config__secondary SET stand_balance=:stand_balance LIMIT 1");
	$STH->execute([':stand_balance' => $stand_balance]);

	write_log("Отредактирован начальный баланс: stand_balance - " . $stand_balance . ";");
	exit('<p class="text-success">Настройки изменены!</p>');
}
if(isset($_POST['edit_referral_percent'])) {
	$referral_percent = checkJs($_POST['referral_percent'], "int");

	if(empty($referral_percent)) {
		$referral_percent = 0;
	}

	$STH = $pdo->prepare("UPDATE config__prices SET referral_percent=:referral_percent LIMIT 1");
	$STH->execute([':referral_percent' => $referral_percent]);

	write_log("Отредактирован процент реферальной программы: referral_percent - " . $referral_percent . ";");
	exit('<p class="text-success">Настройки изменены!</p>');
}
if(isset($_POST['edit_stickers'])) {
	$price4 = checkJs($_POST['price4'], "float");

	if(empty($price4)) {
		exit('<p class="text-danger">Укажите цену!</p>');
	}

	$STH = $pdo->prepare("UPDATE config__prices SET price4=:price4 LIMIT 1");
	$STH->execute([':price4' => $price4]);

	write_log("Отредактирована цена стикеров: price4 - " . $price4 . ";");
	exit('<p class="text-success">Настройки изменены!</p>');
}
if(isset($_POST['edit_template'])) {
	$template = checkJs($_POST['template'], null);
	$type     = check($_POST['type'], "int");

	if(empty($type)) {
		exit(json_encode(['status' => '2']));
	}
	if(($type == 1 || $type == 2) && empty($template)) {
		exit(json_encode(['status' => '2']));
	}

	$template = str_replace(["/", ".", " ", "\\"], "", $template);

	if($type == 1) {
		$STH = $pdo->prepare("UPDATE config SET template=:template LIMIT 1");
		write_log("Шаблон сайта изменен на " . $template);
	} elseif($type == 2) {
		$STH = $pdo->prepare("UPDATE config SET template_mobile=:template LIMIT 1");
		write_log("Мобильный шаблон сайта изменен на " . $template);
	} elseif($type == 3) {
		if(empty($template)) {
			$SC->set_cookie("template", "");
			write_log("Персональный шаблон убран");
		} else {
			$SC->set_cookie("template", $template);
			write_log("Персональный шаблон изменен на " . $template);
		}
	}
	$STH->execute([':template' => $template]);

	exit(json_encode(['status' => '1']));
}
if(isset($_POST['dell_all_chat_messages'])) {
	$pdo->exec("TRUNCATE TABLE `chat`");
}
if(isset($_POST['dell_old_bans'])) {
	$STH = $pdo->query("SELECT id,img FROM bans WHERE date < (NOW() - INTERVAL 30 DAY) AND closed != '0'");
	$STH->execute();
	$row   = $STH->fetchAll();
	$count = count($row);
	for($i = 0; $i < $count; $i++) {
		$id = $row[$i]['id'];
		if(isset($row[$i]['img']) and $row[$i]['img'] != '0') {
			$data = explode(";", $row[$i]['img']);
			for($j = 0; $j < count($data); $j++) {
				if(!empty($data[$j])) {
					unlink('../' . $data[$j]);
				}
			}
		}
		$pdo->exec("DELETE FROM bans WHERE id='$id' LIMIT 1");
		$pdo->exec("DELETE FROM bans__comments WHERE ban_id='$id'");
	}
}
if(isset($_POST['dell_old_tickets'])) {
	$STH = $pdo->query("SELECT id,files FROM tickets WHERE date < (NOW() - INTERVAL 30 DAY) AND closed != '0'");
	$STH->execute();
	$row   = $STH->fetchAll();
	$count = count($row);
	for($i = 0; $i < $count; $i++) {
		$id = $row[$i]['id'];
		if(isset($row[$i]['files']) and $row[$i]['files'] != 'none') {
			unlink('../' . $row[$i]['files']);
		}

		$pdo->exec("DELETE FROM tickets WHERE id='$id' LIMIT 1");
		$pdo->exec("DELETE FROM tickets__answers WHERE ticket='$id'");
	}
}

if(isset($_POST['edit_email_settings'])) {
	$username     = check($_POST['email_username'], null);
	$host         = check($_POST['email_host'], null);
	$port         = check($_POST['email_port'], null);
	$password     = check($_POST['email_password'], null);
	$charset      = check($_POST['email_charset'], null);
	$from_email   = check($_POST['from_email'], null);
	$verify_peers = check($_POST['verify_peers'], null);

	if($host == 'test.worksma.ru') {
		exit('<p class="text-danger">Редактирование данных настроек в тестовой версии движка запрещено!</p>');
	}

	if(empty($username) or empty($port) or empty($host) or empty($password) or empty($charset) or empty($from_email) or empty($verify_peers)) {
		exit('<p class="text-danger">Вы заполнили не все поля!</p>');
	}

	$STH = $pdo->prepare(
		"UPDATE config__email SET username=:username,port=:port,host=:host,password=:password,charset=:charset,from_email=:from_email,verify_peers=:verify_peers LIMIT 1"
	);
	$STH->execute(
		[
			':username'     => $username,
			':port'         => $port,
			':host'         => $host,
			':password'     => $password,
			':charset'      => $charset,
			':from_email'   => $from_email,
			':verify_peers' => $verify_peers
		]
	);

	write_log("Отредактирован почтовый сервер");
	exit('<p class="text-success">Настройки изменены! Обязательно проверьте правильность введеных настроек путем отправки <b class="c-p" onclick="send_test_mail();">тестового письма</b>.</p>');
}

/* Настройка статистики
=========================================*/
if(isset($_POST['dell_stat_log'])) {
	if(file_exists('../logs/stat.log')) {
		unlink("../logs/stat.log");
		$file = fopen("../logs/stat.log", "w");
		fclose($file);
	}
}
if(isset($_POST['edit_stat_number'])) {
	$stat_number = check($_POST['stat_number'], 'int');

	if(empty($stat_number)) {
		exit('<p class="text-danger">Вы не указали количество!</p>');
	}

	$STH = $pdo->prepare("UPDATE config SET stat_number=:stat_number LIMIT 1");
	$STH->execute([':stat_number' => $stat_number]);

	write_log("Количество записей статистики сайта изменено на " . $stat_number);
	exit('<p class="text-success">Количество изменено!</p>');
}

/* Редактор меню
=========================================*/
if(isset($_POST['create_menu'])) {
	$name    = check($_POST['input_name'], null);
	$link    = check($_POST['input_link'], null);
	$checbox = check($_POST['checbox'], "int");

	if(empty($link) or empty($name) or empty($checbox)) {
		exit(json_encode(['status' => '2']));
	}
	if($checbox != 1 and $checbox != 2 and $checbox != 3) {
		exit(json_encode(['status' => '2']));
	}

	$name = preg_icon($name);
	$name = preg_color($name);

	$STH = $pdo->query('SELECT poz FROM menu ORDER BY poz DESC LIMIT 1');
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	$poz = empty($tmp->poz) ? 0 : $tmp->poz + 1;

	$STH = $pdo->prepare("INSERT INTO menu (name, link, poz, for_all) VALUES (:name, :link, :poz, :for_all)");
	if($STH->execute(['name' => $name, 'link' => $link, 'poz' => $poz, 'for_all' => $checbox]) == '1') {
		exit(json_encode(['status' => '1']));
	} else {
		exit(json_encode(['status' => '2']));
	}
}
if(isset($_POST['create_sliding_menu'])) {
	$name    = check($_POST['input_name'], null);
	$checbox = check($_POST['sliding_checbox'], "int");

	if(empty($name) or empty($checbox)) {
		exit(json_encode(['status' => '2']));
	}
	if($checbox != 1 && $checbox != 2 && $checbox != 3) {
		exit(json_encode(['status' => '2']));
	}

	$name = preg_icon($name);
	$name = preg_color($name);

	$STH = $pdo->query('SELECT id,poz FROM menu ORDER BY poz DESC LIMIT 1');
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	$poz = empty($tmp->poz) ? 0 : $tmp->poz + 1;

	$STH = $pdo->query("SHOW TABLE STATUS LIKE 'menu'");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	$id  = $tmp->Auto_increment;

	$STH = $pdo->prepare(
		"INSERT INTO menu (name, link, poz, menu__sub, for_all) VALUES (:name, :link, :poz, :menu__sub, :for_all)"
	);
	if($STH->execute(
			['name' => $name, 'link' => 'none', 'poz' => $poz, 'menu__sub' => $id, 'for_all' => $checbox]
		) == '1') {
		exit(json_encode(['status' => '1']));
	} else {
		exit(json_encode(['status' => '2']));
	}
}
if(isset($_POST['create_menu_pod'])) {
	$name    = check($_POST['input_name'], null);
	$link    = check($_POST['input_link'], null);
	$number  = check($_POST['number'], "int");
	$checbox = check($_POST['checbox'], "int");

	if(empty($link) or empty($name) or empty($number) or empty($checbox)) {
		exit(json_encode(['status' => '2']));
	}
	if($checbox != 1 && $checbox != 2 && $checbox != 3) {
		exit(json_encode(['status' => '2']));
	}

	$name = preg_icon($name);
	$name = preg_color($name);

	$STH = $pdo->query("SELECT poz from menu__sub WHERE menu='$number' ORDER BY poz DESC LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	$poz = empty($tmp->poz) ? 0 : $tmp->poz + 1;

	$STH = $pdo->prepare(
		"INSERT INTO menu__sub (name, link, poz, menu, for_all) VALUES (:name, :link, :poz, :menu, :for_all)"
	);
	if($STH->execute(
			['name' => $name, 'link' => $link, 'poz' => $poz, 'menu' => $number, 'for_all' => $checbox]
		) == '1') {
		exit(json_encode(['status' => '1']));
	} else {
		exit(json_encode(['status' => '2']));
	}
}
if(isset($_POST['load_menu'])) {
	$i = 1;

	$STH = $pdo->query('SELECT * FROM menu ORDER BY poz');
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$row->name = upreg_menu_name($row->name);
		?>
		<tr id="menu_1_<?php echo $row->id ?>">
			<td><?php echo $i ?></td>
			<td>
				<input type="text" class="form-control" id="edit_name_1<?php echo $row->id ?>" maxlength="255" autocomplete="off" value="<?php echo $row->name ?>">
				<?php if($row->menu__sub != '0') { ?>
					<div class="btn-group-vertical w-100">
						<div data-target="#menu__sub_<?php echo $row->id ?>" data-toggle="modal" type="button" class="btn btn-default">
							<span class="glyphicon glyphicon-list-alt"></span> Подменю
						</div>
					</div>
				<input type="hidden" class="form-control" id="edit_link_1<?php echo $row->id ?>" maxlength="255" autocomplete="off" value="<?php echo $row->link ?>">
					<script> $('#menu__sub_<?php echo $row->id ?>').modal('hide'); </script>
					<div id="menu__sub_<?php echo $row->id ?>" class="modal fade">
						<div class="modal-dialog modal-lg">
							<div class="modal-content">
								<div class="modal-header">
									<h4 class="modal-title">Настройка подменю</h4>
								</div>
								<div class="modal-body">
									<h4>Добавить пункт</h4>
									<div class="col-md-5 pd-0">
										<div class="form-group">
											<label for="input_name_pod_<?php echo $row->id ?>">
												<h4>
													Название
												</h4>
											</label>
											<input type="text" class="form-control" id="input_name_pod_<?php echo $row->id ?>" maxlength="255" autocomplete="off">
											<span class="set-menu-icon"></span>
										</div>
									</div>
									<div class="col-md-5 pd-0">
										<div class="form-group">
											<label for="input_link_pod_<?php echo $row->id ?>">
												<h4>
													Ссылка
												</h4>
											</label>
											<input type="text" class="form-control" id="input_link_pod_<?php echo $row->id ?>" maxlength="255" autocomplete="off">
										</div>
									</div>
									<div class="col-md-2 pd-0">
										<div class="form-group">
											<label for="sliding_pod_checbox_<?php echo $row->id ?>">
												<h4>
													Доступ
												</h4>
											</label>
											<select class="form-control" id="sliding_pod_checbox_<?php echo $row->id ?>">
												<option value="1">Для всех</option>
												<option value="2">Для авторизованных</option>
												<option value="3">Для неавторизованных</option>
											</select>
										</div>
									</div>

									<div id="create_menu_result_pod_<?php echo $row->id ?>" class="mt-10"></div>
									<button onclick="create_menu_pod('<?php echo $row->id ?>');" type="button" class="btn btn-default mt-10">Создать</button>
									<br>
									<h4>Список</h4>
									<div class="content">
										<div class="table-responsive mb-0">
											<table class="table table-bordered">
												<thead>
												<tr>
													<td width="5%">#</td>
													<td>Название/Ссылка</td>
													<td width="20%">Доступ</td>
													<td width="15%">Порядок</td>
													<td width="15%">Действие</td>
												</tr>
												</thead>
												<tbody id="menu__sub_links_<?php echo $row->id ?>">

												</tbody>
											</table>
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
								</div>
							</div>
						</div>
					</div>
					<script>load_menu__sub('<?php echo $row->id ?>');</script>
				<?php } else { ?>
				<input type="text" class="form-control" id="edit_link_1<?php echo $row->id ?>" maxlength="255" autocomplete="off" value="<?php echo $row->link ?>">
				<?php } ?>
			</td>
			<td>
				<div class="form-group">
					<select class="form-control" id="checbox_1_<?php echo $row->id ?>">
						<option value="1" <?php if($row->for_all == 1) {
							echo "selected";
						} ?>>Для всех
						</option>
						<option value="2" <?php if($row->for_all == 2) {
							echo "selected";
						} ?>>Для авторизованных
						</option>
						<option value="3" <?php if($row->for_all == 3) {
							echo "selected";
						} ?>>Для неавторизованных
						</option>
					</select>
				</div>
			</td>
			<td>
				<div class="btn-group-vertical w-100">
					<div onclick='up_menu("<?php echo $row->id ?>");' class="btn btn-default">
						<span class="glyphicon glyphicon-chevron-up"></span> <i class="hidden-xs">Поднять</i>
					</div>
					<div onclick='down_menu("<?php echo $row->id ?>");' class="btn btn-default">
						<span class="glyphicon glyphicon-chevron-down"></span> <i class="hidden-xs">Опустить</i>
					</div>
				</div>
			</td>
			<td>
				<div class="btn-group-vertical w-100">
					<div onclick='edit_menu("<?php echo $row->id ?>");' class="btn btn-default">
						<span class="glyphicon glyphicon-pencil"></span> <i class="hidden-xs">Изменить</i>
					</div>
					<div onclick='dell_menu("<?php echo $row->id ?>");' class="btn btn-default">
						<span class="glyphicon glyphicon-trash"></span> <i class="hidden-xs">Удалить</i>
					</div>
				</div>
			</td>
		</tr>
		<?php
		$i++;
	}
	exit();
}
if(isset($_POST['load_menu__sub'])) {
	$number = check($_POST['number'], "int");

	if(empty($number)) {
		exit('Ошибка: [Нет значения переменной]');
	}
	$menu__sub_i = 1;

	$STH = $pdo->query("SELECT * from menu__sub WHERE menu='$number' ORDER BY poz");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($menu__sub_row = $STH->fetch()) {
		$menu__sub_row->name = upreg_menu_name($menu__sub_row->name);
		?>
		<tr id="pod_menu_1_<?php echo $menu__sub_row->id ?>">
			<td><?php echo $menu__sub_i ?></td>
			<td style="width:30% !important">
				<input type="text" class="form-control" id="edit_pod_name_1_1_<?php echo $menu__sub_row->id ?>" maxlength="255" autocomplete="off" value="<?php echo $menu__sub_row->name ?>">
				<input type="text" class="form-control" id="edit_pod_link_1_1_<?php echo $menu__sub_row->id ?>" maxlength="255" autocomplete="off" value="<?php echo $menu__sub_row->link ?>">
			</td>
			<td style="width:30%">
				<div class="form-group">
					<select class="form-control" id="sliding_pod_checbox_1_1_<?php echo $menu__sub_row->id ?>">
						<option value="1" <?php if($menu__sub_row->for_all == 1) {
							echo "selected";
						} ?>>Для всех
						</option>
						<option value="2" <?php if($menu__sub_row->for_all == 2) {
							echo "selected";
						} ?>>Для авторизованных
						</option>
						<option value="3" <?php if($menu__sub_row->for_all == 3) {
							echo "selected";
						} ?>>Для неавторизованных
						</option>
					</select>
				</div>
			</td>
			<td>
				<div class="btn-group-vertical w-100">
					<div onclick='up_pod_menu("<?php echo $menu__sub_row->id ?>","<?php echo $number ?>");' class="btn btn-default">
						<span class="glyphicon glyphicon-chevron-up"></span> <i class="hidden-xs">Поднять</i>
					</div>
					<div onclick='down_pod_menu("<?php echo $menu__sub_row->id ?>","<?php echo $number ?>");' class="btn btn-default">
						<span class="glyphicon glyphicon-chevron-down"></span> <i class="hidden-xs">Опустить</i>
					</div>
				</div>
			</td>
			<td>
				<div class="btn-group-vertical w-100">
					<div onclick='edit_pod_menu("<?php echo $menu__sub_row->id ?>","<?php echo $number ?>");' class="btn btn-default">
						<span class="glyphicon glyphicon-pencil"></span> <i class="hidden-xs">Изменить</i>
					</div>
					<div onclick='dell_pod_menu("<?php echo $menu__sub_row->id ?>","<?php echo $number ?>");' class="btn btn-default">
						<span class="glyphicon glyphicon-trash"></span> <i class="hidden-xs">Удалить</i>
					</div>
				</div>
			</td>
		</tr>
		<?php
		$menu__sub_i++;
	}
}
if(isset($_POST['dell_pod_menu'])) {
	$menu   = check($_POST['menu'], null);
	$number = check($_POST['number'], "int");

	if(empty($number) or empty($menu)) {
		exit('Ошибка: [Нет значения переменной]');
	}

	$STH = $pdo->query("SELECT poz from menu__sub WHERE id='$number' and menu='$menu' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();

	$STH = $pdo->query("SELECT id,poz from menu__sub WHERE poz>'$tmp->poz' and menu='$menu'");
	$STH->execute();
	$row   = $STH->fetchAll();
	$count = count($row);

	if($count == 0) {
		$pdo->exec("DELETE FROM menu__sub WHERE id='$number' and menu='$menu' LIMIT 1");
		exit();
	}

	for($i = 0; $i < $count; $i++) {
		$id  = $row[$i]['id'];
		$STH = $pdo->prepare("UPDATE menu__sub SET poz=:poz WHERE id='$id' and menu='$menu' LIMIT 1");
		$poz = $row[$i]['poz'] - 1;
		if($STH->execute(['poz' => $poz]) != '1') {
			exit();
		}
	}

	$pdo->exec("DELETE FROM menu__sub WHERE id='$number' LIMIT 1");
	exit();
}
if(isset($_POST['edit_pod_menu'])) {
	$name    = check($_POST['input_name'], null);
	$link    = check($_POST['input_link'], null);
	$menu    = check($_POST['menu'], null);
	$number  = check($_POST['number'], "int");
	$checbox = check($_POST['checbox'], "int");

	if(empty($link) or empty($name) or empty($number) or empty($menu) or empty($checbox)) {
		exit('<p class="text-danger">Заполните все поля!</p>');
	}
	if($checbox != 1 && $checbox != 2 && $checbox != 3) {
		exit(json_encode(['status' => '2']));
	}

	$name = preg_icon($name);
	$name = preg_color($name);

	$STH = $pdo->prepare(
		"UPDATE menu__sub SET name=:name,link=:link,for_all=:for_all WHERE id='$number' and menu='$menu' LIMIT 1"
	);
	if($STH->execute(['name' => $name, 'link' => $link, 'for_all' => $checbox]) == '1') {
		exit(json_encode(['status' => '1']));
	} else {
		exit(json_encode(['status' => '2']));
	}
}
if(isset($_POST['up_pod_menu'])) {
	$menu   = check($_POST['menu'], null);
	$number = check($_POST['number'], "int");

	if(empty($number) or empty($menu)) {
		exit('<p class="text-danger">Заполните все поля!</p>');
	}

	$STH = $pdo->query("SELECT id,poz from menu__sub WHERE id='$number' and menu='$menu' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	if(empty($tmp->id)) {
		exit('<p class="text-danger">Ошибка!</p>');
	}
	if($tmp->poz == 1) {
		exit();
	}
	$poz  = $tmp->poz;
	$poz2 = $tmp->poz - 1;

	$STH = $pdo->prepare("UPDATE menu__sub SET poz=:poz WHERE poz='$poz2' and menu='$menu' LIMIT 1");
	if($STH->execute(['poz' => $poz]) == '1') {
		$STH = $pdo->prepare("UPDATE menu__sub SET poz=:poz2 WHERE id='$number' and menu='$menu' LIMIT 1");
		if($STH->execute(['poz2' => $poz2]) == '1') {
			exit('<script>load_menu__sub("' . $menu . '");</script>');
		} else {
			exit();
		}
	} else {
		exit();
	}
}
if(isset($_POST['down_pod_menu'])) {
	$menu   = check($_POST['menu'], null);
	$number = check($_POST['number'], "int");

	if(empty($number) or empty($menu)) {
		exit('<p class="text-danger">Заполните все поля!</p>');
	}

	$STH = $pdo->query("SELECT id,poz from menu__sub WHERE id='$number' and menu='$menu' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	if(empty($tmp->id)) {
		exit('<p class="text-danger">Ошибка!</p>');
	}
	$poz  = $tmp->poz;
	$poz2 = $tmp->poz + 1;
	$STH  = $pdo->query("SELECT poz from menu__sub WHERE menu='$menu' ORDER BY poz DESC LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	$max = $tmp->poz;

	if($poz == $max) {
		exit();
	}

	$STH = $pdo->prepare("UPDATE menu__sub SET poz=:poz WHERE poz='$poz2' and menu='$menu' LIMIT 1");
	if($STH->execute(['poz' => $poz]) == '1') {
		$STH = $pdo->prepare("UPDATE menu__sub SET poz=:poz2 WHERE id='$number' and menu='$menu' LIMIT 1");
		if($STH->execute(['poz2' => $poz2]) == '1') {
			exit('<script>load_menu__sub("' . $menu . '");</script>');
		} else {
			exit();
		}
	} else {
		exit();
	}
}
if(isset($_POST['dell_menu'])) {
	$number = check($_POST['number'], "int");

	if(empty($number)) {
		exit('Ошибка: [Нет значения переменной]');
	}

	$STH = $pdo->query("SELECT poz,menu__sub from menu WHERE id='$number' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();

	if($tmp->menu__sub != '0') {
		$pdo->exec("DELETE FROM menu__sub WHERE menu='$number'");
	}

	$STH = $pdo->query("SELECT id,poz from menu WHERE poz>'$tmp->poz'");
	$STH->execute();
	$row   = $STH->fetchAll();
	$count = count($row);

	if($count == 0) {
		$pdo->exec("DELETE FROM menu WHERE id='$number' LIMIT 1");
		exit();
	}

	for($i = 0; $i < $count; $i++) {
		$id  = $row[$i]['id'];
		$STH = $pdo->prepare("UPDATE menu SET poz=:poz WHERE id='$id' LIMIT 1");
		$poz = $row[$i]['poz'] - 1;
		if($STH->execute(['poz' => $poz]) != '1') {
			exit();
		}
	}

	$pdo->exec("DELETE FROM menu WHERE id='$number' LIMIT 1");
	exit();
}
if(isset($_POST['edit_menu'])) {
	$name    = check($_POST['input_name'], null);
	$link    = check($_POST['input_link'], null);
	$number  = check($_POST['number'], "int");
	$checbox = check($_POST['checbox'], "int");

	if(empty($link) or empty($name) or empty($number) or empty($checbox)) {
		exit('<p class="text-danger">Заполните все поля!</p>');
	}
	if($checbox != 1 && $checbox != 2 && $checbox != 3) {
		exit(json_encode(['status' => '2']));
	}

	$name = preg_icon($name);
	$name = preg_color($name);

	$STH = $pdo->prepare("UPDATE menu SET name=:name,link=:link,for_all=:for_all WHERE id='$number' LIMIT 1");
	if($STH->execute(['name' => $name, 'link' => $link, 'for_all' => $checbox]) == '1') {
		exit(json_encode(['status' => '1']));
	} else {
		exit(json_encode(['status' => '2']));
	}
}
if(isset($_POST['up_menu'])) {
	$number = check($_POST['number'], "int");

	if(empty($number)) {
		exit('<p class="text-danger">Заполните все поля!</p>');
	}

	$STH = $pdo->query("SELECT id,poz from menu WHERE id='$number' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	if(empty($tmp->id)) {
		exit('<p class="text-danger">Ошибка!</p>');
	}
	if($tmp->poz == 1) {
		exit();
	}
	$poz  = $tmp->poz;
	$poz2 = $tmp->poz - 1;

	$STH = $pdo->prepare("UPDATE menu SET poz=:poz WHERE poz='$poz2' LIMIT 1");
	if($STH->execute(['poz' => $poz]) == '1') {
		$STH = $pdo->prepare("UPDATE menu SET poz=:poz2 WHERE id='$number' LIMIT 1");
		if($STH->execute(['poz2' => $poz2]) == '1') {
			exit('<script>load_menu();</script>');
		} else {
			exit();
		}
	} else {
		exit();
	}
}
if(isset($_POST['down_menu'])) {
	$number = check($_POST['number'], "int");

	if(empty($number)) {
		exit('<p class="text-danger">Заполните все поля!</p>');
	}

	$STH = $pdo->query("SELECT id,poz from menu WHERE id='$number' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	if(empty($tmp->id)) {
		exit('<p class="text-danger">Ошибка!</p>');
	}
	$poz  = $tmp->poz;
	$poz2 = $tmp->poz + 1;
	$STH  = $pdo->query('SELECT poz FROM menu ORDER BY poz DESC LIMIT 1');
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	$max = $tmp->poz;

	if($poz == $max) {
		exit();
	}

	$STH = $pdo->prepare("UPDATE menu SET poz=:poz WHERE poz='$poz2' LIMIT 1");
	if($STH->execute(['poz' => $poz]) == '1') {
		$STH = $pdo->prepare("UPDATE menu SET poz=:poz2 WHERE id='$number' LIMIT 1");
		if($STH->execute(['poz2' => $poz2]) == '1') {
			exit('<script>load_menu();</script>');
		} else {
			exit();
		}
	} else {
		exit();
	}
}

/* Логи
=========================================*/
if(isset($_POST['load_logs'])) {
	$file = get_log_file_name("log");

	if(file_exists("../logs/" . $file)) {
		$size = filesize("../logs/" . $file);
		$size = calculate_size($size);
		$log  = '
		<a data-toggle="modal" data-target="#1" class="btn btn-success w-100">Открыть (' . $size . ')</a>
		<a href="#" class="btn btn-danger w-100" onclick="dell_logs()">Удалить</a>
		';
	} else {
		$log = '<a href="#" class="btn btn-success w-100">Файл пуст</a>';
	}
	echo $log;
}
if(isset($_POST['load_error_logs'])) {
	$file = get_log_file_name("error_log");

	if(file_exists("../logs/" . $file)) {
		$size      = filesize("../logs/" . $file);
		$size      = calculate_size($size);
		$error_log = '
		<a data-toggle="modal" data-target="#2" class="btn btn-success w-100">Открыть (' . $size . ')</a>
		<a href="#" class="btn btn-danger w-100" onclick="dell_error_logs()">Удалить</a>
		';
	} else {
		$error_log = '<a href="#" class="btn btn-success w-100">Файл пуст</a>';
	}
	echo $error_log;
}
if(isset($_POST['load_pdo_errors'])) {
	$file = get_log_file_name("pdo_errors");

	if(file_exists("../logs/" . $file)) {
		$size       = filesize("../logs/" . $file);
		$size       = calculate_size($size);
		$pdo_errors = '
		<a data-toggle="modal" data-target="#3" class="btn btn-success w-100">Открыть (' . $size . ')</a>
		<a href="#" class="btn btn-danger w-100" onclick="dell_pdo_errors()">Удалить</a>
		';
	} else {
		$pdo_errors = '<a href="#" class="btn btn-success w-100">Файл пуст</a>';
	}
	echo $pdo_errors;
}
if(isset($_POST['load_payment_successes'])) {
	$file = get_log_file_name("payment_successes");

	if(file_exists("../logs/" . $file)) {
		$size              = filesize("../logs/" . $file);
		$size              = calculate_size($size);
		$payment_successes = '
		<a data-toggle="modal" data-target="#4" class="btn btn-success w-100">Открыть (' . $size . ')</a>
		<a href="#" class="btn btn-danger w-100" onclick="dell_payment_successes()">Удалить</a>
		';
	} else {
		$payment_successes = '<a href="#" class="btn btn-success w-100">Файл пуст</a>';
	}
	echo $payment_successes;
}
if(isset($_POST['load_payment_errors'])) {
	$file = get_log_file_name("payment_errors");

	if(file_exists("../logs/" . $file)) {
		$size           = filesize("../logs/" . $file);
		$size           = calculate_size($size);
		$payment_errors = '
		<a data-toggle="modal" data-target="#5" class="btn btn-success w-100">Открыть (' . $size . ')</a>
		<a href="#" class="btn btn-danger w-100" onclick="dell_payment_errors()">Удалить</a>
		';
	} else {
		$payment_errors = '<a href="#" class="btn btn-success w-100">Файл пуст</a>';
	}
	echo $payment_errors;
}
if(isset($_POST['load_services_log'])) {
	$file = get_log_file_name("services_log");

	if(file_exists("../logs/" . $file)) {
		$size         = filesize("../logs/" . $file);
		$size         = calculate_size($size);
		$services_log = '
		<a data-toggle="modal" data-target="#6" class="btn btn-success w-100">Открыть (' . $size . ')</a>
		<a href="#" class="btn btn-danger w-100" onclick="dell_services_log()">Удалить</a>
		';
	} else {
		$services_log = '<a href="#" class="btn btn-success w-100">Файл пуст</a>';
	}
	echo $services_log;
}
if(isset($_POST['dell_logs'])) {
	unlink("../logs/" . get_log_file_name("log"));
	echo '<a href="#" class="btn btn-success w-100">Файл пуст</a>';
}
if(isset($_POST['dell_error_logs'])) {
	unlink("../logs/" . get_log_file_name("error_log"));
	echo '<a href="#" class="btn btn-success w-100">Файл пуст</a>';
}
if(isset($_POST['dell_pdo_errors'])) {
	unlink("../logs/" . get_log_file_name("pdo_errors"));
	echo '<a href="#" class="btn btn-success w-100">Файл пуст</a>';
}
if(isset($_POST['dell_payment_successes'])) {
	unlink("../logs/" . get_log_file_name("payment_successes"));
	echo '<a href="#" class="btn btn-success w-100">Файл пуст</a>';
}
if(isset($_POST['dell_payment_errors'])) {
	unlink("../logs/" . get_log_file_name("payment_errors"));
	echo '<a href="#" class="btn btn-success w-100">Файл пуст</a>';
}
if(isset($_POST['dell_services_log'])) {
	unlink("../logs/" . get_log_file_name("services_log"));
	echo '<a href="#" class="btn btn-success w-100">Файл пуст</a>';
}

if(isset($_POST['change_value'])) {
	$table = check($_POST['table'], null);
	$attr  = check($_POST['attr'], null);
	$value = check($_POST['value'], null);
	$id    = check($_POST['id'], "int");

	if(empty($attr)) {
		exit();
	}
	if(check_for_php($_POST['value'])) {
		exit();
	}
	if($safe_mode == 1) {
		if(($_POST['value'] != check($_POST['value'], "int")) && (!in_array($_POST['value'], ['RUB', 'USD', 'EUR']))) {
			exit();
		}
		if(!in_array(
			check($_POST['table'], null),
			['config', 'users', 'config__bank', 'config__secondary', 'config__email', 'config__prices']
		)) {
			exit();
		}
	}

	if(empty($value) && $value != 0) {
		$value = '';
	}

	if(empty($id)) {
		$STH = $pdo->prepare("UPDATE `$table` SET `$attr`=:value");
		$STH->execute([':value' => $value]);
	} else {
		$STH = $pdo->prepare("UPDATE `$table` SET `$attr`=:value WHERE `id`='$id' LIMIT 1");
		$STH->execute([':value' => $value]);
	}
	exit();
}
/* Шаблонизатор
=========================================*/
if(isset($_POST['get_content_tpl']) || isset($_POST['save_code'])) {
	$name = $_POST['name'];

	if(empty($name)) {
		exit(json_encode(['status' => '2', 'message' => 'Файл не найден']));
	}

	if(
		(!stristr($name, "templates/" . $conf->template . "/tpl/") && !stristr(
				$name,
				"templates/" . $conf->template . "/css/"
			))
		|| stristr($name, "..")
		|| stristr($name, "./")
		|| !file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . $name)
	) {
		exit(json_encode(['status' => '2', 'message' => 'Загрузка файла невозможна']));
	}

	if($host == 'test.worksma.ru') {
		exit(json_encode(['status' => '2', 'message' => 'В тестовом движке редактирование шаблонов запрещено!']));
	}

	if(isset($_POST['save_code'])) {
		$content = $_POST['content'];

		$name = "../" . $name;

		if(!is_writable($name)) {
			exit(
			json_encode(
				['status' => '2', 'message' => 'Сохранение невозможно! Установите необходимые права на файл ' . $name]
			)
			);
		}

		if(check_for_php($content)) {
			exit(
			json_encode(
				[
					'status'  => '2',
					'message' => 'Использование PHP кода в режиме безопасной эксплуатации запрещено, используйте синтаксис шаблонизатора:  https://worksma.ru/wiki/template_syntax'
				]
			)
			);
		}

		$content = magic_quotes($content);
		$content = str_replace('mytextarea', 'textarea', $content);

		$file = fopen($name, "w+");
		fwrite($file, $content);
		fclose($file);

		$tpl = new Template;
		$tpl->dell_cache($conf->template);
		unset($tpl);

		exit(json_encode(['status' => '1']));
	} else {
		$warning = '';
		$content = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/" . $name);
		if(substr_count($content, 'textarea') > 0) {
			$content = str_replace('textarea', 'mytextarea', $content);
			$warning = 'Внимание! В данном коде тег "textarea" заменен на "mytextarea". При сохранении, "mytextarea" будет заменен на "textarea", что обеспечит корректную работу кода в шаблоне. При написании кода используйте "mytextarea" вместо "textarea".';
		}

		exit(json_encode(['status' => '1', 'content' => $content, 'warning' => $warning]));
	}
}
/* Новости
=========================================*/
if(isset($_POST['load_news_adm'])) {
	$i   = 1;
	$STH = $pdo->query(
		'SELECT news.id,news__classes.name AS class,news.new_name,news.img,news.short_text,news.date,news.author,news.views,users.login,users.id AS user_id FROM news LEFT JOIN users ON news.author = users.id LEFT JOIN news__classes ON news.class = news__classes.id ORDER BY news.date DESC'
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		?>
		<tr id="<?php echo $row->id ?>">
			<td><?php echo $i ?></td>
			<td><a target="_blank" href="../news/new?id=<?php echo $row->id ?>"><?php echo $row->new_name ?></a></td>
			<td><?php echo $row->class ?></td>
			<td><?php echo expand_date($row->date, 1) ?></td>
			<td>
				<a target="_blank" href="../admin/edit_user?id=<?php echo $row->user_id ?>"><?php echo $row->login ?></a>
			</td>
			<td>
				<div class="btn-group-vertical w-100">
					<a class="c-333" target="_blank" href="../news/change_new?id=<?php echo $row->id ?>">
						<div class="btn btn-default w-100">
							<span class="glyphicon glyphicon-pencil"></span> Редактировать
						</div>
					</a>
					<div onclick='dell_new("<?php echo $row->id ?>");' class="btn btn-default">
						<span class="glyphicon glyphicon-trash"></span> Удалить
					</div>
				</div>
			</td>
		</tr>
		<?php
		$i++;
	}
}

/* Сервера
=========================================*/
if(isset($_POST['server_act'])) {
	if(!$func_type = check_function($_POST['func_type'], 'add,edit')) {
		return_html("Ошибка.", 2, 1);
	}

	$SM              = new ServersManager;
	$name            = check($_POST['name'], null);
	$address2        = check($_POST['address'], null);
	$ip              = check($_POST['ip'], null);
	$port            = check($_POST['port'], null);
	$ftp_host        = check($_POST['ftp_host'], null);
	$ftp_port        = check($_POST['ftp_port'], null);
	$ftp_login       = check($_POST['ftp_login'], null);
	$ftp_pass        = check($_POST['ftp_pass'], null);
	$ftp_string      = check($_POST['ftp_string'], null);
	$db_host         = check($_POST['db_host'], null);
	$db_user         = check($_POST['db_user'], null);
	$db_pass         = check($_POST['db_pass'], null);
	$db_db           = check($_POST['db_db'], null);
	$db_code         = check($_POST['db_code'], "int");
	$db_prefix       = check($_POST['db_prefix'], null);
	$type            = checkJs($_POST['type'], "int");
	$game            = checkJs($_POST['game'], "int");
	$st_type         = checkJs($_POST['st_type'], "int");
	$st_db_host      = check($_POST['st_db_host'], null);
	$st_db_user      = check($_POST['st_db_user'], null);
	$st_db_pass      = check($_POST['st_db_pass'], null);
	$st_db_db        = check($_POST['st_db_db'], null);
	$st_db_code      = check($_POST['st_db_code'], "int");
	$st_sort_type    = check($_POST['st_sort_type'], "int");
	$st_db_table     = check($_POST['st_db_table'], null);
	$show            = check($_POST['show'], "int");
	$pass_prifix     = check($_POST['pass_prifix'], null);
	$discount        = check($_POST['discount'], "int");
	$bind_nick_pass  = check($_POST['bind_nick_pass'], null);
	$bind_steam      = check($_POST['bind_steam'], null);
	$bind_steam_pass = check($_POST['bind_steam_pass'], null);

	if($func_type == 'edit') {
		$id = checkJs($_POST['id'], "int");

		if(empty($id)) {
			return_html("Не указан ID.");
		}
	}

	if(empty($discount) || $discount > 99) {
		$discount = 0;
	}
	if(empty($show)) {
		$show = 1;
	}
	if(empty($type)) {
		$type = 0;
	}
	if(empty($st_type)) {
		$st_type = 0;
	}
	if(empty($db_prefix)) {
		$db_prefix = 0;
	}
	if(empty($ftp_port)) {
		$ftp_port = 21;
	}
	if(empty($db_code)) {
		$db_code = 0;
	}
	if(empty($st_db_code)) {
		$st_db_code = 0;
	}
	if(empty($st_sort_type)) {
		$st_sort_type = 0;
	}
	if(empty($st_db_table)) {
		$st_db_table = 0;
	}
	if(empty($pass_prifix)) {
		$pass_prifix = "_pw";
	}
	if(empty($bind_nick_pass) || $bind_nick_pass == 'false') {
		$bind_nick_pass = 0;
	} else {
		$bind_nick_pass = 1;
	}
	if(empty($bind_steam) || $bind_steam == 'false') {
		$bind_steam = 0;
	} else {
		$bind_steam = 1;
	}
	if(empty($bind_steam_pass) || $bind_steam_pass == 'false') {
		$bind_steam_pass = 0;
	} else {
		$bind_steam_pass = 1;
	}

	if(empty($name) or empty($ip) or empty($port) or (empty($game) and $game != 0)) {
		return_html("Основные настройки: Игра, Название, Ip, Port - обязательны для заполнения.", 2, 1);
	}
	if(mb_strlen($name, 'UTF-8') > 255) {
		return_html("Основные настройки: Название должно состоять не более чем из 255 символов.", 2, 1);
	}
	if(mb_strlen($ip, 'UTF-8') > 30) {
		return_html("Основные настройки: Ip должен состоять не более чем из 30 символов.", 2, 1);
	}
	if(mb_strlen($port, 'UTF-8') > 5) {
		return_html("Основные настройки: Port должен состоять не более чем из 5 символов.", 2, 1);
	}
	if(!$SM->check_types($type, $st_type)) {
		return_html("Основные настройки: Неверный тип.", 2, 1);
	}
	if($type == '4' || $type == '6') {
		$bind_nick_pass = 0;
	}
	if($bind_nick_pass == 0 && $bind_steam == 0 && $bind_steam_pass == 0) {
		return_html("Основные настройки: Хотя бы один способ привязки должен быть активен.", 2, 1);
	}
	if(empty($address2)) {
		$address2 = $ip . ':' . $port;
	}

	$binds   = $bind_nick_pass . ';' . $bind_steam . ';' . $bind_steam_pass . ';';
	$game    = $SM->switch_game($game);
	$address = $ip . ':' . $port;
	if($type == '0') {
		$ftp_host   = 0;
		$ftp_login  = 0;
		$ftp_pass   = 0;
		$ftp_port   = 21;
		$db_host    = 0;
		$db_user    = 0;
		$db_pass    = 0;
		$db_db      = 0;
		$db_code    = 0;
		$db_prefix  = 0;
		$ftp_string = '';
	}
	if($st_type == '0') {
		$st_db_host   = 0;
		$st_db_user   = 0;
		$st_db_pass   = 0;
		$st_db_db     = 0;
		$st_db_code   = 0;
		$st_sort_type = 1;
		$st_db_table  = 0;
	}

	if($func_type == 'add') {
		$STH = $pdo->query("SELECT id FROM servers WHERE ip='$ip' and port='$port' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(isset($row->id)) {
			return_html("Основные настройки: Такой сервер уже существует.", 2, 1);
		}
	}

	if($type == '1') {
		if(empty($ftp_host) or empty($ftp_login) or empty($ftp_pass) or empty($ftp_string)) {
			return_html(
				"Дополнительные настройки: FTP хост, FTP логин, FTP пароль, Путь до файла - обязательны для заполнения.",
				2,
				1
			);
		} else {
			if(substr($ftp_string, -1) == '/') {
				$ftp_string = substr($ftp_string, 0, -1);
			}

			if(!$ftp_connection = $SM->ftp_connection($ftp_host, $ftp_port, $ftp_login, $ftp_pass, 'EDIT_SERVER')) {
				return_html("Дополнительные настройки: Не удалось подключиться к FTP серверу.", 2, 1);
			}
			if(!$SM->check_users_file($ftp_string)) {
				return_html("Дополнительные настройки: Неверно введен путь до файла.", 2, 1);
			}
			if(!$SM->find_users_file($ftp_connection, $ftp_string)) {
				return_html("Дополнительные настройки: Не удалось обнаружить файл на FTP сервере.", 2, 1);
			}
			$SM->close_ftp($ftp_connection);
		}
		$db_host   = 0;
		$db_user   = 0;
		$db_pass   = 0;
		$db_db     = 0;
		$db_code   = 0;
		$db_prefix = 0;
	}
	if($type == '2' || $type == '5') {
		if(empty($db_host) or empty($db_user) or empty($db_pass) or empty($db_db) or empty($db_prefix)) {
			return_html(
				"Дополнительные настройки: db хост, db логин, db пароль, db таблица, db префикс - обязательны для заполнения.",
				2,
				1
			);
		} else {
			if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
				return_html("Дополнительные настройки: Ошибка подключения к базе данных.", 2, 1);
			}
			set_names($pdo2, $db_code);
			$table = set_prefix($db_prefix, "serverinfo");
			if(!check_table($table, $pdo2)) {
				return_html("Дополнительные настройки: Структура базы не соответствует данному типу интеграции.", 2, 1);
			}
			$STH = $pdo2->query("SELECT id FROM $table WHERE address='$address' LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$row = $STH->fetch();
			if(empty($row->id)) {
				return_html("Дополнительные настройки: Сервер не найден в базе данных AmxBans/CsBans.", 2, 1);
			}

			$table = set_prefix($db_prefix, "amxadmins");
			if(!check_column($table, $pdo2, 'gamecms')) {
				$pdo2->exec("ALTER TABLE `$table` ADD `gamecms` TEXT");
			}

			$table = set_prefix($db_prefix, "bans");
			if(!check_column($table, $pdo2, 'unban_type')) {
				$pdo2->exec("ALTER TABLE `$table` ADD `unban_type` INT(1) NULL DEFAULT NULL AFTER `expired`");
			}
			if(!check_column($table, $pdo2, 'ban_closed')) {
				$pdo2->exec("ALTER TABLE `$table` ADD `ban_closed` INT(7) NULL DEFAULT NULL AFTER `unban_type`");
			}
		}

		$ftp_host   = 0;
		$ftp_login  = 0;
		$ftp_pass   = 0;
		$ftp_port   = 21;
		$ftp_string = '';
	}
	if($type == '3') {
		if(empty($ftp_host) or empty($ftp_login) or empty($ftp_pass) or empty($ftp_string) or empty($db_host) or empty($db_user) or empty($db_pass) or empty($db_db) or empty($db_prefix)) {
			return_html(
				"Дополнительные настройки: FTP хост, FTP логин, FTP пароль, Путь до файла, db хост, db логин, db пароль, db таблица, db префикс - обязательны для заполнения.",
				2,
				1
			);
		} else {
			if(!$ftp_connection = $SM->ftp_connection($ftp_host, $ftp_port, $ftp_login, $ftp_pass, 'EDIT_SERVER')) {
				return_html("Дополнительные настройки: Не удалось подключиться к FTP серверу.", 2, 1);
			}
			if(!$SM->check_users_file($ftp_string)) {
				return_html("Дополнительные настройки: Неверно введен путь до файла.", 2, 1);
			}
			if(!$SM->find_users_file($ftp_connection, $ftp_string)) {
				return_html("Дополнительные настройки: Не удалось обнаружить файл на FTP сервере.", 2, 1);
			}
			$SM->close_ftp($ftp_connection);

			if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
				return_html("Дополнительные настройки: Ошибка подключения к базе данных.", 2, 1);
			}
			set_names($pdo2, $db_code);
			$table = set_prefix($db_prefix, "serverinfo");
			if(!check_table($table, $pdo2)) {
				return_html("Дополнительные настройки: Структура базы не соответствует данному типу интеграции.", 2, 1);
			}
			$STH = $pdo2->query("SELECT id FROM $table WHERE address='$address' LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$row = $STH->fetch();
			if(empty($row->id)) {
				return_html("Дополнительные настройки: Сервер не найден в базе данных AmxBans/CsBans.", 2, 1);
			}

			$table = set_prefix($db_prefix, "bans");
			if(!check_column($table, $pdo2, 'unban_type')) {
				$pdo2->exec("ALTER TABLE `$table` ADD `unban_type` INT(1) NULL DEFAULT NULL AFTER `expired`");
			}
			if(!check_column($table, $pdo2, 'ban_closed')) {
				$pdo2->exec("ALTER TABLE `$table` ADD `ban_closed` INT(7) NULL DEFAULT NULL AFTER `unban_type`");
			}
		}
	}
	if($type == '4' || $type == '6') {
		if(empty($db_host) or empty($db_user) or empty($db_pass) or empty($db_db) or empty($db_prefix)) {
			return_html(
				"Дополнительные настройки: db хост, db логин, db пароль, db таблица, db префикс - обязательны для заполнения.",
				2,
				1
			);
		} else {
			if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
				return_html("Дополнительные настройки: Ошибка подключения к базе данных.", 2, 1);
			}
			set_names($pdo2, $db_code);
			$table = set_prefix($db_prefix, "servers");
			if(!check_table($table, $pdo2)) {
				return_html("Дополнительные настройки: Структура базы не соответствует данному типу интеграции.", 2, 1);
			}
			$STH = $pdo2->query("SELECT sid FROM $table WHERE ip='$ip' and port='$port' LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$row = $STH->fetch();
			if(empty($row->sid)) {
				return_html("Дополнительные настройки: Сервер не найден в базе данных SourceBans/MaterialAdmin.", 2, 1);
			}
			$table = set_prefix($db_prefix, "admins");
			if(!check_column($table, $pdo2, 'gamecms')) {
				$pdo2->exec("ALTER TABLE `$table` ADD `gamecms` TEXT");
			}
			if(!check_column($table, $pdo2, 'user_id')) {
				$pdo2->exec("ALTER TABLE `$table` ADD `user_id` INT(5) DEFAULT '0'");
			}
			if(!check_column($table, $pdo2, 'nick')) {
				$pdo2->exec("ALTER TABLE `$table` ADD `nick` VARCHAR(30) DEFAULT ''");
			}

			$table = set_prefix($db_prefix, "bans");
			if(!check_column($table, $pdo2, 'unban_type')) {
				$pdo2->exec("ALTER TABLE `$table` ADD `unban_type` INT(1) NULL DEFAULT NULL AFTER `sid`");
			}
			if(!check_column($table, $pdo2, 'ban_closed')) {
				$pdo2->exec("ALTER TABLE `$table` ADD `ban_closed` INT(7) NULL DEFAULT NULL AFTER `unban_type`");
			}

			$table = set_prefix($db_prefix, "comms");
			if(check_table($table, $pdo2)) {
				if(!check_column($table, $pdo2, 'unban_type')) {
					$pdo2->exec("ALTER TABLE `$table` ADD `unban_type` INT(1) NULL DEFAULT NULL AFTER `sid`");
				}
				if(!check_column($table, $pdo2, 'ban_closed')) {
					$pdo2->exec("ALTER TABLE `$table` ADD `ban_closed` INT(7) NULL DEFAULT NULL AFTER `unban_type`");
				}
			}
		}

		$ftp_host   = 0;
		$ftp_login  = 0;
		$ftp_pass   = 0;
		$ftp_port   = 21;
		$ftp_string = '';
	}
	if($st_type != '0') {
		if(empty($st_db_host) or empty($st_db_user) or empty($st_db_pass) or empty($st_db_db)) {
			return_html(
				"Настройки статистики: db хост, db логин, db пароль, db база - обязательны для заполнения.",
				2,
				1
			);
		}
		if(!$pdo2 = db_connect($st_db_host, $st_db_db, $st_db_user, $st_db_pass)) {
			return_html("Настройки статистики: Ошибка подключения к базе данных.", 2, 1);
		}
		set_names($pdo2, $st_db_code);
		if(
			(($st_sort_type == 5 || $st_sort_type == 6) && ($st_type != 1 && $st_type != 2))
			|| (!in_array($st_sort_type, [0, 1, 2, 3, 4, 5, 6, 7, 8]))
			|| (in_array($st_type, [1, 2, 3, 4, 5]) && in_array($st_sort_type, [7, 8]))
		) {
			return_html("Настройки статистики: Неверный способ сортировки.", 2, 1);
		}

		if($st_type == '1') {
			if(!check_table('csstats_players', $pdo2) or !check_table('csstats_settings', $pdo2)) {
				return_html("Настройки статистики: Структура базы не соответствует данному типу интеграции.", 2, 1);
			}
		} elseif($st_type == '2') {
			if(!check_table('csstats_players', $pdo2) or !check_table('csstats_settings', $pdo2)) {
				return_html("Настройки статистики: Структура базы не соответствует данному типу интеграции.", 2, 1);
			}
			$STH = $pdo2->prepare("SELECT value FROM csstats_settings WHERE command=:command LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute([':command' => 'army_enable']);
			$row = $STH->fetch();
			if($row->value != 1 && $row->value != 2 && $row->value != -1) {
				return_html("Настройки статистики: В данной статистике не используется Army Ranks Ultimate.", 2, 1);
			}
		} elseif($st_type == '4') {
			if(!check_table('hlstats_Players', $pdo2) or !check_table('hlstats_Servers', $pdo2)) {
				return_html("Настройки статистики: Структура базы не соответствует данному типу интеграции.", 2, 1);
			}

			$STH = $pdo2->query(
				"SELECT `serverId` FROM `hlstats_Servers` WHERE address='$ip' and port='$port' LIMIT 1"
			);
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$row = $STH->fetch();
			if(empty($row->serverId)) {
				return_html("Настройки статистики: Сервер не найден в базе данных HLstatsX.", 2, 1);
			}
		} elseif($st_type == '3' || $st_type == '5' || $st_type == '6') {
			if(empty($st_db_table)) {
				return_html("Настройки статистики: db таблица - обязательна для заполнения.", 2, 1);
			}
			if(!check_table($st_db_table, $pdo2)) {
				return_html("Настройки статистики: Структура базы не соответствует данному типу интеграции.", 2, 1);
			}
		} else {
			return_html("Настройки статистики: Неверный тип интеграции.", 2, 1);
		}
	}

	if($func_type == 'add') {
		$STH = $pdo->query("SELECT trim FROM servers ORDER BY trim DESC LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(isset($row->trim)) {
			$trim = $row->trim + 1;
		} else {
			$trim = 1;
		}

		$STH = $pdo->prepare(
			"INSERT INTO servers (discount,`show`,name,address,ip,port,type,ftp_port,ftp_host,ftp_login,ftp_pass,db_host,db_user,db_pass,db_db,db_prefix,trim,game,db_code,ftp_string,st_type,st_db_host,st_db_user,st_db_pass,st_db_db,st_db_code,st_sort_type,st_db_table,pass_prifix,binds) VALUES (:discount, :show, :name, :address, :ip, :port, :type, :ftp_port, :ftp_host, :ftp_login, :ftp_pass, :db_host, :db_user, :db_pass, :db_db, :db_prefix, :trim, :game, :db_code, :ftp_string, :st_type, :st_db_host, :st_db_user, :st_db_pass, :st_db_db, :st_db_code, :st_sort_type, :st_db_table, :pass_prifix, :binds)"
		);
		if(
			$STH->execute(
				[
					'discount'     => $discount,
					'show'         => $show,
					'name'         => $name,
					'address'      => $address2,
					'ip'           => $ip,
					'port'         => $port,
					'type'         => $type,
					'ftp_port'     => $ftp_port,
					'ftp_host'     => $ftp_host,
					'ftp_login'    => $ftp_login,
					'ftp_pass'     => $ftp_pass,
					'db_host'      => $db_host,
					'db_user'      => $db_user,
					'db_pass'      => $db_pass,
					'db_db'        => $db_db,
					'db_prefix'    => $db_prefix,
					'trim'         => $trim,
					'game'         => $game,
					'db_code'      => $db_code,
					'ftp_string'   => $ftp_string,
					'st_type'      => $st_type,
					'st_db_host'   => $st_db_host,
					'st_db_user'   => $st_db_user,
					'st_db_pass'   => $st_db_pass,
					'st_db_db'     => $st_db_db,
					'st_db_code'   => $st_db_code,
					'st_sort_type' => $st_sort_type,
					'st_db_table'  => $st_db_table,
					'pass_prifix'  => $pass_prifix,
					'binds'        => $binds
				]
			) == '1'
		) {
			$server = get_ai($pdo, "servers");
			$server--;

			if(isset($_POST['import_settings']) && $_POST['import_settings'] != 0) {
				$import = check($_POST['import_settings'], "int");

				$STH = $pdo->prepare("SELECT game FROM servers WHERE id=:id LIMIT 1");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute([':id' => $import]);
				$row = $STH->fetch();

				$services = [];
				if(isset($row->game) && $row->game == $game && $type != 0) {
					$STH = $pdo->prepare("SELECT * FROM services WHERE server=:server");
					$STH->setFetchMode(PDO::FETCH_OBJ);
					$STH->execute([':server' => $import]);
					while($row = $STH->fetch()) {
						$STH2 = $pdo->prepare(
							"INSERT INTO services (name,rights,server,text,trim,immunity,sale,users_group,sb_group,show_adm,discount) VALUES (:name, :rights, :server, :text, :trim, :immunity, :sale, :users_group, :sb_group, :show_adm, :discount)"
						);
						$STH2->execute(
							[
								':name'        => $row->name,
								':rights'      => $row->rights,
								':server'      => $server,
								':text'        => $row->text,
								':trim'        => $row->trim,
								':immunity'    => $row->immunity,
								':sale'        => $row->sale,
								':users_group' => $row->users_group,
								':sb_group'    => $row->sb_group,
								':show_adm'    => $row->show_adm,
								':discount'    => $row->discount
							]
						);

						$services[$row->id] = get_ai($pdo, "services") - 1;

						$STH2 = $pdo->prepare("SELECT * FROM services__tarifs WHERE service=:service");
						$STH2->setFetchMode(PDO::FETCH_OBJ);
						$STH2->execute([':service' => $row->id]);
						while($row2 = $STH2->fetch()) {
							$STH3 = $pdo->prepare(
								"INSERT INTO services__tarifs (service,price,time,discount) VALUES (:service, :price, :time, :discount)"
							);
							$STH3->execute(
								[
									':service'  => $services[$row->id],
									':price'    => $row2->price,
									':time'     => $row2->time,
									':discount' => $row2->discount
								]
							);

							$tarifs[$row2->id] = get_ai($pdo, "services__tarifs") - 1;
						}
					}

					$STH = $pdo->prepare("SELECT * FROM admins WHERE server=:server");
					$STH->setFetchMode(PDO::FETCH_OBJ);
					$STH->execute([':server' => $import]);
					while($row = $STH->fetch()) {
						$STH2 = $pdo->prepare(
							"INSERT INTO admins (name,pass,pass_md5,type,server,user_id,active,link,cause,price,pause,comment) values (:name, :pass, :pass_md5, :type, :server, :user_id, :active, :link, :cause, :price, :pause, :comment)"
						);
						$STH2->execute(
							[
								':name'     => $row->name,
								':pass'     => $row->pass,
								':pass_md5' => $row->pass_md5,
								':type'     => $row->type,
								':server'   => $server,
								':user_id'  => $row->user_id,
								':active'   => $row->active,
								':link'     => $row->link,
								':cause'    => $row->cause,
								':price'    => $row->price,
								':pause'    => $row->pause,
								':comment'  => $row->comment
							]
						);

						$admin_id = get_ai($pdo, "admins") - 1;

						$STH2 = $pdo->prepare("SELECT * FROM admins__services WHERE admin_id=:admin_id");
						$STH2->setFetchMode(PDO::FETCH_OBJ);
						$STH2->execute([':admin_id' => $row->id]);
						while($row2 = $STH2->fetch()) {
							if(array_key_exists($row2->service, $services)) {
								$service = $services[$row2->service];
							} else {
								$service = 0;
							}

							if(array_key_exists($row2->service_time, $tarifs)) {
								$tarif = $tarifs[$row2->service_time];
							} else {
								$tarif = 0;
							}

							$STH3 = $pdo->prepare(
								"INSERT INTO admins__services (admin_id,service,service_time,bought_date,ending_date,irretrievable,rights_und,immunity_und,sb_group_und,previous_group) VALUES (:admin_id, :service, :service_time, :bought_date, :ending_date, :irretrievable, :rights_und, :immunity_und, :sb_group_und, :previous_group)"
							);
							$STH3->execute(
								[
									':admin_id'       => $admin_id,
									':service'        => $service,
									':service_time'   => $tarif,
									':bought_date'    => $row2->bought_date,
									':ending_date'    => $row2->ending_date,
									':irretrievable'  => $row2->irretrievable,
									':rights_und'     => $row2->rights_und,
									':immunity_und'   => $row2->immunity_und,
									':sb_group_und'   => $row2->sb_group_und,
									':previous_group' => $row2->previous_group
								]
							);
						}
					}

					$AM = new AdminsManager;
					$AM->export_admins($server, $pdo);
				}
			}

			$ServerCommands = new ServerCommands();

			if($type == '0' || $type == '1' || $type == '2' || $type == '3' || $type == '5') {
				$commandPrefix = 'amx';
			} else {
				$commandPrefix = 'sm';
			}

			$commands = [
				$commandPrefix . '_reloadadmins' => [
					'title'    => 'Перезагрузка списка админов',
					'slug'     => ServerCommands::RELOAD_ADMINS_COMMAND_SLUG,
					'category' => ServerCommands::CATEGORY_SYSTEM,
					'params'   => []
				],
				$commandPrefix . '_kick'         => [
					'title'    => 'Кик',
					'slug'     => 'kick',
					'category' => ServerCommands::CATEGORY_ACTIONS_ON_PLAYERS,
					'params'   => ['nick' => 'Ник', 'reason' => 'Причина']
				],
				$commandPrefix . '_ban'          => [
					'title'    => 'Бан',
					'slug'     => 'ban',
					'category' => ServerCommands::CATEGORY_ACTIONS_ON_PLAYERS,
					'params'   => [
						'nick'   => 'Ник',
						'time'   => 'Время',
						'reason' => 'Причина'
					]
				]
			];

			foreach($commands as $commandName => $command) {
				$ServerCommands->addCommand(
					$commandName,
					$server,
					$command['title'],
					$command['slug'],
					$command['category']
				);

				$commandId = get_ai($pdo, 'servers__commands') - 1;

				foreach($command['params'] as $paramName => $paramTitle) {
					$ServerCommands->addCommandParam($commandId, $paramName, $paramTitle);
				}
			}

			return_html("Сервер успешно добавлен.", 1, 1);
		}
	} else {
		$STH = $pdo->prepare(
			"UPDATE servers SET `show`=:show,discount=:discount,name=:name,address=:address,ip=:ip,port=:port,type=:type,ftp_port=:ftp_port,ftp_host=:ftp_host,ftp_login=:ftp_login,ftp_pass=:ftp_pass,db_host=:db_host,db_user=:db_user,db_pass=:db_pass,db_db=:db_db,db_prefix=:db_prefix,game=:game,db_code=:db_code,ftp_string=:ftp_string,st_type=:st_type,st_db_host=:st_db_host,st_db_user=:st_db_user,st_db_pass=:st_db_pass,st_db_db=:st_db_db,st_db_code=:st_db_code,st_sort_type=:st_sort_type,st_db_table=:st_db_table,pass_prifix=:pass_prifix,binds=:binds WHERE id='$id' LIMIT 1"
		);
		if($STH->execute(
				[
					'show'         => $show,
					'discount'     => $discount,
					'name'         => $name,
					'address'      => $address2,
					'ip'           => $ip,
					'port'         => $port,
					'type'         => $type,
					'ftp_port'     => $ftp_port,
					'ftp_host'     => $ftp_host,
					'ftp_login'    => $ftp_login,
					'ftp_pass'     => $ftp_pass,
					'db_host'      => $db_host,
					'db_user'      => $db_user,
					'db_pass'      => $db_pass,
					'db_db'        => $db_db,
					'db_prefix'    => $db_prefix,
					'game'         => $game,
					'db_code'      => $db_code,
					'ftp_string'   => $ftp_string,
					'st_type'      => $st_type,
					'st_db_host'   => $st_db_host,
					'st_db_user'   => $st_db_user,
					'st_db_pass'   => $st_db_pass,
					'st_db_db'     => $st_db_db,
					'st_db_code'   => $st_db_code,
					'st_sort_type' => $st_sort_type,
					'st_db_table'  => $st_db_table,
					'pass_prifix'  => $pass_prifix,
					'binds'        => $binds
				]
			) == '1') {

			return_html("Сервер успешно отредактирован.", 1, 2);
		}
	}

	return_html("Ошибка.", 2, 1);
}
if(isset($_POST['load_servers'])) {
	$i   = 0;
	$STH = $pdo->query("SELECT * FROM servers ORDER BY trim");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$binds = explode(';', $row->binds);
		?>
		<div id="serv_<?php echo $row->id ?>" class="block pd-5 pt-0">
			<div class="block_head">ID сервера <?php echo $row->id ?></div>
			<div class="col-md-4 mb-10">
				<b>Основные настройки</b>
				<div class="form-group">
					<small>Игра</small>
					<select class="form-control" id="game<?php echo $row->id; ?>" onchange="local_change_game(<?php echo $row->id; ?>);">
						<option value="0"<?php if($row->game == 'Counter-Strike: 1.6'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Counter-Strike: 1.6
						</option>
						<option value="1"<?php if($row->game == 'Counter-Strike: Source'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Counter-Strike: Source
						</option>
						<option value="2"<?php if($row->game == 'Counter-Strike: Global Offensive'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Counter-Strike: Global Offensive
						</option>
						<option value="3"<?php if($row->game == 'Alien Swarm'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Alien Swarm
						</option>
						<option value="4"<?php if($row->game == 'CSPromod'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>CSPromod
						</option>
						<option value="5"<?php if($row->game == 'Day of Defeat: Source'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Day of Defeat: Source
						</option>
						<option value="6"<?php if($row->game == 'Dystopia'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Dystopia
						</option>
						<option value="7"<?php if($row->game == 'E.Y.E: Divine Cybermancy'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>E.Y.E: Divine Cybermancy
						</option>
						<option value="8"<?php if($row->game == 'Fortress Forever'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Fortress Forever
						</option>
						<option value="9"<?php if($row->game == "Garry's Mod"){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Garry's Mod
						</option>
						<option value="10"<?php if($row->game == 'Half-Life 2 Deathmatch'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Half-Life 2 Deathmatch
						</option>
						<option value="11"<?php if($row->game == 'Half-Life 2 Capture the Flag'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Half-Life 2 Capture the Flag
						</option>
						<option value="12"<?php if($row->game == 'Hidden: Source'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Hidden: Source
						</option>
						<option value="13"<?php if($row->game == 'Insurgency: Source'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Insurgency: Source
						</option>
						<option value="14"<?php if($row->game == 'Left 4 Dead 2'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Left 4 Dead 2
						</option>
						<option value="15"<?php if($row->game == 'Left 4 Dead'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Left 4 Dead
						</option>
						<option value="16"<?php if($row->game == 'Nuclear Dawn'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Nuclear Dawn
						</option>
						<option value="17"<?php if($row->game == 'Perfect Dark: Source'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Perfect Dark: Source
						</option>
						<option value="18"<?php if($row->game == 'Pirates Vikings and Knights II'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Pirates Vikings and Knights II
						</option>
						<option value="19"<?php if($row->game == 'Team Fortress 2'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Team Fortress 2
						</option>
						<option value="20"<?php if($row->game == 'The Ship'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>The Ship
						</option>
						<option value="21"<?php if($row->game == 'Zombie Panic'){ ?> selected>
							<script>local_change_game(<?php echo $row->id ?>, 1);</script> <?php } else { ?>><?php } ?>Zombie Panic
						</option>
					</select>
				</div>
				<div class="form-group">
					<small>Название</small>
					<input value="<?php echo $row->name ?>" placeholder="Название сервера" type="text" class="form-control" id="name<?php echo $row->id ?>" maxlength="255" autocomplete="off">
				</div>
				<div class="form-group">
					<small>Ip</small>
					<input value="<?php echo $row->ip ?>" placeholder="IP адрес сервера" type="text" class="form-control" id="ip<?php echo $row->id ?>" maxlength="30" autocomplete="off">
				</div>
				<div class="form-group">
					<small>Port</small>
					<input value="<?php echo $row->port ?>" placeholder="Port сервера" type="text" class="form-control" id="port<?php echo $row->id ?>" maxlength="5" autocomplete="off">
				</div>
				<div class="form-group">
					<small>Отображаемый адрес</small>
					<input value="<?php echo $row->address ?>" placeholder="IP:Port" type="text" class="form-control" id="address<?php echo $row->id ?>" maxlength="255" autocomplete="off">
				</div>
				<div class="form-group">
					<small>Скидка на услуги в %</small>
					<input value="<?php echo $row->discount ?>" placeholder="От 0 до 99" type="number" class="form-control" id="discount<?php echo $row->id ?>" maxlength="2" autocomplete="off">
				</div>
				<div class="form-group">
					<small>Отображение в мониторинге</small>
					<select class="form-control" id="show<?php echo $row->id; ?>">
						<option value="1"<?php if($row->show == '1'){ ?> selected <?php } ?>>Показывать</option>
						<option value="2"<?php if($row->show == '2'){ ?> selected <?php } ?>>Скрывать</option>
					</select>
				</div>
				<div class="form-group">
					<small>Способы привязки услуг</small>
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default btn-sm <?php if($binds[0]) { ?>active<?php } ?>" id="bind_nick_pass_btn<?php echo $row->id; ?>" for="bind_nick_pass<?php echo $row->id; ?>">
							<input type="checkbox" id="bind_nick_pass<?php echo $row->id; ?>" autocomplete="off"> Ник + пароль
						</label>
						<label class="btn btn-default btn-sm <?php if($binds[1]) { ?>active<?php } ?>" id="bind_steam_btn<?php echo $row->id; ?>" for="bind_steam<?php echo $row->id; ?>">
							<input type="checkbox" id="bind_steam<?php echo $row->id; ?>" autocomplete="off"> STEAM ID
						</label>
						<label class="btn btn-default btn-sm <?php if($binds[2]) { ?>active<?php } ?>" id="bind_steam_pass_btn<?php echo $row->id; ?>" for="bind_steam_pass<?php echo $row->id; ?>">
							<input type="checkbox" id="bind_steam_pass<?php echo $row->id; ?>" autocomplete="off"> STEAM ID + пароль
						</label>
					</div>

					<script>
						<?php if(!$binds[0]){ ?>
                        $("#bind_nick_pass_btn<?php echo $row->id; ?>").removeClass('active');
						<?php } ?>
                        $("#bind_nick_pass<?php echo $row->id; ?>").prop('checked', <?php if($binds[0]){ ?>true<?php } else { ?>false<?php } ?>);
                        $("#bind_steam<?php echo $row->id; ?>").prop('checked', <?php if($binds[1]){ ?>true<?php } else { ?>false<?php } ?>);
                        $("#bind_steam_pass<?php echo $row->id; ?>").prop('checked', <?php if($binds[2]){ ?>true<?php } else { ?>false<?php } ?>);
					</script>
				</div>
			</div>
			<div class="col-md-4 mb-10">
				<b>Дополнительные настройки</b>
				<div class="form-group">
					<small>Интеграция</small>
					<select class="form-control" id="type<?php echo $row->id ?>" onchange="local_change_type(<?php echo $row->id ?>);">
						<option id="opt0_<?php echo $row->id ?>" value="0" <?php if($row->type == 0){ ?>selected>
							<script>select_serv_type(0, <?php echo $row->id ?>);</script><?php } else { ?>><?php } ?>Нет настроек
						</option>
						<option id="opt1_<?php echo $row->id ?>" value="1" <?php if($row->type == 1){ ?>selected>
							<script>select_serv_type(1, <?php echo $row->id ?>);</script><?php } else { ?>><?php } ?>Файл (Users.ini)
						</option>
						<option id="opt2_<?php echo $row->id ?>" value="2" <?php if($row->type == 2){ ?>selected>
							<script>select_serv_type(2, <?php echo $row->id ?>);</script><?php } else { ?>><?php } ?>AmxBans/CsBans
						</option>
						<option id="opt3_<?php echo $row->id ?>" value="3" <?php if($row->type == 3){ ?>selected>
							<script>select_serv_type(3, <?php echo $row->id ?>);</script><?php } else { ?>><?php } ?>AmxBans/CsBans + файл
						</option>
						<option id="opt4_<?php echo $row->id ?>" value="4" <?php if($row->type == 4){ ?>selected>
							<script>select_serv_type(4, <?php echo $row->id ?>);</script><?php } else { ?>><?php } ?>SourceBans/MaterialAdmin
						</option>
						<option id="opt5_<?php echo $row->id ?>" value="5" <?php if($row->type == 5){ ?>selected>
							<script>select_serv_type(5, <?php echo $row->id ?>);</script><?php } else { ?>><?php } ?>AmxBans/CsBans + GameCMS API
						</option>
						<!--<option id="opt6_<?php echo $row->id ?>" value="66" <?php if($row->type == 6) { ?>selected><script>select_serv_type(6, <?php echo $row->id ?>);</script><?php } else { ?>><?php } ?>SourceBans/MaterialAdmin + GameCMS API</option>-->
					</select>
				</div>
				<div id="none_<?php echo $row->id ?>">
					<div class="bs-callout bs-callout-info bs-callout-sm mt-5">
						<p>Чтение/запись привилегий и банов не осуществляется</p>
					</div>
				</div>
				<div id="tip1_<?php echo $row->id ?>" class="disp-n">
					<div class="bs-callout bs-callout-info bs-callout-sm mt-5">
						<h4>Поддержка: привилегии</h4>
						<p>Для чтения/записи привилегий используется файл</p>
					</div>
				</div>
				<div id="tip2_<?php echo $row->id ?>" class="disp-n">
					<div class="bs-callout bs-callout-info bs-callout-sm mt-5">
						<h4>Поддержка: привилегии и баны</h4>
						<p>Для чтения/записи банов и привилегий используется база данных от AmxBans/CsBans</p>
					</div>
				</div>
				<div id="tip3_<?php echo $row->id ?>" class="disp-n">
					<div class="bs-callout bs-callout-info bs-callout-sm mt-5">
						<h4>Поддержка: привилегии и баны</h4>
						<p>Для чтения/записи привилегий используется файл, для чтения/записи банов используется база данных от AmxBans/CsBans</p>
					</div>
				</div>
				<div id="tip4_<?php echo $row->id ?>" class="disp-n">
					<div class="bs-callout bs-callout-info bs-callout-sm mt-5">
						<h4>Поддержка: привилегии и баны</h4>
						<p>
							Для чтения/записи банов и привилегий используется база данных от
							SourceBans/<a href="https://github.com/SB-MaterialAdmin" target="_blank">MaterialAdmin</a>
						</p>
					</div>
				</div>
				<div id="tip5_<?php echo $row->id ?>" class="disp-n">
					<div class="bs-callout bs-callout-info bs-callout-sm mt-5">
						<h4>Поддержка: привилегии и баны</h4>
						<p>
							Для чтения/записи привилегий используется база данных текущего сайта, для чтения/записи банов используется база данных от
							AmxBans/CsBans. Данный тип интеграции требует установку плагина
							<a href="https://worksma.ru/wiki/game-plugins" target="_blank">GameCMS API(amx)</a> на игровой сервер
						</p>
					</div>
				</div>
				<div id="tip6_<?php echo $row->id ?>" class="disp-n">
					<div class="bs-callout bs-callout-info bs-callout-sm mt-5">
						<h4>Поддержка: привилегии и баны</h4>
						<p>
							Для чтения/записи привилегий используется база данных текущего сайта, для чтения/записи банов используется база данных от
							SourceBans/<a href="https://github.com/SB-MaterialAdmin" target="_blank">MaterialAdmin</a>. Данный тип интеграции требует
							установку плагина <a href="https://worksma.ru/wiki/game-plugins" target="_blank">GameCMS API(sm)</a> на игровой сервер
						</p>
					</div>
				</div>
				<div id="auth_prefix<?php echo $row->id ?>" class="disp-n">
					<div class="form-group">
						<small>Префикс для авторизации админа на сервере</small>
						<input value="<?php echo $row->pass_prifix ?>" type="text" class="form-control" id="pass_prifix<?php echo $row->id ?>" maxlength="10" autocomplete="off" placeholder="_pw">
					</div>
				</div>
				<div id="ftp<?php echo $row->id ?>" class="disp-n">
					<div class="form-group">
						<small>FTP хост</small>
						<input value="<?php echo $row->ftp_host ?>" type="text" class="form-control" id="ftp_host<?php echo $row->id ?>" maxlength="64" autocomplete="off">
					</div>
					<div class="form-group">
						<small>FTP порт</small>
						<input value="<?php echo $row->ftp_port ?>" type="text" class="form-control" id="ftp_port<?php echo $row->id ?>" maxlength="5" autocomplete="off">
					</div>
					<div class="form-group">
						<small>FTP логин</small>
						<input value="<?php echo $row->ftp_login ?>" type="text" class="form-control" id="ftp_login<?php echo $row->id ?>" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group">
						<small>FTP пароль</small>
						<input value="<?php echo $row->ftp_pass ?>" type="password" class="form-control" id="ftp_pass<?php echo $row->id ?>" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group">
						<small>Путь до файла (Пример: cstrike/addons/amxmodx/configs/users.ini)</small>
						<input value="<?php echo $row->ftp_string ?>" type="text" class="form-control" id="ftp_string<?php echo $row->id ?>" maxlength="255" autocomplete="off">
					</div>
				</div>
				<div id="db<?php echo $row->id ?>" class="disp-n">
					<div class="form-group">
						<small>db хост</small>
						<input value="<?php echo $row->db_host ?>" type="text" class="form-control" id="db_host<?php echo $row->id ?>" maxlength="64" autocomplete="off">
					</div>
					<div class="form-group">
						<small>db логин</small>
						<input value="<?php echo $row->db_user ?>" type="text" class="form-control" id="db_user<?php echo $row->id ?>" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group">
						<small>db пароль</small>
						<input value="<?php echo $row->db_pass ?>" type="password" class="form-control" id="db_pass<?php echo $row->id ?>" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group">
						<small>db база</small>
						<input value="<?php echo $row->db_db ?>" type="text" class="form-control" id="db_db<?php echo $row->id ?>" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group">
						<small>db префикс</small>
						<input value="<?php echo $row->db_prefix ?>" type="text" class="form-control" id="db_prefix<?php echo $row->id ?>" maxlength="32" autocomplete="off" placeholder="amx / cs / gm / sb">
					</div>
					<div class="form-group">
						<small>Кодировка</small>
						<select class="form-control" id="db_code<?php echo $row->id ?>">
							<option value="0" <?php if($row->db_code == '0') { ?> selected <?php } ?>>Определять автоматически</option>
							<option value="1" <?php if($row->db_code == '1') { ?> selected <?php } ?>>utf-8</option>
							<option value="2" <?php if($row->db_code == '2') { ?> selected <?php } ?>>latin1</option>
							<option value="3" <?php if($row->db_code == '3') { ?> selected <?php } ?>>utf8mb4</option>
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-4 mb-10">
				<b>Настройки статистики</b>
				<div class="form-group">
					<small>Интеграция</small>
					<select class="form-control" id="st_type<?php echo $row->id ?>" onchange="local_change_type_st(<?php echo $row->id ?>);">
						<option id="st_opt0_<?php echo $row->id ?>" value="0" <?php if($row->st_type == 0){ ?> selected>
							<script>select_stat_type(0, <?php echo $row->id ?>);</script> <?php } else { ?>><?php } ?>Нет настроек
						</option>
						<option id="st_opt1_<?php echo $row->id ?>" value="1" <?php if($row->st_type == 1){ ?> selected>
							<script>select_stat_type(1, <?php echo $row->id ?>);</script><?php } else { ?>><?php } ?>CsStats MySQL
						</option>
						<option id="st_opt2_<?php echo $row->id ?>" value="2" <?php if($row->st_type == 2){ ?> selected>
							<script>select_stat_type(2, <?php echo $row->id ?>);</script><?php } else { ?>><?php } ?>Army Ranks Ultimate
						</option>
						<option id="st_opt3_<?php echo $row->id ?>" value="3" <?php if($row->st_type == 3){ ?> selected>
							<script>select_stat_type(3, <?php echo $row->id ?>);</script><?php } else { ?>><?php } ?>CSstatsX SQL
						</option>
						<option id="st_opt4_<?php echo $row->id ?>" value="4" <?php if($row->st_type == 4){ ?> selected>
							<script>select_stat_type(4, <?php echo $row->id ?>);</script><?php } else { ?>><?php } ?>HLstatsX:CE
						</option>
						<option id="st_opt5_<?php echo $row->id ?>" value="5" <?php if($row->st_type == 5){ ?> selected>
							<script>select_stat_type(5, <?php echo $row->id ?>);</script><?php } else { ?>><?php } ?>RankMe
						</option>
						<option id="st_opt6_<?php echo $row->id ?>" value="6" <?php if($row->st_type == 6){ ?> selected>
							<script>select_stat_type(6, <?php echo $row->id ?>);</script><?php } else { ?>><?php } ?>Level Rank
						</option>
					</select>
				</div>
				<div id="st_none_<?php echo $row->id ?>">
					<div class="bs-callout bs-callout-info bs-callout-sm mt-5">
						<p>Статистика данного сервера будет недоступна</p>
					</div>
				</div>
				<div id="st_tip1_<?php echo $row->id ?>" class="disp-n">
					<div class="form-group">
						<small>db хост</small>
						<input value="<?php echo $row->st_db_host ?>" type="text" class="form-control" id="st_db_host<?php echo $row->id ?>" maxlength="64" autocomplete="off">
					</div>
					<div class="form-group">
						<small>db логин</small>
						<input value="<?php echo $row->st_db_user ?>" type="text" class="form-control" id="st_db_user<?php echo $row->id ?>" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group">
						<small>db пароль</small>
						<input value="<?php echo $row->st_db_pass ?>" type="password" class="form-control" id="st_db_pass<?php echo $row->id ?>" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group">
						<small>db база</small>
						<input value="<?php echo $row->st_db_db ?>" type="text" class="form-control" id="st_db_db<?php echo $row->id ?>" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group" id="st_db_table_input<?php echo $row->id ?>">
						<small>db таблица</small>
						<input value="<?php echo $row->st_db_table ?>" type="text" class="form-control" id="st_db_table<?php echo $row->id ?>" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group">
						<small>Кодировка</small>
						<select class="form-control" id="st_db_code<?php echo $row->id ?>">
							<option value="0" <?php if($row->st_db_code == '0') { ?> selected <?php } ?>>Определять автоматически</option>
							<option value="1" <?php if($row->st_db_code == '1') { ?> selected <?php } ?>>utf-8</option>
							<option value="2" <?php if($row->st_db_code == '2') { ?> selected <?php } ?>>latin1</option>
							<option value="3" <?php if($row->st_db_code == '3') { ?> selected <?php } ?>>utf8mb4</option>
						</select>
					</div>
					<div class="form-group">
						<small>Сортировка</small>
						<select class="form-control" id="st_sort_type<?php echo $row->id ?>">
							<option value="0" <?php if($row->st_sort_type == '0') { ?> selected <?php } ?>>Убийства - смерти - teamkills</option>
							<option value="1" <?php if($row->st_sort_type == '1') { ?> selected <?php } ?>>Убийства</option>
							<option value="2" <?php if($row->st_sort_type == '2') { ?> selected <?php } ?>>Убийства + headshods</option>
							<option value="3" <?php if($row->st_sort_type == '3') { ?> selected <?php } ?>>Skill</option>
							<option value="4" <?php if($row->st_sort_type == '4') { ?> selected <?php } ?>>Время онлайн</option>
							<option value="5" <?php if($row->st_sort_type == '5') { ?> selected <?php } ?>>Место</option>
							<option value="6" <?php if($row->st_sort_type == '6') { ?> selected <?php } ?>>Продвинутая</option>
							<option value="7" <?php if($row->st_sort_type == '7') { ?> selected <?php } ?>>Ранг</option>
							<option value="8" <?php if($row->st_sort_type == '8') { ?> selected <?php } ?>>Очки</option>
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-12">
				<div id="edit_serv_result<?php echo $row->id ?>" class="mt-10"></div>
				<button onclick="server('edit','<?php echo $row->id ?>');" type="button" class="btn2">
					<span class="glyphicon glyphicon-pencil"></span>
				</button>

				<button type="button" class="btn2 btn-cancel" onclick="up_server('<?php echo $row->id ?>');">
					<span class="glyphicon glyphicon-chevron-up"></span>
				</button>

				<button type="button" class="btn2 btn-cancel" onclick="down_server('<?php echo $row->id ?>');">
					<span class="glyphicon glyphicon-chevron-down"></span>
				</button>

				<button onclick="dell_server('<?php echo $row->id ?>');" type="button" class="btn2 btn-cancel">
					<span class="glyphicon glyphicon-trash"></span>
				</button>

				<button
						data-target="#rcon_commands<?php echo $row->id ?>"
						data-toggle="modal"
						type="button"
						class="btn2 btn-cancel"
						onclick="getServerCommands(<?php echo $row->id ?>);"
				>
					Настройка rcon
				</button>

				<?php if($row->type == '2' or $row->type == '3' or $row->type == '4' or $row->type == '5') { ?>
					<button onclick="clear_banlist('<?php echo $row->id ?>', 1);" type="button" class="btn2 btn-cancel">
						Удалить все баны
					</button>
					<button onclick="clear_banlist('<?php echo $row->id ?>', 2);" type="button" class="btn2 btn-cancel">
						Удалить истекшие баны
					</button>
				<?php } ?>

				<?php if($row->type == '1' or $row->type == '2' or $row->type == '3' or $row->type == '4' or $row->type == '5') { ?>
					<button onclick="clear_mutlist('<?php echo $row->id ?>', 1);" type="button" class="btn2 btn-cancel">
						Удалить все муты
					</button>
					<button onclick="clear_mutlist('<?php echo $row->id ?>', 2);" type="button" class="btn2 btn-cancel">
						Удалить истекшие муты
					</button>
				<?php } ?>
				<br>
			</div>
		</div>

		<div id="rcon_commands<?php echo $row->id ?>" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">
							Настройка rcon
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</h4>
					</div>
					<div class="modal-body" id="rcon_settings<?php echo $row->id ?>">
						<div class="block">
							<div class="block_head">Основные</div>

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label>Активация</label>
										<select class="form-control" id="rcon<?php echo $row->id; ?>">
											<option value="1"<?php if($row->rcon == '1'){ ?> selected <?php } ?>>Включено</option>
											<option value="2"<?php if($row->rcon == '2'){ ?> selected <?php } ?>>Выключено</option>
										</select>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label>Rcon пароль сервера</label>
										<input value="<?php echo $row->rcon_password ?>" id="rcon_password<?php echo $row->id ?>" placeholder="Rcon пароль сервера" type="password" class="form-control" maxlength="256" autocomplete="off">
									</div>
								</div>
								<div class="col-md-12">
									<div class="bs-callout bs-callout-info mt-5">
										<p>Для работы опции, на вашем веб хостинге должны поддерживаться udp/tcp соединения</p>
									</div>
									<div class="mt-5" id="edit_rcon_settings_result<?php echo $row->id ?>"></div>
									<button class="btn btn-default mt-5" onclick="save_rcon_settings(<?php echo $row->id ?>);" type="button">Сохранить</button>
								</div>
							</div>
						</div>

						<div class="block">
							<div class="block_head">Добавить команду</div>

							<div class="row">
								<div class="col-md-2">
									<button class="btn btn-default btn-block" type="button" onclick="saveServerCommand(<?php echo $row->id ?>);">
										Добавить
									</button>
								</div>
								<div class="col-md-2">
									<select id="command-category<?php echo $row->id ?>"  class="form-control w-100">
										<option value="2">Действия над игроками</option>
										<option value="3">Управление сервером</option>
									</select>
								</div>
								<div class="col-md-4">
									<input id="command-value<?php echo $row->id ?>" class="form-control w-100" placeholder="Введите команду, пример: amx_kick">
								</div>
								<div class="col-md-4">
									<input id="command-title<?php echo $row->id ?>" class="form-control w-100" placeholder="Название команды, пример: Кик">
								</div>
							</div>
						</div>

						<div class="block">
							<div class="block_head">Команды</div>

							<div class="bs-callout bs-callout-info mb-10">
								<p>
									Для команды действия над игроком необходима переменная nick,
									значением данной переменной будет являться ник игрока.
									Значения всех остальных переменных админ указывает вручную
									при отправке команды
								</p>
							</div>

							<div id="server-commands<?php echo $row->id ?>">
								Загрузка...
							</div>
						</div>

						<div class="block">
							<div class="block_head">Выполнить произвольную команду</div>

							<div class="input-group">
								<span class="input-group-btn">
									<button class="btn btn-default" type="button" onclick="do_rcon_command(<?php echo $row->id ?>);">Выполнить</button>
								</span>
								<input id="command<?php echo $row->id ?>" class="form-control" placeholder="Введите команду">
							</div>
							<div class="mt-5" id="do_rcon_command_result<?php echo $row->id ?>"></div>
						</div>

						<div class="block">
							<div class="block_head">Лог команд</div>

							<div class="input-group">
								<span class="input-group-btn">
									<button class="btn btn-default" type="button" data-toggle="collapse" href="#server_rcon_log<?php echo $row->id ?>" onclick="server_rcon_log(<?php echo $row->id ?>, 'get');">Отобразить</button>
									<button class="btn btn-default" type="button" onclick="server_rcon_log(<?php echo $row->id ?>, 'dell');">Удалить</button>
								</span>
							</div>
							<div class="collapse mt-10" id="server_rcon_log<?php echo $row->id ?>">
								<div class="well mb-0" id="server_rcon_log_data<?php echo $row->id ?>">
									Загрузка...
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		$i++;
	}

	if($i == 0) {
		exit('Серверов нет');
	}
}
if(isset($_POST['dell_server'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit (json_encode(['status' => '2']));
	}

	$STH = $pdo->query("SELECT `trim` from `servers` WHERE `id`='$id' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	if(empty($tmp->trim)) {
		exit (json_encode(['status' => '2']));
	}
	$STH = $pdo->query("SELECT `id`, `trim` from `servers` WHERE `trim`>'$tmp->trim'");
	$STH->execute();
	$row   = $STH->fetchAll();
	$count = count($row);

	if($count > 0) {
		for($i = 0; $i < $count; $i++) {
			$STH = $pdo->prepare("UPDATE servers SET trim=:trim WHERE id=:id LIMIT 1");
			if($STH->execute(['trim' => $row[$i]['trim'] - 1, 'id' => $row[$i]['id']]) != '1') {
				exit (json_encode(['status' => '2']));
			}
		}
	}

	$AM = new AdminsManager;
	$AM->dell_admins($pdo, $id);
	$AM->set_admin_dell_time($pdo);

	$STH = $pdo->prepare("SELECT id FROM bans WHERE server = :server");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':server' => $id]);
	while($ban = $STH->fetch()) {
		$STH = $pdo->prepare("DELETE FROM bans__comments WHERE ban_id=:id LIMIT 1");
		$STH->execute([':id' => $ban->id]);
	}
	$STH = $pdo->prepare("DELETE FROM bans WHERE server=:server");
	$STH->execute([':server' => $id]);

	$STH = $pdo->prepare("SELECT id FROM services WHERE server = :server");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':server' => $id]);
	while($service = $STH->fetch()) {
		$STH = $pdo->prepare("DELETE FROM services__tarifs WHERE service=:id LIMIT 1");
		$STH->execute([':id' => $service->id]);
	}
	$STH = $pdo->prepare("DELETE FROM services WHERE server=:server");
	$STH->execute([':server' => $id]);

	$pdo->exec("DELETE FROM `servers` WHERE id='$id' LIMIT 1");

//	$pdo->exec("DELETE FROM `servers__commands` WHERE server='$id' LIMIT 1");

	exit(json_encode(['status' => '1']));
}
if(isset($_POST['up_server'])) {
	$id = check($_POST['id'], "int");
	if(empty($id)) {
		exit (json_encode(['status' => '2']));
	}

	$STH = $pdo->query("SELECT id,trim from servers WHERE id='$id' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	if(empty($tmp->id)) {
		exit (json_encode(['status' => '2']));
	}
	if($tmp->trim == '1') {
		exit (json_encode(['status' => '2']));
	}
	$trim  = $tmp->trim;
	$trim2 = $tmp->trim - 1;

	$STH = $pdo->prepare("UPDATE servers SET trim=:trim WHERE trim='$trim2' LIMIT 1");
	if($STH->execute(['trim' => $trim]) == '1') {
		$STH = $pdo->prepare("UPDATE servers SET trim=:trim2 WHERE id='$id' LIMIT 1");
		if($STH->execute(['trim2' => $trim2]) == '1') {
			exit (json_encode(['status' => '1']));
		} else {
			exit (json_encode(['status' => '2']));
		}
	} else {
		exit (json_encode(['status' => '2']));
	}
}
if(isset($_POST['down_server'])) {
	$id = check($_POST['id'], "int");

	if(empty($id)) {
		exit (json_encode(['status' => '2']));
	}

	$STH = $pdo->query("SELECT id,trim from servers WHERE id='$id' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	if(empty($tmp->id)) {
		exit (json_encode(['status' => '2']));
	}
	$trim  = $tmp->trim;
	$trim2 = $tmp->trim + 1;
	$STH   = $pdo->query("SELECT trim from servers ORDER BY trim DESC LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	$max = $tmp->trim;

	if($trim == $max) {
		exit (json_encode(['status' => '2']));
	}

	$STH = $pdo->prepare("UPDATE servers SET trim=:trim WHERE trim='$trim2' LIMIT 1");
	if($STH->execute(['trim' => $trim]) == '1') {
		$STH = $pdo->prepare("UPDATE servers SET trim=:trim2 WHERE id='$id' LIMIT 1");
		if($STH->execute(['trim2' => $trim2]) == '1') {
			exit (json_encode(['status' => '1']));
		} else {
			exit (json_encode(['status' => '2']));
		}
	} else {
		exit (json_encode(['status' => '2']));
	}
}
if(isset($_POST['save_rcon_settings'])) {
	$id            = clean($_POST['id'], "int");
	$rcon_password = clean($_POST['rcon_password'], null);
	$rcon          = clean($_POST['rcon'], "int");

	if(empty($id) || empty($rcon)) {
		exit('<p class="text-danger mb-0">Заполните все поля!</p>');
	}

	if($rcon == 1) {
		if(empty($rcon_password)) {
			exit('<p class="text-danger mb-0">Укажите rcon пароль!</p>');
		}

		$STH = $pdo->query("SELECT mon_api FROM config__secondary LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$conf2 = $STH->fetch();
		if($conf2->mon_api == 1) {
			exit('<p class="text-danger mb-0">Для работы опции, внешний мониторинг должен быть выключен!</p>');
		}
	} else {
		if(empty($rcon_password)) {
			$rcon_password = '';
		}
	}

	if(mb_strlen($rcon_password, 'UTF-8') > 256) {
		exit('<p class="text-danger mb-0">Rcon пароль должен содержать не более 256 символов.</p>');
	}

	$STH = $pdo->prepare("UPDATE servers SET rcon_password=:rcon_password, rcon=:rcon WHERE id=:id LIMIT 1");
	$STH->execute([':rcon_password' => $rcon_password, ':rcon' => $rcon, ':id' => $id]);

	exit('<p class="text-success mb-0">Настройки изменены!</p>');
}
if(isset($_POST['do_rcon_command'])) {
	$id      = clean($_POST['id'], "int");
	$command = $_POST['command'];

	if(empty($id)) {
		exit('<p class="text-danger mb-0">Пустой идентификатор сервера!</p>');
	}

	if(empty($command)) {
		exit('<p class="text-danger mb-0">Пустая команда!</p>');
	}

	$server = (new ServersManager())->getServer($id);
	$SourceQuery = (new OurSourceQuery)->setServer($server);

	if(!$SourceQuery->isServerCanWorkWithRcon()) {
		exit('<p class="text-danger mb-0">Отправка rcon команды невозможна!</p>');
	}

	try {
		echo '<p class="text-success mb-0">Команда отправлена!</p>'
			. '<pre class="mt-5">Ответ: <br> '
			. $SourceQuery->checkConnect()->auth()->send($command)
			. '</pre>';
	} catch(Exception $e) {
		echo '<p class="text-danger mb-0">Ошибка: ' . $e->getMessage() . '</p>';
	}

	$SourceQuery->Disconnect();
	unset($SourceQuery);

	exit();
}
if(isset($_POST['server_rcon_logs'])) {
	$id   = checkJs($_POST['id'], "int");
	$file = get_log_file_name("rcon_log_" . $id);

	if($_POST['type'] == 'get') {
		if(file_exists($_SERVER['DOCUMENT_ROOT'] . "/logs/" . $file)) {
			$data = get_log_file($_SERVER['DOCUMENT_ROOT'] . "/logs/" . $file);
		} else {
			$data = 'Лог пуст';
		}
		exit(json_encode(['status' => '1', 'data' => $data]));
	} elseif($_POST['type'] == 'dell') {
		if(file_exists($_SERVER['DOCUMENT_ROOT'] . "/logs/" . $file)) {
			unlink($_SERVER['DOCUMENT_ROOT'] . "/logs/" . $file);
		}
		exit(json_encode(['status' => '1']));
	} else {
		exit(json_encode(['status' => '2']));
	}
}
/* Услуги
=========================================*/
if(isset($_POST['get_services'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit ();
	}

	$i   = 0;
	$STH = $pdo->query("SELECT id,name FROM services WHERE server = '$id' ORDER BY trim");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$i++;
		echo '<option value="' . $row->id . '">' . $row->name . '</option>';
	}

	if($i == 0) {
		echo '<option value="0">Услуг нет</option>';
	}

	exit();
}

if(isset($_POST['get_services2'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit ();
	}

	$STH = $pdo->prepare("SELECT id,name,type FROM servers WHERE id=:id AND type != '0' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $id]);
	$server = $STH->fetch();

	$STH = $pdo->query("SELECT stand_rights FROM config__secondary LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row          = $STH->fetch();
	$stand_rights = $row->stand_rights;

	?>
	<div class="block">
		<?php
		$id  = $server->id;
		$STH = $pdo->query(
			"SELECT id,name,sb_group,rights,text,immunity,users_group,sale,show_adm,discount FROM services WHERE server = '$id' ORDER BY trim"
		);
		$STH->execute();
		$services = $STH->fetchAll();
		$count2   = count($services);
		if($server->type == '4') {
			$disp = 'disp-b';
		} else {
			$disp = 'disp-n';
		}
		for($i2 = 0; $i2 < $count2; $i2++) {
			$user_groups_str = '<option value="0">Группа на сайте: Не выдавать</option>';
			foreach($users_groups as &$value) {
				if($value['id'] != 0) {
					if($stand_rights != $value['id']) {
						if($services[$i2]['users_group'] == $value['id']) {
							$user_groups_str .= '<option value="' . $value['id'] . '" selected>Группа на сайте: ' . $value['name'] . '</option>';
						} else {
							$user_groups_str .= '<option value="' . $value['id'] . '">Группа на сайте: ' . $value['name'] . '</option>';
						}
					}
				}
			}

			$idd = $services[$i2]['id'];
			?>
			<div class="row mb-10" id="service<?php echo $idd ?>">
				<div class="col-md-6">
					<div class="block_head">Услуга #<?php echo $i2 + 1; ?></div>
					<select class="form-control mt-10" id="sale<?php echo $idd ?>">
						<option value="1" <?php if($services[$i2]['sale'] == '1') {
							echo 'selected';
						} ?>>Продажа: включена
						</option>
						<option value="2" <?php if($services[$i2]['sale'] == '2') {
							echo 'selected';
						} ?>>Продажа: выключена
						</option>
					</select>
					<select class="form-control mt-10" id="show<?php echo $idd ?>">
						<option value="1" <?php if($services[$i2]['show_adm'] == '1') {
							echo 'selected';
						} ?>>Отображение на странице администраторов: Включено
						</option>
						<option value="2" <?php if($services[$i2]['show_adm'] == '2') {
							echo 'selected';
						} ?>>Отображение на странице администраторов: Выключено
						</option>
					</select>
					<select class="form-control mt-10" id="user_groups<?php echo $idd ?>">
						<?php echo $user_groups_str; ?>
					</select>
					<select class="form-control mt-10 <?php echo $disp; ?>" onchange="change_group_or_flags(<?php echo $idd ?>);" id="flags_or_group<?php echo $idd ?>">
						<option value="1" <?php if($services[$i2]['rights'] != '') {
							echo 'selected';
						} ?>>Привилегия: По флагам
						</option>
						<option value="2" <?php if($services[$i2]['sb_group'] != '') {
							echo 'selected';
						} ?>>Привилегия: По группе
						</option>
					</select>
					<input value="<?php echo $services[$i2]['name'] ?>" class="form-control mt-10" type="text" maxlength="255" id="name<?php echo $idd ?>" placeholder="Название услуги" autocomplete="off">
					<input value="<?php echo $services[$i2]['rights'] ?>" class="form-control mt-10" type="text" maxlength="25" id="flags<?php echo $idd ?>" placeholder="Флаги" autocomplete="off">
					<input value="<?php echo $services[$i2]['sb_group'] ?>" class="form-control mt-10 <?php echo $disp; ?>" type="text" maxlength="120" id="group<?php echo $idd ?>" placeholder="Название группы в SourceBans" autocomplete="off">
					<input value="<?php echo $services[$i2]['immunity'] ?>" class="form-control mt-10 <?php echo $disp; ?>" type="number" maxlength="3" id="immunity<?php echo $idd ?>" placeholder="Иммунитет" autocomplete="off">
					<input value="<?php echo $services[$i2]['discount'] ?>" class="form-control mt-10" type="number" maxlength="2" id="discount<?php echo $idd ?>" placeholder="Скидка в %" autocomplete="off">
					<?php if($server->type == '4') { ?>
						<script>
                          change_group_or_flags(<?php echo $idd ?>);
						</script>
					<?php } ?>
					<br>
					<textarea id="text<?php echo $idd ?>" class="form-control" rows="5"><?php echo $services[$i2]['text'] ?></textarea>
					<script>
                      $(document).ready(function () {
                        init_tinymce('text<?php echo $idd ?>', '<?php echo md5($conf->code); ?>', 'lite');
                      });
					</script>
					<button class="btn btn-default mt-10" onclick="edit_service(<?php echo $idd ?>);">Изменить</button>
					<button class="btn btn-default mt-10" onclick="dell_service(<?php echo $idd ?>);">Удалить</button>
					<button class="btn btn-default mt-10" onclick="up_service(<?php echo $idd ?>);">Поднять</button>
					<button class="btn btn-default mt-10" onclick="down_service(<?php echo $idd ?>);">Опустить</button>
				</div>
				<div class="col-md-6">
					<div class="block_head">Тарифы услуги #<?php echo $i2 + 1; ?></div>
					<div class="tarifs">
						<table class="table table-bordered table-condensed mb-0">
							<thead>
							<tr>
								<td>#</td>
								<td>Время</td>
								<td>Цена покупки</td>
								<td>Цена продления</td>
								<td>Скидка</td>
								<td>Действие</td>
							</tr>
							</thead>
							<tbody>
							<?php
							$id2 = $idd;
							$STH = $pdo->query("SELECT * FROM services__tarifs WHERE service = '$id2'");
							$STH->execute();
							$tarifs = $STH->fetchAll();
							$count3 = count($tarifs);
							for($i3 = 0; $i3 < $count3; $i3++) {
								if($tarifs[$i3]['time'] == 0) {
									$tarifs[$i3]['time'] = 'Навсегда';
								}
								?>
								<tr id="tarif<?php echo $tarifs[$i3]['id'] ?>">
									<td width="1%"><?php echo $i3 + 1; ?></td>
									<td>
										<input value="<?php echo $tarifs[$i3]['time'] ?>" class="form-control" type="text" maxlength="6" id="time<?php echo $tarifs[$i3]['id'] ?>" placeholder="Время" autocomplete="off">
									</td>
									<td>
										<input value="<?php echo $tarifs[$i3]['price'] ?>" class="form-control" type="text" maxlength="6" id="price<?php echo $tarifs[$i3]['id'] ?>" placeholder="Цена покупки" autocomplete="off">
									</td>
									<td>
										<input value="<?php echo $tarifs[$i3]['price_renewal'] ?>" class="form-control" type="text" maxlength="6" id="priceRenewal<?php echo $tarifs[$i3]['id'] ?>" placeholder="Цена продления" autocomplete="off">
									</td>
									<td>
										<input value="<?php echo $tarifs[$i3]['discount'] ?>" class="form-control" type="text" maxlength="6" id="tarif_discount<?php echo $tarifs[$i3]['id'] ?>" placeholder="Скидка" autocomplete="off">
									</td>
									<td width="20%">
										<div class="btn-group" role="group">
											<button onclick="edit_tarif (<?php echo $tarifs[$i3]['id'] ?>);" class="btn btn-default" type="button">
												<span class="glyphicon glyphicon-pencil"></span></button>
											<button onclick="dell_tarif (<?php echo $tarifs[$i3]['id'] ?>);" class="btn btn-default" type="button">
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
		?>
	</div>
	<?php
	exit();
}
if(isset($_POST['add_tarif'])) {
	$service = check($_POST['service'], "int");
	$type    = 0;
	if(($_POST['time'] == 0) or (strnatcasecmp($_POST['time'], 'навсегда') == 0)) {
		$time = 0;
	} else {
		if(strpos($_POST['time'], '-') == false) {
			$type = 0;
			$time = check($_POST['time'], "int");
		} else {
			$time    = explode("-", $_POST['time']);
			$time[0] = check($time[0], "int");
			$time[1] = check($time[1], "int");
			$type    = 1;
		}
	}

	$price        = check($_POST['price'], "float");
	$priceRenewal = check($_POST['priceRenewal'], "float");
	$discount     = check($_POST['discount'], "int");

	if(empty($discount)) {
		$discount = 0;
	}

	if(empty($priceRenewal)) {
		$priceRenewal = 0;
	}

	if(empty($service)) {
		exit (json_encode(['status' => '2', 'input' => 'services', 'reply' => 'Заполните!']));
	}
	if(empty($price)) {
		exit (json_encode(['status' => '2', 'input' => 'price', 'reply' => 'Заполните!']));
	}
	if(mb_strlen($price, 'UTF-8') > 6) {
		exit (json_encode(['status' => '2', 'input' => 'price', 'reply' => 'Не более 6 символов!']));
	}

	$STH = $pdo->query("SELECT id FROM services WHERE id='$service' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(empty($row->id)) {
		exit(json_encode(['status' => '2']));
	}

	if($type == 0) {
		if(empty($time) and $time != 0) {
			exit (json_encode(['status' => '2', 'input' => 'time', 'reply' => 'Заполните!']));
		}
		if(mb_strlen($time, 'UTF-8') > 6) {
			exit (json_encode(['status' => '2', 'input' => 'time', 'reply' => 'Не более 6 символов!']));
		}

		$STH = $pdo->prepare(
			"INSERT INTO services__tarifs (service,price,price_renewal,time,discount) VALUES (:service, :price, :priceRenewal, :time, :discount)"
		);
		if($STH->execute(['service' => $service, 'price' => $price, 'priceRenewal' => $priceRenewal, 'time' => $time, 'discount' => $discount]) == '1') {
			exit(json_encode(['status' => '1']));
		}
	} elseif($type == 1) {
		if((empty($time[0]) and $time[0] != 0) or (empty($time[1]) and $time[1] != 0) or ($time[0] == $time[1]) or ($time[0] > $time[1])) {
			exit (json_encode(['status' => '2', 'input' => 'time', 'reply' => 'Укажите корректный диапазон!']));
		}
		if(mb_strlen($time[0], 'UTF-8') > 3 or mb_strlen($time[1], 'UTF-8') > 3) {
			exit (
			json_encode(
				['status' => '2', 'input' => 'time', 'reply' => 'Не более 3 символов на каждый конец диапазона!']
			)
			);
		}

		for($i = $time[0]; $i <= $time[1]; $i++) {
			$price2 = $price * $i;
			$STH    = $pdo->prepare(
				"INSERT INTO services__tarifs (service,price,time,discount) values (:service, :price, :time, :discount)"
			);
			$STH->execute(['service' => $service, 'price' => $price2, 'time' => $i, 'discount' => $discount]);
		}
		exit(json_encode(['status' => '1']));
	}
}
if(isset($_POST['edit_tarif'])) {
	$id = check($_POST['id'], "int");
	if(($_POST['time'] == 0) or (strnatcasecmp($_POST['time'], 'навсегда') == 0)) {
		$time = 0;
	} else {
		$time = check($_POST['time'], "int");
	}

	$price        = check($_POST['price'], "float");
	$discount     = check($_POST['discount'], "int");
	$priceRenewal = check($_POST['priceRenewal'], "float");

	if(empty($priceRenewal)) {
		$priceRenewal = 0;
	}

	if(empty($discount)) {
		$discount = 0;
	}

	if(empty($id)) {
		exit(json_encode(['status' => '2']));
	}
	if(empty($time) and $time != 0) {
		exit (json_encode(['status' => '2', 'input' => 'time', 'reply' => 'Заполните!']));
	}
	if(empty($price)) {
		exit (json_encode(['status' => '2', 'input' => 'price', 'reply' => 'Заполните!']));
	}
	if(mb_strlen($time, 'UTF-8') > 6) {
		exit (json_encode(['status' => '2', 'input' => 'time', 'reply' => 'Не более 6 символов!']));
	}
	if(mb_strlen($price, 'UTF-8') > 6) {
		exit (json_encode(['status' => '2', 'input' => 'price', 'reply' => 'Не более 6 символов!']));
	}

	$STH = $pdo->prepare(
		"UPDATE services__tarifs SET time=:time,price=:price,price_renewal=:priceRenewal,discount=:discount WHERE id='$id' LIMIT 1"
	);
	if($STH->execute(['time' => $time, 'price' => $price, 'priceRenewal' => $priceRenewal, 'discount' => $discount]) == '1') {
		exit(json_encode(['status' => '1']));
	}
}
if(isset($_POST['dell_tarif'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit (json_encode(['status' => '2']));
	}
	$pdo->exec("DELETE FROM services__tarifs WHERE id='$id'");
	exit(json_encode(['status' => '1']));
}
/* Блокировки/Логи
=========================================*/
if(isset($_POST['get_shilings_operations'])) {
	$load_val = checkJs($_POST['load_val'], "int");
	if(empty($load_val)) {
		$load_val = 1;
	}

	$limit = 50;
	$start = ($load_val - 1) * $limit;
	$i     = $start;
	$l     = 0;
	$STH   = $pdo->query(
		"SELECT `money__actions`.*,`users`.`login`,`users`.`avatar`, `money__actions_types`.`name`, `money__actions_types`.`class` FROM `money__actions`
		INNER JOIN `money__actions_types` ON `money__actions_types`.`id` = `money__actions`.`type`
		LEFT JOIN `users` ON `money__actions`.`author` = `users`.`id` ORDER BY `money__actions`.`id` DESC LIMIT " . $start . ", " . $limit
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$i++;
		$l++;
		if($row->type == 1) {
			$row->shilings = '<p class="text-success">' . $row->shilings . '</p>';
		}
		if($row->type == 3) {
			$row->shilings = '<p class="text-danger">' . $row->shilings . '</p>';
		}
		?>
		<tr>
			<td><?php echo $i; ?></td>
			<td><?php echo collect_consumption_str(
					2,
					$row->type,
					$row->class,
					$row->name,
					$pdo,
					$row->gave_out
				); ?></td>
			<td><?php echo $row->shilings; ?></td>
			<td>
				<a target="_blank" href="../admin/edit_user?id=<?php echo $row->author ?>">
					<img src="../<?php echo $row->avatar ?>" alt="<?php echo $row->login ?>"> <?php echo $row->login ?>
				</a>
			</td>
			<td><?php echo expand_date($row->date, 7); ?></td>
		</tr>
		<?php
	}
	if(($load_val > 0) and ($l > $limit - 1)) {
		$load_val++;
		exit('<tr id="loader' . $load_val . '" class="c-p" onclick="get_shilings_operations(\'' . $load_val . '\');"><td colspan="10">Подгрузить записи</td></tr>');
	}
	exit();
}
if(isset($_POST['load_banned_ip'])) {
	$i   = 0;
	$STH = $pdo->query("SELECT ip,date,id FROM users__blocked");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$i++;
		if($row->date == '0000-00-00 00:00:00') {
			$date = 'Никогда';
		} else {
			$date = expand_date($row->date, 1);
		}
		?>
		<tr id="ban<?php echo $row->id; ?>">
			<td><?php echo $row->ip; ?></td>
			<td><?php echo $date ?></td>
			<td><a class="c-p" onclick="dell_banned_ip(<?php echo $row->id; ?>);">Удалить</a></td>
		</tr>
		<?php
	}
	if($i == 0) {
		exit('<tr><td colspan="10">Блокировок нет</td></tr>');
	}
}
if(isset($_POST['dell_banned_ip'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit (json_encode(['status' => '2']));
	}
	$pdo->exec("DELETE FROM `users__blocked` WHERE `id`='$id'");
	exit(json_encode(['status' => '1']));
}
if(isset($_POST['add_banned_ip'])) {
	$ip = check($_POST['ip'], null);

	if(empty($ip)) {
		exit (json_encode(['status' => '2']));
	}
	if(!filter_var($ip, FILTER_VALIDATE_IP)) {
		exit (json_encode(['status' => '2']));
	}

	$STH = $pdo->query("SELECT `id` FROM `users__blocked` WHERE `ip`='$ip' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(!empty($row->id)) {
		exit (json_encode(['status' => '2']));
	}

	$STH = $pdo->prepare("INSERT INTO users__blocked (ip) values (:ip)");
	if($STH->execute(['ip' => $ip]) == '1') {
		exit (json_encode(['status' => '1']));
	} else {
		exit (json_encode(['status' => '2']));
	}
}
if(isset($_POST['up_service'])) {
	$number = check($_POST['id'], "int");

	$STH = $pdo->query("SELECT server FROM services WHERE id='$number' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row    = $STH->fetch();
	$server = $row->server;

	if(empty($number) or empty($server)) {
		exit(json_encode(['status' => '2']));
	}

	$STH = $pdo->query("SELECT id,trim FROM services WHERE id='$number' and server='$server' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	if(empty($tmp->id)) {
		exit(json_encode(['status' => '2']));
	}
	if($tmp->trim == 1) {
		exit(json_encode(['status' => '2']));
	}
	$poz  = $tmp->trim;
	$poz2 = $tmp->trim - 1;

	$STH = $pdo->prepare("UPDATE services SET trim=:trim WHERE trim='$poz2' and server='$server' LIMIT 1");
	if($STH->execute(['trim' => $poz]) == '1') {
		$STH = $pdo->prepare("UPDATE services SET trim=:poz2 WHERE id='$number' and server='$server' LIMIT 1");
		if($STH->execute(['poz2' => $poz2]) == '1') {
			exit(json_encode(['status' => '1']));
		} else {
			exit(json_encode(['status' => '2']));
		}
	} else {
		exit(json_encode(['status' => '2']));
	}
}
if(isset($_POST['down_service'])) {
	$number = check($_POST['id'], "int");

	$STH = $pdo->query("SELECT server FROM services WHERE id='$number' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row    = $STH->fetch();
	$server = $row->server;

	if(empty($number) or empty($server)) {
		exit(json_encode(['status' => '2']));
	}

	$STH = $pdo->query("SELECT id,trim from services WHERE id='$number' and server='$server' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	if(empty($tmp->id)) {
		exit(json_encode(['status' => '2']));
	}
	$poz  = $tmp->trim;
	$poz2 = $tmp->trim + 1;
	$STH  = $pdo->query("SELECT trim from services WHERE server='$server' ORDER BY trim DESC LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	$max = $tmp->trim;

	if($poz == $max) {
		exit(json_encode(['status' => '2']));
	}

	$STH = $pdo->prepare("UPDATE services SET trim=:trim WHERE trim='$poz2' and server='$server' LIMIT 1");
	if($STH->execute(['trim' => $poz]) == '1') {
		$STH = $pdo->prepare("UPDATE services SET trim=:trim WHERE id='$number' and server='$server' LIMIT 1");
		if($STH->execute(['trim' => $poz2]) == '1') {
			exit(json_encode(['status' => '1']));
		} else {
			exit(json_encode(['status' => '2']));
		}
	} else {
		exit(json_encode(['status' => '2']));
	}
}
if(isset($_POST['dell_service'])) {
	$main_id = checkJs($_POST['id'], "int");
	if(empty($main_id)) {
		exit (json_encode(['status' => '2']));
	}

	$STH = $pdo->query("SELECT server FROM services WHERE id='$main_id' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row    = $STH->fetch();
	$server = $row->server;

	$STH = $pdo->query("SELECT trim from services WHERE id='$main_id' and server='$server' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();

	$STH = $pdo->query("SELECT id,trim from services WHERE trim>'$tmp->trim' and server='$server'");
	$STH->execute();
	$row   = $STH->fetchAll();
	$count = count($row);

	if($count == 0) {
		$pdo->exec("DELETE FROM services__tarifs WHERE service='$main_id'");
		$pdo->exec("DELETE FROM services WHERE id='$main_id' LIMIT 1");
		exit(json_encode(['status' => '1']));
	}

	for($i = 0; $i < $count; $i++) {
		$id   = $row[$i]['id'];
		$STH  = $pdo->prepare("UPDATE services SET trim=:trim WHERE id='$id' and server='$server' LIMIT 1");
		$trim = $row[$i][trim] - 1;
		if($STH->execute(['trim' => $trim]) != '1') {
			exit(json_encode(['status' => '2']));
		}
	}

	$pdo->exec("DELETE FROM services__tarifs WHERE service='$main_id'");
	$pdo->exec("DELETE FROM services WHERE id='$main_id' LIMIT 1");
	exit(json_encode(['status' => '1']));
}
if(isset($_POST['add_service'])) {
	$server         = checkJs($_POST['server'], "int");
	$immunity       = checkJs($_POST['immunity'], "int");
	$sale           = checkJs($_POST['sale'], "int");
	$name           = checkJs($_POST['name'], null);
	$flags          = checkJs($_POST['flags'], null);
	$user_groups    = checkJs($_POST['user_groups'], "int");
	$flags_or_group = check($_POST['flags_or_group'], "int");
	$group          = check($_POST['group'], null);
	$show           = check($_POST['show'], "int");
	$discount       = check($_POST['discount'], "int");

	$text = HTMLPurifier()->purify($_POST['text']);
	$text = find_img_mp3($text, rand(1, 250), 1);

	if(empty($server)) {
		exit (json_encode(['status' => '2', 'input' => 'server', 'reply' => 'Заполните!']));
	}
	if(empty($name)) {
		exit (json_encode(['status' => '2', 'input' => 'name', 'reply' => 'Заполните!']));
	}
	if(mb_strlen($name, 'UTF-8') > 255) {
		exit (json_encode(['status' => '2', 'input' => 'name', 'reply' => 'Не более 255 символов!']));
	}
	if(mb_strlen($text, 'UTF-8') > 10000) {
		exit (json_encode(['status' => '2', 'input' => 'text', 'reply' => 'Слишком длинный контент.']));
	}
	if($sale != 1 and $sale != 2) {
		exit (json_encode(['status' => '2', 'input' => 'sale', 'reply' => 'Неверное значение!']));
	}
	if($flags_or_group != 1 and $flags_or_group != 2) {
		exit (json_encode(['status' => '2', 'input' => 'flags_or_group', 'reply' => 'Неверное значение!']));
	}
	if($show != 1 and $show != 2) {
		exit (json_encode(['status' => '2', 'input' => 'show', 'reply' => 'Неверное значение!']));
	}
	if(empty($discount)) {
		$discount = 0;
	} else {
		if($discount > 99) {
			exit (json_encode(['status' => '2', 'input' => 'discount', 'reply' => 'Не более 99']));
		}
	}

	$STH = $pdo->prepare(
		"SELECT id,db_host,db_user,db_pass,db_db,db_prefix,db_code,type FROM servers WHERE id=:id LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $server]);
	$server = $STH->fetch();
	if($server->type != 4) {
		$flags_or_group = 1;
	}

	if($flags_or_group == 1) {
		if(empty($flags)) {
			exit (json_encode(['status' => '2', 'input' => 'flags', 'reply' => 'Заполните!']));
		}
		if(mb_strlen($flags, 'UTF-8') > 25) {
			exit (json_encode(['status' => '2', 'input' => 'flags', 'reply' => 'Не более 25 символов!']));
		}
		$group = '';
	} else {
		if(empty($group)) {
			exit (json_encode(['status' => '2', 'input' => 'group', 'reply' => 'Заполните!']));
		}
		if(mb_strlen($group, 'UTF-8') > 120) {
			exit (json_encode(['status' => '2', 'input' => 'group', 'reply' => 'Не более 120 символов!']));
		}

		if(!$pdo2 = db_connect($server->db_host, $server->db_db, $server->db_user, $server->db_pass)) {
			exit (
			json_encode(
				['status' => '2', 'input' => 'group', 'reply' => 'Не удалось подключиться к SourceBans']
			)
			);
		}
		set_names($pdo2, $server->db_code);

		$table = set_prefix($server->db_prefix, "srvgroups");
		$STH   = $pdo2->prepare("SELECT `id` FROM `$table` WHERE `name`=:name LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':name' => $group]);
		$groups = $STH->fetch();
		if(empty($groups->id)) {
			exit (json_encode(['status' => '2', 'input' => 'group', 'reply' => 'Группа не найдена в SourceBans']));
		}

		$STH = $pdo->prepare("SELECT id FROM services WHERE sb_group=:sb_group AND server=:server LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':sb_group' => $group, ':server' => $server->id]);
		$row = $STH->fetch();
		if(isset($row->id)) {
			exit (json_encode(['status' => '2', 'input' => 'group', 'reply' => 'Услуга с данной группой уже создана']));
		}

		$flags    = '';
		$immunity = 0;
	}

	if(empty($immunity)) {
		$immunity = 0;
	}
	if(empty($user_groups)) {
		$user_groups = 0;
	}
	if($immunity > 100) {
		$immunity = 100;
	}

	$STH = $pdo->query("SELECT trim from services WHERE server='$server->id' ORDER BY trim DESC LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$tmp = $STH->fetch();
	if(isset($tmp->trim)) {
		$trim = $tmp->trim + 1;
	} else {
		$trim = 1;
	}

	$STH = $pdo->prepare(
		"INSERT INTO services (discount,name,sb_group,rights,server,text,trim,immunity,sale,users_group,show_adm) VALUES (:discount, :name, :sb_group, :rights, :server, :text, :trim, :immunity, :sale, :users_group, :show_adm)"
	);
	if($STH->execute(
			[
				'discount'    => $discount,
				'name'        => $name,
				'sb_group'    => $group,
				'rights'      => $flags,
				'server'      => $server->id,
				'text'        => $text,
				'trim'        => $trim,
				'immunity'    => $immunity,
				'sale'        => $sale,
				'users_group' => $user_groups,
				'show_adm'    => $show
			]
		) == '1') {
		exit(json_encode(['status' => '1', 'id' => $server->id]));
	}
}
if(isset($_POST['edit_service'])) {
	$id             = checkJs($_POST['id'], "int");
	$immunity       = checkJs($_POST['immunity'], "int");
	$sale           = checkJs($_POST['sale'], "int");
	$name           = checkJs($_POST['name'], null);
	$flags          = checkJs($_POST['flags'], null);
	$sale           = checkJs($_POST['sale'], "int");
	$user_groups    = checkJs($_POST['user_groups'], "int");
	$flags_or_group = check($_POST['flags_or_group'], "int");
	$group          = check($_POST['group'], null);
	$show           = check($_POST['show'], "int");
	$discount       = check($_POST['discount'], "int");

	$text = HTMLPurifier()->purify($_POST['text']);
	$text = find_img_mp3($text, rand(1, 250), 1);

	if(empty($id)) {
		exit(json_encode(['status' => '2']));
	}
	if(empty($name)) {
		exit (json_encode(['status' => '2', 'input' => 'name', 'reply' => 'Заполните!']));
	}
	if(mb_strlen($name, 'UTF-8') > 255) {
		exit (json_encode(['status' => '2', 'input' => 'name', 'reply' => 'Не более 255 символов!']));
	}
	if(mb_strlen($text, 'UTF-8') > 10000) {
		exit (json_encode(['status' => '2', 'input' => 'text', 'reply' => 'Слишком длинный контент.']));
	}
	if($sale != 1 and $sale != 2) {
		exit (json_encode(['status' => '2', 'input' => 'sale', 'reply' => 'Неверное значение!']));
	}
	if($flags_or_group != 1 and $flags_or_group != 2) {
		exit (json_encode(['status' => '2', 'input' => 'flags_or_group', 'reply' => 'Неверное значение!']));
	}
	if($show != 1 and $show != 2) {
		exit (json_encode(['status' => '2', 'input' => 'show', 'reply' => 'Неверное значение!']));
	}
	if(empty($discount)) {
		$discount = 0;
	} else {
		if($discount > 99) {
			exit (json_encode(['status' => '2', 'input' => 'discount', 'reply' => 'Не более 99']));
		}
	}

	$STH = $pdo->prepare("SELECT server FROM services WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $id]);
	$row = $STH->fetch();
	if(empty($row->server)) {
		exit (json_encode(['status' => '2', 'input' => 'name', 'reply' => 'Услуга с данным id не найдена']));
	}

	$STH = $pdo->prepare(
		"SELECT id,db_host,db_user,db_pass,db_db,db_prefix,db_code,type FROM servers WHERE id=:id LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $row->server]);
	$server = $STH->fetch();
	if($server->type != 4) {
		$flags_or_group = 1;
	}

	if($flags_or_group == 1) {
		if(empty($flags)) {
			exit (json_encode(['status' => '2', 'input' => 'flags', 'reply' => 'Заполните!']));
		}
		if(mb_strlen($flags, 'UTF-8') > 25) {
			exit (json_encode(['status' => '2', 'input' => 'flags', 'reply' => 'Не более 25 символов!']));
		}
		$group = '';
	} else {
		if(empty($group)) {
			exit (json_encode(['status' => '2', 'input' => 'group', 'reply' => 'Заполните!']));
		}
		if(mb_strlen($group, 'UTF-8') > 120) {
			exit (json_encode(['status' => '2', 'input' => 'group', 'reply' => 'Не более 120 символов!']));
		}

		if(!$pdo2 = db_connect($server->db_host, $server->db_db, $server->db_user, $server->db_pass)) {
			exit (
			json_encode(
				['status' => '2', 'input' => 'group', 'reply' => 'Не удалось подключиться к SourceBans']
			)
			);
		}
		set_names($pdo2, $server->db_code);

		$table = set_prefix($server->db_prefix, "srvgroups");
		$STH   = $pdo2->prepare("SELECT `id` FROM `$table` WHERE `name`=:name LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':name' => $group]);
		$groups = $STH->fetch();
		if(empty($groups->id)) {
			exit (json_encode(['status' => '2', 'input' => 'group', 'reply' => 'Группа не найдена в SourceBans']));
		}

		$STH = $pdo->prepare("SELECT id FROM services WHERE sb_group=:sb_group AND server=:server LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':sb_group' => $group, ':server' => $server->id]);
		$row = $STH->fetch();
		if(isset($row->id) and $row->id != $id) {
			exit (json_encode(['status' => '2', 'input' => 'group', 'reply' => 'Услуга с данной группой уже создана']));
		}

		$flags    = '';
		$immunity = 0;
	}

	if(empty($immunity)) {
		$immunity = 0;
	}
	if(empty($user_groups)) {
		$user_groups = 0;
	}
	if($immunity > 100) {
		$immunity = 100;
	}

	$STH = $pdo->prepare(
		"UPDATE services SET discount=:discount,name=:name,sb_group=:sb_group,rights=:rights,text=:text,immunity=:immunity,sale=:sale,users_group=:users_group,show_adm=:show_adm WHERE id='$id' LIMIT 1"
	);
	if($STH->execute(
			[
				'discount'    => $discount,
				'name'        => $name,
				'sb_group'    => $group,
				'rights'      => $flags,
				'text'        => $text,
				'immunity'    => $immunity,
				'sale'        => $sale,
				'users_group' => $user_groups,
				'show_adm'    => $show
			]
		) == '1') {
		exit(json_encode(['status' => '1']));
	}
}
if(isset($_POST['recount'])) {
	$Forum = new Forum($pdo);
	$Forum->global_recount_reit();

	exit(json_encode(['status' => '1']));
}
if(isset($_POST['import_admins'])) {
	$server = check($_POST['id'], "int");
	if(empty($server)) {
		exit (json_encode(['status' => '2']));
	}

	$AM = new AdminsManager;
	if(!$AM->import_admins($server, $pdo)) {
		exit (json_encode(['status' => '2']));
	} else {
		exit (json_encode(['status' => '1']));
	}
}
if(isset($_POST['export_admins'])) {
	$server = check($_POST['id'], "int");
	if(empty($server)) {
		exit(json_encode(['status' => '2']));
	}

	$AM = new AdminsManager;
	if(!$AM->export_admins($server, $pdo)) {
		exit (json_encode(['status' => '2']));
	} else {
		exit (json_encode(['status' => '1']));
	}
}
if(isset($_POST['edit_mon_gap'])) {
	$mon_gap = check($_POST['mon_gap'], 'int');

	if(empty($mon_gap)) {
		exit(json_encode(['status' => '2']));
	}

	if($mon_gap > 1000000) {
		$mon_gap = 1000000;
	}

	$STH = $pdo->prepare("UPDATE config__secondary SET mon_gap=:mon_gap LIMIT 1");
	$STH->execute([':mon_gap' => $mon_gap]);
	exit(json_encode(['status' => '1']));
}
if(isset($_POST['edit_mon_api'])) {
	$mon_key = check($_POST['mon_key'], null);
	$type    = check($_POST['type'], 'int');

	if($type != 1 and $type != 2) {
		exit(json_encode(['status' => '2']));
	}

	if($type == 1) {
		if(empty($mon_key)) {
			exit (json_encode(['status' => '2', 'input' => 'mon_key', 'reply' => 'Заполните!']));
		}

		$STH = $pdo->prepare(
			"UPDATE config__secondary SET mon_gap=:mon_gap, mon_key=:mon_key, mon_api=:mon_api LIMIT 1"
		);
		$STH->execute([':mon_gap' => '180', ':mon_key' => $mon_key, ':mon_api' => $type]);

		$STH = $pdo->prepare("UPDATE servers SET rcon=:rcon");
		$STH->execute([':rcon' => 2]);

		exit(json_encode(['status' => '1']));
	} else {
		if(empty($mon_key)) {
			$mon_key = '';
		}
		$STH = $pdo->prepare("UPDATE config__secondary SET mon_key=:mon_key, mon_api=:mon_api LIMIT 1");
		$STH->execute([':mon_key' => $mon_key, ':mon_api' => $type]);
		exit(json_encode(['status' => '1']));
	}
}
if(isset($_POST['add_group'])) {
	$name   = check($_POST['name'], null);
	$rights = check($_POST['rights'], null);
	$color  = check($_POST['color'], null);
	$style  = check($_POST['style'], null);

	if(empty($name) or empty($rights) or empty($color)) {
		exit(json_encode(['status' => '2', 'data' => 'Заполните все поля!']));
	}

	if(empty($style)) {
		$style = '';
	}

	if(!preg_match('/[a-zA-Z0-9:]{1,250}/is', $rights)) {
		exit(json_encode(['status' => '2', 'data' => 'Неверные права!']));
	}

	$color = $color . ';' . $style;

	$STH = $pdo->prepare("INSERT INTO users__groups (name,rights,color) values (:name, :rights, :color)");
	if($STH->execute([':name' => $name, ':rights' => $rights, ':color' => $color]) == '1') {
		exit(json_encode(['status' => '1']));
	} else {
		exit(json_encode(['status' => '2', 'data' => 'Ошибка!']));
	}
}
if(isset($_POST['edit_group'])) {
	$id     = check($_POST['id'], "int");
	$name   = check($_POST['name'], null);
	$rights = check($_POST['rights'], null);
	$color  = check($_POST['color'], null);
	$style  = check($_POST['style'], null);

	if(empty($name) or empty($rights) or empty($color) or empty($id)) {
		exit(json_encode(['status' => '2', 'data' => 'Заполните все поля!']));
	}

	if(empty($style)) {
		$style = '';
	}

	if(!preg_match('/[a-zA-Z0-9:]{1,250}/is', $rights)) {
		exit(json_encode(['status' => '2', 'data' => 'Неверные права!']));
	}

	$color = $color . ';' . $style;

	$STH = $pdo->prepare("UPDATE users__groups SET name=:name, rights=:rights, color=:color WHERE id='$id' LIMIT 1");
	$STH->execute([':name' => $name, ':rights' => $rights, ':color' => $color]);
	exit(json_encode(['status' => '1']));
}
if(isset($_POST['get_groups'])) {
	$tpl                    = new Template;
	$tpl->dir               = '../templates/admin/tpl';
	$tpl->result['content'] = '';

	$STH = $pdo->query("SELECT * FROM users__groups ORDER BY id");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$tpl->load_template('/elements/group_form.tpl');

		$style = explode(";", $row->color, 2);
		if(empty($style[1])) {
			$style[1] = '';
		}

		$tpl->set("{id}", $row->id);
		$tpl->set("{name}", $row->name);
		$tpl->set("{rights}", $row->rights);
		$tpl->set("{color}", $style[0]);
		$tpl->set("{style}", $style[1]);
		$tpl->compile('content');
		$tpl->clear();
	}

	$tpl->show($tpl->result['content']);

	exit();
}
if(isset($_POST['dell_group'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit (json_encode(['status' => '2']));
	}

	$STH = $pdo->query("SELECT stand_rights FROM config__secondary LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if($row->stand_rights == $id) {
		exit (
		json_encode(
			['status' => '2', 'data' => 'Группа установлена по умолчанию при регистрации. Удаление невозможно.']
		)
		);
	}
	$i    = 0;
	$mess = 'Пользователи: <br>';
	$STH  = $pdo->query("SELECT id,login FROM users WHERE rights = '$id'");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$i++;
		if($i == 1) {
			$mess .= ' <a target="_blank" href="edit_user?id=' . $row->id . '">' . $row->login . '</a>';
		} else {
			$mess .= ', <a target="_blank" href="edit_user?id=' . $row->id . '">' . $row->login . '</a> ';
		}
	}
	if($i != 0) {
		exit (json_encode(['status' => '2', 'data' => $mess . '<br> имеют данную группу. Удаление невозможно.']));
	}

	$pdo->exec("DELETE FROM users__groups WHERE id='$id'");
	exit(json_encode(['status' => '1']));
}
if(isset($_POST['change_group'])) {
	$rights = check($_POST['group'], "int");

	if(empty($rights)) {
		exit(json_encode(['status' => '2']));
	}

	$STH = $pdo->prepare("UPDATE config__secondary SET stand_rights=:stand_rights LIMIT 1");
	$STH->execute([':stand_rights' => $rights]);

	exit(json_encode(['status' => '1']));
}
if(isset($_POST['send_email_message'])) {
	$text        = $_POST['text'];
	$email       = $_POST['email'];
	$subject     = check($_POST['subject'], null);
	$dubug       = check($_POST['dubug'], null);
	$dubug_value = check($_POST['dubug_value'], null);

	if(empty($email)) {
		exit("<p class='text-danger mt-10 mb-0'>Введите e-mail получателя</p>");
	}

	if($dubug == 2) {
		$dubug_value = 0;
	}

	incNotifications();
	$letter = letter_byadmin($conf->name, $text);

	$emails = explode(",", $email);
	for($i = 0; $i < count($emails); $i++) {
		$emails[$i] = check($emails[$i], null);
		if($dubug_value != 0) {
			echo "<pre class='mt-5'>";
		}
		sendmail($emails[$i], $subject, $letter, $pdo, 0, $dubug_value);
		if($dubug_value != 0) {
			echo "</pre>";
		}
	}

	exit("<p class='text-success mt-10 mb-0'>Сообщение отправлено</p>");
}
if(isset($_POST['load_users'])) {
	$start = checkJs($_POST['start'], "int");
	if($_POST['group'] === 'multi_accounts') {
		$group = 'multi_accounts';
	} else {
		$group = clean($_POST['group'], "int");
	}
	if(empty($start)) {
		$start = 0;
	}
	if(empty($group)) {
		$group = 0;
	}

	$limit = 30;

	if($group === 'multi_accounts') {
		$STH = $pdo->query(
			"SELECT id,login,avatar,rights from users WHERE multi_account!='0' LIMIT " . $start . ", " . $limit
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);
	} elseif($group == 0) {
		$STH = $pdo->query("SELECT id,login,avatar,rights from users LIMIT " . $start . ", " . $limit);
		$STH->setFetchMode(PDO::FETCH_OBJ);
	} else {
		$STH = $pdo->query(
			"SELECT id,login,avatar,rights from users WHERE rights='$group' LIMIT " . $start . ", " . $limit
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);
	}
	while($row = $STH->fetch()) {
		?>
		<div class="col-md-2" id="<?php echo $row->id ?>">
			<div class="block">
				<div class="block_head">
					<a class="c-fff" target="_blank" href="../profile?id=<?php echo $row->id ?>"><?php echo $row->login ?></a>
				</div>
				<a class="c-fff" target="_blank" href="../profile?id=<?php echo $row->id ?>">
					<img class="img-thumbnail w100" alt="<?php echo $row->login ?>" src=" ../<?php echo $row->avatar ?>"></img>
				</a>
				<ul class="navigation2">
					<li>
						<a class="c-fff" href="../admin/edit_user?id=<?php echo $row->id ?>">
							<span class="glyphicon glyphicon-list-alt"></span>
							Редактировать данные
						</a>
					</li>
					<li onclick="dell_user('<?php echo $row->id ?>')">
						<a class="c-fff">
							<span class="glyphicon glyphicon-trash"></span>
							Удалить пользователя
						</a>
					</li>
				</ul>
			</div>
		</div>
		<?php
	}
	exit();
}
if(isset($_POST['search_login'])) {
	$login = check($_POST['login'], null);
	if($_POST['group'] === 'multi_accounts') {
		$group = 'multi_accounts';
	} else {
		$group = clean($_POST['group'], "int");
	}
	if(empty($group)) {
		$group = 0;
	}

	if(empty($login)) {
		exit('<p class="text-danger pd-15">Введите логин пользователя</p>');
	}

	$i = 0;
	if($group === 'multi_accounts') {
		$STH = $pdo->prepare(
			"SELECT id,login,avatar,nick,birth,skype,vk,rights,name,regdate from users WHERE login LIKE :login or id = :id AND multi_account!='0'"
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([":login" => "%" . strip_data($login) . "%", ":id" => $login]);
	} elseif($group == 0) {
		$STH = $pdo->prepare(
			"SELECT id,login,avatar,nick,birth,skype,vk,rights,name,regdate from users WHERE login LIKE :login or id = :id"
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([":login" => "%" . strip_data($login) . "%", ":id" => $login]);
	} else {
		$STH = $pdo->prepare(
			"SELECT id,login,avatar,nick,birth,skype,vk,rights,name,regdate FROM users WHERE rights=:group AND (login LIKE :login OR id = :id)"
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([":login" => "%" . strip_data($login) . "%", ":id" => $login, ":group" => $group]);
	}
	while($row = $STH->fetch()) {
		$i++;
		?>
		<div class="col-md-2" id="<?php echo $row->id ?>">
			<div class="block">
				<div class="block_head">
					<a class="c-fff" target="_blank" href="../profile?id=<?php echo $row->id ?>"><?php echo $row->login ?></a>
				</div>
				<a class="c-fff" target="_blank" href="../profile?id=<?php echo $row->id ?>">
					<img class="img-thumbnail w100" alt="<?php echo $row->login ?>" src=" ../<?php echo $row->avatar ?>"></img>
				</a>
				<ul class="navigation2">
					<a class="c-fff" href="../admin/edit_user?id=<?php echo $row->id ?>">
						<li>
							<span class="glyphicon glyphicon-list-alt"></span>
							Редактировать данные
						</li>
					</a>
					<a class="c-fff" onclick="dell_user('<?php echo $row->id ?>')">
						<li>
							<span class="glyphicon glyphicon-trash"></span>
							Удалить пользователя
						</li>
					</a>
				</ul>
			</div>
		</div>
		<?php
	}
	if($i == 0) {
		echo '<p class="text-danger pd-15">Пользователь с данным логином не найден.</p>';
	}
}
if(isset($_POST['dell_module'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit (json_encode(['status' => '2']));
	}

	$STH = $pdo->prepare("SELECT name FROM modules WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $id]);
	$row = $STH->fetch();

	$base_path = '../modules_extra/' . $row->name . "/settings/base_dell.sql";
	if(file_exists($base_path)) {
		$pdo->exec(trim(file_get_contents($base_path)));
		unlink($base_path);
	};
	removeDirectory('../modules_extra/' . $row->name . '/');

	$pdo->exec("DELETE FROM `modules` WHERE id='$id'");
	$pdo->exec("DELETE FROM `pages` WHERE module='$id'");

	exit(json_encode(['status' => '1']));
}
if(isset($_POST['off_module'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit (json_encode(['status' => '2']));
	}

	$STH = $pdo->prepare("UPDATE modules SET active=:active WHERE id=:id LIMIT 1");
	$STH->execute([':active' => '2', ':id' => $id]);

	$STH = $pdo->prepare("UPDATE pages SET active=:active WHERE module=:module AND type='1'");
	$STH->execute([':active' => '2', ':module' => $id]);

	exit(json_encode(['status' => '1']));
}
if(isset($_POST['on_module'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit (json_encode(['status' => '2']));
	}

	$STH = $pdo->prepare("UPDATE modules SET active=:active WHERE id=:id LIMIT 1");
	$STH->execute([':active' => '1', ':id' => $id]);

	$STH = $pdo->prepare("UPDATE pages SET active=:active WHERE module=:module AND type='1'");
	$STH->execute([':active' => '1', ':module' => $id]);

	exit(json_encode(['status' => '1']));
}
if(isset($_POST['load_modules'])) {
	$i   = 0;
	$STH = $pdo->prepare("SELECT `id`, `name`, `info`, `active` FROM `modules` ORDER BY `id` DESC");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute();
	while($row = $STH->fetch()) {
		$i++;
		if($row->active == 1) {
			$class = 'success';
			$word  = 'Включен';
			$btn   = '<button class="btn btn-default btn-sm f-l" onclick="off_module('.$row->id.')">Выключить</button>';
		} else {
			$class = 'danger';
			$word  = 'Выключен';
			$btn   = '<button class="btn btn-default btn-sm f-l" onclick="on_module('.$row->id.')">Включить</button>';
		}
		?>
		<div class="block" id="<?php echo $row->id; ?>">
			<div class="block_head">
				<?php echo $row->name; ?>
			</div>
			<p>Статус: <i class="text-<?php echo $class; ?>"><?php echo $word; ?></i></p>
			<hr>
			<?php echo $row->info; ?>
			<div class="clearfix"></div>
			<hr>
			<button class="btn btn-default btn-sm f-l mr-5" onclick="dell_module('<?php echo $row->id; ?>')">Удалить</button>
			<?php echo $btn; ?>
		</div>
		<?php
	}
	if($i == 0) {
		echo 'Нет установленных модулей';
	}
	exit();
}
if(isset($_POST['install_module'])) {
	if($safe_mode == 1) {
		exit('<p class="text-danger">Установка собственных модулей режиме безопасной эксплуатации запрещена!</p>');
	}

	if(empty($_FILES['zip_file']['name'])) {
		exit('<p class="text-danger">Выберите архив с модулем!</p>');
	} else {
		$path = '../modules_extra/';

		if(if_zip($_FILES['zip_file']['name'])) {
			$filename    = $_FILES['zip_file']['name'];
			$module_name = substr($_FILES['zip_file']['name'], 0, -4);
			$source      = $_FILES['zip_file']['tmp_name'];
			$target      = $path.$filename;
			if(!move_uploaded_file($source, $target)) {
				exit('<p class="text-danger">Ошибка загрузки архива</p>');
			}

			$STH = $pdo->prepare("SELECT `id` FROM `modules` WHERE `name`=:name LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':name' => $module_name));
			$row = $STH->fetch();
			if(isset($row->id)) {
				unlink($target);
				exit('<p class="text-danger">Данный модуль уже установлен</p>');
			}

			$archive = new PclZip($target);
			$result  = $archive->extract(PCLZIP_OPT_PATH, $path);
			unlink($target);
		} else {
			exit('<p class="text-danger">Модуль должен быть расширения ZIP</p>');
		}

		$tpls_path  = $path.$module_name."/settings/tpls.txt";
		$pages_path = $path.$module_name."/settings/pages.txt";
		$base_path  = $path.$module_name."/settings/base.sql";
		$info_path  = $path.$module_name."/settings/info.txt";
		$files_path = $path.$module_name."/settings/files.txt";
		if(file_exists($tpls_path)) {
			$tpls = file_get_contents(trim($tpls_path));
			unlink($tpls_path);
		} else {
			$tpls = 'none';
		}
		if(file_exists($pages_path)) {
			$pages = file_get_contents(trim($pages_path));
			eval('$pages = '.$pages);
			unlink($pages_path);
		} else {
			$pages = 'none';
		}
		if(file_exists($base_path)) {
			$pdo->exec(trim(file_get_contents($base_path)));
			unlink($base_path);
		}
		if(file_exists($info_path)) {
			$info = file_get_contents(trim($info_path));
			unlink($info_path);
		}
		if(file_exists($files_path)) {
			$files = file_get_contents(trim($files_path));
			unlink($files_path);
		} else {
			$files = '';
		}

		$STH = $pdo->prepare("INSERT INTO `modules` (`name`,`tpls`,`info`,`files`) values (:name, :tpls, :info, :files)");
		$STH->execute(array(':name' => $module_name, ':tpls' => $tpls, ':info' => $info, ':files' => $files));

		if(is_array($pages)) {
			$module_id = get_ai($pdo, "modules");
			$module_id--;

			for($i = 0; $i < count($pages); $i++) {
				$STH = $pdo->prepare("INSERT INTO pages (file,url,name,title,description,keywords,kind,image,robots,privacy,type,active,module,page,class) VALUES (:file, :url, :name, :title, :description, :keywords, :kind, :image, :robots, :privacy, :type, :active, :module, :page, :class)");
				$STH->execute(array(':file'        => $pages[$i]['file'],
									':url'         => $pages[$i]['url'],
									':name'        => $pages[$i]['name'],
									':title'       => $pages[$i]['title'],
									':description' => $pages[$i]['description'],
									':keywords'    => $pages[$i]['keywords'],
									':kind'        => $pages[$i]['kind'],
									':image'       => $pages[$i]['image'],
									':robots'      => $pages[$i]['robots'],
									':privacy'     => $pages[$i]['privacy'],
									':type'        => $pages[$i]['type'],
									':active'      => $pages[$i]['active'],
									':module'      => $module_id,
									':page'        => '0',
									':class'       => '0'));
			}
		}

		echo '<p class="text-success">Модуль успешно установлен</p>';
	}
	exit("<script>load_modules();</script>");
}
if(isset($_POST['install_module_by_key'])) {
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	
	$key = checkJs($_POST['key'], null);
	if(empty($key)) {
		exit(json_encode(['status' => '2', 'message' => 'Введите ключ.']));
	}
	
	$STH = $pdo->prepare("SELECT id FROM modules WHERE client_key=:client_key LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':client_key' => $key]);
	$row = $STH->fetch();
	if(isset($row->id)) {
		exit(json_encode(['status' => '2', 'message' => 'Ключ уже использован.']));
	}

	$STH = $pdo->query("SELECT version FROM config__secondary LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	
	if(check_update_server($pdo, $conf->update_server)) {
		$r_versions = check_update_version($pdo, $row->version);
		if(!$r_versions['status']) {
			exit(json_encode(array('status' => '2', 'message' => 'Обновите движок, используется устаревшая версия.')));
		}
		else {
			ignore_user_abort(1);
			set_time_limit(0);
			
			$result = curl_get_process([
				'website' => "https://" . get_update_url($pdo) . "/api?type=downloads",
				'data' => "&product=gamecms&server_name={$_SERVER['SERVER_NAME']}&code={$_POST['key']}"
			]);
			
			$result = json_decode(gzdecode($result), true);
			
			if($result['status'] == 2) {
				exit(json_encode(array('status' => '2', 'message' => $result['message'])));
			}
		}
	}
	else {
		exit(json_encode(array('status' => '2', 'message' => 'Главный сервер не доступен')));
	}
	
	$link        = $result['file'];
	$arr         = explode("/", $link);
	$zip_file    = $arr[count($arr) - 1];
	$module_name = $result['name'];

	$STH = $pdo->prepare("SELECT id FROM modules WHERE name=:name LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':name' => $module_name]);
	$row = $STH->fetch();
	
	if(isset($row->id)) {
		exit(json_encode(['status' => '2', 'message' => 'Данный модуль уже установлен']));
	}

	$path = $_SERVER['DOCUMENT_ROOT'] . '/modules_extra/';
	if(!file_exists($path)) {
		mkdir($path, 0777);
	}
	
	$update_file = $path.$zip_file;
	
	$cInit = curl_init($link);
	$fOpen = fopen($update_file, "wb");
	curl_setopt($cInit, CURLOPT_FILE, $fOpen);
	curl_setopt($cInit, CURLOPT_HEADER, 0);
	curl_exec($cInit);
	curl_close($cInit);
	fclose($fOpen);
	
	$archive = new PclZip($update_file);
	$result  = $archive->extract(PCLZIP_OPT_PATH, $path);
	unlink($update_file);

	$tpls_path  = $path . $module_name . "/settings/tpls.txt";
	$pages_path = $path . $module_name . "/settings/pages.txt";
	$base_path  = $path . $module_name . "/settings/base.sql";
	$info_path  = $path . $module_name . "/settings/info.txt";
	$files_path = $path . $module_name . "/settings/files.txt";
	if(file_exists($tpls_path)) {
		$tpls = file_get_contents(trim($tpls_path));
		unlink($tpls_path);
	} else {
		$tpls = 'none';
	}
	if(file_exists($pages_path)) {
		$pages = file_get_contents(trim($pages_path));
		eval('$pages = ' . $pages);
		unlink($pages_path);
	} else {
		$pages = 'none';
	}
	if(file_exists($base_path)) {
		$pdo->exec(trim(file_get_contents($base_path)));
		unlink($base_path);
	}
	
	if(file_exists($info_path)) {
		$info = file_get_contents(trim($info_path));
		unlink($info_path);
	}
	else {
		$info = "";
	}
	
	if(file_exists($files_path)) {
		$files = file_get_contents(trim($files_path));
		unlink($files_path);
	} else {
		$files = '';
	}

	$STH = $pdo->prepare(
		"INSERT INTO modules (name,tpls,info,files,client_key) values (:name, :tpls, :info, :files, :client_key)"
	);
	$STH->execute(
		[':name' => $module_name, ':tpls' => $tpls, ':info' => $info, ':files' => $files, ':client_key' => $key]
	);
	
	if(is_array($pages)) {
		$module_id = get_ai($pdo, "modules");
		$module_id--;

		for($i = 0; $i < count($pages); $i++) {
			$STH = $pdo->prepare(
				"INSERT INTO pages (file,url,name,title,description,keywords,kind,image,robots,privacy,type,active,module,page,class) values (:file, :url, :name, :title, :description, :keywords, :kind, :image, :robots, :privacy, :type, :active, :module, :page, :class)"
			);
			$STH->execute(
				[
					':file'        => $pages[$i]['file'],
					':url'         => $pages[$i]['url'],
					':name'        => $pages[$i]['name'],
					':title'       => $pages[$i]['title'],
					':description' => $pages[$i]['description'],
					':keywords'    => $pages[$i]['keywords'],
					':kind'        => $pages[$i]['kind'],
					':image'       => $pages[$i]['image'],
					':robots'      => $pages[$i]['robots'],
					':privacy'     => $pages[$i]['privacy'],
					':type'        => $pages[$i]['type'],
					':active'      => $pages[$i]['active'],
					':module'      => $module_id,
					':page'        => '0',
					':class'       => '0'
				]
			);
		}
	}

	exit(json_encode(['status' => '1', 'message' => 'Модуль успешно установлен']));
}

if(isset($_POST['install_template_by_key'])) {
	$key = checkJs($_POST['key'], null);
	if(empty($key)) {
		exit(json_encode(array('status' => '2', 'message' => 'Введите ключ.')));
	}

	$STH = $pdo->query("SELECT version FROM config__secondary LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	
	if(check_update_server($pdo, $conf->update_server)) {
		$r_versions = check_update_version($pdo, $row->version);
		if(!$r_versions['status']) {
			exit(json_encode(array('status' => '2', 'message' => 'Обновите движок, используется устаревшая версия.')));
		}
		else {
			ignore_user_abort(1);
			set_time_limit(0);
			
			$result = curl_get_process([
				'website' => "https://" . get_update_url($pdo) . "/api?type=downloads",
				'data' => "&product=gamecms&server_name={$_SERVER['SERVER_NAME']}&code={$_POST['key']}"
			]);
			
			$result = json_decode(gzdecode($result), true);
			
			if($result['status'] == 2) {
				exit(json_encode(array('status' => '2', 'message' => $result['message'])));
			}
		}
	}
	else {
		exit(json_encode(array('status' => '2', 'message' => 'Главный сервер не доступен')));
	}

	$link          = $result['file'];
	$arr           = explode("/", $link);
	$zip_file      = $arr[count($arr) - 1];
	$template_name = $result['name'];

	$path = '../templates/';
	mkdir($path, 0777);

	$update_file = $path.$zip_file;
	
	$cInit = curl_init($link);
	$fOpen = fopen($update_file, "wb");
	curl_setopt($cInit, CURLOPT_FILE, $fOpen);
	curl_setopt($cInit, CURLOPT_HEADER, 0);
	curl_exec($cInit);
	curl_close($cInit);
	fclose($fOpen);

	$archive = new PclZip($update_file);
	$result  = $archive->extract(PCLZIP_OPT_PATH, $path);
	unlink($update_file);

	exit(json_encode(array('status' => '1', 'message' => $template_name)));
}
if(isset($_POST['replace_tpl_img'])) {
	$folderId = clean($_POST['data'], null);

	if($host == 'test.worksma.ru') {
		exit('<p class="text-danger">Загрузка изображений в тестовой версии движка запрещена!</p>');
	}

	if(empty($_POST['folder'])) {
		$folder = substr($_POST['img_name'], 0, strrpos($_POST['img_name'], "."));
	} else {
		$folder = $_POST['folder'];
	}

	if(
		(
			!stristr($folder, "templates/")
			&& !stristr($folder, "files/forums_imgs/")
			&& !stristr($folder, "files/maps_imgs/")
			&& !stristr($folder, "files/news_imgs/")
			&& !stristr($folder, "files/ranks_imgs/")
		)
		|| stristr($folder, "..")
		|| stristr($folder, "./")
	) {
		exit('<p class="text-danger">Загрузка файла невозможна</p>');
	}

	if(empty($_FILES['tpl_img']['name'])) {
		exit('<script>show_input_error("tpl_img", "", null);setTimeout(show_error, 500);</script>');
	} else {
		if(if_img($_FILES['tpl_img']['name']) || if_ico($_FILES['tpl_img']['name'])) {
			$source = $_FILES['tpl_img']['tmp_name'];

			if(empty($_POST['folder'])) {
				$fileName = substr($_POST['img_name'], strrpos($_POST['img_name'], "."));
				$tpl_img  = $folder . $fileName;

				if(
					!file_exists('../' . $tpl_img)
					|| !is_writable('../' . $tpl_img)
					|| !(if_img($fileName) || if_ico($fileName))
				) {
					exit('<p class="text-danger">Сохранение невозможно! Установите необходимые права на файл</p>');
				}
			} else {
				$fileName = translit(clean_str($_FILES['tpl_img']['name']));
				$tpl_img  = $folder . $fileName;
			}

			$target = '../' . $tpl_img;

			if(!move_uploaded_file($source, $target)) {
				exit('<p class="text-danger">Ошибка загрузки файла!</p>');
			}
		} else {
			exit('<p class="text-danger">Изображение должено быть в формате JPG, GIF, BMP, ICO или PNG</p><script>show_input_error("tpl_img", "", null);setTimeout(show_error, 500);</script>');
		}
		?>
		<script>
          $('#img').empty();
          $('#img').
            append(
              '<img class="img-thumbnail black" src="../<?php echo $tpl_img; ?>?anti_cache=' + (new Date()).getTime() +
              '" alt="<?php echo $tpl_img; ?>">');
          setTimeout(show_ok, 500);
		</script>
		<?php
		if(!empty($_POST['folder'])) {
			if($folderId == 'data3') {
				?>
				<script>
                  $('<ol class="tree"><li class="file"><a onclick="get_content_tpl(\'<?php echo $tpl_img; ?>\', \'img\');" alt="<?php echo $tpl_img; ?>" class="c-p"><?php echo $fileName; ?></a></li></ol>').
                    insertBefore("#<?php echo $folderId; ?>");
				</script>
				<?php
			} else {
				?>
				<script>
                  $('<li class="file"><a onclick="get_content_tpl(\'<?php echo $tpl_img; ?>\', \'img\');" alt="<?php echo $tpl_img; ?>" class="c-p"><?php echo $fileName; ?></a></li>').
                    insertAfter("#<?php echo $folderId; ?>");
				</script>
				<?php
			}
		}
	}
	exit();
}
if(isset($_POST['load_stickers'])) {
	$STH = $pdo->query("SELECT * FROM stickers");
	$STH->execute();
	$row   = $STH->fetchAll();
	$count = count($row);
	for($i = 0; $i < $count; $i++) {
		$name_translit = translit($row[$i]['name']);
		?>
		<div class="panel panel-default" id="stickers_<?php echo $row[$i]['id']; ?>">
			<div class="panel-heading" role="tab" id="headingOne">
				<h4 class="panel-title">
					<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $row[$i]['id']; ?>" aria-controls="collapse<?php echo $row[$i]['id']; ?>">
						<?php echo $row[$i]['name']; ?>
					</a>
					<a class="c-p" onclick="delete_stickers(<?php echo $row[$i]['id']; ?>);">(Удалить)</a>
				</h4>
			</div>
			<div id="collapse<?php echo $row[$i]['id']; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
				<div class="panel-body">
					<div id="stickers_box<?php echo $row[$i]['id']; ?>">
						<?php
						$files  = scandir('../files/stickers/' . $name_translit, 1);
						$count2 = count($files);
						for($j = $count2 - 1; $j > -1; $j--) {
							$exp = explode(".", $files[$j]);
							$exp = end($exp);
							if(strnatcasecmp($exp, 'png') == 0 or strnatcasecmp($exp, 'jpg') == 0) {
								?>
								<div class="sticker_edit" id="<?php echo $row[$i]['id'] . $j; ?>">
									<img src="../files/stickers/<?php echo $name_translit; ?>/<?php echo $files[$j]; ?>"><br>
									<a class="btn btn-default btn-xs c-p w-100" onclick="delete_sticker('../files/stickers/<?php echo $name_translit; ?>/<?php echo $files[$j]; ?>', '<?php echo $row[$i]['id'] . $j; ?>');">Удалить</a>
								</div>
								<?php
							}
						}
						?>
					</div>
					<div class="clearfix"></div>
					<br>
					<form id="dropzone<?php echo $row[$i]['id']; ?>" class="dropzone">
						<input type="hidden" id="token" name="token" value="<?php echo $token; ?>">
						<input type="hidden" id="upload_stickers" name="upload_stickers" value="1">
						<input type="hidden" id="phpaction" name="phpaction" value="1">
						<input type="hidden" id="path" name="path" value="../files/stickers/<?php echo $name_translit; ?>/">
					</form>
					<small>*Разрешенные для загрузки файлы: jpg, png, максимальное количество загружаемых файлов за раз: 30шт.</small>
					<script>
                      var myDropzone = new Dropzone("#dropzone<?php echo $row[$i]['id']; ?>", {
                        url: '../ajax/actions_panel.php'
                      });
                      myDropzone.on('complete', function (file) {
                        //myDropzone.removeAllFiles(true);
                        load_new_srickers(<?php echo $row[$i]['id']; ?>);
                      });
					</script>
				</div>
			</div>
		</div>
		<?php
	}
}
if(isset($_POST['load_new_srickers'])) {
	$id = checkJs($_POST['id'], "int");
	if(empty($id)) {
		exit ();
	}
	$STH = $pdo->prepare("SELECT id,name FROM stickers WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $id]);
	$row           = $STH->fetch();
	$name_translit = translit($row->name);

	$files  = scandir('../files/stickers/' . $name_translit, 1);
	$count2 = count($files);
	for($j = $count2 - 1; $j > -1; $j--) {
		$exp = explode(".", $files[$j]);
		$exp = end($exp);
		if(strnatcasecmp($exp, 'png') == 0 or strnatcasecmp($exp, 'jpg') == 0) {
			?>
			<div class="sticker_edit" id="<?php echo $row->id . $j; ?>">
				<img src="../files/stickers/<?php echo $name_translit; ?>/<?php echo $files[$j]; ?>"><br>
				<a class="btn btn-default btn-xs c-p w-100" onclick="delete_sticker('../files/stickers/<?php echo $name_translit; ?>/<?php echo $files[$j]; ?>', '<?php echo $row->id . $j; ?>');">Удалить</a>
			</div>
			<?php
		}
	}
}
if(isset($_POST['upload_stickers'])) {
	$path = $_POST['path'];

	$file      = clean_str($_FILES['file']['name']);
	$file      = translit($file);
	$exp       = explode(".", $file);
	$file_name = reset($exp);
	$exp       = end($exp);
	if((clean($file_name, 'int') == $file_name) and (mb_strlen($file_name, 'UTF-8') == 1)) {
		$file = '0' . $file;
	}

	if(strnatcasecmp($exp, 'png') == 0 or strnatcasecmp($exp, 'jpg') == 0) {
		move_uploaded_file($_FILES['file']['tmp_name'], $path . $file);
	}
}
if(isset($_POST['add_stickers'])) {
	$name = check($_POST['name'], null);

	if(empty($name)) {
		exit(json_encode(['status' => '2']));
	}
	$name = clean_str($name);

	if(isset($name)) {
		if(mb_strlen($name, 'UTF-8') > 50) {
			exit(json_encode(['status' => '2']));
		}
	}

	$STH = $pdo->prepare("SELECT id FROM stickers WHERE name=:name LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':name' => $name]);
	$row = $STH->fetch();
	if(isset($row->id)) {
		exit(json_encode(['status' => '2']));
	}

	$STH = $pdo->prepare("INSERT INTO stickers (name) values (:name)");
	if($STH->execute([':name' => $name]) == '1') {
		$name = translit($name);
		mkdir('../files/stickers/' . $name . '/', 0777);
		chmod('../files/stickers/' . $name . '/', 0777);
		exit(json_encode(['status' => '1']));
	} else {
		exit(json_encode(['status' => '2']));
	}
}
if(isset($_POST['delete_stickers'])) {
	$id = check($_POST['id'], "int");
	if(empty($id)) {
		exit(json_encode(['status' => '2']));
	}
	$STH = $pdo->prepare("SELECT * FROM stickers WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $id]);
	$row = $STH->fetch();
	if(empty($row->name)) {
		exit(json_encode(['status' => '2']));
	}

	$name = translit($row->name);
	removeDirectory('../files/stickers/' . $name . '/');
	$STH = $pdo->prepare("DELETE FROM stickers WHERE id=:id LIMIT 1");
	$STH->execute([':id' => $id]);
	exit(json_encode(['status' => '1']));
}
if(isset($_POST['delete_sticker'])) {
	$path = check($_POST['path'], null);
	unlink($path);
	exit(json_encode(['status' => '1']));
}

if(isset($_POST['load_vouchers'])) {
	$load_val = checkJs($_POST['load_val'], "int");
	if(empty($load_val)) {
		$load_val = 1;
	}

	$start = ($load_val - 1) * 20;
	$end   = 20;
	$i     = $start;
	$i2    = 0;

	$STH = $pdo->query("SELECT * FROM `vouchers` ORDER BY id DESC LIMIT " . $start . ", " . $end);
	$STH->execute();
	$row   = $STH->fetchAll();
	$count = count($row);
	for($l = 0; $l < $count; $l++) {
		$i++;
		$i2++;
		if($row[$l]['status'] == 0) {
			$status = 'Не активирован';
		} else {
			$STH = $pdo->prepare("SELECT id, login, avatar FROM users WHERE id=:val LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute([':val' => $row[$l]['status']]);
			$user   = $STH->fetch();
			$status = 'Активирован: <a target="_blank" href="../admin/edit_user?id=' . $user->id . '"><img src="../' . $user->avatar . '" alt="' . $user->login . '"> ' . $user->login . '</a>';
		}
		?>
		<tr id="voucher_<?php echo $row[$l]['id']; ?>">
			<td><?php echo $i; ?></td>
			<td><?php echo $row[$l]['val'] . $messages['RUB']; ?>.</td>
			<td><?php echo $row[$l]['key']; ?></td>
			<td><?php echo $status; ?></td>
			<td>
				<a class="c-p" title="Удалить ваучер" onclick="delete_voucher(<?php echo $row[$l]['id']; ?>);">Удалить</a>
			</td>
		</tr>
		<?php
	}
	if($i == 0) {
		exit('<tr><td colspan="10">Ваучеров нет</td></tr>');
	}
	if(($load_val > 0) and ($i2 > 19)) {
		$load_val++;
		exit ('<tr id="loader_' . $load_val . '" onclick="load_vouchers(\'' . $load_val . '\');" class="c-p"><td colspan="10">Подгрузить ваучеры</td></tr>');
	}
	if(($load_val > 0) and ($i2 < 20)) {
		exit ();
	}
}
if(isset($_POST['add_vouchers'])) {
	$voucher_val = check($_POST['voucher_val'], "int");
	$voucher_col = check($_POST['voucher_col'], "int");

	if(empty($voucher_val) or empty($voucher_col)) {
		exit(json_encode(['status' => '2', 'data' => '<p class="text-danger">Укажите всю информацию</p>']));
	}
	if($voucher_val == 0 or $voucher_val > 99999) {
		exit(
		json_encode(
			['status' => '2', 'data' => '<p class="text-danger">Сумма должна быть не менее 1 и не более 99999</p>']
		)
		);
	}
	if($voucher_col == 0 or $voucher_col > 10) {
		exit(
		json_encode(
			['status' => '2', 'data' => '<p class="text-danger">Количество должно быть не менее 1 и не более 10</p>']
		)
		);
	}

	$j = 0;
	for($i = 0; $i < $voucher_col; $i++) {
		$key = crate_pass(10, 2);
		$STH = $pdo->prepare("SELECT id FROM vouchers WHERE `key`=:key LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':key' => $key]);
		$row = $STH->fetch();
		if(empty($row->id)) {
			$STH = $pdo->prepare("INSERT INTO vouchers (val, `key`) values (:val, :key)");
			if($STH->execute([':val' => $voucher_val, ':key' => $key]) == '1') {
				$j++;
			}
		}
	}
	exit(json_encode(['status' => '1', 'data' => '<p class="text-success">Создано ' . $j . ' ваучеров</p>']));
}
if(isset($_POST['delete_voucher'])) {
	$id = check($_POST['id'], "int");
	if(empty($id)) {
		exit(json_encode(['status' => '2']));
	}
	$STH = $pdo->prepare("DELETE FROM vouchers WHERE id=:id LIMIT 1");
	$STH->execute([':id' => $id]);
	exit(json_encode(['status' => '1']));
}
if(isset($_POST['edit_unmute'])) {
	$price1 = checkJs($_POST['price1'], "float");
	$price2 = checkJs($_POST['price2'], "float");
	$price3 = checkJs($_POST['price3'], "float");

	if(empty($price1)) {
		$price1 = 0;
	}
	if(empty($price2)) {
		$price2 = 0;
	}
	if(empty($price3)) {
		$price3 = 0;
	}

	$STH = $pdo->prepare("UPDATE config__prices SET price2_1=:price2_1,price2_2=:price2_2,price2_3=:price2_3 LIMIT 1");
	$STH->execute([':price2_1' => $price1, ':price2_2' => $price2, ':price2_3' => $price3]);

	write_log(
		"Отредактирована цена размута: price2_1 - " . $price1 . "; price2_2 - " . $price2 . "; price2_3 - " . $price3 . ";"
	);
	exit('<p class="text-success">Настройки изменены!</p>');
}

if(isset($_POST['edit_update_server'])) {
	if(set_update_server($pdo, $_POST['server_id'])) {
		exit(json_encode([
			'status' => '1',
			'body' => get_update_servers($pdo, $_POST['server_id']),
			'enable' => check_update_server($pdo, $_POST['server_id'])
		]));
	}
	
	exit(json_encode(['status' => '2']));
}

if(isset($_POST['get_main_info'])) {
	$STH = $pdo->query("SELECT version, update_link FROM config__secondary LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	if(check_update_server($pdo, $conf->update_server)) {
		$r_versions = check_update_version($pdo, $row->version);
		if(!$r_versions['status']) {
			$remoteVersions = $r_versions['versions'];
			
			exit(json_encode([
				'version' => "<font class=\"text-danger\">{$row->version}</font> (Доступно обновление <font class=\"text-success\">{$remoteVersions[$r_versions['index'] + 1]['version']}</font>: <a class='c-p' onclick='getFeedback({$remoteVersions[$r_versions['index'] + 1]['id']});' data-toggle='modal' data-target='#feedback'>отзывы</a> | <a class='c-p' onclick='get_update();'>получить</a>)",
				'message' => $remoteVersions[$r_versions['index'] + 1]['description']
			]));
		}
		else {
			exit(json_encode([
				'version' => "{$row->version} (Используется актуальная версия)"
			]));
		}
	}
	else {
		exit(json_encode([
			'version' => "{$row->version} <font class=\"text-muted\">(Сервер не доступен)</font>"
		]));
	}
}

if(isset($_POST['get_update'])) {
	$pdo->query("UPDATE `config__secondary` SET `update_link`='' LIMIT 1");
	
	$STH = $pdo->query("SELECT version, update_link FROM config__secondary LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	
	if(check_update_server($pdo, $conf->update_server)) {
		$r_versions = check_update_version($pdo, $row->version);
		if(r_versions['status']) {
			$remoteVersions = $r_versions['versions'];
			
			if(empty($row->update_link)) {
				ignore_user_abort(1);
				set_time_limit(0);
				
				$gz = json_encode([
					'version' => $remoteVersions[$r_versions['index'] + 1]['version'],
					'file' => $remoteVersions[$r_versions['index'] + 1]['file'],
					'request' => base64_encode($remoteVersions[$r_versions['index'] + 1]['request'])
				]);
				
				if($pdo->query("UPDATE `config__secondary` SET `update_link`='{$gz}' LIMIT 1")) {
					exit(json_encode(array('status' => '1')));
				}
					
				exit(json_encode(array('status' => '2', 'message' => "Ошибка внесения ссылки на обновление!")));
			}
		}
		else {
			exit(json_encode(array('status' => '2', 'message' => 'Используется актуальная версия')));
		}
	}
	else {
		exit(json_encode(array('status' => '2', 'message' => 'Главный сервер не доступен')));
	}
}
if(isset($_POST['clear_banlist'])) {
	$id = checkJs($_POST['id'], "int");
	$clearType = checkJs($_POST['type'], "int");

	if(empty($id)) {
		exit (json_encode(['status' => 2]));
	}

	$STH = $pdo->prepare(
		"SELECT id,ip,port,db_host,db_user,db_pass,db_db,db_prefix,type,db_code FROM servers WHERE (type!=0 and type!=1 and id=:id) LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $id]);
	$row = $STH->fetch();
	if(empty($row->id)) {
		exit (json_encode(['status' => 2]));
	}
	$db_host   = $row->db_host;
	$db_user   = $row->db_user;
	$db_pass   = $row->db_pass;
	$db_db     = $row->db_db;
	$db_prefix = $row->db_prefix;
	$address   = $row->ip . ':' . $row->port;
	$ip        = $row->ip;
	$port      = $row->port;
	$type      = $row->type;

	if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
		exit (json_encode(['status' => 2]));
	}

	set_names($pdo2, $row->db_code);

	if($type == '2' || $type == '3' || $type == '5') {
		$table = set_prefix($db_prefix, 'bans');

		if($clearType == 1) {
			$pdo2->prepare("DELETE FROM $table WHERE `server_ip`=:server_ip")->execute([':server_ip' => $address]);
		} else {
			$STH = $pdo2->prepare(
				"DELETE FROM $table WHERE (`server_ip`=:server_ip AND `ban_length`*60+`ban_created` < :time AND `ban_length` != '0') OR (`server_ip`=:server_ip AND `expired` = '1')"
			);
			$STH->execute([':server_ip' => $address, ':time' => time()]);
		}
	} else {
		$table = set_prefix($db_prefix, 'servers');
		$STH   = $pdo2->query("SELECT sid FROM $table WHERE ip='$ip' and port='$port' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row   = $STH->fetch();
		$sid   = $row->sid;
		$table = set_prefix($db_prefix, 'bans');

		if($clearType == 1) {
			$pdo2->prepare("DELETE FROM $table WHERE `sid`=:sid")->execute([':sid' => $sid]);
		} else {
			$STH = $pdo2->prepare(
				"DELETE FROM $table WHERE (`sid`=:sid AND `ends` < :time AND `length` != '0') OR (`sid`=:sid AND `RemoveType` = 'U') OR (`sid`=:sid AND `RemoveType` = 'E')"
			);
			$STH->execute([':sid' => $sid, ':time' => time()]);
		}
	}

	exit(json_encode(['status' => 1]));
}
if(isset($_POST['clear_mutlist'])) {
	$id = checkJs($_POST['id'], "int");
	$clearType = checkJs($_POST['type'], "int");

	if(empty($id)) {
		exit (json_encode(['status' => 2]));
	}

	$STH = $pdo->prepare(
		"SELECT id,ip,port,db_host,db_user,db_pass,db_db,db_prefix,type,db_code FROM servers WHERE type!=0 AND id=:id LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $id]);
	$row = $STH->fetch();
	if(empty($row->id)) {
		exit (json_encode(['status' => 2]));
	}
	$type = $row->type;

	if($type == '1' || $type == '2' || $type == '3' || $type == '5') {
		if(check_table('comms', $pdo)) {
			if($clearType == 1) {
				$pdo->prepare("DELETE FROM comms WHERE server_id=:id")->execute([':id' => $id]);
			} else {
				$STH = $pdo->prepare(
					"DELETE FROM comms WHERE server_id=:id && ((((expired < :time) || (created+(length*60) < :time)) && length != 0) || modified_by !='')"
				);
				$STH->execute([':id' => $id, ':time' => time()]);
			}
		} else {
			exit (json_encode(['status' => 2]));
		}
	} else {
		$db_host   = $row->db_host;
		$db_user   = $row->db_user;
		$db_pass   = $row->db_pass;
		$db_db     = $row->db_db;
		$db_prefix = $row->db_prefix;
		$address   = $row->ip . ':' . $row->port;
		$ip        = $row->ip;
		$port      = $row->port;

		if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
			exit (json_encode(['status' => 2]));
		}

		set_names($pdo2, $row->db_code);

		$table = set_prefix($db_prefix, 'comms');
		if(check_table($table, $pdo2)) {
			$table = set_prefix($db_prefix, 'servers');
			$STH   = $pdo2->query("SELECT sid FROM $table WHERE ip='$ip' and port='$port' LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$row   = $STH->fetch();
			$sid   = $row->sid;
			$table = set_prefix($db_prefix, 'comms');

			if($clearType == 1) {
				$pdo2->prepare("DELETE FROM $table WHERE sid=:sid")->execute([':sid' => $sid]);
			} else {
				$STH = $pdo2->prepare(
					"DELETE FROM $table WHERE `sid`=:sid AND ((`ends` < :time AND `length` != '0') OR (`RemoveType` = 'U') OR (`RemoveType` = 'E'))"
				);
				$STH->execute([':sid' => $sid, ':time' => time()]);
			}
		}
	}

	exit(json_encode(['status' => 1]));
}
if(isset($_POST['load_bad_nicks'])) {
	$STH = $pdo->prepare("SELECT data FROM config__strings WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => '1']);
	$row = $STH->fetch();
	if(!empty($row->data)) {
		$data = explode(";sp;", $row->data);
		for($i = 0; $i < count($data); $i++) {
			if(!empty($data[$i])) {
				echo '<div class="input-group" id="input_nick_' . $i . '"><span class="input-group-btn"><button class="btn btn-default" type="button" onclick="dell_nick_input(' . $i . ');">Удалить</button></span><input value="' . $data[$i] . '" type="text" name="nick' . $i . '" maxlength="32" placeholder="Введите ник" class="form-control"></div>';
			}
		}
	} else {
		echo '<div class="input-group" id="input_nick_0"><span class="input-group-btn"><button class="btn btn-default" type="button" onclick="dell_nick_input(0);">Удалить</button></span><input type="text" name="nick0" maxlength="32" placeholder="Введите ник" class="form-control"></div>';
	}
}
if(isset($_POST['save_bad_nicks'])) {
	$data = '';
	foreach($_POST as $key => $value) {
		if(substr($key, 0, 4) == "nick") {
			$data .= check($value, null) . ';sp;';
		}
	}

	$STH = $pdo->prepare("UPDATE config__strings SET data=:data WHERE id=:id LIMIT 1");
	if($STH->execute([':data' => $data, ':id' => '1']) == '1') {
		exit(json_encode(['status' => '1']));
	}
}

if(isset($_POST['load_bonuses'])) {
	$STH = $pdo->prepare("SELECT data FROM config__strings WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => '3']);
	$row = $STH->fetch();
	if(!empty($row->data)) {
		$data = unserialize($row->data);

		for($i = 0; $i < count($data); $i++) {
			if(!empty($data[$i]['value'])) {
				$active   = '';
				$active_2 = '';
				if($data[$i]['type'] == 1) {
					$active = 'selected';
				} else {
					$active_2 = 'selected';
				}
				echo '<div class="input-group" id="input_bonus_' . $i . '"><span class="input-group-btn"><button class="btn btn-default" type="button" onclick="dell_bonus_input(' . $i . ');">Удалить</button></span><input value="' . $data[$i]['start'] . '" type="text" name="bonus_start_' . $i . '" maxlength="5" placeholder="Начало диапазона" class="form-control w-25"><input value="' . $data[$i]['end'] . '" type="text" name="bonus_end_' . $i . '" maxlength="5" placeholder="Конец диапазона" class="form-control w-25"><select name="type_' . $i . '" class="form-control w-25"><option ' . $active . ' value="1">Бонус - N ' . $messages['RUB'] . '</option><option ' . $active_2 . ' value="2">Бонус - N% от пополненной суммы</option></select><input value="' . $data[$i]['value'] . '" type="text" name="bonus_' . $i . '" maxlength="5" placeholder="Введите значение N" class="form-control w-25"></div>';
			}
		}
	} else {
		echo '<div class="input-group" id="input_bonus_0"><span class="input-group-btn"><button class="btn btn-default" type="button" onclick="dell_bonus_input(0);">Удалить</button></span><input type="text" name="bonus_start_0" maxlength="32" placeholder="Начало диапазона" class="form-control w-25"><input type="text" name="bonus_end_0" maxlength="32" placeholder="Конец диапазона" class="form-control w-25"><select name="type_0" class="form-control w-25"><option value="1">Бонус - N ' . $messages['RUB'] . '</option><option value="2">Бонус - N% от пополненной суммы</option></select><input type="text" name="bonus_0" maxlength="32" placeholder="Введите значение N" class="form-control w-25"></div>';
	}
}
if(isset($_POST['save_bonuses'])) {
	$data = [];
	$i    = 0;

	foreach($_POST as $key => $value) {
		if(substr($key, 0, 12) == "bonus_start_") {
			$j = substr($key, 12);

			if(isset($_POST['bonus_start_' . $j]) && isset($_POST['bonus_end_' . $j]) && isset($_POST['type_' . $j]) && isset($_POST['bonus_' . $j])) {
				$data[$i]['start'] = check($_POST['bonus_start_' . $j], "float");
				$data[$i]['end']   = check($_POST['bonus_end_' . $j], "float");
				$data[$i]['type']  = check($_POST['type_' . $j], "int");
				$data[$i]['value'] = check($_POST['bonus_' . $j], "float");

				if(empty($data[$i]['start']) || empty($data[$i]['end']) || empty($data[$i]['type']) || empty($data[$i]['value']) || $data[$i]['end'] < $data[$i]['start']) {
					exit(json_encode(['status' => '2']));
				}

				$i++;
			} else {
				exit(json_encode(['status' => '2']));
			}
		}
	}

	$STH = $pdo->prepare("UPDATE config__strings SET data=:data WHERE id=:id LIMIT 1");
	$STH->execute([':data' => serialize($data), ':id' => '3']);

	exit(json_encode(['status' => '1']));
}

if(isset($_POST['load_bank_info'])) {
	$type = checkJs($_POST['type'], "int");
	if(empty($type)) {
		exit ();
	}

	?>
	<div id="chart<?php echo $type; ?>"></div>

	<script>
      google.charts.load('current', { packages: ['corechart', 'line'] });
      google.charts.setOnLoadCallback(drawCurveTypes);

      function drawCurveTypes () {
        var data = new google.visualization.DataTable();
		  <?php
		  if($type == 1) {
		  ?>
        data.addColumn('date', 'X');
		  <?php
		  } else {
		  ?>
        data.addColumn('number', 'X');
		  <?php
		  }
		  ?>
        data.addColumn('number', <?php $messages['RUB']; ?>);
        data.addRows([
			<?php
			$temp_date = '';
			$temp_sum = 0;

			if($type == 1) {
				$STH = $pdo->prepare("SELECT shilings, date FROM money__actions WHERE type=:type ORDER BY date");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute([':type' => '1']);
				while($row = $STH->fetch()) {

					$currentDate = $row->date;

					if(empty($temp_date)) {
						$temp_date = $currentDate;
					}

					if(date("Y-m", strtotime($temp_date)) != date("Y-m", strtotime($row->date))) {
						$date = expand_date($temp_date, 5);
						echo "[new Date(" . $date['year'] . ", " . ($date['month3'] - 1) . ", 0), " . $temp_sum . "],";
						$temp_sum  += $row->shilings;
						$temp_date = $currentDate;
					} else {
						$temp_sum += $row->shilings;
					}
				}
				if(isset($currentDate)) {
					$date = expand_date($currentDate, 5);
					echo "[new Date(" . $date['year'] . ", " . ($date['month3'] - 1) . ", " . ($date['day']) . "), " . $temp_sum . "]";
				}
			} elseif($type == 2 or $type == 3) {
				if($type == 2) {
					$STH = $pdo->prepare(
						"SELECT shilings, date FROM money__actions WHERE type=:type and MONTH(date) = MONTH(NOW()) AND YEAR(date) = YEAR(NOW()) ORDER BY date"
					);
				} else {
					$STH = $pdo->prepare(
						"SELECT shilings, date FROM money__actions WHERE type=:type and MONTH(date) = MONTH(DATE_ADD(NOW(), INTERVAL -1 MONTH)) and YEAR(date) = YEAR(DATE_ADD(NOW(), INTERVAL -1 MONTH)) ORDER BY date"
					);
				}
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute([':type' => '1']);
				while($row = $STH->fetch()) {

					$currentDate = $row->date;

					if(empty($temp_date)) {
						$temp_date = $currentDate;
					}

					if(date("Y-m-d", strtotime($temp_date)) != date("Y-m-d", strtotime($row->date))) {
						$date = expand_date($temp_date, 5);
						echo "[" . $date['day'] . ", " . $temp_sum . "],";
						$temp_sum  += $row->shilings;
						$temp_date = $currentDate;
					} else {
						$temp_sum += $row->shilings;
					}
				}
				if(isset($currentDate)) {
					$date = expand_date($currentDate, 5);
					echo "[" . $date['day'] . ", " . $temp_sum . "]";
				}
			}
			?>
        ]);

        var options = {
          legend: 'none',
          width: 1160,
          height: 400,
          timeline: {
            groupByRowLabel: true
          },
          series: {
            0: { pointShape: 'circle' },
            1: { curveType: 'function' }
          },
          pointSize: 3
        };

        var chart = new google.visualization.LineChart(document.getElementById('chart<?php echo $type; ?>'));
        chart.draw(data, options);
      }
	</script>
	<?php
}
if(isset($_POST['edit_protocol'])) {
	$protocol = check($_POST['protocol'], "int");
	if(empty($protocol)) {
		exit(json_encode(['status' => '2']));
	}

	$STH = $pdo->prepare("UPDATE config SET protocol=:protocol LIMIT 1");
	$STH->execute([':protocol' => $protocol]);
	write_log("Протокол сайта изменен на " . $protocol);

	exit(json_encode(['status' => '1']));
}
if(isset($_POST['dell_cache'])) {
	$STH = $pdo->prepare("UPDATE config SET cache=:cache LIMIT 1");
	$STH->execute([':cache' => $conf->cache + 1]);

	$tpl = new Template;
	$tpl->dell_cache();
	unset($tpl);

	exit();
}

if(isset($_POST['edit_protect'])) {
	$type = check($_POST['type'], 'int');

	if($type != 1 and $type != 2) {
		exit(json_encode(['status' => '2']));
	}

	if($type == 1) {
		$STH = $pdo->query("SELECT captcha FROM config LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if($row->captcha == '2') {
			exit (json_encode(['status' => '2', 'info' => 'Сначала включите капчу!']));
		}

		$STH = $pdo->prepare("UPDATE config SET protect=:protect LIMIT 1");
		$STH->execute([':protect' => $type]);
	} else {
		$STH = $pdo->prepare("DELETE FROM last_actions WHERE action_type=:action_type");
		$STH->execute([':action_type' => 5]);

		$STH = $pdo->prepare("UPDATE config SET protect=:protect LIMIT 1");
		$STH->execute([':protect' => $type]);
	}
	exit(json_encode(['status' => '1']));
}
if(isset($_POST['save_forum_settings'])) {
	$file_manager       = check($_POST['file_manager'], 'int');
	$file_manager_theme = check($_POST['file_manager_theme'], 'int');
	$file_max_size      = check($_POST['file_max_size'], 'int');
	$ext_img            = check($_POST['ext_img'], null);
	$ext_music          = check($_POST['ext_music'], null);
	$ext_misc           = check($_POST['ext_misc'], null);
	$ext_file           = check($_POST['ext_file'], null);
	$ext_video          = check($_POST['ext_video'], null);

	if($file_manager != 1 and $file_manager != 2) {
		exit(json_encode(['status' => '2', 'input' => 'file_manager', 'data' => 'Неверное значение']));
	}
	if($file_manager_theme != 1 and $file_manager_theme != 2) {
		exit(json_encode(['status' => '2', 'input' => 'file_manager', 'data' => 'Неверное значение']));
	}

	if(empty($file_max_size)) {
		exit(json_encode(['status' => '2', 'input' => 'file_max_size', 'data' => 'Не менее 1']));
	}
	if($file_max_size > 99999) {
		exit(json_encode(['status' => '2', 'input' => 'file_max_size', 'data' => 'Не более 99999']));
	}

	if(!empty($ext_img)) {
		if(!ValidateLetterAndNum($ext_img)) {
			exit (json_encode(['status' => '2', 'input' => 'ext_img', 'data' => 'Неверное значение']));
		}
	} else {
		$ext_img = '';
	}
	if(!empty($ext_music)) {
		if(!ValidateLetterAndNum($ext_music)) {
			exit (json_encode(['status' => '2', 'input' => 'ext_music', 'data' => 'Неверное значение']));
		}
	} else {
		$ext_music = '';
	}
	if(!empty($ext_misc)) {
		if(!ValidateLetterAndNum($ext_misc)) {
			exit (json_encode(['status' => '2', 'input' => 'ext_misc', 'data' => 'Неверное значение']));
		}
	} else {
		$ext_misc = '';
	}
	if(!empty($ext_file)) {
		if(!ValidateLetterAndNum($ext_file)) {
			exit (json_encode(['status' => '2', 'input' => 'ext_file', 'data' => 'Неверное значение']));
		}
	} else {
		$ext_file = '';
	}
	if(!empty($ext_video)) {
		if(!ValidateLetterAndNum($ext_video)) {
			exit (json_encode(['status' => '2', 'input' => 'ext_video', 'data' => 'Неверное значение']));
		}
	} else {
		$ext_video = '';
	}

	$data = serialize(
		[
			'file_manager'       => $file_manager,
			'file_manager_theme' => $file_manager_theme,
			'file_max_size'      => $file_max_size,
			'ext_img'            => $ext_img,
			'ext_music'          => $ext_music,
			'ext_misc'           => $ext_misc,
			'ext_file'           => $ext_file,
			'ext_video'          => $ext_video
		]
	);

	$STH = $pdo->prepare("UPDATE config__strings SET data=:data WHERE id=:id LIMIT 1");
	$STH->execute([':data' => $data, ':id' => '2']);

	exit(json_encode(['status' => '1']));
}

if(isset($_POST['loadForbiddenWords'])) {
	$STH = $pdo->prepare("SELECT data FROM config__strings WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => 5]);
	$row = $STH->fetch();
	if(!empty($row->data)) {
		$data = explode(";sp;", $row->data);
		for($i = 0; $i < count($data); $i++) {
			if(!empty($data[$i])) {
				echo '<div class="input-group" id="input-forbidden-word-' . $i . '"><span class="input-group-btn"><button class="btn btn-default" type="button" onclick="dellForbiddenWordInput(' . $i . ');">Удалить</button></span><input value="' . $data[$i] . '" type="text" name="forbidden-word' . $i . '" maxlength="32" placeholder="Введите слово" class="form-control"></div>';
			}
		}
	} else {
		echo '<div class="input-group" id="input-forbidden-word-0"><span class="input-group-btn"><button class="btn btn-default" type="button" onclick="dellForbiddenWordInput(0);">Удалить</button></span><input type="text" name="forbidden-word0" maxlength="32" placeholder="Введите слово" class="form-control"></div>';
	}
}

if(isset($_POST['saveForbiddenWords'])) {
	$data = '';

	foreach($_POST as $key => $value) {
		if(substr($key, 0, 14) == "forbidden-word") {
			$data .= check($value, null) . ';sp;';
		}
	}

	$STH = $pdo->prepare("UPDATE config__strings SET data=:data WHERE id=:id LIMIT 1");
	$STH->execute([':data' => $data, ':id' => 5]);

	exit(json_encode(['status' => '1']));
}

if(isset($_POST['editHidingPlayersId'])) {
	$hidePlayersIdType = check($_POST['hidePlayersIdType'], "int");

	if(empty($hidePlayersIdType)) {
		$hidePlayersIdType = 0;
	}

	$STH = $pdo->prepare("UPDATE config SET hide_players_id=:hide_players_id LIMIT 1");
	$STH->execute([':hide_players_id' => $hidePlayersIdType]);

	exit(json_encode(['status' => 1]));
}

if(isset($_POST['get_md5'])) {
	exit(json_encode(['answer' => md5($_POST['val'])]));
}

if(isset($_POST['saveServerCommand'])) {
	$categoryId = check($_POST['categoryId'], "int");
	$serverId   = check($_POST['serverId'], "int");
	$title      = check($_POST['title'], null);
	$command    = check($_POST['command'], null);
	$id         = check($_POST['id'], "int");

	$ServerCommands = new ServerCommands();

	if(empty($command)) {
		exit(json_encode(['status' => 2, 'input'  => 'command-value', 'data' => 'Заполните']));
	}

	if(empty($title)) {
		exit(json_encode(['status' => 2, 'input'  => 'command-title', 'data' => 'Заполните']));
	}

	if(mb_strlen($command, 'UTF-8') > 512) {
		exit(json_encode(['status' => 2, 'input'  => 'command-value', 'data' => 'Не более 512 символов']));
	}

	if(mb_strlen($title, 'UTF-8') > 512) {
		exit(json_encode(['status' => 2, 'input'  => 'command-title', 'data' => 'Не более 512 символов']));
	}

	if(!ServerCommands::isCategoryExists($categoryId)) {
		exit(json_encode(['status' => 2, 'input'  => 'command-category', 'data' => 'Категория не существует']));
	}

	if(empty($id) && $ServerCommands->isCategoryIsSystem($categoryId)) {
		exit(json_encode(['status' => 2, 'input'  => 'command-category', 'data' => 'Нельзя добавлять системные комманды']));
	}

	$server = (new ServersManager())->getServer($serverId);

	if(empty($server)) {
		exit(json_encode(['status' => 2, 'input'  => 'alert', 'data'   => 'Сервер не существует']));
	}

	$slug = translit($title);
	$commandForCheckSlugBusyness = $ServerCommands->getCommandBySlug(
		$slug,
		$serverId
	);

	if(
		(empty($id) && !empty($commandForCheckSlugBusyness))
		|| (!empty($id) && (!empty($commandForCheckSlugBusyness) && $commandForCheckSlugBusyness->id != $id))
	) {
		exit(json_encode(['status' => 2, 'input' => 'command-title', 'data' => 'Команда уже существует']));
	}

	if(empty($id)) {
		$ServerCommands->addCommand(
			$command,
			$serverId,
			$title,
			$slug,
			$categoryId
		);

		if($ServerCommands->isCategoryIsActionOnPlayer($categoryId)) {
			$commandId = get_ai($pdo, 'servers__commands') - 1;
			$ServerCommands->addCommandParam($commandId, 'nick', 'Ник');
		}
	} else {
		$issetCommand = $ServerCommands->getCommandById($id);

		if($ServerCommands->isCategoryIsSystem($issetCommand->category)) {
			$title = $issetCommand->title;
			$slug = $issetCommand->slug;
			$categoryId = $issetCommand->category;
		}

		if($ServerCommands->isCategoryIsActionOnPlayer($categoryId)) {
			$params = $ServerCommands->getCommandParams($id);

			$needToAddNickParam = true;

			foreach($params as $param) {
				if($param->name == 'nick') {
					$needToAddNickParam = false;
				}
			}

			if($needToAddNickParam) {
				$ServerCommands->addCommandParam($id, 'nick', 'Ник');
			}
		}

		$ServerCommands->updateCommand(
			$id,
			$command,
			$title,
			$slug,
			$categoryId
		);
	}

	exit(json_encode(['status' => 1]));
}

if(isset($_POST['dellServerCommand'])) {
	$id = check($_POST['id'], "int");

	$ServerCommands = new ServerCommands();
	$command = $ServerCommands->getCommandById($id);
	if(!$ServerCommands->isCategoryIsSystem($command->category)) {
		$ServerCommands->removeCommand($id);
	}

	exit(json_encode(['status' => 1]));
}

if(isset($_POST['getServerCommands'])) {
	$serverId   = check($_POST['serverId'], "int");

	$ServerCommands = new ServerCommands();
	$commands = $ServerCommands->getCommands($serverId);

	foreach($commands as $command) {
		?>
		<div class="row" id="server-command<?php echo $command->server_id ?>-<?php echo $command->id ?>">
			<div class="col-md-4">
				<label>Команда</label>
				<input
					<?php if($ServerCommands->isCategoryIsSystem($command->category)) { echo 'disabled'; } ?>
						value="<?php echo $command->title; ?>"
						id="command-title<?php echo $command->server_id ?>-<?php echo $command->id ?>"
						class="form-control w-100"
						placeholder="Название команды, пример: Кик"
				>
				<input
						value="<?php echo $command->command; ?>"
						id="command-value<?php echo $command->server_id ?>-<?php echo $command->id ?>"
						class="form-control w-100 mt-10"
						placeholder="Введите команду, пример: amx_kick"
				>

				<select
						id="command-category<?php echo $command->server_id ?>-<?php echo $command->id ?>"
						class="form-control w-100 mt-10"
					<?php if($ServerCommands->isCategoryIsSystem($command->category)) { echo 'disabled'; } ?>
				>
					<?php if($ServerCommands->isCategoryIsSystem($command->category)) { ?>
						<option value="1" <?php if($ServerCommands->isCategoryIsSystem($command->category)) { echo 'selected'; } ?>>
							Системные
						</option>
					<?php } ?>
					<option value="2" <?php if($ServerCommands->isCategoryIsActionOnPlayer($command->category)) { echo 'selected'; } ?>>
						Действия над игроками
					</option>
					<option value="3" <?php if($ServerCommands->isCategoryIsServerManagement($command->category)) { echo 'selected'; } ?>>
						Управление сервером
					</option>
				</select>
				<button
						class="btn2 mt-10"
						type="button"
						onclick="saveServerCommand(<?php echo $command->server_id ?>, <?php echo $command->id ?>); saveServerCommandParam(<?php echo $command->id ?>);"
				>
					Сохранить
				</button>
				<?php if(!$ServerCommands->isCategoryIsSystem($command->category)) { ?>
					<button
							class="btn2 btn-cancel mt-10"
							type="button"
							onclick="dellServerCommand(<?php echo $command->id ?>, <?php echo $command->server_id ?>);"
					>
						Удалить
					</button>
				<?php } ?>
			</div>
			<div class="col-md-8">
				<label>Переменные</label>
				<?php
				$params = $ServerCommands->getCommandParams($command->id);
				$i      = 0;
				?>
				<input type="hidden" id="command-params-count<?php echo $command->id ?>" value="<?php echo count($params) + 1; ?>">
				<form id="command-params<?php echo $command->id ?>" class="mb-10">
					<?php
					foreach($params as $param) {
						?>
						<div class="row mb-10" id="command-param<?php echo $command->id ?>-<?php echo $i; ?>">
							<div class="col-md-5">
								<input
										value="<?php echo $param->name; ?>"
										name="name<?php echo $i; ?>"
										class="form-control w-100"
										placeholder="Введите переменную, пример: nick"
								>
							</div>
							<div class="col-md-5">
								<input
										value="<?php echo $param->title; ?>"
										name="title<?php echo $i; ?>"
										class="form-control w-100"
										placeholder="Введите название переменной, пример: Ник"
								>
							</div>
							<div class="col-md-2">
								<button
										class="btn btn-default btn-block"
										onclick="removeServerCommandParam(<?php echo $command->id ?>, <?php echo $i; ?>);"
								>
									Удалить
								</button>
							</div>
						</div>
						<?php
						$i++;
					}
					?>
				</form>

				<button
						type="button"
						class="btn btn-default"
						onclick="addServerCommandParam(<?php echo $command->id ?>);">
					Добавить
				</button>
			</div>
			<div class="col-md-12">
				<hr>
			</div>
		</div>
		<?php
	}
}

if(isset($_POST['saveServerCommandParam'])) {
	$commandId = check($_POST['commandId'], "int");

	$ServerCommands = new ServerCommands();
	$issetCommand = $ServerCommands->getCommandById($commandId);

	if(empty($issetCommand)) {
		exit(json_encode(['status' => 2, 'data' => 'Команда не существует']));
	}

	if(
			$ServerCommands->isCategoryIsActionOnPlayer($issetCommand->category)
			&& !in_array('nick', $_POST)
	) {
		exit(json_encode(['status' => 2, 'data' => 'Команда действия над пользователем должна содержать переменную nick']));
	}

	$ServerCommands->removeCommandParams($commandId);

	foreach($_POST as $key => $value) {
		if (stripos($key, 'name') !== false) {
			$id = str_replace('name', '', $key);

			$title = check(empty($_POST['title' . $id]) ? null : $_POST['title' . $id], null);
			$name = check(empty($_POST['name' . $id]) ? null : $_POST['name' . $id], null);

			if(empty($title)) {
				exit(json_encode(['status' => 2, 'data' => 'Названия переменных не должны быть пустыми']));
			}

			if(empty($name)) {
				exit(json_encode(['status' => 2, 'data' => 'Переменные не должны быть пустыми']));
			}

			if(mb_strlen($title, 'UTF-8') > 512) {
				exit(json_encode(['status' => 2, 'data' => 'Названия переменных должна состоять более чем из 512 символов']));
			}

			if(mb_strlen($name, 'UTF-8') > 512) {
				exit(json_encode(['status' => 2, 'data' => 'Переменные должны состоять более чем из 512 символов']));
			}

			if (preg_match('/[^A-Za-z0-9]+/', $name))  {
				exit(json_encode(['status' => 2, 'data' => 'Переменная должна состоять только из букв английского алфавита и цифр']));
			}

			$ServerCommands->addCommandParam($commandId, $name, $title);
		}
	}

	exit(json_encode(['status' => 1]));
}

if(isset($_POST['editCaptcha'])) {
	$captchaClientKey = clean($_POST['captcha_client_key']);
	$captchaSecret = clean($_POST['captcha_secret']);

	if(empty($captchaClientKey)) {
		$AjaxResponse
			->error('captcha_client_key', 'Укажите ключ')
			->send();
	}

	if(empty($captchaSecret)) {
		$AjaxResponse
			->error('captcha_secret', 'Укажите ключ')
			->send();
	}

	$STH = pdo()->prepare(
		"UPDATE config SET captcha_client_key=:captcha_client_key, captcha_secret=:captcha_secret LIMIT 1"
	);
	$STH->execute(
		[
			':captcha_client_key' => $captchaClientKey,
			':captcha_secret'     => $captchaSecret
		]
	);

	$AjaxResponse->send();
}

if(isset($_POST['onCaptcha'])) {
	if(
			empty(configs()->captcha_client_key)
			|| empty(configs()->captcha_secret)
	) {
		$AjaxResponse
			->status(false)
			->alert('Укажите ключи')
			->send();
	}

	$STH = pdo()->prepare("UPDATE config SET captcha=:captcha LIMIT 1");
	$STH->execute([':captcha' => 1]);

	$AjaxResponse->send();
}

if(isset($_POST['offCaptcha'])) {
	if(configs()->protect == 1) {
		$AjaxResponse
			->status(false)
			->alert('Сначала выключите защиту от флуда')
			->send();
	}

	pdo()
		->prepare("UPDATE config SET captcha=:captcha LIMIT 1")
		->execute([':captcha' => 2]);

	$AjaxResponse->send();
}