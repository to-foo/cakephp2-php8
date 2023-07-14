	<?php

		$i = 0;

		if (isset($documents))foreach ($documents as $examiner):

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
$colspan = 6;

echo '<tr>';
echo '<td colspan="' . $colspan . '">';
echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')));
echo '</td>';
echo '<td>';
echo '</td>';
echo '</tr>';
?>
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
	    $(".table_resizable tr").resizable();
	    $(".table_resizable td").resizable();

	}

	$(document).ready(function(){
		setscrollfunctions();
	});
</script>
<?php
if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true){
	echo $this->element('infinite/infinite_links');
}
?>
