<div class="b-images" data-images-total="<?=$author['favourites']?>" data-images-loaded="<?=$imagesOffset+count($author['images'])?>">
	<? if (IS_ADMIN): ?>
	<div class="b-images-actions">
		<span class="m-button m-down i-checkAll"><input class="i-checkAll-checkbox" type="checkbox"></span>
		<span class="m-button m-down i-addGallery" data-action="addGallery">Add Collection</span>
		<span class="m-button m-down i-removeGallery" data-action="removeGallery">Remove Collection</span>
		<span class="m-button i-deleteFavourite">Delete Favourites</span>
	</div>
	<? endif ?>
	<h3>
		<span class="b-images-number"><?=$i+$topOffset+1?></span>
		<a target="_blank" href="?title=by:<?=$author['username']?>&amp;<?=$galleriesParams?>&amp;<?=$userLimitsParams?>"><?=$author['username']?></a>
		<small>
			<?=$author['favourites']?>
			/ <?=$author['deviations']?>
			= <?=round($author['percent'], 1)?>%
			| Score: <?=round($author['score'], 1)?>
			| Wilson Score: <?=round($author['wilson_score']*100, 1)?>
		</small>
		<sup><a target="_blank" href="http://<?=$author['username']?>.deviantart.com/gallery/">DA</a></sup>
	</h3>
	<ul class="b-inline b-images-list">
		<? foreach ($author['images'] as $image): ?>
			<li>
				<a id="image_<?=$image['id']?>" class="m-showInGallery" href="images/original/<?=$image['filename']?>" data-big="<?=$image['page']?>" target="_blank" title="<?=$image['title']?>" data-galleries="<?=join(', ', $image['galleries'])?>" data-id="<?=$image['id']?>">
					<img src="images/mythumbs/<?=$image['filename']?>">
				</a>
				<a class="m-similar" href="similar.php?id=<?=$image['id']?>" target="_blank"></a>
				<? if (IS_ADMIN): ?>
					<a class="m-update" href="#"></a>
				<? endif ?>
			</li>
		<? endforeach ?>
	</ul>
	<? if(count($author['images']) < $author['favourites']): ?>
		<a href="#" class="m-moreImages">More Images (<span class="count"><?=$author['favourites']-$imagesOffset-count($author['images'])?></span>)</a>
	<? endif ?>
</div>
