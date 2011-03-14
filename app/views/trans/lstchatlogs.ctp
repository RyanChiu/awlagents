<?php
$userinfo = $session->read('Auth.TransAccount');
//echo print_r($userinfo, true);
//echo '<br/>';
?>
<h1>Chat Logs</h1>

<?php
if ($userinfo['role'] != 2) {
echo $form->create(null, array('controller' => 'trans', 'action' => 'lstchatlogs'));
?>
<div style="width:100%;margin-top:5px;">
<table width="100%">
<caption>
<?php echo $html->image('iconSearch.png', array('style' => 'width:16px;height:16px;')) . 'Search'; ?>
</caption>
<tr>
	<td class="search-label" style="width:105px;">Start Date:</td>
	<td>
		<div style="float:left;margin-right:20px;">
		<?php
		echo $form->input('ViewChatLog.startdate',
			array('label' => '', 'id' => 'datepicker_start', 'style' => 'width:110px;', 'value' => $startdate));
		?>
		</div>
	</td>
	<td class="search-label">End Date:</td>
	<td colspan="3">
		<div style="float:left;margin-right:46px;">
		<?php
		echo $form->input('ViewChatLog.enddate',
			array('label' => '', 'id' => 'datepicker_end', 'style' => 'width:110px', 'value' => $enddate));
		?>
		</div>
	</td>
</tr>
<tr>
	<td class="search-label">Company:</td>
	<td>
		<div style="float:left;margin-right:20px;">
		<?php
			echo $form->input('Stats.companyid',
				array('label' => '',
					'options' => $coms, 'type' => 'select',
					'value' => $selcom,
					'style' => 'width:110px;'
				)
			);
			echo $ajax->observeField('StatsCompanyid',
				array(
					'url' => array('controller' => 'stats', 'action' => 'switchagent'),
					'update' => 'ViewChatLogAgentid',
					'loading' => 'Element.hide(\'divAgentid\');Element.show(\'divAgentidLoading\');',
					'complete' => 'Element.show(\'divAgentid\');Element.hide(\'divAgentidLoading\');',
					'frequency' => 0.2
				)
			);
		?>
		</div>
	</td>
	<td class="search-label">Agent:</td>
	<td>
		<div style="float:left;margin-right:20px;">
		<?php
			echo $form->input('ViewChatLog.agentid',
				array('label' => '',
					'options' => $ags, 'type' => 'select',
					'value' => $selagent,
					'style' => 'width:110px;',
					'div' => array('id' => 'divAgentid')
				)
			);
		?>
		</div>
		<div id="divAgentidLoading" style="float:left;width:100px;margin-right:20px;display:none;">
		<?php echo $html->image('iconAttention.gif') . '&nbsp;Loading...'; ?>
		</div>
	</td>
	<td class="search-label">Site:</td>
	<td>
		<div style="float:left;margin-right:20px;">
		</div>
		<?php
			echo $form->input('ViewChatLog.siteid',
				array('label' => '',
					'options' => $sites, 'type' => 'select',
					'value' => $selsite,
					'style' => 'width:150px;'
				)
			);
		?>
	</td>
</tr>
<tr>
	<td></td>
	<td colspan="5">
	<?php
	echo $form->submit('Search', array('style' => 'width:110px;'));
	?>
	</td>
</tr>
</table>
</div>
<?php
echo $form->end();
}
?>

<br/>
<div style="margin-bottom:3px">
<?php
if (in_array($userinfo['role'], array(2))) {//means an agent
	echo $form->button('Submit Chat Log',
		array(
			'onclick' => 'javascript:location.href="' .
				$html->url(array('controller' => 'trans', 'action' => 'addchatlogs')) . '"',
			'style' => 'width:160px;'
		)
	);
}
?>
</div>
<?php
if (!empty($rs)) {
?>
	<table width="100%">
	<thead>
	<tr>
		<th><b><?php echo $exPaginator->sort('Office', 'ViewChatLog.officename'); ?></b></th>
		<th><b><?php echo $exPaginator->sort('Agent', 'ViewChatLog.username4m'); ?></b></th>
		<th><b><?php echo $exPaginator->sort('Site', 'ViewChatLog.sitename'); ?></b></th>
		<th><b><?php echo $exPaginator->sort('Client Name', 'ViewChatLog.clientusername'); ?></b></th>
		<th><b><?php echo 'Conversation'; ?></b></th>
		<th><b><?php echo $exPaginator->sort('Submit Time', 'ViewChatLog.submittime'); ?></b></th>
	</tr>
	</thead>
	<?php
	$i = 0;
	foreach ($rs as $r) {
	?>
	<tr <?php echo $i % 2 == 0? '' : 'class="odd"'; ?>>
		<td align="center"><?php echo $r['ViewChatLog']['officename']; ?></td>
		<td align="center"><?php echo $r['ViewChatLog']['username']; ?></td>
		<td align="center"><?php echo $r['ViewChatLog']['sitename']; ?></td>
		<td align="center"><?php echo $r['ViewChatLog']['clientusername']; ?></td>
		<td><?php echo str_replace("\n", "<br/>", $r['ViewChatLog']['conversation']); ?></td>
		<td align="center"><?php echo $r['ViewChatLog']['submittime']; ?></td>
	</tr>
	<?php
	$i++;
	}
	?>
	</table>
<?php
}
?>

<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
<?php
echo $this->element('paginationblock');
?>
<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->

<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery(function() {
		jQuery('#datepicker_start').datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd'
		});
	});
});
</script>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery(function() {
		jQuery('#datepicker_end').datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd'
		});
	});
});
</script>