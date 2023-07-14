<?php
if(!isset($action)) return;
if(!isset($minLength)) return;
if(!isset($discription)) return;

$targetcontroller = 'reportnumbers';
$targetation = 'view';

echo $this->Form->create('QuicksearchReport',array('id' => 'QuicksearchReportForm','class' => 'quip_search_form default'));

echo $this->Form->input('hidden', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => 1,
				)
			);

echo $this->Form->input('this_id', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => 0,
				)
			);

echo $this->Form->input('searching_autocomplet', array(
				'class' =>'autocompletion searching_autocomplet',
				'label' => false,
				'div' => false,
				'title' => $discription,
				'placeholder' => $discription,
				'formaction' => 'autocomplete'
				)
			);

echo $this->Form->input('json_url', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => $this->Html->url(array_merge(array('controller' => 'topprojects','action' => $action),$this->request->projectvars['VarsArray'])),
			)
		);

echo $this->Form->input('min_length', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => $minLength,
			)
		);

echo $this->Form->input('target_controller', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => $targetcontroller,
			)
		);

echo $this->Form->input('target_action', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => $targetation,
			)
		);

		echo $this->Form->input('target_order', array(
						'type' =>'hidden',
						'label' => false,
						'div' => false,
						'value' => $this->Html->url(array_merge(array('controller' => $targetcontroller,'action' => $targetation), $this->request->projectvars['VarsArray'])),
					)
				);

echo $this->Form->end();
/*
echo $this->Html->link(__('Search reports'),
	array_merge(
		array(
			'controller' => 'searchings',
			'action' => 'search'
		),
		$this->request->projectvars['VarsArray']
	),
	array(
		'title' => __('Search reports'),
		'class' => 'round ajax_searching_link',
		'rel' => 'testingreports'
	)
);

echo $this->Html->link('Search orders',
	array_merge(
		array(
			'controller' => 'searchings',
			'action' => 'search_reports'
		),
		$this->request->projectvars['VarsArray']
	),
	array(
		'title' => 'Search orders',
		'class' => 'round'
	)
);
echo $this->Html->link('Statistics',
	array_merge(
		array(
			'controller' => 'searchings',
			'action' => 'search_reports'
		),
		$this->request->projectvars['VarsArray']
	),
	array(
		'title' => 'Statistics',
		'class' => 'round'
	)
);
*/
?>
<script>
$(function() {

	$("a.ajax_searching_link").click(function() {

	$("#AjaxSvgLoader").show();

	var data = $(this).serializeArray();
	data.push({name: "ajax_true", value: 1});
	data.push({name: "search_type", value: $(this).attr("rel")});

	$.ajax({
		type	: "POST",
		cache	: false,
		url		: $(this).attr("href"),
		data	: data,
		success: function(data) {
			$("#container").html(data);
			$("#container").show();
			$("#AjaxSvgLoader").hide();
		},
		statusCode: {
	    404: function() {
	      alert( "page not found" );
				location.reload();
	    }
	  },
		statusCode: {
	    403: function() {
	      alert( "page blocked" );
				location.reload();
	    }
	  }
		});

		return false;
	});

	$("form#QuicksearchReportForm").on("keyup keypress", function(e) {
		var keyCode = e.keyCode || e.which;
		if(keyCode === 13) {
			e.preventDefault();
			return false;
		}
	});

	$("#QuicksearchReportSearchingAutocomplet").blur(function(){
		$("#QuicksearchReportSearchingAutocomplet").val("");
		$("#QuicksearchReportForm").removeClass("search").addClass("default");
	});

	var i = 0;
	$("#QuicksearchReportForm input.autocompletion").each(function(key,value){

		$(this).autocomplete({
			minLength: $("#QuicksearchReportMinLength").val(),
			delay: 4,
			source: function(request,response) {

				$("#QuicksearchReportForm").removeClass("default").addClass("search");

				$.ajax({
					type	: "POST",
					url: $("#QuicksearchReportJsonUrl").val(),
					dataType: "json",
					data: {
						term : request.term,
						targetcontroller : $("#QuicksearchReportTargetController").val(),
						targetation : $("#QuicksearchReportTargetAction").val(),
					},
			success: function(data) {
				response(data);
				$("#QuicksearchReportForm").removeClass("search").addClass("default");
				},
			});
			},
			select: function(event,ui) {

				$("#QuicksearchReportSearchingAutocomplet").val(ui.item.value);
				$("#QuicksearchReportThisId").val(ui.item.key);

				var data = $("#QuicksearchReportForm").serializeArray();

				data.push({name: "ajax_true", value: 1});
				data.push({name: "this_id", value: ui.item.key});

				$.ajax({
					type	: "POST",
					cache	: false,
					url		: "reportnumbers/view" + ui.item.key,
					data	: data,
					success: function(data) {
						$("#container").html(data);
						$("#container").show();
					}
				});
				return false;
			}
		});
		i++;
	});

	$("#QuicksearchReportSearchingAutocomplet").change(function() {
		if($("#QuicksearchReportSearchingAutocomplet").val() == "" && $("#QuicksearchReportThisId").val() == 0){
			$("#container").load($("#QuicksearchReportForm").attr("action"), {"ajax_true": 1});
		}
	});

	$("form#QuicksearchReportForm").bind("submit", function() {
		if($("#this_id").val() == 0){
			return false;
		}

		var data = $(this).serializeArray();
		data.push({name: "ajax_true", value: 1});
		$.ajax({
			type	: "POST",
			cache	: false,
			url		: this.getAttribute("action"),
			data	: data,
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
			}
		});
		return false;
	});
});
</script>
