<div class="modalarea">
<h2>
<?php  
echo __('Welder') . ' ' . 
$certificate_data['Welder']['name'] . ' ' . 
__('add certificate')
;
?>
</h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<div class="hint"><p>
<?php
//echo $this->Html->link(__('Back',true), array_merge(array('action' => 'newcertificate'), $this->request->projectvars['VarsArray']), array('class' => 'round mymodal'));
?>
</p></div>
</div>
<div class="clear"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
