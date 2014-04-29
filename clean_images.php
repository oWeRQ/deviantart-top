<?php

//$images = json_decode(file_get_contents('data/images.json'), true);

require_once 'classes/Profile.php';
require_once 'classes/DeviantartTop.php';
require_once 'classes/DeviantartTopMongo.php';

$deviantartTop = new DeviantartTopMongo;

$images = $deviantartTop->getData('images');

$newImages = array();
$existImages = array_flip(glob('images/original/*'));

foreach ($images as $image) {
	if (!isset($image['id']))
		continue;
	
	$filename = 'images/original/'.$image['filename'];

	if (file_exists($filename)) {
		unset($existImages[$filename]);
	} else {
		$newImages[] = $image['image'];
	}
}

file_put_contents('images.txt', implode("\n", $newImages));
file_put_contents('trash_images.txt', implode("\n", array_flip($existImages)));