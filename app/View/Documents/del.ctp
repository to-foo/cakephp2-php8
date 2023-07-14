<div class="modalarea examiners index inhalt">
<h2><?php echo __('Delete document');?></h2>
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

<div class="hint"><p>
<?php echo __('Do you want to delete this document?',true);?>
</p><p><b>
<?php echo $document['Document']['document_type'];?>
<?php echo $document['Document']['name'];?>
<?php echo $document['Document']['producer'];?>
<?php echo $document['Document']['registration_no'];?>
</b>
</p></div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php echo $this->Form->create('Document', array('class' => 'login')); ?>
<?php echo $this->Form->input('id');?>
<?php // echo $this->Form->end(__('Delete', true));?>
<?php echo $this->element('form_submit_button',array('action' => 'back','description' => __('Delete',true)));?>

</div>

<?php
echo $this->element('js/form_send_modal', array('FormId' => 'DocumentsDelForm'));
echo $this->element('js/ajax_modal_link_global',array('name' => 'a.dropdown'));
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/scroll_modal_top');
?>
