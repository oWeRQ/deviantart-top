<div class="b-author">
	<h3>
		<span class="number"><?=$i+$topOffset+1?></span>
		<a target="_blank" href="?<?=$limitsParams?>&amp;username=<?=$author['username']?>"><?=$author['username']?></a>
		<small><?=$author['percent']?>%</small>
		<sup><a target="_blank" href="http://<?=$author['username']?>.deviantart.com/gallery/">DA</a></sup>
	</h3>
	<div class="b-inline b-images" data-username="<?=$author['username']?>" data-images-total="<?=$author['total']?>" data-images-loaded="<?=count($author['images'])?>">
		<? foreach ($author['images'] as $image): ?>
			<a href="images/<?=$image['filename']?>" target="_blank" title="<?=$image['title']?>" data-galleries="<?=join(', ', $image['galleries'])?>" data-id="<?=$image['id']?>">
				<img src="images/mythumbs/<?=$image['filename']?>">
			</a>
		<? endforeach ?>
	</div>
	<? if(count($author['images']) < $author['total']): ?>
		<a href="#" class="moreImages">More images (<span class="count"><?=$author['total']-count($author['images'])?></span>)</a>
	<? endif ?>
</div>
