<?php
class EventsRibbon {
	private $pdo;
	private $tpl;
	private $categories = array(
		0 => 'Все', 
		1 => 'Новости', 
		2 => 'Комментарии', 
		3 => 'Пользователи', 
		4 => 'Темы на форуме', 
		5 => 'Ответы на форуме',
		6 => 'Остальное'
	);

	function __construct($pdo = null, $tpl = null) {
		if(isset($tpl)) {
			$this->tpl = $tpl;
		}
		if(isset($pdo)) {
			$this->pdo = $pdo;
		}
	}

	public function event_of_dell_event($id) {
		$STH = $this->pdo->prepare("SELECT `content` FROM `events` WHERE `id`=:id LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array( ':id' => $id ));
		$row = $STH->fetch();

		$content = 'Администратор <a href="../profile?id='.$_SESSION['id'].'">'.$_SESSION['login'].'</a> удалил уведомление о событии: '.$row->content;
		$STH = $this->pdo->prepare("INSERT INTO `events` (`date`,`content`,`link`,`data_id`,`type`,`author`) values (:date, :content, :link, :data_id, :type, :author)");  
		$STH->execute(array( ':date' => time(), ':content' => $content, ':link' => '#', ':data_id' => '0', ':type' => '6', ':author' => $_SESSION['id'] ));

		return true;
	}

	public function get_category_name($id = 0) {
		if(count($this->categories) <= $id) {
			$id = 0;
		}

		return $this->categories[$id];
	}

	public function get_categories($id = 0) {
		$data = '';
		for ($i=0; $i < count($this->categories); $i++) {
			if(isset($id) && $id == $i){
				$data .= '<li class="active"><a href="../events?class='.$i.'">'.$this->categories[$i].'</a></li>';
			} else {
				$data .= '<li><a href="../events?class='.$i.'">'.$this->categories[$i].'</a></li>';
			}
		}

		return $data;
	}

	public function get_events($start = 0, $class = 0, $limit = 20) {
		$start = checkJs($start, "int");
		$class = checkJs($class, "int");
		$limit = checkJs($limit, "int");

		if(empty($start)) {
			$start = 0;
		}
		if(empty($class)) {
			$class = 0;
		}
		if(empty($limit)) {
			$limit = 20;
		}

		global $users_groups;

		if(empty($class)){
			$STH = $this->pdo->query("SELECT 
									    `events`.*, 
									    `users`.`login`, 
									    `users`.`avatar`, 
									    `users`.`rights` 
									FROM 
									    `events` 
									        LEFT JOIN 
									            `users` 
									                ON 
									                    `events`.`author` = `users`.`id` 
											LEFT JOIN 
									            users__groups 
									                ON 
									                    users.rights = users__groups.id 
									WHERE 
										users__groups.name IS NOT NULL 
									ORDER BY `events`.`date` DESC LIMIT $start, $limit"); $STH->setFetchMode(PDO::FETCH_OBJ);
		} else {
			$STH = $this->pdo->query("SELECT 
    									`events`.*, 
									    `users`.`login`, 
									    `users`.`avatar`, 
									    `users`.`rights` 
									FROM 
    									`events` 
		                                    LEFT JOIN 
		                                        `users`
		                                             ON 
		                                                `events`.`author` = `users`.`id` 
											LEFT JOIN 
		                                    	users__groups 
		                                            ON 
		                                                users.rights = users__groups.id 
									WHERE 
									    users__groups.name IS NOT NULL 
											AND `events`.`type` = $class 
									ORDER BY `events`.`date` DESC LIMIT $start, $limit"); $STH->setFetchMode(PDO::FETCH_OBJ);
		}
		$this->tpl->result['content'] = '';
		while($row = $STH->fetch()) {
			if($this->have_rights($row->access)) {
				$this->tpl->load_template('elements/event.tpl');
				$this->tpl->set("{id}", $row->id);
				$this->tpl->set("{login}", $row->login);
				$this->tpl->set("{avatar}", $row->avatar);
				$this->tpl->set("{user_id}", $row->author);
				$this->tpl->set("{date}", expand_date($row->date, 7));
				$this->tpl->set("{content}", $row->content);
				$this->tpl->set("{data_id}", $row->data_id);
				$this->tpl->set("{link}", $row->link);
				$this->tpl->set("{gp_name}", $users_groups[$row->rights]['name']);
				$this->tpl->set("{gp_color}", $users_groups[$row->rights]['color']);
				$this->tpl->compile( 'content' );
				$this->tpl->clear();
			}
		}
		if($this->tpl->result['content'] == '') {
			$this->tpl->result['content'] = '<span class="empty-element">Событий нет</span>';
		}

		return $this->tpl->result['content'];
	}

	public function new_new($id, $name) {
		$content = 'Добавлена новость - '.$name;
		$link = '../news/new?id='.$id;
		$data_id = $id;
		$type = 1;
		$author = $_SESSION['id'];

		$STH = $this->pdo->prepare("INSERT INTO `events` (`date`,`content`,`link`,`data_id`,`type`,`author`) values (:date, :content, :link, :data_id, :type, :author)");  
		$STH->execute(array( ':date' => time(), ':content' => $content, ':link' => $link, ':data_id' => $data_id, ':type' => $type, ':author' => $author ));

		return true;
	}

	public function new_new_comment($id, $new_name, $new_id) {
		$content = 'Добавлен комментарий к новости "'.$new_name.'"';
		$link = '../news/new?id='.$new_id.'#message_id_'.$id;
		$data_id = $id;
		$type = 2;
		$author = $_SESSION['id'];

		$STH = $this->pdo->prepare("INSERT INTO `events` (`date`,`content`,`link`,`data_id`,`type`,`author`) values (:date, :content, :link, :data_id, :type, :author)");  
		$STH->execute(array( ':date' => time(), ':content' => $content, ':link' => $link, ':data_id' => $data_id, ':type' => $type, ':author' => $author ));

		return true;
	}

	public function new_user($id, $login) {
		$content = 'Зарегистрирован новый пользователь под именем '.$login;
		$link = '../profile?id='.$id;
		$data_id = $id;
		$type = 3;
		$author = $id;

		$STH = $this->pdo->prepare("INSERT INTO `events` (`date`,`content`,`link`,`data_id`,`type`,`author`) values (:date, :content, :link, :data_id, :type, :author)");  
		$STH->execute(array( ':date' => time(), ':content' => $content, ':link' => $link, ':data_id' => $data_id, ':type' => $type, ':author' => $author ));

		return true;
	}

	public function new_topic($id, $name, $access) {
		$content = 'На форуме открыта новая тема - '.$name;
		$link = '../forum/topic?id='.$id;
		$data_id = $id;
		$type = 4;
		$author = $_SESSION['id'];
		$sec_data_id = $id;

		$STH = $this->pdo->prepare("INSERT INTO `events` (`date`,`content`,`link`,`data_id`,`type`,`author`,`access`,`sec_data_id`) values (:date, :content, :link, :data_id, :type, :author, :access, :sec_data_id)");  
		$STH->execute(array( ':date' => time(), ':content' => $content, ':link' => $link, ':data_id' => $data_id, ':type' => $type, ':author' => $author, ':access' => $access, ':sec_data_id' => $sec_data_id ));

		return true;
	}

	public function new_answer($answer, $topic, $topic_name, $access) {
		$content = 'Оставлен новый ответ в теме "'.$topic_name.'"';
		$data_id = $answer;
		$type = 5;
		$author = $_SESSION['id'];

		$STH = $this->pdo->query("SELECT COUNT(*) as count FROM `forums__messages` WHERE `topic` = '$topic'");
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		$row = $STH->fetch();
		$count = $row['count'];
		$limit = 10;
		if($count <= $limit) {
			$link = '../forum/topic?id='.$topic.'#answer_'.$answer;
		} else {
			$page = ceil($count/$limit);
			$link = '../forum/topic?id='.$topic.'&page='.$page.'#answer_'.$answer;
		}
		$sec_data_id = $topic;

		$STH = $this->pdo->prepare("INSERT INTO `events` (`date`,`content`,`link`,`data_id`,`type`,`author`,`access`,`sec_data_id`) values (:date, :content, :link, :data_id, :type, :author, :access, :sec_data_id)");
		$STH->execute(array( ':date' => time(), ':content' => $content, ':link' => $link, ':data_id' => $data_id, ':type' => $type, ':author' => $author, ':access' => $access, ':sec_data_id' => $sec_data_id ));

		return true;
	}

	public function new_events($id, $content) {
		$link = '#'.$id;
		$data_id = $id;
		$type = 6;
		$author = $_SESSION['id'];

		$STH = $this->pdo->prepare("INSERT INTO `events` (`date`,`content`,`link`,`data_id`,`type`,`author`) values (:date, :content, :link, :data_id, :type, :author)");  
		$STH->execute(array( ':date' => time(), ':content' => $content, ':link' => $link, ':data_id' => $data_id, ':type' => $type, ':author' => $author ));
	
		return true;
	}

	public function have_rights($groups) {
		if($groups == ';') {
			return true;
		}
		if(!is_auth()) {
			return false;
		}
		$groups = explode(";", $groups);
		if(in_array($_SESSION['rights'], $groups)) {
			return true;
		} else {
			return false;
		}
	}
}