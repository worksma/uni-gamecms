<?php

class GetData {
	private $pdo;
	private $tpl;

	function __construct($pdo, $tpl = null) {
		if(!isset($pdo)) {
			return '[Class GetData]: No connection to the database';
		}
		if(isset($tpl)) {
			$this->tpl = $tpl;
		}
		$this->pdo = $pdo;
	}

	public function get_gamer_profile($nick, $steam_id, $type = 1) {
		global $users_groups;
		$nick = check($nick, null);
		$nick = str_replace(array('/', '\\'), '', $nick);

		if($type == 1) {
			$STH = $this->pdo->prepare("SELECT `users`.`id`, `users`.`login`, `users`.`avatar`, `users`.`rights` FROM `admins` LEFT JOIN `users` ON `user_id`=`users`.`id` WHERE `admins`.`name`=:nick OR `admins`.`name`=:steam_id LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':nick' => $nick, ':steam_id' => $steam_id));
			$row = $STH->fetch();
		}
		if(empty($row->id) || $type == 2) {
			if(empty($steam_id)) {
				$STH = $this->pdo->prepare("SELECT `id`, `login`, `avatar`, `rights` FROM `users` WHERE `nick`=:nick LIMIT 1");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array(':nick' => $nick));
				$row = $STH->fetch();
			} else {
				$STH = $this->pdo->prepare("SELECT `id`, `login`, `avatar`, `rights` FROM `users` WHERE `nick`=:nick OR `steam_id`=:steam_id LIMIT 1");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array(':nick' => $nick, ':steam_id' => $steam_id));
				$row = $STH->fetch();
			}
		}

