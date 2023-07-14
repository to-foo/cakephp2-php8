<div class="modalarea">
<h2>
<?php echo __('Measuring point');?>
<?php echo $this->Form->create('DevelopmentData',array('class' => 'open_close_order','id' => 'open_close_order')); ?>
<?php
$options = array(
				'3' => __('All') . ' <span id="count_all" class="count">' . $OrdersStatus['all'] . '</span>',
				'0' => __('Open') . ' <span id="count_open" class="count">' . $OrdersStatus['open'] . '</span>',
				'1' => __('Repairs') . ' <span id="count_repairs" class="count">' . $OrdersStatus['repairs'] . '</span>',
				'2' => __('Closet') . ' <span id="count_closet" class="count">' . $OrdersStatus['closet'] . '</span>'
			);

$attributes = array('legend' => false,'default' => $status['number']);
echo '<div class="input radio">';
echo $this->Form->radio('status', $options, $attributes);
echo '</div>';
/*
$options = array(
				'1' => __('Delete Orders'),
			);
$attributes = array('legend' => false,'default' => $status['number']);
echo ' <div class="input radio">';
echo $this->Form->radio('delete', $options, $attributes);
echo '</div>';
*/
?>
<?php echo $this->Form->end(); ?>

</h2>


	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo __('Discription', true); ?></th>
			<th><?php echo __('Dimension', true); ?></th>
  			<th><?php echo __('Testing method', true); ?></th>
			<th><?php echo __('Status', true); ?></th>
			<th><?php echo __('Edited by', true); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($developments as $development):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo h($development['DevelopmentData']['equipment_discription']); ?>&nbsp;</td>
		<td><?php echo h($development['DevelopmentData']['dimension']); ?>&nbsp;</td>
		<td><?php echo $development['DevelopmentData']['testing_method']; ?>&nbsp;</td>
		<td class="progress_result" id="<?php echo 'cell_' . $development['DevelopmentData']['id'];?>" >
		<?php
		$option = array('0' => __('Open',true), '1' => __('Rep',true), '2' => __('Ok',true));
		echo $option[$development['DevelopmentData']['result']];
		?>
        </td>
		<td>
		<?php
		if(isset($development['Reportnumber'])){
				echo $this->Html->link($development['Reportnumber']['year'] . '-' . $development['Reportnumber']['number'] . ', ' . $development['DevelopmentData']['description'], array('controller' => 'reportnumbers','action' => 'editevalution',
				$development['Reportnumber']['topproject_id'],
				$development['Reportnumber']['cascade_id'],
				$development['Reportnumber']['order_id'],
				$development['Reportnumber']['report_id'],
				$development['Reportnumber']['id'],
				$development['DevelopmentData']['evaluation_id'],
				1
				),
				array('class'=>'ajax round','title' => __('Go to this evalation')));

		}
		elseif(!isset($development['Reportnumber']) && $development['DevelopmentData']['result'] > 0){
			echo $development['DevelopmentData']['description'];
		}
		?>&nbsp;
        </td>
        </td>
		<td class="actions">
			<?php echo $this->Html->link(__('Delete'), array('action' => 'progressdel',
				$this->request->projectvars['VarsArray'][0],
				$this->request->projectvars['VarsArray'][1],
				$this->request->projectvars['VarsArray'][2],
				$this->request->projectvars['VarsArray'][3],
				$this->request->projectvars['VarsArray'][4],
				$development['DevelopmentData']['id'],
				1,
				),
				array('class'=>'icon icon_delete mymodal delete_generally', 'id' => 'text_delete_project','title' => __('Delete this progress'))); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'progressedit',
				$this->request->projectvars['VarsArray'][0],
				$this->request->projectvars['VarsArray'][1],
				$this->request->projectvars['VarsArray'][2],
				$this->request->projectvars['VarsArray'][3],
				$this->request->projectvars['VarsArray'][4],
				$development['DevelopmentData']['id'],
				1,
				),
				array('class'=>'icon icon_edit mymodal','title' => __('Edit this progress'))); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
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
<div class="clear" id="testdiv"></div>
<script type="text/javascript">
	$(document).ready(function(){

		$("form.open_close_order:not(.ui-buttonset)").buttonset();

		$("div.radio input[type=radio]").change(function() {
				var data = $(this).serializeArray();
				data.push({name: "ajax_true", value: 1});
				data.push({name: "dialog", value: 1});

				$.ajax({
						type	: "POST",
						cache	: true,
/*						url		: $("#open_close_order").attr("action"), */
						url		: "<?php echo $this->Html->url(array('controller'=>'developments','action'=>'progress',
																$this->request->params['pass'][0],
																$this->request->params['pass'][1],
																$this->request->params['pass'][2],
																$this->request->params['pass'][3],
																$this->request->params['pass'][4],
											)
										)?>",

						data	: data,
						success: function(data) {
		    				$("#dialog").html(data);
		    				$("#dialpg").show();
						}
					});
				return false;
		});
	});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
