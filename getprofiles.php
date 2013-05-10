<?php

require_once 'deviantart.class.php';
Deviantart::$cache_time = 3600*24*14;

$images = json_decode(file_get_contents('data/images.json'), true);
$authors = array();
foreach ($images as $image) {
	@$authors[$image['author']]++;
}

$profiles = array();

$total = count($authors);
foreach (array_keys($authors) as $i => $author) {
	$line = "get profile: ".($i+1)."/$total $author";
	echo "\r".str_pad($line, 80);

	if ($profile = Deviantart::userinfo($author)) {
		$profile['username'] = $author;
		$profiles[$author] = $profile;
	}
}

echo "\n";

file_put_contents('data/profiles.json', json_encode($profiles, JSON_PRETTY_PRINT));