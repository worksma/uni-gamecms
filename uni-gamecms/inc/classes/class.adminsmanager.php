<?php
class AdminsManager {
	public function ftp_connection($ftp_host, $ftp_port, $ftp_login, $ftp_pass, $title) {
		$ftp_connection = ftp_connect($ftp_host, $ftp_port);
		if(!$ftp_connection) {
			log_error($title.': Не удалось подключиться к FTP серверу '.$ftp_host.':'.$ftp_port);
			return false;
		}
		$ftp_login = ftp_login($ftp_connection, $ftp_login, $ftp_pass);
		if (!$ftp_login) {
			log_error($title.': Не удалось авторизоваться на FTP сервере '.$ftp_host.':'.$ftp_port);
			return false;
		}
		ftp_pasv($ftp_connection, true);
		return $ftp_connection;
	}

	public function close_ftp($ftp_connection) {
		ftp_close($ftp_connection);
		return true;
	}

	public function explode_users_file($string) {
		preg_match("~(.+/)~ui", $string, $parse);
		if(isset($parse[1])) {
			$ftp['string'] = $parse[1];
		} else {
			$ftp['string'] = '';
		}

		$parse = explode("/", $string);
		if($parse[count($parse)-1]) {
			$ftp['file'] = $parse[count($parse)-1];
		} else {
			$ftp['file'] = '';
		}

		return $ftp;
	}

	public function dell_old_admins($pdo, $site_name){
		$now = date("Y-m-d H:i:s");
		$STH = $pdo->prepare("SELECT 
							    `services`.`users_group`,
							    `admins__services`.`id`,
    							`admins__services`.`service`,
							    `users`.`email`,
							    `users`.`email_notice`,
							    `servers`.`name` AS `server_name`,
							    `servers`.`type` AS `server_type`,
							    `admins__services`.`admin_id`,
							    `admins__services`.`ending_date`,
							    `admins`.`server`,
							    `admins`.`name`,
							    `admins`.`user_id`,
							    `services`.`name` AS `service_name`
			 				FROM 
			 				    `admins__services` 
							LEFT JOIN 
			 					`admins` 
			 				ON 
			 				    `admins`.`id` = `admins__services`.`admin_id` 
							LEFT JOIN 
			 					`services` 
			 				ON 
			 				    `services`.`id` = `admins__services`.`service` 
							LEFT JOIN 
			 					`servers` 
			 				ON 
			 				    `servers`.`id` = `admins`.`server` 
							LEFT JOIN 
			 					`users` 
			 				ON 
			 				    `users`.`id` = `admins`.`user_id` 
							WHERE 
							    `admins__services`.`ending_date`!=:ending_date 
								AND `admins__services`.`ending_date`<:now 
								AND `admins`.`active` = '1' 
								AND `admins`.`pause` = '0' 
								AND `servers`.`type` != 0"
		);
		$STH->execute(array( ':ending_date' => '0000-00-00 00:00:00', ':now' => $now ));
		$admin = $STH->fetchAll();
		$acount = count($admin);

		for ($i=0; $i < $acount; $i++) {
			$STH2 = $pdo->prepare("SELECT `id` FROM `admins__services` WHERE `admin_id`=:id ");
			$STH2->execute(array( ':id' => $admin[$i]['admin_id'] ));
			$row = $STH2->fetchAll();
			$count = count($row);

			if($count == 1) {
				service_log("Автоматическое полное удаление прав", $admin[$i]['admin_id'], $admin[$i]['server'], $pdo);

				if(!empty($admin[$i]['user_id'])) {
					$this->set_admin_group($pdo, $admin[$i]['user_id'], $admin[$i]['admin_id'], 0);
				}

				if(!$this->dell_admin_full($pdo, $admin[$i]['admin_id'], "DELL_OLD_ADMINS", 1)) {
					return false;
				}
			} else {
				service_log(
					"Автоматическое удаление услуги",
					$admin[$i]['admin_id'],
					$admin[$i]['server'],
					$pdo,
					$admin[$i]['service']
				);

				if(!empty($admin[$i]['user_id'])) {
					$this->set_admin_group($pdo, $admin[$i]['user_id'], 0, $admin[$i]['id']);
				}

				$STH2 = $pdo->prepare("DELETE FROM `admins__services` WHERE `id`=:id LIMIT 1");
				$STH2->execute(array( ':id' => $admin[$i]['id'] ));

				if ($admin[$i]['server_type'] == 1 or $admin[$i]['server_type'] == 3){
					if(!$this->export_to_users_ini($pdo, $admin[$i]['server'], 'DELL_ADMIN_SERVICE', 1)){
						return false;
					}
				} else {
					if(!$this->export_admin($pdo, $admin[$i]['admin_id'], $admin[$i]['server'], 'DELL_ADMIN_SERVICE')){
						return false;
					}
				}
			}

			if(!empty($admin[$i]['user_id'])) {
				if(empty($admin[$i]['service_name'])) {
					$admin[$i]['service_name'] = 'Неизвестно';
				}

				incNotifications();

				$noty = noty_of_dell_service($admin[$i]['name'], $admin[$i]['service_name'], $admin[$i]['server_name']);
				send_noty($pdo, $noty, $admin[$i]['user_id'], 3);

				if(!empty($admin[$i]['email']) && $admin[$i]['email_notice'] == 1) {
					$letter = letter_of_dell_service($site_name, $admin[$i]['name'], $admin[$i]['service_name'], $admin[$i]['server_name']);
					sendmail($admin[$i]['email'], $letter['subject'], $letter['message'], $pdo);
				}
			}
		}
		$this->set_admin_dell_time($pdo);
		return true;
	}

	public function set_admin_group($pdo, $user_id, $admin_id, $admin_service_id){
		if($admin_id != 0) {
			$STH = $pdo->prepare("SELECT `services`.`users_group` FROM `admins__services` 
				LEFT JOIN `admins` ON `admins`.`id` = `admins__services`.`admin_id` 
				LEFT JOIN `services` ON `services`.`id` = `admins__services`.`service`
				WHERE `admins`.`user_id`=:user_id AND `admins__services`.`previous_group`!='0' AND `admins__services`.`admin_id`!=:admin_id ORDER BY `admins__services`.`bought_date` DESC LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':admin_id' => $admin_id, ':user_id' => $user_id ));
			$row = $STH->fetch();

			if(empty($row->users_group)) {
				$STH = $pdo->prepare("SELECT `previous_group` FROM `admins__services` WHERE `previous_group`!='0' AND `admin_id`=:admin_id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':admin_id' => $admin_id ));
				$row = $STH->fetch();

				if(!empty($row->previous_group)) {
					$previous_group = $row->previous_group;
				} else {
					$previous_group = 0;
				}
			} else {
				$previous_group = $row->users_group;
			}
		} elseif($admin_service_id != 0) {
			$STH = $pdo->prepare("SELECT `services`.`users_group` FROM `admins__services` 
				LEFT JOIN `admins` ON `admins`.`id` = `admins__services`.`admin_id` 
				LEFT JOIN `services` ON `services`.`id` = `admins__services`.`service`
				WHERE `admins`.`user_id`=:user_id AND `admins__services`.`previous_group`!='0' AND `admins__services`.`id`!=:admin_service_id ORDER BY `admins__services`.`bought_date` DESC LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':admin_service_id' => $admin_service_id, ':user_id' => $user_id ));
			$row = $STH->fetch();

			if(empty($row->users_group)) {
				$STH = $pdo->prepare("SELECT `previous_group` FROM `admins__services` WHERE `previous_group`!='0' AND `id`=:admin_service_id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':admin_service_id' => $admin_service_id ));
				$row = $STH->fetch();

				if(!empty($row->previous_group)) {
					$previous_group = $row->previous_group;
				} else {
					$previous_group = 0;
				}
			} else {
				$previous_group = $row->users_group;
			}
		}

		if($previous_group != 0) {
			$STH = $pdo->prepare("UPDATE `users` SET `rights`=:rights WHERE `id`=:id LIMIT 1");
			$STH->execute(array( ':rights' => $previous_group, ':id' => $user_id ));
		}

		return true;
	}

