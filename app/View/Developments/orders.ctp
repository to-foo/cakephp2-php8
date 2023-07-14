<div class="equipments index inhalt">
<h2><?php echo $development['Development']['name'] . ' ';?></h2>
<?php

echo $this->Form->create('Orders',array('class' => 'open_close_order','id' => 'open_close_order'));

$options = array(
				'0' => __('Open Orders') . ' <span class="count">' . $OrdersStatus['open'] . '</span>',
				'1' => __('Closed Orders') . ' <span class="count">' . $OrdersStatus['closet'] . '</span>',
				'2' => __('All Orders') . ' <span class="count">' . $OrdersStatus['all'] . '</span>'
			);

$attributes = array('legend' => false,'default' => $status['number']);
echo '<div class="input radio ordersdevelopments">';
echo $this->Form->radio('status', $options, $attributes);
echo '</div>';
echo $this->Form->end(); ?>
<?php echo $this->element('development/development_progress_bar',array('progress_all' => $progress_all));?>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th>&nbsp;</th>
			<th><?php echo $this->Paginator->sort('MP'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($deliverynumber as $topproject):

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
        <span class="contexmenu_weldposition for_hasmenu_2">
        <?php
        echo $this->Html->link(__('Show details of progress',true) . ' > ' . $topproject['Order']['auftrags_nr'],
						array('controller' => 'developments', 'action' => 'orderdetails',
						$this->request->projectID,
						$topproject['Order']['id'],
						3
						),
						array(
							'class'=>'modal round hasmenu_2',
							'title' => __('Show details of progress',true) . ' ' . $topproject['Order']['auftrags_nr'],
							'rev' =>
								$topproject['Order']['topproject_id'].'/'.
								$topproject['Order']['cascade_id'].'/'.
								$topproject['Order']['id'].'/'.
								2
						)
					);
		?>
    </span>
    <?php
		if($topproject['Order']['status'] == 1) $Status_disc = __('Open this Order',true);
		if($topproject['Order']['status'] == 0) $Status_disc = __('Close this Order',true);
		?>
		</td>
		<td>
		<?php
		$color = '37a500';
		if($topproject['progress']['all'] == 0)	$bg_color = 'bbbbbb';
		if($topproject['progress']['all'] > 0)	$bg_color = '97df73';
		if($topproject['progress']['rep'] > 0)	{$bg_color = 'e6a533';$color = 'c93b20';}

		echo
		$topproject['progress']['ok'] + $topproject['progress']['rep'] . '/' . $topproject['progress']['all'] . ', ' .
		$topproject['progress']['prozent_mr'] .'%, ';
		if($topproject['progress']['rep'] > 0){
			echo $topproject['progress']['rep'] . ' ' . __('repairs',true);
		}
		;?>

		<?php echo $this->element('development/development_progress_bar',array('progress_all' => $topproject['progress']));?>

		</td>
    <td><?php
		echo $topproject['Order']['created'];
		?></td>
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
		$("div.ordersdevelopments input[type=radio]").change(function() {
				var data = $(this).serializeArray();
				data.push({name: "ajax_true", value: 1});
				data.push({name: "dialog", value: 1});

				$.ajax({
						type	: "POST",
						cache	: true,
						url		: "<?php echo $this->Html->url(array('controller'=>'developments','action'=>'orders',$this->request->projectID))?>",
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

		$("span.for_hasmenu_2").contextmenu({
			delegate: ".hasmenu_2",
			autoFocus: true,
			preventContextMenuForPopup: true,
			preventSelect: true,
			taphold: true,
			menu: [
				{
				title: "<?php echo __('Change status');?>",
				cmd: "status",
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
				title: "<?php echo __('Edit order');?>",
				cmd: "status",
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
				title: "<?php echo __('Open order');?>",
				cmd: "status",
				action :	function(event, ui) {
							$("#container").load("reportnumbers/index/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							},
				uiIcon: "qm_edit",
				disabled: false
				}
				],

			select: function(event, ui) {},
		});
	});
</script>

<?php  echo $this->JqueryScripte->LeftMenueHeight(); ?>
