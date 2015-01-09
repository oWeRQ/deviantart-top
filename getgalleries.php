<?php

require_once 'classes/autoload.php';

Deviantart::$cache_time = 0;
$deviantart = new Deviantart;
$deviantartTop = new DeviantartTop;

$try = 5;
do {
	$galleries = $deviantart->getFavGalleries(16413375);
} while (--$try && $galleries === null);

if (!is_array($galleries) || isset($galleries['error'])) {
	var_dump($galleries);
	die();
}

$date = date('Y-m-d');
$galleries_history = json_decode(@file_get_contents('data/galleries_history.json'), true);
$galleries_history[$date] = array();

foreach ($galleries as $gallery) {
	$deviantartTop->saveData('galleries', $gallery['galleryid'], $gallery);

	echo "id: ".$gallery['galleryid']."\ttitle: ".$gallery['title']."\tcount: ".$gallery['approx_total']."\n";

	$galleries_history[$date][] = array(
		'id' => $gallery['galleryid'],
		'name' => $gallery['title'],
		'count' => $gallery['approx_total'],
	);
}

file_put_contents('data/galleries_history.json', json_encode($galleries_history, JSON_PRETTY_PRINT));