	public function set_admin_dell_time($pdo) {
		$STH = $pdo->query("SELECT 
							    admins__services.ending_date 
							FROM 
							    admins__services 
							LEFT JOIN 
								admins 
							ON 
								admins.id = admins__services.admin_id 
							LEFT JOIN 
							        `servers` 
							ON 
							    `servers`.`id` = `admins`.`server` 
							WHERE 
							    admins__services.ending_date != '0000-00-00 00:00:00' 
								AND admins.active = '1' 
								AND admins.pause = '0' 
								AND servers.type != 0
							ORDER BY 
							    admins__services.ending_date
							LIMIT 1"
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(empty($row->ending_date)) {
			$ending_date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s"))+3600*3);
		} else {
			$ending_date = $row->ending_date;
		}

		$STH = $pdo->prepare("UPDATE `config` SET `dell_admin_time`=:dell_admin_time LIMIT 1");
		$STH->execute(array( ':dell_admin_time' => $ending_date ));

		return true;
	}

	public function send_noty_for_admins($pdo, $site_name){
		$now = date("Y-m-d H:i:s");
		$date = date("Y-m-d H:i:s", strtotime($now)+3600*24*5);
		$STH = $pdo->prepare("SELECT 
    							`admins__services`.`id`,
							    `users`.`email`,
							    `users`.`email_notice`,
							    `servers`.`name` AS `server_name`,
							    `servers`.`type` AS `server_type`,
							    `admins__services`.`admin_id`,
							    `admins__services`.`ending_date`,
							    `admins`.`server`,
							    `admins`.`name`,
							    `admins`.`user_id`,
							    `services`.`name` AS `service_name`
							FROM 
			 				    `admins__services` 
							LEFT JOIN 
			 					`admins` 
			 				ON 
			 					`admins`.`id` = `admins__services`.`admin_id` 
							LEFT JOIN 
			 				    `services` 
			 				ON 
			 				    `services`.`id` = `admins__services`.`service` 
							LEFT JOIN 
			 				    `servers` 
			 				ON
			 					`servers`.`id` = `admins`.`server` 
							LEFT JOIN 
			 				    `users` 
			 				ON 
			 					`users`.`id` = `admins`.`user_id` 
							WHERE 
							    `admins__services`.`ending_date`!='0000-00-00 00:00:00' 
							  	AND `admins__services`.`ending_date`<:date 
							  	AND `admins`.`active` = '1' 
								AND `admins`.`pause` = 0 
								AND `servers`.`type` != 0"
		);
		$STH->execute([':date' => $date]);
		$admin = $STH->fetchAll();
		$acount = count($admin);
		for ($i=0; $i < $acount; $i++) {
			if(!empty($admin[$i]['user_id'])) {
				$left = strtotime($admin[$i]['ending_date'])-time();
				$left = expand_seconds2($left, 2);

				if(empty($admin[$i]['service_name'])) {
					$admin[$i]['service_name'] = 'Неизвестно';
				}

				incNotifications();

				$noty = noty_of_ending_service($left, $admin[$i]['name'], $admin[$i]['service_name'], $admin[$i]['server_name']);
				send_noty($pdo, $noty, $admin[$i]['user_id'], 3);

				if(!empty($admin[$i]['email']) && substr($admin[$i]['email'], 6)!='vk_id_' && $admin[$i]['email_notice'] == 1) {
					$letter = letter_of_ending_service($site_name, $left, $admin[$i]['name'], $admin[$i]['service_name'], $admin[$i]['server_name']);
					sendmail($admin[$i]['email'], $letter['subject'], $letter['message'], $pdo);
				}
			}
		}

		return true;
	}

	public function find_users_file($ftp_connection, $ftp_string) {
		$parse = $this->explode_users_file($ftp_string);
		$ftp_file = $parse['file'];
		$ftp_string = $parse['string'];

		$files = ftp_nlist($ftp_connection, $ftp_string);
		for ($i=0; $i <= count($files); $i++) {
			if (isset($files[$i]) && (($files[$i] == $ftp_string.$ftp_file) || ($files[$i] == $ftp_string.'/'.$ftp_file) || ($files[$i] == $ftp_file))){
				return true;
			}
		}
		return false;
	}

	public function admin_services($name, $server, $pdo, $admin_id = null) {
		if($admin_id == null) {
			$STH = $pdo->prepare("SELECT `id`,`user_id` FROM `admins` WHERE `name`=:name AND `server`=:server LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':name' => $name, ':server' => $server ));
			$row = $STH->fetch();
			$admin_id = $row->id;
		}

		$i=0;
		$STH = $pdo->prepare("SELECT `admins__services`.`ending_date`, `admins__services`.`id`, `admins__services`.`irretrievable`, `admins__services`.`rights_und`, `admins__services`.`sb_group_und`, `admins__services`.`immunity_und`, `services`.`sb_group`, `services`.`immunity`, `admins__services`.`service`, `admins__services`.`service_time`, `admins__services`.`bought_date`, `admins__services`.`ending_date`, `services`.`rights` FROM `admins__services` LEFT JOIN `services` ON `admins__services`.`service` = `services`.`id` WHERE `admins__services`.`admin_id` = :admin_id"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':admin_id' => $admin_id ));
		while($row = $STH->fetch()) { 
			$services[$i]['service'] = $row->service;
			$services[$i]['service_time'] = $row->service_time;
			$services[$i]['bought_date'] = $row->bought_date;
			$services[$i]['ending_date'] = $row->ending_date;
			$services[$i]['irretrievable'] = $row->irretrievable;

			$services[$i]['rights'] = $row->rights;
			$services[$i]['sb_group'] = $row->sb_group;
			$services[$i]['immunity'] = $row->immunity;
			$services[$i]['rights_und'] = $row->rights_und;
			$services[$i]['sb_group_und'] = $row->sb_group_und;
			$services[$i]['immunity_und'] = $row->immunity_und;

			$services[$i]['expired'] = strtotime($row->ending_date);
			$i++;
		}
		if($i == 0) {
			return false;
		} elseif($i == 1 and $services[0]['service'] == 0) {
			$services[0]['rights'] = $services[0]['rights_und'];
			$services[0]['sb_group'] = $services[0]['sb_group_und'];
			$services[0]['immunity'] = $services[0]['immunity_und'];
		}
		return $services;
	}

	public function get_rights($services) {
		$count = count($services);
		$rights['flags'] = '';
		$rights['sb_group'] = '';
		for ($i=0; $i < $count; $i++) {
			if(!empty($services[$i]['rights_und']) and $services[$i]['rights_und']!='none') {
				$rights['flags'] .= $services[$i]['rights_und'];
			} else {
				$rights['flags'] .= $services[$i]['rights'];
			}
			if(!empty($services[$i]['sb_group'])) {
				$rights['sb_group'] = $services[$i]['sb_group'];
			}
		}
		$rights['flags'] = trim($rights['flags']);
		$rights['sb_group'] = trim($rights['sb_group']);
		return $rights;
	}

	public function collect_immunity($services) {
		$count = count($services);
		$immunity = 0;
		for ($i=0; $i < $count; $i++) {
			if($services[$i]['immunity'] > $immunity) {
				$immunity = $services[$i]['immunity'];
			}
		}
		$immunity = trim($immunity);
		return $immunity;
	}

	public function collect_expired($services) {
		$count = count($services);
		$expired = 0;
		for ($i=0; $i < $count; $i++) {
			if($services[$i]['expired'] > $expired) {
				$expired = $services[$i]['expired'];
			}
		}
		$expired = trim($expired);
		return $expired;
	}

	public function check_to_expired($pdo, $table) {
		$STH = $pdo->query("SHOW COLUMNS FROM $table");
		$STH->execute();
		$row = $STH->fetchAll();

		$if['expired'] = 0;
		for ($i=0; $i < count($row); $i++) {
			if ($row[$i]['Field'] == 'expired') {
				$if['expired']++;
			}
		}
		if ($if['expired']==0) {
			return 0;
		} else {
			return 1;
		}
	}

	public function collect_rights($rights) {
		$out_rights = '';
		$tmp        = [];

		for($a = 0; $a < strlen($rights); $a++) {
			if(in_array($rights[$a], $tmp))
				continue;
			else $out_rights .= ($tmp[] = $rights[$a]);
		}
		return $out_rights;
	}

	public function serialize_info($admin_services) {
		$count = count($admin_services);
		if($count == 1 and $admin_services[0]['service'] == 0) {
			$admin_services = '';
		} else {
			for ($i=0; $i < $count; $i++) { 
				unset($admin_services[$i]['rights']);
				unset($admin_services[$i]['sb_group']);
				unset($admin_services[$i]['immunity']);
				//unset($admin_services[$i]['rights_und']);
				unset($admin_services[$i]['sb_group_und']);
				unset($admin_services[$i]['immunity_und']);
				unset($admin_services[$i]['expired']);
			}
			$admin_services = serialize($admin_services);
		}

		return $admin_services;
	}

	public function get_ending_date($time) {
		if ($time == 0){
			$time = '0000-00-00 00:00:00';
		} else {
			$time = time() + $time*24*60*60;
			$time = date("Y-m-d H:i:s", $time);
		}
		return $time;
	}

	public function dell_admins($pdo, $server) {
		$STH = $pdo->query("SELECT `id` FROM `admins` WHERE `server` = '$server'");
		$STH->execute();
		$row = $STH->fetchAll();
		$count = count($row);
		for ($i=0; $i < $count; $i++) { 
			$STH = $pdo->prepare("DELETE FROM `admins__services` WHERE `admin_id`=:id");
			$STH->execute(array( ':id' => $row[$i]['id'] ));
		}
		$pdo->exec("DELETE FROM `admins` WHERE `server`='$server'");
	}

	public function generate_user_str($nick, $pdo, $table){
		$i = 0;
		$user = $nick;
		do {
			if($i != 0) {
				$user = $nick.'('.$i.')';
			}
			$STH = $pdo->prepare("SELECT `aid` FROM `$table` WHERE `user`=:user LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':user' => $user ));
			$row = $STH->fetch();
			if(isset($row->aid)) {
				$temp = null;
			} else {
				$temp = 1;
			}
			$i++;
		} while (empty($temp));
		return $user;
	}

	public function dell_admin_from_db($pdo, $old_name, $server, $name, $title) {
		$STH = $pdo->prepare("SELECT `servers`.`db_host`, `servers`.`db_code`, `servers`.`ip`, `servers`.`port`, `servers`.`type`, `servers`.`db_user`, `servers`.`db_pass`, `servers`.`db_db`, `servers`.`db_prefix` FROM `servers` LEFT JOIN `admins` ON `admins`.`server` = `servers`.`id` WHERE `admins`.`name`=:name AND `servers`.`id`=:server LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':name' => $name, ':server' => $server ));
		$info = $STH->fetch();
		$old_name = htmlspecialchars_decode($old_name, ENT_QUOTES);

		if ($info->type == 2 || $info->type == 4){
			if(!$pdo2 = db_connect($info->db_host, $info->db_db, $info->db_user, $info->db_pass)) {
				return false;
			}
			set_names($pdo2, $info->db_code);

			if ($info->type == 2) {
				if(!$admin_id = $this->get_admin_id($old_name, 1, $pdo2, $info->db_prefix, $info->ip, $info->port)) {
					log_error($title.': Не найден ID админа');
					return false;
				}

				$table = set_prefix($info->db_prefix, "amxadmins");
				$STH = $pdo2->prepare("DELETE FROM `$table` WHERE `id`=:id LIMIT 1");
				$STH->execute(array( ':id' => $admin_id ));

				$table = set_prefix($info->db_prefix, "admins_servers");
				$STH = $pdo2->prepare("DELETE FROM `$table` WHERE `admin_id`=:id LIMIT 1");
				$STH->execute(array( ':id' => $admin_id ));
			}
			if ($info->type == 4) {
				if(!$admin_id = $this->get_admin_id($old_name, 2, $pdo2, $info->db_prefix, $info->ip, $info->port)) {
					log_error($title.': Не найден ID админа');
					return false;
				}

				$table = set_prefix($info->db_prefix, "admins");
				$STH = $pdo2->prepare("DELETE FROM `$table` WHERE `aid`=:id LIMIT 1");
				$STH->execute(array( ':id' => $admin_id ));

				$table = set_prefix($info->db_prefix, "admins_servers_groups");
				$STH = $pdo2->prepare("DELETE FROM `$table` WHERE `admin_id`=:id LIMIT 1");
				$STH->execute(array( ':id' => $admin_id ));
			}
		}

		return true;
	}

	public function dell_admin_full($pdo, $id, $title, $str_type = 0) {
		$STH = $pdo->prepare("SELECT `servers`.`db_host`, `admins`.`name`, `admins`.`server`, `servers`.`db_code`, `servers`.`id`, `servers`.`ip`, `servers`.`port`, `servers`.`type`, `servers`.`db_user`, `servers`.`db_pass`, `servers`.`db_db`, `servers`.`db_prefix` FROM `servers` LEFT JOIN `admins` ON `admins`.`server` = `servers`.`id` WHERE `admins`.`id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':id' => $id ));
		$info = $STH->fetch();

		if ($info->type == 2 || $info->type == 4){
			if(!$pdo2 = db_connect($info->db_host, $info->db_db, $info->db_user, $info->db_pass)) {
				return false;
			}
			set_names($pdo2, $info->db_code);
			$name = htmlspecialchars_decode($info->name, ENT_QUOTES);

			if ($info->type == 2) {
				if(!$admin_id = $this->get_admin_id($name, 1, $pdo2, $info->db_prefix, $info->ip, $info->port)) {
					$table = set_prefix($info->db_prefix, "amxadmins");
					$STH = $pdo2->prepare("DELETE FROM `$table` WHERE `steamid`=:steamid LIMIT 1");
					$STH->execute(array( ':steamid' => $name ));
				} else {
					$table = set_prefix($info->db_prefix, "amxadmins");
					$STH = $pdo2->prepare("DELETE FROM `$table` WHERE `id`=:id LIMIT 1");
					$STH->execute(array( ':id' => $admin_id ));

					$table = set_prefix($info->db_prefix, "admins_servers");
					$STH = $pdo2->prepare("DELETE FROM `$table` WHERE `admin_id`=:id LIMIT 1");
					$STH->execute(array( ':id' => $admin_id ));
				}
			}
			if ($info->type == 4) {
				if(!$admin_id = $this->get_admin_id($name, 2, $pdo2, $info->db_prefix, $info->ip, $info->port)) {
					$table = set_prefix($info->db_prefix, "admins");
					$STH = $pdo2->prepare("DELETE FROM `$table` WHERE `authid`=:authid LIMIT 1");
					$STH->execute(array( ':authid' => $name ));
				} else {
					$table = set_prefix($info->db_prefix, "admins");
					$STH = $pdo2->prepare("DELETE FROM `$table` WHERE `aid`=:id LIMIT 1");
					$STH->execute(array( ':id' => $admin_id ));

					$table = set_prefix($info->db_prefix, "admins_servers_groups");
					$STH = $pdo2->prepare("DELETE FROM `$table` WHERE `admin_id`=:id LIMIT 1");
					$STH->execute(array( ':id' => $admin_id ));
				}
			}
		}

		$pdo->exec("DELETE FROM `admins` WHERE `id`='$id' LIMIT 1");
		$pdo->exec("DELETE FROM `admins__services` WHERE `admin_id`='$id'");

		if ($info->type == 1 || $info->type == 3){
			if(!$this->export_to_users_ini($pdo, $info->id, $title, $str_type)) {
				log_error($title.': Не удалось экспортировать администратора в базу данных сервера');
				return false;
			}
		}



		try {
			(new OurSourceQuery())->reloadAdmins($info->id);
		} catch(Exception $e) {
			log_error($e->getMessage());
		}

		$this->set_admin_dell_time($pdo);

		return true;
	}

	public function export_to_users_ini($pdo, $server_id, $title, $str_type = 0, $united = 0) {
		if($united == 0) {
			$original_server = 1;
			$united = $server_id;
		} else {
			$original_server = 0;
		}
		$STH = $pdo->query("SELECT id,ftp_host,ftp_login,ftp_pass,ftp_port,ftp_string,type,united FROM servers WHERE id='$united' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$server = $STH->fetch();
		if (empty($server->id)){
			log_error($title.': Данного сервера не существует, ID: '.$united);
			return false;
		}
		if ($server->type != 1 and $server->type != 3){
			log_error($title.': Неверный тип, невозможно подключение к FTP серверу');
			return false;
		}

		$STH = $pdo->query("SELECT * FROM admins WHERE server='$server_id'");
		$STH->execute();
		$row = $STH->fetchAll();
		$count = count($row);
		$ftp_data = '';
		for ($i=0; $i < $count; $i++) {
			$admin_services = $this->admin_services('none', $server_id, $pdo, $row[$i]['id']);
			$rights = $this->get_rights($admin_services);
			$rights['flags'] = $this->collect_rights($rights['flags']);
			$admin_services = $this->serialize_info($admin_services);
			$name = htmlspecialchars_decode($row[$i]['name'], ENT_QUOTES);
			if($row[$i]['active'] == '2' || $row[$i]['pause'] != '0') {
				$active = ';';
			} else {
				$active = '';
			}
			if(!empty($row[$i]['pass'])) {
				$pass = htmlspecialchars_decode($row[$i]['pass'], ENT_QUOTES);
			}

			$ftp_data .= $active.'"'.$name.'" "'.$pass.'" "'.$rights['flags'].'" "'.$row[$i]['type'].'";'. "'".$admin_services."' '".$row[$i]['user_id']."' written by gamecms :end:"."\n";
		}

		if(!$ftp_connection = $this->ftp_connection($server->ftp_host, $server->ftp_port, $server->ftp_login, $server->ftp_pass, $title)){
			return false;
		}
		if(!$this->find_users_file($ftp_connection, $server->ftp_string)) {
			return false;
		}

		$remote_file = $server->ftp_string;
		$local_file = $_SERVER["DOCUMENT_ROOT"].'/files/temp/users'.rand().'.txt';

		if(!$file = fopen($local_file, 'w')){
			log_error($title.': Не удалось создать временный файл users.txt');
			return false;
		}
		if (ftp_fget($ftp_connection, $file, $remote_file, FTP_ASCII, 0)) {
			fclose($file);
			file_put_contents($local_file, $ftp_data, LOCK_EX);
			$file = fopen($local_file, 'r');
			if (ftp_fput($ftp_connection, $remote_file, $file, FTP_ASCII, 0)) {
				fclose($file);
				unlink($local_file);
				$this->close_ftp($ftp_connection);
			} else {
				log_error($title.': Не удалось сохранить файл на FTP сервере');
				return false;
			}
		} else {
			log_error($title.': Не удалось получить файл с FTP сервера');
			return false;
		}

		try {
			(new OurSourceQuery())->reloadAdmins($united);
		} catch(Exception $e) {
			log_error($e->getMessage());
		}

		$this->set_admin_dell_time($pdo);

		if($original_server == 1) {
			$STH = $pdo->prepare("SELECT id FROM servers WHERE united=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':id' => $server_id ));
			$united_server = $STH->fetch();
			if(isset($united_server->id)) {
				$this->export_to_users_ini($pdo, $server_id, $title, $str_type, $united_server->id);
			}
		}

		return true;
	}

	public function get_admin_id($name, $type, $pdo, $db_prefix, $ip, $port) {
		if($type == 1) {
			$table = set_prefix($db_prefix, "amxadmins");
			$STH = $pdo->prepare("SELECT `id` FROM `$table` WHERE `steamid`=:steamid");
			$STH->execute(array( ':steamid' => $name ));
			$amxadmin = $STH->fetchAll();
			$count = count($amxadmin);

			$table = set_prefix($db_prefix, "serverinfo");
			$STH = $pdo->prepare("SELECT `id` FROM `$table` WHERE `address`=:address LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':address' => $ip.':'.$port ));
			$serverinfo = $STH->fetch();
			if(empty($serverinfo->id)) {
				log_error('Сервер не найден');
				return false;
			}

			$table = set_prefix($db_prefix, "admins_servers");
			for ($i=0; $i < $count; $i++) { 
				$STH = $pdo->prepare("SELECT `admin_id` FROM `$table` WHERE `admin_id`=:admin_id AND `server_id`=:server_id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':admin_id' => $amxadmin[$i]['id'], ':server_id' => $serverinfo->id ));
				$row = $STH->fetch();
				if(isset($row->admin_id)) {
					return $amxadmin[$i]['id'];
				}
			}
		} elseif($type == 2) {
			$table = set_prefix($db_prefix, "admins");
			$STH = $pdo->prepare("SELECT `aid` FROM `$table` WHERE `authid`=:steamid");
			$STH->execute(array( ':steamid' => $name ));
			$admins = $STH->fetchAll();
			$count = count($admins);

			$table = set_prefix($db_prefix, "servers");
			$STH = $pdo->prepare("SELECT `sid` FROM `$table` WHERE `ip`=:ip AND `port`=:port LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':ip' => $ip, ':port' => $port ));
			$servers = $STH->fetch();
			if(empty($servers->sid)) {
				log_error('Сервер не найден');
				return false;
			}

			$table = set_prefix($db_prefix, "admins_servers_groups");
			for ($i=0; $i < $count; $i++) { 
				$STH = $pdo->prepare("SELECT `admin_id` FROM `$table` WHERE `admin_id`=:admin_id AND `server_id`=:server_id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':admin_id' => $admins[$i]['aid'], ':server_id' => $servers->sid ));
				$row = $STH->fetch();
				if(isset($row->admin_id)) {
					return $admins[$i]['aid'];
				}
			}
		}
		return false;
	}

	public function get_admin_id2($id, $name, $pass, $pass_md5, $server, $type, $pdo, $pdo2, $db_prefix) {
		$admin_services = $this->admin_services('none', $server, $pdo, $id);
		$rights = $this->get_rights($admin_services);
		$flags = $this->collect_rights($rights['flags']);
		if($type == 1) {
			$table = set_prefix($db_prefix, "amxadmins");
			$table2 = set_prefix($db_prefix, "admins_servers");
			if(empty($pass_md5)) {
				$STH = $pdo2->prepare("SELECT `id` FROM `$table` WHERE `steamid`=:steamid AND `password` is NULL AND `access`=:access LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':steamid' => $name, ':access' => $flags ));
			} else {
				$STH = $pdo2->prepare("SELECT `id` FROM `$table` WHERE `steamid`=:steamid AND `password`=:password AND `access`=:access LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':steamid' => $name, ':password' => $pass_md5, ':access' => $flags ));
			}
			while($admin = $STH->fetch()) { 
				$STH2 = $pdo2->prepare("SELECT `admin_id` FROM `$table2` WHERE `admin_id`=:admin_id LIMIT 1"); $STH2->setFetchMode(PDO::FETCH_OBJ);
				$STH2->execute(array( ':admin_id' => $admin->id ));
				$row = $STH2->fetch();
				if(empty($row->admin_id)) {
					return $admin->id;
				}
			}
		} elseif($type == 2) {
			$table = set_prefix($db_prefix, "admins");
			$table2 = set_prefix($db_prefix, "admins_servers_groups");

			if($rights['sb_group'] == '') {
				$sb_group = NULL;
			} else {
				$sb_group = $rights['sb_group'];
			}
			if($flags == '') {
				$flags = NULL;
			}
			if(empty($pass)) {
				$pass = NULL;
			}
			/* костыль :( */
			if($sb_group == NULL and $flags == NULL and $pass == NULL) {
				$STH = $pdo2->prepare("SELECT `aid` FROM `$table` WHERE `authid`=:authid AND `srv_password` is NULL AND `srv_flags` is NULL AND `srv_group` is NULL LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':authid' => $name ));
			} elseif($sb_group == NULL and $flags == NULL) {
				$STH = $pdo2->prepare("SELECT `aid` FROM `$table` WHERE `authid`=:authid AND `srv_password`=:srv_password AND `srv_flags` is NULL AND `srv_group` is NULL LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':authid' => $name, ':srv_password' => $pass ));
			} elseif($flags == NULL and $pass == NULL) {
				$STH = $pdo2->prepare("SELECT `aid` FROM `$table` WHERE `authid`=:authid AND `srv_password`  is NULL AND `srv_flags`  is NULL AND `srv_group`=:srv_group LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':authid' => $name, ':srv_group' => $sb_group ));
			} elseif($sb_group == NULL and $pass == NULL) {
				$STH = $pdo2->prepare("SELECT `aid` FROM `$table` WHERE `authid`=:authid AND `srv_password` is NULL AND `srv_flags`=:srv_flags AND `srv_group` is NULL LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':authid' => $name, ':srv_flags' => $flags ));
			} elseif($sb_group == NULL) {
				$STH = $pdo2->prepare("SELECT `aid` FROM `$table` WHERE `authid`=:authid AND `srv_password`=:srv_password AND `srv_flags`=:srv_flags AND `srv_group` is NULL LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':authid' => $name, ':srv_password' => $pass, ':srv_flags' => $flags ));
			} elseif($pass == NULL) {
				$STH = $pdo2->prepare("SELECT `aid` FROM `$table` WHERE `authid`=:authid AND `srv_password` is NULL AND `srv_flags`=:srv_flags AND `srv_group`=:srv_group LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':authid' => $name, ':srv_flags' => $flags, ':srv_group' => $sb_group ));
			} elseif($flags == NULL) {
				$STH = $pdo2->prepare("SELECT `aid` FROM `$table` WHERE `authid`=:authid AND `srv_password`=:srv_password AND `srv_flags` is NULL AND `srv_group`=:srv_group LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':authid' => $name, ':srv_password' => $pass, ':srv_group' => $sb_group ));
			} else {
				$STH = $pdo2->prepare("SELECT `aid` FROM `$table` WHERE `authid`=:authid AND `srv_password`=:srv_password AND `srv_flags`=:srv_flags AND `srv_group`=:srv_group LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':authid' => $name, ':srv_password' => $pass, ':srv_flags' => $flags, ':srv_group' => $sb_group ));
			}

			while($admin = $STH->fetch()) { 
				$STH2 = $pdo2->prepare("SELECT `admin_id` FROM `$table2` WHERE `admin_id`=:admin_id LIMIT 1"); $STH2->setFetchMode(PDO::FETCH_OBJ);
				$STH2->execute(array( ':admin_id' => $admin->aid ));
				$row = $STH2->fetch();
				if(empty($row->admin_id)) {
					return $admin->aid;
				}
			}
		}
		return false;
	}

	public function import_admins($server, $pdo) {
		$STH = $pdo->query("SELECT * FROM servers WHERE id='$server' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$server = $STH->fetch();
		if($server->type == 1 or $server->type == 3){
			if(!$ftp_connection = $this->ftp_connection($server->ftp_host, $server->ftp_port, $server->ftp_login, $server->ftp_pass, 'IMPORT_ADMINS')){
				return false;
			}
			$remote_file = $server->ftp_string;
			$local_file = '../files/temp/users.txt';
			if(!$file = fopen($local_file, 'w')) {
				log_error('IMPORT_ADMINS: Не удалось создать временный файл users.txt');
				return false;
			}

			if (ftp_fget($ftp_connection, $file, $remote_file, FTP_ASCII, 0)) {
				if(!$admins = file_get_contents($local_file)) {
					log_error('IMPORT_ADMINS: Не удалось открыть временный файл users.txt');
					return false;
				}
				$this->dell_admins($pdo, $server->id);

				$admins = explode(":end:", $admins);
				$count = count($admins);
				for($i=0;$i<$count;$i++) {
					$data = explode(';', $admins[$i], 2);
					if(isset($data['0']) and isset($data['1'])) {
						$data['admin'] = explode('"', $data['0']);
						$data['params'] = explode("'", $data['1']);
					}
					if (!empty($data['admin']['1']) and !empty($data['admin']['5']) and !empty($data['admin']['7']) and (trim($data['admin']['0']) != ';')){
						$admin['name'] = check($data['admin']['1'],null);
						$admin['type'] = check($data['admin']['7'],null);
						if (empty($data['admin']['3'])){
							$admin['pass'] = '';
							$admin['pass_md5'] = '';
						} else {
							$admin['pass'] = check($data['admin']['3'],null);
							$admin['pass_md5'] = md5($admin['pass']);
						}

						if(!empty($data['params']['1'])) {
							$services = unserialize($data['params']['1']);
							$admin['user_id'] = $data['params']['3'];
							$admin['immunity_und'] = 0;
							$admin['sb_group_und'] = 'none';
						} else {
							$admin['user_id'] = 0;
							$admin['immunity_und'] = 0;
							$admin['sb_group_und'] = 'none';
							$services[0] = array( 'service' => 0, 'rights_und' => check($data['admin']['5'],null), 'service_time' => 0, 'bought_date' => '0000-00-00 00:00:00', 'ending_date' => '0000-00-00 00:00:00', 'irretrievable' => 0 );
						}

						$STH = $pdo->prepare("SELECT `id` FROM `users` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
						$STH->execute(array( ':id' => $admin['user_id'] ));
						$row = $STH->fetch();
						if(empty($row->id)) {
							$admin['user_id'] = 0;
						}

						$STH = $pdo->prepare("INSERT INTO admins (name,pass,pass_md5,type,server,user_id) values (:name, :pass, :pass_md5, :type, :server, :user_id)");
						if (!$STH->execute(array( 'name' => $admin['name'], 'pass' => $admin['pass'], 'pass_md5' => $admin['pass_md5'], 'type' => $admin['type'], 'server' => $server->id, 'user_id' => $admin['user_id'] )) == '1') {
							log_error('IMPORT_ADMINS: Ошибка записи админа в базу данных.');
							return false;
						}

						$STH = $pdo->prepare("SELECT `id` FROM `admins` WHERE `name`=:name and `server`=:server LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
						$STH->execute(array( ':name' => $admin['name'], ':server' => $server->id ));
						$row = $STH->fetch();
						$admin['id'] = $row->id;

						for ($j=0; $j < count($services); $j++) { 
							if(empty($services[$j]['rights_und'])) {
								$services[$j]['rights_und'] = 'none';
							}
							$STH = $pdo->prepare("INSERT INTO `admins__services` (`admin_id`,`service`,`service_time`,`bought_date`,`ending_date`,`irretrievable`,`rights_und`,`immunity_und`,`sb_group_und`) values (:admin_id, :service, :service_time, :bought_date, :ending_date, :irretrievable, :rights_und, :immunity_und, :sb_group_und)");  
							if (!$STH->execute(array( ':admin_id' => $admin['id'], ':service' => $services[$j]['service'], ':service_time' => $services[$j]['service_time'], ':bought_date' => $services[$j]['bought_date'], ':ending_date' => $services[$j]['ending_date'], ':irretrievable' => $services[$j]['irretrievable'], ':rights_und' => $services[$j]['rights_und'], ':immunity_und' => $admin['immunity_und'], ':sb_group_und' => $admin['sb_group_und'] )) == '1') {
								log_error('IMPORT_ADMINS: Ошибка записи прав в базу данных.');
								return false;
							}
						}
					}
				}

				fclose($file);
				unlink($local_file);
				$this->close_ftp($ftp_connection);
			} else {
				log_error('IMPORT_ADMINS: Не удалось скачать файл '.$remote_file);
				return false;
			}
		} elseif($server->type == 2 or $server->type == 4){
			if(!$pdo2 = db_connect($server->db_host, $server->db_db, $server->db_user, $server->db_pass)) {
				log_error('IMPORT_ADMINS: Не удалось подключиться к базе данных.');
				return false;
			}
			set_names($pdo2, $server->db_code);

			if($server->type == 2){
				$table = set_prefix($server->db_prefix, "serverinfo");
				$STH = $pdo2->prepare("SELECT `id` FROM `$table` WHERE `address`=:address LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':address' => $server->ip.':'.$server->port ));
				$row = $STH->fetch();
				if (empty($row->id)){
					log_error('IMPORT_ADMINS: Сервер не найден');
					return false;
				}

				$table = set_prefix($server->db_prefix, "admins_servers");
				$table2 = set_prefix($server->db_prefix, "amxadmins");
				$STH = $pdo2->query("SELECT `$table`.`admin_id`,`$table2`.* FROM `$table` LEFT JOIN `$table2` ON `$table2`.`id` = `$table`.`admin_id` WHERE `$table`.`server_id` = '$row->id'");
				$STH->execute();
				$data['admin'] = $STH->fetchAll();
				$count = count($data['admin']);
				$this->dell_admins($pdo, $server->id);

				for($i=0;$i<$count;$i++) {
					if (!empty($data['admin'][$i]['steamid'])){
						$admin['name'] = check($data['admin'][$i]['steamid'],null);
						$admin['type'] = check($data['admin'][$i]['flags'],null);
						if (empty($data['admin'][$i]['password'])){
							$admin['pass'] = '';
							$admin['pass_md5'] = '';
						} else {
							$admin['pass'] = '';
							$admin['pass_md5'] = $data['admin'][$i]['password'];
						}

						if(!empty($data['admin'][$i]['gamecms'])) {
							$services = unserialize($data['admin'][$i]['gamecms']);
							$admin['user_id'] = check($data['admin'][$i]['username'],null);
							$admin['immunity_und'] = 0;
							$admin['sb_group_und'] = '';
						} else {
							$admin['user_id'] = 0;
							$admin['immunity_und'] = 0;
							$admin['sb_group_und'] = '';

							$STH = $pdo->prepare("SELECT `id` FROM `services` WHERE `rights`=:rights AND `server` = :server LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
							$STH->execute(array( ':rights' => check($data['admin'][$i]['access'],null), ':server' => $server->id ));
							$row = $STH->fetch();
							if(isset($row->id)) {
								$service = $row->id;
							} else {
								$service = 0;
							}

							if($data['admin'][$i]['expired'] == 0 || $data['admin'][$i]['created'] == 0) {
								$service_time = 0;
							} else {
								$service_time = round(($data['admin'][$i]['expired']-$data['admin'][$i]['created'])/60/60/24);
							}
							if($data['admin'][$i]['expired'] != 0) {
								$ending_date = date("Y-m-d H:i:s", $data['admin'][$i]['expired']);
							} else {
								$ending_date = '0000-00-00 00:00:00';
							}
							if($data['admin'][$i]['created'] != 0) {
								$bought_date = date("Y-m-d H:i:s", $data['admin'][$i]['created']);
							} else {
								$bought_date = '0000-00-00 00:00:00';
							}

							$services[0] = array( 'service' => $service, 'rights_und' => check($data['admin'][$i]['access'],null), 'service_time' => $service_time, 'bought_date' => $bought_date, 'ending_date' => $ending_date, 'irretrievable' => 0 );
						}

						$STH = $pdo->prepare("INSERT INTO admins (name,pass,pass_md5,type,server,user_id) values (:name, :pass, :pass_md5, :type, :server, :user_id)");
						if (!$STH->execute(array( 'name' => $admin['name'], 'pass' => $admin['pass'], 'pass_md5' => $admin['pass_md5'], 'type' => $admin['type'], 'server' => $server->id, 'user_id' => $admin['user_id'] )) == '1') {
							log_error('IMPORT_ADMINS: Ошибка записи админа в базу данных.');
							return false;
						}

						$STH = $pdo->prepare("SELECT `id` FROM `admins` WHERE `name`=:name and `server`=:server LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
						$STH->execute(array( ':name' => $admin['name'], ':server' => $server->id ));
						$row = $STH->fetch();
						$admin['id'] = $row->id;

						for ($j=0; $j < count($services); $j++) { 
							if(empty($services[$j]['rights_und'])) {
								$services[$j]['rights_und'] = 'none';
							}
							$STH = $pdo->prepare("INSERT INTO `admins__services` (`admin_id`,`service`,`service_time`,`bought_date`,`ending_date`,`irretrievable`,`rights_und`,`immunity_und`,`sb_group_und`) values (:admin_id, :service, :service_time, :bought_date, :ending_date, :irretrievable, :rights_und, :immunity_und, :sb_group_und)");  
							if (!$STH->execute(array( ':admin_id' => $admin['id'], ':service' => $services[$j]['service'], ':service_time' => $services[$j]['service_time'], ':bought_date' => $services[$j]['bought_date'], ':ending_date' => $services[$j]['ending_date'], ':irretrievable' => $services[$j]['irretrievable'], ':rights_und' => $services[$j]['rights_und'], ':immunity_und' => $admin['immunity_und'], ':sb_group_und' => $admin['sb_group_und'] )) == '1') {
								log_error('IMPORT_ADMINS: Ошибка записи прав в базу данных.');
								return false;
							}
						}
					}
				}
			} elseif($server->type == 4) {
				$table = set_prefix($server->db_prefix, "servers");
				$STH = $pdo2->prepare("SELECT `sid` FROM `$table` WHERE `ip`=:ip AND `port`=:port LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':ip' => $server->ip, ':port' => $server->port ));
				$row = $STH->fetch();
				if (empty($row->sid)){
					log_error('IMPORT_ADMINS: Сервер не найден');
					return false;
				}

				$table = set_prefix($server->db_prefix, "admins_servers_groups");
				$table2 = set_prefix($server->db_prefix, "admins");
				$STH = $pdo2->query("SELECT `$table`.`admin_id`,`$table2`.* FROM `$table` LEFT JOIN `$table2` ON `$table2`.`aid` = `$table`.`admin_id` WHERE `$table`.`server_id` = '$row->sid'");
				$STH->execute();
				$data['admin'] = $STH->fetchAll();
				$count = count($data['admin']);
				$this->dell_admins($pdo, $server->id);

				for($i=0;$i<$count;$i++) {
					if (!empty($data['admin'][$i]['authid'])){
						$admin['name'] = check($data['admin'][$i]['authid'],null);
						if (empty($data['admin'][$i]['srv_password'])){
							$admin['pass'] = '';
							$admin['pass_md5'] = '';
							$admin['type'] = 'ce';
						} else {
							$admin['pass'] = $data['admin'][$i]['srv_password'];
							$admin['pass_md5'] = md5($data['admin'][$i]['srv_password']);
							$admin['type'] = 'ca';
						}

						if(!empty($data['admin'][$i]['gamecms'])) {
							$services = unserialize($data['admin'][$i]['gamecms']);
							$admin['user_id'] = check($data['admin'][$i]['user_id'],null);
							$admin['immunity_und'] = 0;
							$admin['sb_group_und'] = '';
						} else {
							$admin['user_id'] = 0;
							$admin['immunity_und'] = check($data['admin'][$i]['immunity'],null);
							$admin['sb_group_und'] = check($data['admin'][$i]['srv_group'],null);
							if(empty($admin['rights_und'])) {
								$admin['rights_und'] = '';
							}
							if(empty($admin['immunity_und'])) {
								$admin['immunity_und'] = 0;
							}
							if(empty($admin['sb_group_und'])) {
								$admin['sb_group_und'] = '';
							}
							$services[0] = array( 'service' => 0, 'rights_und' => check($data['admin'][$i]['srv_flags'],null), 'service_time' => 0, 'bought_date' => '0000-00-00 00:00:00', 'ending_date' => '0000-00-00 00:00:00', 'irretrievable' => 0 );
						}

						if(empty($admin['user_id'])) {
							$admin['user_id'] = 0;
						}

						$STH = $pdo->prepare("INSERT INTO admins (name,pass,pass_md5,type,server,user_id) values (:name, :pass, :pass_md5, :type, :server, :user_id)");
						if (!$STH->execute(array( 'name' => $admin['name'], 'pass' => $admin['pass'], 'pass_md5' => $admin['pass_md5'], 'type' => $admin['type'], 'server' => $server->id, 'user_id' => $admin['user_id'] )) == '1') {
							log_error('IMPORT_ADMINS: Ошибка записи админа в базу данных.');
							return false;
						}

						$STH = $pdo->prepare("SELECT `id` FROM `admins` WHERE `name`=:name and `server`=:server LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
						$STH->execute(array( ':name' => $admin['name'], ':server' => $server->id ));
						$row = $STH->fetch();
						$admin['id'] = $row->id;

						for ($j=0; $j < count($services); $j++) {
							if(empty($services[$j]['rights_und'])) {
								$services[$j]['rights_und'] = 'none';
							}
							$STH = $pdo->prepare("INSERT INTO `admins__services` (`admin_id`,`service`,`service_time`,`bought_date`,`ending_date`,`irretrievable`,`rights_und`,`immunity_und`,`sb_group_und`) values (:admin_id, :service, :service_time, :bought_date, :ending_date, :irretrievable, :rights_und, :immunity_und, :sb_group_und)");  
							if (!$STH->execute(array( ':admin_id' => $admin['id'], ':service' => $services[$j]['service'], ':service_time' => $services[$j]['service_time'], ':bought_date' => $services[$j]['bought_date'], ':ending_date' => $services[$j]['ending_date'], ':irretrievable' => $services[$j]['irretrievable'], ':rights_und' => $services[$j]['rights_und'], ':immunity_und' => $admin['immunity_und'], ':sb_group_und' => $admin['sb_group_und'] )) == '1') {
								log_error('IMPORT_ADMINS: Ошибка записи прав в базу данных.');
								return false;
							}
						}
					}
				}
			}
		}

		$this->set_admin_dell_time($pdo);

		return true;
	}

	public function checking_server_status($pdo, $id) {
		$STH = $pdo->query("SELECT `id`,`db_host`,`db_user`,`db_pass`,`db_db`,`db_prefix`,`type`,`ftp_host`,`ftp_login`,`ftp_pass`,`ftp_port`,`ftp_string` FROM `servers` WHERE `id`='$id' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$server = $STH->fetch();
		if (empty($server->id)){
			log_error('CHECKING_SERVER_STATUS: Данного сервера не существует, ID: '.$id);
			return false;
		}
		if($server->type == 0) {
			return false;
		}
		if($server->type == 1 || $server->type == 3) {
			if(!$this->ftp_connection($server->ftp_host, $server->ftp_port, $server->ftp_login, $server->ftp_pass, 'CHECKING_SERVER_STATUS')){
				return false;
			}
		}
		if($server->type == 2 || $server->type == 4) {
			if(!db_connect($server->db_host, $server->db_db, $server->db_user, $server->db_pass)) {
				return false;
			}
		}
		return true;
	}

	public function export_admin($pdo, $admin_id, $server, $title, $old_name = null) {
		$STH = $pdo->query("SELECT id,ip,port,db_host,db_user,db_pass,db_db,db_prefix,db_code,type FROM servers WHERE id='$server' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$server = $STH->fetch();
		if (empty($server->id)){
			log_error($title.': Данного сервера не существует, ID: '.$server);
			return false;
		}

		if ($server->type == 0 || $server->type == 1 || $server->type == 3){
			log_error($title.': Неверный тип, невозможно подключение к базе данных');
			return false;
		} else {
			if ($server->type == 2 || $server->type == 4){
				$STH = $pdo->prepare("SELECT `admins`.*, `users`.`email`, `users`.`nick` FROM `admins` LEFT JOIN `users` ON `admins`.`user_id` = `users`.`id` WHERE `admins`.`id`=:id AND `admins`.`server`=:server AND `admins`.`active`!='2' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array( ':id' => $admin_id, ':server' => $server->id ));
				$admin = $STH->fetch();

				$admin_services = $this->admin_services('none', $server->id, $pdo, $admin->id);
				$rights = $this->get_rights($admin_services);
				$rights['flags'] = $this->collect_rights($rights['flags']);
				$rights['expired'] = $this->collect_expired($admin_services);
				if ($server->type == 4){
					$rights['immunity'] = $this->collect_immunity($admin_services);
				}
				$admin_services = $this->serialize_info($admin_services);
				$admin->name = htmlspecialchars_decode($admin->name, ENT_QUOTES);

				if($old_name != null) {
					$find_name = htmlspecialchars_decode($old_name, ENT_QUOTES);
				} else {
					$find_name = $admin->name;
				}

				if(!$pdo2 = db_connect($server->db_host, $server->db_db, $server->db_user, $server->db_pass)) {
					return false;
				}
				set_names($pdo2, $server->db_code);

				if ($server->type == 2){
					$admin_id = $this->get_admin_id($find_name, 1, $pdo2, $server->db_prefix, $server->ip, $server->port);

					if(!empty($admin->pass_md5)) {
						$admin->pass_md5 = $admin->pass_md5;
					} else {
						$admin->pass_md5 = null;
					}
					$table = set_prefix($server->db_prefix, "amxadmins");
					if(!empty($admin_id)) {
						$STH = $pdo2->prepare("UPDATE `$table` SET `username`=:username, `nickname`=:nickname, `password`=:password, `access`=:access, `flags`=:flags, `steamid`=:steamid, `created`=:created, `expired`=:expired, `days`=:days, `gamecms`=:gamecms WHERE `id`=:id LIMIT 1");
						if ($STH->execute(array( ':username' => $admin->user_id, ':nickname' => $admin->name, ':password' => $admin->pass_md5, ':access' => $rights['flags'], ':flags' => $admin->type, ':steamid' => $admin->name, ':created' => '0', ':expired' => $rights['expired'], ':days' => '0', ':gamecms' => $admin_services, ':id' => $admin_id )) != '1') {
							log_error($title.': Не удалось обновить права в базе данных');
							return false;
						}
					} else {
						$STH = $pdo2->prepare("INSERT INTO `$table` (`username`,`nickname`,`password`,`access`,`flags`,`steamid`,`created`,`expired`,`days`,`gamecms`) values (:username, :nickname, :password, :access, :flags, :steamid, :created, :expired, :days, :gamecms)");  
						if ($STH->execute(array( ':username' => $admin->user_id, ':nickname' => $admin->name, ':password' => $admin->pass_md5, ':access' => $rights['flags'], ':flags' => $admin->type, ':steamid' => $admin->name, ':created' => '0', ':expired' => $rights['expired'], ':days' => '0', ':gamecms' => $admin_services )) != '1') {
							log_error($title.': Не удалось записать права в базу данных');
							return false;
						}
						if($admin->active != 2) {
							$table = set_prefix($server->db_prefix, "serverinfo");
							$STH = $pdo2->prepare("SELECT `id` FROM `$table` WHERE `address`=:address LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
							$STH->execute(array( ':address' => $server->ip.':'.$server->port ));
							$serverinfo = $STH->fetch();

							$admin_id = get_ai($pdo2, set_prefix($server->db_prefix, "amxadmins")) - 1;
							$table = set_prefix($server->db_prefix, "admins_servers");
							$STH = $pdo2->prepare("INSERT INTO `$table` (`admin_id`,`server_id`,`use_static_bantime`,`custom_flags`) values (:admin_id, :server_id, :use_static_bantime, :custom_flags)");  
							if ($STH->execute(array( ':admin_id' => $admin_id, ':server_id' => $serverinfo->id, ':use_static_bantime' => 'no', 'custom_flags' => '' )) != '1') {
								log_error($title.': Не удалось записать права в базу данных');
								return false;
							}
						}
					}
				}
				if ($server->type == 4){
					$admin_id = $this->get_admin_id($find_name, 2, $pdo2, $server->db_prefix, $server->ip, $server->port);

					if($rights['sb_group'] == '') {
						$rights['sb_group'] = NULL;
					}
					if($rights['flags'] == '') {
						$rights['flags'] = NULL;
					}
					if(empty($admin->pass) || $admin->type == 'ce') {
						$admin->pass_md5 = md5(rand());
						$admin->pass = NULL;
					}
					if(empty($admin->email)) {
						$admin->email = '';
					}
					if(empty($admin->nick)) {
						log_error($title.': У пользователя с ID:'.$admin->user_id.' не заполнено поле "ник" в профиле');
						$admin->nick = 'unnamed';
					}

					$table = set_prefix($server->db_prefix, "admins");
					$expired = $this->check_to_expired($pdo2, $table);
					if(!empty($admin_id)) {
						if($expired != 0) {
							$STH = $pdo2->prepare("UPDATE `$table` SET `nick`=:nick, `expired`=:expired, `authid`=:authid, `immunity`=:immunity, `srv_group`=:srv_group, `srv_flags`=:srv_flags, `srv_password`=:srv_password, `gamecms`=:gamecms, `user_id`=:user_id WHERE `aid`=:id LIMIT 1");
							if ($STH->execute(array( ':nick' => $admin->nick, ':expired' => $rights['expired'], ':authid' => $admin->name, ':immunity' => $rights['immunity'], ':srv_group' => $rights['sb_group'], ':srv_flags' => $rights['flags'], ':srv_password' => $admin->pass, ':gamecms' => $admin_services, ':user_id' => $admin->user_id, ':id' => $admin_id )) != '1') {
								log_error('EXPORT_ADMINS: Не удалось обновить права в базе данных');
								return false;
							}
						} else {
							$STH = $pdo2->prepare("UPDATE `$table` SET `nick`=:nick, `authid`=:authid, `immunity`=:immunity, `srv_group`=:srv_group, `srv_flags`=:srv_flags, `srv_password`=:srv_password, `gamecms`=:gamecms, `user_id`=:user_id WHERE `aid`=:id LIMIT 1");
							if ($STH->execute(array( ':nick' => $admin->nick, ':authid' => $admin->name, ':immunity' => $rights['immunity'], ':srv_group' => $rights['sb_group'], ':srv_flags' => $rights['flags'], ':srv_password' => $admin->pass, ':gamecms' => $admin_services, ':user_id' => $admin->user_id, ':id' => $admin_id )) != '1') {
								log_error('EXPORT_ADMINS: Не удалось обновить права в базе данных');
								return false;
							}
						}
					} else {
						$admin->user = $this->generate_user_str($admin->nick, $pdo2, $table);

						if($expired != 0) {
							$STH = $pdo2->prepare("INSERT INTO `$table` (`user`,`expired`,`nick`,`authid`,`password`,`gid`,`email`,`extraflags`,`immunity`,`srv_group`,`srv_flags`,`srv_password`,`gamecms`,`user_id`) values (:user, :expired, :nick, :authid, :password, :gid, :email, :extraflags, :immunity, :srv_group, :srv_flags, :srv_password, :gamecms, :user_id)");
							if ($STH->execute(array( ':user' => $admin->user, ':expired' => $rights['expired'], ':nick' => $admin->nick, ':authid' => $admin->name, ':password' => $admin->pass_md5, ':gid' => '-1', ':email' => $admin->email, ':extraflags' => '0', ':immunity' => $rights['immunity'], ':srv_group' => $rights['sb_group'], ':srv_flags' => $rights['flags'], ':srv_password' => $admin->pass, ':gamecms' => $admin_services, ':user_id' => $admin->user_id )) != '1') {
								log_error('EXPORT_ADMINS: Не удалось записать права в базу данных');
								return false;
							}
						} else {
							$STH = $pdo2->prepare("INSERT INTO `$table` (`user`,`nick`,`authid`,`password`,`gid`,`email`,`extraflags`,`immunity`,`srv_group`,`srv_flags`,`srv_password`,`gamecms`,`user_id`) values (:user, :nick, :authid, :password, :gid, :email, :extraflags, :immunity, :srv_group, :srv_flags, :srv_password, :gamecms, :user_id)");
							if ($STH->execute(array( ':user' => $admin->user, ':nick' => $admin->nick, ':authid' => $admin->name, ':password' => $admin->pass_md5, ':gid' => '-1', ':email' => $admin->email, ':extraflags' => '0', ':immunity' => $rights['immunity'], ':srv_group' => $rights['sb_group'], ':srv_flags' => $rights['flags'], ':srv_password' => $admin->pass, ':gamecms' => $admin_services, ':user_id' => $admin->user_id )) != '1') {
								log_error('EXPORT_ADMINS: Не удалось записать права в базу данных');
								return false;
							}
						}

						if($admin->active != 2) {
							$table = set_prefix($server->db_prefix, "servers");
							$STH = $pdo2->prepare("SELECT `sid` FROM `$table` WHERE `ip`=:ip AND `port`=:port LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
							$STH->execute(array( ':ip' => $server->ip, ':port' => $server->port ));
							$servers = $STH->fetch();

							$admin_id = get_ai($pdo2, set_prefix($server->db_prefix, "admins"), 'aid') - 1;
							$table = set_prefix($server->db_prefix, "admins_servers_groups");
							$STH = $pdo2->prepare("INSERT INTO `$table` (`admin_id`,`server_id`,`group_id`,`srv_group_id`) values (:admin_id, :server_id, :group_id, :srv_group_id)");  
							if ($STH->execute(array( ':admin_id' => $admin_id, ':server_id' => $servers->sid, 'group_id' => '0', 'srv_group_id' => '-1' )) != '1') {
								log_error($title.': Не удалось записать права в базу данных');
								return false;
							}
						}
					}
				}
			}

			try {
				(new OurSourceQuery())->reloadAdmins($server->id);
			} catch(Exception $e) {
				log_error($e->getMessage());
			}

			$this->set_admin_dell_time($pdo);

			return true;
		}
	}

	public function export_admins($server, $pdo) {
		$STH = $pdo->query("SELECT `id`,`type`,`db_host`,`db_db`,`db_user`,`db_pass`,`db_code`,`db_prefix`,`ip`,`port` FROM `servers` WHERE `id`='$server' LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$server = $STH->fetch();

		if($server->type == 0) {
			return false;
		} elseif($server->type == 1 || $server->type == 3){
			if(!$this->export_to_users_ini($pdo, $server->id, 'EXPORT_ADMINS')) {
				return false;
			}
		} else {
			if ($server->type == 2 || $server->type == 4){
				if(!$pdo2 = db_connect($server->db_host, $server->db_db, $server->db_user, $server->db_pass)) {
					return false;
				}
				set_names($pdo2, $server->db_code);

				if ($server->type == 4){
					$expired = $this->check_to_expired($pdo2, set_prefix($server->db_prefix, "admins"));
				}
				$STH2 = $pdo->prepare("SELECT `admins`.*, `users`.`email`, `users`.`nick` FROM `admins` LEFT JOIN `users` ON `admins`.`user_id` = `users`.`id` WHERE `admins`.`server`=:server AND `admins`.`active`!='2'"); $STH2->setFetchMode(PDO::FETCH_OBJ);
				$STH2->execute(array( ':server' => $server->id ));
				while($admin = $STH2->fetch()) { 
					$admin_services = $this->admin_services('none', $server->id, $pdo, $admin->id);
					$rights = $this->get_rights($admin_services);
					$rights['flags'] = $this->collect_rights($rights['flags']);
					$rights['expired'] = $this->collect_expired($admin_services);
					if ($server->type == 4){
						$rights['immunity'] = $this->collect_immunity($admin_services);
					}
					$admin_services = $this->serialize_info($admin_services);
					$admin->name = htmlspecialchars_decode($admin->name, ENT_QUOTES);

					if ($server->type == 2){
						$admin_id = $this->get_admin_id($admin->name, 1, $pdo2, $server->db_prefix, $server->ip, $server->port);

						if(!empty($admin->pass_md5)) {
							$admin->pass_md5 = $admin->pass_md5;
						} else {
							$admin->pass_md5 = null;
						}

						$table = set_prefix($server->db_prefix, "amxadmins");
						if(!empty($admin_id)) {
							$STH = $pdo2->prepare("UPDATE `$table` SET `username`=:username, `nickname`=:nickname, `password`=:password, `access`=:access, `flags`=:flags, `steamid`=:steamid, `created`=:created, `expired`=:expired, `days`=:days, `gamecms`=:gamecms WHERE `id`=:id LIMIT 1");
							if ($STH->execute(array( ':username' => $admin->user_id, ':nickname' => $admin->name, ':password' => $admin->pass_md5, ':access' => $rights['flags'], ':flags' => $admin->type, ':steamid' => $admin->name, ':created' => '0', ':expired' => $rights['expired'], ':days' => '0', ':gamecms' => $admin_services, ':id' => $admin_id )) != '1') {
								log_error('EXPORT_ADMINS: Не удалось обновить права в базе данных');
								return false;
							}
						} else {
							$STH = $pdo2->prepare("INSERT INTO `$table` (`username`,`nickname`,`password`,`access`,`flags`,`steamid`,`created`,`expired`,`days`,`gamecms`) values (:username, :nickname, :password, :access, :flags, :steamid, :created, :expired, :days, :gamecms)");  
							if ($STH->execute(array( ':username' => $admin->user_id, ':nickname' => $admin->name, ':password' => $admin->pass_md5, ':access' => $rights['flags'], ':flags' => $admin->type, ':steamid' => $admin->name, ':created' => '0', ':expired' => $rights['expired'], ':days' => '0', ':gamecms' => $admin_services )) != '1') {
								log_error('EXPORT_ADMINS: Не удалось записать права в базу данных');
								return false;
							}
							if($admin->active != 2) {
								$table = set_prefix($server->db_prefix, "serverinfo");
								$STH = $pdo2->prepare("SELECT `id` FROM `$table` WHERE `address`=:address LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
								$STH->execute(array( ':address' => $server->ip.':'.$server->port ));
								$serverinfo = $STH->fetch();
								$admin_id = get_ai($pdo2, set_prefix($server->db_prefix, "amxadmins")) - 1;
								$table = set_prefix($server->db_prefix, "admins_servers");
								$STH = $pdo2->prepare("INSERT INTO `$table` (`admin_id`,`server_id`,`use_static_bantime`,`custom_flags`) values (:admin_id, :server_id, :use_static_bantime, :custom_flags)");  
								if ($STH->execute(array( ':admin_id' => $admin_id, ':server_id' => $serverinfo->id, ':use_static_bantime' => 'no', 'custom_flags' => '' )) != '1') {
									log_error('EXPORT_ADMINS: Не удалось записать права в базу данных');
									return false;
								}
							}
						}
					}
					if ($server->type == 4){
						$admin_id = $this->get_admin_id($admin->name, 2, $pdo2, $server->db_prefix, $server->ip, $server->port);

						if($rights['sb_group'] == '') {
							$rights['sb_group'] = NULL;
						}
						if($rights['flags'] == '') {
							$rights['flags'] = NULL;
						}
						if(empty($admin->pass) || $admin->type == 'ce') {
							$admin->pass_md5 = md5(rand());
							$admin->pass = NULL;
						}
						if(empty($admin->email)) {
							$admin->email = '';
						}

						if(empty($admin->nick)) {
							log_error('EXPORT_ADMINS: У пользователя с ID:'.$admin->user_id.' не заполнено поле "ник" в профиле');
							$admin->nick = 'unnamed';
						}

						$table = set_prefix($server->db_prefix, "admins");
						if(!empty($admin_id)) {
							if($expired != 0) {
								$STH = $pdo2->prepare("UPDATE `$table` SET `nick`=:nick, `expired`=:expired, `authid`=:authid, `immunity`=:immunity, `srv_group`=:srv_group, `srv_flags`=:srv_flags, `srv_password`=:srv_password, `gamecms`=:gamecms, `user_id`=:user_id WHERE `aid`=:id LIMIT 1");
								if ($STH->execute(array( ':nick' => $admin->nick, ':expired' => $rights['expired'], ':authid' => $admin->name, ':immunity' => $rights['immunity'], ':srv_group' => $rights['sb_group'], ':srv_flags' => $rights['flags'], ':srv_password' => $admin->pass, ':gamecms' => $admin_services, ':user_id' => $admin->user_id, ':id' => $admin_id )) != '1') {
									log_error('EXPORT_ADMINS: Не удалось обновить права в базе данных');
									return false;
								}
							} else {
								$STH = $pdo2->prepare("UPDATE `$table` SET `nick`=:nick, `authid`=:authid, `immunity`=:immunity, `srv_group`=:srv_group, `srv_flags`=:srv_flags, `srv_password`=:srv_password, `gamecms`=:gamecms, `user_id`=:user_id WHERE `aid`=:id LIMIT 1");
								if ($STH->execute(array( ':nick' => $admin->nick, ':authid' => $admin->name, ':immunity' => $rights['immunity'], ':srv_group' => $rights['sb_group'], ':srv_flags' => $rights['flags'], ':srv_password' => $admin->pass, ':gamecms' => $admin_services, ':user_id' => $admin->user_id, ':id' => $admin_id )) != '1') {
									log_error('EXPORT_ADMINS: Не удалось обновить права в базе данных');
									return false;
								}
							}
						} else {
							$admin->user = $this->generate_user_str($admin->nick, $pdo2, $table);

							if($expired != 0) {
								$STH = $pdo2->prepare("INSERT INTO `$table` (`user`,`expired`,`nick`,`authid`,`password`,`gid`,`email`,`extraflags`,`immunity`,`srv_group`,`srv_flags`,`srv_password`,`gamecms`,`user_id`) values (:user, :expired, :nick, :authid, :password, :gid, :email, :extraflags, :immunity, :srv_group, :srv_flags, :srv_password, :gamecms, :user_id)");
								if ($STH->execute(array( ':user' => $admin->user, ':expired' => $rights['expired'], ':nick' => $admin->nick, ':authid' => $admin->name, ':password' => $admin->pass_md5, ':gid' => '-1', ':email' => $admin->email, ':extraflags' => '0', ':immunity' => $rights['immunity'], ':srv_group' => $rights['sb_group'], ':srv_flags' => $rights['flags'], ':srv_password' => $admin->pass, ':gamecms' => $admin_services, ':user_id' => $admin->user_id )) != '1') {
									log_error('EXPORT_ADMINS: Не удалось записать права в базу данных');
									return false;
								}
							} else {
								$STH = $pdo2->prepare("INSERT INTO `$table` (`user`,`nick`,`authid`,`password`,`gid`,`email`,`extraflags`,`immunity`,`srv_group`,`srv_flags`,`srv_password`,`gamecms`,`user_id`) values (:user, :nick, :authid, :password, :gid, :email, :extraflags, :immunity, :srv_group, :srv_flags, :srv_password, :gamecms, :user_id)");
								if ($STH->execute(array( ':user' => $admin->user, ':nick' => $admin->nick, ':authid' => $admin->name, ':password' => $admin->pass_md5, ':gid' => '-1', ':email' => $admin->email, ':extraflags' => '0', ':immunity' => $rights['immunity'], ':srv_group' => $rights['sb_group'], ':srv_flags' => $rights['flags'], ':srv_password' => $admin->pass, ':gamecms' => $admin_services, ':user_id' => $admin->user_id )) != '1') {
									log_error('EXPORT_ADMINS: Не удалось записать права в базу данных');
									return false;
								}
							}

							if($admin->active != 2) {
								$table = set_prefix($server->db_prefix, "servers");
								$STH = $pdo2->prepare("SELECT `sid` FROM `$table` WHERE `ip`=:ip AND `port`=:port LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
								$STH->execute(array( ':ip' => $server->ip, ':port' => $server->port ));
								$servers = $STH->fetch();

								$admin_id = get_ai($pdo2, set_prefix($server->db_prefix, "admins"), 'aid') - 1;
								$table = set_prefix($server->db_prefix, "admins_servers_groups");
								$STH = $pdo2->prepare("INSERT INTO `$table` (`admin_id`,`server_id`,`group_id`,`srv_group_id`) values (:admin_id, :server_id, :group_id, :srv_group_id)");  
								if ($STH->execute(array( ':admin_id' => $admin_id, ':server_id' => $servers->sid, 'group_id' => '0', 'srv_group_id' => '-1' )) != '1') {
									log_error('EXPORT_ADMINS: Не удалось записать права в базу данных');
									return false;
								}
							}
						}
					}
				}
			}

			try {
				(new OurSourceQuery())->reloadAdmins($server->id);
			} catch(Exception $e) {
				log_error($e->getMessage());
			}

			$this->set_admin_dell_time($pdo);
		}

		return true;
	}

	public function check_for_bad_nicks($pdo, $nick) {
		$STH = $pdo->prepare("SELECT `bad_nicks_act` FROM `config__secondary` LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute();
		$row = $STH->fetch();
		if($row->bad_nicks_act == 1) {
			$STH = $pdo->prepare("SELECT `data` FROM `config__strings` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array( ':id' => '1' ));
			$row = $STH->fetch();

			if(!empty($row->data)) {
				$data = explode(";sp;", $row->data);
			}

			$data_count = count($data);
			for ($i = 0; $i < $data_count; $i++) {
				if(substr_count($data[$i], '{%}') >= 1) {
					$metasymbols = array("."=>"\.","^"=>"\^","*"=>"\*","+"=>"\+","?"=>"\?","{"=>"\{","["=>"\[","]"=>"\]","|"=>"\|","("=>"\(",")"=>"\)","}"=>"\}","\\"=>"\\\\");
					if(substr_count($data[$i], '{%}', 0, 3) == 1 && substr_count($data[$i], '{%}', -3) == 1) {  // {%}bad nick{%}
						$data[$i] = substr($data[$i], 3);
						$data[$i] = substr($data[$i], 0, -3);
						if(preg_match('/.*'.strtr($data[$i], $metasymbols).'.*/i', $nick)) {
							return false;
						}
					} elseif(substr_count($data[$i], '{%}', 0, 3)) { // {%}bad nick
						$data[$i] = substr($data[$i], 3);
						if(preg_match('/.*'.strtr($data[$i], $metasymbols).'$/i', $nick)) {
							return false;
						}
					} else { // bad nick{%}
						$data[$i] = substr($data[$i], 0, -3);
						if(preg_match('/^'.strtr($data[$i], $metasymbols).'.*/i', $nick)) {
							return false;
						}
					}
				} else {
					if(strnatcasecmp($data[$i], $nick) == 0) {
						return false;
					}
				}
			}
		}

		return true;
	}
}
