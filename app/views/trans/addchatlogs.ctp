<?php
$userinfo = $session->read('Auth.TransAccount');
//echo print_r($userinfo, true);
//echo '<br/>';
?>
<h1>Submit Chat Logs</h1>
<?php
echo $form->create(null, array('controller' => 'trans', 'action' => 'addchatlogs'));
?>
<table width="100%">
<caption>Fields marked with an asterisk (*) are required.<br/><font color="red"><b>(Please  include  full chats only.)</b></font></caption>
	<tr>
		<td>Client Name:</td>
		<td align="left">
		<div style="float:left">
		<?php
		echo $form->input('ChatLog.clientusername', array('label' => '', 'style' => 'width:260px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Site:</td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('ChatLog.siteid', array('label' => '', 'style' => 'width:260px;', 'type' => 'select', 'options' => $sites));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Conversation:</td>
		<td align="left" colspan="3">
		<div style="float:left">
		<?php
		echo $form->input('ChatLog.conversation', array('label' => '', 'style' => 'width:700px;', 'rows' => 23));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>
		<?php
		echo $form->input('ChatLog.agentid', array('label' => '', 'type' => 'hidden', 'value' => $userinfo['id']));
		?>
		</td>
		<td colspan="3"><?php echo $form->submit('Submit', array('style' => 'width:120px;'));?></td>
	</tr>
</table>
<?php
echo $form->end();
?>