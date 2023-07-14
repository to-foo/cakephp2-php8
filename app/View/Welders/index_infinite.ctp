<?php
	$i = 0;

	foreach($welders as $_welder):

		$class = null;

		if ($i++ % 2 == 0) {
			$class = ' class="altrow infinite_sroll_item ' . $paging['tr_marker'] . '"';
		}

		if($_welder['WelderActive']['active'] == 0){
			$class = ' class="deactive infinite_sroll_item ' . $paging['tr_marker'] . '" title="'.__('This user is deactive',true).'"';
		}
?>
<tr<?php echo $class;?>>
		<td class="small_cell">
		<span class="for_hasmenu1 weldhead">
		<?php echo $this->Html->link($_welder['Welder']['identification'],
			array_merge(array('action' => 'overview'),
			$_welder['Welder']['_welder_link']),
			array(
				'class'=>'round icon_show ajax hasmenu1',
				'rev' => implode('/',$_welder['Welder']['_welder_link'])
			)
		); ?>
        </span>
        </td>

        <?php
		foreach($xml->section->item as $_key => $_xml){
			if(trim($_xml->condition->key) != 'enabled') continue;
			$class= null;
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
		<?php echo __('Certificates'); ?>:
		</span>
        <span class="summary_span">
        <?php

		if(isset($monitorings[$_welder['Welder']['id']]['summary']['monitoring'])){
                    pr($monitorings);
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


</tr>
<?php endforeach; ?>
<?php
$colspan = count($xml->section->item) + 2;

echo '<tr>';
echo '<td colspan="' . $colspan . '">';
echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')));
echo '</td>';
echo '<td>';
echo '</td>';
echo '</tr>';
?>
<script type="text/javascript">

$(document).ready(function(){
			$("div.container_summary_single").hide();
			$("div.container_eyechecksummary_single").hide();
                        $("div.container_monsummary_single").hide();
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

                        $("a.summarymon_tooltip").tooltip({
				content: function () {
					var output = $(".container_summarymon_single_" + $(this).prop('rel')).html();
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
							$("#dialog").load("welders/edit/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
					uiIcon: "qm_edit",
					disabled: false
					},
					{
					title: "<?php echo __('Qualifications');?>",
					cmd: "status",
					action :	function(event, ui) {
							$("#container").load("welders/certificates/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
//							$("#dialog").dialog("open");
							},
					uiIcon: "qm_certificate",
					disabled: false
					},
					{
					title: "<?php echo __('Vision tests');?>",
					cmd: "status",
					action :	function(event, ui) {
							$("#dialog").load("welders/eyechecks/" + ui.target.attr("rev"), {
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
							$("#dialog").load("welders/delete/" + ui.target.attr("rev"), {
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


	    $(".table_resizable th").resizable();
	    $(".table_resizable tr").resizable();
	    $(".table_resizable td").resizable();

});

</script>
<?php
if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true){
	echo $this->element('infinite/infinite_links');
}
?>
