<div class="modalarea">
<h2>
<?php echo __('Examiners'); ?> - <?php echo $examiner['Examiner']['name'];?>
<?php echo ' - ';?>
<?php echo __('add monitoring',true);?>
</h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}
?>

<?php echo $this->Form->create('ExaminerMonitoring', array('class' => 'login')); ?>
	<fieldset>
	<?php echo $this->element('form/modulform',array('data' => $this->request->data,'setting' => $settings,'lang' => $locale,'step' => 'Device','testingmethods' => false));?>
	</fieldset>
<?php
//echo $this->Form->end(__('Submit', true));
echo $this->element('form_submit_button',array('action' => 'reset','description' => __('Submit',true)));
?>

</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'ExaminerMonitoringAddmonitoringForm'));
echo $this->element('js/ajax_modal_link_global',array('name' => 'a.dropdown'));
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');

$form = '#ExaminerAddmonitoringForm';
//echo $this->JqueryScripte->SessionFormData($form);
?>
