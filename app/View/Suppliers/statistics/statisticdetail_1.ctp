<div class="expediting_statistic_text">
<h3><?php echo __('Overview of orders',true);?></h3>
<?php 

if(count($Output) == 0){
	echo __('There are no data',true);
	echo '</div>';	
}

echo '<table>';
echo $this->Html->tableHeaders(array(
							__('Unit',true),
							__('Count of ordered Orders',true),
							__('Count of open Orders',true),
							__('Total count of Orders',true),
						)
					);

echo $this->Html->tableCells(array(
 	array(
		$Output['Cascade'],
		$Output['ordered'],
		$Output['not_ordered'],
		$Output['all_orders']
		),
	)
);

echo '</table>';
?>
</div>