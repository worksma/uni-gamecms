<?php

class Forum {
	private $pdo;
	private $tpl;

	function __construct($pdo = null, $tpl = null) {
		if(isset($pdo)) {
			$this->pdo = $pdo;
		}
		if(isset($tpl)) {
			$this->tpl = $tpl;
		}
	}

	/* Добавление раздела / форума / темы / сообщения
	==================================================*/
	public function add_section($name, $groups) {
		$STH = $this->pdo->query("SELECT `trim` FROM `forums__section` ORDER BY `trim` DESC LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(isset($row->trim)) {
			$trim = $row->trim + 1;
		} else {
			$trim = 1;
		}

		$STH = $this->pdo->prepare("INSERT INTO `forums__section` (`name`, `trim`, `access`) values (:name, :trim, :access)");
		if($STH->execute(array('name' => $name, 'trim' => $trim, 'access' => $groups)) == '1') {
			return true;
		} else {
			return false;
		}
	}

	public function add_forum($section_id, $name, $description, $img) {
		$STH = $this->pdo->query("SELECT `trim` FROM `forums` WHERE `section_id`='$section_id' ORDER BY `trim` DESC LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(isset($row->trim)) {
			$trim = $row->trim + 1;
		} else {
			$trim = 1;
		}

		$STH = $this->pdo->query("SELECT `access` FROM `forums__section` WHERE `id`='$section_id'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$access = $STH->fetch();
		if(!$this->have_rights($access->access)) {
			return false;
		}

		$STH = $this->pdo->prepare("INSERT INTO `forums` (`img`, `section_id`, `name`, `description`, `trim`) values (:img, :section_id, :name, :description, :trim)");
		$STH->execute(array('img' => $img, 'section_id' => $section_id, 'name' => $name, 'description' => $description, 'trim' => $trim));

		return true;
	}

	public function add_topic($name, $img, $text, $forum_id) {
		if(empty($img)) {
			$img = "files/forums_imgs/none.jpg";
		}

		$STH = $this->pdo->query("SELECT `forums__section`.`access` FROM `forums` LEFT JOIN `forums__section` ON `forums__section`.`id` = `forums`.`section_id` WHERE `forums`.`id`='$forum_id'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$access = $STH->fetch();
		if(!$this->have_rights($access->access)) {
			return false;
		}
		$topic['access'] = $access->access;

		if($this->isset_forum($forum_id)) {
			$STH = $this->pdo->prepare("INSERT INTO `forums__topics` (name,img,text,date,old_date,author,views,forum_id,last_msg,status,answers) values (:name,:img, :text, :date, :old_date, :author, :views, :forum_id, :last_msg, :status, :answers)");
			$STH->execute(array('name'     => $name,
			                    'img'      => $img,
			                    'text'     => $text,
			                    'date'     => date("Y-m-d H:i:s"),
			                    'old_date' => date("Y-m-d H:i:s"),
			                    'author'   => $_SESSION['id'],
			                    'views'    => '1',
			                    'forum_id' => $forum_id,
			                    'last_msg' => '0',
			                    'status'   => '1',
			                    'answers'  => '0'));

			$this->set_user_answers($_SESSION['id'], 0, 1);

			$topic['id'] = get_ai($this->pdo, "forums__topics") - 1;

			return $topic;
		} else {
			return false;
		}
	}

	public function add_answer($topic_id, $text) {
		$STH = $this->pdo->query("SELECT `id`, `forum_id`, `name`, `answers`, `status` FROM `forums__topics` WHERE `id`='$topic_id'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(empty($row->id)) {
			return false;
		} else {
			$forum_id       = $row->forum_id;
			$answers        = $row->answers + 1;
			$answer['name'] = $row->name;
		}

		$STH = $this->pdo->query("SELECT `forums__section`.`access` FROM `forums` LEFT JOIN `forums__section` ON `forums__section`.`id` = `forums`.`section_id` WHERE `forums`.`id`='$forum_id'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$access = $STH->fetch();
		if(!$this->have_rights($access->access)) {
			return false;
		}
		$answer['access'] = $access->access;

		if($row->status == 2 or $row->status == 4) {
			return false;
		}

		$date = date("Y-m-d H:i:s");

		$STH = $this->pdo->prepare("INSERT INTO `forums__messages` (text,date,author,topic) values ( :text, :date, :author, :topic )");
		if($STH->execute(array('text' => $text, 'date' => $date, 'author' => $_SESSION['id'], 'topic' => $topic_id)) == '1') {
			$answer['answer'] = get_ai($this->pdo, "forums__messages") - 1;

			$STH = $this->pdo->prepare("UPDATE `forums__topics` SET `last_msg`=:last_msg, `answers`=:answers, `date`=:date WHERE `id`='$topic_id' LIMIT 1");
			$STH->execute(array('last_msg' => $answer['answer'], 'answers' => $answers, 'date' => $date));

			$STH = $this->pdo->prepare("UPDATE `forums` SET `last_msg`=:last_msg WHERE `id`='$forum_id' LIMIT 1");
			$STH->execute(array('last_msg' => $answer['answer']));

			$this->set_user_answers($_SESSION['id'], 1, 1);
			$answer['topic'] = $topic_id;

			return $answer;
		} else {
			return false;
		}
	}

	/* Редактирование раздела / форума / темы / сообщения
	==================================================*/
	public function edit_section($id, $name, $groups) {
		$STH = $this->pdo->prepare("UPDATE `forums__section` SET name=:name, access=:access WHERE id='$id' LIMIT 1");
		$STH->execute(array('name' => $name, 'access' => $groups));

		return true;
	}

	public function up_section($id) {
		$STH = $this->pdo->query("SELECT `trim` FROM `forums__section` WHERE `id`='$id' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(empty($row->trim)) {
			return false;
		}
		if($row->trim == 1) {
			return true;
		}

		$STH = $this->pdo->prepare("UPDATE `forums__section` SET `trim`=:trim WHERE `trim`=:trim2 LIMIT 1");
		$STH->execute(array('trim' => $row->trim, 'trim2' => $row->trim - 1));

		$STH = $this->pdo->prepare("UPDATE `forums__section` SET `trim`=:trim WHERE `id`=:id LIMIT 1");
		$STH->execute(array('trim' => $row->trim - 1, 'id' => $id));

		return true;
	}

	public function down_section($id) {
		$STH = $this->pdo->query("SELECT `trim` FROM `forums__section` WHERE id='$id' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(empty($row->trim)) {
			return false;
		}

		$STH = $this->pdo->query("SELECT `trim` FROM `forums__section` ORDER BY `trim` DESC LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$tmp = $STH->fetch();
		if($row->trim == $tmp->trim) {
			return true;
		}

		$STH = $this->pdo->prepare("UPDATE `forums__section` SET `trim`=:trim WHERE `trim`=:trim2 LIMIT 1");
		$STH->execute(array('trim' => $row->trim, 'trim2' => $row->trim + 1));

		$STH = $this->pdo->prepare("UPDATE `forums__section` SET `trim`=:trim WHERE `id`=:id LIMIT 1");
		$STH->execute(array('trim' => $row->trim + 1, 'id' => $id));

		return true;
	}

	public function edit_forum($id, $name, $description) {
		$STH = $this->pdo->prepare("UPDATE `forums` SET `name`=:name, `description`=:description WHERE `id`='$id' LIMIT 1");
		$STH->execute(array('name' => $name, 'description' => $description));

		return true;
	}

	public function up_forum($id, $section_id) {
		$STH = $this->pdo->query("SELECT `trim` FROM `forums` WHERE `id`='$id' and `section_id`='$section_id' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(empty($row->trim)) {
			return false;
		}
		if($row->trim == 1) {
			return true;
		}

		$STH = $this->pdo->prepare("UPDATE `forums` SET `trim`=:trim WHERE `trim`=:trim2 and `section_id`=:section_id LIMIT 1");
		$STH->execute(array('trim' => $row->trim, 'trim2' => $row->trim - 1, 'section_id' => $section_id));

		$STH = $this->pdo->prepare("UPDATE `forums` SET `trim`=:trim WHERE id=:id and section_id=:section_id LIMIT 1");
		$STH->execute(array('trim' => $row->trim - 1, 'id' => $id, 'section_id' => $section_id));

		return true;
	}

	public function down_forum($id, $section_id) {
		$STH = $this->pdo->query("SELECT `trim` FROM `forums` WHERE `id`='$id' and `section_id`='$section_id' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(empty($row->trim)) {
			return false;
		}

		$STH = $this->pdo->query("SELECT `trim` FROM `forums` WHERE `section_id`='$section_id' ORDER BY `trim` DESC LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$tmp = $STH->fetch();
		if($row->trim == $tmp->trim) {
			return true;
		}

		$STH = $this->pdo->prepare("UPDATE `forums` SET `trim`=:trim WHERE `trim`=:trim2 and `section_id`=:section_id LIMIT 1");
		$STH->execute(array('trim' => $row->trim, 'trim2' => $row->trim + 1, 'section_id' => $section_id));

		$STH = $this->pdo->prepare("UPDATE `forums` SET `trim`=:trim WHERE id=:id and section_id=:section_id LIMIT 1");
		$STH->execute(array('trim' => $row->trim + 1, 'id' => $id, 'section_id' => $section_id));

		return true;
	}

	public function edit_topic($id, $name, $text, $img) {
		$STH = $this->pdo->query("SELECT `author` FROM `forums__topics` WHERE `id`='$id' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$topic = $STH->fetch();
		if(!is_worthy("e") and ($_SESSION['id'] != $topic->author)) {
			return false;
		}

		$STH = $this->pdo->prepare("UPDATE `forums__topics` SET `name`=:name, `text`=:text, `img`=:img, `edited_by`=:edited_by, `edited_time`=:edited_time WHERE `id`=:id LIMIT 1");
		$STH->execute(array(':name'        => $name,
		                    ':text'        => $text,
		                    ':img'         => $img,
		                    ':edited_by'   => $_SESSION['id'],
		                    ':edited_time' => date("Y-m-d H:i:s"),
		                    ':id'          => $id));

		return true;
	}

	public function edit_topic_forum($id, $forum_id) {
		$STH = $this->pdo->query("SELECT `forum_id` FROM `forums__topics` WHERE `id`='$id' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$old = $STH->fetch();

		$STH = $this->pdo->prepare("UPDATE `forums__topics` SET `forum_id`=:forum_id WHERE `id`='$id' LIMIT 1");
		$STH->execute(array('forum_id' => $forum_id));

		$this->set_forum_last_message($forum_id);
		$this->set_forum_last_message($old->forum_id);

		return true;
	}

	public function edit_topic_status($id, $status) {
		$STH = $this->pdo->prepare("UPDATE `forums__topics` SET `status`=:status WHERE id='$id' LIMIT 1");
		$STH->execute(array('status' => $status));

		return true;
	}

	public function edit_answer($id, $text) {
		$STH = $this->pdo->query("SELECT `id`, `author`, `date` FROM `forums__messages` WHERE `id`='$id'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$mess = $STH->fetch();
		if(empty($mess->id)) {
			return false;
		}
		if((!is_worthy("r")) and (($_SESSION['id'] != $mess->author) or ((time() - strtotime($mess->date)) > (60 * 60)))) {
			return false;
		}

		$STH = $this->pdo->prepare("UPDATE `forums__messages` SET `text`=:text, `edited_by`=:edited_by, `edited_time`=:edited_time WHERE `id`=:id LIMIT 1");
		$STH->execute(array(':text' => $text, ':edited_by' => $_SESSION['id'], ':edited_time' => date("Y-m-d H:i:s"), ':id' => $id));

		return true;
	}

	/* Удаление раздела / форума / темы / сообщения
	==================================================*/
	public function dell_section($section_id) {
		$STH = $this->pdo->query("SELECT `trim` FROM `forums__section` WHERE `id`='$section_id' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(empty($row->trim)) {
			return false;
		}

		$this->pdo->exec("DELETE FROM `forums__section` WHERE `id`='$section_id' LIMIT 1");

		$STH = $this->pdo->prepare("SELECT `id`, `trim` FROM `forums__section` WHERE `trim`>:trim");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':trim' => $row->trim));
		while($row = $STH->fetch()) {
			$STH2 = $this->pdo->prepare("UPDATE `forums__section` SET `trim`=:trim WHERE `id`=:id LIMIT 1");
			$STH2->execute(array(':trim' => $row->trim - 1, ':id' => $row->id));
		}

		$STH = $this->pdo->prepare("SELECT `id` FROM `forums` WHERE `section_id`=:section_id");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':section_id' => $section_id));
		while($row = $STH->fetch()) {
			$this->dell_forum($row->id);
		}

		$this->global_recount_reit();

		return true;
	}

	public function dell_forum($forum_id, $type = 1) {
		if($type == 2) {
			$STH = $this->pdo->query("SELECT `trim`, `section_id` FROM `forums` WHERE id='$forum_id' LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$row = $STH->fetch();
			if(empty($row->trim)) {
				return true;
			}

			$STH = $this->pdo->prepare("SELECT `id`, `trim` FROM `forums` WHERE `trim`>:trim AND `section_id`=:section_id ");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':trim' => $row->trim, ':section_id' => $row->section_id));
			while($row = $STH->fetch()) {
				$STH2 = $this->pdo->prepare("UPDATE `forums` SET `trim`=:trim WHERE `id`=:id LIMIT 1");
				$STH2->execute(array(':trim' => $row->trim - 1, ':id' => $row->id));
			}
		}

		$STH = $this->pdo->prepare("SELECT `id` FROM `forums__topics` WHERE `forum_id`=:forum_id");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':forum_id' => $forum_id));
		while($topic = $STH->fetch()) {
			$STH2 = $this->pdo->prepare("SELECT `id` FROM `forums__messages` WHERE `topic`=:topic");
			$STH2->setFetchMode(PDO::FETCH_OBJ);
			$STH2->execute(array(':topic' => $topic->id));
			while($message = $STH2->fetch()) {
				$STH3 = $this->pdo->prepare("DELETE FROM `events` WHERE `data_id`=:data_id AND type = '5' LIMIT 1");
				$STH3->execute(array(':data_id' => $message->id));

				$STH3 = $this->pdo->prepare("DELETE FROM `thanks` WHERE `mes_id`=:mes_id  AND `topic` = '0'");
				$STH3->execute(array(':mes_id' => $message->id));
			}

			$STH2 = $this->pdo->prepare("DELETE FROM `forums__messages` WHERE `topic`=:topic");
			$STH2->execute(array(':topic' => $topic->id));

			$STH2 = $this->pdo->prepare("DELETE FROM `thanks` WHERE `mes_id`=:mes_id  AND `topic` = '1'");
			$STH2->execute(array(':mes_id' => $topic->id));

			$STH2 = $this->pdo->prepare("DELETE FROM `events` WHERE `data_id`=:data_id AND type = '4' LIMIT 1");
			$STH2->execute(array(':data_id' => $topic->id));
		}

		$STH = $this->pdo->prepare("DELETE FROM `forums__topics` WHERE `forum_id`=:forum_id");
		$STH->execute(array(':forum_id' => $forum_id));

		$STH = $this->pdo->prepare("DELETE FROM `forums` WHERE `id`=:id LIMIT 1");
		$STH->execute(array(':id' => $forum_id));

		return true;
	}

	public function dell_topic($topic_id) {
		$STH = $this->pdo->prepare("SELECT `id` FROM `forums__messages` WHERE `topic`=:topic");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':topic' => $topic_id));
		while($row = $STH->fetch()) {
			$this->dell_answer($row->id, 2);
		}

