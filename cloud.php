<?php

require_once 'classes/autoload.php';

$deviantartTop = new DeviantartTop;

$count = 200;

$minSize = 0.75;
$maxSize = 2.25;
$unit = 'em';

$query = @$_REQUEST['q'];
$sort = @$_REQUEST['sort'] or $sort = 'name';

$keywords = $deviantartTop->getData('keywords', [], [
	'sort' => ['local.count' => -1],
	'limit' => $count,
]);

$max = current($keywords)['count'];
$min = end($keywords)['count'];

if ($sort === 'name') {
	ksort($keywords);
}

?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>devianART Cloud</title>
	<style>
		html {
			font: 12px sans-serif;
		}
		body {
			margin: 12px auto;
			width: 800px;
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
			padding: 20px;
			text-align: justify;
			word-spacing: 8px;
			font: 12pt sans-serif;
			background: #f6f6f6;
		}
	</style>
</head>
<body>
	<div class="sort">
		sort:
		<a href="?sort=name">name</a>
		| <a href="?sort=count">count</a>
	</div>
	<div class="cloud">
		<?
		$constant = log($max-$min) / ($maxSize-$minSize);
		?>
		<? foreach ($keywords as $keyword => $images): ?>
			<?
			$size = log($images['count']-$min) / $constant + $minSize;
			?>
			<a href=".?title=<?=$keyword?>" target="_blank" title="Count: <?=$images['count']?>" class="<?=$keyword===$query?'active':''?>" style="font-size:<?=round($size, 2)?><?=$unit?>"><?=$keyword?></a>
		<? endforeach ?>
	</div>
</body>
</html>