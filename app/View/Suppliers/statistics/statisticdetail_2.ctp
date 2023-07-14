<?php if(count($Output) == 0) return; ?>
<div class="expediting_statistic_text">
<h3><?php echo __('Overview of Expeditings',true);?></h3>
<?php 
if(count($Output) == 0){
	echo __('There are no data',true);
	echo '</div>';	
}
?>
<table class="advancetool">
<?php

foreach($Output['Output'] as $_key => $_data){

	$SupplierLink = $this->Html->link($_key,
    	array_merge(
 	     array(
 	       'controller' => 'suppliers',
 	       'action' => 'overview'
 	     )
 	     ,array($_data['Supplier']['topproject_id'],$_data['Supplier']['cascade_id'],$_data['Supplier']['id']),
 	   ),
 	   array(
 	     'title' => $_key,
 	     'class' => 'ajax round'
 	   )
  	);

	echo '<tbody>'; 

	echo $this->Html->tableHeaders(array($SupplierLink,__('critical',true),__('delayed',true),__('plan',true),__('future',true),__('finished',true)));

	foreach($_data['Expeditings'] as $__key => $__data){

		echo '<tr>';

		$Class = '';

		echo '<td>';
		echo '<span class="' . $Class . '"></span>';
		echo $__key;
		echo '</span>';
		echo '</td>';

		foreach($__data as $___key => $___data){

			if($___key == 'total') continue;

			if($___data > 0) $Class = $___key;

			echo '<td>';
			echo '<div class="flex">';
			echo '<p class="expediting_status ' . $Class . '">';
			echo $___data;
			echo '</p>';
			echo '</div>';
			echo '</td>';

			$Class = '';
	
		}			
	}
	
	echo '</tbody>';
}
?>
</table>
</div>