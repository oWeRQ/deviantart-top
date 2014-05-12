<?php

require_once 'classes/autoload.php';

$deviantartTop = new DeviantartTop;

$images = $deviantartTop->getData('images', [
	'local.galleries' => ['$in' => ['Abstract']],
]);

if (isset($_GET['seed']))
	$seed = $_GET['seed'];
else
	$seed = time() % (3600*24);

mt_srand($seed);

foreach ($images as $i => $image) {
	$images[$i]['rand'] = mt_rand();
}

usort($images, function($a, $b){
	return ($a['rand'] > $b['rand']) ? -1 : 1;
});

$images = array_slice($images, 0, 100);

?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Images Justify</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
	<script>
		function ImagesJustify(el){
			this.init(el);
		}

		ImagesJustify.prototype = {
			spacing: 5,
			images: $(),
			imagesWidths: {},
			$el: null,
			init: function(el){
				this.$el = $(el);
				this.addImages(this.$el.find('img'));
			},
			addImages: function(images){
				var that = this;

				var imageLoaded = function(){
					that.process();
				};

				var wait = 0;

				$.each(images, function(i, image){
					that.images.push(image);
					if (!image.complete) {
						wait++;
						image.addEventListener('load', imageLoaded, false);
					}
				});

				console.log('wait images: ', wait);

				this.process();
			},
			process: function(){
				var that = this,
					totalWidth = this.$el.innerWidth()-1,
					margin = 0,
					rowWidth = 0,
					rowHImages = $(),
					rowVImages = $();

				this.images.each(function(i, image){
					rowWidth += image.width + that.spacing;

					if (image.width > image.height)
						rowHImages.push(image);
					else
						rowVImages.push(image);

					if (rowWidth > totalWidth) {
						if (rowHImages.length > rowVImages.length) {
							margin = (totalWidth-rowWidth) / rowHImages.length / 2;
							rowVImages.css({
								margin: 0
							});
						} else {
							margin = (totalWidth-rowWidth) / (rowHImages.length + rowVImages.length) / 2;
							rowVImages.css({
								margin: '0 '+margin+'px'
							});
						}
						rowHImages.css({
							margin: '0 '+margin+'px'
						});

						rowWidth = 0;
						rowHImages.length = 0;
						rowVImages.length = 0;
					}
				});
			}
		};

		$(function(){
		//$(window).load(function(){
			var beforeHeight = document.documentElement.offsetHeight;
			var imagesJustify = new ImagesJustify('.justify-images');
			var afterHeight = document.documentElement.offsetHeight; 
			console.log(beforeHeight, '-', afterHeight, '=', beforeHeight-afterHeight, 'rows:', Math.floor(afterHeight / 120), '+', Math.floor((beforeHeight-afterHeight) / 120));

			$(window).resize(function(){
				imagesJustify.process();
			});
		});
	</script>
	<style>
		.justify-images {
			list-style: none;
			margin: 0;
			padding: 0;
			padding-left: 0.31em;
		}
		.justify-images li {
			display: inline-block;
			vertical-align: top;
			margin-bottom: 4px;
			margin-right: 4px;
			margin-left: -0.31em;
		}
		.justify-images a {
			overflow: hidden;
			display: block;
		}
		.justify-images a:hover {
			overflow: visible;
		}
		.justify-images a:hover img {
			position: relative;
			outline: 6px solid white;
		}
		.justify-images img {
			display: block;
		}
	</style>
</head>
<body>
	Seed: <a href="?seed=<?=$seed?>"><?=$seed?></a>
	<div id="output"></div>
	<ul class="justify-images">
		<? foreach ($images as $image): ?>
			<li>
				<a href="images/original/<?=$image['filename']?>" target="_blank">
					<img src="images/mythumbs/<?=$image['filename']?>" alt="">
				</a>
			</li>
		<? endforeach ?>
	</ul>
</body>
</html>