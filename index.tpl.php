<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>deviantART Top</title>
	<link rel="stylesheet" href="css/jquery.modalWindow.css">
	<link rel="stylesheet" href="css/jquery.thumbsSlider.css">
	<link rel="stylesheet" href="css/jquery.gallery.css">
	<link rel="stylesheet" href="css/gallery.custom.css">
	<link rel="stylesheet" href="css/index.css">
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
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
		<div class="row">
			<input type="text" name="title" value="<?=$title?>" placeholder="Search" list="searchList"><a href="#" class="clearInput"></a>
		</div>

		<h3>Collections</h3>
		<div class="checkboxes">
			<div class="controls">
				<a href="#" class="checkAll">Check All</a>
				| <a href="#" class="uncheckAll">Uncheck All</a>
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
			<label><input type="radio" name="condition" value="only" <?if($condition=='only'):?>checked<?endif?>> ONLY</label>
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

		<h3>Sort</h3>
		<div class="row b-inline">
			<select name="sort">
				<? foreach (array(
					'score'=>'Score',
					'wilson_score'=>'Wilson Score',
					'percent'=>'Percent',
					'favourites'=>'Favourites',
					'deviations'=>'Deviations',
				) as $value => $name): ?>
					<option value="<?=$value?>" <?if($value==$sort):?>selected<?endif?>><?=$name?></option>
				<? endforeach ?>
			</select>
			<select name="sortDir">
				<option value="1" <?if($sortDir==1):?>selected<?endif?>>Desc</option>
				<option value="-1" <?if($sortDir==-1):?>selected<?endif?>>Asc</option>
			</select>
		</div>
		
		<div class="row actions">
			<input type="submit" value="Show">
		</div>
		
		<div class="row actions">
			<a href=".">Reset Filter</a>
		</div>
	</form>
</div>

<div class="l-content">
	<? if ($page > 1): ?>
		<a href="?<?=$galleriesParams?>&amp;<?=$limitsParams?>&amp;title=<?=$title?>&amp;page=<?=$page-1?>" class="m-button showPrev">Show Prev</a>
	<? endif ?>

	<div class="authors-list">
		<div id="page_<?=$page?>" class="page" data-num="<?=$page?>">
			<? foreach ($authors as $i => $author): ?>
				<? require 'index.item.tpl.php'; ?>
			<? endforeach ?>
		</div>
	</div>

	<? if ($page < $pages): ?>
		<a href="?<?=$galleriesParams?>&amp;<?=$limitsParams?>&amp;title=<?=$title?>&amp;page=<?=$page+1?>" class="m-button showMore">Show More</a>
	<? endif ?>
</div>

<datalist id="searchList">
	<? foreach (array_slice($keywords, 0, 200, true) as $keyword => $count): ?>
		<option value="<?=$keyword?>">
	<? endforeach ?>
	<? foreach ($categories as $category => $count): ?>
		<option value="cat:'<?=$category?>'">
	<? endforeach ?>
	<? foreach ($profiles as $profile): ?>
		<option value="by:<?=$profile['username']?>">
	<? endforeach ?>
</datalist>

<? if (IS_ADMIN): ?>
<div id="checkMenu" class="b-dropmenu">
	<ul>
		<li>
			<a href="#" data-check="all">All</a>
			<a href="#" data-check="invert">Invert</a>
			<a href="#" data-check="none">None</a>
		</li>
	</ul>
</div>

<div id="moveMenu" class="b-dropmenu">
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
<? endif ?>

</body>
</html>