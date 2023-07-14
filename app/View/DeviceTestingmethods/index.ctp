
<div class=" modalarea devicetestingmethods index inhalt">
	<h2><?php echo __('DeviceTestingmethods'); ?></h2>
<div class="quicksearch">
<?php
//if(isset($ControllerQuickSearch)){  
////	echo $this->Navigation->quickComponentSearching('quicksearch',$ControllerQuickSearch,false);
//	echo $this->Navigation->quickComponentSearching('quicksearch',$ControllerQuickSearch,Configure::read('DeviceManagerShowSummary'));
//}
//echo $this->Html->link(__('Add devicetestingmethod',true), array_merge(array('action' => 'add'), array()), array('class' => 'dialog modal icon icon_examiners_add','title' => __('Add devicetestingmethod',true)));
?>
</div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<div id="container_table_summary" class="current_content" >
<table id="table_infinite_sroll" class="table_resizable table_infinite_sroll">
	<tr>
			<th class="small_cell"><?php echo $this->Paginator->sort('verfahren', null, array('class'=>'mymodal')); ?></th>

	</tr>

<?php 
	$i = 0; 
		
	foreach($devicetestingmethods as $devicetestingmethod): 
            //if ($devicetestingmethod['DeviceTestingmethod']['deleted'] == 0):
		$class = null;

		if ($i++ % 2 == 0 )  {
			$class = ' class="altrow infinite_sroll_item"';
		}

		if($devicetestingmethod['DeviceTestingmethod']['active'] == 0){
			$class = ' class="deactive infinite_sroll_item" title="'.__('This Testmethod is deactive',true).'"';
		}
?>
<tr<?php echo $class;?>>
		<td class="small_cell">
		<span class="for_hasmenu1 weldhead">
		<?php  
                        echo $this->Html->link($devicetestingmethod['DeviceTestingmethod']['verfahren'],                       
			array_merge(array('action' => 'overview'), 
			$devicetestingmethod['DeviceTestingmethod']['_devicetestingmethod_link']), 
			array(
				'class'=>'dialog modal round icon_show hasmenu1 ',
				//'rev' => implode('/',$devicetestingmethod['DeviceTestingmethod']['_devicetestingmethod_link'])
			)
		); ?>
        </span>
        </td>
</tr>
<?php //endif; ?>
<?php endforeach; ?>
</table>
</div>
</div> 

<?php echo $this->JqueryScripte->ModalFunctions(); ?>

<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>

