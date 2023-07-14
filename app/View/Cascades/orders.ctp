<div class="quicksearch">
<?php // echo $this->Navigation->quickSearching('quicksearch',1,__('Orders', true)); ?>
<?php echo $this->element('searching/search_quick_order',array('action' => 'quickreportsearch','minLength' => 2,'discription' => __('Order', true)));?>
<?php echo $this->element('searching/search_quick_reportnumber',array('action' => 'quickreportsearch','minLength' => 1,'discription' => __('Pr-Nr. (YYYY-NN)')));?>
<?php echo $this->element('barcode_search');?>
<?php echo $this->element('subdevisions_form');?>
</div>
<div class="equipments index inhalt">
<h2></h2>
<?php echo $this->Form->create('Equipment',array('class' => 'open_close_order','id' => 'open_close_order')); ?>
<?php
$options = array();
foreach($OrdersStatus as $_key => $_data){
	if($_data == 0) continue;
	if($_key == 'open') $options[0] = __('Open Orders') . ' <span class="count">' . $_data . '</span>';
	if($_key == 'closed') $options[1] = __('Closed Orders') . ' <span class="count">' . $_data . '</span>';
	if($_key == 'all') $options[2] = __('All Orders') . ' <span class="count">' . $_data . '</span>';
}

$attributes = array('legend' => false,'default' => $status['number']);
echo '<div class="input radio">';
echo $this->Form->radio('gender', $options, $attributes);
echo '</div>';
?>
<?php echo $this->Form->end(); ?>
<?php
echo $this->Navigation->makeLink('orders','add',__('Add new order'),'modal round',null,$this->request->projectvars['VarsArray']);
//echo $this->Navigation->makeLink('orders','create',__('Upload new order'),'modal round',null,$this->request->projectvars['VarsArray']);

if(count($Orders) == 0){
	echo $this->JqueryScripte->LeftMenueHeight();
	echo '</div>';
	return;
}
?>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('auftrags_nr',__('order no.',true)); ?></th>
			<th></th>
			<th><?php echo $this->Paginator->sort('project_name',__('Projekt',true)); ?></th>
			<th><?php echo $this->Paginator->sort('bauteil'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th><?php echo __('status'); ?></th>
	</tr>
	<?php
	$i = 0;

	foreach ($Orders as $topproject):

		$this->request->projectvars['VarsArray'][2] = $topproject['Order']['id'];
		$class = 'class="';

		if ($i++ % 2 == 0) {
			$class .= ' altrow';
		}

		if($topproject['Order']['status'] == 0){
			$class .= ' open';
			$statusValue = __('Open');
		}
		if($topproject['Order']['status'] == 1){
			$class .= ' close';
			$statusValue = __('Closed');
		}

		$class .= '"';
	?>
	<tr <?php echo $class;?> >
		<td>
		<span class="for_hasmenu1 weldhead">
		<?php echo $this->Html->link(h($topproject['Order']['auftrags_nr']),
			array_merge(array('controller' => 'reportnumbers','action' => 'index'),
			$this->request->projectvars['VarsArray']),
			array(
				'class'=>'round icon_show ajax hasmenu1',
				'rev' => implode('/',$this->request->projectvars['VarsArray'])
			)
		); ?>
        </span>
        </td>
				<td><?php echo $this->element('development/development_shortview',array('order' => $topproject));?></td>
        <td>
        <span class="discription_mobil">
					<?php echo __('Projektnamen'); ?>:
				</span>
				<?php echo $topproject['Order']['project_name'];?>
        </td>
        <td>
        <span class="discription_mobil">
		<?php echo __('Bauteil'); ?>:
		</span>
		<?php echo $topproject['Order']['examination_object'];?>
        </td>

        <td>
        <span class="discription_mobil">
		<?php echo __('created'); ?>:
		</span>
		<?php echo $topproject['Deliverynumber']['created'];?>
        </td>
        <td>
        <span class="discription_mobil">
		<?php echo __('modified'); ?>:
		</span>
		<?php echo $topproject['Deliverynumber']['modified'];?>
        </td>
        <td>
        <span class="discription_mobil">
		<?php echo __('Status'); ?>:
		</span>
		<?php echo $statusValue;?>
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
<script type="text/javascript">
	$(document).ready(function(){


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
		    				$("#container").html(data);
		    				$("#container").show();
						}
					});
				return false;
		});
	});
</script>

<?php
echo $this->element('js/form_button_set');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/ajax_paging');
?>
