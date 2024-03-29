<?php

require_once 'classes/autoload.php';

Deviantart::$silent = true;
$deviantart = new Deviantart;
$deviantartTop = new DeviantartTop;

$cursor = $deviantartTop->db->images->find([
	'local_deleted' => ['$ne' => false],
	'server_deleted' => false,
	'server_error' => ['$exists' => false],
]);

$maxCalls = 1;
$calls = [];

$progress = new Progress($cursor->count());
foreach ($cursor as $image) {
	$progress->step();

	$calls[] = [
		'object' => 'Deviation',
		'method' => 'Favourite',
		'params' => [
			$image['id'],
		],
	];

	if (count($calls) >= $maxCalls) {
		$responses = $deviantart->sendCalls($calls, 'post', 1);

		if (!is_array($responses)) {
			continue;
		}

		foreach ($responses as $response) {
			$image_id = $response['request']['args'][0];

			if ($response['response']['status'] != 'SUCCESS') {	
				$deviantartTop->db->images->update(['id' => $image_id], [
					'$inc' => [
						'server_error' => 1,
					],
				]);
				var_dump($response);
				//die();
				continue;
			}

			if ($response['response']['content'] == 'Favourite removed') {
				$deviantartTop->db->images->update(['id' => $image_id], [
					'$set' => [
						'server_deleted' => time(),
					],
				]);
			} elseif ($response['response']['content'] == 'Favourite added') {
				$deviantartTop->db->images->update(['id' => $image_id], [
					'$set' => [
						'server_deleted' => false,
					],
				]);
			} else {
				//var_dump($response);
				//die();
				continue;
			}
		}

		$calls = [];

		sleep(3);
	}
}
$progress->end();