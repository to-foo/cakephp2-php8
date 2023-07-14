<div class="modalarea examiners index inhalt">
<h2><?php echo __('Gerätesuche',true);?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="quicksearch">
<?php echo $this->element('barcode_scanner');?>
<?php
echo $this->element('searching/search_quick_device',array('target_id' => 'id','targedaction' => 'view','action' => 'quicksearch','minLength' => 2,'description' => __('Intern number', true)));
echo $this->Html->link(__('Add device',true), array_merge(array('action' => 'add'), array()), array('class' => 'modal icon icon_devices_add','title' => __('Add device',true)));
echo $this->Html->link(__('Print device list',true), array_merge(array('action' => 'pdfinv'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_devices_pdf showpdflink','title' => __('Print device list',true)));
?>
</div>
<div>
<?php


if(($this->Session->check('searchdevice.params'))) {
	echo '<div class="hint"><p>';
  echo $this->Session->read('searchdevice.params');
	echo '</p></div>';
}
?>
</div>
<?php
if(isset( $devices)){
	if(count($devices) == 0){
		echo '<div class="hint"><p>';
		echo __('No results available.',true);
		echo '</p></div>';
	}
}
?>
<div id="container_summary" class="container_summary" ></div>
<?php echo $this->element('monitoring_legend');?>
<?php echo $this->element('devices/sort_form');?>
<div id="container_table_summary" class="" >
<table class="table_resizable table_infinite_sroll">
<tr>
<?php
foreach($xml->section->item as $_key => $_xml){

	if(trim($_xml->condition->key) != 'enabled' && empty($_xml->condition->link)) continue;

	$class = null;

	if(!empty($_xml->class)) $class = trim($_xml->class);

	echo '<th class="'.$class.'">';
	echo trim($_xml->description->$locale);
	echo '</th>';
}
?>
<th class="small_cell"><?php echo __('Monitorings',true); ?></th>

</tr>

<?php

if(!isset($paging)) $paging['tr_marker'] = null;

$i = 0;

if (isset($devices)) foreach ($devices as $examiner):

	$class = null;

	if ($i++ % 2 == 0) $class = ' class="infinite_sroll_item altrow ' . $paging['tr_marker'] . '"';
	if($examiner['Device']['active'] == 0) $class = ' class="infinite_sroll_item deactive ' . $paging['tr_marker'] . '" title="'.__('This device is deactive',true).'"';

	$this->request->projectvars['VarsArray'][13] = 0;
	$this->request->projectvars['VarsArray'][14] = 0;
	$this->request->projectvars['VarsArray'][16] = $examiner['Device']['id'];

?>

<tr data-page="<?php echo (isset($paging['page']) ? $paging['page'] : '');?>" <?php echo $class;?>>

<?php

foreach($xml->section->item as $_key => $_xml){

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
				'class'=> trim($_xml->condition->link->class).' deviceopenlink',
				'rev' => implode('/',$this->request->projectvars['VarsArray'])
			)
		);

		echo '</span>';

	}

	if(empty($_xml->condition->link)){

		if(isset($examiner[trim($_xml->model)][trim($_xml->key)])){
			echo '<span class="discription_mobil">';
			echo trim($_xml->description->$locale);
			echo '</span>';
			echo h((trim($examiner[trim($_xml->model)][trim($_xml->key)])));
		}
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
</tr>

<?php endforeach;?>

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

	    $(".table_resizable th").resizable();
	    $(".table_resizable tr").resizable();
	    $(".table_resizable td").resizable();

	}

	$(document).ready(function(){
		setscrollfunctions();
	});
</script>

<?php
$SearchUrl = $this->Html->url(array_merge(array(
		'controller' => $this->request->params['controller'],
		'action' => $this->request->params['action']
	),$this->request->projectvars['VarsArray'])
);
$PageUrl = $this->Html->url(array_merge(array(
		'controller' => $this->request->params['controller'],
		'action' => $this->request->params['action']
	),$this->request->projectvars['VarsArray'])
);
$PageUrl .= '/page:';

echo $this->Form->input('CurrentLoadetPage',array('type' => 'hidden','value' => isset($paging['page']) ? $paging['page'] : 1));
echo $this->Form->input('PageCount',array('type' => 'hidden','value' => (isset($paging['pageCount']) ? $paging['pageCount'] : '')));
echo $this->Form->input('DefaultPageUrl',array('type' => 'hidden','value' => $PageUrl));
echo $this->element('js/devices_bread_search_combined');
echo $this->element('js/examiner_summary');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/ajax_paging_devices_searchresult', array('DeviceData' => $DeviceData,
																																 'CurrentModel' => $CurrentModel));
echo $this->element('page_navigation');
?>

<script type="text/javascript">
$(document).ready(function(){
	$("a.deviceopenlink").click(function() {
		$("#maximizethismodal").hide();
		$("#searchresultlinkdevices").show();
		$("#searchresultlinkdevices").css("visibility","visible");



		$("#dialog").dialog().dialog("close");
		$("#searchresultlinkdevices").attr("href","<?php echo $SearchUrl;?>");
		return false;
	});
});
</script>
