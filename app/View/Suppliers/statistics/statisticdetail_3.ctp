<?php if(count($Supplier) == 0) return;?>
<div class="expediting_statistic_text">
<h3><?php echo __('Overview Suppliers',true);?></h3>
<?php 
if(count($Output) == 0){
	echo __('There are no data',true);
	echo '</div>';	
}

//pr($OutputDetail);	

foreach($Output as $_key => $_data){
	echo '<table>'; 
	echo $this->Html->tableHeaders(array($_key,__('critical',true),__('delayed',true),__('plan',true),__('future',true),__('finished',true)));
	foreach($_data as $__key => $__data){

		echo $this->Html->tableCells(
				array(
					array(
						$__key,
					array(
						$__data['critical'] . '/' . $__data['total'],
							array('class' => 'critical')
						),
					array(
						$__data['delayed'] . '/' . $__data['total'],
							array('class' => 'delayed')
						),
					array(
						$__data['plan'] . '/' . $__data['total'],
							array('class' => 'plan')
						),
					array(
						$__data['future'] . '/' . $__data['total'],
							array('class' => 'future')
						),
					array(
						$__data['finished'] . '/' . $__data['total'],
							array('class' => 'finished')
						),
					)
				)
			);
	}
	echo '</table>';
}
?>
</div>
