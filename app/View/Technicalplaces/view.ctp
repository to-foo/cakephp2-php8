<div class="modalarea">
<h2><?php echo $headline;?></h2>
	<div class="related accordion">
	<?php echo $this->ViewData->ShowOrderData($orders,$settings,$locale); ?>
	<?php echo $this->ViewData->ShowOrderEvaluationData($testingreports); ?>
	</div>
	<div class="clear" id="testdiv"></div>
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>