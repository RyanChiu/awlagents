<center>
<b><font color="red"><?php $session->flash('auth'); ?></font></b>
<font color="red"><?php $session->flash(); ?></font>
</center>
<?php
echo $form->create(null, array('controller' => 'trans', 'action' => 'login'));
?>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
	<td rowspan="10" width="195px">
	<?php
	//echo $html->image('loginLeft.png', array('width' => '180px'));
	?>
	</td>
	<td colspan="2" align="center">
	<b><font size="4" color="darkred">Welcome</font></b>
	<br/><br/>
	</td>
	<td rowspan="10" align="right" width="185px">
	<?php
	//echo $html->image('loginRight.png', array('width' => '120px'));
	?>
	</td>
</tr>
<tr>
	<td align="right"><b><font color="black" size="2">Username :</font></b></td>
	<td align="left">
	<?php
	echo $form->input('TransAccount.username', array('label' => '', 'style' => 'width:112px;'));
	?>
	<script type="text/javascript" language="javascript">
	jQuery("#TransAccountUsername").focus();
	</script> 
	</td>
</tr>
<tr><td>&nbsp;</td><td></td></tr>
<tr>
	<td align="right"><b><font color="black" size="2">Password :</font></b></td>
	<td align="left">
	<?php
	echo $form->input('TransAccount.password', array('label' => '', 'style' => 'width:112px;', 'type' => 'password'));
	?>
	</td>
</tr>
<tr><td>&nbsp;</td><td></td></tr>
<tr>
	<td align="right"><b><font color="black" size="2">Code verification :</font></b></td>
	<td align="left">
	<div style="float:left;margin-right:10px;">
	<?php
	echo $form->input('TransAccount.vcode', array('label' => '', 'style' => 'width:112px;', 'div' => array('style' => 'margin-bottom:5px;')));
	?>
	</div>
	<div style="float:left;">
	<script type="text/javascript" language="javascript">
	function __chgVcodes() {
		document.getElementById("imgVcodes").src =
			"<?php echo $html->url(array('controller' => 'trans', 'action' => 'captcha')); ?>"
			+ "?" + Math.random();
	}
	</script>
	<?php
	echo $html->link(
		$html->image(array('controller' => 'trans', 'action' => 'captcha'),
			array('width' => '60', 'height' => '20', 'id' => 'imgVcodes', 'onclick' => 'javascript:__chgVcodes();')
		),
		'#', 
		array('title' => 'Click to try another one.(By entering this code you help yourself prevent spam and fake login.)'),
		false, false
	);
	?>
	</div>
	</td>
</tr>
<tr><td>&nbsp;</td><td></td></tr>
<tr>
<td></td>
<td align="left">
<?php
echo $form->submit('login_btn.png', array('style' => 'border:0px;width:95px;height:28px;'));
?>
</td>
</tr>
<tr>
<td align="center" colspan="2">
<br/>
<?php
echo $html->link(
		'<b><font color="#0000a0" size="2">Lost password?</font></b>',
		array('controller' => 'trans', 'action' => 'forgotpwd'),
		null, false, false
	);
?>
<br/>
<br/>
<?php
echo $html->link(
		'<b><font color="#0000a0" size="2">Help?</font></b>',
		array('controller' => 'trans', 'action' => 'contactus'),
		null, false, false
	);
?>
</td>
</tr>
<tr><td>&nbsp;</td><td></td></tr>
</table>
<?php
echo $form->end();
?>

<div style="margin:20px 15px 10px 15px">
<?php
echo $this->element('frauddefblock');
?>
</div>

<script type="text/javascript" language="javascript">
for (var i = 0; i < 10; i++) {
	jQuery(".suspended-warning").animate({opacity: 'toggle'}, "slow");
}
</script>
