<div class="modalarea welders form">
<h2><?php echo __('Send this vision test informations per email'); ?></h2>
<div class="container_summary">
</div>
<div class="hint"><p>
<?php echo $message;?>
</p></div>
<?php echo $this->Form->create('Welder', array('class' => 'dialogform')); ?>
<fieldset>
<?php echo $this->Form->input('email',array('label' => __('email',true)));?>
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
foreach($summary as $_key => $_summary){

	if($_key == 'hints') continue;

	if(count($_summary) > 0 && isset($_key)){
		echo '<h4>'.$summary_desc[$_key][1].'</h4>';
		foreach($_summary as $__key => $__summary){
			if(count($__summary) == 0) continue;
			foreach($__summary as $___key => $___summary){

				echo '<p>';
				echo $this->Form->input($___key,array(
												'type' => 'checkbox',
												'checked' => 'checked',
												'label' => '&nbsp;'
												)
											);
				echo $___summary['welder']['name'].' - '.$___summary['certificate']['certificat'];
				echo '</p>';
				echo '<ul>';
				foreach($___summary as $____key => $____summary){
					if(is_numeric($____key)){
						echo '<li>'.$____summary.'</li>';
					}
				}
				echo '</ul>';
				echo '<span class="clear"></span>';
			}
		}
	}
}

echo '</ul>';
?>
</div>
</fieldset>
<?php echo $this->Form->end('Submit'); ?>
</div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>