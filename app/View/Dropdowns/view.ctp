<div class="dropdowns view">
<h2><?php  echo __('Dropdown'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($dropdown['Dropdown']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Wert'); ?></dt>
		<dd>
			<?php echo h($dropdown['Dropdown']['wert']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit dropdown'), array('action' => 'edit', $dropdown['Dropdown']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete dropdown'), array('action' => 'delete', $dropdown['Dropdown']['id']), null, __('Are you sure you want to delete # %s?', $dropdown['Dropdown']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List dropdowns'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New dropdown'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List testingcomps'), array('controller' => 'testingcomps', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New testingcomp'), array('controller' => 'testingcomps', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related testingcomps'); ?></h3>
	<?php if (!empty($dropdown['Testingcomp'])): ?>
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
		foreach ($dropdown['Testingcomp'] as $testingcomp): ?>
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
