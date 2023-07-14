<div class="users view modalarea">
<h2><?php  echo __('Topproject'); ?></h2>
	<dl>
		<dt><?php echo __('Project name'); ?></dt>
		<dd>
			<?php echo h($topproject['Topproject']['projektname']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Identification'); ?></dt>
		<dd>
			<?php echo h($topproject['Topproject']['identification']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Project discription'); ?></dt>
		<dd>
			<?php echo h($topproject['Topproject']['projektbeschreibung']); ?>
			&nbsp;
		</dd>
	<div class="clear edit"></div>
	</dl>
	<h3><?php echo __('Reports'); ?></h3>
	<?php if (!empty($topproject['Report'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th><?php echo __('Identification'); ?></th>
		<th><?php echo __('Discription'); ?></th>
		<th class="actions">&nbsp;</th>
	</tr>
	<?php
	$i = 0;
	foreach ($topproject['Report'] as $report):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $report['id']; ?></td>
			<td><?php echo $report['name']; ?></td>
			<td><?php echo $report['identification']; ?></td>
			<td><?php echo $report['projektbeschreibung']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('Delete'), array('controller' => 'reports', 'action' => 'delete', $report['id']), array('class'=>'icon icon_delete mymodal')); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'reports', 'action' => 'edit', $report['id']),array('class'=>'icon icon_edit mymodal')); ?>
				<?php echo $this->Html->link(__('View'), array('controller' => 'reports', 'action' => 'view', $report['id']),array('class'=>'icon icon_view mymodal')); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
	<h3><?php echo __('Related testingcomps'); ?></h3>
	<?php if (!empty($topproject['Testingcomp'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th><?php echo __('Roll Id'); ?></th>
		<th><?php echo __('Company name'); ?></th>
		<th><?php echo __('Street'); ?></th>
		<th><?php echo __('Post code'); ?></th>
		<th><?php echo __('City'); ?></th>
		<th><?php echo __('Phone'); ?></th>
		<th><?php echo __('Fax'); ?></th>
		<th><?php echo __('Internet'); ?></th>
		<th><?php echo __('Email'); ?></th>
		<th class="actions">&nbsp;</th>
	</tr>
	<?php
	$i = 0;
	foreach ($topproject['Testingcomp'] as $testingcomp):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $testingcomp['id']; ?></td>
			<td><?php echo $testingcomp['name']; ?></td>
			<td><?php echo $testingcomp['roll_id']; ?></td>
			<td><?php echo $testingcomp['firmenname']; ?></td>
			<td><?php echo $testingcomp['strasse']; ?></td>
			<td><?php echo $testingcomp['plz']; ?></td>
			<td><?php echo $testingcomp['ort']; ?></td>
			<td><?php echo $testingcomp['telefon']; ?></td>
			<td><?php echo $testingcomp['telefax']; ?></td>
			<td><?php echo $testingcomp['internet']; ?></td>
			<td><?php echo $testingcomp['email']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('Delete'), array('controller' => 'testingcomps', 'action' => 'delete', $testingcomp['id']), array('class'=>'icon icon_delete mymodal')); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'testingcomps', 'action' => 'edit', $testingcomp['id']),array('class'=>'icon icon_edit mymodal')); ?>
				<?php echo $this->Html->link(__('View'), array('controller' => 'testingcomps', 'action' => 'view', $testingcomp['id']),array('class'=>'icon icon_view mymodal')); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>
<div class="clear" id="testdiv"></div>
<?php if(isset($afterEDIT)){echo $afterEDIT;} ?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
