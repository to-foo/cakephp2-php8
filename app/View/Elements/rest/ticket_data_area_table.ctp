<?php
$Status = array('0' => __('active',true), '1' => __('deaktiv',true));
echo $this->Form->input('ShowContentTicketId',array('value' => $ticket_id_show_container,'type' => 'hidden'));

foreach ($ticket as $ticketarea => $tickets) {

echo '<div class="areas" id="'.$ticketarea.'">';
echo '<h3> ';
echo __('Ticket',true);
echo '</h3>';



if(count($tickets) == 0) return;

echo '<table class="advancetool table_infinite_sroll">';
echo '<tr>';
echo '<th class="collaps"> </th>';
echo '<th class="collaps"> </th>';
echo '<th class="collaps">' . __('Weld Statisic',true) . '</th>';
echo '<th class="collaps">' . __('Creator',true) . '</th>';
echo '<th class="collaps">' . __('Priority',true) . '</th>';
echo '<th class="collaps">' . __('Technical Places',true) . '</th>';
echo '<th class="">' . __('Reports',true) . '</th>';
echo '<th class="">' . __('Bemerkungen',true) . '</th>';
echo '<th class="collaps">' . __('Created',true) . '</th>';

/*
*/
echo '</tr>';

$i = 0;

foreach ($tickets as $_key => $_value) {

//	$i++;
//	if($i == 5) break;

	$this->request->projectvars['VarsArray'][16] = $_value['Ticket']['id'];
	$class = null;

	if($i++ % 2 == 0) $class .= ' altrow ';
	if($_value ['Ticket']['status'] == 1) $class .= ' deactive ';
	echo '<tbody>';

	echo '<tr class="' . $class . '">';

	echo '<td>';

	if ($_value['Ticket']['status'] > 0) {

		$this->request->projectvars['VarsArray'] [4] = $_value['Ticket']['reportnumber_id'];
		echo $this->Html->link(
		$_value['Ticket']['id'] . ' - ' . $_value['Ticket']['created'],
		'javascript:',
		array(
		    'id' => 'ViewDetails',
		    'class' => 'round nowrap ViewDetails'
			)
		);
	} else {

		if(isset($_value['Reportnumbers'])) {

			echo $this->Html->link(
			$_value['Ticket']['id'] . ' - ' . $_value['Ticket']['created'],
			'javascript:',
			array(
			    'id' => 'ViewDetails',
			    'class' => 'round nowrap ViewDetails'
				)
			);

		}

		echo '<div class="advance_diagrammcontent">';
		echo '<div class="div_plot_container">';
		echo '<div class="table_result">';

		if(!isset($_value['Reportnumbers'])) {

			echo '<p class="editable nowrap ViewDetails">';
			echo $_value['Ticket']['id'] . ' - ' . $_value['Ticket']['created'];
			echo '</p>';

		}

		echo $this->Html->link(__('Edit ticket', true),array_merge(array('action' => 'editticket'),$this->request->projectvars['VarsArray']),array('title' => __('Edit ticket', true),'class' => 'icon icon_edit modal_post'));
		echo $this->Html->link(__('Create report', true),array_merge(array('action' => 'createreport'),$this->request->projectvars['VarsArray']),array('title' => __('Create report', true),'class' => 'icon icon_add modal_post'));
		echo $this->Html->link(__('Delete ticket', true), array_merge(array('action' => 'deleteticket'), $this->request->projectvars['VarsArray']),array('title' => __('Delete ticket', true),'class' => 'icon icon_delete modal_post'));

		echo '</div>';
		echo '</div>';
		echo '</div>';

	}

	echo '</td>';
	echo '<td>';

	if($_value['Ticket']['status'] > 0){

		echo '<p class="editable edit_status" ticket-id="'.$_value['Ticket']['id'].'" ticket-url="'.$this->Html->url(array_merge(array('action' => 'editticket'),$this->request->projectvars['VarsArray'])).'">';
		echo __('Reopen');
		echo '</p>';

	}

	echo '</td>';
	echo '<td>';

	echo '<div class="advance_diagrammcontent">';
	echo '<div class="div_plot_container">';
	echo '<div class="table_result">';

	if(isset($_value['Weldstatistik']['incomming'])){

		echo '<p class="nowrap">';
		echo __('Number of welds in this ticket') . ': <b>' . $_value['Weldstatistik']['incomming'] . '</b>';
		echo '</p>';

	} 
	
	if(isset($_value['Weldstatistik']) && isset($_value['Weldstatistik']['working']) && is_array($_value['Weldstatistik']['working'])){

		echo '<p class="nowrap"><br>';
		echo '<b>' . __('Selected welds') . '</b><br>';

		foreach($_value['Weldstatistik']['working'] as $__key => $__value){
			echo $__key . ': <b>' . $__value . '</b><br>';
		}

		echo '</p>';

	}

	if (isset($_value['Weldstatistik']) && isset($_value['Weldstatistik']['working']) && $_value['Weldstatistik']['working'] == 0) {
	
		echo '<p class="nowrap"><br>';

		echo __('Selected welds') . ': ';
		echo '<b>' . $_value['Weldstatistik']['working'] . '</b>';
		echo '</p>';

	}
	if (isset($_value['Weldstatistik']) && isset($_value['Weldstatistik']['complete']) && is_array($_value['Weldstatistik']['complete'])) {
		
		echo '<p class="nowrap"><br>';
		echo  __('Complete welds') . '<br>';

		foreach($_value['Weldstatistik']['complete'] as $__key => $__value){
			echo $__key . ': <b>' . $__value . '</b><br>';
		}

		echo '<br></p>';

	}
	if(isset($_value['Weldstatistik']['open'])){

		echo '<p class="nowrap">';
		echo  __('Open welds') . ': ';
		echo '<b>' . $_value['Weldstatistik']['open'] . '</b>';
		echo '</p>';

	}
	echo '</div>';
	echo '</div>';
	echo '</div>';
	
	echo '</td>';
	echo '<td>' . $_value['Ticket']['user_name'] . '</td>';

	echo '<td class="status_td">';

	$priority_class = null;

	if($_value['Ticket']['priority'] == 0) $priority_class = 'low';
	if($_value['Ticket']['priority'] == 1) $priority_class = 'height';

	echo '<p class="editable edit_priority ' . $priority_class . '" ticket-id="'.$_value['Ticket']['id'].'">';
	echo $_value['Ticket']['priority_text'];
	echo '</p>';
	echo '</td>';
	echo '<td>' . $_value['Ticket']['technical_place'] . '</td>';
	echo '<td>';
	if(isset($_value['Reportnumbers'])){
		foreach($_value['Reportnumbers'] as $__key => $__value){

			if(count($__value['TicketReportnumber']) == 2) continue;

			echo $this->Html->link($__value['TicketReportnumber']['year'] . '-' . $__value['TicketReportnumber']['number'] . ' (' . $__value['TicketReportnumber']['name'] . ') ', 
				array_merge(
					array(
						'controller' => 'reportnumbers',
						'action' => 'pdf'
					), 
					$__value['ReportnumberLink']
				),
				array(
					'title' => $__value['TicketReportnumber']['year'] . '-' . $__value['TicketReportnumber']['number'] . '(' . $__value['TicketReportnumber']['name'] . ') ',
					'class' => 'round showpdflink'
					)
				);

		}
	}
	echo '</td>';
	echo '<td>' . $_value['Ticket']['remarks'] . '</td>';
	echo '<td>' . $_value['Ticket']['created'] . '</td>';


	echo '</tr>';

	echo '<tr ticket-id="' . $_value['Ticket']['id'] . '" class="' . 'accordion' . '">';
	echo '<td colspan="100%">';

	if(isset($_value['Reportnumbers'])) {

		echo '<div class="advance_diagrammcontent">';
		echo '<div class="div_plot_container">';

		foreach ($_value['Reportnumbers'] as $key_reportnumber => $reportnumbers) {

			if(count($reportnumbers['TicketReportnumber']) == 2) continue;

			$this->request->projectvars['VarsArray'] [4] = $reportnumbers['TicketReportnumber']['reportnumber_id'];
			$current_label = $reportnumbers['TicketReportnumber']['year'] . '-' . $reportnumbers['TicketReportnumber']['number'] . ' (' . $reportnumbers['TicketReportnumber']['name'] . ')';
			$url = $this->Html->url(array_merge(array('controller'=>'rests','action'=>'createreport'),$this->request->projectvars['VarsArray']));

			echo '<div class="table_result">';
			echo '<div class="editable_content">';
			echo '<p><b>' . $current_label . '</b></p>';
			echo '</div>';
			echo $this->Html->link($current_label,array_merge(array('controller'=>'reportnumbers','action' => 'edit'),$this->request->projectvars['VarsArray']),array('title' => __('Edit') . ' ' . $current_label,'class' => 'icon icon_edit ajax'));
			echo $this->Html->link($current_label,array_merge(array('controller'=>'reportnumbers','action' => 'testingAreas'),$this->request->projectvars['VarsArray']),array('title' => __('View') . ' ' . $current_label,'class' => 'icon icon_weld modal'));
			echo $this->Html->link($current_label,array_merge(array('controller'=>'reportnumbers','action' => 'view'),$this->request->projectvars['VarsArray']),array('title' => __('View welds') . ' ' . $current_label,'class' => 'icon icon_view ajax'));
			echo $this->Html->link($current_label,array_merge(array('controller'=>'reportnumbers','action' => 'sign'),$this->request->projectvars['VarsArray']),array('title' => __('Sign') . ' ' . $current_label,'class' => 'icon icon_sign ajax'));
			echo $this->Html->link($current_label,array_merge(array('controller'=>'reportnumbers','action' => 'errors'),$this->request->projectvars['VarsArray']),array('title' => __('Print') . ' ' . $current_label,'class' => 'icon icon_print modal'));
			echo $this->Html->link(__('Report status', true),'javascipt:',array('title' => __('Report status', true),'class' => 'icon ' . $reportnumbers['TicketReportnumber']['status_class']));

			echo '<div class="editable_content">';

			if($reportnumbers['TicketReportnumber']['status'] == 0) echo '<p class="editable edit_testingcomp" data-id="'.$reportnumbers['TicketReportnumber']['reportnumber_id'].'" title="' . __('Click to change testingcompany',true) . '">';
			else echo '<p class="editable">';

			echo $reportnumbers['TicketReportnumber']['Testingcomp'];

			echo '</p>';
			echo '</div>';
			echo '</div>';

		}

		echo '</div>';
		echo '</div>';

	}

	echo '</td>';
	echo '</tr>';

	echo '</tbody>';

}

echo '</table>';
echo '</div>';
}

