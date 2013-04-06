<?php

$id = intval(@$_REQUEST['id']);

$simular = array();

$images = json_decode(file_get_contents('images_with_galleries.json'), true);
$sigs = json_decode(file_get_contents('images_sigs.json'), true);

if ($id && isset($sigs[$id])) {

	$find_image = $images[$id];

	$time_start = microtime(true);

	$find_sig = $sigs[$id];

	$diff = array();
	foreach ($sigs as $image_id => $sig) {
		$image_diff = 0;

		for ($i = 0; $i < 256; $i++) {
			if ($sig[$i] !== $find_sig[$i])
				$image_diff++;
		}

		$diff[$image_id] = $image_diff/256;
	}

	$time = round(time() - $time_start, 2);

	asort($diff);

	$count = 100;
	foreach ($diff as $image_id => $d) {
		if (--$count < 0)
			break;

		$simular[] = $images[$image_id];
	}
}

?><!DOCTYPE html>
<html>
<head>
	<title>Similar</title>
	<style>
		html {
			font: 12px sans-serif;
		}
		img {
			display: block;
			vertical-align: middle;
		}
		.image {
			overflow: hidden;
			position: relative;
			display: inline-block;
			margin-bottom: 4px;
			padding-bottom: 16px;
			text-decoration: none;
			background: #ddd;
			border: 4px solid #ddd;
		}
		.sig {
			position: absolute;
			bottom: 0;
			right: 0;
			width: 32px;
			height: 32px;
			border-top: 2px solid #ddd;
			border-left: 2px solid #ddd;
		}
		.diff {
			position: absolute;
			bottom: 0;
			right: 36px;
			color: black;
		}
	</style>
</head>
<body>
	<form>
		<input type="text" name="id" value="<?=$id?>">
		<input type="submit" value="Find">
		<a href="?">Examples</a>
	</form>

	<? if ($find_image): ?>
		<p>Time: <?=$time?>s</p>

		<h2>Find</h2>
		<a class="image" href="images/<?=$find_image['filename']?>" target="_blank">
			<img src="images/mythumbs/<?=$find_image['filename']?>" alt="">
			<img class="sig" src="images/sig/<?=$find_image['filename']?>.png">
		</a>

		<h2>Simular</h2>
		<div class="images-list">
		<? foreach ($simular as $image): ?>
			<a class="image" href="?id=<?=$image['id']?>">
				<img src="images/mythumbs/<?=$image['filename']?>">
				<img class="sig" src="images/sig/<?=$image['filename']?>.png">
				<span class="diff" title="&#916;<?=round($diff[$image['id']])?>"><?=100-round($diff[$image['id']]*100)?>%</span>
			</a>
		<? endforeach ?>
		</div>
	<? else: ?>
		<h2>Examples</h2>
		<?
		$rand_images = array_keys($sigs);
		shuffle($rand_images);
		foreach (array_slice($rand_images, 0, 6) as $rand_image_id) {
			?>
			<a class="image" href="?id=<?=$rand_image_id?>">
				<img src="images/mythumbs/<?=$images[$rand_image_id]['filename']?>" alt="">
				<img class="sig" src="images/sig/<?=$images[$rand_image_id]['filename']?>.png">
			</a>
			<?
		}
	?>
	<? endif ?>
</body>
</html>