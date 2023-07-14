<div class="users view modalarea">
<h2><?php  echo __('User'); ?></h2>
	<dl>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($user['User']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Username'); ?></dt>
		<dd>
			<?php echo h($user['User']['username']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($user['User']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($user['User']['modified']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Lastlogin'); ?></dt>
		<dd>
			<?php echo h($user['User']['lastlogin']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Enabled'); ?></dt>
		<dd>
			<?php echo h($user['User']['enabled']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Roll'); ?></dt>
		<dd>
			<?php echo h($user['Roll']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Testingcomp'); ?></dt>
		<dd>
			<?php echo $this->Html->link($user['Testingcomp']['name'], array('controller' => 'testingcomps', 'action' => 'view', $user['Testingcomp']['id']), array('class'=>'mymodal')); ?>
			&nbsp;
		</dd>
			<?php 		
			echo $this->Html->link(__('Edit User', true), array('action' => 'edit', $user['User']['id']), array('class'=>'round mymodal'));
			?>
			<div class="clear edit"></div>		
	</dl>
	<h3><?php echo __('Related Qualifications'); ?></h3>
	<?php if (!empty($user['Qualification'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Certification-number'); ?></th>
		<th><?php echo __('Testingmethod'); ?></th>
		<th><?php echo __('Level'); ?></th>
		<th><?php echo __('Timeperiod'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th><?php echo __('Testingcomp'); ?></th>
		<th><?php echo __('Supervisors'); ?></th>
		<th class="actions">&nbsp;</th>
	</tr>
	<?php
		$i = 0;
		foreach ($user['Qualification'] as $qualification): ?>
		<tr>
			<td><?php echo $qualification['certification-number']; ?></td>
			<td><?php echo $testingmethods[$qualification['testingmethod_id']]; ?></td>
			<td><?php echo $qualification['level']; ?></td>
			<td><?php echo $qualification['timeperiod']; ?></td>
			<td><?php echo $qualification['created']; ?></td>
			<td><?php echo $qualification['modified']; ?></td>
			<td><?php echo $testingcomps[$qualification['testingcomp_id']]; ?></td>
			<td><?php echo $qualification['supervisors']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'qualifications', 'action' => 'edit', $qualification['id']), array('class'=>'right mymodal')); ?>
				<?php echo $this->Html->link(__('View'), array('controller' => 'qualifications', 'action' => 'view', $qualification['id']), array('class'=>'left mymodal')); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>
<div class="clear"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>