<?php
date_default_timezone_set("Asia/Bangkok");
$userinfo = $session->read('Auth.TransAccount');
$curaction = '';
if ($bywhat == 0) $curaction = 'statsdate';
else if ($bywhat == 1) $curaction = 'statscompany';
else if ($bywhat == 2) $curaction = "statsagent";
else if ($bywhat == 3) $curaction = 'statsagdetail';
echo $form->create(null, array('controller' => 'stats', 'action' => $curaction, 'id' => 'frmStats'));
?>
<table width="100%">
<thead>
<tr>
	<th>
		<div style="float:left;margin-right:50px;">
		<input type="radio" name="viewby" id="viewbydate" style="width:10px;border:0px;"
		onclick='javascript:__changeAction("frmStats", "<?php echo $html->url(array('controller' => 'stats', 'action' => 'statsdate')); ?>");return true;'
		<?php echo $bywhat == 0 ? 'checked' : ''; ?>
		/>
		<label for="viewbydate">View By Date</label>
		</div>
		<div style="float:left;margin-right:50px;">
		<input type="radio" name="viewby" id="viewbycompany" style="width:10px;border:0px;"
		onclick='javascript:__changeAction("frmStats", "<?php echo $html->url(array('controller' => 'stats', 'action' => 'statscompany')); ?>");return true;'
		<?php echo $bywhat == 1 ? 'checked' : ''; ?>
		/>
		<label for="viewbycompany">View By Office</label>
		</div>
		<div style="float:left;margin-right:50px;">
		<input type="radio" name="viewby" id="viewbyagent" style="width:10px;border:0px;"
		onclick='javascript:__changeAction("frmStats", "<?php echo $html->url(array('controller' => 'stats', 'action' => 'statsagent')); ?>");return true;'
		<?php echo $bywhat == 2 ? 'checked' : ''; ?>
		/>
		<label for="viewbyagent">View By Agent</label>
		</div>
		<?php
		if ($userinfo['role'] == 0) {
		?>
		<div style="float:left;margin-right:50px;">
		<input type="radio" name="viewby" id="viewbyagdetail" style="width:10px;border:0px;"
		onclick='javascript:__changeAction("frmStats", "<?php echo $html->url(array('controller' => 'stats', 'action' => 'statsagdetail')); ?>");return true;'
		<?php echo $bywhat == 3 ? 'checked' : ''; ?>
		/>
		<label for="viewbyagdetail">Fraud Control</label>
		</div>
		<?php
		}
		?>
	</th>
</tr>
</thead>
<tr>
	<td>
		<div style="float:left;width:90px;">
			<b>Site:</b>
		</div>
		<div style="float:left;margin-right:20px;">
		<?php
		echo $form->input('Stats.siteid',
			array('label' => '',
				'options' => $sites, 'type' => 'select', 'selected' => $selsite,
				'style' => 'width:160px;',
				'onchange' => 'javascript:if (jQuery("#StatsSiteid").val() == -1) {alert("Please choose a site, or the stats will not be loaded.");return false;} else return true;'
			)
		);
		echo $ajax->observeField('StatsSiteid',
			array(
				'url' => array('controller' => 'stats', 'action' => 'switchtype'),
				'update' => 'StatsTypeid',
				'loading' => 'Element.hide(\'divTypeid\');Element.show(\'divTypeidLoading\');',
				'complete' => 'Element.show(\'divTypeid\');Element.hide(\'divTypeidLoading\');',
				'frequency' => 0.2
			)
		);
		?>
		</div>
	</td>
