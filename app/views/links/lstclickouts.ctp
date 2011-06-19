<?php
$userinfo = $session->read('Auth.TransAccount');
//echo str_replace("\n", "<br>", print_r($rs[0], true));
?>
<h1>Click Logs</h1>

<div style="width:100%;margin-top:5px;" id="search">
<?php
echo $form->create(null, array('controller' => 'links', 'action' => 'lstclickouts'));
?>
<table width="100%">
<caption>
<?php echo $html->image('iconSearch.png', array('style' => 'width:16px;height:16px;')) . 'Search'; ?>
</caption>
<tr>
	<td class="search-label" style="width:105px;">Office</td>
	<td>
		<div style="float:left;margin-right:20px;">
		<?php
			if ($userinfo['role'] != 2) {
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
						'update' => 'StatsAgentid',
						'loading' => 'Element.hide(\'divAgentid\');Element.show(\'divAgentidLoading\');',
						'complete' => 'Element.show(\'divAgentid\');Element.hide(\'divAgentidLoading\');',
						'frequency' => 0.2
					)
				);
			} else {
				echo $form->input('Stats.companyid',
					array('label' => '',
						'type' => 'hidden',
						'value' => $selcom
					)
				);
				echo $coms[$selcom];
			}
		?>
		</div>
	</td>
	<td class="search-label" style="width:65px;">Agent</td>
	<td>
		<div style="float:left;margin-right:20px;">
		<?php
			if ($userinfo['role'] != 2) {
				echo $form->input('Stats.agentid',
					array('label' => '',
						'options' => $ags, 'type' => 'select',
						'value' => $selagent,
						'style' => 'width:110px;',
						'div' => array('id' => 'divAgentid')
					)
				);
			} else {
				echo $form->input('Stats.agentid',
					array('label' => '',
						'type' => 'hidden',
						'value' => $selagent
					)
				);
				echo $ags[$selagent];
			}
		?>
		</div>
		<div id="divAgentidLoading" style="float:left;width:100px;margin-right:20px;display:none;">
		<?php echo $html->image('iconAttention.gif') . '&nbsp;Loading...'; ?>
		</div>
	</td>
	<td class="search-label" style="width:65px;">Date</td>
	<td>
		<div style="float:left;width:50px;">
			<b>Start:</b>
		</div>
		<div style="float:left;margin-right:20px;">
		<?php
		echo $form->input('TransViewClickout.startdate',
			array('label' => '', 'id' => 'datepicker_start', 'style' => 'width:80px;', 'value' => $startdate));
		?>
		</div>
		<div style="float:left;width:40px;">
			<b>End:</b>
		</div>
		<div style="float:left;margin-right:46px;">
		<?php
		echo $form->input('TransViewClickout.enddate',
			array('label' => '', 'id' => 'datepicker_end', 'style' => 'width:80px', 'value' => $enddate));
		?>
		</div>
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
<?php
echo $form->end();
?>
</div>
<br/>

<table width="100%">
<caption>
Start Date:<?php echo $startdate; ?>&nbsp;&nbsp;End Date:<?php echo $enddate; ?>&nbsp;&nbsp;|&nbsp;&nbsp;
Office:<?php echo $coms[$selcom]; ?>&nbsp;&nbsp;Agent:<?php echo $ags[$selagent]; ?>
<br/>
<font color="red" size="2"><b>(Click on IP to see a address for a world map, where your link was opened.)</b></font>
</caption>
<thead>
<tr>
	<th><b><?php echo $exPaginator->sort('Office', 'TransViewClickout.officename'); ?></b></th>
	<th><b><?php echo $exPaginator->sort('Agent', 'TransViewClickout.username'); ?></b></th>
	<th><b><?php echo $exPaginator->sort('Click Time', 'TransViewClickout.clicktime'); ?></b></th>
	<th><b><?php echo $exPaginator->sort('IP Address From', 'TransViewClickout.fromip'); ?></b></th>
</tr>
</thead>
<?php
$i = 0;
foreach ($rs as $r):
?>
<tr <?php echo $i % 2 == 0 ? '' : 'class="odd"'; ?>>
	<td><?php echo $r['TransViewClickout']['officename']; ?></td>
	<td><?php echo $r['TransViewClickout']['username']; ?></td>
	<td><?php echo $r['TransViewClickout']['clicktime']; ?></td>
	<td>
		<a href="http://whatismyipaddress.com/ip/<?php echo $r['TransViewClickout']['fromip']; ?>" target="findip_window">
			<?php echo $r['TransViewClickout']['fromip']; ?>
		</a>
	</td>
</tr>
<?php
$i++;
endforeach;
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
