<?php

require_once 'classes/autoload.php';

$deviantartTop = new DeviantartTop;
$imageSig = new ImageSig;

$images = $deviantartTop->db->images->find([
	//'sig' => ['$exists' => false],
	'$or' => [
		['sig' => ['$exists' => false]],
		['sig.code' => null],
		['sig.code' => false],
		['sig.code' => ''],
		['sig.code' => []],
	],
]);

$progress = new Progress($images->count());
foreach ($images as $image) {
	$progress->step();

	if (!isset($image['sig']) || empty($image['sig']['code'])) {
		$image_filename = 'images/mythumbs/'.$image['local']['filename'];
		$sig_filename = 'images/sig/'.$image['local']['filename'].'.png';
		$sig_code = null;
		if (file_exists($image_filename)) {
			$sig_code = $imageSig->makeSig($image_filename, $sig_filename);
		}
		
		$deviantartTop->db->images->update(['_id' => $image['_id']], [
			'$set' => [
				'sig.code' => $sig_code,
			],
		]);
	}
}
$progress->end();
