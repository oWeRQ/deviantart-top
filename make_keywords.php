<?php

$images = json_decode(file_get_contents('data/images.json'), true);

$keywords = array();

foreach ($images as $image) {
	$words = preg_split('#[.,:\s]+#', html_entity_decode($image['title']));
	foreach ($words as $word) {
		$word = ucfirst(strtolower($word));
		if (strlen($word) > 2 && preg_match('#^[A-Z]#', $word))
			@$keywords[$word][] = $image['id'];
	}
}

arsort($keywords);

file_put_contents('data/keywords.json', json_encode($keywords));