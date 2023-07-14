<div class="modalarea">
<?php ;?>

<h2><?php  echo __('DeviceTestingmethod details') . ' ' . $devicetestingmethod['DeviceTestingmethod']['verfahren'];?></h2>
<div class="quicksearch">
</div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<div class="current_content hide_infos_div">
<?php echo $this->ViewData->ShowDataList($arrayData,$locale,'DeviceTestingmethod','ul');?>
<div class="clear"></div>
</div>
<div class="current_content">
<?php //echo $this->Html->link(__('Edit devicetestingmethod',true), array_merge(array('action' => 'edit'), $this->request->projectvars['VarsArray']), array('class' => 'dialog modal icon icon_edit_examiner','title' => __('Edit devicetestingmethod',true)));?>
<div class="clear"></div>
<ul class="listemax current_content">
</ul>
</div>
</div>
<?php  echo $this->JqueryScripte->ModalFunctions(); ?>
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>
