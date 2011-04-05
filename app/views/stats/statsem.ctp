<?php
switch ($bywhat) {
	case 0:
		echo '<h1>Stats (By Date)</h1>';
		break;
	case 1:
		echo '<h1>Stats (By Office)</h1>';
		break;
	case 2:
		echo '<h1>Stats (By Agent)</h1>';
		break;
	case 3:
		echo '<h1>Stats (Details)</h1>';
		break;
	default:
		echo '<h1>No such stats</h1>';
		break;
}
?>

<div style="width:800px;margin:3px 0px 0px 3px;">
	<b><font size="1">
    *Timezone used to show these stats:<br/>
    GMT +0 TIME IN "LONDON GREAT BRITAIN".<br/>
    FOR PHILIPPINES ADD PLUS + 8 HOURS<br/>
  	</font></b>
</div>

<?php
//echo print_r($rs, true);
$userinfo = $session->read('Auth.TransAccount');
?>
<br/>
<?php
	echo $this->element(
		'searchblock', 
		array(
			'bywhat' => $bywhat,
			'startdate' => $startdate,
			'enddate' => $enddate,
			'sites' => $sites,
			'selsite' => $selsite,
			'periods' => $periods,
			compact('types'),
			compact('seltype'),
			compact('coms'),
			compact('selcom'),
			compact('ags'),
			compact('selagent')
		)
	); 
?>
<br/>

