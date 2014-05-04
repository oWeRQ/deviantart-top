<?php

class DeviantartTop
{
	const RECORD_LOCAL = 1;
	const RECORD_SERVER = 2;
	const RECORD_ALL = 3;

	public $mongo;
	public $db;
	public $images = null;

	public function __construct()
	{
		$this->mongo = new MongoClient();
		$this->db = $this->mongo->deviantart_top;
	}

	public function score($pos, $n)
	{
		if ($pos > $n)
			return 0;

		//return $pos * $pos / $n;
		return pow($pos, 1.5) * 10 / $n;
	}

	public function wilson_score($pos, $n)
	{
		$z = 1.64485; //1.0 = 85%, 1.6 = 95%
		$phat = $pos / $n;
		return ($phat + $z*$z/(2*$n) - $z*sqrt(($phat*(1-$phat) + $z*$z/(4*$n))/$n)) / (1 + $z*$z/$n);
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

	public function getImages()
	{
		if ($this->images === null) {
			$this->images = $this->getData('images');
		}

		return $this->images;
	}

	public function saveData($name, $id, $record, $record_type = 3)
	{
		$dbRecord = $this->db->$name->findOne(['id' => $id]);

		if ($dbRecord !== null) {
			$update = [];

			if ($record_type & self::RECORD_LOCAL) {
				$update['local'] = $record;
				$update['local_updated'] = time();
			} elseif ($record_type & self::RECORD_SERVER) {
				$update['server'] = $record;
				$update['server_updated'] = time();
			}

			$this->db->$name->update(['id' => $id], [
				'$set' => $update,
			]);
		} else {
			$insert = [
				'id' => $id,
			];

			if ($record_type & self::RECORD_LOCAL) {
				$insert['local'] = $record;
				$insert['local_created'] = time();
				$insert['local_updated'] = time();
				$insert['local_deleted'] = false;
			} elseif ($record_type & self::RECORD_SERVER) {
				$insert['server'] = $record;
				$insert['server_created'] = time();
				$insert['server_updated'] = time();
				$insert['server_deleted'] = false;
			}

			$this->db->$name->insert($insert);
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

	public function getTop(array $query, array $topQuery)
	{
		Profile::begin('DeviantartTop::getTop');

		$top = array();

		$profiles = $this->getData('profiles');

		if (!empty($topQuery['username'])) {
			$pages = 1;

			if (isset($profiles[$topQuery['username']])) {
				$profile = $profiles[$topQuery['username']];
				$images = $this->getFavImages($topQuery['username'], $query);
				$favourites = count($images);

				$top[] = array(
					'username' => $topQuery['username'],
					'percent' => $favourites/$profile['deviations']*100,
					'score' => $this->score($favourites, $profile['deviations']),
					'wilson_score' => $this->wilson_score($favourites, $profile['deviations']),
					'favourites' => $favourites,
					'deviations' => $profile['deviations'],
					'images' => array_slice($images, $topQuery['imagesOffset'], $topQuery['imagesLimit']),
				);
			}
		} else {
			foreach ($profiles as $profile) {
				if (isset($profile['deviations']) && $profile['deviations'] >= $topQuery['minDevia']) {
					$images = $this->getFavImages($profile['username'], $query);
					$favourites = count($images);

					if ($favourites !==0 && $favourites >= $topQuery['minFavs'] && ($topQuery['maxFavs'] === 0 || $favourites <= $topQuery['maxFavs'])) {
						$top[] = array(
							'username' => $profile['username'],
							'percent' => $favourites/$profile['deviations']*100,
							'score' => $this->score($favourites, $profile['deviations']),
							'wilson_score' => $this->wilson_score($favourites, $profile['deviations']),
							'favourites' => $favourites,
							'deviations' => $profile['deviations'],
							'images' => array_slice($images, $topQuery['imagesOffset'], $topQuery['imagesLimit']),
						);
					}
				}
			}

			$sort = $topQuery['sort'];
			$sortDir = $topQuery['sortDir'];

			usort($top, function($a, $b) use($sort, $sortDir){
				if ($a[$sort] == $b[$sort])
					return strcmp($a['username'], $b['username']);

				return ($a[$sort] > $b[$sort]) ? -$sortDir : $sortDir;
			});
		}

		Profile::end('DeviantartTop::getTop');

		return $top;
	}
}