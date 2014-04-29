<?php

error_reporting(E_ALL);

require_once 'classes/Profile.php';
require_once 'classes/Request.php';
require_once 'classes/View.php';
require_once 'classes/DeviantartTop.php';
require_once 'classes/DeviantartTopMongo.php';

define('IS_ADMIN', Request::getUsername() === 'admin');

Profile::begin('total');
Profile::begin('init');

//$deviantartTop = new DeviantartTop;
$deviantartTop = new DeviantartTopMongo;

$view = new View('layout');

$galleries = $deviantartTop->getData('galleries', [], [
	'sort' => array(
		'local.position' => 1,
	),
]);
$keywords = $deviantartTop->getData('keywords', [], [
	'limit' => 200,
	'sort' => [
		'name' => 1
	],
]);
$categories = $deviantartTop->getData('categories');
$profiles = $deviantartTop->getData('profiles');

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
$titleRegex = '/(^|\s)'.$titleCmp.'(\s|$)/ui';

if (isset($titleParams['by'])) {
	$username = $titleParams['by'];
} else if ($username) {
	$titleParams['by'] = $username;
}

$categoriesQuery = isset($titleParams['cat']) ? preg_split('/\s*,\s*/', $titleParams['cat']) : array();

$title = Request::buildQuery($titleCmp, $titleParams);

Profile::end('init');

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

$authors = $deviantartTop->getTop($query, $topQuery);

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
Profile::begin('render');
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

	$baseUrl = '?'.$galleriesParams.'&'.$limitsParams.'&title='.$title;
	$prevUrl = $baseUrl.'&page='.($page-1);
	$nextUrl = $baseUrl.'&page='.($page+1);

	echo json_encode(compact(
		'authors',
		'authorsHtml',
		'page',
		'baseUrl',
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
Profile::end('render');
Profile::end('total');

if (!Request::isAjax()) {
	echo Profile::consoleScript();
}