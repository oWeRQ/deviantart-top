<?php

require_once 'classes/Deviantart.php';
Deviantart::$cache_time = 3600*24*14;
$deviantart = new Deviantart;

$images = json_decode(file_get_contents('data/images.json'), true);
$authors = array();
foreach ($images as $image) {
	@$authors[$image['author']]++;
}

arsort($authors);

$profiles = json_decode(file_get_contents('data/profiles.json'), true);

$start = time();
$total = count($authors);
foreach (array_keys($authors) as $i => $author) {
	$line = "get profile: ".($i+1)."/$total $author";
	echo "\r".str_pad($line, 80);

	if ($profile = $deviantart->userinfo($author)) {
		$profile['username'] = $author;
		$profiles[$author] = $profile;

		if (time() > $start + 20) {
			file_put_contents('data/profiles.json', json_encode($profiles, JSON_PRETTY_PRINT));
			$start = time();
		}
	}
}

echo "\n";

file_put_contents('data/profiles.json', json_encode($profiles, JSON_PRETTY_PRINT));