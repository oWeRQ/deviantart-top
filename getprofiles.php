<?php

require_once 'classes/Profile.php';
require_once 'classes/Deviantart.php';
require_once 'classes/DeviantartTop.php';
require_once 'classes/DeviantartTopMongo.php';

Deviantart::$cache_time = 3600*24*14;
$deviantart = new Deviantart;
$deviantartTop = new DeviantartTopMongo;

$authors = $deviantartTop->db->images->distinct('local.author');
rsort($authors);

$start = time();
$total = count($authors);
foreach ($authors as $i => $author) {
	$line = "get profile: ".($i+1)."/$total $author";
	echo "\r".str_pad($line, 80);

	if ($profile = $deviantart->userinfo($author)) {
		$profile['username'] = $author;
		$deviantartTop->saveData('profiles', $author, $profile);
	}
}

echo "\n";
