	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo __('F-number'); ?></th>
			<th><?php echo $this->Paginator->sort('order_id', __('Order'), array('class'=>'mymodal', 'linktarget'=>'#workload_data', 'data-day'=>json_encode($this->request->data['day']), 'data-examiner'=>$this->request->data['examiner'])); ?></th>
			<th><?php echo $this->Paginator->sort('kks', __('KKS'), array('class'=>'mymodal', 'linktarget'=>'#workload_data', 'data-day'=>json_encode($this->request->data['day']), 'data-examiner'=>$this->request->data['examiner'])); ?></th>
			<th><?php echo $this->Paginator->sort('reportnumber_id', __('Report'), array('class'=>'mymodal', 'linktarget'=>'#workload_data', 'data-day'=>json_encode($this->request->data['day']), 'data-examiner'=>$this->request->data['examiner'])); ?></th>
			<th><?php echo $this->Paginator->sort('waiting_time_start', null, array('class'=>'mymodal', 'linktarget'=>'#workload_data', 'data-day'=>json_encode($this->request->data['day']), 'data-examiner'=>$this->request->data['examiner'])); ?></th>
			<th><?php echo $this->Paginator->sort('waiting_time_end', null, array('class'=>'mymodal', 'linktarget'=>'#workload_data', 'data-day'=>json_encode($this->request->data['day']), 'data-examiner'=>$this->request->data['examiner'])); ?></th>
			<th><?php echo $this->Paginator->sort('testing_time_start', null, array('class'=>'mymodal', 'linktarget'=>'#workload_data', 'data-day'=>json_encode($this->request->data['day']), 'data-examiner'=>$this->request->data['examiner'])); ?></th>
			<th><?php echo $this->Paginator->sort('testing_time_end', null, array('class'=>'mymodal', 'linktarget'=>'#workload_data', 'data-day'=>json_encode($this->request->data['day']), 'data-examiner'=>$this->request->data['examiner'])); ?></th>
			<th><?php echo $this->Paginator->sort('remarks', null, array('class'=>'mymodal', 'linktarget'=>'#workload_data', 'data-day'=>json_encode($this->request->data['day']), 'data-examiner'=>$this->request->data['examiner'])); ?></th>
			<th>&nbsp;</th>
	</tr>
	<?php
	foreach ($examiners as $examiner):

	$this->request->projectvars['VarsArray'][4] = $examiner['Reportnumber']['report_id'];
	$this->request->projectvars['VarsArray'][5] = $examiner['Reportnumber']['id'];

	?>
	<tr id="workload_<?php echo $examiner['ExaminerTime']['id']; ?>">
	<?php
		$VarsArray[0] = $examiner['Reportnumber']['topproject_id'];
		$VarsArray[1] = 1;
		$VarsArray[2] = $examiner['Reportnumber']['order_id'];
		$VarsArray[3] = $examiner['Reportnumber']['report_id'];
		$VarsArray[4] = $examiner['Reportnumber']['id'];
	?>
		<td><?php echo $examiner['Examiner']['f']; ?></td>
		<td><?php echo $examiner['Order']['auftrags_nr']; ?></td>
		<td><?php echo $examiner['Order']['kks']; ?></td>
		<td><?php
		echo $this->Html->link(
			$this->Pdf->ConstructReportName(array(
				'Reportnumber'=>$examiner['Reportnumber'],
				'Report'=>$examiner['Reportnumber']['Report'],
				'Topproject'=>$examiner['Reportnumber']['Topproject'],
				'Order' => $examiner['Reportnumber']['Order'],
				'Testingcomp'=>$examiner['Reportnumber']['Testingcomp'],
				'Testingmethod'=>$examiner['Reportnumber']['Testingmethod'],
			)),
			array_merge(array('controller'=>'reportnumbers', 'action'=>'view'), $this->request->projectvars['VarsArray']),
			array('class'=>'ajax')
		); ?></td>
		<td><?php echo empty($examiner['ExaminerTime']['waiting_time_start']) ? null : date('d.m.Y H:i:s', strtotime($examiner['ExaminerTime']['waiting_time_start'])); ?></td>
		<td><?php echo empty($examiner['ExaminerTime']['waiting_time_end']) ? null : date('d.m.Y H:i:s', strtotime($examiner['ExaminerTime']['waiting_time_end'])); ?></td>
		<td><?php echo empty($examiner['ExaminerTime']['testing_time_start']) ? null : date('d.m.Y H:i:s', strtotime($examiner['ExaminerTime']['testing_time_start'])); ?></td>
		<td><?php echo empty($examiner['ExaminerTime']['testing_time_end']) ? null : date('d.m.Y H:i:s', strtotime($examiner['ExaminerTime']['testing_time_end'])); ?></td>
		<td><?php echo $examiner['ExaminerTime']['remarks']; ?></td>
		<td><?php if(isset($examiner['ExaminerTime']['collision']) && !empty($examiner['ExaminerTime']['collision'])) {
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
				echo $this->Html->link($this->Html->image('test-fail-icon.png', array('title'=>join(PHP_EOL, $msg))), $link, array('escape'=>false, 'class'=>'ajax'));
			}
		} else {
			echo '&nbsp;';
		}
		?></td>
<!--
		<td class="actions">
			<?php
				/*
				echo $this->Html->link(__('Delete'), array_merge(array('action' => 'delete_workload'), $VarsArray, array($examiner['ExaminerTime']['id'])), array('class' => 'right delete_generally mymodal', 'title' => __('Are you sure you want to delete this entry?')));
				echo $this->Html->link(__('Edit'), array_merge(array('action' => 'edit_workload'), $VarsArray, array($examiner['ExaminerTime']['id'])), array('class' => 'left mymodal'));
				*/
			?>
		</td>
-->
	</tr>
	<?php if(!empty($examiner['ExaminerTime']['collision'])): ?>
	<script type="text/javascript">
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
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
