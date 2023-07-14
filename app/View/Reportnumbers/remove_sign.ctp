<div class="modalarea">
<h2><?php echo __('Remove signaturs');?></h2>
<?php
	echo '<div class="signs">';

	if(isset($thisSign) && isset($error_array[$thisSign]) ) {
		echo '<div class="error_occurred">';
		echo '<div class="errormessage">';
		echo '<p>';
		echo $error_array[$thisSign];
		echo '</p>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}

	if(isset($thisSign) && isset($signature[$thisSign]['data'])){
//		echo '<img src="' . $signature[$thisSign]['data'] . '" alt="' . $signature[$thisSign]['discription'] . '">';
	}

	echo '</div>';
	echo '<div class="hint">';
	echo '<p>';
	echo __('Will you realy remove all signaturs of this report?');
	echo '</p>';

	echo $this->Html->link(
        __('Cancel'),
        'javascript:',
		array(
			'id' => 'closethismodal',
			'class' => 'dialog_close ajax round'
		)
    );
	echo $this->html->link(
					__('Delete'),
							array_merge(array('action' => 'removeSign'),$this->request->projectvars['VarsArray']),
							array(
								'id' => 'remove_signs',
								'class' => 'round'
								)
						);

	echo '<div class="clear"></div>';
	echo '</div>';
?>
<?php echo $this->element('js/remove_sign'); ?>
</div>
<?php
if(isset($FormName)){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
}
echo $this->JqueryScripte->ModalFunctions();
?>
