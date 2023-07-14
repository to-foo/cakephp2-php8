<div class="clear"></div>
<div class="modalarea detail">
	<h2><?php echo __('Testing reports'); ?></h2>

    <?php 
//    if(isset($reportnumber)) pr($reportnumber);
//    if(isset($reportnumbers)) pr($reportnumbers);
    $first = reset($xml);
    $first = $first['settings'];

	if(count($reportnumbers) == 0){
		echo '<div class="error">';
		echo __('There are no reports from your testing company.', true);
		echo '</div>';
	}
        
	foreach($display as $_display) {
		if(trim($_display->url) == $this->request->controller.'/'.$this->request->action) {
			$display = $_display;
			break;
		}
	}
	?>

	<table cellpadding="0" cellspacing="0">
	<tr>
<?php
// muss noch im Controller gemacht werden
//unset($items[1]);
foreach($items as $item) {
	echo '<th>';
	if(isset($item->sortable) && $item->sortable == 1) echo $this->Paginator->sort(trim($item->key), isset($item->description) && trim($item->description) != '' ? __(ucfirst(trim($item->description))) : __(ucfirst(trim($item->key))));
	else echo h(isset($item->description) && trim($item->description) != '' ? __(ucfirst(trim($item->description))) : __(ucfirst(trim($item->key))));
	echo '</th>';
	}
