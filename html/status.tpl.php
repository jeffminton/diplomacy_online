<div class="map" id="map">
	<?php include $this->template('map.tpl.php') ?>
</div>
<div class="orders">
	<h2>Previous Orders</h2>
	<?php foreach($this->vals['orders'] as $orderArr):?>
		Player: <?php echo $this->eprint($orderArr['uid']); ?>
		<br /><br />
		Orders:
		<br />
		<?php echo $this->eprint($orderArr['orders']); ?>
		<br /><br />
		Result:
		<br />
		<?php echo $this->eprint($orderArr['result']); ?>
		<br /><br /><br />
	<?php endforeach; ?>
</div>
<!--
<div class="state">
	<h2>Current Map State</h2>
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