		$STH = $this->pdo->prepare("DELETE FROM `thanks` WHERE `mes_id`=:mes_id  AND `topic` = '1'");
		$STH->execute(array(':mes_id' => $topic_id));

		$STH = $this->pdo->prepare("DELETE FROM `events` WHERE `data_id`=:data_id AND type = '4' LIMIT 1");
		$STH->execute(array(':data_id' => $topic_id));

		$STH = $this->pdo->prepare("SELECT `forum_id` FROM `forums__topics` WHERE `id`=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':id' => $topic_id));
		$row      = $STH->fetch();
		$forum_id = $row->forum_id;

		$STH = $this->pdo->prepare("DELETE FROM `forums__topics` WHERE `id`=:id LIMIT 1");
		$STH->execute(array(':id' => $topic_id));

		$this->set_forum_last_message($forum_id, $topic_id);

		return true;
	}

	public function dell_answer($id, $type = 1) {
		$STH = $this->pdo->query("SELECT `topic`, `author` FROM `forums__messages` WHERE `id`='$id' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$topic = $STH->fetch();

		if(empty($topic->topic)) {
			return true;
		}
		$topic_id = $topic->topic;
		$user_id  = $topic->author;

		$this->pdo->exec("DELETE FROM `forums__messages` WHERE `id`='$id' LIMIT 1");

		if($type == 1) {
			$STH = $this->pdo->prepare("SELECT `forum_id` FROM `forums__topics` WHERE `id`=:id LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':id' => $topic_id));
			$row = $STH->fetch();

			$this->set_forum_last_message($row->forum_id, $topic_id);
		}

		$this->set_user_answers($user_id, 0, 2);

		$STH = $this->pdo->prepare("DELETE FROM `thanks` WHERE `mes_id`=:mes_id  AND `topic` = '0'");
		$STH->execute(array(':mes_id' => $id));

		$STH = $this->pdo->prepare("DELETE FROM `events` WHERE `data_id`=:data_id AND type = '5' LIMIT 1");
		$STH->execute(array(':data_id' => $id));

		return true;
	}

	/* Переподсчеты
	=========================================*/
	public function set_forum_last_message($forum_id, $topic_id = 0) {
		$STH = $this->pdo->prepare("SELECT `id` FROM `forums__topics` WHERE `forum_id`=:forum_id");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':forum_id' => $forum_id));
		$i    = 0;
		$data = array();

		while($row = $STH->fetch()) {
			if($topic_id != 0 && $row->id == $topic_id) {
				$data[$i] = $this->set_topic_last_message($row->id, 1);
			} else {
				$data[$i] = $this->get_topic_last_message($row->id);
			}
			$i++;
		}

		$min    = date("Y-m-d H:i:s");
		$min_id = 0;
		if(count($data) > 0) {
			for($i = 0; $i < count($data); $i++) {
				if(strtotime($data[$i]['date']) < strtotime($min)) {
					$min    = $data[$i]['date'];
					$min_id = $data[$i]['msg_id'];
				}
			}
		} else {
			$min_id = null;
		}


		$STH = $this->pdo->prepare("UPDATE `forums` SET `last_msg`=:last_msg WHERE `id`=:id LIMIT 1");
		$STH->execute(array(':last_msg' => $min_id, ':id' => $forum_id));

		return true;
	}

