<h1>Add Site</h1>
<br/>
<?php
//echo print_r($results, true);
$userinfo = $session->read('Auth.TransAccount');
echo $form->create(null, array('controller' => 'links', 'action' => 'addsite'));
?>
<table width="100%">
	<caption>Fields marked with an asterisk (*) are required.</caption>
	<tr>
		<td>Campaign Name:<font size="1">(for admin only)</font></td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransSite.hostname', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Site Name:<font size="1">(for company or agent)</font></td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransSite.sitename', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<?php
	if ($userinfo['id'] == 2) {
	?>
	<tr>
		<td>Abbreviation:<font size="1">(for admin only)</font></td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransSite.abbr', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<?php
	}
	?>
	<tr>
		<td>Site URL:</td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransSite.url', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Site Contact Name:</td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransSite.contactname', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Email Address:</td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransSite.email', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Phone Numbers:</td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransSite.phonenums', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Type Of Site:</td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransSite.type',
			array('type' => 'select', 'options' => $types,
				'label' => '', 'style' => 'width:390px;'
			)
		);
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Payouts Per Type Of Sale:</td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransSite.payouts', array('label' => '', 'style' => 'width:390px;', 'value' => '0.00'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Notes:<br/><font size="1">(FOR TERMS, OF CONTRACT, DO'S AND DONTS, CONFIDENTIAL INFO)</font></td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransSite.notes', array('label' => '', 'rows' => '20', 'cols' => '70'));
		?>
		</div>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
		<?php
		echo $form->submit('Add', array('style' => 'width:112px;'));
		?>
		</td>
	</tr>
</table>
<?php
echo $form->end();
?>
