<div class="users index inhalt">
<h2><?php echo __('Statistic'); ?> > <?php echo $HeadLine;?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php echo $this->element('expediting/expediting_legend');?>
<div class="hint subbreadcrump"><?php echo $this->element('expediting/bread_crump');?></div>
<div id="container_table_summary" class="current_content" >
<?php
echo $this->element('expediting/expediting_legend');
echo $this->element('expediting/expediting_load_statistik');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
</div>
</div>
