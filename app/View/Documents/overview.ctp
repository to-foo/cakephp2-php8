<div class="modalarea examiners index inhalt">
<h2>
<?php

if(isset($testingmethod)){
	echo $testingmethod['DocumentTestingmethod']['verfahren'];
}
?> -
<?php echo __('Documents'); ?>
</h2>
<div class="quicksearch">
<?php // echo $this->element('barcode_scanner');?>
<?php
if(isset($ControllerQuickSearch)){
	echo $this->Navigation->quickComponentSearching('quicksearch',$ControllerQuickSearch,false);
}
echo $this->Html->link(__('Add document',true), array_merge(array('action' => 'add'), array()), array('class' => 'modal icon icon_documents_add','title' => __('Add document',true)));

?>
</div>
<?php echo $this->element('Flash/_messages');?>
<?php
if(count($documents) == 0){
	echo '<div class="hint"><p>';
	echo __('No results available.',true);
	echo '</p></div>';
}
?>
<div id="container_summary" class="container_summary" ></div>
<div id="container_table_summary" class="" >
<table class="table_resizable table_infinite_sroll">
	<tr>
			<th class="small_cell"><?php echo __('Dokument-Bezeichnung',true); ?></th>
			<th><?php echo __('Dokument-Titel',true); ?></th>

			<th class="small_cell"><?php echo __('Dokument-Art',true); ?></th>
			<th class="small_cell"><?php echo __('Ausgabe',true); ?></th>
			<th class="small_cell"><?php echo __('Datum',true); ?></th>
      <th class="small_cell"><?php echo __('Anzeigen',true); ?></th>
	</tr>
	<?php
if(!isset($paging))$paging['tr_marker'] = null;

		$i = 0;

		foreach ($documents as $examiner):

		$class = null;

		if ($i++ % 2 == 0) {
			$class = ' class="infinite_sroll_item altrow"';
		}

		if($examiner['Document']['active'] == 0){
			$class = ' class="infinite_sroll_item deactive" title="'.__('This document is deactive',true).'"';
		}

		$this->request->projectvars['VarsArray'][13] = 0;
		$this->request->projectvars['VarsArray'][14] = 0;
		$this->request->projectvars['VarsArray'][16] = $examiner['Document']['id'];
	?>
	<tr<?php echo $class;?>>

		<td class="small_cell">
		<span class="for_hasmenu1 weldhead">
		<?php
		echo $this->Html->link($examiner['Document']['name'],
			array_merge(array('action' => 'view'),
			$this->request->projectvars['VarsArray']),
			array(
				'class'=>'round icon_show ajax hasmenu1',
				'rev' => implode('/',$this->request->projectvars['VarsArray'])
			)
		);
		?>
        </span>
        </td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Dokument-Titel'); ?>:
		</span>
		<?php echo h($examiner['Document']['working_place']); ?>&nbsp;
        </td>
         <td class="small_cell">
        <span class="discription_mobil">
		<?php echo __('Dokument-Bezeichnung'); ?>:
		</span>
        <span class="summary_span">
		<?php echo h($examiner['Document']['document_type']); ?>&nbsp;
        </span>
		</td>
        <td class="small_cell">
        <span class="discription_mobil">
		<?php echo __('Ausgabe'); ?>:
		</span>
        <span class="summary_span">
        <?php echo $examiner['Document']['webplan'];?>
        </span>
		</td>
        <td class="small_cell">
        <span class="discription_mobil">
		<?php echo __('Ausgabe'); ?>:
		</span>
        <span class="summary_span">
        <?php echo $examiner['Document']['first_registration'];?>
        </span>
		</td>


        <td class="small_cell">
        <?php

		if($examiner['Document']['certified_file_valide'] == 1){
			echo $this->Html->link(__('Show file',true), array_merge(array('action' => 'getcertificatefile'), $this->request->projectvars['VarsArray']), array('target' => '_blank','class' => 'icon icon_download_file','title' => __('Show file',true)));
		}
		if($examiner['Document']['certified_file_valide'] == 0){
			echo $this->Html->link(__('Upload',true), array_merge(array('action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('title' => __('Upload',true),'class' => 'modal icon icon_upload_red'));

		}
		?>
		</td>

	</tr>
<?php endforeach; ?>
<?php
if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true){
	$colspan = 9;
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

<script type="text/javascript">
	function setscrollfunctions(){
		$("div.container_summary_single").hide();

		$("a.summary_tooltip").tooltip({
			content: function () {
				var output = $(".container_summary_single_" + $(this).prop('rev') + "_" +$(this).prop('rel')).html();
				return output;
			}
		});

	    var onSampleResized = function (e) {
	        var columns = $(e.currentTarget).find("td");
	        var rows = $(e.currentTarget).find("tr");
	        var Cloumnsize;
	        var rowsize;
	        columns.each(function () {
	            Cloumnsize += $(this).attr('id') + "" + $(this).width() + "" + $(this).height() + ";";
	        });
	        rows.each(function () {
	            rowsize += $(this).attr('id') + "" + $(this).width() + "" + $(this).height() + ";";
	        });
	        document.getElementById("hf_columndata").value = Cloumnsize;
	        document.getElementById("hf_rowdata").value = rowsize;
	    };

	    $(".table_resizable th").resizable();

	}

	$(document).ready(function(){
		setscrollfunctions();
	});
</script>
<?php

echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/ajax_paging');
if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true){
//	echo $this->element('infinite/infinite_links');
//	echo $this->element('infinite/infinite_scroll');
}
if(!Configure::check('InfiniteScroll') || (Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == false)){
	echo $this->element('page_navigation');
}

//echo $this->JqueryScripte->LeftMenueHeight();
?>
