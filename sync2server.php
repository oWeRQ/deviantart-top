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
]);

$userId = 16413375;
$maxCalls = 40;
$calls = [];
$updates = [];

$progress = new Progress($cursor->count());
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
			'object' => 'Aggregations',
			'method' => 'add_resource',
			'params' => array(
				$userId,
				0,
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

		foreach ($responses as $response) {
			if ($response['request']['method'] == 'add_resource')
				$image_id = $response['request']['args'][5];
			elseif ($response['request']['method'] == 'remove_resource')
				$image_id = $response['request']['args'][4];
			else
				continue;

			if ($response['response']['status'] != 'SUCCESS')
				unset($updates[$image_id]);
		}

		foreach ($updates as $image_id => $update) {
			$deviantartTop->db->images->update(['id' => $image_id], ['$set' => $update]);
		}

		$calls = [];
		$updates = [];
	}
}
$progress->end();