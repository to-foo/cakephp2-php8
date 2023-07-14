<div class="modalarea">
<h2><?php echo __('Duplicate report');// pr($parts);?></h2>
<?php if($dataOld['Reportnumber']['version'] != $dataOld['Testingmethod']['version'] && Configure::check('OverwriteReportVersionTest') == false):?>
<div class="error">
<p>
<?php echo __('Die Version des zu kopierenden Prüfberichtes ist veraltet und kann nicht mehr dupliziert werden.',true);?>
</p>
</div>
<?php return; endif;?>

<?php if(isset($dataOld['Reportnumber']['revision_write']) && $dataOld['Reportnumber']['revision_write'] == 1):?>
<div class="error">
<p>
<?php echo __('Diese Prüfbericht befindet sich in einer Revision und kann im Moment nicht dubliziert werden.',true);?>
</p>
</div>
<?php
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
<?php return; endif;?>
<div class="hint">
<p>
<?php echo __('Soll folgender Prüfbericht dupliziert werden:') . ' ' . $dataOld['Reportnumber']['number'].'/'.$dataOld['Reportnumber']['year'];?>
</p>
<p>
<?php echo __('Sie haben die Möglichkeit Daten, welche nicht dupliziert werden sollen, abzuwählen.');?>
</p>
</div>
<?php
if(isset($saveOK) && $saveOK  == 1){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo '</div>';
	return;
	}

echo $this->Form->create('Reportnumber', array('class' => 'login'));
echo '<fieldset>';

foreach($parts as $_key => $_parts){

	$disable = array();

	if(Configure::check('StopDuplicate.'.$_parts) && Configure::read('StopDuplicate.'.$_parts) == true){
		 $disable = array('disabled' => 'disabled');
	}

	echo $this->Form->input($_parts,array($disable, 'type' => 'radio','value' => 1,'options' => array('1' => __('yes'),0 => __('no'))));
}
echo '</fieldset>';
echo $this->element('form_submit_button',array('action' => 'close','description' => __('Duplizieren',true)));
?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'ReportnumberDuplicatForm'));
echo $this->element('js/form_button_set');
?>
