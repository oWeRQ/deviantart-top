<div class="b-author" data-username="<?=$author['username']?>">
	<? if (IS_ADMIN): ?>
	<div class="actions">
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
	<ul class="b-inline b-images" data-images-total="<?=$author['favourites']?>" data-images-loaded="<?=$imagesOffset+count($author['images'])?>">
		<? foreach ($author['images'] as $image): ?>
			<li>
				<a id="image_<?=$image['id']?>" class="showInGallery" href="images/<?=$image['filename']?>" data-big="<?=$image['page']?>" target="_blank" title="<?=$image['title']?>" data-galleries="<?=join(', ', $image['galleries'])?>" data-id="<?=$image['id']?>">
					<img src="images/mythumbs/<?=$image['filename']?>">
				</a>
				<? if (IS_ADMIN): ?>
					<a class="update" href="#"></a>
				<? endif ?>
			</li>
		<? endforeach ?>
	</ul>
	<? if(count($author['images']) < $author['favourites']): ?>
		<a href="#" class="moreImages">More images (<span class="count"><?=$author['favourites']-count($author['images'])?></span>)</a>
	<? endif ?>
</div>
