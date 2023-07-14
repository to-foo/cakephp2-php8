	<?php 

		$i = 0;
				
		if (isset($technicalplaces))foreach ($technicalplaces as $technicalplace):

		$class = null;

		if ($i++ % 2 == 0) {
			$class = ' class="infinite_sroll_item altrow"';
		}

		if($technicalplace['Technicalplace']['active'] == 0){
			$class = ' class="infinite_sroll_item deactive" title="'.__('This device is deactive',true).'"';
		}
		
		$this->request->projectvars['VarsArray'][13] = 0;
		$this->request->projectvars['VarsArray'][14] = 0;
		$this->request->projectvars['VarsArray'][16] = $technicalplace['Technicalplace'];
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

      
	</tr>
<?php endforeach; ?>
<?php 
$colspan = count($xml->section->item);		

echo '<tr>';
echo '<td colspan="' . $colspan . '">';
echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')));
echo '</td>';
echo '<td>';
echo '</td>';
echo '</tr>';
?>

		

<?php 
if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true){
	echo $this->element('infinite/infinite_links');
}
?>