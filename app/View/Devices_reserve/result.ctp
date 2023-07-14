<div class="examiners index">
<h2>
<?php
echo __('Search results');
?>
</h2>

<div class="quicksearch">
</div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if (isset( $devices)){
if(count($devices) == 0){
	echo '<div class="hint"><p>';
	echo __('No results available.',true);
	echo '</p></div>';
}
}
?>
<div>
<?php
 //   echo $this->Html->link(__('Print device list',true), array_merge(array('action' => 'pdfinv'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_devices_pdf','title' => __('Print device list',true)));?>
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
<div id="container_summary" class="container_summary" ></div>
<?php  echo $this->element('monitoring_legend');?>
<div id="container_table_summary" class="container_table_summary" >
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
		$i = 0;
		if (isset($devices))foreach ($devices as $examiner):

		$class = null;

		if ($i++ % 2 == 0) {
			$class = ' class="infinite_sroll_item altrow"';
		}

		if($examiner['Device']['active'] == 0){
			$class = ' class="infinite_sroll_item deactive" title="'.__('This device is deactive',true).'"';
		}

		$this->request->projectvars['VarsArray'][13] = 0;
		$this->request->projectvars['VarsArray'][14] = 0;
		$this->request->projectvars['VarsArray'][16] = $examiner['Device']['id'];

		echo '<tr '.$class.'>';

		foreach($xml->section->item as $_key => $_xml){

//			if(trim($_xml->condition->key) != 'enabled') continue;
			if(trim($_xml->condition->key) != 'enabled' && empty($_xml->condition->link)) continue;
			$class= null;
			if(!empty($_xml->class)) $class = trim($_xml->class);
			echo '<td class="'.$class.'">';

			if(!empty($_xml->condition->link)){

			echo '<span class="for_hasmenu1 weldhead">';

			$this->request->projectvars['VarsArray'][15] = isset($examiner['DeviceTestingmethod'][0]['id']) ?$examiner['DeviceTestingmethod'][0]['id'] : '';

			echo $this->Html->link(!empty($examiner[trim($_xml->model)][trim($_xml->key)]) ? $examiner[trim($_xml->model)][trim($_xml->key)] : '-',
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
				echo h((trim($examiner[trim($_xml->model)][trim($_xml->key)])));
			}
			echo '</td>';
		}
        echo '<td><span class="discription_mobil">';
		echo __('Monitorings');
		echo '</span><span class="summary_span">';

		if(isset($monitorings[$examiner['Device']['id']]['summary']['monitoring'])){

			$thissummary = array();

			foreach($monitorings[$examiner['Device']['id']]['summary']['monitoring'] as $_key => $_qualifications){

				$thissummary = $this->Quality->MonitoringSummarySingle($_key,$_qualifications);

				echo '<div class="container_summary_single container_summary_single_'.$_key.'_' . $examiner['Device']['id'] . '">';
				echo $thissummary;
				echo '</div>';

				$thissummary = null;

				$this_link = $this->request->projectvars['VarsArray'];
				$this_link[15] = $examiner['DeviceTestingmethod'][0]['id'];
				$this_link[17] = $_qualifications[key($_qualifications)][$examiner['Device']['id']]['info']['device_certificate_id'];
				$this_link[18] = $_qualifications[key($_qualifications)][$examiner['Device']['id']]['info']['id'];

				echo $this->Html->link($_key,array_merge(array('action' => 'monitorings'),$this_link),array('title' => $_key,'rev'=> $_key,'rel'=> $examiner['Device']['id'], 'class' => 'summary_tooltip ajax icon monitoring_'.$_key));


			}
		}

		echo '</span></td></tr>';

		endforeach;

		echo '</table>';
?>

<?php //echo $this->element('infinite_scroll');?>
<?php // echo $this->element('page_navigation');?>

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
	    $(".table_resizable tr").resizable();
	    $(".table_resizable td").resizable();

	}

	$("a.ajax").click(function() {
		$(".ui-dialog").hide();
		$("#maximizethismodal").show();
		$("a#maximizethismodal").attr("title",$("a#maximizethismodal").text() + " - " + $("div#dialog h2").text());
		return;
	});

	$(document).ready(function(){
		setscrollfunctions();
	});
</script>


<?php echo $this->element('js/ajax_modal_link');?>
<?php echo $this->element('js/ajax_breadcrumb_link');?>
<?php echo $this->element('js/ajax_link');?> 
