<?php

error_reporting(E_ALL);

require_once 'deviantart.class.php';
Devianart::$silent = true;

$images_by_author = json_decode(file_get_contents('images_by_author.json'), true);
$profiles = json_decode(file_get_contents('profiles.json'), true);

$devianart = new Devianart;
$galleries = $devianart->getFavGalleries(16413375, 21);
$checked_galleries = (array)@$_REQUEST['galleries'];
$minFavs = (int)@$_REQUEST['minFavs'];
$minDevia = (int)@$_REQUEST['minDevia'];
$imagesLoaded = (int)@$_REQUEST['imagesLoaded'];
$topLimit = (int)@$_REQUEST['topLimit'];
$imagesLimit = (int)@$_REQUEST['imagesLimit'];
$username = @$_REQUEST['username'];

if ($minDevia <= 0)
	$minDevia = 24;

if ($topLimit <= 0)
	$topLimit = 10;

if ($imagesLimit <= 0)
	$imagesLimit = 16;

$galleriesParams = http_build_query($checked_galleries);
$limitsParams = http_build_query(array(
	'minFavs' => $minFavs,
	'minDevia' => $minDevia,
	'topLimit' => 1,
	'imagesLimit' => 48,
));

function getFavImages($username) {
	global $images_by_author;
	global $checked_galleries;

	if (empty($checked_galleries))
		return $images_by_author[$username];

	return array_values(array_filter($images_by_author[$username], function($image){
		global $checked_galleries;
		foreach ($checked_galleries as $gallery) {
			if (in_array($gallery, $image['galleries']))
				return true;
		}
		return false;
	}));
}

$top = array_map(function($profile) use($minFavs, $minDevia){
	if (isset($profile['deviations']) && $profile['deviations'] >= $minDevia) {
		$favourites = count(getFavImages($profile['username']));
		//echo "$favourites {$profile['username']}/{$profile['deviations']}<br>";
		if ($favourites >= $minFavs)
			return round($favourites/$profile['deviations']*100, 1);
	}
}, $profiles);

arsort($top);

$authors = array();

if (isset($_REQUEST['username']) && !empty($_REQUEST['username'])) {
	$author = $_REQUEST['username'];
	$percent = $top[$author];
	$images = getFavImages($author);

	$authors[] = array(
		'username' => $author,
		'percent' => $percent,
		'total' => count($images),
		'images' => array_slice($images, $imagesLoaded, $imagesLimit),
	);
} else {
	$limit = $topLimit;
	foreach ($top as $author => $percent) {
		if (!$limit-- || !$percent)
			break;

		$images = getFavImages($author);

		$authors[] = array(
			'username' => $author,
			'percent' => $percent,
			'total' => count($images),
			'images' => array_slice($images, $imagesLoaded, $imagesLimit),
		);
	}
}

if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
	&& $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	echo json_encode($authors);
} else {
	include 'index.tpl.php';
}