<?php

error_reporting(E_ALL);

require_once 'classes/Request.php';
require_once 'classes/View.php';
require_once 'classes/DeviantartTop.php';

define('IS_ADMIN', Request::getUsername() === 'admin');

$view = new View('layout');

$galleries = DeviantartTop::getData('galleries');
$keywords = DeviantartTop::getData('keywords');
$categories = DeviantartTop::getData('categories');
$profiles = DeviantartTop::getData('profiles');

// filter params
$exclude_galleries = (array)Request::param('exclude', array());
$checked_galleries = (array)Request::param('galleries', array());
$condition = (string)Request::param('condition', 'or');
$intersect = (int)Request::param('intersect', 1);

$minFavs = (int)Request::param('minFavs', 1);
$maxFavs = (int)Request::param('maxFavs', 0);
$minDevia = (int)Request::param('minDevia', 1);
$imagesOffset = (int)Request::param('imagesOffset', 0);
$topLimit = (int)Request::param('topLimit', 20);
$imagesLimit = (int)Request::param('imagesLimit', 16);
$sort = (string)Request::param('sort', 'score');
$sortDir = (int)Request::param('sortDir', 1);
$page = (int)Request::param('page', 1);

$username = (string)Request::param('username', '');
$title = (string)Request::param('title', '');

list($titleCmp, $titleParams) = Request::parseQuery($title);
$titleRegex = '#(^|\s)'.$titleCmp.'(\s|$)#ui';

if (isset($titleParams['by'])) {
	$username = $titleParams['by'];
} else if ($username) {
	$titleParams['by'] = $username;
}

$categoriesQuery = isset($titleParams['cat']) ? preg_split('/\s*,\s*/', $titleParams['cat']) : array();

$title = Request::buildQuery($titleCmp, $titleParams);

// get top
$topOffset = $topLimit*($page-1);

$query = compact(
	'checked_galleries',
	'exclude_galleries',
	'titleCmp',
	'titleRegex',
	'categoriesQuery',
	'condition',
	'intersect'
);

$topQuery = compact(
	'username',
	'minFavs',
	'maxFavs',
	'minDevia',
	'imagesOffset',
	'imagesLimit',
	'sort',
	'sortDir'
);

$authors = DeviantartTop::getTop($query, $topQuery);

$pages = ceil(count($authors)/$topLimit);
$authors = array_slice($authors, $topOffset, $topLimit);

// query params
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

// render
if (Request::isAjax()) {
	$authorsHtml = array();

	foreach ($authors as $i => $author) {
		$authorsHtml[] = $view->renderPartial('_item', compact(
			'i',
			'author',
			'imagesOffset',
			'topOffset',
			'galleriesParams',
			'userLimitsParams'
		), true);
	}

	$prevUrl = '?'.$galleriesParams.'&'.$limitsParams.'&title='.$title.'&page='.($page-1);
	$nextUrl = '?'.$galleriesParams.'&'.$limitsParams.'&title='.$title.'&page='.($page+1);

	echo json_encode(compact(
		'authors',
		'authorsHtml',
		'page',
		'prevUrl',
		'nextUrl'
	));
} else {
	$view->sidebar = $view->renderPartial('_filter', compact(
		'title',
		'keywords',
		'categories',
		'profiles',
		'galleries',
		'exclude_galleries',
		'checked_galleries',
		'condition',
		'minFavs',
		'maxFavs',
		'minDevia',
		'imagesLimit',
		'topLimit',
		'page',
		'sort',
		'sortDir'
	), true);

	$view->render('index', compact(
		'page',
		'pages',
		'galleriesParams',
		'limitsParams',
		'userLimitsParams',
		'title',
		'authors',
		'imagesOffset',
		'topOffset',
		'keywords',
		'galleries'
	));
}