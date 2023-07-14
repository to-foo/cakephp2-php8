<div class="quicksearch">
<?php // echo $this->Navigation->quickSearching('quicksearch',1,__('Orders', true)); ?>
<?php echo $this->Navigation->quickOrderSearching('quickreportsearch',2,__('Order', true)); ?>
<?php echo $this->Navigation->quickReportSearching('quickreportsearch',1,__('Pr-Nr. (YYYY-NN)', true)); ?>
<?php echo $this->element('subdevisions_form');?>
</div>
<div class="equipments index inhalt">
<h2>
<?php //  echo __('Equipment'); ?> 
<?php // echo __('Orderstatus');?>  
<?php echo $this->Form->create('Equipment',array('class' => 'open_close_order','id' => 'open_close_order')); ?>
<?php
$options = array(
				'0' => __('Open Orders') . ' <span class="count">' . $OrdersStatus['open'] . '</span>', 
				'1' => __('Closed Orders') . ' <span class="count">' . $OrdersStatus['closet'] . '</span>', 
				'2' => __('All Orders') . ' <span class="count">' . $OrdersStatus['all'] . '</span>'
			);

$attributes = array('legend' => false,'default' => $status['number']);
echo '<div class="input radio">';
echo $this->Form->radio('gender', $options, $attributes);
echo '</div>';
?>
<?php echo $this->Form->end(); ?>

</h2>
<?php 
if(count($deliverynumbers) == 0){
	echo $this->Navigation->makeLink('orders','add',__('Add new order'),'modal round',null,$this->request->projectvars['VarsArray']);
	echo $this->Navigation->makeLink('orders','create',__('Upload new order'),'modal round',null,$this->request->projectvars['VarsArray']);
}
?>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('auftrags_nr'); ?></th>
			<th><?php echo $this->Paginator->sort('testingcomp_id'); ?></th>
			<th><?php echo $this->Paginator->sort('bauteil'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th><?php echo __('status'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($deliverynumbers as $topproject):
	
		$this->request->projectvars['VarsArray'][3] = $topproject['Order']['id'];

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
        <td>
        <span class="discription_mobil">
		<?php echo __('Testingcompany'); ?>: 
		</span>
		<?php echo $topproject['Testingcomp']['name'];?>
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

		var modalheight = Math.ceil(($(window).height() * 90) / 100);
		var modalwidth = Math.ceil(($(window).width() * 90) / 100);

		var dialogOpts = {
			modal: false,
			width: modalwidth,
			height: modalheight,
			autoOpen: false,
			draggable: true,
			resizeable: true
			};

		$("#dialog").dialog(dialogOpts);
/*
		$("span.for_hasmenu1").contextmenu({
			delegate: ".hasmenu1",
			autoFocus: true,
			preventContextMenuForPopup: true,
			preventSelect: true,
			taphold: true,
			menu: [
				{
				title: "<?php echo __('Edit');?>", 
				cmd: "edit", 
				action :	function(event, ui) {
							$("#dialog").load("orders/edit/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
				uiIcon: "qm_edit", 
				disabled: false 
				},
				{
					title: "----"
				},
				{
				title: "<?php echo __('Change Status');?>", 
				cmd: "edit", 
				action :	function(event, ui) {
							$("#dialog").load("orders/status/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
				uiIcon: "qm_status", 
				disabled: false 
				},
				{
					title: "----"
				},
				{
				title: "<?php echo __('Delete');?>", 
				cmd: "delete", 
				action :	function(event, ui) {
								checkDuplicate = confirm("<?php echo __('Soll dieser Auftrag gelöscht werden?');?>");
								if (checkDuplicate == false) {
									return false;
								}
					
							$("#dialog").load("orders/delete/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
				uiIcon: "qm_delete", 
				disabled: false 
				}
				],

			select: function(event, ui) {},
		});
*/		
	});
</script>

<?php  echo $this->JqueryScripte->LeftMenueHeight(); ?>