<div class="modalarea">
<h2><?php echo __('Edit discription file') ?></h2>
<?php echo $this->element('Flash/_messages');?>

<div id="image_wrapper">
<?php echo $this->Form->create('Reportfile', array('class' => 'login')); ?>

    </fieldset><fieldset>
	<?php
		echo $this->Form->input('id', array('value' => $reportfiles['Reportfile']['id']));
		echo $this->Form->input('Reportfile.discription', array('value' => $reportfiles['Reportfile']['discription']));
                echo $this->Form->input('Reportfile.attachment', array('options'=> array(__('no'),__('yes')),'selected' => $reportfiles['Reportfile']['attachment']));
	?>
    </fieldset><fieldset>
	<?php echo $this->Form->input(__('Save', true),array('type' => 'submit','value' => __('Save', true),'label' => false,'div' => false)); ?>
	</fieldset>
<?php echo $this->Form->end(); ?>
</div><a href=""
<div id="message_wrapper"><?php // echo $this->Session->flash();?> </div>
<?php
$attribut_disabled = false;

if(isset($reportnumbers['Reportnumber']['status']) && $reportnumbers['Reportnumber']['status'] > 0) $attribut_disabled = true;
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
<hr class="clear dotted" />
</div>

<?php echo $this->element('js/form_send_modal',array('FormId' => 'ReportfileFilediscriptionForm'));
echo $this->element('js/ajax_modal_link_global',array('name' => 'a.dropdown'));
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/scroll_modal_top');?>
