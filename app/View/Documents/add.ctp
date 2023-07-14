<div class="modalarea examiners index inhalt">
	<h2><?php echo __('Add document');?></h2>
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

<?php echo $this->Form->create('Document', array('class' => 'login')); ?>
	<fieldset>
	<?php
	//	echo $this->ViewData->EditModulData($this->request->data,$settings,$locale,array('testingcomp_id'=>$testingcomps,'Testingmethod'=>$testingmethods),'Document');

	?>
	<?php echo $this->Form->input('DocumentTestingmethod',array('label' => __('Kategorie'),'multiple' => false,'options' => $testingmethods));?>

	<?php echo $this->element('form/modulform',array('data' => $this->request->data,'setting' => $settings,'lang' => $locale,'step' => 'Document','testingmethods' => false));?>

	</fieldset>





<?php echo $this->element('form_submit_button',array('action' => 'reset','description' => __('Submit',true)));?>

</div>
<script>
//$(document).ready(function(){
 //   $('#DropdownDropdown').multiSelect();
 //   $('#DropdownDropdown').multiSelect({ selectableOptgroup: true });
//});


</script>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'DocumentAddForm'));
echo $this->element('js/ajax_modal_link_global',array('name' => 'a.dropdown'));
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/scroll_modal_top');
?>
