<div class="modalarea">
<h2><?php echo __('Examiner') . ' ' . $certificate_data['Examiner']['name'] ;?></h2>
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


<?php echo $this->Form->create('Eyecheck', array('class' => 'dialogform')); ?>
<?php echo $this->Form->id('id',array('type' => 'hidden')); ?>
<?php echo $this->Form->end($del_button);?>
</div>
<div class="clear"></div>

<?php
echo $this->element('js/ajax_send_modal_form');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
