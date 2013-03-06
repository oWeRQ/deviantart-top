<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Deviantart Top</title>
	<link rel="stylesheet" href="css/jquery.modalWindow.css">
	<link rel="stylesheet" href="css/jquery.thumbsSlider.css">
	<link rel="stylesheet" href="css/jquery.gallery.css">
	<link rel="stylesheet" href="css/gallery.custom.css">
	<link rel="stylesheet" href="css/index.css">
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
	<script src="js/jquery.plugin.js"></script>
	<script src="js/jquery.overlay.js"></script>
	<script src="js/jquery.windowCenter.js"></script>
	<script src="js/jquery.modalWindow.js"></script>
	<script src="js/jquery.thumbsSlider.js"></script>
	<script src="js/jquery.gallery.js"></script>
	<script src="js/index.js"></script>
</head>
<body>

<div class="l-sidebar">
	<form>
		<h3>Collections</h3>
		<div class="checkboxes">
			<small>
				<a href="#" class="checkAll">Check all</a>
				| <a href="#" class="uncheckAll">Uncheck all</a>
			</small>
			<? foreach ($galleries as $gallery): ?>
				<label>
					<input type="checkbox" name="galleries[]" value="<?=$gallery['title']?>"
						<? if (in_array($gallery['title'], $checked_galleries)): ?>checked <? endif ?>>
					<?=$gallery['title']?>
					<small>(<?=$gallery['approx_total']?>)</small>
				</label>
			<? endforeach ?>
		</div>

		<h3>Limits</h3>
		<div class="row inline">
			<label>Favorites:</label>
			<input type="text" name="minFavs" value="<?=$minFavs?>">
			<a href="#" class="clearInput"></a>
		</div>
		<div class="row inline">
			<label>Deviations:</label>
			<input type="text" name="minDevia" value="<?=$minDevia?>">
			<a href="#" class="clearInput"></a>
		</div>
		<div class="row inline">
			<label>Top:</label>
			<input type="text" name="topLimit" value="<?=$topLimit?>">
			<a href="#" class="clearInput"></a>
		</div>
		<div class="row inline">
			<label>Images:</label>
			<input type="text" name="imagesLimit" value="<?=$imagesLimit?>">
			<a href="#" class="clearInput"></a>
		</div>

		<h3>Author</h3>
		<div class="row">
			<input type="text" name="username" value="<?=$username?>" list="authorsList">
			<a href="#" class="clearInput"></a>
		</div>
		
		<div class="row actions">
			<input type="submit" value="Show">
		</div>
	</form>
</div>

<div class="l-content">
	<? foreach ($authors as $i => $author): ?>
		<div class="b-author">
			<h3>
				<? if (count($authors) > 1): ?>
					<span class="number"><?=$i+1?></span>
				<? endif ?>
				<a target="_blank" href="?<?=$limitsParams?>&amp;username=<?=$author['username']?>"><?=$author['username']?></a>
				<small><?=$author['percent']?>%</small>
				<sup><a target="_blank" href="http://<?=$author['username']?>.deviantart.com/gallery/">DA</a></sup>
			</h3>
			<div class="b-images" data-username="<?=$author['username']?>" data-images-total="<?=$author['total']?>" data-images-loaded="<?=count($author['images'])?>">
			<?
				foreach ($author['images'] as $image) { 
					echo '<a href="images/'.$image['filename'].'" target="_blank" title="'.join(', ', $image['galleries']).'" data-id="'.$image['id'].'">';
					echo '<img src="images/mythumbs/'.$image['filename'].'">';
					echo '</a>';
				}
			?></div>
			<? if(count($author['images']) < $author['total']): ?>
				<a href="#" class="moreImages">More images (<span class="count"><?=$author['total']-count($author['images'])?></span>)</a>
			<? endif ?>
		</div>
	<? endforeach ?>
</div>

<datalist id="authorsList">
	<? foreach ($profiles as $profile): ?>
		<option value="<?=$profile['username']?>">
	<? endforeach ?>
</datalist>

</body>
</html>