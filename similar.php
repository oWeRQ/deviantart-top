<?php

require_once 'imagesig.class.php';
$imageSig = new ImageSig();

$image_id = @$_REQUEST['id'];
$same_author = isset($_REQUEST['same_author']);
$same_galleries = isset($_REQUEST['same_galleries']);

$simular = array();

$images = json_decode(file_get_contents('images_with_galleries.json'), true);
$sigs = json_decode(file_get_contents('images_sigs.json'), true);

if ($image_id) {
	$time_start = microtime(true);

	if (ctype_digit($image_id)) {
		$find_image = $images[$image_id];
		$find_image_original = 'images/'.$find_image['filename'];
		$find_image_url = 'images/mythumbs/'.$find_image['filename'];
		$find_image_sig = 'images/sig/'.$find_image['filename'].'.png';

		$find_sig = $sigs[$image_id];
	} else {
		$same_author = false;
		$same_galleries = false;

		$find_image = [
			'filename' => $image_id,
		];
		$find_image_original = $find_image_url = $image_id;
		$find_image_sig = 'cache/sig/'.md5($image_id).'.png';

		$find_sig = $imageSig->makeSig($image_id, $find_image_sig);
	}

	$diff = array();
	foreach ($sigs as $sig_image_id => $sig) {
		if (empty($sig)) {
			$diff[$sig_image_id] = 1;
		} else {
			$image_diff = 0;

			for ($i = 0; $i < 256; $i++) {
				if ($sig[$i] !== $find_sig[$i])
					$image_diff++;
			}

			$diff[$sig_image_id] = $image_diff/256;
		}
	}

	$time = round(time() - $time_start, 2);

	// output process
	asort($diff);

	$count = 100;
	foreach ($diff as $diff_image_id => $d) {
		$diff_image = $images[$diff_image_id];

		if ($diff_image == null)
			continue;

		if ($same_author) {
			if ($diff_image['author'] !== $find_image['author'])
				continue;
		}

		if ($same_galleries) {
			$diff_count = count(array_diff($diff_image['galleries'], $find_image['galleries']));

			if ($diff_count !== 0)
				continue;
		}

		if (--$count < 0)
			break;

		$simular[] = $diff_image;
	}
}

?><!DOCTYPE html>
<html>
<head>
	<title>deviantART Similar</title>
	<style>
		html {
			font: 12px sans-serif;
		}
		input {
			vertical-align: middle;
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
		Image:
		<input type="text" name="id" value="<?=$image_id?>" size="40">
		<label>
			<input type="checkbox" name="same_author" value="1" <?if($same_author):?>checked<?endif?>>
			Same author
		</label>
		<label>
			<input type="checkbox" name="same_galleries" value="1" <?if($same_galleries):?>checked<?endif?>>
			Same galleries
		</label>
		<input type="submit" value="Find">
		<a href="?">Examples</a>
	</form>

	<? if ($find_image): ?>
		<p>Time: <?=$time?>s</p>

		<h2>Find</h2>
		<a class="image" href="<?=$find_image_original?>" target="_blank">
			<img src="<?=$find_image_url?>" height="120" alt="">
			<img class="sig" src="<?=$find_image_sig?>">
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
		$example_images = [
			183940514,
			207448555,
			294265905,
			358683509,
			202363262,
			209246902,
			76115732,
			341762714,
			264031381,
			337186363,
		];
		foreach ($example_images as $example_image_id) {
			?>
			<a class="image" href="?id=<?=$example_image_id?>">
				<img src="images/mythumbs/<?=$images[$example_image_id]['filename']?>" alt="">
				<img class="sig" src="images/sig/<?=$images[$example_image_id]['filename']?>.png">
			</a>
			<?
		}
		?>

		<h2>Random</h2>
		<?
		$rand_images = array_keys($sigs);
		shuffle($rand_images);
		foreach (array_slice($rand_images, 0, count($example_images)) as $rand_image_id) {
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