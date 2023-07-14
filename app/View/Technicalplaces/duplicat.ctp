<div class="modalarea">
<h2><?php echo __('duplicate order');// pr($parts);?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if(isset($StopAdd) && $StopAdd === true){
	echo $this->JqueryScripte->DialogClose();
	echo $this->JqueryScripte->ModalFunctions();
	echo '</div>';
	return;
	} 

if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName,2000);
	echo $this->JqueryScripte->DialogClose();
	echo $this->JqueryScripte->ModalFunctions();
	echo '</div>';
	return;
	}
?>
<?php echo '<div class="hint"><p>' . __('Order',true) . ' ' . $this->request->data['Order']['auftrags_nr'] . ' ' .  __('duplicating',true) . '?</p></div>';?>
<?php echo $this->Form->create('Order', array('class' => 'dialogform')); ?>
<?php echo $this->Form->input('id');?>
<fieldset>
<?php echo $this->Form->input('auftrags_nr',array('label' => __('New name for',true) . ' ' . trim($xml_order->Order->auftrags_nr->discription->$locale)));?>
</fieldset>
<?php echo $this->Form->end(__('Duplicate')); ?>

</div>
<?php echo $this->JqueryScripte->ModalFunctions();
