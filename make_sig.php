<?php

require_once 'imagesig.class.php';
$imageSig = new ImageSig();

$images = json_decode(file_get_contents('data/images.json'), true);

$count = 0;
$images_count = count($images);

$sigs = array();

$time = microtime(true);
$imgsec = 1;

foreach ($images as $image) {
	//if ($count === 100) break;
	$count++;

	$filename = 'images/mythumbs/'.$image['filename'];
	if (file_exists($filename)) {
		$sigs[$image['id']] = $imageSig->makeSig($filename, 'images/sig/'.$image['filename'].'.png');
	}

	if ($count % 50 == 0) {
		$imgsec = $count / (microtime(true) - $time);
	}

	echo "\rdone: ".round($count/$images_count*100)."% count: $count/$images_count img/sec: ".round($imgsec)." remain: ".round(($images_count-$count)/$imgsec/60).'m        ';

	if ($count % 200 == 0) {
		file_put_contents('data/images_sigs.json', json_encode($sigs));
	}
}

echo "\n";

file_put_contents('data/images_sigs.json', json_encode($sigs));
