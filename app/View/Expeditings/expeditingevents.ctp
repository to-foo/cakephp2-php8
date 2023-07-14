<div class="modalarea detail">
<h2><?php echo __('Expeditin events') . ' ' . $this->request->data['HeadLine'] . ' > ' . $this->request->data['Expediting']['description'];?> </h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}

echo $this->element('expediting/expediting_event');

?>
</div>