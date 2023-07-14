<div class="actions" id="top-menue">
	<h3><?php __('Actions'); ?></h3>
	<ul><?php echo $this->Navigation->showNavigation($menues); ?></ul>
</div>
<div class="inhalt">
<div class="weldingmethods view">
<h2><?php  echo __('Weldingmethod'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($weldingmethod['Weldingmethod']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Value'); ?></dt>
		<dd>
			<?php echo h($weldingmethod['Weldingmethod']['value']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($weldingmethod['Weldingmethod']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Testing method'); ?></dt>
		<dd>
			<?php echo h($weldingmethod['Weldingmethod']['verfahren']); ?>
			&nbsp;
		</dd>
	</dl>
	<div class="clear edit">
		<?php 		
		echo $this->Html->link(__('Back to list', true), array('action' => 'index')); 
		echo $this->Html->link(__('Edit this weldingmethod', true), array('action' => 'edit', $weldingmethod['Weldingmethod']['id'])); 
		echo $this->Html->link(__('New testingcomp'), array('controller' => 'testingcomps', 'action' => 'add'));
		echo $this->Html->link(__('New weldingmethod'), array('controller' => 'weldingmethods', 'action' => 'add'));
		echo $this->Html->link(__('New qualification'), array('controller' => 'qualifications', 'action' => 'add'));
		?>
	</div>	
</div>
<div class="related">
	<h3><?php echo __('Related qualifications'); ?></h3>
	<?php if (!empty($weldingmethod['Qualification'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Testingcomp'); ?></th>
		<th><?php echo __('User'); ?></th>
		<th><?php echo __('Examiner-id'); ?></th>
		<th><?php echo __('Certification-number'); ?></th>
		<th><?php echo __('Weldingmethod'); ?></th>
		<th><?php echo __('Level'); ?></th>
		<th><?php echo __('Timeperiod'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th><?php echo __('Supervisors'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
		<?php
		$i = 0;
		foreach ($weldingmethod['Qualification'] as $qualification):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}		
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $testingcomps[$qualification['testingcomp_id']]; ?></td>
			<td><?php echo $users[$qualification['user_id']]; ?></td>
			<td><?php echo $qualification['examiner-id']; ?></td>
			<td><?php echo $qualification['certification-number']; ?></td>
			<td><?php echo $weldingmethod['Weldingmethod']['name']; ?></td>
			<td><?php echo $qualification['level']; ?></td>
			<td><?php echo $qualification['timeperiod']; ?></td>
			<td><?php echo $qualification['created']; ?></td>
			<td><?php echo $qualification['modified']; ?></td>
			<td><?php echo $qualification['supervisors']; ?></td>
			<td class="actions">
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'qualifications', 'action' => 'delete', $qualification['id']), array('class'=>'right'), __('Are you sure you want to delete # %s?', $qualification['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'qualifications', 'action' => 'edit', $qualification['id']), array('class'=>'middle')); ?>
				<?php echo $this->Html->link(__('View'), array('controller' => 'qualifications', 'action' => 'view', $qualification['id']), array('class'=>'left')); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>
<div class="related">
	<h3><?php echo __('Related topprojects'); ?></h3>
	<?php if (!empty($weldingmethod['Topproject'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Project name'); ?></th>
		<th><?php echo __('Project discription'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
		<?php
		$i = 0;
		foreach ($weldingmethod['Topproject'] as $topproject):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}		
		?>

		<tr<?php echo $class;?>>
			<td><?php echo $topproject['id']; ?></td>
			<td><?php echo $topproject['projektname']; ?></td>
			<td><?php echo $topproject['projektbeschreibung']; ?></td>
			<td class="actions">
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'topprojects', 'action' => 'delete', $topproject['id']), array('class'=>'right'), __('Are you sure you want to delete # %s?', $topproject['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'topprojects', 'action' => 'edit', $topproject['id']), array('class'=>'middle')); ?>
				<?php echo $this->Html->link(__('View'), array('controller' => 'topprojects', 'action' => 'view', $topproject['id']), array('class'=>'left')); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>
