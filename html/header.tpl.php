<div class='header'>
	Diplomacy Online
</div>
<?php if(isset($this->vals['uid'])): ?>
<div class="userinfo">
	<?php $this->eprint($this->vals['uid']); ?>, <a href="index.php?unlog=1">Logout</a>
</div>
<?php endif; ?>
