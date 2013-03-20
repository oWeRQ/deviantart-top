<?php

require_once 'deviantart.class.php';
Deviantart::$cache_time = 3600*24*7;

$images_by_author = json_decode(file_get_contents('images_by_author.json'), true);

$profiles = array();

foreach (array_keys($images_by_author) as $i => $author) {
	echo "get profile #$i $author\n";
	$profile = Deviantart::userinfo($author);
	if ($profile) {
		$profile['username'] = $author;
		$profile['myfavourites'] = count($images_by_author[$author]);
		$profile['myfavourites_notfeatured'] = count(array_filter($images_by_author[$author], function($image){
			return !in_array('Featured', $image['galleries']);
		}));
		$profiles[$author] = $profile;
	}
}

file_put_contents('profiles.json', json_encode($profiles, JSON_PRETTY_PRINT));