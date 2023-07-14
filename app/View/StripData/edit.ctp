<?php echo $this->Session->flash(); ?>
<div class="stripData form modalarea">
<h2><?php echo __('Edit strip', true); ?></h2>
<?php echo $this->Form->create('Strip'); ?>
	<fieldset>
	<?php
		echo $this->Form->input('id', array('type'=>'hidden'));
		echo $this->Form->input('description');
		echo $this->Form->input('batch_no');
		echo $this->Form->input('certificate');
		echo $this->Form->input('Sr');
		echo $this->Form->input('Cr');
		echo $this->Form->input('processor_type');
		echo $this->Form->input('processor_f_no');
		echo $this->Form->input('developer_type');
		echo $this->Form->input('developer_temp');
		echo $this->Form->input('developer_replenishment');
		echo $this->Form->input('fixer_type');
		echo $this->Form->input('fixer_temp');
		echo $this->Form->input('fixer_replenishment');
		echo $this->Form->input('examination_object');
		echo $this->Form->input('year');
		echo $this->Form->input('development');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<script type="text/javascript">
$(document).ready(function() {
	$('.modalarea form').on('submit', function() {
		data = $(this).serializeArray();
		data.push({'name': 'ajax_true', 'value':1});
		
		$.ajax({
			'url': $(this).attr('action'),
			'type': 'post',
			'data': data,
			'success': function(data) {
				$('#dialog').html(data);
			}
		});
		return false;
	});
	
	$('#dialog .settingslink a').on('click', function() {
		data = [];
		data.push({'name': 'ajax_true', 'value':1});
		data.push({'name': 'id', 'value': <?php echo $strip_id; ?>});
		
		$.ajax({
			'url': $(this).attr('href'),
			'type': 'post',
			'data': data,
			'success': function(data) {
				$('#dialog').html(data);
			}
		});
		return false;
	});
});
</script>
<?php echo $this->element('fast_save_report_js');?>
<?php //echo $this->JqueryScripte->ModalFunctions(); ?>
<?php if(isset($afterEdit)) echo $afterEdit; ?>