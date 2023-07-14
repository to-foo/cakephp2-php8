<div class="rolls view">
<h2><?php  echo __('Roll'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($roll['Roll']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($roll['Roll']['name']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit roll'), array('action' => 'edit', $roll['Roll']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete roll'), array('action' => 'delete', $roll['Roll']['id']), null, __('Are you sure you want to delete # %s?', $roll['Roll']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List rolls'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New roll'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List testingcomps'), array('controller' => 'testingcomps', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New testingcomp'), array('controller' => 'testingcomps', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New user'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related testingcomps'); ?></h3>
	<?php if (!empty($roll['Testingcomp'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th><?php echo __('Roll Id'); ?></th>
		<th><?php echo __('Logopath'); ?></th>
		<th><?php echo __('Company name'); ?></th>
		<th><?php echo __('Company name addition'); ?></th>
		<th><?php echo __('Street'); ?></th>
		<th><?php echo __('Postcode'); ?></th>
		<th><?php echo __('City'); ?></th>
		<th><?php echo __('Phone'); ?></th>
		<th><?php echo __('Fax'); ?></th>
		<th><?php echo __('Internet'); ?></th>
		<th><?php echo __('Email'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($roll['Testingcomp'] as $testingcomp): ?>
		<tr>
			<td><?php echo $testingcomp['id']; ?></td>
			<td><?php echo $testingcomp['name']; ?></td>
			<td><?php echo $testingcomp['roll_id']; ?></td>
			<td><?php echo $testingcomp['logopfad']; ?></td>
			<td><?php echo $testingcomp['firmenname']; ?></td>
			<td><?php echo $testingcomp['firmenzusatz']; ?></td>
			<td><?php echo $testingcomp['strasse']; ?></td>
			<td><?php echo $testingcomp['plz']; ?></td>
			<td><?php echo $testingcomp['ort']; ?></td>
			<td><?php echo $testingcomp['telefon']; ?></td>
			<td><?php echo $testingcomp['telefax']; ?></td>
			<td><?php echo $testingcomp['internet']; ?></td>
			<td><?php echo $testingcomp['email']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'testingcomps', 'action' => 'view', $testingcomp['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'testingcomps', 'action' => 'edit', $testingcomp['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'testingcomps', 'action' => 'delete', $testingcomp['id']), null, __('Are you sure you want to delete # %s?', $testingcomp['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New testingcomp'), array('controller' => 'testingcomps', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
<div class="related">
	<h3><?php echo __('Related users'); ?></h3>
	<?php if (!empty($roll['User'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th><?php echo __('Username'); ?></th>
		<th><?php echo __('Password'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th><?php echo __('Lastlogin'); ?></th>
		<th><?php echo __('Enabled'); ?></th>
		<th><?php echo __('Roll id'); ?></th>
		<th><?php echo __('Testingcomp id'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($roll['User'] as $user): ?>
		<tr>
			<td><?php echo $user['id']; ?></td>
			<td><?php echo $user['name']; ?></td>
			<td><?php echo $user['username']; ?></td>
			<td><?php echo $user['password']; ?></td>
			<td><?php echo $user['created']; ?></td>
			<td><?php echo $user['modified']; ?></td>
			<td><?php echo $user['lastlogin']; ?></td>
			<td><?php echo $user['enabled']; ?></td>
			<td><?php echo $user['roll_id']; ?></td>
			<td><?php echo $user['testingcomp_id']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'users', 'action' => 'view', $user['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'users', 'action' => 'edit', $user['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'users', 'action' => 'delete', $user['id']), null, __('Are you sure you want to delete # %s?', $user['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New user'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
