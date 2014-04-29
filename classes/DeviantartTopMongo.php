<?php

class DeviantartTopMongo extends DeviantartTop
{
	public $mongo;
	public $db;

	public static $getUserImages_call = 0;

	public function __construct()
	{
		$this->mongo = new MongoClient();
		$this->db = $this->mongo->deviantart_top;
	}

	public function getData($name, array $query = array(), array $cursorQuery = array())
	{
		Profile::begin('DeviantartTopMongo::getData');

		$records = array();

		$query['local_deleted'] = false;

		$cursor = $this->db->$name->find($query);

		foreach ($cursorQuery as $key => $value) {
			$cursor->$key($value);
		}

		foreach ($cursor as $record) {
			$records[$record['id']] = $record['local'];
		}

		Profile::end('DeviantartTopMongo::getData');

		return $records;
	}

	public function saveData($name, $id, $record)
	{
		$dbRecord = $this->db->$name->findOne(['id' => $id]);

		if ($dbRecord !== null) {
			$this->db->$name->update(['id' => $id], [
				'$set' => [
					'local' => $record,
					'local_updated' => time(),
					'server' => $record,
					'server_updated' => time(),
				],
			]);
		} else {
			$this->db->$name->insert([
				'id' => $id,
				'local' => $record,
				'local_created' => time(),
				'local_updated' => time(),
				'local_deleted' => false,
				'server' => $record,
				'server_created' => time(),
				'server_updated' => time(),
				'server_deleted' => false,
			]);
		}
	}

	public function getUserImages($username)
	{
		Profile::begin('DeviantartTopMongo::getUserImages');

		$images = $this->getData('images', ['local.author' => $username]);

		Profile::end('DeviantartTopMongo::getUserImages');

		return $images;
	}

	public function getFavImages($username, array $query)
	{
		Profile::begin('DeviantartTopMongo::getFavImages');

		$mongoQuery = [
			'local.author' => $username,
		];

		if ($query['titleCmp']) {
			$mongoQuery['local.title'] = [
				'$regex' => new MongoRegex($query['titleRegex']),
			];
		}

		if (!empty($query['checked_galleries'])) {
			if ($query['condition'] == 'or') {
				$mongoQuery['local.galleries'] = [
					'$in' => $query['checked_galleries'],
				];
			} elseif ($query['condition'] == 'and') {
				$mongoQuery['local.galleries'] = [
					'$all' => $query['checked_galleries'],
				];
			} elseif ($query['condition'] == 'only') {
				$mongoQuery['local.galleries'] = $query['checked_galleries'];
			}
		}

		if (!empty($query['exclude_galleries']) && $query['condition'] !== 'only') {
			$mongoQuery['local.galleries']['$nin'] = $query['exclude_galleries'];
		}

		if (!empty($query['categoriesQuery'])) {
			$mongoQuery['local.categories'] = [
				'$in' => $query['categoriesQuery'],
			];
		}

		$images = $this->getData('images', $mongoQuery, [
			'sort' => [
				'id' => -1,
			],
		]);

		Profile::end('DeviantartTopMongo::getFavImages');

		return $images;
	}
}