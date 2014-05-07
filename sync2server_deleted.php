<?php

require_once 'classes/autoload.php';

Deviantart::$silent = true;
$deviantart = new Deviantart;
$deviantartTop = new DeviantartTop;

$cursor = $deviantartTop->db->images->find([
	'local_deleted' => ['$ne' => false],
	'server_deleted' => false,
]);

$userId = 16413375;
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
			var_dump($responses);
			die();
		}

		foreach ($responses as $response) {
			if ($response['response']['status'] != 'SUCCESS') {	
				var_dump($response);
				die();
			}

			$image_id = $response['request']['args'][0];

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
				var_dump($response);
				die();
			}
		}

		$calls = [];
	}
}
$progress->end();