<?php
//echo print_r($results, true);
$userinfo = $session->read('Auth.TransAccount');
$action = 'updagent';
$submittxt = 'Update';
$title = 'Update Agent';
if ($userinfo['role'] == 1) {
	$action = 'requestchg';
	$submittxt = 'Send Ruquest';
	$title = 'Request For Updating Agent';
}
?>
<h1><?php echo $title; ?></h1>
<?php
echo $form->create(null, array('controller' => 'trans', 'action' => $action));
if ($userinfo['role'] == 1) {
	echo $form->input('Requestchg.role', array('type' => 'hidden', 'value' => '2'));
	echo $form->input('Requestchg.type', array('type' => 'hidden', 'value' => 'upd'));
	echo $form->input('Requestchg.from', array('type' => 'hidden', 'value' => $curcom['TransCompany']['manemail']));
	echo $form->input('Requestchg.offiname', array('type' => 'hidden', 'value' => $curcom['TransCompany']['officename']));
}
?>
<table border="0" width="100%">
	<caption>Fields marked with an asterisk (*) are required.</caption>
	<tr>
		<td width="248px">Office : </td>
		<td>
		<div style="float:left">
		<?php
		if ($userinfo['role'] == 0) {// means an administrator
			echo $form->select('TransAgent.companyid', $cps, null, array('style' => 'width:390px;'));
		} else if ($userinfo['role'] == 1 ) {// means an office
			echo $form->input('TransAgent.companyshadow',
				array(
					'label' => '',
					'style' => 'width:390px;border:0px;background:transparent;',
					'readonly' => 'readonly',
					'value' => $cps[$results['TransAgent']['companyid']]
				)
			);
			echo $form->input('TransAgent.companyid', array('type' => 'hidden'));
		} else if ($userinfo['role'] == 2 ) {// means an agent
			echo $form->input('TransAgent.companyshadow',
				array(
					'label' => '',
					'style' => 'width:390px;border:0px;background:transparent;',
					'readonly' => 'readonly',
					'value' => $cps[$results['TransAgent']['companyid']]
				)
			);
			echo $form->input('TransAgent.companyid', array('type' => 'hidden'));
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
		if ($userinfo['role'] == 2) {//means an agent
			echo $form->input('TransAgent.ag1stname', array('label' => '', 'style' => 'width:390px;border:0px;background:transparent;', 'readonly' => 'readonly'));
		} else {
			echo $form->input('TransAgent.ag1stname', array('label' => '', 'style' => 'width:390px;'));
		}
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
		if ($userinfo['role'] == 2) {//means an agent
			echo $form->input('TransAgent.aglastname', array('label' => '', 'style' => 'width:390px;border:0px;background:transparent;', 'readonly' => 'readonly'));
		} else {
			echo $form->input('TransAgent.aglastname', array('label' => '', 'style' => 'width:390px;'));
		}
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
		if ($userinfo['role'] == 2) {//means an agent
			echo $form->input('TransAgent.email', array('label' => '', 'style' => 'width:390px;border:0px;background:transparent;', 'readonly' => 'readonly'));
		} else {
			echo $form->input('TransAgent.email', array('label' => '', 'style' => 'width:390px;'));
		}
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Username : </td>
		<td>
		<div style="float:left">
		<?php
		if ($userinfo['role'] == 2) {//means an agent
			echo $form->input('TransAccount.username', array('label' => '', 'style' => 'width:390px;border:0px;background:transparent;', 'readonly' => 'readonly'));
		} else {
			echo $form->input('TransAccount.username', array('label' => '', 'style' => 'width:390px;'));
		}
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
		echo $form->select('TransAgent.country', $cts, $results['TransAgent']['country'], array('style' => 'width:390px;'));
		?>
		</div>
		<div style="float:left"><font color="red">*</font></div>
		</td>
	</tr>
	<tr>
		<td>Instant Messenger Contact : <br/><font size="1">(AIM, Yahoo, Skype, MSN, ICQ)</font></td>
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
		if ($userinfo['role'] == 2) {//means an agent
		?>
			<div id="msgbox_nochange" style="display:none;float:left;background-color:#ffffcc;">
			<font color="red">
			Sorry, you can't do this.If you want to, please contact your office or administrator.
			</font>
			</div>
			<script type="text/javascript" language="javascript">
			jQuery(":checkbox").click(
					function () {
						jQuery("#msgbox_nochange").show("normal");
						return false;
					}
			);
			jQuery("#msgbox_nochange").click(
					function () {
						jQuery(this).toggle("normal");
					}
			);
			</script>
		<?php	
		}
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
		if ($userinfo['role'] == 2) {//means an agent
			echo 'Activated' . $form->checkbox('TransAccount.status', array('onclick' => 'javascript:return false;', 'style' => 'border:0px;width:16px;'));
		} else {
			echo 'Activated' . $form->checkbox('TransAccount.status', array('style' => 'border:0px;width:16px;'));
		}
		?>
		</td>
	</tr>
	<tr>
		<td></td>	
		<td>
		<?php
		echo $form->submit($submittxt, array('style' => 'width:112px;'));
		?>
		</td>
	</tr>
</table>
<script type="text/javascript" language="javascript"> 
jQuery(":checkbox").attr({style: "border:0px;width:16px;vertical-align:middle;"}); 
</script>
<?php
echo $form->input('TransAccount.id', array('type' => 'hidden'));
echo $form->input('TransAccount.role', array('type' => 'hidden', 'value' => '2'));//the value 2 as being an agent
echo $form->input('TransAgent.id', array('type' => 'hidden'));
echo $form->end();
?>
