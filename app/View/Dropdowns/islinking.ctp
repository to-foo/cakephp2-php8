<div class="modalarea">
	<h2><?php echo __('Entries in this downdown are available due link with another dropdown'); ?></h2>	
<div class="clear">
    <?php echo $this->Html->link(__('remove link'), array('action' => 'dellinking',
				$this->request->projectvars['VarsArray'][0],
				$this->request->projectvars['VarsArray'][1],
				$this->request->projectvars['VarsArray'][2],
				$this->request->projectvars['VarsArray'][3],
				$this->request->projectvars['VarsArray'][4],
				$this->request->projectvars['VarsArray'][5],
				$this->request->projectvars['VarsArray'][6],
				$this->request->projectvars['VarsArray'][7],
				$this->request->projectvars['VarsArray'][8],
				$this->request->projectvars['VarsArray'][9],
				isset($linkingID)?$linkingID : 0
		), array('class'=>'round mymodal')); ?>
    <?php echo $this->Html->link(__('link with another dropdown'), array('action' => 'linking',
				$this->request->projectvars['VarsArray'][0],
				$this->request->projectvars['VarsArray'][1],
				$this->request->projectvars['VarsArray'][2],
				$this->request->projectvars['VarsArray'][3],
				$this->request->projectvars['VarsArray'][4],
				$this->request->projectvars['VarsArray'][5],
				$this->request->projectvars['VarsArray'][6],
				$this->request->projectvars['VarsArray'][7],
				$this->request->projectvars['VarsArray'][8],
				$this->request->projectvars['VarsArray'][9],
		), array('class'=>'round mymodal')); ?>
	</div>
<div class="clear"></div>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo __('Value'); ?></th>
			<th><?php echo __('Value (optional)'); ?></th>
			<th><?php echo __('Testingcompany'); ?></th>
			<th><?php echo __('modified'); ?></th>
	</tr>
<?php
	$i = 0;
	foreach ($modelDatas as $modelData):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo h($modelData[$Model]['discription']); ?>&nbsp;</td>
		<?php
		if(isset($modelDatas[0][$Model]['number']))
		{
			echo '<td>'.h($modelData[$Model]['number']).'</td>';
		}
		?>
		<td><?php echo h($modelData['Testingcomp']['name']); ?>&nbsp;</td>
		<td><?php echo h($modelData[$Model]['modified']); ?>&nbsp;</td>
	</tr>
<?php endforeach; ?>
	</table>

</div>
<div class="clear"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>