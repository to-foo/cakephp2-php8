<div class="modalarea examiners index">
	<?php //pr($examiners);?>
	<h2><?php echo __('Examiner workload'); ?></h2>
	<?php echo $this->element('Flash/_messages');?>
	
    <?php
	if(!isset($this->request->projectvars['reportID']) || $this->request->projectvars['reportID'] == 0){
		echo $this->Html->link('Prüf- und Wartezeiten des gesamten Auftrags anzeigen',
					array_merge(array(
						'controller' => 'examiners',
						'action' => 'view'),
						$this->request->projectvars['VarsArray']
						),
					array('class' => 'round mymodal')
					);
	}
	?>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('name', null, array('class'=>'mymodal')); ?></th>
			<th><?php echo $this->Paginator->sort('order_id', __('Order'), array('class'=>'mymodal')); ?></th>
			<th><?php echo $this->Paginator->sort('reportnumber_id', __('Report'), array('class'=>'mymodal')); ?></th>
			<th><?php echo $this->Paginator->sort('waiting_time_start', null, array('class'=>'mymodal')); ?></th>
			<th><?php echo $this->Paginator->sort('waiting_time_end', null, array('class'=>'mymodal')); ?></th>
			<th><?php echo $this->Paginator->sort('testing_time_start', null, array('class'=>'mymodal')); ?></th>
			<th><?php echo $this->Paginator->sort('testing_time_end', null, array('class'=>'mymodal')); ?></th>
			<th><?php echo $this->Paginator->sort('remarks', null, array('class'=>'mymodal')); ?></th>
			<th>&nbsp;</th>
	</tr>
	<?php
	foreach ($examiners as $examiner):

//	$VarsArray[3] = $examiner['Reportnumber']['Report']['id'];
	$VarsArray[5] = $examiner['Reportnumber']['id'];
	$VarsArrayTime = $VarsArray;
	$VarsArray[15] = $examiner['Examiner']['id'];
	$VarsArrayTime[15] = $examiner['ExaminerTime']['id'];

	if(!isset($this->request->projectvars['reportnumberID'])){
		$VarsArray[4] = 0;
		$VarsArray[5] = 0;
	}

	?>
	<tr id="workload_<?php echo $examiner['ExaminerTime']['id']; ?>">
		<td>
		<span class="for_hasmenu1 weldhead">
		<?php echo $this->Html->link(h($examiner['Examiner']['name']),
			array_merge(array('action' => 'view'),
			$VarsArray),
			array(
				'class'=>'round icon_edit mymodal hasmenu1',
				'rev' => implode('/',$VarsArrayTime)
			)
		); ?>
        </span>
        </td>
        <td>
        <span class="discription_mobil">
		<?php echo __('Order No.'); ?>:
		</span>
		<?php echo $examiner['Order']['auftrags_nr']; ?>
        </td>
        <td>
		<?php echo $this->Html->link($this->Pdf->ConstructReportName(array(
				'Reportnumber'=>$examiner['Reportnumber'],
				'Report'=>$examiner['Reportnumber']['Report'],
				'Topproject'=>$examiner['Reportnumber']['Topproject'],
				'Order' => $examiner['Reportnumber']['Order'],
				'Testingcomp'=>$examiner['Reportnumber']['Testingcomp'],
				'Testingmethod'=>$examiner['Reportnumber']['Testingmethod'],
			)),
			array('controller'=>'reportnumbers', 'action'=>'view',
				$examiner['Reportnumber']['topproject_id'],
				$examiner['Reportnumber']['equipment_type_id'],
				$examiner['Reportnumber']['equipment_id'],
				$examiner['Reportnumber']['order_id'],
				$examiner['Reportnumber']['report_id'],
				$examiner['Reportnumber']['id']
				),
			array('class'=>'ajax')
		); ?>
        </td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Waiting time start'); ?>:
		</span>
		<?php echo empty($examiner['ExaminerTime']['waiting_time_start']) ? null : date('d.m.Y H:i:s', strtotime($examiner['ExaminerTime']['waiting_time_start'])); ?></td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Waiting time end'); ?>:
		</span>
		<?php echo empty($examiner['ExaminerTime']['waiting_time_end']) ? null : date('d.m.Y H:i:s', strtotime($examiner['ExaminerTime']['waiting_time_end'])); ?></td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Testing time start'); ?>:
		</span>
		<?php echo empty($examiner['ExaminerTime']['testing_time_start']) ? null : date('d.m.Y H:i:s', strtotime($examiner['ExaminerTime']['testing_time_start'])); ?></td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Testing time end'); ?>:
		</span>
		<?php echo empty($examiner['ExaminerTime']['testing_time_end']) ? null : date('d.m.Y H:i:s', strtotime($examiner['ExaminerTime']['testing_time_end'])); ?></td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Remarks'); ?>:
		</span>
		<?php echo $examiner['ExaminerTime']['remarks']; ?></td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Collisions'); ?>:
		</span>
		<?php if(isset($examiner['ExaminerTime']['collision']) && !empty($examiner['ExaminerTime']['collision'])) {
			$collision = json_decode($examiner['ExaminerTime']['collision']);

			$link = null;
			$msg = array();
			if(isset($collision->waiting_time)) {
				foreach($collision->waiting_time as $report) {
					$msg[] = __(
						'Collision with waiting time of report no. %s, workload %s: %s - %s',
						$report->report,
						$report->workload,
						date('d.m.Y H:i', strtotime($report->start)),
						date('d.m.Y H:i', strtotime($report->end))
					);
					$link = $report->link;
				}
			}
			if(isset($collision->testing_time)) {
				foreach($collision->testing_time as $report) {
					$msg[] = __(
						'Collision with testing time of report no. %s, workload %s: %s - %s',
						$report->report,
						$report->workload,
						date('d.m.Y H:i', strtotime($report->start)),
						date('d.m.Y H:i', strtotime($report->end))
					);
					$link = $report->link;
				}
			}

			if(isset($collision->waiting_time_missing)) {
				$msg[] = __('Missing entry for waiting time');
			}
			if(isset($collision->order_testing_time)) {
				$msg[] = __('End of testing time before start');
			}
			if(isset($collision->order_waiting_time)) {
				$msg[] = __('End of waiting time before start');
			}
			if(isset($collision->order_waiting_time_testing_time)) {
				$msg[] = __('Start of testing time before end of waiting time');
			}
			if(empty($link)) {
				echo $this->Html->image('test-fail-icon.png', array('title'=>join(PHP_EOL, $msg)));
			} else {
				$link = preg_replace('/'.preg_quote($this->request->base,'/').'/', '', $link);
//				echo $this->Html->link($this->Html->image('test-fail-icon.png', array('title'=>join(PHP_EOL, $msg))), $link, array('escape'=>false, 'class'=>'ajax'));
				echo $this->Html->link('test', '/'.trim($link,'/'), array('escape'=>false, 'class'=>'ajax test_fail_icon', 'title' => join('<br />', $msg)));
				echo '<div class="titlecontent_wrapper" style="display:none"><span class="titltecontent">' .  join(PHP_EOL, $msg) . '</span></div>';
			}
		} else {
			echo '&nbsp;';
		}
		?></td>
	</tr>
	<?php if(!empty($examiner['ExaminerTime']['collision'])): ?>
	<script type="text/javascript">

