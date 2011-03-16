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
    	<div class="typeface-js" style="float:right;height:120px;margin-top:10px;color:#ffffff;font-family:Tahoma, Geneva, Matisse Itc;">
			<div class="cci-textlogo" style="font-size:72px;">A</div>
			<div class="cci-textlogo" style="font-size:48px;">MERICAN</div>
			<div class="cci-textlogo" style="font-size:72px;">&nbsp;</div>
			<div class="cci-textlogo" style="font-size:72px;">W</div>
			<div class="cci-textlogo" style="font-size:48px;">e</div>
			<div class="cci-textlogo" style="font-size:48px;">b</div>
			<div class="cci-textlogo" style="font-size:72px;">&nbsp;</div>
			<div class="cci-textlogo" style="font-size:72px;">L</div>
			<div class="cci-textlogo" style="font-size:48px;">i</div>
			<div class="cci-textlogo" style="font-size:36px;">nk</div>
			<div class="cci-textlogo" style="font-size:72px;">!!!</div>
			<div class="cci-textlogo" style="font-size:72px;">&nbsp;</div>
		</div>
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