<?php
if (!empty($rs)) {
?>
<table width="100%">
	<caption>
	<font style="color:red;">
	<?php
	if ($startdate != $enddate) {
	?>
	From<?php echo '&nbsp;' . $startdate . '&nbsp;'; ?>To<?php echo '&nbsp;' . $enddate; ?>
	<?php
	} else {
	?>
	Date&nbsp;<?php echo $startdate; ?>
	<?php
	}
	?>
	&nbsp;&nbsp;&nbsp;
	<?php
	echo '(';
	echo 'Site:' . $sites[$selsite];
	echo ', Type:' . $types[$seltype];
	if ($userinfo['role'] == 0) {//means an administrator
		echo ', Office:';
		if (!empty($selcoms)) {
			foreach ($selcoms as $selcom) {echo $coms[$selcom] . ' ';};
		} else {
			echo 'All';
		}
		echo ', Agent:' . $ags[$selagent];
	} else if ($userinfo['role'] == 1) {//means an office
		echo ', Agent:' . $ags[$selagent];
	} else if ($userinfo['role'] == 2) {//means an agent
	}
	echo ')';
	?>
	<?php
	if ($selsite != 1) {
	?>
        <br/>
        <!--<font style="background-color:#80ff00">*On free signup (or free), no comission till it converts</font>-->
    <?php
	}
    ?>
    </font>
    <br/>
    <font style="font-size:12px;font-weight:lighter;">
    <?php
    if ($session->check('crumbs_stats')) {
		$crumbs = $session->read('crumbs_stats');
		//#debug echo str_replace("\n", "<br/>", print_r($crumbs, true));
		$j = 0;
		foreach ($crumbs as $k => $v) {
			$j++;
			if ($j == count($crumbs)) {
				$html->addCrumb($k);
			} else {
				$html->addCrumb($k, $v);
			}
		}    
	    echo $html->getCrumbs(" >> ");
    }
    ?>
    </font>
	</caption>
	<thead>
	<tr>
		<?php
		switch ($bywhat) {
			case 0:
				echo '<th>' . $exPaginator->sort('Date', 'TransTmpStats.trxtime') . '</th>';
				break;
			case 1:
				echo '<th>' . $exPaginator->sort('Office Name', 'TransTmpStats.officename') . '</th>';
				break;
			case 2:
				echo '<th>' . $exPaginator->sort('Agent', 'TransTmpStats.username4m') . '</th>';
				echo '<th>' . $exPaginator->sort('Office Name', 'TransTmpStats.officename') . '</th>';
				break;
			case 3:
				echo '<th>' . $exPaginator->sort('Date', 'TransTmpStats.trxtime') . '</th>';
				echo '<th>' . $exPaginator->sort('Office Name', 'TransTmpStats.officename') . '</th>';
				echo '<th>' . $exPaginator->sort('Agent', 'TransTmpStats.username4m') . '</th>';
				break;
			default:
				echo '<th></th>';
				break;
		}
		?>	
		<th><?php echo $exPaginator->sort('Raws', 'TransTmpStats.raws'); ?></th>
		<th <?php echo !in_array($selsite, array(6, 7, 8)) ? '' : 'class="naClassHide"'; ?>>
		<?php echo $exPaginator->sort('Uniques', 'TransTmpStats.uniques'); ?>
		</th>
		<!--
		<th <?php //echo !in_array($selsite, array(6, 7, 8)) ? '' : 'class="naClassHide"'; ?>>
		<?php //echo $exPaginator->sort('Fraud', 'TransTmpStats.chargebacks'); ?>
		</th>
		-->
		<th <?php echo $selsite != 1 ? '' : 'class="naClassHide"'; ?>>
		<?php echo $exPaginator->sort(in_array($selsite, array(6, 7, 8)) ? 'Free*' : 'Free Signups*', 'TransTmpStats.signups'); ?>
		</th>
		<th <?php echo $userinfo['role'] == 0 ? '' : ''; ?>>
		<?php //echo $exPaginator->sort('Frauds', 'TransTmpStats.frauds'); ?>
		<?php
			echo $exPaginator->sort('Fraud', 'TransTmpStats.frauds');
		?>
		</th>
		<th <?php echo $userinfo['role'] == 0 ? '' : 'class="naClassHide"'; ?>>
		<?php
			echo '<font size="1">'; 
			echo $exPaginator->sort('Fraud', 'TransTmpStats.chargebacks');
			echo '</font>';
			echo '<br/><font size="1">(ORIGINAL)</font>';
		?>
		</th>
		<?php
		$typesv = $types;
		ksort($typesv);
		reset($typesv);
		$typesv = array_values($typesv);
		?>
		<th <?php echo count($typesv) > 4 ? '' : 'class="naClassHide"'; ?>>
		<?php
		echo $exPaginator->sort('Sales', 'TransTmpStats.sales_type4')
			. '<br/><i>' . (count($typesv) > 4 ? $typesv[4] : 'N/A') . '</i>';
		?>
		</th>
		<th <?php echo count($typesv) > 3 ? '' : 'class="naClassHide"'; ?>>
		<?php
		echo $exPaginator->sort('Sales', 'TransTmpStats.sales_type3')
			. '<br/><i>' . (count($typesv) > 3 ? $typesv[3] : 'N/A') . '</i>';
		?>
		</th>
		<th <?php echo count($typesv) > 2 ? '' : 'class="naClassHide"'; ?>>
		<?php
		echo $exPaginator->sort('Sales', 'TransTmpStats.sales_type2')
			. '<br/><i>' . (count($typesv) > 2 ? $typesv[2] : 'N/A') . '</i>';
		?>
		</th>
		<th <?php echo count($typesv) > 1 ? '' : 'class="naClassHide"'; ?>>
		<?php
		echo $exPaginator->sort('Sales', 'TransTmpStats.sales_type1')
			. '<br/><i>' . (count($typesv) > 1 ? $typesv[1] : 'N/A') . '</i>';
		?>
		</th>
		<?php
		if ($userinfo['role'] == 0) {
		?>
		<th><?php echo $exPaginator->sort('Earnings', 'TransTmpStats.earnings'); ?></th>
		<th><?php echo $exPaginator->sort('Payouts', 'TransTmpStats.payouts'); ?></th>
		<?php
		}
		?>
	</tr>
	</thead>
	<?php
	$pagetotals = array(
		'raws' => 0, 'uniques' => 0, 'chargebacks' => 0, 'signups' => 0, 'frauds' => 0,
		'sales_type1' => 0, 'sales_type2' => 0, 'sales_type3' => 0, 'sales_type4' => 0,
		'payouts' => 0, 'earnings' => 0
	);
	$i = 0;
	foreach ($rs as $r) {
		$pagetotals['raws'] += $r['TransTmpStats']['raws'];
		$pagetotals['uniques'] += $r['TransTmpStats']['uniques'];
		$pagetotals['chargebacks'] += $r['TransTmpStats']['chargebacks'];
		$pagetotals['signups'] += $r['TransTmpStats']['signups'];
		$pagetotals['frauds'] += $r['TransTmpStats']['frauds'];
		$pagetotals['sales_type1'] += $r['TransTmpStats']['sales_type1'];
		$pagetotals['sales_type2'] += $r['TransTmpStats']['sales_type2'];
		$pagetotals['sales_type3'] += $r['TransTmpStats']['sales_type3'];
		$pagetotals['sales_type4'] += $r['TransTmpStats']['sales_type4'];
		$pagetotals['payouts'] += $r['TransTmpStats']['payouts'];
		$pagetotals['earnings'] += $r['TransTmpStats']['earnings'];
	?>
	<tr>
		<?php
		switch ($bywhat) {
			case 0:
				echo '<td>'
					. $html->link(
							substr($r['TransTmpStats']['trxtime'], 0, 10),
							array('controller' => 'stats', 'action' => 'statscompany',
								'startdate' => substr($r['TransTmpStats']['trxtime'], 0, 10),
								'enddate' => substr($r['TransTmpStats']['trxtime'], 0, 10),
								'siteid' => $selsite,
								'typeid' => $seltype,
								'companyid' => empty($selcoms) ? implode(',', array_keys($coms)) : implode(',', $selcoms),
								'agentid' => $selagent
							)
						)
					. '</td>';
				break;
			case 1:
				echo '<td>'
					. $html->link(
						$r['TransTmpStats']['officename'],
						array('controller' => 'stats', 'action' => 'statsagent',
							'startdate' => $startdate,
							'enddate' => $enddate,
							'siteid' => $selsite,
							'typeid' => $seltype,
							'companyid' => $r['TransTmpStats']['companyid'],
							'agentid' => $selagent
						)
					)
					. '</td>';
				break;
			case 2:
				echo '<td>'
					/*
					. $r['TransTmpStats']['username']
					*/
					. $html->link(
						$r['TransTmpStats']['username'],
						array('controller' => 'stats', 'action' => 'statsagdetail',
							'startdate' => $startdate,
							'enddate' => $enddate,
							'siteid' => $selsite,
							'typeid' => $seltype,
							'companyid' => empty($selcoms) ? implode(',', array_keys($coms)) : implode(',', $selcoms),
							'agentid' => $r['TransTmpStats']['agentid']
						)
					)
					. '</td>';
				echo '<td>' . $r['TransTmpStats']['officename'] . '</td>';
				break;
			case 3:
				echo '<td>' . substr($r['TransTmpStats']['trxtime'], 0, 10)	. '</td>';
				echo '<td>' . $r['TransTmpStats']['officename']	. '</td>';
				echo '<td>' . $r['TransTmpStats']['username']	. '</td>';
				break;
			default:
				echo '<td></td>';
				break;
		}
		?>
		<td><?php echo $r['TransTmpStats']['raws']; ?></td>
		<td><?php echo $r['TransTmpStats']['uniques']; ?></td>
		<!--<td><?php //echo $r['TransTmpStats']['chargebacks']; ?></td>-->
		<td><?php echo $r['TransTmpStats']['signups']; ?></td>
		<td>
		<?php
		$divID = "divFrauds_" . $i;
		$extID = "imgFrauds_" . $i;
		$frauds = $r['TransTmpStats']['frauds'];
		echo $ajax->div($divID, array('style' => 'float:left;'))
			. (empty($frauds) ? '0' : $frauds)
			. $ajax->divEnd($divID);
		/*
		 * the following "if" paragraph means that:
		 * only if it's a view by details and the site is hms (or others in the future)
		 * which has only one type (only one type is very important here),
		 * and then the frauds could be modified manually only by admins.
		 */
		if ($userinfo['role'] == 0 && $bywhat == 3 && in_array($selsite, array(2, 0, 0))) {
			echo '<div style="float:right;margin:0px 3px 0px 3px">'
				. $html->link(
					$html->image('iconEdit.png', array('style' => 'width:16px;height:16px;border:0px;')),
					"#",
					array('id' => $extID),
					false, false
				)
				. '</div>';
			echo $ajax->editor($divID,
				array('controller' => 'stats', 'action' => 'updfrauds',
					'date' => substr($r['TransTmpStats']['trxtime'], 0, 10),
					'agentid' => $r['TransTmpStats']['agentid'],
					'siteid' => $r['TransTmpStats']['siteid'],
					'typeid' => $r['TransTmpStats']['typeid']
				),
				array(
					'okControl' => 'link',//button, link, false
					'cancelControl' => 'link',//button, link, false
					'okText' => ' yes ',
					'cancelText' => ' no ',
					'rows' => 1,
					'cols' => 2,
					'size' => 2,
					'savingText' => 'updating...',
					'externalControl' => $extID
				)
			);
		}
		?>
		</td>
		<td><?php echo $r['TransTmpStats']['chargebacks']; ?></td>
		<td><?php echo $r['TransTmpStats']['sales_type4']; ?></td>
		<td><?php echo $r['TransTmpStats']['sales_type3']; ?></td>
		<td><?php echo $r['TransTmpStats']['sales_type2']; ?></td>
		<td><?php echo $r['TransTmpStats']['sales_type1']; ?></td>
		<?php
		if ($userinfo['role'] == 0) {
		?>
		<td><?php echo '$' . $r['TransTmpStats']['earnings']; ?></td>
		<td><?php echo '$' . $r['TransTmpStats']['payouts']; ?></td>
		<?php
		}
		?>
	</tr>
	<?php
		$i++;
	}
	?>
	<tr>
		<?php
		switch ($bywhat) {
			case 0:
				echo '<td class="totals" align="right">Page Total</td>';
				break;
			case 1:
				echo '<td class="totals" align="right">Page Total</td>';
				break;
			case 2:
				echo '<td class="totals" align="right">Page Total</td>';
				echo '<td class="totals"></td>';
				break;
			case 3:
				echo '<td class="totals" align="right">Page Total</td>';
				echo '<td class="totals"></td>';
				echo '<td class="totals"></td>';
				break;
			default:
				echo '<td class="totals"></td>';
				break;
		}
		?>
		<td class="totals"><?php echo $pagetotals['raws']; ?></td>
		<td class="totals"><?php echo $pagetotals['uniques']; ?></td>
		<!--<td class="totals"><?php //echo $pagetotals['chargebacks']; ?></td>-->
		<td class="totals"><?php echo $pagetotals['signups']; ?></td>
		<td class="totals"><?php echo $pagetotals['frauds']; ?></td>
		<td class="totals"><?php echo $pagetotals['chargebacks']; ?></td>
		<td class="totals"><?php echo $pagetotals['sales_type4']; ?></td>
		<td class="totals"><?php echo $pagetotals['sales_type3']; ?></td>
		<td class="totals"><?php echo $pagetotals['sales_type2']; ?></td>
		<td class="totals"><?php echo $pagetotals['sales_type1']; ?></td>
		<?php
		if ($userinfo['role'] == 0) {
		?>
		<td class="totals"><?php echo '$' . sprintf('%.2f', $pagetotals['earnings']); ?></td>
		<td class="totals"><?php echo '$' . sprintf('%.2f', $pagetotals['payouts']); ?></td>
		<?php
		}
		?>
	</tr>
	<tr>
		<?php
		switch ($bywhat) {
			case 0:
				echo '<td class="totals" align="right">Overall Total</td>';
				break;
			case 1:
				echo '<td class="totals" align="right">Overall Total</td>';
				break;
			case 2:
				echo '<td class="totals" align="right">Overall Total</td>';
				echo '<td class="totals"></td>';
				break;
			case 3:
				echo '<td class="totals" align="right">Overall Total</td>';
				echo '<td class="totals"></td>';
				echo '<td class="totals"></td>';
				break;
			default:
				echo '<td class="totals"></td>';
				break;
		}
		?>
		<td class="totals"><?php echo $totals['raws']; ?></td>
		<td class="totals"><?php echo $totals['uniques']; ?></td>
		<!--<td class="totals"><?php //echo $totals['chargebacks']; ?></td>-->
		<td class="totals"><?php echo $totals['signups']; ?></td>
		<td class="totals"><?php echo $totals['frauds']; ?></td>
		<td class="totals"><?php echo $totals['chargebacks']; ?></td>
		<td class="totals"><?php echo $totals['sales_type4']; ?></td>
		<td class="totals"><?php echo $totals['sales_type3']; ?></td>
		<td class="totals"><?php echo $totals['sales_type2']; ?></td>
		<td class="totals"><?php echo $totals['sales_type1']; ?></td>
		<?php
		if ($userinfo['role'] == 0) {
		?>
		<td class="totals"><?php echo '$' . $totals['earnings']; ?></td>
		<td class="totals"><?php echo '$' . $totals['payouts']; ?></td>
		<?php
		}
		?>
	</tr>
	<tr>
		<?php
		switch ($bywhat) {
			case 0:
				echo '<td class="totals" align="right">Unique to Sale Ratio</td>';
				break;
			case 1:
				echo '<td class="totals" align="right">Unique to Sale Ratio</td>';
				break;
			case 2:
				echo '<td class="totals" align="right">Unique to Sale Ratio</td>';
				echo '<td class="totals"></td>';
				break;
			case 3:
				echo '<td class="totals" align="right">Unique to Sale Ratio</td>';
				echo '<td class="totals"></td>';
				echo '<td class="totals"></td>';
				break;
			default:
				echo '<td class="totals"></td>';
				break;
		}
		?>
		<td class="totals">
		<?php
		$sales_total = $totals['sales_type1'] + $totals['sales_type2'] + $totals['sales_type3'] + $totals['sales_type4'];
		if ($sales_total) {
			echo '1:' . sprintf('%.2f', $totals['uniques'] / $sales_total);
		} else {
			echo '-';
		}
		?>
		</td>
		<td class="totals"></td>
		<!--<td class="totals"></td>-->
		<td class="totals"></td>
		<td class="totals"></td>
		<td class="totals"></td>
		<td class="totals"></td>
		<td class="totals"></td>
		<td class="totals"></td>
		<td class="totals"></td>
		<?php
		if ($userinfo['role'] == 0) {
		?>
		<td class="totals"></td>
		<td class="totals"></td>
		<?php
		}
		?>
	</tr>
	<tr>
		<?php
		switch ($bywhat) {
			case 0:
				echo '<td class="totals" align="right">Signup to Sale Ratio</td>';
				break;
			case 1:
				echo '<td class="totals" align="right">Signup to Sale Ratio</td>';
				break;
			case 2:
				echo '<td class="totals" align="right">Signup to Sale Ratio</td>';
				echo '<td class="totals"></td>';
				break;
			case 3:
				echo '<td class="totals" align="right">Signup to Sale Ratio</td>';
				echo '<td class="totals"></td>';
				echo '<td class="totals"></td>';
				break;
			default:
				echo '<td class="totals"></td>';
				break;
		}
		?>
		<td class="totals">
		<?php
		if ($sales_total) {
			echo '1:' . sprintf('%.2f', $totals['signups'] / $sales_total);
		} else {
			echo '-';
		}
		?>
		</td>
		<td class="totals"></td>
		<!--<td class="totals"></td>-->
		<td class="totals"></td>
		<td class="totals"></td>
		<td class="totals"></td>
		<td class="totals"></td>
		<td class="totals"></td>
		<td class="totals"></td>
		<td class="totals"></td>
		<?php
		if ($userinfo['role'] == 0) {
		?>
		<td class="totals"></td>
		<td class="totals"></td>
		<?php
		}
		?>
	</tr>
</table>

<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
<?php
echo $this->element('paginationblock');
?>
<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
<?php
}
?>

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
