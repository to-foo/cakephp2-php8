<div class="modalarea">
<h2>
<?php
echo __('Examiner') . ' ';
if(isset($certificate_data['Examiner']['name'])) echo $certificate_data['Examiner']['name'];
echo' ' .  __('edit qualification');
?>
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
<?php echo $this->Form->create('Certificate', array('class' => 'login')); ?>
<fieldset>
<?php
echo $this->Form->input(
	 'testingmethod_id',
	 array(
		 'label' => __('testingmethod', true),
		 'empty' => ' ',
		 'multiple' => false,
		 'options' => $testingmethods,
		 'selected' => $this->request->data['Certificate']['testingmethod_id']
	 )
 );
?>
</fieldset>
<fieldset class="multiple_field">
	<?php
	echo $this->Form->input('Testingmethod',array(
		'label' => __('Testingmethods',true),
		'empty' => ' ',
		'options' => $testingmethods,
		'selected' => $this->request->data['CertificatesTestingmethodes']
		)
	);
	?>
</fieldset>
<fieldset>
<?php echo $this->element('form/modulform',array('data' => $this->request->data,'setting' => $settings,'lang' => $locale,'step' => 'Certificate','testingmethods' => false));?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => __('Submit',true)));?>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'examiners','action' => 'qualification'), $this->request->projectvars['VarsArray']));

?>
</div>
<div class="clear"></div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'CertificateQualificationForm'));
echo $this->element('js/form_certifcate_yes_no',array('url' => $url));
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');

$form = '#CertificateQualificationForm';
echo $this->JqueryScripte->SessionFormData($form);
?>
