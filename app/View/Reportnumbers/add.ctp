<div class="modalarea detail">
<h2><?php echo __('Add testing report'); ?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/close_modal_reload_container',array('FormName' => $FormName));
	echo '</div>';
	return;
}
?>
</div>
<?php 
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?> 
