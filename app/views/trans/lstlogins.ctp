<h1>Login Logs</h1>
<?php
$userinfo = $session->read('Auth.TransAccount');
?>

<?php
if ($userinfo['role'] == 0) {
	echo $form->create(null, array('controller' => 'trans', 'action' => 'lstlogins'));
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
			echo $form->input('ViewOnlineLog.startdate',
				array('label' => '', 'id' => 'datepicker_start', 'style' => 'width:110px;', 'value' => $startdate));
			?>
			</div>
		</td>
		<td class="search-label">End Date:</td>
		<td>
			<div style="float:left;margin-right:46px;">
			<?php
			echo $form->input('ViewOnlineLog.enddate',
				array('label' => '', 'id' => 'datepicker_end', 'style' => 'width:110px', 'value' => $enddate));
			?>
			</div>
		</td>
		<td class="search-label">Office:</td>
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
						'update' => 'ViewOnlineLogAgentid',
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
				echo $form->input('ViewOnlineLog.agentid',
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
	</tr>
	<tr>
		<td></td>
		<td colspan="7">
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
<table width="100%">
<thead>
<tr>
	<th><b><?php echo $exPaginator->sort('Username', 'ViewOnlineLog.username'); ?></b></th>
	<th><b><?php echo $exPaginator->sort('Login', 'ViewOnlineLog.intime'); ?></b></th>
	<th><b><?php echo $exPaginator->sort('Logout', 'ViewOnlineLog.outtime'); ?></b></th>
</tr>
</thead>
<?php
$i = 0;
foreach ($rs as $r) {
?>
<tr <?php echo $i % 2 == 0? '' : 'class="odd"'; ?>>
	<td align="center"><?php echo $r['ViewOnlineLog']['username']; ?></td>
	<td align="center"><?php echo $r['ViewOnlineLog']['intime']; ?></td>
	<td align="center"><?php echo $r['ViewOnlineLog']['outtime']; ?></td>
</tr>
<?php
$i++;
}
?>
</table>

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