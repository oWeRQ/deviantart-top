<? if ($page > 1): ?>
	<a href="?<?=$galleriesParams?>&amp;<?=$limitsParams?>&amp;title=<?=rawurlencode($title)?>&amp;page=<?=$page-1?>" class="m-button i-showPrev">Show Prev</a>
<? endif ?>

<div class="b-pages">
	<div id="page_<?=$page?>" class="b-pages-item" data-num="<?=$page?>">
		<? foreach ($authors as $i => $author): ?>
			<? $this->renderPartial('_item', array(
				'i' => $i,
				'author' => $author,
				'imagesOffset' => $imagesOffset,
				'topOffset' => $topOffset,
				'galleriesParams' => $galleriesParams,
				'userLimitsParams' => $userLimitsParams,
			)); ?>
		<? endforeach ?>
	</div>
</div>

<? if ($page < $pages): ?>
	<a href="?<?=$galleriesParams?>&amp;<?=$limitsParams?>&amp;title=<?=rawurlencode($title)?>&amp;page=<?=$page+1?>" class="m-button i-showMore">Show More</a>
<? endif ?>

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

<div id="updateControl" class="b-updateControl">
	<a href="#" class="closeControl closeButton"></a>
	<form action="." method="post">
		<input type="hidden" name="action" value="setGalleries">
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

<script src="js/index.js"></script>
