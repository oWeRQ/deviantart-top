<?php

$images = json_decode(file_get_contents('data/images.json'), true);

$newImages = array();
$existImages = array_flip(glob('images/original/*'));

foreach ($images as $image) {
	$filename = 'images/original/'.$image['filename'];

	if (file_exists($filename)) {
		unset($existImages[$filename]);
	} else {
		$newImages[] = $image['image'];
	}
}

file_put_contents('images.txt', implode("\n", $newImages));
file_put_contents('trash_images.txt', implode("\n", array_flip($existImages)));