//		$("a.test_fail_icon").tooltip({content:$(this).next("div.titlecontent").html()});

		var collision = <?php echo $examiner['ExaminerTime']['collision']; ?>;
		if(collision['waiting_time']) {
			for(var i in collision['waiting_time']) {
				$('#workload_'+collision['waiting_time'][i]['id']+', #workload_'+collision['waiting_time'][i]['workload']).css('background', '#ebd18b');
			}

			delete collision['waiting_time'];
		}

		if(collision['testing_time']) {
			for(var i in collision['testing_time']) {
				$('#workload_'+collision['testing_time'][i]['id']+', #workload_'+collision['testing_time'][i]['workload']).css('background', '#ffc1b2');
			}

			delete collision['testing_time'];
		}

		for(var i in collision) {
			$('#workload_'+collision[i]).css('background','#ffc1b2');
		}

	</script>
	<?php endif; ?>
<?php endforeach; ?>
	</table>
	<div class="clear"></div>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled mymodal'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled mymodal'));
	?>
	</div>
	<p class="paging_query">
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>

</div>
<script type="text/javascript">
		$("span.for_hasmenu1").contextmenu({
			delegate: ".hasmenu1",
			autoFocus: true,
			preventContextMenuForPopup: true,
			preventSelect: true,
			taphold: true,
			menu: [
				{
				title: "<?php echo __('Edit Workload');?>",
				cmd: "edit",
				action :	function(event, ui) {
							$("#dialog").load("examiners/edit_workload/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
				uiIcon: "qm_edit",
				disabled: <?php echo isset($writeprotection) && $writeprotection ? 'true' : 'false'; ?>
				},
				{
					title: "----"
				},
				{
				title: "<?php echo __('Duplicate Workload');?>",
				cmd: "edit",
				action :	function(event, ui) {
							$("#dialog").load("examiners/duplicate_workload/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
				uiIcon: "qm_duplicate",
				disabled: <?php echo isset($writeprotection) && $writeprotection ? 'true' : 'false'; ?>
				},
				{
					title: "----"
				},
				{
				title: "<?php echo __('Delete Workload');?>",
				cmd: "delete",
				action :	function(event, ui) {
								checkDuplicate = confirm("<?php echo __('Soll dieser Eintrag gelöscht werden?');?>");
								if (checkDuplicate == false) {
									return false;
								}

							$("#dialog").load("examiners/delete_workload/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
				uiIcon: "qm_delete",
				disabled: <?php echo isset($writeprotection) && $writeprotection ? 'true' : 'false'; ?>
				}
				],

			select: function(event, ui) {},
		});

		$("a.test_fail_icon").tooltip({
									content:$(this).next("div.titlecontent_wrapper").html(),
									show: {
										effect: "slideDown",
										duration: 400
									}
								});

</script>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>
