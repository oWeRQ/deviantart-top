<?php

error_reporting(E_ALL);

require_once 'deviantart.class.php';
Devianart::$silent = true;

while(file_exists('images_by_author.json.lock'))
	usleep(500);

touch('images_by_author.json.lock');

$images_by_author = json_decode(file_get_contents('images_by_author.json'), true);
$profiles = json_decode(file_get_contents('profiles.json'), true);

$devianart = new Devianart;

$user_id = 16413375;
$galleries_data = $devianart->getFavGalleries($user_id, 21);

$action = @$_REQUEST['action'];
$username = @$_REQUEST['username'];

if ($action === 'setGalleries')
{
	$image_id = (int)@$_REQUEST['image_id'];
	$galleries = (array)@$_REQUEST['galleries'];

	if (empty($galleries) || $image_id === 0)
		die();

	$image = &$images_by_author[$username][$image_id];

	$add_galleries = array_diff($galleries, $image['galleries']);
	$remove_galleries = array_diff($image['galleries'], $galleries);

	foreach ($add_galleries as $gallery) {
		foreach ($galleries_data as $gallery_data) {
			if ($gallery === $gallery_data['title']) {
				$devianart->addFavGalleries($user_id, $gallery_data['galleryid'], $image_id);
				break;
			}
		}
	}

	foreach ($remove_galleries as $gallery) {
		foreach ($galleries_data as $gallery_data) {
			if ($gallery === $gallery_data['title']) {
				$devianart->removeFavGalleries($user_id, $gallery_data['galleryid'], $image_id);
				break;
			}
		}
	}

	$image['galleries'] = $galleries;

	echo json_encode(array(
		'image' => $image,
	));
} elseif ($action === 'addGallery' || $action === 'removeGallery') {
	$images = array();
	$gallery_id = (int)@$_REQUEST['gallery'];
	$image_ids = (array)@$_REQUEST['images'];

	foreach ($galleries_data as $gallery_data) {
		if ($gallery_id === $gallery_data['galleryid']) {
			foreach ($image_ids as $image_id) {
				$image = &$images_by_author[$username][$image_id];

				$pos = array_search($gallery_data['title'], $image['galleries']);

				if ($action === 'removeGallery') {
					if ($pos !== false) {
						array_splice($image['galleries'], $pos, 1);
						$devianart->removeFavGalleries($user_id, $gallery_id, $image_id);
					}
				} else {
					if ($pos === false) {
						$image['galleries'][] = $gallery_data['title'];
						$devianart->addFavGalleries($user_id, $gallery_id, $image_id);
					}
				}

				$images[] = $image;
			}
		}
	}

	echo json_encode(array(
		'images' => $images,
	));
} elseif ($action === 'deleteFavorites') {
	$images = array();
	$image_ids = (array)@$_REQUEST['images'];

	foreach ($image_ids as $image_id) {
		$images[] = $images_by_author[$username][$image_id];
		unset($images_by_author[$username][$image_id]);

		$devianart->toggleFavourite($image_id);
	}

	echo json_encode(array(
		'images' => $images,
	));
}

file_put_contents('images_by_author.json', json_encode($images_by_author));

unlink('images_by_author.json.lock');