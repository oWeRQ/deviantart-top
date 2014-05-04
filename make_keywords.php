<?php

require_once 'classes/autoload.php';

$deviantartTop = new DeviantartTop;

$images = $deviantartTop->getImages();

$keywords = array();
$categories = array();

foreach ($images as $image) {
	$words = preg_split('#[.,:\s]+#', html_entity_decode($image['title']));
	foreach ($words as $word) {
		$word = ucfirst(strtolower($word));
		if (strlen($word) > 2 && preg_match('#^[A-Z]#', $word))
			@$keywords[$word][] = $image['id'];
	}

	if (is_array($image['categories'])) {
		foreach ($image['categories'] as $category) {
			@$categories[$category][] = $image['id'];
		}
	}
}

arsort($keywords);
arsort($categories);

foreach (['keywords', 'categories'] as $name) {
	$db->$name->remove();

	foreach ($$name as $record_name => $record_value) {
		$record = array(
			'name' => $record_name,
			'count' => count($record_value),
			'images' => $record_value,
		);

		$pk = 'name';
		$db->$name->save(array(
			'id' => $record[$pk],
			'local' => $record,
			'local_created' => time(),
			'local_updated' => time(),
			'local_deleted' => false,
			'server' => $record,
			'server_created' => time(),
			'server_updated' => time(),
			'server_deleted' => false,
		));
	}
}