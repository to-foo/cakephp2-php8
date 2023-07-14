<div class="modalarea">
	<h2><?php echo __('Settings'); ?></h2>
<ul class="listemax">
	<ul><?php echo $this->Navigation->showNavigationModal($menues); ?></ul>
</ul>
</div>
<div class="clear" id="mytest"></div>
<?php
echo $this->element('js/maximize_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
echo $this->element('js/ajax_mymodal_link');
?>
