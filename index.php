<?php

error_reporting(E_ALL);

require_once 'classes/autoload.php';

Profile::begin('total');
Profile::begin('init');

define('IS_ADMIN', Request::getUsername() === 'admin');

$deviantartTop = new DeviantartTop;
$view = new View('layout');

$galleries = $deviantartTop->getData('galleries', [], [
	'sort' => [
		'local.position' => 1,
	],
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
$exclude_galleries = (array)Request::param('exclude', []);
$checked_galleries = (array)Request::param('galleries', []);
$condition = (string)Request::param('condition', 'or');
$intersect = (int)Request::param('intersect', 1);

$minFavs = (int)Request::param('minFavs', 1);
$maxFavs = (int)Request::param('maxFavs', 0);
$minDevia = (int)Request::param('minDevia', 1);
$imagesOffset = (int)Request::param('imagesOffset', 0);
$topLimit = (int)Request::param('topLimit', 10);
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

$categoriesQuery = isset($titleParams['cat']) ? preg_split('/\s*,\s*/', $titleParams['cat']) : [];

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
	'topOffset',
	'topLimit',
	'sort',
	'sortDir'
);

$top = $deviantartTop->getTop($query, $topQuery);

$pages = $top['pages'];
$authors = $top['authors'];

// query params
$galleriesParams = http_build_query([
	'exclude' => $exclude_galleries,
	'galleries' => $checked_galleries,
	'condition' => $condition,
]);

$limitsParams = http_build_query([
	'minFavs' => $minFavs,
	'maxFavs' => $maxFavs,
	'minDevia' => $minDevia,
	'imagesLimit' => $imagesLimit,
	'topLimit' => $topLimit,
	'sort' => $sort,
]);

$userLimitsParams = http_build_query([
	'minFavs' => $minFavs,
	'maxFavs' => $maxFavs,
	'minDevia' => $minDevia,
	'imagesLimit' => $imagesLimit * $topLimit,
	'topLimit' => 1,
	'sort' => $sort,
]);

// render
Profile::begin('render');
if (Request::isAjax()) {
	$authorsHtml = [];

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