<div class="quicksearch">
<?php echo $this->element('searching/search_quick_reportnumber',array('action' => 'quickreportsearch','minLength' => 1,'discription' => __('Pr-Nr. (YYYY-NN)')));?>
<?php echo $this->element('barcode_search');?>
</div>
<div class="pagin_links">
</div>
<div class="clear"></div>
<div class="reportnumbers index inhalt">
<?php echo $this->Navigation->showReports($reportsContainer); ?>
</div>
<div class="clear" id="testdiv"></div>

<?php
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
