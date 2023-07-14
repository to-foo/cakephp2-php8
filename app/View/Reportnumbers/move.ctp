<div class="modalarea detail">
<h2><?php echo __('Move report')?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){
echo $this->element('js/close_modal_reload_container',array('FormName' => $FormName));
echo '</div>';
return;
}

if(isset($this->request->data['stop_moving'])){
	echo '</div>';
	return;
}
?>
<?php echo $this->element('reports/move_report_bread_targed');?>
<?php echo $this->Form->create('Reportnumber', array('class' => 'login')); ?>
<fieldset>
<?php
if(isset($this->request->data['CascadeList']) && count($this->request->data['CascadeList']) > 0) {
	echo $this->Form->input('cascade_id', array('type' => 'select','empty' => '','options' => $this->request->data['CascadeList']));
}
echo $this->Form->input('order_id', array('disabled' => 'disabled','type' => 'select','empty' => '','options' => array()));
echo $this->Form->input('order_id_hidden', array('type' => 'hidden','value' => 0));
echo $this->Form->input('report_id', array('disabled' => 'disabled','label' => __('Reportorder'),'type' => 'select','empty' => '','options' => array()));
?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => __('Move',true)));?>
</div>
<?php
echo $this->element('reports/js/move_report_bread_targed');
echo $this->element('js/form_send_modal',array('FormId' => 'ReportnumberMoveForm'));
?>
</div>
