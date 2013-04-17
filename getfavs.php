<?php

require_once 'deviantart.class.php';

$da = new Deviantart;

$galleries = $da->getFavGalleries(16413375, 21);

$images = array();
$newImages = array();
$newImageFiles = array();

foreach ($galleries as $gallery) {
	$t = microtime(true);

	$favs = $da->getFavs('oWeRQ/'.$gallery['galleryid']);

	$galleryDir = 'galleries/'.$gallery['title'].'/';
	if (!file_exists($galleryDir))
		mkdir($galleryDir, 0777, true);

	foreach ($favs as $fav) {
		$filename = pathinfo(parse_url($fav['image'], PHP_URL_PATH), PATHINFO_BASENAME);

		if (!file_exists('images/original/'.$filename) && !in_array($filename, $newImageFiles)) {
			$newImages[] = $fav['image'];
			$newImageFiles[] = $filename;
		}

		if (!is_link($galleryDir.$filename))
			symlink('../../images/original/'.$filename, $galleryDir.$filename);

		if (isset($images[$fav['id']])) {
			$images[$fav['id']]['galleries'][] = $gallery['title'];
		} else {
			$images[$fav['id']] = $fav;
			$images[$fav['id']]['galleries'] = array($gallery['title']);
		}
	}

	echo $gallery['title'].': '.(microtime(true)-$t)."s\n";
}

file_put_contents('images.txt', implode("\n", $newImages));
file_put_contents('data/images.json', json_encode($images));
