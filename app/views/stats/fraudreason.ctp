<h1>Reasons</h1>
<?php
//echo str_replace("\n", "<br/>", print_r($contents, true));
if (isset($contents['error'])) {
	echo "<b><font color='red'>" . $contents['error'] . "</font></b>";
	exit();
}
?>
<table style="border:0;width:100%;height:100%;">
    <caption><?php echo $contents['date']; ?></caption>
	<thead>
	<tr>
		<th>#</th>
		<th>Type</th>
		<!--<th>Email</th>-->
		<th>Signup date</th>
		<th>Rejected time</th>
		<th>Reason</th>
	</tr>
	</thead>
	<?php
	$i = 0;
	foreach ($contents['data'] as $reason) {
	?>
	<tr>
		<td><?php echo $i + 1; ?></td>
		<td><?php echo $reason['record-type']; ?></td>
		<!--<td><?php echo $reason['email']; ?></td>-->
		<td><?php echo $reason['signup-date']; ?></td>
		<td><?php echo $reason['rejected-time']; ?></td>
		<td><?php echo $reason['reason']; ?></td>
	</tr>
	<?php
	}
	?>
</table>
