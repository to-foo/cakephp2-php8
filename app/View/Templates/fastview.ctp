<div class="modalarea">
<!-- Beginn Headline -->
<h2><?php echo __('Show template'); ?></h2>
<?php echo $this->element('Flash/_messages');?>

<?php echo $this->Form->create('TemplateData',array('class' => 'login','url' => array('controller' => 'templates','action' => 'edit',$this->request->data['Template']['id'])));?>
<?php echo $this->element('templates/show_template_data');?>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => __('Edit',true)));?>
</div>
<?php
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/form_send',array('FormId' => 'TemplateDataEditForm'));
?>
