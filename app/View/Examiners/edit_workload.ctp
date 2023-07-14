<div class="modalarea examiners">
<h2><?php echo __('Edit examiner workload'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php echo $this->Form->create('ExaminerTime', array('class' => 'dialogform')); ?>
	<fieldset>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('examiner_id', array('options'=>$examiners));
		echo '</fieldset><fieldset>';
		$msg = array();
		if(isset($collision['waiting_time'])) {
			foreach($collision['waiting_time'] as $report) {
				$msg[] = $this->Html->Tag(
				'div',
				__(
					'Collision with waiting time of report no. %s, workload %s: %s - %s',
					$report['Reportnumber']['number'],
					$report['ExaminerTime']['id'],
					date('d.m.Y H:i', strtotime($report['ExaminerTime']['waiting_time_start'])),
					date('d.m.Y H:i', strtotime($report['ExaminerTime']['waiting_time_end']))
				),
				array('class' => 'error'));
			}
		}
		if(isset($collision['waiting_time_missing'])) {
			$msg[] = $this->Html->Tag('div', __('Missing entry for waiting time'), array('class'=>'error'));
		}
		if(isset($collision['order_waiting_time'])) {
			$msg[] = $this->Html->Tag('div', __('End of waiting time before start'), array('class' => 'error'));
		}
		if(isset($collision['order_waiting_time_testing_time'])) {
			$msg[] = $this->Html->Tag('div', __('Start of testing time before end of waiting time'), array('class' => 'error'));
		}
		if(!empty($msg)) echo $this->Html->tag('div', join(PHP_EOL, $msg), array('class'=>'inhalt'));
		echo '</fieldset><fieldset>';
		echo $this->Form->input('waiting_time_start', array('class'=>'datetime', 'type'=>'text'));
		echo $this->Form->input('waiting_time_end', array('class'=>'datetime', 'type'=>'text'));

		$msg = array();
		if(isset($collision['testing_time'])) {
			foreach($collision['testing_time'] as $report) {
				$msg[] = $this->Html->Tag(
				'div',
				__(
					'Collision with testing time of report no. %s, workload %s: %s - %s',
					$report['Reportnumber']['number'],
					$report['ExaminerTime']['id'],
					date('d.m.Y H:i', strtotime($report['ExaminerTime']['testing_time_start'])),
					date('d.m.Y H:i', strtotime($report['ExaminerTime']['testing_time_end']))
				),
				array('class' => 'error'));
			}
		}
		if(isset($collision['order_testing_time'])) {
			$msg[] = $this->Html->Tag('div', __('End of testing time before start'), array('class' => 'error'));
		}
		if(!empty($msg)) echo $this->Html->tag('div', join(PHP_EOL, $msg), array('class'=>'inhalt'));
		echo '</fieldset><fieldset>';
		echo $this->Form->input('testing_time_start', array('class'=>'datetime', 'type'=>'text'));
		echo $this->Form->input('testing_time_end', array('class'=>'datetime', 'type'=>'text'));
		echo '</fieldset><fieldset>';
		echo $this->Form->input('remarks');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<script type="text/javascript">
	$(document).ready(function(e) {
		$(".datetime").datetimepicker({
			lang:"de",
			format: "Y-m-d H:i"
		});
	});
</script>
<?php if(isset($afterEDIT)){echo $afterEDIT;} ?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
<?php if(isset($ask_force)) {echo $this->JqueryScripte->AskForceSave('ExaminerTimeEditWorkloadForm');} ?>
