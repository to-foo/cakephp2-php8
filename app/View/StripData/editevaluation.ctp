<?php echo $this->Session->flash(); ?>

<div class="stripEvaluation form modalarea">
<h2><?php echo __('Edit strip evaluation', true); ?></h2>
<?php echo $this->Form->create('StripEvaluation'); ?>
	<fieldset>
	<?php
		echo $this->Form->input('id', array('type'=>'hidden'));
		echo $this->Form->input('date', array('type'=>'string', 'rel'=>'date'));
		echo $this->Form->input('D0', array('type'=>'string'));
		echo $this->Form->input('Dx', array('type'=>'string'));
		echo $this->Form->input('Dx4', array('type'=>'string'));
		echo $this->Form->input('developer_fresh', array('type'=>'checkbox'));
		echo $this->Form->input('fixer_fresh', array('type'=>'checkbox'));
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
		if($(this).attr('id') == 'closethismodal') {
			$('#dialog').dialog('close');
			return false;
		}

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

	$('.input.checkbox input').button();
	$('.input input[rel="date"]').datetimepicker({ format: "Y-m-d", timepicker:false, lang:"de", scrollInput: false});
});
</script>
<?php // echo $this->JqueryScripte->ModalFunctions(); ?>
<?php if(isset($afterEdit)) echo $afterEdit; ?>
