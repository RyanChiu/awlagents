<h1>Agents</h1>
<?php
/*
echo '<br/>';
echo print_r($url, true);
echo print_r($pass, true);
echo print_r($passedArgs, true);
*/
$userinfo = $session->read('Auth.TransAccount');
?>
<?php
/*searching part*/
?>
<div style="width:100%;margin-top:5px;" id="search">
<?php
echo $form->create(null, array('controller' => 'trans', 'action' => 'lstagents', 'id' => 'frmSearch'));
?>
<table width="100%" border="0">
	<caption>
	<?php echo $html->image('iconSearch.png', array('style' => 'width:16px;height:16px;')) . 'Search'; ?>
	</caption>
	<tr>
		<td class="search-label" style="width:105px;">Username:</td>
		<td>
		<div style="float:left;width:275px;">
		<?php echo $form->input('TransViewAgent.username', array('label' => '', 'style' => 'width:260px;')); ?>
		</div>
		</td>
		<?php
		if ($userinfo['role'] == 0) {
		?>
		<td class="search-label" style="width:145px;">Office:</td>
		<?php
		} else {
		?>
		<td colspan="2" width="450px"></td>
		<?php
		}
		if ($userinfo['role'] == 0) {
			echo '<td>';
			echo $form->input('TransCompany.id',
				array('label' => '', 'type' => 'select',
					'options' => $coms,	'style' => 'width:260px;'
				)
			);
			echo '</td>';
		}
		?>
	</tr>
	<tr>
		<td class="search-label" style="width:105px;">Last Name:</td>
		<td>
		<div style="float:left;width:275px;">
		<?php echo $form->input('TransViewAgent.aglastname', array('label' => '', 'style' => 'width:260px;')); ?>
		</div>
		</td>
		<td class="search-label">Campaign ID:</td>
		<td>
		<div style="float:left;width:275px;">
		<?php echo $form->input('AgentSiteMapping.campaignid', array('label' => '', 'style' => 'width:260px;')); ?>
		</div>
		</td>
	</tr>
	<tr>
		<td class="search-label" style="width:105px;">First Name:</td>
		<td>
		<div style="float:left;width:275px;">
		<?php echo $form->input('TransViewAgent.ag1stname', array('label' => '', 'style' => 'width:260px;')); ?>
		</div>
		</td>
		<td class="search-label">Status:</td>
		<td>
		<div style="float:left;width:275px;">
		<?php
		echo $form->input('TransViewAgent.status',
			array(
				'label' => '', 'style' => 'width:260px;',
				'type' => 'select', 'options' => (array('-1' => 'All') + $status)
			)
		);
		?>
		</div>
		</td>
	</tr>
	<tr>
		<td class="search-label" style="width:105px;">Email:</td>
		<td>
		<div style="float:left;width:275px;">
		<?php echo $form->input('TransViewAgent.email', array('label' => '', 'style' => 'width:260px;')); ?>
		</div>
		</td>
		<td class="search-label">Suspended Site:</td>
		<td>
		<?php
		echo $form->input('SiteExcluding.siteid',
			array('label' => '', 'type' => 'select',
				'options' => $sites,	'style' => 'width:260px;'
			)
		);
		?>
		</td>
	</tr>
	<tr>
		<td colspan="2"></td>
		<td colspan="2">
		<div style="float:left;width:112px;">
		<?php echo $form->submit('Search', array('style' => 'float:left;width:96px;')) ?>
		</div>
		<div style="float:left;">
		<?php echo $form->submit('Clear', array('style' => 'float:left;width:96px;', 'onclick' => 'javascript:__zClearForm("frmSearch");')); ?>
		</div>
		</td>
	</tr>
</table>
<?php
$companyid = 0;
if ($userinfo['role'] == 1) {//means an office
	$companyid = $userinfo['id'];
}
echo $form->input('TransViewAgent.companyid', array('type' => 'hidden', 'value' => $companyid));
echo $form->end();
?>
</div>
<br/>

<?php
/*showing the results*/
?>
<script type="text/javascript" language="javascript">
function __setActSusLink() {
	var checkboxes;
	checkboxes = document.getElementsByName("data[TransViewAgent][selected]");
	var ids = "";
	for (var i = 0; i < checkboxes.length; i++) {
		if (checkboxes[i].checked && checkboxes[i].value != 0) {
			ids += checkboxes[i].value + ",";
		}
	}
	document.getElementById("linkActivateSelected").href =
		document.getElementById("linkActivateSelected_").href + "/ids:" + ids + "/status:1/from:1";
	document.getElementById("linkSuspendSelected").href =
		document.getElementById("linkSuspendSelected_").href + "/ids:" + ids + "/status:0/from:1";
}
function __setCurSelectedToBeInformed() {
	var checkboxes;
	checkboxes = document.getElementsByName("data[TransViewAgent][selected]");
	var ids = "";
	for (var i = 0; i < checkboxes.length; i++) {
		if (checkboxes[i].checked && checkboxes[i].value != 0) {
			ids += checkboxes[i].value + ",";
		}
	}
	document.getElementById("hidCurSelectedToBeInformed").value = ids;
}
function __setInfLink() {
	document.getElementById("linkInform").href =
		document.getElementById("linkInform_").href + "/from:1"
			+ "/ids:" + document.getElementById("hidCurSelectedToBeInformed").value
			+ "/notes:" + document.getElementById("txtNotes").value;
}
function __checkAll() {
	var checkboxes;
	checkboxes = document.getElementsByName("data[TransViewAgent][selected]");
	for (var i = 0; i < checkboxes.length; i++) {
		checkboxes[i].checked = document.getElementById("checkboxAll").checked;
	}
}
</script>