</tr>
<tr>
	<td>
		<div style="float:left;width:90px;">
			<b>Type:</b>
		</div>
		<div style="float:left;margin-right:20px;">
		<?php
		echo $form->input('Stats.typeid',
			array('label' => '', 'options' => $types,
				'type' => 'select', 'selected' => $seltype,
				'style' => 'width:160px;',
				'div' => array('id' => 'divTypeid')
			)
		);
		?>
		</div>
		<div id="divTypeidLoading" style="float:left;width:160px;margin-right:20px;display:none;">
		<?php echo $html->image('iconAttention.gif') . '&nbsp;Loading...'; ?>
		</div>
		<div style="float:left;margin-right:20px;">
		<?php
		if ($userinfo['role'] == 0) {//means an administrator
		?>
		<div style="float:left;width:90px;">
			<b>Office:</b>
		</div>
		<div style="float:left;margin-right:20px;">
			<input id="iptComs" type="text"
				readonly="readonly"
				style="width:158px;cursor:default;"
				<?php
				$selcomnames = array();
				if (empty($selcoms) || count($selcoms) == count($coms)) {
					array_push($selcomnames, 'All');
				} else {
					foreach ($selcoms as $selcom) {
						array_push($selcomnames, $coms[$selcom]); 
					};
				}
				?>
				value="<?php echo empty($selcomnames) ? 'All' : implode(",", $selcomnames); ?>"
			/>
		</div>
		<div id="divComs" style="display:none;">
		<?php
			echo $form->select('Stats.companyid',
				$coms,
				empty($selcoms) ? 0 : $selcoms,
				array(
					'style' => 'width:160px;height:90px;',
					'multiple' => 'multiple'
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
		?>
		</div>
		<script type="text/javascript">
			jQuery("#iptComs").click(function(){
				var box = jQuery("#divComs");
		        if(box != undefined){
		        	box.addClass("wbx").css({
			        	position:"absolute",
			        	left:jQuery(this).offset().left,
			        	top:jQuery(this).offset().top+jQuery(this).outerHeight()+1,
			        	zIndex:100
			        });
			        if (box.is(":hidden")) {
		        		box.show("fast");
			        } else {
			        	box.hide("fast");
			        }
		        }
			});
			jQuery("#StatsCompanyid").blur(function(){
				var box = jQuery("#divComs");
		        box.hide("fast");
			});
			jQuery("#StatsCompanyid").change(function(){
				var ipt = jQuery("#iptComs");
				var sels = new Array();
				jQuery("#StatsCompanyid").find("option:selected").each(function(i) {
					sels.push(this.text);
				})
				if (sels.length > 0 && sels.length < <?php echo count($coms);?>) {
					ipt.attr("value", sels.join(","));
				} else {
					ipt.attr("value", "All");
				}
			});
		</script>
		<div style="float:left;width:60px;">
			<b>Agent:</b>
		</div>
		<div style="float:left;margin-right:20px;">
		<?php
			echo $form->input('Stats.agentid',
				array('label' => '',
					'options' => $ags, 'type' => 'select',
					'selected' => $selagent, 'style' => 'width:160px;',
					'div' => array('id' => 'divAgentid')
				)
			);
		?>
		</div>
		<div id="divAgentidLoading" style="float:left;width:158px;margin-right:20px;display:none;">
		<?php echo $html->image('iconAttention.gif') . '&nbsp;Loading...'; ?>
		</div>
		<?php
		}
		else if ($userinfo['role'] == 1) {//means an office
			echo $form->input('Stats.companyid', array('type' => 'hidden', 'value' => $userinfo['id']));
		?>
		<div style="float:left;width:60px;">
			<b>Agent:</b>
		</div>
		<div style="float:left;margin-right:20px;">
		<?php
			echo $form->input('Stats.agentid',
				array('label' => '', 'options' => $ags, 'type' => 'select', 'selected' => $selagent, 'style' => 'width:160px;'));
		?>
		</div>
		<?php
		}
		else if ($userinfo['role'] == 2) {//means an agent
			echo $form->input('Stats.companyid', array('type' => 'hidden', 'value' => 0));
			$_ags = array_keys($ags);
			echo $form->input('Stats.agentid', array('type' => 'hidden', 'value' => $_ags[1]));
		}
		?>
		</div>
	</td>
</tr>
<tr>
	<td>
		<div style="float:left;width:90px;">
			<b>Start Date:</b>
		</div>
		<div style="float:left;margin-right:20px;">
		<?php
		echo $form->input('Stats.startdate',
			array('label' => '', 'id' => 'datepicker_start', 'style' => 'width:158px;', 'value' => $startdate));
		?>
		</div>
		<div style="float:left;width:90px;">
			<b>End Date:</b>
		</div>
		<div style="float:left;margin-right:20px;">
		<?php
		echo $form->input('Stats.enddate',
			array('label' => '', 'id' => 'datepicker_end', 'style' => 'width:158px', 'value' => $enddate));
		?>
		</div>
		<div style="float:left;width:60px;">
			<b>Period:</b>
		</div>
		<div style="float:left;margin-right:20px;">
		<?php
		echo $form->input('Stats.period',
			array(
				'id' => 'selPeriod',
				'label' => '', 'type' => 'select',
				'options' => $periods,
				'selected' => 0,
				'style' => 'width:190px;',
				'onchange' => 'javascript:__zSetFromTo("selPeriod", "datepicker_start", "datepicker_end");'
			)
		);
		?>
		</div>
	</td>
</tr>
<tr>
	<td>
		<div style="float:left;width:90px;">
			<b>&nbsp;</b>
		</div>
		<div style="float:left;margin-right:20px;">
		<?php
		echo $form->submit('Load Stats',
			array(
				'style' => 'width:160px;',
				'onclick' => 'javascript:if (jQuery("#StatsSiteid").val() == -1) {alert("Please choose a site, or the stats will not be loaded.");return false;} else return true;'
			)
		);
		?>
		</div>
	</td>
</tr>
</table>
<?php
echo $form->end();
?>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery(function() {
		jQuery('#datepicker_start').datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd',
			prevText: 'Previous Month',
			nextText: 'Next Month',
			prevBigText: '<<',
			nextBigText: '>>'
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
			dateFormat: 'yy-mm-dd',
			prevText: 'Previous Month',
			nextText: 'Next Month',
			prevBigText: '<<',
			nextBigText: '>>'
		});
	});
});
</script>
