<div class="modalarea">
<h2>
<?php echo __('Infos',true);?>
</h2>
<?php echo $this->element('Flash/_messages');?>
</div>
<p>Hier soll eine Zusammenfassung angezeigt werden.</p>
<div class="clear"></div>
<?php
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
