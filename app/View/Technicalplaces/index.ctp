<div class="modalarea examiners index inhalt">
<h2>
<?php echo __('Technical Places'); ?> -> <?php echo $headline; ?>
</h2>
<div class="quicksearch">
<?php if(isset($ControllerQuickSearch)) echo $this->Navigation->quickComponentSearching('quicksearch',$ControllerQuickSearch,true);?>
<div class="clear"></div>
<?php echo $this->Navigation->makeLink('technicalplaces','add',__('Add new') . ' ' . $headline,'modal icon icon_add',null,$this->request->projectvars['VarsArray']); ?>
</div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if (isset( $technicalplaces)){
if(count($technicalplaces) == 0){
	echo '<div class="hint"><p>';
	echo __('No results available.',true);
	echo '</p></div>';
}
}
?>
<div id="container_summary" class="container_summary" ></div>
<?php echo $this->element('monitoring_legend');?>
<div id="container_table_summary" class="" >
<table class="table_resizable table_infinite_sroll">
	<tr>
<!--		<th class="small_cell"><?php // echo $this->Paginator->sort('intern_no', __('Intern-no.',true), array('class'=>'mymodal')); ?></th>	-->
			<?php
			foreach($xml->section->item as $_key => $_xml){
				if(trim($_xml->condition->key) != 'enabled' && empty($_xml->condition->link)) continue;

				$class = null;
				if(!empty($_xml->class)) $class = trim($_xml->class);
				echo '<th class="'.$class.'">';
				echo trim($_xml->description->$locale);
				echo '</th>';
			}
                        echo '<th class="'.$class.'">';

				echo '</th>';
			?>

	</tr>
	<?php
if(!isset($paging))$paging['tr_marker'] = null;

		$i = 0;

		if (isset($technicalplaces))foreach ($technicalplaces as $technicalplace):

		$class = null;

		if ($i++ % 2 == 0) {
			$class = ' class="infinite_sroll_item altrow ' . $paging['tr_marker'] . '"';
		}

		if($technicalplace['Technicalplace']['active'] == 0){
			$class = ' class="infinite_sroll_item deactive ' . $paging['tr_marker'] . '" title="'.__('This technical place is deactive',true).'"';
		}

		$this->request->projectvars['VarsArray'][13] = 0;
		$this->request->projectvars['VarsArray'][14] = 0;
		$this->request->projectvars['VarsArray'][16] = $technicalplace['Technicalplace']['id'];
	?>
	<tr<?php echo $class;?>>
        <?php

		foreach($xml->section->item as $_key => $_xml){

			if(trim($_xml->condition->key) != 'enabled' && empty($_xml->condition->link)) continue;
			$class= null;
			if(!empty($_xml->class)) $class = trim($_xml->class);
			echo '<td class="'.$class.'">';

			if(!empty($_xml->condition->link)){

		echo '<span class="for_hasmenu1 weldhead">';

		echo $this->Html->link($technicalplace[trim($_xml->model)][trim($_xml->key)],
			array_merge(array('controller' => trim($_xml->condition->link->controller), 'action' => trim($_xml->condition->link->action)),
			$this->request->projectvars['VarsArray']),
			array(
				'class'=> trim($_xml->condition->link->class),
				'rev' => implode('/',$this->request->projectvars['VarsArray'])
			)
		);
		echo '</span>';


			}
			if(empty($_xml->condition->link)){
				echo '<span class="discription_mobil">';
				echo trim($_xml->description->$locale);
				echo '</span>';
				echo h((trim($technicalplace[trim($_xml->model)][trim($_xml->key)])));
			}
			echo '</td>';
		}
		?>

        <td>

		</td>
	</tr>
<?php endforeach; ?>
<?php
if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true){
	$colspan = count($xml->section->item);
	echo '<tr>';
	echo '<td colspan="'. $colspan . '">';
	echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')));
	echo '</td>';
	echo '<td>';
	echo '</td>';
	echo '</tr>';
}
?>
</table>
</div>
</div>


<div class="clear" id="testdiv"></div>
<?php
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/form_accordion');
echo $this->element('js/form_datefield');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/ajax_send_modal_form');
echo $this->JqueryScripte->LeftMenueHeight(); ?>
