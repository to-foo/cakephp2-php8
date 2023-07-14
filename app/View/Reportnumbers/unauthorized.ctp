<div class="quicksearch">
<?php echo $this->element('searching/search_quick_reportnumber', array('action' => 'quickreportsearch','minLength' => 1,'discription' => __('Pr-Nr. (YYYY-NN)')));?>
<?php echo $this->element('barcode_search');?>
</div>
<div class="reportnumbers detail">
<h2><?php echo __('Unautorized access', true);?></h2>
<?php
if (isset($writeprotection) && $writeprotection) {
  echo $this->Html->tag('p', __('Report is writeprotected - changes will not be saved.'), array('class'=>'error'));
  echo $this->Html->tag('p', '&nbsp;');
}
?>
<div class="clear edit">
<div id="refresh_revision_container"></div>
</div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<div class="clear" id="testdiv"></div>

<?php
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/revisions_link');
echo $this->element('js/tooltip_revision');
?>
