<?php
$i = 0; 
$action = $this->request->params['action'];
		
foreach($Supplier as $_examiner):

$class = null;

if ($i++ % 2 == 0) $class = ' class="altrow infinite_sroll_item ' . $paging['tr_marker'] . '"';

?>
<tr<?php echo $class;?>>
        <?php
		$x = 0;
		foreach($xml->section->item as $_key => $_xml){

			$ShowThis = false;
	
			foreach($_xml->show->children() as $__key => $__value){
				if(trim($__value) == $action){
					$ShowThis = true;
					break;
				}
			}

			if($ShowThis == false) continue;
			
			$x++;

			$class= null;
			
			if(!empty($_xml->class)) $class = trim($_xml->class);
			echo '<td class="'.$class.'">';
			echo '<span class="discription_mobil">';
			echo trim($_xml->description->$locale);
			echo '</span>';
			echo h((trim($_examiner[trim($_xml->model)][trim($_xml->key)])));
			echo '</td>';
		}
		?>

</tr>


<?php endforeach; ?>
<?php 
if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true){
	$colspan = $x -1;		
	echo '<tr>';
	echo '<td colspan="'. $colspan . '">';
	echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')));
	echo '</td>';
	echo '<td>';
	echo '</td>';
	echo '</tr>';
}

?>
<?php 
if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true){
	echo $this->element('infinite/infinite_links');
}
?>