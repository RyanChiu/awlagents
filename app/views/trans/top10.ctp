<h1>Top 10</h1>

<?php
echo $form->create(null, array('controller' => 'trans', 'action' => 'top10', 'id' => 'frmTop10'));
?>
<div style="margin:6px 20px 6px 2px;">
<table>
	<tr>
		<td>
		<div style="float:left;margin:0px 5px 0px 0px;">
		<?php
		echo $form->input('Top10.period',
			array(
				'id' => 'selPeriod',
				'label' => '', 'type' => 'select',
				'options' => $periods,
				'selected' => $weekstart . ',' . $weekend,
				'style' => 'width:190px;'
			)
		);
		?>
		</div>
		<div style="float:left;">
		<?php
		echo $form->submit('>>', array('style' => 'width:30px;'));
		?>
		</div>
		</td>
	</tr>
</table>
</div>
<?php
if (!empty($weekrs)) {
?>
	<table width="100%" style="font-size:90%;">
		<caption style="font-style:italic;">
		The Week (From <?php echo $weekstart; ?> To <?php echo $weekend; ?>)
		</caption>
		<thead>
		<tr>
			<th>Top NO.</th>
			<th>Company</th>
			<th>Agent</th>
			<th>Total Sales</th>
		</tr>
		</thead>
		<?php
		$i = 0;
		foreach ($weekrs as $r) {
			$i++;
		?>
		<tr <?php echo $i <= 3 ? 'style="font-weight:bold;"' : ''; ?>>
			<td align="center"><?php echo $i; ?></td>
			<td align="center"><?php echo $r[0]['sales'] > 0 ? $r['TransViewStats']['officename'] : ''; ?></td>
			<td align="center"><?php echo $r[0]['sales'] > 0 ? $r['TransViewStats']['username'] : ''; ?></td>
			<td align="center"><?php echo $r[0]['sales'] > 0 ? $r[0]['sales'] : ''; ?></td>
		</tr>
		<?php
		}
		?>
	</table>
	<div style="display:none">
	<?php echo $form->submit('go', array('id' => 'iptSubmit'));?>
	</div>
<?php
}
echo $form->input('Top10.weekstart', array('type' => 'hidden', 'id' => 'iptWeekstart', 'value' => $weekstart));
echo $form->input('Top10.weekend', array('type' => 'hidden', 'id' => 'iptWeekend', 'value' => $weekend));
echo $form->end();
?>

<script type="text/javascript">
jQuery("#selPeriod").change(function() {
	__zSetFromTo("selPeriod", "iptWeekstart", "iptWeekend");
});
</script>