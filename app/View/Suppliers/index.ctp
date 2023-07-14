<div class="users index inhalt">
<h2><?php echo __('Expediting'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php echo $this->element('expediting/expediting_legend');?>
<div class="hint subbreadcrump"><?php echo $this->element('expediting/bread_crump');?></div>
<div class="quicksearch"><?php echo $this->element('expediting/quicksearch');?></div>
<div id="container_table_summary">

<?php // pr($this->request->data['Supplier']);?>
<?php echo $this->element('expediting/expediting_table_cascades');?>
<?php echo $this->element('expediting/expediting_table_suppliers');?>
<?php echo $this->element('expediting/expediting_table');?>
<?php // echo $this->element('page_navigation');?>
</div>
</div>

<?php

echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/resize_table_column');
echo $this->element('expediting/table_short_view');

?>
