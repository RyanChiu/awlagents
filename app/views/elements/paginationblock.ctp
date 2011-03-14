<?php
if ($paginator->hasPage(null, 2)) {
?>
<table>
<tr>
<td>
<!-- Shows the page numbers -->
<?php 
	echo $paginator->numbers(
		array(
			'first' => '|<<', 'last' => '>>|',
			'before' => ' | ', 'after' => ' | ',
			'modulus' => 11
		)
	);
?>
</td>
<td>
<!-- Shows the next and previous links -->
<?php
	echo $paginator->prev(
		$html->image('prev.gif', array('style' => 'border:0px;margin-top:2px;')),
		array('escape' => false),
		$html->image('prev_dis.gif', array('style' => 'border:0px;margin-top:2px;')),
		array('escape' => false, 'class' => 'disabled')
	);
?>
</td>
<td>
<?php
	echo $paginator->next(
		$html->image('next.gif', array('style' => 'border:0px;margin-top:2px;')),
		array('escape' => false),
		$html->image('next_dis.gif', array('style' => 'border:0px;margin-top:2px;')),
		array('escape' => false, 'class' => 'disabled')
	);
?>
</td>
<td>
<!-- prints X of Y, where X is current page and Y is number of pages -->
<?php echo $paginator->counter(); ?>
</td>
</tr>
</table>
<?php
}
?>