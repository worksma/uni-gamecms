<?PHP
	class Addons extends System {
		/*
			Проверка установленного модуля
		*/
		public function is_install_module($key = null) {
			$key = checkJs($key, null);

			if(empty($key)):
				exit(json_encode([
					'status' => '2',
					'message' => 'Введите ключ'
				]));
			endif;

			$sth = pdo()->query("SELECT * FROM `modules` WHERE `client_key`='$key' LIMIT 1");

			if($sth->rowCount()):
				exit(json_encode([
					'status' => '2',
					'message' => 'Ключ уже используется.'
				]));
			endif;

			return false;
		}

		public function is_install_module_name($name = null) {
			$name = checkJs($name, null);
			$sth = pdo()->query("SELECT * FROM `modules` WHERE `name`='$name' LIMIT 1");

			if($sth->rowCount()):
				return true;
			endif;

			return false;
		}

		public function get_module($id) {
			return pdo()->query("SELECT * FROM `modules` WHERE `id`='$id' LIMIT 1")->fetch(PDO::FETCH_OBJ);
		}

		public function enable_module($id, $enable = true) {
			$id = checkJs($id, null);

			if(empty($id)):
				exit(json_encode(['status' => '2']));
			endif;

			pdo()
			->prepare("UPDATE `modules` SET `active`=:active WHERE `id`=:id LIMIT 1")
			->execute([
				':id' => $id,
				':active' => $enable ? "1": "0"
			]);

			pdo()
			->prepare("UPDATE `pages` SET `active`=:active WHERE `module`=:id LIMIT 1")
			->execute([
				':id' => $id,
				':active' => $enable ? "1": "0"
			]);

			exit(json_encode(['status' => '1']));
		}

		public function delete_module($id) {
			$id = checkJs($id, null);

			if(empty($id)):
				exit(json_encode(['status' => '2']));
			endif;

			$module = $this->get_module($id);
			$module_dir = $_SERVER['DOCUMENT_ROOT'] . "/modules_extra/" . $module->name;

			if(file_exists("$module_dir/import/delete.sql")):
				try {
					pdo()->exec(trim(file_get_contents("$module_dir/import/delete.sql")));
					$this->removeDirs($module_dir);
				}
				catch (PDOException $e) {
					exit(json_encode(['status' => '2', 'message' => $e->getMessage()]));
				}
			else:
				$this->removeDirs($module_dir);
			endif;

			pdo()->exec("DELETE FROM `modules` WHERE `id`='$id'");
			pdo()->exec("DELETE FROM `pages` WHERE `module`='$id'");

			exit(json_encode(['status' => '1']));
		}

		public function install_template($key) {
			if(empty($key)):
				exit(json_encode(['status' => '2', 'message' => 'Введите ключ.']));
			endif;

			$result = curl($this->server(true), json_encode([
				'module' => 'uni-gamecms',
				'type' => 'get_template',
				'key' => $key,
				'domain' => $_SERVER['SERVER_NAME']
			]));

			if(json_decode($result)):
				exit(json_encode(['status' => '2', 'message' => json_decode($result)->message]));
			else:
				$temp_name = md5($_SERVER['SERVER_NAME'] . time());
				$dir_templates = $_SERVER['DOCUMENT_ROOT'] . '/templates/';

				if(!file_exists($dir_templates)):
					mkdir($dir_templates);
				endif;

				ignore_user_abort(1);
				set_time_limit(0);

				if(file_put_contents("$dir_templates$temp_name.zip", $result)):
					$zip = new ZipArchive;

					if($zip->open("$dir_templates$temp_name.zip") === true):
						$name = strstr($zip->getNameIndex(0), "/", true);

						if(file_exists("$dir_templates$name")):
							$zip->close();
							unlink("$dir_templates$temp_name.zip");
							exit(json_encode(['status' => '2', 'message' => 'Шаблон с таким именем уже установлен.']));
						endif;

						$zip->extractTo($dir_templates);

						if($zip->close()):
							unlink("$dir_templates$temp_name.zip");
							exit(json_encode(['status' => '1', 'message' => $name]));
						endif;
					endif;

					unlink("$dir_templates$temp_name.zip");
					exit(json_encode(['status' => '2', 'message' => 'Ошибка открытия архива.']));
				endif;

				unlink("$dir_templates$temp_name.zip");
				exit(json_encode(['status' => '2', 'message' => 'Ошибка при создание временного файла.']));
			endif;
		}

		public function install_module($key) {
			$this->is_install_module($key);

			$result = curl($this->server(true), json_encode([
				'module' => 'uni-gamecms',
				'type' => 'get_module',
				'key' => $key,
				'domain' => $_SERVER['SERVER_NAME']
			]));

			if(json_decode($result)):
				exit(json_encode(['status' => '2', 'message' => json_decode($result)->message]));
			else:
				$temp_name = md5($_SERVER['SERVER_NAME'] . time());
				$dir_extra = $_SERVER['DOCUMENT_ROOT'] . '/modules_extra/';

				if(!file_exists($dir_extra)):
					mkdir($dir_extra);
				endif;

				if(file_put_contents($dir_extra . "$temp_name.zip", $result)):
					$zip = new ZipArchive;

					if($zip->open($dir_extra . "$temp_name.zip") === true):
						$name = strstr($zip->getNameIndex(0), "/", true);

						if($this->is_install_module_name($name)):
							$zip->close();
							unlink($dir_extra . "$temp_name.zip");

							exit(json_encode(['status' => '2', 'message' => 'Модуль с данным именем уже установлен.']));
						endif;

						$zip->extractTo($dir_extra);
						$module_dir = $dir_extra . $name;

						if($zip->close()):
							unlink($dir_extra . "$temp_name.zip");

							if(!file_exists($module_dir . "/settings.txt")):
								$this->removeDirs($module_dir);
								exit(json_encode(['status' => '2', 'message' => 'Отсутствуют конфигурации настроек.']));
							endif;

							if(file_exists("$module_dir/import/install.sql")):
								try {
									pdo()->exec(trim(file_get_contents("$module_dir/import/install.sql")));
								}
								catch (PDOException $e) {
									$this->removeDirs($module_dir);
									exit(json_encode(['status' => '2', 'message' => $e->getMessage()]));
								}

								unlink("$module_dir/import/install.sql");
							endif;

							$settings = $this->parse(file_get_contents($module_dir . '/settings.txt'));
							
							if(pdo()
							->prepare("INSERT INTO `modules`(`name`, `tpls`, `info`, `files`, `client_key`) VALUES (:name, :tpls, :info, :files, :client_key)")
							->execute([
								':name' => $name,
								':tpls' => isset($settings->tpls) ? $settings->tpls : 'none',
								':info' => isset($settings->info) ? $settings->info : 'Нет описания',
								':files' => isset($settings->files) ? $settings->files : "",
								':client_key' => $key
							])):
								if(isset($settings->pages)):
									foreach($settings->pages as $page):
										$module_id = get_ai(pdo(), "modules");
										$module_id--;

										pdo()
										->prepare("INSERT INTO `pages`(`file`, `url`, `name`, `title`, `description`, `keywords`, `kind`, `image`, `robots`, `privacy`, `type`, `active`, `module`, `page`, `class`) VALUES (:file, :url, :name, :title, :description, :keywords, :kind, :image, :robots, :privacy, :type, :active, :module, :page, :class)")
										->execute([
											':file' => $page['file'],
											':url' => $page['url'],
											':name' => $page['name'],
											':title' => $page['title'],
											':description' => $page['description'],
											':keywords' => $page['keywords'],
											':kind' => $page['kind'],
											':image' => $page['image'],
											':robots' => $page['robots'],
											':privacy' => $page['privacy'],
											':type' => $page['type'],
											':active' => $page['active'],
											':module' => $module_id,
											':page' => 0,
											':class' => 0
										]);
									endforeach;
								endif;

								unlink("$module_dir/settings.txt");
								exit(json_encode(['status' => '1', 'message' => 'Модуль успешно установлен!']));
							endif;

							$this->removeDirs($module_dir);
							exit(json_encode(['status' => '2', 'message' => 'Ошибка инициализации']));
						endif;
					endif;
				endif;
			endif;
		}

		public function parse($data) {
			eval('$t = ' . $data . ';');
			return (object)$t;
		}
	}