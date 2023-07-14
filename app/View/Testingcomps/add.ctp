<div class="modalarea testingcomps form">
<!-- Beginn Headline -->
<h2><?php echo __('Add testingcomp'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/modal_redirect');
	return;
}
?>
<?php echo $this->Form->create('Testingcomp', array('class' => 'login')); ?>
<fieldset>
<?php
echo $this->Form->input('roll_id');
echo '</fieldset><fieldset>';
echo $this->Form->input('name');
echo $this->Form->input('firmenname');
echo $this->Form->input('firmenzusatz');
echo $this->Form->input('strasse');
echo $this->Form->input('plz');
echo $this->Form->input('ort');
echo $this->Form->input('telefon');
echo $this->Form->input('telefax');
echo $this->Form->input('internet');
echo $this->Form->input('email');
echo $this->Form->input('report_email');

$options = array(0 => 'Intern', 1 => 'Extern');
$attributes = array('value' => 0,'legend' => __('Extern/Intern'));
echo '<div class="radio">';
echo $this->Form->radio('extern', $options,$attributes);
echo '</div>';

echo '</fieldset>';
echo'<fieldset class="multiple_field">';
echo $this->Form->input('Topproject',array('multiple' => true,'label' => __('Involved projects',true)));
echo '</fieldset>';
if(count($developments) > 0) {

	echo'<fieldset class="multiple_field">';
	echo $this->Form->input('Development',array('multiple' => true,'label' => __('Involved developments',true)));
	echo '</fieldset>';
	
}
if(count($testingcompcats) > 0) {

	echo'<fieldset class="multiple_field">';
	echo $this->Form->input('Testingcompcat',array('multiple' => true,'label' => __('Company Type',true)));
	echo'</fieldset>';

}
?>
<?php echo $this->element('form_submit_button',array('action' => 'back','description' => __('Submit',true)));?>
</div>
<?php
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_button_set');
echo $this->element('js/form_send_modal',array('FormId' => 'TestingcompAddForm'));
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
echo $this->element('js/ajax_mymodal_link');
?>
