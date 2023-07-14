<div class="modalarea">
<h2><?php echo __('Create report') . ' ' . $this->request->data['Ticket']['id'] . ' - ' . $this->request->data['Ticket']['created']; ?></h2>

<?php echo $this->element('Flash/_messages');?>

<?php

  if (isset($FormName) && count($FormName) > 0) {
      echo $this->element("rest/js/reload_container", ["FormName" => $FormName,]);
      echo $this->element("js/ajax_stop_loader");
      echo $this->element("js/close_modal_auto");
      echo "</div>";
      return;
  }
?>
<div class="hint">
<?php echo $this->Html->link(__('Edit ticket', true),array_merge(array('action' => 'editticket'),$this->request->projectvars['VarsArray']),array('title' => __('Edit ticket', true),'class' => 'round  mymodal'));?>
</div>

<div class="hint">
<div class=" flex_info">
<?php
if(isset($this->request->data['Ticket']['technical_place'])){
	echo $this->element('infos/flex_info',array('value' => $this->request->data['Ticket']['technical_place'],'label' => __('Techical Place',true)));
	echo $this->element('infos/flex_info',array('value' => $this->request->data['Ticket']['count_welds'],'label' => __('Count Welds',true)));
	echo $this->element('infos/flex_info',array('value' => $this->request->data['Ticket']['spools'],'label' => __('Spools',true)));
	echo $this->element('infos/flex_info',array('value' => $this->request->data['Ticket']['created'],'label' => __('Created',true)));
}
?>
</div>

<?php echo $this->element('rest/spool_infos');?>
</div>
<?php echo $this->element('rest/testingreport_infos',array('type' => 'OpenReports'));?>
<?php echo $this->Form->create('Ticket', array('class' => 'login')); ?>
<?php echo $this->Form->input('id'); ?>
<?php
echo '<fieldset class="multiple_field">';
echo $this->Form->input('reportwelds',array('multiple' => true,'label' => __('Welds',true),'options' => $report_testingmethods));
echo '</fieldset>';
?>
<?php echo $this->element('form_submit_button', array('action' => 'close','description' => __('Save',true)));?>

</div>
<div class="clear" id="testdiv"></div>

<?php
echo $this->element('js/form_send_modal', array('FormId' => 'TicketCreatereportForm'));
echo $this->element('js/form_button_set');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/show_pdf_link');
echo $this->element('rest/js/edit_testingcomp');
echo $this->element('js/json_request_animation');
?>
