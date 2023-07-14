<?php echo $this->Form->create('Examiner', array('class' => 'dialogform')); ?>
<fieldset>
<?php echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'Examiner');?>
</fieldset>
<p>
<?php
$this->request->projectvars['VarsArray'][16] = $certificate_id;
echo $this->Html->link(__('Back',true), array_merge(array('action' => 'certificate'), $this->request->projectvars['VarsArray']), array('class' => 'round mymodal'));
?>
</p>
<?php echo $this->Form->end(); ?>
</div>
<?php echo $this->element('fast_save_examiner_js');?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
