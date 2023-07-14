<div class="actions" id="top-menue">
	<h3><?php __('Actions'); ?></h3>
	<ul><?php echo $this->Navigation->showReports($menues); ?></ul>
	<ul><?php echo $this->Navigation->showReports($reports); ?></ul>
</div>
<div class="reportnumbers index inhalt">
	<h2></h2>

</div>
<div class="clear" id="testdiv"></div>
<?php if(isset($afterEDIT)){echo $afterEDIT;} ?>
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>