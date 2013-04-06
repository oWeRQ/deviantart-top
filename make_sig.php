<?php

require_once 'colormap.php';

$images = json_decode(file_get_contents('images_with_galleries.json'), true);

$size = 16;

$map = new Imagick();
$map->newImage(1, 32, 'none');
$map->setImageType(imagick::IMGTYPE_PALETTE);
//$map->setImageType(imagick::IMGTYPE_PALETTEMATTE);

$color_indexes = [];

foreach ($colormap as $i => $color) {
	$color_indexes[pack('CCC', $color[0], $color[1], $color[2])] = dechex($i);
	$map->setImageColormapColor($i, 'rgb('.join(',', $color).')');
}

$count = 0;
$images_count = count($images);

$sigs = array();

$time = microtime(true);
$imgsec = 1;

foreach ($images as $image) {
	//if ($count === 100) break;
	$count++;

	$sig = new Imagick('images/'.$image['filename']);

	$sig->scaleImage($size, $size);
	$sig->mapImage($map, false);

	$sig->setImageFormat('png');
	$sig->writeImage('images/sig/'.$image['filename'].'.png');	

	$sig->setImageFormat('rgb');
	$blob = $sig->getImageBlob();
	$sig->destroy();

	$sig_data = '';

	for ($i = 0; $i < 768; $i+=3) {
		$raw_color = substr($blob, $i, 3);
		if (isset($color_indexes[$raw_color])) {
			$sig_data .= $color_indexes[$raw_color];
		} else {
			$sig_data .= 0;
		}
	}
	
	$sigs[$image['id']] = $sig_data;

	if ($count % 50 == 0) {
		$imgsec = $count / (microtime(true) - $time);
	}

	echo "\rdone: ".round($count/$images_count*100)."% count: $count/$images_count img/sec: ".round($imgsec)." remain: ".round(($images_count-$count)/$imgsec/60).'m        ';

	if ($count % 200 == 0) {
		file_put_contents('images_sigs.json', json_encode($sigs));
	}
}

echo "\n";

file_put_contents('images_sigs.json', json_encode($sigs));
