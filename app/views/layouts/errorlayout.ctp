<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
<html>
<head>
	<title>Error Page</title>
	<?php
	
	/*for default whole page layout*/
	echo $html->css('main');
		
	?>
</head>
<body bgcolor="white">
<div style="margin:auto;vertical-align:middle;line-height:200px;">
<?php echo $content_for_layout; ?>
</div>
</body>
</html>