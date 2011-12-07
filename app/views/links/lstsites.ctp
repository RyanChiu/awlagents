<?php
$userinfo = $session->read('Auth.TransAccount');
?>
<h1>Sites</h1>
<br/>
<div style="margin-bottom:3px">
<?php
echo $form->button('Add Site',
	array(
		'onclick' => 'javascript:location.href="'
			. $html->url(array('controller' => 'links', 'action' => 'addsite')) . '"',
		'style' => 'width:160px;'
	)
);
?>
</div>
<table width="100%">
	<thead>
	<tr>
		<th><?php echo $exPaginator->sort('Campaigns', 'TransViewSite.hostname') . '<br/><font size="1">(for admin only)</font>'; ?></th>
		<th><?php echo $exPaginator->sort('Site Name', 'TransViewSite.sitename') . '<br/><font size="1">(for office or agent)</font>'; ?></th>
		<th><?php echo $exPaginator->sort('Site Type', 'TransViewSite.type'); ?></th>
		<?php
		if ($userinfo['id'] == 2) {
		?>
		<th><?php echo $exPaginator->sort('Abbreviation', 'TransViewSite.abbr') . '<br/><font size="1">(for admin only)</font>'; ?></th>
		<?php
		}
		?>
		<th><?php echo $exPaginator->sort('Sale Types', 'TransViewSite.typestotal'); ?></th>
		<th><?php echo $exPaginator->sort('Status', 'TransViewSite.status'); ?></th>
		<th>Change</th>
	</tr>
	</thead>
	<?php
	foreach ($rs as $r) :
	?>
	<tr>
		<td><?php echo $r['TransViewSite']['hostname'];	?></td>
		<td><?php echo $r['TransViewSite']['sitename'];	?></td>
		<td><?php echo $r['TransViewSite']['type'];	?></td>
		<?php
		if ($userinfo['id'] == 2) {
		?>
		<td><?php echo $r['TransViewSite']['abbr'];	?></td>
		<?php
		}
		?>
		<td>
		<?php
		echo $html->link(
			$r['TransViewSite']['typestotal'] . '&nbsp;' . $html->image('iconList.gif', array('border' => 0)),
			array('controller' => 'links', 'action' => 'lsttypes', 'id' => $r['TransViewSite']['id']),
			array('title' => 'Click to view the types of the site.'),
			false, false
		);
		?>
		</td>
		<td>
		<?php
		echo in_array($r['TransViewSite']['status'], array(0, 1)) ? $status[$r['TransViewSite']['status']] : $status[0];
		?>
		</td>
		<td align="center">
		<?php
		echo $html->link(
			$html->image('iconEdit.png', array('border' => 0, 'width' => 16, 'height' => 16)) . '&nbsp;',
			array('controller' => 'links', 'action' => 'updsite', 'id' => $r['TransViewSite']['id']),
			array('title' => 'Click to edit this site.'),
			false, false
		);
		echo $html->link(
			$html->image('iconActivate.png', array('border' => 0, 'width' => 16, 'height' => 16)) . '&nbsp;',
			array('controller' => 'links', 'action' => 'activatesite', 'id' => $r['TransViewSite']['id']),
			array('title' => 'Click to activate the site.'),
			false, false
		);
		echo $html->link(
			$html->image('iconSuspend.png', array('border' => 0, 'width' => 16, 'height' => 16)) . '&nbsp;',
			array('controller' => 'links', 'action' => 'suspendsite', 'id' => $r['TransViewSite']['id']),
			array('title' => 'Click to suspend the site.'),
			false, false
		);
		?>
		</td>
	</tr>
	<?php
	endforeach;
	?>
</table>