	public function set_topic_last_message($topic_id, $type = 0) {
		$STH = $this->pdo->prepare("SELECT `id`, `date` FROM `forums__messages` WHERE `topic`=:topic ORDER BY `date` DESC LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':topic' => $topic_id));
		$row = $STH->fetch();

		if(empty($row->id)) {
			$last_msg = null;

			$STH = $this->pdo->prepare("SELECT `old_date` FROM `forums__topics` WHERE `id`=:id LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':id' => $topic_id));
			$row = $STH->fetch();

			$date = $row->old_date;
		} else {
			$last_msg = $row->id;
			$date     = $row->date;
		}

		if($type == 0) {
			$STH = $this->pdo->prepare("UPDATE `forums__topics` SET `last_msg`=:last_msg, `date`=:date WHERE `id`=:id LIMIT 1");
			$STH->execute(array(':last_msg' => $last_msg, ':date' => $date, ':id' => $topic_id));
		} else {
			$STH = $this->pdo->prepare("SELECT `id` FROM `forums__messages` WHERE `topic`=:topic");
			$STH->execute(array(':topic' => $topic_id));
			$row     = $STH->fetchAll();
			$answers = count($row);

			$STH = $this->pdo->prepare("UPDATE `forums__topics` SET `last_msg`=:last_msg, `date`=:date, `answers`=:answers WHERE `id`=:id LIMIT 1");
			$STH->execute(array(':last_msg' => $last_msg, ':date' => $date, ':answers' => $answers, ':id' => $topic_id));
		}

		return array('msg_id' => $last_msg, 'date' => $date);
	}

	public function get_topic_last_message($topic_id) {
		$STH = $this->pdo->prepare("SELECT `id`, `date` FROM `forums__messages` WHERE `topic`=:topic ORDER BY `date` DESC LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':topic' => $topic_id));
		$row = $STH->fetch();

		if(empty($row->id)) {
			$last_msg = null;

			$STH = $this->pdo->prepare("SELECT `old_date` FROM `forums__topics` WHERE `id`=:id LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':id' => $topic_id));
			$row = $STH->fetch();

			$date = $row->old_date;
		} else {
			$last_msg = $row->id;
			$date     = $row->date;
		}

		return array('msg_id' => $last_msg, 'date' => $date);
	}

	public function recount_topic_answers($topic_id) {
		$STH = $this->pdo->prepare("SELECT `id` FROM `forums__messages` WHERE `topic` = :topic ");
		$STH->execute(array('topic' => $topic_id));
		$row   = $STH->fetchAll();
		$count = count($row);

		$STH = $this->pdo->prepare("UPDATE `forums__topics` SET `answers`=:answers WHERE `id`=:id LIMIT 1");
		$STH->execute(array(':answers' => $count, ':id' => $topic_id));
	}

	public function global_recount_reit() {
		$STH = $this->pdo->prepare("UPDATE `users` SET `answers`=:answers, `thanks`=:thanks, `reit`=:reit");
		$STH->execute(array('answers' => 0, 'thanks' => 0, 'reit' => 0));

		$info = array();
		$STH  = $this->pdo->query("SELECT `author` FROM `forums__messages`");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		while($row = $STH->fetch()) {
			$id = $row->author;
			if(empty($info[$id]['answers'])) {
				$info[$id]['answers'] = 0;
				$info[$id]['thanks']  = 0;
			}
			$info[$id]['answers']++;
		}

		$STH = $this->pdo->query("SELECT `author` FROM `forums__topics`");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		while($row = $STH->fetch()) {
			$id = $row->author;
			if(empty($info[$id]['answers'])) {
				$info[$id]['answers'] = 0;
				$info[$id]['thanks']  = 0;
			}
			$info[$id]['answers']++;
		}

		$STH = $this->pdo->query("SELECT `forums__topics`.`author` FROM `thanks` LEFT JOIN `forums__topics` ON `thanks`.`mes_id`=`forums__topics`.`id` WHERE `thanks`.`topic` = '1'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		while($row = $STH->fetch()) {
			$id = $row->author;
			if(empty($info[$id]['thanks'])) {
				$info[$id]['thanks'] = 0;
			}
			$info[$id]['thanks']++;
		}

		$STH = $this->pdo->query("SELECT `forums__messages`.`author` FROM `thanks` LEFT JOIN `forums__messages` ON `thanks`.`mes_id`=`forums__messages`.`id` WHERE `thanks`.`topic` = '0'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		while($row = $STH->fetch()) {
			$id = $row->author;
			if(empty($info[$id]['thanks'])) {
				$info[$id]['thanks'] = 0;
			}
			$info[$id]['thanks']++;
		}

		foreach($info as $key => $data) {
			$reit = collect_reit($data['thanks'], $data['answers']);
			$STH  = $this->pdo->prepare("UPDATE `users` SET `answers`=:answers, `thanks`=:thanks, `reit`=:reit WHERE `id`='$key' LIMIT 1");
			$STH->execute(array('answers' => $data['answers'], 'thanks' => $data['thanks'], 'reit' => $reit));
		}

		return true;
	}

	/* +/- сообщений пользователю
	=========================================*/
	public function set_user_answers($user_id, $recount = 0, $type = 1) {
		$STH = $this->pdo->prepare("SELECT `answers`, `thanks` FROM `users` WHERE `id`=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':id' => $user_id));
		$row = $STH->fetch();
		if($type == 1) {
			$answers = $row->answers + 1;
		} else {
			$answers = $row->answers - 1;
		}

		if($recount == 0) {
			$STH = $this->pdo->prepare("UPDATE `users` SET `answers`=:answers WHERE `id`=:user_id LIMIT 1");
			$STH->execute(array('answers' => $answers, 'user_id' => $user_id));
		} else {
			$reit = collect_reit($row->thanks, $answers);
			$STH  = $this->pdo->prepare("UPDATE `users` SET `answers`=:answers, `reit`=:reit WHERE `id`=:user_id LIMIT 1");
			$STH->execute(array('answers' => $answers, 'reit' => $reit, 'user_id' => $user_id));
		}

		return true;
	}

	/* Проверки
	=========================================*/
	private function isset_forum($forum_id) {
		$STH = $this->pdo->prepare("SELECT `id` FROM `forums` WHERE `id`=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':id' => $forum_id));
		$row = $STH->fetch();
		if(empty($row->id)) {
			return false;
		} else {
			return true;
		}
	}

	/* Спасибо
	=========================================*/
	public function thank($id, $type) {
		$user = $_SESSION['id'];

		$STH = $this->pdo->query("SELECT `id` FROM `thanks` WHERE `mes_id` = '$id' and `author` = '$user' and `topic` = '$type'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(isset($row->id)) {
			return false;
		} else {
			if($type == 0) {
				$STH = $this->pdo->query("SELECT `forums__messages`.`author`,`users`.`thanks`,`users`.`answers` FROM `forums__messages` LEFT JOIN `users` ON `forums__messages`.`author` = `users`.`id` WHERE `forums__messages`.`id`='$id'");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$row = $STH->fetch();
			} else {
				$STH = $this->pdo->query("SELECT `forums__topics`.`author`,`users`.`thanks`,`users`.`answers` FROM `forums__topics` LEFT JOIN `users` ON `forums__topics`.`author`=`users`.`id` WHERE `forums__topics`.`id`='$id'");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$row = $STH->fetch();
			}
			if(empty($row->author)) {
				return false;
			} else {
				$author = $row->author;
			}
		}
		if($author == $_SESSION['id']) {
			return false;
		}

		$thanks = $row->thanks + 1;
		$STH    = $this->pdo->prepare("INSERT INTO `thanks` (`mes_id`,`author`,`topic`) values (:mes_id, :author, :topic)");
		$STH->execute(array('mes_id' => $id, 'author' => $user, 'topic' => $type));

		$reit = collect_reit($thanks, $row->answers);
		$STH  = $this->pdo->prepare("UPDATE `users` SET `thanks`=:thanks, `reit`=:reit WHERE `id`='$author' LIMIT 1");
		$STH->execute(array('thanks' => $thanks, 'reit' => $reit));

		return true;
	}

	/* Лоадеры админские
	=========================================*/
	public function get_sections_admin($template, &$users_groups) {
		$STH = $this->pdo->query("SELECT `id`, `name`, `access` FROM `forums__section` ORDER BY `trim`");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$this->tpl->result['content'] = '';
		while($row = $STH->fetch()) {
			$this->tpl->load_template('elements/forum_section.tpl');
			$this->tpl->set("{id}", $row->id);
			$this->tpl->set("{name}", $row->name);
			$this->tpl->set("{groups}", $this->get_groups($users_groups, $row->access, $row->id));
			$this->tpl->compile('content');
			$this->tpl->clear();
		}
		if($this->tpl->result['content'] == '') {
			$this->tpl->result['content'] = '<span class="empty-element">Разделов нет</span>';
		}

		return $this->tpl->result['content'];
	}

	public function get_forums_admin($section_id, $template, $token) {
		$STH = $this->pdo->query("SELECT `id`, `name`, `description`, `img` FROM `forums` WHERE `section_id` = '$section_id' ORDER BY `trim`");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$this->tpl->result['content'] = '';
		while($row = $STH->fetch()) {
			$this->tpl->load_template('elements/forum_settings.tpl');
			$this->tpl->set("{id}", $row->id);
			$this->tpl->set("{name}", $row->name);
			$this->tpl->set("{description}", $row->description);
			$this->tpl->set("{img}", $row->img);
			$this->tpl->set("{section_id}", $section_id);
			$this->tpl->set("{template}", $template);
			$this->tpl->set("{token}", $token);
			$this->tpl->compile('content');
			$this->tpl->clear();
		}
		if($this->tpl->result['content'] == '') {
			$this->tpl->result['content'] = '<tr><td colspan="10">Раздел пуст</td></tr>';
		}

		return $this->tpl->result['content'];
	}

	public function get_sections_list_admin() {
		$i = 0;
		echo '<select id="forum_sections" class="form-control">';
		$STH = $this->pdo->query("SELECT `id`, `name` FROM `forums__section` ORDER BY `trim`");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		while($row = $STH->fetch()) {
			echo '<option value="'.$row->id.'">'.$row->name.'</option>';
			$i++;
		}
		echo '</select>';

		if($i == 0) {
			echo '<span class="empty-element">Разделов нет</span>';
		}

		return true;
	}

	public function get_forums_list($forum_id) {
		$i = 0;

		$STH = $this->pdo->prepare("SELECT `section_id` FROM `forums` WHERE `id`=:forum_id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':forum_id' => $forum_id));
		$row = $STH->fetch();

		echo '<select id="forums" class="form-control">';
		$STH = $this->pdo->query("SELECT `id`, `name` FROM `forums` WHERE `section_id`=$row->section_id ORDER BY `section_id`");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		while($row = $STH->fetch()) {
			if($forum_id == $row->id) {
				echo '<option value="'.$row->id.'" selected>'.$row->name.'</option>';
			} else {
				echo '<option value="'.$row->id.'">'.$row->name.'</option>';
			}
			$i++;
		}
		echo '</select>';

		if($i == 0) {
			echo '<span class="empty-element">Форумов нет</span>';
		}

		return true;
	}

	/* Лоадеры пользовательские
	=========================================*/
	public function get_forums() {
		$STH = $this->pdo->query("SELECT * FROM `forums__section` ORDER BY `trim`");
		$STH->execute();
		$forums       = $STH->fetchAll();
		$count_forums = count($forums);
		global $users_groups;

		$this->tpl->result['content'] = '';
		if($count_forums != 0) {
			for($i_forums = 0; $i_forums < $count_forums; $i_forums++) {
				if($this->have_rights($forums[$i_forums]['access'])) {
					$section_id = $forums[$i_forums]['id'];
					$STH        = $this->pdo->query("SELECT forums.id AS id1,forums.img,forums.last_msg,forums.name AS n1,forums.description,forums__topics.name AS n2,forums__topics.id AS id2,forums__messages.author,forums__messages.date,users.login,users.avatar,users.rights FROM forums
										LEFT JOIN forums__topics ON forums.last_msg = forums__topics.last_msg
										LEFT JOIN forums__messages ON forums.last_msg = forums__messages.id
										LEFT JOIN users ON forums__messages.author = users.id
										WHERE forums.section_id='$section_id' ORDER BY forums.trim");
					$STH->execute();
					$forum       = $STH->fetchAll();
					$count_forum = count($forum);
					if($count_forum != 0) {
						$this->tpl->result['forums'] = '';
						for($i_forum = 0; $i_forum < $count_forum; $i_forum++) {
							$this->tpl->load_template('elements/forum.tpl');
							$this->tpl->set("{img}", $forum[$i_forum]['img']);
							$this->tpl->set("{name}", $forum[$i_forum]['n1']);
							$this->tpl->set("{id1}", $forum[$i_forum]['id1']);
							$this->tpl->set("{description}", $forum[$i_forum]['description']);

							$forum_id = $forum[$i_forum]['id1'];
							$STH      = $this->pdo->query("SELECT forums__topics.id,forums__topics.name,forums__topics.old_date,forums__topics.author,users.login,users.avatar,users.rights FROM forums__topics LEFT JOIN users ON forums__topics.author = users.id WHERE forums__topics.forum_id = '$forum_id' ORDER BY forums__topics.old_date DESC LIMIT 1");
							$STH->setFetchMode(PDO::FETCH_OBJ);
							$row = $STH->fetch();

							$msg_id     = '';
							$msg_name   = '';
							$msg_login  = '';
							$msg_author = '';
							$msg_avatar = '';
							$msg_date   = '';
							$msg_group  = 0;
							if(isset($row->id) && isset($forum[$i_forum]['last_msg'])) {
								if($forum[$i_forum]['date'] > $row->old_date) {
									$msg_id     = $forum[$i_forum]['id2'];
									$msg_name   = $forum[$i_forum]['n2'];
									$msg_login  = $forum[$i_forum]['login'];
									$msg_author = $forum[$i_forum]['author'];
									$msg_avatar = $forum[$i_forum]['avatar'];
									$msg_group  = $forum[$i_forum]['rights'];
									$msg_date   = expand_date($forum[$i_forum]['date'], 7);
								} else {
									$msg_id     = $row->id;
									$msg_name   = $row->name;
									$msg_login  = $row->login;
									$msg_author = $row->author;
									$msg_avatar = $row->avatar;
									$msg_group  = $row->rights;
									$msg_date   = expand_date($row->old_date, 7);
								}
							} else {
								if(empty($forum[$i_forum]['last_msg']) && empty($row->id)) {
									$this->tpl->set("{msg_id}", '');
								} elseif(empty($forum[$i_forum]['last_msg'])) {
									$msg_id     = $row->id;
									$msg_name   = $row->name;
									$msg_login  = $row->login;
									$msg_author = $row->author;
									$msg_avatar = $row->avatar;
									$msg_group  = $row->rights;
									$msg_date   = expand_date($row->old_date, 7);
								} else {
									$msg_id     = $forum[$i_forum]['id2'];
									$msg_name   = $forum[$i_forum]['n2'];
									$msg_login  = $forum[$i_forum]['login'];
									$msg_author = $forum[$i_forum]['author'];
									$msg_avatar = $forum[$i_forum]['avatar'];
									$msg_group  = $forum[$i_forum]['rights'];
									$msg_date   = expand_date($forum[$i_forum]['date'], 7);
								}
							}
							$group = $users_groups[$msg_group];

							$this->tpl->set("{msg_id}", $msg_id);
							$this->tpl->set("{msg_name}", $msg_name);
							$this->tpl->set("{msg_login}", $msg_login);
							$this->tpl->set("{msg_author}", $msg_author);
							$this->tpl->set("{msg_avatar}", $msg_avatar);
							$this->tpl->set("{msg_date}", $msg_date);
							$this->tpl->set("{gp_color}", $group['color']);
							$this->tpl->set("{gp_name}", $group['name']);
							$this->tpl->compile('forums');
							$this->tpl->clear();
						}
						$this->tpl->load_template('elements/forums.tpl');
						$this->tpl->set("{name}", $forums[$i_forums]['name']);
						$this->tpl->set("{content}", $this->tpl->result['forums']);
						$this->tpl->compile('content');
						$this->tpl->clear();
					}
				}
			}
		}

		if($this->tpl->result['content'] == '') {
			$this->tpl->result['content'] = '<span class="empty-element">Форум пуст</span>';
		}

		return $this->tpl->result['content'];
	}

	public function get_forum($id, $start, $limit = 20) {
		$start = checkStart($start);
		$id    = check($id, "int");
		$limit = check($limit, "int");

		if(empty($start)) {
			$start = 0;
		}
		if(empty($limit)) {
			$limit = 20;
		}

		global $users_groups;

		$i   = 0;
		$STH = $this->pdo->query("SELECT `forums__section`.`access` FROM `forums` LEFT JOIN `forums__section` ON `forums__section`.`id` = `forums`.`section_id` WHERE `forums`.`id`='$id'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		if(!$this->have_rights($row->access)) {
			return "<tr><td colspan='10'>Недостаточно прав</td></tr>";
		}

		$this->tpl->result['content'] = '';
		if($start <= $limit) {
			$STH = $this->pdo->query("SELECT forums__topics.id, forums__topics.name, forums__topics.answers, forums__topics.img, forums__topics.views, forums__topics.status, forums__topics.author AS topic_author, forums__topics.last_msg, 
       							user_topic_info.login AS topic_user_login, user_topic_info.avatar AS topic_user_avatar, user_topic_info.rights AS topic_user_rights, 
       							user_message_info.login AS message_user_login, user_message_info.avatar AS message_user_avatar, user_message_info.rights AS message_user_rights, 
       							forums__messages.date, forums__messages.author AS message_author FROM forums__topics 
								LEFT JOIN users AS user_topic_info ON forums__topics.author = user_topic_info.id 
								LEFT JOIN forums__messages ON forums__topics.last_msg = forums__messages.id
								LEFT JOIN users AS user_message_info ON forums__messages.author = user_message_info.id 
							  	WHERE forums__topics.forum_id = $id and (forums__topics.status = 3 or forums__topics.status= 4) ORDER BY forums__topics.id DESC");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			while($row = $STH->fetch()) {
				$i++;
				if($i == 1) {
					$this->tpl->load_template('elements/topic_fixed.tpl');
					$this->tpl->set("{title}", '1');
					$this->tpl->compile('content');
					$this->tpl->clear();
				}
				$group = $users_groups[$row->topic_user_rights];
				$this->tpl->load_template('elements/topic_fixed.tpl');
				$this->tpl->set("{title}", '0');
				$this->tpl->set("{id}", $row->id);
				$this->tpl->set("{img}", $row->img);
				$this->tpl->set("{name}", $row->name);
				$this->tpl->set("{views}", $row->views);
				$this->tpl->set("{status}", $row->status);
				$this->tpl->set("{answers}", $row->answers);
				$this->tpl->set("{topic_author}", $row->topic_author);
				$this->tpl->set("{topic_login}", $row->topic_user_login);
				$this->tpl->set("{topic_avatar}", $row->topic_user_avatar);
				$this->tpl->set("{topic_user_gp_color}", $group['color']);
				$this->tpl->set("{topic_user_gp_name}", $group['name']);
				if(empty($row->last_msg)) {
					$this->tpl->set("{last_msg}", '');
				} else {
					$group = $users_groups[$row->message_user_rights];
					$this->tpl->set("{last_msg}", $row->last_msg);
					$this->tpl->set("{date}", expand_date($row->date, 7));
					$this->tpl->set("{msg_author}", $row->message_author);
					$this->tpl->set("{msg_login}", $row->message_user_login);
					$this->tpl->set("{msg_avatar}", $row->message_user_avatar);
					$this->tpl->set("{msg_user_gp_color}", $group['color']);
					$this->tpl->set("{msg_user_gp_name}", $group['name']);
				}
				$this->tpl->compile('content');
				$this->tpl->clear();
			}
		}
		$i2  = 0;
		$STH = $this->pdo->query("SELECT forums__topics.id, forums__topics.name, forums__topics.answers, forums__topics.img, forums__topics.views, forums__topics.status, forums__topics.author AS topic_author, forums__topics.last_msg, 
       							user_topic_info.login AS topic_user_login, user_topic_info.avatar AS topic_user_avatar, user_topic_info.rights AS topic_user_rights, 
       							user_message_info.login AS message_user_login, user_message_info.avatar AS message_user_avatar, user_message_info.rights AS message_user_rights,  
       							forums__messages.date, forums__messages.author AS message_author FROM forums__topics 
								LEFT JOIN users AS user_topic_info ON forums__topics.author = user_topic_info.id 
								LEFT JOIN forums__messages ON forums__topics.last_msg = forums__messages.id
								LEFT JOIN users AS user_message_info ON forums__messages.author = user_message_info.id 
								WHERE forums__topics.forum_id = $id and (forums__topics.status != 3) and (forums__topics.status != 4) ORDER BY forums__topics.date DESC LIMIT $start, $limit");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		while($row = $STH->fetch()) {
			$i2++;
			if($i2 == 1) {
				$this->tpl->load_template('elements/topic.tpl');
				$this->tpl->set("{title}", '1');
				$this->tpl->compile('content');
				$this->tpl->clear();
			}
			$group = $users_groups[$row->topic_user_rights];
			$this->tpl->load_template('elements/topic.tpl');
			$this->tpl->set("{title}", '0');
			$this->tpl->set("{id}", $row->id);
			$this->tpl->set("{img}", $row->img);
			$this->tpl->set("{name}", $row->name);
			$this->tpl->set("{views}", $row->views);
			$this->tpl->set("{status}", $row->status);
			$this->tpl->set("{answers}", $row->answers);
			$this->tpl->set("{topic_author}", $row->topic_author);
			$this->tpl->set("{topic_login}", $row->topic_user_login);
			$this->tpl->set("{topic_avatar}", $row->topic_user_avatar);
			$this->tpl->set("{topic_user_gp_color}", $group['color']);
			$this->tpl->set("{topic_user_gp_name}", $group['name']);
			if(empty($row->last_msg)) {
				$this->tpl->set("{last_msg}", '');
			} else {
				$group = $users_groups[$row->message_user_rights];
				$this->tpl->set("{last_msg}", $row->last_msg);
				$this->tpl->set("{date}", expand_date($row->date, 7));
				$this->tpl->set("{msg_author}", $row->message_author);
				$this->tpl->set("{msg_login}", $row->message_user_login);
				$this->tpl->set("{msg_avatar}", $row->message_user_avatar);
				$this->tpl->set("{msg_user_gp_color}", $group['color']);
				$this->tpl->set("{msg_user_gp_name}", $group['name']);
			}
			$this->tpl->compile('content');
			$this->tpl->clear();
		}
		if($i == 0 and $i2 == 0) {
			$this->tpl->result['content'] = "<tr><td colspan='10'>Форум пуст</td></tr>";
		}

		return $this->tpl->result['content'];
	}

	public function get_answers($id, $start, $limit, $script, $link) {
		$id     = check($id, "int");
		$limit  = check($limit, "int");
		$script = check($script, null);
		$link   = check($link, null);
		$start  = checkStart($start);

		if(empty($limit)) {
			$limit = 10;
		}
		if(empty($start)) {
			$start = 0;
		}

		$this->tpl->result['answers'] = '';

		global $users_groups;
		global $messages;

		$STH = $this->pdo->prepare("SELECT `forum_id` FROM `forums__topics` WHERE `id`=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':id' => $id));
		$row = $STH->fetch();
		if(empty($row->forum_id)) {
			return "Темы не существует";
		}

		$STH = $this->pdo->query("SELECT `forums__section`.`access` FROM `forums` LEFT JOIN `forums__section` ON `forums__section`.`id` = `forums`.`section_id` WHERE `forums`.`id`='$row->forum_id'");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$access = $STH->fetch();
		if(!$this->have_rights($access->access)) {
			return "Недостаточно прав";
		}

		//$STH = $this->pdo->query("SELECT `forums__messages`.*,`users`.`login`,`users`.`rights`,`users`.`reit`,`users`.`avatar`,`users`.`signature`,`users`.`answers`,`users`.`thanks` FROM `forums__messages` LEFT JOIN `users` ON `forums__messages`.`author` = `users`.`id` WHERE `forums__messages`.`topic` = '$id' ORDER BY `date` LIMIT $start, $limit");
		$STH->execute();
		$STH = $this->pdo->query("SELECT `forums__messages`.*,`users`.`login`,`users`.`rights`,`users`.`reit`,`users`.`avatar`,`users`.`signature`,`users`.`answers`,`users`.`thanks` FROM `forums__messages` INNER JOIN `users` ON `forums__messages`.`author` = `users`.`id` WHERE `forums__messages`.`topic` = '$id' ORDER BY `date` LIMIT $start, $limit");
		$STH->execute();
		$row   = $STH->fetchAll();
		$count = count($row);
		for($j = 0; $j < $count; $j++) {
			$messages_id = $row[$j]['id'];
			$STH         = $this->pdo->query("SELECT `thanks`.*,`users`.`login` FROM `thanks` LEFT JOIN `users` ON `thanks`.`author` = `users`.`id` WHERE `thanks`.`topic` = '0' and `thanks`.`mes_id` = '$messages_id'");
			$STH->execute();
			$thanks       = $STH->fetchAll();
			$count_thanks = count($thanks);

			$group = $users_groups[$row[$j]['rights']];
			$this->tpl->load_template('elements/forum_message.tpl');

			if($count_thanks > 0) {
				$thanks_str = '<div id="thanks_'.$messages_id.'" class="thank_str">'.$messages['Thanks'].': ';
				for($i = 0; $i < $count_thanks; $i++) {
					if(($count_thanks - $i) == 1) {
						$thanks_str .= '<a href="../profile?id='.$thanks[$i]['author'].'">'.$thanks[$i]['login'].'</a> ';
					} else {
						$thanks_str .= '<a href="../profile?id='.$thanks[$i]['author'].'">'.$thanks[$i]['login'].'</a>, ';
					}
				}
				$thanks_str .= '</div>';
			} else {
				$thanks_str = '<div id="thanks_'.$messages_id.'" class="disp-n thank_str"></div>';
			}

			$this->tpl->set("{signature}", str_replace("'", "&#8216;", $row[$j]['signature']));
			$this->tpl->set("{id}", $row[$j]['id']);
			$this->tpl->set("{topic_id}", $id);
			$this->tpl->set("{author}", $row[$j]['author']);
			$this->tpl->set("{login}", $row[$j]['login']);
			$this->tpl->set("{date}", expand_date($row[$j]['date'], 7));
			$this->tpl->set("{avatar}", $row[$j]['avatar']);
			$this->tpl->set("{gp_color}", $group['color']);
			$this->tpl->set("{gp_name}", $group['name']);
			$this->tpl->set("{reit}", $row[$j]['reit']);
			$this->tpl->set("{answers}", $row[$j]['answers']);
			$this->tpl->set("{thanks}", $row[$j]['thanks']);
			$this->tpl->set("{text}", $row[$j]['text']);
			$this->tpl->set("{link}", $link);
			$this->tpl->set("{mess_thanks}", $thanks_str);
			$this->tpl->set("{author_id}", $row[$j]['author']);
			if(isset($_SESSION['id'])) {
				$this->tpl->set("{my_id}", $_SESSION['id']);
			}
			if($row[$j]['edited_by'] != 0) {
				$STH = $this->pdo->prepare("SELECT `id`,`login` FROM `users` WHERE `id`=:edited_by LIMIT 1");
				$STH->setFetchMode(PDO::FETCH_OBJ);
				$STH->execute(array(':edited_by' => $row[$j]['edited_by']));
				$edited_by = $STH->fetch();
				$this->tpl->set("{edited_by_id}", $edited_by->id);
				$this->tpl->set("{edited_by_login}", $edited_by->login);
				$this->tpl->set("{edited_time}", expand_date($row[$j]['edited_time'], 7));
			} else {
				$this->tpl->set("{edited_by_id}", "");
				$this->tpl->set("{edited_by_login}", "");
				$this->tpl->set("{edited_time}", "");
			}
			if((time() - strtotime($row[$j]['date'])) < (60 * 15)) {
				$this->tpl->set("{check}", '1');
			} else {
				$this->tpl->set("{check}", '2');
			}
			$this->tpl->compile('answers');
			$this->tpl->clear();
		}

		if($script == 1) {
			$count = $count - 1;
			if(isset($row[$count]['id'])) {
				$script = '<script>scrollToBox("#answer_'.$row[$count]['id'].'");</script>';
			} else {
				$script = '';
			}
		}

		return $this->tpl->result['answers'].$script;
	}

	public function get_groups(&$users_groups, $ids = ';', $id = '') {
		if($ids == ';') {
			$user_groups_str = '<label class="btn btn-default btn-sm active" id="access_all'.$id.'" onclick="section_access(\'all\', '.$id.');">
									<input type="checkbox" autocomplete="off" name="0" checked> Все
								</label>';
		} else {
			$user_groups_str = '<label class="btn btn-default btn-sm" id="access_all'.$id.'" onclick="section_access(\'all\', '.$id.');">
									<input type="checkbox" autocomplete="off" name="0"> Все
								</label>';
		}

		$ids = explode(";", $ids);

		foreach($users_groups as &$value) {
			if($value['id'] != 0) {
				if(in_array($value['id'], $ids)) {
					$user_groups_str .= '<label class="btn btn-default btn-sm active">
											<input type="checkbox" autocomplete="off" name="'.$value['id'].'" checked> '.$value['name'].'
										</label>';
				} else {
					$user_groups_str .= '<label class="btn btn-default btn-sm" onclick="section_access('.$value['id'].', '.$id.');">
											<input type="checkbox" autocomplete="off" name="'.$value['id'].'"> '.$value['name'].'
										</label>';
				}
			}
		}

		return $user_groups_str;
	}

	public function have_rights($groups) {
		if($groups == ';') {
			return true;
		}
		$groups = explode(";", $groups);
		if(empty($_SESSION['rights'])) {
			return false;
		}
		if(in_array($_SESSION['rights'], $groups)) {
			return true;
		} else {
			return false;
		}
	}
}