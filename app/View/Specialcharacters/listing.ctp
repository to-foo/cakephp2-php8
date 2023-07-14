<div class="modalarea">
	<h2><?php echo __('Edit specialcharacters', true); ?></h2>
	<table cellpadding="0" cellspacing="0">
    <tr>
    <th><?php echo __('Value', true); ?></th>
    <th><?php echo __('Discription', true); ?></th>
    <th><?php echo __('Created', true); ?></th>
    <th><?php echo __('Modified', true); ?></th>
    <th></th>
    </tr>
	<?php
	$i = 0;
	foreach ($Specialcharacter as $modelData):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo h($modelData[$Model]['value']); ?>&nbsp;</td>
		<td><?php echo h($modelData[$Model]['discription']); ?>&nbsp;</td>
		<td><?php echo h($modelData[$Model]['created']); ?>&nbsp;</td>
		<td><?php echo h($modelData[$Model]['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php $_varsArray = $VarsArray; $_varsArray[15] = $modelData[$Model]['id']; ?>
			<?php echo $this->Html->link(__('Delete'), array_merge(array('action' => 'delete'), $_varsArray), array('class'=>'icon icon_delete delete_generally mymodal')); ?>
			<?php echo $this->Html->link(__('Edit'), array_merge(array('action' => 'edit'), $_varsArray), array('class'=>'icon icon_edit mymodal')); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
