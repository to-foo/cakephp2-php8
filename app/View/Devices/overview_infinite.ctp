	<?php

		$i = 0;

		if (isset($devices))foreach ($devices as $examiner):

		$class = null;

		if ($i++ % 2 == 0) {
			$class = ' class="infinite_sroll_item altrow"';
		}

		if($examiner['Device']['active'] == 0){
			$class = ' class="infinite_sroll_item deactive" title="'.__('This device is deactive',true).'"';
		}

		$this->request->projectvars['VarsArray'][13] = 0;
		$this->request->projectvars['VarsArray'][14] = 0;
		$this->request->projectvars['VarsArray'][16] = $examiner['Device']['id'];
	?>
	<tr<?php echo $class;?>>
        <?php
		foreach($xml->section->item as $_key => $_xml){
//			if(trim($_xml->condition->key) != 'enabled') continue;
			if(trim($_xml->condition->key) != 'enabled' && empty($_xml->condition->link)) continue;
			$class= null;
			if(!empty($_xml->class)) $class = trim($_xml->class);
			echo '<td class="'.$class.'">';

			if(!empty($_xml->condition->link)){

		echo '<span class="for_hasmenu1 weldhead">';

		echo $this->Html->link($examiner[trim($_xml->model)][trim($_xml->key)],
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
				echo h((trim($examiner[trim($_xml->model)][trim($_xml->key)])));
			}
			echo '</td>';
		}
		?>

        <td>
        <span class="discription_mobil">
		<?php echo __('Monitorings'); ?>:
		</span>
        <span class="summary_span">
        <?php

		if(isset($monitorings[$examiner['Device']['id']]['summary']['monitoring'])){

			$thissummary = array();

			foreach($monitorings[$examiner['Device']['id']]['summary']['monitoring'] as $_key => $_qualifications){

				$thissummary = $this->Quality->MonitoringSummarySingle($_key,$_qualifications);

				echo '<div class="container_summary_single container_summary_single_'.$_key.'_' . $examiner['Device']['id'] . '">';
				echo $thissummary;
				echo '</div>';

				$thissummary = null;

				$this_link = $this->request->projectvars['VarsArray'];
				$this_link[17] = $_qualifications[key($_qualifications)][$examiner['Device']['id']]['info']['device_certificate_id'];
				$this_link[18] = $_qualifications[key($_qualifications)][$examiner['Device']['id']]['info']['id'];

				echo $this->Html->link($_key,array_merge(array('action' => 'monitorings'),$this_link),array('title' => $_key,'rev'=> $_key,'rel'=> $examiner['Device']['id'], 'class' => 'summary_tooltip ajax icon monitoring_'.$_key));

			}
		}
		?>
        </span>
		</td>


<tr data-page="<?php echo $paging['page']?>" <?php echo $class;?>>
</tr>
<?php endforeach; ?>
<?php
$colspan = count($xml->section->item) + 4;
echo '<tr data-page="' . $paging['page'] . '" class="infinite_sroll_item '.$paging['tr_marker'].'" >';
echo '<td colspan="' . $colspan . '">';
echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')));
echo '</td>';
echo '<td>';
echo '</td>';
echo '</tr>';
?>
<
<?php
echo $this->element('devices//js_infinite_overview',array('page' => $paging['page']));
if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true) echo $this->element('infinite/infinite_links');
?>

?>
