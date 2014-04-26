<?php

class DeviantartTop
{
	public static $images = null;
	public static $images_by_author = null;

	public static function score($pos, $n)
	{
		if ($pos > $n)
			return 0;

		//return $pos * $pos / $n;
		return pow($pos, 1.5) * 10 / $n;
	}

	public static function wilson_score($pos, $n)
	{
		$z = 1.64485; //1.0 = 85%, 1.6 = 95%
		$phat = $pos / $n;
		return ($phat + $z*$z/(2*$n) - $z*sqrt(($phat*(1-$phat) + $z*$z/(4*$n))/$n)) / (1 + $z*$z/$n);
	}

	public static function getData($name)
	{
		$filename = "data/$name.json";
		return json_decode(file_get_contents($filename), true);
	}

	public static function getImages()
	{
		if (self::$images === null) {
			self::$images = self::getData('images');
		}

		return self::$images;
	}

	public static function getUserImages($username)
	{
		if (self::$images_by_author === null) {
			$images = self::getImages();

			self::$images_by_author = array();
			foreach ($images as $image) {
				if (!array_key_exists($image['author'], self::$images_by_author))
					self::$images_by_author[$image['author']] = array();

				self::$images_by_author[$image['author']][$image['id']] = $image;
			}
		}

		if (!array_key_exists($username, self::$images_by_author))
			return array();

		return self::$images_by_author[$username];
	}	

	public static function getFavImages($username, array $query)
	{
		$images = self::getUserImages($username);

		if (empty($images))
			return array();

		$images = array_values(array_filter($images, function($image) use($query){
			if ($query['titleCmp'] && !preg_match($query['titleRegex'], $image['title']))
				return false;
			
			foreach ($query['exclude_galleries'] as $gallery) {
				if (in_array($gallery, $image['galleries']))
					return false;
			}

			if (!empty($query['categoriesQuery'])) {
				$categories_count = count(array_intersect($query['categoriesQuery'], $image['categories']));
				if ($categories_count == 0)
					return false;
			}
			
			return true;
		}));

		if (!empty($query['checked_galleries'])) {
			$images = array_values(array_filter($images, function($image) use($query){
				$diff_count = count(array_diff($query['checked_galleries'], $image['galleries']));
				$checked_count = count($query['checked_galleries']);

				if ($query['condition'] == 'or')
					return $diff_count < $checked_count;
				elseif ($query['condition'] == 'and')
					return $diff_count === 0;
				elseif ($query['condition'] == 'only')
					return $diff_count === 0 && $checked_count === count($image['galleries']);
				elseif ($query['condition'] == 'xor')
					return $diff_count === $checked_count-$query['intersect'];
			}));
		}

		usort($images, function($a, $b){
			return $a['id'] > $b['id'] ? -1 : 1;
		});

		return $images;
	}

	public static function getTop(array $query, array $topQuery)
	{
		$top = array();

		$profiles = DeviantartTop::getData('profiles');

		if (!empty($topQuery['username'])) {
			$pages = 1;

			if (isset($profiles[$topQuery['username']])) {
				$profile = $profiles[$topQuery['username']];
				$images = DeviantartTop::getFavImages($topQuery['username'], $query);
				$favourites = count($images);

				$top[] = array(
					'username' => $topQuery['username'],
					'percent' => $favourites/$profile['deviations']*100,
					'score' => DeviantartTop::score($favourites, $profile['deviations']),
					'wilson_score' => DeviantartTop::wilson_score($favourites, $profile['deviations']),
					'favourites' => $favourites,
					'deviations' => $profile['deviations'],
					'images' => array_slice($images, $topQuery['imagesOffset'], $topQuery['imagesLimit']),
				);
			}
		} else {
			$top = array_values(array_filter(array_map(function($profile) use($query, $topQuery){
				if (isset($profile['deviations']) && $profile['deviations'] >= $topQuery['minDevia']) {
					$images = DeviantartTop::getFavImages($profile['username'], $query);
					$favourites = count($images);

					if ($favourites !==0 && $favourites >= $topQuery['minFavs'] && ($topQuery['maxFavs'] === 0 || $favourites <= $topQuery['maxFavs'])) {
						return array(
							'username' => $profile['username'],
							'percent' => $favourites/$profile['deviations']*100,
							'score' => DeviantartTop::score($favourites, $profile['deviations']),
							'wilson_score' => DeviantartTop::wilson_score($favourites, $profile['deviations']),
							'favourites' => $favourites,
							'deviations' => $profile['deviations'],
							'images' => array_slice($images, $topQuery['imagesOffset'], $topQuery['imagesLimit']),
						);
					}
				}
			}, $profiles)));

			$sort = $topQuery['sort'];
			$sortDir = $topQuery['sortDir'];

			usort($top, function($a, $b) use($sort, $sortDir){
				if ($a[$sort] == $b[$sort])
					return strcmp($a['username'], $b['username']);

				return ($a[$sort] > $b[$sort]) ? -$sortDir : $sortDir;
			});
		}

		return $top;
	}
}