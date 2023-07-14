<div class="modalarea">
<h2><?php echo __('Delete image'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($this->request->data['Reportnumber']) && $this->request->data['Reportnumber']['status'] > 0 && $this->request->data['Reportnumber']['revision_progress'] == 0) {

	echo $this->element('js/close_modal');
	echo $this->element('js/minimize_modal');
	echo $this->element('js/maximize_modal');
	echo '</div>';
	return;

}
?>
<?php
echo $this->element('js/ajax_stop_loader');

if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}
?>

<div class="hint">
<p>
<?php
echo '<img src="' . $reportimages['Reportimage']['imagedata'] . '" height="250px" alt="' . $reportimages['Reportimage']['discription'] . '">';
?>
</p>
</div>
<?php echo $this->Form->create('Reportimage',array('class' => 'login')); ?>
<fieldset>
<?php
echo $this->Form->input('id', array('value' => $reportimages['Reportimage']['id']));
echo $this->Form->input('delete_image', array('type' => 'hidden','value' => 1));
?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => __('Delete')));?>
<?php
$attribut_disabled = false;

if($reportnumbers['Reportnumber']['status'] > 0) $attribut_disabled = true;
if(isset($reportnumbers['Reportnumber']['revision_write']) && $reportnumbers['Reportnumber']['revision_write'] == 1) $attribut_disabled = false;

if($attribut_disabled == true){
	echo '
	<script type="text/javascript">
	$(document).ready(function(){
		$("input").attr("disabled","disabled");
	});
	</script>
	';
}
?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'ReportimageImagediscriptionForm'));
?>
