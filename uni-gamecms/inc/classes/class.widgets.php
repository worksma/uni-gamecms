<?php

	class Widgets {
		private $pdo;
		private $tpl;

		function __construct($pdo, $tpl = null) {
			if (!isset($pdo)) {
				return '[Class Widgets]: No connection to the database';
			}
			if (isset($tpl)) {
				$this->tpl = $tpl;
			}
			$this->pdo = $pdo;
		}

		public function online_users() {
			$i = 0;
			global $users_groups;

			$this->tpl->result['local_content'] = '';

			$STH = $this->pdo->query("SELECT `users__online`.`user_id`, `users`.`login`, `users`.`avatar`, `users`.`rights`, `users`.`skype`, `users`.`vk`, `users`.`telegram` FROM `users__online` LEFT JOIN `users` ON `users__online`.`user_id` = `users`.`id`");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			while ($row = $STH->fetch()) {
				$gp = $users_groups[$row->rights];
				$this->tpl->load_template('elements/online_user.tpl');
				$this->tpl->set("{avatar}", $row->avatar);
				$this->tpl->set("{user_id}", $row->user_id);
				$this->tpl->set("{login}", $row->login);
				$this->tpl->set("{skype}", $row->skype);
				$this->tpl->set("{vk}", $row->vk);
				$this->tpl->set("{telegram}", $row->telegram);
				$this->tpl->set("{gp_color}", $gp['color']);
				$this->tpl->set("{gp_name}", $gp['name']);
				$this->tpl->compile('local_content');
				$this->tpl->clear();
				$i++;
			}
			if ($this->tpl->result['local_content'] == '') {
				$this->tpl->result['local_content'] = '<span class="empty-element">Авторизованных пользователей нет</span>';
			}
			$this->tpl->result['local_content'] .= "<script>$('#users_online_number').empty();$('#users_online_number').append(" . $i . ");</script>";

			return $this->tpl->result['local_content'];
		}

		public function were_online() {
			global $users_groups;

			$this->tpl->result['local_content'] = '';

			$STH = $this->pdo->query("SELECT `users`.`id`, `users`.`rights`, `users`.`login`, `users`.`avatar` FROM `last_online` LEFT JOIN `users` ON `last_online`.`user_id` = `users`.`id`");
			$STH->execute();
			$row = $STH->fetchAll();
			$count = count($row);
			for ($i = 0; $i < $count; $i++) {
				if (!empty($row[$i]['id'])) {
					$gp = $users_groups[$row[$i]['rights']];
					$this->tpl->load_template('elements/visit_user.tpl');
					$this->tpl->set("{gp_name}", $gp['name']);
					$this->tpl->set("{gp_color}", $gp['color']);
					$this->tpl->set("{user_id}", $row[$i]['id']);
					$this->tpl->set("{avatar}", $row[$i]['avatar']);
					$this->tpl->set("{login}", $row[$i]['login']);
					$this->tpl->compile('local_content');
					$this->tpl->clear();

					if ($i != ($count - 1)) {
						$this->tpl->result['local_content'] .= ', ';
					}
				}
			}
			if ($this->tpl->result['local_content'] == '') {
				$this->tpl->result['local_content'] = '<span class="empty-element">Сегодня пользователей не было</span>';
			}
			$this->tpl->result['local_content'] .= "<script>$('#count_of_last_onl_us').empty();$('#count_of_last_onl_us').append(" . $count . ");</script>";

			return $this->tpl->result['local_content'];
		}

		public function top_users($limit = 10) {
			$limit = check($limit, "int");
			global $users_groups;

			$this->tpl->result['local_content'] = '';

			$STH = $this->pdo->query("SELECT `id`, `login`, `avatar`, `reit`, `thanks`, `answers`, `rights` FROM `users` WHERE active = '1' ORDER BY `reit` DESC LIMIT $limit");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			while ($row = $STH->fetch()) {
				$gp = $users_groups[$row->rights];
				$this->tpl->load_template('elements/top_users.tpl');
				$this->tpl->set("{gp_name}", $gp['name']);
				$this->tpl->set("{gp_color}", $gp['color']);
				$this->tpl->set("{id}", $row->id);
				$this->tpl->set("{avatar}", $row->avatar);
				$this->tpl->set("{login}", $row->login);
				$this->tpl->set("{reit}", $row->reit);
				$this->tpl->set("{answers}", $row->answers);
				$this->tpl->set("{thanks}", $row->thanks);
				$this->tpl->compile('local_content');
				$this->tpl->clear();
			}
			if ($this->tpl->result['local_content'] == '') {
				$this->tpl->result['local_content'] = '<span class="empty-element">Пользователей нет</span>';
			}

			return $this->tpl->result['local_content'];
		}

		public function top_donators($limit = 10) {
			$limit = check($limit, "int");
			global $users_groups;
			global $messages;
			global $conf;

			$this->tpl->result['local_content'] = '';

			$donators = array();
			$STH = $this->pdo->prepare("SELECT `author`, `shilings` FROM `money__actions` WHERE `type`=:type");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':type' => '1'));
			while ($row = $STH->fetch()) {
				if (isset($donators[$row->author])) {
					$donators[$row->author] += $row->shilings;
				} else {
					$donators[$row->author] = $row->shilings;
				}
			}

			arsort($donators);

			$i = 0;
			foreach ($donators as $key => $value) {
				$i++;
				if ($limit < $i) {
					continue;
				}

				$STH = $this->pdo->prepare("SELECT `id`, `login`, `avatar`, `rights` FROM `users` WHERE `id`=:id LIMIT 1");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array(':id' => $key));
				$row = $STH->fetch();
				$gp = $users_groups[$row->rights];

				$this->tpl->load_template('elements/top_donator.tpl');
				$this->tpl->set("{avatar}", $row->avatar);
				$this->tpl->set("{user_id}", $row->id);
				$this->tpl->set("{login}", $row->login);
				$this->tpl->set("{sum}", $value . $messages['RUB']);
				$this->tpl->set("{gp_name}", $gp['name']);
				$this->tpl->set("{gp_color}", $gp['color']);
				$this->tpl->set("{showSum}", $conf->top_donators_show_sum);
				$this->tpl->compile('local_content');
				$this->tpl->clear();
			}

			if ($this->tpl->result['local_content'] == '') {
				$this->tpl->result['local_content'] = '<span class="empty-element">Пользователей нет</span>';
			}

			return $this->tpl->result['local_content'];
		}

		public function birthday_boys() {
			global $users_groups;

			$this->tpl->result['local_content'] = '';

			$STH = $this->pdo->prepare("SELECT `id`, `login`, `avatar`, `rights`, `birth` FROM `users` WHERE MONTH(`birth`) = MONTH(CURRENT_TIMESTAMP) AND (:time_from <= DAY(`birth`) AND DAY(`birth`)<=:time_to)");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':time_from' => date("d", time()), ':time_to' => date("d", time() + 2 * 24 * 60 * 60)));
			while ($row = $STH->fetch()) {
				$gp = $users_groups[$row->rights];

				$this->tpl->load_template('elements/birth_user.tpl');
				$this->tpl->set("{avatar}", $row->avatar);
				$this->tpl->set("{user_id}", $row->id);
				$this->tpl->set("{login}", $row->login);
				$this->tpl->set("{birth_day}", date("Y.m.d", strtotime($row->birth)));
				$this->tpl->set("{gp_color}", $gp['color']);
				$this->tpl->compile('local_content');
				$this->tpl->clear();
			}

			if ($this->tpl->result['local_content'] == '') {
				$this->tpl->result['local_content'] = '<script>$("#birthday_boys").hide();</script>';
			}

			return $this->tpl->result['local_content'];
		}

		public function last_forum_activity($limit = 10) {
			$limit = check($limit, "int");
			global $users_groups;

			$this->tpl->result['local_content'] = '';
			$ES = new EventsRibbon();
			$where = '';

			for ($i = 0; $i < $limit; $i++) {
				$STH = $this->pdo->query("SELECT `events`.`id`, `events`.`sec_data_id`, `events`.`type`, `events`.`access`, `events`.`date`, `events`.`link`, `events`.`data_id`, `events`.`author`, `users`.`login`, `users`.`avatar`, `users`.`rights` FROM `events` 
				LEFT JOIN `users` ON `events`.`author`=`users`.`id` 
				WHERE $where (`events`.`type`='4' OR `events`.`type`='5') ORDER BY `events`.`date` DESC");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute();
				$row = $STH->fetch();

				if (empty($row->id)) {
					break;
				}

				$where .= "`events`.`sec_data_id` != '" . $row->sec_data_id . "' AND `events`.`id` != '" . $row->id . "' AND ";

				if ($ES->have_rights($row->access)) {
					if ($row->type == 4) {
						$STH2 = $this->pdo->prepare("SELECT `forums__topics`.`name` AS 'topic_name', `forums__topics`.`img` AS 'topic_img', `forums`.`name` AS 'forum_name', `forums`.`img` AS 'forum_img' FROM `forums__topics`
					LEFT JOIN `forums` ON `forums`.`id`=`forums__topics`.`forum_id` 
					WHERE `forums__topics`.`id`=:id LIMIT 1");
						$STH2->setFetchMode(PDO::FETCH_OBJ);
						$STH2->execute(array(':id' => $row->data_id));
						$name = $STH2->fetch();
					}
					if ($row->type == 5) {
						$STH2 = $this->pdo->prepare("SELECT `forums__topics`.`name` AS 'topic_name', `forums__topics`.`img` AS 'topic_img', `forums`.`name` AS 'forum_name', `forums`.`img` AS 'forum_img' FROM `forums__messages` 
						LEFT JOIN `forums__topics` ON `forums__messages`.`topic`=`forums__topics`.`id` 
						LEFT JOIN `forums` ON `forums`.`id`=`forums__topics`.`forum_id` 
						WHERE `forums__messages`.`id`=:id LIMIT 1");
						$STH2->setFetchMode(PDO::FETCH_OBJ);
						$STH2->execute(array(':id' => $row->data_id));
						$name = $STH2->fetch();
					}

					$gp = $users_groups[$row->rights];
					$this->tpl->load_template('elements/last_activity.tpl');
					$this->tpl->set("{gp_name}", $gp['name']);
					$this->tpl->set("{gp_color}", $gp['color']);
					$this->tpl->set("{author}", $row->author);
					$this->tpl->set("{avatar}", $row->avatar);
					$this->tpl->set("{login}", $row->login);
					$this->tpl->set("{name}", $name->topic_name);
					$this->tpl->set("{topic_img}", $name->topic_img);
					$this->tpl->set("{forum_name}", $name->forum_name);
					$this->tpl->set("{forum_img}", $name->forum_img);
					$this->tpl->set("{date}", expand_date($row->date, 7));
					$this->tpl->set("{link}", $row->link);
					$this->tpl->compile('local_content');
					$this->tpl->clear();
				}
			}
			if ($this->tpl->result['local_content'] == '') {
				$this->tpl->result['local_content'] = '<span class="empty-element">Сообщений не найдено</span>';
			}

			return $this->tpl->result['local_content'];
		}

		public function others_news($id = 1, $limit = 10) {
			$id = check($id, "int");
			$limit = check($limit, "int");

			$this->tpl->result['local_content'] = '';

			$date = date("Y-m-d H:i:s");
			$STH = $this->pdo->query("SELECT `news__classes`.`name` AS `category`, `news`.`id`, `news`.`new_name`, `news`.`img`, `news`.`views`, `news`.`date` FROM `news`
		LEFT JOIN `news__classes` ON `news__classes`.`id` = `news`.`class`
		WHERE `news`.`date`<'$date' AND `news`.`id` != '$id' ORDER BY `news`.`date` DESC LIMIT $limit");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			while ($row = $STH->fetch()) {
				$this->tpl->load_template('elements/last_new.tpl');
				$this->tpl->set("{id}", $row->id);
				$this->tpl->set("{img}", $row->img);
				$this->tpl->set("{name}", $row->new_name);
				$this->tpl->set("{views}", $row->views);
				$this->tpl->set("{category}", $row->category);
				$this->tpl->set("{date}", expand_date($row->date, 2));
				$this->tpl->compile('local_content');
				$this->tpl->clear();
			}
			if ($this->tpl->result['local_content'] == '') {
				$this->tpl->result['local_content'] = '<span class="empty-element">Новостей нет</span>';
			}

			return $this->tpl->result['local_content'];
		}

		public function last_news($limit = 10) {
			$limit = check($limit, "int");

			$this->tpl->result['local_content'] = '';

			$date = date("Y-m-d H:i:s");
			$STH = $this->pdo->query("SELECT `news__classes`.`name` AS `category`, `news`.`id`, `news`.`new_name`, `news`.`short_text`, `news`.`img`, `news`.`views`, `news`.`date` FROM `news`
		LEFT JOIN `news__classes` ON `news__classes`.`id` = `news`.`class`
		WHERE `news`.`date`<'$date' ORDER BY `news`.`date` DESC LIMIT $limit");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			while ($row = $STH->fetch()) {
				$this->tpl->load_template('elements/new_new.tpl');
				$this->tpl->set("{id}", $row->id);
				$this->tpl->set("{img}", $row->img);
				$this->tpl->set("{name}", $row->new_name);
				$this->tpl->set("{views}", $row->views);
				$this->tpl->set("{category}", $row->category);
				$this->tpl->set("{date}", expand_date($row->date, 2));
				$this->tpl->set("{short_text}", $row->short_text);
				$this->tpl->compile('local_content');
				$this->tpl->clear();
			}
			if ($this->tpl->result['local_content'] == '') {
				$this->tpl->result['local_content'] = '<span class="empty-element">Новостей нет</span>';
			}

			return $this->tpl->result['local_content'];
		}

		public function user_forum_activity($id = 1, $limit = 5) {
			$id = check($id, "int");
			$limit = check($limit, "int");

			global $users_groups;

			$this->tpl->result['local_content'] = '';

			$STH = $this->pdo->query("SELECT `events`.`type`, `events`.`access`, `events`.`date`, `events`.`link`, `events`.`data_id`, `events`.`author`, `users`.`login`, `users`.`rights`, `users`.`avatar` FROM `events` 
			LEFT JOIN `users` ON `events`.`author`=`users`.`id` 
			WHERE `events`.`author` = '$id' and (`events`.`type`='4' OR `events`.`type`='5')
			ORDER BY `events`.`date` DESC LIMIT $limit");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			while ($row = $STH->fetch()) {
				if ($row->type == 4) {
					$STH2 = $this->pdo->prepare("SELECT `forums__topics`.`name` AS 'topic_name', `forums__topics`.`img` AS 'topic_img', `forums`.`name` AS 'forum_name', `forums`.`img` AS 'forum_img' FROM `forums__topics`
				LEFT JOIN `forums` ON `forums`.`id`=`forums__topics`.`forum_id` 
				WHERE `forums__topics`.`id`=:id LIMIT 1");
					$STH2->setFetchMode(PDO::FETCH_OBJ);
					$STH2->execute(array(':id' => $row->data_id));
					$name = $STH2->fetch();
				}
				if ($row->type == 5) {
					$STH2 = $this->pdo->prepare("SELECT `forums__topics`.`name` AS 'topic_name', `forums__topics`.`img` AS 'topic_img', `forums`.`name` AS 'forum_name', `forums`.`img` AS 'forum_img' FROM `forums__messages` 
					INNER JOIN `forums__topics` ON `forums__messages`.`topic`=`forums__topics`.`id` 
					INNER JOIN `forums` ON `forums`.`id`=`forums__topics`.`forum_id` 
					WHERE `forums__messages`.`id`=:id LIMIT 1");
					$STH2->setFetchMode(PDO::FETCH_OBJ);
					$STH2->execute(array(':id' => $row->data_id));
					$name = $STH2->fetch();
				}
				$gp = $users_groups[$row->rights];
				$this->tpl->load_template('elements/last_activity.tpl');
				$this->tpl->set("{gp_name}", $gp['name']);
				$this->tpl->set("{gp_color}", $gp['color']);
				$this->tpl->set("{author}", $row->author);
				$this->tpl->set("{avatar}", $row->avatar);
				$this->tpl->set("{login}", $row->login);
				$this->tpl->set("{name}", $name->topic_name);
				$this->tpl->set("{topic_img}", $name->topic_img);
				$this->tpl->set("{forum_name}", $name->forum_name);
				$this->tpl->set("{forum_img}", $name->forum_img);
				$this->tpl->set("{date}", expand_date($row->date, 7));
				$this->tpl->set("{link}", $row->link);
				$this->tpl->compile('local_content');
				$this->tpl->clear();
			}
			if ($this->tpl->result['local_content'] == '') {
				$this->tpl->result['local_content'] = '<span class="empty-element">Сообщений нет</span>';
			}

			return $this->tpl->result['local_content'];
		}

		public function user_bans($id = 1, $limit = 5) {
			global $messages;

			$id = check($id, "int");
			$limit = check($limit, "int");

			$this->tpl->result['local_content'] = '';

			$STH = $this->pdo->query("SELECT bans.id,bans.date,bans.status,bans.nick,servers.name FROM bans LEFT JOIN users ON bans.author = users.id LEFT JOIN servers ON bans.server = servers.id WHERE bans.author = '$id' ORDER BY bans.date DESC LIMIT $limit");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			while ($row = $STH->fetch()) {
				if ($row->status == 0) {
					$status = $messages['Not_reviewed'];
					$color = "warning";
				}
				if ($row->status == 1) {
					$status = $messages['Unbaned'];
					$color = "success";
				}
				if ($row->status == 2) {
					$status = $messages['Do_not_unbaned'];
					$color = "danger";
				}
				$this->tpl->load_template('elements/last_bans.tpl');
				$this->tpl->set("{id}", $row->id);
				$this->tpl->set("{name}", $row->name);
				$this->tpl->set("{nick}", $row->nick);
				$this->tpl->set("{status}", $status);
				$this->tpl->set("{color}", $color);
				$this->tpl->set("{date}", expand_date($row->date, 7));
				$this->tpl->compile('local_content');
				$this->tpl->clear();
			}
			if ($this->tpl->result['local_content'] == '') {
				$this->tpl->result['local_content'] = '<span class="empty-element">Заявок нет</span>';
			}

			return $this->tpl->result['local_content'];
		}

		public function user_admins($id = 1) {
			$id = check($id, "int");

			$i = 0;
			$this->tpl->result['local_content'] = '';
			$admins = (new GetData($this->pdo, $this->tpl))->getAdmins(0, $id);

			foreach($admins as $admin) {
				if($admin['show']) {
					$i++;

					$admin['i'] = $i;
					$this->tpl->load_template('elements/admin.tpl');
					foreach($admin as $key => $value) {
						$this->tpl->set('{' . $key . '}', $value);
					}
					$this->tpl->compile('local_content');
					$this->tpl->clear();
				}
			}

			if ($this->tpl->result['local_content'] == '') {
				$this->tpl->result['local_content'] = '<tr><td colspan="10">Привилегий нет</td></tr>';
			}

			return $this->tpl->result['local_content'];
		}
	}