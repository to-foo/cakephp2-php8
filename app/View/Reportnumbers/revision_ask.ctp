<div class="modalarea">
<h2><?php echo __('Revision');?></h2>
<div class="hint"><p>
<?php echo $description['text'];?>
</p></div>
<?php echo $this->Form->create('Reportnumber', array('class' => 'dialogform')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
</fieldset>
<?php echo $this->Form->end($description['submit']);?>
</div>
<script type="text/javascript">
	$(document).ready(function(){
	});
</script>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'ReportnumberRevisionForm'));
echo $this->element('js/ajax_modal_link_global',array('name' => 'a.dropdown'));
echo $this->element('js/form_button_set');
?>
