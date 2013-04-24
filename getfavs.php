<?php

require_once 'deviantart.class.php';
Deviantart::$cache_time = 3600*2;
$deviantart = new Deviantart;

$username = 'oWeRQ';
$userid = 16413375;
$perPage = 24;
$perRequest = 40;

$update_galleries = false;
$images = array();

if ($argc > 1) {	
	$update_galleries = array_map('strtolower', array_slice($argv, 1));
	$images = json_decode(file_get_contents('data/images.json'), true);
}

$galleries = $deviantart->getFavGalleries($userid);

foreach ($galleries as $gallery) {
	if ($update_galleries && !in_array(strtolower($gallery['title']), $update_galleries))
		continue;

	$t = microtime(true);

	$offset = 0;

	$pages = ceil($gallery['approx_total'] / $perPage);
	$maxOffset = ($pages-1) * $perPage;

	while ($offset < $maxOffset) {
		$calls = array();

		for ($i = 0; $i < $perRequest; $i++) {
			$calls[] = array(
				'object' => "Resources",
				'method' => "htmlFromQuery",
				'params' => array(
					"favby:$username/".$gallery['galleryid'],
					$offset,
					$perPage,
					"thumb150",
					"artist:0,title:1",
				),
			);

			if ($offset >= $maxOffset)
				break;

			$offset += $perPage;
		}

		$requests = $deviantart->sendCalls($calls);

		foreach ($requests as $request) {
			foreach ($request['response']['content']['resources'] as $resource) {
				$image = $deviantart->parsePageResource($resource);

				if (isset($images[$image['id']])) {
					$images[$image['id']]['galleries'][] = $gallery['title'];
					$images[$image['id']]['galleries'] = array_values(array_unique($images[$image['id']]['galleries']));
				} else {
					$image['galleries'] = array($gallery['title']);
					$images[$image['id']] = $image;
				}
			}
		}
	}

	echo 'done: '.$gallery['title'].': '.(microtime(true)-$t)."s\n";
}

unset($images['']);

file_put_contents('data/images.json', json_encode($images));
