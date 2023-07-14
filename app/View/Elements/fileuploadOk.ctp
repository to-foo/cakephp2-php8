<div class="actions" id="top-menue">
	<h3><?php __('Actions'); ?></h3>
	<ul><?php echo $this->Navigation->showReports($menues); ?></ul>
	<ul><?php // echo $this->Navigation->showReports($reports); ?></ul>
</div>
<div class="reportnumbers index inhalt">
<h2><?php echo __('Edit order'); ?></h2>
<div class="ok">
<?php echo $this->Form->create('Order'); ?>
<?php
		echo $this->Form->input('id');
		echo $this->Form->input('projektname');
?>
<?php 
foreach($project as $_project) {
//	echo $this->Form->input($_CvsOk['id'],array('label' => $_CvsOk['fieldname'], 'value' => $_CvsOk['value']));
}
?>
<?php echo $this->Form->end(__('Submit', true));?>
<div class="clear"></div>

<?php
//pr($CvsOk); 
?>
</div>
<?php echo $CvsErrors;?>
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>