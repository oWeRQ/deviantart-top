<?php

error_reporting(E_ALL);

define('IS_ADMIN', $_SERVER['PHP_AUTH_USER'] === 'admin');

require_once 'deviantart.class.php';
Deviantart::$silent = true;

function param($name, $default = null) {
	if (isset($_REQUEST[$name]) && !empty($_REQUEST[$name]))
		return $_REQUEST[$name];

	return $default;
}

function score($pos, $n) {
	return $pos * $pos / $n;
}

function wilson_score($pos, $n) {
	$z = 1.64485; //1.0 = 85%, 1.6 = 95%
	$phat = $pos / $n;
	return ($phat + $z*$z/(2*$n) - $z*sqrt(($phat*(1-$phat) + $z*$z/(4*$n))/$n)) / (1 + $z*$z/$n);
}

$images_by_author = json_decode(file_get_contents('images_by_author.json'), true);
$profiles = json_decode(file_get_contents('profiles.json'), true);

$deviantart = new Deviantart;
$galleries = $deviantart->getFavGalleries(16413375, 21);

$exclude_galleries = (array)param('exclude', array());
$checked_galleries = (array)param('galleries', array());
$condition = (string)param('condition', 'or');
$intersect = (int)param('intersect', 1);

$minFavs = (int)param('minFavs', 1);
$maxFavs = (int)param('maxFavs', 0);
$minDevia = (int)param('minDevia', 1);
$imagesOffset = (int)param('imagesOffset', 0);
$topLimit = (int)param('topLimit', 10);
$imagesLimit = (int)param('imagesLimit', 16);
$page = (int)param('page', 1);

$username = (string)param('username', '');
$title = (string)param('title', '');
$sort = (string)param('sort', 'score');
$sortDir = (int)param('sortDir', 1);

$titleRegex = '#(^|\s)'.$title.'(\s|$)#ui';

$topOffset = $topLimit*($page-1);

$galleriesParams = http_build_query(array(
	'exclude' => $exclude_galleries,
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

$userLimitsParams = http_build_query(array(
	'minFavs' => $minFavs,
	'maxFavs' => $maxFavs,
	'minDevia' => $minDevia,
	'imagesLimit' => $imagesLimit * $topLimit,
	'topLimit' => 1,
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
		global $intersect;

		$diff_count = count(array_diff($checked_galleries, $image['galleries']));
		$checked_count = count($checked_galleries);

		if ($condition == 'or')
			return $diff_count < $checked_count;
		elseif ($condition == 'and')
			return $diff_count === 0;
		elseif ($condition == 'only')
			return $diff_count === 0 && $checked_count === count($image['galleries']);
		elseif ($condition == 'xor')
			return $diff_count === $checked_count-$intersect;
	}));
}

if (!empty($username)) {
	$profile = $profiles[$username];

	$images = getFavImages($username);
	$favourites = count($images);

	$authors[] = array(
		'username' => $username,
		'percent' => $favourites/$profile['deviations']*100,
		'score' => score($favourites, $profile['deviations']),
		'wilson_score' => wilson_score($favourites, $profile['deviations']),
		'favourites' => $favourites,
		'deviations' => $profile['deviations'],
		'images' => array_slice($images, $imagesOffset, $imagesLimit),
	);
} else {
	$top = array_values(array_filter(array_map(function($profile) use($minFavs, $maxFavs, $minDevia, $imagesOffset, $imagesLimit){
		if (isset($profile['deviations']) && $profile['deviations'] >= $minDevia) {
			$images = getFavImages($profile['username']);
			$favourites = count($images);

			if ($favourites !==0 && $favourites >= $minFavs && ($maxFavs === 0 || $favourites <= $maxFavs)) {
				return array(
					'username' => $profile['username'],
					'percent' => $favourites/$profile['deviations']*100,
					'score' => score($favourites, $profile['deviations']),
					'wilson_score' => wilson_score($favourites, $profile['deviations']),
					'favourites' => $favourites,
					'deviations' => $profile['deviations'],
					'images' => array_slice($images, $imagesOffset, $imagesLimit),
				);
			}
		}
	}, $profiles)));

	usort($top, function($a, $b) use($sort, $sortDir){
		if ($a[$sort] == $b[$sort])
			return 0;

		return ($a[$sort] > $b[$sort]) ? -$sortDir : $sortDir;
	});

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