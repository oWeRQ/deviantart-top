<?php

require_once 'classes/autoload.php';

//Deviantart::$cache_time = 3600*3;
$deviantart = new Deviantart;
$deviantartTop = new DeviantartTop;

$username = 'oWeRQ';
$userid = 16413375;
$perPage = 24;
$perRequest = 1;
$trys = 3;

$update_galleries = false;
$images = array();

if ($argc > 1) {	
	$update_galleries = array_map('strtolower', array_slice($argv, 1));
} else {
	$deviantartTop->db->images->update(array(
		'server_deleted' => false,
	), array(
		'$set' => array(
			'server_deleted' => time(),
		),
	), array(
		'multiple' => true,
	));
}

$galleries = $deviantart->getFavGalleries($userid);

if (!is_array($galleries)) {
	echo "Error in getFavGalleries: ";
	var_dump($galleries);
	die();
}

foreach ($galleries as $gallery) {
	if ($update_galleries && !in_array(strtolower($gallery['title']), $update_galleries))
		continue;

	echo 'start: '.$gallery['title']."\n";

	$t = microtime(true);

	$offset = 0;

	$pages = ceil($gallery['approx_total'] / $perPage);
	$maxOffset = ($pages-1) * $perPage;

	while ($offset < $maxOffset) {
		$existImagesCount = 0;
		
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

		$requests = $deviantart->sendCalls($calls, 'get', 10, 3);

		foreach ($requests as $request) {
			foreach ($request['response']['content']['resources'] as $resource) {
				$image = $deviantart->parsePageResource($resource);

				if ($image === null || empty($image['id']))
					continue;

				$mongoImage = $deviantartTop->db->images->findOne(array('id' => $image['id']));

				if ($mongoImage !== null) {
					if (in_array($gallery['title'], $mongoImage['server']['galleries'])) {
						$existImagesCount++;
					} else {
						$deviantartTop->db->images->update(['_id' => $mongoImage['_id']], [
							'$set' => [
								'server_updated' => time(),
								'server_deleted' => false,
							],
							'$addToSet' => [
								'server.galleries' => $gallery['title'],
							],
						]);
					}
				} else {
					$image['galleries'] = array($gallery['title']);

					$deviantartTop->db->images->insert([
						'id' => $image['id'],
						'local' => $image,
						'local_created' => time(),
						'local_updated' => time(),
						'local_deleted' => false,
						'server' => $image,
						'server_created' => time(),
						'server_updated' => time(),
						'server_deleted' => false,
					]);
				}
			}
		}

		//if ($existImagesCount == $perPage * $perRequest)

		if ($existImagesCount >= $perPage)
			break;
	}

	echo 'end: '.$gallery['title'].' '.round(microtime(true)-$t)."s\n";
}
