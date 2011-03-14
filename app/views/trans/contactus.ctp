<?php
$userinfo = $session->read('Auth.TransAccount');
//echo print_r($userinfo, true);
//echo '<br/>';
?>
<h1>Get Help</h1>
<?php
echo $form->create(null, array('controller' => 'trans', 'action' => 'contactus'));
?>
<table width="100%" <?php echo empty($userinfo) ? 'cellspacing="5" style="border: 1px solid #dddddd;"' : ''; ?>>
<caption>Fields marked with an asterisk (*) are required.</caption>
	<tr>
		<td>Your Email Address:</td>
		<td align="left">
		<div style="float:left">
		<?php
		echo $form->input('FakeContactUs.email', array('label' => '', 'style' => 'width:600px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Subject:</td>
		<td align="left">
		<div style="float:left">
		<?php
		echo $form->input('FakeContactUs.subject', array('label' => '', 'style' => 'width:600px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Message:</td>
		<td align="left">
		<div style="float:left">
		<?php
		echo $form->input('FakeContactUs.message', array('label' => '', 'style' => 'width:600px;', 'rows' => 23));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
		<?php
		echo $form->submit('Send your message for us now', array('style' => 'width:300px;'));
		if (empty($userinfo)) {
			echo '<div style="margin-top:6px;">'
				. $html->link(
					'Let me log in now.',
					array('controller' => 'trans', 'action' => 'login'),
					null, false, false
				)
				. '</div>';
		}
		?>
		</td>
	</tr>
</table>
<?php
echo $form->end();
?>
