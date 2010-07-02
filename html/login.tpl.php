<div class="login">
	<form method="post" action="index.php?log=1" id="login">
		user name: <input type="text" name="uid" id="uid" value=""/>
		<br />
		password: <input type="password" name="pwd" id="pwd" value=""/>
		<br />
		<input type="submit" name="submit" id="submit" value="Submit"/>
	</form>
	<script type="text/javascript">
		document.getElementById("uid").focus();
	</script>
	<br />
	<a href="index.php?reg=1">Register</a>
</div>
