<?php

class DeviantartTopFile extends DeviantartTop
{
	public $images = null;
	public $images_by_author = null;

	public function getData($name)
	{
		Profile::begin('DeviantartTop::getData');

		$filename = "data/$name.json";
		$records = json_decode(file_get_contents($filename), true);

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

	public function getUserImages($username)
	{
		Profile::begin('DeviantartTop::getUserImages');

		if ($this->images_by_author === null) {
			$images = $this->getImages();

			$this->images_by_author = array();
			foreach ($images as $image) {
				if (!array_key_exists($image['author'], $this->images_by_author))
					$this->images_by_author[$image['author']] = array();

				$this->images_by_author[$image['author']][$image['id']] = $image;
			}
		}

		Profile::end('DeviantartTop::getUserImages');

		if (!array_key_exists($username, $this->images_by_author))
			return array();

		return $this->images_by_author[$username];
	}

	public function getFavImages($username, array $query)
	{
		Profile::begin('DeviantartTop::getFavImages');

		$images = $this->getUserImages($username);

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

		Profile::end('DeviantartTop::getFavImages');

		return $images;
	}
}