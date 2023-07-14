<div class="actions" id="top-menue">
	<?php 
	echo $this->Navigation->quickSearch(); 
	?>
	<h3><?php __('Actions'); ?></h3>
	<ul><?php echo $this->Navigation->showNavigation($menues); ?></ul>
</div>
<div class="orders form inhalt">
<?php echo $message;?>
	<table cellpadding="0" cellspacing="0">
	<?php
	$i = 0;
	foreach ($reportnumbers as $reportnumber):
		
		// Wenn nix da ist
		if(!isset($reportnumber['Reportnumber'])){
			echo '<tr><td>';
			echo __('There is no report matching the entered parameters.', true);
			echo '</td></tr></table>';
			break;
		}
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' altrow';
		}
		if($reportnumber['Reportnumber']['status'] == 1){
			$class = ' closed';
		}
		if($reportnumber['Reportnumber']['delete'] == 1){
			$class = ' delete';
		}
	?>
	<tr class="<?php echo $class;?>">
		<td><?php echo h($reportnumber['Reportnumber']['year']).'-'.h($reportnumber['Reportnumber']['number']);?></td>
		<td>
		<?php 
		
		echo $this->Html->link(__('Testing areas'), array('action' => 'view', $reportnumber['Reportnumber']['id']), 
				array(
					'class'=>'show_testareas',
					'rel' => Router::url(array('controller' => 'orders', 'action' => 'reports', $projectID, $orderKat, $orderID)),
					'rev' => $reportnumber['Reportnumber']['id']
				)); 

		
		?>
		</td>
		<td><?php echo h($reportnumber['Testingmethod']['verfahren']);?></td>
		<td><?php echo h($reportnumber['Testingcomp']['name']);?></td>
		<td><?php echo h($reportnumber['Reportnumber']['created']);?></td>
		<td><?php echo h($reportnumber['Reportnumber']['modified']);?></td>
		<td class="actions">
			<?php
		if($reportnumber['Reportnumber']['delete'] != 1){
				echo $this->Html->link(__('Delete'), array('controller' => 'reportnumbers', 'action' => 'delete', $reportnumber['Reportnumber']['id']), array('class'=>'right','id'=>'text_delete_report')); 
				echo $this->Html->link(__('Edit'), array('controller' => 'reportnumbers', 'action' => 'edit', $reportnumber['Reportnumber']['id']), array('class'=>'middle'));
				echo $this->Html->link(__('View'), array('controller' => 'reportnumbers', 'action' => 'view', $reportnumber['Reportnumber']['id']), array('class'=>'left')); 
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>

</div>
<div class="clear" id="testdiv"></div>
<?php if(isset($afterEDIT)){echo $afterEDIT;} ?>
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>