<div class="gameList">
	Please select the id of the game you wish to submit orders for
	<br />
	Games without a clickable id do not have enough people to play yet
	<br />
	<?php foreach ($this->vals['games'] as $gameArr): ?>
		Game ID: 
		<?php if($gameArr['running'] == 1):?>
			<a href="index.php?
				<?php echo $this->eprint($this->vals['link']); ?>=1&gid=
				<?php echo $this->eprint($gameArr['gid']); ?>">
				<?php echo $this->eprint($gameArr['gid']); ?></a>
		<?php else: ?>
			<?php echo $this->eprint($gameArr['gid']); ?>
		<?php endif; ?>
		<br />
		Year: <?php echo $this->eprint($gameArr['year']); ?>
		<br />
		Season: <?php echo $this->eprint($gameArr['season']); ?>
		<br /><br />
	<?php endforeach;?>
</div>