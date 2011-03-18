<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
<html>
<head>
	<title>
		
	<?php echo $title_for_layout; ?>
		
	</title>
	<?php
	
	/*for default whole page layout*/
	echo $html->css('main');
	
	/*for jQuery flash*/
	echo $javascript->link('jQuery/Datepicker/jquery-1.3.2.min');
	
	/*for typeface*/
	echo $javascript->link('typeface/typeface-0.15');
	echo $javascript->link('typeface/tahoma_regular.typeface');
	
	echo $scripts_for_layout;
		
	?>
</head>
<body bgcolor="#ffffff">
<div class="wrapper">
  <!-- Start Border-->
  <div id="border">
    <!-- Start Header -->
    <div class="header">
    	<br/><br/>
		<p><span class="Apple-style-span" style="border-collapse: collapse; font-family: Arial; font-size: large; color: rgb(0, 0, 0);"><strong><font size="7"><font><font face="Arial Black"><font color="#80d5c4"><font face="Wide Latin">A</font>merican&nbsp;<font face="Wide Latin">W</font>eb<font face="Wide Latin">&nbsp; L</font>ink</font></font></font></font></strong></span></p>
    </div>
    <!-- End Header -->
   <!-- Start Right Column -->
    <div id="rightcolumn">
      <!-- Start Main Content -->
      <div class="maincontent">
        <center>
        	<b><font color="red"><?php $session->flash(); ?></font></b>
        </center>
        <div class="content-top"></div>
        <div class="content-mid">

		  <?php echo $content_for_layout; ?>

				</div>
        <div class="content-bottom"></div>
      </div>
      <!-- End Main Content -->
    </div>
    <!-- End Right Column -->
  </div>
  <!-- End Border -->
  <!-- Start Footer -->
  <div id="footer">
  	<font size="2" color="white">Copyright &copy; 2010 American Web Link All Rights Reserved.&nbsp;&nbsp;</font>
  </div>
  <!-- End Footer -->
</div>
</body>
</html>