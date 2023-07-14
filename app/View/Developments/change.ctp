<div class="modalarea">
<h2>
<?php echo __('Edit');?> 
<?php echo $thisTestingmethod['verfahren'];?>  
<?php echo __('Measuring point');?> 
<?php echo __('for');?> 
<?php echo $evaluation['description'];?> 
<?php echo $this->Form->create('DevelopmentData',array('class' => 'open_close_order','id' => 'open_close_order')); ?>
<?php
$options = array(
				'-' => __('All') . ' <span id="count_all" class="count">' . $OrdersStatus['all'] . '</span>', 
				'0' => __('Open') . ' <span id="count_open" class="count">' . $OrdersStatus['open'] . '</span>', 
				'1' => __('Repairs') . ' <span id="count_repairs" class="count">' . $OrdersStatus['repairs'] . '</span>', 
				'2' => __('Closet') . ' <span id="count_closet" class="count">' . $OrdersStatus['closet'] . '</span>'
			);

$attributes = array('legend' => false,'default' => $status['number']);
echo '<div class="input radio">';
echo $this->Form->radio('status', $options, $attributes);
echo '</div>';
?>
<?php echo $this->Form->end(); ?>

</h2>
<?php
if(count($developments) == 0){
	echo '<div class="hint"><p>';
	echo __('Es existieren keine geplanten Prüfpunkte für die ' . $thisTestingmethod['verfahren'] . '. Möglicherweise müssen Sie neue Prüfpunkte erstellen.',true);
	echo '</p></div>';
}
?>

	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo __('Discription', true); ?></th>
			<th><?php echo __('Dimension', true); ?></th>
  			<th><?php echo __('Testing method', true); ?></th>
			<th><?php echo __('Status', true); ?></th>
			<th><?php echo __('Edited by', true); ?></th>
			<th class="actions">&nbsp;</th>
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
		<td><?php echo $alltestingmethods[$development['DevelopmentData']['testing_method']]; ?>&nbsp;</td>
		<td class="progress_result" id="<?php echo 'cell_' . $development['DevelopmentData']['id'];?>" >
		<?php
		$option = array('0' => '-', '1' => 'Rep', '2' => 'Ok'); 
		echo $this->Form->input('DevelopmentData.result',array('options' => $option,'data' => $development['DevelopmentData']['id'], 'label' => false, 'default' => $development['DevelopmentData']['result']));	
		?>
		<td>
		<?php 
		if(isset($development['Reportnumber']) && !isset($development['Reportnumber']['Reportnumber']['link_to_false'])){
			echo $this->Html->link($development['Reportnumber']['year'] . '/' .$development['Reportnumber']['number'] . ' - ' . $development['Evaluation']['description'] , 
					array('controller' => 'reportnumbers', 'action' => 'edit', 
					$development['Reportnumber']['topproject_id'],
					$development['Reportnumber']['cascade_id'],
					$development['Reportnumber']['order_id'],
					$development['Reportnumber']['report_id'],
					$development['Reportnumber']['id'],
					), 
			array('class'=>'round ajax', 'id' => 'text_delete_project','title' => __('Open this report'))); 
		}
		if(isset($development['Reportnumber']) && isset($development['Reportnumber']['Reportnumber']['link_to_false'])){
			echo $development['Reportnumber']['Reportnumber']['link_to_false'];
		}
		?>&nbsp;</td>
        </td>
		<td class="actions">  
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
						url		: $("#open_close_order").attr("action"),
						data	: data,
						success: function(data) {
		    				$("#dialog").html(data);
		    				$("#dialpg").show();
						}
					});
				return false;
		});

		$("td.progress_result select").change(function() {
			
			var url = "<?php echo $this->Html->url(array_merge(array('controller'=>'developments','action'=>'result'),$this->request->projectvars['VarsArray']));?>";
			var data = $("fakeform").serializeArray();
			var target = "td#cell_" + $(this).attr("data");
			data.push({name: "ajax_true", value: 1});
			data.push({name: "data[DevelopmentData][id]", value: $(this).attr("data")});
			data.push({name: "data[DevelopmentData][result]", value: $(this).val()});

			$.ajax({
					type	: "POST",
					cache	: true,
					url		: url,
					data	: data,
					success: function(data) {
		    			$(target).html(data);
		    			$(target).show();
					}
				});
				return false;
			});
	});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>


