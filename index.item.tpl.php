<div class="b-author" data-username="<?=$author['username']?>" data-images-total="<?=$author['favourites']?>" data-images-loaded="<?=$imagesOffset+count($author['images'])?>">
	<? if (IS_ADMIN): ?>
	<div class="actions">
		<a href="#" class="m-button m-down actionCheckAll"><input type="checkbox"></a>
		<a href="#" class="m-button m-down actionAddGallery">Add Collection</a>
		<a href="#" class="m-button m-down actionRemoveGallery">Remove Collection</a>
		<a href="#" class="m-button actionDeleteFavourite">Delete Favourites</a>
	</div>
	<? endif ?>
	<h3>
		<span class="number"><?=$i+$topOffset+1?></span>
		<a target="_blank" href="?<?=$userLimitsParams?>&amp;username=<?=$author['username']?>"><?=$author['username']?></a>
		<small>
			<?=$author['favourites']?>
			/ <?=$author['deviations']?>
			= <?=round($author['percent'], 1)?>%
			| score: <?=round($author['score'], 1)?>
			| wilson score: <?=round($author['wilson_score']*100, 1)?>
		</small>
		<sup><a target="_blank" href="http://<?=$author['username']?>.deviantart.com/gallery/">DA</a></sup>
	</h3>
	<ul class="b-inline b-images">
		<? foreach ($author['images'] as $image): ?>
			<li>
				<a id="image_<?=$image['id']?>" class="showInGallery" href="images/original/<?=$image['filename']?>" data-big="<?=$image['page']?>" target="_blank" title="<?=$image['title']?>" data-galleries="<?=join(', ', $image['galleries'])?>" data-id="<?=$image['id']?>">
					<img src="images/mythumbs/<?=$image['filename']?>">
				</a>
				<a class="similar" href="similar.php?id=<?=$image['id']?>" target="_blank"></a>
				<? if (IS_ADMIN): ?>
					<a class="update" href="#"></a>
				<? endif ?>
			</li>
		<? endforeach ?>
	</ul>
	<? if(count($author['images']) < $author['favourites']): ?>
		<a href="#" class="moreImages">More images (<span class="count"><?=$author['favourites']-$imagesOffset-count($author['images'])?></span>)</a>
	<? endif ?>
</div>
