<div class="modalarea">
<h2><?php echo __('Error message');?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="error"><p><?php echo $this->Session->flash(); ?></p></div>
</div>
<div class="clear"></div>
<?php
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
