<h3><?php echo __('Expediting'); ?> > <?php echo $HeadLine;?></h3>

<?php echo $this->element('Flash/_messages');?>
<?php // echo $this->element('expediting/expediting_legend');?>

<div class="users index inhalt">
<?php echo $this->element('expediting/expediting_table_landingpage_index');?>
<?php echo $this->element('page_navigation');?>
</div>

<?php
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/resize_table_column');
echo $this->element('expediting/table_short_view');

?>
