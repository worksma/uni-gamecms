<?php

class ServerCommands
{
	const CATEGORY_SYSTEM             = 1;
	const CATEGORY_ACTIONS_ON_PLAYERS = 2;
	const CATEGORY_SERVER_MANAGEMENT  = 3;
	const RELOAD_ADMINS_COMMAND_SLUG  = 'reload_admins';

	public static function isCategoryExists($categoryId)
	{
		if(
			in_array(
				$categoryId,
				[
					self::CATEGORY_SYSTEM,
					self::CATEGORY_ACTIONS_ON_PLAYERS,
					self::CATEGORY_SERVER_MANAGEMENT
				]
			)
		) {
			return true;
		} else {
			return false;
		}
	}

	public function getCommandBySlug($slug, $serverId)
	{
		$STH = pdo()->prepare(
			"SELECT * FROM servers__commands WHERE server_id=:server_id AND slug=:slug LIMIT 1"
		);
		$STH->execute([':server_id' => $serverId, ':slug' => $slug]);

		return $STH->fetch(PDO::FETCH_OBJ);
	}

	public function getCommandById($id)
	{
		$STH = pdo()->prepare(
			"SELECT * FROM servers__commands WHERE id=:id LIMIT 1"
		);
		$STH->execute([':id' => $id]);

		return $STH->fetch(PDO::FETCH_OBJ);
	}

	public function getCommands($serverId)
	{
		$STH = pdo()->prepare(
			"SELECT * FROM servers__commands WHERE server_id=:server_id"
		);
		$STH->execute([':server_id' => $serverId]);

		return $STH->fetchAll(PDO::FETCH_OBJ);
	}

	public function getActionOnPlayersCommands($serverId)
	{
		$STH = pdo()->prepare(
			"SELECT * FROM servers__commands WHERE server_id=:server_id AND category=:category"
		);
		$STH->execute(
			[
				':server_id' => $serverId,
				':category'  => self::CATEGORY_ACTIONS_ON_PLAYERS
			]
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);

		return $STH->fetchAll();
	}

	public function getServerManagementCommands($serverId)
	{
		$STH = pdo()->prepare(
			"SELECT * FROM servers__commands WHERE server_id=:server_id AND category=:category"
		);
		$STH->execute(
			[
				':server_id' => $serverId,
				':category'  => self::CATEGORY_SERVER_MANAGEMENT
			]
		);
		$STH->setFetchMode(PDO::FETCH_OBJ);

		return $STH->fetchAll();
	}

	public function getCommandParams($commandId)
	{
		$STH = pdo()->prepare(
			"SELECT * FROM servers__commands_params WHERE command_id=:command_id"
		);
		$STH->execute([':command_id' => $commandId]);

		return $STH->fetchAll(PDO::FETCH_OBJ);
	}

	public function addCommand(
		$command,
		$serverId,
		$title,
		$slug,
		$categoryId
	) {
		$STH = pdo()->prepare(
			"INSERT INTO servers__commands (command, server_id, title, slug, category) values (:command, :server_id, :title, :slug, :category)"
		);
		$STH->execute(
			[
				'command'   => $command,
				'server_id' => $serverId,
				'title'     => $title,
				'slug'      => $slug,
				'category'  => $categoryId
			]
		);
	}

	public function isCategoryIsSystem($categoryId)
	{
		if($categoryId == self::CATEGORY_SYSTEM) {
			return true;
		} else {
			return false;
		}
	}

	public function isCategoryIsActionOnPlayer($categoryId)
	{
		if($categoryId == self::CATEGORY_ACTIONS_ON_PLAYERS) {
			return true;
		} else {
			return false;
		}
	}

	public function isCategoryIsServerManagement($categoryId)
	{
		if($categoryId == self::CATEGORY_SERVER_MANAGEMENT) {
			return true;
		} else {
			return false;
		}
	}

	public function updateCommand(
		$id,
		$command,
		$title,
		$slug,
		$categoryId
	) {
		pdo()->prepare(
			"UPDATE servers__commands SET command=:command, title=:title, slug=:slug, category=:category WHERE id=:id LIMIT 1"
		)->execute(
			[
				':command'  => $command,
				':title'    => $title,
				':slug'     => $slug,
				':category' => $categoryId,
				':id'       => $id
			]
		);
	}

	public function removeCommandParams($commandId)
	{
		pdo()->prepare(
			"DELETE FROM servers__commands_params WHERE command_id=:command_id"
		)->execute([':command_id' => $commandId]);
	}

	public function addCommandParam($commandId, $name, $title)
	{
		pdo()->prepare(
			"INSERT INTO servers__commands_params (command_id, name, title) values (:command_id, :name, :title)"
		)->execute(
			[
				'command_id' => $commandId,
				'name'       => $name,
				'title'      => $title
			]
		);
	}

	public function removeCommand($id)
	{
		pdo()->prepare("DELETE FROM servers__commands WHERE id=:id")
			->execute([':id' => $id]);

		$this->removeCommandParams($id);
	}

	public static function validateParam($param)
	{
		if(
			stristr($param, '"') === false
			&& stristr($param, ';') === false
		) {
			return true;
		} else {
			return false;
		}
	}
}