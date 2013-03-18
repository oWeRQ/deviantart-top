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
	<script src="js/jquery.iskey.js"></script>
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
			<div>
				<small>
					<a href="#" class="checkAll">Check All</a>
					| <a href="#" class="uncheckAll">Uncheck All</a>
				</small>
			</div>
			<? foreach ($galleries as $gallery): ?>
				<input class="exclude" type="checkbox" name="exclude[]" value="<?=$gallery['title']?>"
					<? if (in_array($gallery['title'], $exclude_galleries)): ?>checked <? endif ?>>
				<label>
					<input type="checkbox" name="galleries[]" value="<?=$gallery['title']?>"
						<? if (in_array($gallery['title'], $checked_galleries)): ?>checked <? endif ?>>
					<?=$gallery['title']?>
					<small>(<?=$gallery['approx_total']?>)</small>&nbsp;
				</label>
			<? endforeach ?>
		</div>
		<div class="row">
			<label><input type="radio" name="condition" value="or" <?if($condition=='or'):?>checked<?endif?>> OR</label>
			<label><input type="radio" name="condition" value="and" <?if($condition=='and'):?>checked<?endif?>> AND</label>
			<label><input type="radio" name="condition" value="xor" <?if($condition=='xor'):?>checked<?endif?>> XOR</label>
		</div>

		<h3>Limits</h3>
		<div class="row b-inline">
			<label>Favorites:</label>
			<input type="text" name="minFavs" value="<?=$minFavs?>">
			<a href="#" class="clearInput"></a>
		</div>
		<div class="row b-inline">
			<label>Max Favs:</label>
			<input type="text" name="maxFavs" value="<?=$maxFavs?>">
			<a href="#" class="clearInput"></a>
		</div>
		<div class="row b-inline">
			<label>Deviations:</label>
			<input type="text" name="minDevia" value="<?=$minDevia?>">
			<a href="#" class="clearInput"></a>
		</div>
		<div class="row b-inline">
			<label>Images:</label>
			<input type="text" name="imagesLimit" value="<?=$imagesLimit?>">
			<a href="#" class="clearInput"></a>
		</div>
		<div class="row b-inline">
			<label>Top:</label>
			<input type="text" name="topLimit" value="<?=$topLimit?>">
			<a href="#" class="clearInput"></a>
		</div>
		<div class="row b-inline">
			<label>Page:</label>
			<input type="text" name="page" value="<?=$page?>">
			<a href="#" class="clearInput"></a>
		</div>

		<h3>Author</h3>
		<div class="row">
			<input type="text" name="username" value="<?=$username?>" list="authorsList">
			<a href="#" class="clearInput"></a>
		</div>

		<h3>Title</h3>
		<div class="row">
			<input type="text" name="title" value="<?=$title?>">
			<a href="#" class="clearInput"></a>
		</div>

		<h3>Sort</h3>
		<div class="row">
			<select name="sort">
				<? foreach (array(
					'percent'=>'Percent',
					'favourites'=>'Favourites',
					'deviations'=>'Deviations',
				) as $value => $name): ?>
					<option value="<?=$value?>" <?if($value==$sort):?>selected<?endif?>><?=$name?></option>
				<? endforeach ?>
			</select>
		</div>
		
		<div class="row actions">
			<input type="submit" value="Show">
		</div>
	</form>
</div>

<div class="l-content">
	<? if ($top && $page > 1): ?>
		<a href="?<?=$galleriesParams?>&amp;<?=$limitsParams?>&amp;title=<?=$title?>&amp;page=<?=$page-1?>" class="m-button showPrev">Show Prev</a>
	<? endif ?>

	<div class="authors-list">
		<div id="page_<?=$page?>" class="page" data-num="<?=$page?>">
			<? foreach ($authors as $i => $author): ?>
				<? require 'index.item.tpl.php'; ?>
			<? endforeach ?>
		</div>
	</div>

	<? if (count($authors) === $topLimit): ?>
		<a href="?<?=$galleriesParams?>&amp;<?=$limitsParams?>&amp;title=<?=$title?>&amp;page=<?=$page+1?>" class="m-button showMore">Show More</a>
	<? endif ?>
</div>

<datalist id="authorsList">
	<? foreach ($profiles as $profile): ?>
		<option value="<?=$profile['username']?>">
	<? endforeach ?>
</datalist>

<div class="moveMenu">
	<ul>
		<? foreach ($galleries as $gallery): ?>
			<li>
				<a href="#" data-id="<?=$gallery['galleryid']?>" data-title="<?=$gallery['title']?>"><?=$gallery['title']?></a>
			</li>
		<? endforeach ?>
	</ul>
</div>

<div class="updateControl">
	<a href="#" class="closeControl closeButton"></a>
	<form action="updateImage.php" method="post">
		<input type="hidden" name="action" value="setGalleries">
		<input type="hidden" name="username" value="">
		<input type="hidden" name="image_id" value="">

		<h3>Collections</h3>
		<div class="checkboxes">
			<? foreach ($galleries as $gallery): ?>
				<label>
					<input type="checkbox" name="galleries[]" value="<?=$gallery['title']?>">
					<?=$gallery['title']?>
				</label>
			<? endforeach ?>
		</div>
		<div class="row actions">
			<input type="submit" value="Save">
			or
			<a href="#" class="closeControl">Cancel</a>
		</div>
	</form>
</div>

</body>
</html>