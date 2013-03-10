<?php

require_once 'deviantart.class.php';

$da = new Devianart;

$galleries = $da->getFavGalleries(16413375, 21);

$images = array();
$images_by_author = array();
$newImages = array();
$newMiddle = array();
$newThumbs = array();
$newImageFiles = array();
$newMiddleFiles = array();
$newThumbFiles = array();

foreach ($galleries as $gallery) {
	$t = microtime(true);

	$favs = $da->getFavs('oWeRQ/'.$gallery['galleryid']);

	$galleryDir = 'galleries/'.$gallery['title'].'/';
	if (!file_exists($galleryDir))
		mkdir($galleryDir, 0777, true);

	foreach ($favs as $fav) {
		//var_dump($fav);die();

		$filename = pathinfo(parse_url($fav['image'], PHP_URL_PATH), PATHINFO_BASENAME);

		if (!file_exists('images/'.$filename) && !in_array($filename, $newImageFiles)) {
			$newImages[] = $fav['image'];
			$newImageFiles[] = $filename;
		}

		if (!file_exists('images/middle/'.$filename) && !in_array($filename, $newMiddleFiles)) {
			$newMiddle[] = $fav['middle'];
			$newMiddleFiles[] = $filename;
		}

		if (!file_exists('images/thumbs/'.$filename) && !in_array($filename, $newThumbFiles)) {
			$newThumbs[] = $fav['thumb'];
			$newThumbFiles[] = $filename;
		}

		if (!is_link($galleryDir.$filename))
			symlink('../../images/'.$filename, $galleryDir.$filename);

		/*if (!isset($images[$gallery['title']]))
			$images[$gallery['title']] = array();

		$images[$gallery['title']][] = $fav;*/

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
file_put_contents('middle.txt', implode("\n", $newMiddle));
file_put_contents('thumbs.txt', implode("\n", $newThumbs));
file_put_contents('images_with_galleries.json', json_encode(array_values($images)));

foreach ($images as $image) {
	$images_by_author[$image['author']][$image['id']] = $image;
}

file_put_contents('images_by_author.json', json_encode($images_by_author));
