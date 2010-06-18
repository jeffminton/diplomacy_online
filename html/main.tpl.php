<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN'
'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>

<html>
	<head>
		<title><?php echo $this->eprint($this->vals["title"]);?></title>
		<link rel="stylesheet" type="text/css" href="html/style.css"/>
		<!--
		<script type="text/javascript" src="mapper/cvi_map_lib.js"></script>
		-->
		<?php if(isset($this->vals["loadJava"])): ?>
			<script type="text/javascript">
				<?php echo $this->eprint($this->vals['maplist']); ?>
			</script>
			<script type="text/javascript">
				<?php echo $this->eprint($this->vals['countries']); ?>
			</script>
			<script type="text/javascript" src="map/raphael.js" ></script>
			<script type="text/javascript" src="map/paths.js"></script>
			<script type="text/javascript" src="script.js"></script>
		<?php endif; ?>
		<?php if(isset($this->vals["script"])): ?>
			<?php for($i = 0; $i < count($this->vals["script"]); $i++): ?>
				<script type="text/javascript" src="
					<?php echo $this->eprint($this->vals['script'][$i])?>
				"></script> 
			<?php endfor; ?>
		<? endif; ?>
	</head>
	<body>
		<div class='header'>
			<?php include $this->template('header.tpl.php') ?>
		</div>
		<div class='content'>
			<?php include $this->template($this->vals["page"]) ?>
		</div>
		<div class='footer'>
			<?php include $this->template('footer.tpl.php') ?>
		</div>
	</body>
</html>
