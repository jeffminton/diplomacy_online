<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN'
'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>

<html>
	<head>
		<title><?php echo $this->eprint($this->vals["title"]);?></title>
		<link rel="stylesheet" type="text/css" href="html/style.css"/>
		
		<!-- insert javascript for game map only if needed 
		to reduce code sent over pipe-->
		<?php if(isset($this->vals["loadJava"])): ?>
			<script type="text/javascript">
				<?php echo $this->eprint($this->vals['maplist']); ?>
			</script>
			<script type="text/javascript">
				<?php echo $this->eprint($this->vals['countries']); ?>
			</script>
			<!--<script type="text/javascript" src="webtoolkit.contextmenu.js"></script>-->
			<script type="text/javascript" src="map/raphael.js" ></script>
			<script type="text/javascript" src="map/paths.js"></script>
			<script type="text/javascript" src="script.js"></script>
		<?php endif; ?>
		
	</head>
	<body>
		<div class='header'>
			<?php include $this->template('header.tpl.php') ?>
		</div>
		<div class='content'>
		
			<!--display error if needed-->
			<?php if(isset($this->vals['error'])): ?>
				<div class="error">
					<?php echo $this->eprint($this->vals['error']);?>
				</div>
			<?php endif; ?>
			
			<!--display desired conetnt page-->
			<?php include $this->template($this->vals["page"]) ?>
		</div>
		<div class='footer'>
			<?php include $this->template('footer.tpl.php') ?>
		</div>
	</body>
</html>