if(isset($reportnumbers['Testingcomp'])) echo $this->Form->input('TestingcompsForChange',array('type' => 'hidden','value' => trim($reportnumbers['Testingcomp'])));
?>

<?php

echo $this->Form->input('EditUrl',
	array(
		'type' => 'hidden',
		'value' => Router::url(
			array_merge(
				array(
					'controller' => 'rests',
					'action'=>'createreport',
				),
				$this->request->projectvars['VarsArray'])
			)
		)
	);
?>

<script>
$(() => {

	$('.accordion').hide();
	$("#resultlinktickets").hide();
	$("#resultlinktickets").css("visibility","hidden");

	if($("#ShowContentTicketId").val() > 0){

		let ShowContentId = $("#ShowContentTicketId").val();

		$("table.advancetool tbody").find("tr[ticket-id='" + ShowContentId + "']").show();


	}

	$(".ViewDetails").click( e => {

		$(e.target).css('background-color', '#c6c6c6');
		let accordion = $(e.target).parent().parent().next('.accordion');

		if($(accordion).is(":hidden")) {

			$(e.target).text('↳' + ' ' + $(e.target).text());
			$(accordion).css('background-color', '#c6c6c6');
			$(accordion).fadeIn("slow");

		} else {

			$(e.target).text($(e.target).text().replace("↳ " , ""));
			$(accordion).fadeOut( "slow");

		}

		return false;
	});

	$("div.table_result a.ajax").click(function() {

		let url = $(".breadcrumbs a").last().attr("href");
//		console.log(url);

		$("#resultlinktickets").show();
		$("#resultlinktickets").css("visibility","visible");
		$("#resultlinktickets").attr("href",url);

		return false;
	});

});
</script>
