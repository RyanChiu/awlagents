<h1>Profile</h1>
<br/>
<?php
//echo print_r($rs, true);
$userinfo = $session->read('Auth.TransAccount');
echo $form->create(null, array('controller' => 'trans', 'action' => 'updadmin'));
?>
<table width="100%">
	<caption>Fields marked with an asterisk (*) are required.</caption>
	<tr>
		<td>Password : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransAccount.password', array('label' => '', 'type' => 'password', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Confirm password :</td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransAccount.originalpwd', array('label' => '', 'type' => 'password', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Email Address :</td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransAdmin.email', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td></td>
		<td><?php echo $form->submit('Update', array('style' => 'width:112px;')); ?></td>
	</tr>
</table>
<?php
echo $form->input('TransAccount.id', array('type' => 'hidden'));
echo $form->input('TransAdmin.id', array('type' => 'hidden'));
echo $form->end();
?>
