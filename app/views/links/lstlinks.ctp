<?php
//echo print_r($rs, true);
App::import('vendor', 'ExtraKits', array('file' => 'extrakits.inc.php'));
$userinfo = $session->read('Auth.TransAccount');
?>
<h1>Link Codes</h1>
<br/>
<div style="float:right">
<?php
if ($userinfo['role'] == 0) {//means an administrator
	echo $html->link(
		'Configure Sites...',
		array('controller' => 'links', 'action' => 'lstsites')
	);
}
?>
</div>
<!--  
<small>(You're from:<?php //echo __getclientip(); ?>, and you'll be <?php //echo __isblocked(__getclientip()) ? 'blocked.' : 'passed.'; ?>)</small>
-->
<?php
echo $form->create(null, array('controller' => 'links', 'action' => 'lstlinks'))
?>
<table width="100%">
<caption>
	Please Select An Agent &amp; Generate Link Codes
	<br/>
	<font style="color:red;">
	<?php
	if (!empty($suspsites)) {
		echo '>>Site "' . implode(",", $suspsites) . '"' . (count($suspsites) > 1 ? ' are' : ' is')
			. ' suspended for now.';
	}
	?>
	</font>
</caption>
<tr>
	<td width="31%" align="right">
	<?php
	echo $form->input('TransSite.id',
		array('options' => $sites, 'style' => 'width:170px;', 'label' => 'Site:', 'type' => 'select')
	);
	?>
	</td>
	<td width="40%" align="center">
	<?php
	echo $form->input('TransViewAgent.id',
		array('options' => $ags, 'style' => 'width:290px;', 'label' => 'Agent:', 'type' => 'select')
	);
	?>
	</td>
	<td width="29%">
	<?php
	echo $form->submit('Generate Link Codes', array('style' => 'width:180px;'));
	?>
	</td>
</tr>
</table>
<?php
echo $form->end();
?>

<br/>
<?php
if (!empty($rs)) {
?>
	<table width="100%" border="0">
	<?php
	foreach ($rs as $r):
		if (array_key_exists('TransViewLink', $r)) {
	?>
		<tr>
			<td align="center">
			<?php
			echo $r['TransViewLink']['sitename'] . '_' . $r['TransViewLink']['typename'] . ':&nbsp;&nbsp;&nbsp;';
			echo '<b>';
			echo $html->url(
				array('controller' => 'trans', 'action' => 'golink',
					'to' => __codec($r['TransViewLink']['id'] . ',' . $r['TransViewLink']['agentid'], 'E')
				),
				true
			);
			echo '</b>';
			?>
			</td>	
		</tr>
	<?php
		} else if (array_key_exists('AgentSiteMapping', $r)) {
			foreach ($types as $type) {
	?>
		<tr>
			<td align="center">
			<?php
			echo $sites[$r['AgentSiteMapping']['siteid']] . '_' . $type['TransType']['typename'] . ':&nbsp;&nbsp;&nbsp;';
			echo '<b>';
			echo $html->url(array('controller' => 'trans', 'action' => 'go'), true) . '/'
				. $r['AgentSiteMapping']['siteid'] . '/'
				. $type['TransType']['id']. '/'
				. $ags[$r['AgentSiteMapping']['agentid']];
			echo '</b>';
			?>
			</td>
		</tr>
	<?php
			}
		}
	endforeach;
	?>
	</table>
<?php
}
?>
