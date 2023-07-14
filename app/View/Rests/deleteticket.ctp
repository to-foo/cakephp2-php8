<div class="modalarea tickets form">
<h2><?php echo __('Delete ticket'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
  if (isset($FormName) && count($FormName) > 0) {
      echo $this->element("js/reload_container", [
          "FormName" => $FormName,
      ]);
      echo $this->element("js/ajax_stop_loader");
      echo $this->element("js/close_modal_auto");
      echo "</div>";
      return;
  }
?>
<div class="hint">
<?php echo $this->element('rest/spool_infos');?>
</div>
<?php echo $this->element('rest/testingreport_infos',array('type' => 'OpenReports'));?>
<?php echo $this->Form->create('Ticket', array('class' => 'login')); ?>
<?php echo $this->Form->input('id'); ?>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => __('Delete ticket')));?>
</div>
<?php
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/ajax_modal_request');
echo $this->element('js/form_button_set');
?>
