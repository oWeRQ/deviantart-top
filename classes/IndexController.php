<?php

class IndexController extends View
{
	const ACTION_VIEW = 'view';
	const ACTION_EDIT = 'edit';

	protected $layout = 'layout';
	protected $deviantartTop;

	public function __construct()
	{
		$this->deviantartTop = new DeviantartTop;
	}

	public function checkAccess($action = 'view') {
		if ($action === self::ACTION_EDIT && IS_ADMIN === false) {
			header("Status: 401 Access Denied");
			exit();
		}
	}

	public function actionIndex()
	{
		$this->checkAccess(self::ACTION_VIEW);

		Profile::begin('total');
		Profile::begin('init');

		$galleries = $this->deviantartTop->getData('galleries', [], [
			'sort' => [
				'local.position' => 1,
			],
		]);
		$keywords = $this->deviantartTop->getData('keywords', [], [
			'limit' => 200,
			'sort' => [
				'name' => 1
			],
		]);
		$categories = $this->deviantartTop->getData('categories');
		$profiles = $this->deviantartTop->getData('profiles');

		// filter params
		$exclude_galleries = (array)Request::param('exclude', []);
		$checked_galleries = (array)Request::param('galleries', []);
		$condition = (string)Request::param('condition', 'or');
		$intersect = (int)Request::param('intersect', 1);

		$minFavs = (int)Request::param('minFavs', 1);
		$maxFavs = (int)Request::param('maxFavs', 0);
		$minDevia = (int)Request::param('minDevia', 0);
		$imagesOffset = (int)Request::param('imagesOffset', 0);
		$topLimit = (int)Request::param('topLimit', 10);
		$imagesLimit = (int)Request::param('imagesLimit', 20);
		$sort = (string)Request::param('sort', 'score');
		$sortTotal = (string)Request::param('sortTotal', 'deviations');
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
			'sortTotal',
			'sortDir'
		);

		$top = $this->deviantartTop->getTop($query, $topQuery);

		$count = $top['count'];
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
			'sortTotal' => $sortTotal,
			'sortDir' => $sortDir,
		]);

		$userLimitsParams = http_build_query([
			'minFavs' => $minFavs,
			'maxFavs' => $maxFavs,
			'minDevia' => $minDevia,
			'imagesLimit' => $imagesLimit * $topLimit,
			'topLimit' => 1,
			'sort' => $sort,
			'sortTotal' => $sortTotal,
			'sortDir' => $sortDir,
		]);

		// render
		Profile::begin('render');
		if (Request::isAjax()) {
			$authorsHtml = [];

			foreach ($authors as $i => $author) {
				$authorsHtml[] = $this->renderPartial('_item', compact(
					'i',
					'author',
					'imagesOffset',
					'topOffset',
					'galleriesParams',
					'userLimitsParams'
				), true);
			}

			$baseUrl = '?'.$galleriesParams.'&'.$limitsParams.'&title='.rawurlencode($title);
			$prevUrl = $baseUrl.'&page='.($page-1);
			$nextUrl = $baseUrl.'&page='.($page+1);

			echo json_encode(compact(
				'authors',
				'authorsHtml',
				'page',
				'pages',
				'topOffset',
				'baseUrl',
				'prevUrl',
				'nextUrl'
			));
		} else {
			$this->sidebar = $this->renderPartial('_filter', compact(
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
				'sortTotal',
				'sortDir',
				'count'
			), true);

			$this->render('index', compact(
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
	}

	public function actionGetGalleries()
	{
		$this->checkAccess(self::ACTION_VIEW);

		$galleries = $this->deviantartTop->getData('galleries', [], [
			'sort' => [
				'local.position' => 1,
			],
		]);

		echo json_encode(array(
			'galleries' => array_values($galleries),
		));
	}

	public function actionSetGalleries()
	{
		$this->checkAccess(self::ACTION_EDIT);

		$image_id = (string)Request::param('image_id', 0);
		$galleries = (array)Request::param('galleries', []);

		if (empty($galleries) || $image_id === 0)
			die();

		$image = $this->deviantartTop->db->images->findOne(['id' => $image_id]);

		$this->deviantartTop->db->images->update(['id' => $image_id], [
			'$set' => [
				'local.galleries' => $galleries,
				'local_updated' => time(),
			],
		]);

		$image['local']['galleries'] = $galleries;

		echo json_encode(array(
			'image' => $image['local'],
		));
	}

	public function actionAddGallery()
	{
		$this->checkAccess(self::ACTION_EDIT);

		$updateImages = array();
		$gallery_id = (int)Request::param('gallery', 0);
		$image_ids = (array)Request::param('images', []);

		$galleries_data = $this->deviantartTop->getData('galleries');
		foreach ($galleries_data as $gallery_data) {
			if ($gallery_id == $gallery_data['galleryid']) {
				$gallery_title = $gallery_data['title'];
			}
		}

		$this->deviantartTop->db->images->update(['id' => ['$in' => $image_ids]], [
			'$addToSet' => [
				'local.galleries' => $gallery_title,
			],
			'$set' => [
				'local_updated' => time(),
			],
		], [
			'multiple' => true,
		]);

		$images = $this->deviantartTop->db->images->find(['id' => ['$in' => $image_ids]]);

		foreach ($images as $image) {
			$updateImages[] = $image['local'];
		}

		echo json_encode(array(
			'images' => $updateImages,
		));
	}

	public function actionRemoveGallery()
	{
		$this->checkAccess(self::ACTION_EDIT);

		$updateImages = array();
		$gallery_id = (int)Request::param('gallery', 0);
		$image_ids = (array)Request::param('images', []);

		$galleries_data = $this->deviantartTop->getData('galleries');
		foreach ($galleries_data as $gallery_data) {
			if ($gallery_id === $gallery_data['galleryid']) {
				$gallery_title = $gallery_data['title'];
			}
		}

		$this->deviantartTop->db->images->update(['id' => ['$in' => $image_ids]], [
			'$pull' => [
				'local.galleries' => $gallery_title,
			],
			'$set' => [
				'local_updated' => time(),
			],
		], [
			'multiple' => true,
		]);

		$images = $this->deviantartTop->db->images->find(['id' => ['$in' => $image_ids]]);

		foreach ($images as $image) {
			$updateImages[] = $image['local'];
		}

		echo json_encode(array(
			'images' => $updateImages,
		));
	}

	public function actionDeleteFavorites()
	{
		$this->checkAccess(self::ACTION_EDIT);

		$updateImages = [];
		$image_ids = (array)Request::param('images', []);

		$this->deviantartTop->db->images->update(['id' => ['$in' => $image_ids]], [
			'$set' => [
				'local_deleted' => time(),
			],
		], [
			'multiple' => true,
		]);

		$images = $this->deviantartTop->db->images->find(['id' => ['$in' => $image_ids]]);

		foreach ($images as $image) {
			$updateImages[] = $image['local'];
		}

		echo json_encode(array(
			'images' => $updateImages,
		));
	}
}