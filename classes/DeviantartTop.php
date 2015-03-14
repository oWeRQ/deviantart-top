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
			$pos = $n;

		//return $pos * $pos / $n;
		return pow($pos, 1.35) * 10 / $n;
	}

	public function wilson_score($pos, $n)
	{
		if ($pos > $n)
			$pos = $n;
		
		$z = 1.64485; //1.0 = 85%, 1.6 = 95%
		$phat = $pos / $n;
		return ($phat + $z*$z/(2*$n) - $z*sqrt(($phat*(1-$phat) + $z*$z/(4*$n))/$n)) / (1 + $z*$z/$n);
	}

	public function getData($name, array $mongoQuery = array(), array $cursorQuery = array())
	{
		Profile::begin('DeviantartTop::getData');

		$records = array();

		$mongoQuery['local_deleted'] = false;

		$cursor = $this->db->$name->find($mongoQuery);

		foreach ($cursorQuery as $key => $value) {
			$cursor->$key($value);
		}

		foreach ($cursor as $record) {
			$records[$record['id']] = $record['local'];
		}

		Profile::end('DeviantartTop::getData');

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
		Profile::begin('DeviantartTop::getUserImages');

		$images = $this->getData('images', ['local.author' => $username]);

		Profile::end('DeviantartTop::getUserImages');

		return $images;
	}

	public function getMongoQuery(array $query)
	{
		$mongoQuery = [];

		if (!empty($query['titleCmp'])) {
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

		return $mongoQuery;
	}

	public function getAuthors(array $query = array())
	{
		Profile::begin('DeviantartTop::getAuthors');

		$mongoQuery = $this->getMongoQuery($query);
		$mongoQuery['local_deleted'] = false;

		if ($mongoQuery) {
			$authors = $this->db->images->aggregate([
				'$match' => $mongoQuery,
			], [
				'$group' => [
					'_id' => '$local.author',
					'images' => ['$sum' => 1],
				],
			]);
		} else {
			$authors = $this->db->images->aggregate([
				'$group' => [
					'_id' => '$local.author',
					'images' => ['$sum' => 1],
				],
			]);
		}

		Profile::end('DeviantartTop::getAuthors');

		return $authors['result'];
	}

	public function getFavImages($username, array $query, $limit = false, $skip = false)
	{
		Profile::begin('DeviantartTop::getFavImages');
		
		$images = [];

		$mongoQuery = $this->getMongoQuery($query);
		$mongoQuery['local.author'] = $username;
		$mongoQuery['local_deleted'] = false;

		$cursor = $this->db->images->find($mongoQuery);

		$cursor->sort(['id' => -1]);

		if ($limit)
			$cursor->limit($limit);

		if ($skip)
			$cursor->skip($skip);

		foreach ($cursor as $image) {
			$images[] = $image['local'];
		}

		Profile::end('DeviantartTop::getFavImages');

		return $images;
	}

	public function getFavImagesCount($username, array $query = array())
	{
		Profile::begin('DeviantartTop::getFavImagesCount');

		$mongoQuery = $this->getMongoQuery($query);
		$mongoQuery['local.author'] = $username;
		$mongoQuery['local_deleted'] = false;

		$images = $this->db->images->count($mongoQuery);

		Profile::end('DeviantartTop::getFavImagesCount');

		return $images;
	}

	public function getTop(array $query, array $topQuery)
	{
		Profile::begin('DeviantartTop::getTop');

		$top = [];

		if (!empty($topQuery['username'])) {
			$count = 1;
			$pages = 1;

			$profile = $this->db->profiles->findOne(['id' => $topQuery['username']]);

			if ($profile) {
				$profile = $profile['local'];

				$favourites = $this->getFavImagesCount($topQuery['username'], $query);
				if ($topQuery['sortTotal'] === 'favourites' || !$profile['deviations']) {
					$deviations = $this->getFavImagesCount($topQuery['username']);
				} else {
					$deviations = $profile['deviations'];
				}

				$top[] = array(
					'random' => rand(),
					'username' => $topQuery['username'],
					'percent' => $favourites / $deviations * 100,
					'score' => $this->score($favourites, $deviations),
					'wilson_score' => $this->wilson_score($favourites, $deviations),
					'favourites' => $favourites,
					'deviations' => $deviations,
					'images' => $this->getFavImages($topQuery['username'], $query, $topQuery['imagesLimit'], $topQuery['imagesOffset']),
				);
			}
		} else {
			Profile::begin('DeviantartTop::getTop step1');
			$profiles = $this->getData('profiles');
			$authors = $this->getAuthors($query);
			$authorsTotal = array_column($this->getAuthors(), 'images', '_id');
			Profile::end('DeviantartTop::getTop step1');
			
			Profile::begin('DeviantartTop::getTop step2');
			foreach ($authors as $author) {
				if (!isset($profiles[$author['_id']]))
					continue;

				$profile = $profiles[$author['_id']];

				if (isset($profile['deviations']) && $profile['deviations'] >= $topQuery['minDevia']) {
					$favourites = $author['images'];
					$deviations = ($topQuery['sortTotal'] === 'favourites' || !$profile['deviations']) ? $authorsTotal[$profile['username']] : $profile['deviations'];

					if ($favourites !== 0 && $favourites >= $topQuery['minFavs'] && ($topQuery['maxFavs'] === 0 || $favourites <= $topQuery['maxFavs'])) {
						$top[] = array(
							'random' => rand(),
							'username' => $profile['username'],
							'percent' => $favourites / $deviations * 100,
							'score' => $this->score($favourites, $deviations),
							'wilson_score' => $this->wilson_score($favourites, $deviations),
							'favourites' => $favourites,
							'deviations' => $deviations,
							'images' => [],
						);
					}
				}
			}
			Profile::end('DeviantartTop::getTop step2');

			Profile::begin('DeviantartTop::getTop step3');
			$sort = $topQuery['sort'];
			$sortDir = $topQuery['sortDir'];

			usort($top, function($a, $b) use($sort, $sortDir){
				if ($a[$sort] == $b[$sort])
					return strcmp($a['username'], $b['username']);

				return ($a[$sort] > $b[$sort]) ? -$sortDir : $sortDir;
			});
			Profile::end('DeviantartTop::getTop step3');
			
			Profile::begin('DeviantartTop::getTop step4');
			$count = count($top);
			$pages = ceil($count / $topQuery['topLimit']);
			$top = array_slice($top, $topQuery['topOffset'], $topQuery['topLimit']);
			Profile::end('DeviantartTop::getTop step4');

			Profile::begin('DeviantartTop::getTop step5');
			foreach ($top as &$topItem) {
				$topItem['images'] = $this->getFavImages($topItem['username'], $query, $topQuery['imagesLimit'], $topQuery['imagesOffset']);
			}
			Profile::end('DeviantartTop::getTop step5');
		}

		Profile::end('DeviantartTop::getTop');

		return [
			'count' => $count,
			'pages' => $pages,
			'authors' => $top,
		];
	}
}