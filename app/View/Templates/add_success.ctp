<div class="modalarea">
<h2><?php echo __('Create template from');?> <?php echo $this->Pdf->ConstructReportName($reportnumber,3) ?></h2>
<?php echo $this->element('Flash/_messages');?>

<?php
if(isset($errors) && count($errors) > 0){
	echo '</div>';
	return;
	}
?>

<?php echo $this->Form->create('Template', array('class' => 'login'));?>
<fieldset>
	<?php echo $this->Html->link(__('Edit template',true), array('controller' => 'templates', 'action' => 'edit',1,$Id), array('class' => 'ajax round')); ?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => $SubmitDescription));?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'TemplateAddForm'));
//echo $this->element('js/ajax_modal_link_global',array('name' => 'a.dropdown'));
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
