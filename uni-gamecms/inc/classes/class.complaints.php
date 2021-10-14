<?php

class Complaints
{
	private $pdo;
	private $tpl;

	function __construct($pdo, $tpl = null)
	{
		if(!isset($pdo)) {
			return '[Class Complaints]: No connection to the database';
		}
		if(isset($tpl)) {
			$this->tpl = $tpl;
		}
		$this->pdo = $pdo;
	}

	public function getList($accusedProfileId, $server, $start = 0, $limit = 10)
	{
		global $users_groups;

		$server = check($server, "int");
		$accusedProfileId  = check($accusedProfileId, "int");

		$start  = checkStart($start);
		$limit  = check($limit, "int");

		if(empty($start)) {
			$start = 0;
		}

		if(empty($limit)) {
			$limit = 10;
		}

		if(empty($accusedProfileId)) {
			if(empty($server)) {
				$where = ' ';
			} else {
				$where = ' WHERE complaints.accused_admin_server_id = ' . $server . ' ';
			}
		} else {
			$where = ' WHERE complaints.accused_profile_id = ' . $accusedProfileId . ' ';
		}

		$STH = $this->pdo->query(
			"SELECT 
			    complaints.id,
			    complaints.accused_admin_server_id,
			    complaints.sentence,
			    complaints.accused_admin_nick,
			    complaints.date,
			    complaints.author_id,
    			complaints.accused_profile_id,
			    author.login as author_login,
			    author.avatar as author_avatar,
     			author.rights as author_rights,
    			accused.login as accused_login,
			    accused.avatar as accused_avatar,
    			accused.rights as accused_rights,
			    servers.name as server_name
			FROM 
			    complaints 
			        LEFT JOIN users author
			            ON complaints.author_id = author.id 
			        LEFT JOIN users accused
			            ON complaints.accused_profile_id = accused.id 
			        LEFT JOIN servers 
			            ON complaints.accused_admin_server_id = servers.id 
			$where
			ORDER BY date DESC LIMIT $start, $limit"
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$this->tpl->result['local_content'] = '';
		while($row = $STH->fetch()) {

			$status = self::getComplaintStatus($row->sentence);

			$this->tpl->load_template('elements/complaint.tpl');
			$this->tpl->set("{status}", $status['data']);
			$this->tpl->set("{color}", $status['color']);
			$this->tpl->set("{id}", $row->id);
			$this->tpl->set("{author_id}", $row->author_id);
			$this->tpl->set("{author_login}", $row->author_login);
			$this->tpl->set("{author_avatar}", $row->author_avatar);
			$this->tpl->set("{accused_profile_id}", $row->accused_profile_id);
			$this->tpl->set("{author_group_name}", $users_groups[$row->author_rights]['name']);
			$this->tpl->set("{author_group_color}", $users_groups[$row->author_rights]['color']);
			$this->tpl->set("{accused_login}", $row->accused_login);
			$this->tpl->set("{accused_avatar}", $row->accused_avatar);
			$this->tpl->set("{accused_admin_nick}", $row->accused_admin_nick);
			$this->tpl->set("{accused_admin_server_id}", $row->accused_admin_server_id);
			$this->tpl->set("{accused_group_name}", empty($row->accused_rights) ? '' : $users_groups[$row->accused_rights]['name']);
			$this->tpl->set("{accused_group_color}", empty($row->accused_rights) ? '' : $users_groups[$row->accused_rights]['color']);
			$this->tpl->set("{server_name}", $row->server_name);
			$this->tpl->set("{numberOfOtherComplaints}", self::getNumberOfOtherComplaints($row->accused_profile_id));
			$this->tpl->set("{date}", expand_date($row->date, 7));
			$this->tpl->compile('local_content');
			$this->tpl->clear();
		}

		if($this->tpl->result['local_content'] == '') {
			$this->tpl->result['local_content'] = '<tr><td colspan="10">Жалоб нет</td></tr>';
		}

		return $this->tpl->result['local_content'];
	}

	public static function getNumberOfOtherComplaints($userId)
	{
		$complaints = pdo()->prepare("SELECT COUNT(*) FROM complaints WHERE accused_profile_id = :accused_profile_id");
		$complaints->execute([':accused_profile_id' => $userId]);
		return  $complaints->fetchColumn();
	}

	public function findAccused($serverId, $accused)
	{
		$admins = (new GetData($this->pdo, $this->tpl))->getAdmins($serverId, 0, $accused);

		$result = [];

		foreach($admins as $admin) {
			if($admin['show'] && $admin['active'] == 1) {
				$result[] = [
					'id'        => $admin['id'],
					'user_id'   => $admin['user_id'],
					'avatar'    => $admin['avatar'],
					'login'     => $admin['login'],
					'gp_name'   => $admin['gp_name'],
					'gp_color'  => $admin['gp_color'],
					'name'      => $admin['name'],
					'services'  => $admin['services'],
					'server'    => $admin['server'],
					'server_id' => $admin['server_id'],
				];
			}
		}

		return $result;
	}

	public function getAccusedById($adminId)
	{
		$admins = (new GetData($this->pdo, $this->tpl))->getAdmins(null, null, null, $adminId);

		if(!empty($admins) && $admins[0]['active'] == 1) {
			return $admins[0];
		} else {
			return [];
		}
	}

	public static function getComplaintSentenceText($sentenceId) {
		$data = '';

		switch($sentenceId) {
			case 1:
				$data = 'Оправдан';
				break;
			case 2:
				$data = 'Выдано предупреждение';
				break;
			case 3:
				$data = 'Услуга удалена';
				break;
			case 4:
				$data = 'Услуга заблокирована';
		}

		return $data;
	}

	public static function getComplaintStatus($sentenceId) {
		global $messages;

		$data = $messages['Not_reviewed'];
		$color  = 'warning';

		if(in_array($sentenceId, [1, 2, 3, 4])) {
			$data = self::getComplaintSentenceText($sentenceId);

			if($sentenceId == 1) {
				$color  = 'success';
			}
			if($sentenceId == 2 || $sentenceId == 3 || $sentenceId == 4) {
				$color  = 'danger';
			}
		}

		return [
			'color' => $color,
			'data' => $data
		];
	}
}