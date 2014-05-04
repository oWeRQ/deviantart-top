<?php

require_once 'classes/autoload.php';

$deviantartTop = new DeviantartTop;

$count = 200;

$minSize = 0.75;
$maxSize = 2.25;
$unit = 'em';

$query = @$_REQUEST['q'];

$keywords = $deviantartTop->getData('keywords');

$keywords = array_slice($keywords, 0, $count, true);

$max = count(current($keywords));
$min = count(end($keywords));

ksort($keywords);

?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>devianART Cloud</title>
	<style>
		html {
			font: 12pt sans-serif;
		}
		a {
			text-decoration: none;
			color: #49e;
		}
		a:hover {
			color: #27c;
		}
		a.active {
			color: #999;
		}
		.cloud {
			margin: 0 auto;
			padding: 20px;
			width: 720px;
			text-align: justify;
			word-spacing: 8px;
			background: #f6f6f6;
		}
	</style>
</head>
<body>
	<div class="cloud">
		<?
		$constant = log($max-$min) / ($maxSize-$minSize);
		?>
		<? foreach ($keywords as $keyword => $images): ?>
			<?
			$size = log(count($images)-$min) / $constant + $minSize;
			?>
			<a href=".?title=<?=$keyword?>" target="_blank" title="Count: <?=count($images)?>" class="<?=$keyword===$query?'active':''?>" style="font-size:<?=round($size, 2)?><?=$unit?>"><?=$keyword?></a>
		<? endforeach ?>
	</div>
</body>
</html>