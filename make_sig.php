<?php

require_once 'classes/autoload.php';

$deviantartTop = new DeviantartTop;
$imageSig = new ImageSig;

$images = $deviantartTop->db->images->find([
	'sig' => ['$exists' => false],
]);

$progress = new Progress($images->count());
foreach ($images as $image) {
	$progress->step();

	if (!isset($image['sig'])) {
		$image_filename = 'images/mythumbs/'.$image['local']['filename'];
		$sig_filename = 'images/sig/'.$image['local']['filename'].'.png';
		if (file_exists($image_filename)) {
			$image['sig'] = [
				'code' => $imageSig->makeSig($image_filename, $sig_filename),
			];
		}
		
		$deviantartTop->db->images->update(['_id' => $image['_id']], [
			'$set' => [
				'sig' => $image['sig'],
			],
		]);
	}
}
$progress->end();
