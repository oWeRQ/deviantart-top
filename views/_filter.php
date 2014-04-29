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