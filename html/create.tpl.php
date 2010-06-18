<div>
	Please choose the country you would like to play as
	<br />
	<form method="post" action="index.php?create=1&co=1">
		<select name="country" id="country">
			<?php foreach ($this->vals['gp'] as $gp):?>
				<option value="<?php $this->eprint($gp) ?>"><?php $this->eprint($gp) ?></option>
			<?php endforeach;?>
		</select>
		<input type="submit" name="submit" id="submit" value="Submit" />
	</form>
</div>