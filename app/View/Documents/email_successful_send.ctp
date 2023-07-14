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
	if(count($_summary) == 0) continue;

	if(count($_summary) > 0 && isset($_key)){

		echo '<h4>'.$summary_desc[$_key][1].'</h4>';

		foreach($_summary as $__key => $__summary){

			foreach($__summary as $___key => $___summary){
				
				foreach($___summary as $____key => $____summary){

					echo '<p>';

					echo '<strong>' . $__key . '</strong> - ' . $____summary['Document']['document_type'] . ' ' . $____summary['Document']['name'].' ('.$____summary['Document']['registration_no'].')';
					echo '</p>';
				

					echo '<ul>';

					foreach($____summary['summary'] as $_____key => $_____summary){
						if(is_numeric($_____key)){
							echo '<li>'.$_____summary.'</li>';
						}
					}

					echo '</ul>';
				}
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
