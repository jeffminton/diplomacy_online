<div class="orders">
	<form method="post" action="index.php?ord=1&ent=1">
		<textarea cols="20" rows="5" name="orders" id="orders"></textarea>
		<br />
		<input type="submit" name="submit" value="Submit" id="submit"/>
		<input type="hidden" name="gid" id="gid" value="<?php echo $this->eprint($this->vals['gid']);?>"/>
	</form>
</div>
<div class="map" id="map">
	<?php include $this->template('map.tpl.php') ?>
</div>

<!--
<div class="state">
	<?php foreach($this->vals['countries'] as $key => $value): ?>
		<?php $this->eprint($key . ": " . $value) ?>
		<br />
		<?php foreach($this->vals['maplist'][$key] as $mapArr): ?>
			<?php $this->eprint("    " . $mapArr['aid'] . ": " . $mapArr['type']); ?>
			<br />
		<?php endforeach; ?>
		<br />
	<?php endforeach; ?>
</div>
-->
