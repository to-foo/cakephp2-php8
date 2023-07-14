<div class="modalarea detail">
<h2><?php echo __('Edit weld label')?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash();?></div>

<?php
echo $this->Form->create(false, array(
    'url' => array_merge(array('controller' => 'reportnumbers', 'action' => 'printweldlabel'),$this->request->projectvars['VarsArray']),
    'id' => 'Reportnumber'
));
?>
<fieldset>
<?php echo $this->Form->textarea('Description',array('label' => __('Description for weld label',true),'value' => $AllDescription));?>
</fieldset>
<?php echo $this->Form->end(__('Submit', true)); ?>

</div>
<script>
$(function() {

});
</script>
<?php 
if(isset($saveOK) && $saveOK  == 1){
	echo $this->JqueryScripte->RefreshAfterDialog($reportnumberID,$evalutionID,$FormName);
	echo $this->JqueryScripte->DialogClose(1.5);
	} 

echo $this->JqueryScripte->ModalFunctions(); 

echo $this->Html->url(array_merge(array('controller' => 'reportnumbers','action' => 'printweldlabel'),$this->request->projectvars['VarsArray']));
?>


