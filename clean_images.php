<?php

$images_by_author = json_decode(file_get_contents('images_by_author.json'), true);

$images = array();
foreach ($images_by_author as $author_images) {
	foreach ($author_images as $image) {
		$images[$image['id']] = $image;
	}
}

file_put_contents('images_with_galleries.json', json_encode($images));

$newImages = array();
$existImages = array_flip(glob('images/*.*'));

foreach ($images as $image) {
	$filename = 'images/'.$image['filename'];

	if (file_exists($filename)) {
		unset($existImages[$filename]);
	} else {
		$newImages[] = $image['image'];
	}
}

file_put_contents('images.txt', implode("\n", $newImages));
file_put_contents('trash_images.txt', implode("\n", array_flip($existImages)));