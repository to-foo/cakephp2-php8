<div class="reports view modalarea">
<h2><?php  echo __('Report'); ?></h2>
	<dl>
		<dt><?php echo __('Project name'); ?></dt>
		<dd>
			<?php echo h($report['Report']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Identification'); ?></dt>
		<dd>
			<?php echo h($report['Report']['identification']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Project discription'); ?></dt>
		<dd>
			<?php echo h($report['Report']['projektbeschreibung']); ?>
			&nbsp;
		</dd>
		<?php 		
		echo $this->Html->link(__('Edit this report', true), array('action' => 'edit', $report['Report']['id']), array('class'=>'round mymodal')); 
		?>
		<div class="clear edit"></div>			
	</dl>
	<h3><?php echo __('Related testingmethods'); ?></h3>
	<?php if (!empty($report['Testingmethod'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Value'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th><?php echo __('Testing method'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($report['Testingmethod'] as $testingmethod):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $testingmethod['id']; ?></td>
			<td><?php echo $testingmethod['value']; ?></td>
			<td><?php echo $testingmethod['name']; ?></td>
			<td><?php echo $testingmethod['verfahren']; ?></td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
