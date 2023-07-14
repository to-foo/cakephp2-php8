<div class="clear"></div>
<div class="modalarea detail">
	<h2><?php echo __('Search examiners'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo h(__('firmenname')); ?></th>
			<th><?php echo $this->Paginator->sort('date_of_birth'); ?></th>
			<th><?php echo h(__('Certificate')); ?></th>
			<th class="actions">&nbsp;</th>
	</tr>
	<?php
	$i = 0;
	foreach ($results as $result):
	?>
	<tr>
		<td><?php
			$_varsArray = $this->request->projectvars['VarsArray'];
			$_varsArray[15] = $result['Examiner']['id'];
			echo $this->Html->link($result['Examiner']['name'].' '.$result['Examiner']['first_name'], array_merge(array('controller'=>'examiners', 'action'=>'overview'), $_varsArray), array('class'=>'ajax round'));
		?></td>
		<td><?php echo h($result['Testingcomp']['firmenname']);?></td>
		<td><?php echo h($result['Examiner']['date_of_birth']);?></td>
		<td><?php echo h(join(', ', Hash::extract($result, 'Certificate.{n}.testingmethod'))); ?></td>
		<td class="actions"></td>
	</tr>
<?php endforeach; ?>
	</table>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled mymodal'));
		echo $this->Paginator->numbers(array('separator' => '', 'class'=>'mymodal'));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled mymodal'));
	?>
	</div>
	<p class="paging_query">
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>
	</p>


</div>
<div class="clear" id="testdiv"></div>
<script type="text/javascript">
	$(document).ready(function(){
		$('th .mymodal, .paging .mymodal').data(<?php echo json_encode($this->request->data); ?>);
		$(document).tooltip({
			open: function( event, ui ) {
				ui.tooltip.html(ui.tooltip.text().replace(/[\r\n]+/,"<br />"));
			}
		});
		<?php
		if($this->request->data['type'] == 'assign') {
		?> 
			$(document).contextmenu({
				delegate: ".modalarea a",
				autoFocus: true,
				preventContextMenuForPopup: true,
				preventSelect: true,
				taphold: true,
				menu: [
					{
						title: "Anzeigen",
						cmd: "view",
						action :	function(event, ui) {
										$("#container").load(ui.target.attr('href').replace("setparent","view"), {
											"ajax_true": 1
										});
										$("#dialog").dialog().dialog("close");
									},
						uiIcon: "qm_edit"
					},
					{
						title: "-"
					},
					{
						title: "Bearbeiten",
						cmd: "edit",
						action :	function(event, ui) {
										$("#container").load(ui.target.attr('href').replace("setparent","edit"), {
											"ajax_true": 1
										});
										$("#dialog").dialog().dialog("close");
									},
						uiIcon: "qm_edit"
					},
					{
						title: "-"
					},
					{
						title: "Verknüpfen",
						cmd: "setparent",
						action :	function(event, ui) {
										if(test = $(this).attr("rel").toLowerCase().match(/^(set|remove)(parent|child)\[([0-9]+)\]$/i)) {
											data = {"type": test[2], "id": test[3], "ajax_true": 1};
										} else {
											return false;
										}

										$("#container").load(ui.target.attr('href').replace("setparent","edit"), data);
										$("#dialog").dialog().dialog("close");
									},
						uiIcon: "qm_edit"
					},
				]
			});		
		<?php
		}
		?> 
	});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
