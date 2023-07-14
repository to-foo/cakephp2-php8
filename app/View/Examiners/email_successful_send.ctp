<div class="modalarea examiners form">
<h2><?php echo __('E-mail is sent'); ?></h2>
<div class="container_summary">
</div>
<div class="hint"><p>
<?php echo $message;?>
</p>
</div>
<div class="summary">
<h3><?php echo __('Die folgenden Informationen wurden versendet',true);?></h3>
<?php
if(isset($commentar)){
	echo '<h4>'.__('Commentar',true).'</h4>';
	echo '<p>'.$commentar.'</p>';
}

foreach($summary as $_key => $_summary){

	if($_key == 'hints') continue;

	if(count($_summary) > 0 && isset($_key)){
		echo '<h4>'.$summary_desc[$_key][1].'</h4>';
		foreach($_summary as $__key => $__summary){
			if(count($__summary) == 0) continue;
			foreach($__summary as $___key => $___summary){

				if(isset($___summary['certificate']['sector'])){
					$_examiner = $___summary['examiner']['name'].' '.$___summary['examiner']['working_place'].' - '.$___summary['certificate']['third_part'].'/'.$___summary['certificate']['sector'].'/'.$___summary['certificate']['testingmethod'].'/'.$___summary['certificate']['certificat'].'-'.$___summary['certificate']['level'];
				}
				else {
					$_examiner = $___summary['examiner']['name'].' '.$___summary['examiner']['working_place'] . ' - ' . $___summary['certificate']['certificat'];
				}
				 
				echo '<p>';
				echo $_examiner;
				echo '<br></p>';
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
</div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
