<h1>A New Office</h1>
<?php
echo $form->create(null, array('controller' => 'trans', 'action' => 'regcompany', 'id' => 'frmReg'));
?>
<table border="0" width="100%">
	<caption>Fields marked with an asterisk (*) are required.</caption>
	<tr>
		<td width="222">Office Name : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransCompany.officename', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
		<!--  
		<td rowspan="15" align="center"><?php //echo $html->image('iconGiveDollars.png', array('width' => '160')); ?></td>
		-->
	</tr>
	<tr>
		<td>Manager's First Name : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransCompany.man1stname', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Manager's Last Name : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransCompany.manlastname', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Manager's Email : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransCompany.manemail', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Manager's Cell NO. : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransCompany.mancellphone', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Username for this Office : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransAccount.username', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Password : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransAccount.password', array('label' => '', 'style' => 'width:390px;', 'type' => 'password'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Confirm password : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransAccount.originalpwd', array('label' => '', 'style' => 'width:390px;', 'type' => 'password'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Street Name &amp; Number : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransCompany.street', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		</td>
	</tr>
	<tr>
		<td>City : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransCompany.city', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		</td>
	</tr>
	<tr>
		<td>State &amp; Zip : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransCompany.state', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		</td>
	</tr>
	<tr>
		<td>Country : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->select('TransCompany.country', $cts, null, array('style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Agent Notes : </td>
		<td>
		<?php
		echo $form->input('TransCompany.agentnotes', array('label' => '', 'rows' => '9', 'cols' => '60'));
		?>
		</td>
	</tr>
	<tr>
		<td>Associated Sites: </td>
		<td>
		<?php
		$selsites = array_diff($sites, $exsites);
		$selsites = array_keys($selsites);
		echo $form->select('SiteExcluding.siteid',
			$sites,
			$selsites,
			array(
				'multiple' => 'checkbox',
			)
		);
		?>
		</td>
	</tr>
	<tr>
		<td>
		<?php
		echo 'Activated' . $form->checkbox('TransAccount.status', array('checked' => 'checked', 'style' => 'border:0px;width:16px;'));
		?>
		</td>
		<td>
		<?php
		echo $form->submit('Add & New',
			array(
				'default' => 'default',
				'div' => array('style' => 'float:left;margin-right:15px;'),
				'style' => 'width:112px;',
				'onclick' => 'javascript:__changeAction("frmReg", "'
					. $html->url(array('controller' => 'trans', 'action' => 'regcompany', 'id' => -1))
					. '");' 
			)
		);
		echo $form->submit('Add',
			array(
				'div' => array('style' => 'float:left;margin-right:15px;'),
				'style' => 'width:112px;',
				'onclick' => 'javascript:__changeAction("frmReg", "'
					. $html->url(array('controller' => 'trans', 'action' => 'regcompany'))
					. '");'
			)
		);
		?>
		</td>
	</tr>
</table>
<script type="text/javascript" language="javascript">
jQuery(":checkbox").attr({style: "border:0px;width:16px;vertical-align:middle;"});
</script>
<?php
echo $form->input('TransAccount.role', array('type' => 'hidden', 'value' => '1'));//the value 1 as being an office
echo $form->input('TransAccount.regtime', array('type' => 'hidden', 'value' => ''));//should be set to current time when saving into the DB
echo $form->input('TransAccount.online', array('type' => 'hidden', 'value' => '0'));// the value 0 means "offline"
echo $form->input('TransCompany.id', array('type' => 'hidden'));
echo $form->end();
?>