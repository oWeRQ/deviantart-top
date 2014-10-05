<?php

require_once 'classes/autoload.php';

Deviantart::$silent = true;
$deviantart = new Deviantart;
$deviantartTop = new DeviantartTop;

$galleriesMap = [];
foreach ($deviantartTop->getData('galleries') as $gallery) {
	$galleriesMap[$gallery['title']] = $gallery['galleryid'];
}

$cursor = $deviantartTop->db->images->find([
	'$where' => '(this.local.galleries && this.local.galleries.sort().toString()) != (this.server.galleries && this.server.galleries.sort().toString())',
	'server_deleted' => false,
	'server_error' => ['$exists' => false],
]);
$cursorCount = $cursor->count();

$userId = 16413375;
$maxCalls = min(48, $cursorCount);
$calls = [];
$updates = [];

$progress = new Progress($cursorCount);
foreach ($cursor as $image) {
	$progress->step();

	$updates[$image['id']] = [
		'server.galleries' => $image['local']['galleries'],
		'server_updated' => time(),
	];

	$addGalleries = array_diff($image['local']['galleries'], $image['server']['galleries']);
	$removeGalleries = array_diff($image['server']['galleries'], $image['local']['galleries']);

	foreach ($addGalleries as $addGallery) {
		$calls[] = [
			'object' => 'Gallections',
			'method' => 'add_resource',
			'params' => array(
				$userId,
				21,
				$galleriesMap[$addGallery],
				1,
				$image['id'],
				0,
			),
		];
	}

	foreach ($removeGalleries as $removeGallery) {
		$calls[] = [
			'object' => 'Gallections',
			'method' => 'remove_resource',
			'params' => [
				$userId,
				21,
				$galleriesMap[$removeGallery],
				1,
				$image['id'],
			],
		];
	}

	if (count($calls) >= $maxCalls) {
		$responses = $deviantart->sendCalls($calls, 'post', 1);

		if (is_array($responses)) {
			foreach ($responses as $response) {
				if ($response['request']['method'] == 'add_resource')
					$image_id = $response['request']['args'][4];
				elseif ($response['request']['method'] == 'remove_resource')
					$image_id = $response['request']['args'][4];
				else
					continue;

				if ($response['response']['status'] != 'SUCCESS') {
					//var_dump($response);
					$deviantartTop->db->images->update(['id' => (string)$image_id], ['$inc' => ['server_error' => 1]]);
					unset($updates[$image_id]);
				}
			}

			foreach ($updates as $image_id => $update) {
				$deviantartTop->db->images->update(['id' => (string)$image_id], ['$set' => $update]);
			}
		}

		$calls = [];
		$updates = [];
	}
}
$progress->end();