<?php 
if(!isset($OpenSupplierEdit)) return;

echo $this->Form->input('OpenEditSupplierUrl',array(
  'type' => 'hidden',
  'value' => Router::url(
      array_merge(
       array(
         'controller' => 'suppliers',
          'action'=>'edit',
       ),
       $this->request->projectvars['VarsArray']
     )
   ) 
  )
)
;
?>

<script type="text/javascript">
$(document).ready(function(){

	var modalheight = Math.ceil(($(window).height() * 90) / 100);
	var modalwidth = Math.ceil(($(window).width() * 90) / 100);

	var dialogOpts = {
		modal: false,
		width: modalwidth,
		height: modalheight,
		autoOpen: false,
		draggable: true,
		resizeable: true
	};

	$("#dialog").dialog(dialogOpts);

	var data = new Array();
	data.push({name: "ajax_true", value: 1});
	data.push({name: "dialog", value: 1});

	$("#dialog").empty();

	$.ajax({
		type	: "POST",
		cache	: false,
		url		: $("#OpenEditSupplierUrl").val(),
		data	: data,
		success: function(data) {
			$("#dialog").html(data);
			$("#dialog").dialog("open");
			$("#dialog").show();
			$("#dialog").css('overflow','scroll');
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
</script>