?>
<th class="actions">&nbsp;</th>
	</tr>
	<?php
	$i = 0;
	foreach ($reportnumbers as $reportnumber):

		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' altrow';
		}
		if($reportnumber['Reportnumber']['status'] == 1){
			$class = ' closed';
		}
		if($reportnumber['Reportnumber']['status'] == 2){
			$class = ' closed2';
		}
		if($reportnumber['Reportnumber']['status'] == 3){
			$class = ' settled';
		}
		if($reportnumber['Reportnumber']['delete'] == 1){
			$class = ' delete';
		}

		$showRepair = false;
		if(!Configure::check('RepairsEnabled') || !!Configure::read('RepairsEnabled')) {
			if(count($evaluation = preg_grep('/Evaluation$/', array_keys($reportnumber))) > 0) {
				$evaluation = reset($evaluation);
				$ne = array_filter($reportnumber[$evaluation], function($elem) use($evaluation) {return isset($elem[$evaluation]['result']) && $elem[$evaluation]['result']==2;});
				$showRepair = !!count($ne);
			}
		}

	?>
	<tr class="<?php echo $class;?>">
		
		<?php
			foreach($items as $item) {
				echo '<td>';
				echo '<span class="discription_mobil">';
				echo __(isset($item->description) && trim($item->description) != '' ? __(ucfirst(trim($item->description))) : __(ucfirst(trim($item->key)))).':';
				echo '</span>';

				$VarsArray[0]=$reportnumber['Reportnumber']['topproject_id'];
				$VarsArray[1]=$reportnumber['Reportnumber']['cascade_id'];
				$VarsArray[2]=$reportnumber['Reportnumber']['order_id'];
				$VarsArray[3]=$reportnumber['Reportnumber']['report_id'];
				$VarsArray[4]=$reportnumber['Reportnumber']['id'];
				switch(strtolower(trim($item->model)).'.'.strtolower(trim($item->key))) {
					case 'reportnumber.number':
						if($this->request->data['type'] == 'assign') {
							echo $this->Html->link($reportnumber['Reportnumber']['year'] . '-' . $reportnumber['Reportnumber']['number'] . '/' . $reportnumber['Testingmethod']['name'], array_merge( array('action'=>'setparent'), $this->request->projectvars['VarsArray'] ), array('class'=>'round mymodal', 'rel' => $reportnumber['Testingmethod']['allow_children'] == 1 ? 'setParent['.$reportnumber['Reportnumber']['id'].']' : 'setChild['.$reportnumber['Reportnumber']['id'].']'));
						} else {
							echo $this->Html->link($reportnumber['Reportnumber']['year'] . '-' . $reportnumber['Reportnumber']['number'] . '/' . $reportnumber['Testingmethod']['name'], array_merge(array('action' => 'view'), $VarsArray), array('class'=>'round ajax_modal'));
						}
						break;
						
					case 'reportnumber.status':
						echo $this->ViewData->ShowStatus($reportnumber); 
						break;
						
					case 'evaluation.discription':
						$colors = array();
						$title = array();
						$title[] = __('Testing areas'); 
						if(Configure::read('WeldManager.color')) {
							if(isset($reportnumber['weld_types'][0]) && $reportnumber['weld_types'][0] == 1){
								$colors[] = 'unevaled_welds'; 
								$title[] = __('Unevaled welds',true); 
							}
							if(isset($reportnumber['weld_types'][1]) && $reportnumber['weld_types'][1] == 1){
								$colors[] = 'evaled_welds'; 
								$title[] = __('Evaled welds',true); 
							}
							if(isset($reportnumber['weld_types'][2]) && $reportnumber['weld_types'][2] == 1){
								$colors[] = 'declined_welds'; 
								$title[] = __('Defect welds',true); 
							}
						}
			
						if(Configure::read('WeldManager.show')) {
							if(!isset($xml[$reportnumber['Testingmethod']['value']])) { CakeLog::write('missing-testingmethod', print_r($reportnumber['Reportnumber']['id'], true)); }
							else { 
								if(!Configure::read('WeldManager.hideUseless') || 0 != count($xml[$reportnumber['Testingmethod']['value']]['settings']->xpath('Report'.ucfirst($reportnumber['Testingmethod']['value']).'Evaluation/*[key="id"]'))) {
									echo $this->Navigation->makeLink('reportnumbers','testingAreas',$title,'round_white tooltip_ajax'.(!empty($colors) ? ' '.join(' ',$colors) : null),null,$VarsArray, array('rel'=>$this->request->data['type']));
								}
							}
						}
						break;
						
					default:
						$model = trim($item->model);
						if($model == 'Generally' || $model == 'Specific' || $model == 'Evaluation' ) $model = 'Report'.ucfirst($reportnumber['Testingmethod']['value']).$model;
						echo isset($reportnumber[$model][trim($item->key)])? $reportnumber[$model][trim($item->key)] : '';
				}
				echo '</td>';
			}
		
		?>

		

              
		<td class="actions">
		<?php

		if($reportnumber['Reportnumber']['delete'] != 1){
//			echo $this->Html->link(__('Delete'), array_merge(array('action' => 'delete'), $VarsArray), array('class'=>'icon icon_delete ajax','id'=>'text_delete_report'));
			if($showRepair && AuthComponent::user('Testingcomp.extern') == 0) {
				$count = count($welds = array_unique(Hash::extract($ne, '{n}.'.$evaluation.'.description')));
				echo $this->Html->link(
					__('Create repair'),
					array_merge(array('action' => 'duplicat'), $VarsArray, array(1)),
					array('class'=>'icon icon_repair addrepairlink mymodal', 'title' => __('Create repair report').PHP_EOL.$count.' '.($count == 1 ? __('weld') : __('welds')).':'.PHP_EOL.join(PHP_EOL, $welds))
				);
			}
		}
		elseif($reportnumber['Reportnumber']['delete'] == 1){
				echo $this->Html->link(__('History'), array_merge(array('action' => 'history'), $VarsArray), array('class'=>'round modal'));
		}
			?>
		</td>
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

		$("a.ajax_modal").click(function(){
			$(".ui-dialog").hide();
			$("#maximizethismodal").show();
			$("a#maximizethismodal").attr("title",$("a#maximizethismodal").text() + " - " + $("div#dialog h2").text());
			data = { "ajax_true": 1 };
			$("#container").empty();
			$("#container").css("background-image","url(img/indicator_bg_blue.gif)"); 
			$("#container").css("background-repeat","no-repeat"); 
			$("#container").css("background-position","center center"); 
			$("#container").css("min-height","4em"); 
			$("#container").load($(this).attr("href"), data);
			return false;
		});

		$('th .mymodal, .paging .mymodal').data(<?php echo json_encode($this->request->data); ?>);

		$("a.tooltip_ajax").tooltip({
			disabled: true,
			tooltipClass:"tooltip_testing_areas",
			position: { 
				my: "right", 
				at: "right",
				collision: "flipfit" 
			},
			content: function(callback,ui) {
				var element = $(this);
				var data = $(this).serializeArray();
				data.push({name: "ajax_true", value: 1});
				data.push({name: "dialog", value: 1});
				data.push({name: "layout", value: 1});
				data.push({name: "view", value: 1});
				$.ajax({
						type	: "POST",
						cache	: false,
						url		: element.attr("href"),
						data	: data,
						beforeSend: function(data) {
							$(".modalarea").css("cursor","wait");
						},
						success: function(data) {
							$(".modalarea").css("cursor","inherit");
							callback(data);							
						}
					});
			},
			open: function (event,ui) {
				ui.tooltip.on("click", function(event) {
					$("a.tooltip_ajax").tooltip("close");
				});
			},			
		}).tooltip("close").on("mouseout focusout", function(event) {
			event.stopImmediatePropagation();
		});
		
		$("a.tooltip_ajax").on("click", function () {
			$("a.tooltip_ajax").tooltip("close");
			$(this).tooltip("open");
			return false;
		});

		$("body").on("click", function () {
			$("a.tooltip_ajax").tooltip("close");
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
