<?php

require_once 'classes/autoload.php';

$deviantartTop = new DeviantartTop;

$galleries_data = $deviantartTop->getData('galleries');

$response = null;

$action = @$_REQUEST['action'];

if ($action === 'setGalleries')
{
	$image_id = (string)@$_REQUEST['image_id'];
	$galleries = (array)@$_REQUEST['galleries'];

	if (empty($galleries) || $image_id === 0)
		die();

	$image = $deviantartTop->db->images->findOne(['id' => $image_id]);

	$deviantartTop->db->images->update(['id' => $image_id], [
		'$set' => [
			'local.galleries' => $galleries,
			'local_updated' => time(),
		],
	]);

	$image['local']['galleries'] = $galleries;

	$response = json_encode(array(
		'image' => $image['local'],
	));
} elseif ($action === 'addGallery' || $action === 'removeGallery') {
	$updateImages = array();
	$gallery_id = (int)@$_REQUEST['gallery'];
	$image_ids = (array)@$_REQUEST['images'];

	foreach ($galleries_data as $gallery_data) {
		if ($gallery_id === $gallery_data['galleryid']) {
			$gallery_title = $gallery_data['title'];
		}
	}

	if ($action === 'addGallery') {
		$deviantartTop->db->images->update(['id' => ['$in' => $image_ids]], [
			'$addToSet' => [
				'local.galleries' => $gallery_title,
			],
			'$set' => [
				'local_updated' => time(),
			],
		], [
			'multiple' => true,
		]);
	} elseif ($action === 'removeGallery') {
		$deviantartTop->db->images->update(['id' => ['$in' => $image_ids]], [
			'$pull' => [
				'local.galleries' => $gallery_title,
			],
			'$set' => [
				'local_updated' => time(),
			],
		], [
			'multiple' => true,
		]);
	}

	$images = $deviantartTop->db->images->find(['id' => ['$in' => $image_ids]]);

	foreach ($images as $image) {
		$updateImages[] = $image['local'];
	}

	$response = json_encode(array(
		'images' => $updateImages,
	));
} elseif ($action === 'deleteFavorites') {
	$updateImages = [];
	$image_ids = (array)@$_REQUEST['images'];

	$deviantartTop->db->images->update(['id' => ['$in' => $image_ids]], [
		'$set' => [
			'local_deleted' => time(),
		],
	], [
		'multiple' => true,
	]);

	$images = $deviantartTop->db->images->find(['id' => ['$in' => $image_ids]]);

	foreach ($images as $image) {
		$updateImages[] = $image['local'];
	}

	$response = json_encode(array(
		'images' => $updateImages,
	));
}

echo $response;
