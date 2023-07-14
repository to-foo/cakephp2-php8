<?php
$stamp_id_url = $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=>'save'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('StampIdUrl',array('type' => 'hidden','value' => $stamp_id_url));

//$testingmethod = $this->request->Verfahren;
?>
<script type="text/javascript">
$(document).ready(function(){

	function SendStampId(data,field){ 

		let name = data.attr("name");
		let check = name.indexOf(field);

		if(check < 0) return false;

		let url = $("#StampIdUrl").val();

		let request = new Array();
		let request_field = null;

		if(field == "[examiner]") request_field = "examiner_id";
		if(field == "[supervision]") request_field = "supervisor_id";

		request.push({name: "ajax_true", value: 1});
		request.push({name: request_field, value: data.val()});
		
		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url,
			data	: request,
			success: function(data) {

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


		console.log(url);

	};


$("form.editreport select").change(function() {

	SendStampId($(this),"[examiner]");
	SendStampId($(this),"[supervision]");


	/*

	$("#AjaxSvgLoader").show();

	var data = new Array();

	data.push({name: "ajax_true", value: 1});

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
		*/
	});
});
</script>
