<div class="modalarea">
<h2><?php echo __('Select evaluation template');?></h2>
<?php echo $this->element('Flash/_messages');?>

<?php
if(isset($errors) && count($errors) > 0){
	echo '</div>';
	return;
	}
?>
<?php echo $this->element('templates/evaluation_template_data_get');?>

</div>
<?php
/*
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
*/
?>