<div style="margin-bottom:3px">
<?php
if (in_array($userinfo['role'], array(0, 1)) && $userinfo['id'] != 1) {//means an administrator or an office
	//echo $form->button($userinfo['role'] == 0 ? 'Add Agent' : 'Request New Agent',
	echo $form->button('Add Agent',
		array(
			'onclick' => 'javascript:location.href="' .
				$html->url(array('controller' => 'trans', 'action' => 'regagent')) . '"',
			'style' => 'width:160px;'
		)
	);
}
?>
</div>
<table width="100%">
<thead>
<tr>
	<th><b>
	<?php
	echo $form->checkbox('',
		array('id' => 'checkboxAll', 'value' => -1,
			'style' => 'border:0px;width:16px;',
			'onclick' => 'javascript:__checkAll();__setActSusLink();'
		)
	);
	?>
	</b></th>
	<th><b><?php echo $exPaginator->sort('Username', 'TransViewAgent.username4m'); ?></b></th>
	<th><b><?php echo $exPaginator->sort('Password', 'TransViewAgent.originalpwd'); ?></b></th>
	<th><b><?php echo $exPaginator->sort('Office', 'TransViewAgent.officename'); ?></b></th>
	<th><b><?php echo $exPaginator->sort('Registered', 'TransViewAgent.regtime'); ?></b></th>
	<th><b><?php echo $exPaginator->sort('Last Login', 'TransViewAgent.lastlogintime'); ?></b></th>
	<th><b><?php echo $exPaginator->sort('Login Times', 'TransViewAgent.logintimes'); ?></b></th>
	<th><b><?php echo $exPaginator->sort('Status', 'TransViewAgent.status'); ?></b></th>
	<th><b><?php echo $exPaginator->sort('Campaigns', 'TransViewAgent.campaigns'); ?></b></th>
	<th <?php echo (($userinfo['id'] != 1) ? '' : 'class="naClassHide"'); ?>><b>Operation</b></th>
	<th>
	<?php
	echo $html->link(
		$html->image('viewit.png', array('border' => 0)),
		array('controller' => 'trans', 'action' => 'lstchatlogs'),
		array('title' => 'Click to all the chat logs.'),
		false, false
	);
	?>
	</th>
</tr>
</thead>
<?php
$i = 0;
foreach ($rs as $r):
?>
<tr <?php echo $i % 2 == 0? '' : 'class="odd"'; ?>>
	<td>
	<?php
	echo $form->checkbox('TransViewAgent.selected',
		array('value' => $r['TransViewAgent']['id'],
			'style' => 'border:0px;width:16px;',
			'onclick' => 'javascript:__setActSusLink();'
		)
	);
	echo '<font size="1">' . ($i + 1 + $limit * ($paginator->current() - 1)) . '</font>';
	?>
	</td>
	<td><?php echo $r['TransViewAgent']['username']; ?></td>
	<td><?php echo $r['TransViewAgent']['originalpwd']; ?></td>
	<td><?php echo $r['TransViewAgent']['officename']; ?></td>
	<td><?php echo $r['TransViewAgent']['regtime']; ?></td>
	<td>
	<?php
	$lastintm = $r['TransViewAgent']['lastlogintime'];
	echo empty($lastintm) ? '-' : $lastintm;
	?>
	</td>
	<td align="center">
	<?php
	if ($r['TransViewAgent']['logintimes'] == 0) {
		echo $r['TransViewAgent']['logintimes'];
	} else {
		echo $html->link(
			$r['TransViewAgent']['logintimes'] . '&nbsp;' . $html->image('iconList.gif', array('border' => 0)),
			array('controller' => 'trans', 'action' => 'lstlogins', 'id' => $r['TransViewAgent']['id']),
			array('title' => 'Click to the login logs'), false, false
		);
	}
	?>
	</td>
	<td><?php echo $status[$r['TransViewAgent']['status']]; ?></td>
	<td align="center">
	<?php
	echo $html->link(
		$r['TransViewAgent']['campaigns'] . '&nbsp;' . $html->image('iconList.gif', array('border' => 0)),
		array('controller' => 'links', 'action' => 'lstcampaigns', 'id' => $r['TransViewAgent']['id']),
		array('title' => 'Click to the campaigns.'), false, false
	);
	?>
	</td>
	<td align="center">
	<?php
	echo $html->link(
		$html->image('iconEdit.png', array('border' => 0, 'width' => 16, 'height' => 16)) . '&nbsp;',
		array('controller' => 'trans', 'action' => 'updagent', 'id' => $r['TransViewAgent']['id']),
		array('title' => 'Click to edit this record.'),
		false, false
	);
	echo $html->link(
		$html->image('iconActivate.png', array('border' => 0, 'width' => 16, 'height' => 16)) . '&nbsp;',
		array('controller' => 'trans', 'action' => 'activatem', 'ids' => $r['TransViewAgent']['id'], 'status' => 1, 'from' => 1),
		array('title' => 'Click to activate the user.'),
		false, false
	);
	echo $html->link( 
		$html->image('iconSuspend.png', array('border' => 0, 'width' => 16, 'height' => 16)) . '&nbsp;',
		array('controller' => 'trans', 'action' => 'activatem', 'ids' => $r['TransViewAgent']['id'], 'status' => 0, 'from' => 1),
		array('title' => 'Click to suspend the user.'),
		false, false
	);
	?>
	</td>
	<td align="center">
	<?php
	echo $html->link(
		$html->image('chatlogs.png', array('border' => 0)),
		array('controller' => 'trans', 'action' => 'lstchatlogs', 'id' => $r['TransViewAgent']['id']),
		array('title' => 'Click to the chat logs.'),
		false, false
	);
	?>
	</td>
