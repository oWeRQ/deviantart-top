<form id="filterForm" class="b-form b-form_filter">
	<div class="row">
		<input type="text" name="title" value="<?=$title?>" placeholder="Search" list="searchList"><a href="#" class="clearInput"></a>
	</div>

	<div class="b-form-legend m-open">Collections</div>
	<div class="b-form-fieldset">
		<div class="checkboxes">
			<div class="controls">
				<a href="#" class="checkAll">Check All</a>
				| <a href="#" class="uncheckAll">Uncheck All</a>
			</div>
			<? foreach ($galleries as $gallery): ?>
				<? if (!is_array($gallery)) continue; ?>
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
	</div>

	<div class="b-form-legend m-open">Limits</div>
	<div class="b-form-fieldset">
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
	</div>

	<div class="b-form-legend m-open">Sort</div>
	<div class="b-form-fieldset">
		<div class="row b-inline">
			<label>By:</label>
			<select name="sort">
				<? foreach (array(
					'score'=>'Score',
					'wilson_score'=>'Wilson Score',
					'percent'=>'Percent',
					'deviations'=>'Total',
					'favourites'=>'Favourites',
					'random'=>'Random',
				) as $value => $name): ?>
					<option value="<?=$value?>" <?if($value==$sort):?>selected<?endif?>><?=$name?></option>
				<? endforeach ?>
			</select>
		</div>
		<div class="row b-inline">
			<label>Total:</label>
			<select name="sortTotal">
				<? foreach (array(
					'deviations'=>'Deviations',
					'favourites'=>'Favourites',
				) as $value => $name): ?>
					<option value="<?=$value?>" <?if($value==$sortTotal):?>selected<?endif?>><?=$name?></option>
				<? endforeach ?>
			</select>
		</div>
		<div class="row b-inline">
			<label>Dir:</label>
			<select name="sortDir">
				<option value="1" <?if($sortDir==1):?>selected<?endif?>>Desc</option>
				<option value="-1" <?if($sortDir==-1):?>selected<?endif?>>Asc</option>
			</select>
		</div>
	</div>
	
	<div class="row actions">
		<input type="submit" value="Show">
	</div>
	
	<div class="row actions">
		<span class="pull-right">Found: <?=$count?></span>
		<a href=".">Reset Filter</a>
	</div>
</form>

<datalist id="searchList">
	<? foreach ($keywords as $keyword => $count): ?>
		<option value="<?=$keyword?>">
	<? endforeach ?>
	<? foreach ($categories as $category => $count): ?>
		<option value="cat:'<?=$category?>'">
	<? endforeach ?>
	<? foreach ($profiles as $profile): ?>
		<option value="by:<?=$profile['username']?>">
	<? endforeach ?>
</datalist>