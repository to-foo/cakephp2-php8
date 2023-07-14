<div class="actions" id="top-menue">
	<h3><?php __('Actions'); ?></h3>
	<ul><?php echo $this->Navigation->showNavigation($menues); ?></ul>
</div>
<div class="orders form inhalt">
<div>
<div class="error">
<div id="message">
<?php echo $ErrorResult;?>
<p>
<?php echo $this->Html->link(__('Open the newly created order'), array('action' => 'edit', $insertId, $projectID), array('class'=>'ajax')); ?>
</p>
</div>
</div>
</div>
</div>
<div class="clear" id="testdiv"></div>
<?php if(isset($afterEDIT)){echo $afterEDIT;} ?>
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>