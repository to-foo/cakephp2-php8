<div class="actions" id="top-menue">
	<?php 
	echo $this->Navigation->quickSearch(); 
	?>
	<h3><?php __('Actions'); ?></h3>
	<ul><?php echo $this->Navigation->showNavi($menues); ?></ul>
</div>

<div class="orders index inhalt">
	<h2>
	<?php 
	echo $haedline.' ('.$countOrder.')';
	$this->request->projectvars['VarsArray'][2] = $CloseOpenOrderLink;
	echo $this->Navigation->makeLink('orders','index',__($CloseOpenOrderDisc, true),'roundhaedline ajax',null,$this->request->projectvars['VarsArray']);
	
	?>
	</h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('order-number'); ?></th>
			<th><?php echo __('Overview KKS-No.', true) ?></th>
			<th><?php echo __('Overview measure points', true) ?></th>
	</tr>
	<?php
	$i = 0;
	
	foreach ($orders as $order):

		$class = null;
		if ($i++ % 2 == 0) {
			$class = 'altrow';
		}
		
	?>
	<tr class="<?php echo $class;?>">
		<td>
		<?php
		$kksNrLinks = array();

		foreach($order['Order']['kks'] as $_kks){
			// Wenn der Auftrag geschossen wurde
			if($_kks['Order']['status'] == 1){
				$closeClass = array('class'=>'closed ajax');
			}
			else {
				$closeClass = array('class'=>'ajax');
			}
			
			if(empty($_kks['Order']['kks'])) $_kks['Order']['kks'] = '-';

			$kksNrLinks[] = $this->Html->link(h($_kks['Order']['kks']), array(
				'controller' => 'reportnumbers', 
				'action' => 'index',
				$this->request->projectID, $orderKat, $_kks['Order']['id']
				),
				$closeClass
			);	
		}
		echo $this->Html->link(__('Order', true).' '.h($order['Order']['auftrags_nr']), array(
				'controller' => 'orders', 
				'action' => 'overview',
				$this->request->projectID, $orderKat, $order['Order']['id'], $CloseOpenOrder
				), array('class'=>'round ajax'));	
		?>
		</td>
		<td class="sublink_table">
		<?php
		foreach($kksNrLinks as $_kksNrLinks){
			echo $_kksNrLinks.' '; 
		}
		$kksNrLinks = array();
		?>
		</td>
		<td>
		<?php 
		echo $this->Html->link(__('measure points', true), array(
				'controller' => 'orders', 
				'action' => 'reports',
				$this->Session->read('projectURL'), $orderKat, $order['Order']['id']
				), array('class'=>'round modal'));?>	
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
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>
<?php 	$text_change_status = __('If you change the state, it can not be reversed.', true);?>


<script type="text/javascript">
	$(document).ready(function(){

	$(".OrderStatus").change(function() {
							
		checkStatus = confirm("<?php echo $text_change_status;?>");
							
		if (checkStatus == false) {
			return false;	
		}

		var data = $(".fakeform").serializeArray();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "status", value: $(this).val()});
		data.push({name: "id", value: $(this).attr("title")});
		data.push({name: "projectID", value: <?php echo $projectID;?>});
							
		$.ajax({
			type	: "POST",
			cache	: true,
			url		: '<?php echo $this->Html->url(array('action' => 'status')); ?>',
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
