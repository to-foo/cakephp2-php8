<div class="modalarea">
	<h2><?php echo __('Dropdowns'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort($select_lang, __('Field')); ?></th>
			<th><?php echo __('Count'); ?></th>
			<th><?php echo __('Testingcompany', true);?></th>
			<th>&nbsp;</th>
	</tr>
	<?php
	$hidable = false;
	$i = 0;
	foreach ($dropdowns as $dropdown):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = 'altrow';
		}
		if($dropdown['Dropdown']['testingmethod_id'] != $reportnumber['Testingmethod']['id']) { $hidable = true; $class .= ' hidable'; }

		if(!empty($class)) $class=' class="'.$class.'"';
	?>
	<tr<?php echo $class;?>>
		<td><?php echo h($dropdown['Dropdown'][$select_lang]); ?>&nbsp;</td>
		<td><?php echo h($dropdown['Values']); ?></td>
		<td><?php echo h($dropdown['Testingcomp']['name']);?></td>
		<td class="actions">
			<?php $varsArray = $VarsArray; $varsArray[8] = $dropdown['Dropdown']['id']; ?>
			<?php //echo $this->Html->link(__('Edit dropdown settings'), array_merge(array('action' => 'edit'), $varsArray), array('class' => 'right mymodal editlink')); ?>
			<?php echo $this->Html->link(__('Edit dropdown content', true), array_merge(array('action' => 'dropdownindex'), $varsArray), array('class' => 'left mymodal editlink'));?>
			<div class="clear"></div>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<div class="paging mymodal">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled mymodal'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled mymodal'));
	?>
	</div>
	<p class="paging_query">
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>
	</p>
</div>
<!--
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Dropdown'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Testingcomps'), array('controller' => 'testingcomps', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Testingcomp'), array('controller' => 'testingcomps', 'action' => 'add')); ?> </li>
	</ul>
</div>
-->
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
<script type="text/javascript">
	$(document).ready(function() {
		$('#dialog .settingslink .viewlink').off('click').unbind('click').click(function() {
			$('#dialog .settingslink .viewlink').prop('disabled', true);
			$('#dialog tr.hidable').toggle(0,function() {
				$('#dialog .settingslink .viewlink').prop('disabled', false);
			});
			return false;
		});

		$('#dialog tr.hidable').hide();
	});
</script>
