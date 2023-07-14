<div class="welders index inhalt">
	<h2><?php echo __('Welder Supervisor'); ?></h2>
<div class="quicksearch">

<?php
echo $this->element('searching/search_quick_welder',
	array(
		'target_id' => 'id',
		'targedaction' => 'overview',
		'action' => 'quicksearch',
		'minLength' => 2,
		'discription' => __('Name', true)
		)
	);

echo $this->Html->link(__('Add welder',true), array_merge(array('action' => 'add'),$this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_examiners_add','title' => __('Add welder',true)));
echo $this->Html->link(__('Print deactive welder list',true), array_merge(array('action' => 'pdfinv'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_devices_pdf_negative','title' => __('Print deactive welder list',true)));
 $this->request->projectvars['VarsArray'][17] = 1;
echo $this->Html->link(__('Print active welder list',true), array_merge(array('action' => 'pdfinv'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_devices_pdf','title' => __('Print active welder list',true)));
$this->request->projectvars['VarsArray'][17] = 0;?>

</div>


<?php echo $this->element('Flash/_messages');?>
<div id="container_table_summary" class="current_content" >
<?php echo $this->element('qualification_legend');?>

<table id="" class="table_resizable table_infinite_sroll">
	<tr>
			<th class="small_cell"><?php echo $this->Paginator->sort('Name', null, array('class'=>'mymodal')); ?></th>
			<?php
			foreach($xml->section->item as $_key => $_xml){
				if(trim($_xml->condition->key) != 'enabled') continue;
				$class = null;
				if(!empty($_xml->class)) $class = trim($_xml->class);
				echo '<th class="'.$class.'">';
				echo trim($_xml->description->$locale);
				echo '</th>';
			}
			?>
            <th><?php echo __('qualifications',true); ?></th>
             <th><?php echo __('Welder Tests',true); ?></th>


	</tr>

<?php
if(!isset($paging))$paging['tr_marker'] = null;

	$i = 0;

	foreach($welders as $_welder):

		$class = null;

		if ($i++ % 2 == 0) {
			$class = ' class="altrow infinite_sroll_item ' . $paging['tr_marker'] . '"';
		}

		if($_welder['WelderActive']['active'] == 0){
			$class = ' class="deactive infinite_sroll_item ' . $paging['tr_marker'] . '" title="'.__('This user is deactive',true).'"';
		}
               // $this->request->projectvars['VarsArray'][13] = 0;
		$this->request->projectvars['VarsArray'][14] = $_welder['Welder']['testingcomp_id'];
                $this->request->projectvars['VarsArray'][15] = $_welder['Welder']['id'];
?>
<tr<?php echo $class;?>>
		<td class="small_cell">
			<span class="for_hasmenu1 weldhead">
			<?php echo $this->Html->link($_welder['Welder']['fullname'],
				array_merge(array('action' => 'overview'),
				$_welder['Welder']['_welder_link']),
				array(
					'class'=>'round icon_show ajax hasmenu1',
					'rev' => implode('/',$_welder['Welder']['_welder_link'])
				)
			);  ?>
	    </span>
    </td>
    <?php

		foreach($xml->section->item as $_key => $_xml){

			if(trim($_xml->condition->key) != 'enabled') continue;
			$class= null;
      if(isset($_xml->fieldtype) && trim($_xml->fieldtype) == 'radio'){
      	$_welder[trim($_xml->model)][trim($_xml->key)] =  $_xml->radiooption->value[$_welder[trim($_xml->model)][trim($_xml->key)]];
      }
			if(!empty($_xml->class)) $class = trim($_xml->class);
				//var_dump(trim($_xml->key));

				if(trim($_xml->key) == 'active') {
					echo '<td class="'.$class.'">';
					echo '<span class="discription_mobil">';
					echo trim($_xml->description->$locale);
					echo '</span>';
					echo $this->Html->link(__(trim($_welder[trim($_xml->model)][trim($_xml->key)]),true),'javascript:',array('id'=> $_welder['Welder']['id'],'class'=>'WelderStateLink round icon_show','title' => __('Description',true),'rel' => ''));

				//	echo $this->Html->link(__(h((trim($_welder[trim($_xml->model)][trim($_xml->key)]))),true), array_merge(array('controller' => 'welders','action' => 'index'),$this->request->projectvars['VarsArray']), array('id'=> $_welder['Welder']['id'],'class' => 'WelderStateLink round icon_show ajax','title' => __('Add welder',true)));
					echo '</td>';
					echo '</span>';
					echo '</td>';
				}else {

				echo '<td class="'.$class.'">';
				echo '<span class="discription_mobil">';
				echo trim($_xml->description->$locale);
				echo '</span>';
				echo h((trim($_welder[trim($_xml->model)][trim($_xml->key)])));
				echo '</td>';
			}
		}
		?>

  <td>
        <span class="discription_mobil">
		<?php echo __('Qualification'); ?>:
		</span>
        <span class="summary_span">
        <?php

		if(isset($monitorings[$_welder['Welder']['id']]['summary']['monitoring'])){
			$thissummarymon = array();

			foreach($monitorings[$_welder['Welder']['id']]['summary']['monitoring'] as $_mkey => $_mqualifications){

                                //$thissummarymonotpring = null;
				$thissummarymon = $this->Quality->MonitoringSummarySingle($_mkey,$_mqualifications);
                                //pr($_mqualifications);
                               // pr($thissummary);
				echo '<div class="container_monsummary_single container_summarymon_single_'.$_mqualifications[key($_mqualifications)][$_welder['Welder']['id']]['info']['welder_monitoring_id']. '">';
				echo $thissummarymon;
				echo '</div>';

				$thissummarymon = null;
				//pr($_mqualifications);
				$this_mlink = $this->request->projectvars['VarsArray'];
				$this_mlink[16] = $_mqualifications[key($_mqualifications)][$_welder['Welder']['id']]['info']['welder_monitoring_id'];
				$this_mlink[17] = $_mqualifications[key($_mqualifications)][$_welder['Welder']['id']]['info']['id'];

				echo $this->Html->link($_mkey,array_merge(array('action' => 'monitorings'),$this_mlink),array('title' => $_mkey,'rev'=> $_mkey,'rel'=>$_mqualifications[key($_mqualifications)][$_welder['Welder']['id']]['info']['welder_monitoring_id'], 'class' => 'summarymon_tooltip ajax icon monitoring_'.$_mkey));

			}
		}
		?>
        </span>
	</td>
	<td>
        <span class="discription_mobil">
		<?php echo __('Welder Tests'); ?>:
		</span>

        <?php if(isset($_welder['WelderTest'])) echo $this->Quality->CollectWelderTestInfo($_welder);?>
	</td>
</tr>
<?php endforeach; ?>

<?php

if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true){
	$colspan = count($xml->section->item) + 2;
	echo '<tr>';
	echo '<td colspan="'. $colspan . '">';
	echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')));
	echo '</td>';
	echo '<td>';
	echo '</td>';
	echo '</tr>';
}
?>
</tr>
</table>





<?php

echo $this->element('weldersummary');

if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true){
	echo $this->element('infinite/infinite_links');
	echo $this->element('infinite/infinite_scroll');
}

if(!Configure::check('InfiniteScroll') || (Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == false)){
	echo $this->element('page_navigation');
	echo $this->element('js/ajax_paging');
}

echo $this->element('welder/welderstate');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/tooltip_welder_test');

?>

</div>
</div>
