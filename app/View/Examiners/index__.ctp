<div class="examiners index inhalt">
	<h2><?php echo __('Examiners'); ?></h2>
<div class="quicksearch">
<?php if(isset($ControllerQuickSearch)) echo $this->element('searching/search_quick_examiner',array('target_id' => 'examiner_id','targedaction' => 'overview','action' => 'quicksearch','minLength' => 2,'discription' => __('Examiner last name', true)));?>

</div>
<?php echo $this->element('Flash/_messages');?>
<div id="container_summary" class="container_summary" ></div>
<div id="container_table_summary" class="current_content" >
	<table id="examiner_table" cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('name', null, array('class'=>'mymodal')); ?></th>
			<th><?php echo __('Qualifications',true); ?></th>
			<th><?php echo __('Eye checks',true); ?></th>
			<th><?php echo $this->Paginator->sort('working_place', __('Werkstatt'), array('class'=>'mymodal')); ?></th>
			<th><?php echo $this->Paginator->sort('testingcomp_id', null, array('class'=>'mymodal')); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($examiners as $examiner):

		$class = null;

		if ($i++ % 2 == 0) {
			$class = ' class="altrow post-item"';
		}

		if($examiner['Examiner']['active'] == 0){
			$class = ' class="deactive post-item" title="'.__('This user is deactive',true).'"';
		}

		$this->request->projectvars['VarsArray'][13] = 0;
		$this->request->projectvars['VarsArray'][14] = 0;
		$this->request->projectvars['VarsArray'][15] = $examiner['Examiner']['id'];
		unset($this->request->projectvars['VarsArray'][16]);
	?>
	<tr<?php echo $class;?>>

		<td>
		<span class="for_hasmenu1 weldhead">
		<?php echo $this->Html->link($examiner['Examiner']['name'] . ' ' . $examiner['Examiner']['first_name'],
			array_merge(array('action' => 'overview'),
			$this->request->projectvars['VarsArray']),
			array(
				'class'=>'round icon_show ajax hasmenu1',
				'rev' => implode('/',$this->request->projectvars['VarsArray'])
			)
		); ?>
        </span>
        </td>
        <td>
        <span class="discription_mobil">
		<?php echo __('Qualifications'); ?>:
		</span>
        <span class="summary_span">
		<?php

		if(isset($summary[$examiner['Examiner']['id']]['summary']['qualifications'])){

			$thissummary = array();

			foreach($summary[$examiner['Examiner']['id']]['summary']['qualifications'] as $_key => $_qualifications){

				foreach($summary[$examiner['Examiner']['id']]['summary']['summary'] as $__key => $_summary){
					if(count($_summary) > 0){
						$thissummary[$_qualifications['certificate_id']] = $this->Quality->CertificatSummarySingle($summary[$examiner['Examiner']['id']],$_qualifications['certificate_id'],$_qualifications['certificate_data_id']);
					}
				}

				if(isset($thissummary[$_qualifications['certificate_id']]) && $thissummary[$_qualifications['certificate_id']] != false){
					echo '<div class="container_summary_single container_summary_single_'.$_qualifications['certificate_id'].'">';
					echo $thissummary[$_qualifications['certificate_id']];
					echo '</div>';
				}
				else {
					echo '<div class="container_summary_single container_summary_single_'.$_qualifications['certificate_id'].'">';
					echo $_qualifications['qualification'];
					echo '</div>';
				}

				echo $this->Html->link($_qualifications['qualification'],array_merge(array('action' => 'certificate'),$_qualifications['term']),array('title' => $_qualifications['qualification'],'rel'=> $_qualifications['certificate_id'], 'class' => 'summary_tooltip modal icon '.$_qualifications['class']));
			}
		}
		?>
        </td>
        <td>
        <span class="discription_mobil">
		<?php echo __('Eye checks'); ?>:
		</span>
        <span class="summary_span">
		<?php
		if(isset($summary[$examiner['Examiner']['id']]['eyecheck']['qualifications']) && count($summary[$examiner['Examiner']['id']]['eyecheck']['qualifications']) > 0){

			$thissummary = array();

			foreach($summary[$examiner['Examiner']['id']]['eyecheck']['qualifications'] as $_key => $_eyecheck){
				foreach($summary[$examiner['Examiner']['id']]['eyecheck']['summary'] as $__key => $__summary){
					if(count($__summary) > 0){
						$thissummary[$_eyecheck['certificate_id']] = $this->Quality->EyecheckSummarySingle($summary[$examiner['Examiner']['id']],$_eyecheck['certificate_id'],$_eyecheck['certificate_data_id']);
					}
				}
				if(isset($thissummary[$_eyecheck['certificate_id']]) && $thissummary[$_eyecheck['certificate_id']] != false){
					echo '<div class="container_eyechecksummary_single container_summaryeyecheck_single_'.$_eyecheck['certificate_id'].'">';
					echo $thissummary[$_eyecheck['certificate_id']];
					echo '</div>';
				}
				else {
					echo '<div class="container_eyechecksummary_single container_summaryeyecheck_single_'.$_eyecheck['certificate_id'].'">';
					echo $_eyecheck['certificat'];
					echo '</div>';
				}
			}

			echo $this->Html->link($_eyecheck['certificat'],array_merge(array('action' => 'eyechecks'),$_eyecheck['term']),array('title' => $_eyecheck['certificat'],'rel'=> $_eyecheck['certificate_id'], 'class' => 'summaryeyecheck_tooltip mymodal icon '.$_eyecheck['class']));

		}

		?></span>
		</td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Werkstatt'); ?>:
		</span>
		<?php echo h($examiner['Examiner']['working_place']); ?>&nbsp;
        </td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Testingcompany'); ?>:
		</span>
		<?php echo $this->Html->link($examiner['Testingcomp']['name'], array('controller' => 'testingcomps', 'action' => 'view', $examiner['Testingcomp']['id']), array('class' => 'mymodal')); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<div class="paging">
	<?php
		echo $this->Paginator->next(__('Weitere Einträge werden geladen.',true));
