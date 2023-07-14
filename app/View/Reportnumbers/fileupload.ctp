<?php // pr($order);?>
<div class="actions" id="top-menue">
	<h3><?php __('Actions'); ?></h3>
	<ul><?php echo $this->Navigation->showReports($menues); ?></ul>
	<ul><?php // echo $this->Navigation->showReports($reports); ?></ul>
</div>
<div class="reportnumbers index inhalt">
	<h2><?php echo __('Order'); ?></h2>
<?php echo $this->Form->create('Order', array('type' => 'file')); ?>
	<fieldset>

<?php
echo $this->Form->input('file', array(
'type' => 'file',
'label' => false, 'div' => false,
'class' => 'fileUpload',
'multiple' => 'multiple'
));	
?>
	</fieldset>
<?php
echo $this->Form->button('Upload', array('type' => 'submit', 'id' => 'px-submit'));
echo $this->Form->button('Clear', array('type' => 'reset', 'id' => 'px-clear'));
echo $form->end();
?>
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>
 
<script type="text/javascript">
$(function(){
$('.fileUpload').fileUploader();
});
</script>