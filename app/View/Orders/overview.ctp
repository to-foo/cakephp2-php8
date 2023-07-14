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

	echo $this->Html->link(__($CloseOpenOrderDisc, true), array(
			'controller' => 'orders', 
			'action' => 'overview',
			$this->request->projectID,$orderKat,$orderID,$CloseOpenOrderLink
		), array('class'=>'roundhaedline ajax'));
	?>
	</h2>
	<?php 
		if(isset($noopenorder) && $noopenorder ==  1){   
    		echo '<div class="error">';
			echo '<p>';
			echo __('Es existieren keine offenen Aufträge in dieser Kategorie.', true);
			echo '</p>';
			echo '</div>';
		}
	?>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('kks', __('KKS')); ?></th>
			<th><?php echo __('Messpunkte im Auftrag', true) ?></th>
			<th><?php echo __('Overview Reports', true) ?></th>
			<th><?php echo $this->Paginator->sort('bauteil', __('Component')); ?></th>
			<th><?php echo $this->Paginator->sort('created', __('Created', true)); ?></th>
			<th><?php echo __('State', true); ?></th>
			<th class="actions">&nbsp;</th>
	</tr>
	<?php
	$i = 0;
	foreach ($Orders as $order):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = 'altrow';
		}
		if($order['Order']['status'] == 1){
			$class = 'closed';
		}
		
	?>
	<tr class="<?php echo $class;?>">
		<td>
		<?php
		
		echo $this->Html->link(h(empty($order['Order']['kks']) ? '-' : $order['Order']['kks']), array(
				'controller' => 'reportnumbers', 
				'action' => 'index',
				$this->request->projectID, $orderKat, $order['Order']['id']
				), array('class'=>'round ajax'));?>	
		</td>
        <td>
		<?php 
			if($order['Order']['AT'] != null){echo 'AT: ' . h($order['Order']['AT']) . '; ';} 
			if($order['Order']['OV'] != null){echo 'OV: ' . h($order['Order']['OV']) . ' '; }
			if($order['Order']['UT-WD'] != null){echo 'UT-WD: ' . h($order['Order']['UT-WD']) . ' ';} 
			if($order['Order']['UT'] != null){echo 'UT: ' . h($order['Order']['UT']) . ' ';} 
			if($order['Order']['MT'] != null){echo 'MT: ' . h($order['Order']['MT']) . ' ';} 
			if($order['Order']['PT'] != null){echo 'PT: ' . h($order['Order']['PT']) . ' ';} 
			if($order['Order']['RT'] != null){echo 'RT: ' . h($order['Order']['RT']) . ' ';} 
			if($order['Order']['HT'] != null){echo 'HT: ' . h($order['Order']['HT']) . ' ';} 
			if($order['Order']['RFA'] != null){echo 'RFA: ' . h($order['Order']['RFA']) . ' ';} 
		?>
        </td>
		<td>
		<?php 
		echo $this->Html->link(__('Reports', true).' '.h($order['Order']['kks']), array(
				'controller' => 'reportnumbers', 
				'action' => 'results',
				$this->request->projectID, $orderKat, $order['Order']['id'], 1
				), array('class'=>'round modal'));?>	
		</td>
		<td><?php echo h($order['Order']['bauteil']); ?></td>
		<td><?php echo h($order['Order']['created']); ?>&nbsp;</td>
		<td>
		<?php 
		echo $this->Html->link(__('Change status ', true), 
			array('controller' => 'orders', 'action' => 'status', 
			$this->request->projectID, $orderKat, $order['Order']['id'], 1
			),
			array('class'=>'round modal')
		);
		?>
		</td>		
		<td class="actions">
			<?php 
			$this->request->projectvars['VarsArray'][3] = $order['Order']['id'];
			
			if($order['Order']['status'] == 0){ 			
				echo $this->Navigation->makeLink('orders','delete',__('Delete'),'icon icon_delete modal','delete_order',$this->request->projectvars['VarsArray']);
				echo $this->Navigation->makeLink('orders','edit',__('Edit'),'icon icon_edit modal',null,$this->request->projectvars['VarsArray']);
				echo $this->Navigation->makeLink('orders','view',__('Details'),'icon icon_view modal',null,$this->request->projectvars['VarsArray']);
			}
			else{
				echo $this->Navigation->makeLink('orders','view',__('Details'),'icon icon_view ajax',null,$this->request->projectvars['VarsArray']);
			}
			?>
		</td>		
	</tr>
<?php endforeach; ?>
	</table>
</div>
<div class="clear" id="testdiv"></div> 
<?php 	$text_change_status = __('Will you you change the state.', true);?>
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>

			<script type="text/javascript">
				$(document).ready(function(){

						$(".OrderStatus").change(function() {
							

						var dialogsmallOpts = {
								modal: false,
								width: 450,
								height: 250,
								autoOpen: false,
								draggable: true,
								resizeable: true
								};

							var data = $(".fakeform").serializeArray();
							data.push({name: "ajax_true", value: 1});
							data.push({name: "status", value: $(this).val()});
							data.push({name: "id", value: $(this).attr("title")});
							data.push({name: "projectID", value: <?php echo $projectID;?>});
							data.push({name: "orderKat", value: <?php echo $orderKat;?>});
							data.push({name: "orderID", value: <?php echo $orderID;?>});

							$("#dialog").dialog(dialogsmallOpts);
							
							$.ajax({
								type	: "POST",
								cache	: true,
								url		: '<?php echo $this->Html->url(array('action' => 'status', $projectID, $orderKat, $orderID)); ?>',
								data	: data,
								success: function(data) {
		    						$("#dialog").html(data);
									$("#dialog").dialog("open");
								}
							});


							return false;	
						});

					});
			</script>