//		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
//		echo $this->Paginator->numbers(array('separator' => ''));
//		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
	<p class="paging_query">
	<?php
//	echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')));
	?>	</p>
</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){

	var $container = $('#examiner_table');

		$container.infinitescroll({
		  navSelector  : '.next',    // selector for the paged navigation
		  nextSelector : '.next a',  // selector for the NEXT link (to page 2)
		  itemSelector : '.post-item',     // selector for all items you'll retrieve
		  debug		 	: true,
		  dataType	 	: 'html',
		  loading: {
			  finishedMsg: "<?php echo __('Das Ende der Liste wurde erreicht.',true);?>",
			  img: '<?php echo $this->webroot; ?>img/indicator.gif'
			}
		  }
		);

		$("div.container_summary_single").hide();
		$("div.container_eyechecksummary_single").hide();

		$("a.summary_tooltip").tooltip({
			content: function () {
				var output = $(".container_summary_single_" + $(this).prop('rel')).html();
				return output;
			}
		});

		$("a.summaryeyecheck_tooltip").tooltip({
			content: function () {
				var output = $(".container_summaryeyecheck_single_" + $(this).prop('rel')).html();
				return output;
			}
		});

		$("span.for_hasmenu1").contextmenu({
			delegate: ".hasmenu1",
			autoFocus: true,
			preventContextMenuForPopup: true,
			preventSelect: true,
			taphold: true,
			menu: [
				{
				title: "<?php echo __('Edit');?>",
				cmd: "status",
				action :	function(event, ui) {
							$("#dialog").load("examiners/edit/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#container").dialog("open");
							},
				uiIcon: "qm_edit",
				disabled: false
				},
				{
				title: "<?php echo __('Qualifications');?>",
				cmd: "status",
				action :	function(event, ui) {
							$("#dialog").load("examiners/certificates/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
				uiIcon: "qm_certificate",
				disabled: false
				},
				{
				title: "<?php echo __('Vision tests');?>",
				cmd: "status",
				action :	function(event, ui) {
							$("#dialog").load("examiners/eyechecks/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
				uiIcon: "qm_eyecheck",
				disabled: false
				},
				{
					title: "----"
				},
				{
				title: "<?php echo __('Delete');?>",
				cmd: "status",
				action :	function(event, ui) {
/*
								checkDuplicate = confirm("<?php echo __('Soll dieser Prüfer gelöscht werden?');?>");
								if (checkDuplicate == false) {
									return false;
								}
*/
							$("#dialog").load("examiners/delete/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
				uiIcon: "qm_delete",
				disabled: false
				}
				],

			select: function(event, ui) {},
		});
	});
</script>
<?php //echo $this->JqueryScripte->ModalFunctions(); ?>
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>
