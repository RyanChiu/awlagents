<h1>Types</h1>
<br/>
<table width="100%">
<caption><u>Host Name:</u><?php if (!empty($rs)) echo $rs[0]['TransViewType']['hostname']; ?></caption>
<thead>
	<tr>
		<th><?php echo $exPaginator->sort('Type Name', 'TransViewType.typename'); ?></th>
		<th><?php echo $exPaginator->sort('Type URL', 'TransViewType.url'); ?></th>
		<th><?php echo $exPaginator->sort('Payout', 'TransViewType.price'); ?></th>
		<th><?php echo $exPaginator->sort('Earning', 'TransViewType.earning'); ?></th>
		<th>Start</th>
		<th>End</th>
		<th><?php echo $exPaginator->sort('Status', 'TransViewType.status'); ?></th>
		<th>Operation</th>
	</tr>
</thead>
	<?php
	foreach ($rs as $r) :
	?>
	<tr>
		<td align="center"><?php echo $r['TransViewType']['typename']; ?></td>
		<td align="center"><?php echo $r['TransViewType']['url']; ?></td>
		<td align="center"><?php echo $r['TransViewType']['price']; ?></td>
		<td align="center"><?php echo $r['TransViewType']['earning']; ?></td>
		<td align="center"><?php echo $r['TransViewType']['start']; ?></td>
		<td align="center"><?php echo $r['TransViewType']['end']; ?></td>
		<td align="center"><?php echo $r['TransViewType']['status'] == 0 ? 'Suspended' : 'Activated'; ?></td>
		<td align="center">
		<?php
		echo $html->link(
			$html->image('iconEdit.png', array('border' => 0, 'width' => 16, 'height' => 16)) . '&nbsp;',
			array('controller' => 'links', 'action' => 'updtype', 'id' => $r['TransViewType']['id']),
			array('title' => 'Click to edit this type.'),
			false, false
		);
		?>
		</td>
	</tr>
	<?php
	endforeach;
	?>
</table>
