<div class="invoice detail">
<div class="clear edit">
<?php  echo $this->Navigation->showWaitingMenue($projectID,$orderID); ?>
</div>
<div class="clear"></div>
</div> 
<div class="clear" id="testdiv"></div>
<div class="waitings  inhalt">
	<h2><?php echo __('Waiting times');?></h2>
	<div>Bestellte Arbeitsleisung</div>
	<p class="ordered_work" id="ordered_work"><?php echo $Examiniers['generally']['ordered_work'] ;?></p>
	<?php
	$x = 0;
	foreach ($Examiniers as $key => $Examinier):
	
	if($key != 'generally'):
	
	?>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th colspan="9"><?php echo $key.' '.$Examinier['name'];?></th>
	</tr>
	
	<tr>
	<td><?php echo __('Report no.', true);?></td>
	<td><?php echo __('Date of test', true);?></td>
	<td><?php echo __('Waiting time beginning', true);?></td>
	<td><?php echo __('Waiting time ending', true);?></td>
	<td><?php echo __('Waiting time (h)', true);?></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td><?php echo __('Justification of the waiting time', true);?></td>
	<td><?php echo __('Aktive', true);?></td>
	</tr>

	<?php
	$i = 0;
	$xx = 0;
	if(is_array($Examinier['data'])){
	foreach ($Examinier['data'] as $_key => $_Examinier){

		$class = null;
		if ($i++ % 2 == 0) {
			$class = 'altrow';
		}

		$overlap_mark = null;
		
		if(count($_Examinier['overlap']) > 0 && $_Examinier['active'] == 1){
			foreach($_Examinier['overlap'] as $_overlap){
				if($Examinier['data'][$_overlap]['active'] == 1){
					$overlap_mark = 1;
				}
			}
		}
		
		if($overlap_mark == 1){
			$class = ' doubles ';
		}

		if($_Examinier['active'] != 1){
			$class = ' inactiv ';
		}
		
		echo '<tr class="'.$class.'" id="tr'.$x.$xx.'">';
		echo '<td>';
		echo $this->Html->link($_Examinier['verfahren'].'-'.$_Examinier['number'], array(
					'controller' => 'reportnumbers', 
					'action' => 'edit', $_Examinier['reportnumber_id']
					)
				);

		echo '</td>';
		echo '<td>'.$_Examinier['date_of_test'].'</td>';
		echo '<td>'.$_Examinier['waiting_start'].'</td>';
		echo '<td>'.$_Examinier['waiting_stop'].'</td>';
		echo '<td>'.$_Examinier['waiting_time'].'</td>';
		echo '<td>';

		if($overlap_mark == 1){
			
			echo '<span class="doubles">';
			echo __('This waiting time collides with the waiting time of the following inspection reports', true);
			echo ': ';
			foreach($_Examinier['overlap'] as $_overlap){
				
				echo $this->Html->link($Examiniers[$key]['data'][$_overlap]['verfahren'].'-'.$Examiniers[$key]['data'][$_overlap]['number'], array(
					'controller' => 'reportnumbers', 
					'action' => 'edit', $Examiniers[$key]['data'][$_overlap]['reportnumber_id']
					)
				);
				echo ' ';

			}
			echo '</span>';
		}
		echo '</td>';
		echo '<td>';
		if(isset($_Examinier['reference']) && $_Examinier['reference'] != ''){
			echo '<span class="doubles">';
			echo $_Examinier['reference'];
			echo '</span>';
		}
		echo '</td>';
		echo '<td>';
		echo '<span class="';
		echo 'edit_reason';
		echo '" id="'.$projectID.'_'.$orderID.'_'.$_key.'_'.$x.'_'.$xx.'" name="'.$key.'" >'.$_Examinier['reason'].'</span>';
		echo '</td>';
		echo '<td>';

		if($_Examinier['active'] == 1){
			$checked = ' checked="checked" ';
		}
		else {
			$checked = null;
		}
				
		echo $this->Form->checkbox('check'.$x.$xx, array(
							'title'	=> $key,
							'class'	=> 'checkbox',
							'formaction' => Router::url(array('controller'=>'invoices','action'=>'change', $projectID, $orderID, $_key, $x, $xx)),
							$checked
						)
					);

		echo '</td>';
		echo '</tr>';
	$xx++;
	}
	}
	?>
	</table> 
	<?php 	$x++; endif; endforeach;?>
</div>
<div class="clear" id="hiddenaction"></div>
<?php if(isset($afterEDIT)){echo $afterEDIT;} ?>
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>
<script>
$(function() {

	$(".edit_reason").editable("<?php echo Router::url(array('controller'=>'invoices','action'=>'remark'));?>", { 
		indicator : "<img src='img/indicator.gif'>",
		type   : 'textarea',
		name   : 'remark',
		id   : 'elementid',
		select : true, 
		onblur : 'submit',
		submitdata : {
				ajax_true: 1,
			},
		cssclass : "editable",
		method : 'POST',
  });

	$(".ordered_work").editable("<?php echo Router::url(array('controller'=>'invoices','action'=>'workreason'));?>", { 
		indicator : "<img src='img/indicator.gif'>",
		type   : 'textarea',
		name   : 'remark',
		id   : 'elementid',
		select : true, 
		onblur : 'submit',
		submitdata : {
				ajax_true: 1,
			},
		cssclass : "editable",
		method : 'POST',
  });
  
	$(".checkbox").change(function() {
		$("#hiddenaction").load($(this).attr("formaction"), {
							"ajax_true": 1,
							"examinierer": $(this).attr("title")
							}
						);
	}); 
});
</script>
