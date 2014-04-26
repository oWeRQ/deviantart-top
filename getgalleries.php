<?php

require_once 'classes/Deviantart.php';

$deviantart = new Deviantart;
Deviantart::$cache_time = 0;

$content = $deviantart->getFavGalleries(16413375, 21);

$date = date('Y-m-d');
$galleries_history = json_decode(@file_get_contents('data/galleries_history.json'), true);
$galleries_history[$date] = array();

foreach ($content as $gallery) {
	echo "id: ".$gallery['galleryid']."\ttitle: ".$gallery['title']."\tcount: ".$gallery['approx_total']."\n";

	$galleries_history[$date][] = array(
		'id' => $gallery['galleryid'],
		'name' => $gallery['title'],
		'count' => $gallery['approx_total'],
	);
}

//file_put_contents('getgalleries.out.txt', var_export($content, true));

file_put_contents('data/galleries.json', json_encode($content, JSON_PRETTY_PRINT));
file_put_contents('data/galleries_history.json', json_encode($galleries_history, JSON_PRETTY_PRINT));