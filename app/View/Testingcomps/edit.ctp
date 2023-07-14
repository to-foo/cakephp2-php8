<div class="modalarea testingcomps form">
<!-- Beginn Headline -->
<h2><?php echo __('Edit testingcomp'); ?></h2>
<!-- Ende Headline -->
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
echo $this->Form->input('id');
echo $this->Form->input('roll_id');
echo '</fieldset><fieldset>';
echo $this->Form->input('name');

// Alle Verf�gbaren Logos holen
foreach($logos as $id=>$elem) {
$img = imagecreatefromstring(file_get_contents($elem));
$active = trim($elem) == trim($this->request->data['Testingcomp']['logopfad']);

ob_start();
if(is_resource($img)) {
	imagepng($img);
}
$logos[$id] = '<input type="image" src="data:image/png;base64,'.base64_encode(ob_get_contents()).'" class="logoUpload'.($active ? ' active' : '').'" />';
ob_end_clean();
}

// Logo von Pfad in Datenbank erzeugen
if(isset($this->request->data['Testingcomp']['logopfad']) && is_file($this->request->data['Testingcomp']['logopfad']) && is_readable($this->request->data['Testingcomp']['logopfad'])){
$img = imagecreatefromstring(file_get_contents($this->request->data['Testingcomp']['logopfad']));
ob_start();
imagepng($img);
$logo = '<input type="image" src="data:image/png;base64,'.base64_encode(ob_get_contents()).'" class="logoUpload" />';
ob_end_clean();
}

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
echo $this->Form->input('extern');

$options = array(0 => 'Intern', 1 => 'Extern');
$attributes = array('legend' => __('Extern/Intern'));
echo '<div class="radio">';
echo $this->Form->radio('extern', $options,$attributes);
echo '</div>';

echo '</fieldset>';
echo'<fieldset class="multiple_field">';
echo $this->Form->input('Topproject',array('multiple' => true,'label' => __('Involved projects',true),'selected' => $this->request->data['Topproject']['selected']));
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
echo $this->element('js/form_send_modal',array('FormId' => 'TestingcompEditForm'));
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
echo $this->element('js/ajax_mymodal_link');
?>
