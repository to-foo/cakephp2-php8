<div class="modalarea welders form">
<h2><?php echo __('Send informations per email');?></h2>
<div class="container_summary">
</div>
<div class="hint"><p>
<?php echo $message;?>
</p></div>
<?php echo $this->Form->create('Welder', array('class' => 'login')); ?>
<fieldset>

<?php echo $this->Form->input('url.controller',array('value' => $lastModalURLArray['controller'],'type' => 'hidden'));?>
<?php echo $this->Form->input('url.action',array('value' => $lastModalURLArray['action'],'type' => 'hidden'));?>
<?php
foreach($lastModalURLArray['pass'] as $_key => $_pass){
	echo $this->Form->input('url.pass.'.$_key,array('value' => $_pass,'type' => 'hidden'));
}
?>
</fieldset>
<fieldset>
<?php echo $this->Form->input('comment',array('label' => __('comment',true),'type' => 'textarea'));?>
</fieldset>
<fieldset>
<div class="summary">
<?php


echo '</ul>';
?>
</div>
</fieldset>

<?php echo $this->Form->end('Senden'); ?>
</div>
<?php
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
?>
