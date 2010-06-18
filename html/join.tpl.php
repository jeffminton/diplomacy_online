<div class="gameList">
	Please select the id of a game you want to join
	<br />
	<?php foreach ($this->vals['games'] as $gameArr): ?>
		Game ID: <a href="index.php?join=1&gid=<?php echo $this->eprint($gameArr['gid']); ?>">
			<?php echo $this->eprint($gameArr['gid']); ?></a>
		<br />
		Year: <?php echo $this->eprint($gameArr['year']); ?>
		<br />
		Season: <?php echo $this->eprint($gameArr['season']); ?>
		<br />
		Players: <?php echo $this->eprint($gameArr['players']); ?>
		<br /><br />
	<?php endforeach;?>
</div>