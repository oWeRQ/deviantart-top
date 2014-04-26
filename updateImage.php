<?php

$user_id = 16413375;
$maxCalls = 40;

error_reporting(E_ALL);

require_once 'classes/Deviantart.php';
Deviantart::$silent = true;
$deviantart = new Deviantart;
$calls = array();

if (($images_handle = fopen('data/images.json', 'c+')) === false) {
	echo json_encode(array(
		'error' => 1,
		'message' => 'Couldn\'t open file!',
	));
	exit();
}

if (!flock($images_handle, LOCK_EX)) {
	fclose($images_handle);
	
	echo json_encode(array(
		'error' => 2,
		'message' => 'Couldn\'t get the lock!',
	));
	exit();
}

$images = json_decode(stream_get_contents($images_handle), true);
$profiles = json_decode(file_get_contents('data/profiles.json'), true);

//$galleries_data = $deviantart->getFavGalleries($user_id, 21);
$galleries_data = json_decode(file_get_contents('data/galleries.json'), true);

$response = null;

$action = @$_REQUEST['action'];

if ($action === 'setGalleries')
{
	$image_id = (int)@$_REQUEST['image_id'];
	$galleries = (array)@$_REQUEST['galleries'];

	if (empty($galleries) || $image_id === 0)
		die();

	$image = &$images[$image_id];

	$add_galleries = array_diff($galleries, $image['galleries']);
	$remove_galleries = array_diff($image['galleries'], $galleries);

	foreach ($add_galleries as $gallery) {
		foreach ($galleries_data as $gallery_data) {
			if ($gallery === $gallery_data['title']) {
				$deviantart->addFavGalleries($user_id, $gallery_data['galleryid'], $image_id);
				break;
			}
		}
	}

	foreach ($remove_galleries as $gallery) {
		foreach ($galleries_data as $gallery_data) {
			if ($gallery === $gallery_data['title']) {
				$deviantart->removeFavGalleries($user_id, $gallery_data['galleryid'], $image_id);
				break;
			}
		}
	}

	$image['galleries'] = $galleries;

	$response = json_encode(array(
		'image' => $image,
	));
} elseif ($action === 'addGallery' || $action === 'removeGallery') {
	$updateImages = array();
	$gallery_id = (int)@$_REQUEST['gallery'];
	$image_ids = (array)@$_REQUEST['images'];

	foreach ($galleries_data as $gallery_data) {
		if ($gallery_id === $gallery_data['galleryid']) {
			foreach ($image_ids as $image_id) {
				$image = &$images[$image_id];

				$pos = array_search($gallery_data['title'], $image['galleries']);

				if ($action === 'removeGallery') {
					if ($pos !== false) {
						array_splice($image['galleries'], $pos, 1);
						$calls[] = array(
							'object' => "Gallections",
							'method' => "remove_resource",
							'params' => array(
								$user_id,
								21,
								$gallery_id,
								1,
								$image_id,
							),
						);
					}
				} else {
					if ($pos === false) {
						$image['galleries'][] = $gallery_data['title'];
						$calls[] = array(
							'object' => "Aggregations",
							'method' => "add_resource",
							'params' => array(
								$user_id,
								0,
								21,
								$gallery_id,
								1,
								$image_id,
								0,
							),
						);
					}
				}

				$updateImages[] = $image;
			}
		}
	}

	$response = json_encode(array(
		'images' => $updateImages,
	));
} elseif ($action === 'deleteFavorites') {
	$updateImages = array();
	$image_ids = (array)@$_REQUEST['images'];

	foreach ($image_ids as $image_id) {
		if (!isset($images[$image_id]))
			continue;

		$updateImages[] = $images[$image_id];
		unset($images[$image_id]);

		$calls[] = array(
			'object' => "Deviation",
			'method' => "Favourite",
			'params' => array(
				$image_id,
			),
		); 
	}

	$response = json_encode(array(
		'images' => $updateImages,
	));
}

$images_str = json_encode($images);

if (disk_free_space('data/') < strlen($images_str)) {
	flock($images_handle, LOCK_UN);
	fclose($images_handle);

	echo json_encode(array(
		'error' => 3,
		'message' => 'Disk full',
	));
	exit();
}

if (!empty($calls))
	$deviantart->sendCalls($calls, 'post', 1);

echo $response;

ftruncate($images_handle, 0);
rewind($images_handle);
fwrite($images_handle, $images_str);
fflush($images_handle);
flock($images_handle, LOCK_UN);
fclose($images_handle);