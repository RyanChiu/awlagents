<?php
//echo '<br/>';
//echo print_r($data, true);
$userinfo = $session->read('Auth.TransAccount');
$action = 'regagent';
$title = 'A New Agent';
if ($userinfo['role'] == 1) {
	$action = 'requestchg';
	$title = 'Request For New Agent';
}
?>
<h1><?php echo $title; ?></h1>
<?php
echo $form->create(null, array('controller' => 'trans', 'action' => $action, 'id' => 'frmReg'));
if ($userinfo['role'] == 1) {
	echo $form->input('Requestchg.role', array('type' => 'hidden', 'value' => '2'));
	echo $form->input('Requestchg.type', array('type' => 'hidden', 'value' => 'reg'));
	echo $form->input('Requestchg.from', array('type' => 'hidden', 'value' => $curcom['TransCompany']['manemail']));
	echo $form->input('Requestchg.offiname', array('type' => 'hidden', 'value' => $curcom['TransCompany']['officename']));
}
?>
<table border="0" width="100%">
	<caption>Fields marked with an asterisk (*) are required.</caption>
	<tr>
		<td width="248">Office : </td>
		<td>
		<div style="float:left">
		<?php
		if ($userinfo['role'] == 0) {// means an administrator
			echo $form->input('TransAgent.companyid',
				array('type' => 'select', 'options' => $cps,
					'label' => '', 'style' => 'width:390px;'
				)
			);
		} else if ($userinfo['role'] == 1) {// means an office
			echo $form->input('TransAgent.companyshadow',
				array(
					'label' => '',
					'style' => 'width:390px;border:0px;background:transparent;',
					'readonly' => 'readonly',
					'value' => $cps[$userinfo['id']]
				)
			);
			echo $form->input('TransAgent.companyid', array('type' => 'hidden', 'value' => $userinfo['id']));
		}
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
		<!--  
		<td rowspan="16" align="center"><?php //echo $html->image('iconDollarsKey.png', array('width' => '160')); ?></td>
		-->
	</tr>
	<tr>
		<td>First Name : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransAgent.ag1stname', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Last Name : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransAgent.aglastname', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Email : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransAgent.email', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>
		<div style="float:left">Username : </div>
		<div style="float:left">
		<?php
		echo '('
			. $form->checkbox(
				'TransAccount.auto',
				array(
					'checked' => 'checked',
					'style' => 'border:0px;width:16px;'
				)
			)
			. 'Auto-generated)';
		?>
		</div>
		</td>
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
		<td>Confirm Password : </td>
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
		echo $form->input('TransAgent.street', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		</td>
	</tr>
	<tr>
		<td>City : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransAgent.city', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		</td>
	</tr>
	<tr>
		<td>State &amp; Zip : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransAgent.state', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		</td>
	</tr>
	<tr>
		<td>Country : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->select('TransAgent.country', $cts, null, array('style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Instant Messenger Contact : <br/><small>(AIM, Yahoo, Skype, MSN, ICQ</small></td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransAgent.im', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Cell NO. : </td>
		<td>
		<div style="float:left">
		<?php
		echo $form->input('TransAgent.cellphone', array('label' => '', 'style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
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
		<td>Status :<br/>
		<font style="font-size:15px;font-weight:bold;color:#ff8040;">(Uncheck to suspend the agents</font><br/>
		<font style="font-size:15px;font-weight:bold;color:#ff8040;">from all or some sites.)</font>
		</td>
		<td>
		<?php
		echo 'Activated' . $form->checkbox('TransAccount.status', array('checked' => 'checked', 'style' => 'border:0px;width:16px;'));
		?>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
		<?php
		if ($userinfo['role'] == 0 || $userinfo['role'] == 1) {//means an administrator or an office
			echo $form->submit('Add & New',
				array(
					'default' => 'default',
					'div' => array('style' => 'float:left;margin-right:15px;'),
					'style' => 'width:112px;',
					'onclick' => 'javascript:__changeAction("frmReg", "'
						. $html->url(array('controller' => 'trans', 'action' => 'regagent', 'id' => -1))
						. '");' 
				)
			);
			echo $form->submit('Add',
				array(
					'div' => array('style' => 'float:left;margin-right:15px;'),
					'style' => 'width:112px;',
					'onclick' => 'javascript:__changeAction("frmReg", "'
						. $html->url(array('controller' => 'trans', 'action' => 'regagent'))
						. '");'
				)
			);
		}
		/*
		if ($userinfo['role'] == 1) {
			echo $form->submit('Send Request',
				array('div' => array('style' => 'float:left;margin-right:15px;'))
			);
		}
		*/
		?>
		</td>
	</tr>
</table>
<script type="text/javascript" language="javascript"> 
jQuery(":checkbox").attr({style: "border:0px;width:16px;vertical-align:middle;"});
jQuery("#TransAccountUsername").keyup(function(){
	//this.value = this.value.replace('/^[a-z]{1,3}\d{0,4}_{0,1}\d{0,2}$/i', '');
	this.value = this.value.toUpperCase();
});
function dimUsernameInput() {
	if (jQuery("#TransAccountAuto").attr("checked") == true) {
		jQuery("#TransAccountUsername").attr('disabled','disabled');
	} else {
		jQuery("#TransAccountUsername").removeAttr('disabled');
	}
}
dimUsernameInput();
jQuery("#TransAccountAuto").click(function(){
	dimUsernameInput();
});
</script>
<?php
echo $form->input('TransAccount.role', array('type' => 'hidden', 'value' => '2'));//the value 2 as being an agent
echo $form->input('TransAccount.regtime', array('type' => 'hidden', 'value' => ''));//should be set to current time when saving into the DB
echo $form->input('TransAccount.online', array('type' => 'hidden', 'value' => '0'));// the value 0 means "offline"
echo $form->input('TransAgent.id', array('type' => 'hidden'));
echo $form->end();
?>