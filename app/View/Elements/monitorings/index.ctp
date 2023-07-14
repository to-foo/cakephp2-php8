<div class="inhalt">
<h2><?php echo __('Monitoring manager',true); ?></h2>
<div class="quicksearch">
<div class="clear"></div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
</div>
<div class="monitoring hint">
<?php
$RefreshUrl = $this->Html->url(array_merge(array('action' => 'json_scheme'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('RefreshUrl',array('type' => 'hidden','value' => $RefreshUrl));
$RefreshTime = 10000;
echo $this->Form->input('RefreshTime',array('type' => 'hidden','value' => $RefreshTime));
?>
<div class="monitoring_content monitoring_diagrammcontent">
<?php echo $this->element('monitorings/diagramm/odometer');?>
<?php // echo $this->element('monitorings/diagramm/line_plot');?>
<?php echo $this->element('monitorings/diagramm/slideshow');?>
</div>

<?php echo $this->element('monitorings/info');?>
</div>
</div>
<?php
echo $this->element('monitorings/js/refresh_diagramm');
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/resize_table_column');
?>
