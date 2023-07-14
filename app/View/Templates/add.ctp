<div class="modalarea">
<h2><?php echo __('Create template from');?> <?php echo $this->Pdf->ConstructReportName($reportnumber,3) ?></h2>
<?php echo $this->element('Flash/_messages');?>

<?php

if(isset($errors) && count($errors) > 0){

	echo $this->element('reports/printing_show_errors',array('errors' => $errors));
	echo $this->element('js/testreport_erros_skip',array('errors' => $errors));

	echo '</div>';
	return;
	}

?>

<?php echo $this->Form->create('Template', array('class' => 'login'));?>
<fieldset>
<?php
foreach($parts as $_key => $_parts){

	$disable = array();

	echo $this->Form->input($_parts,array($disable, 'type' => 'radio','value' => 1,'options' => array('1' => __('yes'),0 => __('no'))));
}
?>
</fieldset>
<fieldset>
<?php
echo $this->Form->input('name');
echo $this->Form->input('description');
echo $this->Form->input('delete',array(
		'type' => 'radio',
		'options' => array(0 => __('no'),1 => __('yes')),
		'value' => 0,
		'legend' => __('Soll der Prüfbericht im Anschluss gelöscht werden?')
	)
);
?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => $SubmitDescription));?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'TemplateAddForm'));
echo $this->element('js/form_button_set');
echo $this->element('js/ajax_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
