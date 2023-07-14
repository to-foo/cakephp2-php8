<div class="modalarea">
<table cellpadding="0" cellspacing="0">
<tr>
<th><?php echo __('Testing method', true); ?></th>
<th><?php echo __('Discription', true); ?></th>
<th><?php echo __('Dimensions', true); ?></th>
<th><?php echo __('Filmposition', true); ?></th>
<th><?php echo __('Last edited', true); ?></th>
<th><?php echo __('Evaluation', true); ?></th>
</tr>
<?php
$i = 0;

foreach($WeldArray as $_key => $_WeldArray){
	foreach($_WeldArray as $__key => $__WeldArray){
		foreach($__WeldArray as $___key => $___WeldArray){
			foreach($___WeldArray as $____key => $____WeldArray){
				
				$class = null;
				
				if ($i++ % 2 == 0) {
					$class = ' class="altrow"';
				}
				
				echo '<tr '.$class.'>';
				echo '<td>'.$_key.'</td>';
				echo '<td>'.$____WeldArray['description'].'</td>';
				echo '<td>'.$____WeldArray['dimension'].'</td>';
				echo '<td>'.@$____WeldArray['film_position'].'</td>';
				echo '<td>'.$____WeldArray['modified'].'</td>';
				echo '<td>'.$____WeldArray['result'];
				echo '</tr>';
			}
		}
	}
}
?>
</table>
</div>