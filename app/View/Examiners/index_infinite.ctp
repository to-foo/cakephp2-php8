<?php
	$i = 0;

	if(count($examiners) == 0) return;

	foreach($examiners as $_examiner):

		$class = ' class="infinite_sroll_item ' . $paging['tr_marker'];
		$title = null;

		if ($i++ % 2 == 0) {
			$class .= ' altrow ';
		}

		if($_examiner['Examiner']['active'] == 0){
			$class .= ' deactive ';
			$title .= ' title="'.__('This user is deactive',true).'" ';
		}

		$class .= '" ';

?>
<tr id="examiner_tr_<?php echo $_examiner['Examiner']['id'] ?>" data-page="<?php echo $paging['page']?>" <?php echo $class;?>>
<?php echo $this->element('examiner/examiner_link_table',array('_examiner' => $_examiner));?>
<?php echo $this->element('examiner/item_cells_table',array('_examiner' => $_examiner));?>
<?php echo $this->element('examiner/qualifications_in_table',array('_examiner' => $_examiner));?>
<?php echo $this->element('examiner/eyecheck_in_table',array('_examiner' => $_examiner));?>
<?php echo $this->element('examiner/monitorings_in_table',array('_examiner' => $_examiner));?>
</tr>
<?php endforeach; ?>
<?php
/*
$colspan = count($xml->section->item) + 4;

echo '<tr data-page="' . $paging['page'] . '" class="infinite_sroll_item '.$paging['tr_marker'].'" >';
echo '<td colspan="' . $colspan . '">';
echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')));
echo '</td>';
echo '<td>';
echo '</td>';
echo '</tr>';
*/
?>
<
<?php
echo $this->element('js/show_pdf_link');
echo $this->element('examiner/js/js_infinite_index',array('page' => $paging['page']));
if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true) echo $this->element('infinite/infinite_links');
?>
