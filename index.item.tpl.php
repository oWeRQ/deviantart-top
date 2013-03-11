<div class="b-author">
	<div class="actions">
		<a href="#" class="m-button m-down actionAddGallery">Add Collection</a>
		<a href="#" class="m-button m-down actionRemoveGallery">Remove Collection</a>
		<a href="#" class="m-button actionDeleteFavourite">Delete Favourites</a>
	</div>
	<h3>
		<span class="number"><?=$i+$topOffset+1?></span>
		<a target="_blank" href="?<?=$limitsParams?>&amp;username=<?=$author['username']?>"><?=$author['username']?></a>
		<small><?=$author['percent']?>%</small>
		<sup><a target="_blank" href="http://<?=$author['username']?>.deviantart.com/gallery/">DA</a></sup>
	</h3>
	<ul class="b-inline b-images" data-username="<?=$author['username']?>" data-images-total="<?=$author['total']?>" data-images-loaded="<?=count($author['images'])?>">
		<? foreach ($author['images'] as $image): ?>
			<li>
				<a id="image_<?=$image['id']?>" class="showInGallery" href="images/<?=$image['filename']?>" target="_blank" title="<?=$image['title']?>" data-galleries="<?=join(', ', $image['galleries'])?>" data-id="<?=$image['id']?>">
					<img src="images/mythumbs/<?=$image['filename']?>">
				</a>
				<a class="update" href="#"></a>
			</li>
		<? endforeach ?>
	</ul>
	<? if(count($author['images']) < $author['total']): ?>
		<a href="#" class="moreImages">More images (<span class="count"><?=$author['total']-count($author['images'])?></span>)</a>
	<? endif ?>
</div>
