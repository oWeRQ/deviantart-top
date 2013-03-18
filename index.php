<?php

error_reporting(E_ALL);

require_once 'deviantart.class.php';
Deviantart::$silent = true;

$images_by_author = json_decode(file_get_contents('images_by_author.json'), true);
$profiles = json_decode(file_get_contents('profiles.json'), true);

$deviantart = new Deviantart;
$galleries = $deviantart->getFavGalleries(16413375, 21);
$checked_galleries = (array)@$_REQUEST['galleries'];
$exclude_galleries = (array)@$_REQUEST['exclude'];
$condition = @$_REQUEST['condition'];
$sort = @$_REQUEST['sort'];
$minFavs = (int)@$_REQUEST['minFavs'];
$maxFavs = (int)@$_REQUEST['maxFavs'];
$minDevia = (int)@$_REQUEST['minDevia'];
$imagesLoaded = (int)@$_REQUEST['imagesLoaded'];
$topLimit = (int)@$_REQUEST['topLimit'];
$imagesLimit = (int)@$_REQUEST['imagesLimit'];
$page = (int)@$_REQUEST['page'];
$username = @$_REQUEST['username'];
$title = @$_REQUEST['title'];
$titleRegex = '#'.$title.'#ui';

if (empty($condition))
	$condition = 'or';

if (empty($sort))
	$sort = 'percent';

if ($minFavs <= 0)
	$minFavs = 1;

if ($minDevia <= 0)
	$minDevia = 24;

if ($topLimit <= 0)
	$topLimit = 5;

if ($imagesLimit <= 0)
	$imagesLimit = 16;

if ($page <= 0)
	$page = 1;

$topOffset = $topLimit*($page-1);

$galleriesParams = http_build_query(array(
	'galleries' => $checked_galleries,
	'condition' => $condition,
));
$limitsParams = http_build_query(array(
	'minFavs' => $minFavs,
	'maxFavs' => $maxFavs,
	'minDevia' => $minDevia,
	'imagesLimit' => $imagesLimit,
	'topLimit' => $topLimit,
	'sort' => $sort,
));

function getFavImages($username) {
	global $images_by_author;
	global $checked_galleries;

	$images = array_values(array_filter($images_by_author[$username], function($image){
		global $exclude_galleries;
		global $title;
		global $titleRegex;

		if ($title && !preg_match($titleRegex, $image['title']))
			return false;
		
		foreach ($exclude_galleries as $gallery) {
			if (in_array($gallery, $image['galleries']))
				return false;
		}
		
		return true;
	}));

	if (empty($checked_galleries))
		return $images;

	return array_values(array_filter($images, function($image){
		global $checked_galleries;
		global $condition;

		if ($condition == 'or') {
			foreach ($checked_galleries as $gallery) {
				if (in_array($gallery, $image['galleries']))
					return true;
			}
			return false;
		} elseif ($condition == 'and') {
			foreach ($checked_galleries as $gallery) {
				if (!in_array($gallery, $image['galleries']))
					return false;
			}
			return true;
		} elseif ($condition == 'xor') {
			$count = 0;
			foreach ($checked_galleries as $i => $gallery) {
				if (in_array($gallery, $image['galleries']))
					$count++;
			}
			return $count === 1;
		}
	}));
}

if (!empty($username)) {
	$profile = $profiles[$username];

	$images = getFavImages($username);
	$favourites = count($images);

	$authors[] = array(
		'username' => $username,
		'percent' => round($favourites/$profile['deviations']*100, 1),
		'total' => $favourites,
		'deviations' => $profile['deviations'],
		'images' => array_slice($images, $imagesLoaded, $imagesLimit),
	);
} else {
	$top = array_values(array_filter(array_map(function($profile) use($minFavs, $maxFavs, $minDevia, $imagesLoaded, $imagesLimit){
		if (isset($profile['deviations']) && $profile['deviations'] >= $minDevia) {
			$images = getFavImages($profile['username']);
			$favourites = count($images);
			//echo "$favourites {$profile['username']}/{$profile['deviations']}<br>";
			if ($favourites !==0 && $favourites >= $minFavs && ($maxFavs === 0 || $favourites <= $maxFavs)) {
				return array(
					'username' => $profile['username'],
					'percent' => round($favourites/$profile['deviations']*100, 1),
					'total' => $favourites,
					'deviations' => $profile['deviations'],
					'images' => array_slice($images, $imagesLoaded, $imagesLimit),
				);
			}
		}
	}, $profiles)));

	if ($sort === 'percent') {
		usort($top, function($a, $b){
			if ($a['percent'] == $b['percent'])
				return 0;

			return ($a['percent'] > $b['percent']) ? -1 : 1;
		});
	} elseif ($sort === 'favourites') {
		usort($top, function($a, $b){
			if ($a['total'] == $b['total'])
				return 0;

			return ($a['total'] > $b['total']) ? -1 : 1;
		});
	} elseif ($sort === 'deviations') {
		usort($top, function($a, $b){
			if ($a['deviations'] == $b['deviations'])
				return 0;

			return ($a['deviations'] > $b['deviations']) ? -1 : 1;
		});
	}

	$authors = array_slice($top, $topOffset, $topLimit);
}

if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
	&& $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
{
	$authorsHtml = array();

	foreach ($authors as $i => $author) {
		ob_start();
		require 'index.item.tpl.php';
		$authorsHtml[] = ob_get_clean();
	}

	echo json_encode(array(
		'authors' => $authors,
		'authorsHtml' => $authorsHtml,
		'page' => $page,
		'prevUrl' => '?'.$galleriesParams.'&'.$limitsParams.'&title='.$title.'&page='.($page-1),
		'nextUrl' => '?'.$galleriesParams.'&'.$limitsParams.'&title='.$title.'&page='.($page+1),
	));
} else {
	include 'index.tpl.php';
}