</tr>
<?php
$i++;
endforeach;
?>
</table>

<?php
if ($userinfo['id'] != 1) {
?>
<div style="margin-top:3px;">
<font color="green">With selected :&nbsp;</font>
<?php
/*activate selected*/
echo $html->link(
	$html->image('iconActivate.png', array('border' => 0, 'width' => 16, 'height' => 16)) . '&nbsp;&nbsp;',
	array('controller' => 'trans', 'action' => 'activatem'),
	array('id' => 'linkActivateSelected', 'title' => 'Click to activate the selected users.'),
	false, false
);
echo $html->link(
	'',
	array('controller' => 'trans', 'action' => 'activatem'),
	array('id' => 'linkActivateSelected_')
);
/*suspend selected*/
echo $html->link(
	$html->image('iconSuspend.png', array('border' => 0, 'width' => 16, 'height' => 16)) . '&nbsp;&nbsp;',
	array('controller' => 'trans', 'action' => 'activatem'),
	array('id' => 'linkSuspendSelected', 'title' => 'Click to suspend the selected users.'),
	false, false
);
echo $html->link(
	'',
	array('controller' => 'trans', 'action' => 'activatem'),
	array('id' => 'linkSuspendSelected_')
);
/*inform selected --*/
/*undim this block to function it
echo $html->link(
	$html->image('iconInform.png',
		array('id' => 'open_message',
			'border' => 0, 'width' => 16, 'height' => 16,
			'onclick' => 'javascript:__setCurSelectedToBeInformed();__setInfLink();'
		)
	),
	'#',
	array('title' => 'Click to inform the selected users.'),
	false, false
);
*/
?>
</div>
<?php
}
?>

<!-- ~~~~~~~~~~~~~~~~~~~the floating message box for "inform selected"~~~~~~~~~~~~~~~~~~~ -->
<div id="message_box">
	<table width="100%">
	<thead><tr><th>
		<div style="float:left">Please enter your notes below.</div>
		<?php echo $html->image('iconClose.png', array('id' => 'close_message', 'style' => 'float:right;cursor:pointer')); ?>
	</th></tr></thead>
	<tr><td><textarea id="txtNotes" style="width:99%" rows="6" onchange="javascript:__setInfLink();"></textarea></td></tr>
	<tr><td>
		<?php
		echo $form->input('', array('type' => 'hidden', 'id' => 'hidCurSelectedToBeInformed'));
		echo $html->link(
			'',
			array('controller' => 'trans', 'action' => 'informem'),
			array('id' => 'linkInform_')
		);
		echo $html->link(
			'Inform',
			array('controller' => 'trans', 'action' => 'informem'),
			array('id' => 'linkInform'),
			false, false
		);
		?>
	</td></tr>
	</table>
</div>
<script type="text/javascript" language="javascript">
jQuery(":checkbox").attr({style: "border:0px;width:16px;vertical-align:middle;"}); 
</script>
<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
<?php
echo $this->element('paginationblock');
?>
<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->

<script type="text/javascript">
jQuery(document).ready(function(){
	var obj;
	obj = jQuery(".naClassHide");
	tbl = obj.parent().parent().parent();
	obj.each(function(i){
		idx = jQuery("th", obj.parent()).index(this);
		this.hide();
		jQuery("td:eq(" + idx + ")", jQuery("tr", tbl)).hide();
	});
});
</script>