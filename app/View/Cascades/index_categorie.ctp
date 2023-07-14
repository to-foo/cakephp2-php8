<div class="quicksearch">
<?php echo $this->element('searching/search_quick_reportnumber',array('action' => 'quickreportsearch','minLength' => 1,'discription' => __('Pr-Nr. (YYYY-NN)')));?>
<?php echo $this->element('barcode_search');?>
</div>

<div class="index inhalt">
<h2><?php echo $Cascade['current']['Cascade']['discription']; ?></h2>
<div class="reportnumbers index inhalt">
<?php echo $this->element('cascades/cascades_categorien');?>
</div>
</div>

<?php
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/ajax_paging');
?>