		if(isset($row->id)) {
			if(SteamIDOperations::ValidateSteamID($nick)) {
				$nick = $row->login;
			}

			return '<a target="_blank" href="../profile?id='.$row->id.'" title="'.$users_groups[$row->rights]['name'].'"><img src="../'.$row->avatar.'" alt="'.$row->login.'" class="small_us_av"> <i style="color: '.$users_groups[$row->rights]['color'].'">'.$nick.'</a></i>';
		} else {
			return 0;
		}
	}

	private function get_profile_by_id($id) {
		global $users_groups;

		$STH = $this->pdo->prepare("SELECT `id`, `login`, `avatar`, `rights` FROM `users` WHERE `id`=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':id' => $id));
		$row = $STH->fetch();

		if(isset($row->id)) {
			return '<a target="_blank" href="../profile?id='.$row->id.'" title="'.$users_groups[$row->rights]['name'].'"><img src="../'.$row->avatar.'" alt="'.$row->login.'" class="small_us_av"> <i style="color: '.$users_groups[$row->rights]['color'].'">'.$row->login.'</a></i>';
		} else {
			return 0;
		}
	}

	private function get_stats_sort($type, $sort) {
		if($sort == 0) {
			if($type == 1 or $type == 2) {
				$sort_name = 'frags - deaths - teamkills DESC';
			} elseif($type == 3) {
				$sort_name = 'kills - deaths - tks DESC';
			} elseif($type == 4) {
				$sort_name = 'CAST(CAST(kills - deaths - teamkills AS UNSIGNED) AS SIGNED) DESC';
			} elseif($type == 5) {
				$sort_name = 'kills - deaths - tk DESC';
			}/* elseif($type == 6) {
				$sort_name = 'kills - deaths DESC';
			}*/
		} elseif($sort == 1) {
			if($type == 1 or $type == 2) {
				$sort_name = 'frags DESC';
			} elseif($type == 3) {
				$sort_name = 'kills DESC';
			} elseif($type == 4) {
				$sort_name = 'kills DESC';
			} elseif($type == 5) {
				$sort_name = 'kills DESC';
			}/* elseif($type == 6) {
				$sort_name = 'kills DESC';
			}*/
		} elseif($sort == 2) {
			if($type == 1 or $type == 2) {
				$sort_name = 'frags + headshots DESC';
			} elseif($type == 3) {
				$sort_name = 'kills + hs DESC';
			} elseif($type == 4) {
				$sort_name = 'kills + headshots DESC';
			} elseif($type == 5) {
				$sort_name = 'kills + headshots DESC';
			}/* elseif($type == 6) {
				$sort_name = 'kills + headshots DESC';
			}*/
		} elseif($sort == 3) {
			if($type == 1 or $type == 2) {
				$sort_name = 'skill DESC';
			} elseif($type == 3) {
				$sort_name = 'skill DESC';
			} elseif($type == 4) {
				$sort_name = 'skill DESC';
			} elseif($type == 5) {
				$sort_name = 'score DESC';
			}
		} elseif($sort == 4) {
			if($type == 1 or $type == 2) {
				$sort_name = 'gametime DESC';
			} elseif($type == 3) {
				$sort_name = 'connection_time DESC';
			} elseif($type == 4) {
				$sort_name = 'connection_time DESC';
			} elseif($type == 5) {
				$sort_name = 'connected DESC';
			}
		} elseif($sort == 5) {
			if($type == 1 or $type == 2) {
				$sort_name = 'place';
			}
		} elseif($sort == 6) {
			if($type == 1 or $type == 2) {
				$sort_name = 'frags - deaths + headshots - teamkills * 2 -suicide * 3 + defused * 3 + explode * 3 DESC';
			}
		} elseif($sort == 7) {
			if($type == 6) {
				$sort_name = 'rank DESC';
			}
		} elseif($sort == 8) {
			if($type == 6) {
				$sort_name = 'value DESC';
			}
		}
		return $sort_name;
	}

	private function lvl_rank_($value) {
		if(strripos($value, ';') === false) {
			return $value;
		} else {
			$value = explode(';', $value);
			return trim($value[0]);
		}
	}

	private function get_stats_where($type, $sort, $frags = null, $deaths = null, $teamkills = null, $headshots = null, $skill = null, $gametime = null, $suicide = null, $defused = null, $explode = null, $place = null, $rank = null, $value = null) {
		if($sort == 0) {
			$temp = $frags - $deaths - $teamkills;
			if($type == 1 or $type == 2) {
				$where = '(frags - deaths - teamkills) > '.$temp;
			} elseif($type == 3) {
				$where = '(kills - deaths - tks) > '.$temp;
			} elseif($type == 4) {
				$where = '(CAST(CAST(kills - deaths - teamkills AS UNSIGNED) AS SIGNED)) > '.$temp;
			} elseif($type == 5) {
				$where = '(kills - deaths - tk) > '.$temp;
			}/* elseif($type == 6) {
				$where = '(kills - deaths) > '.$temp;
			}*/
		} elseif($sort == 1) {
			if($type == 1 or $type == 2) {
				$where = 'frags > '.$frags;
			} elseif($type == 3) {
				$where = 'kills > '.$frags;
			} elseif($type == 4) {
				$where = 'kills > '.$frags;
			} elseif($type == 5) {
				$where = 'kills > '.$frags;
			}/* elseif($type == 6) {
				$where = 'kills > '.$frags;
			}*/
		} elseif($sort == 2) {
			$temp = $frags + $headshots;
			if($type == 1 or $type == 2) {
				$where = '(frags + headshots) > '.$temp;
			} elseif($type == 3) {
				$where = '(kills + hs) > '.$temp;
			} elseif($type == 4) {
				$where = '(kills + headshots) > '.$temp;
			} elseif($type == 5) {
				$where = '(kills + headshots) > '.$temp;
			}/* elseif($type == 6) {
				$where = '(kills + headshots) > '.$temp;
			}*/
		} elseif($sort == 3) {
			if($type == 1 or $type == 2) {
				$where = 'skill > '.$skill;
			} elseif($type == 3) {
				$where = 'skill > '.$skill;
			} elseif($type == 4) {
				$where = 'skill > '.$skill;
			} elseif($type == 5) {
				$where = 'score > '.$skill;
			}
		} elseif($sort == 4) {
			if($type == 1 or $type == 2) {
				$where = 'gametime > '.$gametime;
			} elseif($type == 3) {
				$where = 'connection_time > '.$gametime;
			} elseif($type == 4) {
				$where = 'connection_time > '.$gametime;
			} elseif($type == 5) {
				$where = 'connected > '.$gametime;
			}
		} elseif($sort == 5) {
			if($type == 1 or $type == 2) {
				$where = 'place > '.$place;
			}
		} elseif($sort == 6) {
			$temp = $frags - $deaths + $headshots - $teamkills * 2 - $suicide * 3 + $defused * 3 + $explode * 3;
			if($type == 1 or $type == 2) {
				$where = '(frags - deaths + headshots - teamkills * 2 -suicide * 3 + defused * 3 + explode * 3) > '.$temp;
			}
		} elseif($sort == 7) {
			if($type == 6) {
				$where = 'rank > '.$rank;
			}
		} elseif($sort == 8) {
			if($type == 6) {
				$where = 'value > '.$value;
			}
		}
		return $where;
	}

	public function banlist($start, $server, $limit = 30, $name = null) {
		$start = checkStart($start);
		$server = check($server, "int");
		$name = check($name, null);

		global $messages;
		global $users_groups;

		if(empty($server)) {
			return '<tr><td colspan="10">Ошибка: [Неизвестные переменные]</td></tr>';
		}
		if((empty($start) and $start != "0")) {
			return '<tr><td colspan="10">Ошибка: [Неизвестные переменные]</td></tr>';
		}

		$STH = $this->pdo->query("SELECT price1, price2, price3 FROM config__prices LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$bans_conf = $STH->fetch();

		$STH = $this->pdo->query("SELECT id,ip,port,db_host,db_user,db_pass,db_db,db_prefix,type,db_code,name FROM servers WHERE type!=0 and type!=1 and id='$server' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();

		$db_host = $row->db_host;
		$db_user = $row->db_user;
		$db_pass = $row->db_pass;
		$db_db = $row->db_db;
		$db_prefix = $row->db_prefix;
		$address = $row->ip.':'.$row->port;
		$ip = $row->ip;
		$port = $row->port;
		$server_name = $row->name;
		$type = $row->type;
		if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
			return '<tr><td colspan="10">'.$messages['errorConnectingToDatabase'].'</td></tr>';
		}
		set_names($pdo2, $row->db_code);

		if(empty($name)) {
			if($type == '2' || $type == '3' || $type == '5') {
				$table = set_prefix($db_prefix, 'bans');
				$STH = $pdo2->query("SELECT * FROM $table WHERE server_ip = '$address' ORDER BY bid DESC LIMIT $start, $limit");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			} else {
				$table = set_prefix($db_prefix, 'servers');
				$STH = $pdo2->query("SELECT sid FROM $table WHERE ip='$ip' and port='$port' LIMIT 1");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$row = $STH->fetch();
				$sid = $row->sid;
				$table1 = set_prefix($db_prefix, 'bans');
				$table2 = set_prefix($db_prefix, 'admins');
				$STH = $pdo2->query("SELECT $table1.bid,$table1.unban_type,$table1.ban_closed,$table1.ip AS player_ip,$table1.RemoveType AS expired,$table1.authid AS player_id,$table1.name AS player_nick,$table1.created AS ban_created,$table1.length AS ban_length,$table1.reason AS ban_reason,$table1.adminip AS admin_ip,$table2.user AS admin_nick,$table2.nick AS admin_nick2,$table2.authid AS admin_id FROM $table1 LEFT JOIN $table2 ON $table1.aid = $table2.aid WHERE ($table1.sid = '$sid' or $table1.sid = '0') ORDER BY $table1.bid DESC LIMIT $start, $limit");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			}
		} else {
			if($type == '2' || $type == '3' || $type == '5') {
				$table = set_prefix($db_prefix, 'bans');
				$STH = $pdo2->prepare("SELECT * FROM $table WHERE server_ip = '$address' and (player_ip LIKE :name or player_nick LIKE :name or player_id LIKE :name or bid = :bid) ORDER BY bid DESC");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array(":name" => getNameLike($name), ":bid" => $name));
			} else {
				$table = set_prefix($db_prefix, 'servers');
				$STH = $pdo2->query("SELECT sid FROM $table WHERE ip='$ip' and port='$port' LIMIT 1");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$row = $STH->fetch();
				$sid = $row->sid;
				$table1 = set_prefix($db_prefix, 'bans');
				$table2 = set_prefix($db_prefix, 'admins');
				$STH = $pdo2->prepare("SELECT $table1.bid,$table1.unban_type,$table1.ban_closed,$table1.ip AS player_ip,$table1.RemoveType AS expired,$table1.authid AS player_id,$table1.name AS player_nick,$table1.created AS ban_created,$table1.length AS ban_length,$table1.reason AS ban_reason,$table1.adminip AS admin_ip,$table2.user AS admin_nick,$table2.nick AS admin_nick2,$table2.authid AS admin_id FROM $table1 LEFT JOIN $table2 ON $table1.aid = $table2.aid WHERE ($table1.sid = '$sid' or $table1.sid = '0') and ($table1.ip LIKE :name or $table1.authid LIKE :name or $table1.name LIKE :name or $table1.bid = :bid) ORDER BY $table1.bid DESC");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array(":name" => getNameLike($name), ":bid" => $name));
			}
		}

		$i = 0;
		$this->tpl->result['local_content'] = '';

		while($row = $STH->fetch()) {
			$i++;
			$ban_length = $row->ban_length;
			if($type == '2' || $type == '3' || $type == '5') {
				$ban_length = $ban_length * 60;
			}
			$disp = "";
			$disp2 = "";
			$price = 0;

			$ban_created = $row->ban_created;
			$now = time();
			$temp_time = date("Y-m-d H:i:s", ($ban_created + $ban_length));
			if($row->unban_type == 1 || $row->expired == "E") {
				$class = "success";
				$disp = "disp-n";
				$time = 'Срок истек';
			} elseif(
				$row->unban_type == '-1'
				|| ($row->unban_type == '-1' && $row->expired == "U")
				|| $row->expired == 1
			) {
				$class = "success";
				$disp = "disp-n";
				$disp2 = $disp;
				$time = 'Разбанен';
			} elseif($row->unban_type == '-2' || ($row->unban_type == '-2' && $row->expired == "U")) {
				$class = "success";
				$disp = "disp-n";
				$disp2 = $disp;
				$time = 'Куплен разбан';
			} else {
				if($ban_length == 0) {
					$time = 'Никогда';
					$class = "danger";
					$price = $bans_conf->price3;
				} else {
					$time = expand_date($temp_time, 1);
					if(($ban_created + $ban_length) < $now) {
						$class = "success";
						$disp = "disp-n";
					} else {
						$class = "";
						$date = diff_date($temp_time, date("Y-m-d H:i:s"));
						if($date['2'] < '7' and $date['1'] == '0' and $date['0'] == '0') {
							$price = $bans_conf->price1;
						} else {
							$price = $bans_conf->price2;
						}
					}
				}
			}

			if(!is_worthy_specifically("s", $server)) {
				$disp2 = "disp-n";
			}
			if(empty($_SESSION['id']) or ($price == '0' and $disp2 == 'disp-n')) {
				$disp = "disp-n";
			}

			if(!isset($row->admin_nick2)) {
				$row->admin_nick2 = null;
			}

			$admin_nick = get_ban_admin_nick($row->admin_nick, $row->admin_nick2, $server_name, $type);

			if($admin_profile = $this->get_gamer_profile($admin_nick, $row->admin_id)) {
				$admin_nick = $admin_profile;
			}

			if(!empty($row->ban_closed) && ($user_profile = $this->get_profile_by_id($row->ban_closed))) {
				$ban_closed = $user_profile;
			} else {
				$ban_closed = '';
			}

			$this->tpl->load_template('elements/ban.tpl');
			$this->tpl->set("{class}", $class);
			$this->tpl->set("{bid}", $row->bid);
			$this->tpl->set("{player_nick}", check($row->player_nick, null));
			$this->tpl->set("{ban_reason}", check($row->ban_reason, null));
			$this->tpl->set("{server}", $server);
			$this->tpl->set("{time}", $time);
			$this->tpl->set("{disp}", $disp);
			$this->tpl->set("{price}", $price);
			$this->tpl->set("{address}", $address);
			$this->tpl->set("{admin_nick}", $admin_nick);
			$this->tpl->set("{player_ip}", $row->player_ip);
			$this->tpl->set("{player_id}", isNeedHidePlayerId() ? hidePlayerId($row->player_id) : $row->player_id);
			$this->tpl->set("{ban_length}", expand_seconds2($ban_length));
			$this->tpl->set("{ban_created}", expand_date(date("Y-m-d H:i:s", $ban_created), 7));
			$this->tpl->set("{server_name}", $server_name);
			$this->tpl->set("{type}", $type);
			$this->tpl->set("{disp2}", $disp2);
			$this->tpl->set("{ban_closed}", $ban_closed);
			if($ban_length == 0) {
				$temp_time = '00.00.0000 00:00';
			} else {
				$temp_time = date('d.m.Y H:i', strtotime($temp_time));
			}
			$this->tpl->set("{ban_end}", $temp_time);
			$this->tpl->compile('local_content');
			$this->tpl->clear();
		}

		if(empty($this->tpl->result['local_content'])) {
			$this->tpl->result['local_content'] = '<tr><td colspan="10">Банов нет</td></tr>';
		}

		return $this->tpl->result['local_content'];
	}

	public function mutlist($start, $server, $limit = 30, $name = null) {
		$start = checkStart($start);
		$server = check($server, "int");
		$name = checkJs($name, null);

		global $messages;

		if(empty($server)) {
			return '<tr><td colspan="10">Ошибка: [Неизвестные переменные]</td></tr>';
		}
		if((empty($start) and $start != "0")) {
			return '<tr><td colspan="10">Ошибка: [Неизвестные переменные]</td></tr>';
		}

		$STH = $this->pdo->query("SELECT price2_1, price2_2, price2_3 FROM config__prices LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$bans_conf = $STH->fetch();

		$STH = $this->pdo->query("SELECT id,ip,port,db_host,db_user,db_pass,db_db,db_prefix,type,db_code,name FROM servers WHERE type!=0 and id='$server' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();

		$db_host = $row->db_host;
		$db_user = $row->db_user;
		$db_pass = $row->db_pass;
		$db_db = $row->db_db;
		$db_prefix = $row->db_prefix;
		$address = $row->ip.':'.$row->port;
		$ip = $row->ip;
		$port = $row->port;
		$server_name = $row->name;
		$type = $row->type;

		if($type == '1' || $type == '2' || $type == '3' || $type == '5') {
			if(check_table('comms', $this->pdo)) {
				$table1 = 'comms';
			} else {
				return '<tr><td colspan="10">'.$messages['Not_found_tables'].'</td></tr>';
			}

			$table2 = 'admins';
			if(empty($name)) {
				$STH = $this->pdo->query("SELECT $table1.bid,$table1.type, $table1.expired AS unban_type, $table1.modified_by AS ban_closed, $table1.authid AS player_id, $table1.name AS player_nick, $table1.created AS ban_created, $table1.length AS ban_length, $table1.reason AS ban_reason, $table1.admin_nick AS admin_nick, $table2.name AS admin_nick2 FROM $table1 LEFT JOIN $table2 ON $table1.admin_id = $table2.id 
				WHERE $table1.server_id = '$server' ORDER BY $table1.bid DESC LIMIT $start, $limit");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			} else {
				$STH = $this->pdo->prepare("SELECT $table1.bid,$table1.type, $table1.expired AS unban_type, $table1.modified_by AS ban_closed, $table1.authid AS player_id, $table1.name AS player_nick, $table1.created AS ban_created, $table1.length AS ban_length, $table1.reason AS ban_reason, $table1.admin_nick AS admin_nick, $table2.name AS admin_nick2 FROM $table1 LEFT JOIN $table2 ON $table1.admin_id = $table2.id 
				WHERE $table1.server_id = '$server' and ($table1.authid LIKE :name or $table1.name LIKE :name) ORDER BY $table1.bid DESC");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array(":name" => getNameLike($name)));
			}
		} elseif($type == '4') {
			if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
				return '<tr><td colspan="10">'.$messages['errorConnectingToDatabase'].'</td></tr>';
			}
			set_names($pdo2, $row->db_code);

			$table = set_prefix($db_prefix, 'servers');
			$STH = $pdo2->query("SELECT sid FROM $table WHERE ip='$ip' and port='$port' LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$row = $STH->fetch();
			$sid = $row->sid;

			$table = set_prefix($db_prefix, 'comms');
			if(check_table($table, $pdo2)) {
				$table = set_prefix($db_prefix, 'servers');
				$STH = $pdo2->query("SELECT sid FROM $table WHERE ip='$ip' and port='$port' LIMIT 1");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$row = $STH->fetch();
				$sid = $row->sid;
				$table1 = set_prefix($db_prefix, 'comms');
				$table2 = set_prefix($db_prefix, 'admins');
				if(empty($name)) {
					$STH = $pdo2->query("SELECT $table1.bid,$table1.unban_type,$table1.ban_closed,$table1.type,$table1.RemoveType AS expired,$table1.authid AS player_id,$table1.name AS player_nick,$table1.created AS ban_created,$table1.length AS ban_length,$table1.reason AS ban_reason,$table1.adminip AS admin_ip,$table2.user AS admin_nick,$table2.nick AS admin_nick2,$table2.authid AS admin_id FROM $table1 LEFT JOIN $table2 ON $table1.aid = $table2.aid WHERE ($table1.sid = '$sid' or $table1.sid = '0') ORDER BY $table1.bid DESC LIMIT $start, $limit");
					$STH->setFetchMode(PDO::FETCH_OBJ);
				} else {
					$STH = $pdo2->prepare("SELECT $table1.bid,$table1.unban_type,$table1.ban_closed,$table1.type,$table1.RemoveType AS expired,$table1.authid AS player_id,$table1.name AS player_nick,$table1.created AS ban_created,$table1.length AS ban_length,$table1.reason AS ban_reason,$table1.adminip AS admin_ip,$table2.user AS admin_nick,$table2.nick AS admin_nick2,$table2.authid AS admin_id FROM $table1 LEFT JOIN $table2 ON $table1.aid = $table2.aid WHERE ($table1.sid = '$sid' or $table1.sid = '0') and ($table1.authid LIKE :name or $table1.name LIKE :name) ORDER BY $table1.bid DESC");
					$STH->setFetchMode(PDO::FETCH_OBJ);
					$STH->execute(array(":name" => getNameLike($name)));
				}
			} else {
				return '<tr><td colspan="10">'.$messages['Not_found_tables'].'</td></tr>';
			}
		}

		$i = 0;
		$this->tpl->result['local_content'] = '';

		while($row = $STH->fetch()) {
			$i++;

			$ban_length = $row->ban_length;
			if($type == '1' || $type == '2' || $type == '3' || $type == '5') {
				$ban_length = $ban_length * 60;
			}
			$disp = "";
			$disp2 = "";
			$price = 0;

			$ban_length2 = expand_seconds2($ban_length);
			$ban_created = $row->ban_created;

			$now = time();
			$temp_time = date("Y-m-d H:i:s", ($ban_created + $ban_length));
			if($ban_length2 < 0) {
				$class = "success";
				$disp = "disp-n";
				$time = expand_date($temp_time, 1);
				$ban_length2 = "На сессию";
			} else {
				if(($row->unban_type == 1 and $ban_length > 0 and (($ban_created + $ban_length) < $now)) or (isset($row->expired) && $row->expired == "E")) {
					$class = "success";
					$disp = "disp-n";
					$time = 'Срок истек';
				} elseif($row->unban_type == '-1' || ($row->unban_type == '-1' && (isset($row->expired) && $row->expired == "U"))) {
					$class = "success";
					$disp = "disp-n";
					$disp2 = $disp;
					$time = 'Разбанен';
				} elseif($row->unban_type == '-2' || ($row->unban_type == '-2' && (isset($row->expired) && $row->expired == "U"))) {
					$class = "success";
					$disp = "disp-n";
					$disp2 = $disp;
					$time = 'Куплен разбан';
				} else {
					if($ban_length == 0) {
						$time = 'Никогда';
						$class = "danger";
						$price = $bans_conf->price2_3;
					} else {
						$time = expand_date($temp_time, 1);
						if(($ban_created + $ban_length) < $now) {
							$class = "success";
							$disp = "disp-n";
						} else {
							$class = "";
							$date = diff_date($temp_time, date("Y-m-d H:i:s"));
							if($date['2'] < '7' and $date['1'] == '0' and $date['0'] == '0') {
								$price = $bans_conf->price2_1;
							} else {
								$price = $bans_conf->price2_2;
							}
						}
					}
				}
			}

			if(!is_worthy_specifically("s", $server)) {
				$disp2 = "disp-n";
			}
			if(empty($_SESSION['id']) or ($price == '0' and $disp2 == 'disp-n')) {
				$disp = "disp-n";
			}

			$admin_nick = get_ban_admin_nick($row->admin_nick, $row->admin_nick2, $server_name, $type);
			if($type == '1' || $type == '2' || $type == '3' || $type == '5') {
				$admin_id = $admin_nick;
			} elseif($type == '4') {
				$admin_id = $row->admin_id;
			}

			if(!isset($row->admin_nick2)) {
				$row->admin_nick2 = null;
			}

			if($admin_profile = $this->get_gamer_profile($admin_nick, $admin_id)) {
				$admin_nick = $admin_profile;
			}

			if(!empty($row->ban_closed) && ($user_profile = $this->get_profile_by_id($row->ban_closed))) {
				$ban_closed = $user_profile;
			} else {
				$ban_closed = '';
			}

			$this->tpl->load_template('elements/mute.tpl');
			$this->tpl->set("{class}", $class);
			$this->tpl->set("{bid}", $row->bid);
			$this->tpl->set("{player_nick}", check($row->player_nick, null));
			$this->tpl->set("{ban_reason}", check($row->ban_reason, null));
			$this->tpl->set("{server}", $server);
			$this->tpl->set("{time}", $time);
			$this->tpl->set("{disp}", $disp);
			$this->tpl->set("{price}", $price);
			$this->tpl->set("{address}", $address);
			$this->tpl->set("{admin_nick}", $admin_nick);
			$this->tpl->set("{player_id}", isNeedHidePlayerId() ? hidePlayerId($row->player_id) : $row->player_id);
			$this->tpl->set("{ban_length}", $ban_length2);
			$this->tpl->set("{ban_created}", expand_date(date("Y-m-d H:i:s", $ban_created), 7));
			$this->tpl->set("{type}", $row->type);
			$this->tpl->set("{disp2}", $disp2);
			$this->tpl->set("{ban_closed}", $ban_closed);
			if($ban_length == 0) {
				$temp_time = '00.00.0000 00:00';
			} else {
				$temp_time = date('d.m.Y H:i', strtotime($temp_time));
			}
			$this->tpl->set("{ban_end}", $temp_time);
			$this->tpl->compile('local_content');
			$this->tpl->clear();
		}

		if(empty($this->tpl->result['local_content'])) {
			return '<tr><td colspan="10">Мутов нет</td></tr>';
		} else {
			return $this->tpl->result['local_content'];
		}
	}

	public function stats($start, $server, $limit = 30, $name = null, $by_steam_id = null) {
		$start = checkStart($start);
		$server = check($server, "int");
		$name = checkJs($name, null);

		if(empty($server)) {
			return '<tr><td colspan="10">'.$messages['errorConnectingToDatabase'].'</td></tr>';
		}

		if((empty($start) and $start != "0")) {
			return '<tr><td colspan="10">Нет результатов</td></tr>';
		}

		$STH = $this->pdo->query("SELECT id,st_db_host,st_db_user,st_db_pass,st_db_db,st_type,st_db_code,st_sort_type,st_db_table,ip,port FROM servers WHERE st_type!=0 and id='$server' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();

		$db_host = $row->st_db_host;
		$db_user = $row->st_db_user;
		$db_pass = $row->st_db_pass;
		$db_db = $row->st_db_db;
		$type = $row->st_type;
		$sort_type = $row->st_sort_type;
		$table = $row->st_db_table;
		$ip = $row->ip;
		$port = $row->port;
		if(!$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
			return '<tr><td colspan="10">'.$messages['errorConnectingToDatabase'].'</td></tr>';
		}
		set_names($pdo2, $row->st_db_code);

		$STH = $this->pdo->prepare("SELECT `id` FROM `modules` WHERE `name`=:name AND `active`='1' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':name' => 'reset_stats'));
		$temp = $STH->fetch();
		if(isset($temp->id)) {
			$reset_stats['trigger'] = 1;
			$STH = $this->pdo->query("SELECT `reset_stats_price` FROM `config__prices` LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$temp = $STH->fetch();
			$reset_stats['price'] = $temp->reset_stats_price;
		} else {
			$reset_stats['trigger'] = 0;
			$reset_stats['price'] = 0;
		}

		if($type == 1) {
			$STH = $pdo2->query("SELECT value,command FROM csstats_settings WHERE command='statsx_skill' LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$row = $STH->fetch();
			if(isset($row->command)) {
				$skills = explode(" ", $row->value);
			}
		}
		if($type == 2) {
			$STH = $pdo2->query("SELECT value,command FROM csstats_settings WHERE command='ar_levels' or command='statsx_skill' or command='ar_xp_value' or command='ar_xp_c4def' or command='ar_bonus_he' or command='ar_bonus_flash' or command='ar_bonus_smoke' or command='ar_bonus_defuse' or command='ar_bonus_nv' or command='ar_bonus_armor' or command='ar_bonus_hp' or command='ar_bonus_flags' or command='ar_bonus_damage' or command='ar_xp_hs' or command='army_enable'");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			while($row = $STH->fetch()) {
				if($row->command == 'ar_levels') {
					$levels = explode(" ", $row->value);
				} elseif($row->command == 'ar_xp_value') {
					$cv_XPvalue = $row->value;
				} elseif($row->command == 'ar_xp_c4def') {
					$cv_XPc4def = $row->value;
				} elseif($row->command == 'ar_xp_hs') {
					$cv_XPheadshot = $row->value;
				} elseif($row->command == 'statsx_skill') {
					$skills = explode(" ", $row->value);
				} elseif($row->command == 'ar_bonus_he') {
					$ar_bonus_hes = explode(" ", $row->value);
				} elseif($row->command == 'ar_bonus_flash') {
					$ar_bonus_flashs = explode(" ", $row->value);
				} elseif($row->command == 'ar_bonus_smoke') {
					$ar_bonus_smokes = explode(" ", $row->value);
				} elseif($row->command == 'ar_bonus_defuse') {
					$ar_bonus_defuses = explode(" ", $row->value);
				} elseif($row->command == 'ar_bonus_nv') {
					$ar_bonus_nvs = explode(" ", $row->value);
				} elseif($row->command == 'ar_bonus_armor') {
					$ar_bonus_armors = explode(" ", $row->value);
				} elseif($row->command == 'ar_bonus_hp') {
					$ar_bonus_hps = explode(" ", $row->value);
				} elseif($row->command == 'ar_bonus_flags') {
					$ar_bonus_flagss = explode(" ", $row->value);
				} elseif($row->command == 'ar_bonus_damage') {
					$ar_bonus_damages = explode(" ", $row->value);
				} elseif($row->command == 'army_enable') {
					$army_enable = $row->value;
				}
			}

			$val_name = "level_name_";
			$STH = $pdo2->prepare("SELECT * FROM csstats_settings WHERE command LIKE :name");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(":name" => "%".$val_name."%"));
			while($row = $STH->fetch()) {
				$i = str_replace("level_name_", "", $row->command);
				$i--;
				$ranks[$i] = $row->value;
			}
		}
		if($type == 1 || $type == 2 || $type == 3) {
			if(empty($skills[1])) {
				$skills = [0, 60, 75, 85, 100, 115, 130, 140, 150, 165, 180, 195, 210];
			}
			$skills_names  = ["L-", "L", "L+", "M-", "M", "M+", "H-", "H", "H+", "P-", "P", "P+", "G"];
			$skills_colors = [
				"default",
				"default",
				"default",
				"success",
				"success",
				"success",
				"primary",
				"primary",
				"primary",
				"warning",
				"warning",
				"warning",
				"danger"
			];
		}
		if($type == 4) {
			$skills = [0, 0.20, 0.40, 0.60, 0.80, 1.00, 1.20, 1.40, 1.60, 1.80, 2.00, 2.50];
			$skills_names  = ["ULOW", "LOW-", "LOW", "LOW+", "MID-", "MID", "MID+", "HIGH-", "HIGH", "HIGH+", "PRO", "GOD"];
			$skills_colors = [
				"default",
				"default",
				"default",
				"default",
				"success",
				"success",
				"success",
				"primary",
				"primary",
				"primary",
				"warning",
				"danger"
			];
		}
		if($type == 2) {
			if(empty($ranks[0])) {
				$ranks = [
					"Курсант",
					"Рядовой",
					"Ефрейтор",
					"Мл.сержант",
					"Сержант",
					"Ст.сержант",
					"Старшина",
					"Прапорщик",
					"Ст.прапорщик",
					"Мл.лейтенант",
					"Лейтенант",
					"Ст.лейтенант",
					"Капитан",
					"Майор",
					"Подполковник",
					"Полковник",
					"Генерал - Майор",
					"Генерал - Лейтенант",
					"Генерал - Полковник",
					"Генерал - Армии",
					"Маршал РФ"
				];
			}
			if(empty($ar_bonus_damages[0]) && $ar_bonus_damages[0] != '0') {
				$ar_bonus_damages = array_fill(0, 39, 0);
			}
		}
		if($type == 4) {
			$STH = $pdo2->prepare("SELECT `game` FROM `hlstats_Servers` WHERE `address`=:address AND `port`=:port LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':address' => $ip, ':port' => $port));
			$game = $STH->fetch();

			if(empty($game->game)) {
				$game = 'csgo';
			} else {
				$game = $game->game;
			}
		}
		if($type == 6) {
			$ranks = [
				0 => [
					'names' => [
						0  => "Калибровка",
						1  => "Серебро I",
						2  => "Серебро II",
						3  => "Серебро III",
						4  => "Серебро IV",
						5  => "Серебро-Элита",
						6  => "Серебро-Великий Магистр",
						7  => "Золотая Звезда I",
						8  => "Золотая Звезда II",
						9  => "Золотая Звезда III",
						10 => "Золотая Звезда — Магистр",
						11 => "Магистр-хранитель I",
						12 => "Магистр-хранитель II",
						13 => "Магистр-хранитель Элита",
						14 => "Заслуженный Магистр-хранитель",
						15 => "Легендарный Беркут",
						16 => "Легендарный Беркут — Магистр",
						17 => "Великий Магистр Высшего Ранга",
						18 => "Всемирная Элита"
					],
					'imagesFolder' => 'ranks_imgs'
				]
			];

			global $config_additional;

			if(isset($config_additional) && array_key_exists('customRanks', $config_additional)) {
				$ranks = array_merge($ranks, $config_additional['customRanks']);
			}
		}

		$sort = $this->get_stats_sort($type, $sort_type);

		if(empty($name)) {
			if($type == 1 || $type == 2) {
				$STH = $pdo2->query("SELECT * FROM csstats_players WHERE frags!=0 ORDER BY $sort LIMIT $start, $limit");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			} elseif($type == 3) {
				$STH = $pdo2->query("SELECT `id`, `roundt`, `roundct`, `deaths`, `skill`, `shots`, `hits`, `connects`, `wint`, `winct`, `name` AS nick, `steamid` AS authid, `kills` AS frags, `hs` AS headshots, `tks` AS teamkills, `dmg` AS damage, `bombdef` AS defusing, `bombdefused` AS defused, `bombplants` AS planted, `bombexplosions` AS explode, `connection_time` AS gametime, `last_join` AS lasttime FROM $table WHERE kills!=0 ORDER BY $sort LIMIT $start, $limit");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			} elseif($type == 4) {
				$STH = $pdo2->query("SELECT playerId AS id, kill_streak, death_streak, connection_time AS gametime,  last_event AS lasttime,  unhex(replace(hex(lastName), 'E280AE', '')) as nick, skill, kills AS frags, deaths, suicides AS suicide, teamkills, shots, hits, headshots FROM hlstats_Players WHERE kills!=0 and game='$game' ORDER BY $sort LIMIT $start, $limit");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			} elseif($type == 5) {
				$STH = $pdo2->query("SELECT `id`, `steam` AS authid, `name` AS nick, `score` AS skill, `deaths`, `kills` AS frags, `suicides` AS suicide, `tk` AS teamkills, `shots`, `hits`, `connected` AS gametime, `lastconnect` AS lasttime, headshots, `rounds_tr` AS roundt, `rounds_ct` AS roundct, `c4_defused` AS defused, `c4_planted` AS planted, `c4_exploded` AS explode, `tr_win` AS wint, `ct_win` AS winct FROM $table WHERE kills!=0 ORDER BY $sort LIMIT $start, $limit");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			} elseif($type == 6) {
				if(check_column($table, $pdo2, 'weaponhits')) {
					$shoots_ = 'weaponshoots';
				} else {
					$shoots_ = 'shoots';
				}

				if(check_column($table, $pdo2, 'playtime')) {
					$playtime_ = 'playtime AS gametime, ';
				} else {
					$playtime_ = '';
				}

				$STH = $pdo2->query("SELECT `steam` AS authid, `name` AS nick, `deaths`, `kills` AS frags, `$shoots_` AS shots, $playtime_ `hits`, `lastconnect` AS lasttime, headshots, value, rank FROM `$table` ORDER BY $sort LIMIT $start, $limit");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			}
		} else {
			if($type == 1 || $type == 2) {
				$STH = $pdo2->prepare("SELECT * FROM csstats_players WHERE frags!=0 and (nick LIKE :name or authid LIKE :name or ip LIKE :name) ORDER BY $sort");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			} elseif($type == 3) {
				$STH = $pdo2->prepare("SELECT `id`,`roundt`,`roundct`,`deaths`,`skill`,`shots`,`hits`,`connects`,`wint`,`winct`,`name` AS nick, `steamid` AS authid, `kills` AS frags, `hs` AS headshots, `tks` AS teamkills, `dmg` AS damage, `bombdef` AS defusing, `bombdefused` AS defused, `bombplants` AS planted, `bombexplosions` AS explode, `connection_time` AS gametime, `last_join` AS lasttime FROM `$table` WHERE kills!=0 and (`name` LIKE :name or `steamid` LIKE :name or `ip` LIKE :name) ORDER BY $sort");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			} elseif($type == 4) {
				$STH = $pdo2->prepare("SELECT playerId AS id, kill_streak, death_streak, connection_time AS gametime,  last_event AS lasttime,  unhex(replace(hex(lastName), 'E280AE', '')) as nick, skill, kills AS frags, deaths, suicides AS suicide, teamkills, shots, hits, headshots FROM hlstats_Players WHERE kills!=0 and game='$game' and (unhex(replace(hex(lastName), 'E280AE', '')) LIKE :name or `lastAddress` LIKE :name) ORDER BY $sort");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			} elseif($type == 5) {
				$STH = $pdo2->prepare("SELECT `id`, `steam` AS authid, `name` AS nick, `score` AS skill, `deaths`, `kills` AS frags, `suicides` AS suicide, `tk` AS teamkills, `shots`, `hits`, `connected` AS gametime, `lastconnect` AS lasttime, headshots, `rounds_tr` AS roundt, `rounds_ct` AS roundct, `c4_defused` AS defused, `c4_planted` AS planted, `c4_exploded` AS explode, `tr_win` AS wint, `ct_win` AS winct FROM `$table` WHERE kills!=0 and (`name` LIKE :name or `steam` LIKE :name) ORDER BY $sort");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			} elseif($type == 6) {
				if(check_column($table, $pdo2, 'weaponhits')) {
					$shoots_ = 'weaponshoots';
				} else {
					$shoots_ = 'shoots';
				}

				if(check_column($table, $pdo2, 'playtime')) {
					$playtime_ = 'playtime AS gametime, ';
				} else {
					$playtime_ = '';
				}

				$STH = $pdo2->prepare("SELECT `steam` AS authid, `name` AS nick, `deaths`, `kills` AS frags, `hits`, `lastconnect` AS lasttime, headshots, value, rank, $playtime_ `$shoots_` AS shots FROM `$table` WHERE (`name` LIKE :name or `steam` LIKE :name) ORDER BY $sort");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			}
			$STH->execute(array(":name" => getNameLike($name)));
		}

		$this->tpl->result['local_content'] = '';

		while($row = $STH->fetch()) {
			$start++;

			if($type == 6) {
				//Чтобы js не сносил нули в начале числа
				$row->id = '9'.preg_replace('/[^0-9]+/', '', $row->authid);
			}

			if(empty($row->authid)) {
				$row->authid = '';
			}
			if(empty($army_enable)) {
				$army_enable = 0;
			}

			if(empty($name)) {
				$row->place = $start;
			} else {
				if($type == 6) {
					$temp_rank = $row->rank;
					$temp_value = $row->value;
					$temp_teamkills = 0;
					$temp_skill = 0;
					$temp_gametime = 0;
				} else {
					$temp_rank = 0;
					$temp_value = 0;
					$temp_teamkills = $row->teamkills;
					$temp_skill = $row->skill;
					$temp_gametime = $row->gametime;
				}
				if($type == 1 || $type == 2) {
					$temp_suicide = $row->suicide;
					$temp_defused = $row->defused;
					$temp_explode = $row->explode;
					$temp_place = $row->place;
				} else {
					$temp_suicide = 0;
					$temp_defused = 0;
					$temp_explode = 0;
					$temp_place = 0;
				}
				$where = $this->get_stats_where($type, $sort_type, $row->frags, $row->deaths, $temp_teamkills, $row->headshots, $temp_skill, $temp_gametime, $temp_suicide, $temp_defused, $temp_explode, $temp_place, $temp_rank, $temp_value);
				if($type == 1 || $type == 2) {
					$STH2 = $pdo2->query("SELECT `id` FROM `csstats_players` WHERE `frags`!='0' and ($where) ORDER BY $sort");
				} elseif($type == 3) {
					$STH2 = $pdo2->query("SELECT `id` FROM `$table` WHERE `kills`!='0' and ($where) ORDER BY $sort");
				} elseif($type == 4) {
					$STH2 = $pdo2->query("SELECT `playerId` FROM `hlstats_Players` WHERE `kills`!='0' and game='$game' and ($where) ORDER BY $sort");
				} elseif($type == 5) {
					$STH2 = $pdo2->query("SELECT `id` FROM `$table` WHERE `kills`!='0' and ($where) ORDER BY $sort");
				} elseif($type == 6) {
					$STH2 = $pdo2->query("SELECT `steam` FROM `$table` WHERE ($where) ORDER BY $sort");
				}
				$STH2->execute();
				$row->place = count($STH2->fetchAll());
				$row->place++;
			}

			$this->tpl->load_template('elements/stat.tpl');

			if($type == 2) {
				if(empty($row->ar_addxp)) {
					$row->ar_addxp = 0;
				}

				$level = ($row->headshots + $row->frags + $row->defused * $cv_XPc4def + $row->explode * $cv_XPc4def) * $cv_XPvalue + $row->ar_addxp;

				$j = count($levels) - 1;
				if($level > $levels[$j]) {
					$rank = $ranks[$j];
					$ar_bonus_he = $ar_bonus_hes[$j];
					$ar_bonus_flash = $ar_bonus_flashs[$j];
					$ar_bonus_smoke = $ar_bonus_smokes[$j];
					$ar_bonus_defuse = $ar_bonus_defuses[$j];
					$ar_bonus_nv = $ar_bonus_nvs[$j];
					$ar_bonus_armor = $ar_bonus_armors[$j];
					$ar_bonus_hp = $ar_bonus_hps[$j];
					$ar_bonus_flags = $ar_bonus_flagss[$j];
					$ar_bonus_damage = $ar_bonus_damages[$j];
				} else {
					$j = 0;
					while($levels[$j] <= $level) {
						$rank = $ranks[$j];
						$ar_bonus_he = $ar_bonus_hes[$j];
						$ar_bonus_flash = $ar_bonus_flashs[$j];
						$ar_bonus_smoke = $ar_bonus_smokes[$j];
						$ar_bonus_defuse = $ar_bonus_defuses[$j];
						$ar_bonus_nv = $ar_bonus_nvs[$j];
						$ar_bonus_armor = $ar_bonus_armors[$j];
						$ar_bonus_hp = $ar_bonus_hps[$j];
						$ar_bonus_flags = $ar_bonus_flagss[$j];
						$ar_bonus_damage = $ar_bonus_damages[$j];
						$j++;
					}
				}
			}
			if($type == 3) {
				$row->rounds = $row->roundt + $row->roundct;
				$row->lasttime = strtotime($row->lasttime);
			}
			if($type == 5) {
				$row->rounds = $row->roundt + $row->roundct;
			}

			if($user_profile = $this->get_gamer_profile($row->nick, $row->authid, 1)) {
				$nick = $user_profile;
			} else {
				$nick = clean($row->nick, null);
			}

			if($type == 6) {
				$row->frags = $this->lvl_rank_($row->frags);
				$row->deaths = $this->lvl_rank_($row->deaths);
				$row->hits = $this->lvl_rank_($row->hits);
				$row->headshots = $this->lvl_rank_($row->headshots);
				$row->shots = $this->lvl_rank_($row->shots);
			}

			if($army_enable == 2) {
				$this->tpl->set("{hostages}", $row->hostages);
				$this->tpl->set("{assist}", $row->assist);
			}
			if($type == 1 || $type == 2 || $type == 4 || $type == 5) {
				$this->tpl->set("{suicide}", $row->suicide);
			}
			if($type == 2) {
				$this->tpl->set("{rank}", $rank);
				$this->tpl->set("{level}", $level);
				$this->tpl->set("{ar_bonus_he}", $ar_bonus_he);
				$this->tpl->set("{ar_bonus_flash}", $ar_bonus_flash);
				$this->tpl->set("{ar_bonus_smoke}", $ar_bonus_smoke);
				$this->tpl->set("{ar_bonus_defuse}", $ar_bonus_defuse);
				$this->tpl->set("{ar_bonus_nv}", $ar_bonus_nv);
				$this->tpl->set("{ar_bonus_hp}", $ar_bonus_hp);
				$this->tpl->set("{ar_bonus_armor}", $ar_bonus_armor);
				$this->tpl->set("{ar_bonus_flags}", $ar_bonus_flags);
				$this->tpl->set("{ar_bonus_damage}", $ar_bonus_damage);
			}
			if($type == 1 || $type == 2 || $type == 3 || $type == 4) {
				if($type == 4) {
					$skill = round($row->frags/$row->deaths, 2);
				} else {
					$skill = round($row->skill);
				}

				$j = 0;

				do {
					$skill_name = $skills_names[$j];
					$skill_color = $skills_colors[$j];
					$j++;
				} while(isset($skills[$j]) && ($skill >= $skills[$j]) && isset($skills_names[$j]) && isset($skills_colors[$j]));

				$this->tpl->set("{skill_name}", $skill_name);
				$this->tpl->set("{skill_color}", $skill_color);
				$this->tpl->set("{skill}", $skill);
			}
			if($type == 5) {
				$this->tpl->set("{skill}", $row->skill);
			}
			if($type == 4) {
				$this->tpl->set("{score}", $row->skill);
				$this->tpl->set("{kill_streak}", $row->kill_streak);
				$this->tpl->set("{death_streak}", $row->death_streak);
			}
			if($type == 1 || $type == 2 || $type == 3) {
				$this->tpl->set("{damage}", $row->damage);
				$this->tpl->set("{defusing}", $row->defusing);
				$this->tpl->set("{connects}", $row->connects);
			}
			if($type == 1 || $type == 2 || $type == 3 || $type == 5) {
				$this->tpl->set("{authid}", isNeedHidePlayerId() ? hidePlayerId($row->authid) : $row->authid);
				$this->tpl->set("{defused}", $row->defused);
				$this->tpl->set("{planted}", $row->planted);
				$this->tpl->set("{explode}", $row->explode);
				$this->tpl->set("{rounds}", $row->rounds);
				$this->tpl->set("{wint}", $row->wint);
				$this->tpl->set("{winct}", $row->winct);
			}
			if($type == 1 || $type == 2 || $type == 3 || $type == 4 || $type == 5) {
				$this->tpl->set("{teamkills}", $row->teamkills);
				$this->tpl->set("{procent3}", get_procent($row->frags / 100, $row->teamkills));
			}
			if(isset($row->gametime)) {
				$this->tpl->set("{time}", expand_seconds2($row->gametime));
			}
			if($type == 6) {
				$this->tpl->set("{authid}", $row->authid);
				$this->tpl->set("{value}", $row->value);

				if(array_key_exists($server, $ranks)) {
					$ranksId = $server;
				} else {
					$ranksId = 0;
				}

				$this->tpl->set("{rank_img}", '../files/' . $ranks[$ranksId]['imagesFolder'] . '/'.$row->rank.'.png');
				$this->tpl->set("{rank_name}", $ranks[$ranksId]['names'][$row->rank]);
			}

			$this->tpl->set("{type}", $type);
			$this->tpl->set("{reset_stats}", $reset_stats['trigger']);
			$this->tpl->set("{reset_stats_price}", $reset_stats['price']);
			$this->tpl->set("{army_enable}", $army_enable);
			$this->tpl->set("{id}", $row->id);
			$this->tpl->set("{place}", $row->place);
			$this->tpl->set("{nick}", $nick);
			$this->tpl->set("{frags}", $row->frags);
			$this->tpl->set("{deaths}", $row->deaths);
			$this->tpl->set("{headshots}", $row->headshots);
			$this->tpl->set("{shots}", $row->shots);
			$this->tpl->set("{hits}", $row->hits);
			$this->tpl->set("{kdr}", ($row->deaths == 0) ? 0 : round($row->frags/$row->deaths, 2));
			$this->tpl->set("{procent1}", get_procent($row->deaths, $row->frags));
			$this->tpl->set("{procent2}", get_procent($row->frags / 100, $row->headshots));
			$this->tpl->set("{procent4}", get_procent($row->shots / 100, $row->hits));

			$this->tpl->set("{date}", expand_date(date('d.m.Y H:i', $row->lasttime), 7));
			$this->tpl->set("{server}", $server);
			$this->tpl->compile('local_content');
			$this->tpl->clear();
		}
		if(empty($this->tpl->result['local_content'])) {
			return '<tr><td colspan="10">Нет результатов</td></tr>';
		} else {
			return $this->tpl->result['local_content'];
		}
	}

	public function weapon_stats($server, $auth) {
		$server = check($server, "int");
		$auth = checkJs($auth, null);

		global $messages;

		if(empty($server)) {
			return '<tr><td colspan="20">'.$messages['errorConnectingToDatabase'].'</td></tr>';
		}
		if(empty($auth)) {
			return '<tr><td colspan="20">' . $messages['informationNotFound'] . '</td></tr>';
		}

		$STH = $this->pdo->query("SELECT id,st_db_host,st_db_user,st_db_pass,st_db_db,st_db_code,st_type FROM servers WHERE (st_type='1' or st_type='2') and id='$server' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		$db_host = $row->st_db_host;
		$db_user = $row->st_db_user;
		$db_pass = $row->st_db_pass;
		$db_db = $row->st_db_db;
		if(empty($row->id) || !$pdo2 = db_connect($db_host, $db_db, $db_user, $db_pass)) {
			return '<tr><td colspan="20">'.$messages['errorConnectingToDatabase'].'</td></tr>';
		}
		set_names($pdo2, $row->st_db_code);

		$s_name  = [
			"",
			"Sig Sauer P-228",
			"",
			"Steyr Scout",
			"High Explosive Grenade",
			"Benelli/H&K M4 Super 90 XM1014",
			"",
			"Ingram MAC-10",
			"Steyr Aug",
			"",
			"Dual Beretta 96G Elite",
			"Five-SeveN",
			"H&K UMP45",
			"Sig SG-550 Sniper",
			"Galil",
			"Fusil Automatique",
			"H&K USP .45 Tactical",
			"Glock 18 Select Fire",
			"Arctic Warfare Magnum (Police)",
			"H&K MP5-Navy",
			"M249 PARA Light Machine Gun",
			"Benelli M3 Super 90 Combat",
			"Colt M4A1 Carbine",
			"Steyr Tactical Machine Pistol",
			"G3SG1",
			"",
			"Desert Eagle .50AE",
			"Sig Sauer SG-552 Commando",
			"Kalashnikov AK-47",
			"Knife",
			"FN P90"
		];
		$pic_url = [
			"",
			"https://s1.gameme.net/img/weapons/css/p228_14737_197a38f0645b774c6dc0da9eceb06ccc2c9fdfbc.png",
			"",
			"https://s1.gameme.net/img/weapons/cstrike/scout_14737_eb97a1822e3271e16780e47e1f79b516cb4464e1.png",
			"https://s1.gameme.net/img/weapons/cstrike/grenade_14737_4b0f6f063c241da1ce2b68abadf3dad0b7e02884.png",
			"https://s1.gameme.net/img/weapons/cstrike/xm1014_14737_b818c52b319f5b2eb0154e51183073e52354e050.png",
			"",
			"https://s1.gameme.net/img/weapons/cstrike/mac10_14737_95022054905aa11ce482b1454c5a466ea62ad28c.png",
			"https://s1.gameme.net/img/weapons/cstrike/aug_14737_ed09af5570f2567ce2d5ea85a6edf8b09ea0ef4e.png",
			"",
			"https://s1.gameme.net/img/weapons/cstrike/elite_14737_7ab23b0a760a5456ac7a866498073d3a8b420ecb.png",
			"https://s1.gameme.net/img/weapons/cstrike/fiveseven_14737_478edccc078d20a38f28eff5fb30be9a41642f57.png",
			"https://s1.gameme.net/img/weapons/cstrike/ump45_14737_f745dd387401b6dd8ebb8b65bb849b2a3f0fa116.png",
			"https://s1.gameme.net/img/weapons/css/sg550_14737_8463dbda7649d555a09db4d506258b24a870eee4.png",
			"https://s1.gameme.net/img/weapons/cstrike/galil_14737_b09635ec7e1a46005921326293b75601153b25a2.png",
			"https://s1.gameme.net/img/weapons/cstrike/famas_14737_ebf858b8cbe74c3d634c5e1c14b2e6139444f4f3.png",
			"https://s1.gameme.net/img/weapons/cstrike/usp_14737_53a83d1325d8fe2d6c0d620543299848c7610b24.png",
			"https://s1.gameme.net/img/weapons/cstrike/glock18_14737_dd14a18834cd318d19307db32d0418439c310fc8.png",
			"https://s1.gameme.net/img/weapons/cstrike/awp_14737_bdecf4bb7f8217b91be11132f9ca9a0fd0cae0d4.png",
			"https://s1.gameme.net/img/weapons/cstrike/mp5navy_14737_98373da7f9894f271f0b25137f687f6b33e3414b.png",
			"https://s1.gameme.net/img/weapons/cstrike/m249_14737_e15b885e4b99b9ec99040ca32e119d65956a24bb.png",
			"https://s1.gameme.net/img/weapons/cstrike/m3_14737_3d9d28aac03bfe15e079464e907cd826640ee5b3.png",
			"https://s1.gameme.net/img/weapons/cstrike/m4a1_14737_49dde69d3fd7e1ac8ce1a0d1b3e8d2fa3ca5cfe1.png",
			"https://s1.gameme.net/img/weapons/cstrike/tmp_14737_1fcfb822c6f056f7f8d594ba74630f859d1d00f2.png",
			"https://s1.gameme.net/img/weapons/csgo/g3sg1_14737_674ce0a820fbd86265ceaf5b932241f545c709f2.png",
			"",
			"https://s1.gameme.net/img/weapons/cstrike/deagle_14737_c5f8423f8fbbc17c81899ec5a17f5c2df3b568e2.png",
			"https://s1.gameme.net/img/weapons/cstrike/sg552_14737_0a68dfd08103b9470f6fb9c8ea9976550c4215b9.png",
			"https://s1.gameme.net/img/weapons/cstrike/ak47_14737_a9721a64e38ffa329ece483bd5abd78cd23ac828.png",
			"https://s1.gameme.net/img/weapons/csgo/knife_14737_37829b08457785610c778280f8112191940399b9.png",
			"https://s1.gameme.net/img/weapons/cstrike/p90_14737_8c57b5fd997e2d496e56f3fca1b6c617d9953137.png"
		];

		$STH = $pdo2->prepare("SELECT * FROM weapon_ak47 WHERE authid=:authid UNION
										 SELECT * FROM weapon_aug WHERE authid=:authid UNION
										 SELECT * FROM weapon_awp WHERE authid=:authid UNION
										 SELECT * FROM weapon_deagle WHERE authid=:authid UNION
										 SELECT * FROM weapon_elite WHERE authid=:authid UNION
										 SELECT * FROM weapon_famas WHERE authid=:authid UNION
										 SELECT * FROM weapon_fiveseven WHERE authid=:authid UNION
										 SELECT * FROM weapon_g3sg1 WHERE authid=:authid UNION
										 SELECT * FROM weapon_galil WHERE authid=:authid UNION
										 SELECT * FROM weapon_glock18 WHERE authid=:authid UNION
										 SELECT * FROM weapon_hegrenade WHERE authid=:authid UNION
										 SELECT * FROM weapon_knife WHERE authid=:authid UNION
										 SELECT * FROM weapon_m3 WHERE authid=:authid UNION
										 SELECT * FROM weapon_m4a1 WHERE authid=:authid UNION
										 SELECT * FROM weapon_m249 WHERE authid=:authid UNION
										 SELECT * FROM weapon_mac10 WHERE authid=:authid UNION
										 SELECT * FROM weapon_mp5navy WHERE authid=:authid UNION
										 SELECT * FROM weapon_p90 WHERE authid=:authid UNION
										 SELECT * FROM weapon_p228 WHERE authid=:authid UNION
										 SELECT * FROM weapon_scout WHERE authid=:authid UNION
										 SELECT * FROM weapon_sg550 WHERE authid=:authid UNION
										 SELECT * FROM weapon_sg552 WHERE authid=:authid UNION
										 SELECT * FROM weapon_tmp WHERE authid=:authid UNION
										 SELECT * FROM weapon_ump45 WHERE authid=:authid UNION
										 SELECT * FROM weapon_usp WHERE authid=:authid UNION
										 SELECT * FROM weapon_xm1014 WHERE authid=:authid");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(":authid" => $auth));

		$this->tpl->result['local_content'] = '';
		while($row = $STH->fetch()) {
			if(!$row->shots and !$row->damage) {
				continue;
			}
			$wid = $row->weapon_id;
			$this->tpl->load_template('elements/stat_weapon.tpl');
			$this->tpl->set("{pic_url}", $pic_url[$wid]);
			$this->tpl->set("{alt_pic}", $s_name[$wid]);
			$this->tpl->set("{wname}", $s_name[$wid]);
			$this->tpl->set("{wkills}", $row->frags);
			$this->tpl->set("{wshots}", $row->shots);
			$this->tpl->set("{whits}", $row->hits);
			$this->tpl->set("{wdamage}", $row->damage);
			$this->tpl->set("{whit_generic}", $row->hit_generic);
			$this->tpl->set("{whit_head}", $row->hit_head);
			$this->tpl->set("{whit_chest}", $row->hit_chest);
			$this->tpl->set("{whit_stomach}", $row->hit_stomach);
			$this->tpl->set("{whit_leftarm}", $row->hit_leftarm);
			$this->tpl->set("{whit_rightarm}", $row->hit_rightarm);
			$this->tpl->set("{whit_leftleg}", $row->hit_leftleg);
			$this->tpl->set("{whit_rightleg}", $row->hit_rightleg);
			$this->tpl->compile('local_content');
			$this->tpl->clear();
		}

		if($this->tpl->result['local_content'] == '') {
			return '<tr><td colspan="20">' . $messages['informationNotFound'] . '</td></tr>';
		} else {
			return $this->tpl->result['local_content'];
		}
	}

	public function map_stats($server, $auth) {
		$server = check($server, "int");
		$auth = checkJs($auth, null);

		global $messages;

		if(empty($server)) {
			return '<tr><td colspan="10">' . $messages['errorConnectingToDatabase'] . '</td></tr>';
		}
		if(empty($auth)) {
			return '<tr><td colspan="10">' . $messages['informationNotFound'] . '</td></tr>';
		}

		$STH = $this->pdo->query("SELECT id,st_db_host,st_db_user,st_db_pass,st_db_db,st_db_code,st_type FROM servers WHERE (st_type='1' or st_type='2') and id='$server' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		$db_host = $row->st_db_host;
		$db_user = $row->st_db_user;
		$db_pass = $row->st_db_pass;
		$db_db = $row->st_db_db;
		if(
			empty($row->id)
			|| !$pdo2 = db_connect(
				$db_host,
				$db_db,
				$db_user,
				$db_pass
			)
		) {
			return '<tr><td colspan="10">' . $messages['errorConnectingToDatabase'] . '</td></tr>';
		}
		set_names($pdo2, $row->st_db_code);

		$this->tpl->result['local_content'] = '';
		$STH = $pdo2->prepare("SELECT * FROM csstats_maps WHERE authid=:authid");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(":authid" => $auth));
		while($row = $STH->fetch()) {
			$this->tpl->load_template('elements/stat_map.tpl');
			$this->tpl->set("{mapname}", $row->mapname);
			$this->tpl->set("{mfrags}", $row->frags);
			$this->tpl->set("{mdeaths}", $row->deaths);
			$this->tpl->set("{mheadshots}", $row->headshots);
			$this->tpl->set("{mgametime}", expand_seconds2($row->gametime));
			$this->tpl->set("{mrounds}", $row->rounds);
			$this->tpl->compile('local_content');
			$this->tpl->clear();
		}

		if($this->tpl->result['local_content'] == '') {
			return '<tr><td colspan="10">' . $messages['informationNotFound'] . '</td></tr>';
		} else {
			return $this->tpl->result['local_content'];
		}
	}

	public function news($start, $class, $limit = 10) {
		$start = check($start, "int");
		$class = check($class, "int");
		$limit = check($limit, "int");

		global $users_groups;

		if(empty($start)) {
			$start = 0;
		}
		if(empty($limit)) {
			$limit = 10;
		}

		$date = date("Y-m-d H:i:s");
		if(is_worthy("q")) {
			if(empty($class)) {
				$STH = $this->pdo->query("SELECT news.id,news.class,news.new_name,news.img,news.short_text,news.date,news.author,news.views,users.login,users.avatar,news.views FROM news LEFT JOIN users ON news.author = users.id ORDER BY news.date DESC LIMIT $start, $limit");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			} else {
				$STH = $this->pdo->query("SELECT news.id,news.class,news.new_name,news.img,news.short_text,news.date,news.author,news.views,users.login,users.avatar,news.views FROM news LEFT JOIN users ON news.author = users.id WHERE news.class = $class ORDER BY news.date DESC LIMIT $start, $limit");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			}
		} else {
			if(empty($class)) {
				$STH = $this->pdo->query("SELECT news.id,news.class,news.new_name,news.img,news.short_text,news.date,news.author,news.views,users.login,users.avatar,news.views FROM news LEFT JOIN users ON news.author = users.id WHERE news.date < '$date' ORDER BY news.date DESC LIMIT $start, $limit");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			} else {
				$STH = $this->pdo->query("SELECT news.id,news.class,news.new_name,news.img,news.short_text,news.date,news.author,news.views,users.login,users.avatar,news.views FROM news LEFT JOIN users ON news.author = users.id WHERE news.date < '$date' and news.class = $class ORDER BY news.date DESC LIMIT $start, $limit");
				$STH->setFetchMode(PDO::FETCH_OBJ);
			}
		}

		$this->tpl->result['local_content'] = '';
		while($row = $STH->fetch()) {
			if($row->date > $date) {
				$row->new_name = '(Ожидает публикации) '.$row->new_name;
			}
			$this->tpl->load_template('elements/new.tpl');
			$this->tpl->set("{id}", $row->id);
			$this->tpl->set("{new_name}", $row->new_name);
			$this->tpl->set("{img}", $row->img);
			$this->tpl->set("{short_text}", $row->short_text);
			$this->tpl->set("{author}", $row->author);
			$this->tpl->set("{login}", $row->login);
			$this->tpl->set("{date}", expand_date($row->date, 2));
			$this->tpl->set("{avatar}", $row->avatar);
			$this->tpl->set("{views}", $row->views);
			$this->tpl->compile('local_content');
			$this->tpl->clear();
		}
		if($this->tpl->result['local_content'] == '') {
			$this->tpl->result['local_content'] = '<span class="empty-element">Новостей нет</span>';
		}

		return $this->tpl->result['local_content'];
	}

	public function users($start, $group, $limit = 12) {
		$start = check($start, "int");
		$limit = check($limit, "int");
		if($group === 'multi_accounts' && (is_worthy("f") || is_worthy("g"))) {
			$group = 'multi_accounts';
		} else {
			$group = check($group, "int");
		}

		global $users_groups;

		if(empty($start)) {
			$start = 0;
		}
		if(empty($group)) {
			$group = 0;
		}
		if(empty($limit)) {
			$limit = 12;
		}

		if($group === 'multi_accounts') {
			$STH = $this->pdo->query("SELECT id,login,avatar,rights FROM users WHERE active='1' AND multi_account!='0' LIMIT $start, $limit");
			$STH->setFetchMode(PDO::FETCH_OBJ);
		} elseif($group == 0) {
			$STH = $this->pdo->query("SELECT id,login,avatar,rights FROM users WHERE active='1' LIMIT $start, $limit");
			$STH->setFetchMode(PDO::FETCH_OBJ);
		} else {
			$STH = $this->pdo->query("SELECT id,login,avatar,rights FROM users WHERE active='1' AND rights='$group' LIMIT $start, $limit");
			$STH->setFetchMode(PDO::FETCH_OBJ);
		}

		$this->tpl->result['local_content'] = '';
		while($row = $STH->fetch()) {
			$group = $users_groups[$row->rights];
			$this->tpl->load_template('elements/user.tpl');
			$this->tpl->set("{login}", $row->login);
			$this->tpl->set("{id}", $row->id);
			$this->tpl->set("{avatar}", $row->avatar);
			$this->tpl->set("{gp_name}", $group['name']);
			$this->tpl->set("{gp_color}", $group['color']);
			$this->tpl->compile('local_content');
			$this->tpl->clear();
		}

		return $this->tpl->result['local_content'];
	}

	public function search_login($login, $group) {
		$login = check($login, null);
		if($group === 'multi_accounts' && (is_worthy("f") || is_worthy("g"))) {
			$group = 'multi_accounts';
		} else {
			$group = check($group, "int");
		}

		if(empty($login)) {
			return '<span class="empty-element">Введите логин пользователя</span>';
		}
		if(empty($group)) {
			$group = 0;
		}

		global $users_groups;

		if($group === 'multi_accounts') {
			$STH = $this->pdo->prepare("SELECT id,login,avatar,nick,birth,skype,vk,rights,name,regdate FROM users WHERE active='1' and login LIKE :login or id = :id AND multi_account!='0'");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(":login" => "%".$login."%", ":id" => $login));
		} elseif($group == 0) {
			$STH = $this->pdo->prepare("SELECT id,login,avatar,nick,birth,skype,vk,rights,name,regdate FROM users WHERE active='1' and login LIKE :login or id = :id");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(":login" => "%".$login."%", ":id" => $login));
		} else {
			$STH = $this->pdo->prepare("SELECT id,login,avatar,nick,birth,skype,vk,rights,name,regdate FROM users WHERE active='1' and rights=:group and (login LIKE :login or id = :id)");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(":login" => "%".$login."%", ":id" => $login, ":group" => $group));
		}
		$this->tpl->result['local_content'] = '';
		while($row = $STH->fetch()) {
			$group = $users_groups[$row->rights];

			$this->tpl->load_template('elements/user_full.tpl');
			$this->tpl->set("{login}", $row->login);
			$this->tpl->set("{id}", $row->id);
			$this->tpl->set("{avatar}", $row->avatar);
			$this->tpl->set("{gp_name}", $group['name']);
			$this->tpl->set("{date}", expand_date($row->regdate, 2));
			$this->tpl->set("{date2}", expand_date($row->birth, 2));
			$this->tpl->set("{nick}", $row->nick);
			$this->tpl->set("{skype}", $row->skype);
			$this->tpl->set("{vk}", $row->vk);
			$this->tpl->set("{name}", $row->name);
			$this->tpl->set("{gp_color}", $group['color']);
			$this->tpl->compile('local_content');
			$this->tpl->clear();
		}
		if($this->tpl->result['local_content'] == '') {
			$this->tpl->result['local_content'] = '<span class="empty-element">Пользователь с данным логином не найден.</span>';
		}

		return $this->tpl->result['local_content'];
	}

	public function bans_applications($start, $server, $limit = 10) {
		global $messages;

		$start = checkStart($start);
		$server = check($server, "int");
		$limit = check($limit, "int");

		if(empty($start)) {
			$start = 0;
		}
		if(empty($limit)) {
			$limit = 10;
		}

		if(empty($server)) {
			$STH = $this->pdo->query("SELECT bans.id,bans.server,bans.status,bans.nick,bans.date,bans.author,users.login,users.avatar,servers.name FROM bans LEFT JOIN users ON bans.author = users.id LEFT JOIN servers ON bans.server = servers.id ORDER BY date DESC LIMIT $start, $limit");
			$STH->setFetchMode(PDO::FETCH_OBJ);
		} else {
			$STH = $this->pdo->query("SELECT bans.id,bans.server,bans.status,bans.nick,bans.date,bans.author,users.login,users.avatar,servers.name FROM bans LEFT JOIN users ON bans.author = users.id LEFT JOIN servers ON bans.server = servers.id WHERE bans.server = $server ORDER BY date DESC LIMIT $start, $limit");
			$STH->setFetchMode(PDO::FETCH_OBJ);
		}
		$this->tpl->result['local_content'] = '';
		while($row = $STH->fetch()) {
			if($row->status == 0) {
				$status = $messages['Not_reviewed'];
				$color = "warning";
			}
			if($row->status == 1) {
				$status = $messages['Unbaned'];
				$color = "success";
			}
			if($row->status == 2) {
				$status = $messages['Do_not_unbaned'];
				$color = "danger";
			}
			$this->tpl->load_template('elements/ban_application.tpl');
			$this->tpl->set("{color}", $color);
			$this->tpl->set("{id}", $row->id);
			$this->tpl->set("{nick}", $row->nick);
			$this->tpl->set("{author}", $row->author);
			$this->tpl->set("{login}", $row->login);
			$this->tpl->set("{avatar}", $row->avatar);
			$this->tpl->set("{status}", $status);
			$this->tpl->set("{name}", $row->name);
			$this->tpl->set("{date}", expand_date($row->date, 7));
			$this->tpl->compile('local_content');
			$this->tpl->clear();
		}
		if($this->tpl->result['local_content'] == '') {
			$this->tpl->result['local_content'] = '<tr><td colspan="10">Заявок нет</td></tr>';
		}

		return $this->tpl->result['local_content'];
	}

	public function getAdmins($server = 0, $userId = null, $name = null, $adminId = null) {
		$server  = clean($server, "int");
		$userId  = clean($userId, "int");
		$name    = clean($name, null);
		$adminId = clean($adminId, "int");

		global $users_groups;

		$admins = [];
		$whereData = [];
		$where = [];

		if(!empty($server)) {
			$where[] = '(admins.server = :server) ';
			$whereData[':server'] = $server;
		}

		if(!empty($name)) {
			$where[] = '(admins.name LIKE :name OR users.login LIKE :name OR users.nick LIKE :name) ';
			$whereData[':name'] = getNameLike($name);
		}

		if(!empty($userId)) {
			$where[] = '(users.id = :userId) ';
			$whereData[':userId'] = $userId;
		}

		if(!empty($where)) {
			$where = ' WHERE ' . implode(' AND ', $where);
		}

		if(!empty($adminId)) {
			$where = ' WHERE admins.id = :adminId ';
			$whereData = [];
			$whereData[':adminId'] = $adminId;
		}

		$STH = $this->pdo->prepare("SELECT 
    									admins.id, 
    									admins.type, 
    									admins.name, 
    									admins.cause, 
    									admins.price, 
    									admins.link, 
    									admins.active, 
    									admins.user_id, 
    									users.login, 
    									users.avatar, 
    									users.rights, 
										servers.name as server_name,
    									servers.id as server_id
										FROM 
										    admins
							  				LEFT JOIN 
										        users 
										            ON users.id = admins.user_id
										    LEFT JOIN 
										        servers 
										            ON servers.id = admins.server 
							  		$where
									ORDER BY admins.id");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute($whereData);
		while($row = $STH->fetch()) {
			$STH2 = $this->pdo->prepare(
				"SELECT 
					    services.name, 
					    services.show_adm 
					FROM 
					    admins__services 
					        LEFT JOIN 
					        services 
					            ON 
					                admins__services.service = services.id 
					WHERE admins__services.admin_id=:admin_id LIMIT 10");
			$STH2->setFetchMode(PDO::FETCH_OBJ);
			$STH2->execute([':admin_id' => $row->id]);

			$show = 0;
			$services = '';
			while($row2 = $STH2->fetch()) {
				if(empty($row2->show_adm) or $row2->show_adm == 1) {
					if(empty($row2->name)) {
						$row2->name = 'Неизвестно';
					}
					if($services == '') {
						$services .= $row2->name;
					} else {
						$services .= ' + ' . $row2->name;
					}
					$show++;
				}
			}

			if(empty($row->rights)) {
				$row->rights = 0;
			}
			if(empty($row->login)) {
				$row->login = 0;
			}
			if(empty($row->name)) {
				$row->name = 0;
			}
			if(empty($row->avatar)) {
				$row->avatar = 0;
			}

			$admins[] = [
				'id' => $row->id,
				'show' => $show > 0,
				'server' => $row->server_name,
				'server_id' => $row->server_id,
				'services' => $services,
				'active' => $row->active,
				'cause' => $row->cause,
				'price' => $row->price,
				'link' => $row->link,
				'user_id' => $row->user_id,
				'login' => $row->login,
				'name' => isNeedHideAdminId() ? hidePlayerId($row->name) : $row->name,
				'name_original' => $row->name,
				'avatar' => $row->avatar,
				'type' => $row->type,
				'gp_name' => $users_groups[$row->rights]['name'],
				'gp_color' => $users_groups[$row->rights]['color'],
			];
		}

		return $admins;
	}

	public function servers_admins($server) {
		global $messages;
		
		if(!empty($server)) {
			$server = clean($server, "int");
			$STH = $this->pdo->query("SELECT `id`, `name` FROM `servers` WHERE `id`='$server' LIMIT 1");
		} else {
			$STH = $this->pdo->query("SELECT `id`, `name` FROM `servers` WHERE `type`!=0 AND `united` = '0' ORDER BY `trim`");
		}
		$STH->execute();
		$servers = $STH->fetchAll();

		$this->tpl->result['local_content'] = '';
		foreach($servers as $server) {

			$i = 0;
			$this->tpl->result['admin'] = '';
			$admins = $this->getAdmins($server['id']);

			foreach($admins as $admin) {
				if($admin['show']) {
					$i++;

					$admin['i'] = $i;
					$admin['server'] = '';
					$this->tpl->load_template('elements/admin.tpl');
					foreach($admin as $key => $value) {
						$this->tpl->set('{' . $key . '}', $value);
					}
					$this->tpl->compile('admin');
					$this->tpl->clear();
				}
			}

			if($this->tpl->result['admin'] == '') {
				$this->tpl->result['admin'] = '<tr><td colspan="10">' . $messages['informationNotFound'] . '</td></tr>';
			}

			$this->tpl->load_template('elements/admins.tpl');
			$this->tpl->set("{server_name}", $server['name']);
			$this->tpl->set("{server_id}", $server['id']);
			$this->tpl->set("{admins}", $this->tpl->result['admin']);
			$this->tpl->compile('local_content');
			$this->tpl->clear();
		}

		return $this->tpl->result['local_content'];
	}

	public function price_list() {
		$STH = $this->pdo->query("SELECT discount FROM config__prices LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		$discount = $row->discount;

		if(empty($user)) {
			$user_proc = 0;
		} else {
			$user_proc = $user->proc;
		}

		$this->tpl->result['local_content'] = '';
		$STH = $this->pdo->query("SELECT `id`, `address`, `name`, `discount` FROM `servers` WHERE `type`!='0'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		while($server = $STH->fetch()) {
			$this->tpl->result['services'] = '';
			$STH2 = $this->pdo->prepare("SELECT `id`, `name`, `text`, `discount` FROM `services` WHERE `server`=:server AND `sale`='1' ORDER BY `trim`");
			$STH2->setFetchMode(PDO::FETCH_OBJ);
			$STH2->execute(array(':server' => $server->id));
			while($service = $STH2->fetch()) {
				$i = 0;
				$tarifs = '';
				$STH3 = $this->pdo->prepare("SELECT `id`, `price`, `time`, `discount` FROM `services__tarifs` WHERE `service`=:service");
				$STH3->setFetchMode(PDO::FETCH_OBJ);
				$STH3->execute(array(':service' => $service->id));
				while($tarif = $STH3->fetch()) {
					$i++;
					if($tarif->time == 0) {
						$time = 'Навсегда';
					} else {
						$time = $tarif->time.' дней';
					}
					$proc = calculate_discount($server->discount, $discount, $user_proc, $service->discount, $tarif->discount);
					$price = calculate_price($tarif->price, $proc);
					$tarifs .= '<tr>
									<td>'.$i.'</td>
									<td>'.$time.'</td>
									<td>'.$price.'</td>
								</tr>';
				}
				$this->tpl->load_template('/elements/price_list_service.tpl');
				$this->tpl->set("{service_name}", $service->name);
				$this->tpl->set("{service_text}", $service->text);
				$this->tpl->set("{tarifs}", $tarifs);
				$this->tpl->compile('services');
				$this->tpl->clear();
			}
			$this->tpl->load_template('/elements/price_list_server.tpl');
			$this->tpl->set("{server_name}", $server->name);
			$this->tpl->set("{server_ip}", $server->address);
			$this->tpl->set("{services}", $this->tpl->result['services']);
			$this->tpl->compile('local_content');
			$this->tpl->clear();
		}

		return $this->tpl->result['local_content'];
	}

	public function notifications($start, $limit = 10) {
		$start = checkStart($start);
		$limit = check($limit, "int");

		if(empty($start)) {
			$start = 0;
		}
		if(empty($limit)) {
			$limit = 10;
		}

		$STH = $this->pdo->query("SELECT * FROM notifications WHERE user_id='$_SESSION[id]' ORDER BY date DESC LIMIT $start, $limit");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$this->tpl->result['local_content'] = '';
		while($row = $STH->fetch()) {
			if($row->type == 1) {
				$class = 'info';
			}
			if($row->type == 2) {
				$class = 'success';
			}
			if($row->type == 3) {
				$class = 'error';
			}

			$text = find_img_mp3($row->message, 1);
			$this->tpl->load_template('/elements/notification.tpl');
			$this->tpl->set("{class}", $class);
			$this->tpl->set("{date}", expand_date($row->date, 7));
			$this->tpl->set("{text}", $text);
			$this->tpl->set("{function}", 'dell_notification');
			$this->tpl->set("{id}", $row->id);
			$this->tpl->compile('local_content');
			$this->tpl->clear();
		}
		if(empty($this->tpl->result['local_content'])) {
			$this->tpl->load_template('/elements/notification.tpl');
			$this->tpl->set("{class}", "info");
			$this->tpl->set("{date}", expand_date(date("Y-m-d H:i:s"), 7));
			$this->tpl->set("{text}", "Уведомлений нет");
			$this->tpl->set("{function}", 'close_notification');
			$this->tpl->set("{id}", 1);
			$this->tpl->compile('local_content');
			$this->tpl->clear();
		} else {
			$this->tpl->result['local_content'] .= '<script>$("#notifications_line").removeClass("disp-n");</script>';
		}

		return $this->tpl->result['local_content'];
	}
}